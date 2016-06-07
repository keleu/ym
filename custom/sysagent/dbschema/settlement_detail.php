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
        'id' => array(
            'type' => 'number',
            'autoincrement' => true,
            'required' => true,
            'comment' => app::get('sysagent')->_('settlement_detail_id'),
        ),
        'agent_seller' => array(
            'type' => 'number',
            'label' => app::get('sysagent')->_('所属代理商'),
            'width' => 110,
        ),
        'agent_buyer' => array(
            'type' => 'number',
            'label' => app::get('sysagent')->_('购买者'),
            'width' => 110,
        ),
        'settlement_fee_point' => array(
            'type' => 'number',
            'precision' => 20,
            'scale' => 2,
            'default'=>0,
            'label' => app::get('sysagent')->_('积分数量'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'discount' => array(
            'type' => 'decimal',
            'precision' => 20,
            'scale' => 2,
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('sysagent')->_('积分折扣率'),
            'label' => app::get('sysagent')->_('积分折扣率'),
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
        'pay_time' => array(
            'type' => 'time',
            'label' => app::get('sysagent')->_('付款时间'),
            'in_list' => true,
            'default_in_list'=>true,
        ),
    ),

    'primary' => 'id',
    'comment' => app::get('sysagent')->_('商家账号信息'),
);

