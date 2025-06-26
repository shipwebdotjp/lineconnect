<?php

namespace Shipweb\LineConnect\Utilities;

class PrepareArguments {
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
}
