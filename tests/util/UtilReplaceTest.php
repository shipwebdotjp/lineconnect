<?php

class UtilReplaceTest extends WP_UnitTestCase {
    protected static $result;
    protected $injection_data;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
        $this->injection_data = array(
            'user' => array(
                'profile' => array(
                    'displayName' => 'テストユーザー',
                    'pictureUrl' => 'https://example.com/photo.jpg'
                )
            ),
            'webhook' => array(
                'message' => array(
                    'text' => 'テストメッセージ'
                )
            ),
            'return' => array(
                '1' => array(
                    'datetime' => '2024-03-15 12:00:00'
                ),
                '2' => array(
                    'latitude' => '35.6895',
                    'longitude' => '139.6917'
                ),
            )
        );
    }

    public function test_simple_string_replacement() {
        $input = '{{$.user.profile.displayName}}さん';
        $result = lineconnectUtil::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーさん', $result);
    }

    public function test_array_replacement() {
        $input = array(
            'message' => '{{$.user.profile.displayName}}へ',
            'data' => array(
                'time' => '{{$.return.1.datetime}}'
            )
        );
        $result = lineconnectUtil::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーへ', $result['message']);
        $this->assertEquals('2024-03-15 12:00:00', $result['data']['time']);
    }

    public function test_object_replacement() {
        $obj = new stdClass();
        $obj->name = '{{$.user.profile.displayName}}';
        $obj->message = new stdClass();
        $obj->message->text = '{{$.webhook.message.text}}';
        
        $result = lineconnectUtil::replace_object_placeholder($obj, $this->injection_data);
        $this->assertEquals('テストユーザー', $result->name);
        $this->assertEquals('テストメッセージ', $result->message->text);
    }

    public function test_no_placeholder() {
        $input = 'プレースホルダーなし';
        $result = lineconnectUtil::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('プレースホルダーなし', $result);
    }

    public function test_invalid_placeholder() {
        $input = '{{$.invalid.path}}';
        $result = lineconnectUtil::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('', $result);
    }

    public function test_empty_placeholder() {
        $input = '{{}}';
        $result = lineconnectUtil::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('', $result);
    }

    public function test_null_placeholder() {
        $input = null;
        $result = lineconnectUtil::replace_object_placeholder($input, $this->injection_data);
        $this->assertNull($result);
    }

    public function test_no_exist_key_placeholder() {
        $input = 'これは{{$.webhook.message.noexistkey}}存在しません';
        $result = lineconnectUtil::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('これは存在しません', $result);
    }

    public function test_text_message_replacement() {
        $input = lineconnectMessage::createTextMessage('{{$.user.profile.displayName}}さんこんにちは!');
        $result = lineconnectUtil::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーさんこんにちは!', $result[0]['text']);
    }

    public function test_location_message_replacement() {
        $input = lineconnectMessage::createLocationMessage('テストタイトル', 'テストアドレス', '{{$.return.2.latitude}}', '{{$.return.2.longitude}}');
        $result = lineconnectUtil::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストタイトル', $result[0]['title']);
        $this->assertEquals('テストアドレス', $result[0]['address']);
        $this->assertEquals(35.6895, $result[0]['latitude']);
        $this->assertEquals(139.6917, $result[0]['longitude']);
    }
}