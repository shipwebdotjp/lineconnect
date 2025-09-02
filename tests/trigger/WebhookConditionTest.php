<?php

use \Shipweb\LineConnect\Trigger\Webhook;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Class WebhookConditionTest
 *
 * @package LineConnect\Tests\Trigger
 */
class WebhookConditionTest extends WP_UnitTestCase {
    /**
     * @var mixed Initialized test result
     */
    protected static $result;

    /**
     * Set up before running any test in the class.
     *
     * @param WP_UnitTest_Factory $factory
     */
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    /**
     * Set up before each test.
     */
    public function setUp(): void {
        parent::setUp();
    }

    public function test_evaluate_empty_condition() {
        $source = array();
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID1ccdbac80ea15')),
                '2f38'
            )
        );
        $source = array(
            'conditions' => array()
        );
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID1ccdbac80ea15')),
                '2f38'
            )
        );
    }


    /**
     * Test evaluating channel single condition.
     */
    public function test_evaluate_channel_single_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'channel',
                    'secret_prefix' => array(
                        '04f7'
                    )
                )
            )
        );

        // matching prefix
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array(),
                '04f7'
            ),
            'チャネルプリフィックスが等しい'
        );

        // non-matching prefix
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array(),
                'another'
            ),
            'プリフィックスが異なる'
        );
    }

    /**
     * Test evaluating source group and room single conditions.
     */
    public function test_evaluate_source_group_and_room_single_condition() {
        // groupId condition
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'group',
                        'groupId' => array(
                            'group-id',
                            'another-group-id'
                        ),
                    )
                )
            )
        );

        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'group', 'groupId' => 'group-id', 'userId' => 'U4af4980629')),
                '04f7'
            ),
            'グループIDが含まれる'
        );

        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'group', 'groupId' => 'no-group-id', 'userId' => 'U4af4980629')),
                'another'
            ),
            'グループIDが含まれない'
        );

        // roomId condition
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'room',
                        'roomId' => array(
                            'room-id',
                            'another-room-id'
                        ),
                    )
                )
            )
        );

        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'room', 'roomId' => 'room-id', 'userId' => 'U4af4980629')),
                '04f7'
            ),
            'ルームIDが含まれる'
        );

        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'room', 'roomId' => 'no-room-id', 'userId' => 'U4af4980629')),
                'another'
            ),
            'ルームIDが含まれない'
        );
    }

    /**
     * Test evaluating source user single condition.
     */
    public function test_evaluate_source_user_single_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'userId' => array(
                            'user-id',
                            'another-user-id'
                        ),
                    )
                )
            )
        );

        // userId matches
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'user-id')),
                '04f7'
            )
        );

        // userId does not match
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'no-user-id')),
                'another'
            )
        );
    }

    public function test_evaluate_source_user_negative_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'userId' => array(
                            'user-id',
                            'another-user-id'
                        ),
                    ),
                    'not' => true,
                )
            )
        );

        // userId matches
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'user-id')),
                '04f7'
            )
        );

        // userId does not match
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'no-user-id')),
                'another'
            )
        );
    }

    /**
     * Test evaluating source user link condition.
     */
    public function test_evaluate_source_user_link_single_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'link' => 'any',
                    )
                )
            )
        );
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1')),
                '2f38'
            )
        );
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'link' => 'linked',
                    )
                )
            )
        );
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1')),
                '2f38'
            )
        );
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'link' => 'unlinked',
                    )
                )
            )
        );
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1')),
                '2f38'
            )
        );
    }

    /**
     * Test evaluating source user role condition.
     */
    public function test_evaluate_source_user_role_single_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'role' => array('administrator', 'subscriber'),
                    )
                )
            )
        );
        // administrator role matches
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            ),
            'administrator role matches'
        );
        // subscriber role matches
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERIDc3f457cdefcc9')),
                '04f7'
            ),
            'subscriber role matches'
        );
        // role does not match (teacher, student)
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID1ccdbac80ea15')),
                '2f38'
            ),
            'role does not match'
        );
    }

    /**
     * Test evaluating usermeta equals condition.
     */
    public function test_evaluate_source_user_usermeta_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'usermeta' => array(
                            array(
                                'key' => '性別',
                                'value' => '男性',
                                'compare' => '='
                            ),
                        ),
                    ),
                ),
            ),
        );

        // usermeta matches
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );

        // usermeta does not match
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID1ccdbac80ea15')),
                '2f38'
            )
        );
    }

    /**
     * Test evaluating usermeta exists condition.
     */
    public function test_evaluate_source_user_usermeta_exists_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'usermeta' => array(
                            array(
                                'key' => '配信停止フラグ',
                                'compare' => 'EXISTS'
                            ),
                        ),
                    ),
                ),
            ),
        );

        // usermeta exists
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERIDc3f457cdefcc9')),
                '04f7'
            )
        );

        // usermeta does not exist
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );
    }

    /**
     * Test evaluating user profile equals condition.
     */
    public function test_evaluate_source_user_profile_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'profile' => array(
                            array(
                                'key' => '性別',
                                'value' => '女性',
                                'compare' => '='
                            ),
                        ),
                    ),
                ),
            ),
        );

        // profile matches
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1')),
                '2f38'
            )
        );

        // profile does not match
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );
    }

    /**
     * Test evaluating user profile exists condition.
     */
    public function test_evaluate_source_user_profile_exists_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'profile' => array(
                            array(
                                'key' => '好きな色',
                                'compare' => 'EXISTS'
                            ),
                        ),
                    ),
                ),
            ),
        );

        // profile exists
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );

        // profile does not exist
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERIDc3f457cdefcc9')),
                '04f7'
            )
        );
    }

    /**
     * Test evaluating usermeta and profile combined equals condition.
     */
    public function test_evaluate_source_user_usermeta_and_profile_combined_equals_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'usermeta' => array(
                            array(
                                'key' => '性別',
                                'value' => '男性',
                                'compare' => '='
                            ),
                        ),
                        'profile' => array(
                            array(
                                'key' => '性別',
                                'value' => '男性',
                                'compare' => '='
                            ),
                        ),
                    ),
                ),
            ),
        );

        // both usermeta and profile match
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );

        // either usermeta or profile does not match
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID1ccdbac80ea15')),
                '2f38'
            )
        );
    }

    /**
     * Test evaluating usermeta and profile combined exists condition.
     */
    public function test_evaluate_source_user_usermeta_and_profile_combined_exists_condition() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'usermeta' => array(
                            array(
                                'key' => '最終購入日',
                                'compare' => 'EXISTS'
                            ),
                        ),
                        'profile' => array(
                            array(
                                'key' => '最終購入日',
                                'compare' => 'EXISTS'
                            ),
                        ),
                    ),
                ),
            ),
        );

        // both exist
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );

        // either usermeta or profile does not exist
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERIDc3f457cdefcc9')),
                '04f7'
            )
        );
    }

    /**
     * Test evaluating multiple conditions default operator.
     */
    public function test_evaluate_multiple_conditions_default_operator() {
        $source = array(
            'conditions' => array(
                array(
                    'type' => 'channel',
                    'secret_prefix' => array('04f7')
                ),
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'userId' => array('U_PLACEHOLDER_USERID4e7a9902e5e7d')
                    )
                )
            )
        );

        // both channel and source match
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );

        // channel matches but source does not
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1')),
                '04f7'
            )
        );
    }

    /**
     * Test evaluating multiple conditions with explicit AND operator.
     */
    public function test_evaluate_multiple_conditions_and_operator() {
        $source = array(
            'operator' => 'and',
            'conditions' => array(
                array(
                    'type' => 'channel',
                    'secret_prefix' => array('04f7')
                ),
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'userId' => array('U_PLACEHOLDER_USERID4e7a9902e5e7d')
                    )
                )
            )
        );

        // both match
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                '04f7'
            )
        );

        // one fails
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1')),
                '04f7'
            )
        );
    }

    /**
     * Test evaluating multiple conditions with OR operator.
     */
    public function test_evaluate_multiple_conditions_or_operator() {
        $source = array(
            'operator' => 'or',
            'conditions' => array(
                array(
                    'type' => 'channel',
                    'secret_prefix' => array('04f7')
                ),
                array(
                    'type' => 'source',
                    'source' => array(
                        'type' => 'user',
                        'userId' => array('U_PLACEHOLDER_USERID4e7a9902e5e7d')
                    )
                )
            )
        );

        // channel only match
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1')),
                '04f7'
            )
        );

        // source only match
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d')),
                'wrong'
            )
        );

        // none match
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object) array('source' => (object) array('type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1')),
                'wrong'
            )
        );
    }

    /**
     * Test evaluating grouped conditions default operator.
     */
    public function test_evaluate_grouped_conditions_default_operator() {
        $source = [
            'conditions' => [
                [
                    'type'      => 'group',
                    'condition' => [
                        'conditions' => [
                            ['type' => 'channel', 'secret_prefix' => ['04f7']],
                            ['type' => 'source', 'source' => ['type' => 'user', 'userId' => ['U_PLACEHOLDER_USERID4e7a9902e5e7d']]]
                        ]
                    ]
                ]
            ]
        ];

        // both match (AND)
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d']],
                '04f7'
            )
        );

        // one fails
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1']],
                '04f7'
            )
        );
    }

    /**
     * Test evaluating grouped conditions with OR operator.
     */
    public function test_evaluate_grouped_conditions_or_operator() {
        $source = [
            'operator'   => 'or',
            'conditions' => [
                [
                    'type'      => 'group',
                    'condition' => [
                        'operator'   => 'or',
                        'conditions' => [
                            ['type' => 'channel', 'secret_prefix' => ['04f7']],
                            ['type' => 'source', 'source' => ['type' => 'user', 'userId' => ['U_PLACEHOLDER_USERID4e7a9902e5e7d']]]
                        ]
                    ]
                ]
            ]
        ];

        // channel only matches
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1']],
                '04f7'
            )
        );

        // source only matches
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d']],
                'wrong'
            )
        );

        // none match
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1']],
                'wrong'
            )
        );
    }

    /**
     * Test evaluating mixed root conditions default operator.
     */
    public function test_evaluate_mixed_root_conditions_default_operator() {
        $source = [
            'conditions' => [
                ['type' => 'channel', 'secret_prefix' => ['04f7']],
                [
                    'type'      => 'group',
                    'condition' => [
                        'conditions' => [
                            [
                                'type'   => 'source',
                                'source' => ['type' => 'user', 'userId' => ['U_PLACEHOLDER_USERID4e7a9902e5e7d']]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // both channel and nested source match
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d']],
                '04f7'
            )
        );

        // channel matches but nested source does not
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1']],
                '04f7'
            )
        );
    }

    /**
     * Test evaluating mixed root conditions OR operator.
     */
    public function test_evaluate_mixed_root_conditions_or_operator() {
        $source = [
            'operator'   => 'or',
            'conditions' => [
                ['type' => 'channel', 'secret_prefix' => ['04f7']],
                [
                    'type'      => 'group',
                    'condition' => [
                        'conditions' => [
                            [
                                'type'   => 'source',
                                'source' => ['type' => 'user', 'userId' => ['U_PLACEHOLDER_USERID4e7a9902e5e7d']]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // channel only match
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1']],
                '04f7'
            )
        );

        // nested source only match
        $this->assertTrue(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d']],
                'wrong'
            )
        );

        // none match
        $this->assertFalse(
            Webhook::check_webhook_condition(
                $source,
                (object)['source' => (object)['type' => 'user', 'userId' => 'U_PLACEHOLDER_USERID4123a772125a1']],
                'wrong'
            )
        );
    }
}
