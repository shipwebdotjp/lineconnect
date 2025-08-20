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

        // Conversion operations
        if (!empty($rules->HanKatatoZenKata)) {
            $normalized_input = mb_convert_kana($normalized_input, 'K', 'UTF-8');
        }
        if (!empty($rules->ZenKatatoZenKana)) {
            $normalized_input = mb_convert_kana($normalized_input, 'c', 'UTF-8');
        }
        if (!empty($rules->ZenKanatoZenKata)) {
            $normalized_input = mb_convert_kana($normalized_input, 'C', 'UTF-8');
        }
        if (!empty($rules->HanEisutoZenEisu)) {
            $normalized_input = mb_convert_kana($normalized_input, 'A', 'UTF-8');
        }
        if (!empty($rules->ZenEisutoHanEisu)) {
            $normalized_input = mb_convert_kana($normalized_input, 'a', 'UTF-8');
        }
        if (!empty($rules->HanKatatoZenKana)) {
            $normalized_input = mb_convert_kana($normalized_input, 'H', 'UTF-8');
        }

        return $normalized_input;
    }
}
