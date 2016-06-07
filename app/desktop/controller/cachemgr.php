<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_ctl_cachemgr extends desktop_controller 
{

    /**
     * 页面 缓存管理
     *
     * @return \base_http_response
     *
     */
    public function status() 
    {
        $storeResources = array_map(function($store) {
            $store['config'] = array_map(function ($itemConfig) {
                if (is_array($itemConfig)) {
                    $itemConfig = json_encode($itemConfig, 1);
                }
                return $itemConfig;
            }, $store['config']);
            return $store;
        }, cache::getStoreResourcesConfig());

        $pagedata['stores'] = cache::getStoreConfig();
        $pagedata['storeResources']  = $storeResources;
        return $this->page('desktop/cachemgr/index.html', $pagedata);
    }

    /**
     * 动作 清除资源缓存
     *
     * @return \base_http_response
     *
     */
    public function clean() 
    {
        $resource = input::get('resource');
        $this->begin();
        try
        {
            cache::resource($resource)->flush();
            $this->end(true, $store.'缓存清理成功!');
        }
        catch(Exception $e)
        {
            $this->end(false, $e->getMessage());
        }
    }
}
