<?php

/**
 * Lineconnect OpenAi API Class
 *
 * OpenAi API Class
 *
 * @category Components
 * @package  OpenAi
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

namespace Shipweb\LineConnect\Bot\Provider;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Utilities\Logging;

class OpenAi {

	function getResponseByChatGPT( $event, $bot_id, $prompt, $addtional_messages = null, $direct_messages = array() ) {
		$user_id = $event->{'source'}->{'userId'};
		// OpenAI APIにリクエストを送信
		$AiMessage = $this->getResponse( $event, $user_id, $bot_id, $prompt, $addtional_messages );
		// error_log('response Message:' . print_r($AiMessage, true));
		// レスポンスを処理
		if ( isset( $AiMessage['error'] ) ) {
			$message      = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder( $AiMessage['error']['type'] . ': ' . $AiMessage['error']['message'] );
			$responseByAi = false;
		} elseif ( isset( $AiMessage['choices'][0]['message']['tool_calls'] ) ) {
			// add old prompt to addtional messages
			if ( is_array( $prompt ) ) {
				if ( isset( $addtional_messages ) ) {
					$addtional_messages = array_merge( $addtional_messages, $prompt );
				} else {
					$addtional_messages = $prompt;
				}
			} else {
				$addtional_messages[] = array(
					'role'    => 'user',
					'content' => $prompt,
				);
			}
			if ( is_array( $AiMessage['choices'][0]['message']['tool_calls'] ) ) {
				$prompts            = array();
				$direct_response    = null;
				$callable_functions = \Shipweb\LineConnect\Action\Action::get_callable_functions( true );
				foreach ( $AiMessage['choices'][0]['message']['tool_calls'] as $tool_call ) {
					if ( $tool_call['type'] == 'function' && isset( $tool_call['function'] ) ) {
						$function_name = $tool_call['function']['name'];
						$arguments     = $tool_call['function']['arguments'];
						// error_log( 'function_name:' . $function_name . ' arguments:' . print_r( $arguments, true ) );
						$error = null;
						if (
							! in_array( $function_name, lineconnect::get_option( ( 'openai_enabled_functions' ) ) ) ||
							! isset( $callable_functions[ $function_name ] )
						) {
							$error = array( 'error' => "NameError: name '$function_name' is not exists" );
						} else {
							$function_schema = $callable_functions[ $function_name ];
							if ( isset( $function_schema['role'] ) && $function_schema['role'] != 'any' ) {
								// check if user has capability
								$user = lineconnect::get_wpuser_from_line_id( $bot_id, $user_id );
								if ( empty( $user ) || ! $user->exists() || ! function_exists( 'current_user_can' ) || ! \current_user_can( $function_schema['role'] ) ) {
									$error = array( 'error' => "PermissionError: you have no permission to call function '$function_name'" );
								}
							}
							if ( isset( $function_schema['namespace'] ) ) {
								if ( ! class_exists( $function_schema['namespace'] ) ) {
									$error = array(
										'error' => "NameError: namespace '$function_schema[namespace]' is not exists",
										'abort' => true,
									);
								}
								try {
									$class_name = new $function_schema['namespace']();
									if ( method_exists( $class_name, 'set_secret_prefix' ) ) {
										$class_name->set_secret_prefix( $bot_id );
									}
									if ( $event && method_exists( $class_name, 'set_event' ) ) {
										$class_name->set_event( $event );
									}
								} catch ( \Exception $e ) {
									$error = array(
										'error' => "NameError: namespace '$function_schema[namespace]' is not exists",
										'abort' => true,
									);
								}
								if ( ! method_exists( $class_name, $function_name ) ) {
									$error = array(
										'error' => "NameError: name '$function_name' in namespace '$function_schema[namespace]' is not defined",
										'abort' => true,
									);
								}
							} elseif ( ! function_exists( $function_name ) ) {
								$error = array(
									'error' => "NameError: name '$function_name' is not exists",
									'abort' => true,
								);
							}
							// parse arguments
							try {
								$arguments_parsed = json_decode( $arguments, true, 512, JSON_THROW_ON_ERROR );
							} catch ( \JsonException $e ) {
								$error = array( 'error' => 'ParseError: arguments is not valid json' );
							}
						}

						if ( ! isset( $error ) ) {
							$arguments_array = \Shipweb\LineConnect\Utilities\PrepareArguments::arguments_object_to_array( $arguments_parsed, $function_schema['parameters'] );
							if ( isset( $function_schema['namespace'] ) ) {
								if ( empty( $function_schema['parameters'] ) ) {
									$response = call_user_func( array( $class_name, $function_name ) );
								} else {
									$response = call_user_func_array( array( $class_name, $function_name ), $arguments_array );
								}
								// extract arguments to call function
								// $response = $class_name->$function_name( $arguments_array );
							} elseif ( empty( $function_schema['parameters'] ) ) {
								$response = call_user_func( $function_name );
							} else {
								$response = call_user_func_array( $function_name, $arguments_array );
								// $response = $function_name( $arguments_array );

							}
							// error_log( 'function response:' . print_r( $response, true ) );

							if ( is_array( $response ) && isset( $response['response_mode'] ) && $response['response_mode'] === 'direct' ) {
								$direct_response = $response;
								if ( isset( $response['messages'] ) && is_array( $response['messages'] ) ) {
									$direct_messages = array_merge( $direct_messages, $response['messages'] );
								}
							} else {
								$prompts[] = array(
									'tool_call_id' => $tool_call['id'],
									'role'         => 'tool',
									'name'         => $function_name,
									'content'      => json_encode( $response ),
								);
							}
							$responseByAi = true;
						} else {
							$message      = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder( $error['error'] );
							$responseByAi = false;
						}
					}
				}
				// ツールコールを行った後、ダイレクト応答でないものがある場合のみ、再度ChatGPTにプロンプトを投げる
				if ( ! empty( $prompts ) && ! $direct_response ) {
					$addtional_messages[] = $AiMessage['choices'][0]['message'];
					return $this->getResponseByChatGPT( $event, $bot_id, $prompts, $addtional_messages, $direct_messages );
				}
			}
		} elseif ( isset( $AiMessage['choices'][0]['message']['content'] ) ) {
			$content      = $AiMessage['choices'][0]['message']['content'];
			$responseByAi = true;
		}

		$normalized_messages = array();
		if ( ! empty( $direct_messages ) ) {
			foreach ( $direct_messages as $direct_message ) {
				if ( $direct_message ) {
					$normalized_messages[] = \Shipweb\LineConnect\Message\LINE\Builder::get_line_message_builder( $direct_message );
				}
			}
		}
		if ( ! empty( $content ) ) {
			$normalized_messages[] = \Shipweb\LineConnect\Message\LINE\Builder::get_line_message_builder_from_string( $content );
		}

		if ( count( $normalized_messages ) === 1 ) {
			$message = $normalized_messages[0];
		} elseif ( ! empty( $normalized_messages ) ) {
			$message = \Shipweb\LineConnect\Message\LINE\Builder::createMultiMessage( $normalized_messages );
		}

		return array(
			'message'      => $message,
			'responseByAi' => $responseByAi,
			'rowResponse'  => $AiMessage,
			'toolResponse' => $direct_response ?? null,
		);
	}

	// ChatGPTで応答作成
	function getResponse( $event, $user_id, $bot_id, $prompt, $addtional_messages = null ) {
		global $wpdb;

		$apiKey = lineconnect::get_option( 'openai_secret' );
		$url    = lineconnect::get_option( 'openai_endpoint' );

		$headers = array(
			"Authorization: Bearer {$apiKey}",
			'Content-Type: application/json',
		);

		// Define messages
		$messages       = array();
		$injection_data = array(
			'return'  => array(),
			'webhook' => self::merge_postback_data_to_params( json_decode( json_encode( $event ), true ) ),
			'user'    => $event ? lineconnect::get_userdata_from_line_id( $bot_id, $event->{'source'}->{'userId'} ) : array(),
		);
		// error_log("injection_data: ". print_r($injection_data, true));
		$system_content = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder( stripslashes( lineconnect::get_option( 'openai_system' ) ), $injection_data );
		// error_log( 'system_content:' . $system_content );
		if ( lineconnect::get_option( 'openai_system' ) ) {
			$system_message = array(
				'role'    => 'system',
				'content' => $system_content,
			);

			$messages[] = $system_message;
		}

		if ( isset( $user_id ) ) {
			$table_name  = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS;
			$context_num = intval( lineconnect::get_option( 'openai_context' ) * 2 );

			$limit_normal = intval( lineconnect::get_option( 'openai_limit_normal' ) );
			$limit_linked = intval( lineconnect::get_option( 'openai_limit_linked' ) );
			$overlimit    = false;
			if ( $limit_normal != -1 || $limit_linked != -1 ) {

				$convasation_count = $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT(id) 
					FROM {$table_name}
					WHERE source_type = 11 AND user_id = %s AND bot_id = %s AND DATE(timestamp) = CURDATE()
					",
						array(
							$user_id,
							$bot_id,
						)
					)
				);
				// メタ情報からLINEユーザーIDでユーザー検索
				$user = lineconnect::get_wpuser_from_line_id( $bot_id, $user_id );
				if ( $user ) { // ユーザーが見つかればすでに連携されている
					$limit_count = $limit_linked;
				} else {
					$limit_count = $limit_normal;
				}
				// error_log("convasation_count: " . $convasation_count . " limit_count: " . $limit_count);
				if ( $limit_count != -1 && $convasation_count >= $limit_count ) {
					return array(
						'error' => array(
							'type'    => 'エラー',
							'message' => str_replace( '%limit%', $limit_count, lineconnect::get_option( 'openai_limit_message' ) ),
						),
					);
				}
			}
			// 過去の文脈を取得
			if ( version_compare( lineconnect::get_current_db_version(), '1.8', '>=' ) ) {
				$scope = array_search( 'ai', \Shipweb\LineConnect\Bot\Constants::WH_SCOPE ) ?: 0;

				$convasations = $wpdb->get_results(
					$wpdb->prepare(
						"
				SELECT event_type,source_type,message_type,message 
				FROM {$table_name}
				WHERE bot_id = %s AND user_id = %s AND scope = %d 
				ORDER BY id desc
				LIMIT 0, {$context_num}
				",
						array(
							$bot_id,
							$user_id,
							$scope,
						)
					)
				);
			} else {
				$convasations = $wpdb->get_results(
					$wpdb->prepare(
						"
				SELECT event_type,source_type,message_type,message 
				FROM {$table_name}
				WHERE bot_id = %s AND user_id = %s AND (event_type = 1 OR source_type = 11)
				ORDER BY id desc
				LIMIT 1, {$context_num}
				",
						array(
							$bot_id,
							$user_id,
						)
					)
				);
			}

			// error_log( "convasations: " . print_r( $convasations, true ) );
			foreach ( array_reverse( $convasations ) as $convasation ) {
				$role = $convasation->source_type < 11 ? 'user' : 'assistant';

				$message_object = json_decode( $convasation->message, false );
				if ( json_last_error() == JSON_ERROR_NONE ) {
					if ( $convasation->message_type == 1 ) {
						if ( is_array( $message_object ) ) {
							$message_object = $message_object[0];
						}
						if ( ! isset( $message_object->text ) ) {
							continue;
						}

						$messages[] = array(
							'role'    => $role,
							'content' => $message_object->text,
						);
					}
				}
			}
		}
		// function callが合った場合、ユーザーからの当初のプロンプトと、モデルからのfunction call呼出しメッセージを追加
		if ( isset( $addtional_messages ) ) {
			// merge addtional_messages to messages
			$messages = array_merge( $messages, $addtional_messages );
		}
		$quoted_contexts        = null;
		$quoted_message_context = $this->get_quoted_message_context( $event, $bot_id, $table_name );
		if ( ! empty( $quoted_message_context ) ) {
			$quoted_contexts = $quoted_message_context->content;
		}
		// 今回の質問
		if ( is_array( $prompt ) ) {
			foreach ( $prompt as $p ) {
				if ( $p['role'] == 'user' ) {
					if ( is_array( $p['content'] ) ) {
						$content = $p['content'];
					} else {
						$content = array(
							array(
								'type' => 'text',
								'text' => $p['content'],
							),
						);
					}
					if ( isset( $quoted_contexts ) ) {
						$content = array_merge( $content, $quoted_contexts );
					}
					$messages[] = array(
						'role'    => 'user',
						'content' => $content,
					);
				} else {
					$messages[] = $p;
				}
			}
		} else {
			$content = $prompt;
			if ( isset( $quoted_contexts ) ) {
				$content = array_merge( array( $content ), $quoted_contexts );
			}
			$messages[] = array(
				'role'    => 'user',
				'content' => $content,
			);
		}
		Logging::logging_with_redact(
			array(
				'messages' => $messages,
			),
			array()
		);

		// Define data
		$data                = array();
		$data['model']       = lineconnect::get_option( 'openai_model' );
		$data['messages']    = $messages;
		$data['temperature'] = floatval( lineconnect::get_option( 'openai_temperature' ) );
		$data['user']        = $user_id;

		$maxTokens = intval( lineconnect::get_option( 'openai_max_tokens' ) );
		if ( $maxTokens >= 0 ) {
			$data['max_completion_tokens'] = $maxTokens;
		}

		if ( lineconnect::get_option( 'openai_function_call' ) == 'on' ) {
			$callable_functions = $this->get_callable_functions( $user );
			if ( count( $callable_functions ) > 0 ) {
				$data['tools'] = $callable_functions;
			}
		}
		// error_log(print_r($data, true));
		// init curl
		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data ) );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );

		$result = curl_exec( $curl );
		if ( curl_errno( $curl ) ) {
			$responce = array( 'error' => curl_error( $curl ) );
		} else {
			$responce = json_decode( $result, true );
		}
		curl_close( $curl );
		Logging::logging_with_redact(
			array(
				'response' => $responce,
			),
			array( 'url' )
		);
		return $responce;
	}

	/**
	 * 返信元メッセージをログから取得して、OpenAI向けコンテキストに変換する。
	 *
	 * @param object $event イベントオブジェクト
	 * @param string $bot_id ボットID
	 * @param string $table_name ログテーブル名
	 * @return object|false
	 */
	function get_quoted_message_context( $event, $bot_id, $table_name ) {
		global $wpdb;

		$quoted_message_id = $event->{'message'}->{'quotedMessageId'} ?? null;
		if ( empty( $quoted_message_id ) ) {
			return false;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT event_type,source_type,message_type,message FROM {$table_name} WHERE bot_id = %s AND JSON_UNQUOTE(JSON_EXTRACT(message, '$.id')) = %s ORDER BY id DESC LIMIT 1",
				array( $bot_id, $quoted_message_id )
			)
		);
		if ( empty( $row ) ) {
			return false;
		}

		$message_object = json_decode( $row->message, false );
		if ( json_last_error() !== JSON_ERROR_NONE || empty( $message_object ) ) {
			return false;
		}

		$context = array();
		if ( $row->message_type == 1 ) {
			if ( is_array( $message_object ) ) {
				$message_object = $message_object[0];
			}
			if ( isset( $message_object->text ) && $message_object->text !== '' ) {
				$context[] = array(
					'type' => 'text',
					'text' => $message_object->text,
				);
			}
		} elseif ( $row->message_type == 2 ) {
			$image_url = false;
			if ( isset( $message_object->file_path ) ) {
				$image_url = \Shipweb\LineConnect\Utilities\FileSystem::get_lineconnect_file_url( $message_object->file_path );
			} elseif ( isset( $message_object->originalContentUrl ) ) {
				$image_url = $message_object->originalContentUrl;
			}

			if ( ! empty( $image_url ) ) {
				// 画像URLをtextとしてコンテキストに追加
				$context[] = array(
					'type' => 'text',
					'text' => 'Image URL: ' . $image_url,
				);
				$context[] = array(
					'type'      => 'image_url',
					'image_url' => array(
						'url' => $image_url,
					),
				);
			}
		}

		if ( empty( $context ) ) {
			return false;
		}

		return (object) array(
			'event_type'   => $row->event_type,
			'source_type'  => $row->source_type,
			'message_type' => $row->message_type,
			'message'      => json_encode( $message_object ),
			'role'         => $row->source_type < 11 ? 'user' : 'assistant',
			'content'      => $context,
		);
	}

	function get_callable_functions( $user ) {
		$callable_functions = array();
		// $enabled_functions  = lineconnect::get_option( ( 'openai_enabled_functions' ) );
		foreach ( \Shipweb\LineConnect\Action\Action::get_callable_functions( true ) as $function_name => $function_schema ) {
			// if ( isset( lineconnectConst::$callable_functions[ $function_name ] ) ) {
			if (
				! isset( $function_schema['role'] ) ||
				$function_schema['role'] === 'any' ||
				( ! empty( $user ) && $user->exists() && function_exists( 'current_user_can' ) && \current_user_can( $function_schema['role'] ) )
			) {
				$func = array(
					'type'     => 'function',
					'function' => array(
						'name'        => $function_name,
						'description' => $function_schema['description'],
					),
				);
				if ( ! empty( $function_schema['parameters'] ) ) {
					$parameters = array(
						'type' => 'object',
					);
					$properties = array();
					$required   = array();
					foreach ( $function_schema['parameters'] as $idx => $parameter_schema ) {
						$parameter_schema['name'] = $parameter_schema['name'] ?? 'param' . $idx;

						if ( isset( $parameter_schema['required'] ) && $parameter_schema['required'] ) {
							$required[] = $parameter_schema['name'];
						}

						$parameter_value = \Shipweb\LineConnect\Utilities\Schema::get_parameter_schema( $parameter_schema['name'], $parameter_schema );
						unset( $parameter_value['name'] );
						unset( $parameter_value['required'] );
						$properties[ $parameter_schema['name'] ] = $parameter_value;
					}
					$parameters['properties'] = $properties;
					$parameters['required']   = $required;

					$func['function']['parameters'] = $parameters;
				}

				$callable_functions[] = $func;
			}
			// }
		}
		return $callable_functions;
	}

	/**
	 * ポストバックイベントのデータを解析し、paramsにマージして返す関数
	 *
	 * @param array $event ポストバックイベントデータ
	 * @return array パラメータをマージしたイベント配列
	 */
	static function merge_postback_data_to_params( $event ) {
		// 初期値: paramsを取得
		$params = $event['postback']['params'] ?? array();

		// postback.dataを取得してクエリ文字列として扱う
		if ( ! empty( $event['postback']['data'] ) ) {
			parse_str( $event['postback']['data'], $data_params );

			// データが解析できた場合はparamsにマージする
			if ( is_array( $data_params ) ) {
				$params = array_merge( $params, $data_params );
				// $eventにマージ
				$event['postback']['params'] = $params;
			}
		}

		return $event;
	}
}
