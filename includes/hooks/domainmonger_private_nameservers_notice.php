<?php
/**
 * DomainMonger Nameservers tab notice fallback.
 *
 * Patch 820:
 * Replaces failed Patches 818/819. The blank blue notice is on the standard
 * domain Nameservers tab inside clientarea.php?action=domaindetails, where
 * the template uses the domainnsexp language key.
 *
 * Scope:
 * manage/clientarea.php?action=domaindetails#tabNameservers
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_nameservers_tab_notice_is_candidate_page')) {
    function dm_nameservers_tab_notice_is_candidate_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        if ($script !== 'clientarea.php' && strpos($uri, '/manage/clientarea.php') === false) {
            return false;
        }

        return $action === 'domaindetails'
            || strpos($uri, 'action=domaindetails') !== false;
    }
}

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_nameservers_tab_notice_is_candidate_page()) {
        return '';
    }

    $message = 'You can change where your domain points to here. Please be aware changes can take up to 24 hours to propagate.';

    return <<<HTML
<script id="dm-nameservers-tab-notice-js-v820">
(function () {
    'use strict';

    var message = '{$message}';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function normalize(text) {
        return String(text || '').replace(/\\s+/g, ' ').trim();
    }

    function fillAlert(alert) {
        if (!alert) {
            return false;
        }

        if (normalize(alert.textContent) !== '') {
            return false;
        }

        alert.textContent = message;
        alert.setAttribute('data-dm-nameservers-notice-fixed', '1');
        return true;
    }

    function fillNameserversTabNotice() {
        var tab = document.getElementById('tabNameservers');
        var alerts;
        var i;

        if (!tab) {
            return false;
        }

        alerts = tab.querySelectorAll('.alert-info, .alert.alert-info, .info-box, .alert-primary');

        for (i = 0; i < alerts.length; i += 1) {
            if (fillAlert(alerts[i])) {
                return true;
            }
        }

        return false;
    }

    function fillVisibleBlankBlueNoticeConservative() {
        var hash = String(window.location.hash || '').toLowerCase();
        var alerts;
        var i;
        var alert;
        var rect;

        if (hash.indexOf('nameserver') === -1) {
            return false;
        }

        alerts = document.querySelectorAll('.alert-info, .alert.alert-info, .info-box, .alert-primary');

        for (i = 0; i < alerts.length; i += 1) {
            alert = alerts[i];

            if (normalize(alert.textContent) !== '') {
                continue;
            }

            rect = alert.getBoundingClientRect ? alert.getBoundingClientRect() : null;

            if (rect && rect.width > 0 && rect.height > 0) {
                alert.textContent = message;
                alert.setAttribute('data-dm-nameservers-notice-fixed', '1');
                return true;
            }
        }

        return false;
    }

    function applyNotice() {
        return fillNameserversTabNotice() || fillVisibleBlankBlueNoticeConservative();
    }

    ready(function () {
        applyNotice();

        document.addEventListener('click', function (event) {
            var link = event.target && event.target.closest ? event.target.closest('a[href="#tabNameservers"], a[href*="tabNameservers"]') : null;

            if (link) {
                window.setTimeout(applyNotice, 60);
                window.setTimeout(applyNotice, 180);
                window.setTimeout(applyNotice, 500);
            }
        }, true);
    });

    window.addEventListener('hashchange', function () {
        applyNotice();
        window.setTimeout(applyNotice, 120);
    });

    window.setTimeout(applyNotice, 150);
    window.setTimeout(applyNotice, 500);
    window.setTimeout(applyNotice, 1000);
    window.setTimeout(applyNotice, 1600);
}());
</script>
HTML;
});
