<?php

namespace Shipweb\LineConnect\Tests\Interaction\Manage;

use WP_UnitTestCase;
use WP_REST_Request;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;

/**
 * Class rest_api_get_interaction_Test
 * @package Shipweb\LineConnect\Tests\Interaction\Manage
 * @group interaction-manage
 */
class rest_api_get_interaction_Test extends WP_UnitTestCase {

    protected $admin_user_id;
    protected $interaction_post_id;
    protected $session_table;

    public function setUp(): void {
        parent::setUp();
        global $wpdb;
        // bootstrap.phpでテーブルが作られている前提
        $this->session_table = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;

        // 管理者ユーザーを作成してログイン
        $this->admin_user_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($this->admin_user_id);

        // テスト用のインタラクション投稿を作成
        $this->interaction_post_id = $this->factory->post->create([
            'post_type' => InteractionCPT::POST_TYPE,
            'post_title' => 'Test Interaction for API',
        ]);

        // インタラクションのメタデータを設定
        $interaction_data = [
            1 => [
                0 => [
                    'steps' => [
                        [
                            'id' => 'step1',
                            'title' => 'Test Step',
                            'message' => [['type' => 'text', 'text' => 'Test message']],
                        ]
                    ],
                    'timeoutMinutes' => 60,
                    'timeoutRemind' => 30,
                    'onTimeout' => 'mark_timeout',
                    'runPolicy' => 'single_latest_only',
                    'overridePolicy' => 'stack',
                    'storage' => 'none',
                    'excludeSteps' => [],
                    'cancelWords' => [],
                ]
            ]
        ];
        update_post_meta($this->interaction_post_id, InteractionCPT::META_KEY_DATA, $interaction_data);
        update_post_meta($this->interaction_post_id, InteractionCPT::META_KEY_VERSION, 1);

        // テスト用のセッションデータをDBに挿入
        $wpdb->insert($this->session_table, [
            'interaction_id' => $this->interaction_post_id,
            'interaction_version' => 1,
            'channel_prefix' => 'test',
            'line_user_id' => 'user001',
            'status' => 'active',
            'answers' => json_encode(['step1' => 'answer1']),
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 11:00:00',
        ]);
        $wpdb->insert($this->session_table, [
            'interaction_id' => $this->interaction_post_id,
            'interaction_version' => 1,
            'channel_prefix' => 'test',
            'line_user_id' => 'user002',
            'status' => 'completed',
            'answers' => json_encode(['step1' => 'answer2']),
            'created_at' => '2024-01-01 12:00:00',
            'updated_at' => '2024-01-01 13:00:00',
        ]);
        $wpdb->insert($this->session_table, [
            'interaction_id' => $this->interaction_post_id,
            'interaction_version' => 1,
            'channel_prefix' => 'test',
            'line_user_id' => 'user003',
            'status' => 'paused',
            'answers' => json_encode(['step1' => 'answer3']),
            'created_at' => '2024-01-01 14:00:00',
            'updated_at' => '2024-01-01 15:00:00',
        ]);
    }

    public function tearDown(): void {
        parent::tearDown();
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->session_table}");
    }

    /**
     * 正常系のテスト: インタラクションの基本情報と統計が正しく取得できるか
     */
    public function test_get_interaction() {
        $request = new WP_REST_Request('GET', sprintf('/%s/interactions/%d', LineConnect::PLUGIN_NAME, $this->interaction_post_id));
        $response = rest_do_request($request);

        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();

        // 基本情報の検証
        $this->assertEquals($this->interaction_post_id, $data['id']);
        $this->assertEquals('Test Interaction for API', $data['title']);
        $this->assertEquals(1, $data['version']);

        // 統計情報の検証
        $this->assertArrayHasKey('statistics', $data);
        $stats = $data['statistics'];

        // 集計統計の検証
        $this->assertArrayHasKey('total', $stats);
        $total = $stats['total'];
        $this->assertEquals(1, $total['active']);
        $this->assertEquals(1, $total['paused']);
        $this->assertEquals(1, $total['completed']);
        $this->assertEquals(0, $total['timeout']);
        $this->assertEquals(3, $total['total_sessions']);
        $this->assertEquals(33.3, $total['completion_rate']);
        $this->assertEquals(3, $total['unique_users']);

        // バージョン別統計の検証
        $this->assertArrayHasKey('by_version', $stats);
        $this->assertArrayHasKey(1, $stats['by_version']);
        $version_stats = $stats['by_version'][1];
        $this->assertEquals(1, $version_stats['active']);
        $this->assertEquals(1, $version_stats['paused']);
        $this->assertEquals(1, $version_stats['completed']);
        $this->assertEquals(0, $version_stats['timeout']);
        $this->assertEquals(3, $version_stats['total_sessions']);
        $this->assertEquals(33.3, $version_stats['completion_rate']);
        $this->assertEquals(3, $version_stats['unique_users']);
    }

    /**
     * 正常系のテスト: セッションがない場合の統計
     */
    public function test_get_interaction_no_sessions() {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->session_table}");

        $request = new WP_REST_Request('GET', sprintf('/%s/interactions/%d', LineConnect::PLUGIN_NAME, $this->interaction_post_id));
        $response = rest_do_request($request);

        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();

        $total = $data['statistics']['total'];
        $this->assertEquals(0, $total['active']);
        $this->assertEquals(0, $total['paused']);
        $this->assertEquals(0, $total['completed']);
        $this->assertEquals(0, $total['timeout']);
        $this->assertEquals(0, $total['total_sessions']);
        $this->assertEquals(0.0, $total['completion_rate']);
        $this->assertEquals(0, $total['unique_users']);
    }

    /**
     * 異常系のテスト: 存在しないインタラクション
     */
    public function test_get_interaction_not_found() {
        $request = new WP_REST_Request('GET', sprintf('/%s/interactions/%d', LineConnect::PLUGIN_NAME, 99999));
        $response = rest_do_request($request);

        $this->assertEquals(404, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals('interaction_not_found', $data['code']);
    }

    /**
     * 異常系のテスト: 権限がない場合
     */
    public function test_get_interaction_permission_denied() {
        wp_set_current_user(0); // ログアウト

        $request = new WP_REST_Request('GET', sprintf('/%s/interactions/%d', LineConnect::PLUGIN_NAME, $this->interaction_post_id));
        $response = rest_do_request($request);

        $this->assertContains($response->get_status(), [401, 403]);
    }
}
