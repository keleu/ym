<?php

/**
 * 审核列表
 */
class sysitem_ctl_admin_check extends desktop_controller{

    public $workground = 'sysitem.workground.item';

    /**
     * 列表
     *
     * @return
     */
    public function index()
    {
        return $this->finder('sysitem_mdl_item_status',array(
            'title' => app::get('sysitem')->_('商品审核列表'),
            // 'use_buildin_filter' => true,
            'use_buildin_delete' => false,
            'use_view_tab'=>true,
            'actions' => array(
                array(
                    'label'=>app::get('sysitem')->_('批量通过'),
                    'submit'=>"?app=sysitem&ctl=admin_check&act=checkAll",
                ),
                array(
                    'label'=>app::get('sysitem')->_('批量拒绝'),
                    'submit'=>"?app=sysitem&ctl=admin_check&act=refuseAll",
                )
            ),
        ));

    }
    /**
     * ps 商品审核通过
     * Time：2015-11-25 13:03:07
     * @author 沈浩 
     * @param 参数类型
     * @return 返回值类型
    */
    public function doallow()
    {
        $sdf = $_POST;
        $sdf['agree_time'] = time();
        try
        {
            app::get('sysitem')->model('item_status')->save($sdf);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return $this->splash('success',null,"操作成功");
    }

    /**
     * ps 商品审核通过
     * Time：2015-11-25 13:03:07
     * @author 沈浩 
     * @param 参数类型
     * @return 返回值类型
    */
    function goallow($item_id){
        $pagedata['item_id']=$item_id ;
        $sysinfo = kernel::single('sysitem_passport')->memInfo($item_id);
        $sysinfo['check_res'] = 'successful';
        $sysinfo['approve_status'] = 'instock';
        $pagedata['item'] = $sysinfo;
        return view::make('sysitem/admin/sysitem/goEnter.html', $pagedata)->render();

    }

     /**
     * ps 商品审核拒绝
     * Time：2015-11-25 16:41:07
     * @author 沈浩
     * @param 参数类型
     * @return 返回值类型
    */
    public function donoallow()
    {   
        $sdf = $_POST;
        $sdf['agree_time'] = time();
        try
        {
            app::get('sysitem')->model('item_status')->save($sdf);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return $this->splash('success',null,"操作成功");
    }

    /**
     * ps 商品审核拒绝
     * Time：2015-11-25 16:41:07
     * @author 沈浩
     * @param 参数类型
     * @return 返回值类型
    */
    function gonoallow($item_id){
        $pagedata['item_id']=$item_id;
        $sysinfo = kernel::single('sysitem_passport')->memInfo($item_id);
        $sysinfo['check_res'] = 'failing';
        $sysinfo['approve_status'] = 'instock';
        $sysinfo['is_Adjusted'] = 0;
        $pagedata['item'] = $sysinfo;
        return view::make('sysitem/admin/sysitem/refuse.html', $pagedata)->render();

    }

    public function _views()
    {
        $sub_menu = array(
            0=>array('label'=>app::get('sysitem')->_('待审核'),'optional'=>false,'filter'=>array('approve_status'=>'check')),
            1=>array('label'=>app::get('sysitem')->_('审核通过'),'optional'=>false,'filter'=>array('check_res'=>'successful')),
            2=>array('label'=>app::get('sysitem')->_('审核拒绝'),'optional'=>false,'filter'=>array('check_res'=>'failing')),
        );
        return $sub_menu;
    }

    /**
     * ps ：批量审核通过
     * Time：2015/12/07 09:43:12
     * @author liuxin
    */
    public function checkAll(){
        $url = '?app=sysitem&ctl=admin_check&act=index';
        $model_item = app::get('sysitem')->model('item_status');
        foreach ($_POST['item_id'] as $v) {
            $data = array(
                'item_id' => $v,
                'approve_status' => 'instock',
                'check_res' => 'successful',
            );
            $model_item->save($data);
        }
        return $this->splash('success',$url,"操作成功");
    }

    /**
     * ps ：批量审核拒绝
     * Time：2015/12/07 09:43:25
     * @author liuxin
    */
    public function refuseAll(){
        $url = '?app=sysitem&ctl=admin_check&act=index';
        $model_item = app::get('sysitem')->model('item_status');
        foreach ($_POST['item_id'] as $v) {
            $data = array(
                'item_id' => $v,
                'approve_status' => 'instock',
                'check_res' => 'failing',
                'is_Adjusted' => 0
            );
            $model_item->save($data);
        }
        return $this->splash('success',$url,"操作成功");
    }
}


