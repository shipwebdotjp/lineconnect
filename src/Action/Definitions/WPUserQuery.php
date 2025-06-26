<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class WPUserQuery extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'WP_User_Query';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Search users', lineconnect::PLUGIN_NAME),
				'description' => 'Get users information with WP_User_Query. ID, name, email, link status, etc.',
				'parameters'  => array(
					array(
						'type'       => 'object',
						'properties' => array(
							'role'         => array(
								'type'        => 'string',
								'title'       => __('Role', lineconnect::PLUGIN_NAME),
								'description' => 'A comma-separated list of role names that users must match to be included in results.',
							),
							'search'       => array(
								'type'        => 'string',
								'title'       => __('Search', lineconnect::PLUGIN_NAME),
								'description' => 'Search keyword.',
							),
							'include'      => array(
								'type'        => 'array',
								'title'       => __('Include', lineconnect::PLUGIN_NAME),
								'description' => 'List of user id to be included.',
								'items'       => array(
									'type' => 'integer',
									'title'       => __('Item', lineconnect::PLUGIN_NAME),
								),
							),
							'order'        => array(
								'type'    => 'string',
								'title'       => __('Order', lineconnect::PLUGIN_NAME),
								'enum'    => array('ASC', 'DESC'),
								'default' => 'DESC',
							),
							'orderby'      => array(
								'type'        => 'string',
								'title'       => __('Orderby', lineconnect::PLUGIN_NAME),
								'description' => 'Sort retrieved users by parameter.',
								'default'     => 'login',
							),
							'offset'       => array(
								'type'        => 'integer',
								'title'       => __('Offset', lineconnect::PLUGIN_NAME),
								'description' => 'Offset the returned results.',
							),
							'meta_key'     => array(
								'type'        => 'string',
								'title'       => __('Meta Key', lineconnect::PLUGIN_NAME),
								'description' => 'Custom field key',
							),
							'meta_value'   => array(
								'type'        => 'string',
								'title'       => __('Meta Value', lineconnect::PLUGIN_NAME),
								'description' => 'Custom field value',
							),
							'meta_compare' => array(
								'type'        => 'string',
								'title'       => __('Meta Compare', lineconnect::PLUGIN_NAME),
								'description' => 'Operator to test the ‘meta_value‘. ',
							),
						),
					),
				),
				'namespace'   => self::class,
				'role'        => 'administrator',
			);
    }

    // ユーザー検索
	function WP_User_Query($args) {
		$args['number'] = (isset($args['number']) && $args['number'] <= 20 ? $args['number'] : 20); // 取得する投稿を５件までに制限
		$args['fields'] = 'all_with_meta';

		// get user
		$the_query = new \WP_User_Query($args);
		$users     = array();
		if (! empty($the_query->get_results())) {
			foreach ($the_query->get_results() as $user) {
				$user_data = array();

				$user_data['ID']              = $user->ID;
				$user_data['user_login']      = $user->user_login;
				$user_data['user_email']      = $user->user_email;
				$user_data['user_nicename']   = $user->user_nicename;
				$user_data['display_name']    = $user->display_name;
				$user_data['user_registered'] = $user->user_registered;
				$user_meta_line               = $user->get(lineconnect::META_KEY__LINE);
				$user_data[lineconnect::META_KEY__LINE] = $user_meta_line;
				if ($user_meta_line && isset($this->secret_prefix) && $user_meta_line[$this->secret_prefix]) {
					$user_data['linkstatus'] = 'linked';
				}
				// if meta_key is included in args, get meta_value and include in user_data
				if (! empty($args['meta_key'])) {
					$user_data[$args['meta_key']] = $user->get($args['meta_key']);
				}
				$users[] = $user_data;
			}
		}
		return $users;
	}
}