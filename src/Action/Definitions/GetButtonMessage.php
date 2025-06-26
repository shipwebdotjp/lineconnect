<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;

/**
 * Definition for the get_my_user_info action.
 */
class GetButtonMessage extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'get_button_message';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Get LC notify message', lineconnect::PLUGIN_NAME),
				'description' => __('Get LC notify message.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type'        => 'string',
						'name' =>  'title',
						'description' => __('Message title', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'body',
						'description' => __('Message body', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'thumb',
						'description' => __('Thumbnail url', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'type',
						'description' => __('Button action type', lineconnect::PLUGIN_NAME),
						'oneOf'       => array(
							array(
								'const' => 'message',
								'title' => __('Message', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'postback',
								'title' => __('Postback', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'uri',
								'title' => __('URI', lineconnect::PLUGIN_NAME),
							),
						),
					),
					array(
						'type' => 'string',
						'name' => 'label',
						'description' => __('Label', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'link',
						'description' => __('Link', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'displayText',
						'description' => __('Display Text', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'object',
						'name' => 'atts',
						'description' => __('Attributes', lineconnect::PLUGIN_NAME),
						'additionalProperties' => array(
							'type' => 'string',
						),
					),
				),
				'namespace'   => self::class,
				'role'        => 'any',
			);
    }

    // LC 通知メッセージ取得
	function get_button_message($title, $body, $thumb, $type, $label, $link, $displayText = null, $atts = null) {
		$message = Builder::createFlexMessage(
			array(
				'title' => $title,
				'body'  => $body,
				'thumb' => $thumb,
				'type'  => $type,
				'label' => $label,
				'link'  => $link,
				'displayText' => $displayText,
			),
			$atts
		);
		return $message;
	}
}