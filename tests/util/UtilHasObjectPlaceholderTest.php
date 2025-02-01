<?php

class UtilHasObjectPlaceholderTest extends WP_UnitTestCase {
    protected static $result;

    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function test_detects_placeholder_in_string() {
        $input = 'Hello {{$.user.profile.displayName}}!';
        $result = lineconnectUtil::has_object_placeholder($input);
        $this->assertTrue($result);
    }

    public function test_no_placeholder_in_string() {
        $input = '通常の文字列';
        $result = lineconnectUtil::has_object_placeholder($input);
        $this->assertFalse($result);
    }

    public function test_nested_array_with_placeholder() {
        $input = [
            'user' => [
                'message' => '{{$.webhook.message.text}}',
                'data' => [123, true]
            ]
        ];
        $result = lineconnectUtil::has_object_placeholder($input);
        $this->assertTrue($result);
    }

    public function test_nested_array_without_placeholder() {
        $input = [
            'user' => [
                'message' => '通常のメッセージ',
                'data' => [123, true]
            ]
        ];
        $result = lineconnectUtil::has_object_placeholder($input);
        $this->assertFalse($result);
    }

    public function test_object_with_placeholder() {
        $obj = new stdClass();
        $obj->message = '{{$.webhook.message.text}}';
        $obj->data = [123, true];
        
        $result = lineconnectUtil::has_object_placeholder($obj);
        $this->assertTrue($result);
    }

    public function test_object_without_placeholder() {
        $obj = new stdClass();
        $obj->message = '通常のメッセージ';
        $obj->data = [123, true];
        
        $result = lineconnectUtil::has_object_placeholder($obj);
        $this->assertFalse($result);
    }

    public function test_message_builder_with_placeholder() {
        $input = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('{{$.user.profile.displayName}}');
        $result = lineconnectUtil::has_object_placeholder($input);
        $this->assertTrue($result);
    }

    public function test_message_builder_without_placeholder() {
        $input = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('通常のメッセージ');
        $result = lineconnectUtil::has_object_placeholder($input);
        $this->assertFalse($result);
    }

    public function test_empty_string() {
        $input = '';
        $result = lineconnectUtil::has_object_placeholder($input);
        $this->assertFalse($result);
    }

    public function test_null_value() {
        $input = null;
        $result = lineconnectUtil::has_object_placeholder($input);
        $this->assertFalse($result);
    }
}
