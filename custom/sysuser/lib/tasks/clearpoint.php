<?php
/**
 * ShopEx licence
 * @author liuxin
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
/**
 * ps ：自动清除过期积分类
 * Time：2015/12/02 16:25:52
 * @author liuxin
*/
class sysuser_tasks_clearpoint extends base_task_abstract implements base_interface_task{
    var $limit = 100;
    /**
     * ps ：清除过期积分计划任务执行方法
     * Time：2015/12/03 08:56:31
     * @author liuxin
    */
    public function exec($params=null){
        $model_pointlog = app::get('sysuser')->model('user_pointlog');
        //正式代码，一年清空一次
        $expiredMonth = app::get('sysconf')->getConf('point.expired.month');
        $expiredMonth = $expiredMonth?$expiredMonth:12;
        //判断当前时间是否是设置的积分清空时间
        if(strtotime(date('Y-m-d 23:00:00'))!=strtotime(date('Y-'.$expiredMonth.'-01 23:00:00')."+1 month -1 day")){
            return true;
        }
        $expiredTime = strtotime(date('Y-'.$expiredMonth.'-01 23:59:59')."-1 year +1 month -1 day");
        //end
        //测试用代码，一小时清空一次
        // $expiredTime = strtotime(date('Y-m-d H:i:s')."-1 hour");
        //end

        $offset = 0;

        //先删除一年之前的积分记录,提高后续操作的效率
        $del_filter = array('modified_time|lthan'=>$expiredTime);
        try{
            $model_pointlog->delete($del_filter);
        }
        catch(Exception $e){
            logger::error("delete_user_pointlog:".$e);
        }
        logger::info("point_expired:"."删除修改时间小于".date("Y-m-d H:i:s",$expiredTime)."的积分记录");

        //获取分页之后的会员id
        while ($listFlag = $this->get_user_ids($limit_user_ids,$offset)) {
            //把分页得到的会员id加入相关队列
            $this->clearExpiredPoint($limit_user_ids);
            $offset ++;
        }
        logger::info("point_expired:"."清除会员的过期积分");
    }

    /**
     * ps ：分页获取会员id
     * Time：2015/12/03 09:29:57
     * @author liuxin
     * @param array limit_user_ids 引用获取一页会员号
     * @param int $offset          页数
     * @return bool                [description]
    */
    function get_user_ids(&$limit_user_ids,$offset){
        $model_user = app::get('sysuser')->model('user');
        if(!$new_user_ids = $model_user->getList('user_id',array('1'=>'1'),$this->limit*$offset,$this->limit)){
            return false;
        }

        $limit_user_ids = array();
        foreach ($new_user_ids as $v) {
            $limit_user_ids[] = $v['user_id'];
        }
        return true;
    }

    /**
     * ps ：清除会员过期积分
     * Time：2015/12/03 09:37:44
     * @author liuxin
     * @param array user_ids 分页后的会员id
     * @param array user_ids 分页后的会员id
     * @return 返回值类型
    */
    function clearExpiredPoint($user_ids){
        $model_point = app::get('sysuser')->model('user_points');
        $model_user = app::get('sysuser')->model('user');
        $model_pointlog = app::get('sysuser')->model('user_pointlog');

        //逐条处理会员的积分
        foreach ($user_ids as $v) {
            $point = $model_point->getRow('point_count',array('user_id'=>$v));
            if(!$point||$point['point_count'] == 0){//该会员现有积分为0,则跳过,直接处理下一个会员
                continue;
            }

            $filter = array(
                'behavior_type' => 'obtain',
                'user_id' => $v
            );
            $pointLog = $model_pointlog->getList('point',$filter);
            $sum = 0;

            if(count($pointLog)){//会员有增加积分的记录，现有积分超出增加积分总和的部分需清空
                foreach ($pointLog as $value) {//获得增加积分总和
                    $sum += $value['point'];
                }
                if($sum >= $point['point_count']){//现有积分小于等于增加积分总和,跳过
                    continue;
                }
            }
            $expiredPoint = $point['point_count'] - $sum;

            $db = app::get('sysuser')->database();
            $db->beginTransaction();
            //会员表数据
            $userData = array(
                'user_id' => $v,
                'point' => $sum
            );
            //积分表数据
            $pointData = array(
                'user_id' => $v,
                'point_count' => $sum,
                'modified_time' => time()
            );
            //积分日志表数据
            $pointlogData = array(
                'user_id' => $v,
                'modified_time' => time(),
                'point' => $expiredPoint,
                'behavior_type' => 'consume',
                'behavior' => '清除过期积分',
                'remark' => '系统定时清除过期积分',
                'operator' => 'system',
            );
            try{
                $result = $model_user->save($userData);
                if(!$result){
                    logger::error("point_expired_error:"."更新数据表sysuser_user时出错,error:".$e);
                }

                $result = $model_point->save($pointData);
                if(!$result){
                    logger::error("point_expired_error:"."更新数据表sysuser_user_points时出错,error:".$e);
                }

                $result = $model_pointlog->save($pointlogData);
                if(!$result){
                    logger::error("point_expired_error:"."更新数据表sysuser_user_pointlog时出错,error:".$e);
                }
                $db->commit();
            }catch(Exception $e){
                $db->rollback();
                logger::error("point_expired_error:".$e);
            }
        }
    }
}