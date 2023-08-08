<?php

/**
 * Lineconnect Richmenu Class
 *
 * リッチメニューの追加、削除、変更を行うクラス
 *
 * @category Components
 * @package  Richmenu
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

class lineconnectRichmenu {

	/*
	リッチメニューIDの更新
	*/
	static function updateRichMenuId($channel, $state, $richmenu_id) {
		// LINE BOT SDK
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');
		$success_message = $error_message = "";
		if (!isset($channel['channel-access-token']) || !isset($channel['channel-secret'])) {
			$target = ($state == 'linked' ? __('Linked', lineconnect::PLUGIN_NAME) : __('Unlinked', lineconnect::PLUGIN_NAME));
			return $target . ": " . __('Channel access token or channel secret is not set.', lineconnect::PLUGIN_NAME);
		}

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret = $channel['channel-secret'];

		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

		if ($state == 'linked') {
			$secret_prefix = substr($channel_secret, 0, 4);
			//連携済みユーザーのリッチメニューIDを変更
			$args = array(
				'meta_query' => array(
					array(
						'key'     => lineconnect::META_KEY__LINE,
						'compare' => 'EXISTS'
					)
				),
				//'role' => $role,
				'fields' => 'all_with_meta'
			);
			$line_user_ids = array(); //変更するLINEユーザーIDの配列
			$user_query = new WP_User_Query($args); //条件を指定してWordpressからユーザーを検索
			$users = $user_query->get_results(); //クエリ実行
			if (!empty($users)) {  //マッチするユーザーが見つかれば
				//ユーザーのメタデータを取得
				foreach ($users as $user) {
					$user_meta_line = $user->get(lineconnect::META_KEY__LINE);
					if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
						$line_user_ids[] = $user_meta_line[$secret_prefix]['id'];
					}
				}
				$target_cnt = count($line_user_ids) . "人";

				//最大500人なので、500個ごとに配列を分割して変更
				foreach (array_chunk($line_user_ids, 500) as $line_user_id_chunk) {
					if ($richmenu_id == "") {
						//複数のユーザーのリッチメニューのリンクを解除する
						$response = $bot->bulkUnlinkRichMenu($line_user_id_chunk);
					} else {
						//リッチメニューと複数のユーザーをリンクする
						$response = $bot->bulkLinkRichMenu($line_user_id_chunk, $richmenu_id);
					}
				}
				//送信に成功した場合
				if ($response->getHTTPStatus() === 202) {
					$success_message = "(" . $target_cnt . ")";
				} else {
					$error_message = __('Failed', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
				}
			}
		} else {
			if ($richmenu_id == "") {
				//デフォルトのリッチメニューを解除
				$response = $bot->cancelDefaultRichMenuId();
			} else {
				//デフォルトのリッチメニューにセット
				$response = $bot->setDefaultRichMenuId($richmenu_id);
			}
			//送信に成功した場合
			if ($response->getHTTPStatus() === 200) {
				$success_message = "";
			} else {
				$error_message = __('Failed', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
			}
		}

		return ($state == 'linked' ? __('Linked', lineconnect::PLUGIN_NAME) : __('Unlinked', lineconnect::PLUGIN_NAME)) . ": " . $success_message . $error_message;
	}

	/*
	リッチメニューIDの確認
	*/
	static function checkRichMenuId($channel, $richmenu_id) {
		// LINE BOT SDK
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');

		if (isset($channel['channel-access-token']) && isset($channel['channel-secret'])) {
			$channel_access_token = $channel['channel-access-token'];
			$channel_secret = $channel['channel-secret'];

			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

			if ($richmenu_id == "") {
				return true;
			} else {
				//リッチメニューを取得
				$response = $bot->getRichMenu($richmenu_id);
			}
			//送信に成功した場合
			if ($response->getHTTPStatus() === 200) {
				return true;
			} else {
				return array(false, $response->getJSONDecodedBody()['message']);
			}
		} else {
			return array(false, __('Channel access token or channel secret is not set.', lineconnect::PLUGIN_NAME));
		}
	}

	/*
	* リッチメニューとユーザーをリンクする
	*/
	static function link_richmenu($userid) {
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');

		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			$channel_access_token = $channel['channel-access-token'];
			$channel_secret = $channel['channel-secret'];

			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

			$secret_prefix = substr($channel_secret, 0, 4);

			$user = get_userdata($userid);
			if ($user) {
				$target_richmenu_id = $channel['linked-richmenu'];

				$user_meta_line = get_user_meta($userid, lineconnect::META_KEY__LINE, true);
				if (isset($user_meta_line[$secret_prefix])) {
					if ($target_richmenu_id != "" && $user_meta_line[$secret_prefix]['id']) {
						$response = $bot->linkRichMenu($user_meta_line[$secret_prefix]['id'], $target_richmenu_id);
					}
				}
			}
		}
	}

	//リッチメニューとユーザーのリンクを解除する
	static function line_unlink_richmenu($userid, $target_secret_prefix = "all") {
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');

		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			$channel_access_token = $channel['channel-access-token'];
			$channel_secret = $channel['channel-secret'];

			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

			$secret_prefix = substr($channel_secret, 0, 4);

			//対象のチャンネルかどうかチェック
			if ($target_secret_prefix == $secret_prefix || $target_secret_prefix == 'all') {
				$user_meta_line = get_user_meta($userid, lineconnect::META_KEY__LINE, true);
				if (isset($user_meta_line[$secret_prefix])) {
					if ($user_meta_line[$secret_prefix]['id']) {
						$response = $bot->unlinkRichMenu($user_meta_line[$secret_prefix]['id']);
					}
				}
			}
		}
	}
}
