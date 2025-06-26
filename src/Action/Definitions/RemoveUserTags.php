<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class RemoveUserTags extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'remove_user_tags';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Remove LINE user tags', lineconnect::PLUGIN_NAME),
				'description' => __('Remove LINE user tags.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'array',
						'name' => 'tags',
						'description' => __('Array of tags to remove. If empty, tags will not be removed.', lineconnect::PLUGIN_NAME),
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
	 * LINEユーザータグからタグを削除
	 * 
	 * @param array $tags 削除するタグの配列
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return bool 成功・失敗
	 */
	function remove_user_tags(array $tags, ?string $line_user_id = null, ?string $secret_prefix = null): bool {
		global $wpdb;
		$channel_prefix = $secret_prefix ?? $this->secret_prefix;
		$line_user_id = $line_user_id ?? $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		$user_exists = $wpdb->get_var(
			$wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id, $channel_prefix)
		);

		if (!$user_exists) {
			return false;
		}

		// 現在のタグを取得
		$current_tags = $wpdb->get_var(
			$wpdb->prepare("SELECT tags FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id,  $channel_prefix)
		);

		$tags_array = json_decode($current_tags ?? '[]', true) ?: [];

		// タグを削除
		$initial_count = count($tags_array);
		foreach ($tags as $tag) {
			$tags_array = array_diff($tags_array, [$tag]);
		}

		// データベースを更新
		$result = $wpdb->update(
			$table_name,
			['tags' => json_encode(array_values($tags_array), JSON_UNESCAPED_UNICODE)],
			['line_id' => $line_user_id, 'channel_prefix' => $channel_prefix]
		);

		return $result !== false;
	}

}