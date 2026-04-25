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

use Shipweb\LineConnect\Action\Action;
use Shipweb\LineConnect\ActionFlow\ActionFlow;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Core\UserProvider;
use Shipweb\LineConnect\PostType\Audience\Audience;
use Shipweb\LineConnect\PostType\Trigger\Trigger as TriggerPostType;

class ActionHook {
	/**
	 * 配列引数から連想配列キーを優先して値を取得する。
	 *
	 * 既存の数値添字形式も後方互換のために受け付ける。
	 *
	 * @param array      $args         引数配列。
	 * @param string     $key          優先する連想配列キー。
	 * @param int|null   $fallback_key 数値添字のフォールバックキー。
	 * @param mixed|null $default      取得できない場合の既定値。
	 * @return mixed
	 */
	protected static function get_arg_value( array $args, $key, $fallback_key = null, $default = null ) {
		if ( array_key_exists( $key, $args ) ) {
			return $args[ $key ];
		}

		if ( null !== $fallback_key && array_key_exists( $fallback_key, $args ) ) {
			return $args[ $fallback_key ];
		}

		return $default;
	}

	/**
	 * 配列条件のいずれかに一致するか判定する。
	 *
	 * @param array $candidates 判定対象の値一覧。
	 * @param array $targets    条件値一覧。
	 * @param bool  $strict     厳密比較を行うか。
	 * @return bool
	 */
	protected static function matches_any_value( array $candidates, array $targets, bool $strict = true ): bool {
		if ( empty( $candidates ) || empty( $targets ) ) {
			return false;
		}

		foreach ( $candidates as $candidate ) {
			if ( in_array( $candidate, $targets, $strict ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * ユーザーIDからロール一覧を取得する。
	 *
	 * @param int $user_id ユーザーID。
	 * @return array
	 */
	protected static function get_user_roles_by_id( $user_id ): array {
		$user = get_userdata( absint( $user_id ) );
		if ( empty( $user ) || ! is_object( $user ) || ! property_exists( $user, 'roles' ) || ! is_array( $user->roles ) ) {
			return array();
		}

		return $user->roles;
	}

	/**
	 * ユーザーオブジェクトからロール一覧を取得する。
	 *
	 * @param mixed $user ユーザーオブジェクト。
	 * @return array
	 */
	protected static function get_user_roles_from_user( $user ): array {
		if ( empty( $user ) || ! is_object( $user ) || ! property_exists( $user, 'roles' ) || ! is_array( $user->roles ) ) {
			return array();
		}

		return $user->roles;
	}

	/**
	 * トリガー設定配列から対象値を取得する。
	 *
	 * @param array  $trigger トリガー設定。
	 * @param string $hook    フック名。
	 * @param string $key     設定キー。
	 * @return array
	 */
	protected static function get_trigger_values( array $trigger, $hook, $key ): array {
		if ( empty( $trigger[ $hook ][ $key ] ) || ! is_array( $trigger[ $hook ][ $key ] ) ) {
			return array();
		}

		return $trigger[ $hook ][ $key ];
	}

	/**
	 * 設定ロールとユーザーのロールが一致するか判定する。
	 *
	 * @param array $configured_roles 設定ロール一覧。
	 * @param array $user_roles       ユーザーロール一覧。
	 * @return bool
	 */
	protected static function matches_user_roles( array $configured_roles, array $user_roles ): bool {
		if ( empty( $configured_roles ) ) {
			return true;
		}

		return static::matches_any_value( $user_roles, $configured_roles, true );
	}

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
			// error_log( '[ActionHook::process] missing hook name' );
			return false;
		}

		try {
			$hook_name       = $action_hook_args['hook'];
			$trigger_entries = static::get_action_hook_triggers( $hook_name, $action_hook_args );

			if ( empty( $trigger_entries ) ) {
				// error_log( '[ActionHook::process] no triggers found for hook: ' . $hook_name );
				return false;
			}

			$has_executed = false;

			foreach ( $trigger_entries as $trigger_definition ) {
				$trigger_conditions = isset( $trigger_definition['triggers'] ) && is_array( $trigger_definition['triggers'] ) ? $trigger_definition['triggers'] : array();

				if ( empty( $trigger_conditions ) ) {
					error_log( '[ActionHook::process] no valid triggers in entry for hook: ' . $hook_name );
					continue;
				}
				$matched_trigger_condition = null;

				$trigger_action_hook_args = $action_hook_args;

				foreach ( $trigger_conditions as $trigger_condition ) {
					// 条件チェック（デフォルト true）
					if ( static::check_condition( $trigger_action_hook_args, $trigger_condition ) ) {
						$matched_trigger_condition = $trigger_condition;
						break;
					}
				}

				if ( empty( $matched_trigger_condition ) ) {
					error_log( '[ActionHook::process] no matched triggers in entry for hook: ' . $hook_name );
					continue;
				}

				// error_log( '[ActionHook::process] conditions matched for hook: ' . $hook_name );

				// audience 条件構築（デフォルト []）
				$audience_condition = static::build_audience_condition( $trigger_action_hook_args, $trigger_definition );

				// error_log( '[ActionHook::process] audience condition for hook: ' . $hook_name . ' condition: ' . json_encode( $audience_condition ) );
				// audience がなければ直接 Action を実行する
				if ( empty( $audience_condition ) ) {
					static::execute_direct_action( $trigger_definition, $trigger_action_hook_args );
					$has_executed = true;
					continue;
				}

				$recepient = static::get_audience_by_condition( $audience_condition );

				// error_log( '[ActionHook::process] audience for hook: ' . $hook_name . ' recepient: ' . json_encode( $recepient ) );

				if ( empty( $recepient ) ) {
					static::execute_direct_action( $trigger_definition, $trigger_action_hook_args );
				} else {
					static::execute_audience_actionflow( $trigger_definition, $recepient, $trigger_action_hook_args );
				}

				$has_executed = true;
			}

			return $has_executed;
		} catch ( \Throwable $e ) {
			error_log( '[ActionHook::process] ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * action_hook のトリガー投稿を取得する。
	 *
	 * @param string $hook_name フック名。
	 * @param array  $action_hook_args 元の引数。
	 * @return array<int,array{post_id:int,trigger:array}>
	 */
	protected static function get_action_hook_triggers( $hook_name, array $action_hook_args = array() ): array {
		if ( isset( $action_hook_args['trigger'] ) && is_array( $action_hook_args['trigger'] ) ) {
			$trigger_definition = $action_hook_args['trigger'];
			if ( isset( $trigger_definition['hook'] ) && $hook_name === $trigger_definition['hook'] ) {
				return array(
					array(
						'post_id' => 0,
						'trigger' => $trigger_definition,
					),
				);
			}
		}

		$trigger_entries = array();
		$posts           = get_posts(
			array(
				'post_type'      => TriggerPostType::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		foreach ( $posts as $post ) {
			$trigger_form = get_post_meta( $post->ID, TriggerPostType::META_KEY_DATA, true );
			if ( ! is_array( $trigger_form ) ) {
				continue;
			}
			if ( empty( $trigger_form[0]['type'] ) || 'action_hook' !== $trigger_form[0]['type'] ) {
				continue;
			}
			if ( empty( $trigger_form[1] ) || ! is_array( $trigger_form[1] ) ) {
				continue;
			}

			$trigger_entries[] = $trigger_form[1];
		}

		return $trigger_entries;
	}

	/**
	 * audience ありのアクションフローを実行する。
	 *
	 * @param array $trigger_definition トリガー設定。
	 * @param array $recepient オーディエンス。
	 * @param array $action_hook_args action hook 引数。
	 * @return mixed
	 */
	protected static function execute_audience_actionflow( array $trigger_definition, array $recepient, array $action_hook_args = array() ) {
		$action_flow = array(
			'actions' => $trigger_definition['action'] ?? array(),
			'chains'  => $trigger_definition['chain'] ?? array(),
		);

		return ActionFlow::execute_actionflow_by_audience( $action_flow, $recepient, $action_hook_args );
	}

	/**
	 * audience なしの直接アクションを実行する。
	 *
	 * @param array $trigger_definition トリガー設定。
	 * @param array $action_hook_args action hook 引数。
	 * @return mixed
	 */
	protected static function execute_direct_action( array $trigger_definition, array $action_hook_args = array() ) {
		return Action::do_action( $trigger_definition['action'] ?? array(), $trigger_definition['chain'] ?? null, null, null, null, null, $action_hook_args );
	}

	/**
	 * audience 条件から受信者を取得する。
	 *
	 * テストではこのメソッドをオーバーライドして分岐を検証できる。
	 *
	 * @param array $condition audience 条件。
	 * @return array
	 */
	protected static function get_audience_by_condition( array $condition ): array {
		return Audience::get_audience_by_condition( $condition );
	}

	/**
	 * 条件チェックのスタブ
	 *
	 * @param array $action_hook_args
	 * @param array $trigger_condition トリガー条件
	 * @return bool
	 */
	public static function check_condition( array $action_hook_args, array $trigger_condition ): bool {
		try {
			$hook = isset( $action_hook_args['hook'] ) ? $action_hook_args['hook'] : '';
			$args = isset( $action_hook_args['args'] ) && is_array( $action_hook_args['args'] ) ? $action_hook_args['args'] : array();

			if ( empty( $trigger_condition['hook'] ) || $trigger_condition['hook'] !== $hook ) {
				return false;
			}

			switch ( $hook ) {
				case 'user_register':
				case 'profile_update':
				case 'delete_user':
					$cfg_roles = static::get_trigger_values( $trigger_condition, $hook, 'role' );

					if ( empty( $cfg_roles ) ) {
						return true;
					}

					$user_id = 0;
					if ( 'delete_user' === $hook ) {
						$user_obj = self::get_arg_value( $args, 'user', 2, null );
						if ( is_object( $user_obj ) ) {
							return static::matches_user_roles( $cfg_roles, static::get_user_roles_from_user( $user_obj ) );
						}

						$user_id = absint( self::get_arg_value( $args, 'id', 0, 0 ) );
					} else {
						$user_id = absint( self::get_arg_value( $args, 'user_id', 0, 0 ) );
					}

					if ( empty( $user_id ) ) {
						return false;
					}

					return static::matches_user_roles( $cfg_roles, static::get_user_roles_by_id( $user_id ) );

				case 'save_post':
					$post_id = absint( self::get_arg_value( $args, 'post_id', 0, 0 ) );
					$post    = self::get_arg_value( $args, 'post', 1, null );
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
					$cfg_post_types  = $trigger_condition['save_post']['post_type'] ?? null;
					$cfg_post_status = $trigger_condition['save_post']['post_status'] ?? null;

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
					$comment_id = absint( self::get_arg_value( $args, 'comment_id', 0, 0 ) );
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
					$cfg_post_types = $trigger_condition['comment_post']['post_type'] ?? null;
					if ( is_array( $cfg_post_types ) && count( $cfg_post_types ) > 0 ) {
						return in_array( $post->post_type, $cfg_post_types, true );
					}

					// デフォルトは全件対象
					return true;

				case 'wp_login':
					// 引数: ($user_login, $user)
					$user_obj = null;
					$user_arg = self::get_arg_value( $args, 'user', 1, null );
					if ( is_object( $user_arg ) ) {
						$user_obj = $user_arg;
					} else {
						$user_login = self::get_arg_value( $args, 'user_login', 0, null );
						if ( is_string( $user_login ) ) {
							$user_obj = get_user_by( 'login', $user_login );
						}
					}

					if ( empty( $user_obj ) ) {
						// ユーザー情報が取れない場合は限定せず true を返す
						return true;
					}

					$cfg_roles = static::get_trigger_values( $trigger_condition, 'wp_login', 'role' );
					if ( is_array( $cfg_roles ) && count( $cfg_roles ) > 0 ) {
						$user_roles = property_exists( $user_obj, 'roles' ) && is_array( $user_obj->roles ) ? $user_obj->roles : array();
						return static::matches_user_roles( $cfg_roles, $user_roles );
					}

					// デフォルトは全件対象
					return true;

				case 'wp_logout':
					$cfg_roles = static::get_trigger_values( $trigger_condition, 'wp_logout', 'role' );
					if ( empty( $cfg_roles ) ) {
						return true;
					}

					$target_user_id = absint( self::get_arg_value( $args, 'user_id', 0, 0 ) );
					if ( empty( $target_user_id ) ) {
						$target_user_id = UserProvider::get_current_user_id();
					}

					if ( empty( $target_user_id ) ) {
						return false;
					}

					return static::matches_user_roles( $cfg_roles, static::get_user_roles_by_id( $target_user_id ) );

				case 'activated_plugin':
					$cfg_plugins = static::get_trigger_values( $trigger_condition, 'activated_plugin', 'plugin' );
					if ( empty( $cfg_plugins ) ) {
						return true;
					}

					$plugin = (string) self::get_arg_value( $args, 'plugin', 0, '' );

					return static::matches_any_value( array( $plugin ), $cfg_plugins, true );

				case 'deactivated_plugin':
					$cfg_plugins = static::get_trigger_values( $trigger_condition, 'deactivated_plugin', 'plugin' );
					if ( empty( $cfg_plugins ) ) {
						return true;
					}

					$plugin = (string) self::get_arg_value( $args, 'plugin', 0, '' );

					return static::matches_any_value( array( $plugin ), $cfg_plugins, true );

				case 'switch_theme':
					$cfg_themes = static::get_trigger_values( $trigger_condition, 'switch_theme', 'theme' );
					if ( empty( $cfg_themes ) ) {
						return true;
					}

					$theme_name = (string) self::get_arg_value( $args, 'new_name', 0, '' );

					return static::matches_any_value( array( $theme_name ), $cfg_themes, true );

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
				case 'wp_logout':
					return absint( self::get_arg_value( $args, 'user_id', 0, 0 ) );

				case 'delete_user':
				case 'wp_login':
					$user = self::get_arg_value( $args, 'user', 1, null );
					if ( is_object( $user ) && isset( $user->ID ) ) {
						return absint( $user->ID );
					}
					return 0;

				case 'activated_plugin':
				case 'deactivated_plugin':
				case 'switch_theme':
					return UserProvider::get_current_user_id();

				case 'save_post':
					$post = self::get_arg_value( $args, 'post', 1, null );
					if ( is_object( $post ) && isset( $post->post_author ) ) {
						return absint( $post->post_author );
					}
					$post_id = absint( self::get_arg_value( $args, 'post_id', 0, 0 ) );
					if ( $post_id ) {
						if ( $post_id ) {
							$post = get_post( $post_id );
							if ( $post && ! empty( $post->post_author ) ) {
								return absint( $post->post_author );
							}
						}
					}
					return 0;

				case 'comment_post':
					$comment_id = absint( self::get_arg_value( $args, 'comment_id', 0, 0 ) );
					if ( $comment_id ) {
						$comment = get_comment( $comment_id );
						if ( $comment && ! empty( $comment->user_id ) ) {
							return absint( $comment->user_id );
						}
					}

					$comment_context = self::get_arg_value( $args, 'comment_context', 3, array() );
					if ( is_array( $comment_context ) && ! empty( $comment_context['user_id'] ) ) {
						return absint( $comment_context['user_id'] );
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
	 * @param array $action_hook_args
	 * @param array $trigger_definition トリガー設定
	 * @return array
	 */
	public static function build_audience_condition( array $action_hook_args, $trigger_definition = null ): array {
		$audience_mode = isset( $trigger_definition['audience_mode'] ) ? $trigger_definition['audience_mode'] : '';

		if ( 'standard' === $audience_mode ) {
			if ( isset( $trigger_definition['audience']['condition'] ) && is_array( $trigger_definition['audience']['condition'] ) ) {
				return $trigger_definition['audience']['condition'];
			}

			return array();
		}

		if ( 'current_user' !== $audience_mode ) {
			return array();
		}

		// 関連ユーザー解決（デフォルト 0）
		$related_user_id = static::resolve_related_user( $action_hook_args );

		$related_user_id = absint( $related_user_id );
		if ( empty( $related_user_id ) ) {
			return array();
		}

		$conditions = array(
			array(
				'type'     => 'wpUserId',
				'wpUserId' => array( $related_user_id ),
			),
		);

		$current_user_channels = isset( $trigger_definition['current_user_channels'] ) && is_array( $trigger_definition['current_user_channels'] )
			? array_values( array_filter( $trigger_definition['current_user_channels'] ) )
			: array();

		if ( ! empty( $current_user_channels ) ) {
			$conditions[] = array(
				'type'          => 'channel',
				'secret_prefix' => $current_user_channels,
			);
		}

		return array(
			'conditions' => $conditions,
			'operator'   => 'and',
		);
	}
}
