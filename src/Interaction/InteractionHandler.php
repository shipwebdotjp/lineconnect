<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Interaction\InteractionDefinition;
use Shipweb\LineConnect\Interaction\ValidationResult;
use Shipweb\LineConnect\Interaction\StepDefinition;

/**
 * Handles the processing of a single interaction step.
 */
class InteractionHandler {
    private $session_repository;
    private $action_runner;
    private $message_builder;
    private $normalizer;
    private $validator;

    public function __construct(
        SessionRepository $session_repository,
        ActionRunner $action_runner,
        MessageBuilder $message_builder,
        InputNormalizer $normalizer,
        Validator $validator
    ) {
        $this->session_repository = $session_repository;
        $this->action_runner = $action_runner;
        $this->message_builder = $message_builder;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
    }

    /**
     * Presents the current step to the user without processing any input.
     * This is typically used for the first step of an interaction.
     *
     * @param InteractionSession $session
     * @param InteractionDefinition $interaction_definition
     * @param object $event
     * @return array
     */
    public function presentStep(InteractionSession $session, InteractionDefinition $interaction_definition, object $event): array
    {
        $current_step_id = $session->get_current_step_id();
        $step = $interaction_definition->get_step($current_step_id);

        if (!$step) {
            return [];
        }

        $messages = [];

        // Execute before-actions for the current step.
        $before_actions = $step->get_before_actions();
        if (!empty($before_actions)) {
            $action_messages = $this->action_runner->run($before_actions, $session, $event);
            $messages = array_merge($messages, $action_messages);
        }

        // Build the message for the current step.
        $step_message = $this->message_builder->build($step);
        if ($step_message) {
            $messages[] = $step_message;
        }

        return array_filter($messages);
    }

    /**
     * Handle an interaction event.
     *
     * @param InteractionSession $session The current interaction session.
     * @param object $event The LINE event object.
     * @param InteractionDefinition $interaction_definition The interaction definition.
     * @return array A list of messages to be sent.
     */
    public function handle(InteractionSession $session, object $event, InteractionDefinition $interaction_definition): array {
        $current_step_id = $session->get_current_step_id();
        $step = $interaction_definition->get_step($current_step_id);

        if (!$step) {
            // Step not found, maybe log this error.
            // For now, we can't proceed.
            return [];
        }

        $user_input = $this->extractUserInput($event);

        if ($user_input !== null) {
            $normalized_input = $this->normalizer->normalize($user_input, $step->get_normalize_rules());
            $validation_result = $this->validator->validate($normalized_input, $step->get_validation_rules());

            if (!$validation_result->isValid()) {
                $error_message = $this->message_builder->build($step, $validation_result->getErrors());
                return [$error_message];
            }
            $session->set_answer($current_step_id, $normalized_input);
        }

        $messages = [];

        // Execute after-actions
        $after_actions = $step->get_after_actions();
        if (!empty($after_actions)) {
            $action_messages = $this->action_runner->run($after_actions, $session, $event);
            $messages = array_merge($messages, $action_messages);
        }

        // Determine the next step
        $next_step_id = $this->determine_next_step_id($step, $session);

        if ($next_step_id) {
            $session->set_current_step_id($next_step_id);
            $next_step = $interaction_definition->get_step($next_step_id);
            if ($next_step) {
                // Execute before-actions for the next step
                $before_actions = $next_step->get_before_actions();
                if (!empty($before_actions)) {
                    $action_messages = $this->action_runner->run($before_actions, $session, $event);
                    $messages = array_merge($messages, $action_messages);
                }
                $messages[] = $this->message_builder->build($next_step);
            }
        } else {
            // No next step, so complete the interaction
            $session->complete();
            // Potentially build a completion message
            $completion_step = $interaction_definition->get_special_step('complete');
            if ($completion_step) {
                $messages[] = $this->message_builder->build($completion_step);
            }
        }

        $this->session_repository->save($session);

        return array_filter($messages);
    }

    /**
     * Extract user input from a LINE event.
     *
     * @param object $event
     * @return string|null
     */
    private function extractUserInput(object $event): ?string {
        if ($event->type === 'message' && $event->message->type === 'text') {
            return $event->message->text;
        } elseif ($event->type === 'postback') {
            return $event->postback->data;
        }
        return null;
    }

    private function determine_next_step_id(StepDefinition $step, InteractionSession $session): ?string {
        $branches = $step->get_branches();
        $last_answer = $session->get_answer($step->get_id());

        if (!empty($branches) && $last_answer !== null) {
            foreach ($branches as $branch) {
                $branch = (object)$branch;
                $condition_type = $branch->type ?? 'equals';
                $condition_value = $branch->value ?? '';

                $match = false;
                switch ($condition_type) {
                    case 'equals':
                        $match = ($last_answer === $condition_value);
                        break;
                    case 'contains':
                        $match = (is_string($last_answer) && is_string($condition_value) && strpos($last_answer, $condition_value) !== false);
                        break;
                    case 'regex':
                        $match = (is_string($last_answer) && preg_match($condition_value, $last_answer) === 1);
                        break;
                }

                if ($match) {
                    return $branch->nextStepId;
                }
            }
        }

        return $step->get_next_step_id();
    }
}
