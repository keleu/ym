<?php

/**
 * ShopEx licence
 * @author ajx
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syslogistics_finder_ziti {


    public $column_edit = '编辑';
    public $column_edit_order = 2;
    public $column_area_order = 3;
    public $column_shopName_order = 5;

     public function __construct($app)
    {
        $this->column_area = app::get('syslogistics')->_('地区');
        $this->column_shopName = app::get('syslogistics')->_('所属店铺');
    }

    /**
     * 物流公司编辑
     * @var array
     * @return html
     */
    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = '?app=syslogistics&ctl=admin_ziti&act=edit&finder_id='.$_GET['_finder']['finder_id'].'&id='.$row['id'];
            $target = 'dialog::  {title:\''.app::get('syslogistics')->_('编辑自提点').'\', width:1020, height:550}';
            $title = app::get('syslogistics')->_('编辑');

            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
        }
    }


    public function column_area(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $colList[$k] = area::getSelectArea($row['area'],'');
        }
    }

    /**
    * ps ：获取所属店铺
    * Time：2016/05/06 15:54:33
    * @author jianghui
    */

    public function column_shopName(&$colList, $list)
    {
        $modelZit_shopid = app::get('syslogistics')->model('ziti_shopid');
        $modelshop = app::get('sysshop')->model('shop');
        foreach($list as $k=>$row)
        {
            $retshopid = $modelZit_shopid->getList('shop_id',array('ziti_id'=>$row['id']));
            $shopName =array();
            foreach ($retshopid as $key => $value) {
                $shopName[] = $modelshop->getRow('shop_name',array('shop_id'=>$value['shop_id']))['shop_name'];
            }
            $colList[$k] = join('，',$shopName);
        }
    }
}

