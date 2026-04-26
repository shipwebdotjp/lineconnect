<?php

namespace Shipweb\LineConnect\Utilities;

class Logging {

    public static function logging_with_redact(array $payload, array $omit_keys = []): void {
        $walker = function (&$value, $key) use ($omit_keys) {
            if (($key === 'image_url') && is_string($value) && strpos($value, 'data:image/') === 0) {
                $value = preg_replace('#^data:image/[^;]+;base64,.*$#', 'data:image/***;base64,[redacted]', $value);
            }
            if (in_array($key, $omit_keys, true) && is_string($value)) {
                $value = '(redacted)';
            }
        };

        array_walk_recursive($payload, $walker);

        error_log(print_r($payload, true));
        //return $payload;
    }
}