<?php
/**
 * DomainMonger WHMCS auth-poll menu repair cleanup.
 *
 * Patch 547
 * - Neutralizes the failed auth-poll repair/diagnostic chain.
 * - Root cause was confirmed as www vs non-www WHMCS session split.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 538, function ($vars) {
    return '';
});
