<?php

/**
 * Bot.phpから呼び出されるファイルの処理系
 */

namespace Shipweb\LineConnect\Bot;

use Shipweb\LineConnect\Core\LineConnect;

class File {
    const MIME_MAP = array(
        'video/3gpp2'                          => '3g2',
        'video/3gp'                            => '3gp',
        'video/3gpp'                           => '3gp',
        'application/x-compressed'             => '7zip',
        'audio/x-acc'                          => 'aac',
        'audio/ac3'                            => 'ac3',
        'application/postscript'               => 'ai',
        'audio/x-aiff'                         => 'aif',
        'audio/aiff'                           => 'aif',
        'audio/x-au'                           => 'au',
        'video/x-msvideo'                      => 'avi',
        'video/msvideo'                        => 'avi',
        'video/avi'                            => 'avi',
        'application/x-troff-msvideo'          => 'avi',
        'application/macbinary'                => 'bin',
        'application/mac-binary'               => 'bin',
        'application/x-binary'                 => 'bin',
        'application/x-macbinary'              => 'bin',
        'image/bmp'                            => 'bmp',
        'image/x-bmp'                          => 'bmp',
        'image/x-bitmap'                       => 'bmp',
        'image/x-xbitmap'                      => 'bmp',
        'image/x-win-bitmap'                   => 'bmp',
        'image/x-windows-bmp'                  => 'bmp',
        'image/ms-bmp'                         => 'bmp',
        'image/x-ms-bmp'                       => 'bmp',
        'application/bmp'                      => 'bmp',
        'application/x-bmp'                    => 'bmp',
        'application/x-win-bitmap'             => 'bmp',
        'application/cdr'                      => 'cdr',
        'application/coreldraw'                => 'cdr',
        'application/x-cdr'                    => 'cdr',
        'application/x-coreldraw'              => 'cdr',
        'image/cdr'                            => 'cdr',
        'image/x-cdr'                          => 'cdr',
        'zz-application/zz-winassoc-cdr'       => 'cdr',
        'application/mac-compactpro'           => 'cpt',
        'application/pkix-crl'                 => 'crl',
        'application/pkcs-crl'                 => 'crl',
        'application/x-x509-ca-cert'           => 'crt',
        'application/pkix-cert'                => 'crt',
        'text/css'                             => 'css',
        'text/x-comma-separated-values'        => 'csv',
        'text/comma-separated-values'          => 'csv',
        'application/vnd.msexcel'              => 'csv',
        'application/x-director'               => 'dcr',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/x-dvi'                    => 'dvi',
        'message/rfc822'                       => 'eml',
        'application/x-msdownload'             => 'exe',
        'video/x-f4v'                          => 'f4v',
        'audio/x-flac'                         => 'flac',
        'video/x-flv'                          => 'flv',
        'image/gif'                            => 'gif',
        'application/gpg-keys'                 => 'gpg',
        'application/x-gtar'                   => 'gtar',
        'application/x-gzip'                   => 'gzip',
        'application/mac-binhex40'             => 'hqx',
        'application/mac-binhex'               => 'hqx',
        'application/x-binhex40'               => 'hqx',
        'application/x-mac-binhex40'           => 'hqx',
        'text/html'                            => 'html',
        'image/x-icon'                         => 'ico',
        'image/x-ico'                          => 'ico',
        'image/vnd.microsoft.icon'             => 'ico',
        'text/calendar'                        => 'ics',
        'application/java-archive'             => 'jar',
        'application/x-java-application'       => 'jar',
        'application/x-jar'                    => 'jar',
        'image/jp2'                            => 'jp2',
        'video/mj2'                            => 'jp2',
        'image/jpx'                            => 'jp2',
        'image/jpm'                            => 'jp2',
        'image/jpeg'                           => 'jpeg',
        'image/pjpeg'                          => 'jpeg',
        'application/x-javascript'             => 'js',
        'application/json'                     => 'json',
        'text/json'                            => 'json',
        'application/vnd.google-earth.kml+xml' => 'kml',
        'application/vnd.google-earth.kmz'     => 'kmz',
        'text/x-log'                           => 'log',
        'audio/x-m4a'                          => 'm4a',
        'audio/mp4'                            => 'm4a',
        'application/vnd.mpegurl'              => 'm4u',
        'audio/midi'                           => 'mid',
        'application/vnd.mif'                  => 'mif',
        'video/quicktime'                      => 'mov',
        'video/x-sgi-movie'                    => 'movie',
        'audio/mpeg'                           => 'mp3',
        'audio/mpg'                            => 'mp3',
        'audio/mpeg3'                          => 'mp3',
        'audio/mp3'                            => 'mp3',
        'video/mp4'                            => 'mp4',
        'video/mpeg'                           => 'mpeg',
        'application/oda'                      => 'oda',
        'audio/ogg'                            => 'ogg',
        'video/ogg'                            => 'ogg',
        'application/ogg'                      => 'ogg',
        'font/otf'                             => 'otf',
        'application/x-pkcs10'                 => 'p10',
        'application/pkcs10'                   => 'p10',
        'application/x-pkcs12'                 => 'p12',
        'application/x-pkcs7-signature'        => 'p7a',
        'application/pkcs7-mime'               => 'p7c',
        'application/x-pkcs7-mime'             => 'p7c',
        'application/x-pkcs7-certreqresp'      => 'p7r',
        'application/pkcs7-signature'          => 'p7s',
        'application/pdf'                      => 'pdf',
        'application/octet-stream'             => 'pdf',
        'application/x-x509-user-cert'         => 'pem',
        'application/x-pem-file'               => 'pem',
        'application/pgp'                      => 'pgp',
        'application/x-httpd-php'              => 'php',
        'application/php'                      => 'php',
        'application/x-php'                    => 'php',
        'text/php'                             => 'php',
        'text/x-php'                           => 'php',
        'application/x-httpd-php-source'       => 'php',
        'image/png'                            => 'png',
        'image/x-png'                          => 'png',
        'application/powerpoint'               => 'ppt',
        'application/vnd.ms-powerpoint'        => 'ppt',
        'application/vnd.ms-office'            => 'ppt',
        'application/msword'                   => 'doc',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/x-photoshop'              => 'psd',
        'image/vnd.adobe.photoshop'            => 'psd',
        'audio/x-realaudio'                    => 'ra',
        'audio/x-pn-realaudio'                 => 'ram',
        'application/x-rar'                    => 'rar',
        'application/rar'                      => 'rar',
        'application/x-rar-compressed'         => 'rar',
        'audio/x-pn-realaudio-plugin'          => 'rpm',
        'application/x-pkcs7'                  => 'rsa',
        'text/rtf'                             => 'rtf',
        'text/richtext'                        => 'rtx',
        'video/vnd.rn-realvideo'               => 'rv',
        'application/x-stuffit'                => 'sit',
        'application/smil'                     => 'smil',
        'text/srt'                             => 'srt',
        'image/svg+xml'                        => 'svg',
        'application/x-shockwave-flash'        => 'swf',
        'application/x-tar'                    => 'tar',
        'application/x-gzip-compressed'        => 'tgz',
        'image/tiff'                           => 'tiff',
        'font/ttf'                             => 'ttf',
        'text/plain'                           => 'txt',
        'text/x-vcard'                         => 'vcf',
        'application/videolan'                 => 'vlc',
        'text/vtt'                             => 'vtt',
        'audio/x-wav'                          => 'wav',
        'audio/wave'                           => 'wav',
        'audio/wav'                            => 'wav',
        'application/wbxml'                    => 'wbxml',
        'video/webm'                           => 'webm',
        'image/webp'                           => 'webp',
        'audio/x-ms-wma'                       => 'wma',
        'application/wmlc'                     => 'wmlc',
        'video/x-ms-wmv'                       => 'wmv',
        'video/x-ms-asf'                       => 'wmv',
        'font/woff'                            => 'woff',
        'font/woff2'                           => 'woff2',
        'application/xhtml+xml'                => 'xhtml',
        'application/excel'                    => 'xl',
        'application/msexcel'                  => 'xls',
        'application/x-msexcel'                => 'xls',
        'application/x-ms-excel'               => 'xls',
        'application/x-excel'                  => 'xls',
        'application/x-dos_ms_excel'           => 'xls',
        'application/xls'                      => 'xls',
        'application/x-xls'                    => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-excel'             => 'xlsx',
        'application/xml'                      => 'xml',
        'text/xml'                             => 'xml',
        'text/xsl'                             => 'xsl',
        'application/xspf+xml'                 => 'xspf',
        'application/x-compress'               => 'z',
        'application/x-zip'                    => 'zip',
        'application/zip'                      => 'zip',
        'application/x-zip-compressed'         => 'zip',
        'application/s-compressed'             => 'zip',
        'multipart/x-zip'                      => 'zip',
        'text/x-scriptzsh'                     => 'zsh',
    );

    // メッセージコンテント取得
    public static function getMessageContent($secret_prefix, $messageId, $userId) {
        //get $access_token, $channelSecret;
        $channel = lineconnect::get_channel($secret_prefix);
        if (!$channel) {
            return false;
        }
        $access_token = $channel['channel-access-token'];
        $channelSecret = $channel['channel-secret'];



        // Bot作成
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
        $bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channelSecret));

        // コンテンツ取得
        $response = $bot->getMessageContent($messageId);
        if ($response->getHTTPStatus() == 200) {
            // レスポンスからバイナリデータを取得
            $content = $response->getRawBody();
            // レスポンスのContent-Typeヘッダーからバイナリデータのファイル形式を取得
            $contentType = $response->getHeader('Content-Type');

            // 取得したファイル形式から適切なファイル拡張子を選択
            $file_extention = self::get_file_extention($contentType);

            // make file name from message id and file extention
            $file_name = $messageId . '.' . $file_extention;
            // set user directory
            $user_dir = substr($userId, 1, 4);
            // make directory
            $target_dir_path = \Shipweb\LineConnect\Utilities\FileSystem::make_lineconnect_dir($user_dir);
            if ($target_dir_path) {
                // make file path
                $file_path = $target_dir_path . '/' . $file_name;
                // write file
                file_put_contents($file_path, $content);
                // return file path
                return $user_dir . '/' . $file_name;
            }
        }
        return false;
    }

    // MIME type to file Extension
    public static function get_file_extention($mime_type) {
        return self::MIME_MAP[$mime_type] ?? 'bin';
    }

    // update message
    public static function update_message_filepath($logId, $file_path) {
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS;
        // get row from log table
        $row = $wpdb->get_row("SELECT * FROM {$table_name} WHERE id = {$logId}");
        if ($row) {
            // message column is JSON string, so decode to object
            $message = json_decode($row->message);
            // set file path
            $message->file_path = $file_path;
            // update message column
            return $wpdb->update($table_name, array('message' => json_encode($message)), array('id' => $logId));
        }
        return false;
    }
}
