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

class lineconnectMessage {
	//Text Component
	static function createTextComponent($text, $atts = null) {
		$atts = wp_parse_args($atts, array(
			'color' => lineconnect::get_option('body_text_color'),
			'align' => 'center',
			'flex' => 1,
			'size' => 'md',
			'wrap' => false,
			'maxLines' => 0,
			'margin' => 'none',
			'gravity' => 'top',
			'weight' => 'regular',
		));

		$textComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder(
			$text,
			$atts['flex'],
			$atts['margin'], //Margin
			$atts['size'], //FontSize
			$atts['align'], //Align
			$atts['gravity'], //Gravity
			$atts['wrap'], //wrap
			$atts['maxLines'], //maxLines
			$atts['weight'], //FontWeight
			$atts['color'], //color
			NULL //TemplateActionBuilder
		);
		return $textComponent;
	}

	//PostbackAction
	static function createPostbackAction($label, $data, $displayText = null, $inputOption = null, $fillInText = null) {
		$action = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($label, $data, $displayText, $inputOption, $fillInText);
		return $action;
	}

	//MessageTemplateActionBuilder
	static function createMessageTemplateActionBuilder($label, $text) {
		$action = new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder($label, $text);
		return $action;
	}

	//UriTemplateActionBuilder
	static function createUriTemplateActionBuilder($label, $uri) {
		$action = new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder($label, $uri);
		return $action;
	}

	//Button Component
	static function createButtonComponent($action, $atts = null) {
		if(isset($atts['style']) && $atts['style'] == 'button'){
			$default_color = lineconnect::get_option('link_text_color');
			$background_color = lineconnect::get_option('link_button_background_color');
			$border_color = lineconnect::get_option('link_button_background_color');
		}else{
			$default_color = lineconnect::get_option('link_text_color');
			$background_color = lineconnect::get_option('title_backgraound_color');
			$border_color = null;
		}


		$atts = wp_parse_args($atts, array(
			'color' => $default_color,
			'backgroundColor' => $background_color,
			'border_color' => $border_color,
			'layout' => 'vertical',
			'flex' => 1,
			'margin' => 'none',
			'height' => 'md',
			'style' => 'link',
			'spacing' => 'none',
			'cornerRadius' => '5px',
			'alignItems' => 'center',
			'paddingAll' => 'lg',
		));

		// make action
		if(isset($action['type']) && $action['type'] === 'message'){
			$templateAction = self::createMessageTemplateActionBuilder($action['label'], $action['link']);
		}elseif(isset($action['type']) && $action['type'] === 'postback'){
			$templateAction = self::createPostbackAction($action['label'], $action['link'], $action['link']??null);
		}elseif(isset($action['type']) && $action['type'] === 'uri'){
			$templateAction = self::createUriTemplateActionBuilder($action['label'], $action['link']);
		}else{
			$templateAction = null;
		}

		// make text component
		if(isset($action['label'])){
			$label = $action['label'];
		}else{
			$label = 'Button';
		}
		$textComponent = self::createTextComponent($label,['color'=>$atts['color']]);

		$buttonComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder(
			$atts['layout'], //layout
			[$textComponent], //ComponentBuilders
			$atts['flex'], //flex
			$atts['spacing'], //spacing
			$atts['margin'], //margin
			$templateAction,			
		);

		$buttonComponent->setAlignItems($atts['alignItems']);
		$buttonComponent->setPaddingAll($atts['paddingAll']);

		//set border color
		if(!empty($atts['border_color'])){
			$buttonComponent->setBorderColor($atts['border_color']);
			$buttonComponent->setCornerRadius($atts['cornerRadius']);
			$buttonComponent->setBorderWidth('1px');
		}

		//set background color
		if(!empty($atts['backgroundColor'])){
			$buttonComponent->setBackgroundColor($atts['backgroundColor']);
		}

		//set width
		if(isset($atts['width'])){
			$buttonComponent->setWidth($atts['width']);
		}

		return $buttonComponent;
	
	}

	//Flexメッセージを作成
	static function createFlexMessage($data, $atts = null) {
		// LINEBOT SDKの読み込み
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');
		// 設定ファイルの読み込み
		//require_once(plugin_dir_path(__FILE__).'../config.php');

		$atts = wp_parse_args($atts, array(
			'aspect_rate' => lineconnect::get_option('image_aspectrate'),
			'aspect_mode' => lineconnect::get_option('image_aspectmode'),
			'background_color' => lineconnect::get_option('title_backgraound_color'),
			'title_rows' => lineconnect::get_option('title_rows'),
			'body_rows' => lineconnect::get_option('body_rows'),
			'title_color' => lineconnect::get_option('title_text_color'),
			'body_color' => lineconnect::get_option('body_text_color'),
			'link_color' => lineconnect::get_option('link_text_color'),
			'link_button_style' => lineconnect::get_option('link_button_style'),
			'link_button_background_color' => lineconnect::get_option('link_button_background_color'),
		));

		$alttext = $data['title'] . "\r\n" . $data['body'];
		if (mb_strlen($alttext) > 400) {
			$alttext = mb_substr($alttext, 0, 399) . "…";
		}

		//サムネイル画像があれば
		if ($data['thumb'] != "") {
			//サムネイル画像のImageコンポーネント
			$thumbImageComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder($data['thumb'], NULL, 'none', NULL, NULL, '100%', $atts['aspect_rate'], $atts['aspect_mode']);

			//ヒーローブロック
			$thumbBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical", [$thumbImageComponent], NULL, 'none', 'none');
			$thumbBoxComponent->setPaddingAll('none');
		} else {
			$thumbBoxComponent = NULL;
		}

		//タイトルのTextコンポーネント
		$titleTextComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($data['title'], NULL, NULL, NULL, NULL, NULL, TRUE, intval($atts['title_rows']), 'bold', $atts['title_color'], NULL);

		//ヘッダーブロック
		$titleBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical", [$titleTextComponent], NULL, NULL, 'none');
		$titleBoxComponent->setPaddingTop('xl');
		$titleBoxComponent->setPaddingBottom('xs');
		$titleBoxComponent->setPaddingStart('xl');
		$titleBoxComponent->setPaddingEnd('xl');

		//本文のTextコンポーネント
		$bodyTextComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($data['body'], NULL, NULL, NULL, NULL, NULL, TRUE, intval($atts['body_rows']), NULL, $atts['body_color'], NULL);

		//ボディブロック
		$bodyBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical", [$bodyTextComponent], NULL, NULL, 'none');
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
		$linkButtonComponent = self::createButtonComponent($data, ['style' => $atts['link_button_style'], 'margin' => 'sm']);
		//フッターブロック
		$footerBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical", [$linkButtonComponent], NULL, NULL, 'none');
		$footerBoxComponent->setPaddingTop('sm');

		//ブロックスタイル
		$blockStyleBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\BlockStyleBuilder($atts['background_color']);

		//バブルスタイル
		$bubbleStyleBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\BubbleStylesBuilder($blockStyleBuilder, $blockStyleBuilder, $blockStyleBuilder, $blockStyleBuilder);

		//バブルコンテナ
		$bubbleContainerBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder(NULL, $thumbBoxComponent, $titleBoxComponent, $bodyBoxComponent, $footerBoxComponent, $bubbleStyleBuilder);

		//Flexメッセージ
		return new \LINE\LINEBot\MessageBuilder\FlexMessageBuilder($alttext, $bubbleContainerBuilder);
	}

	//Textメッセージを作成
	static function createTextMessage($text, $extraTexts = NULL) {
		// LINEBOT SDKの読み込み
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');
		return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text, $extraTexts);
	}

	//連携済みユーザーへロールを指定して送信($role に slc_linked が含まれるなら全ての連携済みユーザーへ送信)
	static function sendMessageRole($channel, $role, $message) {

		// Lineconnectの読み込み
		require_once(plugin_dir_path(__FILE__) . '../lineconnect.php');

		if (!$channel) {
			$channel = lineconnect::get_channel(0);
		}

		if (is_string($message)) {
			$message = self::createTextMessage($message);
		}

		$secret_prefix = substr($channel['channel-secret'], 0, 4);

		if (!is_array($role)) {
			$role = array($role);
		}

		//$roleが"slc_linked"が含まれる場合は全てのロールユーザーに送信
		if (in_array("slc_linked", $role)) {
			$role = array();
		}
		//設定されているロールユーザーに送信
		$args = array(
			'meta_query' => array(
				array(
					'key'     => lineconnect::META_KEY__LINE,
					'compare' => 'EXISTS'
				)
			),
			'role__in' => $role,
			'fields' => 'all_with_meta'
		);
		$line_user_ids = array();	//送信するLINEユーザーIDの配列
		$user_query = new WP_User_Query($args); //条件を指定してWordpressからユーザーを検索
		$users = $user_query->get_results(); //クエリ実行
		if (!empty($users)) {	//マッチするユーザーが見つかれば
			//ユーザーのメタデータを取得
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
			return array('success' => true, 'num' => 0);
			// $error_message = '条件にマッチするユーザーがいませんでした';
		}
	}

	//連携済みユーザーへWPユーザーを指定して送信
	static function sendMessageWpUser($channel, $wp_user_id, $message) {

		// Lineconnectの読み込み
		require_once(plugin_dir_path(__FILE__) . '../lineconnect.php');

		if (!$channel) {
			$channel = lineconnect::get_channel(0);
		}

		if (is_string($message)) {
			$message = self::createTextMessage($message);
		}

		$secret_prefix = substr($channel['channel-secret'], 0, 4);
		$user_meta_line = get_user_meta($wp_user_id, lineconnect::META_KEY__LINE, true);
		if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
			if (isset($user_meta_line[$secret_prefix]['id'])) {
				return self::sendPushMessage($channel,  $user_meta_line[$secret_prefix]['id'], $message);
			}
		}
	}

	//プッシュ（一人のユーザーに送信）
	static function sendPushMessage($channel, $line_user_id, $message) {
		// LINEBOT SDKの読み込み
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret = $channel['channel-secret'];

		//LINE BOT
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

		//プッシュで送信
		$response = $bot->pushMessage($line_user_id, $message);

		//送信に成功した場合
		if ($response->getHTTPStatus() === 200) {
			if (class_exists('lineconnectConnector')) {
				$class = new lineconnectConnector();
				$class->callback_lineconnect_push_message(array('id' => null, 'title' => sprintf(__('Push to %s', lineconnect::PLUGIN_NAME), $line_user_id)), false);
			}
			return array('success' => true);
		} else {
			return array('success' => false, 'message' => $response->getJSONDecodedBody()['message']);
		}
	}

	//マルチキャスト（複数のユーザーに送信）
	static function sendMulticastMessage($channel, $line_user_ids, $message) {
		// LINEBOT SDKの読み込み
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret = $channel['channel-secret'];

		//LINE BOT
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

		//最大500人なので、500個ごとに配列を分割して送信
		foreach (array_chunk($line_user_ids, 500) as $line_user_id_chunk) {
			//マルチキャストで送信
			$response = $bot->multicast($line_user_id_chunk, $message);
			if ($response->getHTTPStatus() !== 200) {
				return array('success' => false, 'message' => $response->getJSONDecodedBody()['message']);
			}
		}
		if (class_exists('lineconnectConnector')) {
			$class = new lineconnectConnector();
			$class->callback_lineconnect_push_message(
				array(
					'id' => null,
					'title' => sprintf(_n('%s multicast', '%s multicasts', count($line_user_ids), lineconnect::PLUGIN_NAME), number_format(count($line_user_ids)))
				),
				false
			);
		}
		//送信に成功した場合
		return array('success' => true, 'num' => count($line_user_ids));
	}

	//ブロードキャスト（すべての友達登録されているユーザーに送信）
	static function sendBroadcastMessage($channel, $message) {
		// LINEBOT SDKの読み込み
		require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret = $channel['channel-secret'];

		//LINE BOT
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

		$response = $bot->broadcast($message);
		if ($response->getHTTPStatus() === 200) {
			if (class_exists('lineconnectConnector')) {
				$class = new lineconnectConnector();
				$class->callback_lineconnect_push_message(array('id' => null, 'title' => __('Broadcast', lineconnect::PLUGIN_NAME)), false);
			}
			return array('success' => true);
		} else {
			return array('success' => false, 'message' => $response->getJSONDecodedBody()['message']);
		}
	}
}
