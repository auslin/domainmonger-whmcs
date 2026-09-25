<?php
/**
 * DomainMonger WHMCS v9 — Register DNS native prerender navigation 1619
 *
 * Purpose:
 * - Preserve normal full-document WHMCS execution for Register DNS pages.
 * - Prepare the next safe Register DNS display page in the browser before the
 *   user activates its link, allowing Chrome to swap to the fully initialized
 *   native document instead of exposing a white transition frame.
 * - Avoid the fetch/DOM/script replay design from Patch 1616 that caused hangs.
 *
 * Covered GET display routes:
 * - DNS Records
 * - DNSSEC
 * - Domain Forwarding
 * - Email Forwarding
 *
 * Safety:
 * - Does not intercept clicks or replace DOM content.
 * - Does not replay scripts, observers, or timers.
 * - Does not prerender save/delete/apply/download/activation URLs.
 * - Browsers that do not support Speculation Rules retain ordinary navigation.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_register_dns_prerender_1619_is_display_route')) {
    function dm_register_dns_prerender_1619_is_display_route(): bool
    {
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
            return false;
        }

        $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
        $action = strtolower((string) ($_GET['action'] ?? ''));

        if ($script === 'clientarea.php' && $action === 'domaindns') {
            return !isset($_REQUEST['dmplainnative'])
                && !isset($_REQUEST['dmnativeold'])
                && !isset($_REQUEST['dmfeednative']);
        }

        if ($script === 'clientarea.php' && $action === 'domainemailforwarding') {
            return true;
        }

        if ($script === 'dnsmanagement.php' && in_array($action, [
            'dnsseczone',
            'managednszone',
        ], true)) {
            $recordType = strtoupper((string) ($_REQUEST['nsrecordtype'] ?? ''));
            $section = strtolower((string) ($_REQUEST['dmsection'] ?? ''));

            return $action === 'dnsseczone'
                || $recordType === 'DNSSEC'
                || $section === 'dnssec';
        }

        if ($script === 'domainforwarding.php' && $action === 'managedomfwd') {
            return true;
        }

        if ($script === 'emailmanagement.php' && $action === 'manageemails') {
            return true;
        }

        return false;
    }
}

add_hook('ClientAreaFooterOutput', 10022, static function ($vars) {
    if (!dm_register_dns_prerender_1619_is_display_route()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-register-dns-prerender-navigation-1619-script">
(function () {
    'use strict';

    if (window.dmRegisterDnsPrerender1619) {
        return;
    }

    var installedSignature = '';
    var ruleElement = null;
    var refreshTimer = null;
    var lateScanTimers = [];

    function safeUrl(value, base) {
        try {
            return new URL(String(value || ''), base || window.location.href);
        } catch (error) {
            return null;
        }
    }

    function comparableUrl(value) {
        var url = safeUrl(value);
        if (!url) {
            return '';
        }
        url.hash = '';
        return url.href;
    }

    function hasBlockedParameters(url) {
        var blocked = [
            'sub', 'save', 'submit', 'delete', 'remove', 'confirm', 'token',
            'activate', 'deactivate', 'disable', 'enable', 'download',
            'doaction', 'apply', 'bulk', 'ajax'
        ];

        for (var index = 0; index < blocked.length; index += 1) {
            if (url.searchParams.has(blocked[index])) {
                return true;
            }
        }

        return false;
    }

    function isAllowedDisplayRoute(value) {
        var url = safeUrl(value);
        if (!url || url.origin !== window.location.origin || hasBlockedParameters(url)) {
            return false;
        }

        var file = String(url.pathname.split('/').pop() || '').toLowerCase();
        var action = String(url.searchParams.get('action') || '').toLowerCase();

        if (file === 'clientarea.php' && action === 'domaindns') {
            return !url.searchParams.has('dmplainnative')
                && !url.searchParams.has('dmnativeold')
                && !url.searchParams.has('dmfeednative');
        }

        if (file === 'clientarea.php' && action === 'domainemailforwarding') {
            return true;
        }

        if (file === 'dnsmanagement.php'
            && (action === 'dnsseczone' || action === 'managednszone')) {
            var recordType = String(url.searchParams.get('nsrecordtype') || '').toUpperCase();
            var section = String(url.searchParams.get('dmsection') || '').toLowerCase();
            return action === 'dnsseczone'
                || recordType === 'DNSSEC'
                || section === 'dnssec';
        }

        if (file === 'domainforwarding.php' && action === 'managedomfwd') {
            return true;
        }

        if (file === 'emailmanagement.php' && action === 'manageemails') {
            return true;
        }

        return false;
    }

    function collectUrls() {
        var current = comparableUrl(window.location.href);
        var found = Object.create(null);

        Array.prototype.forEach.call(document.querySelectorAll('a[href]'), function (link) {
            if (!link || link.target === '_blank' || link.hasAttribute('download')) {
                return;
            }

            var url = safeUrl(link.getAttribute('href'), window.location.href);
            if (!url || !isAllowedDisplayRoute(url.href)) {
                return;
            }

            url.hash = '';
            if (url.href !== current) {
                found[url.href] = true;
            }
        });

        return Object.keys(found).sort();
    }

    function installRules() {
        if (!window.HTMLScriptElement
            || typeof window.HTMLScriptElement.supports !== 'function'
            || !window.HTMLScriptElement.supports('speculationrules')) {
            return;
        }

        var urls = collectUrls();
        var signature = urls.join('\n');
        if (!urls.length || signature === installedSignature) {
            return;
        }

        var rules = {
            prerender: [{
                urls: urls,
                eagerness: 'moderate',
                referrer_policy: 'same-origin'
            }]
        };

        var nextRule = document.createElement('script');
        nextRule.type = 'speculationrules';
        nextRule.id = 'dm-register-dns-prerender-rules-1619';
        nextRule.textContent = JSON.stringify(rules);

        if (ruleElement && ruleElement.parentNode) {
            ruleElement.parentNode.removeChild(ruleElement);
        }

        document.head.appendChild(nextRule);
        ruleElement = nextRule;
        installedSignature = signature;
        document.documentElement.setAttribute('data-dm-register-dns-prerender-1619', 'ready');
    }

    function scheduleRefresh(delay) {
        if (refreshTimer) {
            window.clearTimeout(refreshTimer);
        }
        refreshTimer = window.setTimeout(function () {
            refreshTimer = null;
            installRules();
        }, typeof delay === 'number' ? delay : 0);
    }

    function relevantInteraction(event) {
        var target = event.target;
        var link = target && target.closest ? target.closest('a[href]') : null;
        if (link && isAllowedDisplayRoute(link.href)) {
            scheduleRefresh(0);
        }
    }

    document.addEventListener('pointerover', relevantInteraction, true);
    document.addEventListener('focusin', relevantInteraction, true);
    document.addEventListener('pointerdown', relevantInteraction, true);
    document.addEventListener('touchstart', relevantInteraction, { capture: true, passive: true });

    scheduleRefresh(0);
    lateScanTimers.push(window.setTimeout(installRules, 300));
    lateScanTimers.push(window.setTimeout(installRules, 1000));

    window.dmRegisterDnsPrerender1619 = {
        isAllowedDisplayRoute: isAllowedDisplayRoute,
        refresh: installRules,
        version: 1619
    };
}());
</script>
HTML;
});
