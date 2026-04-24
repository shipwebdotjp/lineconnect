<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Bot\File;
use Shipweb\LineConnect\Message\LINE\Builder;

/**
 * Definition for the generate_image action.
 */
class GenerateImage extends AbstractActionDefinition {
	/**
	 * Returns the action key.
	 *
	 * @return string
	 */
	public static function name(): string {
		return 'generate_image';
	}

	/**
	 * Returns the action configuration.
	 *
	 * @return array
	 */
	public static function config(): array {
		return array(
			'title'       => __( 'Generate image', LineConnect::PLUGIN_NAME ),
			'description' => __( 'Generate an image with gpt-image-2 and return it as a LINE image message.', LineConnect::PLUGIN_NAME ),
			'parameters'  => array(
				array(
					'type'        => 'string',
					'name'        => 'prompt',
					'description' => __( 'Image generation prompt', LineConnect::PLUGIN_NAME ),
					'required'    => true,
				),
			),
			'namespace'   => self::class,
			'role'        => 'any',
			'order'       => 8050,
		);
	}

	/**
	 * Generate image.
	 *
	 * @param string $prompt
	 * @return array
	 */
	public function generate_image($prompt): array {
		$prompt = trim((string) $prompt);
		if ($prompt === '') {
			return $this->build_direct_error_response(__( 'Error: Prompt is required.', LineConnect::PLUGIN_NAME ));
		}

		$apiKey = LineConnect::get_option('openai_secret');
		$endpoint = $this->resolve_image_endpoint(LineConnect::get_option('openai_endpoint'));

		if (empty($apiKey) || empty($endpoint)) {
			return $this->build_direct_error_response(__( 'Error: OpenAI API Key or Endpoint is not configured.', LineConnect::PLUGIN_NAME ));
		}

		$data = array(
			'model'        => 'gpt-image-2',
			'prompt'       => stripslashes($prompt),
			'size'         => 'auto',
			'quality'      => 'auto',
			'background'   => 'auto',
			'output_format' => 'png',
		);

		if (isset($this->event) && isset($this->event->source) && isset($this->event->source->userId)) {
			$data['user'] = $this->event->source->userId;
		}

		$headers = array(
			"Authorization: Bearer {$apiKey}",
			'Content-Type: application/json',
		);

		$curl = curl_init($endpoint);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT, 120);

		$result = curl_exec($curl);
		if (curl_errno($curl)) {
			$error_message = curl_error($curl);
			curl_close($curl);
			return $this->build_direct_error_response(sprintf(__( 'Error: %s', LineConnect::PLUGIN_NAME ), $error_message));
		}

		$response = json_decode($result, true);
		curl_close($curl);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return $this->build_direct_error_response(__( 'Error: Failed to parse response from OpenAI.', LineConnect::PLUGIN_NAME ));
		}

		if (isset($response['error'])) {
			$error_message = $response['error']['message'] ?? json_encode($response['error']) ?: 'Unknown error from API';
			return $this->build_direct_error_response(sprintf(__( 'Error: %s', LineConnect::PLUGIN_NAME ), $error_message));
		}

		$image_data = $response['data'][0]['b64_json'] ?? '';
		if (empty($image_data)) {
			return $this->build_direct_error_response(__( 'Error: Unexpected response format from OpenAI.', LineConnect::PLUGIN_NAME ));
		}

		$binary = base64_decode($image_data, true);
		if ($binary === false) {
			return $this->build_direct_error_response(__( 'Error: Failed to decode image data.', LineConnect::PLUGIN_NAME ));
		}

		$saved = File::saveGeneratedImage($this->getSecretPrefix(), $binary, 'image/png', 'png');
		if (! $saved) {
			return $this->build_direct_error_response(__( 'Error: Failed to save generated image.', LineConnect::PLUGIN_NAME ));
		}

		// Check if preview is within LINE's 1MB limit
		$file_size = strlen($binary);
		if ($file_size > 1048576) {
			return $this->build_direct_error_response(__( 'Error: Generated image exceeds 1MB preview size limit for LINE messages.', LineConnect::PLUGIN_NAME ));
		}

		$image_message = Builder::createImageMessage($saved['url'], $saved['url']);

		return array(
			'success'        => true,
			'response_mode'  => 'direct',
			'messages'       => array($image_message),
			'data'           => array(
				'file_path' => $saved['file_path'],
				'file_url'  => $saved['url'],
			),
		);
	}

	/**
	 * Get secret prefix safely.
	 *
	 * @return string
	 */
	private function getSecretPrefix(): string {
		return isset($this->secret_prefix) && !empty($this->secret_prefix) ? $this->secret_prefix : '_none';
	}

	/**
	 * Build direct error response.
	 *
	 * @param string $message
	 * @return array
	 */
	private function build_direct_error_response($message): array {
		return array(
			'success'       => false,
			'response_mode' => 'direct',
			'messages'      => array(
				Builder::createTextMessage($message),
			),
			'data'          => array(),
		);
	}

	/**
	 * Resolve the image generation endpoint from the configured endpoint.
	 *
	 * @param string|null $endpoint
	 * @return string
	 */
	private function resolve_image_endpoint($endpoint): string {
		if (empty($endpoint)) {
			return 'https://api.openai.com/v1/images/generations';
		}

		if (preg_match('#/images/generations$#', $endpoint)) {
			return $endpoint;
		}

		$parsed = parse_url($endpoint);
		if (! is_array($parsed) || empty($parsed['scheme']) || empty($parsed['host'])) {
			return 'https://api.openai.com/v1/images/generations';
		}

		$path = $parsed['path'] ?? '';
		// Extract version from the original path and preserve it
		if (preg_match('#/(v\d+)/(chat/completions|responses|completions)$#', $path, $matches)) {
			$version = $matches[1];
			return preg_replace('#/(v\d+)/(chat/completions|responses|completions)$#', '/' . $version . '/images/generations', $endpoint);
		}

		$base = $parsed['scheme'] . '://' . $parsed['host'];
		if (! empty($parsed['port'])) {
			$base .= ':' . $parsed['port'];
		}

		return rtrim($base, '/') . '/v1/images/generations';
	}
}