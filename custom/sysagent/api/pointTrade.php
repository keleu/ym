<?php
/*********************************************************************\
*  Copyright (c) 1998-2013, TH. All Rights Reserved.
*  Author :liuxin
*  FName  :pointTrade.php
*  Time   :2014/08/30 09:18:48
*  Remark :代理商购买积分api
\*********************************************************************/
class sysagent_api_pointTrade {
    /**
     * 接口作用说明
     */
    public $apiDescription = '代理商购买积分';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'agent_seller' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'商家id'],
            'agent_buyer' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'商品id'],
            'discount' => ['type' => 'decimal','precision' => 20,'scale' => 2,'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'积分折扣率'],
            'settlement_fee_point' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'购买的积分总额'],
            'settlement_fee_amount' => ['type'=>'int','type' => 'decimal','precision' => 20,'scale' => 2,'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'结算金额'],
        );

        return $return;
    }

    /**
     * ps ：代理商购买积分
     * Time：2015/12/10 14:59:12
     * @author liuxin
     * @param array
     * @return array(state=>fail or success,data=>string)
    */
    public function getPoint($params)
    {
        $app = app::get('sysagent');
        $model_agent = $app->model('agents');
        $model_point = $app->model('agent_points');
        $model_pointlog = $app->model('agent_pointlog');
        $model_detail = $app->model('settlement_detail');
        $lib_point = kernel::single('sysagent_agent_point');
        $username = $model_agent->getRow('username,name',array('agent_id'=>$params['agent_buyer']));
        if($username['name']==''){
            $username=$username['username'];  
        }else{
            $username=$username['name'];  
        }
        $parentname = $model_agent->getRow('username',array('agent_id'=>$params['agent_seller']))['username'];
        $parentname = $parentname?$parentname:'平台方';
        //验证是否跨级交易
        if(!$lib_point->checkParent($params['agent_buyer'],$params['agent_seller'])){
            return array('state'=>'fail','msg'=>"请勿跨级交易");
        }
        //验证折扣率
        if(!$lib_point->checkDiscount($params['agent_seller'],$params['discount'],$params['agent_buyer'])){
            return array('state'=>'fail','msg'=>"无效的积分折扣率");
        }
        //验证积分与金额兑换是否正确
        $temp_discount = round($params['settlement_fee_point'] * $params['discount'],2);
        $params['settlement_fee_amount'] = round($params['settlement_fee_amount'],2);
        if($temp_discount != $params['settlement_fee_amount']){
            return array('state'=>'fail','msg'=>"积分与金额不符");
        }
        //处理父代理商的积分
        if($params['agent_seller']!=0){
            //验证父代理商积分是否够用
            $parent_point = $model_point->getRow('point_count',array('agent_id'=>$params['agent_seller']));
            $parent_newpoint = $parent_point['point_count'] - $params['settlement_fee_point'];
            if($parent_newpoint < 0){
                return array('state'=>'fail','msg'=>"上级代理商没有足够的积分");
            }
            $parent_info = array(
                'agent_id' => $params['agent_seller'],
                'point' => $parent_newpoint
            );
            $parent_points = array(
                'agent_id' => $params['agent_seller'],
                'point_count' => $parent_newpoint,
                'modified_time' => time()
            );
            $parent_pointlog = array(
                'agent_id' => $params['agent_seller'],
                'modified_time' => time(),
                'behavior_type' => 'consume',
                'behavior' => '卖出积分',
                'point' => $params['settlement_fee_point'],
                'operator' => $username,
                'direction' => $username,
                'discount' => $params['discount'],
                'sub_id' => $params['agent_buyer'],
                'role' => '代理商'
            );
        }

        //处理子代理商的积分
        $point = $model_point->getRow('point_count',array('agent_id'=>$params['agent_buyer']));
        $newpoint = $point['point_count'] + $params['settlement_fee_point'];
        $info = array(
            'agent_id' => $params['agent_buyer'],
            'point' => $newpoint
        );
        $points = array(
            'agent_id' => $params['agent_buyer'],
            'point_count' => $newpoint,
            'modified_time' => time()
        );
        $pointlog = array(
            'agent_id' => $params['agent_buyer'],
            'modified_time' => time(),
            'behavior_type' => 'obtain',
            'behavior' => '购买积分',
            'point' => $params['settlement_fee_point'],
            'operator' => $username,
            'direction' => $parentname,
            'discount' => $params['discount'],
            'sub_id' => $params['agent_seller'],
            'role' => '代理商'
        );
        $db = $app->database();
        $db->beginTransaction();
        try{
            $model_agent->save($info);
            $model_point->save($points);
            $model_pointlog->save($pointlog);
            $params['pay_time'] = time();
            $model_detail->save($params);
            if($params['agent_seller']!=0){
                $model_agent->save($parent_info);
                $model_point->save($parent_points);
                $model_pointlog->save($parent_pointlog);
            }
        }
        catch(Exception $e){
            $msg = $e->getMessage();
            $db->rollback();
            return array('state'=>'fail','msg'=>$msg);
        }
        $db->commit();

        return array('state'=>'success');
    }

}