<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class UpdateUserTags extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'update_user_tags';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Update LINE user tags', lineconnect::PLUGIN_NAME),
				'description' => __('Update or delete LINE user tags.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'array',
						'name' => 'tags',
						'description' => __('Array of tags to update. If empty, all tags will be deleted.', lineconnect::PLUGIN_NAME),
						'items' => array(
							'type' => 'string',
						),
						'required' => false,
					),
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
	 * LINEユーザータグを更新
	 * 
	 * @param array $tags タグの配列
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return bool 成功・失敗
	 */
	public function update_user_tags(array $tags, ?string $line_user_id = null, ?string $secret_prefix = null): bool {
		global $wpdb;
		$channel_prefix = $secret_prefix ?? $this->secret_prefix;
		$line_user_id = $line_user_id ?? $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// データベースを更新
		return $wpdb->update(
			$table_name,
			['tags' => json_encode($tags, JSON_UNESCAPED_UNICODE)],
			['line_id' => $line_user_id, 'channel_prefix' => $channel_prefix]
		);
	}

}