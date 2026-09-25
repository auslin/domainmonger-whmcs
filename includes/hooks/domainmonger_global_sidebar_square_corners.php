<?php
/**
 * DomainMonger WHMCS global side-menu square-corner cleanup.
 *
 * Patch 431
 * - Component-level cleanup for WHMCS client-area side menus/sidebar list groups.
 * - Removes rounded corners from side menu panels, headers, and active/list items.
 * - Intended to catch sidebars such as Billing/My Invoices where the active item
 *   retained rounded Bootstrap corners after the global consistency pass.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 * - Does not touch templates, language files, integration files, or order-form logic.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1012, function ($vars) {
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
<style id="domainmonger-global-sidebar-square-corners">
/*
 * Side menus should be visually squared-off. This targets the WHMCS sidebar/menu
 * component only and avoids changing ordinary cards/tables/modals.
 */
body #main-body .sidebar .panel,
body #main-body .sidebar .panel-heading,
body #main-body .sidebar .panel-body,
body #main-body .sidebar .panel-footer,
body #main-body .sidebar .panel-collapse,
body #main-body .sidebar .collapse,
body #main-body .sidebar .list-group,
body #main-body .sidebar .list-group-item,
body #main-body .sidebar a.list-group-item,
body #main-body .sidebar button.list-group-item,
body #main-body .panel-sidebar,
body #main-body .panel-sidebar .panel,
body #main-body .panel-sidebar .panel-heading,
body #main-body .panel-sidebar .panel-body,
body #main-body .panel-sidebar .panel-footer,
body #main-body .panel-sidebar .panel-collapse,
body #main-body .panel-sidebar .collapse,
body #main-body .panel-sidebar .list-group,
body #main-body .panel-sidebar .list-group-item,
body #main-body .panel-sidebar a.list-group-item,
body #main-body .card-sidebar,
body #main-body .card-sidebar .card,
body #main-body .card-sidebar .card-header,
body #main-body .card-sidebar .card-body,
body #main-body .card-sidebar .card-footer,
body #main-body .card-sidebar .list-group,
body #main-body .card-sidebar .list-group-item,
body #main-body .card-sidebar a.list-group-item,
body #main-body .sidebar-primary .panel,
body #main-body .sidebar-primary .panel-heading,
body #main-body .sidebar-primary .list-group,
body #main-body .sidebar-primary .list-group-item,
body #main-body .sidebar-secondary .panel,
body #main-body .sidebar-secondary .panel-heading,
body #main-body .sidebar-secondary .list-group,
body #main-body .sidebar-secondary .list-group-item {
    border-radius: 0 !important;
}

/*
 * Bootstrap gives first/last list-group items their own corner radius. Override
 * those directly so active side-menu items like My Invoices do not show rounded ends.
 */
body #main-body .sidebar .list-group-item:first-child,
body #main-body .sidebar .list-group-item:last-child,
body #main-body .sidebar a.list-group-item:first-child,
body #main-body .sidebar a.list-group-item:last-child,
body #main-body .sidebar button.list-group-item:first-child,
body #main-body .sidebar button.list-group-item:last-child,
body #main-body .sidebar .panel > .list-group:first-child .list-group-item:first-child,
body #main-body .sidebar .panel > .list-group:last-child .list-group-item:last-child,
body #main-body .sidebar .card > .list-group:first-child .list-group-item:first-child,
body #main-body .sidebar .card > .list-group:last-child .list-group-item:last-child,
body #main-body .panel-sidebar .list-group-item:first-child,
body #main-body .panel-sidebar .list-group-item:last-child,
body #main-body .panel-sidebar a.list-group-item:first-child,
body #main-body .panel-sidebar a.list-group-item:last-child,
body #main-body .card-sidebar .list-group-item:first-child,
body #main-body .card-sidebar .list-group-item:last-child,
body #main-body .card-sidebar a.list-group-item:first-child,
body #main-body .card-sidebar a.list-group-item:last-child {
    border-top-left-radius: 0 !important;
    border-top-right-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

/* Active/current side-menu rows stay square even when another hook controls color. */
body #main-body .sidebar .list-group > .list-group-item.active,
body #main-body .sidebar .list-group > .list-group-item.active:hover,
body #main-body .sidebar .list-group > .list-group-item.active:focus,
body #main-body .panel-sidebar .list-group > .list-group-item.active,
body #main-body .panel-sidebar .list-group > .list-group-item.active:hover,
body #main-body .panel-sidebar .list-group > .list-group-item.active:focus,
body #main-body .card-sidebar .list-group > .list-group-item.active,
body #main-body .card-sidebar .list-group > .list-group-item.active:hover,
body #main-body .card-sidebar .list-group > .list-group-item.active:focus {
    border-radius: 0 !important;
}
</style>
HTML;
});
