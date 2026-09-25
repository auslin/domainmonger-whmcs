<?php
/**
 * DomainMonger Patch 1651 cleanup stub.
 *
 * Patch 1650's handler is intentionally disabled. Patch 1651 uses a new hook
 * filename to avoid stale OPcache execution and contains the current safe
 * multi-destination Email Forwarding logic.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
