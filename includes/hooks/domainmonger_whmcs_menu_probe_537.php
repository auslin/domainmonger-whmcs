<?php
/**
 * DomainMonger WHMCS menu probe cleanup.
 *
 * Patch 547
 * - Keeps Patch 537 diagnostic overlay neutralized.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 537, function ($vars) {
    return '';
});
