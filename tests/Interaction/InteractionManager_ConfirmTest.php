<?php
// tests/Interaction/InteractionManager_ConfirmTest.php

require_once __DIR__ . '/InteractionManager_Base.php';

class InteractionManager_ConfirmTest extends InteractionManager_Base {

    public function testStartInteraction() {
        $interaction_id = self::$interaction_ids['interaction_with_confirmation'];
        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";
        $event = new \stdClass();
        $event->{'source'} = new \stdClass();
        $event->{'source'}->{'userId'} = $line_user_id;

        // start interaction
        $session_repository = new Shipweb\LineConnect\Interaction\SessionRepository();
        $action_runner = new Shipweb\LineConnect\Interaction\ActionRunner();
        $message_builder = new Shipweb\LineConnect\Interaction\MessageBuilder();
        $normalizer = new Shipweb\LineConnect\Interaction\InputNormalizer();
        $validator = new Shipweb\LineConnect\Interaction\Validator();
        $interaction_handler = new Shipweb\LineConnect\Interaction\InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator,
            new Shipweb\LineConnect\Interaction\RunPolicyEnforcer($session_repository)
        );
        $interaction_manager = new Shipweb\LineConnect\Interaction\InteractionManager(
            $session_repository,
            $interaction_handler
        );
        $interaction_messages = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $this->assertNotEmpty($interaction_messages);
        // contains the expected messages
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $interaction_messages[0]
        );
        $this->assertCount(1, $interaction_messages[0]->buildMessage());
        $this->assertStringContainsString("これは確認テストのステップ1です。", $interaction_messages[0]->buildMessage()[0]["text"]);
        // Stage 2: Simulate user reply
        $user_input = 'ステップ1の回答';
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = $user_input;

        // Stage 3: Handle the user's reply
        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $this->assertNotEmpty($next_step_messages);
        // contains the expected messages
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $next_step_messages[0]
        );
        $this->assertCount(1, $next_step_messages[0]->buildMessage());
        $this->assertStringContainsString("これは確認テストのステップ2です。", $next_step_messages[0]->buildMessage()[0]["text"]);

        //　send to step2
        $reply_event->message->text = "ステップ2の回答";
        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $this->assertNotEmpty($next_step_messages);
        // contains the expected messages
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $next_step_messages[0]
        );
        $this->assertCount(1, $next_step_messages[0]->buildMessage());

        /*
        array(3) {
  ["type"]=>
  string(4) "flex"
  ["altText"]=>
  string(42) "入力内容を確認してください。"
  ["contents"]=>
  array(4) {
    ["type"]=>
    string(6) "bubble"
    ["header"]=>
    array(5) {
      ["type"]=>
      string(3) "box"
      ["layout"]=>
      string(8) "vertical"
      ["contents"]=>
      array(1) {
        [0]=>
        array(10) {
          ["type"]=>
          string(4) "text"
          ["text"]=>
          string(42) "入力内容を確認してください。"
          ["flex"]=>
          int(1)
          ["margin"]=>
          string(4) "none"
          ["size"]=>
          string(2) "xl"
          ["align"]=>
          string(6) "center"
          ["wrap"]=>
          bool(false)
          ["maxLines"]=>
          int(0)
          ["weight"]=>
          string(4) "bold"
          ["color"]=>
          string(7) "#000000"
        }
      }
      ["flex"]=>
      int(1)
      ["backgroundColor"]=>
      string(7) "#FFFFFF"
    }
    ["body"]=>
    array(6) {
      ["type"]=>
      string(3) "box"
      ["layout"]=>
      string(8) "vertical"
      ["contents"]=>
      array(4) {
        [0]=>
        array(9) {
          ["type"]=>
          string(4) "text"
          ["text"]=>
          string(19) "Confirmation step 1"
          ["flex"]=>
          int(1)
          ["margin"]=>
          string(4) "none"
          ["size"]=>
          string(2) "sm"
          ["align"]=>
          string(6) "center"
          ["wrap"]=>
          bool(false)
          ["maxLines"]=>
          int(0)
          ["color"]=>
          string(7) "#555555"
        }
        [1]=>
        array(8) {
          ["type"]=>
          string(4) "text"
          ["text"]=>
          string(22) "ステップ1の回答"
          ["flex"]=>
          int(1)
          ["margin"]=>
          string(4) "none"
          ["align"]=>
          string(6) "center"
          ["wrap"]=>
          bool(true)
          ["maxLines"]=>
          int(0)
          ["color"]=>
          string(7) "#000000"
        }
        [2]=>
        array(9) {
          ["type"]=>
          string(4) "text"
          ["text"]=>
          string(19) "Confirmation step 2"
          ["flex"]=>
          int(1)
          ["margin"]=>
          string(4) "none"
          ["size"]=>
          string(2) "sm"
          ["align"]=>
          string(6) "center"
          ["wrap"]=>
          bool(false)
          ["maxLines"]=>
          int(0)
          ["color"]=>
          string(7) "#555555"
        }
        [3]=>
        array(8) {
          ["type"]=>
          string(4) "text"
          ["text"]=>
          string(22) "ステップ2の回答"
          ["flex"]=>
          int(1)
          ["margin"]=>
          string(4) "none"
          ["align"]=>
          string(6) "center"
          ["wrap"]=>
          bool(true)
          ["maxLines"]=>
          int(0)
          ["color"]=>
          string(7) "#000000"
        }
      }
      ["flex"]=>
      int(1)
      ["spacing"]=>
      string(2) "md"
      ["backgroundColor"]=>
      string(7) "#FFFFFF"
    }
    ["footer"]=>
    array(6) {
      ["type"]=>
      string(3) "box"
      ["layout"]=>
      string(10) "horizontal"
      ["contents"]=>
      array(2) {
        [0]=>
        array(9) {
          ["type"]=>
          string(3) "box"
          ["layout"]=>
          string(8) "vertical"
          ["contents"]=>
          array(1) {
            [0]=>
            array(8) {
              ["type"]=>
              string(4) "text"
              ["text"]=>
              string(2) "OK"
              ["flex"]=>
              int(1)
              ["margin"]=>
              string(4) "none"
              ["align"]=>
              string(6) "center"
              ["wrap"]=>
              bool(false)
              ["maxLines"]=>
              int(0)
              ["color"]=>
              string(7) "#1e90ff"
            }
          }
          ["flex"]=>
          int(1)
          ["margin"]=>
          string(4) "none"
          ["action"]=>
          array(4) {
            ["type"]=>
            string(8) "postback"
            ["label"]=>
            string(2) "OK"
            ["data"]=>
            string(56) "mode=interaction&step=cstep-confirm&nextStepId=cstep-end"
            ["displayText"]=>
            string(2) "OK"
          }
          ["paddingAll"]=>
          string(2) "lg"
          ["backgroundColor"]=>
          string(7) "#FFFFFF"
          ["alignItems"]=>
          string(6) "center"
        }
        [1]=>
        array(9) {
          ["type"]=>
          string(3) "box"
          ["layout"]=>
          string(8) "vertical"
          ["contents"]=>
          array(1) {
            [0]=>
            array(8) {
              ["type"]=>
              string(4) "text"
              ["text"]=>
              string(6) "編集"
              ["flex"]=>
              int(1)
              ["margin"]=>
              string(4) "none"
              ["align"]=>
              string(6) "center"
              ["wrap"]=>
              bool(false)
              ["maxLines"]=>
              int(0)
              ["color"]=>
              string(7) "#1e90ff"
            }
          }
          ["flex"]=>
          int(1)
          ["margin"]=>
          string(4) "none"
          ["action"]=>
          array(4) {
            ["type"]=>
            string(8) "postback"
            ["label"]=>
            string(6) "編集"
            ["data"]=>
            string(57) "mode=interaction&step=cstep-confirm&nextStepId=cstep-edit"
            ["displayText"]=>
            string(6) "編集"
          }
          ["paddingAll"]=>
          string(2) "lg"
          ["backgroundColor"]=>
          string(7) "#FFFFFF"
          ["alignItems"]=>
          string(6) "center"
        }
      }
      ["flex"]=>
      int(1)
      ["spacing"]=>
      string(2) "sm"
      ["backgroundColor"]=>
      string(7) "#FFFFFF"
    }
  }
}
  */
        // assert the message()[0] is array and has key:content
        $this->assertIsArray($next_step_messages[0]->buildMessage()[0]);
        $this->assertArrayHasKey("contents", $next_step_messages[0]->buildMessage()[0]);
        $this->assertArrayHasKey("body", $next_step_messages[0]->buildMessage()[0]["contents"]);
        $this->assertArrayHasKey("contents", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]);
        $this->assertCount(4, $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"]);
        $this->assertEquals("Confirmation step 1", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"][0]["text"]);
        $this->assertEquals("ステップ1の回答", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"][1]["text"]);
        $this->assertEquals("Confirmation step 2", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"][2]["text"]);
        $this->assertEquals("ステップ2の回答", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"][3]["text"]);
        $this->assertArrayHasKey("footer", $next_step_messages[0]->buildMessage()[0]["contents"]);
        $this->assertCount(2, $next_step_messages[0]->buildMessage()[0]["contents"]["footer"]["contents"]);
        $this->assertEquals("OK", $next_step_messages[0]->buildMessage()[0]["contents"]["footer"]["contents"][0]["action"]["label"]);
        $this->assertEquals("mode=interaction&step=confirm&nextStepId=cstep-end", $next_step_messages[0]->buildMessage()[0]["contents"]["footer"]["contents"][0]["action"]["data"]);
        $this->assertEquals("編集", $next_step_messages[0]->buildMessage()[0]["contents"]["footer"]["contents"][1]["action"]["label"]);
        $this->assertEquals("mode=interaction&step=confirm&nextStepId=cstep-edit", $next_step_messages[0]->buildMessage()[0]["contents"]["footer"]["contents"][1]["action"]["data"]);

        $reply_event->type = 'postback';
        unset($reply_event->message);
        $reply_event->postback = new \stdClass();
        $reply_event->postback->data = 'mode=interaction&step=confirm&nextStepId=cstep-edit';
        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $this->assertNotEmpty($next_step_messages);
        // contains the expected messages
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $next_step_messages[0]
        );
        $this->assertCount(1, $next_step_messages[0]->buildMessage());
        $this->assertIsArray($next_step_messages[0]->buildMessage()[0]);
        $this->assertArrayHasKey("contents", $next_step_messages[0]->buildMessage()[0]);
        $this->assertArrayHasKey("body", $next_step_messages[0]->buildMessage()[0]["contents"]);
        $this->assertArrayHasKey("contents", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]);
        $this->assertCount(2, $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"]);
        $this->assertEquals("Confirmation step 1", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"][0]["action"]["label"]);
        $this->assertEquals("mode=interaction&step=cstep-edit&nextStepId=cstep-1&returnTo=confirm", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"][0]["action"]["data"]);
        $this->assertEquals("Confirmation step 2", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"][1]["action"]["label"]);
        $this->assertEquals("mode=interaction&step=cstep-edit&nextStepId=cstep-2&returnTo=confirm", $next_step_messages[0]->buildMessage()[0]["contents"]["body"]["contents"][1]["action"]["data"]);

        // var_dump($next_step_messages[0]->buildMessage()[0]);
        $this->assertCount(1, $next_step_messages[0]->buildMessage()[0]["contents"]["footer"]["contents"]);
        $this->assertEquals("キャンセル", $next_step_messages[0]->buildMessage()[0]["contents"]["footer"]["contents"][0]["action"]["label"]);
        $this->assertEquals("mode=interaction&step=cstep-edit&nextStepId=confirm", $next_step_messages[0]->buildMessage()[0]["contents"]["footer"]["contents"][0]["action"]["data"]);

        //　send to step1
        $reply_event->postback->data = 'mode=interaction&step=cstep-edit&nextStepId=cstep-1&returnTo=confirm';
        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);

        $this->assertNotEmpty($next_step_messages);
        // contains the expected messages
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $next_step_messages[0]
        );
        $this->assertCount(1, $next_step_messages[0]->buildMessage());
        $this->assertStringContainsString("これは確認テストのステップ1です。", $next_step_messages[0]->buildMessage()[0]["text"]);

        $user_input = '修正したステップ1の回答';
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = $user_input;

        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $current_session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertEquals('confirm', $current_session->get_current_step_id());

        //修正した内容が反映されているかの確認
        $this->assertEquals($user_input, $current_session->get_answer('cstep-1'));

        $reply_event->type = 'postback';
        unset($reply_event->message);
        $reply_event->postback = new \stdClass();
        $reply_event->postback->data = 'mode=interaction&step=confirm&nextStepId=cstep-end';
        $next_step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        $this->assertNotEmpty($next_step_messages);
        // contains the expected messages
        $this->assertInstanceOf(
            \LINE\LINEBot\MessageBuilder\MultiMessageBuilder::class,
            $next_step_messages[0]
        );
        $this->assertCount(1, $next_step_messages[0]->buildMessage());
        $this->assertStringContainsString("入力が完了しました。ありがとうございました。", $next_step_messages[0]->buildMessage()[0]["text"]);
    }
}
