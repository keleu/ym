<?php
/*********************************************************************\
*  Copyright (c) 2007-2015, TH. All Rights Reserved.
*  Author :li
*  FName  :getdata.php
*  Time   :2015/11/26 13:32:55
*  Remark :获取代理商
\*********************************************************************/
class yytapi_member_info extends yytapi_base {
    /**
     * ps ：查找会员
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * member_login String  N   会员用户名
     * accesstoken  String  Y   帐号登录返回的 accesst
     * member_mobile    String  N   会员手机号
     * member_login，member_mobile必须填写一个参数，不能都为空
     * @return 返回值类型
    */
    public function seach_member($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期',
                    'status'=>'false',
            ]);
        } 
        if(!($params['member_login']||$params['member_mobile'])){
            $service->send_user_error('7001', [
                    'message'=>'相关参数不能为空',
                    'status'=>'false',
            ]);
        }else{
            $sql = "select a.user_id as member_id,
                    b.login_account as member_name,a.sex,
                    b.createtime,b.mobile as member_mobile,
                    a.point as member_point
                    from sysuser_user a
                    left join sysuser_account b on a.user_id=b.user_id
                    where 1 = 1";

            if($params['member_login']){
                $sql .= " and b.login_account = '{$params['member_login']}'";
            }

            if($params['member_mobile']){
                $sql .= " and b.mobile = '{$params['member_mobile']}'";
            }
            $data = db::connection()->fetchAll($sql);
            if(count($data)>0){
                $data[0]['sex']=$data[0]['sex']=='2'?'1':$data[0]['sex'];
            }
            $service->send_user_succ('success', $data);
        }
    }
    /**
    * ps ：扫一扫查找会员 精确查找
    * Time：2016/03/24 17:13:01
    * @author jianghui
    */
    public function scan_member($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期',
                    'status'=>'false',
            ]);
        } 
        if(!($params['member_login']||$params['member_mobile'])){
            $service->send_user_error('7001', [
                    'message'=>'相关参数不能为空',
                    'status'=>'false',
            ]);
        }else{
            $sql = "select a.user_id as member_id,
                    b.login_account as member_name,a.sex,
                    b.createtime,b.mobile as member_mobile,
                    a.point as member_point
                    from sysuser_user a
                    left join sysuser_account b on a.user_id=b.user_id
                    where 1 = 1";

            if($params['member_login']){
                $sql .= " and b.login_account = '{$params['member_login']}'";
            }

            if($params['member_mobile']){
                $sql .= " and b.mobile = '{$params['member_mobile']}'";
            }
            $data = db::connection()->fetchAll($sql);
            if(count($data)>0){
                $data[0]['sex']=$data[0]['sex']=='2'?'1':$data[0]['sex'];
            }
            $service->send_user_succ('success', $data);
        }
    }   
    /**
     * ps ：我的会员首页
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * accesstoken  String  Y   帐号登录返回的 accesst
     * account_id Int Y   代理商id
     * @return 返回值类型
    */
    public function member_index($params, &$service){
        /*return array(
            'member_self_cnt'=>'23',    //   Int 自己发展会员的数量
            'agent_id'=>'23',    //  Int 代理商的id
        );*/
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期',
                    'status'=>'false',
            ]);
        } 
        $filter['agent_account']=$account_id;
        //代理商账号
        $return_data['member_self_cnt']=app::get('sysuser')->model('user')->count($filter);
        $agentObj=app::get('sysagent')->model('account')->getRow('agent_id',array('account_id'=>$account_id));
        $return_data['agent_id']=$agentObj['agent_id'];
        $service->send_user_succ('success', $return_data);
    }
    /**
     * ps ：会员积分转让
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * accesstoken  String  Y   帐号登录返回的 accesst
     * account_id Int Y   代理商id
     * member_id    Int Y   会员id
     * point    Int Y   积分：>0 有效
     * memo String  N   备注
     * @return 返回值类型
    */
    public function save_integral($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期',
                    'status'=>'false',
            ]);
        } 
        if (empty($params['member_id'])) {
            $service->send_user_error('error', [
                    'message'=>'会员ID不能为空！',
                    'status'=>'false',
            ]);
        }
        if (empty($params['point']) || round($params['point']) != $params['point'] || round($params['point']) < 0) {
            $service->send_user_error('error', [
                    'message'=>'积分不能为空且必须大于零！',
                    'status'=>'false',
            ]);
        }
        $accountObj=app::get('sysagent')->model('account')->getRow('agent_id,name,account_type',array('account_id'=>$account_id));
        $params['agent_id']=$accountObj['agent_id'];
        //转让给会员,共改变六张表
        $agent_point_model=app::get('sysagent')->model('agent_points');
        $agent_pointlog_model=app::get('sysagent')->model('agent_pointlog');
        $agent_model=app::get('sysagent')->model('agents');
        //会员相关表
        $user_point_model=app::get('sysuser')->model('user_points');
        $user_pointlog_model=app::get('sysuser')->model('user_pointlog');
        $user_model=app::get('sysuser')->model('user');
        $acc_model=app::get('sysuser')->model('account');

        $parentObj=$agent_model->getRow('name,point',array('agent_id'=>$params['agent_id']));
        $subObj=$user_model->getRow('name,point',array('user_id'=>$params['member_id']));
        $login_Obj=$acc_model->getRow('login_account',array('user_id'=>$params['member_id']));
        
        if ($params['point'] > $parentObj['point']) {
            $service->send_user_error('error', [
                    'message'=>'您的积分不够！',
                    'status'=>'false',
            ]);
        }
        $db = app::get('sysuser')->database();
        $db->beginTransaction();
        try
        {
            //首先组织agents,agent_points表的数据表的数据
            //父代理商
            $parent_points['agent_id']=$parent_point['agent_id']=$params['agent_id'];
            $parent_points['point_count']=$parent_point['point']=$parentObj['point']-$params['point'];
            $parent_points['modified_time']=time();
            $flag = $agent_model->update($parent_point,array('agent_id'=>$parent_points['agent_id']));
            $flagpoints = $agent_point_model->save($parent_points);
            if(!$flag || !$flagpoints)
            {
                throw new Exception('支付失败，支付单明细更新失败');
            }
            //会员
            $sub_points['user_id']=$sub_point['user_id']=$params['member_id'];
            $sub_points['point_count']=$sub_point['point']=round($subObj['point'])+$params['point'];
            $sub_points['modified_time']=time();
            $flagsub = $user_model->update($sub_point,array('user_id'=>$sub_points['user_id']));
            $flagsubpoints = $user_point_model->save($sub_points);
            if(!$flagsub || !$flagsubpoints)
            {
                throw new Exception('支付失败，支付单明细更新失败');
            }
            //代理商日志
            $parent_pointlog['agent_id']=$params['agent_id'];
            $parent_pointlog['modified_time']=time();
            $parent_pointlog['behavior_type']='consume';
            $parent_pointlog['behavior']='积分转让';
            $parent_pointlog['point']=$params['point'];
            $parent_pointlog['remark']=$params['memo'];
            $parent_pointlog['operator']=$accountObj['name'];
            $parent_pointlog['direction']=$login_Obj['login_account'];
            $parent_pointlog['role']='会员';
            $parent_pointlog['sub_id']=$params['member_id'];
            $flaglog = $agent_pointlog_model->save($parent_pointlog);

            //子账号日志
            if($accountObj['account_type'] == 'account'){
                $account_pointlog['agent_id']=$account_id;
                $account_pointlog['modified_time']=time();
                $account_pointlog['behavior_type']='consume';
                $account_pointlog['behavior']='积分转让';
                $account_pointlog['point']=$params['point'];
                $account_pointlog['remark']=$params['memo'];
                $account_pointlog['operator']=$accountObj['name'];
                $account_pointlog['direction']=$login_Obj['login_account'];
                $account_pointlog['role']='会员';
                $account_pointlog['sub_id']=$params['member_id'];
                $account_pointlog['is_account']=1;
                $flaglog = $agent_pointlog_model->save($account_pointlog);
            }
           
            //会员日志
            $user_pointlog['modified_time']=time();
            $user_pointlog['behavior_type']='ontain';
            $user_pointlog['behavior']='购买积分';
            $user_pointlog['point']=$params['point'];
            $user_pointlog['remark']=$params['memo'];
            $user_pointlog['operator']=$params['agent_id'];
            $user_pointlog['direction']=$accountObj['name'];
            $user_pointlog['role']='代理商';
            $user_pointlog['user_id']=$params['member_id'];
            $flagsublog = $user_pointlog_model->save($user_pointlog);
            if(!$flagsublog || !$flaglog)
            {
                throw new Exception('积分日志存入失败');
            }
            $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollback();
            $data['message'] = $e->getMessage();
            $data['status']='false';
            $service->send_user_error('error', $data);
        }

        //获取会员信息：发送积分购买消息到微信
        try{
            $userId = $params['member_id'];
            kernel::single('weixin_base')->jfCound_change($userId,'代理商划拨积分',(int)($params['point']));
        }catch (Exception $e){
            //报错后什么也不处理，不需要处理
        }

        //获取会员信息：发送短信
        try{
            // kernel::single('weixin_base')->jfCound_change($userId,'代理商划拨积分',(int)($params['point']));
            
            $user_change = app::get('sysuser')->getConf('sysuser_setting.user_change')  == 0 ?app::get('sysuser')->getConf('sysuser_setting.user_change'):'1';

            $userId = $params['member_id'];
            
            if((string)$user_change == '1'){
                kernel::single('sysagent_passport')->sendPointSms($parentObj['name'],$params['sub_id'],$params['point'],$type='member');
            }
        }catch (Exception $e){
            //报错后什么也不处理，不需要处理
        }

        $data['status'] = 'true';
        $data['message'] = app::get('sysuser')->_('积分转让成功');
        $service->send_user_succ('success', $data);
    }
    /**
     * ps ：添加会员时验证
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * accesstoken  String  Y   帐号登录返回的 accesst
     * member_mobile    String  N   会员手机号
     * member_login String  N   会员用户名
     * member_password  String  N   会员密码
     * member_mobile、member_login, member_password必填一个参数
     * @return 返回值类型
    */
    public function verify_member($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期',
                    'status'=>'false',
            ]);
        } 
        $model_account = app::get('sysuser')->model('account');
        if($params['member_mobile']){
            if(!preg_match("/^1[34578]\d{9}$/",$params['member_mobile'])){
                $service->send_user_error('error', [
                    'message'=>'请输入正确的手机号',
                    'status'=>'false',
                ]);
            }
            $return = $model_account->getRow('*',array('mobile'=>$params['member_mobile']));
            if($return){
                $service->send_user_error('error', [
                    'message'=>'该手机号已被注册',
                    'status'=>'false',
                ]);
            }
        }
        if($params['member_login']){
            if( strlen(trim($params['member_login']))< 4 )
                {
                    $service->send_user_error('error', [
                        'message'=>'登录账号最少4个字符',
                        'status'=>'false',
                    ]);
                }
                elseif( strlen($params['member_login']) > 100 )
                {
                    $service->send_user_error('error', [
                        'message'=>'登录账号过长，请换一个重试',
                        'status'=>'false',
                    ]);
                }

                if(!preg_match('/^[^\x00-\x2d^\x2f^\x3a-\x3f]+$/i', trim($params['member_login'])) )
                {
                    $service->send_user_error('error', [
                        'message'=>'该登录账号包含非法字符',
                        'status'=>'false',
                    ]);
                }
            $return = $model_account->getRow('*',array('login_account'=>$params['member_login']));
            if($return){
                $service->send_user_error('error', [
                        'message'=>'该账号已被注册，请更换一个重试',
                        'status'=>'false',
                ]);
            }
        }
        if(!($params['member_mobile']||$params['member_login']||$params['member_password'])){
            $service->send_user_error('7001', [
                    'message'=>'请输入至少一个信息',
                    'status'=>'false',
            ]);
        }
        $service->send_user_succ('success', array(
            'status'=>'true',  //添加状态，true表示成功,false失败
            'message'=>'验证成功', //String   消息：帐号添加成功
        ));
    }
    /**
     * ps ：保存会员
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * accesstoken  String  Y   帐号登录返回的 accesst
     * account_id int  Y 当前登录人的id
     * member_mobile    String  Y   会员手机号
     * member_login String  Y   会员用户名
     * member_password  String  N   会员密码
     * pwd_confirm  String  N   会员密码
     * @return 返回值类型
    */
    public function save_member($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期',
                    'status'=>'false',
            ]);
        } 
        if (empty($params['member_mobile']) || empty($params['member_login'])) {
            $service->send_user_error('error', [
                    'message'=>'手机号和用户名不能为空！',
                    'status'=>'false',
            ]);
        }
        if (empty($params['account_id'])) {
            $service->send_user_error('error', [
                    'message'=>'当前登录人不能为空！',
                    'status'=>'false',
            ]);
        }
        $userInfo['account']= $params['member_login'];
        $userInfo['password']= $params['member_password']? $params['member_password']:$params['member_mobile'];
        // $userInfo['pwd_confirm']= $params['pwd_confirm']? $params['pwd_confirm']:$params['member_mobile'];
        //密码确认的密码默认为首次输入的密码 
        $userInfo['pwd_confirm']= $userInfo['password'];
        $userInfo['mobile']=$params['member_mobile'];
        //数据检测
        $validator = validator::make(
            ['loginAccount'=>$userInfo['account'],'password' => $userInfo['password'], 'password_confirmation' =>$userInfo['pwd_confirm']],
            ['loginAccount'=>'required','password' => 'min:6|max:20|confirmed','password_confirmation'=>'required'],
            ['loginAccount'=>'请输入用户名!','password' =>'密码长度不能小于6位!|密码长度不能大于12位!|输入的密码不一致!','password_confirmation'=>'确认密码不能为空!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                $data['message']= $error;
                $service->send_user_error('error', $data);
            }
        }
        try
        {
            // $accountType = kernel::single('pam_tools')->checkLoginNameType($userInfo['account']);
            // kernel::single('sysuser_passport')->checkSignupAccount($userInfo['account'],$accountType);
           //by张艳 2015-12-07 为了不改变原来的代码只能在原来的基础上添加，当手机发展会员时，当用户名不是手机的时候，要把会员手机号码也保存起来，以便后期发短信等使用
            // if($accountType=='login_account'){
                // $userId = userAuth::signUpByApp($params['account_id'],$userInfo['account'],$params['member_mobile'],$userInfo['password'], $userInfo['pwd_confirm']);
            // }else{
            //     $userId = userAuth::signUp($userInfo['account'],$userInfo['password'], $userInfo['pwd_confirm']);
            // }
            // userAuth::login($userId, $userInfo['account']);
            $objUser = kernel::single('sysuser_passport');
            $dataUser=array(
                    'account' => $userInfo['account'],
                      'mobile' => $params['member_mobile'],
                      'account_id' => $params['account_id'],
                      'password' => $userInfo['password'],
                      'pwd_confirm' => $userInfo['pwd_confirm'],
                      'reg_ip' => request::getClientIp()
                );
            $result = $objUser->signupUser($dataUser);

        }
        catch(Exception $e)
        {
            $data['message'] = $e->getMessage();
            $data['status']='false';
            $service->send_user_error('error', $data);
        }
        //发送短信
        try{
            kernel::single('sysagent_passport')->sendMemmberMessage($userInfo);
        }catch(Exception $e){
            // $data['message'] = $e->getMessage();
            // $data['status']='true';
            // $service->send_user_succ('success', $data);
        }
        $data['status']='true';
        $data['message']='添加成功';
        $service->send_user_succ('success', $data);
    }
    /**
     * ps ：我的会员列表，支持模糊搜索
     * Time：2015/11/27 13:02:35
     * @author zhangyan
     * @param 参数类型
     * account_id Int Y   当前登陆代理商或子账号的id
     * where_key    String  N   会员名字或手机号搜索的关键字，模糊匹配
     * accesstoken  String  Y   帐号登录返回的 accesstoken
     * page Int N   页数：默认每页20，如果为空，则全部数据
     * @return 返回值类型
    */
    public function member_list($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期',
                    'status'=>'false',
            ]);
        } 
        if (empty($params['account_id'])) {
            $service->send_user_error('error', [
                    'message'=>'代理商ID不能为空！',
                    'status'=>'false',
            ]);
        }
        $params['page']=$params['page']?$params['page']:1;
        $pagelimit = 20;
        $sql = "select a.username,b.login_account,a.user_id,a.sex,b.mobile,a.point,a.agent_account
                from sysuser_user a
                left join sysuser_account b on a.user_id=b.user_id
                where a.agent_account = '{$params['account_id']}'";
        if($params['where_key']){
            $sql .= "and (a.username like '%{$params['where_key']}%'
                or b.login_account like '%{$params['where_key']}%'
                or b.mobile like '%{$params['where_key']}%')";
        }

        $sql .= " order by b.login_account ";
        $data = db::connection()->fetchAll($sql);
        $count = count($data);

        if($params['page']){
            $npage = ($params['page'] - 1) * $pagelimit;
            $sql .= " limit {$pagelimit} offset {$npage}";
            $data = db::connection()->fetchAll($sql);
        }
        $account_model=app::get('sysagent')->model('account');
        foreach ($data as $key => $value) {
            if($value['username']!=''){
                $agents['name']=$value['username'];    
            }elseif ($value['login_account']!=''){
                $agents['name']=$value['login_account'];    
            }else{
                $agents['name']=$value['mobile'];    
            }
            // $agents['name']=($value['username']!='' ? $value['username']:$value['login_account']);
            $agents['point'] = $value['point'];
            $agents['member_id'] = $value['user_id'];
            $agents['sex'] = $value['sex']=='2'?1:$value['sex'];
            $agents['mobile'] = $value['mobile'].'';
            $accountObj=$account_model->getRow('login_account,name',array('account_id'=>$value['agent_account']));
            $agents['from_username']=$accountObj['login_account'].'';
            $agents['from_name']=$accountObj['name'].'';
            $return_data['members'][] = $agents;
        }
        $return_data['count'] = $count;
        $total = ceil($count / $pagelimit);
        $return_data['current_page'] = intval($total);
        $service->send_user_succ('success', $return_data);
    }
    /**
     * ps ：代理商子代理发展的会员列表，支持模糊搜索
     * Time：2015/11/27 13:02:35
     * @author zhangyan
     * @param 参数类型
     * agent_id Int Y   当前登陆代理商的id
     * where_key    String  N   会员名字或手机号搜索的关键字，模糊匹配
     * accesstoken  String  Y   帐号登录返回的 accesstoken
     * page Int N   页数：默认每页20，如果为空，则全部数据
     * @return 返回值类型
    */
    public function member_all_list($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', '您的会话已过期!');
        } 
        //根据当前代理商，获得所有子代理商
        $agent_model=app::get('sysagent')->model('agents');
        $account_model=app::get('sysagent')->model('account');
        $nodes=kernel::single('sysagent_agent_Nodes')->getChild($params);
        //根据得到的代理商账号，得到代理商登录的account_id
        $params['page']=$params['page']?$params['page']:1;
        $pagelimit=20;
        foreach ($nodes as $key => $value) {
            //得到该代理商的account_id和子账号id
            $agentIds=$account_model->getList('account_id',array('agent_id'=>$value));
            foreach ($agentIds as $account_ids) {
               $accountIds[]=$account_ids['account_id'];
            }
            foreach ($accountIds as $value) {
                $sql="select a.name,a.user_id,a.sex,b.mobile,a.point,a.agent_account
                    from sysuser_user a
                    left join sysuser_account b on a.user_id=b.user_id
                    where a.agent_account = '{$value}'";
                if($params['where_key']){
                $sql .= "and (a.name like '%{$params['where_key']}%'
                            or b.mobile like '%{$params['where_key']}%')";
                }
                $data= db::connection()->fetchAll($sql);
                if (!empty($data)) {
                    $members[]=$data;
                    $count = count($members);
                }
                if($count == 0){
                    $service->send_user_error('error', '未发现记录！');
                }
                if($params['page']){
                    $npage = ($params['page'] - 1) * $pagelimit;
                    $sql .= "limit {$pagelimit} offset {$npage}";
                    $temp_data[] = db::connection()->fetchAll($sql);
                }
            }
        }
        if (!empty($temp_data)) {
            $member_data=$temp_data;
        }else{
            $member_data=$members;
        }
        $account_model=app::get('sysagent')->model('account');
        foreach ($member_data[0] as  $item) {
            $member['name'] = $item['name'];
            $member['point'] = $item['point'];
            $member['member_id'] = $item['user_id'];
            $member['sex'] = $item['sex'];
            $member['mobile'] = $item['mobile'];
            $accountObj=$account_model->getRow('login_account,name',array('account_id'=>$item['agent_account']));
            $member['from_username']=$accountObj['login_account'];
            $member['from_name']=$accountObj['name'];
            $return_data['members'][] = $member;
        }
        $return_data['count'] = $count;
        $total = ceil($count / $pagelimit);
        $return_data['current_page'] = intval($total);
        $service->send_user_succ('success', $return_data);

      /*  return array(
            'members'=>array(
                'member_id'=>'dsfds',   //  Int 会员id
                'sex'=>'dsfds',   //String  性别
                'mobile'=>'dsfds',   // String  手机号
                'point'=>'dsfds',   //  Int 积分余额
                'from'=>'dsfds',   //string  账户来源描述信息
            ),    //array   代理商所有子代理商的会员
            'count'=>'10',   //int 返回总数
            'current_page'=>'1',   // int 当前页数
        );*/
    }
    
}
