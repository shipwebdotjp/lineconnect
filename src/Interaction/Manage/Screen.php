<?php

/**
 * Lineconnect
 * 管理画面ダッシュボード
 */

namespace Shipweb\LineConnect\Interaction\Manage;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction;

class Screen {
	static function initialize() {
	}

	/**
	 * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
	 * 管理画面のトップメニューページを追加する関数
	 */
	static function set_plugin_menu() {
		// 設定のサブメニュー「LINE Connect」を追加
		$page_hook_suffix = add_submenu_page(
			lineconnect::SLUG__DASHBOARD,
			// ページタイトル：
			__('LINE Connect Interaction Sessions', lineconnect::PLUGIN_NAME),
			// メニュータイトル：
			__('Sessions', lineconnect::PLUGIN_NAME),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			lineconnect::SLUG__SESSION,
			// メニューに紐づく画面を描画するcallback関数：
			array(\Shipweb\LineConnect\Interaction\Manage\Screen::class, 'show_page'),
			100
		);
		add_action("admin_print_styles-{$page_hook_suffix}", [\Shipweb\LineConnect\Interaction\Manage\Screen::class, 'wpdocs_plugin_admin_styles']);
		add_action("admin_print_scripts-{$page_hook_suffix}", [\Shipweb\LineConnect\Interaction\Manage\Screen::class, 'wpdocs_plugin_admin_scripts']);
		// remove_submenu_page(lineconnect::SLUG__DASHBOARD, lineconnect::SLUG__SESSION);
	}

	/**
	 * 初期設定画面を表示
	 */
	static function show_page() {
		echo <<< EOM
		<div id="lineconnect-interaction-root"></div>
EOM;
	}

	// 管理画面用にスクリプト読み込み
	static function wpdocs_plugin_admin_scripts() {
		$js = 'frontend/' . Interaction::NAME . '/dist/slc_' . Interaction::NAME . '.js';
		wp_enqueue_script(lineconnect::PLUGIN_PREFIX . Interaction::NAME, plugins_url($js, LineConnect::getRootDir() . lineconnect::PLUGIN_ENTRY_FILE_NAME), array('wp-element', 'wp-i18n'), filemtime(LineConnect::getRootDir() . $js), true);
		wp_set_script_translations(lineconnect::PLUGIN_PREFIX . Interaction::NAME, lineconnect::PLUGIN_NAME, LineConnect::getRootDir() . 'frontend/' . Interaction::NAME . '/languages');

		// Add configuration data for the JavaScript
		wp_localize_script(
			lineconnect::PLUGIN_PREFIX . Interaction::NAME,
			'lineConnectConfig',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'ajax_nonce' => wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST),
				'pluginSlug' => lineconnect::PLUGIN_NAME,
				'rest_url' => esc_url_raw(rest_url()),
				'rest_nonce' => wp_create_nonce('wp_rest'),
				'interaction_session_download_url' => admin_url('admin-post.php?action=' . lineconnect::SLUG__INTERACTION_SESSION_DOWNLOAD),
			)
		);
	}

	// 管理画面用にスタイル読み込み
	static function wpdocs_plugin_admin_styles() {
		$css = 'frontend/' . Interaction::NAME . '/dist/style.css';
		wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'admin-css', plugins_url($css, LineConnect::getRootDir() . lineconnect::PLUGIN_ENTRY_FILE_NAME), array(), filemtime(LineConnect::getRootDir() . $css));
	}

	static function inject_css() {
		echo '<style>
		ul.wp-submenu li a[href*="'.lineconnect::SLUG__SESSION.'"] {
			display: none !important;
		}
		</style>';
	}
}
