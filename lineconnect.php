<?php

/*
  Plugin Name: LINE Connect
  Plugin URI: https://www.shipweb.jp/
  Description: Account link between WordPress user ID and LINE ID
  Version: 1.0.0
  Author: shipweb
  Author URI: https://www.shipweb.jp/
  License: GPLv3
*/

/*  Copyright 2020 shipweb (email : shipwebdotjp@gmail.com)
    https://www.gnu.org/licenses/gpl-3.0.txt

*/

// WordPressの読み込みが完了してヘッダーが送信される前に実行するアクションに、
// LineConnectクラスのインスタンスを生成するStatic関数をフック
add_action('init', 'lineconnect::instance');

class lineconnect {

    /**
     * このプラグインのバージョン
     */
    const VERSION = '1.0.0';

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
     * OPTIONSテーブルのキー：ChannelAccessToken
     */
    const OPTION_KEY__CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'channel-access-token';

    /**
     * OPTIONSテーブルのキー：ChannelSecret
     */
    const OPTION_KEY__CHANNEL_SECRET = self::PLUGIN_PREFIX . 'channel-secret';

    /**
     * 画面のslug：トップ
     */
    const SLUG__SETTINGS_FORM = self::PLUGIN_ID . '-settings-form';

    /**
     * 画面のslug：初期設定
     */
    const SLUG__INITIAL_CONFIG_FORM = self::PLUGIN_PREFIX . 'initial-config-form';

    /**
     * パラメータ名：ChannelAccessToken
     */
    const PARAMETER__CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'channel-access-token';

    /**
     * パラメータ名：ChannelSecret
     */
    const PARAMETER__CHANNEL_SECRET = self::PLUGIN_PREFIX . 'channel-secret';

    /**
     * TRANSIENTキー(一時入力値)：ChannelAccessToken ※4文字+41文字以下
     */
    const TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'temp-channel-access-token';

    /**
     * TRANSIENTキー(一時入力値)：ChannelSecret ※4文字+41文字以下
     */
    const TRANSIENT_KEY__TEMP_CHANNEL_SECRET = self::PLUGIN_PREFIX . 'temp-channel-secret';

    /**
     * TRANSIENTキー(不正メッセージ)：ChannelAccessToken
     */
    const TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'invalid-channel-access-token';

    /**
     * TRANSIENTキー(不正メッセージ)：ChannelSecret
     */
    const TRANSIENT_KEY__INVALID_CHANNEL_SECRET = self::PLUGIN_PREFIX . 'invalid-channel-secret';

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
     * 暗号化する時のパスワード：STRIPEの公開キーとシークレットキーの複合化で使用
     */
    const ENCRYPT_PASSWORD = 's9YQReXd';

    /**
     * 正規表現：ChannelAccessToken
     */
    const REGEXP_CHANNEL_ACCESS_TOKEN = '/^[a-zA-Z0-9+\/=]{100,}$/';
    /**
     * 正規表現：ChannelSecret
     */
    const REGEXP_CHANNEL_SECRET = '/^[a-z0-9]{30,}$/';

    /**
     * WordPressの読み込みが完了してヘッダーが送信される前に実行するアクションにフックする、
     * SimpleStripeCheckoutクラスのインスタンスを生成するStatic関数
     */
    static function instance() {
        return new self();
    }

    /**
     * 複合化：AES 256
     * @param edata 暗号化してBASE64にした文字列
     * @param string 複合化のパスワード
     * @return 複合化された文字列
     */
    static function decrypt($edata, $password) {
        $data = base64_decode($edata);
        $salt = substr($data, 0, 16);
        $ct = substr($data, 16);
        $rounds = 3; // depends on key length
        $data00 = $password.$salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1].$data00, true);
            $result .= $hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv  = substr($result, 32,16);
        return openssl_decrypt($ct, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * crypt AES 256
     *
     * @param data $data
     * @param string $password
     * @return base64 encrypted data
     */
    static function encrypt($data, $password) {
        // Set a random salt
        $salt = openssl_random_pseudo_bytes(16);
        $salted = '';
        $dx = '';
        // Salt the key(32) and iv(16) = 48
        while (strlen($salted) < 48) {
          $dx = hash('sha256', $dx.$password.$salt, true);
          $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);
        $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($salt . $encrypted_data);
    }

    /**
     * HTMLのOPTIONタグを生成・取得
     */
    static function makeHtmlSelectOptions($list, $selected, $label = null) {
        $html = '';
        foreach ($list as $key => $value) {
            $html .= '<option class="level-0" value="' . $key . '"';
            if ($key == $selected) {
                $html .= ' selected="selected';
            }
            $html .= '">' . (is_null($label) ? $value : $value[$label]) . '</option>';
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

    /**
     * コンストラクタ
     */
    function __construct() {
        // 管理画面を表示中、且つ、ログイン済、且つ、特権管理者or管理者の場合
        if (is_admin() && is_user_logged_in() && (is_super_admin() || current_user_can('administrator'))) {
            // 管理画面のトップメニューページを追加
            add_action('admin_menu', [$this, 'set_plugin_menu']);
            // 管理画面各ページの最初、ページがレンダリングされる前に実行するアクションに、
            // 初期設定を保存する関数をフック
            add_action('admin_init', [$this, 'save_settings']);
        }
        //ログイン時、LINEアカウント連携の場合リダイレクトさせる
        add_action( 'wp_login', [$this, 'redirect_account_link'], 10, 2 );

    }


    /**
     * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
     * 管理画面のトップメニューページを追加する関数
     */
    function set_plugin_menu() {
        // トップメニュー「post2lineoa」を追加
        add_menu_page(
            // ページタイトル：
            'Preferences for LINE Connect',
            // メニュータイトル：
            'LINE Connect',
            // 権限：
            // manage_optionsは以下の管理画面設定へのアクセスを許可
            // ・設定 > 一般設定
            // ・設定 > 投稿設定
            // ・設定 > 表示設定
            // ・設定 > ディスカッション
            // ・設定 > パーマリンク設定
            'manage_options',
            // ページを開いたときのURL(slug)：
            self::SLUG__SETTINGS_FORM,
            // メニューに紐づく画面を描画するcallback関数：
            [$this, 'show_settings'],
            // アイコン：
            // WordPressが用意しているカートのアイコン
            // ・参考（https://developer.wordpress.org/resource/dashicons/#awards）
            'dashicons-format-status',
            // メニューが表示される位置：
            // 省略時はメニュー構造の最下部に表示される。
            // 大きい数値ほど下に表示される。
            // 2つのメニューが同じ位置を指定している場合は片方のみ表示され上書きされる可能性がある。
            // 衝突のリスクは整数値でなく小数値を使用することで回避することができる。
            // 例： 63の代わりに63.3（コード内ではクォートを使用。例えば '63.3'）
            // 初期値はメニュー構造の最下部。
            // ・2 - ダッシュボード
            // ・4 - （セパレータ）
            // ・5 - 投稿
            // ・10 - メディア
            // ・15 - リンク
            // ・20 - 固定ページ
            // ・25 - コメント
            // ・59 - （セパレータ）
            // ・60 - 外観（テーマ）
            // ・65 - プラグイン
            // ・70 - ユーザー
            // ・75 - ツール
            // ・80 - 設定
            // ・99 - （セパレータ）
            // 但しネットワーク管理者メニューでは値が以下の様に異なる。
            // ・2 - ダッシュボード
            // ・4 - （セパレータ）
            // ・5 - 参加サイト
            // ・10 - ユーザー
            // ・15 - テーマ
            // ・20 - プラグイン
            // ・25 - 設定
            // ・30 - 更新
            // ・99 - （セパレータ）
            99
        );
    }

    /**
     * 初期設定画面を表示
     */
    function show_settings() {
        // 初期設定の保存完了メッセージ
        if (false !== ($complete_message = get_transient(self::TRANSIENT_KEY__SAVE_SETTINGS))) {
            $complete_message = self::getNotice($complete_message, self::NOTICE_TYPE__SUCCESS);
        }
        // ChannelAccessTokenの不正メッセージ
        if (false !== ($invalid_channel_access_token = get_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN))) {
            $invalid_channel_access_token = self::getNotice($invalid_channel_access_token, self::NOTICE_TYPE__ERROR);
        }
        // ChannelSecretの不正メッセージ
        if (false !== ($invalid_channel_secret = get_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_SECRET))) {
            $invalid_channel_secret = self::getNotice($invalid_channel_secret, self::NOTICE_TYPE__ERROR);
        }
        // ChannelAccessTokenのパラメータ名
        $param_channel_access_token = self::PARAMETER__CHANNEL_ACCESS_TOKEN;
        // ChannelSecretのパラメータ名
        $param_channel_secret = self::PARAMETER__CHANNEL_SECRET;
        // ChannelAccessTokenをTRANSIENTから取得
        if (false === ($channel_access_token = get_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN))) {
            // 無ければoptionsテーブルから取得
            $channel_access_token = self::decrypt(get_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN), self::ENCRYPT_PASSWORD);
        }
        $channel_access_token = esc_html($channel_access_token);
        // ChannelSecretをTRANSIENTから取得
        if (false === ($channel_secret = get_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_SECRET))) {
            // 無ければoptionsテーブルから取得
            $channel_secret = self::decrypt(get_option(self::OPTION_KEY__CHANNEL_SECRET), self::ENCRYPT_PASSWORD);
        }
        $channel_secret = esc_html($channel_secret);
        // nonceフィールドを生成・取得
        $nonce_field = wp_nonce_field(self::CREDENTIAL_ACTION__SETTINGS_FORM, self::CREDENTIAL_NAME__SETTINGS_FORM, true, false);
        // 送信ボタンを生成・取得
        $submit_button = get_submit_button('Save');
        // HTMLを出力
        echo <<< EOM
            <div class="wrap">
            <h2>Preferences</h2>
            {$complete_message}
            {$invalid_channel_access_token}
            {$invalid_channel_secret}
            <form action="" method='post' id="line-auto-post-settings-form">
                {$nonce_field}
                <p>
                    <label for="{$param_channel_access_token}">Channel Access Token：</label>
                    <input type="text" name="{$param_channel_access_token}" value="{$channel_access_token}"/>
                </p>
                <p>
                    <label for="{$param_channel_secret}">Channel Secret：</label>
                    <input type="text" name="{$param_channel_secret}" value="{$channel_secret}"/>
                </p>
                {$submit_button}
            </form>
            </div>
EOM;
    }

    /**
     * 初期設定を保存するcallback関数
     */
    function save_settings() {
        // nonceで設定したcredentialをPOST受信した場合
        if (isset($_POST[self::CREDENTIAL_NAME__SETTINGS_FORM]) && $_POST[self::CREDENTIAL_NAME__SETTINGS_FORM]) {
            // nonceで設定したcredentialのチェック結果が問題ない場合
            if (check_admin_referer(self::CREDENTIAL_ACTION__SETTINGS_FORM, self::CREDENTIAL_NAME__SETTINGS_FORM)) {
                // ChannelAccessTokenをPOSTから取得
                $channel_access_token = trim(sanitize_text_field($_POST[self::PARAMETER__CHANNEL_ACCESS_TOKEN]));
                // ChannelSecretをPOSTから取得
                $channel_secret = trim(sanitize_text_field($_POST[self::PARAMETER__CHANNEL_SECRET]));
                $valid = true;
                // ChannelAccessTokenが正しくない場合
                if (!preg_match(self::REGEXP_CHANNEL_ACCESS_TOKEN, $channel_access_token)) {
                    // ChannelAccessTokenの設定し直しを促すメッセージをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN, "Invalid Channel Access Token.", self::TRANSIENT_TIME_LIMIT);
                    // 有効フラグをFalse
                    $valid = false;
                }
                // ChannelSecretが正しくない場合
                if (!preg_match(self::REGEXP_CHANNEL_SECRET, $channel_secret)) {
                    // ChannelSecretの設定し直しを促すメッセージをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_SECRET, "Invalid Channel Secret", self::TRANSIENT_TIME_LIMIT);
                    // 有効フラグをFalse
                    $valid = false;
                }
                // 有効フラグがTrueの場合(ChannelAccessTokenとChannelSecretが入力されている場合)
                if ($valid) {
                    // 保存処理
                    // ChannelAccessTokenをoptionsテーブルに保存
                    update_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN, self::encrypt($channel_access_token, self::ENCRYPT_PASSWORD));
                    // ChannelSecretをoptionsテーブルに保存
                    update_option(self::OPTION_KEY__CHANNEL_SECRET, self::encrypt($channel_secret, self::ENCRYPT_PASSWORD));
                    // 保存が完了したら、完了メッセージをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__SAVE_SETTINGS, "Settings saved.", self::TRANSIENT_TIME_LIMIT);
                    // (一応)ChannelAccessTokenの不正メッセージをTRANSIENTから削除
                    delete_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN);
                    // (一応)ChannelSecretの不正メッセージをTRANSIENTから削除
                    delete_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_SECRET);
                    // (一応)ユーザが入力したChannelAccessTokenをTRANSIENTから削除
                    delete_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN);
                    // (一応)ユーザが入力したChannelSecretをTRANSIENTから削除
                    delete_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_SECRET);
                }
                // 有効フラグがFalseの場合(ChannelAccessToken、ChannelSecretが入力されていない場合)
                else {
                    // ユーザが入力したChannelAccessTokenをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN, $channel_access_token, self::TRANSIENT_TIME_LIMIT);
                    // ユーザが入力したChannelSecretをTRANSIENTに5秒間保持
                    set_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_SECRET, $channel_secret, self::TRANSIENT_TIME_LIMIT);
                    // (一応)初期設定の保存完了メッセージを削除
                    delete_transient(self::TRANSIENT_KEY__SAVE_SETTINGS);
                }
                // 設定画面にリダイレクト
                wp_safe_redirect(menu_page_url(self::SLUG__SETTINGS_FORM), 303);
            }
        }
    }
    
    /*
    ログイン時にLINE連携から飛んできた場合は連携用のページへリダイレクトさせる
    */
    function redirect_account_link ( $user_login , $current_user ) {
    	if(isset($_COOKIE["line_connect_redirect_to"])){ //COOKIEにリダイレクト先がセットされていたら
    		$redirect_to = $_COOKIE["line_connect_redirect_to"]; //COKIEからリダイレクト先を取得
    		setcookie('line_connect_redirect_to',"",time() - 3600); //COKIE削除
    		wp_safe_redirect($redirect_to, 303); //セーフリダイレクト
			exit();
    	}
  	}
} // end of class


?>