<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * alipay担保交易支付网关（国内）
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_appalipay{

	/**
	 * @var string 支付方式名称
	 */
    public $name = '支付宝支付';

    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '支付宝支付接口';

     /**
     * @var string 支付方式key
     */
    public $app_key = 'appalipay';

	/**
	 * @var string 中心化统一的key
	 */
	public $app_rpc_key = 'appalipay';

	/**
	 * @var string 统一显示的名称
	 */
    public $display_name = '支付宝';

    /**
	 * @var string 货币名称
	 */
    public $curname = 'CNY';

    /**
	 * @var string 当前支付方式的版本号
	 */
    public $ver = '1.0';

    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'isapp';

	/**
	 * @var array 扩展参数
	 */
	var $supportCurrency = array("CNY"=>"01");

    /**
     * @var int 合作身份者id，以2088开头的16位纯数字
     */
    var $partner = '2088121706705157';
    
    //支付宝账号
    var $seller_id = '3059700799@qq.com';

    /**
     * @var string 商户的私钥（后缀是.pen）文件相对路径
     */
    var $private_key_path = '/public/alipayKey/rsa_private_key.pem';

    /**
     * @var 支付宝公钥（后缀是.pen）文件相对路径
     */
    var $ali_public_key_path = '/public/alipayKey/rsa_public_key.pem';

    /**
     * @var string 签名方式
     */
    var $sign_type = 'RSA';

    /**
     * @var string 签名方式
     */
    var $input_charset = 'utf-8';

    /**
     * @var string 签名方式
     */
    var $cacert = '/public/alipayKey/cacert_eqinfo.pem';

    /**
     * @var HTTPS形式消息验证地址
     */
    var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    /**
     * @var HTTP形式消息验证地址
     */
    var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

    /**
     * @var api访问地址
     */
    var $https_url = 'https://openapi.alipay.com/gateway.do';

    /**
	 * 构造方法
	 * @param null
	 * @return boolean
	 */
    public function __construct(){
        // $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_appalipay_server', 'callback');
        $this->notify_url = kernel::base_url(1).kernel::url_prefix().'/apppayaction.do/alipay';
        
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches))
        {
          $this->notify_url = str_replace('http://','',$this->notify_url);
          $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
          $this->notify_url = "http://" . $this->notify_url;
        }
        else
        {
          $this->notify_url = str_replace('https://','',$this->notify_url);
          $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
          $this->notify_url = "https://" . $this->notify_url;
        }

        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_appalipay', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches))
        {
          $this->callback_url = str_replace('http://','',$this->callback_url);
          $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
          $this->callback_url = "http://" . $this->callback_url;
        }
        else
        {
          $this->callback_url = str_replace('https://','',$this->callback_url);
          $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
          $this->callback_url = "https://" . $this->callback_url;
        }
        
        //添加key密钥的地址信息
        $this->private_key_path = ROOT_DIR.$this->private_key_path;
        $this->ali_public_key_path = ROOT_DIR.$this->ali_public_key_path;
        $this->cacert = ROOT_DIR.$this->cacert;
    }

    //创建本地支付单号
    function createBill($params){
        if(!$params['money'] || !$params['account_id'] || !($params['account_id_to']||$params['account_id_to']==0) || !$params['point']){
            throw new \LogicException(app::get('ectools')->_("支付失败,支付单创建必备参数不能为空"));
        }

        $model_payment = app::get('ectools')->model('payments_agent');
        $paymentId = $model_payment->_getPaymentId($params['account_id']);
        
        //开始保存数据
        $payment = array(
            'payment_id' => $paymentId,
            'money' => $params['money'],
            'point' => $params['point'],
            'discount' => $params['discount'],
            'status' => 'ready',
            'cur_money' => $params['money'],
            'account_id' => $params['account_id'],
            'account_id_to' => $params['account_id_to'],
            'op_id' => $params['account_id'],
            'pay_app_id' => $this->app_key,
            'pay_name' => $this->name,
            'bank' => $this->display_name,
            'account' => $this->partner,
            'pay_account' => '付款账号',
            'currency' => $this->curname,
            'pay_ver' => $this->ver,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'created_time' => time(),
            'modified_time' => time(),
            'nextTime' => time()+5*60,
        );
        // dump($payment);exit;
        $result = $model_payment->insert($payment);
        if(!$result){
            throw new \LogicException(app::get('ectools')->_("支付失败,支付单创建失败"));
        }
        
        return $paymentId;
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment){
        //创建本地的支付单
        $paymentId = $this->createBill($payment);
        // echo $paymentId;exit;
        //访问支付宝的参数
        $params = array(
            'service'           => 'mobile.securitypay.pay',
            'partner'           => $this->partner,
            'seller_id'         => $this->seller_id,
            'payment_type'      => 1,
            '_input_charset'    => $this->input_charset,
            'out_trade_no'      => $paymentId,
            'subject'           => $payment['product_title'],
            'body'              => $payment['product_demo'],
            'total_fee'         => round($payment['money'],2),
            'notify_url'        => $this->notify_url,
            'it_b_pay'          => '30m'
        );
        
        $params_ali = $this->paraFilter($params);
        $params_ali = $this->argSort($params_ali);
        $signData = $this->createLinkstring($params_ali);
        $info = $signData;
        
        $sign = $this->rsaSign($signData, $this->private_key_path);
        // $signData = $this->createLinkstring($params_ali);
        $signData .= '&sign_type="'.$this->sign_type.'"&sign="'.urlencode($sign).'"';
        $data=array(
          'orderInfo' => $signData,
          'orderid' => $paymentId,
          // 'sign' => $sign,
          // 'sign_urlencode' => urlencode($sign),
        );
        return $data;
        //开始访问支付宝的api
        // $params_ali['sign'] = $sign;
        // $params_ali['sign_type'] = $this->sign_type;


        // dump($params_ali);exit;

        // $result = $this->getHttpResponseGet($this->https_url.'?'.$signData ,$this->cacert);
        // dump($params);exit;
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
    //post访问支付宝
    function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '') {

        if (trim($input_charset) != '') {
            $url = $url."_input_charset=".$input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST,true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS,$para);// post传输数据
        $responseText = curl_exec($curl);
        // var_dump( curl_error($curl) );
        // 如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        
        return $responseText;
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
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    function createLinkstringUrlencode($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".urlencode($val)."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        
        return $arg;
    }

    //排序，加密前需要排序处理参数
    function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * return 签名结果
     */
    function rsaSign($data, $private_key_path) {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        // $res = openssl_pkey_get_private($priKey);

        //如果需要直接将密钥字符串放进来
        //$res="密钥字符串";

        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码

        $sign = base64_encode($sign);
        return $sign;
    }
    

    /**
     * 接收支付宝返回的数据
     * Time：2016/02/01 15:16:23
     * @author li
    */
    function callback(){
      dump(11);exit;
    }
    
}
