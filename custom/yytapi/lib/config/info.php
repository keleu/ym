<?php
/*********************************************************************\
*  Copyright (c) 2007-2015, TH. All Rights Reserved.
*  Author :li
*  FName  :getdata.php
*  Time   :2015/11/26 13:32:55
*  Remark :获取代理商
\*********************************************************************/
class yytapi_config_info extends yytapi_base {
   /**
     * ps ：获取地区
     * Time：2015/11/27 13:02:35
     * @author zhangyan
     * @param 参数类型
     * area_id  int N   地区Id
     * @return 返回值类型
    */
    public function get_area($params, &$service){
        if($params['area_id']){
            $data = area::getAreaIdPath($params['area_id']);

            if(!$data){
                $result=array();
                $service->send_user_succ('success',$result);
            }

            foreach ($data as $k => $v) {
                $region[$k]['id'] = $v;
                $region[$k]['value'] = area::getAreaNameById($v);

                if(!$result = area::getAreaIdPath($v)){
                    $region[$k]['is_last'] = '1';
                }else{
                    $region[$k]['is_last'] = '0';
                }
            }
        }
        else{
            $data = area::getMap();
            foreach ($data as $k => $v) {
                $region[$k]['id'] = $v['id'];
                $region[$k]['value'] = $v['value'];
            }
        }
        $service->send_user_succ('success', $region);
        // return array(
        //     'province'=>'sgds',   // string  省
        //     'city'=>'sgds',   //string  市
        //     'area'=>'sgds',   //string  地区
        // );
    }
    /**
     * ps ：获取代理商入职协议
     * Time：2015/11/27 13:02:35
     * @author zhangyan
     * @param 参数类型
     * area_id  int N   地区Id
     * @return 返回值类型
    */
    public function get_agent_agreement($params, &$service){
        $data['message'] = app::get('sysagent')->getConf('sysagent.register.setting_agent_license');
        $service->send_user_succ('success', $data);
        // return array(
        //     'message'=>'sgds'   // string  入注协议内容
        // );
    }
    /**
     * ps ：修改密码
     * Time：2015/11/27 14:15:51
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string      Y   代理商帐号登录返回的 accesstoken
     * password_old string  Y   代理商帐号的密码（加密过后的密码）
     * password_new string  Y   新密码（加密过后的密码）
     * account_id Int Y   登录代理商的id
     * @return 返回值类型
    */
    public function change_password($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $account_id) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                    'message'=>'您的会话已过期',
                    'status'=>'false',
            ]);
        }

        //有效性验证
        $len_pass = (int)strlen($params['password_new']);
        // echo ($len_pass>=6);exit;
        if(!$params['password_new'] || $len_pass<6 || $len_pass>20){
            $service->send_user_error('40100320', [
                'message'=>'新密码不能为空，且长度为6~20',
                'status'=>'false',
            ]);
        }

        $model_account = app::get('sysagent')->model('account');
        $old = $model_account->getRow('login_password',array(
            'account_id'=>$params['account_id']
        ));

        if(!pam_encrypt::check($params['password_new'],$old['login_password'])){
            $result = kernel::single('sysagent_passport')->checkPwd($params['password_new'],$params['password_new']);
            if($result!=true){
                $service->send_user_error('error',[
                    'message'=>$result,
                    'status'=>'false',
                ]);
            }

            $new = pam_encrypt::make($params['password_new']);
            $data = array(
                'account_id' => $params['account_id'],
                'login_password' => $new
            );
            try{
                $model_account->save($data);
            }
            catch(Excption $e){
                $msg = $e->getMessage();
                $service->send_user_error('error',[
                    'message'=>"修改失败".$msg,
                    'status'=>'false',
                ]);
            }
        }
        else{
            $service->send_user_error('error', [
                    'message'=>'新密码和原密码不能相同，请重新输入',
                    'status'=>'false',
                ]);
        }
        $service->send_user_succ('success', array(
            'status'=>'true',
            'message'=>'修改成功', //String   成功
        ));
    }
    /**
     * ps ：修改个人信息
     * Time：2015/11/27 14:15:51
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string      Y   代理商帐号登录返回的 accesstoken
     * mobile   string  N   手机号
     * name string  N   真实姓名
     * sex   Int N   性别
     * account_id Int Y   登录代理商的id
     * @return 返回值类型
    */
    public function edit_detail($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $service->send_user_error('40100001', [
                'message'=>'您的会话已过期!',
                'status'=>'false',
            ]);
        }


        if(!$params['mobile'] && !$params['name'] && $params['sex']==''){
            $service->send_user_error('7001', [
                'message'=>'相关参数不能为空',
                'status'=>'false',
            ]);
        }

        $model_account = app::get('sysagent')->model('account');

        if($params['mobile'] && !preg_match("/^1[34578]\d{9}$/",$params['mobile'])){
            $msg = "请输入正确的手机号";
            $service->send_user_error('error', [
                'message'=>$msg,
                'status'=>'false',
            ]);
        }

        if($params['mobile']!=''){
            $return = $model_account->getRow('*',array('mobile'=>$params['mobile']));
            if($return&&$return['account_id']!=$params['account_id']){
            $msg = "该手机号已被注册";
            $service->send_user_error('error', [
                'message'=>$msg,
                'status'=>'false',
            ]);
            }
            //验证密码是不是合法
            $vcodeData=userVcode::verify($params['vcode'],$params['mobile'],'reset');
            if(!$vcodeData['vcode'])
            {
                $service->send_user_error('error',array(
                        'status'=>'false',
                        'message'=>'验证码输入错误',
                ));
            }
        }

        //2016-1-26 by jiang 为空表示为不要修改
        if($params['mobile']) $data['mobile']=$params['mobile'];
        if($params['name']) $data['name']=$params['name'];
        if($params['sex']!='') $data['sex']=$params['sex'];

        $accountType = $model_account->getRow('agent_id,account_type',array('account_id'=>$params['account_id']));
        $db = app::get('sysagent')->database();
        $db->beginTransaction();
        try{
            if($accountType['account_type']=='agent'){
                $data['agent_id'] = $accountType['agent_id'];
                app::get('sysagent')->model('agents')->update($data,array('agent_id'=>$data['agent_id']));
            }
            $data['account_id'] = $params['account_id'];
            $model_account->save($data);
            $db->commit();
        }
        catch(Excption $e){
            $db->rollback();
            $msg = $e->getMessage();
            $service->send_user_error('error', [
                'message'=>'修改失败'.$msg,
                'status'=>'false',
            ]);
        }
        $service->send_user_succ('success', array(
            'status'=>'true',
            'message'=>'修改成功', //String   成功
        ));

    }
    /**
    * ps ：获取显示界面的权限
    * Time：2015/12/11 14:38:55
    * @author jiang
    */
    public function authority($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            $service->send_user_error('40100001', ['message'=>'您的会话已过期!']);
        }

        $agent_obj=app::get('sysagent')->model('account')->getRow('account_type',array('account_id'=>$params['account_id']));
        if($agent_obj['account_type']=='agent'){
            $roles_obj=app::get('sysagent')->model('roles')->getRow('workground',array('role_name'=>'代理商'));
            $menu_workground = unserialize($roles_obj['workground']);
        }else{
            $roles_obj=app::get('sysagent')->model('roles')->getRow('workground',array('role_name'=>'子帐号'));
            $menu_workground = unserialize($roles_obj['workground']);
        }
        $arr=array();
        foreach ($menu_workground as $v) {
            $arr[$v]=$v;
        }
        $huanhuoRate = config::get('authconf');
        $ret=array();
        foreach ($huanhuoRate as & $h) {
            foreach ($h as & $value) {
                $temp_title=$value['mgrptitle']?$value['mgrptitle']:$temp_title;
                $ret[$temp_title][$value['workground']]=$arr[$value['workground']]?'true':'false';
            }
        }
        $service->send_user_succ('success', $ret);
    }

    /**
    * ps ：判断移动端支付方式是否开启
    * Time：2016年2月25日 10:49:22
    * @author shen
    */
    public function pay_setting($params, &$service){
        $data['agent_pointpay'] = app::get('sysagent')->getConf('sysagent_setting.agent_pointpay')?app::get('sysagent')->getConf('sysagent_setting.agent_pointpay'):'0';
        $data['agent_alipay'] = app::get('sysagent')->getConf('sysagent_setting.agent_alipay')?app::get('sysagent')->getConf('sysagent_setting.agent_alipay'):'0';
        $data['agent_unionpay'] = app::get('sysagent')->getConf('sysagent_setting.agent_unionpay')?app::get('sysagent')->getConf('sysagent_setting.agent_unionpay'):'0';
        $service->send_user_succ('success', $data);
    }
}
