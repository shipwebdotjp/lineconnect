<?php

class DataDeleteTest extends \WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_delete_all_data() {
        $func = new \Shipweb\LineConnect\Admin\Setting\DataDelete();
        $func->delete_all_data();
        $this->assertTrue(true);
    }
}
