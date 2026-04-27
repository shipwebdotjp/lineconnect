<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Action\Traits\ImageGenerationTrait;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Bot\Media\Image;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\Utilities\FileSystem;
use Shipweb\LineConnect\Utilities\Logging;

/**
 * Definition for the edit_image action.
 */
class GenerateImageEdit extends AbstractActionDefinition {
	use ImageGenerationTrait;

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
					'description' => __( 'Array of image URLs(Fully qualified HTTPS URL or data URL) to edit. Max 16 images.', LineConnect::PLUGIN_NAME ),
					'items'       => array(
						'type' => 'string',
					),
					'required'    => true,
				),
				array(
					'type'        => array( 'string', 'null' ),
					'name'        => 'mask',
					'description' => __( 'Optional mask image URL or file path.', LineConnect::PLUGIN_NAME ),
					'required'    => false,
				),
				array(
					'type'        => array( 'string', 'null' ),
					'name'        => 'size',
					'description' => __( 'Image size. Available values: auto, 1024x1024, 1536x1024, 1024x1536.', LineConnect::PLUGIN_NAME ),
					'default'     => '1024x1024',
					'enum'        => array( 'auto', '1024x1024', '1536x1024', '1024x1536' ),
					'required'    => false,
				),
				array(
					'type'        => array( 'string', 'null' ),
					'name'        => 'quality',
					'description' => __( 'Rendering quality. Available values: auto, low, medium, high.', LineConnect::PLUGIN_NAME ),
					'default'     => 'auto',
					'enum'        => array( 'auto', 'low', 'medium', 'high' ),
					'required'    => false,
				),
				array(
					'type'        => array( 'string', 'null' ),
					'name'        => 'background',
					'description' => __( 'Background handling. Available values: auto, transparent, opaque.', LineConnect::PLUGIN_NAME ),
					'default'     => 'auto',
					'enum'        => array( 'auto', 'transparent', 'opaque' ),
					'required'    => false,
				),
				array(
					'type'        => array( 'string', 'null' ),
					'name'        => 'output_format',
					'description' => __( 'Output format. Available values: png, jpeg, webp.', LineConnect::PLUGIN_NAME ),
					'default'     => 'png',
					'enum'        => array( 'png', 'jpeg', 'webp' ),
					'required'    => false,
				),
				array(
					'type'        => array( 'integer', 'null' ),
					'name'        => 'output_compression',
					'description' => __( 'Compression level for jpeg and webp output. Range: 0-100.', LineConnect::PLUGIN_NAME ),
					'default'     => 75,
					'required'    => false,
				),
				array(
					'type'        => array( 'string', 'null' ),
					'name'        => 'input_fidelity',
					'description' => __( 'Controls fidelity to the original input image(s). Available values: high, low.', LineConnect::PLUGIN_NAME ),
					'default'     => 'high',
					'enum'        => array( 'high', 'low' ),
					'required'    => false,
				),
			),
			'namespace'   => self::class,
			'role'        => 'any',
			'order'       => 8070,
		);
	}

	public function edit_image(
		$prompt,
		array $images,
		$mask = null,
		$size = '1024x1024',
		$quality = 'auto',
		$background = 'auto',
		$output_format = 'png',
		$output_compression = 75,
		$input_fidelity = 'high'
	): array {
		$prompt = trim( (string) $prompt );
		if ( $prompt === '' ) {
			return $this->build_direct_error_response( __( 'Error: Prompt is required.', LineConnect::PLUGIN_NAME ) );
		}

		if ( empty( $images ) ) {
			return $this->build_direct_error_response( __( 'Error: At least one input image is required.', LineConnect::PLUGIN_NAME ) );
		}

		$apiKey            = LineConnect::get_option( 'openai_secret' );
		$baseEndpoint      = LineConnect::get_option( 'openai_endpoint' );
		$responsesEndpoint = $this->resolve_responses_endpoint( $baseEndpoint );

		if ( empty( $apiKey ) || empty( $responsesEndpoint ) ) {
			return $this->build_direct_error_response( __( 'Error: OpenAI API Key or Endpoint is not configured.', LineConnect::PLUGIN_NAME ) );
		}

		$normalized_size        = $this->normalize_size_option( $size );
		$normalized_quality     = $this->normalize_quality_option( $quality );
		$normalized_background  = $this->normalize_background_option( $background );
		$normalized_format      = $this->normalize_output_format( $output_format );
		$normalized_compression = $this->normalize_output_compression( $output_compression );

		// gpt-image-2 は transparent background 未対応
		if ( $normalized_background === 'transparent' ) {
			return $this->build_direct_error_response(
				__( 'Error: Transparent background is not supported in the Responses API flow for GPT Image 2.', LineConnect::PLUGIN_NAME )
			);
		}

		$content = array(
			array(
				'type' => 'input_text',
				'text' => stripslashes( $prompt ),
			),
		);

		// Input images -> data URL として直接投入
		foreach ( $images as $img_src ) {
			$encoded = $this->resolve_image_to_base64( $img_src );
			if ( ! $encoded ) {
				return $this->build_direct_error_response(
					sprintf( __( 'Error: Failed to process input image: %s', LineConnect::PLUGIN_NAME ), $img_src )
				);
			}

			$content[] = array(
				'type'      => 'input_image',
				'image_url' => $encoded,
				// detail は vision 側の処理レベル。省略でもよいが明示するなら high。
				'detail'    => 'high',
			);
		}

		$tool = array(
			array_filter(
				array(
					'type'               => 'image_generation',
					'action'             => 'edit',
					'model'              => 'gpt-image-2',
					'size'               => $normalized_size,
					'quality'            => $normalized_quality,
					'background'         => $normalized_background,
					'output_format'      => $normalized_format,
					'output_compression' => in_array( $normalized_format, array( 'jpeg', 'jpg', 'webp' ), true ) ? $normalized_compression : null,
				// gpt-image-2 では input_fidelity は送らない
				),
				static function ( $value ) {
					return $value !== null && $value !== '';
				}
			),
		);

		// Mask -> data URL として直接投入
		if ( ! empty( $mask ) ) {
			$encoded_mask = $this->resolve_image_to_base64( $mask );
			if ( ! $encoded_mask ) {
				return $this->build_direct_error_response(
					sprintf( __( 'Error: Failed to process mask image: %s', LineConnect::PLUGIN_NAME ), $mask )
				);
			}

			$tool[0]['input_image_mask'] = array(
				'image_url' => $encoded_mask,
			);
		}

		$request_body = array(
			'model' => $this->resolve_responses_model( LineConnect::get_option( 'openai_responses_model' ) ),
			'input' => array(
				array(
					'role'    => 'user',
					'content' => $content,
				),
			),
			'tools' => $tool,
		);

		if ( isset( $this->event ) && isset( $this->event->source ) && isset( $this->event->source->userId ) ) {
			// Responses API のトップレベル user が必要なら filter で差し込めるようにする
			$request_body['metadata'] = array(
				'line_user_id' => (string) $this->event->source->userId,
			);
		}

		$request_body = apply_filters(
			LineConnect::FILTER_PREFIX . 'edit_image_response_request_body',
			$request_body,
			array(
				'prompt'             => $prompt,
				'images'             => $images,
				'mask'               => $mask,
				'size'               => $normalized_size,
				'quality'            => $normalized_quality,
				'background'         => $normalized_background,
				'output_format'      => $normalized_format,
				'output_compression' => $normalized_compression,
			)
		);

		Logging::logging_with_redact( array( 'endpoint' => $responsesEndpoint ) );
		Logging::logging_with_redact( array( 'request' => $request_body ), array( 'result' ) );

		$headers = array(
			'Authorization: Bearer ' . $apiKey,
			'Content-Type: application/json',
		);

		$curl = curl_init( $responsesEndpoint );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, wp_json_encode( $request_body ) );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 300 );

		$result     = curl_exec( $curl );
		$curl_error = curl_errno( $curl ) ? curl_error( $curl ) : null;
		curl_close( $curl );

		Logging::logging_with_redact( array( 'response' => json_decode( $result, true ) ), array( 'result' ) );

		if ( $curl_error ) {
			return $this->build_direct_error_response( sprintf( __( 'Error: %s', LineConnect::PLUGIN_NAME ), $curl_error ) );
		}

		$response = json_decode( $result, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return $this->build_direct_error_response( __( 'Error: Failed to parse response from OpenAI.', LineConnect::PLUGIN_NAME ) );
		}

		if ( isset( $response['error'] ) ) {
			$error_message = $response['error']['message'] ?? json_encode( $response['error'] ) ?: 'Unknown error from API';
			return $this->build_direct_error_response( sprintf( __( 'Error: %s', LineConnect::PLUGIN_NAME ), $error_message ) );
		}

		$image_data = $this->extract_responses_image_base64( $response );
		if ( empty( $image_data ) ) {
			$error_message = $this->extract_responses_error_message( $response );
			return $this->build_direct_error_response( sprintf( __( 'Error: %s', LineConnect::PLUGIN_NAME ), $error_message ) );
		}

		$binary = base64_decode( $image_data, true );
		if ( $binary === false ) {
			return $this->build_direct_error_response( __( 'Error: Failed to decode image data.', LineConnect::PLUGIN_NAME ) );
		}

		$file_size = strlen( $binary );
		if ( $file_size > 10485760 ) {
			return $this->build_direct_error_response( __( 'Error: Edited image exceeds 10MB size limit for LINE messages.', LineConnect::PLUGIN_NAME ) );
		}

		$output_spec = $this->resolve_output_spec( $normalized_format );
		$saved       = Image::saveGeneratedImage( $this->get_secret_prefix(), $this->get_line_user_id(), $binary, $output_spec['mime_type'], $output_spec['extension'] );
		if ( ! $saved ) {
			return $this->build_direct_error_response( __( 'Error: Failed to save edited image.', LineConnect::PLUGIN_NAME ) );
		}

		$thumb = Image::generateThumbnail( $saved['full_path'], $this->get_secret_prefix(), $this->get_line_user_id() );
		if ( ! $thumb ) {
			if ( $file_size > 1048576 ) {
				if ( file_exists( $saved['full_path'] ) ) {
					unlink( $saved['full_path'] );
				}
				return $this->build_direct_error_response( __( 'Error: Failed to generate thumbnail and original image exceeds 1MB preview size limit.', LineConnect::PLUGIN_NAME ) );
			}
			$preview_url = $saved['url'];
		} else {
			if ( filesize( $thumb['full_path'] ) > 1048576 ) {
				if ( file_exists( $thumb['full_path'] ) ) {
					unlink( $thumb['full_path'] );
				}
				if ( file_exists( $saved['full_path'] ) ) {
					unlink( $saved['full_path'] );
				}
				return $this->build_direct_error_response( __( 'Error: Generated thumbnail exceeds 1MB preview size limit.', LineConnect::PLUGIN_NAME ) );
			}
			$preview_url = $thumb['url'];
		}

		$thumb_data = is_array( $thumb ) ? $thumb : null;

		return $this->build_image_response(
			$prompt,
			$response,
			$saved,
			$thumb_data,
			$preview_url
		);
	}

	/**
	 * Build the edit-image response in the same shape as generate_image().
	 *
	 * @param string     $prompt
	 * @param array      $response
	 * @param array      $saved
	 * @param array|null $thumb
	 * @param string     $preview_url
	 * @return array
	 */
	private function build_image_response( $prompt, array $response, array $saved, ?array $thumb, $preview_url ): array {
		$image_message = Builder::createImageMessage( $saved['url'], $preview_url );

		return array(
			'success'       => true,
			'response_mode' => 'direct',
			'messages'      => array( $image_message ),
			'data'          => array(
				'file_path'       => $saved['file_path'],
				'file_url'        => $saved['url'],
				'thumb_path'      => $thumb ? $thumb['file_path'] : null,
				'thumb_url'       => $thumb ? $thumb['url'] : null,
				'original_prompt' => $prompt,
				'revised_prompt'  => $this->extract_responses_revised_prompt( $response ),
				'original_url'    => $saved['url'],
				'preview_url'     => $preview_url,
			),
		);
	}

	private function resolve_responses_model( $model ): string {
		$model = trim( (string) $model );

		// Responses API の画像生成ツールは gpt-5 以降の対応モデルで使う。
		if ( $model === '' || $model === 'gpt-image-2' ) {
			return 'gpt-5.5';
		}

		return $model;
	}

	private function resolve_responses_endpoint( $endpoint ): string {
		$endpoint = trim( (string) $endpoint );
		if ( $endpoint === '' ) {
			return 'https://api.openai.com/v1/responses';
		}

		$endpoint = rtrim( $endpoint, '/' );

		$fragments = array(
			'/responses',
			'/chat/completions',
			'/completions',
			'/images/edits',
			'/images/generations',
		);

		foreach ( $fragments as $fragment ) {
			if ( substr( $endpoint, -strlen( $fragment ) ) === $fragment ) {
				$endpoint = substr( $endpoint, 0, -strlen( $fragment ) );
				break;
			}
		}

		if ( preg_match( '#/v\d+$#', $endpoint ) ) {
			return $endpoint . '/responses';
		}

		return $endpoint . '/v1/responses';
	}

	private function extract_responses_image_base64( array $response ): string {
		if ( empty( $response['output'] ) || ! is_array( $response['output'] ) ) {
			return '';
		}

		foreach ( $response['output'] as $item ) {
			if ( ( $item['type'] ?? '' ) === 'image_generation_call' && ! empty( $item['result'] ) ) {
				return (string) $item['result'];
			}
		}

		return '';
	}

	private function extract_responses_revised_prompt( array $response ): string {
		if ( empty( $response['output'] ) || ! is_array( $response['output'] ) ) {
			return '';
		}

		foreach ( $response['output'] as $item ) {
			if ( ( $item['type'] ?? '' ) === 'image_generation_call' && ! empty( $item['revised_prompt'] ) ) {
				return (string) $item['revised_prompt'];
			}
		}

		return '';
	}

	private function extract_responses_error_message( array $response ): string {
		if ( ! empty( $response['error']['message'] ) ) {
			return (string) $response['error']['message'];
		}

		if ( ! empty( $response['output'] ) && is_array( $response['output'] ) ) {
			foreach ( $response['output'] as $item ) {
				if ( ( $item['type'] ?? '' ) === 'message' && ! empty( $item['content'] ) && is_array( $item['content'] ) ) {
					foreach ( $item['content'] as $content ) {
						if ( ! empty( $content['text'] ) ) {
							return (string) $content['text'];
						}
					}
				}

				if ( ( $item['type'] ?? '' ) === 'image_generation_call' && ! empty( $item['error']['message'] ) ) {
					return (string) $item['error']['message'];
				}
			}
		}

		return 'Unexpected response format from OpenAI.';
	}

	/**
	 * Normalize the requested size.
	 *
	 * @param string|null $size
	 * @return string
	 */
	private function normalize_size_option( $size ): string {
		$size    = strtolower( trim( (string) $size ) );
		$allowed = array( 'auto', '1024x1024', '1536x1024', '1024x1536' );
		if ( ! in_array( $size, $allowed, true ) ) {
			return '1024x1024';
		}

		return $size;
	}

	/**
	 * Normalize the requested background.
	 *
	 * @param string|null $background
	 * @return string
	 */
	private function normalize_background_option( $background ): string {
		$background = strtolower( trim( (string) $background ) );
		$allowed    = array( 'auto', 'transparent', 'opaque' );
		if ( ! in_array( $background, $allowed, true ) ) {
			return 'auto';
		}

		return $background;
	}

	/**
	 * Resolve image source (URL or file path) to base64 data URL.
	 *
	 * @param string $src
	 * @return string|false
	 */
	private function resolve_image_to_base64( string $src ) {
		if ( strpos( $src, 'data:image' ) === 0 ) {
			return $src;
		}

		// Security check: reject NUL bytes and parent-traversal segments
		if ( strpos( $src, "\0" ) !== false || strpos( $src, '..' ) !== false ) {
			return false;
		}

		$allowed_mimes = array( 'image/png', 'image/jpeg', 'image/webp' );

		// Try as local file path
		$file_path = false;
		if ( file_exists( $src ) && is_readable( $src ) ) {
			$file_path = $src;
		} else {
			// Try as relative path to lineconnect dir
			$candidate = FileSystem::get_lineconnect_file_path( $src );
			if ( $candidate && file_exists( $candidate ) && is_readable( $candidate ) ) {
				$file_path = $candidate;
			}
		}

		if ( $file_path ) {
			$real_path    = realpath( $file_path );
			$upload_dir   = wp_upload_dir();
			$allowed_base = realpath( $upload_dir['basedir'] . '/lineconnect' );

			// Ensure the file is within the allowed lineconnect directory
			if ( $real_path === false || $allowed_base === false || strpos( $real_path, $allowed_base ) !== 0 ) {
				return false;
			}

			// Validate MIME type
			$finfo     = new \finfo( FILEINFO_MIME_TYPE );
			$mime_type = $finfo->file( $real_path );
			if ( ! in_array( $mime_type, $allowed_mimes, true ) ) {
				return false;
			}

			return FileSystem::get_base64_encoded_file( $real_path );
		}

		// Try as URL
		if ( filter_var( $src, FILTER_VALIDATE_URL ) ) {
			// Check if this URL points to the local upload_dir - if so, read as file
			$upload_dir      = wp_upload_dir();
			$upload_base_url = $upload_dir['baseurl'];

			// Parse the URL and check if it starts with the upload base URL
			if ( strpos( $src, $upload_base_url ) === 0 ) {
				// Extract the relative path from the URL
				$relative_path = substr( $src, strlen( $upload_base_url ) );
				// Decode URL-encoded characters and strip query string
				$relative_path = parse_url( $relative_path, PHP_URL_PATH );
				$relative_path = rawurldecode( $relative_path );

				// Build the local file path
				$local_file_path = $upload_dir['basedir'] . $relative_path;

				// Verify the resolved path is still within upload_dir (security check)
				$real_path    = realpath( $local_file_path );
				$allowed_base = realpath( $upload_dir['basedir'] );

				if ( $real_path !== false && $allowed_base !== false && strpos( $real_path, $allowed_base ) === 0 ) {
					// Validate MIME type
					$finfo     = new \finfo( FILEINFO_MIME_TYPE );
					$mime_type = $finfo->file( $real_path );
					if ( in_array( $mime_type, $allowed_mimes, true ) ) {
						return FileSystem::get_base64_encoded_file( $real_path );
					}
				}
			}

			// Fall back to remote fetch if not a local upload URL
			$response = wp_remote_get( $src, array( 'timeout' => 30 ) );
			if ( is_wp_error( $response ) ) {
				return false;
			}

			$body = wp_remote_retrieve_body( $response );
			if ( empty( $body ) ) {
				return false;
			}

			$mime_type = wp_remote_retrieve_header( $response, 'content-type' );
			if ( empty( $mime_type ) || strpos( $mime_type, ';' ) !== false ) {
				$finfo     = new \finfo( FILEINFO_MIME_TYPE );
				$mime_type = $finfo->buffer( $body );
			}

			if ( ! in_array( $mime_type, $allowed_mimes, true ) ) {
				return false;
			}

			return 'data:' . $mime_type . ';base64,' . base64_encode( $body );
		}

		return false;
	}
}
