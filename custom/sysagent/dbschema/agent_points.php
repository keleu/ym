<?php
return array(
    'columns' => array(
        'agent_id' => array(
            'type' => 'table:account@sysagent',
            'in_list' => false,
            //'pkey' => true,
            'required' => true,
            'default_in_list' => false,
            'label' => app::get('sysagent')->_('代理商'),
        ),
        'point_count' => array(
            'type' => 'bigint',
            'in_list' => true,
            'default_in_list' => true,
            'default' => 0,
            'comment' => app::get('sysagent')->_('代理商总积分值'),
            'label' => app::get('sysagent')->_('代理商总积分值'),
        ),
        'expired_point' => array(
            'type' => 'bigint',
            'in_list' => true,
            'default_in_list' => true,
            'default' => 0,
            'comment' => app::get('sysagent')->_('将要过期积分'),
            'label' => app::get('sysagent')->_('将要过期积分'),
        ),
        'modified_time'=> array(
            'type' => 'last_modify',
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('sysagent')->_('记录时间'),
            'label' => app::get('sysagent')->_('记录时间'),
        ),
    ),
    'primary' => 'agent_id',
    'index' => array(
        'ind_modified_time' => ['columns' => ['modified_time']],
    ),
    'comment' => app::get('sysagent')->_('代理商积分表'),
);
