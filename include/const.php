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
	 * イベントタイプ
	 */
	const WH_EVENT_TYPE = array(
		1 => 'message',
		2 => 'unsend',
		3 => 'follow',
		4 => 'unfollow',
		5 => 'join',
		6 => 'leave',
		7 => 'memberJoined',
		8 => 'memberLeft',
		9 => 'postback',
		10 => 'videoPlayComplete',
		11 => 'beacon',
		12 => 'accountLink',
		13 => 'things',
	);

	/**
	 * ソースタイプ
	 */
	const WH_SOURCE_TYPE = array(
		1 => 'user',
		2 => 'group',
		3 => 'room',
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

	const ASSETS_SVG_FILENAME = 'assets/symbol-defs.svg';

	const MIME_MAP = array(
		'video/3gpp2'                                                               => '3g2',
		'video/3gp'                                                                 => '3gp',
		'video/3gpp'                                                                => '3gp',
		'application/x-compressed'                                                  => '7zip',
		'audio/x-acc'                                                               => 'aac',
		'audio/ac3'                                                                 => 'ac3',
		'application/postscript'                                                    => 'ai',
		'audio/x-aiff'                                                              => 'aif',
		'audio/aiff'                                                                => 'aif',
		'audio/x-au'                                                                => 'au',
		'video/x-msvideo'                                                           => 'avi',
		'video/msvideo'                                                             => 'avi',
		'video/avi'                                                                 => 'avi',
		'application/x-troff-msvideo'                                               => 'avi',
		'application/macbinary'                                                     => 'bin',
		'application/mac-binary'                                                    => 'bin',
		'application/x-binary'                                                      => 'bin',
		'application/x-macbinary'                                                   => 'bin',
		'image/bmp'                                                                 => 'bmp',
		'image/x-bmp'                                                               => 'bmp',
		'image/x-bitmap'                                                            => 'bmp',
		'image/x-xbitmap'                                                           => 'bmp',
		'image/x-win-bitmap'                                                        => 'bmp',
		'image/x-windows-bmp'                                                       => 'bmp',
		'image/ms-bmp'                                                              => 'bmp',
		'image/x-ms-bmp'                                                            => 'bmp',
		'application/bmp'                                                           => 'bmp',
		'application/x-bmp'                                                         => 'bmp',
		'application/x-win-bitmap'                                                  => 'bmp',
		'application/cdr'                                                           => 'cdr',
		'application/coreldraw'                                                     => 'cdr',
		'application/x-cdr'                                                         => 'cdr',
		'application/x-coreldraw'                                                   => 'cdr',
		'image/cdr'                                                                 => 'cdr',
		'image/x-cdr'                                                               => 'cdr',
		'zz-application/zz-winassoc-cdr'                                            => 'cdr',
		'application/mac-compactpro'                                                => 'cpt',
		'application/pkix-crl'                                                      => 'crl',
		'application/pkcs-crl'                                                      => 'crl',
		'application/x-x509-ca-cert'                                                => 'crt',
		'application/pkix-cert'                                                     => 'crt',
		'text/css'                                                                  => 'css',
		'text/x-comma-separated-values'                                             => 'csv',
		'text/comma-separated-values'                                               => 'csv',
		'application/vnd.msexcel'                                                   => 'csv',
		'application/x-director'                                                    => 'dcr',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
		'application/x-dvi'                                                         => 'dvi',
		'message/rfc822'                                                            => 'eml',
		'application/x-msdownload'                                                  => 'exe',
		'video/x-f4v'                                                               => 'f4v',
		'audio/x-flac'                                                              => 'flac',
		'video/x-flv'                                                               => 'flv',
		'image/gif'                                                                 => 'gif',
		'application/gpg-keys'                                                      => 'gpg',
		'application/x-gtar'                                                        => 'gtar',
		'application/x-gzip'                                                        => 'gzip',
		'application/mac-binhex40'                                                  => 'hqx',
		'application/mac-binhex'                                                    => 'hqx',
		'application/x-binhex40'                                                    => 'hqx',
		'application/x-mac-binhex40'                                                => 'hqx',
		'text/html'                                                                 => 'html',
		'image/x-icon'                                                              => 'ico',
		'image/x-ico'                                                               => 'ico',
		'image/vnd.microsoft.icon'                                                  => 'ico',
		'text/calendar'                                                             => 'ics',
		'application/java-archive'                                                  => 'jar',
		'application/x-java-application'                                            => 'jar',
		'application/x-jar'                                                         => 'jar',
		'image/jp2'                                                                 => 'jp2',
		'video/mj2'                                                                 => 'jp2',
		'image/jpx'                                                                 => 'jp2',
		'image/jpm'                                                                 => 'jp2',
		'image/jpeg'                                                                => 'jpeg',
		'image/pjpeg'                                                               => 'jpeg',
		'application/x-javascript'                                                  => 'js',
		'application/json'                                                          => 'json',
		'text/json'                                                                 => 'json',
		'application/vnd.google-earth.kml+xml'                                      => 'kml',
		'application/vnd.google-earth.kmz'                                          => 'kmz',
		'text/x-log'                                                                => 'log',
		'audio/x-m4a'                                                               => 'm4a',
		'audio/mp4'                                                                 => 'm4a',
		'application/vnd.mpegurl'                                                   => 'm4u',
		'audio/midi'                                                                => 'mid',
		'application/vnd.mif'                                                       => 'mif',
		'video/quicktime'                                                           => 'mov',
		'video/x-sgi-movie'                                                         => 'movie',
		'audio/mpeg'                                                                => 'mp3',
		'audio/mpg'                                                                 => 'mp3',
		'audio/mpeg3'                                                               => 'mp3',
		'audio/mp3'                                                                 => 'mp3',
		'video/mp4'                                                                 => 'mp4',
		'video/mpeg'                                                                => 'mpeg',
		'application/oda'                                                           => 'oda',
		'audio/ogg'                                                                 => 'ogg',
		'video/ogg'                                                                 => 'ogg',
		'application/ogg'                                                           => 'ogg',
		'font/otf'                                                                  => 'otf',
		'application/x-pkcs10'                                                      => 'p10',
		'application/pkcs10'                                                        => 'p10',
		'application/x-pkcs12'                                                      => 'p12',
		'application/x-pkcs7-signature'                                             => 'p7a',
		'application/pkcs7-mime'                                                    => 'p7c',
		'application/x-pkcs7-mime'                                                  => 'p7c',
		'application/x-pkcs7-certreqresp'                                           => 'p7r',
		'application/pkcs7-signature'                                               => 'p7s',
		'application/pdf'                                                           => 'pdf',
		'application/octet-stream'                                                  => 'pdf',
		'application/x-x509-user-cert'                                              => 'pem',
		'application/x-pem-file'                                                    => 'pem',
		'application/pgp'                                                           => 'pgp',
		'application/x-httpd-php'                                                   => 'php',
		'application/php'                                                           => 'php',
		'application/x-php'                                                         => 'php',
		'text/php'                                                                  => 'php',
		'text/x-php'                                                                => 'php',
		'application/x-httpd-php-source'                                            => 'php',
		'image/png'                                                                 => 'png',
		'image/x-png'                                                               => 'png',
		'application/powerpoint'                                                    => 'ppt',
		'application/vnd.ms-powerpoint'                                             => 'ppt',
		'application/vnd.ms-office'                                                 => 'ppt',
		'application/msword'                                                        => 'doc',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
		'application/x-photoshop'                                                   => 'psd',
		'image/vnd.adobe.photoshop'                                                 => 'psd',
		'audio/x-realaudio'                                                         => 'ra',
		'audio/x-pn-realaudio'                                                      => 'ram',
		'application/x-rar'                                                         => 'rar',
		'application/rar'                                                           => 'rar',
		'application/x-rar-compressed'                                              => 'rar',
		'audio/x-pn-realaudio-plugin'                                               => 'rpm',
		'application/x-pkcs7'                                                       => 'rsa',
		'text/rtf'                                                                  => 'rtf',
		'text/richtext'                                                             => 'rtx',
		'video/vnd.rn-realvideo'                                                    => 'rv',
		'application/x-stuffit'                                                     => 'sit',
		'application/smil'                                                          => 'smil',
		'text/srt'                                                                  => 'srt',
		'image/svg+xml'                                                             => 'svg',
		'application/x-shockwave-flash'                                             => 'swf',
		'application/x-tar'                                                         => 'tar',
		'application/x-gzip-compressed'                                             => 'tgz',
		'image/tiff'                                                                => 'tiff',
		'font/ttf'                                                                  => 'ttf',
		'text/plain'                                                                => 'txt',
		'text/x-vcard'                                                              => 'vcf',
		'application/videolan'                                                      => 'vlc',
		'text/vtt'                                                                  => 'vtt',
		'audio/x-wav'                                                               => 'wav',
		'audio/wave'                                                                => 'wav',
		'audio/wav'                                                                 => 'wav',
		'application/wbxml'                                                         => 'wbxml',
		'video/webm'                                                                => 'webm',
		'image/webp'                                                                => 'webp',
		'audio/x-ms-wma'                                                            => 'wma',
		'application/wmlc'                                                          => 'wmlc',
		'video/x-ms-wmv'                                                            => 'wmv',
		'video/x-ms-asf'                                                            => 'wmv',
		'font/woff'                                                                 => 'woff',
		'font/woff2'                                                                => 'woff2',
		'application/xhtml+xml'                                                     => 'xhtml',
		'application/excel'                                                         => 'xl',
		'application/msexcel'                                                       => 'xls',
		'application/x-msexcel'                                                     => 'xls',
		'application/x-ms-excel'                                                    => 'xls',
		'application/x-excel'                                                       => 'xls',
		'application/x-dos_ms_excel'                                                => 'xls',
		'application/xls'                                                           => 'xls',
		'application/x-xls'                                                         => 'xls',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
		'application/vnd.ms-excel'                                                  => 'xlsx',
		'application/xml'                                                           => 'xml',
		'text/xml'                                                                  => 'xml',
		'text/xsl'                                                                  => 'xsl',
		'application/xspf+xml'                                                      => 'xspf',
		'application/x-compress'                                                    => 'z',
		'application/x-zip'                                                         => 'zip',
		'application/zip'                                                           => 'zip',
		'application/x-zip-compressed'                                              => 'zip',
		'application/s-compressed'                                                  => 'zip',
		'multipart/x-zip'                                                           => 'zip',
		'text/x-scriptzsh'                                                          => 'zsh',
	);

	/**
	 * Function Callingで呼び出せる関数
	 */
	public static array $callable_functions;


	public static function initialize() {
		self::$channnel_field = array(
			'send-checkbox' => __('Send update notification', lineconnect::PLUGIN_NAME),
			'role-selectbox' => __('Send target:', lineconnect::PLUGIN_NAME),
			'future-checkbox' => __('Send when a future post is published', lineconnect::PLUGIN_NAME),
		);

		self::$channnel_option = array(
			'name' => __('Channel name', lineconnect::PLUGIN_NAME),
			'channel-access-token' => __('Channel access token', lineconnect::PLUGIN_NAME),
			'channel-secret' => __('Channel secret', lineconnect::PLUGIN_NAME),
			'role' => __('Default target role', lineconnect::PLUGIN_NAME),
			'linked-richmenu' => __('Rich menu ID for linked users', lineconnect::PLUGIN_NAME),
			'unlinked-richmenu' => __('Rich menu ID for unlinked users', lineconnect::PLUGIN_NAME),
		);

		self::$settings_option = array(
			'channel' => array(
				'prefix' => '1',
				'name' => __('Channel', lineconnect::PLUGIN_NAME),
				'fields' => array(),
			),
			'connect' => array(
				'prefix' => '2',
				'name' => __('Link', lineconnect::PLUGIN_NAME),
				'fields' => array(
					'login_page_url' => array(
						'type' => 'text',
						'label' => __('Login page URL', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => 'wp-login.php',
						'hint' => __('Enter the URL of the login page as a path relative to the site URL.', lineconnect::PLUGIN_NAME)
					),
					'link_start_keyword' => array(
						'type' => 'text',
						'label' => __('Account link/unlink start keywords', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => __('Account link', lineconnect::PLUGIN_NAME),
					),
					'link_start_title' => array(
						'type' => 'text',
						'label' => __('Message title for account linkage initiation', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => __('Start account linkage', lineconnect::PLUGIN_NAME),
					),
					'link_start_body' => array(
						'type' => 'text',
						'label' => __('Message body for account linkage initiation', lineconnect::PLUGIN_NAME),
						'required' => true,
						'size' => 60,
						'default' => __('Start the linkage. Please login at the link.', lineconnect::PLUGIN_NAME),
					),
					'link_start_button' => array(
						'type' => 'text',
						'label' => __('Message button label to start account linkage', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => __('Start linkage', lineconnect::PLUGIN_NAME),
					),
					'link_finish_body' => array(
						'type' => 'text',
						'label' => __('Account Linkage Completion Message', lineconnect::PLUGIN_NAME),
						'required' => true,
						'size' => 60,
						'default' => __('Account linkage completed.', lineconnect::PLUGIN_NAME),
					),
					'link_failed_body' => array(
						'type' => 'text',
						'label' => __('Account Linkage Failure Messages', lineconnect::PLUGIN_NAME),
						'required' => true,
						'size' => 60,
						'default' => __('Account linkage failed.', lineconnect::PLUGIN_NAME),
					),
					'unlink_start_title' => array(
						'type' => 'text',
						'label' => __('Message title for account unlinking initiation', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => __('Unlink account', lineconnect::PLUGIN_NAME),
					),
					'unlink_start_body' => array(
						'type' => 'text',
						'label' => __('Message body for account unlinking initiation', lineconnect::PLUGIN_NAME),
						'required' => true,
						'size' => 60,
						'default' => __('Would you like to unlink your account?', lineconnect::PLUGIN_NAME),
					),
					'unlink_start_button' => array(
						'type' => 'text',
						'label' => __('Message button label to start account unlinking', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => __('Unlink account', lineconnect::PLUGIN_NAME),
					),
					'unlink_finish_body' => array(
						'type' => 'text',
						'label' => __('Account Unlinking Completion Message', lineconnect::PLUGIN_NAME),
						'required' => true,
						'size' => 60,
						'default' => __('Account linkage has been successfully unlinked.', lineconnect::PLUGIN_NAME),
					),
					'unlink_failed_body' => array(
						'type' => 'text',
						'label' => __('Account Unlinking Failure Message', lineconnect::PLUGIN_NAME),
						'required' => true,
						'size' => 60,
						'default' => __('Failed to unlink the account.', lineconnect::PLUGIN_NAME),
					),
				),
			),
			'publish' => array(
				'prefix' => '3',
				'name' => __('Update Notification', lineconnect::PLUGIN_NAME),
				'fields' => array(
					'send_post_types' => array(
						'type' => 'multiselect',
						'label' => __('Post types', lineconnect::PLUGIN_NAME),
						'required' => false,
						'list' => array('post' => __('Post', lineconnect::PLUGIN_NAME), 'page' => __('Page', lineconnect::PLUGIN_NAME)),
						'default' => array('post'),
						'isMulti' => true,
						'hint' => __('The post type to be notified. The Send LINE checkbox will appear on the edit screen of the selected post type.', lineconnect::PLUGIN_NAME)
					),
					'default_send_checkbox' => array(
						'type' => 'select',
						'label' => __('Default value of "Send update notification" checkbox', lineconnect::PLUGIN_NAME),
						'required' => true,
						'list' => array('on' => __('Checked', lineconnect::PLUGIN_NAME), 'off' => __('Unchecked', lineconnect::PLUGIN_NAME), 'new' => __('Unchecked if published', lineconnect::PLUGIN_NAME)),
						'default' => 'new',
						'hint' => __('Default value setting for the "Send update notification" check box when editing an article.', lineconnect::PLUGIN_NAME),
					),
					'more_label' => array(
						'type' => 'text',
						'label' => __('"More" link label', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => __('Read more', lineconnect::PLUGIN_NAME),
					),
					'send_new_comment' => array(
						'type' => 'checkbox',
						'label' => __('Send notification to posters when comments are received', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => false,
						'hint' => __('This setting determines whether or not to notify the poster of an article when there is a comment on the article.', lineconnect::PLUGIN_NAME),
					),
					'comment_read_label' => array(
						'type' => 'text',
						'label' => __('"Read comment" link label', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => __('Read comment', lineconnect::PLUGIN_NAME),
					),
				),
			),
			'style' => array(
				'prefix' => '4',
				'name' => __('Style', lineconnect::PLUGIN_NAME),
				'fields' => array(
					'image_aspectmode' => array(
						'type' => 'select',
						'label' => __('Image fit mode', lineconnect::PLUGIN_NAME),
						'required' => true,
						'list' => array('cover' => __('cover', lineconnect::PLUGIN_NAME), 'fit' => __('contain', lineconnect::PLUGIN_NAME)),
						'default' => 'cover',
						'hint' => __('cover: The replaced content is sized to maintain its aspect ratio while filling the image area. If the image\'s aspect ratio does not match the aspect ratio of its area, then the image will be clipped to fit. \n contain: The replaced image is scaled to maintain its aspect ratio while fitting within the image area. The entire image is made to fill the box, while preserving its aspect ratio, so the image will be "letterboxed" if its aspect ratio does not match the aspect ratio of the area.', lineconnect::PLUGIN_NAME),
					),
					'image_aspectrate' => array(
						'type' => 'text',
						'label' => __('Image area aspect ratio', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => '2:1',
						'regex' => '/^[1-9]+[0-9]*:[1-9]+[0-9]*$/',
						'hint' => __('The aspect ratio of the image area. The height cannot be greater than three times the width.', lineconnect::PLUGIN_NAME),
					),
					'title_backgraound_color' => array(
						'type' => 'color',
						'label' => __('Background color of the message', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => '#FFFFFF',
						'hint' => __('The background color of the notification message.', lineconnect::PLUGIN_NAME),
					),
					'title_text_color' => array(
						'type' => 'color',
						'label' => __('Title text color', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => '#000000',
						'hint' => __('The title text color of the notification message.', lineconnect::PLUGIN_NAME),
					),
					'body_text_color' => array(
						'type' => 'color',
						'label' => __('Body text color', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => '#000000',
						'hint' => __('The body text color of the notification message.', lineconnect::PLUGIN_NAME),
					),
					'link_text_color' => array(
						'type' => 'color',
						'label' => __('Link text color', lineconnect::PLUGIN_NAME),
						'required' => true,
						'default' => '#1e90ff',
						'hint' => __('The link text color of the notification message.', lineconnect::PLUGIN_NAME),
					),
					'title_rows' => array(
						'type' => 'spinner',
						'label' => __('Max title lines', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => 3,
						'hint' => __('This is the setting for the maximum number of lines of title to be displayed in the notification message.', lineconnect::PLUGIN_NAME),
					),
					'body_rows' => array(
						'type' => 'spinner',
						'label' => __('Max body lines', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => 5,
						'hint' => __('This is the setting for the maximum number of lines of text to be displayed in the notification message. Apart from this, it can be truncated to a maximum of 500 characters.', lineconnect::PLUGIN_NAME),
					),
				),
			),
			'chat' => array(
				'prefix' => '5',
				'name' => __('AI Chat', lineconnect::PLUGIN_NAME),
				'fields' => array(
					'enableChatbot' => array(
						'type' => 'select',
						'label' => __('Auto response by AI', lineconnect::PLUGIN_NAME),
						'required' => true,
						'list' => array('off' => __('Disabled', lineconnect::PLUGIN_NAME), 'on' => __('Enabled', lineconnect::PLUGIN_NAME)),
						'default' => 'off',
						'hint' => __('This setting determines whether or not to use AI auto-response for messages sent to official line account.', lineconnect::PLUGIN_NAME),
					),
					'openai_secret' => array(
						'type' => 'text',
						'label' => __('OpenAI API Key', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => '',
						'size' => 60,
						'hint' => __('Enter your OpenAI API Key.', lineconnect::PLUGIN_NAME),
					),
					'openai_model' => array(
						'type' => 'select',
						'label' => __('Model', lineconnect::PLUGIN_NAME),
						'required' => false,
						'list' => array('gpt-3.5-turbo' => 'GPT-3.5 turbo', 'gpt-3.5-turbo-16k' => 'GPT-3.5 turbo 16k', 'gpt-4' => 'GPT-4', 'gpt-4-32k' => 'GPT-4 32k',),
						'default' => 'gpt-3.5-turbo',
						'hint' => __('This is a setting for which model to use.', lineconnect::PLUGIN_NAME),
					),
					'openai_system' => array(
						'type' => 'textarea',
						'label' => __('System prompt', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => '',
						'rows' => 7,
						'cols' => 80,
						'hint' => __('The initial text or instruction provided to the language model before interacting with it in a conversational manner.', lineconnect::PLUGIN_NAME),
					),
					'openai_function_call' => array(
						'type' => 'select',
						'label' => __('Function Calling', lineconnect::PLUGIN_NAME),
						'required' => true,
						'list' => array('off' => __('Disabled', lineconnect::PLUGIN_NAME), 'on' => __('Enabled', lineconnect::PLUGIN_NAME)),
						'default' => 'off',
						'hint' => __('This setting determines whether Function Calling is used or not.', lineconnect::PLUGIN_NAME),
					),
					'openai_enabled_functions' => array(
						'type' => 'multiselect',
						'label' => __('Functions to use', lineconnect::PLUGIN_NAME),
						'required' => false,
						'list' => array(),
						'default' => array(),
						'isMulti' => true,
						'hint' => __('Function to be enabled by Function Calling.', lineconnect::PLUGIN_NAME)
					),
					'openai_context' => array(
						'type' => 'spinner',
						'label' => __('Number of context', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => 3,
						'regex' => '/^\d+$/',
						'hint' => __('This is a setting for how many conversation histories to use in order to have the AI understand the context and respond.', lineconnect::PLUGIN_NAME),
					),
					'openai_max_tokens' => array(
						'type' => 'spinner',
						'label' => __('Max tokens', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => -1,
						'regex' => '/^[+-]?\d+$/',
						'hint' => __('Maximum number of tokens to use. -1 is the upper limit of the model.', lineconnect::PLUGIN_NAME),
					),
					'openai_temperature' => array(
						'type' => 'range',
						'label' => __('Temperature', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => 1,
						'min' => 0,
						'max' => 1,
						'step' => 0.1,
						'hint' => __('This is the temperature parameter. The higher the value, the more diverse words are likely to be selected. Between 0 and 1.', lineconnect::PLUGIN_NAME),
					),
					'openai_limit_normal' => array(
						'type' => 'spinner',
						'label' => __('Limit for unlinked users', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => 3,
						'regex' => '/^[+-]?\d+$/',
						'hint' => __('Number of times an unlinked user can use it per day. -1 is unlimited.', lineconnect::PLUGIN_NAME),
					),
					'openai_limit_linked' => array(
						'type' => 'spinner',
						'label' => __('Limit for linked users', lineconnect::PLUGIN_NAME),
						'required' => false,
						'default' => 5,
						'regex' => '/^[+-]?\d+$/',
						'hint' => __('Number of times an linked user can use it per day. -1 is unlimited.', lineconnect::PLUGIN_NAME),
					),
					'openai_limit_message' => array(
						'type' => 'textarea',
						'label' => __('Limit message', lineconnect::PLUGIN_NAME),
						'required' => false,
						'rows' => 5,
						'cols' => 60,
						'default' => __('The number of times you can use it in a day (%limit% times) has been exceeded. Please try again after the date changes.', lineconnect::PLUGIN_NAME),
						'hint' => __('This message is displayed when the number of times the limit can be used in a day is exceeded. The %limit% is replaced by the limit number of times.', lineconnect::PLUGIN_NAME),
					),
				),
			),
		);

		self::$callable_functions = [
			"get_my_user_info" => [
				"title" => __('Get my user information', lineconnect::PLUGIN_NAME),
				"description" => "Get my information. ID, name, email, link status, etc.",
				"parameters" => [
					"type" => "object",
					"properties" => [
						"dummy_property" => [
							"type" => "null",
						]
					],
					"required" => []
				],
				"namespace" => "lineconnectFunctions",
				"role" => "read",
			],
			"get_the_current_datetime" => [
				"title" => __('Get the current date and time', lineconnect::PLUGIN_NAME),
				"description" => "Get the current date and time.",
				"parameters" => [
					"type" => "object",
					"properties" => [
						"dummy_property" => [
							"type" => "null",
						]
					],
					"required" => []
				],
				"namespace" => "lineconnectFunctions",
				"role" => "any",
			],
			"WP_Query" => [
				"title" => __('Search posts', lineconnect::PLUGIN_NAME),
				"description" => "Get posts with WP_Query. ID, type, title, date, excerpt or content, permalink",
				"parameters" => [
					"type" => "object",
					"properties" => [
						"author_name" => array(
							"type" => "string",
							"description" => "Author's user_nicename. NOT display_name nor user_login."
						),
						"s" => [
							"type" => "string",
							"description" => "Search keyword."
						],
						"p" => [
							"type" => "integer",
							"description" => "Use post ID",
						],
						"name" => [
							"type" => "string",
							"description" => "Use post slug",
						],
						"order" => [
							"type" => "string",
							"enum" => ["ASC", "DESC"],
							"default" => "DESC",
						],
						"orderby" => [
							"type" => "string",
							"description" => "Sort retrieved posts by parameter.",
							"default" => "date",
						],

						"offset" => [
							"type" => "integer",
							"description" => "number of post to displace or pass over.",
						],
						"year" => [
							"type" => "integer",
							"description" => "4 digit year",
						],
						"monthnum" => [
							"type" => "integer",
							"description" => "Month number (from 1 to 12).",
						],
						"day" => [
							"type" => "integer",
							"description" => "Day of the month (from 1 to 31).",
						],
					],
					"required" => []
				],
				"namespace" => "lineconnectFunctions",
				"role" => "any",
			],
		];
	}
}
