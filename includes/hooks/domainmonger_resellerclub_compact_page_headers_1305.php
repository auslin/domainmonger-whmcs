<?php
/**
 * DomainMonger compact converted-page headers — updated by Patch 1501.
 *
 * Keeps the confirmed duplicate page-title removal from Patch 1305, but no
 * longer creates a separate DNS "Records:" badge. The active Register DNS
 * compact-controls hook now owns the single record-count badge.
 *
 * No database changes.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1305, static function ($vars) {
    return <<<'HTML'
<style id="dm-resellerclub-compact-page-headers-1305-css">
/* Remove only the repeated standalone page-name bars used by the converted
 * ResellerClub interface. Internal card/table headers remain untouched. */
body.dm-rc-unified-menu-1152 #dm-rc-unified-nav-1152 .dm-rc-page-header-1162,
body.dm-resellerclub-dns-live-feed-1113 #dm-dns-live-feed-page-header-1173,
body.dm-rc-page-whois .dm-whois-form-head,
body.dm-rc-page-whois .dm-whois-form-head[data-dm-whois-external-header="1"] {
    display: none !important;
}

/* Patch 1501: suppress any stale record-count element left in cached markup.
 * The single approved count is .dm-dns-record-count-1499. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-record-count-1305 {
    display: none !important;
}
</style>
HTML;
});
