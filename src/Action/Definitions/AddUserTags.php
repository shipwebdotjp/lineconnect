<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class AddUserTags extends AbstractActionDefinition {
	/**
	 * Returns the action key.
	 *
	 * @return string
	 */
	public static function name(): string {
		return 'add_user_tags';
	}

	/**
	 * Returns the action configuration.
	 *
	 * @return array
	 */
	public static function config(): array {
		return array(
			'title'       => __('Add LINE user tags', lineconnect::PLUGIN_NAME),
			'description' => __('Add LINE user tags.', lineconnect::PLUGIN_NAME),
			'parameters'  => array(
				array(
					'type' => 'array',
					'name' => 'tags',
					'description' => __('Array of tags to add. If empty, tags will not be added.', lineconnect::PLUGIN_NAME),
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
	 * LINEユーザータグにタグを追加
	 * 
	 * @param array $tags 追加するタグの配列
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return bool 成功・失敗
	 */
	public function add_user_tags(array $tags, ?string $line_user_id = null, ?string $secret_prefix = null): bool {
		global $wpdb;
		$channel_prefix = $secret_prefix ?? $this->secret_prefix;
		$line_user_id = $line_user_id ?? $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// 現在のタグを取得
		$current_tags = $wpdb->get_var(
			$wpdb->prepare("SELECT tags FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id,  $channel_prefix)
		);

		$tags_array = $current_tags ? (json_decode($current_tags, true) ?: []) : [];

		// タグを追加
		foreach ($tags as $tag) {
			if (!in_array($tag, $tags_array)) {
				$tags_array[] = $tag;
			}
		}
		// error_log(print_r($tags_array, true));
		// データベースを更新
		return $wpdb->update(
			$table_name,
			['tags' => json_encode($tags_array, JSON_UNESCAPED_UNICODE)],
			['line_id' => $line_user_id, 'channel_prefix' => $channel_prefix]
		);
	}
}
