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
            'edit.php?post_type=slc_audience',
            false,
            null
        );
    }

    /**
     * メタボックスの登録
     */
    static function register_meta_box() {
        add_meta_box(
            lineconnect::META_KEY__AUDIENCE_DATA,
            'LINE Connect Audience',
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
     * オーディエンスのJSONスキーマを返す
     */
    static function get_audience_schema() {
        $audience_schema = lineconnectConst::$lineconnect_audience_schema;
        $all_roles = array();
		foreach ( wp_roles()->roles as $role_name => $role ) {
			$all_roles[] = array(
				'const' => esc_attr($role_name),
				'title' => translate_user_role($role['name']),
			);
		}
        $audience_schema['definitions']['role']['items']['oneOf'] = $all_roles;
        $all_channels = array();
		foreach(lineconnect::get_all_channels() as $channel_id => $channel ) {
			$all_channels[] = array(
				'const' => $channel['prefix'],
				'title' => $channel['name'],
			);
		}
		if(count($all_channels) == 0){
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
		if(empty($schema_version) || $schema_version == lineconnectConst::AUDIENCE_SCHEMA_VERSION){
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
		$posts         = get_posts( $args );
		$audience_array = array();
		foreach ( $posts as $post ) {
			$audience_array[ $post->ID ] = $post->post_title;
		}
		return $audience_array;
	}

    /**
     * オーディエンスのデータを返す
     * @param $post_id 投稿ID
     * @return array 対応するLINEユーザーIDの配列
     */
    static function get_lineconnect_audience($post_id){
		$formData = get_post_meta( $post_id, lineconnect::META_KEY__AUDIENCE_DATA, true );
        if(empty($formData) || !isset($formData['condition'])){
            return array();
        }
        $result_line_user_ids = self::get_audience_by_condition($formData['condition']);
        return $result_line_user_ids;
    }

    /**
     * オーディエンス条件に応じたLINEユーザーIDの配列を返す
     * @param array $condition オーディエンス条件
     * @return array 対応するLINEユーザーIDの配列 ['channel_prefix' => ['type' => 'multicast', 'line_user_ids' => ['line_user_id1', 'line_user_id2', ...]]]
     */
    static function get_audience_by_condition($condition){
        $result_line_user_ids = array();
        if(!isset($condition['conditions'])){
            return $result_line_user_ids;
        }
        $line_user_ids_by_condition_item = array();
        foreach($condition['conditions'] as $condition_item){
            if($condition_item['type'] == 'channel'){
                $line_user_ids_by_condition_item[] = self::get_line_ids_by_channel($condition_item['secret_prefix']);
            }elseif($condition_item['type'] == 'link'){
                $line_user_ids_by_condition_item[] = self::get_line_ids_by_linkstatus($condition_item['link']['type']);
            }
        }
        if(!isset($condition['operator']) || $condition['operator'] === 'and'){
            // 配列のIDの論理積(AND)を取得
            $result_line_user_ids = self::get_and_arrays($line_user_ids_by_condition_item);
        }elseif($condition['operator'] === 'or'){
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
	static function get_line_ids_by_channel($secret_prefix){
		global $wpdb;
        $line_user_ids_by_channel = array();
		$table_name_line_id = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;
        foreach($secret_prefix as $prefix){
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
    static function get_line_ids_by_linkstatus($link_status){
        $result_line_user_ids = array();
        if($link_status === 'broadcast'){
            $result_line_user_ids = self::get_broadcast();
        }elseif($link_status === 'all'){
            $result_line_user_ids = self::get_all_line_ids();
        }elseif($link_status === 'linked'){
            $result_line_user_ids = self::get_all_linked_line_ids();
        }elseif($link_status === 'unlinked'){
            $result_line_user_ids = self::get_all_unlinked_line_ids();
        }
        return $result_line_user_ids;
    }

    /**
     * ブロードキャスト用配列を返す
     * @return array ブロードキャスト用配列　['channel_prefix' => ['type' => 'broadcast']]
     */
    static function get_broadcast(){
        $result_line_user_ids = array();
        foreach(lineconnect::get_all_channels() as $channel_id => $channel ){
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
            if(!isset($line_user_ids_by_channel[$row->channel_prefix])){
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
        if ( ! is_array( $roles ) ) {
			$roles = array( $roles );
		}
        $args          = array(
			'meta_query' => array(
				array(
					'key'     => lineconnect::META_KEY__LINE,
					'compare' => 'EXISTS',
				),
			),
			'fields'     => 'all_with_meta',
		);
        // $rolesに"linked"が含まれない場合は、そのロールユーザーを取得
		if ( ! in_array( 'linked', $roles ) ) {
			$args[$match_type] = $roles;
		}
		$user_query    = new WP_User_Query( $args ); // 条件を指定してWordPressからユーザーを検索
		$users         = $user_query->get_results(); // クエリ実行
		if ( ! empty( $users ) ) {   // マッチするユーザーが見つかれば
			// ユーザーのメタデータを取得
			foreach ( $users as $user ) {
				$user_meta_line = $user->get( lineconnect::META_KEY__LINE );
				if ( $user_meta_line ) {
                    foreach ( $user_meta_line as $secret_prefix => $user_meta_line_item ) {
						if ( isset( $user_meta_line_item['id'] ) ) {
                            if(!isset($line_user_ids_by_channel[$secret_prefix])){
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
        foreach($all_line_ids as $channel_prefix => $line_ids) {
            if(!isset($unlinked_line_ids[$channel_prefix])) {
                $unlinked_line_ids[$channel_prefix] = array('type' => 'multicast', 'line_user_ids' => array());
            }
            
            // 連携済みユーザーが存在しない場合は全ユーザーが未連携
            if(!isset($linked_line_ids[$channel_prefix])) {
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
            if(isset($values['type']) && $values['type'] === 'broadcast'){
                $result[$key] = $values;
            }else{
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
                    if(isset($array[$key]['type']) && $array[$key]['type'] === 'broadcast'){
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
                $result[$key] = $current_values;
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
                $result[$key] = [
                    'type' => 'multicast',
                    'line_user_ids' => array_values(array_unique($current_values['line_user_ids']))
                ];
            }
        }

        return $result;
    }


}
