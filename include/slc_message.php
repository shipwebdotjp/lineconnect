<?php

/**
 * Lineconnect SLC Message Class
 *
 * LINE Connect SLC Message
 *
 * @category Components
 * @package  SLC Message
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

use Shipweb\LineConnect\Components\ReactJsonSchemaForm;

class lineconnectSLCMessage {

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
			__('LINE Connect Message', lineconnect::PLUGIN_NAME),
			// メニュータイトル：
			__('Messages', lineconnect::PLUGIN_NAME),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			'edit.php?post_type=' . lineconnectConst::POST_TYPE_MESSAGE,
			// メニューに紐づく画面を描画するcallback関数：
			false,
			// メニューの位置
			NULL
		);
		// remove_menu_page( lineconnect::SLUG__DM_FORM );
	}

	static function register_meta_box() {
		// 投稿ページと固定ページ両方でLINE送信チェックボックスを表示
		// $screens = lineconnect::get_option('send_post_types');
		// foreach ($screens as $screen) {
		add_meta_box(
			// チェックボックスのID
			lineconnect::META_KEY__MESSAGE_DATA,
			// チェックボックスのラベル名
			'LINE Connect Message',
			// チェックボックスを表示するコールバック関数
			array('lineconnectSLCMessage', 'show_json_edit_form'),
			// 投稿画面に表示
			lineconnectConst::POST_TYPE_MESSAGE,
			// 投稿画面の下に表示
			'advanced',
			// 優先度(default)
			'default'
		);
		// }
	}

	// 管理画面（投稿ページ）用にスクリプト読み込み
	static function wpdocs_selectively_enqueue_admin_script() {
		// require_once plugin_dir_path(__FILE__) . 'rjsf.php';
		ReactJsonSchemaForm::wpdocs_selectively_enqueue_admin_script(lineconnectConst::POST_TYPE_MESSAGE);
	}

	/**
	 * JSONスキーマからフォームを表示
	 */
	static function show_json_edit_form() {
		$ary_init_data = array();
		$formName                         = lineconnect::PARAMETER__MESSAGE_DATA;
		$ary_init_data['formName']        = $formName;
		$schema_version = get_post_meta(get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true);

		$formData                         = get_post_meta(get_the_ID(), lineconnect::META_KEY__MESSAGE_DATA, true);
		$subSchema = self::get_message_schema();
		$form = array();
		for ($i = 0; $i < 10; $i += 2) {
			$type_schema = lineconnectConst::$lineconnect_message_type_schema;
			$type_schema['title'] = sprintf('%s (%d/%d)', __('Message', lineconnect::PLUGIN_NAME), ($i / 2) + 1, 5);
			$form[] = array(
				'id' => 'type',
				'schema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_type_schema', $type_schema),
				'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_type_uischema', lineconnectConst::$lineconnect_message_type_uischema),
				'formData' => self::get_form_type_data($formData[$i] ?? null, $schema_version),
				'props' => new stdClass(),
			);
			$form[] = array(
				'id' => 'message',
				'schema' => ! empty($formData[$i]["type"]) ? $subSchema[$formData[$i]["type"]] : new stdClass(),
				'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_uischema', lineconnectConst::$lineconnect_message_uischema),
				'formData' => self::get_form_message_data($formData[$i + 1] ?? null, $schema_version),
				'props' => new stdClass(),
			);
		}
		$ary_init_data['subSchema']          = $subSchema;

		$ary_init_data['form']        = $form;
		$ary_init_data['translateString'] = lineconnectConst::$lineconnect_rjsf_translate_string;
		// $ary_init_data['translateString']['%1 Key'] = __( 'Property Name', lineconnect::PLUGIN_NAME );
		// nonceフィールドを生成・取得
		$nonce_field = wp_nonce_field(
			lineconnect::CREDENTIAL_ACTION__MESSAGE,
			lineconnect::CREDENTIAL_NAME__MESSAGE,
			true,
			false
		);

		// require_once plugin_dir_path(__FILE__) . 'rjsf.php';
		ReactJsonSchemaForm::show_json_edit_form($ary_init_data, $nonce_field);

		/*
		$inidata = json_encode( $ary_init_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE );

		error_log( json_encode($formData, JSON_PRETTY_PRINT ) );
		// error_log( json_encode( $ary_init_data['form'], JSON_PRETTY_PRINT ) );
		$hidden_json_filed = '<input type="hidden" id="' . $formName . '" name="' . $formName . '">';
		
		echo $nonce_field;
		echo <<< EOM
		{$hidden_json_filed}
		<div id="app"></div>
		<script>
		var lc_initdata = {$inidata};
		document.addEventListener("DOMContentLoaded", (event) => {
			var origin_data = {};
			lc_initdata.form.map((form, index) => {
				if(Object.keys(form.formData).length !==0 ){
					origin_data[index] = form.formData;
				}
			});
			document.getElementById(lc_initdata['formName']).value = JSON.stringify(origin_data);
		});
		</script>
EOM;
*/
	}

	/**
	 * 記事を保存
	 */
	static function save_post_message($post_ID, $post, $update) {
		if (isset($_POST[lineconnect::CREDENTIAL_NAME__MESSAGE]) && check_admin_referer(lineconnect::CREDENTIAL_ACTION__MESSAGE, lineconnect::CREDENTIAL_NAME__MESSAGE)) {
			//error_log( print_r( $_POST[ lineconnect::PARAMETER_PREFIX . lineconnect::PARAMETER__MESSAGE_DATA ] , true ) );
			//error_log( print_r(stripslashes( $_POST[ lineconnect::PARAMETER_PREFIX . lineconnect::PARAMETER__MESSAGE_DATA ] ), true ) );
			//error_log( print_r(sanitize_text_field(stripslashes( $_POST[ lineconnect::PARAMETER_PREFIX . lineconnect::PARAMETER__MESSAGE_DATA ] )), true ) );
			$message_data = isset($_POST[lineconnect::PARAMETER__MESSAGE_DATA]) ?  stripslashes($_POST[lineconnect::PARAMETER__MESSAGE_DATA])  : '';
			if (! empty($message_data)) {
				$message_data_array = json_decode($message_data, true);
				if (! empty($message_data_array)) {
					update_post_meta($post_ID, lineconnect::META_KEY__MESSAGE_DATA, $message_data_array);
					update_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION, lineconnectConst::MESSAGE_SCHEMA_VERSION);
				} else {
					delete_post_meta($post_ID, lineconnect::META_KEY__MESSAGE_DATA);
					delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
				}
			} else {
				delete_post_meta($post_ID, lineconnect::META_KEY__MESSAGE_DATA);
				delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
			}
		}
	}

	/**
	 * メッセージのJSONスキーマを返す
	 */
	static function get_message_schema() {
		$message_schema = array();
		foreach (lineconnectConst::$lineconnect_message_types as $type => $schema) {
			$message_schema[$type] = apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_schema', lineconnectConst::$lineconnect_message_schema);
			$message_schema[$type]['properties']['message'] = $schema; //['properties']['messages']['items']
		}
		// $message_schema = apply_filters( lineconnect::FILTER_PREFIX . 'lineconnect_message_schema', lineconnectConst::$lineconnect_message_schema );
		return $message_schema;
	}

	/**
	 * Return type data
	 */
	static function get_form_type_data($formData, $schema_version) {
		if (empty($schema_version) || $schema_version == lineconnectConst::MESSAGE_SCHEMA_VERSION) {
			return !empty($formData) ? $formData : new stdClass();
		}
		// if old schema veersion, migrate and return
	}

	/** 
	 * Return message data
	 */
	static function get_form_message_data($formData, $schema_version) {
		if (empty($schema_version) || $schema_version == lineconnectConst::MESSAGE_SCHEMA_VERSION) {
			return !empty($formData) ? $formData : new stdClass();
		}
		// if old schema veersion, migrate and return
	}

	/**
	 * Return message array object post_id and title
	 */
	static function get_lineconnect_message_name_array() {
		$args          = array(
			'post_type'      => lineconnectConst::POST_TYPE_MESSAGE,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);
		$posts         = get_posts($args);
		$message_array = array();
		foreach ($posts as $post) {
			$message_array[$post->ID] = $post->post_title;
		}
		return $message_array;
	}

	/**
	 * Return LINE message object by post_id
	 */
	static function get_lineconnect_message($post_id, $args = null) {
		$formData        = get_post_meta($post_id, lineconnect::META_KEY__MESSAGE_DATA, true);
		if (empty($formData) || $formData === false) {
			return null;
		}
		return self::formData_to_multimessage($formData, $args);
	}

	static function formData_to_multimessage($formData, $args = null) {
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		$multimessagebuilder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
		$message_objects     = array();
		$message_data = array();
		if (! empty($formData)) {
			for ($i = 0; $i < 10; $i += 2) {

				if (! empty($formData[$i + 1])) {
					$message = array(
						'type' => $formData[$i]['type'],
						'message' => $formData[$i + 1],
					);
					$message_data[] = $message;
				}
			}
		}
		foreach ($message_data as $message_item) {
			$message_type = $message_item['type'];
			$message = lineconnectUtil::replace_object_placeholder($message_item['message'], $args);
			$message_object = $quickReply = $sender = null;
			if (! empty($message['quickReply'])) {
				$quickReplay_items = array();
				// foreach ( $message['quickReply'] as $quickReply_item ) {
				foreach ($message['quickReply']['items'] as $quickReplay_container) {
					$templateAction = self::buildTemplateActionBuilder($quickReplay_container['action']);
					/*
						$templateAction = null;
						if ( isset( $quickReply_item['type'] ) && $quickReply_item['action']['type'] === 'message' ) {
							$templateAction = lineconnectMessage::createMessageTemplateActionBuilder( $quickReply_item['action']['label'], $quickReply_item['action']['text'] );
						} elseif ( isset( $quickReply_item['action']['type'] ) && $quickReply_item['action']['type'] === 'postback' ) {
							$templateAction = lineconnectMessage::createPostbackAction( $quickReply_item['action']['label'], $quickReply_item['action']['data'], $quickReply_item['action']['displayText'], $quickReply_item['action']['inputOption'] ?? null, $quickReply_item['action']['fillInText'] ?? null );
						} elseif ( isset( $quickReply_item['action']['type'] ) && $quickReply_item['action']['type'] === 'uri' ) {
							$templateAction = lineconnectMessage::createUriTemplateActionBuilder( $quickReply_item['action']['label'], $quickReply_item['action']['uri'] );
						} elseif ( isset( $quickReply_item['action']['type'] ) && $quickReply_item['action']['type'] === 'datetimepicker' ) {
							$templateAction = lineconnectMessage::createDatetimePickerTemplateActionBuilder( $quickReply_item['action']['label'], $quickReply_item['action']['data'], $quickReply_item['action']['mode'], $quickReply_item['action']['initial'], $quickReply_item['action']['max'], $quickReply_item['action']['min'] );
						} elseif ( isset( $quickReply_item['action']['type'] ) && $quickReply_item['action']['type'] === 'cameraRoll' ) {
							$templateAction = lineconnectMessage::createCameraRollTemplateActionBuilder( $quickReply_item['action']['label'] );
						} elseif ( isset( $quickReply_item['action']['type'] ) && $quickReply_item['action']['type'] === 'camera' ) {
							$templateAction = lineconnectMessage::createCameraTemplateActionBuilder( $quickReply_item['action']['label'] );
						} elseif ( isset( $quickReply_item['action']['type'] ) && $quickReply_item['action']['type'] === 'location' ) {
							$templateAction = lineconnectMessage::createLocationTemplateActionBuilder( $quickReply_item['action']['label'] );
						}
						*/
					if (! empty($templateAction)) {
						$quickReplay_button  = lineconnectMessage::createQuickReplayButtonBuilder($templateAction, $quickReplay_container['imageUrl'] ?? null);
						$quickReplay_items[] = $quickReplay_button;
					}
				}
				// }
				if (! empty($quickReplay_items)) {
					$quickReply = lineconnectMessage::createQuickReplyMessageBuilder($quickReplay_items);
				}
			}
			if (! empty($message['sender'])) {
				$sender = lineconnectMessage::createSenderMessageBuilder(isset($message['sender']['name']) ? $message['sender']['name'] : null, isset($message['sender']['iconUrl']) ? $message['sender']['iconUrl'] : null);
			}

			if ('text' === $message_type) {
				$message_object = lineconnectMessage::createTextMessage($message['message']['text']['text'], $quickReply, $sender);
			} elseif ('sticker' === $message_type) {
				$message_object = lineconnectMessage::createStickerMessage($message['message']['sticker']['packageId'], $message['message']['sticker']['stickerId'], $quickReply, $sender);
			} elseif ('image' === $message_type) {
				$message_object = lineconnectMessage::createImageMessage($message['message']['image']['originalContentUrl'], $message['message']['image']['previewImageUrl'], $quickReply, $sender);
			} elseif ('video' === $message_type) {
				$message_object = lineconnectMessage::createVideoMessage($message['message']['video']['originalContentUrl'], $message['message']['video']['previewImageUrl'], $message['message']['video']['trackingId'], $quickReply, $sender);
			} elseif ('audio' === $message_type) {
				$message_object = lineconnectMessage::createAudioMessage($message['message']['audio']['originalContentUrl'], $message['message']['audio']['duration'], $quickReply, $sender);
			} elseif ('location' === $message_type) {
				$message_object = lineconnectMessage::createLocationMessage($message['message']['location']['title'], $message['message']['location']['address'], $message['message']['location']['latitude'], $message['message']['location']['longitude'], $quickReply, $sender);
			} elseif ('imagemap' === $message_type) {
				$message_object = self::buildImagemapMessage($message, $quickReply, $sender);
			} elseif ('button_template' === $message_type) {
				$message_object = self::buildButtonTemplateMessage($message, $quickReply, $sender);
			} elseif ('confirm_template' === $message_type) {
				$message_object = self::buildConfirmTemplateMessage($message, $quickReply, $sender);
			} elseif ('carousel_template' === $message_type) {
				$message_object = self::buildCarouselTemplateBuilder($message, $quickReply, $sender);
			} elseif ('image_carousel_template' === $message_type) {
				$message_object = self::buildImageCarouselTemplateBuilder($message, $quickReply, $sender);
			} elseif ('flex' === $message_type) {
				$message_object = lineconnectMessage::createFlexRawMessage($message['message']['flex']['raw'], $message['message']['flex']['alttext'] ?? 'Flex message', $quickReply, $sender);
			} elseif ('raw' === $message_type) {
				$message_object = lineconnectMessage::createRawMessage($message['message']['raw']['raw'], $quickReply, $sender);
			} else {
				$message_object = null;
			}
			if ($message_object) {
				$message_objects[] = $message_object;
			}
		}
		foreach ($message_objects as $message_object) {
			// error_log( print_r( $message_object, true ) );
			$multimessagebuilder->add($message_object);
		}
		return $multimessagebuilder;
	}

	static function buildImagemapMessage($message, $quickReply, $sender) {
		require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		$video = null;
		$actions = [];
		if (!empty($message['message']['imagemap']['video']['originalContentUrl'])) {
			$area = new \LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(
				$message['message']['imagemap']['video']['area']['x'],
				$message['message']['imagemap']['video']['area']['y'],
				$message['message']['imagemap']['video']['area']['width'],
				$message['message']['imagemap']['video']['area']['height']
			);
			$externalLink = new \LINE\LINEBot\MessageBuilder\Imagemap\ExternalLinkBuilder(
				$message['message']['imagemap']['video']['externalLink']['linkUri'],
				$message['message']['imagemap']['video']['externalLink']['label']
			);
			$video = new \LINE\LINEBot\MessageBuilder\Imagemap\VideoBuilder(
				$message['message']['imagemap']['video']['originalContentUrl'],
				$message['message']['imagemap']['video']['previewImageUrl'],
				$area,
				$externalLink
			);
		}
		$baseSize = new \LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder(
			$message['message']['imagemap']['baseSize']['height'],
			$message['message']['imagemap']['baseSize']['width']
		);
		if (!empty($message['message']['imagemap']['actions'])) {
			foreach ($message['message']['imagemap']['actions'] as $action) {
				$ImagemapActionBuilder = null;
				$area = new \LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(
					$action['area']['x'],
					$action['area']['y'],
					$action['area']['width'],
					$action['area']['height']
				);
				if ($action['type'] === 'message') {
					$ImagemapActionBuilder = new \LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder(
						$action['text'],
						$area
					);
				} elseif ($action['type'] === 'uri') {
					$ImagemapActionBuilder = new \LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder(
						$action['linkUri'],
						$area
					);
				}
				if ($ImagemapActionBuilder) {
					$actions[] = $ImagemapActionBuilder;
				}
			}
		}
		return lineconnectMessage::createImageMapMessage(
			$message['message']['imagemap']['baseUrl'],
			$message['message']['imagemap']['altText'],
			$baseSize,
			$actions,
			$quickReply,
			$video,
			$sender
		);
	}

	static function buildButtonTemplateMessage($message, $quickReply, $sender) {
		$actionBuilders = self::builderActions($message['message']['button_template']['actions']);
		$defaultAction = self::buildTemplateActionBuilder($message['message']['button_template']['defaultAction']);
		$buttonTemplate = lineconnectMessage::createButtonTemplateBuilder(
			isset($message['message']['button_template']['title']) ? $message['message']['button_template']['title'] : null,
			$message['message']['button_template']['text'],
			isset($message['message']['button_template']['thumbnailImageUrl']) ? $message['message']['button_template']['thumbnailImageUrl'] : null,
			$actionBuilders,
			isset($message['message']['button_template']['imageAspectRatio']) ? $message['message']['button_template']['imageAspectRatio'] : null,
			isset($message['message']['button_template']['imageSize']) ? $message['message']['button_template']['imageSize'] : null,
			isset($message['message']['button_template']['imageBackgroundColor']) ? $message['message']['button_template']['imageBackgroundColor'] : null,
			$defaultAction
		);
		return lineconnectMessage::createTemplateMessageBuilder(
			$message['message']['altText'],
			$buttonTemplate,
			$quickReply,
			$sender
		);
	}

	static function buildConfirmTemplateMessage($message, $quickReply, $sender) {
		$actionBuilders = self::builderActions($message['message']['confirm_template']['actions']);
		$confirmTemplate = lineconnectMessage::createConfirmTemplateBuilder(
			$message['message']['confirm_template']['text'],
			$actionBuilders
		);
		return lineconnectMessage::createTemplateMessageBuilder(
			$message['message']['altText'],
			$confirmTemplate,
			$quickReply,
			$sender
		);
	}

	static function buildCarouselTemplateBuilder($message, $quickReply, $sender) {
		$columnTemplateBuilders = [];
		if (!empty($message['message']['carousel_template']['columns'])) {
			foreach ($message['message']['carousel_template']['columns'] as $column) {
				$actionBuilders = self::builderActions($column['actions']);
				$defaultAction = self::buildTemplateActionBuilder($column['defaultAction'] ?? null);
				$columnTemplateBuilders[] = lineconnectMessage::createCarouselColumnTemplateBuilder(
					$column['title'] ?? null,
					$column['text'],
					$column['thumbnailImageUrl'] ?? null,
					$actionBuilders,
					$column['imageBackgroundColor'] ?? null,
					$defaultAction
				);
			}
		}
		$carouselTemplate = lineconnectMessage::createCarouselTemplateBuilder(
			$columnTemplateBuilders,
			$message['message']['carousel_template']['imageAspectRatio'] ?? null,
			$message['message']['carousel_template']['imageSize'] ?? null
		);
		return lineconnectMessage::createTemplateMessageBuilder(
			$message['message']['altText'],
			$carouselTemplate,
			$quickReply,
			$sender
		);
	}

	static function buildImageCarouselTemplateBuilder($message, $quickReply, $sender) {
		$columnTemplateBuilders = [];
		if (!empty($message['message']['image_carousel_template']['columns'])) {
			foreach ($message['message']['image_carousel_template']['columns'] as $column) {
				$actionBuilder = self::buildTemplateActionBuilder($column['action']);
				$columnTemplateBuilders[] = lineconnectMessage::createImageCarouselColumnTemplateBuilder(
					$column['imageUrl'],
					$actionBuilder,
				);
			}
		}
		$imageCarouselTemplate = lineconnectMessage::createImageCarouselTemplateBuilder(
			$columnTemplateBuilders
		);
		return lineconnectMessage::createTemplateMessageBuilder(
			$message['message']['altText'],
			$imageCarouselTemplate,
			$quickReply,
			$sender
		);
	}

	static function builderActions($actions) {
		$actionBuilders = [];
		if (!empty($actions)) {
			foreach ($actions as $action) {
				$templateAction = self::buildTemplateActionBuilder($action);
				if (!empty($templateAction)) {
					$actionBuilders[] = $templateAction;
				}
			}
		}
		return $actionBuilders;
	}

	static function buildTemplateActionBuilder($action) {
		$templateAction = null;
		if (!empty($action['message'])) {
			$templateAction = lineconnectMessage::createMessageTemplateActionBuilder($action['message']['label'] ?? null, $action['message']['text']);
		} elseif (!empty($action['postback'])) {
			$templateAction = lineconnectMessage::createPostbackAction($action['postback']['label'] ?? null, $action['postback']['data'], $action['postback']['displayText'] ?? null, $action['postback']['inputOption'] ?? null, $action['postback']['fillInText'] ?? null);
		} elseif (!empty($action['uri'])) {
			$templateAction = lineconnectMessage::createUriTemplateActionBuilder($action['uri']['label'] ?? null, $action['uri']['uri']);
		} elseif (!empty($action['datetimepicker'])) {
			$templateAction = lineconnectMessage::createDatetimePickerTemplateActionBuilder($action['datetimepicker']['label'] ?? null, $action['datetimepicker']['data'], $action['datetimepicker']['mode'], $action['datetimepicker']['initial'], $action['datetimepicker']['max'], $action['datetimepicker']['min']);
		} elseif (!empty($action['cameraRoll'])) {
			$templateAction = lineconnectMessage::createCameraRollTemplateActionBuilder($action['cameraRoll']['label']);
		} elseif (!empty($action['camera'])) {
			$templateAction = lineconnectMessage::createCameraTemplateActionBuilder($action['camera']['label']);
		} elseif (!empty($action['location'])) {
			$templateAction = lineconnectMessage::createLocationTemplateActionBuilder($action['location']['label']);
		} elseif (!empty($action['richmenuswitch'])) {
			$templateAction = lineconnectMessage::createRichMenuSwitchTemplateActionBuilder($action['richmenuswitch']['richMenuAliasId'], $action['richmenuswitch']['data'], $action['richmenuswitch']['label'] ?? null);
		} elseif (!empty($action['clipboard'])) {
			$templateAction = lineconnectMessage::createClipboardTemplateActionBuilder($action['clipboard']['label'] ?? null, $action['clipboard']['clipboardText']);
		}
		return $templateAction;
	}


	// Ajaxでメッセージデータを返す
	static function ajax_get_slc_message() {
		$isSuccess = true;
		$formData = [];
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

		if (! isset($_POST['post_id']) || ! $_POST['post_id']) {
			$isSuccess = false;
		}


		if ($isSuccess) {
			$post_id = $_POST['post_id'];
			$formData  = get_post_meta($post_id, lineconnect::META_KEY__MESSAGE_DATA, true);
		}
		$result['result']  = $isSuccess ? 'success' : 'failed';
		$result['formData'] = $formData;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($result);
		wp_die();
	}
}
