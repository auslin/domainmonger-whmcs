<?php
/**
 * DomainMonger WHMCS auth poll endpoint cleanup.
 *
 * Patch 556
 * - Disables Patch 554 hash diagnostic endpoint.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaPage', 554, function ($vars) {
    return [];
});
