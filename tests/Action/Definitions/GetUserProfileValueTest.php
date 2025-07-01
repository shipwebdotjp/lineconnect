<?php


class GetUserProfileValueTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_get_user_profile_value() {
        $func = new \Shipweb\LineConnect\Action\Definitions\GetUserProfileValue();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));
        $result = $func->get_user_profile_value('new_key');
        // assert null
        $this->assertNull($result);

        $result = $func->get_user_profile_value('在住地');
        $this->assertEquals('東京都', $result);
    }

    // Nested value 住所.都道府県
    public function test_get_user_profile_value_nested() {
        $func = new \Shipweb\LineConnect\Action\Definitions\GetUserProfileValue();
        $func->set_secret_prefix("2f38");
        $func->set_event((object) array("source" => (object) array("userId" => "U4123ab4ac2bd7bc6e23018a1996263d5")));
        $result = $func->get_user_profile_value('住所.都道府県');
        $this->assertEquals('静岡県', $result);
    }
}
