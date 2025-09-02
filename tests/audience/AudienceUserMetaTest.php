<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class AudienceUserMetaTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_UserMetaによるオーディエンスの取得() {
        $condition_empty = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{}]}]}', true);
        $this->assertEmpty(Audience::get_audience_by_condition($condition_empty), '空のユーザーメタ');

        $condition_no_compare_no_value = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"no_exist_key"}]}]}', true);
        $this->assertEmpty(Audience::get_audience_by_condition($condition_no_compare_no_value), '比較演算子が指定されていないユーザーメタ');

        $condition_no_compare_no_value_exist_key = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"配信停止フラグ"}]}]}', true);
        $expected_no_compare_no_value_exist_key = lineconnectTest::getExpectedLineIds(["U_PLACEHOLDER_USERIDc3f457cdefcc9"]);
        $actual_no_compare_no_value_exist_key = Audience::get_audience_by_condition($condition_no_compare_no_value_exist_key);
        $this->sortLineUserIds($actual_no_compare_no_value_exist_key);
        $this->assertEqualSets($expected_no_compare_no_value_exist_key, $actual_no_compare_no_value_exist_key, 'valueを指定しないが、ユーザーメタのキーが存在するオーディエンスの取得が正しく行われることを確認');


        // 性別が男性
        $conditions_login = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"性別","compare":"=","value":"男性"}]}]}', true);
        $expected_login = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERID4e7a9902e5e7d"]}}', true);
        $actual_login = Audience::get_audience_by_condition($conditions_login);
        $this->sortLineUserIds($expected_login);
        $this->sortLineUserIds($actual_login);
        $this->assertEqualSets($expected_login, $actual_login, 'ユーザーメタの性別が男性であるオーディエンスの取得が正しく行われることを確認');

        // 会員ランクがゴールドかシルバー
        $conditions_rank = json_decode('{"conditions":[{"type":"usermeta", "usermeta":[{"key":"会員ランク", "compare":"IN", "values":["ゴールド", "シルバー"]}]}]}', true);
        $expected_rank = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]},"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);
        $actual_rank = Audience::get_audience_by_condition($conditions_rank);
        $this->sortLineUserIds($expected_rank);
        $this->sortLineUserIds($actual_rank);
        $this->assertEqualSets($expected_rank, $actual_rank, 'ユーザーメタの会員ランクがゴールドかシルバーであるオーディエンスの取得が正しく行われることを確認');

        $conditions_multi = json_decode('{"conditions":[{"type":"usermeta", "usermeta":[{"key":"会員ランク", "compare":"=", "value":"ゴールド"},{"key":"会員ランク", "compare":"=", "value":"シルバー"}]}]}', true);
        $actual_multi = Audience::get_audience_by_condition($conditions_multi);
        $this->sortLineUserIds($actual_multi);
        $this->assertEqualSets($expected_rank, $actual_multi, 'ユーザーメタを複数した場合にオーディエンスの取得がOR条件として正しく行われることを確認');

        // != 比較テスト
        $conditions_neq = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"性別","compare":"!=","value":"女性"}]}]}', true);
        $expected_neq = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERID4e7a9902e5e7d"]}}', true);
        $actual_neq = Audience::get_audience_by_condition($conditions_neq);
        $this->sortLineUserIds($expected_neq);
        $this->sortLineUserIds($actual_neq);
        $this->assertEqualSets($expected_neq, $actual_neq, '性別が女性でないユーザーの取得');

        // > 比較テスト
        $conditions_gt = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"生年","compare":">","value":"2000"}]}]}', true);
        $expected_gt = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);
        $actual_gt = Audience::get_audience_by_condition($conditions_gt);
        $this->sortLineUserIds($expected_gt);
        $this->sortLineUserIds($actual_gt);
        $this->assertEqualSets($expected_gt, $actual_gt, '生年が2000年より後のユーザー取得');

        // >= 比較テスト
        $conditions_gte = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"購入回数","compare":">=","value":"1"}]}]}', true);
        $expected_gte = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]},"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);
        $actual_gte = Audience::get_audience_by_condition($conditions_gte);
        $this->sortLineUserIds($expected_gte);
        $this->sortLineUserIds($actual_gte);
        $this->assertEqualSets($expected_gte, $actual_gte, '購入回数1回以上のユーザー取得');

        // < 比較テスト
        $conditions_lt = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"最終購入日","compare":"<","value":"2025-01-01 00:00:00"}]}]}', true);
        $expected_lt = json_decode('{"2f38":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);
        $actual_lt = Audience::get_audience_by_condition($conditions_lt);
        $this->sortLineUserIds($expected_lt);
        $this->sortLineUserIds($actual_lt);
        $this->assertEqualSets($expected_lt, $actual_lt, '最終購入日が指定された日より前のユーザー取得');

        // <= 比較テスト
        $conditions_lte = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"購入回数","compare":"<=","value":"5"}]}]}', true);
        $expected_lte = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]},"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);
        $actual_lte = Audience::get_audience_by_condition($conditions_lte);
        $this->sortLineUserIds($expected_lte);
        $this->sortLineUserIds($actual_lte);
        $this->assertEqualSets($expected_lte, $actual_lte, '購入回数5回以下のユーザー取得');

        // LIKE 比較テスト
        $conditions_like = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"居住地","compare":"LIKE","value":"東京都"}]}]}', true);
        $expected_like = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERID4e7a9902e5e7d"]}}', true);
        $actual_like = Audience::get_audience_by_condition($conditions_like);
        $this->sortLineUserIds($expected_like);
        $this->sortLineUserIds($actual_like);
        $this->assertEqualSets($expected_like, $actual_like, '居住地に東京都が含まれるユーザー取得');

        // NOT LIKE 比較テスト
        $conditions_notlike = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"居住地","compare":"NOT LIKE","value":"福岡県"}]}]}', true);
        $expected_notlike = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERID4e7a9902e5e7d"]}}', true);
        $actual_notlike = Audience::get_audience_by_condition($conditions_notlike);
        $this->sortLineUserIds($expected_notlike);
        $this->sortLineUserIds($actual_notlike);
        $this->assertEqualSets($expected_notlike, $actual_notlike, '居住地に福岡県が含まれないユーザー取得');

        // NOT IN 比較テスト
        $conditions_notin = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"会員ランク","compare":"NOT IN","values":["ブロンズ"]}]}]}', true);
        $expected_notin = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]},"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);
        $actual_notin = Audience::get_audience_by_condition($conditions_notin);
        $this->sortLineUserIds($expected_notin);
        $this->sortLineUserIds($actual_notin);
        $this->assertEqualSets($expected_notin, $actual_notin, '会員ランクがブロンズでないユーザー取得');

        // BETWEEN 比較テスト
        $conditions_between = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"生年","compare":"BETWEEN","values":["1980","2000"]}]}]}', true);
        $expected_between = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERID4e7a9902e5e7d","U_PLACEHOLDER_USERIDc3f457cdefcc9"]}}', true);
        $actual_between = Audience::get_audience_by_condition($conditions_between);
        $this->sortLineUserIds($expected_between);
        $this->sortLineUserIds($actual_between);
        $this->assertEqualSets($expected_between, $actual_between, '生年が1980-2000年のユーザー取得');

        // NOT BETWEEN 比較テスト
        $conditions_notbetween = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"購入回数","compare":"NOT BETWEEN","values":["2","4"]}]}]}', true);
        $expected_notbetween = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]},"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);
        $actual_notbetween = Audience::get_audience_by_condition($conditions_notbetween);
        $this->sortLineUserIds($expected_notbetween);
        $this->sortLineUserIds($actual_notbetween);
        $this->assertEqualSets($expected_notbetween, $actual_notbetween, '購入回数が2-4回以外のユーザー取得');

        // EXISTS テスト
        $conditions_exists = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"配信停止フラグ","compare":"EXISTS"}]}]}', true);
        $expected_exists = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]}}', true);
        $actual_exists = Audience::get_audience_by_condition($conditions_exists);
        $this->sortLineUserIds($expected_exists);
        $this->sortLineUserIds($actual_exists);
        $this->assertEqualSets($expected_exists, $actual_exists, '配信停止フラグが存在するユーザー取得');

        // NOT EXISTS テスト
        $conditions_notexists = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"最終購入日","compare":"NOT EXISTS"}]}]}', true);
        $expected_notexists = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]}}', true);
        $actual_notexists = Audience::get_audience_by_condition($conditions_notexists);
        $this->sortLineUserIds($expected_notexists);
        $this->sortLineUserIds($actual_notexists);
        $this->assertEqualSets($expected_notexists, $actual_notexists, '最終購入日が存在しないユーザー取得');

        // REGEXP 比較テスト
        $conditions_regexp = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"居住地","compare":"REGEXP","value":"渋谷区$"}]}]}', true);
        $expected_regexp = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERID4e7a9902e5e7d"]}}', true);
        $actual_regexp = Audience::get_audience_by_condition($conditions_regexp);
        $this->sortLineUserIds($expected_regexp);
        $this->sortLineUserIds($actual_regexp);
        $this->assertEqualSets($expected_regexp, $actual_regexp, '居住地が渋谷区で終わるユーザー取得');

        // NOT REGEXP  比較テスト
        $conditions_notregexp = json_decode('{"conditions":[{"type":"usermeta", "usermeta":[{"key":"居住地", "compare":"NOT REGEXP", "value":"^京都府"}]}]}', true);
        $expected_notregexp = json_decode('{"04f7":{"type":"multicast", "line_user_ids":["U_PLACEHOLDER_USERID4e7a9902e5e7d"]}}', true);
        $actual_notregexp = Audience::get_audience_by_condition($conditions_notregexp);
        $this->sortLineUserIds($expected_notregexp);
        $this->sortLineUserIds($actual_notregexp);
        $this->assertEqualSets($expected_notregexp, $actual_notregexp, '居住地が京都府で始まらないユーザー取得');
        $actual_count = Audience::get_recepients_count($actual_notregexp);
        $this->assertNotEmpty($actual_count, 'オーディエンスの取得数が正しく取得できることを確認');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}
