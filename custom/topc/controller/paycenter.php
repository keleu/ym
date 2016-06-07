<?php
class topc_ctl_paycenter extends topc_controller{
    public function __construct($app)
    {
        parent::__construct();
        $this->setLayoutFlag('paycenter');
        // 检测是否登录
    }

    public function index()
    {
        $filter = input::get();
        if(isset($filter['tid']) && $filter['tid'])
        {
            $pagedata['payment_type'] = "offline";
            $ordersMoney = app::get('topc')->rpcCall('trade.money.get',array('tid'=>$filter['tid']),'buyer');

            if($ordersMoney)
            {
                foreach($ordersMoney as $key=>$val)
                {
                    $newOrders[$val['tid']] = $val['payment'];
                    $newMoney += $val['payment'];
                    $payment_integral += $val['payment_integral'];
                }
                $paymentBill['money'] = $newMoney;
                $paymentBill['cur_money'] = $newMoney;
                $paymentBill['payment_integral'] = $payment_integral;
                //2016-3-30 by jianghui 添加积分+现金的总计
                $paymentBill['payment_blend'] = kernel::single('sysitem_blendShow')->totalshow($payment_integral,$newMoney);
            }
            $pagedata['trades'] = $paymentBill;
            $pagedata['payment_type'] = "offline";
            $pagedata['mainfile'] = "topc/payment/payment.html";
            return $this->page('topc/payment/index.html', $pagedata);
        }

        if($filter['newtrade'])
        {
            $newtrade = $filter['newtrade'];
            unset($filter['newtrade']);
        }

        if($filter['merge'])
        {
            $ifmerge = $filter['merge'];
            unset($filter['merge']);
        }

        //获取可用的支付方式列表
        $filter['fields'] = "*";
        $paymentBill = app::get('topc')->rpcCall('payment.bill.get',$filter,'buyer');
        if($paymentBill['status'] == "succ")
        {
            return $this->finish(['payment_id'=>$paymentBill['payment_id']]);
        }

        //检测订单中的金额是否和支付金额一致 及更新支付金额
        $trade = $paymentBill['trade'];
        $tids['tid'] = implode(',',array_keys($trade));
        $ordersMoney = app::get('topc')->rpcCall('trade.money.get',$tids,'buyer');

        if($ordersMoney)
        {
            foreach($ordersMoney as $key=>$val)
            {
                $newOrders[$val['tid']] = $val['payment'];
                $newMoney += $val['payment'];
            }
            $result = array(
                'trade_own_money' => json_encode($newOrders),
                'money' => $newMoney,
                'cur_money' => $newMoney,
                'payment_id' => $filter['payment_id'],
            );

            if($newMoney != $paymentBill['cur_money'])
            {
                try{
                    app::get('topc')->rpcCall('payment.money.update',$result);
                }
                catch(Exception $e)
                {
                    $msg = $e->getMessage();
                    $url = url::action('topc_ctl_member_trade@tradeList');
                    return $this->splash('error',$url,$msg,true);
                }
                $paymentBill['money'] = $newMoney;
                $paymentBill['cur_money'] = $newMoney;
            }
        }

        $payType['platform'] = 'ispc';
        $payments = app::get('topc')->rpcCall('payment.get.list',$payType,'buyer');
        $pagedata['tids'] = $tids['tid'];
        $pagedata['trades'] = $paymentBill;
        $pagedata['payments'] = $payments;
        $pagedata['newtrade'] = $newtrade;
        $pagedata['mainfile'] = "topc/payment/payment.html";
        //2015-11-20 查找用户的积分余额
        $userId = userAuth::id();
        $userMdlAddr = app::get('sysuser')->model('user_points');
        $filter['user_id'] = $userId;
        $result = $userMdlAddr->getRow('*', $filter);
        $pagedata['point_count']=$result['point_count']?$result['point_count']:0;
        //2016-3-30 by jianghui 添加积分+现金的总计
        $pagedata['trades']['money_blend'] = kernel::single('sysitem_blendShow')->totalshow($pagedata['trades']['money_integral'],$pagedata['trades']['cur_money']);
        return $this->page('topc/payment/index.html', $pagedata);
    }

    public function createPay()
    {
        $filter = input::get();
        $filter['user_id'] = userAuth::id();
        $filter['user_name'] = userAuth::getLoginName();
        if($filter['merge'])
        {
            $ifmerge = $filter['merge'];
            unset($filter['merge']);
        }

        try
        {
            $paymentId = kernel::single('topc_payment')->getPaymentId($filter);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topc_ctl_member_trade@tradeList');
            echo '<meta charset="utf-8"><script>alert("'.$msg.'");location.href="'.$url.'";</script>';
            exit;
        }
        $url = url::action('topc_ctl_paycenter@index',array('payment_id'=>$paymentId,'merge'=>$ifmerge));
        return $this->splash('success',$url,$msg,true);
    }

    public function dopayment()
    {
        $postdata = input::get();
        //2015-11-21 by jiang 如果是积分支付  则 pay_app_id=integral
        $payment = $postdata['payment'];
        $payment['user_id'] = userAuth::id();
        $payment['platform'] = "pc";
        try
        {
            if($payment['pay_app_id']=='integral'){
                $this->_doPayment($payment);
            }
            else{
                app::get('topc')->rpcCall('payment.trade.pay',$payment);
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            echo '<meta charset="utf-8"><script>alert("'.$msg.'"); window.close();</script>';
            exit;
        }
        
        $url = url::action('topc_ctl_paycenter@finish',array('payment_id'=>$payment['payment_id']));
        if($payment['pay_app_id']=='integral'){
            return $this->splash('success',$url);
        }
        else{
            return $this->splash('success',$url,$msg,true);
        }
    }

    public function finish($postdata = array())
    {
        if(!$postdata)
        {
            $postdata = input::get();
        }
        try
        {
            $params['payment_id'] = $postdata['payment_id'];
            $params['fields'] = 'payment_id,status,pay_app_id,pay_name,money,cur_money,money_integral';
            $result = app::get('topc')->rpcCall('payment.bill.get',$params);

            //获取会员信息：发送积分购买消息到微信
            if($result['money_integral']!=0){
                $userId = userAuth::id();
                kernel::single('weixin_base')->jfCound_change($userId,'积分购买商品支付',(int)(-1*$result['money_integral']));
            }

            //2016-3-30 by jianghui 添加积分+现金总计
            $result['money_blend'] = kernel::single('sysitem_blendShow')->totalshow($result['money_integral'],$result['money']);
            
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
        }
        $result['num'] = count($result['trade']);
        $pagedata['msg'] = $msg;
        $pagedata['payment'] = $result;
        $pagedata['mainfile'] = "topc/payment/finish.html";
        return $this->page('topc/payment/index.html', $pagedata);
    }
    function _doPayment($params)
    {
        if(!$params['platform'])
        {
            $params['platform'] = "pc";
        }

        $objMdlPayments = app::get('ectools')->model('payments');
        $objMdlPayBill = app::get('ectools')->model('trade_paybill');
        $objMdlUser = app::get('sysuser')->model('user');
        $objMdlPoint = app::get('sysuser')->model('user_point');
        $paymentBill = $objMdlPayments->getRow('payment_id,status,money,pay_type,currency,cur_money,pay_app_id',array('payment_id'=>$params['payment_id']));
        if($paymentBill['status'] == 'succ' || $paymentBill['status'] == 'progress')
        {
            throw new Exception('该订单已经支付');
        }
        $tradePayBill = $objMdlPayBill->getList('tid',array('payment_id'=>$params['payment_id']));
        $payTids = array_bind_key($tradePayBill,'tid');
        $tids['tid'] = $params['tids'];
        $tids['fields'] = "payment,tid,status,order.title,payment_integral";
        $trades = app::get('ectools')->rpcCall('trade.get.list',$tids);
        $totalMoney = array_sum(array_column($trades['list'],'payment'));
        $totalIntegral = array_sum(array_column($trades['list'],'payment_integral'));
        if($totalMoney != $params['money'])
        {
            throw new Exception('订单金额与需要支付金额不一致，请核对后支付');
        }
        if($totalIntegral != $params['money_integral'])
        {
            throw new Exception('订单积分与需要支付积分不一致，请核对后支付');
        }
        $db = app::get('sysaftersales')->database();
        $db->beginTransaction();

        try{
            $return_url = array("topc_ctl_paycenter@finish",array('payment_id'=>$params['payment_id']));
            if($params['platform'] == "wap")
            {
                $return_url = array("topm_ctl_paycenter@finish",array('payment_id'=>$params['payment_id']));
            }
            //添加订单信息
            $params['list']=$trades['list'];
            //by zhangyan 日期实例化类，调用为了能让app调用到处理积分
            topc_ctl_paycenter::_doPoint($params);

            $paymentData = array(
                'money' => $params['money'],
                'money_integral' => $params['money_integral'],
                'cur_money' => $params['money'],
                'status' => 'succ',
                'pay_app_id' => $params['pay_app_id'],
                'return_url' => $return_url,
                'pay_name' => '积分',
                'pay_type' => 'online',
            );
            $paymentFilter['payment_id'] = $params['payment_id'];
            $result = $objMdlPayments->update($paymentData,$paymentFilter);
            if(!$result)
            {
                throw new Exception('支付失败，支付单更新失败');
            }

            foreach($trades['list'] as $val)
            {
                $data['payment'] = $val['payment'];
                $data['payment_integral'] = $val['payment_integral'];
                $data['status'] = 'succ';
                $data['modified_time'] = time();
                $data['payed_time'] = time();
                $filter['tid'] = $val['tid'];
                $filter['payment_id'] = $params['payment_id'];
                $result = $objMdlPayBill->update($data,$filter);
                $objMdlPayments->update($data,$filter);
                $params['item_title'][] = $val['order'][0]['title'];
                if(!$result)
                {
                    throw new Exception('支付失败，支付单明细更新失败');
                }
                if($payTids[$val['tid']])
                {
                    unset($payTids[$val['tid']]);
                }
            }

            if($payTids)
            {
                $deleteParams['tid'] = array_keys($payTids);
                $deleteParams['payment_id'] = $params['payment_id'];
                $result = $objMdlPayBill->delete($deleteParams);
                if(!$result)
                {
                    throw new Exception('支付失败，清除过期数据失败');
                }
            }

            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollback();
            throw $e;
        }
    }

    /**
    * ps ：处理积分
    * Time：2015/11/21 16:05:45
    * @author jiang
    */
    function _doPoint($params){
        //会员扣除积分
        $objMdlTrade = app::get('systrade')->model('trade');
        $objMdlPayments = app::get('ectools')->model('payments');

        $objRefund = kernel::single('sysuser_data_point');
        $params['behavior']='订单支付';
        $result = $objRefund->update($params);

        //改变订单状态
        // $tradeDate=array(
        //     'tid'=>$params['tids'],
        //     'status'=>'WAIT_SELLER_SEND_GOODS',
        //     'pay_type'=>'online',
        //     'payed_fee'=>$params['money'],
        //     'pay_time'=>time()
        // );
        // $objMdlTrade->save($tradeDate);

        //处理订单
        foreach ($params['list'] as & $v) {
           app::get('ectools')->rpcCall('trade.pay.finish',array('tid'=>$v['tid'],'payment'=>$v['payment'],'payment_integral'=>$v['payment_integral']));

            //将支付方式改为online
            $tradeDate=array(
                'tid'=>$v['tid'],
                'pay_type'=>'online',
            );
            $objMdlTrade->save($tradeDate);
        }
        
    }    
}


