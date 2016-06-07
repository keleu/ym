<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
return  array(
    'columns'=>
    array(
        'agent_id'=>
        array(
            'type' => 'table:agents@sysagent',
            //'pkey'=>true,
            'label' => app::get('sysagent')->_('代理商ID'),
            'comment' => app::get('sysagent')->_('代理商ID'),
            'in_list' => true,
        ),
        'parent_id'=>array(
            'type'=>'string',
            'length' => '10',
            'required' => true,
            'label' => app::get('sysagent')->_('父代理商id'),
            'comment' => app::get('sysagent')->_('父代理商id'),
        ),
        'left_id'=>array(
            'type'=>'number',
            'required' => true,
            'label' => app::get('sysagent')->_('左节点id'),
            'comment' => app::get('sysagent')->_('左节点id'),
        ),
        'right_id'=>array(
            'type'=>'number',
            'required' => true,
            'label' => app::get('sysagent')->_('右节点id'),
            'comment' => app::get('sysagent')->_('右节点id'),
        ),
    ),
    'primary' => 'agent_id',
    'index' => array(
        'agent_id' => ['columns' => ['agent_id']],
    ),
    'comment' => app::get('sysagent')->_('代理商关系表'),
);
