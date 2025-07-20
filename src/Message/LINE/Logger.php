<?php

namespace Shipweb\LineConnect\Message\LINE;

use Shipweb\LineConnect\Core\LineConnect;

class Logger {
    /**
     * メッセージ送信ログの書き込み
     */
    public static function writeOutboundMessageLog(
        array| \LINE\LINEBot\MessageBuilder $message,
        string $event_type,
        string $source_type,
        string|array $user_id = [],
        string $secret_prefix = '',
        string $status = 'pending',
        string|array $error = '',
        ?string $event_id = null,
    ): bool {
        global $wpdb;

        $table_name = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS;

        if (!is_array($user_id)) {
            $user_id = [$user_id];
        }

        if (empty($user_id) && $event_type === 'broadcast') {
            $user_id = self::getAllFollowedUserIds($secret_prefix);
        }

        if ($message instanceof \LINE\LINEBot\MessageBuilder) {
            $message = $message->buildMessage();
        }

        $floatSec = microtime(true);
        $dateTime = \DateTime::createFromFormat('U.u', sprintf('%1.6F', $floatSec));
        $dateTime->setTimeZone(new \DateTimeZone('Asia/Tokyo'));
        $timestamp = $dateTime->format('Y-m-d H:i:s.u');

        $result = false;
        $arrayValues = array();
        $place_holders = array();
        $affected_user_ids = array();
        if (version_compare(lineconnect::get_current_db_version(), '1.5', '>=')) {
            foreach ($user_id as $uid) {
                $arrayValues[] = $event_id ?: null;
                $arrayValues[] = array_search($event_type, \Shipweb\LineConnect\Bot\Constants::WH_EVENT_TYPE) ?: 0;
                $arrayValues[] = array_search($source_type, \Shipweb\LineConnect\Bot\Constants::WH_SOURCE_TYPE) ?: 0;
                $arrayValues[] = $uid;
                $arrayValues[] = $secret_prefix;
                $arrayValues[] = json_encode($message);
                $arrayValues[] = $timestamp;
                $arrayValues[] = array_search($status, \Shipweb\LineConnect\Bot\Constants::WH_STATUS) ?: 0;
                $arrayValues[] = !empty($error) ? json_encode($error) : json_encode(null);
                $place_holders[] = '(%s, %d, %d, %s, %s, %s, %s, %d, %s)';
                $affected_user_ids[] = $uid;
            }
            $sql = 'INSERT INTO ' . $table_name . ' (event_id, event_type, source_type, user_id, bot_id, message, timestamp, status, error) VALUES ' . join(',', $place_holders);
        } else {
            foreach ($user_id as $uid) {
                $arrayValues[] = $event_id ?: null;
                $arrayValues[] = array_search($event_type, \Shipweb\LineConnect\Bot\Constants::WH_EVENT_TYPE) ?: 0;
                $arrayValues[] = array_search($source_type, \Shipweb\LineConnect\Bot\Constants::WH_SOURCE_TYPE) ?: 0;
                $arrayValues[] = $uid;
                $arrayValues[] = $secret_prefix;
                $arrayValues[] = json_encode($message);
                $arrayValues[] = $timestamp;
                $place_holders[] = '(%s, %d, %d, %s, %s, %s, %s)';
                $affected_user_ids[] = $uid;
            }
            $sql = 'INSERT INTO ' . $table_name . ' (event_id, event_type, source_type, user_id, bot_id, message, timestamp) VALUES ' . join(',', $place_holders);
        }
        $result = $wpdb->query($wpdb->prepare($sql, $arrayValues));

        // 各ユーザーの最終メッセージと最終送信時刻を更新
        if (version_compare(lineconnect::get_current_db_version(), '1.5', '>=')) {
            $table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
            $message_text = self::getMessageText($message);

            $update_sql = "UPDATE {$table_name_line_id} SET last_message = %s, last_sent_at = %s WHERE channel_prefix = %s AND line_id IN (" . implode(',', array_fill(0, count($affected_user_ids), '%s')) . ")";
            $update_values = array_merge([$message_text, $timestamp, $secret_prefix], $affected_user_ids);
            $wpdb->query($wpdb->prepare($update_sql, $update_values));
        }
        return $result ? true : false;
    }

    private static function getAllFollowedUserIds($secret_prefix): array {
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

        // ユーザーIDを取得するクエリ
        $query = $wpdb->prepare(
            "SELECT line_id FROM {$table_name} WHERE channel_prefix = %d AND follow = 1",
            $secret_prefix
        );

        return $wpdb->get_col($query);
    }

    private static function getMessageText($message): string {
        if (is_array($message)) {
            // メッセージが配列の場合、最後のメッセージを取得
            $last_message = end($message);
        } else {
            // メッセージがオブジェクトの場合、直接取得
            $last_message = $message;
        }
        switch ($last_message['type'] ?? '') {
            case 'text':
            case 'textV2':
                return $last_message['text'] ?? '';
            case 'sticker':
                return __('Sent sticker message.', lineconnect::PLUGIN_NAME);
            case 'image':
                return __('Sent image message.', lineconnect::PLUGIN_NAME);
            case 'video':
                return __('Sent video message.', lineconnect::PLUGIN_NAME);
            case 'audio':
                return __('Sent audio message.', lineconnect::PLUGIN_NAME);
            case 'location':
                return sprintf(__('Sent location message. %s (%s)', lineconnect::PLUGIN_NAME), $last_message['title'] ?? '', $last_message['address'] ?? '');
            case 'template':
                return sprintf(__('Sent template message. %s', lineconnect::PLUGIN_NAME), $last_message['altText'] ?? '');
            case 'imagemap':
                return sprintf(__('Sent imagemap message. %s', lineconnect::PLUGIN_NAME), $last_message['altText'] ?? '');
            case 'flex':
                return sprintf(__('Sent flex message. %s', lineconnect::PLUGIN_NAME), $last_message['altText'] ?? '');
            default:
                return json_encode($last_message);
        }
    }
}
