<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_mdl_payments_agent extends dbeav_model{
    var $defaultOrder = array('payed_time','DESC');

    /**
     * 模板统一保存的方法
     * @params array - 需要保存的支付信息
     * @params boolean - 是否需要强制保存
     * @return boolean - 保存的成功与否的进程
     */
    public function save(&$data,$mustUpdate = null, $mustInsert=false)
    {
        // 异常处理
        if (!isset($data) || !$data || !is_array($data))
        {
            trigger_error(app::get('ectools')->_("支付单信息不能为空！"), E_USER_ERROR);exit;
        }

        $sdf = array();

        // 支付数据列表
        $background = true;//后台 todo

        $payment_data = $data;
        $sdf_payment = $this->getRow('*',array('payment_id'=>$data['payment_id']));
        if ($sdf_payment)
        {
            if($sdf_payment['status'] == $data['status']
                || ($sdf_payment['status'] != 'progress' && $sdf_payment['status'] != 'ready')){
                return true;
            }
        }
        $sdf = $data;
        $sdf['status'] = $sdf['status'] ? $sdf['status'] : 'ready';

        // 保存支付信息（可能是退款信息）
        $is_succ = parent::save($sdf,$mustUpdate,$mustInsert);

        return $is_succ;
    }

    //获取本地的支付单号
    public function _getPaymentId($agentId = '')
    {
        // echo $agentId;exit;
        $agentId = str_pad($agentId%100000,5,time(),STR_PAD_LEFT);
        $i = rand(0,99999);
        do{
            if(99999==$i){
                $i=0;
            }
            $i++;
            $paymentId = date('ymdHi').str_pad($i,5,'0',STR_PAD_LEFT).$agentId;
            $row = $this->getRow('payment_id',array('payment_id'=>$paymentId));
        }while($row);
        return $paymentId;
    }
}


