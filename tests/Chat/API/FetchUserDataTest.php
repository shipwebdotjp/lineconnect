<?php

use Shipweb\LineConnect\Chat\API\FetchUserData;
use Shipweb\LineConnect\Core\LineConnect;

class FetchUserDataTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        add_action('wp_ajax_slc_fetch_user_data', [FetchUserData::class, 'ajax_fetch_user_data']);
        lineconnectTest::init();
    }

    public function test_ajax_fetch_user_data_success() {
        $this->_setRole('administrator');

        $_POST['nonce'] = wp_create_nonce(LineConnect::CREDENTIAL_ACTION__POST);
        $_POST['channel_prefix'] = '04f7';
        $_POST['line_id'] = 'U_PLACEHOLDER_USERID4e7a9902e5e7d';

        try {
            $this->_handleAjax('slc_fetch_user_data');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('channel_prefix', $response['data']);
        $this->assertArrayHasKey('lineId', $response['data']);
        $this->assertArrayHasKey('profile', $response['data']);
        $this->assertEquals('04f7', $response['data']['channel_prefix']);
        $this->assertEquals('U_PLACEHOLDER_USERID4e7a9902e5e7d', $response['data']['lineId']);
    }

    public function test_ajax_fetch_user_data_no_permission() {
        wp_set_current_user(0);

        $_POST['nonce'] = wp_create_nonce(LineConnect::CREDENTIAL_ACTION__POST);
        $_POST['channel_prefix'] = '04f7';
        $_POST['line_id'] = 'U_PLACEHOLDER_USERID4e7a9902e5e7d';

        try {
            $this->_handleAjax('slc_fetch_user_data');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('error', $response['data']);
    }

    public function test_ajax_fetch_user_data_no_channel_prefix() {
        $this->_setRole('administrator');

        $_POST['nonce'] = wp_create_nonce(LineConnect::CREDENTIAL_ACTION__POST);
        $_POST['line_id'] = 'U_PLACEHOLDER_USERID4e7a9902e5e7d';

        try {
            $this->_handleAjax('slc_fetch_user_data');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('failed', $response['data']['result']);
        $this->assertEquals(__('Channel prefix is required.', 'lineconnect'), $response['data']['message']);
    }

    public function test_ajax_fetch_user_data_no_line_id() {
        $this->_setRole('administrator');

        $_POST['nonce'] = wp_create_nonce(LineConnect::CREDENTIAL_ACTION__POST);
        $_POST['channel_prefix'] = '04f7';

        try {
            $this->_handleAjax('slc_fetch_user_data');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('failed', $response['data']['result']);
        $this->assertEquals(__('Line ID is required.', 'lineconnect'), $response['data']['message']);
    }
}
