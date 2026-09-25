<?php
/**
 * DomainMonger WHMCS global table utility controls consistency.
 *
 * Patch 420
 * - Component-level cleanup for table/data-table utility rows and controls.
 * - Standardizes "Show entries", search/filter wrappers, table info text, and table action toolbars.
 * - Leaves table header styling to Patch 411 and pagination styling to Patch 417/418.
 * - Avoids templates, language files, integration files, and order-form logic.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1014, function ($vars) {
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
<style id="domainmonger-global-table-utility-controls">
:root {
    --dm-table-util-navy: #163a5f;
    --dm-table-util-navy-hover: #214e7a;
    --dm-table-util-orange: #f58220;
    --dm-table-util-orange-soft: #fff3e8;
    --dm-table-util-border: #d7dee6;
    --dm-table-util-bg: #f7f9fb;
    --dm-table-util-text: #1f2933;
    --dm-table-util-muted: #6c757d;
    --dm-table-util-radius: 8px;
}

/* DataTables top/bottom utility rows: consistent spacing and text color. */
body #main-body .dataTables_wrapper .row:first-child,
body #main-body .dataTables_wrapper .row:last-child,
body #main-body .table-container > .row:first-child,
body #main-body .table-container > .row:last-child,
body #main-body .table-wrapper > .row:first-child,
body #main-body .table-wrapper > .row:last-child {
    color: var(--dm-table-util-text) !important;
}

body #main-body .dataTables_wrapper .dataTables_length,
body #main-body .dataTables_wrapper .dataTables_filter,
body #main-body .dataTables_wrapper .dataTables_info,
body #main-body .dataTables_wrapper .dataTables_processing,
body #main-body .dataTables_wrapper .dataTables_paginate,
body #main-body .dataTables_length,
body #main-body .dataTables_filter,
body #main-body .dataTables_info {
    color: var(--dm-table-util-text) !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.45 !important;
}

body #main-body .dataTables_wrapper label,
body #main-body .dataTables_wrapper .dataTables_length label,
body #main-body .dataTables_wrapper .dataTables_filter label {
    color: var(--dm-table-util-text) !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    margin-bottom: 8px !important;
}

/* Keep DataTables search/select controls aligned with the global forms/dropdowns pass. */
body #main-body .dataTables_wrapper .dataTables_length select,
body #main-body .dataTables_wrapper .dataTables_filter input,
body #main-body .dataTables_length select,
body #main-body .dataTables_filter input {
    border: 1px solid var(--dm-table-util-border) !important;
    border-radius: 6px !important;
    color: var(--dm-table-util-text) !important;
    background-color: #ffffff !important;
    min-height: 34px !important;
    box-shadow: none !important;
}

body #main-body .dataTables_wrapper .dataTables_filter input,
body #main-body .dataTables_filter input {
    margin-left: 8px !important;
    padding: 6px 10px !important;
}

body #main-body .dataTables_wrapper .dataTables_length select,
body #main-body .dataTables_length select {
    margin: 0 6px !important;
    padding: 5px 26px 5px 9px !important;
}

body #main-body .dataTables_wrapper .dataTables_length select:focus,
body #main-body .dataTables_wrapper .dataTables_filter input:focus,
body #main-body .dataTables_length select:focus,
body #main-body .dataTables_filter input:focus {
    border-color: var(--dm-table-util-orange) !important;
    box-shadow: 0 0 0 0.15rem rgba(245, 130, 32, 0.18) !important;
    outline: 0 !important;
}

/* Table info text should be readable but secondary. */
body #main-body .dataTables_wrapper .dataTables_info,
body #main-body .dataTables_info,
body #main-body .table-info-text,
body #main-body .results-info,
body #main-body .pagination-info,
body #main-body .showing-records,
body #main-body .table-records-info {
    color: var(--dm-table-util-muted) !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    padding-top: 8px !important;
}

/* Toolbars above/below tables: light shell, not random gray blocks. */
body #main-body .table-toolbar,
body #main-body .datatable-toolbar,
body #main-body .table-actions,
body #main-body .list-table-actions,
body #main-body .table-controls,
body #main-body .dataTables_wrapper .table-toolbar,
body #main-body .dataTables_wrapper .datatable-toolbar {
    background: var(--dm-table-util-bg) !important;
    border: 1px solid var(--dm-table-util-border) !important;
    border-radius: var(--dm-table-util-radius) !important;
    color: var(--dm-table-util-text) !important;
    padding: 10px 12px !important;
}

/* Compact table-adjacent helper text. */
body #main-body .table-caption,
body #main-body .table-help,
body #main-body .table-helper,
body #main-body .dataTables_wrapper small,
body #main-body .table small,
body #main-body .table .small {
    color: var(--dm-table-util-muted) !important;
    font-size: 12px !important;
    font-weight: 400 !important;
}

/* DataTables processing state: match alert/card direction without layout changes. */
body #main-body .dataTables_wrapper .dataTables_processing {
    background: #ffffff !important;
    border: 1px solid var(--dm-table-util-border) !important;
    border-radius: var(--dm-table-util-radius) !important;
    box-shadow: 0 8px 22px rgba(22, 58, 95, 0.14) !important;
    color: var(--dm-table-util-navy) !important;
    font-weight: 600 !important;
    padding: 10px 14px !important;
}

/* Avoid the global link palette turning table utility links/buttons into orange text-only controls. */
body #main-body .dataTables_wrapper .dataTables_paginate a,
body #main-body .dataTables_wrapper .dataTables_paginate .paginate_button,
body #main-body .table-toolbar a:not(.btn),
body #main-body .datatable-toolbar a:not(.btn),
body #main-body .table-actions a:not(.btn) {
    text-decoration: none !important;
}
</style>
HTML;
});
