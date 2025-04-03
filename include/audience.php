<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lineconnect Audience Class
 *
 * LINE Connect Audience
 *
 * @package Lineconnect
 * @subpackage Audience
 * @category Components
 * @package  Audience
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

class lineconnectAudience {

    /**
     * 管理画面メニューの追加
     */
    static function set_plugin_menu() {
        add_submenu_page(
            lineconnect::SLUG__DASHBOARD,
            __('LINE Connect Audience', lineconnect::PLUGIN_NAME),
            __('Audiences', lineconnect::PLUGIN_NAME),
            'manage_options',
            'edit.php?post_type=' . lineconnectConst::POST_TYPE_AUDIENCE,
            false,
            NULL
        );
    }

    /**
     * メタボックスの登録
     */
    static function register_meta_box() {
        add_meta_box(
            lineconnect::META_KEY__AUDIENCE_DATA,
            __('LINE Connect Audience', lineconnect::PLUGIN_NAME),
            array('lineconnectAudience', 'show_audience_form'),
            lineconnectConst::POST_TYPE_AUDIENCE,
            'advanced',
            'default'
        );
    }

    /**
     * 管理画面用スクリプトの読み込み
     */
    static function wpdocs_selectively_enqueue_admin_script() {
        require_once plugin_dir_path(__FILE__) . 'rjsf.php';
        lineconnectRJSF::wpdocs_selectively_enqueue_admin_script(lineconnectConst::POST_TYPE_AUDIENCE);
    }

    /**
     * オーディエンスフォームの表示
     */
    static function show_audience_form() {
        $ary_init_data = array();
        $formName = lineconnect::PARAMETER__AUDIENCE_DATA;
        $ary_init_data['formName'] = $formName;

        $schema_version = get_post_meta(get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true);
        $formData = get_post_meta(get_the_ID(), lineconnect::META_KEY__AUDIENCE_DATA, true);

        // 単一フォームのスキーマとUIスキーマ
        $form = array(
            'id' => 'audience',
            'schema' => self::get_audience_schema(),
            'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_audience_uischema', lineconnectConst::$lineconnect_audience_uischema),
            'formData' => !empty($formData[0]) ? self::get_form_audience_data($formData[0], $schema_version) : new StdClass(),
            'props' => new StdClass(),
        );
        $ary_init_data['translateString'] = lineconnectConst::$lineconnect_rjsf_translate_string;
        $ary_init_data['form'] = array($form);
        $nonce_field = wp_nonce_field(
            lineconnect::CREDENTIAL_ACTION__AUDIENCE,
            lineconnect::CREDENTIAL_NAME__AUDIENCE,
            true,
            false
        );

        require_once plugin_dir_path(__FILE__) . 'rjsf.php';
        lineconnectRJSF::show_json_edit_form($ary_init_data, $nonce_field);
    }

    /**
     * 投稿の保存
     */
    static function save_post_audience($post_ID, $post, $update) {
        if (isset($_POST[lineconnect::CREDENTIAL_NAME__AUDIENCE]) && check_admin_referer(lineconnect::CREDENTIAL_ACTION__AUDIENCE, lineconnect::CREDENTIAL_NAME__AUDIENCE)) {
            $audience_data = isset($_POST[lineconnect::PARAMETER__AUDIENCE_DATA]) ? stripslashes($_POST[lineconnect::PARAMETER__AUDIENCE_DATA]) : '';

            if (!empty($audience_data)) {
                $json_audience_data = json_decode($audience_data, true);
                if (!empty($json_audience_data)) {
                    update_post_meta($post_ID, lineconnect::META_KEY__AUDIENCE_DATA, $json_audience_data);
                    update_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION, lineconnectConst::AUDIENCE_SCHEMA_VERSION);
                } else {
                    delete_post_meta($post_ID, lineconnect::META_KEY__AUDIENCE_DATA);
                    delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
                }
            } else {
                delete_post_meta($post_ID, lineconnect::META_KEY__AUDIENCE_DATA);
                delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
            }
        }
    }

    /**
     * ダウンロードカラム追加
     */
    public static function add_download_column($columns) {
        $new_columns = array();

        // タイトルの後にステータスカラムを挿入
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['download'] = __('Download', lineconnect::PLUGIN_NAME);
            }
        }

        return $new_columns;
    }

    /**
     * ダウンロードカラムの表示
     */
    public static function add_download_column_content($column_name, $post_id) {
        if ($column_name == 'download') {
            $audience = get_post_meta($post_id, lineconnect::META_KEY__AUDIENCE_DATA, true);
            if (!empty($audience)) {
                echo '<a href="' . esc_url(admin_url('admin.php?page=' . lineconnect::SLUG__AUDIENCE_DOWNLOAD . '&audience_id=' . $post_id)) . '" >' . __('CSV Download', lineconnect::PLUGIN_NAME) . '</a>';
            }
        }
    }

    /**
     * ダウンロードメニュー追加
     */
    static function set_download_menu() {
        add_options_page(
            __('LINE Connect Audience Download', lineconnect::PLUGIN_NAME),
            __('Download Audiences', lineconnect::PLUGIN_NAME),
            'manage_options',
            lineconnect::SLUG__AUDIENCE_DOWNLOAD,
            array('lineconnectAudience', 'download_audience_page')
        );
        /*
        remove_submenu_page(
            'options-general.php',
            lineconnect::SLUG__AUDIENCE_DOWNLOAD
        );
        */
    }

    /**
     * CSVダウンロード
     */
    static function download_audience_page() {
        nocache_headers();
        $audience_id = isset($_GET['audience_id']) ? intval($_GET['audience_id']) : 0;
        $line_user_ids = self::get_lineconnect_audience($audience_id);
        $csv_data = array(); // secret_prefix, line_user_id 
        $csv = '';
        foreach ($line_user_ids as $secret_prefix => $recepient_item) {
            if ($recepient_item['type'] == 'multicast' || $recepient_item['type'] == 'push') {
                foreach ($recepient_item['line_user_ids'] as $line_user_id) {
                    $csv_data[] = array($line_user_id);
                }
            }
        }

        if (!empty($csv_data)) {
            foreach ($csv_data as $row) {
                $csv .= implode(',', $row) . "\n";
            }
        }
        $filename = 'lineconnect_audience_id_' . $audience_id . '_' . date('YmdHis') . '.csv';

        // Clear any output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($csv));
        echo $csv;
        exit;
    }

    /**
     * オーディエンスのJSONスキーマを返す
     */
    static function get_audience_schema() {
        $audience_schema = lineconnectConst::$lineconnect_audience_schema;
        $all_roles = array();
        foreach (wp_roles()->roles as $role_name => $role) {
            $all_roles[] = array(
                'const' => esc_attr($role_name),
                'title' => translate_user_role($role['name']),
            );
        }
        $audience_schema['definitions']['role']['items']['oneOf'] = $all_roles;
        $all_channels = array();
        foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
            $all_channels[] = array(
                'const' => $channel['prefix'],
                'title' => $channel['name'],
            );
        }
        if (count($all_channels) == 0) {
            $all_channels[] = array(
                'const' => '',
                'title' => __('Please add channel first', lineconnect::PLUGIN_NAME),
            );
        }
        $audience_schema['definitions']['secret_prefix']['items']['oneOf'] = $all_channels;
        return apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_audience_schema', $audience_schema);
    }

    /** 
     * Return audience data
     */
    static function get_form_audience_data($formData, $schema_version) {
        if (empty($schema_version) || $schema_version == lineconnectConst::AUDIENCE_SCHEMA_VERSION) {
            return !empty($formData) ? $formData : new stdClass();
        }
        // if old schema veersion, migrate and return
    }


    /**
     * Return audience array object post_id and title
     */
    static function get_lineconnect_audience_name_array() {
        $args          = array(
            'post_type'      => lineconnectConst::POST_TYPE_AUDIENCE,
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        );
        $posts         = get_posts($args);
        $audience_array = array();
        foreach ($posts as $post) {
            $audience_array[$post->ID] = $post->post_title;
        }
        return $audience_array;
    }

    /**
     * オーディエンスのデータを返す
     * @param $post_id 投稿ID
     * @return array 対応するLINEユーザーIDの配列
     */
    static function get_lineconnect_audience($post_id, $args = null) {
        $formData = get_post_meta($post_id, lineconnect::META_KEY__AUDIENCE_DATA, true);

        if (empty($formData) || !isset($formData[0]['condition'])) {
            return array();
        }
        $audience = $formData[0];
        if (!empty($args)) {
            $audience = lineconnectUtil::replacePlaceHolder($audience, $args);
        }
        $result_line_user_ids = self::get_audience_by_condition($audience['condition']);
        return $result_line_user_ids;
    }

    /**
     * オーディエンス条件に応じたLINEユーザーIDの配列を返す
     * @param array $condition オーディエンス条件
     * @return array 対応するLINEユーザーIDの配列 ['channel_prefix' => ['type' => 'multicast', 'line_user_ids' => ['line_user_id1', 'line_user_id2', ...]]]
     */
    static function get_audience_by_condition($condition) {

        $result_line_user_ids = array();
        if (!isset($condition['conditions'])) {
            return $result_line_user_ids;
        }
        $line_user_ids_by_condition_item = array();
        foreach ($condition['conditions'] as $condition_item) {
            if ($condition_item['type'] == 'channel') {
                $line_user_ids_by_condition_item[] = self::get_line_ids_by_channel($condition_item['secret_prefix']);
            } elseif ($condition_item['type'] == 'link') {
                $line_user_ids_by_condition_item[] = self::get_line_ids_by_linkstatus($condition_item['link']['type']);
            } elseif ($condition_item['type'] == 'role') {
                $line_user_ids_by_condition_item[] = self::get_all_linked_line_ids($condition_item['role'] ?? [], $condition_item['match'] ?? null);
            } elseif ($condition_item['type'] == 'lineUserId') {
                $line_user_ids_by_condition_item[] = self::get_line_ids_by_lineuserid($condition_item['lineUserId']);
            } elseif ($condition_item['type'] == 'wpUserId') {
                $line_user_ids_by_condition_item[] = self::get_line_ids_by_wpuserid($condition_item['wpUserId']);
            } elseif (in_array($condition_item['type'], ['user_login', 'display_name', 'user_email'])) {
                $line_user_ids_by_condition_item[] = self::get_line_ids_by_userfields($condition_item);
            } elseif ($condition_item['type'] == 'usermeta') {
                $line_user_ids_by_condition_item[] = self::get_line_ids_by_usermeta($condition_item['usermeta']);
            } elseif ($condition_item['type'] == 'profile') {
                $line_user_ids_by_condition_item[] = self::get_line_ids_by_profile($condition_item['profile']);
            } elseif ($condition_item['type'] == 'group') {
                $line_user_ids_by_condition_item[] = self::get_audience_by_condition($condition_item['condition']);
            }
        }
        if (!isset($condition['operator']) || $condition['operator'] === 'and') {
            // 配列のIDの論理積(AND)を取得
            $result_line_user_ids = self::get_and_arrays($line_user_ids_by_condition_item);
        } elseif ($condition['operator'] === 'or') {
            // 配列のIDの論理和(OR)を取得
            $result_line_user_ids = self::get_or_arrays($line_user_ids_by_condition_item);
        }
        return $result_line_user_ids;
    }

    /**
     * チャネルからLINEユーザーIDの配列を取得
     * @param array $secret_prefix チャネルシークレット先頭4文字の配列
     * @return array LINEユーザーIDの配列 ['channel_prefix' => ['type' => 'multicast', 'line_user_ids' => ['line_user_id1', 'line_user_id2', ...]]]
     */
    static function get_line_ids_by_channel($secret_prefix) {
        global $wpdb;
        $line_user_ids_by_channel = array();
        $table_name_line_id = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;
        foreach ($secret_prefix as $prefix) {
            $line_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT line_id FROM {$table_name_line_id} WHERE channel_prefix = %s",
                    array(
                        $prefix,
                    )
                )
            );
            $line_user_ids_by_channel[$prefix] = array('type' => 'multicast', 'line_user_ids' => $line_ids);
        }
        return $line_user_ids_by_channel;
    }

    /**
     * 連携状態に応じたユーザーIDの配列を返す
     * @param string $link_status 連携状態
     * @return array secret_prefixをキー、配信タイプをtype, 対応するLINEユーザーIDの配列をline_user_idsとして持つオブジェクトを値とする連想配列
     * ブロードキャストの場合: ['channel_prefix' => ['type' => 'broadcast']]
     * マルチキャストの場合: ['channel_prefix' => ['type' => 'multicast', 'line_user_ids' => ['line_user_id1', 'line_user_id2', ...]]]
     */
    static function get_line_ids_by_linkstatus($link_status) {
        $result_line_user_ids = array();
        if ($link_status === 'broadcast') {
            $result_line_user_ids = self::get_broadcast();
        } elseif ($link_status === 'all') {
            $result_line_user_ids = self::get_all_line_ids();
        } elseif ($link_status === 'linked') {
            $result_line_user_ids = self::get_all_linked_line_ids();
        } elseif ($link_status === 'unlinked') {
            $result_line_user_ids = self::get_all_unlinked_line_ids();
        }
        return $result_line_user_ids;
    }

    /**
     * ブロードキャスト用配列を返す
     * @return array ブロードキャスト用配列　['channel_prefix' => ['type' => 'broadcast']]
     */
    static function get_broadcast() {
        $result_line_user_ids = array();
        foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
            $result_line_user_ids[$channel['prefix']] = array('type' => 'broadcast');
        }
        return $result_line_user_ids;
    }

    /**
     * 認識しているすべてのLINEユーザーIDの配列を返す
     * @return array 認識しているすべてのLINEユーザーIDの配列　['channel_prefix' => ['type' => 'multicast', 'line_user_ids' => [...]]]
     */
    static function get_all_line_ids() {
        global $wpdb;
        $line_user_ids_by_channel = array();
        $table_name_line_id = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;

        $results = $wpdb->get_results(
            "SELECT channel_prefix, line_id FROM {$table_name_line_id}"
        );

        $line_user_ids_by_channel = array();
        foreach ($results as $row) {
            if (!isset($line_user_ids_by_channel[$row->channel_prefix])) {
                $line_user_ids_by_channel[$row->channel_prefix] = array('type' => 'multicast', 'line_user_ids' => array());
            }
            $line_user_ids_by_channel[$row->channel_prefix]['line_user_ids'][] = $row->line_id;
        }

        return $line_user_ids_by_channel;
    }

    /**
     * 連携済みのLINEユーザーIDの配列を返す
     * @param array|string $roles ユーザーロール linked: 連携済みのユーザー
     * @param string $match_type ロールのマッチタイプ　role: 全ロールにマッチする, role__in: いずれかのロールにマッチする role__not_in: いずれのロールにもマッチしない
     * @return array 連携済みのLINEユーザーIDの配列　['channel_prefix' => ['type' => 'multicast', 'line_user_ids' => [...]]]
     */
    static function get_all_linked_line_ids($roles = array(), $match_type = 'role__in') {
        $line_user_ids_by_channel = array();
        if (! is_array($roles)) {
            $roles = array($roles);
        }
        $args = array();
        // $rolesに"linked"が含まれない場合は、そのロールユーザーを取得
        if (! in_array('linked', $roles)) {
            $args[$match_type] = $roles;
        }
        return self::get_line_ids_by_wpuserquery($args);
    }

    /**
     * WP_USER_Queryを使って、指定されたargsで取得したWPユーザーのLINEユーザーIDの配列を返す
     * @param array $args WP_User_Queryに渡すargs
     * @return array LINEユーザーIDの配列　['channel_prefix' => ['type' => 'multicast', 'line_user_ids' => [...]]]
     */
    static function get_line_ids_by_wpuserquery($args) {
        $args['fields'] = 'all_with_meta';
        $meta_query = array(
            'key'     => lineconnect::META_KEY__LINE,
            'compare' => 'EXISTS',
        );
        if (isset($args['meta_query'])) {
            $args['meta_query'][] = $meta_query;
        } else {
            $args['meta_query'] = array($meta_query);
        }

        $user_query    = new WP_User_Query($args); // 条件を指定してWordPressからユーザーを検索
        $users         = $user_query->get_results(); // クエリ実行
        if (! empty($users)) {   // マッチするユーザーが見つかれば
            // ユーザーのメタデータを取得
            foreach ($users as $user) {
                $user_meta_line = $user->get(lineconnect::META_KEY__LINE);
                if ($user_meta_line) {
                    foreach ($user_meta_line as $secret_prefix => $user_meta_line_item) {
                        if (isset($user_meta_line_item['id'])) {
                            if (!isset($line_user_ids_by_channel[$secret_prefix])) {
                                $line_user_ids_by_channel[$secret_prefix] = array('type' => 'multicast', 'line_user_ids' => array());
                            }
                            $line_user_ids_by_channel[$secret_prefix]['line_user_ids'][] = $user_meta_line_item['id'];
                        }
                    }
                }
            }
            return $line_user_ids_by_channel;
        } else {
            return array();
        }
    }

    /**
     * 未連携のLINEユーザーIDの配列を返す
     * @return array 未連携のLINEユーザーIDの配列 ['channel_prefix' => ['type' => 'multicast', 'line_user_ids' => [...]]]
     */
    static function get_all_unlinked_line_ids() {
        // 未連携ユーザーID = 認識しているすべてのLINEユーザーIDの配列 - 連携済みのLINEユーザーIDの配列
        // 認識しているすべてのLINEユーザーIDの配列を取得
        $all_line_ids = self::get_all_line_ids();
        // 連携済みのLINEユーザーIDの配列を取得
        $linked_line_ids = self::get_all_linked_line_ids();

        $unlinked_line_ids = array();

        // 各チャンネルごとに未連携ユーザーを抽出
        foreach ($all_line_ids as $channel_prefix => $line_ids) {
            if (!isset($unlinked_line_ids[$channel_prefix])) {
                $unlinked_line_ids[$channel_prefix] = array('type' => 'multicast', 'line_user_ids' => array());
            }

            // 連携済みユーザーが存在しない場合は全ユーザーが未連携
            if (!isset($linked_line_ids[$channel_prefix])) {
                $unlinked_line_ids[$channel_prefix]['line_user_ids'] = $line_ids['line_user_ids'];
                continue;
            }

            // 全ユーザーから連携済みユーザーを除外して未連携ユーザーを取得
            $unlinked_line_ids[$channel_prefix]['line_user_ids'] = array_values(
                array_diff($line_ids['line_user_ids'], $linked_line_ids[$channel_prefix]['line_user_ids'])
            );
        }

        return $unlinked_line_ids;
    }

    /**
     * LINEユーザーIDの配列から、チャネル情報を含めたLINEユーザーIDの配列を返す
     * @param array $line_ids LINEユーザーIDの配列 ['U1aaa', 'Udb39', ...]
     * @return array LINEユーザーIDの配列 ['{channel_prefix}' => ['type' => 'multicast', 'line_user_ids' => ['U1aaa', 'Udb39', ...]]]
     */
    static function get_line_ids_by_lineuserid($line_ids) {
        global $wpdb;
        $line_user_ids_by_channel = array();
        $table_name_line_id = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;

        if (!empty($line_ids)) {
            $placeholders = array_fill(0, count($line_ids), '%s');
            $query = "SELECT channel_prefix, line_id FROM {$table_name_line_id} WHERE line_id IN (" . implode(',', $placeholders) . ")";

            $results = $wpdb->get_results(
                $wpdb->prepare($query, $line_ids)
            );

            foreach ($results as $row) {
                if (!isset($line_user_ids_by_channel[$row->channel_prefix])) {
                    $line_user_ids_by_channel[$row->channel_prefix] = array('type' => 'multicast', 'line_user_ids' => array());
                }
                $line_user_ids_by_channel[$row->channel_prefix]['line_user_ids'][] = $row->line_id;
            }
        }

        return $line_user_ids_by_channel;
    }

    /**
     * WPユーザーIDの配列から、チャネル情報を含めたLINEユーザーIDの配列を返す
     * @param array $wp_user_ids WPユーザーIDの配列 [1, 2, 3, ...]
     * @return array LINEユーザーIDの配列 ['{channel_prefix}' => ['type' => 'multicast', 'line_user_ids' => ['U1aaa', 'Udb39', ...]]]
     */
    static function get_line_ids_by_wpuserid($wp_user_ids) {
        if (empty($wp_user_ids)) {
            return array();
        }
        $args = array(
            'include'    => $wp_user_ids,
        );
        return self::get_line_ids_by_wpuserquery($args);
    }

    /**
     * ユーザーのフィールドからLINEユーザーIDの配列を取得
     * @param array $condition_item 条件項目
     * @return array LINEユーザーIDの配列 ['{channel_prefix}' => ['type' => 'multicast', 'line_user_ids' => ['U1aaa', 'Udb39', ...]]]
     */
    static function get_line_ids_by_userfields($condition_item) {
        $line_user_ids_by_channels = array();
        $type = $condition_item['type'];
        $items = $condition_item[$type];
        foreach ($items as $item) {
            $args = array(
                'search' => $item,
                'search_columns' => array($type),
            );
            $line_user_ids_by_channels[] = self::get_line_ids_by_wpuserquery($args);
        }
        return self::get_or_arrays($line_user_ids_by_channels);
    }

    /**
     * ユーザーメタからLINEユーザーIDの配列を取得
     * @param array $usermeta ユーザーメタの配列
     * @return array LINEユーザーIDの配列 ['{channel_prefix}' => ['type' => 'multicast', 'line_user_ids' => ['U1aaa', 'Udb39', ...]]]
     */
    static function get_line_ids_by_usermeta($usermetas) {
        $line_user_ids_by_channels = array();
        foreach ($usermetas as $usermeta) {
            if (!isset($usermeta['key'])) {
                continue;
            }
            $key = $usermeta['key'];
            $compare = $usermeta['compare'] ?? '=';
            $meta_query = array(
                'key'     => $key,
                'compare' => $compare,
            );
            if (in_array($compare, ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
                $meta_query['value'] = $usermeta['values'] ?? [];
            } elseif (isset($usermeta['value'])) {
                $meta_query['value'] = $usermeta['value'];
            }
            $args = array(
                'meta_query' => array(
                    $meta_query,
                ),
            );
            $line_user_ids_by_channels[] = self::get_line_ids_by_wpuserquery($args);
        }
        return self::get_or_arrays($line_user_ids_by_channels);
    }

    /**
     * プロフィールの絞り込み条件からLINEユーザーIDの配列を取得
     * @param array $profile_conditions プロフィールの絞り込み条件
     * @return array LINEユーザーIDの配列 ['{channel_prefix}' => ['type' => 'multicast', 'line_user_ids' => ['U1aaa', 'Udb39', ...]]]
     */
    static function get_line_ids_by_profile($profile_conditions) {
        $line_user_ids_by_channels = array();
        foreach ($profile_conditions as $profile_condition) {
            $line_user_ids_by_channels[] = self::get_line_ids_by_profilefields($profile_condition);
        }
        return self::get_or_arrays($line_user_ids_by_channels);
    }

    /**
     * 個別のプロフィール絞り込み条件からLINEユーザーIDの配列を取得
     * @param array $profile_condition 個別のプロフィール絞り込み条件
     * @return array LINEユーザーIDの配列 ['{channel_prefix}' => ['type' => 'multicast', 'line_user_ids' => ['U1aaa', 'Udb39', ...]]]
     */
    static function get_line_ids_by_profilefields($profile_condition) {
        $line_user_ids_by_channel = array();
        if (!empty($profile_condition)) {
            if (isset($profile_condition['key'])) {
                $results = self::do_profile_query($profile_condition);
                if (empty($results)) {
                    return array();
                }

                foreach ($results as $row) {
                    if (!isset($line_user_ids_by_channel[$row->channel_prefix])) {
                        $line_user_ids_by_channel[$row->channel_prefix] = array('type' => 'multicast', 'line_user_ids' => array());
                    }
                    $line_user_ids_by_channel[$row->channel_prefix]['line_user_ids'][] = $row->line_id;
                }
            }
        }

        return $line_user_ids_by_channel;
    }

    /**
     * クエリを組み立てて実行する
     * @param array $clause WHERE句の条件 key, compare, value, values
     * @return SQLの実行結果
     */
    static function do_profile_query($clause) {
        global $wpdb;
        $table_name_line_id = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;
        $placeholders = array();

        $key = $clause['key'];
        $compare = isset($clause['compare']) ? strtoupper($clause['compare']) : '=';

        // JSONキーのエスケープ処理
        $escaped_key = str_replace('"', '\\"', $key);
        $json_path = '$."' . $escaped_key . '"';
        $json_access = "profile->>'" . $json_path . "'";

        $where = "";
        $condition = "";
        $values = array();

        if (in_array($compare, ['IN', 'NOT IN'])) {
            $values = isset($clause['values']) ? (array)$clause['values'] : array();
            if (!empty($values)) {
                $placeholders = $values;
                $placeholders_str = implode(',', array_fill(0, count($values), '%s'));
                $condition = "{$json_access} {$compare} ({$placeholders_str})";
            } else {
                $condition = '0=1';
            }
        } elseif (in_array($compare, ['BETWEEN', 'NOT BETWEEN'])) {
            $values = isset($clause['values']) ? (array)$clause['values'] : array();
            if (count($values) === 2) {
                $condition = "CAST({$json_access} AS " . self::get_cast_type($values[0]) . ") {$compare} %s AND %s";
                $placeholders = $values;
            } else {
                $condition = '0=1';
            }
        } elseif (in_array($compare, ['EXISTS', 'NOT EXISTS'])) {
            $condition = "JSON_CONTAINS_PATH(profile, 'one', %s) " . ($compare === 'EXISTS' ? '= 1' : '= 0');
            $placeholders = array($json_path);
        } else {
            $value = isset($clause['value']) ? $clause['value'] : null;
            if ($value !== null) {
                $valid_compares = ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'REGEXP', 'NOT REGEXP'];
                if (!in_array($compare, $valid_compares)) {
                    $compare = '=';
                }

                if (in_array($compare, ['>', '>=', '<', '<='])) {
                    $condition = "CAST({$json_access} AS " . self::get_cast_type($value) . ") {$compare} %s";
                    $placeholders[] = $value;
                    //　LIKE, NOT LIKE add % 
                } elseif (in_array($compare, ['LIKE', 'NOT LIKE'])) {
                    $condition = "{$json_access} {$compare} %s";
                    $placeholders[] = "%{$value}%";
                } else {
                    $condition = "{$json_access} {$compare} %s";
                    $placeholders[] = $value;
                }
            } else {
                $condition = '0=1';
            }
        }

        if ($condition) {
            $where = "WHERE {$condition}";
        }

        $query = "SELECT channel_prefix, line_id FROM {$table_name_line_id} {$where}";
        // var_dump($query);

        if (!empty($placeholders)) {
            // var_dump($placeholders);
            $prepared_query = $wpdb->prepare($query, $placeholders);
        } else {
            $prepared_query = $query;
        }

        return $wpdb->get_results($prepared_query);
    }

    /**
     * 配列のIDの論理積(AND)を取得
     * @param array $arrays 配列 ex: [['aaaa' => ['type' => 'broadcast'], 'bbbb' => ['type' => 'multicast', 'line_user_ids' => [2,4,6]]], ['aaaa' => ['type' => 'multicast', 'line_user_ids' => [2,3,5]], 'bbbb' => ['type' => 'multicast', 'line_user_ids' => [4,6,8]]]]
     * @return array 論理積(AND)の配列 ex: [['aaaa' => ['type' => 'broadcast'], 'bbbb' => ['type' => 'multicast', 'line_user_ids' => [4,6]]]]
     */
    static function get_and_arrays($arrays) {
        if (empty($arrays)) {
            return [];
        }

        // 最初の配列を取得
        $first_array = array_shift($arrays);
        $result = [];


        // 最初の配列の各キー(チャネルプリフィックス)に対して処理
        foreach ($first_array as $key => $values) {
            //　typeがmulticastの場合は、論理積を取る broadcastの場合、broadcastを優先
            if (isset($values['type']) && $values['type'] === 'broadcast') {
                $result[$key] = $values;
            } else {
                $current_values = $values;

                // 残りの配列の同じキーと論理積を取る
                foreach ($arrays as $array) {
                    // キーが存在しない場合は空の配列を設定
                    if (!isset($array[$key])) {
                        $current_values = [
                            'type' => $current_values['type'],
                            'line_user_ids' => [],
                        ];
                        break;
                    }
                    //　typeがbroadcastの場合は、broadcastを優先
                    if (isset($array[$key]['type']) && $array[$key]['type'] === 'broadcast') {
                        $current_values = $array[$key];
                        break;
                    }

                    $current_values = [
                        'type' => $current_values['type'],
                        'line_user_ids' => array_values(array_intersect($current_values['line_user_ids'], $array[$key]['line_user_ids'])),
                    ];

                    // 論理積が空になった時点で終了（最適化）
                    if (empty($current_values['line_user_ids'])) {
                        break;
                    }
                }
                if ($current_values['type'] === 'broadcast' || !empty($current_values['line_user_ids'])) {
                    $result[$key] = $current_values;
                }
            }
        }

        return $result;
    }

    /**
     * 配列のIDの論理和(OR)を取得
     * @param array $arrays 配列 ex: [['aaaa' => ['type' => 'broadcast'], 'bbbb' => ['type' => 'multicast', 'line_user_ids' => [2,4,6]]], ['aaaa' => ['type' => 'multicast', 'line_user_ids' => [2,3,5]], 'bbbb' => ['type' => 'multicast', 'line_user_ids' => [4,6,8]]]]
     * @return array 論理和(OR)の配列 ex: [['aaaa' => ['type' => 'broadcast'], 'bbbb' => ['type' => 'multicast', 'line_user_ids' => [2,4,6,8]]]]
     */
    static function get_or_arrays($arrays) {
        if (empty($arrays)) {
            return [];
        }
        // print_r($arrays);

        // 最初の配列を取得
        $first_array = array_shift($arrays);
        $result = [];

        // すべてのキーを収集
        $all_keys = array_keys($first_array);
        foreach ($arrays as $array) {
            $all_keys = array_unique(array_merge($all_keys, array_keys($array)));
        }

        // 各キーに対して論理和を計算
        foreach ($all_keys as $key) {
            $current_values = [
                'type' => 'multicast',
                'line_user_ids' => []
            ];

            // 最初の配列の値を処理
            if (isset($first_array[$key])) {
                if ($first_array[$key]['type'] === 'broadcast') {
                    $result[$key] = $first_array[$key];
                    continue;
                }
                $current_values['line_user_ids'] = $first_array[$key]['line_user_ids'];
            }

            // 残りの配列の値を結合
            foreach ($arrays as $array) {
                if (isset($array[$key])) {
                    if ($array[$key]['type'] === 'broadcast') {
                        $result[$key] = $array[$key];
                        continue 2;
                    }
                    $current_values['line_user_ids'] = array_merge(
                        $current_values['line_user_ids'],
                        $array[$key]['line_user_ids']
                    );
                }
            }

            // 重複を除去して結果に追加
            if (!empty($current_values['line_user_ids'])) {
                $line_ids = array_values(array_unique($current_values['line_user_ids']));
                if (!empty($line_ids)) {
                    $result[$key] = [
                        'type' => 'multicast',
                        'line_user_ids' => $line_ids,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * 与えられたデータの形式を判断してどの型にキャストするかを返す
     * @param mixed $value 値 ex: 1, '1', 1.0, '1.0', 'a', '2025-10-21', '2025-01-01 00:00:00', '12:00:00'
     * @return string キャストする型の文字列
     */
    static function get_cast_type($value) {
        if (is_int($value)) {
            // 整数
            return $value >= 0 ? 'UNSIGNED' : 'SIGNED';
        } elseif (is_float($value)) {
            // 浮動小数点
            return 'FLOAT';
        } elseif (is_numeric($value)) {
            // 文字列形式の数値
            return strpos((string)$value, '.') !== false ? 'FLOAT' : ($value >= 0 ? 'UNSIGNED' : 'SIGNED');
        } elseif (self::is_date($value)) {
            // 日付形式
            return 'DATE';
        } elseif (self::is_datetime($value)) {
            // 日時形式
            return 'DATETIME';
        } elseif (self::is_time($value)) {
            // 時刻形式
            return 'TIME';
        } else {
            // その他
            return 'CHAR';
        }
    }

    /**
     * 日付形式かどうかを判定 (例: '2025-10-21')
     * @param string $value
     * @return bool
     */
    private static function is_date($value) {
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) === $value;
    }

    /**
     * 日時形式かどうかを判定 (例: '2025-01-01 00:00:00')
     * @param string $value
     * @return bool
     */
    private static function is_datetime($value) {
        $format = 'Y-m-d H:i:s';
        $d = DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) === $value;
    }

    /**
     * 時刻形式かどうかを判定 (例: '12:00:00')
     * @param string $value
     * @return bool
     */
    private static function is_time($value) {
        $format = 'H:i:s';
        $d = DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) === $value;
    }


    // Ajaxでオーディエンスデータを返す
    static function ajax_get_slc_audience() {
        $isSuccess = true;
        $formData = [];
        // ログインしていない場合は無視
        if (! is_user_logged_in()) {
            $isSuccess = false;
        }
        // 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
        if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
            $isSuccess = false;
        }
        // nonceで設定したcredentialをPOST受信していない場合は無視
        if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
            $isSuccess = false;
        }
        // nonceで設定したcredentialのチェック結果に問題がある場合
        if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
            $isSuccess = false;
        }

        if (! isset($_POST['post_id']) || ! $_POST['post_id']) {
            $isSuccess = false;
        }

        if ($isSuccess) {
            $post_id = $_POST['post_id'];
            $formData  = get_post_meta($post_id, lineconnect::META_KEY__AUDIENCE_DATA, true);
        }
        $result['result']  = $isSuccess ? 'success' : 'failed';
        $result['formData'] = $formData;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        wp_die();
    }


    /**
     * オーディエンスから各チャネルの送信人数を返す
     * @param array $audience
     * @return array チャネルごとの送信人数の配列
     */
    static function get_recepients_count($audience) {
        $response = array(
            'success' => true,
            'success_messages' => array(),
            'error_messages' => array()
        );
        if (empty($audience)) {
            $response['error_messages'][] = __('The message will not be sent to anyone.', lineconnect::PLUGIN_NAME);
            return $response;
        }
        foreach ($audience as $secret_prefix => $audience_item) {
            $channel = lineconnect::get_channel($secret_prefix);
            if ($audience_item['type'] == 'broadcast') {
                $response['success_messages'][] = $channel['name'] . ': ' . __('Message will be sent to all users who have subscribed to this channel.', lineconnect::PLUGIN_NAME);
            } elseif ($audience_item['type'] == 'multicast') {
                $response['success_messages'][] = $channel['name'] . ': ' . sprintf(_n('Message will be sent to %s person.', 'Message will be sent to %s people.', count($audience_item['line_user_ids']), lineconnect::PLUGIN_NAME), number_format(count($audience_item['line_user_ids'])));
            }
        }
        return $response;
    }
}
