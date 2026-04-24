<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Bot\Media\Image;
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
			'description' => __( 'Generate an image with gpt-image-2 and return it as a LINE image message. You can optionally configure size, quality, background, output format, and compression.', LineConnect::PLUGIN_NAME ),
			'parameters'  => array(
				array(
					'type'        => 'string',
					'name'        => 'prompt',
					'description' => __( 'Image generation prompt', LineConnect::PLUGIN_NAME ),
					'required'    => true,
				),
				array(
					'type'        => 'string',
					'name'        => 'size',
					'description' => __( 'Image size. Use auto or WIDTHxHEIGHT. Both edges must be multiples of 16px, max edge length must be 3840px or less, the long edge to short edge ratio must be 3:1 or less, and total pixels must be between 655,360 and 8,294,400.', LineConnect::PLUGIN_NAME ),
					'default'     => 'auto',
					'required'    => false,
				),
				array(
					'type'        => 'string',
					'name'        => 'quality',
					'description' => __( 'Rendering quality. Available values: auto, low, medium, high.', LineConnect::PLUGIN_NAME ),
					'default'     => 'auto',
					'required'    => false,
				),
				array(
					'type'        => 'string',
					'name'        => 'background',
					'description' => __( 'Background handling. Available values: auto, opaque. Transparent backgrounds are not supported by gpt-image-2.', LineConnect::PLUGIN_NAME ),
					'default'     => 'auto',
					'required'    => false,
				),
				array(
					'type'        => 'string',
					'name'        => 'output_format',
					'description' => __( 'Output format. Available values: png, jpeg, webp.', LineConnect::PLUGIN_NAME ),
					'default'     => 'png',
					'required'    => false,
				),
				array(
					'type'        => 'integer',
					'name'        => 'output_compression',
					'description' => __( 'Compression level for jpeg and webp output. Range: 0-100.', LineConnect::PLUGIN_NAME ),
					'default'     => 75,
					'required'    => false,
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
	 * @param string|null $size
	 * @param string|null $quality
	 * @param string|null $background
	 * @param string|null $output_format
	 * @param int|null $output_compression
	 * @return array
	 */
	public function generate_image($prompt, $size = 'auto', $quality = 'auto', $background = 'auto', $output_format = 'png', $output_compression = 75): array {
		$prompt = trim((string) $prompt);
		if ($prompt === '') {
			return $this->build_direct_error_response(__( 'Error: Prompt is required.', LineConnect::PLUGIN_NAME ));
		}

		$apiKey = LineConnect::get_option('openai_secret');
		$endpoint = $this->resolve_image_endpoint(LineConnect::get_option('openai_endpoint'));

		if (empty($apiKey) || empty($endpoint)) {
			return $this->build_direct_error_response(__( 'Error: OpenAI API Key or Endpoint is not configured.', LineConnect::PLUGIN_NAME ));
		}

		$size_validation = $this->validate_size_option($size);
		if (! $size_validation['valid']) {
			return $this->build_direct_error_response($size_validation['error']);
		}

		$data = $this->build_image_request_data($prompt, $size_validation['value'], $quality, $background, $output_format, $output_compression);

		$headers = array(
			"Authorization: Bearer {$apiKey}",
			'Content-Type: application/json',
		);

		$curl = curl_init($endpoint);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 300);

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

		// Check if original image is within LINE's 10MB limit before saving
		$file_size = strlen($binary);
		if ($file_size > 10485760) {
			return $this->build_direct_error_response(__( 'Error: Generated image exceeds 10MB size limit for LINE messages.', LineConnect::PLUGIN_NAME ));
		}

		$output_spec = $this->resolve_output_spec($data['output_format']);
		$saved = Image::saveGeneratedImage($this->getSecretPrefix(), $binary, $output_spec['mime_type'], $output_spec['extension']);
		if (! $saved) {
			return $this->build_direct_error_response(__( 'Error: Failed to save generated image.', LineConnect::PLUGIN_NAME ));
		}

		// Generate thumbnail
		$thumb = Image::generateThumbnail($saved['full_path'], $this->getSecretPrefix());
		if (!$thumb) {
			// Fallback to original if thumbnail generation fails, but check size
			if ($file_size > 1048576) {
				if (file_exists($saved['full_path'])) {
					unlink($saved['full_path']);
				}
				return $this->build_direct_error_response(__( 'Error: Failed to generate thumbnail and original image exceeds 1MB preview size limit.', LineConnect::PLUGIN_NAME ));
			}
			$preview_url = $saved['url'];
		} else {
			// Verify thumbnail size
			if (filesize($thumb['full_path']) > 1048576) {
				if (file_exists($thumb['full_path'])) {
					unlink($thumb['full_path']);
				}
				if (file_exists($saved['full_path'])) {
					unlink($saved['full_path']);
				}
				return $this->build_direct_error_response(__( 'Error: Generated thumbnail exceeds 1MB preview size limit.', LineConnect::PLUGIN_NAME ));
			}
			$preview_url = $thumb['url'];
		}

		$image_message = Builder::createImageMessage($saved['url'], $preview_url);

		return array(
			'success'        => true,
			'response_mode'  => 'direct',
			'messages'       => array($image_message),
			'data'           => array(
				'file_path'      => $saved['file_path'],
				'file_url'       => $saved['url'],
				'thumb_path'     => $thumb ? $thumb['file_path'] : null,
				'thumb_url'      => $thumb ? $thumb['url'] : null,
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
	 * Build the OpenAI image request payload.
	 *
	 * @param string $prompt
	 * @param string|null $size
	 * @param string|null $quality
	 * @param string|null $background
	 * @param string|null $output_format
	 * @param int|null $output_compression
	 * @return array
	 */
	private function build_image_request_data($prompt, $size, $quality, $background, $output_format, $output_compression): array {
		$size = $this->normalize_size_option($size);
		$quality = $this->normalize_quality_option($quality);
		$background = $this->normalize_background_option($background);
		$output_format = $this->normalize_output_format($output_format);
		$output_compression = $this->normalize_output_compression($output_compression);

		$data = array(
			'model'  => 'gpt-image-2',
			'prompt' => stripslashes($prompt),
			'size'   => $size,
			'quality' => $quality,
			'background' => $background,
			'output_format' => $output_format,
		);

		if ($output_format === 'jpeg' || $output_format === 'webp') {
			$data['output_compression'] = $output_compression;
		}

		if (isset($this->event) && isset($this->event->source) && isset($this->event->source->userId)) {
			$data['user'] = $this->event->source->userId;
		}

		return $data;
	}

	/**
	 * Normalize the requested size.
	 *
	 * @param string|null $size
	 * @return string
	 */
	private function normalize_size_option($size): string {
		$size = strtolower(trim((string) $size));
		if ($size === '') {
			return 'auto';
		}

		return $size;
	}

	/**
	 * Validate the requested size.
	 *
	 * @param string|null $size
	 * @return array{valid: bool, value: string, error: string}
	 */
	private function validate_size_option($size): array {
		$size = $this->normalize_size_option($size);
		if ($size === 'auto') {
			return array(
				'valid' => true,
				'value' => 'auto',
				'error' => '',
			);
		}

		if (! preg_match('/^(\d{2,4})x(\d{2,4})$/', $size, $matches)) {
			return $this->invalid_size_response();
		}

		$width = intval($matches[1]);
		$height = intval($matches[2]);
		$constraints = $this->check_size_constraints($width, $height);
		if (! $constraints['valid']) {
			return $constraints;
		}

		return array(
			'valid' => true,
			'value' => $width . 'x' . $height,
			'error' => '',
		);
	}

	/**
	 * Check size constraints for explicit width and height values.
	 *
	 * @param int $width
	 * @param int $height
	 * @return array{valid: bool, value: string, error: string}
	 */
	private function check_size_constraints($width, $height): array {
		$max_edge = max($width, $height);
		$min_edge = min($width, $height);
		$total_pixels = $width * $height;
		$ratio = $min_edge > 0 ? $max_edge / $min_edge : 0;

		if ($width <= 0 || $height <= 0) {
			return $this->invalid_size_response();
		}

		if ($max_edge > 3840) {
			return $this->invalid_size_response();
		}

		if (($width % 16) !== 0 || ($height % 16) !== 0) {
			return $this->invalid_size_response();
		}

		if ($ratio > 3) {
			return $this->invalid_size_response();
		}

		if ($total_pixels < 655360 || $total_pixels > 8294400) {
			return $this->invalid_size_response();
		}

		return array(
			'valid' => true,
			'value' => $width . 'x' . $height,
			'error' => '',
		);
	}

	/**
	 * Build the standard invalid size response payload.
	 *
	 * @return array{valid: bool, value: string, error: string}
	 */
	private function invalid_size_response(): array {
		return array(
			'valid' => false,
			'value' => '',
			'error' => __( 'Error: Invalid size. Use auto or WIDTHxHEIGHT where both edges are multiples of 16px, the max edge is 3840px or less, the aspect ratio is 3:1 or less, and total pixels are between 655,360 and 8,294,400.', LineConnect::PLUGIN_NAME ),
		);
	}

	/**
	 * Normalize the requested quality.
	 *
	 * @param string|null $quality
	 * @return string
	 */
	private function normalize_quality_option($quality): string {
		$quality = strtolower(trim((string) $quality));
		$allowed = array('auto', 'low', 'medium', 'high');
		if (! in_array($quality, $allowed, true)) {
			return 'auto';
		}

		return $quality;
	}

	/**
	 * Normalize the requested background.
	 *
	 * @param string|null $background
	 * @return string
	 */
	private function normalize_background_option($background): string {
		$background = strtolower(trim((string) $background));
		$allowed = array('auto', 'opaque');
		if (! in_array($background, $allowed, true)) {
			return 'auto';
		}

		return $background;
	}

	/**
	 * Normalize the requested output format.
	 *
	 * @param string|null $output_format
	 * @return string
	 */
	private function normalize_output_format($output_format): string {
		$output_format = strtolower(trim((string) $output_format));
		$allowed = array('png', 'jpeg', 'webp');
		if (! in_array($output_format, $allowed, true)) {
			return 'png';
		}

		return $output_format;
	}

	/**
	 * Normalize the requested output compression.
	 *
	 * @param int|null $output_compression
	 * @return int
	 */
	private function normalize_output_compression($output_compression): int {
		if ($output_compression === null || $output_compression === '') {
			return 75;
		}

		$output_compression = intval($output_compression);
		if ($output_compression < 0) {
			return 0;
		}

		if ($output_compression > 100) {
			return 100;
		}

		return $output_compression;
	}

	/**
	 * Resolve the storage format for the generated image.
	 *
	 * @param string $output_format
	 * @return array
	 */
	private function resolve_output_spec($output_format): array {
		$output_format = $this->normalize_output_format($output_format);
		if ($output_format === 'jpeg') {
			return array(
				'mime_type' => 'image/jpeg',
				'extension' => 'jpg',
			);
		}

		if ($output_format === 'webp') {
			return array(
				'mime_type' => 'image/webp',
				'extension' => 'webp',
			);
		}

		return array(
			'mime_type' => 'image/png',
			'extension' => 'png',
		);
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
