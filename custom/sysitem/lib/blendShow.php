<?php

class sysitem_blendShow
{

    /**
    * ps ：获取混合价
    * Time：2016/03/29 15:31:17
    * @author jianghui
    */
    public function show($blend_integral,$blend_price)
    {
    	if($blend_integral>0){
    		return $blend_integral.'积分+'.round($blend_price,3).'元';
    	}else{
    		return '';
    	}
        
    }
    /**
    * ps ：获取合计中积分加现金的价格
    * Time：2016/03/30 10:30:52
    * @author jianghui
    */
        
    public function totalshow($total_integral,$total_price)
    {
        $total_blend='';
        if($total_integral>0){
            $total_blend = intval($total_integral).'积分';
        }
        if($total_integral>0 && $total_price>0){
            $total_blend .= '+';
        }
        if($total_price>0){
            $total_blend .= '￥'.round($total_price,3);
        }
        return $total_blend?$total_blend:0;
    }

    /**
    * ps ：获取显示的价格
    * Time：2016/04/12 15:29:45
    * @author jianghui
    */
    public function blend($integral,$price,$blend_integral,$blend_price)
    {
        $blend = '';
        if($integral>0){
            $blend = $integral.'积分';
        }else if($price>0){
            $blend = '￥'.round($price,3);
        }else{
            $blend = $this->show($blend_integral,$blend_price);
        }
        return $blend;
    }        
}

