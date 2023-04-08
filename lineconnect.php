<?php

/*
  Plugin Name: LINE Connect
  Plugin URI: https://blog.shipweb.jp/archives/281
  Description: Account link between WordPress user ID and LINE ID
  Version: 2.5.1
  Author: shipweb
  Author URI: https://blog.shipweb.jp/about
  License: GPLv3
*/

/*  Copyright 2020 shipweb (email : shipwebdotjp@gmail.com)
    https://www.gnu.org/licenses/gpl-3.0.txt

*/

require_once (plugin_dir_path(__FILE__ ).'include/richmenu.php');
require_once (plugin_dir_path(__FILE__ ).'include/setting.php');
require_once (plugin_dir_path(__FILE__ ).'include/publish.php');
require_once (plugin_dir_path(__FILE__ ).'include/message.php');
require_once (plugin_dir_path(__FILE__ ).'include/chat.php');
require_once (plugin_dir_path(__FILE__ ).'include/comment.php');
require_once (plugin_dir_path(__FILE__ ).'include/shortcodes.php');

// WordPressの読み込みが完了してヘッダーが送信される前に実行するアクションに、
// LineConnectクラスのインスタンスを生成するStatic関数をフック


class lineconnect {

    /**
     * このプラグインのバージョン
     */
    const VERSION = '2.2.0';

    /**
     * このプラグインのID：Ship Line Connect
     */
    const PLUGIN_ID = 'slc';

    /**
     * Credentialプレフィックス
     */
    const CREDENTIAL_PREFIX = self::PLUGIN_ID . '-nonce-action_';

    /**
     * CredentialAction：設定
     */
    const CREDENTIAL_ACTION__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-action_settings-form';

    /**
     * CredentialAction：投稿
     */
    const CREDENTIAL_ACTION__POST = self::PLUGIN_ID . '-nonce-action_post';

    /**
     * CredentialName：設定
     */
    const CREDENTIAL_NAME__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-name_settings-form';

    /**
     * CredentialName：投稿
     */
    const CREDENTIAL_NAME__POST = self::PLUGIN_ID . '-nonce-name_post';

    /**
     * (23文字)
     */
    const PLUGIN_PREFIX = self::PLUGIN_ID . '_';

    /**
     * OPTIONSテーブルのキー：Channel
     */
    const OPTION_KEY__CHANNELS = self::PLUGIN_PREFIX . 'channels';

    /**
     * OPTIONSテーブルのキー：Setting
     */
    const OPTION_KEY__SETTINGS = self::PLUGIN_PREFIX . 'settings';

    /**
     * 画面のslug：トップ
     */
    const SLUG__SETTINGS_FORM = self::PLUGIN_ID . '-settings-form';

    /**
     * チャンネルごとに持つパラメーター（設定画面）
     */
    const CHANNEL_OPTION = array(
        'name' => 'チャネル名',
        'channel-access-token' => 'チャネルアクセストークン',
        'channel-secret' => 'チャネルシークレット ',
        'role' => 'デフォルトの送信対象',
        'linked-richmenu' => '連携済みユーザー用リッチメニューID',
        'unlinked-richmenu' => '未連携ユーザー用リッチメニューID',
    );

    /**
     * チャンネルごとに持つパラメーター（投稿画面）
     */
    const CHANNEL_FIELD = array(
        'send-checkbox' => 'LINE送信する',
        'role-selectbox' => '送信対象：',
        'future-checkbox' => '予約投稿時にLINE送信する',
    );

    const SETTINGS_OPTIONS = array(
        'channel' => array(
            'prefix' => '1',
            'name' => 'チャネル',
            'fields' => array(
                      
            ),
        ),
        'connect' => array(
            'prefix' => '2',
            'name' => '連携',
            'fields' => array(
                'login_page_url' => array(
                    'type' => 'text',
                    'label' => 'ログインページURL',
                    'required' => false,
                    'default' => 'wp-login.php',
                    'hint' => 'ログインページのURLをサイトURLからの相対パスで入力してください。'
                ), 
                'link_start_keyword' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携／解除開始キーワード',
                    'required' => true,
                    'default' => 'アカウント連携',
                ),            
                'link_start_title' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携開始メッセージタイトル',
                    'required' => true,
                    'default' => 'アカウント連携開始',
                ),            
                'link_start_body' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携開始メッセージ本文',
                    'required' => true,
                    'default' => '連携を開始します。リンク先でログインを行ってください。',
                ),            
                'link_start_button' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携開始メッセージボタンラベル',
                    'required' => true,
                    'default' => '連携開始',
                ),            
                'link_finish_body' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携完了メッセージ',
                    'required' => true,
                    'default' => 'アカウント連携が完了しました。',
                ),            
                'link_failed_body' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携失敗メッセージ',
                    'required' => true,
                    'default' => 'アカウント連携に失敗しました。',
                ),            
                'unlink_start_title' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携解除開始メッセージタイトル',
                    'required' => true,
                    'default' => 'アカウント連携解除',
                ),            
                'unlink_start_body' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携解除開始メッセージ本文',
                    'required' => true,
                    'default' => 'アカウント連携を解除しますか？',
                ),            
                'unlink_start_button' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携解除開始メッセージボタンラベル',
                    'required' => true,
                    'default' => '連携解除',
                ),            
                'unlink_finish_body' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携解除完了メッセージ',
                    'required' => true,
                    'default' => 'アカウント連係を解除しました。',
                ),            
                'unlink_failed_body' => array(
                    'type' => 'text',
                    'label' => 'アカウント連携解除失敗メッセージ',
                    'required' => true,
                    'default' => 'アカウント連携解除に失敗しました。',
                ),            
            ),
        ),
        'publish' => array(
            'prefix' => '3',
            'name' => '投稿通知',
            'fields' => array(
                'send_post_types' => array(
                    'type' => 'multiselect',
                    'label' => '投稿タイプ',
                    'required' => false,
                    'list' => array('post' => '投稿','page' => '固定ページ'),
                    'default' => array('post'),
                    'isMulti' => true,
                    'hint' => '通知する対象の投稿タイプです。選んだ投稿タイプの編集画面にLINE送信チェックボックスが表示されます。'
                ), 
                'default_send_checkbox' => array(
                    'type' => 'select',
                    'label' => '「LINE送信する」チェックボックスのデフォルト値',
                    'required' => true,
                    'list' => array('on' => 'チェックあり','off' => 'チェックなし','new' => '公開済みの場合はチェックなし'),
                    'default' => 'new',
                    'hint' => '記事編集時、「LINE送信する」チェックボックスのデフォルト値設定です。',
                ), 
                'more_label' => array(
                    'type' => 'text',
                    'label' => 'リンクラベル',
                    'required' => true,
                    'default' => 'Read more',
                ),
                'send_new_comment' => array(
                    'type' => 'checkbox',
                    'label' => 'コメントがあった時に投稿者へLINE通知を行う',
                    'required' => false,
                    'default' => false,
                    'hint' => '記事にコメントがあった場合に、その記事の投稿者へコメントがあったことを通知するかどうかの設定です。'
                ),
                'comment_read_label' => array(
                    'type' => 'text',
                    'label' => 'コメントリンクラベル',
                    'required' => true,
                    'default' => 'Read comment',
                ),        
            ),
        ),
        'style' => array(
            'prefix' => '4',
            'name' => '通知スタイル',
            'fields' => array(
                'image_aspectmode' => array(
                    'type' => 'select',
                    'label' => '画像表示スタイル',
                    'required' => true,
                    'list' => array('cover' => '領域全体に表示','fit' => '画像全体を表示'),
                    'default' => 'cover',
                    'hint' => '領域全体：領域に合わせて画像が拡大されます。画像周囲が切り取られる場合があります。画像全体：画像の周囲に余白を追加して画像全体を表示します。',
                ), 
                'image_aspectrate' => array(
                    'type' => 'text',
                    'label' => '画像領域アクペクト比',
                    'required' => true,
                    'default' => '2:1',
                    'regex' => '/^[1-9]+:[1-9]+$/',
                    'hint' => '画像領域のアスペクト比です。高さを幅の三倍を超える値にはできません。',
                ), 
                'title_backgraound_color' => array(
                    'type' => 'color',
                    'label' => '背景色',
                    'required' => true,
                    'default' => '#FFFFFF',
                    'hint' => '通知メッセージの背景色です。',
                ),
                'title_text_color' => array(
                    'type' => 'color',
                    'label' => 'タイトル文字色',
                    'required' => true,
                    'default' => '#000000',
                    'hint' => '通知メッセージのタイトル文字色です。',
                ),
                'body_text_color' => array(
                    'type' => 'color',
                    'label' => '本文文字色',
                    'required' => true,
                    'default' => '#000000',
                    'hint' => '通知メッセージの本文文字色です。',
                ),
                'link_text_color' => array(
                    'type' => 'color',
                    'label' => 'リンク文字色',
                    'required' => true,
                    'default' => '#1e90ff',
                    'hint' => '通知メッセージのリンク文字色です。',
                ),
                'title_rows' => array(
                    'type' => 'spinner',
                    'label' => 'タイトル最大行数',
                    'required' => false,
                    'default' => 3,
                    'hint' => '通知メッセージでタイトルを最大何行まで表示するかの設定です。',
                ),
                'body_rows' => array(
                    'type' => 'spinner',
                    'label' => '本文最大行数',
                    'required' => false,
                    'default' => 5,
                    'hint' => '通知メッセージで本文を最大何行まで表示するかの設定です。これとは別に、最大500文字に切り詰められます。',
                ),
            ),
        ),
        'chat' => array(
            'prefix' => '5',
            'name' => 'チャット',
            'fields' => array(
                'enableChatbot' => array(
                    'type' => 'select',
                    'label' => 'AIによる自動応答',
                    'required' => true,
                    'list' => array('off' => '無効','on' => '有効'),
                    'default' => 'off',
                    'hint' => 'メッセージに対してAIによる自動応答を利用するかどうかの設定です。',
                ), 
                'openai_secret' => array(
                    'type' => 'text',
                    'label' => 'OpenAI API Key',
                    'required' => false,
                    'default' => '',
                    'size' => 60,
                    'hint' => 'OpenAIのAPI Keyを入力してください。',
                ), 
                'openai_model' => array(
                    'type' => 'select',
                    'label' => '使用モデル',
                    'required' => false,
                    'list' => array('gpt-3.5-turbo' => 'GPT-3.5 turbo','gpt-4' => 'GPT-4',),
                    'default' => 'gpt-3.5-turbo',
                    'hint' => 'どのモデルを使用するかの設定です。',
                ),
                'openai_system' => array(
                    'type' => 'textarea',
                    'label' => 'システムプロンプト',
                    'required' => false,
                    'default' => '',
                    'hint' => 'AIに最初に与える命令です。例：「あなたは優秀なサポートスタッフです。お客様からの問い合わせに丁寧に回答してください。」',
                ), 
                'openai_context' => array(
                    'type' => 'spinner',
                    'label' => '使用する文脈数',
                    'required' => false,
                    'default' => 3,
                    'regex' => '/^\d+$/',
                    'hint' => '文脈を理解して回答を行わせるため、会話履歴をいくつ使用するかの設定です。',
                ),
                'openai_max_tokens' => array(
                    'type' => 'spinner',
                    'label' => '最大トークン数',
                    'required' => false,
                    'default' => 512,
                    'regex' => '/^\d+$/',
                    'hint' => '使用する最大トークン数です。',
                ),
                'openai_temperature' => array(
                    'type' => 'text',
                    'label' => 'サンプリング温度',
                    'required' => false,
                    'default' => 1,
                    'hint' => 'パラーメーターtemperatureです。数値が高いほどより多様な単語が選ばれやすくなります。0～1の範囲で指定してください。',
                ),
                'openai_limit_normal' => array(
                    'type' => 'spinner',
                    'label' => '未連携ユーザーが1日に使用できる回数',
                    'required' => false,
                    'default' => 3,
                    'regex' => '/^[+-]?\d+$/',
                    'hint' => 'サイトアカウントと未連携のユーザーが1日に何回まで使用できるかの設定です。-1で制限なしになります。',
                ),
                'openai_limit_linked' => array(
                    'type' => 'spinner',
                    'label' => '連携済みユーザーが1日に使用できる回数',
                    'required' => false,
                    'default' => 5,
                    'regex' => '/^[+-]?\d+$/',
                    'hint' => 'サイトアカウント連携済みのユーザーが1日に何回まで使用できるかの設定です。-1で制限なしになります。',
                ),
                'openai_limit_message' => array(
                    'type' => 'textarea',
                    'label' => '制限回数を超えた場合のメッセージ',
                    'required' => false,
                    'default' => '1日に使用できる回数（%limit%回）を超えました。日付が変わってからお試しください。',
                    'hint' => '1日に使用できる回数を超えた場合に表示するメッセージです。%limit%は制限回数に置き換えられます。',
                ), 
            ),
        ),
    );

    /**
     * OPTIONSテーブルのPREFIX
     */
    const OPTION_PREFIX = self::PLUGIN_PREFIX;

    /**
     * パラメーターのPREFIX
     */
    const PARAMETER_PREFIX = self::PLUGIN_PREFIX;

    /**
     * 一時入力値保持用のPREFIX
     */
    const TRANSIENT_PREFIX = self::PLUGIN_PREFIX. 'temp-';

    /**
     * 不正入力値エラー表示のPREFIX
     */
    const INVALID_PREFIX = self::PLUGIN_PREFIX. 'invalid-';

    /**
     * パラメータ名：LINEメッセージ送信チェックボックス
     */
    const PARAMETER__SEND_CHECKBOX = self::PLUGIN_PREFIX . 'send-checkbox';

    /**
     * TRANSIENTキー(エラー)：LINEメッセージ送信失敗
     */
    const TRANSIENT_KEY__ERROR_SEND_TO_LINE = self::PLUGIN_PREFIX . 'error-send-to-line';

    /**
     * TRANSIENTキー(成功)：LINEメッセージ送信成功
     */
    const TRANSIENT_KEY__SUCCESS_SEND_TO_LINE = self::PLUGIN_PREFIX . 'success-send-to-line';

    /**
     * TRANSIENTキー(保存完了メッセージ)：設定
     */
    const TRANSIENT_KEY__SAVE_SETTINGS = self::PLUGIN_PREFIX . 'save-settings';

    /**
     * TRANSIENTのタイムリミット：5秒
     */
    const TRANSIENT_TIME_LIMIT = 5;

    /**
     * 通知タイプ：エラー
     */
    const NOTICE_TYPE__ERROR = 'error';

    /**
     * 通知タイプ：警告
     */
    const NOTICE_TYPE__WARNING = 'warning';

    /**
     * 通知タイプ：成功
     */
    const NOTICE_TYPE__SUCCESS = 'success';

    /**
     * 通知タイプ：情報
     */
    const NOTICE_TYPE__INFO = 'info';

    /**
     * 正規表現：ChannelAccessToken
     */
    const REGEXP_CHANNEL_ACCESS_TOKEN = '/^[a-zA-Z0-9+\/=]{100,}$/';

    /**
     * 正規表現：ChannelSecret
     */
    const REGEXP_CHANNEL_SECRET = '/^[a-z0-9]{30,}$/';

    /**
     * ユーザーメタキー：line
     */
    const META_KEY__LINE = 'line';

    /**
     * 画面のslug：チャット
     */
    const SLUG__CHAT_FORM = self::PLUGIN_ID . '-linechat-form';

    /**
     * 投稿メタキー：is-send-line
     */
    const META_KEY__IS_SEND_LINE = 'is-send-line';

    /**
     * ボットとのチャットログ MySQLテーブル名
     */
    const TABLE_BOT_LOGS = 'lineconnect_bot_logs';
    
    /**
     * イベントタイプ
     */
    const WH_EVENT_TYPE = array(
        1 => 'message',
        2 => 'unsend',
        3 => 'follow',
        4 => 'unfollow',
        5 => 'join',
        6 => 'leave',
        7 => 'memberJoined',
        8 => 'memberLeft',
        9 => 'postback',
        10 => 'videoPlayComplete',
        11 => 'beacon',
        12 => 'accountLink',
        13 => 'things',
    );
    
    /**
     * ソースタイプ
     */
    const WH_SOURCE_TYPE = array(
        1 => 'user',
        2 => 'group',
        3 => 'room',
        11 => 'bot',
    );
    
    /**
     * メッセージタイプ
     */
    const WH_MESSAGE_TYPE = array(
        1 => 'text',
        2 => 'image',
        3 => 'video',
        4 => 'audio',
        5 => 'file',
        6 => 'location',
        7 => 'sticker',
    );

    const ASSETS_SVG_FILENAME = 'assets/symbol-defs.svg';

    /**
     * WordPressの読み込みが完了してヘッダーが送信される前に実行するアクションにフックする、
     * LineConnectクラスのインスタンスを生成するStatic関数
     */
    static function instance() {
        return new self();
    }

    /**
     * HTMLのOPTIONタグを生成・取得
     */
    static function makeHtmlSelectOptions($list, $selected, $label = null) {
        $html = '';
        foreach ($list as $key => $value) {
            $html .= '<option class="level-0" value="' . $key . '"';
            if ($key == $selected || (is_array($selected) && in_array($key, $selected))) {
                $html .= ' selected="selected"';
            }
            $html .= '>' . (is_null($label) ? $value : $value[$label]) . '</option>';
        }
        return $html;
    }

    /**
     * 通知タグを生成・取得
     * @param message 通知するメッセージ
     * @param type 通知タイプ(error/warning/success/info)
     * @retern 通知タグ(HTML)
     */
    static function getNotice($message, $type) {
        return 
            '<div class="notice notice-' . $type . ' is-dismissible">' .
            '<p><strong>' . esc_html($message) . '</strong></p>' .
            '<button type="button" class="notice-dismiss">' .
            '<span class="screen-reader-text">Dismiss this notice.</span>' .
            '</button>' .
            '</div>';
    }

    static function getErrorBar($message, $type){
        return '<div class="error">' .esc_html($message).'</div>';
    }

    /**
     * コンストラクタ
     */
    function __construct() {
        //ログ記録のためのコネクタ呼び出し
        add_action( 'plugins_loaded', [ $this, 'register_stream_connector' ], 99, 1  );

        add_action('init', function(){
            global $post_type,$pagenow;
            $post_types = self::get_option('send_post_types');
            foreach ( $post_types as $post_type ) {
                add_action('publish_'.$post_type, ['lineconnectPublish', 'publish_post'], 15, 6);
            }
            add_action( 'save_post', ['lineconnectPublish', 'save_post'], 10, 2 );
            if($pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
                // 投稿(公開)した際にLINE送信に失敗した時のメッセージ表示
                add_action('admin_notices', ['lineconnectPublish', 'error_send_to_line']);
                // 投稿(公開)した際にLINE送信に成功した時のメッセージ表示
                add_action('admin_notices', ['lineconnectPublish', 'success_send_to_line']);
            }
            // 投稿画面にチェックボックスを表示
            add_action('add_meta_boxes', ['lineconnectPublish', 'add_send_checkbox'], 10, 2 );  
            // 投稿画面のとき、スクリプトを読み込む
            add_action( 'admin_enqueue_scripts', ['lineconnectPublish', 'wpdocs_selectively_enqueue_admin_script'] );

            // 管理画面を表示中、且つ、ログイン済、且つ、特権管理者or管理者の場合
            if (is_admin() && is_user_logged_in() && (is_super_admin() || current_user_can('administrator'))) {
                // 管理画面のトップメニューページを追加
                add_action('admin_menu', ['lineconnectSetting', 'set_plugin_menu']);
                // 管理画面各ページの最初、ページがレンダリングされる前に実行するアクションに、
                // 初期設定を保存する関数をフック
                add_action('admin_init', ['lineconnectSetting', 'save_settings']);
                // ユーザー一覧一覧のコラム追加
                add_filter('manage_users_columns', [$this, 'lc_manage_columns']);
                // ユーザー一覧に追加したカスタムコラムの表示を行うフィルター
                add_filter('manage_users_custom_column', [$this, 'lc_manage_custom_columns'], 10, 3);
                // チャット画面のトップメニューページを追加
                add_action('admin_menu', ['lineconnectChat', 'set_plugin_menu']);
                // ユーザー一覧の一括操作にメッセージ送信を追加
                add_filter( 'bulk_actions-users', [$this,'add_bulk_users_sendmessage'], 10, 1 );
                // 一括操作を行うフィルター
                add_filter( 'handle_bulk_actions-users', [$this,'handle_bulk_users_sendmessage'], 10, 3 );
                // チャット送信AJAXアクション
                add_action( 'wp_ajax_lc_ajax_chat_send', ['lineconnectChat', 'ajax_chat_send'] );
            }
            //ログイン時、LINEアカウント連携の場合リダイレクトさせる
            add_action( 'wp_login', [$this, 'redirect_account_link'], 10, 2 );

            //ユーザーにリッチメニューを関連付ける
            add_action( 'line_link_richmenu', ['lineconnectRichmenu', 'link_richmenu'], 10, 1 );
        
            //ユーザーからリッチメニューを削除する
            add_action( 'line_unlink_richmenu', ['lineconnectRichmenu', 'line_unlink_richmenu'], 10, 2 );

            //特定ロールの連携済みユーザーへメッセージを送信
            add_action( 'send_message_to_role', ['lineconnectMessage', 'sendMessageRole'], 10, 3 );

            //特定の連携済みユーザーへメッセージを送信
            add_action( 'send_message_to_wpuser', ['lineconnectMessage', 'sendMessageWpUser'], 10, 3 );

            if(self::get_option('send_new_comment')){
                //コメントが投稿されたときのアクション
                add_action( 'comment_post', ['lineconnectComment', 'comment_post_callback'], 10, 2 );

                //コメントステータスが変化したときのアクション
                add_action('transition_comment_status', ['lineconnectComment', 'approve_comment_callback'], 10, 3);                
            }

            //ChatGPTとの会話ログ表示ショートコード
            add_shortcode( 'line_connect_chat_gpt_log',  ['lineconnectShortcodes','show_chat_log'] ); 

            // ショートコード用のCSSを読み込むため
            add_action( 'wp_enqueue_scripts', ['lineconnectShortcodes', 'enqueue_script'] );

        });

        //プラグイン有効化時に呼び出し
        register_activation_hook(__FILE__, [ $this, 'pluginActivation' ] );

    }

    /**
     * 登録されているチャネル情報を返す
     */
    static function get_all_channels(){
        $channels = get_option(self::OPTION_KEY__CHANNELS, array()); //チャネル情報を取得
        return $channels;
    }

    /**
     * チャネルシークレット先頭4文字(またはチャンネル番号)から登録されているチャネル情報を返す
     */
    static function get_channel($channel_prefix){
        foreach(lineconnect::get_all_channels() as $channel_id => $channel){
            if($channel_prefix === $channel_id || $channel_prefix === $channel['prefix']){
                return $channel;
            }
        }
        return null;
    }

    /**
     * LINE IDからWPUserを返す
     */
    static function get_wpuser_from_line_id($secret_prefix, $line_id){
        $args = array(
            'meta_query' => array(
                array(
                    'key'     => self::META_KEY__LINE,
                    'compare' => 'EXISTS'
                )
            ),
            'fields'=>'all_with_meta'
        );
        $user_query = new WP_User_Query( $args );
        $users = $user_query->get_results(); //クエリ実行
        if(! empty( $users )){	//マッチするユーザーが見つかれば
            //ユーザーのメタデータを取得
            foreach($users as $user){
                $user_meta_line = $user->get(self::META_KEY__LINE);
                if($user_meta_line && $user_meta_line[$secret_prefix]){
                    if( $user_meta_line[$secret_prefix]['id'] == $line_id ){
                        return $user;
                    }
                }
            }
        }
        return false;
    }

    /*
    ログイン時にLINE連携から飛んできた場合は連携用のページへリダイレクトさせる
    */
    function redirect_account_link ( $user_login , $current_user ) {
        error_log("logged in: ".$user_login." ID:".$current_user->ID."\n", 3, __DIR__. '/log/loggin.log');
    	if(isset($_COOKIE["line_connect_redirect_to"])){ //COOKIEにリダイレクト先がセットされていたら
    		$redirect_to = $_COOKIE["line_connect_redirect_to"]; //COKIEからリダイレクト先を取得
    		setcookie('line_connect_redirect_to',"",time() - 3600); //COKIE削除
    		wp_safe_redirect($redirect_to, 303); //セーフリダイレクト
			exit();
    	}
  	}

    /**
     * 登録されているオプション情報を全て返す
     */
    static function get_all_options(){
        $options = get_option(self::OPTION_KEY__SETTINGS); //オプションを取得
        foreach(self::SETTINGS_OPTIONS as $tab_name => $tab_details){
            //flatten
            foreach($tab_details['fields'] as $option_key => $option_details){
                if(!isset($options[$option_key])){
                    $options[$option_key] = $option_details['default'];
                }
            }
        }
        return $options;
    }

    /**
     * 登録されているオプションの値を返す
     */
    static function get_option($option_name){
        $options = get_option(self::OPTION_KEY__SETTINGS); //オプションを取得
        if(isset($options[$option_name])){
            return $options[$option_name];
        }
        foreach(self::SETTINGS_OPTIONS as $tab_name => $tab_details){
            //flatten
            foreach($tab_details['fields'] as $option_key => $option_details){
                if($option_name == $option_key){
                    return $option_details['default'];
                }
            }
        }
        return null;
    }

    /**
     * Checks if the current request is a WP REST API request.
     *
     * Case #1: After WP_REST_Request initialisation
     * Case #2: Support "plain" permalink settings
     * Case #3: It can happen that WP_Rewrite is not yet initialized,
     *          so do this (wp-settings.php)
     * Case #4: URL Path begins with wp-json/ (your REST prefix)
     *          Also supports WP installations in subfolders
     *
     * @returns boolean
     * @author matzeeable
     */
    static function is_rest() {
        $prefix = rest_get_url_prefix( );
        if (defined('REST_REQUEST') && REST_REQUEST // (#1)
                || isset($_GET['rest_route']) // (#2)
                        && strpos( trim( $_GET['rest_route'], '\\/' ), $prefix , 0 ) === 0)
                return true;
        // (#3)
        global $wp_rewrite;
        if ($wp_rewrite === null) $wp_rewrite = new WP_Rewrite();

        // (#4)
        $rest_url = wp_parse_url( trailingslashit( rest_url( ) ) );
        $current_url = wp_parse_url( add_query_arg( array( ) ) );
        return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
    }

    function register_stream_connector() {
        add_filter(
			'wp_stream_connectors',
			function( $classes ) {
				require_once (plugin_dir_path(__FILE__ ).'include/logging.php');
                $class = new lineconnectConnector();
				$classes[] = $class;
				return $classes;
			}
		);
    }

    // カラムを追加・削除する
    function lc_manage_columns($columns) {
        // 追加するカラム。 カラム名（任意） => カラムのラベル（任意）
        $add_columns = array(
            'lc_islinked' => 'LINE連携'
        );
    
        return array_merge($columns, $add_columns);
    }
    
    // セルに表示させる値
    function lc_manage_custom_columns($output, $column_name, $user_id) {
        switch($column_name) {
            case 'lc_islinked':
                $ary_output = array();
                foreach(self::get_all_channels() as $channel_id => $channel){
                    $secret_prefix = substr( $channel['channel-secret'],0,4);
                    $user_meta_line = get_user_meta($user_id, lineconnect::META_KEY__LINE, true);
                    if($user_meta_line && isset($user_meta_line[$secret_prefix]) && isset($user_meta_line[$secret_prefix]['id'])){
                        $line_sendmessage_url = add_query_arg(array('users'=>$user_id, 'channel_ids' => $channel_id), admin_url( 'admin.php?page='.lineconnect::SLUG__CHAT_FORM ));
                        $ary_output[] = "<a href=\"".$line_sendmessage_url."\" title=\"".(isset($user_meta_line[$secret_prefix]['displayName']) ? $user_meta_line[$secret_prefix]['displayName'] : "")."\">連携済</a>";
                    }else{
                        $ary_output[] = "未連携";
                    }
                }
                return implode("/",$ary_output);
        }
        return $output;
    }

    //ユーザー一括操作にメッセージ送信を追加
    function add_bulk_users_sendmessage($actions){
        $actions['lc_linechat'] = 'LINEメッセージ送信';
        return $actions;
    }

    //ユーザー一括操作でLINEメッセージ送信が選択されたら
    function handle_bulk_users_sendmessage( $sendback, $doaction, $items ) {
        if ( $doaction !== 'lc_linechat' ) {
            return $sendback;
        }
        $user_args = array();
        foreach($items as $index => $userid){
            $user_args['users['.$index.']'] = $userid;
        }
        $redirect_url = add_query_arg($user_args, admin_url( 'admin.php?page='.lineconnect::SLUG__CHAT_FORM ));
        return $redirect_url;
    }

    function pluginActivation(){
        //プラグイン有効化時
        //テーブル作成
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_BOT_LOGS; 
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            event_id varchar(32) NOT NULL,
            event_type tinyint NOT NULL,
            source_type tinyint NOT NULL,
            user_id varchar(255) NOT NULL,
            bot_id varchar(255) NOT NULL,
            message_type tinyint NOT NULL,
            message text,
            timestamp datetime(3) NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY event_id (event_id)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // //インデックス作成
        // $sql_alter = "
        //     ALTER TABLE `$table_name`
        //     ADD KEY `user_id` (`user_id`),
        //     ADD KEY `event_id` (`event_id`);
        // ";
        // $wpdb->query( $sql_alter );

    }


} // end of class

$GLOBALS['lineconnect'] = new lineconnect;
?>