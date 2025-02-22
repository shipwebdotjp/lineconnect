<?php

namespace Shipweb\LineConnect\Utilities;

class ArrayPrinter {
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
}
