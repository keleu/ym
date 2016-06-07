<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysagent_finder_audit_cancel {
    public $column_edit = '操作';
    public $column_edit_order = 3;
    public $column_edit_width = 200;


    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $colList[$k] = $this->_column_edit($row);
        }
    }

     public function _column_edit($row)
    {
        $sellerModle = app::get('sysagent')->model('audit_cancel');
        if($row['apply_status'] == '0')
        {
           $url = '?app=sysagent&ctl=admin_apply&act=gocancel&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['audit_id'].'&p[1]='.$row['agent_id'];
            $title = app::get('sysagent')->_('同意');
            $target = 'dialog::  {title:\''.app::get('sysagent')->_('同意').'\', width:700, height:500}';
            $return .= ' <a href="' . $url . '" target="' . $target . '">' . $title . '</a>';

            $url = '?app=sysagent&ctl=admin_apply&act=doStopCancel&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['audit_id'].'&p[1]='.$row['agent_id'];
            $title = app::get('sysagent')->_('拒绝');
            $target = 'dialog::  {title:\''.app::get('sysagent')->_('拒绝').'\', width:300, height:150}';
            $return .= ' | <a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
        }

        return $return;
    }

}
