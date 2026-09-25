<?php
/**
 * DomainMonger Patch 1299
 *
 * Prevent the small horizontal launch shift on WHMCS bulk-domain pages by
 * reserving the browser's vertical scrollbar gutter before first paint.
 *
 * The visible 2–3 mm movement occurred when the short Auto Renewal and
 * Registrar Lock pages crossed the viewport-height threshold while their
 * converted styling was applied. The scrollbar appeared/disappeared, changing
 * the available centered-page width. Reserving the gutter keeps the width
 * constant throughout rendering.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, static function ($vars) {
    $vars = is_array($vars) ? $vars : [];
    $templateFile = strtolower((string) ($vars['templatefile'] ?? ''));
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');

    $isBulkDomain = ($templateFile === 'bulkdomainmanagement')
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=bulkdomain(?:&|$)/i', $requestUri));

    if (!$isBulkDomain) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-bulk-scrollbar-stability-1299">
/*
 * Keep the viewport width constant while the bulk page is assembled. Using
 * both declarations covers browsers that support scrollbar-gutter and older
 * desktop browsers that need an always-reserved vertical scrollbar track.
 */
html {
    scrollbar-gutter: stable !important;
    overflow-y: scroll !important;
}
</style>
HTML;
});
