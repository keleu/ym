<?php
/*********************************************************************\
*  Copyright (c) 1998-2013, TH. All Rights Reserved.
*  Author :liuxin
*  FName  :settlement.php
*  Time   :2014/08/30 09:18:48
*  Remark :代理商结算
\*********************************************************************/
class sysagent_ctl_admin_settlement extends desktop_controller {
	function __construct($app){
        parent::__construct($app);
        $this->model = app::get('sysagent')->model('settlement');
        $this->db = db::connection();
    }

    /**
     * ps ：代理商结算汇总
     * Time：2015/12/10 14:05:30
     * @author liuxin
    */
    public function index()
    {
        return $this->finder('sysagent_mdl_settlement',array(
            'title' => app::get('sysagent')->_('代理商结算汇总'),
            'use_buildin_delete' => true,
        ));
    }

    public function _views()
    {
        $mdl_settlement = app::get('sysagent')->model('settlement');
        $sub_menu = array(
            0=>array('label'=>app::get('sysagent')->_('全部'),'optional'=>false,'filter'=>''),
            1=>array('label'=>app::get('sysagent')->_('非零交易代理商'),'optional'=>false,'filter'=>array('tradecount'=>'has')),
            2=>array('label'=>app::get('sysagent')->_('零交易代理商'),'optional'=>false,'filter'=>array('tradecount'=>'0')),
            // 3=>array('label'=>app::get('sysagent')->_('审核拒绝'),'optional'=>false,'filter'=>array('status'=>'2')),
            // 4=>array('label'=>app::get('sysagent')->_('审核停用'),'optional'=>false,'filter'=>array('status'=>'3')),
        );
        return $sub_menu;
    }
    /**
     * ps ：代理商结算明细
     * Time：2015/12/10 14:07:22
     * @author liuxin
    */
    public function detail()
    {
        return $this->finder('sysagent_mdl_settlement_detail',array(
            'title' => app::get('sysagent')->_('代理商结算明细'),
            'use_buildin_delete' => true,
            'use_view_tab' => false,
        ));
    }

    public function confirm($settlementNo)
    {
        $pagedata['settlement_no'] = $settlementNo;
        return $this->page('sysagent/admin/confirm.html', $pagedata);
    }

    public function doConfirm()
    {
        $this->begin("?app=sysagent&ctl=admin_settlement&act=index");
        $settlementNo = input::get('settlement_no');
        $status = input::get('settlement_status');
        try
        {
            kernel::single('sysagent_agent_point')->doConfirm($settlementNo, $status);
            $this->adminlog("确认结算单[分类ID:{$settlementNo}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("确认结算单[分类ID:{$settlementNo}]", 0);
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        $this->end(true);
    }

}