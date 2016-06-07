<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysclearing_ctl_admin_rule extends desktop_controller{

    var $workground = 'sysclearing_ctl_admin_rule';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    
    /**
     * ps ：积分兑换规则
     * Time：2015-12-01 16:10:08
     * @author 沈浩
     * @param 参数类型
     * @return 返回值类型
    */
    public function base_setting()
    {   
        $pagedata['point_ratio']  = app::get('sysclearing')->getConf('sysclearing_setting.point_ratio')?app::get('sysclearing')->getConf('sysclearing_setting.point_ratio'):'0.1';
        return $this->page('sysclearing/pointRule.html',$pagedata);
    }

    /**
     * ps :保存积分兑换比率
     * Time：2015-12-01 16:10:14
     * @author 沈浩
     * @param 参数类型
     * @return 返回值类型
    */
    public function saveSet()
    {
        // dump($_POST);die;
        $this->begin();
            app::get('sysclearing')->setConf('sysclearing_setting.point_ratio',$_POST['point_ratio']);
        $this->end(true,app::get('sysclearing')->_('保存成功'));
    }
}
