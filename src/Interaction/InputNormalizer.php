<?php

namespace Shipweb\LineConnect\Interaction;

/**
 * Normalizes user input based on a set of rules.
 */
class InputNormalizer {
    /**
     * Normalizes the given input according to the provided rules.
     *
     * $rules historically was an object describing flags (old format).
     * The new schema uses an array of string tokens (e.g. ['trim','omit_hyphen']).
     *
     * This method accepts both formats for backward compatibility:
     *  - object (old): properties like ->trim, ->omit, ->HanKatatoZenKata etc.
     *  - array (new): list of tokens applied in order.
     *
     * @param mixed $input The raw user input.
     * @param mixed $rules Either an object (legacy) or an array of rule tokens.
     *                      Example (legacy): (object)['trim' => true, 'omit' => '-']
     *                      Example (new): ['trim','omit_hyphen','HanKatatoZenKata']
     * @return mixed The normalized input.
     */
    public function normalize(mixed $input, mixed $rules): mixed {
        // Most normalization rules apply to strings.
        if (!is_string($input)) {
            return $input;
        }

        $normalized_input = $input;
        /*
        // If rules is an object (legacy format), keep legacy behavior.
        if (is_object($rules)) {
            // Rule: omit (remove specified characters)
            if (!empty($rules->omit) && is_string($rules->omit)) {
                $chars_to_omit = preg_split('//u', $rules->omit, -1, PREG_SPLIT_NO_EMPTY);
                $normalized_input = str_replace($chars_to_omit, '', $normalized_input);
            }

            // Rule: trim (remove whitespace from the beginning and end)
            if (!empty($rules->trim)) {
                $normalized_input = trim($normalized_input);
            }

            // Conversion operations (legacy boolean flags)
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
                // half-width katakana -> full-width katakana, then to hiragana
                $normalized_input = mb_convert_kana($normalized_input, 'K', 'UTF-8');
                $normalized_input = mb_convert_kana($normalized_input, 'c', 'UTF-8');
            }

            return $normalized_input;
        }
*/
        // New format: $rules is expected to be an array of strings.
        if (!is_array($rules)) {
            // If rules is null/empty or unexpected, return input unchanged.
            return $normalized_input;
        }

        foreach ($rules as $rule) {
            if (!is_string($rule)) {
                continue;
            }

            switch ($rule) {
                case 'trim':
                    $normalized_input = trim($normalized_input, " \t\n\r\0\x0B　");
                    break;

                case 'omit_comma':
                    // Remove ASCII comma and common fullwidth/japanese commas
                    $normalized_input = str_replace(array(',', '，', '、'), '', $normalized_input);
                    break;

                case 'omit_hyphen':
                    // Remove several hyphen/dash variants (ASCII + common unicode variants)
                    $normalized_input = str_replace(
                        array('-', 'ー', '－', '—', '‑', '−'),
                        '',
                        $normalized_input
                    );
                    break;

                case 'omit_inner_halfwidth_space':
                    // Remove only half-width spaces that are inside the string (between non-space characters)
                    // Preserve leading/trailing spaces unless 'trim' rule is applied.
                    $normalized_input = preg_replace('/(?<=\S) (?=\S)/u', '', $normalized_input);
                    if ($normalized_input === null) {
                        // preg_replace can return null on error; in that unlikely case, leave input unchanged.
                        $normalized_input = $input;
                    }
                    break;

                case 'omit_inner_fullwidth_space':
                    // Fullwidth ideographic space U+3000
                    $normalized_input = preg_replace('/(?<=\S)　(?=\S)/u', '', $normalized_input);
                    if ($normalized_input === null) {
                        $normalized_input = $input;
                    }
                    break;

                case 'HanKatatoZenKata':
                    // Half-width katakana -> full-width katakana
                    $normalized_input = mb_convert_kana($normalized_input, 'K', 'UTF-8');
                    break;

                case 'HanKatatoZenKana':
                    // Half-width katakana -> full-width katakana, then to hiragana
                    $normalized_input = mb_convert_kana($normalized_input, 'K', 'UTF-8');
                    $normalized_input = mb_convert_kana($normalized_input, 'c', 'UTF-8');
                    break;

                case 'ZenKatatoZenKana':
                    // Full-width katakana -> hiragana
                    $normalized_input = mb_convert_kana($normalized_input, 'c', 'UTF-8');
                    break;

                case 'ZenKanatoZenKata':
                    // Hiragana -> Katakana
                    $normalized_input = mb_convert_kana($normalized_input, 'C', 'UTF-8');
                    break;

                case 'HanEisutoZenEisu':
                    // Half-width alphanumeric -> Full-width
                    $normalized_input = mb_convert_kana($normalized_input, 'A', 'UTF-8');
                    break;

                case 'ZenEisutoHanEisu':
                    // Full-width alphanumeric -> Half-width
                    $normalized_input = mb_convert_kana($normalized_input, 'a', 'UTF-8');
                    break;

                default:
                    // Unknown rule token: ignore
                    break;
            }
        }

        return $normalized_input;
    }
}
