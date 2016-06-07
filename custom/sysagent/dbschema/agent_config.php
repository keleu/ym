<?php
return  array(
    'columns' => array(
        'config_id' => array(
            'type' => 'number',
            //'pkey' => true,
            'autoincrement' => true,
            'label' => app::get('sysagent')->_('代理商常用配置id'),
        ),
        'agent_id' => array(
            'type' => 'table:account@sysagent',
            'in_list' => false,
            'required' => true,
            'default_in_list' => false,
            'label' => app::get('sysagent')->_('代理商'),
        ),
        'discount_sub' => array(
            'type' => 'decimal',
            'length' => 20,
            'scale' => 4,
            'in_list' => true,
            'default_in_list' => true,
            'required' => true,
            'comment' => app::get('sysagent')->_('子代理向我购买积分的价格'),
            'label' => app::get('sysagent')->_('子代理向我购买积分的价格'),
        ),
    ),
    'primary' => 'config_id',
    'index' => array(
        'ind_agent_id' => ['columns' => ['agent_id']],
    ),
    'comment' => app::get('sysagent')->_('代理商积分配置'),
);
