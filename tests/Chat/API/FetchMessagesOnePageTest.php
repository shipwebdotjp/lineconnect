<?php

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Chat\API\FetchMessages;

class FetchMessagesOnePageTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        // 必要なフックを登録
        add_action('wp_ajax_slc_fetch_messages', [FetchMessages::class, 'execute']);
        lineconnectTest::init();
    }

    public function test_ajax_fetch_messages_success() {
        // 管理者ユーザーを作成しログイン
        $this->_setRole('administrator');

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['user_id'] = 'test_user_001';
        $_POST['channel_prefix'] = '04f7';

        // Ajax呼び出し実行
        try {
            $this->_handleAjax('slc_fetch_messages');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            /**
             * Exception for cases of wp_die(), for ajax tests.
             * This means execution of the ajax function should be halted, but the unit
             * test can continue.  The function finished normally and there was not an
             * error (output happened, but wp_die was called to end execution)  This is
             * used with WP_Ajax_Response::send
             *
             * @package    WordPress
             * @subpackage Unit Tests
             * @since      3.4.0
             */
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        // レスポンス検証
        $response = json_decode($this->_last_response, true);
        $this->assertIsArray($response, 'レスポンスが配列であることを確認');
        $this->assertTrue($response['success'], '成功フラグがtrueであることを確認');
        $this->assertArrayHasKey('data', $response, 'dataキーが存在することを確認');
        $this->assertArrayHasKey('messages', $response['data'], 'messagesが含まれることを確認');
        $this->assertArrayHasKey('has_more', $response['data'], 'has_moreが含まれることを確認');
        $this->assertFalse($response['data']['has_more'], 'has_moreがfalseであることを確認');
        $this->assertArrayHasKey('next_cursor', $response['data'], 'next_cursorが含まれることを確認');
        $this->assertNull($response['data']['next_cursor'], 'next_cursorがnullであることを確認');
    }

    public function test_ajax_fetch_messages_no_user_id() {
        $this->_setRole('administrator');

        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['channel_prefix'] = '04f7';

        try {
            $this->_handleAjax('slc_fetch_messages');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        }catch (WPAjaxDieContinueException $e) {
            /**
             * Exception for cases of wp_die(), for ajax tests.
             * This means execution of the ajax function should be halted, but the unit
             * test can continue.  The function finished normally and there was not an
             * error (output happened, but wp_die was called to end execution)  This is
             * used with WP_Ajax_Response::send
             *
             * @package    WordPress
             * @subpackage Unit Tests
             * @since      3.4.0
             */
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertFalse($response['success'], '成功フラグがfalseであることを確認');
        $this->assertArrayHasKey('data', $response, 'dataキーが存在することを確認');
        $this->assertArrayHasKey('message', $response['data'], 'エラーメッセージが含まれることを確認');
        $this->assertEquals('User ID is required.', $response['data']['message'], '正しいエラーメッセージを確認');
    }

    public function test_ajax_fetch_messages_no_channel_prefix() {
        $this->_setRole('administrator');

        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['user_id'] = 'test_user_001';

        try {
            $this->_handleAjax('slc_fetch_messages');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        }catch (WPAjaxDieContinueException $e) {
            /**
             * Exception for cases of wp_die(), for ajax tests.
             * This means execution of the ajax function should be halted, but the unit
             * test can continue.  The function finished normally and there was not an
             * error (output happened, but wp_die was called to end execution)  This is
             * used with WP_Ajax_Response::send
             *
             * @package    WordPress
             * @subpackage Unit Tests
             * @since      3.4.0
             */
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertFalse($response['success'], '成功フラグがfalseであることを確認');
        $this->assertArrayHasKey('data', $response, 'dataキーが存在することを確認');
        $this->assertArrayHasKey('message', $response['data'], 'エラーメッセージが含まれることを確認');
        $this->assertEquals('Channel prefix is required.', $response['data']['message'], '正しいエラーメッセージを確認');
    }

    public function test_ajax_fetch_messages_no_permission() {
        // 未ログイン状態
        wp_set_current_user(0);

        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['user_id'] = 'test_user_001';
        $_POST['channel_prefix'] = '04f7';

        try {
            $this->_handleAjax('slc_fetch_messages');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            /**
             * Exception for cases of wp_die(), for ajax tests.
             * This means execution of the ajax function should be halted, but the unit
             * test can continue.  The function finished normally and there was not an
             * error (output happened, but wp_die was called to end execution)  This is
             * used with WP_Ajax_Response::send
             *
             * @package    WordPress
             * @subpackage Unit Tests
             * @since      3.4.0
             */
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        $response = json_decode($this->_last_response, true);
        $this->assertFalse($response['success'], '権限エラーが発生することを確認');
        $this->assertArrayHasKey('data', $response, 'dataキーが存在することを確認');
        $this->assertArrayHasKey('error', $response['data'], 'エラー情報が含まれることを確認');
    }
}
