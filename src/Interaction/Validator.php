<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Core\LineConnect;

/**
 * Validates input based on a set of rules defined in the interaction step.
 */
class Validator {
    /**
     * Validates the given input against an array of rules.
     *
     * @param mixed $input The normalized input.
     * @param array $rules The array of validation rule objects from the step definition.
     * @return ValidationResult The result of the validation.
     */
    public function validate(mixed $input, array $rules): ValidationResult {
        $errors = [];
        foreach ($rules as $rule) {
            $result = $this->dispatch_rule($input, $rule);
            if (!$result->isValid()) {
                // collect errors from the result
                $errors = array_merge($errors, $result->getErrors());
            }
        }

        if (!empty($errors)) {
            return ValidationResult::fromErrors($errors);
        }

        return ValidationResult::success();
    }

    private function dispatch_rule(mixed $input, mixed $rule): ValidationResult {
        $rule_type = $rule['type'];
        $rule_params = $rule[$rule_type];

        switch ($rule_type) {
            case 'required':
                return $this->validate_required($input, $rule_params);
            case 'email':
                return $this->validate_email($input, $rule_params);
            case 'number':
                return $this->validate_number($input, $rule_params);
            case 'length':
                return $this->validate_length($input, $rule_params);
            case 'regex':
                return $this->validate_regex($input, $rule_params);
            case 'phone':
                return $this->validate_phone($input, $rule_params);
            case 'date':
                return $this->validate_date($input, $rule_params);
            case 'time':
                return $this->validate_time($input, $rule_params);
            case 'datetime':
                return $this->validate_datetime($input, $rule_params);
            case 'url':
                return $this->validate_url($input, $rule_params);
            case 'enum':
                return $this->validate_enum($input, $rule_params);
            case 'forbidden':
                return $this->validate_forbidden($input, $rule_params);
            case 'japanese':
                return $this->validate_japanese($input, $rule_params);
            default:
                return ValidationResult::success(); // For unimplemented rules, assume success.
        }
    }

    private function validate_required(mixed $input, bool $is_required): ValidationResult {
        if (!$is_required) {
            return ValidationResult::success();
        }
        if (is_string($input) && trim($input) === '') {
            return ValidationResult::failure(__('This field is required.', LineConnect::PLUGIN_NAME));
        }
        if (is_null($input)) {
            return ValidationResult::failure(__('This field is required.', LineConnect::PLUGIN_NAME));
        }
        return ValidationResult::success();
    }

    private function validate_email(mixed $input, bool $is_email): ValidationResult {
        if (!$is_email || empty($input)) {
            return ValidationResult::success();
        }
        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return ValidationResult::failure(__('Invalid email format.', LineConnect::PLUGIN_NAME));
        }
        return ValidationResult::success();
    }

    private function validate_number(mixed $input, array $params): ValidationResult {
        // Treat empty string and null as "no input" (do not validate) — but accept "0"
        if ($input === '' || $input === null) {
            return ValidationResult::success();
        }
        if (!is_numeric($input)) {
            return ValidationResult::failure(__('Must be a number.', LineConnect::PLUGIN_NAME));
        }
        if (isset($params['min']) && $input < $params['min']) {
            return ValidationResult::failure(sprintf(__('Must be at least %s.', LineConnect::PLUGIN_NAME), $params['min']));
        }
        if (isset($params['max']) && $input > $params['max']) {
            return ValidationResult::failure(sprintf(__('Must be at most %s.', LineConnect::PLUGIN_NAME), $params['max']));
        }
        return ValidationResult::success();
    }

    private function validate_length(mixed $input, array $params): ValidationResult {
        if (empty($input) || !is_string($input)) {
            return ValidationResult::success();
        }
        $len = mb_strlen($input);
        if (isset($params['minlength']) && $len < $params['minlength']) {
            return ValidationResult::failure(sprintf(__('Length must be at least %s characters.', LineConnect::PLUGIN_NAME), $params['minlength']));
        }
        if (isset($params['maxlength']) && $len > $params['maxlength']) {
            return ValidationResult::failure(sprintf(__('Length must be at most %s characters.', LineConnect::PLUGIN_NAME), $params['maxlength']));
        }
        return ValidationResult::success();
    }

    private function validate_regex(mixed $input, ?string $pattern): ValidationResult {
        if (empty($pattern) || empty($input) || !is_string($input)) {
            return ValidationResult::success();
        }
        if (!preg_match($pattern, $input)) {
            return ValidationResult::failure(__('Invalid format.', LineConnect::PLUGIN_NAME));
        }
        return ValidationResult::success();
    }

    private function validate_phone(mixed $input, bool $is_phone): ValidationResult {
        if (!$is_phone || empty($input) || !is_string($input)) {
            return ValidationResult::success();
        }

        // 数字だけに正規化（ハイフン・スペースを削除）
        $normalized = preg_replace('/[\s-]+/', '', $input);

        // 日本の電話番号のパターン
        // - 固定電話: 0から始まる10桁
        // - 携帯電話/IP電話: 070/080/090/050などで始まる11桁
        // - フリーダイヤル: 0120で始まる10桁
        if (!preg_match('/^(0\d{9}|0\d{10})$/', $normalized)) {
            return ValidationResult::failure(__('Invalid Japanese phone number format.', LineConnect::PLUGIN_NAME));
        }

        return ValidationResult::success();
    }

    private function validate_date(mixed $input, bool $is_date): ValidationResult {
        if (!$is_date || $input === '' || $input === null || !is_string($input)) {
            return ValidationResult::success();
        }

        // 日付の正規表現（YYYY-MM-DD形式） or YYYY/MM/DD形式 (capture components)
        if (!preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})$/', $input, $m)) {
            return ValidationResult::failure(__('Invalid date format. Please use YYYY-MM-DD or YYYY/MM/DD.', LineConnect::PLUGIN_NAME));
        }

        $year = (int) $m[1];
        $month = (int) $m[2];
        $day = (int) $m[3];

        // checkdate ensures calendar validity (e.g. no Feb 30)
        if (!checkdate($month, $day, $year)) {
            return ValidationResult::failure(__('Invalid date.', LineConnect::PLUGIN_NAME));
        }

        // Finally, ensure strtotime can parse it (defensive)
        $timestamp = strtotime($input);
        if ($timestamp === false) {
            return ValidationResult::failure(__('Invalid date.', LineConnect::PLUGIN_NAME));
        }

        return ValidationResult::success();
    }

    private function validate_time(mixed $input, bool $is_time): ValidationResult {
        if (!$is_time || empty($input) || !is_string($input)) {
            return ValidationResult::success();
        }

        // HH:MM or HH:MM:SS (24-hour)
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $input)) {
            return ValidationResult::failure(__('Invalid time format. Use HH:MM or HH:MM:SS.', LineConnect::PLUGIN_NAME));
        }

        return ValidationResult::success();
    }

    private function validate_datetime(mixed $input, bool $is_datetime): ValidationResult {
        if (!$is_datetime || empty($input) || !is_string($input)) {
            return ValidationResult::success();
        }

        // Accept formats:
        // YYYY-MM-DD HH:MM(:SS)?
        // YYYY/MM/DD HH:MM(:SS)?
        // YYYY-MM-DDTHH:MM(:SS)?
        if (!preg_match('/^\d{4}[-\/]\d{2}[-\/]\d{2}[ T]\d{2}:\d{2}(?::\d{2})?$/', $input)) {
            return ValidationResult::failure(__('Invalid datetime format. Use YYYY-MM-DD HH:MM or YYYY-MM-DDTHH:MM.', LineConnect::PLUGIN_NAME));
        }

        // Validate with strtotime and checkdate for date part
        $parts = preg_split('/[ T]/', $input, 2);
        if (!$parts || count($parts) < 2) {
            return ValidationResult::failure(__('Invalid datetime format.', LineConnect::PLUGIN_NAME));
        }
        $date_part = $parts[0];

        // Extract Y, M, D
        if (!preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})$/', $date_part, $m)) {
            return ValidationResult::failure(__('Invalid datetime format.', LineConnect::PLUGIN_NAME));
        }
        $year = (int)$m[1];
        $month = (int)$m[2];
        $day = (int)$m[3];

        if (!checkdate($month, $day, $year)) {
            return ValidationResult::failure(__('Invalid datetime (date part is not valid).', LineConnect::PLUGIN_NAME));
        }

        $timestamp = strtotime($input);
        if ($timestamp === false) {
            return ValidationResult::failure(__('Invalid datetime.', LineConnect::PLUGIN_NAME));
        }

        return ValidationResult::success();
    }

    private function validate_url(mixed $input, bool $is_url): ValidationResult {
        if (!$is_url || empty($input) || !is_string($input)) {
            return ValidationResult::success();
        }

        if (!filter_var($input, FILTER_VALIDATE_URL)) {
            return ValidationResult::failure(__('Invalid URL.', LineConnect::PLUGIN_NAME));
        }

        $parts = parse_url($input);
        if (empty($parts['scheme']) || !in_array(strtolower($parts['scheme']), array('http', 'https'), true)) {
            return ValidationResult::failure(__('Invalid URL. Only http and https are allowed.', LineConnect::PLUGIN_NAME));
        }

        return ValidationResult::success();
    }

    private function validate_enum(mixed $input, mixed $rule_params): ValidationResult {
        if (empty($input)) {
            return ValidationResult::success();
        }

        // Normalize allowed values into a simple array
        $allowed = array();
        if (is_array($rule_params)) {
            $allowed = $rule_params;
        } elseif (is_object($rule_params)) {
            // stdClass from json decode with numeric keys becomes an object; cast to array
            foreach ($rule_params as $v) {
                $allowed[] = $v;
            }
        } else {
            // If scalar provided, treat as single allowed value
            $allowed[] = $rule_params;
        }

        // Compare as strings for robustness
        $found = false;
        foreach ($allowed as $val) {
            if ((string)$input === (string)$val) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return ValidationResult::failure(sprintf(__('Invalid value. Must be one of: %s.', LineConnect::PLUGIN_NAME), implode(', ', $allowed)));
        }

        return ValidationResult::success();
    }

    private function validate_forbidden(mixed $input, mixed $rule_params): ValidationResult {
        if (empty($input) || !is_string($input)) {
            return ValidationResult::success();
        }

        if (empty($rule_params)) {
            return ValidationResult::success();
        }

        // Normalize params object/array
        $words = array();
        $patterns = array();
        if (is_object($rule_params) || is_array($rule_params)) {
            // object may contain 'words' and 'patterns'
            if (isset($rule_params->words)) {
                $words = is_array($rule_params->words) ? $rule_params->words : (array)$rule_params->words;
            } elseif (is_array($rule_params) && isset($rule_params['words'])) {
                $words = $rule_params['words'];
            }
            if (isset($rule_params->patterns)) {
                $patterns = is_array($rule_params->patterns) ? $rule_params->patterns : (array)$rule_params->patterns;
            } elseif (is_array($rule_params) && isset($rule_params['patterns'])) {
                $patterns = $rule_params['patterns'];
            }
        }

        // Check forbidden words (case-insensitive, partial match)
        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }
            if (mb_stripos($input, $word) !== false) {
                return ValidationResult::failure(sprintf(__('Contains forbidden content: %s', LineConnect::PLUGIN_NAME), $word));
            }
        }

        // Check forbidden patterns (regex)
        foreach ($patterns as $pat) {
            if ($pat === '') {
                continue;
            }
            $used = $pat;
            // If the pattern is not a valid regex (no delimiters), wrap it
            if (@preg_match($used, '') === false) {
                $escaped = str_replace('/', '\/', $pat);
                $used = '/' . $escaped . '/u';
            }
            if (@preg_match($used, $input)) {
                return ValidationResult::failure(sprintf(__('Contains forbidden content (pattern matched): %s', LineConnect::PLUGIN_NAME), $pat));
            }
        }

        return ValidationResult::success();
    }

    private function validate_japanese(mixed $input, ?string $mode): ValidationResult {
        if (empty($input) || !is_string($input)) {
            return ValidationResult::success();
        }

        $value = trim($input);
        if ($value === '') {
            return ValidationResult::success();
        }

        // Hiragana validation
        if ($mode === 'hiragana') {
            if (!preg_match('/^\p{Hiragana}+$/u', $value)) {
                return ValidationResult::failure(__('Only hiragana characters are allowed.', LineConnect::PLUGIN_NAME));
            }
            return ValidationResult::success();
        }

        // Katakana validation (allow long vowel 'ー' (U+30FC) and middle dot '・' (U+30FB))
        if ($mode === 'katakana') {
            if (!preg_match('/^[\p{Katakana}\x{30FC}\x{30FB}]+$/u', $value)) {
                return ValidationResult::failure(__('Only katakana characters are allowed.', LineConnect::PLUGIN_NAME));
            }
            return ValidationResult::success();
        }

        return ValidationResult::failure(__('Invalid japanese validation mode.', LineConnect::PLUGIN_NAME));
    }
}
