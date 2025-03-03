<?php

/**
 * Lineconnect Action Class
 *
 * LINE Connect Action
 *
 * @category Components
 * @package  Action
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */


class lineconnectAction {
	/**
	 * Return action array object post_id and title
	 */
	static function get_lineconnect_action_name_array() {
		/*
		$args              = array(
			'post_type'      => lineconnectConst::POST_TYPE_ACTION,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);
		$action_name_array = array();
		$posts             = get_posts( $args );
		*/
		$lineconnect_actions = apply_filters(lineconnect::FILTER_PREFIX . 'actions', lineconnectConst::$lineconnect_actions);
		foreach ($lineconnect_actions as $name => $action) {
			$action_name_array[$name] = $action['title'];
		}
		return $action_name_array;
	}

	/**
	 * Return action array object post_id and action data
	 */
	static function get_lineconnect_action_data_array() {
		/*
		$args              = array(
			'post_type'      => lineconnectConst::POST_TYPE_ACTION,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);
		$posts             = get_posts( $args );
		*/
		$lineconnect_actions = apply_filters(lineconnect::FILTER_PREFIX . 'actions', lineconnectConst::$lineconnect_actions);
		return $lineconnect_actions;
		/*
		$action_data_array = array();
		foreach ( $lineconnect_actions as $name => $action ) {
			$action_data_array[ $name ] = array(
				'title'       => $post->post_title,
				'action_data' => get_post_meta( $post->ID, lineconnect::META_KEY__ACTION_DATA, true ),
			);
		}
		return $action_data_array;
		*/
	}

	static function do_action($actions, $chains, $event = null, $secret_prefix = null, $scenario_id = null) {
		require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		$results = array();
		$message = array();
		$injection_data = array(
			'return' => array(),
			'webhook' => self::merge_postback_data_to_params(json_decode(json_encode($event), true)),
			'user' =>  $event ? lineconnect::get_userdata_from_line_id($secret_prefix, $event->{'source'}->{'userId'}) : [],
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
							$action_parameters =  lineconnectUtil::inject_param($action_idx, $action['parameters'], $chains);
							$arguments_parsed = lineconnectUtil::prepare_arguments($action_parameters, $function_schema['parameters'], $injection_data);
							$arguments_array = lineconnectUtil::arguments_object_to_array($arguments_parsed, $function_schema['parameters']);
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
							$message[] = lineconnectUtil::get_line_message_builder($response);
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
							$val = lineconnectUtil::get_parameter_schema($key, $parameter);
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
