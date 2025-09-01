<?php
// tests/Interaction/InteractionManager_ReminderTest.php

// Ensure base test setup is loaded
require_once __DIR__ . '/InteractionManager_Base.php';
require_once __DIR__ . '/../../tests/LINEBot/Util/DummyHttpClient.php';

// Provide a small stub for the LINE SDK to avoid external HTTP calls during tests.
// We declare minimal parts of the LINE SDK that Builder::sendPushMessage invokes.
// namespace LINE\LINEBot {
//     class LINEBot {
//         public function __construct($httpClient, $opts = []) {
//             // no-op
//         }

//         public function pushMessage($to, $message, $notificationDisabled = false) {
//             return new class {
//                 public function getHTTPStatus() {
//                     return 200;
//                 }
//                 public function isSucceeded() {
//                     return true;
//                 }
//                 public function getJSONDecodedBody() {
//                     return [];
//                 }
//             };
//         }

//         public function multicast($to, $message, $notificationDisabled = false) {
//             return $this->pushMessage($to, $message, $notificationDisabled);
//         }

//         public function broadcast($message, $notificationDisabled = false) {
//             return $this->pushMessage(null, $message, $notificationDisabled);
//         }

//         // Validation methods used in some flows; keep them returning success.
//         public function validatePushMessage($message) {
//             return new class {
//                 public function getHTTPStatus() {
//                     return 200;
//                 }
//                 public function getJSONDecodedBody() {
//                     return [];
//                 }
//             };
//         }

//         public function validateMulticastMessage($message) {
//             return $this->validatePushMessage($message);
//         }
//         public function validateBroadcastMessage($message) {
//             return $this->validatePushMessage($message);
//         }
//         public function validateReplyMessage($message) {
//             return $this->validatePushMessage($message);
//         }
//     }
// }

// namespace {

use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\ActionRunner;
use Shipweb\LineConnect\Interaction\MessageBuilder as InteractionMessageBuilder;
use Shipweb\LineConnect\Interaction\InputNormalizer;
use Shipweb\LineConnect\Interaction\Validator;
use Shipweb\LineConnect\Interaction\InteractionHandler;
use Shipweb\LineConnect\Interaction\RunPolicyEnforcer;
use Shipweb\LineConnect\Interaction\InteractionManager;
use Shipweb\LineConnect\Core\Cron;
use Shipweb\LineConnect\Core\LineConnect;

class InteractionManager_ReminderTest extends InteractionManager_Base {
    public function testSendInteractionSessionsReminders() {
        // prepare
        $interaction_id = self::$interaction_ids['interaction_with_timeout'];
        $this->assertNotEmpty($interaction_id, 'interaction id for timeout interaction is missing');

        $line_user_id = "Ud2be13c6f39c97f05c683d92c696483b";
        $secret_prefix = "04f7";

        // Instantiate services (same pattern as other interaction tests)
        $session_repository = new SessionRepository();
        $action_runner = new ActionRunner();
        $message_builder = new InteractionMessageBuilder();
        $normalizer = new InputNormalizer();
        $validator = new Validator();
        $interaction_handler = new InteractionHandler(
            $session_repository,
            $action_runner,
            $message_builder,
            $normalizer,
            $validator,
            new RunPolicyEnforcer($session_repository)
        );
        $interaction_manager = new InteractionManager(
            $session_repository,
            $interaction_handler
        );
        $mock = function ($testRunner, $httpMethod, $url, $data) {
            /** @var \PHPUnit\Framework\TestCase $testRunner */
            $testRunner->assertEquals('POST', $httpMethod);
            $testRunner->assertEquals('https://api.line.me/v2/bot/message/push', $url);
            return ['status' => 200];
        };
        $httpClient = new LINE\Tests\LINEBot\Util\DummyHttpClient($this, $mock);

        // Start interaction to create a session row
        $interaction_messages = $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix);
        $this->assertNotEmpty($interaction_messages, 'startInteraction did not return messages');

        // Confirm session exists
        $session = $session_repository->find_active($secret_prefix, $line_user_id);
        $this->assertNotNull($session, 'No active session found after startInteraction');
        $this->assertEquals($interaction_id, $session->get_interaction_id());

        // Manipulate DB to simulate time window for reminder:
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnect::TABLE_INTERACTION_SESSIONS;

        $now = time();
        $last_run = $now - 60; // 1 minute ago
        $current_time = $now;

        // Set remind_at to a time between last_run and current_time, and expires_at in the future.
        $remind_at = gmdate('Y-m-d H:i:s', $now - 30); // 30 seconds ago => within (last_run, current_time]
        $expires_at = gmdate('Y-m-d H:i:s', $now + 1200); // 20 minutes in future

        $updated = $wpdb->update(
            $table_name,
            [
                'remind_at' => $remind_at,
                'reminder_sent_at' => null,
                'expires_at' => $expires_at,
            ],
            ['id' => $session->get_id()]
        );
        $this->assertNotFalse($updated, 'Failed to update session row for reminder test');

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $session->get_id()));

        // Call the target method
        $messages = Cron::send_interaction_sessions_reminders($last_run, $current_time, $httpClient);

        // Reload session row and assert reminder_sent_at was set
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $session->get_id()));
        $this->assertNotNull($row, 'Session row disappeared');
        $this->assertNotEmpty($row->reminder_sent_at, 'reminder_sent_at was not updated by send_interaction_sessions_reminders');
        $reminder_sent_at = $row->reminder_sent_at;
        // Also assert that messages array returned from the function is not empty (sanity)
        $this->assertNotEmpty($messages, 'send_interaction_sessions_reminders returned empty messages');
        $this->assertContains($session->get_id(), $messages, 'Session ID was not found in reminder messages');

        // again
        $last_run = $now;
        $current_time = $last_run + 60;
        $messages = Cron::send_interaction_sessions_reminders($last_run, $current_time, $httpClient);
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $session->get_id()));
        $this->assertNotNull($row, 'Session row disappeared');
        $this->assertEquals($reminder_sent_at, $row->reminder_sent_at, 'reminder_sent_at was updated again on second call');
        $this->assertNotNull($row->expires_at, 'expires_at is null');
        $dt = new DateTime($row->expires_at, new DateTimeZone('UTC'));
        $this->assertEquals($expires_at, $dt->format('Y-m-d H:i:s'), 'expires_at was updated again on second call');

        // set user input
        $user_input = 'Any input from user';
        $reply_event = new \stdClass();
        $reply_event->type = 'message';
        $reply_event->replyToken = 'testhandeltoken';
        $reply_event->source = new \stdClass();
        $reply_event->source->type = 'user';
        $reply_event->source->userId = $line_user_id;
        $reply_event->message = new \stdClass();
        $reply_event->message->type = 'text';
        $reply_event->message->text = $user_input;
        $step_messages = $interaction_manager->handleEvent($secret_prefix, $line_user_id, $reply_event);
        // expireが伸びたことを確認
        $this->assertNotEmpty($step_messages, 'handleEvent returned empty messages');
        // var_dump([$session->get_expires_at()->getTimestamp(), new DateTime($expires_at, new DateTimeZone('UTC'))->getTimestamp()]);
        $this->assertLessThan(
            $session->get_expires_at()->getTimestamp(),
            new DateTime($expires_at, new DateTimeZone('UTC'))->getTimestamp(),
            'Session expiration was not extended'
        );
        // remind_atが伸びたことを確認
        $this->assertLessThan(
            $session->get_remind_at()->getTimestamp(),
            new DateTime($row->remind_at, new DateTimeZone('UTC'))->getTimestamp(),
            'Session reminder time was not extended'
        );
        //　remind_atがexpire-10分になっていることを確認
        $this->assertEquals(
            $session->get_expires_at()->getTimestamp() - 600,
            $session->get_remind_at()->getTimestamp(),
            'Session reminder time is not 10 minutes before expiration'
        );

        // check expiration
        $current_time = $session->get_expires_at()->getTimestamp() + 60;
        $last_run = $now - 60;
        $messages = Cron::process_interaction_timeouts($last_run, $current_time, $httpClient);
        $this->assertNotEmpty($messages, 'process_interaction_timeouts returned empty messages');
        $this->assertContains($session->get_id(), $messages, 'Session ID was not found in reminder messages');
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $session->get_id()));
        $this->assertNotNull($row, 'Session row disappeared');
        // status=timeout
        $this->assertEquals('timeout', $row->status, 'Session status is not timeout');
    }
}
// }
