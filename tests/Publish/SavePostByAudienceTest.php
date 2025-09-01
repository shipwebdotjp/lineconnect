<?php

/*
 * チャネルの送信チェックボックス表示のテストクラス
 * Shipweb\LineConnect\Publish\Post::show_send_checkbox()をテストする
 * @package LineConnect
 */
require_once __DIR__ . '/../../tests/LINEBot/Util/DummyHttpClient.php';

use Shipweb\LineConnect\Publish\Post as PublishPost;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Audience\Audience;

class SavePostByAudienceTest extends WP_UnitTestCase {
  protected static $post_id;

  public static function wpSetUpBeforeClass($factory) {
    add_action('save_post', array(PublishPost::class, 'save_post'), 10, 2);
    lineconnectTest::init();
  }

  public function setUp(): void {
    parent::setUp();
    add_filter(LineConnect::FILTER_PREFIX . 'httpclient', function ($httpClient) {
      $mock = function ($testRunner, $httpMethod, $url, $data) {
        return ['status' => 200];
      };
      $dummyHttpClient = new LINE\Tests\LINEBot\Util\DummyHttpClient($this, $mock);
      return $dummyHttpClient;
    });
  }

  public function test_SavePostSendNotificationToAudience() {
    // Set user as administrator
    wp_set_current_user(1); // Assuming user ID 1 is an admin
    // Create post object
    $my_post = array(
      'post_title'    => wp_strip_all_tags('Test Audience Post Title'),
      'post_content'  => wp_strip_all_tags('This is a audience post content.'),
      'post_status'   => 'publish',
      'post_author'   => 1,
    );
    $audience_post = array(
      'post_title'    => wp_strip_all_tags('Test Post Title Audience'),
      'post_content'  => wp_strip_all_tags('This is a test post content for audience.'),
      'post_status'   => 'publish',
      'post_author'   => 1,
    );
    $audience_post_id = wp_insert_post($audience_post);
    $this->assertGreaterThan(0, $audience_post_id, 'Post should be created successfully');

    $audience_data = json_decode('{
  "0": {
    "condition": {
      "conditions": [
        {
          "role": [
            "administrator"
          ],
          "match": "role__in",
          "type": "role"
        }
      ],
      "operator": "and"
    }
  }
}', true);
    update_post_meta($audience_post_id, Audience::META_KEY_DATA, $audience_data);
    update_post_meta($audience_post_id, lineconnect::META_KEY__SCHEMA_VERSION, Audience::SCHEMA_VERSION);

    // set credential
    $_POST[lineconnect::CREDENTIAL_NAME__POST] =  wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
    // echo "nonce check" . wp_verify_nonce(lineconnect::CREDENTIAL_ACTION__POST, lineconnect::CREDENTIAL_NAME__POST);
    // set send checkbox
    $_POST[lineconnect::PARAMETER_PREFIX . 'send-checkbox' . 'audience'] = 'ON';
    // set roles
    $_POST[lineconnect::PARAMETER_PREFIX . 'role-selectbox' . 'audience'] = array($audience_post_id);

    // Insert the post into the database
    $post_id = wp_insert_post($my_post);
    $this->assertGreaterThan(0, $post_id, 'Post should be created successfully');
    $post_meta = get_post_meta($post_id, lineconnect::META_KEY__IS_SEND_LINE, true);
    $this->assertNotEmpty($post_meta, 'Post meta should be set for LINE send status');
    $this->assertArrayHasKey("audience", $post_meta, 'Post meta should contain the key for the channel');
    $this->assertArrayHasKey("role", $post_meta["audience"], 'Post meta should contain the key for the role');
    $this->assertSameSets([$audience_post_id], $post_meta["audience"]["role"], 'Post meta should contain the correct roles');

    // transient check
    $get_transient = get_transient(lineconnect::TRANSIENT_KEY__SUCCESS_SEND_TO_LINE);
    // Check if the transient is set
    $this->assertNotEmpty($get_transient, 'Transient should be set after saving post');
    $this->assertStringContainsString("SOHO MIND: Multicast message sent to 1 person.", $get_transient);
  }
}
