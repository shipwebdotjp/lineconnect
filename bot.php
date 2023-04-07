<?php
/* LINE Bot 
   Copyright 2020 shipweb
*/

require_once('vendor/autoload.php');	//LINE BOT SDKを読み込み
require_once ('../../../wp-load.php');	//WordPressの基本機能を読み込み
require_once ('lineconnect.php');		//LINE Connectを読み込み
require_once ('include/message.php');		//メッセージ関連を読み込み

//JSONリクエストボディを取得
$json_string = file_get_contents('php://input');

//検証結果
$valid_signature = false;

//チャンネルごとに署名を検証
foreach(lineconnect::get_all_channels() as $channel_id => $channel){
	//チャネルアクセストークン（長期）
	//チャネルシークレット
	$access_token = $channel['channel-access-token'];
	$channelSecret = $channel['channel-secret'];

	//LINE ID KEY
	$secret_prefix = substr($channelSecret,0,4);

	//署名を検証するためにチャネルシークレットを秘密鍵として、HMAC-SHA256アルゴリズムを使用してリクエストボディのダイジェスト値を取得
	$hash = hash_hmac('sha256', $json_string, $channelSecret, true);
	//ダイジェスト値をBase64エンコード
	$signature = base64_encode($hash);
	//HTTP HeaderからX-Line-Signatureを取得
	$XLineSignature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

	//署名が一致する場合
	if($signature == $XLineSignature){
		$valid_signature = true;
		break;
	}
}

//署名がどのチャンネルにも一致しない場合は400を返す
if(!$valid_signature){
	http_response_code( 400 );
	print "Bad signature";
	exit;
}else{
	//とりあえずステータスコード200を返す
	http_response_code( 200 );
	ob_start(null, 0, PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE); // 出力をバッファっていう感じで制御できるように、好きなタイミングで吐けるようにオプションをつけておく
	header( 'Content-length: ' . ob_get_length() );
	header("Connection: close");
	while (ob_get_level() > 0) {
		ob_end_flush();
	}
	flush(); 
}

//JSONリクエストボディをデコード
$json_obj = json_decode($json_string);

foreach($json_obj->{'events'} as $event){

	//リプレイトークンを取得
	$reply_token = $event->{'replyToken'};

	//イベントタイプを取得
	$type = $event->{'type'};

	if($type === 'message') {    // メッセージ受け取り時
		
		//メッセージオブジェクトのタイプ
		$msg_obj = $event->{'message'}->{'type'};

		if($msg_obj === 'text') {
			// テキストメッセージを受け取った時
			$msg_text = $event->{'message'}->{'text'};
			// テキストに 連携開始／解除キーワード が含まれていた場合
			if(strpos($msg_text,lineconnect::get_option('link_start_keyword')) !== False) {
				$userId = $event->{'source'}->{'userId'};

				//メタ情報からLINEユーザーIDでユーザー検索
				$user = lineconnect::get_wpuser_from_line_id($secret_prefix, $userId);
				if($user){ //ユーザーが見つかればすでに連携されているということ
					$user_id = $user->ID; //IDを取得
					
					//連携解除メッセージ作成
					$message = lineconnectMessage::createFlexMessage([
						"title"=>lineconnect::get_option('unlink_start_title'),
						"body"=>lineconnect::get_option('unlink_start_body'),
						"type"=>"postback",
						"label"=>lineconnect::get_option('unlink_start_button'),
						"link"=>'action=unlink'
					]);
				}else{
					//連携開始メッセージ作成
					$message = getLinkStartMessage($userId);
				}
			} else {
				$message =  null;
			}
		}
	} else if($type === 'accountLink') {
		//アカウントリンク時
		$link_obj = $event->{'link'};
		//アカウントリンク成功時
		if($link_obj->{'result'} === "ok"){

			//nonceを取得
			$nonce = $link_obj->{'nonce'};
			//nonceから対応するユーザーIDを取得
			$user_id = get_option("lineconnect_nonce".$nonce);
			
			//nonceに対応するユーザーIDがあれば
			if($user_id){
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
				delete_option("lineconnect_nonce".$nonce);

				//Wordpressユーザーのメタ情報にLINEユーザーIDを追加
				$line_user_data = get_user_meta($user_id, lineconnect::META_KEY__LINE, true);
				if(empty($line_user_data)){
					$line_user_data = array();
				}
				$line_user_data[$secret_prefix] = array(
					'id' => $userId,
					'displayName' => $profile['displayName'],
					'pictureUrl' => $profile['pictureUrl'],
				);
				update_user_meta( $user_id, lineconnect::META_KEY__LINE, $line_user_data);

				//リッチメニューをセット
				do_action('line_link_richmenu', $user_id);

				//連携完了のテキストメッセージ作成
				$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(lineconnect::get_option('link_finish_body'));
			}else{
				//連携失敗のテキストメッセージ作成
				$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(lineconnect::get_option('link_failed_body'));
			}
		}
	} else if($type === 'postback') {
		// ポストバック受け取り時

		// 送られたデータ
		$postback = $event->{'postback'}->{'data'};

		if($postback === 'action=unlink') {
			// 解除選択時
			$userId = $event->{'source'}->{'userId'};
			$mes = unAccountLink($userId);

			//連携解除完了のテキストメッセージ作成
			$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($mes);
		}elseif($postback === 'action=link') {
			// 連携選択時
			$userId = $event->{'source'}->{'userId'};
			$message = getLinkStartMessage($userId);
		}

	}else if ($type == 'follow') {
		//友達登録時　アカウントリンクイベントを作成
		$userId = $event->{'source'}->{'userId'};
		$message = getLinkStartMessage($userId);
	} else if ($type == 'unfollow') {
		//友達登録解除（ブロック時）リストから消去
		$userId = $event->{'source'}->{'userId'};
		$mes = unAccountLink($userId);
	}

	if(!isset($message) && lineconnect::get_option('enableChatbot') == 'on'){
		//AIで応答する
		$AiMessage = getResponseByChatGPT($event->{'source'}->{'userId'}, $secret_prefix, $event->{'message'}->{'text'});
		// error_log(print_r($AiMessage,true));
		if(!isset($AiMessage['error'])){
			$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($AiMessage['choices'][0]['message']['content']);
			$responseByAi = true;
		}else{
			$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($AiMessage['error']['type'].": ".$AiMessage['error']['message']);
		}

	}

	//応答メッセージがあれば送信する
	if($message != null){
		//Bot作成
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);
		
		//応答メッセージ送信
		$resp = $bot->replyMessage($reply_token, $message);
	}

	//ログ書き込み
	writeChatLog($event);

	//応答メッセージをロギング
	if(isset($responseByAi)){
		writeAiResponse($event, $AiMessage['choices'][0]['message']['content']);
		$responseByAi = null;
	}
}
exit;

//アカウントリンク用のメッセージ作成
function getLinkStartMessage($userId){
	global $access_token,$channelSecret;

	//Bot作成
    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
    $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

	//ユーザーのLinkToken作成
    $response = $bot->createLinkToken($userId);
    //レスポンスをJSONデコード
    $res_json = $response->getJSONDecodedBody();
	//レスポンスからlinkToken取得
    $linkToken=$res_json['linkToken'];

    //WordpressのサイトURLを取得
	$accountlink_url = plugins_url('accountlink.php', __FILE__);
	$redirect_to = urlencode($accountlink_url.'?linkToken='.$linkToken);
	//Wordpressにログインさせたあと、Nonceを作成してLINEへ送信するページへのリダイレクトをするURLを作成
	$gotologin_url = plugins_url('gotologin.php', __FILE__);
    $url = $gotologin_url.'?redirect_to='. $redirect_to;

	//連携開始メッセージ作成
	return lineconnectMessage::createFlexMessage([
		"title"=>lineconnect::get_option('link_start_title'),
		"body"=>lineconnect::get_option('link_start_body'),
		"type"=>"uri",
		"label"=>lineconnect::get_option('link_start_button'),
		"link"=>$url
	]);
}

//アカウント連携解除
function unAccountLink($userId){
	global $secret_prefix;
	//メタ情報からLINEユーザーIDでユーザー検索
    $user = lineconnect::get_wpuser_from_line_id($secret_prefix, $userId);
	//すでに連携されているユーザーが見つかれば
    if($user){ //ユーザーが見つかればすでに連携されているということ
        $user_id = $user->ID; //IDを取得
        
		//リッチメニューを解除
		do_action('line_unlink_richmenu', $user_id, $secret_prefix);

		$user_meta_line = $user->get(lineconnect::META_KEY__LINE);
		if($user_meta_line && $user_meta_line[$secret_prefix]){
			unset($user_meta_line[$secret_prefix]);
			if(empty($user_meta_line)){
				//ほかに連携しているチャネルがなければメタデータ削除
				if (delete_user_meta( $user_id, lineconnect::META_KEY__LINE)){
					$mes = lineconnect::get_option('unlink_finish_body');
				}else{
					$mes = lineconnect::get_option('unlink_failed_body');
				}
			}else{
				//ほかに連携しているチャネルがあれば残りのチャネルが入ったメタデータを更新
				update_user_meta( $user_id, lineconnect::META_KEY__LINE, $user_meta_line);
				$mes = lineconnect::get_option('unlink_finish_body');
			}
		}else{
			$mes = lineconnect::get_option('unlink_failed_body');
		}
	}else{
		$mes = lineconnect::get_option('unlink_failed_body');
	}
	return $mes;
}

//チャットログ書き込み
function writeChatLog($event){
	global $wpdb,$secret_prefix,$channelSecret;;
	$table = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS;

	$event_type = $source_type = $message_type = 0;
	$user_id = "";
	$message = null;

	$event_type = array_search($event->{'type'}, lineconnect::WH_EVENT_TYPE) ?: 0;
	if(isset($event->{'source'})){
		$source_type = array_search($event->{'source'}->{'type'}, lineconnect::WH_SOURCE_TYPE) ?: 0;
		if(isset($event->{'source'}->{'userId'})){
			$user_id = $event->{'source'}->{'userId'};
		}else{
			$user_id = "";
		}
	}else{
		$source_type = 0;
		$user_id = "";
	}

	if($event_type == 1){
		$message_type = array_search($event->{'message'}->{'type'}, lineconnect::WH_MESSAGE_TYPE) ?: 0;
		$message = json_encode($event->{'message'});
		/*
		if($message_type == 1){	//text
			$message = $event->{'message'}->{'text'};
		}elseif($message_type == 2){ //image
			if($event->{'message'}->{'contentProvider'}->{'type'} == 'line'){
				$message = getMessageContent($event->{'message'}->{'id'});
			}else{
				$message = $event->{'contentProvider'}->{'originalContentUrl'};
			}
			
		}
		*/
	}
	$floatSec = $event->{'timestamp'}/1000.0;
	$dateTime = DateTime::createFromFormat("U\.u", sprintf('%1.6F',$floatSec));
	$dateTime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
	$timestamp = $dateTime->format('Y-m-d H:i:s.u');

	$data = [
		'event_type' => $event_type,
		'source_type' => $source_type,
		'user_id' => $user_id,
		'bot_id' => $secret_prefix,
		'message_type' => $message_type,
		'message' => $message,
		'timestamp' => $timestamp,
	];
	$format = [
		'%d', //event_type
		'%d', //source_type
		'%s', //user_id
		'%s', //bot_id
		'%d', //message_type
		'%s', //message
		'%s', //timestamp
	];

	$wpdb->insert( $table, $data, $format ); 
}

//メッセージコンテント取得
function getMessageContent($messageId){
	global $access_token,$channelSecret;

	//Bot作成
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

	//コンテンツ取得
	$response = $bot->getMessageContent($messageId);
	if($response->getHTTPStatus() == 200){
		
	}
}

//ChatGPTで応答作成
function getResponseByChatGPT($user_id, $bot_id, $prompt){
	global $wpdb,$secret_prefix;

	$apiKey = lineconnect::get_option('openai_secret');
	$url = 'https://api.openai.com/v1/chat/completions';

	$headers = array(
		"Authorization: Bearer {$apiKey}",
		"Content-Type: application/json"
	);

	// Define messages
	$messages = array();

	if(lineconnect::get_option('openai_system')){
		$system_message = [
			"role" => "system",
			"content" => lineconnect::get_option('openai_system')
		];
	
		$messages[] = $system_message;
	}



	//過去の文脈を取得
	if(isset($user_id)){
		$table_name = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS; 
		$context_num = intval(lineconnect::get_option('openai_context') * 2);

		$limit_normal = intval(lineconnect::get_option('openai_limit_normal'));
		$limit_linked = intval(lineconnect::get_option('openai_limit_linked'));
		$overlimit = false;
		if($limit_normal != -1 || $limit_linked != -1){

			$convasation_count = $wpdb->get_var( 
				$wpdb->prepare( 
				"
				SELECT COUNT(id) 
				FROM {$table_name}
				WHERE event_type = 1 AND source_type = 11 AND user_id = %s AND bot_id = %s AND DATE(timestamp) = CURDATE()
				",
				array(
					$user_id,
					$bot_id,
				)
				)
			);

			//メタ情報からLINEユーザーIDでユーザー検索
			$user = lineconnect::get_wpuser_from_line_id($secret_prefix, $user_id);
			if($user){ //ユーザーが見つかればすでに連携されている
				$limit_count = $limit_linked;
			}else{
				$limit_count = $limit_normal;
			}
			if($limit_count != -1 && $convasation_count >= $limit_count){
				return  ['error' => [
					'type' => 'エラー',
					'message' => str_replace('%limit%', $limit_count, lineconnect::get_option('openai_limit_message'))
				]];
			}
		}

		$convasations = $wpdb->get_results( 
			$wpdb->prepare( 
			"
			SELECT event_type,source_type,message_type,message 
			FROM {$table_name}
			WHERE event_type = 1 AND user_id = %s AND bot_id = %s
			ORDER BY id desc
			LIMIT 0, {$context_num}
			",
			array(
				$user_id,
				$bot_id,
			)
			)
		);

		foreach(array_reverse($convasations) as $convasation){
			$role = $convasation->source_type == 1 ? "user" : "assistant";
			$message_object = json_decode($convasation->message,false);
			if(json_last_error() == JSON_ERROR_NONE){
				if($convasation->message_type == 1 && isset($message_object->text)){
					$messages[] = [
						"role" => $role,
						"content" => $message_object->text
					];
				}
			}
		}
	}

	//今回の質問
	$messages[] = [
		"role" => "user",
		"content" => $prompt
	];

	// Define data
	$data = array();
	$data["model"] = lineconnect::get_option('openai_model');
	$data["messages"] = $messages;
	$data["max_tokens"] = intval(lineconnect::get_option('openai_max_tokens'));
	$data["temperature"] =  floatval(lineconnect::get_option('openai_temperature'));
	$data["user"] = $user_id;

	// error_log(print_r($messages, true));

	// init curl
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($curl);
	if (curl_errno($curl)) {
		$responce = ['error' => curl_error($curl)];
	} else {
		$responce = json_decode($result, true);

	}
	curl_close($curl);
	return $responce;

}

//AIからの応答をロギング
function writeAiResponse($event, $responseMessage){
	global $wpdb,$secret_prefix,$channelSecret;;
	$table = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS;

	$event_type = $source_type = $message_type = 0;
	$user_id = "";
	$message = null;

	$event_type = array_search($event->{'type'}, lineconnect::WH_EVENT_TYPE) ?: 0;
	$source_type = array_search('bot', lineconnect::WH_SOURCE_TYPE) ?: 0;
	if(isset($event->{'source'})){
		//$source_type = array_search($event->{'source'}->{'type'}, lineconnect::WH_SOURCE_TYPE) ?: 0;
		if(isset($event->{'source'}->{'userId'})){
			$user_id = $event->{'source'}->{'userId'};
		}else{
			$user_id = "";
		}
	}else{
		$user_id = "";
	}

	if($event_type == 1){
		$message_type = 1;
		$message = json_encode(["type" => "text", "text" => $responseMessage]);
	}
	$floatSec = microtime(true);
	$dateTime = DateTime::createFromFormat("U\.u", sprintf('%1.6F',$floatSec));
	$dateTime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
	$timestamp = $dateTime->format('Y-m-d H:i:s.u');

	$data = [
		'event_type' => $event_type,
		'source_type' => $source_type,
		'user_id' => $user_id,
		'bot_id' => $secret_prefix,
		'message_type' => $message_type,
		'message' => $message,
		'timestamp' => $timestamp,
	];
	$format = [
		'%d', //event_type
		'%d', //source_type
		'%s', //user_id
		'%s', //bot_id
		'%d', //message_type
		'%s', //message
		'%s', //timestamp
	];

	$wpdb->insert( $table, $data, $format ); 
}