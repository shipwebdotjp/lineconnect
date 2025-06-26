<?php

/**
 * Lineconnect React JSON Schema Form Class
 *
 * LINE Connect RJSF
 *
 * @category Components
 * @package  RJSF
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

namespace Shipweb\LineConnect\Components;

use Shipweb\LineConnect\Core\LineConnect;


class ReactJsonSchemaForm {
	/**
	 * 管理画面（投稿ページ）用にスクリプト読み込み
	 */
	static function wpdocs_selectively_enqueue_admin_script($target_post_type) {
		global $post_type, $pagenow;
		$post_types = array($target_post_type);
		if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
			if (in_array($post_type, $post_types)) {
				$js_file = 'frontend/rjsf/dist/main.js';
				wp_enqueue_script(lineconnect::PLUGIN_PREFIX . 'rjsf', lineconnect::plugins_url($js_file), array('wp-element', 'wp-i18n'), filemtime(lineconnect::getRootDir() . $js_file), true);
				//バリデーション用JSファイル読み込み
				$js_file = 'assets/js/rjsf_validation.js';
				wp_enqueue_script(lineconnect::PLUGIN_PREFIX . 'rjsf' . '-validation', lineconnect::plugins_url($js_file), array('wp-element', 'wp-i18n'), filemtime(lineconnect::getRootDir() . $js_file), true);

				// JavaScriptの言語ファイル読み込み
				wp_set_script_translations(lineconnect::PLUGIN_PREFIX . 'rjsf', lineconnect::PLUGIN_NAME, lineconnect::getRootDir() . 'frontend/rjsf/languages');

				$override_css_file = 'frontend/rjsf/dist/rjsf-override.css';
				wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'rjsf' . '-override-css', lineconnect::plugins_url($override_css_file), array(), filemtime(lineconnect::getRootDir() . $override_css_file));
			}
		}
	}

	/**
	 * JSONスキーマからフォームを表示
	 */
	static function show_json_edit_form($ary_init_data, $nonce_field) {
		$inidata = json_encode($ary_init_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

		$hidden_json_filed = '<input type="hidden" id="' . $ary_init_data['formName'] . '" name="' . $ary_init_data['formName'] . '">';


		echo $nonce_field;
		echo $hidden_json_filed;
		echo <<< EOM
		<div id="app"></div>
		<script>
		var lc_initdata = {$inidata};
		/*
		document.addEventListener("DOMContentLoaded", (event) => {
			var origin_data = {};
			lc_initdata.form.map((form, index) => {
				if(Object.keys(form.formData).length !==0 ){
					origin_data[index] = form.formData;
				}
			});
			document.getElementById(lc_initdata['formName']).value = JSON.stringify(origin_data);
		});
		*/
		</script>
EOM;
	}

	public static function get_translate_string() {
		return apply_filters(
			lineconnect::FILTER_PREFIX . 'lineconnect_rjsf_translate_string',
			array(
				'Item'                                   => __('Item', lineconnect::PLUGIN_NAME),
				/** Missing items reason, used by ArrayField */
				'Missing items definition'               => __('Missing items definition', lineconnect::PLUGIN_NAME),
				/** Yes label, used by BooleanField */
				'Yes'                                    => __('Yes', lineconnect::PLUGIN_NAME),
				/** No label, used by BooleanField */
				'No'                                     => __('No', lineconnect::PLUGIN_NAME),
				/** Close label, used by ErrorList */
				'Close'                                  => __('Close', lineconnect::PLUGIN_NAME),
				/** Errors label, used by ErrorList */
				'Errors'                                 => __('Errors', lineconnect::PLUGIN_NAME),
				/** New additionalProperties string default value, used by ObjectField */
				'New Value'                              => __('New Value', lineconnect::PLUGIN_NAME),
				/** Add button title, used by AddButton */
				'Add'                                    => __('Add', lineconnect::PLUGIN_NAME),
				/** Add button title, used by AddButton */
				'Add Item'                               => __('Add Item', lineconnect::PLUGIN_NAME),
				/** Copy button title, used by IconButton */
				'Copy'                                   => __('Copy', lineconnect::PLUGIN_NAME),
				/** Move down button title, used by IconButton */
				'Move down'                              => __('Move down', lineconnect::PLUGIN_NAME),
				/** Move up button title, used by IconButton */
				'Move up'                                => __('Move up', lineconnect::PLUGIN_NAME),
				/** Remove button title, used by IconButton */
				'Remove'                                 => __('Remove', lineconnect::PLUGIN_NAME),
				/** Now label, used by AltDateWidget */
				'Now'                                    => __('Now', lineconnect::PLUGIN_NAME),
				/** Clear label, used by AltDateWidget */
				'Clear'                                  => __('Clear', lineconnect::PLUGIN_NAME),
				/** Aria date label, used by DateWidget */
				'Select a date'                          => __('Select a date', lineconnect::PLUGIN_NAME),
				/** File preview label, used by FileWidget */
				'Preview'                                => __('Preview', lineconnect::PLUGIN_NAME),
				/** Decrement button aria label, used by UpDownWidget */
				'Decrease value by 1'                    => __('Decrease value by 1', lineconnect::PLUGIN_NAME),
				/** Increment button aria label, used by UpDownWidget */
				'Increase value by 1'                    => __('Increase value by 1', lineconnect::PLUGIN_NAME),
				// Strings with replaceable parameters
				/** Unknown field type reason, where %1 will be replaced with the type as provided by SchemaField */
				'Unknown field type %1'                  => __('Unknown field type %1', lineconnect::PLUGIN_NAME),
				/** Option prefix, where %1 will be replaced with the option index as provided by MultiSchemaField */
				'Option %1'                              => __('Option %1', lineconnect::PLUGIN_NAME),
				/** Option prefix, where %1 and %2 will be replaced by the schema title and option index, respectively as provided by
				 * MultiSchemaField
				 */
				'%1 option %2'                           => __('%1 option %2', lineconnect::PLUGIN_NAME),
				/** Key label, where %1 will be replaced by the label as provided by WrapIfAdditionalTemplate */
				'%1 Key'                                 => __('%1 Key', lineconnect::PLUGIN_NAME),
				// Strings with replaceable parameters AND/OR that support markdown and html
				/** Invalid object field configuration as provided by the ObjectField */
				'Invalid %1 object field configuration: <em>%2</em>.' => __('Invalid %1 object field configuration: <em>%2</em>.', lineconnect::PLUGIN_NAME),
				/** Unsupported field schema, used by UnsupportedField */
				'Unsupported field schema.'              => __('Unsupported field schema.', lineconnect::PLUGIN_NAME),
				/** Unsupported field schema, where %1 will be replaced by the idSchema.$id as provided by UnsupportedField */
				'Unsupported field schema for field <code>%1</code>.' => __('Unsupported field schema for field <code>%1</code>.', lineconnect::PLUGIN_NAME),
				/** Unsupported field schema, where %1 will be replaced by the reason string as provided by UnsupportedField */
				'Unsupported field schema: <em>%1</em>.' => __('Unsupported field schema: <em>%1</em>.', lineconnect::PLUGIN_NAME),
				/** Unsupported field schema, where %1 and %2 will be replaced by the idSchema.$id and reason strings, respectively,
				 * as provided by UnsupportedField
				 */
				'Unsupported field schema for field <code>%1</code>: <em>%2</em>.' => __('Unsupported field schema for field <code>%1</code>: <em>%2</em>.', lineconnect::PLUGIN_NAME),
				/** File name, type and size info, where %1, %2 and %3 will be replaced by the file name, file type and file size as
				 * provided by FileWidget
				 */
				'<strong>%1</strong> (%2, %3 bytes)'     => __('<strong>%1</strong> (%2, %3 bytes)', lineconnect::PLUGIN_NAME),
			)
		);
	}
}
