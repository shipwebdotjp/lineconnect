<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Message\LINE\Builder as LineMessageBuilder;
use Shipweb\LineConnect\Interaction\StepDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Builds messages for interactions.
 */
class MessageBuilder {
    /**
     * Build a message for a given step.
     *
     * @param StepDefinition $step The step definition.
     * @param array $validationErrors Optional validation errors.
     * @return \LINE\LINEBot\MessageBuilder
     */
    public function build(StepDefinition $step, array $validationErrors = []): \LINE\LINEBot\MessageBuilder|null {
        if (!empty($validationErrors)) {
            $errorText = implode("\n", $validationErrors);
            return $this->buildErrorMessage($errorText);
        }

        $messages_definitions = $step->get_messages();
        $messages = [];

        if (empty($messages_definitions)) {
            return null;
        }

        foreach ($messages_definitions as $message) {
            $type = isset($message['type']) ? $message['type'] : null;
            switch ($type) {
                case 'text':
                    $messages[] = LineMessageBuilder::createTextMessage($message['text']);
                    break;
                case 'template_button':
                    $messages[] = $this->buildTemplateButtonMessage($message, $step->get_id());
                    break;
                case 'sticker':
                    if (isset($message['packageId'], $message['stickerId'])) {
                        $messages[] = LineMessageBuilder::createStickerMessage($message['packageId'], $message['stickerId']);
                    }
                    break;
                case 'image':
                    if (isset($message['originalContentUrl'], $message['previewImageUrl'])) {
                        $messages[] = LineMessageBuilder::createImageMessage($message['originalContentUrl'], $message['previewImageUrl']);
                    }
                    break;
                case 'video':
                    if (isset($message['originalContentUrl'], $message['previewImageUrl'])) {
                        $messages[] = LineMessageBuilder::createVideoMessage($message['originalContentUrl'], $message['previewImageUrl']);
                    }
                    break;
                case 'audio':
                    if (isset($message['originalContentUrl'], $message['duration'])) {
                        $messages[] = LineMessageBuilder::createAudioMessage($message['originalContentUrl'], $message['duration']);
                    }
                    break;
                case 'location':
                    if (isset($message['title'], $message['address'], $message['latitude'], $message['longitude'])) {
                        $messages[] = LineMessageBuilder::createLocationMessage($message['title'], $message['address'], $message['latitude'], $message['longitude']);
                    }
                    break;
                case 'flex':
                    if (isset($message['contents'])) {
                        $altText = isset($message['altText']) ? $message['altText'] : 'Flex Message';
                        $messages[] = LineMessageBuilder::createFlexRawMessage($message['contents'], $altText);
                    }
                    break;
                case 'raw':
                    if (isset($message['raw'])) {
                        $messages[] = LineMessageBuilder::createRawMessage($message['raw']);
                    }
                    break;
            }
        }

        return $messages ? LineMessageBuilder::createMultiMessage($messages) : null;
    }

    /**
     * Build an error message.
     *
     * @param string $text The error text.
     * @return \LINE\LINEBot\MessageBuilder\TextMessageBuilder
     */
    public function buildErrorMessage(string $text): \LINE\LINEBot\MessageBuilder\TextMessageBuilder {
        return LineMessageBuilder::createTextMessage($text);
    }

    /**
     * Build an template_button message.
     *
     * @param array $message The message definition.
     * @param string $step_id The current step ID.
     * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder
     */
    public function buildTemplateButtonMessage(array $message, string $step_id): \LINE\LINEBot\MessageBuilder\FlexMessageBuilder {
        $options = isset($message['options']) ? $message['options'] : [];
        $columns = isset($message['column']) ? $message['column'] : 1;
        $widths = ["100%", "100%", "48%", "30%", "20%"]; // 0, 1, 2, 3, 4 columns

        $choices = [];
        foreach ($options as $option) {
            $data = 'mode=interaction&step=' . $step_id . '&value=' . $option['value'];
            if (isset($option['nextStepId']) && !is_null($option['nextStepId'])) {
                $data .= '&nextStepId=' . $option['nextStepId'];
            }

            $button_style = 'primary';
            if (isset($option['secondary']) && $option['secondary']) {
                $button_style = 'secondary';
            }

            $button_width = isset($option['width']) ? $option['width'] : (isset($widths[$columns]) ? $widths[$columns] : intval(90 / $columns) . "%");
            $label = isset($option['label']) ? $option['label'] : $option['value'];

            $action = [
                'type' => 'postback',
                'label' => $label,
                'link' => $data,
                'displayText' => $label,
            ];

            $button_atts = [
                'style' => $button_style,
                'width' => $button_width,
            ];

            $choices[] = LineMessageBuilder::createButtonComponent($action, $button_atts);
        }

        $choices_box = [];
        if (!empty($choices)) {
            $chunked_choices = array_chunk($choices, $columns);
            $choices_box[] = LineMessageBuilder::createMultiColumnBoxComponent($chunked_choices);
        }

        $components = [];
        if (isset($message['text'])) {
            $components[] = LineMessageBuilder::createTextComponent($message['text'], ['wrap' => true]);
        }
        $components = array_merge($components, $choices_box);

        $body = LineMessageBuilder::createBoxComponent($components, null, ['layout' => 'vertical', 'spacing' => 'md']);

        $bubble = new \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder();
        $bubble->setBody($body);

        $altText = isset($message['text']) ? $message['text'] : (__('Please select an option.', LineConnect::PLUGIN_NAME));

        return new \LINE\LINEBot\MessageBuilder\FlexMessageBuilder($altText, $bubble);
    }
}
