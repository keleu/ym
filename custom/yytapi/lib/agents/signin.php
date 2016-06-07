<?php
/*********************************************************************\
*  Copyright (c) 2007-2015, TH. All Rights Reserved.
*  Author :li
*  FName  :getdata.php
*  Time   :2015/11/26 13:32:55
*  Remark :获取代理商
\*********************************************************************/
class yytapi_agents_signin extends yytapi_base {

    /**
     * 获取secret
     * Time：2016/01/14 15:50:09
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    function getsecretkey($params,&$service){
        //有效性验证
        if (empty($params['username']) || empty($params['password'])) {
            $service->send_user_error('7001', array(
                    'message'=>'用户名和密码不能为空',
                    'status'=>'false',
            ));
        }

        try{
            $filter = array('login_account' => $params['username']);
            $account = app::get('sysagent')->model('account')->getRow('account_id,agent_id,login_password,account_type,secret,`key`', $filter);
            if(pam_encrypt::check($params['password'], $account['login_password'])){
               if($account['account_type'] != 'agent'){
                    $service->send_user_error('error', array(
                        'message'=>'代理商才能查看，子帐号不能申请api',
                        'status'=>'false',
                    ));
                }

                $data['agentSecret']=$account['secret'];
                $data['agentKey']=$account['key'];
                $service->send_user_succ('success', $data);

            }else{
                $service->send_user_error('error', array(
                        'message'=>'用户名或密码错误',
                        'status'=>'false',
                ));
            }
        }catch(Exception $e){
            $msg = $e->getMessage();
            $service->send_user_error('error',[
                    'message'=>$msg,
                    'status'=>'false',
            ]);
        }
    }

    /**
     * 获取secret
     * Time：2016/01/14 15:50:09
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    function gettoken($params,&$service){
        //有效性验证
        if (empty($params['username']) || empty($params['password'])) {
            $service->send_user_error('7001', array(
                    'message'=>'用户名和密码不能为空',
                    'status'=>'false',
            ));
        }

        try{
            $filter = array('login_account' => $params['username']);
            $account = app::get('sysagent')->model('account')->getRow('account_id,agent_id,login_password,account_type,token', $filter);
            if(pam_encrypt::check($params['password'], $account['login_password'])){
               if($account['account_type'] != 'agent'){
                    $service->send_user_error('error', array(
                        'message'=>'代理商才能查看',
                        'status'=>'false',
                    ));
                }

                $data['token']=$account['token'];
                $service->send_user_succ('success', $data);

            }else{
                $service->send_user_error('error', array(
                        'message'=>'用户名或密码错误',
                        'status'=>'false',
                ));
            }
        }catch(Exception $e){
            $msg = $e->getMessage();
            $service->send_user_error('error',[
                    'message'=>$msg,
                    'status'=>'false',
            ]);
        }
    }

    /**
     * 设置token
     * Time：2016/01/14 13:45:41
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    function settoken($params,&$service){
        //有效性验证
        if (empty($params['username']) || empty($params['password'])) {
            $service->send_user_error('7001', array(
                    'message'=>'用户名和密码不能为空',
                    'status'=>'false',
            ));
        }

        if (empty($params['token'])) {
            $service->send_user_error('70034', array(
                    'message'=>'token必填',
                    'status'=>'false',
            ));
        }

        try{
            $filter = array('login_account' => $params['username']);
            $account = app::get('sysagent')->model('account')->getRow('account_id,agent_id,login_password,account_type', $filter);
            if(pam_encrypt::check($params['password'], $account['login_password'])){
               if($account['account_type'] != 'agent'){
                    $service->send_user_error('error', array(
                        'message'=>'代理商才能申请',
                        'status'=>'false',
                    ));
                }


                $_data = ['token'=>$params['token']];
                app::get('sysagent')->model('account')->update($_data,['account_id'=>$account['account_id']]);

                $data['status']='true';
                $data['message']='设置成功';
                $service->send_user_succ('success', $data);

            }else{
                $service->send_user_error('error', array(
                        'message'=>'用户名或密码错误',
                        'status'=>'false',
                ));
            }
        }catch(Exception $e){
            $msg = $e->getMessage();
            $service->send_user_error('error',[
                    'message'=>$msg,
                    'status'=>'false',
            ]);
        }
    }

    /**
     * 设置secret key token
     * Time：2016/01/14 13:45:41
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    function setsecretkey($params,&$service){
        //有效性验证
        if (empty($params['username']) || empty($params['password'])) {
            $service->send_user_error('7001', array(
                    'message'=>'用户名和密码不能为空',
                    'status'=>'false',
            ));
        }

        try{
            $filter = array('login_account' => $params['username']);
            $account = app::get('sysagent')->model('account')->getRow('account_id,agent_id,login_password,account_type', $filter);
            if(pam_encrypt::check($params['password'], $account['login_password'])){
                if($account['account_type'] != 'agent'){
                    $service->send_user_error('error', array(
                        'message'=>'代理商才能申请',
                        'status'=>'false',
                    ));
                }
                //重置agentSecret agentKey
                $_secret[] = 'EQINFO_SHOPEX_SECRET';
                $_secret[] = $params['username'];
                $_secret[] = $account['account_id'];
                $_secret[] = $account['agent_id'];
                $_secret[] = rand(100000,999999);
                $agentSecret = 'hld'.$account['account_id'].'_'.md5(implode(',',$_secret));


                $_agentKey[] = 'KEY_EQINFO_SHOPEX_KEY';
                $_agentKey[] = $account['agent_id'];
                $_agentKey[] = $account['account_id'];
                $_agentKey[] = $params['username'];
                $_agentKey[] = rand(1000000,9999999);
                $agentKey = md5(implode(',',$_agentKey));

                // echo $agentKey,'：：',$agentSecret;exit;
                $_data = ['secret'=>$agentSecret,'key'=>$agentKey];
                app::get('sysagent')->model('account')->update($_data,['account_id'=>$account['account_id']]);

                $data['status']='true';
                $data['message']='设置成功';
                $data['agentSecret']=$agentSecret;
                $data['agentKey']=$agentKey;
                $service->send_user_succ('success', $data);

            }else{
                $service->send_user_error('error', array(
                        'message'=>'用户名或密码错误',
                        'status'=>'false',
                ));
            }
        }catch(Exception $e){
            $msg = $e->getMessage();
            $service->send_user_error('error',[
                    'message'=>$msg,
                    'status'=>'false',
            ]);
        }
    }

    /**
     * 代理商获取令牌
     * Time：2016/01/14 10:32:54
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    function getaccesstoken($params,&$service){
        //有效性验证
        if (empty($params['agentSecret']) || empty($params['agentKey'])) {
            $service->send_user_error('7001', array(
                    'message'=>'agentSecret,agentKey参数不能为空',
                    'status'=>'false',
            ));
        }

        $_model = app::get("sysagent")->model('account');

        //查找是否存在对应的代理商
        $agent_info = $_model->getRow('account_id,token,account_type',[
            'secret'=>$params['agentSecret'],
            'key'=>$params['agentKey'],
        ]);

        //是否存在用户或是否授权
        if(!$agent_info['account_id'] || !$agent_info['token']){
            $service->send_user_error('70012', array(
                    'message'=>'验证未通过或用户未授权',
                    'status'=>'false',
            ));
        }

        $data['status'] = 'true';
        $data['account_id'] = $agent_info['account_id'];
        // $data['identity'] = $agent_info['account_type'];
        $data['accesstoken'] = $this->set_accesstoken($agent_info['account_id']);
        $data['message'] = app::get('sysagent')->_('验证成功');
        $service->send_user_succ('success', $data);

    }

    /**
     * ps ：代理商登录
     * Time：2015/11/26 16:16:34
     * @author zhangyan
     * @param 参数类型
     * username       string  Y   登录帐号名
     * password    string  Y   加密过后的s密码
     * @return 返回值类型
    */
    public function signin($params,&$service)
    {
        //有效性验证
        if (empty($params['username'])) {
            $service->send_user_error('7001', array(
                    'message'=>'用户名不能为空',
                    'status'=>'false',
            ));
        }
        if (empty($params['password'])) {
            $service->send_user_error('7001', array(
                    'message'=>'密码不能为空',
                    'status'=>'false',
            ));
        }


        $filter = array('login_account' => $params['username']);
        $account = app::get('sysagent')->model('account')->getRow('account_id,agent_id,login_password,account_type', $filter);

        //登录判断是否有子代理权限
        $authconf = config::get('authconf');
        $rolestl = app::get('sysagent')->model('roles');
        if($account['account_type']=='agent'){
            $account_type = 1;
        }elseif($account['account_type']=='account'){
            $account_type = 2;
        }
        $sdf_roles = $rolestl->dump($account_type);
        $menu_workground = unserialize($sdf_roles['workground']);
        if(in_array('agent_succ',(array)$menu_workground)){
            $data['account_auth'] = 1;
        }elseif(in_array('agent_wait',(array)$menu_workground)){
            $data['account_auth'] = 1;
        }elseif(in_array('accounts_succ',(array)$menu_workground)){
            $data['account_auth'] = 1;
        }elseif(in_array('agent_add',(array)$menu_workground)) {
            $data['account_auth'] = 1;
        }elseif (in_array('accounts_add',(array)$menu_workground)){
            $data['account_auth'] = 1;
        }else{
            $data['account_auth'] = 0;
        }

        if(pam_encrypt::check($params['password'], $account['login_password'])){
            /*$agent_obj=app::get('sysagent')->model('agents')->getRow('status',array('agent_id'=>$account['agent_id']));
            if ($agent_obj['status'] != '1') {
                $service->send_user_error('error', array(
                    'message'=>'您的审核还未通过',
                    'status'=>'false',
                ));
            }*/

        }else{
            $service->send_user_error('error', array(
                    'message'=>'用户名或密码错误',
                    'status'=>'false',
            ));
        }
        $data['status'] = 'true';
        $data['account_id'] = $account['account_id'];
        $data['identity'] = $account['account_type'];
        //因为代理商和子帐号分为两类，所以要用统一登录id
        $_GET['autologin'] = 'on';//手机登录需要保存session时间更长
        $data['accesstoken'] = $this->set_accesstoken($account['account_id']);
        if($_SESSION['sess_time'])$data['sess_time'] = $_SESSION['sess_time'];
        $data['message'] = app::get('sysagent')->_('登录成功');
        $service->send_user_succ('success', $data);
    }
    /**
     * ps ：代理商申请入注
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * recommend  string  Y   推荐人
     * username    string  Y   用户名
     * name    string  Y   姓名
     * mobile  string  Y   手机
     * id_card string  Y   身份证号
     * sex string  N   性别
     * area    String  Y   地区
     * password    string  Y   密码
     * picture  string  Y   身份证/营业执照，照片路径
     * kind int Y   1为个人，2为企业
     * license   Y   是否同意协议
     * @return 返回值类型
    */
    public function agent_applys($params, &$service){
        if (empty($params['username']) || empty($params['name']) || empty($params['mobile']) || empty($params['id_card']) || empty($params['area']) || empty($params['password']) || empty($params['recommend']) || empty($params['kind']) || empty($params['picture'])) {
            $service->send_user_error('7001', array(
                'message'=>'存在必填参数为空',
                'status'=>'false',
            ));
        }
        if(strlen(trim($params['password']))< 6 || strlen(trim($params['password']))>20){
            $service->send_user_error('error', array(
                'message'=>'密码必须是6~20个字符',
                'status'=>'false'
            ));
        }
        if ($params['license'] != '1') {
            $service->send_user_error('error', array(
                'message'=>'请阅读并同意注册协议',
                'status'=>'false',
            ));
        }

        $agent_verify = app::get('sysagent')->getConf('sysagent_setting.agent_verify')?app::get('sysagent')->getConf('sysagent_setting.agent_verify'):'平台方';

        $re = app::get('sysagent')->model('agents')->getRow('agent_id',array('username'=>$params['recommend']));
        if($params['recommend']!= $agent_verify ){
            if (empty($re)) {
                $service->send_user_error('error', array(
                    'message'=>'请确认存在此推荐人！',
                    'status'=>'false',
                ));
            }
        }

        $row = app::get('sysagent')->model('agents')->getRow('agent_id',array('status'=>'1','username'=>$params['recommend']));
        if($params['recommend']!= $agent_verify ){
            if (empty($row)) {
                $service->send_user_error('error', array(
                    'message'=>'推荐人审核没有通过',
                    'status'=>'false',
                ));
            }
        }

        //判断手机号码是否存在
        $mb_confirm = app::get('sysagent')->model('account')->getRow('*',array('mobile'=>$params['mobile']));
        if(!empty($mb_confirm)){
            //判断是否为修改
            if($params['is_again']){
                $temp_mob = app::get('sysagent')->model('account')->getRow('mobile',array('account_id'=>$params['account_id']))['mobile'];
                if($temp_mob!=$params['mobile']){
                    $service->send_user_error('error', array(
                        'message'=>'该手机号码已经存在! ',
                        'status'=>'false',
                    ));
                }
            }else{
                $service->send_user_error('error', array(
                    'message'=>'该手机号码已经存在! ',
                    'status'=>'false',
                ));
            }

        }
        //判断用户名是否存在
        $mb_username = app::get('sysagent')->model('account')->getRow('login_account',array('login_account'=>$params['username']))['login_account'];
         if(!empty($mb_username)){
            //判断是否为修改
            if($params['is_again']){
                if($mb_username!=$params['username']){
                    $service->send_user_error('error', array(
                        'message'=>'该用户名已经存在! ',
                        'status'=>'false',
                    ));
                }
            }else{
                $service->send_user_error('error', array(
                    'message'=>'该用户名已经存在! ',
                    'status'=>'false',
                ));
            }
        }

        $msg = kernel::single('sysagent_passport')->agentCheck($params['username'],'login_account',$params['is_again']);
        if($msg != 'true'){
           $service->send_user_error('error', array(
                'message'=>$msg,
                'status'=>'false',
            ));
        }


        $agent = app::get('sysagent')->model('agents');
        $temp=explode(' ',trim($params['area']));
        $data = array(
            'username' => $params['username'],
            'flag' => 'create',
            'name' => $params['name'],
            'mobile' => $params['mobile'],
            'area_first' => $temp[0],
            'area_second' => $temp[1],
            'area_third' => $temp[2],
            'parent_id' => $row['agent_id']+0,
            'id_card' => $params['id_card'],
            'regtime' => time(),
            'reg_ip' => $_SERVER["REMOTE_ADDR"],
            'sex' => $params['sex'],
            'kind' => $params['kind'],
            'login_password' => $params['password'],
            'picture' => $params['picture']
        );
        $lv = $agent -> getRow('agent_level',array('agent_id'=>$data['parent_id']));
        if(is_array($lv)){
            $data['agent_level'] = $lv['agent_level'] + 1;
        }
        else{
            $data['agent_level'] = 1;
        }
        //2015-1-28 by jiang 判断是否是重新申请
        if($params['is_again']){
            $data['agent_id'] = app::get('sysagent')->model('account')->getRow('agent_id',array('account_id'=>$params['account_id']))['agent_id'];
            $data['account_id']=$params['account_id'];
            $data['is_again']=$params['is_again'];
            $data['status']=0;
        }

        try
        {
            $agent->save($data);
        }
        catch(Exception $e)
        {
            $message = $e->getMessage();
            $service->send_user_error('error',$message);
        }
        $date['message'] = $params['is_again']?app::get('sysagent')->_('申请成功'):app::get('sysagent')->_('注册成功');
        $date['status'] = 'true';
        $service->send_user_succ('success',$date);
    }
    /**
     * ps ：代理商入注时验证
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * username string  N   用户名
     * name    string  N   姓名
     * recommend   string  N   推荐人
     * mobile  string  N   手机
     * id_card string  N   身份证号
     * area    String  N   地区
     * password   string  N   密码
     *      所有参数必填一个参数，可以传递多个参数一起验证
     * @return 返回值类型
    */
    public function verif_agent($params, &$service){
        if (empty($params['username'])) {
            $service->send_user_error('error', array(
                'message'=>'用户名不能为空！',
                'status'=>'false'
            ));
        }
        //判断用户名是否存在，是否存在无效
        $msg = kernel::single('sysagent_passport')->agentCheck($params['username'],'login_account',$params['is_again']);
        if($msg != 'true'){
           $service->send_user_error('error', array(
                'message'=>$msg,
                'status'=>'false'
            ));
        }
        $agent_verify = app::get('sysagent')->getConf('sysagent_setting.agent_verify')?app::get('sysagent')->getConf('sysagent_setting.agent_verify'):'平台方';

        //判断推荐人是否存在
        $re = app::get('sysagent')->model('agents')->getRow('agent_id',array('username'=>$params['recommend']));
        if($params['recommend']!= $agent_verify ){
            if (empty($re)) {
                $service->send_user_error('error', array(
                    'message'=>'请确认存在此推荐人！',
                    'status'=>'false',
                ));
            }
        }

        //判断推荐人是否审核通过
        $row = app::get('sysagent')->model('agents')->getRow('agent_id',array('status'=>'1','username'=>$params['recommend']));

         if($params['recommend']!= $agent_verify ){
            if (empty($row)) {
                $service->send_user_error('error', array(
                    'message'=>'推荐人审核没有通过',
                    'status'=>'false',
                ));
            }
        }

        //手机验证
        $msg = kernel::single('sysagent_passport')->isMobile($params['mobile']);
        if($msg != 'true'){
           $service->send_user_error('error', array(
                'message'=>$msg,
                'status'=>'false',
            ));
        }

        $data['message'] = app::get('sysagent')->_('验证通过');
        $data['status'] = 'true';
        $service->send_user_succ('success',$data);
    }
    /**
     * ps ：代理商重置密码
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * mobile  string  Y   手机号
     * vcode   string  Y 验证码
     * password    string  Y   售点帐号的密码
     * psw_confirm    string  Y   售点帐号的密码
     * @return 返回值类型
    */
    public function reset_password($params, &$service){
        if (empty($params['mobile'])) {
            $service->send_user_error('error', array(
                    'status'=>'false',
                    'message'=>'手机号必填！',
            ));
        }
        $pamData = app::get('sysagent')->model('account')->getRow('account_id',array('mobile'=>$params['mobile']));
        //验证密码是不是合法

        $vcodeData=userVcode::verify($params['vcode'],$params['mobile'],'reset');
        if(!$vcodeData)
        {
            $service->send_user_error('error',array(
                    'status'=>'false',
                    'message'=>'验证码输入错误',
            ));
        }
        $msg = kernel::single('sysagent_passport')->checkPwd($params['password'],$params['psw_confirm']);
        if($msg == 'true'){
            $agent['account_id'] = $pamData['account_id'];
            $agent['login_password'] =  pam_encrypt::make($params['password']);
            $agent['modified_time'] = time();
            try
            {
                app::get('sysagent')->model('account')->update($agent,array('account_id'=>$agent['account_id']));
            }
            catch(Exception $e)
            {
                $msg = $e->getMessage();
                $service->send_user_error('error',array(
                    'status'=>'false',
                    'message'=>$msg,
                ));
            }
            $data['message'] = app::get('sysagent')->_('密码重置成功！');
            $data['status'] = 'true';
            $service->send_user_succ('success',$data);
        }else{
            $data['message'] = app::get('sysagent')->_($msg);
            $data['status'] = 'false';
            $service->send_user_error('success',$data);
        }
    }
    /**
     * ps ：发送验证码
     * Time：2015/12/11 10:53:10
     * @author zhangyan
     * @param 参数类型
     * mobile string Y 手机号码
     * @return 返回值类型
    */
    public function sendVcode($params, &$service){
        $postData = $params;
        $status = false;
        $_vcode='';
        if($postData['mobile']!=''){

            if(!preg_match("/^1[34578]\d{9}$/",$postData['mobile'])){
                $data['message'] = "请输入正确的手机号";
                $service->send_user_error('error', $data);
            }

            if($postData['kind']=='1'){
                $model_account = app::get('sysagent')->model('account');
                $return = $model_account->getRow('*',array('mobile'=>$postData['mobile'],'is_del'=>0));
                if($return){
                    $data['message'] = "该手机号已被注册";
                    $service->send_user_error('error', $data);
                }
            }

            try
            {
                // $status = kernel::single('sysagent_passport')->send_sms($postData['mobile'],'aforgot');
                $status = userVcode::send_sms('reset',$postData['mobile']);

                if($status){
                    $msg = app::get('sysagent')->_('发送成功');
                    $_status = 'true';
                    $_vcode=$status;
                }else{
                    $msg = app::get('sysagent')->_('发送失败');
                    $_status = 'false';
                }
            }
            catch(Exception $e)
            {
                $msg = $e->getMessage();
                $_status = 'false';
            }
        }else{
            $msg = '手机号不能为空';
            $_status = 'false';
        }

        // $_res = $_status=='true' ? 'success' :'error';
        if($_status=='true'){
            $_res = 'success';
            $service->send_user_succ($_res,array(
                'message'=>$msg,
                'status'=>$_status,
                'vcode'=>$_vcode,
            ));
        }else{
            $_res = 'error';
            $service->send_user_error($_res,array(
                'message'=>$msg,
                'status'=>$_status,
            ));
        }

    }
    /**
     * ps ：代理商有推荐人的时候自动取得该代理商的代理地区
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * recommend    string  Y   推荐人
     * @return 返回值类型
    */
    public function auto_area($params, &$service){
        if (empty($params['recommend'])) {
            $service->send_user_error('error', array(
                'message'=>'推荐人不能为空',
                'status'=>'false',
            ));
        }

        //查看是否选中的是平台，如果选中的是平台，则应该是一级代理
        $agent_verify = app::get('sysagent')->getConf('sysagent_setting.agent_verify')?app::get('sysagent')->getConf('sysagent_setting.agent_verify'):'平台方';

        //如果是一级代理，需要自己选择地区
        if($params['recommend'] == $agent_verify){
            $areaMap = area::getMap();
            foreach ($areaMap as $k => $v) {
                $data['area_id'][] = array('id'=>$v['id'],'value'=>$v['value'],'is_last'=>'0');
            }
            $data['sonarea']='';
            $data['area']='';
            $data['message']='一级代理商，需要选择代理地区';
            $service->send_user_succ('success',$data);
        }

        //判断推荐代理商是否通过审核
        $result = app::get('sysagent')->model('agents')->getRow('status',array('username'=>$params['recommend']));
        if($result['status']!='1'){
            $service->send_user_error('error', array(
                'message'=>'推荐人审核没有通过！',
                'status'=>'false',
            ));
        }

        $row = app::get('sysagent')->model('agents')->getRow('area_first,area_second,area_third',array('username'=>$params['recommend']));
        if (empty($row)) {
            $service->send_user_error('error', array(
                'message'=>'推荐人代理地区不存在！',
                'status'=>'false',
            ));
        }
        // dump($row);exit;
        $area=implode(" ",$row);
        if (empty($row['area_second'])) {
            $area=rtrim($area,' ');
        }
        if (empty($row['area_third'])) {
            $area=rtrim($area,' ');
        }

        $area_id=kernel::single('sysagent_passport')->getAreaNameByName([$row['area_first'],$row['area_second'],$row['area_third']]);
        // dump($area);exit;
        // $data = area::getAreaIdPath($area_id);
        // if(!$data){
        //     $service->send_user_error('error', array(
        //         'message'=>'未发现记录！',
        //         'status'=>'false',
        //     ));
        // }

        // foreach ($data as $k => $v) {
        //     $region[$k]['id'] = $v;
        //     $region[$k]['value'] = area::getAreaNameById($v);
        // }
        $data['area']=$area;
        $data['sonarea']=$region;
        $data['area_id']=$area_id;
        $service->send_user_succ('success',$data);
    }
    /**
     * ps ：获取代理商信息（或首页使用）
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string      Y   帐号登录返回的 accesstoken
     * account_id    Int Y   代理商id
     * @return 返回值类型
    */
    public function agent_detail($params, &$service){
        //判断参数的合法性，校验参数,先跳过
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        $accountModel=app::get('sysagent')->model('account');
        $accountRow = $accountModel->getRow('*',array('account_id'=>$params['account_id']));
        $agentsModel=app::get('sysagent')->model('agents');
        $row = $agentsModel->getRow('*',array('agent_id'=>$accountRow['agent_id']));
        if (empty($row)) {
            $service->send_user_error('error', ['message'=>'该代理商不存在！']);
        }

        $enterModel=app::get('sysagent')->model('enterapply');
        $enterObj=$enterModel->getRow('reason',array('agent_id'=>$accountRow['agent_id']));
        //重组地区数组
        $temp=array(
            'area_first'=>$row['area_first'],
            'area_second'=>$row['area_second'],
            'area_third'=>$row['area_third'],
        );
        $area=implode(" ",$temp);
        if (empty($temp['area_third'])) {
            $area=rtrim($area,' ');
        }
        //查找父级代理商
        $parentRow = $agentsModel->getRow('username,name,mobile',array('agent_id'=>$row['parent_id']));
        if((string)$row['agent_level'] == '1' && !$row['parent_id']){
            $parentRow['name'] = '平台方';
        }
        //查找代理商积分

        $agentPoint= app::get('sysagent')->model('agent_points')->getRow('*',array('agent_id'=>$accountRow['agent_id']));

        //如果是子帐号
        if($accountRow['account_type']=='account'){
            //如果子帐号is_del=1 则为停用的子帐号
            if($accountRow['is_del']=='1'){
                $status='3';
            }else{
                $status=$row['status'];
            }
            $data=array(
                'agent_id'=>$accountRow['agent_id'],
                'name'=>$accountRow['name'],
                'username'=>$accountRow['login_account'],
                'mobile'=>$accountRow['mobile'],
                'id_card'=>$accountRow['id_card'],
                'sex'=>$accountRow['sex'],
                'area'=>$area,
                'recommend'=>array(
                    'parent_id' => '',
                    'recommend_name' =>'',
                    'recommend_username' =>'',
                    'recommend_tel' => '',
                ),
                'point'=>round($agentPoint['point_count']+0),
                'expired_point'=>round($agentPoint['expired_point']),
                'level'=>'',
                'status'=>$status,
                'kind'=>$row['kind'],
                'picture'=>'',
                'reason'=>$enterObj['reason'].'',
             );
        }else{
            $data=array(
                'agent_id'=>$accountRow['agent_id'],
                'name'=>$row['name'],
                'username'=>$accountRow['login_account'],
                'mobile'=>$row['mobile'],
                'id_card'=>$accountRow['id_card'],
                'sex'=>$row['sex'],
                'area'=>$area,
                'recommend'=>array(
                    'parent_id' => $row['parent_id'],
                    'recommend_name' => $parentRow['name'].'',
                    'recommend_username' => $parentRow['username'].'',
                    'recommend_tel' => $parentRow['mobile'].'',
                ),
                'point'=>round($agentPoint['point_count']+0),
                'expired_point'=>round($agentPoint['expired_point']),
                'level'=>$row['agent_level'],
                'status'=>$row['status'],
                'kind'=>$row['kind'],
                'picture'=>$row['picture'],
                'reason'=>$enterObj['reason'],
             );
        }

        if($row['status']=='0'){
            $data['reason']='您的帐号还未审核!';
        }
        $service->send_user_succ('success',$data);
    }
    /**
     * ps ：代理商积分日志(账单)
     * Time：2015/11/27 09:10:42
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string  Y   售点帐号登录返回的 accesstoken
     * agent_id int Y   代理商id
     * page Int N   页数：默认每页20
     * @return 返回值类型
    */
    public function agent_point_log($params, &$service){
        //判断参数的合法性，校验参数,先跳过
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        //判断登录帐号是代理商还是子账号
        $agent_model = app::get('sysagent')->model('account');
        $accDate = $agent_model->getRow('agent_id,account_type',array('account_id'=>$account_id));
        $agent_id=$accDate['account_type']=='agent'?$accDate['agent_id']:$account_id;

        //判断是查子代理商的还是查父代理商
        $agent_id=$params['sub_id']?$params['sub_id']:$agent_id;

        //默认每页为20条，默认为0页
        $limit = 20 ;
        // $offset = $params['page'] ? (($params['page'] - 1) * 20) : 1;
        $offset =  ($params['page'] - 1) ? (($params['page'] - 1) * 20) : 0;

        $filter['agent_id']=$agent_id;
        $filter['is_account']=$accDate['account_type']=='agent'?'0':'1';
        $orderby='pointlog_id desc';
        $agent_points=app::get('sysagent')->model('agent_pointlog')->getList('*',$filter,$offset,$limit,$orderby);
        $count_current=count($agent_points);
        $count = app::get('sysagent')->model('agent_pointlog')->count($filter);
        //目前折扣率和积分都还没有算，
        //存的时候也要把转出给谁的身份带上
        if (!empty($agent_points)) {
            foreach ($agent_points as $key => $value) {
                //通过积分获得类型来判断积分的消费还是获得
                // if($value['behavior_type']=='consume'){
                //   $points['point'] = 0-$value['point'];
                // }else{
                //   $points['point'] = $value['point'];
                // }
                $points['pointlog_id'] = $value['pointlog_id'];
                $points['modified_time'] = $value['modified_time'];
                $points['operator'] = $value['operator'];
                $points['point'] = $value['point'];
                $points['behavior_type'] = $value['behavior_type'];
                $points['point_discount'] = round($value['discount'] * $value['point'],2);
                $points['from'] = $this->_getName($value);//$value['direction'];
                $points['role'] = $value['role'];
                $points['kind'] = $value['behavior'];
                $return_data['points'][] = $points;
            }
        }else{
            $return_data['points'] = array();
        }
        $total = ceil($count / $limit);
        $return_data['count'] = $count;
        $return_data['current_page'] = intval($total);
        $service->send_user_succ('success', $return_data);
    }
    /**
     * ps ：代理商积分交易详情
     * Time：2015/11/27 09:26:29
     * @author zhangyan
     * @param 参数类型
     * log_id   int Y   积分日志id
     * accesstoken  string  Y   售点帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function agent_point_detail($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        if (empty($params['log_id'])) {
            $service->send_user_error('error', ['message'=>'日志ID不能为空！']);
        }
        $filter['pointlog_id']=$params['log_id'];
        $agent_points=app::get('sysagent')->model('agent_pointlog')->getRow('*',$filter);
        if (!empty($agent_points) && $agent_points['role']=='代理商') {
            $subObj=app::get('sysagent')->model('agents')->getRow('mobile',array('agent_id'=>$agent_points['sub_id']));
        }
        else if (!empty($agent_points) && $agent_points['role']=='会员') {
            $subObj=app::get('sysuser')->model('account')->getRow('mobile',array('user_id'=>$agent_points['sub_id']));
        }
        if($agent_points['role']=='平台方'){
            $points['operator'] = '管理员';
        }else{
            $points['operator'] = $agent_points['operator'];
        }
        $points['modified_time'] = $agent_points['modified_time'];
        $points['point'] = $agent_points['point'];
        $points['point_discount'] = round($agent_points['discount'] * $agent_points['point'],2);
        $points['from'] = $this->_getName($agent_points);//$agent_points['direction'];
        $points['role'] = $agent_points['role'];
        $points['remark'] = $agent_points['remark'];
        $points['behavior'] = $agent_points['behavior'];
        $points['mobile'] = $subObj['mobile'];
        $points['status'] = '交易成功';
        $service->send_user_succ('success', $points);
    }
    /**
      * ps 设置积分折扣
      * Time：2015/11/27 09:31:57
      * @author zhangyan
      * @param 参数类型
      * account_id    int Y   代理商id
      * discount    string  Y   折扣率
      * @return 返回值类型
     */
    public function save_agent_config($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期!',
                    'status'=>'false',
            ]);
        }
        if (empty($params['discount'])) {
            $service->send_user_error('error', [
                    'message'=>'折扣率不能为空！',
                    'status'=>'false',
            ]);
        }
        $agent_id=$this->getAagentId($account_id);
        $config_arr['discount_sub']=round($params['discount']/100,4);
        $config_arr['agent_id']=$agent_id;
        $configObj=app::get('sysagent')->model('agent_config')->getRow('config_id',array('agent_id'=>$agent_id));
        if (!empty($configObj['config_id'])) {
            $config_arr['config_id']=$configObj['config_id'];
        }
        try
        {
            app::get('sysagent')->model('agent_config')->save($config_arr);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $service->send_user_error('error',[
                    'message'=>$msg,
                    'status'=>'false',
            ]);
        }
        $data['message'] = app::get('sysagent')->_('配置成功');
        $data['status'] = 'true';
        $service->send_user_succ('success',$data);
    }
    /**
     * ps ：子代理商购买积分折扣
     * Time：2015/12/21 11:18:27
     * @author zhangyan
     * @param 参数类型
     * account_id    int Y   代理商id
     * discount    string  Y   折扣率
     * sub_id    string  Y   子代理id
     * @return 返回值类型
    */
    public function save_sub_discount($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期',
                    'status'=>'false',
            ]);
        }
        if (empty($params['discount'])) {
            $service->send_user_error('error', [
                    'message'=>'折扣率不能为空！',
                    'status'=>'false',
            ]);
        }
        $config_arr['discount']=$params['discount']/100;
        $config_arr['agent_id']=$params['sub_id'];
        try
        {
            app::get('sysagent')->model('agents')->update($config_arr,array('agent_id'=>$params['sub_id']));
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $service->send_user_error('error',[
                    'message'=>$msg,
                    'status'=>'false',
            ]);
        }
        $data['message'] = app::get('sysagent')->_('配置成功');
        $data['status'] = 'true';
        $service->send_user_succ('success',$data);
    }
    /**
      * ps 子代理商,未审核子代理商，子帐号总数量
      * Time：2015/11/27 09:31:57
      * @author zhangyan
      * @param 参数类型
      * account_id    int Y   代理商id
      * accesstoken string  Y   帐号登录返回的 accesstoken
      * @return 返回值类型
     */
    public function sub_counts($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        $agent_id=$this->getAagentId($account_id);
        $agent_model=app::get('sysagent')->model('agents');
        $filter_audit['parent_id']=$filter_agent['parent_id']=$agent_id;
        $filter_agent['status']='1';
        $filter_audit['status']='0';
        //代理商账号
        $return_data['agent_count']=$agent_model->count($filter_agent);
        //未审核子代理上账号
        $return_data['audit_count']=$agent_model->count($filter_audit);
        //子代理账号
        $filter_account['agent_id']=$agent_id;
        $filter_account['account_type']='account';
        $filter_account['is_del']='0';
        $return_data['account_count']=app::get('sysagent')->model('account')->count($filter_account);
        $service->send_user_succ('success',$return_data);
    }
    /**
     * ps ：我的子代理商列表支持首字母缩写查询
     * Time：2015/11/27 09:42:50
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * accesstoken string  Y   帐号登录返回的 accesstoken
     * status   int N   是否审核，0，未审核，1为审核通过，2，为审核未通过，3为拒绝，参数不填为全部
     * @return 返回值类型
    */
    public function subagent_list($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        $agent_id=$this->getAagentId($account_id);
        $filter_agent['parent_id']=$agent_id;
        //不为空的时候才有该条件
        if ($params['status']!='') {
            $filter_agent['status']=$params['status'];
        }
        $agent_model=app::get('sysagent')->model('agents');
        $agent_list=$agent_model->getList('agent_id,name,username,id_card,point,sex,mobile,kind',$filter_agent);
        //按照首字母排序
        // if(count($agent_list)){
            foreach ($agent_list as $k => &$v) {
                $v['name'] = $v['name'];
                $v['key'] = kernel::single('yytapi_accounts_info')->getfirstchar($v['name']);
                if(is_null($v['key'])){
                    $v['key']= 'Z';
                }
            }
            usort($agent_list, function($a, $b) {
                $al = $a['key'];
                $bl = $b['key'];
                if ($al == $bl)
                    return 0;
                return ($al > $bl) ? 1 : -1;
            });
            foreach ($agent_list as $keyagent => $item) {
                $agent_obj['agent_id'] = $item['agent_id'];
                $agent_obj['name'] = $item['name'];
                $agent_obj['username'] = $item['username'];
                $agent_obj['id_card'] = $item['id_card'];
                $agent_obj['point'] = $item['point'];
                $agent_obj['sex'] = $item['sex'];
                $agent_obj['mobile'] = $item['mobile'];
                $agent_obj['key'] = $item['key'];
                $agent_obj['kind'] = $item['kind'];
                $return_data['agents'][] = $agent_obj;
            }
            if(!$return_data){
                 $return_data['agents']=array();
            }
            // if(!$return_data){
            //      $service->send_user_succ('success', [
            //         'message'=>'未发现记录！',
            //     ]);
            // }
            $service->send_user_succ('success', $return_data);
        // }
        // else{
        //     $service->send_user_error('error', ['message'=>'未发现记录！']);
        // }
    }
    /**
     * ps ：我的子代理商详情
     * Time：2015/11/27 09:46:19
     * @author zhangyan
     * @param 参数类型
     * account_id    int Y   代理商id
     * accesstoken string  Y   帐号登录返回的 accesstoken
     * sub_id   int Y   子代理商id
     * @return 返回值类型
    */
    public function subagent_detail($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        $agent_id=$this->getAagentId($account_id);
        if (empty($params['sub_id'])) {
            $service->send_user_error('error', ['message'=>'子代理商ID不能为空！']);
        }
        $user_model=app::get('sysuser')->model('user');
        $agent_model=app::get('sysagent')->model('agents');
        $audit_model=app::get('sysagent')->model('audit_cancel');

        $return_data=$agent_model->getRow('name,username,id_card,point,sex,mobile,status,discount,kind,picture,parent_id,is_stop',array('agent_id'=>$params['sub_id']));
        $return_data['sub_id']=$params['sub_id'];

        //当代理商的折扣率为空时，取代理商最初设置的折扣率
        if (empty($return_data['discount'])) {
            $temp_discount=app::get('sysagent')->model('agent_config')->getRow('discount_sub',array('agent_id'=>$agent_id));
             $return_data['discount']=$temp_discount['discount_sub'];
        }
        //2016-2-2 by jiang 当代理商默认折扣为空并且代理商为未审核  则取父代理商的积分折扣率
        if(empty($return_data['discount']) && $return_data['status']=='0'){
            $return_data['discount']=$agent_model->getRow('discount',array('agent_id'=>$agent_id))['discount'];
        }

        //子代理商账号
        $return_data['agent_count']=$agent_model->count(array('parent_id'=>$agent_detail['agent_id']));
        //会员个数
        $return_data['mem_count']=$user_model->count(array('agent_account'=>$agent_detail['agent_id']));
        //获取地区
        $row=$agent_model->getRow('area_first,area_second,area_third',array('agent_id'=>$params['sub_id']));
        //获取代理商的审核状态 方便控制是否可以取消代理商资格
        $apply_status = $audit_model->getRow('apply_status',array('agent_id'=>$params['sub_id']))['apply_status'];
        if($apply_status=='1' || $apply_status=='0'){
            $return_data['apply_status'] = 1 ;
        }else{
            $return_data['apply_status'] = 0 ;
        }
        $area=implode(" ",$row);
        if (empty($row['area_third'])) {
            $area=rtrim($area,' ');
        }
        //根据子代理获取总购买金额
        $sql = 'SELECT SUM(point) AS nums FROM `' . $db->prefix . 'sysagent_agent_pointlog`'
                . 'WHERE agent_id=' . $params['sub_id'] . ' AND behavior_type="obtain"';
        $buy_count= db::connection()->fetchAll($sql);
        $return_data['area']=$area;
        $return_data['buy_count']=$buy_count[0]['nums']?$buy_count[0]['nums']:0;
        //获取父代理商的用户名和手机号
        $retparent=$agent_model->getRow('username,mobile',array('agent_id'=>$agent_id));
        $return_data['parent_username']=$retparent['username'];
        $return_data['parent_mobile']=$retparent['mobile'];

        //显示100积分等于多少元
        $return_data['discount']=round($return_data['discount']*100,3);
        $return_data['discount']=$return_data['discount']?$return_data['discount']:'';
        $service->send_user_succ('success',$return_data);
    }
    /**
     * ps ：审核子代理商
     * Time：2015/11/27 09:49:18
     * @author zhangyan
     * @param 参数类型
     * account_id    int Y   代理商id
     * accesstoken string  Y   帐号登录返回的 accesstoken
     * sub_id   int Y   子代理商id
     * apply_status string  Y   审核状态
     * discount string  Y   积分折扣
     * reason string  Y   不同意原因
     * @return 返回值类型
    */
    public function save_subagent_status($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        $agent_id=$this->getAagentId($account_id);
        if (empty($params['sub_id'])) {
            $service->send_user_error('error', [
                'message'=>'子代理商ID不能为空！',
                'status'=>'false'
            ]);
        }
        if (empty($params['apply_status'])) {
            $service->send_user_error('error', [
                'message'=>'审核状态不能为空！',
                'status'=>'false'
            ]);
        }
        $enter_model=app::get('sysagent')->model('enterapply');
        $enterapply = $enter_model->getRow('*',array('agent_id'=>$params['sub_id']));
        if(count($enterapply)==0){
            $service->send_user_error('error', [
                'message'=>'子代理商帐号不存在',
                'status'=>'false'
            ]);
        }
        $sdf['agree_time'] = time();
        $sdf['enterapply_id'] = $enterapply['enterapply_id'];;
        $sdf['apply_status'] = $params['apply_status'];
        switch ($params['apply_status']) {
            case 'successful':
                $agentObj['status']='1';
                break;
            case 'failing':
                $agentObj['status']='2';
                if (empty($params['reason'])) {
                    $service->send_user_error('error', [
                        'message'=>'拒绝原因必填！',
                        'status'=>'false'
                    ]);
                }
                break;
        }
        $sdf['reason'] = $params['reason'];
        if ($agentObj['status'] =='1') {
            if (empty($params['discount'])) {
                $service->send_user_error('error', [
                        'message'=>'积分折扣不能为空！',
                        'status'=>'false'
                    ]);
            }
        }
        $agentObj['agent_id']=$params['sub_id'];
        $agentObj['discount']=$params['discount']/100;
        //审核通过后需要更新关系表，by liuxin，2015-11-24
        $status = $params['apply_status'];
        if($agentObj['status'] == '1'){
            $agentObj['flag']="allow";
        }
        try
        {
            $enter_model->save($sdf);
            app::get('sysagent')->model('agents')->save($agentObj);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $service->send_user_error('error', [
                'message'=>$msg,
                'status'=>'false'
            ]);
        }

        try{
            $agent_auditing  = app::get('sysagent')->getConf('sysagent_setting.agent_auditing') == 0 ? app::get('sysagent')->getConf('sysagent_setting.agent_auditing'):'1';

            if((string)$agent_auditing =='1'){
                kernel::single('sysagent_passport')->sendVettedMessage($params['sub_id'],$status);
            }
        }catch(Exception $e){
            //不处理
        }

        $data['status'] = 'true';
        $data['message'] = app::get('sysagent')->_('操作成功');
        $service->send_user_succ('success', $data);
    }
    /**
     * ps ：我的子代理商所有附属子代理列表支持搜索(暂时不用)
     * Time：2015/11/27 09:51:53
     * @author zhangyan
     * @param 参数类型
     * account_id    int Y   代理商id
     * accesstoken string  Y   帐号登录返回的 accesstoken
     * status   int N   是否审核，0，未审核，1为审核通过，2，为审核未通过，3为拒绝，参数不填为全部
     * where_key string  N   代理商用户名或者手机搜索。模糊匹配
     * page Int N   页数：默认每页20
     * @return 返回值类型
    */
    public function subagent_agent_list($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', '您的会话已过期!');
        }
        $agent_id=$this->getAagentId($account_id);
        $params['agent_id']=$agent_id;
        $agent_list=$this->getChildDetail($params);
        //存的时候也要把转出给谁的身份带上
        $agent_model=app::get('sysagent')->model('agents');
        if (!empty($agent_list)) {
            foreach ($agent_list['item'] as $key => $value) {
                $agents['name'] = $value['name'];
                $agents['point'] = $value['point'];
                $agents['username'] = $value['username'];
                $agents['id_card'] = $value['id_card'];
                $agents['sex'] = $value['sex'];
                $agents['mobile'] = $value['mobile'];
                $agents['level'] = $value['agent_level'];
                $parentObj=$agent_model->getRow('name',array('agent_id'=>$value['parent_id']));
                $agents['parent']=$parentObj['name'];
                $sql = 'SELECT SUM(point) AS nums FROM `' . $db->prefix . 'sysagent_agent_pointlog`'
                . 'WHERE agent_id=' . $params['agent_id'] . ' AND behavior_type="obtain"';
                $buy_count= db::connection()->fetchAll($sql);
                $agents['buy_count']=$buy_count[0]['nums'];
                $return_data['agents'][] = $agents;
            }
        }
        $count=count($row['count']);
        $limit = $params['page'] ? $params['page'] : 20;
        $total = ceil($count / $limit);
        $return_data['current_page'] = intval($total);
        $service->send_user_succ('success', $return_data);
       /* return array(
           'agents'=>array(   // Array    data为代理商id对应的所有子代理商信息
                'name'=>'ddgfdfj',        // string  用户名
                'username'=>'ddgfdfj',        //   String  姓名
                'id_card'=>'ddgfdfj',        //String  身份证号码
                'point'=>'ddgfdfj',        //  Int 积分余额
                'sex'=>'ddgfdfj',        //Int 性别
                'mobile'=>'ddgfdfj',        // String  手机
                'level'=>'ddgfdfj',        //string  等级
                'parent'=>'ddgfdfj',        //int 父代理
                'buy_count'=>'ddgfdfj',        //string  采购总额
            ),
            'count'=> 10,    //int 返回日志总数
            'current_page'=> 1,    // int 当前页数
        );*/
    }
    /**
     * ps ：代理商取消代理资格时，需要审核
     * Time：2015/11/27 10:14:31
     * @author zhangyan
     * @param 参数类型
     * account_id    int Y   代理商id
     * sub_id   int Y   子代理商id
     * apply_status   int Y
     * accesstoken  string  Y   帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function cancel_agent($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                'message'=>'您的会话已过期!',
                'status'=>'false',
            ]);
        }
        $agent_id=$this->getAagentId($account_id);
        if (empty($params['sub_id'])) {
            $service->send_user_error('error', [
                'message'=>'子代理商ID不能为空！',
                'status'=>'false',
            ]);
        }
        if (empty($params['apply_status'])) {
            $service->send_user_error('error', [
                'message'=>'审核状态不能为空！',
                'status'=>'false',
            ]);
        }
        $enter_model=app::get('sysagent')->model('enterapply');
        $enterapply=$enter_model->getRow('*',array('agent_id'=>$params['sub_id']));
        $apply = app::get('sysagent')->model('audit_cancel');
        $sdf['add_time'] = time();
        $sdf['apply_status'] = '0';
        $sdf['enterapply_id'] = $enterapply['enterapply_id'];;
        $sdf['agent_id']=$params['sub_id'];
        $agent_obj['agent_id']=$params['sub_id'];
        $agent_obj['is_stop']='1';
        try
        {
            app::get('sysagent')->model('agents')->save($agent_obj);
            $apply->save($sdf);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $service->send_user_error('40100001', [
                'message'=>$msg,
                'status'=>'false',
            ]);
        }
        $data['status'] = 'true';
        $data['message'] = app::get('sysagent')->_('申请成功');
        $service->send_user_succ('success', $data);
    }
    /**
     * ps ：发展子代理商
     * Time：2015/11/27 10:14:31
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string  Y   帐号登录返回的 accesstoken
     * account_id    int Y   代理商id
     * name  string  Y   子代理商代理商姓名
     * mobile   string  Y   子代理商电话
     * @return 返回值类型
    */
    public function save_subagent($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                'message'=>'您的会话已过期!',
                'status'=>'false',
            ]);
        }
        if (empty($params['name'])) {
            $service->send_user_error('error', [
                'message'=>'子代理商姓名不能为空！',
                'status'=>'false',
            ]);
        }
        if (empty($params['mobile'])) {
            $service->send_user_error('error', [
                'message'=>'手机号码不能为空！',
                'status'=>'false',
            ]);
        }

        $model_account = app::get('sysagent')->model('account');
        $agent = $model_account->getRow('*',array('account_id'=>$params['account_id']));
        $params['agent_id'] = $agent['agent_id'];

        try{
            kernel::single('sysagent_passport')->sendSubMessage($params);
        }catch(Exception $e){
            //先不做任何操作
            $msg = $e->getMessage();
            $service->send_user_error('40100001', [
                'message'=>'短信发送失败'.($params['test']=='on' ? $msg : ''),
                'status'=>'false',
            ]);
        }


        $data['status'] = 'true';
        $data['message'] = app::get('sysagent')->_('发展成功');
        $service->send_user_succ('success', $data);
    }
    /**
     * ps ：子代理积分划拨
     * Time：2015/11/27 10:14:31
     * @author zhangyan
     * @param 参数类型
     * account_id    int Y   代理商id
     * sub_id   Int Y   子代理商id
     * point    int Y   转账积分
     * accesstoken  string  Y   帐号登录返回的 accesstoken
     * memo string  N   转账备注
     * @return 返回值类型
    */
    public function save_integral($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                'message'=>'您的会话已过期!'
            ]);
        }

        $_status = 'false';
        $agent_id=$this->getAagentId($account_id);
        if (empty($params['sub_id'])) {
            $service->send_user_error('error', [
                'message'=>'代理商ID不能为空！',
                'status'=>$_status
            ]);
        }
        if (empty($params['point']) || round($params['point']) != $params['point'] || round($params['point']) < 0) {
            $service->send_user_error('error', [
                'message'=>'积分不能为空且必须大于零！',
                'status'=>$_status
            ]);
        }
        $agent_point_model=app::get('sysagent')->model('agent_points');
        $agent_pointlog_model=app::get('sysagent')->model('agent_pointlog');
        $agent_model=app::get('sysagent')->model('agents');
        $parentObj=$agent_model->getRow('name,point',array('agent_id'=>$agent_id));
        $subObj=$agent_model->getRow('name,point',array('agent_id'=>$params['sub_id']));
        //获取积分折扣率
        $agent_config=app::get('sysagent')->model('agents')->getRow('discount',array('agent_id'=>$params['sub_id']));
        if (!empty($agent_config)) {
            $sub_pointlog['discount']=$parent_pointlog['discount']=$agent_config['discount'];
        }else{
            $configObj=app::get('sysagent')->model('agent_config')->getRow('discount_sub',array('agent_id'=>$agent_id));
            $sub_pointlog['discount']=$parent_pointlog['discount']=$configObj['discount_sub'];
        }
        if ($params['point'] > $parentObj['point']) {
            $service->send_user_error('error', [
                'message'=>'您的积分不够！',
                'status'=>$_status
            ]);
        }
        $db = app::get('sysagent')->database();
        $db->beginTransaction();
        try
        {
            //转让给子代理,代理商要改变三张表，子代理也要改变相同的三张表，要开启事物，用到回滚
            //首先组织agents,agent_points表的数据表的数据
            //父代理商
            $parent_points['agent_id']=$parent_point['agent_id']=$agent_id;
            $parent_points['point_count']=$parent_point['point']=$parentObj['point']-$params['point'];
            $parent_points['modified_time']=time();
            $flag = $agent_model->save($parent_point);
            $flagpoints = $agent_point_model->save($parent_points);
            if(!$flag)
            {
                $service->send_user_error('error', [
                    'message'=>'积分划拨失败',
                    'status'=>$_status
                ]);
            }
            if(!$flagpoints)
            {
                $service->send_user_error('error', [
                    'message'=>'积分划拨失败',
                    'status'=>$_status
                ]);
            }
            //子代理商
            $sub_points['agent_id']=$sub_point['agent_id']=$params['sub_id'];
            $sub_points['point_count']=$sub_point['point']=round($subObj['point'])+$params['point'];
            $sub_points['modified_time']=time();
            $flagsub = $agent_model->save($sub_point);
            $flagsubpoints = $agent_point_model->save($sub_points);
            if(!$flagsub)
            {
                $service->send_user_error('error', [
                    'message'=>'积分划拨失败',
                    'status'=>$_status
                ]);
            }
            if(!$flagsubpoints)
            {
                $service->send_user_error('error', [
                    'message'=>'积分划拨失败',
                    'status'=>$_status
                ]);
            }
            //组织,agent_pointlog数据结构
            $parent_pointlog['agent_id']=$agent_id;
            $parent_pointlog['modified_time']=time();
            $parent_pointlog['behavior_type']='consume';
            $parent_pointlog['behavior']='积分划拨';
            $parent_pointlog['point']=$params['point'];
            $parent_pointlog['remark']=$params['memo'];
            $parent_pointlog['operator']=$parentObj['name'];
            $parent_pointlog['direction']=$subObj['name'];
            $parent_pointlog['role']='代理商';
            $parent_pointlog['sub_id']=$params['sub_id'];
            $flaglog = $agent_pointlog_model->save($parent_pointlog);
            if(!$flaglog)
            {
                $service->send_user_error('error', [
                    'message'=>'积分日志存入失败',
                    'status'=>$_status
                ]);
            }

            //子代理商
            $sub_pointlog['agent_id']=$params['sub_id'];
            $sub_pointlog['modified_time']=time();
            $sub_pointlog['behavior_type']='obtain';
            $sub_pointlog['behavior']='购买积分';
            $sub_pointlog['point']=$params['point'];
            $sub_pointlog['remark']=$params['memo'];
            $sub_pointlog['operator']=$parentObj['name'];
            $sub_pointlog['direction']=$subObj['name'];
            $sub_pointlog['role']='代理商';
            $sub_pointlog['sub_id']=$agent_id;
            $flagsublog = $agent_pointlog_model->save($sub_pointlog);
            if(!$flagsublog)
            {
                $service->send_user_error('error', [
                    'message'=>'积分日志存入失败',
                    'status'=>$_status
                ]);
            }

            $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollback();
            $msg = $e->getMessage();
            $service->send_user_error('error', ['message'=>$msg,'status'=>$_status]);
        }

        //发送短信
        //是否需要发送短信
        try{
            $agent_arrival = app::get('sysagent')->getConf('sysagent_setting.agent_arrival')  == 0 ?app::get('sysagent')->getConf('sysagent_setting.agent_arrival'):'1';
            if((string)$agent_arrival == '1'){
                kernel::single('sysagent_passport')->sendPointSms($parentObj['name'],$params['sub_id'],$params['point']);
            }
        }
        catch (Exception $e)
        {
            //报错什么也不做，因为短信不发送不影响这个问题
        }

        $data['status'] = 'true';
        $data['message'] = app::get('sysagent')->_('积分转让成功');
        $service->send_user_succ('success', $data);
    }
    /**
     * ps ：删除子代理
     * Time：2015/11/27 10:14:31
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * sub_id   Int Y   子代理商id
     * accesstoken  string  Y   帐号登录返回的 accesstoken
     * @return 返回值类型
    */
/*    public function delete_agent($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', '您的会话已过期!');
        }
        if (empty($params['agent_id'])) {
            $service->send_user_succ('error', '代理商ID不能为空！');
        }
        if (empty($params['sub_id'])) {
            $service->send_user_succ('error', '代理商ID不能为空！');
        }

        $data_post['delagent_id'][0]=$params['sub_id'];
        $data_post['operation_type']='delete';

        if(!sysagent_ctl_admin_agent::changeParent($data_post)){
            $service->send_user_succ('error', '删除代理商失败');
        }
        $data['status'] = 'true';
        $data['message'] = app::get('sysagent')->_('删除成功');
        $service->send_user_succ('success', $data);
    }*/
    /**
     * ps ：根据子代理商设置的折扣率计算钱数
     * Time：2015/12/04 16:06:49
     * @author zhangyan
     * @param 参数类型
     * account_id   int Y   代理商登录Id
     * sub_id   int Y   子代理
     * point   int Y   积分
     * accesstoken  string  Y   帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function get_money($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        $agent_id=$this->getAagentId($params['account_id']);
        if (empty($params['point']) || round($params['point']) != $params['point']) {
            $service->send_user_error('error', ['message'=>'积分不能为空且积分要填数字！']);
        }
        $agent_model=app::get('sysagent')->model('agents');
        $agentObj=$agent_model->getRow('discount,parent_id',array('agent_id'=>$agent_id));

        //判断该代理商是否存在折扣金额
        if (!empty($agentObj['discount'])&&$agentObj['discount']>=0.01) {
            $discount=$agentObj['discount'];
        }else{
            $agent_discount=app::get('sysagent')->model('agent_config')->getRow('discount_sub',array('agent_id'=>$agentObj['parent_id']));
            if(!$agent_discount['discount_sub']){
               $discount = app::get('sysagent')->getConf('sysagent_setting.agent_ratio');
            }else{
               $discount=$agent_discount['discount_sub'];
            }
        }
        $momey['momey']=round($params['point']) * $discount;
        $service->send_user_succ('success', $momey);
    }
     /**
     * ps ：退出
     * Time：2015/12/04 16:06:49
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string  Y   帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function logout($params, &$service){
        $ret = $this->sess_destory($params['accesstoken']);
        if(!$ret) {
            $service->send_user_error('40100001', [
                'message'=>'注销失败!',
                'status'=>'false',
            ]);
        }
        $infoSuccess['message'] = '注销成功';
        $infoSuccess['status'] = 'true';
        $service->send_user_succ('success', $infoSuccess);
    }
      /**
     * ps ：获取所有子节点的详细信息
     * Time：2015/12/03 12:38:24
     * @author 张艳
     * @param array 源节点
     * @return array 子节点id
    */
    public function getChildDetail($nodes){
        $sourceNode = app::get('sysagent')->model('agent_relation')->getRow('*',array('agent_id'=>$nodes['agent_id']));
        $str=$sql = "select r.*,a.username,a.id_card,a.point,a.sex,a.mobile,a.agent_level,a.parent_id,a.status,a.name
                from sysagent_agent_relation r
                LEFT JOIN sysagent_agents a ON a.agent_id=r.agent_id
                where r.left_id > {$sourceNode['left_id']}
                            and r.right_id <= {$sourceNode['right_id']}";
        $str=  "select r.agent_id
                from sysagent_agent_relation r
                LEFT JOIN sysagent_agents a ON a.agent_id=r.agent_id
                where r.left_id > {$sourceNode['left_id']}
                            and r.right_id <= {$sourceNode['right_id']}";
        if($nodes['status']){
            $sql .= " and a.status = {$nodes['status']}";
            $str .= " and a.status = {$nodes['status']}";
        }
        if($nodes['where_key']){
            $sql .= " and (a.name like '%{$nodes['where_key']}%'
                        or a.mobile like '%{$nodes['where_key']}%')";
            $str .= " and (a.name like '%{$nodes['where_key']}%'
                        or a.mobile like '%{$nodes['where_key']}%')";
        }
        if($nodes['page']){
            $limit = $nodes['page'] ? $nodes['page'] : 20;
            $offset = $nodes['page_index'] ? (($nodes['page_index'] - 1) * $limit) : 0;
            $sql .= " limit {$offset},{$limit}";
        }
        $row['item']= db::connection()->fetchAll($sql);
        //获取总数量
        $row['count']= db::connection()->fetchAll($str);
        return $row;
    }
    /**
     * ps ：保存我够买的积分数量
     * Time：2015/12/11 10:08:37
     * @author liuxin
     * @param 参数类型
     * account_id int 代理商账号id
     * accesstoken string 登录返回的验证信息
     * discount float 折扣率
     * point int 购买的积分数量
     * amount float 支付的金额
     * @return 返回值类型
    */
    public function buyPoint($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)||$account_id!=$params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        if(!($params['account_id']&&$params['point'])){
            $service->send_user_error('error', [
                'message'=>'所有参数不能为空',
                'status'=>'false',
            ]);
        }
        $params['agent_id'] = app::get('sysagent')->model('account')->getRow('agent_id',array('account_id'=>$account_id))['agent_id'];
        $params['parent_id'] = app::get('sysagent')->model('agents')->getRow('parent_id',array('agent_id'=>$params['agent_id']))['parent_id'];
        $data = array(
            'agent_seller' => $params['parent_id'],
            'agent_buyer' => $params['agent_id'],
            'discount' => $params['discount'],
            'settlement_fee_point' => $params['point'],
            'settlement_fee_amount' => $params['amount']
        );
        $result = app::get('sysuser')->rpcCall('agent.point.trade',$data);
        if($result['state'] == 'success'){
            $service->send_user_succ('success', [
                'message'=>'购买成功',
                'status'=>'true',
            ]);
        }
        else{
            $service->send_user_error('error', [
                'message'=>$result['msg'],
                'status'=>'false',
            ]);
        }
    }

    /**
     * ps ：获取代理商积分的信息
     * Time：2015/12/11 10:08:37
     * @author liuxin
     * @param 参数类型
     * account_id int 代理商账号id
     * accesstoken string 登录返回的验证信息
     * @return 返回值类型
     * max_count int 最大可购买积分
     * discount_self float 购买积分的折扣率
     * discount_sub float 卖出积分的折扣率
    */
    public function getPointConf($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)||$account_id!=$params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        $app = app::get('sysagent');
        $agent_id = $app->model('account')->getRow('agent_id',array('account_id'=>$params['account_id']))['agent_id'];

        //判断是不是获取子代理商的积分
        $agent_id=$params['sub_id']?$params['sub_id']:$agent_id;

        $agent_info = $app->model('agents')->getRow('parent_id,discount',array('agent_id'=>$agent_id));
        $agent_parent = $app->model('agents')->getRow('point,discount',array('agent_id'=>$agent_info['parent_id']));
        if (empty($agent_parent)) {
            if(empty($agent_info['discount'])){
                $return['discount_self']=app::get('sysagent')->getConf('sysagent_setting.agent_ratio')?app::get('sysagent')->getConf('sysagent_setting.agent_ratio'):'0.1';
            }else{
                $return['discount_self']=$agent_info['discount'];
            }
            $return['max_count'] = '10000000';
        }else{
            $return['max_count'] = $agent_parent['point'];
            $return['discount_self'] = $agent_info['discount'];

        }
        //查找子代理商购买积分价的默认值
        $return['discount_sub'] = $app->model('agent_config')->getRow('discount_sub',array('agent_id'=>$agent_id))['discount_sub'];
        //如果子代理商购买积分默认值为空 取父代理商的积分折扣 2016年1月31日
        // if($return['discount_sub']=='' || $return['discount_sub'] == '0'){
        //     $return['discount_sub'] = round($agent_info['discount']*100,3);
        // }else{
        //     //显示100个积分等于多少元
        //     $return['discount_sub']=round($return['discount_sub']*100,3);
        // }
        //显示100个积分等于多少元
        $return['discount_sub']=round($return['discount_sub']*100,3);
        $return['discount_sub']=$return['discount_sub']?$return['discount_sub']:'';
        $return['discount_self']=round($return['discount_self']*100,3);
        $service->send_user_succ('success', $return);
    }

    /**
     * ps ：获取支付方式（支付宝、微信、银联）
     * Time：2015/12/11 14:01:28
     * @author liuxin
     * @param 参数类型
     * @return 返回值类型
     * json 支付方式相关参数
    */
    public function getPayment($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)||$account_id!=$params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        $payments = array(
            '0' => 'ectools_payment_plugin_wxpayjsapi',
            '1' => 'ectools_payment_plugin_malipay'
        );
        foreach ($payments as $v) {
            $data[$v] = unserialize(app::get('ectools')->getConf($v));
        }
        $service->send_user_succ('success', $data);
    }
    /**
     * ps ：获取代理商ID统一方法
     * Time：2015/12/21 09:06:15
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    public function getAagentId($accountId){
        $agent_model = app::get('sysagent')->model('account');
        $agent_id = $agent_model->getRow('agent_id',array('account_id'=>$accountId));
        return $agent_id['agent_id'];
    }

    /**
    * ps ：上传图片
    * Time：2016/01/05 17:54:37
    * @author jiang
    */
     public function image_upload($params ,&$service){
        //暂时只有注册界面需要上传图片 注册没有accesstoken
        // $account_id = $this->check_accesstoken($params['accesstoken']);
        // if (empty($account_id)) {
        //     $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        // }

        //处理图片
        $objLibImage = kernel::single('image_data_image');

       try {
           $imageData = $objLibImage->store($_FILES['picture'],'agent', $_FILES['picture']['type'],true);
           // dump($imageData);exit;
       }catch( Exception $e) {
           $service->send_user_error('error', [
                'message'=>$e->getMessage(),
                'status'=>'false'
            ]);
       }
       if(!$imageData['url'])
       {
            $service->send_user_error('error', [
                'message'=>'图片上传失败',
                'status'=>'false'
            ]);
       }

       $objLibImage->rebuild($imageData['ident'], 'admin');

       $data_image['url'] = $imageData['url'];
       $data_image['message'] = '图片上传成功';
       $data_image['status'] = 'true';

       $service->send_user_succ('success', $data_image);
    }
    /**
    * ps ：生成支付单
    * Time：2016/02/16 08:18:14
    * @author jiang
    */
    public function save_payment($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)||$account_id!=$params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }

        if(!($params['point']>0)){
            $service->send_user_error('error', [
                'message'=>'数量必须大于0',
                'status'=>'false'
            ]);
        }
        $params['product_title']='欢乐兑通用积分';
        $params['product_demo']="欢乐兑通用积分{$params['point']}积分";
        //account_id_to默认为上级代理商
        $account_model = app::get('sysagent')->model('account');
        $agent_id = $account_model->getRow('agent_id',array('account_id'=>$params['account_id']))['agent_id'];
        $agent_model = app::get('sysagent')->model('agents');
        $agentDate = $agent_model->getRow('*',array('agent_id'=>$agent_id,'account_type'=>'agent'));

;        //查积分折扣率
        $params['discount'] = $agentDate['discount'];
        //金额=折扣率*数量
        $params['money'] = round($agentDate['discount']*$params['point'],2);

        //判断父代理商是否有
        if(!$params['account_id_to']){
            $parent_id = $agentDate['parent_id'];
            if($parent_id==0){
                $params['account_id_to']=0;
            }else{
                $params['account_id_to'] = $account_model->getRow('account_id',array('agent_id'=>$parent_id))['account_id'];
            }
        }
        switch ($params['paykind']) {
            case 'alipay':
                $alipay = kernel::single('ectools_payment_plugin_appalipay');
                break;
            case 'wxpay':
                $alipay = kernel::single('ectools_payment_plugin_appwxpay');
                break;
            case 'unionpay':
                $alipay = kernel::single('ectools_payment_plugin_appunionpay');
                break;
            default:
                $service->send_user_error('error', [
                    'message'=>'请选择正确的支付方式',
                    'status'=>'false'
                ]);
                break;
        }
        try{
            $data = $alipay->dopay($params);
        }catch( Exception $e) {
           $service->send_user_error('error', [
                'message'=>$e->getMessage(),
                'status'=>'false'
            ]);
       }
       $service->send_user_succ('success', $data);
    }

    /**
    * ps ：确认支付前判断
    * Time：2016年2月25日 17:53:40
    * @author shen
    */
    public function beforedopay($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)||$account_id!=$params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }

        if(!($params['point']>0)){
            $service->send_user_error('error', [
                'message'=>'数量必须大于0',
                'status'=>'false'
            ]);
        }
        //account_id_to默认为上级代理商
        $account_model = app::get('sysagent')->model('account');
        $agent_id = $account_model->getRow('agent_id',array('account_id'=>$params['account_id']))['agent_id'];
        $agent_model = app::get('sysagent')->model('agents');
        $agentDate = $agent_model->getRow('*',array('agent_id'=>$agent_id,'account_type'=>'agent'));
        $agent_parent = $agent_model->getRow('point,discount',array('agent_id'=>$agentDate['parent_id']));
        //判断购买积分是否超出限制
        if(!empty($agent_parent)){
            if($params['point']>$agent_parent['point']){
                $service->send_user_error('error', ['message'=>'积分超出最多可购买积分限制！']);
            }
        }
        $service->send_user_succ('success', [
            'message'=>'操作成功',
            'status'=>'true'
        ]);
    }

    /**
    * ps ：确认支付
    * Time：2016/02/16 15:22:46
    * @author jiang
    */
    public function dopayment($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)||$account_id!=$params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }
        if(!$params['payment_id']){
           $service->send_user_error('error', [
                'message'=>'支付单号不能为空',
                'status'=>'false'
            ]);
        }
        $objMdlPayment = app::get('ectools')->model('payments_agent');
        $payDate = $objMdlPayment->getRow('*', array('payment_id'=>$params['payment_id']));
        // file_put_contents('jiang_txt.txt',serialize($params['payment_id'].'+11'),FILE_APPEND);
        //判断是否已经支付成功了
        if($payDate['status'] != 'succ'){
            $payment['status'] = 'paying';
            $paymentFilter['payment_id'] = $params['payment_id'];
            try{
                $result = $objMdlPayment->update($payment,$paymentFilter);
            }catch( Exception $e){
                $service->send_user_error('error', [
                    'message'=>$e->getMessage(),
                    'status'=>'false'
                ]);
            }
        }
        $service->send_user_succ('success', [
            'message'=>'支付单更新成功',
            'status'=>'true'
        ]);
    }
    /**
    * ps ：支付宝回调方法
    * Time：2016/02/19 18:10:34
    * @author jiang
    */
    public function callback($params, &$service){
        $server = kernel::single('ectools_payment_plugin_appalipay_server');
        try{
            $data=$server->callback($params);
            $service->send_user_error('error', [
                'message'=>'',
                'status'=>$data
            ]);
        }catch( Exception $e){
            $service->send_user_error('error', [
                'message'=>$e->getMessage(),
                'status'=>'false'
            ]);
        }
    }

    /**
    * ps ：判断是代理商还是会员 并优先显示真实姓名
    * Time：2016/02/26 08:44:27
    * @author jiang
    */
    function _getName($data){
        $user_acc=app::get('sysuser')->model('account');
        $user_model=app::get('sysuser')->model('user');
        $agent_model=app::get('sysagent')->model('agents');
        $name='';
        if($data['direction']=='平台方'){
            $name = '平台方';
        }else{
            if($data['role'] == '会员'){
                $userAcc=$user_acc->getRow('*',array('user_id' => $data['sub_id']));
                $userDate=$user_model->getRow('*',array('user_id' => $data['sub_id']));
                $name=$userDate['username']?$userDate['username']:$userAcc['login_account'];
            }else{
                $agentDate=$agent_model->getRow('*',array('agent_id' => $data['sub_id']));
                $name=$agentDate['name']?$agentDate['name']:$agentDate['username'];
            }
        }
        return $name;
    }
}
