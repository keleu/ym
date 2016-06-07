<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 微信担保交易支付网关（国内）
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_appunionpay{

	/**
	 * @var string 支付方式名称
	 */
    public $name = '中国银联';

    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '中国银联支付接口';

     /**
     * @var string 支付方式key
     */
    public $app_key = 'appunionpay';

	/**
	 * @var string 中心化统一的key
	 */
	public $app_rpc_key = 'appunionpay';

	/**
	 * @var string 统一显示的名称
	 */
    public $display_name = '中国银联';

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
     * @var string 签名证书地址
     */
    var $signCertPath = '/public/unionpayCert/sign.pfx';
    
    /**
     * @var string 签名证书密码
     */
    var $signCertPwd = '000000';

    /**
     * @var string 验签证书地址
     */
    var $verifyCertPath = '/public/unionpayCert/verify_sign_acp.cer';

    /**
     * @var string 商户代码
     */
    var $merId = '777290058125790';

    /**
     * @var api访问地址
     */
    var $https_url = 'https://101.231.204.80:5000/gateway/api/appTransReq.do?';
    // var $https_url = 'https://gateway.95516.com/gateway/api/appTransReq.do?';

    /**
	 * 构造方法
	 * @param null
	 * @return boolean
	 */
    public function __construct(){
        // $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_appunionpay_server', 'callback');
        $this->notify_url = kernel::base_url(1).kernel::url_prefix().'/apppayaction.do/unionpay';

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

        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_appunionpay', 'callback');
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

        //路径处理
        $this->verifyCertPath = ROOT_DIR.$this->verifyCertPath;
        $this->signCertPath = ROOT_DIR.$this->signCertPath;
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
            'pay_type' => 'unionpay',
            'bank' => $this->display_name,
            'account' => $this->merId,
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
        
        //处理需要传递给微信的参数信息
        $data = array(
          'version' => '5.0.0',                 //版本号
          'encoding' => 'utf-8',                //编码方式
          'txnType' => '01',                    //交易类型
          'txnSubType' => '01',                 //交易子类
          'certId' => $this->getSignCertId(), //证书ID
          'bizType' => '000201',                //业务类型
          'backUrl' => $this->notify_url,       //后台通知地址
          'signMethod' => '01',                 //签名方法
          'channelType' => '08',                //渠道类型，07-PC，08-手机
          'accessType' => '0',                  //接入类型
          'currencyCode' => '156',               //交易币种，境内商户固定156
          'merId' => $this->merId,
          'orderId' => $paymentId,
          'txnTime' =>date('YmdHis'),
          'txnAmt' => bcmul($payment['money'],100,0), //交易金额，单位分
        );

        //签名
        // dump($data);
        self::sign ( $data );
        // dump($data);exit;
        $result_arr = $this->post($data , $this->https_url);
        // dump($result_arr);exit;

        if(count($result_arr)<=0) { //没收到200应答的情况
          return false;
        }

        //验签
        if(!$this->verify($result_arr)){
          return false;
        }

        //验签结果返回
        if ($result_arr["respCode"] == "00"){
            $data=array(
                'prepayid' => $result_arr['tn'],
                'orderid' => $paymentId,
              );
            return $data;
        } else {
            return false;
        }
    }

    /**
   * 签名
   * @param req 请求要素
   * @param resp 应答要素
   * @return 是否成功
   */
  function sign(&$params) {
    if(isset($params['signature'])){
      unset($params['signature']);
    }
    // 转换成key=val&串
    // ksort($params);
    $params_str = $this->createLinkString( $params, true, false );
    // echo $params_str;exit;
    
    $params_sha1x16 = sha1 ( $params_str, FALSE );
    
    $private_key = $this->getPrivateKey();
    // echo $private_key;exit;
    // 签名
    $sign_falg = openssl_sign( $params_sha1x16, $signature, $private_key, OPENSSL_ALGO_SHA1 );
    if ($sign_falg) {
      $signature_base64 = base64_encode ( $signature );
      $params ['signature'] = $signature_base64;
    } else {
      throw new \LogicException(app::get('ectools')->_("签名失败"));
    }
  }

  /**
   * 取证书ID(.pfx)
   *
   * @return unknown
   */
  function getSignCertId() {
    $pkcs12certdata = file_get_contents ( $this->signCertPath );
    openssl_pkcs12_read ( $pkcs12certdata, $certs, $this->signCertPwd );
    $x509data = $certs ['cert'];
    openssl_x509_read ( $x509data );
    $certdata = openssl_x509_parse ( $x509data );
    $cert_id = $certdata ['serialNumber'];
    return $cert_id;
  }

  /**
   * 后台交易 HttpClient通信
   *
   * @param unknown_type $params          
   * @param unknown_type $url         
   * @return mixed
   */
   function post($params, $url) {
    $opts = $this->createLinkString( $params, false, true );
    // echo $url;exit;
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false ); // 不验证证书
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false ); // 不验证HOST
    curl_setopt( $ch, CURLOPT_SSLVERSION, 1 );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array (
        'Content-type:application/x-www-form-urlencoded;charset=UTF-8' 
    ));
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $opts );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec( $ch );
    // echo $html;exit;
    if(curl_errno($ch)){
      $errmsg = curl_error($ch);
      curl_close ($ch);
      throw new \LogicException(app::get('ectools')->_("请求失败，报错信息>" . $errmsg));
      return null;
    }
    if( curl_getinfo($ch, CURLINFO_HTTP_CODE) != "200"){
      $errmsg = "http状态=" . curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close( $ch );
      throw new \LogicException(app::get('ectools')->_("请求失败，报错信息>" . $errmsg));
      return null;
    }
    curl_close ( $ch );
    $result_arr = $this->parseQString($html);
    return $result_arr;
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
   * key1=value1&key2=value2转array
   * @param $str key1=value1&key2=value2的字符串
   * @param $$needUrlDecode 是否需要解url编码，默认不需要
   */
  function parseQString($str, $needUrlDecode=false){
    $result = array();
    $len = strlen($str);
    $temp = "";
    $curChar = "";
    $key = "";
    $isKey = true;
    $isOpen = false;
    $openName = "\0";

    for($i=0; $i<$len; $i++){
      $curChar = $str[$i];
      if($isOpen){
        if( $curChar == $openName){
          $isOpen = false;
        }
        $temp = $temp . $curChar;
      } elseif ($curChar == "{"){
        $isOpen = true;
        $openName = "}";
        $temp = $temp . $curChar;
      } elseif ($curChar == "["){
        $isOpen = true;
        $openName = "]";
        $temp = $temp . $curChar;
      } elseif ($isKey && $curChar == "="){
        $key = $temp;
        $temp = "";
        $isKey = false;
      } elseif ( $curChar == "&" && !$isOpen){
        $this->putKeyValueToDictionary($temp, $isKey, $key, $result, $needUrlDecode);
        $temp = "";
        $isKey = true;
      } else {
        $temp = $temp . $curChar;
      }
    }
    $this->putKeyValueToDictionary($temp, $isKey, $key, $result, $needUrlDecode);
    return $result;
  }


  function putKeyValueToDictionary($temp, $isKey, $key, &$result, $needUrlDecode) {
    if ($isKey) {
      $key = $temp;
      if (strlen ( $key ) == 0) {
        return false;
      }
      $result [$key] = "";
    } else {
      if (strlen ( $key ) == 0) {
        return false;
      }
      if ($needUrlDecode)
        $result [$key] = urldecode ( $temp );
      else
        $result [$key] = $temp;
    }
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

  /**
   * 验签
   *
   * @param String $params_str          
   * @param String $signature_str         
   */
  function verify($params) {
    // 公钥
    $public_key = $this->getPulbicKeyByCertId ( $params['certId'] );  

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
    // echo $certId;exit;
    if ($this->getCertIdByCerPath ( $this->verifyCertPath ) == $certId) {
      return file_get_contents ( $this->verifyCertPath );
    }else{
      throw new \LogicException(app::get('ectools')->_("验签失败"));
    }
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
    // echo $cert_id;exit;
    return $cert_id;
  }

  /**
   * 返回(签名)证书私钥 
   * @return unknown
   */
  function getPrivateKey() {
    $pkcs12 = file_get_contents( $this->signCertPath );

    openssl_pkcs12_read( $pkcs12, $certs, $this->signCertPwd );
    // dump($certs);exit;
    return $certs ['pkey'];
  }
}
