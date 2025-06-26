<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Scenario\Scenario;
/**
 * Definition for the get_my_user_info action.
 */
class ExecuteScenarioStep extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'execute_scenario_step';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Execute LC Scenario Step', lineconnect::PLUGIN_NAME),
				'description' => __('Execute LINE Connect Scenario Step.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_scenario',
						'name' => 'slc_scenario_id',
						'description' => __('Scenario ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'step_id',
						'description' => __('ID of the step to execute.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
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
	 * 指定したステップを実行
	 * @param ?int $scenario_id シナリオID
	 * @param ?string $step_id ステップID
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 成功・失敗
	 */
	public function execute_scenario_step(?int $scenario_id = null, ?string $step_id = null, ?string $line_user_id = null, ?string $secret_prefix = null): array {
		$line_user_id = $line_user_id ?? $this->event->source->userId;
		$secret_prefix = $secret_prefix ?? $this->secret_prefix;
		$scenario_id = $scenario_id ?? $this->scenario_id;

		if (empty($line_user_id) || empty($secret_prefix) || empty($scenario_id)) {
			return ['result' => 'error', 'message' => 'Invalid user ID or secret prefix'];
		}

		return Scenario::execute_step($scenario_id, $step_id, $line_user_id, $secret_prefix, wp_date('Y-m-d H:i:s'));
	}

}