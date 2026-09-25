<?php
/**
 * DomainMonger converted domain-management loader flash guard.
 *
 * Patch 962:
 * - Targets the visible full-page loading overlay/spinner only.
 * - Does not change color mode, cookies, localStorage, body classes, head.tpl, or routing.
 * - Keeps Patch 953 converted link routing untouched.
 * - Leaves forms, POST actions, hidden fields, tokens, registrar/module behavior untouched.
 * - Does not touch the language override file or the integration folder.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\View\Menu\Item as MenuItem;

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $filename = isset($vars['filename']) ? (string) $vars['filename'] : '';
    $templatefile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';

    $isConvertedDomainArea = in_array($filename, [
        'dnsmanagement',
        'domainmanagement',
    ], true);

    if (!$isConvertedDomainArea && $filename === 'clientarea') {
        $action = isset($_GET['action']) ? (string) $_GET['action'] : '';
        $isConvertedDomainArea = in_array($action, [
            'domaindetails',
            'domaingetepp',
            'domaincontacts',
        ], true);
    }

    if (!$isConvertedDomainArea && strpos($templatefile, 'domain') !== false) {
        $isConvertedDomainArea = true;
    }

    if (!$isConvertedDomainArea) {
        return '';
    }

    return <<<'HTML'
<style id="dm-converted-loader-flash-guard-962">
/*
 * Patch 962: hide only the WHMCS full-page overlay while it is explicitly
 * marked hidden. This prevents the large spinner/dark overlay from painting
 * during first load without disabling the overlay when WHMCS intentionally
 * shows it by removing .w-hidden.
 */
#fullpage-overlay.w-hidden,
body.whmcsbody #fullpage-overlay.w-hidden {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
    background: transparent !important;
}

/* The Bootstrap system modal also contains a loader in the footer. Keep it
 * non-painting unless the modal is actively shown.
 */
body.whmcsbody .modal.system-modal:not(.show) .loader,
body.whmcsbody .modal.system-modal:not(.in) .loader {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
}
</style>
HTML;
});
