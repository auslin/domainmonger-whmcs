<?php
/**
 * DomainMonger WHMCS Menu Status Diagnostic cleanup.
 *
 * Patch 556
 * - Disables Patch 551/552 diagnostic overlay.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 551, function ($vars) {
    return '';
});
