<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class testCache extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        cache::enable();
    }

    public function testPutAndGet()
    {
        //         echo;
        cache::put('dd', 20, 10);
        $this->assertEquals(cache::get('dd'), 20);
    }

    public function testGetDefault()
    {
        $this->assertEquals(cache::get('future', function () {return 'default';}), 'default');
    }

    public function ktestFlush()
    {
        cache::put('testdd', 20, 1);
        cache::flush();
        $this->assertEquals(cache::get('test:dd'), null);
    }

    public function testIncrementAndDecrement()
    {
        cache::put('xxincre', 0, 0);
        $this->assertEquals(cache::increment('xxincre', 1), 1);
        $this->assertEquals(cache::increment('xxincre', 1), 2);
        $this->assertEquals(cache::decrement('xxincre', 1), 1);
    }

    public function testDisable()
    {
        cache::put('t:ddddd', 20, 10);
        echo 'nnd';
        cache::disable();
        echo '!!!!!!!!!!!';
        var_dump(cache::get('t:ddddd'));
        
        $this->assertEquals(cache::get('t:ddddd'), null);
        cache::enable();
        $this->assertEquals(cache::get('t:ddddd'), 20);
    }

    public function testHasAndForget()
    {
        cache::put('t:ddddd', 20, 10);
        $this->assertEquals(cache::get('t:ddddd'), 20);
        $this->assertEquals(cache::has('t:ddddd'), true);
        cache::forget('t:ddddd');
        $this->assertEquals(cache::has('t:ddddd'), false);
        $this->assertEquals(cache::get('t:ddddd'), null);
    }

    public function testForever()
    {
        cache::forever('aaa', 30);
        $this->assertEquals(cache::get('aaa') , 30);
    }

    public function testGetCacheVersionKey()
    {
        var_dump(cache::getCacheVersion());
    }

    public function testStore()
    {
        cache::store('session')->forever('mmd', 20);
        var_dump(cache::store('session')->get('mmd'));
    }

    public function testArrayBuild()
    {
        $a = ['a' => 1, 'b' =>2, 'c' =>3];

        $b = array_build($a, function($key, $value){
            if ($value%2) return [$key, $value+1];
            
            
        });
        var_dump($b);
    }
}

