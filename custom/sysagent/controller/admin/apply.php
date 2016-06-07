<?php
/**
 * @brief 商城账号
 */
class sysagent_ctl_admin_apply extends desktop_controller {


    function __construct($app){
        parent::__construct($app);
        $this->sysAgentModel = app::get('sysagent')->model('agents');
        $this->db = db::connection();
    }
    /**
     * @brief  商家账号列表
     *
     * @return
     */
    public function index()
    {
        return $this->finder('sysagent_mdl_audit_cancel',array(
            'title' => app::get('sysagent')->_('停用待审核列表'),
            'use_buildin_delete' => false,
            'use_view_tab'=>true,
            'base_filter' => array('isStop'=>'1'),
        ));
    }

    /**
     * ps 代理商审核停用
     * Time：2015/11/17 18:42:03
     * @author 张艳
     * @param 参数类型
     * @return 返回值类型
    */
   function gocancel($audit_id,$agent_id){
        //获取申请时间
        $apply_time=app::get('sysagent')->model('audit_cancel')->getRow('add_time,reason',array('audit_id'=>$audit_id));
        $pagedata['time']=$apply_time['add_time'];
        $pagedata['reason']=$apply_time['reason'];
        $pagedata['agent_id']=$agent_id ;
        $sysinfo = kernel::single('sysagent_passport')->memInfo($agent_id);
        $sysinfo['apply_status'] = 'lock';
        $sysinfo['status'] = 3;
        $pagedata['agent'] = $sysinfo;
        $data['agent_id'][0] = $agent_id;
        $data['type'] = 'stop';
        //停用时需要对其子代理商进行处理，by liuxin 2015-11-24
        $tempdata = sysagent_ctl_admin_agent::removeAgent($data);
        $pagedata['agentInfo'] = $tempdata['agentInfo'];
        $pagedata['type'] = $tempdata['type'];
        $pagedata['audit_id'] = $audit_id;
        return view::make('sysagent/admin/agent/docancel.html', $pagedata);
    }
     /**
     * ps ：代理商停用
     * Time：2015/11/19 09:36:02
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
   function docancel(){
        $audit_id=$_POST['audit_id'];
        $audit_cancel= app::get('sysagent')->model('audit_cancel');
        $enterapply = $this->app->model('enterapply');
        $sdf['agree_time'] = time();
        $sdf['enterapply_id'] = $_POST['enterapply_id'];
        $sdf['apply_status'] = $_POST['apply_status'];
        $sdf['reason'] = $_POST['reason'];
        $enter_model=app::get('sysagent')->model('enterapply');
        $agentObj['status']=$_POST['status'];
        $agentObj['agent_id']=$_POST['agent_id'];
        $agentObj['is_stop']='0';
        //发短信
        kernel::single('sysagent_passport')->sendVettedMessage($_POST['agent_id'],$sdf['apply_status']);
        //停用时需要对其子代理商进行处理，by liuxin 2015-11-24
        if(!sysagent_ctl_admin_agent::moveParent($_POST)){
            return $this->splash('error',null,"改变上级代理商时出错");
        }
        //2016-2-1 by jiang 代理商停用 子账号也跟着停用  
        $sql="update sysagent_account set is_del=1 where account_type='account' and agent_id={$agentObj['agent_id']}";
        //需要做判断，#todo 
        try
        {
            db::connection()->executeQuery($sql);
            $audit_cancel->delete(array('audit_id'=>$audit_id));
            $enter_model->save($sdf);
            app::get('sysagent')->model('agents')->save($agentObj);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $url="?app=sysagent&ctl=admin_apply&act=index";
        return $this->splash('success',$url,"操作成功");
    }
    /**
     * ps ：拒绝代理商提出停用的申请
     * Time：2015/12/10 10:58:10
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    function stopcancel(){
        $audit_id=$_POST['audit_id'];
        $audit_cancel= app::get('sysagent')->model('audit_cancel');
        try
        {
            $audit_cancel->delete(array('audit_id'=>$audit_id));
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return $this->splash('success',null,"操作成功");

    }
    public function doStopCancel($audit_id,$agent_id)
    {
        $pagedata['audit_id'] = $audit_id;
        $agentObj['agent_id'] = $agent_id;
        $agentObj['is_stop'] = '0';
        try
        {
            app::get('sysagent')->model('agents')->save($agentObj);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return view::make('sysagent/admin/agent/audit_cancel.html', $pagedata);
    }
}