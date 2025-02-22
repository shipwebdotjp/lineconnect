<?php

use \Shipweb\LineConnect\Utilities\Condition;

class UtilConditionChannelTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_evaluate_channel() {
        // からの場合は無条件でtrue
        $this->assertTrue(Condition::evaluate_channel([], 'aaaa'), '空');
        // チャンネルIDの配列を指定
        $this->assertTrue(Condition::evaluate_channel(['aaaa'], 'aaaa'), 'チャンネルIDあり');
        $this->assertFalse(Condition::evaluate_channel(['aaaa'], 'bbbb'), 'チャンネルIDなし');


        $this->assertTrue(Condition::evaluate_channel(['aaaa', 'bbbb'], 'aaaa'), '複数のチャンネルIDあり');
        $this->assertFalse(Condition::evaluate_channel(['aaaa', 'bbbb'], 'cccc'), '複数のチャンネルIDなし');
    }

    public function test_evaluate_condition() {
        $condition1 = array(
            'type' => 'channel',
            'secret_prefix' => array('aaaa'),
        );
        $this->assertTrue(Condition::evaluate_condition($condition1, 'aaaa', 'U1ccd59c9cace6053f6614fb6997f978d'), 'チャンネルIDあり');


        $condition2 = array(
            'type' => 'destination',
            'destination' => array('type' => 'user', 'lineUserId' => array('U1ccd59c9cace6053f6614fb6997f978d')),
        );
        $this->assertTrue(Condition::evaluate_condition($condition2, 'bbbb', 'U1ccd59c9cace6053f6614fb6997f978d'), '複数チャンネルIDあり');
        $this->assertFalse(Condition::evaluate_condition($condition2, 'cccc', 'a'), '複数チャンネルIDなし');

        $conditions = array(
            'conditions' => array(
                $condition1,
                $condition2,
            ),
        );
        $this->assertTrue(Condition::evaluate_conditions($conditions, 'aaaa', 'U1ccd59c9cace6053f6614fb6997f978d'), '複数条件あり');
        $this->assertFalse(Condition::evaluate_conditions($conditions, 'bbbb', 'U1ccd59c9cace6053f6614fb6997f978d'), 'チャネル違い');
        $this->assertFalse(Condition::evaluate_conditions($conditions, 'cccc', 'a'), '条件すべて外れ');

        $single_conditions = array(
            'conditions' => array(
                $condition1,
            ),
        );
        $this->assertTrue(Condition::evaluate_conditions($single_conditions, 'aaaa', 'U1ccd59c9cace6053f6614fb6997f978d'), '単一条件あり');
    }
}
