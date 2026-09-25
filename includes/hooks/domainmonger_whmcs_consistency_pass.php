<?php
/**
 * DomainMonger WHMCS consistency pass.
 *
 * Patch 405
 * - Small global client-area visual cleanup.
 * - Uses CSS only; no template rewrites.
 * - Skips the custom register domain route to protect the v8x namespinner work.
 * - Skips the isolated WHMCS v9 support/testing route when dmv9support=1 is present.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // Keep the isolated WHMCS v9 support/testing route untouched.
    if (stripos($requestUri, 'dmv9support=1') !== false) {
        return '';
    }

    // Protect the custom v8x namespinner register page and its shorthand route.
    $isCart = stripos($scriptName, '/cart.php') !== false || stripos($requestUri, '/cart.php') !== false;
    $isRegisterRoute = preg_match('/(?:\?|&)domain=(?:register|r)(?:&|$)/i', $requestUri) === 1;
    if ($isCart && $isRegisterRoute) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-whmcs-consistency-pass">
:root {
    --dm-brand-orange: #f58220;
    --dm-brand-orange-soft: #d8741f;
    --dm-brand-navy: #163a5f;
    --dm-brand-navy-hover: #214e7a;
    --dm-brand-red: #b94a48;
    --dm-brand-text: #2f3945;
    --dm-brand-muted: #6f7d8b;
    --dm-border: #d8e0e8;
    --dm-border-soft: #e8edf2;
    --dm-body-soft: #f7f9fb;
    --dm-pale-orange: #fff3e8;
    --dm-warning-bg: #fff8e1;
    --dm-warning-border: #f1d88c;
    --dm-radius: 6px;
}

/* Typography and readable text weight. */
#main-body,
.main-content,
.primary-content,
.clientarea,
body {
    color: var(--dm-brand-text);
}

#main-body p,
#main-body li,
#main-body td,
#main-body .form-control,
#main-body .input-group-text,
#main-body .alert,
#main-body .list-group-item {
    font-size: 14px;
    line-height: 1.55;
}

#main-body h1,
#main-body .h1,
#main-body .header-lined h1 {
    color: var(--dm-brand-navy);
    font-size: 26px;
    font-weight: 650;
    line-height: 1.25;
    letter-spacing: 0;
    text-transform: none;
}

#main-body h2,
#main-body .h2 {
    color: var(--dm-brand-navy);
    font-size: 22px;
    font-weight: 650;
    line-height: 1.3;
    text-transform: none;
}

#main-body h3,
#main-body .h3,
#main-body h4,
#main-body .h4 {
    color: var(--dm-brand-navy);
    font-weight: 650;
    text-transform: none;
}

#main-body .text-muted,
#main-body small,
#main-body .small {
    color: var(--dm-brand-muted) !important;
}

/* Cards, boxes, wells, panels, and modal surfaces. */
#main-body .card,
#main-body .panel,
#main-body .well,
#main-body .modal-content,
#main-body .domain-pricing,
#main-body .domain-checker-container,
#main-body .tiles .tile,
#main-body .client-home-panels .panel,
#main-body .client-home-panels .card {
    border-color: var(--dm-border) !important;
    border-radius: var(--dm-radius) !important;
    background: #fff;
    box-shadow: none;
}

#main-body .card-body,
#main-body .panel-body,
#main-body .well,
#main-body .modal-body,
#main-body .tile,
#main-body .list-group-item {
    background-color: #fff;
}

/* Box/table/sidebar headers: navy background with white text. */
#main-body .card > .card-header,
#main-body .panel > .panel-heading,
#main-body .panel-default > .panel-heading,
#main-body .client-home-panels .panel > .panel-heading,
#main-body .client-home-panels .card > .card-header,
#main-body .sidebar .card > .card-header,
#main-body .sidebar .panel > .panel-heading,
#main-body .card-sidebar > .card-header,
#main-body .modal-header {
    background: var(--dm-brand-navy) !important;
    border-color: var(--dm-brand-navy) !important;
    color: #fff !important;
    border-radius: var(--dm-radius) var(--dm-radius) 0 0 !important;
    font-size: 14px;
    font-weight: 650;
    line-height: 1.35;
    text-transform: none;
}

#main-body .card > .card-header *,
#main-body .panel > .panel-heading *,
#main-body .panel-default > .panel-heading *,
#main-body .client-home-panels .panel > .panel-heading *,
#main-body .client-home-panels .card > .card-header *,
#main-body .sidebar .card > .card-header *,
#main-body .sidebar .panel > .panel-heading *,
#main-body .card-sidebar > .card-header *,
#main-body .modal-header * {
    color: #fff !important;
    font-weight: 650;
    text-transform: none;
}

/* Tables and DataTables: consistent headers, borders, and spacing. */
#main-body table.table {
    border-color: var(--dm-border) !important;
    color: var(--dm-brand-text);
}

#main-body .table thead th,
#main-body table.dataTable thead th,
#main-body table.dataTable thead td,
#main-body .dataTables_wrapper table.table thead th {
    background: var(--dm-brand-navy) !important;
    color: #fff !important;
    border-color: var(--dm-brand-navy) !important;
    font-size: 13px;
    font-weight: 650;
    line-height: 1.35;
    vertical-align: middle;
    text-transform: none;
}

#main-body .table tbody td,
#main-body .table tbody th,
#main-body table.dataTable tbody td,
#main-body table.dataTable tbody th {
    border-color: var(--dm-border-soft) !important;
    vertical-align: middle;
    font-size: 13.5px;
}

#main-body .table-striped tbody tr:nth-of-type(odd),
#main-body table.dataTable.stripe tbody tr.odd,
#main-body table.dataTable.display tbody tr.odd {
    background-color: #fbfcfd;
}

#main-body .table-hover tbody tr:hover,
#main-body table.dataTable.hover tbody tr:hover,
#main-body table.dataTable.display tbody tr:hover {
    background-color: var(--dm-pale-orange) !important;
}

/* Buttons: brand palette, consistent height/weight/corners. */
#main-body .btn,
#main-body button,
#main-body input[type="submit"],
#main-body input[type="button"] {
    border-radius: var(--dm-radius) !important;
    font-size: 14px;
    font-weight: 650;
    line-height: 1.35;
    text-transform: none;
    box-shadow: none !important;
    text-decoration: none !important;
}

#main-body .btn-primary,
#main-body button.btn-primary,
#main-body input.btn-primary,
#main-body .btn-success,
#main-body button.btn-success,
#main-body input.btn-success {
    background-color: var(--dm-brand-orange) !important;
    border-color: var(--dm-brand-orange) !important;
    color: #fff !important;
}

#main-body .btn-primary:hover,
#main-body .btn-primary:focus,
#main-body .btn-primary:active,
#main-body .btn-success:hover,
#main-body .btn-success:focus,
#main-body .btn-success:active {
    background-color: var(--dm-brand-navy-hover) !important;
    border-color: var(--dm-brand-navy-hover) !important;
    color: #fff !important;
}

#main-body .btn-default,
#main-body .btn-secondary,
#main-body .btn-info,
#main-body .btn-outline-primary,
#main-body .btn-outline-secondary {
    background-color: var(--dm-brand-navy) !important;
    border-color: var(--dm-brand-navy) !important;
    color: #fff !important;
}

#main-body .btn-default:hover,
#main-body .btn-default:focus,
#main-body .btn-secondary:hover,
#main-body .btn-secondary:focus,
#main-body .btn-info:hover,
#main-body .btn-info:focus,
#main-body .btn-outline-primary:hover,
#main-body .btn-outline-primary:focus,
#main-body .btn-outline-secondary:hover,
#main-body .btn-outline-secondary:focus {
    background-color: var(--dm-brand-navy-hover) !important;
    border-color: var(--dm-brand-navy-hover) !important;
    color: #fff !important;
}

#main-body .btn-danger,
#main-body button.btn-danger,
#main-body input.btn-danger {
    background-color: var(--dm-brand-red) !important;
    border-color: var(--dm-brand-red) !important;
    color: #fff !important;
}

#main-body .btn-danger:hover,
#main-body .btn-danger:focus,
#main-body .btn-danger:active {
    background-color: #9f3735 !important;
    border-color: #9f3735 !important;
    color: #fff !important;
}

#main-body .btn-link {
    color: var(--dm-brand-text) !important;
    background: transparent !important;
    border-color: transparent !important;
    font-weight: 600;
    text-decoration: none !important;
}

#main-body .btn-link:hover,
#main-body .btn-link:focus {
    color: var(--dm-brand-orange) !important;
    text-decoration: none !important;
}

/* Links: dark by default, orange on hover/focus. */
#main-body a:not(.btn),
#main-body .breadcrumb a,
#main-body .list-group-item a {
    color: var(--dm-brand-text);
    text-decoration: none;
}

#main-body a:not(.btn):hover,
#main-body a:not(.btn):focus,
#main-body .breadcrumb a:hover,
#main-body .breadcrumb a:focus,
#main-body .list-group-item a:hover,
#main-body .list-group-item a:focus {
    color: var(--dm-brand-orange);
    text-decoration: none;
}

/* Forms and inputs. */
#main-body .form-control,
#main-body input[type="text"],
#main-body input[type="email"],
#main-body input[type="password"],
#main-body input[type="number"],
#main-body input[type="search"],
#main-body select,
#main-body textarea,
#main-body .input-group-text,
#main-body .input-group-addon {
    border-color: var(--dm-border) !important;
    border-radius: var(--dm-radius) !important;
    color: var(--dm-brand-text);
    box-shadow: none !important;
}

#main-body .form-control:focus,
#main-body input[type="text"]:focus,
#main-body input[type="email"]:focus,
#main-body input[type="password"]:focus,
#main-body input[type="number"]:focus,
#main-body input[type="search"]:focus,
#main-body select:focus,
#main-body textarea:focus {
    border-color: var(--dm-brand-orange-soft) !important;
    box-shadow: 0 0 0 .14rem rgba(216, 116, 31, .18) !important;
}

#main-body label,
#main-body .control-label,
#main-body .form-label {
    color: var(--dm-brand-text);
    font-weight: 600;
    text-transform: none;
}

/* Dropdowns and menus. */
#main-body .dropdown-menu {
    background-color: #fff !important;
    border-color: var(--dm-border) !important;
    border-radius: var(--dm-radius) !important;
    box-shadow: 0 8px 22px rgba(22, 58, 95, .12) !important;
    padding: 6px 0;
}

#main-body .dropdown-menu > li > a,
#main-body .dropdown-item,
#main-body .dropdown-menu a {
    color: var(--dm-brand-navy) !important;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none !important;
    text-transform: none;
}

#main-body .dropdown-menu > li > a:hover,
#main-body .dropdown-menu > li > a:focus,
#main-body .dropdown-item:hover,
#main-body .dropdown-item:focus,
#main-body .dropdown-menu a:hover,
#main-body .dropdown-menu a:focus {
    background-color: var(--dm-pale-orange) !important;
    color: var(--dm-brand-orange) !important;
    text-decoration: none !important;
}

/* Sidebar/list active states: navy/orange polish without underlines. */
#main-body .sidebar .list-group-item,
#main-body .card-sidebar .list-group-item,
#main-body .panel-sidebar .list-group-item {
    border-color: var(--dm-border-soft) !important;
    color: var(--dm-brand-text);
    text-decoration: none !important;
}

#main-body .sidebar .list-group-item:hover,
#main-body .card-sidebar .list-group-item:hover,
#main-body .panel-sidebar .list-group-item:hover {
    background-color: var(--dm-pale-orange) !important;
    color: var(--dm-brand-orange) !important;
    text-decoration: none !important;
}

#main-body .sidebar .list-group-item.active,
#main-body .card-sidebar .list-group-item.active,
#main-body .panel-sidebar .list-group-item.active,
#main-body .list-group-item.active {
    background-color: var(--dm-brand-navy) !important;
    border-color: var(--dm-brand-navy) !important;
    color: #fff !important;
}

#main-body .sidebar .list-group-item.active *,
#main-body .card-sidebar .list-group-item.active *,
#main-body .panel-sidebar .list-group-item.active *,
#main-body .list-group-item.active * {
    color: #fff !important;
}

/* Alerts and badges. */
#main-body .alert {
    border-radius: var(--dm-radius) !important;
    font-weight: 500;
}

#main-body .alert-warning {
    background-color: var(--dm-warning-bg) !important;
    border-color: var(--dm-warning-border) !important;
    color: #5f4a15 !important;
}

#main-body .badge,
#main-body .label {
    border-radius: 999px !important;
    font-weight: 650;
    letter-spacing: 0;
    text-transform: none;
}

#main-body .badge-primary,
#main-body .label-primary {
    background-color: var(--dm-brand-navy) !important;
    color: #fff !important;
}

#main-body .badge-info,
#main-body .label-info {
    background-color: var(--dm-brand-navy-hover) !important;
    color: #fff !important;
}

#main-body .badge-danger,
#main-body .label-danger {
    background-color: var(--dm-brand-red) !important;
    color: #fff !important;
}

#main-body .badge-warning,
#main-body .label-warning {
    background-color: #f5df9b !important;
    color: #5f4a15 !important;
}

/* Closed ticket/status labels should read as muted gray, not bright status colors. */
#main-body .status-closed,
#main-body .ticket-status-closed,
#main-body .badge-closed,
#main-body .label-closed,
#main-body .label.status-closed,
#main-body .badge.status-closed {
    background-color: #e1e5ea !important;
    border-color: #cbd3dc !important;
    color: #5b6773 !important;
}

/* Preserve green only for special promo/sale/success badges, not core controls. */
#main-body .badge-success,
#main-body .label-success,
#main-body .promo-badge,
#main-body .sale-badge,
#main-body .tld-sale,
#main-body .domain-sale,
#main-body .label-sale,
#main-body .badge-sale {
    color: #fff !important;
}

/* Pagination and compact controls. */
#main-body .pagination > li > a,
#main-body .pagination > li > span,
#main-body .page-link {
    color: var(--dm-brand-navy) !important;
    border-color: var(--dm-border) !important;
    text-decoration: none !important;
}

#main-body .pagination > .active > a,
#main-body .pagination > .active > span,
#main-body .page-item.active .page-link {
    background-color: var(--dm-brand-orange) !important;
    border-color: var(--dm-brand-orange) !important;
    color: #fff !important;
}

#main-body .pagination > li > a:hover,
#main-body .pagination > li > span:hover,
#main-body .page-link:hover {
    background-color: var(--dm-pale-orange) !important;
    color: var(--dm-brand-orange) !important;
}
</style>
HTML;
});
