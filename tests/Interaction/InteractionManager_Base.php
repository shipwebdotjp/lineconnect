<?php
// tests/Interaction/InteractionManager_Base.php

// 共通の use 文はここに記述
// 例: use WP_UnitTestCase; がグローバルに読み込まれている前提です。
use Shipweb\LineConnect\Interaction\InteractionManager;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;
use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\InteractionHandler;

abstract class InteractionManager_Base extends WP_UnitTestCase {
    // 共通テストデータを格納する静的プロパティ
    protected static $init_result;
    protected static $interaction_datas;
    protected static $interaction_ids;


    // wpSetUpBeforeClass をここにまとめる
    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory): void {
        // ここにデータ準備（tests/initdb.phpで行っている処理など）を移行してください。
        self::$init_result = lineconnectTest::init();
        self::$interaction_datas = [
            "シンプルインタラクション" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "steps" => [
                            [
                                "id" => "step-1",
                                "title" => "最初のステップ",
                                "description" => "これはシンプルなインタラクションの最初のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの最初のステップの最初のメッセージです。",
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの最初のステップの2番目のメッセージです。",
                                    ],
                                ],
                                'nextStepId' => 'step-2',
                            ],
                            [
                                "id" => "step-2",
                                "title" => "2番目のステップ",
                                "description" => "これはシンプルなインタラクションの2番目のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの2番目のステップの最初のメッセージです。",
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの2番目のステップの2番目のメッセージです。",
                                    ],
                                ],
                                'stop' => true,
                            ],
                            [
                                "id" => "step-complete",
                                "title" => "完了",
                                "description" => "これはシンプルなインタラクションの完了ステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの完了ステップの最初のメッセージです。",
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "こんにちは！これはシンプルなインタラクションの完了ステップの2番目のメッセージです。",
                                    ],
                                ],
                                'special' => 'complete',
                            ]
                        ]
                    ],
                ],
            ],
            // Additional interaction used for testing different-form behaviors
            "別インタラクション" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "steps" => [
                            [
                                "id" => "other-step-1",
                                "title" => "別の最初のステップ",
                                "description" => "別インタラクションの最初のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "別インタラクション: 最初のメッセージ",
                                    ],
                                ],
                                'nextStepId' => 'other-step-2',
                            ],
                            [
                                "id" => "other-step-2",
                                "title" => "別の2番目のステップ",
                                "description" => "別インタラクションの2番目のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "別インタラクション: 2番目のメッセージ",
                                    ],
                                ],
                                'stop' => true,
                            ],
                            [
                                "id" => "completed-message",
                                "title" => "完了メッセージ",
                                "description" => "これはシンプルなインタラクションの完了メッセージです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "別インタラクション: 完了メッセージ",
                                    ]
                                ],
                                'special' => 'complete',
                            ],
                        ],
                        "runPolicy" => "multi_keep_history",
                    ],
                ],
            ],
            "RunPolicy_single_latest_only" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "steps" => [
                            [
                                "id" => "step-1",
                                "title" => "最初のステップ",
                                "description" => "最初のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "最初のメッセージ",
                                    ],
                                ],
                                'stop' => true,
                            ]
                        ],
                        "runPolicy" => "single_latest_only",
                    ],
                ],
            ],
            "RunPolicy_single_forbid" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "steps" => [
                            [
                                "id" => "step-1",
                                "title" => "最初のステップ",
                                "description" => "最初のステップです。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "最初のメッセージ",
                                    ],
                                ],
                                'stop' => true,
                            ]
                        ],
                        "runPolicy" => "single_forbid",
                    ],
                ],
            ],
        ];
        self::$interaction_ids = [];
        foreach (self::$interaction_datas as $interaction_name => $interaction_data) {
            $post_id = wp_insert_post(array(
                'post_title'   => $interaction_name,
                'post_type' => InteractionCPT::POST_TYPE,
                'post_status' => 'publish',
            ));
            update_post_meta($post_id, InteractionCPT::META_KEY_VERSION, 1);
            update_post_meta($post_id, InteractionCPT::META_KEY_DATA, $interaction_data);
            update_post_meta($post_id, LineConnect::META_KEY__SCHEMA_VERSION, InteractionCPT::SCHEMA_VERSION);
            self::$interaction_ids[$interaction_name] = $post_id;
        }
    }

    // 必要な共通セットアップ（個々のテスト実行前）
    protected function setUp(): void {
        parent::setUp();
        // テスト毎の初期化処理があればここに
    }

    // 必要な共通ヘルパーメソッド（空のテンプレ）
    // protected function createSampleSession(array $overrides = []) {
    //     // 実装を移行してください
    // }
}
