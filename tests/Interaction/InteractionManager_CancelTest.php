<?php
// tests/Interaction/InteractionManager_CancelTest.php

require_once __DIR__ . '/InteractionManager_Base.php';

class InteractionManager_CancelTest extends InteractionManager_Base {

    public function testCancelAbort() {
        $interaction_id = self::$interaction_ids['interaction_with_cancel'];
        $line_user_id = "U_PLACEHOLDER_USERID4e7a9902e5e7d";
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
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        $interaction_messages = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $this->assertNotEmpty($interaction_messages);
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $interaction_messages[0]
        );
        $this->assertCount(1, $interaction_messages[0]->buildMessage());
        $this->assertStringContainsString("これはキャンセルテストのステップ1です。", $interaction_messages[0]->buildMessage()[0]["text"]);

        // Send text to proceed from step1
        $user_input = 'ステップ1の回答';
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = $user_input;

        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $this->assertNotEmpty($next_step_messages);
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $next_step_messages[0]
        );
        // after answering step1, step2 should be presented
        $this->assertCount(1, $next_step_messages[0]->buildMessage());
        $this->assertStringContainsString("これはキャンセルテストのステップ2です。", $next_step_messages[0]->buildMessage()[0]["text"]);

        $session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($session);
        $this->assertEquals($interaction_id, $session->get_interaction_id());


        // Now request cancel confirm via postback with action=cancel
        $reply_event->type = 'postback';
        unset($reply_event->message);
        $reply_event->postback = new \stdClass();
        $reply_event->postback->data = 'mode=interaction&step=cstep-2&action=cancel';
        $cancel_confirm_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        // Expect cancel confirm message to be returned
        $this->assertNotEmpty($cancel_confirm_messages);
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $cancel_confirm_messages[0]
        );
        // Expect built message to be array-like with contents (flex) or text
        $built = $cancel_confirm_messages[0]->buildMessage()[0];
        $this->assertIsArray($built);
        // Title should be present somewhere (raw flex contents may vary), check altText/header/body presence
        $this->assertStringContainsString("申込みを中止しますか？", json_encode($built, JSON_UNESCAPED_UNICODE));

        // Now send abort action
        $reply_event->postback->data = 'mode=interaction&step=cancelConfirm&action=abort';
        $abort_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        $this->assertNotEmpty($abort_messages);
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $abort_messages[0]
        );
        // var_dump($abort_messages[0]->buildMessage());
        $this->assertCount(1, $abort_messages[0]->buildMessage());
        $this->assertStringContainsString("申込みは中止されました。", $abort_messages[0]->buildMessage()[0]["text"]);

        // Session should be deleted
        $current_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNull($current_session);
    }

    public function testCancelContinue() {
        $interaction_id = self::$interaction_ids['interaction_with_cancel'];
        $line_user_id = "U_PLACEHOLDER_USERID4e7a9902e5e7d";
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
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        $interaction_messages = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $this->assertNotEmpty($interaction_messages);
        $this->assertStringContainsString("これはキャンセルテストのステップ1です。", $interaction_messages[0]->buildMessage()[0]["text"]);

        // Send text to proceed from step1
        $user_input = 'ステップ1の回答';
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = $user_input;

        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $this->assertNotEmpty($next_step_messages);
        $this->assertStringContainsString("これはキャンセルテストのステップ2です。", $next_step_messages[0]->buildMessage()[0]["text"]);

        // Request cancel confirm
        $reply_event->type = 'postback';
        unset($reply_event->message);
        $reply_event->postback = new \stdClass();
        $reply_event->postback->data = 'mode=interaction&step=cstep-2&action=cancel';
        $cancel_confirm_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        $this->assertNotEmpty($cancel_confirm_messages);
        $this->assertInstanceOf(\LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class, $cancel_confirm_messages[0]);
        $built = $cancel_confirm_messages[0]->buildMessage()[0];
        $this->assertIsArray($built);
        $this->assertStringContainsString("申込みを中止しますか？", json_encode($built, JSON_UNESCAPED_UNICODE));

        // Choose to continue
        $reply_event->postback->data = 'mode=interaction&step=cancelConfirm&action=continue';
        $continued_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        // Continued messages should present the current step (likely step2 or back to step1 depending on flow).
        $this->assertNotEmpty($continued_messages);
        $this->assertInstanceOf(\LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class, $continued_messages[0]);

        // The present step should show the interaction step text. It might show step1 or step2 depending on implementation.
        $present_text = json_encode($continued_messages[0]->buildMessage()[0], JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString("これはキャンセルテストのステップ2", $present_text);

        // Continue interaction to completion: send a message to move forward if needed
        // If we are back at step1, send step1 answer then step2 answer
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = '最終の回答';

        $final_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        $this->assertEmpty($final_messages);
    }

    public function testCancelByKeyword() {
        $interaction_id = self::$interaction_ids['interaction_with_cancel'];
        $line_user_id = "U_PLACEHOLDER_USERID4e7a9902e5e7d";
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
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );

        $interaction_messages = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $this->assertNotEmpty($interaction_messages);
        $this->assertStringContainsString("これはキャンセルテストのステップ1です。", $interaction_messages[0]->buildMessage()[0]["text"]);

        // Send cancel keyword as plain text message
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = '申し込み中止';

        $cancel_confirm_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        $this->assertNotEmpty($cancel_confirm_messages);
        $this->assertInstanceOf(\LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class, $cancel_confirm_messages[0]);
        $built = $cancel_confirm_messages[0]->buildMessage()[0];
        $this->assertIsArray($built);
        $this->assertStringContainsString("申込みを中止しますか？", json_encode($built, JSON_UNESCAPED_UNICODE));
    }
}
