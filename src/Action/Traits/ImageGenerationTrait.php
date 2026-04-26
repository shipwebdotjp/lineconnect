<?php

namespace Shipweb\LineConnect\Action\Traits;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;

/**
 * Trait for common image generation functionality.
 *
 * Used by GenerateImage and GenerateImageEdit action definitions.
 */
trait ImageGenerationTrait {
	/**
	 * Get secret prefix safely.
	 *
	 * @return string
	 */
	private function getSecretPrefix(): string {
		return isset( $this->secret_prefix ) && ! empty( $this->secret_prefix ) ? $this->secret_prefix : '_none';
	}

	private function getLineUserId(): string {
		// $this->event->source->userId
		return isset( $this->event->source->userId ) && ! empty( $this->event->source->userId ) ? $this->event->source->userId : '_unknown';
	}

	/**
	 * Normalize the requested quality.
	 *
	 * @param string|null $quality
	 * @return string
	 */
	private function normalize_quality_option( $quality ): string {
		$quality = strtolower( trim( (string) $quality ) );
		$allowed = array( 'auto', 'low', 'medium', 'high' );
		if ( ! in_array( $quality, $allowed, true ) ) {
			return 'auto';
		}

		return $quality;
	}

	/**
	 * Normalize the requested output format.
	 *
	 * @param string|null $output_format
	 * @return string
	 */
	private function normalize_output_format( $output_format ): string {
		$output_format = strtolower( trim( (string) $output_format ) );
		$allowed       = array( 'png', 'jpeg', 'webp' );
		if ( ! in_array( $output_format, $allowed, true ) ) {
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
	private function normalize_output_compression( $output_compression ): int {
		if ( $output_compression === null || $output_compression === '' ) {
			return 75;
		}

		$output_compression = intval( $output_compression );
		if ( $output_compression < 0 ) {
			return 0;
		}

		if ( $output_compression > 100 ) {
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
	private function resolve_output_spec( $output_format ): array {
		$output_format = $this->normalize_output_format( $output_format );
		if ( $output_format === 'jpeg' ) {
			return array(
				'mime_type' => 'image/jpeg',
				'extension' => 'jpg',
			);
		}

		if ( $output_format === 'webp' ) {
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
	private function build_direct_error_response( $message ): array {
		return array(
			'success'       => false,
			'response_mode' => 'direct',
			'messages'      => array(
				Builder::createTextMessage( $message ),
			),
			'data'          => array(),
		);
	}
}
