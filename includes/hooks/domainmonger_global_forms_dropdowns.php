<?php
/**
 * DomainMonger WHMCS global form control and dropdown polish.
 *
 * Patch 414 / consistency update 1331 / flash correction 1332
 * - Component-level global cleanup for inputs, selects, textareas, input groups,
 *   and light/dropdown-style controls.
 * - Keeps dropdown controls white/light with navy text and pale orange hover.
 * - Avoids broad typography/layout changes and avoids changing normal action buttons.
 * - Skips the custom v8x register route and the isolated WHMCS v9 support route.
 * - Does not touch templates, language files, integration files, or order-form logic.
 * - Patch 1332 suppresses the native black full-page overlay only during initial
 *   rendering on My Details and Change Password, then releases it after load.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 120, function ($vars) {
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

    $action = strtolower((string) ($_GET['action'] ?? ''));
    $templateFile = strtolower((string) ($vars['templatefile'] ?? ''));
    $useInitialOverlayGuard = in_array($action, ['details', 'changepw', 'changepassword', 'change-password'], true)
        || $templateFile === 'user-password'
        || stripos($requestUri, '/user/password') !== false;

    $initialOverlayGuard = '';
    if ($useInitialOverlayGuard) {
        $initialOverlayGuard = <<<'HTML'
<style id="domainmonger-form-focus-initial-overlay-guard-1332">
/*
 * My Details and Change Password expose WHMCS's black #fullpage-overlay during
 * initial rendering when the shared form layer is loaded in the document head.
 * Hide it only while the page is initially assembling; later intentional
 * overlay use remains available after the guard class is released.
 */
html.dm-form-focus-initial-overlay-guard-1332 #fullpage-overlay,
body.dm-form-focus-initial-overlay-guard-1332 #fullpage-overlay {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}
html.dm-form-focus-initial-overlay-guard-1332 #fullpage-overlay img,
body.dm-form-focus-initial-overlay-guard-1332 #fullpage-overlay img,
html.dm-form-focus-initial-overlay-guard-1332 #fullpage-overlay .overlay-spinner,
body.dm-form-focus-initial-overlay-guard-1332 #fullpage-overlay .overlay-spinner {
    display: none !important;
}
</style>
<script id="domainmonger-form-focus-initial-overlay-guard-script-1332">
(function () {
    'use strict';

    var guardClass = 'dm-form-focus-initial-overlay-guard-1332';
    var root = document.documentElement;
    var released = false;

    root.classList.add(guardClass);

    function addBodyGuard() {
        if (document.body) {
            document.body.classList.add(guardClass);
        }
    }

    function releaseGuard() {
        if (released) {
            return;
        }
        released = true;

        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                root.classList.remove(guardClass);
                if (document.body) {
                    document.body.classList.remove(guardClass);
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', addBodyGuard, true);
    window.addEventListener('load', releaseGuard, true);

    // Safety release for cached pages or unusual load ordering.
    window.setTimeout(releaseGuard, 2500);
})();
</script>
HTML;
    }

    $formCss = <<<'HTML'
<style id="domainmonger-global-forms-dropdowns">
:root {
    --dm-form-navy: #163a5f;
    --dm-form-navy-hover: #214e7a;
    --dm-form-orange: #f58220;
    --dm-form-orange-soft: #fff3e8;
    --dm-form-border: #c9d4df;
    --dm-form-border-hover: #9fb0c0;
    --dm-form-text: #24384f;
    --dm-form-muted: #6c757d;
    --dm-form-bg: #ffffff;
    --dm-form-disabled-bg: #f4f6f8;
    --dm-form-radius: 6px;
}

/* Core form controls: keep sizing/layout from WHMCS, only normalize palette and corners. */
body #main-body .form-control,
body #main-body input[type="text"],
body #main-body input[type="email"],
body #main-body input[type="password"],
body #main-body input[type="number"],
body #main-body input[type="search"],
body #main-body input[type="tel"],
body #main-body input[type="url"],
body #main-body select,
body #main-body textarea,
body #main-body .custom-select {
    background-color: var(--dm-form-bg) !important;
    border-color: var(--dm-form-border) !important;
    border-radius: var(--dm-form-radius) !important;
    color: var(--dm-form-text) !important;
    box-shadow: none !important;
    text-decoration: none !important;
}

body #main-body .form-control:hover,
body #main-body input[type="text"]:hover,
body #main-body input[type="email"]:hover,
body #main-body input[type="password"]:hover,
body #main-body input[type="number"]:hover,
body #main-body input[type="search"]:hover,
body #main-body input[type="tel"]:hover,
body #main-body input[type="url"]:hover,
body #main-body select:hover,
body #main-body textarea:hover,
body #main-body .custom-select:hover {
    border-color: var(--dm-form-border-hover) !important;
}

body #main-body .form-control:focus,
body #main-body input[type="text"]:focus,
body #main-body input[type="email"]:focus,
body #main-body input[type="password"]:focus,
body #main-body input[type="number"]:focus,
body #main-body input[type="search"]:focus,
body #main-body input[type="tel"]:focus,
body #main-body input[type="url"]:focus,
body #main-body select:focus,
body #main-body textarea:focus,
body #main-body .custom-select:focus {
    border-color: var(--dm-form-orange) !important;
    box-shadow: 0 0 0 0.14rem rgba(245, 130, 32, 0.18) !important;
    outline: 0 !important;
}

body #main-body .form-control::placeholder,
body #main-body input::placeholder,
body #main-body textarea::placeholder {
    color: var(--dm-form-muted) !important;
    opacity: 0.78 !important;
}

body #main-body .form-control:disabled,
body #main-body .form-control[readonly],
body #main-body input:disabled,
body #main-body input[readonly],
body #main-body select:disabled,
body #main-body textarea:disabled,
body #main-body textarea[readonly],
body #main-body .custom-select:disabled {
    background-color: var(--dm-form-disabled-bg) !important;
    border-color: #d5dde5 !important;
    color: #65717d !important;
    opacity: 1 !important;
}

/* Input groups: keep attached controls aligned and avoid mismatched gray addon blocks. */
body #main-body .input-group-text,
body #main-body .input-group-addon {
    background-color: #f7f9fb !important;
    border-color: var(--dm-form-border) !important;
    color: var(--dm-form-navy) !important;
    font-weight: 600 !important;
}

body #main-body .input-group .form-control:focus,
body #main-body .input-group input:focus,
body #main-body .input-group select:focus {
    position: relative;
    z-index: 3;
}

/* Bootstrap/selectpicker style dropdown controls should remain light, not gray or navy action buttons. */
body #main-body .bootstrap-select > .dropdown-toggle,
body #main-body .bootstrap-select > .dropdown-toggle.btn,
body #main-body .dropdown-toggle.form-control,
body #main-body .select2-selection,
body #main-body .select2-container--default .select2-selection--single,
body #main-body .select2-container--default .select2-selection--multiple,
body #main-body .chosen-container-single .chosen-single,
body #main-body .chosen-container-multi .chosen-choices {
    background: var(--dm-form-bg) !important;
    background-color: var(--dm-form-bg) !important;
    border-color: var(--dm-form-border) !important;
    border-radius: var(--dm-form-radius) !important;
    color: var(--dm-form-navy) !important;
    box-shadow: none !important;
    text-decoration: none !important;
}

body #main-body .bootstrap-select > .dropdown-toggle:hover,
body #main-body .bootstrap-select > .dropdown-toggle:focus,
body #main-body .dropdown-toggle.form-control:hover,
body #main-body .dropdown-toggle.form-control:focus,
body #main-body .select2-selection:hover,
body #main-body .select2-container--default.select2-container--focus .select2-selection--multiple,
body #main-body .select2-container--default.select2-container--open .select2-selection--single,
body #main-body .chosen-container-active .chosen-single,
body #main-body .chosen-container-active .chosen-choices {
    border-color: var(--dm-form-orange) !important;
    box-shadow: 0 0 0 0.14rem rgba(245, 130, 32, 0.18) !important;
    color: var(--dm-form-navy) !important;
    outline: 0 !important;
}

body #main-body .bootstrap-select > .dropdown-toggle .filter-option,
body #main-body .bootstrap-select > .dropdown-toggle .filter-option-inner-inner,
body #main-body .select2-selection__rendered,
body #main-body .chosen-single span {
    color: var(--dm-form-navy) !important;
}

/* Dropdown menus: light surface, navy text, pale-orange hover/active. */
body #main-body .dropdown-menu,
body #main-body .select2-dropdown,
body #main-body .chosen-drop {
    background-color: #ffffff !important;
    border-color: var(--dm-form-border) !important;
    border-radius: var(--dm-form-radius) !important;
    box-shadow: 0 8px 22px rgba(22, 58, 95, 0.13) !important;
}

body #main-body .dropdown-menu > li > a,
body #main-body .dropdown-menu .dropdown-item,
body #main-body .select2-results__option,
body #main-body .chosen-results li {
    color: var(--dm-form-navy) !important;
    text-decoration: none !important;
}

body #main-body .dropdown-menu > li > a:hover,
body #main-body .dropdown-menu > li > a:focus,
body #main-body .dropdown-menu .dropdown-item:hover,
body #main-body .dropdown-menu .dropdown-item:focus,
body #main-body .select2-results__option--highlighted,
body #main-body .chosen-results li.highlighted {
    background-color: var(--dm-form-orange-soft) !important;
    color: var(--dm-form-navy) !important;
    text-decoration: none !important;
}

body #main-body .dropdown-menu > .active > a,
body #main-body .dropdown-menu > .active > a:hover,
body #main-body .dropdown-menu > .active > a:focus,
body #main-body .dropdown-menu .dropdown-item.active,
body #main-body .select2-results__option[aria-selected="true"] {
    background-color: var(--dm-form-navy) !important;
    color: #ffffff !important;
}

/* Validation states stay meaningful, but use the approved red for errors. */
body #main-body .has-error .form-control,
body #main-body .form-control.is-invalid,
body #main-body input.is-invalid,
body #main-body select.is-invalid,
body #main-body textarea.is-invalid {
    border-color: #b94a48 !important;
}

body #main-body .has-error .form-control:focus,
body #main-body .form-control.is-invalid:focus,
body #main-body input.is-invalid:focus,
body #main-body select.is-invalid:focus,
body #main-body textarea.is-invalid:focus {
    box-shadow: 0 0 0 0.14rem rgba(185, 74, 72, 0.16) !important;
}
</style>
HTML;

    return $initialOverlayGuard . $formCss;
});
