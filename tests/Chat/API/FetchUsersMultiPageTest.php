<?php

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Chat\API\FetchUsers;

class FetchUsersMultiPageTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        add_action('wp_ajax_slc_fetch_users', [FetchUsers::class, 'ajax_fetch_users']);
        lineconnectTest::init();
        // Truncate table
        global $wpdb;
        $table = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
        $wpdb->query("TRUNCATE TABLE {$table}");
        
        // 35件のダミーユーザーを登録
        global $wpdb;
        $table = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
        for ($i = 1; $i <= 35; $i++) {
            $wpdb->insert($table, [
                'channel_prefix' => '04f7',
                'line_id' => 'U' . str_pad($i, 32, '0', STR_PAD_LEFT),
                'follow' => 1,
                'profile' => json_encode([
                    'display_name' => 'User ' . $i,
                    'picture_url' => 'https://example.com/user' . $i . '.jpg',
                ]),
                'tags' => json_encode([])
            ]);
        }
    }

    public function test_ajax_fetch_users_with_pagination() {
        $this->_setRole('administrator');
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['channel_prefix'] = '04f7';

        // 1ページ目取得
        $first_response = $this->fetch_users();
       
        $this->assertTrue($first_response['success']);
        $this->assertCount(25, $first_response['data']['users']);
        $this->assertTrue($first_response['data']['has_more']);
        $this->assertNotNull($first_response['data']['next_cursor']);

        // 2ページ目取得
        $_POST['cursor'] = $first_response['data']['next_cursor'];
        $second_response = $this->fetch_users();
        // var_dump($second_response);
        $this->assertTrue($second_response['success']);
        $this->assertCount(10, $second_response['data']['users']);
        $this->assertFalse($second_response['data']['has_more']);
        $this->assertNull($second_response['data']['next_cursor']);

        // 全データの整合性チェック
        $all_users = array_merge(
            array_column($first_response['data']['users'], 'id'),
            array_column($second_response['data']['users'], 'id')
        );

        $this->assertCount(35, array_unique($all_users));
    }

    private function fetch_users() {
        // clear last_response
        $this->_last_response = '';

        try {
            $this->_handleAjax('slc_fetch_users');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            // その他の例外をキャッチ
            $this->assertNotNull($e->getMessage(), '例外が発生し、エラーメッセージが空でないことを確認');
        }
        // var_dump($this->_last_response);
        // var_dump(json_last_error_msg());
        return json_decode($this->_last_response, true);
    }
}
