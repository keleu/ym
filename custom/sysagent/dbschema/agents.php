<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' =>
    array (
        'agent_id' => array(
            'type' => 'number',
            'autoincrement' => true,
            'required' => true,
            'comment' => app::get('sysagent')->_('对应的代理商id'),
            'in_list' => true,
            'label' => app::get('sysagent')->_('代理商id'),
            'default_in_list' => true,
            'width' => '30',
            'order' => 10,
        ),
        'username' =>
        array (
            'type' => 'string',
            'length' => 50,
            'required' => true,
            'label' => app::get('sysagent')->_('用户名'),
            'comment' => app::get('sysagent')->_('用户名'),
            'width' => 75,
            'searchtype' => 'has', // 简单搜索
            'filtertype' => 'normal', // 高级搜索
            'filterdefault' => 'true',
            'in_list' => true,
            'is_title' => true,
            'default_in_list' => false,
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
            'default_in_list' => false,
        ),
        'id_card' =>
        array (
            'type' => 'string',
            'length' => 50,
            'label' => app::get('sysagent')->_('身份证号码(企业执照号)'),
            'comment' => app::get('sysagent')->_('身份证号'),
            'width' => 75,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => false,
        ),
        'point' =>
        array (
            'type' => 'number',
            'default' => 0,
            'required' => true,
            'label' => app::get('sysagent')->_('积分'),
            'comment' => app::get('sysagent')->_('积分余额'),
            'width' => 110,
        ),
        //没什么用，先注释掉，by liuxin，2015-11-20
        // 'refer_id' =>
        // array (
        //     'type' => 'string',
        //     'length' => 50,
        //     'label' => app::get('sysagent')->_('来源ID'),
        //     'comment' => app::get('sysagent')->_('来源ID'),
        //     'width' => 75,
        //     'editable' => false,
        //     'filtertype' => 'normal',
        //     'in_list' => false,
        // ),
        // 'refer_url' =>
        // array (
        //     'type' => 'string',
        //     'length' => 200,
        //     'label' => app::get('sysagent')->_('推广来源URL'),
        //     'comment' => app::get('sysagent')->_('推广来源URL'),
        //     'width' => 75,
        //     'editable' => false,
        //     'filtertype' => 'normal',
        //     'in_list' => false,
        // ),
        'birthday' =>
        array (
            'label' => app::get('sysagent')->_('生日'),
            'comment' => app::get('sysagent')->_('生日'),
            'type' => 'time',
            'width' => 100,
            'editable' => false,
            'in_list'=>true,
            'default_in_list' => true,
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
        //没什么用，先注释掉，by liuxin，2015-11-20
        // 'wedlock' =>
        // array (
        //     'type' => 'bool',
        //     'default' => '0',
        //     'required' => true,
        //     'editable' => false,
        //     'comment' => app::get('sysagent')->_('婚姻状况'),
        // ),
        // 'education' =>
        // array (
        //     'type' => 'string',
        //     'length' => 30,
        //     'editable' => false,
        //     'comment' => app::get('sysagent')->_('教育程度'),
        // ),
        // 'vocation' =>
        // array (
        //     'type' => 'string',
        //     'length' => 50,
        //     'editable' => false,
        //     'comment' => app::get('sysagent')->_('职业'),
        // ),
        'reg_ip' =>
        array (
            'type' => 'string',
            'length' => 16,
            'label' => app::get('sysagent')->_('注册IP'),
            'width' => 110,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('sysagent')->_('注册时IP地址'),
        ),
        'regtime' =>
        array (
            'label' => app::get('sysagent')->_('注册时间'),
            'width' => 150,
            'type' => 'time',
            'editable' => false,
            'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('sysagent')->_('注册时间'),
        ),
        'disabled' =>
        array (
            'type' => 'bool',
            'default' => 0,
            'editable' => false,
            'comment' => app::get('sysagent')->_('启用状态'),
        ),
        'source' =>
        array (
            'type' => array(
                'pc' =>app::get('sysagent')->_('标准平台'),
                'wap' => app::get('sysagent')->_('手机触屏'),
                'weixin' => app::get('sysagent')->_('微信商城'),
                'api' => app::get('sysagent')->_('API注册')
            ),
            'required' => false,
            'label' => app::get('sysagent')->_('平台来源'),
            'comment' => app::get('sysagent')->_('平台来源'),
            'width' => 110,
            'editable' => false,
            'default' =>'pc',
            'in_list' => true,
            'default_in_list' => false,
            'filterdefault' => false,
            'filtertype' => 'normal',
        ),
        'area_first' =>
        array (
            'label' => app::get('sysagent')->_('一级地区'),
            'comment' => app::get('sysagent')->_('省'),
            'width' => 110,
            'type' => 'string',
            'length' => 55,
            'in_list' => false,
            'editable' => false,
            'filterdefault' => 'true',
        ),
        'area_second' =>
        array (
            'label' => app::get('sysagent')->_('二级地区'),
            'comment' => app::get('sysagent')->_('市'),
            'width' => 110,
            'type' => 'string',
            'length' => 55,
            'default' => '',
            'in_list' => false,
            'editable' => false,
            'filterdefault' => 'true',
        ),
        'area_third' =>
        array (
            'label' => app::get('sysagent')->_('三级地区'),
            'comment' => app::get('sysagent')->_('区'),
            'width' => 110,
            'type' => 'string',
            'length' => 55,
            'default' => '',
            'in_list' => false,
            'editable' => false,
            'filterdefault' => 'true',
        ),
        'addr' =>
        array (
            'type' => 'varchar(255)',
            'type' => 'string',
            'label' => app::get('sysagent')->_('地址'),
            'comment' => app::get('sysagent')->_('地址'),
            'width' => 110,
            'editable' => true,
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => false,
        ),
        'mobile' =>
        array (
            'type' => 'varchar(20)',
            'type' => 'string',
            'label' => app::get('sysagent')->_('手机号'),
            'comment' => app::get('sysagent')->_('手机号'),
            'width' => 110,
            'editable' => true,
            'searchtype' => 'has', // 简单搜索
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => false,
        ),
        'email' =>
        array (
            'type' => 'varchar(50)',
            'type' => 'string',
            'label' => app::get('sysagent')->_('邮箱'),
            'comment' => app::get('sysagent')->_('邮箱'),
            'width' => 110,
            'editable' => true,
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => false,
        ),
        'parent_id' =>
        array (
            'type' => 'string',
            'required' => true,
            'comment' => app::get('sysagent')->_('父代理商id'),
            'in_list' => false,
            'label' => app::get('sysagent')->_('父代理商id'),
            'default' => '0',
            'default_in_list' => false,
            'width' => '30',
            'order' => 10,
        ),
        'agent_level' =>
        array (
            'type' => 'number',
            'required' => true,
            'comment' => app::get('sysagent')->_('代理商等级'),
            'in_list' => false,
            'label' => app::get('sysagent')->_('代理商等级'),
            'default_in_list' => false,
            'filtertype' => 'yes',
            'width' => '30',
            'order' => 10,
        ),
        'status' =>
        array (
            'type' => array(
                '0' =>app::get('sysagent')->_('审核中'),
                '1' => app::get('sysagent')->_('通过'),
                '2' => app::get('sysagent')->_('拒绝'),
                '3' => app::get('sysagent')->_('停止代理')
            ),
            'required' => true,
            'label' => app::get('sysagent')->_('代理商状态'),
            'comment' => app::get('sysagent')->_('代理商状态'),
            'width' => 110,
            'default' =>'0',
            'in_list' => true,
            'default_in_list' => true,
        ),
        //by zhangyan 2015-12-09 添加子代理积分率 
        'discount' => array(
            'type' => 'decimal',
            'precision' => 20,
            'scale' => 4,
            'in_list' => true,
            'default_in_list' => true,
            'comment' => app::get('sysagent')->_('我向父代理商购买积分的折扣率'),
            'label' => app::get('sysagent')->_('我向父代理商购买积分的折扣率'),
        ),
        //by zhangyan 2015-12-09 添加字段判断是否已经提交取消代理请求
        'is_stop' =>
        array (
            'type' => array(
                '0' =>app::get('sysagent')->_('未提交'),
                '1' => app::get('sysagent')->_('提交'),
            ),
            'required' => true,
            'label' => app::get('sysagent')->_('代理商是否提交停用'),
            'comment' => app::get('sysagent')->_('代理商是否提交停用'),
            'width' => 110,
            'default' =>'0',
            'in_list' => true,
            'default_in_list' => true,
        ),
         //by zhangyan 2015-12-11 添加字段区分企业和个人
        'kind' =>
        array (
            'type' =>
            array (
                1 => app::get('sysagent')->_('个人'),
                2 => app::get('sysagent')->_('企业'),
            ),
            'default' => 1,
            'required' => true,
            'label' => app::get('sysagent')->_('申请类型'),
            'comment' => app::get('sysagent')->_('申请类型'),
            'order' => 30,
            'in_list' => ture,
            'default_in_list' => true,
        ),
        //by zhangyan 2015-12-11 记录图片的字段
        'picture' =>
        array (
            'type' => 'string',
            'length' => 500,
            'label' => app::get('sysagent')->_('身份证或营业执照图片'),
            'comment' => app::get('sysagent')->_('身份证或营业执照图片'),
            'width' => 75,
            'filterdefault' => 'true',
            'in_list' => false,
            'default_in_list' => false,
        ),
    ),

    'primary' => 'agent_id',

    'comment' => app::get('sysagent')->_('代理商列表'),
);
