<?php
// tests/Interaction/InteractionManager_SingleTest.php

require_once __DIR__ . '/InteractionManager_Base.php';

class InteractionManager_SingleTest extends InteractionManager_Base {
    // テスト一覧（実装は空）
    // - testStartInteraction
    // - testInteractionLifecycle
    // - testInteractionLifecycleB

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
}
