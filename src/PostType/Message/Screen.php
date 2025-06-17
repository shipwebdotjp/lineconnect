<?php

/**
 * Lineconnect SLC Message Class
 *
 * LINE Connect SLC Message
 *
 * @category Components
 * @package  SLC Message
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

namespace Shipweb\LineConnect\PostType\Message;

use Shipweb\LineConnect\Components\ReactJsonSchemaForm;
use lineconnect;
use lineconnectConst;
use lineconnectUtil;

class Screen {

	/**
	 * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
	 * 管理画面のトップメニューページを追加する関数
	 */
	static function set_plugin_menu() {
		// 設定のサブメニュー「LINE Connect」を追加
		$page_hook_suffix = add_submenu_page(
			// 親ページ：
			lineconnect::SLUG__DASHBOARD,
			// ページタイトル：
			__('LINE Connect Message', lineconnect::PLUGIN_NAME),
			// メニュータイトル：
			__('Messages', lineconnect::PLUGIN_NAME),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			'edit.php?post_type=' . lineconnectConst::POST_TYPE_MESSAGE,
			// メニューに紐づく画面を描画するcallback関数：
			false,
			// メニューの位置
			NULL
		);
		// remove_menu_page( lineconnect::SLUG__DM_FORM );
	}

	static function register_meta_box() {
		// 投稿ページと固定ページ両方でLINE送信チェックボックスを表示
		// $screens = lineconnect::get_option('send_post_types');
		// foreach ($screens as $screen) {
		add_meta_box(
			// チェックボックスのID
			lineconnect::META_KEY__MESSAGE_DATA,
			// チェックボックスのラベル名
			'LINE Connect Message',
			// チェックボックスを表示するコールバック関数
			array(self::class, 'show_json_edit_form'),
			// 投稿画面に表示
			lineconnectConst::POST_TYPE_MESSAGE,
			// 投稿画面の下に表示
			'advanced',
			// 優先度(default)
			'default'
		);
		// }
	}

	// 管理画面（投稿ページ）用にスクリプト読み込み
	static function wpdocs_selectively_enqueue_admin_script() {
		ReactJsonSchemaForm::wpdocs_selectively_enqueue_admin_script(lineconnectConst::POST_TYPE_MESSAGE);
	}

	/**
	 * JSONスキーマからフォームを表示
	 */
	static function show_json_edit_form() {
		$ary_init_data = array();
		$formName                         = lineconnect::PARAMETER__MESSAGE_DATA;
		$ary_init_data['formName']        = $formName;
		$schema_version = get_post_meta(get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true);

		$formData                         = get_post_meta(get_the_ID(), lineconnect::META_KEY__MESSAGE_DATA, true);
		$subSchema = Message::get_message_schema();
		$form = array();
		for ($i = 0; $i < 10; $i += 2) {
			$type_schema = lineconnectConst::$lineconnect_message_type_schema;
			$type_schema['title'] = sprintf('%s (%d/%d)', __('Message', lineconnect::PLUGIN_NAME), ($i / 2) + 1, 5);
			$form[] = array(
				'id' => 'type',
				'schema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_type_schema', $type_schema),
				'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_type_uischema', lineconnectConst::$lineconnect_message_type_uischema),
				'formData' => Message::get_form_type_data($formData[$i] ?? null, $schema_version),
				'props' => new \stdClass(),
			);
			$form[] = array(
				'id' => 'message',
				'schema' => ! empty($formData[$i]["type"]) ? $subSchema[$formData[$i]["type"]] : new \stdClass(),
				'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_uischema', lineconnectConst::$lineconnect_message_uischema),
				'formData' => Message::get_form_message_data($formData[$i + 1] ?? null, $schema_version),
				'props' => new \stdClass(),
			);
		}
		$ary_init_data['subSchema']          = $subSchema;

		$ary_init_data['form']        = $form;
		$ary_init_data['translateString'] = lineconnectConst::$lineconnect_rjsf_translate_string;
		// nonceフィールドを生成・取得
		$nonce_field = wp_nonce_field(
			lineconnect::CREDENTIAL_ACTION__MESSAGE,
			lineconnect::CREDENTIAL_NAME__MESSAGE,
			true,
			false
		);

		ReactJsonSchemaForm::show_json_edit_form($ary_init_data, $nonce_field);
	}

	/**
	 * 記事を保存
	 */
	static function save_post_message($post_ID, $post, $update) {
		if (isset($_POST[lineconnect::CREDENTIAL_NAME__MESSAGE]) && check_admin_referer(lineconnect::CREDENTIAL_ACTION__MESSAGE, lineconnect::CREDENTIAL_NAME__MESSAGE)) {
			$message_data = isset($_POST[lineconnect::PARAMETER__MESSAGE_DATA]) ?  stripslashes($_POST[lineconnect::PARAMETER__MESSAGE_DATA])  : '';
			if (! empty($message_data)) {
				$message_data_array = json_decode($message_data, true);
				if (! empty($message_data_array)) {
					update_post_meta($post_ID, lineconnect::META_KEY__MESSAGE_DATA, $message_data_array);
					update_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION, lineconnectConst::MESSAGE_SCHEMA_VERSION);
				} else {
					delete_post_meta($post_ID, lineconnect::META_KEY__MESSAGE_DATA);
					delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
				}
			} else {
				delete_post_meta($post_ID, lineconnect::META_KEY__MESSAGE_DATA);
				delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
			}
		}
	}

	// Ajaxでメッセージデータを返す
	static function ajax_get_slc_message() {
		$isSuccess = true;
		$formData = [];
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
			$isSuccess = false;
		}

		if (! isset($_POST['post_id']) || ! $_POST['post_id']) {
			$isSuccess = false;
		}


		if ($isSuccess) {
			$post_id = $_POST['post_id'];
			$formData  = get_post_meta($post_id, lineconnect::META_KEY__MESSAGE_DATA, true);
		}
		$result['result']  = $isSuccess ? 'success' : 'failed';
		$result['formData'] = $formData;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($result);
		wp_die();
	}
}
