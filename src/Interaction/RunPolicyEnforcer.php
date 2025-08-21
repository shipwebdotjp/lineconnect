<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Interaction\InteractionSession;

/**
 * Enforces runPolicy rules when a session completes.
 */
class RunPolicyEnforcer {
    private SessionRepository $session_repository;

    public function __construct(SessionRepository $session_repository) {
        $this->session_repository = $session_repository;
    }

    /**
     * Enforce runPolicy after a session is completed.
     *
     * @param InteractionSession $session
     * @param InteractionDefinition $definition
     * @return void
     */
    public function enforceOnComplete(InteractionSession $session, InteractionDefinition $definition): void {
        $policy = $definition->get_run_policy();

        switch ($policy) {
            case 'single_latest_only':
                $this->applySingleLatestOnly($session);
                break;

            case 'single_forbid':
                // start-time check is used for single_forbid; nothing to do on complete.
                break;

            case 'multi_keep_history':
            default:
                // No-op
                break;
        }
    }

    /**
     * Delete other sessions for the same interaction (keep only the completed one).
     *
     * @param InteractionSession $session
     */
    private function applySingleLatestOnly(InteractionSession $session): void {
        $channel_prefix = $session->get_channel_prefix();
        $line_user_id = $session->get_line_user_id();
        $interaction_id = $session->get_interaction_id();

        $sessions = $this->session_repository->find_sessions_by_interaction($channel_prefix, $line_user_id, $interaction_id);

        if (empty($sessions)) {
            return;
        }

        foreach ($sessions as $s) {
            // Skip the session we just completed (may compare by id)
            if ($s->get_id() === $session->get_id()) {
                continue;
            }
            // Attempt to delete other sessions; ignore failures but continue.
            $this->session_repository->delete($s);
        }
    }
}
