<?php
/*********************************************************************\
*  Copyright (c) 1998-2013, TH. All Rights Reserved.
*  Author :liuxin
*  FName  :offline.php
*  Time   :2014/08/30 09:18:48
*  Remark :商家后台门店相关功能
\*********************************************************************/
class topshop_ctl_trade_offline extends topshop_controller{

    public function addtrade(){
        $this->contentHeaderTitle = app::get('topshop')->_('新增门店订单');
        return $this->page('topshop/offline/addtrade.html');
    }

    /**
     * ps ：根据商品编号动态加载商品信息
     * Time：2015/12/22 16:29:20
     * @author liuxin
    */
    public function getItems(){
        $model_sku = app::get('sysitem')->model('sku');
        $data = $model_sku->getRow('*',array('bn'=>$_POST['bn'],));
        $params = array(
            'item_id'=>$data['item_id'],
            'shop_id'=>$this->shopId,
            'fields'=>'*'
        );
        try{
            $itemData = app::get('topshop')->rpcCall('item.get',$params);
        }
        catch(Exception $e){
            return 'fail';
        }
        if(count($itemData) == 0){
            return 'error:000';
        }
        if($itemData['approve_status']!='onsale'){
             return 'error:001';
        }
        //默认选中
        if($data['integral']>0){
            $data['is_select'] = 0;
            $data['item_integral'] = $data['integral'];
            $data['item_price'] = 0;
        }else if($data['price']>0){
            $data['is_select'] = 2;
            $data['item_integral'] = 0;
            $data['item_price'] = $data['price'];
        }else{
            $data['is_select'] = 1;
            $data['item_integral'] = $data['blend_integral'];
            $data['item_price'] = $data['blend_price'];
        }

        $pagedata = array(
            'sku_id'=>$data['sku_id'],
            'item_title'=>$itemData['title'],
            'image_src'=>$itemData['image_default_id'],
            'spec_info'=>$data['spec_info'],
            'price'=>$data['price'],
            'integral'=>$data['integral'],
            'blend'=>kernel::single('sysitem_blendShow')->show($data['blend_integral'],$data['blend_price']),
            'item_id'=>$data['item_id'],
            'is_select'=>$data['is_select'],
            'item_integral'=>$data['item_integral'],
            'item_price'=>$data['item_price'],
        );
        return view::make('topshop/offline/item.html',$pagedata);
    }
    /**
    * ps ：付款
    * Time：2015/12/22 14:04:15
    * @author jiang
    */
    function doPayment(){
        $payment=input::get();
        //计算金额和总数量
        $tval=$this->_tval($payment);
        $db = app::get('sysaftersales')->database();
        try
        {
            $db->beginTransaction();
            //判断商品和会员是否有
            if(empty($payment['user_id']) && (!empty($tval['total_fee'])||!empty($tval['total_integral']))){
                throw new \LogicException(app::get('systrade')->_('会员不存在或者应收金额和积分不能全为0'));
            }
            //验证会员支付码
            $key = 'VCODE_PAYMENT:'.$payment['user_id'].'pay';
            $vcode = cache::store('vcode')->get($key);
            if(!($vcode == $payment['user_vcode']&&($newCode = $this->deleteVcode($payment['user_id'])))){
                throw new \LogicException(app::get('systrade')->_('付款码已过期，请重新扫描！'));
            }
            $objMdlTrade = app::get('systrade')->model('trade');
            //查找会员信息
            $objMdlUser = app::get('sysuser')->model('user_addrs');
            $userDate = $objMdlUser->getRow('*',array('user_id'=>$payment['user_id']));
            // 格式化订单数据，库存修改
            $orderDate = $this->_chgdata($payment,$tval,$userDate);
            if (!$orderDate)
            {
                return false;
            }
            //支付单创建
            $this->_createBill($orderDate);
            // 保存订单数据
            $flag = $objMdlTrade->save($orderDate, $msg);
            if(!$flag)
            {
                throw new \LogicException(app::get('systrade')->_('订单生成失败'));
            }
            //处理积分
            $objRefund = kernel::single('sysuser_data_point');
            $tradeInfo['behavior']='订单支付';
            $tradeInfo['money_integral']=$orderDate['payment_integral'];
            $tradeInfo['tids']=$orderDate['tid'];
            $tradeInfo['user_id']=$orderDate['user_id'];
            $result = $objRefund->update($tradeInfo);
            //确认订单
            app::get('topc')->rpcCall('trade.confirm',$orderDate,'buyer');
            $db->commit();
            
            //发送微信消息
            kernel::single('weixin_base')->jfCound_change(
                $tradeInfo['user_id'],
                '门店支付',
                (int)($tradeInfo['money_integral']*-1)
            );

            //如果只有现金消费 不需要发短信
            if($tval['total_fee']>0 && $tval['total_integral']=='0'){
                $url = url::action('topshop_ctl_trade_offline@addtrade');
                $msg = app::get('topshop')->_('付款成功');
                return $this->splash('success', $url, $msg, true);
            }else{
                $user_change = app::get('sysuser')->getConf('sysuser_setting.user_change')  == 0 ?app::get('sysuser')->getConf('sysuser_setting.user_change'):'1';

                if((string)$user_change == '1'){
                    //发送短信
                    $this->sendSms($tradeInfo['user_id'],$tradeInfo['money_integral']);
                }

                $url = url::action('topshop_ctl_trade_offline@addtrade');
                $msg = app::get('topshop')->_('付款成功');
                return $this->splash('success', $url, $msg, true);
            }
            
        }
        catch(Exception $e)
        {
            $db->rollback();
            return $this->splash('error', '', $e->getMessage(), true);
        }
    }

    /**
     * ps ：验证会员支付码
     * Time：2015/12/22 16:30:18
     * @author liuxin
     * @return json
    */
    public function checkVcode($payment){
        $key = 'VCODE_PAYMENT:'.$_GET['userId'].'pay';
        $vcode = cache::store('vcode')->get($key);
        if($vcode['vcode'] == $_GET['vcode']&&($newCode = $this->deleteVcode($_GET['userId']))){
            $user_info = app::get('sysuser')->model('user')->getRow('*',array('user_id'=>$_GET['userId']));
            $user_account = app::get('sysuser')->model('account')->getRow('*',array('user_id'=>$_GET['userId']));
            $point = $user_info['point'];
            if($point >= $_GET['point']){
                return json_encode(array('success'=>true,'userId'=>$_GET['userId'],'login_account'=>$user_account['login_account'],'point_account'=>'足够','point'=>$point,'vcode'=>$newCode));
            }
            else{
                return json_encode(array('success'=>true,'userId'=>$_GET['userId'],'login_account'=>$user_account['login_account'],'point_account'=>'不足','point'=>$point,'vcode'=>$newCode));
            }
        }
        else{
            $msg = "无效的支付码！";
        }
        return json_encode(array('success'=>false,'msg'=>$msg));
    }

    /**
     * ps ：删除验证码（非物理删除，重新生成一个新的验证码），应在支付完成时调用
     * Time：2015/12/09 15:55:14
     * @author liuxin
     * @param int
     * @return bool
    */
    public function deleteVcode($userId){
        $key = 'VCODE_PAYMENT:'.$userId.'pay';
        $ttl = 3;
        $vcode = $this->create_guid();
        cache::store('vcode')->put($key, $vcode, $ttl);
        return $vcode;
    }

    /**
     * ps ：生成唯一码
     * Time：2015/12/14 14:21:55
     * @author liuxin
    */
    public function create_guid() {
        if (function_exists ( 'com_create_guid' )) {
            return com_create_guid ();
        } else {
            mt_srand ( ( double ) microtime () * 10000 ); //optional for php 4.2.0 and up.随便数播种，4.2.0以后不需要了。
            $charid = strtoupper ( md5 ( uniqid ( rand (), true ) ) ); //根据当前时间（微秒计）生成唯一id.
            $hyphen = chr ( 45 ); // "-"
            $uuid = '' . //chr(123)// "{"
            substr ( $charid, 0, 8 ) . $hyphen . substr ( $charid, 8, 4 ) . $hyphen . substr ( $charid, 12, 4 ) . $hyphen . substr ( $charid, 16, 4 ) . $hyphen . substr ( $charid, 20, 12 );
            //.chr(125);// "}"
            return $uuid;
        }
    }

	function _tval($payment){
        foreach ($payment['item_amount'] as $key => & $value) {
            $tavl['itemnum']+=$value;
            $tavl['total_fee']+=$payment['total_fee'][$key];
            $tavl['total_integral']+=$payment['total_integral'][$key];
        }
        return $tavl;
    }

    /**
    * ps ：格式化订单 改变库存
    * Time：2015/12/23 13:18:11
    * @author jiang
    */
    function _chgdata($payment,$tval,$userDate){
        $model_sku = app::get('sysitem')->model('sku');
        $objLibCatServiceRate = kernel::single('sysshop_data_cat');
        $tradeBaseTime = date('ymdHi');
        $tradeBaseRandNum = rand(0,49);//str_pad($tradeBaseRandNum,2,'0',STR_PAD_LEFT);
        $tradeModUserId = str_pad($payment['user_id']%10000,4,'0',STR_PAD_LEFT);
        $tid = $tradeBaseTime.str_pad(++$tradeBaseRandNum,2,'0',STR_PAD_LEFT).$tradeModUserId;
        $now = time();
        $order_data = array(
            'user_id'           => $payment['user_id'],
            'user_name'           => $userDate['name'],
            'shop_id'           => $this->shopId,
            'tid'               => $tid,
            'status'            => 'WAIT_BUYER_CONFIRM_GOODS',
            'created_time'      => $now,
            'modified_time'     => $now,
            'trade_memo'        => '',
            'ip'                => $_SERVER['REMOTE_ADDR'],
            'title'             => app::get('systrade')->_('订单明细介绍'),
            'itemnum'           => $tval['itemnum'],
            'dlytmpl_id'        => 0,
            'ziti_addr'        => '',
            'total_weight'      => 0,
            'pay_type'          => 'online',
            'need_invoice'      => 0,
            'trade_from'        => 'pc',
            'invoice_name'      => '',
            'invoice_main'      => '',
            'invoice_type'      => '',
            'receiver_name'     => '',
            'receiver_address'  => '',
            'receiver_zip'      => '',
            'receiver_tel'      => '',
            'receiver_mobile'   => '',
            'receiver_state'    => '',
            'receiver_city'     => '',
            'receiver_district' => '',
            'buyer_area'        => '',
            'payment'           => $tval['total_fee'],
            'total_fee'         => $tval['total_fee'],
            'payed_fee'         => $tval['total_fee'],
            'payment_integral'  => $tval['total_integral'],
            'total_integral'         => $tval['total_integral'],
            'payed_integral'         => $tval['total_integral'],
            'discount_fee'      => 0,
            'obtain_point_fee'  => 0,
            'post_fee'        => 0,
            'trade_memo'        => '门店交易',
            'pay_time'          =>$now,
            'consign_time'          =>$now,
            'end_time'          =>$now,
            'is_offline'          =>0,
            'is_mendian'         =>1
        );
        //子订单信息
        foreach($payment['sku_id'] as $k=> & $v){
            //获取货品信息
            $skuData = $model_sku->getRow('*',array('sku_id'=>$v));
            //获取单价
            $arr_price= $this->_price($payment['item_price'][$k],$skuData);
            //获取商品信息
            $itemData = app::get('topshop')->rpcCall('item.get',array('item_id'=>$skuData['item_id'],'fields'=>'*'));
            $oid = $tradeBaseTime.str_pad(++$tradeBaseRandNum,2,'0',STR_PAD_LEFT).$tradeModUserId;
            $order_data['order'][$k] = array(
                'oid'              => $oid,
                'tid'              => $tid,
                'shop_id'          => $this->shopId,
                'user_id'          => $payment['user_id'],
                'item_id'          => $skuData['item_id'],
                'sku_id'           => $v,
                'bn'               => $skuData['bn'],
                'price'            => $arr_price['price'],
                'integral'         => $arr_price['integral'],
                'num'              => $payment['item_amount'][$k],
                'payment'          => $payment['total_fee'][$k],
                'total_fee'        => $payment['total_fee'][$k],
                'payment_integral' => $payment['total_integral'][$k],
                'total_integral'   => $payment['total_integral'][$k],
                'part_mjz_discount'=> 0,
                'pic_path'         => $itemData['image_default_id'],
                'sub_stock'        => 1,
                'cat_service_rate' => $objLibCatServiceRate->getCatServiceRate(array('shop_id'=>$this->shopId, 'cat_id'=>$itemData['cat_id'])),
                'sendnum'          => 0,
                'created_time'     => $now,
                'modified_time'    => $now,
                'status'           => 'TRADE_FINISHED',
                'title'            => $itemData['title'],
                'spec_nature_info' => $skuData['spec_info'],
                'order_from'       => 'pc',
            );
        }
        return $order_data;
    }
    /**
    * ps ：创建支付单
    * Time：2015/12/23 15:03:53
    * @author jiang
    */
    function _createBill($orderDate){
        try{
            $objMdlPayment = app::get('ectools')->model('payments');
            $objMdlPayBill = app::get('ectools')->model('trade_paybill');

            $paymentId = $this->_getPaymentId($orderDate['user_id'].$count);
            $payment = array(
                'payment_id' => $paymentId,
                'money' => $orderDate['payment'],
                'cur_money' => $orderDate['total_fee'],
                'money_integral' => $orderDate['payment_integral'],
                'cur_integral' => $orderDate['total_integral'],
                'user_id' => $orderDate['user_id'],
                'user_name' => $orderDate['user_name'],
                'op_id' => $orderDate['user_id'],
                'op_name' => $orderDate['user_name'],
                'created_time' => time(),
                'pay_app_id' => '',
                'return_url' => '',
                'pay_name' => '门店支付',
                'pay_type' => 'online',
            );
            $result = $objMdlPayment->insert($payment);
            if(!$result)
            {
                    throw new \LogicException(app::get('ectools')->_("支付失败,支付单创建失败"));
            }
            $payBill = array(
                'payment_id' => $paymentId,
                'tid' => $orderDate['tid'],
                'status' =>'succ',
                'payment' => $orderDate['payment'],
                'payment_integral' => $orderDate['payment_integral'],
                'user_id' => $orderDate['user_id'],
                'created_time' => time(),
                'modified_time' => time(),
            );
            $billResult = $objMdlPayBill->insert($payBill);
            if(!$billResult)
            {
                throw new \LogicException(app::get('ectools')->_("支付失败,支付单明细添加失败"));
            }
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }
    private function _getPaymentId($tradeId)
    {
        $objMdlPayment = app::get('ectools')->model('payments');
        $tradeId = str_pad($tradeId%100000,5,time(),STR_PAD_LEFT);
        $i = rand(0,99999);
        do{
            if(99999==$i){
                $i=0;
            }
            $i++;
            $paymentId = date('ymdHi').str_pad($i,5,'0',STR_PAD_LEFT).$tradeId;
            $row = $objMdlPayment->getRow('payment_id',array('payment_id'=>$paymentId));
        }while($row);
        return $paymentId;

    }           

    /**
     * ps ：门店消费之后发送短信给会员
     * Time：2015/12/24 12:46:15
     * @author liuxin
     * @param userId  会员id
     * @param point   消费积分
     * @return 返回值类型
    */
    public function sendSms($userId,$point){
        $sendto = app::get('sysuser')->model('account')->getRow('mobile',array('user_id'=>$userId))['mobile'];
        $point_account = app::get('sysuser')->model('user')->getRow('point',array('user_id'=>$userId))['point'];
        if($sendto){
            $content = "您于".date("Y-m-d H:i:s")."o2o消费支出{$point}积分，账户余额{$point_account}积分";
            $type = array(
            'sendType' => 'notice',
            'use_reply' => false
            );
            if(!kernel::single('system_messenger_sms')->send($sendto,'',$content,$type)){
                return false;
            }
        }
    }
    /**
    * ps ：获取单价
    * Time：2016/01/22 11:03:37
    * @author jiang
    */
    private function _price($item_price,$skuData){
        $arr=explode(',', $item_price);
        $row=array();
        switch ($arr[1]) {
            case '0':
                $row['integral']=$skuData['integral'];
                $row['price']=0;
                break;
            case '1':
                $row['integral']=$skuData['blend_integral'];
                $row['price']=$skuData['blend_price'];
                break;
            case '2':
                $row['integral']=0;
                $row['price']=$skuData['price'];
                break;
        }
        return $row;
    }                                        
}