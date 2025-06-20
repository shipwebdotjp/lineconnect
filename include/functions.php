<?php

/**
 * Lineconnect Functions Class
 *
 * Functions Class
 *
 * @category Components
 * @package  Functions
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

use Shipweb\LineConnect\Scenario\Scenario;
use Shipweb\LineConnect\PostType\Message\Message as SLCMessage;
use Shipweb\LineConnect\Message\LINE\Builder;

class lineconnectFunctions {
	// public $lineUserId;
	public $secret_prefix;
	public $event;
	public $scenario_id;

	public function __construct() {
	}

	/*
		public function set_line_user_id( string $lineUserId ) {
		$this->lineUserId = $lineUserId;
	} */

	public function set_secret_prefix(string $secret_prefix) {
		$this->secret_prefix = $secret_prefix;
	}

	public function set_event(object $event) {
		$this->event = $event;
	}

	public function set_scenario_id(int $scenario_id) {
		$this->scenario_id = $scenario_id;
	}

	public static function get_callable_functions($only_enabled_gpt = false) {
		// get post form custom post type by WP_Query
		$functions = array();
		/*
		$args      = array(
			'post_type'      => lineconnectConst::POST_TYPE_ACTION,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		*/
		if ($only_enabled_gpt) {
			$enabled_functions = lineconnect::get_option(('openai_enabled_functions'));
		}
		/*
		$posts = get_posts( $args );

		foreach ( $posts as $post ) {
			$action = get_post_meta( $post->ID, lineconnect::META_KEY__ACTION_DATA, true );
			if ( ! $only_enabled_gpt || in_array( $action['function'], $enabled_functions ) ) {
				$functions[ $action['function'] ] = array(
					'title'       => get_the_title(),
					'description' => $action['description'],
					'parameters'  => $action['parameters'],
					'namespace'   => $action['namespace'],
					'role'        => $action['role'],
				);
			}
		}
		*/
		$lineconnect_actions = apply_filters(lineconnect::FILTER_PREFIX . 'actions', lineconnectConst::$lineconnect_actions);
		foreach ($lineconnect_actions as $name => $action) {
			if (! $only_enabled_gpt || in_array($name, $enabled_functions)) {
				$functions[$name] = array(
					'title'       => $action['title'],
					'description' => $action['description'],
					'parameters'  => $action['parameters'] ?? [],
					'namespace'   => $action['namespace'],
					'role'        => $action['role'],
				);
			}
		}


		return $functions;
	}

	// 自分のユーザー情報取得
	function get_my_user_info() {
		// メタ情報からLINEユーザーIDでユーザー検索
		$user = lineconnect::get_wpuser_from_line_id($this->secret_prefix, $this->event->source->userId);
		if ($user) { // ユーザーが見つかればすでに連携されているということ
			return array(
				'linkstatus'      => 'linked',
				'user_id'         => $user->ID,
				'user_login'      => $user->user_login,
				'user_email'      => $user->user_email,
				'user_nicename'   => $user->user_nicename,
				'display_name'    => $user->display_name,
				'user_registered' => $user->user_registered,
			);
		} else {
			$line_id_row  = lineconnectUtil::line_id_row($this->event->source->userId, $this->secret_prefix);
			if ($line_id_row) {
				$profile = json_decode($line_id_row['profile'], true);
				return array(
					'linkstatus'      => 'not_linked',
					'display_name'    => $profile['displayName'],
				);
			}
			return array(
				'error'   => 'not_linked',
				'message' => 'You are not linked to WordPress',
			);
		}
	}

	// 現在日時取得
	function get_the_current_datetime() {
		return array('datetime' => date(DATE_RFC2822));
	}

	// Twigレンダリング結果取得
	function render_template($body, $args) {
		$loader = new \Twig\Loader\ArrayLoader(
			array(
				'template' => $body,
			)
		);
		$twig = new \Twig\Environment($loader);
		return $twig->render('template', $args);
	}

	// 記事検索
	function WP_Query($args) {
		// set not overwrite args
		$args['has_password']   = false;      // パスワードが掛かっていない投稿のみ
		$args['post_status']    = 'publish';   // 公開ステータスの投稿のみに限定
		$args['posts_per_page'] = (isset($args['posts_per_page']) && $args['posts_per_page'] <= 5 ? $args['posts_per_page'] : 5);   // 取得する投稿を５件までに制限

		// get post
		$the_query = new WP_Query($args);
		$posts     = array();
		if ($the_query->have_posts()) {
			while ($the_query->have_posts()) {
				$the_query->the_post();
				$post_object = get_post();
				$post        = array();
				$post['ID']  = $post_object->ID;
				// $post["post_name"] = $post_object->post_name;
				$post['post_type']     = $post_object->post_type;
				$post['post_title']    = get_the_title();
				$post['post_date']     = $post_object->post_date;
				$post['post_modified'] = $post_object->post_modified;
				if ($the_query->found_posts > 1) {
					$post['post_excerpt'] = get_the_excerpt();
				} else {
					// omit conent size to 1024
					$post['post_content'] = strip_tags($post_object->post_content);
					if (mb_strlen($post['post_content']) > 1024) {
						$post['post_content'] = mb_substr($post['post_content'], 0, 1023) . '…';
					}
				}
				$post['permalink'] = get_permalink();

				$posts[] = $post;
			}
		}
		return $posts;
	}

	// ユーザー検索
	function WP_User_Query($args) {
		$args['number'] = (isset($args['number']) && $args['number'] <= 20 ? $args['number'] : 20); // 取得する投稿を５件までに制限
		$args['fields'] = 'all_with_meta';

		// get user
		$the_query = new WP_User_Query($args);
		$users     = array();
		if (! empty($the_query->get_results())) {
			foreach ($the_query->get_results() as $user) {
				$user_data = array();

				$user_data['ID']              = $user->ID;
				$user_data['user_login']      = $user->user_login;
				$user_data['user_email']      = $user->user_email;
				$user_data['user_nicename']   = $user->user_nicename;
				$user_data['display_name']    = $user->display_name;
				$user_data['user_registered'] = $user->user_registered;
				$user_meta_line               = $user->get(lineconnect::META_KEY__LINE);
				$user_data[lineconnect::META_KEY__LINE] = $user_meta_line;
				if ($user_meta_line && isset($this->secret_prefix) && $user_meta_line[$this->secret_prefix]) {
					$user_data['linkstatus'] = 'linked';
				}
				// if meta_key is included in args, get meta_value and include in user_data
				if (! empty($args['meta_key'])) {
					$user_data[$args['meta_key']] = $user->get($args['meta_key']);
				}
				$users[] = $user_data;
			}
		}
		return $users;
	}

	// LINETEXT メッセージ取得
	function get_text_message($body) {
		return Builder::createTextMessage($body);
	}

	// LC 通知メッセージ取得
	function get_button_message($title, $body, $thumb, $type, $label, $link, $displayText = null, $atts = null) {
		error_log(print_r(array(
			'title' => $title,
			'body'  => $body,
			'thumb' => $thumb,
			'type'  => $type,
			'label' => $label,
			'link'  => $link,
			'displayText' => $displayText,
			'atts'  => $atts,
		), true));
		$message = Builder::createFlexMessage(
			array(
				'title' => $title,
				'body'  => $body,
				'thumb' => $thumb,
				'type'  => $type,
				'label' => $label,
				'link'  => $link,
				'displayText' => $displayText,
			),
			$atts
		);
		return $message;
	}

	/**
	 * Return LINE Connect message
	 */
	function get_line_connect_message($slc_message_id, $args = null) {
		return SLCMessage::get_lineconnect_message($slc_message_id, $args);
	}

	/**
	 * LINEメッセージのJSONを受け取って、構築したLINEメッセージを返す
	 * 
	 * @param $raw LINEメッセージのJSON
	 * @return MessageBuilder
	 */
	function get_raw_message($raw) {
		//if raw is string to JSON
		if (is_string($raw)) {
			$raw = json_decode($raw, true);
		}
		return 	Builder::createRawMessage($raw);
	}

	function send_mail_to_admin($subject, $body) {
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: LINECONNECT <' . get_option('admin_email') . '>',
		);
		return wp_mail(get_option('admin_email'), $subject, $body, $headers);
	}

	function send_line_message($message, $line_user_id = null, $secret_prefix = null) {
		$message = lineconnectUtil::get_line_message_builder($message);
		$line_user_id = $line_user_id ? $line_user_id : $this->event->source->userId;
		if (!preg_match('/^U[a-f0-9]{32}$/', $line_user_id)) {
			return array(
				'success' => false,
				'message' => "<h2>" . __('Error: Invalid line user ID.', lineconnect::PLUGIN_NAME),
			);
		}
		$channel = lineconnect::get_channel(isset($this->secret_prefix) ? $this->secret_prefix : $secret_prefix);
		$response = Builder::sendPushMessage($channel, $line_user_id, $message);
		return $response;
	}

	/**
	 * オーディエンスからLINEメッセージを送信する
	 * @param mixed message メッセージ
	 * @param int slc_audience_id LCオーディエンスID
	 * @return array LINE APIのレスポンス
	 */
	function send_line_message_by_audience($message, $slc_audience_id, $message_args = null, $audience_args = null, $notification_disabled = false) {
		$message = lineconnectUtil::get_line_message_builder($message, $message_args);
		$audience = lineconnectUtil::get_lineconnect_audience($slc_audience_id, $audience_args);
		if (!empty($audience)) {
			$response = Builder::sendAudienceMessage($audience, $message, $notification_disabled);
			return $response;
		} else {
			return array(
				'success' => false,
				'message' => "<h2>" . __('Error: Invalid audience ID.', lineconnect::PLUGIN_NAME),
			);
		}
	}

	/**
	 * ユーザーのリッチメニューを設定する
	 * @param string $richmenu_id リッチメニューID
	 * @param string $line_user_id LINEユーザーID
	 * @param string $secret_prefix チャネルID
	 * @return array LINE APIのレスポンス
	 */
	function link_richmenu($richmenu_id, $line_user_id = null, $secret_prefix = null) {
		$channel = lineconnect::get_channel(isset($this->secret_prefix) ? $this->secret_prefix : $secret_prefix);
		$line_user_id = $line_user_id ? $line_user_id : $this->event->source->userId;
		if ($channel) {
			require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');

			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel['channel-access-token']);
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel['channel-secret']]);

			$response = $bot->linkRichMenu($line_user_id, $richmenu_id);
			return $response;
		}
		return null;
	}

	/**
	 * ユーザーメタを取得
	 * @param int $user_id WordPressユーザーID
	 * @param string $key メタキー
	 * @return mixed メタの値
	 */
	function get_user_meta($user_id, $key) {
		return get_user_meta($user_id, $key, true);
	}

	/**
	 * ユーザーメタを更新
	 * @param int $user_id WordPressユーザーID
	 * @param string $key メタキー
	 * @param mixed $value メタの値
	 * @return bool 成功・失敗
	 */
	function update_user_meta($user_id, $key, $value) {
		if (!lineconnectUtil::is_empty($value)) {
			return update_user_meta($user_id, $key, $value);
		} else {
			return delete_user_meta($user_id, $key);
		}
	}

	/**
	 * LINEユーザープロフィールに保存されている値を取得
	 * 
	 * @param string $key キー
	 * @param string $line_user_id LINEユーザーID
	 * @param string $secret_prefix チャネルシークレットの先頭4文字
	 * @return mixed|null 値（存在しない場合は null）
	 */
	function get_user_profile_value($key, $line_user_id = null, $secret_prefix = null) {
		global $wpdb;
		$channel_prefix = $secret_prefix ? $secret_prefix : $this->secret_prefix;
		$line_user_id = $line_user_id ? $line_user_id : $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// プロフィール情報を取得
		$profile_json = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT profile FROM $table_name WHERE line_id = %s AND channel_prefix = %s",
				$line_user_id,
				$channel_prefix
			)
		);

		if ($profile_json === null) {
			return null;
		}

		$profile = json_decode($profile_json, true);

		// ドット記法で階層を分解
		$keys = explode('.', $key);
		$value = $profile;

		// 階層を順に探索
		foreach ($keys as $k) {
			if (!is_array($value) || !isset($value[$k])) {
				return null;
			}
			$value = $value[$k];
		}

		return $value;
	}

	/**
	 * LINEユーザープロフィールに値を保存
	 * @param string $key キー
	 * @param mixed $value
	 * @param string $line_user_id LINEユーザーID
	 * @param string $secret_prefix チャネルシークレットの先頭4文字
	 */
	function update_user_profile($key, $value, $line_user_id = null, $secret_prefix = null) {
		global $wpdb;
		$channel_prefix = $secret_prefix ? $secret_prefix : $this->secret_prefix;
		$line_user_id = $line_user_id ? $line_user_id : $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// 現在のプロフィールを取得
		$current_profile = $wpdb->get_var(
			$wpdb->prepare("SELECT profile FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id,  $channel_prefix)
		);

		$profile_array = json_decode($current_profile, true) ?: [];

		if (! lineconnectUtil::is_empty($value)) {
			$profile_array[$key] = $value;
		} else {
			unset($profile_array[$key]);
		}

		// データベースを更新
		return $wpdb->update(
			$table_name,
			['profile' => json_encode($profile_array, JSON_UNESCAPED_UNICODE)],
			['line_id' => $line_user_id]
		);
	}

	/**
	 * LINEユーザータグを取得
	 * 
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array タグ
	 */
	function get_user_tags(?string $line_user_id = null, ?string $secret_prefix = null): array {
		global $wpdb;
		$channel_prefix = $secret_prefix ?? $this->secret_prefix;
		$line_user_id = $line_user_id ?? $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// タグを取得
		$tags = $wpdb->get_var(
			$wpdb->prepare("SELECT tags FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id,  $channel_prefix)
		);

		if ($tags === null) {
			return [];
		}

		return json_decode($tags, true) ?: [];
	}

	/**
	 * LINEユーザータグにタグを追加
	 * 
	 * @param array $tags 追加するタグの配列
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return bool 成功・失敗
	 */
	function add_user_tags(array $tags, ?string $line_user_id = null, ?string $secret_prefix = null): bool {
		global $wpdb;
		$channel_prefix = $secret_prefix ?? $this->secret_prefix;
		$line_user_id = $line_user_id ?? $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// 現在のタグを取得
		$current_tags = $wpdb->get_var(
			$wpdb->prepare("SELECT tags FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id,  $channel_prefix)
		);

		$tags_array = $current_tags ? (json_decode($current_tags, true) ?: []) : [];

		// タグを追加
		foreach ($tags as $tag) {
			if (!in_array($tag, $tags_array)) {
				$tags_array[] = $tag;
			}
		}

		// データベースを更新
		return $wpdb->update(
			$table_name,
			['tags' => json_encode($tags_array, JSON_UNESCAPED_UNICODE)],
			['line_id' => $line_user_id]
		);
	}

	/**
	 * LINEユーザータグからタグを削除
	 * 
	 * @param array $tags 削除するタグの配列
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return bool 成功・失敗
	 */
	function remove_user_tags(array $tags, ?string $line_user_id = null, ?string $secret_prefix = null): bool {
		global $wpdb;
		$channel_prefix = $secret_prefix ?? $this->secret_prefix;
		$line_user_id = $line_user_id ?? $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// 現在のタグを取得
		$current_tags = $wpdb->get_var(
			$wpdb->prepare("SELECT tags FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id,  $channel_prefix)
		);

		$tags_array = json_decode($current_tags, true) ?: [];

		// タグを削除
		foreach ($tags as $tag) {
			$tags_array = array_diff($tags_array, [$tag]);
		}

		// データベースを更新
		return $wpdb->update(
			$table_name,
			['tags' => json_encode($tags_array, JSON_UNESCAPED_UNICODE)],
			['line_id' => $line_user_id]
		);
	}

	/**
	 * LINEユーザータグを更新
	 * 
	 * @param array $tags タグの配列
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return bool 成功・失敗
	 */
	function update_user_tags(array $tags, ?string $line_user_id = null, ?string $secret_prefix = null): bool {
		global $wpdb;
		$channel_prefix = $secret_prefix ?? $this->secret_prefix;
		$line_user_id = $line_user_id ?? $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// データベースを更新
		return $wpdb->update(
			$table_name,
			['tags' => json_encode($tags, JSON_UNESCAPED_UNICODE)],
			['line_id' => $line_user_id]
		);
	}

	/**
	 * シナリオを開始する
	 * 
	 * @param int $scenario_id シナリオID
	 * @param ?string $flg フラグ
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 成功・失敗
	 */
	function start_scenario(int $scenario_id, ?string $flg = null, ?string $line_user_id = null, ?string $secret_prefix = null): array {
		$line_user_id = $line_user_id ?? $this->event->source->userId;
		$secret_prefix = $secret_prefix ?? $this->secret_prefix;

		if (empty($line_user_id) || empty($secret_prefix)) {
			return ['result' => 'error', 'message' => 'Invalid user ID or secret prefix'];
		}
		if (is_null($flg)) {
			$flg = Scenario::START_FLAG_RESTART_NONE;
		}
		return Scenario::start_scenario($scenario_id, $flg, $line_user_id, $secret_prefix);
	}

	/**
	 * 指定したステップへ移動
	 * 
	 * @param ?int $scenario_id シナリオID
	 * @param ?string $step_id ステップID
	 * @param ?string $next_date 次回実行日時
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 成功・失敗
	 */
	function set_scenario_step(?int $scenario_id = null, ?string $step_id = null, ?string $next_date = null, ?string $line_user_id = null, ?string $secret_prefix = null): array {
		$line_user_id = $line_user_id ?? $this->event->source->userId;
		$secret_prefix = $secret_prefix ?? $this->secret_prefix;
		$scenario_id = $scenario_id ?? $this->scenario_id;

		if (empty($line_user_id) || empty($secret_prefix) || empty($scenario_id)) {
			return ['result' => 'error', 'message' => 'Invalid user ID or secret prefix'];
		}

		return Scenario::set_scenario_step($scenario_id, $step_id, $next_date, $line_user_id, $secret_prefix);
	}

	/**
	 * 指定したステップを実行
	 * @param ?int $scenario_id シナリオID
	 * @param ?string $step_id ステップID
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 成功・失敗
	 */
	function execute_scenario_step(?int $scenario_id = null, ?string $step_id = null, ?string $line_user_id = null, ?string $secret_prefix = null): array {
		$line_user_id = $line_user_id ?? $this->event->source->userId;
		$secret_prefix = $secret_prefix ?? $this->secret_prefix;
		$scenario_id = $scenario_id ?? $this->scenario_id;

		if (empty($line_user_id) || empty($secret_prefix) || empty($scenario_id)) {
			return ['result' => 'error', 'message' => 'Invalid user ID or secret prefix'];
		}

		return Scenario::execute_step($scenario_id, $step_id, $line_user_id, $secret_prefix, wp_date('Y-m-d H:i:s'));
	}


	/**
	 * シナリオの状態を変更する
	 * 
	 * @param int $scenario_id シナリオID
	 * @param string $status 新しい状態
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return bool 成功・失敗
	 */
	function change_scenario_status(int $scenario_id, string $status, ?string $line_user_id = null, ?string $secret_prefix = null): bool {
		$line_user_id = $line_user_id ?? $this->event->source->userId;
		$secret_prefix = $secret_prefix ?? $this->secret_prefix;
		if (empty($line_user_id) || empty($secret_prefix)) {
			return false;
		}
		return Scenario::update_scenario_status($scenario_id, $status, $line_user_id, $secret_prefix);
	}
}
