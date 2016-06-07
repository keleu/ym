<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 商家基本设置
 */

return array(
    0=>array(
        0=>array(
            'menu_id'=> 1,
            'app_id'=> 'bill',
            'workground'=> 'bill',
            'menu_title'=> '账单',
            'display'=> 1,
            'parent'=> 0,
            'mgrpname' => '常用配置',
            'mgrptitle' => 'configure'
        ),
        1=>array(
            'menu_id'=> 2,
            'app_id'=> 'price',
            'workground'=> 'price',
            'menu_title'=> '积分购买价',
            'display'=> 1,
            'parent'=> 0,
        ),
        2=>array(
            'menu_id'=> 3,
            'app_id'=> 'protocol',
            'workground'=> 'protocol',
            'menu_title'=> '代理商协议',
            'display'=> 1,
            'parent'=> 0,
        ),
        3=>array(
            'menu_id'=> 4,
            'app_id'=> 'buy',
            'workground'=> 'buy',
            'menu_title'=> '购买积分',
            'display'=> 1,
            'parent'=> 0,
        ),

        4=>array(
            'menu_id'=> 5,
            'app_id'=> 'agent_succ',
            'workground'=> 'agent_succ',
            'menu_title'=> '我的子代理',
            'display'=> 1,
            'parent'=> 0,
            'mgrpname' => '子代理模块 (子帐号不可以给此权限)',
            'mgrptitle' => 'agent'
        ),
        5=>array(
            'menu_id'=> 6,
            'app_id'=> 'agent_wait',
            'workground'=> 'agent_wait',
            'menu_title'=> '未审核子代理',
            'display'=> 1,
            'parent'=> 0,
        ),
        6=>array(
            'menu_id'=> 7,
            'app_id'=> 'accounts_succ',
            'workground'=> 'accounts_succ',
            'menu_title'=> '我的子账号',
            'display'=> 1,
            'parent'=> 0,
        ),
        7=>array(
            'menu_id'=> 8,
            'app_id'=> 'agent_add',
            'workground'=> 'agent_add',
            'menu_title'=> '我要发展子代理',
            'display'=> 1,
            'parent'=> 0,
        ), 
        8=>array(
            'menu_id'=> 9,
            'app_id'=> 'accounts_add',
            'workground'=> 'accounts_add',
            'menu_title'=> '添加子账号',
            'display'=> 1,
            'parent'=> 0,
        ),

        9=>array(
            'menu_id'=> 10,
            'app_id'=> 'vip_succ',
            'workground'=> 'vip_succ',
            'menu_title'=> '查找会员/转让积分',
            'display'=> 1,
            'parent'=> 0,
            'mgrpname' => '会员模块',
            'mgrptitle' => 'vip'
        ),
        10=>array(
            'menu_id'=> 11,
            'app_id'=> 'vip_add',
            'workground'=> 'vip_add',
            'menu_title'=> '添加会员帐号',
            'display'=> 1,
            'parent'=> 0,
        ),  
        11=>array(
            'menu_id'=> 12,
            'app_id'=> 'vip_show',
            'workground'=> 'vip_show',
            'menu_title'=> '我发展的会员',
            'display'=> 1,
            'parent'=> 0,
        ),   
    )
);

