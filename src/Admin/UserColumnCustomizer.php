<?php


namespace Shipweb\LineConnect\Admin;

use Shipweb\LineConnect\Core\LineConnect;

class UserColumnCustomizer {
    // 連携状態を表示するカラムを追加
    public static function lc_manage_columns($columns) {
        $add_columns = array(
            'lc_islinked' => __('LINE', LineConnect::PLUGIN_NAME)
        );
        return array_merge($columns, $add_columns);
    }

    // 管理画面のカラムに連携状態を表示させる
    public static function lc_manage_custom_columns($output, $column_name, $user_id) {
        switch ($column_name) {
            case 'lc_islinked':
                $linked_label   = __('&#9745;', LineConnect::PLUGIN_NAME);
                $unlinked_label = __('&#9744;', LineConnect::PLUGIN_NAME);
                $ary_output     = array();
                foreach (LineConnect::get_all_channels() as $channel_id => $channel) {
                    $secret_prefix  = substr($channel['channel-secret'], 0, 4);
                    $user_meta_line = get_user_meta($user_id, LineConnect::META_KEY__LINE, true);
                    if ($user_meta_line && isset($user_meta_line[$secret_prefix]['id'])) {
                        $line_sendmessage_url = add_query_arg(
                            array(
                                'line_id'        => $user_meta_line[$secret_prefix]['id'],
                                'channel_prefix' => $secret_prefix,
                                'action'         => 'message',
                            ),
                            admin_url('admin.php?page=' . LineConnect::SLUG__DM_FORM)
                        );
                        $label = isset($user_meta_line[$secret_prefix]['displayName'])
                            ? $user_meta_line[$secret_prefix]['displayName']
                            : $linked_label;
                        $ary_output[] = "<a href=\"{$line_sendmessage_url}\" title=\"{$label}\">{$label}</a>";
                    } else {
                        $ary_output[] = $unlinked_label;
                    }
                }
                return implode("/", $ary_output);
        }
        return $output;
    }

    // ユーザー一括操作にメッセージ送信を追加
    public static function add_bulk_users_sendmessage($actions) {
        $actions['lc_linechat'] = __('Send LINE Message', LineConnect::PLUGIN_NAME);
        return $actions;
    }

    // ユーザー一括操作でLINEメッセージ送信が選択されたら
    public static function handle_bulk_users_sendmessage($sendback, $doaction, $items) {
        if ($doaction !== 'lc_linechat') {
            return $sendback;
        }
        $user_args = array();
        foreach ($items as $index => $userid) {
            $user_args["users[{$index}]"] = $userid;
        }
        $redirect_url = add_query_arg($user_args, admin_url('admin.php?page=' . LineConnect::SLUG__BULKMESSAGE_FORM));
        return $redirect_url;
    }
}
