<?php

namespace Shipweb\LineConnect\Utilities;

class Logging {

	/**
	 * Log a payload with sensitive image data URLs and specified keys redacted.
	 *
	 * Note: array_walk_recursive only processes array elements; objects should be cast
	 * to arrays or passed as JSON-decoded associative arrays to ensure full redaction.
	 *
	 * @param array $payload   The data to log and redact.
	 * @param array $omit_keys Keys whose values should be replaced with '(redacted)'.
	 * @return array The redacted payload.
	 */
	public static function logging_with_redact( array $payload, array $omit_keys = [] ): array {
		$walker = function ( &$value, $key ) use ( $omit_keys ) {
			if ( ( $key === 'image_url' ) && is_string( $value ) && strpos( $value, 'data:image/' ) === 0 ) {
				$value = preg_replace( '#^data:image/[^;]+;base64,.*$#', 'data:image/***;base64,[redacted]', $value );
			}
			if ( in_array( $key, $omit_keys, true ) && is_string( $value ) ) {
				$value = '(redacted)';
			}
		};

		array_walk_recursive( $payload, $walker );

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( print_r( $payload, true ) );
		}

		return $payload;
	}
}