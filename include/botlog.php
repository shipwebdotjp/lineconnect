<?php
/**
 * Lineconnect Bot Log Class
 *
 * Bot Log Class
 *
 * @category Components
 * @package  Bot Log
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

class lineconnectBotLog {
	public $event;

	public function __construct( object $event ) {
		$this->event = $event;
	}

	// チャットログ書き込み
	function writeChatLog() {
		global $wpdb, $secret_prefix, $channelSecret;

		$table_name = $wpdb->prefix . lineconnectConst::TABLE_BOT_LOGS;

		$event_type = $source_type = $message_type = 0;
		$user_id    = $event_id = '';
		$message    = null;

		$event_id = $this->event->{'webhookEventId'};
		if ( isset( $this->event->{'deliveryContext'}->{'isRedelivery'} ) ) {
			if ( $this->event->{'deliveryContext'}->{'isRedelivery'} ) {
				// 再送イベントならすでに記録されていないかチェック
				$event_count = $wpdb->get_var(
					$query   = $wpdb->prepare(
						"SELECT COUNT(id) FROM {$table_name} WHERE event_id = %s ",
						$event_id
					)
				);
				if ( $event_count ) {
					return true;
				}
			}
		}
		$event_type = array_search( $this->event->{'type'}, lineconnectConst::WH_EVENT_TYPE ) ?: 0;
		if ( isset( $this->event->{'source'} ) ) {
			$source_type = array_search( $this->event->{'source'}->{'type'}, lineconnectConst::WH_SOURCE_TYPE ) ?: 0;
			if ( isset( $this->event->{'source'}->{'userId'} ) ) {
				$user_id = $this->event->{'source'}->{'userId'};
			} else {
				$user_id = '';
			}
		} else {
			$source_type = 0;
			$user_id     = '';
		}

		if ( $event_type == 1 ) { // message
			$message_type = array_search( $this->event->{'message'}->{'type'}, lineconnectConst::WH_MESSAGE_TYPE ) ?: 0;
			$message      = json_encode( $this->event->{'message'} );
		} elseif ( $event_type == 9 ) { // postback
			$message = json_encode( $this->event->{'postback'} );
		} elseif ( $event_type == 10 ) { // videoPlayComplete
			$message = json_encode( $this->event->{'videoPlayComplete'} );
		} elseif ( $event_type == 11 ) { // beacon
			$message = json_encode( $this->event->{'beacon'} );
		} elseif ( $event_type == 12 ) { // accountLink
			$message = json_encode( $this->event->{'link'} );
		} elseif ( $event_type == 13 ) { // things
			$message = json_encode( $this->event->{'things'} );
		}
		$floatSec = $this->event->{'timestamp'} / 1000.0;
		$dateTime = DateTime::createFromFormat( 'U\.u', sprintf( '%1.6F', $floatSec ) );
		$dateTime->setTimeZone( new DateTimeZone( 'Asia/Tokyo' ) );
		$timestamp = $dateTime->format( 'Y-m-d H:i:s.u' );

		$data   = array(
			'event_id'     => $event_id,
			'event_type'   => $event_type,
			'source_type'  => $source_type,
			'user_id'      => $user_id,
			'bot_id'       => $secret_prefix,
			'message_type' => $message_type,
			'message'      => $message,
			'timestamp'    => $timestamp,
		);
		$format = array(
			'%s', // event_id
			'%d', // event_type
			'%d', // source_type
			'%s', // user_id
			'%s', // bot_id
			'%d', // message_type
			'%s', // message
			'%s', // timestamp
		);

		$wpdb->insert( $table_name, $data, $format );
		// get inserted id
		$inserted_id = $wpdb->insert_id;
		return $inserted_id;
	}

	// AIからの応答をロギング
	function writeAiResponse( $responseMessage ) {
		global $wpdb, $secret_prefix, $channelSecret;

		$table_name = $wpdb->prefix . lineconnectConst::TABLE_BOT_LOGS;

		$event_type = $source_type = $message_type = 0;
		$user_id    = '';
		$message    = null;

		$event_id    = $this->event->{'webhookEventId'};
		$event_type  = array_search( $this->event->{'type'}, lineconnectConst::WH_EVENT_TYPE ) ?: 0;
		$source_type = array_search( 'bot', lineconnectConst::WH_SOURCE_TYPE ) ?: 0;
		if ( isset( $this->event->{'source'} ) ) {
			if ( isset( $this->event->{'source'}->{'userId'} ) ) {
				$user_id = $this->event->{'source'}->{'userId'};
			}
		}

		if ( $event_type == 1 ) {
			$message_type = 1;
			$message      = json_encode(
				array(
					'type' => 'text',
					'text' => $responseMessage,
					'for'  => $this->event->{'message'}->{'id'},
				)
			);
		}
		$floatSec = microtime( true );
		$dateTime = DateTime::createFromFormat( 'U\.u', sprintf( '%1.6F', $floatSec ) );
		$dateTime->setTimeZone( new DateTimeZone( 'Asia/Tokyo' ) );
		$timestamp = $dateTime->format( 'Y-m-d H:i:s.u' );

		$data   = array(
			'event_id'     => $event_id,
			'event_type'   => $event_type,
			'source_type'  => $source_type,
			'user_id'      => $user_id,
			'bot_id'       => $secret_prefix,
			'message_type' => $message_type,
			'message'      => $message,
			'timestamp'    => $timestamp,
		);
		$format = array(
			'%s', // event_id
			'%d', // event_type
			'%d', // source_type
			'%s', // user_id
			'%s', // bot_id
			'%d', // message_type
			'%s', // message
			'%s', // timestamp
		);

		$wpdb->insert( $table_name, $data, $format );
	}
}
