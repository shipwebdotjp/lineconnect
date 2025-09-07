<?php

namespace Shipweb\LineConnect\Utilities;

class DateUtil {
    public static function format_utc_in_wp_tz(?string $utcIsoOrDateTime, string $format = 'Y-m-d H:i:s'): string {
        if (empty($utcIsoOrDateTime)) {
            return '';
        }
        $dtUtc = new \DateTimeImmutable($utcIsoOrDateTime, new \DateTimeZone('UTC'));
        return $dtUtc->setTimezone( wp_timezone() )->format($format);
    }
    public static function format_wp_tz_in_utc(?string $wpIsoOrDateTime, string $format = 'Y-m-d H:i:s'): string {
        if (empty($wpIsoOrDateTime)) {
            return '';
        }
        $dtWp = new \DateTimeImmutable($wpIsoOrDateTime, wp_timezone());
        return $dtWp->setTimezone( new \DateTimeZone('UTC') )->format($format);
    }
}