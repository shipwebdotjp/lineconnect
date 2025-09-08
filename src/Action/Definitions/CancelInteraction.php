<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Interaction\SessionRepository;
use Shipweb\LineConnect\Interaction\InteractionManager;
use Shipweb\LineConnect\Interaction\InteractionHandler;
use Shipweb\LineConnect\Interaction\ActionRunner;
use Shipweb\LineConnect\Interaction\MessageBuilder;
use Shipweb\LineConnect\Interaction\InputNormalizer;
use Shipweb\LineConnect\Interaction\Validator;
use Shipweb\LineConnect\Interaction\RunPolicyEnforcer;
use Shipweb\LineConnect\Interaction\InteractionDefinition;

class CancelInteraction extends AbstractActionDefinition
{

    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string
    {
        return 'cancel_interaction';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array
    {
        return array(
            'title'       => __('Cancel Interaction', lineconnect::PLUGIN_NAME),
            'description' => __('Cancel LINE Connect Interaction.', lineconnect::PLUGIN_NAME),
            'parameters'  => array(
                array(
                    'type' => 'slc_interaction',
                    'name' => 'slc_interaction_id',
                    'description' => __('Interaction ID. If not specified, the current active interaction will be targeted.', lineconnect::PLUGIN_NAME),
                    'required' => false,
                ),
                array(
                    'type' => 'string',
                    'name' => 'cancelPolicy',
                    'description' => __('Cancel policy. force=delete immediately, confirm=show confirmation', lineconnect::PLUGIN_NAME),
                    'oneOf'       => array(
                        array('const' => 'force', 'title' => __('Force cancel', LineConnect::PLUGIN_NAME)),
                        array('const' => 'confirm', 'title' => __('Show confirmation', LineConnect::PLUGIN_NAME)),
                    ),
                ),
                array(
                    'type' => 'string',
                    'name' => 'line_user_id',
                    'description' => __('Line user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
                ),
                array(
                    'type' => 'slc_channel',
                    'name' => 'channel',
                    'description' => __('First 4 characters of channel secret. Default value is channel of event source.', lineconnect::PLUGIN_NAME),
                ),
            ),
            'namespace'   => self::class,
            'role'        => 'administrator',
        );
    }

    /**
     * インタラクションを中止する
     *
     * @param ?int $interaction_id インタラクションID
     * @param ?string $cancelPolicy キャンセルポリシー force=即削除, confirm=確認を表示
     * @param ?string $line_user_id LINEユーザーID
     * @param ?string $secret_prefix チャネルシークレットの先頭4文字
     * @return \LINE\LINEBot\MessageBuilder\MultiMessageBuilder|null
     */
    public function cancel_interaction(?int $interaction_id = null, ?string $cancelPolicy = 'force', ?string $line_user_id = null, ?string $secret_prefix = null): ?\LINE\LINEBot\MessageBuilder\MultiMessageBuilder
    {
        $line_user_id = $line_user_id ?? $this->event->source->userId;
        $secret_prefix = $secret_prefix ?? $this->secret_prefix;

        if (empty($line_user_id) || empty($secret_prefix)) {
            return null;
        }

        $session_repository = new SessionRepository();
        $session = $session_repository->find_active($secret_prefix, $line_user_id);

        if (!$session) {
            return null;
        }

        // If an interaction_id is specified, only cancel if it matches the active session.
        if ($interaction_id && $session->get_interaction_id() != $interaction_id) {
            return null;
        }

        $interaction_definition = InteractionDefinition::from_post(
            $session->get_interaction_id(),
            $session->get_interaction_version()
        );

        // If definition is not found, just delete the session without sending a message.
        if (!$interaction_definition) {
            $session_repository->delete($session);
            return null;
        }
        $session->set_interaction_definition($interaction_definition);

        $message_builder = new MessageBuilder();
        $messages = [];

        switch ($cancelPolicy) {
            case 'confirm':
                $cancel_step = $interaction_definition->get_special_step('cancelConfirm');
                if ($cancel_step) {
                    $messages[] = $message_builder->build($cancel_step, $session);
                }
                break;

            case 'force':
            default:
                $canceled_step = $interaction_definition->get_special_step('canceled');
                if ($canceled_step) {
                    $messages[] = $message_builder->build($canceled_step, $session);
                }
                $session_repository->delete($session);
                break;
        }

        if (empty($messages)) {
            return null;
        }

        $multimessage = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        foreach ($messages as $message_item) {
            if ($message_item) {
                $multimessage->add($message_item);
            }
        }

        return $multimessage->size() > 0 ? $multimessage : null;
    }
}
