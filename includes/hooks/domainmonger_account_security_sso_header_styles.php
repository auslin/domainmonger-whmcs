<?php
/**
 * DomainMonger Patch 579
 * Account Security / Single Sign-On header text visibility fix v2.
 *
 * Replaces Patch 578 in the same hook file.
 *
 * Why v2:
 * - Patch 578 was too strict about filename/action and did not output on the live route.
 * - WHMCS can render Account Security as clientarea.php?action=security or a friendly
 *   account/security route, and the active template exposes body/template classes.
 *
 * Scope:
 * - CSS is safe to output globally because selectors are scoped to Account Security
 *   body/template classes and the SSO card classes.
 * - JS fallback only runs when the page looks like Account Security/SSO.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, function (array $vars) {
    return <<<'HTML'
<style id="domainmonger-account-security-sso-header-579">
/* DM Patch 579: Account Security SSO header readability, route-safe. */
body.whmcsbody.whmcs-templatefile-clientareasecurity .dm-sso-card-header,
body.whmcsbody.templatefile-clientareasecurity .dm-sso-card-header,
body.whmcsbody .dm-sso-card > .dm-sso-card-header,
body.whmcsbody .dm-sso-card .dm-sso-card-header {
    display: block !important;
    background: #163a5f !important;
    background-color: #163a5f !important;
    background-image: none !important;
    border-color: #163a5f !important;
    border-bottom: 1px solid #163a5f !important;
    color: #ffffff !important;
    opacity: 1 !important;
    visibility: visible !important;
    text-shadow: none !important;
}

body.whmcsbody.whmcs-templatefile-clientareasecurity .dm-sso-card-header,
body.whmcsbody.templatefile-clientareasecurity .dm-sso-card-header,
body.whmcsbody .dm-sso-card > .dm-sso-card-header {
    padding: 12px 18px !important;
}

body.whmcsbody.whmcs-templatefile-clientareasecurity .dm-sso-card-header .dm-sso-title,
body.whmcsbody.whmcs-templatefile-clientareasecurity .dm-sso-card-header .card-title,
body.whmcsbody.whmcs-templatefile-clientareasecurity .dm-sso-card-header h1,
body.whmcsbody.whmcs-templatefile-clientareasecurity .dm-sso-card-header h2,
body.whmcsbody.whmcs-templatefile-clientareasecurity .dm-sso-card-header h3,
body.whmcsbody.whmcs-templatefile-clientareasecurity .dm-sso-card-header h4,
body.whmcsbody.templatefile-clientareasecurity .dm-sso-card-header .dm-sso-title,
body.whmcsbody.templatefile-clientareasecurity .dm-sso-card-header .card-title,
body.whmcsbody.templatefile-clientareasecurity .dm-sso-card-header h1,
body.whmcsbody.templatefile-clientareasecurity .dm-sso-card-header h2,
body.whmcsbody.templatefile-clientareasecurity .dm-sso-card-header h3,
body.whmcsbody.templatefile-clientareasecurity .dm-sso-card-header h4,
body.whmcsbody .dm-sso-card > .dm-sso-card-header .dm-sso-title,
body.whmcsbody .dm-sso-card > .dm-sso-card-header .card-title,
body.whmcsbody .dm-sso-card > .dm-sso-card-header h1,
body.whmcsbody .dm-sso-card > .dm-sso-card-header h2,
body.whmcsbody .dm-sso-card > .dm-sso-card-header h3,
body.whmcsbody .dm-sso-card > .dm-sso-card-header h4,
body.whmcsbody .dm-sso-card .dm-sso-card-header *,
body.whmcsbody .dm-sso-card-header .dm-sso-title,
body.whmcsbody .dm-sso-card-header .card-title {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-shadow: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}

body.whmcsbody .dm-sso-card-header a,
body.whmcsbody .dm-sso-card-header a:visited,
body.whmcsbody .dm-sso-card-header a:hover,
body.whmcsbody .dm-sso-card-header a:focus {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-decoration: none !important;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function (array $vars) {
    return <<<'HTML'
<script id="domainmonger-account-security-sso-header-579-js">
(function () {
    'use strict';

    function pageLooksRelevant() {
        var bodyClass = document.body ? document.body.className : '';
        var pathSearch = (window.location.pathname + window.location.search).toLowerCase();
        return bodyClass.indexOf('clientareasecurity') !== -1
            || pathSearch.indexOf('action=security') !== -1
            || pathSearch.indexOf('/account/security') !== -1
            || !!document.querySelector('.dm-sso-card, .dm-sso-card-header, #frmSingleSignOn');
    }

    function forceSsoHeaderTextVisible() {
        if (!pageLooksRelevant() || !document.querySelectorAll) {
            return;
        }

        var headers = document.querySelectorAll('.dm-sso-card-header, .dm-sso-card > .card-header, .card-header, .panel-heading');
        Array.prototype.forEach.call(headers, function (header) {
            var text = (header.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
            var isSsoHeader = header.classList.contains('dm-sso-card-header')
                || (header.closest && header.closest('.dm-sso-card'))
                || text.indexOf('single sign-on') !== -1;

            if (!isSsoHeader) {
                return;
            }

            header.classList.add('dm-account-security-sso-header-fixed');
            header.style.setProperty('background', '#163a5f', 'important');
            header.style.setProperty('background-color', '#163a5f', 'important');
            header.style.setProperty('background-image', 'none', 'important');
            header.style.setProperty('border-color', '#163a5f', 'important');
            header.style.setProperty('color', '#ffffff', 'important');
            header.style.setProperty('-webkit-text-fill-color', '#ffffff', 'important');
            header.style.setProperty('opacity', '1', 'important');
            header.style.setProperty('visibility', 'visible', 'important');

            Array.prototype.forEach.call(header.querySelectorAll('*'), function (child) {
                child.style.setProperty('color', '#ffffff', 'important');
                child.style.setProperty('-webkit-text-fill-color', '#ffffff', 'important');
                child.style.setProperty('text-shadow', 'none', 'important');
                child.style.setProperty('opacity', '1', 'important');
                child.style.setProperty('visibility', 'visible', 'important');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceSsoHeaderTextVisible);
    } else {
        forceSsoHeaderTextVisible();
    }

    window.setTimeout(forceSsoHeaderTextVisible, 150);
    window.setTimeout(forceSsoHeaderTextVisible, 600);
})();
</script>
HTML;
});
