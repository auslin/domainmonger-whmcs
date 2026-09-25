<?php
/**
 * DomainMonger WHMCS global card/table/header palette.
 *
 * Patch 411
 * - Component-level global cleanup for WHMCS client-area cards, panels, boxes, and table headers.
 * - Keeps the scope narrow: no broad typography pass, no template edits, no language edits.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1002, function ($vars) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // Keep the isolated WHMCS v9 support/testing route untouched.
    if (stripos($requestUri, 'dmv9support=1') !== false) {
        return '';
    }

    // Protect the custom v8x register/namespinner route.
    $isCart = stripos($scriptName, '/cart.php') !== false || stripos($requestUri, '/cart.php') !== false;
    $isDomainRegister = preg_match('/(?:\?|&)a=add(?:&|$)/i', $requestUri) === 1
        && preg_match('/(?:\?|&)domain=(register|r)(?:&|$)/i', $requestUri) === 1;

    if ($isCart && $isDomainRegister) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-global-card-table-headers">
:root {
    --dm-card-navy: #163a5f;
    --dm-card-navy-hover: #214e7a;
    --dm-card-border: #d8e0e8;
    --dm-card-border-soft: #e8edf2;
    --dm-card-body: #ffffff;
    --dm-card-row-hover: #fff3e8;
    --dm-card-radius: 6px;
}

/*
 * Global WHMCS component cleanup for boxes/cards/panels.
 * This intentionally avoids changing global body fonts, text sizes, templates,
 * menus, or the custom register page.
 */
body #main-body .card,
body #main-body .panel,
body #main-body .client-home-panels .panel,
body #main-body .client-home-panels .card,
body #main-body .card-sidebar,
body #main-body .list-group,
body #main-body .well {
    border-color: var(--dm-card-border) !important;
    border-radius: var(--dm-card-radius) !important;
    background-color: var(--dm-card-body) !important;
    box-shadow: none !important;
}

body #main-body .card-body,
body #main-body .panel-body,
body #main-body .list-group-item,
body #main-body .well {
    background-color: var(--dm-card-body) !important;
}

/* Navy component headers with white readable text. */
body #main-body .card > .card-header:not(.bg-warning):not(.alert-warning):not(.bg-danger):not(.alert-danger),
body #main-body .panel > .panel-heading:not(.bg-warning):not(.alert-warning):not(.bg-danger):not(.alert-danger),
body #main-body .panel-default > .panel-heading,
body #main-body .client-home-panels .panel > .panel-heading,
body #main-body .client-home-panels .card > .card-header,
body #main-body .sidebar .card > .card-header,
body #main-body .sidebar .panel > .panel-heading,
body #main-body .card-sidebar > .card-header {
    background: var(--dm-card-navy) !important;
    background-color: var(--dm-card-navy) !important;
    border-color: var(--dm-card-navy) !important;
    color: #ffffff !important;
    border-radius: var(--dm-card-radius) var(--dm-card-radius) 0 0 !important;
    font-weight: 650 !important;
    text-transform: none !important;
    letter-spacing: 0 !important;
}

body #main-body .card > .card-header:not(.bg-warning):not(.alert-warning):not(.bg-danger):not(.alert-danger) *,
body #main-body .panel > .panel-heading:not(.bg-warning):not(.alert-warning):not(.bg-danger):not(.alert-danger) *,
body #main-body .panel-default > .panel-heading *,
body #main-body .client-home-panels .panel > .panel-heading *,
body #main-body .client-home-panels .card > .card-header *,
body #main-body .sidebar .card > .card-header *,
body #main-body .sidebar .panel > .panel-heading *,
body #main-body .card-sidebar > .card-header * {
    color: #ffffff !important;
    font-weight: 650 !important;
    text-transform: none !important;
    text-decoration: none !important;
}

/* Table headers: navy background, white text, consistent weight. */
body #main-body .table thead th,
body #main-body .table thead td,
body #main-body table.table-list thead th,
body #main-body table.table-list thead td,
body #main-body table.dataTable thead th,
body #main-body table.dataTable thead td,
body #main-body .dataTables_wrapper table.table thead th,
body #main-body .dataTables_wrapper table.table thead td {
    background: var(--dm-card-navy) !important;
    background-color: var(--dm-card-navy) !important;
    border-color: var(--dm-card-navy) !important;
    color: #ffffff !important;
    font-weight: 650 !important;
    text-transform: none !important;
    vertical-align: middle !important;
}

body #main-body .table thead th *,
body #main-body .table thead td *,
body #main-body table.table-list thead th *,
body #main-body table.table-list thead td *,
body #main-body table.dataTable thead th *,
body #main-body table.dataTable thead td *,
body #main-body .dataTables_wrapper table.table thead th *,
body #main-body .dataTables_wrapper table.table thead td * {
    color: #ffffff !important;
    font-weight: 650 !important;
    text-transform: none !important;
    text-decoration: none !important;
}

/* Table body borders and hover are intentionally light and reusable. */
body #main-body .table tbody td,
body #main-body .table tbody th,
body #main-body table.table-list tbody td,
body #main-body table.table-list tbody th,
body #main-body table.dataTable tbody td,
body #main-body table.dataTable tbody th {
    border-color: var(--dm-card-border-soft) !important;
    vertical-align: middle !important;
}

body #main-body .table-hover tbody tr:hover,
body #main-body table.dataTable.hover tbody tr:hover,
body #main-body table.dataTable.display tbody tr:hover,
body #main-body table.table-list tbody tr:hover {
    background-color: var(--dm-card-row-hover) !important;
}

/* Keep sort/header icons readable on navy headers. */
body #main-body .table thead th:before,
body #main-body .table thead th:after,
body #main-body table.dataTable thead th:before,
body #main-body table.dataTable thead th:after,
body #main-body table.table-list thead th:before,
body #main-body table.table-list thead th:after {
    color: #ffffff !important;
    opacity: 0.9 !important;
}
</style>
HTML;
});
