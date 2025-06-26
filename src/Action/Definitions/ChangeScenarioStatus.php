<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Scenario\Scenario;
/**
 * Definition for the get_my_user_info action.
 */
class ChangeScenarioStatus extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'change_scenario_status';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Change LC scenario status', lineconnect::PLUGIN_NAME),
				'description' => __('Change LINE Connect scenario status.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_scenario',
						'name' => 'slc_scenario_id',
						'description' => __('Scenario ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'status',
						'description' => __('The status to set.', lineconnect::PLUGIN_NAME),
						'oneOf'	   => array(
							array(
								'const' => 'active',
								'title' => __('Active', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'error',
								'title' => __('Error', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'paused',
								'title' => __('Paused', lineconnect::PLUGIN_NAME),
							),
							array(
								'const' => 'completed',
								'title' => __('Completed', lineconnect::PLUGIN_NAME),
							),
						),
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
	 * シナリオの状態を変更する
	 * 
	 * @param int $scenario_id シナリオID
	 * @param string $status 新しい状態
	 * @param ?string $line_user_id LINEユーザーID
	 * @param ?string $secret_prefix チャネルシークレットの先頭4文字
	 * @return bool 成功・失敗
	 */
	public function change_scenario_status(int $scenario_id, string $status, ?string $line_user_id = null, ?string $secret_prefix = null): bool {
		$line_user_id = $line_user_id ?? $this->event->source->userId;
		$secret_prefix = $secret_prefix ?? $this->secret_prefix;
		if (empty($line_user_id) || empty($secret_prefix)) {
			return false;
		}
		return Scenario::update_scenario_status($scenario_id, $status, $line_user_id, $secret_prefix);
	}
}