<?php

namespace Shipweb\LineConnect\Utilities;

class ArrayUtil {
    private static $indent = '    ';
    private static $level = 0;

    public static function print($array) {
        return "array(\n" . self::arrayToCode($array) . ")";
    }

    private static function arrayToCode($array) {
        if (!is_array($array)) {
            return '';
        }
        self::$level++;
        $output = '';

        foreach ($array as $key => $value) {
            $output .= str_repeat(self::$indent, self::$level);

            // Format key
            if (is_string($key)) {
                $output .= "'" . addslashes($key) . "' => ";
            } else {
                $output .= $key . " => ";
            }

            // Format value
            if (is_array($value)) {
                $output .= "array(\n" . self::arrayToCode($value) .
                    str_repeat(self::$indent, self::$level) . "),\n";
            } elseif (is_null($value)) {
                $output .= "null,\n";
            } elseif (is_string($value)) {
                $output .= "'" . addslashes($value) . "',\n";
            } elseif (is_bool($value)) {
                $output .= ($value ? 'true' : 'false') . ",\n";
            } else {
                $output .= $value . ",\n";
            }
        }

        self::$level--;
        return $output;
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
     * Flatten WordPress post meta array:
     * - ['key' => ['value']] => ['key' => 'value']
     * - 数値キーの配列は先頭要素のみ採用
     * - 連想配列は再帰的に処理
     *
     * @param array $meta
     * @return array
     */
    public static function flatten_post_meta(array $meta): array {
        $result = [];

        foreach ($meta as $key => $value) {
            if (is_array($value)) {
                // 連想配列か数値配列かを判定
                $is_assoc = array_keys($value) !== range(0, count($value) - 1);

                if ($is_assoc) {
                    // 連想配列なら再帰的にフラット化
                    $result[$key] = self::flatten_post_meta($value);
                } else {
                    // 数値配列なら先頭要素だけ採用（存在すれば）
                    $result[$key] = array_key_exists(0, $value) ? $value[0] : null;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
