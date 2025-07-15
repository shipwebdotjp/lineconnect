<?php

/*
 * チャネルの送信チェックボックス表示のテストクラス
 * Shipweb\LineConnect\Publish\Post::show_send_checkbox()をテストする
 * @package LineConnect
 */

use Shipweb\LineConnect\Publish\Post as PublishPost;
use Shipweb\LineConnect\Core\LineConnect;

class SavePostTest extends WP_UnitTestCase {
    protected static $post_id;

    public static function wpSetUpBeforeClass($factory) {
        add_action('save_post', array(PublishPost::class, 'save_post'), 10, 2);

        lineconnectTest::init();
    }

    public function test_SavePostSendNotification() {
        // Set user as administrator
        wp_set_current_user(1); // Assuming user ID 1 is an admin
        // Create post object
        $my_post = array(
            'post_title'    => wp_strip_all_tags('Test Post Title'),
            'post_content'  => wp_strip_all_tags('This is a test post content.'),
            'post_status'   => 'publish',
            'post_author'   => 1,
        );
        // set credential
        $_POST[lineconnect::CREDENTIAL_NAME__POST] =  wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        // echo "nonce check" . wp_verify_nonce(lineconnect::CREDENTIAL_ACTION__POST, lineconnect::CREDENTIAL_NAME__POST);
        // set send checkbox
        $_POST[lineconnect::PARAMETER_PREFIX . 'send-checkbox' . '04f7'] = 'ON';
        // set roles
        $_POST[lineconnect::PARAMETER_PREFIX . 'role-selectbox' . '04f7'] = array('teacher', 'student');

        // Insert the post into the database
        $post_id = wp_insert_post($my_post);
        $this->assertGreaterThan(0, $post_id, 'Post should be created successfully');
        $post_meta = get_post_meta($post_id, lineconnect::META_KEY__IS_SEND_LINE, true);
        $this->assertNotEmpty($post_meta, 'Post meta should be set for LINE send status');
        $this->assertArrayHasKey("04f7", $post_meta, 'Post meta should contain the key for the channel');
        $this->assertArrayHasKey("role", $post_meta["04f7"], 'Post meta should contain the key for the role');
        $this->assertSameSets(['teacher', 'student'], $post_meta["04f7"]["role"], 'Post meta should contain the correct roles');

        // transient check
        $get_transient = get_transient(lineconnect::TRANSIENT_KEY__SUCCESS_SEND_TO_LINE);
        // Check if the transient is set
        $this->assertNotEmpty($get_transient, 'Transient should be set after saving post');
        $this->assertStringContainsString("SOHO MIND: Sent a LINE message to 1 person", $get_transient);
    }

    public function test_SavePostNotSendNotification() {
        // Set user as administrator
        wp_set_current_user(1); // Assuming user ID 1 is an admin
        // Create post object
        $my_post = array(
            'post_title'    => wp_strip_all_tags('Test Post Title Not Send'),
            'post_content'  => wp_strip_all_tags('This is a test post content for not sending.'),
            'post_status'   => 'publish',
            'post_author'   => 1,
        );
        // set credential
        $_POST[lineconnect::CREDENTIAL_NAME__POST] =  wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        // set send checkbox
        $_POST[lineconnect::PARAMETER_PREFIX . 'send-checkbox' . '04f7'] = '';
        // set roles
        $_POST[lineconnect::PARAMETER_PREFIX . 'role-selectbox' . '04f7'] = array('teacher', 'student');

        // Insert the post into the database
        $post_id = wp_insert_post($my_post);
        $this->assertGreaterThan(0, $post_id, 'Post should be created successfully');
        $post_meta = get_post_meta($post_id, lineconnect::META_KEY__IS_SEND_LINE, true);
        $this->assertNotEmpty($post_meta, 'Post meta should not be set for LINE send status when not sending');

        // transient check
        $get_transient = get_transient(lineconnect::TRANSIENT_KEY__SUCCESS_SEND_TO_LINE);
        // Check if the transient is not set
        $this->assertEmpty($get_transient, 'Transient should not be set when not sending post');
    }
}
