<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class UpdateUserProfile extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'update_user_profile';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Update LINE user profile', lineconnect::PLUGIN_NAME),
				'description' => __('Update or delete value in LINE user profile', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'key',
						'description' => __('Profile key to update', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'value',
						'description' => __('Profile value. If empty, key will be deleted.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
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
	 * LINEユーザープロフィールに値を保存
	 * @param string $key キー
	 * @param mixed $value
	 * @param string $line_user_id LINEユーザーID
	 * @param string $secret_prefix チャネルシークレットの先頭4文字
	 */
	public function update_user_profile($key, $value, $line_user_id = null, $secret_prefix = null) {
		global $wpdb;
		$channel_prefix = $secret_prefix ? $secret_prefix : $this->secret_prefix;
		$line_user_id = $line_user_id ? $line_user_id : $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// 現在のプロフィールを取得
		$current_profile = $wpdb->get_var(
			$wpdb->prepare("SELECT profile FROM $table_name WHERE line_id = %s AND channel_prefix = %s", $line_user_id,  $channel_prefix)
		);

		$profile_array = json_decode($current_profile ?? '{}', true);

		if (! \Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($value)) {
			$profile_array[$key] = $value;
		} else {
			unset($profile_array[$key]);
		}

		// データベースを更新
		return $wpdb->update(
			$table_name,
			['profile' => json_encode($profile_array, JSON_UNESCAPED_UNICODE)],
			['line_id' => $line_user_id]
		);
	}
}