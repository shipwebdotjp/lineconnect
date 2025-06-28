<?php

/**
 * Class AjaxTest
 *
 * @package LineConnect
 */

use \Shipweb\LineConnect\ActionFlow\ActionFlow;
use Shipweb\LineConnect\Core\LineConnect;


class ActionFlowAjaxChatSendTest extends WP_Ajax_UnitTestCase {
    protected static $result;
    protected static $inserted_actionflows;
    protected static $actionflows_data;


    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();

        self::$inserted_actionflows = array();
        self::$actionflows_data = array(
            'actionflow_1' => array(
                array(
                    'actions' => array(
                        array(
                            'action_name' => 'get_text_message',
                            'parameters' => array(
                                'body' => 'Hello World'
                            ),
                            'response_return_value' => true,
                        )
                    ),
                    'chains' => array()
                )
            )
        );

        foreach (self::$actionflows_data as $actionflow_name => $actionflow_data) {
            $post_id = wp_insert_post(
                array(
                    'post_title' => $actionflow_name,
                    'post_type' => ActionFlow::POST_TYPE,
                    'post_status' => 'publish',
                )
            );
            update_post_meta($post_id, ActionFlow::META_KEY_DATA, $actionflow_data);
            update_post_meta(
                $post_id,
                lineconnect::META_KEY__SCHEMA_VERSION,
                ActionFlow::SCHEMA_VERSION
            );

            self::$inserted_actionflows[] = $post_id;
        }
    }

    public static function wpTearDownAfterClass() {
        foreach (self::$inserted_actionflows as $post_id) {
            wp_delete_post($post_id, true);
        }
    }

    public function setUp(): void {
        parent::setUp();
        $_POST = [];
        add_action('wp_ajax_lc_ajax_get_slc_actionflow', [\Shipweb\LineConnect\ActionFlow\ActionFlow::class, 'ajax_get_actionflow']);
    }

    public function tearDown(): void {
        parent::tearDown();
        remove_action('wp_ajax_lc_ajax_get_slc_actionflow', [\Shipweb\LineConnect\ActionFlow\ActionFlow::class, 'ajax_get_actionflow']);
    }

    public function test_ajax_ajax_get_actionflow_success() {
        // Adminでログイン
        $this->_setRole('administrator');

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['post_id'] = self::$inserted_actionflows[0];

        // Ajax 呼び出しの実行
        try {
            $this->_handleAjax('lc_ajax_get_slc_actionflow');
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
        $this->assertArrayHasKey('formData', $response, '成功レスポンスが含まれることを確認');
        $this->assertNotEmpty($response['formData'], '成功レスポンスのformDataが配列であることを確認');
        $this->assertEmpty($response['error'], '失敗レスポンスが空であることを確認');
    }

    public function test_ajax_ajax_get_actionflow_failed_no_permission() {
        // ログインしていない状態
        wp_set_current_user(0);

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['post_id'] = self::$inserted_actionflows[0];

        // Ajax 呼び出しの実行
        try {
            $this->_handleAjax('lc_ajax_get_slc_actionflow');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }

        // レスポンスの検証
        $response = json_decode($this->_last_response, true);
        $this->assertEquals('failed', $response['result'], '結果フラグが正しいことを確認');
        $this->assertEmpty($response['formData'], '成功レスポンスが空であることを確認');
    }

    public function test_ajax_ajax_get_actionflow_failed_no_post_id() {
        // Adminでログイン
        $this->_setRole('administrator');

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);

        // Ajax 呼び出しの実行
        try {
            $this->_handleAjax('lc_ajax_get_slc_actionflow');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないこを確認');
        }

        // レスポンスの検証
        $response = json_decode($this->_last_response, true);
        $this->assertEquals('failed', $response['result'], '結果フラグが正しいことを確認');
        $this->assertEmpty($response['formData'], '成功レスポンスが空であることを確認');
    }

    public function test_ajax_ajax_get_actionflow_failed_invalid_post_id() {
        // Adminでログイン
        $this->_setRole('administrator');

        // リクエストデータを設定
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['post_id'] = 999999;

        // Ajax 呼び出しの実行
        try {
            $this->_handleAjax('lc_ajax_get_slc_actionflow');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないこを確認');
        }

        // レスポンスの検証
        $response = json_decode($this->_last_response, true);
        $this->assertEquals('success', $response['result'], '結果フラグが正しいことを確認');
        $this->assertEmpty($response['formData'], '成功レスポンスが空であることを確認');
    }
}
