<?php
/**
 * DomainMonger patch 1193: prevent the legacy ResellerClub/LogicBoxes page UI
 * from painting before the converted unified interface is ready.
 *
 * The legacy templates and forms remain in the DOM because the converted pages
 * still depend on their WHMCS tokens, registrar data, forms, and actions. This
 * hook only controls initial visibility on supported registrar pages.
 * Patch 1231 covers the confirmed default native DNS Records route.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm_rc_paint_guard_1192_client_id')) {
    function dm_rc_paint_guard_1192_client_id(): int
    {
        if (!empty($_SESSION['uid'])) {
            return (int) $_SESSION['uid'];
        }
        if (!empty($_SESSION['clientareauserid'])) {
            return (int) $_SESSION['clientareauserid'];
        }
        return 0;
    }
}

if (!function_exists('dm_rc_paint_guard_1192_supported_registrar')) {
    function dm_rc_paint_guard_1192_supported_registrar(string $registrar): bool
    {
        return (bool) preg_match('/(?:resellerclub|netearth|logicboxes)/i', $registrar);
    }
}

if (!function_exists('dm_rc_paint_guard_1192_page')) {
    function dm_rc_paint_guard_1192_page(): string
    {
        $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        // Never mask intentional raw, legacy, diagnostic, probe, or fallback routes.
        foreach ([
            'dmlegacydns', 'dmnoredirect', 'dmplainnative', 'dmnativeold', 'dmfeednative',
            'dmemailprobe', 'dmemaildiagraw', 'dmefdiag', 'dmshowdnsfallbacks',
            'dmdebugdnsfeed', 'dmmenuaudit', 'dmnavdiag',
        ] as $flag) {
            if (isset($_REQUEST[$flag])) {
                return '';
            }
        }

        if ($script === 'clientarea.php') {
            if ($action === 'domaindns') {
                return 'dnsrecords';
            }
            if ($action === 'domaincontacts') {
                return 'whois';
            }
            if ($action === 'domaingetepp') {
                return 'getepp';
            }
            if ($action === 'domainemailforwarding') {
                return 'emailforwarding';
            }
            if ($action === 'domaindetails') {
                $section = strtolower((string) ($_REQUEST['dmsection'] ?? $_REQUEST['dmnav'] ?? 'overview'));
                $section = preg_replace('/[^a-z0-9_-]/', '', $section);
                $map = [
                    'overview' => 'overview',
                    'autorenew' => 'autorenew',
                    'nameservers' => 'nameservers',
                    'reglock' => 'reglock',
                    'addons' => 'idprotection',
                    'idprotection' => 'idprotection',
                ];
                return $map[$section] ?? 'overview';
            }
            return '';
        }

        if ($script === 'domainmanagement.php') {
            if ($action === 'childns') {
                return 'privatens';
            }
            if (in_array($action, ['dnssec', 'managednssec'], true)) {
                return 'dnssec';
            }
            return '';
        }

        if ($script === 'dnsmanagement.php') {
            if ($action === 'managednszone') {
                return 'dnsrecords';
            }
            if ($action === 'dnsseczone') {
                return 'dnssec';
            }
            return '';
        }

        if ($script === 'domainforwarding.php') {
            return 'domainforwarding';
        }

        if ($script === 'emailmanagement.php') {
            return 'emailforwarding';
        }

        return '';
    }
}

if (!function_exists('dm_rc_paint_guard_1192_is_supported_domain')) {
    function dm_rc_paint_guard_1192_is_supported_domain(array $vars): bool
    {
        $clientId = dm_rc_paint_guard_1192_client_id();
        if ($clientId <= 0) {
            return false;
        }

        $domainId = 0;
        foreach (['domainid', 'id'] as $key) {
            if (!empty($_REQUEST[$key])) {
                $domainId = (int) $_REQUEST[$key];
                if ($domainId > 0) {
                    break;
                }
            }
        }
        if ($domainId <= 0) {
            foreach (['domainid', 'id'] as $key) {
                if (!empty($vars[$key])) {
                    $domainId = (int) $vars[$key];
                    if ($domainId > 0) {
                        break;
                    }
                }
            }
        }

        $domainName = trim((string) ($_REQUEST['domain'] ?? ($vars['domain'] ?? '')));
        if ($domainId <= 0 && $domainName === '') {
            return false;
        }

        try {
            $query = Capsule::table('tbldomains')
                ->select('registrar')
                ->where('userid', $clientId);

            if ($domainId > 0) {
                $query->where('id', $domainId);
            } else {
                $query->where('domain', $domainName);
            }

            $row = $query->first();
            return $row && dm_rc_paint_guard_1192_supported_registrar((string) ($row->registrar ?? ''));
        } catch (\Throwable $e) {
            return false;
        }
    }
}

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $page = dm_rc_paint_guard_1192_page();
    if ($page === '' || !dm_rc_paint_guard_1192_is_supported_domain((array) $vars)) {
        return '';
    }

    $pageJson = json_encode($page, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<style id="dm-rc-initial-paint-guard-1192-css">
/* The native WHMCS/registrar DOM remains available to every converter and form.
 * It is simply withheld from first paint until the unified page is ready. */
html.dm-rc-preload-1192 #main-body {
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}
html:not(.dm-rc-preload-1192) #main-body {
    visibility: visible;
    opacity: 1;
}
</style>
<script id="dm-rc-initial-paint-guard-1192-js">
(function () {
    'use strict';

    var page = {$pageJson};
    var root = document.documentElement;
    var released = false;
    var releaseQueued = false;
    var releaseDelayTimer = null;
    var observer = null;
    var timer = null;

    root.classList.add('dm-rc-preload-1192');
    root.setAttribute('data-dm-rc-preload-page', page);

    function navReady() {
        var nav = document.getElementById('dm-rc-unified-nav-1152');
        return !!(nav && !nav.hidden && nav.getAttribute('aria-hidden') !== 'true');
    }

    function has(selector) {
        return !!document.querySelector(selector);
    }

    function convertedReady() {
        if (!document.body || !navReady()) {
            return false;
        }

        switch (page) {
            case 'overview':
                return has('#tabOverview.dm-overview-live-1005, .dm-overview-live-1005');
            case 'autorenew':
                return has('.dm-rc-page-header-1162[data-dm-page-key="autorenew"]')
                    && has('#tabAutorenew form input[name="autorenew"]');
            case 'nameservers':
                return has('.dm-rc-page-header-1162[data-dm-page-key="nameservers"]')
                    && has('#tabNameservers form input[name^="ns"]');
            case 'reglock':
                return has('.dm-rc-page-header-1162[data-dm-page-key="reglock"]')
                    && has('#tabReglock form');
            case 'idprotection':
                return has('.dm-rc-page-header-1162[data-dm-page-key="idprotection"]')
                    && has('#tabAddons form input[name="buy"][value="idprotect"], #tabAddons form input[name="disable"][value="idprotect"]');
            case 'whois':
                return document.body.classList.contains('dm-whois-stage2')
                    && has('.dm-whois-form-head[data-dm-whois-external-header="1"]');
            case 'privatens':
                return has('.dm-rc-page-header-1162[data-dm-page-key="privatens"]')
                    && has('.dm-private-ns-shell, .dm-private-ns-wrap');
            case 'dnsrecords':
                return document.body.classList.contains('dm-dns-unified')
                    && has('#dm-dns-live-feed-page-header-1173')
                    && has('.dm-dns-live-feed-panel-1113');
            case 'dnssec':
                return document.body.classList.contains('dm-dns-unified')
                    && has('.dm-rc-page-header-1162[data-dm-page-key="dnssec"]');
            case 'domainforwarding':
                return document.body.classList.contains('dm-dns-unified');
            case 'emailforwarding':
                return document.body.classList.contains('dm-rc-email-forwarding-clean')
                    && has('.dm-rc-page-header-1162[data-dm-page-key="emailforwarding"]');
            case 'getepp':
                return document.body.classList.contains('dm-getepp-983-ready')
                    && has('.dm-getepp-983-shell');
            default:
                return false;
        }
    }

    function releaseNow() {
        if (released) {
            return;
        }
        released = true;
        if (observer) {
            observer.disconnect();
        }
        if (timer) {
            window.clearInterval(timer);
        }
        if (releaseDelayTimer) {
            window.clearTimeout(releaseDelayTimer);
        }
        root.classList.remove('dm-rc-preload-1192');
        root.removeAttribute('data-dm-rc-preload-page');
        var main = document.getElementById('main-body');
        if (main) {
            main.removeAttribute('aria-busy');
        }
    }

    function isSettingsTabPage() {
        return page === 'autorenew' || page === 'nameservers' || page === 'reglock' || page === 'idprotection';
    }

    function queueRelease() {
        if (released || releaseQueued) {
            return;
        }
        releaseQueued = true;

        function afterSettlingDelay() {
            // Two paint frames ensure the converted DOM and styles have settled
            // before the content becomes visible.
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(releaseNow);
            });
        }

        // These four WHMCS settings tabs are converted by one footer hook. The
        // unified header can be ready a fraction before the tab rewrite ends,
        // so allow that converter one short settling window without leaving the
        // page blank for the old nine-second safety timeout.
        if (isSettingsTabPage()) {
            releaseDelayTimer = window.setTimeout(afterSettlingDelay, 360);
        } else {
            afterSettlingDelay();
        }
    }

    function check() {
        if (convertedReady()) {
            queueRelease();
        }
    }

    function watch() {
        var main = document.getElementById('main-body');
        if (main) {
            main.setAttribute('aria-busy', 'true');
        }

        observer = new MutationObserver(check);
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'hidden', 'aria-hidden']
        });
        timer = window.setInterval(check, 40);
        check();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', watch, { once: true });
    } else {
        watch();
    }

    // Accessibility/safety fallback: if a future converter fails, return the
    // native page rather than leaving the client area permanently hidden.
    window.setTimeout(releaseNow, isSettingsTabPage() ? 1800 : 9000);
}());
</script>
HTML;
});
