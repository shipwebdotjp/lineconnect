<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;

/**
 * Definition for the get_my_user_info action.
 */
class GetRawMessage extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'get_raw_message';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Get LINE message from Raw JSON', lineconnect::PLUGIN_NAME),
				'description' => __('Get LINE message from Raw JSON.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'json',
						'description' => __('Single raw Message JSON object', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => self::class,
				'role'        => 'any',
			);
    }
	/**
	 * LINEメッセージのJSONを受け取って、構築したLINEメッセージを返す
	 * 
	 * @param $raw LINEメッセージのJSON
	 * @return MessageBuilder
	 */
	function get_raw_message($raw) {
		//if raw is string to JSON
		if (is_string($raw)) {
			$raw = json_decode($raw, true);
		}
		return 	Builder::createRawMessage($raw);
	}
}