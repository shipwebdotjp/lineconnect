<?php

use Shipweb\LineConnect\Trigger\ActionHook;

class ActionHookResolveRelatedUserTest extends WP_UnitTestCase {
	protected static $result;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$result = lineconnectTest::init();
	}

	public function setUp(): void {
		parent::setUp();
	}

	public function test_returns_user_id_for_user_register_like_hooks() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user    = get_user_by( 'id', $user_id );
		$this->assertSame(
			$user_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'user_register',
					'args' => array(
						'user_id' => $user_id,
					),
				)
			)
		);

		$this->assertSame(
			$user_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'profile_update',
					'args' => array(
						'user_id' => $user_id,
					),
				)
			)
		);

		$this->assertSame(
			$user_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'delete_user',
					'args' => array(
						'user' => $user,
					),
				)
			)
		);
	}

	public function test_returns_user_id_for_wp_login_when_user_object_is_given() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user    = get_user_by( 'id', $user_id );

		$this->assertSame(
			$user_id,
			ActionHook::resolve_related_user(
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

	public function test_returns_post_author_for_save_post() {
		$author_id = $this->factory->user->create( array( 'role' => 'author' ) );
		$post_id   = $this->factory->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_author' => $author_id,
			)
		);
		$post      = get_post( $post_id );

		$this->assertSame(
			$author_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'save_post',
					'args' => array(
						'post_id' => $post_id,
						'post'    => $post,
						'update'  => false,
					),
				)
			)
		);

		$this->assertSame(
			$author_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'save_post',
					'args' => array(
						'post_id' => $post_id,
						'update'  => false,
					),
				)
			)
		);
	}

	public function test_returns_comment_user_or_context_user_for_comment_post() {
		$comment_user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$post_id         = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$comment_id      = $this->factory->comment->create(
			array(
				'comment_post_ID' => $post_id,
				'user_id'         => $comment_user_id,
			)
		);

		$this->assertSame(
			$comment_user_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'comment_post',
					'args' => array(
						'comment_id'       => $comment_id,
						'comment_approved' => 1,
					),
				)
			)
		);

		$this->assertSame(
			123,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'comment_post',
					'args' => array(
						'comment_context' => array(
							'user_id' => 123,
						),
					),
				)
			)
		);
	}

	public function test_uses_current_user_for_logout_and_plugin_hooks() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->assertSame(
			$user_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'wp_logout',
					'args' => array(
						'user_id' => $user_id,
					),
				)
			)
		);

		$this->assertSame(
			$user_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'activated_plugin',
					'args' => array(
						'plugin' => 'sample/sample.php',
					),
				)
			)
		);

		$this->assertSame(
			$user_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'switch_theme',
					'args' => array(
						'new_name' => 'sample',
					),
				)
			)
		);
	}

	public function test_returns_current_user_for_default_hooks_and_zero_when_user_is_missing() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->assertSame(
			$user_id,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'some_custom_hook',
					'args' => array(),
				)
			)
		);

		$this->assertSame(
			0,
			ActionHook::resolve_related_user(
				array(
					'hook' => 'wp_login',
					'args' => array(
						'user_login' => 'missing-user',
					),
				)
			)
		);
	}
}
