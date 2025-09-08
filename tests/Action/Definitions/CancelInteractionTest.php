<?php

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;
use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\InteractionSession;

class CancelInteractionTest extends WP_UnitTestCase {
    protected static $interaction_id;

    public static function wpSetUpBeforeClass($factory) {
        lineconnectTest::init();

        $interaction_data = [
            "キャンセルテスト用インタラクション" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => "interactions",
                        "steps" => [
                            [
                                "id" => "step-1",
                                "title" => "最初のステップ",
                                "messages" => [["type" => "text", "text" => "最初のステップです。"]],
                                "nextStepId" => "step-2",
                            ],
                            [
                                "id" => "step-canceled",
                                "title" => "キャンセル完了",
                                "messages" => [["type" => "text", "text" => "インタラクションはキャンセルされました。"]],
                                "special" => "canceled",
                            ],
                            [
                                "id" => "step-cancel-confirm",
                                "title" => "キャンセル確認",
                                "messages" => [["type" => "text", "text" => "本当にキャンセルしますか？"]],
                                "special" => "cancelConfirm",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $post_id = wp_insert_post([
            'post_title'   => 'キャンセルテスト用インタラクション',
            'post_type'    => InteractionCPT::POST_TYPE,
            'post_status'  => 'publish',
        ]);

        update_post_meta($post_id, InteractionCPT::META_KEY_VERSION, 1);
        update_post_meta($post_id, InteractionCPT::META_KEY_DATA, $interaction_data["キャンセルテスト用インタラクション"]);
        update_post_meta($post_id, LineConnect::META_KEY__SCHEMA_VERSION, InteractionCPT::SCHEMA_VERSION);

        self::$interaction_id = $post_id;
    }

    public function setUp(): void {
        parent::setUp();
        // 各テストの前にセッションテーブルをクリア
        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;
        $wpdb->query("TRUNCATE TABLE {$table_name}");
    }

    private function create_session(string $line_user_id, string $secret_prefix, string $status = 'active', ?int $interaction_id = null): InteractionSession {
        global $wpdb;
        $session_table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;
        $interaction_id = $interaction_id ?? self::$interaction_id;
        $result =$wpdb->insert($session_table_name, [
            'interaction_id' => $interaction_id,
            'interaction_version' => 1,
            'channel_prefix' => $secret_prefix,
            'line_user_id' => $line_user_id,
            'status' => $status,
            'answers' => json_encode(['step1' => 'Answer 1']),
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 11:00:00',
        ]);
        // var_dump($result);
        // var_dump($wpdb->insert_id);
        // var_dump($wpdb->get_row($wpdb->prepare("SELECT * FROM {$session_table_name} WHERE id = %d", $wpdb->insert_id)));
        $session = InteractionSession::from_db_row($wpdb->get_row($wpdb->prepare("SELECT * FROM {$session_table_name} WHERE id = %d", $wpdb->insert_id)));
        $this->assertNotNull($session, 'テスト用のセッション作成に失敗しました。');
        return $session;
    }

    public function test_cancel_interaction_with_force_policy() {
        $line_user_id = 'U_USER_FORCE_CANCEL';
        $secret_prefix = '04f7';
        $this->create_session($line_user_id, $secret_prefix, 'active');

        $func = new \Shipweb\LineConnect\Action\Definitions\CancelInteraction();
        $func->set_secret_prefix($secret_prefix);
        $func->set_event((object) ['source' => (object) ['userId' => $line_user_id]]);

        // interaction_id, status を指定しない場合、activeなセッションが対象になる
        $result = $func->cancel_interaction(null, 'force');

        $this->assertNotEmpty($result, 'cancel_interactionの返り値が空です。');
        $this->assertInstanceOf(\LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class, $result);
        $this->assertStringContainsString("インタラクションはキャンセルされました。", $result->buildMessage()[0]["text"]);

        $session_repository = new SessionRepository();
        $this->assertNull($session_repository->find_active($secret_prefix, $line_user_id), 'セッションがDBから削除されていません。');
    }

    public function test_cancel_interaction_with_confirm_policy() {
        $line_user_id = 'U_USER_CONFIRM_CANCEL';
        $secret_prefix = '04f7';
        $session = $this->create_session($line_user_id, $secret_prefix, 'active');

        $func = new \Shipweb\LineConnect\Action\Definitions\CancelInteraction();
        $func->set_secret_prefix($secret_prefix);
        $func->set_event((object) ['source' => (object) ['userId' => $line_user_id]]);

        $result = $func->cancel_interaction(null, 'confirm');

        $this->assertNotEmpty($result, 'cancel_interactionの返り値が空です。');
        $this->assertInstanceOf(\LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class, $result);
        $this->assertStringContainsString("本当にキャンセルしますか？", $result->buildMessage()[0]["text"]);

        $session_repository = new SessionRepository();
        $found_session = $session_repository->find($session->get_id());
        $this->assertNotNull($found_session, 'セッションがDBから削除されています。');
        $this->assertEquals('active', $found_session->get_status(), 'セッションのステータスが変更されています。');
    }

    public function test_cancel_interaction_with_interaction_id() {
        $line_user_id = 'U_USER_ID_CANCEL';
        $secret_prefix = '04f7';
        $session = $this->create_session($line_user_id, $secret_prefix, 'active');

        $func = new \Shipweb\LineConnect\Action\Definitions\CancelInteraction();
        $func->set_secret_prefix($secret_prefix);
        $func->set_event((object) ['source' => (object) ['userId' => $line_user_id]]);

        $result = $func->cancel_interaction($session->get_interaction_id(), 'force');

        $this->assertNotEmpty($result);
        $session_repository = new SessionRepository();
        $this->assertNull($session_repository->find($session->get_id()), '指定したinteraction_idのセッションが削除されていません。');
    }

    public function test_cancel_interaction_with_status() {
        $line_user_id = 'U_USER_STATUS_CANCEL';
        $secret_prefix = '04f7';
        $this->create_session($line_user_id, $secret_prefix, 'paused');

        $func = new \Shipweb\LineConnect\Action\Definitions\CancelInteraction();
        $func->set_secret_prefix($secret_prefix);
        $func->set_event((object) ['source' => (object) ['userId' => $line_user_id]]);

        $result = $func->cancel_interaction(null, 'force', ['paused', 'timeout']);

        $this->assertNotEmpty($result);
        $session_repository = new SessionRepository();
        $this->assertEmpty($session_repository->find_paused($secret_prefix, $line_user_id), '指定したステータスのセッションが削除されていません。');
    }

    public function test_cancel_interaction_no_target_session() {
        $line_user_id = 'U_USER_NO_SESSION';
        $secret_prefix = '04f7';

        $func = new \Shipweb\LineConnect\Action\Definitions\CancelInteraction();
        $func->set_secret_prefix($secret_prefix);
        $func->set_event((object) ['source' => (object) ['userId' => $line_user_id]]);

        $result = $func->cancel_interaction();

        $this->assertNull($result, '対象セッションがない場合にnullが返されていません。');
    }
}
