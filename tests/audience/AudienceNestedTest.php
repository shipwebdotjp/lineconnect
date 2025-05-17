<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class AudienceNestedTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_ネストした条件よるオーディエンスの取得() {
        $condition_empty = json_decode('{"conditions":[{},{"type":"group","condition":{"conditions":[{}]}]}', true);
        $this->assertEmpty(Audience::get_audience_by_condition($condition_empty), '空のネストした条件の場合、空のオーディエンスを返すことを確認');

        $condition_and_group_of_or = json_decode('{"conditions":[{"type":"usermeta","usermeta":[{"key":"配信停止フラグ","compare":"EXISTS"}]},{"type":"group","condition":{"conditions":[{"type":"channel","secret_prefix":["04f7"]},{"type":"profile","profile":[{"key":"来店回数","value":"5","compare":">"}]}],"operator":"or"}}]}', true);
        $expected_and_group_of_or = lineconnectTest::getExpectedLineIds(["U131aa592ec09610ca4d5e36f4b60ccdb"]);
        $actual_and_group_of_or = Audience::get_audience_by_condition($condition_and_group_of_or);
        $this->sortLineUserIds($actual_and_group_of_or);
        $this->assertEqualSets($expected_and_group_of_or, $actual_and_group_of_or, '単一の条件とグループ化した条件でオーディエンスの取得が正しく行われることを確認');


        $condition_and_group_of_or = json_decode('{"conditions":[{"type":"link","link":{"type":"all"}},{"type":"group","condition":{"conditions":[{"type":"profile","profile":[{"key":"職業","value":"警察官","compare":"="}]},{"type":"profile","profile":[{"key":"来店回数","value":"10","compare":">"}]}],"operator":"or"}}]}', true);
        $expected_and_group_of_or = lineconnectTest::getExpectedLineIds(["U4123ab4ac2bd7bc6e23018a1996263d5"]);
        $actual_and_group_of_or = Audience::get_audience_by_condition($condition_and_group_of_or);
        $this->sortLineUserIds($actual_and_group_of_or);
        $this->assertEqualSets($expected_and_group_of_or, $actual_and_group_of_or, '単一の条件とグループ化したOR条件でオーディエンスの取得が正しく行われることを確認');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}
