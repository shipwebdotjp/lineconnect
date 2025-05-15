<?php

/**
 * Bot.phpから呼び出されるファイルの処理系
 */

namespace Shipweb\LineConnect\Bot;

use \lineconnect;
use \lineconnectUtil;
use \lineconnectConst;

class File {


    // メッセージコンテント取得
    public static function getMessageContent($secret_prefix, $messageId, $userId) {
        //get $access_token, $channelSecret;
        $channel = lineconnect::get_channel($secret_prefix);
        if (!$channel) {
            return false;
        }
        $access_token = $channel['channel-access-token'];
        $channelSecret = $channel['channel-secret'];



        // Bot作成
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
        $bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channelSecret));

        // コンテンツ取得
        $response = $bot->getMessageContent($messageId);
        if ($response->getHTTPStatus() == 200) {
            // レスポンスからバイナリデータを取得
            $content = $response->getRawBody();
            // レスポンスのContent-Typeヘッダーからバイナリデータのファイル形式を取得
            $contentType = $response->getHeader('Content-Type');

            // 取得したファイル形式から適切なファイル拡張子を選択
            $file_extention = self::get_file_extention($contentType);

            // make file name from message id and file extention
            $file_name = $messageId . '.' . $file_extention;
            // set user directory
            $user_dir = substr($userId, 1, 4);
            // make directory
            $target_dir_path = lineconnectUtil::make_lineconnect_dir($user_dir);
            if ($target_dir_path) {
                // make file path
                $file_path = $target_dir_path . '/' . $file_name;
                // write file
                file_put_contents($file_path, $content);
                // return file path
                return $user_dir . '/' . $file_name;
            }
        }
        return false;
    }

    // MIME type to file Extension
    public static function get_file_extention($mime_type) {
        return lineconnectConst::MIME_MAP[$mime_type] ?? 'bin';
    }

    // update message
    public static function update_message_filepath($logId, $file_path) {
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnectConst::TABLE_BOT_LOGS;
        // get row from log table
        $row = $wpdb->get_row("SELECT * FROM {$table_name} WHERE id = {$logId}");
        if ($row) {
            // message column is JSON string, so decode to object
            $message = json_decode($row->message);
            // set file path
            $message->file_path = $file_path;
            // update message column
            return $wpdb->update($table_name, array('message' => json_encode($message)), array('id' => $logId));
        }
        return false;
    }
}
