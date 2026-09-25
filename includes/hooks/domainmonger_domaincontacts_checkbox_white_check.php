<?php
/**
 * DomainMonger patch 825.
 * Page-specific WHOIS Contact Info checkbox polish.
 *
 * Goal:
 * - Keep the existing orange checkbox on the domain contacts page.
 * - Force the selected checkmark to white so it matches the site style.
 *
 * Scope:
 * - clientarea.php?action=domaincontacts
 *
 * This does not touch language overrides, the Stellar integration folder,
 * or the Register Domain namespinner.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_domaincontacts_white_check_is_target')) {
    function dm_domaincontacts_white_check_is_target()
    {
        $script = strtolower(basename(parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: ''));
        $query = strtolower($_SERVER['QUERY_STRING'] ?? '');
        $uri = strtolower($_SERVER['REQUEST_URI'] ?? '');

        return $script === 'clientarea.php'
            && (strpos($query, 'action=domaincontacts') !== false || strpos($uri, 'action=domaincontacts') !== false);
    }
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    if (!dm_domaincontacts_white_check_is_target()) {
        return '';
    }

    return <<<'HTML'
<style>
/* Patch 825: WHOIS Contact Info same-contact checkbox white checkmark. */
body.whmcsbody #main-body input.dm-domaincontacts-same-contact-checkbox[type="checkbox"],
body.whmcsbody .main-content input.dm-domaincontacts-same-contact-checkbox[type="checkbox"] {
    -webkit-appearance: none !important;
    appearance: none !important;
    display: inline-block !important;
    position: relative !important;
    width: 14px !important;
    min-width: 14px !important;
    height: 14px !important;
    min-height: 14px !important;
    margin: 0 8px 0 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    vertical-align: -2px !important;
    border: 2px solid #f58220 !important;
    border-radius: 3px !important;
    background-color: #fff !important;
    background-image: none !important;
    cursor: pointer !important;
}

body.whmcsbody #main-body input.dm-domaincontacts-same-contact-checkbox[type="checkbox"]:checked,
body.whmcsbody .main-content input.dm-domaincontacts-same-contact-checkbox[type="checkbox"]:checked {
    background-color: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M4 8.2l2.4 2.4L12 5'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: 11px 11px !important;
}

body.whmcsbody #main-body input.dm-domaincontacts-same-contact-checkbox[type="checkbox"]:focus,
body.whmcsbody .main-content input.dm-domaincontacts-same-contact-checkbox[type="checkbox"]:focus {
    outline: none !important;
    box-shadow: 0 0 0 .18rem rgba(245, 130, 32, .24) !important;
}

/* If the current checkbox implementation uses generated content, keep that checkmark white too. */
body.whmcsbody #main-body .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked::before,
body.whmcsbody #main-body .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked::after,
body.whmcsbody #main-body .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked + label::before,
body.whmcsbody #main-body .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked + label::after,
body.whmcsbody #main-body .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked ~ label::before,
body.whmcsbody #main-body .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked ~ label::after,
body.whmcsbody .main-content .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked::before,
body.whmcsbody .main-content .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked::after,
body.whmcsbody .main-content .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked + label::before,
body.whmcsbody .main-content .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked + label::after,
body.whmcsbody .main-content .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked ~ label::before,
body.whmcsbody .main-content .dm-domaincontacts-same-contact-wrap input[type="checkbox"]:checked ~ label::after {
    color: #fff !important;
    border-color: #fff !important;
}

body.whmcsbody #main-body .dm-domaincontacts-same-contact-wrap .fa-check,
body.whmcsbody #main-body .dm-domaincontacts-same-contact-wrap .fas.fa-check,
body.whmcsbody .main-content .dm-domaincontacts-same-contact-wrap .fa-check,
body.whmcsbody .main-content .dm-domaincontacts-same-contact-wrap .fas.fa-check {
    color: #fff !important;
}
</style>
<script>
(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    function cleanText(value) {
        return String(value || '').replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function cssEscape(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }
        return String(value || '').replace(/[^a-zA-Z0-9_-]/g, '\\$&');
    }

    function labelFor(input) {
        var label = input.closest('label');
        if (label) {
            return label;
        }

        if (input.id) {
            label = document.querySelector('label[for="' + cssEscape(input.id) + '"]');
            if (label) {
                return label;
            }
        }

        return null;
    }

    function markSameContactCheckboxes() {
        var inputs = document.querySelectorAll('#main-body input[type="checkbox"], .main-content input[type="checkbox"]');

        Array.prototype.forEach.call(inputs, function (input) {
            var label = labelFor(input);
            var nearby = input.closest('.alert, .form-check, .checkbox, .custom-control, .row, .panel, .card, div') || input.parentNode;
            var text = cleanText((label ? label.textContent : '') + ' ' + (nearby ? nearby.textContent : ''));

            if (text.indexOf('use same contact') === -1) {
                return;
            }

            input.classList.add('dm-domaincontacts-same-contact-checkbox');

            if (nearby && nearby.classList) {
                nearby.classList.add('dm-domaincontacts-same-contact-wrap');
            }

            if (label && label.classList) {
                label.classList.add('dm-domaincontacts-same-contact-label');
            }
        });
    }

    ready(function () {
        markSameContactCheckboxes();
        window.setTimeout(markSameContactCheckboxes, 250);
        window.setTimeout(markSameContactCheckboxes, 900);
    });
}());
</script>
HTML;
});
