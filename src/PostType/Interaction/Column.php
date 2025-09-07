<?php

namespace Shipweb\LineConnect\PostType\Interaction;

use Shipweb\LineConnect\Core\LineConnect;

/**
 * Interaction 管理画面のカラム表示とソート処理
 */
class Column {
    /**
     * フック登録
     */
    public static function init() {
        // カラム追加・表示
        add_filter('manage_edit-interaction_columns', [__CLASS__, 'add_columns']);
        add_action('manage_interaction_posts_custom_column', [__CLASS__, 'add_columns_content'], 10, 2);

        // ソート可能カラムの登録
        add_filter('manage_edit-interaction_sortable_columns', [__CLASS__, 'sortable_columns']);

        // 管理画面のメインクエリに対して SQL を拡張（orderby 処理）
        add_filter('posts_clauses', [__CLASS__, 'posts_clauses_for_interaction_list'], 10, 2);
    }

    /**
     * カスタム投稿タイプの一覧にカラムを追加
     */
    public static function add_columns($columns) {
        $new_columns = array();

        // タイトルの後に統計カラムを挿入
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['active_count'] = __('Active', LineConnect::PLUGIN_NAME);
                $new_columns['paused_count'] = __('Paused', LineConnect::PLUGIN_NAME);
                $new_columns['completed_count'] = __('Completed', LineConnect::PLUGIN_NAME);
                $new_columns['timeout_count'] = __('Timeout', LineConnect::PLUGIN_NAME);
                $new_columns['completion_rate'] = __('Completion Rate', LineConnect::PLUGIN_NAME);
                $new_columns['unique_users'] = __('Unique Users', LineConnect::PLUGIN_NAME);
                $new_columns['session_list'] = __('Sessions', LineConnect::PLUGIN_NAME);
            }
        }

        return $new_columns;
    }

    /**
     * カスタムカラムの内容を表示
     */
    public static function add_columns_content($column_name, $post_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;

        // 統計カラムのみ処理
        if (!in_array($column_name, ['active_count', 'paused_count', 'completed_count', 'timeout_count', 'completion_rate', 'unique_users', 'session_list'])) {
            return;
        }

        switch ($column_name) {
            case 'active_count':
                $count = self::get_status_count($table_name, $post_id, 'active');
                echo intval($count);
                break;
            case 'paused_count':
                $count = self::get_status_count($table_name, $post_id, 'paused');
                echo intval($count);
                break;
            case 'completed_count':
                $count = self::get_status_count($table_name, $post_id, 'completed');
                echo intval($count);
                break;
            case 'timeout_count':
                $count = self::get_status_count($table_name, $post_id, 'timeout');
                echo intval($count);
                break;
            case 'completion_rate':
                $completed_count = self::get_status_count($table_name, $post_id, 'completed');
                $total_count = self::get_total_count($table_name, $post_id);
                if ($total_count > 0) {
                    $rate = ($completed_count / $total_count) * 100;
                    echo number_format($rate, 1) . '%';
                } else {
                    echo '0.0%';
                }
                break;
            case 'unique_users':
                $count = self::get_unique_users_count($table_name, $post_id);
                echo intval($count);
                break;
            case 'session_list':
                // link to admin session page
                $admin_url = admin_url("admin.php?page=" . lineconnect::SLUG__SESSION . "#/interactions/" . $post_id . "/sessions");
                echo '<a href="' . esc_url($admin_url) . '" >' . __('View Sessions', LineConnect::PLUGIN_NAME) . '</a>';
        }
    }

    /**
     * 管理画面でソート可能なカラムを登録
     */
    public static function sortable_columns($columns) {
        $columns['active_count'] = 'active_count';
        $columns['paused_count'] = 'paused_count';
        $columns['completed_count'] = 'completed_count';
        $columns['timeout_count'] = 'timeout_count';
        $columns['completion_rate'] = 'completion_rate';
        $columns['unique_users'] = 'unique_users';
        // session_list はソート対象外
        return $columns;
    }

    /**
     * 管理画面の posts_clauses を拡張して、orderby をカスタム集計結果に差し替える
     *
     * @param array    $clauses SQL の各句（fields, join, where, groupby, orderby, distinct）
     * @param \WP_Query $query
     * @return array
     */
    public static function posts_clauses_for_interaction_list($clauses, $query) {
        // 管理画面メインクエリで、post_type が interaction の場合のみ処理
        if (!is_admin() || ! $query->is_main_query()) {
            return $clauses;
        }
        
        $post_type = $query->get('post_type') ?: (isset($_GET['post_type']) ? $_GET['post_type'] : '');
        if ($post_type !== Interaction::POST_TYPE) {
            return $clauses;
        }
        
        // orderby がカスタムキーのときにだけ処理
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : $query->get('orderby');
        if (! $orderby) {
            return $clauses;
        }
        
        $valid = ['active_count', 'paused_count', 'completed_count', 'timeout_count', 'completion_rate', 'unique_users'];
        if (! in_array($orderby, $valid, true)) {
            return $clauses;
        }

        global $wpdb;
        $table = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;

        // 別名
        $alias = 'slc_' . $orderby;

        // サブクエリを作る（数値を返す）
        switch ($orderby) {
            case 'active_count':
            case 'paused_count':
            case 'completed_count':
            case 'timeout_count':
                $status = $orderby === 'active_count' ? 'active'
                        : ($orderby === 'paused_count' ? 'paused'
                        : ($orderby === 'completed_count' ? 'completed' : 'timeout'));
                $sub = "(SELECT COUNT(*) FROM {$table} WHERE interaction_id = {$wpdb->posts}.ID AND status = '{$status}')";
                break;
            case 'unique_users':
                $sub = "(SELECT COUNT(DISTINCT CONCAT(channel_prefix, ':', line_user_id)) FROM {$table} WHERE interaction_id = {$wpdb->posts}.ID)";
                break;
            case 'completion_rate':
                // completed / total を 0..1 で返す。COUNT を 0 チェックしてゼロ回避
                // SUM(status='completed') は MySQL で真偽を 1/0 として扱える
                $sub = "(SELECT IF(COUNT(*)=0, 0, SUM(status='completed')/COUNT(*)) FROM {$table} WHERE interaction_id = {$wpdb->posts}.ID)";
                break;
            default:
                return $clauses;
        }

        // SELECT にフィールドを追加（重複防止）
        if (stripos($clauses['fields'], $alias) === false) {
            $clauses['fields'] .= ", {$sub} AS {$alias}";
        }

        // order (ASC/DESC) を決定（URL パラメータ or query）
        $order = (isset($_GET['order']) ? $_GET['order'] : ($query->get('order') ? $query->get('order') : 'DESC'));
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        // ORDER BY を差し替え（既存の ORDER BY は置き換える）
        $clauses['orderby'] = "{$alias} {$order}";
        return $clauses;
    }

    /**
     * 指定されたステータスの件数を取得
     */
    private static function get_status_count($table_name, $interaction_id, $status) {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE interaction_id = %d AND status = %s",
            $interaction_id,
            $status
        );
        return $wpdb->get_var($query);
    }

    /**
     * 全セッション数を取得
     */
    private static function get_total_count($table_name, $interaction_id) {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE interaction_id = %d",
            $interaction_id
        );
        return $wpdb->get_var($query);
    }

    /**
     * ユニークユーザー数を取得
     */
    private static function get_unique_users_count($table_name, $interaction_id) {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(channel_prefix, ':', line_user_id)) FROM {$table_name} WHERE interaction_id = %d",
            $interaction_id
        );
        return $wpdb->get_var($query);
    }
}

// ファイル読み込み時にフック登録（既にどこかで init しているなら二重登録は無害）
Column::init();
