<?php

/**
 * Lineconnect
 * インタラクション管理 REST API
 */

namespace Shipweb\LineConnect\Interaction\Manage;

use PhpParser\Node\Scalar\MagicConst\Line;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Interaction\InteractionDefinition;
use Shipweb\LineConnect\Interaction\InteractionSession;
use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;
use Shipweb\LineConnect\Utilities\DateUtil;

class RESTAPI {
    public static function register_routes() {
        // インタラクションの統計情報リストを取得
        register_rest_route(
            LineConnect::PLUGIN_NAME,
            '/interactions',
            array(
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_interactions'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            )
        );

        // 特定のインタラクションの基本情報と統計情報を取得
        register_rest_route(
            LineConnect::PLUGIN_NAME,
            '/interactions/(?P<interaction_id>\d+)',
            array(
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_interaction'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => array(
                    'interaction_id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'required' => true,
                    ),
                ),
            )
        );

        // 特定のインタラクションに紐づくセッションのリストを取得
        register_rest_route(
            LineConnect::PLUGIN_NAME,
            '/interactions/(?P<interaction_id>\d+)/sessions',
            array(
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_sessions_by_interaction'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => array(
                    'interaction_id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'required' => true,
                    ),
                    'status' => array(
                        'sanitize_callback' => function ($param, $request, $key) {
                            return is_array($param) ? array_map('sanitize_text_field', $param) : sanitize_text_field($param);
                        }
                    ),
                    'version' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_array($param) ? array_map('absint', $param) : absint($param);
                        }
                    ),
                    'channel' => array(
                        'sanitize_callback' => function ($param, $request, $key) {
                            return is_array($param) ? array_map('sanitize_text_field', $param) : sanitize_text_field($param);
                        }
                    ),
                    'line_user_id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            if (empty($param)) {
                                return true; // 空の場合はOK
                            }
                            // 32桁の英数字かどうかチェック
                            return preg_match('/^U[a-zA-Z0-9]{32}$/', $param);
                        },
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'updated_at_start' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            if (empty($param)) {
                                return true; // 空の場合はOK
                            }
                            // ISO 8601形式の日時文字列かどうかチェック
                            return preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{3})?Z?$/', $param);
                        },
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'updated_at_end' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            if (empty($param)) {
                                return true; // 空の場合はOK
                            }
                            // ISO 8601形式の日時文字列かどうかチェック
                            return preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{3})?Z?$/', $param);
                        },
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'page' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'sanitize_callback' => 'absint',
                        'default' => 1,
                    ),
                    'per_page' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'sanitize_callback' => 'absint',
                        'default' => 20,
                    ),
                ),
            )
        );

        // 特定のセッションを更新する（answers 完全上書き / status 更新）
        register_rest_route(
            LineConnect::PLUGIN_NAME,
            '/interactions/(?P<interaction_id>\d+)/sessions/(?P<session_id>\d+)',
            array(
                'methods' => array('PATCH','PUT'),
                'callback' => [__CLASS__, 'update_session_by_interaction'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => array(
                    'interaction_id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'required' => true,
                    ),
                    'session_id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'required' => true,
                    ),
                ),
            )
        );

        // 特定のセッションを削除する
        register_rest_route(
            LineConnect::PLUGIN_NAME,
            '/interactions/(?P<interaction_id>\d+)/sessions/(?P<session_id>\d+)',
            array(
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_session'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => array(
                    'interaction_id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'required' => true,
                    ),
                    'session_id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'required' => true,
                    ),
                ),
            )
        );
    }

    /**
     * 特定のインタラクションの基本情報と統計情報を取得
     */
    public static function get_interaction(\WP_REST_Request $request) {
        $interaction_id = $request['interaction_id'];

        $interaction = InteractionDefinition::from_post($interaction_id, null);
        if (!$interaction) {
            return new \WP_Error('interaction_not_found', __('Interaction not found', LineConnect::PLUGIN_NAME), array('status' => 404));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;

        // Get aggregated stats
        $aggregated_query = $wpdb->prepare(
            "SELECT status, COUNT(*) as count FROM {$table_name} WHERE interaction_id = %d GROUP BY status",
            $interaction_id
        );
        $aggregated_results = $wpdb->get_results($aggregated_query, ARRAY_A);

        $total_sessions = 0;
        $stats = array(
            'active' => 0,
            'paused' => 0,
            'completed' => 0,
            'timeout' => 0,
        );

        foreach ($aggregated_results as $row) {
            $stats[$row['status']] = intval($row['count']);
            $total_sessions += intval($row['count']);
        }

        // Unique users
        $unique_query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(channel_prefix, ':', line_user_id)) FROM {$table_name} WHERE interaction_id = %d",
            $interaction_id
        );
        $unique_users = intval($wpdb->get_var($unique_query));

        // Completion rate
        $completion_rate = $total_sessions > 0 ? round(($stats['completed'] / $total_sessions) * 100, 1) : 0.0;

        $total_stats = array(
            'active' => $stats['active'],
            'paused' => $stats['paused'],
            'completed' => $stats['completed'],
            'timeout' => $stats['timeout'],
            'total_sessions' => $total_sessions,
            'completion_rate' => $completion_rate,
            'unique_users' => $unique_users,
        );

        // Per version stats
        $version_query = $wpdb->prepare(
            "SELECT interaction_version, status, COUNT(*) as count FROM {$table_name} WHERE interaction_id = %d GROUP BY interaction_version, status",
            $interaction_id
        );
        $version_results = $wpdb->get_results($version_query, ARRAY_A);

        $by_version = array();
        foreach ($version_results as $row) {
            $version = intval($row['interaction_version']);
            if (!isset($by_version[$version])) {
                $by_version[$version] = array(
                    'active' => 0,
                    'paused' => 0,
                    'completed' => 0,
                    'timeout' => 0,
                    'total_sessions' => 0,
                    'completion_rate' => 0.0,
                    'unique_users' => 0,
                );
            }
            $by_version[$version][$row['status']] = intval($row['count']);
            $by_version[$version]['total_sessions'] += intval($row['count']);
        }

        // Calculate completion rate and unique users for each version
        foreach ($by_version as $version => &$v_stats) {
            $v_stats['completion_rate'] = $v_stats['total_sessions'] > 0 ? round(($v_stats['completed'] / $v_stats['total_sessions']) * 100, 1) : 0.0;

            $unique_v_query = $wpdb->prepare(
                "SELECT COUNT(DISTINCT CONCAT(channel_prefix, ':', line_user_id)) FROM {$table_name} WHERE interaction_id = %d AND interaction_version = %d",
                $interaction_id,
                $version
            );
            $v_stats['unique_users'] = intval($wpdb->get_var($unique_v_query));
        }

        $response = array(
            'id' => $interaction->get_id(),
            'title' => $interaction->get_title(),
            'version' => $interaction->get_version(),
            'statistics' => array(
                'total' => $total_stats,
                'by_version' => $by_version,
            ),
        );

        return rest_ensure_response($response);
    }

    /**
     * 特定のインタラクションに紐づくセッションのリストを取得
     */
    public static function get_sessions_by_interaction(\WP_REST_Request $request) {
        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;
        $table_name_line_id = $wpdb->prefix . LineConnect::TABLE_LINE_ID;

        $interaction_id = $request['interaction_id'];

        // パラメータの取得とサニタイズ
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $offset = ($page - 1) * $per_page;

        // WHERE句の構築
        $where_clauses = array();
        $where_values = array();

        $where_clauses[] = 'sessions.interaction_id = %d';
        $where_values[] = $interaction_id;

        if ($status = $request->get_param('status')) {
            if (is_array($status)) {
                $placeholders = implode(', ', array_fill(0, count($status), '%s'));
                $where_clauses[] = "sessions.status IN ($placeholders)";
                $where_values = array_merge($where_values, $status);
            } else {
                $where_clauses[] = 'sessions.status = %s';
                $where_values[] = $status;
            }
        }

        if ($version = $request->get_param('version')) {
            if(is_array($version)) {
                $placeholders = implode(', ', array_fill(0, count($version), '%d'));
                $where_clauses[] = "sessions.interaction_version IN ($placeholders)";
                $where_values = array_merge($where_values, $version);
            } else {
                $where_clauses[] = 'sessions.interaction_version = %d';
                $where_values[] = $version;
            }
        }

        if ($channel = $request->get_param('channel')) {
            if(is_array($channel)) {
                $placeholders = implode(', ', array_fill(0, count($channel), '%s'));
                $where_clauses[] = "sessions.channel_prefix IN ($placeholders)";
                $where_values = array_merge($where_values, $channel);
            } else {
                $where_clauses[] = 'sessions.channel_prefix = %s';
                $where_values[] = $channel;
            }
        }

        if ($lineUserId = $request->get_param('line_user_id')) {
            $where_clauses[] = 'sessions.line_user_id = %s';
            $where_values[] = $lineUserId;
        }

        if ($updatedAtStart = $request->get_param('updated_at_start')) {
            // UTCで送られてきているのでそのまま使用
            $startDateTime = new \DateTime($updatedAtStart, new \DateTimeZone('UTC'));
            // $startDateTime->setTimezone(new \DateTimeZone('UTC'));
            $where_clauses[] = 'sessions.updated_at >= %s';
            $where_values[] = $startDateTime->format('Y-m-d H:i:s');
        }

        if ($updatedAtEnd = $request->get_param('updated_at_end')) {
            // UTCで送られてきているのでそのまま使用
            $endDateTime = new \DateTime($updatedAtEnd, new \DateTimeZone('UTC'));
            // $endDateTime->setTimezone(new \DateTimeZone('UTC'));
            $where_clauses[] = 'sessions.updated_at < %s';
            $where_values[] = $endDateTime->format('Y-m-d H:i:s');
        }

        $where_sql = implode(' AND ', $where_clauses);

        // 総件数の取得
        $total_query = $wpdb->prepare("SELECT COUNT(sessions.id) FROM {$table_name} sessions LEFT JOIN {$table_name_line_id} line_id ON line_id.line_id = sessions.line_user_id AND line_id.channel_prefix = sessions.channel_prefix WHERE {$where_sql}", $where_values);
        $total_items = $wpdb->get_var($total_query);

        // データ本体の取得
        $query = $wpdb->prepare(
            "SELECT sessions.*, JSON_UNQUOTE(JSON_EXTRACT(line_id.profile, '$.displayName')) AS displayName FROM {$table_name} sessions LEFT JOIN {$table_name_line_id} line_id ON line_id.line_id = sessions.line_user_id AND line_id.channel_prefix = sessions.channel_prefix WHERE {$where_sql} ORDER BY sessions.updated_at DESC LIMIT %d OFFSET %d",
            array_merge($where_values, [$per_page, $offset])
        );
        $results = $wpdb->get_results($query, ARRAY_A);


        // answersカラムがJSONなのでデコードする
        foreach ($results as &$result) {
            // get interaction def
            $interaction = InteractionDefinition::from_post($result['interaction_id'], $result['interaction_version']);
            $session = InteractionSession::from_db_row((object)$result);
            $session->set_interaction_definition($interaction);
            $exclude_steps = $interaction->get_exclude_steps();

            $answers = $session->get_excluded_answers($exclude_steps);
            $enhanced_answers = [];
            foreach ($answers as $step_id => $answer) {
                $step = $interaction->get_step($step_id);
                $title = $step ? $step->get_title() : $step_id; // ステップが見つからない場合はIDをフォールバック

                $enhanced_answers[$step_id] = [
                    'answer' => $answer,
                    'title' => $title
                ];
            }
            $result['answers'] = $enhanced_answers;

            $channel = LineConnect::get_channel($result['channel_prefix']);
            if (!empty($channel)) {
                $result['channel_name'] = $channel["name"];
            } else {
                $result['channel_name'] = null;
            }
            // TimezoneをWordpressのタイムゾーンに変換
            $result['updated_at'] = $result['updated_at'] ? DateUtil::format_utc_in_wp_tz($result['updated_at']) : null;
            $result['created_at'] = DateUtil::format_utc_in_wp_tz($result['created_at']);
            $result['expires_at'] = $result['expires_at'] ? DateUtil::format_utc_in_wp_tz($result['expires_at']) : null;
            $result['remind_at'] = $result['remind_at'] ? DateUtil::format_utc_in_wp_tz($result['remind_at']) : null;
            $result['reminder_sent_at'] = $result['reminder_sent_at'] ? DateUtil::format_utc_in_wp_tz($result['reminder_sent_at']) : null;
        }

        // filter optiom
        $channels = LineConnect::get_all_channels();
        $channels_options = [];
        if (!empty($channels)) {
            foreach ($channels as $channel_id => $channel) {
                $channels_options[] = [
                    'value' => $channel['prefix'],
                    'label' => $channel['name'],
                ];
            }
        }
        // フィルターオプション取得のSQLクエリ
        $versions_query = $wpdb->prepare(
            "SELECT DISTINCT interaction_version 
            FROM {$table_name} 
            WHERE interaction_id = %d 
            ORDER BY interaction_version DESC",
            $interaction_id
        );
        $versions = $wpdb->get_col($versions_query);


        $response_data = array(
            'data' => $results,
            'meta' => array(
                'pagination' => array(
                    'total' => $total_items,
                    'pages' => ceil($total_items / $per_page),
                    'page' => $page,
                    'per_page' => $per_page,
                ),
                'filters' => array(
                    'versions' => $versions,
                    'channels' => $channels_options,
                ),
            ),
        );
        $response = rest_ensure_response($response_data);

        return $response;
    }

    /**
     * セッションを更新する
     * answers は完全上書き、status は更新可能
     */
    public static function update_session_by_interaction(\WP_REST_Request $request) {
        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;
        $table_name_line_id = $wpdb->prefix . LineConnect::TABLE_LINE_ID;

        $interaction_id = intval($request['interaction_id']);
        $session_id = intval($request['session_id']);

        // 存在確認
        $existing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND interaction_id = %d", $session_id, $interaction_id),
            ARRAY_A
        );
        if (!$existing) {
            return new \WP_Error('session_not_found', __('Session not found', LineConnect::PLUGIN_NAME), array('status' => 404));
        }

        // REST_Request の JSON / body パラメータを取得（テストでは set_body_params を使うため両方対応）
        $body = $request->get_json_params();
        if (empty($body)) {
            $body = $request->get_body_params();
        }
        // フォームエンコードされる場合は raw body をパースしてみる
        if (empty($body)) {
            $raw = $request->get_param('body') ?? $request->get_body();
            if (!empty($raw)) {
                $decoded_raw = json_decode($raw, true);
                if (is_array($decoded_raw)) {
                    $body = $decoded_raw;
                }
            }
        }

        $new_answers = isset($body['answers']) ? $body['answers'] : null;
        $new_status = isset($body['status']) ? sanitize_text_field($body['status']) : null;

        // Validate status
        $allowed_status = array('active', 'paused', 'completed', 'timeout');
        if ($new_status !== null && !in_array($new_status, $allowed_status, true)) {
            return new \WP_Error('invalid_status', __('Invalid status', LineConnect::PLUGIN_NAME), array('status' => 400));
        }

        // Validate and sanitize answers (must be an object/associative array)
        $encoded_answers = null;
        if ($new_answers !== null) {
            // If answers is a JSON string, try decode
            if (is_string($new_answers)) {
                $decoded = json_decode($new_answers, true);
                if (is_array($decoded)) {
                    $new_answers = $decoded;
                } else {
                    return new \WP_Error('invalid_answers', __('Answers must be an object', LineConnect::PLUGIN_NAME), array('status' => 400));
                }
            }

            if (!is_array($new_answers)) {
                return new \WP_Error('invalid_answers', __('Answers must be an object', LineConnect::PLUGIN_NAME), array('status' => 400));
            }

            $sanitized = array();
            foreach ($new_answers as $step_id => $answer) {
                // force to string and sanitize (HTML not allowed)
                $val = is_scalar($answer) ? strval($answer) : '';
                $sanitized[$step_id] = sanitize_textarea_field($val);
            }
            $encoded_answers = wp_json_encode($sanitized);
        }

        if ($encoded_answers === null && $new_status === null) {
            return new \WP_Error('nothing_to_update', __('No fields to update', LineConnect::PLUGIN_NAME), array('status' => 400));
        }

        $update_data = array();
        if ($encoded_answers !== null) {
            $update_data['answers'] = $encoded_answers;
        }
        if ($new_status !== null) {
            $update_data['status'] = $new_status;
        }
        // updated_at を現在UTCでセット
        $update_data['updated_at'] = gmdate('Y-m-d H:i:s');

        $updated = $wpdb->update($table_name, $update_data, array('id' => $session_id));
        if ($updated === false) {
            return new \WP_Error('db_update_failed', __('Failed to update session', LineConnect::PLUGIN_NAME), array('status' => 500));
        }

        // 更新後の行を取得してフロント用に整形
        $query = $wpdb->prepare(
            "SELECT sessions.*, JSON_UNQUOTE(JSON_EXTRACT(line_id.profile, '$.displayName')) AS displayName FROM {$table_name} sessions LEFT JOIN {$table_name_line_id} line_id ON line_id.line_id = sessions.line_user_id AND line_id.channel_prefix = sessions.channel_prefix WHERE sessions.id = %d",
            $session_id
        );
        $result = $wpdb->get_row($query, ARRAY_A);

        if (!$result) {
            return new \WP_Error('session_not_found_after_update', __('Session not found after update', LineConnect::PLUGIN_NAME), array('status' => 404));
        }

        // get interaction def and build answers with titles
        $interaction = InteractionDefinition::from_post($result['interaction_id'], $result['interaction_version']);
        $session = InteractionSession::from_db_row((object)$result);
        $session->set_interaction_definition($interaction);
        $exclude_steps = $interaction->get_exclude_steps();

        $answers = $session->get_excluded_answers($exclude_steps);
        $enhanced_answers = [];
        foreach ($answers as $step_id => $answer) {
            $step = $interaction->get_step($step_id);
            $title = $step ? $step->get_title() : $step_id;

            $enhanced_answers[$step_id] = [
                'answer' => $answer,
                'title' => $title
            ];
        }
        $result['answers'] = $enhanced_answers;

        $channel = LineConnect::get_channel($result['channel_prefix']);
        if (!empty($channel)) {
            $result['channel_name'] = $channel["name"];
        } else {
            $result['channel_name'] = null;
        }

        // TimezoneをWordpressのタイムゾーンに変換
        $result['updated_at'] = $result['updated_at'] ? DateUtil::format_utc_in_wp_tz($result['updated_at']) : null;
        $result['created_at'] = DateUtil::format_utc_in_wp_tz($result['created_at']);
        $result['expires_at'] = $result['expires_at'] ? DateUtil::format_utc_in_wp_tz($result['expires_at']) : null;
        $result['remind_at'] = $result['remind_at'] ? DateUtil::format_utc_in_wp_tz($result['remind_at']) : null;
        $result['reminder_sent_at'] = $result['reminder_sent_at'] ? DateUtil::format_utc_in_wp_tz($result['reminder_sent_at']) : null;

        $response_data = array(
            'data' => $result
        );

        return rest_ensure_response($response_data);
    }

    /**
     * セッションを削除する
     */
    public static function delete_session(\WP_REST_Request $request) {
        global $wpdb;
        $table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;

        $interaction_id = intval($request['interaction_id']);
        $session_id = intval($request['session_id']);

        // 存在確認
        $existing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d AND interaction_id = %d", $session_id, $interaction_id),
            ARRAY_A
        );
        if (!$existing) {
            return new \WP_Error('session_not_found', __('Session not found', LineConnect::PLUGIN_NAME), array('status' => 404));
        }

        // 物理削除
        $deleted = $wpdb->delete($table_name, array('id' => $session_id));
        if ($deleted === false) {
            return new \WP_Error('db_delete_failed', __('Failed to delete session', LineConnect::PLUGIN_NAME), array('status' => 500));
        }

        // 成功時は204 No Contentを返す
        return new \WP_REST_Response(null, 204);
    }

}
