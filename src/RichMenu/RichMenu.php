<?php

/**
 * Lineconnect Richmenu Class
 *
 * リッチメニューの追加、削除、変更を行うクラス
 *
 * @category Components
 * @package  Richmenu
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

namespace Shipweb\LineConnect\RichMenu;

use Shipweb\LineConnect\PostType\Message\Message as SLCMessage;
use Shipweb\LineConnect\Components\ReactJsonSchemaForm;
use lineconnect;
use lineconnectUtil;
use lineconnectConst;

class RichMenu {
	/**
	 * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
	 * 管理画面のトップメニューページを追加する関数
	 */
	public static function set_plugin_menu() {
		// 設定のサブメニュー「LINE Connect」を追加
		$page_hook_suffix = add_submenu_page(
			// 親ページ：
			lineconnect::SLUG__DASHBOARD,
			// ページタイトル：
			__('LINE Connect Richmenu', lineconnect::PLUGIN_NAME),
			// メニュータイトル：
			__('Richmenu', lineconnect::PLUGIN_NAME),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			lineconnect::SLUG__RICHMENU_ADD,
			// メニューに紐づく画面を描画するcallback関数：
			array(self::class, 'show_add_richmenu'),
			// メニューの位置
			NULL
		);
		add_action("admin_print_styles-{$page_hook_suffix}", array(self::class, 'wpdocs_plugin_admin_styles'));
		add_action("admin_print_scripts-{$page_hook_suffix}", array(self::class, 'wpdocs_plugin_admin_scripts'));
	}

	// 管理画面用にスクリプト読み込み
	public static function wpdocs_plugin_admin_scripts() {
		$richmenu_js = 'frontend/richmenu/dist/slc_richmenu.js';
		wp_enqueue_script(lineconnect::PLUGIN_PREFIX . 'richmenu', lineconnect::plugins_url($richmenu_js), array('wp-element', 'wp-i18n'), filemtime(lineconnect::getRootDir() . $richmenu_js), true);
		// JavaScriptの言語ファイル読み込み
		wp_set_script_translations(lineconnect::PLUGIN_PREFIX . 'richmenu', lineconnect::PLUGIN_NAME, lineconnect::getRootDir() . 'frontend/richmenu/languages');
	}

	// 管理画面用にスタイル読み込み
	public static function wpdocs_plugin_admin_styles() {
		$richmenu_css = 'frontend/richmenu/dist/style.css';
		wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'admin-css', lineconnect::plugins_url($richmenu_css), array(), filemtime(lineconnect::getRootDir() . $richmenu_css));
		$override_css_file = 'frontend/rjsf/dist/rjsf-override.css';
		wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'rjsf-override-css', lineconnect::plugins_url($override_css_file), array(), filemtime(lineconnect::getRootDir() . $override_css_file));
	}

	public static function show_add_richmenu() {
		$ary_init_data = array();
		$ary_init_data['channels']       = lineconnect::get_all_channels();
		$ary_init_data['ajaxurl']        = admin_url('admin-ajax.php');
		$ary_init_data['ajax_nonce']     = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
		$ary_init_data['formName']        = 'richmenuform-data';
		$formData = [];
		$form = array(
			'id' => 'richmenu',
			'schema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_richmenu_schema', Schema::get_richmenu_schema()),
			'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_richmenu_uischema',  Schema::get_richmenu_uischema()),
			'formData' => $formData,
			'props' => new \stdClass(),
		);

		$default_channel = lineconnect::get_channel(0);
		if ($default_channel) {
			$default_channel_prefix = $default_channel['prefix'];
		}
		$ary_init_data['templates'] = self::get_richmenu_templates();
		$ary_init_data['richmenus'] = $default_channel ? self::get_richmenus_with_data($default_channel) : array();
		$ary_init_data['aliases'] = $default_channel ? self::get_richmenu_aliases($default_channel) : array();
		$ary_init_data['channel_prefix'] = $default_channel_prefix;
		$ary_init_data['form']        = $form;
		$ary_init_data['translateString'] = ReactJsonSchemaForm::get_translate_string();

		$inidata = json_encode($ary_init_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		echo <<< EOM
<div id="line_richmenu_root"></div>
<script>
var lc_initdata = {$inidata};
</script>
EOM;
	}

	/**
	 * 	リッチメニューIDの更新
	 * @param array $channel チャネルデータ
	 * @param array $is_changed_richmenus 変更されたリッチメニュー名の配列
	 * @param array $ary_richmeneus リッチメニュー名をキー、リッチメニューIDを値とするリスト
	 * @return array $result 更新結果
	 */
	public static function updateRichMenuId($channel, $is_changed_richmenus, $ary_richmeneus) {
		// error_log(print_r($is_changed_richmenus, true));
		// error_log(print_r($ary_richmeneus, true));
		$result = array();
		$success_message = $error_message = "";
		if (!isset($channel['channel-access-token']) || !isset($channel['channel-secret'])) {
			// $target = ($state == 'linked' ? __('Linked', lineconnect::PLUGIN_NAME) : __('Unlinked', lineconnect::PLUGIN_NAME));
			return array(__('Channel access token or channel secret is not set.', lineconnect::PLUGIN_NAME));
		}

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret = $channel['channel-secret'];

		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

		if (in_array('unlinked', $is_changed_richmenus)) {
			$richmenu_id = $ary_richmeneus['unlinked'];
			if ($richmenu_id == "") {
				//デフォルトのリッチメニューを解除
				$response = $bot->cancelDefaultRichMenuId();
			} else {
				//デフォルトのリッチメニューにセット
				$response = $bot->setDefaultRichMenuId($richmenu_id);
			}
			//送信に成功した場合
			if ($response->getHTTPStatus() === 200) {
				$success_message = "";
			} else {
				$error_message = __('Failed', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
			}
			$result[] = __('Unlinked', lineconnect::PLUGIN_NAME) . ": " . $success_message . $error_message;
			// remove unlinked from $is_changed_richmenus
			if (($key = array_search('unlinked', $is_changed_richmenus)) !== false) {
				unset($is_changed_richmenus[$key]);
			}
		}

		if (!empty($is_changed_richmenus)) {
			//ロールのリッチメニューIDを優先するが、ロールのリッチメニューIDが無い場合はlinkedのリッチメニューIDをセットする
			$line_user_ids_by_type = array(
				'delete' => array(),
				'linked' => array(),
			);
			foreach ($ary_richmeneus as $role => $richmenu_id) {
				if ($role == 'unlinked') {
					continue;
				}
				$line_user_ids_by_type[$role] = array();
			}

			$secret_prefix = substr($channel_secret, 0, 4);
			//連携済みユーザーのリッチメニューIDを変更
			$args = array(
				'meta_query' => array(
					array(
						'key'     => lineconnect::META_KEY__LINE,
						'compare' => 'EXISTS'
					)
				),
				//'role' => $role,
				'fields' => 'all_with_meta'
			);
			// $line_user_ids = array(); //変更するLINEユーザーIDの配列
			$user_query = new \WP_User_Query($args); //条件を指定してWordpressからユーザーを検索
			$users = $user_query->get_results(); //クエリ実行
			if (!empty($users)) {  //マッチするユーザーが見つかれば
				//ユーザーのメタデータを取得
				foreach ($users as $user) {
					$user_meta_line = $user->get(lineconnect::META_KEY__LINE);
					if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
						// $line_user_ids[] = $user_meta_line[$secret_prefix]['id'];
						// ユーザーのロールに応じてセットするリッチメニューIDを変える
						$isIdsetted = false;
						foreach ($user->roles as $role) {
							if (isset($line_user_ids_by_type[$role]) && $ary_richmeneus[$role] != "") {
								$line_user_ids_by_type[$role][] = $user_meta_line[$secret_prefix]['id'];
								$isIdsetted = true;
								break;
							}
						}
						if (!$isIdsetted) {
							if ($ary_richmeneus['linked'] != "") {
								$line_user_ids_by_type['linked'][] = $user_meta_line[$secret_prefix]['id'];
							} else {
								$line_user_ids_by_type['delete'][] = $user_meta_line[$secret_prefix]['id'];
							}
						}
					}
				}

				foreach ($line_user_ids_by_type as $state => $line_user_ids) {
					if (empty($line_user_ids)) {
						continue;
					}
					$target_cnt = count($line_user_ids) . "人";

					//最大500人なので、500個ごとに配列を分割して変更
					foreach (array_chunk($line_user_ids, 500) as $line_user_id_chunk) {
						if ($state == 'delete') {
							//複数のユーザーのリッチメニューのリンクを解除する
							$response = $bot->bulkUnlinkRichMenu($line_user_id_chunk);
						} else {
							//リッチメニューと複数のユーザーをリンクする
							$richmenu_id = $ary_richmeneus[$state];
							$response = $bot->bulkLinkRichMenu($line_user_id_chunk, $richmenu_id);
						}
					}
					//送信に成功した場合
					if ($response->getHTTPStatus() === 202) {
						$success_message = "(" . $target_cnt . ")";
					} else {
						$error_message = __('Failed', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
					}
					$result[] = ($state == 'linked' ? __('Linked', lineconnect::PLUGIN_NAME) : ($state == 'delete' ? __('Deleted', lineconnect::PLUGIN_NAME) : translate_user_role($state))) . ": " . $success_message . $error_message . implode(',', $line_user_id_chunk);
				}
			}
		}
		return $result;
	}

	/*
	リッチメニューIDの確認
	*/
	public static function checkRichMenuId($channel, $richmenu_id) {
		if (isset($channel['channel-access-token']) && isset($channel['channel-secret'])) {
			$channel_access_token = $channel['channel-access-token'];
			$channel_secret = $channel['channel-secret'];

			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

			if ($richmenu_id == "") {
				return true;
			} else {
				//リッチメニューを取得
				$response = $bot->getRichMenu($richmenu_id);
			}
			//送信に成功した場合
			if ($response->getHTTPStatus() === 200) {
				return true;
			} else {
				return array(false, $response->getJSONDecodedBody()['message']);
			}
		} else {
			return array(false, __('Channel access token or channel secret is not set.', lineconnect::PLUGIN_NAME));
		}
	}

	/*
	* リッチメニューとユーザーをリンクする
	*/
	public static function link_richmenu($userid) {
		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			$channel_access_token = $channel['channel-access-token'];
			$channel_secret = $channel['channel-secret'];

			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

			$secret_prefix = substr($channel_secret, 0, 4);

			$user = get_userdata($userid);
			if ($user) {
				$target_richmenu_id = "";
				$user_meta_line = get_user_meta($userid, lineconnect::META_KEY__LINE, true);
				if (isset($user_meta_line[$secret_prefix]) && isset($user_meta_line[$secret_prefix]['id'])) {

					foreach ($user->roles as $role) {
						if (isset($channel[$role . '-richmenu']) && $channel[$role . '-richmenu'] != "") {
							$target_richmenu_id = $channel[$role . '-richmenu'];
							break;
						}
					}
					if ($target_richmenu_id == "") {
						$target_richmenu_id = $channel['linked-richmenu'];
					}
					if ($target_richmenu_id != "" && $user_meta_line[$secret_prefix]['id']) {
						$response = $bot->linkRichMenu($user_meta_line[$secret_prefix]['id'], $target_richmenu_id);
						// error_log("userid: " . $userid . " LINE userid: ". $user_meta_line[$secret_prefix]['id']. " richmenu:".$target_richmenu_id);
					}
				}
			}
		}
	}

	//リッチメニューとユーザーのリンクを解除する
	public static function line_unlink_richmenu($userid, $target_secret_prefix = "all") {

		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			$channel_access_token = $channel['channel-access-token'];
			$channel_secret = $channel['channel-secret'];

			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

			$secret_prefix = substr($channel_secret, 0, 4);

			//対象のチャンネルかどうかチェック
			if ($target_secret_prefix == $secret_prefix || $target_secret_prefix == 'all') {
				$user_meta_line = get_user_meta($userid, lineconnect::META_KEY__LINE, true);
				if (isset($user_meta_line[$secret_prefix])) {
					if ($user_meta_line[$secret_prefix]['id']) {
						$response = $bot->unlinkRichMenu($user_meta_line[$secret_prefix]['id']);
					}
				}
			}
		}
	}

	/**
	 * ユーザーロールが変化したフックを受け取り、リッチメニューを変更する
	 * @param int $user_id ユーザーID
	 * @param string $role ロール
	 * @param string[] $old_roles 旧ロール
	 */
	public static function change_user_role($user_id, $role, $old_roles) {
		self::link_richmenu($user_id);
	}

	/**
	 * チャンネルのリッチメニューリストをすべて取得
	 * @param object $target_channel 取得するチャンネルデータ(nullの場合は全チャンネルのチャネルデータを取得)
	 * @return array リッチメニューのID、タイトルを値とする配列
	 */
	public static function get_richmenus($target_channel = null) {
		$all_richmenus = array();
		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			if ($target_channel == null || $channel == $target_channel) {

				$channel_access_token = $channel['channel-access-token'];
				$channel_secret = $channel['channel-secret'];
				$secret_prefix = substr($channel_secret, 0, 4);

				if (false === ($richmenus = get_transient(lineconnect::TRANSIENT_KEY__RICHMENUS_LIST . $secret_prefix))) {

					$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
					$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

					$response = $bot->getRichMenuList();
					$richmenus = array();
					if ($response->getHTTPStatus() === 200) {
						$temp_richmenus = $response->getJSONDecodedBody()['richmenus'];
						foreach ($temp_richmenus as $richmenu) {
							$richmenus[$richmenu['richMenuId']] = $richmenu['name'];
						}
					}
					set_transient(lineconnect::TRANSIENT_KEY__RICHMENUS_LIST . $secret_prefix, $richmenus, MONTH_IN_SECONDS);
				}
				$all_richmenus = array_merge($all_richmenus, $richmenus);
			}
		}
		return $all_richmenus;
	}

	/**
	 * チャンネルのリッチメニューをデータ含めてすべて取得
	 * @param object $channel チャンネルデータ
	 * @return array リッチメニューのIDをキー、リッチメニューデータを値とする配列
	 */
	private static function get_richmenus_with_data($channel) {

		$channel_access_token = $channel['channel-access-token'];
		$channel_secret = $channel['channel-secret'];
		$secret_prefix = substr($channel_secret, 0, 4);

		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

		$response = $bot->getRichMenuList();
		$richmenus = array();
		if ($response->getHTTPStatus() === 200) {
			$temp_richmenus = $response->getJSONDecodedBody()['richmenus'];
			foreach ($temp_richmenus as $richmenu) {
				$richmenu_image_path = self::save_richmenu_image($channel, $richmenu['richMenuId']);
				$richmenu['imageUrl'] = $richmenu_image_path ? wp_upload_dir()['baseurl'] . substr($richmenu_image_path, strpos($richmenu_image_path, '/lineconnect')) : '';
				$richmenus[$richmenu['richMenuId']] = $richmenu;
			}
		}
		return $richmenus;
	}

	/**
	 * リッチメニュー画像をダウンロードして保存
	 * @param string $channel　チャンネルデータ
	 * @param string $richmenu_id リッチメニューID
	 * @param string $save_dir 保存先ディレクトリ
	 * @return string 保存したファイル名
	 */
	private static function save_richmenu_image($channel, $rich_menu_id, $save_dir = 'richmenu') {
		$target_dir_path = lineconnectUtil::make_lineconnect_dir($save_dir . '/' . $channel['prefix'], false);
		if ($target_dir_path) {
			// if file exists, return file path
			if (file_exists($target_dir_path . '/' . $rich_menu_id)) {
				return $target_dir_path . '/' . $rich_menu_id;
			}

			$channel_access_token = $channel['channel-access-token'];
			$channel_secret = $channel['channel-secret'];
			$secret_prefix = substr($channel_secret, 0, 4);


			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

			$response = $bot->downloadRichMenuImage($rich_menu_id);
			if ($response->getHTTPStatus() === 200) {
				$target_file_path = $target_dir_path . '/' . $rich_menu_id;
				file_put_contents($target_file_path, $response->getRawBody());
				return $target_file_path;
			}
		}
		return null;
	}

	/**
	 * AJAXでリッチメニュー一覧を取得
	 * @return array リッチメニューのID、タイトルを連想配列に持つ配列
	 */
	public static function ajax_get_richmenus() {
		$isSuccess = true;
		$richmenus = array();
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
			$isSuccess = false;
		}

		if ($isSuccess) {
			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			if (!empty($channel_prefix)) {
				$channel = lineconnect::get_channel($channel_prefix);
				if ($channel) {
					$richmenus = self::get_richmenus_with_data($channel);
				}
			}
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($richmenus);
		wp_die();
	}

	/**
	 * AJAXでリッチメニューを取得
	 * @return array リッチメニューのデータ
	 */
	public static function ajax_get_richmenu() {
		$isSuccess = true;
		$richmenu = array();
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
			$isSuccess = false;
		}

		if ($isSuccess) {
			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			if (!empty($channel_prefix) && isset($_POST['richmenu_id'])) {
				$channel = lineconnect::get_channel($channel_prefix);
				if ($channel) {
					$channel_access_token = $channel['channel-access-token'];
					$channel_secret = $channel['channel-secret'];
					$secret_prefix = substr($channel_secret, 0, 4);

					$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
					$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

					$response = $bot->getRichMenu($_POST['richmenu_id']);

					if ($response->getHTTPStatus() === 200) {
						$richmenu = $response->getJSONDecodedBody();
					}
				}
			}
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($richmenu);
		wp_die();
	}

	/**
	 * AJAXでリッチメニューを削除
	 * @return array リッチメニューリスト
	 */
	public static function ajax_delete_richmenu() {
		$isSuccess = true;
		$richmenus = array();
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
			$isSuccess = false;
		}
		$error_message = $success_message = '';
		$ary_success_message = array();
		$ary_error_message   = array();

		if ($isSuccess) {
			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			if (!empty($channel_prefix) && isset($_POST['richmenu_id'])) {
				$richmenu_id = sanitize_text_field($_POST['richmenu_id']);
				$channel = lineconnect::get_channel($channel_prefix);
				if ($channel) {
					$channel_access_token = $channel['channel-access-token'];
					$channel_secret = $channel['channel-secret'];
					$secret_prefix = substr($channel_secret, 0, 4);

					// delete cache image
					$target_dir_path = lineconnectUtil::make_lineconnect_dir('richmenu/' . $channel['prefix'], false);
					if ($target_dir_path) {
						$target_file_path = $target_dir_path . '/' . $richmenu_id;
						if (file_exists($target_file_path)) {
							unlink($target_file_path);
						}
					}

					$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
					$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

					$response = $bot->deleteRichmenu($richmenu_id);

					if ($response->getHTTPStatus() === 200) {
						$success_message = __('Richmenu was successfully deleted', lineconnect::PLUGIN_NAME);
						$richmenus = self::get_richmenus_with_data($channel);
					} else {
						$isSuccess = false;
						$error_message = __('Failed to delete richmenu', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
					}
				}
			} else {
				$isSuccess = false;
				$error_message = __('Richmenu ID is not set', lineconnect::PLUGIN_NAME);
			}
		}
		if ($isSuccess) {
			self::clearRichMenuCache($secret_prefix);
		}
		if (!empty($success_message)) {
			$ary_success_message[] = $success_message;
		}
		if (!empty($error_message)) {
			$ary_error_message[] = $error_message;
		}
		$result['result']  = $isSuccess ? 'success' : 'failed';
		$result['success'] = $ary_success_message;
		$result['error']   = $ary_error_message;
		$result['richmenus'] = $richmenus;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($result);
		wp_die();
	}

	/**
	 * AJAXでリッチメニューを作成
	 * @return array リッチメニューリスト
	 */
	public static function ajax_create_richmenu() {
		$isSuccess = true;
		$richmenus = array();
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
			$isSuccess = false;
		}
		$error_message = $success_message = '';
		$ary_success_message = array();
		$ary_error_message   = array();

		if ($isSuccess) {
			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			if (!empty($channel_prefix) && isset($_POST['richmenu'])) {
				$channel = lineconnect::get_channel($channel_prefix);
				if ($channel) {
					$channel_access_token = $channel['channel-access-token'];
					$channel_secret = $channel['channel-secret'];
					$secret_prefix = substr($channel_secret, 0, 4);

					$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
					$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

					$richmenu = json_decode(stripslashes($_POST['richmenu']), true);
					$richMenuSizeBuilder = new \LINE\LINEBot\RichMenuBuilder\RichMenuSizeBuilder($richmenu['size']['height'], $richmenu['size']['width']);
					$areaBuilders = array();
					foreach ($richmenu['areas'] as $area) {
						$richMenuAreaBoundsBuilder = new \LINE\LINEBot\RichMenuBuilder\RichMenuAreaBoundsBuilder($area['bounds']['x'], $area['bounds']['y'], $area['bounds']['width'], $area['bounds']['height']);
						$richMenuAreaActionBuilder = SLCMessage::buildTemplateActionBuilder($area['action']);
						$richMenuAreaBuilder = new \LINE\LINEBot\RichMenuBuilder\RichMenuAreaBuilder($richMenuAreaBoundsBuilder, $richMenuAreaActionBuilder);
						$areaBuilders[] = $richMenuAreaBuilder;
					}
					$richMenuBuilder = new \LINE\LINEBot\RichMenuBuilder($richMenuSizeBuilder, $richmenu['selected'], $richmenu['name'], $richmenu['chatBarText'], $areaBuilders);
					$response = $bot->createRichMenu($richMenuBuilder);

					if ($response->getHTTPStatus() === 200) {
						// upload image file
						$richmenu_id = $response->getJSONDecodedBody()['richMenuId'];
						// if file uploaded 
						if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
							$richmenu_image_file = $_FILES['file'];
						} else if (!empty($richmenu['richMenuId'])) {
							$image_file_path = self::save_richmenu_image($channel, $richmenu['richMenuId']);
							$richmenu_image_file = array(
								'tmp_name' => $image_file_path,
								'type' => mime_content_type($image_file_path),
							);
						}
						if (isset($richmenu_image_file)) {
							$response = $bot->uploadRichMenuImage($richmenu_id, $richmenu_image_file['tmp_name'], $richmenu_image_file['type']);
							if ($response->getHTTPStatus() === 200) {
								$success_message = __('Richmenu was successfully created', lineconnect::PLUGIN_NAME);
								$richmenus = self::get_richmenus_with_data($channel);
							} else {
								$isSuccess = false;
								$error_message = __('Failed to upload richmenu image', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
							}
						} else {
							$isSuccess = false;
							$error_message = __('Failed to upload richmenu image', lineconnect::PLUGIN_NAME) . ": " . $richmenu_image_file['error'];
						}
					} else {
						$isSuccess = false;
						$error_message = __('Failed to create richmenu', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
					}
				}
			} else {
				$isSuccess = false;
				$error_message = __('Richmenu is not set', lineconnect::PLUGIN_NAME);
			}
		}
		if ($isSuccess) {
			self::clearRichMenuCache($secret_prefix);
		}
		if (!empty($success_message)) {
			$ary_success_message[] = $success_message;
		}
		if (!empty($error_message)) {
			$ary_error_message[] = $error_message;
		}
		$result['result']  = $isSuccess ? 'success' : 'failed';
		$result['success'] = $ary_success_message;
		$result['error']   = $ary_error_message;
		$result['richmenus'] = $richmenus;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($result);
		wp_die();
	}

	/**
	 * リッチメニューリストのキャッシュを削除
	 * @param string|null $target_secret_prefix チャンネルシークレットの先頭4文字
	 */
	public static function clearRichMenuCache($target_secret_prefix = null) {
		$ary_channels  = lineconnect::get_all_channels();
		foreach ($ary_channels as $channel_id => $channel) {
			$channel_secret = $channel['channel-secret'];
			$secret_prefix = substr($channel_secret, 0, 4);
			if ($target_secret_prefix == null || $target_secret_prefix == $secret_prefix) {
				delete_transient(lineconnect::TRANSIENT_KEY__RICHMENUS_LIST . $secret_prefix);
			}
		}
		return true;
	}

	/**
	 * リッチメニューテンプレートを取得
	 * return array リッチメニューテンプレートの配列
	 */
	private static function get_richmenu_templates() {
		$richmenu_templates = array();
		foreach (Schema::get_template_bounds() as $bounds) {
			$richmenu_template = array();
			$richmenu_template['id'] = $bounds['id'];
			$richmenu_template['title'] = $bounds['title'];
			$richmenu_template['image'] = $bounds['image'];
			$template_data = Schema::get_template_defalut_data();
			$template_data['size'] = $bounds['size'];
			$area = array();
			foreach ($bounds['bounds'] as $area_bounds) {
				$area[] = array(
					'bounds' => $area_bounds,
					//'action' => new stdClass(),
				);
			}
			$template_data['areas'] = $area;
			$richmenu_template['data'] = $template_data;
			$richmenu_templates[] = $richmenu_template;
		}
		return $richmenu_templates;
	}

	/**
	 * チャンネルのリッチメニューエイリアスリストをすべて取得
	 * @param object $target_channel チャンネルデータ(nullの場合は全チャンネルのエイリアスを取得)
	 * @return array リッチメニューエイリアスIDをキー、リッチメニューIDを値として持つ配列
	 */
	public static function get_richmenu_aliases($target_channel = null) {
		$all_richmenu_aliases = array();
		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			if ($target_channel == null || $channel == $target_channel) {

				$channel_access_token = $channel['channel-access-token'];
				$channel_secret = $channel['channel-secret'];
				$secret_prefix = substr($channel_secret, 0, 4);

				if (false === ($richmenu_aliases = get_transient(lineconnect::TRANSIENT_KEY__RICHMENU_ALIAS_LIST . $secret_prefix))) {
					// $richmenus = self::get_richmenus($channel);
					$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
					$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

					$response = $bot->getRichMenuAliasList();
					$richmenu_aliases = array();
					if ($response->getHTTPStatus() === 200) {
						$temp_richmenu_aliases = $response->getJSONDecodedBody()['aliases'];
						foreach ($temp_richmenu_aliases as $richmenu_alias) {
							$richmenu_aliases[$richmenu_alias['richMenuAliasId']] = $richmenu_alias['richMenuId'];
						}
					}
					set_transient(lineconnect::TRANSIENT_KEY__RICHMENU_ALIAS_LIST . $secret_prefix, $richmenu_aliases, MONTH_IN_SECONDS);
				}
				$all_richmenu_aliases = array_merge($all_richmenu_aliases, $richmenu_aliases);
			}
		}
		return $all_richmenu_aliases;
	}

	/**
	 * AJAXでリッチメニューエイリアス一覧を取得
	 * @return array　リッチメニューエイリアスIDをキー、リッチメニューIDを値として持つ配列
	 */
	public static function ajax_get_richmenus_alias() {
		$isSuccess = true;
		$richmenu_aliases = array();
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
			$isSuccess = false;
		}

		if ($isSuccess) {
			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			if (!empty($channel_prefix)) {
				$channel = lineconnect::get_channel($channel_prefix);
				if ($channel) {
					$richmenu_aliases = self::get_richmenu_aliases($channel);
				}
			}
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($richmenu_aliases);
		wp_die();
	}

	/**
	 * AJAXでリッチメニューエイリアスを削除
	 * @return array リッチメニューエイリアス一覧
	 */
	public static function ajax_delete_richmenu_alias() {
		$isSuccess = true;
		$richmenu_aliases = array();
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
			$isSuccess = false;
		}
		$error_message = $success_message = '';
		$ary_success_message = array();
		$ary_error_message   = array();

		if ($isSuccess) {
			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			if (!empty($channel_prefix) && isset($_POST['richMenuAliasId'])) {
				$richMenuAliasId = sanitize_text_field($_POST['richMenuAliasId']);
				$channel = lineconnect::get_channel($channel_prefix);
				if ($channel) {
					$channel_access_token = $channel['channel-access-token'];
					$channel_secret = $channel['channel-secret'];
					$secret_prefix = substr($channel_secret, 0, 4);

					$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
					$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

					$response = $bot->deleteRichMenuAlias($richMenuAliasId);

					if ($response->getHTTPStatus() === 200) {
						$success_message = __('Richmenu alias was successfully deleted', lineconnect::PLUGIN_NAME);
						self::clearRichMenuAliasCache($secret_prefix);
						$richmenu_aliases = self::get_richmenu_aliases($channel);
					} else {
						$isSuccess = false;
						$error_message = __('Failed to delete richmenu alias', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
					}
				}
			} else {
				$isSuccess = false;
				$error_message = __('Richmenu alias ID is not set', lineconnect::PLUGIN_NAME);
			}
		}
		if (!empty($success_message)) {
			$ary_success_message[] = $success_message;
		}
		if (!empty($error_message)) {
			$ary_error_message[] = $error_message;
		}
		$result['result']  = $isSuccess ? 'success' : 'failed';
		$result['success'] = $ary_success_message;
		$result['error']   = $ary_error_message;
		$result['aliases'] = $richmenu_aliases;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($result);
		wp_die();
	}

	/**
	 * AJAXでリッチメニューエイリアスを作成
	 * @return array リッチメニューリスト
	 */
	public static function ajax_create_richmenu_alias() {
		$isSuccess = true;
		$richmenu_aliases = array();
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
			$isSuccess = false;
		}
		$error_message = $success_message = '';
		$ary_success_message = array();
		$ary_error_message   = array();

		if ($isSuccess) {
			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			if (!empty($channel_prefix) && isset($_POST['richMenuAliasId']) && isset($_POST['richMenuId'])) {
				$channel = lineconnect::get_channel($channel_prefix);
				if ($channel) {
					$channel_access_token = $channel['channel-access-token'];
					$channel_secret = $channel['channel-secret'];
					$secret_prefix = substr($channel_secret, 0, 4);

					$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
					$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

					$richMenuAliasId = stripslashes($_POST['richMenuAliasId']);
					$richMenuId = stripslashes($_POST['richMenuId']);
					$response = $bot->createRichMenuAlias($richMenuAliasId, $richMenuId);

					if ($response->getHTTPStatus() === 200) {
						$success_message = __('Richmenu alias was successfully created', lineconnect::PLUGIN_NAME);
						self::clearRichMenuAliasCache($secret_prefix);
						$richmenu_aliases = self::get_richmenu_aliases($channel);
					} else {
						$isSuccess = false;
						$error_message = __('Failed to create richmenu alias', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
					}
				}
			} else {
				$isSuccess = false;
				$error_message = __('Richmenu alias is not set', lineconnect::PLUGIN_NAME);
			}
		}

		if (!empty($success_message)) {
			$ary_success_message[] = $success_message;
		}
		if (!empty($error_message)) {
			$ary_error_message[] = $error_message;
		}
		$result['result']  = $isSuccess ? 'success' : 'failed';
		$result['success'] = $ary_success_message;
		$result['error']   = $ary_error_message;
		$result['aliases'] = $richmenu_aliases;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($result);
		wp_die();
	}

	/**
	 * AJXでリッチメニューエイリアスリストの更新
	 * @return array リッチメニューエイリアスリスト
	 */
	public static function ajax_update_richmenu_alias() {
		$isSuccess = true;
		$richmenu_aliases = array();
		// ログインしていない場合は無視
		if (! is_user_logged_in()) {
			$isSuccess = false;
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
			$isSuccess = false;
		}
		// nonceで設定したcredentialのチェック結果に問題がある場合
		if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
			$isSuccess = false;
		}
		$error_message = $success_message = '';
		$ary_success_message = array();
		$ary_error_message   = array();

		if ($isSuccess) {
			$channel_prefix = isset($_POST['channel']) ? $_POST['channel'] : null;
			if (!empty($channel_prefix) && isset($_POST['richMenuAliasId']) && isset($_POST['richMenuId'])) {
				$channel = lineconnect::get_channel($channel_prefix);
				if ($channel) {
					$channel_access_token = $channel['channel-access-token'];
					$channel_secret = $channel['channel-secret'];
					$secret_prefix = substr($channel_secret, 0, 4);

					$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
					$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

					$richMenuAliasId = stripslashes($_POST['richMenuAliasId']);
					$richMenuId = stripslashes($_POST['richMenuId']);
					$response = $bot->updateRichMenuAlias($richMenuAliasId, $richMenuId);

					if ($response->getHTTPStatus() === 200) {
						$success_message = __('Richmenu alias was successfully updated', lineconnect::PLUGIN_NAME);
						self::clearRichMenuAliasCache($secret_prefix);
						$richmenu_aliases = self::get_richmenu_aliases($channel);
					} else {
						$isSuccess = false;
						$error_message = __('Failed to update richmenu alias', lineconnect::PLUGIN_NAME) . ": " . $response->getRawBody();
					}
				}
			} else {
				$isSuccess = false;
				$error_message = __('Richmenu alias or richmenu ID is not set', lineconnect::PLUGIN_NAME);
			}
		}
		if (!empty($success_message)) {
			$ary_success_message[] = $success_message;
		}
		if (!empty($error_message)) {
			$ary_error_message[] = $error_message;
		}
		$result['result']  = $isSuccess ? 'success' : 'failed';
		$result['success'] = $ary_success_message;
		$result['error']   = $ary_error_message;
		$result['aliases'] = $richmenu_aliases;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($result);
		wp_die();
	}

	/**
	 * リッチメニューエイリアスリストのキャッシュを削除
	 * @param string|null $target_secret_prefix チャンネルシークレットの先頭4文字
	 */
	private static function clearRichMenuAliasCache($target_secret_prefix = null) {
		$ary_channels  = lineconnect::get_all_channels();
		foreach ($ary_channels as $channel_id => $channel) {
			$channel_secret = $channel['channel-secret'];
			$secret_prefix = substr($channel_secret, 0, 4);
			if ($target_secret_prefix == null || $target_secret_prefix == $secret_prefix) {
				delete_transient(lineconnect::TRANSIENT_KEY__RICHMENU_ALIAS_LIST . $secret_prefix);
			}
		}
		return true;
	}
}
