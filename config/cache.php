<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


return [

    /*
    |--------------------------------------------------------------------------
    | 默认缓存存储场景(Cache Store)
    |--------------------------------------------------------------------------
    |
    | 这个选项用来当没有明确缓存存储场景时, 默认指定的缓存存储场景,
    |
    */
    'default' => 'default',

    /*
    |--------------------------------------------------------------------------
    | 设置缓存开启或关闭
    |--------------------------------------------------------------------------
    |
    | 这个选项用来当没有明确缓存存储场景时, 默认指定的缓存存储场景,
    |
    */
    'enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | 当设置缓存关闭时, 哪几个`store`忽略此设置
    |--------------------------------------------------------------------------
    |
    | 当设置缓存关闭时, 哪几个`store`忽略此设置,
    | 因为在开发环境下关闭缓存进行调试下, 类似`session`这样的场景, 仍然希望`sesion`奏效,
    | 否则就连登陆也不能够了
    |
    */
    'disabled_except' => ['session', 'vcode'],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | 这里定义了应用系统所有的缓存使用场景(stores), 每个store可以设定独立的缓存资
    | 源(resoource).
    | 在两种情况下会使用`default`存储场景的`resource`资源:
    | 1. 使用了没有定义的场景(store)
    | 2. 定义了场景, 但是没有指定资源(resource)
    | 
    */
    'stores' => [

        'default' => [
            'title' => '默认',
            'memo' => '当使用缓存时, 没有指定场景(store)时或场景(store)没有设置资源(resouce)时, 会默认使用此配置指定的resource(资源)',
            'resource' => 'null',
        ],
        
        'session' => [
            'title' => '会话',
            'memo' => '访问Web应用程序的每个用户都生成一个单独的Session. Session会保存用户当次访问的运行时数据',
            'resource' => 'secache',
        ],

        'vcode' => [
            'title' => '验证码',
            'memo' => '验证码',
            'resource' => 'secache',
        ],

        'compiler' => [
            'title' => '系统模板缓存',
            'memo' => '系统模板缓存, 建议使用`APC`/`secache`之类的本地缓存的方式',
        ],

    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Resources
    |--------------------------------------------------------------------------
    |
    | 这里定义缓存的所有资源,在stores里需要制定对应的资源
    |
    */
    'resources' => [
        // 此条勿要删除, 系统默认使用
        'null'=> [
            'driver' => 'null'
        ],

        'secache' => [
            'driver' => 'secache',
        ],

        'apc' => [
            'driver' => 'apc'
        ],

        'memcached' => [
            'driver' => 'memcached',
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100],
            ],
        ],
    ],
    

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM based store such as APC or Memcached, there might
    | be other applications utilizing the same cache. So, we'll specify a
    | value to get prefixed to all our keys so we can avoid collisions.
    |
    */

    'prefix' => 'luckymall',
    
];

