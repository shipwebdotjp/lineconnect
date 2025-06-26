<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class UpdateUserMeta extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'update_user_meta';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Update user meta', lineconnect::PLUGIN_NAME),
				'description' => __('Update or delete WordPress user meta value', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'integer',
						'name' => 'user_id',
						'description' => __('WordPress user ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'key',
						'description' => __('Meta key', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'value',
						'description' => __('Meta value. If empty, meta will be deleted.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
				),
				'namespace'   => self::class,
				'role'        => 'administrator',
			);
    }

    /**
	 * ユーザーメタを更新
	 * @param int $user_id WordPressユーザーID
	 * @param string $key メタキー
	 * @param mixed $value メタの値
	 * @return bool 成功・失敗
	 */
	public function update_user_meta($user_id, $key, $value) {
		if (!\Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($value)) {
			return update_user_meta($user_id, $key, $value);
		} else {
			return delete_user_meta($user_id, $key);
		}
	}
}