<?php
/**
 * DomainMonger - ResellerClub discontinued Free Email Forwarding menu cleanup
 * Patch 736
 *
 * Scope: /manage/emailmanagement.php only.
 * Purpose: When ResellerClub reports that the free email service has been
 * discontinued for a domain, hide the legacy Email Forwarding menu actions that
 * lead to unavailable/broken pages. This does not rewrite requests, does not
 * touch .htaccess, and does not affect domains that still have the legacy email
 * forwarding service because it only activates after the discontinued message is
 * present in the rendered page.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_rc_email_discontinued_guard_is_emailmanagement')) {
    function dm_rc_email_discontinued_guard_is_emailmanagement(): bool
    {
        $script = strtolower((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));

        return substr($script, -20) === '/emailmanagement.php'
            || basename($script) === 'emailmanagement.php'
            || strpos($uri, '/manage/emailmanagement.php') !== false;
    }
}

add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_rc_email_discontinued_guard_is_emailmanagement()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-rc-email-discontinued-menu-cleanup-v736">
.dm-rc-email-discontinued-hidden-v736 {
    display: none !important;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_rc_email_discontinued_guard_is_emailmanagement()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-rc-email-discontinued-menu-cleanup-js-v736">
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function normalizedText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function pageText() {
        return normalizedText(document.body ? (document.body.innerText || document.body.textContent || '') : '');
    }

    function pageIsDiscontinuedFreeEmail() {
        var text = pageText();
        return text.indexOf('free email service has been discontinued') !== -1 ||
            text.indexOf('the free email service has been discontinued') !== -1 ||
            text.indexOf('free email forwarding has been discontinued') !== -1 ||
            text.indexOf('email forwarding has been discontinued') !== -1;
    }

    function isLegacyEmailForwardingUrl(value) {
        value = normalizedText(value);
        if (value.indexOf('emailmanagement.php') === -1) {
            return false;
        }

        return value.indexOf('action=manageemails') !== -1 ||
            value.indexOf('action=createemail') !== -1 ||
            value.indexOf('action=addemail') !== -1 ||
            value.indexOf('action=addmail') !== -1 ||
            value.indexOf('action=createmail') !== -1 ||
            value.indexOf('action=mailforward') !== -1 ||
            value.indexOf('action=manageemailforward') !== -1;
    }

    function elementText(el) {
        if (!el) {
            return '';
        }
        return normalizedText(el.value || el.getAttribute('title') || el.getAttribute('aria-label') || el.textContent || '');
    }

    function labelIsLegacyForwarding(label) {
        return label === 'manage email forwards' ||
            label === 'email forwarding discontinued' ||
            label === 'create email forward' ||
            label === 'create email forwards' ||
            label.indexOf('manage email forwards') !== -1 ||
            label.indexOf('email forwarding discontinued') !== -1 ||
            label.indexOf('create email forward') !== -1;
    }

    function formTargetsLegacyForwarding(form) {
        var fields;
        var i;

        if (!form) {
            return false;
        }

        if (isLegacyEmailForwardingUrl(form.getAttribute('action') || form.action || '')) {
            return true;
        }

        fields = form.querySelectorAll('[name="action"]');
        for (i = 0; i < fields.length; i += 1) {
            if (labelIsLegacyForwarding(fields[i].value) || normalizedText(fields[i].value) === 'manageemails') {
                return true;
            }
        }

        return false;
    }

    function controlTargetsLegacyForwarding(el) {
        var label;

        if (!el) {
            return false;
        }

        if (isLegacyEmailForwardingUrl(el.getAttribute('href') || '')) {
            return true;
        }

        if (isLegacyEmailForwardingUrl(el.getAttribute('onclick') || '')) {
            return true;
        }

        label = elementText(el);
        if (labelIsLegacyForwarding(label)) {
            return true;
        }

        return formTargetsLegacyForwarding(el.form || (el.closest ? el.closest('form') : null));
    }

    function removableMenuItem(el) {
        var selectors = [
            'li',
            '.list-group-item',
            '.dropdown-item',
            '.nav-item',
            '.menu-item',
            '.sidebar-menu-item',
            '.panel-sidebar a',
            '.card-sidebar a'
        ];
        var i;
        var found;

        if (!el || !el.closest) {
            return el;
        }

        for (i = 0; i < selectors.length; i += 1) {
            found = el.closest(selectors[i]);
            if (found) {
                return found;
            }
        }

        return el;
    }

    function hideLegacyForwardingControls() {
        var controls;
        var i;
        var el;
        var item;

        if (!pageIsDiscontinuedFreeEmail()) {
            return;
        }

        controls = document.querySelectorAll('a, button, input[type="button"], input[type="submit"], span');
        for (i = 0; i < controls.length; i += 1) {
            el = controls[i];
            if (!controlTargetsLegacyForwarding(el)) {
                continue;
            }

            item = removableMenuItem(el);
            if (item) {
                item.classList.add('dm-rc-email-discontinued-hidden-v736');
                item.setAttribute('aria-hidden', 'true');
                item.setAttribute('data-dm-rc-email-v736', 'hidden');
            }
        }
    }

    function installClickBlocker() {
        document.addEventListener('click', function (event) {
            var target;
            var control;

            if (!pageIsDiscontinuedFreeEmail()) {
                return true;
            }

            target = event.target;
            control = target && target.closest ? target.closest('a, button, input') : target;
            if (controlTargetsLegacyForwarding(control)) {
                event.preventDefault();
                event.stopPropagation();
                hideLegacyForwardingControls();
                return false;
            }

            return true;
        }, true);
    }

    ready(function () {
        installClickBlocker();
        hideLegacyForwardingControls();
        window.setTimeout(hideLegacyForwardingControls, 250);
        window.setTimeout(hideLegacyForwardingControls, 1000);
    });
}());
</script>
HTML;
});
