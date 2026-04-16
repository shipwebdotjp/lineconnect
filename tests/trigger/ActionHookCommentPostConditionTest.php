<?php

use Shipweb\LineConnect\Trigger\ActionHook;

class ActionHookCommentPostConditionTest extends WP_UnitTestCase {
	protected static $result;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$result = lineconnectTest::init();
	}

	public function setUp(): void {
		parent::setUp();
	}

	public function test_default_allows_comments_for_post_type_post() {
		$post_id    = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $post_id ) );

		$this->assertTrue(
			ActionHook::check_condition(
				array(
					'hook' => 'comment_post',
					'args' => array(
						'comment_id'       => $comment_id,
						'comment_approved' => 1,
					),
				)
			)
		);
	}

	public function test_filters_by_trigger_post_type() {
		$post_id    = $this->factory->post->create( array( 'post_type' => 'page' ) );
		$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $post_id ) );

		$this->assertFalse(
			ActionHook::check_condition(
				array(
					'hook'    => 'comment_post',
					'args'    => array(
						'comment_id'       => $comment_id,
						'comment_approved' => 1,
					),
					'trigger' => array( 'comment_post' => array( 'post_type' => array( 'post' ) ) ),
				)
			)
		);
	}
}
