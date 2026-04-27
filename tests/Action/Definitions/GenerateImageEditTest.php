<?php

use Shipweb\LineConnect\Action\Definitions\GenerateImageEdit;

class GenerateImageEditTest extends WP_UnitTestCase {
	public function test_edit_image_config() {
		$config = GenerateImageEdit::config();

		$this->assertSame('edit_image', GenerateImageEdit::name());
		$this->assertSame('Edit image', $config['title']);
		$this->assertSame(8070, $config['order']);
		if (count($config['parameters']) !== 9) {
			$names = array_map(function($p) { return $p['name'] ?? 'no-name'; }, $config['parameters']);
			$this->fail("Expected 9 parameters, but got " . count($config['parameters']) . ": " . implode(', ', $names));
		}
		$this->assertSame('prompt', $config['parameters'][0]['name']);
		$this->assertSame('images', $config['parameters'][1]['name']);
		$this->assertSame('mask', $config['parameters'][2]['name']);
		$this->assertSame('size', $config['parameters'][3]['name']);
		$this->assertSame('quality', $config['parameters'][4]['name']);
		$this->assertSame('background', $config['parameters'][5]['name']);
		$this->assertSame('output_format', $config['parameters'][6]['name']);
		$this->assertSame('output_compression', $config['parameters'][7]['name']);
		$this->assertSame('input_fidelity', $config['parameters'][8]['name']);
	}

	public function test_resolve_responses_endpoint_normalizes_chat_completions() {
		$definition = new GenerateImageEdit();
		$method = new ReflectionMethod(GenerateImageEdit::class, 'resolve_responses_endpoint');

		$this->assertSame('https://api.openai.com/v1/responses', $method->invoke($definition, 'https://api.openai.com/v1/chat/completions'));
		$this->assertSame('https://myproxy.com/v1/responses', $method->invoke($definition, 'https://myproxy.com/v1/chat/completions'));
		$this->assertSame('https://myproxy.com/v2/responses', $method->invoke($definition, 'https://myproxy.com/v2/chat/completions'));
		$this->assertSame('https://myproxy.com/v1/responses', $method->invoke($definition, 'https://myproxy.com/v1/responses'));
	}

	public function test_resolve_image_edit_endpoint() {
		$definition = new GenerateImageEdit();
		$method = new ReflectionMethod(GenerateImageEdit::class, 'resolve_responses_endpoint');

		$this->assertSame('https://api.openai.com/v1/responses', $method->invoke($definition, null));
		$this->assertSame('https://api.openai.com/v1/responses', $method->invoke($definition, ''));
		$this->assertSame('https://myproxy.com/v1/responses', $method->invoke($definition, 'https://myproxy.com/v1/chat/completions'));
		$this->assertSame('https://myproxy.com/v1/responses', $method->invoke($definition, 'https://myproxy.com/v1/images/generations'));
		$this->assertSame('https://myproxy.com/v1/responses', $method->invoke($definition, 'https://myproxy.com/v1/responses'));
	}

	public function test_normalize_size_option() {
		$definition = new GenerateImageEdit();
		$method = new ReflectionMethod(GenerateImageEdit::class, 'normalize_size_option');

		$this->assertSame('auto', $method->invoke($definition, 'auto'));
		$this->assertSame('1024x1024', $method->invoke($definition, '1024x1024'));
		$this->assertSame('1024x1024', $method->invoke($definition, 'invalid'));
		$this->assertSame('1536x1024', $method->invoke($definition, '1536x1024'));
	}

	public function test_build_image_response_matches_generate_image_shape() {
		$definition = new GenerateImageEdit();
		$method = new ReflectionMethod(GenerateImageEdit::class, 'build_image_response');

		$response = array(
			'output' => array(
				array(
					'type'           => 'image_generation_call',
					'revised_prompt' => 'revised prompt',
				),
			),
		);
		$saved = array(
			'file_path' => '/tmp/generated-image.png',
			'url'       => 'https://example.com/generated-image.png',
		);
		$thumb = array(
			'file_path' => '/tmp/generated-image-thumb.png',
			'url'       => 'https://example.com/generated-image-thumb.png',
		);

		$result = $method->invoke($definition, 'original prompt', $response, $saved, $thumb, $thumb['url']);

		$this->assertTrue($result['success']);
		$this->assertSame('direct', $result['response_mode']);
		$this->assertCount(1, $result['messages']);
		$this->assertSame('/tmp/generated-image.png', $result['data']['file_path']);
		$this->assertSame('https://example.com/generated-image.png', $result['data']['file_url']);
		$this->assertSame('/tmp/generated-image-thumb.png', $result['data']['thumb_path']);
		$this->assertSame('https://example.com/generated-image-thumb.png', $result['data']['thumb_url']);
		$this->assertSame('original prompt', $result['data']['original_prompt']);
		$this->assertSame('revised prompt', $result['data']['revised_prompt']);
		$this->assertSame('https://example.com/generated-image.png', $result['data']['original_url']);
		$this->assertSame('https://example.com/generated-image-thumb.png', $result['data']['preview_url']);
	}
}
