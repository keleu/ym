<?php
class topm_ctl_member_aftersales extends topm_ctl_member{

    public $aftersalesReason = [
        '物实不符','质量原因','现在不想购买','商品价格较贵',
        '价格波动','商品缺货','重复下单','订单商品选择有误',
        '支付方式选择有误','收货信息填写有误','支付方式选择有误',
        '发票信息填写有误','其他原因',
    ];

    /*
     * 显示申请售后页面
     */
    public function aftersalesApply()
    {
        $this->setLayoutFlag('order_detail');

        $tid = input::get('tid');
        $oid = input::get('oid');
        $data = app::get('topm')->rpcCall('aftersales.verify',array('oid'=>$oid),'buyer');

        if( $data[$oid] != 'NOT_APPLY' )
        {
          //如果已经申请过 或者被驳回过则跳转到详情页
        }

        $pagedata['tid'] = $tid;
        $pagedata['oid'] = $oid;
        $pagedata['reason'] = $this->aftersalesReason;
        $pagedata['aftersales_type'] = input::get('type','ONLY_REFUND');
        $pagedata['title'] = "申请售后";

        return $this->page('topm/member/aftersales/apply.html' ,$pagedata);
    }

    public function exchange()
    {
        $tid = input::get('tid');
        $oid = input::get('oid');
        $tradeStatus = input::get('status');
        $data = app::get('topm')->rpcCall('aftersales.verify',array('oid'=>$oid),'buyer');
        if( $data[$oid] == 'NOT_APPLY' )
        {
            $pagedata['tid'] = $tid;
            $pagedata['oid'] = $oid;
            $pagedata['status'] = $tradeStatus;
        }
        else
        {
            //售后已申请 或者申请已驳回
        }

        $pagedata['title'] = "选择售后类型";
        return $this->page('topm/member/aftersales/exchange.html' ,$pagedata);
    }

    public function commitAftersalesApply()
    {
        try
        {
            $result = app::get('topm')->rpcCall('aftersales.apply', input::get(),'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

            $url = url::action('topm_ctl_member_trade@detail', ['tid' => input::get('tid')]);

        $msg = '售后申请提交成功';
        return $this->splash('success',$url,$msg,true);
    }

    public function sendback()
    {

        $postdata = input::get();
        $postdata['user_id'] = userAuth::id();
        if( !$postdata['corp_code'] )
        {
            $postdata['corp_code'] = 'other';
        }

        if($postdata['corp_code'] == "other" && !$postdata['logi_name'])
        {
            return $this->splash('error',"","请填写物流公司");
        }
        if(!$postdata['logi_no'])
        {
            return $this->splash('error',"","请填写物流单号",true);
        }

        try
        {
            $result = app::get('topm')->rpcCall('aftersales.send.back', $postdata,'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        $url = url::action('topm_ctl_member_aftersales@aftersalesDetail',['id'=>$postdata['aftersales_bn']]);

        $msg = '回寄物流信息提交成功';
        return $this->splash('success',$url,$msg);
    }

    public function goAftersalesDetail()
    {
        $params['oid'] = input::get('id');
        $params['fields'] = 'aftersales_bn';
        $result = app::get('topm')->rpcCall('aftersales.get.bn', $params);
        if(is_null($result))
        {
            redirect::action('topm_ctl_member_trade@tradeList')->send();exit;
        }
        redirect::action('topm_ctl_member_aftersales@aftersalesDetail',array('id'=>$result['aftersales_bn']))->send();exit;
    }

    public function aftersalesDetail()
    {

        $this->setLayoutFlag('order_detail');

        $params['aftersales_bn'] = input::get('id');
        $params['user_id'] = userAuth::id();
        $tradeFields = 'trade.status,trade.shop_id,trade.receiver_name,trade.user_id,trade.receiver_state,trade.receiver_city,trade.receiver_district,trade.receiver_address,trade.receiver_mobile,trade.receiver_phone,trade.buyer_area';
        $params['fields'] = 'aftersales_bn,aftersales_type,reason,sendback_data,sendconfirm_data,description,shop_explanation,admin_explanation,evidence_pic,created_time,oid,tid,num,progress,status,sku,ziti_id,ziti_addr,ziti_memo,'.$tradeFields;
        $result = app::get('topm')->rpcCall('aftersales.get', $params);
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

        $pagedata['info'] = $result;
        if( $result['progress'] == '1' )
        {
            //快递公司代码
            $params['fields'] = "corp_code,corp_name";
            $corpData = app::get('topm')->rpcCall('logistics.dlycorp.get.list',$params);
            $pagedata['corpData'] = $corpData['data'];
            $pagedata['aftersales_bn'] = $params['aftersales_bn'];
        }
        $pagedata['title'] = "售后详情";
        return $this->page('topm/member/aftersales/detail.html' ,$pagedata);
    }

    public function ajaxGetTrack()
    {
        $postData = input::get();
        $pagedata['track'] = app::get('topc')->rpcCall('logistics.tracking.get.hqepay',$postData);
        return view::make('topm/member/track.html', $pagedata);
    }
    /**
    * ps ：
    * Time：2016/05/09 21:07:48
    * @author jianghui
    */
    public function ajaxGetBack()
    {
        if($_POST['jihui']=='wuliu'){
            $pagedata['info']['aftersales_type'] = $_POST['aftersales_type'];
            return view::make('topm/member/aftersales/sendback.html', $pagedata);
        }else{
            $postData = $_POST;
            $area = explode(':',$postData['area']);
            $area = implode(',',explode('/',$postData['area']));
            $shop_id = $postData['shop_id'];
            $ziti = app::get('topm')->rpcCall('logistics.ziti.list',array('area_id'=>$area,'shop_id'=>$shop_id));
            foreach ($ziti as $key => $value) {
                $pagedata['data'][$value['id']]=$value;
            }
            $pagedata['ziti_id'] = $postData['ziti_id'];
            $pagedata['aftersales_bn'] = $_POST['id'];
            return view::make('topm/member/aftersales/shopziti.html', $pagedata);
        }

    }
    /**
    * ps ：保存自提点
    * Time：2016/05/10 08:46:19
    * @author jianghui
    */
     public function sendziti()
    {

        $postData = input::get();
        $ziti = app::get('topm')->rpcCall('logistics.ziti.get',array('id'=>$postData['ziti_id']));
        $postData['user_id'] = userAuth::id();
        $postData['ziti_addr'] = $ziti['area'].$ziti['addr'];
        try
        {
            $result = app::get('topm')->rpcCall('aftersales.send.ziti', $postData,'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        $url = url::action('topm_ctl_member_aftersales@aftersalesDetail',['id'=>$postData['aftersales_bn']]);

        $msg = '回寄自提信息提交成功';
        return $this->splash('success',$url,$msg);
    }
}
