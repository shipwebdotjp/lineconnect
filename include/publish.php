<?php

class lineconnectPublish{
    
    static function add_send_checkbox() {
        // 投稿ページと固定ページ両方でLINE送信チェックボックスを表示
        $screens = lineconnect::get_option('send_post_types');
        foreach ( $screens as $screen ) {
            add_meta_box(
                // チェックボックスのID
                lineconnect::PARAMETER__SEND_CHECKBOX,
                // チェックボックスのラベル名
                'LINE Connect',
                // チェックボックスを表示するコールバック関数
                ['lineconnectPublish', 'show_send_checkbox'],
                // 投稿画面に表示
                $screen,
                // 投稿画面の右サイドに表示
                'side',
                // 優先度(default)
                'default'
            );
        }
    }

    /**
     * LINEにメッセージを送信するチェックボックスを表示
     */
    static function show_send_checkbox() {

        // nonceフィールドを生成・取得
        $nonce_field = wp_nonce_field(
            lineconnect::CREDENTIAL_ACTION__POST,
            lineconnect::CREDENTIAL_NAME__POST,
            true,
            false
        );
        echo $nonce_field;
        echo "<div>";
        //チャンネルリスト毎に出力
        foreach(lineconnect::get_all_channels() as $channel_id => $channel){
            
            $htmls = array();
            foreach(lineconnect::CHANNEL_FIELD as $option_key => $option_name){
                $input_filed = "";
                if($option_key == 'role-selectbox'){
                    $role = $channel['role'];
                    
                    $role = esc_html($role);
                    // ロール選択セレクトボックスを出力
                    // Sendboxのパラメータ名
                    $param_role = lineconnect::PARAMETER_PREFIX.$option_key.$channel['prefix'];
                    $input_filed = '<label for="'.$param_role.'">'.$option_name.'</label>'."<select name=".$param_role.">";
                    $all_roles = array("slc_all"=>"すべての友達", "slc_linked"=>"連携済みの友達");
                    foreach (wp_roles()->get_names() as $role_name) {
                        $all_roles[esc_attr($role_name)] = translate_user_role($role_name);
                    }
                    $input_filed .= lineconnect::makeHtmlSelectOptions($all_roles, $role);
                    $input_filed .= "</select>";
                    
                }elseif($option_key == 'send-checkbox'){
                    if (get_post_status(get_the_ID()) === 'publish') {
                        $checked = '';
                    }else{
                        $checked = 'checked';
                    }
                    $param_select = lineconnect::PARAMETER_PREFIX.$option_key.$channel['prefix'];
                    $input_filed = '<input type="checkbox" name="' . $param_select . '" value="ON" id="id_' . $param_select . '" '.$checked.'>'.
                    '<label for="id_' . $param_select . '">'.$option_name.'</label><br>';
                }
                $htmls[$option_key] = $input_filed;
            }
            
            echo "<div>";
            echo '<h3>'.$channel['name'].'</h3>';
            echo '<div>'.$htmls['send-checkbox'].'</div>';
            echo '<div>'.$htmls['role-selectbox'].'</div>';
            echo '</div>';
        }
        echo "</div>";
    }

    /**
     * LINEメッセージを送信
     */
    static function send_to_line($post_ID, $post){
        // ログインしていない場合は無視
        if (!is_user_logged_in()) return;
        // 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
        if (!is_super_admin() && !current_user_can('administrator') && !current_user_can('editor') && !current_user_can('author')) return;
        // nonceで設定したcredentialをPOST受信していない場合は無視
        if (!isset($_POST[lineconnect::CREDENTIAL_NAME__POST]) || !$_POST[lineconnect::CREDENTIAL_NAME__POST]) return;
        // nonceで設定したcredentialのチェック結果に問題がある場合
        if (!check_admin_referer(lineconnect::CREDENTIAL_ACTION__POST, lineconnect::CREDENTIAL_NAME__POST)) return;
        $ary_success_message = array();
        $ary_error_message = array();
        //チャンネルリスト毎に送信
        foreach(lineconnect::get_all_channels() as $channel_id => $channel){
            $error_message = $success_message = "";

            $channel_access_token = $channel['channel-access-token'];
            $channel_secret = $channel['channel-secret'];

            // RoleをPOSTから、なければOPTIONSテーブルから取得
            $role = $_POST[lineconnect::PARAMETER_PREFIX.'role-selectbox'.$channel['prefix']];
            if(!$role){
                $role =  $channel['role'];
            }

            // ChannelAccessTokenとChannelSecretが設定されており、LINEメッセージ送信チェックボックスにチェックがある場合
            if (strlen($channel_access_token) > 0 && strlen($channel_secret) > 0 && $_POST[lineconnect::PARAMETER_PREFIX.'send-checkbox'.$channel['prefix']] == 'ON') {
                // 投稿のタイトルを取得
                $title = sanitize_text_field($post->post_title);
                
                // 投稿の本文を取得
                $body = preg_replace("/( |　|\n|\r)/", "", strip_tags(sanitize_text_field(strip_shortcodes($post->post_content))));
                
                if(mb_strlen($body) > 500){
                    // 投稿の本文の先頭500文字取得
                    $body = mb_substr($body, 0, 499)."…";
                }
                
                //空BODYでは送れないため、本文がない場合はスペースを送信
                if(mb_strlen($body) == 0){
                    $body = " ";
                }

                // 投稿のURLを取得
                $link = get_permalink($post_ID);

                // 投稿のサムネイルを取得
                $thumb = get_the_post_thumbnail_url($post_ID);
                if(substr($thumb,0,5) != "https"){  //httpsから始まらない場合はサムネなしとする
                    $thumb = "";
                }

                //通知用の本文を作成（400文字に切り詰め）
                $alttext = $title . "\r\n" . $body . "\r\n" . $link;
                if(mb_strlen($alttext) > 400){
                    $alttext = mb_substr($alttext, 0, 399)."…";
                }

                // LINEBOT SDKの読み込み
                // require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');

                // 設定ファイルの読み込み
                require_once(plugin_dir_path(__FILE__).'../config.php');

                //メッセージ関連を読み込み
                require_once (plugin_dir_path(__FILE__).'message.php');

                $link_label = lineconnect::get_option('more_label');
	            $flexMessage = lineconnectMessage::createFlexMessage(
                    ["title"=>$title,"body"=>$body,"thumb"=>$thumb,"type"=>"uri","label"=>$link_label,"link"=>$link]);

                if($role == "slc_all"){
                    //送信するロールがすべてのユーザーならブロードキャスト
                    $response = lineconnectMessage::sendBroadcastMessage($channel, $flexMessage);
                    if($response['success']){
                        $success_message = '全ての友達にLINEを送信しました';
                    }else{
                        $error_message = '全ての友達への送信に失敗しました'.$response['message'];
                    }
                }else{
                    $response = lineconnectMessage::sendMessageRole($channel, $role, $flexMessage);
                    if($response['success']){
                        if($response['num']){
                            $success_message = $response['num'].'にLINEを送信しました';
                        }else{
                            $error_message = '条件にマッチするユーザーがいませんでした';
                        }
                    }else{
                        $error_message = '指定したロールユーザーへの送信に失敗しました'.$response['message'];
                    }
                }
                // 送信に成功した場合
                if ($success_message) {
                    $ary_success_message[] = $channel['name'].": ".$success_message;
                }
                // 送信に失敗した場合
                else {
                    $ary_error_message[] = $channel['name'].": ".$error_message;
                }
            }
        }
        if(!empty($ary_success_message)){
            // LINE送信に成功した旨をTRANSIENTに5秒間保持
            set_transient(lineconnect::TRANSIENT_KEY__SUCCESS_SEND_TO_LINE, join(' ,',$ary_success_message), lineconnect::TRANSIENT_TIME_LIMIT);
        }
        if(!empty($ary_error_message)){
            // LINE送信に失敗した旨をTRANSIENTに5秒間保持
            set_transient(lineconnect::TRANSIENT_KEY__ERROR_SEND_TO_LINE, join(' ,',$ary_error_message), lineconnect::TRANSIENT_TIME_LIMIT);
        }      
    }

    /**
     * 投稿(公開)した際にLINE送信に失敗した時のメッセージ表示
     */
    static function error_send_to_line() {
        // LINE送信に失敗した旨のメッセージをTRANSIENTから取得
        if (false !== ($error_send_to_line = get_transient(lineconnect::TRANSIENT_KEY__ERROR_SEND_TO_LINE))) {
            echo lineconnect::getNotice($error_send_to_line, lineconnect::NOTICE_TYPE__ERROR);
        }
    }

    /**
     * 投稿(公開)した際にLINE送信に成功した時のメッセージ表示
     */
    static function success_send_to_line() {
        // LINE送信に成功した旨のメッセージをTRANSIENTから取得
        if (false !== ($success_send_to_line = get_transient(lineconnect::TRANSIENT_KEY__SUCCESS_SEND_TO_LINE))) {
            echo lineconnect::getNotice($success_send_to_line, lineconnect::NOTICE_TYPE__SUCCESS);
        }
    }
}