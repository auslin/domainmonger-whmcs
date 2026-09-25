<?php
/**
 * DomainMonger patch 939: DNS toolbar primary button contrast fix.
 *
 * Narrow cleanup for the remaining orange "Back to DNS Workspace" style button
 * whose nested text can inherit dark text from earlier converted-page styles.
 * CSS only; no WHMCS/registrar forms, actions, tokens, JS, or backend behavior changed.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 10020, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? ''));

    $isDnsArea = in_array($scriptName, ['dnsmanagement.php', 'domainforwarding.php', 'emailmanagement.php'], true)
        || ($scriptName === 'domainmanagement.php' && $action === 'dnssec');

    if (!$isDnsArea) {
        return '';
    }

    return <<<'HTML'
<style id="dm-dns-toolbar-primary-button-contrast-fix-939">
/* Patch 939: final narrow fix for the remaining orange DNS toolbar button. */
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:link,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:visited,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:hover,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:focus,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:link,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:visited,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:hover,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:focus {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-shadow: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:hover,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:focus,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:hover,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:focus {
    background: #d8741f !important;
    border-color: #d8741f !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary strong,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary span,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary i,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary svg,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary strong,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary span,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary i,
body.whmcsbody .dm-dns-unified .dm-dns-module-toolbar-link.dm-primary svg {
    color: #ffffff !important;
    fill: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-shadow: none !important;
}
</style>
HTML;
});
