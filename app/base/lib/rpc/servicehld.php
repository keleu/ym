<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_rpc_servicehld{

    private $start_time;
    private $path = array();
    private $finish = false;
    static $node_id;
    static $api_info;
    static public $is_start = false;

    function __construct(){
        if(!kernel::is_online()){
            die('error');
        }

        self::$is_start = true;
    }

    public function process($path){
        $this->process_rpc();
    }

    private function strip_magic_quotes(&$var){
        foreach($var as $k=>$v){
            if(is_array($v)){
                self::strip_magic_quotes($var[$k]);
            }else{
                $var[$k] = stripcslashes($v);
            }
        }
    }

    //获取每个代理商的token
    private function token($params){
        //model
        $_model = app::get("sysagent")->model('account');

        //判断是否已经有令牌，
        //如果有令牌，通过令牌获取token，
        //没有令牌通过agentSecret,agentKey获取
        if($params['accesstoken']!=''){
            $account_id=kernel::single('yytapi_base')->check_accesstoken($params['accesstoken']);
            $agent_info = $_model->getRow('account_id,token',['account_id'=>$account_id]);

        }elseif ($params['agentSecret'] != '' && $params['agentKey'] != '') {
            $agent_info = $_model->getRow('account_id,token',[
                'secret'=>$params['agentSecret'],
                'key'=>$params['agentKey'],
            ]);
        }

        //如果无法获取对应的代理商
        if(!$agent_info['account_id']){
            $msg='agentSecret,agentKey不正确，无法获取相应信息';
            $params['accesstoken']!='' && $msg='会话已过期，agentSecret,agentKey重新获取令牌';
            $this->send_user_error('4004', $msg);
        }

        //判断是否是有效的token信息，如果没有设置则无效
        if(!$agent_info['token']){
            $this->send_user_error('4004', '无有效token，请先平台设置');
        }

        return $agent_info['token'];

    }

    private function gen_sign($params){
        return md5(md5(self::assemble($params)).strtoupper(self::token($params)));
    }

    private function assemble($params)
    {
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? self::assemble($val) : $val);
        }
        return $sign;
    }

    //app_id     String     Y     分配的APP_KEY
    //method     String     Y     api接口名称
    //date     string     Y     时间戳，为datetime格式
    //format     string     Y     响应格式，xml[暂无],json
    //certi_id     int     Y     分配证书ID
    //v     string     Y     API接口版本号
    //sign     string     Y     签名，见生成sign
    private function parse_rpc_request($request){
        // dump($request);exit;
        $sign = $request['sign'];
        unset($request['sign']);
        unset($request['CARTNUMBER']);
        $app_id = $request['app_id'];
        if ($app_id)
            $app_id = substr($app_id, strpos($app_id, '.')+1,strlen($app_id));

        //签名验证
        // $sign_check_old = base_certificate::gen_sign($request);
        $sign_check = self::gen_sign($request);
        // echo $sign_check;exit;

        //测试使用token验证，方便测试
        // $sign_check = self::token();

        if('private' == app::get('system')->getConf('system.matrix.set')){
            $sign_check = kernel::single('system_shopmatrix')->get_sign($request);
        }

        # 2015-12-3 by jeff,如果是登陆换取令牌，不用判断sign
        if($sign != $sign_check){
            $this->send_user_error('4003', 'sign error');
            return false;
        }

        $system_params = array('app_id','method','date','format','certi_id','v','sign','node_id');
        foreach($system_params as $name){
            $call[$name] = $request[$name];
            unset($request[$name]);
        }

        //api version control 20120627 mabaineng
        $system_params_addon = array('from_node_id', 'from_api_v', 'to_node_id', 'to_api_v');
        foreach($system_params_addon as $name){
          if( $request[$name] ) {
            self::$api_info[$name] = $request[$name];
            unset($request[$name]);
          }
        }


        //if method request = 'aaa.bbb.ccc.ddd'
        //then: object_service = api.aaa.bbb.ccc, method=ddd
        //dump($call['method']);exit;
        /*if(isset($call['method']{2})){
            if($p = strrpos($call['method'],'.')){
                $service = substr($call['method'],0,$p);
                self::$api_info['api_name'] = $service;
                // $service = 'api.'.$service;
                $method = substr($call['method'],$p+1);
            }
        }else{
            $this->send_user_error('4001', 'error method');
            return false;
        }*/

        if(isset($call['method'])){
            //获得对应的方法
            $api_module = self::getConf($call['method']);

            if($api_module!=false){
                list($service,$method,$rpc_title) = $api_module;
            }
        }

        if(!$service || !$method){
            $this->send_user_error('4001', 'error method');
            return false;
        }

        if($call['node_id']){
            self::$node_id = $call['node_id'];
        }

        return array($service,$method,$request,$rpc_title);
    }

    private function gen_uniq_process_id(){
        return uniqid();
    }

    //处理对外的api
    private function process_rpc(){

        ignore_user_abort();
        set_time_limit(0);

        $this->process_id = $this->gen_uniq_process_id();
        header('Process-id: '.$this->process_id);
        header('Connection: close');
        flush();

        if(get_magic_quotes_gpc()){
            self::strip_magic_quotes($_REQUEST);
        }

        $this->begin(__FUNCTION__);
        set_error_handler(array(&$this,'error_handle'),E_ERROR);
        set_error_handler(array(&$this,'user_error_handle'),E_USER_ERROR);

        $this->start_time = $_SERVER['REQUEST_TIME']?$_SERVER['REQUEST_TIME']:time();
        list($service,$method,$params,$rpc_title) = $this->parse_rpc_request($_REQUEST);

        $data = array(
            'apilog'=>$_REQUEST['task'],
            'calltime'=>$this->start_time,
            'params'=>$params,
            'title'=>$rpc_title,
            'api_type'=>'response',
            'msg_id'=>$this->process_id,
            'method'=>$service,
            'worker'=>$service.":".$method,
        );
        $obj_rpc_poll = app::get('apiactionlog')->model('apilog');
        // dump($data);exit;

        //检测日志表
        $_api_log_sql = 'SELECT apilog_id FROM ' . $obj_rpc_poll->table_name(1) . ' WHERE apilog=\''.$_REQUEST['task'].'\' AND api_type=\'response\' LIMIT 0,30 LOCK IN SHARE MODE';
        if (!app::get('apiactionlog')->database()->fetchAll($_api_log_sql)) {
            //记录apilog
            $apilog_services = kernel::single('apiactionlog_router_logging');
            $apilog_services->save_log($service,$method,$data);

            //获取方法
            if(isset($service) && isset($method)){
                $object = kernel::single($service);
                $result = $object->$method($params,$this);
                $output = $this->end();
            }else{
                $output = $this->end();
                $msg = '我不存在'.$_REQUEST['method'].'接口';
                $output = app::get('base')->_($msg);
                $status = 'fail';
            }

        }else {
            $output = $this->end();
            $output = app::get('base')->_('该请求已经处理，不能再处理了！');
        }


        $result_json = array(
            'rsp'=> $status ? $status : 'succ',
            'data'=>$result,
            'res'=>strip_tags($output)
        );

        $this->rpc_response_end($result, $this->process_id, $result_json);
        if($_REQUEST['isWeixin']){
            echo $result_json = $output;
            return;
        }
        echo json_encode($result_json);
    }

    private function begin()
    {
        register_shutdown_function(array(&$this, 'shutdown'));
        array_push($this->path,$key);
        @ob_start();
    }//End Function

    private function end($shutdown=false){
        if($this->path){
            $this->finish = true;
            $content = ob_get_contents();
            ob_end_clean();
            $name = array_pop($this->path);
            if(defined('SHOP_DEVELOPER')){
                error_log("\n\n".str_pad(@date(DATE_RFC822).' ',60,'-')."\n".$content
                    ,3,ROOT_DIR.'/data/logs/trace.'.$name.'.log');
            }
            if($shutdown){
                echo json_encode(array(
                    'rsp'=>'fail',
                    'res'=>$content,
                    'data'=>null,
                ));
                exit;
            }
            return $content;
        }
    }

    public function shutdown(){
        $this->end(true);
    }

    public function send_user_error($code, $data)
    {
        $this->end();
        $res = array(
            'rsp'   =>  'fail',
            'res'   =>  $code,
            'data'  =>  $data,
        );
        $this->rpc_response_end($data,$this->process_id, $res);
        echo json_encode($res);
        exit;
    }//End Function

    public function send_user_succ($code, $data) {
        $this->end();
        $res = array(
            'rsp' => 'succ',
            'res' => $code,
            'data' => $data,
        );
        $this->rpc_response_end($data,$this->process_id, $res);
        echo json_encode($res);
        exit;
    }
    private function rpc_response_end($result, $process_id, $result_json)
    {
        if (isset($process_id) && $process_id)
        {
            $connection_aborted = $this->connection_aborted();
            $obj_rpc_poll = app::get('apiactionlog')->model('apilog');
            switch($result_json['rsp']){
            case 'succ':
                $status="success";
                break;
            case 'fail':
                $status="fail";
                break;
            }

            //写日志的功能，日志还没有开始，
            $data=array(
                'status'=>$status,
                'msg'=>$result_json['res'],
                'msg_id'=>$process_id,
                'api_type'=>'response',
                );
            $obj_rpc_poll->save_data($data);

            if($connection_aborted){
                if($_SERVER['HTTP_CALLBACK']){
                    $return = kernel::single('base_httpclient')->get($_SERVER['HTTP_CALLBACK'].'?'.json_encode($result_json));
                    $return = json_decode($return);
                }
            }
        }
    }

    private function connection_aborted(){
        $return = connection_aborted();
        if(!$return){
            if(is_numeric($_SERVER['HTTP_CONNECTION']) && $_SERVER['HTTP_CONNECTION']>0){
                if(time()-$this->start_time>=$_SERVER['HTTP_CONNECTION']){
                    $return = true;
                }
            }
        }
        return $return;
    }

    /**
     * 映射api
     * Time：2015/11/27 16:09:26
     * @author li
     * @param 参数类型
     * @return array
    */
    private function getConf($key){
        if($p = strpos($key,'.')){
            $app_val = substr($key,0,$p);
            $method_val = substr($key,$p+1);

            if(file_exists(ROOT_DIR.'/config/api.php')){
                require(ROOT_DIR.'/config/api.php');
                // dump($apis);exit;
                $uses = $apis[$app_val][$method_val];

                list($service ,$method) = explode('@', $uses['uses']);
                $rpc_title = $uses['title'];
                return array($service ,$method,$rpc_title);
            }
        }else{
            return false;
        }
    }
}
