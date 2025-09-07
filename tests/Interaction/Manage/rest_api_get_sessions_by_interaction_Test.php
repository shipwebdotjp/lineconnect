<?php

// namespace Shipweb\LineConnect\Tests\Interaction\Manage;

// use WP_UnitTestCase;
// use WP_REST_Request;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;

/**
 * Class RESTAPITest
 * @package Shipweb\LineConnect\Tests\Interaction\Manage
 * @group interaction-manage
 */



class rest_api_get_sessions_by_interaction_Test extends WP_UnitTestCase {

    protected $admin_user_id;
    protected $interaction_post_id;
    protected $session_table;

    protected static $init_result;
    protected static $interaction_datas;
    protected static $interaction_ids;

    // wpSetUpBeforeClass をここにまとめる
    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory): void {
        // ここにデータ準備（tests/initdb.phpで行っている処理など）を移行してください。
        self::$init_result = lineconnectTest::init();
        self::$interaction_datas = [
            "シンプルインタラクション" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "steps" => [
                            [
                                "id" => "step-1",
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
                                'nextStepId' => 'step-2',
                            ],
                            [
                                "id" => "step-2",
                                "title" => "2番目のステップ",
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
    }

    public function setUp(): void {
        parent::setUp();
        global $wpdb;
        // bootstrap.phpでテーブルが作られている前提
        $this->session_table = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;

        // 管理者ユーザーを作成してログイン
        $this->admin_user_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($this->admin_user_id);

        // テスト用のインタラクション投稿を作成
        // $this->interaction_post_id = $this->factory->post->create([
        //     'post_type' => 'slc_interaction',
        //     'post_title' => 'Test Interaction for API',
        // ]);

        // テスト用のセッションデータをDBに挿入
        $wpdb->insert($this->session_table, [
            'interaction_id' => self::$interaction_ids['シンプルインタラクション'],
            'interaction_version' => 1,
            'channel_prefix' => 'test',
            'line_user_id' => 'user001',
            'status' => 'active',
            'answers' => json_encode(['q1' => 'a1']),
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 11:00:00',
        ]);
        $wpdb->insert($this->session_table, [
            'interaction_id' => self::$interaction_ids['シンプルインタラクション'],
            'interaction_version' => 1,
            'channel_prefix' => 'test',
            'line_user_id' => 'user002',
            'status' => 'completed',
            'answers' => json_encode(['q1' => 'a2']),
            'created_at' => '2024-01-01 12:00:00',
            'updated_at' => '2024-01-01 13:00:00',
        ]);
    }

    public function tearDown(): void {
        parent::tearDown();
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->session_table}");
    }

    /**
     * 正常系のテスト: セッションリストが正しく取得できるか
     */
    public function test_get_sessions_by_interaction() {
        $request = new WP_REST_Request('GET', sprintf('/%s/interactions/%d/sessions', LineConnect::PLUGIN_NAME, self::$interaction_ids['シンプルインタラクション']));
        $response = rest_do_request($request);

        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();

        // APIは top-level に 'data' と 'meta' を返す
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);

        $this->assertCount(2, $data['data']);
        // updated_at DESCでソートされる
        $this->assertEquals('user002', $data['data'][0]['line_user_id']);
        $this->assertEquals('user001', $data['data'][1]['line_user_id']);

        // メタ情報の検証（ヘッダーではなく meta.pagination を使用）
        $this->assertEquals(2, $data['meta']['pagination']['total']);
        $this->assertEquals(1, $data['meta']['pagination']['pages']);
    }

    /**
     * 正常系のテスト: statusによるフィルタリング
     */
    public function test_get_sessions_by_interaction_with_status_filter() {
        $request = new WP_REST_Request('GET', sprintf('/%s/interactions/%d/sessions', LineConnect::PLUGIN_NAME, self::$interaction_ids['シンプルインタラクション']));
        $request->set_query_params(['status' => 'completed']);
        $response = rest_do_request($request);

        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();

        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);
        $this->assertEquals('completed', $data['data'][0]['status']);
        $this->assertEquals('user002', $data['data'][0]['line_user_id']);
    }

    /**
     * 異常系のテスト: 権限がない場合
     */
    public function test_get_sessions_permission_denied() {
        wp_set_current_user(0); // ログアウト

        $request = new WP_REST_Request('GET', sprintf('/%s/interactions/%d/sessions', LineConnect::PLUGIN_NAME, self::$interaction_ids['シンプルインタラクション']));
        $response = rest_do_request($request);

        $this->assertContains($response->get_status(), [401, 403]);
    }
}
