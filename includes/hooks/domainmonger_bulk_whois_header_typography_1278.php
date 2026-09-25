<?php
/**
 * DomainMonger Bulk WHOIS header typography alignment — Patch 1278.
 *
 * Matches the Bulk WHOIS Contact Info navy header to the confirmed sizing
 * used by Bulk Nameservers, Bulk Auto Renewal, and Bulk Registrar Lock.
 * Presentation only; bulk-domain form processing is unchanged.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 140, static function ($vars) {
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $isBulkDomain = ($templateFile === 'bulkdomainmanagement')
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=bulkdomain(?:&|$)/i', $requestUri));

    if (!$isBulkDomain) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-bulk-whois-header-typography-1278-css">
/* Match Bulk WHOIS header dimensions and type scale to the other bulk pages. */
body.dm-bulk-contact-converted-1256 .dm-bulk-contact-section-header-1258 {
    padding: 13px 18px !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-section-header-1258 > div {
    display: flex !important;
    flex-direction: column !important;
    gap: 2px !important;
    min-width: 0 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-section-header-1258 strong {
    margin: 0 0 2px !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.25 !important;
}

body.dm-bulk-contact-converted-1256 .dm-bulk-contact-section-header-1258 span {
    color: rgba(255, 255, 255, 0.88) !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.35 !important;
}
</style>
HTML;
});
