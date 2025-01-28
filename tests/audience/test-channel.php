<?php

class AudienceChannelTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp() :void{
        parent::setUp();
    }

    public function test_チャネル指定でのオーディエンス取得(){
        $this->assertEquals([], lineconnectAudience::get_line_ids_by_channel([]), 'チャネル指定が空なら空配列を返す');
        $condition_single = json_decode('{"condition":{"conditions":[{"type":"channel","secret_prefix":["2f38"]}]}}', true);
        $condition_double = json_decode('{"condition":{"conditions":[{"type":"channel","secret_prefix":["2f38","04f7"]}]}}', true);

        $expected_single = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U4123ab4ac2bd7bc6e23018a1996263d5","U1ccd59c9cace6053f6614fb6997f978d"]}}', true);
        $expected_double = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U4123ab4ac2bd7bc6e23018a1996263d5","U1ccd59c9cace6053f6614fb6997f978d"]},"04f7":{"type":"multicast","line_user_ids":["U131aa592ec09610ca4d5e36f4b60ccdb","Ud2be13c6f39c97f05c683d92c696483b"]}}', true);
    
        $actual_single = lineconnectAudience::get_audience_by_condition($condition_single['condition']);
        $actual_double = lineconnectAudience::get_audience_by_condition($condition_double['condition']);
    
        $this->sortLineUserIds($expected_single);
        $this->sortLineUserIds($actual_single);
        $this->sortLineUserIds($expected_double);
        $this->sortLineUserIds($actual_double);
    
        $this->assertEquals($expected_single, $actual_single, 'チャネル指定が1つならオーディエンスを返す');
        $this->assertEquals($expected_double, $actual_double, 'チャネル指定が2つならオーディエンスを返す');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}