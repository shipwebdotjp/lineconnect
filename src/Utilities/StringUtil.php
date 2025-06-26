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
}
