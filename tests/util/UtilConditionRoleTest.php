<?php

use \Shipweb\LineConnect\Utilities\Condition;
class UtilConditionRoleTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    /**
     * evaluate_role のテスト
     *
     * 各ユーザーのロールはテストデータで下記のようになっていると仮定
     * - testuser1: role ["administrator"]
     * - testuser2: role ["subscriber", "teacher"]
     * - testuser3: role ["teacher", "student"]
     * - testuser4, testuser5: metaにlineが存在しないので、get_wpuser_from_line_id() が false を返す
     */
    public function test_evaluate_role(){
        // testuser1 の場合（secret_prefix '04f7', id 'Ud2be13c6f39c97f05c683d92c696483b'）
        $this->assertTrue(
            Condition::evaluate_role(['administrator'], '04f7', 'Ud2be13c6f39c97f05c683d92c696483b'),
            'testuser1は administrator ロールを持つ'
        );
        $this->assertFalse(
            Condition::evaluate_role(['subscriber'], '04f7', 'Ud2be13c6f39c97f05c683d92c696483b'),
            'testuser1は subscriber ロールを持たない'
        );

        // testuser2 の場合（secret_prefix '04f7', id 'U131aa592ec09610ca4d5e36f4b60ccdb'）
        $this->assertTrue(
            Condition::evaluate_role(['teacher'], '04f7', 'U131aa592ec09610ca4d5e36f4b60ccdb'),
            'testuser2は teacher ロールを持つ'
        );
        $this->assertFalse(
            Condition::evaluate_role(['administrator'], '04f7', 'U131aa592ec09610ca4d5e36f4b60ccdb'),
            'testuser2は administrator ロールを持たない'
        );

        // testuser3 の場合（secret_prefix '2f38', id 'U1ccd59c9cace6053f6614fb6997f978d'）
        $this->assertTrue(
            Condition::evaluate_role(['teacher'], '2f38', 'U1ccd59c9cace6053f6614fb6997f978d'),
            'testuser3は teacher ロールを持つ'
        );

        $this->assertTrue(
            Condition::evaluate_role(['student'], '2f38', 'U1ccd59c9cace6053f6614fb6997f978d'),
            'testuser3は student ロールを持つ'
        );

        $this->assertFalse(
            Condition::evaluate_role(['administrator'], '2f38', 'U1ccd59c9cace6053f6614fb6997f978d'),
            'testuser3は administrator ロールを持たない'
        );

        // 存在しないユーザーの場合、false を返す
        $this->assertFalse(
            Condition::evaluate_role(['administrator'], '04f7', 'NonExistingLineUserId'),
            '存在しないユーザーは false となる'
        );
    }

}