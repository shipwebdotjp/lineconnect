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
}
