<?php

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;
use Shipweb\LineConnect\Interaction\SessionRepository;

class StartInteractionTest extends WP_UnitTestCase {
    protected static $init_result;
    protected static $interaction_data;
    protected static $interaction_id;

    public static function wpSetUpBeforeClass($factory) {
        self::$init_result = lineconnectTest::init();

        self::$interaction_data = [
            "シンプルインタラクション" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => "interactions",
                        "steps" => [
                            [
                                "id" => "step-1",
                                "title" => "最初のステップ",
                                "description" => "これはテスト用の最初のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "テスト: 最初のメッセージです。",
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "テスト: 2番目のメッセージです。",
                                    ],
                                ],
                                "nextStepId" => "step-2",
                            ],
                            [
                                "id" => "step-2",
                                "title" => "2番目のステップ",
                                "description" => "これはテスト用の2番目のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "テスト: 2番目ステップの最初のメッセージです。",
                                    ],
                                ],
                                "stop" => true,
                            ],
                            [
                                "id" => "step-complete",
                                "title" => "完了",
                                "description" => "完了ステップ",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "テスト: 完了メッセージです。",
                                    ],
                                ],
                                "special" => "complete",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $post_id = wp_insert_post(array(
            'post_title'   => 'シンプルインタラクション',
            'post_type'    => InteractionCPT::POST_TYPE,
            'post_status'  => 'publish',
        ));

        update_post_meta($post_id, InteractionCPT::META_KEY_VERSION, 1);
        update_post_meta($post_id, InteractionCPT::META_KEY_DATA, self::$interaction_data["シンプルインタラクション"]);
        update_post_meta($post_id, LineConnect::META_KEY__SCHEMA_VERSION, InteractionCPT::SCHEMA_VERSION);

        self::$interaction_id = $post_id;
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_start_interaction_creates_session_and_returns_messages() {
        $interaction_id = self::$interaction_id;
        $line_user_id = 'Ud2be13c6f39c97f05c683d92c696483b';
        $secret_prefix = '04f7';

        // Prepare the StartInteraction action
        $func = new \Shipweb\LineConnect\Action\Definitions\StartInteraction();
        $func->set_secret_prefix($secret_prefix);
        $func->set_event((object) array('source' => (object) array('userId' => $line_user_id)));


        // Execute start_interaction
        $result = $func->start_interaction($interaction_id);

        // Assert messages returned
        $this->assertNotEmpty($result, 'start_interaction の返り値が空です。');
        // $this->assertIsArray($result);
        // Expect at least the built step message (MessageBuilder returns MultiMessageBuilder inside array)
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $result
        );
        $this->assertStringContainsString("テスト: 最初のメッセージです。", $result->buildMessage()[0]["text"]);
        $this->assertStringContainsString("テスト: 2番目のメッセージです。", $result->buildMessage()[1]["text"]);

        // Verify session saved in DB
        $session_repository = new SessionRepository();
        $saved_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($saved_session, 'アクティブなセッションがDBに保存されていません。');
        $this->assertEquals($interaction_id, $saved_session->get_interaction_id(), 'セッションのインタラクションIDが一致しません。');
        $this->assertEquals('step-1', $saved_session->get_current_step_id(), 'セッションの現在のステップIDが正しくありません。');
        $this->assertEquals('active', $saved_session->get_status(), 'セッションのステータスがactiveではありません。');
    }
}
