<?php
/**
 * Patch 1701 - WHMCS 9 admin invoice refund same-host repair.
 *
 * The WHMCS 9 refund form can render an absolute action on
 * https://www.domainmonger.com while the admin session/page is on the
 * canonical https://domainmonger.com host. DomainMonger's canonical redirect
 * is a 302, so a POST sent to the www host can be redirected as a GET and the
 * WHMCS POST-only refund route answers "HTTP Method Not Allowed".
 *
 * Scope is intentionally narrow:
 * - admin invoice route only
 * - refund form only
 * - www.domainmonger.com -> current domainmonger.com origin only
 * - no refund fields, values, CSRF tokens, transaction logic, or handlers are
 *   changed
 */
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('AdminAreaFooterOutput', 1701, function (array $vars): string {
    return <<<'HTML'
<script id="domainmonger-admin-invoice-refund-same-host-1701">
(function () {
    'use strict';

    function isInvoicePage() {
        var value = String(window.location.pathname || '') + String(window.location.search || '');
        try { value += ' ' + decodeURIComponent(String(window.location.search || '')); } catch (ignore) {}
        return /\/admin\/billing\/invoice\/\d+/i.test(value);
    }

    if (!isInvoicePage() || window.location.hostname !== 'domainmonger.com') {
        return;
    }

    function repairRefundForm(form) {
        if (!form || String(form.tagName || '').toLowerCase() !== 'form') {
            return;
        }

        var rawAction = String(form.getAttribute('action') || '');
        if (!rawAction || rawAction.indexOf('/admin/billing/invoice/') === -1 || rawAction.indexOf('/refund') === -1) {
            return;
        }

        var target;
        try {
            target = new URL(rawAction, window.location.href);
        } catch (ignore) {
            return;
        }

        if (target.hostname !== 'www.domainmonger.com') {
            return;
        }

        target.protocol = window.location.protocol;
        target.host = window.location.host;
        form.setAttribute('action', target.pathname + target.search + target.hash);
    }

    function repairAllRefundForms() {
        var forms = document.querySelectorAll('form[action*="/admin/billing/invoice/"][action*="/refund"]');
        for (var i = 0; i < forms.length; i++) {
            repairRefundForm(forms[i]);
        }
    }

    document.addEventListener('submit', function (event) {
        repairRefundForm(event.target);
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', repairAllRefundForms);
    } else {
        repairAllRefundForms();
    }

    var observer = new MutationObserver(function () {
        repairAllRefundForms();
    });
    observer.observe(document.documentElement, {childList: true, subtree: true});

    window.setTimeout(repairAllRefundForms, 500);
    window.setTimeout(repairAllRefundForms, 1500);
}());
</script>
HTML;
});
