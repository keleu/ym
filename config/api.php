<?php
$apis = array(
    'yytapi'=>array(
        'test.getdata.signin'=>['uses' => 'yytapi_test_getdata@signin','title'=>'模拟登陆'],
        'test.getdata.apitest'=>['uses' => 'yytapi_test_getdata@apitest','title'=>'测试api'],
        'test.getdata.logout'=>['uses' => 'yytapi_test_getdata@logout','title'=>'登出'],
        'test.getdata.get_data'=>['uses' => 'yytapi_test_getdata@get_data','title'=>'测试数据'],
        'test.getdata.get_sess'=>['uses' => 'yytapi_test_getdata@get_sess','title'=>'测试数据获取session'],
        'test.getdata.goout'=>['uses' => 'yytapi_test_getdata@goout','title'=>'测试数据销毁session'],
        ///////////////////////////////代理商相关////////////////////////////
        'agents.getaccesstoken'=>['uses' => 'yytapi_agents_signin@getaccesstoken','title'=>'代理商获取令牌'],
        'agents.setsecretkey'=>['uses' => 'yytapi_agents_signin@setsecretkey','title'=>'设置验证信息'],
        'agents.settoken'=>['uses' => 'yytapi_agents_signin@settoken','title'=>'设置token'],
        'agents.getsecretkey'=>['uses' => 'yytapi_agents_signin@getsecretkey','title'=>'获取secret'],
        'agents.gettoken'=>['uses' => 'yytapi_agents_signin@gettoken','title'=>'获取token'],
        'agents.signin'=>['uses' => 'yytapi_agents_signin@signin','title'=>'代理商登录'],
        'agents.agent_applys'=>['uses' => 'yytapi_agents_signin@agent_applys','title'=>'代理商申请入注'],
        'agents.verif_agent'=>['uses' => 'yytapi_agents_signin@verif_agent','title'=>'代理商入注时验证'],
        'agents.reset_password'=>['uses' => 'yytapi_agents_signin@reset_password','title'=>'代理商重置密码'],
        'agents.auto_area'=>['uses' => 'yytapi_agents_signin@auto_area','title'=>'代理商有推荐人的时候自动取得该代理商的代理地区 '],
        'agents.agent_detail'=>['uses' => 'yytapi_agents_signin@agent_detail','title'=>'获取代理商信息（或首页使用）'],
        'agents.agent_point_log'=>['uses' => 'yytapi_agents_signin@agent_point_log','title'=>'代理商积分日志(账单)'],
        'agents.agent_point_detail'=>['uses' => 'yytapi_agents_signin@agent_point_detail','title'=>'代理商积分交易详情'],
        'agents.agent_config'=>['uses' => 'yytapi_agents_signin@agent_config','title'=>'代理商常用配置'],
        'agents.agent_point_conf'=>['uses' => 'yytapi_agents_signin@getPointConf', 'title'=>'获取积分相关配置'],
        'agents.save_agent_config'=>['uses' => 'yytapi_agents_signin@save_agent_config','title'=>'设置代理商默认积分折扣'],
        'agents.save_sub_discount'=>['uses' => 'yytapi_agents_signin@save_sub_discount','title'=>'设置子代理商积分折扣率'],
        'agents.sub_counts'=>['uses' => 'yytapi_agents_signin@sub_counts','title'=>'子代理商,未审核子代理商，子帐号总数量'],
        'agents.subagent_list'=>['uses' => 'yytapi_agents_signin@subagent_list','title'=>'我的子代理商列表支持首字母缩写查询'],
        'agents.subagent_details'=>['uses' => 'yytapi_agents_signin@subagent_detail','title'=>'我的子代理商详情'],
        'agents.save_subagent_status'=>['uses' => 'yytapi_agents_signin@save_subagent_status','title'=>'审核子代理商'],
        'agents.subagent_agent_list'=>['uses' => 'yytapi_agents_signin@subagent_agent_list','title'=>'我的子代理商所有附属子代理列表支持搜索'],
        'agents.cancel_agent'=>['uses' => 'yytapi_agents_signin@cancel_agent','title'=>'代理商取消代理资格'],
        'agents.save_subagent'=>['uses' => 'yytapi_agents_signin@save_subagent','title'=>'保存子代理商'],
        'agents.save_agent_credit'=>['uses' => 'yytapi_agents_signin@save_integral','title'=>'积分划拨'],
        'agents.delete_agent'=>['uses' => 'yytapi_agents_signin@delete_agent','title'=>'删除子代理'],
        'agents.get_all_payments'=>['uses' => 'yytapi_agents_signin@getPayment','title'=>'获取支付方式配置信息'],
        'agents.get_money'=>['uses' => 'yytapi_agents_signin@get_money','title'=>'根据子代理商设置的折扣率计算钱数'],
        'agents.save_integral'=>['uses' => 'yytapi_agents_signin@buyPoint', 'title'=>'主动购买积分'],
        'agents.logout'=>['uses' => 'yytapi_agents_signin@logout','title'=>'退出'],
        'agents.sendVcode'=>['uses' => 'yytapi_agents_signin@sendVcode','title'=>'发送验证码'],
        'agents.image_upload'=>['uses' => 'yytapi_agents_signin@image_upload','title'=>'上传图片'],
        'agents.save_payment'=>['uses' => 'yytapi_agents_signin@save_payment','title'=>'确认购买并支付'],
        'agents.beforedopay'=>['uses' => 'yytapi_agents_signin@beforedopay','title'=>'确认支付前判断'],
        'agents.dopayment'=>['uses' => 'yytapi_agents_signin@dopayment','title'=>'确认支付'],
        'agents.callback'=>['uses' => 'yytapi_agents_signin@callback','title'=>'回调方法'],
        //////////////////////////////////////子账号相关///////////////////////////////
        'accounts.account_list'=>['uses' => 'yytapi_accounts_info@account_list','title'=>'我的子账号列表支持排序搜所'],
        'accounts.account_detail'=>['uses' => 'yytapi_accounts_info@account_detail','title'=>'子帐号详情'],
        'accounts.account_member'=>['uses' =>'yytapi_accounts_info@account_member','title'=>'子账号的会员支持模糊搜索'],
        'accounts.add_account'=>['uses' => 'yytapi_accounts_info@add_account','title'=>'添加子帐号'],
        'accounts.verify_account'=>['uses' => 'yytapi_accounts_info@verify_account','title'=>'新增子帐号时验证'],
        'accounts.delete_account'=>['uses' => 'yytapi_accounts_info@delete_account','title'=>'删除子帐号'],
        //////////////////////////////////////会员相关///////////////////////////////
        'members.seach_member'=>['uses' => 'yytapi_member_info@seach_member','title'=>'查找会员'],
        'members.scan_member'=>['uses' => 'yytapi_member_info@scan_member','title'=>'扫一扫查找会员'],
        'members.member_index'=>['uses' => 'yytapi_member_info@member_index','title'=>'我的会员首页'],
        'members.save_integral'=>['uses' => 'yytapi_member_info@save_integral','title'=>'会员积分转让'],
        'members.verify_member'=>['uses' => 'yytapi_member_info@verify_member','title'=>'添加会员时验证'],
        'members.save_member'=>['uses' => 'yytapi_member_info@save_member','title'=>'保存会员'],
        'members.member_list'=>['uses' => 'yytapi_member_info@member_list','title'=>'我的会员列表，支持模糊搜索'],
        'members.member_all_list'=>['uses' => 'yytapi_member_info@member_all_list','title'=>'代理商子代理发展的会员列表，支持模糊搜索'],
         //////////////////////////////////////会员相关///////////////////////////////
        'info.get_area'=>['uses' => 'yytapi_config_info@get_area','title'=>'获取地区'],
        'info.get_agent_agreement'=>['uses' => 'yytapi_config_info@get_agent_agreement','title'=>'获取代理商入职协议'],
        'info.pay_setting'=>['uses' => 'yytapi_config_info@pay_setting','title'=>'判断移动端支付方式是否开启'],
        'info.change_password'=>['uses' => 'yytapi_config_info@change_password','title'=>'修改密码'],
        'info.edit_detail'=>['uses' => 'yytapi_config_info@edit_detail','title'=>'修改个人信息'],
        'info.authority'=>['uses' => 'yytapi_config_info@authority','title'=>'获取显示界面的权限'],
        ///////////////////////////////////////weixin相关接口///////////////////////////////////////////////////
        'weixin.valid'=>['uses' => 'yytapi_weixin_base@valid','title'=>'weixin端验证url'],
        'weixin.responseMsg'=>['uses' => 'yytapi_weixin_base@responseMsg','title'=>'weixin端统一接口'],
    ),
);
