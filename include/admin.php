<?php

/**
 * Lineconnect Admin Class
 *
 * Admin Class
 *
 * @category Components
 * @package  Admin
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

class lineconnectAdmin {


	// 連携状態を表示するカラムを追加
	static function lc_manage_columns($columns) {
		// 追加するカラム。 カラム名（任意） => カラムのラベル（任意）
		$add_columns = array(
			'lc_islinked' => __('LINE', lineconnect::PLUGIN_NAME)
		);

		return array_merge($columns, $add_columns);
	}

	// 管理画面のカラムに連携状態を表示させる
	static function lc_manage_custom_columns($output, $column_name, $user_id) {
		switch ($column_name) {
			case 'lc_islinked':
				$linked_label = __('&#9745;', lineconnect::PLUGIN_NAME);
				$unlinked_label = __('&#9744;', lineconnect::PLUGIN_NAME);
				$ary_output = array();
				foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
					$secret_prefix = substr($channel['channel-secret'], 0, 4);
					$user_meta_line = get_user_meta($user_id, lineconnect::META_KEY__LINE, true);
					if ($user_meta_line && isset($user_meta_line[$secret_prefix]) && isset($user_meta_line[$secret_prefix]['id'])) {
						$line_sendmessage_url = add_query_arg(array('users' => $user_id, 'channel_ids' => $channel_id), admin_url('admin.php?page=' . lineconnect::SLUG__CHAT_FORM));
						$ary_output[] = "<a href=\"" . $line_sendmessage_url . "\" title=\"" . (isset($user_meta_line[$secret_prefix]['displayName']) ? $user_meta_line[$secret_prefix]['displayName'] : "") . "\">{$linked_label}</a>";
					} else {
						$ary_output[] = $unlinked_label;
					}
				}
				return implode("/", $ary_output);
		}
		return $output;
	}

	//ユーザー一括操作にメッセージ送信を追加
	static function add_bulk_users_sendmessage($actions) {
		$actions['lc_linechat'] = __('Send LINE Message', lineconnect::PLUGIN_NAME);
		return $actions;
	}

	//ユーザー一括操作でLINEメッセージ送信が選択されたら
	static function handle_bulk_users_sendmessage($sendback, $doaction, $items) {
		if ($doaction !== 'lc_linechat') {
			return $sendback;
		}
		$user_args = array();
		foreach ($items as $index => $userid) {
			$user_args['users[' . $index . ']'] = $userid;
		}
		$redirect_url = add_query_arg($user_args, admin_url('admin.php?page=' . lineconnect::SLUG__CHAT_FORM));
		return $redirect_url;
	}
}
