<?php
/*
LINE Bot
	Copyright 2020 shipweb
*/

require_once 'vendor/autoload.php'; // LINE BOT SDKを読み込み
require_once '../../../wp-load.php'; // WordPressの基本機能を読み込み
require_once 'lineconnect.php'; // LINE Connectを読み込み
// require_once('include/message.php'); // メッセージ関連を読み込み

// JSONリクエストボディを取得
$json_string = file_get_contents( 'php://input' );

// 検証結果
$valid_signature = false;

// チャンネルごとに署名を検証
foreach ( lineconnect::get_all_channels() as $channel_id => $channel ) {
	// チャネルアクセストークン（長期）
	// チャネルシークレット
	$access_token  = $channel['channel-access-token'];
	$channelSecret = $channel['channel-secret'];

	// LINE ID KEY
	$secret_prefix = substr( $channelSecret, 0, 4 );

	// 署名を検証するためにチャネルシークレットを秘密鍵として、HMAC-SHA256アルゴリズムを使用してリクエストボディのダイジェスト値を取得
	$hash = hash_hmac( 'sha256', $json_string, $channelSecret, true );
	// ダイジェスト値をBase64エンコード
	$signature = base64_encode( $hash );
	// HTTP HeaderからX-Line-Signatureを取得
	$XLineSignature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

	// 署名が一致する場合
	if ( $signature == $XLineSignature ) {
		$valid_signature = true;
		break;
	}
}

// 署名がどのチャンネルにも一致しない場合は400を返す
if ( ! $valid_signature ) {
	http_response_code( 400 );
	print 'Bad signature';
	exit;
} else {
	// とりあえずステータスコード200を返す
	http_response_code( 200 );
	ob_start( null, 0, PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE ); // 出力をバッファっていう感じで制御できるように、好きなタイミングで吐けるようにオプションをつけておく
	header( 'Content-length: ' . ob_get_length() );
	header( 'Connection: close' );
	while ( ob_get_level() > 0 ) {
		ob_end_flush();
	}
	flush();
}

// JSONリクエストボディをデコード
$json_obj = json_decode( $json_string );

foreach ( $json_obj->{'events'} as $event ) {
	$message = array();
	// ログ書き込み
	$botlog                         = new lineconnectBotLog( $event );
	$isEventDuplicationOrInsertedId = $botlog->writeChatLog();
	if ( $isEventDuplicationOrInsertedId === true ) {
		// イベントがすでに記録されていればスキップ
		continue;
	}

	// リプレイトークンを取得
	$reply_token = isset( $event->{'replyToken'} ) ? $event->{'replyToken'} : null;

	// イベントタイプを取得
	$type = $event->{'type'};

	if ( $type === 'message' ) {    // メッセージ受け取り時

		// メッセージオブジェクトのタイプ
		$msg_obj = $event->{'message'}->{'type'};

		if ( $msg_obj === 'text' ) {
			// テキストメッセージを受け取った時
			$msg_text = $event->{'message'}->{'text'};
			// テキストに 連携開始／解除キーワード が含まれていた場合
			if ( strpos( $msg_text, lineconnect::get_option( 'link_start_keyword' ) ) !== false ) {
				$userId = $event->{'source'}->{'userId'};

				// メタ情報からLINEユーザーIDでユーザー検索
				$user = lineconnect::get_wpuser_from_line_id( $secret_prefix, $userId );
				if ( $user ) { // ユーザーが見つかればすでに連携されているということ
					$user_id = $user->ID; // IDを取得

					// 連携解除メッセージ作成
					$message[] = lineconnectMessage::createFlexMessage(
						array(
							'title' => lineconnect::get_option( 'unlink_start_title' ),
							'body'  => lineconnect::get_option( 'unlink_start_body' ),
							'type'  => 'postback',
							'label' => lineconnect::get_option( 'unlink_start_button' ),
							'link'  => 'action=unlink',
						)
					);
				} else {
					// 連携開始メッセージ作成
					$message[] = getLinkStartMessage( $userId );
				}
			}
		}
	} elseif ( $type === 'accountLink' ) {
		// アカウントリンク時
		$link_obj = $event->{'link'};
		// アカウントリンク成功時
		if ( $link_obj->{'result'} === 'ok' ) {

			// nonceを取得
			$nonce = $link_obj->{'nonce'};
			// nonceから対応するユーザーIDを取得
			$user_id = get_option( 'lineconnect_nonce' . $nonce );

			// nonceに対応するユーザーIDがあれば
			if ( $user_id ) {
				// LINE ユーザーID
				$userId = $event->{'source'}->{'userId'};

				// Bot作成
				$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient( $access_token );
				$bot        = new \LINE\LINEBot( $httpClient, array( 'channelSecret' => $channelSecret ) );

				// ユーザーのプロフィール取得
				$response = $bot->getProfile( $userId );
				// レスポンスをJSONデコード
				$profile = $response->getJSONDecodedBody();

				// nonceを削除
				delete_option( 'lineconnect_nonce' . $nonce );

				// WordPressユーザーのメタ情報にLINEユーザーIDを追加
				$line_user_data = get_user_meta( $user_id, lineconnect::META_KEY__LINE, true );
				if ( empty( $line_user_data ) ) {
					$line_user_data = array();
				}
				$line_user_data[ $secret_prefix ] = array(
					'id'          => $userId,
					'displayName' => $profile['displayName'],
					'isFriend'    => true,
				);
				if ( isset( $profile['pictureUrl'] ) ) {
					$line_user_data['pictureUrl'] = $profile['pictureUrl'];
				}
				update_user_meta( $user_id, lineconnect::META_KEY__LINE, $line_user_data );

				// WP Line Loginと連携
				do_action( 'line_login_update_user_meta', $user_id, $line_user_data[ $secret_prefix ], $secret_prefix );

				// リッチメニューをセット
				do_action( 'line_link_richmenu', $user_id );

				// 連携完了のテキストメッセージ作成
				$message[] = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder( lineconnect::get_option( 'link_finish_body' ) );
			} else {
				// 連携失敗のテキストメッセージ作成
				$message[] = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder( lineconnect::get_option( 'link_failed_body' ) );
			}
		}
	} elseif ( $type === 'postback' ) {
		// ポストバック受け取り時

		// 送られたデータ
		$postback = $event->{'postback'}->{'data'};

		if ( $postback === 'action=unlink' ) {
			// 解除選択時
			$userId = $event->{'source'}->{'userId'};
			$mes    = unAccountLink( $userId );

			// 連携解除完了のテキストメッセージ作成
			$message[] = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder( $mes );
		} elseif ( $postback === 'action=link' ) {
			// 連携選択時
			$userId    = $event->{'source'}->{'userId'};
			$message[] = getLinkStartMessage( $userId );
		}
	} elseif ( $type == 'follow' ) {
		$userId = $event->{'source'}->{'userId'};
		if ( lineconnect::get_option( 'enable_link_autostart' ) === 'on' ) {
			// 友達登録時　自動連携開始がONであれば、アカウントリンクイベントを作成
			$message[] = getLinkStartMessage( $userId );
		}
		update_line_id_follow( $userId, true );
	} elseif ( $type == 'unfollow' ) {
		// 友達登録解除（ブロック時）リストから消去
		$userId = $event->{'source'}->{'userId'};
		$mes    = unAccountLink( $userId );
		update_line_id_follow( $userId, false );

	}

	// if message type is image,video,audio,file and contentProvider.type is line
	if ( $type === 'message' && in_array( $event->{'message'}->{'type'}, array( 'image', 'video', 'audio', 'file' ) ) && $event->{'message'}->{'contentProvider'}->{'type'} === 'line' ) {
		// save content
		$saved_content_file_name = getMessageContent( $event->{'message'}->{'id'}, isset( $event->{'source'}->{'userId'} ) ? $event->{'source'}->{'userId'} : '_none' );

		// update filepath to log table
		$result = update_message_filepath( $isEventDuplicationOrInsertedId, $saved_content_file_name );
	}

	// check if match trigger
	$triggers = array();
	$args     = array(
		'post_type'      => lineconnectConst::POST_TYPE_TRIGGER,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	);
	$posts    = get_posts( $args );
	foreach ( $posts as $post ) {
		$form = get_post_meta( $post->ID, lineconnect::META_KEY__TRIGGER_DATA, true );
		if ( isset( $form[0]['type'] ) && $form[0]['type'] === 'webhook' ) {
			$triggers[] = $form[1];
		}
	}

	// wp_reset_postdata();

	foreach ( $triggers as $trigger ) {
		$matched_array = array();
		// $trigger['triggers']の各条件のいずれかに一致するかどうかをチェック
		foreach ( $trigger['triggers'] as $trigger_item ) {
			$matched_array[] = check_trigger_condition( $trigger_item, $event, $secret_prefix );
		}

		// $trigger['triggers']の各条件のいずれかに一致する場合
		if ( ! in_array( true, $matched_array ) ) {
			// error_log( 'trigger not match' . print_r( $matched_array, true ));
			continue;
		}
		// error_log( 'trigger type match:' . print_r( $trigger, true ) );

		if ( isset( $trigger['action'] ) ) {
			$action_return = lineconnectAction::do_action($trigger['action'], $trigger['chain']??null, $event, $secret_prefix);
			if(is_array($action_return)){
				$message = array_merge($message, $action_return);
			}
		}
	}

	if ( empty( $message ) && $type === 'message' && $event->{'message'}->{'type'} === 'text' && $event->{'message'}->{'text'} != null && lineconnect::get_option( 'enableChatbot' ) == 'on' ) {
		// AIで応答する
		$openAi       = new lineconnectOpenAi();
		$gptResponse  = $openAi->getResponseByChatGPT(
			$event,
			$secret_prefix,
			array(
				array(
					'role'    => 'user',
					'content' => $event->{'message'}->{'text'},
				),
			)
		);
		$message[]    = $gptResponse['message'];
		$responseByAi = $gptResponse['responseByAi'];
		error_log( 'gpt response:' . print_r( $gptResponse, true ) );
	}

	// 応答メッセージがあれば送信する
	if ( ! empty( $message ) && ! empty( $reply_token ) ) {
		// Bot作成
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient( $access_token );
		$bot        = new \LINE\LINEBot( $httpClient, array( 'channelSecret' => $channelSecret ) );

		$multimessage = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
		foreach ( $message as $message_item ) {
			$multimessage->add( $message_item );
		}
		// 応答メッセージ送信
		$resp = $bot->replyMessage( $reply_token, $multimessage );
		LoggingAPIResponse( $resp, $message );
	}

	// 応答メッセージをロギング
	if ( isset( $responseByAi ) && $responseByAi === true ) {
		$botlog->writeAiResponse( $gptResponse['rowResponse']['choices'][0]['message']['content'] );
		$responseByAi = null;
	}
}
exit;

// アカウントリンク用のメッセージ作成
function getLinkStartMessage( $userId ) {
	global $access_token, $channelSecret;

	// Bot作成
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient( $access_token );
	$bot        = new \LINE\LINEBot( $httpClient, array( 'channelSecret' => $channelSecret ) );

	// ユーザーのLinkToken作成
	$response = $bot->createLinkToken( $userId );
	// レスポンスをJSONデコード
	$res_json = $response->getJSONDecodedBody();
	// レスポンスからlinkToken取得
	$linkToken = $res_json['linkToken'];

	// WordPressのサイトURLを取得
	$accountlink_url = plugins_url( 'accountlink.php', __FILE__ );
	$redirect_to     = urlencode( $accountlink_url . '?linkToken=' . $linkToken );
	// WordPressにログインさせたあと、Nonceを作成してLINEへ送信するページへのリダイレクトをするURLを作成
	$gotologin_url = plugins_url( 'gotologin.php', __FILE__ );
	$url           = $gotologin_url . '?redirect_to=' . $redirect_to;

	// 連携開始メッセージ作成
	return lineconnectMessage::createFlexMessage(
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
function unAccountLink( $userId ) {
	global $secret_prefix;
	// メタ情報からLINEユーザーIDでユーザー検索
	$user = lineconnect::get_wpuser_from_line_id( $secret_prefix, $userId );
	// すでに連携されているユーザーが見つかれば
	if ( $user ) { // ユーザーが見つかればすでに連携されているということ
		$user_id = $user->ID; // IDを取得

		// リッチメニューを解除
		do_action( 'line_unlink_richmenu', $user_id, $secret_prefix );

		$user_meta_line = $user->get( lineconnect::META_KEY__LINE );
		if ( $user_meta_line && $user_meta_line[ $secret_prefix ] ) {
			unset( $user_meta_line[ $secret_prefix ] );
			if ( empty( $user_meta_line ) ) {
				// ほかに連携しているチャネルがなければメタデータ削除
				if ( delete_user_meta( $user_id, lineconnect::META_KEY__LINE ) ) {
					$mes = lineconnect::get_option( 'unlink_finish_body' );
				} else {
					$mes = lineconnect::get_option( 'unlink_failed_body' );
				}
			} else {
				// ほかに連携しているチャネルがあれば残りのチャネルが入ったメタデータを更新
				update_user_meta( $user_id, lineconnect::META_KEY__LINE, $user_meta_line );
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


// メッセージコンテント取得
function getMessageContent( $messageId, $userId ) {
	global $access_token, $channelSecret;

	// Bot作成
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient( $access_token );
	$bot        = new \LINE\LINEBot( $httpClient, array( 'channelSecret' => $channelSecret ) );

	// コンテンツ取得
	$response = $bot->getMessageContent( $messageId );
	if ( $response->getHTTPStatus() == 200 ) {
		// レスポンスからバイナリデータを取得
		$content = $response->getRawBody();
		// レスポンスのContent-Typeヘッダーからバイナリデータのファイル形式を取得
		$contentType = $response->getHeader( 'Content-Type' );

		// 取得したファイル形式から適切なファイル拡張子を選択
		$file_extention = get_file_extention( $contentType );

		// make file name from message id and file extention
		$file_name = $messageId . '.' . $file_extention;
		// set user directory
		$user_dir = substr( $userId, 1, 4 );
		// make directory
		$target_dir_path = make_lineconnect_dir( $user_dir );
		if ( $target_dir_path ) {
			// make file path
			$file_path = $target_dir_path . '/' . $file_name;
			// write file
			file_put_contents( $file_path, $content );
			// return file path
			return $user_dir . '/' . $file_name;
		}
	}
	return false;
}

// make 'lineconnect' directory in wp-content/uploads
function make_lineconnect_dir( $user_dir ) {
	$root_dir_path = WP_CONTENT_DIR . '/uploads/lineconnect';
	// check if root dir exists
	if ( ! file_exists( $root_dir_path ) ) {
		// make root dir
		if ( mkdir( $root_dir_path, 0777, true ) ) {
			// put .htaccess file to root dir
			$htaccess_file_path    = $root_dir_path . '/.htaccess';
			$htaccess_file_content = 'deny from all';
			file_put_contents( $htaccess_file_path, $htaccess_file_content );
		}
	}
	$target_dir_path = $root_dir_path . '/' . $user_dir;
	// check if target dir exists
	if ( ! file_exists( $target_dir_path ) ) {
		// make target dir
		if ( mkdir( $target_dir_path, 0777, true ) ) {
			// put .htaccess file to target dir
			$htaccess_file_path    = $target_dir_path . '/.htaccess';
			$htaccess_file_content = 'deny from all';
			file_put_contents( $htaccess_file_path, $htaccess_file_content );
			return $target_dir_path;
		} else {
			return false;
		}
	}
	return $target_dir_path;
}

// MIME type to file Extension
function get_file_extention( $mime_type ) {
	return lineconnectConst::MIME_MAP[ $mime_type ] ?? 'bin';
}

// update message
function update_message_filepath( $logId, $file_path ) {
	global $wpdb;
	$table_name = $wpdb->prefix . lineconnectConst::TABLE_BOT_LOGS;
	// get row from log table
	$row = $wpdb->get_row( "SELECT * FROM {$table_name} WHERE id = {$logId}" );
	if ( $row ) {
		// message column is JSON string, so decode to object
		$message = json_decode( $row->message );
		// set file path
		$message->file_path = $file_path;
		// update message column
		return $wpdb->update( $table_name, array( 'message' => json_encode( $message ) ), array( 'id' => $logId ) );
	}
	return false;
}

// update line id follow
function update_line_id_follow( $line_id, $is_follow ) {
	global $access_token, $channelSecret, $secret_prefix, $wpdb;
	if ( version_compare( lineconnect::get_current_db_version(), '1.2', '<' ) ) {
		return;
	}
	$table_name_line_id = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;
	$line_id_row = lineconnectUtil::line_id_row( $line_id, $secret_prefix );
	if ( $line_id_row ) {
		$user_data = json_decode( $line_id_row['profile'], true );
	} else {
		$user_data = array();
	}

	if ( $is_follow ) {
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
	}
	if ( $line_id_row ) {
		// update
		$result = $wpdb->update(
			$table_name_line_id,
			array(
				'follow'  => $is_follow,
				'profile' => ! empty( $user_data ) ? json_encode( $user_data, JSON_UNESCAPED_UNICODE ) : null,
			),
			array(
				'line_id'        => $line_id,
				'channel_prefix' => $secret_prefix,
			),
			array(
				'%d',
				'%s',
			)
		);
		if ( $result === false ) {
			error_log( 'update_line_id_follow error' );
		}
	} else {
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
		}
	}
}

function LoggingAPIResponse( $response, $message ) {
	$isSucced      = $response->isSucceeded();
	$response_body = $response->getJSONDecodedBody();
	if ( ! $isSucced ) {
		// liceconnectError::error_logging(['message' => $message, 'response' => $response_body]);
		error_log(
			print_r(
				array(
					'message'  => $message,
					'response' => $response_body,
				),
				true
			)
		);
	} else {
		// error_log( print_r( ['response' => $response_body], true ) );
	}
}

function check_trigger_condition( $trigger, $event, $secret_prefix ) {
	if ( $trigger['type'] !== $event->{'type'} ) {
		return false;
	}

	if ( $trigger['type'] === 'message' ) {
		if ( $trigger['message']['type'] === $event->{'message'}->{'type'} ) {
			$result = check_webhook_message_text_condition( $trigger['message']['text'], $event->{'message'}->{'text'} );
			if ( ! $result ) {
				// error_log("check_webhook_message_text_condition:".$result);
				return false;
			}
		}
	} elseif ( $trigger['type'] === 'postback' ) {
		if ( ! lineconnectUtil::is_empty( $trigger['postback']['data'] ) ) {
			$result = check_webhook_message_text_condition( $trigger['postback']['data'], $event->{'postback'}->{'data'} );
			if ( ! $result ) {
				return false;
			}
		}
	} elseif ( $trigger['type'] === 'follow' ) {
		if ( $trigger['follow']['isUnblocked'] === 'add' && $event->{'follow'}->{'isUnblocked'} === true ) {
			return false;
		} elseif ( $trigger['follow']['isUnblocked'] === 'unblocked' && $event->{'follow'}->{'isUnblocked'} === false ) {
			return false;
		}
	}
	// error_log(print_r($trigger['condition'],true));
	if ( ! empty( $trigger['condition'] ) ) {
		$result = check_webhook_condition( $trigger['condition'], $event, $secret_prefix );
		if ( ! $result ) {
			// error_log("check_webhook_condition:".$result);

			return false;
		}
	}
	return true;
}

function check_webhook_message_text_condition( $message, $data ) {
	$condition_results = array();
	foreach ( $message['conditions'] as $condition ) {
		if ( isset( $condition['type'] ) && $condition['type'] === 'source' )  {
			$result_bool         = check_webhook_message_text_keyword_condition( $condition['source'], $data );
			$condition_results[] = isset( $condition['not'] ) && $condition['not'] === true  ? ! $result_bool : $result_bool;
		} elseif ( isset( $condition['type']) && $condition['type'] === 'group'  ) {
			$condition_results[] = check_webhook_message_text_condition( $condition['condition'], $data );
		}
	}
	// error_log(print_r($condition_results, true));
	if ( isset( $message['operator'] )  && $message['operator'] === 'or' && ! in_array( true, $condition_results, true ) ) {
		return false;
	} elseif ( ( ! isset( $message['operator'] ) || $message['operator'] === 'and' ) && in_array( false, $condition_results, true ) ) {
		return false;
	}
	return true;
}

function check_webhook_message_text_keyword_condition( $source, $data ) {
	if ( isset( $source['type'] ) && $source['type'] === 'keyword' ) {
		if ( isset( $source['keyword']['match'] ) && $source['keyword']['match'] === 'contains' ) {
			if ( isset( $source['keyword']['keyword'] ) && strpos( $data, $source['keyword']['keyword'] ) === false ) {
				return false;
			}
		} elseif ( isset( $source['keyword']['match'] ) && $source['keyword']['match'] === 'equals' ) {
			if ( isset( $source['keyword']['keyword'] ) && $source['keyword']['keyword'] !== $data ) {
				return false;
			}
		} elseif ( isset( $source['keyword']['match'] ) && $source['keyword']['match'] === 'startWith' ) {
			if ( isset( $source['keyword']['keyword'] ) && strpos( $data, $source['keyword']['keyword'] ) !== 0 ) {
				return false;
			}
		} elseif ( isset( $source['keyword']['match'] ) && $source['keyword']['match'] === 'endWith' ) {
			if ( isset( $source['keyword']['keyword'] ) && substr( $data, -strlen( $source['keyword']['keyword'] ) ) !== $source['keyword']['keyword'] ) {
				return false;
			}
		} elseif ( isset( $source['keyword']['match'] ) && $source['keyword']['match'] === 'regexp' ) {
			if ( isset( $source['keyword']['keyword'] ) && ! preg_match( $source['keyword']['keyword'], $data ) ) {
				return false;
			}
		}
	} elseif ( isset( $source['type'] ) && $source['type'] === 'query' ) {
		$query_array = array();
		foreach ( $source['query']['parameters'] as $param ) {
			if ( isset( $param['key'] ) && isset( $param['value'] ) ) {
				$query_array[ $param['key'] ] = $param['value'];
			}
		}
		$data_array = array();
		// query string $data to array
		parse_str( $data, $data_array );
		// compare query array and data array

		if ( ! isset( $source['query']['match'] ) || $source['query']['match'] === 'contains' ) {
			// error_log(print_r(array_intersect_assoc( $query_array, $data_array ), true));

			if ( count( array_intersect_assoc( $query_array, $data_array ) ) !== count( $query_array ) ) {
				return false;
			}
		} elseif ( $source['query']['match'] === 'equals' ) {
			ksort($query_array);
			ksort($data_array);
			// error_log(print_r(array( $query_array, $data_array ), true));
			// error_log(print_r(array_diff_assoc( $query_array, $data_array ), true));

			if ( $query_array !== $data_array) {
				return false;
			}
		}
	}
	return true;
}


function check_webhook_condition( $condition_object, $event, $secret_prefix ) {
	if(empty($condition_object['conditions'])){
		return true;
	}
	$condition_results = array();
	foreach ( $condition_object['conditions'] as $condition ) {
		// json compare with $event->{'source'}
		if ( isset( $condition['type']) && $condition['type'] === 'source'  ) {
			$result_bool         = check_webhook_source_condition( $condition['source'], $event, $secret_prefix );
			$condition_results[] = isset( $condition['not'] ) && $condition['not'] === true  ? ! $result_bool : $result_bool;
		}elseif ( isset( $condition['type']) && $condition['type'] === 'channel'  ) {
			$result_bool         = check_webhook_secret_prefix_condition( $condition['secret_prefix'], $event, $secret_prefix );
			$condition_results[] = isset( $condition['not'] ) && $condition['not'] === true  ? ! $result_bool : $result_bool;
		} elseif ( isset( $condition['type'] ) && $condition['type'] === 'group'  ) {
			$condition_results[] = check_webhook_condition( $condition['condition'], $event, $secret_prefix );
		}
	}
	if (  isset( $condition_object['operator'] ) && $condition_object['operator'] === 'or' && ! in_array( true, $condition_results, true ) ) {
		return false;
	} elseif ( (! isset( $condition_object['operator'] ) || $condition_object['operator'] === 'and') && in_array( false, $condition_results, true ) ) {
		return false;
	}
	
	return true;
}

function check_webhook_source_condition( $condition, $event, $secret_prefix ) {
	if ( isset( $condition['type'] ) && isset( $event->{'source'}->{'type'} ) && $condition['type'] !== $event->{'source'}->{'type'} ) {
		return false;
	}
	if ( $condition['type'] === 'group' && ! empty( $condition['groupId'] ) && isset( $event->{'source'}->{'groupId'} ) && ! in_array( $event->{'source'}->{'groupId'}, $condition['groupId'] ) ) {
		return false;
	}
	if ( $condition['type'] === 'room' && ! empty( $condition['roomId'] ) && isset( $event->{'source'}->{'roomId'} ) && ! in_array( $event->{'source'}->{'roomId'}, $condition['roomId'] ) ) {
		return false;
	}
	if ( ! empty( $condition['userId'] ) && isset( $event->{'source'}->{'userId'} ) && ! in_array( $event->{'source'}->{'userId'}, $condition['userId'] ) ) {
		return false;
	}
	if ( $condition['type'] === 'user' ) {
		if ( ! empty( $condition['link'] ) &&
		(
			( $condition['link'] === 'linked' && lineconnect::get_wpuser_from_line_id( $secret_prefix, $event->{'source'}->{'userId'} ) === false )
			|| ( $condition['link'] === 'unlinked' && lineconnect::get_wpuser_from_line_id( $secret_prefix, $event->{'source'}->{'userId'} ) !== false )
		) ) {
			return false;
		}
		if ( ! empty( $condition['role'] ) ) {
			$user = lineconnect::get_wpuser_from_line_id( $secret_prefix, $event->{'source'}->{'userId'} );
			if ( $user === false ) {
				return false;
			}
			$user_roles        = (array) $user->roles;
			$user_roles_name = array();
			foreach ( $user_roles as $role ) {
				$user_roles_name[] = wp_roles()->get_names()[$role];
			}

			$user_roles_result = array_intersect( $condition['role'], $user_roles_name );
			if ( empty( $user_roles_result ) ) {
				return false;
			}
		}
	}
	return true;
}

function check_webhook_secret_prefix_condition($secret_prefixs, $event, $secret_prefix) {
	if(!empty($secret_prefixs) && ! in_array($secret_prefix, $secret_prefixs)){
		return false;
	}
	return true;
}
