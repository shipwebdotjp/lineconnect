<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class GetUserTags extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'get_user_tags';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Get LINE user tags', lineconnect::PLUGIN_NAME),
				'description' => __('Get LINE user tags.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => self::class,
				'role'        => 'administrator',
			);
    }

	/**
	 * LINEユーザータグを取得
	 * 
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array タグ
	 */
	public function get_user_tags(?string $line_user_id = null, ?string $secret_prefix = null): array {
		global $wpdb;
		$channel_prefix = $secret_prefix ?? $this->secret_prefix;
		$line_user_id = $line_user_id ?? $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// タグを取得
		$tags = $wpdb->get_var(
			$wpdb->prepare("SELECT tags FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id,  $channel_prefix)
		);

		if ($tags === null) {
			return [];
		}

		return json_decode($tags, true) ?: [];
	}
}