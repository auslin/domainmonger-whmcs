<?php
/**
 * DomainMonger My Domains checkbox color match.
 *
 * Patch 660: page-specific My Domains checkbox treatment.
 * Uses a native-control filter instead of replacing/overlaying checkboxes, so the
 * WHMCS/DataTables/select-all behavior remains untouched while the checked state
 * visually matches the orange DNS Records checkbox style.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1000, static function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';

    if ($templateFile !== 'clientareadomains') {
        return '';
    }

    return <<<'HTML'
<style id="dm-my-domains-checkbox-orange-match-660">
/* Patch 660: My Domains only. Match DNS Records/Register native orange checkbox style. */
body.whmcsbody.whmcs-templatefile-clientareadomains table#tableDomainsList input#dmSelectAllDomains,
body.whmcsbody.whmcs-templatefile-clientareadomains table#tableDomainsList input.domids,
body.whmcsbody.whmcs-loggedin table#tableDomainsList input#dmSelectAllDomains,
body.whmcsbody.whmcs-loggedin table#tableDomainsList input.domids {
    -webkit-appearance: auto !important;
    -moz-appearance: auto !important;
    appearance: auto !important;
    width: auto !important;
    height: auto !important;
    min-width: 0 !important;
    min-height: 0 !important;
    max-width: none !important;
    max-height: none !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    margin-left: auto !important;
    margin-right: auto !important;
    padding: 0 !important;
    border: initial !important;
    border-radius: initial !important;
    background: initial !important;
    box-shadow: none !important;
    opacity: 1 !important;
    cursor: pointer !important;
    vertical-align: middle !important;
    filter: hue-rotate(172deg) saturate(1.25) brightness(1.03) !important;
}

body.whmcsbody.whmcs-templatefile-clientareadomains table#tableDomainsList input#dmSelectAllDomains:focus,
body.whmcsbody.whmcs-templatefile-clientareadomains table#tableDomainsList input.domids:focus,
body.whmcsbody.whmcs-loggedin table#tableDomainsList input#dmSelectAllDomains:focus,
body.whmcsbody.whmcs-loggedin table#tableDomainsList input.domids:focus {
    outline: 2px solid rgba(245, 130, 32, 0.28) !important;
    outline-offset: 2px !important;
}
</style>
HTML;
});
