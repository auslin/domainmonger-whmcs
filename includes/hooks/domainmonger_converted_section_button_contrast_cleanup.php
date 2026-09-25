<?php
/**
 * DomainMonger patch 938: converted section button contrast cleanup.
 *
 * Fixes accumulated button/link contrast issues in the converted ResellerClub
 * domain-management design and unified DNS Management workspace. This is CSS-only
 * and does not alter any WHMCS/registrar forms, actions, tokens, JavaScript, or
 * backend behavior.
 * Patch 1147: keeps the converted domain overview action-strip button text white
 * after the later global normal-link palette is applied.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 10000, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? ''));
    $hasDesignFlag = isset($_GET['dmdesign']);

    $isConvertedDomainArea = false;
    if ($hasDesignFlag) {
        $isConvertedDomainArea = true;
    }
    if (in_array($scriptName, ['dnsmanagement.php', 'domainforwarding.php', 'emailmanagement.php'], true)) {
        $isConvertedDomainArea = true;
    }
    if ($scriptName === 'domainmanagement.php' && in_array($action, ['childns', 'dnssec'], true)) {
        $isConvertedDomainArea = true;
    }
    if ($scriptName === 'clientarea.php' && in_array($action, ['domaindetails', 'domaincontacts', 'domaingetepp'], true)) {
        $isConvertedDomainArea = true;
    }

    if (!$isConvertedDomainArea) {
        return '';
    }

    return <<<'HTML'
<style id="dm-converted-button-contrast-cleanup-938">
/* Patch 938: last-load contrast cleanup for converted DomainMonger domain pages. */
body.whmcsbody .dm-rc-btn-orange,
body.whmcsbody .dm-rc-btn-orange:visited,
body.whmcsbody .dm-rc-btn-orange:hover,
body.whmcsbody .dm-rc-btn-orange:focus,
body.whmcsbody .dm-rc-btn-navy,
body.whmcsbody .dm-rc-btn-navy:visited,
body.whmcsbody .dm-rc-btn-navy:hover,
body.whmcsbody .dm-rc-btn-navy:focus,
body.whmcsbody .dm-rc-mini-btn,
body.whmcsbody .dm-rc-mini-btn:hover,
body.whmcsbody .dm-rc-mini-btn:focus,
body.whmcsbody .dm-dns-unified .dm-dns-active-action.dm-primary,
body.whmcsbody .dm-dns-unified .dm-dns-active-action.dm-primary:visited,
body.whmcsbody .dm-dns-unified .dm-dns-active-action.dm-primary:hover,
body.whmcsbody .dm-dns-unified .dm-dns-active-action.dm-primary:focus,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:visited,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:hover,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:focus,
body.whmcsbody .dm-dns-unified .dm-dns-title-actions a,
body.whmcsbody .dm-dns-unified .dm-dns-title-actions a:visited,
body.whmcsbody .dm-dns-unified .dm-dns-title-actions a:hover,
body.whmcsbody .dm-dns-unified .dm-dns-title-actions a:focus {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-shadow: none !important;
}
body.whmcsbody .dm-rc-btn-orange *,
body.whmcsbody .dm-rc-btn-navy *,
body.whmcsbody .dm-rc-mini-btn *,
body.whmcsbody .dm-dns-unified .dm-dns-active-action.dm-primary *,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary *,
body.whmcsbody .dm-dns-unified .dm-dns-title-actions a * {
    color: inherit !important;
    -webkit-text-fill-color: inherit !important;
    text-shadow: none !important;
}
body.whmcsbody .dm-rc-btn-orange,
body.whmcsbody .dm-dns-unified .dm-dns-active-action.dm-primary,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary {
    background-color: #f58220 !important;
    border-color: #f58220 !important;
}
body.whmcsbody .dm-rc-btn-orange:hover,
body.whmcsbody .dm-rc-btn-orange:focus,
body.whmcsbody .dm-dns-unified .dm-dns-active-action.dm-primary:hover,
body.whmcsbody .dm-dns-unified .dm-dns-active-action.dm-primary:focus,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:hover,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:focus {
    background-color: #d8741f !important;
    border-color: #d8741f !important;
}
body.whmcsbody .dm-rc-btn-navy,
body.whmcsbody .dm-rc-mini-btn-navy,
body.whmcsbody .dm-dns-unified .dm-dns-title-actions a {
    background-color: #163a5f !important;
    border-color: #163a5f !important;
}
body.whmcsbody .dm-rc-btn-navy:hover,
body.whmcsbody .dm-rc-btn-navy:focus,
body.whmcsbody .dm-rc-mini-btn-navy:hover,
body.whmcsbody .dm-rc-mini-btn-navy:focus,
body.whmcsbody .dm-dns-unified .dm-dns-title-actions a:hover,
body.whmcsbody .dm-dns-unified .dm-dns-title-actions a:focus {
    background-color: #214e7a !important;
    border-color: #214e7a !important;
}
body.whmcsbody .dm-rc-mini-btn-danger,
body.whmcsbody .dm-rc-mini-btn-danger:hover,
body.whmcsbody .dm-rc-mini-btn-danger:focus {
    background-color: #b94a48 !important;
    border-color: #b94a48 !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}
/* Patch 1147: these overview controls are links styled as buttons.
 * Two page IDs keep this rule above the global normal-link palette without
 * changing ordinary content links elsewhere. */
body.whmcsbody.dm-domaindetails-live-1005 #main-body #tabOverview .dm-live-action-strip a,
body.whmcsbody.dm-domaindetails-live-1005 #main-body #tabOverview .dm-live-action-strip a:link,
body.whmcsbody.dm-domaindetails-live-1005 #main-body #tabOverview .dm-live-action-strip a:visited,
body.whmcsbody.dm-domaindetails-live-1005 #main-body #tabOverview .dm-live-action-strip a:hover,
body.whmcsbody.dm-domaindetails-live-1005 #main-body #tabOverview .dm-live-action-strip a:focus {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-shadow: none !important;
}
/* Keep text-style links readable when they sit beside filled buttons. */
body.whmcsbody .dm-rc-link-btn,
body.whmcsbody .dm-rc-link-btn:visited,
body.whmcsbody .dm-dns-unified a:not(.dm-primary):not(.dm-active):not(.btn):not(.dm-domain-section-link):not(.dm-dns-tool-link):not(.dm-dns-task-link):not(.dm-dns-record-shortcut):not(.dm-dns-record-family-chip):not(.dm-dns-flow-tab):not(.dm-dns-module-toolbar-link):not(.dm-dns-active-action) {
    -webkit-text-fill-color: currentColor;
}
/* Module-output buttons can contain spans/icons; force readable text on filled WHMCS buttons. */
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-primary,
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-success,
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-default,
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-secondary,
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-danger,
body.whmcsbody .dm-dns-unified .dm-dns-module-area button.btn,
body.whmcsbody .dm-dns-unified .dm-dns-module-area input[type="submit"].btn,
body.whmcsbody .dm-dns-unified .dm-dns-module-area input[type="button"].btn {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-shadow: none !important;
}
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-primary *,
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-success *,
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-default *,
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-secondary *,
body.whmcsbody .dm-dns-unified .dm-dns-module-area .btn-danger *,
body.whmcsbody .dm-dns-unified .dm-dns-module-area button.btn *,
body.whmcsbody .dm-dns-unified .dm-dns-module-area input[type="submit"].btn *,
body.whmcsbody .dm-dns-unified .dm-dns-module-area input[type="button"].btn * {
    color: inherit !important;
    -webkit-text-fill-color: inherit !important;
    text-shadow: none !important;
}
</style>
HTML;
});
