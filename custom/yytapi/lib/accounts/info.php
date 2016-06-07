<?php
/*********************************************************************\
*  Copyright (c) 2007-2015, TH. All Rights Reserved.
*  Author :li
*  FName  :getdata.php
*  Time   :2015/11/26 13:32:55
*  Remark :获取代理商
\*********************************************************************/
class yytapi_accounts_info extends yytapi_base {
    /**
     * ps ：我的子账号列表支持排序搜所
     * Time：2015/11/27 10:40:32
     * @author zhangyan
     * @param 参数类型
     * account_id string  Y   当前代理商id
     * accesstoken  String  Y   帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function account_list($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id) || $account_id != $params['account_id']) {
            //如果用户名不存在，说明是非法登陆
            $data['message']='您的会话已过期!';
            $service->send_user_error('40100001', $data);
        }
        $model_account = app::get("sysagent")->model('account');
        $accountObj=$model_account->getRow('agent_id',array('account_id'=>$params['account_id']));
        $data = $model_account->getList('account_id,name',array('agent_id'=>$accountObj['agent_id'],'account_type'=>'account','is_del'=>'0'));
        // if(count($data)){
        foreach ($data as $k => &$v) {
            $v['account_name'] = $v['name'];
            $v['key'] = $this->getfirstchar($v['name']);
            if(is_null($v['key'])){
                $v['key']= 'Z';
            }
            unset($v['name']);
        }
        usort($data, function($a, $b) {
            $al = $a['key'];
            $bl = $b['key'];
            if ($al == $bl)
                return 0;
            return ($al > $bl) ? 1 : -1;
        });
        $service->send_user_succ('success', $data);
        // }
        // else{
        //     $service->send_user_error('error', $data);
        // }

        // $import_data = print_r($data,1);
        // file_put_contents('../testlog/'.date('YmdHis').'.txt', $import_data);
        // return array(
        //     'accounts'=>array(
        //         'account_id'=>'10',   // Int 子帐号id
        //         'account_name'=>'dfhfg',   //   String  子帐号姓名
        //         'key'=>'a',   //String  首字母，如A/B……
        //     )   //array   子帐号信息
        // );
    }

    /**
     * ps ：获取首字母
     * Time：2015/12/02 09:50:20
     * @author liuxin
     * @param char
     * @return char
    */
    function getfirstchar($s0){
        $fchar = ord($s0{0});
        if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
        $s1 = iconv("UTF-8","gb2312", $s0);
        $s2 = iconv("gb2312","UTF-8", $s1);
        if($s2 == $s0){$s = $s1;}else{$s = $s0;}
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if($asc >= -20319 and $asc <= -20284) return "A";
        if($asc >= -20283 and $asc <= -19776) return "B";
        if($asc >= -19775 and $asc <= -19219) return "C";
        if($asc >= -19218 and $asc <= -18711) return "D";
        if($asc >= -18710 and $asc <= -18527) return "E";
        if($asc >= -18526 and $asc <= -18240) return "F";
        if($asc >= -18239 and $asc <= -17923) return "G";
        if($asc >= -17922 and $asc <= -17418) return "H";
        if($asc >= -17417 and $asc <= -16475) return "J";
        if($asc >= -16474 and $asc <= -16213) return "K";
        if($asc >= -16212 and $asc <= -15641) return "L";
        if($asc >= -15640 and $asc <= -15166) return "M";
        if($asc >= -15165 and $asc <= -14923) return "N";
        if($asc >= -14922 and $asc <= -14915) return "O";
        if($asc >= -14914 and $asc <= -14631) return "P";
        if($asc >= -14630 and $asc <= -14150) return "Q";
        if($asc >= -14149 and $asc <= -14091) return "R";
        if($asc >= -14090 and $asc <= -13319) return "S";
        if($asc >= -13318 and $asc <= -12839) return "T";
        if($asc >= -12838 and $asc <= -12557) return "W";
        if($asc >= -12556 and $asc <= -11848) return "X";
        if($asc >= -11847 and $asc <= -11056) return "Y";
        if($asc >= -11055 and $asc <= -10247) return "Z";
        return null;
    }
    /**
     * ps ：子帐号详情
     * Time：2015/11/27 10:40:32
     * @author zhangyan
     * @param 参数类型
     * account_id   Int Y   子帐号id
     * accesstoken  String  Y   帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function account_detail($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $data['message']='您的会话已过期!';
            $service->send_user_error('40100001',$data);
        } 
        $model_account = app::get('sysagent')->model('account');
        $model_user = app::get('sysuser')->model('user');
        $data = $model_account->getRow('*',array('account_id'=>$params['account_id']));
        $count = count($model_user->getList('user_id',array('agent_account'=>$params['account_id'])));
        // if($data){
        $return = array(
            'account_id' => $data['account_id'],
            'account_name' => $data['name'],
            'sex' => $data['sex']=='2'?1:$data['sex'],
            'id_card' => $data['id_card'],
            'mobile' => $data['mobile'],
            'member_cnt' => $count
        );
        $service->send_user_succ('success', $return);
        // }
        // else{
        //     $service->send_user_error('error', '未发现记录！');
        // }

        // return array(
        //     'account_id'=>'sdgds',            //int 子帐号id
        //     'account_name'=>'sdgds',            //  String  子帐号姓名
        //     'sex'=>'sdgds',            //   String  性别
        //     'id_card'=>'sdgds',            //   string  身份证号码
        //     'mobile'=>'sdgds',            // String  手机号
        //     'member_cnt'=>'sdgds',            // int 该子帐号发展的会员个数
        // );//array   子帐号信息
    }
    /**
     * ps ：子账号的会员支持模糊搜索
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * account_id   Int Y   子帐号id
     * accesstoken  String  Y   帐号登录返回的 accesst
     * where_key    String  N   会员名字或手机号搜索的关键字，模糊匹配
     * page Int N   页数：默认每页20，如果为空，则全部数据
     * @return 返回值类型
    */
    public function account_member($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $data['message']='您的会话已过期!';
            $service->send_user_error('40100001', $data);
        } 
        $params['page']=$params['page']?$params['page']:1;
        $model_account = app::get('sysagent')->model('account');
        $model_user = app::get('sysuser')->model('user');
        $model_useracc = app::get('sysuser')->model('account');
        $pagelimit = 20;

        $sql = "select a.user_id as member_id,b.login_account as member_name,a.sex,b.mobile,a.point
                from sysuser_user a
                left join sysuser_account b on a.user_id=b.user_id
                where a.agent_account = '{$params['account_id']}'";
        if($params['where_key']){
            $sql .= "and (a.name like '%{$params['where_key']}%'
                        or b.mobile like '%{$params['where_key']}%')";
        }
        $data = db::connection()->fetchAll($sql);
        $count = count($data);
        // if($count == 0){
        //     $service->send_user_error('error', '未发现记录！');
        // }
        if($params['page']){
            $npage = ($params['page'] - 1) * $pagelimit;
            $sql .= "limit {$pagelimit} offset {$npage}";
            $members = db::connection()->fetchAll($sql);
            $return['members'] = $members;
        }
        else{
            $return['members'] = $data;
        }
        $return['count'] = $count;
        $return['current_page'] = $params['page'];
        $service->send_user_succ('success', $return);
        // return array(
        //     'members'=>array(
        //         'member_id'=>'zfsdf',   //  Int 会员id
        //         'member_name'=>'李四', //String 会员姓名
        //         'sex'=>'zfsdf',   //String  性别
        //         'mobile'=>'zfsdf',   // String  手机号
        //         'point'=>'zfsdf',   //  Int 积分余额
        //     ),    //array   子帐号的会员信息
        //     'count'=>'10',  // int 返回会员总数
        //     'current_page'=>'10', //int 当前页数
        // );
    }
    /**
     * ps ：添加子帐号时验证
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * accesstoken  String  Y   帐号登录返回的 accesst
     * account_mobile   string  Y   子帐号手机号
     * account_id_card  String  Y   子帐号身份证号
     * account_login    String  Y   登录用户名
     * account_name String  Y   真实姓名
     * sex String Y 性别
     * agent_id Int Y 代理商id
     * 注意：account_mobile，account_id_card，account_login，account_name必须有一个参数传递值，否则无效
     * @return 返回值类型
    */
    public function verify_account($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
           $data['message']='您的会话已过期!';
           $service->send_user_error('40100001', $data);
        } 
        $this->accountCheck($params,$service);
        $service->send_user_succ('success', array(
            'status'=>'true',  //添加状态，true表示成功,false失败
            'message'=>'验证成功', //String   消息：帐号添加成功
            )
        );
        // return array(
        //     'status'=>'true',  //添加状态，true表示成功,false失败
        //     'message'=>'1dgfdgd0', //String   消息：帐号添加成功
        // );
    }

    function accountCheck($params,&$service){
        $model_account = app::get('sysagent')->model('account');
        if($params['account_mobile']){
            if(!preg_match("/^1[34578]\d{9}$/",$params['account_mobile'])){
                $data['message'] = "请输入正确的手机号";
                $service->send_user_error('error', $data);
            }
            $return = $model_account->getRow('*',array('mobile'=>$params['account_mobile'],'is_del'=>0));
            if($return){
                $data['message'] = "该手机号已被注册";
                $service->send_user_error('error', $data);
            }
        }

        if($params['account_id_card']){
            if(!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $params['account_id_card'])){
                $data['message'] = "请输入正确的身份证号";
                $service->send_user_error('error', $data);
            }
            $return = $model_account->getRow('*',array('id_card'=>$params['account_id_card']));
            if($return){
                $data['message'] = "该身份证号已被注册";
                $service->send_user_error('error', $data);
            }
        }

        if($params['account_login']){
            if( strlen(trim($params['account_login']))< 4 )
                {
                    $data['message'] = '登录账号最少4个字符';
                    $service->send_user_error('error', $data);
                }
                elseif( strlen($params['account_login']) > 100 )
                {
                    $data['message'] = '登录账号过长，请换一个重试';
                    $service->send_user_error('error', $data);
                }

                if(!preg_match('/^[^\x00-\x2d^\x2f^\x3a-\x3f]+$/i', trim($params['account_login'])) )
                {
                    $data['message'] = '该登录账号包含非法字符';
                    $service->send_user_error('error', $data);
                }
            $return = $model_account->getRow('*',array('login_account'=>$params['account_login'],'is_del'=>0));
            if($return){
                $data['message'] = "该账号已被注册，请更换一个重试";
                $service->send_user_error('error', $data);
            }
        }

        // if ($params['account_name']) {
        //     $return = $model_account->getRow('*',array('name'=>$params['account_name']));
        // }

        if(!($params['account_mobile']||$params['account_id_card']||$params['account_login']||$params['account_name'])){
            $data['message'] = "请输入至少一个信息";
            $service->send_user_error('7001', $data);
        }
    }
    /**
     * ps ：添加子帐号
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * account_id   Int Y   子帐号id
     * accesstoken  String  Y   帐号登录返回的 accesst
     * account_mobile   string  Y   子帐号手机号
     * account_id_card  String  Y   子帐号身份证号
     * account_login    String  Y   登录用户名
     * account_name String  Y   真实姓名
     * sex  String  Y   性别
     * @return 返回值类型
    */
    public function add_account($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $data['message']='您的会话已过期!';
            $service->send_user_error('40100001', $data);
        }
        $this->accountCheck($params,$service);
        $model_account = app::get('sysagent')->model('account');
        $agent_id = $model_account->getRow('*',array('account_id'=>$params['account_id']));
        //2016-2-1 by jiang 如果该手机好已存在并且子账号为停用 则为修改
        $temp_acc = $model_account->getRow('account_id',array('mobile'=>$params['account_mobile'],'is_del'=>1));
        $_pass = '123456';
        if($params['account_mobile']&&$params['account_login']&&$params['account_name']){
            $data = array(
                'agent_id' => $agent_id['agent_id'],
                'login_account' => $params['account_login'],
                'login_password' => pam_encrypt::make($_pass),
                'name' => $params['account_name'],
                'mobile' => $params['account_mobile'],
                'id_card' => $params['account_id_card'].'',
                'sex' => $params['sex'],
                'account_type' => 'account',
                'createtime' => time()
            );
            if($temp_acc['account_id']){
                $data['account_id']=$temp_acc['account_id'];
                $data['is_del']=0;
            }
            try{
                $model_account->save($data);
            }catch(Exception $e){
                $message = $e->getMessage();
                $service->send_user_error('error', $data);
            }
        }else{
            $data['message']='帐号添加失败，请输入完整信息!';
            $service->send_user_error('7001', $data);
        }
        //发送短信
        try{
            $data['pass']=$_pass;
            kernel::single('sysagent_passport')->sendAccountMessage($data,$agent_id['name']?$agent_id['name']:$agent_id['username']);
        }catch(Exception $e){
            // $data['message'] = $e->getMessage();
            // $data['status']='true';
            // $service->send_user_succ('success', $data);
        }
        $service->send_user_succ('success', array('status'=>'true','message'=>"添加成功,默认密码：".$_pass));
    }
    /**
     * ps ：删除子帐号(只做标记，不直接删除)
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * account_id   Int Y   子帐号id
     * accesstoken  String  Y   帐号登录返回的 accesst
     * agent_id Int Y   当前登录的代理商id
     * @return 返回值类型
    */
    public function delete_account($params, &$service){
        $account_id = $this->check_accesstoken($params['accesstoken']);
        if (empty($account_id)) {
            //如果用户名不存在，说明是非法登陆
            $data['message']='您的会话已过期!';
            $service->send_user_error('40100001', $data);
        } 
        $model_account = app::get('sysagent')->model('account');
        $agent_id = $model_account->getRow('agent_id',array('account_id'=>$params['sub_id']));
        if(!$agent_id){
            $data['message']='删除失败，未发现记录！!';
            $service->send_user_error('error', $data);
        }else{
            $save['account_id']=$params['sub_id'];
            $save['is_del']='1';
            try{
                $model_account->save($save);
            }
            catch(Exception $e){
                $message = $e->getMessage();
                $service->send_user_error('error',"删除失败".$message);
            }
        }

        $service->send_user_succ('success', array('status'=>'true','message'=>"子账号删除成功"));
        // return array(
        //     'status'=>'true',  
        //     'message'=>'1dgfdgd0', //String   成功
        // );
    }

    
}
