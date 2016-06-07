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
        'account_id'=>array(
            'type'=>'number',
            'autoincrement' => true,
            'required' => true,
            'order' => 1,
            'label' => app::get('sysagent')->_('代理账号 id'),
            'comment' => app::get('sysagent')->_('代理账号id 自增'),
        ),
        'agent_id'=>
        array(
            'type' => 'table:agents@sysagent',
            //'pkey'=>true,
            'label' => app::get('sysagent')->_('代理商ID'),
            'comment' => app::get('sysagent')->_('代理商ID'),
            'in_list' => true,
        ),
        'login_account'=>
        array(
            //'type'=>'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'is_title'=>true,
            'required' => true,
            'comment' => app::get('sysagent')->_('登录名'),
        ),
        'login_password'=>
        array(
            //'type'=>'varchar(60)',
            'type' => 'string',
            'length' => 60,
            'required' => true,
            'comment' => app::get('sysagent')->_('登录密码'),
        ),
        'name' =>
        array (
            'type' => 'string',
            'length' => 50,
            'label' => app::get('sysagent')->_('真实姓名'),
            'comment' => app::get('sysagent')->_('真实姓名'),
            'width' => 75,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'is_title'=>true,
            'default_in_list' => false,
        ),
        'mobile' =>
        array (
            'type' => 'string',
            'length' => 20,
            'label' => app::get('sysagent')->_('手机号'),
            'comment' => app::get('sysagent')->_('手机号'),
            'width' => 110,
            'editable' => true,
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => false,
        ),
        'id_card' =>
        array (
            'type' => 'string',
            'length' => 50,
            'label' => app::get('sysagent')->_('身份证号码'),
            'comment' => app::get('sysagent')->_('身份证号'),
            'width' => 75,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'is_title'=>true,
            'default_in_list' => false,
        ),
        'sex' =>
        array (
            'type' =>
            array (
                0 => app::get('sysagent')->_('女'),
                1 => app::get('sysagent')->_('男'),
                2 => '-',
            ),
            'default' => 2,
            'required' => true,
            'label' => app::get('sysagent')->_('性别'),
            'comment' => app::get('sysagent')->_('性别'),
            'order' => 30,
            'width' => 40,
            'editable' => true,
            'filtertype' => 'yes',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'account_type' =>
        array (
            'type' =>
            array (
                'agent' => app::get('sysagent')->_('代理商主账号'),
                'account' => app::get('sysagent')->_('子帐号'),
            ),
            'default' => 'agent',
            'required' => true,
            'label' => app::get('sysagent')->_('账号类型'),
            'comment' => app::get('sysagent')->_('账号类型'),
            'order' => 30,
            'width' => 40,
            'editable' => true,
            'filtertype' => 'yes',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'is_del' =>
        array (
            'type' =>
            array (
                '0' => app::get('sysagent')->_('存在'),
                '1' => app::get('sysagent')->_('删除子帐号标记'),
            ),
            'default' => '0',
            'required' => true,
            'label' => app::get('sysagent')->_('删除子帐号标记'),
            'comment' => app::get('sysagent')->_('删除子帐号标记'),
        ),
        'secret' =>
        array (
            'type' => 'string',
            'length'=>100,
            'label' => app::get('sysagent')->_('api验证信息'),
            'comment' => app::get('sysagent')->_('api验证信息'),
            'width' => 100,
            'editable' => true,
            'filtertype' => 'normal',
            'in_list' => false,
            'default_in_list' => false,
        ),
        'key' =>
        array (
            'type' => 'string',
            'length'=>100,
            'label' => app::get('sysagent')->_('api验证信息'),
            'comment' => app::get('sysagent')->_('api验证信息'),
            'width' => 100,
            'editable' => true,
            'filtertype' => 'normal',
            'in_list' => false,
            'default_in_list' => false,
        ),
        'token' =>
        array (
            'type' => 'string',
            // 'length'=>150,
            'label' => app::get('sysagent')->_('api验证信息,用户自定义'),
            'comment' => app::get('sysagent')->_('api验证信息,用户自定义'),
            'width' => 100,
            'editable' => true,
            'filtertype' => 'normal',
            'in_list' => false,
            'default_in_list' => false,
        ),
        'disabled'=>
        array (
            'type'=>'bool',
            'default'=>0,
        ),
        'createtime'=>
        array (
            'type'=>'time',
            'comment' => app::get('sysagent')->_('创建时间'),
        ),
        'modified_time' =>
        array (
            'type' => 'last_modify',
            'label' => app::get('sysagent')->_('最后修改时间'),
        ),
    ),
    'primary' => 'account_id',
    'index' => array(
        'agent_id' => ['columns' => ['agent_id']],
        'login_account' => ['columns' => ['login_account']],
        'createtime' => ['columns' => ['createtime']],
    ),
    'comment' => app::get('sysagent')->_('代理商账号表'),
);
