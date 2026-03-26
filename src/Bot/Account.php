<?php

/**
 * Bot.phpから呼び出されるアカウント関連処理
 */

namespace Shipweb\LineConnect\Bot;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Core\UserProvider;
use Shipweb\LineConnect\Message\LINE\Builder;


class Account {

	// アカウントリンク用のメッセージ作成
	public static function getLinkStartMessage( $secret_prefix, $userId ) {
		$channel = lineconnect::get_channel( $secret_prefix );
		if ( ! $channel ) {
			return false;
		}
		$access_token  = $channel['channel-access-token'];
		$channelSecret = $channel['channel-secret'];

		// Bot作成
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient( $access_token );
		$bot        = new \LINE\LINEBot( $httpClient, array( 'channelSecret' => $channelSecret ) );

		// ユーザーのLinkToken作成
		$response = $bot->createLinkToken( $userId );
		// レスポンスをJSONデコード
		$res_json = $response->getJSONDecodedBody();
		// レスポンスからlinkToken取得
		$linkToken = $res_json['linkToken'];

		$root_dir = trailingslashit( dirname( __FILE__, substr_count( plugin_basename( __FILE__ ), '/' ) ) );
		// WordPressのサイトURLを取得
		// $accountlink_url = plugins_url( 'accountlink.php', $root_dir . lineconnect::PLUGIN_ENTRY_FILE_NAME );
		// $accountlink_url = admin_url('admin-post.php?action=' . lineconnect::SLUG__ACCOUNT_LINK);
		// $redirect_to     = urlencode( $accountlink_url . '?linkToken=' . $linkToken );
		$redirect_page_method = LineConnect::get_option( 'redirect_page_method' );
		if ( $redirect_page_method === 'file' ) {
			$accountlink_base_url = plugins_url( 'accountlink.php', $root_dir . lineConnect::PLUGIN_ENTRY_FILE_NAME );
			$gotologin_base_url   = plugins_url( 'gotologin.php', $root_dir . lineConnect::PLUGIN_ENTRY_FILE_NAME );
		} elseif ( $redirect_page_method === 'admin_post' ) {
			$accountlink_base_url = admin_url( 'admin-post.php?action=' . lineconnect::SLUG__ACCOUNT_LINK );
			$gotologin_base_url   = admin_url( 'admin-post.php?action=' . lineconnect::SLUG__GOTO_LOGIN );
		} elseif ( $redirect_page_method === 'restapi' ) {
			$accountlink_base_url = rest_url( 'lineconnect/v1/accountlink' );
			$gotologin_base_url   = rest_url( 'lineconnect/v1/gotologin' );
		}

		$accountlink_url = add_query_arg(
			array(
				'linkToken' => $linkToken,
			),
			$accountlink_base_url
		);
		$redirect_to     = urlencode( $accountlink_url );

		// WordPressにログインさせたあと、Nonceを作成してLINEへ送信するページへのリダイレクトをするURLを作成
		// $gotologin_url = plugins_url( 'gotologin.php', $root_dir . lineconnect::PLUGIN_ENTRY_FILE_NAME );
		// $gotologin_url = admin_url('admin-post.php?action=' . lineconnect::SLUG__GOTO_LOGIN);
		// $url           = $gotologin_url . '?redirect_to=' . $redirect_to;
		$gotologin_url = add_query_arg(
			array(
				'redirect_to' => $redirect_to,
			),
			$gotologin_base_url
		);
		$url           = $gotologin_url;
		// 連携開始メッセージ作成
		return Builder::createFlexMessage(
			array(
				'title' => lineconnect::get_option( 'link_start_title' ),
				'body'  => lineconnect::get_option( 'link_start_body' ),
				'type'  => 'uri',
				'label' => lineconnect::get_option( 'link_start_button' ),
				'link'  => $url,
			)
		);
	}

	// アカウント連携解除
	public static function unAccountLink( $secret_prefix, $userId ) {
		// global $secret_prefix;
		// メタ情報からLINEユーザーIDでユーザー検索
		$user = lineconnect::get_wpuser_from_line_id( $secret_prefix, $userId );
		// すでに連携されているユーザーが見つかれば
		if ( $user ) { // ユーザーが見つかればすでに連携されているということ
			$user_id = $user->ID; // IDを取得

			// リッチメニューを解除
			do_action( 'line_unlink_richmenu', $user_id, $secret_prefix );

			$user_meta_line = UserProvider::get_user_meta( $user_id, lineconnect::META_KEY__LINE, true );
			if ( $user_meta_line && $user_meta_line[ $secret_prefix ] ) {
				unset( $user_meta_line[ $secret_prefix ] );
				if ( empty( $user_meta_line ) ) {
					// ほかに連携しているチャネルがなければメタデータ削除
					if ( UserProvider::delete_user_meta( $user_id, lineconnect::META_KEY__LINE ) ) {
						$mes = lineconnect::get_option( 'unlink_finish_body' );
					} else {
						$mes = lineconnect::get_option( 'unlink_failed_body' );
					}
				} else {
					// ほかに連携しているチャネルがあれば残りのチャネルが入ったメタデータを更新
					UserProvider::update_user_meta( $user_id, lineconnect::META_KEY__LINE, $user_meta_line );
					$mes = lineconnect::get_option( 'unlink_finish_body' );
				}
				// WP Line Loginと連携解除
				do_action( 'line_login_delete_user_meta', $user_id, $secret_prefix );
			} else {
				$mes = lineconnect::get_option( 'unlink_failed_body' );
			}
		} else {
			$mes = lineconnect::get_option( 'unlink_failed_body' );
		}
		return $mes;
	}
	// update line id profile
	public static function update_line_id_profile_for_new_user( $secret_prefix, $line_id ) {
		global $wpdb;
		$channel = lineconnect::get_channel( $secret_prefix );
		if ( ! $channel ) {
			return false;
		}
		$access_token  = $channel['channel-access-token'];
		$channelSecret = $channel['channel-secret'];
		if ( version_compare( lineconnect::get_current_db_version(), '1.2', '<' ) ) {
			return;
		}
		$table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;
		$line_id_row        = \Shipweb\LineConnect\Utilities\LineId::line_id_row( $line_id, $secret_prefix );
		if ( $line_id_row ) {
			return; // すでに登録されている場合は何もしない
		}
		$user_data = array();
		$is_follow = true;

		// get line profile via LINE Messaging API
		// Bot作成
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient( $access_token );
		$bot        = new \LINE\LINEBot( $httpClient, array( 'channelSecret' => $channelSecret ) );

		// ユーザーのプロフィール取得
		$response = $bot->getProfile( $line_id );
		// check if response is 200
		if ( $response->getHTTPStatus() === 200 ) {
			// レスポンスをJSONデコード
			$profile = $response->getJSONDecodedBody();
			if ( isset( $profile['displayName'] ) ) {
				$user_data['displayName'] = $profile['displayName'];
			}
			if ( isset( $profile['pictureUrl'] ) ) {
				$user_data['pictureUrl'] = $profile['pictureUrl'];
			} else {
				unset( $user_data['pictureUrl'] );
			}
			if ( isset( $profile['language'] ) ) {
				$user_data['language'] = $profile['language'];
			} else {
				unset( $user_data['language'] );
			}
			if ( isset( $profile['statusMessage'] ) ) {
				$user_data['statusMessage'] = $profile['statusMessage'];
			} else {
				unset( $user_data['statusMessage'] );
			}
		} else {
			$is_follow = false;
		}

		// insert
		$result = $wpdb->insert(
			$table_name_line_id,
			array(
				'channel_prefix' => $secret_prefix,
				'line_id'        => $line_id,
				'follow'         => $is_follow,
				'profile'        => ! empty( $user_data ) ? json_encode( $user_data, JSON_UNESCAPED_UNICODE ) : null,
			),
			array(
				'%s',
				'%s',
				'%d',
				'%s',
			)
		);
		if ( $result === false ) {
			error_log( 'insert_line_id_follow error' );
		} else {
			// error_log('insert_line_id_follow success');
		}
	}

	// update line id follow
	public static function update_line_id_follow( $secret_prefix, $line_id, $is_follow ) {
		global $wpdb;
		$channel = lineconnect::get_channel( $secret_prefix );
		if ( ! $channel ) {
			return false;
		}
		$access_token  = $channel['channel-access-token'];
		$channelSecret = $channel['channel-secret'];
		if ( version_compare( lineconnect::get_current_db_version(), '1.2', '<' ) ) {
			return;
		}
		$table_name_line_id = $wpdb->prefix . lineconnect::TABLE_LINE_ID;

		// update
		$result = $wpdb->update(
			$table_name_line_id,
			array(
				'follow' => $is_follow,
			),
			array(
				'channel_prefix' => $secret_prefix,
				'line_id'        => $line_id,
			),
			array(
				'%d',
			),
			array(
				'%s',
				'%s',
			)
		);
		if ( $result === false ) {
			error_log( 'update_line_id_follow error' );
		}
	}

	public static function goto_login_page() {
		// パラーメータからリダイレクト先を取得
		$raw_redirect = filter_input( INPUT_GET, 'redirect_to', FILTER_DEFAULT );
		if ( $raw_redirect === null ) {
			wp_die( 'Bad Request: redirect_to is required.', 400 );
		}

		// 初期値取得・デコード・改行などの除去（ヘッダーインジェクション対策）
		$redirect_to_raw = wp_unslash( $raw_redirect );
		$redirect_to_raw = preg_replace( '/[\r\n]/', '', $redirect_to_raw );
		// `redirect_to` は URL エンコードされて渡されることがあるためデコードしてから正規化する
		$redirect_to = urldecode( $redirect_to_raw );
		$redirect_to = esc_url_raw( $redirect_to );

		// リダイレクト先は自サイト（ホスト）か、または相対パスのみ許可する
		$allowed_host = parse_url( get_site_url(), PHP_URL_HOST );
		$target_host  = parse_url( $redirect_to, PHP_URL_HOST );
		if ( null !== $target_host && $allowed_host !== $target_host ) {
			wp_die( 'Bad Request: Invalid redirect destination.', 400 );
		}

		$user_id = UserProvider::get_current_user_id();
		// デバッグログは WP_DEBUG 時のみ出す
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'goto_login_page: user_id = ' . $user_id );
		}

		if ( ! $user_id ) {
			// ログインしていない場合、ログインページへリダイレクト
			// COOKIE にリダイレクト先を格納（改行は除去済み）
			$cookie_value = $redirect_to;
			$secure       = is_ssl();
			setcookie( 'line_connect_redirect_to', $cookie_value, 0, '/', '', $secure, true );

			// ログインページの URL を組み立て
			$site_url  = trailingslashit( get_site_url() );
			$login_url = LineConnect::get_option( 'login_page_url' );
			if ( strpos( $login_url, 'http' ) === false ) {
				$login_url = $site_url . ltrim( $login_url, '/' );
			}

			// クエリ値は urlencode して付与
			$redirect_url = add_query_arg( 'redirect_to', urlencode( $redirect_to ), $login_url );

			wp_redirect( $redirect_url );
			exit();
		} else {
			// ログインしている場合は直接アカウントリンク用のページへ
			wp_redirect( $redirect_to );
			exit();
		}
	}

	public static function account_link_page() {

		$user_id = UserProvider::get_current_user_id();
		if ( ! $user_id ) {
			wp_die( 'Forbidden: Please Login first.', 403 );
		}

		$link_token = isset( $_GET['linkToken'] ) ? sanitize_text_field( wp_unslash( $_GET['linkToken'] ) ) : '';
		if ( empty( $link_token ) ) {
			wp_die( 'Bad Request: linkToken is required.', 400 );
		}

		$nonce     = self::make_rand_str( 32 );
		$nonce_key = 'lineconnect_nonce' . $nonce;
		set_transient( $nonce_key, $user_id, 10 * MINUTE_IN_SECONDS );

		$redirect_url = 'https://access.line.me/dialog/bot/accountLink?linkToken='
						. urlencode( $link_token ) . '&nonce=' . urlencode( $nonce );
		wp_redirect( $redirect_url );
		exit();
	}

	private static function make_rand_str( int $length ): string {
		$chars  = array_merge( range( 'a', 'z' ), range( 'A', 'Z' ), range( '0', '9' ) );
		$result = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$result .= $chars[ random_int( 0, count( $chars ) - 1 ) ];
		}
		return $result;
	}
}
