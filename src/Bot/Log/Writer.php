<?php

namespace Shipweb\LineConnect\Bot\Log;

use \DateTime;
use \DateTimeZone;
use \lineconnectConst;
use lineconnect;

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

        $event_type   = array_search($this->event->type, lineconnectConst::WH_EVENT_TYPE) ?: 0;
        $source_type  = 0;
        $user_id      = '';
        if (isset($this->event->source)) {
            $source_type = array_search($this->event->source->type, lineconnectConst::WH_SOURCE_TYPE) ?: 0;
            if (isset($this->event->source->userId)) {
                $user_id = $this->event->source->userId;
            }
        }

        $message      = null;
        $message_type = 0;
        if ($event_type === 1) { // message
            $message_type = array_search($this->event->message->type, lineconnectConst::WH_MESSAGE_TYPE) ?: 0;
            $message      = json_encode($this->event->message);
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
        return $wpdb->insert_id;
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
        $event_type    = array_search($this->event->type, lineconnectConst::WH_EVENT_TYPE) ?: 0;
        $source_type   = array_search('bot', lineconnectConst::WH_SOURCE_TYPE) ?: 0;
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
}
