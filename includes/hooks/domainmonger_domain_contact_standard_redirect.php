<?php
/**
 * DomainMonger standard WHOIS contact route preference.
 *
 * Patch 777:
 * Use WHMCS's standard domain contact page instead of the ResellerClub
 * domainmanagement.php?action=domaincontacts page.
 *
 * This keeps existing confirmed fixes intact and avoids editing
 * public_html/manage/lang/overrides/english.php.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_standard_domain_contact_route_is_domainmanagement_contacts')) {
    function dm_standard_domain_contact_route_is_domainmanagement_contacts(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        return ($script === 'domainmanagement.php' || strpos($uri, '/manage/domainmanagement.php') !== false)
            && ($action === 'domaincontacts' || strpos($uri, 'action=domaincontacts') !== false);
    }
}

if (!function_exists('dm_standard_domain_contact_route_domainid')) {
    function dm_standard_domain_contact_route_domainid(): int
    {
        $domainId = (int) ($_REQUEST['domainid'] ?? $_REQUEST['id'] ?? 0);
        return $domainId > 0 ? $domainId : 0;
    }
}

if (!function_exists('dm_standard_domain_contact_route_url')) {
    function dm_standard_domain_contact_route_url(?int $domainId = null): string
    {
        $domainId = $domainId ?? dm_standard_domain_contact_route_domainid();
        $url = 'clientarea.php?action=domaincontacts';

        if ($domainId > 0) {
            $url .= '&domainid=' . $domainId;
        }

        return $url;
    }
}

/*
 * Server-side redirect when the old ResellerClub contact route is opened.
 */
add_hook('ClientAreaPage', 1, function ($vars) {
    if (!dm_standard_domain_contact_route_is_domainmanagement_contacts()) {
        return [];
    }

    $url = dm_standard_domain_contact_route_url();

    if (!headers_sent()) {
        header('Location: ' . $url, true, 302);
        exit;
    }

    return [];
});

/*
 * Client-side fallback:
 * - If something loads the old page after headers are already sent, redirect it.
 * - Rewrite rendered "Contact Information" links/forms/buttons that still point
 *   at domainmanagement.php?action=domaincontacts.
 */
add_hook('ClientAreaFooterOutput', 1, function () {
    $currentUrl = htmlspecialchars(dm_standard_domain_contact_route_url(), ENT_QUOTES, 'UTF-8');
    $shouldRedirect = dm_standard_domain_contact_route_is_domainmanagement_contacts() ? 'true' : 'false';

    return <<<HTML
<script id="dm-standard-domain-contact-route-js-v777">
(function () {
    'use strict';

    var shouldRedirect = {$shouldRedirect};
    var targetUrl = '{$currentUrl}';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function domainIdFromUrl(url) {
        var match = String(url || '').match(/[?&]domainid=([0-9]+)/i);
        return match ? match[1] : '';
    }

    function standardUrlFor(oldUrl) {
        var id = domainIdFromUrl(oldUrl) || domainIdFromUrl(window.location.href);
        return 'clientarea.php?action=domaincontacts' + (id ? '&domainid=' + id : '');
    }

    function rewriteOldContactLinks() {
        var links = document.querySelectorAll('a[href*="domainmanagement.php?action=domaincontacts"]');
        var forms = document.querySelectorAll('form[action*="domainmanagement.php?action=domaincontacts"], form[action*="domainmanagement.php"][action*="action=domaincontacts"]');
        var i;
        var href;
        var action;
        var target;

        for (i = 0; i < links.length; i += 1) {
            href = links[i].getAttribute('href') || '';
            links[i].setAttribute('href', standardUrlFor(href));
            links[i].setAttribute('data-dm-standard-contact-link-fixed', '1');
        }

        for (i = 0; i < forms.length; i += 1) {
            action = forms[i].getAttribute('action') || '';
            target = standardUrlFor(action);
            forms[i].setAttribute('action', target);
            forms[i].setAttribute('method', 'get');
            forms[i].setAttribute('data-dm-standard-contact-form-fixed', '1');
        }
    }

    ready(function () {
        rewriteOldContactLinks();

        if (shouldRedirect) {
            window.location.replace(targetUrl || standardUrlFor(window.location.href));
        }
    });

    window.setTimeout(rewriteOldContactLinks, 250);
    window.setTimeout(rewriteOldContactLinks, 750);
}());
</script>
HTML;
});
