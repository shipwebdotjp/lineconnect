<?php
/*
 * チャネル取得のテストクラス
 * @package LineConnect
 */

class ChannelTest extends WP_UnitTestCase {
    public static function wpSetUpBeforeClass( $factory ) {
        lineconnectTest::init();
    }

    public function test_すべてのチャネルを取得(){
        $channels = lineconnect::get_all_channels();
        $this->assertNotEmpty($channels);
    }

    public function test_最初のチャネルを取得(){
        $channel = lineconnect::get_channel(0);
        $this->assertNotEmpty($channel);
        // 配列のキーとしてprefixがあることを確認する
        $this->assertArrayHasKey('prefix', $channel);
        // channel-secret が 32桁であることを確認する
        $this->assertEquals(32, strlen($channel['channel-secret']));
        
    }
}