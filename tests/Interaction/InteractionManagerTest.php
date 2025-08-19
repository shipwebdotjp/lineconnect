<?php

use Shipweb\LineConnect\Interaction\InteractionManager;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;
use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\InteractionHandler;


class InteractionManagerTest extends WP_UnitTestCase {
    protected static $init_result;
    protected static $interaction_datas;
    protected static $interaction_ids;

    public static function wpSetUpBeforeClass($factory) {
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
            // Additional interaction used for testing different-form behaviors
            "別インタラクション" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "steps" => [
                            [
                                "id" => "other-step-1",
                                "title" => "別の最初のステップ",
                                "description" => "別インタラクションの最初のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "別インタラクション: 最初のメッセージ",
                                    ],
                                ],
                                'nextStepId' => 'other-step-2',
                            ],
                            [
                                "id" => "other-step-2",
                                "title" => "別の2番目のステップ",
                                "description" => "別インタラクションの2番目のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "別インタラクション: 2番目のメッセージ",
                                    ],
                                ],
                                'stop' => true,
                            ],
                            [
                                "id" => "completed-message",
                                "title" => "完了メッセージ",
                                "description" => "これはシンプルなインタラクションの完了メッセージです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "別インタラクション: 完了メッセージ",
                                    ]
                                ],
                                'special' => 'complete',
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
    }

    public function testStartInteraction() {
        $interaction_id = self::$interaction_ids['シンプルインタラクション'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";
        $event = new \stdClass();
        $event->{'source'} = new \stdClass();
        $event->{'source'}->{'userId'} = $line_user_id;

        // start interaction
        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );
        $interaction_messages = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $this->assertNotEmpty($interaction_messages);
        // contains the expected messages
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $interaction_messages[0]
        );
        $this->assertCount(2, $interaction_messages[0]->buildMessage());
        $this->assertStringContainsString("こんにちは！これはシンプルなインタラクションの最初のステップの最初のメッセージです。", $interaction_messages[0]->buildMessage()[0]["text"]);
        $this->assertStringContainsString("こんにちは！これはシンプルなインタラクションの最初のステップの2番目のメッセージです。", $interaction_messages[0]->buildMessage()[1]["text"]);

        // Verify database state
        $saved_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($saved_session, 'アクティブなセッションがDBに保存されていません。');
        $this->assertEquals($interaction_id, $saved_session->get_interaction_id(), 'セッションのインタラクションIDが一致しません。');
        $this->assertEquals('step-1', $saved_session->get_current_step_id(), 'セッションの現在のステップIDが正しくありません。');
        $this->assertEquals('active', $saved_session->get_status(), 'セッションのステータスがactiveではありません。');
    }

    public function testInteractionLifecycle() {
        $interaction_id = self::$interaction_ids['シンプルインタラクション'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        // Instantiate services
        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        // Stage 1: Start the interaction
        $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);

        // Stage 2: Simulate user reply
        $user_input = 'Any input from user';
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = $user_input;

        // Stage 3: Handle the user's reply
        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        // Stage 4: Assert the response
        $this->assertNotEmpty($next_step_messages);
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $next_step_messages[0]
        );
        $this->assertCount(2, $next_step_messages[0]->buildMessage());
        $this->assertStringContainsString("こんにちは！これはシンプルなインタラクションの2番目のステップの最初のメッセージです。", $next_step_messages[0]->buildMessage()[0]["text"]);
        $this->assertStringContainsString("こんにちは！これはシンプルなインタラクションの2番目のステップの2番目のメッセージです。", $next_step_messages[0]->buildMessage()[1]["text"]);

        // Also, verify the session state in the database
        $updated_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($updated_session, 'アクティブなセッションが見つかりません。');
        $this->assertEquals('step-2', $updated_session->get_current_step_id(), 'セッションが次のステップに進んでいません。');
        $this->assertEquals($user_input, $updated_session->get_answer('step-1'), 'ユーザーの入力が正しく保存されていません。');

        // goto step-3
        $complete_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        // Assert the response for step-3
        $this->assertNotEmpty($complete_step_messages);
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $complete_step_messages[0]
        );
        $this->assertCount(2, $complete_step_messages[0]->buildMessage());
        $this->assertStringContainsString("こんにちは！これはシンプルなインタラクションの完了ステップの最初のメッセージです。", $complete_step_messages[0]->buildMessage()[0]["text"]);
        $this->assertStringContainsString("こんにちは！これはシンプルなインタラクションの完了ステップの2番目のメッセージです。", $complete_step_messages[0]->buildMessage()[1]["text"]);
        // Also, verify the session state in the database
        $completed_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNull($completed_session, 'アクティブなセッションが見つかりました。');
    }

    public function testInteractionLifecycleB() {
        $interaction_id = self::$interaction_ids['別インタラクション'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        // Instantiate services
        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        // Stage 1: Start the interaction
        $next_step_messages = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        // var_dump($next_step_messages);
        // Stage 4: Assert the response
        $this->assertNotEmpty($next_step_messages);
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $next_step_messages[0]
        );
        $this->assertCount(1, $next_step_messages[0]->buildMessage());
        $this->assertStringContainsString("別インタラクション: 最初のメッセージ", $next_step_messages[0]->buildMessage()[0]["text"]);

        // Also, verify the session state in the database
        $updated_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($updated_session, 'アクティブなセッションが見つかりません。');
        $this->assertEquals('other-step-1', $updated_session->get_current_step_id(), 'セッションが次のステップに進んでいません。');


        // Stage 2: Simulate user reply
        $user_input = 'Any input from user';
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = $user_input;

        // Stage 3: Handle the user's reply
        $final_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        // Assert the response 
        $this->assertNotEmpty($final_step_messages);
        $this->assertCount(1, $final_step_messages[0]->buildMessage());
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $final_step_messages[0]
        );
        $this->assertStringContainsString("別インタラクション: 2番目のメッセージ", $final_step_messages[0]->buildMessage()[0]["text"]);
        // Also, verify the session state in the database
        $final_session = $session_repository->find_active($secret_prefix, $line_user_id);
        // var_dump($final_session);
        $this->assertNotNull($final_session, 'アクティブなセッションが見つかりません。');

        // Put final session assertions here
        $this->assertEquals($interaction_id, $final_session->get_interaction_id());
        $this->assertEquals('active', $final_session->get_status());
        $this->assertEquals('other-step-2', $final_session->get_current_step_id());

        $completed_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        // Assert the response
        $this->assertNotEmpty($completed_messages);
        $this->assertCount(1, $completed_messages[0]->buildMessage());
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $completed_messages[0]
        );
        $this->assertStringContainsString("別インタラクション: 完了メッセージ", $completed_messages[0]->buildMessage()[0]["text"]);
    }

    // ---- New tests for override policies ----

    public function testStartInteractionReject() {
        $interaction_id = self::$interaction_ids['シンプルインタラクション'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        // Start initial session A
        $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);

        // Attempt to start same interaction with 'reject' policy
        $result = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix, 'reject');

        // Expect rejection (no messages)
        $this->assertEmpty($result);

        // Existing session should remain active and unchanged
        $saved_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($saved_session);
        $this->assertEquals($interaction_id, $saved_session->get_interaction_id());
        $this->assertEquals('active', $saved_session->get_status());
        $this->assertEquals('step-1', $saved_session->get_current_step_id());
    }

    public function testStartInteractionRestartSame() {
        $interaction_id = self::$interaction_ids['シンプルインタラクション'];
        $interactionB = self::$interaction_ids['別インタラクション'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        // Start initial session and simulate progress
        $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($session);
        // Simulate answering first step and moving to step-2
        $session->set_answer('step-1', 'user answer');
        $session->set_current_step_id('step-2');
        $session_repository->save($session);

        // Now call restart_same
        $result = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix, 'restart_same');

        // Expect presentStep result
        $this->assertNotEmpty($result);
        // Verify session reset
        $reset_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($reset_session);
        $this->assertEquals('step-1', $reset_session->get_current_step_id());
        $this->assertEmpty($reset_session->get_answers());

        $result_b = $interaction_manager->startInteraction($interactionB, $line_user_id, $secret_prefix, 'restart_same');
        $this->assertEmpty($result_b, 'Restart same on different interaction should not create a new session.');
    }

    public function testStartInteractionRestartDiff() {
        $interactionA = self::$interaction_ids['シンプルインタラクション'];
        $interactionB = self::$interaction_ids['別インタラクション'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        // Start interaction B first (active)
        $interaction_manager->startInteraction($interactionB, $line_user_id, $secret_prefix);
        $active_before = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($active_before);
        $this->assertEquals($interactionB, $active_before->get_interaction_id());

        // call same with diff should not create new session
        $result_b = $interaction_manager->startInteraction($interactionB, $line_user_id, $secret_prefix, 'restart_diff');
        $this->assertEmpty($result_b, 'Restart same on different interaction should not create a new session do nothing..');

        // Now start A with restart_diff -> should delete B and create A
        $result = $interaction_manager->startInteraction($interactionA, $line_user_id, $secret_prefix, 'restart_diff');
        $this->assertNotEmpty($result);

        $active_after = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($active_after);
        $this->assertEquals($interactionA, $active_after->get_interaction_id());

        //最初のBセッションが削除されていることを確認
        $old_active = $session_repository->find($active_before->get_id());
        $this->assertNull($old_active);
    }

    public function testStartInteractionRestartAlways() {
        $interactionA = self::$interaction_ids['シンプルインタラクション'];
        $interactionB = self::$interaction_ids['別インタラクション'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        // Case 1: same-form reset
        $interaction_manager->startInteraction($interactionA, $line_user_id, $secret_prefix);
        $session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($session);
        // simulate progress
        $session->set_answer('step-1', 'user answer');
        $session->set_current_step_id('step-2');
        $session_repository->save($session);

        // restart_always on same form should reset
        $result_same = $interaction_manager->startInteraction($interactionA, $line_user_id, $secret_prefix, 'restart_always');
        $this->assertNotEmpty($result_same);
        $reset_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($reset_session);
        $this->assertEquals('step-1', $reset_session->get_current_step_id());
        $this->assertEmpty($reset_session->get_answers());

        // Case 2: different-form - start B then restart_always A should remove B and create A
        $interaction_manager->startInteraction($interactionB, $line_user_id, $secret_prefix);
        $active_before = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($active_before);
        $this->assertEquals($interactionB, $active_before->get_interaction_id());

        $result_diff = $interaction_manager->startInteraction($interactionA, $line_user_id, $secret_prefix, 'restart_always');
        $this->assertNotEmpty($result_diff);
        $active_after = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($active_after);
        $this->assertEquals($interactionA, $active_after->get_interaction_id());
    }

    public function testStartInteractionStack() {
        $interactionA = self::$interaction_ids['シンプルインタラクション'];
        $interactionB = self::$interaction_ids['別インタラクション'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        // Start B as active
        $interaction_manager->startInteraction($interactionB, $line_user_id, $secret_prefix);
        $active_before = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($active_before);
        $this->assertEquals($interactionB, $active_before->get_interaction_id());

        // again B
        $result = $interaction_manager->startInteraction($interactionB, $line_user_id, $secret_prefix, 'stack');
        $this->assertEmpty($result, 'Stacking same interaction should not create a new session.');

        // Start A with 'stack' policy -> should pause B and create A
        $result = $interaction_manager->startInteraction($interactionA, $line_user_id, $secret_prefix, 'stack');
        $this->assertNotEmpty($result);

        // B should be paused
        $paused = $session_repository->find_paused_by_interaction($secret_prefix, $line_user_id, $interactionB);
        $this->assertNotNull($paused);
        $this->assertEquals('paused', $paused->get_status());

        // Active should now be A
        $active_after = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($active_after);
        $this->assertEquals($interactionA, $active_after->get_interaction_id());

        // again start B with restart_same
        $result = $interaction_manager->startInteraction($interactionB, $line_user_id, $secret_prefix, 'restart_same');
        $this->assertNotEmpty($result);

        // B should be active again
        $active_after_restart = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($active_after_restart);
        $this->assertEquals($interactionB, $active_after_restart->get_interaction_id());

        // A should be paused
        $paused_after_restart = $session_repository->find_paused_by_interaction($secret_prefix, $line_user_id, $interactionA);
        $this->assertNotNull($paused_after_restart);
        $this->assertEquals('paused', $paused_after_restart->get_status());

        // again start A with stack
        $result = $interaction_manager->startInteraction($interactionA, $line_user_id, $secret_prefix, 'stack');
        $this->assertNotEmpty($result);

        // B should be paused
        $paused = $session_repository->find_paused_by_interaction($secret_prefix, $line_user_id, $interactionB);
        $this->assertNotNull($paused);
        $this->assertEquals('paused', $paused->get_status());

        // Active should now be A
        $active_after = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($active_after);
        $this->assertEquals($interactionA, $active_after->get_interaction_id());

        // again start B with restart_always
        $result = $interaction_manager->startInteraction($interactionB, $line_user_id, $secret_prefix, 'restart_always');
        $this->assertNotEmpty($result);

        // B should be active again
        $active_after_restart = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($active_after_restart);
        $this->assertEquals($interactionB, $active_after_restart->get_interaction_id());

        // A should be none
        $deleted_after_restart = $session_repository->find($interactionA);
        $this->assertEmpty($deleted_after_restart);
    }

    public function testHandleEventResumesPausedSession() {
        $interactionA_id = self::$interaction_ids['シンプルインタラクション'];
        $interactionB_id = self::$interaction_ids['別インタラクション'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        // Instantiate services
        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        // Stage 1: Start Interaction A and progress it to step-2
        $interaction_manager->startInteraction($interactionA_id, $line_user_id, $secret_prefix);
        $sessionA = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($sessionA);
        $this->assertEquals('step-1', $sessionA->get_current_step_id());

        // Simulate user input for step-1 of A to move to step-2
        $reply_event_A = new \stdClass();
        $reply_event_A->type = 'message';
        $reply_event_A->replyToken = 'tokenA';
        $reply_event_A->source = (object)['type' => 'user', 'userId' => $line_user_id];
        $reply_event_A->message = (object)['type' => 'text', 'text' => 'input for A step-1'];
        $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event_A);

        $sessionA_step2 = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($sessionA_step2);
        $this->assertEquals('step-2', $sessionA_step2->get_current_step_id());

        // Stage 2: Start Interaction B with 'stack' policy, pausing A
        $interaction_manager->startInteraction($interactionB_id, $line_user_id, $secret_prefix, 'stack');
        $sessionB = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($sessionB);
        $this->assertEquals($interactionB_id, $sessionB->get_interaction_id());
        $this->assertEquals('other-step-1', $sessionB->get_current_step_id());

        $pausedSessionA = $session_repository->find_paused_by_interaction($secret_prefix, $line_user_id, $interactionA_id);
        $this->assertNotNull($pausedSessionA);
        $this->assertEquals('paused', $pausedSessionA->get_status());
        $this->assertEquals('step-2', $pausedSessionA->get_current_step_id()); // A should be paused at step-2

        // Stage 3: Simulate user input for step-1 of B to move to step-2
        $reply_event_B_step1 = new \stdClass();
        $reply_event_B_step1->type = 'message';
        $reply_event_B_step1->replyToken = 'tokenB1';
        $reply_event_B_step1->source = (object)['type' => 'user', 'userId' => $line_user_id];
        $reply_event_B_step1->message = (object)['type' => 'text', 'text' => 'input for B step-1'];
        $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event_B_step1);

        $sessionB_step2 = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($sessionB_step2);
        $this->assertEquals('other-step-2', $sessionB_step2->get_current_step_id());

        // Stage 4: Simulate user input for step-2 of B to complete B, which should resume A
        $reply_event_B_step2 = new \stdClass();
        $reply_event_B_step2->type = 'message';
        $reply_event_B_step2->replyToken = 'tokenB2';
        $reply_event_B_step2->source = (object)['type' => 'user', 'userId' => $line_user_id];
        $reply_event_B_step2->message = (object)['type' => 'text', 'text' => 'input for B step-2'];
        $final_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event_B_step2);
        // var_dump($final_messages); // For debugging, can be removed in production

        // Assertions
        // B should be completed (deleted from active)
        $completedSessionB = $session_repository->find($sessionB_step2->get_id());
        $this->assertEquals('completed', $completedSessionB->get_status());

        // A should now be active and at step-2
        $resumedSessionA = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($resumedSessionA, 'Interaction A should have been resumed and be active.');
        $this->assertEquals($interactionA_id, $resumedSessionA->get_interaction_id());
        $this->assertEquals('step-2', $resumedSessionA->get_current_step_id());
        $this->assertEquals('active', $resumedSessionA->get_status());

        // Verify messages: completion message from B + messages from A's step-2
        $this->assertNotEmpty($final_messages);
        // Expecting 2 messages from B's completion step + 2 messages from A's step-2
        // The simple interaction has 2 messages per step.
        // The '別インタラクション' has 1 message per step.
        // B's completion step is 'other-step-2' which has 1 message.
        // A's step-2 has 2 messages.
        // So, total messages should be 1 (B completion) + 2 (A step-2) = 3 messages.
        $this->assertCount(3, $final_messages[0]->buildMessage());

        // Check content of messages (simplified check)
        $this->assertStringContainsString('別インタラクション: 完了メッセージ', $final_messages[0]->buildMessage()[0]['text']); // B's completion message
        $this->assertStringContainsString('こんにちは！これはシンプルなインタラクションの2番目のステップの最初のメッセージです。', $final_messages[0]->buildMessage()[1]['text']); // A's step-2 message 1
        $this->assertStringContainsString('こんにちは！これはシンプルなインタラクションの2番目のステップの2番目のメッセージです。', $final_messages[0]->buildMessage()[2]['text']); // A's step-2 message 2
    }
}
