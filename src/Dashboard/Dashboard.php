<?php

/**
 * LineConnect
 * ダッシュボード
 */

namespace Shipweb\LineConnect\Dashboard;

use \LineConnect;

class Dashboard {
    const NAME = 'dashboard';
    const TRANSIENT_EXPIRATION = 86400; // 24 hours in seconds

    static function get_update_notice() {
        if (self::my_plugin_check_for_updates()) {
            $latest_release = self::get_latest_release();
            if ($latest_release) {
                $update_available_message = lineconnect::getNotice(
                    sprintf(
                        __('A new version %1$s is available. <a href="%2$s" target="_blank">Download from GitHub</a>', lineconnect::PLUGIN_NAME),
                        $latest_release['tag_name'],
                        $latest_release['html_url']
                    ),
                    lineconnect::NOTICE_TYPE__SUCCESS
                );
                return $update_available_message;
            }
        }
        return '';
    }

    static function my_plugin_check_for_updates() {
        // インストールされているバージョンを取得する
        $installed_version = lineconnect::get_current_plugin_version();

        // 最新のバージョンを取得する
        $latest_version = self::my_plugin_get_latest_version();

        // インストールされているバージョンよりも最新のバージョンが存在する場合は、更新通知を表示する
        if (version_compare($latest_version, $installed_version, '>')) {
            // 更新通知を表示する
            return true;
        }
        return false;
    }

    static function get_latest_release() {
        // Check for cached data in transient
        $transient_key = 'lineconnect_latest_release';
        $release = get_transient($transient_key);

        if (false === $release) {
            // No valid cache found, fetch from GitHub API
            $response = wp_remote_get('https://api.github.com/repos/shipwebdotjp/lineconnect/releases/latest');
            if (is_wp_error($response)) {
                return false;
            }
            $release = json_decode($response['body'], true);

            // Cache the result for 24 hours
            set_transient($transient_key, $release, self::TRANSIENT_EXPIRATION);
        }

        return $release;
    }

    static function my_plugin_get_latest_version() {
        // Check for cached data in transient
        $transient_key = 'lineconnect_latest_version';
        $latest_version = get_transient($transient_key);

        if (false === $latest_version) {
            // No valid cache found, fetch from GitHub API
            $response = wp_remote_get('https://api.github.com/repos/shipwebdotjp/lineconnect/tags');
            if (is_wp_error($response)) {
                return false;
            }

            // Process the response
            $tags = json_decode($response['body'], true);
            if (!empty($tags) && isset($tags[0]['name'])) {
                $latest_version = ltrim($tags[0]['name'], 'vV');

                // Cache the result for 24 hours
                set_transient($transient_key, $latest_version, self::TRANSIENT_EXPIRATION);
            } else {
                return false;
            }
            error_log("from remote:" . $latest_version);
        }

        return $latest_version;
    }
}
