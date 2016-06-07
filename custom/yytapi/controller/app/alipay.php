<?php

class yytapi_ctl_app_alipay {

  function __construct(){
    
  }

  /**
   * 测试支付宝支付
   * Time：2016/02/05 11:53:29
   * @author li
  */
  function dopay(){
    $alipay = kernel::single('ectools_payment_plugin_appalipay');
    $result = $alipay->dopay(array(
      'account_id'=>10,
      'account_id_to'=>9,
      'money'=>1,
      'point'=>10,
      'product_title'=>'欢乐兑通用积分',
      'product_demo'=>'欢乐兑通用积分10积分',
    ));
    dump($result);
    exit;
  }
  //支付宝回调
  function huidiao(){
    $alipay = kernel::single('ectools_payment_plugin_appalipay_server');
    $data='body="欢乐兑通用积分3000积分"&buyer_email="chencheng2046@126.com"&buyer_id="2088002724633580"&discount="0.00"&gmt_create="2016-02-23 14:41:12"&gmt_payment="2016-02-23 14:41:12"&is_total_fee_adjust="N"¬ify_id="e3f4691fcb0f21ad6771d34e3bfb89fkh6"¬ify_time="2016-02-23 14:41:12"¬ify_type="trade_status_sync"&out_trade_no="16022314413271814519"&payment_type="1"&price="0.01"&quantity="1"&seller_email="3059700799@qq.com"&seller_id="2088121706705157"&subject="欢乐兑通用积分"&total_fee="0.01"&trade_no="2016022321001004580271702972"&trade_status="TRADE_SUCCESS"&use_coupon="N"';
    $alipayKey_public="-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB
-----END PUBLIC KEY-----";
    $sign="O4+X+6LH/uEcQhKBpYeVSSEeGZy6y4IXEljbkTThGxB9ZP9cWdkWrprbGJswpwhb5bkO4erAbP2y/yKuIzWW2R6Dok7S5qgrXkZxG33jfnIrhdj8K3Y1w7rPXQlBsKidoW+i1hGk10aPCzxXDSomO7KN1UeS/VUXC9WMG3bwyNk=";

            $data = array
        (
            'discount' => '0.00',
            'payment_type' => '1',
            'subject' => '欢乐兑通用积分',
            'trade_no' => '2016022321001004580271702972',
            'buyer_email' => 'chencheng2046@126.com',
            'gmt_create' => '2016-02-23 14:41:12',
            'notify_type' => 'trade_status_sync',
            'quantity' => '1',
            'out_trade_no' => '16022314413271814519',
            'seller_id' => '2088121706705157',
            'notify_time' => '2016-02-23 14:41:12',
            'body' => '欢乐兑通用积分3000积分',
            'trade_status' => 'TRADE_SUCCESS',
            'is_total_fee_adjust' => 'N',
            'total_fee' => '0.01',
            'gmt_payment' => '2016-02-23 14:41:12',
            'seller_email' => '3059700799@qq.com',
            'price' => '0.01',
            'buyer_id' => '2088002724633580',
            'notify_id' => 'e3f4691fcb0f21ad6771d34e3bfb89fkh6',
            'use_coupon' => 'N'
            ,
        );
    $result = $alipay->getSignVeryfy($data,$sign);
    echo $result;
    exit;
  }

  function wxpay(){
    $alipay = kernel::single('ectools_payment_plugin_appwxpay');
    $result = $alipay->dopay(array(
      'account_id'=>10,
      'account_id_to'=>9,
      'money'=>1,
      'point'=>10,
      'product_title'=>'欢乐兑通用积分',
      'product_demo'=>'欢乐兑通用积分10积分',
    ));
    dump($result);
    exit;
  }
   //微信回调方法
  function huidiaowx(){
    $data='a:16:{s:5:"appid";s:18:"wx426b3015555a46be";s:9:"bank_type";s:3:"CFT";s:8:"cash_fee";s:1:"1";s:8:"fee_type";s:3:"CNY";s:12:"is_subscribe";s:1:"N";s:6:"mch_id";s:10:"1225312702";s:9:"nonce_str";s:32:"ok9rupt1jn18nj5jktvw8cnsy13dslvt";s:6:"openid";s:28:"oHZx6uDzOqb43-c-Ls45uY3c41_s";s:12:"out_trade_no";s:20:"16030313383149514539";s:11:"result_code";s:7:"SUCCESS";s:11:"return_code";s:7:"SUCCESS";s:4:"sign";s:32:"41E9AE22496FDC84E6E736CF2CEF2110";s:8:"time_end";s:14:"20160303133824";s:9:"total_fee";s:1:"1";s:10:"trade_type";s:3:"APP";s:14:"transaction_id";s:28:"1007480492201603033692840841";}';
    $ret=unserialize($data);
    $alipay = kernel::single('ectools_payment_plugin_appwxpay_server');
    $result = $alipay->callback($ret);
    dump($result);
    exit;
  }
  
  //查询微信订单
  function wxorder(){
    $alipay = kernel::single('ectools_payment_plugin_appwxpay_server');
    $result = $alipay->orderQuery(array(
      'out_trade_no'=>'16022513067950214510'
    ));
    dump($result);
    exit;
  }

  //微信的定时任务
  function wxtask(){
    $alipay = kernel::single('yytapi_task_agentPayOrder');
    $result = $alipay->exec();
    exit;
  }

  //银联
  function unionpay(){
    $alipay = kernel::single('ectools_payment_plugin_appunionpay');
    $result = $alipay->dopay(array(
      'account_id'=>10,
      'account_id_to'=>9,
      'money'=>1,
      'point'=>10,
      'product_title'=>'欢乐兑通用积分',
      'product_demo'=>'欢乐兑通用积分10积分',
    ));
    dump($result);
    exit;
  }

  //银联回调
  function huidiaoun(){
    $data="accessType=0&bizType=000201&certId=68759585097&currencyCode=156&encoding=utf-8&merId=777290058125790&orderId=16030815266872414597&queryId=201603081526343636648&respCode=00&respMsg=Success!&settleAmt=1&settleCurrencyCode=156&settleDate=0308&signMethod=01&traceNo=363664&traceTime=0308152634&txnAmt=1&txnSubType=01&txnTime=20160308152634&txnType=01&version=5.0.0&signature=IBGxXy2oybR89y9OyZ2qYCe474Dx26ga571VqR1rp447yImfARm69ZXdIcBNAtryVbXliSBHsU5fmIcqwtyWYy%2bHwH3q%2fpWXcbiD5YqwA8HPj5%2bOVMDfug%2bsZ1RhnZd924kulgWmmEek76ML2m20ydlpkwsrTGetkTbV4Nnaqq%2bd9088ukXFD5wk1FI5GYQtsGaqMYRJTZJlfAY19bwLJ23Rn8qQbU2N4mjuIiwELoQ7JEZPUlwtHU1eFohZeAFRHI5ByyL4Pn8m4rglYSXQTCNkZ8R76a1ZqFxMrx4kg06mgjq0c3Mgz2b7yCPYQ86iLVUUlzVOwYcsnjunRzjtww%3d%3d";
    $data = array(
        'accessType' =>'0',
        'bizType' => '000201',
        'certId' =>'68759585097',
        'currencyCode' => '156',
        'encoding' =>'utf-8',
        'merId' => '777290058125790',
        'orderId' =>'16030815266872414597',
        'queryId' => '201603081526343636648',
        'respCode' =>'00',
        'respMsg' =>'Success!',
        'settleAmt' => '1',
        'settleCurrencyCode' => '156',
        'settleDate' => '0308',
        'signMethod' => '01',
        'traceNo' => '363664',
        'traceTime' => '0308152634',
        'txnAmt' => '1',
        'txnSubType' => '01',
        'txnTime' => '20160308152634',
        'txnType' => '01',
        'version' => '5.0.0',
        'signature' => 'IBGxXy2oybR89y9OyZ2qYCe474Dx26ga571VqR1rp447yImfARm69ZXdIcBNAtryVbXliSBHsU5fmIcqwtyWYy%2bHwH3q%2fpWXcbiD5YqwA8HPj5%2bOVMDfug%2bsZ1RhnZd924kulgWmmEek76ML2m20ydlpkwsrTGetkTbV4Nnaqq%2bd9088ukXFD5wk1FI5GYQtsGaqMYRJTZJlfAY19bwLJ23Rn8qQbU2N4mjuIiwELoQ7JEZPUlwtHU1eFohZeAFRHI5ByyL4Pn8m4rglYSXQTCNkZ8R76a1ZqFxMrx4kg06mgjq0c3Mgz2b7yCPYQ86iLVUUlzVOwYcsnjunRzjtww%3d%3d',
      );
      $data['signature'] = urldecode($data['signature']);
      $alipay = kernel::single('ectools_payment_plugin_appunionpay_server');
      $result = $alipay->callback($data);
      dump($result);exit;
  }

  //查询银联订单
  function unorder(){
    $alipay = kernel::single('ectools_payment_plugin_appunionpay_server');
    $result = $alipay->orderQuery('16031014553525814597');
    dump($result);
    exit;
  }

  //测试计算汇总
  function tasks(){
    kernel::single('sysagent_tasks_settlement')->exec();
    dump('OK');
    exit;
  }

  //改代理商申请表数据
  function updateEnterapply(){
    $agent_apply = app::get('sysagent')->model('enterapply');
    $enter=$agent_apply->getlist('*');
    $agent=app::get('sysagent')->model('agents')->getlist('*');
    $ret=array();
    foreach ($enter as & $v) {
      $ret[$v['agent_id']]=$v;
    }
    foreach ($agent as & $value) {
      $temp=array();
      $temp=$ret[$value['agent_id']];
      switch ($value['status']) {
        case '0':
          $temp['apply_status'] = 'active';
          break;
        case '1':
          $temp['apply_status'] = 'successful';
          break;
        case '2':
          $temp['apply_status'] = 'failing';
          break;
        case '3':
          $temp['apply_status'] = 'lock';
          break;
      }
      $agent_apply -> update($temp,array('enterapply_id'=>$temp['enterapply_id']));
    }
    dump('ok');exit;
  }

  function urlLog(){
    $private_key_path="/public/urlKey/private_key.pem";
    $private_key_path=ROOT_DIR.$private_key_path;
    $private_key = file_get_contents($private_key_path);
    $pi_key = openssl_get_privatekey($private_key);
    $data="jiang123";
    $encrypted="";
    openssl_private_encrypt($data,$encrypted,$pi_key);
    $encrypted = base64_encode($encrypted);
    $encrypted = urlencode($encrypted);
    $user=urlencode('jiang');
    echo "<meta http-equiv=Content-Type content='text/html;charset=utf-8'>
          <a href='loginurl.html?user={$user}&poss={$encrypted}' target='_blank'>登录</a>";exit;
  }
  
}
