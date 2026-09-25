<?php
/**
 * DomainMonger Patch 1308
 *
 * ResellerClub Overview action toolbar only:
 * - Match the established My Domains action-toolbar presentation.
 * - Remove the extra inset card/frame around the action links.
 * - Add consistent Font Awesome icons.
 * - Reorder actions into a clearer workflow while preserving every existing URL:
 *   DNS Management, Nameservers, WHOIS Contact Info, Registrar Lock,
 *   Auto Renew, Renew.
 * - Keep Renew orange as the primary one-time action.
 *
 * This presentation hook does not alter registrar processing, native WHMCS
 * forms, route generation, DNS loading, or the unified ResellerClub menu.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1307, static function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? ''));

    if ($scriptName !== 'clientarea.php' || $action !== 'domaindetails') {
        return '';
    }

    if (empty($_GET['dmconverted']) && empty($_GET['dmsection']) && empty($_GET['dmnav'])) {
        return '';
    }

    return <<<'HTML'
<style id="dm-resellerclub-overview-action-toolbar-1307-css">
/* The Overview shortcuts are an action toolbar, not a second framed menu. */
body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 {
    align-items: center !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
    margin: 0 0 12px !important;
    padding: 0 !important;
    width: 100% !important;
}

body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a {
    align-items: center !important;
    background: #163a5f !important;
    border: 1px solid #163a5f !important;
    border-radius: 4px !important;
    box-shadow: none !important;
    box-sizing: border-box !important;
    color: #ffffff !important;
    display: inline-flex !important;
    flex: 0 0 auto !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    gap: 7px !important;
    justify-content: center !important;
    line-height: 1.2 !important;
    margin: 0 !important;
    min-height: 34px !important;
    padding: 8px 13px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
}

body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a > i {
    color: inherit !important;
    flex: 0 0 auto !important;
    font-size: 12px !important;
    line-height: 1 !important;
    margin: 0 !important;
    min-width: 13px !important;
    text-align: center !important;
}

body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a.dm-primary,
body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a[data-dm-action-key="renew"] {
    background: #f58220 !important;
    border-color: #f58220 !important;
}

body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a:hover,
body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a:focus,
body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a.dm-primary:hover,
body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a.dm-primary:focus,
body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a[data-dm-action-key="renew"]:hover,
body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a[data-dm-action-key="renew"]:focus {
    background: #214e7a !important;
    border-color: #214e7a !important;
    color: #ffffff !important;
    outline: none !important;
    text-decoration: none !important;
}

body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a:focus-visible {
    outline: 2px solid rgba(245, 130, 32, 0.36) !important;
    outline-offset: 2px !important;
}

@media (max-width: 767px) {
    body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a {
        flex: 1 1 calc(50% - 3px) !important;
        white-space: normal !important;
    }
}

@media (max-width: 479px) {
    body.dm-domaindetails-live-1005 #tabOverview .dm-live-action-strip.dm-rc-overview-toolbar-1307 > a {
        flex-basis: 100% !important;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1307, static function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? ''));

    if ($scriptName !== 'clientarea.php' || $action !== 'domaindetails') {
        return '';
    }

    if (empty($_GET['dmconverted']) && empty($_GET['dmsection']) && empty($_GET['dmnav'])) {
        return '';
    }

    return <<<'HTML'
<script id="dm-resellerclub-overview-action-toolbar-1307-js">
(function () {
    'use strict';

    if (window.domainmongerResellerclubOverviewToolbar1307Loaded) {
        return;
    }
    window.domainmongerResellerclubOverviewToolbar1307Loaded = true;

    var order = [
        'dns',
        'nameservers',
        'whois',
        'reglock',
        'autorenew',
        'renew'
    ];

    var icons = {
        nameservers: 'fas fa-globe',
        whois: 'fas fa-user',
        dns: 'fas fa-server',
        reglock: 'fas fa-lock',
        autorenew: 'fas fa-toggle-on',
        renew: 'fas fa-sync-alt'
    };

    function normalizedText(link) {
        return String(link.textContent || '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function actionKey(link) {
        var text = normalizedText(link);
        var href = String(link.getAttribute('href') || '').toLowerCase();

        if (text.indexOf('nameserver') !== -1 && text.indexOf('private') === -1) {
            return 'nameservers';
        }
        if (text.indexOf('whois') !== -1 || text.indexOf('contact info') !== -1) {
            return 'whois';
        }
        if (text.indexOf('dns management') !== -1 || href.indexOf('action=domaindns') !== -1) {
            return 'dns';
        }
        if (text.indexOf('registrar lock') !== -1 || text === 'lock') {
            return 'reglock';
        }
        if (text.indexOf('auto renew') !== -1) {
            return 'autorenew';
        }
        if (text === 'renew' || text.indexOf('renew domain') !== -1) {
            return 'renew';
        }
        return '';
    }

    function decorateLink(link, key) {
        var label = String(link.textContent || '').replace(/\s+/g, ' ').trim();
        var icon = document.createElement('i');
        var text = document.createElement('span');

        icon.className = icons[key] || 'fas fa-chevron-right';
        icon.setAttribute('aria-hidden', 'true');
        text.textContent = label;

        while (link.firstChild) {
            link.removeChild(link.firstChild);
        }

        link.appendChild(icon);
        link.appendChild(text);
        link.setAttribute('data-dm-action-key', key);
        link.setAttribute('aria-label', label);
        link.setAttribute('title', label);
        link.classList.add('dm-rc-overview-action-1307');

        if (key === 'renew') {
            link.classList.add('dm-primary');
        }
    }

    function applyToolbar1307() {
        var strip = document.querySelector('#tabOverview .dm-live-action-strip');
        var links;
        var mapped = {};
        var unknown = [];
        var fragment;
        var appliedCount = 0;

        if (!strip || strip.getAttribute('data-dm-toolbar-1307') === '1') {
            return Boolean(strip);
        }

        links = Array.prototype.slice.call(strip.querySelectorAll(':scope > a[href]'));
        if (!links.length) {
            return false;
        }

        links.forEach(function (link) {
            var key = actionKey(link);
            if (key && !mapped[key]) {
                mapped[key] = link;
                decorateLink(link, key);
                appliedCount += 1;
            } else {
                unknown.push(link);
            }
        });

        if (!appliedCount) {
            return false;
        }

        fragment = document.createDocumentFragment();
        order.forEach(function (key) {
            if (mapped[key]) {
                fragment.appendChild(mapped[key]);
            }
        });
        unknown.forEach(function (link) {
            fragment.appendChild(link);
        });

        strip.appendChild(fragment);
        strip.classList.add('dm-rc-overview-toolbar-1307');
        strip.setAttribute('data-dm-toolbar-1307', '1');
        return true;
    }

    function boot1307() {
        var observer;

        if (applyToolbar1307()) {
            return;
        }

        observer = new MutationObserver(function () {
            if (applyToolbar1307()) {
                observer.disconnect();
            }
        });

        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });

        window.setTimeout(function () {
            applyToolbar1307();
        }, 150);
        window.setTimeout(function () {
            applyToolbar1307();
        }, 500);
        window.setTimeout(function () {
            applyToolbar1307();
            observer.disconnect();
        }, 1600);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot1307, { once: true });
    } else {
        boot1307();
    }
}());
</script>
HTML;
});
