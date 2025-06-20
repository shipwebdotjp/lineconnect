<?php

namespace Shipweb\LineConnect\Utilities;

class FileSystem {

    /**
     * lineconnect用の指定されたフォルダをアップロードディレクトリに作成する
     * @param string $dir_name 作成するディレクトリ名
     * @return string $dir_path 作成されたディレクトリのパス
     */
    public static function make_lineconnect_dir($dir_name, $deny_from_all = true) {
        $root_dir_path = WP_CONTENT_DIR . '/uploads/lineconnect';
        // check if root dir exists
        if (! file_exists($root_dir_path)) {
            // make root dir
            if (mkdir($root_dir_path, 0777, true)) {
                // put .htaccess file to root dir
                $htaccess_file_path    = $root_dir_path . '/.htaccess';
                $htaccess_file_content = 'deny from all';
                file_put_contents($htaccess_file_path, $htaccess_file_content);
            }
        }
        $target_dir_path = $root_dir_path . '/' . $dir_name;
        // check if target dir exists
        if (! file_exists($target_dir_path)) {
            // make target dir
            if (mkdir($target_dir_path, 0777, true)) {
                $htaccess_file_path    = $target_dir_path . '/.htaccess';
                if ($deny_from_all) {
                    $htaccess_file_content = 'deny from all';
                } else {
                    $htaccess_file_content = 'allow from all';
                }
                file_put_contents($htaccess_file_path, $htaccess_file_content);
                return $target_dir_path;
            } else {
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
        $root_dir_path = WP_CONTENT_DIR . '/uploads/lineconnect';
        $full_path = $root_dir_path . '/' . $file_path;
        if (file_exists($full_path)) {
            return $full_path;
        } else {
            return false;
        }
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
