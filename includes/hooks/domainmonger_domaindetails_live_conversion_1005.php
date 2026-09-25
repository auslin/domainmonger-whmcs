<?php
/**
 * DomainMonger patch 1005: live converted domaindetails wrapper.
 *
 * Scope: clientarea.php?action=domaindetails with dmconverted/dmsection routes.
 * Purpose: prevent the five main domain-detail sections from looking like the old
 * side-menu/preview page while preserving the existing WHMCS forms, hidden fields,
 * submit names, tokens, and registrar behavior.
 * Patch 1101/1103: DNS Management temporarily pointed directly to the native DNS route.
 * Patch 1109 update:
 * - DNS Management links now point back to dnsmanagement.php so the ResellerClub URL
 *   stays visible while the fast native DNS feed powers the table.
 * Patch 1163 update:
 * - Simplify the Overview action area, move the orange primary state to Renew,
 *   and remove the duplicated summary and native "What would you like to do today?" block.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaFooterOutput', 1002, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? ''));

    if ($scriptName !== 'clientarea.php' || $action !== 'domaindetails') {
        return '';
    }

    // Keep old/current WHMCS detail routes available. Only convert explicit converted routes.
    if (empty($_GET['dmconverted']) && empty($_GET['dmsection']) && empty($_GET['dmnav'])) {
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
    if (isset($_GET['id'])) {
        $domainId = (int) $_GET['id'];
    } elseif (isset($_GET['domainid'])) {
        $domainId = (int) $_GET['domainid'];
    }
    if ($domainId <= 0) {
        return '';
    }

    try {
        $domainRow = Capsule::table('tbldomains')
            ->select('id', 'domain', 'status', 'registrar', 'expirydate', 'nextduedate', 'donotrenew')
            ->where('userid', $clientId)
            ->where('id', $domainId)
            ->first();
    } catch (\Throwable $e) {
        return '';
    }

    if (!$domainRow) {
        return '';
    }

    $domainId = (int) $domainRow->id;
    $domainName = (string) $domainRow->domain;
    $domainStatus = (string) $domainRow->status;
    $registrar = (string) $domainRow->registrar;
    $expiryDate = (string) $domainRow->expirydate;
    $nextDueDate = (string) $domainRow->nextduedate;
    $autoRenew = ((int) $domainRow->donotrenew === 1) ? 'Disabled' : 'Enabled';
    $encodedDomain = rawurlencode($domainName);
    $nativeDnsUrl = 'clientarea.php?action=domaindns&domainid=' . $domainId;

    $e = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    $makeDetail = static function ($section, $hash) use ($domainId) {
        return 'clientarea.php?action=domaindetails&id=' . $domainId
            . '&dmsection=' . rawurlencode($section)
            . '&dmdesign=1&dmconverted=1'
            . $hash;
    };

    $menuItems = [
        ['key' => 'overview', 'label' => 'Overview', 'meta' => 'Domain', 'href' => $makeDetail('overview', '#tabOverview'), 'target' => 'tabOverview'],
        ['key' => 'autorenew', 'label' => 'Auto Renew', 'meta' => 'Billing', 'href' => $makeDetail('autorenew', '#tabAutorenew'), 'target' => 'tabAutorenew'],
        ['key' => 'nameservers', 'label' => 'Nameservers', 'meta' => 'DNS', 'href' => $makeDetail('nameservers', '#tabNameservers'), 'target' => 'tabNameservers'],
        ['key' => 'reglock', 'label' => 'Registrar Lock', 'meta' => 'Security', 'href' => $makeDetail('reglock', '#tabReglock'), 'target' => 'tabReglock'],
        ['key' => 'addons', 'label' => 'Addons', 'meta' => 'Domain', 'href' => $makeDetail('addons', '#tabAddons'), 'target' => 'tabAddons'],
        ['key' => 'whois', 'label' => 'WHOIS Contact Info', 'meta' => 'Contacts', 'href' => 'clientarea.php?action=domaincontacts&domainid=' . $domainId, 'target' => ''],
        ['key' => 'privatens', 'label' => 'Private Nameservers', 'meta' => 'Hosts', 'href' => 'domainmanagement.php?action=childns&id=' . $domainId . '&domainid=' . $domainId . '&domain=' . $encodedDomain, 'target' => ''],
        ['key' => 'dns', 'label' => 'DNS Management', 'meta' => 'DNS', 'href' => $nativeDnsUrl, 'target' => ''],
        ['key' => 'dnssec', 'label' => 'DNSSEC Management', 'meta' => 'DNS', 'href' => 'dnsmanagement.php?action=managednszone&domain=' . $encodedDomain . '&domainid=' . $domainId . '&nsrecordtype=DNSSEC&dmsection=dnssec&dmdesign=1&dmconverted=1#dm-dns-dnssec', 'target' => ''],
        ['key' => 'domainforwarding', 'label' => 'Domain Forwarding', 'meta' => 'Forwarding', 'href' => 'dnsmanagement.php?action=managednszone&domain=' . $encodedDomain . '&domainid=' . $domainId . '&nsrecordtype=A#dm-dns-domain-forwarding', 'target' => ''],
        ['key' => 'emailforwarding', 'label' => 'Email Forwarding', 'meta' => 'Forwarding', 'href' => 'dnsmanagement.php?action=managednszone&domain=' . $encodedDomain . '&domainid=' . $domainId . '&nsrecordtype=A#dm-dns-email-forwarding', 'target' => ''],
        ['key' => 'getepp', 'label' => 'Get EPP Code', 'meta' => 'Transfer', 'href' => 'clientarea.php?action=domaingetepp&domainid=' . $domainId . '&dmsection=epp&dmdesign=1&dmconverted=1', 'target' => ''],
    ];

    $menuHtml = '<ul class="dm-domain-section-menu dm-live-domain-section-menu" aria-label="Domain management sections">';
    foreach ($menuItems as $item) {
        $targetAttr = $item['target'] !== '' ? ' data-dm-tab-target="' . $e($item['target']) . '"' : '';
        $menuHtml .= '<li><a class="dm-domain-section-link" data-dm-section-key="' . $e($item['key']) . '" href="' . $e($item['href']) . '"' . $targetAttr . '>'
            . $e($item['label']) . '<span>' . $e($item['meta']) . '</span></a></li>';
    }
    $menuHtml .= '</ul>';

    $section = strtolower(preg_replace('/[^a-z0-9_-]/i', '', (string) ($_GET['dmsection'] ?? $_GET['dmnav'] ?? 'overview')));
    if ($section === '') {
        $section = 'overview';
    }

    $payload = [
        'domainId' => (string) $domainId,
        'domainName' => $domainName,
        'status' => $domainStatus,
        'registrar' => $registrar,
        'expiryDate' => $expiryDate,
        'nextDueDate' => $nextDueDate,
        'autoRenew' => $autoRenew,
        'section' => $section,
        'menuHtml' => $menuHtml,
    ];

    $json = json_encode($payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<style id="dm-domaindetails-live-conversion-1005-css">
body.whmcsbody.dm-domaindetails-live-1005 {
    --dm-navy: #163a5f;
    --dm-navy-hover: #214e7a;
    --dm-orange: #f58220;
    --dm-orange-soft: #d8741f;
    --dm-red: #b94a48;
    --dm-text: #293f56;
    --dm-muted: #60738a;
    --dm-border: rgba(17, 43, 77, 0.14);
    --dm-border-soft: rgba(17, 43, 77, 0.08);
    --dm-shadow: 0 2px 8px rgba(17, 43, 77, 0.055);
}
body.whmcsbody.dm-domaindetails-live-1005 .sidebar,
body.whmcsbody.dm-domaindetails-live-1005 .secondary-sidebar,
body.whmcsbody.dm-domaindetails-live-1005 .panel-sidebar,
body.whmcsbody.dm-domaindetails-live-1005 .dm-client-area-sidebar,
body.whmcsbody.dm-domaindetails-live-1005 aside,
body.whmcsbody.dm-domaindetails-live-1005 [class*="sidebar"] {
    display: none !important;
}
body.whmcsbody.dm-domaindetails-live-1005 #main-body .primary-content,
body.whmcsbody.dm-domaindetails-live-1005 #main-body .main-content,
body.whmcsbody.dm-domaindetails-live-1005 #main-body .col-md-9,
body.whmcsbody.dm-domaindetails-live-1005 #main-body .col-lg-9,
body.whmcsbody.dm-domaindetails-live-1005 #main-body .col-xl-9 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    display: flex;
    flex-wrap: wrap;
    gap: 0;
    list-style: none;
    margin: 0 0 14px;
    overflow: hidden;
    padding: 0;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu li { margin: 0; }
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu a {
    align-items: center;
    border-right: 1px solid var(--dm-border-soft);
    color: var(--dm-navy);
    display: flex;
    flex-direction: column;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    line-height: 1.15;
    min-height: 50px;
    min-width: 108px;
    padding: 9px 12px;
    text-align: center;
    text-decoration: none !important;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu a span {
    color: #60738a;
    display: block;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .035em;
    margin-bottom: 2px;
    text-transform: uppercase;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu a:hover,
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu a:focus {
    background: #fff4eb;
    color: var(--dm-orange);
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu a.dm-active,
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu a[aria-current="page"] {
    background: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu a.dm-active span,
body.whmcsbody.dm-domaindetails-live-1005 .dm-domain-section-menu a[aria-current="page"] span {
    color: rgba(255,255,255,.9) !important;
}
body.whmcsbody.dm-domaindetails-live-1005 .tab-content {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin-top: 0;
    padding: 0;
}
body.whmcsbody.dm-domaindetails-live-1005 .tab-content > .tab-pane {
    padding: 18px;
}
body.whmcsbody.dm-domaindetails-live-1005 .tab-content > .tab-pane > .card {
    border: 0;
    box-shadow: none;
    margin-bottom: 0;
}
body.whmcsbody.dm-domaindetails-live-1005 .tab-content > .tab-pane > .card > .card-body {
    padding: 0;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-section-header,
body.whmcsbody.dm-domaindetails-live-1005 .dm-overview-live-summary,
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-action-strip {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 14px;
    padding: 14px 16px;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-section-header {
    align-items: center;
    display: flex;
    gap: 16px;
    justify-content: space-between;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-section-header span {
    color: var(--dm-orange);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .055em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-section-header h3 {
    color: var(--dm-navy);
    font-size: 22px;
    font-weight: 900;
    line-height: 1.15;
    margin: 0;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-section-header p {
    color: var(--dm-muted);
    font-size: 13px;
    margin: 5px 0 0;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-domain-pill {
    background: var(--dm-navy);
    border-radius: 6px;
    color: #fff;
    font-weight: 900;
    padding: 8px 12px;
    white-space: nowrap;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-overview-live-summary {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-overview-live-summary div {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 7px;
    padding: 10px;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-overview-live-summary span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .045em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-overview-live-summary strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    font-weight: 900;
    overflow-wrap: anywhere;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-action-strip {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-action-strip a {
    background: var(--dm-navy);
    border-radius: 6px;
    color: #fff !important;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    padding: 10px 12px;
    text-decoration: none !important;
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-action-strip a.dm-primary {
    background: var(--dm-orange);
}
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-action-strip a:hover,
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-action-strip a:focus {
    background: var(--dm-navy-hover);
    color: #fff !important;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview.dm-overview-live-1005 > h3:first-of-type,
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview.dm-overview-live-1005 .card-body > h3.card-title:first-of-type,
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-generic-converted > h3:first-of-type,
body.whmcsbody.dm-domaindetails-live-1005 .dm-live-generic-converted .card-body > h3.card-title:first-of-type {
    display: none !important;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview ul {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    list-style: none;
    padding: 12px 14px;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview ul li {
    margin: 5px 0;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview a,
body.whmcsbody.dm-domaindetails-live-1005 .tab-content a {
    color: var(--dm-navy);
    font-weight: 700;
    text-decoration: none !important;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview a:hover,
body.whmcsbody.dm-domaindetails-live-1005 .tab-content a:hover {
    color: var(--dm-orange);
}
@media (max-width: 900px) {
    body.whmcsbody.dm-domaindetails-live-1005 .dm-overview-live-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    body.whmcsbody.dm-domaindetails-live-1005 .dm-live-section-header { align-items: flex-start; flex-direction: column; }
    body.whmcsbody.dm-domaindetails-live-1005 .dm-overview-live-summary { grid-template-columns: 1fr; }
}
</style>
<script id="dm-domaindetails-live-conversion-1005-js">
(function () {
    'use strict';
    var data = {$json};
    var sectionToTab = {
        overview: 'tabOverview',
        autorenew: 'tabAutorenew',
        nameservers: 'tabNameservers',
        reglock: 'tabReglock',
        addons: 'tabAddons'
    };
    var labels = {
        tabOverview: ['Overview', 'Review core domain details, renewal dates, and common actions.'],
        tabAutorenew: ['Auto Renew', 'Review or update the domain auto-renew status.'],
        tabNameservers: ['Nameservers', 'Manage the nameservers assigned to this domain.'],
        tabReglock: ['Registrar Lock', 'Review or update registrar lock protection.'],
        tabAddons: ['Addons', 'Manage available domain addon services.']
    };

    function esc(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function route(section, hash) {
        return 'clientarea.php?action=domaindetails&id=' + encodeURIComponent(data.domainId)
            + '&dmsection=' + encodeURIComponent(section)
            + '&dmdesign=1&dmconverted=1'
            + (hash || '');
    }

    function requestedTab() {
        var params = new URLSearchParams(window.location.search || '');
        var section = String(params.get('dmsection') || params.get('dmnav') || data.section || '').toLowerCase();
        var hash = String(window.location.hash || '').replace('#', '');
        if (hash && document.getElementById(hash)) {
            return hash;
        }
        return sectionToTab[section] || 'tabOverview';
    }

    function ensureMenu() {
        var tabContent = document.querySelector('.tab-content');
        if (!tabContent) {
            return;
        }
        if (!document.querySelector('.dm-domain-section-menu')) {
            tabContent.insertAdjacentHTML('beforebegin', data.menuHtml || '');
        }
    }

    function setActiveMenu(tabId) {
        var map = {
            tabOverview: 'overview',
            tabAutorenew: 'autorenew',
            tabNameservers: 'nameservers',
            tabReglock: 'reglock',
            tabAddons: 'addons'
        };
        var activeKey = map[tabId] || '';
        var links = document.querySelectorAll('.dm-domain-section-menu a');
        for (var i = 0; i < links.length; i++) {
            var key = links[i].getAttribute('data-dm-section-key') || '';
            var target = links[i].getAttribute('data-dm-tab-target') || '';
            var isActive = (key && key === activeKey) || (target && target === tabId);
            links[i].classList.toggle('dm-active', isActive);
            if (isActive) {
                links[i].setAttribute('aria-current', 'page');
            } else {
                links[i].removeAttribute('aria-current');
            }
        }
    }

    function activateTab(tabId) {
        var target = document.getElementById(tabId);
        if (!target) {
            return;
        }
        var tabContent = target.closest('.tab-content');
        if (tabContent) {
            var panes = tabContent.querySelectorAll('.tab-pane');
            for (var i = 0; i < panes.length; i++) {
                panes[i].classList.remove('active', 'show', 'in');
            }
        }
        target.classList.add('active', 'show', 'in');
        setActiveMenu(tabId);
    }

    function headerHtml(tabId) {
        var label = labels[tabId] || ['Manage Domain', 'Manage this domain.'];
        return '<div class="dm-live-section-header"><div><h3>'
            + esc(label[0]) + '</h3><p>' + esc(label[1]) + '</p></div></div>';
    }

    function addGenericHeader(tabId) {
        var tab = document.getElementById(tabId);
        var existingHeaders = {
            tabAutorenew: '.dm-ar-card-header',
            tabNameservers: '.dm-ns-card-header',
            tabReglock: '.dm-rl-card-header',
            tabAddons: '.dm-addons-card-header'
        };
        if (!tab || tab.getAttribute('data-dm-live-header-1005') === '1') {
            return;
        }
        if (existingHeaders[tabId] && tab.querySelector(existingHeaders[tabId])) {
            return;
        }
        tab.setAttribute('data-dm-live-header-1005', '1');
        tab.classList.add('dm-live-generic-converted');
        var container = tab.querySelector('.card > .card-body') || tab;
        container.insertAdjacentHTML('afterbegin', headerHtml(tabId));
    }

    function findRenewHref(tab) {
        var links = tab ? tab.querySelectorAll('a[href]') : [];
        for (var i = 0; i < links.length; i++) {
            var href = links[i].getAttribute('href') || '';
            var text = (links[i].textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
            if (text.indexOf('renew domain') !== -1
                || text.indexOf('renew your domain') !== -1
                || href.indexOf('/domain/renew/') !== -1
                || href.indexOf('action=domainrenew') !== -1
                || href.indexOf('domain=renew') !== -1) {
                return href;
            }
        }
        return '';
    }

    function removeNativeOverviewActions(tab) {
        if (!tab) {
            return;
        }
        var headings = tab.querySelectorAll('h3, h4, h5');
        for (var i = 0; i < headings.length; i++) {
            var text = (headings[i].textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
            if (text.indexOf('what would you like to do today') === -1) {
                continue;
            }
            var list = headings[i].nextElementSibling;
            if (list && (list.tagName || '').toLowerCase() === 'ul') {
                list.remove();
            }
            headings[i].remove();
        }
    }

    function convertOverview() {
        var tab = document.getElementById('tabOverview');
        if (!tab || tab.getAttribute('data-dm-overview-live-1005') === '1') {
            return;
        }
        var renewHref = findRenewHref(tab);
        removeNativeOverviewActions(tab);
        tab.setAttribute('data-dm-overview-live-1005', '1');
        tab.classList.add('dm-overview-live-1005');
        var container = tab.querySelector('.card > .card-body') || tab;
        container.insertAdjacentHTML('afterbegin', headerHtml('tabOverview')
            + '<div class="dm-live-action-strip">'
            + '<a href="' + route('nameservers', '#tabNameservers') + '">Nameservers</a>'
            + '<a href="' + route('autorenew', '#tabAutorenew') + '">Auto Renew</a>'
            + '<a href="' + route('reglock', '#tabReglock') + '">Registrar Lock</a>'
            + '<a href="clientarea.php?action=domaincontacts&domainid=' + encodeURIComponent(data.domainId) + '">WHOIS Contact Info</a>'
            + '<a href="clientarea.php?action=domaindns&domainid=' + encodeURIComponent(data.domainId) + '">DNS Management</a>'
            + (renewHref ? '<a class="dm-primary" href="' + esc(renewHref) + '">Renew</a>' : '')
            + '</div>');
    }

    function bindMenu() {
        if (document.body.getAttribute('data-dm-live-menu-bound-1005') === '1') {
            return;
        }
        document.body.setAttribute('data-dm-live-menu-bound-1005', '1');
        document.addEventListener('click', function (event) {
            var link = event.target && event.target.closest ? event.target.closest('.dm-domain-section-menu a[data-dm-tab-target]') : null;
            if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }
            var tabId = link.getAttribute('data-dm-tab-target') || '';
            if (!tabId || !document.getElementById(tabId)) {
                return;
            }
            event.preventDefault();
            if (history && history.pushState) {
                history.pushState(null, '', link.getAttribute('href'));
            }
            activateTab(tabId);
        }, true);
    }

    function cleanupOldPreviewShell() {
        var text = document.body ? (document.body.textContent || '') : '';
        // If the old preview shell somehow already rendered before this hook, do not try to
        // destructively rewrite it. The server-side preview bypass in patch 1005 should prevent it.
        if (text.indexOf('No-side-menu design preview') !== -1 && text.indexOf('Production Layout Preview') !== -1) {
            document.body.setAttribute('data-dm-preview-shell-detected-1005', '1');
        }
    }

    function removeSupersededInnerHeaders() {
        // Patch 1164: the shared ResellerClub navigation now supplies the only
        // page-name header for these sections. Remove the older white/domain
        // summary header rather than allowing two title areas to stack.
        var tabIds = ['tabAutorenew', 'tabNameservers', 'tabReglock', 'tabAddons'];
        for (var i = 0; i < tabIds.length; i++) {
            var tab = document.getElementById(tabIds[i]);
            if (!tab) { continue; }
            var headers = tab.querySelectorAll('.dm-live-section-header');
            for (var j = 0; j < headers.length; j++) {
                headers[j].remove();
            }
        }
    }

    function run() {
        document.body.classList.add('dm-domaindetails-live-1005');
        ensureMenu();
        convertOverview();
        removeSupersededInnerHeaders();
        activateTab(requestedTab());
        bindMenu();
        cleanupOldPreviewShell();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
    window.setTimeout(run, 150);
    window.setTimeout(run, 500);
    window.addEventListener('hashchange', function () {
        activateTab(requestedTab());
    });
})();
</script>
HTML;
});
