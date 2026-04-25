<?php

namespace Shipweb\LineConnect\Bot\Media;

use Shipweb\LineConnect\Utilities\FileSystem;
use Shipweb\LineConnect\Utilities\MediaManager;
class Image {


    /**
     * 生成画像を公開用ディレクトリへ保存する
     *
     * @param string      $secret_prefix
     * @param string      $content
     * @param string      $mime_type
     * @param string      $extension
     * @param string|null $file_name
     * @return array{file_path:string,full_path:string,url:string,mime_type:string}|false
     */
    public static function saveGeneratedImage(
        $secret_prefix,
        $content,
        $mime_type = 'image/png',
        $extension = 'png',
        $file_name = null
    ) {
        return MediaManager::saveGeneratedMedia(
            $secret_prefix,
            $content,
            'image',          // $media_type
            $mime_type,
            $extension,
            'gpt-image-2',    // $model_slug
            $file_name
        );
    }

    /**
     * オリジナル画像からサムネイルを生成する
     *
     * @param string $original_full_path
     * @param int    $max_edge
     * @param int    $quality
     * @return array{file_path:string,full_path:string,url:string}|false
     */
    public static function generateThumbnail($original_full_path, $secret_prefix, $max_edge = 1024, $quality = 60) {
        $editor = wp_get_image_editor($original_full_path);
        if (is_wp_error($editor)) {
            return false;
        }

        $size = $editor->get_size();
        if (! $size) {
            return false;
        }

        if ($size['width'] > $max_edge || $size['height'] > $max_edge) {
            $editor->resize($max_edge, $max_edge, false);
        }

        $editor->set_quality($quality);

        $path_info = pathinfo($original_full_path);
        $channel_prefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $secret_prefix);
        if (empty($channel_prefix)) {
            $channel_prefix = '_none';
        }

        $thumb_dir = FileSystem::make_lineconnect_dir(
            'generated/' . $channel_prefix . '/' . gmdate('Y/m') . '/image/thumbnails',
            false
        );
        if (! $thumb_dir) {
            return false;
        }

        $thumb_filename  = $path_info['filename'] . '.jpg';
        $thumb_full_path = trailingslashit($thumb_dir) . $thumb_filename;

        $saved = $editor->save($thumb_full_path, 'image/jpeg');
        if (is_wp_error($saved)) {
            return false;
        }

        $actual_thumb_path = $saved['path'];
        $upload_dir        = wp_upload_dir();
        $relative_path     = str_replace(trailingslashit($upload_dir['basedir']), '', $actual_thumb_path);
        $url               = trailingslashit($upload_dir['baseurl']) . $relative_path;

        return array(
            'file_path' => $relative_path,
            'full_path' => $actual_thumb_path,
            'url'       => $url,
        );
    }
}