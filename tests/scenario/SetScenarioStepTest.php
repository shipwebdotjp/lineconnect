<?php

use \Shipweb\LineConnect\Scenario\Scenario;
use Shipweb\LineConnect\Core\LineConnect;


class SetScenarioStepTest extends WP_UnitTestCase {
    protected static $result;
    protected static $scenarios;
    protected static $scenario_data;

    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
        self::$scenario_data = array(
            "シンプルシナリオ" => array(
                array(
                    array(
                        'condition' => array(
                            'conditions' => array(),
                        ),
                        'actions' => array(
                            array(
                                'action_name' => 'get_text_message',
                                'response_return_value' => false,
                                'parameters' => array(
                                    'body' => '1通目です。',
                                ),
                            ),
                        ),
                        'chains' => array(),
                        'schedule' => array(
                            'relative' => 5,
                            'unit' => 'minutes',
                        ),
                        'id' => 'first',
                        'next' => 'second',
                    ),
                    array(
                        'condition' => array(
                            'conditions' => array(),
                        ),
                        'actions' => array(
                            array(
                                'action_name' => 'get_text_message',
                                'response_return_value' => false,
                                'parameters' => array(
                                    'body' => '2通目です。',
                                ),
                            ),
                        ),
                        'chains' => array(),
                        'schedule' => array(
                            'relative' => 10,
                            'unit' => 'minutes',
                        ),
                        'id' => 'second',
                        'stop' => true,
                    ),
                ),
            )
        );

        // Save scenario to database
        foreach (self::$scenario_data as $scenario_name => $scenario_data) {
            $post_id = wp_insert_post(array(
                'post_title' => $scenario_name,
                'post_type' => Scenario::POST_TYPE,
                'post_status' => 'publish',
            ));
            update_post_meta($post_id, Scenario::META_KEY_DATA, $scenario_data);
            update_post_meta($post_id, lineconnect::META_KEY__SCHEMA_VERSION, Scenario::SCHEMA_VERSION);
            self::$scenarios[] = $post_id;
        }
    }

    /**
     * Test setting a scenario step
     */
    public function test_set_scenario_step() {
        $scenario_id = self::$scenarios[0];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        // Initial setup: Start the scenario
        $func = new \Shipweb\LineConnect\Action\Definitions\StartScenario();
        $func->set_secret_prefix($secret_prefix);
        $func->set_event((object) array("source" => (object) array("userId" => $line_user_id)));
        $result = $func->start_scenario($scenario_id);

        // Verify scenario started correctly
        $this->assertNotEmpty($result, "Failed to start the scenario.");
        $status = Scenario::get_scenario_status($scenario_id, $line_user_id, $secret_prefix);
        $this->assertEquals('active', $status['status'], "Scenario status should be active.");
        $this->assertEquals('second', $status['next'], "Next step should be 'second'.");

        // Test 1: Set step without changing next_date
        $result = Scenario::set_scenario_step($scenario_id, 'first', null, $line_user_id, $secret_prefix);
        $this->assertEquals('success', $result['result'], "Setting scenario step should be successful.");
        $this->assertEquals('first', $result['next'], "Next step should be 'first'.");

        // Verify status changed
        $status = Scenario::get_scenario_status($scenario_id, $line_user_id, $secret_prefix);
        $this->assertEquals('active', $status['status'], "Scenario status should be active.");
        $this->assertEquals('first', $status['next'], "Next step should be changed to 'first'.");
        $expected_date = gmdate(DATE_ATOM, strtotime('+5 minutes'));
        $this->assertEquals($expected_date, $status['next_date'], "Next date should be set to 5 minutes from now.");

        // Test 2: Set step with specific date string
        $future_date = gmdate(DATE_ATOM, strtotime('+30 minutes'));
        $result = Scenario::set_scenario_step($scenario_id, 'second', $future_date, $line_user_id, $secret_prefix);
        $this->assertEquals('success', $result['result'], "Setting scenario step with date should be successful.");
        $this->assertEquals('second', $result['next'], "Next step should be 'second'.");
        $this->assertEquals($future_date, $result['next_date'], "Next date should match the set date.");

        // Verify status changed
        $status = Scenario::get_scenario_status($scenario_id, $line_user_id, $secret_prefix);
        $this->assertEquals('active', $status['status'], "Scenario status should be active.");
        $this->assertEquals('second', $status['next'], "Next step should be changed to 'second'.");
        $this->assertEquals($future_date, $status['next_date'], "Next date should match the set date.");

        // Test 3: Set step with relative date string
        $result = Scenario::set_scenario_step($scenario_id, 'first', '+15 minutes', $line_user_id, $secret_prefix);
        $expected_date = gmdate(DATE_ATOM, strtotime('+15 minutes'));
        $this->assertEquals('success', $result['result'], "Setting scenario step with relative date should be successful.");
        $this->assertEquals('first', $result['next'], "Next step should be 'first'.");
        $this->assertEquals($expected_date, $result['next_date'], "Next date should match the relative date.");

        // Test 4: Test with past date (should set to current date)
        $past_date = gmdate(DATE_ATOM, strtotime('-1 hour'));
        $result = Scenario::set_scenario_step($scenario_id, 'second', $past_date, $line_user_id, $secret_prefix);
        $now = gmdate(DATE_ATOM);
        $this->assertEquals('success', $result['result'], "Setting scenario step with past date should be successful.");
        $this->assertEquals('second', $result['next'], "Next step should be 'second'.");
        // Past dates should be adjusted to current time
        $this->assertNotEquals($past_date, $result['next_date'], "Past date should not be used as next date.");
        $this->assertEquals($now, $result['next_date'], "Next date should be set to current time.");

        // Test 5: Test with non-existent step
        $result = Scenario::set_scenario_step($scenario_id, 'nonexistent', null, $line_user_id, $secret_prefix);
        $this->assertEquals('error', $result['result'], "Setting a non-existent step should return an error.");
        $this->assertArrayHasKey('message', $result, "Error result should contain a message.");

        // Test 6: Test with non-existent scenario
        $non_existent_scenario_id = 999999;
        $result = Scenario::set_scenario_step($non_existent_scenario_id, 'first', null, $line_user_id, $secret_prefix);
        $this->assertEquals('error', $result['result'], "Setting a step in a non-existent scenario should return an error.");
        $this->assertArrayHasKey('message', $result, "Error result should contain a message.");

        // Test 7: Test with no next-date
        $result = Scenario::update_scenario_status($scenario_id, Scenario::STATUS_COMPLETED, $line_user_id, $secret_prefix);
        $this->assertTrue($result, "Updating scenario status should be successful.");
        $status = Scenario::get_scenario_status($scenario_id, $line_user_id, $secret_prefix);
        $this->assertEquals('completed', $status['status'], "Scenario status should be completed.");
        $this->assertArrayNotHasKey('next', $status, "Next step should be empty.");
        $this->assertArrayNotHasKey('next_date', $status, "Next date should be empty.");
        $expected_date = gmdate(DATE_ATOM);

        $result = Scenario::set_scenario_step($scenario_id, 'first', null, $line_user_id, $secret_prefix);
        $this->assertEquals('success', $result['result'], "Setting scenario step should be successful.");
        $this->assertEquals('first', $result['next'], "Next step should be 'first'.");
        $this->assertEquals($expected_date, $result['next_date'], "Next date should be set to 5 minutes from now.");
    }
}
