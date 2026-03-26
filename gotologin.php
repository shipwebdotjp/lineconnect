<?php
// WordPressの基本機能を読み込み
require_once '../../../wp-load.php';
// LINE Connectを読み込み
use Shipweb\LineConnect\Bot\Account;

Account::goto_login_page();
