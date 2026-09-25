<?php
/**
 * DomainMonger ClouDNS color/style normalization hook.
 *
 * Patch 520
 * - Builds from Patch 519 confirmed color fix.
 * - Keeps DNS Records Execute orange/white when enabled and darker orange on hover.
 * - Restores Execute visual/disabled state when all record checkboxes are unchecked.
 * - Preserves Mail Forwards Delete muted-orange disabled behavior and ClouDNS-wide button normalization.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 99999, function ($vars) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    // Keep the isolated WHMCS v9 support/testing route untouched.
    if (stripos($requestUri, 'dmv9support=1') !== false) {
        return '';
    }

    // ClouDNS-only selectors. Safe on other WHMCS client-area pages because every
    // rule is scoped to ClouDNS IDs/classes used by the server module templates.
    return <<<'HTML'
<style id="domainmonger-cloudns-color-normalizer-520">
/* Patch 520: ClouDNS-wide approved orange normalization + DNS Records Execute state refresh. */
body #main-body .cloudns-add-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body .cloudns-btn-primary:not([disabled]):not(.disabled):not(:disabled),
body #main-body a.btn.cloudns-btn-primary:not([disabled]):not(.disabled),
body #main-body button.btn.cloudns-btn-primary:not([disabled]):not(.disabled):not(:disabled),
body #main-body input.btn.cloudns-btn-primary:not([disabled]):not(.disabled):not(:disabled),
body #main-body .cloudns-switch-style-button:not(.cloudns-btn-secondary):not(.cloudns-btn-danger):not(.btn-danger):not([disabled]):not(.disabled):not(:disabled),
body #main-body form#recordsForm.recordsForm #bulk-action-execute:not([disabled]):not(.disabled):not(:disabled),
body #main-body #bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body button#bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body .cloudns-record-toolbar #cloudns-search-toggle:not([disabled]):not(.disabled):not(:disabled),
body #main-body .cloudns-record-toolbar button#cloudns-search-toggle.btn:not([disabled]):not(.disabled):not(:disabled),
body #main-body .cloudns-global-domain-switcher #cloudns-global-domain-switcher-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body .cloudns-global-domain-switcher .cloudns-domain-switch-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body .cloudns-bulk-domain-switcher .cloudns-domain-switch-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body #mass-delete.cloudns-mailforward-delete-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a,
body #main-body ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a:visited {
    background: #f58220 !important;
    background-color: #f58220 !important;
    background-image: none !important;
    border-color: #f58220 !important;
    border-bottom-color: #f58220 !important;
    color: #ffffff !important;
    text-shadow: none !important;
    box-shadow: none !important;
    text-decoration: none !important;
}

body #main-body .cloudns-add-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-add-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body .cloudns-btn-primary:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-btn-primary:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body a.btn.cloudns-btn-primary:not([disabled]):not(.disabled):hover,
body #main-body a.btn.cloudns-btn-primary:not([disabled]):not(.disabled):focus,
body #main-body button.btn.cloudns-btn-primary:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body button.btn.cloudns-btn-primary:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body input.btn.cloudns-btn-primary:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body input.btn.cloudns-btn-primary:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body .cloudns-switch-style-button:not(.cloudns-btn-secondary):not(.cloudns-btn-danger):not(.btn-danger):not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-switch-style-button:not(.cloudns-btn-secondary):not(.cloudns-btn-danger):not(.btn-danger):not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body form#recordsForm.recordsForm #bulk-action-execute:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body form#recordsForm.recordsForm #bulk-action-execute:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body #bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body #bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body button#bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body button#bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body .cloudns-record-toolbar #cloudns-search-toggle:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-record-toolbar #cloudns-search-toggle:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body .cloudns-record-toolbar button#cloudns-search-toggle.btn:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-record-toolbar button#cloudns-search-toggle.btn:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body .cloudns-global-domain-switcher #cloudns-global-domain-switcher-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-global-domain-switcher #cloudns-global-domain-switcher-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body .cloudns-global-domain-switcher .cloudns-domain-switch-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-global-domain-switcher .cloudns-domain-switch-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body .cloudns-bulk-domain-switcher .cloudns-domain-switch-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-bulk-domain-switcher .cloudns-domain-switch-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body #mass-delete.cloudns-mailforward-delete-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body #mass-delete.cloudns-mailforward-delete-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a:hover,
body #main-body ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a:focus {
    background: #d8741f !important;
    background-color: #d8741f !important;
    background-image: none !important;
    border-color: #d8741f !important;
    border-bottom-color: #d8741f !important;
    color: #ffffff !important;
    text-shadow: none !important;
    box-shadow: none !important;
    outline: none !important;
    text-decoration: none !important;
}

/* Orange-outline ClouDNS secondary controls: keep outline treatment, normalize old orange. */
body #main-body .cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled),
body #main-body a.btn.cloudns-btn-secondary:not([disabled]):not(.disabled),
body #main-body button.btn.cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled),
body #main-body input.btn.cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled),
body #main-body .cloudns-switch-style-button.cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled) {
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    border-color: #f58220 !important;
    border-bottom-color: #f58220 !important;
    color: #f58220 !important;
    text-shadow: none !important;
    box-shadow: none !important;
    text-decoration: none !important;
}

body #main-body .cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body a.btn.cloudns-btn-secondary:not([disabled]):not(.disabled):hover,
body #main-body a.btn.cloudns-btn-secondary:not([disabled]):not(.disabled):focus,
body #main-body button.btn.cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body button.btn.cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body input.btn.cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body input.btn.cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body .cloudns-switch-style-button.cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body .cloudns-switch-style-button.cloudns-btn-secondary:not([disabled]):not(.disabled):not(:disabled):focus {
    background: #fff7ef !important;
    background-color: #fff7ef !important;
    background-image: none !important;
    border-color: #d8741f !important;
    border-bottom-color: #d8741f !important;
    color: #d8741f !important;
    text-shadow: none !important;
    box-shadow: none !important;
    outline: none !important;
    text-decoration: none !important;
}

/* Preserve red/destructive ClouDNS controls. */
body #main-body .cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute),
body #main-body .cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute):visited,
body #main-body .btn-danger.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute),
body #main-body .cloudns-switch-style-button.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute),
body #main-body .btn-danger:not(#mass-delete):not(#bulk-action-execute) {
    background: #b94a48 !important;
    background-color: #b94a48 !important;
    background-image: none !important;
    border-color: #b94a48 !important;
    border-bottom-color: #b94a48 !important;
    color: #ffffff !important;
    text-shadow: none !important;
    text-decoration: none !important;
}

body #main-body .cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute):hover,
body #main-body .cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute):focus,
body #main-body .btn-danger.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute):hover,
body #main-body .btn-danger.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute):focus,
body #main-body .cloudns-switch-style-button.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute):hover,
body #main-body .cloudns-switch-style-button.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute):focus,
body #main-body .btn-danger:not(#mass-delete):not(#bulk-action-execute):hover,
body #main-body .btn-danger:not(#mass-delete):not(#bulk-action-execute):focus {
    background: #a94442 !important;
    background-color: #a94442 !important;
    background-image: none !important;
    border-color: #a94442 !important;
    border-bottom-color: #a94442 !important;
    color: #ffffff !important;
    outline: none !important;
    text-decoration: none !important;
}

/* DNS Records Execute is an orange action in this customized workflow, even if the source gives it a danger-style class. */
body #main-body input#bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body button#bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body #bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body form#recordsForm.recordsForm #bulk-action-execute:not([disabled]):not(.disabled):not(:disabled) {
    background: #f58220 !important;
    background-color: #f58220 !important;
    background-image: none !important;
    border-color: #f58220 !important;
    border-bottom-color: #f58220 !important;
    color: #ffffff !important;
    opacity: 1 !important;
    cursor: pointer !important;
    pointer-events: auto !important;
    box-shadow: none !important;
    text-shadow: none !important;
    text-decoration: none !important;
}

body #main-body input#bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body input#bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body button#bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body button#bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body #bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body #bulk-action-execute.cloudns-execute-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body form#recordsForm.recordsForm #bulk-action-execute:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body form#recordsForm.recordsForm #bulk-action-execute:not([disabled]):not(.disabled):not(:disabled):focus {
    background: #d8741f !important;
    background-color: #d8741f !important;
    background-image: none !important;
    border-color: #d8741f !important;
    border-bottom-color: #d8741f !important;
    color: #ffffff !important;
    opacity: 1 !important;
    cursor: pointer !important;
    pointer-events: auto !important;
    box-shadow: 0 0 0 2px rgba(216, 116, 31, 0.22) !important;
    outline: none !important;
    text-shadow: none !important;
    text-decoration: none !important;
}

/* Mail Forwards Delete is not red in this customized workflow, even though the source template gives it a danger-style class. */
body #main-body input#mass-delete.cloudns-mailforward-delete-button:not([disabled]):not(.disabled):not(:disabled),
body #main-body #mass-delete.cloudns-mailforward-delete-button:not([disabled]):not(.disabled):not(:disabled) {
    background: #f58220 !important;
    background-color: #f58220 !important;
    background-image: none !important;
    border-color: #f58220 !important;
    border-bottom-color: #f58220 !important;
    color: #ffffff !important;
    opacity: 1 !important;
    cursor: pointer !important;
    pointer-events: auto !important;
    box-shadow: none !important;
    text-shadow: none !important;
    text-decoration: none !important;
}

body #main-body input#mass-delete.cloudns-mailforward-delete-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body input#mass-delete.cloudns-mailforward-delete-button:not([disabled]):not(.disabled):not(:disabled):focus,
body #main-body #mass-delete.cloudns-mailforward-delete-button:not([disabled]):not(.disabled):not(:disabled):hover,
body #main-body #mass-delete.cloudns-mailforward-delete-button:not([disabled]):not(.disabled):not(:disabled):focus {
    background: #d8741f !important;
    background-color: #d8741f !important;
    background-image: none !important;
    border-color: #d8741f !important;
    border-bottom-color: #d8741f !important;
    color: #ffffff !important;
    opacity: 1 !important;
    cursor: pointer !important;
    pointer-events: auto !important;
    box-shadow: 0 0 0 2px rgba(216, 116, 31, 0.22) !important;
    outline: none !important;
    text-shadow: none !important;
    text-decoration: none !important;
}

/* Mail Forwards Delete: disabled state should always return to muted orange, not red. */
body #main-body #mass-delete.cloudns-mailforward-delete-button[disabled],
body #main-body #mass-delete.cloudns-mailforward-delete-button.disabled,
body #main-body #mass-delete.cloudns-mailforward-delete-button:disabled {
    background: #f58220 !important;
    background-color: #f58220 !important;
    background-image: none !important;
    border-color: #f58220 !important;
    border-bottom-color: #f58220 !important;
    color: #ffffff !important;
    opacity: 0.55 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    box-shadow: none !important;
    text-shadow: none !important;
}

/* DNS Records Execute: disabled state should return to inactive muted orange after all records are unchecked. */
body #main-body form#recordsForm.recordsForm #bulk-action-execute[disabled],
body #main-body form#recordsForm.recordsForm #bulk-action-execute.disabled,
body #main-body form#recordsForm.recordsForm #bulk-action-execute:disabled,
body #main-body #bulk-action-execute.cloudns-execute-button[disabled],
body #main-body #bulk-action-execute.cloudns-execute-button.disabled,
body #main-body #bulk-action-execute.cloudns-execute-button:disabled {
    background: #f58220 !important;
    background-color: #f58220 !important;
    background-image: none !important;
    border-color: #f58220 !important;
    border-bottom-color: #f58220 !important;
    color: #ffffff !important;
    opacity: 0.55 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    box-shadow: none !important;
    text-shadow: none !important;
}

/* Keep disabled controls disabled/inactive. Do not force active orange/clickable. */
body #main-body .cloudns-btn-primary[disabled],
body #main-body .cloudns-btn-primary.disabled,
body #main-body .cloudns-btn-primary:disabled,
body #main-body .cloudns-btn-secondary[disabled],
body #main-body .cloudns-btn-secondary.disabled,
body #main-body .cloudns-btn-secondary:disabled,
body #main-body .cloudns-switch-style-button[disabled],
body #main-body .cloudns-switch-style-button.disabled,
body #main-body .cloudns-switch-style-button:disabled,
body #main-body form#recordsForm.recordsForm #bulk-action-execute[disabled],
body #main-body form#recordsForm.recordsForm #bulk-action-execute.disabled,
body #main-body form#recordsForm.recordsForm #bulk-action-execute:disabled,
body #main-body #bulk-action-execute.cloudns-execute-button[disabled],
body #main-body #bulk-action-execute.cloudns-execute-button.disabled,
body #main-body #bulk-action-execute.cloudns-execute-button:disabled,
body #main-body #mass-delete.cloudns-mailforward-delete-button[disabled],
body #main-body #mass-delete.cloudns-mailforward-delete-button.disabled,
body #main-body #mass-delete.cloudns-mailforward-delete-button:disabled {
    opacity: 0.55 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    box-shadow: none !important;
}

/* Preserve/add the confirmed orange checkbox treatment on ClouDNS tables. */
body #main-body #records-table input[type="checkbox"],
body #main-body #table-forwards input[type="checkbox"],
body #main-body #table-forwards input.forward-checkbox,
body #main-body .cloudns-body-panel input[type="checkbox"],
body #main-body .cloudns-mailforward-panel input[type="checkbox"] {
    accent-color: #d8741f !important;
    -webkit-accent-color: #d8741f !important;
}
</style>
<script id="domainmonger-cloudns-color-normalizer-520-js">
(function () {
    'use strict';

    var primary = '#f58220';
    var primaryHover = '#d8741f';
    var outlineHoverBg = '#fff7ef';
    var danger = '#b94a48';
    var dangerHover = '#a94442';
    var white = '#ffffff';

    function paintMailForwardDeleteDisabled(el) {
        paintFilled(el, primary);
        setImportant(el, 'opacity', '0.55');
        setImportant(el, 'cursor', 'not-allowed');
        setImportant(el, 'pointer-events', 'none');
        setImportant(el, 'box-shadow', 'none');
    }

    function paintRecordsExecuteDisabled(el) {
        paintFilled(el, primary);
        setImportant(el, 'opacity', '0.55');
        setImportant(el, 'cursor', 'not-allowed');
        setImportant(el, 'pointer-events', 'none');
        setImportant(el, 'box-shadow', 'none');
    }

    function setImportant(el, property, value) {
        if (!el || !el.style || !el.style.setProperty) {
            return;
        }
        el.style.setProperty(property, value, 'important');
    }

    function isDisabled(el) {
        return !!(el && (el.disabled || el.hasAttribute('disabled') || el.classList.contains('disabled')));
    }

    function isDanger(el) {
        if (!el) {
            return false;
        }
        if (el.id === 'mass-delete' && el.classList.contains('cloudns-mailforward-delete-button')) {
            return false;
        }
        if (el.id === 'bulk-action-execute' && (el.classList.contains('cloudns-execute-button') || el.closest('form#recordsForm.recordsForm'))) {
            return false;
        }
        return !!(el.classList.contains('cloudns-btn-danger') || (el.classList.contains('btn-danger') && el.id !== 'mass-delete' && el.id !== 'bulk-action-execute'));
    }

    function paintFilled(el, color) {
        setImportant(el, 'background', color);
        setImportant(el, 'background-color', color);
        setImportant(el, 'background-image', 'none');
        setImportant(el, 'border-color', color);
        setImportant(el, 'border-bottom-color', color);
        setImportant(el, 'color', white);
        setImportant(el, 'text-shadow', 'none');
        setImportant(el, 'text-decoration', 'none');
    }

    function paintOutline(el, borderColor, bgColor) {
        setImportant(el, 'background', bgColor);
        setImportant(el, 'background-color', bgColor);
        setImportant(el, 'background-image', 'none');
        setImportant(el, 'border-color', borderColor);
        setImportant(el, 'border-bottom-color', borderColor);
        setImportant(el, 'color', borderColor);
        setImportant(el, 'text-shadow', 'none');
        setImportant(el, 'text-decoration', 'none');
    }

    function paintDanger(el, color) {
        paintFilled(el, color);
    }

    function applyCloudnsButtonColors() {
        var filledSelectors = [
            '.cloudns-add-button',
            '.cloudns-btn-primary',
            '.cloudns-switch-style-button:not(.cloudns-btn-secondary):not(.cloudns-btn-danger):not(.btn-danger)',
            '.cloudns-record-toolbar #cloudns-search-toggle',
            '.cloudns-global-domain-switcher #cloudns-global-domain-switcher-button',
            '.cloudns-global-domain-switcher .cloudns-domain-switch-button',
            '.cloudns-bulk-domain-switcher .cloudns-domain-switch-button',
            'ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a'
        ];

        filledSelectors.forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (el) {
                if (isDisabled(el) || isDanger(el)) {
                    return;
                }
                paintFilled(el, primary);
            });
        });

        document.querySelectorAll('.cloudns-btn-secondary, .cloudns-switch-style-button.cloudns-btn-secondary').forEach(function (el) {
            if (isDisabled(el) || isDanger(el)) {
                return;
            }
            paintOutline(el, primary, white);
        });

        document.querySelectorAll('.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute), .btn-danger.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute), .cloudns-switch-style-button.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute), .btn-danger:not(#mass-delete):not(#bulk-action-execute)').forEach(function (el) {
            if (isDisabled(el)) {
                return;
            }
            paintDanger(el, danger);
        });

        document.querySelectorAll('form#recordsForm.recordsForm #bulk-action-execute, #bulk-action-execute.cloudns-execute-button').forEach(function (el) {
            el.classList.remove('cloudns-btn-danger');
            el.classList.add('dm-cloudns-execute-orange');
            if (isDisabled(el)) {
                paintRecordsExecuteDisabled(el);
                return;
            }
            setImportant(el, 'opacity', '1');
            setImportant(el, 'cursor', 'pointer');
            setImportant(el, 'pointer-events', 'auto');
            paintFilled(el, primary);
        });

        document.querySelectorAll('#mass-delete.cloudns-mailforward-delete-button').forEach(function (el) {
            el.classList.remove('cloudns-btn-danger');
            el.classList.add('dm-mailforward-delete-orange');
            if (isDisabled(el)) {
                paintMailForwardDeleteDisabled(el);
                return;
            }
            setImportant(el, 'opacity', '1');
            setImportant(el, 'cursor', 'pointer');
            setImportant(el, 'pointer-events', 'auto');
            paintFilled(el, primary);
        });

        document.querySelectorAll('#records-table input[type="checkbox"], #table-forwards input[type="checkbox"], #table-forwards input.forward-checkbox, .cloudns-body-panel input[type="checkbox"], .cloudns-mailforward-panel input[type="checkbox"]').forEach(function (el) {
            setImportant(el, 'accent-color', primaryHover);
        });
    }

    function bindHover(selector, mode) {
        document.querySelectorAll(selector).forEach(function (el) {
            if (el.getAttribute('data-dm-cloudns-color-bound') === mode) {
                return;
            }
            el.setAttribute('data-dm-cloudns-color-bound', mode);
            el.addEventListener('mouseenter', function () {
                if (isDisabled(el)) {
                    return;
                }
                if (mode === 'outline') {
                    paintOutline(el, primaryHover, outlineHoverBg);
                } else if (mode === 'danger') {
                    paintDanger(el, dangerHover);
                } else {
                    paintFilled(el, primaryHover);
                }
            });
            el.addEventListener('mouseleave', function () {
                if (isDisabled(el)) {
                    return;
                }
                if (mode === 'outline') {
                    paintOutline(el, primary, white);
                } else if (mode === 'danger') {
                    paintDanger(el, danger);
                } else {
                    paintFilled(el, primary);
                }
            });
            el.addEventListener('focus', function () {
                if (isDisabled(el)) {
                    return;
                }
                if (mode === 'outline') {
                    paintOutline(el, primaryHover, outlineHoverBg);
                } else if (mode === 'danger') {
                    paintDanger(el, dangerHover);
                } else {
                    paintFilled(el, primaryHover);
                }
            });
            el.addEventListener('blur', function () {
                if (isDisabled(el)) {
                    return;
                }
                if (mode === 'outline') {
                    paintOutline(el, primary, white);
                } else if (mode === 'danger') {
                    paintDanger(el, danger);
                } else {
                    paintFilled(el, primary);
                }
            });
        });
    }

    function refreshMailForwardDeleteState() {
        var deleteButton = document.querySelector('#mass-delete.cloudns-mailforward-delete-button');
        if (!deleteButton) {
            return;
        }
        deleteButton.classList.remove('cloudns-btn-danger');
        deleteButton.classList.add('dm-mailforward-delete-orange');
        var checkedCount = document.querySelectorAll('#table-forwards input.forward-checkbox:checked, #table-forwards input[name="forwards[]"]:checked').length;
        var shouldDisable = checkedCount === 0;

        deleteButton.disabled = shouldDisable;
        if (shouldDisable) {
            deleteButton.setAttribute('disabled', 'disabled');
            paintMailForwardDeleteDisabled(deleteButton);
        } else {
            deleteButton.removeAttribute('disabled');
            deleteButton.classList.remove('disabled');
            setImportant(deleteButton, 'opacity', '1');
            setImportant(deleteButton, 'cursor', 'pointer');
            setImportant(deleteButton, 'pointer-events', 'auto');
            paintFilled(deleteButton, primary);
        }
    }

    function refreshRecordsExecuteState() {
        var executeButton = document.querySelector('form#recordsForm.recordsForm #bulk-action-execute, #bulk-action-execute.cloudns-execute-button');
        if (!executeButton) {
            return;
        }

        executeButton.classList.remove('cloudns-btn-danger');
        executeButton.classList.add('dm-cloudns-execute-orange');

        var checkedCount = document.querySelectorAll('form#recordsForm.recordsForm input.record-checkbox:checked, #records-table input.record-checkbox:checked, input.record-checkbox:checked').length;
        var shouldDisable = checkedCount === 0;

        if (shouldDisable) {
            executeButton.disabled = true;
            executeButton.setAttribute('disabled', 'disabled');
            executeButton.classList.remove('disabled');
            paintRecordsExecuteDisabled(executeButton);
        } else {
            executeButton.disabled = false;
            executeButton.removeAttribute('disabled');
            executeButton.classList.remove('disabled');
            setImportant(executeButton, 'opacity', '1');
            setImportant(executeButton, 'cursor', 'pointer');
            setImportant(executeButton, 'pointer-events', 'auto');
            paintFilled(executeButton, primary);
        }
    }

    function bindRecordsExecuteState() {
        if (document.body.getAttribute('data-dm-records-execute-state-bound') === '1') {
            return;
        }
        document.body.setAttribute('data-dm-records-execute-state-bound', '1');

        document.addEventListener('change', function (event) {
            if (event.target && (event.target.matches('input.record-checkbox') || event.target.matches('#check-all'))) {
                window.setTimeout(function () {
                    refreshRecordsExecuteState();
                    applyCloudnsButtonColors();
                    refreshRecordsExecuteState();
                }, 0);
                window.setTimeout(refreshRecordsExecuteState, 50);
            }
        }, true);

        document.addEventListener('click', function (event) {
            if (event.target && (event.target.matches('input.record-checkbox') || event.target.matches('#check-all'))) {
                window.setTimeout(function () {
                    refreshRecordsExecuteState();
                    applyCloudnsButtonColors();
                    refreshRecordsExecuteState();
                }, 0);
                window.setTimeout(refreshRecordsExecuteState, 50);
            }
        }, true);

        refreshRecordsExecuteState();
    }

    function bindMailForwardDeleteState() {
        if (document.body.getAttribute('data-dm-mailforward-delete-state-bound') === '1') {
            return;
        }
        document.body.setAttribute('data-dm-mailforward-delete-state-bound', '1');

        document.addEventListener('change', function (event) {
            if (event.target && (event.target.matches('#table-forwards input.forward-checkbox') || event.target.matches('#table-forwards input[name="forwards[]"]') || event.target.matches('#check-all'))) {
                window.setTimeout(function () {
                    refreshMailForwardDeleteState();
                    applyCloudnsButtonColors();
                }, 0);
            }
        }, true);

        document.addEventListener('click', function (event) {
            if (event.target && (event.target.matches('#table-forwards input.forward-checkbox') || event.target.matches('#table-forwards input[name="forwards[]"]') || event.target.matches('#check-all'))) {
                window.setTimeout(function () {
                    refreshMailForwardDeleteState();
                    applyCloudnsButtonColors();
                }, 0);
            }
        }, true);

        refreshMailForwardDeleteState();
    }

    function bindCloudnsButtonColors() {
        applyCloudnsButtonColors();
        bindRecordsExecuteState();
        refreshRecordsExecuteState();
        bindMailForwardDeleteState();
        refreshMailForwardDeleteState();

        bindHover('.cloudns-add-button, .cloudns-btn-primary, .cloudns-switch-style-button:not(.cloudns-btn-secondary):not(.cloudns-btn-danger):not(.btn-danger), .cloudns-record-toolbar #cloudns-search-toggle, .cloudns-global-domain-switcher #cloudns-global-domain-switcher-button, .cloudns-global-domain-switcher .cloudns-domain-switch-button, .cloudns-bulk-domain-switcher .cloudns-domain-switch-button, ul#cloudnsSettingsMenu > li.cloudns-menu-add-zone > a, form#recordsForm.recordsForm #bulk-action-execute, #bulk-action-execute.cloudns-execute-button, #mass-delete.cloudns-mailforward-delete-button', 'filled');
        bindHover('.cloudns-btn-secondary, .cloudns-switch-style-button.cloudns-btn-secondary', 'outline');
        bindHover('.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute), .btn-danger.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute), .cloudns-switch-style-button.cloudns-btn-danger:not(#mass-delete):not(#bulk-action-execute), .btn-danger:not(#mass-delete):not(#bulk-action-execute)', 'danger');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindCloudnsButtonColors);
    } else {
        bindCloudnsButtonColors();
    }

    window.setTimeout(bindCloudnsButtonColors, 50);
    window.setTimeout(bindCloudnsButtonColors, 300);
    window.setTimeout(bindCloudnsButtonColors, 800);

    if (window.jQuery) {
        window.jQuery(document).on('change click draw.dt', function () {
            window.setTimeout(function () {
                bindCloudnsButtonColors();
                refreshRecordsExecuteState();
            }, 0);
        });
    }
})();
</script>
HTML;
});
