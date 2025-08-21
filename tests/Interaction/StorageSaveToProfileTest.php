<?php

require_once __DIR__ . '/InteractionManager_Base.php';

use Shipweb\LineConnect\Core\LineConnect;

class StorageSaveToProfileTest extends InteractionManager_Base {
    // テストケースをここに追加
    public function test_savetoprofile() {
        $interaction_id = self::$interaction_ids['storage_profile'];
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
        $user_input_1 = '〇〇株式会社';
        $user_input_2 = '営業部';
        $user_input_3 = '部長';
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = $user_input_1;

        // Stage 3: Handle the user's reply
        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $reply_event->message->text = $user_input_2;
        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $reply_event->message->text = $user_input_3;
        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        //　プロフィールに保存されたかどうかをチェック
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

        // 現在のプロフィールを取得
        $current_profile = $wpdb->get_var(
            $wpdb->prepare("SELECT profile FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id,  $secret_prefix)
        );

        $profile_array = json_decode($current_profile ?? '{}', true);
        // var_dump($profile_array);
        $this->assertArrayHasKey('会社名', $profile_array);
        $this->assertArrayHasKey('部署名', $profile_array);
        $this->assertArrayNotHasKey('役職名', $profile_array);
        $this->assertEquals($user_input_1, $profile_array['会社名']);
        $this->assertEquals($user_input_2, $profile_array['部署名']);
    }
}
