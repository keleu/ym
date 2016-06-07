<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysagent_ctl_admin_regions extends desktop_controller {

    var $workground = 'sysagent.workground.logistics';

    /**
     * 展示所有地区
     * @params null
     * @return null
     */
    public function index()
    {
        $pagedata['areaMap'] = area::getMap();
        $pagedata['level'] = 2;
        return $this->page('sysagent/delivery/area_treeList.html',$pagedata);
    }

    /**
     * 加载地区子节点
     */
    public function getChildNode()
    {
        $id = input::get('regionId');
        $data = area::getAreaIdPath()[$id];
        $area = area::getAreaNameById($id);
        $row = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status',array('status'=>'1','area_first'=>$area,'area_second'=>'','parent_id'=>0,'agent_level'=>1));
        // dump($data);die;
        foreach( $data as $key)
        {
            $childData[$key] = area::areaKvdata()[$key];
            if( area::getAreaIdPath()[$key] )
            {
                $childData[$key]['is_child'] = true;
            }
            $rs = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status',array('status'=>'1','area_first'=>$area,'area_second'=>area::getAreaNameById($key),'parent_id'=>0,'agent_level'=>1));
             if(count($rs)>0){
               $childData[$key]['is_child'] = 1;
            }
        }
        foreach ($row as &$v) {
            $res = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,parent_id',array('status'=>'1','parent_id'=>$v['agent_id']));
            if(count($res)>0){
               $childData[$v['agent_id']]['is_child'] = 1;
            }
            $childData[$v['agent_id']]['value'] = $v['name'];
            $childData[$v['agent_id']]['point'] = $v['point'];
            $childData[$v['agent_id']]['parentId'] = $id;
            $childData[$v['agent_id']]['is_agent'] = 1;
        }
        $pagedata['step'] = input::get('level');
        $pagedata['level'] = input::get('level')+1;
        $pagedata['childData'] = $childData;
        // dump($pagedata);die;
        return view::make('sysagent/delivery/area_sub_treeList.html', $pagedata);
    }

    /**
     * 加载供应商子节点
     */
    public function getChildNodeSon()
    {
        $id = input::get('regionId');
        // $data = area::getAreaIdPath()[$id];
        // $area = area::getAreaNameById($id);
        // if(!$area){
        $row = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,parent_id',array('status'=>'1','parent_id'=>$id));
        // }
        // else{
        //     $row = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,area_first,area_second',array('agent_id'=>$id,'status'=>1));
        // }
        // dump($row);die;
        // if($row){
        //     $rs = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,area_first,area_second',array('area_first'=>$row[0]['area_first'],'agent_id|noequal'=>$id,'status'=>1));
        // }
        // foreach( $data as $key)
        // {
        //     $childData[$key] = area::areaKvdata()[$key];
        //     if( area::getAreaIdPath()[$key] )
        //     {
        //         $childData[$key]['is_child'] = true;
        //     }
        // }
        foreach ($row as &$v) {
            $rs = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,parent_id',array('status'=>'1','parent_id'=>$v['agent_id']));
            if(count($rs)>0){
               $childData[$v['agent_id']]['is_child'] = 1;
            }
            $childData[$v['agent_id']]['value'] = $v['name'];
            $childData[$v['agent_id']]['point'] = $v['point'];
            $childData[$v['agent_id']]['parentId'] = $id;
            $childData[$v['agent_id']]['is_agent'] = 1;
        }
        $pagedata['step'] = input::get('level');
        $pagedata['level'] = input::get('level')+1;
        $pagedata['childData'] = $childData;
        // dump($pagedata);die;
        return view::make('sysagent/delivery/area_sub_treeList.html', $pagedata);
    }
    public function getChildNodeSec()
    {
        $id = input::get('regionId');
        // dump($id);die;
        $data = area::getAreaIdPath()[$id];
        $area = area::getAreaNameById($id);
        $row = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status',array('status'=>'1','area_second'=>$area,'parent_id'=>0,'agent_level'=>1));
        // dump($data);die;
        foreach( $data as $key)
        {
            $childData[$key] = area::areaKvdata()[$key];
            if( area::getAreaIdPath()[$key] )
            {
                $childData[$key]['is_child'] = true;
            }
            $rs = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status',array('status'=>'1','area_second'=>$area,'area_third'=>area::getAreaNameById($key),'parent_id'=>0,'agent_level'=>1));
             if(count($rs)>0){
               $childData[$key]['is_child'] = 1;
            }
        }
        foreach ($row as &$v) {
            $res = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,parent_id',array('status'=>'1','parent_id'=>$v['agent_id']));
            if(count($res)>0){
               $childData[$v['agent_id']]['is_child'] = 1;
            }
            $childData[$v['agent_id']]['value'] = $v['name'];
            $childData[$v['agent_id']]['point'] = $v['point'];
            $childData[$v['agent_id']]['parentId'] = $id;
            $childData[$v['agent_id']]['is_agent'] = 1;
        }
        $pagedata['step'] = input::get('level');
        $pagedata['level'] = input::get('level')+1;
        $pagedata['childData'] = $childData;
        // dump($pagedata);die;
        return view::make('sysagent/delivery/area_sub_treeList.html', $pagedata);
    }

    public function getChildNodeThird()
    {
        $id = input::get('regionId');
        $idsec=substr($id,0,4).'00';
        $area_sec = area::getAreaNameById($idsec);
        $area_third = area::getAreaNameById($id);
        $row = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status',array('status'=>'1','area_second'=>$area_sec,'area_third'=>$area_third,'parent_id'=>0,'agent_level'=>1));
        foreach ($row as &$v) {
            $rs = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,parent_id',array('status'=>'1','parent_id'=>$v['agent_id']));
            if(count($rs)>0){
               $childData[$v['agent_id']]['is_child'] = 1;
            }
            $childData[$v['agent_id']]['value'] = $v['name'];
            $childData[$v['agent_id']]['point'] = $v['point'];
            $childData[$v['agent_id']]['parentId'] = $id;
            $childData[$v['agent_id']]['is_agent'] = 1;
        }
        $pagedata['step'] = input::get('level');
        $pagedata['level'] = input::get('level')+1;
        $pagedata['childData'] = $childData;
        return view::make('sysagent/delivery/area_sub_treeList.html', $pagedata);
    }
    // /**
    //  * 删除指定ID，地区
    //  */
    // public function toRemoveArea()
    // {
    //     $this->begin('?app=sysagent&ctl=admin_regions&act=index');
    //     $id = input::get('regionId');
    //     if( empty($id) )
    //     {
    //         $this->end(false,app::get('sysagent')->_('删除地区失败！'));
    //     }

    //     try
    //     {
    //         $this->adminlog("删除地区[id:{$id}]", 1);
    //         area::editArea('remove', $id);
    //     }
    //     catch(Exception $e)
    //     {
    //         $msg = $e->getMessage();
    //         $this->end(false,$msg);
    //     }

    //     $this->end(true,app::get('sysagent')->_('删除地区成功！'));
    // }

    // //编辑地区
    // public function detailDlArea()
    // {
    //     $id = input::get('regionId');
    //     $pagedata['name'] = area::getAreaNameById($id);
    //     $pagedata['regionId'] = $id;
    //     return view::make('sysagent/delivery/area_edit.html',$pagedata);
    // }

    // //编辑地区名称
    // public function saveDlArea()
    // {
    //     $this->begin('?app=sysagent&ctl=admin_regions&act=index');
    //     $id = input::get('regionId');
    //     $name = input::get('name');
    //     try
    //     {
    //         $this->adminlog("编辑地区[name:{$name}][id:{$id}]", 1);
    //         area::editArea('update',$id,$name);
    //     }
    //     catch(Exception $e)
    //     {
    //         $msg = $e->getMessage();
    //         $this->end(false,$msg);
    //     }
    //     $this->end(true, app::get('sysagent')->_('修改成功'));
    // }

    // /**
    //  * 添加新地区界面
    //  * @params string 父级region id
    //  * @return null
    //  */
    // public function showNewArea()
    // {
    //     $id = input::get('regionId');
    //     $pagedata['parent']['name'] = area::getAreaNameById($id);
    //     $pagedata['parent']['id'] = $id;
    //     return view::make('sysagent/delivery/area_new.html', $pagedata);
    // }

    // public function addDlArea()
    // {
    //     $this->begin('?app=sysagent&ctl=admin_regions&act=index');
    //     $parentId = input::get('parentId');
    //     $name = input::get('name');
    //     try
    //     {
    //         $this->adminlog("新增地区[name:{$name}][parentId:{$parentId}]", 1);
    //         area::editArea('add', $parentId, $name);
    //     }
    //     catch(Exception $e)
    //     {
    //         $msg = $e->getMessage();
    //         $this->end(false,$msg);
    //     }
    //     $this->end(true, app::get('sysagent')->_('添加成功'));
    // }

    public function resetFile()
    {
        $this->begin('?app=sysagent&ctl=admin_regions&act=index');
        try{
            area::resetFile();
        }
        catch( LogicException $e)
        {
            $this->end(false,$e->getMessage());
        }
        $this->end(true,app::get('sysagent')->_('保存成功！'));
    }

    public function init()
    {
        $this->begin('?app=sysagent&ctl=admin_regions&act=index');
        $this->adminlog("初始化地区数据", 1);
        area::initFileContents();
        $this->end(true,app::get('sysagent')->_('初始化成功！'));
    }
}

