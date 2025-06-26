<?php
/*
 * Initialize database for testing.
 * This file is included from the bootstrap file.
 * @package LineConnect
 * @since 1.0.0
 */

use Shipweb\LineConnect\Core\LineConnect;

class lineconnectTest {
    public static function init() {
        global $wpdb;
        $table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

        LineConnect::pluginActivation();
        $result = array(
            'user' => array(),
            'audience' => array(),
        );
        // チャネルデータを保存
        update_option(lineconnect::OPTION_KEY__CHANNELS, json_decode(file_get_contents(__DIR__ . '/testdata/channels.json'), true));
        // 新規ロールを追加
        add_role('teacher', __('Teacher'));
        add_role('student', __('Student'));

        // ユーザーを作成
        $user_array = json_decode(file_get_contents(__DIR__ . '/testdata/wp_users.json'), true);
        foreach ($user_array as $user) {
            $user_id = wp_create_user($user['login'], $user['password'], $user['email']);
            $u = new WP_User($user_id);
            if (isset($user['role'])) {
                $u->set_role(""); // clear role
                foreach ($user['role'] as $role) {
                    $u->add_role($role);
                }
            }
            if (isset($user['display_name'])) {
                // set display_name to new user using wp_update_user
                wp_update_user(array('ID' => $user_id, 'display_name' => $user['display_name']));
            }
            if (isset($user['meta'])) {
                foreach ($user['meta'] as $key => $value) {
                    update_user_meta($user_id, $key, $value);
                }
            }
            $result['user'][] = $u;
        }

        // LINE IDテーブルを初期化
        $wpdb->query("TRUNCATE TABLE $table_name_line_id");
        $line_id_array = json_decode(file_get_contents(__DIR__ . '/testdata/line_users.json'), true);
        foreach ($line_id_array as $line_id) {
            $wpdb->insert(
                $table_name_line_id,
                array(
                    'channel_prefix' => $line_id['channel_prefix'],
                    'line_id'        => $line_id['line_id'],
                    'follow'         => $line_id['follow'],
                    'profile'        => ! empty($line_id['profile']) ? json_encode($line_id['profile'], JSON_UNESCAPED_UNICODE) : null,
                    'tags'           => ! empty($line_id['tags']) ? json_encode($line_id['tags'], JSON_UNESCAPED_UNICODE) : null,
                ),
                array(
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                )
            );
        }


        return $result;
    }

    /**
     * ユーザーIDから期待されるLINEユーザーIDの配列を作成する
     * @param array $user_ids
     * @return array
     */
    public static function getExpectedLineIds($line_ids) {
        $line_ids_by_channel = array();
        $user_array = json_decode(file_get_contents(__DIR__ . '/testdata/line_users.json'), true);
        foreach ($user_array as $user) {
            if (isset($user['channel_prefix']) && isset($user['line_id'])) {
                if (!in_array($user['line_id'], $line_ids)) {
                    continue;
                }
                if (!isset($line_ids_by_channel[$user['channel_prefix']])) {
                    $line_ids_by_channel[$user['channel_prefix']] = array(
                        'type' => 'multicast',
                        'line_user_ids' => array(),
                    );
                }
                $line_ids_by_channel[$user['channel_prefix']]['line_user_ids'][] = $user['line_id'];
            }
        }
        foreach ($line_ids_by_channel as &$channel) {
            sort($channel['line_user_ids']);
        }
        return $line_ids_by_channel;
    }
}
