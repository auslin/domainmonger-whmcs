<?php
/**
 * DomainMonger WHMCS global modal/popup consistency.
 *
 * Patch 419
 * - Component-level cleanup for WHMCS client-area modals only.
 * - Navy modal headers with white text, white bodies, light footer, soft borders/corners.
 * - Keeps buttons owned by the global button palette and danger/success/warning states meaningful.
 * - Avoids templates, language files, integration files, and order-form logic.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1013, function ($vars) {
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
<style id="domainmonger-global-modals-popups">
:root {
    --dm-modal-navy: #163a5f;
    --dm-modal-navy-hover: #214e7a;
    --dm-modal-orange: #f58220;
    --dm-modal-border: #d7dee6;
    --dm-modal-footer: #f7f9fb;
    --dm-modal-text: #1f2933;
    --dm-modal-muted: #6c757d;
    --dm-modal-radius: 8px;
}

/* Modal shell: clean card shape without changing layout or sizing. */
body #main-body .modal-content,
body .modal .modal-content {
    border: 1px solid var(--dm-modal-border) !important;
    border-radius: var(--dm-modal-radius) !important;
    box-shadow: 0 12px 34px rgba(22, 58, 95, 0.20) !important;
    overflow: hidden !important;
    color: var(--dm-modal-text) !important;
}

/* Header: match WHMCS card/table header direction. */
body #main-body .modal-header,
body .modal .modal-header {
    background-color: var(--dm-modal-navy) !important;
    border-bottom: 1px solid var(--dm-modal-navy) !important;
    color: #ffffff !important;
    padding: 14px 18px !important;
}

body #main-body .modal-header .modal-title,
body #main-body .modal-header h1,
body #main-body .modal-header h2,
body #main-body .modal-header h3,
body #main-body .modal-header h4,
body #main-body .modal-header h5,
body #main-body .modal-header h6,
body .modal .modal-header .modal-title,
body .modal .modal-header h1,
body .modal .modal-header h2,
body .modal .modal-header h3,
body .modal .modal-header h4,
body .modal .modal-header h5,
body .modal .modal-header h6 {
    color: #ffffff !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    margin: 0 !important;
    text-transform: none !important;
}

/* Close buttons should stay visible on navy headers. */
body #main-body .modal-header .close,
body #main-body .modal-header button.close,
body #main-body .modal-header .btn-close,
body .modal .modal-header .close,
body .modal .modal-header button.close,
body .modal .modal-header .btn-close {
    color: #ffffff !important;
    opacity: 0.92 !important;
    text-shadow: none !important;
    box-shadow: none !important;
    outline: none !important;
}

body #main-body .modal-header .close:hover,
body #main-body .modal-header .close:focus,
body #main-body .modal-header button.close:hover,
body #main-body .modal-header button.close:focus,
body #main-body .modal-header .btn-close:hover,
body #main-body .modal-header .btn-close:focus,
body .modal .modal-header .close:hover,
body .modal .modal-header .close:focus,
body .modal .modal-header button.close:hover,
body .modal .modal-header button.close:focus,
body .modal .modal-header .btn-close:hover,
body .modal .modal-header .btn-close:focus {
    color: #ffffff !important;
    opacity: 1 !important;
    background-color: rgba(255, 255, 255, 0.14) !important;
    border-radius: 4px !important;
}

body #main-body .modal-body,
body .modal .modal-body {
    background-color: #ffffff !important;
    color: var(--dm-modal-text) !important;
}

body #main-body .modal-body p,
body #main-body .modal-body label,
body #main-body .modal-body .form-label,
body .modal .modal-body p,
body .modal .modal-body label,
body .modal .modal-body .form-label {
    color: var(--dm-modal-text) !important;
}

body #main-body .modal-body .text-muted,
body .modal .modal-body .text-muted {
    color: var(--dm-modal-muted) !important;
}

/* Footer: light, separated, and aligned with the card body direction. */
body #main-body .modal-footer,
body .modal .modal-footer {
    background-color: var(--dm-modal-footer) !important;
    border-top: 1px solid var(--dm-modal-border) !important;
    padding: 12px 18px !important;
}

/* Modal forms inherit Patch 414; these guards prevent gray addon drift inside popups. */
body #main-body .modal .input-group-text,
body #main-body .modal .input-group-addon,
body .modal .input-group-text,
body .modal .input-group-addon {
    background-color: #f7f9fb !important;
    border-color: var(--dm-modal-border) !important;
    color: var(--dm-modal-navy) !important;
}

/* Keep modal links consistent without overriding buttons. */
body #main-body .modal a:not(.btn):not(.badge):not(.label):not(.dropdown-item),
body .modal a:not(.btn):not(.badge):not(.label):not(.dropdown-item) {
    color: var(--dm-modal-navy) !important;
    text-decoration: none !important;
}

body #main-body .modal a:not(.btn):not(.badge):not(.label):not(.dropdown-item):hover,
body #main-body .modal a:not(.btn):not(.badge):not(.label):not(.dropdown-item):focus,
body .modal a:not(.btn):not(.badge):not(.label):not(.dropdown-item):hover,
body .modal a:not(.btn):not(.badge):not(.label):not(.dropdown-item):focus {
    color: var(--dm-modal-orange) !important;
    text-decoration: none !important;
}
</style>
HTML;
});
