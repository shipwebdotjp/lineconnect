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
            echo json_encode($result);
            wp_die();
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

        $user_id = sanitize_text_field(wp_unslash($_POST['user_id']));
        $channel_prefix = sanitize_text_field(wp_unslash($_POST['channel_prefix']));

        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_BOT_LOGS;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $messages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id,event_type,source_type,user_id,message_type,message,UNIX_TIMESTAMP(timestamp) as timestamp 
                FROM {$table_name} 
                WHERE event_type = 1 and bot_id = %s and user_id = %s
                ORDER BY id desc
                LIMIT 100
                ",
                [
                    $channel_prefix,
                    $user_id
                ]
            ),
            ARRAY_A
        );
        // phpcs:enable

        if (is_null($messages)) {
            wp_send_json_error(['result' => 'failed', 'message' => __('Failed to retrieve messages.', lineconnect::PLUGIN_NAME)]);
        }
        $ary_response = array();
        $ary_messages = array();
        foreach (array_reverse($messages) as $convasation) {
            $isMe       = $convasation['source_type'] == 11 ? true : false;
            $msg_time   = date('Y/m/d H:i:s', intval($convasation['timestamp']));
            $message_object = json_decode($convasation['message'], false);

            $ary_messages[] = array(
                'id' => intval($convasation['id']),
                'isMe' => $isMe,
                'type' => intval($convasation['message_type']),
                'message' => $message_object,
                'date' => $msg_time,
            );
        }
        $ary_response = array(
            'result' => 'success',
            'messages' => $ary_messages,
        );
        wp_send_json_success($ary_response);

        wp_die();
    }
}
