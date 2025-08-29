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
            "storage_profile" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'profile',
                        "excludeSteps" => [
                            "役職名"
                        ],
                        "steps" => [
                            [
                                "id" => "会社名",
                                "title" => "会社名",
                                "description" => "会社名を入力してください。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "会社名を教えてください。",
                                    ],
                                ],
                                'nextStepId' => "部署名",
                            ],
                            [
                                "id" => "部署名",
                                "title" => "部署名",
                                "description" => "部署名を入力してください。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "部署名を教えてください。",
                                    ],
                                ],
                                'nextStepId' => "役職名",
                            ],
                            [
                                "id" => "役職名",
                                "title" => "役職名",
                                "description" => "役職名を入力してください。",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "役職名を教えてください。",
                                    ],
                                ],
                                'stop' => true,
                            ],
                        ],
                    ],
                ],
            ],
            "interaction_with_timeout" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "timeoutMinutes" => 30,
                        "timeoutRemind" => 10,
                        "onTimeout" => "mark_timeout",
                        "steps" => [
                            [
                                "id" => "tstep-1",
                                "title" => "Timeout step 1",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "これはタイムアウトテストのステップ1です。",
                                    ],
                                ],
                                "nextStepId" => "tstep-2",
                            ],
                            [
                                "id" => "tstep-2",
                                "title" => "Timeout step 2",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "これはタイムアウトテストのステップ2です。",
                                    ],
                                ],
                                "stop" => true,
                            ],
                            [
                                "id" => "timeout-remind",
                                "title" => "Timeout Remind",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "[Test]このセッションはまもなくタイムアウトします。",
                                    ],
                                ],
                                "special" => "timeoutRemind",
                            ],
                            [
                                "id" => "timeout-notice",
                                "title" => "Timeout Notice",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "[Test]セッションはタイムアウトしました。",
                                    ],
                                ],
                                "special" => "timeoutNotice",
                            ],
                        ],
                    ],
                ],
            ],
            "interaction_with_confirmation" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "timeoutMinutes" => 30,
                        "timeoutRemind" => 10,
                        "onTimeout" => "mark_timeout",
                        "steps" => [
                            [
                                "id" => "cstep-1",
                                "title" => "Confirmation step 1",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "これは確認テストのステップ1です。",
                                    ],
                                ],
                                "nextStepId" => "cstep-2",
                            ],
                            [
                                "id" => "cstep-2",
                                "title" => "Confirmation step 2",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "これは確認テストのステップ2です。",
                                    ],
                                ],
                                "nextStepId" => "confirm",
                            ],
                            [
                                "id" => "confirm",
                                "title" => "Confirmation step confirm",
                                "messages" => [
                                    [
                                        "type" => "confirm_template",
                                        "confirm_template" => [
                                            "text" => "入力内容を確認してください。",
                                            "apply" => [
                                                "nextStepId" => "cstep-end",
                                                "label" => "OK",
                                            ],
                                            "edit" => [
                                                "nextStepId" => "cstep-edit",
                                                "label" => "編集",
                                            ],
                                        ],
                                    ],
                                ],
                                "special" => "confirm",
                                "stop" => true,
                            ],
                            [
                                "id" => "cstep-edit",
                                "title" => "Confirmation step edit",
                                "messages" => [
                                    [
                                        "type" => "editPicker_template",
                                        "editPicker_template" => [
                                            "title" => "編集する項目を選択してください。",
                                            "cancel" => [
                                                "nextStepId" => "confirm",
                                                "label" => "キャンセル",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                "id" => "cstep-end",
                                "title" => "Confirmation step end",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "入力が完了しました。ありがとうございました。",
                                    ],
                                ],
                                "special" => "complete",
                            ],
                        ],
                    ],
                ],
            ],
            "interaction_with_cancel" => [
                "1" => [
                    [
                        "version" => "1",
                        "storage" => 'interactions',
                        "timeoutMinutes" => 30,
                        "timeoutRemind" => 10,
                        "onTimeout" => "mark_timeout",
                        "steps" => [
                            [
                                "id" => "cstep-1",
                                "title" => "Cancel step 1",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "これはキャンセルテストのステップ1です。",
                                    ],
                                ],
                                "nextStepId" => "cstep-2",
                            ],
                            [
                                "id" => "cstep-2",
                                "title" => "Cancel step 2",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "これはキャンセルテストのステップ2です。",
                                    ],
                                ],
                                "stop" => true,
                            ],
                            [
                                "id" => "cancelConfirm",
                                "title" => "Confirmation Cancel",
                                "messages" => [
                                    [
                                        "type" => "cancel_confirm_template",
                                        "cancel_confirm_template" => [
                                            "title" => "申込みを中止しますか？",
                                            "abort" => [
                                                "label" => "申込みを中止",
                                            ],
                                            "continue" => [
                                                "label" => "申込みを続ける",
                                            ],
                                        ],
                                    ],
                                ],
                                "special" => "cancelConfirm",
                            ],
                            [
                                "id" => "canceled",
                                "title" => "Canceled",
                                "messages" => [
                                    [
                                        "type" => "text",
                                        "text" => "申込みは中止されました。",
                                    ],
                                ],
                                "special" => "canceled",
                            ],
                        ],
                        "cancelWords" => [
                            [
                                "type" => "equals",
                                "value" => "申し込み中止"
                            ],
                        ],
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
