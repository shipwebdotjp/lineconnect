<?php

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;

/**
 * Class rest_api_delete_sessions_bulk_Test
 * @package Shipweb\LineConnect\Tests\Interaction\Manage
 * @group interaction-manage
 */
class rest_api_delete_sessions_bulk_Test extends WP_UnitTestCase {

    protected $admin_user_id;
    protected $session_table;
    protected $session_ids = [];
    protected static $interaction_id;

    protected static $init_result;
    protected static $interaction_datas;
    protected static $interaction_ids;

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory): void {
        self::$init_result = lineconnectTest::init();
        self::$interaction_datas = [
            "シンプルインタラクション" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "steps" => [
                            [
                                "id" => "q1",
                                "title" => "最初のステップ",
                                "description" => "これはシンプルなインタラクションの最初のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの最初のステップの最初のメッセージです。",
                                    ],
                                ],
                                'nextStepId' => 'q2',
                            ],
                        ]
                    ],
                ],
            ],
        ];
        self::$interaction_ids = [];
        foreach (self::$interaction_datas as $interaction_name => $interaction_data) {
            $post_id = wp_insert_post(array(
                'post_title'   => $interaction_name,
                'post_type' => InteractionCPT::POST_TYPE,
                'post_status' => 'publish',
            ));
            update_post_meta($post_id, InteractionCPT::META_KEY_VERSION, 1);
            update_post_meta($post_id, InteractionCPT::META_KEY_DATA, $interaction_data);
            update_post_meta($post_id, LineConnect::META_KEY__SCHEMA_VERSION, InteractionCPT::SCHEMA_VERSION);
            self::$interaction_ids[$interaction_name] = $post_id;
        }
        self::$interaction_id = self::$interaction_ids['シンプルインタラクション'];
    }

    public function setUp(): void {
        parent::setUp();
        global $wpdb;
        $this->session_table = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;

        // 管理者ユーザーを作成してログイン
        $this->admin_user_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($this->admin_user_id);

        // テスト用のセッションデータを3つDBに挿入
        for ($i = 1; $i <= 3; $i++) {
            $wpdb->insert($this->session_table, [
                'interaction_id' => self::$interaction_id,
                'interaction_version' => 1,
                'channel_prefix' => 'test',
                'line_user_id' => "test_user_{$i}",
                'status' => 'active',
                'answers' => wp_json_encode(['q1' => "answer_{$i}"]),
                'created_at' => "2024-01-01 10:00:0{$i}",
                'updated_at' => "2024-01-01 11:00:0{$i}",
            ]);
            $this->session_ids[] = $wpdb->insert_id;
        }
    }

    public function tearDown(): void {
        parent::tearDown();
        global $wpdb;
        if (!empty($this->session_table)) {
            $wpdb->query("TRUNCATE TABLE {$this->session_table}");
        }
    }

    /**
     * 正常系: 複数のセッションが正常に一括削除されることを確認 (204 No Content)
     */
    public function test_delete_sessions_bulk_success() {
        global $wpdb;
        $interactionId = self::$interaction_id;

        $request = new WP_REST_Request('DELETE', sprintf('/%s/interactions/%d/sessions', LineConnect::PLUGIN_NAME, $interactionId));
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(wp_json_encode(['session_ids' => $this->session_ids]));

        $response = rest_do_request($request);
        $this->assertEquals(204, $response->get_status(), 'Expected 204 No Content from bulk delete endpoint');

        // DBからレコードが削除されていることを確認
        foreach ($this->session_ids as $session_id) {
            $deleted_session = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->session_table} WHERE id = %d", $session_id));
            $this->assertNull($deleted_session, "Session {$session_id} should be deleted from the database");
        }
    }

    /**
     * 異常系: 存在しないIDが含まれている場合、存在するIDのみ削除される
     */
    public function test_delete_sessions_bulk_with_non_existent_id() {
        global $wpdb;
        $interactionId = self::$interaction_id;
        $ids_to_delete = [$this->session_ids[0], 99999, $this->session_ids[2]]; // 1つ存在しないID

        $request = new WP_REST_Request('DELETE', sprintf('/%s/interactions/%d/sessions', LineConnect::PLUGIN_NAME, $interactionId));
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(wp_json_encode(['session_ids' => $ids_to_delete]));

        $response = rest_do_request($request);
        $this->assertEquals(204, $response->get_status(), 'Expected 204 even with non-existent IDs');

        // 存在するIDが削除されたか確認
        $this->assertNull($wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->session_table} WHERE id = %d", $this->session_ids[0])));
        $this->assertNull($wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->session_table} WHERE id = %d", $this->session_ids[2])));

        // 削除されるべきでなかったIDが残っているか確認
        $this->assertNotNull($wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->session_table} WHERE id = %d", $this->session_ids[1])));
    }

    /**
     * 異常系: session_idsが空配列の場合 (400 Bad Request)
     */
    public function test_delete_sessions_bulk_empty_ids() {
        $interactionId = self::$interaction_id;

        $request = new WP_REST_Request('DELETE', sprintf('/%s/interactions/%d/sessions', LineConnect::PLUGIN_NAME, $interactionId));
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(wp_json_encode(['session_ids' => []]));

        $response = rest_do_request($request);
        $this->assertEquals(400, $response->get_status(), 'Expected 400 Bad Request for empty session_ids');
    }

    /**
     * 異常系: 権限がない（ログアウト）場合は 401 or 403
     */
    public function test_delete_sessions_bulk_permission_denied() {
        wp_set_current_user(0); // ログアウト

        $interactionId = self::$interaction_id;

        $request = new WP_REST_Request('DELETE', sprintf('/%s/interactions/%d/sessions', LineConnect::PLUGIN_NAME, $interactionId));
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(wp_json_encode(['session_ids' => $this->session_ids]));

        $response = rest_do_request($request);
        $this->assertContains($response->get_status(), [401, 403], 'Expected 401 or 403 for permission denied');
    }
}
