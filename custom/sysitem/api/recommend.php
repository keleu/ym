<?php
/**
 * 接口作用说明
 * item.search
 */
class sysitem_api_recommend{

    public $apiDescription = '获取推荐商品列表';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'item_id' => ['type'=>'int','valid'=>'','description'=>'商品id','example'=>'','default'=>''],
            'shop_id' => ['type'=>'int','valid'=>'','description'=>'店铺id','example'=>'','default'=>''],
        );
        $return['extendsFields'] = ['promotion','store'];
        return $return;
    }

    public function itemList($params)
    {
        $shop_id = $params['shop_id'];
        $objMdlRecommed= app::get('sysitem')->model('recommend');
        $ret = $objMdlRecommed->getList('*', array('shop_id'=>$shop_id));
        if(count($ret)>0){
	        $objMdlItem= app::get('sysitem')->model('item');
	         $searchParams = array(
	            'item_id' => $ret[0]['valid_item'],
	            'fields' => 'item_id,title,image_default_id,cat_id,brand_id,price,integral,blend_integral,blend_price',
	        );
	        $itemsList = app::get('syspromotion')->rpcCall('item.search',$searchParams);
	    }
        $data = $ret[0];
        $data['itemsList'] = $itemsList['list'];
        return $data;
    }
}
