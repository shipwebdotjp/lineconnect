<?php
/*
LINE Bot
	Copyright 2020 shipweb
*/

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Core\Stats;
use Shipweb\LineConnect\Trigger\Webhook;
use Shipweb\LineConnect\Bot\File;
use Shipweb\LineConnect\Bot\Account;
use Shipweb\LineConnect\Action\Action;
use Shipweb\LineConnect\Bot\Log\Writer as BotLogWriter;
use Shipweb\LineConnect\Bot\Provider\OpenAi;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\Message\LINE\Logger as MessageLogger;
use Shipweb\LineConnect\PostType\Trigger\Trigger as TriggerPostType;

require_once '../../../wp-load.php'; // WordPressの基本機能を読み込み
// require_once 'vendor/autoload.php'; // LINE BOT SDKを読み込み
// require_once 'lineconnect.php'; // LINE Connectを読み込み
// require_once('include/message.php'); // メッセージ関連を読み込み

// JSONリクエストボディを取得
$json_string = file_get_contents('php://input');

// 検証結果
$valid_signature = false;

// チャンネルごとに署名を検証
foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
	// チャネルアクセストークン（長期）
	// チャネルシークレット
	$access_token  = $channel['channel-access-token'];
	$channelSecret = $channel['channel-secret'];

	// LINE ID KEY
	$secret_prefix = substr($channelSecret, 0, 4);

	// 署名を検証するためにチャネルシークレットを秘密鍵として、HMAC-SHA256アルゴリズムを使用してリクエストボディのダイジェスト値を取得
	$hash = hash_hmac('sha256', $json_string, $channelSecret, true);
	// ダイジェスト値をBase64エンコード
	$signature = base64_encode($hash);
	// HTTP HeaderからX-Line-Signatureを取得
	$XLineSignature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

	// 署名が一致する場合
	if ($signature == $XLineSignature) {
		$valid_signature = true;
		break;
	}
}

// 署名がどのチャンネルにも一致しない場合は400を返す
if (! $valid_signature) {
	http_response_code(400);
	print 'Bad signature';
	exit;
} else {
	// とりあえずステータスコード200を返す
	http_response_code(200);
	ob_start(null, 0, PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE); // 出力をバッファっていう感じで制御できるように、好きなタイミングで吐けるようにオプションをつけておく
	header('Content-length: ' . ob_get_length());
	header('Connection: close');
	while (ob_get_level() > 0) {
		ob_end_flush();
	}
	flush();
}

// JSONリクエストボディをデコード
$json_obj = json_decode($json_string);

foreach ($json_obj->{'events'} as $event) {
	$message = array();
	// ユーザーをDB登録
	Account::update_line_id_profile_for_new_user($secret_prefix, $event->{'source'}->{'userId'});
	// ログ書き込み
	$botlog                         = new BotLogWriter($event, $secret_prefix);
	$isEventDuplicationOrInsertedId = $botlog->writeChatLog();
	if ($isEventDuplicationOrInsertedId === true) {
		// イベントがすでに記録されていればスキップ
		continue;
	}

	// リプレイトークンを取得
	$reply_token = isset($event->{'replyToken'}) ? $event->{'replyToken'} : null;

	// イベントタイプを取得
	$type = $event->{'type'};

	if ($type === 'message') {    // メッセージ受け取り時

		// メッセージオブジェクトのタイプ
		$msg_obj = $event->{'message'}->{'type'};

		if ($msg_obj === 'text') {
			// テキストメッセージを受け取った時
			$msg_text = $event->{'message'}->{'text'};
			// テキストに 連携開始／解除キーワード が含まれていた場合
			if (strpos($msg_text, lineconnect::get_option('link_start_keyword')) !== false) {
				$userId = $event->{'source'}->{'userId'};

				// メタ情報からLINEユーザーIDでユーザー検索
				$user = lineconnect::get_wpuser_from_line_id($secret_prefix, $userId);
				if ($user) { // ユーザーが見つかればすでに連携されているということ
					$user_id = $user->ID; // IDを取得

					// 連携解除メッセージ作成
					$message[] = Builder::createFlexMessage(
						array(
							'title' => lineconnect::get_option('unlink_start_title'),
							'body'  => lineconnect::get_option('unlink_start_body'),
							'type'  => 'postback',
							'label' => lineconnect::get_option('unlink_start_button'),
							'link'  => 'action=unlink',
						)
					);
				} else {
					// 連携開始メッセージ作成
					$message[] = Account::getLinkStartMessage($secret_prefix, $userId);
				}
			}
		}
	} elseif ($type === 'accountLink') {
		// アカウントリンク時
		$link_obj = $event->{'link'};
		// アカウントリンク成功時
		if ($link_obj->{'result'} === 'ok') {

			// nonceを取得
			$nonce = $link_obj->{'nonce'};
			// nonceから対応するユーザーIDを取得
			$user_id = get_option('lineconnect_nonce' . $nonce);

			// nonceに対応するユーザーIDがあれば
			if ($user_id) {
				// LINE ユーザーID
				$userId = $event->{'source'}->{'userId'};

				// Bot作成
				$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
				$bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channelSecret));

				// ユーザーのプロフィール取得
				$response = $bot->getProfile($userId);
				// レスポンスをJSONデコード
				$profile = $response->getJSONDecodedBody();

				// nonceを削除
				delete_option('lineconnect_nonce' . $nonce);

				// WordPressユーザーのメタ情報にLINEユーザーIDを追加
				$line_user_data = get_user_meta($user_id, lineconnect::META_KEY__LINE, true);
				if (empty($line_user_data)) {
					$line_user_data = array();
				}
				$line_user_data[$secret_prefix] = array(
					'id'          => $userId,
					'displayName' => $profile['displayName'],
					'isFriend'    => true,
				);
				if (isset($profile['pictureUrl'])) {
					$line_user_data['pictureUrl'] = $profile['pictureUrl'];
				}
				update_user_meta($user_id, lineconnect::META_KEY__LINE, $line_user_data);

				// WP Line Loginと連携
				do_action('line_login_update_user_meta', $user_id, $line_user_data[$secret_prefix], $secret_prefix);

				// リッチメニューをセット
				do_action('line_link_richmenu', $user_id);

				//デイリー連携数を増加
				Stats::increase_daily_link($secret_prefix);

				// 連携完了のテキストメッセージ作成
				$message[] = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(lineconnect::get_option('link_finish_body'));
			} else {
				// 連携失敗のテキストメッセージ作成
				$message[] = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(lineconnect::get_option('link_failed_body'));
			}
		}
	} elseif ($type === 'postback') {
		// ポストバック受け取り時

		// 送られたデータ
		$postback = $event->{'postback'}->{'data'};

		if ($postback === 'action=unlink') {
			// 解除選択時
			$userId = $event->{'source'}->{'userId'};
			$mes    = Account::unAccountLink($secret_prefix, $userId);

			Stats::increase_daily_unlink($secret_prefix);

			// 連携解除完了のテキストメッセージ作成
			$message[] = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($mes);
		} elseif ($postback === 'action=link') {
			// 連携選択時
			$userId    = $event->{'source'}->{'userId'};
			$message[] = Account::getLinkStartMessage($secret_prefix, $userId);
		}
	} elseif ($type == 'follow') {
		$userId = $event->{'source'}->{'userId'};
		if (lineconnect::get_option('enable_link_autostart') === 'on') {
			// 友達登録時　自動連携開始がONであれば、アカウントリンクイベントを作成
			$message[] = Account::getLinkStartMessage($secret_prefix, $userId);
		}
		Account::update_line_id_follow($secret_prefix, $userId, true);
		Stats::increase_daily_followers($secret_prefix);
	} elseif ($type == 'unfollow') {
		// 友達登録解除（ブロック時）リストから消去
		$userId = $event->{'source'}->{'userId'};
		$mes    = Account::unAccountLink($secret_prefix, $userId);
		Account::update_line_id_follow($secret_prefix, $userId, false);
		Stats::increase_daily_unfollowers($secret_prefix);
	}

	// if message type is image,video,audio,file and contentProvider.type is line
	if ($type === 'message' && in_array($event->{'message'}->{'type'}, array('image', 'video', 'audio', 'file')) && $event->{'message'}->{'contentProvider'}->{'type'} === 'line') {
		// save content
		$saved_content_file_name = File::getMessageContent($secret_prefix, $event->{'message'}->{'id'}, isset($event->{'source'}->{'userId'}) ? $event->{'source'}->{'userId'} : '_none');

		// update filepath to log table
		$result = File::update_message_filepath($isEventDuplicationOrInsertedId, $saved_content_file_name);
	}

	//　インタラクション
	$session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
	$action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
	$message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
	$normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
	$validator = new Shipweb\LineConnect\Interaction\Validator();
	$interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
		$session_repository,
		$action_runner,
		$message_builder,
		$normalizer,
		$validator,
		new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
	);
	$interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
		$session_repository,
		$interaction_handler
	);
	if (isset($event->{'source'}->{'userId'})) {
		$interaction_messages = $interaction_manager->handleEvent($secret_prefix, $event->{'source'}->{'userId'}, $event);
		if (!empty($interaction_messages)) {
			$message = array_merge($message, $interaction_messages);
		}
	}

	// check if match trigger
	$triggers = array();
	$args     = array(
		'post_type'      => TriggerPostType::POST_TYPE,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	);
	$posts    = get_posts($args);
	foreach ($posts as $post) {
		$form = get_post_meta($post->ID, TriggerPostType::META_KEY_DATA, true);
		if (isset($form[0]['type']) && $form[0]['type'] === 'webhook') {
			$triggers[] = $form[1];
		}
	}

	// wp_reset_postdata();

	foreach ($triggers as $trigger) {
		$matched_array = array();
		// $trigger['triggers']の各条件のいずれかに一致するかどうかをチェック
		foreach ($trigger['triggers'] as $trigger_item) {
			$matched_array[] = Webhook::check_trigger_condition($trigger_item, $event, $secret_prefix);
		}

		// $trigger['triggers']の各条件のいずれかに一致する場合
		if (! in_array(true, $matched_array)) {
			// error_log('trigger not match' . print_r($matched_array, true));
			continue;
		}
		// error_log('trigger type match:' . print_r($trigger, true));

		if (isset($trigger['action'])) {
			$action_return = Action::do_action($trigger['action'], $trigger['chain'] ?? null, $event, $secret_prefix);
			if (!empty($action_return['messages'])) {
				$message = array_merge($message, $action_return['messages']);
			}
		}
	}

	if (empty($message) && $type === 'message' && $event->{'message'}->{'type'} === 'text' && $event->{'message'}->{'text'} != null && lineconnect::get_option('enableChatbot') == 'on') {
		// AIで応答する
		$openAi       = new OpenAi();
		$gptResponse  = $openAi->getResponseByChatGPT(
			$event,
			$secret_prefix,
			array(
				array(
					'role'    => 'user',
					'content' => $event->{'message'}->{'text'},
				),
			)
		);
		$message[]    = $gptResponse['message'];
		$responseByAi = $gptResponse['responseByAi'];
		// error_log('gpt response:' . print_r($gptResponse, true));
	}

	// 応答メッセージがあれば送信する
	if (! empty($message) && ! empty($reply_token)) {
		// Bot作成
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
		$bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channelSecret));

		$multimessage = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
		foreach ($message as $message_item) {
			$multimessage->add($message_item);
		}
		// 応答メッセージ送信
		$resp = $bot->replyMessage($reply_token, $multimessage);
		// 応答メッセージをロギング
		MessageLogger::writeOutboundMessageLog(
			$multimessage,
			'reply',
			isset($responseByAi) && $responseByAi === true ? 'bot' : 'system',
			isset($event->{'source'}->{'userId'}) ? $event->{'source'}->{'userId'} : '',
			$secret_prefix,
			$resp->isSucceeded() ? 'sent' : 'failed',
			$resp->getJSONDecodedBody(),
			$event->{'webhookEventId'} ?? null
		);
		LoggingAPIResponse($resp, $message);
	}

	if (isset($responseByAi) && $responseByAi === true) {
		// $botlog->writeAiResponse($gptResponse['rowResponse']['choices'][0]['message']['content']);
		$responseByAi = null;
	}
}
exit;


function LoggingAPIResponse($response, $message) {
	$isSucced      = $response->isSucceeded();
	$response_body = $response->getJSONDecodedBody();
	if (! $isSucced) {
		// liceconnectError::error_logging(['message' => $message, 'response' => $response_body]);
		error_log(
			print_r(
				array(
					'message'  => $message,
					'response' => $response_body,
				),
				true
			)
		);
	} else {
		// error_log( print_r( ['response' => $response_body], true ) );
	}
}
