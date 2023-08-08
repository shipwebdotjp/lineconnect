<?php

/**
 * Accountlink
 *
 * ログイン後、LINE連携のためトークンを保存し、LINEへリダイレクトさせる
 *
 * @category   Components
 * @package    WordPress
 * @subpackage テーマ名
 * @author     名前 <foo.bar@example.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://example.com
 * @since      1.0.0
 */

/**
 * WordPressの基本機能を読み込み
 */
require_once '../../../wp-load.php';

$user_id = get_current_user_id();
//ログインしていない場合
if (!$user_id) {
	exit('Forbidden: Please Login first.');
}

//パラーメータからlinkTokenを取得
$linkToken = $_GET['linkToken'];

//nonce作成
$nonce = base64_encode(lineconnect_makeRandStr(32));

//WPのオプションとして保存
//key: lineconnect_nonce{nonce}
//Val: WordpressユーザーID
$option_key_nonce = "lineconnect_nonce" . $nonce;
update_option($option_key_nonce, $user_id);

//リダイレクトURLを作成
$redirect_url = "https://access.line.me/dialog/bot/accountLink?linkToken=$linkToken&nonce=$nonce";
header('Location: ' . $redirect_url);
exit();

//ランダムな文字列を返す関数(PHP7以上対応。PHP5.xの倍はrandom_intをrandへ変更）
function lineconnect_makeRandStr($length) {
	$str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
	$r_str = null;
	for ($i = 0; $i < $length; $i++) {
		$r_str .= $str[random_int(0, count($str) - 1)];
	}
	return $r_str;
}
