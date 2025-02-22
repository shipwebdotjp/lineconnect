<?php

use \Shipweb\LineConnect\Utilities\Condition;
class UtilConditionCompareTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_compare() {
        $this->assertTrue(Condition::compare('=', 10, 10));
        $this->assertFalse(Condition::compare('=', 10, 5));

        $this->assertTrue(Condition::compare('!=', 10, 5));
        $this->assertFalse(Condition::compare('!=', 10, 10));

        $this->assertTrue(Condition::compare('>', 10, 5));
        $this->assertFalse(Condition::compare('>', 5, 10));

        $this->assertTrue(Condition::compare('<', 5, 10));
        $this->assertFalse(Condition::compare('<', 10, 5));

        $this->assertTrue(Condition::compare('>=', 10, 10));
        $this->assertTrue(Condition::compare('>=', 10, 5));
        $this->assertFalse(Condition::compare('>=', 5, 10));

        $this->assertTrue(Condition::compare('<=', 5, 5));
        $this->assertTrue(Condition::compare('<=', 5, 10));
        $this->assertFalse(Condition::compare('<=', 10, 5));

        $this->assertTrue(Condition::compare('IN', 3, [1, 2, 3]));
        $this->assertFalse(Condition::compare('IN', 4, [1, 2, 3]));

        $this->assertTrue(Condition::compare('NOT IN', 4, [1, 2, 3]));
        $this->assertFalse(Condition::compare('NOT IN', 2, [1, 2, 3]));

        $this->assertTrue(Condition::compare('LIKE', 'abc123', 'abc'));
        $this->assertFalse(Condition::compare('LIKE', 'xyz123', 'abc'));

        $this->assertTrue(Condition::compare('NOT LIKE', 'xyz123', 'abc'));
        $this->assertFalse(Condition::compare('NOT LIKE', 'abc123', 'abc'));

        $this->assertTrue(Condition::compare('REGEXP', 'abc123', '^[a-z0-9]+$'));
        $this->assertFalse(Condition::compare('REGEXP', 'abc123', '^[0-9]+$'));

        $this->assertTrue(Condition::compare('NOT REGEXP', 'abc123', '^[0-9]+$'));
        $this->assertFalse(Condition::compare('NOT REGEXP', 'abc123', '^[a-z0-9]+$'));

        $this->assertTrue(Condition::compare('BETWEEN', 5, [1, 10]));
        $this->assertFalse(Condition::compare('BETWEEN', 11, [1, 10]));

        $this->assertTrue(Condition::compare('NOT BETWEEN', 11, [1, 10]));
        $this->assertFalse(Condition::compare('NOT BETWEEN', 5, [1, 10]));
    }
}