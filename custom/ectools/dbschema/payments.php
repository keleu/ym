<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' => array (
        'payment_id' => array (
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'required' => true,
            //'pkey' => true,
            'default' => '',
            'comment' => app::get('ectools')->_('支付单号'),
        ),
       'money' => array (
            'type' => 'money',
            'required' => true,
            'default' => '0',
            'comment' => app::get('ectools')->_('需要支付的金额'),
        ),
       'money_integral' => array (
            'type' => 'number',
            'required' => true,
            'default' => '0',
            'comment' => app::get('ectools')->_('需要支付的积分'),
        ),
       'cur_money' => array (
            'type' => 'money',
            'required' => true,
            'default' => '0',
            'comment' => app::get('ectools')->_('支付货币金额'),
        ),
        'status' => array (
            'type' => array (
                'succ' => app::get('ectools')->_('支付成功'),
                'failed' => app::get('ectools')->_('支付失败'),
                'cancel' => app::get('ectools')->_('未支付'),
                'error' => app::get('ectools')->_('处理异常'),
                'invalid' => app::get('ectools')->_('非法参数'),
                'progress' => app::get('ectools')->_('已付款至担保方'),
                'timeout' => app::get('ectools')->_('超时'),
                'ready' => app::get('ectools')->_('准备中'),
                'paying' => app::get('ectools')->_('支付中'),
            ),
            'required' => true,
            'default' => 'ready',
            'comment' => app::get('ectools')->_('支付状态'),
            'is_title' => true,
        ),
        'user_id' => array (
            //'type' => 'varchar(100)',
            'type' => 'number',
            'length' => 100,
            'comment' => app::get('ectools')->_('会员'),
        ),
        'user_name' => array (
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('ectools')->_('会员用户名'),
        ),
        'pay_type' => array (
            'type' => array (
                'integral' => app::get('ectools')->_('积分支付'),//2015-11-19 by jiang 添加积分支付
                'online' => app::get('ectools')->_('在线支付'),
                'offline' => app::get('ectools')->_('货到付款'),
                //'deposit' => app::get('ectools')->_('预存款支付')
            ),
            'required' => true,
            'default' => 'online',//2015-11-19 by jiang 默认改为积分支付
            'comment' => app::get('ectools')->_('支付类型'),
        ),
       'pay_app_id' => array (
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('ectools')->_('支付方式名称'),
        ),
       'pay_name' => array (
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('ectools')->_('支付方式名'),
        ),
        'payed_time' => array (
            'type' => 'time',
            'comment' => app::get('ectools')->_('支付完成时间'),
        ),
        'op_id' => array (
            'type' => 'number',//'table:users@desktop',
            'comment' => app::get('ectools')->_('操作员'),
        ),
        'op_name' => array (
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('ectools')->_('操作员'),
        ),
        'account' => array (
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'comment' => app::get('ectools')->_('收款账号'),
        ),
        'bank' => array (
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'comment' => app::get('ectools')->_('收款银行'),
        ),
        'pay_account' => array (
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'comment' => app::get('ectools')->_('支付账户'),
        ),
        'currency' => array (
            //'type' => 'varchar(10)',
            'type' => 'string',
            'length' => 10,
            'comment' => app::get('ectools')->_('货币'),
        ),
        'paycost' => array (
            'type' => 'money',
            'comment' => app::get('ectools')->_('支付网关费用'),
        ),
        'pay_ver' => array (
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'comment' => app::get('ectools')->_('支付版本号'),
        ),
        'ip' => array (
            'type' => 'ipaddr',
            'comment' => app::get('ectools')->_('支付IP'),
        ),
        'created_time' => array (
            'type' => 'time',
            'comment' => app::get('ectools')->_('支付单创建时间'),
            'in_list' => true,
            'default_in_list' => true,
            'is_title' => true,
        ),
        'modified_time' => array (
            'type' => 'time',
            'comment' => app::get('ectools')->_('最后更新时间'),
        ),
        'memo' => array (
            'type' => 'text',
            'comment' => app::get('ectools')->_('支付注释'),
        ),
        'return_url' => array (
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('ectools')->_('支付返回地址'),
        ),
        'disabled' => array (
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('ectools')->_('支付单状态'),
        ),
        'trade_no' => array (
            //'type' => 'varchar(30)',
            'type' => 'string',
            'length' => 30,
            'comment' => app::get('ectools')->_('支付单交易编号'),
        ),
        'thirdparty_account' => array (
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'comment' => app::get('ectools')->_('第三方支付账户'),
        ),
        'tids' => array(
            'type' => 'text',
            'required' => false,
            'default' => '',
            'comment' => app::get('ectools')->_('订单id集, 已废弃'),
        ),
        'trade_own_money' => array(
            'type' => 'serialize',
            'comment' => app::get('ectools')->_('订单自有的金额,已废弃'),
        ),
    ),
    'primary' => 'payment_id',
    'index' => array(
        'ind_disabled' => ['columns' => ['disabled']],
    ),
    'version' => '$Rev: 43384 $',
    'comment' => app::get('ectools')->_('支付记录'),
);

