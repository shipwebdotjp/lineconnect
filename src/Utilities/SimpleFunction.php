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
}
