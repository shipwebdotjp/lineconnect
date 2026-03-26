<?php

class RequestLlmTest extends WP_UnitTestCase {
	public static function wpSetUpBeforeClass($factory) {
		lineconnectTest::init();
	}

	public function test_request_llm_logic() {
		$action = new \Shipweb\LineConnect\Action\Definitions\LlmRequest();

		// Set some dummy options
		update_option(\Shipweb\LineConnect\Core\LineConnect::OPTION_KEY__SETTINGS, array(
			'openai_secret' => 'dummy_key',
			'openai_endpoint' => 'https://api.openai.com/v1/chat/completions',
			'openai_model' => 'gpt-4o-mini',
			'openai_temperature' => 1,
			'openai_max_tokens' => -1
		));

		$this->assertTrue(method_exists($action, 'request_llm'));

		// We expect an error because the API key is dummy
		$result = $action->request_llm('Hello');
		$this->assertStringContainsString('Error', $result);
	}

	public function test_request_llm_config() {
		$config = \Shipweb\LineConnect\Action\Definitions\LlmRequest::config();
		$this->assertEquals('Request LLM (AI)', $config['title']);
		$this->assertCount(3, $config['parameters']);
		$this->assertEquals('prompt', $config['parameters'][0]['name']);
		$this->assertTrue($config['parameters'][0]['required']);
	}
}
