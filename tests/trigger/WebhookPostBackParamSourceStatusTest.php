<?php

use \Shipweb\LineConnect\Trigger\Webhook;

class WebhookPostBackParamSourceStatusTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_evaluate_in_single() {
        $source = array(
            'type' => 'status',
            'status' => array(
                'SUCCESS',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('status' => 'SUCCESS')
        ), 'ステータスが含まれる');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('status' => 'FAILED')
        ), 'ステータスが含まれない');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('status' => 'RICHMENU_NOTFOUND')
        ), 'ステータスが含まれない (RICHMENU_NOTFOUND)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array() // statusプロパティ自体がない場合
        ), 'ステータスプロパティが存在しない');

        $source_failed = array(
            'type' => 'status',
            'status' => array(
                'FAILED',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source_failed,
            (object)array('status' => 'FAILED')
        ), 'ステータスがFAILEDで一致');

        $source_richmenu_notfound = array(
            'type' => 'status',
            'status' => array(
                'RICHMENU_NOTFOUND',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source_richmenu_notfound,
            (object)array('status' => 'RICHMENU_NOTFOUND')
        ), 'ステータスがRICHMENU_NOTFOUNDで一致');

        $source_richmenu_alias_id_notfound = array(
            'type' => 'status',
            'status' => array(
                'RICHMENU_ALIAS_ID_NOTFOUND',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source_richmenu_alias_id_notfound,
            (object)array('status' => 'RICHMENU_ALIAS_ID_NOTFOUND')
        ), 'ステータスがRICHMENU_ALIAS_ID_NOTFOUNDで一致');
    }

    public function test_evaluate_in_multiple() {
        $source = array(
            'type' => 'status',
            'status' => array('SUCCESS', 'RICHMENU_NOTFOUND', 'FAILED'),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition($source, (object)array('status' => 'SUCCESS')), '複数指定: SUCCESSが含まれる');
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition($source, (object)array('status' => 'RICHMENU_NOTFOUND')), '複数指定: RICHMENU_NOTFOUNDが含まれる');
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition($source, (object)array('status' => 'FAILED')), '複数指定: FAILEDが含まれる');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition($source, (object)array('status' => 'RICHMENU_ALIAS_ID_NOTFOUND')), '複数指定: RICHMENU_ALIAS_ID_NOTFOUNDは含まれない');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition($source, (object)array('status' => 'UNKNOWN_STATUS')), '複数指定: UNKNOWN_STATUSは含まれない');
    }
}
