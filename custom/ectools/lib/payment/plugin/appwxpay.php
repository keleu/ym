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
final class ectools_payment_plugin_appwxpay{

	/**
	 * @var string 支付方式名称
	 */
    public $name = '微信支付';

    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '微信支付接口';

     /**
     * @var string 支付方式key
     */
    public $app_key = 'appweixin';

	/**
	 * @var string 中心化统一的key
	 */
	public $app_rpc_key = 'appweixin';

	/**
	 * @var string 统一显示的名称
	 */
    public $display_name = '微信';

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
     * @var 绑定支付的APPID
     */
    var $appid = 'wxba387d9a993dce78';//'wx426b3015555a46be';
    
    /**
     * @var 公众帐号secert
     */
    var $appsecret = 'feba083d31fab3aa759a42e3a54b7260';//'01c6d59a3f9024db6336662ac95c8e74';

    /**
     * @var 商户号
     */
    var $mchid = '1320088801';//'1225312702';

    /**
     * @var 商户支付密钥
     */
    var $key_pay = 'b6b76e0Jv2bFf8be9cAbd2aGr0Fe6e4a';//'e10adc3949ba59abbe56e057f20f883e';

    /**
     * @var api访问地址
     */
    var $https_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    //全局变来那个
    var $values = [];

    /**
	 * 构造方法
	 * @param null
	 * @return boolean
	 */
    public function __construct(){
        // $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_appwxpay_server', 'callback');
      $this->notify_url = kernel::base_url(1).kernel::url_prefix().'/apppayaction.do/weixinpay';

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

        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_appwxpay', 'callback');
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
            'pay_type' => 'weixinpay',
            'pay_name' => $this->name,
            'bank' => $this->display_name,
            'account' => $this->mchid,
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
          'appid'           =>    strval($this->appid),
          'mch_id'          =>    strval($this->mchid),
          'nonce_str'       =>    strval(self::getNonceStr()),
          'body'            =>    strval($payment['product_title']),
          'detail'          =>    strval($payment['product_demo']),
          'out_trade_no'    =>    strval($paymentId),
          'total_fee'       =>    bcmul($payment['money'],100,0),
          'spbill_create_ip'=>    strval($_SERVER['REMOTE_ADDR']),
          'notify_url'      =>    strval($this->notify_url),
          'trade_type'      =>    'APP',
          'time_start'      =>    strval(date("YmdHis")),
          'time_expire'     =>    strval(date("YmdHis", time() + 600)),
        );

        //有效性验证
        if(!$data['body']){
          throw new \LogicException(app::get('ectools')->_("商品名称不存在"));
        }elseif(!$data['total_fee'] || $data['total_fee']<=0){
          throw new \LogicException(app::get('ectools')->_("金额不合法"));
        }

        //全局变量中
        $this->values = $data;
        // dump($this->values);exit;
        
        //签名
        $sign = $this->setSign();

        //xml字符串获取
        $xml = $this->toXml();
        // dump($xml);exit;

        $startTimeStamp = self::getMillisecond();//请求开始时间

        $response = kernel::single('base_httpclient')->post($this->https_url,$xml);
        // dump($response);exit;
        $result = self::fromXml($response);
        
        self::reportCostTime($this->https_url, $startTimeStamp, $result);//上报请求花费时间
        $data=array(
          'prepayid' => $result['prepay_id'],
          'orderid' => $paymentId,
        );
        return $data;
    }
    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    function callback(&$in){
        $mch_id     = '';
        $key        = '';
        $in = $in['weixin_postdata'];
        $insign = $in['sign'];
        unset($in['sign']);
        if( $in['return_code'] == 'SUCCESS' && $in['result_code'] == 'SUCCESS' )
        {
            if( $insign == $this->makeSign($in))
            {
                $objMath = kernel::single('ectools_math');
                $money   = $objMath->number_multiple(array($in['total_fee'], 0.01));
            }
        }
    }
    /**
    * 设置签名，详见签名生成算法
    * @param string $value 
    **/
    public function setSign()
    {
      $sign = $this->makeSign($this->values);
      $this->values['sign'] = $sign;
      return $sign;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function init($xml)
    { 
        $this->fromXml($xml);
        if($this->values['return_code'] != 'SUCCESS'){
           return $this->values;
        }
        $this->checkSign();
        return $this->values;
    }

    /**
     * 
     * 检测签名
     */
    public function checkSign()
    {
      //fix异常
      if(!$this->isSignSet()){
        throw new \LogicException("签名错误！");
      }
      
      $sign = $this->makeSign($this->values);
      if($this->values['sign'] == $sign){
        return true;
      }
      throw new \LogicException("签名错误！");
    }

    /**
     * 
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32) 
    {
      $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
      $str ="";
      for ( $i = 0; $i < $length; $i++ )  {  
        $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
      } 
      return $str;
    }

    /**
     * 生成签名
     */
    public function makeSign($data)
    {
      //签名步骤一：按字典序排序参数
      ksort($data);
      // dump($data);exit;
      $string = $this->toUrlParams($data);

      //签名步骤二：在string后加入KEY
      $string = $string . "&key=".$this->key_pay;
      // echo $string;exit;
      //签名步骤三：MD5加密
      $string = md5($string);
      // echo $string;exit;
      //签名步骤四：所有字符转为大写
      $result = strtoupper($string);
      return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function toUrlParams($data)
    {
      $buff = "";
      foreach ($data as $k => $v)
      {
        if($k != "sign" && $v != "" && !is_array($v)){
          $buff .= $k . "=" . $v . "&";
        }
      }
      
      $buff = trim($buff, "&");
      return $buff;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
    **/
    public function toXml($data)
    {
      if(!is_array($this->values) || count($this->values) <= 0)
      {
        throw new \LogicException("数组数据异常！");
      }
      
      $xml = "<xml>";
      foreach ($this->values as $key=>$val)
      {
        if (is_numeric($val)){
          $xml.="<".$key.">".$val."</".$key.">";
        }else{
          $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
      }
      $xml.="</xml>";
      return $xml; 
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function fromXml($xml)
    { 
      if(!$xml){
        throw new \LogicException("xml数据异常！");
      }
      //将XML转为array
      //禁止引用外部xml实体
      libxml_disable_entity_loader(true);
      $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);    
      return $result;
    }

    //毫秒级时间
    private static function getMillisecond()
    {
      //获取毫秒的时间戳
      $time = explode ( " ", microtime () );
      $time = $time[1] . ($time[0] * 1000);
      $time2 = explode( ".", $time );
      $time = $time2[0];
      return $time;
    }

    /**
    * 判断签名，详见签名生成算法是否存在
    * @return true 或 false
    **/
    public function isSignSet($data)
    {
      return array_key_exists('sign', $data);
    }

    /**
     * 
     * 上报数据， 上报的时候将屏蔽所有异常流程
     * @param string $usrl
     * @param int $startTimeStamp
     * @param array $data
     */
    private static function reportCostTime($url, $startTimeStamp, $data)
    {
      
    }
}
