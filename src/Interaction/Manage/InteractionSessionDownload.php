<?php

namespace Shipweb\LineConnect\Interaction\Manage;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Interaction\InteractionDefinition;
use Shipweb\LineConnect\Interaction\InteractionSession;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;
use Shipweb\LineConnect\Utilities\DateUtil;

class InteractionSessionDownload {
    /**
     * Build CSV contents and filename for given interaction and options.
     *
     * @param int $interaction_id
     * @param array $options {
     *   Optional. Filter options:
     *   - status: array|string
     *   - version: array|int
     *   - channel: array|string
     *   - line_user_id: string
     *   - updated_at_start: string (ISO 8601)
     *   - updated_at_end: string (ISO 8601)
     * }
     * @return array ['filename' => string, 'csv' => string]
     */
    public static function build_sessions_csv(int $interaction_id, array $options = []) : array {
        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;
        $table_name_line_id = $wpdb->prefix . LineConnect::TABLE_LINE_ID;

        // normalize options
        $status = isset($options['status']) ? (is_array($options['status']) ? $options['status'] : array($options['status'])) : null;
        $version = isset($options['version']) ? (is_array($options['version']) ? $options['version'] : array($options['version'])) : null;
        $channel = isset($options['channel']) ? (is_array($options['channel']) ? $options['channel'] : array($options['channel'])) : null;
        $lineUserId = isset($options['line_user_id']) ? sanitize_text_field($options['line_user_id']) : null;
        $updatedAtStart = isset($options['updated_at_start']) ? sanitize_text_field($options['updated_at_start']) : null;
        $updatedAtEnd = isset($options['updated_at_end']) ? sanitize_text_field($options['updated_at_end']) : null;

        // sanitize array values
        if (is_array($status)) {
            $status = array_map('sanitize_text_field', $status);
        }
        if (is_array($version)) {
            $version = array_map('absint', $version);
        }
        if (is_array($channel)) {
            $channel = array_map('sanitize_text_field', $channel);
        }

        // WHERE句構築
        $where_clauses = array();
        $where_values = array();
        $where_clauses[] = 'sessions.interaction_id = %d';
        $where_values[] = $interaction_id;

        if (!empty($status)) {
            if (count($status) > 1) {
                $placeholders = implode(', ', array_fill(0, count($status), '%s'));
                $where_clauses[] = "sessions.status IN ($placeholders)";
                $where_values = array_merge($where_values, $status);
            } else {
                $where_clauses[] = 'sessions.status = %s';
                $where_values[] = $status[0];
            }
        }

        if (!empty($version)) {
            if (count($version) > 1) {
                $placeholders = implode(', ', array_fill(0, count($version), '%d'));
                $where_clauses[] = "sessions.interaction_version IN ($placeholders)";
                $where_values = array_merge($where_values, $version);
            } else {
                $where_clauses[] = 'sessions.interaction_version = %d';
                $where_values[] = intval($version[0]);
            }
        }

        if (!empty($channel)) {
            if (count($channel) > 1) {
                $placeholders = implode(', ', array_fill(0, count($channel), '%s'));
                $where_clauses[] = "sessions.channel_prefix IN ($placeholders)";
                $where_values = array_merge($where_values, $channel);
            } else {
                $where_clauses[] = 'sessions.channel_prefix = %s';
                $where_values[] = $channel[0];
            }
        }

        if (!empty($lineUserId)) {
            $where_clauses[] = 'sessions.line_user_id = %s';
            $where_values[] = $lineUserId;
        }

        if (!empty($updatedAtStart)) {
            try {
                $startDateTime = new \DateTime($updatedAtStart, new \DateTimeZone('UTC'));
                $where_clauses[] = 'sessions.updated_at >= %s';
                $where_values[] = $startDateTime->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // invalid date, ignore filter
            }
        }

        if (!empty($updatedAtEnd)) {
            try {
                $endDateTime = new \DateTime($updatedAtEnd, new \DateTimeZone('UTC'));
                $where_clauses[] = 'sessions.updated_at < %s';
                $where_values[] = $endDateTime->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // invalid date, ignore filter
            }
        }

        $where_sql = implode(' AND ', $where_clauses);

        // データ取得 (ページネーションなし)
        $query = $wpdb->prepare(
            "SELECT sessions.*, JSON_UNQUOTE(JSON_EXTRACT(line_id.profile, '$.displayName')) AS displayName FROM {$table_name} sessions LEFT JOIN {$table_name_line_id} line_id ON line_id.line_id = sessions.line_user_id AND line_id.channel_prefix = sessions.channel_prefix WHERE {$where_sql} ORDER BY sessions.updated_at DESC",
            $where_values
        );
        $results = $wpdb->get_results($query, ARRAY_A);

        // 対象インタラクションのステップ一覧（指定バージョンがあればそのバージョンのみ）
        $interaction_versions = !empty($version) ? array_map('absint', $version) : null;
        $all_steps = [];
        $interaction_post = get_post($interaction_id);
        if ($interaction_post) {
            $latest_version = get_post_meta($interaction_id, InteractionCPT::META_KEY_VERSION, true) ?: 1;
            $versions_to_load = $interaction_versions ?? range(1, intval($latest_version));
            foreach ($versions_to_load as $v) {
                $interaction_def = InteractionDefinition::from_post($interaction_id, $v);
                if ($interaction_def) {
                    foreach ($interaction_def->get_steps() as $step) {
                        if (!isset($all_steps[$step->get_id()])) {
                            $all_steps[$step->get_id()] = $step->get_title();
                        }
                    }
                }
            }
        }

        // CSVヘッダ
        $headers = [
            __('Session ID', LineConnect::PLUGIN_NAME),
            __('Version', LineConnect::PLUGIN_NAME),
            __('Channel', LineConnect::PLUGIN_NAME),
            __('LINE User ID', LineConnect::PLUGIN_NAME),
            __('Status', LineConnect::PLUGIN_NAME),
            __('Current Step', LineConnect::PLUGIN_NAME),
            __('Updated At', LineConnect::PLUGIN_NAME),
            __('Created At', LineConnect::PLUGIN_NAME),
        ];
        $step_headers = array_values($all_steps);
        $headers = array_merge($headers, $step_headers);

        // CSV をメモリストリームに出力
        $fp = fopen('php://temp', 'r+');
        // Excelでの文字化け対策にBOMを付けたい場合は下記を有効化してください
        // fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $headers, ',', '"', '\\');

        foreach ($results as $row) {
            $session = InteractionSession::from_db_row((object)$row);
            $answers = $session->get_answers();

            $channel_info = LineConnect::get_channel($row['channel_prefix']);
            $channel_name = $channel_info ? $channel_info['name'] : $row['channel_prefix'];

            $csv_row = [
                $row['id'],
                $row['interaction_version'],
                $channel_name,
                $row['line_user_id'],
                $row['status'],
                $row['current_step_id'],
                DateUtil::format_utc_in_wp_tz($row['updated_at']),
                DateUtil::format_utc_in_wp_tz($row['created_at']),
            ];

            foreach ($all_steps as $step_id => $step_title) {
                $csv_row[] = isset($answers[$step_id]) ? $answers[$step_id] : '';
            }

            fputcsv($fp, $csv_row, ',', '"', '\\');
        }

        rewind($fp);
        $csv_contents = stream_get_contents($fp);
        fclose($fp);

        $interaction = get_post($interaction_id);
        $filename_slug = $interaction ? sanitize_title($interaction->post_title) : 'sessions';
        $filename = "{$filename_slug}_sessions_" . date('Y-m-d') . ".csv";

        return [
            'filename' => $filename,
            'csv' => $csv_contents,
        ];
    }

    /**
     * Admin-post handler: outputs headers and CSV, then exits.
     */
    public static function download_interaction_session_page() {
        // 権限チェック
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_die(__('Permission denied', LineConnect::PLUGIN_NAME), '', array('response' => 403));
        }

        if (!headers_sent()) {
            nocache_headers();
        }

        $interaction_id = isset($_REQUEST['interaction_id']) ? intval($_REQUEST['interaction_id']) : 0;
        if ($interaction_id <= 0) {
            wp_die(__('Invalid interaction_id', LineConnect::PLUGIN_NAME), '', array('response' => 400));
        }

        // Build options from request
        $options = [
            'status' => isset($_REQUEST['status']) ? (is_array($_REQUEST['status']) ? $_REQUEST['status'] : array($_REQUEST['status'])) : null,
            'version' => isset($_REQUEST['version']) ? (is_array($_REQUEST['version']) ? $_REQUEST['version'] : array($_REQUEST['version'])) : null,
            'channel' => isset($_REQUEST['channel']) ? (is_array($_REQUEST['channel']) ? $_REQUEST['channel'] : array($_REQUEST['channel'])) : null,
            'line_user_id' => isset($_REQUEST['line_user_id']) ? sanitize_text_field($_REQUEST['line_user_id']) : null,
            'updated_at_start' => isset($_REQUEST['updated_at_start']) ? sanitize_text_field($_REQUEST['updated_at_start']) : null,
            'updated_at_end' => isset($_REQUEST['updated_at_end']) ? sanitize_text_field($_REQUEST['updated_at_end']) : null,
        ];

        $res = self::build_sessions_csv($interaction_id, $options);
        $filename = $res['filename'];
        $csv_contents = $res['csv'];

        if (ob_get_level()) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Description: File Transfer');
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($csv_contents));
        }
        echo $csv_contents;

        exit;
    }
}
