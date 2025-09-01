<?php

namespace Shipweb\LineConnect\Message\LINE;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Logger as MessageLogger;
use Shipweb\LineConnect\Utilities\StreamConnector;
use Shipweb\LineConnect\Core\Stats;

class Sender {

    // 連携済みユーザーへロールを指定して送信($role に slc_linked が含まれるなら全ての連携済みユーザーへ送信)
    static function sendMessageRole($channel, $role, $message) {
        if (! $channel) {
            $channel = lineconnect::get_channel(0);
        }

        if (is_string($message)) {
            $message = Builder::createTextMessage($message);
        }

        $secret_prefix = substr($channel['channel-secret'], 0, 4);

        if (! is_array($role)) {
            $role = array($role);
        }

        // $roleが"slc_linked"が含まれる場合は全てのロールユーザーに送信
        if (in_array('slc_linked', $role)) {
            $role = array();
        }
        // 設定されているロールユーザーに送信
        $args          = array(
            'meta_query' => array(
                array(
                    'key'     => lineconnect::META_KEY__LINE,
                    'compare' => 'EXISTS',
                ),
            ),
            'role__in'   => $role,
            'fields'     => 'all_with_meta',
        );
        $line_user_ids = array();   // 送信するLINEユーザーIDの配列
        $user_query    = new \WP_User_Query($args); // 条件を指定してWordPressからユーザーを検索
        $users         = $user_query->get_results(); // クエリ実行
        if (! empty($users)) {   // マッチするユーザーが見つかれば
            // ユーザーのメタデータを取得
            foreach ($users as $user) {
                $user_meta_line = $user->get(lineconnect::META_KEY__LINE);
                if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
                    if (isset($user_meta_line[$secret_prefix]['id'])) {
                        $line_user_ids[] = $user_meta_line[$secret_prefix]['id'];
                    }
                }
            }
            return self::sendMulticastMessage($channel, $line_user_ids, $message);
        } else {
            return array(
                'success' => true,
                'num'     => 0,
            );
            // $error_message = '条件にマッチするユーザーがいませんでした';
        }
    }

    // 連携済みユーザーへWPユーザーを指定して送信
    static function sendMessageWpUser($channel, $wp_user_id, $message) {
        if (! $channel) {
            $channel = lineconnect::get_channel(0);
        }

        if (is_string($message)) {
            $message = Builder::createTextMessage($message);
        }

        $secret_prefix  = substr($channel['channel-secret'], 0, 4);
        $user_meta_line = get_user_meta($wp_user_id, lineconnect::META_KEY__LINE, true);
        if ($user_meta_line && isset($user_meta_line[$secret_prefix])) {
            if (isset($user_meta_line[$secret_prefix]['id'])) {
                return self::sendPushMessage($channel, $user_meta_line[$secret_prefix]['id'], $message);
            }
        }
    }

    //オーディエンスに送信
    static function sendAudienceMessage($recepient, $message, $notificationDisabled = false) {
        // LINEBOT SDKの読み込み
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
                $message = Builder::get_line_message_builder($message);
                $response = self::sendBroadcastMessage($channel, $message, $notificationDisabled);
                if ($response['success']) {
                    $success_message = __('Broadcast message sent successfully.', lineconnect::PLUGIN_NAME);
                } else {
                    $error_message = __('Broadcast message failed to send.', lineconnect::PLUGIN_NAME) . $response['message'];
                }
            } elseif ($type == 'multicast') {
                $message = Builder::get_line_message_builder($message);
                $response = self::sendMulticastMessage($channel, $recepient_item['line_user_ids'], $message, $notificationDisabled);
                if ($response['success']) {
                    // $success_message = __('Multicast message sent successfully.', lineconnect::PLUGIN_NAME);
                    $success_message = sprintf(_n('Multicast message sent to %s person.', 'Multicast message sent to %s people.', $response['num'], lineconnect::PLUGIN_NAME), number_format($response['num']));
                } else {
                    $error_message = __('Multicast message failed to send.', lineconnect::PLUGIN_NAME) . $response['message'];
                }
            } elseif ($type == 'push') {
                $ary_push_success = array();
                $ary_push_error = array();
                foreach ($recepient_item['line_user_ids'] as $line_user_id) {
                    $response = self::sendPushMessage($channel, $line_user_id, Builder::get_line_message_builder($message, self::make_injection_data($channel, $line_user_id)), $notificationDisabled);
                    if ($response['success']) {
                        $ary_push_success[] = $line_user_id;
                    } else {
                        $ary_push_error[] = $line_user_id;
                    }
                }
                if (empty($ary_push_error)) {
                    $success_message = sprintf(_n('Push message sent to %s person.', 'Push message sent to %s people.', count($ary_push_success), lineconnect::PLUGIN_NAME), number_format(count($ary_push_success)));
                } else {
                    $error_message = sprintf(_n('Push message failed to sent to %s person.', 'Push message failed to sent to %s people.', count($ary_push_error), lineconnect::PLUGIN_NAME), number_format(count($ary_push_error)));
                }
            }

            // 送信に成功した場合
            if ($success_message) {
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

    // プッシュ（一人のユーザーに送信）
    static function sendPushMessage($channel, $line_user_id, $message, $notificationDisabled = false,  ?\LINE\LINEBot\HTTPClient $httpClient = null) {
        // LINEBOT SDKの読み込み
        // require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

        $channel_access_token = $channel['channel-access-token'];
        $channel_secret       = $channel['channel-secret'];

        // LINE BOT
        if ($httpClient === null) {
            $httpClient = apply_filters(LineConnect::FILTER_PREFIX . 'httpclient', new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token));
        }
        $bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channel_secret));
        $message = self::replacePlaceHolder($channel, $line_user_id, $message);
        // プッシュで送信
        $response = $bot->pushMessage($line_user_id, $message, $notificationDisabled);
        // 応答メッセージをロギング
        self::writeOutboundMessageLog($message, 'push', $line_user_id, $channel['prefix'], $response);

        // 送信に成功した場合
        if ($response->getHTTPStatus() === 200) {
            if (class_exists('WP_Stream\Connector')) {
                $class = new StreamConnector();
                $class->callback_lineconnect_push_message(
                    array(
                        'id'    => null,
                        'title' => sprintf(__('Push to %s', lineconnect::PLUGIN_NAME), $line_user_id),
                    ),
                    false
                );
            }
            Stats::increase_stats_message($channel['prefix'], 'apiPush', 1);
            return array('success' => true);
        } else {
            return array(
                'success' => false,
                'message' => self::prettyPrintLINEMessagingAPIError($response->getJSONDecodedBody()),
            );
        }
    }

    // マルチキャスト（複数のユーザーに送信）
    static function sendMulticastMessage($channel, $line_user_ids, $message, $notificationDisabled = false,  ?\LINE\LINEBot\HTTPClient $httpClient = null) {
        // LINEBOT SDKの読み込み
        // require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

        $channel_access_token = $channel['channel-access-token'];
        $channel_secret       = $channel['channel-secret'];

        // LINE BOT
        if ($httpClient === null) {
            $httpClient = apply_filters(LineConnect::FILTER_PREFIX . 'httpclient', new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token));
        }
        $bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channel_secret));

        // 最大500人なので、500個ごとに配列を分割して送信
        foreach (array_chunk($line_user_ids, 500) as $line_user_id_chunk) {
            // マルチキャストで送信
            $response = $bot->multicast($line_user_id_chunk, $message, $notificationDisabled);
            // 応答メッセージをロギング
            self::writeOutboundMessageLog($message, 'multicast', $line_user_id_chunk, $channel['prefix'], $response);

            if ($response->getHTTPStatus() !== 200) {
                // error_log(print_r($response->getJSONDecodedBody(),true));
                return array(
                    'success' => false,
                    'message' => self::prettyPrintLINEMessagingAPIError($response->getJSONDecodedBody()),
                );
            }
        }
        if (class_exists('WP_Stream\Connector')) {
            $class = new StreamConnector();
            $class->callback_lineconnect_push_message(
                array(
                    'id'    => null,
                    'title' => sprintf(_n('%s multicast', '%s multicasts', count($line_user_ids), lineconnect::PLUGIN_NAME), number_format(count($line_user_ids))),
                ),
                false
            );
        }
        Stats::increase_stats_message($channel['prefix'], 'apiMulticast', count($line_user_ids));
        // 送信に成功した場合
        return array(
            'success' => true,
            'num'     => count($line_user_ids),
        );
    }

    // ブロードキャスト（すべての友達登録されているユーザーに送信）
    static function sendBroadcastMessage($channel, $message, $notificationDisabled = false,  ?\LINE\LINEBot\HTTPClient $httpClient = null) {
        // LINEBOT SDKの読み込み
        // require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

        $channel_access_token = $channel['channel-access-token'];
        $channel_secret       = $channel['channel-secret'];

        // LINE BOT
        if ($httpClient === null) {
            $httpClient = apply_filters(LineConnect::FILTER_PREFIX . 'httpclient', new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token));
        }
        $bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channel_secret));

        $response = $bot->broadcast($message, $notificationDisabled);
        // 応答メッセージをロギング
        self::writeOutboundMessageLog($message, 'broadcast', [], $channel['prefix'], $response);
        if ($response->getHTTPStatus() === 200) {
            if (class_exists('WP_Stream\Connector')) {
                $class = new StreamConnector();
                $class->callback_lineconnect_push_message(
                    array(
                        'id'    => null,
                        'title' => __('Broadcast', lineconnect::PLUGIN_NAME),
                    ),
                    false
                );
            }
            Stats::increase_stats_message($channel['prefix'], 'apiBroadcast', null);
            return array('success' => true);
        } else {
            return array(
                'success' => false,
                'message' => self::prettyPrintLINEMessagingAPIError($response->getJSONDecodedBody()),
            );
        }
    }

    // LINEメッセージ送信時のエラーを文字列に変換
    static function prettyPrintLINEMessagingAPIError($error) {
        // エラーのメインメッセージ
        $output = "<h2>" . __('Error', lineconnect::PLUGIN_NAME) . ": " . htmlspecialchars(\Shipweb\LineConnect\Utilities\Translate::dynamic_translate($error['message']), ENT_QUOTES, 'UTF-8') . "</h2>";

        // エラーの詳細がある場合
        if (isset($error['details']) && is_array($error['details'])) {
            $output .= "<ul>";
            foreach ($error['details'] as $detail) {
                $output .= "<li>";
                $output .= "<strong>" . __('Property', lineconnect::PLUGIN_NAME) . ": </strong> " . htmlspecialchars(\Shipweb\LineConnect\Utilities\Translate::dynamic_translate($detail['property']), ENT_QUOTES, 'UTF-8') . "<br>";
                $output .= "<strong>" . __('Message', lineconnect::PLUGIN_NAME) . ": </strong> " . htmlspecialchars(\Shipweb\LineConnect\Utilities\Translate::dynamic_translate($detail['message']), ENT_QUOTES, 'UTF-8');
                $output .= "</li>";
            }
            $output .= "</ul>";
        }

        return $output;
    }

    /**
     * プッシュメッセージ用にプレースホルダーを置換する
     * @param array $channel チャネル情報
     * @param string $line_user_id LINEユーザーID
     * @param \LINE\LINEBot\MessageBuilder\MultiMessageBuilder $message プレースホルダーを含んだメッセージオブジェクト
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder $message 置換済みメッセージオブジェクト
     */
    static function replacePlaceHolder($channel, $line_user_id, $message) {
        // メッセージに含まれるプレースホルダーへユーザーデータの埋め込み
        $injection_data = self::make_injection_data($channel, $line_user_id);
        $messages = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($message->buildMessage(), $injection_data);
        // メッセージオブジェクトの再構築
        $multimessagebuilder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        foreach ($messages as $msg) {
            $multimessagebuilder->add(Builder::createRawMessage($msg));
        }
        return $multimessagebuilder;
    }

    /**
     * ユーザー用のインジェクションデータを作成する
     * 
     * @param array $channnel チャネル情報
     * @param string $line_user_id LINEユーザーID
     * @return array
     */
    static function make_injection_data($channel, $line_user_id) {
        $user_data = lineconnect::get_userdata_from_line_id($channel['prefix'], $line_user_id);
        $injection_data = array(
            'user' => $user_data,
        );
        return $injection_data;
    }

    //応答メッセージをロギング
    static function writeOutboundMessageLog($message, $type, $line_user_id, $secret_prefix, $response) {
        $source_type = 'system';
        if (current_user_can('manage_options')) {
            $source_type = 'admin';
        } elseif (current_user_can('read')) {
            $source_type = 'user';
        }
        // 応答メッセージをロギング
        MessageLogger::writeOutboundMessageLog(
            $message,
            $type,
            $source_type,
            $line_user_id,
            $secret_prefix,
            $response->isSucceeded() ? 'sent' : 'failed',
            $response->getJSONDecodedBody()
        );
    }
}
