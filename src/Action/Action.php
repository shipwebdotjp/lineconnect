<?php

namespace Shipweb\LineConnect\Action;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Action\ActionDefinitionInterface;
use Shipweb\LineConnect\Message\LINE\Builder as LineBuilder;
use Shipweb\LineConnect\Utilities\ActionParameterInjector;
use Shipweb\LineConnect\Utilities\PlaceholderReplacer;
use Shipweb\LineConnect\Utilities\PrepareArguments;
use Shipweb\LineConnect\Interaction\InteractionSession;

/**
 * Central dispatcher for LINE Connect actions.
 */
class Action {
	public static function get_callable_functions($only_enabled_gpt = false) {
		// get post form custom post type by WP_Query
		$functions = array();
		if ($only_enabled_gpt) {
			$enabled_functions = lineconnect::get_option(('openai_enabled_functions'));
		}

		foreach (self::get_lineconnect_action_data_array() as $name => $action) {
			if (! $only_enabled_gpt || in_array($name, $enabled_functions)) {
				$functions[$name] = array(
					'title'       => $action['title'],
					'description' => $action['description'],
					'parameters'  => $action['parameters'] ?? [],
					'namespace'   => $action['namespace'],
					'role'        => $action['role'],
				);
			}
		}
		return $functions;
	}

	/**
	 * Scan Definitions directory and load all action definitions.
	 *
	 * @return array<string,array> key => config
	 */
	private static function getAll(): array {
		$actions = [];
		$dir     = __DIR__ . '/Definitions';
		if (is_dir($dir)) {
			foreach (glob("$dir/*.php") as $file) {
				require_once $file;
				$class = 'Shipweb\\LineConnect\\Action\\Definitions\\' . basename($file, '.php');
				if (class_exists($class) && in_array(ActionDefinitionInterface::class, class_implements($class), true)) {
					$actions[$class::name()] = $class::config();
				}
			}
		}
		return $actions;
	}

	/**
	 * Get mapping of action name => title.
	 *
	 * @return array<string,string>
	 */
	public static function get_lineconnect_action_name_array(): array {
		$actions = self::getAll();
		$list    = apply_filters(lineconnect::FILTER_PREFIX . 'actions', $actions);
		$out     = [];
		foreach ($list as $name => $cfg) {
			$out[$name] = $cfg['title'] ?? $name;
		}
		return $out;
	}

	/**
	 * Get full action configuration array.
	 *
	 * @return array<string,array>
	 */
	public static function get_lineconnect_action_data_array(): array {
		$actions = self::getAll();
		return apply_filters(lineconnect::FILTER_PREFIX . 'actions', $actions);
	}

	/**
	 * Execute a series of actions.
	 *
	 * @param array  $actions       List of action calls (with action_name, parameters, response_return_value).
	 * @param array  $chains        Chain definitions.
	 * @param object $event         LINE webhook event object.
	 * @param string $secret_prefix Channel secret prefix.
	 * @param string $scenario_id   Scenario ID.
	 * @param InteractionSession $session
	 * @return array{success:bool,messages:array,results:array}
	 */

	static function do_action($actions, $chains, $event = null, $secret_prefix = null, $scenario_id = null, ?InteractionSession $session = null) {
		// require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		$results = array();
		$message = array();
		$injection_data = array(
			'return' => array(),
			'webhook' => self::merge_postback_data_to_params(json_decode(json_encode($event), true)),
			'user' =>  $event ? lineconnect::get_userdata_from_line_id($secret_prefix, $event->{'source'}->{'userId'}) : [],
			'session' => $session ? $session->get_answers() : [],
		);
		// error_log(print_r($injection_data['user'], true));
		foreach ($actions as $action_idx => $action) {
			$error = null;
			if (isset($action['action_name'])) {
				//$function_schema = get_post_meta( $action['action_id'], lineconnect::META_KEY__ACTION_DATA, true );
				$function_schema = self::get_lineconnect_action_data_array()[$action['action_name']];
				if (isset($function_schema)) {
					$function_name = $action['action_name']; //$function_schema['function'];
					if (isset($function_schema['namespace'])) {
						if (! class_exists($function_schema['namespace'])) {
							$error = array(
								'error' => "NameError: namespace '$function_schema[namespace]' is not exists",
								'abort' => true,
							);
						}
						try {
							$class_name = new $function_schema['namespace']();
							if ($secret_prefix && method_exists($class_name, 'set_secret_prefix')) {
								$class_name->set_secret_prefix($secret_prefix);
							}
							if ($event && method_exists($class_name, 'set_event')) {
								$class_name->set_event($event);
							}
							if ($scenario_id && method_exists($class_name, 'set_scenario_id')) {
								$class_name->set_scenario_id($scenario_id);
							}
							if ($session && method_exists($class_name, 'set_session')) {
								$class_name->set_session($session);
							}
						} catch (\Exception $e) {
							$error = array(
								'error' => "NameError: namespace '$function_schema[namespace]' is not exists",
								'abort' => true,
							);
						}
						if (! method_exists($class_name, $function_name)) {
							$error = array(
								'error' => "NameError: name '$function_name' in namespace '$function_schema[namespace]' is not defined",
								'abort' => true,
							);
						}
					} elseif (! function_exists($function_name)) {
						$error = array(
							'error' => "NameError: name '$function_name' is not exists",
							'abort' => true,
						);
					}
					// error_log('class response:' . print_r(array($class_name, $function_name), true));
					if (! isset($error)) {
						$arguments_array = null;
						if (isset($function_schema['parameters'])) {

							// error_log('parameters:' . print_r($function_schema['parameters'], true));
							// error_log('action:' . print_r($action, true));

							if (!array_key_exists('parameters', $action) || empty($action['parameters'])) {
								$action['parameters'] = [];
							}
							// error_log('action parameters:' . print_r($injection_data, true));
							$action_parameters =  \Shipweb\LineConnect\Utilities\ActionParameterInjector::inject_param($action_idx, $action['parameters'], $chains);
							$arguments_parsed = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::prepare_arguments($action_parameters, $function_schema['parameters'], $injection_data);
							$arguments_array = \Shipweb\LineConnect\Utilities\PrepareArguments::arguments_object_to_array($arguments_parsed, $function_schema['parameters']);
						}
						// error_log('arguments:' . print_r($arguments_array, true));
						if (isset($function_schema['namespace'])) {
							if (empty($function_schema['parameters'])) {
								$response = call_user_func(array($class_name, $function_name));
							} else {
								$response = call_user_func_array(array($class_name, $function_name), $arguments_array);
							}
						} elseif (empty($function_schema['parameters'])) {
							$response = call_user_func($function_name);
						} else {
							$response = call_user_func_array($function_name, $arguments_array); // $response = $function_name( $arguments_array );
						}
						$injection_data['return'][$action_idx + 1] = $response;
						// error_log("val" . ($action['response_return_value'] === true ? 'true' : 'false'));
						if (isset($action['response_return_value']) && filter_var($action['response_return_value'], FILTER_VALIDATE_BOOLEAN)) {
							// error_log(print_r($response, true));
							// error_log(print_r($action['response_return_value'], true));
							$message[] = \Shipweb\LineConnect\Message\LINE\Builder::get_line_message_builder($response);
						}
					} else {
						// $message[] = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($error['error']);
					}
				}
			}
			$results[$action_idx] = array(
				'success' => !isset($error),
				'response' => $response ?? null,
				'error' => $error ?? null,
			);
		}

		return array(
			//'resultsの配列の'success'が全てtrueの場合はtrue、一つでもfalseがある場合はfalse
			'success' => (count(array_filter(array_column($results, 'success'))) === count($results)) ? true : false,
			'messages' => $message,
			'results' => $results,
		);
	}

	/**
	 * ポストバックイベントのデータを解析し、paramsにマージして返す関数
	 * 
	 * @param array $event ポストバックイベントデータ
	 * @return array パラメータをマージしたイベント配列
	 */
	static function merge_postback_data_to_params($event) {
		// 初期値: paramsを取得
		$params = $event['postback']['params'] ?? [];

		// postback.dataを取得してクエリ文字列として扱う
		if (!empty($event['postback']['data'])) {
			parse_str($event['postback']['data'], $data_params);

			// データが解析できた場合はparamsにマージする
			if (is_array($data_params)) {
				$params = array_merge($params, $data_params);
				// $eventにマージ
				$event['postback']['params'] = $params;
			}
		}

		return $event;
	}

	/**
	 * アクションスキーマを返す
	 * 
	 * @param array one_of
	 * @return void
	 */
	public static function build_action_schema_items(array &$one_of): void {
		$action_array = self::get_lineconnect_action_data_array();

		if (!empty($action_array)) {
			foreach ($action_array as $name => $action) {
				$properties = array(
					'action_name' => array(
						'type'    => 'string',
						'const'   => $name,
						'default' => $name,
					),
					'response_return_value' => array(
						'type'    => 'boolean',
						'default' => true,
						'title'   => __('Send the return value as a response', lineconnect::PLUGIN_NAME),
						'description' => __('Send the return value of this action as a response message by LINE message', lineconnect::PLUGIN_NAME),
					),
				);

				if (isset($action['parameters'])) {
					$parameters = $action['parameters'];
					$parameters_properties = array();

					if (!empty($parameters)) {
						foreach ($parameters as $idx => $parameter) {
							$key = $parameter['name'] ?? 'param' . $idx;
							$val = \Shipweb\LineConnect\Utilities\Schema::get_parameter_schema($key, $parameter);
							$parameters_properties[$key] = $val;
						}
					}

					if (!empty($parameters_properties)) {
						$properties['parameters'] = array(
							'type'       => 'object',
							'title'      => __('Parameters', lineconnect::PLUGIN_NAME),
							'properties' => $parameters_properties,
						);
					}
				}

				$one_of[] = array(
					'title'      => $action['title'],
					'properties' => $properties,
					'required'   => array('action_name'),
				);
			}
		} else {
			$one_of = array(
				array(
					'title'      => __('Please add action first', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'action_name' => array(
							'type' => 'null',
						),
					),
				),
			);
		}
	}
}
