<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class GetMyUserInfoTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_get_my_user_info() {
        $func = new \Shipweb\LineConnect\Action\Definitions\GetMyUserInfo();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $result = $func->get_my_user_info();
        $this->assertEquals('linked', $result['linkstatus']);
        $this->assertEquals('Test User 1', $result['display_name']);
        $this->assertEquals('testuser1@example.com', $result['user_email']);
    }

    public function test_not_linked_but_registered() {
        $func = new \Shipweb\LineConnect\Action\Definitions\GetMyUserInfo();
        $func->set_secret_prefix("2f38");
        $func->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4123a772125a1")));
        $result = $func->get_my_user_info();
        $this->assertEquals('not_linked', $result['linkstatus']);
        $this->assertEquals('TestUser1(未連携)', $result['display_name']);
    }

    public function test_not_linked_and_not_registered() {
        $func = new \Shipweb\LineConnect\Action\Definitions\GetMyUserInfo();
        $func->set_secret_prefix("2f38");
        $func->set_event((object) array("source" => (object) array("userId" => "Unotfound")));
        $result = $func->get_my_user_info();
        $this->assertEquals('not_linked', $result['error']);
        $this->assertEquals('You are not linked to WordPress', $result['message']);
    }
}
