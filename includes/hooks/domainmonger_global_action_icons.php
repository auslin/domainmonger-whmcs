<?php
/**
 * DomainMonger WHMCS global action/icon link consistency.
 *
 * Patch 422
 * - Component-level cleanup for small action links, icon-only links, and table/action toolbar icons.
 * - Keeps this separate from buttons (Patch 409), links (Patch 415), badges (Patch 413), and nav/menus (Patch 412/417).
 * - Avoids templates, language files, integration files, and order-form logic.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1015, function ($vars) {
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
<style id="domainmonger-global-action-icons">
:root {
    --dm-action-navy: #163a5f;
    --dm-action-navy-hover: #214e7a;
    --dm-action-orange: #f58220;
    --dm-action-orange-soft: #fff3e8;
    --dm-action-border: #d7dee6;
    --dm-action-text: #1f2933;
    --dm-action-muted: #6c757d;
    --dm-action-radius: 6px;
}

/* Icon/action links: remove default Bootstrap blue drift without touching buttons, badges, menus, or alerts. */
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link) > i,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link) > .fa,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link) > .fas,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link) > .far,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link) > .fal,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link) > .fab,
body #main-body .action-links a:not(.btn):not(.badge):not(.label) > i,
body #main-body .action-links a:not(.btn):not(.badge):not(.label) > .fa,
body #main-body .action-buttons a:not(.btn):not(.badge):not(.label) > i,
body #main-body .action-buttons a:not(.btn):not(.badge):not(.label) > .fa,
body #main-body .table-actions a:not(.btn):not(.badge):not(.label) > i,
body #main-body .table-actions a:not(.btn):not(.badge):not(.label) > .fa,
body #main-body .actions a:not(.btn):not(.badge):not(.label) > i,
body #main-body .actions a:not(.btn):not(.badge):not(.label) > .fa {
    color: var(--dm-action-navy) !important;
    text-decoration: none !important;
}

body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link):hover > i,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link):focus > i,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link):hover > .fa,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link):focus > .fa,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link):hover > .fas,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link):focus > .fas,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link):hover > .far,
body #main-body .table a:not(.btn):not(.badge):not(.label):not(.dropdown-item):not(.nav-link):focus > .far,
body #main-body .action-links a:not(.btn):not(.badge):not(.label):hover > i,
body #main-body .action-links a:not(.btn):not(.badge):not(.label):focus > i,
body #main-body .action-links a:not(.btn):not(.badge):not(.label):hover > .fa,
body #main-body .action-links a:not(.btn):not(.badge):not(.label):focus > .fa,
body #main-body .action-buttons a:not(.btn):not(.badge):not(.label):hover > i,
body #main-body .action-buttons a:not(.btn):not(.badge):not(.label):focus > i,
body #main-body .action-buttons a:not(.btn):not(.badge):not(.label):hover > .fa,
body #main-body .action-buttons a:not(.btn):not(.badge):not(.label):focus > .fa,
body #main-body .table-actions a:not(.btn):not(.badge):not(.label):hover > i,
body #main-body .table-actions a:not(.btn):not(.badge):not(.label):focus > i,
body #main-body .table-actions a:not(.btn):not(.badge):not(.label):hover > .fa,
body #main-body .table-actions a:not(.btn):not(.badge):not(.label):focus > .fa,
body #main-body .actions a:not(.btn):not(.badge):not(.label):hover > i,
body #main-body .actions a:not(.btn):not(.badge):not(.label):focus > i,
body #main-body .actions a:not(.btn):not(.badge):not(.label):hover > .fa,
body #main-body .actions a:not(.btn):not(.badge):not(.label):focus > .fa {
    color: var(--dm-action-orange) !important;
}

/* Icon-only utility links get a small, consistent hit area without becoming full buttons. */
body #main-body a.icon-link,
body #main-body a.icon-action,
body #main-body .icon-actions a:not(.btn),
body #main-body .table-actions a:not(.btn),
body #main-body .action-icons a:not(.btn) {
    align-items: center !important;
    border-radius: var(--dm-action-radius) !important;
    color: var(--dm-action-navy) !important;
    display: inline-flex !important;
    gap: 5px !important;
    line-height: 1.2 !important;
    min-height: 28px !important;
    padding: 4px 6px !important;
    text-decoration: none !important;
}

body #main-body a.icon-link:hover,
body #main-body a.icon-link:focus,
body #main-body a.icon-action:hover,
body #main-body a.icon-action:focus,
body #main-body .icon-actions a:not(.btn):hover,
body #main-body .icon-actions a:not(.btn):focus,
body #main-body .table-actions a:not(.btn):hover,
body #main-body .table-actions a:not(.btn):focus,
body #main-body .action-icons a:not(.btn):hover,
body #main-body .action-icons a:not(.btn):focus {
    background: var(--dm-action-orange-soft) !important;
    color: var(--dm-action-orange) !important;
    outline: none !important;
    text-decoration: none !important;
}

/* Preserve semantic icon colors for states that carry meaning. */
body #main-body .text-success,
body #main-body .text-success i,
body #main-body .text-success .fa,
body #main-body .fa-check-circle.text-success,
body #main-body .fa-check.text-success {
    color: #198754 !important;
}

body #main-body .text-danger,
body #main-body .text-danger i,
body #main-body .text-danger .fa,
body #main-body .fa-times-circle.text-danger,
body #main-body .fa-exclamation-triangle.text-danger {
    color: #b94a48 !important;
}

body #main-body .text-warning,
body #main-body .text-warning i,
body #main-body .text-warning .fa,
body #main-body .fa-exclamation-triangle.text-warning {
    color: #8a5a00 !important;
}

body #main-body .text-muted,
body #main-body .text-muted i,
body #main-body .text-muted .fa {
    color: var(--dm-action-muted) !important;
}
</style>
HTML;
});
