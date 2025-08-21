<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\InteractionHandler;
use Shipweb\LineConnect\Interaction\InteractionDefinition;
use Shipweb\LineConnect\Interaction\InteractionSession;
use Shipweb\LineConnect\Message\LINE\Builder as LineMessageBuilder;


/**
 * Manages the overall interaction flow.
 */
class InteractionManager {

    private $session_repository;
    private $interaction_handler;

    public function __construct(
        SessionRepository $session_repository,
        InteractionHandler $interaction_handler
    ) {
        $this->session_repository = $session_repository;
        $this->interaction_handler = $interaction_handler;
    }

    /**
     * Handles an incoming event for an ONGOING interaction.
     *
     * @param string $channel_prefix
     * @param string $line_user_id
     * @param object $event The LINE webhook event.
     * @return array Messages to be sent back to the user.
     */
    public function handleEvent(string $channel_prefix, string $line_user_id, object $event): array {
        // Find an active session for the user.
        $session = $this->session_repository->find_active($channel_prefix, $line_user_id);

        if (!$session) {
            // No active session found, so do nothing.
            return [];
        }

        // Session exists, so load its definition.
        $interaction_definition = InteractionDefinition::from_post(
            $session->get_interaction_id(),
            $session->get_interaction_version()
        );

        if (!$interaction_definition) {
            // TODO: Handle error: definition not found for an existing session.
            // Maybe log the error and complete the session to prevent it from being stuck.
            $session->complete();
            $this->session_repository->save($session);
            return [];
        }

        // Delegate the handling of the step to the InteractionHandler.
        $messages = $this->interaction_handler->handle($session, $event, $interaction_definition);

        // Check if the session was completed and if there's a paused session to resume.
        if ($session->get_status() === 'completed') {
            $paused_sessions = $this->session_repository->find_paused($channel_prefix, $line_user_id);
            if (!empty($paused_sessions)) {
                // Take the most recent paused session (first in the array due to ORDER BY DESC)
                $paused_session = $paused_sessions[0];

                // A paused session exists, so let's resume it.
                $paused_session->set_status('active');
                $this->session_repository->save($paused_session);

                // Load the definition for the resumed interaction.
                $resumed_definition = InteractionDefinition::from_post(
                    $paused_session->get_interaction_id(),
                    $paused_session->get_interaction_version()
                );

                if ($resumed_definition) {
                    // Present the current step of the resumed session.
                    $resume_messages = $this->interaction_handler->presentStep($paused_session, $resumed_definition, $event);
                    $messages = array_merge($messages, $resume_messages);
                } else {
                    // Error handling: definition for paused session not found.
                    // To prevent a stuck session, we should probably complete it.
                    $paused_session->complete();
                    $this->session_repository->save($paused_session);
                }
            }
        }

        return [LineMessageBuilder::createMultiMessage($messages)];
    }

    /**
     * Starts a new interaction for a user, triggered programmatically.
     *
     * @param int $interaction_id The ID of the interaction to start.
     * @param string $line_user_id The LINE user ID.
     * @param string $channel_prefix The channel prefix.
     * @param string $overridePolicy 上書きポリシー
     * @return array The initial messages for the first step of the interaction.
     */
    public function startInteraction(int $interaction_id, string $line_user_id, string $channel_prefix, ?string $overridePolicy = null): array {
        // Load the definition for the interaction to be started.
        $interaction_definition = InteractionDefinition::from_post($interaction_id, null);

        if (!$interaction_definition) {
            // TODO: Handle error: couldn't find the interaction definition to start.
            error_log("Interaction definition not found for ID: $interaction_id");
            return [];
        }

        // Fallback to the definition's override policy when overridePolicy is not provided.
        // Note: InteractionDefinition::get_override_policy() is used here as the definition method.
        $overridePolicy = $overridePolicy ?? $interaction_definition->get_override_policy() ?? 'stack';

        // check if overridePolicy is valid
        if (!in_array($overridePolicy, ['reject', 'restart_same', 'restart_diff', 'restart_always', 'stack'])) {
            $overridePolicy = 'stack';
        }

        // Enforce runPolicy 'single_forbid' at start time (interaction unit).
        // If run_policy is single_forbid and any session for this interaction exists for this user/channel,
        // refuse to start a new interaction.
        if ($interaction_definition->get_run_policy() === 'single_forbid') {
            $existing_sessions = $this->session_repository->find_sessions_by_interaction($channel_prefix, $line_user_id, $interaction_definition->get_id());
            if (!empty($existing_sessions)) {
                return [];
            }
        }

        // prepare first step (needed for resets)
        $first_step = $interaction_definition->get_first_step();
        if (!$first_step) {
            // TODO: Handle error: interaction has no steps.
            error_log("Interaction has no steps for ID: $interaction_id");
            return [];
        }
        $first_step_id = $first_step->get_id();

        // Determine active session (if any) for this user/channel.
        $activeSession = $this->session_repository->find_active($channel_prefix, $line_user_id);
        $same_form = $activeSession && $activeSession->get_interaction_id() === $interaction_definition->get_id();

        // Check paused/stacked sessions for duplication (used by some policies).
        $stack_same = $this->session_repository->find_paused_by_interaction($channel_prefix, $line_user_id, $interaction_definition->get_id());

        // Synthetic event used when presenting the first step or resetting.
        $synthetic_event = (object) [
            'type' => 'internal-start',
            'source' => (object) [
                'type' => 'user',
                'userId' => $line_user_id
            ]
        ];
        // var_dump($interaction_id, $line_user_id, $channel_prefix, $overridePolicy, $activeSession, $same_form, $stack_same);

        // Implement override policies.
        switch ($overridePolicy) {
            case 'reject':
                // Always refuse to interrupt an existing active/editing session.
                if ($activeSession) {
                    return [];
                }
                // No active session -> proceed to create.
                break;

            case 'restart_same':
                if ($activeSession && $same_form) {
                    // Reset the existing active session to the first step and present it.
                    $activeSession->reset_to_step($first_step_id);
                    if (!$this->session_repository->save($activeSession)) {
                        return [];
                    }
                    return $this->interaction_handler->presentStep($activeSession, $interaction_definition, $synthetic_event);
                }

                // If there's a paused session with same interaction, activate & reset it.
                if ($stack_same) {
                    // if active session then pause it
                    if ($activeSession) {
                        // $activeSession->pause();
                        $activeSession->set_status('paused');
                        if (!$this->session_repository->save($activeSession)) {
                            return [];
                        }
                    }

                    $stack_same->reset_to_step($first_step_id);
                    $stack_same->set_status('active');
                    if (!$this->session_repository->save($stack_same)) {
                        return [];
                    }
                    return $this->interaction_handler->presentStep($stack_same, $interaction_definition, $synthetic_event);
                }

                // Otherwise: if there's an active different session, do nothing.
                if ($activeSession && !$same_form) {
                    return [];
                }
                // Else no active session -> proceed to create new.
                break;

            case 'restart_diff':
                if ($activeSession && !$same_form) {
                    // Destroy the different active session and allow new start.
                    if (!$this->session_repository->delete($activeSession)) {
                        return [];
                    }
                    // proceed to create new session
                    break;
                }

                // If active session is same form -> continue (do nothing).
                if ($activeSession && $same_form) {
                    return [];
                }

                // No active session -> proceed to create.
                break;

            case 'restart_always':
                if ($activeSession && $same_form) {
                    // Reset existing active session to first step.
                    $activeSession->reset_to_step($first_step_id);
                    if (!$this->session_repository->save($activeSession)) {
                        return [];
                    }
                    return $this->interaction_handler->presentStep($activeSession, $interaction_definition, $synthetic_event);
                }

                if ($activeSession && !$same_form) {
                    // Destroy different active session and start new.
                    if (!$this->session_repository->delete($activeSession)) {
                        return [];
                    }
                    break; // proceed to create new session
                }

                if ($stack_same) {
                    // Bring paused same-form session to active and reset it.
                    $stack_same->reset_to_step($first_step_id);
                    $stack_same->set_status('active');
                    if (!$this->session_repository->save($stack_same)) {
                        return [];
                    }
                    return $this->interaction_handler->presentStep($stack_same, $interaction_definition, $synthetic_event);
                }

                // Otherwise proceed to create new session.
                break;

            case 'stack':
            default:
                // Stack behaviour: if an active same-form session exists, do nothing.
                if ($activeSession && $same_form) {
                    return [];
                }

                // If active different session exists:
                if ($activeSession && !$same_form) {
                    // If stack already contains the same-form, activate
                    if ($stack_same) {
                        // Pause the current active session and activate the stacked one.
                        $activeSession->set_status('paused');
                        if (!$this->session_repository->save($activeSession)) {
                            return [];
                        }
                        $stack_same->set_status('active');
                        if (!$this->session_repository->save($stack_same)) {
                            return [];
                        }
                        return $this->interaction_handler->presentStep($stack_same, $interaction_definition, $synthetic_event);
                    }
                    // Pause the current active session and create a new one.
                    $activeSession->set_status('paused');
                    if (!$this->session_repository->save($activeSession)) {
                        return [];
                    }
                    break; // proceed to create new session
                }

                // No active session -> proceed to create new session.
                break;
        }

        // Create a new session (common path for allowed cases).
        $session = InteractionSession::start($interaction_definition, $line_user_id, $channel_prefix);
        $session->set_current_step_id($first_step_id);

        if (!$this->session_repository->save($session)) {
            return [];
        }

        return $this->interaction_handler->presentStep($session, $interaction_definition, $synthetic_event);
    }
}
