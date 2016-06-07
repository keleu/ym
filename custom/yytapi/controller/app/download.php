<?php

class yytapi_ctl_app_download {

  function __construct(){
    //获取当前上传的最新版本号和下载地址
    $this->android_ver = 'Android下载';
    $this->ios_ver = 'iphone下载';

    $this->android_url = kernel::base_url().'/appsource/android_hld.apk';
    $this->ios_url = 'https://www.pgyer.com/ceuJ';
  }
  /**
   * 下载app
   * Time：2016/01/27 13:51:03
   * @author li
   * @param 参数类型
   * @return 返回值类型
  */
  public function download()
  {
    $pagedata['version'] = [
      'ios'=>[
        'ver'=>$this->ios_ver,
        'url'=>$this->ios_url,
      ],
      'android'=>[
        'ver'=>$this->android_ver,
        'url'=>$this->android_url,
      ]
    ];
    echo view::make('yytapi/appdownload.html', $pagedata);
  }
}
