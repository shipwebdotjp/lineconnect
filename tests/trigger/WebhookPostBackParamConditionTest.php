<?php

use \Shipweb\LineConnect\Trigger\Webhook;

class WebhookPostBackParamConditionTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_evaluate_source_single_condition() {
        $source = array(
            'conditions' => array(
                array(
                    "type" => "source",
                    'source' => array(
                        'type' => 'status',
                        'status' => array(
                            'SUCCESS',
                        ),
                    )
                )
            )
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'SUCCESS')
        ), 'ステータスが等しい');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'FAILED')
        ), 'ステータスが等しくない');
    }

    public function test_evaluate_multiple_source_conditions_and_operator() {
        $source = array(
            'conditions' => array(
                array(
                    "type" => "source",
                    'source' => array(
                        'type' => 'status',
                        'status' => array('SUCCESS'),
                    )
                ),
                array(
                    "type" => "source",
                    'source' => array(
                        'type' => 'date',
                        'date' => array(
                            'date' => '2023-10-26',
                            'compare' => 'equals',
                        ),
                    )
                )
            ),
            'operator' => 'and'
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'SUCCESS', 'date' => '2023-10-26')
        ), 'AND: 両方の条件が真');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'SUCCESS', 'date' => '2023-10-27')
        ), 'AND: 日付条件が偽');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'FAILED', 'date' => '2023-10-26')
        ), 'AND: ステータス条件が偽');
    }

    public function test_evaluate_multiple_source_conditions_or_operator() {
        $source = array(
            'conditions' => array(
                array(
                    "type" => "source",
                    'source' => array(
                        'type' => 'status',
                        'status' => array('SUCCESS'),
                    )
                ),
                array(
                    "type" => "source",
                    'source' => array(
                        'type' => 'date',
                        'date' => array(
                            'date' => '2023-10-26',
                            'compare' => 'equals',
                        ),
                    )
                )
            ),
            'operator' => 'or'
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'SUCCESS', 'date' => '2023-10-26')
        ), 'OR: ステータス条件が真');
        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'SUCCESS', 'date' => '2023-10-27')
        ), 'OR: ステータス条件が真');
        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'FAILED', 'date' => '2023-10-26')
        ), 'OR: 日付条件が真');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'FAILED', 'date' => '2023-10-27')
        ), 'OR: 両方の条件が偽');
    }

    public function test_evaluate_condition_with_not_flag() {
        $source = array(
            'conditions' => array(
                array(
                    "type" => "source",
                    'source' => array(
                        'type' => 'status',
                        'status' => array('SUCCESS'),
                    ),
                    'not' => true
                )
            )
            // operator defaults to 'and'
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'FAILED')
        ), 'NOT: ステータスがSUCCESSでない (実際はFAILED)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'SUCCESS')
        ), 'NOT: ステータスがSUCCESSである');
    }

    public function test_evaluate_nested_group_conditions() {
        $source = array(
            'conditions' => array(
                array( // Outer condition 1
                    "type" => "source",
                    'source' => array(
                        'type' => 'status',
                        'status' => array('SUCCESS'),
                    )
                ),
                array( // Outer condition 2 (Group)
                    "type" => "group",
                    'condition' => array(
                        'conditions' => array(
                            array( // Inner condition 1
                                "type" => "source",
                                'source' => array(
                                    'type' => 'date',
                                    'date' => array('date' => '2023-10-26', 'compare' => 'equals'),
                                )
                            ),
                            array( // Inner condition 2
                                "type" => "source",
                                'source' => array(
                                    'type' => 'time',
                                    'time' => array('time' => '10:00', 'compare' => 'equals'),
                                )
                            )
                        ),
                        'operator' => 'or' // Inner group operator
                    )
                )
            ),
            'operator' => 'and' // Outer group operator
        );

        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'SUCCESS', 'date' => '2023-10-26', 'time' => '11:00')
        ), 'ネスト: 外側AND、内側OR (日付一致)');

        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'SUCCESS', 'date' => '2023-10-27', 'time' => '10:00')
        ), 'ネスト: 外側AND、内側OR (時刻一致)');

        $this->assertFalse(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'SUCCESS', 'date' => '2023-10-27', 'time' => '11:00')
        ), 'ネスト: 外側AND、内側OR (両方不一致)');

        $this->assertFalse(Webhook::check_webhook_message_postback_param_condition(
            $source,
            (object)array('status' => 'FAILED', 'date' => '2023-10-26', 'time' => '10:00')
        ), 'ネスト: 外側AND (ステータス不一致)');
    }

    public function test_evaluate_empty_conditions() {
        $source_and = array(
            'conditions' => array(),
            'operator' => 'and'
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source_and,
            (object)array('status' => 'SUCCESS')
        ), '空のconditions配列 (AND operator)');

        $source_or = array(
            'conditions' => array(),
            'operator' => 'or'
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_condition(
            $source_or,
            (object)array('status' => 'SUCCESS')
        ), '空のconditions配列 (OR operator)');
    }
}
