<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysagent_mdl_roles extends dbeav_model{

	####检查工作组名称
   function check_gname($name){
       $result = $this->getList('role_id',array('role_name'=>$name));
       if($result){

           return $result[0]['role_id'];
       }
       else{
           return false;
       }
   }
}
?>
