<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\PostType\Message\Message as SLCMessage;


/**
 * Definition for the get_my_user_info action.
 */
class GetLineConnectMessage extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'get_line_connect_message';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Get LINE Connect message', lineconnect::PLUGIN_NAME),
				'description' => __('Get LINE Connect message.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_message',
						'name' => 'slc_message_id',
						'description' => __('LC Message id', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'object',
						'name' => 'args',
						'description' => __('Arguments to insert into the message', lineconnect::PLUGIN_NAME),
						'additionalProperties' => array(
							'type' => 'string',
						),
					),
				),
				'namespace'   => self::class,
				'role'        => 'any',
			);
    }

    	/**
	 * Return LINE Connect message
	 */
	function get_line_connect_message($slc_message_id, $args = null) {
		return SLCMessage::get_lineconnect_message($slc_message_id, $args);
	}
}