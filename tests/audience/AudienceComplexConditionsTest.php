<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class AudienceComplexConditionsTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_チャネルと連携スターテスの複数の条件を指定してのオーディエンス取得() {
        $condition_and = json_decode('{"condition":{"conditions":[{"type":"link","link":{"type":"linked"}},{"type":"channel","secret_prefix":["2f38"]}],"operator":"and"}}', true);

        // 期待値のセットアップ
        $expected = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);

        $actual = Audience::get_audience_by_condition($condition_and['condition']);

        // 'line_user_ids' をソートしてから比較する
        $this->sortLineUserIds($expected);
        $this->sortLineUserIds($actual);

        $this->assertEquals($expected, $actual, 'チャネル指定と連携ステータスのAND条件で正しくオーディエンスを取得できること');
    }

    public function test_チャネルとロールの複数の条件を指定してのオーディエンス取得() {
        $condition_and = json_decode('{"condition":{"conditions":[{"type":"channel","secret_prefix":["2f38"]},{"type":"role","role":["teacher"]}]}}', true);
        $expected = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}}', true);

        $actual = Audience::get_audience_by_condition($condition_and['condition']);

        // 'line_user_ids' をソートしてから比較する
        $this->sortLineUserIds($expected);
        $this->sortLineUserIds($actual);

        $this->assertEquals($expected, $actual, 'チャネル指定とロールのAN条件で正しくオーディエンスを取得できることを確認');
    }

    public function test_チャネルとLINEユーザーIDの複数の条件を指定してのオーディエンス取得() {
        $condition_and = json_decode('{"condition":{"conditions":[{"type":"channel","secret_prefix":["04f7"]},{"type":"lineUserId","lineUserId":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]}]}}', true);
        $expected = json_decode('{"04f7":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]}}', true);
        $actual = Audience::get_audience_by_condition($condition_and['condition']);
        $this->assertEquals($expected, $actual, '存在するチャネルとLINEユーザーIDの複数の条件をAND指定してのオーディエンス取得');

        $condition_and_noexist = json_decode('{"condition":{"conditions":[{"type":"channel","secret_prefix":["04f7"]},{"type":"lineUserId","lineUserId":["U_PLACEHOLDER_USERID1ccdbac80ea15"]}]}}', true);
        $this->assertEquals([], Audience::get_audience_by_condition($condition_and_noexist['condition']), '存在しないチャネルとLINEユーザーIDの複数の条件を指定してのオーディエンス取得');

        $condition_or = json_decode('{"condition":{"conditions":[{"type":"channel","secret_prefix":["2f38"]},{"type":"lineUserId","lineUserId":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]}], "operator":"or"}}', true);
        $expected = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERID1ccdbac80ea15", "U_PLACEHOLDER_USERID4123a772125a1"]}, "04f7":{"type":"multicast","line_user_ids":["U_PLACEHOLDER_USERIDc3f457cdefcc9"]}}', true);
        $actual = Audience::get_audience_by_condition($condition_or['condition']);
        $this->sortLineUserIds($expected);
        $this->sortLineUserIds($actual);
        $this->assertEqualSets($expected, $actual, '存在するチャネルとLINEユーザーIDの複数の条件をOR指定してのオーディエンス取得');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}
