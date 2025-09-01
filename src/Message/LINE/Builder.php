<?php

/**
 * Lineconnect Message Class
 *
 * Build and Send LINE Message
 *
 * @category Components
 * @package  Message
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

namespace Shipweb\LineConnect\Message\LINE;

use Shipweb\LineConnect\Core\Stats;
use Shipweb\LineConnect\Utilities\StreamConnector;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Message\Message as SLCMessage;
use Shipweb\LineConnect\Message\LINE\Logger as MessageLogger;


class Builder {
	// Text Component
	static function createTextComponent($text, $atts = null) {
		$atts = wp_parse_args(
			$atts,
			array(
				'color'    => lineconnect::get_option('body_text_color'),
				'align'    => 'center',
				'flex'     => 1,
				'size'     => null, //'md',
				'wrap'     => false,
				'maxLines' => 0,
				'margin'   => 'none',
				'gravity'  => null, //'top',
				'weight'   => null, //'regular',
			)
		);

		$textComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder(
			$text,
			$atts['flex'],
			$atts['margin'], // Margin
			$atts['size'], // FontSize
			$atts['align'], // Align
			$atts['gravity'], // Gravity
			$atts['wrap'], // wrap
			$atts['maxLines'], // maxLines
			$atts['weight'], // FontWeight
			$atts['color'], // color
			null // TemplateActionBuilder
		);
		return $textComponent;
	}

	// PostbackAction
	static function createPostbackAction($label, $data, $displayText = null, $inputOption = null, $fillInText = null) {
		return new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($label, $data, $displayText, $inputOption, $fillInText);
	}

	// MessageTemplateActionBuilder
	static function createMessageTemplateActionBuilder($label, $text) {
		return new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder($label, $text);
	}

	// UriTemplateActionBuilder
	static function createUriTemplateActionBuilder($label, $uri) {
		return new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder($label, $uri);
	}

	// DatetimePickerTemplateActionBuilder
	static function createDatetimePickerTemplateActionBuilder($label, $data, $mode, $initial, $max, $min) {
		return new \LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder($label, $data, $mode, $initial, $max, $min);
	}

	// CameraRollTemplateActionBuilder
	static function createCameraRollTemplateActionBuilder($label) {
		return new \LINE\LINEBot\TemplateActionBuilder\CameraRollTemplateActionBuilder($label);
	}

	// LocationTemplateActionBuilder
	static function createLocationTemplateActionBuilder($label) {
		return new \LINE\LINEBot\TemplateActionBuilder\LocationTemplateActionBuilder($label);
	}

	// CameraTemplateActionBuilder
	static function createCameraTemplateActionBuilder($label) {
		return new \LINE\LINEBot\TemplateActionBuilder\CameraTemplateActionBuilder($label);
	}

	// RichmenuSwitchTemplateActionBuilder
	static function createRichmenuSwitchTemplateActionBuilder($richMenuAliasId, $data, $label = null) {
		return new \LINE\LINEBot\TemplateActionBuilder\RichmenuSwitchTemplateActionBuilder($richMenuAliasId, $data, $label);
	}

	// ClipboardTemplateActionBuilder
	static function createClipboardTemplateActionBuilder($label, $clipboardText) {
		return new \LINE\LINEBot\TemplateActionBuilder\ClipboardTemplateActionBuilder($label, $clipboardText);
	}

	// SenderMessageBuilder
	static function createSenderMessageBuilder($name, $iconUrl) {
		return new \LINE\LINEBot\SenderBuilder\SenderMessageBuilder($name, $iconUrl);
	}

	// ButtonTemplateBuilder
	static function createButtonTemplateBuilder($title, $text, $thumbnailImageUrl, array $actionBuilders, $imageAspectRatio = null,	$imageSize = null,	$imageBackgroundColor = null, $defaultAction = null) {
		return new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder(
			$title,
			$text,
			$thumbnailImageUrl,
			$actionBuilders,
			$imageAspectRatio,
			$imageSize,
			$imageBackgroundColor,
			$defaultAction
		);
	}

	// ConfirmTemplateBuilder
	static function createConfirmTemplateBuilder($text, $actionBuilders) {
		return new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder(
			$text,
			$actionBuilders
		);
	}

	// CarouselColumnTemplateBuilder
	static function createCarouselColumnTemplateBuilder($title, $text, $thumbnailImageUrl, $actions, $imageBackgroundColor = null, $defaultAction = null) {
		return new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder(
			$title,
			$text,
			$thumbnailImageUrl,
			$actions,
			$imageBackgroundColor,
			$defaultAction
		);
	}

	// CarouselTemplateBuilder
	static function createCarouselTemplateBuilder($columnTemplateBuilders, $imageAspectRatio = null, $imageSize = null) {
		return new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder(
			$columnTemplateBuilders,
			$imageAspectRatio,
			$imageSize
		);
	}

	// ImageCarouselColumnTemplateBuilder
	static function createImageCarouselColumnTemplateBuilder($imageUrl, $actionBuilder) {
		return new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder(
			$imageUrl,
			$actionBuilder
		);
	}

	// createImageCarouselTemplateBuilder
	static function createImageCarouselTemplateBuilder($columnTemplateBuilders) {
		return new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder(
			$columnTemplateBuilders
		);
	}

	// TemplateMessageBuilder
	static function createTemplateMessageBuilder($altText, $templateBuilder, $quickReply = null, $sender = null) {
		return new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
			$altText,
			$templateBuilder,
			$quickReply,
			$sender
		);
	}

	// Button Component
	static function createButtonComponent($action, $atts = null) {
		if (isset($atts['style']) && ($atts['style'] == 'button' || $atts['style'] == 'primary')) {
			$default_color    = lineconnect::get_option('link_text_color');
			$background_color = lineconnect::get_option('link_button_background_color');
			$border_color     = lineconnect::get_option('link_button_background_color');
		} elseif (isset($atts['style']) && $atts['style'] == 'secondary') {
			$default_color    = lineconnect::get_option('link_button_background_color');
			$background_color = lineconnect::get_option('title_backgraound_color');
			$border_color     = lineconnect::get_option('link_button_background_color');
		} else {
			$default_color    = lineconnect::get_option('link_text_color');
			$background_color = lineconnect::get_option('title_backgraound_color');
			$border_color     = null;
		}

		$atts = wp_parse_args(
			$atts,
			array(
				'color'           => $default_color,
				'backgroundColor' => $background_color,
				'border_color'    => $border_color,
				'layout'          => 'vertical',
				'flex'            => 1,
				'margin'          => null, //'none',
				'height'          => 'md',
				'style'           => 'link',
				'spacing'         => null, //'none',
				'cornerRadius'    => '5px',
				'alignItems'      => 'center',
				'paddingAll'      => 'lg',
				'gravity'         => null, //' top ',
				'wrap'            => false,
			)
		);

		// make action
		if (isset($action['type']) && $action['type'] === 'message') {
			$templateAction = self::createMessageTemplateActionBuilder($action['label'], $action['link']);
		} elseif (isset($action['type']) && $action['type'] === 'postback') {
			$templateAction = self::createPostbackAction($action['label'], $action['link'], $action['displayText'] ?? null);
		} elseif (isset($action['type']) && $action['type'] === 'uri') {
			$templateAction = self::createUriTemplateActionBuilder($action['label'], $action['link']);
		} else {
			$templateAction = null;
		}

		// make text component
		if (isset($action['label'])) {
			$label = $action['label'];
		} else {
			$label = 'Button';
		}
		$textComponent = self::createTextComponent($label, array(
			'color' => $atts['color'],
			'wrap' => $atts['wrap'],
		));

		$buttonComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder(
			$atts['layout'], // layout
			array($textComponent), // ComponentBuilders
			$atts['flex'], // flex
			$atts['spacing'], // spacing
			$atts['margin'], // margin
			$templateAction,
		);

		$buttonComponent->setAlignItems($atts['alignItems']);
		$buttonComponent->setPaddingAll($atts['paddingAll']);

		// set border color
		if (! empty($atts['border_color'])) {
			$buttonComponent->setBorderColor($atts['border_color']);
			$buttonComponent->setCornerRadius($atts['cornerRadius']);
			$buttonComponent->setBorderWidth('1px');
		}

		// set background color
		if (! empty($atts['backgroundColor'])) {
			$buttonComponent->setBackgroundColor($atts['backgroundColor']);
		}

		// set width
		if (isset($atts['width'])) {
			$buttonComponent->setWidth($atts['width']);
		}

		// set justifyContent
		if (isset($atts['justifyContent'])) {
			$buttonComponent->setJustifyContent($atts['justifyContent']);
		}

		return $buttonComponent;
	}

	//Box Component
	static function createBoxComponent($componentBuilders, $action = null, $atts = null) {
		$atts = wp_parse_args($atts, array(
			'background_color' => lineconnect::get_option('title_backgraound_color'),
			'flex' => 1,
			'margin' => null,
			'layout' => 'vertical',
			'spacing' => null,
			'paddingAll' => null,
		));

		$boxComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder(
			$atts['layout'], //Layout
			$componentBuilders, //ComponentBuilders
			$atts['flex'], //Flex
			$atts['spacing'], //Spacing
			$atts['margin'], //Margin
			$action //Action
		);
		$boxComponent->setBackgroundColor($atts['background_color']);
		//set justifyContent
		if (isset($atts['justifyContent'])) {
			$boxComponent->setJustifyContent($atts['justifyContent']);
		}
		//set paddingAll
		if (isset($atts['paddingAll'])) {
			$boxComponent->setPaddingAll($atts['paddingAll']);
		}
		return $boxComponent;
	}

	//Multi Column box
	static function createMultiColumnBoxComponent($componentBuilders, $atts = null) {
		$row = [];
		foreach ($componentBuilders as $rows) {
			$row_box = self::createBoxComponent($rows, null, [
				'layout' => 'horizontal',
				'justifyContent' => 'space-between',
			]);
			$row[] = $row_box;
		}
		$body = self::createBoxComponent($row, null, [
			'spacing' => 'lg',
		]);
		return $body;
	}

	// Flexメッセージを作成
	static function createFlexMessage($data, $atts = null) {
		// LINEBOT SDKの読み込み
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		// 設定ファイルの読み込み
		// require_once(plugin_dir_path(__FILE__).'../config.php');

		$atts = wp_parse_args(
			$atts,
			array(
				'aspect_rate'                  => lineconnect::get_option('image_aspectrate'),
				'aspect_mode'                  => lineconnect::get_option('image_aspectmode'),
				'background_color'             => lineconnect::get_option('title_backgraound_color'),
				'title_rows'                   => lineconnect::get_option('title_rows'),
				'body_rows'                    => lineconnect::get_option('body_rows'),
				'title_color'                  => lineconnect::get_option('title_text_color'),
				'body_color'                   => lineconnect::get_option('body_text_color'),
				'link_color'                   => lineconnect::get_option('link_text_color'),
				'link_button_style'            => lineconnect::get_option('link_button_style'),
				'link_button_background_color' => lineconnect::get_option('link_button_background_color'),
			)
		);

		$alttext = $data['title'] . "\r\n" . $data['body'];
		if (mb_strlen($alttext) > 400) {
			$alttext = mb_substr($alttext, 0, 399) . '…';
		}

		// サムネイル画像があれば
		if (!empty($data['thumb'])) {
			// サムネイル画像のImageコンポーネント
			$thumbImageComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder($data['thumb'], null, 'none', null, null, '100%', $atts['aspect_rate'], $atts['aspect_mode']);

			// ヒーローブロック
			$thumbBoxComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder('vertical', array($thumbImageComponent), null, 'none', 'none');
			$thumbBoxComponent->setPaddingAll('none');
		} else {
			$thumbBoxComponent = null;
		}

		// タイトルのTextコンポーネント
		$titleTextComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($data['title'], null, null, null, null, null, true, intval($atts['title_rows']), 'bold', $atts['title_color'], null);

		// ヘッダーブロック
		$titleBoxComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder('vertical', array($titleTextComponent), null, null, 'none');
		$titleBoxComponent->setPaddingTop('xl');
		$titleBoxComponent->setPaddingBottom('xs');
		$titleBoxComponent->setPaddingStart('xl');
		$titleBoxComponent->setPaddingEnd('xl');

		// 本文のTextコンポーネント
		$bodyTextComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($data['body'], null, null, null, null, null, true, intval($atts['body_rows']), null, $atts['body_color'], null);

		// ボディブロック
		$bodyBoxComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder('vertical', array($bodyTextComponent), null, null, 'none');
		$bodyBoxComponent->setPaddingBottom('none');
		$bodyBoxComponent->setPaddingTop('xs');
		$bodyBoxComponent->setPaddingStart('xl');
		$bodyBoxComponent->setPaddingEnd('xl');

		/*
		if ($data['type'] == "uri") {
			//リンクアクションコンポーネント
			$linkActionBuilder = new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder($data['label'], $data['link']);
		} elseif ($data['type'] == "postback") {
			//ポストバックアクションコンポーネント
			$linkActionBuilder = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($data['label'], $data['link']);
		}
		//リンクのボタンコンポーネント
		$linkButtonComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder($linkActionBuilder, NULL, NULL, NULL, $atts['link_button_style'], ( 'link' === $atts['link_button_style'] ? $atts['link_color'] : $atts['link_button_background_color']), NULL);
		*/
		$linkButtonComponent = self::createButtonComponent(
			$data,
			array(
				'style'  => $atts['link_button_style'],
				'margin' => 'sm',
			)
		);
		// フッターブロック
		$footerBoxComponent = new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder('vertical', array($linkButtonComponent), null, null, 'none');
		$footerBoxComponent->setPaddingTop('sm');

		// ブロックスタイル
		$blockStyleBuilder = new \LINE\LINEBot\MessageBuilder\Flex\BlockStyleBuilder($atts['background_color']);

		// バブルスタイル
		$bubbleStyleBuilder = new \LINE\LINEBot\MessageBuilder\Flex\BubbleStylesBuilder($blockStyleBuilder, $blockStyleBuilder, $blockStyleBuilder, $blockStyleBuilder);

		// バブルコンテナ
		$bubbleContainerBuilder = new \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder(null, $thumbBoxComponent, $titleBoxComponent, $bodyBoxComponent, $footerBoxComponent, $bubbleStyleBuilder);

		// Flexメッセージ
		return new \LINE\LINEBot\MessageBuilder\FlexMessageBuilder($alttext, $bubbleContainerBuilder);
	}

	// QuickReplayButtonBuilder
	static function createQuickReplayButtonBuilder($actionBuilder, $imageUri = null) {
		return new \LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder($actionBuilder, $imageUri);
	}

	// QuickReplyMessageBuilder
	static function createQuickReplyMessageBuilder($buttonBuilders) {
		return new \LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder($buttonBuilders);
	}

	// Textメッセージを作成
	static function createTextMessage($text, $quickReply = null, $sender = null, $extraTexts = null) {
		// LINEBOT SDKの読み込み
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text,  $quickReply, $sender, $extraTexts);
	}

	// Image message
	static function createImageMessage($originalContentUrl, $previewImageUrl, $quickReply = null, $sender = null) {
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		return new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalContentUrl, $previewImageUrl, $quickReply, $sender);
	}

	// StickerMessageBuilder
	static function createStickerMessage($packageId, $stickerId, $quickReply = null, $sender = null) {
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		return new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder($packageId, $stickerId, $quickReply, $sender);
	}

	// Video message
	static function createVideoMessage($originalContentUrl, $previewImageUrl, $trackingId = null, $quickReply = null, $sender = null) {
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		return new \LINE\LINEBot\MessageBuilder\VideoMessageBuilder($originalContentUrl, $previewImageUrl, $trackingId, $quickReply, $sender);
	}

	// Audio message
	static function createAudioMessage($originalContentUrl, $duration, $quickReply = null, $sender = null) {
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		return new \LINE\LINEBot\MessageBuilder\AudioMessageBuilder($originalContentUrl, $duration, $quickReply, $sender);
	}

	// Location message
	static function createLocationMessage($title, $address, $latitude, $longitude, $quickReply = null, $sender = null) {
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		return new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($title, $address, $latitude, $longitude, $quickReply, $sender);
	}

	// Image map message
	static function createImageMapMessage($baseUrl, $altText,	$baseSizeBuilder, $imagemapActionBuilders, $quickReply, $videoBuilder, $sender) {
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		return new \LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder($baseUrl, $altText, $baseSizeBuilder, $imagemapActionBuilders, $quickReply, $videoBuilder, $sender);
	}

	// Raw message
	static function createFlexRawMessage($flex_content, $alttext = 'Flex Message', $quickReply = null, $sender = null) {
		// LINEBOT SDKの読み込み
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		if (is_string($flex_content)) {
			$flex_content = json_decode($flex_content, true);
		}
		$messageBuilder = array(
			'type'     => 'flex',
			'altText'  => $alttext,
			'contents' => $flex_content,
		);
		if ($quickReply) {
			$messageBuilder['quickReply'] = \LINE\LINEBot\Util\BuildUtil::build($quickReply, 'buildQuickReply');
		}
		if ($sender) {
			$messageBuilder['sender'] = \LINE\LINEBot\Util\BuildUtil::build($sender, 'buildSender');
		}
		return new \LINE\LINEBot\MessageBuilder\RawMessageBuilder($messageBuilder);
	}

	// Raw message
	static function createRawMessage($raw_message_object, $quickReply = null, $sender = null) {
		// LINEBOT SDKの読み込み
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		if (is_string($raw_message_object)) {
			$raw_message_object = json_decode($raw_message_object, true);
		}

		if ($quickReply) {
			$raw_message_object['quickReply'] = \LINE\LINEBot\Util\BuildUtil::build($quickReply, 'buildQuickReply');
		}
		if ($sender) {
			$raw_message_object['sender'] = \LINE\LINEBot\Util\BuildUtil::build($sender, 'buildSender');
		}
		return new \LINE\LINEBot\MessageBuilder\RawMessageBuilder($raw_message_object);
	}

	// 連携済みユーザーへロールを指定して送信($role に slc_linked が含まれるなら全ての連携済みユーザーへ送信)
	static function sendMessageRole($channel, $role, $message) {
		if (! $channel) {
			$channel = lineconnect::get_channel(0);
		}

		if (is_string($message)) {
			$message = self::createTextMessage($message);
		}

		$secret_prefix = substr($channel['channel-secret'], 0, 4);

		if (! is_array($role)) {
			$role = array($role);
		}

		// $roleが"slc_linked"が含まれる場合は全てのロールユーザーに送信
		if (in_array('slc_linked', $role)) {
			$role = array();
		}
		// 設定されているロールユーザーに送信
		$args          = array(
			'meta_query' => array(
				array(
					'key'     => lineconnect::META_KEY__LINE,
					'compare' => 'EXISTS',
				),
			),
			'role__in'   => $role,
			'fields'     => 'all_with_meta',
		);
		$line_user_ids = array();   // 送信するLINEユーザーIDの配列
		$user_query    = new \WP_User_Query($args); // 条件を指定してWordPressからユーザーを検索
		$users         = $user_query->get_results(); // クエリ実行
		if (! empty($users)) {   // マッチするユーザーが見つかれば
			// ユーザーのメタデータを取得
			foreach ($users as $user) {
				$user_meta_line = $user->get(lineconnect::META_KEY__LINE);
				if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
					if (isset($user_meta_line[$secret_prefix]['id'])) {
						$line_user_ids[] = $user_meta_line[$secret_prefix]['id'];
					}
				}
			}
			return self::sendMulticastMessage($channel, $line_user_ids, $message);
		} else {
			return array(
				'success' => true,
				'num'     => 0,
			);
			// $error_message = '条件にマッチするユーザーがいませんでした';
		}
	}

	// 連携済みユーザーへWPユーザーを指定して送信
	static function sendMessageWpUser($channel, $wp_user_id, $message) {
		if (! $channel) {
			$channel = lineconnect::get_channel(0);
		}

		if (is_string($message)) {
			$message = self::createTextMessage($message);
		}

		$secret_prefix  = substr($channel['channel-secret'], 0, 4);
		$user_meta_line = get_user_meta($wp_user_id, lineconnect::META_KEY__LINE, true);
		if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
			if (isset($user_meta_line[$secret_prefix]['id'])) {
				return self::sendPushMessage($channel, $user_meta_line[$secret_prefix]['id'], $message);
			}
		}
	}

	//オーディエンスに送信
	static function sendAudienceMessage($recepient, $message, $notificationDisabled = false) {
		// LINEBOT SDKの読み込み
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		$ary_success_message = array();
		$ary_error_message   = array();
		foreach ($recepient as $secret_prefix => $recepient_item) {
			$error_message        = $success_message = '';
			$channel = lineconnect::get_channel($secret_prefix);
			$type = $recepient_item['type'];
			// if multicast and has placeholder then push
			if ($type == 'multicast' && \Shipweb\LineConnect\Utilities\PlaceholderReplacer::has_object_placeholder($message)) {
				$type = 'push';
			}

			if ($type == 'broadcast') {
				$message = self::get_line_message_builder($message);
				$response = self::sendBroadcastMessage($channel, $message, $notificationDisabled);
				if ($response['success']) {
					$success_message = __('Broadcast message sent successfully.', lineconnect::PLUGIN_NAME);
				} else {
					$error_message = __('Broadcast message failed to send.', lineconnect::PLUGIN_NAME) . $response['message'];
				}
			} elseif ($type == 'multicast') {
				$message = self::get_line_message_builder($message);
				$response = self::sendMulticastMessage($channel, $recepient_item['line_user_ids'], $message, $notificationDisabled);
				if ($response['success']) {
					// $success_message = __('Multicast message sent successfully.', lineconnect::PLUGIN_NAME);
					$success_message = sprintf(_n('Multicast message sent to %s person.', 'Multicast message sent to %s people.', $response['num'], lineconnect::PLUGIN_NAME), number_format($response['num']));
				} else {
					$error_message = __('Multicast message failed to send.', lineconnect::PLUGIN_NAME) . $response['message'];
				}
			} elseif ($type == 'push') {
				$ary_push_success = array();
				$ary_push_error = array();
				foreach ($recepient_item['line_user_ids'] as $line_user_id) {
					$response = self::sendPushMessage($channel, $line_user_id, self::get_line_message_builder($message, self::make_injection_data($channel, $line_user_id)), $notificationDisabled);
					if ($response['success']) {
						$ary_push_success[] = $line_user_id;
					} else {
						$ary_push_error[] = $line_user_id;
					}
				}
				if (empty($ary_push_error)) {
					$success_message = sprintf(_n('Push message sent to %s person.', 'Push message sent to %s people.', count($ary_push_success), lineconnect::PLUGIN_NAME), number_format(count($ary_push_success)));
				} else {
					$error_message = sprintf(_n('Push message failed to sent to %s person.', 'Push message failed to sent to %s people.', count($ary_push_error), lineconnect::PLUGIN_NAME), number_format(count($ary_push_error)));
				}
			}

			// 送信に成功した場合
			if ($success_message) {
				$ary_success_message[] = $channel['name'] . ': ' . $success_message;
			}
			// 送信に失敗した場合
			else {
				$ary_error_message[] = $channel['name'] . ': ' . $error_message;
			}
		}

		$result = array(
			'success' => empty($ary_error_message),
			'message' => implode("\n", array_merge($ary_error_message, $ary_success_message)),
			'success_messages' => $ary_success_message,
			'error_messages'   => $ary_error_message,
		);
		return $result;
	}

	/**
	 * レシピエントオブジェクトからメッセージオブジェクトを検証する
	 * @param array $recepient レシピエント
	 * @param \LINE\LINEBot\MessageBuilder\MultiMessageBuilder $message メッセージオブジェクト
	 * @return array
	 */
	static function validateAudienceMessage($recepient, $message) {
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		$ary_success_message = array();
		$ary_error_message   = array();
		foreach ($recepient as $secret_prefix => $recepient_item) {
			$error_message        = $success_message = '';
			$channel = lineconnect::get_channel($secret_prefix);
			$type = $recepient_item['type'];
			// if multicast and has placeholder then push
			if ($type == 'multicast' && \Shipweb\LineConnect\Utilities\PlaceholderReplacer::has_object_placeholder($message)) {
				$type = 'push';
			}

			if ($type == 'broadcast') {
				$success_message .= __('Message will be sent to all users who have subscribed to this channel.', lineconnect::PLUGIN_NAME);
			} elseif ($type == 'multicast') {
				$success_message .= sprintf(_n('Message will be sent to %s person by multicast.', 'Message will be sent to %s people by multicast.', count($recepient_item['line_user_ids']), lineconnect::PLUGIN_NAME), number_format(count($recepient_item['line_user_ids'])));
			} elseif ($type == 'push') {
				$success_message .= sprintf(_n('Message will be sent to %s person by push.', 'Message will be sent to %s people by push.', count($recepient_item['line_user_ids']), lineconnect::PLUGIN_NAME), number_format(count($recepient_item['line_user_ids'])));
			}

			if ($type == 'push') {
				// $replaced_message = self::replacePlaceHolder($channel, $recepient_item['line_user_ids'][0], $message);
				$replaced_message = self::get_line_message_builder($message, self::make_injection_data($channel, $recepient_item['line_user_ids'][0]));
				// $replaced_message = self::replacePlaceHolder($channel, $recepient_item['line_user_ids'][0], $message);
			} else {
				$message = self::get_line_message_builder($message);
				$replaced_message = $message;
			}
			$success_message .= ' ';
			$response = self::validateMessage($type, $channel, $replaced_message);
			if ($response['success']) {
				$success_message .= __('Valid message.', lineconnect::PLUGIN_NAME);
			} else {
				$error_message .= __('Invalid message.', lineconnect::PLUGIN_NAME) . $response['message'];
			}

			// 送信に成功した場合
			if ($error_message === '') {
				$ary_success_message[] = $channel['name'] . ': ' . $success_message;
			}
			// 送信に失敗した場合
			else {
				$ary_error_message[] = $channel['name'] . ': ' . $error_message;
			}
		}

		$result = array(
			'success' => empty($ary_error_message),
			'message' => implode("\n", array_merge($ary_error_message, $ary_success_message)),
			'success_messages' => $ary_success_message,
			'error_messages'   => $ary_error_message,
		);
		return $result;
	}

	/**
	 * プッシュメッセージ用にプレースホルダーを置換する
	 * @param array $channel チャネル情報
	 * @param string $line_user_id LINEユーザーID
	 * @param \LINE\LINEBot\MessageBuilder\MultiMessageBuilder $message プレースホルダーを含んだメッセージオブジェクト
	 * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder $message 置換済みメッセージオブジェクト
	 */
	static function replacePlaceHolder($channel, $line_user_id, $message) {
		// メッセージに含まれるプレースホルダーへユーザーデータの埋め込み
		$injection_data = self::make_injection_data($channel, $line_user_id);
		$messages = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($message->buildMessage(), $injection_data);
		// メッセージオブジェクトの再構築
		$multimessagebuilder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
		foreach ($messages as $msg) {
			$multimessagebuilder->add(self::createRawMessage($msg));
		}
		return $multimessagebuilder;
	}

	/**
	 * ユーザー用のインジェクションデータを作成する
	 * 
	 * @param array $channnel チャネル情報
	 * @param string $line_user_id LINEユーザーID
	 * @return array
	 */
	static function make_injection_data($channel, $line_user_id) {
		$user_data = lineconnect::get_userdata_from_line_id($channel['prefix'], $line_user_id);
		$injection_data = array(
			'user' => $user_data,
		);
		return $injection_data;
	}

	//応答メッセージをロギング
	static function writeOutboundMessageLog($message, $type, $line_user_id, $secret_prefix, $response) {
		$source_type = 'system';
		if (current_user_can('manage_options')) {
			$source_type = 'admin';
		} elseif (current_user_can('read')) {
			$source_type = 'user';
		}
		// 応答メッセージをロギング
		MessageLogger::writeOutboundMessageLog(
			$message,
			$type,
			$source_type,
			$line_user_id,
			$secret_prefix,
			$response->isSucceeded() ? 'sent' : 'failed',
			$response->getJSONDecodedBody()
		);
	}


	// プッシュ（一人のユーザーに送信）
	static function sendPushMessage($channel, $line_user_id, $message, $notificationDisabled = false) {
		// LINEBOT SDKの読み込み
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret       = $channel['channel-secret'];

		// LINE BOT
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channel_secret));
		$message = self::replacePlaceHolder($channel, $line_user_id, $message);
		// プッシュで送信
		$response = $bot->pushMessage($line_user_id, $message, $notificationDisabled);
		// 応答メッセージをロギング
		self::writeOutboundMessageLog($message, 'push', $line_user_id, $channel['prefix'], $response);

		// 送信に成功した場合
		if ($response->getHTTPStatus() === 200) {
			if (class_exists('WP_Stream\Connector')) {
				$class = new StreamConnector();
				$class->callback_lineconnect_push_message(
					array(
						'id'    => null,
						'title' => sprintf(__('Push to %s', lineconnect::PLUGIN_NAME), $line_user_id),
					),
					false
				);
			}
			Stats::increase_stats_message($channel['prefix'], 'apiPush', 1);
			return array('success' => true);
		} else {
			return array(
				'success' => false,
				'message' => self::prettyPrintLINEMessagingAPIError($response->getJSONDecodedBody()),
			);
		}
	}

	// マルチキャスト（複数のユーザーに送信）
	static function sendMulticastMessage($channel, $line_user_ids, $message, $notificationDisabled = false) {
		// LINEBOT SDKの読み込み
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret       = $channel['channel-secret'];

		// LINE BOT
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channel_secret));

		// 最大500人なので、500個ごとに配列を分割して送信
		foreach (array_chunk($line_user_ids, 500) as $line_user_id_chunk) {
			// マルチキャストで送信
			$response = $bot->multicast($line_user_id_chunk, $message, $notificationDisabled);
			// 応答メッセージをロギング
			self::writeOutboundMessageLog($message, 'multicast', $line_user_id_chunk, $channel['prefix'], $response);

			if ($response->getHTTPStatus() !== 200) {
				// error_log(print_r($response->getJSONDecodedBody(),true));
				return array(
					'success' => false,
					'message' => self::prettyPrintLINEMessagingAPIError($response->getJSONDecodedBody()),
				);
			}
		}
		if (class_exists('WP_Stream\Connector')) {
			$class = new StreamConnector();
			$class->callback_lineconnect_push_message(
				array(
					'id'    => null,
					'title' => sprintf(_n('%s multicast', '%s multicasts', count($line_user_ids), lineconnect::PLUGIN_NAME), number_format(count($line_user_ids))),
				),
				false
			);
		}
		Stats::increase_stats_message($channel['prefix'], 'apiMulticast', count($line_user_ids));
		// 送信に成功した場合
		return array(
			'success' => true,
			'num'     => count($line_user_ids),
		);
	}

	// ブロードキャスト（すべての友達登録されているユーザーに送信）
	static function sendBroadcastMessage($channel, $message, $notificationDisabled = false) {
		// LINEBOT SDKの読み込み
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret       = $channel['channel-secret'];

		// LINE BOT
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channel_secret));

		$response = $bot->broadcast($message, $notificationDisabled);
		// 応答メッセージをロギング
		self::writeOutboundMessageLog($message, 'broadcast', [], $channel['prefix'], $response);
		if ($response->getHTTPStatus() === 200) {
			if (class_exists('WP_Stream\Connector')) {
				$class = new StreamConnector();
				$class->callback_lineconnect_push_message(
					array(
						'id'    => null,
						'title' => __('Broadcast', lineconnect::PLUGIN_NAME),
					),
					false
				);
			}
			Stats::increase_stats_message($channel['prefix'], 'apiBroadcast', null);
			return array('success' => true);
		} else {
			return array(
				'success' => false,
				'message' => self::prettyPrintLINEMessagingAPIError($response->getJSONDecodedBody()),
			);
		}
	}

	// LINEメッセージ送信時のエラーを文字列に変換
	static function prettyPrintLINEMessagingAPIError($error) {
		// エラーのメインメッセージ
		$output = "<h2>" . __('Error', lineconnect::PLUGIN_NAME) . ": " . htmlspecialchars(\Shipweb\LineConnect\Utilities\Translate::dynamic_translate($error['message']), ENT_QUOTES, 'UTF-8') . "</h2>";

		// エラーの詳細がある場合
		if (isset($error['details']) && is_array($error['details'])) {
			$output .= "<ul>";
			foreach ($error['details'] as $detail) {
				$output .= "<li>";
				$output .= "<strong>" . __('Property', lineconnect::PLUGIN_NAME) . ": </strong> " . htmlspecialchars(\Shipweb\LineConnect\Utilities\Translate::dynamic_translate($detail['property']), ENT_QUOTES, 'UTF-8') . "<br>";
				$output .= "<strong>" . __('Message', lineconnect::PLUGIN_NAME) . ": </strong> " . htmlspecialchars(\Shipweb\LineConnect\Utilities\Translate::dynamic_translate($detail['message']), ENT_QUOTES, 'UTF-8');
				$output .= "</li>";
			}
			$output .= "</ul>";
		}

		return $output;
	}

	/**
	 * メッセージオブジェクトを検証
	 * @param string $type
	 * @param array $channel
	 * @param \LINE\LINEBot\MessageBuilder\MultiMessageBuilder $message
	 * @return array
	 */
	static function validateMessage($type, $channel, $message) {
		// LINEBOT SDKの読み込み
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret       = $channel['channel-secret'];

		// LINE BOT
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channel_secret));

		// バリデーション
		switch ($type) {
			case 'broadcast':
				$response = $bot->validateBroadcastMessage($message);
				break;
			case 'multicast':
				$response = $bot->validateMulticastMessage($message);
				break;
			case 'push':
				$response = $bot->validatePushMessage($message);
				break;
			case 'reply':
				$response = $bot->validateReplyMessage($message);
				break;
			case 'narrowcast':
				$response = $bot->validateNarrowcastMessage($message);
				break;
		}
		if ($response->getHTTPStatus() === 200) {
			return array('success' => true);
		} else {
			return array(
				'success' => false,
				'message' => self::prettyPrintLINEMessagingAPIError($response->getJSONDecodedBody()),
			);
		}
	}

	/**
	 * メッセージの配列からマルチメッセージを作成
	 * @param array $messages メッセージの配列
	 * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder
	 */

	public static function createMultiMessage(array $messages): \LINE\LINEBot\MessageBuilder\MultiMessageBuilder {
		$multiMessageBuilder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
		foreach ($messages as $message) {
			$multiMessageBuilder->add($message);
		}
		return $multiMessageBuilder;
	}

	public static function get_line_message_builder($source, $args = null) {
		if ($source instanceof \LINE\LINEBot\MessageBuilder) {
			return $source;
		}
		if (is_numeric($source)) {
			$message = SLCMessage::get_lineconnect_message($source, $args);
			if ($message) {
				return $message;
			}
		}
		if (is_string($source)) {
			return self::get_line_message_builder_from_string($source);
		}

		if (is_array($source)) {
			//message formdataの場合はformData_to_multimessageを呼び出す
			$isFormdata = true;
			if (count($source) > 10) {
				$isFormdata = false;
			}
			for ($i = 0; $i < count($source); $i += 2) {
				if (!empty($source[$i]) && !empty($source[$i + 1])) {
					if (!isset($source[$i]['type']) ||  ! in_array(
						$source[$i]['type'],
						array(
							'text',
							'sticker',
							'image',
							'video',
							'audio',
							'location',
							'imagemap',
							'button_template',
							'confirm_template',
							'carousel_template',
							'image_carousel_template',
							'flex',
							'raw',
						)
					)) {
						$isFormdata = false;
					}
				}
			}
			if ($isFormdata) {
				return SLCMessage::formData_to_multimessage($source, $args);
			}
		}
		return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(print_r($source, true));
	}

	/**
	 * 文字列からLINEメッセージビルダーを作成する関数
	 * @param string $source ソース: 文字列
	 * @return \LINE\LINEBot\MessageBuilder メッセージビルダー
	 */
	public static function get_line_message_builder_from_string($source) {
		// 文字列がJSON形式の場合は、JSONをデコードしてオブジェクトに変換し、RawMessageBuilderを作成
		$json = \Shipweb\LineConnect\Utilities\StringUtil::extractAndDecodeJson($source);
		if (is_array($json) && isset($json['type']) && in_array($json['type'], Constants::LINE_MESSAGE_TYPES)) {
			$rawmessage = new \LINE\LINEBot\MessageBuilder\RawMessageBuilder($json);
			// validate message
			$channels = lineconnect::get_all_channels();
			$validate_response = Builder::validateMessage('reply', $channels[0], $rawmessage);
			if ($validate_response['success']) {
				return $rawmessage;
			}
		}
		// 文字列がJSON形式でない場合は、通常のテキストメッセージビルダーを作成
		return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($source);
	}
}
