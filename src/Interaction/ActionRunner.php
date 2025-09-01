<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Action\Action;

/**
 * ActionRunner
 */
class ActionRunner {
    /**
     * Run actions
     *
     * @param object $action_definition
     * @param InteractionSession $session
     * @param object $event
     * @return array
     */
    public function run(object $action_definition, InteractionSession $session, object $event): array {
        $actions = $action_definition->actions ?? [];
        if (empty($actions)) {
            return [];
        }

        $chains = $action_definition->chains ?? null;
        $secret_prefix = $session->get_channel_prefix();

        $action_result = Action::do_action($actions, $chains, $event, $secret_prefix, null, $session);
        if (!empty($action_result['messages'])) {
            return $action_result['messages'];
        }

        return [];
    }
}
