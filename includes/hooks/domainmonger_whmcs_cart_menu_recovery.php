<?php
/**
 * DomainMonger cleanup for failed/no-change Patch 467 cart menu recovery.
 *
 * Patch 470 keeps this recovery hook neutralized while reverting the Patch 469
 * submenu template change that broke the site.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Intentionally no-op.
