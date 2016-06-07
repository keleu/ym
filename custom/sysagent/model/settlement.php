<?php
class sysagent_mdl_settlement extends dbeav_model{
    public function _filter($filter)
    {
        if( is_array($filter) &&  $filter['agent_seller'] )
        {
            $objMdlAgent = app::get('sysagent')->model('agents');
            $adata = $objMdlAgent->getList('agent_id',array('username|has'=>$filter['agent_seller']));
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
            // unset($filter['agent_seller']);
        }

        if($filter['tradecount'] == 'has'){
            $filter['tradecount|than'] = '0';
            unset($filter['tradecount']);
        }
        // dump($filter);die;
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
            'agent_seller'=>app::get('sysagent')->_('所属代理商'),
        ));
        return $columns;
    }
}