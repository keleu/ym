<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_facades_cache extends base_facades_facade
{
	/**
	 * Return the View instance
	 * 
	 * @var \Symfony\Component\HttpFoundation\Request;
	 */
    
    private static $__cache;
    
    protected static function getFacadeAccessor()
    {
        if (!static::$__cache)
        {
            static::$__cache = new base_cache_manager;
        }
        return static::$__cache;
    }
}
