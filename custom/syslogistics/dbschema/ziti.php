<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'editable' => false,
            'comment' => app::get('syslogistics')->_('自增ID'),
        ),
        'name' =>
        array (
            'type' => 'string',
            'label' => app::get('syslogistics')->_('自提点名称'),
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('syslogistics')->_('自提点名称'),
        ),
        'area_state_id' =>
        array (
            'type' => 'string',
            'required' => true,
            'comment' => app::get('syslogistics')->_('自提地区ID(省)'),
        ),
        'area_city_id' =>
        array (
            'type' => 'string',
            'required' => true,
            'comment' => app::get('syslogistics')->_('自提地区ID(城市)'),
        ),
        'area_district_id' =>
        array (
            'type' => 'string',
            'required' => true,
            'comment' => app::get('syslogistics')->_('自提地区ID(区,县)'),
        ),
        'area' =>
        array (
            'type' => 'string',
            'required' => true,
            'comment' => app::get('syslogistics')->_('地区ID'),
        ),
        'addr' =>
        array (
            'type' => 'string',
            'label' => app::get('syslogistics')->_('自提地址'),
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('syslogistics')->_('地址'),
        ),
        'tel' =>
        array (
            'type' => 'string',
            'length' => 50,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syslogistics')->_('联系方式'),
            'comment' => app::get('sysuser')->_('电话或者手机号码'),
        ),
        //2016-5-3 by jianghui 添加所属店铺 经纬度
        'shop_id' => array(
            'type' => 'string',
            'required' => true,
            'label' => app::get('syslogistics')->_('所属店铺'),
            'comment' => app::get('syslogistics')->_('多个店铺的id'),
            'in_list' => false,
            'default_in_list' => true,
        ),
        'longitude' =>
        array(
            'type' => 'decimal',
            'precision' => 20,
            'scale' => 6,
            'label' => app::get('syslogistics')->_('经度'),
            'comment' => app::get('syslogistics')->_('经度'),
            'in_list' => true,
            'default_in_list' => false,
        ),
        'latitude' =>
        array(
            'type' => 'decimal',
            'precision' => 20,
            'scale' => 6,
            'label' => app::get('syslogistics')->_('纬度'),
            'comment' => app::get('syslogistics')->_('纬度'),
            'in_list' => true,
            'default_in_list' => false,
        ),
        'memo' =>
        array (
            'type' => 'text',
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syslogistics')->_('自提点介绍'),
            'comment' => app::get('syslogistics')->_('自提点介绍'),
        ),
        'is_selfshop' =>
        array (
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('syslogistics')->_('是否自营'),
        ),
        'ziti_image' => array(
            'type' => 'text',
            // 'required' => true,
            'comment' => app::get('syslogistics')->_('图片'),
        ),
        'geohash' =>
        array(
            'type' => 'string',
            'length' => 20,
            'label' => app::get('syslogistics')->_('GeoHash'),
            'comment' => app::get('syslogistics')->_('GeoHash'),
            'in_list' => false,
            'default_in_list' => false,
        ),
    ),
    'primary' => 'id',
    'index' => array(
        'ind_area_state_id' => ['columns' => ['area_state_id']],
        'ind_area_city_id' => ['columns' => ['area_city_id']],
        'ind_area_district_id' => ['columns' => ['area_district_id']],
    ),
    'comment' => app::get('syslogistics')->_('自提点表'),
);
