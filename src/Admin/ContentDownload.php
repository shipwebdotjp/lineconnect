<?php

namespace Shipweb\LineConnect\Admin;

use Shipweb\LineConnect\Core\LineConnect;

class ContentDownload {

    /**
     * コンテンツダウンロード
     */
    static function download_content_page() {
        nocache_headers();

        if (! current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', lineconnect::PLUGIN_NAME));
        }

        if (empty($_GET['file'])) {
            wp_die(__('No file specified!', lineconnect::PLUGIN_NAME));
        }

        // URLデコードしてスラッシュを復元
        $relative_path = wp_unslash($_GET['file']);
        $relative_path = urldecode($relative_path);

        // ディレクトリトラバーサルの「../」を即座に弾く
        if (strpos($relative_path, '../') !== false || strpos($relative_path, '..\\') !== false) {
            wp_die(__('Nice try!', lineconnect::PLUGIN_NAME));
        }

        // アップロードディレクトリ
        $upload_dir = wp_upload_dir();
        $root_dir_path = $upload_dir['basedir'] . '/lineconnect';

        // 連結してrealpath
        $full_path = $root_dir_path . '/' . $relative_path;
        $real_path = realpath($full_path);

        // ルートディレクトリ外を拒否
        if ($real_path === false || strpos($real_path, $root_dir_path) !== 0) {
            wp_die(__('Invalid file path!', lineconnect::PLUGIN_NAME));
        }

        if (!file_exists($real_path)) {
            wp_die(__('File does not exist!' . $real_path, lineconnect::PLUGIN_NAME));
        }

        $type = wp_check_filetype($real_path);

        // Clear any output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: ' . ($type['type'] ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($real_path));
        readfile($real_path);
        exit;
    }
}
