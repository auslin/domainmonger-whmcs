<?php
/**
 * DomainMonger patch 823.
 * Fixes the empty informational notice on the Private Nameservers page.
 *
 * Scope:
 * - domainmanagement.php?action=childns
 * - clientarea.php?action=domainregisterns
 *
 * This is intentionally route-specific and does not touch language overrides
 * or the Stellar integration folder.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!defined('DM_PRIVATE_NAMESERVERS_NOTICE_TEXT')) {
    define(
        'DM_PRIVATE_NAMESERVERS_NOTICE_TEXT',
        'Create and manage custom private nameservers for this domain, such as ns1.example.com or ns2.example.com.'
    );
}

if (!function_exists('dm_private_nameservers_notice_is_target')) {
    function dm_private_nameservers_notice_is_target()
    {
        $script = strtolower(basename(parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: ''));
        $query = strtolower($_SERVER['QUERY_STRING'] ?? '');
        $uri = strtolower($_SERVER['REQUEST_URI'] ?? '');

        if ($script === 'domainmanagement.php' && (strpos($query, 'action=childns') !== false || strpos($uri, 'action=childns') !== false)) {
            return true;
        }

        if ($script === 'clientarea.php' && (strpos($query, 'action=domainregisterns') !== false || strpos($uri, 'action=domainregisterns') !== false)) {
            return true;
        }

        return false;
    }
}

add_hook('ClientAreaPage', 1, function ($vars) {
    if (!dm_private_nameservers_notice_is_target()) {
        return [];
    }

    global $_LANG;
    if (!isset($_LANG) || !is_array($_LANG)) {
        $_LANG = [];
    }

    // Some legacy/private-nameserver templates read from $LANG directly.
    $_LANG['domainregisternsexplanation'] = DM_PRIVATE_NAMESERVERS_NOTICE_TEXT;

    return [];
});

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    if (!dm_private_nameservers_notice_is_target()) {
        return '';
    }

    $notice = json_encode(DM_PRIVATE_NAMESERVERS_NOTICE_TEXT, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return <<<HTML
<style>
body.whmcsbody #main-body .dm-private-ns-notice-fixed {
    background-color: #eef5fb !important;
    border: 1px solid #b9cad9 !important;
    color: #163a5f !important;
    border-radius: 6px !important;
    padding: 12px 16px !important;
    line-height: 1.45 !important;
    font-weight: 400 !important;
}
</style>
<script>
(function () {
    'use strict';

    var noticeText = {$notice};

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    function cleanText(value) {
        return String(value || '').replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim();
    }

    function isMissingNoticeText(text) {
        var cleaned = cleanText(text);
        return cleaned === '' ||
            cleaned === 'domainregisternsexplanation' ||
            cleaned === '{lang key=\'domainregisternsexplanation\'}' ||
            cleaned === '{lang key="domainregisternsexplanation"}';
    }

    function fillExistingInfoAlerts() {
        var changed = false;
        var alerts = document.querySelectorAll(
            '#main-body .alert.alert-info, ' +
            '#main-body .alert-info, ' +
            '.main-content .alert.alert-info, ' +
            '.moduleoutput .alert.alert-info'
        );

        Array.prototype.forEach.call(alerts, function (alert) {
            if (alert.classList.contains('dm-private-ns-notice-fixed')) {
                return;
            }

            if (isMissingNoticeText(alert.textContent)) {
                alert.textContent = noticeText;
                alert.classList.add('dm-private-ns-notice-fixed');
                changed = true;
            }
        });

        return changed;
    }

    function insertNoticeIfMissing() {
        if (document.querySelector('.dm-private-ns-notice-fixed')) {
            return;
        }

        var anchor = document.querySelector(
            '#main-body form[action*="action=childns"], ' +
            '#main-body form[action*="action=domainregisterns"], ' +
            '#main-body input[name="addregchildns"], ' +
            '#main-body input[name="sub"][value="register"]'
        );

        if (!anchor) {
            return;
        }

        var container = anchor.closest('form') || anchor;
        var notice = document.createElement('div');
        notice.className = 'alert alert-info dm-private-ns-notice-fixed';
        notice.textContent = noticeText;

        container.parentNode.insertBefore(notice, container);
    }

    function applyFix() {
        if (!fillExistingInfoAlerts()) {
            insertNoticeIfMissing();
        }
    }

    ready(function () {
        applyFix();
        window.setTimeout(applyFix, 250);
        window.setTimeout(applyFix, 900);
    });
}());
</script>
HTML;
});
