<?php
/**
 * DomainMonger DNS navigation spinner - Patch 1828.
 *
 * Purpose:
 * - Give immediate visual feedback while the browser waits for the slower
 *   RegistrarDNS (clientarea.php?action=domaindns) and DNSPlus/ClouDNS DNS
 *   Records (customAction=zone-settings) pages.
 * - The indicator is drawn on the current page before navigation starts, so it
 *   remains useful even when the destination spends several seconds in PHP/API
 *   work before returning HTML.
 *
 * Safety/scope:
 * - Does not change DNS routes, requests, forms, API calls, or backend logic.
 * - Does not use or re-enable WHMCS' #fullpage-overlay.
 * - Does not touch language overrides or the integration folder.
 * - Activates only when the destination URL is one of the two DNS Records
 *   routes named above (plus the retained legacy RegistrarDNS route).
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 9998, function ($vars) {
    return <<<'HTML'
<style id="dm-dns-navigation-spinner-1828-style">
#dm-dns-navigation-spinner-1828 {
    position: fixed;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    z-index: 2147483000;
    display: none;
    align-items: center;
    gap: 12px;
    min-width: 205px;
    max-width: calc(100vw - 32px);
    padding: 14px 18px;
    border: 1px solid rgba(22, 58, 95, 0.18);
    border-radius: 8px;
    background: #fff;
    color: #163a5f;
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.18);
    font: 600 14px/1.35 Arial, Helvetica, sans-serif;
    pointer-events: none;
}
#dm-dns-navigation-spinner-1828.dm-dns-navigation-spinner-visible {
    display: flex;
}
#dm-dns-navigation-spinner-1828 .dm-dns-navigation-spinner-ring {
    width: 24px;
    height: 24px;
    flex: 0 0 24px;
    border: 3px solid rgba(245, 130, 32, 0.28);
    border-top-color: #f58220;
    border-radius: 50%;
    animation: dm-dns-navigation-spinner-1828-spin 0.7s linear infinite;
}
#dm-dns-navigation-spinner-1828 .dm-dns-navigation-spinner-text {
    white-space: nowrap;
}
html.dm-dns-navigation-loading-1828,
html.dm-dns-navigation-loading-1828 body,
html.dm-dns-navigation-loading-1828 a,
html.dm-dns-navigation-loading-1828 button {
    cursor: progress !important;
}
@keyframes dm-dns-navigation-spinner-1828-spin {
    to { transform: rotate(360deg); }
}
@media (prefers-reduced-motion: reduce) {
    #dm-dns-navigation-spinner-1828 .dm-dns-navigation-spinner-ring {
        animation-duration: 1.4s;
    }
}
</style>
<script id="dm-dns-navigation-spinner-1828-script">
(function () {
    'use strict';

    if (window.dmDnsNavigationSpinner1828) {
        return;
    }

    var loader = null;
    var hideTimer = null;

    function parseUrl(value) {
        try {
            return new URL(String(value || ''), window.location.href);
        } catch (error) {
            return null;
        }
    }

    function basename(pathname) {
        var parts = String(pathname || '').split('/');
        return String(parts.pop() || '').toLowerCase();
    }

    function classifyDestination(value) {
        var url = parseUrl(value);
        if (!url || url.origin !== window.location.origin) {
            return '';
        }

        var file = basename(url.pathname);
        var action = String(url.searchParams.get('action') || '').toLowerCase();
        var customAction = String(url.searchParams.get('customAction') || '').toLowerCase();

        // Current/default RegistrarDNS route. Exclude raw internal feed requests,
        // which are implementation details rather than user navigation.
        if (file === 'clientarea.php' && action === 'domaindns') {
            if (url.searchParams.has('dmplainnative') || url.searchParams.has('dmnativeold') || url.searchParams.has('dmfeednative')) {
                return '';
            }
            return 'registrardns';
        }

        // Retained legacy RegistrarDNS route/fallback.
        if (file === 'dnsmanagement.php' && action === 'managednszone') {
            return 'registrardns';
        }

        // Main DNSPlus/ClouDNS DNS Records page.
        if (file === 'clientarea.php' && action === 'productdetails' && customAction === 'zone-settings') {
            return 'cloudns';
        }

        return '';
    }

    function ensureLoader() {
        if (loader && document.documentElement.contains(loader)) {
            return loader;
        }

        loader = document.createElement('div');
        loader.id = 'dm-dns-navigation-spinner-1828';
        loader.setAttribute('role', 'status');
        loader.setAttribute('aria-live', 'polite');
        loader.setAttribute('aria-hidden', 'true');
        loader.innerHTML = '<span class="dm-dns-navigation-spinner-ring" aria-hidden="true"></span>'
            + '<span class="dm-dns-navigation-spinner-text">Loading DNS Records...</span>';
        (document.body || document.documentElement).appendChild(loader);
        return loader;
    }

    function showLoader(kind) {
        var element = ensureLoader();
        var text = element.querySelector('.dm-dns-navigation-spinner-text');

        if (text) {
            text.textContent = kind === 'cloudns' ? 'Loading DNSPlus Records...' : 'Loading DNS Records...';
        }

        if (hideTimer) {
            window.clearTimeout(hideTimer);
        }

        document.documentElement.classList.add('dm-dns-navigation-loading-1828');
        element.classList.add('dm-dns-navigation-spinner-visible');
        element.setAttribute('aria-hidden', 'false');

        // Safety valve for intercepted/aborted navigations. A real document
        // navigation removes this DOM naturally; soft DNSPlus navigation fires
        // its completion event below and hides it immediately.
        hideTimer = window.setTimeout(hideLoader, 30000);
    }

    function hideLoader() {
        if (hideTimer) {
            window.clearTimeout(hideTimer);
            hideTimer = null;
        }
        document.documentElement.classList.remove('dm-dns-navigation-loading-1828');
        if (loader) {
            loader.classList.remove('dm-dns-navigation-spinner-visible');
            loader.setAttribute('aria-hidden', 'true');
        }
    }

    function targetFromEvent(event) {
        var node = event.target;
        if (!node || !node.closest) {
            return '';
        }

        var anchor = node.closest('a[href]');
        if (anchor) {
            if (anchor.target === '_blank' || anchor.hasAttribute('download')) {
                return '';
            }
            return anchor.href || anchor.getAttribute('href') || '';
        }

        // DomainMonger's Services list uses a row-level redirect rather than an
        // anchor for DNSLite 10. Read the already-established target URL.
        var redirectNode = node.closest('[data-dm-manage-url]');
        if (redirectNode) {
            return redirectNode.getAttribute('data-dm-manage-url') || '';
        }

        return '';
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        var target = targetFromEvent(event);
        var kind = classifyDestination(target);
        if (!kind) {
            return;
        }

        var parsedTarget = parseUrl(target);
        var parsedCurrent = parseUrl(window.location.href);
        if (parsedTarget && parsedCurrent && parsedTarget.href === parsedCurrent.href) {
            return;
        }

        showLoader(kind);
    }, true);

    // Keyboard activation of the Services-list row can invoke its redirect
    // directly instead of producing a normal anchor click.
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }
        var node = event.target;
        if (!node || !node.closest) {
            return;
        }
        var redirectNode = node.closest('[data-dm-manage-url]');
        if (!redirectNode) {
            return;
        }
        var target = redirectNode.getAttribute('data-dm-manage-url') || '';
        var kind = classifyDestination(target);
        if (kind) {
            showLoader(kind);
        }
    }, true);

    // DNSPlus Patch 1612 can complete a page navigation without unloading the
    // document. Remove this loader when that soft navigation finishes.
    document.addEventListener('dmCloudnsSoftNavigated1612', hideLoader, false);
    window.addEventListener('pageshow', hideLoader, false);

    window.dmDnsNavigationSpinner1828 = {
        classifyDestination: classifyDestination,
        show: showLoader,
        hide: hideLoader,
        version: 1828
    };
}());
</script>
HTML;
});
