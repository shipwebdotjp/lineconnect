<?php

use Shipweb\LineConnect\Trigger\ActionHook;

class ActionHookProcessAudienceBranchTestDouble extends ActionHook {
	public static $audience_condition   = null;
	public static $audience_result      = array();
	public static $audience_flow_called = false;
	public static $direct_action_called = false;
	public static $audience_flow_args   = array();
	public static $direct_action_args   = array();

	public static function reset_state() {
		self::$audience_condition   = null;
		self::$audience_result      = array();
		self::$audience_flow_called = false;
		self::$direct_action_called = false;
		self::$audience_flow_args   = array();
		self::$direct_action_args   = array();
	}

	protected static function get_action_hook_triggers( $hook_name, array $action_hook_args = array() ): array {
		if ( isset( $action_hook_args['trigger'] ) && is_array( $action_hook_args['trigger'] ) ) {
			return array(
				array(
					'post_id' => 1,
					'trigger' => $action_hook_args['trigger'],
				),
			);
		}

		return array();
	}

	protected static function get_audience_by_condition( array $condition ): array {
		self::$audience_condition = $condition;
		return self::$audience_result;
	}

	protected static function execute_audience_actionflow( array $trigger, array $recepient, array $action_hook_args = array() ) {
		self::$audience_flow_called = true;
		self::$audience_flow_args   = array(
			'trigger'          => $trigger,
			'recepient'        => $recepient,
			'action_hook_args' => $action_hook_args,
		);

		return array(
			'success' => true,
		);
	}

	protected static function execute_direct_action( array $trigger, array $action_hook_args = array() ) {
		self::$direct_action_called = true;
		self::$direct_action_args   = array(
			'trigger'          => $trigger,
			'action_hook_args' => $action_hook_args,
		);

		return array(
			'success' => true,
		);
	}
}

class ActionHookProcessAudienceBranchTest extends WP_UnitTestCase {
	protected static $result;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$result = lineconnectTest::init();
	}

	public function setUp(): void {
		parent::setUp();
		ActionHookProcessAudienceBranchTestDouble::reset_state();
	}

	public function test_current_user_mode_uses_audience_flow_when_receipient_exists() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'author',
			)
		);
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_author' => $user_id,
			)
		);
		$post    = get_post( $post_id );

		ActionHookProcessAudienceBranchTestDouble::$audience_result = array(
			'abcd' => array(
				'type'          => 'multicast',
				'line_user_ids' => array( 'U123' ),
			),
		);

		$result = ActionHookProcessAudienceBranchTestDouble::process(
			array(
				'hook'    => 'save_post',
				'args'    => array(
					'post_id' => $post_id,
					'post'    => $post,
					'update'  => false,
				),
				'trigger' => array(
					'hook'                  => 'save_post',
					'audience_mode'         => 'current_user',
					'current_user_channels' => array( 'abcd' ),
					'save_post'             => array(),
					'action'                => array(),
					'chain'                 => array(),
				),
			)
		);

		$this->assertTrue( $result );
		$this->assertSame(
			array(
				'conditions' => array(
					array(
						'type'     => 'wpUserId',
						'wpUserId' => array( $user_id ),
					),
					array(
						'type'          => 'channel',
						'secret_prefix' => array( 'abcd' ),
					),
				),
				'operator'   => 'and',
			),
			ActionHookProcessAudienceBranchTestDouble::$audience_condition
		);
		$this->assertTrue( ActionHookProcessAudienceBranchTestDouble::$audience_flow_called );
		$this->assertFalse( ActionHookProcessAudienceBranchTestDouble::$direct_action_called );
	}

	public function test_standard_mode_uses_saved_audience_and_falls_back_to_direct_action_when_receipient_is_empty() {
		ActionHookProcessAudienceBranchTestDouble::$audience_result = array();

		$saved_condition = array(
			'conditions' => array(
				array(
					'type'          => 'channel',
					'secret_prefix' => array( 'abcd' ),
				),
			),
			'operator'   => 'or',
		);

		$result = ActionHookProcessAudienceBranchTestDouble::process(
			array(
				'hook'    => 'wp_login',
				'args'    => array(
					'user_login' => 'missing-user',
					'user'       => null,
				),
				'trigger' => array(
					'hook'          => 'wp_login',
					'audience_mode' => 'standard',
					'audience'      => array(
						'condition' => $saved_condition,
					),
					'wp_login'      => array(
						'role' => array(),
					),
					'action'        => array(),
					'chain'         => array(),
				),
			)
		);

		$this->assertTrue( $result );
		$this->assertNull( ActionHookProcessAudienceBranchTestDouble::$audience_condition );
		$this->assertFalse( ActionHookProcessAudienceBranchTestDouble::$audience_flow_called );
		$this->assertTrue( ActionHookProcessAudienceBranchTestDouble::$direct_action_called );
	}

	public function test_current_user_mode_falls_back_to_direct_action_when_related_user_is_zero() {
		$result = ActionHookProcessAudienceBranchTestDouble::process(
			array(
				'hook'    => 'wp_logout',
				'args'    => array(),
				'trigger' => array(
					'hook'                  => 'wp_logout',
					'audience_mode'         => 'current_user',
					'current_user_channels' => array( 'abcd' ),
					'action'                => array(),
					'chain'                 => array(),
				),
			)
		);

		$this->assertTrue( $result );
		$this->assertNull( ActionHookProcessAudienceBranchTestDouble::$audience_condition );
		$this->assertFalse( ActionHookProcessAudienceBranchTestDouble::$audience_flow_called );
		$this->assertTrue( ActionHookProcessAudienceBranchTestDouble::$direct_action_called );
	}
}
