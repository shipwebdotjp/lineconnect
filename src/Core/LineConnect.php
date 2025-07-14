<?php

namespace Shipweb\LineConnect\Core;

use Shipweb\LineConnect\Scenario\Scenario;
use Shipweb\LineConnect\Scenario\Admin as ScenarioAdmin;
use Shipweb\LineConnect\ActionFlow\ActionFlow;
use Shipweb\LineConnect\ActionFlow\Admin as ActionFlowAdmin;
use Shipweb\LineConnect\ActionExecute\Admin as ActionExecuteAdmin;
use Shipweb\LineConnect\Dashboard\Screen as DashboardScreen;
use Shipweb\LineConnect\Admin\UserColumnCustomizer as UserColumnCustomizer;
use Shipweb\LineConnect\Action\Action;
use Shipweb\LineConnect\Bot\Log\ListTable as BotLogListTable;
use Shipweb\LineConnect\Publish\Comment as PublishComment;
use Shipweb\LineConnect\Publish\Post as PublishPost;
use Shipweb\LineConnect\Shortcodes\Shortcodes;
use Shipweb\LineConnect\Utilities\StreamConnector;
use Shipweb\LineConnect\DirectMessage\Screen as DirectMessageScreen;
use Shipweb\LineConnect\Core\Cron;
use Shipweb\LineConnect\PostType\Trigger\Trigger as TriggerPostType;
use Shipweb\LineConnect\PostType\Trigger\Screen as TriggerScreen;
use Shipweb\LineConnect\PostType\Audience\Screen as AudienceScreen;
use Shipweb\LineConnect\PostType\Audience\Column as AudienceColumn;
use Shipweb\LineConnect\PostType\Audience\Audience as AudiencePostType;
use Shipweb\LineConnect\BulkMessage\Screen as BulkMessageScreen;
use Shipweb\LineConnect\PostType\Message\Screen as MessageScreen;
use Shipweb\LineConnect\PostType\Message\Message as MessagePostType;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\RichMenu\RichMenu;
use \Shipweb\LineConnect\Admin\Setting\Setting as SettingScreen;
use Shipweb\LineConnect\Admin\Setting\Constants as SettingConstants;
use Shipweb\LineConnect\Chat\API\FetchUsers;
use Shipweb\LineConnect\Chat\API\FetchMessages;
use Shipweb\LineConnect\Chat\Screen as ChatScreen;
use Shipweb\LineConnect\Admin\ContentDownload;

class LineConnect {

	private static $instance;

	/**
	 * このプラグインのバージョン
	 */
	const VERSION = '4.2.0';

	/**
	 * このプラグインのデータベースバージョン
	 */
	const DB_VERSION = '1.5';
	// 1.5: lineconnect_bot_logsテーブルのstatus,errorカラムの追加、インデックスの変更
	// 1.3: line_user_idテーブルにinteractions, scenario, statsカラムの追加

	/**
	 * DBバージョンのキー
	 */
	const DB_VERSION_KEY = 'db_version';

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
	 * CredentialAction：Action
	 */
	const CREDENTIAL_ACTION__ACTION = self::PLUGIN_ID . '-nonce-action_action';

	/**
	 * CredentialAction：Trigger
	 */
	// const CREDENTIAL_ACTION__TRIGGER = self::PLUGIN_ID . '-nonce-action_trigger';

	/**
	 * CredentialAction：Audience
	 */
	// const CREDENTIAL_ACTION__AUDIENCE = self::PLUGIN_ID . '-nonce-action_audience';

	/**
	 * CredentialAction：Message
	 */
	// const CREDENTIAL_ACTION__MESSAGE = self::PLUGIN_ID . '-nonce-action_message';

	/**
	 * CredentialName：設定
	 */
	const CREDENTIAL_NAME__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-name_settings-form';

	/**
	 * CredentialName：投稿
	 */
	const CREDENTIAL_NAME__POST = self::PLUGIN_ID . '-nonce-name_post';

	/**
	 * CredentialName：アクション
	 */
	const CREDENTIAL_NAME__ACTION = self::PLUGIN_ID . '-nonce-name_action';

	/**
	 * CredentialName：Trigger
	 */
	// const CREDENTIAL_NAME__TRIGGER = self::PLUGIN_ID . '-nonce-name_trigger';

	/**
	 * CredentialName：Audience
	 */
	// const CREDENTIAL_NAME__AUDIENCE = self::PLUGIN_ID . '-nonce-name_audience';

	/**
	 * CredentialName：Message
	 */
	// const CREDENTIAL_NAME__MESSAGE = self::PLUGIN_ID . '-nonce-name_message';

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
	 * パラメータ名：LINE ACTION データ
	 */
	// const PARAMETER__ACTION_DATA = self::PLUGIN_PREFIX . 'action-data';

	/**
	 * パラメータ名：LINE TRIGGER データ
	 */
	// const PARAMETER__TRIGGER_DATA = self::PLUGIN_PREFIX . 'trigger-data';

	/**
	 * パラメータ名：LINE AUDIENCE データ
	 */
	// const PARAMETER__AUDIENCE_DATA = self::PLUGIN_PREFIX . 'audience-data';

	/**
	 * パラメータ名：LINE MESSAGE データ
	 */
	// const PARAMETER__MESSAGE_DATA = self::PLUGIN_PREFIX . 'message-data';

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
	 * TRANSIENTキーリッチメニューリスト
	 */
	const TRANSIENT_KEY__RICHMENUS_LIST = self::PLUGIN_PREFIX . 'richmenus-list';

	/**
	 * TRANSIENTキーリッチメニューエイリアスリスト
	 */
	const TRANSIENT_KEY__RICHMENU_ALIAS_LIST = self::PLUGIN_PREFIX . 'richmenus-alias-list';

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
	const SLUG__BULKMESSAGE_FORM = self::PLUGIN_ID . '-linechat-form';

	/**
	 * 画面のslug：リッチメニュー
	 */
	const SLUG__RICHMENU_ADD = self::PLUGIN_ID . '-richmenu';

	/**
	 * 画面のslug：LINE GPT Log
	 */
	const SLUG__LINE_GPTLOG = self::PLUGIN_ID . '-linegpt-log';

	/**
	 * 画面のslug：LINE ID List
	 */
	const SLUG__LINE_ID_LIST = self::PLUGIN_ID . '-lineid-list';

	/**
	 * 画面のslug：オーディエンスダウンロード
	 */
	const SLUG__AUDIENCE_DOWNLOAD = self::PLUGIN_ID . '-audience-download';

	/**
	 * 画面のslug：コンテンツダウンロード
	 */
	const SLUG__CONTENT_DOWNLOAD = self::PLUGIN_ID . '-content-download';

	/**
	 * 画面のslug：LINE Dashboard
	 */
	const SLUG__DASHBOARD = self::PLUGIN_ID . '-dashboard';

	/**
	 * 画面のslug：DM(LINE)
	 */
	const SLUG__DM_FORM = self::PLUGIN_ID . '-linedm-form';

	/**
	 * 画面のslug：Chat(LINE)
	 */
	const SLUG__CHAT_SCREEN = self::PLUGIN_ID . '-chat';

	/**
	 * 投稿メタキー：is-send-line
	 */
	const META_KEY__IS_SEND_LINE = 'is-send-line';

	/**
	 * 投稿メタキー：action-data
	 */
	// const META_KEY__ACTION_DATA = 'action-data';

	/**
	 * 投稿メタキー：trigger-data
	 */
	// const META_KEY__TRIGGER_DATA = 'trigger-data';

	/**
	 * 投稿メタキー：audience-data
	 */
	// const META_KEY__AUDIENCE_DATA = 'audience-data';

	/**
	 * 投稿メタキー：message-data
	 */
	// const META_KEY__MESSAGE_DATA = 'message-data';

	/**
	 * 投稿メタキー：schema-version
	 */
	const META_KEY__SCHEMA_VERSION = 'data-schema-version';

	/**
	 * Filter hook prefix
	 */
	const FILTER_PREFIX = self::PLUGIN_ID . '_filter_';

	/**
	 * Cronイベント名：
	 */
	const CRON_EVENT_NAME = self::PLUGIN_NAME . '_schedule_event';

	/**
	 * Cron前回実行日時オプション名：
	 */
	const CRON_EVENT_LAST_TIMESTAMP = self::PLUGIN_NAME . '_cron_last_timestamp';

	/**
	 * ボットとのチャットログ MySQLテーブル名
	 */
	const TABLE_BOT_LOGS = 'lineconnect_bot_logs';

	/**
	 * LINEユーザー情報 MySQLテーブル名
	 */
	const TABLE_LINE_ID = 'lineconnect_line_id';

	/**
	 * LINE公式アカウント統計ログ MySQLテーブル名
	 */
	const TABLE_LINE_STATS = 'lineconnect_line_stats';

	/**
	 * LINE公式アカウント日々の増減数ログ MySQLテーブル名
	 */
	const TABLE_LINE_DAILY = 'lineconnect_line_daily';

	/**
	 * メッセージ MySQLテーブル名
	 */
	// const TABLE_MESSAGES = 'lineconnect_messages';

	/**
	 * チャットログリスト表示用クラス
	 */
	var $wp_gptlog_list_table;

	/**
	 * LINE IDリスト表示用クラス
	 */
	var $lineid_list_table;

	/**
	 * プラグインのルートディレクトリ
	 */
	var $root_dir;

	/**
	 * プラグインのルートURL
	 */
	var $root_url;

	/**
	 * プラグインのメインファイル名
	 */
	const PLUGIN_ENTRY_FILE_NAME = 'lineconnect.php';

	/**
	 * 変数項目
	 */
	public static array $variables_option;

	public static function get_instance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// プラグインディレクトリの相対パス(plugins/以下)　例) lineconnect/
		$plugin_rel_path = trailingslashit(dirname(plugin_basename(__FILE__), substr_count(plugin_basename(__FILE__), '/')));
		// プラグインディレクトリの絶対パス
		$this->root_dir = trailingslashit(dirname(__FILE__, substr_count(plugin_basename(__FILE__), '/')));
		$this->root_url = plugins_url('', $this->root_dir . self::PLUGIN_ENTRY_FILE_NAME);
		// ログ記録のためのコネクタ呼び出し
		add_action('plugins_loaded', array($this, 'register_stream_connector'), 99, 1);
		add_action('init', array($this, 'init'));


		//プラグイン無効化時に呼び出し
		register_deactivation_hook($this->root_dir . self::PLUGIN_ENTRY_FILE_NAME, [$this, 'pluginDeactivation']);

		// テキストドメイン呼出し
		load_plugin_textdomain(self::PLUGIN_NAME, false, $plugin_rel_path . 'languages');
	}




	public function init() {
		global $post_type, $pagenow;

		// lineconnectConst class initialize
		// lineconnectConst::initialize();

		self::$variables_option = array(
			'plugin_version' => lineconnect::VERSION,
			'db_version'     => array(
				'initial' => '1.0',
				'default' => lineconnect::DB_VERSION,
			),
		);

		$post_types = self::get_option('send_post_types');
		foreach ($post_types as $post_type) {
			add_action('publish_' . $post_type, array(PublishPost::class, 'publish_post'), 15, 6);
		}
		add_action('save_post', array(PublishPost::class, 'save_post'), 10, 2);
		if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
			// 投稿(公開)した際にLINE送信に失敗した時のメッセージ表示
			add_action('admin_notices', array(PublishPost::class, 'error_send_to_line'));
			// 投稿(公開)した際にLINE送信に成功した時のメッセージ表示
			add_action('admin_notices', array(PublishPost::class, 'success_send_to_line'));
		}
		// 投稿画面にチェックボックスを表示
		add_action('add_meta_boxes', array(PublishPost::class, 'add_send_checkbox'), 10, 2);
		// 投稿画面のとき、スクリプトを読み込む
		add_action('admin_enqueue_scripts', array(PublishPost::class, 'wpdocs_selectively_enqueue_admin_script'));

		// Action関係
		// add_action( 'save_post_' . lineconnectConst::POST_TYPE_ACTION, array( 'Action', 'save_post_action' ), 15, 6 );
		// add_action( 'admin_enqueue_scripts', array( 'Action', 'wpdocs_selectively_enqueue_admin_script' ) );

		// Trigger関係
		add_action('save_post_' . TriggerPostType::POST_TYPE, array(TriggerScreen::class, 'save_post_trigger'), 15, 6);
		add_action('admin_enqueue_scripts', array(TriggerScreen::class, 'wpdocs_selectively_enqueue_admin_script'));

		// Audience関係
		add_action('save_post_' . AudiencePostType::POST_TYPE, array(AudienceScreen::class, 'save_post_audience'), 15, 6);
		add_action('admin_enqueue_scripts', array(AudienceScreen::class, 'wpdocs_selectively_enqueue_admin_script'));

		// message
		add_action('save_post_' . MessagePostType::POST_TYPE, array(MessageScreen::class, 'save_post_message'), 15, 6);
		add_action('admin_enqueue_scripts', array(MessageScreen::class, 'wpdocs_selectively_enqueue_admin_script'));

		// Scenario
		add_action('save_post_' . Scenario::POST_TYPE, array(ScenarioAdmin::class, 'save_post'), 15, 6);
		add_action('admin_enqueue_scripts', array(ScenarioAdmin::class, 'wpdocs_selectively_enqueue_admin_script'));

		// ActionFlow
		add_action('save_post_' . ActionFlow::POST_TYPE, array(ActionFlowAdmin::class, 'save_post'), 15, 6);
		add_action('admin_enqueue_scripts', array(ActionFlowAdmin::class, 'wpdocs_selectively_enqueue_admin_script'));

		// 管理画面を表示中、且つ、ログイン済、且つ、特権管理者or管理者の場合
		if (is_admin() && is_user_logged_in() && (is_super_admin() || current_user_can('administrator'))) {
			//メニュー追加
			// LINE Connect ダッシュボードページ(親となるページ)を追加
			add_action('admin_menu', array(DashboardScreen::class, 'set_plugin_menu'));
			// 一括配信のメニューページを追加
			add_action('admin_menu', array(BulkMessageScreen::class, 'set_plugin_menu'));
			// ダイレクトメッセージのメニューを追加
			add_action('admin_menu', array(DirectMessageScreen::class, 'set_plugin_menu'));
			// チャットのメニューを追加
			add_action('admin_menu', array(ChatScreen::class, 'set_plugin_menu'));
			// アクション実行のトップページメニューを追加
			add_action('admin_menu', array(\Shipweb\LineConnect\ActionExecute\Admin::class, 'set_plugin_menu'));
			// LCメッセージのメニューを追加
			add_action('admin_menu', array(MessageScreen::class, 'set_plugin_menu'));
			// オーディエンスのメニューを追加
			add_action('admin_menu', array(AudienceScreen::class, 'set_plugin_menu'));
			// アクションフローのメニューを追加
			add_action('admin_menu', array(\Shipweb\LineConnect\ActionFlow\Admin::class, 'set_plugin_menu'));
			// トリガーのメニューを追加
			add_action('admin_menu', array(TriggerScreen::class, 'set_plugin_menu'));
			// シナリオのメニューを追加
			add_action('admin_menu', array(\Shipweb\LineConnect\Scenario\Admin::class, 'set_plugin_menu'));
			// リッチメニューのメニューを追加
			add_action('admin_menu', array(RichMenu::class, 'set_plugin_menu'));
			// LINE IDリストのメニューを追加
			add_action('admin_menu', array($this, 'set_page_lineid'));
			// BOT LOGリストのメニューを追加
			add_action('admin_menu', array($this, 'set_page_gptlog'));
			// 設定画面のメニューを追加
			add_action('admin_menu', array(SettingScreen::class, 'set_plugin_menu'));
			// オーディエンスダウンロードメニューページを追加
			add_action('admin_menu', array(AudienceColumn::class, 'set_download_menu'));
			// コンテンツダウンロードメニューページを追加
			add_action('admin_menu', array(ContentDownload::class, 'set_download_menu'));

			// 管理画面各ページの最初、ページがレンダリングされる前に実行するアクションに、
			// 初期設定を保存する関数をフック
			add_action('admin_init', array(SettingScreen::class, 'save_settings'));

			//カラム追加
			// ユーザー一覧一覧のコラム追加
			add_filter('manage_users_columns', array(UserColumnCustomizer::class, 'lc_manage_columns'));
			// ユーザー一覧に追加したカスタムコラムの表示を行うフィルター
			add_filter('manage_users_custom_column', array(UserColumnCustomizer::class, 'lc_manage_custom_columns'), 10, 3);
			// ユーザー一覧の一括操作にメッセージ送信を追加
			add_filter('bulk_actions-users', array(UserColumnCustomizer::class, 'add_bulk_users_sendmessage'), 10, 1);
			// 一括操作を行うフィルター
			add_filter('handle_bulk_actions-users', array(UserColumnCustomizer::class, 'handle_bulk_users_sendmessage'), 10, 3);
			//シナリオにカラム追加
			add_filter('manage_' . Scenario::POST_TYPE . '_posts_columns', array(ScenarioAdmin::class, 'add_columns'));
			add_action('manage_' . Scenario::POST_TYPE . '_posts_custom_column', array(ScenarioAdmin::class, 'add_columns_content'), 10, 2);
			//オーディエンスにダウンロードカラム追加
			add_filter('manage_' . AudiencePostType::POST_TYPE . '_posts_columns', array(AudienceColumn::class, 'add_download_column'));
			add_action('manage_' . AudiencePostType::POST_TYPE . '_posts_custom_column', array(AudienceColumn::class, 'add_download_column_content'), 10, 2);

			// AJAXアクション
			// 一括配信AJAXアクション
			add_action('wp_ajax_lc_ajax_chat_send', array(BulkMessageScreen::class, 'ajax_chat_send'));
			// LCメッセージデータ取得AJAXアクション
			add_action('wp_ajax_lc_ajax_get_slc_message', array(MessageScreen::class, 'ajax_get_slc_message'));
			// オーディエンスデータ取得AJAXアクション
			add_action('wp_ajax_lc_ajax_get_slc_audience', array(AudienceScreen::class, 'ajax_get_slc_audience'));
			// アクションフローデータ取得AJAXアクション
			add_action('wp_ajax_lc_ajax_get_slc_actionflow', array(\Shipweb\LineConnect\ActionFlow\ActionFlow::class, 'ajax_get_actionflow'));
			// アクション実行AJAXアクション
			add_action('wp_ajax_lc_ajax_action_execute', array(\Shipweb\LineConnect\ActionExecute\Admin::class, 'ajax_action_execute'));
			// ダッシュボードデータ取得AJAXアクション
			add_action('wp_ajax_lc_ajax_get_dashboard', array(DashboardScreen::class, 'ajax_get_dashboard'));
			// DM送信アクション
			add_action('wp_ajax_lc_ajax_dm_send', array(DirectMessageScreen::class, 'ajax_dm_send'));
			// リッチメニュー一覧取得AJAXアクション
			add_action('wp_ajax_lc_ajax_get_richmenus', array(RichMenu::class, 'ajax_get_richmenus'));
			// リッチメニュー取得AJAXアクション
			add_action('wp_ajax_lc_ajax_get_richmenu', array(RichMenu::class, 'ajax_get_richmenu'));
			// リッチメニュー削除AJAXアクション
			add_action('wp_ajax_lc_ajax_delete_richmenu', array(RichMenu::class, 'ajax_delete_richmenu'));
			// リッチメニュー作成AJAXアクション
			add_action('wp_ajax_lc_ajax_create_richmenu', array(RichMenu::class, 'ajax_create_richmenu'));
			// リッチメニューエイリアス一覧取得AJAXアクション
			add_action('wp_ajax_lc_ajax_get_richmenus_alias', array(RichMenu::class, 'ajax_get_richmenus_alias'));
			// リッチメニューエイリアス削除AJAXアクション
			add_action('wp_ajax_lc_ajax_delete_richmenu_alias', array(RichMenu::class, 'ajax_delete_richmenu_alias'));
			// リッチメニューエイリアス作成AJAXアクション
			add_action('wp_ajax_lc_ajax_create_richmenu_alias', array(RichMenu::class, 'ajax_create_richmenu_alias'));
			// リッチメニューエイリアス更新AJAXアクション
			add_action('wp_ajax_lc_ajax_update_richmenu_alias', array(RichMenu::class, 'ajax_update_richmenu_alias'));

			// ユーザー一覧取得AJAXアクション
			add_action('wp_ajax_slc_fetch_users', array(FetchUsers::class, 'ajax_fetch_users'));
			// メッセージ一覧取得AJAXアクション
			add_action('wp_ajax_slc_fetch_messages', array(FetchMessages::class, 'execute'));
		}
		// ログイン時、LINEアカウント連携の場合リダイレクトさせる
		add_action('wp_login', array($this, 'redirect_account_link'), 10, 2);

		// ユーザーのロール変更時にリッチメニューを変更する
		add_action('set_user_role', array(RichMenu::class, 'change_user_role'), 10, 3);

		// ユーザーにリッチメニューを関連付ける
		add_action('line_link_richmenu', array(RichMenu::class, 'link_richmenu'), 10, 1);

		// ユーザーからリッチメニューを削除する
		add_action('line_unlink_richmenu', array(RichMenu::class, 'line_unlink_richmenu'), 10, 2);

		// 特定ロールの連携済みユーザーへメッセージを送信
		add_action('send_message_to_role', array(Builder::class, 'sendMessageRole'), 10, 3);

		// 特定の連携済みユーザーへメッセージを送信
		add_action('send_message_to_wpuser', array(Builder::class, 'sendMessageWpUser'), 10, 3);

		if (self::get_option('send_new_comment')) {
			// コメントが投稿されたときのアクション
			add_action('comment_post', array(PublishComment::class, 'comment_post_callback'), 10, 2);

			// コメントステータスが変化したときのアクション
			add_action('transition_comment_status', array(PublishComment::class, 'approve_comment_callback'), 10, 3);
		}

		// ChatGPTとの会話ログ表示ショートコード
		add_shortcode('line_connect_chat_gpt_log', array(Shortcodes::class, 'show_chat_log'));

		// ショートコード用のCSSを読み込むため
		add_action('wp_enqueue_scripts', array(Shortcodes::class, 'enqueue_script'));

		// カスタム投稿タイプの登録
		$this->register_custom_post_type();

		// add shedule interval filter
		add_filter('cron_schedules', array($this, 'add_cron_interval'));

		// add shedule action
		add_action(self::CRON_EVENT_NAME, [Cron::class, 'schedule_event']);

		//set cron
		self::cron_initialaize();
	}

	/*
	ログイン時にLINE連携から飛んできた場合は連携用のページへリダイレクトさせる
	*/
	function redirect_account_link($user_login, $current_user) {
		// error_log("logged in: " . $user_login . " ID:" . $current_user->ID . "\n", 3, __DIR__ . '/log/loggin.log');
		if (isset($_COOKIE['line_connect_redirect_to']) && get_current_user_id()) { // COOKIEにリダイレクト先がセットされており、ログイン済みなら
			$redirect_to = $_COOKIE['line_connect_redirect_to']; // COKIEからリダイレクト先を取得
			setcookie('line_connect_redirect_to', '', time() - 3600); // COKIE削除
			wp_safe_redirect($redirect_to, 303); // セーフリダイレクト
			exit();
		}
	}

	function register_stream_connector() {
		add_filter(
			'wp_stream_connectors',
			function ($classes) {
				if (class_exists('WP_Stream\Connector')) {
					$class     = new StreamConnector();
					$classes[] = $class;
				}
				return $classes;
			}
		);
	}

	function set_page_lineid() {
		$page_hook_suffix = add_submenu_page(
			self::SLUG__DASHBOARD,
			__('LINE ID List', self::PLUGIN_NAME),
			__('LINE ID List', self::PLUGIN_NAME),
			'manage_options',
			self::SLUG__LINE_ID_LIST,
			array($this, 'show_lineid_list'),
			NULL
		);

		if (isset($_GET['page']) && self::SLUG__LINE_ID_LIST === $_GET['page']) {
			add_action('admin_enqueue_scripts', array($this, 'load_settings_page_lineid'));
		}
	}

	function load_settings_page_lineid() {
		require_once $this->root_dir . 'src/ListTable/LineId.php';
		$this->lineid_list_table = new \Shipweb\LineConnect\ListTable\LineId();
		$jsfile = 'assets/js/clipboard.js';
		wp_enqueue_script(self::PLUGIN_PREFIX . 'clipboard-js', plugins_url($jsfile, $this->root_dir . self::PLUGIN_ENTRY_FILE_NAME), array(), filemtime($this->root_dir . $jsfile), true);
	}

	function show_lineid_list() {
		$this->lineid_list_table->show_list();
	}

	function set_page_gptlog() {
		$page_hook_suffix = add_submenu_page(
			// 親ページ：
			self::SLUG__DASHBOARD,
			// ページタイトル：
			__('LINE Event Log', self::PLUGIN_NAME),
			// メニュータイトル：
			__('Event Log', self::PLUGIN_NAME),
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
			array($this, 'show_gpt_log'),
			// メニューの位置
			NULL
		);

		if (isset($_GET['page']) && self::SLUG__LINE_GPTLOG === $_GET['page']) {
			add_action('admin_enqueue_scripts', array($this, 'load_settings_page_gptlog'));
		}
	}

	function load_settings_page_gptlog() {
		$this->wp_gptlog_list_table = new BotLogListTable();
		$action                     = $this->wp_gptlog_list_table->current_action();
		if (! empty($action)) {
			if ($action == 'delete') {
				$this->wp_gptlog_list_table->delete_items();
			}
		}
		$gptlog_css = 'assets/gptlog-style.css';
		wp_enqueue_style(self::PLUGIN_PREFIX . 'gptlog-css', plugins_url($gptlog_css, $this->root_dir . self::PLUGIN_ENTRY_FILE_NAME), array(), filemtime($this->root_dir . $gptlog_css));
	}

	function show_gpt_log() {
		// error_log(print_r($this->wp_gptlog_list_table, true));
		// $this->wp_gptlog_list_table->prepare_items();
		$this->wp_gptlog_list_table->show_list();
	}

	static function pluginActivation() {
		// プラグイン有効化時
		// $current_db_version = self::get_variable( 'db_version', '1.0' );
		self::delta_database();
		// error_log("activate");

	}

	function pluginDeactivation() {
		$timestamp = wp_next_scheduled(self::CRON_EVENT_NAME);
		if (isset($timestamp)) {
			wp_unschedule_event($timestamp, self::CRON_EVENT_NAME);
		}
		// error_log("deactivate".$timestamp);
	}

	function add_cron_interval($schedules) {
		$schedules['minly'] = array(
			'interval' => 60,
			'display' => 'every minutes'
		);
		return $schedules;
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
	 *
	 * @param message 通知するメッセージ
	 * @param type 通知タイプ(error/warning/success/info)
	 * @retern 通知タグ(HTML)
	 */
	static function getNotice($message, $type) {
		return '<div class="notice notice-' . $type . ' is-dismissible">' .
			'<p><strong>' . wp_kses_post($message) . '</strong></p>' .
			'<button type="button" class="notice-dismiss">' .
			'<span class="screen-reader-text">Dismiss this notice.</span>' .
			'</button>' .
			'</div>';
	}

	static function getErrorBar($message, $type) {
		return '<div class="error">' . wp_kses_post($message) . '</div>';
	}

	/**
	 * 登録されているチャネル情報を返す
	 */
	static function get_all_channels() {
		$channels = get_option(self::OPTION_KEY__CHANNELS, array()); // チャネル情報を取得
		return $channels;
	}

	/**
	 * チャネルシークレット先頭4文字(またはチャンネル番号)から登録されているチャネル情報を返す
	 */
	static function get_channel($channel_prefix) {
		foreach (self::get_all_channels() as $channel_id => $channel) {
			if ($channel_prefix === $channel_id || $channel_prefix === $channel['prefix']) {
				return $channel;
			}
		}
		return null;
	}

	/**
	 * LINE IDからWPUserを返す
	 */
	static function get_wpuser_from_line_id($secret_prefix, $line_id) {
		$args       = array(
			'meta_query' => array(
				array(
					'key'     => self::META_KEY__LINE,
					'compare' => 'EXISTS',
				),
			),
			'fields'     => 'all_with_meta',
		);
		$user_query = new \WP_User_Query($args);
		$users      = $user_query->get_results(); // クエリ実行
		if (! empty($users)) {   // マッチするユーザーが見つかれば
			// ユーザーのメタデータを取得
			foreach ($users as $user) {
				$user_meta_line = $user->get(self::META_KEY__LINE);
				if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
					if ($user_meta_line[$secret_prefix]['id'] == $line_id) {
						return $user;
					}
				}
			}
		}
		return false;
	}

	/**
	 * LINE IDからプロフィール情報を取得
	 *
	 * @param string  $secret_prefix チャンネルID
	 * @param string  $line_id LINE ID
	 * @return array 
	 */
	static function get_userdata_from_line_id($secret_prefix, $line_id) {
		$user_data = array();
		$line_id_row  = \Shipweb\LineConnect\Utilities\LineId::line_id_row($line_id, $secret_prefix);
		if ($line_id_row) {
			$user_data['profile']       = json_decode($line_id_row['profile'] ?? '{}', true);
			$user_data['tags']          = json_decode($line_id_row['tags'] ?? '{}', true);
		}
		$wp_user = self::get_wpuser_from_line_id($secret_prefix, $line_id);
		if ($wp_user) {
			$user_data = array_merge($user_data, json_decode(json_encode($wp_user), true));
		} else if ($line_id_row && isset($user_data['profile']['displayName'])) {
			$user_data['data'] = array(
				'display_name' => $user_data['profile']['displayName'],
			);
		}
		return $user_data;
	}

	/**
	 * WordPressユーザーとチャネルからLINEユーザーIDを取得
	 *
	 * @param WP_User $user WordPressユーザー
	 * @param string  $secret_prefix チャネルシークレット先頭4文字
	 * @return string LINEユーザーID
	 */
	public static function get_line_id_from_wpuser($user, $secret_prefix) {
		$line_user_id   = null;
		$user_meta_line = $user->get(self::META_KEY__LINE);
		if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
			if (isset($user_meta_line[$secret_prefix]['id'])) {
				$line_user_id = $user_meta_line[$secret_prefix]['id'];
			}
		}
		return $line_user_id;
	}
	/**
	 * WordPressユーザーからLINEユーザーIDを取得
	 *
	 * @param WP_User $user WordPressユーザー
	 * @return array LINEユーザーID
	 */
	public static function get_line_ids_from_wpuser($user) {
		$line_user_ids  = array();
		$user_meta_line = $user->get(self::META_KEY__LINE);
		if ($user_meta_line && is_array($user_meta_line)) {
			foreach ($user_meta_line as $secret_prefix => $line_data) {
				if (isset($line_data['id'])) {
					$line_user_ids[] = $line_data['id'];
				}
			}
		}
		return $line_user_ids;
	}

	/**
	 * 登録されているオプション情報を全て返す
	 */
	static function get_all_options() {
		$options = get_option(self::OPTION_KEY__SETTINGS); // オプションを取得
		if (! is_array($options)) {
			$options = [];
		}
		foreach (SettingConstants::get_settings_option() as $tab_name => $tab_details) {
			// flatten
			foreach ($tab_details['fields'] as $option_key => $option_details) {
				if (! isset($options[$option_key])) {
					$options[$option_key] = $option_details['default'];
				}
			}
		}
		return $options;
	}

	/**
	 * 登録されているオプションの値を返す
	 */
	static function get_option($option_name) {
		$options = get_option(self::OPTION_KEY__SETTINGS); // オプションを取得
		if (! is_array($options)) {
			$options = [];
		}
		if (isset($options[$option_name])) {
			return $options[$option_name];
		}
		foreach (SettingConstants::get_settings_option() as $tab_name => $tab_details) {
			// flatten
			foreach ($tab_details['fields'] as $option_key => $option_details) {
				if ($option_name == $option_key) {
					return $option_details['default'];
				}
			}
		}
		return null;
	}

	/**
	 * 登録されている設定値を返す
	 */
	static function get_variable($variable_name, $default_value) {
		$variables = get_option(self::OPTION_KEY__VARIABLES); // オプションを取得
		if ($variables === false) {
			$variables = array();
		}
		if (isset($variables[$variable_name])) {
			return $variables[$variable_name];
		}
		return $default_value;
	}

	/**
	 * 設定値を保存
	 */
	static function set_variable($variable_name, $value) {
		$variables                   = get_option(self::OPTION_KEY__VARIABLES); // オプションを取得
		if ($variables === false) {
			$variables = array();
		}
		$variables[$variable_name] = $value;
		update_option(self::OPTION_KEY__VARIABLES, $variables);
	}

	/**
	 * 現在のプラグインバージョンを返す
	 */
	static function get_current_plugin_version() {
		return self::VERSION;
	}

	/**
	 * 現在のデータベースバージョンを返す
	 */
	static function get_current_db_version() {
		return self::get_variable(self::DB_VERSION_KEY, self::$variables_option[self::DB_VERSION_KEY]['initial']);
	}
	/**
	 * WordPress REST APIリクエストかどうかを判断します。
	 *
	 * 以下のケースを検証します：
	 * ケース #1: WP_REST_Request 初期化後のリクエスト
	 * ケース #2: "プレーン" パーマリンク設定のサポート
	 * ケース #3: WP_Rewrite がまだ初期化されていない可能性への対応
	 * ケース #4: URLパスが wp-json/ (REST プレフィックス) で始まる場合
	 *           サブフォルダにインストールされた WordPress も対応
	 *
	 * @return boolean REST APIリクエストの場合はtrue、それ以外はfalse
	 */
	public static function is_rest() {
		$prefix = rest_get_url_prefix();

		// ケース #1: REST_REQUEST 定数が定義され、値がtrueの場合
		if (defined('REST_REQUEST') && REST_REQUEST) {
			return true;
		}

		// ケース #2: GET パラメータに rest_route が存在し、プレフィックスと一致する場合
		if (
			isset($_GET['rest_route'])
			&& strpos(trim($_GET['rest_route'], '\\/'), $prefix, 0) === 0
		) {
			return true;
		}

		// ケース #3: WP_Rewrite の初期化チェックと対応
		global $wp_rewrite;
		if ($wp_rewrite === null) {
			$wp_rewrite = new \WP_Rewrite();
		}

		// ケース #4: 現在のURLパスと REST URLパスの比較
		$rest_url = wp_parse_url(trailingslashit(rest_url()));
		$current_url = wp_parse_url(add_query_arg(array()));

		// パスが存在しない場合のエラーハンドリング
		if (! isset($rest_url['path']) || ! isset($current_url['path'])) {
			return false;
		}

		return strpos($current_url['path'], $rest_url['path'], 0) === 0;
	}

	static function delta_database() {
		// テーブル作成
		global $wpdb;

		$table_name      = $wpdb->prefix . self::TABLE_BOT_LOGS;
		$charset_collate = $wpdb->get_charset_collate();
		/*
		id: UUID v5を使ってRetry Keyとしても使用
		event_id: LINEのWebhookイベントID(INBOUND時) sentMessages.id(OUTBOUND時)
		event_type: LINEのWebhookイベントタイプ(INBOUND時) 送信方法(OUTBOUND時) ブロードキャストなど
		source_type: LINEのWebhookイベントソースタイプ(INBOUND時)(1:user, 2:group, 3:room) 送信元(OUTBOUND時) bot, system, user
		user_id: 送信元LINEユーザーID(INBOUND時) 送信先LINEユーザーID(OUTBOUND時)
		bot_id: 送信元LINE Channel Prefix(INBOUND時) 送信先LINE Channel Prefix(OUTBOUND時)
		message_type: LINEのWebhookイベントメッセージタイプ(INBOUND時) 送信メッセージタイプ(OUTBOUND時) 
		message: LINEのWebhookイベントメッセージ本体(INBOUND時) 送信メッセージ本体(OUTBOUND時)
		timestamp: LINEのWebhookイベントタイムスタンプ(INBOUND時) 送信日時(OUTBOUND時)
		status: ステータス(0:pending, 1:sent, 9:failed)
		error: 送信失敗時のエラーメッセージJSON(OUTBOUND時)
		*/
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
			status tinyint DEFAULT NULL,
			error json DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY idx_bot_user_ts_id_desc (bot_id, user_id, timestamp, id),
			KEY event_id (event_id)
		) $charset_collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);

		$table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
		$sql_line_id        = "CREATE TABLE $table_name_line_id (
			id int(11) NOT NULL AUTO_INCREMENT,
			channel_prefix char(4) NOT NULL,
			line_id char(33) NOT NULL,
			follow tinyint NOT NULL DEFAULT 1,
			profile json,
			tags json,
			interactions json,
			scenarios json,
			stats json,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
            KEY line_id (line_id)
        ) $charset_collate;";
		dbDelta($sql_line_id);

		$table_name_line_stats = $wpdb->prefix . self::TABLE_LINE_STATS;

		$sql_line_stats = "CREATE TABLE $table_name_line_stats (
			id int(11) NOT NULL AUTO_INCREMENT,
			channel_prefix char(4) NOT NULL,
			date date NOT NULL,
			followers int DEFAULT NULL,
			targetedReaches int DEFAULT NULL,
			blocks int DEFAULT NULL,
			recognized int DEFAULT NULL,
			linked int DEFAULT NULL,
			broadcast int DEFAULT NULL,
			targeting int DEFAULT NULL,
			autoResponse int DEFAULT NULL,
			welcomeResponse int DEFAULT NULL,
			chat int DEFAULT NULL,
			apiBroadcast int DEFAULT NULL,
			apiPush int DEFAULT NULL,
			apiMulticast int DEFAULT NULL,
			apiNarrowcast int DEFAULT NULL,
			apiReply int DEFAULT NULL,
			demographic json,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY unique_channel_date (channel_prefix, date)
		) $charset_collate;";
		dbDelta($sql_line_stats);

		$table_name_line_daily = $wpdb->prefix . self::TABLE_LINE_DAILY;
		$sql_line_daily = "CREATE TABLE $table_name_line_daily (
			id int(11) NOT NULL AUTO_INCREMENT,
			channel_prefix char(4) NOT NULL,
			date date NOT NULL,
			follow int DEFAULT 0,
			unfollow int DEFAULT 0,
			link int DEFAULT 0,
			unlink int DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY unique_channel_date (channel_prefix, date)
		) $charset_collate;";
		dbDelta($sql_line_daily);
		/*
		$table_name_messages = $wpdb->prefix . self::TABLE_MESSAGES;
		$sql_messages = "CREATE TABLE $table_name_messages (
			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`request_id` VARCHAR(50) NULL COMMENT 'LINE APIリクエストID (X-Line-Request-Idヘッダーなど)',
			`api_message_id` VARCHAR(50) NULL COMMENT 'LINE APIから返却された個別のメッセージID',
			`direction` TINYINT NOT NULL COMMENT '1: Inbound (User->System), 2: Outbound (System->User)',
			`line_user_id` VARCHAR(33) NOT NULL COMMENT 'LINEユーザーID',
			`channel_prefix` CHAR(4) NOT NULL COMMENT 'チャンネル識別子',
			`source_type` TINYINT NOT NULL COMMENT 'メッセージのソース元 (1:user, 2:group, 3:room, 11:broadcast, 12:multicast, 13:push, 14:reply, 15:narrowcast)',
			`sender_id` VARCHAR(255) NULL COMMENT '送信者ID (Inbound: LINE User ID, Outbound: WP User ID or system)',
			`event_id` VARCHAR(32) NULL COMMENT 'WebhookイベントID (Inbound時)',
			`event_type` TINYINT NULL COMMENT 'Webhookイベントタイプ (Inbound時)',
			`message_type` TINYINT NOT NULL COMMENT 'メッセージタイプ (text, image, etc.)',
			`payload` JSON NOT NULL COMMENT 'メッセージ本体',
			`quote_token` VARCHAR(32) NULL COMMENT '引用返信元のトークン (Inbound時)',
			`status` TINYINT NOT NULL DEFAULT 0 COMMENT 'ステータス (0:pending, 1:sent, 2:delivered, 3:read, 10:scheduled, 99:failed)',
			`error_message` TEXT NULL COMMENT '送信失敗時のエラーメッセージ',
			`created_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '作成日時(ミリ秒含む)',
			`scheduled_at` DATETIME NULL COMMENT '送信予約日時',
			PRIMARY KEY (`id`),
			INDEX `idx_user_time` (`line_user_id`, `created_at`),
			INDEX `idx_request_id` (`request_id`),
			INDEX `idx_api_message_id` (`api_message_id`),
			INDEX `idx_status_scheduled_at` (`status`, `scheduled_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;";
		dbDelta($sql_messages);
*/
		self::set_variable(self::DB_VERSION_KEY, self::DB_VERSION);
	}

	private function register_custom_post_type() {
		// register custom post type: action
		/*
		register_post_type(
			lineconnectConst::POST_TYPE_ACTION,
			array(
				'labels'               => array(
					'name'                     => __( 'LC Actions', self::PLUGIN_NAME ),
					'singular_name'            => __( 'LC Action', self::PLUGIN_NAME ),
					'add_new'                  => __( 'Add New', self::PLUGIN_NAME ),
					'add_new_item'             => __( 'Add New Action', self::PLUGIN_NAME ),
					'edit_item'                => __( 'Edit Action', self::PLUGIN_NAME ),
					'new_item'                 => __( 'New Action', self::PLUGIN_NAME ),
					'view_item'                => __( 'View Action', self::PLUGIN_NAME ),
					'search_items'             => __( 'Search Actions', self::PLUGIN_NAME ),
					'not_found'                => __( 'No Actions found', self::PLUGIN_NAME ),
					'not_found_in_trash'       => __( 'No Actions found in Trash', self::PLUGIN_NAME ),
					'parent_item_colon'        => '',
					'menu_name'                => __( 'LC Actions', self::PLUGIN_NAME ),
					'all_items'                => __( 'All Actions', self::PLUGIN_NAME ),
					'archives'                 => __( 'Action Archives', self::PLUGIN_NAME ),
					'attributes'               => __( 'Action Attributes', self::PLUGIN_NAME ),
					'insert_into_item'         => __( 'Insert into Action', self::PLUGIN_NAME ),
					'uploaded_to_this_item'    => __( 'Uploaded to this Action', self::PLUGIN_NAME ),
					'featured_image'           => __( 'Featured Image', self::PLUGIN_NAME ),
					'set_featured_image'       => __( 'Set featured image', self::PLUGIN_NAME ),
					'remove_featured_image'    => __( 'Remove featured image', self::PLUGIN_NAME ),
					'use_featured_image'       => __( 'Use as featured image', self::PLUGIN_NAME ),
					'filter_items_list'        => __( 'Filter Actions list', self::PLUGIN_NAME ),
					'items_list_navigation'    => __( 'Actions list navigation', self::PLUGIN_NAME ),
					'items_list'               => __( 'Actions list', self::PLUGIN_NAME ),
					'item_published'           => __( 'Action published.', self::PLUGIN_NAME ),
					'item_published_privately' => __( 'Action published privately.', self::PLUGIN_NAME ),
					'item_reverted_to_draft'   => __( 'Action reverted to draft.', self::PLUGIN_NAME ),
					'item_scheduled'           => __( 'Action scheduled.', self::PLUGIN_NAME ),
					'item_updated'             => __( 'Action updated.', self::PLUGIN_NAME ),
				),
				'description'          => __( 'LINE Connect Actions', self::PLUGIN_NAME ),
				'public'               => false,
				'hierarchical'         => false,
				'show_ui'              => true,
				'show_in_menu'         => false, // self::SLUG__DASHBOARD,
				'show_in_rest'         => false,
				'supports'             => array( 'title' ),
				'register_meta_box_cb' => array( 'Action', 'register_meta_box' ),
				'has_archive'          => false,
				'rewrite'              => false,
				'query_var'            => false,
				'menu_position'        => null,
			)
		);
		*/

		// register custom post type: trigger
		register_post_type(
			TriggerPostType::POST_TYPE,
			array(
				'labels'               => array(
					'name'                     => __('LC Triggers', self::PLUGIN_NAME),
					'singular_name'            => __('LC Trigger', self::PLUGIN_NAME),
					'add_new'                  => __('Add New', self::PLUGIN_NAME),
					'add_new_item'             => __('Add New Trigger', self::PLUGIN_NAME),
					'edit_item'                => __('Edit Trigger', self::PLUGIN_NAME),
					'new_item'                 => __('New Trigger', self::PLUGIN_NAME),
					'view_item'                => __('View Trigger', self::PLUGIN_NAME),
					'search_items'             => __('Search Triggers', self::PLUGIN_NAME),
					'not_found'                => __('No Triggers found', self::PLUGIN_NAME),
					'not_found_in_trash'       => __('No Triggers found in Trash', self::PLUGIN_NAME),
					'parent_item_colon'        => '',
					'menu_name'                => __('LC Triggers', self::PLUGIN_NAME),
					'all_items'                => __('All Triggers', self::PLUGIN_NAME),
					'archives'                 => __('Trigger Archives', self::PLUGIN_NAME),
					'attributes'               => __('Trigger Attributes', self::PLUGIN_NAME),
					'insert_into_item'         => __('Insert into Trigger', self::PLUGIN_NAME),
					'uploaded_to_this_item'    => __('Uploaded to this Trigger', self::PLUGIN_NAME),
					'featured_image'           => __('Featured Image', self::PLUGIN_NAME),
					'set_featured_image'       => __('Set featured image', self::PLUGIN_NAME),
					'remove_featured_image'    => __('Remove featured image', self::PLUGIN_NAME),
					'use_featured_image'       => __('Use as featured image', self::PLUGIN_NAME),
					'filter_items_list'        => __('Filter Triggers list', self::PLUGIN_NAME),
					'items_list_navigation'    => __('Triggers list navigation', self::PLUGIN_NAME),
					'items_list'               => __('Triggers list', self::PLUGIN_NAME),
					'item_published'           => __('Trigger published.', self::PLUGIN_NAME),
					'item_published_privately' => __('Trigger published privately.', self::PLUGIN_NAME),
					'item_reverted_to_draft'   => __('Trigger reverted to draft.', self::PLUGIN_NAME),
					'item_scheduled'           => __('Trigger scheduled.', self::PLUGIN_NAME),
					'item_updated'             => __('Trigger updated.', self::PLUGIN_NAME),
				),
				'description'          => __('LINE Connect Triggers', self::PLUGIN_NAME),
				'public'               => false,
				'hierarchical'         => false,
				'show_ui'              => true,
				'show_in_menu'         => false, // self::SLUG__DASHBOARD,
				'show_in_rest'         => false,
				'supports'             => array('title'),
				'register_meta_box_cb' => array(TriggerScreen::class, 'register_meta_box'),
				'has_archive'          => false,
				'rewrite'              => false,
				'query_var'            => false,
				'menu_position'        => null,
			)
		);

		// register custom post type: Message
		register_post_type(
			MessagePostType::POST_TYPE,
			array(
				'labels'               => array(
					'name'                     => __('LC Messages', self::PLUGIN_NAME),
					'singular_name'            => __('LC Message', self::PLUGIN_NAME),
					'add_new'                  => __('Add New', self::PLUGIN_NAME),
					'add_new_item'             => __('Add New Message', self::PLUGIN_NAME),
					'edit_item'                => __('Edit Message', self::PLUGIN_NAME),
					'new_item'                 => __('New Message', self::PLUGIN_NAME),
					'view_item'                => __('View Message', self::PLUGIN_NAME),
					'search_items'             => __('Search Messages', self::PLUGIN_NAME),
					'not_found'                => __('No Messages found', self::PLUGIN_NAME),
					'not_found_in_trash'       => __('No Messages found in Trash', self::PLUGIN_NAME),
					'parent_item_colon'        => '',
					'menu_name'                => __('LC Messages', self::PLUGIN_NAME),
					'all_items'                => __('All Messages', self::PLUGIN_NAME),
					'archives'                 => __('Message Archives', self::PLUGIN_NAME),
					'attributes'               => __('Message Attributes', self::PLUGIN_NAME),
					'insert_into_item'         => __('Insert into Message', self::PLUGIN_NAME),
					'uploaded_to_this_item'    => __('Uploaded to this Message', self::PLUGIN_NAME),
					'featured_image'           => __('Featured Image', self::PLUGIN_NAME),
					'set_featured_image'       => __('Set featured image', self::PLUGIN_NAME),
					'remove_featured_image'    => __('Remove featured image', self::PLUGIN_NAME),
					'use_featured_image'       => __('Use as featured image', self::PLUGIN_NAME),
					'filter_items_list'        => __('Filter Messages list', self::PLUGIN_NAME),
					'items_list_navigation'    => __('Messages list navigation', self::PLUGIN_NAME),
					'items_list'               => __('Messages list', self::PLUGIN_NAME),
					'item_published'           => __('Message published.', self::PLUGIN_NAME),
					'item_published_privately' => __('Message published privately.', self::PLUGIN_NAME),
					'item_reverted_to_draft'   => __('Message reverted to draft.', self::PLUGIN_NAME),
					'item_scheduled'           => __('Message scheduled.', self::PLUGIN_NAME),
					'item_updated'             => __('Message updated.', self::PLUGIN_NAME),
				),
				'description'          => __('LINE Connect Messages', self::PLUGIN_NAME),
				'public'               => false,
				'hierarchical'         => false,
				'show_ui'              => true,
				'show_in_menu'         => false, // self::SLUG__DASHBOARD,
				'show_in_rest'         => false,
				'supports'             => array('title'),
				'register_meta_box_cb' => array(MessageScreen::class, 'register_meta_box'),
				'has_archive'          => false,
				'rewrite'              => false,
				'query_var'            => false,
				'menu_position'        => null,
			)
		);

		// register custom post type: Audience
		register_post_type(
			AudiencePostType::POST_TYPE,
			array(
				'labels'               => array(
					'name'                     => __('LC Audiences', self::PLUGIN_NAME),
					'singular_name'            => __('LC Audience', self::PLUGIN_NAME),
					'add_new'                  => __('Add New', self::PLUGIN_NAME),
					'add_new_item'             => __('Add New Audience', self::PLUGIN_NAME),
					'edit_item'                => __('Edit Audience', self::PLUGIN_NAME),
					'new_item'                 => __('New Audience', self::PLUGIN_NAME),
					'view_item'                => __('View Audience', self::PLUGIN_NAME),
					'search_items'             => __('Search Audiences', self::PLUGIN_NAME),
					'not_found'                => __('No Audiences found', self::PLUGIN_NAME),
					'not_found_in_trash'       => __('No Audiences found in Trash', self::PLUGIN_NAME),
					'parent_item_colon'        => '',
					'menu_name'                => __('LC Audiences', self::PLUGIN_NAME),
					'all_items'                => __('All Audiences', self::PLUGIN_NAME),
					'archives'                 => __('Audience Archives', self::PLUGIN_NAME),
					'attributes'               => __('Audience Attributes', self::PLUGIN_NAME),
					'insert_into_item'         => __('Insert into Audience', self::PLUGIN_NAME),
					'uploaded_to_this_item'    => __('Uploaded to this Audience', self::PLUGIN_NAME),
					'featured_image'           => __('Featured Image', self::PLUGIN_NAME),
					'set_featured_image'       => __('Set featured image', self::PLUGIN_NAME),
					'remove_featured_image'    => __('Remove featured image', self::PLUGIN_NAME),
					'use_featured_image'       => __('Use as featured image', self::PLUGIN_NAME),
					'filter_items_list'        => __('Filter Audiences list', self::PLUGIN_NAME),
					'items_list_navigation'    => __('Audiences list navigation', self::PLUGIN_NAME),
					'items_list'               => __('Audiences list', self::PLUGIN_NAME),
					'item_published'           => __('Audience published.', self::PLUGIN_NAME),
					'item_published_privately' => __('Audience published privately.', self::PLUGIN_NAME),
					'item_reverted_to_draft'   => __('Audience reverted to draft.', self::PLUGIN_NAME),
					'item_scheduled'           => __('Audience scheduled.', self::PLUGIN_NAME),
					'item_updated'             => __('Audience updated.', self::PLUGIN_NAME),
				),
				'description'          => __('LINE Connect Audiences', self::PLUGIN_NAME),
				'public'               => false,
				'hierarchical'         => false,
				'show_ui'              => true,
				'show_in_menu'         => false, // self::SLUG__DASHBOARD,
				'show_in_rest'         => false,
				'supports'             => array('title'),
				'register_meta_box_cb' => array(AudienceScreen::class, 'register_meta_box'),
				'has_archive'          => false,
				'rewrite'              => false,
				'query_var'            => false,
				'menu_position'        => null,
			)
		);

		// register custom post type: Scenario
		register_post_type(
			Scenario::POST_TYPE,
			array(
				'labels'               => array(
					'name'                     => __('Scenarios', self::PLUGIN_NAME),
					'singular_name'            => __('Scenario', self::PLUGIN_NAME),
					'add_new'                  => __('Add New', self::PLUGIN_NAME),
					'add_new_item'             => __('Add New Scenario', self::PLUGIN_NAME),
					'edit_item'                => __('Edit Scenario', self::PLUGIN_NAME),
					'new_item'                 => __('New Scenario', self::PLUGIN_NAME),
					'view_item'                => __('View Scenario', self::PLUGIN_NAME),
					'search_items'             => __('Search Scenarios', self::PLUGIN_NAME),
					'not_found'                => __('No Scenarios found', self::PLUGIN_NAME),
					'not_found_in_trash'       => __('No Scenarios found in Trash', self::PLUGIN_NAME),
					'parent_item_colon'        => '',
					'menu_name'                => __('Scenarios', self::PLUGIN_NAME),
					'all_items'                => __('All Scenarios', self::PLUGIN_NAME),
					'archives'                 => __('Scenario Archives', self::PLUGIN_NAME),
					'attributes'               => __('Scenario Attributes', self::PLUGIN_NAME),
					'insert_into_item'         => __('Insert into Scenario', self::PLUGIN_NAME),
					'uploaded_to_this_item'    => __('Uploaded to this Scenario', self::PLUGIN_NAME),
					'featured_image'           => __('Featured Image', self::PLUGIN_NAME),
					'set_featured_image'       => __('Set featured image', self::PLUGIN_NAME),
					'remove_featured_image'    => __('Remove featured image', self::PLUGIN_NAME),
					'use_featured_image'       => __('Use as featured image', self::PLUGIN_NAME),
					'filter_items_list'        => __('Filter Scenarios list', self::PLUGIN_NAME),
					'items_list_navigation'    => __('Scenarios list navigation', self::PLUGIN_NAME),
					'items_list'               => __('Scenarios list', self::PLUGIN_NAME),
					'item_published'           => __('Scenario published.', self::PLUGIN_NAME),
					'item_published_privately' => __('Scenario published privately.', self::PLUGIN_NAME),
					'item_reverted_to_draft'   => __('Scenario reverted to draft.', self::PLUGIN_NAME),
					'item_scheduled'           => __('Scenario scheduled.', self::PLUGIN_NAME),
					'item_updated'             => __('Scenario updated.', self::PLUGIN_NAME),
				),
				'description'          => __('LINE Connect Scenarios', self::PLUGIN_NAME),
				'public'               => false,
				'hierarchical'         => false,
				'show_ui'              => true,
				'show_in_menu'         => false,
				'show_in_rest'         => false,
				'supports'             => array('title'),
				'register_meta_box_cb' => array(ScenarioAdmin::class, 'register_meta_box'),
				'has_archive'          => false,
				'rewrite'              => false,
				'query_var'            => false,
				'menu_position'        => null,
			)
		);

		// register custom post type: ActionFlow
		register_post_type(
			ActionFlow::POST_TYPE,
			array(
				'labels'               => array(
					'name'                     => __('Action Flows', self::PLUGIN_NAME),
					'singular_name'            => __('Action Flow', self::PLUGIN_NAME),
					'add_new'                  => __('Add New', self::PLUGIN_NAME),
					'add_new_item'             => __('Add New Action Flow', self::PLUGIN_NAME),
					'edit_item'                => __('Edit Action Flow', self::PLUGIN_NAME),
					'new_item'                 => __('New Action Flow', self::PLUGIN_NAME),
					'view_item'                => __('View Action Flow', self::PLUGIN_NAME),
					'search_items'             => __('Search Action Flows', self::PLUGIN_NAME),
					'not_found'                => __('No Action Flows found', self::PLUGIN_NAME),
					'not_found_in_trash'       => __('No Action Flows found in Trash', self::PLUGIN_NAME),
					'parent_item_colon'        => '',
					'menu_name'                => __('Action Flows', self::PLUGIN_NAME),
					'all_items'                => __('All Action Flows', self::PLUGIN_NAME),
					'archives'                 => __('Action Flow Archives', self::PLUGIN_NAME),
					'attributes'               => __('Action Flow Attributes', self::PLUGIN_NAME),
					'insert_into_item'         => __('Insert into Action Flow', self::PLUGIN_NAME),
					'uploaded_to_this_item'    => __('Uploaded to this Action Flow', self::PLUGIN_NAME),
					'featured_image'           => __('Featured Image', self::PLUGIN_NAME),
					'set_featured_image'       => __('Set featured image', self::PLUGIN_NAME),
					'remove_featured_image'    => __('Remove featured image', self::PLUGIN_NAME),
					'use_featured_image'       => __('Use as featured image', self::PLUGIN_NAME),
					'filter_items_list'        => __('Filter Action Flows list', self::PLUGIN_NAME),
					'items_list_navigation'    => __('Action Flows list navigation', self::PLUGIN_NAME),
					'items_list'               => __('Action Flows list', self::PLUGIN_NAME),
					'item_published'           => __('Action Flow published.', self::PLUGIN_NAME),
					'item_published_privately' => __('Action Flow published privately.', self::PLUGIN_NAME),
					'item_reverted_to_draft'   => __('Action Flow reverted to draft.', self::PLUGIN_NAME),
					'item_scheduled'           => __('Action Flow scheduled.', self::PLUGIN_NAME),
					'item_updated'             => __('Action Flow updated.', self::PLUGIN_NAME),
				),
				'description'          => __('LINE Connect Action Flows', self::PLUGIN_NAME),
				'public'               => false,
				'hierarchical'         => false,
				'show_ui'              => true,
				'show_in_menu'         => false,
				'show_in_rest'         => false,
				'supports'             => array('title'),
				'register_meta_box_cb' => array(ActionFlowAdmin::class, 'register_meta_box'),
				'has_archive'          => false,
				'rewrite'              => false,
				'query_var'            => false,
				'menu_position'        => null,
			)
		);
	}

	static function cron_initialaize() {
		if (wp_get_schedule(self::CRON_EVENT_NAME) === false) {

			$unixTime = date('U') + 60; // 1 min after
			$timeStamp = mktime(date('H', $unixTime), date('i', $unixTime), 0, date('m', $unixTime), date('d', $unixTime), date('Y', $unixTime));
			wp_schedule_event($timeStamp, 'minly', self::CRON_EVENT_NAME);
		}

		/* else {

			$unixTime = wp_next_scheduled(self::CRON_EVENT_NAME);
			if (intval(date('i', $unixTime)) > 0) {

				wp_clear_scheduled_hook(self::CRON_EVENT_NAME);
				$timeStamp = mktime(date('H', $unixTime), 0, 0, date('m', $unixTime), date('d', $unixTime), date('Y', $unixTime));
				wp_schedule_event($timeStamp, 'hourly', self::CRON_EVENT_NAME);

			}

		}*/
	}

	/**
	 * プラグインのルートディレクトリを返す
	 * 
	 * @return string プラグインのルートディレクトリ
	 */
	public static function getRootDir() {
		return trailingslashit(dirname(__FILE__, substr_count(plugin_basename(__FILE__), '/')));
	}

	/**
	 * プラグインのルートディレクトリからの相対パスを受け取り、絶対パスURLを作成して返す
	 * 
	 * @param string $path URLパス
	 * @return string
	 */
	public static function plugins_url($path) {
		return plugins_url($path, self::getRootDir() . self::PLUGIN_ENTRY_FILE_NAME);
	}
}
// LineConnect::get_instance();
