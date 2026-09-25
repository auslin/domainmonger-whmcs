<?php
/**
 * DomainMonger patch 1034 cleanup.
 *
 * Disabled old DNS Modify popup/diagnostic experiments.
 * Unified DNS records now use the faster loader in the 1010/1020 hooks.
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}
// Intentionally no hooks.
