<?php
class topc_ctl_member_aftersales extends topc_ctl_member{

    /*
     * 显示申请售后页面
     */
    public function aftersalesApply()
    {
        $tid = input::get('tid');
        $oid = input::get('oid');
        $data = app::get('topc')->rpcCall('aftersales.verify',array('oid'=>$oid),'buyer');

        try
        {
            $tradeFiltr = array(
                'tid' => $tid,
                'oid' => $oid,
                'fields' => 'tid,user_id,shop_id,ziti_addr,ziti_id,status,receiver_state,receiver_city,receiver_district,receiver_name,receiver_mobile,receiver_address,created_time,orders.oid,orders.user_id,orders.sku_id,orders.num,orders.sendnum,orders.title,orders.pic_path,orders.price,orders.payment,orders.spec_nature_info,payment_integral,orders.integral,orders.payment_integral',
            );
            $pagedata['tradeInfo']= app::get('topc')->rpcCall('trade.get', $tradeFiltr,'buyer');
        }
        catch(\LogicException $e)
        {
            //
        }
        $this->action_view = "aftersales/apply.html";
        return $this->output($pagedata);
    }

    public function commitAftersalesApply()
    {
        $postdata = input::get();
        $validator = validator::make(
            ['reason'=>$postdata['reason']],
            ['reason'=>'required'],
            ['reason'=>'取消原因必选!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        try
        {
            $result = app::get('topc')->rpcCall('aftersales.apply', input::get(),'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        $url = url::action('topc_ctl_member_aftersales@aftersalesList');

        $msg = '售后申请提交成功';
        return $this->splash('success',$url,$msg,true);
    }

    public function aftersalesList()
    {
        $params['user_id'] = userAuth::id();
        $params['page_no'] = input::get('pages',1);
        $params['page_size'] = 10;
        $params['fields'] = 'aftersales_bn,aftersales_type,created_time,oid,tid,num,progress,status,sku';
        $result = app::get('topc')->rpcCall('aftersales.list.get', $params,'buyer');

        //2016-5-9 by jianghui 获取订单信息
        foreach ($result['list'] as $key => $value) {
            $tradeFiltr['tid'] = $value['tid'];
            $tradeFiltr['oid'] = $value['oid'];
            $tradeFiltr['fields'] = 'shop_id,buyer_area';
            $tradeData = app::get('sysaftersales')->rpcCall('trade.get', $tradeFiltr);
            $result['list'][$key]['shop_id'] = $tradeData['shop_id'];
            $result['list'][$key]['buyer_area'] = $tradeData['buyer_area'];
            //增加自提点判断
            $objMdlShop = app::get('sysshop')->model('shop');
            $result['list'][$key]['is_ziti'] = $objMdlShop->getRow('is_ziti',array('shop_id'=>$tradeData['shop_id']))['is_ziti'];
        }
        $pagedata['list'] = $result['list'];

        //处理翻页数据
        $filter['pages'] = time();
        if($result['total_found']>0) $total = ceil($result['total_found']/10);
        $current = input::get('pages',1);
        $current = $total < $current ? $total : $current;
        $pagedata['pagers'] = array(
            'link'=>url::action('topc_ctl_member_aftersales@aftersalesList',$filter),
            'current'=>$current,
            'total'=>$total,
            'use_app'=>'topc',
            'token'=>$filter['pages'],
        );
        $pagedata['action'] = 'topc_ctl_member_aftersales@aftersalesList';
        $this->action_view = "aftersales/list.html";
        return $this->output($pagedata);
    }

    public function sendback()
    {
        $postdata = input::get();
        $postdata['user_id'] = userAuth::id();
        if($postdata['corp_code'] == "other" && !$postdata['logi_name'])
        {
            return $this->splash('error',"","其他物流公司不能为空",true);
        }
        if(!$postdata['logi_no']) return $this->splash('error',"","运单号不可为空",true);
        // if(!$postdata['mobile']) return $this->splash('error',"","收货人手机不可为空",true);
        // if(!$postdata['receiver_address']) return $this->splash('error',"","收货地址不可为空",true);

        try
        {
            $result = app::get('topc')->rpcCall('aftersales.send.back', $postdata,'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        $url = url::action('topc_ctl_member_aftersales@aftersalesList');

        $msg = '回寄物流信息提交成功';
        return $this->splash('success',$url,$msg,true);
    }

    public function goAftersalesDetail()
    {
        $params['oid'] = input::get('id');
        $params['fields'] = 'aftersales_bn';
        $result = app::get('topc')->rpcCall('aftersales.get.bn', $params);
        if(is_null($result))
        {
            redirect::action('topc_ctl_member_trade@tradeList')->send();exit;
        }
        redirect::action('topc_ctl_member_aftersales@aftersalesDetail',array('id'=>$result['aftersales_bn']))->send();exit;
    }

    public function aftersalesDetail()
    {
        $params['aftersales_bn'] = input::get('id');
        $params['user_id'] = userAuth::id();
        $tradeFields = 'trade.status,trade.shop_id,trade.ziti_addr,trade.ziti_id,trade.receiver_name,trade.user_id,trade.receiver_state,trade.receiver_city,trade.receiver_district,trade.receiver_address,trade.receiver_mobile,trade.receiver_phone,trade.created_time,trade.payment_integral,trade.buyer_area';
        $params['fields'] = 'aftersales_bn,aftersales_type,reason,sendback_data,sendconfirm_data,description,shop_explanation,admin_explanation,evidence_pic,created_time,oid,tid,num,progress,status,sku,ziti_id,ziti_addr,ziti_memo,'.$tradeFields;
        $result = app::get('topc')->rpcCall('aftersales.get', $params);
        $result['evidence_pic'] = $result['evidence_pic'] ? explode(',',$result['evidence_pic']) : null;
        $result['sendback_data'] = $result['sendback_data'] ? unserialize($result['sendback_data']) : null;
        $result['sendconfirm_data'] = $result['sendconfirm_data'] ? unserialize($result['sendconfirm_data']) : null;

        if( $result['sendback_data']['corp_code']  && $result['sendback_data']['corp_code'] != "other")
        {
            $logiData = explode('-',$result['sendback_data']['corp_code']);
            $result['sendback_data']['corp_code'] = $logiData[0];
            $result['sendback_data']['logi_name'] = $logiData[1];
       }

        if( $result['sendconfirm_data']['corp_code'] && $result['sendconfirm_data']['corp_code'] != "other")
        {
            $logiData = explode('-',$result['sendconfirm_data']['corp_code']);
            $result['sendconfirm_data']['corp_code'] = $logiData[0];
            $result['sendconfirm_data']['logi_name'] = $logiData[1];
        }
        //增加自提点开启判断
        $objMdlShop = app::get('sysshop')->model('shop');
        $result['is_ziti'] = $objMdlShop->getRow('is_ziti',array('shop_id'=>$result['trade']['shop_id']))['is_ziti'];

        $pagedata['info'] = $result;
        $this->action_view = "aftersales/detail.html";
        return $this->output($pagedata);
    }

    public function ajaxLogistics()
    {
        //快递公司代码
        $params['fields'] = "corp_code,corp_name";
        $corpData = app::get('topc')->rpcCall('logistics.dlycorp.get.list',$params);
        $pagedata['corpData'] = $corpData['data'];
        $pagedata['aftersales_bn'] = input::get('id');
        $pagedata['aftersales_type'] = input::get('aftersales_type');
        return view::make('topc/member/gather/logistics.html', $pagedata);
    }
    /**
    * ps ：选择自提点
    * Time：2016/05/09 15:08:21
    * @author jianghui
    */
    public function ajaxZiti()
    {
        $postData = input::get();
        $area = explode(':',$postData['area']);
        $area = implode(',',explode('/',$postData['area']));
        $shop_id = $postData['shop_id'];
        $pagedata['data'] = app::get('topc')->rpcCall('logistics.ziti.list',array('area_id'=>$area,'shop_id'=>$shop_id));
        $pagedata['ziti_id'] = $postData['ziti_id'];
        $pagedata['aftersales_bn'] = input::get('id');
        return view::make('topc/member/gather/shopZiti.html', $pagedata);
    }

    /**
    * ps ：保存自提点
    * Time：2016/05/09 15:45:32
    * @author jianghui
    */
    public function sendZiti(){
        $postData = input::get();
        $ziti = app::get('topc')->rpcCall('logistics.ziti.get',array('id'=>$postData['ziti_id']));
        $postData['user_id'] = userAuth::id();
        $postData['ziti_addr'] = $ziti['area'].$ziti['addr'];
        try
        {
            $result = app::get('topc')->rpcCall('aftersales.send.ziti', $postData,'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
        $url = url::action('topc_ctl_member_aftersales@aftersalesList');
        $msg = $ziti_id;
        return $this->splash('success',$url,$msg,true);
    }
}
