<?php

use Shipweb\LineConnect\Trigger\ActionHook;

class ActionHookBuildAudienceConditionTest extends WP_UnitTestCase {
	protected static $result;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$result = lineconnectTest::init();
	}

	public function setUp(): void {
		parent::setUp();
	}

	public function test_current_user_mode_builds_wpuserid_and_channel_conditions() {
		$condition = ActionHook::build_audience_condition(
			array(
				'hook'    => 'save_post',
				'trigger' => array(
					'audience_mode'         => 'current_user',
					'current_user_channels' => array( 'abcd', 'efgh' ),
				),
			),
			123
		);

		$this->assertSame(
			array(
				'conditions' => array(
					array(
						'type'     => 'wpUserId',
						'wpUserId' => array( 123 ),
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
	}

	public function test_current_user_mode_returns_empty_array_when_related_user_is_zero() {
		$condition = ActionHook::build_audience_condition(
			array(
				'trigger' => array(
					'audience_mode'         => 'current_user',
					'current_user_channels' => array( 'abcd' ),
				),
			),
			0
		);

		$this->assertSame( array(), $condition );
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

		$condition = ActionHook::build_audience_condition(
			array(
				'trigger' => array(
					'audience_mode' => 'standard',
					'audience'      => array(
						'condition' => $saved_condition,
					),
				),
			),
			999
		);

		$this->assertSame( $saved_condition, $condition );
	}

	public function test_unknown_mode_returns_empty_array() {
		$condition = ActionHook::build_audience_condition(
			array(
				'trigger' => array(
					'audience_mode' => 'unexpected',
				),
			),
			123
		);

		$this->assertSame( array(), $condition );
	}
}
