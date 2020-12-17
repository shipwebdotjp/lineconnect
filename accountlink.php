<?php

//WordPressの基本機能を読み込み
require_once ('../../../wp-blog-header.php');

$user_id = get_current_user_id();
//ログインしていない場合
if(!$user_id){
	exit('Forbidden: Please Login first.');
}

//パラーメータからlinkTokenを取得
$linkToken = $_GET['linkToken'];

//nonce作成
$nonce = base64_encode(lineconnect_makeRandStr(32));

//WPのオプションとして保存
$option_key_nonce = "lineconnect_nonce".$nonce;
update_option($option_key_nonce, $user_id);

//リダイレクトURLをセット
$redirect_url = "https://access.line.me/dialog/bot/accountLink?linkToken=$linkToken&nonce=$nonce";

header('Location: ' . $redirect_url);
exit();

//ランダムな文字列を返す
function lineconnect_makeRandStr($length) {
    $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
    $r_str = null;
    for ($i = 0; $i < $length; $i++) {
        $r_str .= $str[rand(0, count($str) - 1)];
    }
    return $r_str;
}
