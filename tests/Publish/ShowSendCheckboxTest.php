<?php

/*
 * チャネルの送信チェックボックス表示のテストクラス
 * Shipweb\LineConnect\Publish\Post::show_send_checkbox()をテストする
 * @package LineConnect
 */

use Shipweb\LineConnect\Publish\Post;
use Shipweb\LineConnect\Core\LineConnect;

class ShowSendCheckboxTest extends WP_UnitTestCase {
    public static function wpSetUpBeforeClass($factory) {
        lineconnectTest::init();
    }

    public function test_ShowSendCheckbox() {
        ob_start();
        $output = Post::show_send_checkbox();
        $result = ob_get_clean();

        $this->assertStringContainsString('Send update notification', $result);
        $this->assertStringContainsString('Send target:', $result);
        $this->assertStringContainsString('Message template:', $result);
        $this->assertStringContainsString('Send when a future post is published', $result);
        $this->assertStringContainsString('Test Channel 1', $result);
        $this->assertStringContainsString('<input type="checkbox" name="slc_send-checkbox04f7" value="ON" id="id_slc_send-checkbox04f7" checked>', $result);
        $this->assertStringContainsString('<select name=slc_role-selectbox04f7[] multiple class=\'slc-multi-select\'>', $result);
        $this->assertStringContainsString('<input type="checkbox" name="slc_future-checkbox04f7" value="ON" id="id_slc_future-checkbox04f7" >', $result);
        $this->assertStringContainsString('<select name=slc_template-selectbox04f7', $result);
    }
}
