<?php

namespace Shipweb\LineConnect\Bot\Log;

use \DateTime;
use \DateTimeZone;
use \lineconnectConst;
use ParagonIE\Sodium\Core\Poly1305\State;
use Shipweb\LineConnect\Core\LineConnect;

class Writer {
    /** @var object */
    protected $event;
    protected $secret_prefix;

    /**
     * Write constructor.
     *
     * @param object $event LINE webhook event object.
     */
    public function __construct(object $event, string $secret_prefix) {
        $this->event = $event;
        $this->secret_prefix = $secret_prefix;
    }

    /**
     * チャットログ書き込み
     *
     * @return int|true 挿入されたレコードID、または既存で処理スキップ時に true。
     */
    public function writeChatLog() {
        global $wpdb;
        $secret_prefix = $this->secret_prefix;

        $table_name = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS;

        $event_id      = $this->event->webhookEventId;
        // 再送チェック
        if (
            isset($this->event->deliveryContext->isRedelivery) &&
            $this->event->deliveryContext->isRedelivery
        ) {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(id) FROM {$table_name} WHERE event_id = %s",
                    $event_id
                )
            );
            if ($count) {
                return true;
            }
        }

        $event_type   = array_search($this->event->type, \Shipweb\LineConnect\Bot\Constants::WH_EVENT_TYPE) ?: 0;
        $source_type  = 0;
        $user_id      = '';
        if (isset($this->event->source)) {
            $source_type = array_search($this->event->source->type, \Shipweb\LineConnect\Bot\Constants::WH_SOURCE_TYPE) ?: 0;
            if (isset($this->event->source->userId)) {
                $user_id = $this->event->source->userId;
            }
        }

        $message      = null;
        $message_type = 0;
        if ($event_type === 1) { // message
            $message_type = array_search($this->event->message->type, \Shipweb\LineConnect\Bot\Constants::WH_MESSAGE_TYPE) ?: 0;
            $message      = json_encode($this->event->message);
        } elseif ($event_type === 2) { // unsend
            $message = json_encode($this->event->unsend);
        } elseif ($event_type === 3) { // follow
            $message = json_encode($this->event->follow);
        } elseif ($event_type === 4) { // unfollow
            $message = null;
        } elseif ($event_type === 7) { // memberJoined
            $message = json_encode($this->event->joined);
        } elseif ($event_type === 8) { // memberLeft
            $message = json_encode($this->event->left);
        } elseif ($event_type === 9) {
            $message = json_encode($this->event->postback);
        } elseif ($event_type === 10) {
            $message = json_encode($this->event->videoPlayComplete);
        } elseif ($event_type === 11) {
            $message = json_encode($this->event->beacon);
        } elseif ($event_type === 12) {
            $message = json_encode($this->event->link);
        } elseif ($event_type === 13) {
            $message = json_encode($this->event->things);
        } elseif ($event_type === 14) {
            $message = json_encode($this->event->membership);
        }

        $floatSec = $this->event->timestamp / 1000.0;
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%1.6F', $floatSec));
        $dateTime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $timestamp = $dateTime->format('Y-m-d H:i:s.u');

        $data = [
            'event_id'     => $event_id,
            'event_type'   => $event_type,
            'source_type'  => $source_type,
            'user_id'      => $user_id,
            'bot_id'       => $secret_prefix,
            'message_type' => $message_type,
            'message'      => $message,
            'timestamp'    => $timestamp,
        ];
        $format = ['%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s'];

        $wpdb->insert($table_name, $data, $format);
        $last_inserted_id = $wpdb->insert_id;

        // 各ユーザーの最終メッセージと最終受信時刻を更新
        if (version_compare(lineconnect::get_current_db_version(), '1.5', '>=')) {
            $table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
            $message_text = self::getEventText($event_type, $message_type, $message);

            $result = $wpdb->update(
                $table_name_line_id,
                array(
                    'last_message' => $message_text,
                    'last_sent_at' => $timestamp,
                ),
                array(
                    'channel_prefix' => $secret_prefix,
                    'line_id'        => $user_id,
                ),
                array(
                    '%s',
                    '%s',
                ),
                array(
                    '%s',
                    '%s',
                )
            );
        }
        return $last_inserted_id;
    }

    /**
     * AIからの応答をロギング
     *
     * @param string $responseMessage AI の応答テキスト
     */
    public function writeAiResponse(string $responseMessage): void {
        global $wpdb, $secret_prefix;

        $table_name    = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS;
        $event_id      = $this->event->webhookEventId;
        $event_type    = array_search($this->event->type, \Shipweb\LineConnect\Bot\Constants::WH_EVENT_TYPE) ?: 0;
        $source_type   = array_search('bot', \Shipweb\LineConnect\Bot\Constants::WH_SOURCE_TYPE) ?: 0;
        $user_id       = isset($this->event->source->userId) ? $this->event->source->userId : '';

        $message = json_encode([
            'type' => 'text',
            'text' => $responseMessage,
            'for'  => $this->event->message->id ?? '',
        ]);
        $message_type = 1;

        $floatSec = microtime(true);
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%1.6F', $floatSec));
        $dateTime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $timestamp = $dateTime->format('Y-m-d H:i:s.u');

        $data = [
            'event_id'     => $event_id,
            'event_type'   => $event_type,
            'source_type'  => $source_type,
            'user_id'      => $user_id,
            'bot_id'       => $secret_prefix,
            'message_type' => $message_type,
            'message'      => $message,
            'timestamp'    => $timestamp,
        ];
        $format = ['%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s'];

        $wpdb->insert($table_name, $data, $format);
    }

    private static function getEventText($event_type, $message_type, $message): string {
        $message = json_decode($message, true);
        switch ($event_type) {
            case 1: // message
                return self::getMessageTextByType($message_type, $message);
            case 2: // unsend
                return __('Receive Unsend message event', lineconnect::PLUGIN_NAME);
            case 3: // follow
                return __('Receive Followed event', lineconnect::PLUGIN_NAME);
            case 4: // unfollow
                return __('Receive Unfollowed event', lineconnect::PLUGIN_NAME);
            case 7: // memberJoined
                return __('Receive Member Joined event', lineconnect::PLUGIN_NAME);
            case 8: // memberLeft
                return __('Receive Member Left event', lineconnect::PLUGIN_NAME);
            case 9: // postback
                return __('Receive Postback event', lineconnect::PLUGIN_NAME);
            case 10: // videoPlayComplete
                return __('Receive Video Play Complete event', lineconnect::PLUGIN_NAME);
            case 11: // beacon
                return __('Receive Beacon event', lineconnect::PLUGIN_NAME);
            case 12: // link
                return __('Receive Account Link event', lineconnect::PLUGIN_NAME);
            case 13: // things
                return __('Receive Things event', lineconnect::PLUGIN_NAME);
            case 14: // membership
                return __('Receive Membership event', lineconnect::PLUGIN_NAME);
            default:
                return __('Receive Unknown event', lineconnect::PLUGIN_NAME);
        }
    }

    private static function getMessageTextByType($message_type, $message): string {
        switch ($message_type) {
            case 1: // text
                return $message['text'] ?? '';
            case 2: // image
                return __('Receive Image message', lineconnect::PLUGIN_NAME);
            case 3: // video
                return __('Receive Video message', lineconnect::PLUGIN_NAME);
            case 4: // audio
                return __('Receive Audio message', lineconnect::PLUGIN_NAME);
            case 5: // file
                return __('Receive File message', lineconnect::PLUGIN_NAME);
            case 6: // location
                return __('Receive Location message', lineconnect::PLUGIN_NAME);
            case 7: // sticker
                return __('Receive Sticker message', lineconnect::PLUGIN_NAME);
        }
        return __('Receive Unknown message type', lineconnect::PLUGIN_NAME);
    }
}
