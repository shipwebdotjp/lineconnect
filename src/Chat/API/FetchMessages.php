<?php

/**
 * REST API for fetching chat messages.
 *
 * @package Shipweb\LineConnect\Chat\API
 */

namespace Shipweb\LineConnect\Chat\API;

use Shipweb\LineConnect\Core\LineConnect;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class FetchMessages
 *
 * @package Shipweb\LineConnect\Chat\API
 */
class FetchMessages {

    /**
     * AJAX handler to fetch messages for a user.
     */
    public static function execute() {

        header('Content-Type: application/json; charset=utf-8');
        $result = \Shipweb\LineConnect\Utilities\Guard::check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST);
        if ($result['result'] === 'failed') {
            wp_send_json_error($result);
        }

        if (! isset($_POST['user_id']) || empty($_POST['user_id'])) {
            wp_send_json_error([
                'result' => 'failed',
                'message' => __('User ID is required.', lineconnect::PLUGIN_NAME)
            ]);
        }

        if (! isset($_POST['channel_prefix']) || empty($_POST['channel_prefix'])) {
            wp_send_json_error([
                'result' => 'failed',
                'message' => __('Channel prefix is required.', lineconnect::PLUGIN_NAME)
            ]);
        }

        $cursor = isset($_POST['cursor']) ? json_decode(base64_decode(sanitize_text_field(wp_unslash($_POST['cursor']))), true) : null;

        $where = 'bot_id = %s and user_id = %s';
        $values = [
            sanitize_text_field(wp_unslash($_POST['channel_prefix'])),
            sanitize_text_field(wp_unslash($_POST['user_id']))
        ];
        if ($cursor) {
            $where .= ' and (timestamp < %s or (timestamp = %s and id < %d))';
            $values[] = $cursor['last_timestamp'];
            $values[] = $cursor['last_timestamp'];
            $values[] = $cursor['last_id'];
        }

        $user_id = sanitize_text_field(wp_unslash($_POST['user_id']));
        $channel_prefix = sanitize_text_field(wp_unslash($_POST['channel_prefix']));

        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_BOT_LOGS;
        if (version_compare(lineconnect::get_current_db_version(), '1.6', '>=')) {
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $messages = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id,event_type,source_type,user_id,message_type,message,timestamp as raw_timestamp,TIMESTAMPDIFF(MICROSECOND, '1970-01-01 00:00:00.000000', timestamp) / 1e6 as timestamp,status,error 
                    FROM {$table_name} 
                    WHERE {$where}
                    ORDER BY timestamp desc, id desc
                    LIMIT 101
                    ",
                    $values
                ),
                ARRAY_A
            );
            // phpcs:enable
        } else {
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $messages = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id,event_type,source_type,user_id,message_type,message,timestamp as raw_timestamp,UNIX_TIMESTAMP(timestamp) as timestamp 
                    FROM {$table_name} 
                    WHERE {$where}
                    ORDER BY timestamp desc, id desc
                    LIMIT 101
                    ",
                    $values
                ),
                ARRAY_A
            );
            // phpcs:enable
        }
        if (is_null($messages)) {
            wp_send_json_error(['result' => 'failed', 'message' => __('Failed to retrieve messages.', lineconnect::PLUGIN_NAME)]);
        }
        // check if has more messages
        $has_more = false;
        if (count($messages) >= 101) {
            $has_more = true;
        }
        // remove the last message if has more messages
        if ($has_more) {
            array_pop($messages);
        }

        $next_cursor = null;
        if ($has_more) {
            $last_message = end($messages);
            $next_cursor_data = [
                'last_timestamp' => $last_message['raw_timestamp'],
                'last_id'        => $last_message['id'],
            ];
            $next_cursor = base64_encode(json_encode($next_cursor_data));
        }


        $ary_response = array();
        $ary_messages = array();
        foreach (array_reverse($messages) as $convasation) {
            $isMe       = $convasation['source_type'] >= 10 ? true : false;
            $msg_time   = wp_date('Y/m/d H:i:s', intval($convasation['timestamp']));
            $message_object = isset($convasation['message']) ? json_decode($convasation['message'], false) : null;
            $error_object = isset($convasation['error']) ? json_decode($convasation['error'], false) : null;

            $ary_messages[] = array(
                'id' => intval($convasation['id']),
                'isMe' => $isMe,
                'event_type' => intval($convasation['event_type']),
                'source_type' => intval($convasation['source_type']),
                'type' => intval($convasation['message_type']),
                'message' => $message_object,
                'status' => isset($convasation['status']) ? intval($convasation['status']) : 0,
                'error' => $error_object,
                'date' => $msg_time,
            );
        }

        $ary_response = array(
            'result' => 'success',
            'messages' => $ary_messages,
            'has_more' => $has_more,
            'next_cursor' => $next_cursor,
        );
        wp_send_json_success($ary_response);

        wp_die();
    }
}
