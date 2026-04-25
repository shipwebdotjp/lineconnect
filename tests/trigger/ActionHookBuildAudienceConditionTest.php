<?php

use Shipweb\LineConnect\Trigger\ActionHook;
class ActionHookBuildAudienceConditionTestDouble extends ActionHook {
	public static $related_user_calls = 0;
	public static $related_user_id    = 0;

	public static function reset_state() {
		self::$related_user_calls = 0;
		self::$related_user_id    = 0;
	}

	public static function resolve_related_user( array $action_hook_args ): int {
		self::$related_user_calls++;
		return self::$related_user_id;
	}
}


class ActionHookBuildAudienceConditionTest extends WP_UnitTestCase {
	protected static $result;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$result = lineconnectTest::init();
	}

	public function setUp(): void {
		parent::setUp();
		ActionHookBuildAudienceConditionTestDouble::reset_state();
	}

	public function test_current_user_mode_builds_wpuserid_and_channel_conditions() {
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
		$post = get_post( $post_id );
		ActionHookBuildAudienceConditionTestDouble::$related_user_id = $user_id;

		$condition = ActionHookBuildAudienceConditionTestDouble::build_audience_condition(
			array(
				'hook'    => 'save_post',
				'args'    => array(
					'post_id' => $post_id,
					'post'    => $post,
					'update'  => false,
				),
			),
			array(
				'hook'                  => 'save_post',
				'audience_mode'         => 'current_user',
				'current_user_channels' => array( 'abcd', 'efgh' ),
				'save_post'             => array(),
			)
		);

		$this->assertSame(
			array(
				'conditions' => array(
					array(
						'type'     => 'wpUserId',
						'wpUserId' => array( $user_id ),
					),
					array(
						'type'          => 'channel',
						'secret_prefix' => array( 'abcd', 'efgh' ),
					),
				),
				'operator'   => 'and',
			),
			$condition
		);
		$this->assertSame( 1, ActionHookBuildAudienceConditionTestDouble::$related_user_calls );
	}

	public function test_current_user_mode_returns_empty_array_when_related_user_is_zero() {
		$condition = ActionHookBuildAudienceConditionTestDouble::build_audience_condition(
			array(
				'hook' => 'wp_login',
				'args' => array(
					'user_login' => 'missing-user',
				),
			),
			array(
				'hook'                  => 'wp_login',
				'audience_mode'         => 'current_user',
				'current_user_channels' => array( 'abcd' ),
				'wp_login'              => array(
					'role' => array(),
				),
			),
		);

		$this->assertSame( array(), $condition );
		$this->assertSame( 1, ActionHookBuildAudienceConditionTestDouble::$related_user_calls );
	}

	public function test_standard_mode_returns_saved_audience_condition() {
		$saved_condition = array(
			'conditions' => array(
				array(
					'type'          => 'channel',
					'secret_prefix' => array( 'abcd' ),
				),
			),
			'operator'   => 'or',
		);

		$condition = ActionHookBuildAudienceConditionTestDouble::build_audience_condition(
			array(
				'hook' => 'wp_login',
				'args' => array(),
			),
			array(
				'hook'          => 'wp_login',
				'audience_mode' => 'standard',
				'audience'      => array(
					'condition' => $saved_condition,
				),
			)
		);

		$this->assertSame( $saved_condition, $condition );
		$this->assertSame( 0, ActionHookBuildAudienceConditionTestDouble::$related_user_calls );
	}

	public function test_unknown_mode_returns_empty_array() {
		$condition = ActionHookBuildAudienceConditionTestDouble::build_audience_condition(
			array(
				'hook' => 'wp_login',
				'args' => array(),
			),
			array(
				'hook'          => 'wp_login',
				'audience_mode' => 'unexpected',
			)
		);

		$this->assertSame( array(), $condition );
		$this->assertSame( 0, ActionHookBuildAudienceConditionTestDouble::$related_user_calls );
	}
}
