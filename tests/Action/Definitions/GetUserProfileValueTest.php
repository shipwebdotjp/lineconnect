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
        $func->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
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
        $func->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4123a772125a1")));
        $result = $func->get_user_profile_value('住所.都道府県');
        $this->assertEquals('静岡県', $result);
    }
}
