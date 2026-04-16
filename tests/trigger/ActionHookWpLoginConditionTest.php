<?php

use Shipweb\LineConnect\Trigger\ActionHook;

class ActionHookWpLoginConditionTest extends WP_UnitTestCase {
	protected static $result;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$result = lineconnectTest::init();
	}

	public function setUp(): void {
		parent::setUp();
	}

	public function test_default_allows_when_no_role_filter() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user    = get_user_by( 'id', $user_id );

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'wp_login',
					'args' => array(
						'user_login' => $user->user_login,
						'user'       => $user,
					),
				)
			)
		);
	}

	public function test_filters_by_role_with_user_object() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user    = get_user_by( 'id', $user_id );

		$this->assertFalse(
			ActionHook::check_condition(
				array(
					'hook'    => 'wp_login',
					'args'    => array(
						'user_login' => $user->user_login,
						'user'       => $user,
					),
					'trigger' => array( 'wp_login' => array( 'role' => array( 'administrator' ) ) ),
				)
			)
		);
	}

	public function test_user_lookup_by_login_string() {
		$user_id = $this->factory->user->create(
			array(
				'role'       => 'administrator',
				'user_login' => 'bob',
			)
		);

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook'    => 'wp_login',
					'args'    => array(
						'user_login' => 'bob',
					),
					'trigger' => array( 'wp_login' => array( 'role' => array( 'administrator' ) ) ),
				)
			)
		);
	}
}
