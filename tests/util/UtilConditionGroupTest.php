<?php

use \Shipweb\LineConnect\Utilities\Condition;
class UtilConditionGroupTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_evaluate_group(){
        // 空の場合は無条件でtrue
        $this->assertTrue(Condition::evaluate_condition([], '04f7', 'Ud2be13c6f39c97f05c683d92c696483b'), '空');
        $this->assertTrue(Condition::evaluate_condition(
            [
                'type' => 'group',
                'condition' => [
                    'conditions' => [
                        ['type' => 'user', 'lineUserId' => ['Ud2be13c6f39c97f05c683d92c696483b']]
                    ]
                ]
            ], '04f7', 'Ud2be13c6f39c97f05c683d92c696483b'), '一つのグループ(true)');

        $this->assertFalse(Condition::evaluate_condition([
            'type' => 'group',
            'condition' => [
                'conditions' => [
                    [
                        'type' => 'destination',
                        'destination' => [
                            'type' => 'user', 
                            'lineUserId' => ['Ud2be13c6f39c97f05c683d92c696483b']
                        ],
                    ]
                ]
            ]
        ], '04f7', 'U131aa592ec09610ca4d5e36f4b60ccdb'), '一つのグループ(false)');

        // 複数のグループ
        $this->assertTrue(Condition::evaluate_condition([
            'type' => 'group',
            'condition' => [
                'conditions' => [
                    ['type' => 'user', 'lineUserId' => ['Ud2be13c6f39c97f05c683d92c696483b']],
                    ['type' => 'profile', 'profile' => [['key' => '性別', 'value' => '男性']]]
                ]
            ]
        ], '04f7', 'Ud2be13c6f39c97f05c683d92c696483b'), '複数のグループ(true)');

        $this->assertFalse(Condition::evaluate_condition([
            'type' => 'group',
            'condition' => [
                'conditions' => [
                    ['type' => 'user', 'lineUserId' => ['U131aa592ec09610ca4d5e36f4b60ccdb']],
                    ['type' => 'profile', 'profile' => [['key' => '性別', 'value' => '女性']]]
                ]
            ]
        ], '04f7', 'U131aa592ec09610ca4d5e36f4b60ccdb'), '複数のグループ(false)');

        // ネストしたグループ
        $this->assertTrue(Condition::evaluate_condition([
            'type' => 'group',
            'condition' => [
                'conditions' => [
                    [
                        'type' => 'group',
                        'condition' => [
                            'conditions' => [
                                ['type' => 'user', 'lineUserId' => ['Ud2be13c6f39c97f05c683d92c696483b']],
                                ['type' => 'profile', 'profile' => [['key' => '性別', 'value' => '男性']]]
                            ]
                        ]
                    ]
                ]
            ]
        ], '04f7', 'Ud2be13c6f39c97f05c683d92c696483b'), 'ネストしたグループ(true)');
        $this->assertFalse(Condition::evaluate_condition([
            'type' => 'group',
            'condition' => [
                'conditions' => [
                    [
                        'type' => 'group',
                        'condition' => [
                            'conditions' => [
                                ['type' => 'user', 'lineUserId' => ['U131aa592ec09610ca4d5e36f4b60ccdb']],
                                ['type' => 'profile', 'profile' => [['key' => '性別', 'value' => '女性']]]
                            ]
                        ]
                    ]
                ]
            ]
        ], '04f7', 'U131aa592ec09610ca4d5e36f4b60ccdb'), 'ネストしたグループ(false)');
        // ネストしたグループ(1つの通常条件と、グループ条件)
        $this->assertTrue(Condition::evaluate_condition([
            'type' => 'group',
            'condition' => [
                'conditions' => [
                    [
                        'type' => 'channel',
                        'secret_prefix' => ['04f7'],
                    ],
                    [
                        'type' => 'group',
                        'condition' => [
                            'conditions' => [
                                ['type' => 'user', 'lineUserId' => ['Ud2be13c6f39c97f05c683d92c696483b']],
                                ['type' => 'profile', 'profile' => [['key' => '性別', 'value' => '男性']]]
                            ]
                        ]
                    ]
                ]
            ]
        ], '04f7', 'Ud2be13c6f39c97f05c683d92c696483b'), '1つの通常条件と、グループ条件(true)');

        // ネストしたグループ(1つの通常条件と、グループ条件) Operator: 指定なし(デフォルトのAND)
        $this->assertFalse(Condition::evaluate_condition([
            'type' => 'group',
            'condition' => [
                'conditions' => [
                    [
                        'type' => 'channel',
                        'secret_prefix' => ['04f7'],
                    ],
                    [
                        'type' => 'group',
                        'condition' => [
                            'conditions' => [
                                ['type' => 'user', 'lineUserId' => ['U131aa592ec09610ca4d5e36f4b60ccdb']],
                                ['type' => 'profile', 'profile' => [['key' => '性別', 'value' => '女性']]]
                            ]
                        ]
                    ]
                ]
            ]
        ], '04f7', 'U131aa592ec09610ca4d5e36f4b60ccdb'), '1つの通常条件と、グループ条件(false)');

        // ネストしたグループ(1つの通常条件と、グループ条件) Operator: OR
        $this->assertTrue(Condition::evaluate_condition([
            'type' => 'group',
            'condition' => [
                'conditions' => [
                    [
                        'type' => 'channel',
                        'secret_prefix' => ['04f7'],
                    ],
                    [
                        'type' => 'group',
                        'operator' => 'OR',
                        'condition' => [
                            'conditions' => [
                                ['type' => 'user', 'lineUserId' => ['U131aa592ec09610ca4d5e36f4b60ccdb']],
                                ['type' => 'profile', 'profile' => [['key' => '性別', 'value' => '女性']]]
                            ]
                        ]
                    ]
                ],
                'operator' => 'OR',
            ]
        ], '04f7', 'U131aa592ec09610ca4d5e36f4b60ccdb'), '1つの通常条件(True)と、グループ条件(False) OR');
    }

}