<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;


/**
 * Definition for the get_my_user_info action.
 */
class GetTextMessage extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'get_text_message';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Get LINE text message', lineconnect::PLUGIN_NAME),
				'description' => __('Get LINE text message.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'body',
						'description' => __('Message body', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
				),
				'namespace'   => self::class,
				'role'        => 'any',
			);
    }


	// LINETEXT メッセージ取得
	function get_text_message($body) {
		return Builder::createTextMessage($body);
	}
}