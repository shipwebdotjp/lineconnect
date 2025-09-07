<?php

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;

/**
 * Class RESTAPIDeleteTest
 * @package Shipweb\LineConnect\Tests\Interaction\Manage
 * @group interaction-manage
 */
class rest_api_delete_session_Test extends WP_UnitTestCase {

    protected $admin_user_id;
    protected $session_table;
    protected $insert_id;
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
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの最初のステップの2番目のメッセージです。",
                                    ],
                                ],
                                'nextStepId' => 'q2',
                            ],
                            [
                                "id" => "q2",
                                "title" => "q2",
                                "description" => "これはシンプルなインタラクションの2番目のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの2番目のステップの最初のメッセージです。",
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの2番目のステップの2番目のメッセージです。",
                                    ],
                                ],
                                'stop' => true,
                            ],
                            [
                                "id" => "step-complete",
                                "title" => "完了",
                                "description" => "これはシンプルなインタラクションの完了ステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの完了ステップの最初のメッセージです。",
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの完了ステップの2番目のメッセージです。",
                                    ],
                                ],
                                'special' => 'complete',
                            ]
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

        // テスト用のセッションデータをDBに挿入
        $wpdb->insert($this->session_table, [
            'interaction_id' => self::$interaction_id,
            'interaction_version' => 1,
            'channel_prefix' => 'test',
            'line_user_id' => 'test_user_1',
            'status' => 'active',
            'answers' => wp_json_encode(['q1' => 'original']),
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 11:00:00',
        ]);
        // insert_id を利用して後で確認可能にする
        $this->insert_id = $wpdb->insert_id;
    }

    public function tearDown(): void {
        parent::tearDown();
        global $wpdb;
        if (!empty($this->session_table)) {
            $wpdb->query("TRUNCATE TABLE {$this->session_table}");
        }
    }

    /**
     * 正常系: セッションが正常に削除されることを確認 (204 No Content)
     */
    public function test_delete_session_success() {
        global $wpdb;
        $interactionId = self::$interaction_id;
        $sessionId = $this->insert_id;

        $request = new WP_REST_Request('DELETE', sprintf('/%s/interactions/%d/sessions/%d', LineConnect::PLUGIN_NAME, $interactionId, $sessionId));

        $response = rest_do_request($request);
        $this->assertEquals(204, $response->get_status(), 'Expected 204 No Content from delete endpoint');

        // DBからレコードが削除されていることを確認
        $deleted_session = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->session_table} WHERE id = %d", $sessionId));
        $this->assertNull($deleted_session, 'Session should be deleted from the database');
    }

    /**
     * 異常系: 存在しないセッションIDを指定した場合のエラー処理 (404 Not Found)
     */
    public function test_delete_session_not_found() {
        global $wpdb;
        $interactionId = self::$interaction_id;
        $nonExistentSessionId = 999999;

        // まず、存在しないIDで削除を試みる
        $request = new WP_REST_Request('DELETE', sprintf('/%s/interactions/%d/sessions/%d', LineConnect::PLUGIN_NAME, $interactionId, $nonExistentSessionId));
        $response = rest_do_request($request);
        $this->assertEquals(404, $response->get_status(), 'Expected 404 Not Found for non-existent session');

        $data = $response->get_data();
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Session not found', $data['message']);

        // 他のセッションが影響を受けていないことを確認
        $session_count_before = $wpdb->get_var("SELECT COUNT(*) FROM {$this->session_table}");
        $this->assertEquals(1, $session_count_before, 'Other sessions should remain unaffected');
    }

    /**
     * 異常系: interaction_id と session_id の組み合わせが存在しない場合 (404 Not Found)
     */
    public function test_delete_session_interaction_mismatch() {
        global $wpdb;
        // 別のinteraction_idを用意
        $other_interaction_id = self::$interaction_id + 1;
        $sessionId = $this->insert_id; // このsession_idは元のinteraction_idのものである

        $request = new WP_REST_Request('DELETE', sprintf('/%s/interactions/%d/sessions/%d', LineConnect::PLUGIN_NAME, $other_interaction_id, $sessionId));
        $response = rest_do_request($request);
        $this->assertEquals(404, $response->get_status(), 'Expected 404 Not Found for mismatched interaction_id and session_id');

        $data = $response->get_data();
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Session not found', $data['message']);
    }


    /**
     * 異常系: 権限がない（ログアウト）場合は 401 or 403
     */
    public function test_delete_session_permission_denied() {
        // ログアウト
        wp_set_current_user(0);

        $interactionId = self::$interaction_id;
        $sessionId = $this->insert_id;

        $request = new WP_REST_Request('DELETE', sprintf('/%s/interactions/%d/sessions/%d', LineConnect::PLUGIN_NAME, $interactionId, $sessionId));

        $response = rest_do_request($request);
        $this->assertContains($response->get_status(), [401, 403], 'Expected 401 or 403 for permission denied');
    }
}
