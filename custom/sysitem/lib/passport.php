<?php

class sysitem_passport
{

    public $agentId = null;

    public $userName = null;

    public function __construct()
    {
        $this->app = app::get('sysitem');
    }

    /**
     * 获取当前代理商用户基本信息
     *
     * @param int $agentId 如果agentId不存在则返回当前会员用户信息,存在返回指定
     *
     * @return array
     */
    public function memInfo($itemId)
    {
        if( !$this->memInfo[$itemId] )
        {
            // $enterapply = app::get('sysagent')->model('enterapply')->getRow('enterapply_id,apply_status',array('agent_id'=>$agentId));
            // $account = app::get('sysagent')->model('account')->getRow('login_account',array('agent_id'=>$agentId));
            // $sysagentInfo = app::get('sysagent')->model('agents')->getRow('*',array('agent_id'=>$agentId));
            $sysinfo = app::get('sysitem')->model('item_status')->getRow('check_res',array('item_id'=>$itemId));;
            $sys_info = app::get('sysitem')->model('item')->getRow('*',array('item_id'=>$itemId));;
            // dump($sys_info);die;
            $sys_info['is_Adjusted'] = $sys_info['is_Adjusted']==1?'是':'否';
            $memInfo = ['item_id' => $itemId,
                        'check_res' => $sysinfo['check_res'],
                        'title' => $sys_info['title'],
                        'integral' => $sys_info['integral'],
                        'blend' => kernel::single('sysitem_blendShow')->show($sys_info['blend_integral'],$sys_info['blend_price']),
                        'price' => $sys_info['price'],
                        'shenhe_num' => $sys_info['shenhe_num'],
                        'is_Adjusted' => $sys_info['is_Adjusted'],
                        ];
            $this->memInfo[$itemId] = $memInfo;
        }
        return $this->memInfo[$itemId];
    }
}

