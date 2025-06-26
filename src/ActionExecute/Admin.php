<?php

/**
 * Lineconnect ActionExecuteAdmin
 * 
 * @category Component
 * @package  Shipweb\LineConnect\ActionExecute
 */

namespace Shipweb\LineConnect\ActionExecute;

use Shipweb\LineConnect\ActionExecute\ActionExecute;
use Shipweb\LineConnect\ActionFlow\ActionFlow;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Audience\Audience as Audience;
use Shipweb\LineConnect\PostType\Audience\Schema as AudienceSchema;
use Shipweb\LineConnect\Components\ReactJsonSchemaForm;

class Admin {

    /**
     * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
     * 管理画面のトップメニューページを追加する関数
     */

    static function set_plugin_menu() {
        // 設定のサブメニュー「LINE Connect」を追加
        $page_hook_suffix = add_submenu_page(
            // 親ページ：
            lineconnect::SLUG__DASHBOARD,
            // ページタイトル：
            __('LINE Connect Action Execute', lineconnect::PLUGIN_NAME),
            // メニュータイトル：
            __('Action Execute', lineconnect::PLUGIN_NAME),
            // 権限：
            // manage_optionsは以下の管理画面設定へのアクセスを許可
            // ・設定 > 一般設定
            // ・設定 > 投稿設定
            // ・設定 > 表示設定
            // ・設定 > ディスカッション
            // ・設定 > パーマリンク設定
            'manage_options',
            // ページを開いたときのURL(slug)：
            ActionExecute::SLUG__FORM,
            // メニューに紐づく画面を描画するcallback関数：
            array(\Shipweb\LineConnect\ActionExecute\Admin::class, 'show_form'),
            // メニューの位置
            NULL
        );
        add_action("admin_print_styles-{$page_hook_suffix}", array(\Shipweb\LineConnect\ActionExecute\Admin::class, 'wpdocs_plugin_admin_styles'));
        add_action("admin_print_scripts-{$page_hook_suffix}", array(\Shipweb\LineConnect\ActionExecute\Admin::class, 'wpdocs_plugin_admin_scripts'));
        // remove_menu_page( lineconnect::SLUG__DM_FORM );
    }

    // 管理画面用にスクリプト読み込み
    static function wpdocs_plugin_admin_scripts() {
        $js = 'frontend/' . ActionExecute::NAME . '/dist/slc_' . ActionExecute::NAME . '.js';
        wp_enqueue_script(lineconnect::PLUGIN_PREFIX . ActionExecute::NAME, plugins_url($js, LineConnect::getRootDir() . lineconnect::PLUGIN_ENTRY_FILE_NAME), array('wp-element', 'wp-i18n'), filemtime(LineConnect::getRootDir() . $js), true);
        wp_set_script_translations(lineconnect::PLUGIN_PREFIX . ActionExecute::NAME, lineconnect::PLUGIN_NAME, LineConnect::getRootDir() . 'frontend/' . ActionExecute::NAME . '/languages');
    }

    // 管理画面用にスタイル読み込み
    static function wpdocs_plugin_admin_styles() {
        $css = 'frontend/' . ActionExecute::NAME . '/dist/style.css';
        wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'admin-css', plugins_url($css, LineConnect::getRootDir() . lineconnect::PLUGIN_ENTRY_FILE_NAME), array(), filemtime(LineConnect::getRootDir() . $css));
        $override_css_file = 'frontend/rjsf/dist/rjsf-override.css';
        wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'rjsf-override-css', plugins_url($override_css_file, LineConnect::getRootDir() . lineconnect::PLUGIN_ENTRY_FILE_NAME), array(), filemtime(LineConnect::getRootDir() . $override_css_file));
    }

    /**
     * フォームを表示
     */

    static function show_form() {
        $users                           = isset($_GET['users']) ? $_GET['users'] : array();
        if (! is_array($users)) {
            $users = array($users);
        }

        $ary_init_data = array();
        $ary_init_data['ajaxurl']        = admin_url('admin-ajax.php');
        $ary_init_data['ajax_nonce']     = wp_create_nonce(lineconnect::CREDENTIAL_ACTION__POST);
        $ary_init_data['audienceFormName'] = 'actionexecute-audience-data';
        $audience_schema = Audience::get_audience_schema();
        $audience_form_data = [];
        if (!empty($users)) {
            $audience_form_data = array(
                'condition' => array(
                    'conditions' => array(
                        array('type' => 'wpUserId', 'wpUserId' => $users)
                    )
                )
            );
        }
        $audience_form = array(
            'id' => 'audience',
            'schema' => $audience_schema,
            'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_audience_uischema', AudienceSchema::get_uischema()),
            'formData' => $audience_form_data,
            'props' => new \stdClass(),
        );
        $ary_init_data['audienceForm'] = array($audience_form);
        $slc_audiences = [];
        foreach (Audience::get_lineconnect_audience_name_array() as $post_id => $title) {
            $slc_audiences[] = array(
                'post_id' => $post_id,
                'title' => $title,
            );
        }
        $ary_init_data['slc_audiences'] = $slc_audiences;

        $formName                         = 'actionexecute-actionflow-data';
        $ary_init_data['actionFlowFormName']        = $formName;
        $actionFlowForm =
            array(
                array(
                    'id' => 'actionexecute',
                    'schema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_' . ActionExecute::NAME . '_schema', ActionFlow::getSchema()),
                    'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_' . ActionExecute::NAME . '_uischema', ActionFlow::getUiSchema()),
                    'formData' => [],
                    'props' => new \stdClass(),
                )
            );

        $ary_init_data['actionFlowForm']        = $actionFlowForm;
        $slc_actionflows = [];
        foreach (ActionFlow::get_lineconnect_actionflow_name_array() as $post_id => $title) {
            $slc_actionflows[] = array(
                'post_id' => $post_id,
                'title' => $title,
            );
        }
        $ary_init_data['slc_actionflows'] = $slc_actionflows;
        $ary_init_data['translateString'] = ReactJsonSchemaForm::get_translate_string();

        $inidata = json_encode($ary_init_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
        echo <<< EOM
<div id="line_actionexecute_root"></div>
<script>
var lc_initdata = {$inidata};
</script>
EOM;
    }

    //アクション実行AJAX
    static function ajax_action_execute() {
        $result = self::do_action_execute();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        wp_die();
    }
    public static function do_action_execute() {
        // ログインしていない場合は無視
        if (! is_user_logged_in()) {
            return array(
                'result' => 'failed',
                'success' => array(),
                'error' => array(__('You are not logged in.', lineconnect::PLUGIN_NAME),),
            );
        }
        // 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
        if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
            return array(
                'result' => 'failed',
                'success' => array(),
                'error' => array(__('You do not have permission to send messages.', lineconnect::PLUGIN_NAME),),
            );
        }
        // nonceで設定したcredentialをPOST受信していない場合は無視
        if (! isset($_POST['nonce']) || ! $_POST['nonce'] || ! check_ajax_referer(lineconnect::CREDENTIAL_ACTION__POST, 'nonce')) {
            return array(
                'result' => 'failed',
                'success' => array(),
                'error' => array(__('Nonce is not set or invalid.', lineconnect::PLUGIN_NAME),),
            );
        }
        $audience              = isset($_POST['audience']) ? array_map('stripslashes_deep', $_POST['audience']) : [];
        $actionFlow            = isset($_POST['actionFlow']) ? array_map('stripslashes_deep', $_POST['actionFlow']) : [];
        // error_log(print_r(gettype($actionFlow[0]['actions'][0]['response_return_value']), true));
        // error_log(print_r($actionFlow[0]['actions'][0]['response_return_value'], true));

        $mode = isset($_POST['mode']) ? $_POST['mode'] : '';
        if (in_array($mode, ['execute', 'count']) === false) {
            $mode = 'execute';
        }

        if ($mode == 'count') {
            $response = Audience::get_recepients_count(Audience::get_audience_by_condition($audience[0]['condition'] ?? []));
        } else if ($mode == 'execute') {
            $recepient = Audience::get_audience_by_condition($audience[0]['condition'] ?? []);
            if (empty($recepient)) {
                return array(
                    'result' => 'success',
                    'success' => array(__('There was no target to be sent that matched the condition.', lineconnect::PLUGIN_NAME)),
                    'error' => array(),
                );
            } else {
                $response = ActionFlow::execute_actionflow_by_audience($actionFlow[0], $recepient);
            }
        }

        return array(
            'result' => $response['success'] ? 'success' : 'failed',
            'success' => $response['success_messages'],
            'error' => $response['error_messages'],
        );
    }
}
