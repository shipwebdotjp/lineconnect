<?php

use \Shipweb\LineConnect\Utilities\Condition;
class UtilConditionProfileTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    /**
     * evaluate_profile のテスト
     *
     * テストデータ例：
     * - Ud2be13c6f39c97f05c683d92c696483b: displayName=しんぺい, 性別=男性, 年齢=20代, 職業=会社員, 在住地=東京都, 来店回数=10
     * - U131aa592ec09610ca4d5e36f4b60ccdb: displayName=山下慎平, 性別=男性, 年齢=30代, 職業=自営業, 在住地=北海道, 来店回数=0
     * - U1ccd59c9cace6053f6614fb6997f978d: displayName=山下慎平, 性別=男性, 年齢=40代, 職業=エンジニア, 来店回数=5
     * - U4123ab4ac2bd7bc6e23018a1996263d5: displayName=しんぺい(未連携), 性別=女性, 年齢=20代, 職業=ライター, 来店回数=15
     */
    public function test_evaluate_profile() {
        // 単一の条件のテスト（=演算子）
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '性別', 'compare' => '=', 'value' => '男性']], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1の性別は男性'
        );

        // 単一の条件のテスト（!=演算子）
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '性別', 'compare' => '!=', 'value' => '女性']], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1の性別は女性ではない'
        );

        // IN演算子のテスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '年齢', 'compare' => 'IN', 'values' => ['20代', '30代']]], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1の年齢は20代または30代'
        );

        // NOT IN演算子のテスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '年齢', 'compare' => 'NOT IN', 'values' => ['40代', '50代']]], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1の年齢は40代でも50代でもない'
        );

        // EXISTS演算子のテスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => 'displayName', 'compare' => 'EXISTS']], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1のdisplayNameは存在する'
        );

        // NOT EXISTS演算子のテスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '存在しないキー', 'compare' => 'NOT EXISTS']], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            '存在しないキーはNOT EXISTSでtrue'
        );

        // LIKE演算子のテスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '在住地', 'compare' => 'LIKE', 'value' => '東京']], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1の在住地に東京が含まれる'
        );

        // NOT LIKE演算子のテスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => 'displayName', 'compare' => 'NOT LIKE', 'value' => '太郎']], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1のdisplayNameに太郎が含まれない'
        );

        // 数値の比較テスト（>演算子）
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '来店回数', 'compare' => '>', 'value' => '5']], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1の来店回数は5より大きい'
        );

        // 数値の比較テスト（<=演算子）
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '来店回数', 'compare' => '<=', 'value' => '0']], 
                '04f7', 
                'U131aa592ec09610ca4d5e36f4b60ccdb'
            ),
            'ユーザー2の来店回数は0以下'
        );

        // 複数条件のテスト（AND条件）
        $this->assertTrue(
            Condition::evaluate_profile(
                [
                    ['key' => '性別', 'compare' => '=', 'value' => '男性'],
                    ['key' => '職業', 'compare' => '=', 'value' => '会社員']
                ],
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1は男性で会社員'
        );

        // 複数条件のテスト（一つがfalseの場合）
        $this->assertFalse(
            Condition::evaluate_profile(
                [
                    ['key' => '性別', 'compare' => '=', 'value' => '男性'],
                    ['key' => '職業', 'compare' => '=', 'value' => 'エンジニア']
                ],
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1は男性だがエンジニアではない'
        );

        // 日付の比較テスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '最終購入日', 'compare' => '<', 'value' => '2024-01-01 00:00:00']], 
                '04f7', 
                'Ud2be13c6f39c97f05c683d92c696483b'
            ),
            'ユーザー1の最終購入日は2024年1月1日より前'
        );

        // 異なるチャネルのユーザーのテスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '性別', 'compare' => '=', 'value' => '女性']], 
                '2f38', 
                'U4123ab4ac2bd7bc6e23018a1996263d5'
            ),
            '異なるチャネルのユーザーの性別は女性'
        );

        // 言語設定のテスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => 'language', 'compare' => '=', 'value' => 'en']], 
                '2f38', 
                'U4123ab4ac2bd7bc6e23018a1996263d5'
            ),
            'ユーザーの言語設定は英語'
        );

        //　ネストされたデータのテスト
        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '住所.都道府県', 'compare' => '=', 'value' => '静岡県']], 
                '2f38', 
                'U4123ab4ac2bd7bc6e23018a1996263d5'
            ),
            'ネストされた住所データの取得'
        );


        $this->assertTrue(
            Condition::evaluate_profile(
                [['key' => '住所.存在しない.キー', 'compare' => '=', 'value' => '静岡県']], 
                '2f38',
                'U4123ab4ac2bd7bc6e23018a1996263d5'
            ),
            '存在しない階層へのアクセス'
        );
    }
}