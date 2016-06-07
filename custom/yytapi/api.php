<?php
class yytapi_ctl_api extends topc_ctl_member {

    /**
     * ps ：代理商登录
     * Time：2015/11/26 16:16:34
     * @author zhangyan
     * @param 参数类型
     * username       string  Y   登录帐号名
     * password    string  Y   加密过后的s密码
     * @return 返回值类型
    */
    public function signin($param=null)
    {
        return  array(
            'accesstoken' =>'972b22d645ef8c2025499b6dd77596d7',  //string  972b22d645ef8c2025499b6dd77596d7
            'agent_id' =>'sdfdgdf',     //int 10
            'identity' =>'agent',     //int agent/account；agent:代理商，account:子帐号
            'message' =>'succ',      //  string  登录成功
            'status' =>'true',       //string  true/false
        );
    }
    /**
     * ps ：代理商申请入注
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * recommend  string  Y   推荐人
     * username    string  Y   用户名
     * name    string  Y   姓名
     * mobile  string  Y   手机
     * id_card string  Y   身份证号
     * sex string  N   性别
     * area    String  Y   地区
     * password    string  Y   密码
     * @return 返回值类型
    */
    public function agent_applys($param=null){
        //判断参数的合法性，校验参数,暂时先跳过
        return array(
            'message'=>'cc',         //string  入注成功
            'status'=>true           //true/false
        );
    }
    /**
     * ps ：代理商入注时验证
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * username string  N   用户名
     * name    string  N   姓名
     * recommend   string  N   推荐人
     * mobile  string  N   手机
     * id_card string  N   身份证号
     * area    String  N   地区
     * password   string  N   密码
     *      所有参数必填一个参数，可以传递多个参数一起验证
     * @return 返回值类型
    */
    public function verif_agent($param=null){
        //判断参数的合法性，校验参数,暂时先跳过
        return array(
            'message'=>'cc',         //string  验证通过
            'status'=>true           //true/false
        );
    }
    /**
     * ps ：代理商重置密码
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string      Y   帐号登录返回的 accesstoken
     * username    string  Y   用户名
     * mobile  string  Y   手机号
     * id_card string  Y   身份证
     * password    string  Y   售点帐号的密码（加密过后的密码）
     * @return 返回值类型
    */
    public function reset_password($param=null){
        //判断参数的合法性，校验参数,暂时先跳过
        return array(
            'message'=>'cc',         //string  重置成功
            'status'=>true           //true/false
        );
    }
    /**
     * ps ：代理商有推荐人的时候自动取得该代理商的代理地区 
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * recommend    string  Y   推荐人
     * @return 返回值类型
    */
    public function auto_area($param=null){
        //判断参数的合法性，校验参数,暂时先跳过
        return array(
            'area'=>'江苏省',         //string  代理商代理地区
        );
    }
    /**
     * ps ：获取代理商信息（或首页使用）
     * Time：2015/11/26 16:28:35
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string      Y   帐号登录返回的 accesstoken
     * agent_id    Int Y   代理商id
     * @return 返回值类型
    */
    public function agent_detail($param=null){
        //判断参数的合法性，校验参数,暂时先跳过
        return array(
            'agent_id'=>'12',                // int 代理商id
            'name'=>'sdgdf',                 //string  姓名
            'username'=>'fsdgddf',           //String  用户名
            'mobile'=>'15145523456',            //string  手机号码
            'id_card'=>'323544556586787',       //string  身份证号
            'area'=>'江苏省',                  //String  代理区域
            'recommend'=>'',=>array('parent_id'=>'12',  //int 推荐人id
                'recommend_name'=>'zfds',               // string  推荐人姓名
                'recommend_tel'=>'1531232434',  //string  推荐人电话
             ),                             //Array   推荐人
            'point'=>'10',                   //int 积分余额
            'level'=>'二级',                  //int 级别
        );
    }
    /**
     * ps ：代理商积分日志(账单)
     * Time：2015/11/27 09:10:42
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string  Y   售点帐号登录返回的 accesstoken
     * agent_id int Y   代理商id
     * page Int N   页数：默认每页20，如果为空，则全部数据
     * @return 返回值类型
    */
    public function agent_point_log($param=null){
         //判断参数的合法性，校验参数,暂时先跳过
        return array(
           'points'=>array(   // Array   积分日志列表
                'modified_time'=>1456434567,                  //  Int 记录日期（时间戳）
                'point'=>10,                  //  int 积分值
                'point_discount'=>'0.1',                  // String  折扣金额
                'from'=>'张三',                  //  string  积分来源或去向：张三/平台等
                'role'=>'会员',                  // string  角色：会员/代理商/平台
                'kind'=>'dfd',                  //   String  积分日志类型
                'operator'=>'sdgsdhdfg',                  //   string  操作人
            ),
            'count'=> 10,    //int 返回日志总数
            'current_page'=> 1,    // int 当前页数

        );
    }
    /**
     * ps ：代理商积分交易详情
     * Time：2015/11/27 09:26:29
     * @author zhangyan
     * @param 参数类型
     * log_id   int Y   积分日志id
     * accesstoken  string  Y   售点帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function agent_point_detail($param=null){
        return array(
            'modified_time'=>1435256354,              //Int 记录日期（时间戳）
            'point'=>10,              //int 积分值
            'point_discount'=>'0.1',              //   String  折扣金额
            'from'=>'张三',              //string  积分来源或去向：张三/平台等
            'role'=>'平台',              //string  角色
            'remark'=>'dfds',              //   String  备注
            'operator'=>'zfddsg',              // string  操作员
            'mobile'=>'dffh',              //   string  手机号
            'status'=>'sgfdfhg',              //   string  交易状态：交易成功/交易失败
        );
    }
    /**
      * ps 代理商常用配置
      * Time：2015/11/27 09:31:57
      * @author zhangyan
      * @param 参数类型
      * agent_id    int Y   代理商id
      * accesstoken     string      Y   帐号登录返回的 accesstoken
      * @return 返回值类型
     */
    public function agent_config($param=null){
        return array(
            'discount_self'=>'0.1',              //我购买积分的折扣价格
            'discount_sub'=>'10',              //子代理向我购买积分的价格
            'point_discount'=>'0.1',              //   String  折扣金额
        );
    }
    /**
      * ps 代理商常用配置保存代理商常用配置
      * Time：2015/11/27 09:31:57
      * @author zhangyan
      * @param 参数类型
      * agent_id    int Y   代理商id
      * discount    string  Y   折扣率
      * @return 返回值类型
     */
    public function save_agent_config($param=null){
        return array(
            'message'=>'success',              //成功
            'status'=>true,              //true
        );
    }
    /**
      * ps 子代理商,未审核子代理商，子帐号总数量
      * Time：2015/11/27 09:31:57
      * @author zhangyan
      * @param 参数类型
      * agent_id    int Y   代理商id
      * accesstoken string  Y   帐号登录返回的 accesstoken
      * @return 返回值类型
     */
    public function sub_counts($param=null){
        return array(
            'agent_count'=>10,     //  Int 我的子代理商个数
            'audit_count'=>10,     //  int 未审核代理商个数
            'account_count'=>10,     //   int 我的子帐号个数
        );
    }
    /**
     * ps ：我的子代理商列表支持首字母缩写查询
     * Time：2015/11/27 09:42:50
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * accesstoken string  Y   帐号登录返回的 accesstoken
     * status   int N   是否审核，0，未审核，1为审核通过，2，为审核未通过，3为拒绝，参数不填为全部
     * @return 返回值类型
    */
    public function subagent_list($param=null){
        return array(
            'agents'=>array(     // array   子代理商信息
                'name'=>'fsgrrt',      // string  用户名
                'username'=>'fsgrrt',      //   String  姓名
                'id_card'=>'fsgrrt',      //String  身份证号码
                'point'=>'fsgrrt',      //  Int 积分
                'sex'=>'fsgrrt',      //Int 性别
                'mobile'=>'fsgrrt',      // String  手机
                'key'=>'fsgrrt',      //String  首字母关键字
            ) 
        );
    }
    /**
     * ps ：我的子代理商详情
     * Time：2015/11/27 09:46:19
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * accesstoken string  Y   帐号登录返回的 accesstoken
     * sub_id   int Y   子代理商id
     * @return 返回值类型
    */
    public function subagent_detail($param=null){
        return array(
            'sub_id'=>'10dfds',                         //  int 子代理id
            'name'=>'10dfds',                         //string  用户名
            'username'=>'10dfds',                         //String  姓名
            'id_card'=>'10dfds',                         // String  身份证号码
            'point'=>'10dfds',I                        //nt 积分
            'sex'=>'10dfds',                         // Int 性别
            'mobile'=>'10dfds',                         //  String  手机
            'area'=>'10dfds',                         //string  代理区域
            'buy_count'=>'10dfds',s                        //tring  采购总金额
            'agent_count'=>'10dfds',                         // int 附属子代理个数
            'mem_count'=>'10dfds',i                        //nt 子代理的会员个数
            'apply_status'=>'10dfds',       //String  当前的状态，active=>未审核,successful=>审核通过,failing=>审核驳回,lock=>停用,
        );
    }
    /**
     * ps ：审核子代理商
     * Time：2015/11/27 09:49:18
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * accesstoken string  Y   帐号登录返回的 accesstoken
     * sub_id   int Y   子代理商id
     * apply_status string  Y   审核状态
     * @return 返回值类型
    */
    public function save_subagent_status($param=null){
        return array(
            'message'=>'success',              //成功
            'status'=>true,              //true
        );
    }
    /**
     * ps ：我的子代理商所有附属子代理列表支持搜索
     * Time：2015/11/27 09:51:53
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * accesstoken string  Y   帐号登录返回的 accesstoken
     * status   int N   是否审核，0，未审核，1为审核通过，2，为审核未通过，3为拒绝，参数不填为全部
     * where_key string  N   代理商用户名或者手机搜索。模糊匹配
     * page Int N   页数：默认每页20，如果为空，则全部数据
     * @return 返回值类型
    */
    public function subagent_agent_list($param=null){
        return array(
           'agents'=>array(   // Array    data为代理商id对应的所有子代理商信息
                'name'=>'ddgfdfj',        // string  用户名
                'username'=>'ddgfdfj',        //   String  姓名
                'id_card'=>'ddgfdfj',        //String  身份证号码
                'point'=>'ddgfdfj',        //  Int 积分余额
                'sex'=>'ddgfdfj',        //Int 性别
                'mobile'=>'ddgfdfj',        // String  手机
                'level'=>'ddgfdfj',        //string  等级
                'parent_id'=>'ddgfdfj',        //int 父代理
                'buy_count'=>'ddgfdfj',        //string  采购总额
            ),
            'count'=> 10,    //int 返回日志总数
            'current_page'=> 1,    // int 当前页数
        );
    }
    /**
     * ps ：代理商取消代理资格
     * Time：2015/11/27 10:14:31
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * sub_id   int Y   子代理商id
     * status   int N   1/2：1表示停用（取消资格）代理商，2表示重新启用代理商
     * accesstoken  string  Y   帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function cancel_agent($param=null){
        return array(
            'message'=>'success',              //成功
            'status'=>true,              //true
        );
    }
    /**
     * ps ：保存子代理商
     * Time：2015/11/27 10:14:31
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * uername  string  Y   子代理商代理商用户名
     * mobile   string  Y   子代理商电话
     * @return 返回值类型
    */
    public function save_subagent($param=null){
        return array(
            'message'=>'success',              //成功
            'status'=>true,              //true
        );
    }
    /**
     * ps ：保存子代理商
     * Time：2015/11/27 10:14:31
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * uername  string  Y   子代理商代理商用户名
     * mobile   string  Y   子代理商电话
     * @return 返回值类型
    */
    public function save_subagent($param=null){
        return array(
            'message'=>'success',              //成功
            'status'=>true,              //true
        );
    }
    /**
     * ps ：子代理积分转让
     * Time：2015/11/27 10:14:31
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * sub_id   Int Y   子代理商id
     * point    int Y   转账积分
     * accesstoken  string  Y   帐号登录返回的 accesstoken
     * memo string  N   转账备注
     * @return 返回值类型
    */
    public function save_integral($param=null){
        return array(
            'message'=>'success',              //成功
            'status'=>true,              //true
        );
    }
    /**
     * ps ：删除子代理
     * Time：2015/11/27 10:14:31
     * @author zhangyan
     * @param 参数类型
     * agent_id    int Y   代理商id
     * sub_id   Int Y   子代理商id
     * accesstoken  string  Y   帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function delete_agent($param=null){
        return array(
            'message'=>'success',              //成功
            'status'=>true,              //true
        );
    }


    ///////////////////////////////////////////////////////////////////
    //子帐号相关
    ///////////////////////////////////////////////////////////////////
    /**
     * ps ：我的子账号列表支持排序搜所
     * Time：2015/11/27 10:40:32
     * @author zhangyan
     * @param 参数类型
     * agent_id string  Y   当前代理商id
     * accesstoken  String  Y   帐号登录返回的 accesstoken
     * @return 返回值类型
    */
    public function account_list($param=null){
        return array(
            'accounts'=>array(
                'account_id'=>'10',   // Int 子帐号id
                'account_name'=>'dfhfg',   //   String  子帐号姓名
                'key'=>'a',   //String  首字母，如A/B……
            )   //array   子帐号信息
        );
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
    public function account_detail($param=null){
        return array(
            'account_id'=>'sdgds',            //int 子帐号id
            'account_name'=>'sdgds',            //  String  子帐号姓名
            'sex'=>'sdgds',            //   String  性别
            'id_card'=>'sdgds',            //   string  身份证号码
            'mobile'=>'sdgds',            // String  手机号
            'member_cnt'=>'sdgds',            // int 该子帐号发展的会员个数
        );//array   子帐号信息
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
    public function account_member($param=null){
        return array(
            'members'=>array(
                'member_id'=>'zfsdf',   //  Int 会员id
                'sex'=>'zfsdf',   //String  性别
                'mobile'=>'zfsdf',   // String  手机号
                'point'=>'zfsdf',   //  Int 积分余额
            ),    //array   子帐号的会员信息
            'count'=>'10',  // int 返回子代理商总数
            'current_page'=>'10', //int 当前页数
        );
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
    public function add_account($param=null){
        return array(
            'status'=>'true',  //添加状态，true表示成功,false失败
            'message'=>'1dgfdgd0', //String   消息：帐号添加成功
        );
    }
    /**
     * ps ：删除子帐号
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * account_id   Int Y   子帐号id
     * accesstoken  String  Y   帐号登录返回的 accesst
     * agent_id Int Y   当前登录的代理商id
     * @return 返回值类型
    */
    public function delete_account($param=null){
        return array(
            'status'=>'true',  
            'message'=>'1dgfdgd0', //String   成功
        );
    }


    ///////////////////////////////////////////////////////////////////
    //会员相关
    ///////////////////////////////////////////////////////////////////
    /**
     * ps ：查找会员
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * member_login String  N   会员用户名
     * accesstoken  String  Y   帐号登录返回的 accesst
     * member_mobile    String  N   会员手机号
     * member_login，member_mobile必须填写一个参数，不能都为空
     * @return 返回值类型
    */
    public function seach_member($param=null){
        return array(
            'member_id'=>'dgdsg',       //Int 会员id
            'member_name'=>'dgdsg',       // String  会员真是姓名
            'sex'=>'dgdsg',       // String  性别
            'createtime'=>'dgdsg',     //String  时间戳1428391889
            'member_mobile'=>'dgdsg',     //String  手机号
            'member_point'=>'dgdsg',       //Int 积分余额
        );
    }
    /**
     * ps ：我的会员首页
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * accesstoken  String  Y   帐号登录返回的 accesst
     * agent_id Int Y   代理商id
     * @return 返回值类型
    */
    public function member_index($param=null){
        return array(
            'member_self_cnt'=>'23',    //   Int 自己发展会员的数量
            'member_agents_cnt'=>'23',    // Int 所有子代理发展会员的数量
            'agent_id'=>'23',    //  Int 代理商的id
        );
    }
    /**
     * ps ：会员积分转让
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * accesstoken  String  Y   帐号登录返回的 accesst
     * agent_id Int Y   代理商id
     * member_id    Int Y   会员id
     * point    Int Y   积分：>0 有效
     * memo String  N   备注
     * @return 返回值类型
    */
    public function save_integral($param=null){
        return array(
            'status'=>'true',  
            'message'=>'1dgfdgd0', //String   成功
        );
    }
    /**
     * ps ：添加会员时验证
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * accesstoken  String  Y   帐号登录返回的 accesst
     * member_mobile    String  N   会员手机号
     * member_login String  N   会员用户名
     * member_password  String  N   会员密码
     * member_mobile、member_login, member_password必填一个参数
     * @return 返回值类型
    */
    public function verify_member($param=null){
        return array(
            'status'=>'true',  
            'message'=>'1dgfdgd0', //String   成功
        );
    }
    /**
     * ps ：保存会员
     * Time：2015/11/27 11:00:14
     * @author zhangyan
     * @param 参数类型
     * accesstoken  String  Y   帐号登录返回的 accesst
     * member_mobile    String  Y   会员手机号
     * member_login String  Y   会员用户名
     * member_password  String  N   会员密码
     * @return 返回值类型
    */
    public function save_member($param=null){
        return array(
            'status'=>'true',  
            'message'=>'1dgfdgd0', //String   成功
        );
    }
    /**
     * ps ：我的会员列表，支持模糊搜索
     * Time：2015/11/27 13:02:35
     * @author zhangyan
     * @param 参数类型
     * agent_id Int Y   当前登陆代理商的id
     * where_key    String  N   会员名字或手机号搜索的关键字，模糊匹配
     * accesstoken  String  Y   帐号登录返回的 accesstoken
     * page Int N   页数：默认每页20，如果为空，则全部数据
     * @return 返回值类型
    */
    public function member_list($param=null){
        return array(
            'members'=>array(
                'member_id'=>'dsfds',   //  Int 会员id
                'sex'=>'dsfds',   //String  性别
                'mobile'=>'dsfds',   // String  手机号
                'point'=>'dsfds',   //  Int 积分余额
                'from'=>'dsfds',   //string  账户来源描述信息
            ),    //array   子帐号的会员信息
            'count'=>'10',   //int 返回总数
            'current_page'=>'1',   // int 当前页数
        );
    }
    /**
     * ps ：代理商子代理发展的会员列表，支持模糊搜索
     * Time：2015/11/27 13:02:35
     * @author zhangyan
     * @param 参数类型
     * agent_id Int Y   当前登陆代理商的id
     * where_key    String  N   会员名字或手机号搜索的关键字，模糊匹配
     * accesstoken  String  Y   帐号登录返回的 accesstoken
     * page Int N   页数：默认每页20，如果为空，则全部数据
     * @return 返回值类型
    */
    public function member_all_list($param=null){
        return array(
            'members'=>array(
                'member_id'=>'dsfds',   //  Int 会员id
                'sex'=>'dsfds',   //String  性别
                'mobile'=>'dsfds',   // String  手机号
                'point'=>'dsfds',   //  Int 积分余额
                'from'=>'dsfds',   //string  账户来源描述信息
            ),    //array   代理商所有子代理商的会员
            'count'=>'10',   //int 返回总数
            'current_page'=>'1',   // int 当前页数
        );
    }
    ///////////////////////////////////////////////////////////////////
    //基本配置相关
    ///////////////////////////////////////////////////////////////////
    /**
     * ps ：获取地区
     * Time：2015/11/27 13:02:35
     * @author zhangyan
     * @param 参数类型
     * area_id  int N   地区Id
     * @return 返回值类型
    */
    public function get_area($param=null){
        return array(
            'province'=>'sgds',   // string  省
            'city'=>'sgds',   //string  市
            'area'=>'sgds',   //string  地区
        );
    }
    /**
     * ps ：获取代理商入职协议
     * Time：2015/11/27 13:02:35
     * @author zhangyan
     * @param 参数类型
     * area_id  int N   地区Id
     * @return 返回值类型
    */
    public function get_agent_agreement($param=null){
        return array(
            'message'=>'sgds'   // string  入注协议内容
        );
    }
    /**
     * ps ：修改密码
     * Time：2015/11/27 14:15:51
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string      Y   代理商帐号登录返回的 accesstoken
     * password_old string  Y   代理商帐号的密码（加密过后的密码）
     * password_new string  Y   新密码（加密过后的密码）
     * agent_id Int Y   登录代理商的id
     * @return 返回值类型
    */
    public function change_password($param=null){
        return array(
            'status'=>'true',  
            'message'=>'1dgfdgd0', //String   成功
        );
    }
    /**
     * ps ：修改个人信息
     * Time：2015/11/27 14:15:51
     * @author zhangyan
     * @param 参数类型
     * accesstoken  string      Y   代理商帐号登录返回的 accesstoken
     * mobile   string  N   手机号
     * name string  N   真实姓名
     * sex   Int N   性别
     * agent_id Int Y   登录代理商的id
     * @return 返回值类型
    */
    public function edit_detail($param=null){
        return array(
            'status'=>'true',  
            'message'=>'1dgfdgd0', //String   成功
        );
    }
}
