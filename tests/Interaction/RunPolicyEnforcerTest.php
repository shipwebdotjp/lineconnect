<?php

require_once __DIR__ . '/InteractionManager_Base.php';

class RunPolicyEnforcerTest extends InteractionManager_Base {
    // テストケースをここに追加
    public function test_single_latest_only() {
        // 複数回実行した際に、最後の結果だけが保持されることを確認する
        $interaction_id = self::$interaction_ids['RunPolicy_single_latest_only'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";
        $event = new \stdClass();
        $event->{'source'} = new \stdClass();
        $event->{'source'}->{'userId'} = $line_user_id;

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

        // Stage 4: Assert the data in session
        $session = $session_repository->find_sessions_by_interaction($secret_prefix, $line_user_id, $interaction_id);
        $this->assertCount(1, $session);

        // 同じインタラクションで別のセッションを開始して完了させたきの動作を検証する

        $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $session = $session_repository->find_sessions_by_interaction($secret_prefix, $line_user_id, $interaction_id);
        $this->assertCount(1, $session);
    }

    public function test_single_forbid() {
        // すでに保存されている結果があるインタラクションを開始しようとしても何も返ってこない(結果が空)確認する
        // 複数回実行した際に、最後の結果だけが保持されることを確認する
        $interaction_id = self::$interaction_ids['RunPolicy_single_forbid'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";
        $event = new \stdClass();
        $event->{'source'} = new \stdClass();
        $event->{'source'}->{'userId'} = $line_user_id;

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

        // Stage 4: Assert the data in session
        $session = $session_repository->find_sessions_by_interaction($secret_prefix, $line_user_id, $interaction_id);
        $this->assertCount(1, $session);

        // 同じインタラクションで別のセッションを開始して完了させたきの動作を検証する
        $messages = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $this->assertEmpty($messages);
    }
}
