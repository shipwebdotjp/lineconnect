<?php

use Shipweb\LineConnect\Action\Definitions\GenerateSpeech;

class GenerateSpeechTest extends WP_UnitTestCase {
	public static function wpSetUpBeforeClass($factory) {
		if (method_exists('lineconnectTest', 'init')) {
			lineconnectTest::init();
		}
	}

	public function test_generate_speech_config() {
		$config = GenerateSpeech::config();

		$this->assertSame('generate_speech', GenerateSpeech::name());
		$this->assertSame('Generate speech', $config['title']);
		$this->assertSame(8060, $config['order']);
		$this->assertCount(4, $config['parameters']);
		$this->assertSame('input', $config['parameters'][0]['name']);
		$this->assertTrue($config['parameters'][0]['required']);
		$this->assertSame('marin', $config['parameters'][1]['default']);
		$this->assertSame('mp3', $config['parameters'][3]['default']);
	}

	public function test_build_speech_request_data_uses_defaults() {
		$definition = new GenerateSpeech();
		$method = new ReflectionMethod(GenerateSpeech::class, 'build_speech_request_data');
		$method->setAccessible(true);

		$data = $method->invoke($definition, 'Hello world', 'marin', null, 'mp3');

		$this->assertSame('gpt-4o-mini-tts', $data['model']);
		$this->assertSame('Hello world', $data['input']);
		$this->assertSame('marin', $data['voice']);
		$this->assertSame('mp3', $data['response_format']);
		$this->assertArrayNotHasKey('instructions', $data);
	}

	public function test_build_speech_request_data_includes_instructions() {
		$definition = new GenerateSpeech();
		$method = new ReflectionMethod(GenerateSpeech::class, 'build_speech_request_data');
		$method->setAccessible(true);

		$data = $method->invoke($definition, 'Hello', 'ash', 'Speak slowly', 'mp3');

		$this->assertSame('ash', $data['voice']);
		$this->assertSame('Speak slowly', $data['instructions']);
	}

	public function test_resolve_audio_output_spec_maps_formats() {
		$definition = new GenerateSpeech();
		$method = new ReflectionMethod(GenerateSpeech::class, 'resolve_audio_output_spec');
		$method->setAccessible(true);

		$this->assertSame(
			array('mime_type' => 'audio/mpeg', 'extension' => 'mp3'),
			$method->invoke($definition, 'mp3')
		);
	}

	/**
	 * @dataProvider provide_endpoints
	 */
	public function test_resolve_speech_endpoint($input, $expected) {
		$definition = new GenerateSpeech();
		$method = new ReflectionMethod(GenerateSpeech::class, 'resolve_speech_endpoint');
		$method->setAccessible(true);

		$this->assertSame($expected, $method->invoke($definition, $input));
	}

	public function provide_endpoints() {
		return array(
			array(null, 'https://api.openai.com/v1/audio/speech'),
			array('', 'https://api.openai.com/v1/audio/speech'),
			array('https://api.openai.com/v1', 'https://api.openai.com/v1/audio/speech'),
			array('https://api.openai.com/v1/', 'https://api.openai.com/v1/audio/speech'),
			array('https://api.openai.com/v1/images/generations', 'https://api.openai.com/v1/audio/speech'),
			array('https://api.openai.com/v1/chat/completions', 'https://api.openai.com/v1/audio/speech'),
			array('https://example-proxy.local/openai/v1', 'https://example-proxy.local/openai/v1/audio/speech'),
		);
	}
}
