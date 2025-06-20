<?php

/**
 * LineConnect
 * アクションフローの管理画面
 * 
 * @package Shipweb\LineConnect\ActionFlow
 */

namespace Shipweb\LineConnect\ActionFlow;

use Shipweb\LineConnect\ActionFlow\ActionFlow;
use Shipweb\LineConnect\ActionFlow\Admin as ActionFlowAdmin;
use LineConnect;
use Shipweb\LineConnect\Components\ReactJsonSchemaForm;
use stdClass;
use lineconnectConst;

/**
 * アクションフローの管理画面
 */
class Admin {
    /**
     * 画面のslug
     */
    static function set_plugin_menu() {
        // 設定のサブメニュー「LINE Connect」を追加
        $page_hook_suffix = add_submenu_page(
            // 親ページ：
            lineconnect::SLUG__DASHBOARD,
            // ページタイトル：
            __('LINE Connect ActionFlow', lineconnect::PLUGIN_NAME),
            // メニュータイトル：
            __('ActionFlows', lineconnect::PLUGIN_NAME),
            // 権限：
            // manage_optionsは以下の管理画面設定へのアクセスを許可
            // ・設定 > 一般設定
            // ・設定 > 投稿設定
            // ・設定 > 表示設定
            // ・設定 > ディスカッション
            // ・設定 > パーマリンク設定
            'manage_options',
            // ページを開いたときのURL(slug)：
            'edit.php?post_type=' . ActionFlow::POST_TYPE,
            // メニューに紐づく画面を描画するcallback関数：
            false,
            // メニューの位置
            NULL
        );
        // remove_menu_page( lineconnect::SLUG__DM_FORM );
    }

    static function register_meta_box() {
        // 投稿ページでRJSFフォームを表示
        add_meta_box(
            // チェックボックスのID
            ActionFlow::META_KEY_DATA,
            // チェックボックスのラベル名
            __('LINE Connect ActionFlow', lineconnect::PLUGIN_NAME),
            // チェックボックスを表示するコールバック関数
            array(ActionFlowAdmin::class, 'show_json_edit_form'),
            // 投稿画面に表示
            ActionFlow::POST_TYPE,
            // 表示位置
            'advanced',
            // 表示優先度
            'default'
        );
    }

    // 管理画面（投稿ページ）用にスクリプト読み込み
    static function wpdocs_selectively_enqueue_admin_script() {
        // require_once LineConnect::getRootDir() . 'include/rjsf.php';
        ReactJsonSchemaForm::wpdocs_selectively_enqueue_admin_script(ActionFlow::POST_TYPE);
    }

    /**
     * JSONスキーマからフォームを表示
     */
    static function show_json_edit_form() {
        $ary_init_data = array();
        $ary_init_data['formName'] = ActionFlow::PARAMETER_DATA;
        $schema_version = get_post_meta(get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true);
        $formData = get_post_meta(get_the_ID(), ActionFlow::META_KEY_DATA, true);

        $mainSchema = ActionFlow::getSchema();
        $form = array(
            array(
                'id' => ActionFlow::NAME,
                'schema' => $mainSchema,
                'uiSchema' => ActionFlow::getUiSchema(),
                'formData' => self::get_form_data($formData[0] ?? null, $schema_version),
                'props' => new stdClass(),
            ),
        );
        $ary_init_data['subSchema'] = array();
        $ary_init_data['form'] = $form;
        $ary_init_data['translateString'] = ReactJsonSchemaForm::get_translate_string();
        $nonce_field = wp_nonce_field(
            ActionFlow::CREDENTIAL_ACTION,
            ActionFlow::CREDENTIAL_NAME,
            true,
            false
        );
        // require_once LineConnect::getRootDir() . 'include/rjsf.php';
        ReactJsonSchemaForm::show_json_edit_form($ary_init_data, $nonce_field);
    }

    /**
     * 記事を保存
     */
    static function save_post($post_ID, $post, $update) {
        if (isset($_POST[ActionFlow::CREDENTIAL_NAME]) && check_admin_referer(ActionFlow::CREDENTIAL_ACTION, ActionFlow::CREDENTIAL_NAME)) {
            $data = isset($_POST[ActionFlow::PARAMETER_DATA]) ? stripslashes($_POST[ActionFlow::PARAMETER_DATA]) : '';

            if (!empty($data)) {
                $json_data = json_decode($data, true);
                if (!empty($json_data)) {
                    update_post_meta($post_ID, ActionFlow::META_KEY_DATA, $json_data);
                    update_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION, ActionFlow::SCHEMA_VERSION);
                } else {
                    delete_post_meta($post_ID, ActionFlow::META_KEY_DATA);
                    delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
                }
            } else {
                delete_post_meta($post_ID, ActionFlow::META_KEY_DATA);
                delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
            }
        }
    }

    /** 
     * Return form data
     */
    static function get_form_data($formData, $schema_version) {
        if (empty($schema_version) || $schema_version == ActionFlow::SCHEMA_VERSION) {
            return !empty($formData) ? $formData : new stdClass();
        }
        // if old schema version, migrate and return
    }
}
