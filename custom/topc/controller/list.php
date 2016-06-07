<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_list extends topc_controller {

    /**
     * 每页搜索多少个商品
     */
    public $limit = 20;

    /**
     * 最多搜索前100页的商品
     */
    public $maxPages = 100;


    public function index()
    {
        $objLibFilter = kernel::single('topc_item_filter');
        $params = $objLibFilter->decode(input::get());
        // dump($params);exit;
        //2016-4-27 by jianghui 获取访问地区
        if($params['company_area_first']){
            $company_area_first= $params['company_area_first'];
            $company_area_second = $params['company_area_second'];
            $company_area_third = $params['company_area_third'];
        }
        else{
            //先隐藏获取默认地区功能
            // $area = $this->getCity();//$_SERVER['REMOTE_ADDR'] 218.93.10.227常州 59.108.111.0 北京
            // $company_area_first= $area['province']==$area['city']?$area['province'].'市':$area['province'].'省';
            // $company_area_second = $area['province']==$area['city']?'':$area['city'].'市';
            // $params['company_area_first'] = $company_area_first;
            // $params['company_area_second'] = $company_area_second;
        }

        $params['use_platform'] = '0,1';
        //判断自营  自营是1，非自营是0
        if($params['is_selfshop']=='1')
        {
            $pagedata['isself'] = '0';
        }
        else
        {
            $pagedata['isself'] = '1';
        }

        //如果不是从分类进入，并且没有关键字搜索则不能进入列表页
        $params['search_keywords'] = trim($params['search_keywords']);
        if( empty($params['cat_id']) && empty($params['search_keywords']) )
        {
            return redirect::back();
        }

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        //搜索或者筛选获取商品
        $searchParams = $this->__preFilter($params);
        $searchParams['fields'] = 'item_id,title,image_default_id,price,promotion,integral,blend_integral,blend_price';
        $itemsList = app::get('topc')->rpcCall('item.search',$searchParams);

        //检测是否有参加团购活动
        if($itemsList['list'])
        {
            $itemsList['list'] = array_bind_key($itemsList['list'],'item_id');
            $itemIds = array_keys($itemsList['list']);
            $activityParams['item_id'] = implode(',',$itemIds);
            $activityParams['status'] = 'agree';
            $activityParams['end_time'] = 'bthan';
            $activityParams['start_time'] = 'sthan';
            $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
            $activityItemList = app::get('topc')->rpcCall('promotion.activity.item.list',$activityParams);
            if($activityItemList['list'])
            {
                foreach($activityItemList['list'] as $key=>$value)
                {
                    $itemsList['list'][$value['item_id']]['activity'] = $value;
                    $itemsList['list'][$value['item_id']]['activity_price'] = $value['activity_price'];
                }
            }
        }

        //根据条件搜索出最多商品的分类，进行显示渐进式筛选项
        $filterItems = app::get('topc')->rpcCall('item.search.filterItems',$params);
        //渐进式筛选的数据
        $pagedata['screen'] = $filterItems;

        $pagedata['items'] = $itemsList['list'];//根据条件搜索出的商品
        $pagedata['count'] = $itemsList['total_found']; //根据条件搜索到的总数

        //已有的搜索条件
        $tmpFilter = $params;
        unset($tmpFilter['pages']);
        $pagedata['filter'] = $objLibFilter->encode($tmpFilter);

        //分页
        $pagedata['pagers'] = $this->__pages($params['pages'], $pagedata['count'], $pagedata['filter']);

        //已选择的搜索条件
        $pagedata['activeFilter'] = $params;

        //2016-4-27 by jianghui 添加地区筛选
        $pagedata['screen']['area'] = true;
        $areaMap = area::getMap();
        foreach ($areaMap as $key => $value) {
            $children = array();
            foreach ($value['children'] as $k => $v) {
                $children[$v['value']] = $v;
            }
            $value['children'] = $children;
            $pagedata['areaMap'][$value['value']] = $value;
        }
        $pagedata['company_area_first'] = $company_area_first;
        $pagedata['company_area_second'] = $company_area_second;
        $pagedata['company_area_third'] = $company_area_third;
        return $this->page('topc/list/index.html', $pagedata);
    }

    /**
     * 将post过的数据转换为搜索需要的参数
     *
     * @param array $params
     */
    private function __preFilter($params)
    {
        $searchParams = $params;
        $searchParams['page_no'] = ($params['pages'] >=1 || $params['pages'] <= 100) ? $params['pages'] : 1;
        $searchParams['page_size'] = $this->limit;

        $searchParams['approve_status'] = 'onsale';
        $searchParams['buildExcerpts'] = true;

        if( $searchParams['brand_id'] && is_array($searchParams['brand_id']) )
        {
            $searchParams['brand_id'] = implode(',', $searchParams['brand_id']);
        }

        if( $searchParams['prop_index'] && is_array($searchParams['prop_index']) )
        {
            $searchParams['prop_index'] = implode(',', $searchParams['prop_index']);
        }

        //排序
        if( !$params['orderBy'] )
        {
            $params['orderBy'] = 'sold_quantity desc';
        }
        $searchparams['orderBy'] = $params['orderBy'];

        //2016-4-27 by jianghui 获取该地区的商家
        if($params['company_area_first']!='no' && !empty($params['company_area_first'])){
            $arrArea['company_area_first'] = $params['company_area_first'];
        }
        if($params['company_area_second']!='no' && !empty($params['company_area_second'])){
            $arrArea['company_area_second'] = $params['company_area_second'];
        }
        if($params['company_area_third']!='no' && !empty($params['company_area_third'])){
            $arrArea['company_area_third'] = $params['company_area_third'];
        }
        if($arrArea){
            $shopRow=app::get('sysshop')->model('shop')->getList('shop_id',$arrArea);
            $shopRow = array_column($shopRow,'shop_id');
            $searchParams['shop_id'] = join(',',$shopRow);
            $searchParams['shop_id'] = $searchParams['shop_id']?$searchParams['shop_id']:'no';
        }
        return $searchParams;
    }

    /**
     * 分页处理
     * @param int $current 当前页
     * @return int $total  总页数
     * @return array $filter 查询条件
     *
     * @return $pagers
     */
    private function __pages($current, $total, $filter)
    {
        //处理翻页数据
        $current = ($current || $current <= 100 ) ? $current : 1;
        $filter['pages'] = time();

        if( $total > 0 ) $totalPage = ceil($total/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_list@index',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'token'=>time(),
        );
        return $pagers;
    }

    /**
    * ps ：获取地区
    * Time：2016/04/27 13:10:31
    * @author jianghui
    */
    function getCity($ip = '')
    {
        if($ip){
            $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=".$ip;
            $ip=json_decode(file_get_contents($url),true);
            $data = $ip;
        }else{
            $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json";
            $ip=json_decode(file_get_contents($url),true);
            $data = $ip;
        }

        return $data;
    }
}

