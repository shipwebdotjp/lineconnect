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

use lineconnect;
use lineconnectConst;
use lineconnectUtil;
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
            'edit.php?post_type=' . lineconnectConst::POST_TYPE_AUDIENCE,
            false,
            NULL
        );
    }

    /**
     * メタボックスの登録
     */
    static function register_meta_box() {
        add_meta_box(
            lineconnect::META_KEY__AUDIENCE_DATA,
            __('LINE Connect Audience', lineconnect::PLUGIN_NAME),
            array(self::class, 'show_audience_form'),
            lineconnectConst::POST_TYPE_AUDIENCE,
            'advanced',
            'default'
        );
    }

    /**
     * 管理画面用スクリプトの読み込み
     */
    static function wpdocs_selectively_enqueue_admin_script() {
        // require_once plugin_dir_path(__FILE__) . 'rjsf.php';
        ReactJsonSchemaForm::wpdocs_selectively_enqueue_admin_script(lineconnectConst::POST_TYPE_AUDIENCE);
    }

    /**
     * オーディエンスフォームの表示
     */
    static function show_audience_form() {
        $ary_init_data = array();
        $formName = lineconnect::PARAMETER__AUDIENCE_DATA;
        $ary_init_data['formName'] = $formName;

        $schema_version = get_post_meta(get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true);
        $formData = get_post_meta(get_the_ID(), lineconnect::META_KEY__AUDIENCE_DATA, true);

        // 単一フォームのスキーマとUIスキーマ
        $form = array(
            'id' => 'audience',
            'schema' => Audience::get_audience_schema(),
            'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_audience_uischema', lineconnectConst::$lineconnect_audience_uischema),
            'formData' => !empty($formData[0]) ? Audience::get_form_audience_data($formData[0], $schema_version) : new \stdClass(),
            'props' => new \stdClass(),
        );
        $ary_init_data['translateString'] = lineconnectConst::$lineconnect_rjsf_translate_string;
        $ary_init_data['form'] = array($form);
        $nonce_field = wp_nonce_field(
            lineconnect::CREDENTIAL_ACTION__AUDIENCE,
            lineconnect::CREDENTIAL_NAME__AUDIENCE,
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
        if (isset($_POST[lineconnect::CREDENTIAL_NAME__AUDIENCE]) && check_admin_referer(lineconnect::CREDENTIAL_ACTION__AUDIENCE, lineconnect::CREDENTIAL_NAME__AUDIENCE)) {
            $audience_data = isset($_POST[lineconnect::PARAMETER__AUDIENCE_DATA]) ? stripslashes($_POST[lineconnect::PARAMETER__AUDIENCE_DATA]) : '';

            if (!empty($audience_data)) {
                $json_audience_data = json_decode($audience_data, true);
                if (!empty($json_audience_data)) {
                    update_post_meta($post_ID, lineconnect::META_KEY__AUDIENCE_DATA, $json_audience_data);
                    update_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION, lineconnectConst::AUDIENCE_SCHEMA_VERSION);
                } else {
                    delete_post_meta($post_ID, lineconnect::META_KEY__AUDIENCE_DATA);
                    delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
                }
            } else {
                delete_post_meta($post_ID, lineconnect::META_KEY__AUDIENCE_DATA);
                delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
            }
        }
    }
}
