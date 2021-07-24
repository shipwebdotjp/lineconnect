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
}

//JSONリクエストボディをデコード
$json_obj = json_decode($json_string);

//リプレイトークンを取得
$reply_token = $json_obj->{'events'}[0]->{'replyToken'};

//イベントタイプを取得
$type = $json_obj->{'events'}[0]->{'type'};

if($type === 'message') {    // メッセージ受け取り時
	
	//メッセージオブジェクトのタイプ
	$msg_obj = $json_obj->{'events'}[0]->{'message'}->{'type'};

    if($msg_obj === 'text') {
        // テキストメッセージを受け取った時
        $msg_text = $json_obj->{'events'}[0]->{'message'}->{'text'};
        // テキストに 連携開始／解除キーワード が含まれていた場合
        if(strpos($msg_text,lineconnect::get_option('link_start_keyword')) !== False) {
            $userId = $json_obj->{'events'}[0]->{'source'}->{'userId'};

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
	$link_obj = $json_obj->{'events'}[0]->{'link'};
	//アカウントリンク成功時
	if($link_obj->{'result'} === "ok"){

		//nonceを取得
		$nonce = $link_obj->{'nonce'};
		//nonceから対応するユーザーIDを取得
		$user_id = get_option("lineconnect_nonce".$nonce);
		
		//nonceに対応するユーザーIDがあれば
		if($user_id){
			//LINE ユーザーID
			$userId = $json_obj->{'events'}[0]->{'source'}->{'userId'};
			
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
    $postback = $json_obj->{'events'}[0]->{'postback'}->{'data'};

    if($postback === 'action=unlink') {
        // 解除選択時
        $userId = $json_obj->{'events'}[0]->{'source'}->{'userId'};
		$mes = unAccountLink($userId);

		//連携解除完了のテキストメッセージ作成
       	$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($mes);
    }elseif($postback === 'action=link') {
        // 連携選択時
        $userId = $json_obj->{'events'}[0]->{'source'}->{'userId'};
		$message = getLinkStartMessage($userId);
    }

}else if ($type == 'follow') {
	//友達登録時　アカウントリンクイベントを作成
    $userId = $json_obj->{'events'}[0]->{'source'}->{'userId'};
	$message = getLinkStartMessage($userId);
} else if ($type == 'unfollow') {
	//友達登録解除（ブロック時）リストから消去
	$userId = $json_obj->{'events'}[0]->{'source'}->{'userId'};
	$mes = unAccountLink($userId);
}

//応答メッセージがあれば送信する
if($message != null){
	//Bot作成
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);
	
	//応答メッセージ送信
	$resp = $bot->replyMessage($reply_token, $message);
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