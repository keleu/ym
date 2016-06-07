<?php
/**
 * @brief 商城账号
 */
class sysagent_ctl_admin_agent extends desktop_controller {


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
        return $this->finder('sysagent_mdl_agents',array(
            'title' => app::get('sysagent')->_('代理商列表'),
            'use_buildin_delete' => false,
            'use_view_tab'=>true,
            'actions' => array(
                array(
                    'label'=>app::get('sysagent')->_('添加个人代理商'),
                    'href'=>'?app=sysagent&ctl=admin_agent&act=addAgent&kind=1',
                    'target'=>'dialog::{title:\''.app::get('sysAgent')->_('添加代理商').'\',  width:500,height:450}',
                ),
                array(
                    'label'=>app::get('sysagent')->_('添加企业代理商'),
                    'href'=>'?app=sysagent&ctl=admin_agent&act=addAgent&kind=2',
                    'target'=>'dialog::{title:\''.app::get('sysAgent')->_('添加代理商').'\',  width:500,height:450}',
                )
                //去掉代理商删除按钮，2015-12-09，by liuxin
                // array(
                //     'label'=>app::get('sysagent')->_('删除代理商'),
                //     'submit'=>"?app=sysagent&ctl=admin_agent&act=removeAgent&p[0][type]=delete",
                //     'target'=>'dialog::{title:\''.app::get('sysAgent')->_('删除代理商').'\',  width:500,height:450}',
                // )
            ),
        ));
    }

    /**
     * ps ：后台添加代理商
     * Time：2015/11/16 16:39:16
     * @author liuxin
     * @param 参数类型
     * @return 返回值类型
    */
    public function addAgent()
    {
        $row = app::get('sysagent')->model('agents')->getList('*',array('status'=>'1'));
        foreach ($row as $key => $value) {
            $options[$value['agent_id']] = $value['username'];
        }
        $pagedata['agent'] = $options;
        $this->contentHeaderTitle = '添加代理商';

        $areaMap = area::getMap();
        $pagedata['areaMap'][''] = '-请选择-';
        foreach ($areaMap as $k=>&$v) {
            if($v['value']!='')
                $pagedata['areaMap'][$v['id']] = $v['value'];
        }
        if($_GET['kind']==1) return view::make('sysagent/admin/agentPer.html', $pagedata);
        else return view::make('sysagent/admin/agentCom.html', $pagedata);
    }

    public function _views()
    {
        $mdl_aftersales = app::get('sysagent')->model('agents');
        $sub_menu = array(
            0=>array('label'=>app::get('sysagent')->_('全部'),'optional'=>false,'filter'=>''),
            1=>array('label'=>app::get('sysagent')->_('待审核'),'optional'=>false,'filter'=>array('status'=>'0')),
            2=>array('label'=>app::get('sysagent')->_('已审核通过'),'optional'=>false,'filter'=>array('status'=>'1')),
            3=>array('label'=>app::get('sysagent')->_('已审核拒绝'),'optional'=>false,'filter'=>array('status'=>'2')),
            4=>array('label'=>app::get('sysagent')->_('已审核停用'),'optional'=>false,'filter'=>array('status'=>'3')),
        );
        return $sub_menu;
    }

    /**
     * ps ：验证当前地区是否已有一级代理商
     * Time：2015/11/24 17:01:28
     * @author liuxin
     * @param $_POST
     * @return json
    */
    function checkArea(){
        $_POST['second_id'] = $_POST['second_id'] == "-请选择-"?'':$_POST['second_id'];
        $_POST['third_id'] = $_POST['third_id'] == "-请选择-"?'':$_POST['third_id'];
        if(is_numeric($_POST['first_id'])){
            $_POST['first_id'] = area::getAreaNameById($_POST['first_id']);
        }
        if(is_numeric($_POST['second_id'])){
            $_POST['second_id'] = area::getAreaNameById($_POST['second_id']);
        }
        if(is_numeric($_POST['third_id'])){
            $_POST['third_id'] = area::getAreaNameById($_POST['third_id']);
        }
        // dump($_POST);die;
        $agent = app::get('sysagent')->model('agents');
        $data = $agent->getList('agent_id',array('area_first'=>$_POST['first_id'],'area_second'=>$_POST['second_id'],'area_third'=>$_POST['third_id'],'agent_level'=>'1','status'=>'1'));
        $count = count($data);
        $area = $_POST['first_id'].$_POST['second_id'].$_POST['third_id'];
        if($count == 0){
            echo json_encode(array('success'=>true));exit;
        }
        else{
            $msg = "{$area}已有{$count}个一级代理商，是否继续？";
            echo json_encode(array('success'=>false,'msg'=>$msg));exit;
        }
    }

    /**
     * ps ：根据Id验证当前地区是否已有一级代理商
     * Time：2015/11/25 09:02:58
     * @author liuxin
     * @param _POST
     * @return json
    */

    function checkAreaById(){
        $agent = app::get('sysagent')->model('agents');
        $data = $agent->getRow('*',array('agent_id'=>$_POST['agent_id']));
        if($data['agent_level'] != 1){
            echo json_encode(array('success'=>true));exit;
        }
        $row = $agent->getList('agent_id',array('area_first'=>$data['area_first'],'area_second'=>$data['area_second'],'area_third'=>$data['area_third'],'agent_level'=>'1','status'=>'1'));
        $count = count($row);
        $area = $data['area_first'].$data['area_second'].$data['area_third'];
        if($count == 0){
            echo json_encode(array('success'=>true));exit;
        }
        else{
            $msg = "{$area}已有{$count}个一级代理商，是否继续？";
            echo json_encode(array('success'=>false,'msg'=>$msg));exit;
        }
    }
    /**
     * ps ：后台保存代理商
     * Time：2015/11/16 16:40:44
     * @author liuxin
     * @param $_POST
     * @return 返回值类型
    */
    public function saveAgent()
    {
        // dump($_POST);exit;
        $url = '?app=sysagent&ctl=admin_agent&act=index';
        // dump($_POST);die;
        $msg = kernel::single('sysagent_passport')->agentCheck($_POST['agent_username'],'login_account');
        if($msg != 'true'){
            return $this->splash('error',null,$msg);
        }
        //2015-12-7 by jiang 判断父代理商是否存在
        $_agent_level=1;
        $_parent_id=0;
        $error='';
        $agent = app::get('sysagent')->model('agents');
        if($_POST['agent_parentId']){
            $lv = $agent -> getRow('agent_level,agent_id',array('username'=>$_POST['agent_parentId']));
            if(is_array($lv)&&count($lv)>0){
                $_agent_level = $lv['agent_level'] + 1;
                $_parent_id=$lv['agent_id'];
            }else{
                $error="请填写正确的代理商！";
            }
        }
        $mb_confirm = app::get('sysagent')->model('agents')->getRow('agent_id',array('mobile'=>$_POST['agent_tel']));
        // dump($_POST);exit;
        if(!empty($mb_confirm)){
            $error="该手机号码已经存在!";
        }
        if(is_numeric($_POST['agent_area_first'])){
            $_POST['agent_area_first'] = area::getAreaNameById($_POST['agent_area_first']);
        }
        if(is_numeric($_POST['agent_area_second'])){
            $_POST['agent_area_second'] = area::getAreaNameById($_POST['agent_area_second']);
        }
        if(is_numeric($_POST['agent_area_third'])){
            $_POST['agent_area_third'] = area::getAreaNameById($_POST['agent_area_third']);
        }
        $_POST['agent_area_second'] = $_POST['agent_area_second'] == "-请选择-"?'':$_POST['agent_area_second'];
        $_POST['agent_area_third'] = $_POST['agent_area_third'] == "-请选择-"?'':$_POST['agent_area_third'];
        $data = array(
            'username' => $_POST['agent_username'],
            'flag' => 'create',
            'name' => $_POST['agent_name'],
            'mobile' => $_POST['agent_tel'],
            'area_first' => $_POST['agent_area_first'],
            'area_second' => $_POST['agent_area_second'],
            'area_third' => $_POST['agent_area_third'],
            'addr' => $_POST['agent_address'],
            'parent_id' => $_parent_id,
            'point' => $_POST['agent_point'],
            'id_card' => $_POST['agent_idcard'],
            'regtime' => time(),
            'reg_ip' => $_SERVER["REMOTE_ADDR"],
            'email' => $_POST['agent_email'],
            'sex' => $_POST['agent_sex'],
            'agent_level' => $_agent_level,
            'kind' =>$_POST['agent_kind'],
            'picture' =>$_POST['agent_picturez'].','.$_POST['agent_picturef'],
        );
        try
        {
            if($error) throw new \logicException($error);
            $agent->save($data);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return $this->splash('success',$url,"添加成功");
    }

    /**
     * ps ：获取二级地区
     * Time：2015/11/18 10:10:50
     * @author liuxin
     * @param $_POST
     * @return json
    */
    public function getChildArea()
    {
        $areaMap = area::getMap();
        foreach ($areaMap as $k => &$v) {
            if($v['id']==$_POST['area_id']){
                $data = $v['children'];
                break;
            }
        }
        foreach ($data as $key => &$value) {
            $optiondata[$value['id']] = $value['value'];
        }
        echo json_encode(array('success'=>true,'data'=>$optiondata));exit;
    }

    /**
     * ps ：获取三级地区
     * Time：2015/11/18 10:10:50
     * @author liuxin
     * @param $_POST
     * @return json
    */
    public function getThirdArea()
    {
        $areaMap = area::getMap();
        if(!is_numeric($_POST['first_id'])){
            $area['0'] = $_POST['first_id'];
            $_POST['first_id'] = kernel::single('sysagent_passport')->getAreaNameByName($area)['0']['id'];
        }
        if(!is_numeric($_POST['second_id'])){
            $area['1'] = $_POST['second_id'];
            $_POST['second_id'] = kernel::single('sysagent_passport')->getAreaNameByName($area)['1']['id'];
        }
        foreach ($areaMap as $k => &$v) {
            if($v['id']==$_POST['first_id']){
                $data = $v['children'];
                break;
            }
        }
        foreach ($data as $k => &$v) {
            if($v['id']==$_POST['second_id']){
                $row = $v['children'];
                break;
            }
        }
        if(is_array($row)){
            foreach ($row as $key => &$value) {
                $optiondata[$value['id']] = $value['value'];
            }
            echo json_encode(array('success'=>true,'data'=>$optiondata));exit;
        }
        echo json_encode(array('success'=>false));exit;
    }
    /**
     * ps 代理商审核通过。启用
     * Time：2015/11/17 18:42:03
     * @author 张艳
     * @param 参数类型
     * @return 返回值类型
    */
    function doallow(){
        $enterapply = $this->app->model('enterapply')->getRow('*',array('agent_id'=>$_POST['agent_id']));
        $sdf['agree_time'] = time();
        $sdf['enterapply_id'] = $_POST['enterapply_id'];;
        $sdf['apply_status'] = $_POST['apply_status'];
        $sdf['reason'] = $_POST['reason'];
        $enter_model=app::get('sysagent')->model('enterapply');
        $agentObj['status']=$_POST['status'];
        $agentObj['agent_id']=$_POST['agent_id'];
        $agentObj['discount']=$_POST['discount'];
        //审核通过后需要更新关系表，by liuxin，2015-11-24
        if($enterapply['apply_status'] == 'lock'&&$_POST['apply_status'] == 'successful'){
            $status = 'restart';
        }
        else{
            $status = $_POST['apply_status'];
        }
        if($agentObj['status'] == '1'){
            $agentObj['flag']="allow";
        }
        try
        {
            // kernel::single('sysagent_passport')->sendVettedMessage($_POST['agent_id'],$status);
            $enter_model->save($sdf);
            app::get('sysagent')->model('agents')->save($agentObj);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        //短信发送
         try
        {
            kernel::single('sysagent_passport')->sendVettedMessage($_POST['agent_id'],$status);
        }
        catch(Exception $e)
        {
            //短信发送失败不做操作 2016年1月28日 12:09:17
            // $msg = $e->getMessage();
            // return $this->splash('error',null,$msg);
        }
        return $this->splash('success',null,"审核通过");
    }
    /**
     * ps ：代理商审核拒绝
     * Time：2015/12/09 13:01:43
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
        function donoallow(){
        $enterapply = $this->app->model('enterapply')->getRow('*',array('agent_id'=>$_POST['agent_id']));
        $sdf['agree_time'] = time();
        $sdf['enterapply_id'] = $_POST['enterapply_id'];;
        $sdf['apply_status'] = $_POST['apply_status'];
        $sdf['reason'] = $_POST['reason'];
        $enter_model=app::get('sysagent')->model('enterapply');
        $agentObj['status']=$_POST['status'];
        $agentObj['agent_id']=$_POST['agent_id'];
        //审核通过后需要更新关系表，by liuxin，2015-11-24
        if($enterapply['apply_status'] == 'lock'&&$_POST['apply_status'] == 'successful'){
            $status = 'restart';
        }
        else{
            $status = $_POST['apply_status'];
        }
        try
        {
            // kernel::single('sysagent_passport')->sendVettedMessage($_POST['agent_id'],$status);
            $enter_model->save($sdf);
            app::get('sysagent')->model('agents')->save($agentObj);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

         try
        {
            kernel::single('sysagent_passport')->sendVettedMessage($_POST['agent_id'],$status);
        }
        catch(Exception $e)
        {
            //短信发送失败不做操作 2016年1月28日 12:09:46
            // $msg = $e->getMessage();
            // return $this->splash('error',null,$msg);
        }
        return $this->splash('success',null,"操作成功");
    }
    /**
     * ps 代理商审核通过
     * Time：2015/11/17 18:42:03
     * @author 张艳
     * @param 参数类型
     * @return 返回值类型
    */
    function goallow($agent_id){
        $pagedata['agent_id']=$agent_id ;
        $sysinfo = kernel::single('sysagent_passport')->memInfo($agent_id);
        $sysinfo['apply_status'] = 'successful';
        $sysinfo['status'] = 1;
        //by zhangyan 2015-12-09 添加代理商的积分折扣率
        $parenObj=app::get('sysagent')->model('agents')->getRow('discount',array('agent_id'=>$sysinfo['parent_id']));
        if (!empty($parenObj) && $parenObj['discount'] != '0') {
            $pagedata['discount']=$parenObj['discount'];
        }else{
            $pagedata['discount']=app::get('sysagent')->getConf('sysagent_setting.agent_ratio')?app::get('sysagent')->getConf('sysagent_setting.agent_ratio'):'0.1';
        }
        $pagedata['agent'] = $sysinfo;
        return view::make('sysagent/admin/agent/goenter.html', $pagedata)->render();

    }
    /**
     * ps 代理商审核拒绝
     * Time：2015/11/17 18:42:03
     * @author 张艳
     * @param 参数类型
     * @return 返回值类型
    */
    function gonoallow($agent_id){
        $pagedata['agent_id']=$agent_id ;
        $sysinfo = kernel::single('sysagent_passport')->memInfo($agent_id);
        $sysinfo['apply_status'] = 'failing';
        $sysinfo['status'] = 2;
        $pagedata['agent'] = $sysinfo;
        return view::make('sysagent/admin/agent/gonoenter.html', $pagedata)->render();

    }
    /**
     * ps 代理商审核停用
     * Time：2015/11/17 18:42:03
     * @author 张艳
     * @param 参数类型
     * @return 返回值类型
    */
   /* function gocancel($agent_id){
        $pagedata['agent_id']=$agent_id ;
        $sysinfo = kernel::single('sysagent_passport')->memInfo($agent_id);
        $sysinfo['apply_status'] = 'lock';
        $sysinfo['status'] = 3;
        $pagedata['agent'] = $sysinfo;
        $data['agent_id'][0] = $agent_id;
        $data['type'] = 'stop';
        //停用时需要对其子代理商进行处理，by liuxin 2015-11-24
        $tempdata = $this->removeAgent($data);
        $pagedata['agentInfo'] = $tempdata['agentInfo'];
        $pagedata['type'] = $tempdata['type'];
        return view::make('sysagent/admin/agent/docancel.html', $pagedata)->render();

    }*/
    /**
     * ps ：停用代理商需要平台审核
     * Time：2015/12/10 09:28:44
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    function gocancel($agent_id){
        $pagedata['agent_id']=$agent_id ;
        $sysinfo = kernel::single('sysagent_passport')->memInfo($agent_id);
        $sysinfo['apply_status'] = 'lock';
        $sysinfo['status'] = 3;
        $pagedata['agent'] = $sysinfo;
        $data['agent_id'][0] = $agent_id;
        $data['type'] = 'stop';
        return view::make('sysagent/admin/agent/gocancel.html', $pagedata)->render();
    }
   
    function docancel(){
        $apply = $this->app->model('audit_cancel');
        $sdf['add_time'] = time();
        $sdf['apply_status'] = '0';
        $sdf['enterapply_id'] = $_POST['enterapply_id'];;
        $sdf['agent_id']=$_POST['agent_id'];
        $sdf['reason']=$_POST['reason'];
        $agent_obj['agent_id']=$_POST['agent_id'];
        $agent_obj['is_stop']='1';
        try
        {
            $this->app->model('agents')->save($agent_obj);
            $apply->save($sdf);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return $this->splash('success',null,"申请成功");
    }
    /**
     * ps ：删除停用代理商
     * Time：2015/11/24 15:08:51
     * @author liuxin
     * @param array() agent_id,type
     * @return array() or htmlPage
    */
    function removeAgent($data){
        $agent_id = $_POST['agent_id']?$_POST['agent_id']:$data['agent_id'];
        $agent = app::get('sysagent')->model('agents');
        $find = kernel::single('sysagent_passport');
        $del_agent = $agent->getList('agent_id,username,agent_level',array('agent_id'=>$agent_id));
        $temp = $agent->getList('agent_id,username,parent_id',array('parent_id'=>$agent_id));

        foreach ($temp as $k => &$v) {
            $son_agent[$v['parent_id']][$v['agent_id']] = $v['username'];
        }
        // dump($son_agent);die;
        $i = 0;
        foreach ($del_agent as $k => &$value) {
            if($value['agent_level']==1){
                $k = 0;
                foreach ($son_agent[$value['agent_id']] as $k1 => &$v1) {
                    $data['agentInfo'][$i]['del_agent'][$value['agent_id']] = $value['username'];
                    $data['agentInfo'][$i]['son_agent'][$k1] = $v1;
                    $i++;$k++;
                }
                if($k == 0){
                    $data['agentInfo'][$i]['del_agent'][$value['agent_id']] = $value['username'];
                    $i++;
                }
            }
            else{
                $data['agentInfo'][$i]['del_agent'][$value['agent_id']] = $value['username'];
                $i++;
            }
            // $data['agentInfo'][$k]['new_agent'] = $new_data;
        }
        // dump($data);die;
        foreach ($data['agentInfo'] as &$value) {
            foreach ($value['son_agent'] as $k => $v1) {
                if($v1 != ''&&!in_array($k, $agent_id)){
                    $value['showrow'] = '1';
                    $count++;
                    $tempdata = $find -> getSamearea($k);
                    foreach ($tempdata as $k => $v2) {
                        if(!in_array($v2['agent_id'], $agent_id)){
                            $value['new_agent'][$v2['agent_id']] = $v2['username'];
                        }
                    }
                }
            }
            if(count($value['new_agent']) == 0){
                $value['new_agent'][''] = '暂无可选代理商，请先添加！';
            }
        }
        $data['count'] = $count?$count:0;
        $data['type'] = $data['type'];
        // dump($data);die;
        if($data['type'] == 'delete'){
            return view::make('sysagent/admin/agent/changeParent.html', $data);
        }
        elseif($data['type'] == 'stop'){
            return $data;
        }
    }

    /**
     * ps ：删除代理商同时处理其子代理商
     * Time：2015/11/24 15:09:44
     * @author liuxin
     * @param array()
     * @return bool
    */
    public function changeParent($data){
        $_POST = $data?$data:$_POST;
        // dump($_POST);die;
        if($this->moveParent($_POST,$msg)){
            return $this->splash('success',null,"操作成功");
        }
        else{
            return $this->splash('error',null,$msg);
        }

    }

    function moveParent($data,&$msg){
        $this->db = db::connection();
        $agent = app::get('sysagent')->model('agents');
        $agent_account = app::get('sysagent')->model('account');
        $agent_apply = app::get('sysagent')->model('enterapply');
        $nodes = kernel::single('sysagent_agent_Nodes');
        //处理一级代理商的子代理商
        foreach ($data['sonagent_id'] as $k => &$v) {
            // $update_data['agent_id'] = $v;
            // $update_data['parent_id'] = $data['newagent_id'][$k];
            $update_data = array(
                'agent_id' => $v,
                'parent_id' => $data['newagent_id'][$k]
            );
            $nodes -> moveNode($update_data);
            try
            {
                $agent->save($update_data);
            }
            catch(Exception $e)
            {
                $msg = $e->getMessage();
                return false;
            }
        }

        //处理非一级代理商的子代理商
        foreach ($data['delagent_id'] as $k => &$v) {
            $agent_info = $agent->getRow('agent_level,parent_id',array('agent_id'=>$v));
            if($agent_info['agent_level'] == '1'){
                continue;
            }
            else{
                $son_agent = $agent->getList('agent_id',array('parent_id'=>$v));
                foreach ($son_agent as $k1 => $v1) {
                    if(in_array($v1['agent_id'], $data['delagent_id'])){
                        continue;
                    }
                    else{
                        $tempdata = array(
                            'agent_id' => $v1['agent_id'],
                            'parent_id' => $agent_info['parent_id'],
                            'agent_level' => $agent_info['agent_level']
                        );
                        $nodes -> moveNode($tempdata);
                        $relation = app::get('sysagent')->model('agent_relation')->getRow('*',array('agent_id'=>$tempdata['agent_id']));
                        if(count($relation) != 0){
                            $sql = "update sysagent_agents
                                    set agent_level = agent_level - 1
                                    where agent_id in
                                    (select agent_id 
                                    from sysagent_agent_relation
                                    where left_id>{$relation['left_id']}
                                    and left_id<{$relation['right_id']})";
                            $this->db->executeQuery($sql);
                            $str = "update sysagent_agents 
                                    set agent_level = agent_level - 1
                                    where parent_id in
                                    (select agent_id
                                    from sysagent_agent_relation
                                    where left_id>={$relation['left_id']}
                                    and left_id<={$relation['right_id']})
                                    and status != '1'";
                            $this->db->executeQuery($str);
                        }
                        try
                        {
                            $agent->save($tempdata);
                        }
                        catch(Exception $e)
                        {
                            $msg = $e->getMessage();
                            return false;
                        }
                    }
                }
            }
        }

        //进行删除操作
        $nodes -> removeNodes($data['delagent_id']);
        if($data['operation_type'] == 'delete'){
            try
            {
                $agent->dodelete($data['delagent_id']);
            }
            catch(Exception $e)
            {
                $msg = $e->getMessage();
                return false;
            }
            return true;
        }
        return true;
    }
    /**
     * ps ：获取选中代理商所在地区
     * Time：2015/11/24 15:10:39
     * @author liuxin
     * @param $_POST
     * @return json
    */
    public function getAgentArea(){
        $areaMap = area::getMap();
        if($_POST['agent_name']){
            $agent = app::get('sysagent')->model('agents');
            $area = $agent->getRow('*',array('username'=>$_POST['agent_name'],'status'=>1));
            $data['agent_id'] = $area['agent_id'];
            $data['0']['area_first'] = $area['area_first'];
            $data['1']['area_second'] = $area['area_second'];
            $data['2']['area_third'] = $area['area_third'];

            if(!$data['1']['area_second']){
                unset($data['1']['area_second']);
                foreach ($areaMap as $v) {
                    if($v['value'] == $data['0']['area_first']){
                        $temparea = $v['children'];
                        break;
                    }
                }
                foreach ($temparea as $v) {
                    $data['1'][$v['id']] = $v['value'];
                }
                $data['1'][''] = "-请选择-";
            }

            if(!$data['2']['area_third']){
                unset($data['2']['area_third']);
                foreach ($areaMap as $v) {
                    if($v['value'] == $data['0']['area_first']){
                        $temparea = $v['children'];
                        break;
                    }
                }
                if(is_array($temparea)){
                    foreach ($temparea as $v) {
                        if($v['value'] == $data['1']['area_second']){
                            $tempdata = $v['children'];
                            break;
                        }
                    }
                    if(is_array($tempdata)){
                        foreach ($tempdata as $v) {
                            $data['2'][$v['id']] = $v['value'];
                        }
                        $data['2'][''] = "-请选择-";
                    }
                }
            }
        }
        else{
            foreach ($areaMap as $k=>&$v) {
                if($v['value']!='')
                    $data[$v['id']] = $v['value'];
            }
        }
        echo json_encode(array('success'=>true,'data'=>$data));exit;
    }

    function textApi(){
        $text['username']=2;
        $text['password']=1;
        $trades = app::get('agents')->rpcCall('agents.signin',$text);

    }

    /**
     * ps ：修改代理商地区
     * Time：2015/11/26 14:32:19
     * @author liuxin
     * @param $_POST
     * @return json
    */
    function editArea(){
        // dump($_POST);die;
        $agent = app::get('sysagent')->model('agents');
        $area = area::getMap();
        $agentData = $agent -> getRow('*',array('agent_id'=>$_POST['agent_id']));
        if($_POST['parent_id'] == 0){
            foreach ($area as $k => $v) {
                if($v['value'] == "") continue;
                $data['parent']['area_first'][$v['id']] = $v['value'];
                if($v['value'] == $agentData['area_first']){
                    $secondArea = $v['children'];
                    $data['agent']['area_first'][0] = $v['id'];
                }
            }
            if(count($secondArea) != 0){
                $data['agent']['area_second'][0] = "";
                foreach ($secondArea as $k => $v) {
                    $data['parent']['area_second'][$v['id']] = $v['value'];
                    if($v['value'] == $agentData['area_second']){
                        $thirdArea = $v['children'];
                        $data['agent']['area_second'][0] = $v['id'];
                    }
                }
                $data['parent']['area_second'][''] = "-请选择-";
            }
            if(count($thirdArea) != 0){
                foreach ($thirdArea as $k => $v) {
                    $data['parent']['area_third'][$v['id']] = $v['value'];
                    if($v['value'] == $agentData['area_third']){
                        $data['agent']['area_third'][0] = $v['id'];
                    }
                }
                $data['parent']['area_third'][''] = "-请选择-";
                if($agentData['area_third'] == ""){
                    $data['agent']['area_third'][0] = '';
                }
                $data['parent']['area_third']['show'] = "1";
            }
            else{
                $data['parent']['area_third']['show'] = "0";
            }
            echo json_encode(array('success'=>true,'data'=>$data));exit;
        }
        else{
            $parent = $agent->getRow('*',array('agent_id'=>$_POST['parent_id']));
            $data['parent']['area_first'][$parent['area_first']] = $parent['area_first'];
            $data['agent']['area_first'][0] = $parent['area_first'];

            if($parent['area_second'] != ''){
                $data['parent']['area_second'][$parent['area_second']] = $parent['area_second'];
                $data['agent']['area_second'][0] = $parent['area_second'];
                if($parent['area_third'] != ''){
                    $data['parent']['area_third'][$parent['area_third']] = $parent['area_third'];
                    $data['agent']['area_third'][0] = $parent['area_third'];
                    $data['parent']['area_third']['show'] = "1";
                }
                else{
                    $thirdId = kernel::single("sysagent_passport")->getAreaNameByName(array('0'=>$parent['area_first'],'1'=>$parent['area_second']));
                    $thirdArea = area::getAreaIdPath($thirdId['1']['id']);
                    foreach ($thirdArea as $k => $v) {
                        $data['parent']['area_third'][$v] = area::getAreaNameById($v);
                        if(area::getAreaNameById($v) == $agentData['area_third']){
                            $data['agent']['area_third'][0] = $v;
                        }
                    }
                    if(count($data['parent']['area_third'])){
                        $data['parent']['area_third'][''] = "-请选择-";
                        if($agentData['area_third'] == ''){
                            $data['agent']['area_third'][0] = "";
                        }
                        $data['parent']['area_third']['show'] = "1";
                    }
                    else{
                        $data['parent']['area_third']['show'] = "0";
                    }
                }
            }
            else{
                $tempdata = kernel::single("sysagent_passport")->getAreaNameByName(array('0'=>$parent['area_first']));
                foreach ($area as $k => $v) {
                    if($v['id'] == $tempdata['0']['id']){
                        $secondArea = $v['children'];
                        break;
                    }
                }
                foreach ($secondArea as $k => $v) {
                    $data['parent']['area_second'][$v['id']] = $v['value'];
                    if($v['value'] == $agentData['area_second']){
                        $data['agent']['area_second'][0] = $v['id'];
                        $thirdArea = $v['children'];
                    }
                    $data['parent']['area_second'][''] = "-请选择-";
                }
                if($agentData['area_second'] == ""){
                    $data['agent']['area_second'][0] = "";
                    $data['parent']['area_third']['show'] = "0";
                }
                else{
                    $data['agent']['area_third'][0] = "";
                    if(count($thirdArea)){
                        foreach ($thirdArea as $k => $v) {
                            $data['parent']['area_third'][$v['id']] = $v['value'];
                            if($v['value'] == $agentData['area_third']){
                                $data['agent']['area_third'][0] = $v['id'];
                            }
                        }
                        $data['parent']['area_third'][''] = "-请选择-";
                        $data['parent']['area_third']['show'] = "1";
                    }
                    else{
                        $data['parent']['area_third']['show'] = "0";
                    }
                }
            }
            echo json_encode(array('success'=>true,'data'=>$data));exit;
        }
    }

    /**
     * ps ：保存代理商地区的修改
     * Time：2015/11/26 16:28:17
     * @author liuxin
     * @param $_POST
     * @return json
    */
    function saveArea(){
        $model_relation = app::get("sysagent")->model("agent_relation");
        $model_agent = app::get("sysagent")->model("agents");
        if(is_numeric($_POST['first_id'])){
            $_POST['first_id'] = area::getAreaNameById($_POST['first_id']);
        }
        if(is_numeric($_POST['second_id'])){
            $_POST['second_id'] = area::getAreaNameById($_POST['second_id']);
        }
        if(is_numeric($_POST['third_id'])){
            $_POST['third_id'] = area::getAreaNameById($_POST['third_id']);
        }
        // dump($_POST);die;
        $relation = $model_relation->getRow('*',array('agent_id'=>$_POST['agent_id']));
        if(count($relation)){
            //查找是否存在和修改后地区不同的子代理商
            $sql = "select * 
                    from sysagent_agents
                    where agent_id in (
                    select agent_id
                    from sysagent_agent_relation
                    where left_id > {$relation['left_id']}
                    and left_id < {$relation['right_id']})";
            if($_POST['first_id']){
                $sqlcon = " and (area_first != '{$_POST['first_id']}'";
                if($_POST['second_id']){
                    $sqlcon .= " or area_second != '{$_POST['second_id']}'";
                    if($_POST['third_id']){
                        $sqlcon .=" or area_third != '{$_POST['third_id']}'";
                    }
                }
                $sqlcon .= ")";
            }
            $sql = $sql.$sqlcon;
            $row = $this->db->fetchAll($sql);
            $str = "select * 
                    from sysagent_agents
                    where parent_id in
                    (select agent_id
                    from sysagent_agent_relation
                    where left_id>={$relation['left_id']}
                    and left_id<={$relation['right_id']})
                    and status != '1'";
            $str = $str.$sqlcon;
            $rowset = $this->db->fetchAll($str);
            if(count($row)||count($rowset)){
                $msg = "该代理商有与修改后区域不符合的子代理商，请先处理其子代理商";
                echo json_encode(array('success'=>false,'msg'=>$msg));exit;
            }
        }

        $data = array(
            'agent_id' => $_POST['agent_id'],
            'area_first' => $_POST['first_id'],
            'area_second' => $_POST['second_id']?$_POST['second_id']:'',
            'area_third' => $_POST['third_id']?$_POST['third_id']:''
        );
        $model_agent->save($data);
        echo json_encode(array('success'=>true,'msg'=>"修改成功",'data'=>$data));exit;
    }

    public function pagination($current,$count,$get,$limit){ //本控制器公共分页函数
        $app = app::get('sysagent');
        $ui = new base_component_ui($app);
        //unset($get['singlepage']);
        $link = '?app=sysagent&ctl=admin_agent&act=ajax_html&id='.$get['id'].'&finder_act='.$get['page'].'&'.$get['page'].'=%d';
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
        if(!$row) return null;
        $pagelimit = 10;
        $objMdlagent = app::get('sysagent')->model('agents');
        // $pagedata = $objMdlagent->getRow('point',array('agent_id'=>$row));
        $objMdlagentPoint = app::get('sysagent')->model('agent_points');
        $objMdlagentPointLog = app::get('sysagent')->model('agent_pointlog');
        $nPage = $_GET['detail_point'] ? $_GET['detail_point'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $point = $objMdlagentPoint->getRow('point_count,expired_point',array('agent_id'=>$row));
        $point['point_count'] = $point['point_count']?$point['point_count']:'0';
        $point['expired_point'] = $point['expired_point']?$point['expired_point']:'0';
        $point['agent_id'] = $row;
        $count = count($objMdlagentPointLog->getList('*',array('agent_id'=>$row)));
        $pointLog = $objMdlagentPointLog->getList('*',array('agent_id'=>$row),($nPage - 1)*$pagelimit,$pagelimit,"modified_time desc");
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
        $pagedata['pager'] = $this->pagination($nPage,$count,$_GET,$pagelimit);
        $pagedata['point_log'] = $pointLog;
        return view::make('sysagent/admin/agent/page_point_list.html', $pagedata)->render();
    }

    /**
     * ps ：显示代理商入驻申请时上传的图片
     * Time：2015/12/18 16:46:18
     * @author liuxin
    */
    function showAgentImages($agent_id){
        $objMdlagent = app::get('sysagent')->model('agents');
        $pagedata = $objMdlagent->getRow('picture',array('agent_id'=>$agent_id));
        $temp_pic=explode(',',$pagedata['picture']);

        $pics=array();
        foreach ($temp_pic as & $value) {
            if(!$value) continue;
            $temp=array();
            $temp['h']=explode('.', $value);
            $temp['t']=$value.'_t.'.$temp['h'][count($temp['h'])-1];
            $temp['l']=$value;
            $pics[]=$temp;
        }
        $pagedata['pictures']=$pics;
        return view::make('sysagent/admin/agent/picture.html', $pagedata)->render();
    }
    /**
    * ps ：发送短信
    * Time：2016/01/31 16:04:13
    * @author jiang
    */
    function expand_sms(){
        if($_POST){
            $agent_verify = app::get('sysagent')->getConf('sysagent_setting.agent_verify')?app::get('sysagent')->getConf('sysagent_setting.agent_verify'):'平台方';
            $i=0;
            foreach ($_POST['mobile'] as $key => $value) {
                $params=array();
               if($_POST['name'][$key] && $value){
                    try{
                        $content = "{$_POST['name'][$key]}您好，{$agent_verify}诚邀您成为欢乐兑一级代理商,点击链接下载app并申请入驻。推荐ID：{$agent_verify}；http://www.huanledui.cn/index.php/app";
                        //发送短信
                        kernel::single('sysagent_passport')->sendSms($value,$content);
                    }
                    catch(Exception $e){
                        $msg = $e->getMessage();
                        return $this->splash('error',null,'短信发送失败');
                    }
                    $i++;
               }
            }
            $url = '?app=sysagent&ctl=admin_agent&act=expand_sms';
            $msg = app::get('sysuser')->_('发送成功，共发送'.$i.'条短信');
            return $this->splash('success',$url,$msg);
        }
        return $this->page('sysagent/admin/agent/expand_sms.html', $pagedata);
    }
}