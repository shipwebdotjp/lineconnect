<?php

/**
 * LineConnect
 * シナリオの管理画面
 */

namespace Shipweb\LineConnect\Scenario;

use Shipweb\LineConnect\Scenario\Scenario;
use Shipweb\LineConnect\Scenario\Admin as ScenarioAdmin;
use LineConnect;
use Shipweb\LineConnect\Components\ReactJsonSchemaForm;
use stdClass;
use lineconnectConst;

/**
 * シナリオの管理画面
 */
class Admin {
	/**
	 * 画面のslug
	 */
	static function set_plugin_menu() {
		// 設定のサブメニュー「LINE Connect」を追加
		$page_hook_suffix = add_submenu_page(
			// 親ページ：
			lineconnect::SLUG__DASHBOARD,
			// ページタイトル：
			__('LINE Connect Scenario', lineconnect::PLUGIN_NAME),
			// メニュータイトル：
			__('Scenarios', lineconnect::PLUGIN_NAME),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			'edit.php?post_type=' . Scenario::POST_TYPE,
			// メニューに紐づく画面を描画するcallback関数：
			false,
			// メニューの位置
			NULL
		);
		// remove_menu_page( lineconnect::SLUG__DM_FORM );
	}

	static function register_meta_box() {
		// 投稿ページでRJSFフォームを表示
		add_meta_box(
			// チェックボックスのID
			Scenario::META_KEY_DATA,
			// チェックボックスのラベル名
			__('LINE Connect Scenario', lineconnect::PLUGIN_NAME),
			// チェックボックスを表示するコールバック関数
			array(ScenarioAdmin::class, 'show_json_edit_form'),
			// 投稿画面に表示
			Scenario::POST_TYPE,
			// 投稿画面の下に表示
			'advanced',
			// 優先度(default)
			'default'
		);
	}

	// 管理画面（投稿ページ）用にスクリプト読み込み
	static function wpdocs_selectively_enqueue_admin_script() {
		// require_once LineConnect::getRootDir() . 'include/rjsf.php';
		ReactJsonSchemaForm::wpdocs_selectively_enqueue_admin_script(Scenario::POST_TYPE);
	}

	/**
	 * JSONスキーマからフォームを表示
	 */
	static function show_json_edit_form() {
		$ary_init_data = array();
		$ary_init_data['formName'] = Scenario::PARAMETER_DATA;
		$schema_version = get_post_meta(get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true);
		$formData = get_post_meta(get_the_ID(), Scenario::META_KEY_DATA, true);
		// error_log(print_r(\Shipweb\LineConnect\Utilities\ArrayPrinter::print($formData), true));
		$mainSchema = Scenario::getSchema();
		$form = array(
			array(
				'id' => Scenario::NAME,
				'schema' => $mainSchema,
				'uiSchema' => Scenario::getUiSchema(),
				'formData' => self::get_form_data($formData[0] ?? null, $schema_version),
				'props' => new stdClass(),
			),
		);
		$ary_init_data['subSchema'] = array();
		$ary_init_data['form'] = $form;
		$ary_init_data['translateString'] = ReactJsonSchemaForm::get_translate_string();
		$nonce_field = wp_nonce_field(
			Scenario::CREDENTIAL_ACTION,
			Scenario::CREDENTIAL_NAME,
			true,
			false
		);
		// require_once LineConnect::getRootDir() . 'include/rjsf.php';
		ReactJsonSchemaForm::show_json_edit_form($ary_init_data, $nonce_field);
	}

	/**
	 * 記事を保存
	 */
	static function save_post($post_ID, $post, $update) {
		if (isset($_POST[Scenario::CREDENTIAL_NAME]) && check_admin_referer(Scenario::CREDENTIAL_ACTION, Scenario::CREDENTIAL_NAME)) {
			$data = isset($_POST[Scenario::PARAMETER_DATA]) ?  stripslashes($_POST[Scenario::PARAMETER_DATA])  : '';

			if (! empty($data)) {
				$json_data = json_decode($data, true);
				if (! empty($json_data)) {
					// error_log( print_r( $data, true ) );
					update_post_meta($post_ID, Scenario::META_KEY_DATA, $json_data);
					update_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION, Scenario::SCHEMA_VERSION);
				} else {
					// error_log( 'delete' . print_r( $data, true ) );
					delete_post_meta($post_ID, Scenario::META_KEY_DATA);
					delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
				}
			} else {
				// error_log( 'empty' . print_r( $data, true ) );
				delete_post_meta($post_ID, Scenario::META_KEY_DATA);
				delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
			}
		}
	}

	/** 
	 * Return form data
	 */
	static function get_form_data($formData, $schema_version) {
		if (empty($schema_version) || $schema_version == Scenario::SCHEMA_VERSION) {
			return !empty($formData) ? $formData : new stdClass();
		}
		// if old schema veersion, migrate and return
	}

	/**
	 * カスタム投稿タイプの一覧にカラムを追加
	 */
	public static function add_columns($columns) {
		$new_columns = array();

		// タイトルの後にステータスカラムを挿入
		foreach ($columns as $key => $value) {
			$new_columns[$key] = $value;
			if ($key === 'title') {
				$new_columns['active_users'] = __('Active', lineconnect::PLUGIN_NAME);
				$new_columns['completed_users'] = __('Completed', lineconnect::PLUGIN_NAME);
				$new_columns['error_users'] = __('Error', lineconnect::PLUGIN_NAME);
				$new_columns['paused_users'] = __('Paused', lineconnect::PLUGIN_NAME);
			}
		}

		return $new_columns;
	}

	/**
	 * カスタムカラムの内容を表示
	 */
	public static function add_columns_content($column_name, $post_id) {
		global $wpdb;

		// ステータスカラムのみ処理
		if (!in_array($column_name, ['active_users', 'completed_users', 'error_users', 'paused_users'])) {
			return;
		}

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
		$status_map = [
			'active_users' => Scenario::STATUS_ACTIVE,
			'completed_users' => Scenario::STATUS_COMPLETED,
			'error_users' => Scenario::STATUS_ERROR,
			'paused_users' => Scenario::STATUS_PAUSED
		];

		$status = $status_map[$column_name];

		// JSONカラムからステータスごとのカウントを取得するクエリ
		$query = $wpdb->prepare(
			"SELECT COUNT(*) as count 
			FROM {$table_name} 
			WHERE 
				JSON_EXTRACT(scenarios, '$.\"%d\".status') = %s",
			$post_id,
			$status
		);

		$count = $wpdb->get_var($query);
		echo intval($count);
	}
}
