<?php
/**
 * @brief 平台操作日志
 */
class system_ctl_admin_apilog extends desktop_controller {

    /**
     * @brief  平台操作日志
     *
     * @return
     */
    public function index()
    {
        return $this->finder('system_mdl_apilog',array(
            'use_buildin_delete' => false,
            'title' => app::get('system')->_('API日志'),
            'actions'=>array(),
        ));
    }

}


