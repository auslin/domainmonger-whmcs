<?php
/**
 * DomainMonger patch 949 + 1005: converted domain-management menu route normalizer.
 *
 * Keeps the shared converted menus pointing at the converted/no-sidebar domain
 * management routes instead of old side-menu/current-tool/Dashboard routes.
 * Patch 1005: keeps the five domaindetails section routes on converted live pages while the old design-preview hook is bypassed for dmconverted routes.
 * Patch 1101: DNS Management links route directly to the confirmed fast native DNS page instead of bouncing through dnsmanagement.php.
 * This is frontend route cleanup only; it does not change registrar/module forms,
 * hidden fields, submit names, or backend behavior.
 * Patch 1231 update:
 * - The confirmed native DNS interface is now the default DNS Records route.
 * - Explicit raw-native escape flags remain untouched.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaFooterOutput', 1001, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? ''));

    $allowed = false;
    if ($scriptName === 'clientarea.php' && in_array($action, [
        'domaindetails',
        'domaincontacts',
        'domaingetepp',
        'domainemailforwarding',
        'domaindns',
    ], true)) {
        $allowed = true;
    }
    if ($scriptName === 'domainmanagement.php' && in_array($action, [
        'childns',
        'dnssec',
        'managednssec',
    ], true)) {
        $allowed = true;
    }
    if (in_array($scriptName, [
        'dnsmanagement.php',
        'domainforwarding.php',
        'emailmanagement.php',
    ], true)) {
        $allowed = true;
    }

    if (!$allowed) {
        return '';
    }

    if ($scriptName === 'clientarea.php' && $action === 'domaindns'
        && (isset($_REQUEST['dmplainnative']) || isset($_REQUEST['dmnativeold']) || isset($_REQUEST['dmfeednative']))) {
        return '';
    }

    $clientId = 0;
    if (!empty($_SESSION['uid'])) {
        $clientId = (int) $_SESSION['uid'];
    } elseif (!empty($_SESSION['clientareauserid'])) {
        $clientId = (int) $_SESSION['clientareauserid'];
    }
    if ($clientId <= 0) {
        return '';
    }

    $domainId = 0;
    if (isset($_GET['domainid'])) {
        $domainId = (int) $_GET['domainid'];
    } elseif (isset($_GET['id'])) {
        $domainId = (int) $_GET['id'];
    }
    $domainName = trim((string) ($_GET['domain'] ?? ''));

    try {
        $query = Capsule::table('tbldomains')
            ->select('id', 'domain')
            ->where('userid', $clientId);

        if ($domainId > 0) {
            $query->where('id', $domainId);
        } elseif ($domainName !== '') {
            $query->where('domain', $domainName);
        } else {
            return '';
        }

        $domainRow = $query->first();
        if (!$domainRow) {
            return '';
        }

        $domainId = (int) $domainRow->id;
        $domainName = (string) $domainRow->domain;
    } catch (\Throwable $e) {
        return '';
    }

    if ($domainId <= 0 || $domainName === '') {
        return '';
    }

    $routeMap = [
        'overview' => 'clientarea.php?action=domaindetails&id=' . $domainId . '&dmsection=overview&dmdesign=1&dmconverted=1#tabOverview',
        'autorenew' => 'clientarea.php?action=domaindetails&id=' . $domainId . '&dmsection=autorenew&dmdesign=1&dmconverted=1#tabAutorenew',
        'nameservers' => 'clientarea.php?action=domaindetails&id=' . $domainId . '&dmsection=nameservers&dmdesign=1&dmconverted=1#tabNameservers',
        'reglock' => 'clientarea.php?action=domaindetails&id=' . $domainId . '&dmsection=reglock&dmdesign=1&dmconverted=1#tabReglock',
        'addons' => 'clientarea.php?action=domaindetails&id=' . $domainId . '&dmsection=addons&dmdesign=1&dmconverted=1#tabAddons',
        'whois' => 'clientarea.php?action=domaincontacts&domainid=' . $domainId,
        // Include id, domainid, and domain so both the converted child template and old WHMCS router have what they expect.
        'privatens' => 'domainmanagement.php?action=childns&id=' . $domainId . '&domainid=' . $domainId . '&domain=' . rawurlencode($domainName),
        'dns' => 'clientarea.php?action=domaindns&domainid=' . $domainId,
        'dnssec' => 'dnsmanagement.php?action=managednszone&domain=' . rawurlencode($domainName) . '&domainid=' . $domainId . '&nsrecordtype=A#dm-dns-dnssec',
        'domainforwarding' => 'dnsmanagement.php?action=managednszone&domain=' . rawurlencode($domainName) . '&domainid=' . $domainId . '&nsrecordtype=A#dm-dns-domain-forwarding',
        'emailforwarding' => 'dnsmanagement.php?action=managednszone&domain=' . rawurlencode($domainName) . '&domainid=' . $domainId . '&nsrecordtype=A#dm-dns-email-forwarding',
        'getepp' => 'clientarea.php?action=domaingetepp&domainid=' . $domainId,
    ];

    $routeJson = json_encode($routeMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<script>
(function () {
    var routes = {$routeJson};

    function cleanText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function shouldSkipLink(link) {
        if (!link) { return true; }
        if (link.getAttribute('data-dm-route-lock') === '1' || (link.closest && link.closest('#dm-rc-unified-nav-1152'))) {
            return true;
        }
        var text = cleanText(link.textContent);
        var cls = String(link.className || '').toLowerCase();
        var href = String(link.getAttribute('href') || '').toLowerCase();

        // DomainMonger Patch 1827: the dedicated Manage Domains navbar link
        // already carries the correct converted Overview route without a hash.
        // Do not let this legacy normalizer append #tabOverview, which causes
        // the destination page to jump after it opens. Normal Overview links
        // continue through the existing converted-route normalization.
        if (text === 'manage domains') {
            return true;
        }

        // Explicit old/current-tool escape hatches must remain true exits.
        if (text.indexOf('open current') !== -1 || text.indexOf('current tool') !== -1 || cls.indexOf('open-current') !== -1) {
            return true;
        }

        // Do not touch non-page actions, form posts, modal controls, delete links, or javascript/mail/tel links.
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) {
            return true;
        }
        if (text.indexOf('delete') !== -1 || text.indexOf('remove') !== -1) {
            return true;
        }
        return false;
    }

    function inferRouteKey(link) {
        var href = cleanText(link.getAttribute('href'));
        var text = cleanText(link.textContent);
        var haystack = text + ' ' + href;

        // More specific matches first.
        if (haystack.indexOf('email forwarding') !== -1 || haystack.indexOf('domainemailforwarding') !== -1 || haystack.indexOf('emailmanagement.php') !== -1 || haystack.indexOf('managemailhost') !== -1) {
            return 'emailforwarding';
        }
        if (haystack.indexOf('domain forwarding') !== -1 || haystack.indexOf('activate domain forwarding') !== -1 || haystack.indexOf('domainforwarding.php') !== -1 || haystack.indexOf('managedomfwd') !== -1) {
            return 'domainforwarding';
        }
        if (haystack.indexOf('dnssec') !== -1 || haystack.indexOf('managednssec') !== -1 || haystack.indexOf('dnsseczone') !== -1) {
            return 'dnssec';
        }
        if (haystack.indexOf('private nameserver') !== -1 || haystack.indexOf('childns') !== -1 || haystack.indexOf('hosts') !== -1 && haystack.indexOf('nameserver') !== -1) {
            return 'privatens';
        }
        if (haystack.indexOf('whois contact info') !== -1 || haystack.indexOf('contact information') !== -1 || haystack.indexOf('domaincontacts') !== -1) {
            return 'whois';
        }
        if (haystack.indexOf('get epp') !== -1 || haystack.indexOf('domaingetepp') !== -1 || haystack.indexOf('auth code') !== -1 || haystack.indexOf('authorization code') !== -1) {
            return 'getepp';
        }
        if (haystack.indexOf('dns management') !== -1 || haystack.indexOf('dns records') !== -1 || haystack.indexOf('managednszone') !== -1) {
            return 'dns';
        }
        if (haystack.indexOf('registrar lock') !== -1 || haystack.indexOf('tabreglock') !== -1) {
            return 'reglock';
        }
        if (haystack.indexOf('auto renew') !== -1 || haystack.indexOf('tabautorenew') !== -1) {
            return 'autorenew';
        }
        if (haystack.indexOf('nameservers') !== -1 || haystack.indexOf('tabnameservers') !== -1) {
            return 'nameservers';
        }
        if (haystack.indexOf('addons') !== -1 || haystack.indexOf('tabaddons') !== -1) {
            return 'addons';
        }
        if (haystack.indexOf('overview') !== -1 || haystack.indexOf('taboverview') !== -1) {
            return 'overview';
        }
        return '';
    }

    function normalizeConvertedMenuLinks(root) {
        var scope = root || document;
        var selectors = [
            '.dm-domain-section-menu a',
            '.dm-epp-section-menu a',
            '.dm-pns-section-menu a',
            '.dm-dns-title-actions a',
            '.dm-dns-workspace a',
            'a[href*="domaindetails"]',
            'a[href*="domainmanagement.php?action=childns"]',
            'a[href*="domaingetepp"]',
            'a[href*="domaincontacts"]',
            'a[href*="dnsmanagement.php"]',
            'a[href*="domainforwarding.php"]',
            'a[href*="emailmanagement.php"]',
            'a[href*="domainemailforwarding"]'
        ];
        var links = scope.querySelectorAll(selectors.join(','));
        for (var i = 0; i < links.length; i++) {
            var link = links[i];
            if (shouldSkipLink(link)) { continue; }
            var key = inferRouteKey(link);
            if (!key || !routes[key]) { continue; }
            link.setAttribute('href', routes[key]);
            link.setAttribute('data-dm-normalized-route', key);
        }
    }

    function selectDnsHashTool() {
        var hash = String(window.location.hash || '').toLowerCase();
        if (!hash || !document.getElementById('dm-dns-unified-shell')) {
            return;
        }
        var desired = '';
        if (hash.indexOf('email-forwarding') !== -1) {
            desired = 'email forwarding';
        } else if (hash.indexOf('domain-forwarding') !== -1) {
            desired = 'domain forwarding';
        } else if (hash.indexOf('dnssec') !== -1) {
            desired = 'dnssec';
        } else if (hash.indexOf('soa') !== -1) {
            desired = 'soa';
        }
        if (!desired) { return; }
        var links = document.querySelectorAll('.dm-dns-workspace a');
        for (var i = 0; i < links.length; i++) {
            var linkText = cleanText(links[i].textContent);
            if (linkText.indexOf(desired) !== -1) {
                links[i].click();
                return;
            }
        }
    }

    function bindDomainHashRoutes() {
        if (document.body.getAttribute('data-dm-converted-route-normalizer-bound') === '1') {
            return;
        }
        document.body.setAttribute('data-dm-converted-route-normalizer-bound', '1');

        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }
            var link = event.target && event.target.closest ? event.target.closest('a[data-dm-normalized-route]') : null;
            if (!link) { return; }
            var key = link.getAttribute('data-dm-normalized-route') || '';

            // DNS Workspace forwarding/security links should behave as internal selectors when already on DNS Management.
            if (document.getElementById('dm-dns-unified-shell') && (key === 'domainforwarding' || key === 'emailforwarding' || key === 'dnssec')) {
                event.preventDefault();
                var targetText = key === 'domainforwarding' ? 'domain forwarding' : (key === 'emailforwarding' ? 'email forwarding' : 'dnssec');
                var links = document.querySelectorAll('.dm-dns-workspace a');
                for (var i = 0; i < links.length; i++) {
                    var text = cleanText(links[i].textContent);
                    if (text.indexOf(targetText) !== -1 && links[i] !== link) {
                        links[i].click();
                        return;
                    }
                }
            }
        }, true);
    }

    function boot() {
        normalizeConvertedMenuLinks(document);
        bindDomainHashRoutes();
        window.setTimeout(function () { normalizeConvertedMenuLinks(document); selectDnsHashTool(); }, 250);
        window.setTimeout(function () { normalizeConvertedMenuLinks(document); selectDnsHashTool(); }, 900);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
HTML;
});
