<?php

use Shipweb\LineConnect\Action\Definitions\GenerateImage;

class GenerateImageTest extends WP_UnitTestCase {
	public static function wpSetUpBeforeClass($factory) {
		lineconnectTest::init();
	}

	public function test_generate_image_config() {
		$config = GenerateImage::config();

		$this->assertSame('generate_image', GenerateImage::name());
		$this->assertSame('Generate image', $config['title']);
		$this->assertSame(8050, $config['order']);
		$this->assertCount(1, $config['parameters']);
		$this->assertSame('prompt', $config['parameters'][0]['name']);
		$this->assertTrue($config['parameters'][0]['required']);
	}
}
