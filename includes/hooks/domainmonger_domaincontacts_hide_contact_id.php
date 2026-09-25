<?php
/**
 * DomainMonger WHOIS Contact Information Contact ID cleanup.
 *
 * Patch 804:
 * Replaces failed Patch 803 with a precise label-based version.
 *
 * Hides only the "Contact ID (information only)" field on the standard WHMCS
 * Contact Information page by hiding its closest .form-group. It does not
 * hide rows, columns, tab panes, or broader containers.
 *
 * Scope:
 * manage/clientarea.php?action=domaincontacts&domainid=...
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_domaincontacts_hide_contact_id_is_page')) {
    function dm_domaincontacts_hide_contact_id_is_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        return ($script === 'clientarea.php' || strpos($uri, '/manage/clientarea.php') !== false)
            && ($action === 'domaincontacts' || strpos($uri, 'action=domaincontacts') !== false);
    }
}

add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_domaincontacts_hide_contact_id_is_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-domaincontacts-hide-contact-id-style-v804">
#frmDomainContactModification .dm-domaincontacts-contact-id-field-hidden {
    display: none !important;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_domaincontacts_hide_contact_id_is_page()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-domaincontacts-hide-contact-id-js-v804">
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function normalize(text) {
        return String(text || '')
            .replace(/\u00a0/g, ' ')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function isContactIdText(text) {
        var t = normalize(text);

        return t === 'contact id'
            || t === 'contact id (information only)'
            || (t.indexOf('contact id') !== -1 && t.indexOf('information only') !== -1);
    }

    function isContactIdInput(input) {
        var name = String(input && input.name ? input.name : '');
        var match = name.match(/^contactdetails\[[^\]]+\]\[([^\]]+)\]$/i);
        var fieldName = '';

        if (!match) {
            return false;
        }

        fieldName = normalize(match[1]);

        return isContactIdText(fieldName);
    }

    function hideFieldGroupFromElement(el) {
        var group;
        var input;

        if (!el) {
            return false;
        }

        group = el.closest ? el.closest('.form-group') : null;

        /*
         * Safety guard: only hide a .form-group. Never hide broad layout
         * containers like .row, .col-md-6, .tab-pane, or .tab-content.
         */
        if (!group || !group.classList || !group.classList.contains('form-group')) {
            return false;
        }

        input = group.querySelector('input, select, textarea');

        if (input && input.tagName && input.tagName.toLowerCase() === 'input') {
            input.type = 'hidden';
            input.setAttribute('data-dm-contact-id-hidden', '1');
        }

        group.classList.add('dm-domaincontacts-contact-id-field-hidden');

        return true;
    }

    function hideContactIdFields() {
        var form = document.getElementById('frmDomainContactModification');
        var labels;
        var inputs;
        var i;

        if (!form) {
            return;
        }

        /*
         * Primary targeting: label text exactly/clearly says Contact ID.
         */
        labels = form.querySelectorAll('label');

        for (i = 0; i < labels.length; i += 1) {
            if (isContactIdText(labels[i].textContent)) {
                hideFieldGroupFromElement(labels[i]);
            }
        }

        /*
         * Fallback targeting: field name third bracket is Contact ID or
         * Contact ID (information only).
         */
        inputs = form.querySelectorAll('input[name^="contactdetails["]');

        for (i = 0; i < inputs.length; i += 1) {
            if (isContactIdInput(inputs[i])) {
                hideFieldGroupFromElement(inputs[i]);
            }
        }
    }

    ready(hideContactIdFields);
    window.setTimeout(hideContactIdFields, 150);
    window.setTimeout(hideContactIdFields, 500);
    window.setTimeout(hideContactIdFields, 1000);
}());
</script>
HTML;
});
