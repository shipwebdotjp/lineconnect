<?php

/**
 * Lineconnect
 * 管理画面での一括メッセージ画面
 */
class lineconnectBulkMessage {
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
			__( 'LINE Connect Bulk message', lineconnect::PLUGIN_NAME ),
			// メニュータイトル：
			__( 'Bulk message', lineconnect::PLUGIN_NAME ),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			lineconnect::SLUG__BULKMESSAGE_FORM,
			// メニューに紐づく画面を描画するcallback関数：
			array( 'lineconnectBulkMessage', 'show_bulkmessage' ),
			// メニューの位置
			3
		);
		add_action( "admin_print_styles-{$page_hook_suffix}", array( 'lineconnectBulkMessage', 'wpdocs_plugin_admin_styles' ) );
		add_action( "admin_print_scripts-{$page_hook_suffix}", array( 'lineconnectBulkMessage', 'wpdocs_plugin_admin_scripts' ) );
	}

	/**
	 * メッセージ一括配信画面を表示
	 */
	static function show_bulkmessage() {
		$ary_init_data = array();
		// プラグインのオプション
		$ary_init_data['plugin_options'] = lineconnect::get_all_options();
		$ary_init_data['channels']       = lineconnect::get_all_channels();
		$ary_init_data['ajaxurl']        = admin_url( 'admin-ajax.php' );
		$ary_init_data['ajax_nonce']     = wp_create_nonce( lineconnect::CREDENTIAL_ACTION__POST );
		$messageFormData = [];
		$users                           = isset( $_GET['users'] ) ? $_GET['users'] : array();
		if ( ! is_array( $users ) ) {
			$users = array( $users );
		}
		
		$formName                         = 'chatform-data';
		$ary_init_data['formName']        = $formName;
		$messageSubSchema = lineconnectSLCMessage::get_message_schema();
		$messageForm = array();
		for($i = 0; $i < 10; $i+=2){

			$type_schema = lineconnectConst::$lineconnect_message_type_schema;
			$type_schema['title'] = sprintf('%s (%d/%d)', __( 'Message', lineconnect::PLUGIN_NAME ), ($i/2)+1, 5);
			$messageForm[] = array(
				'id' => 'type',
				'schema' => apply_filters( lineconnect::FILTER_PREFIX . 'lineconnect_message_type_schema', $type_schema ),
				'uiSchema' => apply_filters( lineconnect::FILTER_PREFIX . 'lineconnect_message_type_uischema', lineconnectConst::$lineconnect_message_uischema ),
				'formData' => lineconnectSLCMessage::get_form_type_data($messageFormData[$i] ?? null, null),
				'props' => new stdClass(),
			);
			$messageForm[] = array(
				'id' => 'message',
				'schema' => ! empty($messageFormData[$i]["type"]) ? $messageSubSchema[$messageFormData[$i]["type"]] : new stdClass(),
				'uiSchema' => apply_filters( lineconnect::FILTER_PREFIX . 'lineconnect_message_uischema', lineconnectConst::$lineconnect_message_uischema ),
				'formData' => lineconnectSLCMessage::get_form_message_data($messageFormData[$i+1] ?? null, null),
				'props' => new stdClass(),
			);
		}
		$ary_init_data['messageSubSchema']          = $messageSubSchema;
		
		$ary_init_data['messageForm']        = $messageForm;
		$ary_init_data['translateString'] = lineconnectConst::$lineconnect_rjsf_translate_string;

        // オーディエンスフォームのデータ
        $audience_formName = lineconnect::PARAMETER__AUDIENCE_DATA;
        $ary_init_data['audienceFormName'] = $audience_formName;
        $audience_schema = lineconnectAudience::get_audience_schema();
        $audience_form_data = [];
		if (!empty($users)) {
			$audience_form_data = array(
				'condition' => array(
					'conditions' => array(
						array( 'type' => 'wpUserId', 'wpUserId' => $users )
					)
				)
			);
		}
        $audience_form = array(
            'id' => 'audience',
            'schema' => $audience_schema,
            'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_audience_uischema', lineconnectConst::$lineconnect_audience_uischema),
            'formData' => $audience_form_data,
            'props' => new stdClass(),
        );
        $ary_init_data['audienceForm'] = array($audience_form);

		$slc_messages = [];
		foreach ( lineconnectSLCMessage::get_lineconnect_message_name_array() as $post_id => $title ) {
			$slc_messages[] = array(
				'post_id' => $post_id,
				'title' => $title,
			);
		}
		$ary_init_data['slc_messages'] = $slc_messages;

		$slc_audiences = [];
		foreach ( lineconnectAudience::get_lineconnect_audience_name_array() as $post_id => $title ) {
			$slc_audiences[] = array(
				'post_id' => $post_id,
				'title' => $title,
			);
		}
		$ary_init_data['slc_audiences'] = $slc_audiences;

		$inidata = json_encode( $ary_init_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE  );
		echo <<< EOM
<div id="line_chat_root"></div>
<script>
var lc_initdata = {$inidata};
</script>
EOM;
	}

	// メッセージ送信
	static function ajax_chat_send() {
		$result = self::do_chat_send();

		// $result['result']  = $isSuccess ? 'success' : 'failed';
		// $result['success'] = $ary_success_message;
		// $result['error']   = $ary_error_message;
		header( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode( $result );
		wp_die();
	}

	static function do_chat_send(){

		// ログインしていない場合は無視
		if ( ! is_user_logged_in() ) {
			return array(
				'result' => 'failed',
				'success' => array(),
				'error' => array(__('You are not logged in.', lineconnect::PLUGIN_NAME),),
			);
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if ( ! is_super_admin() && ! current_user_can( 'administrator' ) && ! current_user_can( 'editor' ) && ! current_user_can( 'author' ) ) {
			return array(
				'result' => 'failed',
				'success' => array(),
				'error' => array(__('You do not have permission to send messages.', lineconnect::PLUGIN_NAME),),
			);
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if ( ! isset( $_POST['nonce'] ) || ! $_POST['nonce'] || ! check_ajax_referer( lineconnect::CREDENTIAL_ACTION__POST, 'nonce' ) ) {
			return array(
				'result' => 'failed',
				'success' => array(),
				'error' => array(__('Nonce is not set or invalid.', lineconnect::PLUGIN_NAME),),
			);
		}

		$messages              = isset( $_POST['messages'] ) ? array_map('stripslashes_deep', $_POST['messages']) : [];
		$audience              = isset( $_POST['audience'] ) ? array_map('stripslashes_deep', $_POST['audience']) : [];
		$mode = isset( $_POST['mode'] ) ? $_POST['mode'] : '';
		if( in_array($mode, ['send', 'count']) === false ){
			$mode = 'send';
		}

		if($mode === 'send'){
			$message = lineconnectSLCMessage::formData_to_multimessage($messages);
			$recepient = lineconnectAudience::get_audience_by_condition($audience[0]['condition']??[]);
			if( empty( $recepient ) ){
				return array(
					'result' => 'success',
					'success' => array(__( 'There was no target to be sent that matched the condition.', lineconnect::PLUGIN_NAME )),
					'error' => array(),
				);
			}else{
				$response = lineconnectMessage::sendAudienceMessage($recepient, $message);
			}
		}elseif($mode === 'count'){
			$response = lineconnectAudience::get_recepients_count(lineconnectAudience::get_audience_by_condition($audience[0]['condition']??[]));
		}

		return array(
			'result' => $response['success'] ? 'success' : 'failed',
			'success' => $response['success_messages'],
			'error' => $response['error_messages'],
		);
		
	}


	// 管理画面用にスクリプト読み込み
	static function wpdocs_plugin_admin_scripts() {
		$chat_js = 'line-bulkmessage/dist/slc_bulkmessage.js';
		wp_enqueue_script( lineconnect::PLUGIN_PREFIX . 'bulkmessage', plugins_url( $chat_js, __DIR__ ), array( 'wp-element', 'wp-i18n' ), filemtime( plugin_dir_path( __DIR__ ) . $chat_js ), true );
		// JavaScriptの言語ファイル読み込み
		wp_set_script_translations( lineconnect::PLUGIN_PREFIX . 'bulkmessage', lineconnect::PLUGIN_NAME, plugin_dir_path( __DIR__ ) .'line-bulkmessage/languages' );
	}

	// 管理画面用にスタイル読み込み
	static function wpdocs_plugin_admin_styles() {
		$chat_css = 'line-bulkmessage/dist/style.css';
		wp_enqueue_style( lineconnect::PLUGIN_PREFIX . 'admin-css', plugins_url( $chat_css, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $chat_css ) );
		$override_css_file = 'react-jsonschema-form/dist/rjsf-override.css';
		wp_enqueue_style( lineconnect::PLUGIN_PREFIX . 'rjsf-override-css', plugins_url( $override_css_file, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $override_css_file ) );
	}
}
