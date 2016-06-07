<?php

class sysagent_agent_Nodes{

    public function __construct()
    {
        $this->app = app::get('sysagent');
        $this->model = $this->app->model('agent_relation');
        $this->db = db::connection();
    }

    /**
     * ps ：新增节点
     * Time：2015/11/20 16:40:12
     * @author liuxin
     * @param array()节点数据
     * @return bool
    */
    public function create($nodes){
        $root_node = $this->model->getRow('*',array('parent_id'=>'-1'));
        if(is_array($root_node)){
            $data = array(
                'agent_id' => '0',
                'left_id' => '1',
                'right_id' => '2',
                'parent_id' => '-1'
            );
            $this->model->save($data);
        }
        $parent_node = $this->model->getRow('*',array('agent_id'=>$nodes['parent_id']));
        $left_id = $parent_node['left_id'] + 1;
        $right_id = $left_id + 1;
        $sql = "update sysagent_agent_relation
                set left_id = left_id + 2
                where left_id >= {$left_id}";
        $str = "update sysagent_agent_relation
                set right_id = right_id + 2
                where right_id >= {$left_id}";
        if($this->db->executeQuery($sql)&&$this->db->executeQuery($str)){
            $nodes['left_id'] = $left_id;
            $nodes['right_id'] = $right_id;
            $this->model->save($nodes);
            return true;
        }
        return false;
    }

    /**
     * ps ：移动节点及其子节点
     * Time：2015/11/23 10:32:52
     * @author liuxin
     * @param array() 源节点，目标节点
     * @return bool
    */
    public function moveNode($nodes){
        $sourceNode = $this->model->getRow('*',array('agent_id'=>$nodes['agent_id']));
        $targetNode = $this->model->getRow('*',array('agent_id'=>$nodes['parent_id']));
        if(count($sourceNode) == 0||count($targetNode) == 0){
            return true;
        }
        $childCount = $this->getChildCount($sourceNode);
        $addNum1 = ($childCount + 1) * 2;
        $sourceQStr = implode(",", $this->getChild($sourceNode));
        if($sourceNode['left_id'] < $targetNode['left_id'] && $sourceNode['right_id'] < $targetNode['right_id']){
            //parent to brother and S<'T
            $sql1 = "update sysagent_agent_relation
                        set left_id = left_id - {$addNum1}
                        where left_id > {$sourceNode['right_id']}
                        and left_id < {$targetNode['right_id']}";
            $sql2 = "update sysagent_agent_relation
                        set right_id = right_id - {$addNum1}
                        where right_id > {$sourceNode['right_id']}
                        and right_id < {$targetNode['right_id']}";
            $addNum = $targetNode['right_id'] - $sourceNode['right_id'] - 1;
            $sql3 = "update sysagent_agent_relation
                        set left_id = left_id + {$addNum},
                        right_id = right_id + {$addNum}
                        where agent_id in ({$sourceQStr})";
        }
        else if($sourceNode['left_id'] < $targetNode['left_id'] && $sourceNode['right_id'] > $targetNode['right_id']){
            //parent move to child . not allow
            return false;
        }
        else if($sourceNode['left_id'] > $targetNode['left_id'] && $sourceNode['right_id'] < $targetNode['right_id']){
            //move to parents
            $sql1 = "update sysagent_agent_relation
                        set left_id = left_id - {$addNum1}
                        where left_id > {$sourceNode['right_id']}
                        and left_id < {$targetNode['right_id']}";
            $sql2 = "update sysagent_agent_relation
                        set right_id = right_id - {$addNum1}
                        where right_id > {$sourceNode['right_id']}
                        and right_id < {$targetNode['right_id']}";
            $addNum = $targetNode['right_id'] - $sourceNode['right_id'] - 1;
            $sql3 = "update sysagent_agent_relation
                        set left_id = left_id + {$addNum},
                        right_id = right_id + {$addNum}
                        where agent_id in ({$sourceQStr})";
        }
        else if($sourceNode['left_id'] > $targetNode['left_id'] && $sourceNode['right_id'] > $targetNode["right_id"]){
            //move to brother and S>T
            $sql1 = "update sysagent_agent_relation
                        set left_id = left_id + {$addNum1}
                        where left_id > {$targetNode['right_id']}
                        and left_id < {$sourceNode['left_id']}";
            $sql2 = "update sysagent_agent_relation
                        set right_id = right_id + {$addNum1}
                        where right_id >= {$targetNode['right_id']}
                        and right_id < {$sourceNode['left_id']}";
            $addNum = $sourceNode['left_id'] - $targetNode['right_id'];
            $sql3 = "update sysagent_agent_relation
                        set left_id = left_id - {$addNum},
                        right_id = right_id - {$addNum}
                        where agent_id in ({$sourceQStr})";
        }
        // update parent_id
        $sql4 = "update sysagent_agent_relation
                    set parent_id = {$targetNode['agent_id']}
                    where agent_id = {$sourceNode['agent_id']}";
        if($this->db->executeQuery($sql1)&&$this->db->executeQuery($sql2)&&$this->db->executeQuery($sql3)&&$this->db->executeQuery($sql4)){
            return true;
        }
        return false;
    }

    /**
     * ps ：获取子节点数
     * Time：2015/11/23 10:57:48
     * @author liuxin
     * @param array 源节点
     * @return int 子节点数
    */
    public function getChildCount($nodes){
        $sourceNode = $this->model->getRow('*',array('agent_id'=>$nodes['agent_id']));
        return intval(($sourceNode['right_id'] - $sourceNode['left_id'] - 1)/2);
    }

    /**
     * ps ：获取子节点
     * Time：2015/11/23 12:38:24
     * @author liuxin
     * @param array 源节点
     * @return array 子节点id（包括自己）
    */
    public function getChild($nodes){
        $sourceNode = $this->model->getRow('*',array('agent_id'=>$nodes['agent_id']));
        $sql = "select * 
                from sysagent_agent_relation
                where left_id between {$sourceNode['left_id']} and {$sourceNode['right_id']}";
        return array_column($this->db->fetchAll($sql),'agent_id');//executeQuery()
    }

    /**
     * ps ：删除节点及其子节点
     * Time：2015/11/23 13:17:21
     * @author liuxin
     * @param array 源节点ID
     * @return bool
    */
    public function removeNodes($nodeIds){
        foreach ($nodeIds as $k => $v) {
            $sourceNode = $this->model->getRow('*',array('agent_id'=>$v));
            if(count($sourceNode) == 0){
                continue;
            }
            if($sourceNode['agent_id'] != ''){
                $span = $sourceNode['right_id'] - $sourceNode['left_id'] + 1;
                $sql1 = "delete from sysagent_agent_relation
                            where left_id >= {$sourceNode['left_id']}
                            and right_id <= {$sourceNode['right_id']}";
                if(!$this->db->executeQuery($sql1)){
                    return false;
                }

                $sql2 = "update sysagent_agent_relation
                            set left_id = left_id - {$span}
                            where left_id > {$sourceNode['right_id']}";
                if(!$this->db->executeQuery($sql2)){
                    return false;
                }

                $sql3 = "update sysagent_agent_relation
                            set right_id = right_id - {$span}
                            where right_id > {$sourceNode['right_id']}";
                if(!$this->db->executeQuery($sql3)){
                    return false;
                }
            }
        }
        return true;
    }
}