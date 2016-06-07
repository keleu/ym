<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class sysitem_sysitem_actionbutton{

    function __construct($app){
        $this->app = $app;
    }
    
    
    
    //////////////////////////////////////////////////////////////////////////
    // order finder 按钮接口 设置可见的按钮 默认所有
    ///////////////////////////////////////////////////////////////////////////
    public function is_display( $arr=array() )
    {
        $this->is_display = $arr;
    }
    #End Func

    function get_buttons($sdf_items=array(), $is_all_disable = false)
    {
        $arr_items = array();
        //待审核
        $disable_apply = false;
        //审核通过
        $disable_allow = false;
        $disable_noallow = false;
        //停止
        $disable_cancel = false;
        
        $flow_apply = false;
        $flow_allow = false;
        $flow_noallow = false;
        $flow_cancel = false;
       
        if ($is_all_disable)
        {
            //待审核
            $disable_apply = true;
            //审核通过
            $disable_allow = true;
            $disable_noallow = true;
            //停止
            $disable_cancel = true;
        }
        if ($sdf_items)
        {         
            //已通过审核
            if ($sdf_items['check_res'] == 'active' )
            {
                $disable_noallow = false;
                $flow_noallow =true;
                $disable_allow =false;
                $flow_allow = true;
                $disable_cancel =false;
                $flow_cancel =true;
            }
            if ($sdf_items['check_res'] == 'successful')
            {
                $disable_noallow = true;
                $disable_allow =true;
                $disable_cancel = false;
                $flow_cancel =true;
            }
            if ($sdf_items['check_res'] == 'failing' )
            {
                $disable_allow =false;
                $flow_allow = true;
                $disable_noallow = true;
                $disable_cancel = true;
            }
        }
        $buttons = array(
            're_sequence'=>array(
                'allow'=>array(
                    'label'=>app::get('sysitem')->_('通过'),
                    'flow'=>$flow_allow,
                    'disable'=>$disable_allow,
                    'app'=>'sysitem',
                    'act'=>'allow',
                ),
                'noallow'=>array(
                    'label'=>app::get('sysitem')->_('拒绝'),
                    'flow'=>$flow_noallow,
                    'disable'=>$disable_noallow,
                    'app'=>'sysitem',
                    'act'=>'noallow',
                ),
                // 'cancel'=>array(
                //     'label'=>app::get('sysitem')->_('停用'),
                //     'flow'=>$flow_cancel,
                //     'disable'=>$disable_cancel,
                //     'app'=>'sysitem',
                //     'act'=>'cancel',
                // ),
            ),
        );
        if( $this->is_display && is_array($this->is_display) ) {
            foreach( $buttons['re_sequence'] as $key => $val ) {
                if( !in_array($key,$this->is_display) ) unset($buttons['re_sequence'][$key]);
            }
        }
        return $buttons;
    }
    
}
