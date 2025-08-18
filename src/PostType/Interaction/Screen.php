<?php

namespace Shipweb\LineConnect\PostType\Interaction;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Components\ReactJsonSchemaForm;

class Screen {
    /**
     * 管理画面メニューの追加
     */
    static function set_plugin_menu() {
        add_submenu_page(
            lineconnect::SLUG__DASHBOARD,
            __('LINE Connect Interaction', lineconnect::PLUGIN_NAME),
            __('Interactions', lineconnect::PLUGIN_NAME),
            'manage_options',
            'edit.php?post_type=' . Interaction::POST_TYPE,
            false,
            NULL
        );
    }

    /**
     * メタボックスの登録
     */
    static function register_meta_box() {
        add_meta_box(
            Interaction::META_KEY_DATA,
            __('LINE Connect Interaction', lineconnect::PLUGIN_NAME),
            array(self::class, 'show_interaction_form'),
            Interaction::POST_TYPE,
            'advanced',
            'default'
        );
    }

    /**
     * 管理画面用スクリプトの読み込み
     */
    static function wpdocs_selectively_enqueue_admin_script() {
        // require_once plugin_dir_path(__FILE__) . 'rjsf.php';
        ReactJsonSchemaForm::wpdocs_selectively_enqueue_admin_script(Interaction::POST_TYPE);
    }

    /**
     * オーディエンスフォームの表示
     */
    static function show_interaction_form() {
        $ary_init_data = array();
        $formName = Interaction::PARAMETER_DATA;
        $ary_init_data['formName'] = $formName;

        $schema_version = get_post_meta(get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true);
        $latest_form_version = get_post_meta(get_the_ID(), Interaction::META_KEY_VERSION, true);

        // 単一フォームのスキーマとUIスキーマ
        $form = array(
            'id' => 'interaction',
            'schema' => Interaction::get_schema(),
            'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_interaction_uischema', Schema::get_uischema()),
            'formData' => Interaction::get_form_data(get_the_ID(), $schema_version, $latest_form_version),
            'props' => new \stdClass(),
        );
        $ary_init_data['translateString'] = ReactJsonSchemaForm::get_translate_string();
        $ary_init_data['form'] = array($form);
        $nonce_field = wp_nonce_field(
            Interaction::CREDENTIAL_ACTION,
            Interaction::CREDENTIAL_NAME,
            true,
            false
        );

        // require_once plugin_dir_path(__FILE__) . 'rjsf.php';
        ReactJsonSchemaForm::show_json_edit_form($ary_init_data, $nonce_field);
    }

    /**
     * 投稿の保存
     */
    static function save_post_interaction($post_ID, $post, $update) {
        if (isset($_POST[Interaction::CREDENTIAL_NAME]) && check_admin_referer(Interaction::CREDENTIAL_ACTION, Interaction::CREDENTIAL_NAME)) {
            $interaction_data = isset($_POST[Interaction::PARAMETER_DATA]) ? stripslashes($_POST[Interaction::PARAMETER_DATA]) : '';

            if (!empty($interaction_data)) {
                $json_interaction_data = json_decode($interaction_data, true);
                if (!empty($json_interaction_data)) {
                    $form_version = $json_interaction_data[0]['version'];
                    $current_data = get_post_meta($post_ID, Interaction::META_KEY_DATA, true);
                    if (empty($current_data)) {
                        $current_data = array();
                    }
                    $current_data[$form_version] = $json_interaction_data;
                    update_post_meta($post_ID, Interaction::META_KEY_DATA, $current_data);
                    update_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION, Interaction::SCHEMA_VERSION);
                    update_post_meta($post_ID, Interaction::META_KEY_VERSION, $form_version);
                } else {
                    delete_post_meta($post_ID, Interaction::META_KEY_DATA);
                    delete_post_meta($post_ID, Interaction::META_KEY_VERSION);
                    delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
                }
            } else {
                delete_post_meta($post_ID, Interaction::META_KEY_DATA);
                delete_post_meta($post_ID, Interaction::META_KEY_VERSION);
                delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
            }
        }
    }
}
