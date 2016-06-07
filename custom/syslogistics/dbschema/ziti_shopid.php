<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'editable' => false,
            'comment' => app::get('syslogistics')->_('自增ID'),
        ),
        'ziti_id' => array(
            'type' => 'table:ziti@syslogistics',
            'required' => true,
            'comment' => app::get('syslogistics')->_('自提id'),
        ),
        'shop_id' => array(
            'type' => 'table:shop@sysshop',
            'required' => true,
            'comment' => app::get('syslogistics')->_('店铺id'),
        ),

    ),
    'primary' => 'id',
    'index' => array(
        'ziti_id' => ['columns' => ['ziti_id']],
        'shop_id' => ['columns' => ['shop_id']],
    ),
    'comment' => app::get('syslogistics')->_('自提点和店铺关系表'),
);
