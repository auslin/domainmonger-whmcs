<?php
/**
 * DomainMonger WHMCS Patch 662
 * Dashboard stat tile icon color alignment.
 *
 * Page-specific: client area dashboard only.
 * Changes the dashboard stat tile icons from default gray to the site navy
 * while preserving the existing orange underline/accent styling.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    $filename = isset($vars['filename']) ? (string) $vars['filename'] : '';
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';

    if ($filename !== 'clientarea' || $templateFile !== 'clientareahome') {
        return '';
    }

    return <<<'HTML'
<style id="dm-dashboard-tile-icons-navy-662">
/* DomainMonger Patch 662: Dashboard stat tile icons should match site navy. */
body.whmcsbody .tiles.mb-4 .tile > i,
body.whmcsbody .tiles.mb-4 .tile > i.fas,
body.whmcsbody .tiles.mb-4 .tile > i.far,
body.whmcsbody .tiles.mb-4 .tile > i.fa {
    color: #163a5f !important;
}

body.whmcsbody .tiles.mb-4 .tile:hover > i,
body.whmcsbody .tiles.mb-4 .tile:focus > i {
    color: #163a5f !important;
}
</style>
HTML;
});
