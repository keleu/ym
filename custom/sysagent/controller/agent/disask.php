<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysagent_ctl_agent_disask extends desktop_controller{

    var $workground = 'sysagent_ctl_admin_agent';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    /**
     * ps ：代理商注册协议配置
     * Time：2015/11/27 14:21:36
     * @author liuxin
    */
    public function license()
    {
        if( $_POST['license'] )
        {
            $this->begin();
            app::get('sysagent')->setConf('sysagent.register.setting_agent_license',$_POST['license']);
            $this->end(true, app::get('sysagent')->_('当前配置修改成功！'));
        }
        $pagedata['license'] = app::get('sysagent')->getConf('sysagent.register.setting_agent_license');
        return $this->page('sysagent/license.html', $pagedata);
    }
    /**
     * ps ：代理商基本配置
     * Time：2015/11/19 14:59:53
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    public function base_setting()
    {
        $pagedata['agent_ratio']  = app::get('sysagent')->getConf('sysagent_setting.agent_ratio')?app::get('sysagent')->getConf('sysagent_setting.agent_ratio'):'0.1';
        $pagedata['agent_auditing']  = app::get('sysagent')->getConf('sysagent_setting.agent_auditing') == 0 ? app::get('sysagent')->getConf('sysagent_setting.agent_auditing'):'1';
        $pagedata['agent_arrival'] = app::get('sysagent')->getConf('sysagent_setting.agent_arrival')  == 0 ?app::get('sysagent')->getConf('sysagent_setting.agent_arrival'):'1';
        $pagedata['agent_verify'] = app::get('sysagent')->getConf('sysagent_setting.agent_verify')?app::get('sysagent')->getConf('sysagent_setting.agent_verify'):'平台方';
        return $this->page('sysagent/disasksetting.html',$pagedata);
    }

    /**
     * ps ：保存代理商基本配置
     * Time：2015/11/19 15:46:57
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    //移动端配置
    public function saveSet()
    {
        // dump($_POST);die;
        $this->begin();
            app::get('sysagent')->setConf('sysagent_setting.agent_ratio',$_POST['agent_ratio']);
            app::get('sysagent')->setConf('sysagent_setting.agent_auditing',$_POST['agent_auditing']);
            app::get('sysagent')->setConf('sysagent_setting.agent_arrival',$_POST['agent_arrival']);
            app::get('sysagent')->setConf('sysagent_setting.agent_verify',$_POST['agent_verify']);
        $this->adminlog("编辑移动端配置", 1);
        $this->end(true,app::get('sysagent')->_('保存成功'));
    }

    //移动端支付方式配置
    public function saveSetting()
    {
        // dump($_POST);die;
        $this->begin();
            app::get('sysagent')->setConf('sysagent_setting.agent_pointpay',$_POST['agent_pointpay']);
            app::get('sysagent')->setConf('sysagent_setting.agent_alipay',$_POST['agent_alipay']);
            app::get('sysagent')->setConf('sysagent_setting.agent_unionpay',$_POST['agent_unionpay']);
        $this->adminlog("编辑移动端配置", 1);
        $this->end(true,app::get('sysagent')->_('保存成功'));
    }

    /**
    * ps ：显示移动端角色列表
    * Time：2015/12/10 14:13:15
    * @author jiang
    */
    public function authority()
    {
        //判断表里是否有数据  如果没有则插入
        $rolestl = $this->app->model('roles');
        $sdf_roles = $rolestl->getlist('*');
        if(count($sdf_roles)==0){
            $arr=array(
                'role_name'=>'代理商',
                'workground'=>array(
                        0=>'bill',
                        1=>'price',
                        2=>'protocol',
                        3=>'buy',
                        4=>'agent_succ',
                        5=>'agent_wait',
                        6=>'accounts_succ',
                        7=>'agent_add',
                        8=>'accounts_add',
                        9=>'vip_succ',
                        10=>'vip_add',
                        11=>'vip_show',
                    )
            );
            $rolestl->save($arr);
            $arr=array(
                'role_name'=>'子帐号',
                'workground'=>array(
                        9=>'vip_succ',
                        10=>'vip_add',
                        11=>'vip_show',
                    )
            );
            $rolestl->save($arr);
        }
        return $this->finder('sysagent_mdl_roles',array(
            'use_buildin_delete' => false,
            'title'=>app::get('sysagent')->_('移动端权限配置'),
            ));
    }
    /**
    * ps ：修改代理商权限
    * Time：2015/12/10 15:46:23
    * @author jiang
    */
                
    public function authEdit($param_id)
    {
        $this->begin();
        // dump($_POST);exit;
        if($_POST){
            if($_POST['role_name']==''){
                 $this->end(false,app::get('desktop')->_('工作组名称不能为空'));
            }
            if(!$_POST['workground']){
                $this->end(false,app::get('desktop')->_('请至少选择一个权限'));
            }
            $opctl = $this->app->model('roles');
            $result = $opctl->check_gname($_POST['role_name']);
            if($result && ($result!=$_POST['role_id'])) {$this->end(false,app::get('desktop')->_('该工作组名称已存在'));}
            if($opctl->save($_POST)){
                 $this->end(true,app::get('desktop')->_('保存成功'));
            }else{
               $this->end(false,app::get('desktop')->_('保存失败'));
            }

        }
        else{
            $huanhuoRate = config::get('authconf');
            $rolestl = $this->app->model('roles');
            $sdf_roles = $rolestl->dump($param_id);
            $pagedata['roles']=$sdf_roles;
            $menu_workground = unserialize($sdf_roles['workground']);
            if(workground){
                foreach ($huanhuoRate as & $v) {
                    foreach ($v as $k=> & $row) {
                        if(in_array($row['workground'],(array)$menu_workground)){
                            $row['checked'] = 1;
                        }
                    }
                }
            }
            foreach($huanhuoRate as $item){//权限列表生成
                $html = $this->procHTML($item);
                $pagedata['menus3'][]= $html['html'];
                $checkarr[] = $html['checkall'];
            }
        }
        return $this->page('sysagent/users/edit_roles.html', $pagedata);
    }

    /**
    * ps ：移动端的支付方式设置
    * Time：2016年2月25日 10:13:57
    * @author shen
    */
    public function pay_setting(){
        $pagedata['agent_pointpay']  = app::get('sysagent')->getConf('sysagent_setting.agent_pointpay')?app::get('sysagent')->getConf('sysagent_setting.agent_pointpay'):'0';
        $pagedata['agent_alipay'] = app::get('sysagent')->getConf('sysagent_setting.agent_alipay') ?app::get('sysagent')->getConf('sysagent_setting.agent_alipay'):'0';
        $pagedata['agent_unionpay'] = app::get('sysagent')->getConf('sysagent_setting.agent_unionpay')?app::get('sysagent')->getConf('sysagent_setting.agent_unionpay'):'0';
        return $this->page('sysagent/admin/agent/pay_setting.html', $pagedata);
    }

    function procHTML($tree){
        $html = '';
        $checkall = 'false';
        foreach($tree as $k=>$t){
            if($t['mgrpname']){
                $html .= "<li style='text-align:left;font-weight:bold;font-style:italic;'>".$t['mgrpname'];
            }
            if($t['parent'] == ''){
                if($t['checked']){
                $html .= "<li style='padding-left:25px;text-align:left;'><input  class='leaf'  type='checkbox' checked='checked' name='workground[]' value=".$t['app_id'].">".$t['menu_title'];
                $checkall = 'true';
                }else{
                $html .= "<li style='padding-left:25px;text-align:left;'><input   class='leaf' type='checkbox' name='workground[]' value=".$t['app_id'].">{$t['menu_title']}</li>";
                $checkall = 'false';
                }
            }else{
                if($t['checked']){
                $html .= "<li style='padding-left:25px;text-align:left;'><input  class='parent leaf'  type='checkbox' checked='checked' name='workground[]' value=".$t['app_id'].">".$t['menu_title'];
                $checkall = 'true';
                }else{
                $html .= "<li style='padding-left:25px;text-align:left;'><input  class='parent leaf'  type='checkbox' name='workground[]' value=".$t['app_id'].">".$t['menu_title'];
                $checkall = 'false';
                }
                $str = $this->procHTML($t['parent']);
                $html .= $str['html'];
                $html = $html."</li>";
            }
        }
        //return $html ? "<ul>".$html."</ul>" : $html;
        return array(
            "html"=>"<ul>".$html."</ul>",
            "checkall"=>$checkall
        );
    }
}
