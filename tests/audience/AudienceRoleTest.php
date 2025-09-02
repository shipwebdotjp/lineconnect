<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class AudienceRoleTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_ロールによるオーディエンス取得() {
        // 期待値のセットアップ
        // 単一のロールを指定して、そのロールを持つユーザーが正しく取得できることを確認
        $condition_single = json_decode('{"condition":{"conditions":[{"type":"role","role":["teacher"],"match":"role"}]}}', true);
        $expected_single = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]},"04f7":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]}}', true);
        $single_line_ids = Audience::get_audience_by_condition($condition_single['condition']);
        $this->assertEqualSets($expected_single, $single_line_ids, '単一のロールを指定して、そのロールを持つユーザーが正しく取得できることを確認');

        // 存在しない単一のロールを指定して、結果が空であることを確認
        $condition_nonexistent = json_decode('{"condition":{"conditions":[{"type":"role","role":["nonexistent"],"match":"role"}]}}', true);
        $nonexistent_line_ids = Audience::get_audience_by_condition($condition_nonexistent['condition']);
        $this->assertEmpty($nonexistent_line_ids, '存在しない単一のロールを指定して、結果が空であることを確認');

        // 存在しない複数のロールを指定して、結果が空であることを確認
        $condition_nonexistent_multiple = json_decode('{"condition":{"conditions":[{"type":"role","role":["nonexistent","nonexistent2"],"match":"role__in"}]}}', true);
        $nonexistent_multiple_line_ids = Audience::get_audience_by_condition($condition_nonexistent_multiple['condition']);
        $this->assertEmpty($nonexistent_multiple_line_ids, '存在しない複数のロールを指定して、結果が空であることを確認');

        //複数のロールを指定して、そのすべてのロールを持つユーザーが正しく取得できることを確認
        $condition_multiple = json_decode('{"condition":{"conditions":[{"type":"role","role":["teacher","student"],"match":"role"}]}}', true);
        $expected_multiple = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);
        $multiple_line_ids = Audience::get_audience_by_condition($condition_multiple['condition']);
        // 結果を確認する
        $this->assertEqualSets($expected_multiple, $multiple_line_ids, '複数のロールを指定して、そのすべてのロールを持つユーザーが正しく取得できることを確認');

        // role__in のテスト
        $condition_role_in = json_decode('{"condition":{"conditions":[{"type":"role","role":["teacher","editor","administrator"],"match":"role__in"}]}}', true);
        $expected_role_in = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]},"04f7":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9","U_PLACEHOLDER_USERID4e7a9902e5e7d"]}}', true);
        $role_in_line_ids = Audience::get_audience_by_condition($condition_role_in['condition']);
        $this->sortLineUserIds($expected_role_in);
        $this->sortLineUserIds($role_in_line_ids);
        // 結果を確認する
        $this->assertEqualSets($expected_role_in, $role_in_line_ids, 'role__in: 指定したロールのいずれかを持つユーザーが正しく取得できることを確認');

        // role__not_in のテスト
        $condition_role_not_in = json_decode('{"condition":{"conditions":[{"type":"role","role":["administrator","editor","author"],"match":"role__not_in"}]}}', true);
        $expected_role_not_in = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]},"04f7":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]}}', true);
        $role_not_in_line_ids = Audience::get_audience_by_condition($condition_role_not_in['condition']);
        // 結果を確認する
        $this->assertEqualSets($expected_role_not_in, $role_not_in_line_ids, 'role__not_in: 指定したロールを持たないユーザーが正しく取得できることを確認');

        // 複合条件のテスト（role__in と role__not_in の組み合わせ）
        $condition_complex = json_decode('{"condition":{"conditions":[{"type":"role","role":["teacher","student"],"match":"role__in"},{"type":"role","role":["subscriber"],"match":"role__not_in"}]}}', true);
        $expected_complex = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);
        $complex_line_ids = Audience::get_audience_by_condition($condition_complex['condition']);
        // 結果を確認する
        $this->assertEqualSets($expected_complex, $complex_line_ids, '複合条件: role__in と role__not_in の組み合わせで正しくユーザーが取得できることを確認');

        // 複合条件のテスト ["subscriber", "teacher"] 両方を持つユーザーか、["teacher", "student"]の両方を持つユーザー (roleとroleのOR)
        $condition_complex_role_or_role = json_decode('{"condition":{"conditions":[{"type":"role","role":["subscriber", "teacher"],"match":"role"},{"type":"role","role":["teacher", "student"],"match":"role"}],"operator":"or"}}', true);
        $expected_complex_role_or_role = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]},"04f7":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]}}', true);
        $complex_role_or_role_line_ids = Audience::get_audience_by_condition($condition_complex_role_or_role['condition']);
        // print_r($complex_role_or_role_line_ids);
        $this->assertEqualSets($expected_complex_role_or_role, $complex_role_or_role_line_ids, '複合条件のテスト: 複数のroleと複数のrole の組み合わせで正しくユーザーが取得できることを確認');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}
