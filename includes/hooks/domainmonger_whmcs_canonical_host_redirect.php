<?php
/**
 * DomainMonger WHMCS canonical host redirect.
 *
 * Patch 1460
 *
 * Restores the confirmed Restore 557 architecture for production:
 * - The server-level www -> non-www redirect lives in manage/.htaccess.
 * - An early fallback in the WHMCS head catches cached/stale www pages.
 * - This late footer hook remains disabled so it cannot interfere with cart
 *   routes or rewrite only part of the integrated main menu.
 *
 * Patch 1459's incomplete same-host link normalizer is intentionally removed.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1460, function ($vars) {
    return '';
});
