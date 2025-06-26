<?php

/**
 * Class ActionExecuteAjaxActionExecuteTest
 *
 * @package LineConnect
 */

use Shipweb\LineConnect\Core\LineConnect;

class ActionExecuteAjaxActionExecuteTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        // Register necessary hooks
        add_action('wp_ajax_lc_ajax_action_execute', ['Shipweb\LineConnect\ActionExecute\Admin', 'ajax_action_execute']);
        lineconnectTest::init();
    }

    public function test_ajax_action_execute_count_success() {
        // Set user as administrator
        $this->_setRole('administrator');

        // Set request data
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['audience'] = [['condition' => ['conditions' => [['type' => 'role', 'match' => 'role__in', 'role' => ['administrator']]]]]];
        $_POST['actionFlow'] = [['actions' => [['action_name' => 'update_user_profile', 'parameters' => ['key' => 'test_key', 'value' => 'updated']]], 'chains' => []]];
        $_POST['mode'] = 'count';

        // Execute Ajax call
        try {
            $this->_handleAjax('lc_ajax_action_execute');
        } catch (WPAjaxDieStopException $e) {
            // Check for normal termination
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            // Catch other exceptions
            $this->assertNotNull($e->getMessage(), 'Exception occurred, error message should not be empty');
        }

        // Validate response
        $response = json_decode($this->_last_response, true);
        $this->assertIsArray($response, 'Response should be an array');
        $this->assertArrayHasKey('result', $response, 'Response should contain result key');
        $this->assertEquals('success', $response['result'], 'Result flag should be success');
        $this->assertArrayHasKey('success', $response, 'Response should contain success key');
        $this->assertNotEmpty($response['success'], 'Success response should not be empty');
        $this->assertEmpty($response['error'], 'Error response should be empty');
    }

    public function test_ajax_action_execute_execute_success() {
        // Set user as administrator
        $this->_setRole('administrator');

        // Set request data
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['audience'] = [['condition' => ['conditions' => [['type' => 'role', 'match' => 'role__in', 'role' => ['administrator']]]]]];
        $_POST['actionFlow'] = [['actions' => [['action_name' => 'update_user_profile', 'parameters' => ['key' => 'test_key', 'value' => 'updated']]], 'chains' => []]];
        $_POST['mode'] = 'execute';

        // Execute Ajax call
        try {
            $this->_handleAjax('lc_ajax_action_execute');
        } catch (WPAjaxDieStopException $e) {
            // Check for normal termination
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            // Catch other exceptions
            $this->assertNotNull($e->getMessage(), 'Exception occurred, error message should not be empty');
        }

        // Validate response
        $response = json_decode($this->_last_response, true);
        var_dump($response);
        $this->assertIsArray($response, 'Response should be an array');
        $this->assertArrayHasKey('result', $response, 'Response should contain result key');
        $this->assertEquals('success', $response['result'], 'Result flag should be success');
        $this->assertArrayHasKey('success', $response, 'Response should contain success key');
        $this->assertNotEmpty($response['success'], 'Success response should not be empty');
        $this->assertEmpty($response['error'], 'Error response should be empty');
        // check if the user profile has been updated
        $func = new \Shipweb\LineConnect\Action\Definitions\GetUserProfileValue();
        $value = $func->get_user_profile_value("test_key", 'Ud2be13c6f39c97f05c683d92c696483b', '04f7');
        $this->assertNotNull($value, 'User profile value should not be null');
        $this->assertEquals('updated', $value, 'User profile value should match the expected updated value');
    }
}
