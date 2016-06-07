<?php

class yytapi_ctl_loginurl_login extends topc_controller{

  function __construct(){
    
  }

	function callBack(){
		//解密
		$public_key_path="/public/urlKey/rsa_public_key.pem";
		$public_key_path=ROOT_DIR.$public_key_path;
		$public_key = file_get_contents($public_key_path);
		$pu_key = openssl_get_publickey($public_key);
		$encrypted = $_GET['poss'];
		openssl_public_decrypt(base64_decode($encrypted),$decrypted,$pu_key);
		
		//登录
		try
        {
			if (userAuth::attempt($_GET['user'], $decrypted))
	        {
	            return redirect::action('topc_ctl_default@index');
	        }
	    }
        catch(Exception $e)
        {
            return redirect::action('topc_ctl_passport@signin');
        }
	}
 }