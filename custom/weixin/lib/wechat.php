<?php
/*********************************************************************\
*  Copyright (c) 2007-2015, TH. All Rights Reserved.
*  Author :li
*  FName  :getdata.php
*  Time   :2015/11/26 13:32:55
*  Remark :微信首页入口
\*********************************************************************/
class weixin_wechat extends weixin_base{

    /**
     * 初始化界面
     * Time：2015/12/29 14:04:48
     * @author li
    */
    function index(){
        $this->wechat_auth();

        //转向wap
        $this->login_auto();

        //如果有其他跳转，支持跳转
        $url = '';
        if($_GET['wap']['url'] != ''){
            $url = $_GET['wap']['url'];
        }
        header('Location: '. url::route('topm').$url);

    }

    //自动登录
    function login_auto(){
        if(!$_SESSION['wxweb']['openId'])return false;

        $_model = app::get('sysuser')->model('user');
        $row = $_model->getRow('user_id', ['opend_id'=>$_SESSION['wxweb']['openId']]);
        // dump($_SESSION);exit;
        if($row['user_id']!=''){
            kernel::single('pam_auth_user')->login($row['user_id']);
        }
        return true;
    }

    //绑定：就是登录
    function login(){
        $this->wechat_auth();
        // dump($_SESSION);exit;
        //转向wap
        header('Location:'. url::route('topm').'/passport-signin.html?state_wx='.$_REQUEST['state']);
    }

    //处理微信端打开网页和网页授权问题
    function wechat_auth(){
        $_from = $this->from_weixin();

        // dump($_REQUEST['code']);exit;
        //如果不是微信打开，不能使用
        if($_from == false){
            echo view::make('weixin/wechat_from.html', array());
            die();
        }else{
            //获取网页授权的access_token
            $access_token = $this->get_access_token_auth($_REQUEST['code']);
            // dump($_SESSION);exit;
            return $access_token;
        }
    }

    /**
     * 微信注册，公用wap端注册的代码
     * Time：2016/05/13 17:48:20
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    function registered(){
        // dump($_REQUEST);exit;
        $this->wechat_auth();

        //转向wap
        header('Location: '. url::route('topm').'/passport-signup.html');
    }

    /**
     * 微信自动注册，通过openId检测是否已经注册
     * 用户名密码随机：会发送微信消息提示用户名和密码
     * Time：2016/05/13 17:46:45
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    function registered_auto(){
        // dump($_REQUEST);
        $this->wechat_auth();

        //开始注册
        //如果没有获取到微信的openId，直接返回错误信息
        if(!$_SESSION['wxweb']['openId']){
            echo "微信身份验证失败，请重新打开该功能";
        }else{
            //检查是否已经注册成功
            $_model = app::get('sysuser')->model('user');
            $row = $_model->getRow('user_id', ['opend_id'=>$_SESSION['wxweb']['openId']]);
            if($row['user_id']!=''){
                $this->login_auto();
                header('Location: '. url::route('topm').$url);
                exit;
            }
            //基础信息获取
            $openId = $_SESSION['wxweb']['openId'];

            //获取微信用户的基础信息
            $info = $this->get_user_detail($openId);
            // dump($info);
            $sex = 2;
            if($info['sex']==1)$sex=1;
            elseif($info['sex']==2)$sex=0;

            //随机创建密码
            $pass = self::create_name(2).'123456';
            $userInfo = array(
                'opend_id'=>$info['openid'],
                // 'nickname'=>$info['nickname'],
                'sex'=>$sex,
                'password'=>$pass,
                'pwd_confirm'=>$pass,
            );
            // dump($userInfo);exit;

            //开始保存会员
            while ( $userInfo['account']=='' ) {
                $len_max = 10;//当获取10次还是名字不通过的时候，就直接增加长度
                $current_cnt = 1;
                $_rand = rand(0,9999);
                $name_len = 4;//默认名字的长度
                $name = self::create_name($name_len);
                $account = $name.$_rand;

                //验证名字是否已经被实用哦
                $isCreate = kernel::single('sysuser_passport')->checkSignupAccount_info(trim($account),'login_account');
                if($isCreate['success']){
                    $userInfo['account'] = $account;
                }

                //当前创建名字的次数递增
                $current_cnt++;

                //如果当前登录用户名创建次数超过设置的次数还没有通过，则字符串重新生成
                if($current_cnt > $len_max){
                    $current_cnt = 1;
                    $name_len++;
                }
            }

            //开始注册用户信息
            // dump($userInfo);exit;
            $userid = kernel::single('topc_ctl_passport')->create_wx($userInfo);

            if($userid){
                $this->sendtextmsg('用户名：'.$userInfo['account'].'密码：'.$userInfo['password'],$userInfo['opend_id']);
                //自动登录
                $this->login_auto();
                header('Location: '. url::route('topm').$url);
            }else{
                echo "注册失败，请重新注册";
                $this->sendtextmsg('注册失败，请重新注册',$userInfo['opend_id']);
            }
        }
    }

    /**
     * 创建用户名
     * Time：2016/05/13 17:48:59
     * @author li
     * @param length int 需要创建用户名的长度
     * @return 返回值类型
    */
    function create_name($length = 4){
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $str = "";
        for ($i = 0; $i < $length; $i++) {
          $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //积分查询界面跳转
    function point_list(){
        $_GET['wap']['url'] = '/mypoint.html';
        $this->index();
    }

    //我的二维码
    function my_qrcode(){
        $_GET['wap']['url'] = '/member-qrcode.html';
        $this->index();
    }

    //我的付款码
    function pay_qrcode(){
        $_GET['wap']['url'] = '/member-paymentQrcode.html';
        $this->index();
    }

    /**
     * 初始化配置
     * 包括菜单的初始化
     * Time：2015/12/29 16:13:31
     * @author li
    */
    public function init($author){
        //判断操作
        if($_GET['huanledui_wechat'] != 'huanledui_wechat'){
            echo "你好，你访问的地址无法正常运行！";exit;
        }

        //获取基础地址
        $_url = url::route('weixin');

        //base
        $_base_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appID."&redirect_uri=%s&response_type=code&scope=snsapi_base&state=%s#wechat_redirect";

        //授权登录
        $_login = urlencode($_url.'/login.html');
        $url_login = sprintf($_base_url,$_login,'weixin_bangding');

        //授权注册
        $_registered = urlencode($_url.'/registered.html');
        $url_registered = sprintf($_base_url,$_registered,'registered_weixin');

        //授权注册 自动注册
        $_registered = urlencode($_url.'/registered_auto.html');
        $url_registered_auto = sprintf($_base_url,$_registered,'registered_auto_weixin');

        //商城首页
        $_index = sprintf($_base_url,$_url,'index_wx');

        //我的二维码
        $myqrcode = urlencode($_url.'/myQrcode.html');
        $my_qrcode = sprintf($_base_url,$myqrcode,'myQrcode_wx');

        //付款吗
        $payQrcode = urlencode($_url.'/payQrcode.html');
        $pay_qrcode = sprintf($_base_url,$payQrcode,'payQrcode_wx');

        if($_GET['see'] == 'see'){
            echo $_index;
            echo $url_registered;
            echo $url_registered_auto;exit;
        }

        // 定义菜单信息
        $menu = [
            'button'=>[
                [
                    'name'=>'我的服务',
                    'sub_button'=>[
                        [
                            'type'=>'click',
                            'name'=>'积分查询',
                            'key'=>'jf_current_balance_1',
                        ],
                        [
                            'type'=>'view',
                            'name'=>'我的二维码',
                            'url'=>$my_qrcode,
                        ],
                        [
                            'type'=>'view',
                            'name'=>'付款码/门店使用',
                            'url'=>$pay_qrcode,
                        ]
                    ]
                ],
                [
                    'name'=>'帐号管理',
                    'sub_button'=>[
                        [
                            'type'=>'view',
                            'name'=>'微信绑定',
                            'url'=>$url_login,
                        ],
                        [
                            'type'=>'view',
                            'name'=>'手动注册',
                            'url'=>$url_registered,
                        ],
                        [
                            'type'=>'view',
                            'name'=>'自动注册',
                            'url'=>$url_registered_auto,
                        ]
                    ]
                ],
                [
                    'type'=>'view',
                    'name'=>'商城入口',
                    'url'=>$_index,
                ]
            ]
        ];

        $access_token = $this->get_access_token();

        $url_target = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        // echo json_encode($menu,JSON_UNESCAPED_UNICODE);exit;
        $result = $this->http_request($url_target ,json_encode($menu,JSON_UNESCAPED_UNICODE),10,false);

        echo $result;
    }




    /**
     * ps ：再使用全局ACCESS_TOKEN获取OpenID的详细信息
     * Time：2015/12/18 12:32:56
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    function get_user_detail($openId){
        $access_token = $this->get_access_token();
        //获取access_token
        $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openId.'&lang=zh_CN';
        // echo $url;exit;

        $userinfo = $this->http_request($url);

        return $userinfo;
    }
}
