<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;
use Shipweb\LineConnect\Core\LineConnect;

class AudienceWpUserIdTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_WPユーザーIDによるオーディエンスの取得() {
        // テストケース1: 空の配列
        $conditions_empty = json_decode('{"conditions":[{"type":"wpUserId","wpUserId":[]}]}', true);
        $actual_empty = Audience::get_audience_by_condition($conditions_empty);
        $this->assertEquals([], $actual_empty, '空の配列を指定した場合、空の配列を返すこと');

        // テストケース2: 有効な1つのID
        $user = self::$result['user'][0];
        $user_id = $user->ID;
        $user_meta_line = $user->get(lineconnect::META_KEY__LINE);
        $expected_single = [];
        if ($user_meta_line && is_array($user_meta_line)) {
            foreach ($user_meta_line as $secret_prefix => $line_data) {
                if (isset($line_data['id'])) {
                    $expected_single[$secret_prefix] = [
                        'type' => 'multicast',
                        'line_user_ids' => [$line_data['id']]
                    ];
                }
            }
        }
        $conditions_single = [
            'conditions' => [
                ['type' => 'wpUserId', 'wpUserId' => [$user_id]],
            ]
        ];
        $actual_single = Audience::get_audience_by_condition($conditions_single);
        $this->assertEqualSets($expected_single, $actual_single, 'WPユーザーIDによるオーディエンスの取得が正しく行われることを確認');

        // テストケース3: 未連携ユーザーのWPユーザーIDを指定
        $conditions_unlinked_user = json_decode('{"conditions":[{"type":"wpUserId","wpUserId":[' . self::$result['user'][4]->ID . ']}]}', true);
        $actual_unlinked_user = Audience::get_audience_by_condition($conditions_unlinked_user);
        $this->assertEquals([], $actual_unlinked_user, '未連携ユーザーのWPユーザーIDを指定した場合、空の配列を返すこと');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}
