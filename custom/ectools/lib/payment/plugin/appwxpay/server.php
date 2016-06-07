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
class ectools_payment_plugin_appwxpay_server extends ectools_payment_app {

    var $key = 'b6b76e0Jv2bFf8be9cAbd2aGr0Fe6e4a';
    /**
     * @var 商户号
     */
    var $mchid = '1320088801';
    /**
     * @var 绑定支付的APPID
     */
    var $appid = 'wxba387d9a993dce78';


    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    public function callback(&$in)
    {
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $result = $this->fromXml($xml);
        $insign = $result['sign'];
        unset($result['sign']);
        file_put_contents('jiang_txt.txt',serialize($result['out_trade_no']).'+'.date('Y-m-d H:i:s').'+22/r/n',FILE_APPEND);
        if( $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' )
        {
          // file_put_contents('jiang_txt.txt',serialize($result['out_trade_no'].'+'.date('Y-m-d H:i:s').'+22'),FILE_APPEND);
            if( $insign == $this->makeSign($result))
            {
                $replyDate='';
                if($this->updateOrder($result)==true){
                    //该分支在成功回调到NotifyCallBack方法，处理完成之后流程
                    $replyDate['return_code'] = "SUCCESS";
                    $replyDate['return_msg'] = "OK";
                    $replyDate['is_sign'] = true;
                }else{
                    $replyDate['return_code'] = "FAIL";
                    $replyDate['return_msg'] = "OK";
                    $replyDate['is_sign'] = false;
                }
                $this->replyNotify($replyDate);
            }
        }

    }
    /**
     * 
     * 查询订单，WxPayOrderQuery中out_trade_no、transaction_id至少填一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param WxPayOrderQuery $inputObj
     * @param int $timeOut
     * @throws WxPayException
     * @return 成功时返回，其他抛异常
     */
    public function orderQuery($inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        //检测必填参数
        if(!$inputObj['out_trade_no'] && !$inputObj['transaction_id']) {
            return "订单查询接口中，out_trade_no、transaction_id至少填一个！";
        }
        $inputObj['appid'] = $this->appid;//公众账号ID
        $inputObj['mch_id'] = $this->mchid;//商户号
        $inputObj['nonce_str']= $this->getNonceStr();//随机字符串
        
        $inputObj['sign'] = $this->MakeSign($inputObj);//签名
        $xml = $this->toXml($inputObj);
        
        $startTimeStamp = $this->getMillisecond();//请求开始时间
        $response = kernel::single('base_httpclient')->post($url,$xml);
        $result = self::fromXml($response);
        // dump($result);exit;
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        if( $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' ){
            if($result['trade_state'] == 'SUCCESS'){
                $data = $this->updateOrder($result);
                return $data;
            }else{
              return 'strade_state!=SUCCESS';
            }
        }
        $str='return_code:'.$result['return_code'].',result_code:'.$result['result_code'];
        return $str;
    }
    
    //支付成功修改订单信息
    public function updateOrder($orderInfo){
        $objPayments = app::get('ectools')->model('payments_agent');
        //先判断该订单是否已经支付过了
        $payDate = $objPayments->getRow('*', array('payment_id'=>$orderInfo['out_trade_no']));
        if($payDate['status']!='succ'){
            $objMath = kernel::single('ectools_math');
            $money   = $objMath->number_multiple(array($orderInfo['total_fee'], 0.01));
            $ret=array(
                'payment_id' => $orderInfo['out_trade_no'],
                'trade_no' => $orderInfo['transaction_id'],
                'money' => $money,
                'modified_time' => time(),
                'status' => 'succ',
                'memo' => '',
                'account' => $orderInfo['openid'],
                'pay_account' => $orderInfo['appid'],
                'postStr' => json_encode($orderInfo),
            );
            $filter=array('payment_id'=>$orderInfo['out_trade_no']);
            try{
                $is_save = $objPayments->update($ret, $filter);
            }catch( Exception $e){
                return $e->getMessage();
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
                return $result;
            }
        }
        return true;
    }

    /**
     * 生成签名
     */
    public function makeSign($data)
    {
      //签名步骤一：按字典序排序参数
      ksort($data);
      $string = $this->toUrlParams($data);
      //签名步骤二：在string后加入KEY
      $string = $string . "&key=".$this->key;
      //签名步骤三：MD5加密
      $string = md5($string);
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
     * 
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public static function getNonceStr($length = 32) 
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {  
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
        } 
        return $str;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
    **/
    public function toXml($data)
    {
      if(!is_array($data) || count($data) <= 0)
      {
        throw new \LogicException("数组数据异常！");
      }
      
      $xml = "<xml>";
      foreach ($data as $key=>$val)
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
     * 获取毫秒级别的时间戳
     */
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


    /**
     * 
     * 回复通知
     * @param bool $needSign 是否需要签名输出
     */
    final private function replyNotify($replyDate)
    {
        //如果需要签名
        if($replyDate['is_sign'] == true && 
            $replyDate['return_code'] == "SUCCESS")
        {
            $replyDate['sign'] = $this->makeSign($replyDate);
        }
        echo $this->toXml($replyDate);
    }
}
