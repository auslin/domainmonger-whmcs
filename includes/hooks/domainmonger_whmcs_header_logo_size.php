<?php
/**
 * DomainMonger WHMCS header logo size override.
 *
 * Keeps the WHMCS integrated header logo size in sync with the WordPress header
 * logo sizing added via the WordPress mu-plugin.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    return <<<'HTML'
<style id="domainmonger-whmcs-header-logo-size-669">
/* DomainMonger Patch 669: match WHMCS integrated header logo size to WordPress. */
body.whmcsbody .headermain .headermain-logo img,
body.whmcsbody .headermain-logo > a > img {
    width: 295px !important;
    max-width: 295px !important;
    height: auto !important;
}

@media (max-width: 1260px) {
    body.whmcsbody .headermain .headermain-logo img,
    body.whmcsbody .headermain-logo > a > img {
        width: 260px !important;
        max-width: 260px !important;
    }
}

@media (max-width: 640px) {
    body.whmcsbody .headermain .headermain-logo img,
    body.whmcsbody .headermain-logo > a > img {
        width: 225px !important;
        max-width: 225px !important;
    }
}
</style>
HTML;
});
