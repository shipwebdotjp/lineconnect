<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_my_user_info action.
 */
class SendMailToAdmin extends AbstractActionDefinition {
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string {
        return 'send_mail_to_admin';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array {
        return array(
				'title'       => __('Send mail to admin', lineconnect::PLUGIN_NAME),
				'description' => __('Send mail to admin.', lineconnect::PLUGIN_NAME),
				'parameters'  => array(
					array(
						'type' => 'string',
						'name' => 'subject',
						'description' => __('Mail subject', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
					array(
						'type' => 'string',
						'name' => 'body',
						'description' => __('Mail body', lineconnect::PLUGIN_NAME),
						'required' => true,
					),
				),
				'namespace'   => self::class,
				'role'        => 'any',
			);
    }

	public function send_mail_to_admin($subject, $body) {
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: LINECONNECT <' . get_option('admin_email') . '>',
		);
		return wp_mail(get_option('admin_email'), $subject, $body, $headers);
	}
}