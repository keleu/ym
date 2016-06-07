<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class sysagent_agent_actionbutton{

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

    function get_buttons($sdf_agent=array(), $is_all_disable = false)
    {
        $arr_agent = array();
        //待审核
        $disable_apply = false;
        //审核通过
        $disable_allow = false;
        $disable_noallow = false;
        //停止
        $disable_cancel = false;
        //重新启用
        $disable_restart = false;
        
        $flow_apply = false;
        $flow_allow = false;
        $flow_noallow = false;
        $flow_cancel = false;
        $flow_restart = false;
       
        if ($is_all_disable)
        {
            //待审核
            $disable_apply = true;
            //审核通过
            $disable_allow = true;
            $disable_noallow = true;
            //停止
            $disable_cancel = true;
            $disable_restart = true;
        }
        if ($sdf_agent)
        {        
            //已通过审核
            if ($sdf_agent['apply_status'] == 'active' )
            {
                $disable_noallow = false;
                $flow_noallow =true;
                $disable_allow =false;
                $flow_allow = true;
                $disable_cancel =true;
                $flow_cancel =false;
            }
            if ($sdf_agent['apply_status'] == 'successful')
            {
                $disable_noallow = true;
                $disable_allow =true;
                $disable_cancel = false;
                $flow_cancel =true;
            }
            if ($sdf_agent['apply_status'] == 'successful' && $sdf_agent['is_stop'] =='1'){
                $disable_noallow = true;
                $disable_allow =true;
                $disable_cancel = true;
            }
            if ($sdf_agent['apply_status'] == 'failing' )
            {
                $disable_allow =false;
                $flow_allow = true;
                $disable_noallow = true;
                $disable_cancel = true;
            }
            if ($sdf_agent['apply_status'] == 'lock' )
            {
                $disable_restart = false;
                $flow_restart = true;
            }
        }
        $buttons = array(
            're_sequence'=>array(
                'allow'=>array(
                    'label'=>app::get('aysagent')->_('通过'),
                    'flow'=>$flow_allow,
                    'disable'=>$disable_allow,
                    'app'=>'sysagent',
                    'act'=>'allow',
                ),
                'noallow'=>array(
                    'label'=>app::get('sysagent')->_('拒绝'),
                    'flow'=>$flow_noallow,
                    'disable'=>$disable_noallow,
                    'app'=>'sysagent',
                    'act'=>'noallow',
                ),
                'cancel'=>array(
                    'label'=>app::get('sysagent')->_('停用'),
                    'flow'=>$flow_cancel,
                    'disable'=>$disable_cancel,
                    'app'=>'sysagent',
                    'act'=>'cancel',
                ),
                'restart'=>array(
                    'label'=>app::get('sysagent')->_('启用'),
                    'flow'=>$flow_restart,
                    'disable'=>$disable_restart,
                    'app'=>'sysagent',
                    'act'=>'allow',
                ),
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
