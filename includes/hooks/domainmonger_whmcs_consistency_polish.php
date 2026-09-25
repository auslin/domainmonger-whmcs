<?php
/**
 * DomainMonger WHMCS client-area consistency polish.
 *
 * Patch 349 changes the output hook to ClientAreaFooterOutput so this
 * CSS loads after the rebuilt header.tpl style block. That lets the
 * table-header cleanup win without editing header.tpl.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 20, function ($vars) {
    return <<<'HTML'
<style id="dm-whmcs-consistency-polish-349">
body.whmcsbody {
    --dm-orange: #f58220;
    --dm-orange-hover: #dd711b;
    --dm-navy: #163a5f;
    --dm-navy-hover: #214e7a;
    --dm-red: #b94a48;
    --dm-text: #13283b;
    --dm-muted: #687789;
    --dm-border: #d9e1ea;
    --dm-border-soft: #e7edf3;
    --dm-soft-orange: #fff3e7;
}

/* Typography and page headings. */
body.whmcsbody #main-body {
    color: var(--dm-text);
    font-size: 14px;
    line-height: 1.55;
}

body.whmcsbody #main-body h1,
body.whmcsbody #main-body h2,
body.whmcsbody #main-body h3,
body.whmcsbody #main-body h4,
body.whmcsbody #main-body h5,
body.whmcsbody #main-body h6 {
    color: var(--dm-navy);
    font-weight: 700;
    letter-spacing: 0;
    line-height: 1.25;
}

body.whmcsbody #main-body .header-lined {
    margin: 0 0 24px;
    padding: 0;
    border: 0;
}

body.whmcsbody #main-body .header-lined h1 {
    margin: 0;
    font-size: 26px;
    font-weight: 700;
    color: var(--dm-navy);
}

/* Labels and helper text. */
body.whmcsbody #main-body label,
body.whmcsbody #main-body .control-label {
    color: var(--dm-text);
    font-weight: 700;
    margin-bottom: 7px;
}

body.whmcsbody #main-body .help-block,
body.whmcsbody #main-body .small,
body.whmcsbody #main-body small {
    color: var(--dm-muted);
}

/* Cards/panels: consistent padding, radius, and text weight. */
body.whmcsbody #main-body .panel,
body.whmcsbody #main-body .card {
    border-radius: 5px;
}

body.whmcsbody #main-body .panel-heading,
body.whmcsbody #main-body .card-header {
    min-height: 42px;
    display: flex;
    align-items: center;
}

body.whmcsbody #main-body .panel-heading .panel-title,
body.whmcsbody #main-body .card-header .card-title,
body.whmcsbody #main-body .panel-heading h1,
body.whmcsbody #main-body .panel-heading h2,
body.whmcsbody #main-body .panel-heading h3,
body.whmcsbody #main-body .panel-heading h4 {
    width: 100%;
    margin: 0;
    line-height: 1.25;
    font-weight: 700;
}

body.whmcsbody #main-body .panel-body,
body.whmcsbody #main-body .card-body {
    padding: 22px 24px;
}

/* Forms: consistent heights and spacing. */
body.whmcsbody #main-body .form-group {
    margin-bottom: 18px;
}

body.whmcsbody #main-body .form-control,
body.whmcsbody #main-body select.form-control,
body.whmcsbody #main-body input[type="text"],
body.whmcsbody #main-body input[type="email"],
body.whmcsbody #main-body input[type="password"],
body.whmcsbody #main-body input[type="tel"],
body.whmcsbody #main-body input[type="number"] {
    min-height: 38px;
    height: auto;
    padding: 8px 11px;
    font-size: 14px;
    line-height: 1.4;
}

body.whmcsbody #main-body textarea.form-control {
    min-height: 120px;
}

/* Buttons: consistent weight, height, padding, and vertical centering. */
body.whmcsbody #main-body .btn {
    min-height: 38px;
    padding: 8px 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.25;
    border-radius: 4px;
    text-shadow: none !important;
    box-shadow: none;
}

body.whmcsbody #main-body .btn-sm,
body.whmcsbody #main-body .btn-xs {
    min-height: 32px;
    padding: 6px 11px;
    font-size: 13px;
}

body.whmcsbody #main-body .btn-lg {
    min-height: 44px;
    padding: 10px 20px;
    font-size: 15px;
}

body.whmcsbody #main-body .btn i,
body.whmcsbody #main-body .btn .fa,
body.whmcsbody #main-body .btn .fas,
body.whmcsbody #main-body .btn .far {
    line-height: 1;
}

/* Keep button groups from looking jammed. */
body.whmcsbody #main-body .btn + .btn,
body.whmcsbody #main-body .btn-group + .btn,
body.whmcsbody #main-body .btn + .btn-group {
    margin-left: 8px;
}

/* Tables and DataTables controls. */
body.whmcsbody #main-body table.table th,
body.whmcsbody #main-body table.table-list th,
body.whmcsbody #main-body table.datatable th {
    font-size: 13px;
    font-weight: 700;
    line-height: 1.3;
    padding: 11px 12px;
}

body.whmcsbody #main-body table.table td,
body.whmcsbody #main-body table.table-list td,
body.whmcsbody #main-body table.datatable td {
    padding: 11px 12px;
}

body.whmcsbody #main-body .dataTables_wrapper .dataTables_filter label,
body.whmcsbody #main-body .dataTables_wrapper .dataTables_length label {
    font-weight: 700;
    color: var(--dm-text);
}

body.whmcsbody #main-body .dataTables_wrapper .dataTables_filter input,
body.whmcsbody #main-body .dataTables_wrapper .dataTables_length select {
    margin-left: 8px;
}

/* DataTables sortable header cleanup: force a single navy header background. */
body.whmcsbody #main-body table.table thead tr th,
body.whmcsbody #main-body table.table-list thead tr th,
body.whmcsbody #main-body table.datatable thead tr th,
body.whmcsbody #main-body table.dataTable thead tr th,
body.whmcsbody #main-body .dataTables_wrapper table thead tr th,
body.whmcsbody #main-body .dataTables_wrapper table.table-list thead tr th,
body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead tr th,
body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead .sorting,
body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead .sorting_asc,
body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead .sorting_desc,
body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead .sorting_asc_disabled,
body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead .sorting_desc_disabled {
    background: var(--dm-navy) !important;
    background-color: var(--dm-navy) !important;
    background-image: none !important;
    color: #fff !important;
    box-shadow: none !important;
}

body.whmcsbody #main-body table.table thead tr th *,
body.whmcsbody #main-body table.table-list thead tr th *,
body.whmcsbody #main-body table.datatable thead tr th *,
body.whmcsbody #main-body table.dataTable thead tr th *,
body.whmcsbody #main-body .dataTables_wrapper table thead tr th * {
    background-color: transparent !important;
    color: #fff !important;
}

body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead th:before,
body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead th:after,
body.whmcsbody #main-body .dataTables_wrapper table.table-list thead th:before,
body.whmcsbody #main-body .dataTables_wrapper table.table-list thead th:after {
    color: #dbe7f2 !important;
    opacity: .85 !important;
}

body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead th:hover,
body.whmcsbody #main-body .dataTables_wrapper table.dataTable thead th:focus,
body.whmcsbody #main-body .dataTables_wrapper table.table-list thead th:hover,
body.whmcsbody #main-body .dataTables_wrapper table.table-list thead th:focus {
    background: var(--dm-navy-hover) !important;
    background-color: var(--dm-navy-hover) !important;
    background-image: none !important;
    color: #fff !important;
    outline: none !important;
}

/* Badges/labels: consistent weight and readable sizing. */
body.whmcsbody #main-body .badge,
body.whmcsbody #main-body .label {
    font-weight: 700;
    line-height: 1.2;
    border-radius: 4px;
    padding: 5px 8px;
    text-shadow: none;
}

/* Sidebars: consistent row height, text weight, and icon spacing. */
body.whmcsbody #main-body .sidebar .panel-heading,
body.whmcsbody #main-body .sidebar .card-header {
    min-height: 38px;
    padding: 9px 13px;
}

body.whmcsbody #main-body .sidebar .list-group-item,
body.whmcsbody #main-body .sidebar .panel-body a,
body.whmcsbody #main-body .sidebar .card-body a,
body.whmcsbody #main-body .sidebar .nav > li > a {
    min-height: 32px;
    padding-top: 8px;
    padding-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.25;
}

body.whmcsbody #main-body .sidebar i,
body.whmcsbody #main-body .sidebar .fa,
body.whmcsbody #main-body .sidebar .fas,
body.whmcsbody #main-body .sidebar .far {
    min-width: 16px;
    text-align: center;
    margin-right: 5px;
}

/* Order form consistency without moving sidebars. */
body.whmcsbody #main-body #order-standard_cart .panel-heading,
body.whmcsbody #main-body #order-standard_cart .card-header {
    justify-content: center;
    text-align: center;
}

body.whmcsbody #main-body #order-standard_cart .panel-heading .panel-title,
body.whmcsbody #main-body #order-standard_cart .card-header .card-title {
    text-align: center;
}

body.whmcsbody #main-body #order-standard_cart .panel-footer {
    padding: 18px 20px 22px;
}

body.whmcsbody #main-body #order-standard_cart .products .product,
body.whmcsbody #main-body #order-standard_cart .product-info,
body.whmcsbody #main-body #order-standard_cart .domain-selection-options,
body.whmcsbody #main-body #order-standard_cart .summary-container {
    border-radius: 5px;
}

/* Alerts: consistent spacing, but keep WHMCS warning yellow. */
body.whmcsbody #main-body .alert {
    border-radius: 4px;
    padding: 12px 16px;
    line-height: 1.45;
}

body.whmcsbody #main-body .alert:empty,
body.whmcsbody #main-body .alert.hidden {
    display: none !important;
}

/* Mobile stacking cleanup. */
@media (max-width: 767px) {
    body.whmcsbody #main-body .panel-body,
    body.whmcsbody #main-body .card-body {
        padding: 18px 16px;
    }

    body.whmcsbody #main-body .btn {
        white-space: normal;
    }

    body.whmcsbody #main-body .btn + .btn,
    body.whmcsbody #main-body .btn-group + .btn,
    body.whmcsbody #main-body .btn + .btn-group {
        margin-left: 0;
        margin-top: 8px;
    }
}
</style>
HTML;
});
