<?php

use Shipweb\LineConnect\Bot\Provider\OpenAi;

class OpenAiTest extends WP_UnitTestCase {
	public static function wpSetUpBeforeClass( $factory ) {
		lineconnectTest::init();
	}

	public function test_build_context_messages_from_conversation_for_user_supports_mixed_message_types() {
		$open_ai = new OpenAi();
		$conversation = (object) array(
			'source_type' => 1,
			'message'     => wp_json_encode(
				array(
					(object) array(
						'type' => 'text',
						'text' => 'Hello',
					),
					(object) array(
						'type'               => 'image',
						'originalContentUrl' => 'https://example.com/image.jpg',
					),
					(object) array(
						'type'               => 'audio',
						'originalContentUrl' => 'https://example.com/audio.m4a',
					),
				)
			),
		);

		$messages = $open_ai->build_context_messages_from_conversation( $conversation );

		$this->assertCount( 1, $messages );
		$this->assertSame( 'user', $messages[0]['role'] );
		$this->assertIsArray( $messages[0]['content'] );
		$this->assertSame( 'text', $messages[0]['content'][0]['type'] );
		$this->assertSame( 'Hello', $messages[0]['content'][0]['text'] );
		$this->assertSame( 'text', $messages[0]['content'][1]['type'] );
		$this->assertSame( 'Image URL: https://example.com/image.jpg', $messages[0]['content'][1]['text'] );
		$this->assertSame( 'image_url', $messages[0]['content'][2]['type'] );
		$this->assertSame( 'https://example.com/image.jpg', $messages[0]['content'][2]['image_url']['url'] );
		$this->assertSame( 'text', $messages[0]['content'][3]['type'] );
		$this->assertSame( 'Audio URL: https://example.com/audio.m4a', $messages[0]['content'][3]['text'] );
	}

	public function test_build_context_messages_from_conversation_for_assistant_collapses_to_text_only() {
		$open_ai = new OpenAi();
		$conversation = (object) array(
			'source_type' => 11,
			'message'     => wp_json_encode(
				array(
					(object) array(
						'type' => 'text',
						'text' => 'Hello',
					),
					(object) array(
						'type'               => 'image',
						'originalContentUrl' => 'https://example.com/image.jpg',
					),
					(object) array(
						'type'               => 'audio',
						'originalContentUrl' => 'https://example.com/audio.m4a',
					),
				)
			),
		);

		$messages = $open_ai->build_context_messages_from_conversation( $conversation );

		$this->assertCount( 1, $messages );
		$this->assertSame( 'assistant', $messages[0]['role'] );
		$this->assertIsString( $messages[0]['content'] );
		$this->assertSame(
			"Hello\nImage URL: https://example.com/image.jpg\nAudio URL: https://example.com/audio.m4a",
			$messages[0]['content']
		);
	}
}
