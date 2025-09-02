<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class AudienceLineUserIdTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_LINEユーザーIDによるオーディエンスの取得() {
        // テストケース1: 空の配列
        $empty_result = Audience::get_audience_by_condition([
            'conditions' => [
                ['type' => 'lineUserId', 'lineUserId' => []]
            ]
        ]);
        $this->assertEquals([], $empty_result, '空の配列を指定した場合、空の配列を返すこと');

        // テストケース2: 有効な1つのID
        $single_id = ['U_PLACEHOLDER_USERID1ccdbac80ea15'];
        $single_result = Audience::get_audience_by_condition([
            'conditions' => [
                ['type' => 'lineUserId', 'lineUserId' => $single_id]
            ]
        ]);
        $expected_single = [
            '2f38' => [
                'type' => 'multicast',
                'line_user_ids' => $single_id
            ]
        ];
        $this->sortLineUserIds($expected_single);
        $this->sortLineUserIds($single_result);
        $this->assertEquals($expected_single, $single_result, '有効な1つのIDを指定した場合、そのユーザーIDを含む配列を返すこと');

        // テストケース3: 有効な2つのID
        $two_ids = ['U_PLACEHOLDER_USERID1ccdbac80ea15', 'U_PLACEHOLDER_USERIDc3f457cdefcc9'];
        $two_result = Audience::get_audience_by_condition([
            'conditions' => [
                ['type' => 'lineUserId', 'lineUserId' => $two_ids]
            ]
        ]);
        $expected_two = [
            '2f38' => [
                'type' => 'multicast',
                'line_user_ids' => ['U_PLACEHOLDER_USERID1ccdbac80ea15']
            ],
            '04f7' => [
                'type' => 'multicast',
                'line_user_ids' => ['U_PLACEHOLDER_USERIDc3f457cdefcc9']
            ]
        ];
        $this->sortLineUserIds($expected_two);
        $this->sortLineUserIds($two_result);
        $this->assertEquals($expected_two, $two_result, '有効な2つのIDを指定した場合、それぞれのユーザーIDを含む配列を返すこと');

        // テストケース4: 存在しないID
        $invalid_id = ['Uinvalid1234567890'];
        $invalid_result = Audience::get_audience_by_condition([
            'conditions' => [
                ['type' => 'lineUserId', 'lineUserId' => $invalid_id]
            ]
        ]);
        $this->assertEquals([], $invalid_result, '存在しないIDを指定した場合、空の配列を返すこと');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}
