<?php

/**
 * Lineconnect
 * 管理画面でのLINEメッセージ画面
 */

namespace Shipweb\LineConnect\Chat;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\PostType\Message\Message as SLCMessage;
use Shipweb\LineConnect\PostType\Message\Schema as SLCMessageSchema;
use Shipweb\LineConnect\Components\ReactJsonSchemaForm;

class Screen {
	static function initialize() {
	}

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
			__('LINE Connect Chat', lineconnect::PLUGIN_NAME),
			// メニュータイトル：
			__('Chat', lineconnect::PLUGIN_NAME),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			lineconnect::SLUG__CHAT_SCREEN,
			// メニューに紐づく画面を描画するcallback関数：
			array(self::class, 'show_chat'),
			// メニューの位置
			NULL
		);
		add_action("admin_print_styles-{$page_hook_suffix}", array(self::class, 'wpdocs_plugin_admin_styles'));
		add_action("admin_print_scripts-{$page_hook_suffix}", array(self::class, 'wpdocs_plugin_admin_scripts'));
	}

	/**
	 * CHAT送信画面を表示
	 */
	static function show_chat() {
		$ary_init_data = array();
		// プラグインのオプション
		// $ary_init_data['plugin_options'] = lineconnect::get_all_options();
		$ary_init_data['channels']   = lineconnect::get_all_channels();
		$ary_init_data['ajaxurl']    = admin_url('admin-ajax.php');
		$ary_init_data['ajax_nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
		$ary_init_data['downloadurl'] = admin_url('admin-post.php?action=' . lineconnect::SLUG__CONTENT_DOWNLOAD);

		/*
		$line_id                     = isset($_GET['line_id']) ? $_GET['line_id'] : array();
		$channel_prefix              = isset($_GET['channel_prefix']) ? $_GET['channel_prefix'] : array();

		if (! empty($line_id) && ! empty($channel_prefix)) {
			$results     = \Shipweb\LineConnect\Utilities\LineId::line_id_row($line_id, $channel_prefix);
			if ($results) {
				$results['profile']       = json_decode($results['profile'] ?? '', true);
				$results['tags']          = json_decode($results['tags'] ?? '', true);
				$ary_init_data['line_id'] = $results;
			} else {
				$ary_init_data['line_id'] = array();
			}
		} else {
			$ary_init_data['line_id'] = array();
		}

		$ary_init_data['channel_prefix'] = $channel_prefix;
		*/
		$messageFormData = [];
		$formName                         = 'chatform-data';
		$ary_init_data['formName']        = $formName;
		$messageSubSchema = SLCMessage::get_message_schema();
		$messageForm = array();
		for ($i = 0; $i < 10; $i += 2) {

			$type_schema = SLCMessageSchema::get_message_type_schema();
			$type_schema['title'] = sprintf('%s (%d/%d)', __('Message', lineconnect::PLUGIN_NAME), ($i / 2) + 1, 5);
			$messageForm[] = array(
				'id' => 'type',
				'schema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_type_schema', $type_schema),
				'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_type_uischema', SLCMessageSchema::get_message_type_uischema()),
				'formData' => SLCMessage::get_form_type_data($messageFormData[$i] ?? null, null),
				'props' => new \stdClass(),
			);
			$messageForm[] = array(
				'id' => 'message',
				'schema' => ! empty($messageFormData[$i]["type"]) ? $messageSubSchema[$messageFormData[$i]["type"]] : new \stdClass(),
				'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_uischema', SLCMessageSchema::get_message_uischema()),
				'formData' => SLCMessage::get_form_message_data($messageFormData[$i + 1] ?? null, null),
				'props' => new \stdClass(),
			);
		}
		$ary_init_data['messageSubSchema']          = $messageSubSchema;
		$ary_init_data['messageForm']        = $messageForm;
		$ary_init_data['translateString'] = ReactJsonSchemaForm::get_translate_string();

		$slc_messages = [];
		foreach (SLCMessage::get_lineconnect_message_name_array() as $post_id => $title) {
			$slc_messages[] = array(
				'post_id' => $post_id,
				'title' => $title,
			);
		}
		$ary_init_data['slc_messages'] = $slc_messages;
		$inidata = json_encode($ary_init_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		// error_log( print_r( $ary_init_data['line_id'], true ) );
		echo <<< EOM
<div id="slc_chat_root"></div>
<script>
var lc_initdata = {$inidata};
</script>
EOM;
	}

	// チャット送信
	static function ajax_chat_send() {
		$isSuccess = true;
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

		if ($isSuccess) {
			$ary_success_message = array();
			$ary_error_message   = array();
			$send_count          = 0;

			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			$to             = isset($_POST['to']) ? $_POST['to'] : null;
			$messages_formdata              = isset($_POST['messages']) ? array_map('stripslashes_deep', $_POST['messages']) : [];
			$notificationDisabled = isset($_POST['notificationDisabled']) && $_POST['notificationDisabled'] == 1;


			if (! empty($channel_prefix) && ! empty($to) && ! empty($messages_formdata)) {

				$error_message = $success_message = '';

				$channel              = lineconnect::get_channel($channel_prefix);
				$channel_access_token = $channel['channel-access-token'];
				$channel_secret       = $channel['channel-secret'];
				$secret_prefix        = substr($channel['channel-secret'], 0, 4);

				$send_count = 0;
				if (strlen($channel_access_token) > 0 && strlen($channel_secret) > 0) {
					if (is_array($messages_formdata)) {
						$message = Builder::get_line_message_builder($messages_formdata, Builder::make_injection_data($channel, $to));
						$response = Builder::sendPushMessage($channel, $to, $message, $notificationDisabled);
						/*
						foreach ($messages as $index => $message) {
							$type = $message['type'];
							if ($type == 'message') {
								// $body = liceconnectSchedule::replace_placeholder($message,$reservation);
								$line_message = Builder::createTextMessage(stripslashes($message['text']));
							}
							$response = Builder::sendPushMessage($channel, $to, $line_message); // メッセージ送信
							if ($response['success'] === false) {
								$isSuccess = false;
								// $ary_error_message[ $index ] = '送信に失敗しました。LINEメッセージに問題がありました。' . $response['message'];
							} else {
								++$send_count;
							}
						}
							*/
						if ($response['success']) {
							$success_message = __('Sent a LINE message.', lineconnect::PLUGIN_NAME);
						} else {
							$error_message = __('Faild to sent a LINE message', lineconnect::PLUGIN_NAME);
						}
					} else {
						$isSuccess           = false;
						$ary_error_message[] = __('Messages is not Array.', lineconnect::PLUGIN_NAME);
					}
				} else {
					$isSuccess           = false;
					$ary_error_message[] = __('Channel not found.', lineconnect::PLUGIN_NAME);
				}
				// 送信に成功した場合
				if (! empty($success_message)) {
					$ary_success_message[] = $channel['name'] . ': ' . $success_message;
				} else {
					$isSuccess           = false;
					$ary_error_message[] = $channel['name'] . ': ' . $error_message;
				}
			} else {
				$isSuccess           = false;
				$ary_error_message[] = __('Channel or User is not set.', lineconnect::PLUGIN_NAME);
			}

			$result['result']  = $isSuccess ? 'success' : 'failed';
			$result['success'] = $ary_success_message;
			$result['error']   = $ary_error_message;
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode($result);
			wp_die();
		}
	}

	// 管理画面用にスクリプト読み込み
	static function wpdocs_plugin_admin_scripts() {
		$chat_js = 'frontend/chat/dist/slc_chat.js';
		wp_enqueue_script(lineconnect::PLUGIN_PREFIX . 'chat', LineConnect::plugins_url($chat_js), array('wp-element', 'wp-i18n'), filemtime(LineConnect::getRootDir() . $chat_js), true);
		// JavaScriptの言語ファイル読み込み
		wp_set_script_translations(lineconnect::PLUGIN_PREFIX . 'chat', lineconnect::PLUGIN_NAME, LineConnect::getRootDir() . 'frontend/chat/languages');
	}

	// 管理画面用にスタイル読み込み
	static function wpdocs_plugin_admin_styles() {
		$chat_css = 'frontend/chat/dist/style.css';
		wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'admin-css', LineConnect::plugins_url($chat_css), array(), filemtime(LineConnect::getRootDir() . $chat_css));
	}
}
