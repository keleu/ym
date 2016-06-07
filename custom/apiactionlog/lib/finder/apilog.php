<?php
class apiactionlog_finder_apilog{

    public function __construct($app)
    {
        $this->app = $app;
    }

    // var $column_editbutton = '操作';
    // public $column_editbutton_order = COLUMN_IN_HEAD;
    // public $column_editbutton_width = 70;
    /*public function column_editbutton($row)
    {
        $model = app::get('apiactionlog')->model('apilog');
        $log = $model->getList("apilog",array('apilog_id'=>$row['apilog_id']));
        if (($row['status'] == 'fail' || $row['status'] == 'sending') && $row['api_type'] == 'request'){
            $str_operation = '<a icon="sss.ccc" target="{onComplete:function(){if (finderGroup&&finderGroup[\''. $_GET['_finder']['finder_id'] . '\']) finderGroup[\''. $_GET['_finder']['finder_id'] . '\'].refresh();}}" href="index.php?app=apiactionlog&ctl=admin_apilog&act=re_request&p[0]=' . $log[0]['apilog'] . '" label="重试"><span><!--todo ICON-->' . app::get('b2c')->_('重试') . '</span></a>';
        }else{
            $str_operation = "";
        }

        return $str_operation;
    }

    var $detail_edit = '详情';
    public $detail_edit_order = COLUMN_IN_HEAD;
    public $detail_edit_width = 30;
    public function detail_edit($id){
        $render = app::get('apiactionlog')->render(); 
        $api_model = kernel::single('apiactionlog_mdl_apilog');
        $shopObj = app::get('b2c')->model('shop');
        $loglist = $api_model->getList('*',array('apilog_id'=>$id));
        $status = $loglist[0]['status'];
        $msg = $loglist[0]['msg'];
        $apilog=$loglist[0];

        // 批量同步成功的msg_id
        // if (isset($loglist[0]['msg_id'])){
        //     $render->pagedata['batch_msg_id'] = $loglist[0]['msg_id'];
        // }

        $apilog['params'] = unserialize($loglist[0]['params']);
        $apilog['send_api_name'] = $loglist[0]['worker'];// API名称
        foreach($apilog['params'] as $key=>$val){
            $str_params .= $key."=".$val.",<br>  ";
        }

        $apilog['send_api_params'] = $str_params;
        if($status == 'fail' && $msg){
            $result = $msg;
        }elseif($status != 'success'){
            $result = $msg;
        }else{
            $result = '成功';
        }

        $apilog['msg'] = $result;
        $apilog['api_arr'] = $api_arr;
        $render->pagedata['apilog'] = $apilog;
        return $render->display('admin/detail.html');
    }*/



}
