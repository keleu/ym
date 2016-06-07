<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * alipay notify 验证接口
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
class ectools_payment_plugin_appalipay_server {
    /**
     * @var 支付宝公钥（后缀是.pen）文件相对路径
     */
    var $ali_public_key_path = '/public/alipayKey/alipay_public_key.pem';
    /**
     * HTTPS形式消息验证地址
     */
    var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    /**
     * HTTP形式消息验证地址
     */
    var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

    var $partner = '2088121706705157';
    var $cacert = '/public/alipayKey/cacert_eqinfo.pem';

    //访问模式
    var $transport='http';

    //公钥字符串
    // var $alipayKey_public="/public/alipayKey/rsa_private_key.pem";

    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct(){
        // $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/', 'callback');
        $this->notify_url = kernel::base_url(1).kernel::url_prefix().'/apppayaction.do/alipay';

        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches))
        {
          $this->transport="http";
        }
        else
        {
          $this->transport="https";
        }

        $this->ali_public_key_path = ROOT_DIR.$this->ali_public_key_path;
    }
	
	/**
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */
    public function callback(&$recv)
	{   
        $objPayments = app::get('ectools')->model('payments_agent');
        // file_put_contents('jiang_txt.txt',serialize($_POST['out_trade_no'].'+'.date('Y-m-d H:i:s').'+22'.$_POST['trade_status']),FILE_APPEND);
        if($this->is_return_vaild()){
            if($_POST['trade_status']=='TRADE_SUCCESS'){
                //先判断该订单是否已经支付过了
                $payDate = $objPayments->getRow('*', array('payment_id'=>$_POST['out_trade_no']));
                if($payDate['status']!='succ'){
                    //更新支付单
                    $ret=array(
                        'payment_id' => $_POST['out_trade_no'],
                        'trade_no' => $_POST['trade_no'],
                        'money' => $_POST['total_fee'],
                        'modified_time' => $_POST['notify_reg_time'],
                        'status' => 'succ',
                        'memo' => $_POST['subject'],
                        'account' => $_POST['buyer_email'],
                        'pay_account' => $_POST['seller_email'],
                        'postStr' => json_encode($_POST)
                    );
                    $filter=array('payment_id'=>$_POST['out_trade_no']);
                    try{
                        $is_save = $objPayments->update($ret, $filter);
                    }catch( Exception $e){
                        echo 'false';exit;
                    }
                    $payment = $objPayments->getRow('*', array('payment_id'=>$ret['payment_id']));
                    //处理积分
                    $agent_id = app::get('sysagent')->model('account')->getRow('agent_id',array('account_id'=>$payment['account_id']))['agent_id'];
                    $parent_id = app::get('sysagent')->model('agents')->getRow('parent_id',array('agent_id'=>$agent_id))['parent_id'];
                    $data = array(
                        'agent_seller' => $parent_id,
                        'agent_buyer' => $agent_id,
                        'discount' => $payment['discount'],
                        'settlement_fee_point' => $payment['point'],
                        'settlement_fee_amount' => $payment['money']
                    );
                    $result = app::get('sysuser')->rpcCall('agent.point.trade',$data);
                    if($result['state'] != 'success'){
                        echo "false";
                    }
                }
            echo "success";
            }else if($_POST['trade_status']=='WAIT_BUYER_PAY'){
                echo "success";
            }else if($_POST['trade_status']=='TRADE_FINISHED'){
                echo "success";
            }else{
                echo "false";
            }
        }else{
            echo "false";
        }
    }
    
    /**
     * 检验返回数据合法性
     * @param mixed $form 包含签名数据的数组
     * @param mixed $key 签名用到的私钥
     * @access private
     * @return boolean
     */
    public function is_return_vaild()
	{
        if(empty($_POST)) {//判断POST来的数组是否为空
            return false;
        }
        else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'false';
            if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
            
            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i",$responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**RSA验签
     * $data待签名数据
     * $sign需要验签的签名
     * 验签用支付宝公钥
     * return 验签是否通过 bool值
     */
    function getSignVeryfy($para_temp, $sign) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);
        
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
        
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort,true);
        $isSgin = false;
        $isSgin = $this->rsaVerify($prestr, trim($this->ali_public_key_path), $sign);
        
        return $isSgin;
    }

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    function rsaVerify($data, $ali_public_key_path, $sign)  {
        $pubKey = file_get_contents($ali_public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }
    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    function getResponse($notify_id) {
        $transport = strtolower(trim($this->transport));
        $partner = trim($this->partner);
        $veryfy_url = '';
        if($transport == 'https') {
            $veryfy_url = $this->https_verify_url;
        }
        else {
            $veryfy_url = $this->http_verify_url;
        }
        $veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = $this->getHttpResponseGET($veryfy_url, $this->cacert);
        return $responseTxt;
    }

    function getHttpResponseGET($url,$cacert_url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );
        //如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        
        return $responseText;
    }
    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }
    //排序，加密前需要排序处理参数
    function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    function createLinkstring($para ,$islink = false) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            if($islink == false)$arg.=$key."=\"".$val."\"&";
            else $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        
        return $arg;
    }
}
