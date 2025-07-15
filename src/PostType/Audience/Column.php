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

class Column {

    /**
     * ダウンロードカラム追加
     */
    public static function add_download_column($columns) {
        $new_columns = array();

        // タイトルの後にステータスカラムを挿入
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['download'] = __('Download', lineconnect::PLUGIN_NAME);
            }
        }

        return $new_columns;
    }

    /**
     * ダウンロードカラムの表示
     */
    public static function add_download_column_content($column_name, $post_id) {
        if ($column_name == 'download') {
            $audience = get_post_meta($post_id, Audience::META_KEY_DATA, true);
            if (!empty($audience)) {
                echo '<a href="' . esc_url(admin_url('admin-post.php?action=' . lineconnect::SLUG__AUDIENCE_DOWNLOAD . '&audience_id=' . $post_id)) . '" >' . __('CSV Download', lineconnect::PLUGIN_NAME) . '</a>';
            }
        }
    }

    /**
     * ダウンロードメニュー追加
     */
    /*
    static function set_download_menu() {
        add_options_page(
            __('LINE Connect Audience Download', lineconnect::PLUGIN_NAME),
            __('Download Audiences', lineconnect::PLUGIN_NAME),
            'manage_options',
            lineconnect::SLUG__AUDIENCE_DOWNLOAD,
            array(self::class, 'download_audience_page')
        );

        remove_submenu_page(
            'options-general.php',
            lineconnect::SLUG__AUDIENCE_DOWNLOAD
        );
    }
*/
    /**
     * CSVダウンロード
     */
    static function download_audience_page() {
        nocache_headers();

        if (! current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', lineconnect::PLUGIN_NAME));
        }

        $audience_id = isset($_GET['audience_id']) ? intval($_GET['audience_id']) : 0;
        $line_user_ids = Audience::get_lineconnect_audience($audience_id);
        $csv_data = array(); // secret_prefix, line_user_id 
        $csv = '';
        foreach ($line_user_ids as $secret_prefix => $recepient_item) {
            if ($recepient_item['type'] == 'multicast' || $recepient_item['type'] == 'push') {
                foreach ($recepient_item['line_user_ids'] as $line_user_id) {
                    $csv_data[] = array($line_user_id);
                }
            }
        }

        if (!empty($csv_data)) {
            foreach ($csv_data as $row) {
                $csv .= implode(',', $row) . "\n";
            }
        }
        $filename = 'lineconnect_audience_id_' . $audience_id . '_' . date('YmdHis') . '.csv';

        // Clear any output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($csv));
        echo $csv;
        exit;
    }
}
