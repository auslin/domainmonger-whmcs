<?php
/**
 * DomainMonger global WHMCS submenu width correction.
 *
 * Moves the logged-in WHMCS submenu in from the page edges across the client area.
 * This takes the menu-width correction tested on the Domains page and applies it
 * globally without editing header.tpl, submenu-whmcs.tpl, navbar.tpl, or language files.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 25, function ($vars) {
    return <<<'HTML'
<style id="dm-global-whmcs-menu-width-370">
/* DM Patch 370: global WHMCS white-menu width correction.
   Applies the Patch 368 menu-width correction across WHMCS pages. */
@media (min-width: 992px) {
    body.whmcsbody.whmcs-loggedin .whmcssubmenu .contentcontainer,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu #header.header,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu #header.header > .navbar > .container-fluid,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu #header.header > .main-navbar-wrapper > .container-fluid,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu .navbar .container-fluid,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu .main-navbar-wrapper .container-fluid {
        max-width: 980px !important;
        width: 980px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.whmcsbody.whmcs-loggedin .whmcssubmenu .navbar,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu .main-navbar-wrapper {
        width: 100% !important;
    }
}

@media (min-width: 992px) and (max-width: 1099px) {
    body.whmcsbody.whmcs-loggedin .whmcssubmenu .contentcontainer,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu #header.header,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu #header.header > .navbar > .container-fluid,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu #header.header > .main-navbar-wrapper > .container-fluid,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu .navbar .container-fluid,
    body.whmcsbody.whmcs-loggedin .whmcssubmenu .main-navbar-wrapper .container-fluid {
        width: calc(100% - 64px) !important;
        max-width: calc(100% - 64px) !important;
    }
}
</style>
HTML;
});
