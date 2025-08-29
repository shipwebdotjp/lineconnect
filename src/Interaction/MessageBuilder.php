<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Message\LINE\Builder as LineMessageBuilder;
use Shipweb\LineConnect\Interaction\StepDefinition;
use Shipweb\LineConnect\Interaction\InteractionSession;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Builds messages for interactions.
 */
class MessageBuilder {
    /**
     * Build a message for a given step.
     *
     * @param StepDefinition $step The step definition.
     * @param InteractionSession|null $session The interaction session.
     * @param array $validationErrors Optional validation errors.
     * @return \LINE\LINEBot\MessageBuilder
     */
    public function build(StepDefinition $step, ?InteractionSession $session = null, array $validationErrors = []): \LINE\LINEBot\MessageBuilder|null {
        if (!empty($validationErrors)) {
            $errorText = implode("\n", $validationErrors);
            return $this->buildErrorMessage($errorText);
        }

        // if ($step->get_special_type() === 'editPicker' && $session) {
        //     $message_definition = $step->get_messages()[0] ?? [];
        //     return $this->buildEditPickerMessage($session, $message_definition, $step->get_id());
        // }

        $messages = [];
        $messages_definitions = $step->get_messages();

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
                case 'confirm_template':
                    $messages[] = $this->buildConfirmTemplateMessage($session, $message, $step->get_id());
                    break;
                case 'editPicker_template':
                    $messages[] = $this->buildEditPickerMessage($session, $message, $step->get_id());
                    break;
                case 'cancel_confirm_template':
                    $messages[] = $this->buildCancelConfirmTemplateMessage($session, $message, $step->get_id());
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
                'data' => $data,
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

    /**
     * Build a confirm_template message.
     *
     * @param InteractionSession|null $session The interaction session.
     * @param array $message The message definition.
     * @param string $step_id The current step ID.
     * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder
     */
    private function buildConfirmTemplateMessage(?InteractionSession $session, array $message, string $step_id): \LINE\LINEBot\MessageBuilder\FlexMessageBuilder {
        $interaction_id = $session->get_interaction_id();
        $interaction_version = $session->get_interaction_version();
        $interaction = InteractionDefinition::from_post($interaction_id, $interaction_version);
        $exclude_steps = $interaction->get_exclude_steps();
        $exclude_steps[] = $step_id;

        $answers = $session->get_excluded_answers($exclude_steps);

        $title = $message['title'] ?? (__('Confirmation', LineConnect::PLUGIN_NAME));
        $header = LineMessageBuilder::createBoxComponent([
            LineMessageBuilder::createTextComponent($title, ['weight' => 'bold', 'size' => 'xl']),
        ], null, ['layout' => 'vertical', 'paddingAll' => 'md']);

        $body_components = [];
        foreach ($answers as $answer_step_id => $answer_value) {
            $step = $interaction->get_step($answer_step_id);
            if ($step) {
                $body_components[] = LineMessageBuilder::createTextComponent($step->get_title(), ['size' => 'sm', 'color' => '#555555']);
                $body_components[] = LineMessageBuilder::createTextComponent(is_array($answer_value) ? implode(', ', $answer_value) : $answer_value, ['wrap' => true]);
            }
        }

        $body = LineMessageBuilder::createBoxComponent($body_components, null, ['layout' => 'vertical', 'spacing' => 'md']);

        $apply_button = LineMessageBuilder::createButtonComponent([
            'type' => 'postback',
            'label' => $message['apply']['label'] ?? (__('Apply', LineConnect::PLUGIN_NAME)),
            'link' => 'mode=interaction&step=' . $step_id . '&nextStepId=' . $message['apply']['nextStepId'],
            'displayText' => $message['apply']['label'] ?? (__('Apply', LineConnect::PLUGIN_NAME)),
        ]);

        $edit_button = LineMessageBuilder::createButtonComponent([
            'type' => 'postback',
            'label' => $message['edit']['label'] ?? (__('Edit', LineConnect::PLUGIN_NAME)),
            'link' => 'mode=interaction&step=' . $step_id . '&nextStepId=' . ($message['edit']['nextStepId'] ?? 'editPicker'),
            'displayText' => $message['edit']['label'] ?? (__('Edit', LineConnect::PLUGIN_NAME)),
        ]);

        $footer = LineMessageBuilder::createBoxComponent([$apply_button, $edit_button], null, ['layout' => 'horizontal', 'spacing' => 'sm']);

        $bubble = new \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder();
        $bubble->setHeader($header);
        $bubble->setBody($body);
        $bubble->setFooter($footer);

        $altText = $title;

        return new \LINE\LINEBot\MessageBuilder\FlexMessageBuilder($altText, $bubble);
    }

    /**
     * Build an edit picker message.
     *
     * @param InteractionSession $session The interaction session.
     * @param array $message The message definition from the step.
     * @param string $step_id The current step ID ('editPicker').
     * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder
     */
    private function buildEditPickerMessage(InteractionSession $session, array $message, string $step_id): \LINE\LINEBot\MessageBuilder\FlexMessageBuilder {
        $interaction_id = $session->get_interaction_id();
        $interaction_version = $session->get_interaction_version();
        $interaction = InteractionDefinition::from_post($interaction_id, $interaction_version);

        $exclude_steps = $interaction->get_exclude_steps();
        $exclude_steps[] = $step_id; // Exclude the picker itself

        $answers = $session->get_excluded_answers($exclude_steps);
        $returnTo = $session->get_previous_step_id(); // This should be the 'confirm' step

        $title = $message['title'] ?? (__('Select item to edit', LineConnect::PLUGIN_NAME));
        $header = LineMessageBuilder::createBoxComponent([
            LineMessageBuilder::createTextComponent($title, ['weight' => 'bold', 'size' => 'xl']),
        ], null, ['layout' => 'vertical', 'paddingAll' => 'md']);

        $body_components = [];
        foreach (array_keys($answers) as $answer_step_id) {
            $step_to_edit = $interaction->get_step($answer_step_id);
            if ($step_to_edit) {
                $data = http_build_query([
                    'mode' => 'interaction',
                    'step' => $step_id,
                    'nextStepId' => $answer_step_id,
                    'returnTo' => $returnTo
                ]);
                $body_components[] = LineMessageBuilder::createButtonComponent([
                    'type' => 'postback',
                    'label' => $step_to_edit->get_title(),
                    'link' => $data,
                    'displayText' => $step_to_edit->get_title(),
                ], ['style' => 'secondary', 'height' => 'sm']);
            }
        }

        $body = LineMessageBuilder::createBoxComponent($body_components, null, ['layout' => 'vertical', 'spacing' => 'sm']);

        $cancel_label = $message['cancel']['label'] ?? (__('Back to confirmation', LineConnect::PLUGIN_NAME));
        $footer_button = LineMessageBuilder::createButtonComponent([
            'type' => 'postback',
            'label' => $cancel_label,
            'link' => http_build_query([
                'mode' => 'interaction',
                'step' => $step_id,
                'nextStepId' => $message['cancel']['nextStepId'] ?? $returnTo
            ]),
            'displayText' => $cancel_label,
        ]);

        $footer = LineMessageBuilder::createBoxComponent([$footer_button], null, ['layout' => 'vertical', 'spacing' => 'sm']);

        $bubble = new \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder();
        $bubble->setHeader($header);
        $bubble->setBody($body);
        $bubble->setFooter($footer);

        $altText = $title;

        return new \LINE\LINEBot\MessageBuilder\FlexMessageBuilder($altText, $bubble);
    }

    /**
     * Build a cancel confirmation template message.
     *
     * @param InteractionSession $session The interaction session.
     * @param array $message The message definition from the step.
     * @param string $step_id The current step ID ('cancel_confirm_template').
     * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder
     */
    private function buildCancelConfirmTemplateMessage(InteractionSession $session, array $message, string $step_id): \LINE\LINEBot\MessageBuilder\FlexMessageBuilder {
        $interaction_id = $session->get_interaction_id();
        $interaction_version = $session->get_interaction_version();
        $interaction = InteractionDefinition::from_post($interaction_id, $interaction_version);

        $title = $message['title'] ?? (__('Are you sure you want to cancel?', LineConnect::PLUGIN_NAME));
        $header = LineMessageBuilder::createBoxComponent([
            LineMessageBuilder::createTextComponent($title, ['weight' => 'bold', 'size' => 'xl']),
        ], null, ['layout' => 'vertical', 'paddingAll' => 'md']);

        $abort_label = $message['abort']['label'] ?? (__('Yes', LineConnect::PLUGIN_NAME));
        $continue_label = $message['continue']['label'] ?? (__('No', LineConnect::PLUGIN_NAME));

        $footer = LineMessageBuilder::createBoxComponent([
            LineMessageBuilder::createButtonComponent([
                'type' => 'postback',
                'label' => $abort_label,
                'link' => http_build_query([
                    'mode' => 'interaction',
                    'step' => $step_id,
                    'action' => 'abort'
                ]),
                'displayText' => $abort_label,
            ], ['style' => 'primary', 'height' => 'sm']),
            LineMessageBuilder::createButtonComponent([
                'type' => 'postback',
                'label' => $continue_label,
                'link' => http_build_query([
                    'mode' => 'interaction',
                    'step' => $step_id,
                    'action' => 'continue'
                ]),
                'displayText' => $continue_label,
            ], ['style' => 'secondary', 'height' => 'sm']),
        ], null, ['layout' => 'vertical', 'spacing' => 'sm']);

        $bubble = new \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder();
        $bubble->setHeader($header);
        // $bubble->setBody($body);
        $bubble->setFooter($footer);

        $altText = $title;

        return new \LINE\LINEBot\MessageBuilder\FlexMessageBuilder($altText, $bubble);
    }
}
