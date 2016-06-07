<?php
class sysagent_service_agent_autocomplete{
    function get_data($key,$cols){
        if(!$key) return null;
        $obj_pam = app::get('sysagent')->model('agents');
        $filter['username|head'] = $key;
        $filter['status'] = 1;
        $result = $obj_pam->getList('agent_id,username',$filter);
        foreach((array)($result) as $k=>$v){
            $return[$k]['username'] = $v['username'];
            $return[$k]['agent_id'] = $v['agent_id'];
        } 
        return $return;
    }
}
