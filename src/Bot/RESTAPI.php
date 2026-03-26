<?php

namespace Shipweb\LineConnect\Bot;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Bot\Account;
use Shipweb\LineConnect\Bot\Webhook as WebhookHandler;

class RESTAPI {

    public static function register_routes() {
        // register_rest_route must be called when rest_api_init runs.
        // LineConnect::init() already hooks Bot\RESTAPI::register_routes to rest_api_init,
        // so here we should call register_rest_route directly. Wrapping another
        // add_action('rest_api_init', ...) inside this method would register the
        // routes too late (after the current rest_api_init has already fired),
        // resulting in routes not being available for the current request.

        register_rest_route(
            'lineconnect/v1',
            '/gotologin',
            array(
                'methods'             => 'GET',
                'callback'            => array( Account::class, 'goto_login_page' ),
                'permission_callback' => '__return_true', // 未ログインでもアクセス可
            )
        );

        register_rest_route(
            'lineconnect/v1',
            '/accountlink',
            array(
                'methods'             => 'GET',
                'callback'            => array( Account::class, 'account_link_page' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'lineconnect/v1',
            '/webhook',
            array(
                'methods'             => 'POST',
                'callback'            => array( WebhookHandler::class, 'rest_callback' ),
                'permission_callback' => '__return_true',
            )
        );
    }
}
