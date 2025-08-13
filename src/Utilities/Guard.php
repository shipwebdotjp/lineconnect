<?php

namespace Shipweb\LineConnect\Utilities;

use Shipweb\LineConnect\Core\LineConnect;

class Guard {

    /**
     * Ajaxの認証を行う関数
     * @param string $action アクション名
     * @param string $nonce_name nonceの名前
     * @param string $nonce nonceの値
     * @return bool $result 認証結果
     */
    public static function check_ajax_referer($action, $nonce_name = 'nonce') {
        if (! is_user_logged_in()) {
            return array(
                'result' => 'failed',
                'success' => array(),
                'error' => array(__('You are not logged in.', lineconnect::PLUGIN_NAME),),
            );
        }
        // 特権管理者、管理者、編集者、投稿者の何れでもない場合は無視
        if (! is_super_admin() && ! current_user_can('administrator') && ! current_user_can('editor') && ! current_user_can('author')) {
            return array(
                'result' => 'failed',
                'success' => array(),
                'error' => array(__('You do not have permission to send messages.', lineconnect::PLUGIN_NAME),),
            );
        }
        // nonceで設定したcredentialをPOST受信していない場合は無視
        if (! isset($_POST[$nonce_name]) || ! $_POST[$nonce_name] || ! check_ajax_referer($action, $nonce_name, false)) {
            return array(
                'result' => 'failed',
                'success' => array(),
                'error' => array(__('Nonce is not set or invalid.', lineconnect::PLUGIN_NAME),),
            );
        }


        return array(
            'result' => 'success',
            'success' => array(__('Nonce verified successfully.', lineconnect::PLUGIN_NAME),),
            'error' => array(),
        );
    }
}
