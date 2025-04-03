<?php

/**
 * Lineconnect
 * 管理画面でのLINEメッセージ画面
 */
class lineconnectDm {
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
			__('LINE Connect Direct Message', lineconnect::PLUGIN_NAME),
			// メニュータイトル：
			__('Direct Message', lineconnect::PLUGIN_NAME),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			lineconnect::SLUG__DM_FORM,
			// メニューに紐づく画面を描画するcallback関数：
			array('lineconnectDm', 'show_dm'),
			// メニューの位置
			40
		);
		add_action("admin_print_styles-{$page_hook_suffix}", array('lineconnectDm', 'wpdocs_plugin_admin_styles'));
		add_action("admin_print_scripts-{$page_hook_suffix}", array('lineconnectDm', 'wpdocs_plugin_admin_scripts'));
		// remove_menu_page( lineconnect::SLUG__DM_FORM );
	}

	/**
	 * DM送信画面を表示
	 */
	static function show_dm() {
		$ary_init_data = array();
		// プラグインのオプション
		// $ary_init_data['plugin_options'] = lineconnect::get_all_options();
		$ary_init_data['channels']   = lineconnect::get_all_channels();
		$ary_init_data['ajaxurl']    = admin_url('admin-ajax.php');
		$ary_init_data['ajax_nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
		$line_id                     = isset($_GET['line_id']) ? $_GET['line_id'] : array();
		$channel_prefix              = isset($_GET['channel_prefix']) ? $_GET['channel_prefix'] : array();

		if (! empty($line_id) && ! empty($channel_prefix)) {
			$results     = lineconnectUtil::line_id_row($line_id, $channel_prefix);
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

		$inidata = json_encode($ary_init_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		// error_log( print_r( $ary_init_data['line_id'], true ) );
		echo <<< EOM
<div id="line_dm_root"></div>
<script>
var lc_initdata = JSON.parse(`{$inidata}`);
</script>
EOM;
	}

	// チャット送信
	static function ajax_dm_send() {
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
			$messages       = isset($_POST['messages']) ? $_POST['messages'] : null;

			if (! empty($channel_prefix) && ! empty($to) && ! empty($messages)) {

				$error_message = $success_message = '';

				$channel              = lineconnect::get_channel($channel_prefix);
				$channel_access_token = $channel['channel-access-token'];
				$channel_secret       = $channel['channel-secret'];
				$secret_prefix        = substr($channel['channel-secret'], 0, 4);

				$send_count = 0;
				if (strlen($channel_access_token) > 0 && strlen($channel_secret) > 0) {
					if (is_array($messages)) {
						require_once plugin_dir_path(__FILE__) . 'message.php';
						foreach ($messages as $index => $message) {
							$type = $message['type'];
							if ($type == 'message') {
								// $body = liceconnectSchedule::replace_placeholder($message,$reservation);
								$line_message = lineconnectMessage::createTextMessage(stripslashes($message['text']));
							}
							$response = lineconnectMessage::sendPushMessage($channel, $to, $line_message); // メッセージ送信
							if ($response['success'] === false) {
								$isSuccess = false;
								// $ary_error_message[ $index ] = '送信に失敗しました。LINEメッセージに問題がありました。' . $response['message'];
							} else {
								++$send_count;
							}
						}
						if ($send_count > 0) {
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
		/*
		$chat_js = "line-bulkmessage/build/static/js/2.55a144b5.chunk.js";
		wp_enqueue_script(lineconnect::PLUGIN_PREFIX.'chat-2', plugins_url($chat_js, dirname(__FILE__)),array('wp-element'),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_js),true);
		$chat_js = "line-bulkmessage/build/static/js/main.baed2f09.chunk.js";
		wp_enqueue_script(lineconnect::PLUGIN_PREFIX.'chat', plugins_url($chat_js, dirname(__FILE__)),array('wp-element'),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_js),true);
		*/
		$dm_js = 'line-dm/dist/slc_dm.js';
		wp_enqueue_script(lineconnect::PLUGIN_PREFIX . 'dm', plugins_url($dm_js, __DIR__), array('wp-element', 'wp-i18n'), filemtime(plugin_dir_path(__DIR__) . $dm_js), true);
		// JavaScriptの言語ファイル読み込み
		wp_set_script_translations(lineconnect::PLUGIN_PREFIX . 'dm', lineconnect::PLUGIN_NAME, plugin_dir_path(__DIR__) . 'line-dm/languages');
	}

	// 管理画面用にスタイル読み込み
	static function wpdocs_plugin_admin_styles() {
		$dm_css = 'line-dm/dist/style.css';
		wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'admin-css', plugins_url($dm_css, __DIR__), array(), filemtime(plugin_dir_path(__DIR__) . $dm_css));
	}
}
