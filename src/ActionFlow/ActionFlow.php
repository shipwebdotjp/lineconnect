<?php

namespace Shipweb\LineConnect\ActionFlow;

use Shipweb\LineConnect\Action\Action;
use LineConnect;
use lineconnectConst;
use Shipweb\LineConnect\Message\LINE\Builder;

/**
 * アクションフロークラス
 */
class ActionFlow {
    const NAME = 'action_flow';

    /**
     * CredentialAction
     */
    const CREDENTIAL_ACTION = LineConnect::PLUGIN_ID . '-nonce-action_' . self::NAME;

    /**
     * CredentialName
     */
    const CREDENTIAL_NAME = LineConnect::PLUGIN_ID . '-nonce-name_' . self::NAME;

    /**
     * 投稿メタキー
     */
    const META_KEY_DATA = self::NAME . '-data';

    /**
     * パラメータ名
     */
    const PARAMETER_DATA = LineConnect::PLUGIN_PREFIX . self::META_KEY_DATA;

    /**
     * Schema Version
     */
    const SCHEMA_VERSION = 1;

    /**
     * カスタム投稿タイプスラッグ
     */
    const POST_TYPE = lineconnect::PLUGIN_PREFIX . self::NAME;


    /**
     * スキーマを返す
     * 
     * @return array JSONスキーマ
     */
    public static function getSchema() {
        $schema = array(
            'type'       => 'object',
            'properties' => array(
                'actions'  => array(
                    'title' => __('Action', lineconnect::PLUGIN_NAME),
                    'type'  => 'array',
                    'items' => array(
                        'type'     => 'object',
                        'oneOf'    => array(),
                        'required' => array(
                            'parameters',
                        ),
                    ),
                ),
                'chains' => array(
                    'type' => 'array',
                    'title' => __('Action chain', lineconnect::PLUGIN_NAME),
                    'items' => array(
                        'type'     => 'object',
                        'properties' => array(
                            'to' => array(
                                'type' => 'string',
                                'title' => __('Destination argument to', lineconnect::PLUGIN_NAME),
                                'description' => __('Injection Destination Argument Path. e.g. 2.message', lineconnect::PLUGIN_NAME),
                            ),
                            'data' => array(
                                'type' => 'string',
                                'title' => __('Data', lineconnect::PLUGIN_NAME),
                                'description' => __('Injection Data. You can use return value of previous action. e.g. {{$.return.1}}', lineconnect::PLUGIN_NAME),
                            ),
                        ),
                    ),
                ),
            ),
        );
        Action::build_action_schema_items($schema['properties']['actions']['items']['oneOf']);
        $schema = apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_' . self::NAME . '_schema', $schema);
        return $schema;
    }

    /**
     * UIスキーマを返す
     * 
     * @return array JSONスキーマ
     */
    public static function getUiSchema() {
        $uiSchema = array(
            'ui:submitButtonOptions' => array(
                'norender' => true,
            ),
            'actions' => array(
                'items' => array(
                    'action_name' => array(
                        'ui:widget' => 'hidden',
                    ),
                    'parameters' => array(
                        'ui:options' => array(
                            'addText' => __('Add parameter', lineconnect::PLUGIN_NAME),
                        ),
                        'body' => array(
                            'ui:widget' => 'textarea',
                            'ui:options' => array(
                                'rows' => 5,
                            ),
                        ),
                        'json' => array(
                            'ui:widget' => 'textarea',
                            'ui:options' => array(
                                'rows' => 5,
                            ),
                        ),
                    ),
                ),
                'ui:options' => array(
                    'addText' => __('Add action', lineconnect::PLUGIN_NAME),
                ),
            ),
            'chains' => array(
                'ui:options' => array(
                    'addText' => __('Add chain', lineconnect::PLUGIN_NAME),
                ),
            ),
        );
        return apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_' . self::NAME . '_uischema', $uiSchema);
    }

    static function get_lineconnect_actionflow_name_array() {
        $args = array(
            'post_type' => self::POST_TYPE,
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
        );
        $posts = get_posts($args);
        $ret = array();
        foreach ($posts as $post) {
            $ret[$post->ID] = $post->post_title;
        }
        return $ret;
    }

    /**
     * アクションフローを実行する
     * 
     * @param array $actionFlow アクションフロー
     * @param array $recepient オーディエンス
     * @return array 実行結果
     */
    static function execute_actionflow_by_audience($actionFlow, $recepient) {
        $ary_success_message = array();
        $ary_error_message   = array();
        foreach ($recepient as $secret_prefix => $recepient_item) {
            $error_message        = $success_message = '';
            $channel = lineconnect::get_channel($secret_prefix);
            $type = $recepient_item['type'];
            if ($type == 'broadcast') {
                $error_message = __('Broadcast is not supported.', lineconnect::PLUGIN_NAME);
            } else {
                $ary_result_success = array();
                $ary_result_error = array();
                foreach ($recepient_item['line_user_ids'] as $line_user_id) {
                    $event = new \stdClass();
                    $event->source = new \stdClass();
                    $event->source->userId = $line_user_id;
                    $action_result = Action::do_action($actionFlow['actions'], $actionFlow['chains'] ?? null, $event, $secret_prefix);
                    $response = null;
                    if (! empty($action_result['messages'])) {
                        $multimessage = Builder::createMultiMessage($action_result['messages']);
                        $response = Builder::sendPushMessage($channel, $line_user_id, $multimessage);
                    }
                    if ($action_result['success'] && (is_null($response) || (isset($response['success']) && $response['success']))) {
                        $ary_result_success[] = $line_user_id;
                    } else {
                        $ary_result_error[] = $line_user_id;
                        if (isset($response) && isset($response['success']) && !$response['success']) {
                            $action_result['results'][] = array(
                                'success' => $response['success'],
                                'response' => $response ?? null,
                                'error' => $response['message'] ?? null,
                            );
                        }
                    }
                }
                if (empty($ary_result_error)) {
                    $success_message = sprintf(_n('Action executed %s person.', 'Action executed %s people.', count($ary_result_success), lineconnect::PLUGIN_NAME), number_format(count($ary_result_success)));
                    if (isset($action_result['results'])) {
                        foreach ($action_result['results'] as $action_idx => $result) {
                            $success_message .= sprintf("\n%s-%s: %s", __('Action', lineconnect::PLUGIN_NAME), ($action_idx + 1), print_r($result['response'] ?? '', true));
                        }
                    }
                } else {
                    $error_message = sprintf(_n('Action execution failed for %s person.', 'Action execution failed for %s people.', count($ary_result_error), lineconnect::PLUGIN_NAME), number_format(count($ary_result_error)));
                    if (isset($action_result['results'])) {
                        foreach ($action_result['results'] as $action_idx => $result) {
                            $error_message .= sprintf("\n%s-%s: %s", __('Action', lineconnect::PLUGIN_NAME), ($action_idx + 1), print_r($result['error'] ?? '', true));
                        }
                    }
                }
            }

            // 送信に成功した場合
            if ($success_message) {
                $ary_success_message[] = $channel['name'] . ': ' . $success_message;
            }
            // 送信に失敗した場合
            else {
                $ary_error_message[] = $channel['name'] . ': ' . $error_message;
            }
        }
        $result = array(
            'success' => empty($ary_error_message),
            'message' => implode("\n", array_merge($ary_error_message, $ary_success_message)),
            'success_messages' => $ary_success_message,
            'error_messages'   => $ary_error_message,
        );
        return $result;
    }

    static function ajax_get_actionflow() {
        $isSuccess = true;
        $formData = [];
        // ログインしていない場合は無視
        if (! is_user_logged_in()) {
            $isSuccess = false;
        }
        // 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
        if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
            $isSuccess = false;
        }
        // nonceで設定したcredentialをPOST受信していない場合は無視
        if (! isset($_POST['nonce']) || ! $_POST['nonce']) {
            $isSuccess = false;
        }
        // nonceで設定したcredentialのチェック結果に問題がある場合
        if (! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
            $isSuccess = false;
        }

        if (! isset($_POST['post_id']) || ! $_POST['post_id']) {
            $isSuccess = false;
        }

        if ($isSuccess) {
            $post_id = $_POST['post_id'];
            $formData  = get_post_meta($post_id, self::META_KEY_DATA, true);
        }
        $result['result']  = $isSuccess ? 'success' : 'failed';
        $result['formData'] = $formData;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        wp_die();
    }
}
