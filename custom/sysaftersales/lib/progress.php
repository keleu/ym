<?php
/**
 * 售后进度跟踪修改，变化操作类
 */

class sysaftersales_progress {

    public function __construct()
    {
        $this->objMdlAftersales = app::get('sysaftersales')->model('aftersales');
    }

    /**
     * 商家审核消费者提交的售后申请
     *
     * @param string $filter 售后编号
     * @param bool   $result       审核结果
     * @param string $explanation  审核处理说明
     * @param array  $refundsData  如果是审核退款申请，为提交的退款数据
     */
    public function check($filter, $result, $explanation=null, $refundsData=null,$shopId)
    {
        $info = $this->objMdlAftersales->getRow('aftersales_bn,aftersales_type,progress,tid,oid,status,shop_id,user_id',$filter);
        //2016-1-19 by jiang 查找订单明细的总积分
        $objMdOrder = app::get('systrade')->model('order');
        $info['total_integral']=$objMdOrder->getRow('payment_integral',array('oid'=>$info['oid']))['payment_integral'];

        kernel::single('sysaftersales_verify')->checkPermission($info, 'seller',$shopId);

        if( !in_array($info['progress'],['0','2'] ) )
        {
            throw new \LogicException(app::get('sysaftersales')->_('审核的售后编号已处理'));
        }

        $updateData['shop_explanation'] = $explanation;
        $updateData['modified_time'] = time();

        //拒绝售后申请
        if( $result == 'false' )
        {
            $updateData['progress'] = '3';
            $updateData['status'] = '3';
        }
        else
        {
            //同意售后申请
            $updateData['status'] = 1;//处理状态为正在处理
            $updateData['progress'] = '1';

            if( $info['aftersales_type'] == 'ONLY_REFUND'  || ($info['progress'] == '2' && $info['aftersales_type'] == 'REFUND_GOODS'))
            {
                $refundsData['user_id'] =$info['user_id'];
                $refundsData['shop_id'] =$info['shop_id'];
                $refundsData['oid'] =$info['oid'];
                $refundsData['tid'] =$info['tid'];
                $refundsData['total_integral'] =$info['total_integral'];
                if( empty($refundsData['refunds_reason']) )
                {
                    throw new \LogicException('退款申请原因必填');
                }
                kernel::single('sysaftersales_refunds')->create($refundsData, $info['tid'], $info['oid']);
                //生成退款申请单到平台
                $updateData['progress'] = '5'; //处理进度为，等待平台处理
            }
        }
        $result =  $this->objMdlAftersales->update($updateData, $filter);

        switch($updateData['progress'])
        {
        case '5':
            $params['aftersales_status'] =  "REFUNDING";
            break;
        case '1':
            $params['aftersales_status'] =  "WAIT_BUYER_RETURN_GOODS";
            break;
        case '3':
            $params['aftersales_status'] =  "SELLER_REFUSE_BUYER";
            break;
        }
        $params['oid']=$info['oid'];
        $params['tid']=$info['tid'];
        $params['user_id']=$info['user_id'];
        $tradeInfo = app::get('sysaftersales')->rpcCall('order.aftersales.status.update', $params);
        return $tradeInfo;
    }

    /**
     * 更新售后状态，更新售后处理进度
     */
    public function updateStatus($filter, $status, $progress)
    {
        $updateData['status'] = $status;
        $updateData['progress'] = $progress;
        $result = $this->objMdlAftersales->update($updateData, $filter);
        return $result;
    }

    /**
     * 售后过程中的退换货物流保存
     *
     * @param int $filter 售后编号
     * @param string $type buyer_back 消费者回寄，seller_confirm 商家重新发货
     * @param array $data 物流信息
     */
    public function sendGoods($filter, $type, $data, $loginId)
    {
        $info = $this->objMdlAftersales->getRow('aftersales_bn,aftersales_type,tid,oid,user_id,status,shop_id,progress', $filter);
        if( $type == 'buyer_back' )
        {
            kernel::single('sysaftersales_verify')->checkPermission($info, 'buyer',$loginId);
            if( $info['progress'] !== '1' && $info['aftersales_type'] == 'ONLY_REFUND' )
            {
                throw new \LogicException(app::get('sysaftersales')->_('不需要回寄货品'));
            }

            if($data['corp_code'] == "other" && !$data['logi_name'])
            {
                throw new \LogicException(app::get('sysaftersales')->_('请填写物流公司'));
            }

            if( $info['aftersales_type'] == 'RETURN_GOODS')
            {
                if(!$data['mobile'])
                {
                   throw new \LogicException(app::get('sysaftersales')->_('请填写手机号'));
                }
                if(!$data['receiver_address'])
                {
                   throw new \LogicException(app::get('sysaftersales')->_('请填写收货地址'));
                }
            }

            $updateData['sendback_data'] = serialize($data);
            $updateData['progress'] = '2';
            $params['aftersales_status'] = "WAIT_SELLER_CONFIRM_GOODS";
        }
        else
        {
            kernel::single('sysaftersales_verify')->checkPermission($info, 'seller',$loginId);
            if( $info['progress'] != '2' && $info['aftersales_type'] != 'EXCHANGING_GOODS' )
            {
                throw new \LogicException(app::get('sysaftersales')->_('不需要重新发货'));
            }
            $updateData['sendconfirm_data'] = serialize($data);
            $updateData['progress'] = '4';
            $updateData['status'] = '2';
            $params['aftersales_status'] = "SELLER_SEND_GOODS";
        }

        $result = $this->objMdlAftersales->update($updateData, $filter);
        $params['oid'] = $info['oid'];
        $params['tid'] = $info['tid'];
        $params['user_id'] = $info['user_id'];

        //更新子订单售后状态
        $tradeInfo = app::get('sysaftersales')->rpcCall('order.aftersales.status.update', $params);
        return $tradeInfo;
    }

    /**
    * ps ：售后过程中的退换货自提点保存
    * Time：2016/05/09 16:51:39
    * @author jianghui
    */
    public function sendZiti($filter, $type, $data, $loginId)
    {

        $info = $this->objMdlAftersales->getRow('aftersales_bn,aftersales_type,tid,oid,user_id,status,shop_id,progress', $filter);
        if( $type == 'buyer_back' )
        {
            kernel::single('sysaftersales_verify')->checkPermission($info, 'buyer',$loginId);
            if( $info['progress'] !== '1' && $info['aftersales_type'] == 'ONLY_REFUND' )
            {
                throw new \LogicException(app::get('sysaftersales')->_('不需要回寄货品'));
            }

            if(!$data['ziti_id'])
            {
                throw new \LogicException(app::get('sysaftersales')->_('请选择自提点'));
            }

            if(!$data['ziti_addr'])
            {
                throw new \LogicException(app::get('sysaftersales')->_('自提点地址不能为空'));
            }

            $updateData['ziti_id'] = $data['ziti_id'];
            $updateData['ziti_addr'] = $data['ziti_addr'];
            $updateData['progress'] = '2';
            $params['aftersales_status'] = "WAIT_SELLER_CONFIRM_GOODS";
        }
        else
        {
            kernel::single('sysaftersales_verify')->checkPermission($info, 'seller',$loginId);
            if( $info['progress'] != '2' && $info['aftersales_type'] != 'EXCHANGING_GOODS' )
            {
                throw new \LogicException(app::get('sysaftersales')->_('不需要重新发货'));
            }
            $updateData['ziti_memo'] = $data['ziti_memo'];
            $updateData['progress'] = '4';
            $updateData['status'] = '2';
            $params['aftersales_status'] = "SELLER_SEND_GOODS";
        }

        $result = $this->objMdlAftersales->update($updateData, $filter);
        $params['oid'] = $info['oid'];
        $params['tid'] = $info['tid'];
        $params['user_id'] = $info['user_id'];

        //更新子订单售后状态
        $tradeInfo = app::get('sysaftersales')->rpcCall('order.aftersales.status.update', $params);
        return $tradeInfo;
    }
}
