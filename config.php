<?php
/**
 * Settings
 */

//ログインページへの相対パス(ログインページURLを変更している場合はこちらを変更してください。)
$login_path ="wp-login.php";

//アカウント連携／解除を開始するためのキーワード
const ACCOUNT_LINK_START_KEYWORD = 'アカウント連携';
//アカウント連携開始ダイアログのタイトル
const ACCOUNT_LINK_START_TITLE = 'アカウント連携'; //最大文字数：40
//アカウント連携開始ダイアログのメッセージ本文
const ACCOUNT_LINK_START_BODY = '連携を開始します。リンク先でログインしてください。'; //最大文字数：60
//アカウント連携開始ダイアログの開始ボタンラベル
const ACCOUNT_LINK_START_BUTTON = '連携開始'; //最大文字数：20
//アカウント連携完了時のメッセージ
const ACCOUNT_LINK_FINISH_BODY = 'アカウント連携が完了しました。'; //最大文字数：5000
//アカウント連携失敗時のメッセージ
const ACCOUNT_LINK_FAILED_BODY = 'アカウント連携に失敗しました。'; //最大文字数：5000
//アカウント連携解除開始時のタイトル
const ACCOUNT_UNLINK_START_TITLE = 'アカウント連携解除'; //最大文字数：40
//アカウント連携解除開始時のメッセージ本文
const ACCOUNT_UNLINK_START_BODY = 'すでにアカウント連携されています。連携を解除しますか？'; //最大文字数：60
//アカウント連携解除開始時の開始ボタンラベル
const ACCOUNT_UNLINK_START_BUTTON = '連携解除'; //最大文字数：20
//アカウント連携解除完了時のメッセージ
const ACCOUNT_UNLINK_FINISH_BODY = 'アカウント連係を解除しました。'; //最大文字数：5000
//アカウント連携解除失敗時のメッセージ
const ACCOUNT_UNLINK_FAILED_BODY = 'アカウント連携解除に失敗しました。'; //最大文字数：5000

?>