<?php
// tests/Interaction/InteractionManager_ValidateTest.php

require_once __DIR__ . '/InteractionManager_Base.php';

class InteractionManager_quickReplyTest extends InteractionManager_Base {
    /**
     * ケース1:
     * 数字のみ許可されているところに英字を入れてエラーメッセージが表示されることを確認
     */
    public function testQuickReplySkipOption() {
        $interaction_id = self::$interaction_ids['interaction_with_quickreply'];
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
        // var_dump($start_messages[0]->buildMessage());
        // QuickReplyがあることをアサート (quickReplyはbuildMessage()の返り値の配列のキーとして存在するか)
        $this->assertArrayHasKey("quickReply", $start_messages[0]->buildMessage()[0]);
        $this->assertArrayHasKey("items", $start_messages[0]->buildMessage()[0]["quickReply"]);
        $this->assertArrayHasKey("type", $start_messages[0]->buildMessage()[0]["quickReply"]["items"][0]);
        $this->assertArrayHasKey("action", $start_messages[0]->buildMessage()[0]["quickReply"]["items"][0]);
    }
}
