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
use Shipweb\LineConnect\Bot\Account;

Account::account_link_page();
