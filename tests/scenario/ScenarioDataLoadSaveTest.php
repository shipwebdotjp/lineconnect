<?php

use Shipweb\LineConnect\Scenario\Scenario;
use Shipweb\LineConnect\Core\Cron;
use Shipweb\LineConnect\Core\LineConnect;


class ScenarioDataLoadSaveTest extends WP_UnitTestCase {
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
            ),
            "条件分岐シナリオ" => array(
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
                            'relative' => 10,
                            'unit' => 'minutes',
                        ),
                        'id' => 'first',
                        'next' => 'second',
                    ),
                    array(
                        'condition' => array(
                            'conditions' => array(
                                array(
                                    'type' => 'channel',
                                    'secret_prefix' => array(
                                        '04f7',
                                    ),
                                ),
                            ),
                        ),
                        'actions' => array(
                            array(
                                'action_name' => 'get_text_message',
                                'response_return_value' => false,
                                'parameters' => array(
                                    'body' => '2通目です。',
                                ),
                            ),
                            array(
                                'action_name' => 'set_scenario_step',
                                'response_return_value' => false,
                                'parameters' => array(
                                    'slc_scenario_id' => 5,
                                    'step_id' => 'fourth',
                                    'next_date' => '+20 minutes',
                                ),
                            ),
                        ),
                        'chains' => array(),
                        'schedule' => array(
                            'relative' => 10,
                            'unit' => 'minutes',
                        ),
                        'id' => 'second',
                        'next' => 'third',
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
                            'relative' => 10,
                            'unit' => 'minutes',
                        ),
                        'id' => 'third',
                        'stop' => true,
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
                                    'body' => '4通目です。',
                                ),
                            ),
                        ),
                        'chains' => array(),
                        'schedule' => array(
                            'relative' => 10,
                            'unit' => 'minutes',
                        ),
                        'id' => 'fourth',
                        'stop' => true,
                    ),
                ),
            )
        );
        foreach (self::$scenario_data as $scenario_name => $scenario_data) {
            //save post
            $post_id = wp_insert_post(array(
                'post_title' => $scenario_name,
                'post_type' => Scenario::POST_TYPE,
                'post_status' => 'publish',
            ));
            // inject real scenario ID for any set_scenario_step actions
            $data = $scenario_data;
            foreach ($data as &$group) {
                foreach ($group as &$step) {
                    if (!empty($step['actions'])) {
                        foreach ($step['actions'] as &$action) {
                            if ($action['action_name'] === 'set_scenario_step') {
                                $action['parameters']['slc_scenario_id'] = $post_id;
                            }
                        }
                    }
                }
            }
            unset($action, $step, $group);
            update_post_meta($post_id, Scenario::META_KEY_DATA, $data);
            update_post_meta($post_id, lineconnect::META_KEY__SCHEMA_VERSION, Scenario::SCHEMA_VERSION);
            self::$scenarios[] = $post_id;
        }
    }

    public function testGetScenario() {
        $post = get_post(self::$scenarios[0]);
        $this->assertNotEmpty($post, "Failed to get post data.");
        $this->assertEquals($post->post_title, "シンプルシナリオ", "Post data loaded successfully.");
        $post_meta = get_post_meta($post->ID, Scenario::META_KEY_DATA, true);
        $this->assertNotEmpty($post_meta, "Failed to get post meta data.");
        $scenario = Scenario::getScenario(self::$scenarios[0]);
        $this->assertNotEmpty($scenario, "Failed to load scenario data.");
    }

    public function test_get_scenario_status() {
        $start_scenario = new \Shipweb\LineConnect\Action\Definitions\StartScenario();
        $start_scenario->set_secret_prefix("04f7");
        $start_scenario->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $set_scenario_step = new \Shipweb\LineConnect\Action\Definitions\SetScenarioStep();
        $set_scenario_step->set_secret_prefix("04f7");
        $set_scenario_step->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $change_scenario_status = new \Shipweb\LineConnect\Action\Definitions\ChangeScenarioStatus();
        $change_scenario_status->set_secret_prefix("04f7");
        $change_scenario_status->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");

        $this->assertEmpty($status, "Failed to get scenario status.");
        //シナリオ開始
        $result = $start_scenario->start_scenario(self::$scenarios[0]);
        $this->assertNotEmpty($result, "シナリオが開始されることを確認");
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertNotEmpty($status, "Failed to get scenario status.");

        $scheduled_scenarios = Cron::get_scenarios(time() - 60, time());
        $this->assertEmpty($scheduled_scenarios, "実行すべきシナリオは見つからないことを確認");
        $scheduled_scenarios = Cron::get_scenarios(time() + 240, time() + 300);
        $this->assertNotEmpty($scheduled_scenarios, "実行すべきシナリオが見つかることを確認");
        $this->assertEquals($scheduled_scenarios[0]['id'], self::$scenarios[0], "実行すべきシナリオが正しいことを確認");
        $this->assertEquals($scheduled_scenarios[0]['next'], 'second', "次のステップが正しいことを確認");
        $this->assertEquals($scheduled_scenarios[0]['line_id'], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "line_idが正しいことを確認");
        $this->assertEquals($scheduled_scenarios[0]['channel_prefix'], "04f7", "channel_prefixが正しいことを確認");

        $this->assertArrayHasKey('status', $status, "Status key is missing in the response.");
        $this->assertEquals($status['status'], 'active', "Scenario status is not active.");
        $this->assertArrayHasKey('next', $status, "Next key is missing in the response.");
        $this->assertEquals($status['next'], 'second', "Next scenario is not second.");
        $this->assertArrayHasKey('next_date', $status, "Next date key is missing in the response.");
        $this->assertNotEmpty($status['next_date'], "Next date is empty.");
        $this->assertEquals($status['next_date'], gmdate(DATE_ATOM, strtotime('+5 minutes')), "Next date is not correct.");

        //シナリオ開始アクションのテスト
        $result = $start_scenario->start_scenario(self::$scenarios[0], 'none');
        $this->assertEqualSets($result, ['result' => 'skip', 'message' => 'Scenario already started'], "実行中の場合は開始されないことを確認.");

        $result = $start_scenario->start_scenario(self::$scenarios[0], 'completed');
        $this->assertEquals($result['result'], 'skip', "completedではないので開始されないことを確認");

        $result = $start_scenario->start_scenario(self::$scenarios[0], 'always');
        $this->assertEquals($result['result'], 'success', "シナリオが開始されることを確認");

        //execute second step
        $result = Scenario::execute_step(self::$scenarios[0], 'second', "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($result['result'], 'success', "シナリオの第2ステップが正常に実行されることを確認。");
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");

        $this->assertNotEmpty($status, "Failed to get scenario status after executing step.");
        $this->assertArrayHasKey('status', $status, "Status key is missing in the response after step execution.");
        $this->assertEquals($status['status'], 'completed', "Scenario status is not completed after executing step.");

        $this->assertArrayNotHasKey('next_date', $status, "レスポンスに次の日付キーがありません。");
        $this->assertArrayNotHasKey('next', $status, "レスポンスに次のキーがありません。");

        $scheduled_scenarios = Cron::get_scenarios(time() - 300, time() + 300);
        $this->assertEmpty($scheduled_scenarios, "実行すべきシナリオは見つからないことを確認");

        //シナリオ開始アクションのテスト
        $result = $start_scenario->start_scenario(self::$scenarios[0], 'completed');
        $this->assertEquals($result['result'], 'success', "completedの場合は開始されることを確認");
        $status = Scenario::get_scenario_status(self::$scenarios[0],            "U_PLACEHOLDER_USERID4e7a9902e5e7d",            "04f7");
        $this->assertEquals($status['status'], 'active', "Scenario status is active.");
        $this->assertEquals($status['next'], 'second', "Next scenario is second.");

        //ステップセットアクションのテスト
        $result = $set_scenario_step->set_scenario_step(self::$scenarios[0], 'first', null, "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $laststatus = $status;
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($status['status'], 'active', "Scenario status is active.");
        $this->assertEquals($status['next'], 'first', "Next scenario is first.");
        $this->assertEquals($status['next_date'], $laststatus['next_date'], "Next date is same as before.");

        $next_date = gmdate(DATE_ATOM, strtotime('+5 minutes'));
        $result = $set_scenario_step->set_scenario_step(self::$scenarios[0], 'first', $next_date, "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertArrayHasKey('result', $result, "Result key is missing in the response.");
        $this->assertEquals($result['result'], 'success', "Failed to set user scenario step.");

        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($status['status'], 'active', "Scenario status is active.");
        $this->assertEquals($status['next'], 'first', "Next scenario is first.");
        $this->assertEquals($status['next_date'], $next_date, "Next date is same as before.");

        $result = $set_scenario_step->set_scenario_step(self::$scenarios[0], 'first', '+1 minutes', "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($result['next_date'], gmdate(DATE_ATOM, strtotime('+1 minutes')), "Next date should be adjusted by +1 minute.");

        $result = $set_scenario_step->set_scenario_step(self::$scenarios[0], 'first', '2125-01-01 15:00:00', "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($result['next_date'], gmdate(DATE_ATOM, strtotime('2125-01-01 15:00:00')), "Next date should match the set date.");

        $set_scenario_step->set_scenario_id(self::$scenarios[0]);
        $result = $set_scenario_step->set_scenario_step(null, 'first', '2125-02-01 15:00:00', "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($result['next_date'], gmdate(DATE_ATOM, strtotime('2125-02-01 15:00:00')), "Next date should be adjusted by +1 minute.");

        //条件分岐シナリオテスト
        $result = $start_scenario->start_scenario(self::$scenarios[1]);
        $this->assertNotEmpty($result, "Failed to start the conditional branch scenario.");
        $status = Scenario::get_scenario_status(self::$scenarios[1], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertNotEmpty($status, "Failed to get scenario status after starting conditional branch.");
        $this->assertArrayHasKey('status', $status, "Status key is missing in the response for conditional branch.");
        $this->assertEquals($status['status'], 'active', "Conditional branch scenario status is not active.");
        $this->assertArrayHasKey('next', $status, "Next key is missing in the response for conditional branch.");
        $this->assertEquals($status['next'], 'second', "Next scenario is not second for conditional branch.");

        //条件に一致するのでジャンウアクションが実行されfourthに進む
        // error_log("id:" . print_r(self::$scenarios[1], true));  
        $result = Scenario::execute_step(self::$scenarios[1], 'second', "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($result['result'], 'success', "Failed to execute step for conditional branch.");
        $this->assertEquals($result['step'], 'second', "Failed to execute step for conditional branch.");

        $status = Scenario::get_scenario_status(self::$scenarios[1], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertArrayHasKey('status', $status, "Status key is missing in the response after step execution.");
        $this->assertEquals($status['status'], 'active', "Scenario status is not active after executing step.");
        $this->assertEquals($status['next'], 'fourth', "Next scenario is not fourth after executing step.");

        $scheduled_scenarios = Cron::get_scenarios(time() - 60, time());
        $this->assertEmpty($scheduled_scenarios, "実行すべきシナリオは見つからないことを確認");
        $scheduled_scenarios = Cron::get_scenarios(time() + 1140, time() + 1200);
        $this->assertNotEmpty($scheduled_scenarios, "実行すべきシナリオが見つかることを確認");
        $scheduled_scenarios = Cron::get_scenarios(time() + 1200, time() + 1260);
        $this->assertEmpty($scheduled_scenarios, "実行すべきシナリオは見つからないことを確認");

        //別ユーザーでのテスト
        $func_2f38_Uicc = new \Shipweb\LineConnect\Action\Definitions\StartScenario();
        $func_2f38_Uicc->set_secret_prefix("2f38");
        $func_2f38_Uicc->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID1ccdbac80ea15")));
        $func_2f38_Uicc->start_scenario(self::$scenarios[1]);

        $status = Scenario::get_scenario_status(self::$scenarios[1], "U_PLACEHOLDER_USERID1ccdbac80ea15", "2f38");
        $this->assertNotEmpty($status, "Failed to get scenario status after starting.");
        $this->assertArrayHasKey('status', $status, "Status key is missing in the response.");
        $this->assertEquals($status['status'], 'active', "Scenario status is not active after starting.");
        $this->assertArrayHasKey('next', $status, "Next key is missing in the response.");
        $this->assertEquals($status['next'], 'second', "Next scenario is not second after starting.");

        //条件に一致しないのでthirdに進む
        $result = Scenario::execute_step(self::$scenarios[1], 'second', "U_PLACEHOLDER_USERID1ccdbac80ea15", "2f38");
        $this->assertEquals($result['result'], 'skip', "Failed to execute step for conditional branch after modifying the input.");
        $this->assertEquals($result['step'], 'second', "Failed to execute step for conditional branch after modifying the input.");

        $status = Scenario::get_scenario_status(self::$scenarios[1], "U_PLACEHOLDER_USERID1ccdbac80ea15", "2f38");
        $this->assertEquals($status['status'], 'active', "Scenario status is not active after executing step.");
        $this->assertEquals($status['next'], 'third', "Next scenario is not third after executing step.");

        $result = Scenario::execute_step(self::$scenarios[1], 'noexsists', "U_PLACEHOLDER_USERID1ccdbac80ea15", "2f38");
        $this->assertEquals($result['result'], 'error', "Executing a non-existent step should return an error.");
        $this->assertArrayHasKey('message', $result, "Error message should be present in the response.");

        $result = Scenario::execute_step(999999999999999, null, "U_PLACEHOLDER_USERID1ccdbac80ea15", "2f38");
        $this->assertEquals($result['result'], 'error', "Executing a non-existent scenario should return an error.");
        $this->assertArrayHasKey('message', $result, "Error message should be present in the response for non-existent scenario.");

        //シナリオ再スタート時にnextとnext_dateがリセットされることを確認
        $result = $func_2f38_Uicc->start_scenario(self::$scenarios[1], 'always');
        $this->assertEquals($result['result'], 'success', "Failed to start the conditional branch scenario.");
        $status = Scenario::get_scenario_status(self::$scenarios[1], "U_PLACEHOLDER_USERID1ccdbac80ea15", "2f38");
        $this->assertNotEmpty($status, "Failed to get scenario status after starting conditional branch.");
        $this->assertArrayHasKey('status', $status, "Status key is missing in the response for conditional branch.");
        $this->assertEquals($status['status'], 'active', "Conditional branch scenario status is not active.");
        $this->assertArrayHasKey('next', $status, "Next key is missing in the response for conditional branch.");
        $this->assertArrayHasKey('next_date', $status, "Next date key is missing in the response for conditional branch.");
        $this->assertEquals($status['next'], 'second', "Next scenario should be second for conditional branch.");
        $this->assertEquals($status['next_date'], gmdate(DATE_ATOM, strtotime('+10 minutes')), "Next date should be 10 minutes after the start.");
    }

    public function test_duble_start_scenario() {
        $start_scenario = new \Shipweb\LineConnect\Action\Definitions\StartScenario();
        $start_scenario->set_secret_prefix("04f7");
        $start_scenario->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $result = $start_scenario->start_scenario(self::$scenarios[0]);
        $this->assertNotEmpty($result, "Failed to start the scenario.");
        $result = $start_scenario->start_scenario(self::$scenarios[0]);
        $this->assertEqualSets($result, ['result' => 'skip', 'message' => 'Scenario already started'], "Failed to skip the scenario that is already started.");

        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($status['status'], 'active', "Scenario status should remain active.");
        $this->assertEquals($status['next'], 'second', "Next scenario should still be first.");

        //別ユーザーでのテスト
        $start_scenario = new \Shipweb\LineConnect\Action\Definitions\StartScenario();
        $start_scenario->set_secret_prefix("2f38");
        $start_scenario->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID1ccdbac80ea15")));
        $start_scenario->start_scenario(self::$scenarios[0]);
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID1ccdbac80ea15", "2f38");
        $this->assertEquals($status['status'], 'active', "Scenario status should remain active.");
        $this->assertEquals($status['next'], 'second', "Next scenario should still be first.");

        $scheduled_scenarios = Cron::get_scenarios(time() + 240, time() + 300);
        $this->assertEquals(count($scheduled_scenarios), 2, "Scheduled scenarios should be 2.");
    }

    public function test_update_status() {
        $start_scenario = new \Shipweb\LineConnect\Action\Definitions\StartScenario();
        $start_scenario->set_secret_prefix("04f7");
        $start_scenario->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $result = $start_scenario->start_scenario(self::$scenarios[0]);
        $this->assertNotEmpty($result, "Failed to start the scenario.");
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertNotEmpty($status, "Failed to get scenario status after starting.");
        $this->assertEquals($status['status'], 'active', "Scenario status is not active.");

        $change_scenario_status = new \Shipweb\LineConnect\Action\Definitions\ChangeScenarioStatus();
        $change_scenario_status->set_secret_prefix("04f7");
        $change_scenario_status->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        // set paused status
        $change_scenario_status->change_scenario_status(self::$scenarios[0], 'paused', "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($status['status'], 'paused', "Scenario status is not paused.");

        // set error status
        $change_scenario_status->change_scenario_status(self::$scenarios[0], 'error', "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($status['status'], 'error', "Scenario status is not error.");

        // set completed status
        $change_scenario_status->change_scenario_status(self::$scenarios[0], 'completed', "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($status['status'], 'completed', "Scenario status is not completed.");

        // set active status
        $change_scenario_status->change_scenario_status(self::$scenarios[0], 'active', "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $status = Scenario::get_scenario_status(self::$scenarios[0], "U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals($status['status'], 'active', "Scenario status is not active.");
    }
}
