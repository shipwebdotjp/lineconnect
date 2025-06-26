<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;

/**
 * Definition for the get_my_user_info action.
 */
class SendLineMessageByAudience extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'send_line_message_by_audience';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Send LINE message with audience', lineconnect::PLUGIN_NAME),
				'description' => __('Send LINE message with audience.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_message',
						'name' => 'message',
						'description' => __('LC message id, text or message object', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'slc_audience',
						'name' => 'audience',
						'description' => __('LC Audience id or object', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'object',
						'name' => 'message_args',
						'description' => __('Arguments to insert into the message', lineconnect::PLUGIN_NAME),
						'additionalProperties' => array(
							'type' => 'string',
						),
					),
					array(
						'type' => 'object',
						'name' => 'audience_args',
						'description' => __('Arguments to insert into the audience', lineconnect::PLUGIN_NAME),
						'additionalProperties' => array(
							'type' => 'string',
						),
					),
					array(
						'type' => 'boolean',
						'name' => 'notification_disabled',
						'title' => __('Notification Disabled', lineconnect::PLUGIN_NAME),
						'description' => __('Notification disabled', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => self::class,
				'role'        => 'any',
			);
    }

	/**
	 * オーディエンスからLINEメッセージを送信する
	 * @param mixed message メッセージ
	 * @param int slc_audience_id LCオーディエンスID
	 * @return array LINE APIのレスポンス
	 */
	function send_line_message_by_audience($message, $slc_audience_id, $message_args = null, $audience_args = null, $notification_disabled = false) {
		$message = \Shipweb\LineConnect\Message\LINE\Builder::get_line_message_builder($message, $message_args);
		$audience = \Shipweb\LineConnect\PostType\Audience\Audience::get_lineconnect_audience_from_vary($slc_audience_id, $audience_args);
		if (!empty($audience)) {
			$response = Builder::sendAudienceMessage($audience, $message, $notification_disabled);
			return $response;
		} else {
			return array(
				'success' => false,
				'message' => "<h2>" . __('Error: Invalid audience ID.', lineconnect::PLUGIN_NAME),
			);
		}
	}
}