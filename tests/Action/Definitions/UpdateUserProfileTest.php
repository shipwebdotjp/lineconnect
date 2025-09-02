<?php
use Shipweb\LineConnect\Core\LineConnect;

class UpdateUserProfileTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
        // Reset profile for the user before each test
        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_LINE_ID;
        $wpdb->update(
            $table_name,
            ['profile' => json_encode(['initial_key' => 'initial_value'])],
            ['line_id' => 'U_PLACEHOLDER_USERID4e7a9902e5e7d', 'channel_prefix' => '04f7']
        );
        $wpdb->update(
            $table_name,
            ['profile' => json_encode([])],
            ['line_id' => 'U_PLACEHOLDER_USERID4123a772125a1', 'channel_prefix' => '2f38']
        );
    }

    private function get_user_profile($line_user_id, $secret_prefix) {
        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_LINE_ID;
        $profile_json = $wpdb->get_var(
            $wpdb->prepare("SELECT profile FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id, $secret_prefix)
        );
        return json_decode($profile_json, true);
    }

    public function test_update_profile_with_new_value() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserProfile();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));

        $result = $func->update_user_profile('new_key', 'new_value');
        $this->assertSame(1, $result);

        $profile = $this->get_user_profile("U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertArrayHasKey('new_key', $profile);
        $this->assertEquals('new_value', $profile['new_key']);
        $this->assertArrayHasKey('initial_key', $profile); // Ensure existing keys are kept
    }

    public function test_update_profile_with_existing_key() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserProfile();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));

        $result = $func->update_user_profile('initial_key', 'updated_value');
        $this->assertSame(1, $result);

        $profile = $this->get_user_profile("U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertEquals('updated_value', $profile['initial_key']);
    }

    public function test_delete_profile_key() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserProfile();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));

        $result = $func->update_user_profile('initial_key', ''); // Empty value should delete the key
        $this->assertSame(1, $result);

        $profile = $this->get_user_profile("U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertArrayNotHasKey('initial_key', $profile);
    }

    public function test_invalid_user_id() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserProfile();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Unotfound")));

        $result = $func->update_user_profile('any_key', 'any_value');
        $this->assertSame(0, $result); // Should update 0 rows
    }

    public function test_update_profile_for_user_without_profile() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserProfile();
        $func->set_secret_prefix("2f38");
        $func->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4123a772125a1")));

        // This user has an empty profile initially in the test data.
        $result = $func->update_user_profile('first_key', 'first_value');
        $this->assertSame(1, $result);

        $profile = $this->get_user_profile("U_PLACEHOLDER_USERID4123a772125a1", "2f38");
        $this->assertEquals(['first_key' => 'first_value'], $profile);
    }

    public function test_update_profile_with_explicit_user_id() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserProfile();
        // Event source is different from the user being updated
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "some_other_user")));

        $result = $func->update_user_profile('new_key_for_specific_user', 'value', 'U_PLACEHOLDER_USERID4e7a9902e5e7d');
        $this->assertSame(1, $result);

        $profile = $this->get_user_profile("U_PLACEHOLDER_USERID4e7a9902e5e7d", "04f7");
        $this->assertArrayHasKey('new_key_for_specific_user', $profile);
        $this->assertEquals('value', $profile['new_key_for_specific_user']);
    }
}
