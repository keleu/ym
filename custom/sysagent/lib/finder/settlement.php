<?php
/*********************************************************************\
*  Copyright (c) 1998-2013, TH. All Rights Reserved.
*  Author :liuxin
*  FName  :detail.php
*  Time   :2014/08/30 09:18:48
*  Remark :代理商结算明细
\*********************************************************************/
class sysagent_finder_settlement {
    public $column_edit = '操作';
    public $column_edit_order = 1;
    public $column_edit_width = 60;

	public function __construct($app)
    {
        $this->controller = app::get('sysagent')->controller('admin_settlement');
        $this->app = $app;
        $this->db = db::connection();
        $this->column_agent_seller = app::get('sysagent')->_('所属代理商');
    }

    /**
     * ps ：代理商id列重定义
     * Time：2015/12/10 17:03:25
     * @author liuxin
    */
    public function column_agent_seller(&$colList, $list)
    {
        $ids = array_column($list, 'agent_seller');
        if( !$ids ) return $colList;

        $agentInfoList = app::get('sysagent')->model('agents')->getList('agent_id,username', array('agent_id'=>$ids));
        $agentInfoList = array_bind_key($agentInfoList,'agent_id');

        foreach($list as $k=>$row)
        {
            $info = $agentInfoList[$row['agent_seller']];
            $colList[$k] = $info['username'];
        }
    }

    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            if($row['settlement_status']=='2')
            {
                $colList[$k] = '已结算';
            }
            else
            {
                $url = '?app=sysagent&ctl=admin_settlement&act=confirm&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['settlement_no'];
                $target = 'dialog::{title:\''.app::get('sysagent')->_('结算确认').'\', width:300, height:200}';
                $title = app::get('sysagent')->_('结算确认');
                $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
            }
            
        }
    }

}