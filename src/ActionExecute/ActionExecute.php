<?php

/**
 * Lineconnect ActionExecute
 * 
 * @category Component
 * @package  Shipweb\LineConnect\ActionExecute
 */

namespace Shipweb\LineConnect\ActionExecute;

use Shipweb\LineConnect\Core\LineConnect;

class ActionExecute {
    /**
     * CredentialAction
     */
    const NAME = 'action-execute';

    /**
     * CredentialAction
     */
    const CREDENTIAL_ACTION = LineConnect::PLUGIN_ID . '-nonce-action_' . self::NAME;

    /**
     * CredentialName
     */
    const CREDENTIAL_NAME = LineConnect::PLUGIN_ID . '-nonce-name_' . self::NAME;

    /**
     * 画面のslug
     */
    const SLUG__FORM = LineConnect::PLUGIN_ID . self::NAME;
}
