<?php
/**
 * DomainMonger WHMCS Menu DOM Diagnostic cleanup.
 *
 * Patch 556
 * - Disables Patch 552 browser-side diagnostic overlay.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 552, function ($vars) {
    return '';
});
