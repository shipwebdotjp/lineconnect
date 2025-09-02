<?php
// tests/Interaction/InteractionManager_ValidateTest.php

require_once __DIR__ . '/InteractionManager_Base.php';

class InteractionManager_ValidateTest extends InteractionManager_Base {
    /**
     * ケース1:
     * 数字のみ許可されているところに英字を入れてエラーメッセージが表示されることを確認
     */
    public function testNumberValidationRejectsNonNumeric() {
        $interaction_id = self::$interaction_ids['interaction_with_validate'];
        $line_user_id = "U_PLACEHOLDER_USERID4e7a9902e5e7d";
        $secret_prefix = "04f7";

        // Instantiate services (same pattern as other tests)
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

        // Start the interaction
        $start_messages = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $this->assertNotEmpty($start_messages);

        // Simulate user sending a non-numeric input (should trigger validation error)
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = 'abc123'; // non-numeric input

        // Handle the event
        $error_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        // Assert that we received an error message
        $this->assertNotEmpty($error_messages, 'バリデーションエラーのメッセージが返されていません。');
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $error_messages[0]
        );

        // Expect exactly one message (the error text)
        $built = $error_messages[0]->buildMessage();
        $this->assertCount(1, $built, 'エラーメッセージは1件であるべきです。');

        // Validator::validate の失敗メッセージは "Must be a number."
        $this->assertStringContainsString('Must be a number.', $built[0]["text"]);

        // Ensure session still exists and the step hasn't advanced, and answer not saved
        $saved_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($saved_session, 'アクティブなセッションがDBに保存されていません。');
        $this->assertEquals('step-1', $saved_session->get_current_step_id(), 'バリデーション失敗後にステップが進んでいます。');
        $this->assertNull($saved_session->get_answer('step-1'), 'バリデーション失敗時に回答が保存されています。');
    }
}
