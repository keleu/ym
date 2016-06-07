<?php

class yytapi_base {

    function __construct(){
        //session的有效时间
        if($_GET['autologin'] == 'on'){
            $this->sess_time = 7200;//两个小时
        }else{
            $this->sess_time = 3*24*3600;//3天
        }
        
        $this->sess_time > 0 && ini_set('session.gc_maxlifetime',$this->sess_time);
    }

    protected function set_accesstoken($agent_id ,$auto_login = '') {
        if (empty($agent_id)) {
            return false;
        }

        //产生新的session
        $session_id = kernel::single("base_session")->gen_session_id();
        session_id($session_id);
        session_start(); 
        //设置cookie
        $cookie_path = kernel::base_url();
        $cookie_path = $cookie_path ? $cookie_path : "/";
        // $cookie_expires='';
        $cookie_expires = sprintf("expires=%s;",  gmdate('D, d M Y H:i:s T', time()+$this->sess_time));
        header(sprintf('Set-Cookie: %s=%s; path=%s; %s; httpOnly;', 's', $session_id, $cookie_path, $cookie_expires), true);
               
        $_SESSION['agent_id'] = $agent_id;
        $_SESSION['session_time'] = time() + $this->sess_time;
        $_SESSION['sess_time'] = $this->sess_time;
        return $session_id;
    }

	
	/**
     * @time 2015-07-14
     * @author Mark 
     * @param type $accesstoken
     * @param type $member_id member_id 2015/7/28 20:40
     * @return boolean
     */
    public function check_accesstoken($accesstoken) {
        if (empty($accesstoken)) {
            return false;
        }

        //根据accesstoken还原session
        session_id($accesstoken);
        session_start();

        //判断session是否过期
        // dump($_SESSION);exit;
        if($_SESSION['session_time'] < time()){
            $this->sess_destory($accesstoken);
            return false;
        }

        if($_SESSION['agent_id']){
            $_SESSION['session_time'] = time() + $this->sess_time;
            return $_SESSION['agent_id'];
        }
        return false;
    }

    /**
     * 退出的时候使用
     * Time：2015/12/02 13:28:44
     * @author li
    */
    protected function sess_destory($accesstoken){
      if (empty($accesstoken)) {
          return false;
      }
      //正确的注销session方法：
      //1开启session
      session_id($accesstoken);
      session_start();        
      //2、清空session信息
      $_SESSION = array();        
      //3、清楚客户端sessionid
      if(isset($_COOKIE[session_name()])) {
        setCookie(session_name(),'',time()-3600,'/');
      }
      //4、彻底销毁session
      session_destroy();
      return true;
    }
}
