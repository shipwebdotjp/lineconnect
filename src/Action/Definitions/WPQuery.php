<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class WPQuery extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'WP_Query';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Search posts', lineconnect::PLUGIN_NAME),
				'description' => 'Get posts with WP_Query. ID, type, title, date, excerpt or content, permalink',
				'parameters'  => array(
					array(
						'type'       => 'object',
						'properties' => array(
							'author_name' => array(
								'type'        => 'string',
								'title'       => __('Author Name', lineconnect::PLUGIN_NAME),
								'description' => "Author's user_nicename. NOT display_name nor user_login.",
							),
							's'           => array(
								'type'        => 'string',
								'title'       => __('S', lineconnect::PLUGIN_NAME),
								'description' => 'Search keyword.',
							),
							'p'           => array(
								'type'        => 'integer',
								'title'       => __('P', lineconnect::PLUGIN_NAME),
								'description' => 'Use post ID',
							),
							'name'        => array(
								'type'        => 'string',
								'title'       => __('Name', lineconnect::PLUGIN_NAME),
								'description' => 'Use post slug',
							),
							'order'       => array(
								'type'    => 'string',
								'title'       => __('Order', lineconnect::PLUGIN_NAME),
								'enum'    => array('ASC', 'DESC'),
								'default' => 'DESC',
							),
							'orderby'     => array(
								'type'        => 'string',
								'title'       => __('Orderby', lineconnect::PLUGIN_NAME),
								'description' => 'Sort retrieved posts by parameter.',
								'default'     => 'date',
							),
							'offset'      => array(
								'type'        => 'integer',
								'title'       => __('Offset', lineconnect::PLUGIN_NAME),
								'description' => 'number of post to displace or pass over.',
							),
							'year'        => array(
								'type'        => 'integer',
								'title'       => __('Year', lineconnect::PLUGIN_NAME),
								'description' => '4 digit year',
							),
							'monthnum'    => array(
								'type'        => 'integer',
								'title'       => __('Monthnum', lineconnect::PLUGIN_NAME),
								'description' => 'Month number (from 1 to 12).',
							),
							'day'         => array(
								'type'        => 'integer',
								'title'       => __('Day', lineconnect::PLUGIN_NAME),
								'description' => 'Day of the month (from 1 to 31).',
							),
						),
					),
				),
				'namespace'   => self::class,
				'role'        => 'any',
			);
    }

    // 記事検索
	public function WP_Query($args) {
		// set not overwrite args
		$args['has_password']   = false;      // パスワードが掛かっていない投稿のみ
		$args['post_status']    = 'publish';   // 公開ステータスの投稿のみに限定
		$args['posts_per_page'] = (isset($args['posts_per_page']) && $args['posts_per_page'] <= 5 ? $args['posts_per_page'] : 5);   // 取得する投稿を５件までに制限

		// get post
		$the_query = new \WP_Query($args);
		$posts     = array();
		if ($the_query->have_posts()) {
			while ($the_query->have_posts()) {
				$the_query->the_post();
				$post_object = get_post();
				$post        = array();
				$post['ID']  = $post_object->ID;
				// $post["post_name"] = $post_object->post_name;
				$post['post_type']     = $post_object->post_type;
				$post['post_title']    = get_the_title();
				$post['post_date']     = $post_object->post_date;
				$post['post_modified'] = $post_object->post_modified;
				if ($the_query->found_posts > 1) {
					$post['post_excerpt'] = get_the_excerpt();
				} else {
					// omit conent size to 1024
					$post['post_content'] = strip_tags($post_object->post_content);
					if (mb_strlen($post['post_content']) > 1024) {
						$post['post_content'] = mb_substr($post['post_content'], 0, 1023) . '…';
					}
				}
				$post['permalink'] = get_permalink();

				$posts[] = $post;
			}
		}
		return $posts;
	}
}