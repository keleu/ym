<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysuser_finder_user {
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
    public $detail_grade;
    public $detail_experience;
    public $detail_point;
    var $pagelimit = 10;

    public function __construct($app)
    {
        $this->app = $app;
        $this->controller = app::get('sysuser')->controller('admin_user');
        $this->column_editbutton = app::get('sysuser')->_('操作');
        $this->column_uname = app::get('sysuser')->_('用户名');
        $this->column_email = app::get('sysuser')->_('邮箱');
        $this->column_mobile = app::get('sysuser')->_('手机号');
        $this->column_area = app::get('sysuser')->_('地区');
        $this->detail_basic = app::get('sysuser')->_('会员信息');
        $this->detail_pwd = app::get('sysuser')->_('密码修改');
        $this->detail_grade = app::get('sysuser')->_('会员等级');
        $this->detail_experience = app::get('sysuser')->_('会员经验值');
        $this->detail_point = app::get('sysuser')->_('会员积分');
    }

    public function column_editbutton_2(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $colList[$k] = $this->_column_editbutton_2();
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
                    'submit'=>'?'.$newu.'&finderview=detail_basic&id='.$row['user_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysuser')->_('会员信息'),
                    'target'=>'tab',
                ),
            ),
            'finder'=>array(
                'detail_pwd'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_pwd&id='.$row['user_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysuser')->_('修改密码'),
                    'target'=>'tab',
                ),
                'detail_experience'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_experience&id='.$row['user_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysuser')->_('经验值'),
                    'target'=>'tab',
                ),
                'detail_point'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_point&id='.$row['user_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysuser')->_('积分'),
                    'target'=>'tab',
                ),
            ),
        );

        //增加编辑菜单权限@lujy
        $permObj = kernel::single('desktop_controller');
        if(!$permObj->has_permission('editpwd')){
            unset($arr_link['finder']['detail_pwd']);
        }
        if(!$permObj->has_permission('editexp')){
            unset($arr_link['finder']['detail_experience']);
        }
        if(!$permObj->has_permission('editpoint')){
            unset($arr_link['finder']['detail_point']);
        }

        $pagedata['arr_link'] = $arr_link;
        $pagedata['handle_title'] = app::get('sysuser')->_('编辑');
        $pagedata['is_active'] = 'true';
        return view::make('sysuser/admin/user/actions.html', $pagedata)->render();
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
        $ids = array_column($list, 'user_id');
        if( !$ids ) return $colList;

        $userInfoList = app::get('sysuser')->model('account')->getList('login_account,user_id', array('user_id'=>$ids));
        $userInfoList = array_bind_key($userInfoList,'user_id');

        foreach($list as $k=>$row)
        {
            $info = $userInfoList[$row['user_id']];
            $colList[$k] = $info['login_account'];
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
        $ids = array_column($list, 'user_id');
        if( !$ids ) return $colList;

        $userInfoList = app::get('sysuser')->model('account')->getList('email,user_id', array('user_id'=>$ids));
        $userInfoList = array_bind_key($userInfoList,'user_id');

        foreach($list as $k=>$row)
        {
            $info = $userInfoList[$row['user_id']];
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
        $ids = array_column($list, 'user_id');
        if( !$ids ) return $colList;

        $userInfoList = app::get('sysuser')->model('account')->getList('mobile,user_id', array('user_id'=>$ids));
        $userInfoList = array_bind_key($userInfoList,'user_id');

        foreach($list as $k=>$row)
        {
            $info = $userInfoList[$row['user_id']];
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
            $colList[$k] = explode(':',$row['area'])[0];
        }
    }

    /**
     * @brief 操作列内容的显示(one)
     *
     * @param $row
     *
     * @return
     */
    public function column_editbutton(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = '?app=sysuser&ctl=admin_user&act=editUserInfo&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['user_id'];
            $target = 'dialog::  {title:\''.app::get('sysuser')->_('会员信息编辑').'\', width:500, height:400}';
            $title = app::get('sysuser')->_('编辑');
            $button = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';

            //$url = '?app=desktop&act=alertpages&goto='.urlencode('?app=sysuser&ctl=admin_point&act=index&user_id='.$row['user_id'].'&nobuttion=1');
            //$title = app::get('sysuser')->_('查看积分');
            //$button .= ' | <a target="_blank" href="' . $url . '">' . $title . '</a>';

            //$url = '?app=desktop&act=alertpages&goto='.urlencode('?app=sysuser&ctl=admin_experience&act=index&user_id='.$row['user_id'].'&nobuttion=1');
            //$title = app::get('sysuser')->_('查看经验值');
            //$button .= ' | <a target="_blank" href="' . $url . '">' . $title . '</a>';

            $colList[$k] = $button;
        }
    }

    /**
     * @brief 会员详细信息显示
     *
     * @param $row
     *
     * @return
     */
    public function detail_basic($row)
    {
        $sysinfo = kernel::single('sysuser_passport')->memInfo($row);
        $pagedata['data'] = $sysinfo;
        return view::make('sysuser/admin/user/detail.html', $pagedata)->render();
    }

    /**
     * @brief 会员密码管理(update)
     *
     * @param $row
     *
     * @return
     */
    public function detail_pwd($row)
    {
        $paminfo = app::get('sysuser')->model('account')->getRow('*',array('user_id'=>$row));
        $sysinfo['login_account'] = $paminfo['login_account'];
        $sysinfo['user_id'] = $paminfo['user_id'];

        $pagedata['data'] = $sysinfo;
        return view::make('sysuser/admin/user/updatepwd.html', $pagedata)->render();
        //return 'ok';
    }

    /**
     * @brief 会员经验值管理
     *
     * @param $row
     *
     * @return
     */
    public function detail_experience($row)
    {
        $permObj = kernel::single('desktop_controller');
        if(!$permObj->has_permission('experience')){
            return '<br/>经验值<br/>';
        }

        $objMdlUser = app::get('sysuser')->model('user');
        $pagedata = $objMdlUser->getRow('grade_id,experience',array('user_id'=>$row));
        return view::make('sysuser/admin/user/detail_experience.html', $pagedata)->render();
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
        $permObj = kernel::single('desktop_controller');
        if(!$permObj->has_permission('point')){
            return '<br/>积分详情<br/>';
        }

        $objMdlUserPoint = app::get('sysuser')->model('user_points');
        $objMdlUserPointLog = app::get('sysuser')->model('user_pointlog');
        if($_POST){
            $params = $_POST;
            $data = array(
                'behavior' => '管理员改变积分',
                'modify_point' =>$params['point']['modify_point'],
                'modify_remark' =>$params['point']['modify_remark'],
                'user_id' =>$params['user_id'],
            );
            if(round($params['point']['modify_point']) != $params['point']['modify_point']){
                $msg = app::get('sysuser')->_('请输入一个整数！');
                return app::get('sysuser')->controller('admin_user')->splash('error',null,$msg);
            }

            $objMdlUserPoint = app::get('sysuser')->model('user_points');
            $point = $objMdlUserPoint->getRow('point_count,expired_point',array('user_id'=>$data['user_id']));

            if($point['point_count'] + $data['modify_point'] < 0){
                $data['modify_point'] = 0 - $point['point_count'];
                $data['point_count'] = 0;
            }
            else{
                $data['point_count'] = $point['point_count'] + $data['modify_point'];
            }

            $data['modified_time'] = time();
            $dataPoint = array(
                'user_id' => $data['user_id'],
                'point_count' => $data['point_count'],
                'expired_point' => $point['expired_point'],
                'modified_time' => $data['modified_time']
            );
            $dataPointLog = array(
                'user_id' => $data['user_id'],
                'modified_time' => $data['modified_time'],
                'behavior' => $data['behavior'],
                'point' => abs($data['modify_point']),
                'remark' => $data['modify_remark'],
                'operator' => $_SESSION['account']['shopadmin']['account'],
                'direction' => "平台方"
            );

            if($params['point']['modify_point'] > 0){
                $dataPointLog['behavior_type'] = 'obtain';
            }else{
                $dataPointLog['behavior_type'] = 'consume';
            }
            $dataUser = array(
                'user_id' => $data['user_id'],
                'point' => $data['point_count']
            );
            // dump($_SESSION);dump($dataPointLog);die;
            $objMdlUserPointLog = app::get('sysuser')->model('user_pointlog');
            $objMdlUser = app::get('sysuser')->model('user');
            if( (!$userId = $objMdlUserPoint->save($dataPoint))||(!$objMdlUserPointLog->save($dataPointLog))||(!$objMdlUser->save($dataUser)))
            {
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{修改失败}';
                exit;
            }else{
                //保存成功:在微信端提示积分变动信息
                //查找该会员的opendid:微信对应账号
                kernel::single('weixin_base')->jfCound_change($data['user_id'],'平台管理员在后台操作',$params['point']['modify_point']);
            }
        }
        $nPage = $_GET['detail_point'] ? $_GET['detail_point'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $point = $objMdlUserPoint->getRow('point_count,expired_point',array('user_id'=>$row));
        $point['point_count'] = $point['point_count']?$point['point_count']:'0';
        $point['expired_point'] = $point['expired_point']?$point['expired_point']:'0';
        $point['user_id'] = $row;
        $count = count($objMdlUserPointLog->getList('*',array('user_id'=>$row)));
        $pointLog = $objMdlUserPointLog->getList('*',array('user_id'=>$row),($nPage - 1)*$this->pagelimit,$this->pagelimit,'modified_time DESC');
        $accountObj = app::get('desktop')->model('account');
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
        $pagedata['pager'] = $this->controller->pagination($nPage,$count,$_GET,$this->pagelimit);
        return view::make('sysuser/admin/user/detail_point.html', $pagedata)->render();
    }

    /**
     * @brief 会员积分管理
     *
     * @param $row
     *
     * @return
     */
    public function detail_grade($row)
    {
        $objMdlUserGrade = app::get('sysuser')->model('user_grade');
        $objMdlUser = app::get('sysuser')->model('user');
        $user = $objMdlUser->getRow('grade_id',array('user_id'=>$row));
        $pagedata = $objMdlUserGrade->getRow('grade_name',array('grade_id'=>$user['grade_id']));
        return view::make('sysuser/admin/user/detail_grade.html', $pagedata)->render();
    }

}
