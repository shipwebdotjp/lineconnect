<?php

//WordPressの基本機能を読み込み
require_once ('../../../wp-load.php');
//設定ファイルを読み込み
require_once ('config.php');

//パラーメータからリダイレクト先を取得
$redirect_to = $_GET['redirect_to'];

if($redirect_to){
	$user_id = get_current_user_id();
	//ログインしていない場合ログインページへリダイレクト
	if(!$user_id){

		//COOKIEにリダイレクト先を格納
		setcookie ('line_connect_redirect_to',$redirect_to, 0,'/',"",TRUE,TRUE);

		//リダイレクトURLをセット
		$site_url=get_site_url(null, '/');
		$redirect_url = $site_url.$login_path;

		//ログインページへリダイレクトさせる
		header('Location: ' . $redirect_url);
	}else{
		//ログインしている場合は直接アカウントリンク用のページへ
		header('Location: ' . $redirect_to);
	}


}else{
	print "Bad args";
}
exit();

