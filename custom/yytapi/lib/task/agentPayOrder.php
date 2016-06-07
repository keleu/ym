<?php
class yytapi_task_agentPayOrder extends base_task_abstract implements base_interface_task{
    var $plan_exec = [5,10,30,30,30];
    var $limit = 100;
    public function exec($params=null){
        set_time_limit(0);
        $offset = 0;
        //获取分页之后的支付单id
        while ($listFlag = $this->get_payments($limit_payments,$offset)) {
            //把分页得到的支付单id加入相关队列
            $this->updateOrder($limit_payments);
            $offset ++;
        }
        logger::info("agent_expired:"."查询代理商支付单");
        
    }

    /**
    * ps ：分页获取支付单号
    * Time：2016/03/10 11:18:26
    * @author jianghui
    */
        
    function get_payments(&$limit_payments,$offset){
        $model_payment = app::get('ectools')->model('payments_agent');
        $filter['pay_type|noequal'] ='alipay';
        $filter['status'] ='paying';
        $filter['executions|lthan'] =count($this->plan_exec);
        $filter['nextTime|sthan'] = time();
        if(!$limit_payments = $model_payment->getList('payment_id,pay_type,executions',$filter,$this->limit*$offset,$this->limit)){
            return false;
        }
        return true;
    }

    /**
    * ps ：查询支付单并改变状态
    * Time：2016/03/10 11:15:45
    * @author jianghui
    */
     function updateOrder($paymentDate){
        $wxpay = kernel::single('ectools_payment_plugin_appwxpay_server');
        $unpay = kernel::single('ectools_payment_plugin_appunionpay_server');
        $objPayments = app::get('ectools')->model('payments_agent');
        foreach ($paymentDate as $key => & $value) {
            $result = true;
            if($value['pay_type']=='weixin'){
                $result = $wxpay->orderQuery(array(
                  'out_trade_no'=>$value['payment_id']
                ));
            }else{
                $result = $unpay->orderQuery(array(
                  'out_trade_no'=>$value['payment_id']
                ));
            }
            if($value['executions']>=count($this->plan_exec)-1){
                $nextTime='';
            }else{
                $nextTime=time()+$this->plan_exec[$value['executions']]*60;
            }
            $ret=array(
                'payment_id' => $value['payment_id'],
                'errorMemo' => $result,
                'executions' => $value['executions']+1,
                'nextTime' => $nextTime,
                'modified_time' => time(),
            );
            $filter=array('payment_id'=>$value['payment_id']);
            try{
                $is_save = $objPayments->update($ret, $filter);
            }catch( Exception $e){
                
            }
        }
    }   
}