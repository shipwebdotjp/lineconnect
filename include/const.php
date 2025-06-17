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
	public static array $channnel_option;

	/**
	 * チャンネルごとに持つパラメーター（投稿画面）
	 */
	public static array $channnel_field;

	/**
	 * 設定項目
	 */
	public static array $settings_option;

	/**
	 * 変数項目
	 */
	public static array $variables_option;

	/**
	 * データ管理コマンド
	 */
	public static array $management_command;

	/**
	 * LINE Connect アクション
	 */
	public static array $lineconnect_action;


	/**
	 * LINE Connect RJSF translate string
	 */
	public static array $lineconnect_rjsf_translate_string;

	/**
	 * LINE Connect Action schema version
	 */
	const ACTION_SCHEMA_VERSION = 1;

	/**
	 * LINE Connect アクションスキーマ
	 */
	public static array $lineconnect_action_schema;

	/**
	 * LINE Connect Action UI Schema
	 */
	public static array $lineconnect_action_uischema;

	/**
	 * トリガー アクション
	 */
	// public static array $trigger_action;

	/**
	 * LINE Connect Trigger schema version
	 */
	const TRIGGER_SCHEMA_VERSION = 1;

	/**
	 * LINE Connect Trigger schema
	 */
	public static array $lineconnect_trigger_schema;

	/**
	 * LINE Connect Trigger Main UI Schema
	 */
	public static array $lineconnect_trigger_uischema;

	/**
	 * LINE Connect Trigger Type schema
	 */
	public static array $lineconnect_trigger_type_schema;

	/**
	 * LINE Connect Trigger Type UI schema
	 */
	public static array $lineconnect_trigger_type_uischema;

	/**
	 * LINE Connect Trigger Types
	 */
	public static array $lineconnect_trigger_types;

	/**
	 * LINE Connect Message schema version
	 */
	const MESSAGE_SCHEMA_VERSION = 1;

	/**
	 * LINE Connect Message schema
	 */
	public static array $lineconnect_message_schema;

	/**
	 * LINE Connect Message type schema
	 */
	public static array $lineconnect_message_type_schema;

	/**
	 * LINE Connect Message type UI Schema
	 */
	public static array $lineconnect_message_type_uischema;

	/**
	 * LINE Connect Message types
	 */
	public static array $lineconnect_message_types;

	/**
	 * LINE Connect Message sub UI Schema
	 */
	public static array $lineconnect_message_uischema;

	/**
	 * LINE アクションオブジェクトスキーマ
	 */
	public static array $lineconnect_action_object_schema;

	/**
	 * LINE Connect Richmenu template bounds
	 */
	public static array $lineconnect_richmenu_template_bounds;

	/**
	 * LINE Connect Richmenu template default data
	 */
	public static array $lineconnect_richmenu_template_defalut_data;

	/**
	 * LINE Connect Richmenus schema
	 */
	public static array $lineconnect_richmenu_schema;

	/**
	 * LINE Connect Richmenu sub UI Schema
	 */
	public static array $lineconnect_richmenu_uischema;

	/**
	 * LINE Connect Audience schema version
	 */
	const AUDIENCE_SCHEMA_VERSION = 1;

	/**
	 * LINE Connect Audience schema
	 */
	public static array $lineconnect_audience_schema;

	/**
	 * LINE Connect Audience Main UI Schema
	 */
	public static array $lineconnect_audience_uischema;

	/**
	 * LINE Connectアクションカスタム投稿タイプ Slug
	 */
	// const POST_TYPE_ACTION = lineconnect::PLUGIN_PREFIX . 'action';

	/**
	 * LINE Connectトリガーカスタム投稿タイプ Slug
	 */
	const POST_TYPE_TRIGGER = lineconnect::PLUGIN_PREFIX . 'trigger';

	/**
	 * LINE Connectオーディエンスカスタム投稿タイプ Slug
	 */
	const POST_TYPE_AUDIENCE = lineconnect::PLUGIN_PREFIX . 'audience';

	/**
	 * LINE Connect メッセージカスタム投稿タイプ Slug
	 */
	const POST_TYPE_MESSAGE = lineconnect::PLUGIN_PREFIX . 'message';

	/**
	 * LINE Connectインタラクティブフォームカスタム投稿タイプ Slug
	 */
	const POST_TYPE_INTERACTIVE_FORM = lineconnect::PLUGIN_PREFIX . 'interactive_form';


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

	/**
	 * ボットとのチャットログ MySQLテーブル名
	 */
	const TABLE_BOT_LOGS = 'lineconnect_bot_logs';

	/**
	 * LINEユーザー情報 MySQLテーブル名
	 */
	const TABLE_LINE_ID = 'lineconnect_line_id';

	/**
	 * LINE公式アカウント統計ログ MySQLテーブル名
	 */
	const TABLE_LINE_STATS = 'lineconnect_line_stats';

	/**
	 * LINE公式アカウント日々の増減数ログ MySQLテーブル名
	 */
	const TABLE_LINE_DAILY = 'lineconnect_line_daily';

	/**
	 * DBバージョンのキー
	 */
	const DB_VERSION_KEY = 'db_version';

	const ASSETS_SVG_FILENAME = 'assets/symbol-defs.svg';

	const MIME_MAP = array(
		'video/3gpp2'                          => '3g2',
		'video/3gp'                            => '3gp',
		'video/3gpp'                           => '3gp',
		'application/x-compressed'             => '7zip',
		'audio/x-acc'                          => 'aac',
		'audio/ac3'                            => 'ac3',
		'application/postscript'               => 'ai',
		'audio/x-aiff'                         => 'aif',
		'audio/aiff'                           => 'aif',
		'audio/x-au'                           => 'au',
		'video/x-msvideo'                      => 'avi',
		'video/msvideo'                        => 'avi',
		'video/avi'                            => 'avi',
		'application/x-troff-msvideo'          => 'avi',
		'application/macbinary'                => 'bin',
		'application/mac-binary'               => 'bin',
		'application/x-binary'                 => 'bin',
		'application/x-macbinary'              => 'bin',
		'image/bmp'                            => 'bmp',
		'image/x-bmp'                          => 'bmp',
		'image/x-bitmap'                       => 'bmp',
		'image/x-xbitmap'                      => 'bmp',
		'image/x-win-bitmap'                   => 'bmp',
		'image/x-windows-bmp'                  => 'bmp',
		'image/ms-bmp'                         => 'bmp',
		'image/x-ms-bmp'                       => 'bmp',
		'application/bmp'                      => 'bmp',
		'application/x-bmp'                    => 'bmp',
		'application/x-win-bitmap'             => 'bmp',
		'application/cdr'                      => 'cdr',
		'application/coreldraw'                => 'cdr',
		'application/x-cdr'                    => 'cdr',
		'application/x-coreldraw'              => 'cdr',
		'image/cdr'                            => 'cdr',
		'image/x-cdr'                          => 'cdr',
		'zz-application/zz-winassoc-cdr'       => 'cdr',
		'application/mac-compactpro'           => 'cpt',
		'application/pkix-crl'                 => 'crl',
		'application/pkcs-crl'                 => 'crl',
		'application/x-x509-ca-cert'           => 'crt',
		'application/pkix-cert'                => 'crt',
		'text/css'                             => 'css',
		'text/x-comma-separated-values'        => 'csv',
		'text/comma-separated-values'          => 'csv',
		'application/vnd.msexcel'              => 'csv',
		'application/x-director'               => 'dcr',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
		'application/x-dvi'                    => 'dvi',
		'message/rfc822'                       => 'eml',
		'application/x-msdownload'             => 'exe',
		'video/x-f4v'                          => 'f4v',
		'audio/x-flac'                         => 'flac',
		'video/x-flv'                          => 'flv',
		'image/gif'                            => 'gif',
		'application/gpg-keys'                 => 'gpg',
		'application/x-gtar'                   => 'gtar',
		'application/x-gzip'                   => 'gzip',
		'application/mac-binhex40'             => 'hqx',
		'application/mac-binhex'               => 'hqx',
		'application/x-binhex40'               => 'hqx',
		'application/x-mac-binhex40'           => 'hqx',
		'text/html'                            => 'html',
		'image/x-icon'                         => 'ico',
		'image/x-ico'                          => 'ico',
		'image/vnd.microsoft.icon'             => 'ico',
		'text/calendar'                        => 'ics',
		'application/java-archive'             => 'jar',
		'application/x-java-application'       => 'jar',
		'application/x-jar'                    => 'jar',
		'image/jp2'                            => 'jp2',
		'video/mj2'                            => 'jp2',
		'image/jpx'                            => 'jp2',
		'image/jpm'                            => 'jp2',
		'image/jpeg'                           => 'jpeg',
		'image/pjpeg'                          => 'jpeg',
		'application/x-javascript'             => 'js',
		'application/json'                     => 'json',
		'text/json'                            => 'json',
		'application/vnd.google-earth.kml+xml' => 'kml',
		'application/vnd.google-earth.kmz'     => 'kmz',
		'text/x-log'                           => 'log',
		'audio/x-m4a'                          => 'm4a',
		'audio/mp4'                            => 'm4a',
		'application/vnd.mpegurl'              => 'm4u',
		'audio/midi'                           => 'mid',
		'application/vnd.mif'                  => 'mif',
		'video/quicktime'                      => 'mov',
		'video/x-sgi-movie'                    => 'movie',
		'audio/mpeg'                           => 'mp3',
		'audio/mpg'                            => 'mp3',
		'audio/mpeg3'                          => 'mp3',
		'audio/mp3'                            => 'mp3',
		'video/mp4'                            => 'mp4',
		'video/mpeg'                           => 'mpeg',
		'application/oda'                      => 'oda',
		'audio/ogg'                            => 'ogg',
		'video/ogg'                            => 'ogg',
		'application/ogg'                      => 'ogg',
		'font/otf'                             => 'otf',
		'application/x-pkcs10'                 => 'p10',
		'application/pkcs10'                   => 'p10',
		'application/x-pkcs12'                 => 'p12',
		'application/x-pkcs7-signature'        => 'p7a',
		'application/pkcs7-mime'               => 'p7c',
		'application/x-pkcs7-mime'             => 'p7c',
		'application/x-pkcs7-certreqresp'      => 'p7r',
		'application/pkcs7-signature'          => 'p7s',
		'application/pdf'                      => 'pdf',
		'application/octet-stream'             => 'pdf',
		'application/x-x509-user-cert'         => 'pem',
		'application/x-pem-file'               => 'pem',
		'application/pgp'                      => 'pgp',
		'application/x-httpd-php'              => 'php',
		'application/php'                      => 'php',
		'application/x-php'                    => 'php',
		'text/php'                             => 'php',
		'text/x-php'                           => 'php',
		'application/x-httpd-php-source'       => 'php',
		'image/png'                            => 'png',
		'image/x-png'                          => 'png',
		'application/powerpoint'               => 'ppt',
		'application/vnd.ms-powerpoint'        => 'ppt',
		'application/vnd.ms-office'            => 'ppt',
		'application/msword'                   => 'doc',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
		'application/x-photoshop'              => 'psd',
		'image/vnd.adobe.photoshop'            => 'psd',
		'audio/x-realaudio'                    => 'ra',
		'audio/x-pn-realaudio'                 => 'ram',
		'application/x-rar'                    => 'rar',
		'application/rar'                      => 'rar',
		'application/x-rar-compressed'         => 'rar',
		'audio/x-pn-realaudio-plugin'          => 'rpm',
		'application/x-pkcs7'                  => 'rsa',
		'text/rtf'                             => 'rtf',
		'text/richtext'                        => 'rtx',
		'video/vnd.rn-realvideo'               => 'rv',
		'application/x-stuffit'                => 'sit',
		'application/smil'                     => 'smil',
		'text/srt'                             => 'srt',
		'image/svg+xml'                        => 'svg',
		'application/x-shockwave-flash'        => 'swf',
		'application/x-tar'                    => 'tar',
		'application/x-gzip-compressed'        => 'tgz',
		'image/tiff'                           => 'tiff',
		'font/ttf'                             => 'ttf',
		'text/plain'                           => 'txt',
		'text/x-vcard'                         => 'vcf',
		'application/videolan'                 => 'vlc',
		'text/vtt'                             => 'vtt',
		'audio/x-wav'                          => 'wav',
		'audio/wave'                           => 'wav',
		'audio/wav'                            => 'wav',
		'application/wbxml'                    => 'wbxml',
		'video/webm'                           => 'webm',
		'image/webp'                           => 'webp',
		'audio/x-ms-wma'                       => 'wma',
		'application/wmlc'                     => 'wmlc',
		'video/x-ms-wmv'                       => 'wmv',
		'video/x-ms-asf'                       => 'wmv',
		'font/woff'                            => 'woff',
		'font/woff2'                           => 'woff2',
		'application/xhtml+xml'                => 'xhtml',
		'application/excel'                    => 'xl',
		'application/msexcel'                  => 'xls',
		'application/x-msexcel'                => 'xls',
		'application/x-ms-excel'               => 'xls',
		'application/x-excel'                  => 'xls',
		'application/x-dos_ms_excel'           => 'xls',
		'application/xls'                      => 'xls',
		'application/x-xls'                    => 'xls',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
		'application/vnd.ms-excel'             => 'xlsx',
		'application/xml'                      => 'xml',
		'text/xml'                             => 'xml',
		'text/xsl'                             => 'xsl',
		'application/xspf+xml'                 => 'xspf',
		'application/x-compress'               => 'z',
		'application/x-zip'                    => 'zip',
		'application/zip'                      => 'zip',
		'application/x-zip-compressed'         => 'zip',
		'application/s-compressed'             => 'zip',
		'multipart/x-zip'                      => 'zip',
		'text/x-scriptzsh'                     => 'zsh',
	);
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
		self::$channnel_field = apply_filters(
			lineconnect::FILTER_PREFIX . 'channnel_field',
			array(
				'send-checkbox'   => __('Send update notification', lineconnect::PLUGIN_NAME),
				'role-selectbox'  => __('Send target:', lineconnect::PLUGIN_NAME),
				'template-selectbox' => __('Message template:', lineconnect::PLUGIN_NAME),
				'future-checkbox' => __('Send when a future post is published', lineconnect::PLUGIN_NAME),
			)
		);

		self::$channnel_option = apply_filters(
			lineconnect::FILTER_PREFIX . 'channnel_option',
			array(
				'name'                 => __('Channel name', lineconnect::PLUGIN_NAME),
				'channel-access-token' => __('Channel access token', lineconnect::PLUGIN_NAME),
				'channel-secret'       => __('Channel secret', lineconnect::PLUGIN_NAME),
				'role'                 => __('Default target role', lineconnect::PLUGIN_NAME),
				'linked-richmenu'      => __('Rich menu ID for linked users', lineconnect::PLUGIN_NAME),
				'unlinked-richmenu'    => __('Rich menu ID for unlinked users', lineconnect::PLUGIN_NAME),
			)
		);

		self::$management_command = array(
			'clear_richmenu_cache' => array(
				'type' => 'button',
				'label' => __('Clear the rich menu cache', lineconnect::PLUGIN_NAME),
				'description' => __('Clear the cache of the rich menu list.', lineconnect::PLUGIN_NAME),
			),
			// 'delete_all_data'	  => array(
			// 	'type' => 'button',
			// 	'label' => __( 'Delete all plugin data', lineconnect::PLUGIN_NAME ),
			// 	'description' => __( 'Delete all plugin data.', lineconnect::PLUGIN_NAME ),
			// ),
		);

		self::$settings_option = apply_filters(
			lineconnect::FILTER_PREFIX . 'settings_option',
			array(
				'channel' => array(
					'prefix' => '1',
					'name'   => __('Channel', lineconnect::PLUGIN_NAME),
					'fields' => array(),
				),
				'connect' => array(
					'prefix' => '2',
					'name'   => __('Link', lineconnect::PLUGIN_NAME),
					'fields' => array(
						'login_page_url'        => array(
							'type'     => 'text',
							'label'    => __('Login page URL', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => 'wp-login.php',
							'hint'     => __('Enter the URL of the login page as a path relative to the site URL.', lineconnect::PLUGIN_NAME),
						),
						'enable_link_autostart' => array(
							'type'     => 'select',
							'label'    => __('Automatically initiate linkage', lineconnect::PLUGIN_NAME),
							'required' => true,
							'list'     => array(
								'off' => __('Disabled', lineconnect::PLUGIN_NAME),
								'on'  => __('Enabled', lineconnect::PLUGIN_NAME),
							),
							'default'  => 'on',
							'hint'     => __('This setting determines whether or not to automatically initiate linkage When user add an official account as a friend.', lineconnect::PLUGIN_NAME),
						),
						'link_start_keyword'    => array(
							'type'     => 'text',
							'label'    => __('Account link/unlink start keywords', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => __('Account link', lineconnect::PLUGIN_NAME),
						),
						'link_start_title'      => array(
							'type'     => 'text',
							'label'    => __('Message title for account linkage initiation', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => __('Start account linkage', lineconnect::PLUGIN_NAME),
						),
						'link_start_body'       => array(
							'type'     => 'text',
							'label'    => __('Message body for account linkage initiation', lineconnect::PLUGIN_NAME),
							'required' => true,
							'size'     => 60,
							'default'  => __('Start the linkage. Please login at the link.', lineconnect::PLUGIN_NAME),
						),
						'link_start_button'     => array(
							'type'     => 'text',
							'label'    => __('Message button label to start account linkage', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => __('Start linkage', lineconnect::PLUGIN_NAME),
						),
						'link_finish_body'      => array(
							'type'     => 'text',
							'label'    => __('Account Linkage Completion Message', lineconnect::PLUGIN_NAME),
							'required' => true,
							'size'     => 60,
							'default'  => __('Account linkage completed.', lineconnect::PLUGIN_NAME),
						),
						'link_failed_body'      => array(
							'type'     => 'text',
							'label'    => __('Account Linkage Failure Messages', lineconnect::PLUGIN_NAME),
							'required' => true,
							'size'     => 60,
							'default'  => __('Account linkage failed.', lineconnect::PLUGIN_NAME),
						),
						'unlink_start_title'    => array(
							'type'     => 'text',
							'label'    => __('Message title for account unlinking initiation', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => __('Unlink account', lineconnect::PLUGIN_NAME),
						),
						'unlink_start_body'     => array(
							'type'     => 'text',
							'label'    => __('Message body for account unlinking initiation', lineconnect::PLUGIN_NAME),
							'required' => true,
							'size'     => 60,
							'default'  => __('Would you like to unlink your account?', lineconnect::PLUGIN_NAME),
						),
						'unlink_start_button'   => array(
							'type'     => 'text',
							'label'    => __('Message button label to start account unlinking', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => __('Unlink account', lineconnect::PLUGIN_NAME),
						),
						'unlink_finish_body'    => array(
							'type'     => 'text',
							'label'    => __('Account Unlinking Completion Message', lineconnect::PLUGIN_NAME),
							'required' => true,
							'size'     => 60,
							'default'  => __('Account linkage has been successfully unlinked.', lineconnect::PLUGIN_NAME),
						),
						'unlink_failed_body'    => array(
							'type'     => 'text',
							'label'    => __('Account Unlinking Failure Message', lineconnect::PLUGIN_NAME),
							'required' => true,
							'size'     => 60,
							'default'  => __('Failed to unlink the account.', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'publish' => array(
					'prefix' => '3',
					'name'   => __('Update Notification', lineconnect::PLUGIN_NAME),
					'fields' => array(
						'send_post_types'       => array(
							'type'     => 'multiselect',
							'label'    => __('Post types', lineconnect::PLUGIN_NAME),
							'required' => false,
							'list'     => array(
								'post' => __('Post', lineconnect::PLUGIN_NAME),
								'page' => __('Page', lineconnect::PLUGIN_NAME),
							),
							'default'  => array('post'),
							'isMulti'  => true,
							'hint'     => __('The post type to be notified. The Send LINE checkbox will appear on the edit screen of the selected post type.', lineconnect::PLUGIN_NAME),
						),
						'default_send_checkbox' => array(
							'type'     => 'select',
							'label'    => __('Default value of "Send update notification" checkbox', lineconnect::PLUGIN_NAME),
							'required' => true,
							'list'     => array(
								'on'  => __('Checked', lineconnect::PLUGIN_NAME),
								'off' => __('Unchecked', lineconnect::PLUGIN_NAME),
								'new' => __('Unchecked if published', lineconnect::PLUGIN_NAME),
							),
							'default'  => 'new',
							'hint'     => __('Default value setting for the "Send update notification" check box when editing an article.', lineconnect::PLUGIN_NAME),
						),
						'default_send_template' => array(
							'type'     => 'select',
							'label'    => __('Default template of notification message.', lineconnect::PLUGIN_NAME),
							'required' => true,
							'list'     => array(
								0  => __('Default template', lineconnect::PLUGIN_NAME),
							),
							'default'  => 0,
							'hint'     => __('Default value setting for the "Message template" select box when editing an article.', lineconnect::PLUGIN_NAME),
						),
						'more_label'            => array(
							'type'     => 'text',
							'label'    => __('"More" link label', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => __('Read more', lineconnect::PLUGIN_NAME),
						),
						'send_new_comment'      => array(
							'type'     => 'checkbox',
							'label'    => __('Send notification to posters when comments are received', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => false,
							'hint'     => __('This setting determines whether or not to notify the poster of an article when there is a comment on the article.', lineconnect::PLUGIN_NAME),
						),
						'comment_read_label'    => array(
							'type'     => 'text',
							'label'    => __('"Read comment" link label', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => __('Read comment', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'style'   => array(
					'prefix' => '4',
					'name'   => __('Style', lineconnect::PLUGIN_NAME),
					'fields' => array(
						'image_aspectmode'             => array(
							'type'     => 'select',
							'label'    => __('Image fit mode', lineconnect::PLUGIN_NAME),
							'required' => true,
							'list'     => array(
								'cover' => __('cover', lineconnect::PLUGIN_NAME),
								'fit'   => __('contain', lineconnect::PLUGIN_NAME),
							),
							'default'  => 'cover',
							'hint'     => __('cover: The replaced content is sized to maintain its aspect ratio while filling the image area. If the image\'s aspect ratio does not match the aspect ratio of its area, then the image will be clipped to fit. \n contain: The replaced image is scaled to maintain its aspect ratio while fitting within the image area. The entire image is made to fill the box, while preserving its aspect ratio, so the image will be "letterboxed" if its aspect ratio does not match the aspect ratio of the area.', lineconnect::PLUGIN_NAME),
						),
						'image_aspectrate'             => array(
							'type'     => 'text',
							'label'    => __('Image area aspect ratio', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => '2:1',
							'regex'    => '/^[1-9]+[0-9]*:[1-9]+[0-9]*$/',
							'hint'     => __('The aspect ratio of the image area. The height cannot be greater than three times the width.', lineconnect::PLUGIN_NAME),
						),
						'title_backgraound_color'      => array(
							'type'     => 'color',
							'label'    => __('Background color of the message', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => '#FFFFFF',
							'hint'     => __('The background color of the notification message.', lineconnect::PLUGIN_NAME),
						),
						'title_text_color'             => array(
							'type'     => 'color',
							'label'    => __('Title text color', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => '#000000',
							'hint'     => __('The title text color of the notification message.', lineconnect::PLUGIN_NAME),
						),
						'body_text_color'              => array(
							'type'     => 'color',
							'label'    => __('Body text color', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => '#000000',
							'hint'     => __('The body text color of the notification message.', lineconnect::PLUGIN_NAME),
						),
						'link_button_style'            => array(
							'type'     => 'select',
							'label'    => __('Link style', lineconnect::PLUGIN_NAME),
							'required' => true,
							'list'     => array(
								'button' => __('Button', lineconnect::PLUGIN_NAME),
								'link'   => __('HTML Link', lineconnect::PLUGIN_NAME),
							),
							'default'  => 'link',
							'hint'     => __('Button: button style. Link: HTML link style', lineconnect::PLUGIN_NAME),
						),
						'link_text_color'              => array(
							'type'     => 'color',
							'label'    => __('Link text color', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => '#1e90ff',
							'hint'     => __('The link text color of the notification message.', lineconnect::PLUGIN_NAME),
						),
						'link_button_background_color' => array(
							'type'     => 'color',
							'label'    => __('Link button background color', lineconnect::PLUGIN_NAME),
							'required' => true,
							'default'  => '#00ff00',
							'hint'     => __('The link button background color of the notification message.', lineconnect::PLUGIN_NAME),
						),
						'title_rows'                   => array(
							'type'     => 'spinner',
							'label'    => __('Max title lines', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => 3,
							'hint'     => __('This is the setting for the maximum number of lines of title to be displayed in the notification message.', lineconnect::PLUGIN_NAME),
						),
						'body_rows'                    => array(
							'type'     => 'spinner',
							'label'    => __('Max body lines', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => 5,
							'hint'     => __('This is the setting for the maximum number of lines of text to be displayed in the notification message. Apart from this, it can be truncated to a maximum of 500 characters.', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'chat'    => array(
					'prefix' => '5',
					'name'   => __('AI Chat', lineconnect::PLUGIN_NAME),
					'fields' => array(
						'enableChatbot'            => array(
							'type'     => 'select',
							'label'    => __('Auto response by AI', lineconnect::PLUGIN_NAME),
							'required' => true,
							'list'     => array(
								'off' => __('Disabled', lineconnect::PLUGIN_NAME),
								'on'  => __('Enabled', lineconnect::PLUGIN_NAME),
							),
							'default'  => 'off',
							'hint'     => __('This setting determines whether or not to use AI auto-response for messages sent to official line account.', lineconnect::PLUGIN_NAME),
						),
						'openai_endpoint' => array(
							'type'     => 'text',
							'label'    => __('OpenAI API Endpoint', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => 'https://api.openai.com/v1/chat/completions',
							'size'     => 60,
							'hint'     => __('Enter your OpenAI (or Compatible) API Endpoint.', lineconnect::PLUGIN_NAME),
						),
						'openai_secret'            => array(
							'type'     => 'text',
							'label'    => __('OpenAI API Key', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => '',
							'size'     => 60,
							'hint'     => __('Enter your OpenAI (or Compatible) API Key.', lineconnect::PLUGIN_NAME),
						),
						'openai_model'             => array(
							'type'     => 'text',
							'label'    => __('Model', lineconnect::PLUGIN_NAME),
							'required' => false,
							/*
							'list'     => array(
								'gpt-3.5-turbo'     => 'GPT-3.5 turbo',
								'gpt-3.5-turbo-16k' => 'GPT-3.5 turbo 16k(Legacy)',
								'gpt-4'             => 'GPT-4',
								'gpt-4-32k'         => 'GPT-4 32k',
								'gpt-4-turbo-preview'         => 'GPT-4 turbo',
								'gpt-4o'             => 'GPT-4o',
								'gpt-4o-mini'             => 'GPT-4o mini',
								'gpt-4.1'			 => 'GPT-4.1',
								'gpt-4.1-mini'			 => 'GPT-4.1 mini',
								'gpt-4.1-nano'			 => 'GPT-4.1 nano',
								'o3'			 => 'o3',
								'o4-mini'			 => 'o4-mini',
							),
							*/
							'default'  => 'gpt-4o-mini',
							'size'     => 60,
							'hint'     => __('This is a setting for which model to use. Such as gpt-4o-mini', lineconnect::PLUGIN_NAME),
						),
						'openai_system'            => array(
							'type'     => 'textarea',
							'label'    => __('System prompt', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => '',
							'rows'     => 7,
							'cols'     => 80,
							'hint'     => __('The initial text or instruction provided to the language model before interacting with it in a conversational manner.', lineconnect::PLUGIN_NAME),
						),
						'openai_function_call'     => array(
							'type'     => 'select',
							'label'    => __('Function Calling', lineconnect::PLUGIN_NAME),
							'required' => true,
							'list'     => array(
								'off' => __('Disabled', lineconnect::PLUGIN_NAME),
								'on'  => __('Enabled', lineconnect::PLUGIN_NAME),
							),
							'default'  => 'off',
							'hint'     => __('This setting determines whether Function Calling is used or not.', lineconnect::PLUGIN_NAME),
						),
						'openai_enabled_functions' => array(
							'type'     => 'multiselect',
							'label'    => __('Functions to use', lineconnect::PLUGIN_NAME),
							'required' => false,
							'list'     => array(),
							'default'  => array(),
							'isMulti'  => true,
							'hint'     => __('Function to be enabled by Function Calling.', lineconnect::PLUGIN_NAME),
						),
						'openai_context'           => array(
							'type'     => 'spinner',
							'label'    => __('Number of context', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => 3,
							'regex'    => '/^\d+$/',
							'hint'     => __('This is a setting for how many conversation histories to use in order to have the AI understand the context and respond.', lineconnect::PLUGIN_NAME),
						),
						'openai_max_tokens'        => array(
							'type'     => 'spinner',
							'label'    => __('Max tokens', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => -1,
							'regex'    => '/^[+-]?\d+$/',
							'hint'     => __('Maximum number of tokens to use. -1 is the upper limit of the model.', lineconnect::PLUGIN_NAME),
						),
						'openai_temperature'       => array(
							'type'     => 'range',
							'label'    => __('Temperature', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => 1,
							'min'      => 0,
							'max'      => 1,
							'step'     => 0.1,
							'hint'     => __('This is the temperature parameter. The higher the value, the more diverse words are likely to be selected. Between 0 and 1.', lineconnect::PLUGIN_NAME),
						),
						'openai_limit_normal'      => array(
							'type'     => 'spinner',
							'label'    => __('Limit for unlinked users', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => 3,
							'regex'    => '/^[+-]?\d+$/',
							'hint'     => __('Number of times an unlinked user can use it per day. -1 is unlimited.', lineconnect::PLUGIN_NAME),
						),
						'openai_limit_linked'      => array(
							'type'     => 'spinner',
							'label'    => __('Limit for linked users', lineconnect::PLUGIN_NAME),
							'required' => false,
							'default'  => 5,
							'regex'    => '/^[+-]?\d+$/',
							'hint'     => __('Number of times an linked user can use it per day. -1 is unlimited.', lineconnect::PLUGIN_NAME),
						),
						'openai_limit_message'     => array(
							'type'     => 'textarea',
							'label'    => __('Limit message', lineconnect::PLUGIN_NAME),
							'required' => false,
							'rows'     => 5,
							'cols'     => 60,
							'default'  => __('The number of times you can use it in a day (%limit% times) has been exceeded. Please try again after the date changes.', lineconnect::PLUGIN_NAME),
							'hint'     => __('This message is displayed when the number of times the limit can be used in a day is exceeded. The %limit% is replaced by the limit number of times.', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'data'    => array(
					'prefix' => '6',
					'name'   => __('Data', lineconnect::PLUGIN_NAME),
					'fields' => array(),
				),
			)
		);

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

		self::$variables_option = array(
			'plugin_version' => lineconnect::VERSION,
			'db_version'     => array(
				'initial' => '1.0',
				'default' => lineconnect::DB_VERSION,
			),
		);

		self::$lineconnect_action_schema = apply_filters(
			lineconnect::FILTER_PREFIX . 'lineconnect_action_schema',
			array(
				// '$schema'     => 'https://json-schema.org/draft/draft-07/schema',
				// 'title'       => __( 'LINE Connect Action', lineconnect::PLUGIN_NAME ),
				// 'description' => __( 'Action used in LINE Connect', lineconnect::PLUGIN_NAME ),
				'type'        => 'object',
				'properties'  => array(
					/*
							'title'       => array(
								'type'        => 'string',
								'title'       => __( 'Title', lineconnect::PLUGIN_NAME ),
								'description' => __( 'Title of the action', lineconnect::PLUGIN_NAME ),
							),
					*/
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
								/*
								'name'        => array(
									'type'        => 'string',
									'title'       => __( 'Parameter name', lineconnect::PLUGIN_NAME ),
									'description' => __( 'Name of the parameter', lineconnect::PLUGIN_NAME ),
								),*/
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

		// Trigger types
		self::$lineconnect_trigger_types = array(
			'webhook' => array(
				'type' => 'object',
				'title' => __('Webhook', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'type' => array(
						'title' => __('Event type', lineconnect::PLUGIN_NAME),
						'type' => 'string',
						'oneOf' => array(
							array(
								'const' => 'message',
								'title' => __('Message', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'postback',
								'title' => __('Post back', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'accountLink',
								'title' => __('Account Link', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'follow',
								'title' => __('Follow', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'unfollow',
								'title' => __('Unfollow', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'videoPlayComplete',
								'title' => __('Video play complete', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'join',
								'title' => __('Join', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'leave',
								'title' => __('Leave', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'memberJoined',
								'title' => __('Member joined', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'memberLeft',
								'title' => __('Member left', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'unsend',
								'title' => __('Unsend', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'membership',
								'title' => __('Membership', lineconnect::PLUGIN_NAME),
							),
						),
					),
					'condition' => array(
						'$ref' => '#/definitions/condition',
					),
				),
				'required'   => array(
					'type',
				),
				'dependencies' => array(
					'type' => array(
						'oneOf' => array(
							array(
								'properties' => array(
									'type' => array(
										'const' => 'message',
									),
									'message' => array(
										'title' => __('Message', lineconnect::PLUGIN_NAME),
										'type' => 'object',
										'properties' => array(
											'type' => array(
												'type' => 'string',
												'title' => __('Message type', lineconnect::PLUGIN_NAME),
												'oneOf' => array(
													array(
														'const' => 'text',
														'title' => __('Text', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'image',
														'title' => __('Image', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'video',
														'title' => __('Video', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'audio',
														'title' => __('Audio', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'file',
														'title' => __('File', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'location',
														'title' => __('Location', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'sticker',
														'title' => __('Sticker', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
										'required' => array(
											'type',
										),
										'dependencies' => array(
											'type' => array(
												'oneOf' => array(
													array(
														'properties' => array(
															'type' => array(
																'const' => 'text',
															),
															'text' => array(
																'$ref' => '#/definitions/keyword',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'image',
															)
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'video',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'audio',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'file',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'location',
															),
														),
													),
													array(
														'properties' => array(
															'type' => array(
																'const' => 'sticker',
															),
														),
													),
												),
											),
										),
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'postback',
									),
									'postback' => array(
										'title' => __('Post back', lineconnect::PLUGIN_NAME),
										'type' => 'object',
										'properties' => array(
											'data' => array(
												'$ref' => '#/definitions/keyword',
											),
											'params' => array(
												'$ref' => '#/definitions/postbackparams',
											),
										),
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'accountLink',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'follow',
									),
									'follow' => array(
										'title' => __('Follow', lineconnect::PLUGIN_NAME),
										'type' => 'object',
										'properties' => array(
											'isUnblocked' => array(
												'type' => 'string',
												'title' => __('Unblocked', lineconnect::PLUGIN_NAME),
												'oneOf' => array(
													array(
														'const' => 'any',
														'title' => __('Any', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'add',
														'title' => __('Add Freind', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'unblocked',
														'title' => __('Unblocked', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'unfollow',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'videoPlayComplete',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'join',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'leave',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'memberJoined',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'memberLeft',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'unsend',
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'membership',
									),
									'membership' => array(
										'title' => __('Membership', lineconnect::PLUGIN_NAME),
										'type' => 'object',
										'properties' => array(
											'type' => array(
												'type' => 'string',
												'title' => __('Type', lineconnect::PLUGIN_NAME),
												'oneOf' => array(
													array(
														'const' => 'joined',
														'title' => __('Joined', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'left',
														'title' => __('Left', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'renewed',
														'title' => __('Renewed', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
									),
								),
							),
						),
					),
				),
			),
			'schedule' => array(
				// 'type' => 'object',
				// 'title' => __('Schedules', lineconnect::PLUGIN_NAME),
				// 'items' => array(
				'type' => 'object',
				'title' => __('Schedule', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'type' => array(
						'title' => __('Schedule type', lineconnect::PLUGIN_NAME),
						'type' => 'string',
						'oneOf' => array(
							array(
								'const' => 'once',
								'title' => __('Once', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'repeat',
								'title' => __('Repeat', lineconnect::PLUGIN_NAME),
							),
						),
					),
				),
				'dependencies' => array(
					'type' => array(
						'oneOf' => array(
							array(
								'properties' => array(
									'type' => array(
										'const' => 'once',
									),
									'once' => array(
										'type' => 'object',
										'title' => __('Once', lineconnect::PLUGIN_NAME),
										'properties' => array(
											'datetime' => array(
												'type' => 'string',
												'format' => 'date-time',
												'title' => __('Date and time', lineconnect::PLUGIN_NAME),
											),
										),
									),
								),
							),
							array(
								'properties' => array(
									'type' => array(
										'const' => 'repeat',
									),
									'repeat' => array(
										'type' => 'object',
										'title' => __('Repeat', lineconnect::PLUGIN_NAME),
										'properties' => array(
											'every' => array(
												'type' => 'string',
												'title' => __('Every', lineconnect::PLUGIN_NAME),
												'oneOf' => array(
													array(
														'const' => 'hour',
														'title' => __('Hour', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'day',
														'title' => __('Day', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'date',
														'title' => __('Date', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'week',
														'title' => __('Week', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'month',
														'title' => __('Month', lineconnect::PLUGIN_NAME),
													),
													array(
														'const' => 'year',
														'title' => __('Year', lineconnect::PLUGIN_NAME),
													),
												),
											),
											'start' => array(
												'type' => 'string',
												'format' => 'date-time',
												'title' => __('Start date', lineconnect::PLUGIN_NAME),
											),
											'end' => array(
												'type' => 'string',
												'format' => 'date-time',
												'title' => __('End date', lineconnect::PLUGIN_NAME),
											),
											'lag' => array(
												'type' => 'integer',
												'title' => __('Beforehand notice (min)', lineconnect::PLUGIN_NAME),
												'description' => __('How many minutes in advance notice', lineconnect::PLUGIN_NAME),
											),
										),
										'required' => array(
											'every',
											'start',
										),
										'dependencies' => array(
											'every' => array(
												'oneOf' => array(
													array(
														'properties' => array(
															'every' => array(
																'const' => 'hour',
															),
															'hour' => array(
																'type' => 'array',
																'title' => __('Hour', lineconnect::PLUGIN_NAME),
																'uniqueItems' => true,
																'items' => array(
																	'type' => 'integer',
																	'oneOf' => array(
																		array(
																			'const' => 0,
																			'title' => __('0 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 1,
																			'title' => __('1 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 2,
																			'title' => __('2 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 3,
																			'title' => __('3 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 4,
																			'title' => __('4 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 5,
																			'title' => __('5 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 6,
																			'title' => __('6 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 7,
																			'title' => __('7 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 8,
																			'title' => __('8 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 9,
																			'title' => __('9 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 10,
																			'title' => __('10 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 11,
																			'title' => __('11 am', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 12,
																			'title' => __('12 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 13,
																			'title' => __('1 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 14,
																			'title' => __('2 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 15,
																			'title' => __('3 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 16,
																			'title' => __('4 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 17,
																			'title' => __('5 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 18,
																			'title' => __('6 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 19,
																			'title' => __('7 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 20,
																			'title' => __('8 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 21,
																			'title' => __('9 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 22,
																			'title' => __('10 pm', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 23,
																			'title' => __('11 pm', lineconnect::PLUGIN_NAME),
																		),
																	),
																),
															),
														),
													),
													array(
														'properties' => array(
															'every' => array(
																'const' => 'day',
															),
															'day' => array(
																'type' => 'array',
																'title' => __('Day', lineconnect::PLUGIN_NAME),
																'items' => array(
																	'type' => 'object',
																	'properties' => array(
																		'type' => array(
																			'type' => 'string',
																			'title' => __('Way of Calc', lineconnect::PLUGIN_NAME),
																			'oneOf' => array(
																				array(
																					'const' => 'nthday',
																					'title' => __('nth day', lineconnect::PLUGIN_NAME),
																				),
																				array(
																					'const' => 'nthweek',
																					'title' => __('nth Week in a month', lineconnect::PLUGIN_NAME),
																				),
																			),
																		),
																		'number' => array(
																			'type' => 'array',
																			'title' => __('Day of the week in the month', lineconnect::PLUGIN_NAME),
																			'uniqueItems' => true,
																			'items' => array(
																				'type' => 'integer',
																				'oneOf' => array(
																					array(
																						'const' => 1,
																						'title' => __('1st', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 2,
																						'title' => __('2nd', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 3,
																						'title' => __('3rd', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 4,
																						'title' => __('4th', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 5,
																						'title' => __('5th', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 6,
																						'title' => __('6th', lineconnect::PLUGIN_NAME),
																					),
																				),
																			),
																		),
																		'day' => array(
																			'type' => 'array',
																			'title' => __('Day', lineconnect::PLUGIN_NAME),
																			'uniqueItems' => true,
																			'items' => array(
																				'type' => 'integer',
																				'oneOf' => array(
																					array(
																						'const' => 0,
																						'title' => __('Sunday', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 1,
																						'title' => __('Monday', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 2,
																						'title' => __('Tuesday', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 3,
																						'title' => __('Wednesday', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 4,
																						'title' => __('Thursday', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 5,
																						'title' => __('Friday', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 6,
																						'title' => __('Saturday', lineconnect::PLUGIN_NAME),
																					),
																				),
																			),
																		),
																	),
																	'dependencies' => array(
																		'type' => array(
																			'oneOf' => array(
																				array(
																					'properties' => array(
																						'type' => array(
																							'const' => 'nthweek',
																						),
																						'startdayofweek' => array(
																							'type' => 'integer',
																							'title' => __('First day of the week', lineconnect::PLUGIN_NAME),
																							'oneOf' => array(
																								array(
																									'const' => 0,
																									'title' => __('Sunday', lineconnect::PLUGIN_NAME),
																								),
																								array(
																									'const' => 1,
																									'title' => __('Monday', lineconnect::PLUGIN_NAME),
																								),
																							),
																						),
																					),
																				),
																				array(
																					'properties' => array(
																						'type' => array(
																							'const' => 'nthday',
																						),
																					),
																				),
																			),
																		),
																	),
																),
															),
														),
													),
													array(
														'properties' => array(
															'every' => array(
																'const' => 'date',
															),
															'date' => array(
																'type' => 'array',
																'title' => __('Date', lineconnect::PLUGIN_NAME),
																'uniqueItems' => true,
																'items' => array(
																	'type' => 'integer',
																	'oneOf' => array(
																		array(
																			'const' => 1,
																			'title' => __('1st', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 2,
																			'title' => __('2nd', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 3,
																			'title' => __('3rd', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 4,
																			'title' => __('4th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 5,
																			'title' => __('5th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 6,
																			'title' => __('6th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 7,
																			'title' => __('7th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 8,
																			'title' => __('8th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 9,
																			'title' => __('9th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 10,
																			'title' => __('10th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 11,
																			'title' => __('11th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 12,
																			'title' => __('12th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 13,
																			'title' => __('13th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 14,
																			'title' => __('14th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 15,
																			'title' => __('15th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 16,
																			'title' => __('16th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 17,
																			'title' => __('17th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 18,
																			'title' => __('18th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 19,
																			'title' => __('19th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 20,
																			'title' => __('20th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 21,
																			'title' => __('21st', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 22,
																			'title' => __('22nd', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 23,
																			'title' => __('23rd', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 24,
																			'title' => __('24th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 25,
																			'title' => __('25th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 26,
																			'title' => __('26th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 27,
																			'title' => __('27th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 28,
																			'title' => __('28th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 29,
																			'title' => __('29th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 30,
																			'title' => __('30th', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 31,
																			'title' => __('31st', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 0,
																			'title' => __('Last day of the month', lineconnect::PLUGIN_NAME),
																		),
																	),
																),
															),
														),
													),
													array(
														'properties' => array(
															'every' => array(
																'const' => 'week',
															),
															'week' => array(
																'type' => 'array',
																'title' => __('Week number', lineconnect::PLUGIN_NAME),
																'items' => array(
																	'type' => 'integer',
																	'minimum' => 1,
																	'maximum' => 52,
																),
															),
														),
													),
													array(
														'properties' => array(
															'every' => array(
																'const' => 'month',
															),
															'month' => array(
																'type' => 'array',
																'title' => __('Month', lineconnect::PLUGIN_NAME),
																'uniqueItems' => true,
																'items' => array(
																	'type' => 'integer',
																	'oneOf' => array(
																		array(
																			'const' => 1,
																			'title' => __('January', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 2,
																			'title' => __('February', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 3,
																			'title' => __('March', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 4,
																			'title' => __('April', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 5,
																			'title' => __('May', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 6,
																			'title' => __('June', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 7,
																			'title' => __('July', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 8,
																			'title' => __('August', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 9,
																			'title' => __('September', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 10,
																			'title' => __('October', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 11,
																			'title' => __('November', lineconnect::PLUGIN_NAME),
																		),
																		array(
																			'const' => 12,
																			'title' => __('December', lineconnect::PLUGIN_NAME),
																		),
																	),
																),
															),
														),
													),
													array(
														'properties' => array(
															'every' => array(
																'const' => 'year',
															),
															'year' => array(
																'type' => 'array',
																'title' => __('Year', lineconnect::PLUGIN_NAME),
																'items' => array(
																	'type' => 'integer',
																	'minimum' => 2024,
																	'maximum' => 2099,
																),
															),
														),
													),
												),
											),
										),
									),
								),
							),
						),
					),
				),
				//),
			),
		);
		// Trigger schema
		self::$lineconnect_trigger_schema = array(
			'type'       => 'object',
			'properties' => array(
				'triggers' => array(
					'type'       => 'array',
					'title'      => __('Triggers', lineconnect::PLUGIN_NAME),
					'items' => array(
						//'type'       => 'object',
						//'title'      => __('Trigger', lineconnect::PLUGIN_NAME),
						//'properties' => array(),
					),
				),
				'action'  => array(
					'title' => __('Action', lineconnect::PLUGIN_NAME),
					'type'  => 'array',
					'items' => array(
						'type'     => 'object',
						'oneOf'    => array(),
						'required' => array(
							'parameters',
						),
					),
				),
				'chain' => array(
					'type' => 'array',
					'title' => __('Action chain', lineconnect::PLUGIN_NAME),
					'items' => array(
						'type'     => 'object',
						'properties' => array(
							'to' => array(
								'type' => 'string',
								'title' => __('Destination argument to', lineconnect::PLUGIN_NAME),
								'description' => __('Injection Destination Argument Path. e.g. 2.message', lineconnect::PLUGIN_NAME),
							),
							'data' => array(
								'type' => 'string',
								'title' => __('Data', lineconnect::PLUGIN_NAME),
								'description' => __('Injection Data. You can use return value of previous action. e.g. {{$.return.1}}', lineconnect::PLUGIN_NAME),
							),
						),
					),
				),
			),
			'definitions' => array(
				'condition' => array(
					'type' => 'object',
					'title' => __('Source condition', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'conditions' => array(
							'type' => 'array',
							'title' => __('Source condition group', lineconnect::PLUGIN_NAME),
							'items' => array(
								'type'  => 'object',
								'title' => __('Source condition', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'type' => array(
										'type' => 'string',
										'title' =>  __('Type', lineconnect::PLUGIN_NAME),
										'anyOf' => array(
											array(
												'const' => 'channel',
												'title' => __('Channel', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'source',
												'title' => __('Source', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'group',
												'title' => __('Source condition group', lineconnect::PLUGIN_NAME),
											),
										),
									),
									'not' => array(
										'type' => 'boolean',
										'title' => __('Not', lineconnect::PLUGIN_NAME),
										'description' => __('Logical negation', lineconnect::PLUGIN_NAME),
									),
								),
								'dependencies' => array(
									'type' => array(
										'oneOf' => array(
											array(
												'properties' => array(
													'type' => array(
														'const' => 'channel',
													),
													'secret_prefix' => array(
														'$ref' => '#/definitions/secret_prefix',
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'source',
													),
													'source' => array(
														'type' => 'object',
														'title' => __('Source', lineconnect::PLUGIN_NAME),
														'properties' => array(
															'type' => array(
																'type' => 'string',
																'title' => __('Type', lineconnect::PLUGIN_NAME),
																'anyOf' => array(
																	array(
																		'const' => 'user',
																		'title' => __('User', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'group',
																		'title' => __('Group', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'room',
																		'title' => __('Room', lineconnect::PLUGIN_NAME),
																	),
																),
															),
														),
														'dependencies' => array(
															'type' => array(
																'oneOf' => array(
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'user',
																			),
																			'link' => array(
																				'type' => 'string',
																				'title' => __('Link status', lineconnect::PLUGIN_NAME),
																				'anyOf' => array(
																					array(
																						'const' => 'any',
																						'title' => __('Any', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 'linked',
																						'title' => __('Linked', lineconnect::PLUGIN_NAME),
																					),
																					array(
																						'const' => 'unlinked',
																						'title' => __('Unlinked', lineconnect::PLUGIN_NAME),
																					),
																				),
																			),
																			'role' => array(
																				'$ref' => '#/definitions/role',
																			),
																			'userId' => array(
																				'type' => 'array',
																				'title' => __('LINE user ID', lineconnect::PLUGIN_NAME),
																				'items' => array(
																					'type' => 'string',
																				),
																			),
																			'usermeta' => array(
																				'type' => 'array',
																				'title' => __('User Meta', lineconnect::PLUGIN_NAME),
																				'minItems' => 1,
																				'items' => array(
																					'type' => 'object',
																					'required' => ['key', 'value', 'compare'],
																					'properties' => array(
																						'key' => array(
																							'type' => 'string',
																							'title' => __('Meta Key', lineconnect::PLUGIN_NAME),
																						),
																						'compare' => array(
																							'$ref' => '#/definitions/compare',
																						),
																					),
																					'dependencies' => array(
																						'compare' => array(
																							'$ref' => '#/definitions/compare_dependencies',
																						),
																					),
																				),
																			),
																			'profile' => array(
																				'type' => 'array',
																				'title' => __('Profile data', lineconnect::PLUGIN_NAME),
																				'minItems' => 1,
																				'items' => array(
																					'type' => 'object',
																					'required' => ['key', 'value', 'compare'],
																					'properties' => array(
																						'key' => array(
																							'type' => 'string',
																							'title' => __('Profile field', lineconnect::PLUGIN_NAME),
																						),
																						'compare' => array(
																							'$ref' => '#/definitions/compare',
																						),
																					),
																					'dependencies' => array(
																						'compare' => array(
																							'$ref' => '#/definitions/compare_dependencies',
																						),
																					),
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'group',
																			),
																			'groupId' => array(
																				'type' => 'array',
																				'title' => __('LINE group ID', lineconnect::PLUGIN_NAME),
																				'items' => array(
																					'type' => 'string',
																				),
																			),
																			'userId' => array(
																				'type' => 'array',
																				'title' => __('LINE user ID', lineconnect::PLUGIN_NAME),
																				'items' => array(
																					'type' => 'string',
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'room',
																			),
																			'roomId' => array(
																				'type' => 'array',
																				'title' => __('LINE Room ID', lineconnect::PLUGIN_NAME),
																				'items' => array(
																					'type' => 'string',
																				),
																			),
																			'userId' => array(
																				'type' => 'array',
																				'title' => __('LINE user ID', lineconnect::PLUGIN_NAME),
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
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'group',
													),
													'condition' => array(
														'$ref' => '#/definitions/condition',
													),
												),
											),
										),
									),
								),
							),
						),
						'operator' => array(
							'type'  => 'string',
							'title' => __('Operator', lineconnect::PLUGIN_NAME),
							'oneOf' => array(
								array(
									'const' => 'and',
									'title' => __('And', lineconnect::PLUGIN_NAME),
									'description' => __('All conditions must be true', lineconnect::PLUGIN_NAME),
								),
								array(
									'const' => 'or',
									'title' => __('Or', lineconnect::PLUGIN_NAME),
									'description' => __('At least one condition must be true', lineconnect::PLUGIN_NAME),
								),
							),
						),
					),
				),
				'role' => array(
					'type' => 'array',
					'title' => __('Role', lineconnect::PLUGIN_NAME),
					'items' => array(
						'type' => 'string',
						'oneOf' => array(),
					),
					'uniqueItems' => true,
				),
				'keyword' => array(
					'type' => 'object',
					'title' => __('Keyword', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'conditions' => array(
							'type' => 'array',
							'title' => __('Keyword condition group', lineconnect::PLUGIN_NAME),
							'items' => array(
								'type'  => 'object',
								'title' => __('Keyword condition', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'type' => array(
										'type' => 'string',
										'title' => __('Type', lineconnect::PLUGIN_NAME),
										'anyOf' => array(
											array(
												'const' => 'source',
												'title' => __('Source', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'group',
												'title' => __('Keyword condition group', lineconnect::PLUGIN_NAME),
											),
										),
									),
									'not' => array(
										'type' => 'boolean',
										'title' => __('Not', lineconnect::PLUGIN_NAME),
										'description' => __('Logical negation', lineconnect::PLUGIN_NAME),
									),
								),
								'dependencies' => array(
									'type' => array(
										'oneOf' => array(
											array(
												'properties' => array(
													'type' => array(
														'const' => 'source',
													),
													'source' => array(
														'type' => 'object',
														'title' => __('Source', lineconnect::PLUGIN_NAME),
														'properties' => array(
															'type' => array(
																'type' => 'string',
																'title' => __('Type', lineconnect::PLUGIN_NAME),
																'anyOf' => array(
																	array(
																		'const' => 'keyword',
																		'title' => __('Keyword', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'query',
																		'title' => __('Query string', lineconnect::PLUGIN_NAME),
																	),
																),
															),
														),
														'dependencies' => array(
															'type' => array(
																'oneOf' => array(
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'keyword',
																			),
																			'keyword' => array(
																				'type' => 'object',
																				'title' =>  __('Keyword', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'keyword' => array(
																						'type' => 'string',
																						'title' => __('Keyword', lineconnect::PLUGIN_NAME),
																						'description' => __('Keyword to match', lineconnect::PLUGIN_NAME),
																					),
																					'match' => array(
																						'type' => 'string',
																						'title' => __('Match type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'contains',
																								'title' => __('Contains', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'startsWith',
																								'title' => __('Starts with', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'endsWith',
																								'title' => __('Ends with', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'regexp',
																								'title' => __('Regular expression', lineconnect::PLUGIN_NAME),
																							),
																						),
																					),
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'query',
																			),
																			'query' => array(
																				'type' => 'object',
																				'title' => __('Query string', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'parameters' => array(
																						'type' => 'array',
																						'title' => __('Parameters', lineconnect::PLUGIN_NAME),
																						'items' => array(
																							'type' => 'object',
																							'title' => __('Parameter', lineconnect::PLUGIN_NAME),
																							'properties' => array(
																								'key' => array(
																									'type' => 'string',
																									'title' => __('Key', lineconnect::PLUGIN_NAME),
																								),
																								'value' => array(
																									'type' => 'string',
																									'title' => __('Value', lineconnect::PLUGIN_NAME),
																								),
																							),
																						),
																					),
																					'match' => array(
																						'type' => 'string',
																						'title' => __('Match type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'contains',
																								'title' => __('Contains', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																						),
																					),
																				),
																			),
																		),
																	),
																),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'group',
													),
													'condition' => array(
														'$ref' => '#/definitions/keyword',
													),
												),
											),
										),
									),
								),
							),
						),
						'operator' => array(
							'type' => 'string',
							'title' => __('Operator', lineconnect::PLUGIN_NAME),
							'oneOf' => array(
								array(
									'const' => 'and',
									'title' => __('And', lineconnect::PLUGIN_NAME),
									'description' => __('All conditions must be true', lineconnect::PLUGIN_NAME),
								),
								array(
									'const' => 'or',
									'title' => __('Or', lineconnect::PLUGIN_NAME),
									'description' => __('At least one condition must be true', lineconnect::PLUGIN_NAME),
								),
							),
						),
					),
				),
				'secret_prefix' => array(
					'type' => 'array',
					'title' => __('Channel', lineconnect::PLUGIN_NAME),
					'description' => __('Target channel', lineconnect::PLUGIN_NAME),
					'uniqueItems' => true,
					'items' => array(
						'type' => 'string',
						'oneOf' => array(),
					),
				),
				'compare' => array(
					'type' => 'string',
					'title' => __('Compare method', lineconnect::PLUGIN_NAME),
					'default' => '=',
					'anyOf' => array(
						array(
							'const' => '=',
							'title' => __('Equals', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '!=',
							'title' => __('Not equals', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '>',
							'title' => __('Greater than', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '>=',
							'title' => __('Greater than or equal', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '<',
							'title' => __('Less than', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '<=',
							'title' => __('Less than or equal', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'LIKE',
							'title' => __('Contains (String)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT LIKE',
							'title' => __('Not contains (String)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'IN',
							'title' => __('In (Array)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT IN',
							'title' => __('Not in (Array)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'BETWEEN',
							'title' => __('Between 2 values', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT BETWEEN',
							'title' => __('Not Between 2 values', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'EXISTS',
							'title' => __('Exists', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT EXISTS',
							'title' => __('Not exists', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'REGEXP',
							'title' => __('Regular expression match', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT REGEXP',
							'title' => __('No regular expression match', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'compare_dependencies' => array(
					'oneOf' => array(
						array(
							'properties' => array(
								'compare' => array(
									'enum' => array(
										'IN',
										'NOT IN',
									),
								),
								'values' => array(
									'type' => 'array',
									'title' => __('Values', lineconnect::PLUGIN_NAME),
									'minItems' => 1,
									'items' => array(
										'type' => 'string',
									),
								),
							),
						),
						array(
							'properties' => array(
								'compare' => array(
									'enum' => array(
										'BETWEEN',
										'NOT BETWEEN',
									),
								),
								'values' => array(
									'type' => 'array',
									'title' => __('Values', lineconnect::PLUGIN_NAME),
									'minItems' => 2,
									'maxItems' => 2,
									'items' => array(
										'type' => 'string',
									),
								),
							),
						),
						array(
							'properties' => array(
								'compare' => array(
									'enum' => array(
										'=',
										'!=',
										'>',
										'>=',
										'<',
										'<=',
										'LIKE',
										'NOT LIKE',
										'REGEXP',
										'NOT REGEXP',
									),
								),
								'value' => array(
									'type' => 'string',
									'title' => __('Value', lineconnect::PLUGIN_NAME),
								),
							),
						),
						array(
							'properties' => array(
								'compare' => array(
									'enum' => array(
										'EXISTS',
										'NOT EXISTS',
									),
								),
							),
						),
					),
				),
				'postbackparams' => array(
					'type' => 'object',
					'title' => __('Parameters', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'conditions' => array(
							'type' => 'array',
							'title' => __('Parameters condition group', lineconnect::PLUGIN_NAME),
							'items' => array(
								'type'  => 'object',
								'title' => __('Parameter condition', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'type' => array(
										'type' => 'string',
										'title' => __('Type', lineconnect::PLUGIN_NAME),
										'anyOf' => array(
											array(
												'const' => 'source',
												'title' => __('Source', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'group',
												'title' => __('Parameter condition group', lineconnect::PLUGIN_NAME),
											),
										),
									),
									'not' => array(
										'type' => 'boolean',
										'title' => __('Not', lineconnect::PLUGIN_NAME),
										'description' => __('Logical negation', lineconnect::PLUGIN_NAME),
									),
								),
								'dependencies' => array(
									'type' => array(
										'oneOf' => array(
											array(
												'properties' => array(
													'type' => array(
														'const' => 'source',
													),
													'source' => array(
														'type' => 'object',
														'title' => __('Source', lineconnect::PLUGIN_NAME),
														'properties' => array(
															'type' => array(
																'type' => 'string',
																'title' => __('Type', lineconnect::PLUGIN_NAME),
																'anyOf' => array(
																	array(
																		'const' => 'newRichMenuAliasId',
																		'title' => __('New richmenu alias ID', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'status',
																		'title' => __('Status', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'date',
																		'title' => __('Date', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'time',
																		'title' => __('Time', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'datetime',
																		'title' => __('Date and time', lineconnect::PLUGIN_NAME),
																	),
																),
															),
														),
														'dependencies' => array(
															'type' => array(
																'oneOf' => array(
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'newRichMenuAliasId',
																			),
																			'newRichMenuAliasId' => array(
																				'type' => 'object',
																				'title' =>  __('New richmenu alias ID', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'newRichMenuAliasId' => array(
																						'type' => 'string',
																						'title' => __('New richmenu alias ID', lineconnect::PLUGIN_NAME),
																						'description' => __('New richmenu alias ID', lineconnect::PLUGIN_NAME),
																					),
																					'match' => array(
																						'type' => 'string',
																						'title' => __('Match type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'contains',
																								'title' => __('Contains', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'startsWith',
																								'title' => __('Starts with', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'endsWith',
																								'title' => __('Ends with', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'regexp',
																								'title' => __('Regular expression', lineconnect::PLUGIN_NAME),
																							),
																						),
																					),
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'status',
																			),
																			'status' => array(
																				'type' => 'array',
																				'title' => __('Status', lineconnect::PLUGIN_NAME),
																				'uniqueItems' => true,
																				'items' => array(
																					'type' => 'string',
																					'oneOf' => array(
																						array(
																							'const' => 'SUCCESS',
																							'title' => __('Success', lineconnect::PLUGIN_NAME),
																						),
																						array(
																							'const' => 'RICHMENU_ALIAS_ID_NOTFOUND',
																							'title' => __('Richmenu alias ID not found', lineconnect::PLUGIN_NAME),
																						),
																						array(
																							'const' => 'RICHMENU_NOTFOUND',
																							'title' => __('Richmenu not found', lineconnect::PLUGIN_NAME),
																						),
																						array(
																							'const' => 'FAILED',
																							'title' => __('Failed', lineconnect::PLUGIN_NAME),
																						),
																					),
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'date',
																			),
																			'date' => array(
																				'type' => 'object',
																				'title' =>  __('Date', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'date' => array(
																						'type' => 'string',
																						'title' => __('Date', lineconnect::PLUGIN_NAME),
																						'description' => __('Date: YYYY-MM-DD', lineconnect::PLUGIN_NAME),
																						"format" => "date",
																					),
																					'compare' => array(
																						'type' => 'string',
																						'title' => __('Compare type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before',
																								'title' => __('Before', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before_or_equal',
																								'title' => __('Before or equal', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after',
																								'title' => __('After', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after_or_equal',
																								'title' => __('After or equal', lineconnect::PLUGIN_NAME),
																							),
																						),
																					),
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'time',
																			),
																			'time' => array(
																				'type' => 'object',
																				'title' =>  __('Time', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'time' => array(
																						'type' => 'string',
																						'title' => __('Time', lineconnect::PLUGIN_NAME),
																						'description' => __('Time: hh:mm', lineconnect::PLUGIN_NAME),
																						"format" => "time",
																					),
																					'compare' => array(
																						'type' => 'string',
																						'title' => __('Compare type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before',
																								'title' => __('Before', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before_or_equal',
																								'title' => __('Before or equal', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after',
																								'title' => __('After', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after_or_equal',
																								'title' => __('After or equal', lineconnect::PLUGIN_NAME),
																							),
																						),
																					),
																				),
																			),
																		),
																	),
																	array(
																		'properties' => array(
																			'type' => array(
																				'const' => 'datetime',
																			),
																			'datetime' => array(
																				'type' => 'object',
																				'title' =>  __('DateTime', lineconnect::PLUGIN_NAME),
																				'properties' => array(
																					'datetime' => array(
																						'type' => 'string',
																						'title' => __('DateTime', lineconnect::PLUGIN_NAME),
																						'description' => __('DateTime: YYYY-MM-DDThh:mm', lineconnect::PLUGIN_NAME),
																						"format" => "date-time",
																					),
																					'compare' => array(
																						'type' => 'string',
																						'title' => __('Compare type', lineconnect::PLUGIN_NAME),
																						'anyOf' => array(
																							array(
																								'const' => 'equals',
																								'title' => __('Equals', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before',
																								'title' => __('Before', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'before_or_equal',
																								'title' => __('Before or equal', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after',
																								'title' => __('After', lineconnect::PLUGIN_NAME),
																							),
																							array(
																								'const' => 'after_or_equal',
																								'title' => __('After or equal', lineconnect::PLUGIN_NAME),
																							),
																						),
																					),
																				),
																			),
																		),
																	),
																),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'group',
													),
													'condition' => array(
														'$ref' => '#/definitions/postbackparams',
													),
												),
											),
										),
									),
								),
							),
						),
						'operator' => array(
							'type' => 'string',
							'title' => __('Operator', lineconnect::PLUGIN_NAME),
							'oneOf' => array(
								array(
									'const' => 'and',
									'title' => __('And', lineconnect::PLUGIN_NAME),
									'description' => __('All conditions must be true', lineconnect::PLUGIN_NAME),
								),
								array(
									'const' => 'or',
									'title' => __('Or', lineconnect::PLUGIN_NAME),
									'description' => __('At least one condition must be true', lineconnect::PLUGIN_NAME),
								),
							),
						),
					),
				),
			),
		);

		// Trigger UI schema
		self::$lineconnect_trigger_uischema = apply_filters(
			lineconnect::FILTER_PREFIX . 'lineconnect_trigger_uischema',
			array(
				'ui:submitButtonOptions' => array(
					'norender' => true,
				),
				'triggers' => array(
					'items' => array(
						'ui:order' => array(
							'type',
							'message',
							'postback',
							'follow',
							'*',
						),
						'condition' => array(
							'conditions' => array(
								'items' => array(
									'ui:order' => array(
										'type',
										'source',
										'secret_prefix',
										'*',
									),
									/*
									'not' => array(
										'ui:widget' => 'select',
									),*/
								),
								'ui:options' => array(
									'addText' =>  __('Add source condition', lineconnect::PLUGIN_NAME),
								),
							),
						),
						'message' => array(
							'text' => array(
								'conditions' => array(
									'items' => array(
										'ui:order' => array(
											'type',
											'source',
											'*',
										),
										/*
										'not' => array(
											'ui:widget' => 'select',
										),*/
										'source' => array(
											'query' => array(
												'parameters' => array(
													'ui:options' => array(
														'addText' =>  __('Add query parameter', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
									),
									'ui:options' => array(
										'addText' =>  __('Add message condition', lineconnect::PLUGIN_NAME),
									),
								),
							),
						),
						'postback' => array(
							'data' => array(
								'conditions' => array(
									'items' => array(
										'ui:order' => array(
											'type',
											'source',
											'*',
										),
										/*
										'not' => array(
											'ui:widget' => 'select',
										),*/
									),
									'ui:options' => array(
										'addText' =>  __('Add postback condition', lineconnect::PLUGIN_NAME),
									),
								),
							),
						),
						'repeat' => array(
							'ui:order' => array(
								'every',
								'hour',
								'day',
								'date',
								'week',
								'month',
								'year',
								'*',
							),
							'hour' => array(
								'ui:widget' => 'checkboxes',
								'ui:options' => array(
									'inline' => true,
								),
							),
							'day' => array(
								'items' => array(
									'number' => array(
										'ui:widget' => 'checkboxes',
										'ui:options' => array(
											'inline' => true,
										),
									),
									'day' => array(
										'ui:widget' => 'checkboxes',
										'ui:options' => array(
											'inline' => true,
										),
									),
								),
								'ui:options' => array(
									'addText' =>  __('Add day', lineconnect::PLUGIN_NAME),
								),
							),
							'date' => array(
								'ui:widget' => 'checkboxes',
								'ui:options' => array(
									'inline' => true,
								),
							),
							'week' => array(
								'ui:options' => array(
									'addText' =>  __('Add week', lineconnect::PLUGIN_NAME),
								),
							),
							'month' => array(
								'ui:widget' => 'checkboxes',
								'ui:options' => array(
									'inline' => true,
								),
							),
							'year' => array(
								'ui:options' => array(
									'addText' =>  __('Add year', lineconnect::PLUGIN_NAME),
								),
							),
						),
					),
					'ui:options' => array(
						'addText' =>  __('Add trigger', lineconnect::PLUGIN_NAME),
					),
				),
				'action'                 => array(
					'items' => array(
						'action_name' => array(
							'ui:style' => array(
								'display' => 'none',
							),
						),
						'parameters' => array(
							'ui:options' => array(
								'addText' => __('Add parameter', lineconnect::PLUGIN_NAME),
							),
							'body' => array(
								'ui:widget' => 'textarea',
								'ui:options' => array(
									'rows' => 5,
								),
							),
							'json' => array(
								'ui:widget' => 'textarea',
								'ui:options' => array(
									'rows' => 5,
								),
							),
						),
					),
					'ui:options' => array(
						'addText' =>  __('Add action', lineconnect::PLUGIN_NAME),
					),
				),
				'chain' => array(
					'ui:options' => array(
						'addText' =>  __('Add chain', lineconnect::PLUGIN_NAME),
					),
				),
			)
		);

		// Message type schema
		self::$lineconnect_message_type_schema = array(
			'type'        => 'object',
			'properties'  => array(
				'type'  => array(
					'type'  => 'string',
					'title' => __('Message type', lineconnect::PLUGIN_NAME),
					'anyOf' => array(
						array(
							'const' => 'text',
							'title' => __('Text', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'sticker',
							'title' => __('Sticker', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'image',
							'title' => __('Image', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'video',
							'title' => __('Video', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'audio',
							'title' => __('Audio', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'location',
							'title' => __('Location', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'imagemap',
							'title' => __('Image map', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'button_template',
							'title' => __('Button template', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'confirm_template',
							'title' => __('Confirm template', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'carousel_template',
							'title' => __('Carousel template', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'image_carousel_template',
							'title' => __('Image carousel template', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'flex',
							'title' => __('Flex', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'raw',
							'title' => __('Raw', lineconnect::PLUGIN_NAME),
						),
					),
				),
			),
		);

		// Message type UI schema
		self::$lineconnect_message_type_uischema = array(
			'ui:submitButtonOptions' => array(
				'norender' => true,
			),
			'type' => array(
				'ui:description' => __('Choose message type.', lineconnect::PLUGIN_NAME),
			),
		);

		self::$lineconnect_message_types = array(
			'text' => array(
				'type'       => 'object',
				'title'      => __('Text message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'text' => array(
						'type'     => 'object',
						'title'    => __('Text', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'text' => array(
								'type' => 'string',
								'title' => __('Text', lineconnect::PLUGIN_NAME),
								'description' => __('Message text. Max character limit: 5000', lineconnect::PLUGIN_NAME),
								'maxLength' => 5000,
							),
						),
						'required' => array(
							'text',
						),
					),
				),
			),
			'sticker' => array(
				'type'       => 'object',
				'title'      => __('Sticker message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'sticker' => array(
						'type'     => 'object',
						'title'    => __('Sticker', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'packageId' => array(
								'type' => 'string',
								'title' => __('Package ID', lineconnect::PLUGIN_NAME),
								'description' => __('Package ID for a set of stickers.', lineconnect::PLUGIN_NAME),
							),
							'stickerId' => array(
								'type' => 'string',
								'title' => __('Sticker ID', lineconnect::PLUGIN_NAME),
							),
						),
						'required' => array(
							'packageId',
							'stickerId',
						),
					),
				),
			),
			'image' => array(
				'type'       => 'object',
				'title'      => __('Image message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'image' => array(
						'type'     => 'object',
						'title'    => __('Image', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'originalContentUrl' => array(
								'type' => 'string',
								'title' => __('Original content URL', lineconnect::PLUGIN_NAME),
								'description' => __('Image file URL. Protocol: HTTPS, Image format: JPEG or PNG, Max file size: 10 MB', lineconnect::PLUGIN_NAME),
								'maxLength' => 2000,
							),
							'previewImageUrl' => array(
								'type' => 'string',
								'title' => __('Preview Image Url', lineconnect::PLUGIN_NAME),
								'description' => __('Preview image URL. Image format: JPEG or PNG, Max file size: 1 MB', lineconnect::PLUGIN_NAME),
								'maxLength' => 2000,
							),
						),
						'required' => array(
							'originalContentUrl',
							'previewImageUrl',
						),
					),
				),
			),
			'video' => array(
				'type'       => 'object',
				'title'      => __('Video message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'video' => array(
						'type'     => 'object',
						'title'    => __('Video', lineconnect::PLUGIN_NAME),
						'description' => __('If the video isn\'t playing properly, make sure the video is a supported file type and the HTTP server hosting the video supports HTTP range requests.', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'originalContentUrl' => array(
								'type' => 'string',
								'title' => __('Original content URL', lineconnect::PLUGIN_NAME),
								'description' => __('Video file URL. Protocol: HTTPS, Video format: MP4, Max file size: 200 MB', lineconnect::PLUGIN_NAME),
								'maxLength' => 2000,
							),
							'previewImageUrl' => array(
								'type' => 'string',
								'title' => __('Preview image URL', lineconnect::PLUGIN_NAME),
								'description' => __('Preview image URL. Protocol: HTTPS, Image format: JPEG or PNG, Max file size: 1 MB', lineconnect::PLUGIN_NAME),
								'maxLength' => 2000,
							),
							'trackingId' => array(
								'type' => 'string',
								'title' => __('Tracking ID', lineconnect::PLUGIN_NAME),
								'description' => __('ID used to identify the video when Video viewing complete event occurs. You can use the same ID in multiple messages.', lineconnect::PLUGIN_NAME),
								'maxLength' => 100,
							),
						),
						'required' => array(
							'originalContentUrl',
							'previewImageUrl',
						),
					),
				),
			),
			'audio' => array(
				'type'       => 'object',
				'title'      => __('Audio message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'audio' => array(
						'type'     => 'object',
						'title'    => __('Audio', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'originalContentUrl' => array(
								'type' => 'string',
								'title' => __('Original content URL', lineconnect::PLUGIN_NAME),
								'description' => __('Audio file URL. Protocol: HTTPS, Audio format: MP3 or MP4, Max file size: 200 MB', lineconnect::PLUGIN_NAME),
							),
							'duration' => array(
								'type' => 'number',
								'title' => __('Duration', lineconnect::PLUGIN_NAME),
								'description' => __('Length of audio file (milliseconds).', lineconnect::PLUGIN_NAME),
							),
						),
						'required' => array(
							'originalContentUrl',
							'duration',
						),
					),
				),
			),
			'location' => array(
				'type'       => 'object',
				'title'      => __('Location message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'location' => array(
						'type'     => 'object',
						'title'    => __('Location', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'title'    => array(
								'type' => 'string',
								'title' => __('Title', lineconnect::PLUGIN_NAME),
								'maxLength' => 100,
							),
							'address'  => array(
								'type' => 'string',
								'title' => __('Address', lineconnect::PLUGIN_NAME),
								'maxLength' => 100,
							),
							'latitude' => array(
								'type' => 'number',
								'title' => __('Latitude', lineconnect::PLUGIN_NAME),
							),
							'longitude' => array(
								'type' => 'number',
								'title' => __('Longitude', lineconnect::PLUGIN_NAME),
							),
						),
						'required' => array(
							'title',
							'address',
							'latitude',
							'longitude',
						),
					),
				),
			),
			'imagemap' => array(
				'type'       => 'object',
				'title'      => __('Image map message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'imagemap' => array(
						'type'     => 'object',
						'title'    => __('Image map', lineconnect::PLUGIN_NAME),
						'description' => __('Imagemap messages are messages configured with an image that has multiple tappable areas. You can assign one tappable area for the entire image or different tappable areas on divided areas of the image.', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'baseUrl'  => array(
								'type' => 'string',
								'title' => __('Base Url', lineconnect::PLUGIN_NAME),
								'description' => __('Image base URL. Protocol: HTTPS', lineconnect::PLUGIN_NAME),
								'maxLength' => 2000,
							),
							'altText'  => array(
								'type' => 'string',
								'title' => __('Alt Text', lineconnect::PLUGIN_NAME),
								'description' => __('Alternative text. When a user receives a message, it will appear as an alternative to the image in the notification or chat list of their device.', lineconnect::PLUGIN_NAME),
								'maxLength' => 400,
							),
							'baseSize' => array(
								'type' => 'object',
								'title' => __('Base Size', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'width' => array(
										'type' => 'number',
										'title' => __('Width', lineconnect::PLUGIN_NAME),
										'default' => 1040,
									),
									'height' => array(
										'type' => 'number',
										'title' => __('Height', lineconnect::PLUGIN_NAME),
										'description' => __('Height of base image. Set to the height that corresponds to a width of 1040 pixels.', lineconnect::PLUGIN_NAME),
									),
								),
							),
							'video'    => array(
								'type' => 'object',
								'title' => __('Video', lineconnect::PLUGIN_NAME),
								'description' => __('You can also play a video on the image and display a label with a hyperlink after the video is finished.', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'originalContentUrl' => array(
										'type' => 'string',
										'title' => __('Original content URL', lineconnect::PLUGIN_NAME),
										'description' => __('Video file URL. Protocol: HTTPS, Video format: MP4, Max file size: 200 MB', lineconnect::PLUGIN_NAME),
									),
									'previewImageUrl' => array(
										'type' => 'string',
										'title' => __('Preview image URL', lineconnect::PLUGIN_NAME),
										'description' => __('Preview image URL. Protocol: HTTPS, Image format: JPEG or PNG, Max file size: 1 MB', lineconnect::PLUGIN_NAME),
									),
									'area' => array(
										'type' => 'object',
										'title' => __('Image map area', lineconnect::PLUGIN_NAME),
										'properties' => array(
											'x' => array(
												'type' => 'number',
												'title' => __('X', lineconnect::PLUGIN_NAME),
												'description' => __('Horizontal position of the video area relative to the left edge of the imagemap area. Value must be 0 or higher.', lineconnect::PLUGIN_NAME),
											),
											'y' => array(
												'type' => 'number',
												'title' => __('Y', lineconnect::PLUGIN_NAME),
												'description' => __('Vertical position of the video area relative to the top edge of the imagemap area. Value must be 0 or higher.', lineconnect::PLUGIN_NAME),
											),
											'width' => array(
												'type' => 'number',
												'title' => __('Width', lineconnect::PLUGIN_NAME),
											),
											'height' => array(
												'type' => 'number',
												'title' => __('Height', lineconnect::PLUGIN_NAME),
											),
										),
									),
									'externalLink' => array(
										'type' => 'object',
										'title' => __('External link', lineconnect::PLUGIN_NAME),
										'properties' => array(
											'linkUri' => array(
												'type' => 'string',
												'title' => __('Link URI', lineconnect::PLUGIN_NAME),
												'description' => __('Webpage URL. Called when the label displayed after the video is tapped. The available schemes are http, https, line, and tel', lineconnect::PLUGIN_NAME),
												'maxLength' => 1000,
											),
											'label' => array(
												'type' => 'string',
												'title' => __('Label', lineconnect::PLUGIN_NAME),
												'description' => __('Displayed after the video is finished.', lineconnect::PLUGIN_NAME),
												'maxLength' => 30,
											),
										),
									),
								),
							),
							'actions'  => array(
								'type' => 'array',
								'title' => __('Image map action objects', lineconnect::PLUGIN_NAME),
								'description' => __('Object which specifies the actions and tappable areas of an imagemap.', lineconnect::PLUGIN_NAME),
								'items' => array(
									'type' => 'object',
									'title' => __('Image map action', lineconnect::PLUGIN_NAME),
									'properties' => array(
										'type' => array(
											'type'  => 'string',
											'title' => __('Type', lineconnect::PLUGIN_NAME),
											'anyOf' => array(
												array(
													'const' => 'uri',
													'title' => __('URL', lineconnect::PLUGIN_NAME),
												),
												array(
													'const' => 'message',
													'title' => __('Message', lineconnect::PLUGIN_NAME),
												),
											),
										),
										'label' => array(
											'type' => 'string',
											'title' => __('Label', lineconnect::PLUGIN_NAME),
											'description' => __('Label for the action. Spoken when the accessibility feature is enabled on the client device.', lineconnect::PLUGIN_NAME),
											'maxLength' => 50,
										),
										'area' => array(
											'type' => 'object',
											'title' => __('Image map area', lineconnect::PLUGIN_NAME),
											'description' => __('Defines the size of a tappable area. The top left is used as the origin of the area. Set these properties based on the baseSize.width property and the baseSize.height property.', lineconnect::PLUGIN_NAME),
											'properties' => array(
												'x' => array(
													'type' => 'number',
													'title' => __('X', lineconnect::PLUGIN_NAME),
													'description' => __('Horizontal position relative to the left edge of the area. Value must be 0 or higher.', lineconnect::PLUGIN_NAME),

												),
												'y' => array(
													'type' => 'number',
													'title' => __('Y', lineconnect::PLUGIN_NAME),
													'description' => __('Vertical position relative to the top edge of the area. Value must be 0 or higher.', lineconnect::PLUGIN_NAME),
												),
												'width' => array(
													'type' => 'number',
													'title' => __('Width', lineconnect::PLUGIN_NAME),
												),
												'height' => array(
													'type' => 'number',
													'title' => __('Height', lineconnect::PLUGIN_NAME),
												),
											),
										),
									),
									'allOf'      => array(
										array(
											'if'   => array(
												'properties' => array(
													'type' => array(
														'const' => 'uri',
													),
												),
											),
											'then' => array(
												'properties' => array(
													'linkUri' => array(
														'type'       => 'string',
														'title'      => __('Link URI', lineconnect::PLUGIN_NAME),
														'description' => __('Webpage URL. The available schemes are http, https, line, and tel', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
										array(
											'if'   => array(
												'properties' => array(
													'type' => array(
														'const' => 'message',
													),
												),
											),
											'then' => array(
												'properties' => array(
													'text' => array(
														'type'       => 'string',
														'title'      => __('Text', lineconnect::PLUGIN_NAME),
													),
												),
											),
										),
									),
								),
							),
						),
						'required' => array(
							'baseUrl',
							'altText',
							'baseSize',
							'actions',
						),
					),
				),
			),
			'button_template' => array(
				'type'       => 'object',
				'title'      => __('Button Template message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'altText' => array(
						'type'  => 'string',
						'title' => __('Alt text', lineconnect::PLUGIN_NAME),
						'maxLength' => 400,
					),
					'button_template' => array(
						'type' => 'object',
						'title' => __('Button template', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'thumbnailImageUrl'    => array(
								'type'  => 'string',
								'title' => __('Thumbnail image url', lineconnect::PLUGIN_NAME),
								'description' => __('Image URL. Protocol: HTTPS, Image format: JPEG or PNG, Max width: 1024px, Max file size: 10 MB', lineconnect::PLUGIN_NAME),
								'maxLength' => 2000,
							),
							'imageAspectRatio'     => array(
								'type'  => 'string',
								'title' => __('Image aspect ratio', lineconnect::PLUGIN_NAME),
								'description' => __('Aspect ratio of the image. One of: rectangle: 1.51:1, square: 1:1', lineconnect::PLUGIN_NAME),
								'oneOf' => array(
									array(
										'const' => 'rectangle',
										'title' => __('Rectangle', lineconnect::PLUGIN_NAME),
									),
									array(
										'const' => 'square',
										'title' => __('Square', lineconnect::PLUGIN_NAME),
									),
								),
								'default' => 'rectangle',
							),
							'imageSize'            => array(
								'type'  => 'string',
								'title' => __('Image size', lineconnect::PLUGIN_NAME),
								'description' => __('Size of the image. One of: cover, contain', lineconnect::PLUGIN_NAME),
								'oneOf' => array(
									array(
										'const' => 'cover',
										'title' => __('Cover', lineconnect::PLUGIN_NAME),
										'description' => __('The image fills the entire image area. Parts of the image that do not fit in the area are not displayed.', lineconnect::PLUGIN_NAME),
									),
									array(
										'const' => 'contain',
										'title' => __('Contain', lineconnect::PLUGIN_NAME),
										'description' => __('The entire image is displayed in the image area. A background is displayed in the unused areas to the left and right of vertical images and in the areas above and below horizontal images.',  lineconnect::PLUGIN_NAME),
									),
								),
							),
							'imageBackgroundColor' => array(
								'type'  => 'string',
								'title' => __('Image background color', lineconnect::PLUGIN_NAME),
							),
							'title'                => array(
								'type'      => 'string',
								'title'     => __('Title', lineconnect::PLUGIN_NAME),
								'maxLength' => 40,
							),
							'text'                 => array(
								'type'  => 'string',
								'title' => __('Message text', lineconnect::PLUGIN_NAME),
								'description' => __('Max character limit: 160 (no image or title) 60 (message with an image or title)', lineconnect::PLUGIN_NAME),
							),
							'defaultAction'        => array(
								'$ref' => '#/definitions/action',
							),
							'actions'              => array(
								'type'     => 'array',
								'title'    => __('Actions', lineconnect::PLUGIN_NAME),
								'maxItems' => 4,
								'items'    => array(
									'$ref' => '#/definitions/action',

								),
							),
						),
						'required' => array(
							'text',
							'actions',
						),
					),
				),
				'required' => array(
					'altText',
					'button_template',
				),
			),
			'confirm_template' => array(
				'type'       => 'object',
				'title'      => __('Confirm template message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'altText' => array(
						'type'  => 'string',
						'title' => __('Alt text', lineconnect::PLUGIN_NAME),
					),
					'confirm_template' => array(
						'type' => 'object',
						'title' => __('Confirm template', lineconnect::PLUGIN_NAME),
						'description' => __('Template with two action buttons.', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'text'    => array(
								'type' => 'string',
								'title' => __('Message text', lineconnect::PLUGIN_NAME),
								'maxLength' => 240,
							),
							'actions' => array(
								'type' => 'array',
								'title' => __('Actions', lineconnect::PLUGIN_NAME),
								'description' => __('Set 2 actions for the 2 buttons', lineconnect::PLUGIN_NAME),
								'maxItems' => 2,
								'items' => array(
									'$ref' => '#/definitions/action',
								),
							),
						),
						'required' => array(
							'text',
							'actions',
						),
					),
				),
				'required' => array(
					'altText',
					'confirm_template',
				),
			),
			'carousel_template' => array(
				'type'       => 'object',
				'title'      => __('Carousel template message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'altText' => array(
						'type'  => 'string',
						'title' => __('Alt text', lineconnect::PLUGIN_NAME),
					),
					'carousel_template' => array(
						'type' => 'object',
						'title' => __('Carousel template', lineconnect::PLUGIN_NAME),
						'description' => __('Template with multiple columns which can be cycled like a carousel. The columns are shown in order when scrolling horizontally.', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'imageAspectRatio' => array(
								'type' => 'string',
								'title' => __('Image aspect ratio', lineconnect::PLUGIN_NAME),
								'description' => __('Aspect ratio of the image. One of: rectangle: 1.51:1, square: 1:1', lineconnect::PLUGIN_NAME),
								'oneOf' => array(
									array(
										'const' => 'rectangle',
										'title' => __('Rectangle', lineconnect::PLUGIN_NAME),
									),
									array(
										'const' => 'square',
										'title' => __('Square', lineconnect::PLUGIN_NAME),
									),
								),
							),
							'imageSize' => array(
								'type' => 'string',
								'title' => __('Image size', lineconnect::PLUGIN_NAME),
								'description' => __('Size of the image. One of: cover, contain. Applies to all columns.', lineconnect::PLUGIN_NAME),
								'oneOf' => array(
									array(
										'const' => 'cover',
										'title' => __('Cover', lineconnect::PLUGIN_NAME),
										'description' => __('The image fills the entire image area. Parts of the image that do not fit in the area are not displayed.', lineconnect::PLUGIN_NAME),
									),
									array(
										'const' => 'contain',
										'title' => __('Contain', lineconnect::PLUGIN_NAME),
										'description' => __('The entire image is displayed in the image area. A background is displayed in the unused areas to the left and right of vertical images and in the areas above and below horizontal images.',  lineconnect::PLUGIN_NAME),
									),
								),
							),
							'columns' => array(
								'type' => 'array',
								'title' => __('Columns', lineconnect::PLUGIN_NAME),
								'description' => __('Keep the number of actions consistent for all columns. If you use an image or title for a column, make sure to do the same for all other columns.', lineconnect::PLUGIN_NAME),
								'maxItems' => 10,
								'items' => array(
									'$ref' => '#/definitions/carousel_column',
								),
							),
						),
						'required' => array(
							'columns',
						),
					),
				),
				'required' => array(
					'altText',
					'carousel_template',
				),
			),
			'image_carousel_template' => array(
				'type'       => 'object',
				'title'      => __('Image carousel template message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'altText' => array(
						'type'  => 'string',
						'title' => __('Alt text', lineconnect::PLUGIN_NAME),
					),
					'image_carousel_template' => array(
						'type' => 'object',
						'title' => __('Image carousel template', lineconnect::PLUGIN_NAME),
						'description' => __('Template with multiple images which can be cycled like a carousel. The images are shown in order when scrolling horizontally.', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'columns' => array(
								'type' => 'array',
								'title' => __('Columns', lineconnect::PLUGIN_NAME),
								'maxItems' => 10,
								'items' => array(
									'$ref' => '#/definitions/image_carousel_column',
								),
							),
						),
						'required' => array(
							'columns',
						),
					),
				),
				'required' => array(
					'altText',
					'image_carousel_template',
				),
			),
			'flex' => array(
				'type'       => 'object',
				'title'      => __('Flex message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'flex'     => array(
						'type'     => 'object',
						'title'    => __('Flex message object', lineconnect::PLUGIN_NAME),
						'description' => __('Flex Messages are messages with a customizable layout. You can customize the layout freely based on the specification for CSS Flexible Box.', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'raw' => array(
								'type' => 'string',
								'title' => __('Flex message JSON object', lineconnect::PLUGIN_NAME),
								'description' => __('Flex message JSON object such as generated by Flex Message Simulator', lineconnect::PLUGIN_NAME),
							),
							'alttext' => array(
								'type' => 'string',
								'title' => __('Alt text', lineconnect::PLUGIN_NAME),
								'maxLength' => 400,
								'description' => __('Alt text of Flex message', lineconnect::PLUGIN_NAME),
							),
						),
						'required' => array(
							'raw',
							'alttext',
						),
					),
				),
				'required' => array(
					'flex',
				),
			),
			'raw' => array(
				'type'       => 'object',
				'title'      => __('Raw message', lineconnect::PLUGIN_NAME),
				'properties' => array(
					'raw' => array(
						'type'     => 'object',
						'title'    => __('Raw message object', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'raw' => array(
								'type' => 'string',
								'title' => __('Message JSON object', lineconnect::PLUGIN_NAME),
								'description' => __('Message JSON object', lineconnect::PLUGIN_NAME),
							),
						),
						'required' => array(
							'raw',
						),
					),
				),
			),
		);

		// message object schema
		self::$lineconnect_action_object_schema = array(
			'type'       => 'object',
			'title'      => __('Action', lineconnect::PLUGIN_NAME),
			'anyOf' => array(
				array(
					'type'       => 'object',
					'title'      => __('Postback action', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'postback' => array(
							'type' => 'object',
							'title'      => __('Postback', lineconnect::PLUGIN_NAME),
							'properties' => array(
								'label'       => array(
									'type'  => 'string',
									'title' => __('Label', lineconnect::PLUGIN_NAME),
								),
								'data'        => array(
									'type'      => 'string',
									'title'     => __('Data', lineconnect::PLUGIN_NAME),
									'description' => __('String returned via webhook in the postback.data property of the postback event', lineconnect::PLUGIN_NAME),
									'maxLength' => 300,
								),
								'displayText' => array(
									'type'      => 'string',
									'title'     => __('Display text', lineconnect::PLUGIN_NAME),
									'description' => __('Text displayed in the chat as a message sent by the user when the action is performed. Required for quick reply buttons. Optional for the other message types.', lineconnect::PLUGIN_NAME),
									'maxLength' => 300,
								),
								'inputOption' => array(
									'type'  => 'string',
									'title' => __('Input option', lineconnect::PLUGIN_NAME),
									'description' => __('The display method of such as rich menu based on user action.', lineconnect::PLUGIN_NAME),
									'oneOf' => array(
										array(
											'const' => 'closeRichMenu',
											'title' => __('Close richmenu', lineconnect::PLUGIN_NAME),
										),
										array(
											'const' => 'openRichMenu',
											'title' => __('Open richmenu', lineconnect::PLUGIN_NAME),
										),
										array(
											'const' => 'openKeyboard',
											'title' => __('Open keyboard', lineconnect::PLUGIN_NAME),
										),
										array(
											'const' => 'openVoice',
											'title' => __('Open voice', lineconnect::PLUGIN_NAME),
										),
									),
								),
							),
							'required'   => array(
								'data',
							),
							'dependencies' => array(
								'inputOption' => array(
									'oneOf' => array(
										array(
											'properties' => array(
												'inputOption' => array(
													'const' => 'closeRichMenu',
												),
											),
										),
										array(
											'properties' => array(
												'inputOption' => array(
													'const' => 'openRichMenu',
												),
											),
										),
										array(
											'properties' => array(
												'inputOption' => array(
													'const' => 'openKeyboard',
												),
												'fillInText' => array(
													'type' => 'string',
													'title' => __('Fill in text', lineconnect::PLUGIN_NAME),
													'description' => __('String to be pre-filled in the input field when the keyboard is opened. Valid only when the inputOption property is set to openKeyboard.', lineconnect::PLUGIN_NAME),
													'maxLength' => 300,
												),
											),
										),
										array(
											'properties' => array(
												'inputOption' => array(
													'const' => 'openVoice',
												),
											),
										),
									),
								),
							),
						),
					),
				),
				array(
					'type'       => 'object',
					'title'      => __('Message action', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'message' => array(
							'type'       => 'object',
							'title'      => __('Message', lineconnect::PLUGIN_NAME),
							'properties' => array(
								'label' => array(
									'type'  => 'string',
									'title' => __('Label', lineconnect::PLUGIN_NAME),
								),
								'text'  => array(
									'type'      => 'string',
									'title'     => __('Text', lineconnect::PLUGIN_NAME),
									'maxLength' => 300,
								),
							),
							'required'   => array(
								'text',
							),
						),
					),
				),
				array(
					'type'       => 'object',
					'title'      => __('URI action', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'uri' => array(
							'type'       => 'object',
							'title'      => __('URI', lineconnect::PLUGIN_NAME),
							'properties' => array(
								'label' => array(
									'type'  => 'string',
									'title' => __('Label', lineconnect::PLUGIN_NAME),
								),
								'uri'   => array(
									'type'      => 'string',
									'title'     => __('URI', lineconnect::PLUGIN_NAME),
									'description' => __('URI opened when the action is performed. The available schemes are http, https, line, and tel', lineconnect::PLUGIN_NAME),
									'maxLength' => 1000,
								),
							),
							'required'   => array(
								'uri',
							),
						),
					),
				),
				array(
					'type'       => 'object',
					'title'      => __('Datetime picker action', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'datetimepicker' => array(
							'type'       => 'object',
							'title'      => __('Datetime picker', lineconnect::PLUGIN_NAME),
							'properties' => array(
								'label'   => array(
									'type'  => 'string',
									'title' => __('Label', lineconnect::PLUGIN_NAME),
								),
								'data'    => array(
									'type'      => 'string',
									'title'     => __('Data', lineconnect::PLUGIN_NAME),
									'maxLength' => 300,
								),
								'mode'    => array(
									'type'  => 'string',
									'title' => __('Action mode', lineconnect::PLUGIN_NAME),
									'oneOf' => array(
										array(
											'const' => 'date',
											'title' => __('Select date', lineconnect::PLUGIN_NAME),
										),
										array(
											'const' => 'time',
											'title' => __('Select time', lineconnect::PLUGIN_NAME),
										),
										array(
											'const' => 'datetime',
											'title' => __('Select date and time', lineconnect::PLUGIN_NAME),
										),
									),
								),
								'initial' => array(
									'type'  => 'string',
									'title' => __('Initial date or time', lineconnect::PLUGIN_NAME),
								),
								'max'     => array(
									'type'  => 'string',
									'title' => __('Max date or time', lineconnect::PLUGIN_NAME),
								),
								'min'     => array(
									'type'  => 'string',
									'title' => __('Min date or time', lineconnect::PLUGIN_NAME),
								),
							),
							'required'   => array(
								'data',
								'mode',
							),
						),
					),
				),
				array(
					'type' => 'object',
					'title' => __('Camera action', lineconnect::PLUGIN_NAME),
					'description' => __('This action can be configured only with quick reply buttons.', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'camera' => array(
							'type' => 'object',
							'title' => __('Camera', lineconnect::PLUGIN_NAME),
							'properties' => array(
								'label' => array(
									'type'      => 'string',
									'title'     => __('Label', lineconnect::PLUGIN_NAME),
									'maxLength' => 20,
								),
							),
							'required'   => array(
								'label',
							),
						),
					),
				),
				array(
					'type' => 'object',
					'title' => __('Camera Roll action', lineconnect::PLUGIN_NAME),
					'description' => __('This action can be configured only with quick reply buttons.', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'cameraRoll' => array(
							'type' => 'object',
							'title' => __('Camera Roll', lineconnect::PLUGIN_NAME),
							'properties' => array(
								'label' => array(
									'type'      => 'string',
									'title'     => __('Label', lineconnect::PLUGIN_NAME),
									'maxLength' => 20,
								),
							),
							'required'   => array(
								'label',
							),
						),
					),
				),
				array(
					'type'       => 'object',
					'title'      => __('Location action', lineconnect::PLUGIN_NAME),
					'description' => __('This action can be configured only with quick reply buttons.', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'location' => array(
							'type'       => 'object',
							'title'      => __('Location', lineconnect::PLUGIN_NAME),
							'properties' => array(
								'label' => array(
									'type'      => 'string',
									'title'     => __('Label', lineconnect::PLUGIN_NAME),
									'maxLength' => 20,
								),
							),
							'required'   => array(
								'label',
							),
						),
					),
				),
				array(
					'type'       => 'object',
					'title'      => __('Clipboard action', lineconnect::PLUGIN_NAME),
					'description' => __('When a user taps a control associated with this action, the text specified in the clipboardText property is copied to the device clipboard.', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'clipboard' => array(
							'type'       => 'object',
							'title'      => __('Clipboard', lineconnect::PLUGIN_NAME),
							'properties' => array(
								'label' => array(
									'type'  => 'string',
									'title' => __('Label', lineconnect::PLUGIN_NAME),
									'maxLength' => 20,
								),
								'clipboardText' => array(
									'type'  => 'string',
									'title' => __('Clipboard text', lineconnect::PLUGIN_NAME),
									'maxLength' => 1000,
								),
							),
							'required'   => array(
								'label',
								'clipboardText',
							),
						),
					),
				),
				array(
					'type'       => 'object',
					'title'      => __('Rich menu switch action', lineconnect::PLUGIN_NAME),
					'description' => __('This action can be configured only with richmenus.', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'richmenuswitch' => array(
							'type'       => 'object',
							'title'      => __('Rich menu switch', lineconnect::PLUGIN_NAME),
							'properties' => array(
								'label' => array(
									'type'  => 'string',
									'title' => __('Label', lineconnect::PLUGIN_NAME),
									'maxLength' => 20,
								),
								'richMenuAliasId' => array(
									'type'  => 'string',
									'title' => __('Rich menu alias ID', lineconnect::PLUGIN_NAME),
									'maxLength' => 300,
								),
								'data' => array(
									'type'  => 'string',
									'title' => __('Data', lineconnect::PLUGIN_NAME),
									'description' => __('String returned via webhook in the postback.data property of the postback event', lineconnect::PLUGIN_NAME),
									'maxLength' => 300,
								),
							),
							'required'   => array(
								'data',
								'richMenuAliasId',
							),
						),
					),
				),
			),
		);

		// 　Message schema
		self::$lineconnect_message_schema = array(
			'type'        => 'object',
			// 'properties'  => array(
			//'messages' => array(
			// 'type'  => 'array',
			// 'title' => __( 'Messages', lineconnect::PLUGIN_NAME ),
			// 'items' => array(
			// 'type'       => 'object',
			//'title'      => __( 'Message', lineconnect::PLUGIN_NAME ),
			'properties' => array(
				'message'    => array(),
				'quickReply' => array(
					'type'       => 'object',
					'title'      => __('Quick reply', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'items' => array(
							'type'  => 'array',
							'title' => __('Quick reply container', lineconnect::PLUGIN_NAME),
							'items' => array(
								'type'       => 'object',
								'title'      => __('Quick reply button object', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'imageUrl' => array(
										'type' => 'string',
										'title' => __('Image URL', lineconnect::PLUGIN_NAME),
									),
									'action'   => array(
										'$ref' => '#/definitions/action',
									),
								),
							),
							'maxItems' => 13,
						),
					),
				),
				'sender'     => array(
					'type'       => 'object',
					'title'      => __('Sender', lineconnect::PLUGIN_NAME),
					'description' => __('Customize icon and display name', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'name'    => array(
							'type'      => 'string',
							'title'     => __('Sender name', lineconnect::PLUGIN_NAME),
							'description' => __('Display name. Certain words such as LINE may not be used.', lineconnect::PLUGIN_NAME),
							'maxLength' => 20,
						),
						'iconUrl' => array(
							'type'  => 'string',
							'title' => __('Sender icon URL', lineconnect::PLUGIN_NAME),
							'description' => __('URL of the image to display as an icon when sending a message.HTTPS, PNG, 1:1, 1MB', lineconnect::PLUGIN_NAME),
							'maxLength' => 2000,
						),
					),
				),
			),
			// ),
			// ),
			// ),
			'definitions' => array(
				'action' => self::$lineconnect_action_object_schema,
				'carousel_column'       => array(
					'type'       => 'object',
					'properties' => array(
						'thumbnailImageUrl'    => array(
							'type'  => 'string',
							'title' => __('Thumbnail image url', lineconnect::PLUGIN_NAME),
							'description' => __('Image URL. Protocol: HTTPS, Image format: JPEG or PNG, Aspect ratio: 1.51:1 ,Max width: 1024px, Max file size: 10 MB', lineconnect::PLUGIN_NAME),
							'maxLength' => 2000,
						),
						'imageBackgroundColor' => array(
							'type'  => 'string',
							'title' => __('Image background color', lineconnect::PLUGIN_NAME),
						),
						'title'                => array(
							'type'      => 'string',
							'title'     => __('Title', lineconnect::PLUGIN_NAME),
							'maxLength' => 40,
						),
						'text'                 => array(
							'type'  => 'string',
							'title' => __('Message text', lineconnect::PLUGIN_NAME),
							'description' => __('Max character limit: 120 (no image or title) 60 (message with an image or title)', lineconnect::PLUGIN_NAME),
						),
						'defaultAction'        => array(
							'$ref' => '#/definitions/action',
						),
						'actions'              => array(
							'type'     => 'array',
							'title'    => __('Actions', lineconnect::PLUGIN_NAME),
							'maxItems' => 3,
							'items'    => array(
								'$ref' => '#/definitions/action',
							),
						),
					),
					'required'   => array(
						'text',
						'actions',
					),
				),
				'image_carousel_column' => array(
					'type'       => 'object',
					'properties' => array(
						'imageUrl' => array(
							'type'  => 'string',
							'title' => __('Image url', lineconnect::PLUGIN_NAME),
							'description' => __('Image URL. Protocol: HTTPS, Image format: JPEG or PNG, Aspect ratio: 1:1 ,Max width: 1024px, Max file size: 10 MB', lineconnect::PLUGIN_NAME),
							'maxLength' => 2000,
						),
						'action'   => array(
							'$ref' => '#/definitions/action',
						),
					),
					'required'   => array(
						'imageUrl',
						'action',
					),
				),
			),
		);

		// Message Sub UI schema
		self::$lineconnect_message_uischema = array(
			'ui:submitButtonOptions' => array(
				'norender' => true,
			),
			// 'messages'               => array(
			// 'items' => array(
			// 'ui:order' => array( 'message', '*', ),
			'message'  => array(
				'text' => array(
					'ui:classNames' => 'title-hidden',
					'text' => array(
						'ui:widget'  => 'textarea',
						'ui:options' => array(
							'rows' => 10,
						),
					),
				),
				'imagemap' => array(
					'actions' => array(
						'items' => array(
							'ui:order' => array('type', 'label', 'linkUri', 'text', '*',),
						),
						'ui:options' => array(
							'addText' =>  __('Add image map action', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'button_template' => array(
					'text' => array(
						'ui:widget'  => 'textarea',
						'ui:options' => array(
							'rows' => 4,
						),
					),
					'actions' => array(
						'ui:options' => array(
							'addText' =>  __('Add action', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'confirm_template' => array(
					'text' => array(
						'ui:widget'  => 'textarea',
						'ui:options' => array(
							'rows' => 5,
						),
					),
					'actions' => array(
						'ui:options' => array(
							'addText' =>  __('Add action', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'carousel_template' => array(
					'columns' => array(
						'items' => array(
							'text' => array(
								'ui:widget'  => 'textarea',
								'ui:options' => array(
									'rows' => 4,
								),
							),
							'actions' => array(
								'ui:options' => array(
									'addText' =>  __('Add action', lineconnect::PLUGIN_NAME),
								),
							),
						),
						'ui:options' => array(
							'addText' =>  __('Add column', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'image_carousel_template' => array(
					'columns' => array(
						'ui:options' => array(
							'addText' =>  __('Add column', lineconnect::PLUGIN_NAME),
						),
					),
				),
				'flex'  => array(
					'raw' => array(
						'ui:widget'  => 'textarea',
						'ui:options' => array(
							'rows' => 15,
						),
					),
				),
				'raw'  => array(
					'raw' => array(
						'ui:widget'  => 'textarea',
						'ui:options' => array(
							'rows' => 15,
						),
					),
				),
			),
			'quickReply' => array(
				'items' => array(
					'ui:options' => array(
						'addText' =>  __('Add quick reply', lineconnect::PLUGIN_NAME),
					),
				),
			),
			// ),
			// ),
		);

		// Richmenu type schema
		self::$lineconnect_richmenu_template_bounds = array(
			array(
				'id' => '3-2_3x2_3_3',
				'title' => __('3:2 3x2 row1: 3 col, row2: 3 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_3x2_3_3.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 833,
						'height' => 843,
					),
					array(
						'x'      => 834,
						'y'      => 0,
						'width'  => 833,
						'height' => 843,
					),
					array(
						'x'      => 1667,
						'y'      => 0,
						'width'  => 833,
						'height' => 843,
					),
					array(
						'x'      => 0,
						'y'      => 843,
						'width'  => 833,
						'height' => 843,
					),
					array(
						'x'      => 834,
						'y'      => 843,
						'width'  => 833,
						'height' => 843,
					),
					array(
						'x'      => 1667,
						'y'      => 843,
						'width'  => 833,
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-2_2x2_2_2',
				'title' => __('3:2 2x2 row1: 2 col, row2: 2 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_2x2_2_2.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 1250,
						'height' => 843,
					),
					array(
						'x'      => 1251,
						'y'      => 0,
						'width'  => 1250,
						'height' => 843,
					),
					array(
						'x'      => 0,
						'y'      => 843,
						'width'  => 1250,
						'height' => 843,
					),
					array(
						'x'      => 1251,
						'y'      => 843,
						'width'  => 1250,
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-2_1x1',
				'title' => __('3:2 1x1 row1: 1', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_1x1.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 2500,
						'height' => 1686,
					),
				),
			),
			array(
				'id' => '3-2_1x2_1_2',
				'title' => __('3:2 1x2 row1: 1 col, row2: 1col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_1x2_1_2.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 1250,
						'height' => 843,
					),
					array(
						'x'      => 1251,
						'y'      => 0,
						'width'  => 1250,
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-2_2x1_2',
				'title' => __('3:2 2x1 row1: 2 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_2x1_2.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 1250,
						'height' => 1686,
					),
					array(
						'x'      => 1251,
						'y'      => 0,
						'width'  => 1250,
						'height' => 1686,
					),
				),
			),
			array(
				'id' => '3-2_3x1_2_2-1',
				'title' => __('3:2 3x1 row1: 2 col, 2:1', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_3x1_2_2-1.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 1666, // 幅を調整
						'height' => 1686,
					),
					array(
						'x'      => 1667,
						'y'      => 0,
						'width'  => 833, // 幅を調整
						'height' => 1686,
					),
				),
			),
			array(
				'id' => '3-2_3x1_2_1-2',
				'title' => __('3:2 3x1 row1: 2 col, 1:2', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_3x1_2_1-2.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 833, // 幅を調整
						'height' => 1686,
					),
					array(
						'x'      => 834,
						'y'      => 0,
						'width'  => 1666, // 幅を調整
						'height' => 1686,
					),
				),
			),
			array(
				'id' => '3-2_3x2_2_2-1_2_2-1',
				'title' => __('3:2 3x2 row1: 2 col (2:1), row2: 2col (2:1)', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_3x2_2_2-1_2_2-1.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 1666, // 左の2／3を一つのブロック
						'height' => 1686,
					),
					array(
						'x'      => 1667,
						'y'      => 0,
						'width'  => 833, // 右の1／3,上部のブロック
						'height' => 843,
					),
					array(
						'x'      => 1667,
						'y'      => 843,
						'width'  => 833, // 右の1／3,下部のブロック
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-2_3x2_1_3',
				'title' => __('3:2 3x2 row1: 1 col , row2: 3 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_3x2_1_3.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 2500, // 上は3つぶち抜き
						'height' => 843,
					),
					array(
						'x'      => 0,
						'y'      => 843,
						'width'  => 833, // 下は3つに分割
						'height' => 843,
					),
					array(
						'x'      => 834,
						'y'      => 843,
						'width'  => 833, // 下は3つに分割
						'height' => 843,
					),
					array(
						'x'      => 1667,
						'y'      => 843,
						'width'  => 833, // 下は3つに分割
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-2_3x2_3_1',
				'title' => __('3:2 3x2 row1: 3 col, row2: 1 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_3x2_3_1.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 833, // 上は3つに分割
						'height' => 843,
					),
					array(
						'x'      => 834,
						'y'      => 0,
						'width'  => 833, // 上は3つに分割
						'height' => 843,
					),
					array(
						'x'      => 1667,
						'y'      => 0,
						'width'  => 833, // 上は3つに分割
						'height' => 843,
					),
					array(
						'x'      => 0,
						'y'      => 843,
						'width'  => 2500, // 下は3つぶち抜き
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-2_2x2_1_2',
				'title' => __('3:2 2x2 row1: 1 col, row2: 2 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_2x2_1_2.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 2500, // 上は2つぶち抜き
						'height' => 843,
					),
					array(
						'x'      => 0,
						'y'      => 843,
						'width'  => 1250, // 下は2つに分割
						'height' => 843,
					),
					array(
						'x'      => 1251,
						'y'      => 843,
						'width'  => 1250, // 下は2つに分割
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-2_2x2_2_1',
				'title' => __('3:2 2x2 row1: 2 col, row2: 1 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-2_2x2_2_1.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 1686,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 1250, // 上は2つに分割
						'height' => 843,
					),
					array(
						'x'      => 1251,
						'y'      => 0,
						'width'  => 1250, // 上は2つに分割
						'height' => 843,
					),
					array(
						'x'      => 0,
						'y'      => 843,
						'width'  => 2500, // 下は2つぶち抜き
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-1_3x1_3',
				'title' => __('3:1 3x1 row1: 3 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-1_3x1_3.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 843,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 833,
						'height' => 843,
					),
					array(
						'x'      => 834,
						'y'      => 0,
						'width'  => 833,
						'height' => 843,
					),
					array(
						'x'      => 1667,
						'y'      => 0,
						'width'  => 833,
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-1_3x1_2_1-2',
				'title' => __('3:1 3x1 row1: 2 col (1:2)', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-1_3x1_2_1-2.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 843,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 833, // 1:2の比率
						'height' => 843,
					),
					array(
						'x'      => 834,
						'y'      => 0,
						'width'  => 1666, // 1:2の比率
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-1_3x1_2_2-1',
				'title' => __('3:1 3x1 row1: 2 col (2:1)', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-1_3x1_2_2-1.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 843,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 1666, // 2:1の比率
						'height' => 843,
					),
					array(
						'x'      => 1667,
						'y'      => 0,
						'width'  => 833, // 2:1の比率
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-1_2x1_2',
				'title' => __('3:1 2x1 row1: 2 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-1_2x1_2.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 843,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 1250,
						'height' => 843,
					),
					array(
						'x'      => 1251,
						'y'      => 0,
						'width'  => 1250,
						'height' => 843,
					),
				),
			),
			array(
				'id' => '3-1_1x1_1',
				'title' => __('3:1 1x1 row1: 1 col', lineconnect::PLUGIN_NAME),
				'image' => plugins_url('assets/richmenu/3-1_1x1_1.png', __DIR__),
				'size' => array(
					'width'  => 2500,
					'height' => 843,
				),
				'bounds' => array(
					array(
						'x'      => 0,
						'y'      => 0,
						'width'  => 2500,
						'height' => 843,
					),
				),
			),
		);

		// Richmenu type UI schema
		// self::$lineconnect_richmenu_type_uischema = array(
		// 	'type' => array(
		// 		'ui:widget' => 'radio',
		// 		'ui:enableMarkdownInDescription' => true,
		// 	),
		// );

		// Richmenu default data
		self::$lineconnect_richmenu_template_defalut_data = array(
			'richMenuId' => '',
			'size' => array(),
			'selected' => true,
			'name'     => __('New richmenu', lineconnect::PLUGIN_NAME),
			'chatBarText' => __('MENU', lineconnect::PLUGIN_NAME),
			'areas'    => array(),
		);

		// Richmenu schema
		self::$lineconnect_richmenu_schema = array(
			'type'       => 'object',
			'properties' => array(
				'richMenuId' => array(
					'type'  => 'string',
					'title' => __('Rich menu ID', lineconnect::PLUGIN_NAME),
					'description' => __('Unique identifier for the rich menu. Max character limit: 100', lineconnect::PLUGIN_NAME),
					'maxLength' => 100,
				),
				'name'     => array(
					'type'      => 'string',
					'title'     => __('Name', lineconnect::PLUGIN_NAME),
					'description' => __('This value can be used to help manage your rich menus and is not displayed to users.', lineconnect::PLUGIN_NAME),
					'maxLength' => 300,
				),
				'size' => array(
					'type'       => 'object',
					'title'      => __('Size', lineconnect::PLUGIN_NAME),
					'description' => __('The width and height of the rich menu displayed in the chat. The aspect ratio (width / height) must be at least 1.45.', lineconnect::PLUGIN_NAME),
					'properties' => array(
						'width'  => array(
							'type'  => 'integer',
							'title' => __('Width', lineconnect::PLUGIN_NAME),
							'description' => __('Width of the rich menu. Must be between 800px and 2500px.', lineconnect::PLUGIN_NAME),
							'minimum' => 800,
							'maximum' => 2500,
						),
						'height' => array(
							'type'  => 'integer',
							'title' => __('Height', lineconnect::PLUGIN_NAME),
							'description' => __('Height of the rich menu. Must be at least 250px.', lineconnect::PLUGIN_NAME),
							'minimum' => 250,
						),
					),
					'required'   => array(
						'width',
						'height',
					),
				),
				'areas'    => array(
					'type'  => 'array',
					'title' => __('Tap Areas', lineconnect::PLUGIN_NAME),
					'items' => array(
						'type'       => 'object',
						'title'      => __('Area', lineconnect::PLUGIN_NAME),
						'properties' => array(
							'bounds' => array(
								'type'       => 'object',
								'title'      => __('Bounds', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'x'      => array(
										'type'  => 'integer',
										'title' => __('X', lineconnect::PLUGIN_NAME),
										'description' => __('Horizontal position of the tappable area’s top-left corner (min: 0).', lineconnect::PLUGIN_NAME),
										'minimum' => 0,
									),
									'y'      => array(
										'type'  => 'integer',
										'title' => __('Y', lineconnect::PLUGIN_NAME),
										'description' => __('Vertical position of the tappable area’s top-left corner (min: 0).', lineconnect::PLUGIN_NAME),
										'minimum' => 0,
									),
									'width'  => array(
										'type'  => 'integer',
										'title' => __('Width', lineconnect::PLUGIN_NAME),
										'description' => __('Tappable area width (max: 2500).', lineconnect::PLUGIN_NAME),
										'maximum' => 2500,
									),
									'height' => array(
										'type'  => 'integer',
										'title' => __('Height', lineconnect::PLUGIN_NAME),
										'description' => __('Tappable area height (max: 1686).', lineconnect::PLUGIN_NAME),
										'maximum' => 1686,
									),
								),
								'required'   => array(
									'x',
									'y',
									'width',
									'height',
								),
							),
							'action' => array(
								'$ref' => '#/definitions/action',
							),
						),
					),
					'maxItems' => 20,
				),
				'selected' => array(
					'type'    => 'boolean',
					'title'   => __('Display the rich menu by default', lineconnect::PLUGIN_NAME),
					'default' => false,
				),
				'chatBarText' => array(
					'type'      => 'string',
					'title'     => __('Chat bar text', lineconnect::PLUGIN_NAME),
					'description' => __('Text displayed in the chat bar. Max character limit: 14', lineconnect::PLUGIN_NAME),
					'maxLength' => 14,
				),
			),
			'required'   => array(
				'size',
				'name',
				'chatBarText',
				'areas',
			),
			'definitions' => array(
				'action' => self::$lineconnect_action_object_schema,
			),
		);

		// Richmenu UI schema
		self::$lineconnect_richmenu_uischema = array(
			'ui:submitButtonOptions' => array(
				'norender' => true,
			),
			'richMenuId' => array(
				'ui:widget' => 'hidden',
			),
			'size' => array(
				'width' => array(
					'ui:widget' => 'updown',
				),
				'height' => array(
					'ui:widget' => 'updown',
				),
			),
			'areas' => array(
				'items' => array(
					'ui:order' => array('bounds', 'action',),
				),
				'ui:options' => array(
					'addText' =>  __('Add tap areas', lineconnect::PLUGIN_NAME),
					'copyable' => true,
				),
			),
		);

		// Audience schema
		self::$lineconnect_audience_schema = array(
			'type'       => 'object',
			'title'      => __('Audience', lineconnect::PLUGIN_NAME),
			'properties' => array(
				'condition' => array(
					'$ref' => '#/definitions/condition',
				),
			),
			'required'   => array(
				'condition',
			),
			'definitions' => array(
				'condition' => array(
					'title' => __('Audience condition', lineconnect::PLUGIN_NAME),
					'type' => 'object',
					'properties' => array(
						'conditions' => array(
							'type' => 'array',
							'title' => __('Audience condition group', lineconnect::PLUGIN_NAME),
							'minItems' => 1,
							'items' => array(
								'type'  => 'object',
								'title' => __('Audience condition', lineconnect::PLUGIN_NAME),
								'properties' => array(
									'type' => array(
										'type' => 'string',
										'title' =>  __('Type', lineconnect::PLUGIN_NAME),
										'anyOf' => array(
											array(
												'const' => 'channel',
												'title' => __('Channel', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'link',
												'title' => __('Link status', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'role',
												'title' => __('Role', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'lineUserId',
												'title' => __('Line user ID', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'wpUserId',
												'title' => __('WordPress user ID', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'user_email',
												'title' => __('Email', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'user_login',
												'title' => __('User login name', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'display_name',
												'title' => __('User display name', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'usermeta',
												'title' => __('User meta', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'profile',
												'title' => __('Profile', lineconnect::PLUGIN_NAME),
											),
											array(
												'const' => 'group',
												'title' => __('Audience condition group', lineconnect::PLUGIN_NAME),
											),
										),
									)
								),
								'dependencies' => array(
									'type' => array(
										'oneOf' => array(
											array(
												'properties' => array(
													'type' => array(
														'const' => 'channel',
													),
													'secret_prefix' => array(
														'$ref' => '#/definitions/secret_prefix',
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'link',
													),
													'link' => array(
														'type' => 'object',
														'title' => __('Link status', lineconnect::PLUGIN_NAME),
														'properties' => array(
															'type' => array(
																'type' => 'string',
																'title' => __('Type', lineconnect::PLUGIN_NAME),
																'anyOf' => array(
																	array(
																		'const' => 'broadcast',
																		'title' => __('All friends(Broadcast)', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'all',
																		'title' => __('All recognized friends(Multicast)', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'linked',
																		'title' => __('Linked', lineconnect::PLUGIN_NAME),
																	),
																	array(
																		'const' => 'unlinked',
																		'title' => __('Unlinked', lineconnect::PLUGIN_NAME),
																	),
																),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'role',
													),
													'role' => array(
														'$ref' => '#/definitions/role',
													),
													'match' => array(
														'type' => 'string',
														'title' => __('Match type', lineconnect::PLUGIN_NAME),
														'description' => __('Select how to match user roles', lineconnect::PLUGIN_NAME),
														'default' => 'role__in',
														'anyOf' => array(
															array(
																'const' => 'role',
																'title' => __('Must have all roles (AND)', lineconnect::PLUGIN_NAME),
															),
															array(
																'const' => 'role__in',
																'title' => __('Must have at least one role (OR)', lineconnect::PLUGIN_NAME),
															),
															array(
																'const' => 'role__not_in',
																'title' => __('Must not have these roles (NOT)', lineconnect::PLUGIN_NAME),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'lineUserId',
													),
													'lineUserId' => array(
														'type' => 'array',
														'title' => __('LINE user ID', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'string',
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'wpUserId',
													),
													'wpUserId' => array(
														'type' => 'array',
														'title' => __('WordPress User ID', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'integer',
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'user_email',
													),
													'user_email' => array(
														'type' => 'array',
														'title' => __('Email', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'string',
															'format' => 'email',
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'user_login',
													),
													'user_login' => array(
														'type' => 'array',
														'title' => __('User login name', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'string',
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'display_name',
													),
													'display_name' => array(
														'type' => 'array',
														'title' => __('User display name', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'string',
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'usermeta',
													),
													'usermeta' => array(
														'type' => 'array',
														'title' => __('User Meta', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'object',
															'required' => ['key', 'value', 'compare'],
															'properties' => array(
																'key' => array(
																	'type' => 'string',
																	'title' => __('Meta Key', lineconnect::PLUGIN_NAME),
																),
																'compare' => array(
																	'$ref' => '#/definitions/compare',
																),
															),
															'dependencies' => array(
																'compare' => array(
																	'oneOf' => array(
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'IN',
																						'NOT IN',
																					),
																				),
																				'values' => array(
																					'type' => 'array',
																					'title' => __('Values', lineconnect::PLUGIN_NAME),
																					'minItems' => 1,
																					'items' => array(
																						'type' => 'string',
																					),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'BETWEEN',
																						'NOT BETWEEN',
																					),
																				),
																				'values' => array(
																					'type' => 'array',
																					'title' => __('Values', lineconnect::PLUGIN_NAME),
																					'minItems' => 2,
																					'maxItems' => 2,
																					'items' => array(
																						'type' => 'string',
																					),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'=',
																						'!=',
																						'>',
																						'>=',
																						'<',
																						'<=',
																						'LIKE',
																						'NOT LIKE',
																						'REGEXP',
																						'NOT REGEXP',
																					),
																				),
																				'value' => array(
																					'type' => 'string',
																					'title' => __('Value', lineconnect::PLUGIN_NAME),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'EXISTS',
																						'NOT EXISTS',
																					),
																				),
																			),
																		),
																	),
																),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'profile',
													),
													'profile' => array(
														'type' => 'array',
														'title' => __('Profile data', lineconnect::PLUGIN_NAME),
														'minItems' => 1,
														'items' => array(
															'type' => 'object',
															'required' => ['key', 'value', 'compare'],
															'properties' => array(
																'key' => array(
																	'type' => 'string',
																	'title' => __('Profile field', lineconnect::PLUGIN_NAME),
																),
																'compare' => array(
																	'$ref' => '#/definitions/compare',
																),
															),
															'dependencies' => array(
																'compare' => array(
																	'oneOf' => array(
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'IN',
																						'NOT IN',
																					),
																				),
																				'values' => array(
																					'type' => 'array',
																					'title' => __('Values', lineconnect::PLUGIN_NAME),
																					'minItems' => 1,
																					'items' => array(
																						'type' => 'string',
																					),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'BETWEEN',
																						'NOT BETWEEN',
																					),
																				),
																				'values' => array(
																					'type' => 'array',
																					'title' => __('Values', lineconnect::PLUGIN_NAME),
																					'minItems' => 2,
																					'maxItems' => 2,
																					'items' => array(
																						'type' => 'string',
																					),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'=',
																						'!=',
																						'>',
																						'>=',
																						'<',
																						'<=',
																						'LIKE',
																						'NOT LIKE',
																						'REGEXP',
																						'NOT REGEXP',
																					),
																				),
																				'value' => array(
																					'type' => 'string',
																					'title' => __('Value', lineconnect::PLUGIN_NAME),
																				),
																			),
																		),
																		array(
																			'properties' => array(
																				'compare' => array(
																					'enum' => array(
																						'EXISTS',
																						'NOT EXISTS',
																					),
																				),
																			),
																		),
																	),
																),
															),
														),
													),
												),
											),
											array(
												'properties' => array(
													'type' => array(
														'const' => 'group',
													),
													'condition' => array(
														'$ref' => '#/definitions/condition',
													),
												),
											),
										),
									),
								),
							),
						),
						'operator' => array(
							'type'  => 'string',
							'title' => __('Operator', lineconnect::PLUGIN_NAME),
							'default' => 'and',
							'oneOf' => array(
								array(
									'const' => 'and',
									'title' => __('And', lineconnect::PLUGIN_NAME),
									'description' => __('All conditions must be true', lineconnect::PLUGIN_NAME),
								),
								array(
									'const' => 'or',
									'title' => __('Or', lineconnect::PLUGIN_NAME),
									'description' => __('At least one condition must be true', lineconnect::PLUGIN_NAME),
								),
							),
						),
					),
				),
				'role' => array(
					'type' => 'array',
					'title' => __('Role', lineconnect::PLUGIN_NAME),
					'items' => array(
						'type' => 'string',
						'oneOf' => array(),
					),
					'uniqueItems' => true,
				),
				'secret_prefix' => array(
					'type' => 'array',
					'title' => __('Channel', lineconnect::PLUGIN_NAME),
					'description' => __('Target channel', lineconnect::PLUGIN_NAME),
					'uniqueItems' => true,
					'items' => array(
						'type' => 'string',
						'oneOf' => array(),
					),
				),
				'compare' => array(
					'type' => 'string',
					'title' => __('Compare method', lineconnect::PLUGIN_NAME),
					'default' => '=',
					'anyOf' => array(
						array(
							'const' => '=',
							'title' => __('Equals', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '!=',
							'title' => __('Not equals', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '>',
							'title' => __('Greater than', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '>=',
							'title' => __('Greater than or equal', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '<',
							'title' => __('Less than', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => '<=',
							'title' => __('Less than or equal', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'LIKE',
							'title' => __('Contains (String)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT LIKE',
							'title' => __('Not contains (String)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'IN',
							'title' => __('In (Array)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT IN',
							'title' => __('Not in (Array)', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'BETWEEN',
							'title' => __('Between 2 values', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT BETWEEN',
							'title' => __('Not Between 2 values', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'EXISTS',
							'title' => __('Exists', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT EXISTS',
							'title' => __('Not exists', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'REGEXP',
							'title' => __('Regular expression match', lineconnect::PLUGIN_NAME),
						),
						array(
							'const' => 'NOT REGEXP',
							'title' => __('No regular expression match', lineconnect::PLUGIN_NAME),
						),
					),
				),
			),
		);

		// Audience type UI schema
		self::$lineconnect_audience_uischema = array(
			'ui:submitButtonOptions' => array(
				'norender' => true,
			),

			'condition' => array(
				// 'ui:classNames' => 'title-hidden',
				'ui:options' => array(
					"label" => false,
				),
				'conditions' => array(
					'ui:options' => array(
						'addText' =>  __('Add condition', lineconnect::PLUGIN_NAME),
						'copyable' => true,
					),
					'items' => array(
						'userId' => array(
							'ui:options' => array(
								'addText' =>  __('Add LINE user ID', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'wpUserId' => array(
							'ui:options' => array(
								'addText' =>  __('Add WordPress user ID', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'email' => array(
							'ui:options' => array(
								'addText' =>  __('Add email', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'username' => array(
							'ui:options' => array(
								'addText' =>  __('Add username', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'userMeta' => array(
							'ui:options' => array(
								'addText' =>  __('Add user meta', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'profile' => array(
							'ui:options' => array(
								'addText' =>  __('Add profile data', lineconnect::PLUGIN_NAME),
								'copyable' => true,
							),
						),
						'condition' => array(
							'$.ref' => 'condition',
						),

					),
				),
			),
		);

		self::$lineconnect_rjsf_translate_string = apply_filters(
			lineconnect::FILTER_PREFIX . 'lineconnect_rjsf_translate_string',
			array(
				'Item'                                   => __('Item', lineconnect::PLUGIN_NAME),
				/** Missing items reason, used by ArrayField */
				'Missing items definition'               => __('Missing items definition', lineconnect::PLUGIN_NAME),
				/** Yes label, used by BooleanField */
				'Yes'                                    => __('Yes', lineconnect::PLUGIN_NAME),
				/** No label, used by BooleanField */
				'No'                                     => __('No', lineconnect::PLUGIN_NAME),
				/** Close label, used by ErrorList */
				'Close'                                  => __('Close', lineconnect::PLUGIN_NAME),
				/** Errors label, used by ErrorList */
				'Errors'                                 => __('Errors', lineconnect::PLUGIN_NAME),
				/** New additionalProperties string default value, used by ObjectField */
				'New Value'                              => __('New Value', lineconnect::PLUGIN_NAME),
				/** Add button title, used by AddButton */
				'Add'                                    => __('Add', lineconnect::PLUGIN_NAME),
				/** Add button title, used by AddButton */
				'Add Item'                               => __('Add Item', lineconnect::PLUGIN_NAME),
				/** Copy button title, used by IconButton */
				'Copy'                                   => __('Copy', lineconnect::PLUGIN_NAME),
				/** Move down button title, used by IconButton */
				'Move down'                              => __('Move down', lineconnect::PLUGIN_NAME),
				/** Move up button title, used by IconButton */
				'Move up'                                => __('Move up', lineconnect::PLUGIN_NAME),
				/** Remove button title, used by IconButton */
				'Remove'                                 => __('Remove', lineconnect::PLUGIN_NAME),
				/** Now label, used by AltDateWidget */
				'Now'                                    => __('Now', lineconnect::PLUGIN_NAME),
				/** Clear label, used by AltDateWidget */
				'Clear'                                  => __('Clear', lineconnect::PLUGIN_NAME),
				/** Aria date label, used by DateWidget */
				'Select a date'                          => __('Select a date', lineconnect::PLUGIN_NAME),
				/** File preview label, used by FileWidget */
				'Preview'                                => __('Preview', lineconnect::PLUGIN_NAME),
				/** Decrement button aria label, used by UpDownWidget */
				'Decrease value by 1'                    => __('Decrease value by 1', lineconnect::PLUGIN_NAME),
				/** Increment button aria label, used by UpDownWidget */
				'Increase value by 1'                    => __('Increase value by 1', lineconnect::PLUGIN_NAME),
				// Strings with replaceable parameters
				/** Unknown field type reason, where %1 will be replaced with the type as provided by SchemaField */
				'Unknown field type %1'                  => __('Unknown field type %1', lineconnect::PLUGIN_NAME),
				/** Option prefix, where %1 will be replaced with the option index as provided by MultiSchemaField */
				'Option %1'                              => __('Option %1', lineconnect::PLUGIN_NAME),
				/** Option prefix, where %1 and %2 will be replaced by the schema title and option index, respectively as provided by
				 * MultiSchemaField
				 */
				'%1 option %2'                           => __('%1 option %2', lineconnect::PLUGIN_NAME),
				/** Key label, where %1 will be replaced by the label as provided by WrapIfAdditionalTemplate */
				'%1 Key'                                 => __('%1 Key', lineconnect::PLUGIN_NAME),
				// Strings with replaceable parameters AND/OR that support markdown and html
				/** Invalid object field configuration as provided by the ObjectField */
				'Invalid %1 object field configuration: <em>%2</em>.' => __('Invalid %1 object field configuration: <em>%2</em>.', lineconnect::PLUGIN_NAME),
				/** Unsupported field schema, used by UnsupportedField */
				'Unsupported field schema.'              => __('Unsupported field schema.', lineconnect::PLUGIN_NAME),
				/** Unsupported field schema, where %1 will be replaced by the idSchema.$id as provided by UnsupportedField */
				'Unsupported field schema for field <code>%1</code>.' => __('Unsupported field schema for field <code>%1</code>.', lineconnect::PLUGIN_NAME),
				/** Unsupported field schema, where %1 will be replaced by the reason string as provided by UnsupportedField */
				'Unsupported field schema: <em>%1</em>.' => __('Unsupported field schema: <em>%1</em>.', lineconnect::PLUGIN_NAME),
				/** Unsupported field schema, where %1 and %2 will be replaced by the idSchema.$id and reason strings, respectively,
				 * as provided by UnsupportedField
				 */
				'Unsupported field schema for field <code>%1</code>: <em>%2</em>.' => __('Unsupported field schema for field <code>%1</code>: <em>%2</em>.', lineconnect::PLUGIN_NAME),
				/** File name, type and size info, where %1, %2 and %3 will be replaced by the file name, file type and file size as
				 * provided by FileWidget
				 */
				'<strong>%1</strong> (%2, %3 bytes)'     => __('<strong>%1</strong> (%2, %3 bytes)', lineconnect::PLUGIN_NAME),
			)
		);
	}

	/**
	 * Return channnel options
	 * @return array channel options
	 */
	public static function get_channel_options() {
		$channnel_option = self::$channnel_option;
		// insert each role's richmenu-id
		// 'linked-richmenu'      => __( 'Rich menu ID for linked users', lineconnect::PLUGIN_NAME ),
		// 'unlinked-richmenu'    => __( 'Rich menu ID for unlinked users', lineconnect::PLUGIN_NAME ),
		foreach (wp_roles()->roles as $role_name => $role) {
			$channnel_option[$role_name . '-richmenu'] = sprintf(__('Rich menu ID for %s.', lineconnect::PLUGIN_NAME), translate_user_role($role['name']));
		}
		return $channnel_option;
	}
}
