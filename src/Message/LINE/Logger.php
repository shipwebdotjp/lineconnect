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
            }
            $sql = 'INSERT INTO ' . $table_name . ' (event_id, event_type, source_type, user_id, bot_id, message, timestamp) VALUES ' . join(',', $place_holders);
        }
        $result = $wpdb->query($wpdb->prepare($sql, $arrayValues));
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
}
