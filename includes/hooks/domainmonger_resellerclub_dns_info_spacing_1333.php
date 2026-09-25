<?php
/**
 * DomainMonger — ResellerClub DNS informational notice spacing
 * Patch 1333
 *
 * Adds a small separation between the Development License notice and the
 * informational DNS guidance notice on the converted native DNS page.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1333, static function (array $vars): string {
    $action = strtolower(trim((string) ($_GET['action'] ?? '')));

    if ($action !== 'domaindns') {
        return '';
    }

    return <<<'HTML'
<style id="dm-resellerclub-dns-info-spacing-1333">
/* Keep the two full-width notices visually separate without changing either alert. */
body.dm-resellerclub-dns-live-feed-1113
.dm-dns-live-feed-body-1113 > .dm-dns-live-feed-message-1113.dm-info,
body.dm-resellerclub-dns-live-feed-1113
.dm-dns-live-feed-body-1113 > .dm-dns-live-feed-message-1113:not(.dm-success):not(.dm-error):not(.dm-warning) {
    margin-top: 10px !important;
}
</style>
HTML;
});
