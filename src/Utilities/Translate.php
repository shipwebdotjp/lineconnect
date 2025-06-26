<?php

namespace Shipweb\LineConnect\Utilities;

use Shipweb\LineConnect\Core\LineConnect;

class Translate {
    /**
     * 動的に翻訳を行う関数
     * @param $text 翻訳対象のテキスト
     * @return string $translated 翻訳後のテキスト
     */
    public static function dynamic_translate($text) {
        if (preg_match('/The request body has (\d+) error/', $text, $matches)) {
            $translated = sprintf(_n('The request body has %s error.', 'The request body has %s errors.', $matches[1], lineconnect::PLUGIN_NAME), number_format($matches[1]));
        } elseif (strpos('Invalid reply token', $text) !== false) {
            $translated = __('Invalid reply token.', lineconnect::PLUGIN_NAME);
        } elseif (preg_match('/The property, (.*?), in the request body is invalid /', $text, $matches)) {
            $translated = sprintf(__('The property, %s, in the request body is invalid.', lineconnect::PLUGIN_NAME), $matches[1]);
        } elseif (strpos('The request body could not be parsed as JSON', $text) !== false) {
            $translated = __('The request body could not be parsed as JSON.', lineconnect::PLUGIN_NAME);
        } elseif (preg_match('/The content type, (.*?), is not supported/', $text, $matches)) {
            $translated = sprintf(__('The content type, %s, is not supported.', lineconnect::PLUGIN_NAME), $matches[1]);
        } elseif (strpos('Authentication failed due to the following reason:', $text) !== false) {
            $translated = __('Authentication failed.', lineconnect::PLUGIN_NAME);
        } elseif (strpos('Access to this API is not available for your account', $text) !== false) {
            $translated = __('Access to this API is not available for your account.', lineconnect::PLUGIN_NAME);
        } elseif (strpos('Failed to send messages', $text) !== false) {
            $translated = __('Failed to send messages.', lineconnect::PLUGIN_NAME);
        } elseif (strpos('You have reached your monthly limit.', $text) !== false) {
            $translated = __('You have reached your monthly limit.', lineconnect::PLUGIN_NAME);
        } elseif (strpos('Not found', $text) !== false) {
            $translated = __('Not found.', lineconnect::PLUGIN_NAME);
        } elseif (strpos('May not be empty', $text) !== false) {
            $translated = __('May not be empty.', lineconnect::PLUGIN_NAME);
        } elseif (preg_match('/Size must be between ([0-9,]+) and ([0-9,]+)/', $text, $matches)) {
            $translated = sprintf(__('Size must be between %s and %s.', lineconnect::PLUGIN_NAME), $matches[1], $matches[2]);
        } else {
            $translated = $text;
        }
        return $translated;
    }
}
