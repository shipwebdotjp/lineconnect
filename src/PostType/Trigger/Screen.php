<?php

/**
 * Lineconnect Trigger Class
 *
 * LINE Connect Trigger
 *
 * @category Components
 * @package  Trigger
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

namespace Shipweb\LineConnect\PostType\Trigger;

use lineconnect;
use lineconnectConst;

use Shipweb\LineConnect\Action\Action;
use Shipweb\LineConnect\Components\ReactJsonSchemaForm;


class Screen {

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
			__('LINE Connect Trigger', lineconnect::PLUGIN_NAME),
			// メニュータイトル：
			__('Triggers', lineconnect::PLUGIN_NAME),
			// 権限：
			// manage_optionsは以下の管理画面設定へのアクセスを許可
			// ・設定 > 一般設定
			// ・設定 > 投稿設定
			// ・設定 > 表示設定
			// ・設定 > ディスカッション
			// ・設定 > パーマリンク設定
			'manage_options',
			// ページを開いたときのURL(slug)：
			'edit.php?post_type=' . lineconnectConst::POST_TYPE_TRIGGER,
			// メニューに紐づく画面を描画するcallback関数：
			false,
			// メニューの位置
			NULL
		);
		// remove_menu_page( lineconnect::SLUG__DM_FORM );
	}

	static function register_meta_box() {
		add_meta_box(
			// チェックボックスのID
			lineconnect::META_KEY__TRIGGER_DATA,
			// チェックボックスのラベル名
			'LINE Connect Trigger',
			// チェックボックスを表示するコールバック関数
			array(self::class, 'show_json_edit_form'),
			// 投稿画面に表示
			lineconnectConst::POST_TYPE_TRIGGER,
			// 投稿画面の下に表示
			'advanced',
			// 優先度(default)
			'default'
		);
	}

	// 管理画面（投稿ページ）用にスクリプト読み込み
	static function wpdocs_selectively_enqueue_admin_script() {
		ReactJsonSchemaForm::wpdocs_selectively_enqueue_admin_script(lineconnectConst::POST_TYPE_TRIGGER);
	}

	/**
	 * JSONスキーマからフォームを表示
	 */
	static function show_json_edit_form() {
		$ary_init_data = array();
		$formName                         = lineconnect::PARAMETER__TRIGGER_DATA;
		$ary_init_data['formName']        = $formName;
		$schema_version = get_post_meta(get_the_ID(), lineconnect::META_KEY__SCHEMA_VERSION, true);
		$formData                         = get_post_meta(get_the_ID(), lineconnect::META_KEY__TRIGGER_DATA, true);
		$subSchema = self::get_trigger_schema();
		$form = array(
			array(
				'id' => 'type',
				'schema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_trigger_type_schema', lineconnectConst::$lineconnect_trigger_type_schema),
				'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_trigger_type_uischema', lineconnectConst::$lineconnect_trigger_type_uischema),
				'formData' => self::get_form_type_data($formData[0] ?? null, $schema_version),
				'props' => new \stdClass(),
			),
			array(
				'id' => 'trigger',
				'schema' => ! empty($formData[0]["type"]) ? $subSchema[$formData[0]["type"]] : new \stdClass(),
				'uiSchema' => apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_trigger_uischema', lineconnectConst::$lineconnect_trigger_uischema),
				'formData' => self::get_form_trigger_data($formData[1] ?? null, $schema_version),
				'props' => new \stdClass(),
			),
		);
		$ary_init_data['subSchema']          = $subSchema;
		$ary_init_data['form']        = $form;
		$ary_init_data['translateString'] = lineconnectConst::$lineconnect_rjsf_translate_string;
		$nonce_field = wp_nonce_field(
			lineconnect::CREDENTIAL_ACTION__TRIGGER,
			lineconnect::CREDENTIAL_NAME__TRIGGER,
			true,
			false
		);
		ReactJsonSchemaForm::show_json_edit_form($ary_init_data, $nonce_field);
	}

	/**
	 * 記事を保存
	 */
	static function save_post_trigger($post_ID, $post, $update) {
		if (isset($_POST[lineconnect::CREDENTIAL_NAME__TRIGGER]) && check_admin_referer(lineconnect::CREDENTIAL_ACTION__TRIGGER, lineconnect::CREDENTIAL_NAME__TRIGGER)) {
			$trigger_data = isset($_POST[lineconnect::PARAMETER__TRIGGER_DATA]) ?  stripslashes($_POST[lineconnect::PARAMETER__TRIGGER_DATA])  : '';

			if (! empty($trigger_data)) {
				$json_trigger_data = json_decode($trigger_data, true);
				if (! empty($json_trigger_data)) {
					update_post_meta($post_ID, lineconnect::META_KEY__TRIGGER_DATA, $json_trigger_data);
					update_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION, lineconnectConst::TRIGGER_SCHEMA_VERSION);
				} else {
					delete_post_meta($post_ID, lineconnect::META_KEY__TRIGGER_DATA);
					delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
				}
			} else {
				delete_post_meta($post_ID, lineconnect::META_KEY__TRIGGER_DATA);
				delete_post_meta($post_ID, lineconnect::META_KEY__SCHEMA_VERSION);
			}
		}
	}

	/**
	 * トリガーのJSONスキーマを返す
	 */
	static function get_trigger_schema() {
		$trigger_schema = lineconnectConst::$lineconnect_trigger_schema;
		Action::build_action_schema_items($trigger_schema['properties']['action']['items']['oneOf']);

		$all_roles = array();
		foreach (wp_roles()->roles as $role_name => $role) {
			$all_roles[] = array(
				'const' => esc_attr($role_name),
				'title' => translate_user_role($role['name']),
			);
		}
		$trigger_schema['definitions']['role']['items']['oneOf'] = $all_roles;

		$all_channels = array();
		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			$all_channels[] = array(
				'const' => $channel['prefix'],
				'title' => $channel['name'],
			);
		}
		if (count($all_channels) == 0) {
			$all_channels[] = array(
				'const' => '',
				'title' => __('Please add channel first', lineconnect::PLUGIN_NAME),
			);
		}
		$trigger_schema['definitions']['secret_prefix']['items']['oneOf'] = $all_channels;


		$trigger_schema_bytype = array();
		foreach (lineconnectConst::$lineconnect_trigger_types as $type => $schema) {
			$trigger_schema_bytype[$type] = $trigger_schema;
			$trigger_schema_bytype[$type]['properties']['triggers']['items'] = $schema;
		}

		return $trigger_schema_bytype;
	}


	/**
	 * Return type data
	 */
	static function get_form_type_data($formData, $schema_version) {
		if (empty($schema_version) || $schema_version == lineconnectConst::TRIGGER_SCHEMA_VERSION) {
			return !empty($formData) ? $formData : new \stdClass();
		}
		// if old schema veersion, migrate and return
	}

	/** 
	 * Return trigger data
	 */
	static function get_form_trigger_data($formData, $schema_version) {
		if (empty($schema_version) || $schema_version == lineconnectConst::TRIGGER_SCHEMA_VERSION) {
			return !empty($formData) ? $formData : new \stdClass();
		}
		// if old schema veersion, migrate and return
	}
}
