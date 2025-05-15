<?php

use \Shipweb\LineConnect\Trigger\Webhook;

class WebhookPostBackParamSourceRichMenuTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_evaluate_equals_newRichMenuAliasId() {
        $source = array(
            'type' => 'newRichMenuAliasId',
            'newRichMenuAliasId' => array(
                'newRichMenuAliasId' => 'test-id',
                'match' => 'equals',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'test-id')
        ), 'リッチメニューエイリアスが等しい');
        //リッチメニューエイリアスが異なるためFalseを返す
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'test-id-2')
        ), 'リッチメニューエイリアスが異なる');
        //リッチメニューエイリアスが渡されない場合はFalse
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array()
        ), 'リッチメニューエイリアスが存在しない');
    }

    //contains
    public function test_evaluate_contains_newRichMenuAliasId() {
        $source = array(
            'type' => 'newRichMenuAliasId',
            'newRichMenuAliasId' => array(
                'newRichMenuAliasId' => 'test-id',
                'match' => 'contains',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'test-id-1')
        ), 'リッチメニューエイリアスが含まれる (前方一致)');
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'another-test-id')
        ), 'リッチメニューエイリアスが含まれる (中間一致)');
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'prefix-test-id')
        ), 'リッチメニューエイリアスが含まれる (後方一致)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'completely-different')
        ), 'リッチメニューエイリアスが含まれない');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array()
        ), 'リッチメニューエイリアスが存在しない (contains)');
    }

    //startsWith
    public function test_evaluate_startsWith_newRichMenuAliasId() {
        $source = array(
            'type' => 'newRichMenuAliasId',
            'newRichMenuAliasId' => array(
                'newRichMenuAliasId' => 'test-prefix',
                'match' => 'startsWith',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'test-prefix-suffix')
        ), 'リッチメニューエイリアスが前方一致する');
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'test-prefix')
        ), 'リッチメニューエイリアスが完全に一致する (前方一致)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'another-test-prefix')
        ), 'リッチメニューエイリアスが前方一致しない (中間)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'suffix-test-prefix')
        ), 'リッチメニューエイリアスが前方一致しない (後方)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array()
        ), 'リッチメニューエイリアスが存在しない (startsWith)');
    }

    //endsWith
    public function test_evaluate_endsWith_newRichMenuAliasId() {
        $source = array(
            'type' => 'newRichMenuAliasId',
            'newRichMenuAliasId' => array(
                'newRichMenuAliasId' => 'test-suffix',
                'match' => 'endsWith',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'prefix-test-suffix')
        ), 'リッチメニューエイリアスが後方一致する');
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'test-suffix')
        ), 'リッチメニューエイリアスが完全に一致する (後方一致)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'test-suffix-another')
        ), 'リッチメニューエイリアスが後方一致しない (前方)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'test-suffix-middle-text')
        ), 'リッチメニューエイリアスが後方一致しない (中間)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array()
        ), 'リッチメニューエイリアスが存在しない (endsWith)');
    }

    //regexp
    public function test_evaluate_regexp_newRichMenuAliasId() {
        $source = array(
            'type' => 'newRichMenuAliasId',
            'newRichMenuAliasId' => array(
                'newRichMenuAliasId' => '^richmenu-alias-[0-9]+$', // richmenu-alias-数字 の形式
                'match' => 'regexp',
            ),
        );
        $this->assertTrue(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'richmenu-alias-123')
        ), 'リッチメニューエイリアスが正規表現に一致する');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'richmenu-alias-abc')
        ), 'リッチメニューエイリアスが正規表現に一致しない (末尾が数字でない)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'test-richmenu-alias-123')
        ), 'リッチメニューエイリアスが正規表現に一致しない (先頭が異なる)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array('newRichMenuAliasId' => 'richmenu-alias-123-extra')
        ), 'リッチメニューエイリアスが正規表現に一致しない (末尾に余分な文字)');
        $this->assertFalse(Webhook::check_webhook_message_postback_param_source_condition(
            $source,
            (object)array()
        ), 'リッチメニューエイリアスが存在しない (regexp)');
    }
}
