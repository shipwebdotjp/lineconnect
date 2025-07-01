<?php

namespace Shipweb\LineConnect\Admin\Setting;

use Shipweb\LineConnect\Core\LineConnect;

class DataDelete {
    public static function delete_all_data() {
        //プラグインのデータ(DB)を削除する
        global $wpdb;
        $table_names      = array(
            $wpdb->prefix . LineConnect::TABLE_BOT_LOGS,
            $wpdb->prefix . LineConnect::TABLE_LINE_ID,
            $wpdb->prefix . LineConnect::TABLE_LINE_STATS,
            $wpdb->prefix . LineConnect::TABLE_LINE_DAILY,
        );
        foreach ($table_names as $table_name) {
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
        }

        // オプションを削除
        delete_option(LineConnect::OPTION_KEY__CHANNELS);
        delete_option(LineConnect::OPTION_KEY__SETTINGS);
        delete_option(LineConnect::OPTION_KEY__VARIABLES);
        delete_option(LineConnect::CRON_EVENT_LAST_TIMESTAMP);

        // カスタム投稿タイプの投稿を削除
        $cpts = [
            \Shipweb\LineConnect\PostType\Trigger\Trigger::POST_TYPE,
            \Shipweb\LineConnect\PostType\Audience\Audience::POST_TYPE,
            \Shipweb\LineConnect\PostType\Message\Message::POST_TYPE,
            \Shipweb\LineConnect\Scenario\Scenario::POST_TYPE,
            \Shipweb\LineConnect\ActionFlow\ActionFlow::POST_TYPE,
        ];
        foreach ($cpts as $cpt) {
            $posts = get_posts([
                'post_type'   => $cpt,
                'numberposts' => -1,
                'post_status' => 'any',
                'fields'      => 'ids', // 投稿IDのみ取得して効率化
            ]);
            foreach ($posts as $post_id) {
                wp_delete_post($post_id, true); // trueでゴミ箱を経由せず完全に削除
            }
        }

        // ユーザーメタを削除
        $users = get_users(['fields' => 'ID']);
        foreach ($users as $user_id) {
            delete_user_meta($user_id, LineConnect::META_KEY__LINE);
        }

        // 投稿のメタを削除

        $posts = get_posts([
            'post_type'   => 'any',
            'numberposts' => -1,
            'post_status' => 'any',
            'fields'      => 'ids', // 投稿IDのみ取得して効率化
        ]);
        foreach ($posts as $post_id) {
            delete_post_meta($post_id, lineconnect::META_KEY__IS_SEND_LINE);
        }

        // Cronイベントを削除
        $timestamp = wp_next_scheduled(LineConnect::CRON_EVENT_NAME);
        if ($timestamp) {
            wp_unschedule_event($timestamp, LineConnect::CRON_EVENT_NAME);
        }

        //プラグインのデータ(ファイル)を削除する
        $upload_dir      = wp_upload_dir();
        $lineconnect_dir = $upload_dir['basedir'] . '/lineconnect';
        if (is_dir($lineconnect_dir)) {
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }
            $wp_filesystem->rmdir($lineconnect_dir, true);
        }

        // トランジェントを削除
        $prefix = $wpdb->esc_like(LineConnect::PLUGIN_PREFIX);
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_' . $prefix . '%',
                '_transient_timeout_' . $prefix . '%'
            )
        );

        return true;
    }
}
