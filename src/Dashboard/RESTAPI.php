<?php

/**
 * Lineconnect
 * 管理画面でのダッシュボード REST API
 */

namespace Shipweb\LineConnect\Dashboard;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Core\Stats;

class RESTAPI {
    public static function register_routes() {
        register_rest_route(
            LineConnect::PLUGIN_NAME,
            '/dashboard',
            array(
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_dashboard'],
                'permission_callback' =>
                function () {
                    return current_user_can('manage_options'); // 認証が必要
                },
                'args' => array(
                    'ym' => array(
                        'type' => 'string',
                        'required' => false,
                        'default' => null,
                    ),
                ),
            )
        );
    }

    public static function get_dashboard_permissions_check($request) {
        error_log(print_r('id:' . get_current_user_id()));

        // Check if user is logged in and has manage_options capability
        if (!current_user_can('manage_options')) {
            return false;
        }

        // The nonce check was commented out before - properly verify the nonce now
        $nonce = $request->get_header('X-WP-Nonce');
        error_log(print_r('nonce:' . $nonce));
        $result = wp_verify_nonce($nonce, LineConnect::CREDENTIAL_ACTION__POST);


        error_log(print_r('result:' . $result));

        return $result;
    }

    public static function get_dashboard($request) {
        $ym = $request->get_param('ym');
        $data = Stats::get_monthly_summary($ym);
        return rest_ensure_response($data);
    }
}
