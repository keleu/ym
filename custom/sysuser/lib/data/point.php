<?php
class sysuser_data_point{
    public function update($params)
    {
        if(!$params['money_integral']) return true;

        $objMdlUserPoint = app::get('sysuser')->model('user_points');
        $objMdlUserPointLog = app::get('sysuser')->model('user_pointlog');
        $objMdlUser = app::get('sysuser')->model('user');
        $point = $objMdlUserPoint->getRow('point_count,expired_point',array('user_id'=>$params['user_id']));
        if($params['behavior']=='订单支付'||$params['behavior_type']=='consume'){//付款
            $_money=$params['money_integral']*-1;
            $params['behavior_type']='consume';
        }else{//存款
            $_money=$params['money_integral'];
            $params['behavior_type']='obtain';
        }
        $_money=$point['point_count']+$_money;

        if($_money<0){
            throw new Exception('积分余额不足！');
        }
        $dateTime=time();
        $pointDate=array(
           'user_id'=>$params['user_id'],
           'point_count'=>$_money,
           'modified_time'=>$dateTime
        );
        $pointLogDate = array(
            'user_id' => $params['user_id'],
            'modified_time' => $dateTime,
            'behavior' => $params['behavior'],
            'point' => $params['money_integral'],
            'behavior_type' => $params['behavior_type'],
            'remark' => '订单：'.$params['tids'],
            'operator' => $_SESSION['account']['shopadmin']['id']
        );
        $userDate=array(
            'user_id' => $params['user_id'],
             'point' => $_money
        );
        $objMdlUserPoint->save($pointDate);
        $objMdlUserPointLog->save($pointLogDate);
        $objMdlUser->save($userDate);
    }
}
