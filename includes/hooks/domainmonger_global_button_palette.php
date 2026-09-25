<?php
/**
 * DomainMonger WHMCS global button component palette.
 *
 * Patch 409
 * - Component-level global cleanup for grey/default WHMCS client-area buttons.
 * - Converts secondary/default/light/info action buttons to the DomainMonger navy palette.
 * - Leaves primary, danger, warning, success, disabled, and link buttons alone.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 * - Does not touch templates, language files, integration files, or order-form logic.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1000, function ($vars) {
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
<style id="domainmonger-global-button-palette">
:root {
    --dm-btn-navy: #163a5f;
    --dm-btn-navy-hover: #214e7a;
    --dm-btn-radius: 6px;
}

/*
 * Global WHMCS client-area button cleanup.
 * This is intentionally component-scoped: it only changes grey/default-style
 * action buttons, not card layout, typography, tables, menus, templates, or the
 * custom register page.
 */
body #main-body a.btn-default:not(.disabled):not([disabled]),
body #main-body button.btn-default:not(.disabled):not([disabled]),
body #main-body input.btn-default:not(.disabled):not([disabled]),
body #main-body .btn.btn-default:not(.disabled):not([disabled]),
body #main-body a.btn-secondary:not(.disabled):not([disabled]),
body #main-body button.btn-secondary:not(.disabled):not([disabled]),
body #main-body input.btn-secondary:not(.disabled):not([disabled]),
body #main-body .btn.btn-secondary:not(.disabled):not([disabled]),
body #main-body a.btn-light:not(.disabled):not([disabled]),
body #main-body button.btn-light:not(.disabled):not([disabled]),
body #main-body input.btn-light:not(.disabled):not([disabled]),
body #main-body .btn.btn-light:not(.disabled):not([disabled]),
body #main-body a.btn-info:not(.disabled):not([disabled]),
body #main-body button.btn-info:not(.disabled):not([disabled]),
body #main-body input.btn-info:not(.disabled):not([disabled]),
body #main-body .btn.btn-info:not(.disabled):not([disabled]) {
    background: var(--dm-btn-navy) !important;
    background-color: var(--dm-btn-navy) !important;
    border-color: var(--dm-btn-navy) !important;
    color: #fff !important;
    border-radius: var(--dm-btn-radius) !important;
    box-shadow: none !important;
    text-decoration: none !important;
    font-weight: 650 !important;
    text-transform: none !important;
}

body #main-body a.btn-default:not(.disabled):not([disabled]):hover,
body #main-body a.btn-default:not(.disabled):not([disabled]):focus,
body #main-body button.btn-default:not(.disabled):not([disabled]):hover,
body #main-body button.btn-default:not(.disabled):not([disabled]):focus,
body #main-body input.btn-default:not(.disabled):not([disabled]):hover,
body #main-body input.btn-default:not(.disabled):not([disabled]):focus,
body #main-body .btn.btn-default:not(.disabled):not([disabled]):hover,
body #main-body .btn.btn-default:not(.disabled):not([disabled]):focus,
body #main-body a.btn-secondary:not(.disabled):not([disabled]):hover,
body #main-body a.btn-secondary:not(.disabled):not([disabled]):focus,
body #main-body button.btn-secondary:not(.disabled):not([disabled]):hover,
body #main-body button.btn-secondary:not(.disabled):not([disabled]):focus,
body #main-body input.btn-secondary:not(.disabled):not([disabled]):hover,
body #main-body input.btn-secondary:not(.disabled):not([disabled]):focus,
body #main-body .btn.btn-secondary:not(.disabled):not([disabled]):hover,
body #main-body .btn.btn-secondary:not(.disabled):not([disabled]):focus,
body #main-body a.btn-light:not(.disabled):not([disabled]):hover,
body #main-body a.btn-light:not(.disabled):not([disabled]):focus,
body #main-body button.btn-light:not(.disabled):not([disabled]):hover,
body #main-body button.btn-light:not(.disabled):not([disabled]):focus,
body #main-body input.btn-light:not(.disabled):not([disabled]):hover,
body #main-body input.btn-light:not(.disabled):not([disabled]):focus,
body #main-body .btn.btn-light:not(.disabled):not([disabled]):hover,
body #main-body .btn.btn-light:not(.disabled):not([disabled]):focus,
body #main-body a.btn-info:not(.disabled):not([disabled]):hover,
body #main-body a.btn-info:not(.disabled):not([disabled]):focus,
body #main-body button.btn-info:not(.disabled):not([disabled]):hover,
body #main-body button.btn-info:not(.disabled):not([disabled]):focus,
body #main-body input.btn-info:not(.disabled):not([disabled]):hover,
body #main-body input.btn-info:not(.disabled):not([disabled]):focus,
body #main-body .btn.btn-info:not(.disabled):not([disabled]):hover,
body #main-body .btn.btn-info:not(.disabled):not([disabled]):focus {
    background: var(--dm-btn-navy-hover) !important;
    background-color: var(--dm-btn-navy-hover) !important;
    border-color: var(--dm-btn-navy-hover) !important;
    color: #fff !important;
    text-decoration: none !important;
}

/* Keep icon/text color consistent inside converted secondary buttons. */
body #main-body .btn-default:not(.disabled):not([disabled]) i,
body #main-body .btn-default:not(.disabled):not([disabled]) .fas,
body #main-body .btn-default:not(.disabled):not([disabled]) .far,
body #main-body .btn-default:not(.disabled):not([disabled]) .fa,
body #main-body .btn-secondary:not(.disabled):not([disabled]) i,
body #main-body .btn-secondary:not(.disabled):not([disabled]) .fas,
body #main-body .btn-secondary:not(.disabled):not([disabled]) .far,
body #main-body .btn-secondary:not(.disabled):not([disabled]) .fa,
body #main-body .btn-light:not(.disabled):not([disabled]) i,
body #main-body .btn-light:not(.disabled):not([disabled]) .fas,
body #main-body .btn-light:not(.disabled):not([disabled]) .far,
body #main-body .btn-light:not(.disabled):not([disabled]) .fa,
body #main-body .btn-info:not(.disabled):not([disabled]) i,
body #main-body .btn-info:not(.disabled):not([disabled]) .fas,
body #main-body .btn-info:not(.disabled):not([disabled]) .far,
body #main-body .btn-info:not(.disabled):not([disabled]) .fa {
    color: #fff !important;
}
</style>
HTML;
});
