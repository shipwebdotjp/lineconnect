<?php

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Chat\API\FetchMessages;

class FetchMessagesMultiPageTest extends WP_Ajax_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        add_action('wp_ajax_slc_fetch_messages', [FetchMessages::class, 'execute']);
        lineconnectTest::init();
        
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS;
        $wpdb->query("TRUNCATE TABLE {$table_name}");

        // 105件のダミーメッセージ生成（INBOUNDとOUTBOUND混在）
        $base_time = time() - 105;
        $values = [];
        for ($i = 1; $i <= 105; $i++) {
            $timestamp = date('Y-m-d H:i:s', $base_time + $i) . '.' . str_pad($i%1000, 3, '0', STR_PAD_LEFT);
            $values[] = $wpdb->prepare(
                '(%s, %d, %d, %s, %s, %d, %s, %s)',
                'INBOUND_' . md5($i),
                $i % 2 + 1, // event_type: 1 or 2
                $i % 3 + 1, // source_type: 1-3
                'U' . str_pad(1, 32, '0', STR_PAD_LEFT), // 全メッセージ同一ユーザーID
                '04f7',
                $i % 2 + 1, // message_type: 1 or 2
                json_encode(['text' => "Message $i"]),
                $timestamp
            );
        }

        // バルクインサート
        $wpdb->query(
            "INSERT INTO {$table_name} 
            (event_id, event_type, source_type, user_id, bot_id, message_type, message, timestamp)
            VALUES " . implode(', ', $values)
        );
    }

    public function test_ajax_fetch_messages_pagination() {
        $this->_setRole('administrator');
        $_POST['nonce'] = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $_POST['user_id'] = 'U' . str_pad(1, 32, '0', STR_PAD_LEFT);
        $_POST['channel_prefix'] = '04f7';

        // 1ページ目（100件）
        $first_response = $this->fetch_messages();
        $this->assertTrue($first_response['success']);
        $this->assertCount(100, $first_response['data']['messages']);
        $this->assertTrue($first_response['data']['has_more']);
        $this->assertNotNull($first_response['data']['next_cursor']);

        // 2ページ目（5件）
        $_POST['cursor'] = $first_response['data']['next_cursor'];
        $second_response = $this->fetch_messages();
        $this->assertTrue($second_response['success']);
        $this->assertCount(5, $second_response['data']['messages']);
        $this->assertFalse($second_response['data']['has_more']);
        $this->assertNull($second_response['data']['next_cursor']);

        // 全データ整合性チェック
        $all_messages = array_merge(
            array_column($first_response['data']['messages'], 'id'),
            array_column($second_response['data']['messages'], 'id')
        );
        $this->assertCount(105, array_unique($all_messages));
    }

    private function fetch_messages() {
        $this->_last_response = '';
        try {
            $this->_handleAjax('slc_fetch_messages');
        } catch (WPAjaxDieStopException $e) {
            $this->assertEquals('', $e->getMessage());
        } catch (WPAjaxDieContinueException $e) {
            $this->assertNotNull($e->getMessage());
        }
        return json_decode($this->_last_response, true);
    }
}
