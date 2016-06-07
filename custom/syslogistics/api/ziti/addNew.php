<?php

class syslogistics_api_ziti_addNew {

    public $apiDescription = "添加自提点";

    public function getParams()
    {
        $return['params'] = array(
            'name' =>['type'=>'string','valid'=>'required|max:20', 'description'=>'自提点名称','default'=>'','example'=>'商派自提点'],
            'area_id' =>['type'=>'string','valid'=>'required', 'description'=>'自提地区ID','default'=>'','example'=>'430000,430000,430102'],
            'addr' =>['type'=>'string','valid'=>'required:max:50', 'description'=>'自提详细地址','default'=>'','example'=>'桂林路396号2号楼'],
            'tel' =>['type'=>'string','valid'=>'required', 'description'=>'自提点联系方式','default'=>'','example'=>'13918765432'],
            'shop_id' =>['type'=>'number','valid'=>'', 'description'=>'所属店铺','default'=>'','example'=>''],
            'longitude' =>['type'=>'decimal','valid'=>'', 'description'=>'经度','default'=>'','example'=>''],
            'latitude' =>['type'=>'string','valid'=>'', 'description'=>'纬度','default'=>'','example'=>''],
        );

        return $return;
    }

    public function create($params)
    {
        $objMdlZiti = app::get('syslogistics')->model('ziti');

        if( !area::checkArea($params['area_id']) )
        {
            throw new LogicException('请选择完整地区');
        }

        $name = $objMdlZiti->getRow('id', ['name'=>trim($params['name'])] );
        if( $name ) throw new LogicException('自提点名称已存在');

        $areaIds = explode(',', $params['area_id']);
        if( count($areaIds) == 2 )
        {
            $insertData['area_state_id'] = 1;
            $insertData['area_city_id'] = $areaIds[0];
            $insertData['area_district_id'] = $areaIds[1];
        }
        else
        {
            $insertData['area_state_id'] = $areaIds[0];
            $insertData['area_city_id'] = $areaIds[1];
            $insertData['area_district_id'] = $areaIds[2];
        }

        $insertData['name'] = trim($params['name']);
        $insertData['area'] = $params['area_id'];
        $insertData['addr'] = $params['addr'];
        $insertData['tel'] = $params['tel'];
        //2016-5-3 by jianghui 添加所属店铺和经纬度
        $insertData['shop_id'] = $params['shop_id'];
        $insertData['longitude'] = $params['longitude'];
        $insertData['latitude'] = $params['latitude'];
        $insertData['ziti_image'] = $params['ziti_image'];
        $insertData['is_selfshop'] = $params['is_selfshop'];
        $insertData['geohash'] = $params['geohash'];
        $insertData['memo'] = $params['memo'];
        $insertData['ziti_shopid'] = $params['ziti_shopid'];
        return $objMdlZiti->save($insertData);
    }
}

