<?php

class UtilInjectParamTest extends WP_UnitTestCase {
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
                '3' => array(
                    'user_id' => '2'
                )
            )
        );
    }    public function test_inject_param()
    {
        $action_idx = 0;
        $action_parameters = [
            'name' => 'John',
            'details' => [
                'age' => 25
            ]
        ];
        
        $chains = [
            [
                'to' => '1.name',
                'data' => '{{$.user.profile.displayName}}'
            ],
            [
                'to' => '1.details.age',
                'data' => 30
            ]
        ];

        $result = lineconnectUtil::inject_param($action_idx, $action_parameters, $chains);
        
        $this->assertEquals('{{$.user.profile.displayName}}', $result['name']);
        $this->assertEquals(30, $result['details']['age']);
    }


}