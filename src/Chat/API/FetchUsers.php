<?php

namespace Shipweb\LineConnect\Chat\API;

use Shipweb\LineConnect\Core\LineConnect;

class FetchUsers {
    // 指定されたチャネルのLINEユーザーリストを取得
    static function ajax_fetch_users() {
        global $wpdb;

        header('Content-Type: application/json; charset=utf-8');
        $result = \Shipweb\LineConnect\Utilities\Guard::check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST);
        if ($result['result'] === 'failed') {
            echo json_encode($result);
            wp_die();
        }
        $channel_prefix = isset($_POST['channel_prefix']) ? stripslashes($_POST['channel_prefix']) : "";
        $table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
        $query = "
                SELECT channel_prefix, line_id, follow, profile
                FROM {$table_name}
                WHERE channel_prefix = %s
                ORDER BY updated_at DESC
                LIMIT 25
        ";
        $query = $wpdb->prepare($query, array($channel_prefix));
        $results = $wpdb->get_results($query, ARRAY_A);
        $results = array_map(function ($result) {
            $result['profile'] = json_decode($result['profile'], true);
            return $result;
        }, $results);
        echo json_encode($results);
        wp_die();
    }
}
