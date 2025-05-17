<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class AudienceProfileTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_profile属性によるオーディエンスの取得() {
        $condition_empty = json_decode('{"conditions":[{"type":"profile","profile":[{}]}]}', true);
        $this->assertEmpty(Audience::get_audience_by_condition($condition_empty), '空のプロフィールの場合、空のオーディエンスを返すことを確認');

        $condition_no_compare_no_value = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"no_exist_key"}]}]}', true);
        $this->assertEmpty(Audience::get_audience_by_condition($condition_no_compare_no_value), '比較演算子が指定されておらず、存在しないキーの場合、空のオーディエンスを返すことを確認');

        $condition_no_compare_no_value_exist_key = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"配信停止フラグ"}]}]}', true);
        $actual_no_compare_no_value_exist_key = Audience::get_audience_by_condition($condition_no_compare_no_value_exist_key);
        $this->sortLineUserIds($actual_no_compare_no_value_exist_key);
        $this->assertEmpty($actual_no_compare_no_value_exist_key, 'valueを指定しない場合、空のオーディエンスを返すことを確認');

        // 一致テスト
        $conditions_eq = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"職業","value":"エンジニア","compare":"="}]}]}', true);
        $expected_eq = lineconnectTest::getExpectedLineIds(["U1ccd59c9cace6053f6614fb6997f978d"]);
        $actual_eq = Audience::get_audience_by_condition($conditions_eq);
        $this->sortLineUserIds($actual_eq);
        $this->assertEqualSets($expected_eq, $actual_eq, '一致テストでオーディエンスの取得が正しく行われることを確認');

        // IN テスト
        $conditions_in = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"職業","values":["会社員","エンジニア"],"compare":"IN"}]}]}', true);
        $expected_in = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U1ccd59c9cace6053f6614fb6997f978d"]);
        $actual_in = Audience::get_audience_by_condition($conditions_in);
        $this->sortLineUserIds($actual_in);
        $this->assertEqualSets($expected_in, $actual_in, 'IN演算子でオーディエンスの取得が正しく行われることを確認');

        // NOT IN テスト
        $conditions_not_in = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"職業","values":["ライター"],"compare":"NOT IN"}]}]}', true);
        $expected_not_in = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U131aa592ec09610ca4d5e36f4b60ccdb", "U1ccd59c9cace6053f6614fb6997f978d"]);
        $actual_not_in = Audience::get_audience_by_condition($conditions_not_in);
        $this->sortLineUserIds($actual_not_in);
        $this->assertEqualSets($expected_not_in, $actual_not_in, 'NOT IN演算子でオーディエンスの取得が正しく行われることを確認');

        // BETWEEN テスト
        $conditions_between = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"来店回数","values":[5,10],"compare":"BETWEEN"}]}]}', true);
        $expected_between = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U1ccd59c9cace6053f6614fb6997f978d"]);
        $actual_between = Audience::get_audience_by_condition($conditions_between);
        $this->sortLineUserIds($actual_between);
        $this->assertEqualSets($expected_between, $actual_between, 'BETWEEN演算子でオーディエンスの取得が正しく行われることを確認');

        // NOT BETWEEN テスト
        $conditions_not_between = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"来店回数","values":[4,11],"compare":"NOT BETWEEN"}]}]}', true);
        $expected_not_between = lineconnectTest::getExpectedLineIds(["U131aa592ec09610ca4d5e36f4b60ccdb", "U4123ab4ac2bd7bc6e23018a1996263d5"]);
        $actual_not_between = Audience::get_audience_by_condition($conditions_not_between);
        // print_r($actual_not_between);
        // print_r($expected_not_between);
        $this->sortLineUserIds($actual_not_between);
        $this->assertEqualSets($expected_not_between, $actual_not_between, 'NOT BETWEEN演算子でオーディエンスの取得が正しく行われることを確認');

        // 複数条件テスト
        $conditions_multi = json_decode('{"conditions":[{"type":"profile","profile":[
            {"key":"職業","values":["会社員","エンジニア"],"compare":"IN"},
            {"key":"在住地","value":"東京都","compare":"="}
        ]}]}', true);
        $expected_multi = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U1ccd59c9cace6053f6614fb6997f978d"]);
        $actual_multi = Audience::get_audience_by_condition($conditions_multi);
        $this->sortLineUserIds($actual_multi);
        $this->assertEqualSets($expected_multi, $actual_multi, '複数条件でのオーディエンスの取得が正しく行われることを確認');
    }

    public function test_profile属性によるオーディエンスの取得_追加演算子() {
        $conditions_not_equal = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"職業","value":"ライター","compare":"!="}]}]}', true);
        $expected_not_equal = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U131aa592ec09610ca4d5e36f4b60ccdb", "U1ccd59c9cace6053f6614fb6997f978d"]);
        $actual_not_equal = Audience::get_audience_by_condition($conditions_not_equal);
        $this->sortLineUserIds($actual_not_equal);
        $this->assertEqualSets($expected_not_equal, $actual_not_equal, '!=演算子でオーディエンスの取得が正しく行われることを確認');

        // > テスト
        $conditions_greater_than = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"来店回数","value":"5","compare":">"}]}]}', true);
        $expected_greater_than = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U4123ab4ac2bd7bc6e23018a1996263d5"]);
        $actual_greater_than = Audience::get_audience_by_condition($conditions_greater_than);
        $this->sortLineUserIds($actual_greater_than);
        $this->assertEqualSets($expected_greater_than, $actual_greater_than, '>演算子でオーディエンスの取得が正しく行われることを確認');

        // >= テスト
        $conditions_greater_equal = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"来店回数","value":"5","compare":">="}]}]}', true);
        $expected_greater_equal = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U1ccd59c9cace6053f6614fb6997f978d", "U4123ab4ac2bd7bc6e23018a1996263d5"]);
        $actual_greater_equal = Audience::get_audience_by_condition($conditions_greater_equal);
        $this->sortLineUserIds($actual_greater_equal);
        $this->assertEqualSets($expected_greater_equal, $actual_greater_equal, '>=演算子でオーディエンスの取得が正しく行われることを確認');

        // < テスト
        $conditions_less_than = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"最終購入日","value":"2024-10-01 04:40:00","compare":"<"}]}]}', true);
        $expected_less_than = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b"]);
        $actual_less_than = Audience::get_audience_by_condition($conditions_less_than);
        $this->sortLineUserIds($actual_less_than);
        $this->assertEqualSets($expected_less_than, $actual_less_than, '<演算子でオーディエンスの取得が正しく行われることを確認');

        // <= テスト
        $conditions_less_equal = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"最終購入日","value":"2024-10-01 04:40:00","compare":"<="}]}]}', true);
        $expected_less_equal = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U131aa592ec09610ca4d5e36f4b60ccdb"]);
        $actual_less_equal = Audience::get_audience_by_condition($conditions_less_equal);
        $this->sortLineUserIds($actual_less_equal);
        $this->assertEqualSets($expected_less_equal, $actual_less_equal, '<=演算子でオーディエンスの取得が正しく行われることを確認');

        // LIKE テスト
        $conditions_like = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"在住地","value":"東京","compare":"LIKE"}]}]}', true);
        $expected_like = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U1ccd59c9cace6053f6614fb6997f978d"]);
        $actual_like = Audience::get_audience_by_condition($conditions_like);
        $this->sortLineUserIds($actual_like);
        $this->assertEqualSets($expected_like, $actual_like, 'LIKE演算子でオーディエンスの取得が正しく行われることを確認');

        // NOT LIKE テスト
        $conditions_not_like = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"在住地","value":"東京","compare":"NOT LIKE"}]}]}', true);
        $expected_not_like = lineconnectTest::getExpectedLineIds(["U131aa592ec09610ca4d5e36f4b60ccdb", "U4123ab4ac2bd7bc6e23018a1996263d5"]);
        $actual_not_like = Audience::get_audience_by_condition($conditions_not_like);
        $this->sortLineUserIds($actual_not_like);
        $this->assertEqualSets($expected_not_like, $actual_not_like, 'NOT LIKE演算子でオーディエンスの取得が正しく行われることを確認');

        // REGEXP テスト
        $conditions_regexp = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"在住地","value":"^[東静]","compare":"REGEXP"}]}]}', true);
        $expected_regexp = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U1ccd59c9cace6053f6614fb6997f978d", "U4123ab4ac2bd7bc6e23018a1996263d5"]);
        $actual_regexp = Audience::get_audience_by_condition($conditions_regexp);
        $this->sortLineUserIds($actual_regexp);
        $this->assertEqualSets($expected_regexp, $actual_regexp, 'REGEXP演算子でオーディエンスの取得が正しく行われることを確認');

        // NOT REGEXP テスト
        $conditions_not_regexp = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"在住地","value":"^[東静]","compare":"NOT REGEXP"}]}]}', true);
        $expected_not_regexp = lineconnectTest::getExpectedLineIds(["U131aa592ec09610ca4d5e36f4b60ccdb"]);
        $actual_not_regexp = Audience::get_audience_by_condition($conditions_not_regexp);
        $this->sortLineUserIds($actual_not_regexp);
        $this->assertEqualSets($expected_not_regexp, $actual_not_regexp, 'NOT REGEXP演算子でオーディエンスの取得が正しく行われることを確認');

        // EXISTS テスト
        $conditions_exists = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"車種","compare":"EXISTS"}]}]}', true);
        $expected_exists = lineconnectTest::getExpectedLineIds(["U131aa592ec09610ca4d5e36f4b60ccdb"]);
        $actual_exists = Audience::get_audience_by_condition($conditions_exists);
        $this->sortLineUserIds($actual_exists);
        $this->assertEqualSets($expected_exists, $actual_exists, 'EXISTS演算子でオーディエンスの取得が正しく行われることを確認');

        // NOT EXISTS テスト
        $conditions_not_exists = json_decode('{"conditions":[{"type":"profile","profile":[{"key":"車種","compare":"NOT EXISTS"}]}]}', true);
        $expected_not_exists = lineconnectTest::getExpectedLineIds(["Ud2be13c6f39c97f05c683d92c696483b", "U1ccd59c9cace6053f6614fb6997f978d", "U4123ab4ac2bd7bc6e23018a1996263d5"]);
        $actual_not_exists = Audience::get_audience_by_condition($conditions_not_exists);
        $this->sortLineUserIds($actual_not_exists);
        $this->assertEqualSets($expected_not_exists, $actual_not_exists, 'NOT EXISTS演算子でオーディエンスの取得が正しく行われることを確認');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}
