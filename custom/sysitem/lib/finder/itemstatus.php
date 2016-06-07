<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysitem_finder_itemstatus {
    public $column_editbutton;
    public $column_editbutton_width=220;
    public $column_uname;
    public $column_uname_order = 120;
    public $column_email;
    public $column_email_order = 130;
    public $column_mobile;
    public $column_mobile_order = 140;
    public $detail_basic;
    public $detail_pwd;
    public $detail_grade;
    public $detail_experience;
    public $detail_point;

    public function __construct($app)
    {
        $this->app = $app;
        $this->column_editbutton = app::get('sysitem')->_('操作');
        $this->column_uname = app::get('sysitem')->_('用户名');
        $this->column_email = app::get('sysitem')->_('邮箱');
        $this->column_mobile = app::get('sysitem')->_('手机号');
        $this->column_area = app::get('sysitem')->_('地区');
        $this->detail_basic = app::get('sysitem')->_('审核列表');
        $this->detail_pwd = app::get('sysitem')->_('密码修改');
        $this->detail_grade = app::get('sysitem')->_('会员等级');
        $this->detail_experience = app::get('sysitem')->_('会员经验值');
        $this->detail_point = app::get('sysitem')->_('会员积分');
    }

     public function detail_basic($row)
    {
        //这里$this->odr_action_is_all_disable好像暂时没什作用，但是欧宝ec中这个定义，所以先留着
        $this->odr_action_is_all_disable=true;
        $sysinfo = kernel::single('sysitem_passport')->memInfo($row);
        $pagedata['items'] = $sysinfo;
        //测试数据
       /* $pagedata['item'] = array('item_is' => 1
                                ,'username' => 'zhang'
                                ,'status' => '0'
                                ,'apply_status' => 'failing'
                                     );*/
        $actionbutton = kernel::single('sysitem_sysitem_actionbutton');
        $pagedata['action_buttons']= $actionbutton->get_buttons($pagedata['items'],$this->odr_action_is_all_disable);
        // dump($pagedata);die;
        return view::make('sysitem/admin/sysitem/actions.html', $pagedata)->render();
    }

 

}
