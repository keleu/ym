<?php
class topshop_ctl_shop_setting extends topshop_controller{

    public function index()
    {
        $shopdata = app::get('topshop')->rpcCall('shop.get',array('shop_id'=>shopAuth::getShopId()),'seller');
        $pagedata['shop'] = $shopdata;
        $this->contentHeaderTitle = app::get('topshop')->_('店铺设置');
        //2016-4-25 by jianghui 获取地区
        $areaMap = area::getMap();
        foreach ($areaMap as $key => $value) {
            $children = array();
            foreach ($value['children'] as $k => $v) {
                $children[$v['value']] = $v;
            }
            $value['children'] = $children;
            $pagedata['areaMap'][$value['value']] = $value;
        }
        return $this->page('topshop/shop/setting.html', $pagedata);
    }

    public function saveSetting()
    {
        $postData = input::get();
        $validator = validator::make(
            [$postData['shop_descript']],['max:200'],['店铺描述不能超过200个字符!']
        );
         //2016-4-27 by jianghui 添加填写完整的地区
        if($postData['company_area_first'] == "no" || $postData['company_area_second'] == "no" || $postData['company_area_third'] == "no"){
            $msg = app::get('topshop')->_("请填写完整的所在地区");
            return $this->splash('error',null,$msg);
        }
        $postData['shop_area'] = $postData['company_area_first'].$postData['company_area_second'].$postData['company_area_third'];
        $postData['company_area_third'] = $postData['company_area_third']?$postData['company_area_third']:'';

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
            $result = app::get('topshop')->rpcCall('shop.update',$postData);
            if( $result )
            {
                $msg = app::get('topshop')->_('设置成功');
                $result = 'success';
            }
            else
            {
                $msg = app::get('topshop')->_('设置失败');
                $result = 'error';
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $result = 'error';
        }
        $url = url::action('topshop_ctl_shop_setting@index');
        return $this->splash($result,$url,$msg,true);

    }
}


