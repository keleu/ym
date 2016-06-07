<?php
class topshop_ctl_trade_detail extends topshop_controller{
    public function index()
    {
        $tids = input::get('tid');
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topshop_ctl_index@index'),'title' => app::get('topshop')->_('首页')],
            ['url'=> url::action('topshop_ctl_trade_list@index'),'title' => app::get('topshop')->_('订单列表')],
            ['title' => app::get('topshop')->_('订单详情')],
        );

        $params['tid'] = $tids;
        //by zhangyan 2015-11-25 添加sku_id
        $params['fields'] = "user_id,tid,status,payment,ziti_addr,ziti_memo,dlytmpl_id,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,orders.price,orders.num,orders.title,orders.item_id,orders.sku_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,orders.bn,cancel_reason,payment_integral,orders.integral,orders.total_integral";
        $tradeInfo = app::get('topshop')->rpcCall('trade.get',$params,'seller');
        //by 张艳 2015-11-26 订单详情页面添加商品销售属性
        $objSku = app::get('sysitem')->model('sku');
        foreach ($tradeInfo['orders'] as $key => & $tradeOrder) {
            $skudata = $objSku->getRow('spec_info',array('sku_id'=>$tradeOrder['sku_id']));
            $tradeOrder['spec_info']=$skudata['spec_info'];
        }
        if($tradeInfo['dlytmpl_id'] == 0 && $tradeInfo['ziti_addr'])
        {
            $pagedata['ziti'] = "true";
        }

        if(!$tradeInfo)
        {
            redirect::action('topshop_ctl_trade_list@index')->send();exit;
        }
        $userInfo = app::get('topshop')->rpcCall('user.get.account.name', ['user_id' => $tradeInfo['user_id']], 'seller');
        $tradeInfo['login_account'] = $userInfo[$tradeInfo['user_id']];

        $pagedata['trade']= $tradeInfo;
        $pagedata['logi'] = app::get('topc')->rpcCall('delivery.get',array('tid'=>$params['tid']));
        $this->contentHeaderTitle = app::get('topshop')->_('订单详情');
        return $this->page('topshop/trade/detail.html', $pagedata);
    }

    public function ajaxGetTrack()
    {
        $postData = input::get();
        $pagedata['track'] = app::get('topc')->rpcCall('logistics.tracking.get.hqepay',$postData);
        return view::make('topshop/trade/trade_logistics.html',$pagedata);
    }

    public function setTradeMemo()
    {
        $params['tid'] = input::get('tid');
        $params['shop_id'] = $this->shopId;
        try
        {
            $params['shop_memo'] = input::get('shop_memo');
            $result = app::get('topshop')->rpcCall('trade.add.memo',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }
        $msg = app::get('topshop')->_('备注添加成功');
        $url = url::action('topshop_ctl_trade_detail@index',array('tid'=>$params['tid']));
        return $this->splash('success',$url,$msg,true);
    }
}
