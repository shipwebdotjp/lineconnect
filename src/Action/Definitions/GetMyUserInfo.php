<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class GetMyUserInfo extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'get_my_user_info';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return [
            'title'       => __('Get my user information', lineconnect::PLUGIN_NAME),
            'description' => 'Get my information. ID, name, email, link status, etc.',
            'namespace'   => self::class,
            'role'        => 'any',
        ];
    }

    // 自分のユーザー情報取得
	public function get_my_user_info() {
		// メタ情報からLINEユーザーIDでユーザー検索
		$user = lineconnect::get_wpuser_from_line_id($this->secret_prefix, $this->event->source->userId);
		if ($user) { // ユーザーが見つかればすでに連携されているということ
			return array(
				'linkstatus'      => 'linked',
				'user_id'         => $user->ID,
				'user_login'      => $user->user_login,
				'user_email'      => $user->user_email,
				'user_nicename'   => $user->user_nicename,
				'display_name'    => $user->display_name,
				'user_registered' => $user->user_registered,
			);
		} else {
			$line_id_row  = \Shipweb\LineConnect\Utilities\LineId::line_id_row($this->event->source->userId, $this->secret_prefix);
			if ($line_id_row) {
				$profile = json_decode($line_id_row['profile'], true);
				return array(
					'linkstatus'      => 'not_linked',
					'display_name'    => $profile['displayName'],
				);
			}
			return array(
				'error'   => 'not_linked',
				'message' => 'You are not linked to WordPress',
			);
		}
	}
}
