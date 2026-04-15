<?php

use Shipweb\LineConnect\Trigger\ActionHook;
use Shipweb\LineConnect\Core\LineConnect;

class TestActionHookSavePostCondition extends WP_UnitTestCase {
	protected static $result;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$result = lineconnectTest::init();
	}

	public function setUp(): void {
		parent::setUp();
	}

	public function test_default_accepts_post_and_page_publish() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);
		$post    = get_post( $post_id );

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'save_post',
					'args' => array( $post_id, $post, false ),
				)
			)
		);

		$page_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		$page    = get_post( $page_id );

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'save_post',
					'args' => array( $page_id, $page, false ),
				)
			)
		);
	}

	public function test_default_rejects_unlisted_post_type_and_status() {
		// attachment as non-default post type
		$attach_id = $this->factory->post->create(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'publish',
			)
		);
		$attach    = get_post( $attach_id );
		$this->assertFalse(
			ActionHook::check_condition(
				array(
					'hook' => 'save_post',
					'args' => array( $attach_id, $attach, false ),
				)
			)
		);

		// trash status should be excluded by default
		$trash_id = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'trash',
			)
		);
		$trash    = get_post( $trash_id );
		$this->assertFalse(
			ActionHook::check_condition(
				array(
					'hook' => 'save_post',
					'args' => array( $trash_id, $trash, false ),
				)
			)
		);
	}

	public function test_respects_trigger_config_post_type_and_status() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);
		$post    = get_post( $post_id );

		// trigger config that restricts to page -> should be false for post
		$this->assertFalse(
			ActionHook::check_condition(
				array(
					'hook'    => 'save_post',
					'args'    => array( $post_id, $post, false ),
					'trigger' => array( 'save_post' => array( 'post_type' => array( 'page' ) ) ),
				)
			)
		);

		// trigger config that allows post and publish -> true
		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook'    => 'save_post',
					'args'    => array( $post_id, $post, false ),
					'trigger' => array(
						'save_post' => array(
							'post_type'   => array( 'post' ),
							'post_status' => array( 'publish' ),
						),
					),
				)
			)
		);
	}
}
