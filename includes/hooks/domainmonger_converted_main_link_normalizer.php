<?php
/**
 * DomainMonger converted domain-management main link normalizer.
 *
 * Patch 1005:
 * - Cleans up failed Patch 1003 live-tab routing for the five converted domaindetails sections.
 * - Sends Overview/Auto Renew/Nameservers/Registrar Lock/Addons back through converted dmsection routes.
 * - Preserves the confirmed working WHOIS, Private Nameservers, DNSSEC, Domain/Email Forwarding, and Get EPP routes.
 * - Link-route cleanup only; does not alter forms, POST actions, tokens, registrar calls, templates, language files, or DNSSEC behavior.
 * - Adds opt-in diagnostic panel with ?dmnavdiag=1005 or #dmnavdiag1005.
 *
 * Patch 1615:
 * - Excludes DNSPlus desktop/mobile menus and explicit DNSPlus DNSSEC links.
 * - Prevents the converted-domain route normalizer from replacing the native
 *   DNSPlus dnssec-show service route with #dm-dns-dnssec.
 *
 * Patch 1158:
 * - Excludes the unified ResellerClub navigation and data-dm-route-lock links.
 * - Prevents the legacy normalizer from rewriting DNSSEC to DNS Records when
 *   clicked from Domain Forwarding or Email Forwarding pages.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    return <<<'HTML'
<script id="dm-converted-main-link-normalizer-1005">
(function () {
    'use strict';

    var PATCH = '1005';

    var SECTION_ROUTES = {
        overview: {
            label: 'Overview',
            file: 'clientarea.php',
            params: { action: 'domaindetails', dmsection: 'overview', dmdesign: '1', dmconverted: '1' },
            idParams: ['id'],
            hash: '#tabOverview'
        },
        autorenew: {
            label: 'Auto Renew',
            file: 'clientarea.php',
            params: { action: 'domaindetails', dmsection: 'autorenew', dmdesign: '1', dmconverted: '1' },
            idParams: ['id'],
            hash: '#tabAutorenew'
        },
        nameservers: {
            label: 'Nameservers',
            file: 'clientarea.php',
            params: { action: 'domaindetails', dmsection: 'nameservers', dmdesign: '1', dmconverted: '1' },
            idParams: ['id'],
            hash: '#tabNameservers'
        },
        reglock: {
            label: 'Registrar Lock',
            file: 'clientarea.php',
            params: { action: 'domaindetails', dmsection: 'reglock', dmdesign: '1', dmconverted: '1' },
            idParams: ['id'],
            hash: '#tabReglock'
        },
        addons: {
            label: 'Addons',
            file: 'clientarea.php',
            params: { action: 'domaindetails', dmsection: 'addons', dmdesign: '1', dmconverted: '1' },
            idParams: ['id'],
            hash: '#tabAddons'
        },
        privateNameservers: {
            label: 'Private Nameservers',
            file: 'domainmanagement.php',
            params: { action: 'childns' },
            idParams: ['id', 'domainid'],
            includeDomain: true,
            hash: ''
        },
        whois: {
            label: 'WHOIS Contact Info',
            file: 'clientarea.php',
            params: { action: 'domaincontacts' },
            idParams: ['domainid'],
            hash: ''
        },
        dnssec: {
            label: 'DNSSEC Management',
            file: 'dnsmanagement.php',
            params: { action: 'managednszone', dmsection: 'dnssec', nsrecordtype: 'DNSSEC', dmdesign: '1', dmconverted: '1' },
            idParams: ['domainid'],
            includeDomain: true,
            hash: '#dm-dns-dnssec'
        },
        epp: {
            label: 'Get EPP Code',
            file: 'clientarea.php',
            params: { action: 'domaingetepp', dmsection: 'epp', dmdesign: '1', dmconverted: '1' },
            idParams: ['domainid'],
            hash: ''
        }
    };

    function normalizeText(value) {
        return String(value || '')
            .replace(/\u00a0/g, ' ')
            .replace(/[\r\n\t]+/g, ' ')
            .replace(/\s+/g, ' ')
            .replace(/^\s+|\s+$/g, '')
            .toLowerCase();
    }

    function safeUrl(value) {
        try {
            return new URL(value || '', window.location.href);
        } catch (e) {
            return null;
        }
    }

    function getManageBase() {
        var match = String(window.location.pathname || '').match(/^(.*\/manage\/)/);
        return match ? match[1] : '/manage/';
    }

    function getParamFromUrl(value, names) {
        var url = safeUrl(value);
        var i;
        var found;
        if (!url) {
            return '';
        }
        for (i = 0; i < names.length; i++) {
            found = url.searchParams.get(names[i]);
            if (found) {
                return found;
            }
        }
        return '';
    }

    function getInputValue(names) {
        var i;
        var input;
        for (i = 0; i < names.length; i++) {
            input = document.querySelector('input[name="' + names[i] + '"], select[name="' + names[i] + '"]');
            if (input && input.value) {
                return input.value;
            }
        }
        return '';
    }

    function findDomainId(node) {
        var id = '';
        var href = node && node.getAttribute ? [
            node.getAttribute('href') || '',
            node.getAttribute('data-href') || '',
            node.getAttribute('data-url') || '',
            node.getAttribute('onclick') || ''
        ].join(' ') : '';
        var links;
        var i;

        if (href) {
            id = getParamFromUrl(href, ['id', 'domainid']);
            if (id) {
                return id;
            }
        }

        id = getParamFromUrl(window.location.href, ['id', 'domainid']);
        if (id) {
            return id;
        }

        id = getInputValue(['id', 'domainid']);
        if (id) {
            return id;
        }

        links = document.querySelectorAll('a[href]');
        for (i = 0; i < links.length; i++) {
            id = getParamFromUrl(links[i].getAttribute('href'), ['id', 'domainid']);
            if (id) {
                return id;
            }
        }

        return '';
    }


    function findDomainName(node) {
        var value = '';
        var href = node && node.getAttribute ? [
            node.getAttribute('href') || '',
            node.getAttribute('data-href') || '',
            node.getAttribute('data-url') || '',
            node.getAttribute('onclick') || ''
        ].join(' ') : '';
        var links;
        var input;
        var i;

        if (href) {
            value = getParamFromUrl(href, ['domain']);
            if (value) {
                return value;
            }
        }

        value = getParamFromUrl(window.location.href, ['domain']);
        if (value) {
            return value;
        }

        input = document.querySelector('input[name="domain"], input[name="domainname"], input[name="domain_name"]');
        if (input && input.value) {
            return input.value;
        }

        links = document.querySelectorAll('a[href]');
        for (i = 0; i < links.length; i++) {
            value = getParamFromUrl(links[i].getAttribute('href'), ['domain']);
            if (value) {
                return value;
            }
        }

        return '';
    }

    function buildRoute(key, node, forceReloadParam) {
        var route = SECTION_ROUTES[key];
        var id;
        var domainName;
        var url;
        var name;
        var idParams;
        var i;
        if (!route) {
            return '';
        }

        id = findDomainId(node);
        if (!id) {
            return '';
        }

        url = new URL(getManageBase() + route.file, window.location.origin);
        Object.keys(route.params || {}).forEach(function (paramName) {
            url.searchParams.set(paramName, route.params[paramName]);
        });
        if (route.includeDomain) {
            domainName = findDomainName(node);
            if (domainName) {
                url.searchParams.set('domain', domainName);
            }
        }

        idParams = route.idParams || ['id'];
        for (i = 0; i < idParams.length; i++) {
            name = idParams[i];
            url.searchParams.set(name, id);
        }

        if (forceReloadParam) {
            url.searchParams.set('dmnav', key);
            url.searchParams.set('_dmr', String(Date.now()));
        }
        url.hash = route.hash || '';
        return url.toString();
    }

    function nodeText(node) {
        var bits = [];
        if (!node) {
            return '';
        }
        bits.push(node.textContent || '');
        if (node.getAttribute) {
            bits.push(node.getAttribute('title') || '');
            bits.push(node.getAttribute('aria-label') || '');
            bits.push(node.getAttribute('data-title') || '');
            bits.push(node.getAttribute('data-label') || '');
            bits.push(node.getAttribute('data-section') || '');
            bits.push(node.getAttribute('data-dm-section') || '');
        }
        return normalizeText(bits.join(' '));
    }

    function hrefText(node) {
        if (!node || !node.getAttribute) {
            return '';
        }
        return normalizeText([
            node.getAttribute('href') || '',
            node.getAttribute('data-href') || '',
            node.getAttribute('onclick') || '',
            node.getAttribute('data-url') || '',
            node.getAttribute('data-link') || ''
        ].join(' '));
    }

    function isExplicitCurrentToolLink(node) {
        var text;
        var href;
        var cursor;
        var depth;
        if (!node) {
            return false;
        }

        text = nodeText(node);
        href = hrefText(node);

        if (text.indexOf('open current') !== -1 || text.indexOf('current tool') !== -1 || text.indexOf('old tool') !== -1) {
            return true;
        }
        if (href.indexOf('dm_current=1') !== -1 || href.indexOf('open-current') !== -1 || href.indexOf('current-tool') !== -1) {
            return true;
        }
        if (node.getAttribute && (node.getAttribute('target') || '').toLowerCase() === '_blank') {
            return true;
        }

        cursor = node;
        for (depth = 0; cursor && depth < 6; depth++, cursor = cursor.parentElement) {
            if (!cursor.getAttribute) {
                continue;
            }
            text = normalizeText([
                cursor.getAttribute('class') || '',
                cursor.getAttribute('data-dm-link-mode') || '',
                cursor.getAttribute('data-link-mode') || ''
            ].join(' '));
            if (text.indexOf('open-current') !== -1 || text.indexOf('current-tool') !== -1) {
                return true;
            }
        }

        return false;
    }

    function phraseMatch(text, phrases) {
        var i;
        var phrase;
        for (i = 0; i < phrases.length; i++) {
            phrase = phrases[i];
            if (text === phrase || text.indexOf(phrase) !== -1) {
                return true;
            }
        }
        return false;
    }

    function keyFromText(text) {
        if (!text) {
            return '';
        }

        /* More-specific labels must be checked before generic Nameservers. */
        if (phraseMatch(text, ['private nameservers', 'private name servers', 'child nameservers', 'child name servers', 'registered nameservers', 'registered name servers', 'register nameserver', 'register nameservers', 'hosts'])) {
            return 'privateNameservers';
        }
        if (phraseMatch(text, ['dnssec management', 'dnssec', 'dns security', 'dnssec records'])) {
            return 'dnssec';
        }
        if (phraseMatch(text, ['whois contact info', 'whois contact information', 'contact information', 'domain contacts'])) {
            return 'whois';
        }
        if (phraseMatch(text, ['get epp code', 'epp code', 'auth code', 'authorization code'])) {
            return 'epp';
        }
        if (phraseMatch(text, ['auto renew', 'autorenew', 'auto-renew', 'renewal settings'])) {
            return 'autorenew';
        }
        if (phraseMatch(text, ['registrar lock', 'reg lock', 'domain lock', 'transfer lock'])) {
            return 'reglock';
        }
        if (phraseMatch(text, ['nameservers', 'name servers', 'change nameserver', 'change nameservers'])) {
            return 'nameservers';
        }
        if (phraseMatch(text, ['addons', 'add-ons', 'domain addons', 'domain add-ons'])) {
            return 'addons';
        }
        if (phraseMatch(text, ['overview', 'summary'])) {
            return 'overview';
        }
        return '';
    }

    function keyFromHref(href) {
        if (!href) {
            return '';
        }

        if (href.indexOf('a=childns') !== -1 || href.indexOf('action=childns') !== -1 || (href.indexOf('domainmanagement.php') !== -1 && href.indexOf('childns') !== -1)) {
            return 'privateNameservers';
        }
        if (href.indexOf('dnssec') !== -1 || href.indexOf('nsrecordtype=dnssec') !== -1 || href.indexOf('securesign') !== -1) {
            return 'dnssec';
        }
        if (href.indexOf('action=domaincontacts') !== -1 || href.indexOf('domaincontacts') !== -1) {
            return 'whois';
        }
        if (href.indexOf('action=domaingetepp') !== -1 || href.indexOf('domaingetepp') !== -1) {
            return 'epp';
        }
        if (href.indexOf('tabautorenew') !== -1 || href.indexOf('autorenew') !== -1 || href.indexOf('auto-renew') !== -1) {
            return 'autorenew';
        }
        if (href.indexOf('tabreglock') !== -1 || href.indexOf('reglock') !== -1 || href.indexOf('registrarlock') !== -1 || href.indexOf('registrar-lock') !== -1) {
            return 'reglock';
        }
        if (href.indexOf('tabnameservers') !== -1 || href.indexOf('nameserver') !== -1 || href.indexOf('changedns') !== -1 || href.indexOf('change-dns') !== -1) {
            return 'nameservers';
        }
        if (href.indexOf('tabaddons') !== -1 || href.indexOf('addons') !== -1 || href.indexOf('add-ons') !== -1) {
            return 'addons';
        }
        if (href.indexOf('domaindetails') !== -1 && href.indexOf('tab') === -1 && href.indexOf('modop=custom') === -1) {
            return 'overview';
        }
        return '';
    }

    function isDnsWorkspaceInternalLink(node) {
        var href = hrefText(node);
        var text = nodeText(node);
        var currentPath = normalizeText(window.location.pathname || '');

        if (currentPath.indexOf('/dnsmanagement.php') === -1) {
            return false;
        }

        if (href.indexOf('#dm-dns-') !== -1 || href.indexOf('#dm-dns-workspace') !== -1) {
            return true;
        }

        return phraseMatch(text, [
            'dns records',
            'dnssec',
            'soa record',
            'domain forwarding',
            'email forwarding'
        ]);
    }

    function isUnifiedMenuRouteLocked(node) {
        if (!node || !node.getAttribute) {
            return false;
        }
        if (node.getAttribute('data-dm-route-lock') === '1') {
            return true;
        }

        /*
         * Patch 1615: DNSPlus has its own service/zone routes and its own
         * soft-navigation controller. The older converted-domain normalizer
         * must never reinterpret DNSPlus menu labels such as "DNSSEC" as
         * registrar-domain management links. That legacy rewrite changed the
         * correct dnssec-show URL into #dm-dns-dnssec and sent the service ID
         * through the unrelated domain-management router.
         */
        if (node.getAttribute('data-dm-dnssec-target') === 'dnssec-show') {
            return true;
        }
        if (node.closest && node.closest('#cloudnsSettingsMenu, #cloudnsMobileSettingsMenu, .cloudns-add-zone-shared-shell')) {
            return true;
        }

        return !!(node.closest && node.closest('#dm-rc-unified-nav-1152'));
    }

    function isCandidateLink(node) {
        var href = hrefText(node);
        var text = nodeText(node);
        if (!node || isUnifiedMenuRouteLocked(node) || isExplicitCurrentToolLink(node) || isDnsWorkspaceInternalLink(node)) {
            return false;
        }

        // DomainMonger Patch 1827: preserve the dedicated Manage Domains
        // navbar URL exactly as generated by Patch 1191. Its fragment-free
        // dmsection=overview route avoids the post-load #tabOverview jump.
        if (text === 'manage domains') {
            return false;
        }
        if (/^(mailto:|tel:|javascript:)/i.test((node.getAttribute && node.getAttribute('href')) || '')) {
            return false;
        }
        return !!(keyFromText(text) || keyFromHref(href));
    }

    function sectionKeyFromNode(node) {
        var key;
        if (!node || isUnifiedMenuRouteLocked(node) || isExplicitCurrentToolLink(node) || isDnsWorkspaceInternalLink(node)) {
            return '';
        }
        key = keyFromText(nodeText(node));
        if (key) {
            return key;
        }
        return keyFromHref(hrefText(node));
    }

    function sameDocumentNavigation(target) {
        var next = safeUrl(target);
        if (!next) {
            return false;
        }
        return next.origin === window.location.origin &&
            next.pathname === window.location.pathname &&
            next.search === window.location.search;
    }

    function normalizeElement(node) {
        var key;
        var target;
        if (!node || !node.getAttribute || !isCandidateLink(node)) {
            return false;
        }

        key = sectionKeyFromNode(node);
        target = buildRoute(key, node, false);
        if (!target) {
            return false;
        }

        if (node.tagName && node.tagName.toLowerCase() === 'a') {
            node.setAttribute('href', target);
        }
        node.setAttribute('data-dm-converted-route-normalized', PATCH);
        node.setAttribute('data-dm-converted-section', key);
        node.setAttribute('data-dm-converted-target', target);
        return true;
    }

    function normalizeAnchors() {
        var nodes = document.querySelectorAll('a[href], [data-href], [data-url], [onclick]');
        var i;
        for (i = 0; i < nodes.length; i++) {
            normalizeElement(nodes[i]);
        }
    }

    function closestCandidate(start) {
        var node = start;
        var depth = 0;
        while (node && node !== document && depth < 8) {
            if (node.matches && node.matches('a[href], [data-href], [data-url], [onclick]') && isCandidateLink(node)) {
                return node;
            }
            node = node.parentElement;
            depth++;
        }
        return null;
    }

    function activateRequestedDomainTab() {
        var params = new URLSearchParams(window.location.search || '');
        var section = normalizeText(params.get('dmsection') || params.get('dmnav') || '');
        var hash = String(window.location.hash || '');
        var target = '';
        var selectors;
        var i;
        var node;

        if (hash === '#tabOverview' || section === 'overview') {
            target = '#tabOverview';
        } else if (hash === '#tabAutorenew' || section === 'autorenew') {
            target = '#tabAutorenew';
        } else if (hash === '#tabNameservers' || section === 'nameservers') {
            target = '#tabNameservers';
        } else if (hash === '#tabReglock' || section === 'reglock') {
            target = '#tabReglock';
        } else if (hash === '#tabAddons' || section === 'addons') {
            target = '#tabAddons';
        }

        if (!target) {
            return;
        }

        document.body.setAttribute('data-dm-converted-route-section', section || target.replace('#tab', '').toLowerCase());

        selectors = [
            'a[href="' + target + '"]',
            'a[data-toggle="tab"][href="' + target + '"]',
            'button[data-target="' + target + '"]',
            '[data-toggle="tab"][data-target="' + target + '"]'
        ];

        for (i = 0; i < selectors.length; i++) {
            node = document.querySelector(selectors[i]);
            if (node) {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.tab) {
                    try {
                        window.jQuery(node).tab('show');
                    } catch (e) {}
                }
                try {
                    node.click();
                } catch (e2) {}
                break;
            }
        }
    }

    document.addEventListener('click', function (event) {
        var node = closestCandidate(event.target);
        var key;
        var target;

        if (!node || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        key = sectionKeyFromNode(node);
        target = buildRoute(key, node, false);
        if (!target) {
            return;
        }

        if (sameDocumentNavigation(target)) {
            target = buildRoute(key, node, true) || target;
        }

        try {
            window.sessionStorage.setItem('dmConvertedLastRouteKey', key);
            window.sessionStorage.setItem('dmConvertedLastRouteTarget', target);
        } catch (e) {}

        event.preventDefault();
        event.stopPropagation();
        window.location.assign(target);
    }, true);

    function shouldShowDiagnostic() {
        return /(?:\?|&)dmnavdiag=1005(?:&|$)/.test(window.location.search || '') || String(window.location.hash || '') === '#dmnavdiag1005';
    }

    function esc(value) {
        return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function showDiagnostic() {
        var panel;
        var rows = [];
        var nodes = document.querySelectorAll('a[href], [data-href], [data-url], [onclick]');
        var i;
        var node;
        var key;
        var target;
        var storedKey = '';
        var storedTarget = '';

        if (!shouldShowDiagnostic() || document.getElementById('dm-nav-diag-1005')) {
            return;
        }

        try {
            storedKey = window.sessionStorage.getItem('dmConvertedLastRouteKey') || '';
            storedTarget = window.sessionStorage.getItem('dmConvertedLastRouteTarget') || '';
        } catch (e) {}

        for (i = 0; i < nodes.length; i++) {
            node = nodes[i];
            key = sectionKeyFromNode(node);
            if (!key) {
                continue;
            }
            target = buildRoute(key, node, false);
            rows.push({
                text: (node.textContent || node.getAttribute('aria-label') || node.getAttribute('title') || '').replace(/\s+/g, ' ').trim().slice(0, 90),
                key: key,
                href: (node.getAttribute('href') || node.getAttribute('data-href') || node.getAttribute('data-url') || node.getAttribute('onclick') || '').slice(0, 220),
                target: target,
                normalized: node.getAttribute('data-dm-converted-route-normalized') || ''
            });
        }

        panel = document.createElement('div');
        panel.id = 'dm-nav-diag-1005';
        panel.style.cssText = 'position:fixed;z-index:2147483647;left:12px;right:12px;bottom:12px;max-height:55vh;overflow:auto;background:#fff;border:3px solid #163a5f;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,.25);font:13px/1.35 Arial,sans-serif;color:#182536;padding:12px;';
        panel.innerHTML = '<div style="display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:8px;">' +
            '<strong>DM Converted Main Link Diagnostic 1005</strong>' +
            '<button type="button" style="background:#b94a48;color:#fff;border:0;border-radius:6px;padding:5px 10px;" onclick="this.closest(\'#dm-nav-diag-1005\').remove()">Close</button>' +
            '</div>' +
            '<div><strong>Current URL:</strong> ' + esc(window.location.href) + '</div>' +
            '<div><strong>Domain ID found:</strong> ' + esc(findDomainId(document.body) || 'none') + '</div>' +
            '<div><strong>Last direct route:</strong> ' + esc(storedKey || 'none') + ' → ' + esc(storedTarget || '') + '</div>' +
            '<div><strong>Candidates found:</strong> ' + rows.length + '</div>' +
            '<table style="width:100%;border-collapse:collapse;margin-top:8px;">' +
            '<thead><tr><th style="text-align:left;border-bottom:1px solid #ddd;">Text</th><th style="text-align:left;border-bottom:1px solid #ddd;">Key</th><th style="text-align:left;border-bottom:1px solid #ddd;">Current href/source</th><th style="text-align:left;border-bottom:1px solid #ddd;">New target</th><th style="text-align:left;border-bottom:1px solid #ddd;">Norm</th></tr></thead>' +
            '<tbody>' + rows.map(function (row) {
                return '<tr>' +
                    '<td style="vertical-align:top;border-bottom:1px solid #eee;padding:4px;">' + esc(row.text) + '</td>' +
                    '<td style="vertical-align:top;border-bottom:1px solid #eee;padding:4px;">' + esc(row.key) + '</td>' +
                    '<td style="vertical-align:top;border-bottom:1px solid #eee;padding:4px;word-break:break-all;">' + esc(row.href) + '</td>' +
                    '<td style="vertical-align:top;border-bottom:1px solid #eee;padding:4px;word-break:break-all;">' + esc(row.target) + '</td>' +
                    '<td style="vertical-align:top;border-bottom:1px solid #eee;padding:4px;">' + esc(row.normalized) + '</td>' +
                '</tr>';
            }).join('') + '</tbody></table>';
        document.body.appendChild(panel);
    }

    function run() {
        normalizeAnchors();
        activateRequestedDomainTab();
        showDiagnostic();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }

    window.setTimeout(run, 100);
    window.setTimeout(run, 350);
    window.setTimeout(run, 1000);
    window.setTimeout(run, 2500);

    if (window.MutationObserver) {
        try {
            new MutationObserver(function () {
                run();
            }).observe(document.documentElement || document.body, { childList: true, subtree: true });
        } catch (e) {}
    }
})();
</script>
HTML;
});
