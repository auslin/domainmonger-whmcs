<?php
/**
 * DomainMonger WHMCS menu-to-page-header spacing — Patch 1304.
 *
 * The visible gap is created by two stacked padding layers:
 *   1. Stellar's outer <main class="mainmain ..."> wrapper.
 *   2. WHMCS's inner <section id="main-body"> wrapper.
 *
 * Patches 1301-1303 changed only one layer at a time, so the other layer
 * continued to produce nearly the same visible gap.  Normalize both layers
 * together while preserving the established bottom spacing before the footer.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1304, static function ($vars) {
    return <<<'HTML'
<style id="domainmonger-whmcs-menu-header-spacing-1304">
/* Patch 1304: both stacked wrappers must be normalized together. */
body.whmcsbody main.mainmain.wordpresscontainer.whmcscontainer,
body.whmcsbody .mainmain.wordpresscontainer.whmcscontainer {
    padding-top: 6px !important;
}

body.whmcsbody main.mainmain.wordpresscontainer.whmcscontainer > .contentcontainer > #main-body,
body.whmcsbody .mainmain.wordpresscontainer.whmcscontainer > .contentcontainer > #main-body,
body.whmcsbody #main-body {
    padding-top: 0 !important;
}

@media (max-width: 991px) {
    body.whmcsbody main.mainmain.wordpresscontainer.whmcscontainer,
    body.whmcsbody .mainmain.wordpresscontainer.whmcscontainer {
        padding-top: 6px !important;
    }

    body.whmcsbody main.mainmain.wordpresscontainer.whmcscontainer > .contentcontainer > #main-body,
    body.whmcsbody .mainmain.wordpresscontainer.whmcscontainer > .contentcontainer > #main-body,
    body.whmcsbody #main-body {
        padding-top: 0 !important;
    }
}
</style>
HTML;
});
