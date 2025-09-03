<?php

namespace Shipweb\LineConnect\PostType\Interaction;

use Shipweb\LineConnect\Core\LineConnect;

class Column {
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
        if (!in_array($column_name, ['active_count', 'paused_count', 'completed_count', 'timeout_count', 'completion_rate', 'unique_users'])) {
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
        }
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
