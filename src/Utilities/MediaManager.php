<?php

namespace Shipweb\LineConnect\Utilities;

use Shipweb\LineConnect\Utilities\FileSystem;

class MediaManager {

    
    /**
     * 生成メディアファイルを公開用ディレクトリへ保存する（共通処理）
     *
     * 保存先は uploads/lineconnect/generated/{channel_prefix}/{Y/m}/{media_type}/
     *
     * @param string      $secret_prefix
     * @param string      $content        バイナリ or テキストコンテンツ
     * @param string      $media_type     サブディレクトリ名 例: 'image', 'audio'
     * @param string      $mime_type      例: 'image/png', 'audio/mpeg'
     * @param string      $extension      拡張子 例: 'png', 'mp3'
     * @param string      $model_slug     ファイル名プレフィックス 例: 'gpt-image-2', 'tts-1'
     * @param string|null $file_name      指定時はサニタイズして使用
     * @return array{file_path:string,full_path:string,url:string,mime_type:string}|false
     */
    public static function saveGeneratedMedia(
        $secret_prefix,
        $content,
        $media_type,
        $mime_type,
        $extension,
        $model_slug,
        $file_name = null
    ) {
        $upload_dir = wp_upload_dir();
        if (empty($upload_dir['basedir']) || empty($upload_dir['baseurl'])) {
            return false;
        }

        $channel_prefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $secret_prefix);
        if (empty($channel_prefix)) {
            $channel_prefix = '_none';
        }

        $relative_dir    = 'lineconnect/generated/' . $channel_prefix . '/' . gmdate('Y/m') . '/' . $media_type;
        $target_dir_path = FileSystem::make_lineconnect_dir($relative_dir, false);
        if (! $target_dir_path) {
            return false;
        }

        if (empty($file_name)) {
            $timestamp = gmdate('Ymd-His');
            try {
                $random = bin2hex(random_bytes(4));
            } catch (\Exception $e) {
                $random = wp_generate_password(8, false, false);
            }
            $file_name = $model_slug . '-' . $timestamp . '-' . $random . '.' . ltrim($extension, '.');
        } else {
            $file_name = str_replace("\0", '', $file_name);
            $file_name = basename($file_name);
            $file_name = str_replace(array('..', '/', '\\'), '', $file_name);
            if (! preg_match('/^[A-Za-z0-9._-]+$/', $file_name)) {
                return false;
            }
            $expected_ext = '.' . ltrim($extension, '.');
            if (! str_ends_with($file_name, $expected_ext)) {
                return false;
            }
        }

        $full_path = trailingslashit($target_dir_path) . $file_name;

        // パストラバーサル防止
        $real_target_dir = realpath($target_dir_path);
        $real_full_path  = realpath(dirname($full_path));
        if ($real_target_dir === false || $real_full_path === false || strpos($real_full_path, $real_target_dir) !== 0) {
            $parent_dir  = dirname($full_path);
            if (! file_exists($parent_dir)) {
                return false;
            }
            $real_parent = realpath($parent_dir);
            if ($real_parent === false || strpos($real_parent, $real_target_dir) !== 0) {
                return false;
            }
        }

        if (file_put_contents($full_path, $content) === false) {
            return false;
        }

        $relative_path = $relative_dir . '/' . $file_name;
        $url           = trailingslashit($upload_dir['baseurl']) . $relative_path;

        return array(
            'file_path' => $relative_path,
            'full_path' => $full_path,
            'url'       => $url,
            'mime_type' => $mime_type,
        );
    }
}