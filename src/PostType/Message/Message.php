<?php

/**
 * Lineconnect SLC Message Class
 *
 * LINE Connect SLC Message
 *
 * @category Components
 * @package  SLC Message
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

namespace Shipweb\LineConnect\PostType\Message;

use Shipweb\LineConnect\Components\ReactJsonSchemaForm;
use lineconnect;
use lineconnectConst;
use lineconnectUtil;
use Shipweb\LineConnect\Message\LINE\Builder;

class Message {
    const NAME = 'message';
    const CREDENTIAL_ACTION = LineConnect::PLUGIN_ID . '-nonce-action_' . self::NAME;
    const CREDENTIAL_NAME = LineConnect::PLUGIN_ID . '-nonce-name_' . self::NAME;
    const META_KEY_DATA = self::NAME . '-data';
    const PARAMETER_DATA = LineConnect::PLUGIN_PREFIX . self::META_KEY_DATA;
    const SCHEMA_VERSION = 1;
    const POST_TYPE = LineConnect::PLUGIN_PREFIX . self::NAME;
	
	/**
	 * メッセージのJSONスキーマを返す
	 */
	public static function get_message_schema() {
		$message_schema = array();
		foreach (Schema::get_message_type_items() as $type => $schema) {
			$message_schema[$type] = apply_filters(lineconnect::FILTER_PREFIX . 'lineconnect_message_schema', Schema::get_message_schema());
			$message_schema[$type]['properties']['message'] = $schema; //['properties']['messages']['items']
		}
		return $message_schema;
	}

	/**
	 * Return type data
	 */
	public static function get_form_type_data($formData, $schema_version) {
		if (empty($schema_version) || $schema_version == self::SCHEMA_VERSION) {
			return !empty($formData) ? $formData : new \stdClass();
		}
		// if old schema veersion, migrate and return
	}

	/** 
	 * Return message data
	 */
	public static function get_form_message_data($formData, $schema_version) {
		if (empty($schema_version) || $schema_version == self::SCHEMA_VERSION) {
			return !empty($formData) ? $formData : new \stdClass();
		}
		// if old schema veersion, migrate and return
	}

	/**
	 * Return message array object post_id and title
	 */
	public static function get_lineconnect_message_name_array() {
		$args          = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);
		$posts         = get_posts($args);
		$message_array = array();
		foreach ($posts as $post) {
			$message_array[$post->ID] = $post->post_title;
		}
		return $message_array;
	}

	/**
	 * Return LINE message object by post_id
	 */
	public static function get_lineconnect_message($post_id, $args = null) {
		$formData        = get_post_meta($post_id, self::META_KEY_DATA, true);
		if (empty($formData) || $formData === false) {
			return null;
		}
		return self::formData_to_multimessage($formData, $args);
	}

	public static function formData_to_multimessage($formData, $args = null) {
		$multimessagebuilder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
		$message_objects     = array();
		$message_data = array();
		if (! empty($formData)) {
			for ($i = 0; $i < 10; $i += 2) {

				if (! empty($formData[$i + 1])) {
					$message = array(
						'type' => $formData[$i]['type'],
						'message' => $formData[$i + 1],
					);
					$message_data[] = $message;
				}
			}
		}
		foreach ($message_data as $message_item) {
			$message_type = $message_item['type'];
			$message = \Shipweb\LineConnect\Utilities\PlaceholderReplacer::replace_object_placeholder($message_item['message'], $args);
			$message_object = $quickReply = $sender = null;
			if (! empty($message['quickReply'])) {
				$quickReplay_items = array();
				foreach ($message['quickReply']['items'] as $quickReplay_container) {
					$templateAction = self::buildTemplateActionBuilder($quickReplay_container['action']);
					if (! empty($templateAction)) {
						$quickReplay_button  = Builder::createQuickReplayButtonBuilder($templateAction, $quickReplay_container['imageUrl'] ?? null);
						$quickReplay_items[] = $quickReplay_button;
					}
				}
				if (! empty($quickReplay_items)) {
					$quickReply = Builder::createQuickReplyMessageBuilder($quickReplay_items);
				}
			}
			if (! empty($message['sender'])) {
				$sender = Builder::createSenderMessageBuilder(isset($message['sender']['name']) ? $message['sender']['name'] : null, isset($message['sender']['iconUrl']) ? $message['sender']['iconUrl'] : null);
			}

			if ('text' === $message_type) {
				$message_object = Builder::createTextMessage($message['message']['text']['text'], $quickReply, $sender);
			} elseif ('sticker' === $message_type) {
				$message_object = Builder::createStickerMessage($message['message']['sticker']['packageId'], $message['message']['sticker']['stickerId'], $quickReply, $sender);
			} elseif ('image' === $message_type) {
				$message_object = Builder::createImageMessage($message['message']['image']['originalContentUrl'], $message['message']['image']['previewImageUrl'], $quickReply, $sender);
			} elseif ('video' === $message_type) {
				$message_object = Builder::createVideoMessage($message['message']['video']['originalContentUrl'], $message['message']['video']['previewImageUrl'], $message['message']['video']['trackingId'], $quickReply, $sender);
			} elseif ('audio' === $message_type) {
				$message_object = Builder::createAudioMessage($message['message']['audio']['originalContentUrl'], $message['message']['audio']['duration'], $quickReply, $sender);
			} elseif ('location' === $message_type) {
				$message_object = Builder::createLocationMessage($message['message']['location']['title'], $message['message']['location']['address'], $message['message']['location']['latitude'], $message['message']['location']['longitude'], $quickReply, $sender);
			} elseif ('imagemap' === $message_type) {
				$message_object = self::buildImagemapMessage($message, $quickReply, $sender);
			} elseif ('button_template' === $message_type) {
				$message_object = self::buildButtonTemplateMessage($message, $quickReply, $sender);
			} elseif ('confirm_template' === $message_type) {
				$message_object = self::buildConfirmTemplateMessage($message, $quickReply, $sender);
			} elseif ('carousel_template' === $message_type) {
				$message_object = self::buildCarouselTemplateBuilder($message, $quickReply, $sender);
			} elseif ('image_carousel_template' === $message_type) {
				$message_object = self::buildImageCarouselTemplateBuilder($message, $quickReply, $sender);
			} elseif ('flex' === $message_type) {
				$message_object = Builder::createFlexRawMessage($message['message']['flex']['raw'], $message['message']['flex']['alttext'] ?? 'Flex message', $quickReply, $sender);
			} elseif ('raw' === $message_type) {
				$message_object = Builder::createRawMessage($message['message']['raw']['raw'], $quickReply, $sender);
			} else {
				$message_object = null;
			}
			if ($message_object) {
				$message_objects[] = $message_object;
			}
		}
		foreach ($message_objects as $message_object) {
			$multimessagebuilder->add($message_object);
		}
		return $multimessagebuilder;
	}

	private static function buildImagemapMessage($message, $quickReply, $sender) {
		require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
		$video = null;
		$actions = [];
		if (!empty($message['message']['imagemap']['video']['originalContentUrl'])) {
			$area = new \LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(
				$message['message']['imagemap']['video']['area']['x'],
				$message['message']['imagemap']['video']['area']['y'],
				$message['message']['imagemap']['video']['area']['width'],
				$message['message']['imagemap']['video']['area']['height']
			);
			$externalLink = new \LINE\LINEBot\MessageBuilder\Imagemap\ExternalLinkBuilder(
				$message['message']['imagemap']['video']['externalLink']['linkUri'],
				$message['message']['imagemap']['video']['externalLink']['label']
			);
			$video = new \LINE\LINEBot\MessageBuilder\Imagemap\VideoBuilder(
				$message['message']['imagemap']['video']['originalContentUrl'],
				$message['message']['imagemap']['video']['previewImageUrl'],
				$area,
				$externalLink
			);
		}
		$baseSize = new \LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder(
			$message['message']['imagemap']['baseSize']['height'],
			$message['message']['imagemap']['baseSize']['width']
		);
		if (!empty($message['message']['imagemap']['actions'])) {
			foreach ($message['message']['imagemap']['actions'] as $action) {
				$ImagemapActionBuilder = null;
				$area = new \LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(
					$action['area']['x'],
					$action['area']['y'],
					$action['area']['width'],
					$action['area']['height']
				);
				if ($action['type'] === 'message') {
					$ImagemapActionBuilder = new \LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder(
						$action['text'],
						$area
					);
				} elseif ($action['type'] === 'uri') {
					$ImagemapActionBuilder = new \LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder(
						$action['linkUri'],
						$area
					);
				}
				if ($ImagemapActionBuilder) {
					$actions[] = $ImagemapActionBuilder;
				}
			}
		}
		return Builder::createImageMapMessage(
			$message['message']['imagemap']['baseUrl'],
			$message['message']['imagemap']['altText'],
			$baseSize,
			$actions,
			$quickReply,
			$video,
			$sender
		);
	}

	private static function buildButtonTemplateMessage($message, $quickReply, $sender) {
		$actionBuilders = self::builderActions($message['message']['button_template']['actions']);
		$defaultAction = self::buildTemplateActionBuilder($message['message']['button_template']['defaultAction']);
		$buttonTemplate = Builder::createButtonTemplateBuilder(
			isset($message['message']['button_template']['title']) ? $message['message']['button_template']['title'] : null,
			$message['message']['button_template']['text'],
			isset($message['message']['button_template']['thumbnailImageUrl']) ? $message['message']['button_template']['thumbnailImageUrl'] : null,
			$actionBuilders,
			isset($message['message']['button_template']['imageAspectRatio']) ? $message['message']['button_template']['imageAspectRatio'] : null,
			isset($message['message']['button_template']['imageSize']) ? $message['message']['button_template']['imageSize'] : null,
			isset($message['message']['button_template']['imageBackgroundColor']) ? $message['message']['button_template']['imageBackgroundColor'] : null,
			$defaultAction
		);
		return Builder::createTemplateMessageBuilder(
			$message['message']['altText'],
			$buttonTemplate,
			$quickReply,
			$sender
		);
	}

	private static function buildConfirmTemplateMessage($message, $quickReply, $sender) {
		$actionBuilders = self::builderActions($message['message']['confirm_template']['actions']);
		$confirmTemplate = Builder::createConfirmTemplateBuilder(
			$message['message']['confirm_template']['text'],
			$actionBuilders
		);
		return Builder::createTemplateMessageBuilder(
			$message['message']['altText'],
			$confirmTemplate,
			$quickReply,
			$sender
		);
	}

	private static function buildCarouselTemplateBuilder($message, $quickReply, $sender) {
		$columnTemplateBuilders = [];
		if (!empty($message['message']['carousel_template']['columns'])) {
			foreach ($message['message']['carousel_template']['columns'] as $column) {
				$actionBuilders = self::builderActions($column['actions']);
				$defaultAction = self::buildTemplateActionBuilder($column['defaultAction'] ?? null);
				$columnTemplateBuilders[] = Builder::createCarouselColumnTemplateBuilder(
					$column['title'] ?? null,
					$column['text'],
					$column['thumbnailImageUrl'] ?? null,
					$actionBuilders,
					$column['imageBackgroundColor'] ?? null,
					$defaultAction
				);
			}
		}
		$carouselTemplate = Builder::createCarouselTemplateBuilder(
			$columnTemplateBuilders,
			$message['message']['carousel_template']['imageAspectRatio'] ?? null,
			$message['message']['carousel_template']['imageSize'] ?? null
		);
		return Builder::createTemplateMessageBuilder(
			$message['message']['altText'],
			$carouselTemplate,
			$quickReply,
			$sender
		);
	}

	private static function buildImageCarouselTemplateBuilder($message, $quickReply, $sender) {
		$columnTemplateBuilders = [];
		if (!empty($message['message']['image_carousel_template']['columns'])) {
			foreach ($message['message']['image_carousel_template']['columns'] as $column) {
				$actionBuilder = self::buildTemplateActionBuilder($column['action']);
				$columnTemplateBuilders[] = Builder::createImageCarouselColumnTemplateBuilder(
					$column['imageUrl'],
					$actionBuilder,
				);
			}
		}
		$imageCarouselTemplate = Builder::createImageCarouselTemplateBuilder(
			$columnTemplateBuilders
		);
		return Builder::createTemplateMessageBuilder(
			$message['message']['altText'],
			$imageCarouselTemplate,
			$quickReply,
			$sender
		);
	}

	private static function builderActions($actions) {
		$actionBuilders = [];
		if (!empty($actions)) {
			foreach ($actions as $action) {
				$templateAction = self::buildTemplateActionBuilder($action);
				if (!empty($templateAction)) {
					$actionBuilders[] = $templateAction;
				}
			}
		}
		return $actionBuilders;
	}

	static function buildTemplateActionBuilder($action) {
		$templateAction = null;
		if (!empty($action['message'])) {
			$templateAction = Builder::createMessageTemplateActionBuilder($action['message']['label'] ?? null, $action['message']['text']);
		} elseif (!empty($action['postback'])) {
			$templateAction = Builder::createPostbackAction($action['postback']['label'] ?? null, $action['postback']['data'], $action['postback']['displayText'] ?? null, $action['postback']['inputOption'] ?? null, $action['postback']['fillInText'] ?? null);
		} elseif (!empty($action['uri'])) {
			$templateAction = Builder::createUriTemplateActionBuilder($action['uri']['label'] ?? null, $action['uri']['uri']);
		} elseif (!empty($action['datetimepicker'])) {
			$templateAction = Builder::createDatetimePickerTemplateActionBuilder($action['datetimepicker']['label'] ?? null, $action['datetimepicker']['data'], $action['datetimepicker']['mode'], $action['datetimepicker']['initial'], $action['datetimepicker']['max'], $action['datetimepicker']['min']);
		} elseif (!empty($action['cameraRoll'])) {
			$templateAction = Builder::createCameraRollTemplateActionBuilder($action['cameraRoll']['label']);
		} elseif (!empty($action['camera'])) {
			$templateAction = Builder::createCameraTemplateActionBuilder($action['camera']['label']);
		} elseif (!empty($action['location'])) {
			$templateAction = Builder::createLocationTemplateActionBuilder($action['location']['label']);
		} elseif (!empty($action['richmenuswitch'])) {
			$templateAction = Builder::createRichMenuSwitchTemplateActionBuilder($action['richmenuswitch']['richMenuAliasId'], $action['richmenuswitch']['data'], $action['richmenuswitch']['label'] ?? null);
		} elseif (!empty($action['clipboard'])) {
			$templateAction = Builder::createClipboardTemplateActionBuilder($action['clipboard']['label'] ?? null, $action['clipboard']['clipboardText']);
		}
		return $templateAction;
	}
}
