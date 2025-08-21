<?php
// tests/Interaction/InteractionManager_DuplicateTest.php

require_once __DIR__ . '/InteractionManager_Base.php';

class InteractionManager_DuplicateTest extends InteractionManager_Base {
    // テスト一覧（実装は空）
    // - testStartInteractionReject
    // - testStartInteractionRestartSame
    // - testStartInteractionRestartDiff
    // - testStartInteractionRestartAlways
    // - testStartInteractionStack

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
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
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
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
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
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
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
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
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
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
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
}
