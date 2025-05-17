<?php

/**
 * @deprecated 4.2.0 Use \Shipweb\LineConnect\Utilities\StreamConnector instead.
 */

_deprecated_file(__FILE__, '4.2.0', 'src/Utilities/StreamConnector.php');

require_once __DIR__ . '/../src/Utilities/StreamConnector.php';

use Shipweb\LineConnect\Utilities\StreamConnector as BaseStreamConnector;

/**
 * Backward compatibility stub for logging connector.
 */
class lineconnectConnector extends BaseStreamConnector {
}
