<?php
class topm_ctl_topics extends topm_controller{

    public function __construct($app)
    {
        parent::__construct();
        $this->setLayoutFlag('topics');
    }

    function index($catId)
    {
        return $this->page('topm/topics.html');
    }
}
