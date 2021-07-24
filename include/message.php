<?php

class lineconnectMessage{

    //Flexメッセージを作成
    static function createFlexMessage($data, $atts = null){
       
        // LINEBOT SDKの読み込み
        require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');
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
        ));

        $alttext = $data['title'] . "\r\n" . $data['body'];
        if(mb_strlen($alttext) > 400){
            $alttext = mb_substr($alttext, 0, 399)."…";
        }

        //サムネイル画像があれば
        if($data['thumb'] != ""){
            //サムネイル画像のImageコンポーネント
            $thumbImageComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder($data['thumb'],NULL,'none',NULL,NULL,'100%',$atts['aspect_rate'],$atts['aspect_mode']);
            
            //ヒーローブロック
            $thumbBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$thumbImageComponent],NULL,'none','none');
            $thumbBoxComponent->setPaddingAll('none');

        }else{
            $thumbBoxComponent = NULL;
        }

        //タイトルのTextコンポーネント
        $titleTextComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($data['title'],NULL,NULL,NULL,NULL,NULL,TRUE,intval($atts['title_rows']),'bold',$atts['title_color'],NULL);
        
        //ヘッダーブロック
        $titleBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$titleTextComponent],NULL,NULL,'none');
        $titleBoxComponent->setPaddingTop('xl');
        $titleBoxComponent->setPaddingBottom('xs');
        $titleBoxComponent->setPaddingStart('xl');
        $titleBoxComponent->setPaddingEnd('xl');        
        
        //本文のTextコンポーネント
        $bodyTextComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($data['body'],NULL,NULL,NULL,NULL,NULL,TRUE,intval($atts['body_rows']),NULL,$atts['body_color'],NULL);

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
        $linkButtonComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder($linkActionBuilder,NULL,NULL,NULL,'link',$atts['link_color'],NULL);
        
        //フッターブロック
        $footerBoxComponent =  new \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder("vertical",[$linkButtonComponent],NULL,NULL,'none');
        $footerBoxComponent->setPaddingTop('none');

        //ブロックスタイル
        $blockStyleBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\BlockStyleBuilder($atts['background_color']);

        //バブルスタイル
        $bubbleStyleBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\BubbleStylesBuilder($blockStyleBuilder,$blockStyleBuilder,$blockStyleBuilder,$blockStyleBuilder);

        //バブルコンテナ
        $bubbleContainerBuilder =  new \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder(NULL, $thumbBoxComponent, $titleBoxComponent,$bodyBoxComponent,$footerBoxComponent,$bubbleStyleBuilder);

        //Flexメッセージ
        return new \LINE\LINEBot\MessageBuilder\FlexMessageBuilder($alttext, $bubbleContainerBuilder);
    }

    //Textメッセージを作成
    static function createTextMessage($text, $extraTexts = NULL){
        // LINEBOT SDKの読み込み
        require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');
        return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text, $extraTexts);
    }

    //連携済みユーザーへロールを指定して送信($role = slc_linked なら全ての連携済みユーザーへ送信)
    static function sendMessageRole($channel, $role, $message){

        // Lineconnectの読み込み
        require_once(plugin_dir_path(__FILE__).'../lineconnect.php');

        if(!$channel){
            $channel = lineconnect::get_channel(0);
        }

        if(is_string($message)){
            $message = self::createTextMessage($message);
        }  
        
        $secret_prefix = substr( $channel['channel-secret'],0,4);

        //$roleが"slc_linked"の場合は全てのロールユーザーに送信
        if($role == "slc_linked"){
            $role = "";
        }
        //設定されているロールユーザーに送信
        $args = array(
            'meta_query' => array(
                array(
                    'key'     => lineconnect::META_KEY__LINE,
                    'compare' => 'EXISTS'
                )
            ),
            'role' => $role,
            'fields'=>'all_with_meta'
        );
        $line_user_ids=array();	//送信するLINEユーザーIDの配列
        $user_query = new WP_User_Query( $args ); //条件を指定してWordpressからユーザーを検索
        $users = $user_query->get_results(); //クエリ実行
        if(! empty( $users )){	//マッチするユーザーが見つかれば
            //ユーザーのメタデータを取得
            foreach($users as $user){
                $user_meta_line = $user->get(lineconnect::META_KEY__LINE);
                if($user_meta_line && $user_meta_line[$secret_prefix]){
                    if( $user_meta_line[$secret_prefix]['id'] ){
                        $line_user_ids[] = $user_meta_line[$secret_prefix]['id'];
                    }
                }
            }
            return self::sendMulticastMessage($channel, $line_user_ids, $message);

        }else{
            return array('success' => true, 'num' => 0);
            // $error_message = '条件にマッチするユーザーがいませんでした';
        }
    }

    //連携済みユーザーへWPユーザーを指定して送信
    static function sendMessageWpUser($channel, $wp_user_id, $message){

        // Lineconnectの読み込み
        require_once(plugin_dir_path(__FILE__).'../lineconnect.php');

        if(!$channel){
            $channel = lineconnect::get_channel(0);
        }

        if(is_string($message)){
            $message = self::createTextMessage($message);
        }        

        $secret_prefix = substr( $channel['channel-secret'],0,4);
        $user_meta_line = get_user_meta($wp_user_id, lineconnect::META_KEY__LINE, true);
        if($user_meta_line && $user_meta_line[$secret_prefix]){
            if( $user_meta_line[$secret_prefix]['id'] ){
                return self::sendPushMessage($channel,  $user_meta_line[$secret_prefix]['id'], $message);
            }
        }
    }


    //プッシュ（一人のユーザーに送信）
    static function sendPushMessage($channel, $line_user_id, $message){
                       
        // LINEBOT SDKの読み込み
        require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');

        $channel_access_token = $channel['channel-access-token'];
        $channel_secret = $channel['channel-secret'];

        //LINE BOT
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

        //プッシュで送信
        $response = $bot->pushMessage($line_user_id, $message);

        //送信に成功した場合
        if ($response->getHTTPStatus() === 200) {
            return array('success' => true, 'num' => $target_cnt);
        }else{
            return array('success' => false, 'message' => $response->getJSONDecodedBody()['message']);
        }
    }

    //マルチキャスト（複数のユーザーに送信）
    static function sendMulticastMessage($channel, $line_user_ids, $message){
                       
        // LINEBOT SDKの読み込み
        require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');

        $channel_access_token = $channel['channel-access-token'];
        $channel_secret = $channel['channel-secret'];

        //LINE BOT
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

        //最大500人なので、500個ごとに配列を分割して送信
        foreach(array_chunk($line_user_ids, 500) as $line_user_id_chunk){
            //マルチキャストで送信
            $response = $bot->multicast($line_user_id_chunk, $message);
            if ($response->getHTTPStatus() !== 200) {
                return array('success' => false, 'message' => $response->getJSONDecodedBody()['message']);
            }
        }
        //送信に成功した場合
        return array('success' => true, 'num' => count($line_user_ids));
        
    }

    //ブロードキャスト（すべての友達登録されているユーザーに送信）
    static function sendBroadcastMessage($channel, $message){
        // LINEBOT SDKの読み込み
        require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');

        $channel_access_token = $channel['channel-access-token'];
        $channel_secret = $channel['channel-secret'];

        //LINE BOT
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

        $response = $bot->broadcast($message);
        if ($response->getHTTPStatus() === 200) {
            return array('success' => true);
        }else{
            return array('success' => false, 'message' => $response->getJSONDecodedBody()['message']);
        }
    }
}