<?php

namespace Shipweb\LineConnect\Utilities;

use lineconnect;

class LineId {
    public static function line_id_row($line_id, $secret_prefix) {
        global $wpdb;
        $table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
        $line_id_row        = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name_line_id} WHERE line_id = %s and channel_prefix = %s",
                array(
                    $line_id,
                    $secret_prefix,
                )
            ),
            'ARRAY_A'
        );
        return $line_id_row;
    }
}
