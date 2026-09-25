<?php
/**
 * DomainMonger WHMCS global link palette.
 *
 * Patch 418 update to Patch 415
 * - Keeps normal content links dark and hover/focus orange.
 * - Removes pagination from the normal-link palette so Patch 417/418 pagination rules own pagination states.
 * - Avoids buttons, dropdowns, side menus, badges, card/table headers, and pagination controls.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1006, function ($vars) {
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
<style id="domainmonger-global-link-palette">
:root {
    --dm-link-dark: #24384f;
    --dm-link-navy: #163a5f;
    --dm-link-orange: #f58220;
    --dm-link-orange-soft: rgba(245, 130, 32, 0.16);
}

/*
 * Normal content links: dark by default, orange on hover/focus.
 * Pagination is intentionally excluded; pagination states are controlled by
 * domainmonger_global_pagination_tabs_nav.php.
 */
body #main-body .card-body a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link),
body #main-body .panel-body a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link),
body #main-body .well a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link),
body #main-body .alert a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link),
body #main-body .table tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link),
body #main-body table.table-list tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link),
body #main-body table.dataTable tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link),
body #main-body .dataTables_wrapper a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link),
body #main-body .breadcrumb a:not(.btn):not(.badge):not(.label):not(.dropdown-item),
body #main-body .main-content a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.list-group-item):not(.page-link),
body #main-body .contentarea a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.list-group-item):not(.page-link) {
    color: var(--dm-link-dark) !important;
    text-decoration: none !important;
    text-underline-offset: 2px;
}

body #main-body .card-body a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):hover,
body #main-body .card-body a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus,
body #main-body .panel-body a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):hover,
body #main-body .panel-body a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus,
body #main-body .well a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):hover,
body #main-body .well a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus,
body #main-body .alert a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):hover,
body #main-body .alert a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus,
body #main-body .table tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):hover,
body #main-body .table tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus,
body #main-body table.table-list tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):hover,
body #main-body table.table-list tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus,
body #main-body table.dataTable tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):hover,
body #main-body table.dataTable tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus,
body #main-body .dataTables_wrapper a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):hover,
body #main-body .dataTables_wrapper a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus,
body #main-body .breadcrumb a:not(.btn):not(.badge):not(.label):not(.dropdown-item):hover,
body #main-body .breadcrumb a:not(.btn):not(.badge):not(.label):not(.dropdown-item):focus,
body #main-body .main-content a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.list-group-item):not(.page-link):hover,
body #main-body .main-content a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.list-group-item):not(.page-link):focus,
body #main-body .contentarea a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.list-group-item):not(.page-link):hover,
body #main-body .contentarea a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.list-group-item):not(.page-link):focus {
    color: var(--dm-link-orange) !important;
    text-decoration: none !important;
    outline: 0 !important;
}

/* Keyboard focus should be visible but subtle and brand-aligned. */
body #main-body .card-body a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus-visible,
body #main-body .panel-body a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus-visible,
body #main-body .well a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus-visible,
body #main-body .alert a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus-visible,
body #main-body .table tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus-visible,
body #main-body table.table-list tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus-visible,
body #main-body table.dataTable tbody a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.page-link):focus-visible,
body #main-body .breadcrumb a:not(.btn):not(.badge):not(.label):not(.dropdown-item):focus-visible,
body #main-body .main-content a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.list-group-item):not(.page-link):focus-visible,
body #main-body .contentarea a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.list-group-item):not(.page-link):focus-visible {
    box-shadow: 0 0 0 0.14rem var(--dm-link-orange-soft) !important;
    border-radius: 3px;
}

/* Keep table/header/sidebar links readable after the normal-link cleanup. */
body #main-body .card-header a:not(.btn),
body #main-body .panel-heading a:not(.btn),
body #main-body .table thead a:not(.btn),
body #main-body table.table-list thead a:not(.btn),
body #main-body table.dataTable thead a:not(.btn),
body #main-body .sidebar a.list-group-item,
body #main-body .list-group a.list-group-item {
    text-decoration: none !important;
}
</style>
HTML;
});
