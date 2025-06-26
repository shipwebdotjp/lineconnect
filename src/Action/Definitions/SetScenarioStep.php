<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Scenario\Scenario;
/**
 * Definition for the get_my_user_info action.
 */
class SetScenarioStep extends AbstractActionDefinition {

	// ovverride set_scenario_id
	public function set_scenario_id(int $scenario_id): void {
		// error_log("set_scenario_id: " . $scenario_id. "type: " . gettype($scenario_id));
		$this->scenario_id = $scenario_id;
	}

    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'set_scenario_step';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Set LC Scenario Step', lineconnect::PLUGIN_NAME),
				'description' => __('Set LINE Connect Scenario Step.', lineconnect::PLUGIN_NAME),
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
						'description' => __('ID of the step to set.', lineconnect::PLUGIN_NAME),
					),
					array(
						'type' => 'string',
						'name' => 'next_date',
						'description' => __('Next date to execute the step. Absolute or relative.', lineconnect::PLUGIN_NAME),
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
	 * 指定したステップへ移動
	 * 
	 * @param ?int $scenario_id シナリオID
	 * @param ?string $step_id ステップID
	 * @param ?string $next_date 次回実行日時
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 成功・失敗
	 */
	function set_scenario_step(?int $scenario_id = null, ?string $step_id = null, ?string $next_date = null, ?string $line_user_id = null, ?string $secret_prefix = null): array {
		$line_user_id = $line_user_id ?? $this->event->source->userId;
		$secret_prefix = $secret_prefix ?? $this->secret_prefix;
		$scenario_id = $scenario_id ?? $this->scenario_id;

		if (empty($line_user_id) || empty($secret_prefix) || empty($scenario_id)) {
			return ['result' => 'error', 'message' => 'Invalid user ID or secret prefix'];
		}

		return Scenario::set_scenario_step($scenario_id, $step_id, $next_date, $line_user_id, $secret_prefix);
	}
}