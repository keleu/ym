<?php
class topc_ctl_member_trade extends topc_ctl_member {

    public function tradeList()
    {
        //2016.4.25 by yangjie 获取默认图片地址
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $user_id = userAuth::id();
        $postdata = input::get();
        if(input::get('status'))
        {
            $status =input::get('status');
        }
        $params = array(
            'user_id' => userAuth::id(),
            'status' => $status,
            'page_no' =>$postdata['pages'] ? $postdata['pages'] : 1,
            'page_size' =>$this->limit,
            'order_by' =>'created_time desc',
            'fields' =>'tid,shop_id,user_id,status,payment,total_fee,post_fee,payed_fee,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,is_mendian,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.aftersales_status,buyer_rate,order.complaints_status,order.item_id,order.shop_id,order.status,order.spec_nature_info,activity,pay_type,is_offline,payment_integral,total_integral,payed_integral,order.integral',
        );
        $tradelist = app::get('topc')->rpcCall('trade.get.list',$params,'buyer');
        $count = $tradelist['count'];
        $tradelist = $tradelist['list'];
        foreach( $tradelist as $key=>$row)
        {
            $tradelist[$key]['is_buyer_rate'] = false;

            foreach( $row['order'] as $k=>$orderListData )
            {
                if( !$orderListData['aftersales_status'] && $row['buyer_rate'] == '0' && $row['status'] == 'TRADE_FINISHED' )
                {
                    $tradelist[$key]['is_buyer_rate'] = true;
                }
                //2016-3-30 by jianghui 添加积分+现金单价
                $tradelist[$key]['order'][$k]['blend'] = kernel::single('sysitem_blendShow')->totalshow($orderListData['integral'],$orderListData['price']);
            }
            //2016-3-30 by jianghui 添加积分+现金总计
            $tradelist[$key]['payment_blend'] = kernel::single('sysitem_blendShow')->totalshow($row['payment_integral'],$row['payment']);
        }
        $pagedata['trades'] = $tradelist;
        $pagedata['pagers'] = $this->__pages($postdata['pages'],$postdata,$count);
        $pagedata['count'] = $count;
        $pagedata['action'] = 'topc_ctl_member_trade@tradeList';
        $this->action_view = "trade/list.html";
        return $this->output($pagedata);
    }

    public function tradeDetail()
    {
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        $params['fields'] = "tid,dlytmpl_id,status,payment,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,trade_memo,receiver_name,receiver_mobile,ziti_addr,ziti_id,ziti_memo,orders.price,orders.num,orders.title,orders.item_id,orders.sku_id,orders.pic_path,total_fee,total_integral,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,activity,cancel_reason,payment_integral,orders.integral,orders.total_integral";
        $trade = app::get('topc')->rpcCall('trade.get',$params,'buyer');
        if($trade['dlytmpl_id'] == 0 && $trade['ziti_addr'])
        {
            $pagedata['ziti'] = "true";
        }
        //by 张艳 2015-11-25 订单详情页面添加商品销售属性
        $objSku = app::get('sysitem')->model('sku');
        foreach ($trade['orders'] as $orderKey => & $iteam) {
            $skudata = $objSku->getRow('spec_info',array('sku_id'=>$iteam['sku_id']));
            $iteam['spec_info']=$skudata['spec_info'];

            //2016-3-30 by jianghui 添加积分+现金总计
            $iteam['total_blend'] = kernel::single('sysitem_blendShow')->totalshow($iteam['total_integral'],$iteam['total_fee']);
            //单价
            $iteam['blend'] = kernel::single('sysitem_blendShow')->totalshow($iteam['integral'],$iteam['price']);
        }

        //2016-3-30 by jianghui 添加积分+现金应付
        $trade['payment_blend'] = kernel::single('sysitem_blendShow')->totalshow($trade['payment_integral'],$trade['payment']);
        //总计
        $trade['total_blend'] = kernel::single('sysitem_blendShow')->totalshow($trade['total_integral'],$trade['payment']);
        //已支付
        $trade['payed_blend'] = kernel::single('sysitem_blendShow')->totalshow($trade['payed_integral'],$trade['payed_fee']);//2016.5.4 by yangjie 把$trade['total_integral']改成$trade['payed_integral']

        $pagedata['trade'] = $trade;
        $pagedata['logi'] = app::get('topc')->rpcCall('delivery.get',array('tid'=>$params['tid']));
        $pagedata['action'] = 'topc_ctl_member_trade@tradeList';
        $this->action_view = "trade/detail.html";
        return $this->output($pagedata);
    }

    public function ajaxGetTrack()
    {
        $postData = input::get();
        $pagedata['track'] = app::get('topc')->rpcCall('logistics.tracking.get.hqepay',$postData);
        return view::make('topc/member/trade/logistics.html', $pagedata);
    }


    public function ajaxCancelTrade()
    {
        $pagedata['tid'] = input::get('tid');
        $pagedata['reason'] = config::get('tradeCancelReason');
        return view::make('topc/member/gather/cancel.html', $pagedata);
    }

    public function ajaxConfirmTrade()
    {
        $pagedata['tid'] = input::get('tid');
        return view::make('topc/member/gather/confirm.html', $pagedata);
    }

    public function cancelOrderBuyer()
    {
        $reasonSetting = config::get('tradeCancelReason');
        $reasonPost = input::get('cancel_reason');
        $validator = validator::make($reasonPost,['required'],['取消原因必选!']);
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        if($reasonPost == "other")
        {
            $cancelReason = input::get('other_reason');
            $validator = validator::make($cancelReason,['required'],['取消原因必须填写!']);
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();
                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }
        }
        else
        {
            $cancelReason = $reasonSetting['user'][$reasonPost];
        }
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        $params['cancel_reason'] = $cancelReason;
        try
        {
            app::get('topc')->rpcCall('trade.cancel',$params,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        $url = url::action('topc_ctl_member_trade@tradeList');
        $msg = app::get('topc')->_('订单取消成功');
        return $this->splash('success',$url,$msg,true);
    }

    public function confirmReceipt()
    {
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        try
        {
            app::get('topc')->rpcCall('trade.confirm',$params,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        $url = url::action('topc_ctl_member_trade@tradeList');
        $msg = app::get('topc')->_('订单确认收货完成');
        return $this->splash('success',$url,$msg,true);
    }

    /**
     * 分页处理
     * @param int $current 当前页
     *
     * @return $pagers
     */
    private function __pages($current,$filter,$count)
    {
        //处理翻页数据
        $current = $current ? $current : 1;
        $filter['pages'] = time();
        $limit = $this->limit;

        if( $count > 0 ) $totalPage = ceil($count/$limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_member_trade@tradeList',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'token'=>time(),
        );
        return $pagers;
    }
}
