<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class AudienceLinkTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_連携スターテスによるオーディエンス取得() {
        $condition_linked = json_decode('{"condition":{"conditions":[{"type":"link","link":{"type":"linked"}}]}}', true);
        $condition_unlinked = json_decode('{"condition":{"conditions":[{"type":"link","link":{"type":"unlinked"}}]}}', true);
        $condition_all = json_decode('{"condition":{"conditions":[{"type":"link","link":{"type":"all"}}]}}', true);
        $condition_broadcast = json_decode('{"condition":{"conditions":[{"type":"link","link":{"type":"broadcast"}}]}}', true);

        $expected_linked = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U1ccd59c9cace6053f6614fb6997f978d"]},"04f7":{"type":"multicast","line_user_ids":["Ud2be13c6f39c97f05c683d92c696483b","U131aa592ec09610ca4d5e36f4b60ccdb"]}}', true);
        $expected_unlinked = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U4123ab4ac2bd7bc6e23018a1996263d5"]}}', true);
        $expected_all = json_decode('{"2f38":{"type":"multicast","line_user_ids":["U1ccd59c9cace6053f6614fb6997f978d","U4123ab4ac2bd7bc6e23018a1996263d5"]},"04f7":{"type":"multicast","line_user_ids":["Ud2be13c6f39c97f05c683d92c696483b","U131aa592ec09610ca4d5e36f4b60ccdb"]}}', true);
        $expected_broadcast = json_decode('{"04f7":{"type":"broadcast"},"2f38":{"type":"broadcast"}}', true);

        $linked_line_ids = Audience::get_audience_by_condition($condition_linked['condition']);
        $unlinked_line_ids = Audience::get_audience_by_condition($condition_unlinked['condition']);
        $all_line_ids = Audience::get_audience_by_condition($condition_all['condition']);
        $broadcast_line_ids = Audience::get_audience_by_condition($condition_broadcast['condition']);

        $this->assertNotEmpty($linked_line_ids, '連携済みユーザーが存在することを確認');
        $this->assertEquals(3, count($linked_line_ids['2f38']['line_user_ids']) + count($linked_line_ids['04f7']['line_user_ids']), '連携済みユーザーが4件であることを確認');
        $this->assertEqualSets($expected_linked, $linked_line_ids, '連携済みユーザーが正しく取得できることを確認');
        $this->assertNotEmpty($unlinked_line_ids, '未連携ユーザーが存在することを確認');
        $this->assertEquals(1, count($unlinked_line_ids['2f38']['line_user_ids']), '未連携ユーザーが1件であることを確認');
        $this->assertEqualSets($expected_unlinked, $unlinked_line_ids, '未連携ユーザーが正しく取得できることを確認');
        $this->assertNotEmpty($all_line_ids, '全ユーザーが存在することを確認');
        $this->assertEquals(4, count($all_line_ids['2f38']['line_user_ids']) + count($all_line_ids['04f7']['line_user_ids']), '全ユーザーが5件であることを確認');
        $this->assertEqualSets($expected_all, $all_line_ids, '全ユーザーが正しく取得できることを確認');
        $this->assertNotEmpty($broadcast_line_ids, 'ブロードキャストが存在することを確認');
        $this->assertEqualSets($expected_broadcast, $broadcast_line_ids, 'ブロードキャストが正しく取得できることを確認');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}
