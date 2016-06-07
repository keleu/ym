<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysitem_finder_items {
    // public $column_blend;
    // public $column_blend_order = 120;

    public function __construct($app)
    {
        $this->app = $app;
        $this->column_blend = app::get('sysitem')->_('混合价');
    }

    /**
     * @brief 混合价重定义显示
     *
     * @param $row
     *
     * @return
     */
    public function column_blend(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $colList[$k] = kernel::single('sysitem_blendShow')->show($row['blend_integral'],$row['blend_price']);
        }
    }
}
