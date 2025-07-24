<?php

namespace Shipweb\LineConnect\Chat\API;

use Shipweb\LineConnect\Core\LineConnect;

class EditUserData {
    // 指定されたユーザーデータを更新
    static function ajax_edit_user_data() {
        global $wpdb;

        header('Content-Type: application/json; charset=utf-8');
        $result = \Shipweb\LineConnect\Utilities\Guard::check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST);
        if ($result['result'] === 'failed') {
            wp_send_json_error($result);
            wp_die();
        }
        $channel_prefix = isset($_POST['channel_prefix']) ? stripslashes($_POST['channel_prefix']) : "";
        $line_id = isset($_POST['line_id']) ? stripslashes($_POST['line_id']) : "";
        $type = isset($_POST['type']) ? stripslashes($_POST['type']) : "";
        $data = isset($_POST['data']) ? array_map('stripslashes_deep', $_POST['data']) : null;
        $data_json = json_encode($data, JSON_UNESCAPED_UNICODE);

        if (empty($channel_prefix) || empty($line_id) || empty($type)) {
            wp_send_json_error(array('message' => 'Invalid parameters.'));
            wp_die();
        }

        if (!in_array($type, ['profile', 'tags', 'interactions', 'scenarios', 'stats'])) {
            wp_send_json_error(array('message' => 'Invalid type.'));
            wp_die();
        }

        $table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

        $result = $wpdb->update(
            $table_name,
            [
                $type => $data_json
            ],
            [
                'channel_prefix' => $channel_prefix,
                'line_id' => $line_id
            ]
        );
        $response = $result === false ? array('result' => 'failed') : array('result' => 'success');
        wp_send_json_success($response);
        wp_die();
    }
}
