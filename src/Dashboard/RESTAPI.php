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
 
         // Check if user is logged in and has manage_options capability
         if (!current_user_can('manage_options')) {
             return new \WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', array('status' => 403));
         }
 
         // The nonce check was commented out before - properly verify the nonce now
         $nonce = $request->get_header('X-WP-Nonce');
         $result = wp_verify_nonce($nonce, LineConnect::CREDENTIAL_ACTION__POST);
 
 
         return $result;
     }

    public static function get_dashboard($request) {
        $ym = $request->get_param('ym');
        $data = Stats::get_monthly_summary($ym);
        return rest_ensure_response($data);
    }
}
