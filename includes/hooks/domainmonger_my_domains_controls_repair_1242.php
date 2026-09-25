<?php
/**
 * DomainMonger My Domains checkbox and bulk-action repair.
 *
 * Patch 1254 (custom orange bulk-domain radio selectors)
 * - Registers an early capture-phase guard from ClientAreaHeadOutput.
 * - Prevents the global converted-link normalizer from treating a domain row
 *   containing "Auto Renew" as an Auto Renew navigation target when a user
 *   clicks an individual checkbox or a bulk-action control.
 * - Preserves the checkbox's intended checked state after cancelling the
 *   competing routed click.
 * - Keeps the already-working Select All behavior.
 * - Submits selected domids[] through the correct WHMCS bulk action.
 * - Uses an orange fill with an explicit white checkmark for My Domains checkboxes.
 * - Opens the existing converted Manage Domain page when exactly one domain is selected.
 * - Preserves true multi-domain operations and restyles the WHMCS bulk page to match the converted interface.
 * - Scoped only to clientareadomains / clientarea.php?action=domains.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, static function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $isDomainsList = ($templateFile === 'clientareadomains')
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=domains(?:&|$)/i', $requestUri));

    if (!$isDomainsList) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-my-domains-controls-repair-1254-css">
body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input#dmSelectAllDomains,
body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input.domids {
    -webkit-appearance: none !important;
    appearance: none !important;
    position: relative !important;
    z-index: 30 !important;
    width: 18px !important;
    min-width: 18px !important;
    height: 18px !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 1px solid #b9c0c7 !important;
    border-radius: 3px !important;
    background-color: #ffffff !important;
    background-image: none !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    background-size: 12px 12px !important;
    box-shadow: none !important;
    pointer-events: auto !important;
    opacity: 1 !important;
    visibility: visible !important;
    cursor: pointer !important;
    vertical-align: middle !important;
}

body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input#dmSelectAllDomains:checked,
body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input.domids:checked {
    border-color: #f58220 !important;
    background-color: #f58220 !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.6' d='M3 8.4l3.1 3.1L13 4.7'/%3E%3C/svg%3E") !important;
}

body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input#dmSelectAllDomains:indeterminate,
body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input.domids:indeterminate {
    border-color: #b9c0c7 !important;
    background-color: #ffffff !important;
    background-image: none !important;
}

body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input#dmSelectAllDomains:focus,
body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input.domids:focus {
    border-color: #f58220 !important;
    outline: 2px solid rgba(245, 130, 32, 0.28) !important;
    outline-offset: 2px !important;
}

body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input#dmSelectAllDomains:disabled,
body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList input.domids:disabled {
    cursor: not-allowed !important;
    opacity: 0.55 !important;
}

body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList td.dm-domain-select-cell {
    position: relative !important;
    cursor: pointer !important;
}

body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList td.dm-domain-select-cell::before,
body.whmcsbody.whmcs-templatefile-clientareadomains #tableDomainsList td.dm-domain-select-cell::after {
    pointer-events: none !important;
}
</style>
<script id="domainmonger-my-domains-controls-repair-1254-js">
(function () {
    'use strict';

    if (window.domainmongerMyDomains1254Loaded) {
        return;
    }
    window.domainmongerMyDomains1254Loaded = true;

    var actionMap = {
        nameservers: { update: 'nameservers', route: 'nameservers', renew: false },
        contactinfo: { update: 'contactinfo', route: 'contactinfo', renew: false },
        renewDomains: { update: '', route: '', renew: true },
        autorenew: { update: 'autorenew', route: 'autorenew', renew: false },
        reglock: { update: 'reglock', route: 'reglock', renew: false }
    };

    /* Patch 1267: Match the established WHOIS terminology on My Domains. */
    function updateContactActionLabel() {
        var button = document.querySelector('#domainForm #contactinfo');
        var textUpdated = false;

        if (!button) {
            return;
        }

        Array.prototype.forEach.call(button.childNodes, function (node) {
            if (node.nodeType === 3 && String(node.nodeValue || '').trim() !== '') {
                node.nodeValue = ' Edit WHOIS Contact Info';
                textUpdated = true;
            }
        });

        if (!textUpdated) {
            var labelElement = button.querySelector('span:not(.caret):not([class*="icon"])');
            if (labelElement) {
                labelElement.textContent = 'Edit WHOIS Contact Info';
                textUpdated = true;
            }
        }

        if (!textUpdated) {
            button.appendChild(document.createTextNode(' Edit WHOIS Contact Info'));
        }

        button.setAttribute('aria-label', 'Edit WHOIS Contact Info');
        button.setAttribute('title', 'Edit WHOIS Contact Info');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateContactActionLabel, { once: true });
    } else {
        updateContactActionLabel();
    }

    function closest(element, selector) {
        if (!element || element === document) {
            return null;
        }

        if (element.nodeType !== 1) {
            element = element.parentElement;
        }

        if (!element) {
            return null;
        }

        if (typeof element.closest === 'function') {
            return element.closest(selector);
        }

        while (element && element.nodeType === 1) {
            if (typeof element.matches === 'function' && element.matches(selector)) {
                return element;
            }
            element = element.parentElement;
        }

        return null;
    }

    function rowCheckboxes() {
        return Array.prototype.slice.call(
            document.querySelectorAll('#tableDomainsList tbody input.domids[type="checkbox"]')
        );
    }

    function selectedCheckboxes() {
        return rowCheckboxes().filter(function (checkbox) {
            return checkbox.checked && !checkbox.disabled;
        });
    }

    function dispatchChange(checkbox) {
        var changeEvent;

        if (typeof Event === 'function') {
            changeEvent = new Event('change', { bubbles: true });
        } else {
            changeEvent = document.createEvent('Event');
            changeEvent.initEvent('change', true, false);
        }

        checkbox.dispatchEvent(changeEvent);
    }

    function syncMasterCheckbox() {
        var master = document.getElementById('dmSelectAllDomains');
        var rows = rowCheckboxes().filter(function (checkbox) {
            return !checkbox.disabled;
        });
        var checkedCount;

        if (!master) {
            return;
        }

        checkedCount = rows.filter(function (checkbox) {
            return checkbox.checked;
        }).length;

        master.checked = rows.length > 0 && checkedCount === rows.length;
        /* Keep Select All visually empty until every visible row is selected. */
        master.indeterminate = false;
    }

    function preserveNativeCheckboxResult(event, checkbox) {
        /*
         * Checkbox activation toggles checked before the click event is
         * dispatched. Capture that intended state, cancel only the competing
         * routed click, then reapply the state after the browser finishes its
         * cancelled-click rollback.
         */
        var intendedChecked = checkbox.checked;

        event.preventDefault();
        event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') {
            event.stopImmediatePropagation();
        }

        window.setTimeout(function () {
            checkbox.checked = intendedChecked;
            dispatchChange(checkbox);
            syncMasterCheckbox();
        }, 0);
    }

    function toggleCheckboxCell(event, cell) {
        var checkbox = cell.querySelector('input.domids[type="checkbox"]');

        if (!checkbox || checkbox.disabled) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') {
            event.stopImmediatePropagation();
        }

        checkbox.checked = !checkbox.checked;
        dispatchChange(checkbox);
        syncMasterCheckbox();
    }

    function renewActionUrl() {
        try {
            if (window.WHMCS
                && window.WHMCS.utils
                && typeof window.WHMCS.utils.getRouteUrl === 'function') {
                return window.WHMCS.utils.getRouteUrl('/cart/domain/renew');
            }
        } catch (ignore) {}

        return 'cart.php?a=add&domain=renew';
    }

    function convertedActionUrl(route, domainId) {
        var encodedId = encodeURIComponent(String(domainId || '').replace(/[^0-9]/g, ''));

        if (!encodedId) {
            return '';
        }

        if (route === 'contactinfo') {
            return 'clientarea.php?action=domaincontacts&domainid=' + encodedId;
        }

        if (route === 'nameservers') {
            return 'clientarea.php?action=domaindetails&id=' + encodedId
                + '&dmsection=nameservers&dmdesign=1&dmconverted=1#tabNameservers';
        }

        if (route === 'autorenew') {
            return 'clientarea.php?action=domaindetails&id=' + encodedId
                + '&dmsection=autorenew&dmdesign=1&dmconverted=1#tabAutorenew';
        }

        if (route === 'reglock') {
            return 'clientarea.php?action=domaindetails&id=' + encodedId
                + '&dmsection=reglock&dmdesign=1&dmconverted=1#tabReglock';
        }

        return '';
    }

    function submitAction(event, button) {
        var config = actionMap[button.id];
        var form = document.getElementById('domainForm');
        var bulkAction = document.getElementById('bulkaction');
        var selected = selectedCheckboxes();
        var convertedUrl;

        event.preventDefault();
        event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') {
            event.stopImmediatePropagation();
        }

        if (!config || !form) {
            return;
        }

        if (selected.length === 0) {
            window.alert('Please select at least one domain.');
            return;
        }

        if (!config.renew && config.route && selected.length === 1) {
            convertedUrl = convertedActionUrl(config.route, selected[0].value);
            if (convertedUrl) {
                window.location.assign(convertedUrl);
                return;
            }
        }

        if (config.renew) {
            form.setAttribute('action', renewActionUrl());
        } else {
            /* Multiple selections keep WHMCS bulk processing, with the page-specific converted skin below. */
            form.setAttribute('action', 'clientarea.php?action=bulkdomain&dmconverted=1');
            if (bulkAction) {
                bulkAction.value = config.update;
            }
        }

        HTMLFormElement.prototype.submit.call(form);
    }

    /*
     * This listener is emitted in the page head, before the legacy/global
     * converted-link normalizer in the footer. That registration order is
     * essential: the global normalizer otherwise climbs from the checkbox to
     * the row, sees the row text "Auto Renew", and redirects during capture.
     */
    document.addEventListener('click', function (event) {
        var target = event.target;
        var rowCheckbox = closest(target, '#tableDomainsList tbody input.domids[type="checkbox"]');
        var masterCheckbox = closest(target, '#tableDomainsList thead input#dmSelectAllDomains[type="checkbox"]');
        var selectCell = closest(target, '#tableDomainsList tbody td.dm-domain-select-cell');
        var actionButton = closest(
            target,
            '#domainForm #nameservers, #domainForm #contactinfo, #domainForm #renewDomains, #domainForm #autorenew, #domainForm #reglock'
        );

        if (rowCheckbox) {
            preserveNativeCheckboxResult(event, rowCheckbox);
            return;
        }

        /* Keep the already-working Select All native/template behavior. */
        if (masterCheckbox) {
            event.stopPropagation();
            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }
            return;
        }

        if (selectCell) {
            toggleCheckboxCell(event, selectCell);
            return;
        }

        if (actionButton) {
            submitAction(event, actionButton);
        }
    }, true);

    document.addEventListener('change', function (event) {
        if (closest(event.target, '#tableDomainsList tbody input.domids[type="checkbox"]')) {
            syncMasterCheckbox();
        }
    });
}());
</script>
HTML;
});

/*
 * Multi-domain actions must continue to use WHMCS's bulk processor. This
 * page-specific skin makes that inherited bulk form match the converted
 * DomainMonger interface without replacing any registrar/backend behavior.
 */
add_hook('ClientAreaHeadOutput', 2, static function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $isBulkDomain = ($templateFile === 'bulkdomainmanagement')
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=bulkdomain(?:&|$)/i', $requestUri));

    if (!$isBulkDomain) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-bulk-domain-converted-1254-css">
body.dm-bulk-domain-converted-1254 .main-content > .card,
body.dm-bulk-domain-converted-1254 .main-content .dm-bulk-domain-card {
    overflow: hidden !important;
    border: 1px solid #d8dee6 !important;
    border-radius: 5px !important;
    background: #ffffff !important;
    box-shadow: 0 8px 24px rgba(22, 58, 95, 0.08) !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-intro {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 18px !important;
    margin: 0 0 16px !important;
    padding: 15px 18px !important;
    border: 1px solid #d8dee6 !important;
    border-left: 4px solid #f58220 !important;
    border-radius: 5px !important;
    background: #ffffff !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-intro strong {
    display: block !important;
    margin: 0 !important;
    color: #163a5f !important;
    font-size: 17px !important;
    line-height: 1.08 !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-intro span {
    display: block !important;
    color: #5d6875 !important;
    font-size: 13px !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-intro em {
    flex: 0 0 auto !important;
    padding: 5px 10px !important;
    border-radius: 4px !important;
    background: #f58220 !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-style: normal !important;
    font-weight: 700 !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card > .card-body {
    padding: 0 20px 20px !important;
    background: #ffffff !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card h3.card-title {
    margin: 0 -20px 20px !important;
    padding: 14px 18px !important;
    border: 0 !important;
    background: #163a5f !important;
    color: #ffffff !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.3 !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card p {
    color: #424c57 !important;
    line-height: 1.55 !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .list-group {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    grid-auto-flow: row !important;
    column-gap: 28px !important;
    row-gap: 0 !important;
    margin: 5px 0 10px !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    list-style: none !important;
}

/*
 * The global consistency hook uses #main-body .list-group-item with
 * !important padding. Include the same ID here so this page-specific rule
 * wins without changing list spacing elsewhere in WHMCS.
 */
body.dm-bulk-domain-converted-1254 #main-body .dm-bulk-domain-card ul.list-group > li.list-group-item {
    display: block !important;
    min-width: 0 !important;
    min-height: 0 !important;
    height: auto !important;
    margin: 0 !important;
    padding: 1px 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #263544 !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    line-height: 1.08 !important;
    overflow-wrap: anywhere !important;
    box-shadow: none !important;
}

body.dm-bulk-domain-converted-1254 #main-body .dm-bulk-domain-card ul.list-group > li.list-group-item:first-child,
body.dm-bulk-domain-converted-1254 #main-body .dm-bulk-domain-card ul.list-group > li.list-group-item:last-child {
    border-radius: 0 !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .form-control,
body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .custom-select {
    min-height: 40px !important;
    border: 1px solid #bcc6d0 !important;
    border-radius: 4px !important;
    background-color: #ffffff !important;
    color: #263544 !important;
    box-shadow: none !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .form-control:focus,
body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .custom-select:focus {
    border-color: #f58220 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.16) !important;
}

/*
 * Bulk nameserver selectors use a fully custom radio appearance. This removes
 * the browser/Bootstrap square focus frame instead of trying to hide one
 * state of the native radio control.
 */
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] input#nsChoiceDefault.form-check-input[type="radio"],
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] input#nsChoiceCustom.form-check-input[type="radio"] {
    -webkit-appearance: none !important;
    appearance: none !important;
    display: inline-block !important;
    flex: 0 0 15px !important;
    width: 15px !important;
    min-width: 15px !important;
    height: 15px !important;
    min-height: 15px !important;
    margin: 0 7px 0 0 !important;
    padding: 0 !important;
    border: 2px solid #f58220 !important;
    border-radius: 50% !important;
    outline: 0 !important;
    background-color: #ffffff !important;
    background-image: none !important;
    box-shadow: none !important;
    filter: none !important;
    cursor: pointer !important;
    vertical-align: middle !important;
}

body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] input#nsChoiceDefault.form-check-input[type="radio"]:checked,
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] input#nsChoiceCustom.form-check-input[type="radio"]:checked {
    border-color: #f58220 !important;
    background-color: #ffffff !important;
    background-image: radial-gradient(circle at center, #f58220 0 38%, transparent 42%) !important;
    box-shadow: none !important;
}

body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] input#nsChoiceDefault.form-check-input[type="radio"]:focus,
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] input#nsChoiceDefault.form-check-input[type="radio"]:focus-visible,
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] input#nsChoiceCustom.form-check-input[type="radio"]:focus,
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] input#nsChoiceCustom.form-check-input[type="radio"]:focus-visible,
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] .form-check:focus-within,
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] .form-check:focus-within::before,
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] .form-check:focus-within::after {
    outline: 0 !important;
    box-shadow: none !important;
}

/* Remove any legacy iCheck wrapper if another script has wrapped these radios. */
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] .iradio_square-blue,
body.dm-bulk-domain-converted-1254 #main-body form[action*="action=bulkdomain"] .iradio_flat-blue {
    width: 15px !important;
    height: 15px !important;
    margin: 0 7px 0 0 !important;
    border: 0 !important;
    outline: 0 !important;
    background: none !important;
    box-shadow: none !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .nav-tabs {
    margin-top: 14px !important;
    border-bottom: 2px solid #163a5f !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .nav-tabs .nav-link {
    margin: 0 4px 0 0 !important;
    border: 1px solid #d8dee6 !important;
    border-bottom: 0 !important;
    border-radius: 4px 4px 0 0 !important;
    background: #ffffff !important;
    color: #163a5f !important;
    font-weight: 700 !important;
    text-decoration: none !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .nav-tabs .nav-link.active,
body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .nav-tabs .nav-link:hover,
body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .nav-tabs .nav-link:focus {
    border-color: #163a5f !important;
    background: #163a5f !important;
    color: #ffffff !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .tab-content {
    border: 1px solid #d8dee6 !important;
    border-top: 0 !important;
    border-radius: 0 0 4px 4px !important;
    background: #ffffff !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .btn-primary,
body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .btn-success {
    border-color: #f58220 !important;
    background: #f58220 !important;
    color: #ffffff !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .btn-primary:hover,
body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .btn-primary:focus,
body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .btn-success:hover,
body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .btn-success:focus {
    border-color: #d8741f !important;
    background: #d8741f !important;
    color: #ffffff !important;
}

body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .btn-danger {
    border-color: #b94a48 !important;
    background: #b94a48 !important;
    color: #ffffff !important;
}

body.dm-bulk-domain-converted-1254 .main-content > a.btn-default,
body.dm-bulk-domain-converted-1254 .main-content a.btn-secondary,
body.dm-bulk-domain-converted-1254 .main-content a[href*="action=domains"].btn {
    border-color: #163a5f !important;
    background: #163a5f !important;
    color: #ffffff !important;
    text-decoration: none !important;
}

body.dm-bulk-domain-converted-1254 .main-content > a.btn-default:hover,
body.dm-bulk-domain-converted-1254 .main-content a.btn-secondary:hover,
body.dm-bulk-domain-converted-1254 .main-content a[href*="action=domains"].btn:hover {
    border-color: #214e7a !important;
    background: #214e7a !important;
    color: #ffffff !important;
}

body.dm-bulk-domain-converted-1254 .alert-success {
    border-color: #d6e8d3 !important;
    background: #f2f8f1 !important;
    color: #315b2e !important;
}

body.dm-bulk-domain-converted-1254 .alert-error,
body.dm-bulk-domain-converted-1254 .alert-danger {
    border-color: #e2b9b8 !important;
    background: #fbefef !important;
    color: #7e3432 !important;
}

@media (max-width: 991px) {
    body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .list-group {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}

@media (max-width: 767px) {
    body.dm-bulk-domain-converted-1254 .dm-bulk-domain-intro {
        align-items: flex-start !important;
        flex-direction: column !important;
    }

    body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card .list-group {
        grid-template-columns: minmax(0, 1fr) !important;
        row-gap: 2px !important;
    }

    body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card > .card-body {
        padding-right: 14px !important;
        padding-left: 14px !important;
    }

    body.dm-bulk-domain-converted-1254 .dm-bulk-domain-card h3.card-title {
        margin-right: -14px !important;
        margin-left: -14px !important;
    }
}
</style>
<script id="domainmonger-bulk-domain-converted-1254-js">
(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    }

    ready(function () {
        var form = document.querySelector('form[action*="action=bulkdomain"]');
        var card;
        var updateInput;
        var update;
        var titleMap;
        var title;
        var domainInputs;
        var intro;
        var parent;

        if (!form) {
            return;
        }

        document.body.classList.add('dm-bulk-domain-converted-1254');

        /*
         * Force these two controls to remain native DOM inputs with our custom
         * CSS. If a legacy iCheck wrapper exists, destroy/unwrap it first so
         * no blue or dark square can be painted around the orange selector.
         */
        ['nsChoiceDefault', 'nsChoiceCustom'].forEach(function (id) {
            var radio = document.getElementById(id);
            var wrapper;

            if (!radio) {
                return;
            }

            radio.classList.add('no-icheck', 'dm-bulk-nameserver-radio');

            if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.iCheck === 'function') {
                try {
                    window.jQuery(radio).iCheck('destroy');
                } catch (ignore) {
                    /* The radio was not initialized by iCheck. */
                }
            }

            wrapper = radio.parentElement;
            if (wrapper && /(?:^|\s)iradio_(?:square|flat)-blue(?:\s|$)/.test(wrapper.className || '')) {
                wrapper.parentNode.insertBefore(radio, wrapper);
                wrapper.parentNode.removeChild(wrapper);
            }
        });

        card = form.closest('.card');
        if (card) {
            card.classList.add('dm-bulk-domain-card');
        }

        updateInput = form.querySelector('input[name="update"]');
        update = updateInput ? String(updateInput.value || '').toLowerCase() : '';
        titleMap = {
            nameservers: 'Manage Nameservers',
            contactinfo: 'Edit Contact Information',
            autorenew: 'Auto Renewal Status',
            reglock: 'Registrar Lock Status'
        };
        title = titleMap[update] || 'Manage Selected Domains';
        domainInputs = form.querySelectorAll('input[name="domids[]"]');

        if (card && !document.querySelector('.dm-bulk-domain-intro')) {
            intro = document.createElement('div');
            intro.className = 'dm-bulk-domain-intro';
            intro.innerHTML = '<div><strong>' + title + '</strong>'
                + '<span>Review and apply this change to the selected domains.</span></div>'
                + '<em>' + domainInputs.length + (domainInputs.length === 1 ? ' domain' : ' domains') + '</em>';
            parent = card.parentNode;
            parent.insertBefore(intro, card);
        }
    });
}());
</script>
HTML;
});

/*
 * Patch 1261: Refine the converted bulk WHOIS Contact Information form to match
 * the confirmed single-domain WHOIS contact interface.
 *
 * This is visual/client-side only. WHMCS keeps handling the existing
 * contactinfo bulk form, validation, registrar calls, and save response.
 */
add_hook('ClientAreaHeadOutput', 3, static function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $isBulkDomain = ($templateFile === 'bulkdomainmanagement')
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=bulkdomain(?:&|$)/i', $requestUri));

    if (!$isBulkDomain) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-bulk-contact-match-whois-1256-css">
body.dm-bulk-contact-converted-1256 {
    --dm-bulk-contact-navy: #163a5f;
    --dm-bulk-contact-navy-hover: #214e7a;
    --dm-bulk-contact-orange: #f58220;
    --dm-bulk-contact-orange-soft: #d8741f;
    --dm-bulk-contact-text: #293f56;
    --dm-bulk-contact-muted: #60738a;
    --dm-bulk-contact-border: rgba(17, 43, 77, 0.14);
    --dm-bulk-contact-border-soft: rgba(17, 43, 77, 0.08);
    --dm-bulk-contact-shadow: 0 2px 8px rgba(17, 43, 77, 0.055);
}

/* Keep the existing WHMCS bulk form, but remove the narrow/scrolling shell. */
body.dm-bulk-contact-converted-1256 .dm-bulk-domain-card,
body.dm-bulk-contact-converted-1256 .dm-bulk-domain-card > .card-body,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 .tab-content,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 .tab-pane {
    min-width: 0 !important;
    max-width: 100% !important;
    overflow-x: visible !important;
}

/* Four equal connected role tabs, matching the confirmed single-domain WHOIS page. */
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 > ul.nav-tabs,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 > .responsive-tabs-sm {
    display: flex !important;
    flex-wrap: nowrap !important;
    gap: 0 !important;
    margin: 8px 0 10px !important;
    padding: 0 !important;
    overflow: hidden !important;
    border: 1px solid var(--dm-bulk-contact-border) !important;
    border-radius: 8px !important;
    background: #ffffff !important;
    box-shadow: var(--dm-bulk-contact-shadow) !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 > ul.nav-tabs > .nav-item,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 > .responsive-tabs-sm > .nav-item {
    flex: 1 1 25% !important;
    min-width: 0 !important;
    margin: 0 !important;
}

body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-link,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-link {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    min-height: 42px !important;
    margin: 0 !important;
    padding: 11px 8px !important;
    border: 0 !important;
    border-right: 1px solid var(--dm-bulk-contact-border-soft) !important;
    border-radius: 0 !important;
    background: #ffffff !important;
    color: var(--dm-bulk-contact-navy) !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    text-align: center !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    box-shadow: none !important;
}

body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-item:last-child .nav-link,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-item:last-child .nav-link {
    border-right: 0 !important;
}

body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-link:hover,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-link:focus,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-link:hover,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-link:focus {
    background: #fff4eb !important;
    color: var(--dm-bulk-contact-orange-soft) !important;
    outline: 0 !important;
    box-shadow: inset 0 0 0 3px rgba(245, 130, 32, 0.16) !important;
}

body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-link.active,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-link.active:hover,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-link.active:focus,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-item.active > .nav-link,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-link[aria-selected="true"],
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-link.active,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-link.active:hover,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-link.active:focus,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-item.active > .nav-link,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-link[aria-selected="true"] {
    background: var(--dm-bulk-contact-orange) !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    box-shadow: none !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 > .responsive-tabs-sm-connector {
    display: none !important;
}

/* Purple outline area: active tab can be copied to the other three roles. */
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-same-wrap-1256 {
    margin: 0 0 12px !important;
    padding: 11px 13px !important;
    border: 1px solid var(--dm-bulk-contact-border-soft) !important;
    border-radius: 8px !important;
    background: #f8fafc !important;
    color: var(--dm-bulk-contact-navy) !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-same-wrap-1256 label {
    display: flex !important;
    align-items: flex-start !important;
    gap: 9px !important;
    margin: 0 !important;
    color: var(--dm-bulk-contact-navy) !important;
    cursor: pointer !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    line-height: 1.15 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-same-wrap-1256 small {
    display: block !important;
    margin-top: 3px !important;
    color: var(--dm-bulk-contact-muted) !important;
    font-size: 11px !important;
    font-weight: 400 !important;
    line-height: 1.4 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-same-wrap-1256 input[type="checkbox"] {
    -webkit-appearance: none !important;
    appearance: none !important;
    flex: 0 0 15px !important;
    width: 15px !important;
    min-width: 15px !important;
    height: 15px !important;
    min-height: 15px !important;
    margin: 1px 0 0 !important;
    padding: 0 !important;
    border: 2px solid var(--dm-bulk-contact-orange) !important;
    border-radius: 3px !important;
    background: #ffffff !important;
    box-shadow: none !important;
    cursor: pointer !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-same-wrap-1256 input[type="checkbox"]:checked {
    border-color: var(--dm-bulk-contact-orange) !important;
    background-color: var(--dm-bulk-contact-orange) !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M4 8.2l2.4 2.4L12 5'/%3E%3C/svg%3E") !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    background-size: 11px 11px !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-same-wrap-1256 input[type="checkbox"]:focus {
    outline: 0 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.22) !important;
}

/* White contact card beneath the tabs and copy option. */
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 > .tab-content {
    margin: 0 !important;
    padding: 16px !important;
    border: 1px solid var(--dm-bulk-contact-border) !important;
    border-radius: 8px !important;
    background: #ffffff !important;
    box-shadow: var(--dm-bulk-contact-shadow) !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-pane-intro-1256 {
    margin: 0 0 12px !important;
    padding: 10px 12px !important;
    border: 1px solid rgba(245, 130, 32, 0.25) !important;
    border-radius: 8px !important;
    background: #fff8f1 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-pane-intro-1256 strong {
    display: block !important;
    margin: 0 0 3px !important;
    color: var(--dm-bulk-contact-navy) !important;
    font-size: 13px !important;
    line-height: 1.3 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-pane-intro-1256 span {
    display: block !important;
    color: var(--dm-bulk-contact-muted) !important;
    font-size: 11px !important;
    line-height: 1.4 !important;
}

/* Green outline area: existing contact / choose contact / custom information. */
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-source-stack-1256 {
    margin: 0 0 12px !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-source-stack-1256 > .form-check {
    margin: 0 0 9px !important;
    padding: 10px 12px !important;
    border: 1px solid var(--dm-bulk-contact-border-soft) !important;
    border-radius: 8px !important;
    background: #f8fafc !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-source-stack-1256 > .row {
    margin: 0 0 9px !important;
    padding: 0 12px !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-source-stack-1256 > .row > [class*="col-"] {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    margin-left: 0 !important;
    padding-right: 0 !important;
    padding-left: 0 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-source-stack-1256 .form-group {
    margin: 0 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-source-stack-1256 label,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 .tab-pane > .form-group > label {
    color: var(--dm-bulk-contact-text) !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}

/* Keep radio controls and their text on one line. Bootstrap's native
 * absolute-positioned .form-check-input was placing the label over the circle. */
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-contact-source-stack-1256 > .form-check {
    display: flex !important;
    align-items: center !important;
    gap: 7px !important;
    min-height: 38px !important;
    padding-left: 12px !important;
}

body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-contact-source-stack-1256 > .form-check > label,
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-contact-source-stack-1256 > .form-check > .form-check-label {
    position: static !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    margin: 0 !important;
    padding: 0 !important;
    text-indent: 0 !important;
    line-height: 1.3 !important;
}

/* Orange radio ring and selected dot, matching the single-domain page. */
body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-contact-source-stack-1256 input[type="radio"] {
    -webkit-appearance: none !important;
    appearance: none !important;
    position: static !important;
    inset: auto !important;
    float: none !important;
    transform: none !important;
    display: inline-block !important;
    flex: 0 0 14px !important;
    width: 14px !important;
    min-width: 14px !important;
    height: 14px !important;
    min-height: 14px !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 1.5px solid var(--dm-bulk-contact-orange) !important;
    border-radius: 50% !important;
    background: #ffffff !important;
    box-shadow: none !important;
    outline: 0 !important;
    cursor: pointer !important;
    vertical-align: -2px !important;
}

body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-contact-source-stack-1256 input[type="radio"]:checked {
    border-color: var(--dm-bulk-contact-orange) !important;
    background: radial-gradient(circle at center, var(--dm-bulk-contact-orange) 0 38%, transparent 42%) !important;
}

body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-contact-source-stack-1256 input[type="radio"]:focus {
    outline: 0 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.22) !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 .form-control,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 .custom-select,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 input[type="text"] {
    width: 100% !important;
    max-width: 100% !important;
    min-height: 38px !important;
    border: 1px solid rgba(17, 43, 77, 0.20) !important;
    border-radius: 6px !important;
    background-color: #ffffff !important;
    color: var(--dm-bulk-contact-text) !important;
    box-shadow: none !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 .form-control:focus,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 .custom-select:focus,
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 input[type="text"]:focus {
    border-color: var(--dm-bulk-contact-orange) !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.18) !important;
}

/* Standalone section header matching the confirmed single-domain WHOIS page. */
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-section-header-1258 {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 12px 18px !important;
    width: 100% !important;
    margin: 0 0 10px !important;
    padding: 10px 14px !important;
    border: 0 !important;
    border-radius: 5px !important;
    background: var(--dm-bulk-contact-navy) !important;
    color: #ffffff !important;
    box-shadow: none !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-section-header-1258 > div {
    min-width: 0 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-section-header-1258 strong {
    display: block !important;
    margin: 0 0 2px !important;
    color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-section-header-1258 span {
    display: block !important;
    margin: 0 !important;
    color: #ffffff !important;
    font-size: 10px !important;
    font-weight: 500 !important;
    line-height: 1.25 !important;
}

/* Keep the WHOIS header text-only. Put the count on the same line as the
 * selected-domain explanation, directly above the three-column list. */
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-domain-summary-row-1261 {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    width: 100% !important;
    margin: 0 0 4px !important;
    padding: 0 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-domain-summary-row-1261 > p {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    margin: 0 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-domain-summary-row-1261 em {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex: 0 0 auto !important;
    margin: 0 !important;
    padding: 4px 10px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: var(--dm-bulk-contact-orange-soft) !important;
    color: #ffffff !important;
    box-shadow: none !important;
    font-size: 11px !important;
    font-style: normal !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
    cursor: default !important;
    pointer-events: none !important;
}

@media (max-width: 575px) {
    body.dm-bulk-contact-converted-1256 .dm-bulk-contact-domain-summary-row-1261 {
        align-items: flex-start !important;
        flex-direction: column !important;
        gap: 5px !important;
    }
}

/* The original title was inside the card and supplied its top spacing. */
body.dm-bulk-contact-converted-1256 .dm-bulk-domain-card > .card-body {
    padding-top: 16px !important;
}

@media (max-width: 575px) {
    body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .nav-tabs .nav-link,
    body.dm-bulk-contact-converted-1256 #main-body .dm-bulk-domain-card .dm-bulk-contact-form-1256 .responsive-tabs-sm .nav-link {
        min-height: 38px !important;
        padding: 9px 4px !important;
        font-size: 11px !important;
    }

    body.dm-bulk-contact-converted-1256 .dm-bulk-contact-form-1256 > .tab-content {
        padding: 12px !important;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1000, static function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $isBulkDomain = ($templateFile === 'bulkdomainmanagement')
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=bulkdomain(?:&|$)/i', $requestUri));

    if (!$isBulkDomain) {
        return '';
    }

    return <<<'HTML'
<script id="domainmonger-bulk-contact-match-whois-1256-js">
(function () {
    'use strict';

    if (window.domainmongerBulkContact1256Loaded) {
        return;
    }
    window.domainmongerBulkContact1256Loaded = true;

    var orderedRoles = ['registrant', 'admin', 'billing', 'technical'];
    var roleLabels = {
        registrant: 'Registrant',
        admin: 'Admin',
        billing: 'Billing',
        technical: 'Technical'
    };

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    }

    function normalize(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function roleFromText(value) {
        var text = normalize(value);

        if (text.indexOf('registrant') !== -1) {
            return 'registrant';
        }
        if (text.indexOf('billing') !== -1) {
            return 'billing';
        }
        if (text.indexOf('technical') !== -1 || /\btech\b/.test(text)) {
            return 'technical';
        }
        if (text.indexOf('administrative') !== -1 || text.indexOf('administrator') !== -1 || /\badmin\b/.test(text)) {
            return 'admin';
        }

        return '';
    }

    function cssEscape(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }
        return String(value || '').replace(/[^a-zA-Z0-9_-]/g, '\\$&');
    }

    function dispatchChange(element) {
        var event;

        if (!element) {
            return;
        }

        if (typeof Event === 'function') {
            event = new Event('change', { bubbles: true });
        } else {
            event = document.createEvent('Event');
            event.initEvent('change', true, false);
        }

        element.dispatchEvent(event);
    }

    function tabRole(item) {
        var link = item && item.matches && item.matches('.nav-link')
            ? item
            : item && item.querySelector
                ? item.querySelector('.nav-link, a[data-toggle="tab"], a[href^="#tab"]')
                : null;

        return roleFromText(link ? link.textContent : item ? item.textContent : '');
    }

    function paneForTab(item) {
        var link = item && item.matches && item.matches('.nav-link')
            ? item
            : item && item.querySelector
                ? item.querySelector('.nav-link, a[data-toggle="tab"], a[href^="#tab"]')
                : null;
        var target;

        if (!link) {
            return null;
        }

        target = link.getAttribute('href') || link.getAttribute('data-target') || '';
        if (!target || target.charAt(0) !== '#') {
            return null;
        }

        try {
            return document.querySelector(target);
        } catch (ignore) {
            return null;
        }
    }

    function reorderTabs(form) {
        var nav = form.querySelector(':scope > ul.nav-tabs, :scope > .responsive-tabs-sm');
        var content = form.querySelector(':scope > .tab-content');
        var items;
        var activeRole = '';

        if (!nav || nav.getAttribute('data-dm-bulk-contact-ordered') === '1') {
            return;
        }

        items = Array.prototype.slice.call(nav.children).filter(function (child) {
            return child && child.querySelector && child.querySelector('.nav-link, a[data-toggle="tab"], a[href^="#tab"]');
        });

        items.forEach(function (item) {
            var link = item.querySelector('.nav-link, a[data-toggle="tab"], a[href^="#tab"]');
            var role = tabRole(item);

            if (link && link.classList.contains('active')) {
                activeRole = role;
            }

            if (link && role === 'admin') {
                link.textContent = 'Admin';
            }
        });

        items.sort(function (a, b) {
            var aIndex = orderedRoles.indexOf(tabRole(a));
            var bIndex = orderedRoles.indexOf(tabRole(b));
            return (aIndex === -1 ? 99 : aIndex) - (bIndex === -1 ? 99 : bIndex);
        });

        items.forEach(function (item) {
            var pane = paneForTab(item);
            nav.appendChild(item);
            if (content && pane && pane.parentNode === content) {
                content.appendChild(pane);
            }
        });

        if (!activeRole) {
            items.some(function (item) {
                var role = tabRole(item);
                var link;
                var pane;

                if (role !== 'registrant') {
                    return false;
                }

                link = item.querySelector('.nav-link, a[data-toggle="tab"], a[href^="#tab"]');
                pane = paneForTab(item);
                if (link) {
                    link.classList.add('active');
                    link.setAttribute('aria-selected', 'true');
                }
                if (pane) {
                    pane.classList.add('show', 'active');
                }
                return true;
            });
        }

        nav.setAttribute('data-dm-bulk-contact-ordered', '1');
    }

    function activeRole(form) {
        var link = form.querySelector('.nav-tabs .nav-link.active, .responsive-tabs-sm .nav-link.active');
        var pane;
        var role = roleFromText(link ? link.textContent : '');

        if (role) {
            return role;
        }

        pane = form.querySelector('.tab-pane.show.active, .tab-pane.active');
        role = roleFromText(pane ? pane.id + ' ' + pane.textContent : '');
        return role || 'registrant';
    }

    function roleContactName(form, role) {
        var fields = form.querySelectorAll('[name^="contactdetails["]');
        var i;
        var match;

        for (i = 0; i < fields.length; i += 1) {
            match = String(fields[i].name || '').match(/^contactdetails\[([^\]]+)\]\[/);
            if (match && roleFromText(match[1]) === role) {
                return match[1];
            }
        }

        return '';
    }

    function roleRadio(form, role, mode) {
        var radios = form.querySelectorAll('input[type="radio"][name^="wc["]');
        var i;
        var match;

        for (i = 0; i < radios.length; i += 1) {
            match = String(radios[i].name || '').match(/^wc\[([^\]]+)\]$/);
            if (match && roleFromText(match[1]) === role && String(radios[i].value || '').toLowerCase() === mode) {
                return radios[i];
            }
        }

        return null;
    }

    function roleSelect(form, role) {
        var selects = form.querySelectorAll('select[name^="sel["]');
        var i;
        var match;

        for (i = 0; i < selects.length; i += 1) {
            match = String(selects[i].name || '').match(/^sel\[([^\]]+)\]$/);
            if (match && roleFromText(match[1]) === role) {
                return selects[i];
            }
        }

        return null;
    }

    function roleFields(form, role) {
        var contactName = roleContactName(form, role);
        var fields = {};
        var inputs;

        if (!contactName) {
            return fields;
        }

        inputs = form.querySelectorAll('[name^="contactdetails["]');
        Array.prototype.forEach.call(inputs, function (input) {
            var match = String(input.name || '').match(/^contactdetails\[([^\]]+)\]\[([^\]]+)\]$/);
            if (match && match[1] === contactName) {
                fields[match[2]] = input;
            }
        });

        return fields;
    }

    function selectMode(form, role, mode) {
        var radio = roleRadio(form, role, mode);

        if (!radio) {
            return;
        }

        radio.checked = true;

        if (mode === 'contact' && typeof window.useDefaultWhois === 'function') {
            try {
                window.useDefaultWhois(radio.id);
            } catch (ignoreDefault) {}
        }

        if (mode === 'custom' && typeof window.useCustomWhois === 'function') {
            try {
                window.useCustomWhois(radio.id);
            } catch (ignoreCustom) {}
        }

        dispatchChange(radio);
    }

    function otherRoles(sourceRole) {
        return orderedRoles.filter(function (role) {
            return role !== sourceRole;
        });
    }

    function copyActiveRole(form) {
        var sourceRole = activeRole(form);
        var sourceContactRadio = roleRadio(form, sourceRole, 'contact');
        var sourceMode = sourceContactRadio && sourceContactRadio.checked ? 'contact' : 'custom';
        var sourceSelect = roleSelect(form, sourceRole);
        var sourceFields = roleFields(form, sourceRole);

        otherRoles(sourceRole).forEach(function (targetRole) {
            var targetSelect;
            var targetFields;

            selectMode(form, targetRole, sourceMode);

            if (sourceMode === 'contact') {
                targetSelect = roleSelect(form, targetRole);
                if (sourceSelect && targetSelect) {
                    targetSelect.disabled = false;
                    targetSelect.value = sourceSelect.value;
                    dispatchChange(targetSelect);
                }
                return;
            }

            targetFields = roleFields(form, targetRole);
            Object.keys(sourceFields).forEach(function (fieldName) {
                if (!targetFields[fieldName]) {
                    return;
                }
                targetFields[fieldName].disabled = false;
                targetFields[fieldName].value = sourceFields[fieldName].value;
                dispatchChange(targetFields[fieldName]);
            });
        });
    }

    function sameContactLabel(sourceRole) {
        var targets = otherRoles(sourceRole).map(function (role) {
            return roleLabels[role];
        });

        return 'Use same contact for ' + targets[0] + ', ' + targets[1] + ' & ' + targets[2];
    }

    function updateSameContactText(form) {
        var sourceRole = activeRole(form);
        var label = document.getElementById('dmBulkSameContactLabel1256');
        var help = document.getElementById('dmBulkSameContactHelp1256');
        var checkbox = document.getElementById('dmBulkSameContact1256');

        if (label) {
            label.textContent = sameContactLabel(sourceRole);
        }
        if (help) {
            help.textContent = 'When checked, the ' + roleLabels[sourceRole] + ' tab is copied to the other contact tabs before saving.';
        }
        if (checkbox) {
            checkbox.setAttribute('data-dm-source-role', sourceRole);
        }
    }

    function addSameContact(form) {
        var content = form.querySelector(':scope > .tab-content');
        var wrap;
        var checkbox;

        if (!content || document.getElementById('dmBulkSameContact1256')) {
            return;
        }

        wrap = document.createElement('div');
        wrap.className = 'dm-bulk-contact-same-wrap-1256';
        wrap.innerHTML = ''
            + '<label for="dmBulkSameContact1256">'
            + '  <input id="dmBulkSameContact1256" type="checkbox" value="1">'
            + '  <span><span id="dmBulkSameContactLabel1256">Use same contact for Admin, Billing &amp; Technical</span>'
            + '  <small id="dmBulkSameContactHelp1256">When checked, the Registrant tab is copied to the other contact tabs before saving.</small></span>'
            + '</label>';

        content.parentNode.insertBefore(wrap, content);
        checkbox = document.getElementById('dmBulkSameContact1256');

        checkbox.addEventListener('change', function () {
            updateSameContactText(form);
            if (checkbox.checked) {
                copyActiveRole(form);
            }
        });

        form.addEventListener('submit', function () {
            updateSameContactText(form);
            if (checkbox.checked) {
                copyActiveRole(form);
            }
        }, true);

        form.addEventListener('input', function (event) {
            var pane = event.target && event.target.closest ? event.target.closest('.tab-pane') : null;
            if (checkbox.checked && pane && pane.classList.contains('active')) {
                copyActiveRole(form);
            }
        }, true);

        form.addEventListener('change', function (event) {
            var pane = event.target && event.target.closest ? event.target.closest('.tab-pane') : null;
            if (checkbox.checked && pane && pane.classList.contains('active')) {
                copyActiveRole(form);
            }
        }, true);

        updateSameContactText(form);
    }

    function addPaneEnhancements(form) {
        var panes = form.querySelectorAll(':scope > .tab-content > .tab-pane');

        Array.prototype.forEach.call(panes, function (pane) {
            var role = roleFromText((pane.id || '') + ' ' + (pane.getAttribute('aria-labelledby') || '')) || roleFromText(pane.textContent);
            var firstCheck;
            var chooseRow;
            var secondCheck;
            var stack;
            var intro;

            if (!role || pane.getAttribute('data-dm-bulk-contact-enhanced') === '1') {
                return;
            }

            firstCheck = pane.querySelector(':scope > .form-check');
            chooseRow = firstCheck ? firstCheck.nextElementSibling : null;
            secondCheck = chooseRow ? chooseRow.nextElementSibling : null;

            if (firstCheck && chooseRow && secondCheck && secondCheck.classList.contains('form-check')) {
                stack = document.createElement('div');
                stack.className = 'dm-bulk-contact-source-stack-1256';
                pane.insertBefore(stack, firstCheck);
                stack.appendChild(firstCheck);
                stack.appendChild(chooseRow);
                stack.appendChild(secondCheck);
            }

            intro = document.createElement('div');
            intro.className = 'dm-bulk-contact-pane-intro-1256';
            intro.innerHTML = '<strong>' + roleLabels[role] + ' contact</strong>'
                + '<span>Choose an existing account contact or specify custom information for this contact role.</span>';
            pane.insertBefore(intro, pane.firstChild);
            pane.setAttribute('data-dm-bulk-contact-enhanced', '1');
        });
    }

    function installSectionHeader(form) {
        var card = form.closest ? form.closest('.card') : null;
        var existing = document.querySelector('.dm-bulk-contact-section-header-1258');
        var duplicateIntro = document.querySelector('.dm-bulk-domain-intro');
        var originalTitle;
        var header;

        if (!card || !card.parentNode) {
            return;
        }

        /* Contact Information already has the WHOIS section header below.
         * Remove the generic bulk-action intro so the page has one header only. */
        if (duplicateIntro && duplicateIntro.parentNode) {
            duplicateIntro.parentNode.removeChild(duplicateIntro);
        }

        originalTitle = card.querySelector('h3.card-title');
        if (originalTitle && originalTitle.parentNode) {
            originalTitle.parentNode.removeChild(originalTitle);
        }

        if (existing) {
            return;
        }

        header = document.createElement('div');
        header.className = 'dm-bulk-contact-section-header-1258';
        header.setAttribute('role', 'heading');
        header.setAttribute('aria-level', '2');
        header.innerHTML = '<div><strong>WHOIS Contact Info</strong>'
            + '<span>Registrant, Admin, Billing &amp; Technical</span></div>';

        card.parentNode.insertBefore(header, card);
    }

    function installDomainCount(form) {
        var card = form.closest ? form.closest('.card') : null;
        var list;
        var existing;
        var row;
        var explanation;
        var paragraphs;
        var domainCount;
        var domainCountText;
        var badge;

        if (!card) {
            return;
        }

        existing = card.querySelector('.dm-bulk-contact-domain-summary-row-1261');
        if (existing) {
            return;
        }

        list = card.querySelector('ul.list-group, .list-group');
        if (!list || !list.parentNode) {
            return;
        }

        paragraphs = card.querySelectorAll('p');
        Array.prototype.some.call(paragraphs, function (paragraph) {
            var text = normalize(paragraph.textContent);
            if (text.indexOf('affect the following domains') !== -1
                || text.indexOf('following domains') !== -1) {
                explanation = paragraph;
                return true;
            }
            return false;
        });

        if (!explanation) {
            explanation = document.createElement('p');
            explanation.textContent = 'The changes made below will affect the following domains:';
        }

        domainCount = form.querySelectorAll('input[name="domids[]"]').length;
        domainCountText = domainCount + (domainCount === 1 ? ' domain selected' : ' domains selected');

        row = document.createElement('div');
        row.className = 'dm-bulk-contact-domain-summary-row-1261';
        list.parentNode.insertBefore(row, list);
        row.appendChild(explanation);

        badge = document.createElement('em');
        badge.textContent = domainCountText;
        row.appendChild(badge);
    }

    function installTabEvents(form) {
        var nav = form.querySelector(':scope > ul.nav-tabs, :scope > .responsive-tabs-sm');

        if (!nav || nav.getAttribute('data-dm-bulk-contact-events') === '1') {
            return;
        }

        nav.addEventListener('click', function () {
            window.setTimeout(function () {
                updateSameContactText(form);
            }, 50);
            window.setTimeout(function () {
                updateSameContactText(form);
            }, 220);
        });

        if (window.jQuery) {
            try {
                window.jQuery(nav).on('shown.bs.tab', 'a[data-toggle="tab"]', function () {
                    updateSameContactText(form);
                });
            } catch (ignoreJquery) {}
        }

        nav.setAttribute('data-dm-bulk-contact-events', '1');
    }


    function build() {
        var form = document.querySelector('form[action*="action=bulkdomain"]');
        var updateInput;
        var update;

        if (!form) {
            return;
        }

        updateInput = form.querySelector('input[name="update"]');
        update = updateInput ? normalize(updateInput.value) : '';
        if (update !== 'contactinfo') {
            return;
        }

        document.body.classList.add('dm-bulk-contact-converted-1256');
        form.classList.add('dm-bulk-contact-form-1256');

        installSectionHeader(form);
        installDomainCount(form);
        reorderTabs(form);
        addPaneEnhancements(form);
        addSameContact(form);
        installTabEvents(form);
        updateSameContactText(form);
    }

    ready(build);
    window.setTimeout(build, 180);
    window.setTimeout(build, 650);
}());
</script>
HTML;
});

/*
 * Patch 1269: Apply the confirmed bulk WHOIS page structure to the remaining
 * bulk-domain pages without replacing WHMCS's native bulk processing.
 *
 * Applies to:
 * - Manage Nameservers
 * - Auto Renewal Status
 * - Registrar Lock Status
 *
 * The native forms, registrar calls, success/error handling, and selected
 * domids[] values remain unchanged.
 */
add_hook('ClientAreaHeadOutput', 4, static function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $isBulkDomain = ($templateFile === 'bulkdomainmanagement')
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=bulkdomain(?:&|$)/i', $requestUri));

    if (!$isBulkDomain) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-bulk-standard-layout-1263-css">
body.dm-bulk-standard-layout-1263 {
    --dm-bulk-standard-navy: #163a5f;
    --dm-bulk-standard-orange: #f58220;
    --dm-bulk-standard-orange-soft: #d8741f;
    --dm-bulk-standard-muted: #60738a;
    --dm-bulk-standard-border: rgba(17, 43, 77, 0.14);
}

/* The title is now outside the card, so restore normal top padding. */
body.dm-bulk-standard-layout-1263 .dm-bulk-domain-card > .card-body {
    padding-top: 20px !important;
}

body.dm-bulk-standard-layout-1263 .dm-bulk-standard-section-header-1263 {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 16px !important;
    width: 100% !important;
    margin: 0 0 12px !important;
    padding: 13px 18px !important;
    border: 0 !important;
    border-radius: 5px !important;
    background: var(--dm-bulk-standard-navy) !important;
    color: #ffffff !important;
    box-shadow: none !important;
}

body.dm-bulk-standard-layout-1263 .dm-bulk-standard-section-header-1263 > div {
    display: flex !important;
    flex-direction: column !important;
    gap: 2px !important;
    min-width: 0 !important;
}

body.dm-bulk-standard-layout-1263 .dm-bulk-standard-section-header-1263 strong {
    display: block !important;
    margin: 0 0 2px !important;
    color: #ffffff !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.25 !important;
}

body.dm-bulk-standard-layout-1263 .dm-bulk-standard-section-header-1263 span {
    display: block !important;
    margin: 0 !important;
    color: rgba(255, 255, 255, 0.88) !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.35 !important;
}

body.dm-bulk-standard-layout-1263 .dm-bulk-standard-domain-summary-row-1263 {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 16px !important;
    margin: 0 0 4px !important;
}

body.dm-bulk-standard-layout-1263 .dm-bulk-standard-domain-summary-row-1263 > p {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    margin: 0 !important;
    color: #424c57 !important;
}

body.dm-bulk-standard-layout-1263 .dm-bulk-standard-domain-summary-row-1263 em {
    flex: 0 0 auto !important;
    margin: 0 !important;
    padding: 5px 10px !important;
    border-radius: 999px !important;
    background: var(--dm-bulk-standard-orange-soft) !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-style: normal !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
    cursor: default !important;
    pointer-events: none !important;
    box-shadow: none !important;
}

/*
 * Auto Renewal and Registrar Lock each begin with two related explanatory
 * paragraphs. Keep them visually grouped instead of inheriting Bootstrap's
 * full paragraph margin between the two lines.
 */
body.dm-bulk-standard-layout-1263 #main-body form[action*="action=bulkdomain"] > .dm-bulk-standard-info-copy-1269 {
    margin: 0 0 12px !important;
    padding: 0 !important;
}

body.dm-bulk-standard-layout-1263 #main-body form[action*="action=bulkdomain"] > .dm-bulk-standard-info-copy-1269 > p {
    margin: 0 !important;
    padding: 0 !important;
    line-height: 1.35 !important;
}

body.dm-bulk-standard-layout-1263 #main-body form[action*="action=bulkdomain"] > .dm-bulk-standard-info-copy-1269 > p + p {
    margin-top: 0 !important;
}

/* The compact three-column domain list remains directly below the summary. */
body.dm-bulk-standard-layout-1263 .dm-bulk-standard-domain-summary-row-1263 + .list-group {
    margin-top: 2px !important;
}

@media (max-width: 767px) {
    body.dm-bulk-standard-layout-1263 .dm-bulk-standard-domain-summary-row-1263 {
        align-items: flex-start !important;
        flex-direction: column !important;
        gap: 7px !important;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 98, static function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $isBulkDomain = ($templateFile === 'bulkdomainmanagement')
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=bulkdomain(?:&|$)/i', $requestUri));

    if (!$isBulkDomain) {
        return '';
    }

    return <<<'HTML'
<script id="domainmonger-bulk-standard-layout-1263-js">
(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    }

    function normalize(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    /*
     * Patch 1269: The Patch 1268 helper was accidentally declared inside the
     * separate Bulk Contact IIFE, so this standard bulk-page script could not
     * call it. Keep the helper in the same scope as build() and wrap the two
     * native Auto Renewal / Registrar Lock explanation paragraphs directly.
     */
    function installTightInfoCopy(form, update) {
        var directChildren;
        var infoParagraphs = [];
        var wrapper;
        var index;
        var child;
        var text;

        if (['autorenew', 'reglock'].indexOf(update) === -1
            || form.querySelector('.dm-bulk-standard-info-copy-1269')) {
            return;
        }

        directChildren = form.children;
        for (index = 0; index < directChildren.length; index += 1) {
            child = directChildren[index];
            if (!child || child.tagName !== 'P') {
                continue;
            }

            text = normalize(child.textContent);
            if (text.indexOf('affect the following domains') !== -1
                || text.indexOf('following domains') !== -1
                || text.indexOf('changes will affect') !== -1) {
                continue;
            }

            infoParagraphs.push(child);
            if (infoParagraphs.length === 2) {
                break;
            }
        }

        if (infoParagraphs.length !== 2 || !infoParagraphs[0].parentNode) {
            return;
        }

        wrapper = document.createElement('div');
        wrapper.className = 'dm-bulk-standard-info-copy-1269';
        infoParagraphs[0].parentNode.insertBefore(wrapper, infoParagraphs[0]);
        wrapper.appendChild(infoParagraphs[0]);
        wrapper.appendChild(infoParagraphs[1]);
    }

    function findAffectParagraph(card, list) {
        var paragraphs = card.querySelectorAll('p');
        var match = null;

        Array.prototype.some.call(paragraphs, function (paragraph) {
            var text = normalize(paragraph.textContent);
            if (text.indexOf('affect the following domains') !== -1
                || text.indexOf('following domains') !== -1
                || text.indexOf('changes will affect') !== -1) {
                match = paragraph;
                return true;
            }
            return false;
        });

        if (!match && list && list.previousElementSibling
            && list.previousElementSibling.tagName === 'P') {
            match = list.previousElementSibling;
        }

        return match;
    }

    function installHeader(form, update) {
        var card = form.closest ? form.closest('.card') : null;
        var genericIntro = document.querySelector('.dm-bulk-domain-intro');
        var existing = document.querySelector('.dm-bulk-standard-section-header-1263');
        var originalTitle;
        var header;
        var config = {
            nameservers: {
                title: 'Manage Nameservers',
                subtitle: 'Use the default nameservers or specify custom nameservers for the selected domains.'
            },
            autorenew: {
                title: 'Auto Renewal Status',
                subtitle: 'Enable or disable automatic renewal for the selected domains.'
            },
            reglock: {
                title: 'Registrar Lock Status',
                subtitle: 'Enable or disable registrar lock for the selected domains.'
            }
        };

        if (!card || !card.parentNode || !config[update]) {
            return;
        }

        if (genericIntro && genericIntro.parentNode) {
            genericIntro.parentNode.removeChild(genericIntro);
        }

        originalTitle = card.querySelector('h3.card-title');
        if (originalTitle && originalTitle.parentNode) {
            originalTitle.parentNode.removeChild(originalTitle);
        }

        if (existing) {
            return;
        }

        header = document.createElement('div');
        header.className = 'dm-bulk-standard-section-header-1263';
        header.setAttribute('role', 'heading');
        header.setAttribute('aria-level', '2');
        header.innerHTML = '<div><strong>' + config[update].title + '</strong>'
            + '<span>' + config[update].subtitle + '</span></div>';
        card.parentNode.insertBefore(header, card);
    }

    function installDomainSummary(form) {
        var card = form.closest ? form.closest('.card') : null;
        var list;
        var explanation;
        var row;
        var badge;
        var domainCount;

        if (!card || card.querySelector('.dm-bulk-standard-domain-summary-row-1263')) {
            return;
        }

        list = card.querySelector('ul.list-group, .list-group');
        if (!list || !list.parentNode) {
            return;
        }

        explanation = findAffectParagraph(card, list);
        if (!explanation) {
            explanation = document.createElement('p');
            explanation.textContent = 'The changes made below will affect the following domains:';
        }

        domainCount = form.querySelectorAll('input[name="domids[]"]').length;

        row = document.createElement('div');
        row.className = 'dm-bulk-standard-domain-summary-row-1263';
        list.parentNode.insertBefore(row, list);
        row.appendChild(explanation);

        badge = document.createElement('em');
        badge.textContent = domainCount + (domainCount === 1 ? ' domain selected' : ' domains selected');
        row.appendChild(badge);
    }

    function build() {
        var form = document.querySelector('form[action*="action=bulkdomain"]');
        var updateInput;
        var update;

        if (!form) {
            return;
        }

        updateInput = form.querySelector('input[name="update"]');
        update = updateInput ? normalize(updateInput.value) : '';
        if (['nameservers', 'autorenew', 'reglock'].indexOf(update) === -1) {
            return;
        }

        document.body.classList.add('dm-bulk-standard-layout-1263');
        installHeader(form, update);
        installDomainSummary(form);
        installTightInfoCopy(form, update);
    }

    ready(build);
    window.setTimeout(build, 180);
    window.setTimeout(build, 650);
}());
</script>
HTML;
});
