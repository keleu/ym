<?php
/*********************************************************************\
*  Copyright (c) 1998-2013, TH. All Rights Reserved.
*  Author :liuxin
*  FName  :detail.php
*  Time   :2014/08/30 09:18:48
*  Remark :代理商结算明细
\*********************************************************************/
class sysagent_finder_settlement_detail {
	public function __construct($app)
    {
        $this->controller = app::get('sysagent')->controller('admin_agent');
        $this->app = $app;
        $this->db = db::connection();
        $this->column_settlement_fee_amount = app::get('sysagent')->_('结算金额');
        $this->column_agent_seller = app::get('sysagent')->_('卖出代理商');
        $this->column_agent_buyer = app::get('sysagent')->_('购买代理商');
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
        $agent_verify = app::get('sysagent')->getConf('sysagent_setting.agent_verify')?app::get('sysagent')->getConf('sysagent_setting.agent_verify'):'平台方';
        foreach($list as $k=>$row)
        {
            $info = $agentInfoList[$row['agent_seller']];
            $colList[$k] = $info['username']?$info['username']:$agent_verify;
        }
    }

    /**
     * ps ：同上
     * Time：2015/12/10 17:03:42
     * @author liuxin
    */
    public function column_agent_buyer(&$colList, $list)
    {
        $ids = array_column($list, 'agent_buyer');
        if( !$ids ) return $colList;

        $agentInfoList = app::get('sysagent')->model('agents')->getList('agent_id,username', array('agent_id'=>$ids));
        $agentInfoList = array_bind_key($agentInfoList,'agent_id');

        foreach($list as $k=>$row)
        {
            $info = $agentInfoList[$row['agent_buyer']];
            $colList[$k] = $info['username'];
        }
    }

    // public function column_settlement_fee_amount(&$colList, $list)
    // {
    //     foreach ($list as $key => $v) {
    //         $colList[$key] = $v['settlement_fee_amount'].'元';
    //     }
    // }

}