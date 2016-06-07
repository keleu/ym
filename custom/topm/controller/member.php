<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
use Endroid\QrCode\QrCode;
class topm_ctl_member extends topm_controller {

    public function __construct(&$app)
    {
        parent::__construct();
        kernel::single('base_session')->start();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        // 检测是否登录
        if( !userAuth::check() )
        {
            redirect::action('topm_ctl_passport@signin')->send();exit;
        }
        $this->limit = 4;
        $this->passport = kernel::single('topm_passport');
    }

    //会员中心
    public function index()
    {
        $userId = userAuth::id();
        //会员信息
        $userInfo = userAuth::getUserInfo();
        $pagedata['userInfo'] = $userInfo;
        $pagedata['account'] = userAuth::getLoginName();

        //商家收藏的总数
        $shopcount = app::get('topm')->rpcCall('user.shopcollect.count',array('user_id'=>$userId));
        $pagedata['shopcount'] = $shopcount;
        //商家收藏的总数
        $itemcount = app::get('topm')->rpcCall('user.itemcollect.count',array('user_id'=>$userId));
        $pagedata['itemcount'] = $itemcount;
        //获取订单各种状态的数量
        $pagedata['nupay'] = app::get('topm')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_PAY'));
        $pagedata['nudelivery'] = app::get('topm')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_SELLER_SEND_GOODS'));
        $pagedata['nuconfirm'] = app::get('topm')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_CONFIRM_GOODS'));
        $pagedata['unrate'] = app::get('topm')->rpcCall('trade.notrate.count',array('user_id'=>$userId));

        $pagedata['title'] = "会员中心";
        $pagedata['payImgsrc'] = app::get('topm')->res_url;
        return $this->page('topm/member/index.html', $pagedata);
    }

    //店铺收藏
    public function shopsCollect()
    {
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 20;
        $params = array(
            'page_no' => $pageSize*($filter['pages']-1),
            'page_size' => $pageSize,
            'fields' =>'*',
            'user_id'=>userAuth::id(),
        );
        $favData = app::get('topm')->rpcCall('user.shopcollect.list',$params);

        $count = $favData['shopcount'];
        $favList = $favData['shopcollect'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topm_ctl_member@shopsCollect',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );
        $pagedata['favshop_info']= $favList;
        $pagedata['count'] = $count;
        $pagedata['action']= 'topm_ctl_member@shopsCollect';
        $pagedata['title'] = "我的店铺收藏";

        return $this->page('topm/member/shopcollect.html', $pagedata);
    }

    //商品收藏
    public function itemsCollect()
    {
        $this->setLayoutFlag('cart');
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 20;
        $params = array(
            'page_no' => $pageSize*($filter['pages']-1),
            'page_size' => $pageSize,
            'fields' =>'*',
            'user_id'=>userAuth::id(),
            'cat_id'=>$filter['cat_id'],
        );
        $favData = app::get('topm')->rpcCall('user.itemcollect.list',$params);
        $count = $favData['itemcount'];
        $favList = $favData['itemcollect'];
        //获取类目
        $catInfo = $this->getCatInfo();
        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topm_ctl_member@itemsCollect',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );
        $pagedata['fav_info']= $favList;
        $pagedata['catInfo']= $catInfo;
        $pagedata['count'] = $count;
        $pagedata['action']= 'topm_ctl_member@itemsCollect';
        $pagedata['title'] = "我的商品收藏";
        if(empty($favList))
        {
            return $this->page('topm/member/emptycollect.html',$pagedata['title']);
        }
        return $this->page('topm/member/itemcollect.html', $pagedata);
    }

    public function getCatInfo()
    {
        $params = array(
            'user_id'=>userAuth::id(),
        );
        $favData = app::get('topm')->rpcCall('user.itemcollect.list',$params);
        $infoList = $favData['itemcollect'];

        if(!$infoList) return "";

        foreach ($infoList as $key => $value)
        {
            $catId[] = $value['cat_id'];
        }

        $catNum = array_count_values($catId);
        $catInfo = app::get('topm')->rpcCall('category.cat.get.info',array('cat_id'=>implode(',',$catId),'fields'=>'cat_id,cat_name'));
        foreach ($catInfo as $k => $val)
        {
            $catInfo[$k]['num'] = $catNum[$k];
            $catName[$k]['cat_id'] = $val['cat_id'];
            $catName[$k]['cat_name'] = $val['cat_name'];
        }
        return $catName;
    }

    /**
     * @brief 会员地址输出
     *
     * @return html
     */
    public function address()
    {
        $this->setLayoutFlag('cart');
        $params['user_id'] = userAuth::id();
        //会员收货地址
        $userAddrList = app::get('topm')->rpcCall('user.address.list',$params,'buyer');
        $count = $userAddrList['count'];
        $userAddrList = $userAddrList['list'];
        foreach ($userAddrList as $key => $value) {
            $userAddrList[$key]['area'] = explode(":",$value['area'])[0];
        }

        $pagedata['title'] = "我的收货地址";
        $pagedata['userAddrList'] = $userAddrList;
        $pagedata['userAddrCount'] = $count;
        if(empty($userAddrList))
        {
            return $this->page('topm/member/addressempty.html', $pagedata);
        }
        return $this->page('topm/member/address.html', $pagedata);
    }
    /**
     * @brief 会员地址保存
     *
     * @return html
     */
    public function saveAddress()
    {
        $postData =utils::_filter_input(input::get());
        $postData['area'] = input::get()['area'][0];
        $postData['user_id'] = userAuth::id();

        if(empty($postData['def_addr']))
        {
            $postData['def_addr']=0;
        }
        $area = app::get('topm')->rpcCall('logistics.area',array('area'=>$postData['area']));
        $validator = validator::make(
            [
             'area' => $area,
             'addr' => $postData['addr'] ,
             'name' => $postData['name'],
             'mobile' => $postData['mobile'],
             'user_id' =>$postData['user_id']
            ],
            [
            'area' => 'required|max:20',
            'addr' => 'required',
            'name' => 'required',
            'mobile' => 'required|mobile',
            'user_id' => 'required'
            ],
            [
             'area' => '地区不存在!',
             'addr' => '会员街道地址必填!',
             'name' => '收货人姓名未填写!',
             'mobile' => '手机号码必填!|手机号码格式不正确!',
             'user_id' => '缺少参数!'
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();

            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }

        $areaId =  str_replace(",","/", $postData['area']);
        $postData['area'] = $area . ':' . $areaId;
        try
        {
            app::get('topm')->rpcCall('user.address.add',$postData);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $url = url::action('topm_ctl_member@address');
        //2016.4.29 by yangjie 添加判断，当修改地址和添加地址时显示正确的提示
        if( $postData['addr_id'] )
        {
            $msg = app::get('topc')->_('修改收货地址成功');
        }
        else
        {
            $msg = app::get('topc')->_('添加成功');
        }
        return $this->splash('success',$url,$msg);

    }
    /**
     * @brief 会员地址编辑
     *
     * @return html
     */
    public function addrUpdate()
    {
        $this->setLayoutFlag('cart');
        if($addrId = input::get('addr_id'))
        {
            $params['addr_id'] = $addrId;
            $params['user_id'] = userAuth::id();
            $addrInfo = app::get('topm')->rpcCall('user.address.info',$params);
            list($regions,$region_id) = explode(':', $addrInfo['area']);
            $addrInfo['area'] = $regions;
            $addrInfo['region_id'] = str_replace('/', ',', $region_id);

            $pagedata['addrInfo'] = $addrInfo;
        }
        $pagedata['addrdetail'] = $addrInfo['area'].'/'.$addrInfo['addr'];
        $pagedata['title'] = "我的收货地址";
        return $this->page('topm/member/upaddress.html', $pagedata);
    }
        /**
     * @brief 设置默认会员地址
     *
     * @return html
     */
    public function ajaxAddrDef()
    {
        $params['addr_id'] = $_POST['addr_id'];
        $params['user_id'] = userAuth::id();

        try
        {
            app::get('topm')->rpcCall('user.address.setDef',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('topm')->_('设置成功');
        return $this->splash('success',null,$msg);

    }
    /**
     * @brief 删除会员地址
     *
     * @return html
     */
    public function ajaxDelAddr()
    {
        $params['addr_id'] = $_POST['addr_id'];
        $params['user_id'] = userAuth::id();

        try
        {
            app::get('topm')->rpcCall('user.address.del',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();

            return $this->splash('error',null,$msg);
        }
        $url = url::action('topm_ctl_member@address');
        $msg = app::get('topm')->_('删除成功');
        return $this->splash('success',$url,$msg);
    }

    /**
     * @brief 会员信息设置
     *
     * @return html
     */
    public function userinfoSet()
    {
        $this->setLayoutFlag('cart');
        $userInfo = userAuth::getUserInfo();

        //获取是否存在openid,存在表示绑定了微信
        $_model = app::get('sysuser')->model('user');
        $opend_id = $_model->getRow('user_id,opend_id', ['user_id'=>$userInfo['userId']]);

        //显示微信信息
        if($opend_id['opend_id']!=''){
            $wechat = kernel::single('weixin_wechat');
            $token = $wechat->get_access_token();
            $wx_user = $wechat->get_user_detail($token ,$opend_id['opend_id']);
            $userInfo['nickname_wx'] = $wx_user['nickname'];
        }
        // dump($wx_user);exit;
        list($regions,$region_id) = explode(':', $userInfo['area']);
        $userInfo['area'] = $regions;
        $userInfo['region_id'] = str_replace('/', ',', $region_id);
        $pagedata['userInfo'] = $userInfo;
        $pagedata['addrdetail'] = $userInfo['area'].'/'.$userInfo['addr'];
        $pagedata['title'] = "会员中心";
        $pagedata['img_src'] = app::get('topm')->res_url;
        return $this->page('topm/member/infoset.html', $pagedata);
    }
    /**
     * @brief 会员信息设置保存
     *
     * @return html
     */

    public function saveInfoSet()
    {
        $userId = userAuth::id();
        $postData = input::get();
        $postData['user']['user_id'] = $userId;
        $area = app::get('topm')->rpcCall('logistics.area',array('area'=>$postData['area'][0]));

        $validator = validator::make(
            ['name' => $postData['user']['name'] ,
             'username' => $postData['user']['username'],
             'user_id' => $postData['user']['user_id'],
             'area' => $area
            ],
            ['name' => 'required|min:4|max:20' ,
            'username' => 'required|max:20',
            'user_id' => 'required',
            'area' => 'required'
            ],
            ['name' => '用户昵称不能为空!|用户昵称最少4个字符!|用户昵最多20个字符!' ,
             'username' => '用户姓名不能为空!|用户姓名过长,请输入20个英文或10个汉字!',
             'user_id' => '您还没有登陆，请先登陆!',
             'area' => '地区不存在!'
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();

            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        $areaId =  str_replace(",","/", $postData['area'][0]);
        $postData['area'] = $area . ':' . $areaId;
        try
        {
            $data = array('user_id'=>$userId,'data'=>json_encode($postData));
            $result = app::get('topm')->rpcCall('user.basics.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $url = url::action('topm_ctl_member@userinfoSet');
        $msg = app::get('topm')->_('修改成功');
        return $this->splash('success',$url,$msg);

    }

    /**
     * @brief 解绑第一步
     *
     * @return html
     */
    /*public function unVerifyOne()
    {
        $postData = utils::_filter_input(input::get());

        $userId = userAuth::id();
        //会员信息
        $userInfo = userAuth::getUserInfo();

        $pagedata['userInfo']= $userInfo;
        $pagedata['verifyType']= $postData['verifyType'];
        $pagedata['type']= $postData['type'];
        $pagedata['title'] = "解绑手机";
        return $this->page('topm/member/unverify.html',$pagedata);

    }*/

    //解绑的验证码检测
    /*public function checkVcode()
    {
        $postData =utils::_filter_input(input::get());
        if(empty($postData['verifycode']) || !base_vcode::verify('topm_unverify', $postData['verifycode']))
        {
            $msg = app::get('topm')->_('验证码填写错误') ;
            return $this->splash('error',null,$msg,true);
        }
        $verifyType = $postData['verifyType'];
        $url = url::action("topm_ctl_member@unVerifyTwo",array('verifyType'=>$verifyType,'op'=>$postData['type']));
        return $this->splash('success',$url,null);

    }*/

    /**
     * @brief 解绑第二步
     *
     * @return html
     */
    public function unVerifyTwo()
    {
        //会员信息
        $userInfo = userAuth::getUserInfo();
        $postdata = input::get();

        $pagedata['userInfo'] = $userInfo;
        $pagedata['op'] = $postdata['op'];

        $pagedata['verifyType']= $postdata['verifyType'];
        if($postdata['verifyType']=='email'&&$userInfo['email_verify'])
        {
            $pagedata['title'] = "解绑邮箱";
            return $this->page('topm/member/unemail.html',$pagedata);

        }
        elseif($postdata['verifyType']=='mobile'&&$userInfo['mobile'])
        {
            $pagedata['title'] = "解绑手机";
            return $this->page('topm/member/unmobile.html',$pagedata);
        }
        else
        {
            $msg = app::get('topm')->_('参数错误');
            return $this->splash('error',$url,$msg);
        }
    }
    //解绑mobile
    public function unVerifyMobile()
    {
        $postData = utils::_filter_input(input::get());
        $sendType = $postData['verifyType'];
        $postData['user_id'] = userAuth::id();
        try
        {
            if(!userVcode::verify($postData['vcode'],$postData['uname'],$postData['type']))
            {
                throw new \LogicException(app::get('topm')->_('验证码错误'));
                return false;
            }

            $data['user_id'] = $postData['user_id'];
            $data['user_name'] = $postData['uname'];
            $data['type'] = $postData['op'];
            app::get('topm')->rpcCall('user.account.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $url = url::action("topm_ctl_member@unVerifyLast",array('sendType'=>$sendType));
        return $this->splash('success',$url,null);
    }

     //绑定邮箱
    public function unVerifyEmail()
    {
        $postData = utils::_filter_input(input::get());
        try
        {
            $userId = userAuth::id();
            if(md5($userId) != $postData['verify'])
            {
                throw new \LogicException(app::get('topm')->_('用户不一致！'));
            }
            if(!userVcode::verify($postData['vcode'],$postData['uname'],$postData['type']))
            {
                throw new \LogicException(app::get('topm')->_('验证码错误'));
            }

            $data['user_id'] = userAuth::id();
            $data['user_name'] = $postData['uname'];
            $data['type'] = 'delete';
            app::get('topm')->rpcCall('user.account.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $pagedata['sendType']= 'email';
        return $this->page('topm/member/unverifylast.html',$pagedata);
    }

    /**
     * @brief 解绑最后一步
     *
     * @return html
     */
    public function unVerifyLast()
    {
        $sendType = input::get();

        $pagedata['sendType']= $sendType['sendType'];
        return $this->page('topm/member/unverifylast.html',$pagedata);
    }

    //安全中心绑定手机页面路由
    public function  verifyRoute()
    {
        $this->setLayoutFlag('cart');
        $postData = utils::_filter_input(input::get());
        $userInfo = userAuth::getUserInfo();
        $pagedata['userInfo'] = $userInfo;
        if($postData['verifyType']=='mobile')
        {
            $pagedata['title'] = '绑定手机号';
            return $this->page('topm/member/mobileverify.html',$pagedata);
        }
        elseif($postData['verifyType']=='email')
        {
            $pagedata['title'] = '绑定邮箱';
            return $this->page('topm/member/emailverify.html',$pagedata);
        }
    }

    /**
     * @brief 安全中心
     *
     * @return html
     */
    public function security()
    {
        $pagedata['next_checkout'] = input::get('next_page', request::server('HTTP_REFERER'));
        $this->setLayoutFlag('cart');
        $pagedata['title'] = "安全中心";
        $userInfo = userAuth::getUserInfo();
        $pagedata['userInfo'] = $userInfo;
        return $this->page('topm/member/security.html',$pagedata);
    }
    /**
     * @brief 绑定第一步
     *
     * @return html
     */
    public function verify()
    {
        $pagedata['next_checkout'] = $_GET['next_checkout'];
        $this->setLayoutFlag('cart');
        $postData = utils::_filter_input(input::get());
        $pagedata['verifyType']= $postData['verifyType'];
        $pagedata['type']= $postData['type'];
        $pagedata['title'] = "验证密码";
        return $this->page('topm/member/setuserinfo.html',$pagedata);
    }
    /**
     * @brief 验证登陆密码
     *
     * @return html
     */
    public function CheckSetInfo()
    {
        $this->setLayoutFlag('cart');
        $userName = userAuth::getLoginName();
        $postData =utils::_filter_input(input::get());
        $validator = validator::make(
            ['password' => $postData['password']],
            ['password' => 'required'],
            ['password' => '密码不能为空!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        $data['password'] = $postData['password'];
        try
        {
            app::get('topm')->rpcCall('user.login.pwd.check',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $verifyType = $postData['verifyType'];
        $type = $postData['type'];
        $url = url::action("topm_ctl_member@setUserInfoOne",array('verifyType'=>$verifyType,'type'=>$type,'next_checkout'=>$postData['next_checkout']));

        return $this->splash('success',$url,null);
    }

    /**
     * @brief 验证第二步
     *
     * @return html
     */
    public function setUserInfoOne()
    {
        $this->setLayoutFlag('cart');
        $userInfo = userAuth::getUserInfo();
        $postdata = input::get();
        $pagedata['type'] = $postdata['type'];
        $pagedata['next_checkout'] = $postdata['next_checkout'];
        if($postdata['type'] && $postdata['type'] = "update" && !$userInfo['login_account'])
        {
            $msg = app::get('topm')->_('您还没有设置用户名，请前往设置用户名!');
            return $this->splash('error',null,$msg);
        }
        $pagedata['userInfo']= $userInfo;
        $pagedata['verifyType']= $postdata['verifyType'];
        if($postdata['verifyType']=='mobile')
        {
            $pagedata['title'] = "绑定手机号";
        }
        if($postdata['verifyType']=='email')
        {
            $pagedata['title'] = "绑定邮箱";
        }
        return $this->page('topm/member/setinfoone.html',$pagedata);

    }


    //绑定mobile
    public function bindEmail()
    {
        $postData = utils::_filter_input(input::get());

        try
        {
            $userId = userAuth::id();
            if(md5($userId) != $postData['verify'])
            {
                throw new \LogicException(app::get('topm')->_('用户不一致！'));
            }
            if(!userVcode::verify($postData['vcode'],$postData['uname'],$postData['type']))
            {
                throw new \LogicException(app::get('topm')->_('验证码错误'));
                return false;
            }
            $data['user_id'] = $userId;
            $data['email'] = $postData['uname'];
            app::get('topm')->rpcCall('user.email.verify',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $pagedata['title'] = "绑定邮箱";
        $pagedata['sendType']= 'email';
        return $this->page('topm/member/setinfotwo.html',$pagedata);
    }

    /**
     * @brief 会员中心安全中心的最后一步
     *
     * @return true or false
     */
    public function saveSetInfo()
    {
        $postData = utils::_filter_input(input::get());
        try
        {
            $sendType = kernel::single('pam_tools')->checkLoginNameType($postData['uname']);
            $postData['user_id'] = userAuth::id();
            if(!userVcode::verify($postData['vcode'],$postData['uname'],$postData['type']))
            {
                throw new \LogicException(app::get('topm')->_('验证码错误'));
                return false;
            }

            $data['user_id'] = $postData['user_id'];
            $data['user_name'] = $postData['uname'];
            app::get('topm')->rpcCall('user.account.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $url = url::action("topm_ctl_member@setUserInfoTwo",array('sendType'=>$sendType,'next_checkout'=>$postData['next_checkout']));
        return $this->splash('success',$url,null);
    }

     /**
     * @brief 验证最后一步
     *
     * @return html
     */
    public function setUserInfoTwo()
    {
        $sendType = input::get();
        $pagedata['sendType']= $sendType['sendType'];
        $pagedata['next_checkout']= $sendType['next_checkout'];
        if($sendType['sendType']=='mobile')
        {
            $pagedata['title'] = "绑定手机号";
        }
        if($sendType['sendType']=='email')
        {
            $pagedata['title'] = "绑定邮箱";
        }
        return $this->page('topm/member/setinfotwo.html',$pagedata);
    }

    /**
     * @brief 会员中心安全中心密码修改
     *
     * @return html
     */
    public function modifyPwd()
    {
        $this->setLayoutFlag('cart');
        $this->setLayoutFlag('cart');
        $pagedata['title'] = "安全中心密码修改";
        return $this->page('topm/member/modifypwd.html',$pagedata);
    }
    /**
     * @brief 会员中心安全中心密码修改保存
     *
     * @return html
     */
    public function saveModifyPwd()
    {
        try{
            $userId = userAuth::id();
            $postData = utils::_filter_input(input::get());

            $validator = validator::make(
                ['oldpassword' => $postData['old_password'] ,'password' => $postData['new_password'] , 'password_confirmation' =>$postData['confirm_password']],
                ['oldpassword' => 'required' ,'password' => 'min:6|max:20|confirmed','password_confirmation' =>'required'],
                ['oldpassword' => '老密码不能为空！' ,'password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!','password_confirmation' =>'确认密码不能为空!']
            );
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();
                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }
            $data = array(
                'new_pwd' => $postData['new_password'],
                'confirm_pwd' => $postData['confirm_password'],
                'old_pwd' => $postData['old_password'],
                'user_id' => $userId,
                'type' => "update",
            );
            app::get('topm')->rpcCall('user.pwd.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $url = url::action("topm_ctl_passport@logout");
        $msg = app::get('topm')->_('修改成功');

        return $this->splash('success',$url,$msg);
    }

    /**
     * @brief 发送短信验证码
     *
     * @return html
     */
    public function sendVcode()
    {
        $postData = utils::_filter_input(input::get());
        if(isset($postData['imagevcode']))
        {
            $valid = validator::make(
                [$postData['imagevcode']],['required']
            );
            if($valid->fails())
            {
                return $this->splash('error',null,"图片验证码不能为空!");
            }
            if(!base_vcode::verify($postData['imagevcodekey'],$postData['imagevcode']))
            {
                return $this->splash('error',null,"图片验证码错误!");
            }

        }
        //echo '<pre>';print_r($postData);exit();
        //判断手机号是否已被注册,2015-12-04,by liuxin
        // $userId = $_SESSION['account']['member']['id'];
        $filter['mobile'] = $postData['uname'];
        // if($userId)
        // {
        //      $filter['user_id|noequal'] = $userId;
        // }
        $objMdlaccount = app::get('sysuser')->model('account');
        $flag = $objMdlaccount->getRow('user_id',$filter);
        if($flag){
            $message = "该手机已被注册，请换一个重试";
            return $this->splash('error',null,$message);
        }

        $accountType = kernel::single('pam_tools')->checkLoginNameType($postData['uname']);
        try
        {
            $this->passport->sendVcode($postData['uname'],$postData['type']);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        if($accountType == "email")
        {
            return $this->splash('success',null,"邮箱验证链接已经发送至邮箱，请登录邮箱验证");
        }
        else
        {
            return $this->splash('success',null,"验证码发送成功");
        }
    }

    /**
     * 户名设置
     */
    public function saveUserAccount()
    {
        $postData = input::get();
        $userId = userAuth::id();
        $url = url::action("topm_ctl_member@userinfoSet");
        try
        {
            $this->__checkAccount($postData['login_account']);
            $data = array(
                'user_name'   => $postData['login_account'],
                'user_id' => $userId,
            );
            app::get('topm')->rpcCall('user.account.update',$data,'buyer');
        }
        catch(\Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        return $this->splash('success',$url,app::get('topm')->_('修改成功'),true);
    }

    private function __checkAccount($username)
    {
        $validator = validator::make(
            ['username' => $username],
            ['username' => 'loginaccount'],
            ['username' => '用户名不能为纯数字或邮箱地址!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                throw new LogicException( $error[0] );
            }
        }
        return true;
    }

    /**
     * ps ：会员信息二维码
     * Time：2015/12/09 14:23:47
     * @author liuxin
    */
    public function getUserQrcode(){
        $userId = userAuth::id();
        $username = app::get('sysuser')->model('account')->getRow('login_account',array('user_id'=>$userId));

        //2016年2月29日 by li 修复bug：手机号注册没有用户名，这里会报错
        if(!$username['login_account']){
            $pagedata['error_msg'] = "还未设置用户名，请先设置用户名";
            return $this->page('topm/member/qrcode/error.html',$pagedata);
        }
        $pagedata['qrCode'] = $this->__qrCode($username['login_account']);
        $pagedata['title'] = "我的二维码";
        return $this->page('topm/member/qrcode/index.html',$pagedata);
    }

    /**
     * ps ：生成二维码
     * Time：2015/12/09 14:24:02
     * @author liuxin
     * @param $data string
     * @return image
    */
    private function __qrCode($data)
    {
        $qrCode = new QrCode();
        return $qrCode
            ->setText($data)
            ->setSize(260)
            ->setPadding(10)
            ->setErrorCorrection(1)
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabelFontSize(16)
            ->getDataUri('png');
    }

    /**
     * ps ：支付码（二维码）
     * Time：2015/12/09 14:24:29
     * @author liuxin
    */
    public function paymentQrcode(){
        $userId = userAuth::id();
        $vcode = $this->create_guid();
        $this->saveVcode($vcode);
        $url = url::action("topshop_ctl_trade_offline@checkVcode",array('userId'=>$userId,'vcode'=>$vcode));
        $pagedata['qrCode'] = $this->__qrCode($url);
        $pagedata['title'] = "我的支付码";
        $pagedata['refresh_src'] = app::get('topm')->res_url;
        return $this->page('topm/member/qrcode/paymentQrcode.html',$pagedata);
    }

    /**
     * ps ：生成随机数(0~9,a~z)
     * Time：2015/12/09 13:38:01
     * @author liuxin
     * @param length int
     * @return string
    */
    private function randomkeys($length)
    {
        $key = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';    //字符池
        for($i=0;$i<$length;$i++)
        {
            $key .= $pattern{mt_rand(0,36)};    //生成php随机数
        }
        return $key;
    }

    /**
     * ps ：生成唯一码
     * Time：2015/12/14 14:21:55
     * @author liuxin
    */
    public function create_guid() {
        if (function_exists ( 'com_create_guid' )) {
            return com_create_guid ();
        } else {
            mt_srand ( ( double ) microtime () * 10000 ); //optional for php 4.2.0 and up.随便数播种，4.2.0以后不需要了。
            $charid = strtoupper ( md5 ( uniqid ( rand (), true ) ) ); //根据当前时间（微秒计）生成唯一id.
            $hyphen = chr ( 45 ); // "-"
            $uuid = '' . //chr(123)// "{"
            substr ( $charid, 0, 8 ) . $hyphen . substr ( $charid, 8, 4 ) . $hyphen . substr ( $charid, 12, 4 ) . $hyphen . substr ( $charid, 16, 4 ) . $hyphen . substr ( $charid, 20, 12 );
            //.chr(125);// "}"
            return $uuid;
        }
    }

    /**
     * ps ：动态刷新支付码
     * Time：2015/12/09 15:30:35
     * @author liuxin
    */
    public function ajaxPayQrcode(){
        $userId = userAuth::id();
        $vcode = $this->create_guid();
        $this->saveVcode($vcode);
        $url = url::action("topshop_ctl_trade_offline@checkVcode",array('userId'=>$userId,'vcode'=>$vcode));
        $data = $this->__qrCode($url);
        return $data;
    }

    /**
     * ps ：将支付码中的验证码信息存入缓存
     * Time：2015/12/09 15:48:36
     * @author liuxin
     * @param string
     * @return bool
    */
    private function saveVcode($vcode){
        $userId = userAuth::id();
        $key = 'VCODE_PAYMENT:'.$userId.'pay';
        $ttl = 3;
        $vcodeData['account'] = $userId;
        $vcodeData['vcode'] = $vcode;
        $vcodeData['count']  += 1;
        $vcodeData['createtime'] = date('Ymd');
        $vcodeData['lastmodify'] = time();

        cache::store('vcode')->put($key, $vcodeData, $ttl);
        //cache::store('vcode')->get($key);获取缓存中的值
        return true;
    }

}


