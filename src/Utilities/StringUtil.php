<?php

namespace Shipweb\LineConnect\Utilities;

class StringUtil {
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
     * クエリストリング形式の文字列から指定されたキーの値を取得する
     * 
     * @param string $queryString クエリストリング形式の文字列
     * @param string $key 取得したいキー名
     * @return string|null キーが存在する場合はその値、存在しない場合やクエリストリングでない場合はnull
     */
    public static function getQueryValue($queryString, $key) {
        // 空文字列や null の場合
        if (empty($queryString)) {
            return null;
        }

        // クエリストリング形式かチェック（=を含むかどうか）
        if (strpos($queryString, '=') === false) {
            return null;
        }

        // parse_str を使ってクエリストリングを解析
        parse_str($queryString, $params);

        // キーが存在するかチェック
        if (isset($params[$key])) {
            return $params[$key];
        }

        return null;
    }
}
