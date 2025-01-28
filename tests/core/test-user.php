<?php
/*
 * ユーザーのテストクラス
 * @package LineConnect
 */

class userTest extends WP_UnitTestCase {
    protected static $result;

    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function test_LINEIDからWPUserを取得(){
        // var_dump(self::$result);
        
        $this->assertEquals(false, lineconnect::get_wpuser_from_line_id('no_exist', 'test'), 'wpuserが存在しない場合はfalseを返す');
        $user = get_user_by('login', 'testuser1');
        $this->assertNotEmpty($user, 'テストユーザーが存在することを確認');
        $user_meta_line = $user->get( lineconnect::META_KEY__LINE );
        $this->assertNotEmpty($user_meta_line, 'ユーザーにLINEメタデータが存在することを確認');
        $secret_prefix = array_keys($user_meta_line)[0];
        $line_id = $user_meta_line[$secret_prefix]['id'];
        $this->assertEquals($user, lineconnect::get_wpuser_from_line_id($secret_prefix, $line_id), 'LINEIDからWPUserを取得できることを確認');
        $this->assertNotEmpty(lineconnect::get_userdata_from_line_id($secret_prefix, $line_id), 'LINEIDからプロフィール情報を取得できることを確認');
        $this->assertEquals($line_id, lineconnect::get_line_id_from_wpuser($user, $secret_prefix), 'LINEIDを取得できることを確認');
    }

}