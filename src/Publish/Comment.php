<?php

/**
 * Lineconnect Comment Class
 *
 * 記事にコメントが付いた時にLINE通知を行う
 *
 * @category Components
 * @package  Comment
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

namespace Shipweb\LineConnect\Publish;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Message\LINE\Builder;


class Comment {
	static function comment_post_callback($comment_ID, $comment_approved) {
		if ($comment_approved === 1) {
			$comment = get_comment($comment_ID);
			self::line_send_comment($comment);
		}
	}

	static function approve_comment_callback($new_status, $old_status, $comment) {
		if ($old_status != $new_status) {
			if ($new_status == 'approved') {
				self::line_send_comment($comment);
			}
		}
	}

	static function line_send_comment($comment) {

		$post_ID = $comment->comment_post_ID;   //コメントが投稿された投稿ID
		$post = get_post($post_ID);
		$title = sprintf(__('New comment on %s', lineconnect::PLUGIN_NAME), sanitize_text_field($post->post_title));
		$body = sprintf(__('Comment by %s', lineconnect::PLUGIN_NAME), sanitize_text_field($comment->comment_author)) . "\r\n";
		$body .= preg_replace("/( |　|\n|\r)/", "", strip_tags(sanitize_text_field(strip_shortcodes($comment->comment_content))));
		if (mb_strlen($body) > 500) {
			// 投稿の本文の先頭500文字取得
			$body = mb_substr($body, 0, 499) . "…";
		}

		//空BODYでは送れないため、本文がない場合はスペースを送信
		if (mb_strlen($body) == 0) {
			$body = " ";
		}
		// 投稿のURLを取得
		$link = get_comment_link($comment);
		$thumb = get_the_post_thumbnail_url($post_ID);
		if (substr($thumb, 0, 5) != "https") {  //httpsから始まらない場合はサムネなしとする
			$thumb = "";
		}
		//通知用の本文を作成（400文字に切り詰め）
		$alttext = sprintf(__('New comment on %s', lineconnect::PLUGIN_NAME), $title);
		$alttext .= "\r\n" . $body . "\r\n" . $link;
		if (mb_strlen($alttext) > 400) {
			$alttext = mb_substr($alttext, 0, 399) . "…";
		}

		//メッセージ関連を読み込み
		// require_once(plugin_dir_path(__FILE__) . 'message.php');
		$link_label = lineconnect::get_option('comment_read_label');;
		$flexMessage = Builder::createFlexMessage(
			["title" => $title, "body" => $body, "thumb" => $thumb, "type" => "uri", "label" => $link_label, "link" => $link]
		);

		$author_id = $post->post_author;
		//$comment_author_id = $comment->user_id;

		foreach (lineconnect::get_all_channels() as $channel_id => $channel) {
			Builder::sendMessageWpUser($channel, $author_id, $flexMessage);
			//Builder::sendMessageWpUser($channel, $comment_author_id, $flexMessage);
		}
	}
}
