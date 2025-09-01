<?php

use Shipweb\LineConnect\Interaction\InteractionManager;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;
use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\InteractionHandler;


class InteractionDefinitionTest extends WP_UnitTestCase {
    protected static $init_result;
    protected static $interaction_datas;
    protected static $interaction_ids;

    public static function wpSetUpBeforeClass($factory) {
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
                            ],
                        ]
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

    public function testStartInteraction() {
        $interaction_id = self::$interaction_ids['シンプルインタラクション'];
        $result = Shipweb\LineConnect\Interaction\InteractionDefinition::from_post($interaction_id, null);
        $this->assertInstanceOf(Shipweb\LineConnect\Interaction\InteractionDefinition::class, $result);
        $this->assertEquals('シンプルインタラクション', $result->get_title());
        $this->assertEquals('1', $result->get_version());
        //get_steps
        $steps = $result->get_steps();
        $this->assertIsArray($steps);
        $this->assertCount(3, $steps);
        $this->assertInstanceOf(Shipweb\LineConnect\Interaction\StepDefinition::class, $steps[0]);
        $this->assertInstanceOf(Shipweb\LineConnect\Interaction\StepDefinition::class, $steps[1]);
        $this->assertInstanceOf(Shipweb\LineConnect\Interaction\StepDefinition::class, $steps[2]);

        $this->assertInstanceOf(Shipweb\LineConnect\Interaction\StepDefinition::class, $result->get_first_step());
        //get_step by id
        $step = $result->get_step('step-1');
        $this->assertInstanceOf(Shipweb\LineConnect\Interaction\StepDefinition::class, $step);
        $this->assertEquals('step-1', $step->get_id());
        // get complete step
        $complete_step = $result->get_special_step('complete');
        $this->assertInstanceOf(Shipweb\LineConnect\Interaction\StepDefinition::class, $complete_step);
        $this->assertEquals('step-complete', $complete_step->get_id());
    }
}
