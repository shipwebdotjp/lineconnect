<?php

/**
 * Lineconnect Util Class
 *
 * Util Class
 *
 * @category Components
 * @package  Util
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

use \Shipweb\LineConnect\Scenario\Scenario;
use Shipweb\LineConnect\PostType\Audience\Audience as Audience;
use Shipweb\LineConnect\PostType\Message\Message as SLCMessage;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\RichMenu\RichMenu;


class lineconnectUtil {
	public static function is_empty($var = null) {
		if (empty($var) && 0 !== $var && '0' !== $var) { // 論理型のfalseを取り扱う場合は、更に「&& false !== $var」を追加する
			return true;
		} else {
			return false;
		}
	}

	/**
	 * パラメーターをアクションチェインの値で置き換えて返す
	 * 
	 * @param int $action_idx アクションのインデックス(1から始まる現在処理しているアクションの番号)
	 * @param array $action_parameters アクションのパラメーター。キーは引数名値はその引数に渡す値の配列
	 * @param array $chains アクションチェインの配列 各要素はtoとdataの連想配列
	 * @return array $action_parameters 置き換えられたパラメーター
	 */

	public static function inject_param($action_idx, $action_parameters, $chains) {
		if (is_array($chains)) {
			foreach ($chains as $chain) {
				//error_log(print_r($chain, true));
				if (isset($chain['to']) && isset($chain['data'])) {
					// 'to': '1.param1' や '1.ary.0' や '1.atts.link.label' など
					// 'to' の値を '.' で分割してアクション番号と引数名(+添字のパス)を取得
					list($chain_action_idx, $param_path) = explode('.', $chain['to'], 2);
					// アクション番号が現在のアクションインデックスと一致するか確認
					// $param_pathが指し示す$action_parametersの該当する位置に$chain['data']をセットしたい。
					// 'data': 文字列や、プレースホルダー 例)'{{$.return.1}}'
					if ((int)$chain_action_idx === $action_idx + 1) {
						// $param_pathが指し示す$action_parametersの該当する位置に$chain['data']をセットする
						$action_parameters = self::set_param_by_path($action_parameters, $param_path, $chain['data']);
					}
				}
			}
		}
		// error_log(print_r($action_parameters, true));
		return $action_parameters;
	}

	/**
	 * .で結合された形式のパスから、対象配列の該当場所へチェインデータをセットする関数
	 * @param array $action_parameters 対象の配列
	 * @param string $param_path .で結合された形式のパス
	 * @param mixed $data セットするチェインデータ
	 * @return array $action_parameters セットされた後の配列
	 */
	public static function set_param_by_path($action_parameters, $param_path, $data) {
		$keys = explode('.', $param_path); // パスパラメータを配列に変換
		$current_data = &$action_parameters; // 参照渡しで更新できるようにする

		foreach ($keys as $key) {
			if (!isset($current_data[$key])) {
				$current_data[$key] = []; // 存在しないキーの場合は空の配列を作成
			}
			$current_data = &$current_data[$key]; // 現在のデータオブジェクトを次のキーのデータオブジェクトに更新
		}

		$current_data = $data; // 最終的なキーでデータをセット
		return $action_parameters;
	}

	/**
	 * 関数のパラメーターのプレースホルダーが含まれていれば、対象データへ置換してパラメーターを用意する関数
	 * @param array $parameters アクションから渡されるパラメーター
	 * @param array $parameters_schemas 関数が取りうるパラメーターのスキーマ
	 * @param array $injection_data 前のアクションの戻り値など注入用データ
	 * @return array $parameters 置換されたパラメーター
	 */
	public static function prepare_arguments($parameters, $parameters_schemas, $injection_data) {
		if (is_array($parameters_schemas)) {
			foreach ($parameters_schemas as $idx => $parameter_schema) {
				$parameter_schema['name'] = $parameter_schema['name'] ?? 'param' . $idx;
				if (isset($parameters[$parameter_schema['name']])) {
					$parameters[$parameter_schema['name']] = self::replace_object_placeholder($parameters[$parameter_schema['name']], $injection_data);
				}
			}
		}
		return $parameters;
	}

	/**
	 * オブジェクトの値にプレースホルダーが含まれる場合、再帰的置換する関数
	 * @param mixed $object 対象となるオブジェクト(オブジェクトとは限らず、メッセージであったり、文字列や配列の場合もある) 
	 * @param array $injection_data 前のアクションの戻り値など注入用データ
	 * @return mixed $object 置換されたオブジェクト
	 */
	public static function replace_object_placeholder($object, $injection_data) {
		if ($object instanceof \LINE\LINEBot\MessageBuilder) {
			$object = self::replace_object_placeholder($object->buildMessage(), $injection_data);
		} elseif (is_object($object)) {
			// オブジェクトの場合、オブジェクトのキーを再帰的に処理する
			foreach ($object as $key => $value) {
				$object->{$key} = self::replace_object_placeholder($value, $injection_data);
			}
		} else if (is_array($object)) {
			// 配列の場合、配列の要素を再帰的に処理する
			foreach ($object as $key => $value) {
				$object[$key] = self::replace_object_placeholder($value, $injection_data);
			}
		} elseif (is_string($object)) {
			//　対象が文字列の場合、プレースホルダーを置換する
			/*
			if (preg_match('/^{{(.*?)}}$/', $object, $matches)) {
				// 全体がプレースホルダーの場合は、そのまま全置換
				$object = self::replace_injection_data($matches[1], $injection_data);
			} else {
				// 文字列の一部にプレースホルダーが含まれている場合、コールバック関数を用いて置換する
				$object = preg_replace_callback('/{{(.*?)}}/', function ($matches) use (&$injection_data) {
					$replaced = self::replace_injection_data($matches[1], $injection_data);
					return is_null($replaced) ? '' : (string)$replaced;
				}, $object);
			}*/
			// $.から始まり、全体が1つのプレースホルダーのみの場合はLegacy(オブジェクト置換も含む)
			if (preg_match('/^{{\s*(\$\.[^{}]+)\s*}}$/', $object, $matches)) {
				$object = self::legacy_replace_injection_data($matches[1], $injection_data);
			} elseif (strpos($object, '{{') !== false || strpos($object, '{%') !== false) {
				$object = preg_replace('/\{\{\s*\$\.(.*?)\s*\}\}/', '{{ $1 }}', $object);
				$object = self::replace_injection_data($object, $injection_data);
			}
		}
		return $object;
	}

	public static function replace_injection_data($injection_path, $injection_data) {
		// if php 8.1 or later then use twig
		if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
			return self::twig_replace_injection_data($injection_path, $injection_data);
		} else {
			// if php 8.0 or earlier then use legacy_replace_injection_data
			return self::legacy_replace_injection_data($injection_path, $injection_data);
		}
	}

	public static function twig_replace_injection_data($injection_path, $injection_data) {
		$loader = new \Twig\Loader\ArrayLoader(
			[
				'template' => $injection_path,
			]
		);
		$twig = new \Twig\Environment($loader);
		return $twig->render('template', $injection_data ?? array());
	}

	/**
	 * プレースホルダーを実際のデータに置換する関数
	 * @param $injection_path どのデータで置換するかを示す文字列。例) $.return.1 / $.webhook.message.text / $.user.display_name
	 * @param array $injection_data 前のアクションの戻り値やイベントデータ、ユーザーデータなどの置き換え用データ
	 * @return mixed $value 置換された値
	 */
	public static function legacy_replace_injection_data($injection_path, $injection_data) {
		$injection_path = trim($injection_path);
		// パスの先頭が '$' または '$.' で始まっている場合は削除
		if (strpos($injection_path, '$') === 0) {
			// 先頭の '$' を除去し、さらに先頭の '.' があれば除去
			$path = ltrim(substr($injection_path, 1), '.');

			// パスが空だった場合、nullを返す
			if ($path === '') {
				return null;
			}
		}

		// パスをドット(.)、開きブラケット([)、閉じブラケット(])で分割し、
		// 空の要素（例: `[1]` の後の `]` の分割結果など）を除去する。
		// 例: "return[1][0].name" -> ["return", "1", "0", "name"]
		// 例: "data.items[0]" -> ["data", "items", "0"]
		$parts = preg_split('/[.\\[\\]]/', $path, -1, PREG_SPLIT_NO_EMPTY);

		// $parts が false や空配列になるケースも考慮 (preg_splitのエラーなど)
		if (empty($parts)) {
			// パスが '$[' のような不正な形式だった場合などもここに該当しうる
			return null;
		}

		$value = $injection_data;

		foreach ($parts as $part) {
			if (is_array($value)) {
				// 現在の値が配列の場合
				// キーまたはインデックスが存在するか確認
				// array_key_exists はキーが存在し値が null の場合と、キー自体が存在しない場合を区別できる
				if (array_key_exists($part, $value)) {
					$value = $value[$part];
				} else {
					// 配列にキー/インデックスが存在しない
					return null;
				}
			} elseif (is_object($value)) {
				// 現在の値がオブジェクトの場合
				// プロパティが存在するか確認 (public プロパティのみアクセス可能)
				if (property_exists($value, $part)) {
					// PHP 8.0 以降では $value->{$part} より $value->$part が推奨されるが、
					// $part が数値文字列の場合もあるため、$value->{$part} を使う方が安全
					$value = $value->{$part};
				} else {
					// オブジェクトにプロパティが存在しない
					return null;
				}
			} else {
				// パスの途中で配列でもオブジェクトでもなくなった場合 (例: 文字列や数値にアクセスしようとした)
				return null;
			}
		}

		return $value;
	}



	/**                                                                                                                                                                                                                                     
	 * オブジェクトの値にプレースホルダーが含まれるかチェックする関数                                                                                                                                                                       
	 * @param mixed $object 検査対象のオブジェクト                                                                                                                                                                                          
	 * @return bool プレースホルダーが含まれる場合true                                                                                                                                                                                      
	 */
	public static function has_object_placeholder($object) {
		if ($object instanceof \LINE\LINEBot\MessageBuilder) {
			// メッセージビルダーはメッセージ配列化してチェック                                                                                                                                                                             
			return self::has_object_placeholder($object->buildMessage());
		} elseif (is_object($object)) {
			foreach (get_object_vars($object) as $value) {
				if (self::has_object_placeholder($value)) {
					return true;
				}
			}
		} elseif (is_array($object)) {
			foreach ($object as $value) {
				if (self::has_object_placeholder($value)) {
					return true;
				}
			}
		} elseif (is_string($object)) {
			// 全体がプレースホルダー or 部分にプレースホルダーを含む場合                                                                                                                                                                   
			return preg_match('/{{.*?}}|{%.*?%}/', $object) === 1;
		}
		return false;
	}


	/**
	 * 連想配列を関数の引数の順番に合わせて配列に変換する関数
	 * @param $arguments_parsed 引数の連想配列
	 * @param array $parameters_array 呼び出す関数がとる引数のスキーマ
	 * @return array $arguments_array 引数の配列
	 */
	public static function arguments_object_to_array($arguments_parsed, $parameters_array) {
		$arguments_array = array();
		if (is_array($parameters_array)) {
			foreach ($parameters_array as $idx => $parameter_schema) {
				$parameter_schema['name'] = $parameter_schema['name'] ?? 'param' . $idx;
				if (isset($arguments_parsed[$parameter_schema['name']])) {
					$arguments_array[] = $arguments_parsed[$parameter_schema['name']];
				}
			}
		}
		return $arguments_array;
	}

	public static function local_mktime() {
		$defaults = array(
			date("H"),
			date("i"),
			date("s"),
			date("n"),
			date("j"),
			date("Y"),
		);
		$args = func_get_args();
		$param = array_merge($defaults, $args);
		$offset = get_option('gmt_offset') * 60 * 60;
		return mktime($param[0], $param[1], $param[2], $param[3], $param[4], $param[5]); // + $offset;
	}

	public static function local_strtotime($time, $now = null) {
		$offset = get_option('gmt_offset') * 60 * 60;
		if ($now == null) {
			$now = time();
		}

		return strtotime($time, $now) + $offset;
	}

	public static function get_line_message_builder($source, $args = null) {
		if ($source instanceof \LINE\LINEBot\MessageBuilder) {
			return $source;
		}
		if (is_numeric($source)) {
			$message = SLCMessage::get_lineconnect_message($source, $args);
			if ($message) {
				return $message;
			}
		}
		if (is_string($source)) {
			return self::get_line_message_builder_from_string($source);
		}

		if (is_array($source)) {
			//message formdataの場合はformData_to_multimessageを呼び出す
			$isFormdata = true;
			if (count($source) > 10) {
				$isFormdata = false;
			}
			for ($i = 0; $i < count($source); $i += 2) {
				if (!empty($source[$i]) && !empty($source[$i + 1])) {
					if (!isset($source[$i]['type']) ||  ! in_array(
						$source[$i]['type'],
						array(
							'text',
							'sticker',
							'image',
							'video',
							'audio',
							'location',
							'imagemap',
							'button_template',
							'confirm_template',
							'carousel_template',
							'image_carousel_template',
							'flex',
							'raw',
						)
					)) {
						$isFormdata = false;
					}
				}
			}
			if ($isFormdata) {
				return SLCMessage::formData_to_multimessage($source, $args);
			}
		}
		return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(print_r($source, true));
	}

	/**
	 * 文字列からLINEメッセージビルダーを作成する関数
	 * @param string $source ソース: 文字列
	 * @return \LINE\LINEBot\MessageBuilder メッセージビルダー
	 */
	public static function get_line_message_builder_from_string($source) {
		// 文字列がJSON形式の場合は、JSONをデコードしてオブジェクトに変換し、RawMessageBuilderを作成
		$json = self::extractAndDecodeJson($source);
		if (is_array($json) && isset($json['type']) && in_array($json['type'], lineconnectConst::LINE_MESSAGE_TYPES)) {
			$rawmessage = new \LINE\LINEBot\MessageBuilder\RawMessageBuilder($json);
			// validate message
			$channels = lineconnect::get_all_channels();
			$validate_response = Builder::validateMessage('reply', $channels[0], $rawmessage);
			if ($validate_response['success']) {
				return $rawmessage;
			}
		}
		// 文字列がJSON形式でない場合は、通常のテキストメッセージビルダーを作成
		return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($source);
	}

	public static function extractAndDecodeJson(string $input) {
		// 最初の '{' の位置を探す
		$startPos = strpos($input, '{');
		if ($startPos === false) {
			// '{' がなければJSONではない
			return null;
		}

		// 最後の '}' の位置を探す
		$endPos = strrpos($input, '}');
		if ($endPos === false || $endPos < $startPos) {
			// '}' がなければJSONではない、または順序がおかしい
			return null;
		}

		// '{' から '}' までの文字列を抽出
		$jsonString = substr($input, $startPos, $endPos - $startPos + 1);

		// json_decodeして返す
		$decoded = json_decode($jsonString, true);

		// JSONとして正しくない場合はnullを返す
		if (json_last_error() !== JSON_ERROR_NONE) {
			return null;
		}

		return $decoded;
	}

	/**
	 * 引数の型に応じてオーディエンスを取得する関数
	 * @param mixed $source ソース: 数値→オーディエンスの投稿ID、オブジェクト→オーディエンスオブジェクトとして扱う
	 * @return object オーディエンスオブジェクト
	 */
	public static function get_lineconnect_audience($source, $args = null) {
		if (is_numeric($source)) {
			$audience = Audience::get_lineconnect_audience($source, $args);
			if ($audience) {
				return $audience;
			}
		}
		if (is_object($source)) {
			if (!empty($args)) {
				return self::replace_object_placeholder($source, $args);
			} else {
				return $source;
			}
		}
		return null;
	}

	public static function line_id_row($line_id, $secret_prefix) {
		global $wpdb;
		$table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
		$line_id_row        = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name_line_id} WHERE line_id = %s and channel_prefix = %s",
				array(
					$line_id,
					$secret_prefix,
				)
			),
			'ARRAY_A'
		);
		return $line_id_row;
	}


	/**
	 * 動的に翻訳を行う関数
	 * @param $text 翻訳対象のテキスト
	 * @return string $translated 翻訳後のテキスト
	 */
	public static function dynamic_translate($text) {
		if (preg_match('/The request body has (\d+) error/', $text, $matches)) {
			$translated = sprintf(_n('The request body has %s error.', 'The request body has %s errors.', $matches[1], lineconnect::PLUGIN_NAME), number_format($matches[1]));
		} elseif (strpos('Invalid reply token', $text) !== false) {
			$translated = __('Invalid reply token.', lineconnect::PLUGIN_NAME);
		} elseif (preg_match('/The property, (.*?), in the request body is invalid /', $text, $matches)) {
			$translated = sprintf(__('The property, %s, in the request body is invalid.', lineconnect::PLUGIN_NAME), $matches[1]);
		} elseif (strpos('The request body could not be parsed as JSON', $text) !== false) {
			$translated = __('The request body could not be parsed as JSON.', lineconnect::PLUGIN_NAME);
		} elseif (preg_match('/The content type, (.*?), is not supported/', $text, $matches)) {
			$translated = sprintf(__('The content type, %s, is not supported.', lineconnect::PLUGIN_NAME), $matches[1]);
		} elseif (strpos('Authentication failed due to the following reason:', $text) !== false) {
			$translated = __('Authentication failed.', lineconnect::PLUGIN_NAME);
		} elseif (strpos('Access to this API is not available for your account', $text) !== false) {
			$translated = __('Access to this API is not available for your account.', lineconnect::PLUGIN_NAME);
		} elseif (strpos('Failed to send messages', $text) !== false) {
			$translated = __('Failed to send messages.', lineconnect::PLUGIN_NAME);
		} elseif (strpos('You have reached your monthly limit.', $text) !== false) {
			$translated = __('You have reached your monthly limit.', lineconnect::PLUGIN_NAME);
		} elseif (strpos('Not found', $text) !== false) {
			$translated = __('Not found.', lineconnect::PLUGIN_NAME);
		} elseif (strpos('May not be empty', $text) !== false) {
			$translated = __('May not be empty.', lineconnect::PLUGIN_NAME);
		} elseif (preg_match('/Size must be between ([0-9,]+) and ([0-9,]+)/', $text, $matches)) {
			$translated = sprintf(__('Size must be between %s and %s.', lineconnect::PLUGIN_NAME), $matches[1], $matches[2]);
		} else {
			$translated = $text;
		}
		return $translated;
	}

	/**
	 * 配列やオブジェクトを展開してキーを.でつなげた形でフラット化する関数
	 * @param mixed $array_or_object 展開したい配列やオブジェクト
	 * @param string $prefix 展開したい配列やオブジェクトのキーの接頭辞
	 * @return array $result 展開後の配列
	 */
	public static function flat($ary, $prefix = '') {
		$result = [];
		foreach ($ary as $key => $value) {
			if (is_array($value)) {
				$result = array_merge($result, self::flat($value, $prefix . $key . '.'));
			} else {
				$result[$prefix . $key] = $value;
			}
		}
		return $result;
	}

	/**
	 * lineconnect用の指定されたフォルダをアップロードディレクトリに作成する
	 * @param string $dir_name 作成するディレクトリ名
	 * @return string $dir_path 作成されたディレクトリのパス
	 */
	public static function make_lineconnect_dir($dir_name, $deny_from_all = true) {
		$root_dir_path = WP_CONTENT_DIR . '/uploads/lineconnect';
		// check if root dir exists
		if (! file_exists($root_dir_path)) {
			// make root dir
			if (mkdir($root_dir_path, 0777, true)) {
				// put .htaccess file to root dir
				$htaccess_file_path    = $root_dir_path . '/.htaccess';
				$htaccess_file_content = 'deny from all';
				file_put_contents($htaccess_file_path, $htaccess_file_content);
			}
		}
		$target_dir_path = $root_dir_path . '/' . $dir_name;
		// check if target dir exists
		if (! file_exists($target_dir_path)) {
			// make target dir
			if (mkdir($target_dir_path, 0777, true)) {
				$htaccess_file_path    = $target_dir_path . '/.htaccess';
				if ($deny_from_all) {
					$htaccess_file_content = 'deny from all';
				} else {
					$htaccess_file_content = 'allow from all';
				}
				file_put_contents($htaccess_file_path, $htaccess_file_content);
				return $target_dir_path;
			} else {
				return false;
			}
		}
		return $target_dir_path;
	}

	/**
	 * lineconnect用の指定されたフォルダにアップロードされたファイルのフルパスを取得する
	 * @param string $file_path ファイルパス
	 * @return string $file_path ファイルパス
	 */
	public static function get_lineconnect_file_path($file_path) {
		$root_dir_path = WP_CONTENT_DIR . '/uploads/lineconnect';
		$full_path = $root_dir_path . '/' . $file_path;
		if (file_exists($full_path)) {
			return $full_path;
		} else {
			return false;
		}
	}

	/**
	 * ファイルをbase64エンコードして返す関数
	 * @param string $file_path ファイルパス
	 * @return string $base64 ファイルのbase64エンコード
	 */
	public static function get_base64_encoded_file($file_path) {
		if (! file_exists($file_path) || ! is_readable($file_path)) {
			return '';
		}
		// ファイルの内容を取得
		$file_content = file_get_contents($file_path);

		// MIME TYPEを検出
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime_type = $finfo->buffer($file_content);
		// または別の方法
		// $mime_type = mime_content_type($file_path);

		// Base64エンコード
		$base64_file = base64_encode($file_content);

		return  'data:' . $mime_type . ';base64,' . $base64_file;
	}


	/**
	 * オブジェクトを再帰的に捜査してプレースホルダーを置換する関数
	 * @param array $obj 捜査対象のオブジェクト
	 * @param array $args 置換用のデータ
	 * @return array $object 置換後のオブジェクト
	 */
	public static function replacePlaceHolder($obj, $args) {
		if (is_object($obj)) {
			foreach ($obj as $key => $value) {
				$obj->{$key} = self::replacePlaceHolder($value, $args);
			}
		} elseif (is_array($obj)) {
			foreach ($obj as $key => $value) {
				$obj[$key] = self::replacePlaceHolder($value, $args);
			}
		} elseif (is_string($obj)) {
			if (is_array($args)) {
				foreach ($args as $key => $value) {
					if (is_string($value)) {
						$obj = str_replace('{{' . $key . '}}', $value, $obj);
					}
				}
			}
		}
		return $obj;
	}


	/**
	 * get parameter schema
	 */
	static function get_parameter_schema($title, $parameter) {
		$schema      = array();
		if (!empty($title)) {
			$schema['title'] = $title;
		}
		if (! empty($parameter['title'])) {
			$schema['title'] = $parameter['title'];
		}
		$actual_type = $parameter['type'];
		if (! empty($parameter['description'])) {
			$schema['description'] = $parameter['description'];
		}
		if (! empty($parameter['oneOf'])) {
			$schema['oneOf'] = $parameter['oneOf'];
		}
		if ($parameter['type'] == 'object') {
			if (! empty($parameter['properties'])) {
				$schema['properties'] = array();
				foreach ($parameter['properties'] as $key => $val) {
					$schema['properties'][$key] = self::get_parameter_schema($key, $val);
				}
			}
			if (! empty($parameter['additionalProperties'])) {
				$schema['additionalProperties'] = self::get_parameter_schema(null, $parameter['additionalProperties']);
			}
		} elseif ($parameter['type'] == 'array') {
			$schema['items'] = self::get_parameter_schema('parameter', $parameter['items']);
		} elseif ($parameter['type'] == 'slc_message') {
			$actual_type     = 'integer';
			$schema['oneOf'] = array();
			foreach (SLCMessage::get_lineconnect_message_name_array() as $post_id => $title) {
				$schema['oneOf'][] = array(
					'const' => $post_id,
					'title' => $title,
				);
			}
			// if count == 0, add empty
			if (count($schema['oneOf']) == 0) {
				$schema['oneOf'][] = array(
					'const' => 0,
					'title' => __('Please add message first', lineconnect::PLUGIN_NAME),
				);
			}
		} elseif ($parameter['type'] == 'slc_channel') {
			$actual_type     = 'string';
			$schema['oneOf'] = array();
			foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
				$schema['oneOf'][] = array(
					'const' => $channel['prefix'],
					'title' => $channel['name'],
				);
			}
			if (count($schema['oneOf']) == 0) {
				$schema['oneOf'][] = array(
					'const' => '',
					'title' => __('Please add channel first', lineconnect::PLUGIN_NAME),
				);
			}
		} elseif ($parameter['type'] == 'slc_richmenu') {
			$actual_type     = 'string';
			$schema['oneOf'] = array();
			foreach (RichMenu::get_richmenus() as $richmenu_id => $richmenu) {
				$schema['oneOf'][] = array(
					'const' => $richmenu_id,
					'title' => $richmenu,
				);
			}
			if (count($schema['oneOf']) == 0) {
				$schema['oneOf'][] = array(
					'const' => '',
					'title' => __('Please add richmenu first', lineconnect::PLUGIN_NAME),
				);
			}
		} elseif ($parameter['type'] == 'slc_richmenualias') {
			$actual_type     = 'string';
			$schema['oneOf'] = array();
			foreach (RichMenu::get_richmenu_aliases() as $alias_id => $richmenu_id) {
				$schema['oneOf'][] = array(
					'const' => $alias_id,
				);
			}
			if (count($schema['oneOf']) == 0) {
				$schema['oneOf'][] = array(
					'const' => '',
					'title' => __('Please add richmenu alias first', lineconnect::PLUGIN_NAME),
				);
			}
		} elseif ($parameter['type'] == 'slc_audience') {
			$actual_type     = 'integer';
			$schema['oneOf'] = array();
			foreach (Audience::get_lineconnect_audience_name_array() as $audience_id => $audience) {
				$schema['oneOf'][] = array(
					'const' => $audience_id,
					'title' => $audience,
				);
			}
			if (count($schema['oneOf']) == 0) {
				$schema['oneOf'][] = array(
					'const' => '',
					'title' => __('Please add audience first', lineconnect::PLUGIN_NAME),
				);
			}
		} elseif ($parameter['type'] == 'slc_scenario') {
			$actual_type     = 'integer';
			$schema['oneOf'] = array();
			foreach (Scenario::get_scenario_name_array() as $scenario_id => $scenario) {
				$schema['oneOf'][] = array(
					'const' => $scenario_id,
					'title' => $scenario,
				);
			}
			if (count($schema['oneOf']) == 0) {
				$schema['oneOf'][] = array(
					'const' => '',
					'title' => __('Please add scenario first', lineconnect::PLUGIN_NAME),
				);
			}
		}

		if (! empty($parameter['enum'])) {
			$schema['enum'] = $parameter['enum'];
		}

		$schema['type'] = $actual_type;
		return $schema;
	}

	/**
	 * Ajaxの認証を行う関数
	 * @param string $action アクション名
	 * @param string $nonce_name nonceの名前
	 * @param string $nonce nonceの値
	 * @return bool $result 認証結果
	 */
	public static function check_ajax_referer($action, $nonce_name = 'nonce') {
		if (! is_user_logged_in()) {
			return array(
				'result' => 'failed',
				'success' => array(),
				'error' => array(__('You are not logged in.', lineconnect::PLUGIN_NAME),),
			);
		}
		// 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
		if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
			return array(
				'result' => 'failed',
				'success' => array(),
				'error' => array(__('You do not have permission to send messages.', lineconnect::PLUGIN_NAME),),
			);
		}
		// nonceで設定したcredentialをPOST受信していない場合は無視
		if (! isset($_POST[$nonce_name]) || ! $_POST[$nonce_name] || ! check_ajax_referer($action, $nonce_name)) {
			return array(
				'result' => 'failed',
				'success' => array(),
				'error' => array(__('Nonce is not set or invalid.', lineconnect::PLUGIN_NAME),),
			);
		}


		return array(
			'result' => 'success',
			'success' => array(__('Nonce verified successfully.', lineconnect::PLUGIN_NAME),),
			'error' => array(),
		);
	}
}
