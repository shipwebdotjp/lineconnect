<?php

namespace Shipweb\LineConnect\Utilities;

class FileSystem {

    /**
     * lineconnect用の指定されたフォルダをアップロードディレクトリに作成する
     * @param string $dir_name 作成するディレクトリ名
     * @return string $dir_path 作成されたディレクトリのパス
     */
    public static function make_lineconnect_dir($dir_name) {
        $upload_dir = \wp_upload_dir();
        $root_dir_path = $upload_dir['basedir'] . '/lineconnect';

        if (! file_exists($root_dir_path)) {
            if (! mkdir($root_dir_path, 0777, true)) {
                return false;
            }
            if (file_put_contents($root_dir_path . '/index.php', '<?php http_response_code(404);') === false) {
                return false;
            }
        }
        // 既存サーバーの古い deny from all を上書きするため毎回書き込む
        $htaccess_path = $root_dir_path . '/.htaccess';
        $htaccess_content = 'Options -Indexes';
        if (! file_exists($htaccess_path) || file_get_contents($htaccess_path) !== $htaccess_content) {
            if (file_put_contents($htaccess_path, $htaccess_content) === false) {
                return false;
            }
        }

        $target_dir_path = $root_dir_path . '/' . $dir_name;

        if (! file_exists($target_dir_path)) {
            if (! mkdir($target_dir_path, 0777, true)) {
                return false;
            }
            if (file_put_contents($target_dir_path . '/index.php', '<?php http_response_code(404);') === false) {
                return false;
            }
        }
        // 既存サーバーの古い deny from all を上書きするため毎回書き込む
        $target_htaccess_path = $target_dir_path . '/.htaccess';
        if (! file_exists($target_htaccess_path) || file_get_contents($target_htaccess_path) !== $htaccess_content) {
            if (file_put_contents($target_htaccess_path, $htaccess_content) === false) {
                return false;
            }
        }

        return $target_dir_path;
    }

    /**
     * lineconnect用の指定されたフォルダにアップロードされたファイルのフルパスを取得する
     * @param string $file_path ファイルパス
     * @return string $file_path ファイルパス
     */
    public static function get_lineconnect_file_path($file_path) {
        // Defensive check: reject paths containing directory traversal segments
        if (strpos($file_path, '..') !== false) {
            return false;
        }

        $upload_dir = \wp_upload_dir();
        $root_dir_path = $upload_dir['basedir'] . '/lineconnect';
        $full_path = $root_dir_path . '/' . ltrim($file_path, '/');
        if (file_exists($full_path)) {
            return $full_path;
        } else {
            return false;
        }
    }

    /**
     * lineconnect用の指定されたフォルダにアップロードされたファイルの公開URLを取得する
     * @param string $file_path ファイルパス
     * @return string|false ファイルの公開URL、存在しない場合は false
     */
    public static function get_lineconnect_file_url($file_path) {
        // Defensive check: reject paths containing directory traversal segments
        if (strpos($file_path, '..') !== false) {
            return false;
        }

        $upload_dir = \wp_upload_dir();
        $root_dir_path = $upload_dir['basedir'] . '/lineconnect';
        $full_path = $root_dir_path . '/' . ltrim($file_path, '/');

        if (! file_exists($full_path)) {
            return false;
        }

        return \trailingslashit($upload_dir['baseurl']) . 'lineconnect/' . ltrim($file_path, '/');
    }

    /**
     * ファイルをbase64エンコードして返す関数
     * @param string $file_path ファイルパス
     * @return string $base64 ファイルのbase64エンコード
     */
    public static function get_base64_encoded_file($file_path) {
        if (! file_exists($file_path) || ! is_readable($file_path)) {
            return '';
        }
        // ファイルの内容を取得
        $file_content = file_get_contents($file_path);

        // MIME TYPEを検出
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($file_content);
        // または別の方法
        // $mime_type = mime_content_type($file_path);

        // Base64エンコード
        $base64_file = base64_encode($file_content);

        return  'data:' . $mime_type . ';base64,' . $base64_file;
    }
}
