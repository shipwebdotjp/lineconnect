<?php

/**
 * Lineconnect
 * 管理画面でのLINEメッセージ画面
 */
class lineconnectDashboard {
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
			__( 'LINE Connect Dashboard', lineconnect::PLUGIN_NAME ),
			// メニュータイトル：
			__( 'LINE Connect', lineconnect::PLUGIN_NAME ),
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
			array( 'lineconnectDashboard', 'show_dashboard' ),
			'dashicons-email-alt'
		);
		// add_action("admin_print_styles-{$page_hook_suffix}", ['lineconnectDashboard', 'wpdocs_plugin_admin_styles']);
		// add_action("admin_print_scripts-{$page_hook_suffix}", ['lineconnectDashboard', 'wpdocs_plugin_admin_scripts']);
	}

	/**
	 * 初期設定画面を表示
	 */
	static function show_dashboard() {
		$update_available_message = self::get_update_notice();
		$title                    = __( 'LINE Connect Dashboard', lineconnect::PLUGIN_NAME );
		echo <<< EOM
		<h2>{$title}</h2>
		{$update_available_message}
EOM;
	}

	static function get_update_notice() {
		if ( self::my_plugin_check_for_updates() ) {
			$latest_release = self::get_latest_release();
			if ( $latest_release ) {
				$update_available_message = lineconnect::getNotice(
					sprintf(
						__( 'A new version %1$s is available. <a href="%2$s" target="_blank">Download from GitHub</a>', lineconnect::PLUGIN_NAME ),
						$latest_release['tag_name'],
						$latest_release['html_url']
					),
					lineconnect::NOTICE_TYPE__SUCCESS
				);
				return $update_available_message;
			}
		}
		return '';
	}

	static function my_plugin_get_latest_version() {
		// GitHub の API から最新のバージョンを取得する
		$response = wp_remote_get( 'https://api.github.com/repos/shipwebdotjp/lineconnect/tags' );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// レスポンスから最新のバージョンを取得する
		$tags           = json_decode( $response['body'], true );
		$latest_version = ltrim( $tags[0]['name'], 'vV' );

		return $latest_version;
	}

	static function my_plugin_check_for_updates() {
		// インストールされているバージョンを取得する
		$installed_version = lineconnect::get_current_plugin_version();

		// 最新のバージョンを取得する
		$latest_version = self::my_plugin_get_latest_version();

		// インストールされているバージョンよりも最新のバージョンが存在する場合は、更新通知を表示する
		if ( version_compare( $latest_version, $installed_version, '>' ) ) {
			// 更新通知を表示する
			return true;
		}
		return false;
	}

	static function get_latest_release() {
		$response = wp_remote_get( 'https://api.github.com/repos/shipwebdotjp/lineconnect/releases/latest' );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$release = json_decode( $response['body'], true );
		return $release;
	}


	// 管理画面用にスクリプト読み込み
	static function wpdocs_plugin_admin_scripts() {
		$dm_js = 'line-dm/dist/slc_dm.js';
		wp_enqueue_script( lineconnect::PLUGIN_PREFIX . 'dm', plugins_url( $dm_js, __DIR__ ), array( 'wp-element', 'wp-i18n' ), filemtime( plugin_dir_path( __DIR__ ) . $dm_js ), true );
		// JavaScriptの言語ファイル読み込み
		wp_set_script_translations( lineconnect::PLUGIN_PREFIX . 'dm', lineconnect::PLUGIN_NAME, plugin_dir_path( __DIR__ ) . 'languages' );
	}

	// 管理画面用にスタイル読み込み
	static function wpdocs_plugin_admin_styles() {
		$dm_css = 'line-dm/dist/style.css';
		wp_enqueue_style( lineconnect::PLUGIN_PREFIX . 'admin-css', plugins_url( $dm_css, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $dm_css ) );
	}
}
