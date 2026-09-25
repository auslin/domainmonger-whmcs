<?php
/**
 * DomainMonger WHMCS global side-menu hover correction.
 *
 * Patch 412
 * - Component-level global cleanup for WHMCS client-area side menu/list-group hover states.
 * - Fixes side menu hover changing text to orange by making hover use a pale orange background
 *   with navy/dark text instead.
 * - Keeps active/current side menu items navy with white text.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 * - Does not touch templates, language files, integration files, or order-form logic.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1010, function ($vars) {
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
<style id="domainmonger-global-side-menu-hover">
:root {
    --dm-side-menu-navy: #163a5f;
    --dm-side-menu-navy-hover: #214e7a;
    --dm-side-menu-hover-bg: #fff3e8;
    --dm-side-menu-border: #d8e0e8;
}

/*
 * Side-menu hover behavior.
 * The menu should indicate hover by changing the background, not by turning text orange.
 * Kept intentionally scoped to sidebar/list-menu components, not all links or all list groups.
 */
body #main-body .sidebar .list-group > a.list-group-item:not(.active):not(.disabled):hover,
body #main-body .sidebar .list-group > a.list-group-item:not(.active):not(.disabled):focus,
body #main-body .sidebar .panel > .list-group > a.list-group-item:not(.active):not(.disabled):hover,
body #main-body .sidebar .panel > .list-group > a.list-group-item:not(.active):not(.disabled):focus,
body #main-body .sidebar .card > .list-group > a.list-group-item:not(.active):not(.disabled):hover,
body #main-body .sidebar .card > .list-group > a.list-group-item:not(.active):not(.disabled):focus,
body #main-body .panel-sidebar .list-group > a.list-group-item:not(.active):not(.disabled):hover,
body #main-body .panel-sidebar .list-group > a.list-group-item:not(.active):not(.disabled):focus,
body #main-body .card-sidebar .list-group > a.list-group-item:not(.active):not(.disabled):hover,
body #main-body .card-sidebar .list-group > a.list-group-item:not(.active):not(.disabled):focus {
    background: var(--dm-side-menu-hover-bg) !important;
    background-color: var(--dm-side-menu-hover-bg) !important;
    border-color: var(--dm-side-menu-border) !important;
    color: var(--dm-side-menu-navy) !important;
    text-decoration: none !important;
}

body #main-body .sidebar .list-group > a.list-group-item:not(.active):not(.disabled):hover *,
body #main-body .sidebar .list-group > a.list-group-item:not(.active):not(.disabled):focus *,
body #main-body .sidebar .panel > .list-group > a.list-group-item:not(.active):not(.disabled):hover *,
body #main-body .sidebar .panel > .list-group > a.list-group-item:not(.active):not(.disabled):focus *,
body #main-body .sidebar .card > .list-group > a.list-group-item:not(.active):not(.disabled):hover *,
body #main-body .sidebar .card > .list-group > a.list-group-item:not(.active):not(.disabled):focus *,
body #main-body .panel-sidebar .list-group > a.list-group-item:not(.active):not(.disabled):hover *,
body #main-body .panel-sidebar .list-group > a.list-group-item:not(.active):not(.disabled):focus *,
body #main-body .card-sidebar .list-group > a.list-group-item:not(.active):not(.disabled):hover *,
body #main-body .card-sidebar .list-group > a.list-group-item:not(.active):not(.disabled):focus * {
    color: var(--dm-side-menu-navy) !important;
    text-decoration: none !important;
}

/* Active/current side-menu item remains navy with white text. */
body #main-body .sidebar .list-group > a.list-group-item.active,
body #main-body .sidebar .list-group > a.list-group-item.active:hover,
body #main-body .sidebar .list-group > a.list-group-item.active:focus,
body #main-body .panel-sidebar .list-group > a.list-group-item.active,
body #main-body .panel-sidebar .list-group > a.list-group-item.active:hover,
body #main-body .panel-sidebar .list-group > a.list-group-item.active:focus,
body #main-body .card-sidebar .list-group > a.list-group-item.active,
body #main-body .card-sidebar .list-group > a.list-group-item.active:hover,
body #main-body .card-sidebar .list-group > a.list-group-item.active:focus {
    background: var(--dm-side-menu-navy) !important;
    background-color: var(--dm-side-menu-navy) !important;
    border-color: var(--dm-side-menu-navy) !important;
    color: #ffffff !important;
    text-decoration: none !important;
}

body #main-body .sidebar .list-group > a.list-group-item.active *,
body #main-body .sidebar .list-group > a.list-group-item.active:hover *,
body #main-body .sidebar .list-group > a.list-group-item.active:focus *,
body #main-body .panel-sidebar .list-group > a.list-group-item.active *,
body #main-body .panel-sidebar .list-group > a.list-group-item.active:hover *,
body #main-body .panel-sidebar .list-group > a.list-group-item.active:focus *,
body #main-body .card-sidebar .list-group > a.list-group-item.active *,
body #main-body .card-sidebar .list-group > a.list-group-item.active:hover *,
body #main-body .card-sidebar .list-group > a.list-group-item.active:focus * {
    color: #ffffff !important;
    text-decoration: none !important;
}
</style>
HTML;
});
