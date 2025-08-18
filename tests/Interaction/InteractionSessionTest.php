<?php

use Shipweb\LineConnect\Interaction\InteractionDefinition;
use Shipweb\LineConnect\Interaction\InteractionSession;

class InteractionSessionTest extends WP_UnitTestCase {
    public function test_start_session() {
        // Mock InteractionDefinition
        $interaction_definition_mock = $this->createMock(InteractionDefinition::class);
        $interaction_definition_mock->method('get_id')->willReturn(1);

        $session = InteractionSession::start($interaction_definition_mock, 'test_user_123', 'chan');

        $this->assertEquals('active', $session->get_status());

        $db_array = $session->to_db_array();
        $this->assertEquals('test_user_123', $db_array['line_user_id']);
        $this->assertEquals('chan', $db_array['channel_prefix']);
        $this->assertEquals(1, $db_array['interaction_id']);
    }

    public function test_from_db_row() {
        $row = (object) [
            'id' => 10,
            'channel_prefix' => 'chan',
            'line_user_id' => 'test_user_123',
            'interaction_id' => 1,
            'interaction_version' => 1,
            'status' => 'active',
            'current_step_id' => 'step1',
            'previous_step_id' => null,
            'answers' => json_encode(['name' => 'Taro']),
            'expires_at' => '2025-08-16 12:30:00',
            'created_at' => '2025-08-16 12:00:00',
            'updated_at' => '2025-08-16 12:05:00',
        ];

        $session = InteractionSession::from_db_row($row);

        $this->assertEquals(10, $session->get_id());
        $this->assertEquals('active', $session->get_status());
        $this->assertEquals('step1', $session->get_current_step_id());
        $this->assertEquals('Taro', $session->get_answer('name'));
    }
}
