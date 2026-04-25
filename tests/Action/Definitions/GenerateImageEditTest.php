<?php

use Shipweb\LineConnect\Action\Definitions\GenerateImageEdit;

class GenerateImageEditTest extends WP_UnitTestCase {
	public function test_edit_image_config() {
		$config = GenerateImageEdit::config();

		$this->assertSame('edit_image', GenerateImageEdit::name());
		$this->assertSame('Edit image', $config['title']);
		$this->assertSame(8060, $config['order']);
		$this->assertCount(9, $config['parameters']);
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

	public function test_build_image_edit_request_data_uses_defaults() {
		$definition = new GenerateImageEdit();
		$method = new ReflectionMethod(GenerateImageEdit::class, 'build_image_edit_request_data');

		$images = array(array('image_url' => 'data:image/png;base64,xxxx'));
		$data = $method->invoke($definition, 'add a cat', $images, null, '1024x1024', 'auto', 'auto', 'png', 75, 'high');

		$this->assertSame('gpt-image-1.5', $data['model']);
		$this->assertSame('add a cat', $data['prompt']);
		$this->assertSame($images, $data['images']);
		$this->assertSame('1024x1024', $data['size']);
		$this->assertSame('auto', $data['quality']);
		$this->assertSame('auto', $data['background']);
		$this->assertSame('png', $data['output_format']);
		$this->assertSame('high', $data['input_fidelity']);
		$this->assertArrayNotHasKey('mask', $data);
	}

	public function test_build_image_edit_request_data_includes_mask_and_compression() {
		$definition = new GenerateImageEdit();
		$method = new ReflectionMethod(GenerateImageEdit::class, 'build_image_edit_request_data');

		$images = array(array('image_url' => 'data:image/png;base64,xxxx'));
		$mask = array('image_url' => 'data:image/png;base64,yyyy');
		$data = $method->invoke($definition, 'change color', $images, $mask, '1536x1024', 'high', 'transparent', 'jpeg', 80, 'low');

		$this->assertSame($mask, $data['mask']);
		$this->assertSame('1536x1024', $data['size']);
		$this->assertSame('high', $data['quality']);
		$this->assertSame('transparent', $data['background']);
		$this->assertSame('jpeg', $data['output_format']);
		$this->assertSame(80, $data['output_compression']);
		$this->assertSame('low', $data['input_fidelity']);
	}

	public function test_resolve_image_edit_endpoint() {
		$definition = new GenerateImageEdit();
		$method = new ReflectionMethod(GenerateImageEdit::class, 'resolve_image_edit_endpoint');

		$this->assertSame('https://api.openai.com/v1/images/edits', $method->invoke($definition, null));
		$this->assertSame('https://api.openai.com/v1/images/edits', $method->invoke($definition, ''));

		// Standard OpenAI-like custom endpoint
		$this->assertSame('https://myproxy.com/v1/images/edits', $method->invoke($definition, 'https://myproxy.com/v1/chat/completions'));
		$this->assertSame('https://myproxy.com/v1/images/edits', $method->invoke($definition, 'https://myproxy.com/v1/images/generations'));

		// If it's already edits endpoint
		$this->assertSame('https://myproxy.com/v1/images/edits', $method->invoke($definition, 'https://myproxy.com/v1/images/edits'));
	}

	public function test_normalize_size_option() {
		$definition = new GenerateImageEdit();
		$method = new ReflectionMethod(GenerateImageEdit::class, 'normalize_size_option');

		$this->assertSame('auto', $method->invoke($definition, 'auto'));
		$this->assertSame('1024x1024', $method->invoke($definition, '1024x1024'));
		$this->assertSame('1024x1024', $method->invoke($definition, 'invalid'));
		$this->assertSame('1536x1024', $method->invoke($definition, '1536x1024'));
	}
}
