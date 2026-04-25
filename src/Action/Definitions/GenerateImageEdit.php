<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Bot\Media\Image;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\Utilities\FileSystem;

/**
 * Definition for the edit_image action.
 */
class GenerateImageEdit extends AbstractActionDefinition {
	/**
	 * Returns the action key.
	 *
	 * @return string
	 */
	public static function name(): string {
		return 'edit_image';
	}

	/**
	 * Returns the action configuration.
	 *
	 * @return array
	 */
	public static function config(): array {
		return array(
			'title'       => __( 'Edit image', LineConnect::PLUGIN_NAME ),
			'description' => __( 'Edit an image with gpt-image-1.5 and return it as a LINE image message. You can provide multiple input images and an optional mask.', LineConnect::PLUGIN_NAME ),
			'parameters'  => array(
				array(
					'type'        => 'string',
					'name'        => 'prompt',
					'description' => __( 'Image editing prompt', LineConnect::PLUGIN_NAME ),
					'required'    => true,
				),
				array(
					'type'        => 'array',
					'name'        => 'images',
					'description' => __( 'Array of image URLs or file paths to edit. Max 16 images.', LineConnect::PLUGIN_NAME ),
					'items'       => array(
						'type' => 'string',
					),
					'required'    => true,
				),
				array(
					'type'        => 'string',
					'name'        => 'mask',
					'description' => __( 'Optional mask image URL or file path.', LineConnect::PLUGIN_NAME ),
					'required'    => false,
				),
				array(
					'type'        => 'string',
					'name'        => 'size',
					'description' => __( 'Image size. Available values: auto, 1024x1024, 1536x1024, 1024x1536.', LineConnect::PLUGIN_NAME ),
					'default'     => '1024x1024',
					'enum'        => array('auto', '1024x1024', '1536x1024', '1024x1536'),
					'required'    => false,
				),
				array(
					'type'        => 'string',
					'name'        => 'quality',
					'description' => __( 'Rendering quality. Available values: auto, low, medium, high.', LineConnect::PLUGIN_NAME ),
					'default'     => 'auto',
					'enum'        => array('auto', 'low', 'medium', 'high'),
					'required'    => false,
				),
				array(
					'type'        => 'string',
					'name'        => 'background',
					'description' => __( 'Background handling. Available values: auto, transparent, opaque.', LineConnect::PLUGIN_NAME ),
					'default'     => 'auto',
					'enum'        => array('auto', 'transparent', 'opaque'),
					'required'    => false,
				),
				array(
					'type'        => 'string',
					'name'        => 'output_format',
					'description' => __( 'Output format. Available values: png, jpeg, webp.', LineConnect::PLUGIN_NAME ),
					'default'     => 'png',
					'enum'        => array('png', 'jpeg', 'webp'),
					'required'    => false,
				),
				array(
					'type'        => 'integer',
					'name'        => 'output_compression',
					'description' => __( 'Compression level for jpeg and webp output. Range: 0-100.', LineConnect::PLUGIN_NAME ),
					'default'     => 75,
					'required'    => false,
				),
				array(
					'type'        => 'string',
					'name'        => 'input_fidelity',
					'description' => __( 'Controls fidelity to the original input image(s). Available values: high, low.', LineConnect::PLUGIN_NAME ),
					'default'     => 'high',
					'enum'        => array('high', 'low'),
					'required'    => false,
				),
			),
			'namespace'   => self::class,
			'role'        => 'any',
			'order'       => 8070,
		);
	}

	/**
	 * Edit image.
	 *
	 * @param string $prompt
	 * @param array $images
	 * @param string|null $mask
	 * @param string|null $size
	 * @param string|null $quality
	 * @param string|null $background
	 * @param string|null $output_format
	 * @param int|null $output_compression
	 * @param string|null $input_fidelity
	 * @return array
	 */
	public function edit_image($prompt, array $images, $mask = null, $size = '1024x1024', $quality = 'auto', $background = 'auto', $output_format = 'png', $output_compression = 75, $input_fidelity = 'high'): array {
		$prompt = trim((string) $prompt);
		if ($prompt === '') {
			return $this->build_direct_error_response(__( 'Error: Prompt is required.', LineConnect::PLUGIN_NAME ));
		}

		if (empty($images)) {
			return $this->build_direct_error_response(__( 'Error: At least one input image is required.', LineConnect::PLUGIN_NAME ));
		}

		$apiKey = LineConnect::get_option('openai_secret');
		$endpoint = $this->resolve_image_edit_endpoint(LineConnect::get_option('openai_endpoint'));

		if (empty($apiKey) || empty($endpoint)) {
			return $this->build_direct_error_response(__( 'Error: OpenAI API Key or Endpoint is not configured.', LineConnect::PLUGIN_NAME ));
		}

		$request_images = array();
		foreach ($images as $img_src) {
			$encoded = $this->resolve_image_to_base64($img_src);
			if (!$encoded) {
				return $this->build_direct_error_response(sprintf(__( 'Error: Failed to process input image: %s', LineConnect::PLUGIN_NAME ), $img_src));
			}
			$request_images[] = array('image_url' => $encoded);
		}

		$request_mask = null;
		if (!empty($mask)) {
			$encoded_mask = $this->resolve_image_to_base64($mask);
			if (!$encoded_mask) {
				return $this->build_direct_error_response(sprintf(__( 'Error: Failed to process mask image: %s', LineConnect::PLUGIN_NAME ), $mask));
			}
			$request_mask = array('image_url' => $encoded_mask);
		}

		$data = apply_filters(LineConnect::FILTER_PREFIX . 'edit_image_request_data', $this->build_image_edit_request_data($prompt, $request_images, $request_mask, $size, $quality, $background, $output_format, $output_compression, $input_fidelity));

		$log_data = $data;
		if (isset($log_data['images'])) {
			foreach ($log_data['images'] as &$img) {
				if (isset($img['image_url']) && strlen($img['image_url']) > 100) {
					$img['image_url'] = substr($img['image_url'], 0, 50) . '... (base64 data)';
				}
			}
		}
		if (isset($log_data['mask']['image_url']) && strlen($log_data['mask']['image_url']) > 100) {
			$log_data['mask']['image_url'] = substr($log_data['mask']['image_url'], 0, 50) . '... (base64 data)';
		}
		error_log("Requesting image edit with data: " . print_r($log_data, true));

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
			return $this->build_direct_error_response(__( 'Error: Edited image exceeds 10MB size limit for LINE messages.', LineConnect::PLUGIN_NAME ));
		}

		$output_spec = $this->resolve_output_spec($data['output_format']);
		$saved = Image::saveGeneratedImage($this->getSecretPrefix(), $binary, $output_spec['mime_type'], $output_spec['extension']);
		if (! $saved) {
			return $this->build_direct_error_response(__( 'Error: Failed to save edited image.', LineConnect::PLUGIN_NAME ));
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
	 * Build the OpenAI image edit request payload.
	 *
	 * @param string $prompt
	 * @param array $images
	 * @param array|null $mask
	 * @param string|null $size
	 * @param string|null $quality
	 * @param string|null $background
	 * @param string|null $output_format
	 * @param int|null $output_compression
	 * @param string|null $input_fidelity
	 * @return array
	 */
	private function build_image_edit_request_data($prompt, array $images, $mask, $size, $quality, $background, $output_format, $output_compression, $input_fidelity): array {
		$size = $this->normalize_size_option($size);
		$quality = $this->normalize_quality_option($quality);
		$background = $this->normalize_background_option($background);
		$output_format = $this->normalize_output_format($output_format);
		$output_compression = $this->normalize_output_compression($output_compression);

		$data = array(
			'model'  => 'gpt-image-1.5',
			'prompt' => stripslashes($prompt),
			'images' => $images,
			'response_format' => 'b64_json',
			'size'   => $size,
			'quality' => $quality,
			'background' => $background,
			'output_format' => $output_format,
			'input_fidelity' => $input_fidelity,
		);

		if ($mask) {
			$data['mask'] = $mask;
		}

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
		$allowed = array('auto', '1024x1024', '1536x1024', '1024x1536');
		if (! in_array($size, $allowed, true)) {
			return '1024x1024';
		}

		return $size;
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
		$allowed = array('auto', 'transparent', 'opaque');
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
	 * Resolve the image edit endpoint from the configured endpoint.
	 *
	 * @param string|null $endpoint
	 * @return string
	 */
	private function resolve_image_edit_endpoint($endpoint): string {
		if (empty($endpoint)) {
			return 'https://api.openai.com/v1/images/edits';
		}

		if (preg_match('#/images/edits$#', $endpoint)) {
			return $endpoint;
		}

		$parsed = parse_url($endpoint);
		if (! is_array($parsed) || empty($parsed['scheme']) || empty($parsed['host'])) {
			return 'https://api.openai.com/v1/images/edits';
		}

		$path = $parsed['path'] ?? '';
		// Extract version from the original path and preserve it
		if (preg_match('#/(v\d+)/(chat/completions|responses|completions|images/generations)$#', $path, $matches)) {
			$version = $matches[1];
			return preg_replace('#/(v\d+)/(chat/completions|responses|completions|images/generations)$#', '/' . $version . '/images/edits', $endpoint);
		}

		$base = $parsed['scheme'] . '://' . $parsed['host'];
		if (! empty($parsed['port'])) {
			$base .= ':' . $parsed['port'];
		}

		return rtrim($base, '/') . '/v1/images/edits';
	}

	/**
	 * Resolve image source (URL or file path) to base64 data URL.
	 *
	 * @param string $src
	 * @return string|false
	 */
	private function resolve_image_to_base64(string $src) {
		if (strpos($src, 'data:image') === 0) {
			return $src;
		}

		// Try as local file path first
		if (file_exists($src) && is_readable($src)) {
			return FileSystem::get_base64_encoded_file($src);
		}

		// Try as relative path to lineconnect dir
		$full_path = FileSystem::get_lineconnect_file_path($src);
		if ($full_path && file_exists($full_path) && is_readable($full_path)) {
			return FileSystem::get_base64_encoded_file($full_path);
		}

		// Try as URL
		if (filter_var($src, FILTER_VALIDATE_URL)) {
			$response = wp_remote_get($src, array('timeout' => 30));
			if (is_wp_error($response)) {
				return false;
			}

			$body = wp_remote_retrieve_body($response);
			$mime_type = wp_remote_retrieve_header($response, 'content-type');
			if (empty($body)) {
				return false;
			}

			if (empty($mime_type)) {
				$finfo = new \finfo(FILEINFO_MIME_TYPE);
				$mime_type = $finfo->buffer($body);
			}

			return 'data:' . $mime_type . ';base64,' . base64_encode($body);
		}

		return false;
	}
}
