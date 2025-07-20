<?php

/**
 * Bot.phpから呼び出されるアカウント関連処理
 */

namespace Shipweb\LineConnect\Bot;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;


class Account {

    // アカウントリンク用のメッセージ作成
    public static function getLinkStartMessage($secret_prefix, $userId) {
        $channel = lineconnect::get_channel($secret_prefix);
        if (!$channel) {
            return false;
        }
        $access_token = $channel['channel-access-token'];
        $channelSecret = $channel['channel-secret'];

        // Bot作成
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
        $bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channelSecret));

        // ユーザーのLinkToken作成
        $response = $bot->createLinkToken($userId);
        // レスポンスをJSONデコード
        $res_json = $response->getJSONDecodedBody();
        // レスポンスからlinkToken取得
        $linkToken = $res_json['linkToken'];

        $root_dir = trailingslashit(dirname(__FILE__, substr_count(plugin_basename(__FILE__), '/')));
        // WordPressのサイトURLを取得
        $accountlink_url = plugins_url('accountlink.php', $root_dir . lineconnect::PLUGIN_ENTRY_FILE_NAME);
        $redirect_to     = urlencode($accountlink_url . '?linkToken=' . $linkToken);
        // WordPressにログインさせたあと、Nonceを作成してLINEへ送信するページへのリダイレクトをするURLを作成
        $gotologin_url = plugins_url('gotologin.php', $root_dir . lineconnect::PLUGIN_ENTRY_FILE_NAME);
        $url           = $gotologin_url . '?redirect_to=' . $redirect_to;

        // 連携開始メッセージ作成
        return Builder::createFlexMessage(
            array(
                'title' => lineconnect::get_option('link_start_title'),
                'body'  => lineconnect::get_option('link_start_body'),
                'type'  => 'uri',
                'label' => lineconnect::get_option('link_start_button'),
                'link'  => $url,
            )
        );
    }

    // アカウント連携解除
    public static function unAccountLink($secret_prefix, $userId) {
        // global $secret_prefix;
        // メタ情報からLINEユーザーIDでユーザー検索
        $user = lineconnect::get_wpuser_from_line_id($secret_prefix, $userId);
        // すでに連携されているユーザーが見つかれば
        if ($user) { // ユーザーが見つかればすでに連携されているということ
            $user_id = $user->ID; // IDを取得

            // リッチメニューを解除
            do_action('line_unlink_richmenu', $user_id, $secret_prefix);

            $user_meta_line = $user->get(lineconnect::META_KEY__LINE);
            if ($user_meta_line && $user_meta_line[$secret_prefix]) {
                unset($user_meta_line[$secret_prefix]);
                if (empty($user_meta_line)) {
                    // ほかに連携しているチャネルがなければメタデータ削除
                    if (delete_user_meta($user_id, lineconnect::META_KEY__LINE)) {
                        $mes = lineconnect::get_option('unlink_finish_body');
                    } else {
                        $mes = lineconnect::get_option('unlink_failed_body');
                    }
                } else {
                    // ほかに連携しているチャネルがあれば残りのチャネルが入ったメタデータを更新
                    update_user_meta($user_id, lineconnect::META_KEY__LINE, $user_meta_line);
                    $mes = lineconnect::get_option('unlink_finish_body');
                }
                // WP Line Loginと連携解除
                do_action('line_login_delete_user_meta', $user_id, $secret_prefix);
            } else {
                $mes = lineconnect::get_option('unlink_failed_body');
            }
        } else {
            $mes = lineconnect::get_option('unlink_failed_body');
        }
        return $mes;
    }
    // update line id profile
    public static function update_line_id_profile_for_new_user($secret_prefix, $line_id) {
        global $wpdb;
        $channel = lineconnect::get_channel($secret_prefix);
        if (!$channel) {
            return false;
        }
        $access_token = $channel['channel-access-token'];
        $channelSecret = $channel['channel-secret'];
        if (version_compare(lineconnect::get_current_db_version(), '1.2', '<')) {
            return;
        }
        $table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
        $line_id_row = \Shipweb\LineConnect\Utilities\LineId::line_id_row($line_id, $secret_prefix);
        if ($line_id_row) {
            return; // すでに登録されている場合は何もしない
        }
        $user_data = array();
        $is_follow = true;

        // get line profile via LINE Messaging API
        // Bot作成
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
        $bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channelSecret));

        // ユーザーのプロフィール取得
        $response = $bot->getProfile($line_id);
        // check if response is 200
        if ($response->getHTTPStatus() === 200) {
            // レスポンスをJSONデコード
            $profile = $response->getJSONDecodedBody();
            if (isset($profile['displayName'])) {
                $user_data['displayName'] = $profile['displayName'];
            }
            if (isset($profile['pictureUrl'])) {
                $user_data['pictureUrl'] = $profile['pictureUrl'];
            } else {
                unset($user_data['pictureUrl']);
            }
            if (isset($profile['language'])) {
                $user_data['language'] = $profile['language'];
            } else {
                unset($user_data['language']);
            }
            if (isset($profile['statusMessage'])) {
                $user_data['statusMessage'] = $profile['statusMessage'];
            } else {
                unset($user_data['statusMessage']);
            }
        } else {
            $is_follow = false;
        }

        // insert
        $result = $wpdb->insert(
            $table_name_line_id,
            array(
                'channel_prefix' => $secret_prefix,
                'line_id'        => $line_id,
                'follow'         => $is_follow,
                'profile'        => ! empty($user_data) ? json_encode($user_data, JSON_UNESCAPED_UNICODE) : null,
            ),
            array(
                '%s',
                '%s',
                '%d',
                '%s',
            )
        );
        if ($result === false) {
            error_log('insert_line_id_follow error');
        } else {
            // error_log('insert_line_id_follow success');
        }
    }

    // update line id follow
    public static function update_line_id_follow($secret_prefix, $line_id, $is_follow) {
        global $wpdb;
        $channel = lineconnect::get_channel($secret_prefix);
        if (!$channel) {
            return false;
        }
        $access_token = $channel['channel-access-token'];
        $channelSecret = $channel['channel-secret'];
        if (version_compare(lineconnect::get_current_db_version(), '1.2', '<')) {
            return;
        }
        $table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

        // update
        $result = $wpdb->update(
            $table_name_line_id,
            array(
                'follow'  => $is_follow,
            ),
            array(
                'channel_prefix' => $secret_prefix,
                'line_id'        => $line_id,
            ),
            array(
                '%d',
            ),
            array(
                '%s',
                '%s',
            )
        );
        if ($result === false) {
            error_log('update_line_id_follow error');
        }
    }
}
