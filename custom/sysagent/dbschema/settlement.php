<?php

/**
 * ShopEx LuckyMall
 *
 * @author     ajx
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return  array(
    'columns' => array(
        'settlement_no' => array(
            //'type' => 'bigint unsigned',
            'type' => 'bigint',
            'unsigned' => true,

            'required' => true,
            //'pkey' => true,
            'in_list' => true,
            'is_title' => true,
            'filterdefault' => true,
            'default_in_list' => true,
            'label' => app::get('sysagent')->_('账单编号'),
            'width' => '15',
        ),
        'agent_seller' => array(
            'type' => 'number',
            'label' => app::get('sysagent')->_('所属代理商'),
            'width' => 110,
            'searchtype' => 'has', // 简单搜索
        ),
        'tradecount' => array(
            //'type'=>'int(10)',
            'type' => 'number',
            'default' => '0',
            'label' => app::get('sysagent')->_('交易数量'),
            'required' => true,
            // 'in_list' => true,
            // 'default_in_list'=>true,
        ), 
        'settlement_fee_point' => array(
            'type' => 'number',
            'default'=>0,
            'label' => app::get('sysagent')->_('积分数量'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'settlement_fee_amount' => array(
            'type' => 'money',
            'precision' => 20,
            'scale' => 3,
            'default'=>0,
            'label' => app::get('sysagent')->_('结算金额'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'settlement_status' => array(
            'type' => array(
                '1'=>'未结算',
                '2'=>'已结算',
            ),
            'default' => '1',
            'label' => app::get('sysagent')->_('结算状态'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'account_start_time' => array(
            'type' => 'time',
            'label' => app::get('sysagent')->_('账单开始时间'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'account_end_time' => array(
            'type' => 'time',
            'label' => app::get('sysagent')->_('账单结束时间'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'settlement_time' => array(
            'type' => 'time',
            'label' => app::get('sysagent')->_('结算时间'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
    ),

    'primary' => 'settlement_no',
    'comment' => app::get('sysagent')->_('代理商卖出积分结算'),
);

