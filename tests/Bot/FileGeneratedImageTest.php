<?php

use Shipweb\LineConnect\Bot\File;
use Shipweb\LineConnect\Bot\Media\Image;

class FileGeneratedImageTest extends WP_UnitTestCase {
	public static function wpSetUpBeforeClass( $factory ) {
		lineconnectTest::init();
	}

	public function test_save_generated_image_creates_public_file() {
		$secret_prefix = 'testprefix';
		$file_name     = 'sample.png';

		$result = Image::saveGeneratedImage( $secret_prefix, 'binary-content', 'image/png', 'png', $file_name );
		var_dump( $result );
		$this->assertIsArray( $result );
		$this->assertSame( 'sample.png', basename( $result['file_path'] ) );
		$this->assertStringContainsString( 'generated/' . $secret_prefix . '/' . gmdate( 'Y/m' ) . '/image/', $result['file_path'] );
		$this->assertStringContainsString( '/lineconnect/generated/' . $secret_prefix . '/' . gmdate( 'Y/m' ) . '/image/', $result['url'] );
		$this->assertFileExists( $result['full_path'] );

		if ( file_exists( $result['full_path'] ) ) {
			unlink( $result['full_path'] );
		}
	}
}
