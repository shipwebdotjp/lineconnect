<?php

namespace Shipweb\LineConnect\Chat\API;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Interaction\Interaction;
use Shipweb\LineConnect\Scenario\Scenario;

class FetchUserData {
    // 指定されたユーザーデータを取得
    static function ajax_fetch_user_data() {
        global $wpdb;

        $result = \Shipweb\LineConnect\Utilities\Guard::check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST);
        if ($result['result'] === 'failed') {
            wp_send_json_error($result);
        }

        if (!isset($_POST['channel_prefix']) || empty($_POST['channel_prefix'])) {
            wp_send_json_error([
                'result' => 'failed',
                'message' => __('Channel prefix is required.', 'lineconnect')
            ]);
        }

        if (!isset($_POST['line_id']) || empty($_POST['line_id'])) {
            wp_send_json_error([
                'result' => 'failed',
                'message' => __('Line ID is required.', 'lineconnect')
            ]);
        }

        $channel_prefix = isset($_POST['channel_prefix']) ? stripslashes($_POST['channel_prefix']) : "";
        $line_id = isset($_POST['line_id']) ? stripslashes($_POST['line_id']) : "";
        $table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
        $query = "
                SELECT channel_prefix, line_id as lineId, follow, tags, profile, interactions, scenarios, stats, created_at, updated_at
                FROM {$table_name}
                WHERE channel_prefix = %s AND line_id = %s
        ";
        $query = $wpdb->prepare($query, array($channel_prefix, $line_id));
        $result = $wpdb->get_row($query, ARRAY_A);
        $result['profile'] = json_decode($result['profile'] ?: '{}', true);
        $result['tags'] = json_decode($result['tags'] ?: '[]', true);
        // $result['interactions'] = json_decode($result['interactions'] ?: '{}', true);
        $result['stats'] = json_decode($result['stats'] ?: '{}', true);

        $scenario_names = Scenario::get_scenario_name_array();
        if ($result['scenarios']) {
            $scenarios = json_decode($result['scenarios'] ?: '{}', true);
            foreach ($scenarios as &$scenario) {
                $scenario['name'] = $scenario_names[$scenario['id']] ?? __('Unknown Scenario', 'lineconnect');
            }
        }
        $result['scenarios'] = $scenarios ?? [];

        // get interactions
        $interactions = [];
        $interaction_table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;
        $interaction_sessions = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$interaction_table_name}
            WHERE channel_prefix = %s AND line_user_id = %s
            ORDER BY updated_at DESC
        ", $channel_prefix, $line_id), ARRAY_A);

        $interaction_names = Interaction::get_name_array();
        if ($interaction_sessions) {
            foreach ($interaction_sessions as $session) {
                $interaction_id = $session['interaction_id'];
                $interactions[] = array(
                    'id' => $session['id'],
                    'interaction_id' => $interaction_id,
                    'interaction_name' => isset($interaction_names[$interaction_id]) ? $interaction_names[$interaction_id] : __('Deleted Interaction', 'lineconnect'),
                    'interaction_version' => $session['interaction_version'],
                    'status' => $session['status'],
                    'current_step_id' => $session['current_step_id'],
                    'answers' => json_decode($session['answers'] ?: '{}', true),
                    'created_at' => $session['created_at'],
                    'updated_at' => $session['updated_at'],
                );
            }
        }
        $result['interactions'] = $interactions;

        wp_send_json_success($result);
    }
}
