<?php
/**
 * DomainMonger cleanup for failed/no-change Patch 468 menu diagnostic.
 *
 * Patch 470 keeps this diagnostic hook neutralized while reverting the Patch 469
 * submenu template change that broke the site.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally no-op.
