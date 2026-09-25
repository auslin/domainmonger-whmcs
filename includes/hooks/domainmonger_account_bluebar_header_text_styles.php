<?php
/**
 * DomainMonger Patch 581
 * Account-area blue bar header text visibility fix.
 *
 * Scope:
 * - Your Profile
 * - Change Password
 * - Security Settings
 * - Account Security / Single Sign-On
 *
 * Purpose:
 * - Some WHMCS/global styles can override the custom blue bar heading text color,
 *   making titles visible only when highlighted.
 * - Keep this hook narrow by targeting DomainMonger account-page header classes only.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function domainmonger_account_bluebar_header_text_css_581()
{
    return <<<'CSS'
<style id="domainmonger-account-bluebar-header-text-581">
/* DM Patch 581: account/profile/security blue bar header text readability. */
body.whmcsbody .dm-user-profile-page .dm-profile-section-header,
body.whmcsbody .dm-change-password-page .dm-password-section-header,
body.whmcsbody .dm-security-settings-page .dm-security-section-header,
body.whmcsbody .dm-sso-card .dm-sso-card-header,
body.whmcsbody .dm-profile-section-header,
body.whmcsbody .dm-password-section-header,
body.whmcsbody .dm-security-section-header,
body.whmcsbody .dm-sso-card-header {
    background: #163a5f !important;
    background-color: #163a5f !important;
    background-image: none !important;
    border-color: #163a5f !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    opacity: 1 !important;
    visibility: visible !important;
    text-shadow: none !important;
}

body.whmcsbody .dm-user-profile-page .dm-profile-section-header *,
body.whmcsbody .dm-change-password-page .dm-password-section-header *,
body.whmcsbody .dm-security-settings-page .dm-security-section-header *,
body.whmcsbody .dm-sso-card .dm-sso-card-header *,
body.whmcsbody .dm-profile-section-header *,
body.whmcsbody .dm-password-section-header *,
body.whmcsbody .dm-security-section-header *,
body.whmcsbody .dm-sso-card-header *,
body.whmcsbody .dm-profile-section-header .card-title,
body.whmcsbody .dm-password-section-header .card-title,
body.whmcsbody .dm-security-section-header .card-title,
body.whmcsbody .dm-sso-card-header .card-title,
body.whmcsbody .dm-profile-title,
body.whmcsbody .dm-password-title,
body.whmcsbody .dm-security-title,
body.whmcsbody .dm-sso-title {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    opacity: 1 !important;
    visibility: visible !important;
    text-shadow: none !important;
}

body.whmcsbody .dm-profile-section-header a,
body.whmcsbody .dm-password-section-header a,
body.whmcsbody .dm-security-section-header a,
body.whmcsbody .dm-sso-card-header a,
body.whmcsbody .dm-profile-section-header a:visited,
body.whmcsbody .dm-password-section-header a:visited,
body.whmcsbody .dm-security-section-header a:visited,
body.whmcsbody .dm-sso-card-header a:visited,
body.whmcsbody .dm-profile-section-header a:hover,
body.whmcsbody .dm-password-section-header a:hover,
body.whmcsbody .dm-security-section-header a:hover,
body.whmcsbody .dm-sso-card-header a:hover,
body.whmcsbody .dm-profile-section-header a:focus,
body.whmcsbody .dm-password-section-header a:focus,
body.whmcsbody .dm-security-section-header a:focus,
body.whmcsbody .dm-sso-card-header a:focus {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-decoration: none !important;
}
</style>
CSS;
}

add_hook('ClientAreaHeadOutput', 1, function (array $vars) {
    return domainmonger_account_bluebar_header_text_css_581();
});

add_hook('ClientAreaFooterOutput', 1, function (array $vars) {
    return domainmonger_account_bluebar_header_text_css_581() . <<<'HTML'
<script id="domainmonger-account-bluebar-header-text-581-js">
(function () {
    'use strict';

    function pageLooksRelevant() {
        var bodyClass = document.body ? document.body.className : '';
        var pathSearch = (window.location.pathname + window.location.search).toLowerCase();

        return bodyClass.indexOf('templatefile-user-profile') !== -1
            || bodyClass.indexOf('whmcs-templatefile-user-profile') !== -1
            || bodyClass.indexOf('templatefile-user-password') !== -1
            || bodyClass.indexOf('whmcs-templatefile-user-password') !== -1
            || bodyClass.indexOf('templatefile-user-security') !== -1
            || bodyClass.indexOf('whmcs-templatefile-user-security') !== -1
            || bodyClass.indexOf('clientareasecurity') !== -1
            || pathSearch.indexOf('/account/profile') !== -1
            || pathSearch.indexOf('/account/password') !== -1
            || pathSearch.indexOf('/account/security') !== -1
            || pathSearch.indexOf('action=security') !== -1
            || !!document.querySelector('.dm-user-profile-page, .dm-change-password-page, .dm-security-settings-page, .dm-sso-card');
    }

    function forceBlueBarTextVisible() {
        if (!pageLooksRelevant() || !document.querySelectorAll) {
            return;
        }

        var headers = document.querySelectorAll([
            '.dm-profile-section-header',
            '.dm-password-section-header',
            '.dm-security-section-header',
            '.dm-sso-card-header'
        ].join(','));

        Array.prototype.forEach.call(headers, function (header) {
            header.classList.add('dm-account-bluebar-header-fixed-581');
            header.style.setProperty('background', '#163a5f', 'important');
            header.style.setProperty('background-color', '#163a5f', 'important');
            header.style.setProperty('background-image', 'none', 'important');
            header.style.setProperty('border-color', '#163a5f', 'important');
            header.style.setProperty('color', '#ffffff', 'important');
            header.style.setProperty('-webkit-text-fill-color', '#ffffff', 'important');
            header.style.setProperty('opacity', '1', 'important');
            header.style.setProperty('visibility', 'visible', 'important');
            header.style.setProperty('text-shadow', 'none', 'important');

            Array.prototype.forEach.call(header.querySelectorAll('*'), function (child) {
                child.style.setProperty('color', '#ffffff', 'important');
                child.style.setProperty('-webkit-text-fill-color', '#ffffff', 'important');
                child.style.setProperty('opacity', '1', 'important');
                child.style.setProperty('visibility', 'visible', 'important');
                child.style.setProperty('text-shadow', 'none', 'important');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceBlueBarTextVisible);
    } else {
        forceBlueBarTextVisible();
    }

    window.setTimeout(forceBlueBarTextVisible, 150);
    window.setTimeout(forceBlueBarTextVisible, 600);
})();
</script>
HTML;
});
