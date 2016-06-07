<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysagent_finder_agents {
    public $column_editbutton;
    public $column_editbutton_width=220;
    public $column_uname;
    public $column_uname_order = 120;
    public $column_email;
    public $column_email_order = 130;
    public $column_mobile;
    public $column_mobile_order = 140;
    public $detail_basic;
    public $detail_pwd;
    public $detail_point;
    var $pagelimit = 10;

    public function __construct($app)
    {
        $this->controller = app::get('sysagent')->controller('admin_agent');
        $this->app = $app;
        $this->db = db::connection();
        $this->column_editbutton = app::get('sysagent')->_('操作');
        $this->column_area = app::get('sysagent')->_('地区');
        $this->column_level = app::get('sysagent')->_('代理商等级');
        $this->column_parent = app::get('sysagent')->_('上级代理商');
        $this->detail_basic = app::get('sysagent')->_('基础信息');
        $this->detail_pwd = app::get('sysagent')->_('密码修改');
        $this->detail_point = app::get('sysagent')->_('积分');
        $this->age_action_buttons = array('allow','noallow','cancel','restart');
    }

    public function column_editbutton(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $colList[$k] = $this->_column_editbutton_2($row);
        }
    }

    /**
     * @brief 操作列显示的信息(two)
     *
     * @param $row
     *
     * @return
     */
    public function _column_editbutton_2($row)
    {
        $arr = array(
            'app'=>$_GET['app'],
            'ctl'=>$_GET['ctl'],
            'act'=>$_GET['act'],
            'finder_id'=>$_GET['_finder']['finder_id'],
            'action'=>'detail',
            'finder_name'=>$_GET['_finder']['finder_id'],
        );

        $newu = http_build_query($arr,'','&');
        $arr_link = array(
            'info'=>array(
                'detail_basic'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_basic&id='.$row['agent_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysagent')->_('代理商信息'),
                    'target'=>'tab',
                ),
            ),
            'finder'=>array(
                'detail_pwd'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_pwd&id='.$row['agent_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysagent')->_('修改密码'),
                    'target'=>'tab',
                ),
                'detail_point'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_point&id='.$row['agent_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysagent')->_('积分'),
                    'target'=>'tab',
                ),
            ),
        );

        //增加编辑菜单权限@lujy
        $permObj = kernel::single('desktop_controller');
        if(!$permObj->has_permission('editbasic')){
            unset($arr_link['finder']['detail_basic']);
        }
        if(!$permObj->has_permission('editpwd')){
            unset($arr_link['finder']['detail_pwd']);
        }
        if(!$permObj->has_permission('editpoint')){
            unset($arr_link['finder']['detail_point']);
        }

        $pagedata['arr_link'] = $arr_link;
        $pagedata['handle_title'] = app::get('sysagent')->_('编辑');
        $pagedata['is_active'] = 'true';
       return view::make('sysagent/admin/agent/actions.html', $pagedata)->render();
    }

    /**
     * @brief 用户名列重定义
     *
     * @param $row
     *
     * @return
     */
    public function column_uname(&$colList, $list)
    {
        $ids = array_column($list, 'agent_id');
        if( !$ids ) return $colList;

        $agentInfoList = app::get('sysagent')->model('agents')->getList('agent_id', array('agent_id'=>$ids));
        $agentInfoList = array_bind_key($agentInfoList,'agent_id');

        foreach($list as $k=>$row)
        {
            $info = $agentInfoList[$row['agent_id']];
            $colList[$k] = $info['username'];
        }
    }

    /**
     * @brief 邮箱字段列重定义
     *
     * @param $row
     *
     * @return
     */
    public function column_email(&$colList, $list)
    {
        $ids = array_column($list, 'agent_id');
        if( !$ids ) return $colList;

        $agentInfoList = app::get('sysagent')->model('agents')->getList('agent_id', array('agent_id'=>$ids));
        $agentInfoList = array_bind_key($agentInfoList,'agent_id');

        foreach($list as $k=>$row)
        {
            $info = $agentInfoList[$row['agent_id']];
            $colList[$k] = $info['email'];
        }
    }

    /**
     * @brief 会员手机号重定义显示
     *
     * @param $row
     *
     * @return
     */
    public function column_mobile(&$colList, $list)
    {
        $ids = array_column($list, 'agent_id');
        if( !$ids ) return $colList;

        $agentInfoList = app::get('sysagent')->model('agents')->getList('agent_id', array('agent_id'=>$ids));
        $agentInfoList = array_bind_key($agentInfoList,'agent_id');

        foreach($list as $k=>$row)
        {
            $info = $agentInfoList[$row['agent_id']];
            $colList[$k] = $info['mobile'];
        }
    }

    /**
     * @brief 会员手机号重定义显示
     *
     * @param $row
     *
     * @return
     */
    public function column_area(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $colList[$k] = $row['area_first'].$row['area_second'].$row['area_third'];
        }
    }


    /**
     * ps ：代理商详情页面
     * Time：2015/11/19 09:00:09
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
     public function detail_basic($row)
    {
        $url = '?app=sysagent&ctl=admin_agent&act=index';
        //这里$this->odr_action_is_all_disable好像暂时没什作用，但是欧宝ec中这个定义，所以先留着
        $this->odr_action_is_all_disable=true;
        $agent = app::get('sysagent')->model("agents");
        $app = app::get('sysagent')->controller('admin_agent');
        if($_POST){
            // dump($_POST);die;
            $data = array(
                'agent_id' => $_POST['agent_id'],
                'name' => $_POST['agent_name'],
                'sex' => $_POST['agent_sex'],
                'mobile' => $_POST['agent_mobile'],
                'email' => $_POST['agent_email'],
                'parent_id' => $_POST['agent_parent_id'],
                'addr' => $_POST['agent_addr'],
                'discount' => $_POST['discount'],
                'kind' => $_POST['agent_kind'],
                'discount' => $_POST['agent_discount']
            );
            $mb_confirm = app::get('sysagent')->model('agents')->getRow('agent_id',array('mobile'=>$data['mobile']));
            if(!empty($mb_confirm)&&$mb_confirm['agent_id']!=$data['agent_id']){
                return $app->splash('error',$url,"该手机号码已经存在!");
            }
            if ($data['discount'] == '0') {
               return $app->splash('error',null,'积分率必须大于零！');
            }
            if($_POST['agent_parent_id'] == $_POST['agent_parentId']){
                $agent->save($data);
                return $app->splash('success',$url,"操作成功");
            }else{//处理子代理商
                if(kernel::single('sysagent_agent_Nodes')->moveNode($data)){
                    if($data['parent_id'] == 0){
                        $parent['agent_level'] = 0;
                    }
                    else{
                        $parent = $agent->getRow('agent_level',array('agent_id'=>$data['parent_id']));
                    }
                    $level = $agent->getRow('agent_level',array('agent_id'=>$data['agent_id']));
                    $num = $parent['agent_level'] + 1 - $level['agent_level'];
                    $data['agent_level'] = $parent['agent_level'] + 1;
                    $agent->save($data);
                    $relation = app::get('sysagent')->model('agent_relation')->getRow('*',array('agent_id'=>$data['agent_id']));
                    if(count($relation) != 0){
                        $sql = "update sysagent_agents 
                                set agent_level = agent_level + {$num}
                                where agent_id in
                                (select agent_id
                                from sysagent_agent_relation
                                where left_id>{$relation['left_id']}
                                and left_id<{$relation['right_id']})";
                        $this->db->executeQuery($sql);
                        $str = "update sysagent_agents 
                                set agent_level = agent_level + {$num}
                                where parent_id in
                                (select agent_id
                                from sysagent_agent_relation
                                where left_id>={$relation['left_id']}
                                and left_id<={$relation['right_id']})
                                and status != '1'";
                        $this->db->executeQuery($str);
                    }
                    return $app->splash('success',$url,"操作成功");
                }else{
                    $msg = "新上级代理商不能是他的子代理商！";
                    return $app->splash('error',null,$msg);
                }
            }
        }
        //测试数据
       /* $pagedata['agent'] = array('agent_is' => 1
                                ,'username' => 'zhang'
                                ,'status' => '0'
                                ,'apply_status' => 'failing'
                                     );*/
        $sysinfo = kernel::single('sysagent_passport')->memInfo($row);

        $sysinfo['level'] = $sysinfo['level']."级代理商";
        $rowset = kernel::single("sysagent_passport")->getSameareaAll($sysinfo['agent_id']);
        foreach ($rowset as $key => $value) {
            if($value['agent_id']!=$sysinfo['agent_id']){
                $options[$value['agent_id']] = $value['username'];
            }
        }
        if($rowset['status'] == 1){
            $sonagent = kernel::single("sysagent_agent_Nodes")->getChild($sysinfo);
            foreach ($sonagent as $k => $v) {
                if($options[$v]){
                    unset($options[$v]);
                }
            }
        }
        //处理图片
        if($sysinfo['picture']&&$sysinfo['picture']!=',') $sysinfo['pictures']=explode(',',$sysinfo['picture']);

        $options['0'] = "平台方";
        $sysinfo['parents'] = $options;
        $pagedata['agent'] = $sysinfo;
        // dump($pagedata);exit;
        $actionbutton = kernel::single('sysagent_agent_actionbutton');
        $pagedata['action_buttons']= $actionbutton->get_buttons($pagedata['agent'],$this->odr_action_is_all_disable);
        if($pagedata['agent']['kind']==1) return view::make('sysagent/admin/agent/detailPer.html', $pagedata)->render();
        else return view::make('sysagent/admin/agent/detailCom.html', $pagedata)->render();
    }

   /**
     * @brief代理商密码管理(update)
     *
     * @param $row
     *
     * @return
     */
    public function detail_pwd($row)
    {
        // $paminfo = app::get('sysagent')->model('account')->getRow('*',array('agent_id'=>$row));
        $paminfo = app::get('sysagent')->model('agents')->getRow('agent_id,username',array('agent_id'=>$row));
        if($_POST){
            $app = app::get('sysagent')->controller('admin_agent');
            $checkPwd = kernel::single('sysagent_passport')->checkPwd($_POST['login_password'],$_POST['psw_confirm']);
            if($checkPwd=='true'){
                $data['login_account'] = $paminfo['username'];
                $data['account_id'] = app::get('sysagent')->model('account')->getRow('account_id',array('login_account'=>$paminfo['username']))['account_id'];
                $data['login_password'] =  pam_encrypt::make($_POST['login_password']);
                $data['modified_time'] = time();
                try
                {
                    app::get('sysagent')->model('account')->save($data);
                }
                catch(Exception $e)
                {
                    $msg = $e->getMessage();
                    return $app->splash('error',null,$msg);
                }

            }
            else{
                return $app->splash('error',null,$checkPwd);
            }
        }
        $sysinfo['login_account'] = $paminfo['username'];
        $sysinfo['agent_id'] = $paminfo['agent_id'];
        $pagedata['data'] = $sysinfo;
        return view::make('sysagent/admin/agent/updatepwd.html', $pagedata)->render();
    }
    

    /**
     * @brief 会员积分管理
     *
     * @param $row
     *
     * @return
     */
    public function detail_point($row)
    {   
        if(!$row) return null;
        $objMdlagent = app::get('sysagent')->model('agents');
        // $pagedata = $objMdlagent->getRow('point',array('agent_id'=>$row));
        $objMdlagentPoint = app::get('sysagent')->model('agent_points');
        $objMdlagentPointLog = app::get('sysagent')->model('agent_pointlog');
        if($_POST){
            $params = $_POST;
            $data = array(
                'behavior' => '管理员改变积分',
                'modify_point' =>$params['point']['modify_point'],
                'modify_remark' =>$params['point']['modify_remark'],
                'agent_id' =>$params['agent_id'],
            );
            if(round($params['point']['modify_point']) != $params['point']['modify_point']){
                $msg = app::get('sysagent')->_('请输入一个整数！');
                return app::get('sysagent')->controller('admin_agent')->splash('error',null,$msg);
            }

            $objMdlagentPoint = app::get('sysagent')->model('agent_points');
            $point = $objMdlagentPoint->getRow('point_count,expired_point',array('agent_id'=>$data['agent_id']));

            if($point['point_count'] + $data['modify_point'] < 0){
                $data['modify_point'] = 0 - $point['point_count'];
                $data['point_count'] = 0;
            }
            else{
                $data['point_count'] = $point['point_count'] + $data['modify_point'];
            }

            $data['modified_time'] = time();
            $dataPoint = array(
                'agent_id' => $data['agent_id'],
                'point_count' => $data['point_count'],
                'expired_point' => $point['expired_point'],
                'modified_time' => $data['modified_time']
            );
            $discount_self = app::get('sysagent')->model('agents')->getRow('*',array('agent_id'=>$data['agent_id']));
            if($discount_self['discount']!=''){
                $dataPointLog['discount'] = $discount_self['discount'];
            }else{
                $dataPointLog['discount'] = app::get('sysagent')->getConf('sysagent_setting.agent_ratio')?app::get('sysagent')->getConf('sysagent_setting.agent_ratio'):'0.1';
            }
            // dump($dataPointLog['discount']);die;
            $dataPointLog = array(
                'agent_id' => $data['agent_id'],
                'modified_time' => $data['modified_time'],
                'behavior' => $data['behavior'],
                'point' => abs($data['modify_point']),
                'remark' => $data['modify_remark'],
                'operator' => $_SESSION['account']['shopadmin']['account'],
                'direction' => "平台方",
                'role' => "平台方",
                'discount'=>$dataPointLog['discount'],
                'sub_id'=>0,
            );

            if($params['point']['modify_point'] > 0){
                $dataPointLog['behavior_type'] = 'obtain';
            }else{
                $dataPointLog['behavior_type'] = 'consume';
            }

            $dataagent = array(
                'agent_id' => $data['agent_id'],
                'point' => $data['point_count']
            );
           
            // dump($dataPoint); dump($dataagent);dump($dataPointLog);die;
            $objMdlagentPointLog = app::get('sysagent')->model('agent_pointlog');
            // if( (!$agentId = $objMdlagentPoint->save($dataPoint))||(!$agentId = $objMdlagentPointLog->save($dataPointLog))||(!$objMdlagent->save($dataagent)) )
            // {   

            //     header('Content-Type:text/jcmd; charset=utf-8');
            //     echo '{修改失败}';
            //     exit;
            // }
             try{
                // kernel::single('sysagent_passport')->sendPointSms('平台方',$dataagent['agent_id'],$params['point']['modify_point']);
                $objMdlagentPoint->save($dataPoint);
                $objMdlagentPointLog->save($dataPointLog);
                $objMdlagent->save($dataagent);
            }
            catch(Exception $e){
                // $objMdlagentPoint->save_msg = $e->getMessage();
                // $objMdlagentPointLog->save_msg = $e->getMessage();
                // $objMdlagent->save_msg = $e->getMessage();
                //2016年1月28日 09:07:23 发送短信失败不做操作
                $msg = $e->getMessage();
                return false;
            }
            //发送短信
             try{
                $agent_arrival = app::get('sysagent')->getConf('sysagent_setting.agent_arrival')  == 0 ?app::get('sysagent')->getConf('sysagent_setting.agent_arrival'):'1';

                if((string)$agent_arrival == '1'){
                    kernel::single('sysagent_passport')->sendPointSms('平台方',$dataagent['agent_id'],$params['point']['modify_point']);
                }
            }
            catch(Exception $e){
                //2016年1月28日 09:07:23 发送短信失败不做操作
            }
        }
        $nPage = $_GET['detail_point'] ? $_GET['detail_point'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $point = $objMdlagentPoint->getRow('point_count,expired_point',array('agent_id'=>$row));
        $point['point_count'] = $point['point_count']?$point['point_count']:'0';
        $point['expired_point'] = $point['expired_point']?$point['expired_point']:'0';
        $point['agent_id'] = $row;
        $count = count($objMdlagentPointLog->getList('*',array('agent_id'=>$row)));
        $pointLog = $objMdlagentPointLog->getList('*',array('agent_id'=>$row),($nPage - 1)*$this->pagelimit,$this->pagelimit,"modified_time desc");
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
        $pagedata['pager'] = $this->controller->pagination($nPage,$count,$_GET,$this->pagelimit);
        $pagedata['point_log'] = $pointLog;
        return view::make('sysagent/admin/agent/detail_point.html', $pagedata)->render();
    }

   public function column_level(&$colList, $list)
    {
        foreach ($list as $k => &$v) {
            $colList[$k] = $v['agent_level'].'级代理商';
        }
    }

    /**
     * ps ：重定义上级代理商列
     * Time：2015/11/16 20:10:06
     * @author liuxin
     * @param $colList, $list
    */
    public function column_parent(&$colList, $list)
    {
        $parent_ids = array_column($list, 'parent_id');
        $ids = array_column($list, 'agent_id');
        if( !$ids ) return $colList;

        $model = app::get('sysagent')->model('agents');
        foreach ($parent_ids as $k=>&$v) {
            if($v!=0){
                $data[$ids[$k]] = $model -> getRow('username',array('agent_id'=>$v));
            }else{
                $data[$ids[$k]]['username'] = '平台方';
            }
        }

        foreach($list as $k=>$row)
        {
            $info = $data[$row['agent_id']];
            $colList[$k] = $info['username'];
        }
    }

}
