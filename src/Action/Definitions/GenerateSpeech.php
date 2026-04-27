<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Bot\Media\Audio;
use Shipweb\LineConnect\Message\LINE\Builder;

/**
 * Definition for the generate_speech action.
 */
class GenerateSpeech extends AbstractActionDefinition {
	/**
	 * Returns the action key.
	 *
	 * @return string
	 */
	public static function name(): string {
		return 'generate_speech';
	}

	/**
	 * Returns the action configuration.
	 *
	 * @return array
	 */
	public static function config(): array {
		return array(
			'title'       => __( 'Generate speech', LineConnect::PLUGIN_NAME ),
			'description' => __( 'Convert text to speech using OpenAI TTS and return it as a LINE audio message.', LineConnect::PLUGIN_NAME ),
			'parameters'  => array(
				array(
					'type'        => 'string',
					'name'        => 'input',
					'description' => __( 'The text to generate audio for.', LineConnect::PLUGIN_NAME ),
					'required'    => true,
				),
				array(
					'type'        => 'string',
					'name'        => 'voice',
					'description' => __( 'The voice to use for generating the audio.', LineConnect::PLUGIN_NAME ),
					'default'     => 'marin',
					'enum'        => array( 'alloy', 'ash', 'ballad', 'coral', 'echo', 'fable', 'nova', 'onyx', 'sage', 'shimmer', 'verse', 'marin', 'cedar' ),
					'required'    => false,
				),
				array(
					'type'        => 'string',
					'name'        => 'instructions',
					'description' => __( 'Optional instructions to influence the style of the generated audio.', LineConnect::PLUGIN_NAME ),
					'required'    => false,
				),
				/*
				array(
					'type'        => 'string',
					'name'        => 'response_format',
					'description' => __( 'The format of the generated audio. Currently only mp3 is supported for LINE messages.', LineConnect::PLUGIN_NAME ),
					'default'     => 'mp3',
					'enum'        => array( 'mp3' ),
					'required'    => false,
				),
				*/
			),
			'namespace'   => self::class,
			'role'        => 'any',
			'order'       => 8060,
		);
	}

	/**
	 * Generate speech.
	 *
	 * @param string $input
	 * @param string $voice
	 * @param string|null $instructions
	 * @param string $response_format
	 * @return array
	 */
	public function generate_speech( $input, $voice = 'marin', $instructions = null, $response_format = 'mp3' ): array {
		$input = trim( (string) $input );
		if ( $input === '' ) {
			return $this->build_direct_error_response( __( 'Error: Input text is required.', LineConnect::PLUGIN_NAME ) );
		}

		$voice = strtolower( trim( (string) $voice ) );
		$allowed_voices = array( 'alloy', 'ash', 'ballad', 'coral', 'echo', 'fable', 'nova', 'onyx', 'sage', 'shimmer', 'verse', 'marin', 'cedar' );
		if ( ! in_array( $voice, $allowed_voices, true ) ) {
			return $this->build_direct_error_response( __( 'Error: Invalid voice option.', LineConnect::PLUGIN_NAME ) );
		}

		if ( $response_format !== 'mp3' ) {
			return $this->build_direct_error_response( __( 'Error: Invalid response format option.', LineConnect::PLUGIN_NAME ) );
		}

		$apiKey   = LineConnect::get_option( 'openai_secret' );
		$endpoint = $this->resolve_speech_endpoint( LineConnect::get_option( 'openai_endpoint' ) );

		if ( empty( $apiKey ) || empty( $endpoint ) ) {
			return $this->build_direct_error_response( __( 'Error: OpenAI API Key or Endpoint is not configured.', LineConnect::PLUGIN_NAME ) );
		}

		$data = apply_filters( LineConnect::FILTER_PREFIX . 'generate_speech_request_data', $this->build_speech_request_data( $input, $voice, $instructions, $response_format ) );

		$headers = array(
			"Authorization: Bearer {$apiKey}",
			'Content-Type: application/json',
		);

		$curl = curl_init( $endpoint );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data, JSON_UNESCAPED_UNICODE ) );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 300 );

		$result    = curl_exec( $curl );
		$http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		if ( curl_errno( $curl ) ) {
			$error_message = curl_error( $curl );
			curl_close( $curl );
			return $this->build_direct_error_response( sprintf( __( 'Error: %s', LineConnect::PLUGIN_NAME ), $error_message ) );
		}

		if ( $http_code >= 400 ) {
			$response      = json_decode( $result, true );
			$error_message = $response['error']['message'] ?? $result ?: 'Unknown error from API';
			curl_close( $curl );
			return $this->build_direct_error_response( sprintf( __( 'Error: %s', LineConnect::PLUGIN_NAME ), $error_message ) );
		}

		curl_close( $curl );

		if ( empty( $result ) ) {
			return $this->build_direct_error_response( __( 'Error: Received empty response from OpenAI.', LineConnect::PLUGIN_NAME ) );
		}

		$output_spec = $this->resolve_audio_output_spec( $response_format );
		$saved       = Audio::saveGeneratedAudio( $this->getSecretPrefix(), $this->getLineUserId(), $result, $output_spec['mime_type'], $output_spec['extension'], 'gpt-4o-mini-tts' );

		if ( ! $saved ) {
			return $this->build_direct_error_response( __( 'Error: Failed to save generated audio.', LineConnect::PLUGIN_NAME ) );
		}

		if ( empty( $saved['duration_ms'] ) || $saved['duration_ms'] <= 0 ) {
			return $this->build_direct_error_response( __( 'Error: Failed to determine audio duration.', LineConnect::PLUGIN_NAME ) );
		}

		$audio_message = Builder::createAudioMessage( $saved['url'], $saved['duration_ms'] );

		return array(
			'success'       => true,
			'response_mode' => 'direct',
			'messages'      => array( $audio_message ),
			'data'          => array(
				'file_path'       => $saved['file_path'],
				'file_url'        => $saved['url'],
				'mime_type'       => $output_spec['mime_type'],
				'duration_ms'     => $saved['duration_ms'],
				'voice'           => $voice,
				'response_format' => $response_format,
			),
		);
	}

	/**
	 * Get LINE user ID safely.
	 *
	 * @return string
	 */
	private function getLineUserId(): string {
		return isset( $this->event->source->userId ) && ! empty( $this->event->source->userId ) ? $this->event->source->userId : '_unknown';
	}

	/**
	 * Get secret prefix safely.
	 *
	 * @return string
	 */
	private function getSecretPrefix(): string {
		return isset( $this->secret_prefix ) && ! empty( $this->secret_prefix ) ? $this->secret_prefix : '_none';
	}

	/**
	 * Build the OpenAI speech request payload.
	 *
	 * @param string $input
	 * @param string $voice
	 * @param string|null $instructions
	 * @param string $response_format
	 * @return array
	 */
	private function build_speech_request_data( $input, $voice, $instructions, $response_format ): array {
		$data = array(
			'model'           => 'gpt-4o-mini-tts',
			'input'           => stripslashes( $input ),
			'voice'           => $voice,
			'response_format' => $response_format,
		);

		$instructions = trim( (string) $instructions );
		if ( $instructions !== '' ) {
			$data['instructions'] = $instructions;
		}

		return $data;
	}

	/**
	 * Resolve the speech endpoint from the configured endpoint.
	 *
	 * @param string|null $endpoint
	 * @return string
	 */
	private function resolve_speech_endpoint( $endpoint ): string {
		if ( empty( $endpoint ) ) {
			return 'https://api.openai.com/v1/audio/speech';
		}

		// Remove trailing slash
		$endpoint = rtrim( $endpoint, '/' );

		// Known OpenAI path fragments to be replaced/removed if they are at the end
		$fragments = array(
			'/audio/speech',
			'/images/generations',
			'/chat/completions',
			'/responses',
			'/completions',
		);

		foreach ( $fragments as $fragment ) {
			if ( substr( $endpoint, -strlen( $fragment ) ) === $fragment ) {
				$endpoint = substr( $endpoint, 0, -strlen( $fragment ) );
				break;
			}
		}

		// If it ends with /v{N}, we just append /audio/speech
		if ( preg_match( '#/v\d+$#', $endpoint ) ) {
			return $endpoint . '/audio/speech';
		}

		// If it doesn't have a version in the path, append /v1/audio/speech
		return $endpoint . '/v1/audio/speech';
	}

	/**
	 * Resolve audio output spec.
	 *
	 * @param string $format
	 * @return array
	 */
	private function resolve_audio_output_spec( $format ): array {
		if ( $format === 'mp3' ) {
			return array(
				'mime_type' => 'audio/mpeg',
				'extension' => 'mp3',
			);
		}

		// Fallback (though validation should prevent this)
		return array(
			'mime_type' => 'audio/mpeg',
			'extension' => 'mp3',
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
