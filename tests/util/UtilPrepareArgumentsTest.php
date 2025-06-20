<?php

class UtilPrepareArgumentsTest extends WP_UnitTestCase {
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
                '0' => [
                    'name' => 'John Doe',
                    'address' => [
                        'city' => 'Tokyo'
                    ],
                    'id' => 123,
                    'name-with-dash' => 'Test-Name'
                ],
                '1' => array(
                    'datetime' => '2024-03-15 12:00:00'
                ),
                '2' => array(
                    'latitude' => '35.6895',
                    'longitude' => '139.6917'
                ),
                '3' => array(
                    'user_id' => '2'
                )
            )
        );
    }
    public function testIsEmpty()
    {
        $this->assertTrue(\Shipweb\LineConnect\Utilities\SimpleFunction::is_empty(null));
        $this->assertTrue(\Shipweb\LineConnect\Utilities\SimpleFunction::is_empty(''));
        $this->assertTrue(\Shipweb\LineConnect\Utilities\SimpleFunction::is_empty([]));
        
        $this->assertFalse(\Shipweb\LineConnect\Utilities\SimpleFunction::is_empty('0'));
        $this->assertFalse(\Shipweb\LineConnect\Utilities\SimpleFunction::is_empty(0));
        $this->assertFalse(\Shipweb\LineConnect\Utilities\SimpleFunction::is_empty('test'));
        $this->assertFalse(\Shipweb\LineConnect\Utilities\SimpleFunction::is_empty(['item']));
    }



    
    public function test_prepare_arguments()
    {
        $parameters = [
            'user_id' => '{{$.return.3.user_id}}',
            'key' => 'user_name'
        ];

        $parameters_schemas = array(
            array(
                'type' => 'integer',
                'name' => 'user_id',
                'title' => __('User ID', lineconnect::PLUGIN_NAME),
                'description' => __('WordPress user ID', lineconnect::PLUGIN_NAME),
                'required' => true,
            ),
            array(
                'type' => 'string',
                'name' => 'key',
                'title' => __('Key', lineconnect::PLUGIN_NAME),
                'description' => __('Meta key', lineconnect::PLUGIN_NAME),
                'required' => true,
            ),
        );

        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::prepare_arguments(
            $parameters,
            $parameters_schemas,
            $this->injection_data
        );

        $this->assertEquals('2', $result['user_id']);
        $this->assertEquals('user_name', $result['key']);
    }

    public function test_basic_placeholder_replacement() {
        $parameters = [
            'username' => '{{$.return.0.name}}',
            'city' => '{{$.return.0.address.city}}'
        ];

        $schema = [
            [
                'name' => 'username',
                'type' => 'string',
                'required' => true
            ],
            [
                'name' => 'city',
                'type' => 'string',
                'required' => true
            ]
        ];

        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::prepare_arguments($parameters, $schema, $this->injection_data);
        
        $this->assertEquals('John Doe', $result['username']);
        $this->assertEquals('Tokyo', $result['city']);
    }

    public function test_multiple_placeholders_in_string() {
        $parameters = [
            'message' => 'Hello {{$.return.0.name}} from {{$.return.0.address.city}}'
        ];

        $schema = [
            [
                'name' => 'message',
                'type' => 'string',
                'required' => true
            ]
        ];

        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::prepare_arguments($parameters, $schema, $this->injection_data);
        $this->assertEquals('Hello John Doe from Tokyo', $result['message']);
    }

    public function test_non_existent_placeholder() {
        $parameters = [
            'value' => '{{$.return.0.non_existent}}'
        ];

        $schema = [
            [
                'name' => 'value',
                'type' => 'string',
                'required' => true
            ]
        ];

        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::prepare_arguments($parameters, $schema, $this->injection_data);
        $this->assertEquals('', $result['value']);
    }

    public function test_special_characters_in_placeholder() {
        $parameters = [
            'value' => '{{$.return.0.name-with-dash}}'
        ];

        $schema = [
            [
                'name' => 'value',
                'type' => 'string',
                'required' => true
            ]
        ];

        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::prepare_arguments($parameters, $schema, $this->injection_data);
        $this->assertEquals('Test-Name', $result['value']);
    }

    public function test_missing_schema_name() {
        $parameters = [
            'param0' => '{{$.return.0.name}}'
        ];

        $schema = [
            [
                'type' => 'string',
                'required' => true
            ]
        ];

        $result = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::prepare_arguments($parameters, $schema, $this->injection_data);
        $this->assertEquals('John Doe', $result['param0']);
    }

}
