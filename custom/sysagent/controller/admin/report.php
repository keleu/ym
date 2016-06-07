<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysagent_ctl_admin_report extends desktop_controller {

    var $workground = 'sysagent.workground.logistics';

    /**
     * 展示所有一级代理商
     * @params null
     * @return null
     */
    public function index()
    {
        $row = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,mobile,addr,area_first,area_second,area_third',array('status'=>'1','parent_id'=>0,'agent_level'=>1));
        // dump($row);die;
        foreach ($row as &$v) {
           // dump($v);
           $rs = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,mobile,addr,area_first,area_second,area_third',array('status'=>'1','parent_id'=>$v['agent_id']));
           // dump($rs);
           $v['children']=$rs;
        }
        $pagedata['agents'] = $row;
        $pagedata['areaMap'] = area::getMap();
        $pagedata['level'] = 2;
        // dump($pagedata);die;
        return $this->page('sysagent/report/agent_treeList.html',$pagedata);
    }

    /**
     * 加载地区子节点
     */
    public function getChildNode()
    {
        $id = input::get('reportId');
        // $data = area::getAreaIdPath()[$id];
        // $area = area::getAreaNameById($id);
        $row = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,mobile,area_first,area_second,area_third',array('status'=>'1','parent_id'=>$id));
        // dump($data);die;
        // foreach( $data as $key)
        // {
        //     $childData[$key] = area::areaKvdata()[$key];
        //     if( area::getAreaIdPath()[$key] )
        //     {
        //         $childData[$key]['is_child'] = true;
        //     }
        //     $rs = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status',array('status'=>'1','area_first'=>$area,'area_second'=>area::getAreaNameById($key),'parent_id'=>0,'agent_level'=>1));
        //      if(count($rs)>0){
        //        $childData[$key]['is_child'] = 1;
        //     }
        // }
        foreach ($row as &$v) {
            $res = app::get('sysagent')->model('agents')->getList('agent_id,name,point,status,parent_id',array('status'=>'1','parent_id'=>$v['agent_id']));
            if(count($res)>0){
               $childData[$v['agent_id']]['is_child'] = 1;
            }
            $childData[$v['agent_id']]['value'] = $v['name'];
            $childData[$v['agent_id']]['point'] = $v['point'];
            $childData[$v['agent_id']]['mobile'] = $v['mobile'];
            $childData[$v['agent_id']]['area'] = $v['area_first'].$v['area_second'].$v['area_third'];
            // $childData[$v['agent_id']]['parentId'] = $id;
            $childData[$v['agent_id']]['is_agent'] = 1;
        }
        $pagedata['step'] = input::get('level');
        $pagedata['level'] = input::get('level')+1;
        $pagedata['childData'] = $childData;
        // dump($pagedata);die;
        return view::make('sysagent/report/agent_sub_treeList.html', $pagedata);
    }

}

