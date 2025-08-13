<?php

use Shipweb\LineConnect\Chat\API\ChatSend;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;

class ChatSendTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        add_action('wp_ajax_lc_ajax_chat_send', [ChatSend::class, 'ajax_chat_send']);
        lineconnectTest::init();
    }

    private function create_valid_post_data($messages) {
        return [
            'action' => 'lc_ajax_chat_send',
            'nonce' => wp_create_nonce(LineConnect::CREDENTIAL_ACTION__POST),
            'channel' => '04f7',
            'to' => 'Ud2be13c6f39c97f05c683d92c696483a',
            'messages' => $messages,
            'notificationDisabled' => 0
        ];
    }

    public function test_failed_message_send() {
        $this->_setRole('administrator');
        $_POST = $this->create_valid_post_data(
            [
                [ 'type' => 'text'],
                [ 'message' => ['text' => [ 'text' => 'Hello']]]
            ]
        );

        $response = $this->handle_ajax_request();

        $this->assertFalse($response['success']);
        $this->assertEquals('failed', $response['data']['result']);
    }

    public function test_invalid_nonce() {
        $this->_setRole('administrator');
        $_POST = $this->create_valid_post_data([['type' => 'text', 'message' => ['text' => 'Test']]]);
        $_POST['nonce'] = 'invalid_nonce';

        $response = $this->handle_ajax_request();
        $this->assertFalse($response['success']);
        $this->assertEquals('failed', $response['data']['result']);
    }

    public function test_unauthorized_user() {
        $this->_setRole('subscriber');
        $_POST = $this->create_valid_post_data([['type' => 'text', 'message' => ['text' => 'Test']]]);

        $response = $this->handle_ajax_request();
        
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('error', $response['data']);
    }

    public function test_missing_required_parameters() {
        $this->_setRole('administrator');
        
        // channelなし
        $_POST = $this->create_valid_post_data([['type' => 'text', 'message' => ['text' => 'Test']]]);
        unset($_POST['channel']);
        $response = $this->handle_ajax_request();
        $this->assertFalse($response['success']);
        $this->assertEquals('Channel or User is not set.', $response['data']['error'][0]);

        // toなし
        $_POST = $this->create_valid_post_data([['type' => 'text', 'message' => ['text' => 'Test']]]);
        unset($_POST['to']);
        $response = $this->handle_ajax_request();
        $this->assertFalse($response['success']);

        // messagesなし
        $_POST = $this->create_valid_post_data([]);
        unset($_POST['messages']);
        $response = $this->handle_ajax_request();
        $this->assertFalse($response['success']);
    }

    public function test_invalid_channel() {
        $this->_setRole('administrator');
        $_POST = $this->create_valid_post_data([['type' => 'text', 'message' => ['text' => 'Test']]]);
        $_POST['channel'] = 'invalid_channel';

        $response = $this->handle_ajax_request();
        
        $this->assertFalse($response['success']);
        $this->assertEquals('Channel not found.', $response['data']['error'][0]);
    }

    public function test_non_array_messages() {
        $this->_setRole('administrator');
        $_POST = $this->create_valid_post_data('invalid_messages_format');

        $response = $this->handle_ajax_request();
        
        $this->assertFalse($response['success']);
        $this->assertEquals('Channel or User is not set.', $response['data']['error'][0]);
    }

    private function handle_ajax_request() {
        $this->_last_response = '';
        try {
            $this->_handleAjax('lc_ajax_chat_send');
        } catch (WPAjaxDieStopException $e) {
            // 正常終了
        } catch (WPAjaxDieContinueException $e) {
            // エラー発生
        }
        // var_dump($this->_last_response);
        return json_decode($this->_last_response, true);
    }
}
