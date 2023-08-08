<?php
/* LINE Bot 
   Copyright 2020 shipweb
*/

require_once('vendor/autoload.php'); // LINE BOT SDKを読み込み
require_once('../../../wp-load.php'); // WordPressの基本機能を読み込み
require_once('lineconnect.php'); // LINE Connectを読み込み
// require_once('include/message.php'); // メッセージ関連を読み込み

//JSONリクエストボディを取得
$json_string = file_get_contents('php://input');

//検証結果
$valid_signature = false;

//チャンネルごとに署名を検証
foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
	//チャネルアクセストークン（長期）
	//チャネルシークレット
	$access_token = $channel['channel-access-token'];
	$channelSecret = $channel['channel-secret'];

	//LINE ID KEY
	$secret_prefix = substr($channelSecret, 0, 4);

	//署名を検証するためにチャネルシークレットを秘密鍵として、HMAC-SHA256アルゴリズムを使用してリクエストボディのダイジェスト値を取得
	$hash = hash_hmac('sha256', $json_string, $channelSecret, true);
	//ダイジェスト値をBase64エンコード
	$signature = base64_encode($hash);
	//HTTP HeaderからX-Line-Signatureを取得
	$XLineSignature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

	//署名が一致する場合
	if ($signature == $XLineSignature) {
		$valid_signature = true;
		break;
	}
}

//署名がどのチャンネルにも一致しない場合は400を返す
if (!$valid_signature) {
	http_response_code(400);
	print "Bad signature";
	exit;
} else {
	//とりあえずステータスコード200を返す
	http_response_code(200);
	ob_start(null, 0, PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE); // 出力をバッファっていう感じで制御できるように、好きなタイミングで吐けるようにオプションをつけておく
	header('Content-length: ' . ob_get_length());
	header("Connection: close");
	while (ob_get_level() > 0) {
		ob_end_flush();
	}
	flush();
}

//JSONリクエストボディをデコード
$json_obj = json_decode($json_string);

foreach ($json_obj->{'events'} as $event) {
	$message = null;
	//ログ書き込み
	$botlog = new lineconnectBotLog($event);
	$isEventDuplicationOrInsertedId = $botlog->writeChatLog();
	if ($isEventDuplicationOrInsertedId === true) {
		//イベントがすでに記録されていればスキップ
		continue;
	}

	//リプレイトークンを取得
	$reply_token = $event->{'replyToken'};

	//イベントタイプを取得
	$type = $event->{'type'};

	if ($type === 'message') {    // メッセージ受け取り時

		//メッセージオブジェクトのタイプ
		$msg_obj = $event->{'message'}->{'type'};

		if ($msg_obj === 'text') {
			// テキストメッセージを受け取った時
			$msg_text = $event->{'message'}->{'text'};
			// テキストに 連携開始／解除キーワード が含まれていた場合
			if (strpos($msg_text, lineconnect::get_option('link_start_keyword')) !== False) {
				$userId = $event->{'source'}->{'userId'};

				//メタ情報からLINEユーザーIDでユーザー検索
				$user = lineconnect::get_wpuser_from_line_id($secret_prefix, $userId);
				if ($user) { //ユーザーが見つかればすでに連携されているということ
					$user_id = $user->ID; //IDを取得

					//連携解除メッセージ作成
					$message = lineconnectMessage::createFlexMessage([
						"title" => lineconnect::get_option('unlink_start_title'),
						"body" => lineconnect::get_option('unlink_start_body'),
						"type" => "postback",
						"label" => lineconnect::get_option('unlink_start_button'),
						"link" => 'action=unlink'
					]);
				} else {
					//連携開始メッセージ作成
					$message = getLinkStartMessage($userId);
				}
			} else {
				$message =  null;
			}
		}
	} else if ($type === 'accountLink') {
		//アカウントリンク時
		$link_obj = $event->{'link'};
		//アカウントリンク成功時
		if ($link_obj->{'result'} === "ok") {

			//nonceを取得
			$nonce = $link_obj->{'nonce'};
			//nonceから対応するユーザーIDを取得
			$user_id = get_option("lineconnect_nonce" . $nonce);

			//nonceに対応するユーザーIDがあれば
			if ($user_id) {
				//LINE ユーザーID
				$userId = $event->{'source'}->{'userId'};

				//Bot作成
				$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
				$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

				//ユーザーのプロフィール取得
				$response = $bot->getProfile($userId);
				//レスポンスをJSONデコード
				$profile = $response->getJSONDecodedBody();

				//nonceを削除
				delete_option("lineconnect_nonce" . $nonce);

				//Wordpressユーザーのメタ情報にLINEユーザーIDを追加
				$line_user_data = get_user_meta($user_id, lineconnect::META_KEY__LINE, true);
				if (empty($line_user_data)) {
					$line_user_data = array();
				}
				$line_user_data[$secret_prefix] = array(
					'id' => $userId,
					'displayName' => $profile['displayName'],
					'pictureUrl' => $profile['pictureUrl'],
				);
				update_user_meta($user_id, lineconnect::META_KEY__LINE, $line_user_data);

				//WP Line Loginと連携
				do_action('line_login_update_user_meta', $user_id, $line_user_data[$secret_prefix], $secret_prefix);

				//リッチメニューをセット
				do_action('line_link_richmenu', $user_id);

				//連携完了のテキストメッセージ作成
				$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(lineconnect::get_option('link_finish_body'));
			} else {
				//連携失敗のテキストメッセージ作成
				$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(lineconnect::get_option('link_failed_body'));
			}
		}
	} else if ($type === 'postback') {
		// ポストバック受け取り時

		// 送られたデータ
		$postback = $event->{'postback'}->{'data'};

		if ($postback === 'action=unlink') {
			// 解除選択時
			$userId = $event->{'source'}->{'userId'};
			$mes = unAccountLink($userId);

			//連携解除完了のテキストメッセージ作成
			$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($mes);
		} elseif ($postback === 'action=link') {
			// 連携選択時
			$userId = $event->{'source'}->{'userId'};
			$message = getLinkStartMessage($userId);
		}
	} else if ($type == 'follow') {
		//友達登録時　アカウントリンクイベントを作成
		$userId = $event->{'source'}->{'userId'};
		$message = getLinkStartMessage($userId);
	} else if ($type == 'unfollow') {
		//友達登録解除（ブロック時）リストから消去
		$userId = $event->{'source'}->{'userId'};
		$mes = unAccountLink($userId);
	}

	//if message type is image,video,audio,file and contentProvider.type is line
	if ($type === 'message' && in_array($event->{'message'}->{'type'}, ['image', 'video', 'audio', 'file']) && $event->{'message'}->{'contentProvider'}->{'type'} === 'line') {
		//save content 
		$saved_content_file_name = getMessageContent($event->{'message'}->{'id'}, isset($event->{'source'}->{'userId'}) ? $event->{'source'}->{'userId'} : "_none");

		//update filepath to log table
		$result = update_message_filepath($isEventDuplicationOrInsertedId, $saved_content_file_name);
	}

	if (!isset($message) && $type === 'message' && $event->{'message'}->{'type'}  === 'text' && $event->{'message'}->{'text'} != null && lineconnect::get_option('enableChatbot') == 'on') {
		//AIで応答する
		$openAi = new lineconnectOpenAi();
		$gptResponse = $openAi->getResponseByChatGPT($event->{'source'}->{'userId'}, $secret_prefix, [
			"role" => "user",
			"content" => $event->{'message'}->{'text'}]);
		$message = $gptResponse['message'];
		$responseByAi = $gptResponse['responseByAi'];
	}

	//応答メッセージがあれば送信する
	if (isset($message)) {
		//Bot作成
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

		//応答メッセージ送信
		$resp = $bot->replyMessage($reply_token, $message);
	}

	//応答メッセージをロギング
	if (isset($responseByAi) && $responseByAi === true) {
		$botlog->writeAiResponse($gptResponse['rowResponse']['choices'][0]['message']['content']);
		$responseByAi = null;
	}
}
exit;

//アカウントリンク用のメッセージ作成
function getLinkStartMessage($userId) {
	global $access_token, $channelSecret;

	//Bot作成
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

	//ユーザーのLinkToken作成
	$response = $bot->createLinkToken($userId);
	//レスポンスをJSONデコード
	$res_json = $response->getJSONDecodedBody();
	//レスポンスからlinkToken取得
	$linkToken = $res_json['linkToken'];

	//WordpressのサイトURLを取得
	$accountlink_url = plugins_url('accountlink.php', __FILE__);
	$redirect_to = urlencode($accountlink_url . '?linkToken=' . $linkToken);
	//Wordpressにログインさせたあと、Nonceを作成してLINEへ送信するページへのリダイレクトをするURLを作成
	$gotologin_url = plugins_url('gotologin.php', __FILE__);
	$url = $gotologin_url . '?redirect_to=' . $redirect_to;

	//連携開始メッセージ作成
	return lineconnectMessage::createFlexMessage([
		"title" => lineconnect::get_option('link_start_title'),
		"body" => lineconnect::get_option('link_start_body'),
		"type" => "uri",
		"label" => lineconnect::get_option('link_start_button'),
		"link" => $url
	]);
}

//アカウント連携解除
function unAccountLink($userId) {
	global $secret_prefix;
	//メタ情報からLINEユーザーIDでユーザー検索
	$user = lineconnect::get_wpuser_from_line_id($secret_prefix, $userId);
	//すでに連携されているユーザーが見つかれば
	if ($user) { //ユーザーが見つかればすでに連携されているということ
		$user_id = $user->ID; //IDを取得

		//リッチメニューを解除
		do_action('line_unlink_richmenu', $user_id, $secret_prefix);

		$user_meta_line = $user->get(lineconnect::META_KEY__LINE);
		if ($user_meta_line && $user_meta_line[$secret_prefix]) {
			unset($user_meta_line[$secret_prefix]);
			if (empty($user_meta_line)) {
				//ほかに連携しているチャネルがなければメタデータ削除
				if (delete_user_meta($user_id, lineconnect::META_KEY__LINE)) {
					$mes = lineconnect::get_option('unlink_finish_body');
				} else {
					$mes = lineconnect::get_option('unlink_failed_body');
				}
			} else {
				//ほかに連携しているチャネルがあれば残りのチャネルが入ったメタデータを更新
				update_user_meta($user_id, lineconnect::META_KEY__LINE, $user_meta_line);
				$mes = lineconnect::get_option('unlink_finish_body');
			}
			//WP Line Loginと連携解除
			do_action('line_login_delete_user_meta', $user_id, $secret_prefix);
		} else {
			$mes = lineconnect::get_option('unlink_failed_body');
		}
	} else {
		$mes = lineconnect::get_option('unlink_failed_body');
	}
	return $mes;
}


//メッセージコンテント取得
function getMessageContent($messageId, $userId) {
	global $access_token, $channelSecret;

	//Bot作成
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

	//コンテンツ取得
	$response = $bot->getMessageContent($messageId);
	if ($response->getHTTPStatus() == 200) {
		//レスポンスからバイナリデータを取得
		$content = $response->getRawBody();
		//レスポンスのContent-Typeヘッダーからバイナリデータのファイル形式を取得
		$contentType = $response->getHeader('Content-Type');

		//取得したファイル形式から適切なファイル拡張子を選択
		$file_extention = get_file_extention($contentType);

		//make file name from message id and file extention
		$file_name = $messageId . '.' . $file_extention;
		//set user directory
		$user_dir = substr($userId, 1, 4);
		//make directory
		$target_dir_path = make_lineconnect_dir($user_dir);
		if ($target_dir_path) {
			//make file path
			$file_path = $target_dir_path . '/' . $file_name;
			//write file
			file_put_contents($file_path, $content);
			//return file path
			return $user_dir . '/' . $file_name;
		}
	}
	return false;
}

//make 'lineconnect' directory in wp-content/uploads
function make_lineconnect_dir($user_dir) {
	$root_dir_path = WP_CONTENT_DIR . '/uploads/lineconnect';
	//check if root dir exists
	if (!file_exists($root_dir_path)) {
		//make root dir
		if (mkdir($root_dir_path, 0777, true)) {
			//put .htaccess file to root dir
			$htaccess_file_path = $root_dir_path . '/.htaccess';
			$htaccess_file_content = 'deny from all';
			file_put_contents($htaccess_file_path, $htaccess_file_content);
		}
	}
	$target_dir_path = $root_dir_path . '/' . $user_dir;
	//check if target dir exists
	if (!file_exists($target_dir_path)) {
		//make target dir
		if (mkdir($target_dir_path, 0777, true)) {
			//put .htaccess file to target dir
			$htaccess_file_path = $target_dir_path . '/.htaccess';
			$htaccess_file_content = 'deny from all';
			file_put_contents($htaccess_file_path, $htaccess_file_content);
			return $target_dir_path;
		} else {
			return false;
		}
	}
	return $target_dir_path;
}

//MIME type to file Extension
function get_file_extention($mime_type) {
	return lineconnectConst::MIME_MAP[$mime_type] ?? 'bin';
}

//update message
function update_message_filepath($logId, $file_path) {
	global $wpdb;
	$table_name = $wpdb->prefix . lineconnectConst::TABLE_BOT_LOGS;
	//get row from log table
	$row = $wpdb->get_row("SELECT * FROM {$table_name} WHERE id = {$logId}");
	if ($row) {
		//message column is JSON string, so decode to object
		$message = json_decode($row->message);
		// set file path
		$message->file_path = $file_path;
		// update message column
		return $wpdb->update($table_name, ['message' => json_encode($message)], ['id' => $logId]);
	}
	return false;
}
