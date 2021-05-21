<?php
/* LINE Bot 
   Copyright 2020 shipweb
*/

//アカウント連携／解除を開始するためのキーワード
const ACCOUNT_LINK_START_KEYWORD = 'アカウント連携';
//アカウント連携開始ダイアログのタイトル
const ACCOUNT_LINK_START_TITLE = 'アカウント連携'; //最大文字数：40
//アカウント連携開始ダイアログのメッセージ本文
const ACCOUNT_LINK_START_BODY = '連携を開始します。リンク先でログインしてください。'; //最大文字数：60
//アカウント連携開始ダイアログの開始ボタンラベル
const ACCOUNT_LINK_START_BUTTON = '連携開始'; //最大文字数：20
//アカウント連携完了時のメッセージ
const ACCOUNT_LINK_FINISH_BODY = 'アカウント連携が完了しました。'; //最大文字数：5000
//アカウント連携失敗時のメッセージ
const ACCOUNT_LINK_FAILED_BODY = 'アカウント連携に失敗しました。'; //最大文字数：5000
//アカウント連携解除開始時のタイトル
const ACCOUNT_UNLINK_START_TITLE = 'アカウント連携解除'; //最大文字数：40
//アカウント連携解除開始時のメッセージ本文
const ACCOUNT_UNLINK_START_BODY = 'すでにアカウント連携されています。連携を解除しますか？'; //最大文字数：60
//アカウント連携解除開始時の開始ボタンラベル
const ACCOUNT_UNLINK_START_BUTTON = '連携解除'; //最大文字数：20
//アカウント連携解除完了時のメッセージ
const ACCOUNT_UNLINK_FINISH_BODY = 'アカウント連係を解除しました。'; //最大文字数：5000
//アカウント連携解除失敗時のメッセージ
const ACCOUNT_UNLINK_FAILED_BODY = 'アカウント連携解除に失敗しました。'; //最大文字数：5000

//LINE BOT SDKを読み込み
require_once('vendor/autoload.php');
//WordPressの基本機能を読み込み
require_once ('../../../wp-load.php');
//設定ファイルを読み込み
require_once ('config.php');

//LINE Connectを読み込み
require_once ('lineconnect.php');

//チャネルアクセストークン（長期）
$access_token = lineconnect::decrypt(get_option(lineconnect::OPTION_KEY__CHANNEL_ACCESS_TOKEN), lineconnect::ENCRYPT_PASSWORD);
//チャネルシークレット
$channelSecret = lineconnect::decrypt(get_option(lineconnect::OPTION_KEY__CHANNEL_SECRET), lineconnect::ENCRYPT_PASSWORD);


//JSONリクエストボディを取得
$json_string = file_get_contents('php://input');

//署名を検証するためにチャネルシークレットを秘密鍵として、HMAC-SHA256アルゴリズムを使用してリクエストボディのダイジェスト値を取得
$hash = hash_hmac('sha256', $json_string, $channelSecret, true);
//ダイジェスト値をBase64エンコード
$signature = base64_encode($hash);
//HTTP HeaderからX-Line-Signatureを取得
$XLineSignature = $_SERVER['HTTP_X_LINE_SIGNATURE'];
//署名が一致しない場合は400を返す
if($signature != $XLineSignature){
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
        if(strpos($msg_text,ACCOUNT_LINK_START_KEYWORD) !== False) {
            $userId = $json_obj->{'events'}[0]->{'source'}->{'userId'};

			//メタ情報からLINEユーザーIDでユーザー検索
			$user_query = new WP_User_Query( array( 'meta_key' => 'line_user_id', 'meta_value' => $userId ) );

            $users = $user_query->get_results();
            if(! empty( $users )){ //ユーザーが見つかればすでに連携されているということ
            	$user =  $users[0]; //ユーザーの一人目
            	$user_id = $user->ID; //IDを取得
				
				/*
            	//連係解除ポストバックを備えたテンプレート作成
            	$postbackTemplateAction = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder(ACCOUNT_UNLINK_START_BUTTON,'action=unlink');
            	//ボタンテンプレート作成
            	$template = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder(ACCOUNT_UNLINK_START_TITLE,ACCOUNT_UNLINK_START_BODY,NULL,[$postbackTemplateAction]);
            	//メッセージテンプレート作成
            	$message =  new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(ACCOUNT_UNLINK_START_TITLE,$template);
            	*/

				$message = createFlexMessageTemplate(["title"=>ACCOUNT_UNLINK_START_TITLE,"body"=>ACCOUNT_UNLINK_START_BODY,"type"=>"postback","label"=>ACCOUNT_UNLINK_START_BUTTON,"link"=>'action=unlink']);
            }else{
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
			update_user_meta( $user_id, 'line_user_id', $userId);
			//Wordpressユーザーのメタ情報にLINE表示名を追加
			update_user_meta( $user_id, 'line_displayname', $profile['displayName']);
			//Wordpressユーザーのメタ情報にLINEアイコンURLを追加
			update_user_meta( $user_id, 'line_picture_url', $profile['pictureUrl']);

			//連携完了のテキストメッセージ作成
		    $message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(ACCOUNT_LINK_FINISH_BODY);
		}else{
			//連携失敗のテキストメッセージ作成
			$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(ACCOUNT_LINK_FAILED_BODY);
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
	global $access_token,$channelSecret,$login_path;

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
    $site_url=get_site_url(null, '/');
	//Wordpressにログインさせたあと、Nonceを作成してLINEへ送信するページへのリダイレクトをするURLを作成
    $url = $site_url.'wp-content/plugins/lineconnect/gotologin.php?redirect_to='.urlencode($site_url.'wp-content/plugins/lineconnect/accountlink.php?linkToken='.$linkToken);
    
	/*
	//アカウント連係ポストバックを備えたテンプレート作成
	$postbackTemplateAction = new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder(ACCOUNT_LINK_START_BUTTON,$url);
	//ボタンテンプレート作成
	$template = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder(ACCOUNT_LINK_START_TITLE,ACCOUNT_LINK_START_BODY,NULL,[$postbackTemplateAction]);
    //メッセージテンプレート作成
    return new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(ACCOUNT_LINK_START_TITLE,$template);
	*/

	return createFlexMessageTemplate(["title"=>ACCOUNT_LINK_START_TITLE,"body"=>ACCOUNT_LINK_START_BODY,"type"=>"uri","label"=>ACCOUNT_LINK_START_BUTTON,"link"=>$url]);
}

//アカウント連携解除
function unAccountLink($userId){
	//メタ情報からLINEユーザーIDでユーザー検索
	$user_query = new WP_User_Query( array( 'meta_key' => 'line_user_id', 'meta_value' => $userId ) );
	//すでに連携されているユーザーが見つかれば
	$users = $user_query->get_results();
    if(! empty( $users )){
        $user = $users[0]; //ユーザーの一人目
        $user_id = $user->ID; //IDを取得
        
        //Wordpressユーザーのメタ情報からLINEユーザーIDを消去
		if (delete_user_meta( $user_id, 'line_user_id')){
			//Wordpressユーザーのメタ情報からLINE表示名を消去
			delete_user_meta( $user_id, 'line_displayname');
			//Wordpressユーザーのメタ情報にLINEアイコンURLを消去
			delete_user_meta( $user_id, 'line_picture_url');
			$mes = ACCOUNT_UNLINK_FINISH_BODY;
		}else{
			$mes = ACCOUNT_UNLINK_FAILED_BODY;
		}
	}else{
		$mes = ACCOUNT_UNLINK_FAILED_BODY;
	}
	return $mes;
}

//Flexメッセージテンプレートを作成
function createFlexMessageTemplate($data){
	$alttext = $data['title'] . "\r\n" . $data['body'];

	$thumbBoxComponent = NULL;

	//タイトルのTextコンポーネント
	$titleTextComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($data['title'],NULL,NULL,NULL,NULL,NULL,TRUE,2,'bold',NULL,NULL);
	
	//ヘッダーブロック
	$titleBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$titleTextComponent],NULL,NULL,'none');
	$titleBoxComponent->setPaddingTop('xl');
	$titleBoxComponent->setPaddingBottom('xs');
	$titleBoxComponent->setPaddingStart('xl');
	$titleBoxComponent->setPaddingEnd('xl');        
	
	//本文のTextコンポーネント
	$bodyTextComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($data['body'],NULL,NULL,NULL,NULL,NULL,TRUE,3,NULL,NULL,NULL);

	//ボディブロック
	$bodyBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$bodyTextComponent],NULL,NULL,'none');
	$bodyBoxComponent->setPaddingBottom('none');
	$bodyBoxComponent->setPaddingTop('xs');
	$bodyBoxComponent->setPaddingStart('xl');
	$bodyBoxComponent->setPaddingEnd('xl');  

	if($data['type']=="uri"){
		//リンクアクションコンポーネント
		$linkActionBuilder = new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder($data['label'],$data['link']);
	}elseif($data['type']=="postback"){
		//ポストバックアクションコンポーネント
		$linkActionBuilder = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($data['label'],$data['link']);
	}
	//リンクのボタンコンポーネント
	$linkButtonComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder($linkActionBuilder,NULL,NULL,NULL,'link',NULL,NULL);
	
	//フッターブロック
	$footerBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$linkButtonComponent],NULL,NULL,'none');
	$footerBoxComponent->setPaddingTop('none');

	//ブロックスタイル
	$blockStyleBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\BlockStyleBuilder("#FFFFFF");

	//バブルスタイル
	$bubbleStyleBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\BubbleStylesBuilder($blockStyleBuilder,$blockStyleBuilder,$blockStyleBuilder,$blockStyleBuilder);

	//バブルコンテナ
	$bubbleContainerBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder(NULL, $thumbBoxComponent, $titleBoxComponent,$bodyBoxComponent,$footerBoxComponent,$bubbleStyleBuilder);

	//Flexメッセージ
	return new \LINE\LINEBot\MessageBuilder\FlexMessageBuilder($alttext, $bubbleContainerBuilder);
}