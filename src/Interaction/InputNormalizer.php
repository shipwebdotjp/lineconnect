<?php

namespace Shipweb\LineConnect\Interaction;

/**
 * Normalizes user input based on a set of rules.
 */
class InputNormalizer {
    /**
     * Normalizes the given input according to the provided rules.
     *
     * @param mixed $input The raw user input.
     * @param object $rules An object containing normalization rules for the current step.
     *                      Example: (object)[
     *                          'trim' => true,
     *                          'omit' => '-,#',
     *                          'HanKatatoZenKata' => true // Options for mb_convert_kana
     *                      ]
     * @return mixed The normalized input.
     */
    public function normalize(mixed $input, object $rules): mixed {
        // Most normalization rules apply to strings.
        if (!is_string($input)) {
            return $input;
        }

        $normalized_input = $input;

        // Rule: omit (remove specified characters)
        if (!empty($rules->omit) && is_string($rules->omit)) {
            $chars_to_omit = preg_split('//u', $rules->omit, -1, PREG_SPLIT_NO_EMPTY);
            $normalized_input = str_replace($chars_to_omit, '', $normalized_input);
        }

        // Rule: trim (remove whitespace from the beginning and end)
        if (!empty($rules->trim)) {
            $normalized_input = trim($normalized_input);
        }

        // Collect flags for mb_convert_kana
        $convert_flg = '';

        if (!empty($rules->HanKatatoZenKata)) {
            $convert_flg .= 'K'; // 半角カタカナ → 全角カタカナ
        }
        if (!empty($rules->ZenKatatoZenKana)) {
            $convert_flg .= 'R'; // 全角カタカナ → 全角ひらがな
        }
        if (!empty($rules->ZenKanatoZenKata)) {
            $convert_flg .= 'r'; // 全角ひらがな → 全角カタカナ
        }
        if (!empty($rules->HanEisutoZenEisu)) {
            $convert_flg .= 'A'; // 半角英数字 → 全角
        }
        if (!empty($rules->ZenEisutoHanEisu)) {
            $convert_flg .= 'a'; // 全角英数字 → 半角
        }

        // Apply mb_convert_kana if flags exist
        if ($convert_flg !== '') {
            $normalized_input = mb_convert_kana($normalized_input, $convert_flg, 'UTF-8');
        }

        // Special case: 半角カタカナ → 全角ひらがな
        if (!empty($rules->HanKatatoZenKana)) {
            // Step1: 半角カタカナ → 全角カタカナ
            $tmp = mb_convert_kana($normalized_input, 'K', 'UTF-8');
            // Step2: 全角カタカナ → 全角ひらがな
            $normalized_input = mb_convert_kana($tmp, 'R', 'UTF-8');
        }

        return $normalized_input;
    }
}
