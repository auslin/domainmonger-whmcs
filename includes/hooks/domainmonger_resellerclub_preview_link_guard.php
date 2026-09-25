<?php
/**
 * DomainMonger patch 852: guard Private Nameservers preview links.
 *
 * The design preview is rendered safely from the confirmed dnsmanagement.php
 * route with dmdesign=1. Some older preview buttons can still point at routes
 * that redirect to Dashboard before the preview hook can render. This small
 * guard rewrites only preview/prototype links for Private Nameservers to the
 * confirmed safe route. It leaves Open Current / Use Current Form links alone.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaFooterOutput', 999, function ($vars) {
    if (empty($_GET['dmdesign']) || (string) $_GET['dmdesign'] !== '1') {
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

    $domainName = '';
    if (!empty($_GET['domain'])) {
        $domainName = trim((string) $_GET['domain']);
    }

    try {
        if ($domainId > 0) {
            $domainRow = Capsule::table('tbldomains')
                ->select('id', 'domain')
                ->where('userid', $clientId)
                ->where('id', $domainId)
                ->first();
            if ($domainRow) {
                $domainId = (int) $domainRow->id;
                $domainName = (string) $domainRow->domain;
            }
        } elseif ($domainName !== '') {
            $domainRow = Capsule::table('tbldomains')
                ->select('id', 'domain')
                ->where('userid', $clientId)
                ->where('domain', $domainName)
                ->first();
            if ($domainRow) {
                $domainId = (int) $domainRow->id;
                $domainName = (string) $domainRow->domain;
            }
        }
    } catch (\Throwable $e) {
        return '';
    }

    if ($domainId <= 0 || $domainName === '') {
        return '';
    }

    $scriptDir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/manage/dnsmanagement.php')));
    $scriptDir = rtrim($scriptDir, '/');
    if ($scriptDir === '' || $scriptDir === '.') {
        $scriptDir = '/manage';
    }

    $safePrivateNsUrl = $scriptDir
        . '/dnsmanagement.php?action=managednszone&domain=' . rawurlencode($domainName)
        . '&domainid=' . $domainId
        . '&dmdesign=1&dmpanel=privatens';

    $safeJson = json_encode($safePrivateNsUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<script>
(function () {
    var safePrivateNsUrl = {$safeJson};

    function cleanText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function isPrivateNsPreviewLink(anchor) {
        if (!anchor || !safePrivateNsUrl) {
            return false;
        }

        var text = cleanText(anchor.textContent);
        var href = String(anchor.getAttribute('href') || '').toLowerCase();

        // Leave the real/current WHMCS interface links untouched.
        if (text.indexOf('open current') !== -1 || text.indexOf('use current') !== -1) {
            return false;
        }

        if (text === 'preview private nameservers' || text === 'view prototype') {
            return true;
        }

        if (href.indexOf('dmpanel=privatens') !== -1 && (text.indexOf('preview') !== -1 || text.indexOf('prototype') !== -1)) {
            return true;
        }

        if (text === 'preview design') {
            var card = anchor.closest('.dm-rc-action-card, .dm-rc-rollout-card, .dm-rc-prototype-banner, .dm-rc-section-card, li');
            if (card && /private nameservers/i.test(card.textContent || '')) {
                return true;
            }
        }

        return false;
    }

    function rewritePrivateNsPreviewLinks() {
        var scope = document.getElementById('dm-rc-route-preview') || document;
        var anchors = scope.querySelectorAll('a');
        for (var i = 0; i < anchors.length; i++) {
            if (isPrivateNsPreviewLink(anchors[i])) {
                anchors[i].setAttribute('href', safePrivateNsUrl);
                anchors[i].setAttribute('data-dm-private-ns-preview-guard', '852');
            }
        }
    }

    document.addEventListener('click', function (event) {
        var anchor = event.target && event.target.closest ? event.target.closest('a') : null;
        if (anchor && isPrivateNsPreviewLink(anchor)) {
            event.preventDefault();
            window.location.href = safePrivateNsUrl;
        }
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', rewritePrivateNsPreviewLinks);
    } else {
        rewritePrivateNsPreviewLinks();
    }

    window.setTimeout(rewritePrivateNsPreviewLinks, 250);
    window.setTimeout(rewritePrivateNsPreviewLinks, 1000);
})();
</script>
HTML;
});
