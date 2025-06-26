<?php

/**
 * Lineconnect Audience Screen Class
 *
 * LINE Connect Audience
 *
 * @package Lineconnect
 * @subpackage Audience
 * @category Components
 * @package  Audience
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

namespace Shipweb\LineConnect\PostType\Audience;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Components\ReactJsonSchemaForm;

class Screen {

    /**
     * 管理画面メニューの追加
     */
    static function set_plugin_menu() {
        add_submenu_page(
            lineconnect::SLUG__DASHBOARD,
            __('LINE Connect Audience', lineconnect::PLUGIN_NAME),
            __('Audiences', lineconnect::PLUGIN_NAME),
            'manage_options',
            'edit.php?post_type=' . Audience::POST_TYPE,
            false,
            NULL
        );
    }

    /**
     * メタボックスの登録
     */
    static function register_meta_box() {
        add_meta_box(
            Audience::META_KEY_DATA,
            __('LINE Connect Audience', lineconnect::PLUGIN_NAME),
            array(self::class, 'show_audience_form'),
            Audience::POST_TYPE,
            'advanced',
            'default'
        );
    }

    /**
     * 管理画面用スクリプトの読み込み
     */
    static function wpdocs_selectively_enqueue_admin_script() {
        // require_once plugin_dir_path(__FILE__) . 'rjsf.php';
        ReactJsonSchemaForm::wpdocs_selectively_enqueue_admin_script(Audience::POST_TYPE);
    }

    /**
     * オーディエンスフォームの表示
     */
    static function show_audience_form() {
        $ary_init_data = array();
        $formName = Audience::PARAMETER_DATA;
        $ary_init_data['formName'] = $formName;

        $schema_version = get_post_meta(get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true);
        $formData = get_post_meta(get_the_ID(), Audience::META_KEY_DATA, true);

        // 単一フォームのスキーマとUIスキーマ
        $form = array(
            'id' => 'audience',
            'schema' => Audience::get_audience_schema(),
            'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_audience_uischema', Schema::get_uischema()),
            'formData' => !empty($formData[0]) ? Audience::get_form_audience_data($formData[0], $schema_version) : new \stdClass(),
            'props' => new \stdClass(),
        );
        $ary_init_data['translateString'] = ReactJsonSchemaForm::get_translate_string();
        $ary_init_data['form'] = array($form);
        $nonce_field = wp_nonce_field(
            Audience::CREDENTIAL_ACTION,
            Audience::CREDENTIAL_NAME,
            true,
            false
        );

        // require_once plugin_dir_path(__FILE__) . 'rjsf.php';
        ReactJsonSchemaForm::show_json_edit_form($ary_init_data, $nonce_field);
    }

    /**
     * 投稿の保存
     */
    static function save_post_audience($post_ID, $post, $update) {
        if (isset($_POST[Audience::CREDENTIAL_NAME]) && check_admin_referer(Audience::CREDENTIAL_ACTION, Audience::CREDENTIAL_NAME)) {
            $audience_data = isset($_POST[Audience::PARAMETER_DATA]) ? stripslashes($_POST[Audience::PARAMETER_DATA]) : '';

            if (!empty($audience_data)) {
                $json_audience_data = json_decode($audience_data, true);
                if (!empty($json_audience_data)) {
                    update_post_meta($post_ID, Audience::META_KEY_DATA, $json_audience_data);
                    update_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION, Audience::SCHEMA_VERSION);
                } else {
                    delete_post_meta($post_ID, Audience::META_KEY_DATA);
                    delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
                }
            } else {
                delete_post_meta($post_ID, Audience::META_KEY_DATA);
                delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
            }
        }
    }

    // Ajaxでオーディエンスデータを返す
    static function ajax_get_slc_audience() {
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
            $formData  = get_post_meta($post_id, Audience::META_KEY_DATA, true);
        }
        $result['result']  = $isSuccess ? 'success' : 'failed';
        $result['formData'] = $formData;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        wp_die();
    }
}
