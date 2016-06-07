<?php
/**
 * 接口作用说明
 * ziti.show
 */
class syslogistics_api_ziti_searchZiti{

    public $apiDescription = '根据店铺获取自提列表';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'id' => ['type'=>'string','valid'=>'','description'=>'自提id，多个id用，隔开','example'=>'2,3,5,6','default'=>''],
            'shop_id' => ['type'=>'number','valid'=>'','description'=>'店铺id','example'=>'','default'=>''],
        );
        $return['extendsFields'] = ['ind_area_state_id','ind_area_city_id','area_district_id'];
        return $return;
    }

    private function __getFilter($params)
    {
        $filter['shop_id'] = $params['shop_id'];
        return $filter;
    }

    /**
    * ps ：分页获取自提数据
    * Time：2016/05/11 14:37:03
    * @author jianghui
    */
    public function getList($params)
    {
        $objMdlZiti = app::get('syslogistics')->model('ziti');

        $row = $params['fields']['rows']?$params['fields']['rows']:'*';

        //分页使用
        $pageSize = $params['page_size'] ? $params['page_size'] : 10;
        $pageNo = $params['page_no'] ? $params['page_no'] : 1;
        $max = 1000000;
        if($pageSize >= 1 && $pageSize < 500 && $pageNo >=1 && $pageSize*$pageNo < $max)
        {
            $limit = $pageSize;
            $page = ($pageNo-1)*$limit;
        }

        $orderBy = $params['orderBy'];
        if(!$params['orderBy'])
        {
            $orderBy = "id desc";
        }

        $objMdlZiti = app::get('syslogistics')->model('ziti');
        $count = $objMdlZiti->count($params);
        $data['list'] = $objMdlZiti->getList($row,$params,$page,$limit,$orderBy);
        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['areaName'] = area::getSelectArea($value['area']);
        }
        $data['count'] = $count;
        return $data;
    }
}
