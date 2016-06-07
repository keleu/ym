<?php
class topshop_ctl_trade_list extends topshop_controller{
    public $limit = 10;

    public function index()
    {
        $pagedata['status'] = array(
            '0' => '全部',
            '1' => '待支付',
            '2' => '待发货',
            '3' => '待收货',
            '4' => '已收货',
            '5' => '已取消',
        );
        $pagedata['filter']['status'] = input::get('status');
        $this->contentHeaderTitle = app::get('topshop')->_('订单列表');
        return $this->page('topshop/trade/list.html', $pagedata);
    }

    public function search()
    {
        $tradeStatus = array(
            'WAIT_BUYER_PAY' => '未付款',
            'WAIT_SELLER_SEND_GOODS' => '已付款，请发货',
            'WAIT_BUYER_CONFIRM_GOODS' => '已发货，等待确认',
            'TRADE_FINISHED' => '已完成',
            'TRADE_CLOSED' => '已关闭',
            'TRADE_CLOSED_BY_SYSTEM' => '已关闭',
        );
        $this->contentHeaderTitle = app::get('topshop')->_('订单查询');
        $postFilter = input::get();
        $filter = $this->_checkParams($postFilter);
        $limit = $this->limit;
        $status = $filter['status'];
        if(is_array($filter['status']))
        {
            $status = implode(',',$filter['status']);
        }

        $page = $filter['pages'] ? $filter['pages'] : 1;
        $params = array(
            'status' => $status,
            'tid' => $filter['tid'],
            'create_time_start' =>$filter['created_time_start'],
            'create_time_end' =>$filter['created_time_end'],
            'receiver_mobile' =>$filter['receiver_mobile'],
            'receiver_phone' =>$filter['receiver_phone'],
            'receiver_name' =>$filter['receiver_name'],
            'user_name' =>$filter['user_name'],
            'page_no' => $page,
            'page_size' =>$limit,
            'order_by' =>'created_time desc',
            //by zhangyan 2015-11-25 没有查询订单备注信息,添加trade_memo
            'fields' =>'tid,shop_id,user_id,status,payment,total_fee,post_fee,payed_fee,receiver_name,created_time,receiver_mobile,discount_fee,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.item_id,order.sku_id,need_invoice,invoice_name,invoice_type,invoice_main,pay_type,trade_memo,payment_integral,order.integral',
        );
        $tradeList = app::get('topshop')->rpcCall('trade.get.list',$params,'seller');
        $count = $tradeList['count'];
        $tradeList = $tradeList['list'];
        //by 张艳 2015-11-26 订单详情页面添加商品销售属性
        $objSku = app::get('sysitem')->model('sku');
        foreach ($tradeList as $key => & $tradeOrderList) {
            foreach ($tradeOrderList['order'] as $key => & $tradeOrder) {
                $skudata = $objSku->getRow('spec_info',array('sku_id'=>$tradeOrder['sku_id']));
                $tradeOrder['spec_info']=$skudata['spec_info'];
            }
        }
        foreach((array)$tradeList as $key=>$value)
        {
            $usersId[] = $value['user_id'];
            if( $value['status'] == 'WAIT_SELLER_SEND_GOODS' )
            {
                $tids[] = $value['tid'];
                $tradeList[$key]['is_apply_abnormal'] = true;
            }
            else
            {
                $tradeList[$key]['is_apply_abnormal'] = false;//不能申请取消异常订单
            }
        }

        if( $tids )
        {
            $abnormalData = app::get('topshop')->rpcCall('trade.abnormal.list.get',['role'=>'seller','tid'=>$tids,'fields'=>'tid']);
            $tradeAnormalData = array_bind_key($abnormalData['tradeAnormal'], 'tid');
        }

        if( $usersId )
        {
            $username = app::get('topshop')->rpcCall('user.get.account.name', ['user_id' => implode(',', $usersId)], 'seller');
        }

        foreach((array)$tradeList as $key=>$value)
        {
            $tradeList[$key]['status_depict'] = $tradeStatus[$value['status']];
            $tradeList[$key]['user_login_name'] = $username[$value['user_id']];
            if( $tradeAnormalData[$value['tid']] )
            {
                $tradeList[$key]['is_apply_abnormal'] = false;
            }
            //2016-3-30 by jianghui 添加积分+现金总计
            $tradeList[$key]['payment_blend'] = kernel::single('sysitem_blendShow')->totalshow($value['payment_integral'],$value['payment']);
            foreach ($value['order'] as $k => $v) {
                //2016-3-30 by jianghui 添加积分+现金单价
            $tradeList[$key]['order'][$k]['blend'] = kernel::single('sysitem_blendShow')->totalshow($v['integral'],$v['price']);
            }
        }
        $pagedata['orderlist'] =$tradeList;
        $pagedata['count'] =$count;
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $pagedata['pagers'] = $this->__pager($postFilter,$page,$count);
        return view::make('topshop/trade/item.html', $pagedata);
    }

    private function __pager($postFilter,$page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topshop_ctl_trade_list@search',$postFilter),
            'current'=>$page,
            'use_app' => 'topshop',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;

    }

    private function _checkParams($filter)
    {
        $statusLUT = array(
            '1' => 'WAIT_BUYER_PAY',
            '2' => 'WAIT_SELLER_SEND_GOODS',
            '3' => 'WAIT_BUYER_CONFIRM_GOODS',
            '4' => 'TRADE_FINISHED',
            '5' => array('TRADE_CLOSED','TRADE_CLOSED_BY_SYSTEM'),
        );
        foreach($filter as $key=>$value)
        {
            if(!$value) unset($filter[$key]);
            if($key == 'create_time')
            {
                $times = array_filter(explode('-',$value));
                if($times)
                {
                    $filter['created_time_start'] = strtotime($times['0']);
                    $filter['created_time_end'] = strtotime($times['1'])+86400;
                    unset($filter['create_time']);
                }
            }

            if($key=='status' && $value)
            {
                $filter['status'] = $statusLUT[$value];
            }
        }
        return $filter;
    }

    public function ajaxCloseTrade()
    {
        $pagedata['tid'] = input::get('tid');
        $pagedata['reason'] = config::get('tradeCancelReason');

        return view::make('topshop/trade/cancel.html', $pagedata);
    }

    public function closeTrade()
    {
        $reasonSetting = config::get('tradeCancelReason');
        $reasonPost = input::get('cancel_reason');
        if($reasonPost == "other")
        {
            $cancelReason = input::get('other_reason');
        }
        else
        {
            $cancelReason = $reasonSetting['shopuser'][$reasonPost];
        }
        $params['tid'] = input::get('tid');
        $params['cancel_reason'] = $cancelReason;
        $url = url::action('topshop_ctl_trade_list@index');
        try
        {
            app::get('topshop')->rpcCall('trade.cancel',$params,'seller');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',"",$msg,true);
        }
        $msg = '取消成功';
        return $this->splash('succecc',$url,$msg,true);
    }

    /**
     * 修改订单价格页面
     * @return
     */
    public function modifyPrice()
    {
        $tids = input::get('tid');
        $params['tid'] = $tids;
        $params['fields'] = "total_fee,post_fee,payment,tid,receiver_state,receiver_city,receiver_district,receiver_address,orders.pic_path,orders.title,orders.item_id,orders.spec_nature_info,orders.price,orders.num,orders.total_fee,orders.discount_fee,orders.part_mjz_discount,orders.oid,orders.adjust_fee,orders.integral,payment_integral,total_integral,orders.total_integral";
        $pagedata['trade_detail'] = app::get('topshop')->rpcCall('trade.get',$params,'seller');
        //2016-3-21 by jianghui 添加总优惠金额
        $pagedata['trade_detail']['part_mjz_discount'] = round($pagedata['trade_detail']['total_fee'] + $pagedata['trade_detail']['post_fee'] - $pagedata['trade_detail']['payment'],3);
        return view::make('topshop/trade/modify_price.html', $pagedata);
    }

    /**
     * 修改订单价格
     * @return
     */
    public function updatePrice()
    {
        $url = url::action('topshop_ctl_trade_list@index');
        $params = input::get('trade');
        $params['order'] = json_encode($params['order']);

        try
        {
            app::get('topshop')->rpcCall('trade.update.price',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',"",$msg,true);
        }
        $msg = '修改成功';
        return $this->splash('succecc',$url,$msg,true);
    }

    //收钱并收货页面
    public function ajaxFinishTrade()
    {
        $params['tid'] = input::get('tid');
        $params['fields'] = "user_id,tid,shop_id,status,payment,post_fee,pay_type,receiver_state,receiver_city,receiver_district,receiver_address,receiver_name,receiver_mobile,payment_integral";
        $tradeInfo = app::get('topshop')->rpcCall('trade.get',$params,'seller');
        $pagedata['tradeInfo'] = $tradeInfo;
        $pagedata['logi'] = app::get('topc')->rpcCall('delivery.get',array('tid'=>$params['tid']));
        return view::make('topshop/trade/finish.html', $pagedata);
    }


    //收钱并收货
    public function finishTrade()
    {
        $params = input::get('trade');
        $params['seller_id'] = $this->sellerId;
        $params['seller_name'] = $this->sellerName;
        $params['memo'] = "商家处理货到付款订单";
        try
        {
            app::get('topshop')->rpcCall('trade.moneyAndGoods.receipt',$params);
            //处理积分
            $objRefund = kernel::single('sysuser_data_point');
            $tradeInfo['behavior']='订单支付';
            $tradeInfo['money_integral']=$params['payment_integral'];
            $tradeInfo['tids']=$params['tid'];
            $tradeInfo['user_id']=$params['user_id'];
            $result = $objRefund->update($tradeInfo);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',"",$msg,true);
        }
        $msg = "处理完成";
        $url = url::action('topshop_ctl_trade_list@index');
        return $this->splash('succecc',$url,$msg,true);
    }
}

