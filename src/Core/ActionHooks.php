<?php

/**
 * Action Hooks 集約クラス (骨格)
 *
 * WordPress の主要なアクションフックをここで登録し、各ハンドラで受け取った引数を
 * トリガー処理層 (src/Trigger/ActionHook::process) に正規化して渡します。
 *
 * このファイルは骨格実装のため、個別ハンドラの詳細ロジックは含みません。
 *
 * @package Core
 */

namespace Shipweb\LineConnect\Core;

use Shipweb\LineConnect\Core\LineConnect;

class ActionHooks {

	/**
	 * register hooks
	 */
	public static function init() {
		// デフォルトのプリセットフック一覧
		$default_hooks = array(
			'user_register',
			'wp_login',
			'wp_logout',
			'profile_update',
			'delete_user',
			'save_post',
			'comment_post',
			'activated_plugin',
			'deactivated_plugin',
			'switch_theme',
		);

		// フィルタでプリセットを拡張可能にする
		$hooks = apply_filters( LineConnect::FILTER_PREFIX . 'predefined_action_hooks', $default_hooks );

		// 個別に適切なコールバックと引数数を登録
		foreach ( $hooks as $hook ) {
			switch ( $hook ) {
				case 'user_register':
					add_action( 'user_register', array( self::class, 'on_user_register' ), 10, 1 );
					break;
				case 'wp_login':
					add_action( 'wp_login', array( self::class, 'on_wp_login' ), 10, 2 );
					break;
				case 'wp_logout':
					add_action( 'wp_logout', array( self::class, 'on_wp_logout' ), 10, 0 );
					break;
				case 'profile_update':
					add_action( 'profile_update', array( self::class, 'on_profile_update' ), 10, 2 );
					break;
				case 'delete_user':
					add_action( 'delete_user', array( self::class, 'on_delete_user' ), 10, 1 );
					break;
				case 'save_post':
					// save_post の引数は環境により2 or 3だが、ここでは3を受け取る想定で登録
					add_action( 'save_post', array( self::class, 'on_save_post' ), 10, 3 );
					break;
				case 'comment_post':
					add_action( 'comment_post', array( self::class, 'on_comment_post' ), 10, 2 );
					break;
				case 'activated_plugin':
					add_action( 'activated_plugin', array( self::class, 'on_activated_plugin' ), 10, 1 );
					break;
				case 'deactivated_plugin':
					add_action( 'deactivated_plugin', array( self::class, 'on_deactivated_plugin' ), 10, 1 );
					break;
				case 'switch_theme':
					add_action( 'switch_theme', array( self::class, 'on_switch_theme' ), 10, 1 );
					break;
				default:
					// カスタムフックを文字列で渡された場合は汎用ハンドラを登録（引数は可変）
					add_action( $hook, array( self::class, 'handle_custom_hook' ), 10, 99 );
					break;
			}
		}

		add_action( 'lineconnect_custom_hook', array( self::class, 'handle_custom_hook' ), 10, 2 );
	}
	/**
	 * ユーザー登録後
	 */
	public static function on_user_register( $user_id ) {
		try {
			$action_hook_args = self::normalize_user_register_args( $user_id );
			self::dispatch( 'user_register', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_user_register] ' . $e->getMessage() );
		}
	}

	public static function on_wp_login( $user_login, $user ) {
		try {
			$action_hook_args = self::normalize_wp_login_args( $user_login, $user );
			self::dispatch( 'wp_login', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_wp_login] ' . $e->getMessage() );
		}
	}

	public static function on_wp_logout() {
		try {
			$action_hook_args = self::normalize_wp_logout_args();
			self::dispatch( 'wp_logout', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_wp_logout] ' . $e->getMessage() );
		}
	}

	public static function on_profile_update( $user_id, $old_user_data ) {
		try {
			$action_hook_args = self::normalize_profile_update_args( $user_id, $old_user_data );
			self::dispatch( 'profile_update', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_profile_update] ' . $e->getMessage() );
		}
	}

	public static function on_delete_user( $user_id ) {
		try {
			$action_hook_args = self::normalize_delete_user_args( $user_id );
			self::dispatch( 'delete_user', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_delete_user] ' . $e->getMessage() );
		}
	}

	public static function on_save_post( $post_id, $post = null, $update = null ) {
		try {
			$action_hook_args = self::normalize_save_post_args( $post_id, $post, $update );
			self::dispatch( 'save_post', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_save_post] ' . $e->getMessage() );
		}
	}

	public static function on_comment_post( $comment_id, $comment_approved ) {
		try {
			$action_hook_args = self::normalize_comment_post_args( $comment_id, $comment_approved );
			self::dispatch( 'comment_post', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_comment_post] ' . $e->getMessage() );
		}
	}

	public static function on_activated_plugin( $plugin ) {
		try {
			$action_hook_args = self::normalize_activated_plugin_args( $plugin );
			self::dispatch( 'activated_plugin', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_activated_plugin] ' . $e->getMessage() );
		}
	}

	public static function on_deactivated_plugin( $plugin ) {
		try {
			$action_hook_args = self::normalize_deactivated_plugin_args( $plugin );
			self::dispatch( 'deactivated_plugin', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_deactivated_plugin] ' . $e->getMessage() );
		}
	}

	public static function on_switch_theme( $new_name ) {
		try {
			$action_hook_args = self::normalize_switch_theme_args( $new_name );
			self::dispatch( 'switch_theme', $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_switch_theme] ' . $e->getMessage() );
		}
	}

	/**
	 * カスタムフックハンドラ（汎用）
	 */
	public static function handle_custom_hook( string $hook_name, array $args = array() ): void {
		$action_hook_args = array(
			'hook_name' => $hook_name,  // ← do_actionの第1引数が正しく入る
			'args'      => $args,
		);
		self::dispatch( $hook_name, $action_hook_args );
	}

	protected static function normalize_user_register_args( $user_id ): array {
		return array(
			'user_id' => $user_id,
		);
	}

	protected static function normalize_wp_login_args( $user_login, $user ): array {
		return array(
			'user_login' => $user_login,
			'user'       => $user,
		);
	}

	protected static function normalize_wp_logout_args(): array {
		return array();
	}

	protected static function normalize_profile_update_args( $user_id, $old_user_data ): array {
		return array(
			'user_id'       => $user_id,
			'old_user_data' => $old_user_data,
		);
	}

	protected static function normalize_delete_user_args( $user_id ): array {
		return array(
			'user_id' => $user_id,
		);
	}

	protected static function normalize_save_post_args( $post_id, $post = null, $update = null ): array {
		return array(
			'post_id' => $post_id,
			'post'    => $post,
			'update'  => $update,
		);
	}

	protected static function normalize_comment_post_args( $comment_id, $comment_approved ): array {
		return array(
			'comment_id'       => $comment_id,
			'comment_approved' => $comment_approved,
		);
	}

	protected static function normalize_activated_plugin_args( $plugin ): array {
		return array(
			'plugin' => $plugin,
		);
	}

	protected static function normalize_deactivated_plugin_args( $plugin ): array {
		return array(
			'plugin' => $plugin,
		);
	}

	protected static function normalize_switch_theme_args( $new_name ): array {
		return array(
			'new_name' => $new_name,
		);
	}

	protected static function normalize_custom_hook_args( $hook, array $args ): array {
		return array(
			'hook_name' => $hook,
			'args'      => $args,
		);
	}

	/**
	 * dispatch to Trigger/ActionHook::process
	 *
	 * @param string $hook_name
	 * @param array  $args
	 */
	protected static function dispatch( $hook_name, array $args = array() ) {
		$payload = array(
			'hook' => $hook_name,
			'args' => $args,
		);

		// 呼び出し先のクラスが存在すれば process を呼ぶ
		$target = '\\Shipweb\\LineConnect\\Trigger\\ActionHook';
		if ( class_exists( $target ) && is_callable( array( $target, 'process' ) ) ) {
			try {
				$target::process( $payload );
			} catch ( \Throwable $e ) {
				error_log( '[ActionHooks::dispatch] process error: ' . $e->getMessage() );
			}
		}
	}
}
