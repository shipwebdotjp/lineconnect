<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Interaction\InteractionDefinition;
use Shipweb\LineConnect\Interaction\ValidationResult;
use Shipweb\LineConnect\Interaction\StepDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Interaction\RunPolicyEnforcer;

/**
 * Handles the processing of a single interaction step.
 */
class InteractionHandler {
    private $session_repository;
    private $action_runner;
    private $message_builder;
    private $normalizer;
    private $validator;
    private $run_policy_enforcer;

    public function __construct(
        SessionRepository $session_repository,
        ActionRunner $action_runner,
        MessageBuilder $message_builder,
        InputNormalizer $normalizer,
        Validator $validator,
        RunPolicyEnforcer $run_policy_enforcer
    ) {
        $this->session_repository = $session_repository;
        $this->action_runner = $action_runner;
        $this->message_builder = $message_builder;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->run_policy_enforcer = $run_policy_enforcer;
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
    public function presentStep(InteractionSession $session, InteractionDefinition $interaction_definition, object $event): array {
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
            // var_dump($messages);
        }

        // Build the message for the current step.
        $step_message = $this->message_builder->build($step, $session);
        if ($step_message) {
            $messages[] = $step_message;
        }

        return array_filter(apply_filters(LineConnect::FILTER_PREFIX . 'interaction_message', $messages, $session, $event));
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

        $messages = [];

        // Check for cancel words
        $isCancelConfirm = $this->determine_is_cancel_confirm($event, $interaction_definition);
        if ($isCancelConfirm) {
            $cancel_step = $interaction_definition->get_special_step('cancelConfirm');
            if ($cancel_step) {
                $messages[] = $this->message_builder->build($cancel_step, $session);
                return array_filter(apply_filters(LineConnect::FILTER_PREFIX . 'interaction_message_cancel_confirm', $messages, $session, $event));
            }
        }
        $userChoice = $this->determine_user_choice($event, $interaction_definition);
        if ($userChoice === 'abort') {
            // delete session
            $canceled_step = $interaction_definition->get_special_step('canceled');
            if ($canceled_step) {
                $messages[] = $this->message_builder->build($canceled_step, $session);
            }
            $this->session_repository->delete($session);
            return array_filter(apply_filters(LineConnect::FILTER_PREFIX . 'interaction_message_cancel_request', $messages, $session, $event));
        } elseif ($userChoice === 'continue') {
            // continue interaction
            return $this->presentStep($session, $interaction_definition, $event);
        }

        $user_input = $this->extractUserInput($event);

        if ($user_input !== null) {
            $normalized_input = apply_filters(LineConnect::FILTER_PREFIX . 'interaction_normalize', $this->normalizer->normalize($user_input, $step->get_normalize_rules()), $step, $session, $event);
            $validation_result = apply_filters(LineConnect::FILTER_PREFIX . 'interaction_validate', $this->validator->validate($normalized_input, $step->get_validation_rules()), $step, $session, $event);
            if (!$validation_result->isValid()) {
                $error_message = $this->message_builder->build($step, $session, $validation_result->getErrors());
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
        $next_step_id = $this->determine_next_step_id($step, $session, $event);

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
                $messages[] = $this->message_builder->build($next_step, $session);
            }
        } else {
            // No next step, so complete the interaction
            $session->complete();
            // Potentially build a completion message
            $completion_step = $interaction_definition->get_special_step('complete');
            if ($completion_step) {
                $messages[] = $this->message_builder->build($completion_step, $session);
            }
        }


        // Apply runPolicy if session was completed
        if ($session->get_status() === 'completed') {
            $this->run_policy_enforcer->enforceOnComplete($session, $interaction_definition);
            // save answers
            $this->save_answers($session, $interaction_definition);
        }

        $this->session_repository->save($session);
        return array_filter(apply_filters(LineConnect::FILTER_PREFIX . 'interaction_message', $messages, $session, $event));
    }

    /**
     * Extract user input from a LINE event.
     *
     * @param object $event
     * @return string|null
     */
    private function extractUserInput(?object $event): ?string {
        if (!$event) {
            return null;
        }

        // 1. postback->params->datetime|date|time (最優先)
        if ($event->type === 'postback' && isset($event->postback->params)) {
            $params = $event->postback->params;

            // datetime, date, time の順で確認
            if (isset($params->datetime)) {
                return $params->datetime;
            }
            if (isset($params->date)) {
                return $params->date;
            }
            if (isset($params->time)) {
                return $params->time;
            }
        }

        // 2. postback->dataがクエリストリング形式の場合のvalue
        if ($event->type === 'postback' && isset($event->postback->data)) {
            $data = $event->postback->data;

            // クエリストリング形式かチェック
            if (strpos($data, '=') !== false) {
                parse_str($data, $parsed);
                if (isset($parsed['value'])) {
                    return $parsed['value'];
                }
            }
        }

        // 3. postback->data (そのまま)
        if ($event->type === 'postback' && isset($event->postback->data)) {
            return $event->postback->data;
        }

        // 4. message->text (最後)
        if (
            $event->type === 'message' &&
            isset($event->message->type) &&
            $event->message->type === 'text' &&
            isset($event->message->text)
        ) {
            return $event->message->text;
        }

        return null;
    }

    /**
     * Determine if the incoming event represents a cancel-confirm action.
     *
     * Returns true if:
     *  - a postback contains a query parameter `action=cancel`
     *  - or the user's input matches any of the cancelWords rules defined on the interaction
     *
     * @param object $event
     * @param InteractionDefinition $interaction_definition
     * @return bool
     */
    private function determine_is_cancel_confirm(object $event, InteractionDefinition $interaction_definition): bool {
        if (!$event) {
            return false;
        }

        // 1) Postback with explicit cancel=confirm in query-string style data
        if ($event->type === 'postback' && isset($event->postback->data)) {
            $data = $event->postback->data;

            if (strpos($data, '=') !== false) {
                parse_str($data, $parsed);
                if (isset($parsed['action']) && mb_strtolower((string)$parsed['action'], 'UTF-8') === 'cancel') {
                    return true;
                }
            }
        }

        // 2) Check cancelWords rules against the extracted user input
        $input = $this->extractUserInput($event);
        if ($input === null) {
            return false;
        }

        $input_lower = mb_strtolower((string)$input, 'UTF-8');
        $cancel_words = $interaction_definition->get_cancel_words();
        if (empty($cancel_words) || !is_array($cancel_words)) {
            return false;
        }

        foreach ($cancel_words as $cw) {
            // cw can be array or object; normalise to array-like access
            $type = null;
            $value = null;
            if (is_array($cw)) {
                $type = $cw['type'] ?? null;
                $value = $cw['value'] ?? null;
            } elseif (is_object($cw)) {
                $type = $cw->type ?? null;
                $value = $cw->value ?? null;
            }

            if ($type === null || $value === null) {
                continue;
            }

            $type = (string)$type;
            $value = (string)$value;

            if ($value === '') {
                continue;
            }

            switch ($type) {
                case 'equals':
                    if ($input_lower === mb_strtolower($value, 'UTF-8')) {
                        return true;
                    }
                    break;
                case 'contains':
                    if (mb_stripos((string)$input, $value, 0, 'UTF-8') !== false) {
                        return true;
                    }
                    break;
                case 'regex':
                    // Ensure pattern has delimiters. If not, wrap with /.../u
                    $pattern = $value;
                    if ($pattern === '') {
                        break;
                    }
                    $first = $pattern[0] ?? '';
                    if ($first !== '/' && $first !== '#') {
                        // escape delimiter occurrences and add unicode flag
                        $escaped = str_replace('/', '\/', $pattern);
                        $pattern = '/' . $escaped . '/u';
                    }
                    // Suppress warnings from invalid patterns; treat as non-match on error.
                    if (@preg_match($pattern, (string)$input) === 1) {
                        return true;
                    }
                    break;
                default:
                    // Unknown type - ignore
                    break;
            }
        }

        return false;
    }

    /**
     * イベントからユーザーの選択を判定する
     * 
     * postbackイベントのdataフィールドからaction パラメータを解析し、
     * ユーザーの選択（'abort' または 'continue'）を特定する。
     * 
     * @param object $event イベントオブジェクト（postbackタイプを想定）
     * @param InteractionDefinition $interaction_definition インタラクション定義
     * @return string|null ユーザーの選択（'abort' または 'continue'）、該当しない場合はnull
     */
    private function determine_user_choice(object $event, InteractionDefinition $interaction_definition): ?string {
        if (!$event || $event->type !== 'postback' || !isset($event->postback->data)) {
            return null;
        }

        $data = $event->postback->data;

        // クエリ文字列形式をパース
        if (strpos($data, '=') !== false) {
            parse_str($data, $parsed);

            if (isset($parsed['action'])) {
                $action = mb_strtolower((string)$parsed['action'], 'UTF-8');

                // 有効なアクションのみ受け入れ
                if (in_array($action, ['abort', 'continue'], true)) {
                    return $action;
                }
            }
        }

        return null;
    }

    private function determine_next_step_id(StepDefinition $step, InteractionSession $session, object $event): ?string {
        $return_to_key = '__return_to';

        // Handle postback data for navigation
        if ($event->type === 'postback' && isset($event->postback->data)) {
            $data = $event->postback->data;
            if (strpos($data, '=') !== false) {
                parse_str($data, $parsed);

                // If 'returnTo' is present, it means we are in an "edit" flow.
                // We save the step to return to (e.g., a confirmation step) in the session.
                if (isset($parsed['returnTo'])) {
                    $session->set_answer($return_to_key, $parsed['returnTo']);
                }

                // If 'nextStepId' is present, navigate to that step immediately.
                // This is used for "edit" buttons to jump to a specific step.
                if (isset($parsed['nextStepId'])) {
                    return $parsed['nextStepId'];
                }
            }
        }

        // After a user provides a new value for an edited step, this logic returns them
        // to the step specified in 'returnTo' (e.g., the confirmation step).
        $return_to_step = $session->get_answer($return_to_key);
        if ($return_to_step) {
            $session->set_answer($return_to_key, null); // Clear the return marker to avoid loops
            return $return_to_step;
        }

        // Standard branching logic based on the user's answer for the current step
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

        // If no branches match, apply fallback rules:
        // 1) If this step is marked as a stop step, treat as final (null)
        if ($step->is_stop_step()) {
            return null;
        }

        // 2) If the step defines a nextStepId, use it
        $next = $step->get_next_step_id();
        if (!empty($next)) {
            return $next;
        }

        // 3) nextStepId is empty — determine the next step by locating the current step
        //    in the interaction's ordered steps and returning the subsequent element.
        $interaction_id = $session->get_interaction_id();
        $interaction_version = $session->get_interaction_version();
        $interaction = InteractionDefinition::from_post($interaction_id, $interaction_version);

        if (!$interaction) {
            return null;
        }

        $steps = $interaction->get_steps();
        for ($i = 0; $i < count($steps); $i++) {
            $s = $steps[$i];
            if ($s->get_id() === $step->get_id()) {
                if (isset($steps[$i + 1])) {
                    return $steps[$i + 1]->get_id();
                }
                break;
            }
        }

        // No next step found; treat as final
        return null;
    }

    private function save_answers(InteractionSession &$session, InteractionDefinition $interaction_definition): void {
        global $wpdb;

        $session->unset_answers($interaction_definition->get_exclude_steps());

        // Check if the interaction is configured to save answers to the profile.
        if ($interaction_definition->get_storage() !== 'profile') {
            return;
        }

        // $answers = $session->get_excluded_answers($interaction_definition->get_exclude_steps());
        $answers = $session->get_answers();
        if (empty($answers)) {
            return;
        }

        $line_user_id = $session->get_line_user_id();
        $channel_prefix = $session->get_channel_prefix();
        $table_name = $wpdb->prefix . LineConnect::TABLE_LINE_ID;

        // Get current profile
        $current_profile = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT profile FROM $table_name WHERE line_id = %s AND channel_prefix = %s",
                $line_user_id,
                $channel_prefix
            )
        );

        $profile_array = json_decode($current_profile ?? '{}', true);

        foreach ($answers as $step_id => $answer) {

            $step = $interaction_definition->get_step($step_id);
            if ($step) {
                $profile_key = $step->get_id();
                if (isset($answer) && $answer !== '') {
                    $profile_array[$profile_key] = $answer;
                } else {
                    // Remove the key from the profile if the answer is empty.
                    unset($profile_array[$profile_key]);
                }
            }
        }

        // Update database
        $wpdb->update(
            $table_name,
            ['profile' => json_encode($profile_array, JSON_UNESCAPED_UNICODE)],
            ['line_id' => $line_user_id, 'channel_prefix' => $channel_prefix]
        );
    }
}
