<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class GetUserMeta extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'get_user_meta';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Get user meta', lineconnect::PLUGIN_NAME),
				'description' => __('Get WordPress user meta value', lineconnect::PLUGIN_NAME),
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
				),
				'namespace'   => self::class,
				'role'        => 'administrator',
			);
    }

	/**
	 * ユーザーメタを取得
	 * @param int $user_id WordPressユーザーID
	 * @param string $key メタキー
	 * @return mixed メタの値
	 */
	public function get_user_meta($user_id, $key) {
		return get_user_meta($user_id, $key, true);
	}
}