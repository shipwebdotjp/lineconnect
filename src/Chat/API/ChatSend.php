<?php

namespace Shipweb\LineConnect\Chat\API;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\Message\LINE\Sender;

class ChatSend {

	// チャット送信
	static function ajax_chat_send() {
		$isSuccess = true;
		$ary_error_message = [];
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
			$ary_error_message[] = __('User is not logged in.', lineconnect::PLUGIN_NAME);
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
			$ary_error_message[] = __('User is not authorized.', lineconnect::PLUGIN_NAME);
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
			$ary_error_message[] = __('Nonce is not set.', lineconnect::PLUGIN_NAME);
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		$result = \Shipweb\LineConnect\Utilities\Guard::check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST);
		if ($result['result'] === 'failed') {
			$isSuccess = false;
			$ary_error_message = array_merge($ary_error_message, $result['error']);
		}

		if ($isSuccess) {
			$ary_success_message = array();

			$send_count          = 0;

			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			$to             = isset($_POST['to']) ? $_POST['to'] : null;
			$messages_formdata              = isset($_POST['messages']) && is_array($_POST['messages']) ? array_map('stripslashes_deep', $_POST['messages']) : [];
			$notificationDisabled = isset($_POST['notificationDisabled']) && $_POST['notificationDisabled'] == 1;


			if (! empty($channel_prefix) && ! empty($to) && ! empty($messages_formdata)) {

				$error_message = $success_message = '';

				$channel              = lineconnect::get_channel($channel_prefix);
				if (!$channel) {
					$isSuccess = false;
					$ary_error_message[] = __('Channel not found.', lineconnect::PLUGIN_NAME);
				} else {
					$channel_access_token = $channel['channel-access-token'];
					$channel_secret       = $channel['channel-secret'];
					$secret_prefix        = substr($channel['channel-secret'], 0, 4);
				}

				if (isset($channel_access_token) && isset($channel_secret) && strlen($channel_access_token) > 0 && strlen($channel_secret) > 0) {
					if (is_array($messages_formdata)) {
						$message = Builder::get_line_message_builder($messages_formdata, Sender::make_injection_data($channel, $to));
						$response = Sender::sendPushMessage($channel, $to, $message, $notificationDisabled);
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
			if ($isSuccess) {
				wp_send_json_success($result);
			} else {
				wp_send_json_error($result);
			}
		} else {
			wp_send_json_error([
				'result' => 'failed',
				'error' => $ary_error_message,
			]);
		}
	}
}
