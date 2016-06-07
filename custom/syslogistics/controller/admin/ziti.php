<?php

/**
 * ShopEx licence
 * @author ajx
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syslogistics_ctl_admin_ziti extends desktop_controller {

    public $workground = 'syslogistics.workground.logistics';


    /**
     * 物流公司列表
     * @var string $key
     * @var int $offset
     * @access public
     * @return int
     */
    public function index()
    {
        return $this->finder('syslogistics_mdl_ziti',array(
            'title' => app::get('syslogistics')->_('自提点列表'),
            //2016-5-3 by jianghui 隐藏该功能 只有商家才可以添加自提点
            'actions' => array(
                array(
                    'label'=>app::get('syslogistics')->_('添加自提点'),
                    'href'=>'?app=syslogistics&ctl=admin_ziti&act=edit&action=add',
                    'target'=>'dialog::{title:\''.app::get('syslogistics')->_('添加自提点').'\',  width:1020,height:550}',
                ),
            ),
        ));
    }

    public function edit()
    {
        $pagedata['areaData'] = area::areaKvdata();
        $pagedata['areaPath'] = area::getAreaIdPath();

        if( input::get('id') )
        {
            $data = app::get('syslogistics')->rpcCall('logistics.ziti.get',['id'=>input::get('id')]);
            foreach( (array)explode(',',$data['area_id']) as $areaId)
            {
                if( $parentId )
                {
                    $areaData[$areaId] = $pagedata['areaPath'][$parentId];
                    $parentId = $areaId;
                }
                else
                {
                    $areaData[$areaId] = area::getAreaDataLv1();
                    $parentId = $areaId;
                }
            }
            $pagedata['selectArea'] = $areaData;

            $data['area'] = $data['area'].":".$data['area_id'];
            $pagedata['data'] = $data;

            //获取所属店铺
            $objMdlShop = app::get('syslogistics')->model('ziti_shopid');
            $shopids = $objMdlShop->getList('shop_id',array('ziti_id'=>$data['id']));
            if(count($shopids)>0){
                foreach ($shopids as $key => $value) {
                    $pagedata['shoparr'][$value['shop_id']] = $value;
                }
            }
        }
        else
        {
            $pagedata['areaLv1'] = area::getAreaDataLv1();
        }

        $objMdlShop = app::get('sysshop')->model('shop');
        $shopData = $objMdlShop->getList('shop_name,shop_id,shopuser_name,seller_id',array('shop_type'=>'self'));
        foreach ($shopData as $key => $value) {
            if($pagedata['shoparr'][$value['shop_id']]){
                $shopData[$key]['show'] = 1;
            }
        }
        $pagedata['shop_ids'] = $shopData;
        return view::make('syslogistics/ziti/edit.html', $pagedata);
    }

    public function save()
    {
        $this->begin('?app=syslogistics&ctl=admin_ziti&act=index');
        $areaIds = explode(':',input::get('area_id'));
        $params = array(
            'addr' => input::get('addr'),
            'longitude' => input::get('longitude'),
            'latitude' => input::get('latitude'),
            'area_id' => $areaIds[1],
            'tel' => input::get('tel'),
            'name' => input::get('name'),
            'ziti_image' => input::get('ziti_image'),
            'memo' => input::get('memo'),
            'is_selfshop' => 1,
        );
        // 经纬度转换
        $params['geohash'] = geohash_encode($params['longitude'], $params['latitude']);
        if( !$params['area_id'] )
        {
            $this->end(false, '请选择自提地区');
        }
        if(count(input::get('shop_ids'))<1){
            $this->end(false, '请选择自营店铺');
        }
        $params['shop_id'] = join(',',input::get('shop_ids'));
        foreach (input::get('shop_ids') as $key => $value) {
                $params['ziti_shopid'][$key]['shop_id'] = $value;
        }
        // dump($params);exit;
        try{
        if( input::get('id',false) )
        {
            $params['id'] = input::get('id');
            $this->adminlog("修改自提点[id:{$params['id']}]", 1);
            app::get('syslogistics')->rpcCall('logistics.ziti.update',$params);
        }
        else
        {
            $this->adminlog("添加自提点[名称:{$params['name']}]", 1);
            app::get('syslogistics')->rpcCall('logistics.ziti.add',$params);
        }
        }catch( LogicException $e){
            $this->end(false, $e->getMessage());
        }

        $this->end(true, app::get('syslogistics')->_('操作成功'));
    }

    public function setting()
    {
        if( $_POST )
        {
            $this->adminlog("设置是否启用自提点[启用状态:{$_POST['open']}]", 1);
            app::get('syslogistics')->setConf('syslogistics.ziti.open',$_POST['open']);
            $pagedata['open'] = $_POST['open'];
        }
        else
        {
            $pagedata['open'] = app::get('syslogistics')->getConf('syslogistics.ziti.open');
        }
        return $this->page('syslogistics/ziti/setting.html', $pagedata);

    }

    /**
    * ps ：获取地区
    * Time：2016/05/03 13:17:58
    * @author jianghui
    */
    public function getarea(){
        $_POST = json_decode($_POST['data'],true);

        list($addr ,$area) = explode(':',$_POST['area'][0]);
        // dump($_POST);
        $address['first'] = area::getAreaNameById(explode(',', $area)[0]);
        $address['address'] = area::getSelectArea($area,'').$_POST['addr'];
        // dump($address);exit;
        return json_encode($address);
    }
}

