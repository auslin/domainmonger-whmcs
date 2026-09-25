<?php
/**
 * DomainMonger Patch 1276
 *
 * Makes the converted ResellerClub Manage Domain header/menu join match the
 * confirmed ClouDNS treatment: no gray outer frame around the menu, only
 * internal separators and a subtle bottom edge.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 10020, static function ($vars) {
    return <<<'HTML'
<style id="dm-rc-menu-border-alignment-1276-css">
#dm-rc-unified-nav-1152 .dm-rc-domain-header,
#dm-rc-unified-nav-1152 .dm-rc-main-menu {
    width: 100% !important;
    box-sizing: border-box !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

#dm-rc-unified-nav-1152 .dm-rc-main-menu {
    border-top: 0 !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-bottom: 1px solid var(--dm-rc-border, #dbe4ed) !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

/* Keep separators between choices, but never draw an outside right edge. */
#dm-rc-unified-nav-1152 .dm-rc-main-menu > .dm-rc-main-link,
#dm-rc-unified-nav-1152 .dm-rc-main-menu > .dm-rc-dropdown > .dm-rc-dropdown-button {
    border-left: 0 !important;
    border-top: 0 !important;
    border-bottom: 0 !important;
    border-right: 1px solid var(--dm-rc-border, #dbe4ed) !important;
    border-radius: 0 !important;
}

#dm-rc-unified-nav-1152 .dm-rc-main-menu > .dm-rc-main-link:last-child,
#dm-rc-unified-nav-1152 .dm-rc-main-menu > .dm-rc-dropdown:last-child > .dm-rc-dropdown-button {
    border-right: 0 !important;
}
</style>
HTML;
});
