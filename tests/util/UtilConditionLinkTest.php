<?php

use \Shipweb\LineConnect\Utilities\Condition;
class UtilConditionLinkTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    /**
     * evaluate_link のテスト
     *
     * テストデータ例
     * - testuser1: meta.line に "04f7" キーが存在し、'id' が "U_PLACEHOLDER_USERID4e7a9902e5e7d"  
     * - testuser2: meta.line に "04f7" キーが存在し、'id' が "U_PLACEHOLDER_USERIDc3f457cdefcc9"  
     * - testuser3: meta.line に "2f38" キーが存在し、'id' が "U_PLACEHOLDER_USERID1ccdbac80ea15"  
     */
    public function test_evaluate_link(){
        // 「linked」の場合はユーザーが取得できることが条件

        // testuser1 は連携済み（secret_prefix '04f7' かつ id 'U_PLACEHOLDER_USERID4e7a9902e5e7d'）
        $this->assertTrue(
            Condition::evaluate_link('linked', '04f7', 'U_PLACEHOLDER_USERID4e7a9902e5e7d'),
            'testuser1は連携済みなので linked で true となる'
        );
        // 存在しないユーザーの場合、linked 指定は false
        $this->assertFalse(
            Condition::evaluate_link('linked', '04f7', 'NonExistingLineUserId'),
            '存在しないユーザーは連携されていないので linked で false となる'
        );

        // 「unlinked」の場合はユーザーが取得できないことが条件
        // testuser2 は連携済みなので unlinked 指定では false
        $this->assertFalse(
            Condition::evaluate_link('unlinked', '04f7', 'U_PLACEHOLDER_USERIDc3f457cdefcc9'),
            'testuser2は連携済みなので unlinked では false となる'
        );
        // 存在しないユーザーの場合、unlinked 指定は true
        $this->assertTrue(
            Condition::evaluate_link('unlinked', '04f7', 'NonExistingLineUserId'),
            '存在しないユーザーは連携されていないので unlinked で true となる'
        );

        // testuser3 の場合（secret_prefix '2f38'、id 'U_PLACEHOLDER_USERID1ccdbac80ea15'）
        $this->assertTrue(
            Condition::evaluate_link('linked', '2f38', 'U_PLACEHOLDER_USERID1ccdbac80ea15'),
            'testuser3は連携済みなので linked で true となる'
        );
        $this->assertFalse(
            Condition::evaluate_link('unlinked', '2f38', 'U_PLACEHOLDER_USERID1ccdbac80ea15'),
            'testuser3は連携済みなので unlinked では false となる'
        );
    }

}