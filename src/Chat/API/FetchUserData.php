<?php

namespace Shipweb\LineConnect\Chat\API;

use Shipweb\LineConnect\Core\LineConnect;

class FetchUserData {
    // 指定されたユーザーデータを取得
    static function ajax_fetch_user_data() {
        global $wpdb;

        header('Content-Type: application/json; charset=utf-8');
        $result = \Shipweb\LineConnect\Utilities\Guard::check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST);
        if ($result['result'] === 'failed') {
            echo json_encode($result);
            wp_die();
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
        $result['profile'] = json_decode($result['profile'] ?: '', true);
        $result['tags'] = json_decode($result['tags'] ?: '', true);
        $result['interactions'] = json_decode($result['interactions'] ?: '', true);
        $result['scenarios'] = json_decode($result['scenarios'] ?: '', true);
        $result['stats'] = json_decode($result['stats'] ?: '', true);
        echo json_encode($result);
        wp_die();
    }
}
