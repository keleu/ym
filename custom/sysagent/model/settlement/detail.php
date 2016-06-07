<?php
class sysagent_mdl_settlement_detail extends dbeav_model{
    public function _filter($filter)
    {
        if( is_array($filter) &&  $filter['username'] )
        {
            $objMdlAgent = app::get('sysagent')->model('agents');
            $adata = $objMdlAgent->getList('agent_id',array('username|has'=>$filter['username']));
            if($adata)
            {
                foreach($adata as $key=>$value)
                {
                    $shop[$key] = $value['agent_id'];
                }
                $filter['agent_seller'] = $shop;
            }
            else
            {
                $filter['agent_seller'] = "-1";
            }
            unset($filter['username']);
        }
        return parent::_filter($filter);
    }

    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v)
        {
            if(isset($v['searchtype']) && $v['searchtype'])
            {
                $columns[$k] = $v['label'];
            }
        }

        $columns = array_merge($columns, array(
            'username'=>app::get('sysagent')->_('卖出代理商'),
        ));
        return $columns;
    }
}