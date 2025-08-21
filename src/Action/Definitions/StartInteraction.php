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

class StartInteraction extends AbstractActionDefinition {
	/**
	 * Returns the action key.
	 *
	 * @return string
	 */
	public static function name(): string {
		return 'start_interaction';
	}

	/**
	 * Returns the action configuration.
	 *
	 * @return array
	 */
	public static function config(): array {
		return array(
			'title'       => __('Start Interaction', lineconnect::PLUGIN_NAME),
			'description' => __('Start LINE Connect Interaction.', lineconnect::PLUGIN_NAME),
			'parameters'  => array(
				array(
					'type' => 'slc_interaction',
					'name' => 'slc_interaction_id',
					'description' => __('Interaction ID', lineconnect::PLUGIN_NAME),
					'required' => true,
				),
				array(
					'type' => 'string',
					'name' => 'overridePolicy',
					'description' => __('Interaction override policy', lineconnect::PLUGIN_NAME),
					'oneOf'       => array(
						array('const' => 'reject', 'title' => __('Do nothing if exists active session', LineConnect::PLUGIN_NAME)),
						array('const' => 'restart_same', 'title' => __('Restart only same interaction', LineConnect::PLUGIN_NAME)),
						array('const' => 'restart_diff', 'title' => __('Restart only different interaction', LineConnect::PLUGIN_NAME)),
						array('const' => 'restart_always', 'title' => __('Always restart interaction', LineConnect::PLUGIN_NAME)),
						array('const' => 'stack', 'title' => __('Stack interaction', LineConnect::PLUGIN_NAME)),
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
	 * インタラクションを開始する
	 * 
	 * @param int $interaction_id インタラクションID
	 * @param ?string $overridePolicy 上書きポリシー
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 成功・失敗
	 */
	public function start_interaction(int $interaction_id, ?string $overridePolicy = null, ?string $line_user_id = null, ?string $secret_prefix = null): array {
		$line_user_id = $line_user_id ?? $this->event->source->userId;
		$secret_prefix = $secret_prefix ?? $this->secret_prefix;

		if (empty($line_user_id) || empty($secret_prefix)) {
			return ['result' => 'error', 'message' => 'Invalid user ID or secret prefix'];
		}
		// if (is_null($overridePolicy)) {
		// 	$overridePolicy = null;
		// }

		$session_repository = new SessionRepository();
		$action_runner = new ActionRunner();
		$message_builder = new MessageBuilder();
		$normalizer = new InputNormalizer();
		$validator = new Validator();
		$run_policy_enforcer = new RunPolicyEnforcer($session_repository);
		$interaction_handler = new InteractionHandler(
			$session_repository,
			$action_runner,
			$message_builder,
			$normalizer,
			$validator,
			$run_policy_enforcer
		);
		$interaction_manager = new InteractionManager(
			$session_repository,
			$interaction_handler
		);

		// Stage 1: Start the interaction
		return $interaction_manager->startInteraction($interaction_id, $line_user_id, $secret_prefix, $overridePolicy);
	}
}
