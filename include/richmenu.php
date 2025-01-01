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

	/**
	 * 	リッチメニューIDの更新
	 * @param array $channel チャネルデータ
	 * @param array $is_changed_richmenus 変更されたリッチメニュー名の配列
	 * @param array $ary_richmeneus リッチメニュー名をキー、リッチメニューIDを値とするリスト
	 * @return array $result 更新結果
	*/
	static function updateRichMenuId($channel, $is_changed_richmenus, $ary_richmeneus) {
		error_log(print_r($is_changed_richmenus, true));
		error_log(print_r($ary_richmeneus, true));
		$result = array();
		// LINE BOT SDK
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');
		$success_message = $error_message = "";
		if (!isset($channel['channel-access-token']) || !isset($channel['channel-secret'])) {
			// $target = ($state == 'linked' ? __('Linked', lineconnect::PLUGIN_NAME) : __('Unlinked', lineconnect::PLUGIN_NAME));
			return array( __('Channel access token or channel secret is not set.', lineconnect::PLUGIN_NAME));
		}

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret = $channel['channel-secret'];

		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

		if (in_array( 'unlinked', $is_changed_richmenus )) {
			$richmenu_id = $ary_richmeneus['unlinked'];
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
			$result[] = __('Unlinked', lineconnect::PLUGIN_NAME) . ": " . $success_message . $error_message;
			// remove unlinked from $is_changed_richmenus
			if (($key = array_search('unlinked', $is_changed_richmenus)) !== false) {
				unset($is_changed_richmenus[$key]);
			}
		}

		if( !empty($is_changed_richmenus) ){
			//ロールのリッチメニューIDを優先するが、ロールのリッチメニューIDが無い場合はlinkedのリッチメニューIDをセットする
			$line_user_ids_by_type = array(
				'delete' => array(),
				'linked' => array(),
			);
			foreach( $ary_richmeneus as $role => $richmenu_id ){
				if( $role == 'unlinked' ){
					continue;
				}
				$line_user_ids_by_type[ $role ] = array();
			}				

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
			// $line_user_ids = array(); //変更するLINEユーザーIDの配列
			$user_query = new WP_User_Query($args); //条件を指定してWordpressからユーザーを検索
			$users = $user_query->get_results(); //クエリ実行
			if (!empty($users)) {  //マッチするユーザーが見つかれば
				//ユーザーのメタデータを取得
				foreach ($users as $user) {
					$user_meta_line = $user->get(lineconnect::META_KEY__LINE);
					if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
						// $line_user_ids[] = $user_meta_line[$secret_prefix]['id'];
						// ユーザーのロールに応じてセットするリッチメニューIDを変える
						$isIdsetted = false;
						foreach( $user->roles as $role ){
							if( isset( $line_user_ids_by_type[ $role ] ) && $ary_richmeneus[$role] != "" ){
								$line_user_ids_by_type[ $role ][] = $user_meta_line[$secret_prefix]['id'];
								$isIdsetted = true;
								break;
							}
						}
						if( !$isIdsetted){
							if($ary_richmeneus['linked'] != ""){
								$line_user_ids_by_type['linked'][] = $user_meta_line[$secret_prefix]['id'];
							}else{
								$line_user_ids_by_type['delete'][] = $user_meta_line[$secret_prefix]['id'];
							}
						}
					}
				}

				foreach( $line_user_ids_by_type as $state => $line_user_ids ){
					if( empty( $line_user_ids ) ){
						continue;
					}
					$target_cnt = count($line_user_ids) . "人";

					//最大500人なので、500個ごとに配列を分割して変更
					foreach (array_chunk($line_user_ids, 500) as $line_user_id_chunk) {
						if ($state == 'delete') {
							//複数のユーザーのリッチメニューのリンクを解除する
							$response = $bot->bulkUnlinkRichMenu($line_user_id_chunk);
						} else {
							//リッチメニューと複数のユーザーをリンクする
							$richmenu_id = $ary_richmeneus[$state];
							$response = $bot->bulkLinkRichMenu($line_user_id_chunk, $richmenu_id);
						}
					}
					//送信に成功した場合
					if ($response->getHTTPStatus() === 202) {
						$success_message = "(" . $target_cnt . ")";
					} else {
						$error_message = __('Failed', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
					}
					$result[] = ($state == 'linked' ? __('Linked', lineconnect::PLUGIN_NAME) : ($state == 'delete' ? __('Deleted', lineconnect::PLUGIN_NAME) : translate_user_role($state))) . ": " . $success_message . $error_message. implode(',',$line_user_id_chunk);
				}
			}
		}
		return $result;
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
				$target_richmenu_id = "";
				$user_meta_line = get_user_meta($userid, lineconnect::META_KEY__LINE, true);
				if (isset($user_meta_line[$secret_prefix]) && isset($user_meta_line[$secret_prefix]['id'])) {

					foreach( $user->roles as $role ){
						if( isset( $channel[$role. '-richmenu'] ) && $channel[$role. '-richmenu'] != "") {
							$target_richmenu_id = $channel[$role. '-richmenu'];
							break;
						}
					}
					if( $target_richmenu_id == "" ){
						$target_richmenu_id = $channel['linked-richmenu'];
					}
					if ($target_richmenu_id != "" && $user_meta_line[$secret_prefix]['id']) {
						$response = $bot->linkRichMenu($user_meta_line[$secret_prefix]['id'], $target_richmenu_id);
						// error_log("userid: " . $userid . " LINE userid: ". $user_meta_line[$secret_prefix]['id']. " richmenu:".$target_richmenu_id);
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

	/**
	 * ユーザーロールが変化したフックを受け取り、リッチメニューを変更する
	 * @param int $user_id ユーザーID
	 * @param string $role ロール
	 * @param string[] $old_roles 旧ロール
	 */
	static function change_user_role($user_id, $role, $old_roles ){
		self::link_richmenu($user_id);
	}
}
