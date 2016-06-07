<?php

class syslogistics_mdl_ziti_shopid extends dbeav_model{

    /**
    * ps ：保存 暂时没用到
    * Time：2016/05/13 09:21:14
    * @author jianghui
    */
    function save(&$data,$mustUpdate = null, $mustInsert=false){
        dump($data);exit;
        $flagSaveSku = parent::save($data,$mustUpdate);
    }

}
