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
        }
        $channel_prefix = isset($_POST['channel_prefix']) ? stripslashes($_POST['channel_prefix']) : "";
        $line_id = isset($_POST['line_id']) ? stripslashes($_POST['line_id']) : "";
        $type = isset($_POST['type']) ? stripslashes($_POST['type']) : "";
        $id = isset($_POST['id']) ? stripslashes($_POST['id']) : "";
        $data = isset($_POST['data']) ? array_map('stripslashes_deep', $_POST['data']) : null;
        
        if (empty($channel_prefix) || empty($line_id) || empty($type)) {
            wp_send_json_error(array('message' => 'Invalid parameters.'));
        }
        
        if (!in_array($type, ['profile', 'tags', 'interactions', 'scenarios', 'stats'])) {
            wp_send_json_error(array('message' => 'Invalid type.'));
        }
        
        if (in_array($type, ['scenarios', 'interactions']) && empty($id)) {
            wp_send_json_error(array('message' => 'id is required.'));
        }
        
        if( $type === 'scenarios' ) {
            $data['id'] = (int)$data['id'];
            $data['next_date'] = gmdate(DATE_ATOM, strtotime($data['next_date']));
            $data['started_at'] = gmdate(DATE_ATOM, strtotime($data['started_at']));
            $data['updated_at'] = gmdate(DATE_ATOM, strtotime($data['updated_at']));
        }
        $data_json = json_encode($data, JSON_UNESCAPED_UNICODE);
        // 'scenarios', 'interactions'の場合は既存のデータを取得
        if (in_array($type, ['scenarios', 'interactions'])) {
            $table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
            $result = $wpdb->get_var($wpdb->prepare("SELECT {$type} FROM {$table_name} WHERE channel_prefix = %s AND line_id = %s", $channel_prefix, $line_id));
            $original_data = json_decode($result ?? '[]', true);
            $original_data[$id] = $data;
            $data_json = json_encode($original_data, JSON_UNESCAPED_UNICODE);
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
    }
}
