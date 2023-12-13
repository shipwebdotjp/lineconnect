<?php
/**
 * Lineconnect Gpt Log List Table Class
 *
 * List Table Class
 *
 * @category Components
 * @package  Gpt Log List Table
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

class lineconnectGptLogListTable extends WP_List_Table {
	/**
	 * 初期設定画面を表示
	 */
	function show_list() {
		?>
		<form method="post" id="bulk-action-form">
		<?php
		$this->prepare_items();
		$this->search_box( _( 'Search', lineconnect::PLUGIN_NAME ), 'search' );
		$this->display();
		?>
		</form>
		<?php
	}

	/**
	 * 初期化時の設定を行う
	 */
	public function __construct( $args = array() ) {
		parent::__construct(
			array(
				'plural' => 'chatlogs',
				'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
			)
		);
	}

	/**
	 * 表で使用されるカラム情報の連想配列を返す
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'           => '<input type="checkbox" />',
			'id'           => __( 'ID', lineconnect::PLUGIN_NAME ),
			'event_id'     => __( 'Event ID', lineconnect::PLUGIN_NAME ),
			'event_type'   => __( 'Event Type', lineconnect::PLUGIN_NAME ),
			'source_type'  => __( 'Source Type', lineconnect::PLUGIN_NAME ),
			'user_id'      => __( 'User ID', lineconnect::PLUGIN_NAME ),
			'bot_id'       => __( 'BOT ID', lineconnect::PLUGIN_NAME ),
			'message_type' => __( 'Message Type', lineconnect::PLUGIN_NAME ),
			'message'      => __( 'Message', lineconnect::PLUGIN_NAME ),
			'timestamp'    => __( 'DATE TIME', lineconnect::PLUGIN_NAME ),
		);
	}

	/**
	 * プライマリカラム名を返す
	 *
	 * @return string
	 */
	protected function get_primary_column_name() {
		return 'id';
	}

	/**
	 * 表示するデータを準備する
	 */
	public function prepare_items() {
		global $wpdb;

		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
		$order   = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';
		// sanitize $orderby
		$allowed_keys = array_keys( $this->get_sortable_columns() );
		if ( ! in_array( $orderby, $allowed_keys ) ) {
			$orderby = 'id';
		}
		// sanitize $order
		if ( ! in_array( $order, array( 'asc', 'desc' ) ) ) {
			$order = 'desc';
		}

		$per_page     = (int) 20;
		$current_page = (int) $this->get_pagenum();
		$start_from   = ( $current_page - 1 ) * $per_page;

		$keyvalues = array();
		if ( isset( $_REQUEST['s'] ) ) {
			$keyvalues[] = array(
				'key'   => 'AND (user_id LIKE %s OR event_id LIKE %s OR message LIKE %s ) ',
				'value' => array( '%' . $wpdb->esc_like( $_REQUEST['s'] ) . '%', '%' . $wpdb->esc_like( $_REQUEST['s'] ) . '%', '%' . $wpdb->esc_like( $_REQUEST['s'] ) . '%' ),
			);
		}

		$addtional_query = '';

		if ( ! empty( $keyvalues ) ) {
			$keys   = '';
			$values = array();
			foreach ( $keyvalues as $keyval ) {
				$keys  .= $keyval['key'];
				$values = array_merge( $values, $keyval['value'] );
			}
			// cut first "AND"
			$keys            = 'WHERE' . substr( $keys, 3 );
			$addtional_query = $wpdb->prepare( $keys, $values );
		}

		$table_name = $wpdb->prefix . lineconnectConst::TABLE_BOT_LOGS;
		$query      = "
            SELECT COUNT(id) 
            FROM {$table_name}
            {$addtional_query}";

		$total_items = $wpdb->get_var( $query );

		$history = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT id,bot_id,message_type,UNIX_TIMESTAMP(timestamp) as timestamp,event_type,source_type,user_id,message,event_id 
			FROM $table_name 
			{$addtional_query}
			ORDER BY {$orderby} {$order} 
			LIMIT %d, %d",
				$start_from,
				$per_page
			),
			ARRAY_A
		);

		$chatlog_list = array();
		foreach ( $history as $item ) {
			$row_data                 = array();
			$row_data['id']           = $item['id'];
			$row_data['bot_id']       = $item['bot_id'];
			$row_data['message_type'] = $item['message_type'];
			$row_data['timestamp']    = $item['timestamp'];
			$row_data['event_type']   = $item['event_type'];
			$row_data['source_type']  = $item['source_type'];
			$row_data['user_id']      = $item['user_id'];
			$row_data['message']      = $item['message'];
			$row_data['event_id']     = $item['event_id'];

			$chatlog_list[] = $row_data;
		}
		// error_log(print_r($chatlog_list,true));
		// $columns  = $this->get_columns();
		// $hidden   = array( );
		// $sortable = array( );
		// $this->_column_headers= array($columns, $hidden, $sortable);
		$this->items = $chatlog_list;
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				// 'total_pages' => 5, //設定してないと、ceil(total_items / per_page)
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * 1行分のデータを表示する
	 *
	 * @param array $item
	 */
	// public function single_row( $item ) {
	// }

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'timestamp':
			case 'message_type':
			case 'message':
			case 'bot_id':
			case 'user_id':
			case 'event_id':
			case 'event_type':
			case 'source_type':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}

	public function column_timestamp( $item ) {
		return date( 'Y/m/d H:i:s', $item['timestamp'] );
	}

	public function column_event_type( $item ) {
		return lineconnectConst::WH_EVENT_TYPE[ $item['event_type'] ];
	}

	public function column_source_type( $item ) {
		return lineconnectConst::WH_SOURCE_TYPE[ $item['source_type'] ];
	}

	public function column_message_type( $item ) {
		return lineconnectConst::WH_MESSAGE_TYPE[ $item['message_type'] ];
	}

	public function column_bot_id( $item ) {
		$channel = lineconnect::get_channel( $item['bot_id'] );
		if ( empty( $channel ) ) {
			return $item['bot_id'];
		} else {
			return $channel['name'];
		}
	}

	public function column_message( $item ) {
		$message = json_decode( $item['message'], true );
		if ( json_last_error() == JSON_ERROR_NONE ) {
			if ( $item['message_type'] == 1 && isset( $message['text'] ) ) {
				$msg_text = $message['text'];
			} elseif ( in_array( $item['message_type'], array( 2, 3, 4, 5 ) ) ) {
				// $msg_text = $message['type'];
				if ( isset( $message['file_path'] ) ) {
					$msg_text = $message['file_path'];
				}
			}
		}
		if ( ! empty( $msg_text ) ) {
			// return first 100 characters
			return mb_substr( $msg_text, 0, 100 );
		}
		return '';
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%s" />',
			$item['id']
		);
	}

	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $column_name === $primary ) {
			$line_deletelog_url = add_query_arg(
				array(
					'ids'    => $item['id'],
					'action' => 'delete',
				),
				admin_url( 'admin.php?page=' . lineconnect::SLUG__LINE_GPTLOG )
			);

			$actions = array(
				'delete' => sprintf( '<a href="%s">%s</a>', $line_deletelog_url, __( 'Delete', lineconnect::PLUGIN_NAME ) ),
			);

			return $this->row_actions( $actions );
		}
	}

	protected function get_sortable_columns() {
		return array(
			'id' => array( 'id', false ),
		);
	}

	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', lineconnect::PLUGIN_NAME ),
		);
	}

	public static function is_empty( $var = null ) {
		if ( empty( $var ) && 0 !== $var && '0' !== $var ) { // 論理型のfalseを取り扱う場合は、更に「&& false !== $var」を追加する
			return true;
		} else {
			return false;
		}
	}

	public function getFormattedDate( $unixTime, $format = null ) {
		$week = array(
			'Sun' => '日',
			'Mon' => '月',
			'Tue' => '火',
			'Wed' => '水',
			'Thu' => '木',
			'Fri' => '金',
			'Sat' => '土',
		);
		if ( ! isset( $format ) ) {
			$format = 'm/d (D) H:i';
		}
		$date_formatted = wp_date( $format, $unixTime );
		// replace English Day to Japanese
		$date_formatted = str_replace( array_keys( $week ), array_values( $week ), $date_formatted );
		return $date_formatted;
	}

	public function delete_items() {
		$ids = isset( $_REQUEST['ids'] ) ? $_REQUEST['ids'] : array();
		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}
		if ( ! empty( $ids ) ) {
			// make sql query
			global $wpdb;
			$table_name = $wpdb->prefix . lineconnectConst::TABLE_BOT_LOGS;
			// sanitize $ids
			$sanitized_ids = implode( ',', array_map( 'intval', $ids ) );
			$wpdb->query( "DELETE FROM $table_name WHERE id IN ($sanitized_ids)" );
			// check if success
			if ( $wpdb->last_error ) {
				// error_log($wpdb->last_error);
				wp_die( __( 'Error: Failed to delete items.', lineconnect::PLUGIN_NAME ) );
			}
			wp_safe_redirect( admin_url( 'admin.php?page=' . lineconnect::SLUG__LINE_GPTLOG ) );
		}
	}
}