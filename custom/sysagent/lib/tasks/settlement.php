<?php
class sysagent_tasks_settlement extends base_task_abstract implements base_interface_task{
    // 每个队列执行100条订单信息
    var $limit = 100;
    public function exec($params=null){
        $filter = array(
            'pay_time|than'=>strtotime(date('Y-m-01 00:00:00', strtotime('-1 month'))),
            'pay_time|lthan'=>strtotime(date('Y-m-t  23:59:59', strtotime('-1 month'))),
        );
        //测试代码
        // $filter = array(
        //     'pay_time|than'=>strtotime(date('Y-m-01 00:00:00')),
        //     'pay_time|lthan'=>strtotime(date('Y-m-t  23:59:59')),
        // );
        $objLibMath = kernel::single('ectools_math');
        $objMdlAgent = app::get('sysagent')->model('agents');
        $objMdlSettlement = app::get('sysagent')->model('settlement');
        $objMdlSettlementDetail = app::get('sysagent')->model('settlement_detail');
        $agentids = $objMdlAgent->getList('agent_id');
        $offset = 0;

        //获取分页之后的代理商id
        while ($listFlag = $this->get_agent_ids($limit_agent_ids,$offset)) {
            //对分页得到的代理商id进行处理
            foreach ($limit_agent_ids as $v) {
                $filter['agent_seller'] = $v;
                if($settlementList =  $objMdlSettlementDetail->getList('*',$filter)){
                    $tradecount = 0;
                    $settle=array();
                    $settle['settlement_no'] = date('ym').str_pad($v,6,'0',STR_PAD_LEFT);
                    $settle['agent_seller'] = $v;

                    $item_fee_point=array();
                    $item_fee_amount=array();
                    foreach($settlementList as $detail){
                        $tradecount += 1;
                        $item_fee_point[] = $detail['settlement_fee_point'];
                        $item_fee_amount[] = $detail['settlement_fee_amount'];
                    }
                    $settle['settlement_fee_point'] = $objLibMath->number_plus($item_fee_point);
                    $settle['settlement_fee_amount'] = $objLibMath->number_plus($item_fee_amount);
                    $settle['settlement_status'] = '1';
                    $settle['account_start_time'] = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')));
                    $settle['account_end_time'] = strtotime(date('Y-m-t  23:59:59', strtotime('-1 month')));
                    $settle['settlement_time'] = time();
                    $settle['tradecount'] = $tradecount;
                }
                else{
                    $tradecount = 0;
                    $settle['settlement_no'] = date('ym').str_pad($v,5,'0',STR_PAD_LEFT);
                    $settle['agent_seller'] = $v;
                    $settle['settlement_fee_point'] = 0;
                    $settle['settlement_fee_amount'] = 0;
                    $settle['settlement_status'] = '1';
                    $settle['account_start_time'] = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')));
                    $settle['account_end_time'] = strtotime(date('Y-m-t  23:59:59', strtotime('-1 month')));
                    $settle['settlement_time'] = time();
                    $settle['tradecount'] = $tradecount;
                }
                $objMdlSettlement->save($settle);
            }
            $offset ++;
        }
        logger::info("agent_settlement:"."代理商结算汇总");
    }

    /**
     * ps ：分页获取代理商id
     * Time：2015/12/10 18:29:28
     * @author liuxin
    */
    function get_agent_ids(&$limit_agent_ids,$offset){
        $objMdlAgent = app::get('sysagent')->model('agents');
        if(!$new_agent_ids = $objMdlAgent->getList('agent_id',array('1'=>'1'),$this->limit*$offset,$this->limit)){
            return false;
        }

        $limit_agent_ids = array();
        foreach ($new_agent_ids as $v) {
            $limit_agent_ids[] = $v['agent_id'];
        }
        return true;
    }
}