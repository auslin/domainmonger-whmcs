<?php
/**
 * DomainMonger WHMCS global alerts and notification box palette.
 *
 * Patch 416
 * - Component-level global cleanup for WHMCS client-area alerts/notifications.
 * - Keeps warning alerts as pale WHMCS yellow and preserves meaningful success/danger/info states.
 * - Avoids broad typography, layout, templates, language files, integration files, and order-form logic.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1007, function ($vars) {
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
<style id="domainmonger-global-alerts-notifications">
:root {
    --dm-alert-navy: #163a5f;
    --dm-alert-navy-border: #12304f;
    --dm-alert-navy-soft: #e8f0f7;
    --dm-alert-orange: #f58220;
    --dm-alert-warning-bg: #fff3cd;
    --dm-alert-warning-border: #f0d98c;
    --dm-alert-warning-text: #604200;
    --dm-alert-danger: #b94a48;
    --dm-alert-danger-bg: #f8e6e5;
    --dm-alert-danger-border: #ddb4b2;
    --dm-alert-success: #2f7d4f;
    --dm-alert-success-bg: #e7f3ec;
    --dm-alert-success-border: #b8d8c5;
    --dm-alert-muted-bg: #f4f6f8;
    --dm-alert-muted-border: #d7dee6;
    --dm-alert-muted-text: #425466;
    --dm-alert-radius: 6px;
}

/* Shared alert shape/padding cleanup. Keeps WHMCS layout but removes random Bootstrap palette drift. */
body #main-body .alert,
body #main-body .bootstrap-switch-alert,
body #main-body .system-alert,
body #main-body .client-notification,
body #main-body .notification,
body #main-body .promo-banner:not(.domain-promo),
body #main-body .marketing-email-optin {
    border-radius: var(--dm-alert-radius) !important;
    border-width: 1px !important;
    box-shadow: none !important;
    font-weight: 400 !important;
    text-transform: none !important;
}

/* WHMCS warning/yellow notices should stay pale yellow and readable. */
body #main-body .alert-warning,
body #main-body .alert.alert-warning,
body #main-body .warning,
body #main-body .alert-warning-light {
    background-color: var(--dm-alert-warning-bg) !important;
    border-color: var(--dm-alert-warning-border) !important;
    color: var(--dm-alert-warning-text) !important;
}

body #main-body .alert-warning .alert-link,
body #main-body .alert-warning a:not(.btn) {
    color: #4d3500 !important;
    font-weight: 650 !important;
}

body #main-body .alert-warning a:not(.btn):hover,
body #main-body .alert-warning a:not(.btn):focus {
    color: var(--dm-alert-orange) !important;
}

/* Informational notices should use a light navy treatment, not default bright blue. */
body #main-body .alert-info,
body #main-body .alert.alert-info,
body #main-body .alert-primary,
body #main-body .alert.alert-primary,
body #main-body .info-box,
body #main-body .info-message {
    background-color: var(--dm-alert-navy-soft) !important;
    border-color: #b9cad9 !important;
    color: var(--dm-alert-navy) !important;
}

body #main-body .alert-info .alert-link,
body #main-body .alert-primary .alert-link,
body #main-body .alert-info a:not(.btn),
body #main-body .alert-primary a:not(.btn) {
    color: var(--dm-alert-navy) !important;
    font-weight: 650 !important;
}

body #main-body .alert-info a:not(.btn):hover,
body #main-body .alert-primary a:not(.btn):hover,
body #main-body .alert-info a:not(.btn):focus,
body #main-body .alert-primary a:not(.btn):focus {
    color: var(--dm-alert-orange) !important;
}

/* Error/danger notices keep the approved red. */
body #main-body .alert-danger,
body #main-body .alert.alert-danger,
body #main-body .alert-error,
body #main-body .errorbox,
body #main-body .error-box {
    background-color: var(--dm-alert-danger-bg) !important;
    border-color: var(--dm-alert-danger-border) !important;
    color: #7d2e2c !important;
}

body #main-body .alert-danger .alert-link,
body #main-body .alert-danger a:not(.btn),
body #main-body .alert-error a:not(.btn),
body #main-body .errorbox a:not(.btn) {
    color: #7d2e2c !important;
    font-weight: 650 !important;
}

body #main-body .alert-danger a:not(.btn):hover,
body #main-body .alert-danger a:not(.btn):focus,
body #main-body .alert-error a:not(.btn):hover,
body #main-body .alert-error a:not(.btn):focus,
body #main-body .errorbox a:not(.btn):hover,
body #main-body .errorbox a:not(.btn):focus {
    color: var(--dm-alert-danger) !important;
}

/* Success notices remain green because they represent confirmed positive state. */
body #main-body .alert-success,
body #main-body .alert.alert-success,
body #main-body .successbox,
body #main-body .success-box {
    background-color: var(--dm-alert-success-bg) !important;
    border-color: var(--dm-alert-success-border) !important;
    color: #245f3c !important;
}

body #main-body .alert-success .alert-link,
body #main-body .alert-success a:not(.btn),
body #main-body .successbox a:not(.btn) {
    color: #245f3c !important;
    font-weight: 650 !important;
}

body #main-body .alert-success a:not(.btn):hover,
body #main-body .alert-success a:not(.btn):focus,
body #main-body .successbox a:not(.btn):hover,
body #main-body .successbox a:not(.btn):focus {
    color: var(--dm-alert-orange) !important;
}

/* Neutral/secondary notices should be calm light gray with readable text. */
body #main-body .alert-secondary,
body #main-body .alert-light,
body #main-body .alert-default,
body #main-body .alert.alert-secondary,
body #main-body .alert.alert-light,
body #main-body .alert.alert-default {
    background-color: var(--dm-alert-muted-bg) !important;
    border-color: var(--dm-alert-muted-border) !important;
    color: var(--dm-alert-muted-text) !important;
}

/* Alert close buttons should stay visible but not introduce Bootstrap blue/black emphasis. */
body #main-body .alert .close,
body #main-body .alert button.close {
    color: inherit !important;
    opacity: 0.7 !important;
    text-shadow: none !important;
}

body #main-body .alert .close:hover,
body #main-body .alert .close:focus,
body #main-body .alert button.close:hover,
body #main-body .alert button.close:focus {
    opacity: 1 !important;
    color: var(--dm-alert-orange) !important;
    outline: none !important;
}
</style>
HTML;
});
