<?php

namespace Shipweb\LineConnect\PostType\Message;

use Shipweb\LineConnect\Core\LineConnect;

class Schema {
	static function get_message_schema() {
		return array(
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
				'action' => self::get_action_object_schema(),
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
	}

	static function get_message_type_schema() {
		return array(
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
	}
	// Message type UI schema
	static function get_message_type_uischema() {
		return  array(
			'ui:submitButtonOptions' => array(
				'norender' => true,
			),
			'type' => array(
				'ui:description' => __('Choose message type.', lineconnect::PLUGIN_NAME),
			),
		);
	}

	// Message type schema
	static function get_message_type_items() {
		return array(
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
	}

	static function get_message_uischema() {
		return array(
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
	}

	public static function get_action_object_schema() {
		return array(
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
	}
}
