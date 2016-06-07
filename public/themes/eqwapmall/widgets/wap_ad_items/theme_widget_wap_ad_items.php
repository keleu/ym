<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_wap_ad_items(&$setting){
    $rows = 'item_id,title,price,integral,image_default_id,blend_integral,blend_price';
    $objItem = kernel::single('sysitem_item_info');
    $setting['item'] = $objItem->getItemList($setting['item_select'], $rows,[],array('orderBy'=>'cat_id,item_id'));
    $setting['defaultImg'] = app::get('image')->getConf('image.set');
    if( userAuth::check() )
    {
        $setting['nologin'] = 1;
    }

    //2016-3-16 by jianghui 查找商品是否已经收藏过该商品
    foreach ($setting['item'] as & $v) {
        $filter=array();
        $filter['user_id'] = userAuth::id();
        $filter['item_id'] = $v['item_id'];
        $filter['objectType'] = 'goods';
        $retitem = app::get('sysuser')->model('user_fav')->getRow('gnotify_id',$filter);
        $v['is_item'] = count($retitem)>0?1:0;
        $v['blend'] = kernel::single('sysitem_blendShow')->show($v['blend_integral'],$v['blend_price']);
    }
    
    //自定义排序:如果全部默认为0，则默认没有手动排序，就不需要处理
    $order_by = array_filter($setting['order_by']);
    // dump($setting['order_by']);
    if(count($order_by)>0){
        foreach ($setting['item_select'] as $key => $select_row) {
            foreach ($setting['item'] as $dkey => $it) {
                if($it['item_id']==$select_row){
                    $setting['item_bak'][] = $it;
                    unset($setting['item'][$dkey]);
                }
            }
        }
        $setting['item'] = $setting['item_bak'];
        unset($setting['item_bak']);
        // dump($setting);
        array_multisort($setting['order_by'] ,$setting['item']);
    }

    return $setting;
}
?>
