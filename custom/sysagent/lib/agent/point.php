<?php
/*********************************************************************\
*  Copyright (c) 1998-2013, TH. All Rights Reserved.
*  Author :liuxin
*  FName  :point.php
*  Time   :2014/08/30 09:18:48
*  Remark :代理商积分处理
\*********************************************************************/
class sysagent_agent_point{

    public function __construct()
    {
        $this->app = app::get('sysagent');
        $this->model_agent = $this->app->model('agents');
        $this->db = db::connection();
    }

    /**
     * ps ：验证折扣率是否正确
     * Time：2015/12/10 15:06:41
     * @author liuxin
     * @param int float
     * @return bool
    */
    public function checkDiscount($parent_id,$discount,$agent_id){
        //先判断有没有设置积分折扣率  如果没有则去父代理商中取默认折扣率
        $point=app::get('sysagent')->model('agents')->getRow('discount',array('agent_id'=>$agent_id))['discount'];
        if(!$point||$point<0.01){
            if($parent_id == 0){
            $agent_info['discount'] = app::get('sysagent')->getConf('sysagent_setting.agent_ratio')?app::get('sysagent')->getConf('sysagent_setting.agent_ratio'):'0.1';
            }else{
                $agent_info = $this->model_agent->getRow('discount',array('agent_id'=>$agent_id));
            }
        }else{
            $agent_info['discount']=$point;
        }
        if($agent_info['discount'] != $discount){
            return false;
        }
        return true;
    }

    /**
     * ps ：验证是否跨级交易
     * Time：2015/12/10 15:07:14
     * @author liuxin
     * @param int
     * @return bool
    */
    public function checkParent($agent_id,$parent_id){
        $agent_info = $this->model_agent->getList('agent_id',array('agent_id'=>$agent_id,'parent_id'=>$parent_id));
        if(count($agent_info) == 0){
            return false;
        }
        return true;
    }

    public function doConfirm($settlementNo, $status)
    {
        if($status=='2')
        {
            $status = '2';
        }
        else
        {
            return fase;
        }
        return app::get('sysagent')->model('settlement')->update(array('settlement_status'=>$status),array('settlement_no'=>$settlementNo));
    }

}