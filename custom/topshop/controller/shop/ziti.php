<?php
/*********************************************************************\
*  Copyright (c) 2007-2015, TH. All Rights Reserved.
*  Author :jiang
*  FName  :ziti.php
*  Time   :2016/05/11 14:29:28
*  Remark :商家后台获取自提点
\*********************************************************************/
class topshop_ctl_shop_ziti extends topshop_controller{
    public $limit = 10;

    /**
    * ps ：自提点列表
    * Time：2016/05/11 14:29:46
    * @author jianghui
    */
    public function index()
    {
        $params['shop_id'] = $this->shopId;
        $page = input::get('pages')?input::get('pages'):1;
        $params['page_no'] = $page;
        $params['page_size'] = $this->limit;
        $ziti = app::get('topshop')->rpcCall('logistics.ziti.search',$params);

        $count = $ziti['count'];
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topshop_ctl_shop_ziti@index',$postFilter),
            'current'=>$page,
            'use_app' => 'topshop',
            'total'=>$total,
            'token'=>time(),
        );
        $pagedata['ziti'] = $ziti['list'];
        $pagedata['count'] =$count;
        $pagedata['pagers'] = $pagers;
        return $this->page('topshop/shop/ziti/index.html', $pagedata);
    }

    public function editarea()
    {
        $id = input::get('id');
        if($id){
            $objMdlZiti = app::get('syslogistics')->model('ziti');
            $ziti = $objMdlZiti->getRow('*',array('id'=>$id));
        }
        $pagedata['ziti'] = $ziti;
        return $this->page('topshop/shop/ziti/config.html', $pagedata);
    }

    /**
    * ps ：获取地区
    * Time：2016/05/03 13:17:58
    * @author jianghui
    */
    function getarea(){
        $address['first'] = area::getAreaNameById(explode(',',$_POST['area'])[0]);
        $address['address'] = area::getSelectArea($_POST['area'],'').$_POST['addr'];
        return json_encode($address);
    }

    /**
    * ps ：保存自提配置
    * Time：2016/05/03 13:33:18
    * @author jianghui
    */
    function savearea(){
        $data = array(
            'shop_id' => $this->shopId,
            'addr' => input::get('address'),
            'longitude' => input::get('longitude'),
            'latitude' => input::get('latitude'),
            'area_id' => input::get('area')[0],
            'tel' => input::get('tel'),
            'name' => input::get('name'),
            'ziti_image' => input::get('ziti_image'),
            'memo' => input::get('memo'),
        );
         //判断店铺是不是自营店铺 gongjiapeng
        $selfShopType = app::get('topshop')->rpcCall('shop.get',array('shop_id'=>$this->shopId));
        if($selfShopType['shop_type']=='self')
        {
            $data['is_selfshop'] = 1;
        }
        // 经纬度转换
        $data['geohash'] = geohash_encode($data['longitude'], $data['latitude']);
        try{
            if( input::get('id',false) )
            {
                $data['id'] = input::get('id');
                app::get('syslogistics')->rpcCall('logistics.ziti.update',$data);
            }
            else
            {
                $data['ziti_shopid'][0]['shop_id'] = $data['shop_id'];
                app::get('syslogistics')->rpcCall('logistics.ziti.add',$data);
            }
        }catch( LogicException $e){
            $msg = $e->getMessage();
            $url = url::action('topshop_ctl_shop_ziti@editarea', array('id'=>$data['id']));
            return $this->splash('error',$url,$msg,true);
        }
        $url = url::action('topshop_ctl_shop_ziti@index');
        $msg = app::get('topshop')->_('保存成功');
        return $this->splash('success',$url,$msg,true);
    }

    /**
    * ps ：删除自提点
    * Time：2016/05/03 17:13:57
    * @author jianghui
    */
    function deletearea()
    {
        $apiData['shop_id'] = $this->shopId;
        $url = url::action('topshop_ctl_shop_ziti@index');
        $objMdlZiti = app::get('syslogistics')->model('ziti');
        try
        {
            $objMdlZiti->delete( array('id'=>input::get('id')));
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $msg = app::get('topshop')->_('删除自提点成功');
        return $this->splash('success', $url, $msg, true);
    }

    /**
    * ps ：自提配置
    * Time：2016/05/04 13:42:58
    * @author jianghui
    */
    function setting(){
        $objMdlShop = app::get('sysshop')->model('shop');
        $pagedata = $objMdlShop->getRow('is_ziti',array('shop_id'=>$this->shopId));
        return $this->page('topshop/shop/ziti/setting.html', $pagedata);
    }
    /**
    * ps ：保存自提配置
    * Time：2016/05/04 15:20:54
    * @author jianghui
    */
     function savesetting(){
        $objMdlShop = app::get('sysshop')->model('shop');
        $url = url::action('topshop_ctl_shop_ziti@setting');
        try
        {
            $objMdlShop->update(input::get(),array('shop_id'=>$this->shopId));
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $msg = app::get('topshop')->_('保存成功');
        return $this->splash('success',$url,$msg,true);
     }

     /**
     * ps ：获取该自提点未完成的订单
     * Time：2016/05/07 17:23:27
     * @author jianghui
     */
     function ajaxTrade(){
        $objMdlShop = app::get('systrade')->model('trade');
        $status = array('WAIT_BUYER_PAY','WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS');
        $trade = $objMdlShop->getList('tid',array('ziti_id'=>$_POST['ziti_id'],'status|in'=>$status));
        $is_trade = count($trade)>0?'true':'false';
        return response::json(array('is_trade'=>$is_trade));
     }
}



