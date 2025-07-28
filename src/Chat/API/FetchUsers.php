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
            wp_send_json_error($result);
        }

        if (!isset($_POST['channel_prefix']) || empty($_POST['channel_prefix'])) {
            wp_send_json_error([
                'result' => 'failed',
                'message' => __('Channel prefix is required.', 'lineconnect')
            ]);
        }
        $channel_prefix = stripslashes($_POST['channel_prefix']);
        $cursor = isset($_POST['cursor']) ? stripslashes($_POST['cursor']) : null;

        $last_sent_at = null;
        $last_id = null;

        if ($cursor) {
            $decoded_cursor = json_decode(base64_decode($cursor), true);
            if ($decoded_cursor) {
                if (isset($decoded_cursor['last_id'])) {
                    $last_id = $decoded_cursor['last_id'];
                }
                if (isset($decoded_cursor['last_sent_at'])) {
                    $last_sent_at = $decoded_cursor['last_sent_at'];
                }
            }
        }

        $table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
        $where_clauses = ['channel_prefix = %s'];
        $values = [$channel_prefix];

        $number_of_users = 25;

        if (empty($last_sent_at) && $last_id) {
            $where_clauses[] = 'last_sent_at IS NULL AND id < %d';
            $values[] = $last_id;
        }else if ($last_sent_at && $last_id) {
            $where_clauses[] = '(last_sent_at < %s OR (last_sent_at = %s AND id < %d))';
            $values[] = $last_sent_at;
            $values[] = $last_sent_at;
            $values[] = $last_id;
        }

        $where = implode(' AND ', $where_clauses);

        $query = "
            SELECT id, channel_prefix, line_id as lineId, follow, profile->>'$.displayName' AS displayName, profile->>'$.pictureUrl' AS pictureUrl, last_message, last_sent_at
            FROM {$table_name}
            WHERE {$where}
            ORDER BY last_sent_at DESC, id DESC
            LIMIT %d";

        $values[] = $number_of_users + 1;

        $results = $wpdb->get_results($wpdb->prepare($query, $values), ARRAY_A);

        if (is_null($results)) {
            wp_send_json_error(['result' => 'failed', 'message' => 'Failed to retrieve users.']);
        }

        $has_more = count($results) > $number_of_users;
        if ($has_more) {
            array_pop($results);
        }

        $next_cursor = null;
        if ($has_more && !empty($results)) {
            $last_user = end($results);
            $next_cursor = base64_encode(json_encode([
                'last_sent_at' => $last_user['last_sent_at'],
                'last_id' => $last_user['id'],
            ]));
        }

        $ary_users = array();
        foreach ($results as $result) {
            $ary_users[] = array(
                'id' => $result['id'],
                'lineId' => $result['lineId'],
                'follow' => $result['follow'],
                'displayName' => $result['displayName'],
                'pictureUrl' => $result['pictureUrl'],
                'last_message' => $result['last_message'],
                'last_sent_at' => $result['last_sent_at'] ? wp_date('Y-m-d H:i:s', strtotime($result['last_sent_at'])) : null,
            );
        }
        wp_send_json_success(['users' => $ary_users, 'has_more' => $has_more, 'next_cursor' => $next_cursor]);
    }
}
