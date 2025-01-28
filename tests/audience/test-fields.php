<?php

class AudienceFieldsTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp() :void{
        parent::setUp();
    }

    public function test_WPUser_fieldによるオーディエンスの取得(){
        // user_loginのテスト
        $conditions_login = json_decode('{"conditions":[{"type":"user_login","user_login":["testuser2"]}]}', true);
        $expected_login = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U131aa592ec09610ca4d5e36f4b60ccdb"]}}', true);
        $this->assertEqualSets($expected_login, lineconnectAudience::get_audience_by_condition($conditions_login), 'WPUserのloginフィールドによるオーディエンスの取得が正しく行われることを確認');

        // display_nameのテスト
        $conditions_display_name = json_decode('{"conditions":[{"type":"display_name","display_name":["Test User 2"]}]}', true);
        $expected_display_name = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U131aa592ec09610ca4d5e36f4b60ccdb"]}}', true);
        $this->assertEqualSets($expected_display_name, lineconnectAudience::get_audience_by_condition($conditions_display_name), 'WPUserのdisplay_nameフィールドによるオーディエンスの取得が正しく行われることを確認');

        // user_emailのテスト
        $conditions_email = json_decode('{"conditions":[{"type":"user_email","user_email":["testuser2@example.com"]}]}', true);
        $expected_email = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U131aa592ec09610ca4d5e36f4b60ccdb"]}}', true);
        $this->assertEqualSets($expected_email, lineconnectAudience::get_audience_by_condition($conditions_email), 'WPUserのuser_emailフィールドによるオーディエンスの取得が正しく行われることを確認');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}