<?php
/*
 * ロガーのテストクラス
 * @package LineConnect
 */

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Logger;

class LoggerTest extends WP_UnitTestCase {
    public static function wpSetUpBeforeClass($factory) {
        lineconnectTest::init();
    }

    public function test_writeBroadcastLog() {
        $message = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('Test broadcast message');

        $result = Logger::writeOutboundMessageLog($message, 'broadcast', 'system', [], '04f7', 'pending', []);
        $this->assertTrue($result);
    }
}
