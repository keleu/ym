<?php

/**
 * @brief 商家商品管理
 */
class topshop_ctl_item extends topshop_controller {

    public $limit = 10;

    public function add()
    {
        //$pagedata['return_to_url'] = request::server('HTTP_REFERER');
        $pagedata['shopCatList'] = app::get('topshop')->rpcCall('shop.cat.get',array('shop_id'=>$this->shopId,'fields'=>'cat_id,cat_name,is_leaf,parent_id,level'));
        $pagedata['shopId'] = $this->shopId;

        $this->contentHeaderTitle = app::get('topshop')->_('添加商品');
        return $this->page('topshop/item/edit.html', $pagedata);
    }

    public function edit()
    {
        //$pagedata['return_to_url'] = request::server('HTTP_REFERER');
        $itemId = input::get('item_id');
        $pagedata['shopId'] = $this->shopId;

        //如果是申请审核的时候不能编辑商品
        $objMdlStatus = app::get('sysitem')->model('item_status');
        $statusInfo = $objMdlStatus->getRow('*', array('item_id'=>$itemId));
        if($statusInfo['approve_status']=='check')
        {
            echo '<meta charset="utf-8">
                <script>alert("审核中的商品不能编辑！");
                    window.history.go(-1);
                </script>';
            exit;
        }

        // 店铺关联的商品品牌列表
        // 商品详细信息
        $params['item_id'] = $itemId;
        $params['shop_id'] = $this->shopId;
        $params['fields'] = "*,sku,item_store,item_status,item_count,item_desc,item_nature,spec_index";
        $pagedata['item'] = app::get('topshop')->rpcCall('item.get',$params);
        //2016-1-15 by jiang 处理混合价
        if($pagedata['item']['blend_integral']>0)
        {
            $pagedata['item']['blend']=$pagedata['item']['blend_integral'].'+'.$pagedata['item']['blend_price'];
        }
        // 商家分类及此商品关联的分类标示selected
        $scparams['shop_id'] = $this->shopId;
        $scparams['fields'] = 'cat_id,cat_name,is_leaf,parent_id,level';
        $pagedata['shopCatList'] = app::get('topshop')->rpcCall('shop.cat.get',$scparams);
        $selectedShopCids = explode(',', $pagedata['item']['shop_cat_id']);
        foreach($pagedata['shopCatList'] as &$v)
        {
            if($v['children'])
            {
                foreach($v['children'] as &$vv)
                {
                    if(in_array($vv['cat_id'], $selectedShopCids))
                    {
                        $vv['selected'] = true;
                    }
                }
            }
            else
            {
                if(in_array($v['cat_id'], $selectedShopCids))
                {
                    $v['selected'] = true;
                }
            }
        }
        $this->contentHeaderTitle = app::get('topshop')->_('添加商品');
        return $this->page('topshop/item/edit.html', $pagedata);
    }

    public function itemList()
    {
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $status = input::get('status',false);
        $pages =  input::get('pages',1);
        $pagedata['status'] = $status;
        $filter = array(
            'shop_id' => $this->shopId,
            'approve_status' => $status,
            'page_no' =>$pages,
            'page_size' => $this->limit,
        );
        $shopCatId = input::get('shop_cat_id',false);
        if( $shopCatId )
        {
            $filter['shop_cat_id'] = $shopCatId;
        }

        $filter['fields'] = 'item_id,list_time,modified_time,title,image_default_id,price,integral,blend_integral,blend_price,approve_status,store,check_res,comment,shenhe_num,is_Adjusted';
        $itemsList = app::get('topshop')->rpcCall('item.search',$filter);


        $pagedata['item_list'] = $itemsList['list'];
        $pagedata['total'] = $itemsList['total_found'];


        $totalPage = ceil($itemsList['total_found']/$this->limit);
        $pagersFilter['pages'] = time();
        $pagersFilter['status'] = $status;
        $pagers = array(
            'link'=>url::action('topshop_ctl_item@itemList',$pagersFilter),
            'current'=>$pages,
            'use_app' => 'topshop',
            'total'=>$totalPage,
            'token'=>time(),
        );
        $pagedata['pagers'] = $pagers;
        // dump($pagedata);exit;
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $this->contentHeaderTitle = app::get('topshop')->_('商品列表');
        return $this->page('topshop/item/list.html', $pagedata);
    }
    /**
    * ps ：货品打印列表
    * Time：2015/12/28 09:24:35
    * @author jiang
    */

    public function itemPrint()
    {
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        $status = 'onsale';
        $pages =  input::get('pages',1);
        $pagedata['status'] = $status;
        $filter = array(
            'shop_id' => $this->shopId,
            'approve_status' => $status,
            'page_no' =>$pages,
            'page_size' => $this->limit,
        );
        $shopCatId = input::get('shop_cat_id',false);
        if( $shopCatId )
        {
            $filter['shop_cat_id'] = $shopCatId;
        }

        $filter['fields'] = 'item_id,list_time,modified_time,title,image_default_id,price,integral,approve_status,store,check_res,comment,shenhe_num,is_Adjusted,blend_integral,blend_price';
        $itemsList = app::get('topshop')->rpcCall('item.search',$filter);
        $retItem=array();
        foreach ($itemsList['list'] as & $i) {
            $retItem[$i['item_id']]=$i;
            $itemsList['item_ids'][]=$i['item_id'];
        }

        $item_ids=implode(',',(array)$itemsList['item_ids']);
        //查找货品
        $sql = "select * from sysitem_sku where item_id in ({$item_ids})";
        if($item_ids){
            $arr=db::connection()->fetchAll($sql);
            $temp_sku=array();
            foreach ($arr as $key => $value) {
                $temp_v=array_merge($value,$retItem[$value['item_id']]);
                $temp_sku[]=$temp_v;
            }
        }
        // dump($temp_sku);exit;
        $pagedata['item_list']=$temp_sku;
        $pagedata['total'] = count($temp_sku);


        $totalPage = ceil($itemsList['total_found']/$this->limit);
        $pagersFilter['pages'] = time();
        $pagersFilter['status'] = $status;
        $pagers = array(
            'link'=>url::action('topshop_ctl_item@itemPrint',$pagersFilter),
            'current'=>$pages,
            'use_app' => 'topshop',
            'total'=>$totalPage,
            'token'=>time(),
        );
        $pagedata['pagers'] = $pagers;
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $this->contentHeaderTitle = app::get('topshop')->_('商品列表');
        return $this->page('topshop/item/print.html', $pagedata);
    }
    /**
    * ps ：货品打印
    * Time：2015/12/28 09:24:54
    * @author jiang
    */
    public function getPrint(){
        $postData = input::get();
        $itemId = $postData['sku_id'];
        $itemIds=explode(',',$itemId);
        $objSku = app::get('sysitem')->model('sku');
        foreach ($itemIds as $key => &$value) {
            $SkuInfo[] = $objSku->getList('bn', array('sku_id'=>$value));
        }
        foreach ($SkuInfo as $key => &$vv) {
             $pagedata['qrCode_t'][$key]['barcode']=$vv[0]['bn'];
             $pagedata['qrCode_t'][$key]['code']= kernel::single('topshop_barcode_Barcode')->makeBarcode($vv[0]['bn']);
        }
        return view::make('topshop/item/getPrint.html', $pagedata);
    }
    //商品搜所
    public function searchItem()
    {
        $filter = input::get();
        if($filter['min_price']&&$filter['max_price'])
        {
            if($filter['min_price']>$filter['max_price'])
            {
                $msg = app::get('topshop')->_('最大值不能小于最小值！');
                return $this->splash('error', null, $msg, true);
            }
        }
        $pages =  input::get('pages',1);
        $params = array(
            'shop_id' => $this->shopId,
            'search_keywords' => $filter['item_title'],
            'min_price' => $filter['min_price'],
            'max_price' => $filter['max_price'],
            'page_no' =>$pages,
            'page_size' => $this->limit,
        );

        if($filter['use_platform'] >= 0)
        {
            $params['use_platform'] = $filter['use_platform'];
        }
        // by zhangyan 2015-12-16 商家搜索商品状态和发布时间都应该有
        $params['fields'] = 'item_id,title,image_default_id,integral,price,approve_status,store,list_time,check_res,modified_time,blend_integral,blend_price';
        $itemsList = app::get('topshop')->rpcCall('item.search',$params);

        $pagedata['item_list'] = $itemsList['list'];
        $pagedata['total'] = $itemsList['total_found'];


        $totalPage = ceil($itemsList['total_found']/$this->limit);
        $pagersFilter['min_price'] = $filter['min_price'];
        $pagersFilter['max_price'] = $filter['max_price'];
        $pagersFilter['use_platform'] = $filter['use_platform'];
        $pagersFilter['item_title'] = $filter['item_title'];
        $pagersFilter['item_cat'] = $filter['item_cat'];
        $pagersFilter['item_no'] = $filter['item_no'];
        if(isset($filter['status']))
        {
            $pagersFilter['status'] =  $filter['status'];
        }
        $pagersFilter['pages'] = time();
        $pagers = array(
            'link'=>url::action('topshop_ctl_item@searchItem',$pagersFilter),
            'current'=>$pages,
            'use_app' => 'topshop',
            'total'=>$totalPage,
            'token'=>time(),
        );
        $pagedata['pagers'] = $pagers;
        $pagedata['filter']=$filter;
        $this->contentHeaderTitle = app::get('topshop')->_('商品列表');
        return $this->page('topshop/item/list.html', $pagedata);

    }
    /**
    * ps ：货品搜索
    * Time：2015/12/28 13:53:45
    * @author jiang
    */

    public function searchSku()
    {
        $filter = input::get();
        if($filter['min_price']&&$filter['max_price'])
        {
            if($filter['min_price']>$filter['max_price'])
            {
                $msg = app::get('topshop')->_('最大值不能小于最小值！');
                return $this->splash('error', null, $msg, true);
            }
        }
        $pages =  input::get('pages',1);
        $params = array(
            'shop_id' => $this->shopId,
            'search_keywords' => $filter['item_title'],
            'min_price' => $filter['min_price'],
            'max_price' => $filter['max_price'],
            'page_no' =>$pages,
            'page_size' => $this->limit,
        );

        if($filter['use_platform'] >= 0)
        {
            $params['use_platform'] = $filter['use_platform'];
        }
        // by zhangyan 2015-12-16 商家搜索商品状态和发布时间都应该有
        $params['fields'] = 'item_id,title,image_default_id,integral,price,approve_status,store,list_time,check_res';
        $itemsList = app::get('topshop')->rpcCall('item.search',$params);
        $retItem=array();
        foreach ($itemsList['list'] as & $i) {
            $retItem[$i['item_id']]=$i;
            $itemsList['item_ids'][]=$i['item_id'];
        }

        $item_ids=implode(',',(array)$itemsList['item_ids']);
        //查找货品
        $sql = "select * from sysitem_sku where item_id in ({$item_ids})";
        if($item_ids){
            $arr=db::connection()->fetchAll($sql);
            $temp_sku=array();
            foreach ($arr as $key => $value) {
                $temp_v=array_merge($value,$retItem[$value['item_id']]);
                $temp_sku[]=$temp_v;
            }
        }
        // dump($temp_sku);exit;
        $pagedata['item_list']=$temp_sku;
        $pagedata['total'] = count($temp_sku);


        $totalPage = ceil($itemsList['total_found']/$this->limit);
        $pagersFilter['pages'] = time();
        $pagers = array(
            'link'=>url::action('topshop_ctl_item@itemList',$pagersFilter),
            'current'=>$pages,
            'use_app' => 'topshop',
            'total'=>$totalPage,
            'token'=>time(),
        );
        $pagedata['pagers'] = $pagers;
        $pagedata['filter']=$filter;
        $this->contentHeaderTitle = app::get('topshop')->_('商品列表');
        return $this->page('topshop/item/list.html', $pagedata);

    }
    public function storeItem()
    {
        $postData = input::get();
        $postData['item']['shop_id'] = $this->shopId;
        $postData['item']['cat_id'] = $postData['cat_id'];
        $postData['item']['approve_status'] = 'instock';
        $postData['item']['shop_cat_id'] = implode(',', $postData['item']['shop_cids']);
         //判断店铺是不是自营店铺 gongjiapeng
        $selfShopType = app::get('topshop')->rpcCall('shop.get',array('shop_id'=>$this->shopId));
        if($selfShopType['shop_type']=='self')
        {
            $postData['item']['is_selfshop'] = 1;
        }
        try
        {
            $postData = $this->_checkPost($postData);
            $result = app::get('topshop')->rpcCall('item.create',$postData);
            //$url = $postData['return_to_url'];
            $url = url::action('topshop_ctl_item@itemList');
            $msg = app::get('topshop')->_('保存成功');
            return $this->splash('success', $url, $msg, true);
        }
        catch (Exception $e)
        {
            return $this->splash('error', '', $e->getMessage(), true);
        }
    }
    private function _checkPost($postData)
    {
        if(!$postData['item']['mkt_price'])
        {
            $postData['item']['mkt_price'] = 0;
        }
        if(!$postData['item']['cost_price'])
        {
            $postData['item']['cost_price'] = 0;
        }
        if(!$postData['item']['weight'])
        {
            $postData['item']['weight'] = 0;
        }
        if(!$postData['item']['order_sort'])
        {
            $postData['item']['order_sort'] = 1;
        }
        /*
        if(mb_strlen($postData['item']['title'],'UTF8') > 30)
        {
            throw new Exception('商品名称至多30个字符');
        }
         */
        return $postData;
    }


    public function setItemStatus(){

        $postData = input::get();
        try
        {
            if(!$itemId = $postData['item_id'])
            {
                $msg = app::get('topshop')->_('商品id不能为空');
                return $this->splash('error',null,$msg,true);
            }

            if($postData['type'] == 'tosale')
            {
                $shopdata = app::get('topshop')->rpcCall('shop.get',array('shop_id'=>$this->shopId),'seller');
                if( empty($shopdata) || $shopdata['status'] == "dead" )
                {
                    $msg = app::get('topshop')->_('抱歉，您的店铺处于关闭状态，不能发布(上架)商品');
                    return $this->splash('error',null,$msg,true);
                }
                $status = 'onsale';
                $msg = app::get('topshop')->_('上架成功');
            }
            elseif($postData['type'] == 'tostock')
            {
                $status = 'instock';
                $msg = app::get('topshop')->_('下架成功');
            }
            elseif($postData['type'] == 'checkout')
            {
                $status = 'check';
                $check_res = 'active';
                $msg = app::get('topshop')->_('申请审核成功');
            }
            else
            {
                return $this->splash('error',null,'非法操作!', true);
            }

            $params['item_id'] = intval($itemId);
            $params['shop_id'] = intval($this->shopId);
            $params['approve_status'] = $status;
            $params['check_res'] = $check_res;
            // dump($params);die;
            app::get('topshop')->rpcCall('item.sale.status',$params);
            $queue_params['item_id'] = intval($itemId);
            $queue_params['shop_id'] = intval($this->shopId);
            //发送到货通知的邮件
            if($status == "onsale")
            {
                system_queue::instance()->publish('sysitem_tasks_userItemNotify', 'sysitem_tasks_userItemNotify', $queue_params);
            }
            $url = url::action('topshop_ctl_item@itemList');
            return $this->splash('success', $url, $msg, true);
        }
        catch(Exception $e)
        {
            return $this->splash('error',null,$e->getMessage(), true);
        }
    }

    public function deleteItem()
    {
        $postData = input::get();
        try
        {
            if(!$itemId = $postData['item_id'])
            {
                $msg = app::get('topshop')->_('商品id不能为空');
                return $this->splash('error',null,$msg, true);
            }
            app::get('topshop')->rpcCall('item.delete',array('item_id'=>intval($itemId),'shop_id'=>intval($this->shopId)));
        }
        catch(Exception $e)
        {
            return $this->splash('error',null, $e->getMessage(), true);
        }
        return $this->splash('success',null,'删除成功', true);
    }

    public function ajaxGetBrand($cat_id)
    {
        $params['shop_id'] = $this->shopId;
        $params['cat_id'] = input::get('cat_id');
        try
        {
            $brand = app::get('topshop')->rpcCall('category.get.cat.rel.brand',$params);
        }
        catch(Exception $e)
        {
            return $this->splash('error',null, $e->getMessage(), true);
        }
        return response::json($brand);exit;
    }
}


