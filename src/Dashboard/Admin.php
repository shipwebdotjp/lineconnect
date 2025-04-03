<?php

/**
 * Lineconnect
 * 管理画面ダッシュボード
 */

namespace Shipweb\LineConnect\Dashboard;

use \LineConnect;
use \lineconnectUtil;
use Shipweb\LineConnect\Core\Stats;

class Admin {
	static function initialize() {
	}

	/**
	 * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
	 * 管理画面のトップメニューページを追加する関数
	 */
	static function set_plugin_menu() {
		// 設定のサブメニュー「LINE Connect」を追加
		$page_hook_suffix = add_menu_page(
			// ページタイトル：
			__('LINE Connect Dashboard', lineconnect::PLUGIN_NAME),
			// メニュータイトル：
			__('LINE Connect', lineconnect::PLUGIN_NAME),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			lineconnect::SLUG__DASHBOARD,
			// メニューに紐づく画面を描画するcallback関数：
			array(\Shipweb\LineConnect\Dashboard\Admin::class, 'show_dashboard'),
			'dashicons-email-alt',
			NULL
		);
		add_action("admin_print_styles-{$page_hook_suffix}", [\Shipweb\LineConnect\Dashboard\Admin::class, 'wpdocs_plugin_admin_styles']);
		add_action("admin_print_scripts-{$page_hook_suffix}", [\Shipweb\LineConnect\Dashboard\Admin::class, 'wpdocs_plugin_admin_scripts']);
	}

	/**
	 * 初期設定画面を表示
	 */
	static function show_dashboard() {
		$update_available_message = Dashboard::get_update_notice();
		// $title                    = __('LINE Connect Dashboard', LineConnect::PLUGIN_NAME);
		echo <<< EOM
		{$update_available_message}
		<div id="lineconnect-dashboard-root"></div>
EOM;
	}

	// 管理画面用にスクリプト読み込み
	static function wpdocs_plugin_admin_scripts() {
		$js = 'frontend/' . Dashboard::NAME . '/dist/slc_' . Dashboard::NAME . '.js';
		wp_enqueue_script(lineconnect::PLUGIN_PREFIX . Dashboard::NAME, plugins_url($js, LineConnect::getRootDir() . lineconnect::PLUGIN_ENTRY_FILE_NAME), array('wp-element', 'wp-i18n'), filemtime(LineConnect::getRootDir() . $js), true);
		wp_set_script_translations(lineconnect::PLUGIN_PREFIX . Dashboard::NAME, lineconnect::PLUGIN_NAME, LineConnect::getRootDir() . 'frontend/' . Dashboard::NAME . '/languages');

		// Add configuration data for the JavaScript
		wp_localize_script(
			lineconnect::PLUGIN_PREFIX . Dashboard::NAME,
			'lineConnectConfig',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'ajax_nonce' => wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST),
				'pluginSlug' => lineconnect::PLUGIN_NAME
			)
		);
	}

	// 管理画面用にスタイル読み込み
	static function wpdocs_plugin_admin_styles() {
		$css = 'frontend/' . Dashboard::NAME . '/dist/style.css';
		wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'admin-css', plugins_url($css, LineConnect::getRootDir() . lineconnect::PLUGIN_ENTRY_FILE_NAME), array(), filemtime(LineConnect::getRootDir() . $css));
		// $override_css_file = 'react-jsonschema-form/dist/rjsf-override.css';
		// wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'rjsf-override-css', plugins_url($override_css_file, LineConnect::getRootDir() . lineconnect::PLUGIN_ENTRY_FILE_NAME), array(), filemtime(LineConnect::getRootDir() . $override_css_file));
	}

	public static function ajax_get_dashboard() {
		header('Content-Type: application/json; charset=utf-8');
		$result = lineconnectUtil::check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST);
		if ($result['result'] === 'failed') {
			echo json_encode($result);
			wp_die();
		}
		$period = isset($_POST['period']) ? stripslashes($_POST['period']) : "monthly";
		$channel_prefix = isset($_POST['channel_prefix']) ? stripslashes($_POST['channel_prefix']) : "";
		$ym = isset($_POST['ym']) ? stripslashes($_POST['ym']) : wp_date('Y-m');
		if ($period === 'monthly') {
			$data = Stats::get_monthly_summary($ym);
		} else {
			$data = Stats::get_daily_summary($ym, $channel_prefix);
		}
		echo json_encode($data);
		wp_die();
	}
}
