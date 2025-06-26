<?php

use Twig\Error\SyntaxError;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\Core\LineConnect;

class UtilReplaceTest extends WP_UnitTestCase {
    protected static $result;
    protected $injection_data;
    public static function wpSetUpBeforeClass($factory) {
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
                '3' => array(
                    array(
                        'user_id' => '2',
                        'name' => 'テストユーザー2',
                    ),
                    array(
                        'user_id' => '3',
                        'name' => 'テストユーザー3',
                    ),
                ),
                '4' => new \Twig\Error\SyntaxError('Error message here'),
            )
        );
    }

    public function test_simple_string_replacement_with_doller() {
        $input = '{{$.user.profile.displayName}}さん';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーさん', $result);
    }

    public function test_simple_string_replacement_without_doller() {
        $input = '{{user.profile.displayName}}さん';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーさん', $result);
    }

    public function test_simple_string_replacement_with_doller_and_noexistkey() {
        $input = '{{$.user.profile.displayName}}さん{{$.webhook.message.noexistkey}}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーさん', $result);
    }

    //2つのブラケットを含む文字列のテスト
    public function test_double_bracket_replacement_with_doller() {
        $input = '{{$.user.profile.displayName}}さんの{{$.webhook.message.text}}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーさんのテストメッセージ', $result);
    }

    public function test_double_bracket_replacement_without_doller() {
        $input = '{{user.profile.displayName}}さんの{{webhook.message.text}}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーさんのテストメッセージ', $result);
    }

    public function test_array_replacement_with_doller() {
        $input = array(
            'message' => '{{$.user.profile.displayName}}へ',
            'data' => array(
                'time' => '{{$.return.1.datetime}}'
            )
        );
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーへ', $result['message']);
        $this->assertEquals('2024-03-15 12:00:00', $result['data']['time']);
    }

    public function test_array_replacement_without_doller() {
        $input = array(
            'message' => '{{user.profile.displayName}}へ',
            'data' => array(
                'time' => '{{return.1.datetime}}'
            )
        );
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーへ', $result['message']);
        $this->assertEquals('2024-03-15 12:00:00', $result['data']['time']);
    }

    public function test_direct_from_nested_array_with_doller() {
        $input = '{{$.return[3].0.name}}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2', $result);
        $input = '{{$.return[3][0].name}}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2', $result);
        $input = '{{$.return[3][0][name]}}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2', $result);
    }

    public function test_string_from_nested_array_with_doller() {
        $input = '{{$.return[3].0.name}}さん';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2さん', $result);
        $input = '{{$.return[3][0].name}}さん';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2さん', $result);
        $input = '{{$.return[3][0]["name"]}}さん';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2さん', $result);
    }

    public function test_string_from_nested_array_without_doller() {
        $input = '{{return[3].0.name}}さん';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2さん', $result);
        $input = '{{return[3][0].name}}さん';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2さん', $result);
        $input = '{{return[3][0]["name"]}}さん';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2さん', $result);
    }

    public function test_get_object_from_nested_array_with_doller() {
        $input = '{{$.return[4]}}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertInstanceOf(Twig\Error\SyntaxError::class, $result); //オブジェクトインスタンスが返されることを確認
    }

    public function test_object_replacement() {
        $obj = new stdClass();
        $obj->name = '{{$.user.profile.displayName}}';
        $obj->message = new stdClass();
        $obj->message->text = '{{$.webhook.message.text}}';

        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($obj, $this->injection_data);
        $this->assertEquals('テストユーザー', $result->name);
        $this->assertEquals('テストメッセージ', $result->message->text);
    }

    public function test_no_placeholder() {
        $input = 'プレースホルダーなし';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('プレースホルダーなし', $result);
    }

    public function test_invalid_placeholder() {
        $input = '{{$.invalid.path}}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('', $result);
    }

    public function test_empty_placeholder() {
        $input = '{{}}';
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            // PHP 8.1 以降: Twig が SyntaxError をスローすることを期待
            $this->expectException(SyntaxError::class);
            // オプション: 特定の例外メッセージを期待する場合
            // $this->expectExceptionMessage('Unexpected token "end of print statement" of value "" in "template" at line 1.');

            // 例外がスローされるはずのコードを実行
            \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);

            // expectException を設定した場合、この行以降は実行されないはず
            // (例外が発生しなかった場合にテストが失敗することを示すためにコメントアウトしておく)
            // $this->fail('SyntaxError was not thrown for empty placeholder on PHP >= 8.1');

        } else {
            // PHP 8.0 以前: legacy_replace_injection_data が呼び出され、
            // パスが見つからず null を返すことを期待
            $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
            $this->assertNull($result, 'Expected null for empty placeholder on PHP <= 8.0');
        }
    }

    public function test_null_placeholder() {
        $input = null;
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertNull($result);
    }

    public function test_no_exist_key_placeholder() {
        $input = 'これは{{$.webhook.message.noexistkey}}存在しません';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('これは存在しません', $result);
        $input = 'これは{{webhook.message.noexistkey}}存在しません';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('これは存在しません', $result);
    }

    public function test_text_message_replacement() {
        $input = Builder::createTextMessage('{{$.user.profile.displayName}}さんこんにちは!');
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザーさんこんにちは!', $result[0]['text']);
    }

    public function test_location_message_replacement() {
        $input = Builder::createLocationMessage('テストタイトル', 'テストアドレス', '{{$.return.2.latitude}}', '{{$.return.2.longitude}}');
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストタイトル', $result[0]['title']);
        $this->assertEquals('テストアドレス', $result[0]['address']);
        $this->assertEquals(35.6895, $result[0]['latitude']);
        $this->assertEquals(139.6917, $result[0]['longitude']);
    }

    public function test_time_format() {
        $input = '{{return.1.datetime|date("Y/m/d H:i:s")}}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('2024/03/15 12:00:00', $result);
    }

    public function test_loop_users() {
        $input = '{% for user in return[3] %}{{user.name}}さん、{% endfor %}';
        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($input, $this->injection_data);
        $this->assertEquals('テストユーザー2さん、テストユーザー3さん、', $result);
    }
}
