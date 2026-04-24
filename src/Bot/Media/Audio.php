<?php

namespace Shipweb\LineConnect\Bot\Media;

use Shipweb\LineConnect\Utilities\FileSystem;
use Shipweb\LineConnect\Utilities\MediaManager;
class Audio{

    /**
     * 生成音声を公開用ディレクトリへ保存する（将来用）
     *
     * @param string      $secret_prefix
     * @param string      $content
     * @param string      $mime_type
     * @param string      $extension
     * @param string      $model_slug
     * @param string|null $file_name
     * @return array{file_path:string,full_path:string,url:string,mime_type:string}|false
     */
    public static function saveGeneratedAudio(
        $secret_prefix,
        $content,
        $mime_type = 'audio/mpeg',
        $extension = 'mp3',
        $model_slug = 'tts-1',
        $file_name = null
    ) {
        return MediaManager::saveGeneratedMedia(
            $secret_prefix,
            $content,
            'audio',          // $media_type
            $mime_type,
            $extension,
            $model_slug,
            $file_name
        );
    }
}