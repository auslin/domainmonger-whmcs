<?php
/**
 * DomainMonger Patch 1336
 * ResellerClub converted Overview: display the native plain-text domain status
 * as the same semantic pill used elsewhere in the client area.
 *
 * Scope: converted clientarea.php?action=domaindetails Overview only.
 * Does not alter domain data, actions, navigation, forms, or registrar behavior.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaFooterOutput', 1012, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? ''));

    if ($scriptName !== 'clientarea.php' || $action !== 'domaindetails') {
        return '';
    }

    // Keep this page-specific to the converted ResellerClub workspace.
    if (empty($_GET['dmconverted']) && empty($_GET['dmdesign']) && empty($_GET['dmsection'])) {
        return '';
    }

    $section = strtolower(trim((string) ($_GET['dmsection'] ?? $_GET['dmnav'] ?? 'overview')));
    if ($section !== '' && $section !== 'overview') {
        return '';
    }

    $clientId = 0;
    if (!empty($_SESSION['uid'])) {
        $clientId = (int) $_SESSION['uid'];
    } elseif (!empty($_SESSION['clientareauserid'])) {
        $clientId = (int) $_SESSION['clientareauserid'];
    }

    $domainId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_GET['domainid'] ?? 0);
    if ($clientId <= 0 || $domainId <= 0) {
        return '';
    }

    try {
        $domain = Capsule::table('tbldomains')
            ->select('status')
            ->where('id', $domainId)
            ->where('userid', $clientId)
            ->first();
    } catch (\Throwable $e) {
        return '';
    }

    if (!$domain) {
        return '';
    }

    $status = trim((string) $domain->status);
    if ($status === '') {
        return '';
    }

    $normalized = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $status));
    $normalized = trim($normalized, '-');

    $stateClass = 'dm-status-info';
    if ($normalized === 'active') {
        $stateClass = 'dm-status-active';
    } elseif (in_array($normalized, ['expired', 'cancelled', 'canceled', 'terminated', 'transferred-away', 'inactive'], true)) {
        $stateClass = 'dm-status-muted';
    } elseif (in_array($normalized, ['suspended', 'fraud', 'overdue', 'unpaid'], true)) {
        $stateClass = 'dm-status-danger';
    } elseif (in_array($normalized, ['pending-transfer', 'pending-registration', 'grace', 'redemption'], true)) {
        $stateClass = 'dm-status-warning';
    }

    $statusJson = json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $classJson = json_encode($stateClass, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<style id="domainmonger-resellerclub-overview-status-pill-1336-css">
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview .dm-overview-status-pill-1336 {
    align-items: center;
    border: 1px solid transparent;
    border-radius: 999px !important;
    display: inline-flex;
    font-size: 12px;
    font-weight: 700 !important;
    justify-content: center;
    line-height: 1.15 !important;
    min-height: 24px;
    padding: 4px 10px;
    text-decoration: none !important;
    white-space: nowrap;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview .dm-overview-status-pill-1336.dm-status-active {
    background: #2f7d4f !important;
    border-color: #286b43 !important;
    color: #fff !important;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview .dm-overview-status-pill-1336.dm-status-muted {
    background: #6c757d !important;
    border-color: #5f6871 !important;
    color: #fff !important;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview .dm-overview-status-pill-1336.dm-status-danger {
    background: #b94a48 !important;
    border-color: #a64240 !important;
    color: #fff !important;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview .dm-overview-status-pill-1336.dm-status-warning {
    background: #fff3cd !important;
    border-color: #f0d98c !important;
    color: #604200 !important;
}
body.whmcsbody.dm-domaindetails-live-1005 #tabOverview .dm-overview-status-pill-1336.dm-status-info {
    background: #163a5f !important;
    border-color: #163a5f !important;
    color: #fff !important;
}
</style>
<script id="domainmonger-resellerclub-overview-status-pill-1336-js">
(function () {
    'use strict';

    var statusText = {$statusJson};
    var stateClass = {$classJson};

    function normalizeLabel(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .replace(/:\s*$/, '')
            .trim()
            .toLowerCase();
    }

    function applyStatusPill() {
        var tab = document.getElementById('tabOverview');
        if (!tab) {
            return false;
        }

        var headings = tab.querySelectorAll('h5');
        var heading = null;
        for (var i = 0; i < headings.length; i++) {
            if (normalizeLabel(headings[i].textContent) === 'status') {
                heading = headings[i];
                break;
            }
        }

        if (!heading || !heading.parentElement) {
            return false;
        }

        var cell = heading.parentElement;
        var existing = cell.querySelector('.dm-overview-status-pill-1336');
        if (existing) {
            existing.className = 'domain-status dm-overview-status-pill-1336 ' + stateClass;
            existing.textContent = statusText;
            return true;
        }

        // This native status cell contains only the heading and its plain-text value.
        // Remove only the nodes after the heading, leaving the native layout intact.
        while (heading.nextSibling) {
            heading.parentElement.removeChild(heading.nextSibling);
        }

        var pill = document.createElement('span');
        pill.className = 'domain-status dm-overview-status-pill-1336 ' + stateClass;
        pill.textContent = statusText;
        pill.setAttribute('role', 'status');
        pill.setAttribute('aria-label', 'Domain status: ' + statusText);
        cell.appendChild(pill);
        cell.setAttribute('data-dm-overview-status-pill-1336', '1');
        return true;
    }

    function run() {
        applyStatusPill();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run, { once: true });
    } else {
        run();
    }

    // The converted Overview finishes its own DOM pass shortly after page ready.
    window.setTimeout(run, 180);
    window.setTimeout(run, 600);
})();
</script>
HTML;
});
