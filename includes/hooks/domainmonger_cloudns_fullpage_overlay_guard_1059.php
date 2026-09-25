<?php
/**
 * DomainMonger ClouDNS Fullpage Overlay Guard 1059 / safer route expansion 1062 / zone-tools first-paint guard 1601 / DNSPlus-wide soft navigation 1612
 *
 * Purpose:
 * - Patch 1059 confirmed this fixes the black page-load flash with white spinner
 *   on ClouDNS DNS Records.
 * - Patch 1060 expanded too broadly/heavily and caused pages not to load.
 * - Patch 1062 keeps the proven 1059 implementation, removes the heavy 1060
 *   MutationObserver/zone-wide auto-scope, and expands only through explicit
 *   known ClouDNS customAction routes.
 * - Patch 1599 adds the dedicated full-page Add Zones and Delete Zones routes.
 * - Patch 1600 loads the established DNSPlus visual stylesheets from the real
 *   document head on zone-tool routes.
 * - Patch 1603 removes the failed Patch 1602 shared-head experiment, which
 *   exposed CSS/Smarty content at the top of the page. The shared head is
 *   restored exactly, while the confirmed WHMCS menu stacking rule is emitted
 *   through ClientAreaHeadOutput where it belongs.
 *
 * Scope: WHMCS clientarea productdetails pages for explicit ClouDNS routes only.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function dm_cloudns_fullpage_overlay_guard_1059_is_scoped_page()
{
    $scriptName = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = (string) ($_GET['action'] ?? '');
    $customAction = (string) ($_GET['customAction'] ?? '');

    if ($scriptName !== 'clientarea.php' || $action !== 'productdetails') {
        return false;
    }

    // Explicit ClouDNS routes only. Do not use a broad "zone exists" match here;
    // that broad match was part of failed Patch 1060.
    $cloudnsActions = [
        // Main ClouDNS pages/navigation
        'zone-settings',
        'mail-forwarding',
        'statistics',
        'update-status',
        'update',
        'soa-settings',
        'dnssec',
        'dnssec-settings',
        'dnssec-show',
        'dnssec-waiting',
        'zone-transfers',
        'free-ssl',
        'import',
        'export-zone-file',
        'parked-templates',

        // DNS record routes
        'edit-record',
        'do-edit-record',
        'add-new-record',
        'add-record',
        'delete-record',

        // Mail forward routes
        'add-new-forwarding',
        'do-add-forward',
        'add-forward',
        'do-edit-forward',
        'edit-forward',
        'delete-forward',
        'mail-forwarding-add-mx',

        // SOA/DNSSEC/SSL/status/action redirects
        'edit-soa-settings',
        'dnssec-activate',
        'dnssec-deactivate',
        'freessl-activate',
        'freessl-deactivate',
        'freessl-change-issuer',

        // Import/export/add-zone flows
        'import-records',
        'add-new-zone',
        'add-new-zone-bulk-master',
        'add-new-zone-bulk-delete',
        'add-new-zone-slave',
        'add-new-zone-parked',
        'add-new-zone-master-reverse',
        'add-new-zone-slave-reverse',
        'bulk-add-zones',
        'bulk-delete-zones',
        'add-zone',
        'add-existing-zone',
        'delete-zone',
        'parked-templates-apply',

        // Zone transfer routes
        'zone-transfers-add',
        'zone-transfers-delete',
        'add-master-servers',
        'delete-master-servers',
    ];

    return in_array($customAction, $cloudnsActions, true);
}


/**
 * Patch 1603 emergency cleanup:
 * - Restore the shared template head to the exact pre-1602 file.
 * - Keep the confirmed WHMCS menu stacking fix in a proper head-output hook.
 * - Retain the proven dynamic overlay suppression for explicit ClouDNS routes.
 */


/**
 * Keep WHMCS navigation dropdowns above the page-local DNSPlus blue header on
 * Add Zones and Delete Zones pages. This replaces the failed shared-head block
 * from Patch 1602 without modifying global template markup.
 */
add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $scriptName = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = (string) ($_GET['action'] ?? '');
    $customAction = (string) ($_GET['customAction'] ?? '');

    if ($scriptName !== 'clientarea.php' || $action !== 'productdetails') {
        return '';
    }

    if (!in_array($customAction, [
        'add-new-zone',
        'add-new-zone-bulk-master',
        'add-new-zone-bulk-delete',
        'bulk-add-zones',
        'bulk-delete-zones',
        'delete-zone',
    ], true)) {
        return '';
    }

    return <<<'HTML'
<style id="dm-cloudns-zone-tools-menu-stack-1603">
body #header.header,
body .whmcssubmenu,
body .whmcssubmenu .navbar,
body .whmcssubmenu .main-navbar-wrapper {
    position: relative !important;
    z-index: 2000 !important;
}
body #header.header .dropdown-menu,
body .whmcssubmenu .dropdown-menu {
    z-index: 2020 !important;
}
</style>
HTML;
});

add_hook('ClientAreaHeaderOutput', 1, function ($vars) {
    if (!dm_cloudns_fullpage_overlay_guard_1059_is_scoped_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-cloudns-fullpage-overlay-guard-1059-style">
/* The black flash with a white spinner matches WHMCS' global fullpage overlay.
   Hide that overlay on scoped ClouDNS pages unless a later intentional action
   explicitly opts in. The custom ClouDNS modals do not use #fullpage-overlay. */
html.dm-cloudns-suppress-fullpage-overlay-1059 #fullpage-overlay,
body.dm-cloudns-suppress-fullpage-overlay-1059 #fullpage-overlay,
body:not(.dm-cloudns-allow-fullpage-overlay-1059) #fullpage-overlay {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}
html.dm-cloudns-suppress-fullpage-overlay-1059 #fullpage-overlay img,
body.dm-cloudns-suppress-fullpage-overlay-1059 #fullpage-overlay img,
body:not(.dm-cloudns-allow-fullpage-overlay-1059) #fullpage-overlay img,
html.dm-cloudns-suppress-fullpage-overlay-1059 #fullpage-overlay .overlay-spinner,
body.dm-cloudns-suppress-fullpage-overlay-1059 #fullpage-overlay .overlay-spinner,
body:not(.dm-cloudns-allow-fullpage-overlay-1059) #fullpage-overlay .overlay-spinner {
    display: none !important;
}
</style>
<script id="dm-cloudns-fullpage-overlay-guard-1059-head">
(function () {
    'use strict';

    var html = document.documentElement;
    html.classList.add('dm-cloudns-suppress-fullpage-overlay-1059');

    function suppressOverlay() {
        var overlay = document.getElementById('fullpage-overlay');
        if (!overlay) {
            return;
        }
        if (document.body && document.body.classList && document.body.classList.contains('dm-cloudns-allow-fullpage-overlay-1059')) {
            return;
        }
        overlay.classList.add('w-hidden');
        overlay.setAttribute('aria-hidden', 'true');
        overlay.style.setProperty('display', 'none', 'important');
        overlay.style.setProperty('visibility', 'hidden', 'important');
        overlay.style.setProperty('opacity', '0', 'important');
        overlay.style.setProperty('pointer-events', 'none', 'important');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', suppressOverlay, true);
    } else {
        suppressOverlay();
    }

    var attempts = 0;
    var timer = window.setInterval(function () {
        attempts += 1;
        suppressOverlay();
        if (attempts >= 80) {
            window.clearInterval(timer);
        }
    }, 25);

    window.dmCloudnsFullpageOverlayGuard1059 = {
        suppressOverlay: suppressOverlay,
        expandedInPatch: 1062
    };
})();
</script>
HTML;
});

/**
 * Patch 1612: DNSPlus-wide soft navigation.
 *
 * The 1608 diagnostic confirmed these pages perform one normal document
 * navigation with no redirect or second reload. The remaining white frame is
 * the browser clearing the old document before the new WHMCS document paints.
 * CSS on the destination page cannot remove that frame.
 *
 * Keep the current DNSPlus page on screen while another safe DNSPlus display
 * page is fetched, then replace only WHMCS' #main-body container. Mutating
 * actions, downloads, form submissions, cancellation, and unrelated WHMCS links
 * retain normal navigation. This expands the confirmed Patch 1611 behavior from
 * Add/Delete Zones to the full DNSPlus page set.
 */
add_hook('ClientAreaFooterOutput', 100, function ($vars) {
    $scriptName = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = (string) ($_GET['action'] ?? '');
    $serviceId = (string) ($_GET['id'] ?? '');

    // The default DNSPlus Zones List has no customAction, so emit the small
    // self-scoping controller on product-details pages and let the definitive
    // DNSPlus DOM markers below decide whether it activates.
    if ($scriptName !== 'clientarea.php' || $action !== 'productdetails' || $serviceId === '') {
        return '';
    }

    return <<<'HTML'
<style id="dm-cloudns-soft-navigation-1612-style">
html.dm-cloudns-soft-navigation-enabled-1612 #main-body {
    view-transition-name: dm-cloudns-main-body-1612;
}
::view-transition-group(dm-cloudns-main-body-1612) {
    animation-duration: 90ms !important;
    animation-timing-function: linear !important;
}
::view-transition-old(dm-cloudns-main-body-1612),
::view-transition-new(dm-cloudns-main-body-1612) {
    mix-blend-mode: normal !important;
}
html.dm-cloudns-soft-nav-loading-1612,
html.dm-cloudns-soft-nav-loading-1612 a,
html.dm-cloudns-soft-nav-loading-1612 button,
html.dm-cloudns-soft-nav-loading-1612 select {
    cursor: progress !important;
}
@media (prefers-reduced-motion: reduce) {
    ::view-transition-group(dm-cloudns-main-body-1612) {
        animation-duration: 1ms !important;
    }
}
</style>
<script id="dm-cloudns-soft-navigation-1612-script">
(function () {
    'use strict';

    if (window.dmCloudnsSoftNavigate1612) {
        return;
    }

    var allowedActions = {
        '': true,
        'zones': true,
        'zone-settings': true,
        'mail-forwarding': true,
        'statistics': true,
        'update-status': true,
        'soa-settings': true,
        'dnssec': true,
        'dnssec-settings': true,
        'dnssec-show': true,
        'dnssec-waiting': true,
        'zone-transfers': true,
        'free-ssl': true,
        'import': true,
        'export-zone-file': true,
        'parked-templates': true,
        'bind-settings': true,
        'add-new-record': true,
        'edit-record': true,
        'do-edit-record': true,
        'add-new-forwarding': true,
        'edit-forward': true,
        'do-edit-forward': true,
        'add-existing-zone': true,
        'add-new-zone': true,
        'add-new-zone-bulk-master': true,
        'add-new-zone-bulk-delete': true,
        'add-new-zone-slave': true,
        'add-new-zone-parked': true,
        'add-new-zone-master-reverse': true,
        'add-new-zone-slave-reverse': true,
        'failover-edit': true,
        'failover-action-log': true,
        'failover-monitoring-log': true,
        'failover-monitoring-notifications': true,
        'get-failover-settings': true
    };

    var navigationSequence = 0;
    var runningController = null;
    var loadedExternalScripts = Object.create(null);
    var loadedSharedInlineScripts = Object.create(null);

    function isDnsPlusDocument(doc) {
        if (!doc || !doc.querySelector) {
            return false;
        }
        return !!doc.querySelector([
            '#cloudnsSettingsMenu',
            '#cloudnsMobileSettingsMenu',
            '.cloudns-zones-panel',
            '.cloudns-add-zone-shared-shell',
            '.cloudns-module-header',
            '[class*="cloudns-"]'
        ].join(','));
    }

    if (!isDnsPlusDocument(document)) {
        return;
    }

    document.documentElement.classList.add('dm-cloudns-soft-navigation-enabled-1612');

    function scriptHash(text) {
        var hash = 5381;
        var value = String(text || '');
        for (var i = 0; i < value.length; i += 1) {
            hash = ((hash << 5) + hash) ^ value.charCodeAt(i);
        }
        return String(hash >>> 0);
    }

    function absoluteScriptSource(script) {
        var src = script.getAttribute('src');
        if (!src) {
            return '';
        }
        try {
            return new URL(src, window.location.href).href;
        } catch (error) {
            return src;
        }
    }

    function isSharedInlineScript(script) {
        return script.id === 'dm-cloudns-header-settings-css-loader-1612' ||
            script.id === 'dm-cloudns-header-settings-script-1612';
    }

    Array.prototype.forEach.call(document.scripts || [], function (script) {
        var src = absoluteScriptSource(script);
        if (src) {
            loadedExternalScripts[src] = true;
        } else if (isSharedInlineScript(script)) {
            loadedSharedInlineScripts[script.id + ':' + scriptHash(script.textContent || '')] = true;
        }
    });

    function targetUrl(url) {
        try {
            return new URL(String(url || ''), window.location.href);
        } catch (error) {
            return null;
        }
    }

    function comparableUrl(url) {
        var copy = targetUrl(url);
        if (!copy) {
            return '';
        }
        copy.hash = '';
        return copy.href;
    }

    function isCurrentUrl(url) {
        return comparableUrl(url) === comparableUrl(window.location.href);
    }

    function canHandle(url) {
        var target = targetUrl(url);
        var current = targetUrl(window.location.href);
        if (!target || !current || target.origin !== current.origin || target.pathname !== current.pathname) {
            return false;
        }
        if (target.searchParams.get('action') !== 'productdetails') {
            return false;
        }
        if (String(target.searchParams.get('id') || '') !== String(current.searchParams.get('id') || '')) {
            return false;
        }
        if (target.searchParams.get('download') === '1') {
            return false;
        }
        return allowedActions[String(target.searchParams.get('customAction') || '')] === true;
    }

    function ensureMenuStackStyle() {
        if (document.getElementById('dm-cloudns-zone-tools-menu-stack-1603')) {
            return;
        }
        var style = document.createElement('style');
        style.id = 'dm-cloudns-zone-tools-menu-stack-1603';
        style.textContent = [
            'body #header.header, body .whmcssubmenu, body .whmcssubmenu .navbar, body .whmcssubmenu .main-navbar-wrapper {',
            'position: relative !important; z-index: 2000 !important;',
            '}',
            'body #header.header .dropdown-menu, body .whmcssubmenu .dropdown-menu {',
            'z-index: 2020 !important;',
            '}'
        ].join('\n');
        document.head.appendChild(style);
    }

    function activateScripts(root) {
        var scripts = Array.prototype.slice.call(root.querySelectorAll('script'));
        scripts.forEach(function (oldScript) {
            var src = absoluteScriptSource(oldScript);
            var sharedKey = oldScript.id + ':' + scriptHash(oldScript.textContent || '');

            if (src && loadedExternalScripts[src]) {
                oldScript.parentNode.removeChild(oldScript);
                return;
            }
            if (!src && isSharedInlineScript(oldScript) && loadedSharedInlineScripts[sharedKey]) {
                oldScript.parentNode.removeChild(oldScript);
                return;
            }

            var newScript = document.createElement('script');
            Array.prototype.forEach.call(oldScript.attributes || [], function (attribute) {
                newScript.setAttribute(attribute.name, attribute.value);
            });

            if (src) {
                newScript.async = false;
                newScript.src = src;
                loadedExternalScripts[src] = true;
            } else {
                newScript.text = oldScript.textContent || '';
                if (isSharedInlineScript(oldScript)) {
                    loadedSharedInlineScripts[sharedKey] = true;
                }
            }

            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    function performSwap(incomingMain, incomingDocument, url, pushHistory) {
        var currentMain = document.getElementById('main-body');
        if (!currentMain || !incomingMain) {
            throw new Error('WHMCS main content container was not found.');
        }

        document.dispatchEvent(new CustomEvent('dmCloudnsSoftBeforeSwap1612', {
            detail: { url: url.href }
        }));

        var importedMain = document.importNode(incomingMain, true);
        currentMain.parentNode.replaceChild(importedMain, currentMain);

        if (incomingDocument.title) {
            document.title = incomingDocument.title;
        }

        ensureMenuStackStyle();
        activateScripts(importedMain);

        if (pushHistory) {
            window.history.pushState({ dmCloudnsSoft1612: true }, '', url.href);
        }

        window.scrollTo(0, 0);
        document.dispatchEvent(new CustomEvent('dmCloudnsSoftNavigated1612', {
            detail: { url: url.href }
        }));
    }

    function fallbackNavigate(url) {
        window.location.assign(url.href || String(url));
    }

    function navigate(url, options) {
        var target = targetUrl(url);
        var pushHistory = !options || options.pushHistory !== false;

        if (!target) {
            return Promise.resolve(false);
        }
        if (isCurrentUrl(target)) {
            return Promise.resolve(true);
        }
        if (!canHandle(target.href)) {
            fallbackNavigate(target);
            return Promise.resolve(false);
        }

        navigationSequence += 1;
        var thisSequence = navigationSequence;

        if (runningController && typeof runningController.abort === 'function') {
            runningController.abort();
        }
        runningController = typeof AbortController !== 'undefined' ? new AbortController() : null;

        document.documentElement.classList.add('dm-cloudns-soft-nav-loading-1612');
        var currentMain = document.getElementById('main-body');
        if (currentMain) {
            currentMain.setAttribute('aria-busy', 'true');
        }

        return fetch(target.href, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            redirect: 'follow',
            headers: {
                'Accept': 'text/html,application/xhtml+xml',
                'X-DM-DNSPlus-Soft-Navigation': '1612'
            },
            signal: runningController ? runningController.signal : undefined
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            var finalUrl = targetUrl(response.url || target.href);
            if (!finalUrl || !canHandle(finalUrl.href)) {
                throw new Error('The requested DNSPlus route redirected elsewhere.');
            }

            return response.text().then(function (html) {
                return { html: html, finalUrl: finalUrl };
            });
        }).then(function (result) {
            if (thisSequence !== navigationSequence) {
                return false;
            }

            var incomingDocument = new DOMParser().parseFromString(result.html, 'text/html');
            var incomingMain = incomingDocument.getElementById('main-body');
            if (!incomingMain || !isDnsPlusDocument(incomingMain)) {
                throw new Error('The fetched page was not recognized as DNSPlus.');
            }

            var swap = function () {
                performSwap(incomingMain, incomingDocument, result.finalUrl, pushHistory);
            };

            if (typeof document.startViewTransition === 'function') {
                var transition = document.startViewTransition(swap);
                return transition.finished.catch(function () {
                    return true;
                });
            }

            swap();
            return true;
        }).catch(function (error) {
            if (error && error.name === 'AbortError') {
                return false;
            }
            fallbackNavigate(target);
            return false;
        }).finally(function () {
            if (thisSequence === navigationSequence) {
                document.documentElement.classList.remove('dm-cloudns-soft-nav-loading-1612');
                var main = document.getElementById('main-body');
                if (main) {
                    main.removeAttribute('aria-busy');
                }
            }
        });
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        var link = event.target && event.target.closest ? event.target.closest('a[href]') : null;
        if (!link || link.target === '_blank' || link.hasAttribute('download')) {
            return;
        }

        var target = targetUrl(link.href);
        if (!target || (target.hash && isCurrentUrl(target)) || isCurrentUrl(target) || !canHandle(target.href)) {
            return;
        }

        event.preventDefault();
        navigate(target.href);
    }, true);

    window.addEventListener('popstate', function () {
        if (canHandle(window.location.href)) {
            navigate(window.location.href, { pushHistory: false });
            return;
        }
        window.location.reload();
    });

    var api = {
        canHandle: canHandle,
        navigate: navigate,
        version: 1612
    };
    window.dmCloudnsSoftNavigate1612 = api;
    // Backward-compatible alias for the confirmed Patch 1611 Add/Delete template.
    window.dmCloudnsSoftNavigate1611 = api;
})();
</script>
HTML;
});

