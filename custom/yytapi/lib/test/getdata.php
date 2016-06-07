<?php
/*********************************************************************\
*  Copyright (c) 2007-2015, TH. All Rights Reserved.
*  Author :li
*  FName  :getdata.php
*  Time   :2015/11/26 13:32:55
*  Remark :获取测试数据，用于检测api是否可以连接通 
\*********************************************************************/
class yytapi_test_getdata extends yytapi_base {

    //模拟登陆
    public function signin($params, &$service) {
      //判断参数的合法性，校验参数,暂时先跳过
        /* return array(
          'accesstoken'=>'aaa',     //string  972b22d645ef8c2025499b6dd77596d7
          'member_id'=>11,       //int 10
          'message'=>'cc',         //string  登录成功
          'status'=>true           //true/false
          ); */       

        //检测传入的用户名密码的合法性，并得到相应的member_id
        // $member_id = 70;
        // $data['member_id'] = $member_id;
        $data['accesstoken'] = $this->set_accesstoken($params['memberid']);
        $data['message'] = app::get('b2c')->_('登录成功');

        //将登陆的member_id保存
        // $_SESSION['AGENT']['AGENT_ID'] = $member_id;
        // $_SESSION['accesstoken'] = $data['accesstoken'];
        // setcookie("accesstoken", $data['accesstoken']);
        // dump($_COOKIE);dump($_SESSION);exit;        

        // return $data['accesstoken'];//正式环境去掉该句
        $service->send_user_succ('20000001', $data);
    }

    //模拟api取数据
    public function apitest($params, &$service) {
      $member_id = $this->check_accesstoken($params['accesstoken']);
      if (empty($member_id)) {
        //如果用户名不存在，说明是非法登陆
        $service->send_user_error('40100001', '您的会话已过期!');
        return false;
      } else {
        $infoSuccess = $_SESSION;
        $infoSuccess['member_id'] = $member_id;
        $service->send_user_succ('20000001', $infoSuccess);
      }
            
    }

    //模拟登出
    public function logout($params, &$service) {
      $ret = $this->sess_destory($params['accesstoken']);
      if(!$ret) {
        $service->send_user_error('40100001', '注销失败!');
        return false;
      }
      $infoSuccess['msg'] = '注销成功';
      $service->send_user_succ('20000001', $infoSuccess);
      
    }

    /**
     * 测试数据获取
     * Time：2015/11/26 13:36:14
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    public function get_data($params, &$service)
    {
        $_POST['login_auto'] = $params['login_auto'];
        $_token = $this->set_accesstoken($params['memberid'] ,$params['autologin']);
        // $this->set_member_session($params['memberid']);
        $arr = ['小明','上山','偶遇','小花'];
        return array('token'=>$_token,'msg'=>$arr);
    }

    public function get_sess($params, &$service)
    {
        $member_id = $this->check_accesstoken($params['accesstoken']);

        return array('member_id'=>$member_id);
    }

    public function goout($params, &$service){
        return $this->sess_destory($params['accesstoken']);
    }
}
