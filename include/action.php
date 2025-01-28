<?php

/**
 * Lineconnect Action Class
 *
 * LINE Connect Action
 *
 * @category Components
 * @package  Action
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */


class lineconnectAction {

	/**
	 * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
	 * 管理画面のトップメニューページを追加する関数
	 */
	/*
	static function set_plugin_menu() {
		// 設定のサブメニュー「LINE Connect」を追加
		$page_hook_suffix = add_submenu_page(
		// 親ページ：
			lineconnect::SLUG__DASHBOARD,
			// ページタイトル：
			__( 'LINE Connect Action', lineconnect::PLUGIN_NAME ),
			// メニュータイトル：
			__( 'Actions', lineconnect::PLUGIN_NAME ),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			'edit.php?post_type=slc_action',
			// メニューに紐づく画面を描画するcallback関数：
			false,
			// メニューの位置
			null
		);
		// add_action( "admin_print_styles-{$page_hook_suffix}", array( 'lineconnectAction', 'wpdocs_plugin_admin_styles' ) );
		// add_action( "admin_print_scripts-{$page_hook_suffix}", array( 'lineconnectAction', 'wpdocs_plugin_admin_scripts' ) );
		// remove_menu_page( lineconnect::SLUG__DM_FORM );
	}

	static function register_meta_box() {
		// 投稿ページと固定ページ両方でLINE送信チェックボックスを表示
		// $screens = lineconnect::get_option('send_post_types');
		// foreach ($screens as $screen) {
			add_meta_box(
				// チェックボックスのID
				lineconnect::PARAMETER__ACTION_DATA,
				// チェックボックスのラベル名
				'LINE Connect Action',
				// チェックボックスを表示するコールバック関数
				array( 'lineconnectAction', 'show_json_edit_form' ),
				// 投稿画面に表示
				lineconnectConst::POST_TYPE_ACTION,
				// 投稿画面の下に表示
				'advanced',
				// 優先度(default)
				'default'
			);
		// }
	}
*/
/*
	// 管理画面用にスクリプト読み込み
	static function wpdocs_plugin_admin_scripts() {
		$js_file = 'react-jsonschema-form/dist/main.js';
		wp_enqueue_script( lineconnect::PLUGIN_PREFIX . 'action', plugins_url( $js_file, __DIR__ ), array( 'wp-element', 'wp-i18n' ), filemtime( plugin_dir_path( __DIR__ ) . $js_file ), true );
		// JavaScriptの言語ファイル読み込み
		wp_set_script_translations( lineconnect::PLUGIN_PREFIX . 'action', lineconnect::PLUGIN_NAME, plugin_dir_path( __DIR__ ) . 'languages' );
	}

	// 管理画面用にスタイル読み込み
	static function wpdocs_plugin_admin_styles() {
		$css_file = 'react-jsonschema-form/dist/style.css';
		wp_enqueue_style( lineconnect::PLUGIN_PREFIX . 'action-css', plugins_url( $css_file, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $css_file ) );
	}
	*/

	// 管理画面（投稿ページ）用にスクリプト読み込み
	/*
	static function wpdocs_selectively_enqueue_admin_script() {
		require_once plugin_dir_path( __FILE__ ) . 'rjsf.php';
		lineconnectRJSF::wpdocs_selectively_enqueue_admin_script(lineconnectConst::POST_TYPE_ACTION);
	}
	*/

	/**
	 * JSONスキーマからフォームを表示
	 */
	/*
	static function show_json_edit_form() {
		$ary_init_data = array();
		$schema_version = get_post_meta( get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true );
		$formData                          = get_post_meta( get_the_ID(), lineconnect::META_KEY__ACTION_DATA, true );
		$form = array(
			array(
				'id' => 'action',
				'schema' => apply_filters( lineconnect::FILTER_PREFIX . 'lineconnect_action_schema', lineconnectConst::$lineconnect_action_schema ),
				'uiSchema' => apply_filters( lineconnect::FILTER_PREFIX . 'lineconnect_action_uischema', lineconnectConst::$lineconnect_action_uischema),
				'formData' => self::get_form_data($formData ?? null, $schema_version),
				'props' => new stdClass(),
			),
		);
		// $ary_init_data['ajax_nonce'] = wp_create_nonce( lineconnect::CREDENTIAL_ACTION__ACTION );
		// $schema_file                 = plugin_dir_path( __DIR__ ) . 'docs/schema/lineconnect_action.schema.json';
		// $ary_init_data['mainSchema']                    = lineconnectConst::$lineconnect_action_schema;// json_decode( file_get_contents( $schema_file ), true );
		// $ary_init_data['mainUiSchema']                  = lineconnectConst::$lineconnect_action_uischema;
		// $formData                                   = get_post_meta( get_the_ID(), lineconnect::META_KEY__ACTION_DATA, true );
		// $ary_init_data['formData']                  = ! empty( $formData ) ? $formData : new StdClass();
		
		$ary_init_data['formName']                  = lineconnect::PARAMETER_PREFIX . lineconnect::PARAMETER__ACTION_DATA;
		$ary_init_data['form']        = $form;
		$ary_init_data['translateString']           = lineconnectConst::$lineconnect_rjsf_translate_string;
		$ary_init_data['translateString']['%1 Key'] = __( 'Property Name', lineconnect::PLUGIN_NAME );

		//$inidata = json_encode( $ary_init_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE );
		// nonceフィールドを生成・取得
		$nonce_field = wp_nonce_field(
			lineconnect::CREDENTIAL_ACTION__ACTION,
			lineconnect::CREDENTIAL_NAME__ACTION,
			true,
			false
		);
		require_once plugin_dir_path( __FILE__ ) . 'rjsf.php';
		lineconnectRJSF::show_json_edit_form($ary_init_data, $nonce_field );
*/
		/*
		error_log( json_encode( $ary_init_data['mainSchema'], JSON_PRETTY_PRINT ) );
		$hidden_json_filed = '<input type="hidden" id="' . $formName . '" name="' . $formName . '">';

		echo $nonce_field;
		echo <<< EOM
		{$hidden_json_filed}
		<div id="app"></div>
		<script>
		var lc_initdata = {$inidata};
		</script>
EOM;
*/
/*
	}
*/

	/**
	 * Return form data
	 */
	/*
	static function get_form_data($formData, $schema_version) {
		if(empty($schema_version) || $schema_version == lineconnectConst::ACTION_SCHEMA_VERSION){
			return !empty($formData) ? $formData : new stdClass();
		}
		// if old schema veersion, migrate and return
	}
	*/

	/**
	 * 記事を保存
	 */
	/*
	static function save_post_action( $post_ID, $post, $update ) {
		if ( isset( $_POST[ lineconnect::CREDENTIAL_NAME__ACTION ] ) && check_admin_referer( lineconnect::CREDENTIAL_ACTION__ACTION, lineconnect::CREDENTIAL_NAME__ACTION ) ) {
			$action_data = isset( $_POST[ lineconnect::PARAMETER_PREFIX . lineconnect::PARAMETER__ACTION_DATA ] ) ?  stripslashes( $_POST[ lineconnect::PARAMETER_PREFIX . lineconnect::PARAMETER__ACTION_DATA ]  ) : '';
			error_log( print_r( $action_data, true ) );
			if ( ! empty( $action_data ) ) {
				$json_action_data = json_decode( $action_data, true );
				if ( ! empty( $json_action_data[0] ) ) {
					update_post_meta( $post_ID, lineconnect::META_KEY__ACTION_DATA, $json_action_data[0] );
				} else {
					delete_post_meta( $post_ID, lineconnect::META_KEY__ACTION_DATA );
				}
			} else {
				delete_post_meta( $post_ID, lineconnect::META_KEY__ACTION_DATA );
			}
		}
	}
	*/

	/**
	 * Return action array object post_id and title
	 */
	static function get_lineconnect_action_name_array() {
		/*
		$args              = array(
			'post_type'      => lineconnectConst::POST_TYPE_ACTION,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);
		$action_name_array = array();
		$posts             = get_posts( $args );
		*/
		$lineconnect_actions = apply_filters(lineconnect::FILTER_PREFIX . 'actions', lineconnectConst::$lineconnect_actions); 
		foreach ( $lineconnect_actions as $name => $action ) {
			$action_name_array[ $name ] = $action['title'];
		}
		return $action_name_array;
	}

	/**
	 * Return action array object post_id and action data
	 */
	static function get_lineconnect_action_data_array() {
		/*
		$args              = array(
			'post_type'      => lineconnectConst::POST_TYPE_ACTION,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);
		$posts             = get_posts( $args );
		*/
		$lineconnect_actions = apply_filters(lineconnect::FILTER_PREFIX . 'actions', lineconnectConst::$lineconnect_actions); 
		return $lineconnect_actions;
		/*
		$action_data_array = array();
		foreach ( $lineconnect_actions as $name => $action ) {
			$action_data_array[ $name ] = array(
				'title'       => $post->post_title,
				'action_data' => get_post_meta( $post->ID, lineconnect::META_KEY__ACTION_DATA, true ),
			);
		}
		return $action_data_array;
		*/
	}

	static function do_action($actions, $chains, $event = null, $secret_prefix = null){
		require_once plugin_dir_path( __FILE__ ) . '../vendor/autoload.php';

		$message = array();
		$injection_data = array(
			'return' => array(),
			'webhook' => self::merge_postback_data_to_params(json_decode(json_encode($event), true)),
			'user' =>  $event ? lineconnect::get_userdata_from_line_id( $secret_prefix, $event->{'source'}->{'userId'} ) : [],
		);
		// error_log(print_r($injection_data['user'], true));
		foreach ( $actions as $action_idx => $action ) {
			if ( isset( $action['action_name'] ) ) {
				//$function_schema = get_post_meta( $action['action_id'], lineconnect::META_KEY__ACTION_DATA, true );
				$function_schema = self::get_lineconnect_action_data_array()[$action['action_name']];
				if ( isset( $function_schema ) ) {
					$function_name = $action['action_name'];//$function_schema['function'];
					if ( isset( $function_schema['namespace'] ) ) {
						if ( ! class_exists( $function_schema['namespace'] ) ) {
							$error = array(
								'error' => "NameError: namespace '$function_schema[namespace]' is not exists",
								'abort' => true,
							);
						}
						try {
							$class_name = new $function_schema['namespace']();
							if ( $secret_prefix && method_exists( $class_name, 'set_secret_prefix' ) ) {
								$class_name->set_secret_prefix( $secret_prefix );
							}
							if ( $event && method_exists( $class_name, 'set_event' ) ) {
								$class_name->set_event( $event );
							}
						} catch ( \Exception $e ) {
							$error = array(
								'error' => "NameError: namespace '$function_schema[namespace]' is not exists",
								'abort' => true,
							);
						}
						if ( ! method_exists( $class_name, $function_name ) ) {
							$error = array(
								'error' => "NameError: name '$function_name' in namespace '$function_schema[namespace]' is not defined",
								'abort' => true,
							);
						}
					} elseif ( ! function_exists( $function_name ) ) {
						$error = array(
							'error' => "NameError: name '$function_name' is not exists",
							'abort' => true,
						);
					}
					error_log( 'class response:' . print_r( array( $class_name, $function_name ), true ) );
					if ( ! isset( $error ) ) {
						$arguments_array = null;
						if(isset($function_schema['parameters'])){
							$action_parameters =  lineconnectUtil::inject_param($action_idx, $action['parameters'], $chains);
							$arguments_parsed = lineconnectUtil::prepare_arguments($action_parameters , $function_schema['parameters'], $injection_data);
							$arguments_array = lineconnectUtil::arguments_object_to_array( $arguments_parsed, $function_schema['parameters'] );
						}
						error_log('arguments:'.print_r($arguments_array, true));
						if ( isset( $function_schema['namespace'] ) ) {
							if ( empty( $function_schema['parameters'] ) ) {
								$response = call_user_func( array( $class_name, $function_name ) );
							} else {
								$response = call_user_func_array( array( $class_name, $function_name ), $arguments_array );
							}
						} elseif ( empty( $function_schema['parameters'] ) ) {
							$response = call_user_func( $function_name );
						} else {
							$response = call_user_func_array( $function_name, $arguments_array );// $response = $function_name( $arguments_array );
						}
						$injection_data['return'][$action_idx+1] = $response;
						// error_log(print_r($injection_data, true));
						if ( isset( $action['response_return_value'] ) && $action['response_return_value'] === true ) {
							$message[] = lineconnectUtil::get_line_message_builder($response);
						}
					} else {
						$message[] = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder( $error['error'] );

					}
				}
			}
		}
		return $message;
	}

	/**
	 * ポストバックイベントのデータを解析し、paramsにマージして返す関数
	 * 
	 * @param array $event ポストバックイベントデータ
	 * @return array パラメータをマージしたイベント配列
	 */
	static function merge_postback_data_to_params($event) {
		// 初期値: paramsを取得
		$params = $event['postback']['params'] ?? [];

		// postback.dataを取得してクエリ文字列として扱う
		if (!empty($event['postback']['data'])) {
			parse_str($event['postback']['data'], $data_params);

			// データが解析できた場合はparamsにマージする
			if (is_array($data_params)) {
				$params = array_merge($params, $data_params);
				// $eventにマージ
				$event['postback']['params'] = $params;
			}
		}

		return $event;
	}

}
