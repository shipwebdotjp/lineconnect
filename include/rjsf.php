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


class lineconnectRJSF {
	/**
	 * 管理画面（投稿ページ）用にスクリプト読み込み
	 */
	static function wpdocs_selectively_enqueue_admin_script($target_post_type) {
		global $post_type, $pagenow;
		$post_types = array($target_post_type);
		if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
			if (in_array($post_type, $post_types)) {
				$js_file = 'frontend/rjsf/dist/main.js';
				wp_enqueue_script(lineconnect::PLUGIN_PREFIX . 'rjsf', plugins_url($js_file, __DIR__), array('wp-element', 'wp-i18n'), filemtime(plugin_dir_path(__DIR__) . $js_file), true);
				//バリデーション用JSファイル読み込み
				$js_file = 'assets/js/rjsf_validation.js';
				wp_enqueue_script(lineconnect::PLUGIN_PREFIX . 'rjsf' . '-validation', plugins_url($js_file, __DIR__), array('wp-element', 'wp-i18n'), filemtime(plugin_dir_path(__DIR__) . $js_file), true);

				// JavaScriptの言語ファイル読み込み
				wp_set_script_translations(lineconnect::PLUGIN_PREFIX . 'rjsf', lineconnect::PLUGIN_NAME, plugin_dir_path(__DIR__) . 'frontend/rjsf/languages');

				//$css_file = 'frontend/rjsf/dist/bootstrap-4.css';
				//wp_enqueue_style( lineconnect::PLUGIN_PREFIX . $target_post_type. '-css', plugins_url( $css_file, __DIR__ ), array(), filemtime( plugin_dir_path( __DIR__ ) . $css_file ) );
				$override_css_file = 'frontend/rjsf/dist/rjsf-override.css';
				wp_enqueue_style(lineconnect::PLUGIN_PREFIX . 'rjsf' . '-override-css', plugins_url($override_css_file, __DIR__), array(), filemtime(plugin_dir_path(__DIR__) . $override_css_file));
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
}
