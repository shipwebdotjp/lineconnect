<?php

/**
 * Lineconnect Setting Class
 *
 * Setting Class
 *
 * @category Components
 * @package  Setting
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

class lineconnectSetting {
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
			__( 'LINE Connect settings', lineconnect::PLUGIN_NAME ),
			// メニュータイトル：
			__( 'Settings', lineconnect::PLUGIN_NAME ),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			lineconnect::SLUG__SETTINGS_FORM,
			// メニューに紐づく画面を描画するcallback関数：
			array( 'lineconnectSetting', 'show_settings' ),
			// メニューの位置
			1
		);
		add_action( "admin_print_styles-{$page_hook_suffix}", array( 'lineconnectSetting', 'wpdocs_plugin_admin_styles' ) );
		add_action( "admin_print_scripts-{$page_hook_suffix}", array( 'lineconnectSetting', 'wpdocs_plugin_admin_scripts' ) );
	}

	/**
	 * 初期設定画面を表示
	 */
	static function show_settings() {
		// プラグインのオプション
		$plugin_options = lineconnect::get_all_options();

		// 初期設定の保存完了メッセージ
		if ( false !== ( $complete_message = get_transient( lineconnect::TRANSIENT_KEY__SAVE_SETTINGS ) ) ) {
			$complete_message = lineconnect::getNotice( $complete_message, lineconnect::NOTICE_TYPE__SUCCESS );
		}

		$version_update_message = '';
		// Check DB Version is latest
		if ( version_compare( lineconnect::DB_VERSION, lineconnect::get_current_db_version(), '>' ) ) {
			$version_update_message = lineconnect::getNotice(
				__( 'Database is not up to date. Click the Setting Save button to update the database. It is recommended that you make a backup of the database before updating.', lineconnect::PLUGIN_NAME ),
				lineconnect::NOTICE_TYPE__ERROR
			);
		}

		// nonceフィールドを生成・取得
		$nonce_field                 = wp_nonce_field( lineconnect::CREDENTIAL_ACTION__SETTINGS_FORM, lineconnect::CREDENTIAL_NAME__SETTINGS_FORM, true, false );
		$translated_channel_settings = __( 'Channel settings', lineconnect::PLUGIN_NAME );
		// 開いておくタブ
		$active_tab = 0;
		echo <<< EOM
        {$complete_message}{$version_update_message}
        <form action="" method='post' id="line-auto-post-settings-form">
        <div class="wrap ui-tabs ui-corner-all ui-widget ui-widget-content" id="stabs">
            <ul class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
EOM;
		foreach ( lineconnectConst::$settings_option as $tab_name => $tab_details ) {
			echo "<li class='ui-tabs-tab ui-corner-top ui-state-default ui-tab'><a href='#stabs-{$tab_details['prefix']}'>{$tab_details['name']}</a></li>";
		}
		echo <<< EOM
                </ul>
EOM;
		foreach ( lineconnectConst::$settings_option as $tab_name => $tab_details ) {
			switch ( $tab_name ) {
				case 'channel':
					echo <<< EOM
                        <div id="stabs-1" class="ui-tabs-panel ui-corner-bottom ui-widget-content">
                        <h3>{$translated_channel_settings}</h3>
                        <div class="metabox-holder">
                        {$nonce_field}
EOM;
					// チャンネルリスト毎に出力
					foreach ( lineconnect::get_all_channels() as $channel_id => $channel ) {

						$ary_option = array();
						foreach ( lineconnectConst::get_channel_options() as $option_key => $option_name ) {
							$options = array();

							// 不正メッセージ
							if ( false !== ( $invalid = get_transient( lineconnect::INVALID_PREFIX . $option_key . $channel['prefix'] ) ) ) {
								$options['invalid'] = lineconnect::getErrorBar( $invalid, lineconnect::NOTICE_TYPE__ERROR );
							} else {
								$options['invalid'] = '';
							}
							// パラメータ名
							$options['param'] = lineconnect::PARAMETER_PREFIX . $option_key . $channel['prefix'];

							// 設定値
							if ( false === ( $value = get_transient( lineconnect::TRANSIENT_PREFIX . $option_key . $channel['prefix'] ) ) ) {
								// 無ければoptionsテーブルから取得
								$value = $channel[ $option_key ] ?? null;
							}
							$options['value']          = is_array( $value ) ? $value : esc_html( $value );
							$ary_option[ $option_key ] = $options;
						}
						// シークレットの先頭4文字
						$secret_prefix = substr( $ary_option['channel-secret']['value'], 0, 4 );

						// チャンネル名出力
						echo "<div class='postbox'>";
						echo "<h3 class='hndle'><span>{$ary_option['name']['value']}</span></h3>";
						echo "<div class='inside'>";
						echo "<div class='main'>";

						// オプションごとにHTML INPUTフィールド出力
						foreach ( lineconnectConst::get_channel_options() as $option_key => $option_name ) {
							if ( $option_key == 'role' ) {
								// ロール選択セレクトボックスを出力
								$role_select = '<select name=' . $ary_option[ $option_key ]['param'] . "[] multiple class='slc-multi-select' >";
								$all_roles   = array(
									'slc_all'    => __( 'All Friends', lineconnect::PLUGIN_NAME ),
									'slc_linked' => __( 'Linked Friends', lineconnect::PLUGIN_NAME ),
								);
								foreach ( wp_roles()->roles as $role_name => $role ) {
									$all_roles[ esc_attr( $role_name ) ] = translate_user_role( $role['name'] );
								}
								$role_select .= lineconnect::makeHtmlSelectOptions( $all_roles, $ary_option[ $option_key ]['value'] );
								$role_select .= '</select>';

								if ( empty( $ary_option[ $option_key ]['value'] ) ) {
									$target_cnt = sprintf( _n( 'The number of person to be notified is %s.', 'The number of people to be notified is %s.', 0, lineconnect::PLUGIN_NAME ), number_format( 0 ) );
									$target     = __( 'No role selected', lineconnect::PLUGIN_NAME );
								} elseif ( ! in_array( 'slc_all', $ary_option[ $option_key ]['value'] ) ) {
									$role = $ary_option[ $option_key ]['value'];

									if ( in_array( 'slc_linked', $role ) ) {
										$role = array();
									}

									$args          = array(
										'meta_query' => array(
											array(
												'key'     => lineconnect::META_KEY__LINE,
												'compare' => 'EXISTS',
											),
										),
										'role__in'   => $role,
										'fields'     => 'ID',
									);
									$line_user_ids = array();
									$user_query    = new WP_User_Query( $args );
									$users         = $user_query->get_results();
									if ( ! empty( $users ) ) {
										foreach ( $users as $user ) {
											$user_meta_line = get_user_meta( $user, lineconnect::META_KEY__LINE, true );
											if ( isset( $user_meta_line[ $secret_prefix ] ) ) {
												$line_user_ids[] = $user_meta_line[ $secret_prefix ]['id'];
											}
										}
										$target_cnt = sprintf( _n( 'The number of person to be notified is %s.', 'The number of people to be notified is %s.', count( $line_user_ids ), lineconnect::PLUGIN_NAME ), number_format( count( $line_user_ids ) ) );
										$target     = '';
									} else {
										$target_cnt = sprintf( _n( 'The number of person to be notified is %s.', 'The number of people to be notified is %s.', 0, lineconnect::PLUGIN_NAME ), number_format( 0 ) );
										$target     = __( 'No matching user', lineconnect::PLUGIN_NAME );
									}
								} else {
									$target_cnt = __( 'Unknown', lineconnect::PLUGIN_NAME );
									$target     = __( 'Notify to all freinds.', lineconnect::PLUGIN_NAME );
								}
								echo <<< EOM
                                <p>
                                    <label for="{$ary_option[$option_key]['param']}">{$option_name}: </label>
                                    {$role_select}
                                </p>
                                <p>
                                    {$target_cnt}
                                </p>
                                <p>
                                    {$target}
                                </p>
EOM;
							} else {
								// ロール選択以外の普通のフィールド
								$error_class = $ary_option[ $option_key ]['invalid'] ? 'class="error-message" ' : '';
								echo <<< EOM
                                <p>
                                    <label for="{$ary_option[$option_key]['param']}" {$error_class}>{$option_name}: </label>
                                    <input type="text" name="{$ary_option[$option_key]['param']}" value="{$ary_option[$option_key]['value']}"/>
                                    {$ary_option[$option_key]['invalid']}
                                </p>
EOM;
							}
						}
						$del_param = lineconnect::PARAMETER_PREFIX . 'delete_channel';
						$del_label = __( 'Delete this channel', lineconnect::PLUGIN_NAME );
						echo <<< EOM
                        <button type="submit" name="{$del_param}" value="{$channel_id}" class="button button-secondary button-large">{$del_label}</button>
                        </div>
                        </div>
                        </div>
EOM;
					}
					// チャネル追加フォーム
					$new_channel_html = '';
					$new_has_error    = false;
					$channel          = array( 'prefix' => 'new' );
					foreach ( lineconnectConst::get_channel_options() as $option_key => $option_name ) {

						$param = lineconnect::PARAMETER_PREFIX . $option_key . $channel['prefix'];
						$value = get_transient( lineconnect::TRANSIENT_PREFIX . $option_key . $channel['prefix'] );

						// 不正メッセージ
						if ( false !== ( $invalid = get_transient( lineconnect::INVALID_PREFIX . $option_key . $channel['prefix'] ) ) ) {
							$invalid       = lineconnect::getErrorBar( $invalid, lineconnect::NOTICE_TYPE__ERROR );
							$new_has_error = true;
						}

						if ( $option_key == 'role' ) {
							// ロール選択セレクトボックスを出力
							$role_select = '<select name=' . $param . "[] multiple class='slc-multi-select' >";
							$all_roles   = array(
								'slc_all'    => __( 'All Friends', lineconnect::PLUGIN_NAME ),
								'slc_linked' => __( 'Linked Friends', lineconnect::PLUGIN_NAME ),
							);
							foreach ( wp_roles()->roles as $role_name => $role ) {
								$all_roles[ esc_attr( $role_name ) ] = translate_user_role( $role['name'] );
							}
							$role_select .= lineconnect::makeHtmlSelectOptions( $all_roles, $value );
							$role_select .= '</select>';

							$new_channel_html .= <<< EOM
                            <p>
                                <label for="{$param}">{$option_name}: </label>
                                {$role_select}
                            </p>
EOM;
						} else {
							$error_class       = $invalid ? 'class="error-message" ' : '';
							$new_channel_html .= <<< EOM
                            <p>
                                <label for="{$param}" {$error_class}>{$option_name}: </label>
                                <input type="text" name="{$param}" value="{$value}"/>
                                {$invalid}
                            </p>
EOM;
						}
					}
					$display                 = $new_has_error ? '' : 'style="display: none;"';
					$new_channel_title       = __( 'New channel', lineconnect::PLUGIN_NAME );
					$new_channel_label       = __( 'Add new channel', lineconnect::PLUGIN_NAME );
					$new_channel_html_before = <<< EOM
                    <div class='postbox hide' id='new-channel-box' {$display}>
                        <h3 class='hndle'><span>{$new_channel_title}</span></h3>
                        <div class='inside'>
                            <div class='main'>
EOM;
					echo $new_channel_html_before;
					echo $new_channel_html;
					echo <<< EOM
                            </div>
                        </div>
                    </div>
                    <button type="button" id="newChannelBtn" onclick="showNewChannel()" class="button button-secondary button-large">{$new_channel_label}</button>
EOM;

					// 送信ボタンを生成・取得
					$submit_button = get_submit_button( __( 'Save', lineconnect::PLUGIN_NAME ) );
					echo <<< EOM
                            </div>
                        </div>
EOM;
					break;
				default:
					// チャネル以外のタブ
					echo <<< EOM
                    <div id="stabs-{$tab_details['prefix']}"  class="ui-tabs-panel ui-corner-bottom ui-widget-content">
                        <h3>{$tab_details['name']}</h3>
EOM;
					$ary_option = array();
					foreach ( $tab_details['fields'] as $option_key => $option_details ) {

						$options = array();

						// 不正メッセージ
						if ( false !== ( $invalid = get_transient( lineconnect::INVALID_PREFIX . $option_key ) ) ) {
							$options['invalid'] = lineconnect::getErrorBar( $invalid, lineconnect::NOTICE_TYPE__ERROR );
							$active_tab         = intval( $tab_details['prefix'] ) - 1;
						} else {
							$options['invalid'] = '';
						}
						// パラメータ名
						$options['param'] = lineconnect::PARAMETER_PREFIX . $option_key . ( isset( $option_details['isMulti'] ) && $option_details['isMulti'] == true ? '[]' : '' );

						// 設定値
						if ( false === ( $value = get_transient( lineconnect::TRANSIENT_PREFIX . $option_key ) ) ) {
							// 無ければoptionsテーブルから取得
							$value = $plugin_options[ $option_key ];
							// それでもなければデフォルト値
						}
						$options['value'] = is_array( $value ) ? $value : esc_html( $value );

						// 特殊オプション
						if ( $option_key == 'send_post_types' ) {
							$args       = array(
								'public'   => true,
								'_builtin' => false,
							);
							$post_types = get_post_types( $args, 'objects', 'and' );
							foreach ( $post_types as $post_type ) {
								$option_details['list'][ $post_type->name ] = $post_type->label;
							}
						} elseif ( $option_key == 'default_send_template' ){
							$slc_messages = lineconnectSLCMessage::get_lineconnect_message_name_array();
							foreach ( $slc_messages as $message_id => $message_title ) {
								$option_details['list'][ $message_id ] = $message_title;
							}
						} elseif ( $option_key == 'openai_enabled_functions' ) {
							foreach ( lineconnectFunctions::get_callable_functions( false ) as $function_name => $function_schema ) {
								$option_details['list'][ $function_name ] = $function_schema['title'];
							}
						}

						$error_class = $options['invalid'] ? 'class="error-message" ' : '';
						$required    = isset( $option_details['required'] ) && $option_details['required'] ? 'required' : '';
						$hint        = isset( $option_details['hint'] ) ? "<a href=# title='" . $option_details['hint'] . "'><span class='ui-icon ui-icon-info'></span></a>" : '';
						$size        = isset( $option_details['size'] ) && $option_details['size'] ? 'size="' . $option_details['size'] . '" ' : '';

						echo <<< EOM
                        <p>
                            <label for="{$options['param']}" {$error_class}>{$option_details['label']}: </label>
EOM;
						switch ( $option_details['type'] ) {
							case 'select':
							case 'multiselect':
								// セレクトボックスを出力
								$select  = "<select name='{$options['param']}' " . ( $option_details['type'] == 'multiselect' ? "multiple class='slc-multi-select' " : '' ) . '>';
								$select .= lineconnect::makeHtmlSelectOptions( $option_details['list'], $options['value'] );
								$select .= "</select>{$hint}";
								echo $select;
								break;
							case 'color':
								// カラーピッカーを出力
								echo "<input type='text' name='{$options['param']}' value='{$options['value']}' class='slc-color-picker' data-default-color='{$option_details['default']}' {$required}/>{$hint}";
								break;
							case 'spinner':
								// スピナーを出力
								echo "<input type='number' name='{$options['param']}' value='{$options['value']}' {$required} />{$hint}";
								break;
							case 'checkbox':
								// チェックボックスを出力
								echo "<input type='checkbox' name='{$options['param']}' id='{$options['param']}' " . ( $options['value'] ? 'checked' : '' ) . ' >';
								break;
							case 'date':
								// 日付セレクトボックスを出力
								echo "<input type='date' name='{$options['param']}' value='{$options['value']}' {$required} />{$hint}";
								break;
							case 'textarea':
								// テキストエリア出力
								echo "<textarea name='{$options['param']}' rows='{$option_details['rows']}' cols='{$option_details['cols']}' {$required} >{$options['value']}</textarea>{$hint}";
								break;
							case 'range':
								// Range出力
								echo "<input type='range' name='{$options['param']}' value='{$options['value']}' min='{$option_details['min']}' max='{$option_details['max']}' step='{$option_details['step']}' {$required} />{$hint}";
								break;
							default:
								// テキストボックス出力
								echo "<input type='text' name='{$options['param']}' value='{$options['value']}' {$required}  {$size} />{$hint}";
						}
						echo <<< EOM
                                {$options['invalid']}
                        </p>
EOM;
					}
					echo <<< EOM
                    </div>
EOM;
					break;
			}
		}
		$slc_json = json_encode(
			array(
				'active_tab' => $active_tab,
			)
		);

		echo <<< EOM
                </div><!-- stabs -->
                {$submit_button}
            </form>
            <script>
                var slc_json = JSON.parse('{$slc_json}');
            </script>
EOM;
	}

	/**
	 * 初期設定を保存するcallback関数
	 */
	static function save_settings() {
		// nonceで設定したcredentialをPOST受信した場合
		if ( isset( $_POST[ lineconnect::CREDENTIAL_NAME__SETTINGS_FORM ] ) && $_POST[ lineconnect::CREDENTIAL_NAME__SETTINGS_FORM ] ) {
			// nonceで設定したcredentialのチェック結果が問題ない場合
			if ( check_admin_referer( lineconnect::CREDENTIAL_ACTION__SETTINGS_FORM, lineconnect::CREDENTIAL_NAME__SETTINGS_FORM ) ) {
				$valid         = true;
				$channel_value = array();
				$ary_channels  = lineconnect::get_all_channels();
				$richmenes     = array(
					'linked-richmenu'   => 'linked',
					'unlinked-richmenu' => 'unlinked',
				);
				foreach ( wp_roles()->roles as $role_name => $role ) {
					$richmenes[$role_name . '-richmenu'] = $role_name;
				}
				$new_key       = '';
				// 新規チャネルのチェック
				if ( ! empty( $_POST[ lineconnect::PARAMETER_PREFIX . 'channel-access-token' . 'new' ] ) && ! empty( $_POST[ lineconnect::PARAMETER_PREFIX . 'channel-secret' . 'new' ] ) ) {
					$new_key        = substr( $_POST[ lineconnect::PARAMETER_PREFIX . 'channel-secret' . 'new' ], 0, 4 );
					$ary_channels[] = array( 'prefix' => $new_key );
				}
				// チャンネルリスト毎にチェック
				foreach ( $ary_channels as $channel_id => $channel ) {
					if ( isset( $_POST[ lineconnect::PARAMETER_PREFIX . 'delete_channel' ] ) && $_POST[ lineconnect::PARAMETER_PREFIX . 'delete_channel' ] == $channel_id ) {
						// チャネル削除フラグON
						$channel_value[ $channel_id ] = array( 'delete' => true );
					} else {
						$ary_option = array();

						foreach ( lineconnectConst::get_channel_options() as $option_key => $option_name ) {
							$options = array();

							// POSTされた値
							if ( $option_key == 'role' ) {
								if ( $channel['prefix'] == $new_key ) {
									$options['value'] = $_POST[ lineconnect::PARAMETER_PREFIX . $option_key . 'new' ];
								} else {
									$options['value'] = $_POST[ lineconnect::PARAMETER_PREFIX . $option_key . $channel['prefix'] ];
								}
								foreach ( $options['value'] as $key => $tmp ) {
									$options['value'][ $key ] = trim( sanitize_text_field( $tmp ) );
								}
							} elseif ( $channel['prefix'] == $new_key ) {
									$options['value'] = trim( sanitize_text_field( $_POST[ lineconnect::PARAMETER_PREFIX . $option_key . 'new' ] ) );
							} else {
								$options['value'] = trim( sanitize_text_field( $_POST[ lineconnect::PARAMETER_PREFIX . $option_key . $channel['prefix'] ] ) );
							}
							$ary_option[ $option_key ] = $options;
						}
						$ary_option['prefix']         = array( 'value' => substr( $ary_option['channel-secret']['value'], 0, 4 ) );
						$channel_value[ $channel_id ] = $ary_option;

						foreach ( lineconnectConst::get_channel_options() as $option_key => $option_name ) {
							// 入力値チェック
							if ( ( $option_key == 'channel-access-token' && ! preg_match( lineconnect::REGEXP_CHANNEL_ACCESS_TOKEN, $ary_option[ $option_key ]['value'] ) ) ||
								( $option_key == 'channel-secret' && ! preg_match( lineconnect::REGEXP_CHANNEL_SECRET, $ary_option[ $option_key ]['value'] ) )
							) {
								// 不正な値であることを示すメッセージをTRANSIENTに5秒間保持
								if ( $channel['prefix'] == $new_key ) {
									set_transient( lineconnect::INVALID_PREFIX . $option_key . 'new', sprintf( __( '"%s" is invalid in new channel.', lineconnect::PLUGIN_NAME ), $option_name ), lineconnect::TRANSIENT_TIME_LIMIT );
								} else {
									set_transient( lineconnect::INVALID_PREFIX . $option_key . $channel['prefix'], sprintf( __( '"%2$s" is invalid in %1$s channel.', lineconnect::PLUGIN_NAME ), $channel['name'], $option_name ), lineconnect::TRANSIENT_TIME_LIMIT );
								}
								// 有効フラグをFalse
								$valid = false;
							} elseif ( $valid && array_key_exists( $option_key, $richmenes ) && ( ( isset( $ary_channels[ $channel_id ][ $option_key ] ) && $ary_channels[ $channel_id ][ $option_key ] != $ary_option[ $option_key ]['value'] ) || ( ! isset( $ary_channels[ $channel_id ][ $option_key ] ) && $ary_option[ $option_key ]['value'] ) ) ) {
								// リッチメニューが変更されている場合、リッチメニューの存在チェック
								$rech_result = lineconnectRichmenu::checkRichMenuId(
									array(
										'channel-access-token' => $ary_option['channel-access-token']['value'],
										'channel-secret' => $ary_option['channel-secret']['value'],
									),
									$ary_option[ $option_key ]['value']
								);
								if ( is_array( $rech_result ) && ! $rech_result[0] ) {
									$valid = false;
									if ( $channel['prefix'] == $new_key ) {
										set_transient( lineconnect::INVALID_PREFIX . $option_key . 'new', sprintf( __( '"%1$s" is invalid in new channel. Error message: "%2$s"', lineconnect::PLUGIN_NAME ), $option_name, $rech_result[1] ), lineconnect::TRANSIENT_TIME_LIMIT );
									} else {
										set_transient( lineconnect::INVALID_PREFIX . $option_key . $channel['prefix'], sprintf( __( '"%2$s" is invalid in %1$s channel. Error message: "%3$s"', lineconnect::PLUGIN_NAME ), $channel['name'], $option_name, $rech_result[1] ), lineconnect::TRANSIENT_TIME_LIMIT );
									}
								}
							}
						}
						// 重複チェック
						foreach ( $ary_channels as $channel_id_loop => $channel_loop ) {
							if ( $channel_id != $channel_id_loop && $ary_option['prefix']['value'] == $channel_loop['prefix'] ) {
								if ( $channel['prefix'] == $new_key ) {
									set_transient( lineconnect::INVALID_PREFIX . 'channel-secret' . 'new', __( 'The same channel secret is already registered of the new channel.', lineconnect::PLUGIN_NAME ), lineconnect::TRANSIENT_TIME_LIMIT );
								} else {
									set_transient( lineconnect::INVALID_PREFIX . 'channel-secret' . $channel['prefix'], sprintf( __( 'The same channel secret is already registered of the "%s".', lineconnect::PLUGIN_NAME ), $channel['name'] ), lineconnect::TRANSIENT_TIME_LIMIT );
								}
								// 有効フラグをFalse
								$valid = false;
							}
						}
					}
				}

				// チャンネル以外のオプション値チェック
				$plugin_options = array();
				foreach ( lineconnectConst::$settings_option as $tab_name => $tab_details ) {
					if ( $tab_name == 'channel' ) {
						continue;
					}
					foreach ( $tab_details['fields'] as $option_key => $option_details ) {
						if ( isset( $option_details['isMulti'] ) && $option_details['isMulti'] ) {
							$value =  isset( $_POST[ lineconnect::PARAMETER_PREFIX . $option_key ] ) ? $_POST[ lineconnect::PARAMETER_PREFIX . $option_key ] : [];
							foreach ( $value as $key => $tmp ) {
								$value[ $key ] = trim( sanitize_text_field( $tmp ) );
							}
						} elseif ( $option_details['type'] == 'checkbox' ) {
							$value = isset( $_POST[ lineconnect::PARAMETER_PREFIX . $option_key ] ) && $_POST[ lineconnect::PARAMETER_PREFIX . $option_key ] == 'on' ? true : false;
						} else {
							$value = trim( sanitize_text_field( $_POST[ lineconnect::PARAMETER_PREFIX . $option_key ] ) );
						}
						if ( self::is_empty( $value ) && $option_details['required'] ) {
							set_transient( lineconnect::INVALID_PREFIX . $option_key, sprintf( __( '"%s" is required.', lineconnect::PLUGIN_NAME ), $option_details['label'] ), lineconnect::TRANSIENT_TIME_LIMIT );
							$valid = false;
						} elseif ( isset( $option_details['regex'] ) && ! preg_match( $option_details['regex'], $value ) ) {
							set_transient( lineconnect::INVALID_PREFIX . $option_key, sprintf( __( '"%s" is invalid.', lineconnect::PLUGIN_NAME ), $option_details['label'] ), lineconnect::TRANSIENT_TIME_LIMIT );
							$valid = false;
						} elseif ( $option_key == 'image_aspectrate' ) {
							preg_match( '/^([1-9]+[0-9]*):([1-9]+[0-9]*)$/', $value, $matches );
							if ( $matches[2] > $matches[1] * 3 ) {
								set_transient( lineconnect::INVALID_PREFIX . $option_key, sprintf( __( '"%s" is invalid. The height cannot be greater than three times the width.', lineconnect::PLUGIN_NAME ), $option_details['label'] ), lineconnect::TRANSIENT_TIME_LIMIT );
								$valid = false;
							}
						}
						$plugin_options[ $option_key ] = $value;
					}
				}

				// すべてのチャンネルの値をチェックして、なお有効フラグがTrueの場合
				if ( $valid ) {
					$complete_message = __( 'Settings saved.', lineconnect::PLUGIN_NAME );
					$totalchanged     = array();  // 更新したリッチメニューリスト

					$new_ary_channels = array();
					// チャンネルリスト毎にチェック
					foreach ( $ary_channels as $channel_id => $channel ) {
						if ( isset( $channel_value[ $channel_id ]['delete'] ) && $channel_value[ $channel_id ]['delete'] ) {
							continue;
						}
						$changed_richmenus = array();  // チャンネルごとの更新したリッチメニューリスト
						$is_changed_richmenus = array(); // 変更のあったリッチメニュー
						$ary_richmeneus = array();
						foreach ( lineconnectConst::get_channel_options() as $option_key => $option_name ) {

							// リッチメニューIDの更新処理（各ロールに応じてメニューIDを関連付け）
							if ( array_key_exists( $option_key, $richmenes ) ) {
								$ary_richmeneus[ $richmenes[ $option_key ] ] = $channel_value[ $channel_id ][ $option_key ]['value'];
								if ( ( ( isset( $ary_channels[ $channel_id ][ $option_key ] ) && $ary_channels[ $channel_id ][ $option_key ] != $channel_value[ $channel_id ][ $option_key ]['value'] ) || ( ! isset( $ary_channels[ $channel_id ][ $option_key ] ) && $channel_value[ $channel_id ][ $option_key ]['value'] ) ) ) {
									// richmenu_idが変更されていたら
									$is_changed_richmenus[] = $richmenes[ $option_key ];
									// $changed_richmenus[] = lineconnectRichmenu::updateRichMenuId(
									// 	array(
									// 		'channel-access-token' => $channel_value[ $channel_id ]['channel-access-token']['value'],
									// 		'channel-secret' => $channel_value[ $channel_id ]['channel-secret']['value'],
									// 	),
									// 	$richmenes[ $option_key ],
									// 	$channel_value[ $channel_id ][ $option_key ]['value']
									// );
								}
							}
							// 保存処理
							$ary_channels[ $channel_id ][ $option_key ] = $channel_value[ $channel_id ][ $option_key ]['value'];

							// (一応)不正値メッセージをTRANSIENTから削除
							delete_transient( lineconnect::INVALID_PREFIX . $option_key . $channel['prefix'] );

							// (一応)ユーザーが入力した値をTRANSIENTから削除
							delete_transient( lineconnect::TRANSIENT_PREFIX . $option_key . $channel['prefix'] );
						}
						if( !empty( $is_changed_richmenus ) ) {
							$changed_richmenus = lineconnectRichmenu::updateRichMenuId(
								array(
									'channel-access-token' => $channel_value[ $channel_id ]['channel-access-token']['value'],
									'channel-secret' => $channel_value[ $channel_id ]['channel-secret']['value'],
								),
								$is_changed_richmenus,
								$ary_richmeneus
							);
						}
						// Prefix
						$ary_channels[ $channel_id ]['prefix'] = $channel_value[ $channel_id ]['prefix']['value'];
						// リッチメニュー変更メッセージがあれば
						if ( ! empty( $changed_richmenus ) ) {
							$totalchanged[] = $channel_value[ $channel_id ]['name']['value'] . ': ' . join( ', ', $changed_richmenus );
						}
						$new_ary_channels[] = $ary_channels[ $channel_id ];
					}

					if ( ! empty( $totalchanged ) ) {
						$complete_message .= sprintf( __( 'Updated the following rich menus: %s', lineconnect::PLUGIN_NAME ), join( ' ', $totalchanged ) );
					}
					// チャンネルオプションを保存
					update_option( lineconnect::OPTION_KEY__CHANNELS, $new_ary_channels );
					// プラグインオプションを保存
					update_option( lineconnect::OPTION_KEY__SETTINGS, $plugin_options );
					// 保存が完了したら、完了メッセージをTRANSIENTに5秒間保持
					set_transient( lineconnect::TRANSIENT_KEY__SAVE_SETTINGS, $complete_message, lineconnect::TRANSIENT_TIME_LIMIT );
				} else {
					// 有効フラグがFalseの場合
					foreach ( $ary_channels as $channel_id => $channel ) {
						foreach ( lineconnectConst::get_channel_options() as $option_key => $option_name ) {
							// ユーザが入力した値を5秒間保持
							if ( $channel['prefix'] == $new_key ) {
								set_transient( lineconnect::TRANSIENT_PREFIX . $option_key . 'new', $channel_value[ $channel_id ][ $option_key ]['value'], lineconnect::TRANSIENT_TIME_LIMIT );
							} else {
								set_transient( lineconnect::TRANSIENT_PREFIX . $option_key . $channel['prefix'], $channel_value[ $channel_id ][ $option_key ]['value'], lineconnect::TRANSIENT_TIME_LIMIT );
							}
						}
					}
					foreach ( lineconnectConst::$settings_option as $tab_name => $tab_details ) {
						if ( $tab_name == 'channel' ) {
							continue;
						}
						foreach ( $tab_details['fields'] as $option_key => $option_details ) {
							if ( isset( $option_details['isMulti'] ) && $option_details['isMulti'] ) {
								$value = $_POST[ lineconnect::PARAMETER_PREFIX . $option_key ];
								foreach ( $value as $key => $tmp ) {
									$value[ $key ] = trim( sanitize_text_field( $tmp ) );
								}
							} elseif ( $option_details['type'] == 'checkbox' ) {
								$value = $_POST[ lineconnect::PARAMETER_PREFIX . $option_key ] == 'on' ? true : false;
							} else {
								$value = trim( sanitize_text_field( $_POST[ lineconnect::PARAMETER_PREFIX . $option_key ] ) );
							}
							set_transient( lineconnect::TRANSIENT_PREFIX . $option_key, $value, lineconnect::TRANSIENT_TIME_LIMIT );
						}
					}
					// (一応)初期設定の保存完了メッセージを削除
					delete_transient( lineconnect::TRANSIENT_KEY__SAVE_SETTINGS );
				}
				// Database update
				if ( version_compare( lineconnect::DB_VERSION, lineconnect::get_variable( lineconnectConst::DB_VERSION_KEY, lineconnectConst::$variables_option[ lineconnectConst::DB_VERSION_KEY ]['initial'] ), '>' ) ) {
					lineconnect::delta_database();
				}
				// 設定画面にリダイレクト
				wp_safe_redirect( menu_page_url( lineconnect::SLUG__SETTINGS_FORM ), 303 );
			}
		}
	}

	// 管理画面用にスクリプト読み込み
	static function wpdocs_plugin_admin_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core', false, array( 'jquery' ) );
		wp_enqueue_script( 'jquery-ui-tabs', false, array( 'jquery-ui-core' ) );
		wp_enqueue_script( 'jquery-ui-tooltip', false, array( 'jquery-ui-core' ) );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-multiselect-widget', plugins_url( 'js/jquery.multiselect.min.js', __DIR__ ), array( 'jquery-ui-core' ), '3.0.1', true );
		$setting_js = 'js/slc_setting.js';
		wp_enqueue_script( lineconnect::PLUGIN_PREFIX . 'admin', plugins_url( $setting_js, __DIR__ ), array( 'jquery-ui-tabs', 'wp-color-picker', 'jquery-ui-multiselect-widget', 'wp-i18n' ), filemtime( plugin_dir_path( __DIR__ ) . $setting_js ), true );

		// JavaScriptの言語ファイル読み込み
		wp_set_script_translations( lineconnect::PLUGIN_PREFIX . 'admin', lineconnect::PLUGIN_NAME, plugin_dir_path( __DIR__ ) . 'languages' );
	}

	// 管理画面用にスタイル読み込み
	static function wpdocs_plugin_admin_styles() {
		$jquery_ui_css = 'css/jquery-ui.css';
		wp_enqueue_style( lineconnect::PLUGIN_ID . '-admin-ui-css', plugins_url( $jquery_ui_css, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $jquery_ui_css ) );
		wp_enqueue_style( 'wp-color-picker' );
		$setting_css = 'css/slc_setting.css';
		wp_enqueue_style( lineconnect::PLUGIN_PREFIX . 'admin-css', plugins_url( $setting_css, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $setting_css ) );
		$multiselect_css = 'css/jquery.multiselect.css';
		wp_enqueue_style( lineconnect::PLUGIN_PREFIX . 'multiselect-css', plugins_url( $multiselect_css, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $multiselect_css ) );
	}

	static function is_empty($value) {
		return empty($value) && $value !== 0 && $value !== '0';
	}
}
