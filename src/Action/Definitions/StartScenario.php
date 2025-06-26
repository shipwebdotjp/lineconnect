<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Scenario\Scenario;
/**
 * Definition for the get_my_user_info action.
 */
class StartScenario extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'start_scenario';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Start LC Scenario', lineconnect::PLUGIN_NAME),
				'description' => __('Start LINE Connect Scenario.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_scenario',
						'name' => 'slc_scenario_id',
						'description' => __('Scenario ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'flg',
						'description' => __('Scenario restart flag', lineconnect::PLUGIN_NAME),
						'oneOf'       => array(
							array(
								'const' => 'none',
								'title' => __('Never restart', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'completed',
								'title' => __('Restart only completed', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'always',
								'title' => __('Always restart', lineconnect::PLUGIN_NAME),
							),
						),
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('Line user ID. Default value is LINE user ID of event source.', lineconnect::PLUGIN_NAME),
						'required' => true,
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
	 * シナリオを開始する
	 * 
	 * @param int $scenario_id シナリオID
	 * @param ?string $flg フラグ
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return array 成功・失敗
	 */
	public function start_scenario(int $scenario_id, ?string $flg = null, ?string $line_user_id = null, ?string $secret_prefix = null): array {
		$line_user_id = $line_user_id ?? $this->event->source->userId;
		$secret_prefix = $secret_prefix ?? $this->secret_prefix;

		if (empty($line_user_id) || empty($secret_prefix)) {
			return ['result' => 'error', 'message' => 'Invalid user ID or secret prefix'];
		}
		if (is_null($flg)) {
			$flg = Scenario::START_FLAG_RESTART_NONE;
		}
		return Scenario::start_scenario($scenario_id, $flg, $line_user_id, $secret_prefix);
	}
}