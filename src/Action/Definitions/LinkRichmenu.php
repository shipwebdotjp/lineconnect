<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class LinkRichmenu extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'link_richmenu';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Link rich menu', lineconnect::PLUGIN_NAME),
				'description' => __('Link rich menu with line user ID and Channel.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'slc_richmenu',
						'name' => 'richmenu',
						'description' => __('Rich menu ID', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'line_user_id',
						'description' => __('LINE user id. Default value is LINE user id of event source.', lineconnect::PLUGIN_NAME),
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
	 * ユーザーのリッチメニューを設定する
	 * @param string $richmenu_id リッチメニューID
	 * @param string $line_user_id LINEユーザーID
	 * @param string $secret_prefix チャネルID
	 * @return array LINE APIのレスポンス
	 */
	public function link_richmenu($richmenu_id, $line_user_id = null, $secret_prefix = null) {
		if ( !isset($secret_prefix) ) {
			$secret_prefix = $this->secret_prefix;
		}
		if ( empty($secret_prefix) ) {
			return array(
				'success' => false,
				'message' => "<h2>" . __('Error: Secret prefix is not set.', lineconnect::PLUGIN_NAME),
			);
		}
		$channel = lineconnect::get_channel($secret_prefix);
		$line_user_id = $line_user_id ? $line_user_id : $this->event->source->userId;
		if ($channel) {
			
			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel['channel-access-token']);
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel['channel-secret']]);

			$response = $bot->linkRichMenu($line_user_id, $richmenu_id);
			return $response;
		}
		return null;
	}
}