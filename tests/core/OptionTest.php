<?php
/*
 * ユーザーのテストクラス
 * @package LineConnect
 */

use Shipweb\LineConnect\Core\LineConnect;

class OptionTest extends WP_UnitTestCase {
    protected static $result;

    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    /**
     * Test for get_all_options()
     */
    public function test_get_all_options() {
        $options = LineConnect::get_all_options();
        $this->assertIsArray($options);
    }

    /**
     * Test for get_option()
     */
    public function test_get_option() {
        $option_key_settings = LineConnect::PLUGIN_PREFIX . 'settings';
        $original_options = get_option($option_key_settings);
        $option_name = 'login_page_url';
        $option_value = 'wp-login.php';

        $retrieved_option_value = LineConnect::get_option($option_name);
        $this->assertEquals($option_value, $retrieved_option_value);
    }
}
