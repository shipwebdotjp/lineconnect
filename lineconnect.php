<?php

/*
	Plugin Name: LINE Connect
	Plugin URI: https://blog.shipweb.jp/lineconnect/
	Description: Account link between WordPress user ID and LINE ID
	Version: 2.7.0
	Author: shipweb
	Author URI: https://blog.shipweb.jp/about
	License: GPLv3
*/

/*
	Copyright 2020 shipweb (email : shipwebdotjp@gmail.com)
	https://www.gnu.org/licenses/gpl-3.0.txt

*/

require_once plugin_dir_path( __FILE__ ) . 'include/richmenu.php';
require_once plugin_dir_path( __FILE__ ) . 'include/setting.php';
require_once plugin_dir_path( __FILE__ ) . 'include/publish.php';
require_once plugin_dir_path( __FILE__ ) . 'include/message.php';
require_once plugin_dir_path( __FILE__ ) . 'include/chat.php';
require_once plugin_dir_path( __FILE__ ) . 'include/comment.php';
require_once plugin_dir_path( __FILE__ ) . 'include/shortcodes.php';
require_once plugin_dir_path( __FILE__ ) . 'include/admin.php';
require_once plugin_dir_path( __FILE__ ) . 'include/const.php';
require_once plugin_dir_path( __FILE__ ) . 'include/botlog.php';
require_once plugin_dir_path( __FILE__ ) . 'include/openai.php';
require_once plugin_dir_path( __FILE__ ) . 'include/functions.php';

// WordPressの読み込みが完了してヘッダーが送信される前に実行するアクションに、
// LineConnectクラスのインスタンスを生成するStatic関数をフック


class lineconnect {

	/**
	 * このプラグインのバージョン
	 */
	const VERSION = '2.7.0';

	/**
	 * このプラグインのデータベースバージョン
	 */
	const DB_VERSION = '1.1';

	/**
	 * このプラグインのID：Ship Line Connect
	 */
	const PLUGIN_ID = 'slc';

	/**
	 * このプラグインの名前：Line Connect
	 */
	const PLUGIN_NAME = 'lineconnect';

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
	 * OPTIONSテーブルのキー：Variable
	 */
	const OPTION_KEY__VARIABLES = self::PLUGIN_PREFIX . 'variables';

	/**
	 * 画面のslug：トップ
	 */
	const SLUG__SETTINGS_FORM = self::PLUGIN_ID . '-settings-form';

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
	const TRANSIENT_PREFIX = self::PLUGIN_PREFIX . 'temp-';

	/**
	 * 不正入力値エラー表示のPREFIX
	 */
	const INVALID_PREFIX = self::PLUGIN_PREFIX . 'invalid-';

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
	 * 画面のslug：LINE GPT Log
	 */
	const SLUG__LINE_GPTLOG = self::PLUGIN_ID . '-linegpt-log';

	/**
	 * 投稿メタキー：is-send-line
	 */
	const META_KEY__IS_SEND_LINE = 'is-send-line';

	/**
	 * チャットログリスト表示用クラス
	 */
	var $wp_gptlog_list_table;

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
	static function makeHtmlSelectOptions( $list, $selected, $label = null ) {
		$html = '';
		foreach ( $list as $key => $value ) {
			$html .= '<option class="level-0" value="' . $key . '"';
			if ( $key == $selected || ( is_array( $selected ) && in_array( $key, $selected ) ) ) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . ( is_null( $label ) ? $value : $value[ $label ] ) . '</option>';
		}
		return $html;
	}

	/**
	 * 通知タグを生成・取得
	 *
	 * @param message 通知するメッセージ
	 * @param type 通知タイプ(error/warning/success/info)
	 * @retern 通知タグ(HTML)
	 */
	static function getNotice( $message, $type ) {
		return '<div class="notice notice-' . $type . ' is-dismissible">' .
			'<p><strong>' . esc_html( $message ) . '</strong></p>' .
			'<button type="button" class="notice-dismiss">' .
			'<span class="screen-reader-text">Dismiss this notice.</span>' .
			'</button>' .
			'</div>';
	}

	static function getErrorBar( $message, $type ) {
		return '<div class="error">' . esc_html( $message ) . '</div>';
	}

	/**
	 * コンストラクタ
	 */
	function __construct() {
		// ログ記録のためのコネクタ呼び出し
		add_action( 'plugins_loaded', array( $this, 'register_stream_connector' ), 99, 1 );

		add_action(
			'init',
			function () {
				global $post_type, $pagenow;
				$post_types = self::get_option( 'send_post_types' );
				foreach ( $post_types as $post_type ) {
					add_action( 'publish_' . $post_type, array( 'lineconnectPublish', 'publish_post' ), 15, 6 );
				}
				add_action( 'save_post', array( 'lineconnectPublish', 'save_post' ), 10, 2 );
				if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
					// 投稿(公開)した際にLINE送信に失敗した時のメッセージ表示
					add_action( 'admin_notices', array( 'lineconnectPublish', 'error_send_to_line' ) );
					// 投稿(公開)した際にLINE送信に成功した時のメッセージ表示
					add_action( 'admin_notices', array( 'lineconnectPublish', 'success_send_to_line' ) );
				}
				// 投稿画面にチェックボックスを表示
				add_action( 'add_meta_boxes', array( 'lineconnectPublish', 'add_send_checkbox' ), 10, 2 );
				// 投稿画面のとき、スクリプトを読み込む
				add_action( 'admin_enqueue_scripts', array( 'lineconnectPublish', 'wpdocs_selectively_enqueue_admin_script' ) );

				// 管理画面を表示中、且つ、ログイン済、且つ、特権管理者or管理者の場合
				if ( is_admin() && is_user_logged_in() && ( is_super_admin() || current_user_can( 'administrator' ) ) ) {
					// 管理画面のトップメニューページを追加
					add_action( 'admin_menu', array( 'lineconnectSetting', 'set_plugin_menu' ) );
					// 管理画面各ページの最初、ページがレンダリングされる前に実行するアクションに、
					// 初期設定を保存する関数をフック
					add_action( 'admin_init', array( 'lineconnectSetting', 'save_settings' ) );
					// ユーザー一覧一覧のコラム追加
					add_filter( 'manage_users_columns', array( 'lineconnectAdmin', 'lc_manage_columns' ) );
					// ユーザー一覧に追加したカスタムコラムの表示を行うフィルター
					add_filter( 'manage_users_custom_column', array( 'lineconnectAdmin', 'lc_manage_custom_columns' ), 10, 3 );
					// チャット画面のトップメニューページを追加
					add_action( 'admin_menu', array( 'lineconnectChat', 'set_plugin_menu' ) );
					// ユーザー一覧の一括操作にメッセージ送信を追加
					add_filter( 'bulk_actions-users', array( 'lineconnectAdmin', 'add_bulk_users_sendmessage' ), 10, 1 );
					// 一括操作を行うフィルター
					add_filter( 'handle_bulk_actions-users', array( 'lineconnectAdmin', 'handle_bulk_users_sendmessage' ), 10, 3 );
					// チャット送信AJAXアクション
					add_action( 'wp_ajax_lc_ajax_chat_send', array( 'lineconnectChat', 'ajax_chat_send' ) );
					// BOT LOGリストのトップメニューページを追加
					add_action( 'admin_menu', array( $this, 'set_page_gptlog' ) );
				}
				// ログイン時、LINEアカウント連携の場合リダイレクトさせる
				add_action( 'wp_login', array( $this, 'redirect_account_link' ), 10, 2 );

				// ユーザーにリッチメニューを関連付ける
				add_action( 'line_link_richmenu', array( 'lineconnectRichmenu', 'link_richmenu' ), 10, 1 );

				// ユーザーからリッチメニューを削除する
				add_action( 'line_unlink_richmenu', array( 'lineconnectRichmenu', 'line_unlink_richmenu' ), 10, 2 );

				// 特定ロールの連携済みユーザーへメッセージを送信
				add_action( 'send_message_to_role', array( 'lineconnectMessage', 'sendMessageRole' ), 10, 3 );

				// 特定の連携済みユーザーへメッセージを送信
				add_action( 'send_message_to_wpuser', array( 'lineconnectMessage', 'sendMessageWpUser' ), 10, 3 );

				if ( self::get_option( 'send_new_comment' ) ) {
					// コメントが投稿されたときのアクション
					add_action( 'comment_post', array( 'lineconnectComment', 'comment_post_callback' ), 10, 2 );

					// コメントステータスが変化したときのアクション
					add_action( 'transition_comment_status', array( 'lineconnectComment', 'approve_comment_callback' ), 10, 3 );
				}

				// ChatGPTとの会話ログ表示ショートコード
				add_shortcode( 'line_connect_chat_gpt_log', array( 'lineconnectShortcodes', 'show_chat_log' ) );

				// ショートコード用のCSSを読み込むため
				add_action( 'wp_enqueue_scripts', array( 'lineconnectShortcodes', 'enqueue_script' ) );
			}
		);

		// プラグイン有効化時に呼び出し
		register_activation_hook( __FILE__, array( $this, 'pluginActivation' ) );

		// テキストドメイン呼出し
		load_plugin_textdomain( self::PLUGIN_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// チャットのイニシャライズ
		// lineconnectChat::initialize();

		// lineconnectConst class initialize
		lineconnectConst::initialize();
	}

	/**
	 * 登録されているチャネル情報を返す
	 */
	static function get_all_channels() {
		$channels = get_option( self::OPTION_KEY__CHANNELS, array() ); // チャネル情報を取得
		return $channels;
	}

	/**
	 * チャネルシークレット先頭4文字(またはチャンネル番号)から登録されているチャネル情報を返す
	 */
	static function get_channel( $channel_prefix ) {
		foreach ( self::get_all_channels() as $channel_id => $channel ) {
			if ( $channel_prefix === $channel_id || $channel_prefix === $channel['prefix'] ) {
				return $channel;
			}
		}
		return null;
	}

	/**
	 * LINE IDからWPUserを返す
	 */
	static function get_wpuser_from_line_id( $secret_prefix, $line_id ) {
		$args       = array(
			'meta_query' => array(
				array(
					'key'     => self::META_KEY__LINE,
					'compare' => 'EXISTS',
				),
			),
			'fields'     => 'all_with_meta',
		);
		$user_query = new WP_User_Query( $args );
		$users      = $user_query->get_results(); // クエリ実行
		if ( ! empty( $users ) ) {   // マッチするユーザーが見つかれば
			// ユーザーのメタデータを取得
			foreach ( $users as $user ) {
				$user_meta_line = $user->get( self::META_KEY__LINE );
				if ( $user_meta_line && $user_meta_line[ $secret_prefix ] ) {
					if ( $user_meta_line[ $secret_prefix ]['id'] == $line_id ) {
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
	function redirect_account_link( $user_login, $current_user ) {
		// error_log("logged in: " . $user_login . " ID:" . $current_user->ID . "\n", 3, __DIR__ . '/log/loggin.log');
		if ( isset( $_COOKIE['line_connect_redirect_to'] ) && get_current_user_id() ) { // COOKIEにリダイレクト先がセットされており、ログイン済みなら
			$redirect_to = $_COOKIE['line_connect_redirect_to']; // COKIEからリダイレクト先を取得
			setcookie( 'line_connect_redirect_to', '', time() - 3600 ); // COKIE削除
			wp_safe_redirect( $redirect_to, 303 ); // セーフリダイレクト
			exit();
		}
	}

	/**
	 * 登録されているオプション情報を全て返す
	 */
	static function get_all_options() {
		$options = get_option( self::OPTION_KEY__SETTINGS ); // オプションを取得
		foreach ( lineconnectConst::$settings_option as $tab_name => $tab_details ) {
			// flatten
			foreach ( $tab_details['fields'] as $option_key => $option_details ) {
				if ( ! isset( $options[ $option_key ] ) ) {
					$options[ $option_key ] = $option_details['default'];
				}
			}
		}
		return $options;
	}

	/**
	 * 登録されているオプションの値を返す
	 */
	static function get_option( $option_name ) {
		$options = get_option( self::OPTION_KEY__SETTINGS ); // オプションを取得
		if ( isset( $options[ $option_name ] ) ) {
			return $options[ $option_name ];
		}
		foreach ( lineconnectConst::$settings_option as $tab_name => $tab_details ) {
			// flatten
			foreach ( $tab_details['fields'] as $option_key => $option_details ) {
				if ( $option_name == $option_key ) {
					return $option_details['default'];
				}
			}
		}
		return null;
	}

	/** 
	 * 登録されている設定値を返す
	 * 
	 */
	static function get_variable( $variable_name, $default_value ) {
		$variables = get_option( self::OPTION_KEY__VARIABLES ); // オプションを取得
		if ( isset( $variables[ $variable_name ] ) ) {
			return $variables[ $variable_name ];
		}
		return $default_value;
	}

	/** 
	 * 設定値を保存
	 * 
	 */
	static function set_variable( $variable_name, $value ) {
		$variables = get_option( self::OPTION_KEY__VARIABLES ); // オプションを取得
		$variables[ $variable_name ] = $value;
		update_option( self::OPTION_KEY__VARIABLES, $variables );
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
		$prefix = rest_get_url_prefix();
		if (
			defined( 'REST_REQUEST' ) && REST_REQUEST // (#1)
			|| isset( $_GET['rest_route'] ) // (#2)
			&& strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 ) === 0
		) {
			return true;
		}
		// (#3)
		global $wp_rewrite;
		if ( $wp_rewrite === null ) {
			$wp_rewrite = new WP_Rewrite();
		}

		// (#4)
		$rest_url    = wp_parse_url( trailingslashit( rest_url() ) );
		$current_url = wp_parse_url( add_query_arg( array() ) );
		return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}

	function register_stream_connector() {
		add_filter(
			'wp_stream_connectors',
			function ( $classes ) {
				require_once plugin_dir_path( __FILE__ ) . 'include/logging.php';
				$class     = new lineconnectConnector();
				$classes[] = $class;
				return $classes;
			}
		);
	}

	function set_page_gptlog() {
		$page_hook_suffix = add_menu_page(
			// ページタイトル：
			__( 'LINE GPT Log', self::PLUGIN_NAME ),
			// メニュータイトル：
			__( 'LINE GPT Log', self::PLUGIN_NAME ),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			self::SLUG__LINE_GPTLOG,
			// メニューに紐づく画面を描画するcallback関数：
			array( $this, 'show_gpt_log' ),
			'dashicons-testimonial'
		);

		if ( isset( $_GET['page'] ) && self::SLUG__LINE_GPTLOG === $_GET['page'] ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_settings_page_gptlog' ) );
		}
	}

	function load_settings_page_gptlog() {
		require_once plugin_dir_path( __FILE__ ) . 'include/chatlist.php';
		$this->wp_gptlog_list_table = new lineconnectGptLogListTable();
		$action                     = $this->wp_gptlog_list_table->current_action();
		if ( ! empty( $action ) ) {
			if ( $action == 'delete' ) {
				$this->wp_gptlog_list_table->delete_items();
			}
		}
		$gptlog_css = 'assets/gptlog-style.css';
		wp_enqueue_style( self::PLUGIN_PREFIX . 'gptlog-css', plugins_url( $gptlog_css, __FILE__ ), array(), filemtime( plugin_dir_path( __FILE__ ) . $gptlog_css ) );
	}

	function show_gpt_log() {
		// error_log(print_r($this->wp_gptlog_list_table, true));
		// $this->wp_gptlog_list_table->prepare_items();
		$this->wp_gptlog_list_table->show_list();
	}

	function pluginActivation() {
		// プラグイン有効化時
		// $current_db_version = self::get_variable( 'db_version', '1.0' );
		self::delta_database();
		
	}

	static function delta_database(){
		// テーブル作成
		global $wpdb;

		$table_name      = $wpdb->prefix . lineconnectConst::TABLE_BOT_LOGS;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			event_id varchar(32) NOT NULL,
			event_type tinyint NOT NULL,
			source_type tinyint NOT NULL,
			user_id varchar(255) NOT NULL,
			bot_id varchar(255) NOT NULL,
			message_type tinyint NOT NULL,
			message json,
			timestamp datetime(3) NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY event_id (event_id)
		) $charset_collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		self::set_variable( lineconnectConst::DB_VERSION_KEY, self::DB_VERSION);
	}
} // end of class

$GLOBALS['lineconnect'] = new lineconnect();
