<?php

/*
	Plugin Name: LINE Connect
	Plugin URI: https://blog.shipweb.jp/lineconnect/
	Description: Account link between WordPress user ID and LINE ID
	Version: 4.3.5
	Author: shipweb
	Author URI: https://blog.shipweb.jp/about
	License: GPLv3
*/

/*
	Copyright 2020 shipweb (email : shipwebdotjp@gmail.com)
	https://www.gnu.org/licenses/gpl-3.0.txt

*/

use Shipweb\LineConnect\Core\LineConnect;

if (!defined('ABSPATH')) {
	exit; // 直接アクセスを防ぐ
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// require_once plugin_dir_path(__FILE__) . 'src/Core/LineConnect.php'; // namespace導入前の暫定処置
// require_once plugin_dir_path(__FILE__) . 'include/richmenu.php';
// require_once plugin_dir_path(__FILE__) . 'include/setting.php';
// require_once plugin_dir_path(__FILE__) . 'include/publish.php';
// require_once plugin_dir_path(__FILE__) . 'include/message.php';
// require_once plugin_dir_path(__FILE__) . 'include/bulk_message.php';
// require_once plugin_dir_path(__FILE__) . 'include/comment.php';
// require_once plugin_dir_path(__FILE__) . 'include/shortcodes.php';
// require_once plugin_dir_path(__FILE__) . 'include/admin.php';
// require_once plugin_dir_path(__FILE__) . 'include/const.php';
// require_once plugin_dir_path(__FILE__) . 'include/botlog.php';
// require_once plugin_dir_path(__FILE__) . 'include/openai.php';
// require_once plugin_dir_path(__FILE__) . 'include/functions.php';
// require_once plugin_dir_path( __FILE__ ) . 'include/dashboard.php';
// require_once plugin_dir_path(__FILE__) . 'include/dm.php';
// require_once plugin_dir_path(__FILE__) . 'include/action.php';
// require_once plugin_dir_path(__FILE__) . 'include/trigger.php';
// require_once plugin_dir_path(__FILE__) . 'include/audience.php';
// require_once plugin_dir_path(__FILE__) . 'include/util.php';
// require_once plugin_dir_path(__FILE__) . 'include/slc_message.php';
// require_once plugin_dir_path(__FILE__) . 'include/schedule.php';

// プラグインのメインクラスを呼び出す
function load_lineconnect_plugin() {
	// namespace導入後
	// $plugin = Shipweb\LineConnect\Core\LineConnect::get_instance();
	// namespace導入前の暫定処置
	$plugin = LineConnect::get_instance();
	// $test = new \Shipweb\LineConnect\Scenario\Admin();

}
add_action('plugins_loaded', 'load_lineconnect_plugin');

// プラグイン有効化時に呼び出し
register_activation_hook(__FILE__, array(LineConnect::class, 'pluginActivation'));
