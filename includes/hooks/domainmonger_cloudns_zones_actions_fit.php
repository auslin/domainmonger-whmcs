<?php
/**
 * DomainMonger Patch 478
 * ClouDNS Zones List action-button fit override.
 *
 * Keeps this fix scoped to the client-area product-details view and to the
 * ClouDNS Zones List markup only. This avoids touching global tables/buttons
 * and avoids relying on the module template compile/cache state.
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, function (array $vars) {
    $filename = isset($vars['filename']) ? (string) $vars['filename'] : '';
    $action = isset($_GET['action']) ? (string) $_GET['action'] : '';

    if ($filename !== 'clientarea' || $action !== 'productdetails') {
        return '';
    }

    return <<<'HTML'
<style id="dm-cloudns-zones-actions-fit-478">
/* Patch 478: Pull ClouDNS Zones List action buttons farther inside the card. */
.cloudns-zones-panel,
.cloudns-zones-panel .table,
.cloudns-zones-panel #zones-list {
    overflow: visible !important;
}

.cloudns-zones-panel #zones-list {
    width: 100% !important;
    table-layout: fixed !important;
}

.cloudns-zones-panel #zones-list th:first-child,
.cloudns-zones-panel #zones-list td:first-child {
    width: auto !important;
    min-width: 0 !important;
    overflow-wrap: anywhere !important;
}

.cloudns-zones-panel #zones-list .zones-actions-header,
.cloudns-zones-panel #zones-list .zones-actions-cell {
    width: 270px !important;
    min-width: 270px !important;
    max-width: 270px !important;
    padding-right: 78px !important;
    padding-left: 8px !important;
    overflow: visible !important;
    white-space: nowrap !important;
    box-sizing: border-box !important;
    text-align: right !important;
}

.cloudns-zones-panel #zones-list .zones-actions-cell .zones-options {
    display: inline-flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 5px !important;
    width: 100% !important;
    min-width: 144px !important;
    max-width: none !important;
    margin-left: auto !important;
    transform: translateX(-18px) !important;
    overflow: visible !important;
    white-space: nowrap !important;
    box-sizing: border-box !important;
}

.cloudns-zones-panel #zones-list .zones-actions-cell .zones-options .btn,
.cloudns-zones-panel #zones-list .zones-actions-cell .zones-options .cloudns-btn-secondary,
.cloudns-zones-panel #zones-list .zones-actions-cell .zones-options .cloudns-btn-danger {
    flex: 0 0 auto !important;
    min-width: 66px !important;
    width: auto !important;
    padding-left: 8px !important;
    padding-right: 8px !important;
    box-sizing: border-box !important;
}

.cloudns-zones-panel #zones-list .zones-actions-header .cloudns-zones-actions-label {
    width: 66px !important;
    margin-left: auto !important;
    margin-right: 0 !important;
    text-align: center !important;
}

@media only screen and (max-width: 650px) {
    .cloudns-zones-panel #zones-list .zones-actions-header,
    .cloudns-zones-panel #zones-list .zones-actions-cell {
        width: 58px !important;
        min-width: 58px !important;
        max-width: 58px !important;
        padding-right: 8px !important;
    }
}
</style>
HTML;
});
