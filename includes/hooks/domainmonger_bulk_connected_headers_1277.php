<?php
/**
 * DomainMonger bulk-domain full-width section headers.
 *
 * Patch 1293
 * - Keeps the confirmed connected bulk-header styling for layouts without a
 *   sidebar.
 * - On the four bulk-change pages with a left sidebar, moves the existing navy
 *   header above both the sidebar and main-content columns, matching Patch 1292.
 * - Applies to WHOIS Contact Info, Manage Nameservers, Auto Renewal Status,
 *   and Registrar Lock Status only.
 * - Does not alter form fields, selected domains, routes, or registrar actions.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmongerIsBulkDomainPage1293')) {
    function domainmongerIsBulkDomainPage1293(array $vars): bool
    {
        $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        return ($templateFile === 'bulkdomainmanagement')
            || (stripos($requestUri, 'clientarea.php') !== false
                && preg_match('/(?:\?|&)action=bulkdomain(?:&|$)/i', $requestUri));
    }
}

add_hook('ClientAreaHeadOutput', 120, static function ($vars) {
    $vars = is_array($vars) ? $vars : [];
    if (!domainmongerIsBulkDomainPage1293($vars)) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-bulk-connected-headers-1277-css">
/*
 * Without a sidebar, retain the confirmed connected header/card treatment.
 */
body:not(.dm-bulk-wide-header-1293).dm-bulk-contact-converted-1256
    .dm-bulk-contact-section-header-1258,
body:not(.dm-bulk-wide-header-1293).dm-bulk-standard-layout-1263
    .dm-bulk-standard-section-header-1263 {
    box-sizing: border-box !important;
    width: 100% !important;
    margin: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

body:not(.dm-bulk-wide-header-1293).dm-bulk-contact-converted-1256
    .dm-bulk-contact-section-header-1258 + .dm-bulk-domain-card,
body:not(.dm-bulk-wide-header-1293).dm-bulk-standard-layout-1263
    .dm-bulk-standard-section-header-1263 + .dm-bulk-domain-card {
    box-sizing: border-box !important;
    width: 100% !important;
    margin-top: 0 !important;
    border-top: 0 !important;
    border-radius: 0 0 5px 5px !important;
}

body:not(.dm-bulk-wide-header-1293).dm-bulk-contact-converted-1256
    .dm-bulk-contact-section-header-1258 + .dm-bulk-domain-card > .card-body,
body:not(.dm-bulk-wide-header-1293).dm-bulk-standard-layout-1263
    .dm-bulk-standard-section-header-1263 + .dm-bulk-domain-card > .card-body {
    border-radius: 0 0 5px 5px !important;
}

/*
 * Sidebar pages: the existing bulk section header spans the complete row above
 * both the sidebar and main content, matching the confirmed portal layout.
 */
body.dm-bulk-wide-header-1293 .dm-bulk-wide-header-wrap-1293 {
    box-sizing: border-box !important;
    flex: 0 0 100% !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 0 12px !important;
}

body.dm-bulk-wide-header-1293
    .dm-bulk-wide-header-wrap-1293 > .dm-bulk-contact-section-header-1258,
body.dm-bulk-wide-header-1293
    .dm-bulk-wide-header-wrap-1293 > .dm-bulk-standard-section-header-1263 {
    box-sizing: border-box !important;
    width: 100% !important;
    margin: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

/* The white form is now an independent card below the full-width header. */
body.dm-bulk-wide-header-1293 .dm-bulk-domain-card {
    margin-top: 0 !important;
    border: 1px solid #d8dee6 !important;
    border-radius: 5px !important;
}

body.dm-bulk-wide-header-1293 .dm-bulk-domain-card > .card-body {
    border-radius: 5px !important;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1200, static function ($vars) {
    $vars = is_array($vars) ? $vars : [];
    if (!domainmongerIsBulkDomainPage1293($vars)) {
        return '';
    }

    return <<<'HTML'
<script id="domainmonger-bulk-full-width-headers-1293-js">
(function () {
    'use strict';

    function findSidebarRow(primary) {
        var row;
        var children;
        var i;
        var child;

        if (!primary || !primary.parentElement) {
            return null;
        }

        row = primary.parentElement;
        if (!row.classList || !row.classList.contains('row')) {
            return null;
        }

        children = row.children || [];
        for (i = 0; i < children.length; i += 1) {
            child = children[i];
            if (child === primary
                || (child.classList && child.classList.contains('dm-bulk-wide-header-wrap-1293'))) {
                continue;
            }

            if ((child.classList && child.classList.contains('sidebar'))
                || (child.querySelector && child.querySelector('.sidebar'))) {
                return row;
            }
        }

        return null;
    }

    function moveBulkHeader() {
        var primary = document.querySelector('#main-body .primary-content');
        var header = document.querySelector(
            '.dm-bulk-contact-section-header-1258, .dm-bulk-standard-section-header-1263'
        );
        var row;
        var wrapper;

        if (!primary || !header) {
            return false;
        }

        row = findSidebarRow(primary);
        if (!row) {
            return false;
        }

        wrapper = row.querySelector(':scope > .dm-bulk-wide-header-wrap-1293');
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'col-12 dm-bulk-wide-header-wrap-1293';
            row.insertBefore(wrapper, row.firstChild);
        }

        if (header.parentNode !== wrapper) {
            wrapper.appendChild(header);
        }

        document.body.classList.add('dm-bulk-wide-header-1293');
        return true;
    }

    function start() {
        var attempts = 0;
        var timer;
        var observer;

        if (moveBulkHeader()) {
            return;
        }

        timer = window.setInterval(function () {
            attempts += 1;
            if (moveBulkHeader() || attempts >= 20) {
                window.clearInterval(timer);
            }
        }, 100);

        if (window.MutationObserver && document.body) {
            observer = new MutationObserver(function () {
                if (moveBulkHeader()) {
                    observer.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
            window.setTimeout(function () {
                observer.disconnect();
            }, 2500);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
}());
</script>
HTML;
});
