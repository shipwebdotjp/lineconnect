<?php

namespace Shipweb\LineConnect\Utilities;

class SimpleFunction {
    public static function is_empty($var = null) {
        if (empty($var) && 0 !== $var && '0' !== $var) { // 論理型のfalseを取り扱う場合は、更に「&& false !== $var」を追加する
            return true;
        } else {
            return false;
        }
    }

    /**
     * 数字のみで構成された配列かどうかを判定する
     * @param array $array
     * @return bool
     */
    public static function array_is_list_compat(array $array): bool {
        return $array === [] || array_keys($array) === range(0, count($array) - 1);
    }
}
