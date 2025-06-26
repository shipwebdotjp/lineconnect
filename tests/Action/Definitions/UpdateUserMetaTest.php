<?php

use Shipweb\LineConnect\Action\Definitions\UpdateUserMeta;

class UpdateUserMetaTest extends WP_UnitTestCase {
    protected static $result;

    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_update_existing_user_meta() {
        $user_id = 2; // testadmin
        $meta_key = 'first_name';
        $new_value = 'Jiro';

        $action = new UpdateUserMeta();
        $result = $action->update_user_meta($user_id, $meta_key, $new_value);

        $this->assertTrue($result);
        $this->assertEquals($new_value, get_user_meta($user_id, $meta_key, true));
    }

    public function test_add_new_user_meta() {
        $user_id = 2;
        $meta_key = 'new_custom_meta_key';
        $meta_value = 'custom_value';

        $action = new UpdateUserMeta();
        $result = $action->update_user_meta($user_id, $meta_key, $meta_value);

        $this->assertTrue($result);
        $this->assertEquals($meta_value, get_user_meta($user_id, $meta_key, true));
    }

    public function test_delete_user_meta() {
        $user_id = 2;
        $meta_key = 'description';
        // Add a value first to ensure it exists before deletion
        update_user_meta($user_id, $meta_key, 'Some description');
        $this->assertEquals('Some description', get_user_meta($user_id, $meta_key, true));

        $action = new UpdateUserMeta();
        // Passing an empty string for the value should trigger deletion
        $result = $action->update_user_meta($user_id, $meta_key, '');

        $this->assertTrue($result);
        $this->assertEmpty(get_user_meta($user_id, $meta_key, true));
    }

    public function test_update_meta_for_non_existent_user() {
        $user_id = 99999; // A user that does not exist
        $meta_key = 'any_key';
        $meta_value = 'any_value';

        $action = new UpdateUserMeta();
        $result = $action->update_user_meta($user_id, $meta_key, $meta_value);

        // update_user_meta returns false for non-existent user_id
        $this->assertFalse($result);
    }
}
