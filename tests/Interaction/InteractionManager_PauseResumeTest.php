<?php
// tests/Interaction/InteractionManager_PauseResumeTest.php

require_once __DIR__ . '/InteractionManager_Base.php';

class InteractionManager_PauseResumeTest extends InteractionManager_Base {
    // テスト一覧（実装は空）
    // - testHandleEventResumesPausedSession

    public function testHandleEventResumesPausedSession() {
        $interactionA_id = self::$interaction_ids['シンプルインタラクション'];
        $interactionB_id = self::$interaction_ids['別インタラクション'];
        $line_user_id = "U_PLACEHOLDER_USERID4e7a9902e5e7d";
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
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
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
