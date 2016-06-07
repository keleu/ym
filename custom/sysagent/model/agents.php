<?php

class sysagent_mdl_agents extends dbeav_model {

    /**
     * ps ：save方法重写
     * Time：2015/11/18 16:46:21
     * @author liuxin
     * @param array()
     * @return bool
    */
    public function save($data){
        $flag = $data['flag'];
        unset($data['flag']);
        if($data['is_again']){
            if(!parent::update($data,array('agent_id'=>$data['agent_id']))){
                $msg = "保存失败";
                throw new \logicException($msg);
                return false;
            }
        }else{
            if(!parent::save($data)){
                $msg = "保存失败";
                throw new \logicException($msg);
                return false;
            }
        }
        $agents = app::get('sysagent')->model('agents');
        $agent_account = app::get('sysagent')->model('account');
        $agent_apply = app::get('sysagent')->model('enterapply');
        //by zhangyan 2015-11-18 添加标记，以防修改的时候同样增加新的内容
        if ($flag=='create') {
            $data['agent_id'] = $agents -> getRow('agent_id',array('username'=>$data['username']))['agent_id'];
            $row = array(
                'agent_id' => $data['agent_id'],
                'login_account' => $data['username'],
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'id_card' => $data['id_card'],
                'sex' => $data['sex'],
                'account_type' => $data['account_type'],
                'login_password' => $data['login_password']?pam_encrypt::make($data['login_password']):pam_encrypt::make('123456'),
                'createtime' => $data['regtime'],
                'modeified_time' => time()
            );
            if($data['is_again']) {
                $row['account_id']=$data['account_id'];
                $agent_account -> update($row,array('account_id' =>$data['account_id']));
            }else{
                $agent_account -> save($row);
            }

            if($data['is_again']){
                $data['enterapply_id'] = $agent_apply -> getRow('enterapply_id',array('agent_id'=>$data['agent_id']))['enterapply_id'];
                $apply_data = array(
                    'enterapply_id' => $data['enterapply_id'],
                    'agent_id' => $data['agent_id'],
                    'add_time' => $data['regtime'],
                    'apply_status' => 'active'
                );
                $agent_apply -> update($apply_data,array('enterapply_id'=>$apply_data['enterapply_id']));
            }else{
                $apply_data = array(
                'agent_id' => $data['agent_id'],
                'add_time' => $data['regtime']
                );
                $agent_apply -> save($apply_data);
            }
        }
        elseif($flag == 'allow'){
            $nodes['agent_id'] = $data['agent_id'];
            $nodes['parent_id'] = $agents->getRow('parent_id',array('agent_id'=>$data['agent_id']))['parent_id'];
            kernel::single('sysagent_agent_Nodes')->create($nodes);
        }

        return true;
    }

    /**
     * ps ：dodelete方法重写
     * Time：2015/11/18 16:46:48
     * @author liuxin
     * @param array()
     * @return bool
    */
    public function dodelete($data){
        // dump($data);die;
        $agent_account = app::get('sysagent')->model('account');
        $agents = app::get('sysagent')->model('agents');
        $agent_apply = app::get('sysagent')->model('enterapply');
        // $row = $agents->getRow('*',array('parent_id'=>$data));
        // $point = $agents->getList('*',array('agent_id'=>$data));
        // dump($data);die;
        // if($row){
        //     $msg = "某些代理商有下级代理商，无法删除！";
        //     throw new \logicException($msg);
        //     return false;
        // }

        // foreach ($point as &$v) {
        //     if($v['point'] != 0){
        //         throw new Exception("代理商".$v['username']."有未处理的积分，无法删除！");
        //         return false;
        //     }
        // }
        foreach ($data as $k => $v) {
            try{
                $agent_account->delete(array('agent_id'=>$v));
                $agent_apply->delete(array('agent_id'=>$v));
            }
            catch(Exception $e){
                $agent_account->delete_msg = $e->getMessage();
                $agent_apply->delete_msg = $e->getMessage();
                return false;
            }
        }
        parent::delete(array('agent_id'=>$data));
    }
}
?>
