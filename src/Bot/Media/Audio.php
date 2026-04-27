<?php

namespace Shipweb\LineConnect\Bot\Media;

use Shipweb\LineConnect\Utilities\FileSystem;
use Shipweb\LineConnect\Utilities\MediaManager;
class Audio{

    /**
     * 生成音声を公開用ディレクトリへ保存する（将来用）
     *
     * @param string      $secret_prefix
     * @param string      $line_user_id
     * @param string      $content
     * @param string      $mime_type
     * @param string      $extension
     * @param string      $model_slug
     * @param string|null $file_name
     * @return array{file_path:string,full_path:string,url:string,mime_type:string,duration_ms:int}|false
     */
    public static function saveGeneratedAudio(
        $secret_prefix,
        $line_user_id,
        $content,
        $mime_type = 'audio/mpeg',
        $extension = 'mp3',
        $model_slug = 'tts-1',
        $file_name = null
    ) {
        $saved = MediaManager::saveGeneratedMedia(
            $secret_prefix,
            $line_user_id,
            $content,
            'audio',          // $media_type
            $mime_type,
            $extension,
            $model_slug,
            $file_name
        );

        if ( $saved && ! empty( $saved['full_path'] ) ) {
            $saved['duration_ms'] = self::getAudioDurationMs( $saved['full_path'] );
        }

        return $saved;
    }

    /**
     * 音声ファイルのデュレーション（ミリ秒）を取得する
     *
     * @param string $file_path
     * @return int
     */
    public static function getAudioDurationMs( $file_path ) {
        if ( ! file_exists( $file_path ) ) {
            return 0;
        }

        if ( ! function_exists( 'wp_read_audio_metadata' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        $metadata = wp_read_audio_metadata( $file_path );

        if ( ! empty( $metadata['length'] ) ) {
            return (int) round( $metadata['length'] * 1000 );
        }

        return 0;
    }
}