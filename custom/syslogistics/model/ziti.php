<?php
class syslogistics_mdl_ziti extends dbeav_model{
    /**
    * @var bool 不启用标签
    */
    var $has_tag = false;

    function __construct(&$app){
        parent::__construct($app);
    }

    public $has_many = array(
        'ziti_shopid' => 'ziti_shopid:contrast',
        'props' => 'ziti_shopid:replace:ziti_id^ziti_id',
    );

    /**
    * ps ：保存
    * Time：2016/05/13 09:13:40
    * @author jianghui
    */
    public function save(&$ziti,$mustUpdate = null, $mustInsert = false){
        $rs = parent::save($ziti, $mustUpdate);
        return $rs;
    }
}
