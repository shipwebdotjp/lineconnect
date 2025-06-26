<?php

/**
 * Class AjaxTest
 *
 * @package LineConnect
 */
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\BulkMessage\Screen as BulkMessageScreen;

class BulkmessageAjaxChatSendTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        // 必要なフックを登録
        add_action('wp_ajax_lc_ajax_chat_send', [BulkMessageScreen::class, 'ajax_chat_send']);
        lineconnectTest::init();

    }

    public function test_ajax_chat_send_count_success() {
        // ユーザーを作成しログイン
        // $user_id = WP_UnitTestCase_Base::factory()->user->create([
        //     'role' => 'administrator', // テスト用の適切な権限を持ったユーザー
        // ]);
        // wp_set_current_user($user_id);

        $this->_setRole( 'administrator' );

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['messages'] = [['type' => 'text'], ['message' => ['text' => ['text' => 'Test']]]];
        $_POST['audience'] = [['condition' => ['conditions' => [['type'=>'role', 'match'=>'role__in', 'role' => ['administrator']]]]]]; 
        $_POST['mode'] = 'count';
        
        // Ajax 呼び出しの実行
        try {
            $this->_handleAjax('lc_ajax_chat_send');
        } catch (WPAjaxDieStopException $e) {
            // エラーではなく正常終了を確認
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            // その他の例外をキャッチ
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }
        // var_dump($this->_last_response);
        // レスポンスの検証
        $response = json_decode($this->_last_response, true);
        $this->assertIsArray($response, 'レスポンスは配列であることを確認');
        $this->assertArrayHasKey('result', $response, '正しいキーが含まれることを確認');
        $this->assertEquals('success', $response['result'], '結果フラグが正しいことを確認');
        $this->assertArrayHasKey('success', $response, '成功レスポンスが含まれることを確認');
        $this->assertNotEmpty($response['success'], '成功レスポンスが配列であることを確認');
        $this->assertEmpty($response['error'], '失敗レスポンスが空であることを確認');


    }

    public function test_ajax_chat_send_count_no_permission() {
        // ログインしていない状態
        wp_set_current_user(0);

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['messages'] = [['type' => 'text'], ['message' => ['text' => ['text' => 'Test']]]];
        $_POST['audience'] = [['condition' => ['conditions' => [['type'=>'role', 'match'=>'role__in', 'role' => ['administrator']]]]]]; 
        $_POST['mode'] = 'count';

        // Ajax 呼び出しの実行
        try {
            $this->_handleAjax('lc_ajax_chat_send');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        }catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        // レスポンスの検証
        $response = json_decode($this->_last_response, true);
        $this->assertEquals('failed', $response['result'], '結果フラグが正しいことを確認');
        $this->assertEmpty($response['success'], '成功レスポンスが空であることを確認');
        $this->assertNotEmpty($response['error'], '失敗レスポンスが配列であることを確認');
    }

    public function test_ajax_chat_send_条件に一致する送信対象なし(){
        $this->_setRole( 'administrator' );
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['messages'] = [['type' => 'text'], ['message' => ['text' => ['text' => 'Test']]]];
        $_POST['audience'] = [['condition' => ['conditions' => [['type'=>'role', 'match'=>'role__in', 'role' => ['noExistRole']]]]]]; 
        $_POST['mode'] = 'send';
        
        try {
            $this->_handleAjax('lc_ajax_chat_send');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertIsArray($response, 'レスポンスは配列であることを確認');
        $this->assertArrayHasKey('result', $response, '正しいキーが含まれることを確認');
        $this->assertEquals('success', $response['result'], '結果フラグが正しいことを確認');
        $this->assertArrayHasKey('success', $response, '成功レスポンスが含まれることを確認');
        $this->assertNotEmpty($response['success'], '成功レスポンスが配列であることを確認');
        $this->assertEmpty($response['error'], '失敗レスポンスが空であることを確認');
    }

    public function test_ajax_chat_send_no_nonce(){
        $this->_setRole( 'administrator' );
        $_POST['messages'] = [['type' => 'text'], ['message' => ['text' => ['text' => 'Test']]]];
        $_POST['audience'] = [['condition' => ['conditions' => [['type'=>'role', 'match'=>'role__in', 'role' => ['administrator']]]]]];
        $_POST['mode'] = 'count';

        try {
            $this->_handleAjax('lc_ajax_chat_send');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertIsArray($response, 'レスポンスは配列であることを確認');
        $this->assertArrayHasKey('result', $response, '正しいキーが含まれることを確認');
        $this->assertEquals('failed', $response['result'], '結果フラグが正しいことを確認');
        $this->assertEmpty($response['success'], '成功レスポンスが空であることを確認');
        $this->assertNotEmpty($response['error'], '失敗レスポンスが配列であることを確認');
    }

    public function test_ajax_chat_send_invalid_nonce(){
        $this->_setRole( 'administrator' );
        $_POST['nonce'] = 'invalid_nonce_value';
        $_POST['messages'] = [['type' => 'text'], ['message' => ['text' => ['text' => 'Test']]]];
        $_POST['audience'] = [['condition' => ['conditions' => [['type'=>'role', 'match'=>'role__in', 'role' => ['administrator']]]]]];
        $_POST['mode'] = 'count';

        try {
            $this->_handleAjax('lc_ajax_chat_send');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('-1', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertNull($response, 'レスポンスがNULLであることを確認');
    }

    public function test_ajax_chat_send_validate_success() {
        $this->_setRole( 'administrator' );

        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['messages'] = [['type' => 'text'], ['message' => ['text' => ['text' => 'Test']]]];
        $_POST['audience'] = [['condition' => ['conditions' => [['type'=>'role', 'match'=>'role__in', 'role' => ['administrator']]]]]]; 
        $_POST['mode'] = 'validate';
        
        try {
            $this->_handleAjax('lc_ajax_chat_send');
        } catch (WPAjaxDieStopException $e) {
            // エラーではなく正常終了を確認
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            // その他の例外をキャッチ
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }
        $response = json_decode($this->_last_response, true);
        // var_dump($response);
        $this->assertIsArray($response, 'レスポンスは配列であることを確認');
        $this->assertArrayHasKey('result', $response, '正しいキーが含まれることを確認');
        $this->assertEquals('success', $response['result'], '結果フラグが正しいことを確認');
        $this->assertArrayHasKey('success', $response, '成功レスポンスが含まれることを確認');
        $this->assertNotEmpty($response['success'], '成功レスポンスが配列であることを確認');
        $this->assertEmpty($response['error'], '失敗レスポンスが空であることを確認');
    }

    public function test_ajax_chat_send_validate_failed_with_invalid_message() {
        $this->_setRole( 'administrator' );

        // Invalid message structure - missing required 'text' field
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['messages'] = [['type' => 'text'], ['message' => ['invalid' => 'data']]];
        $_POST['audience'] = [['condition' => ['conditions' => [['type'=>'role', 'match'=>'role__in', 'role' => ['administrator']]]]]]; 
        $_POST['mode'] = 'validate';
        
        try {
            $this->_handleAjax('lc_ajax_chat_send');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertIsArray($response, 'レスポンスは配列であることを確認');
        $this->assertArrayHasKey('result', $response, '正しいキーが含まれることを確認');
        $this->assertEquals('failed', $response['result'], '結果フラグが正しいことを確認');
        $this->assertEmpty($response['success'], '成功レスポンスが空であることを確認');
        $this->assertNotEmpty($response['error'], '失敗レスポンスが配列であることを確認');
    }
}
