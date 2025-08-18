<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Message\LINE\Builder as LineMessageBuilder;
use Shipweb\LineConnect\Interaction\StepDefinition;

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
            if (isset($message['type']) && $message['type'] === 'text') {
                $messages[] = LineMessageBuilder::createTextMessage($message['text']);
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
}
