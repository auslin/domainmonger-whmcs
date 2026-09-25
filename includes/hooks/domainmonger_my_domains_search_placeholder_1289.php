<?php
/**
 * DomainMonger My Domains search placeholder.
 *
 * Patch 1290
 * - Replaces failed Patch 1289 JavaScript (extra closing parenthesis).
 * - Adds faded "Search for Domain" guidance to the My Domains table filter.
 * - Page-specific: clientareadomains / clientarea.php?action=domains only.
 * - Does not alter filtering, rows, checkboxes, sorting, pagination, or bulk actions.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 90, static function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $isDomainsList = in_array($templateFile, ['clientareadomains', 'clientareadomainsx'], true)
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=domains(?:&|$)/i', $requestUri));

    if (!$isDomainsList) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-my-domains-search-placeholder-1290-css">
body.whmcsbody #tableDomainsList_wrapper .dataTables_filter input::placeholder,
body.whmcsbody #tableDomainsList_wrapper .dataTables_filter input::-webkit-input-placeholder {
    color: #8a939d !important;
    opacity: 1 !important;
}
</style>
<script id="domainmonger-my-domains-search-placeholder-1290-js">
(function () {
    'use strict';

    if (window.domainmongerDomainsSearchPlaceholder1290Loaded) {
        return;
    }
    window.domainmongerDomainsSearchPlaceholder1290Loaded = true;

    function applyPlaceholder() {
        var inputs = document.querySelectorAll(
            '#tableDomainsList_wrapper .dataTables_filter input, ' +
            '#tableDomainsList_filter input, ' +
            '.dataTables_filter input[aria-controls="tableDomainsList"]'
        );

        if (!inputs.length) {
            return false;
        }

        inputs.forEach(function (input) {
            input.setAttribute('placeholder', 'Search for Domain');
            input.setAttribute('aria-label', 'Search for Domain');
        });

        return true;
    }

    function initialise() {
        applyPlaceholder();

        var attempts = 0;
        var timer = window.setInterval(function () {
            attempts += 1;
            if (applyPlaceholder() || attempts >= 100) {
                window.clearInterval(timer);
            }
        }, 100);

        var observer = new MutationObserver(function () {
            applyPlaceholder();
        });

        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });

        window.setTimeout(function () {
            observer.disconnect();
        }, 15000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, { once: true });
    } else {
        initialise();
    }

    document.addEventListener('draw.dt', applyPlaceholder, true);
}());
</script>
HTML;
});
