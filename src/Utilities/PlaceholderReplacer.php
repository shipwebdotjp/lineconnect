<?php

namespace Shipweb\LineConnect\Utilities;

class PlaceholderReplacer {
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

    private static function replace_injection_data($injection_path, $injection_data) {
        // if php 8.1 or later then use twig
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            return self::twig_replace_injection_data($injection_path, $injection_data);
        } else {
            // if php 8.0 or earlier then use legacy_replace_injection_data
            return self::legacy_replace_injection_data($injection_path, $injection_data);
        }
    }

    private static function twig_replace_injection_data($injection_path, $injection_data) {
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
    private static function legacy_replace_injection_data($injection_path, $injection_data) {
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
}
