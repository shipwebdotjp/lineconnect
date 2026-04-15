<?php

/**
 * Trigger: ActionHook - 骨格
 *
 * ActionHooks から渡された payload を受け取り、条件評価・関連ユーザー解決・audience 構築
 * のための API を提供します。現段階では最小限のスタブ実装です。
 *
 * @package Trigger
 */

namespace Shipweb\LineConnect\Trigger;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Core\UserProvider;

class ActionHook {

	/**
	 * エントリポイント
	 *
	 * @param array $action_hook_args ['hook' => string, 'args' => array]
	 * @return bool 成功したら true
	 */
	public static function process( array $action_hook_args ) {
		// 外部に加工ポイントを提供
		$action_hook_args = apply_filters( LineConnect::FILTER_PREFIX . 'preprocess_action_hook', $action_hook_args );

		if ( ! isset( $action_hook_args['hook'] ) ) {
			error_log( '[ActionHook::process] missing hook name' );
			return false;
		}

		try {
			// 条件チェック（デフォルト true）
			$ok = self::check_condition( $action_hook_args );
			if ( ! $ok ) {
				return false;
			}

			// 関連ユーザー解決（デフォルト null）
			$related_user_id = self::resolve_related_user( $action_hook_args );

			// audience 条件構築（デフォルト []）
			$audience_condition = self::build_audience_condition( $action_hook_args, $related_user_id );

			// 将来的に ActionFlow / Action を呼び出す場所
			// 現在は骨格のためここでは実行しない

			return true;
		} catch ( \Throwable $e ) {
			error_log( '[ActionHook::process] ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * 条件チェックのスタブ
	 *
	 * @param array $action_hook_args
	 * @return bool
	 */
	public static function check_condition( array $action_hook_args ): bool {
		try {
			$hook = isset( $action_hook_args['hook'] ) ? $action_hook_args['hook'] : '';
			$args = isset( $action_hook_args['args'] ) && is_array( $action_hook_args['args'] ) ? $action_hook_args['args'] : array();
			// トリガーの設定が渡される場合に備える（process から trigger 情報を付与する想定）
			$trigger = isset( $action_hook_args['trigger'] ) && is_array( $action_hook_args['trigger'] ) ? $action_hook_args['trigger'] : null;

			switch ( $hook ) {
				case 'save_post':
					$post_id = isset( $args[0] ) ? intval( $args[0] ) : 0;
					$post    = $args[1] ?? null;
					// 引数に post オブジェクトがなければ取得する
					if ( is_null( $post ) && $post_id ) {
						$post = get_post( $post_id );
					}
					if ( empty( $post ) || empty( $post_id ) ) {
						// 評価できなければ実行しない
						return false;
					}

					// リビジョンは除外
					if ( function_exists( 'wp_is_post_revision' ) && wp_is_post_revision( $post_id ) ) {
						return false;
					}

					// autosave は除外
					if ( ( function_exists( 'wp_is_post_autosave' ) && wp_is_post_autosave( $post_id ) ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
						return false;
					}

					// トリガー設定があればそれを利用、なければドキュメントのデフォルトを適用
					$cfg_post_types  = $trigger['save_post']['post_type'] ?? null;
					$cfg_post_status = $trigger['save_post']['post_status'] ?? null;

					$post_type_ok = true;
					if ( is_array( $cfg_post_types ) ) {
						$post_type_ok = in_array( $post->post_type, $cfg_post_types, true );
					} else {
						// デフォルト: post, page
						$post_type_ok = in_array( $post->post_type, array( 'post', 'page' ), true );
					}

					$post_status    = get_post_status( $post_id );
					$post_status_ok = true;
					if ( is_array( $cfg_post_status ) ) {
						$post_status_ok = in_array( $post_status, $cfg_post_status, true );
					} else {
						// デフォルト: publish, draft, pending
						$post_status_ok = in_array( $post_status, array( 'publish', 'draft', 'pending' ), true );
					}

					return ( $post_type_ok && $post_status_ok );

				case 'comment_post':
					$comment_id = isset( $args[0] ) ? intval( $args[0] ) : 0;
					if ( empty( $comment_id ) ) {
						return false;
					}
					$comment = get_comment( $comment_id );
					if ( empty( $comment ) ) {
						return false;
					}
					$post = get_post( $comment->comment_post_ID );
					if ( empty( $post ) ) {
						return false;
					}

					// トリガー設定に post_type がある場合はフィルタする
					$cfg_post_types = $trigger['comment_post']['post_type'] ?? null;
					if ( is_array( $cfg_post_types ) && count( $cfg_post_types ) > 0 ) {
						return in_array( $post->post_type, $cfg_post_types, true );
					}

					// デフォルトは全件対象
					return true;

				case 'wp_login':
					// 引数: ($user_login, $user)
					$user_obj = null;
					if ( isset( $args[1] ) && is_object( $args[1] ) ) {
						$user_obj = $args[1];
					} elseif ( isset( $args[0] ) && is_string( $args[0] ) ) {
						$user_obj = get_user_by( 'login', $args[0] );
					}

					if ( empty( $user_obj ) ) {
						// ユーザー情報が取れない場合は限定せず true を返す
						return true;
					}

					$cfg_roles = $trigger['wp_login']['role'] ?? null;
					if ( is_array( $cfg_roles ) && count( $cfg_roles ) > 0 ) {
						$user_roles = property_exists( $user_obj, 'roles' ) ? $user_obj->roles : array();
						foreach ( $user_roles as $r ) {
							if ( in_array( $r, $cfg_roles, true ) ) {
								return true;
							}
						}
						return false;
					}

					// デフォルトは全件対象
					return true;

				default:
					// その他のフックはデフォルトで実行
					return true;
			}
		} catch ( \Throwable $e ) {
			error_log( '[ActionHook::check_condition] ' . $e->getMessage() );
			return false;
		}
	}
	/**
	 * 関連ユーザーの推論（スタブ）
	 *
	 * @param array $action_hook_args
	 * @return int user_id または 0
	 */
	public static function resolve_related_user( array $action_hook_args ): int {
		$hook = isset( $action_hook_args['hook'] ) ? $action_hook_args['hook'] : '';
		$args = isset( $action_hook_args['args'] ) && is_array( $action_hook_args['args'] )
			? $action_hook_args['args']
			: array();

		try {
			switch ( $hook ) {
				case 'user_register':
				case 'profile_update':
				case 'delete_user':
					return isset( $args[0] ) ? absint( $args[0] ) : 0;

				case 'wp_login':
					if ( isset( $args[1] ) && is_object( $args[1] ) && isset( $args[1]->ID ) ) {
						return absint( $args[1]->ID );
					}
					return 0;

				case 'wp_logout':
				case 'activated_plugin':
				case 'deactivated_plugin':
				case 'switch_theme':
					return UserProvider::get_current_user_id();

				case 'save_post':
					if ( isset( $args[1] ) && is_object( $args[1] ) && isset( $args[1]->post_author ) ) {
						return absint( $args[1]->post_author );
					}
					if ( isset( $args[0] ) ) {
						$post_id = absint( $args[0] );
						if ( $post_id ) {
							$post = get_post( $post_id );
							if ( $post && ! empty( $post->post_author ) ) {
								return absint( $post->post_author );
							}
						}
					}
					return 0;

				case 'comment_post':
					if ( isset( $args[0] ) ) {
						$comment_id = absint( $args[0] );
						$comment    = get_comment( $comment_id );
						if ( $comment && ! empty( $comment->user_id ) ) {
							return absint( $comment->user_id );
						}
					}

					if ( isset( $args[3] ) && is_array( $args[3] ) ) {
						if ( ! empty( $args[3]['user_id'] ) ) {
							return absint( $args[3]['user_id'] );
						}
					}

					return 0;

				default:
					return UserProvider::get_current_user_id();
			}
		} catch ( \Throwable $e ) {
			error_log( '[ActionHook::resolve_related_user] ' . $e->getMessage() );
			return 0;
		}
	}
	/**
	 * audience 条件構築のスタブ
	 *
	 * @param array    $action_hook_args
	 * @param int|null $related_user_id
	 * @return array
	 */
	public static function build_audience_condition( array $action_hook_args, $related_user_id = null ): array {
		// TODO: current_user モードや standard モードの分岐を実装
		return array();
	}
}
