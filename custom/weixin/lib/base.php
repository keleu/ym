<?php
/*********************************************************************\
*  Copyright (c) 2007-2015, TH. All Rights Reserved.
*  Author :li
*  FName  :getdata.php
*  Time   :2015/11/26 13:32:55
*  Remark :获取代理商
\*********************************************************************/
class weixin_base{

    function __construct(){
        // kernel::single('base_session')->start();
        $this->appID= "wx22dac85e3ca93877";
        $this->appsecret= "50af6bc30c224541de19002e0c2e4d42";

        //本地定义的token验证
        $this->token="nLd6ksdrXiNQGyU5";
        $this->messageId = [
            '积分消息'=>'nQWYruIWjLQ6ib-xmdqcCeN02fiowPYtkdss_W9HUhY',
            '积分余额'=>'nQWYruIWjLQ6ib-xmdqcCeN02fiowPYtkdss_W9HUhY'
        ];
    }

    /**
     * weixin验证url
     * Time：2015/12/24 16:56:18
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    public function valid(){
        if (!isset($_GET['echostr'])) {
            $result=$this->getResult();
            echo $result;
        }else{
            $echoStr = $_GET["echostr"];
            if($this->checkSignature()){
                echo $echoStr;
                exit;
            }
        }

    }
    private function checkSignature()
    {
        if (!$this->token) {
            throw new Exception('TOKEN is not defined!');
        }
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array($this->token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }




    /**
     * 访问
     * Time：2015/12/29 15:24:08
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    public function http_request($url ,$data=null ,$timeout=5 ,$decode=true){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ( $ch1, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($ch);
        curl_close($ch);

        $decode && $output = json_decode( $output, true );

        return $output;
    }
    /**
     * ps ：获取access_token
     * Time：2015/12/18 12:32:44
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    function get_access_token(){
        // session_start();
        if(!isset($_SESSION)){
            kernel::single('base_session')->start();
        }

        $_file_session = 'weixin_eqinfo.txt';
        $_txt_session = unserialize(file_get_contents($_file_session));

        // dump($_txt_session);exit;
        if($_txt_session['wx_access_token'] && time() <= $_txt_session['wx_access_token_time']) {
            return $_txt_session['wx_access_token'];
        } else {
            //获取access_token
            $url_get='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appID.'&secret='.$this->appsecret;

            $access = $this->http_request($url_get);

            //放入session
            $_SESSION['wx_access_token'] = $access['access_token'];
            $_SESSION['wx_access_token_time'] = time() + $access['expires_in'];

            //放到缓存中
            file_put_contents($_file_session, serialize(['wx_access_token'=>$_SESSION['wx_access_token'],'wx_access_token_time'=>$_SESSION['wx_access_token_time']]));

            // dump($access);exit;
            return $access['access_token'];
        }
    }

    //获取js需要用到的jsapi_ticket
    function get_jsapi_ticket(){
        // session_start();
        // kernel::single('base_session')->start();
        $access_token = $this->get_access_token();

        if($_SESSION['wx_jsapi_ticket'] && time() <= $_SESSION['wx_jsapi_ticket_time']) {
            return $_SESSION['wx_jsapi_ticket'];
        } else {
            //获取jsapi_ticket
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
            $access = $this->http_request($url);

            //放入session
            $_SESSION['wx_jsapi_ticket'] = $access['ticket'];
            $_SESSION['wx_jsapi_ticket_time'] = time() + $access['expires_in'];

            // dump($access);exit;
            return $access['ticket'];
        }

    }

    /**
     * ps ：接口汇总，统一经过
     * Time：2015/12/28 09:35:18
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
     //回复消息
    public function getResult()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            //定义微信openID
            $MsgType=$postObj->MsgType;
            switch($MsgType){
               case "event":
                $result = $this->receiveEvent($postObj);
               break;
               case "text":
                $result = $this->receiveText($postObj);
               break;
            }
            return $result;
        }else {
            echo "没有接收到任何信息！";
            exit;
        }
    }

    /**
       * ps ：得到是关注的时候才会输出会员信息
       * Time：2015/12/18 12:33:22
       * @author zhangyan
       * @param 参数类型
       * @return 返回值类型
    */
    function receiveEvent($postObj){
        $content = "";
        switch (strtoupper($postObj->Event)){
            case "SUBSCRIBE":
                $content = "欢乐兑等您很久啦/鼓掌\n\n“欢乐兑”是用积分免费兑换的商城/强\n\n在这里还有很多您意想不到的兑换惊喜/>色\n\n好了\n/憨笑/坏笑/跳跳开始免费兑换吧\n\n\n电脑登录www.huanledui.cn";//这里是向关注者发送的提示信息
            break;
            case "VIEW":
               ;
            break;
            case "CLICK":
                switch ($postObj->EventKey)
                    {
                        case "jf_current_balance_1":
                            $content = $this->getPoint($postObj->FromUserName);
                        break;
                    }
                break;
            default:
                break;
        }

        $result = '';
        if($content != ''){
            $result = $this->transmitText($postObj,$content);
        }
        return $result;
    }

    /**
     * ps ：用户发送消息的时候回复消息
     * Time：2015/12/18 12:33:11
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    function receiveText($postObj){
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $msgType = "text";
        //设置关键字
        if(empty( $keyword )){
            $contentStr = "您好，我是小云，很高兴为你服务";
        }else{
            switch ($keyword) {
                case '积分':
                    $contentStr = $this->getPoint($fromUsername);
                break;

                case '积分余额':
                    $contentStr = $this->getPoint($fromUsername);
                break;

                case '余额':
                    $contentStr = $this->getPoint($fromUsername);
                break;

                default:
                    $contentStr = null;
                break;
            }
        }

        //同时返回个人信息
        $result = '';
        if($contentStr != ''){
            $result = $this->transmitText($postObj,$contentStr,$msgType);
        }
        return $result;
    }
    /**
     * ps ：关注的时候，回复关注信息，返回用户信息
     * Time：2015/12/18 12:32:27
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    function transmitText($postObj,$content,$msgType='text'){
        $textTpl = "<xml>
           <ToUserName><![CDATA[%s]]></ToUserName>
           <FromUserName><![CDATA[%s]]></FromUserName>
           <CreateTime>%s</CreateTime>
           <MsgType><![CDATA[%s]]></MsgType>
           <Content><![CDATA[%s]]></Content>
           <FuncFlag>0</FuncFlag>
           </xml>";
        $resultStr= sprintf($textTpl,$postObj->FromUserName,$postObj->ToUserName,time(),$msgType,$content);
        return $resultStr;
    }

    // 判断是否来自微信浏览器
    public function from_weixin() {
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }

    /**
     * ps ：获取积分接口
     * Time：2015/12/24 17:10:50
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    public function getPoint($opendId){
        //按照微信查找对应的用户信息
        // $opendId = $_GET['opendId'];
        $opendId = (string)$opendId;
        if(!$opendId)return '';

        //会员信息
        $_model = app::get('sysuser')->model('user');
        $uesrPoints = $_model->getRow('user_id,point', ['opend_id'=>$opendId]);
        // dump($uesrPoints);exit;

        if ($uesrPoints['user_id'] != '') {
            $userInfo = app::get(pam_auth_user::appId)->rpcCall('user.get.info', ['user_id'=>$uesrPoints['user_id']], 'buyer');

            $_url = url::route('weixin');
            $_point_url = urlencode($_url.'/point.html');
            $_point_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appID.'&redirect_uri='.$_point_url.'&response_type=code&scope=snsapi_base&state=point_wx#wechat_redirect';

            $template=array(
                'touser'=>$opendId,
                'template_id'=>(string)$this->messageId['积分余额'],    //模板的id
                'url'=>$_point_url,
                'topcolor'=>"#FF0000",
                'data'=>array(
                    'keyword1'=>[
                        'value'=>$userInfo['login_account'].' '.$userInfo['username'],
                        'color'=>"#444444"
                    ],
                    'keyword2'=>[
                        'value'=>date('Y-m-d H:i:s'),
                        'color'=>"#444444"
                    ],
                    'first'=>[
                        'value'=>"欢迎您{$userInfo['name']}\n",
                        'color'=>"#999999"
                    ],
                    'remark'=>[
                        'value'=>"\n当前余额：".$uesrPoints['point'].' 积分',
                        'color'=>'#444444'
                    ]/*,
                    'name'=>[
                        'value'=>$userInfo['name'],
                        'color'=>"#444444"
                    ],
                    'point'=>[
                        'value'=>$uesrPoints['point'].' 积分',
                        'color'=>'#444444'
                    ]*/
                )
            );
            // dump($template);exit;
            //
            $result = $this->send_template_message($template);
            /*if(is_array($result)){
                $resultStr = 'msgid:'.$result['msgid'].'errcode'.$result['errcode'].'errmsg:'.$result['errmsg'].'openid:'.$opendId.'userInfo:'.$userInfo['login_account'];
            }else{
                $resultStr = $result;
            }*/
            $resultStr = '';

        }else{
            $resultStr = '小云提示：账号管理 > 绑定微信后才有这个服务哦';
        }

        return $resultStr;
    }

    /**
     * 当积分变动的时候会触发，及时提示信息
     * Time：2016/01/05 13:21:24
     * @author li
    */
    public function jfCound_change($userId , $msg='',$point=0){
        if(!$userId)return false;
        //获取会员信息
        $_model = app::get('sysuser')->model('user');
        $sysuser = $_model->getRow('user_id,point,opend_id,name,username', ['user_id'=>$userId]);
        // dump($sysuser);exit;
        $point = $point > 0 ? '+'.$point : $point;
        $msg = $msg.'，'.$point.'积分';

        $opendId = (string)$sysuser['opend_id'];
        if(!$opendId)return false;

        //获取会员更多信息
        if ($sysuser['user_id'] != '') {
            // $userInfo = app::get(pam_auth_user::appId)->rpcCall('user.get.info', ['user_id'=>$userId], 'buyer');
            $account = app::get('sysuser')->model('account')->getRow('login_account',array('user_id'=>$userId));

            //需要跳转的url
            $_url = url::route('weixin');
            $_point_url = urlencode($_url.'/point.html');
            $_point_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appID.'&redirect_uri='.$_point_url.'&response_type=code&scope=snsapi_base&state=point_wx#wechat_redirect';

            $template=array(
                'touser'=>$opendId,
                'template_id'=>(string)$this->messageId['积分消息'],    //模板的id
                'url'=>$_point_url,
                'topcolor'=>"#FF0000",
                'data'=>array(
                    /*'login_account'=>[
                        'value'=>$userInfo['login_account'],
                        'color'=>"#444444"
                    ],
                    'username'=>[
                        'value'=>$userInfo['username'],
                        'color'=>"#444444"
                    ],
                    'name'=>[
                        'value'=>$userInfo['name'],
                        'color'=>"#444444"
                    ],
                    'point'=>[
                        'value'=>$sysuser['point'].' 积分',
                        'color'=>'#444444'
                    ],*/
                    'keyword1'=>[
                        'value'=>$account['login_account'].' '.$sysuser['username'],
                        'color'=>"#444444"
                    ],
                    'keyword2'=>[
                        'value'=>date('Y-m-d H:i:s'),
                        'color'=>"#444444"
                    ],
                    'remark'=>[
                        'value'=>"\n当前余额：{$sysuser['point']}积分\n".$msg."\n积分日志中可以查询",
                        'color'=>'#444444'
                    ],
                    'first'=>[
                        'value'=>"{$sysuser['name']}\n您的积分有变动\n",
                        'color'=>'#999999'
                    ],
                )
            );

            //
            // dump($template);exit;
            $result = $this->send_template_message($template);
            $resultStr = '';

        }else{
            return false;
        }

        return $resultStr;
    }

    /**
     * ps ：
     * Time：2015/12/23 13:42:16
     * @author zhangyan
     * @param 参数类型
     * @return 返回值类型
    */
    public function send_template_message($data){
        //加上时间判断
        $access_token = $this->get_access_token();

        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
        $res = $this->http_request($url,json_encode($data));

        return $res;
    }

    /**
     * 在网页端处理事件需要获取user信息
     * 网页上需要微信的授权access_token ，该token 和之前的access_token不同
     * Time：2015/12/31 13:18:06
     * @author li
    */
    function get_access_token_auth($code=''){
        // session_start();
        kernel::single('base_session')->start();
        if($_SESSION['wxweb']['access_token'] && time() <= $_SESSION['wxweb']['access_token_time']) {
            return $_SESSION['wxweb']['access_token'];
        } else {
            //获取access_token
            if($_SESSION['wxweb']['refresh_token']!=''){
                $url_get='https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$this->appID.'&grant_type=refresh_token&refresh_token='.$_SESSION['wxweb']['refresh_token'];
            }elseif($code!=''){
                $url_get='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appID.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code';
            }else{
                return false;
            }

            //获取access_token
            $access = $this->http_request($url_get);

            //放入session
            // echo $url_get;
            // unset($_SESSION['wxweb']);
            $_SESSION['wxweb']['access_token'] = $access['access_token'];
            $_SESSION['wxweb']['openId'] = $access['openid'];
            $_SESSION['wxweb']['refresh_token'] = $access['refresh_token'];
            $_SESSION['wxweb']['access_token_time'] = time() + $access['expires_in'];

            return $access['access_token'];
        }
    }

    /**
     * 发送微信文本消息，在授权页面发送的文本消息，和在公众号发送的微信消息有区别
     * Time：2016/05/13 17:51:50
     * @author li
     * @param 参数类型
     * @return 返回值类型
    */
    function sendtextmsg($msg ,$touser){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;

        $data=array(
            'touser'=>$touser,
            'msgtype'=>'text',
            'text'=>['content'=>$msg],
            // 'content'=>$msg
        );
        // dump($data);
        $res = $this->http_request($url,json_encode($data,JSON_UNESCAPED_UNICODE));
        // dump($res);exit;
    }
}
