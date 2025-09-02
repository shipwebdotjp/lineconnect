<?php

/**
 * Class AjaxTest
 *
 * @package LineConnect
 */

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\BulkMessage\Screen as BulkMessageScreen;

require_once __DIR__ . '/../../tests/LINEBot/Util/DummyHttpClient.php';

class BulkmessageAjaxChatSendFailTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        // 必要なフックを登録
        add_action('wp_ajax_lc_ajax_chat_send', [BulkMessageScreen::class, 'ajax_chat_send']);

        lineconnectTest::init();
    }

    public function test_ajax_chat_send_validate_failed_with_invalid_message() {
        $this->_setRole('administrator');
        add_filter(LineConnect::FILTER_PREFIX . 'httpclient', function ($httpClient) {
            $mock = function ($testRunner, $httpMethod, $url, $data) {
                /*{
  "message": "The request body has 2 error(s)",
  "details": [
    {
      "message": "May not be empty",
      "property": "messages[0].text"
    }
  ]
}
*/
                return [
                    'status' => 400,
                    'message' => 'The request body has 1 error(s)',
                    'details' => [
                        [
                            'message' => 'May not be empty',
                            'property' => 'messages[0].text'
                        ]
                    ]
                ];
            };
            $dummyHttpClient = new LINE\Tests\LINEBot\Util\DummyHttpClient($this, $mock);
            return $dummyHttpClient;
        });

        // Invalid message structure - missing required 'text' field
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['messages'] = [['type' => 'text'], ['message' => ['invalid' => 'data']]];
        $_POST['audience'] = [['condition' => ['conditions' => [['type' => 'role', 'match' => 'role__in', 'role' => ['administrator']]]]]];
        $_POST['mode'] = 'validate';

        try {
            $this->_handleAjax('lc_ajax_chat_send');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        // var_dump($response);
        $this->assertIsArray($response, 'レスポンスは配列であることを確認');
        $this->assertArrayHasKey('result', $response, '正しいキーが含まれることを確認');
        $this->assertEquals('failed', $response['result'], '結果フラグが正しいことを確認');
        $this->assertEmpty($response['success'], '成功レスポンスが空であることを確認');
        $this->assertNotEmpty($response['error'], '失敗レスポンスが配列であることを確認');
    }
}
