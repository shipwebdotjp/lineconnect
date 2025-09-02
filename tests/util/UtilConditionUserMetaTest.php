<?php

use \Shipweb\LineConnect\Utilities\Condition;
class UtilConditionUserMetaTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }


    /**
     * evaluate_usermeta のテスト
     *
     * テストデータ例：
     * - testuser1: 性別=男性, 生年=2000, 居住地=東京都渋谷区, 最終購入日=2025-12-01 10:30:00
     * - testuser2: 誕生月=7, 配信停止フラグ=1, 会員ランク=シルバー, 生年=1980, 購入回数=5
     * - testuser3: 性別=女性, 会員ランク=ゴールド, 最終購入日=2024-10-15 10:30:00, 生年=2005, 購入回数=1
     */
    public function test_evaluate_usermeta() {
        // 単一の条件のテスト
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '性別', 'compare' => '=', 'value' => '男性']], 
                '04f7', 
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1の性別は男性'
        );

        // 単一の条件のテスト
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '性別', 'compare' => '=', 'value' => '女性']], 
                '04f7', 
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1の性別は女性ではない'
        );

        // 複数条件のテスト
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [
                    ['key' => '会員ランク', 'compare' => '=', 'value' => 'シルバー'],
                    ['key' => '購入回数', 'compare' => '>=', 'value' => '5']
                ],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser2は会員ランクがシルバーで購入回数が5以上'
        );

        // 複数条件のテスト
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [
                    ['key' => '会員ランク', 'compare' => '=', 'value' => 'シルバー'],
                    ['key' => '購入回数', 'compare' => '>=', 'value' => '6']
                ],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser2は会員ランクがシルバーだが購入回数が6以上ではないのでFalse'
        );

        // 複数条件のテスト
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [
                    ['key' => '会員ランク', 'compare' => '=', 'value' => 'ゴールド'],
                    ['key' => '購入回数', 'compare' => '>=', 'value' => '6']
                ],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser2は会員ランクがゴールドでもないし、購入回数が6以上でもないのでFalse'
        );

        // 存在チェックのテスト
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '最終購入日', 'compare' => 'EXISTS']],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3の最終購入日は存在する'
        );

        // 存在チェックのテスト
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => 'ポイント', 'compare' => 'EXISTS']],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3のポイントは存在しない'
        );

        // NOT EXISTSのテスト
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '存在しないキー', 'compare' => 'NOT EXISTS']],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            '存在しないキーに対するNOT EXISTSはtrue'
        );

        // NOT EXISTSのテスト
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '最終購入日', 'compare' => 'NOT EXISTS']],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            '存在するキーに対するNOT EXISTSはfalse'
        );

        // IN 演算子のテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '生年', 'compare' => 'IN', 'values' => ['2000', '2005', '2010']]],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3の生年は2000, 2005, 2010のいずれか'
        );

        // IN 演算子のテスト(false)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '生年', 'compare' => 'IN', 'values' => ['2001', '2004', '2010']]],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3の生年は2001, 2004, 2010のいずれでもない'
        );

        // NOT INのテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '会員ランク', 'compare' => 'NOT IN', 'values' => ['プラチナ', 'ダイヤモンド']]],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser2の会員ランクはプラチナでもダイヤモンドでもない'
        );

        // NOT INのテスト(false)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '会員ランク', 'compare' => 'NOT IN', 'values' => ['シルバー', 'ゴールド']]],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser2の会員ランクはシルバーでもゴールドでもないはFalse(実際はシルバー)'
        );

        // BETWEEN 比較演算子のテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '生年', 'compare' => 'BETWEEN', 'values' => ['2000', '2010']]],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3の生年は2000-2010の間'
        );

        // BETWEEN 比較演算子のテスト(false)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '生年', 'compare' => 'BETWEEN', 'values' => ['2006', '2010']]],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3の生年は2006-2010の間ではない'
        );

        // NOT BETWEEN 比較演算子のテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '生年', 'compare' => 'NOT BETWEEN', 'values' => ['2001', '2010']]],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1の生年は2001-2010の間ではない'
        );

        // NOT BETWEEN 比較演算子のテスト(false)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '生年', 'compare' => 'NOT BETWEEN', 'values' => ['2000', '2010']]],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1の生年は2000-2010の間なので、NOT BETWEEN は false'
        );

        // 存在しないユーザーのテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '性別', 'compare' => '=', 'value' => '男性']],
                '04f7',
                'NonExistingLineUserId'
            ),
            '存在しないユーザーはtrueを返す'
        );

        // 存在しないメタキーのテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '存在しないキー', 'compare' => '=', 'value' => '何か']],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            '存在しないメタキーは比較をスキップしてtrueを返す'
        );

        // LIKE演算子のテスト
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '居住地', 'compare' => 'LIKE', 'value' => '東京都']],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1の居住地は東京都を含む'
        );

        // LIKE演算子のテスト(False)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '居住地', 'compare' => 'LIKE', 'value' => '埼玉県']],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1の居住地は埼玉県を含まない'
        );

        // NOT LIKE演算子のテスト
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '居住地', 'compare' => 'NOT LIKE', 'value' => '埼玉県']],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1の居住地は埼玉県を含まないのでNOT LIKE は true'
        );
        
        // NOT LIKE演算子のテスト(False)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '居住地', 'compare' => 'NOT LIKE', 'value' => '東京都']],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1の居住地は東京都を含むのでNOT LIKE は false'
        );

        // !=演算子のテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '会員ランク', 'compare' => '!=', 'value' => 'プラチナ']],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser2は会員ランクがプラチナではない'
        );

        // !=演算子のテスト(false)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '会員ランク', 'compare' => '!=', 'value' => 'シルバー']],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser2は会員ランクがシルバーなので!=シルバーはfalse'
        );

        // >演算子のテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '購入回数', 'compare' => '>', 'value' => '1']],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser2は購入回数が1以上である'
        );

        // >演算子のテスト(false)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '購入回数', 'compare' => '>', 'value' => '10']],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser2は購入回数が10以上ではない'
        );

        // >演算子のテスト(false): 文字列で比較した場合
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '性別', 'compare' => '>', 'value' => '女性']],
                '04f7',
                'U_PLACEHOLDER_USERIDc3f457cdefcc9'
            ),
            'testuser1は性別が女性以上という比較(男性のコードポイントが大きいためtrue)'
        );

        // >=演算子のテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '最終購入日', 'compare' => '>=', 'value' => '2025-12-01 10:30:00']],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1は最終購入日が2025-12-01以降である'
        );

        // >=演算子のテスト(false)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '最終購入日', 'compare' => '>=', 'value' => '2026-01-01 10:30:00']],
                '04f7',
                'U_PLACEHOLDER_USERID4e7a9902e5e7d'
            ),
            'testuser1は最終購入日が2026-01-01以降ではない'
        );

        // <演算子のテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '最終購入日', 'compare' => '<', 'value' => '2025-12-01 10:30:00']],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3は最終購入日が2025-12-01より前である'
        );

        // <演算子のテスト(false)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '最終購入日', 'compare' => '<', 'value' => '2024-10-10 10:30:00']],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3は最終購入日が2024-10-10より前ではない'
        );
        

        // <=演算子のテスト(true)
        $this->assertTrue(
            Condition::evaluate_usermeta(
                [['key' => '最終購入日', 'compare' => '<=', 'value' => '2024-10-15 10:30:00']],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3は最終購入日が2024-10-15以前である'
        );

        // <=演算子のテスト(false)
        $this->assertFalse(
            Condition::evaluate_usermeta(
                [['key' => '最終購入日', 'compare' => '<=', 'value' => '2024-10-10 10:30:00']],
                '2f38',
                'U_PLACEHOLDER_USERID1ccdbac80ea15'
            ),
            'testuser3は最終購入日が2024-10-10以前ではない'
        );
    }


}