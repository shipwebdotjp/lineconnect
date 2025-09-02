<?php

namespace Shipweb\LineConnect\Message\LINE;

use Shipweb\LineConnect\Core\LineConnect;

class Validater {


    /**
     * レシピエントオブジェクトからメッセージオブジェクトを検証する
     * @param array $recepient レシピエント
     * @param \LINE\LINEBot\MessageBuilder\MultiMessageBuilder $message メッセージオブジェクト
     * @return array
     */
    static function validateAudienceMessage($recepient, $message) {
        // require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
        $ary_success_message = array();
        $ary_error_message   = array();
        foreach ($recepient as $secret_prefix => $recepient_item) {
            $error_message        = $success_message = '';
            $channel = lineconnect::get_channel($secret_prefix);
            $type = $recepient_item['type'];
            // if multicast and has placeholder then push
            if ($type == 'multicast' && \Shipweb\LineConnect\Utilities\PlaceholderReplacer::has_object_placeholder($message)) {
                $type = 'push';
            }

            if ($type == 'broadcast') {
                $success_message .= __('Message will be sent to all users who have subscribed to this channel.', lineconnect::PLUGIN_NAME);
            } elseif ($type == 'multicast') {
                $success_message .= sprintf(_n('Message will be sent to %s person by multicast.', 'Message will be sent to %s people by multicast.', count($recepient_item['line_user_ids']), lineconnect::PLUGIN_NAME), number_format(count($recepient_item['line_user_ids'])));
            } elseif ($type == 'push') {
                $success_message .= sprintf(_n('Message will be sent to %s person by push.', 'Message will be sent to %s people by push.', count($recepient_item['line_user_ids']), lineconnect::PLUGIN_NAME), number_format(count($recepient_item['line_user_ids'])));
            }

            if ($type == 'push') {
                // $replaced_message = self::replacePlaceHolder($channel, $recepient_item['line_user_ids'][0], $message);
                $replaced_message = Builder::get_line_message_builder($message, Sender::make_injection_data($channel, $recepient_item['line_user_ids'][0]));
                // $replaced_message = self::replacePlaceHolder($channel, $recepient_item['line_user_ids'][0], $message);
            } else {
                $message = Builder::get_line_message_builder($message);
                $replaced_message = $message;
            }
            $success_message .= ' ';
            $response = self::validateMessage($type, $channel, $replaced_message);
            if ($response['success']) {
                $success_message .= __('Valid message.', lineconnect::PLUGIN_NAME);
            } else {
                $error_message .= __('Invalid message.', lineconnect::PLUGIN_NAME) . $response['message'];
            }

            // 送信に成功した場合
            if ($error_message === '') {
                $ary_success_message[] = $channel['name'] . ': ' . $success_message;
            }
            // 送信に失敗した場合
            else {
                $ary_error_message[] = $channel['name'] . ': ' . $error_message;
            }
        }

        $result = array(
            'success' => empty($ary_error_message),
            'message' => implode("\n", array_merge($ary_error_message, $ary_success_message)),
            'success_messages' => $ary_success_message,
            'error_messages'   => $ary_error_message,
        );
        return $result;
    }


    /**
     * メッセージオブジェクトを検証
     * @param string $type
     * @param array $channel
     * @param \LINE\LINEBot\MessageBuilder\MultiMessageBuilder $message
     * @return array
     */
    static function validateMessage($type, $channel, $message) {
        // LINEBOT SDKの読み込み
        // require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

        $channel_access_token = $channel['channel-access-token'];
        $channel_secret       = $channel['channel-secret'];

        // LINE BOT
        $httpClient = apply_filters(LineConnect::FILTER_PREFIX . 'httpclient', new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token));
        $bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channel_secret));

        // バリデーション
        switch ($type) {
            case 'broadcast':
                $response = $bot->validateBroadcastMessage($message);
                break;
            case 'multicast':
                $response = $bot->validateMulticastMessage($message);
                break;
            case 'push':
                $response = $bot->validatePushMessage($message);
                break;
            case 'reply':
                $response = $bot->validateReplyMessage($message);
                break;
            case 'narrowcast':
                $response = $bot->validateNarrowcastMessage($message);
                break;
        }
        if ($response->getHTTPStatus() === 200) {
            return array('success' => true);
        } else {
            return array(
                'success' => false,
                'message' => Sender::prettyPrintLINEMessagingAPIError($response->getJSONDecodedBody()),
            );
        }
    }
}
