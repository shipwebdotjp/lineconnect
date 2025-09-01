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
			$validate_response = Validater::validateMessage('reply', $channels[0], $rawmessage);
			if ($validate_response['success']) {
				return $rawmessage;
			}
		}
		// 文字列がJSON形式でない場合は、通常のテキストメッセージビルダーを作成
		return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($source);
	}
}
