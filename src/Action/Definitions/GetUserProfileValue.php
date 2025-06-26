<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class GetUserProfileValue extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'get_user_profile_value';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Get LINE user profile value', lineconnect::PLUGIN_NAME),
				'description' => __('Get value from LINE user profile', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'key',
						'description' => __('Profile key to get', lineconnect::PLUGIN_NAME),
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
	 * LINEユーザープロフィールに保存されている値を取得
	 * 
	 * @param string $key キー
	 * @param string $line_user_id LINEユーザーID
	 * @param string $secret_prefix チャネルシークレットの先頭4文字
	 * @return mixed|null 値（存在しない場合は null）
	 */
	public function get_user_profile_value($key, $line_user_id = null, $secret_prefix = null) {
		global $wpdb;
		$channel_prefix = $secret_prefix ? $secret_prefix : $this->secret_prefix;
		$line_user_id = $line_user_id ? $line_user_id : $this->event->source->userId;

		$table_name = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// プロフィール情報を取得
		$profile_json = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT profile FROM $table_name WHERE line_id = %s AND channel_prefix = %s",
				$line_user_id,
				$channel_prefix
			)
		);

		if ($profile_json === null) {
			return null;
		}

		$profile = json_decode($profile_json, true);

		// ドット記法で階層を分解
		$keys = explode('.', $key);
		$value = $profile;

		// 階層を順に探索
		foreach ($keys as $k) {
			if (!is_array($value) || !isset($value[$k])) {
				return null;
			}
			$value = $value[$k];
		}

		return $value;
	}
}