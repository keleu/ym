<?php
return  array(
    'columns'=>array(
        'audit_id'=>array(
            'type'=>'number',
            'autoincrement' => true,
            'required' => true,
            'order' => 1,
            'label' => app::get('sysagent')->_('审核id'),
            'comment' => app::get('sysagent')->_('审核id自增'),
        ),
        'apply_status'=>array(
            'type'=>array(
                '0'=>'未审核',
                '1'=>'同意',
                '2'=>'拒绝',
            ),
            'required'=>true,
            'in_list'=>true,
            'default'=>'0',
            'default_in_list'=>true,
            'label' => app::get('sysagent')->_('申请状态'),
            'comment' => app::get('sysagent')->_('申请状态'),
            'order' => 5,
        ),
        'agent_id'=>array(
            'type'=>'table:agents@sysagent',
            'required'=>true,
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('sysagent')->_('代理商账号'),
            'comment' => app::get('sysagent')->_('提交申请的账号'),
            'order' => 6,
        ),
        'add_time' => array(
            'type'=>'time',
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('sysagent')->_('申请时间'),
            'comment' => app::get('sysagent')->_('申请时间'),
            'order' => 13,

        ),
        'reason'=>array(
            //'type'=>'varchar(500)',
            'type' => 'string',
            'length' => 500,
            'in_list'=>true,
            'default_in_list'=>true,
            'label' => app::get('sysagent')->_('申请停用原因'),
            'comment' => app::get('sysagent')->_('申请原因'),
            'order' => 12,
        ),
        'enterapply_id'=>array(
            'type'=>'string',
            'required'=>true,
            'in_list'=>false,
            'label' => app::get('sysagent')->_('代理商账号'),
            'comment' => app::get('sysagent')->_('申请账号'),
            'order' => 6,
        ),
    ),
    'primary' => 'audit_id',
    'index' => array(
        'ind_agent_id' => ['columns' => ['agent_id']],
        'ind_apply_status' => ['columns' => ['apply_status']],
    ),
    'comment' => app::get('sysagent')->_('代理商停用申请表'),
);
