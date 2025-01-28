<?php

class AudienceTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp() :void{
        parent::setUp();
    }

    public function testGetAndArrays() {
        // テストケース1: 空の配列
        $this->assertEquals([], lineconnectAudience::get_and_arrays([]), '空の配列の論理積は空の配列であること');

        // テストケース2: 単一配列
        $input = [
            'aaaa' => ['type' => 'multicast', 'line_user_ids' => [1, 2, 3]],
            'bbbb' => ['type' => 'broadcast']
        ];
        $this->assertEquals($input, lineconnectAudience::get_and_arrays([$input]), '単一配列の論理積は元の配列であること');

        // テストケース3: 複数配列の論理積
        $arrays = [
            [
                'aaaa' => ['type' => 'multicast', 'line_user_ids' => [1, 2, 3]],
                'bbbb' => ['type' => 'multicast', 'line_user_ids' => [4, 5, 6]]
            ],
            [
                'aaaa' => ['type' => 'multicast', 'line_user_ids' => [2, 3, 4]],
                'bbbb' => ['type' => 'broadcast']
            ]
        ];
        $expected = [
            'aaaa' => ['type' => 'multicast', 'line_user_ids' => [2, 3]],
            'bbbb' => ['type' => 'broadcast']
        ];
        $this->assertEquals($expected, lineconnectAudience::get_and_arrays($arrays), '複数配列の論理積が正しく計算されること');

        // テストケース4: broadcastの優先
        $arrays = [
            [
                'aaaa' => ['type' => 'broadcast'],
                'bbbb' => ['type' => 'multicast', 'line_user_ids' => [1, 2, 3]]
            ],
            [
                'aaaa' => ['type' => 'multicast', 'line_user_ids' => [2, 3, 4]],
                'bbbb' => ['type' => 'multicast', 'line_user_ids' => [3, 4, 5]]
            ]
        ];
        $expected = [
            'aaaa' => ['type' => 'broadcast'],
            'bbbb' => ['type' => 'multicast', 'line_user_ids' => [3]]
        ];
        $this->assertEquals($expected, lineconnectAudience::get_and_arrays($arrays), '論理積でbroadcastの優先が正しく計算されること');
    }

    public function testGetOrArrays() {
        // テストケース1: 空の配列
        $this->assertEquals([], lineconnectAudience::get_or_arrays([]), '空の配列の論理和は空の配列であること');

        // テストケース2: 単一配列
        $input = [
            'aaaa' => ['type' => 'multicast', 'line_user_ids' => [1, 2, 3]],
            'bbbb' => ['type' => 'broadcast']
        ];
        $this->assertEqualSets($input, lineconnectAudience::get_or_arrays([$input]), '単一配列の論理和は元の配列であること');

        // テストケース3: 複数配列の論理和
        $arrays = [
            [
                'aaaa' => ['type' => 'multicast', 'line_user_ids' => [1, 2, 3]],
                'bbbb' => ['type' => 'multicast', 'line_user_ids' => [4, 5, 6]]
            ],
            [
                'aaaa' => ['type' => 'multicast', 'line_user_ids' => [2, 3, 4]],
                'bbbb' => ['type' => 'broadcast']
            ]
        ];
        $expected = [
            'aaaa' => ['type' => 'multicast', 'line_user_ids' => [1, 2, 3, 4]],
            'bbbb' => ['type' => 'broadcast']
        ];
        $result = lineconnectAudience::get_or_arrays($arrays);
        $this->sortLineUserIds($expected);
        $this->sortLineUserIds($result);
        $this->assertEqualSets($expected, $result, '複数配列の論理和が正しく計算されること');

        // テストケース4: broadcastの優先
        $arrays = [
            [
                'aaaa' => ['type' => 'broadcast'],
                'bbbb' => ['type' => 'multicast', 'line_user_ids' => [1, 2, 3]]
            ],
            [
                'aaaa' => ['type' => 'multicast', 'line_user_ids' => [2, 3, 4]],
                'bbbb' => ['type' => 'multicast', 'line_user_ids' => [3, 4, 5]]
            ]
        ];
        $expected = [
            'aaaa' => ['type' => 'broadcast'],
            'bbbb' => ['type' => 'multicast', 'line_user_ids' => [1, 2, 3, 4, 5]]
        ];
        $result = lineconnectAudience::get_or_arrays($arrays);
        $this->sortLineUserIds($expected);
        $this->sortLineUserIds($result);
        $this->assertEqualSets($expected, $result, '論理和でbroadcastの優先が正しく計算されること');
    }

    private function sortLineUserIds(array &$audience): void {
        foreach ($audience as &$channel) {
            if (isset($channel['line_user_ids'])) {
                sort($channel['line_user_ids']);
            }
        }
    }
}
