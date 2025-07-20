<?php

namespace Shipweb\LineConnect\Utilities;

use Shipweb\LineConnect\Core\LineConnect;

class LineId {
    public static function line_id_row($line_id, $secret_prefix) {
        global $wpdb;
        $table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
        $line_id_row        = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name_line_id} WHERE channel_prefix = %s and line_id = %s",
                array(
                    $secret_prefix,
                    $line_id,
                )
            ),
            'ARRAY_A'
        );
        return $line_id_row;
    }
}
