<?php

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Chat\API\FetchUsers;

class FetchUsersOnePageTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        // 必要なフックを登録
        add_action('wp_ajax_slc_fetch_users', [FetchUsers::class, 'ajax_fetch_users']);
        lineconnectTest::init();
    }

    public function test_ajax_fetch_users_success() {
        // ユーザーを作成しログイン
        $this->_setRole( 'administrator' );

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['channel_prefix'] = '04f7';
        // Ajax 呼び出しの実行
        try {
            $this->_handleAjax('slc_fetch_users');
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
        $this->assertTrue($response['success'], '結果フラグが正しいことを確認');
        $this->assertArrayHasKey('data', $response, '正しいキーが含まれることを確認');
        $this->assertArrayHasKey('users', $response['data'], 'usersが含まれることを確認');
        $this->assertNotEmpty($response['data']['users'], 'usersが空でないことを確認');
        $this->assertArrayHasKey('has_more', $response['data'], 'has_moreが含まれることを確認');
        $this->assertFalse($response['data']['has_more'], 'has_moreがfalseであることを確認');
        $this->assertArrayHasKey('next_cursor', $response['data'], 'next_cursorが含まれることを確認');
        $this->assertNull($response['data']['next_cursor'], 'next_cursorがnullであることを確認');
    }

    public function test_ajax_fetch_users_no_permission() {
        // ログインしていない状態
        wp_set_current_user(0);

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['channel_prefix'] = '04f7';
        // Ajax 呼び出しの実行
        try {
            $this->_handleAjax('slc_fetch_users');
        } catch (WPAjaxDieStopException $e) {
            // エラーではなく正常終了を確認
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            // その他の例外をキャッチ
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッジが空でないことを確認');
        }
        // レスポンスの検証
        $response = json_decode($this->_last_response, true);
        $this->assertFalse($response['success'], '成功レスポンスが空であることを確認');
        $this->assertArrayHasKey('data', $response, '正しいキーが含まれることを確認');
        $this->assertArrayHasKey('error', $response['data'], 'errorが含まれることを確認');
        $this->assertNotEmpty($response['data']['error'], 'errorが空でないことを確認');
    }

    public function test_ajax_fetch_users_no_channel_prefix() {
        $this->_setRole( 'administrator' );

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        // Ajax 呼び出しの実行
        try {
            $this->_handleAjax('slc_fetch_users');
        } catch (WPAjaxDieStopException $e) {
            // エラーではなく正常終了を確認
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            // その他の例外をキャッチ
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッジが空でないことを確認');
        }
        // レスポンスの検証
        $response = json_decode($this->_last_response, true);
        $this->assertFalse($response['success'], '成功レスポンスが空であることを確認');
        $this->assertArrayHasKey('data', $response, '正しいキーが含まれることを確認');
        $this->assertArrayHasKey('result', $response['data'], 'resultが含まれることを確認');
        $this->assertEquals($response['data']['result'], 'failed', 'resultがfailedであることを確認');
        $this->assertArrayHasKey('message', $response['data'], 'messageが含まれることを確認');
        $this->assertEquals($response['data']['message'], 'Channel prefix is required.', 'messageが正しいことを確認');
    }
}