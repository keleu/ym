<?php
/**
 * @brief 商城账号
 */
class sysuser_ctl_admin_user extends desktop_controller {


    function __construct($app){
        parent::__construct($app);
        $this->pamUserModel = app::get('sysuser')->model('account');
        $this->sysUserModel = app::get('sysuser')->model('user');
    }
    /**
     * @brief  商家账号列表
     *
     * @return
     */
    public function index()
    {
        //判断是否有权限，如果没有查看会员的权限，重定向至其他地址
        $user_check = $this->has_permission('user');
        if(!$user_check){
            //获取当前所属app下哪些有权限
            $permMenu = app::get('desktop')->model('menus');
            $_menu = $permMenu->menu($_GET);
            //获取默认第一个有权限列表的path用于跳转
            $url = reset(reset($_menu)['menu'])['menu_path'];
            if($url){
                //跳转界面
                header('Location: '.request::url(). '?'.$url);

            }

            return '会员列表';
        }

        //查看列表
        return $this->finder('sysuser_mdl_user',array(
            'title' => app::get('sysuser')->_('商城会员列表'),
            'use_buildin_filter' => true,
            'use_buildin_delete' => true,
        ));

    }

    public function license()
    {
        if( $_POST['license'] )
        {
            $this->begin();
            app::get('sysuser')->setConf('sysuser.register.setting_user_license',$_POST['license']);
            $this->end(true, app::get('sysuser')->_('当前配置修改成功！'));
        }
        $pagedata['license'] = app::get('sysuser')->getConf('sysuser.register.setting_user_license');
        return $this->page('sysuser/license.html', $pagedata);
    }

    /**
     * @brief  前台会员信息修改
     *
     * @return
     */
    public function editUserInfo($userId)
    {

        $sysInfo = kernel::single('sysuser_passport')->memInfo($userId);

        if($sysInfo['sex']==1)
        {
            $sysInfo['sex']='male';
        }
        else
        {
            $sysInfo['sex']='female';
        }
        $data = array(
            'user_id'=>$sysInfo['userId'],
            'name'=>$sysInfo['name'],
            'sex'=>$sysInfo['sex'],
            'birthday'=>$sysInfo['birthday'],
            'reg_ip'=>$sysInfo['reg_ip'],
            'regtime'=>$sysInfo['regtime'],
            'login_account'=>$sysInfo['login_account'],
            'email'=>$sysInfo['email'],
            'mobile'=>$sysInfo['mobile'],
        );

        $pagedata['data'] = $data;
        return $this->page('sysuser/admin/editinfo.html', $pagedata);
    }

    /**
     * @brief  前台会员信息保存
     *
     * @return
     */
    public function saveUserInfo()
    {
        try
        {
            $data = $_POST;
            kernel::single('sysuser_passport')->saveInfo($data);
            $this->adminlog("修改会员信息[USER_ID:{$data['user']['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("修改会员信息[USER_ID:{$data['user']['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('修改成功');

        return $this->splash('success',null,$msg);
    }

    /**
     * @brief  前台会员密码修改
     *
     * @return
     */
    public function updatePwd()
    {
        try
        {
            $data = $_POST;
            $params = array(
                'type' =>'reset',
                'new_pwd' =>$data['login_password'],
                'confirm_pwd' =>$data['psw_confirm'],
               'user_id' =>$data['user_id'],
            );
            kernel::single('sysuser_passport')->modifyPwd($params);
            $this->adminlog("修改会员密码[USER_ID:{$data['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("修改会员密码[USER_ID:{$data['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('修改成功');

        return $this->splash('success',null,$msg);
    }

    public function pagination($current,$count,$get,$limit){ //本控制器公共分页函数
        $app = app::get('sysuser');
        $ui = new base_component_ui($app);
        //unset($get['singlepage']);
        $link = '?app=sysuser&ctl=admin_user&act=ajax_html&id='.$get['id'].'&finder_act='.$get['page'].'&'.$get['page'].'=%d';
        $pagedata['pager'] = $ui->pager(array(
                'current'=>$current,
                'total'=>ceil($count/$limit),
                'link' =>$link,
            ));
        // dump($pagedata);die;
        return $pagedata['pager'];
    }

    public function ajax_html()
    {
        $finder_act = $_GET['finder_act'];
        $html = $this->$finder_act($_GET['id']);
        echo $html;
    }

    public function detail_point($row){
        $pagelimit = 10;
        $objMdlUserPoint = app::get('sysuser')->model('user_points');
        $objMdlUserPointLog = app::get('sysuser')->model('user_pointlog');
        $nPage = $_GET['detail_point'] ? $_GET['detail_point'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $point = $objMdlUserPoint->getRow('point_count,expired_point',array('user_id'=>$row));
        $point['point_count'] = $point['point_count']?$point['point_count']:'0';
        $point['expired_point'] = $point['expired_point']?$point['expired_point']:'0';
        $point['user_id'] = $row;
        $count = count($objMdlUserPointLog->getList('*',array('user_id'=>$row)));
        $pointLog = $objMdlUserPointLog->getList('*',array('user_id'=>$row),($nPage - 1)*$pagelimit,$pagelimit,'modified_time DESC');
        foreach ($pointLog as &$v) {
            if($v['operator']==''){
                $v['operator'] = '--';
            }
            if($v['behavior_type'] == 'consume'){
                $v['point'] = -$v['point'];
            }
        }

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_point';

        $pagedata = $point;
        $pagedata['point_log'] = $pointLog;
        $pagedata['pager'] = $this->pagination($nPage,$count,$_GET,$pagelimit);
        return view::make('sysuser/admin/user/page_point_list.html', $pagedata)->render();
    }

    /**
     * ps ：会员基本配置
     * Time：2016年2月19日 15:23:20
     * @author shen
     * @param 参数类型
     * @return 返回值类型
    */
    public function base_setting()
    {
        $pagedata['user_change'] = app::get('sysuser')->getConf('sysuser_setting.user_change')  == 0 ?app::get('sysuser')->getConf('sysuser_setting.user_change'):'1';
        return $this->page('sysuser/disasksetting.html',$pagedata);
    }

     /**
     * ps ：保存会员基本配置
     * Time：2016年2月19日 15:27:50
     * @author shen
     * @param 参数类型
     * @return 返回值类型
    */
    //移动端配置
    public function saveSet()
    {
        // dump($_POST);die;
        $this->begin();
            app::get('sysuser')->setConf('sysuser_setting.user_change',$_POST['user_change']);
        $this->adminlog("编辑移动端配置", 1);
        $this->end(true,app::get('sysuser')->_('保存成功'));
    }
}
