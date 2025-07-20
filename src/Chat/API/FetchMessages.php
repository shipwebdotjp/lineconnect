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

        $timestamp = isset($_POST['timestamp']) ? sanitize_text_field(wp_unslash(urldecode($_POST['timestamp']))) : null;
        if ($timestamp && !preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}$/', $timestamp)) {
            wp_send_json_error([
                'result' => 'failed',
                'message' => sprintf(__('Invalid timestamp format. %s', lineconnect::PLUGIN_NAME), $timestamp)
            ]);
        }
        $where = 'bot_id = %s and user_id = %s';
        $values = [
            sanitize_text_field(wp_unslash($_POST['channel_prefix'])),
            sanitize_text_field(wp_unslash($_POST['user_id']))
        ];
        if ($timestamp) {
            $where .= ' and timestamp < %s';
            $values[] = $timestamp;
        }

        $user_id = sanitize_text_field(wp_unslash($_POST['user_id']));
        $channel_prefix = sanitize_text_field(wp_unslash($_POST['channel_prefix']));

        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_BOT_LOGS;
        if (version_compare(lineconnect::get_current_db_version(), '1.5', '>=')) {
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $messages = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id,event_type,source_type,user_id,message_type,message,UNIX_TIMESTAMP(timestamp) as timestamp,status,error 
                    FROM {$table_name} 
                    WHERE {$where}
                    ORDER BY timestamp desc, id desc
                    LIMIT 100
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
                    "SELECT id,event_type,source_type,user_id,message_type,message,UNIX_TIMESTAMP(timestamp) as timestamp 
                    FROM {$table_name} 
                    WHERE {$where}
                    ORDER BY timestamp desc, id desc
                    LIMIT 100
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
        $ary_response = array();
        $ary_messages = array();
        foreach (array_reverse($messages) as $convasation) {
            $isMe       = $convasation['source_type'] >= 11 ? true : false;
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
        );
        wp_send_json_success($ary_response);

        wp_die();
    }
}
