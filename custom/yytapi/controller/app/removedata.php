<?php

class yytapi_ctl_app_removedata{

  function __construct(){
    // 定义需要删除的表和需要保留的数据，或者不要删除的数据

  }

  /**
   * 删除测试数据
   * 只对本天的数据有效，如果超过了本天，则无效
   * Time：2016/02/05 11:53:29
   * @author li
  */
  function doremove(){
    //----------删除会员-------------
    /*$user_id = "176,208,210,211,212,213,214,216,217,218";//需要保留的数据
    $table_sysuser = [
      'sysuser_account',
      'sysuser_user',
      'sysuser_shop_fav',
      'sysuser_trustinfo',
      'sysuser_user_addrs',
      'sysuser_user_coupon',
      'sysuser_user_experience',
      'sysuser_user_fav',
      'sysuser_user_pointlog',
      'sysuser_user_points',
      'sysuser_user_trade_count'
    ];

    foreach ($table_sysuser as $key =>& $v) {
      $sql="delete from {$v} where user_id not in({$user_id})";
      db::connection()->executeQuery($sql);
    }
    echo "sysuser delete over <br/>";*/
    //end sysuser

    //-------删除售后信息-------
    /*$table_sysaftersales = [
      'sysaftersales_aftersales',
      'sysaftersales_refunds',
    ];

    foreach ($table_sysaftersales as $key =>& $v) {
      $sql="delete from {$v} where user_id not in({$user_id})";
      db::connection()->executeQuery($sql);
    }
    echo "sysaftersales delete over <br/>";*/
    //end sysaftersales

    //-------删除订单交易信息-------
    $table_systrade = [
      // 'systrade_cart',
      // 'systrade_log',
      // 'systrade_order',
      // 'systrade_order_complaints',
      'systrade_trade',
    ];

    /*$tid = "1604260755080208,1604251357280208";

    foreach ($table_systrade as $key =>& $v) {
      $sql="delete from {$v} where tid not in({$tid})";
      db::connection()->executeQuery($sql);
    }

    //删除订单日志表和订单明细表
    $sql="delete a from systrade_log a
        left join systrade_trade b on a.rel_id = b.tid
        where b.tid is null";
    db::connection()->executeQuery($sql);

    $sql="delete a from systrade_order a
        left join systrade_trade b on b.tid = a.tid
        where b.tid is null";
    db::connection()->executeQuery($sql);

    echo "systrade delete over <br/>";*/
    //end sysaftersales

    //-------删除商家交易信息-------

    /*$table_sysshop = [
      'sysshop_account',
      'sysshop_enterapply',
      'sysshop_seller',
    ];

    $table_sysshop_shop = [
      'sysshop_roles',
      'sysshop_shop_cat',
      'sysshop_shop_rel_brand',
      'sysshop_shop_rel_lv1cat',
    ];

    $table_sysshop_shop2seller = [
      'sysshop_shop',
      'sysshop_shop_info',
      'sysshop_shop_rel_seller',
    ];

    $seller_id = "23,26,27,28,29,36,37,38,39,43,44,45,46,47,48";
    $shop_id = "21,23,24,25,27,29,30,34,35,36,37,38,40,41";
    //查找对应的shop_id
    $sql="select group_concat(shop_id) as shop_id from sysshop_shop where seller_id in ({$seller_id}) order by shop_id asc";
    $arr = db::connection()->fetchAll($sql);
    $shop_id_2 = $arr[0]['shop_id'];
    // dump($arr);exit;

    foreach ($table_sysshop as $key =>& $v) {
      $sql="delete from {$v} where seller_id not in({$seller_id})";
      db::connection()->executeQuery($sql);
    }

    foreach ($table_sysshop_shop as $key =>& $v) {
      $sql="delete from {$v} where shop_id not in({$shop_id_2})";
      db::connection()->executeQuery($sql);
    }

    foreach ($table_sysshop_shop2seller as $key =>& $v) {
      $sql="delete from {$v} where seller_id not in({$seller_id}) and shop_id not in({$shop_id_2})";
      db::connection()->executeQuery($sql);
    }


    echo "sysshop delete over <br/>";*/
    //end sysaftersales

    echo 'end all';
  }

}
