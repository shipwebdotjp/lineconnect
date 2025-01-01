<?php

/**
 * Lineconnect Publish Class
 *
 * 記事の公開時にLINE通知を行う
 *
 * @category Components
 * @package  Publish
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */


class lineconnectPublish {

	static function add_send_checkbox() {
		// 投稿ページと固定ページ両方でLINE送信チェックボックスを表示
		$screens = lineconnect::get_option('send_post_types');
		foreach ($screens as $screen) {
			add_meta_box(
				// チェックボックスのID
				lineconnect::PARAMETER__SEND_CHECKBOX,
				// チェックボックスのラベル名
				'LINE Connect',
				// チェックボックスを表示するコールバック関数
				['lineconnectPublish', 'show_send_checkbox'],
				// 投稿画面に表示
				$screen,
				// 投稿画面の右サイドに表示
				'side',
				// 優先度(default)
				'default'
			);
		}
	}

	//管理画面（投稿ページ）用にスクリプト読み込み
	static function wpdocs_selectively_enqueue_admin_script() {
		global $post_type, $pagenow;
		$post_types = lineconnect::get_option('send_post_types');
		if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
			if (in_array($post_type, $post_types)) {
				//jQuery uiとmultiselectを読み込み
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core', false, array('jquery'));
				wp_enqueue_script('jquery-ui-multiselect-widget', plugins_url("js/jquery.multiselect.min.js", dirname(__FILE__)), array('jquery-ui-core'), "3.0.1", true);
				$multiselect_js = "js/slc_multiselect.js";
				wp_enqueue_script(lineconnect::PLUGIN_PREFIX . 'admin-multiselect', plugins_url($multiselect_js, dirname(__FILE__)), array('jquery-ui-multiselect-widget', 'wp-i18n'), filemtime(plugin_dir_path(dirname(__FILE__)) . $multiselect_js), true);
				//JavaScriptの言語ファイル読み込み
				wp_set_script_translations(lineconnect::PLUGIN_PREFIX . 'admin-multiselect', lineconnect::PLUGIN_NAME, plugin_dir_path(dirname(__FILE__)) . 'languages');

				//スタイルを読み込み
				$jquery_ui_css = "css/jquery-ui.css";
				wp_enqueue_style(lineconnect::PLUGIN_ID . '-admin-ui-css', plugins_url($jquery_ui_css, dirname(__FILE__)), array(), filemtime(plugin_dir_path(dirname(__FILE__)) . $jquery_ui_css));
				wp_enqueue_style('wp-color-picker');
				$multiselect_css = "css/jquery.multiselect.css";
				wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'multiselect-css', plugins_url($multiselect_css, dirname(__FILE__)), array(), filemtime(plugin_dir_path(dirname(__FILE__)) . $multiselect_css));
			}
		}
	}

	/**
	 * LINEにメッセージを送信するチェックボックスを表示
	 */
	static function show_send_checkbox() {

		// nonceフィールドを生成・取得
		$nonce_field = wp_nonce_field(
			lineconnect::CREDENTIAL_ACTION__POST,
			lineconnect::CREDENTIAL_NAME__POST,
			true,
			false
		);
		echo $nonce_field;
		echo "<div>";
		$is_send_line = get_post_meta(get_the_ID(), lineconnect::META_KEY__IS_SEND_LINE, true);
		$is_send_line = apply_filters( lineconnect::FILTER_PREFIX . 'publish_postmeta_is_send_line', $is_send_line, get_the_ID() );
		$default_send_checkbox = lineconnect::get_option('default_send_checkbox');
		$default_send_template = lineconnect::get_option('default_send_template');
		//error_log($is_send_line);
		//チャンネルリスト毎に出力
		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {

			$htmls = array();
			foreach (lineconnectConst::$channnel_field as $option_key => $option_name) {
				$input_filed = "";
				if ($option_key == 'role-selectbox') {
					if (isset($is_send_line[$channel['prefix']])) {
						$roles = $is_send_line[$channel['prefix']]['role'] ?? $channel['role'];
						// error_log($channel['prefix'].' saved role:'.implode(',',$roles));

					} else {
						$roles = $channel['role'];
						// error_log($channel['prefix'].' role:'.implode(',',$roles));

					}
					

					$roles = is_array($roles) ? $roles : esc_html($roles);
					// ロール選択セレクトボックスを出力
					// Sendboxのパラメータ名
					$param_role = lineconnect::PARAMETER_PREFIX . $option_key . $channel['prefix'] . "[]";
					$input_filed = '<label for="' . $param_role . '">' . $option_name . '</label>' . "<select name=" . $param_role . " multiple class='slc-multi-select'>";
					// $all_roles = array("slc_all" => "すべての友達", "slc_linked" => "連携済みの友達");
					$all_roles = array("slc_all" => __('All Friends', lineconnect::PLUGIN_NAME), "slc_linked" => __('Linked Friends', lineconnect::PLUGIN_NAME));
					foreach (wp_roles()->roles as $role_name => $role) {
						$all_roles[esc_attr($role_name)] = translate_user_role($role['name']);
					}
					$input_filed .= lineconnect::makeHtmlSelectOptions($all_roles, $roles);
					$input_filed .= "</select>";
				} elseif ( $option_key == 'template-selectbox') {
					if (isset($is_send_line[$channel['prefix']])) {
						$template = $is_send_line[$channel['prefix']]['template'] ?? $default_send_template;
					} else {
						$template = $default_send_template;
					}
					$template = esc_html( $template );
					// テンプレート選択セレクトボックスを出力
					// Sendboxのパラメータ名
					$param_template = lineconnect::PARAMETER_PREFIX . $option_key . $channel['prefix'];
					$input_filed = '<label for="' . $param_template . '">' . $option_name . '</label>' . "<select name=" . $param_template . " class=''>";
					$slc_messages = lineconnectSLCMessage::get_lineconnect_message_name_array();
					$all_templates = array(0 => __('Default template', lineconnect::PLUGIN_NAME));
					foreach ($slc_messages as $template_id => $template_name) {
						$all_templates[$template_id] = $template_name;
					}
					$input_filed .= lineconnect::makeHtmlSelectOptions($all_templates, $template);
					$input_filed .= "</select>";

				} elseif ($option_key == 'send-checkbox' || $option_key == 'future-checkbox') {
					if ($option_key == 'future-checkbox') {
						if (isset($is_send_line[$channel['prefix']])) {
							$checked = (isset( $is_send_line[$channel['prefix']]['isSend'] ) && $is_send_line[$channel['prefix']]['isSend'] == 'ON' ) ? 'checked' : '';
						} else {
							$checked = '';
						}
					} else if ($option_key == 'send-checkbox') {
						if (($default_send_checkbox == 'new' && get_post_status(get_the_ID()) === 'publish') || $default_send_checkbox == 'off') {
							$checked = '';
						} else {
							$checked = 'checked';
						}
					}
					$param_select = lineconnect::PARAMETER_PREFIX . $option_key . $channel['prefix'];
					$input_filed = '<input type="checkbox" name="' . $param_select . '" value="ON" id="id_' . $param_select . '" ' . $checked . '>' .
						'<label for="id_' . $param_select . '">' . $option_name . '</label><br>';
				}
				$htmls[$option_key] = $input_filed;
			}

			echo "<div>";
			echo '<h3>' . $channel['name'] . '</h3>';
			echo '<div>' . $htmls['send-checkbox'] . '</div>';
			echo '<div>' . $htmls['role-selectbox'] . '</div>';
			echo '<div>' . $htmls['template-selectbox'] . '</div>';
			echo '<div>' . $htmls['future-checkbox'] . '</div>';
			echo '</div>';
		}
		echo "</div>";
	}

	/**
	 * 投稿を公開
	 */
	static function publish_post($post_ID, $post) {
		self::send_to_line($post_ID, $post);
	}

	/**
	 * LINEメッセージを送信
	 */
	static function send_to_line($post_ID, $post) {
		//error_log("send_to_line fired!");

		$ary_success_message = array();
		$ary_error_message = array();
		$isRestAPI = lineconnect::is_rest();
		//投稿メタを取得
		$is_send_line = get_post_meta($post_ID, lineconnect::META_KEY__IS_SEND_LINE, true);
		//チャンネルリスト毎に送信
		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			$error_message = $success_message = "";
			$send_checkbox_value = "";
			$roles = array();
			$template = 0;
			$channel_access_token = $channel['channel-access-token'];
			$channel_secret = $channel['channel-secret'];

			//投稿メタからLINE送信チェックボックスと、ロールを取得
			if ($isRestAPI) {
				$req_json = json_decode(WP_REST_Server::get_raw_data());
				$channels = $req_json->{'lc_channels'};
				foreach ($channels as $rest_cid => $rest_channel_value) {
					if ($rest_cid == $channel_id || $rest_cid == $channel['prefix']) {
						$send_checkbox_value = 'ON';
						// 後方互換性を保つため、文字列の場合はロールとみなす
						if( is_string($rest_channel_value) ) {
							$rest_role = $rest_channel_value;
							$roles = explode(',', $rest_role);
						} else {
							$roles = $rest_channel_value->{'roles'};
							$template = $rest_channel_value->{'template'};
						}
					}
				}
			} elseif (isset($_POST[lineconnect::CREDENTIAL_NAME__POST]) && check_admin_referer(lineconnect::CREDENTIAL_ACTION__POST, lineconnect::CREDENTIAL_NAME__POST)) {
				$send_checkbox_value = isset($_POST[lineconnect::PARAMETER_PREFIX . 'send-checkbox' . $channel['prefix']]) ? $_POST[lineconnect::PARAMETER_PREFIX . 'send-checkbox' . $channel['prefix']] : '';
				$roles =  isset($_POST[lineconnect::PARAMETER_PREFIX . 'role-selectbox' . $channel['prefix']]) ? $_POST[lineconnect::PARAMETER_PREFIX . 'role-selectbox' . $channel['prefix']] : [];
				$template = $_POST[lineconnect::PARAMETER_PREFIX . 'template-selectbox' . $channel['prefix']] ?? 0;
			} elseif (isset($is_send_line[$channel['prefix']])) {
				$send_checkbox_value = $is_send_line[$channel['prefix']]['isSend'] ?? '';
				$roles = $is_send_line[$channel['prefix']]['role'] ?? [];
				$template = $is_send_line[$channel['prefix']]['template'] ?? 0;
			}
			// apply_filters compact extract $send_checkbox_value, $roles $template
			extract(apply_filters(lineconnect::FILTER_PREFIX . 'send_notification_is_send_line', compact( 'send_checkbox_value', 'roles', 'template' ), $post_ID, $post));
			// ChannelAccessTokenとChannelSecretが設定されており、LINEメッセージ送信チェックボックスにチェックがある場合
			if (strlen($channel_access_token) > 0 && strlen($channel_secret) > 0 && $send_checkbox_value == 'ON') {
				// 投稿のタイトルを取得
				$title = sanitize_text_field($post->post_title);

				// 投稿の本文を取得
				$body = preg_replace("/( |　|\n|\r)/", "", strip_tags(sanitize_text_field(strip_shortcodes($post->post_content))));

				if (mb_strlen($body) > 500) {
					// 投稿の本文の先頭500文字取得
					$body = mb_substr($body, 0, 499) . "…";
				}

				//空BODYでは送れないため、本文がない場合はスペースを送信
				if (mb_strlen($body) == 0) {
					$body = " ";
				}

				// 投稿のURLを取得
				$link = get_permalink($post_ID);

				// 投稿のサムネイルを取得
				if ($isRestAPI) {
					if (property_exists($req_json, 'featured_media')) {
						$featured_media_id = $req_json->{'featured_media'};
						foreach (array('full', 'large', 'medium', 'thumbnail') as $thumbsize) {
							$thumb_array = wp_get_attachment_image_src($featured_media_id, $thumbsize);
							if ($thumb_array && $thumb_array[1] <= 1024 && $thumb_array[2] <= 1024) {
								$thumb = $thumb_array[0];
								break;
							}
						}
					}
				} else {
					$thumb = get_the_post_thumbnail_url($post_ID);
				}

				//$body .= $thumb;

				if (substr($thumb, 0, 5) != "https") {  //httpsから始まらない場合はサムネなしとする
					$thumb = "";
				}

				//通知用の本文を作成（400文字に切り詰め）
				$alttext = $title . "\r\n" . $body . "\r\n" . $link;
				if (mb_strlen($alttext) > 400) {
					$alttext = mb_substr($alttext, 0, 399) . "…";
				}

				// LINEBOT SDKの読み込み
				// require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');

				//メッセージ関連を読み込み
				require_once(plugin_dir_path(__FILE__) . 'message.php');

				$link_label = lineconnect::get_option('more_label');
				$args = [];
				if( !$template ){
					$args = ["title" => $title, "body" => $body, "thumb" => $thumb, "type" => "uri", "label" => $link_label, "link" => $link];
					$buildMessage = lineconnectMessage::createFlexMessage($args);
				}else{
					$args = lineconnectUtil::flat( $post->to_array() );
					// get and merge post_meta
					foreach( get_post_meta( $post_ID, '', true ) as $key => $value ) {
						$value = maybe_unserialize($value[0]);
						if(is_array($value) || is_object($value)){
							$args = array_merge( $args, lineconnectUtil::flat( $value, 'post_meta.'.$key ));
						}elseif( is_string($value) ){
							$args['post_meta.'.$key] = $value;
						}
					}
					$args['formatted_title'] = $title;
					$args['formatted_content'] = $body;
					$args['post_thumbnail'] = $thumb;
					$args['post_permalink'] = $link;
					$args['link_label'] = $link_label;
					$args['alttext'] = $alttext;
					$args = apply_filters( lineconnect::FILTER_PREFIX . 'notification_message_args', $args, $template );
					$buildMessage = lineconnectSLCMessage::get_lineconnect_message( $template, $args );
				}
				$buildMessage = apply_filters( lineconnect::FILTER_PREFIX . 'notification_message', $buildMessage, $args, $template );

				if (in_array("slc_all", $roles)) {
					//送信するロールがすべてのユーザーならブロードキャスト
					$response = lineconnectMessage::sendBroadcastMessage($channel, $buildMessage);
					if ($response['success']) {
						$success_message = __('Sent a LINE message to all friends.', lineconnect::PLUGIN_NAME);
					} else {
						$error_message = __('Failed to send a LINE message to all friends.', lineconnect::PLUGIN_NAME) . $response['message'];
					}
				} else {

					$response = lineconnectMessage::sendMessageRole($channel, $roles, $buildMessage);
					if ($response['success']) {
						if ($response['num']) {
							$success_message =  sprintf(_n('Sent a LINE message to %s person.', 'Sent a LINE message to %s people.', $response['num'], lineconnect::PLUGIN_NAME), number_format($response['num']));
						} else {
							$error_message = __('No users matched', lineconnect::PLUGIN_NAME);
						}
					} else {
						$error_message = __('Failed to send a LINE message to selected roles.', lineconnect::PLUGIN_NAME) . $response['message'];
					}
				}
				// 送信に成功した場合
				if ($success_message) {
					$ary_success_message[] = $channel['name'] . ": " . $success_message;
				}
				// 送信に失敗した場合
				else {
					$ary_error_message[] = $channel['name'] . ": " . $error_message;
				}
			}
		}
		if (!empty($ary_success_message)) {
			// LINE送信に成功した旨をTRANSIENTに5秒間保持
			set_transient(lineconnect::TRANSIENT_KEY__SUCCESS_SEND_TO_LINE, join(' ,', $ary_success_message), lineconnect::TRANSIENT_TIME_LIMIT);
		}
		if (!empty($ary_error_message)) {
			// LINE送信に失敗した旨をTRANSIENTに5秒間保持
			set_transient(lineconnect::TRANSIENT_KEY__ERROR_SEND_TO_LINE, join(' ,', $ary_error_message), lineconnect::TRANSIENT_TIME_LIMIT);
		}
	}

	/**
	 * 投稿(公開)した際にLINE送信に失敗した時のメッセージ表示
	 */
	static function error_send_to_line() {
		// LINE送信に失敗した旨のメッセージをTRANSIENTから取得
		if (false !== ($error_send_to_line = get_transient(lineconnect::TRANSIENT_KEY__ERROR_SEND_TO_LINE))) {
			echo lineconnect::getNotice($error_send_to_line, lineconnect::NOTICE_TYPE__ERROR);
		}
	}

	/**
	 * 投稿(公開)した際にLINE送信に成功した時のメッセージ表示
	 */
	static function success_send_to_line() {
		// LINE送信に成功した旨のメッセージをTRANSIENTから取得
		if (false !== ($success_send_to_line = get_transient(lineconnect::TRANSIENT_KEY__SUCCESS_SEND_TO_LINE))) {
			echo lineconnect::getNotice($success_send_to_line, lineconnect::NOTICE_TYPE__SUCCESS);
		}
	}

	/**
	 * 投稿を保存
	 */
	static function save_post($post_ID, $post) {
		$isRestAPI = lineconnect::is_rest();
		//REST API経由の場合は適切な認証がされているのでパス
		if (!$isRestAPI) {
			// ログインしていない場合は無視
			if (!is_user_logged_in()) return;
			// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
			if (!is_super_admin() && !current_user_can('administrator') && !current_user_can('editor') && !current_user_can('author')) return;
			// nonceで設定したcredentialをPOST受信していない場合は無視
			if (!isset($_POST[lineconnect::CREDENTIAL_NAME__POST]) || !$_POST[lineconnect::CREDENTIAL_NAME__POST]) return;
			// nonceで設定したcredentialのチェック結果に問題がある場合
			if (!check_admin_referer(lineconnect::CREDENTIAL_ACTION__POST, lineconnect::CREDENTIAL_NAME__POST)) return;
		}

		$is_send_line = array();
		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			if ($isRestAPI) {
				$req_json = json_decode(WP_REST_Server::get_raw_data());
				$channels = $req_json->{'lc_channels'};
				foreach ($channels as $rest_cid => $rest_channel_value) {
					if ($rest_cid == $channel_id || $rest_cid == $channel['prefix']) {
						$future_checkbox = 'ON';
						// 後方互換性を保つため、文字列の場合はロールとみなす
						if( is_string($rest_channel_value) ) {
							$rest_role = $rest_channel_value;
							$roles = explode(',', $rest_role);
						} else {
							$roles = $rest_channel_value->{'roles'};
							$template = $rest_channel_value->{'template'};
						}
					}
				}
			} else {
				// RoleをPOSTから取得
				$roles = $_POST[lineconnect::PARAMETER_PREFIX . 'role-selectbox' . $channel['prefix']] ?? null;
				$template = $_POST[lineconnect::PARAMETER_PREFIX . 'template-selectbox' . $channel['prefix']] ?? null;
				$future_checkbox = isset($_POST[lineconnect::PARAMETER_PREFIX . 'future-checkbox' . $channel['prefix']]) ? $_POST[lineconnect::PARAMETER_PREFIX . 'future-checkbox' . $channel['prefix']] : null;
			}
			$is_send_line[$channel['prefix']] = array();

			if ($future_checkbox == 'ON') {
				$is_send_line[$channel['prefix']]['isSend'] = 'ON';
			}
			if ( isset($roles)) {
				$is_send_line[$channel['prefix']]['role'] = $roles;
			}
			if ( isset($template)) {
				$is_send_line[$channel['prefix']]['template'] = $template;
			}
		}

		if (!empty($is_send_line)) {
			update_post_meta($post_ID, lineconnect::META_KEY__IS_SEND_LINE, $is_send_line);
		} else {
			delete_post_meta($post_ID, lineconnect::META_KEY__IS_SEND_LINE);
		}
	}
}
