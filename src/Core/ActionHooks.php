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
use Shipweb\LineConnect\Trigger\ActionHook;

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
					add_action( 'user_register', array( self::class, 'on_user_register' ), 10, 2 );
					break;
				case 'wp_login':
					add_action( 'wp_login', array( self::class, 'on_wp_login' ), 10, 2 );
					break;
				case 'wp_logout':
					add_action( 'wp_logout', array( self::class, 'on_wp_logout' ), 10, 1 );
					break;
				case 'profile_update':
					add_action( 'profile_update', array( self::class, 'on_profile_update' ), 10, 3 );
					break;
				case 'delete_user':
					add_action( 'delete_user', array( self::class, 'on_delete_user' ), 10, 3 );
					break;
				case 'save_post':
					// save_post の引数は環境により 2 or 3 だが、ここでは 3 を受け取る想定で登録
					add_action( 'save_post', array( self::class, 'on_save_post' ), 10, 3 );
					break;
				case 'comment_post':
					add_action( 'comment_post', array( self::class, 'on_comment_post' ), 10, 3 );
					break;
				case 'activated_plugin':
					add_action( 'activated_plugin', array( self::class, 'on_activated_plugin' ), 10, 2 );
					break;
				case 'deactivated_plugin':
					add_action( 'deactivated_plugin', array( self::class, 'on_deactivated_plugin' ), 10, 2 );
					break;
				case 'switch_theme':
					add_action( 'switch_theme', array( self::class, 'on_switch_theme' ), 10, 3 );
					break;
				default:
					add_action( $hook, array( self::class, 'handle_general_hook' ), 10, 99 );
					break;
			}
		}

		add_action( LineConnect::ACTION_PREFIX . 'custom_hook', array( self::class, 'handle_custom_hook' ), 10, 2 );
	}

	/**
	 * ユーザー登録後
	 */
	public static function on_user_register( $user_id, $userdata ) {
		try {
			self::dispatch(
				'user_register',
				array(
					'user_id'  => $user_id,
					'userdata' => $userdata,
				)
			);
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_user_register] ' . $e->getMessage() );
		}
	}

	public static function on_wp_login( $user_login, $user ) {
		try {
			self::dispatch(
				'wp_login',
				array(
					'user_login' => $user_login,
					'user'       => $user,
				)
			);
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_wp_login] ' . $e->getMessage() );
		}
	}

	public static function on_wp_logout( $user_id ) {
		try {
			self::dispatch( 'wp_logout', array( 'user_id' => $user_id ) );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_wp_logout] ' . $e->getMessage() );
		}
	}

	public static function on_profile_update( $user_id, $old_user_data, $userdata ) {
		try {
			self::dispatch(
				'profile_update',
				array(
					'user_id'       => $user_id,
					'old_user_data' => $old_user_data,
					'userdata'      => $userdata,
				)
			);
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_profile_update] ' . $e->getMessage() );
		}
	}

	public static function on_delete_user( $id, $reassign, $user ) {
		try {
			self::dispatch(
				'delete_user',
				array(
					'id'       => $id,
					'reassign' => $reassign,
					'user'     => $user,
				)
			);
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_delete_user] ' . $e->getMessage() );
		}
	}

	public static function on_save_post( $post_id, $post = null, $update = null ) {
		try {
			self::dispatch(
				'save_post',
				array(
					'post_id' => $post_id,
					'post'    => $post,
					'update'  => $update,
				)
			);
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_save_post] ' . $e->getMessage() );
		}
	}

	public static function on_comment_post( $comment_id, $comment_approved, $commentdata ) {
		try {
			self::dispatch(
				'comment_post',
				array(
					'comment_id'       => $comment_id,
					'comment_approved' => $comment_approved,
					'commentdata'      => $commentdata,
				)
			);
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_comment_post] ' . $e->getMessage() );
		}
	}

	public static function on_activated_plugin( $plugin, $network_wide ) {
		try {
			self::dispatch(
				'activated_plugin',
				array(
					'plugin'       => $plugin,
					'network_wide' => $network_wide,

				)
			);
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_activated_plugin] ' . $e->getMessage() );
		}
	}

	public static function on_deactivated_plugin( $plugin, $network_wide ) {
		try {
			self::dispatch(
				'deactivated_plugin',
				array(
					'plugin'       => $plugin,
					'network_wide' => $network_wide,
				)
			);
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_deactivated_plugin] ' . $e->getMessage() );
		}
	}

	public static function on_switch_theme( $new_name, $new_theme, $old_theme ) {
		try {
			self::dispatch(
				'switch_theme',
				array(
					'new_name'  => $new_name,
					'new_theme' => $new_theme,
					'old_theme' => $old_theme,
				)
			);
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::on_switch_theme] ' . $e->getMessage() );
		}
	}

	/**
	 * カスタムフックハンドラ（名前付き引数）
	 */
	public static function handle_custom_hook( string $hook_name, array $args = array() ): void {
		$action_hook_args = self::normalize_hook_args( $hook_name, $args, false );
		self::dispatch( $hook_name, $action_hook_args );
	}

	/**
	 * 一般ハンドラ（カスタムフック用）
	 */
	public static function handle_general_hook( ...$args ): void {
		try {
			$hook_name        = current_filter();
			$action_hook_args = self::normalize_hook_args( $hook_name, $args );
			self::dispatch( $hook_name, $action_hook_args );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::handle_general_hook] ' . $e->getMessage() );
		}
	}

	/**
	 * フック引数を正規化する。
	 *
	 * @param string $hook フック名。
	 * @param array  $args 引数配列。
	 * @param bool   $numeric 数値添字を強制するか（一般ハンドラ用）。
	 * @return array
	 */
	protected static function normalize_hook_args( $hook, array $args, bool $numeric = true ): array {
		return array(
			'hook_name' => $hook,
			'args'      => $numeric ? array_values( $args ) : $args,
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
		error_log( '[ActionHooks::dispatch] Dispatching hook: ' . $hook_name . ' with args: ' . json_encode( $args ) );
		try {
			ActionHook::process( $payload );
		} catch ( \Throwable $e ) {
			error_log( '[ActionHooks::dispatch] process error: ' . $e->getMessage() );
		}
	}
}
