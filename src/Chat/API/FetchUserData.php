<?php

namespace Shipweb\LineConnect\Chat\API;

use Shipweb\LineConnect\Core\LineConnect;

class FetchUserData {
    // 指定されたユーザーデータを取得
    static function ajax_fetch_user_data() {
        global $wpdb;

        $result = \Shipweb\LineConnect\Utilities\Guard::check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST);
        if ($result['result'] === 'failed') {
            wp_send_json_error($result);
        }

        if (!isset($_POST['channel_prefix']) || empty($_POST['channel_prefix'])) {
            wp_send_json_error([
                'result' => 'failed',
                'message' => __('Channel prefix is required.', 'lineconnect')
            ]);
        }

        if (!isset($_POST['line_id']) || empty($_POST['line_id'])) {
            wp_send_json_error([
                'result' => 'failed',
                'message' => __('Line ID is required.', 'lineconnect')
            ]);
        }
        
        $channel_prefix = isset($_POST['channel_prefix']) ? stripslashes($_POST['channel_prefix']) : "";
        $line_id = isset($_POST['line_id']) ? stripslashes($_POST['line_id']) : "";
        $table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
        $query = "
                SELECT channel_prefix, line_id as lineId, follow, tags, profile, interactions, scenarios, stats, created_at, updated_at
                FROM {$table_name}
                WHERE channel_prefix = %s AND line_id = %s
        ";
        $query = $wpdb->prepare($query, array($channel_prefix, $line_id));
        $result = $wpdb->get_row($query, ARRAY_A);
        $result['profile'] = json_decode($result['profile'] ?: '{}', true);
        $result['tags'] = json_decode($result['tags'] ?: '[]', true);
        $result['interactions'] = json_decode($result['interactions'] ?: '{}', true);
        $result['scenarios'] = json_decode($result['scenarios'] ?: '{}', true);
        $result['stats'] = json_decode($result['stats'] ?: '{}', true);
        wp_send_json_success($result);
    }
}
