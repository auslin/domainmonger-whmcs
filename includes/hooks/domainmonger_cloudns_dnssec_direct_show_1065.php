<?php
/**
 * DomainMonger ClouDNS DNSSEC Direct Show Route 1065 / passive soft-navigation compatibility 1615
 *
 * Purpose:
 * - 1063/1064 did not change the live DNSSEC click behavior.
 * - Use the stable DNSSEC display route (customAction=dnssec-show) for menu clicks.
 * - Keep this scoped to ClouDNS productdetails pages and avoid broad WHMCS changes.
 * - Patch 1615 removes the old forced window.location navigation. The hook now
 *   only keeps the native dnssec-show href correct and lets Patch 1612 perform
 *   the same confirmed soft navigation used by all other DNSPlus pages.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    $scriptName = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = (string) ($_GET['action'] ?? '');
    if ($scriptName !== 'clientarea.php' || $action !== 'productdetails') {
        return '';
    }

    $showDiag = ((string) ($_GET['dmdnsseclinkdiag'] ?? '') === '1065') ? 'true' : 'false';

    return <<<HTML
<script id="dm-cloudns-dnssec-direct-show-1065">
(function () {
    'use strict';

    var showDiag = {$showDiag};

    function getParam(name) {
        try {
            return new URL(window.location.href).searchParams.get(name) || '';
        } catch (e) {
            return '';
        }
    }

    function textOf(node) {
        return String((node && node.textContent) || '').replace(/\s+/g, ' ').trim();
    }

    function isDnssecAnchor(anchor) {
        if (!anchor || !anchor.tagName || anchor.tagName.toLowerCase() !== 'a') {
            return false;
        }
        var label = textOf(anchor).toLowerCase();
        var href = String(anchor.getAttribute('href') || '').toLowerCase();
        return label === 'dnssec' || href.indexOf('customaction=dnssec') !== -1 || anchor.getAttribute('data-dm-dnssec-target') === 'dnssec-show';
    }

    function readServiceId() {
        var id = getParam('id');
        if (id) {
            return id;
        }
        var link = document.querySelector('a[href*="clientarea.php?action=productdetails"][href*="id="]');
        if (link) {
            try {
                return new URL(link.getAttribute('href'), window.location.href).searchParams.get('id') || '';
            } catch (e) {}
        }
        return '';
    }

    function readZone() {
        var zone = getParam('zone');
        if (zone) {
            return zone;
        }
        var switcher = document.getElementById('cloudns-global-domain-switcher-select');
        if (switcher && switcher.value) {
            return switcher.value;
        }
        var link = document.querySelector('a[href*="customAction=zone-settings"][href*="zone="], a[href*="customaction=zone-settings"][href*="zone="]');
        if (link) {
            try {
                return new URL(link.getAttribute('href'), window.location.href).searchParams.get('zone') || '';
            } catch (e) {}
        }
        return '';
    }

    function buildTarget() {
        var serviceId = readServiceId();
        var zone = readZone();
        if (!serviceId || !zone) {
            return '';
        }
        return 'clientarea.php?action=productdetails&id=' + encodeURIComponent(serviceId) + '&customAction=dnssec-show&zone=' + encodeURIComponent(zone);
    }

    function findDnssecAnchors() {
        return Array.prototype.slice.call(document.querySelectorAll('a')).filter(isDnssecAnchor);
    }

    function applyFix() {
        var target = buildTarget();
        var anchors = findDnssecAnchors();
        anchors.forEach(function (anchor) {
            if (target) {
                anchor.setAttribute('href', target);
                anchor.setAttribute('data-dm-dnssec-direct-show-1065', '1');
            } else {
                anchor.setAttribute('data-dm-dnssec-direct-show-1065', 'missing-service-or-zone');
            }
        });
        return anchors;
    }

    // Patch 1615: no click interception here. Once the href is corrected, the
    // confirmed Patch 1612 DNSPlus controller handles the link uniformly.


    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'})[ch];
        });
    }

    function showDiagnostic(anchors) {
        if (!showDiag) {
            return;
        }
        var rows = anchors.map(function (anchor, index) {
            return {
                index: index + 1,
                text: textOf(anchor),
                href: anchor.getAttribute('href') || '',
                fixed: anchor.getAttribute('data-dm-dnssec-direct-show-1065') || '',
                classes: anchor.className || ''
            };
        });
        var panel = document.createElement('div');
        panel.id = 'dm-cloudns-dnssec-direct-show-diag-1065';
        panel.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:2147483600;max-width:620px;background:#fff;border:2px solid #163a5f;border-radius:10px;box-shadow:0 12px 34px rgba(0,0,0,.25);font:13px/1.45 Arial,sans-serif;color:#1f2a35;overflow:hidden;';
        panel.innerHTML = '' +
            '<div style="background:#163a5f;color:#fff;padding:10px 12px;font-weight:bold;display:flex;justify-content:space-between;gap:12px;align-items:center;">' +
                '<span>DM ClouDNS DNSSEC Diagnostic 1065</span>' +
                '<button type="button" style="background:transparent;border:0;color:#fff;font-size:20px;line-height:1;cursor:pointer;">×</button>' +
            '</div>' +
            '<div style="padding:12px;">' +
                '<div><strong>URL:</strong> ' + escapeHtml(window.location.href) + '</div>' +
                '<div><strong>Service ID:</strong> ' + escapeHtml(readServiceId()) + '</div>' +
                '<div><strong>Zone:</strong> ' + escapeHtml(readZone()) + '</div>' +
                '<div><strong>Target:</strong> ' + escapeHtml(buildTarget()) + '</div>' +
                '<div style="margin-top:8px;"><strong>DNSSEC anchors:</strong></div>' +
                '<pre style="white-space:pre-wrap;max-height:260px;overflow:auto;background:#f6f8fa;border:1px solid #d9e1ea;border-radius:6px;padding:8px;margin:6px 0 0;">' + escapeHtml(JSON.stringify(rows, null, 2)) + '</pre>' +
            '</div>';
        panel.querySelector('button').addEventListener('click', function () {
            panel.remove();
        });
        document.body.appendChild(panel);
    }

    function init() {
        var anchors = applyFix();
        showDiagnostic(anchors);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }

    // Patch 1612 replaces #main-body without reloading footer hooks. Correct
    // the freshly inserted DNSPlus menu after every successful soft swap.
    document.addEventListener('dmCloudnsSoftNavigated1612', function () {
        applyFix();
    });
})();
</script>
HTML;
});
