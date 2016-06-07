<?php

class sysagent_passport
{

    public $agentId = null;

    public $userName = null;

    public function __construct()
    {
        $this->app = app::get('sysagent');
        $this->db = db::connection();
    }

    /**
     * 获取当前代理商用户基本信息
     *
     * @param int $agentId 如果agentId不存在则返回当前会员用户信息,存在返回指定
     *
     * @return array
     */
    public function memInfo($agentId)
    {
        if( !$this->memInfo[$agentId] )
        {
            $enterapply = app::get('sysagent')->model('enterapply')->getRow('enterapply_id,apply_status',array('agent_id'=>$agentId));
            $account = app::get('sysagent')->model('account')->getRow('login_account',array('agent_id'=>$agentId));
            $sysagentInfo = app::get('sysagent')->model('agents')->getRow('*',array('agent_id'=>$agentId));

            $memInfo = ['agent_id' => $agentId,
                        'addr' => $sysagentInfo['addr'],
                        'area_first' => $sysagentInfo['area_first'],
                        'area_second' => $sysagentInfo['area_second'],
                        'area_third' => $sysagentInfo['area_third'],
                        'level' => $sysagentInfo['agent_level'],
                        'parent_id' => $sysagentInfo['parent_id'],
                        'id_card' => $sysagentInfo['id_card'],
                        'email' => $sysagentInfo['email'],
                        'mobile' => $sysagentInfo['mobile'],
                        'name' => $sysagentInfo['name'],
                        'username' => $sysagentInfo['username'],
                        'birthday' => $sysagentInfo['birthday'],
                        'reg_ip' => $sysagentInfo['reg_ip'],
                        'regtime' => $sysagentInfo['regtime'],
                        'sex' => $sysagentInfo['sex'],
                        'point' => $point['point'] ? $point['point'] : 0,
                        'status' => $sysagentInfo['status'],
                        'apply_status' => $enterapply['apply_status'],
                        'enterapply_id' => $enterapply['enterapply_id'],
                        'discount' => $sysagentInfo['discount'],
                        'is_stop' => $sysagentInfo['is_stop'],
                        'picture' => $sysagentInfo['picture'],
                        'kind' => $sysagentInfo['kind']];
            $this->memInfo[$agentId] = $memInfo;
        }
        return $this->memInfo[$agentId];
    }

    /**
     * 检查密码是否合法，密码是否一致(注册，找回密码，修改密码)调用
     *
     * @params string $pwd  密码
     * @params string $pwdConfirm 确认密码
     *
     * @return bool
     */
    public function checkPwd($pwd, $pwdConfirm){
        if(!$pwd){
            return "新密码不能为空";
        }
        $validator = validator::make(
            ['password' => $pwd , 'password_confirmation' => $pwdConfirm],
            ['password' => 'min:6|max:20|confirmed'],
            ['password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $error[0];
            }
        }

        return true;
    }//end function

    function agentCheck($account,$type,$is_again=0)
    {
        if( empty($account) )
        {
            return $this->app->_('请输入用户名');
        }

        //获取到注册时账号类型
        switch( $type )
        {
            case 'login_account':
                if( strlen(trim($account))< 4 )
                {
                    return $this->app->_('登录账号最少4个字符');
                }
                elseif( strlen($account) > 100 )
                {
                    return $this->app->_('登录账号过长，请换一个重试');
                }

                // if( is_numeric($account) )
                // {
                //     return $this->app->_('登录账号不能全为数字');
                // }

                if(!preg_match('/^[^\x00-\x2d^\x2f^\x3a-\x3f]+$/i', trim($account)) )
                {
                    return $this->app->_('该登录账号包含非法字符');
                }
                $message = $this->app->_('该账号已经被占用，请换一个重试');
                break;
            case 'email':
                if(!preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i',trim($account)) )
                {
                    return $this->app->_('邮件格式不正确');
                }
                $message = $this->app->_('该邮箱已被注册，请更换一个');
                break;
            case 'mobile':
                $message = $this->app->_('该手机号已被注册，请更换一个');
                break;
        }

        //判断账号是否存在
        if(!$is_again){
            if( app::get('sysagent')->model('account')->getRow('*',array('login_account'=>$account)) )
            {
                return $message;
            }
        }
        return true;
    }

    /**
     * ps ：获取上级代理商通用方法
     * Time：2015/11/19 11:11:54
     * @author liuxin
     * @param array() 代理商id
     * @return array() 上级代理商id,username,name
    */
    public function getParent($agent_id){
        $agent = app::get('sysagent')->model('agents');
        $parent_id = $agent->getList('agent_id,parent_id',array('agent_id'=>$agent_id));
        foreach ($parent_id as $k => &$v) {
            $data = $agent->getRow('agent_id,username,name',array('agent_id'=>$v['parent_id']));
            $parent[$k]['agent_id'] = $v['agent_id'];
            $parent[$k]['parent_id'] = $data['agent_id']?$data['agent_id']:'0';
            $parent[$k]['parent_username'] = $data['username']?$data['username']:'平台方';
            $parent[$k]['parent_name'] = $data['name']?$data['name']:'平台方';
        }
        return $parent;
    }

    /**
     * ps ：获取同一地区的一级代理商
     * Time：2015/11/19 13:51:22
     * @author liuxin
     * @param array() 代理商id
     * @return 同一地区一级代理商id,username,name
    */
    public function getSamearea($agent_id){
        $agent = app::get('sysagent')->model('agents');
        $area = $agent->getRow('area_first,area_second,area_third',array('agent_id'=>$agent_id));
        $sql = "select agent_id,username,name
                from sysagent_agents
                where ((area_first = '{$area['area_first']}'
                and area_second = '{$area['area_second']}'
                and area_third = '{$area['area_third']}')
                or (area_first = '{$area['area_first']}'
                and area_second = '{$area['area_second']}'
                and area_third = '')
                or (area_first = '{$area['area_first']}'
                and area_second = ''
                and area_third = ''))
                and (agent_level = '1'
                and status = '1')";
        $data = $this->db->fetchAll($sql);
        return $data;
    }

    /**
     * ps ：获取同一地区的所有代理商
     * Time：2015/11/25 10:28:05
     * @author liuxin
     * @param array() 代理商id
     * @return 同一地区一级代理商id,username,name
    */
    public function getSameareaAll($agent_id){
        $agent = app::get('sysagent')->model('agents');
        $area = $agent->getRow('area_first,area_second,area_third',array('agent_id'=>$agent_id));
        $sql = "select agent_id,username,name
                from sysagent_agents
                where ((area_first = '{$area['area_first']}'
                and area_second = '{$area['area_second']}'
                and area_third = '{$area['area_third']}')
                or (area_first = '{$area['area_first']}'
                and area_second = '{$area['area_second']}'
                and area_third = '')
                or (area_first = '{$area['area_first']}'
                and area_second = ''
                and area_third = ''))
                and status = '1'";
        $data = $this->db->fetchAll($sql);
        return $data;
    }
    /**
     * ps ：根据地区名称获取地区的ID
     * Time：2015/11/23 16:14:10
     * @author liuxin
     * @param array('0'=>$firstName,'1'=>$secondName,'2'=>thirdName)
     * @return array 对应的id和value
    */
    public function getAreaNameByName($area){
        $areaMap = area::getMap();
        foreach ($areaMap as $k => $v) {
            if($v['value'] == $area['0']){
                $tempdata = $v;
                $data[] = array('id'=>$v['id'],'value'=>$v['value']);
                break;
            }
        }
        if($area['1'] != null){
            foreach ($tempdata['children'] as $k => $v) {
                if($v['value'] == $area['1']){
                    $row = $v;
                    $data[] = array('id'=>$v['id'],'value'=>$v['value']);
                }
            }
        }

        if($area['2'] != null){
            foreach ($row['children'] as $k => $v) {
                if($v['value'] == $area['2']){
                    $res = $v;
                    $data[] = array('id'=>$v['id'],'value'=>$v['value']);
                }
            }
        }

        return $data;
    }

    /**
     * ps ：审核、停用、启用发短信通用方法
     * Time：2015/11/27 13:32:31
     * @author liuxin
     * @param 代理商id 代理商状态
     * @return bool
    */
    public function sendVettedMessage($agent_id,$status){
        // dump($agent_id);die;
        $model_agent = app::get('sysagent')->model('agents');
        $flag = app::get('sysagent')->getConf('sysagent_setting.agent_auditing');
        if($flag){
            $agent = $model_agent->getRow('*',array('agent_id'=>$agent_id));
            $sendto = $agent['mobile'];
            $username = $agent['username'];
            switch ($status) {
                case 'successful':
                    $content = "您的代理申请已审核通过,用户名为{$username},点击链接下载app、http://www.huanledui.cn/index.php/app";
                    break;
                case 'failing':
                    $content = "很遗憾,您的代理申请未审核通过,点击链接下载app并查看详细、http://www.huanledui.cn/index.php/app";
                    break;
                case 'lock':
                    $content = "您在欢乐兑的代理资格已被停用，点击链接下载app并查看详细、http://www.huanledui.cn/index.php/app，如有疑问，请咨询上级代理或平台方";
                    break;
                case 'restart':
                    $content = "您在欢乐兑的代理资格已恢复正常,点击链接下载app并查看详细、http://www.huanledui.cn/index.php/app";
                    break;
                case 'back':
                    $content = "您申请欢乐兑的代理商信息填写的不够完整，请完善。点击链接下载app并查看详细、http://www.huanledui.cn/index.php/app";
                    break;
            }
            $this->sendSms($sendto,$content);
        }
        return true;
    }
    /**
     * ps ：代理商发展子代理商发送短信
     * Time：2015/12/03 09:46:48
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    public function sendSubMessage($params){
        $model_agent = app::get('sysagent')->model('agents');
        $agent = $model_agent->getRow('*',array('agent_id'=>$params['agent_id']));
        $username = $agent['name']!='' ? $agent['name'] : $agent['username'];
        $level=intval($agent['agent_level']) + 1;
        $name=$params['name'];
        $sendto=$params['mobile'];
        $content = "{$name}您好，{$username}诚邀您成为欢乐兑{$level}级代理商,点击链接下载app并申请入驻。推荐ID：{$agent['username']}；http://www.huanledui.cn/index.php/app";
        $this->sendSms($sendto,$content);
    }
    /**
     * ps ：子代理商或子代理发展会员发送短信通知
     * Time：2015/12/07 13:41:32
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    public function sendMemmberMessage($params){
        $name=$params['account'];
        $password=$params['password'];
        $sendto=$params['mobile'];

        $content = "{$name}您好，您已经是欢乐兑的会员,初始密码为{$password},官方网站http://www.huanledui.cn";
        $this->sendSms($sendto,$content);
    }
    /**
     * ps ：平台添加积分发送短信
     * Time：2015/11/27 14:11:01
     * @author liuxin
    */
    public function sendPointSms($parent_name=null,$agent_id,$point,$type=null){
        $model_agent = app::get('sysagent')->model('agents');
        $flag = app::get('sysagent')->getConf('sysagent_setting.agent_arrival');
        if($flag){
            if ($type=='member') {
                $agent = app::get('sysuser')->model('account')->getRow('*',array('user_id'=>$agent_id));
                $_msg = "请登录http://www.huanledui.cn或关注官方微信查看";
            }else{
                $agent = $model_agent->getRow('*',array('agent_id'=>$agent_id));
                $_msg = "登陆app可以查看账单";
            }
            $sendto = $agent['mobile'];
            $time = date("Y-m-d H:i:s");
            if($point>0){
                $content = "{$parent_name}于{$time}为您添加了{$point}点积分，".$_msg;
            }
            else{
                $point = abs($point);
                $content = "{$parent_name}于{$time}为您扣除了{$point}点积分，".$_msg;
            }
            $this->sendSms($sendto,$content);
        }
        return true;
    }
    /**
    * ps ：添加子账号发送短信
    * Time：2016/01/29 12:53:03
    * @author jiang
    */
    public function sendAccountMessage($params,$name){
        $content = "{$params['name']}您好，{$name}诚邀您成为欢乐兑子账号,点击链接下载app并登录。用户名：{$params['login_account']} 密码：{$params['pass']}, http://www.huanledui.cn/index.php/app";
        $sendto = $params['mobile'];
        $this->sendSms($sendto,$content);
    }   

    /**
     * ps ：重置密码发送短信
     * Time：2015/12/11 11:20:54
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
        //短信发送
    public function send_sms($mobile,$type)
    {
        $this->ttl = 60;
        //暂时先隐藏掉  方便测试
        $vcodeData = userVcode::checkVcode($mobile,$type);
        $vcode = $this->randomkeys(6);
        $vcodeData['account'] = $mobile;
        $vcodeData['vcode'] = $vcode;
        $vcodeData['count']  += 1;
        $vcodeData['createtime'] = date('Ymd');
        $vcodeData['lastmodify'] = time();
        $data['vcode'] = $vcode;
        $key = userVcode::getVcodeKey($mobile,$type);
        $sendto=$mobile;
        $content = "您的验证码>：{$vcode}请在页面中输入完成验证，不要把此验证码泄露给任何人，如非本人操作，请忽略此信息";
        $this->sendSms($sendto,$content);
        cache::store('vcode')->put($key, $vcodeData, $this->ttl);

        return $vcode;//直接返回验证码 方便测试
    }

    //随机取6位字符数
    public function randomkeys($length)
    {
        $key = '';
        $pattern = '1234567890';    //字符池
        for($i=0;$i<$length;$i++)
        {
            $key .= $pattern{mt_rand(0,9)};    //生成php随机数
        }
        return $key;
    }
    /**
     * ps ：发送短信
     * Time：2015/11/27 15:06:15
     * @author liuxin
    */
    function sendSms($sendto,$content){
        $type = array(
            'sendType' => 'notice',
            'use_reply' => false
        );
        if(!kernel::single('system_messenger_sms')->send($sendto,'',$content,$type)){
            throw new \LogicException(app::get('sysagent')->_('短信发送失败!'));
        }
    }
    /**
     * ps ：手机号 验证
     * Time：2016年1月29日 10:22:24
     * @author shen
    */
    function isMobile($mobile) {
        if (!is_numeric($mobile)) {
            return false;
        }
        if(!preg_match("/^1[34578]\d{9}$/", $mobile))
        {
            return $this->app->_('手机号码格式不正确');
            break;
        }
        return true;
    }
}

