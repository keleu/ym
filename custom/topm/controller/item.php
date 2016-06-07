<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topm_ctl_item extends topm_controller {

    public function __construct($app)
    {
        parent::__construct();
        $this->setLayoutFlag('product');
    }

    private function __setting()
    {
        $setting['image_default_id']= kernel::single('image_data_image')->getImageSetting('item');
        return $setting;
    }

    public function index()
    {
        $itemId = intval(input::get('item_id'));
        if( empty($itemId) )
        {
            return redirect::action('topm_ctl_default@index');
        }

        if( userAuth::check() )
        {
            $pagedata['nologin'] = 1;
        }

        $pagedata['user_id'] = userAuth::id();

        $pagedata['image_default_id'] = $this->__setting();

        $params['item_id'] = $itemId;
        $params['use_platform'] = 1;
        $params['fields'] = "*,item_desc.wap_desc,item_count,item_store,item_status,sku,item_nature,spec_index";
        $detailData = app::get('topm')->rpcCall('item.get',$params);

        if($detailData[approve_status]!='onsale'||!$detailData)
        {
            $pagedata['error'] = "商品过期不存在";
            return $this->page('topm/items/error.html', $pagedata);
        }

        if(count($detailData['sku']) == 1)
        {
            $detailData['default_sku_id'] = array_keys($detailData['sku'])[0];
        }

        $detailData['valid'] = $this->__checkItemValid($detailData);

        if($detailData['use_platform'] != 2 && $detailData['use_platform'] != 0)
        {
            redirect::action('topm_ctl_item@index',array('item_id'=>$itemId))->send();exit;
        }
        //相册图片
        if( $detailData['list_image'] )
        {
            $detailData['list_image'] = explode(',',$detailData['list_image']);
        }

        //获取商品的促销信息
        $promotionInfo = app::get('topc')->rpcCall('item.promotion.get', array('item_id'=>$itemId));
        if($promotionInfo)
        {
            foreach($promotionInfo as $vp)
            {
                $basicPromotionInfo = app::get('topc')->rpcCall('promotion.promotion.get', array('promotion_id'=>$vp['promotion_id'], 'platform'=>'pc'));
                if($basicPromotionInfo['valid']===true)
                {
                    $pagedata['promotionDetail'][$vp['promotion_id']] = $basicPromotionInfo;
                }
            }
        }

        $pagedata['promotion_count'] = count($pagedata['promotionDetail']);

        // 活动促销(如名字叫团购)
        $activityDetail = app::get('topm')->rpcCall('promotion.activity.item.info',array('item_id'=>$itemId,'valid'=>1),'buyer');
        if($activityDetail)
        {
            $pagedata['activityDetail'] = $activityDetail;
        }

        $detailData['spec'] = $this->__getSpec($detailData['spec_desc'], $detailData['sku']);

        $pagedata['item'] = $detailData;

        $pagedata['shopCat'] = app::get('topm')->rpcCall('shop.cat.get',array('shop_id'=>$pagedata['item']['shop_id']));

        $pagedata['shop'] = app::get('topm')->rpcCall('shop.get',array('shop_id'=>$pagedata['item']['shop_id']));
        $pagedata['next_page'] = url::action("topm_ctl_item@index",array('item_id'=>$itemId));

        if(empty($pagedata['item']['item_id']))
        {
            return $this->page('topm/items/goodsEmpty.html');
        }

        //设置商品描述信息
        //写死了的开关，by li 2种展示模式，客户比较倾向模式1，模式0为默认，客户不喜欢
        $pagedata['item']['itemPic_status'] = 1 ;
        if($pagedata['item']['itemPic_status'] == 1){
            $params['fields'] = "*,item_desc.wap_desc,item_count,item_store,item_status,sku,item_nature,spec_index";
            $detailData = app::get('topm')->rpcCall('item.get',$params);
            $pagedata['itemPic'] = $detailData;
        }

        //设置此页面的seo
        $brand = app::get('topm')->rpcCall('category.brand.get.info',array('brand_id'=>$detailData['brand_id']));
        $cat = app::get('topm')->rpcCall('category.cat.get.info',array('cat_id'=>$detailData['cat_id']));
        $seoData = array(
            'item_title' => $detailData['title'],
            'shop_name' =>$pagedata['shop']['shop_name'],
            'item_bn' => $detailData['bn'],
            'item_brand' => $brand['brand_name'],
            'item_cat' =>$cat[$detailData['cat_id']]['cat_name'],
            'sub_title' =>$detailData['sub_title'],
        );
        seo::set('topm.item.detail',$seoData);

        //2016-3-15 by jianghui 查找商品是否已经收藏过该商品和收藏过该店铺
        $filter=array();
        $filter['user_id'] = $pagedata['user_id'];
        $filter['item_id'] = $pagedata['item']['item_id'];
        $filter['objectType'] = 'goods';
        $retitem = app::get('sysuser')->model('user_fav')->getRow('gnotify_id',$filter);
        $pagedata['is_item'] = count($retitem)>0?1:0;
        $retshop = app::get('sysuser')->model('shop_fav')->getRow('snotify_id',array('shop_id'=>$pagedata['item']['shop_id'],'user_id'=>$pagedata['user_id']));
        $pagedata['is_shop'] = count($retshop)>0?1:0;

        //2016-3-23 by jianghui 添加评价列表数据
        $pagedata['rateInfo'] = $this->__searchRate3($pagedata['item']['item_id']);
        $pagedata['is_rate'] = 1;  //先隐藏评价列表

        //2016-4-11 by jianghui 添加看了又看的数据
        $params=array(
                'shop_id' =>$pagedata['item']['shop_id']
            );
        $itemsList = app::get('sysitem')->rpcCall('item.get.recommend',$params);
        $pagedata['itemList'] = $itemsList['itemsList'];
        return $this->page('topm/items/index.html', $pagedata);
    }

    private function __checkItemValid($itemsInfo)
    {
        if( empty($itemsInfo) ) return false;

        //违规商品
        if( $itemsInfo['violation'] == 1 ) return false;

        //未启商品
        if( $itemsInfo['disabled'] == 1 ) return false;

        //未上架商品
        if($itemsInfo['approve_status'] == 'instock' ) return false;

        //库存小于或者等于0的时候，为无效商品
        //if($itemsInfo['realStore'] <= 0 ) return false;

        return true;
    }


    private function __getSpec($spec, $sku)
    {
        if( empty($spec) ) return array();

        foreach( $sku as $row )
        {
            $key = implode('_',$row['spec_desc']['spec_value_id']);

            if( $key )
            {
                $result['specSku'][$key]['sku_id'] = $row['sku_id'];
                $result['specSku'][$key]['item_id'] = $row['item_id'];
                $result['specSku'][$key]['price'] = $row['price'];
                $result['specSku'][$key]['integral'] = $row['integral'];
                $result['specSku'][$key]['blend'] = $row['blend'];
                $result['specSku'][$key]['store'] = $row['realStore'];
                $result['specSku'][$key]['mkt_price'] = $row['mkt_price'];
                if( $row['status'] == 'delete')
                {
                    $result['specSku'][$key]['valid'] = false;
                }
                else
                {
                    $result['specSku'][$key]['valid'] = true;
                }

                $specIds = array_flip($row['spec_desc']['spec_value_id']);
                $specInfo = explode('、',$row['spec_info']);
                foreach( $specInfo  as $info)
                {
                    $id = each($specIds)['value'];
                    $result['specName'][$id] = explode('：',$info)[0];
                }
            }
        }
        return $result;
    }
    //商品照片
    public function itemPic()
    {
        $itemId = intval(input::get('item_id'));
        if( empty($itemId) )
        {
            return redirect::action('topm_ctl_default@index');
        }

        $pagedata['image_default_id'] = $this->__setting();
        $params['item_id'] = $itemId;
        $params['fields'] = "*,item_desc.wap_desc,item_count,item_store,item_status,sku,item_nature,spec_index";
        $detailData = app::get('topm')->rpcCall('item.get',$params);
        $pagedata['title'] = "商品描述";

        $pagedata['itemPic'] = $detailData;
        return $this->page('topm/items/itempic.html', $pagedata);
    }
    //商品参数
    public function itemParams()
    {
        $itemId = intval(input::get('item_id'));
        if( empty($itemId) )
        {
            return redirect::action('topm_ctl_default@index');
        }

        $pagedata['image_default_id'] = $this->__setting();
        $params['item_id'] = $itemId;
        $params['fields'] = "*,item_desc.wap_desc,item_count,item_store,item_status,sku,item_nature,spec_index";
        $detailData = app::get('topm')->rpcCall('item.get',$params);

        $pagedata['itemParams'] = $detailData['params'];
        $pagedata['title'] = "商品参数";
        return $this->page('topm/items/itemparams.html', $pagedata);
    }

    public function getItemRate()
    {
        $itemId = input::get('item_id');
        if( empty($itemId) ) return '';

        $pagedata =  $this->__searchRate($itemId);
        $pagedata['item_id'] = $itemId;

        $pagedata['title'] = '产品评价';
        return $this->page('topm/items/rate/index.html', $pagedata);
    }

    public function getItemRateList()
    {
        $itemId = input::get('item_id');

        $pagedata =  $this->__searchRate($itemId);

        if( input::get('json') )
        {
            $data['html'] = view::make('topm/items/rate/list.html',$pagedata)->render();
            $data['pagers'] = $pagedata['pagers'];
            $data['success'] = true;
            return response::json($data);exit;
        }

        return view::make('topm/items/rate/list.html',$pagedata);
    }

    private function __searchRate($itemId)
    {
        $current = input::get('pages',1);
        $limit = 10;
        $params = ['item_id'=>$itemId,'page_no'=>$current,'page_size'=>$limit,'fields'=>'*'];

        if( input::get('query_type') == 'content'  )
        {
            $params['is_content'] = true;
        }
        elseif( input::get('query_type') == 'pic' )
        {
            $params['is_pic'] = true;
        }

        $data = app::get('topm')->rpcCall('rate.list.get', $params);
        foreach($data['trade_rates'] as $k=>$row )
        {
            if($row['rate_pic'])
            {
                $data['trade_rates'][$k]['rate_pic'] = explode(",",$row['rate_pic']);
            }

            $userId[] = $row['user_id'];
        }

        $pagedata['rate']= $data['trade_rates'];
        if( $userId )
        {
            $pagedata['userName'] = app::get('topm')->rpcCall('user.get.account.name',array('user_id'=>$userId),'buyer');
        }

        //处理翻页数据
        $filter = input::get();
        $pagedata['filter'] = $filter;
        $filter['pages'] = time();
        if($data['total_results']>0) $total = ceil($data['total_results']/$limit);
        $current = $total < $current ? $total : $current;
        $pagedata['pagers'] = array(
            //'link'=>url::action('topm_ctl_item@getItemRateList',$filter),
            'current'=>$current,
            'total'=>$total,
        );

        return $pagedata;
    }

    /**
    * ps ：3条默认评价
    * Time：2016/04/01 13:49:45
    * @author jianghui
    */
        
    private function __searchRate3($itemId)
    {
        $current = input::get('pages',1);
        $limit = 3;
        $params = ['item_id'=>$itemId,'page_no'=>$current,'page_size'=>$limit,'fields'=>'*'];

        if( input::get('query_type') == 'content'  )
        {
            $params['is_content'] = true;
        }
        elseif( input::get('query_type') == 'pic' )
        {
            $params['is_pic'] = true;
        }

        $data = app::get('topm')->rpcCall('rate.list.get', $params);
        foreach($data['trade_rates'] as $k=>$row )
        {
            if($row['rate_pic'])
            {
                $data['trade_rates'][$k]['rate_pic'] = explode(",",$row['rate_pic']);
            }

            $userId[] = $row['user_id'];
        }

        $pagedata['rate']= $data['trade_rates'];
        if( $userId )
        {
            $pagedata['userName'] = app::get('topm')->rpcCall('user.get.account.name',array('user_id'=>$userId),'buyer');
        }

        //处理翻页数据
        $filter = input::get();
        $pagedata['filter'] = $filter;
        $filter['pages'] = time();
        if($data['total_results']>0) $total = ceil($data['total_results']/$limit);
        $current = $total < $current ? $total : $current;
        $pagedata['pagers'] = array(
            //'link'=>url::action('topm_ctl_item@getItemRateList',$filter),
            'current'=>$current,
            'total'=>$total,
        );

        return $pagedata;
    }
}

