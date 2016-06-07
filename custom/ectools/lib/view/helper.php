<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_view_helper{

    function modifier_barcode($data){
        return kernel::single('ectools_barcode')->get($data);
    }
    public function modifier_pot($pots){
    	if(!$pots) $pots=0;
    	return $pots.'积分';
    }
}
