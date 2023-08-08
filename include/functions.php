<?php

/**
 * Lineconnect Functions Class
 *
 * Functions Class
 *
 * @category Components
 * @package  Functions
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

class lineconnectFunctions {
	public $lineUserId;
	public $secret_prefix;

	public function __construct(string $lineUserId, string $secret_prefix) {
		$this->lineUserId = $lineUserId;
		$this->secret_prefix = $secret_prefix;
	}

	//自分のユーザー情報取得
	function get_my_user_info() {
		//メタ情報からLINEユーザーIDでユーザー検索
		$user = lineconnect::get_wpuser_from_line_id($this->secret_prefix, $this->lineUserId);
		if ($user) { //ユーザーが見つかればすでに連携されているということ
			return ["linkstatus" => "linked", "user_id" => $user->ID, "user_login" => $user->user_login, "user_email" => $user->user_email, "user_nicename" => $user->user_nicename, "display_name" => $user->display_name, "user_registered" => $user->user_registered];
		} else {
			return ["error" => "not_linked", "message" => "You are not linked to WordPress"];
		}
	}

	//現在日時取得
	function get_the_current_datetime() {
		return ["datetime" => date(DATE_RFC2822)];
	}

	//記事検索
	function WP_Query($args) {
		// set not overwrite args
		$args["has_password"] = false;		//パスワードが掛かっていない投稿のみ
		$args["post_status"] = "publish";	//公開ステータスの投稿のみに限定
		$args["posts_per_page"] = (isset($args["posts_per_page"]) && $args["posts_per_page"] <= 5 ? $args["posts_per_page"] : 5);	// 取得する投稿を５件までに制限

		//get post
		$the_query = new WP_Query($args);
		$posts = [];
		if ($the_query->have_posts()) {
			while ($the_query->have_posts()) {
				$the_query->the_post();
				$post_object = get_post();
				$post = [];
				$post["ID"] = $post_object->ID;
				//$post["post_name"] = $post_object->post_name;
				$post["post_type"] = $post_object->post_type;
				$post["post_title"] = get_the_title();
				$post["post_date"] = $post_object->post_date;
				$post["post_modified"] = $post_object->post_modified;
				if ($the_query->found_posts > 1) {
					$post["post_excerpt"] = get_the_excerpt();
				} else {
					//omit conent size to 1024
					$post["post_content"] = strip_tags($post_object->post_content);
					if (mb_strlen($post["post_content"]) > 1024) {
						$post["post_content"] = mb_substr($post["post_content"], 0, 1023) . "…";
					}
				}
				$post["permalink"] = get_permalink();

				$posts[] = $post;
			}
		}
		return $posts;
	}
}
