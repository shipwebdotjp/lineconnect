<?php

use Shipweb\LineConnect\Utilities\Logging;

class UtilLoggingTest extends WP_UnitTestCase {
	public function test_logging_with_redact_masks_data_urls() {
		$payload = array(
			'input' => array(
				array(
					'role' => 'user',
					'content' => array(
						array(
							'type' => 'input_image',
							'image_url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABBBBBBBBBBBBBBBBBBBBBBBBBBBBBB',
						),
						array(
							'type' => 'input_text',
							'text' => 'keep this',
						),
					),
				),
			),
		);

		$redacted = Logging::logging_with_redact($payload);

		$this->assertSame('data:image/***;base64,[redacted]', $redacted['input'][0]['content'][0]['image_url']);
		$this->assertSame('keep this', $redacted['input'][0]['content'][1]['text']);
	}

	public function test_logging_with_redact_masks_omit_keys() {
		$payload = array(
			'result' => 'secret_data',
			'other'  => 'public_data',
			'nested' => array(
				'result' => 'nested_secret',
			),
		);

		$redacted = Logging::logging_with_redact($payload, array('result'));

		$this->assertSame('(redacted)', $redacted['result']);
		$this->assertSame('public_data', $redacted['other']);
		$this->assertSame('(redacted)', $redacted['nested']['result']);
	}
}
