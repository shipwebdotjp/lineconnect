<?php

// use WP_UnitTestCase;
// use WP_REST_Request;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;

/**
 * Class RESTAPIUpdateTest
 * @package Shipweb\LineConnect\Tests\Interaction\Manage
 * @group interaction-manage
 */
class rest_api_update_session_Test extends WP_UnitTestCase {

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
     * 正常系: answers と status を更新できること、DBに反映されること
     */
    public function test_update_session_answers_and_status_success() {
        global $wpdb;
        $interactionId = self::$interaction_id;
        $sessionId = $this->insert_id;

        $request = new WP_REST_Request('PATCH', sprintf('/%s/interactions/%d/sessions/%d', LineConnect::PLUGIN_NAME, $interactionId, $sessionId));
        $payload = [
            'answers' => [
                'q1' => 'updated answer',
                'q2' => '<b>should be stripped</b>'
            ],
            'status' => 'paused'
        ];
        $request->set_body_params($payload);

        $response = rest_do_request($request);
        // var_dump($response);
        $this->assertEquals(200, $response->get_status(), 'Expected 200 OK from update endpoint');

        $data = $response->get_data();
        $this->assertArrayHasKey('data', $data);
        $updated = $data['data'];

        // レスポンスの確認
        $this->assertEquals($sessionId, intval($updated['id']));
        $this->assertEquals('paused', $updated['status']);
        $this->assertArrayHasKey('answers', $updated);
        // レスポンスの answers はステップごとの配列（title, answer）が入るので q1 が存在することを確認
        $this->assertArrayHasKey('q1', $updated['answers']);
        $this->assertEquals('updated answer', $updated['answers']['q1']['answer']);

        // DB の生の answers カラムを確認（HTML タグが除去されていること）
        $stored = $wpdb->get_var($wpdb->prepare("SELECT answers FROM {$this->session_table} WHERE id = %d", $sessionId));
        $this->assertNotEmpty($stored);
        $decoded = json_decode($stored, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('updated answer', $decoded['q1']);
        // HTML タグは sanitize_textarea_field により除去されるため '<b>...' -> 'should be stripped'
        $this->assertEquals('should be stripped', $decoded['q2']);
    }

    /**
     * 異常系: 無効な status を渡すと 400 を返す
     */
    public function test_update_session_invalid_status() {
        $interactionId = self::$interaction_id;
        $sessionId = $this->insert_id;

        $request = new WP_REST_Request('PATCH', sprintf('/%s/interactions/%d/sessions/%d', LineConnect::PLUGIN_NAME, $interactionId, $sessionId));
        $request->set_body_params([
            'status' => 'invalid_status_value'
        ]);

        $response = rest_do_request($request);
        $this->assertEquals(400, $response->get_status());
    }

    /**
     * 異常系: 権限がない（ログアウト）場合は 401 or 403
     */
    public function test_update_session_permission_denied() {
        // ログアウト
        wp_set_current_user(0);

        $interactionId = self::$interaction_id;
        $sessionId = $this->insert_id;

        $request = new WP_REST_Request('PATCH', sprintf('/%s/interactions/%d/sessions/%d', LineConnect::PLUGIN_NAME, $interactionId, $sessionId));
        $request->set_body_params([
            'status' => 'paused'
        ]);

        $response = rest_do_request($request);
        $this->assertContains($response->get_status(), [401, 403]);
    }
}
