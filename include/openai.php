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

class lineconnectOpenAi {

	function getResponseByChatGPT($user_id, $bot_id, $prompt, $addtional_messages = null) {
		//OpenAI APIにリクエストを送信
		$AiMessage = $this->getResponse($user_id, $bot_id, $prompt, $addtional_messages);
		error_log("response Message:" . print_r($AiMessage, true));
		//レスポンスを処理
		if (isset($AiMessage['error'])) {
			$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($AiMessage['error']['type'] . ": " . $AiMessage['error']['message']);
			$responseByAi = false;
		} elseif (isset($AiMessage['choices'][0]['message']['function_call'])) {
			//add old prompt to addtional messages
			$addtional_messages[] = $prompt;

			$function_name = $AiMessage['choices'][0]['message']['function_call']['name'];
			$arguments = $AiMessage['choices'][0]['message']['function_call']['arguments'];
			error_log("function_name:" . $function_name . " arguments:" . print_r($arguments, true));
			$error = null;
			if (
				!in_array($function_name, lineconnect::get_option(('openai_enabled_functions'))) ||
				!isset(lineconnectConst::$callable_functions[$function_name])
			) {
				$error = ["error" => "NameError: name '$function_name' is not exists"];
			} else {
				$function_schema = lineconnectConst::$callable_functions[$function_name];
				if (isset($function_schema["role"]) && $function_schema["role"] != "any") {
					// check if user has capability
					$user = lineconnect::get_wpuser_from_line_id($bot_id, $user_id);
					if (empty($user) || !$user->exists() || !user_can($user, $function_schema["role"])) {
						$error = ["error" => "PermissionError: you have no permission to call function '$function_name'"];
					}
				}
				if (isset($function_schema["namespace"])) {
					if (!class_exists($function_schema["namespace"])) {
						$error = ["error" => "NameError: namespace '$function_schema[namespace]' is not exists", "abort" => true];
					}
				}
				if ($function_schema["namespace"] === "lineconnectFunctions") {
					$functions = new lineconnectFunctions($user_id, $bot_id);
				} else {
					$functions = new $function_schema["namespace"];
				}
				// check if function exists
				if (!method_exists($functions, $function_name)) {
					$error = ["error" => "NameError: name '$function_name' in namespace '$function_schema[namespace]' is not defined", "abort" => true];
				}

				//parse arguments
				try {
					$arguments_parsed = json_decode($arguments, true, 512, JSON_THROW_ON_ERROR);
				} catch (\JsonException $e) {
					$error = ["error" => "ParseError: arguments is not valid json"];
				}
			}

			if (!isset($error)) {
				$response = $functions->$function_name($arguments_parsed);

				error_log("function response:" .  print_r($response, true));
				$prompt = array(
					"role" => "function",
					"name" => $function_name,
					"content" => json_encode($response),
				);
				$addtional_messages[] = $AiMessage['choices'][0]['message'];
				return $this->getResponseByChatGPT($user_id, $bot_id, $prompt, $addtional_messages);
			} else {
				$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($error['error']);
				$responseByAi = false;
			}
		} elseif (isset($AiMessage['choices'][0]['message']['content'])) {
			$message =  new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($AiMessage['choices'][0]['message']['content']);
			$responseByAi = true;
		}
		return ['message' => $message, 'responseByAi' => $responseByAi, 'rowResponse' => $AiMessage];
	}

	//ChatGPTで応答作成
	function getResponse($user_id, $bot_id, $prompt, $addtional_messages = null) {
		global $wpdb, $secret_prefix;

		$apiKey = lineconnect::get_option('openai_secret');
		$url = 'https://api.openai.com/v1/chat/completions';

		$headers = array(
			"Authorization: Bearer {$apiKey}",
			"Content-Type: application/json"
		);

		// Define messages
		$messages = array();

		if (lineconnect::get_option('openai_system')) {
			$system_message = [
				"role" => "system",
				"content" => lineconnect::get_option('openai_system')
			];

			$messages[] = $system_message;
		}

		//過去の文脈を取得
		if (isset($user_id)) {
			$table_name = $wpdb->prefix . lineconnectConst::TABLE_BOT_LOGS;
			$context_num = intval(lineconnect::get_option('openai_context') * 2);

			$limit_normal = intval(lineconnect::get_option('openai_limit_normal'));
			$limit_linked = intval(lineconnect::get_option('openai_limit_linked'));
			$overlimit = false;
			if ($limit_normal != -1 || $limit_linked != -1) {

				$convasation_count = $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT(id) 
					FROM {$table_name}
					WHERE event_type = 1 AND source_type = 11 AND user_id = %s AND bot_id = %s AND DATE(timestamp) = CURDATE()
					",
						array(
							$user_id,
							$bot_id,
						)
					)
				);

				//メタ情報からLINEユーザーIDでユーザー検索
				$user = lineconnect::get_wpuser_from_line_id($secret_prefix, $user_id);
				if ($user) { //ユーザーが見つかればすでに連携されている
					$limit_count = $limit_linked;
				} else {
					$limit_count = $limit_normal;
				}
				if ($limit_count != -1 && $convasation_count >= $limit_count) {
					return  ['error' => [
						'type' => 'エラー',
						'message' => str_replace('%limit%', $limit_count, lineconnect::get_option('openai_limit_message'))
					]];
				}
			}

			$convasations = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT event_type,source_type,message_type,message 
				FROM {$table_name}
				WHERE event_type = 1 AND user_id = %s AND bot_id = %s
				ORDER BY id desc
				LIMIT 0, {$context_num}
				",
					array(
						$user_id,
						$bot_id,
					)
				)
			);

			foreach (array_reverse($convasations) as $convasation) {
				$role = $convasation->source_type == 11 ? "assistant" : "user";
				$message_object = json_decode($convasation->message, false);
				if (json_last_error() == JSON_ERROR_NONE) {
					if ($convasation->message_type == 1 && isset($message_object->text)) {
						$messages[] = [
							"role" => $role,
							"content" => $message_object->text
						];
					}
				}
			}
		}
		//function callが合った場合、ユーザーからの当初のプロンプトと、モデルからのfunction call呼出しメッセージを追加
		if (isset($addtional_messages)) {
			//merge addtional_messages to messages
			$messages = array_merge($messages, $addtional_messages);
		}

		//今回の質問
		if (is_array($prompt)) {
			$messages[] = $prompt;
		} else {
			$messages[] = [
				"role" => "user",
				"content" => $prompt
			];
		}



		// Define data
		$data = array();
		$data["model"] = lineconnect::get_option('openai_model');
		$data["messages"] = $messages;
		$data["temperature"] =  floatval(lineconnect::get_option('openai_temperature'));
		$data["user"] = $user_id;

		$maxTokens = intval(lineconnect::get_option('openai_max_tokens'));
		if ($maxTokens >= 0) {
			$data["max_tokens"] = $maxTokens;
		}

		if (lineconnect::get_option('openai_function_call') == 'on') {
			$callable_functions = $this->get_callable_functions($user);
			if (count($callable_functions) > 0) {
				$data["functions"] = $callable_functions;
			}
		}

		error_log("request messages:" . print_r($data, true));

		// init curl
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		if (curl_errno($curl)) {
			$responce = ['error' => curl_error($curl)];
		} else {
			$responce = json_decode($result, true);
		}
		curl_close($curl);

		return $responce;
	}

	function get_callable_functions($user) {
		$callable_functions = [];
		$enabled_functions = lineconnect::get_option(('openai_enabled_functions'));
		foreach ($enabled_functions as $function_name) {
			if (isset(lineconnectConst::$callable_functions[$function_name])) {
				$function_schema = lineconnectConst::$callable_functions[$function_name];
				if (
					!isset($function_schema["role"]) ||
					$function_schema["role"] === "any" ||
					(!empty($user) && $user->exists() && user_can($user, $function_schema["role"]))
				) {
					$callable_functions[] = [
						"name" => $function_name,
						"description" => $function_schema['description'],
						"parameters" => $function_schema["parameters"],
					];
				}
			}
		}
		return $callable_functions;
	}
}
