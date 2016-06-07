<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class  sysstat_ctl_admin_operating extends desktop_controller
{
    public function index()
    {
        //kernel::single('sysstat_tasks_operatorday')->exec();exit();
        $params = array(
            'time_start'=>date('Y-m-d 00:00:00', strtotime('-1 day')),
            'time_end'=>date('Y-m-d 23:59:59', strtotime('-1 day'))
        );
        $pagedata['time_start'] = $params['time_start'];
        $pagedata['time_end'] = $params['time_end'];

        //echo '<pre>';print_r($pagedata);exit();
        return $this->page('sysstat/admin/report/operat.html',$pagedata);
    }

    //报表
    public function analysis()
    {
        $data = input::get();
        $pagedata = $this->_getPageData($data);
        return view::make('sysstat/admin/report/operatAnalysis.html',$pagedata);
    }

    //异步请求获取的数据
    public function ajaxData()
    {
        $data = input::get();
        try
        {
            $pagedata = $this->_getPageData($data);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return  response::json($pagedata);
    }
     //异步请求时间获取的数据
    public function ajaxTimeData()
    {
        $data = input::get();
        //echo '<pre>';print_r($data);exit();
        try
        {
            $pagedata = $this->_getPageData($data);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return  response::json($pagedata) ;
    }

    /**
     * 计算经营概况页面需要的订单关联数据
     */
    protected function _getPageData($data){
        $pagedata = kernel::single('sysstat_desktop_tradeData')->getCommonData($data);
        $pagedata['operatTradeData'] = kernel::single('sysstat_desktop_tradeData')->getOperatData($data);
        $pagedata['operatUserData'] = kernel::single('sysstat_desktop_userListData')->getUserOperatData($data);

        // //2016-3-30 by jianghui 添加积分+现金总计
        // $pagedata['operatTradeData']['new_blend'] = kernel::single('sysitem_blendShow')->totalshow($pagedata['operatTradeData']['new_integral'],$pagedata['operatTradeData']['new_fee']);

        // $pagedata['operatTradeData']['complete_blend'] = kernel::single('sysitem_blendShow')->totalshow($pagedata['operatTradeData']['complete_integral'],$pagedata['operatTradeData']['complete_fee']);

        // $pagedata['operatTradeData']['refunds_blend'] = kernel::single('sysitem_blendShow')->totalshow($pagedata['operatTradeData']['refunds_integral'],$pagedata['operatTradeData']['refunds_fee']);
        return $pagedata;
    }

}
