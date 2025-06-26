<?php

use \Shipweb\LineConnect\Scenario\Scenario;
use Shipweb\LineConnect\Core\LineConnect;


class ExecuteScenarioStepTest extends WP_UnitTestCase {
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
                                    'body' => '3通目です。',
                                ),
                            ),
                        ),
                        'chains' => array(),
                        'schedule' => array(
                            'relative' => 15,
                            'unit' => 'minutes',
                        ),
                        'id' => 'third',
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
        $this->assertEquals($status['next_date'],  wp_date('Y-m-d H:i:s', strtotime("+5 minutes")), "Next date should be 5 minutes from now.");

        // Test 1: Execute second step
        $result = Scenario::execute_step($scenario_id, 'second', $line_user_id, $secret_prefix, wp_date('Y-m-d H:i:s'));
        $this->assertEquals('success', $result['result'], "Executing scenario step should be successful.");
        $this->assertEquals('second', $result['step'], "executed step should be 'second'.");

        // Verify status changed
        $status = Scenario::get_scenario_status($scenario_id, $line_user_id, $secret_prefix);
        $this->assertEquals('active', $status['status'], "Scenario status should be active.");
        $this->assertEquals('third', $status['next'], "Next step should be 'third'.");
        $this->assertEquals($status['next_date'],  wp_date('Y-m-d H:i:s', strtotime("+10 minutes")), "Next date should be 10 minutes from now.");

        // $this->assertArrayNotHasKey('next', $status, "Next step should not be set.");
    }
}
