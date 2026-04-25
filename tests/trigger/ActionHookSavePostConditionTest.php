<?php

use Shipweb\LineConnect\Trigger\ActionHook;
use Shipweb\LineConnect\Core\LineConnect;

class ActionHookSavePostConditionTest extends WP_UnitTestCase {
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
					'args' => array(
						'post_id' => $post_id,
						'post'    => $post,
						'update'  => false,
					),
				),
				array( 'hook' => 'save_post' )
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
					'args' => array(
						'post_id' => $page_id,
						'post'    => $page,
						'update'  => false,
					),
				),
				array( 'hook' => 'save_post' )
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
					'args' => array(
						'post_id' => $attach_id,
						'post'    => $attach,
						'update'  => false,
					),
				),
				array( 'hook' => 'save_post' )
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
					'args' => array(
						'post_id' => $trash_id,
						'post'    => $trash,
						'update'  => false,
					),
				),
				array( 'hook' => 'save_post' )
			)
		);
	}

	public function test_rejects_revision_posts() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		$revision_id = $this->factory->post->create(
			array(
				'post_type'   => 'revision',
				'post_status' => 'inherit',
				'post_parent' => $post_id,
			)
		);

		$revision = get_post( $revision_id );

		$this->assertFalse(
			ActionHook::check_condition(
				array(
					'hook' => 'save_post',
					'args' => array(
						'post_id' => $revision_id,
						'post'    => $revision,
						'update'  => false,
					),
				),
				array()
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
					'args'    => array(
						'post_id' => $post_id,
						'post'    => $post,
						'update'  => false,
					),
				),
				array(
					'hook'      => 'save_post',
					'save_post' => array( 'post_type' => array( 'page' ) ),
				)
			)
		);

		// trigger config that allows post and publish -> true
		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook'    => 'save_post',
					'args'    => array(
						'post_id' => $post_id,
						'post'    => $post,
						'update'  => false,
					),
				),
				array(
					'hook'      => 'save_post',
					'save_post' => array(
						'post_type'   => array( 'post' ),
						'post_status' => array( 'publish' ),
					),
				)
			)
		);
	}
}
