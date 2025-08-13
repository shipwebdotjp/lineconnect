<?php

use Shipweb\LineConnect\Chat\API\EditUserData;
use Shipweb\LineConnect\Core\LineConnect;

class EditUserDataTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        add_action('wp_ajax_slc_edit_user_data', [EditUserData::class, 'ajax_edit_user_data']);
        lineconnectTest::init();
    }

    private function create_valid_post_data($type, $id = null, $data = null) {
        return [
            'nonce' => wp_create_nonce(LineConnect::CREDENTIAL_ACTION__POST),
            'channel_prefix' => '04f7',
            'line_id' => 'Ud2be13c6f39c97f05c683d92c696483b',
            'type' => $type,
            'id' => $id,
            'data' => $data
        ];
    }

    public function test_successful_profile_update() {
        $this->_setRole('administrator');
        $_POST = $this->create_valid_post_data('profile', null, [
            'name' => 'Test User',
            'age' => 30
        ]);

        $response = $this->edit_user_data();
        
        $this->assertTrue($response['success']);
        $this->assertEquals('success', $response['data']['result']);

        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_LINE_ID;
        $result = $wpdb->get_var($wpdb->prepare("SELECT profile FROM {$table_name} WHERE channel_prefix = %s AND line_id = %s", '04f7', 'Ud2be13c6f39c97f05c683d92c696483b'));
        $profile = json_decode($result, true);
        $this->assertEquals('Test User', $profile['name']);
        $this->assertEquals(30, $profile['age']);
    }

    public function test_scenario_update_with_date_formatting() {
        $this->_setRole('administrator');
        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_LINE_ID;

        $_POST = $this->create_valid_post_data('scenarios', '1', [
            'id' => 1,
            'logs' => [
                [
                    'date' => '2025-08-02 14:30:00',
                    'step' => 'step_1',
                    'result' => 'success',
                    'message' => 'message_1'
                ]
            ],
            'status' => 'active',
            'started_at' => '2025-08-02 14:30:00',
            'updated_at' => '2025-08-02 14:30:00',
            'next_date' => '2025-08-03 15:00:00',
            'next' => 'step_2'
        ]);

        $response = $this->edit_user_data();

        $data = $wpdb->get_var($wpdb->prepare(
            "SELECT scenarios FROM {$table_name} WHERE channel_prefix = %s AND line_id = %s",
            '04f7',
            'Ud2be13c6f39c97f05c683d92c696483b'
        ));
        $scenarios = json_decode($data, true);
        $this->assertArrayHasKey(1, $scenarios);
        $scenario = $scenarios[1];
        $this->assertEquals('2025-08-03T15:00:00+00:00', $scenario['next_date']);
        $this->assertEquals('2025-08-02T14:30:00+00:00', $scenario['started_at']);
        $this->assertEquals('2025-08-02T14:30:00+00:00', $scenario['updated_at']);
        $this->assertEquals('active', $scenario['status']);
        $this->assertEquals('step_2', $scenario['next']);
        $this->assertEquals('step_1', $scenario['logs'][0]['step']);
        $this->assertEquals('success', $scenario['logs'][0]['result']);
        $this->assertEquals('message_1', $scenario['logs'][0]['message']);
    }

    public function test_missing_required_parameters() {
        $this->_setRole('administrator');
        $_POST = [
            'nonce' => wp_create_nonce(LineConnect::CREDENTIAL_ACTION__POST),
            'channel_prefix' => '04f7',
            'line_id' => 'Ud2be13c6f39c97f05c683d92c696483b'
        ];

        $response = $this->edit_user_data();

        $this->assertFalse($response['success']);
        $this->assertEquals('Invalid parameters.', $response['data']['message']);
    }

    public function test_invalid_type_parameter() {
        $this->_setRole('administrator');
        $_POST = $this->create_valid_post_data('invalid_type');

        $response = $this->edit_user_data();
        $this->assertFalse($response['success']);
        $this->assertEquals('Invalid type.', $response['data']['message']);
    }

    public function test_missing_id_for_scenario_type() {
        $this->_setRole('administrator');
        $_POST = $this->create_valid_post_data('scenarios');

        $response = $this->edit_user_data();

        $this->assertFalse($response['success']);
        $this->assertEquals('id is required.', $response['data']['message']);
    }

    public function test_unauthorized_user() {
        wp_set_current_user(0);
        $_POST = $this->create_valid_post_data('profile');

        $response = $this->edit_user_data();

        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('error', $response['data']);
    }

    private function edit_user_data() {
        $this->_last_response = '';
        try {
            $this->_handleAjax('slc_edit_user_data');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage());
        }
        return json_decode($this->_last_response, true);
    }
}
