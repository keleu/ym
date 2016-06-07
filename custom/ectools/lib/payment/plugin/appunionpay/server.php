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
class ectools_payment_plugin_appunionpay_server extends ectools_payment_app {

    /**
     * @var string 签名证书地址
     */
    var $signCertPath = '/public/unionpayCert/';
    /**
     * @var string 商户代码
     */
    var $merId = '777290058125790';
    // 后台请求地址
    var $sdk_back_trans_url = 'https://101.231.204.80:5000/gateway/api/backTransReq.do';

    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct(){
        $this->signCertPath = ROOT_DIR.$this->signCertPath;
    }

    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    public function callback($in)
    {
        if($this->validate($_POST)){
          // file_put_contents('jiang_txt.txt',serialize($_POST['orderId'].'+'.date('Y-m-d H:i:s').'+22'),FILE_APPEND);
            if($this->updateOrder($_POST)){
                echo 'true';
            }else{
                echo 'false';
            }
        }else{
            echo 'false';
        }
    }

    //查询支付单状态
    public function orderQuery($orderId){
        $unionpay = kernel::single('ectools_payment_plugin_appunionpay');
        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => '5.0.0',         //版本号
            'encoding' => 'utf-8',        //编码方式
            'signMethod' => '01',         //签名方法
            'txnType' => '00',            //交易类型    
            'txnSubType' => '00',         //交易子类
            'bizType' => '000000',        //业务类型
            'accessType' => '0',          //接入类型
            'channelType' => '07',        //渠道类型

            //TODO 以下信息需要填写
            'orderId' => $orderId,
            'merId' => $this->merId,    
            'txnTime' => date('Ymdhis'),
            'certId' => $unionpay->getSignCertId(), //证书ID 
        );
        $sign = $unionpay->sign($params); // 签名
        $result_arr = $unionpay->post($params , $this->sdk_back_trans_url);
        if(count($result_arr)<=0) { //没收到200应答的情况
          return '没收到200应答';
        }
        //验签
        if(!$this->verify($result_arr)){
          return '验签失败';
        }
        if ($result_arr["respCode"] == "00"){
            if ($result_arr["origRespCode"] == "00"){
                $ret = $this->updateOrder($result_arr);
                return $ret;
            } else if ($result_arr["origRespCode"] == "03"
                || $result_arr["origRespCode"] == "04"
                || $result_arr["origRespCode"] == "05"){

                return "交易处理中，请稍微查询";
            } else {

                return "交易失败：" . $result_arr["origRespMsg"];
            }
        } else if ($result_arr["respCode"] == "03"
                || $result_arr["respCode"] == "04"
                || $result_arr["respCode"] == "05" ){

            return "处理超时，请稍微查询。";
        } else {

            return "失败：" . $result_arr["respMsg"];
        }
    }
    //支付成功修改订单信息
    public function updateOrder($orderInfo){
        $objPayments = app::get('ectools')->model('payments_agent');
        //先判断该订单是否已经支付过了
        $payDate = $objPayments->getRow('*', array('payment_id'=>$orderInfo['orderId']));
        if($payDate['status']!='succ'){
            $objMath = kernel::single('ectools_math');
            $money   = $objMath->number_multiple(array($orderInfo['txnAmt'], 0.01));
            $ret=array(
                'payment_id' => $orderInfo['orderId'],
                'trade_no' => $orderInfo['queryId'],
                'money' => $money,
                'modified_time' => time(),
                'status' => 'succ',
                'memo' => '',
                'account' => $orderInfo['accNo'],
                'pay_account' => $orderInfo['merId'],
                'postStr' => json_encode($orderInfo)
            );
            $filter=array('payment_id'=>$orderInfo['orderId']);
            try{
                $is_save = $objPayments->update($ret, $filter);
            }catch( Exception $e){
                return false;
            }
            $payment = $objPayments->getRow('*', array('payment_id'=>$ret['payment_id']));
            //处理积分
            $params['agent_id'] = app::get('sysagent')->model('account')->getRow('agent_id',array('account_id'=>$payment['account_id']))['agent_id'];
            $params['parent_id'] = app::get('sysagent')->model('agents')->getRow('parent_id',array('agent_id'=>$params['agent_id']))['parent_id'];
            $data = array(
                'agent_seller' => $params['parent_id'],
                'agent_buyer' => $params['agent_id'],
                'discount' => $payment['discount'],
                'settlement_fee_point' => $payment['point'],
                'settlement_fee_amount' => $payment['money']
            );
            $result = app::get('sysuser')->rpcCall('agent.point.trade',$data);
            if($result['state'] != 'success'){
                return false;
            }
        }
        return true;
    }

    /**
     * 验签
     * @param $params 应答数组
     * @return 是否成功
     */
    function validate($params) {
        return $this->verify($params);
    }

    /**
     * 验签
     *
     * @param String $params_str            
     * @param String $signature_str         
     */
    function verify($params) {
        // 公钥
        $public_key = $this->getPulbicKeyByCertId ( $params ['certId'] );  
        // 签名串
        $signature_str = $params ['signature'];
        unset ( $params ['signature'] );
        $params_str = $this->createLinkString ( $params, true, false );
        $signature = base64_decode ( $signature_str );
        $params_sha1x16 = sha1 ( $params_str, FALSE );
        $isSuccess = openssl_verify ( $params_sha1x16, $signature,$public_key, OPENSSL_ALGO_SHA1 );
        return $isSuccess;
    }

    /**
     * 根据证书ID 加载 证书
     *
     * @param unknown_type $certId          
     * @return string NULL
     */
    function getPulbicKeyByCertId($certId) {
        // 证书目录
        $cert_dir = $this->signCertPath;
        $handle = opendir ( $cert_dir );
        if ($handle) {
            while ( $file = readdir ( $handle ) ) {
                clearstatcache ();
                $filePath = $cert_dir . '/' . $file;
                if (is_file ( $filePath )) {
                    if (pathinfo ( $file, PATHINFO_EXTENSION ) == 'cer') {
                        if ($this->getCertIdByCerPath ( $filePath ) == $certId) {
                            closedir ( $handle );
                            return $this->getPublicKey ( $filePath );
                        }
                    }
                }
            }
        } else {
        }
        closedir ( $handle );
        return null;
    }

    /**
     * 取证书ID(.cer)
     *
     * @param unknown_type $cert_path           
     */
    function getCertIdByCerPath($cert_path) {
        $x509data = file_get_contents ( $cert_path );
        openssl_x509_read ( $x509data );
        $certdata = openssl_x509_parse ( $x509data );
        $cert_id = $certdata ['serialNumber'];
        return $cert_id;
    }
    /**
     * 取证书公钥 -验签
     *
     * @return string
     */
    function getPublicKey($cert_path) {
        return file_get_contents ( $cert_path );
    }

    /**
     * 讲数组转换为string
     *
     * @param $para 数组          
     * @param $sort 是否需要排序          
     * @param $encode 是否需要URL编码         
     * @return string
     */
    function createLinkString($para, $sort, $encode) {
        if($para == NULL || !is_array($para))
            return "";
        
        $linkString = "";
        if ($sort) {
            $para = $this->argSort ( $para );
        }
        while ( list ( $key, $value ) = each ( $para ) ) {
            if ($encode) {
                $value = urlencode ( $value );
            }
            $linkString .= $key . "=" . $value . "&";
        }
        // 去掉最后一个&字符
        $linkString = substr ( $linkString, 0, count ( $linkString ) - 2 );
        
        return $linkString;
    }
    /**
     * 对数组排序
     *
     * @param $para 排序前的数组
     *          return 排序后的数组
     */
    function argSort($para) {
        ksort ( $para );
        reset ( $para );
        return $para;
    }

}