<?php

namespace Shipweb\LineConnect\Core;

class UserProvider {
	public static function get_userdata( $user_id ) {
		$use_alternative = apply_filters( LineConnect::FILTER_PREFIX . 'use_alternative_user_provider', false );
		if ( $use_alternative ) {
			return apply_filters( LineConnect::FILTER_PREFIX . 'get_userdata_alternative', $user_id );
		}
		return apply_filters( LineConnect::FILTER_PREFIX . 'get_userdata', get_userdata( $user_id ), $user_id );
	}

	public static function get_user_meta( $user_id, $key = '', $single = false ) {
		$use_alternative = apply_filters( LineConnect::FILTER_PREFIX . 'use_alternative_user_provider', false );
		if ( $use_alternative ) {
			// error_log("Userprovider get_user_meta_alternative:".$user_id.":". $key);
			return apply_filters( LineConnect::FILTER_PREFIX . 'get_user_meta_alternative', $user_id, $key, $single );
		}
		// error_log("Userprovider get_user_meta (default):".$user_id.":". $key);

		return apply_filters( LineConnect::FILTER_PREFIX . 'get_user_meta', get_user_meta( $user_id, $key, $single ), $user_id, $key, $single );
	}

	public static function update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
		$use_alternative = apply_filters( LineConnect::FILTER_PREFIX . 'use_alternative_user_provider', false );
		if ( $use_alternative ) {
			return apply_filters( LineConnect::FILTER_PREFIX . 'update_user_meta_alternative', $user_id, $meta_key, $meta_value, $prev_value );
		}
		return apply_filters( LineConnect::FILTER_PREFIX . 'update_user_meta', update_user_meta( $user_id, $meta_key, $meta_value, $prev_value ), $user_id, $meta_key, $meta_value, $prev_value );
	}

	public static function delete_user_meta( $user_id, $meta_key, $meta_value = '' ) {
		$use_alternative = apply_filters( LineConnect::FILTER_PREFIX . 'use_alternative_user_provider', false );
		if ( $use_alternative ) {
			return apply_filters( LineConnect::FILTER_PREFIX . 'delete_user_meta_alternative', $user_id, $meta_key, $meta_value );
		}
		return apply_filters( LineConnect::FILTER_PREFIX . 'delete_user_meta', delete_user_meta( $user_id, $meta_key, $meta_value ), $user_id, $meta_key, $meta_value );
	}

	public static function get_current_user_id() {
		$use_alternative = apply_filters( LineConnect::FILTER_PREFIX . 'use_alternative_user_provider', false );
		if ( $use_alternative ) {
			return apply_filters( LineConnect::FILTER_PREFIX . 'get_current_user_id_alternative', null );
		}
		// error_log( 'UserProvider::get_current_user_id: default get_current_user_id = ' . get_current_user_id() );
		return apply_filters( LineConnect::FILTER_PREFIX . 'get_current_user_id', get_current_user_id() );
	}

	public static function is_user_id_valid( $user_id ) {
		$use_alternative = apply_filters( LineConnect::FILTER_PREFIX . 'use_alternative_user_provider', false );
		if ( $use_alternative ) {
			return apply_filters( LineConnect::FILTER_PREFIX . 'is_user_id_valid_alternative', $user_id );
		}
		return apply_filters( LineConnect::FILTER_PREFIX . 'is_user_id_valid', is_numeric( $user_id ) && get_userdata( $user_id ) !== false, $user_id );
	}

	/**
	 * Retrieve users by given roles who have LINE meta.
	 *
	 * @param array $roles
	 * @return $userids[]
	 */
	public static function get_linked_userids_by_roles( array $roles = array() ): array {
		$use_alternative = apply_filters( LineConnect::FILTER_PREFIX . 'use_alternative_user_provider', false );
		if ( $use_alternative ) {
			return apply_filters( LineConnect::FILTER_PREFIX . 'get_linked_userids_by_roles_alternative', $roles );
		}

		$args = array(
			'meta_query' => array(
				array(
					'key'     => lineconnect::META_KEY__LINE,
					'compare' => 'EXISTS',
				),
			),
			'role__in'   => $roles,
			'fields'     => 'ID',
		);

		$user_query = new \WP_User_Query( $args );
		$ids        = $user_query->get_results();
		return $ids ? $ids : array();
	}
}
