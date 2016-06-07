<?php
class sysaftersales_api_refunds_restore{

    /**
     * 接口作用说明
     */
    public $apiDescription = '平台对退款申请进行退款处理';

    public function getParams()
    {
        $return['params'] = array(
            'aftersales_bn' => ['type'=>'string','valid'=>'required', 'description'=>'申请售后的编号'],
            'refunds_id' => ['type'=>'string','valid'=>'required', 'description'=>'退款申请单编号'],
            'refundsData' => ['type'=>'json','valid'=>'required', 'description'=>'退款单内容'],
        );
        return $return;
    }

    public function restore($params)
    {

        $filter['refunds_id'] = $params['refunds_id'];
        $objMdlRefunds = app::get('sysaftersales')->model('refunds');
        $refunds = $objMdlRefunds->getRow('status,aftersales_bn,user_id,shop_id,tid,oid',$filter);
        if($refunds['aftersales_bn'] != $params['aftersales_bn'])
        {
            throw new \LogicException(app::get('sysaftersales')->_('数据有误，请重新处理'));
            return false;
        }

        if($refunds['status'] > 0)
        {
            throw new \LogicException(app::get('sysaftersales')->_('当前申请已被处理，不能在处理'));
            return false;
        }


        try{
            $refundsData = json_decode($params['refundsData'],true);
            $refund_fee = $refundsData['money'];
            $refundsData['aftersales_bn'] = $params['aftersales_bn'];
            unset($params['refundsData']);
            app::get('sysaftersales')->rpcCall('refund.create',$refundsData);
            $objMdlRefunds = app::get('sysaftersales')->model('refunds');

            //2015-11-25 by jiang 积分处理
            $refundsData['behavior']='退款';
            $refundsData['tids']=$refundsData['tid'];
            $objRefund = kernel::single('sysuser_data_point');
            $result = $objRefund->update($refundsData);

            //2016-1-31 by jiang 扣除购物获得积分  只有订单完成的才扣除
            $objMdlTrade = app::get('systrade')->model('trade');
            $trade = $objMdlTrade->getRow('status,obtain_point_fee',array('tid'=>$refundsData['tid']));
            if($trade['status']=='TRADE_FINISHED' && $trade['obtain_point_fee']>0){
                $tradeData=$refundsData;
                $tradeData['money_integral'] = $trade['obtain_point_fee'];
                $tradeData['behavior']='退款扣除购物获得积分';
                $tradeData['behavior_type']='consume';
                $result = $objRefund->update($tradeData);
            }


            //2015-11-27 库存处理
            $objMdlOrder = app::get('systrade')->model('order');
            $orders = $objMdlOrder->getList('item_id,sku_id,num',array('tid'=>$refundsData['tid']));
            $store=kernel::single('sysitem_trade_store');
            foreach ($orders as & $v) {
                $v['quantity']=$v['num'];
                $isRecover=$store->recoverItemStore($v);
            }
            

        }
        catch(\LogicException $e)
        {
            throw new \LogicException(app::get('sysaftersales')->_($e->getMessage()));
            return false;
        }

        $db = app::get('sysaftersales')->database();
        $db->beginTransaction();

        try
        {
            $params['status'] ="1";
            $result = $objMdlRefunds->update($params,$filter);
            if(!$result)
            {
                throw new \LogicException(app::get('sysaftersales')->_('退款申请单更新失败'));
            }

            $objMdlAftersales = app::get('sysaftersales')->model('aftersales');
            $aftersales = $objMdlAftersales->getRow('progress,status,tid,oid,user_id,shop_id',array('aftersales_bn'=>$refunds['aftersales_bn']));
            if($aftersales['tid'] != $refunds['tid'] || $aftersales['oid'] != $refunds['oid'] || $aftersales['user_id'] != $refunds['user_id'] || $aftersales['shop_id'] != $refunds['shop_id'])
            {
                throw new \LogicException(app::get('sysaftersales')->_('数据有误，请重新处理'));
            }

            if(in_array($aftersales['progress'],['3','4','6','7']) || in_array($aftersales['status'],['2','3']))
            {
                throw new \LogicException(app::get('sysaftersales')->_('当前处理异常，无法处理'));
            }

            $afterparams['progress'] = '7';
            $afterparams['status'] = '2';
            $afterFilter['aftersales_bn'] = $refunds['aftersales_bn'];
            $result = $objMdlAftersales->update($afterparams,$afterFilter);
            if(!$result)
            {
                throw new \LogicException(app::get('sysaftersales')->_('售后单状态更新失败'));
            }

            try
            {
                $orderparams['oid'] = $refunds['oid'];
                $orderparams['tid'] = $refunds['tid'];
                $orderparams['user_id'] = $refunds['user_id'];
                $orderparams['aftersales_status'] = 'SUCCESS';
                $orderparams['refund_fee'] = $refund_fee;
                app::get('sysaftersales')->rpcCall('order.aftersales.status.update', $orderparams);
            }
            catch(\Exception $e)
            {
                throw new \LogicException(app::get('sysaftersales')->_($e->getMessage()));
            }
            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();
            throw $e;
        }
        return true;
    }
}


