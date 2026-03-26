<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the request_llm action.
 */
class LlmRequest extends AbstractActionDefinition {
	/**
	 * Returns the action key.
	 *
	 * @return string
	 */
	public static function name(): string {
		return 'request_llm';
	}

	/**
	 * Returns the action configuration.
	 *
	 * @return array
	 */
	public static function config(): array {
		return array(
			'title'       => __('Request LLM (AI)', LineConnect::PLUGIN_NAME),
			'description' => __('Send a request to an LLM (OpenAI) and get the response text.', LineConnect::PLUGIN_NAME),
			'parameters'  => array(
				array(
					'type' => 'string',
					'name' => 'prompt',
					'description' => __('Prompt (required)', LineConnect::PLUGIN_NAME),
					'required' => true,
				),
				array(
					'type' => 'string',
					'name' => 'system_message',
					'description' => __('System message (optional)', LineConnect::PLUGIN_NAME),
					'required' => false,
				),
				array(
					'type' => 'string',
					'name' => 'model',
					'description' => __('Model (optional)', LineConnect::PLUGIN_NAME),
					'required' => false,
				),
			),
			'namespace'   => self::class,
			'role'        => 'any',
			'order'       => 8000,
		);
	}

	/**
	 * Execute action: request LLM.
	 *
	 * @param string $prompt
	 * @param string|null $system_message
	 * @param string|null $model
	 * @return string
	 */
	public function request_llm($prompt, $system_message = null, $model = null): string {
		$apiKey = LineConnect::get_option('openai_secret');
		$url    = LineConnect::get_option('openai_endpoint');

		if (empty($apiKey) || empty($url)) {
			return __('Error: OpenAI API Key or Endpoint is not configured.', LineConnect::PLUGIN_NAME);
		}

		if (empty($model)) {
			$model = LineConnect::get_option('openai_model');
		}

		if (empty($system_message)) {
			$system_message = LineConnect::get_option('openai_system');
		}

		$headers = array(
			"Authorization: Bearer {$apiKey}",
			'Content-Type: application/json',
		);

		$messages = array();
		if (!empty($system_message)) {
			$messages[] = array(
				'role'    => 'system',
				'content' => stripslashes($system_message),
			);
		}

		$messages[] = array(
			'role'    => 'user',
			'content' => stripslashes($prompt),
		);

		$data = array(
			'model'       => $model,
			'messages'    => $messages,
			'temperature' => floatval(LineConnect::get_option('openai_temperature')),
		);

		$maxTokens = intval(LineConnect::get_option('openai_max_tokens'));
		if ($maxTokens > 0) {
			$data['max_tokens'] = $maxTokens;
		}

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);

		$result = curl_exec($curl);
		if (curl_errno($curl)) {
			$error_message = curl_error($curl);
			curl_close($curl);
			return 'Error: ' . $error_message;
		}

		$response = json_decode($result, true);
		curl_close($curl);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return 'Error: Failed to parse response from OpenAI. Result: ' . $result;
		}

		if (isset($response['error'])) {
			return 'Error: ' . ($response['error']['message'] ?? print_r($response['error'], true));
		}

		if (isset($response['choices'][0]['message']['content'])) {
			return $response['choices'][0]['message']['content'];
		}

		return 'Error: Unexpected response format from OpenAI.';
	}
}
