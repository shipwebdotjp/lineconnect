<?php

use Shipweb\LineConnect\Trigger\ActionHook;

class ActionHookUserPluginThemeConditionTest extends WP_UnitTestCase {
	protected static $result;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$result = lineconnectTest::init();
	}

	public function setUp(): void {
		parent::setUp();
	}

	public function test_user_register_filters_by_role() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'user_register',
					'args' => array(
						'user_id' => $user_id,
					),
				),
				array(
					'hook'          => 'user_register',
					'user_register' => array(
						'role' => array( 'subscriber' ),
					),
				)
			)
		);

		$this->assertFalse(
			ActionHook::check_condition(
				array(
					'hook' => 'user_register',
					'args' => array(
						'user_id' => $user_id,
					),
				),
				array(
					'hook'          => 'user_register',
					'user_register' => array(
						'role' => array( 'administrator' ),
					),
				)
			)
		);
	}

	public function test_profile_update_filters_by_role() {
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'profile_update',
					'args' => array(
						'user_id' => $user_id,
					),
				),
				array(
					'hook'           => 'profile_update',
					'profile_update' => array(
						'role' => array( 'editor' ),
					),
				)
			)
		);
	}

	public function test_delete_user_filters_by_role_with_user_object() {
		$user_id = $this->factory->user->create( array( 'role' => 'author' ) );
		$user    = get_user_by( 'id', $user_id );

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'delete_user',
					'args' => array(
						'id'   => $user_id,
						'user' => $user,
					),
				),
				array(
					'hook'        => 'delete_user',
					'delete_user' => array(
						'role' => array( 'author' ),
					),
				)
			)
		);
	}

	public function test_wp_logout_filters_by_role() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'wp_logout',
					'args' => array(
						'user_id' => $user_id,
					),
				),
				array(
					'hook'      => 'wp_logout',
					'wp_logout' => array(
						'role' => array( 'administrator' ),
					),
				)
			)
		);
	}

	public function test_plugin_and_theme_conditions_match_configured_value() {
		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'activated_plugin',
					'args' => array(
						'plugin' => 'sample/sample.php',
					),
				),
				array(
					'hook'             => 'activated_plugin',
					'activated_plugin' => array(
						'plugin' => array( 'sample/sample.php' ),
					),
				)
			)
		);

		$this->assertFalse(
			ActionHook::check_condition(
				array(
					'hook' => 'deactivated_plugin',
					'args' => array(
						'plugin' => 'sample/sample.php',
					),
				),
				array(
					'hook'               => 'deactivated_plugin',
					'deactivated_plugin' => array(
						'plugin' => array( 'another/another.php' ),
					),
				)
			)
		);

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'switch_theme',
					'args' => array(
						'new_name' => 'twentytwentyfour',
					),
				),
				array(
					'hook'         => 'switch_theme',
					'switch_theme' => array(
						'theme' => array( 'twentytwentyfour' ),
					),
				)
			)
		);
	}
}