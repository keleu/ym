<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

// 推荐商品表
return  array(
    'columns' => array(
        'recommend_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'width' => 110,
            'label' => app::get('syspromotion')->_('id'),
            'comment' => app::get('syspromotion')->_('推荐id'),
        ),
        'shop_id' => array(
            'type' => 'number',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'label' => app::get('syspromotion')->_('所属商家'),
            'comment' => app::get('syspromotion')->_('所属商家的店铺id'),
        ),
        'item_id' => array(
            'type' => 'number',
            'default' => 0,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 100,
            'label' => app::get('syspromotion')->_('所属商品'),
            'comment' => app::get('syspromotion')->_('所属商品id'),
        ),
        'recommend_desc' => array(
            'type' => 'string',
            'in_list' => true,
            'width' => 110,
            'label' => app::get('syspromotion')->_('推荐描述'),
            'comment' => app::get('syspromotion')->_('推荐描述'),
        ),
        'used_platform' => array(
            'type' => array(
                '0' => app::get('syspromotion')->_('商家全场可用'),
                '1' => app::get('syspromotion')->_('只能用于pc'),
                '2' => app::get('syspromotion')->_('只能用于wap'),
            ),
            'default' => 0,
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('使用平台'),
            'comment' => app::get('syspromotion')->_('使用平台'),
        ),
        'valid_grade' => array(
            'type' => 'string',
            'default' => '',
            'required' => true,
            'label' => app::get('syspromotion')->_('会员级别集合'),
        ),
        'valid_item' => array(
            'type' => 'string',
            'default' => '',
            'required' => true,
            'label' => app::get('syspromotion')->_('商品id集合'),
        ),
        'use_bound' => array(
            'type' => array(
                '0' => app::get('syspromotion')->_('商家全场可用'),
                '1' => app::get('syspromotion')->_('指定商品可用'),
            ),
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '50',
            'label' => app::get('syspromotion')->_('使用范围'),
            'comment' => app::get('syspromotion')->_('使用范围'),
        ),
        'created_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'label' => app::get('syspromotion')->_('创建时间'),
            'comment' => app::get('syspromotion')->_('创建时间'),
        ),
        'recommend_status' => array(
            'type' => array(
                'pending' => app::get('syspromotion')->_('待审核'),
                'agree' => app::get('syspromotion')->_('审核通过'),
                'refuse' => app::get('syspromotion')->_('审核拒绝'),
                'cancel' => app::get('syspromotion')->_('已取消'),
            ),
            'default' => 'agree',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => 110,
            'label' => app::get('syspromotion')->_('促销状态'),
            'comment' => app::get('syspromotion')->_('促销状态'),
        ),
    ),
    'primary' => 'recommend_id',
    'index' => array(
        'ind_recommend_status' => ['columns' => ['recommend_status']],
        'ind_shop_id' => ['columns' => ['shop_id']],
        'ind_item_id' => ['columns' => ['item_id']],
    ),
    'comment' => app::get('syspromotion')->_('橱窗商品表'),
);
