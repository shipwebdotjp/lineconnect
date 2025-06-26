<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;

/**
 * Definition for the get_my_user_info action.
 */
class SendLineMessage extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'send_line_message';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Send LINE message', lineconnect::PLUGIN_NAME),
				'description' => __('Send LINE message with Audience and Channel.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'message',
						'description' => __('LC message id, message text or message object', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user id of event source.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('Channel. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => self::class,
				'role'        => 'any',
			);
    }

	function send_line_message($message, $line_user_id = null, $secret_prefix = null) {
		$message = \Shipweb\LineConnect\Message\LINE\Builder::get_line_message_builder($message);
		$line_user_id = $line_user_id ? $line_user_id : $this->event->source->userId;
		if (!preg_match('/^U[a-f0-9]{32}$/', $line_user_id)) {
			return array(
				'success' => false,
				'message' => "<h2>" . __('Error: Invalid line user ID.', lineconnect::PLUGIN_NAME),
			);
		}
        if ( !isset($secret_prefix) ) {
            $secret_prefix = $this->secret_prefix;
        }
        if ( empty($secret_prefix) ) {
            return array(
                'success' => false,
                'message' => "<h2>" . __('Error: Secret prefix is not set.', lineconnect::PLUGIN_NAME),
            );
        }
		$channel = lineconnect::get_channel($secret_prefix);
		$response = Builder::sendPushMessage($channel, $line_user_id, $message);
		return $response;
	}
}