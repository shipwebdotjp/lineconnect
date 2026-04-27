<?php

use Shipweb\LineConnect\Action\Definitions\GenerateImage;
use Shipweb\LineConnect\Utilities\Schema;

class GenerateImageTest extends WP_UnitTestCase {
	public static function wpSetUpBeforeClass( $factory ) {
		lineconnectTest::init();
	}

	public function test_generate_image_config() {
		$config = GenerateImage::config();

		$this->assertSame( 'generate_image', GenerateImage::name() );
		$this->assertSame( 'Generate image', $config['title'] );
		$this->assertSame( 8050, $config['order'] );
		$this->assertCount( 6, $config['parameters'] );
		$this->assertSame( 'prompt', $config['parameters'][0]['name'] );
		$this->assertTrue( $config['parameters'][0]['required'] );
		$this->assertSame( 'auto', $config['parameters'][1]['default'] );
		$this->assertSame( 'auto', $config['parameters'][2]['default'] );
		$this->assertSame( 'auto', $config['parameters'][3]['default'] );
		$this->assertSame( 'png', $config['parameters'][4]['default'] );
		$this->assertSame( 75, $config['parameters'][5]['default'] );
	}

	public function test_build_image_request_data_uses_defaults() {
		$definition = new GenerateImage();
		$method     = new ReflectionMethod( GenerateImage::class, 'build_image_request_data' );

		$data = $method->invoke( $definition, 'a cat in a hat', null, null, null, null, null );

		$this->assertSame( 'gpt-image-2', $data['model'] );
		$this->assertSame( 'a cat in a hat', $data['prompt'] );
		$this->assertSame( 'auto', $data['size'] );
		$this->assertSame( 'auto', $data['quality'] );
		$this->assertSame( 'auto', $data['background'] );
		$this->assertSame( 'png', $data['output_format'] );
		$this->assertArrayNotHasKey( 'output_compression', $data );
	}

	public function test_build_image_request_data_includes_compression_for_jpeg() {
		$definition = new GenerateImage();
		$method     = new ReflectionMethod( GenerateImage::class, 'build_image_request_data' );

		$data = $method->invoke( $definition, 'a cat', '1024x1024', 'high', 'opaque', 'jpeg', 40 );

		$this->assertSame( '1024x1024', $data['size'] );
		$this->assertSame( 'high', $data['quality'] );
		$this->assertSame( 'opaque', $data['background'] );
		$this->assertSame( 'jpeg', $data['output_format'] );
		$this->assertSame( 40, $data['output_compression'] );
	}

	public function test_resolve_output_spec_maps_formats() {
		$definition = new GenerateImage();
		$method     = new ReflectionMethod( GenerateImage::class, 'resolve_output_spec' );

		$this->assertSame(
			array(
				'mime_type' => 'image/png',
				'extension' => 'png',
			),
			$method->invoke( $definition, 'png' )
		);
		$this->assertSame(
			array(
				'mime_type' => 'image/jpeg',
				'extension' => 'jpg',
			),
			$method->invoke( $definition, 'jpeg' )
		);
		$this->assertSame(
			array(
				'mime_type' => 'image/webp',
				'extension' => 'webp',
			),
			$method->invoke( $definition, 'webp' )
		);
	}

	public function test_schema_preserves_default_values() {
		$parameter_schema = Schema::get_parameter_schema( 'size', GenerateImage::config()['parameters'][1] );

		$this->assertSame( 'auto', $parameter_schema['default'] );
		$this->assertContains( 'string', $parameter_schema['type'] );
	}

	/**
	 * @dataProvider provide_valid_sizes
	 */
	public function test_validate_size_option_accepts_valid_sizes( $input, $expected ) {
		$definition = new GenerateImage();
		$method     = new ReflectionMethod( GenerateImage::class, 'validate_size_option' );

		$result = $method->invoke( $definition, $input );

		$this->assertTrue( $result['valid'] );
		$this->assertSame( $expected, $result['value'] );
	}

	/**
	 * @dataProvider provide_invalid_sizes
	 */
	public function test_validate_size_option_rejects_invalid_sizes( $input ) {
		$definition = new GenerateImage();
		$method     = new ReflectionMethod( GenerateImage::class, 'validate_size_option' );

		$result = $method->invoke( $definition, $input );

		$this->assertFalse( $result['valid'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function provide_valid_sizes() {
		return array(
			array( 'auto', 'auto' ),
			array( '1024x1024', '1024x1024' ),
			array( '1536x1024', '1536x1024' ),
			array( '3840x1280', '3840x1280' ),
			array( ' 1024x1536 ', '1024x1536' ),
		);
	}

	public function provide_invalid_sizes() {
		return array(
			array( '1025x1024' ),
			array( '4000x1024' ),
			array( '3840x1279' ),
			array( '640x640' ),
			array( '3840x2304' ),
			array( '1024' ),
			array( 'autoe' ),
		);
	}
}
