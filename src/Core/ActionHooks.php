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
        $default_hooks = [
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
        ];

        // フィルタでプリセットを拡張可能にする
        $hooks = apply_filters(LineConnect::FILTER_PREFIX . 'predefined_action_hooks', $default_hooks);

        // 個別に適切なコールバックと引数数を登録
        foreach ($hooks as $hook) {
            switch ($hook) {
                case 'user_register':
                    add_action('user_register', [self::class, 'on_user_register'], 10, 1);
                    break;
                case 'wp_login':
                    add_action('wp_login', [self::class, 'on_wp_login'], 10, 2);
                    break;
                case 'wp_logout':
                    add_action('wp_logout', [self::class, 'on_wp_logout'], 10, 0);
                    break;
                case 'profile_update':
                    add_action('profile_update', [self::class, 'on_profile_update'], 10, 2);
                    break;
                case 'delete_user':
                    add_action('delete_user', [self::class, 'on_delete_user'], 10, 1);
                    break;
                case 'save_post':
                    // save_post の引数は環境により2 or 3だが、ここでは3を受け取る想定で登録
                    add_action('save_post', [self::class, 'on_save_post'], 10, 3);
                    break;
                case 'comment_post':
                    add_action('comment_post', [self::class, 'on_comment_post'], 10, 2);
                    break;
                case 'activated_plugin':
                    add_action('activated_plugin', [self::class, 'on_activated_plugin'], 10, 1);
                    break;
                case 'deactivated_plugin':
                    add_action('deactivated_plugin', [self::class, 'on_deactivated_plugin'], 10, 1);
                    break;
                case 'switch_theme':
                    add_action('switch_theme', [self::class, 'on_switch_theme'], 10, 1);
                    break;
                default:
                    // カスタムフックを文字列で渡された場合は汎用ハンドラを登録（引数は可変）
                    add_action($hook, [self::class, 'handle_custom_hook'], 10, 99);
                    break;
            }
        }

        // lineconnect_custom_hook は仕様で固定の引数形を想定しているため明示的に登録
        add_action('lineconnect_custom_hook', [self::class, 'handle_custom_hook'], 10, 2);
    }

    /**
     * ユーザー登録後
     */
    public static function on_user_register($user_id) {
        try {
            $args = func_get_args();
            self::dispatch('user_register', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_user_register] ' . $e->getMessage());
        }
    }

    public static function on_wp_login($user_login, $user) {
        try {
            $args = func_get_args();
            self::dispatch('wp_login', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_wp_login] ' . $e->getMessage());
        }
    }

    public static function on_wp_logout() {
        try {
            $args = func_get_args();
            self::dispatch('wp_logout', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_wp_logout] ' . $e->getMessage());
        }
    }

    public static function on_profile_update($user_id, $old_user_data) {
        try {
            $args = func_get_args();
            self::dispatch('profile_update', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_profile_update] ' . $e->getMessage());
        }
    }

    public static function on_delete_user($user_id) {
        try {
            $args = func_get_args();
            self::dispatch('delete_user', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_delete_user] ' . $e->getMessage());
        }
    }

    public static function on_save_post($post_id, $post = null, $update = null) {
        try {
            $args = func_get_args();
            self::dispatch('save_post', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_save_post] ' . $e->getMessage());
        }
    }

    public static function on_comment_post($comment_id, $comment_approved) {
        try {
            $args = func_get_args();
            self::dispatch('comment_post', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_comment_post] ' . $e->getMessage());
        }
    }

    public static function on_activated_plugin($plugin) {
        try {
            $args = func_get_args();
            self::dispatch('activated_plugin', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_activated_plugin] ' . $e->getMessage());
        }
    }

    public static function on_deactivated_plugin($plugin) {
        try {
            $args = func_get_args();
            self::dispatch('deactivated_plugin', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_deactivated_plugin] ' . $e->getMessage());
        }
    }

    public static function on_switch_theme($new_name) {
        try {
            $args = func_get_args();
            self::dispatch('switch_theme', $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::on_switch_theme] ' . $e->getMessage());
        }
    }

    /**
     * カスタムフックハンドラ（汎用）
     *
     * @param mixed ...$args
     */
    public static function handle_custom_hook(...$args) {
        try {
            // 第一引数がフック名であることを期待する呼び出し側の慣例に依存
            $hook = current_filter();
            self::dispatch($hook, $args);
        } catch (\Throwable $e) {
            error_log('[ActionHooks::handle_custom_hook] ' . $e->getMessage());
        }
    }

    /**
     * dispatch to Trigger/ActionHook::process
     *
     * @param string $hook_name
     * @param array $args
     */
    protected static function dispatch($hook_name, array $args = []) {
        $payload = [
            'hook' => $hook_name,
            'args' => $args,
        ];

        // 呼び出し先のクラスが存在すれば process を呼ぶ
        $target = '\\Shipweb\\LineConnect\\Trigger\\ActionHook';
        if (class_exists($target) && is_callable([$target, 'process'])) {
            try {
                $target::process($payload);
            } catch (\Throwable $e) {
                error_log('[ActionHooks::dispatch] process error: ' . $e->getMessage());
            }
        }
    }
}
