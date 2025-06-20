<?php

/**
 * Lineconnect Const Class
 *
 * Const Class
 *
 * @category Components
 * @package  Const
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

use Shipweb\LineConnect\ActionExecute\ActionExecute;
use Shipweb\LineConnect\ActionFlow\ActionFlow;
use Shipweb\LineConnect\Scenario\Scenario;


class lineconnectConst {
	/**
	 * チャンネルごとに持つパラメーター（設定画面）
	 */
	// public static array $channnel_option;

	/**
	 * チャンネルごとに持つパラメーター（投稿画面）
	 */
	// public static array $channnel_field;

	/**
	 * 設定項目
	 */
	// public static array $settings_option;



	/**
	 * データ管理コマンド
	 */
	// public static array $management_command;

	/**
	 * LINE Connect アクション
	 */
	// public static array $lineconnect_action;


	/**
	 * LINE Connect RJSF translate string
	 */
	// public static array $lineconnect_rjsf_translate_string;

	/**
	 * LINE Connect Action schema version
	 */
	// const ACTION_SCHEMA_VERSION = 1;

	/**
	 * LINE Connect アクションスキーマ
	 */
	// public static array $lineconnect_action_schema;

	/**
	 * LINE Connect Action UI Schema
	 */
	// public static array $lineconnect_action_uischema;

	/**
	 * トリガー アクション
	 */
	// public static array $trigger_action;

	/**
	 * LINE Connect Trigger schema version
	 */
	// const TRIGGER_SCHEMA_VERSION = 1;

	/**
	 * LINE Connect Trigger schema
	 */
	// public static array $lineconnect_trigger_schema;

	/**
	 * LINE Connect Trigger Main UI Schema
	 */
	// public static array $lineconnect_trigger_uischema;

	/**
	 * LINE Connect Trigger Type schema
	 */
	// public static array $lineconnect_trigger_type_schema;

	/**
	 * LINE Connect Trigger Type UI schema
	 */
	// public static array $lineconnect_trigger_type_uischema;

	/**
	 * LINE Connect Trigger Types
	 */
	// public static array $lineconnect_trigger_types;

	/**
	 * LINE Connect Message schema version
	 */
	// const MESSAGE_SCHEMA_VERSION = 1;

	/**
	 * LINE Connect Message schema
	 */
	// public static array $lineconnect_message_schema;

	/**
	 * LINE Connect Message type schema
	 */
	// public static array $lineconnect_message_type_schema;

	/**
	 * LINE Connect Message type UI Schema
	 */
	// public static array $lineconnect_message_type_uischema;

	/**
	 * LINE Connect Message types
	 */
	// public static array $lineconnect_message_types;

	/**
	 * LINE Connect Message sub UI Schema
	 */
	// public static array $lineconnect_message_uischema;

	/**
	 * LINE アクションオブジェクトスキーマ
	 */
	// public static array $lineconnect_action_object_schema;

	/**
	 * LINE Connect Richmenu template bounds
	 */
	// public static array $lineconnect_richmenu_template_bounds;

	/**
	 * LINE Connect Richmenu template default data
	 */
	// public static array $lineconnect_richmenu_template_defalut_data;

	/**
	 * LINE Connect Richmenus schema
	 */
	// public static array $lineconnect_richmenu_schema;

	/**
	 * LINE Connect Richmenu sub UI Schema
	 */
	// public static array $lineconnect_richmenu_uischema;

	/**
	 * LINE Connect Audience schema version
	 */
	// const AUDIENCE_SCHEMA_VERSION = 1;

	/**
	 * LINE Connect Audience schema
	 */
	// public static array $lineconnect_audience_schema;

	/**
	 * LINE Connect Audience Main UI Schema
	 */
	// public static array $lineconnect_audience_uischema;

	/**
	 * LINE Connectアクションカスタム投稿タイプ Slug
	 */
	// const POST_TYPE_ACTION = lineconnect::PLUGIN_PREFIX . 'action';

	/**
	 * LINE Connectトリガーカスタム投稿タイプ Slug
	 */
	// const POST_TYPE_TRIGGER = lineconnect::PLUGIN_PREFIX . 'trigger';

	/**
	 * LINE Connectオーディエンスカスタム投稿タイプ Slug
	 */
	// const POST_TYPE_AUDIENCE = lineconnect::PLUGIN_PREFIX . 'audience';

	/**
	 * LINE Connect メッセージカスタム投稿タイプ Slug
	 */
	// const POST_TYPE_MESSAGE = lineconnect::PLUGIN_PREFIX . 'message';

	/**
	 * LINE Connectインタラクティブフォームカスタム投稿タイプ Slug
	 */
	// const POST_TYPE_INTERACTIVE_FORM = lineconnect::PLUGIN_PREFIX . 'interactive_form';


	/**
	 * イベントタイプ
	 */
	const WH_EVENT_TYPE = array(
		1  => 'message',
		2  => 'unsend',
		3  => 'follow',
		4  => 'unfollow',
		5  => 'join',
		6  => 'leave',
		7  => 'memberJoined',
		8  => 'memberLeft',
		9  => 'postback',
		10 => 'videoPlayComplete',
		11 => 'beacon',
		12 => 'accountLink',
		13 => 'things',
		14 => 'membership',
	);

	/**
	 * ソースタイプ
	 */
	const WH_SOURCE_TYPE = array(
		1  => 'user',
		2  => 'group',
		3  => 'room',
		11 => 'bot',
	);

	/**
	 * メッセージタイプ
	 */
	const WH_MESSAGE_TYPE = array(
		1 => 'text',
		2 => 'image',
		3 => 'video',
		4 => 'audio',
		5 => 'file',
		6 => 'location',
		7 => 'sticker',
	);



	// const ASSETS_SVG_FILENAME = 'assets/symbol-defs.svg';


	const LINE_MESSAGE_TYPES = array(
		'text',
		'textV2',
		'sticker',
		'image',
		'video',
		'audio',
		'location',
		'imagemap',
		'template',
		'flex',
	);

	/**
	 * LINE Connect Actions
	 */
	public static array $lineconnect_actions;


	public static function initialize() {


		self::$lineconnect_actions = array(
			'get_my_user_info'         => array(
				'title'       => __('Get my user information', lineconnect::PLUGIN_NAME),
				'description' => 'Get my information. ID, name, email, link status, etc.',
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'any',
			),
			'get_the_current_datetime' => array(
				'title'       => __('Get the current date and time', lineconnect::PLUGIN_NAME),
				'description' => 'Get the current date and time.',
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'any',
			),
			// 'render_template' => array(
			// 	'title'       => __('Render Twig template', lineconnect::PLUGIN_NAME),
			// 	'description' => 'Render Twig template with parameters.',
			// 	'parameters'  => array(
			// 		array(
			// 			'type'       => 'string',
			// 			'name'	   => 'body',
			// 			'description' => 'Twig template string. Such as {{ name }}',
			// 		),
			// 		array(
			// 			'type' => 'object',
			// 			'name' => 'args',
			// 			'description' => __('Arguments to insert into the template.', lineconnect::PLUGIN_NAME),
			// 			'additionalProperties' => array(
			// 				'type' => 'string',
			// 			),
			// 		),
			// 	),
			// 	'namespace' => 'lineconnectFunctions',
			// 	'role'      => 'any',
			// ),
			'WP_Query'                 => array(
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
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'any',
			),
			'WP_User_Query'            => array(
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
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'get_line_connect_message' => array(
				'title'       => __('Get LINE Connect message', lineconnect::PLUGIN_NAME),
				'description' => __('Get LINE Connect message.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_message',
						'name' => 'slc_message_id',
						'description' => __('LC Message id', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'object',
						'name' => 'args',
						'description' => __('Arguments to insert into the message', lineconnect::PLUGIN_NAME),
						'additionalProperties' => array(
							'type' => 'string',
						),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'any',
			),
			'get_text_message' => array(
				'title'       => __('Get LINE text message', lineconnect::PLUGIN_NAME),
				'description' => __('Get LINE text message.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'body',
						'description' => __('Message body', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'any',
			),
			'get_button_message' => array(
				'title'       => __('Get LC notify message', lineconnect::PLUGIN_NAME),
				'description' => __('Get LC notify message.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type'        => 'string',
						'name' =>  'title',
						'description' => __('Message title', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'body',
						'description' => __('Message body', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'thumb',
						'description' => __('Thumbnail url', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'type',
						'description' => __('Button action type', lineconnect::PLUGIN_NAME),
						'oneOf'       => array(
							array(
								'const' => 'message',
								'title' => __('Message', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'postback',
								'title' => __('Postback', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'uri',
								'title' => __('URI', lineconnect::PLUGIN_NAME),
							),
						),
					),
					array(
						'type' => 'string',
						'name' => 'label',
						'description' => __('Label', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'link',
						'description' => __('Link', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'displayText',
						'description' => __('Display Text', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'object',
						'name' => 'atts',
						'description' => __('Attributes', lineconnect::PLUGIN_NAME),
						'additionalProperties' => array(
							'type' => 'string',
						),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'any',
			),
			'get_raw_message' => array(
				'title'       => __('Get LINE message from Raw JSON', lineconnect::PLUGIN_NAME),
				'description' => __('Get LINE message from Raw JSON.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'json',
						'description' => __('Single raw Message JSON object', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'any',
			),
			'send_line_message' => array(
				'title'       => __('Send LINE message', lineconnect::PLUGIN_NAME),
				'description' => __('Send LINE message with Audience and Channel.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'message',
						'description' => __('LC message id, message text or message object', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user id of event source.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('Channel. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => lineconnectFunctions::class,
				'role'        => 'any',
			),
			'send_line_message_by_audience' => array(
				'title'       => __('Send LINE message with audience', lineconnect::PLUGIN_NAME),
				'description' => __('Send LINE message with audience.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_message',
						'name' => 'message',
						'description' => __('LC message id, text or message object', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'slc_audience',
						'name' => 'audience',
						'description' => __('LC Audience id or object', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'object',
						'name' => 'message_args',
						'description' => __('Arguments to insert into the message', lineconnect::PLUGIN_NAME),
						'additionalProperties' => array(
							'type' => 'string',
						),
					),
					array(
						'type' => 'object',
						'name' => 'audience_args',
						'description' => __('Arguments to insert into the audience', lineconnect::PLUGIN_NAME),
						'additionalProperties' => array(
							'type' => 'string',
						),
					),
					array(
						'type' => 'boolean',
						'name' => 'notification_disabled',
						'title' => __('Notification Disabled', lineconnect::PLUGIN_NAME),
						'description' => __('Notification disabled', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'any',
			),
			'send_mail_to_admin' => array(
				'title'       => __('Send mail to admin', lineconnect::PLUGIN_NAME),
				'description' => __('Send mail to admin.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'subject',
						'description' => __('Mail subject', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'body',
						'description' => __('Mail body', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'any',
			),
			'link_richmenu' => array(
				'title'       => __('Link rich menu', lineconnect::PLUGIN_NAME),
				'description' => __('Link rich menu with line user ID and Channel.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_richmenu',
						'name' => 'richmenu',
						'description' => __('Rich menu ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user id. Default value is LINE user id of event source.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'get_user_meta' => array(
				'title'       => __('Get user meta', lineconnect::PLUGIN_NAME),
				'description' => __('Get WordPress user meta value', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'integer',
						'name' => 'user_id',
						'description' => __('WordPress user ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'key',
						'description' => __('Meta key', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'update_user_meta' => array(
				'title'       => __('Update user meta', lineconnect::PLUGIN_NAME),
				'description' => __('Update or delete WordPress user meta value', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'integer',
						'name' => 'user_id',
						'description' => __('WordPress user ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'key',
						'description' => __('Meta key', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'value',
						'description' => __('Meta value. If empty, meta will be deleted.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'get_user_profile_value' => array(
				'title'       => __('Get LINE user profile value', lineconnect::PLUGIN_NAME),
				'description' => __('Get value from LINE user profile', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'key',
						'description' => __('Profile key to get', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'update_user_profile' => array(
				'title'       => __('Update LINE user profile', lineconnect::PLUGIN_NAME),
				'description' => __('Update or delete value in LINE user profile', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'key',
						'description' => __('Profile key to update', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'value',
						'description' => __('Profile value. If empty, key will be deleted.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'get_user_tags' => array(
				'title'       => __('Get LINE user tags', lineconnect::PLUGIN_NAME),
				'description' => __('Get LINE user tags.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'update_user_tags' => array(
				'title'       => __('Update LINE user tags', lineconnect::PLUGIN_NAME),
				'description' => __('Update or delete LINE user tags.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'array',
						'name' => 'tags',
						'description' => __('Array of tags to update. If empty, all tags will be deleted.', lineconnect::PLUGIN_NAME),
						'items' => array(
							'type' => 'string',
						),
						'required' => false,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'add_user_tags' => array(
				'title'       => __('Add LINE user tags', lineconnect::PLUGIN_NAME),
				'description' => __('Add LINE user tags.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'array',
						'name' => 'tags',
						'description' => __('Array of tags to add. If empty, tags will not be added.', lineconnect::PLUGIN_NAME),
						'items' => array(
							'type' => 'string',
						),
						'required' => false,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'remove_user_tags' => array(
				'title'       => __('Remove LINE user tags', lineconnect::PLUGIN_NAME),
				'description' => __('Remove LINE user tags.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'array',
						'name' => 'tags',
						'description' => __('Array of tags to remove. If empty, tags will not be removed.', lineconnect::PLUGIN_NAME),
						'items' => array(
							'type' => 'string',
						),
						'required' => false,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'start_scenario' => array(
				'title'       => __('Start LC Scenario', lineconnect::PLUGIN_NAME),
				'description' => __('Start LINE Connect Scenario.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_scenario',
						'name' => 'slc_scenario_id',
						'description' => __('Scenario ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'flg',
						'description' => __('Scenario restart flag', lineconnect::PLUGIN_NAME),
						'oneOf'       => array(
							array(
								'const' => 'none',
								'title' => __('Never restart', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'completed',
								'title' => __('Restart only completed', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'always',
								'title' => __('Always restart', lineconnect::PLUGIN_NAME),
							),
						),
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('Line user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'set_scenario_step' => array(
				'title'       => __('Set LC Scenario Step', lineconnect::PLUGIN_NAME),
				'description' => __('Set LINE Connect Scenario Step.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_scenario',
						'name' => 'slc_scenario_id',
						'description' => __('Scenario ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'step_id',
						'description' => __('ID of the step to set.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'next_date',
						'description' => __('Next date to execute the step. Absolute or relative.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'execute_scenario_step' => array(
				'title'       => __('Execute LC Scenario Step', lineconnect::PLUGIN_NAME),
				'description' => __('Execute LINE Connect Scenario Step.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_scenario',
						'name' => 'slc_scenario_id',
						'description' => __('Scenario ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'step_id',
						'description' => __('ID of the step to execute.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
			'change_scenario_status' => array(
				'title'       => __('Change LC scenario status', lineconnect::PLUGIN_NAME),
				'description' => __('Change LINE Connect scenario status.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_scenario',
						'name' => 'slc_scenario_id',
						'description' => __('Scenario ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'status',
						'description' => __('The status to set.', lineconnect::PLUGIN_NAME),
						'oneOf'	   => array(
							array(
								'const' => 'active',
								'title' => __('Active', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'error',
								'title' => __('Error', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'paused',
								'title' => __('Paused', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'completed',
								'title' => __('Completed', lineconnect::PLUGIN_NAME),
							),
						),
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'slc_channel',
						'name' => 'channel',
						'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
					),
				),
				'namespace'   => 'lineconnectFunctions',
				'role'        => 'administrator',
			),
		);



		/*
		self::$lineconnect_action_schema = apply_filters(
			lineconnect::FILTER_PREFIX . 'lineconnect_action_schema',
			array(
				// '$schema'     => 'https://json-schema.org/draft/draft-07/schema',
				// 'title'       => __( 'LINE Connect Action', lineconnect::PLUGIN_NAME ),
				// 'description' => __( 'Action used in LINE Connect', lineconnect::PLUGIN_NAME ),
				'type'        => 'object',
				'properties'  => array(

					'function'    => array(
						'type'        => 'string',
						'title'       => __('Function name', lineconnect::PLUGIN_NAME),
						'description' => __('Function name to be called', lineconnect::PLUGIN_NAME),
					),
					'namespace'   => array(
						'type'        => 'string',
						'title'       => __('Namespace', lineconnect::PLUGIN_NAME),
						'description' => __('Namespace of the function', lineconnect::PLUGIN_NAME),
					),
					'description' => array(
						'type'        => 'string',
						'title'       => __('Description', lineconnect::PLUGIN_NAME),
						'description' => __('Description of the action', lineconnect::PLUGIN_NAME),
					),
					'parameters'  => array(
						'$ref' => '#/definitions/parameters',
					),
					'role'        => array(
						'type'        => 'string',
						'title'       => __('Role', lineconnect::PLUGIN_NAME),
						'description' => __('User role to execute this function by AI Chat', lineconnect::PLUGIN_NAME),
						'oneOf'       => array(
							array(
								'const' => 'any',
								'title' => __('Anyone', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'read',
								'title' => 'Read',
							),
							array(
								'const' => 'edit_post',
								'title' => 'Edit Post',
							),
							array(
								'const' => 'publish_posts',
								'title' => 'Publish Posts',
							),
							array(
								'const' => 'upload_files',
								'title' => 'Upload files',
							),
							array(
								'const' => 'edit_pages',
								'title' => 'Edit pages',
							),
							array(
								'const' => 'edit_published_posts',
								'title' => 'Edit published posts',
							),
							array(
								'const' => 'edit_others_posts',
								'title' => 'Edit others posts',
							),
							array(
								'const' => 'unfiltered_html',
								'title' => 'Unfiltered html',
							),
							array(
								'const' => 'manage_links',
								'title' => 'Manage links',
							),
							array(
								'const' => 'manage_categories',
								'title' => 'Manage categories',
							),
							array(
								'const' => 'moderate_comments',
								'title' => 'Moderate comments',
							),
							array(
								'const' => 'import',
								'title' => 'Import',
							),
							array(
								'const' => 'manage_options',
								'title' => 'Manage options',
							),
							array(
								'const' => 'edit_files',
								'title' => 'Edit files',
							),
							array(
								'const' => 'edit_users',
								'title' => 'Edit users',
							),
							array(
								'const' => 'activate_plugins',
								'title' => 'Activate plugins',
							),
							array(
								'const' => 'edit_themes',
								'title' => 'Edit themes',
							),
							array(
								'const' => 'switch_themes',
								'title' => 'Switch themes',
							),
						),
					),
				),
				'required'    => array(
					'function',
				),
				'definitions' => array(
					'parameters' => array(
						'type'        => 'array',
						'title'       => __('Parameters', lineconnect::PLUGIN_NAME),
						'description' => __('Parameters of the function', lineconnect::PLUGIN_NAME),
						'items'       => array(
							'type'       => 'object',
							'properties' => array(
								'type'        => array(
									'$ref' => '#/definitions/type',
								),
								'description' => array(
									'type'        => 'string',
									'title'       => __('Description', lineconnect::PLUGIN_NAME),
									'description' => __('Description of the parameter', lineconnect::PLUGIN_NAME),
								),
								'required'    => array(
									'type'        => 'boolean',
									'title'       => __('Required', lineconnect::PLUGIN_NAME),
									'description' => __('Whether the parameter is required', lineconnect::PLUGIN_NAME),
								),
							),
							'allOf'      => array(
								array(
									'if'   => array(

										'properties' => array(
											'type' => array(
												'const' => 'object',
												'title' => __('Object', lineconnect::PLUGIN_NAME),
											),
										),
									),
									'then' => array(
										'properties' => array(
											'properties' => array(
												'title' => __('Object property', lineconnect::PLUGIN_NAME),
												'type'  => 'object',
												'additionalProperties' => array(
													'$ref' => '#/definitions/property',
												),
											),
										),
									),

								),
								array(
									'if'   => array(
										'properties' => array(
											'type' => array(
												'const' => 'array',
												'title' => __('Array', lineconnect::PLUGIN_NAME),
											),
										),
									),
									'then' => array(
										'properties' => array(
											'items' => array(
												'title' => __('Array element', lineconnect::PLUGIN_NAME),
												'type'  => 'object',
												'properties' => array(
													'type' => array(
														'$ref' => '#/definitions/type',
													),
													'properties' => array(
														'title' => __('Property', lineconnect::PLUGIN_NAME),
														'description' => __('Object properties when data type is object', lineconnect::PLUGIN_NAME),
														'type'  => 'object',
														'additionalProperties' => array(
															'$ref' => '#/definitions/property',
														),
													),
												),
											),
										),
									),
								),
								array(
									'if'   => array(
										'properties' => array(
											'type' => array(
												'const' => 'string',
												'title' => __('String', lineconnect::PLUGIN_NAME),
											),
										),
									),
									'then' => array(
										'properties' => array(
											'enum' => array(
												'title' => __('Enum list', lineconnect::PLUGIN_NAME),
												'description' => __('List of enum value', lineconnect::PLUGIN_NAME),
												'type'  => 'array',
												'items' => array(
													'type' => 'string',
												),
											),
										),
									),

								),
							),
						),
					),
					'property'   => array(
						// 'title'      => 'Property',
						'type'       => 'object',
						'properties' => array(
							'type'        => array(
								'$ref' => '#/definitions/type',
							),
							'description' => array(
								'type'  => 'string',
								'title' => __('Description', lineconnect::PLUGIN_NAME),
								// 'description' => __( 'Description of the property', lineconnect::PLUGIN_NAME ),
							),
							'properties'  => array(
								'title'                => __('Addtional Property', lineconnect::PLUGIN_NAME),
								'type'                 => 'object',
								'additionalProperties' => array(
									'$ref' => '#/definitions/property',
								),
							),
							'enum'        => array(
								'title'       => __('Enum list', lineconnect::PLUGIN_NAME),
								'description' => __('List of enum value', lineconnect::PLUGIN_NAME),
								'type'        => 'array',
								'items'       => array(
									'type' => 'string',
								),
							),
						),
					),
					'type'       => array(
						'type'  => 'string',
						'title' => __('Data Type', lineconnect::PLUGIN_NAME),
						// 'description' => __( 'Type of the data', lineconnect::PLUGIN_NAME ),
						'oneOf' => array(
							array(
								'const' => 'string',
								'title' => __('String', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'number',
								'title' => __('Number', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'integer',
								'title' => __('Integer', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'boolean',
								'title' => __('Boolean', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'array',
								'title' => __('Array', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'object',
								'title' => __('Object', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'null',
								'title' => __('Null', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'slc_message',
								'title' => __('LINE Connect Message', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'slc_channel',
								'title' => __('LINE Messaging API Channel', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'slc_richmenu',
								'title' => __('Richmenu', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'slc_richmenualias',
								'title' => __('Richmenu Alias', lineconnect::PLUGIN_NAME),
							),
						),
					),
				),
			)
		);
		*/
		/*
		self::$lineconnect_action_uischema = apply_filters(
			lineconnect::FILTER_PREFIX . 'lineconnect_action_uischema',
			array(
				'ui:submitButtonOptions' => array(
					'norender' => true,
				),
				'parameters'             => array(
					'items' => array(
						'required' => array(
							'ui:widget' => 'select',
						),
						'items'    => array(
							'type' => array(
								'ui:description' => __('Data type of each array element', lineconnect::PLUGIN_NAME),
							),
						),
					),
				),
			)
		);
		
		// Trigger type schema
		self::$lineconnect_trigger_type_schema = array(
			'type'       => 'object',
			'properties' => array(
				'type' => array(
					'type'  => 'string',
					'title' => __('Trigger type', lineconnect::PLUGIN_NAME),
					'oneOf' => array(
						array(
							'const' => 'webhook',
							'title' => __('Webhook', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'schedule',
							'title' => __('Schedule', lineconnect::PLUGIN_NAME),
						),
					),
				),
			),
		);

		// Trigger type UI schema
		self::$lineconnect_trigger_type_uischema = array(
			'ui:submitButtonOptions' => array(
				'norender' => true,
			),
			'type' => array(
				'ui:description' => __('Choose trigger type.', lineconnect::PLUGIN_NAME),
				'ui:widget' => 'radio',
				'ui:options' => array(
					'inline' => true,
				),
			),
		);
		*/

		// Message type schema


		// Message type UI schema




		// action object schema


		// 　Message schema


		// Message Sub UI schema


		// Richmenu type schema

		// Richmenu type UI schema
		// self::$lineconnect_richmenu_type_uischema = array(
		// 	'type' => array(
		// 		'ui:widget' => 'radio',
		// 		'ui:enableMarkdownInDescription' => true,
		// 	),
		// );

		// Richmenu default data

		// Richmenu schema

		// Richmenu UI schema

		// Audience schema

		// Audience type UI schema


	}
}
