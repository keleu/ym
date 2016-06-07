<?php
class systrade_api_trade_list{
    public $apiDescription = "获取订单列表";
    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单所属用户id'],
            'shop_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单所属店铺id'],
            'status' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单状态'],
            'buyer_rate' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单评价状态'],
            'tid' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单编号,多个用逗号隔开'],
            'create_time_start' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'查询指定时间内的交易创建时间开始yyyy-MM-dd'],
            'create_time_end' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'查询指定时间内的交易创建时间结束yyyy-MM-dd'],
            'receiver_mobile' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'收货人手机'],
            'receiver_phone' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'收货人电话'],
            'receiver_name' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'收货人姓名'],
            'user_name' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'会员用户名/手机号/邮箱'],

            'page_no' => ['type'=>'int','valid'=>'','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
            'page_size' =>['type'=>'int','valid'=>'','description'=>'分页每页条数(1<=size<=200)','example'=>'','default'=>'40'],
            'order_by' => ['type'=>'int','valid'=>'','description'=>'排序方式','example'=>'','default'=>'created_time desc'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'获取的交易字段集','example'=>'','default'=>''],
        );
        $return['extendsFields'] = ['order','activity'];
        return $return;
    }
    public function tradeList($params)
    {
        if($params['oauth']['account_id'] && $params['oauth']['auth_type'] == "member" )
        {
            $params['user_id'] = $params['oauth']['account_id'];
        }
        elseif($params['oauth']['account_id'] && $params['oauth']['auth_type'] == "shop")
        {
            $sellerId = $params['oauth']['account_id'];
            $params['shop_id'] = app::get('systrade')->rpcCall('shop.get.loginId',array('seller_id'=>$sellerId),'seller');
        }

        $tradeRow = $params['fields']['rows'];
        $orderRow = $params['fields']['extends']['order'];
        $activityRow = $params['fields']['extends']['activity'];

        //分页使用
        $pageSize = $params['page_size'] ? $params['page_size'] : 40;
        $pageNo = $params['page_no'] ? $params['page_no'] : 1;
        $max = 1000000;
        if($pageSize >= 1 && $pageSize < 500 && $pageNo >=1 && $pageNo < 200 && $pageSize*$pageNo < $max)
        {
            $limit = $pageSize;
            $page = ($pageNo-1)*$limit;
        }

        $orderBy = $params['orderBy'];
        if(!$params['orderBy'])
        {
            $orderBy = "created_time desc";
        }
        unset($params['fields'],$params['page_no'],$params['page_size'],$params['order_by'],$params['oauth']);

        foreach($params as $k=>$val)
        {
            if(!$val)
            {
                unset($params[$k]);
                continue;
            }
            if($k == "status" || $k == "tid")
            {
                $params[$k] = explode(',',$val);
            }
        }

        if( $params['create_time_start'] )
        {
            $params['created_time|bthan'] = $params['create_time_start'];
            unset($params['create_time_start']);
        }

        if( $params['create_time_end'] )
        {
            $params['created_time|lthan'] = $params['create_time_end'];
            unset($params['create_time_end']);
        }

        if( $params['user_name'] )
        {
            $userIds = app::get('systrade')->rpcCall('user.get.account.id',['user_name'=>$params['user_name']]);
            unset($params['user_name']);
            //增加判断，防止没有对应的会员数据时出错，by liuxin 2015-12-07
            if($userIds){
                $params['user_id'] = $userIds;
            }
            else{
                $params['user_id'] = '';
            }
        }

        $objMdlTrade = app::get('systrade')->model('trade');
        $count = $objMdlTrade->count($params);
        $tradeLists = $objMdlTrade->getList($tradeRow,$params,$page,$limit,$orderBy);
        $tradeLists = array_bind_key($tradeLists,'tid');
        if($orderRow && $tradeLists)
        {
            foreach ($tradeLists as $k => $v) {
                //2016-3-31 by jianghui 添加积分+现金总计
                $tradeLists[$k]['payment_blend'] = kernel::single('sysitem_blendShow')->totalshow($v['payment_integral'],$v['payment']);
            }
            
            $orderRow = str_append($orderRow,'tid');
            $objMdlOrder = app::get('systrade')->model('order');
            $tids = array_column($tradeLists,'tid');
            $orderLists = $objMdlOrder->getList($orderRow,array('tid'=>$tids));
            //是否需要显示标签促销tag
            if( $activityRow && $orderLists )
            {
                $oids = array_column($orderLists,'oid');
                $promotionActivityData = app::get('systrade')->model('promotion_detail')->getList('promotion_tag,oid',['promotion_type'=>'activity','oid'=>$oids]);
                //一个子订单只可参加一次标签促销活动
                $promotionActivityData = array_bind_key($promotionActivityData,'oid');
            }

            foreach($orderLists as $key=>$value)
            {
                //2016-3-31 by jianghui 添加积分+现金单价
                $value['blend'] = kernel::single('sysitem_blendShow')->totalshow($value['integral'],$value['price']);

                if( $promotionActivityData[$value['oid']]['promotion_tag'] )
                {
                    $value['promotion_tag'] = $promotionActivityData[$value['oid']]['promotion_tag'];
                }
                $tradeLists[$value['tid']]['order'][] = $value;
            }
        }

        $trade['list'] = $tradeLists;
        $trade['count'] = $count;
        return $trade;
    }
}



