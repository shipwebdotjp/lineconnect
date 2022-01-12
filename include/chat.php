<?php
/**
 * Lineconnect
 * 管理画面でのチャット画面
 */
class lineconnectChat{
    /**
     * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
     * 管理画面のトップメニューページを追加する関数
     */
    static function set_plugin_menu() {
        // 設定のサブメニュー「LINE Connect」を追加
        $page_hook_suffix = add_menu_page(
            // ページタイトル：
            'LINE Connect チャット',
            // メニュータイトル：
            'LINE チャット',
            // 権限：
            // manage_optionsは以下の管理画面設定へのアクセスを許可
            // ・設定 > 一般設定
            // ・設定 > 投稿設定
            // ・設定 > 表示設定
            // ・設定 > ディスカッション
            // ・設定 > パーマリンク設定
            'manage_options',
            // ページを開いたときのURL(slug)：
            lineconnect::SLUG__CHAT_FORM,
            // メニューに紐づく画面を描画するcallback関数：
            ['lineconnectChat', 'show_chat'],
            'dashicons-format-status'
        );
        add_action( "admin_print_styles-{$page_hook_suffix}", ['lineconnectChat', 'wpdocs_plugin_admin_styles']);
        add_action( "admin_print_scripts-{$page_hook_suffix}", ['lineconnectChat', 'wpdocs_plugin_admin_scripts']);
    }
    
    /**
     * 初期設定画面を表示
     */
    static function show_chat() {
        $ary_init_data = array();
        // プラグインのオプション
        $ary_init_data['plugin_options'] = lineconnect::get_all_options();
        $ary_init_data['channels'] = lineconnect::get_all_channels();
        $ary_init_data['ajaxurl'] = admin_url( 'admin-ajax.php');
        $ary_init_data['ajax_nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $users = isset($_GET['users']) ? $_GET['users'] : array();
        $toType = isset($_GET['toType']) ? $_GET['toType'] : "multi";
        $user_ids = array();

        $toTypeList = array(
            array("name" => "broad", "label" => "全て"),
            array("name" => "linked", "label" => "連携済み"),
            array("name" => "role" , "label" => "ロール指定"),
            array("name" => "multi", "label" => "ユーザー指定"),
        );

        if(!is_array($users)){
            $users = array($users);
        }
        
        if(!empty($users)){
            //設定されているロールユーザーに送信
            $args = array(
                'include' => $users,
                'fields' => 'all'
            );
            $to_users=array();	//ユーザーの配列
            $user_query = new WP_User_Query( $args ); //条件を指定してWordpressからユーザーを検索
            $users = $user_query->get_results(); //クエリ実行
            if(! empty( $users )){	//マッチするユーザーが見つかれば
                //ユーザーのメタデータを取得
                foreach($users as $user){
                    $to_users[] = array(
                        'ID' => $user->ID,
                        'user_login' => $user->get('user_login'),
                        'name' => $user->get('display_name'),
                        'user_email' => $user->get('user_email'),
                        'user_url' => $user->get('user_url'),
                        
                    );
                    $user_ids[] = $user->ID;
                }
                
            }
            $ary_init_data['toUsers'] = $to_users;
        }else{
            $ary_init_data['toUsers'] = array();
        }
        $ary_init_data['user_ids'] = $user_ids;


        //チャネルリスト
        $channel_ids = isset($_GET['channel_ids']) ? $_GET['channel_ids'] : array();
        if(!is_array($channel_ids)){
            $channel_ids = array($channel_ids);
        }
        $channelChecked = array();
        foreach($ary_init_data['channels'] as $channel_id => $channel){
            $channelChecked[$channel_id] = array_search($channel_id, $channel_ids) !== false ? true : false;
        }
        $ary_init_data['channelChecked'] = $channelChecked;
        $ary_init_data['toType'] = $toType;
        $ary_init_data['toTypeList'] = $toTypeList;

        // ロールリスト
        $all_roles = array();
        foreach (wp_roles()->get_names() as $role_name) {
            $all_roles[] = array(
                'name' => esc_attr($role_name),
                'label' => translate_user_role($role_name)
            );
        }
        $ary_init_data['roleList'] = $all_roles;
        // error_log(print_r($ary_init_data,true),3,plugin_dir_path(__FILE__ )."../log/chatlog.log");

        $inidata = json_encode($ary_init_data);
        echo <<< EOM
<div id="line_chat_root"></div>
<script>
var lc_initdata = JSON.parse('{$inidata}');
</script>
EOM;
    }

    //チャット送信
    static function ajax_chat_send(){
        $isSuccess = true;
        // ログインしていない場合は無視
        if (!is_user_logged_in()) $isSuccess = false;
        // 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
        if (!is_super_admin() && !current_user_can('administrator') && !current_user_can('editor') && !current_user_can('author')) $isSuccess = false;
        // nonceで設定したcredentialをPOST受信していない場合は無視
        if (!isset($_POST['nonce']) || !$_POST['nonce']) $isSuccess = false;
        // nonceで設定したcredentialのチェック結果に問題がある場合
        if (!check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) $isSuccess = false;

        if($isSuccess){
            $ary_success_message = array();
            $ary_error_message = array();
            //チャンネルリスト毎に送信
            foreach(lineconnect::get_all_channels() as $channel_id => $channel){
                $error_message = $success_message = "";
                $send_checkbox_value = isset($_POST['channel']) && isset($_POST['channel'][$channel_id]) ? $_POST['channel'][$channel_id] === 'true' : false;
                $role = isset($_POST['role']) ? $_POST['role'] : array();
                $message = isset($_POST['message']) ? $_POST['message'] : " ";
                $to = isset($_POST['to']) ? $_POST['to'] : array();
                $type = isset($_POST['type']) ? $_POST['type'] : "";
                $channel_access_token = $channel['channel-access-token'];
                $channel_secret = $channel['channel-secret'];
                $secret_prefix = substr( $channel['channel-secret'],0,4);

                if (strlen($channel_access_token) > 0 && strlen($channel_secret) > 0 && $send_checkbox_value) {
                    require_once (plugin_dir_path(__FILE__).'message.php');
                    $textMessage = lineconnectMessage::createTextMessage($message);
                    if($type == 'broad'){
                        //送信するロールがすべての友達ならブロードキャスト
                        $response = lineconnectMessage::sendBroadcastMessage($channel, $textMessage);
                        if($response['success']){
                            $success_message = '全ての友達にLINEを送信しました';
                        }else{
                            $error_message = '全ての友達への送信に失敗しました'.$response['message'];
                        }
                    }elseif($type == 'multi'){
                        if(!empty($to)){
                            //設定されているロールユーザーに送信
                            $args = array(
                                'include' => $to,
                                'meta_query' => array(
                                    array(
                                        'key'     => lineconnect::META_KEY__LINE,
                                        'compare' => 'EXISTS'
                                    )
                                ),
                                'fields' => 'all_with_meta'
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
                                $response = lineconnectMessage::sendMulticastMessage($channel, $line_user_ids, $textMessage);
                                if($response['success']){
                                    if($response['num']){
                                        $success_message = $response['num'].'人にLINEを送信しました';
                                    }else{
                                        $error_message = '条件にマッチするユーザーがいませんでした';
                                    }
                                }else{
                                    $error_message = '指定したユーザーへの送信に失敗しました '.$response['message'];
                                }
                            }else{
                                //return array('success' => true, 'num' => 0);
                                $error_message = '条件にマッチするユーザーがいませんでした';
                            }
                        }else{
                            $error_message = '対象となるユーザーが指定されていません';
                        }
                    }else{
                        if($type == 'linked'){
                            $role = 'slc_linked';
                        }
                        $response = lineconnectMessage::sendMessageRole($channel, $role, $textMessage);
                        if($response['success']){
                            if($response['num']){
                                $success_message = $response['num'].'人にLINEを送信しました';
                            }else{
                                $error_message = '条件にマッチするユーザーがいませんでした';
                            }
                        }else{
                            $error_message = '指定したロールユーザーへの送信に失敗しました '.$response['message'];
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
        }

        $result['result'] = $isSuccess ? "success" : "failed";
        $result['success'] = $ary_success_message;
        $result['error'] = $ary_error_message;
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($result);
        wp_die();
    }

    //管理画面用にスクリプト読み込み
    static function wpdocs_plugin_admin_scripts(){
        /*
        $chat_js = "line-chat/build/static/js/2.55a144b5.chunk.js";
        wp_enqueue_script(lineconnect::PLUGIN_PREFIX.'chat-2', plugins_url($chat_js, dirname(__FILE__)),array('wp-element'),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_js),true);
        $chat_js = "line-chat/build/static/js/main.baed2f09.chunk.js";
        wp_enqueue_script(lineconnect::PLUGIN_PREFIX.'chat', plugins_url($chat_js, dirname(__FILE__)),array('wp-element'),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_js),true);
        */
        $chat_js = "line-chat/dist/slc_chat.js";
        wp_enqueue_script(lineconnect::PLUGIN_PREFIX.'chat', plugins_url($chat_js, dirname(__FILE__)),array('wp-element'),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_js),true);
    }

    //管理画面用にスタイル読み込み
    static function wpdocs_plugin_admin_styles(){
        $chat_css = "line-chat/dist/style.css";
        wp_enqueue_style(lineconnect::PLUGIN_PREFIX.'admin-css', plugins_url($chat_css, dirname(__FILE__)),array(),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_css));
    }
}