<?php
/**
 * DomainMonger WHMCS menu live repair cleanup.
 *
 * Patch 547
 * - Keeps failed Patch 536 live submenu repair neutralized.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 536, function ($vars) {
    return '';
});
