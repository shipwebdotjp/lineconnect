<?php

use \Shipweb\LineConnect\Trigger\Webhook;

class WebhookPostBackParamSourceDateTimeTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_evaluate_date() {
        $source = array(
            'type' => 'date',
            'date' => array(
                'date' => '2023-10-26',
                'compare' => 'equals',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('date' => '2023-10-26')
        ), '日付が等しい');
    }
    //Timeを比較
    public function test_evaluate_time() {
        $source = array(
            'type' => 'time',
            'time' => array(
                'time' => '10:00',
                'compare' => 'equals',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('time' => '10:00')
        ), '時刻が等しい');
    }
    //DateTimeを比較
    public function test_evaluate_datetime() {
        $source = array(
            'type' => 'datetime',
            'datetime' => array(
                'datetime' => '2023-10-26T10:00:00',
                'compare' => 'equals',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('datetime' => '2023-10-26T10:00'),
        ), '日付時刻が等しい');
    }
}
