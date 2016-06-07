<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/* TODO: Add code here */
class sysagent_finder_roles{
    var $column_control = '角色操作';
    function __construct($app){
        $this->app= app::get('sysagent');
        // $this->obj_roles = kernel::single('sysagent_roles');
    }

    function column_control(&$colList, $list)
    {
        foreach($list as $k => $row)
        {
            $pagedata['role_id'] = $row['role_id'];
            $colList[$k] = view::make('sysagent/users/href.html', $pagedata)->render();
        }
    }

    function detail_indo($param_id){
        $huanhuoRate = config::get('authconf');
        $rolestl = $this->app->model('roles');
        $sdf_roles = $rolestl->dump($param_id);
        $pagedata['roles']=$sdf_roles;
        $menu_workground = unserialize($sdf_roles['workground']);
        $temp_title='';
        $pagedata['workgrounds']=array();
        if(workground){
            foreach ($huanhuoRate as & $v) {
                foreach ($v as $k=> & $row) {
                    $temp_title=$row['mgrpname']?$row['mgrpname']:$temp_title;
                    if(in_array($row['workground'],(array)$menu_workground)){
                        $row['checked'] = 1;
                        $pagedata['workgrounds'][$temp_title][]=$row;
                    }
                }
            }
        }
        return view::make('sysagent/users/users_roles.html', $pagedata)->render(); 
    }
}
?>
