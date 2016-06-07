<?php
class topshop_ctl_shop_recommend extends topshop_controller {

    public function index()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('免邮管理');
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 10;
        $params = array(
            'page_no' => $pageSize*($filter['pages']-1),
            'page_size' => $pageSize,
            'fields' =>'*',
            'shop_id'=> $this->shopId,
        );
        $freepostageListData = app::get('topshop')->rpcCall('promotion.freepostage.list', $params,'seller');
        $count = $freepostageListData['count'];
        $pagedata['freepostageList'] = $freepostageListData['freepostages'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topshop_ctl_promotion_freepostage@list_freepostage', $filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        $gradeList = app::get('topshop')->rpcCall('user.grade.list');
        // 组织会员等级的key,value的数组，方便取会员等级名称
        $gradeKeyValue = array_bind_key($gradeList, 'grade_id');

        // 增加列表中会员等级名称字段
        foreach($pagedata['freepostageList'] as &$v)
        {
            $valid_grade = explode(',', $v['valid_grade']);

            $checkedGradeName = array();
            foreach($valid_grade as $gradeId)
            {
                $checkedGradeName[] = $gradeKeyValue[$gradeId]['grade_name'];
            }
            $v['valid_grade_name'] = implode(',', $checkedGradeName);
        }

        $pagedata['now'] = time();
        $pagedata['total'] = $count;

        return $this->page('topshop/promotion/freepostage/index.html', $pagedata);
    }



    public function edit()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('新添/编辑橱窗商品');
        $params=array(
                'shop_id' => $this->shopId
            );
        $itemsList = app::get('sysitem')->rpcCall('item.get.recommend',$params);
        $pagedata = $itemsList;
        $pagedata['shopCatList'] = app::get('topshop')->rpcCall('shop.authorize.cat',array('shop_id'=>$this->shopId));
        return $this->page('topshop/shop/recommend/edit.html', $pagedata);
    }

    public function save()
    {
        $params = input::get();
        $apiData = $params;
        $apiData['shop_id'] = $this->shopId;
        $apiData['valid_item'] = implode(',',$params['item_id']);
        $apiData['item_id'] = 0;
        $recommend = app::get('sysitem')->model('recommend');
        try
        {
            // 保存推荐
            if($apiData['valid_item']){
                $result = $recommend->save($apiData);
            }else{
                $result = $recommend->delete($apiData);
            }
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topshop_ctl_shop_recommend@edit', array('recommend_id'=>$params['recommend_id']));
            return $this->splash('error',$url,$msg,true);
        }
        $url = url::action('topshop_ctl_shop_recommend@edit', array('recommend_id'=>$params['recommend_id']));
        $msg = app::get('topshop')->_('保存成功');
        return $this->splash('success',$url,$msg,true);
    }

    public function delete_freepostage()
    {
        $apiData['shop_id'] = $this->shopId;
        $apiData['freepostage_id'] = input::get('freepostage_id');
        $url = url::action('topshop_ctl_promotion_freepostage@list_freepostage');
        try
        {
            app::get('topshop')->rpcCall('promotion.freepostage.delete', $apiData);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
        $msg = app::get('topshop')->_('删除免邮成功');
        return $this->splash('success', $url, $msg, true);
    }

    //根据商家id和3级分类id获取商家所经营的所有品牌
    public function getBrandList()
    {
        $shopId = $this->shopId;
        $catId = input::get('catId');
        $params = array(
            'shop_id'=>$shopId,
            'cat_id'=>$catId,
            'fields'=>'brand_id,brand_name,brand_url'
        );
        $brands = app::get('topshop')->rpcCall('category.get.cat.rel.brand',$params);
        return response::json($brands);
    }

    //根据商家类目id的获取商家所经营类目下的所有商品
    public function searchItem()
    {
        $shopId = $this->shopId;
        $catId = input::get('catId');
        $brandId = input::get('brandId');
        $keywords = input::get('searchname');
        $freepostageId = input::get('freepostageId');
        if($brandId)
        {
            $searchParams = array(
                'shop_id' => $shopId,
                'cat_id' => $catId,
                'brand_id' => $brandId,
                'search_keywords' =>$keywords,
                'page_size' => 1000,
            );
        }
        else
        {
            $searchParams = array(
                'shop_id' => $shopId,
                'cat_id' => $catId,
                'search_keywords' =>$keywords,
                'page_size' => 1000,
            );
        }

        $searchParams['fields'] = 'item_id,title,image_default_id,price,integral,blend_integral,blend_price';
        $itemsList = app::get('topshop')->rpcCall('item.search',$searchParams);
        $pagedata['itemsList'] = $itemsList['list'];
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $objMdlRecommend = app::get('sysitem')->model('recommend');
        $ret = $objMdlRecommend->getList('valid_item', array('shop_id'=>$shopId));
        $notEndItem = explode(',', $ret[0]['valid_item']);
        $pagedata['notEndItem'] = $notEndItem;
        return response::json($pagedata);
    }

}
