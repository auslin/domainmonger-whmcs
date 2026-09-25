<?php
/**
 * DomainMonger WHMCS top header match WordPress.
 *
 * Patch 456
 * - WHMCS client-area header only.
 * - Mirrors the confirmed WordPress header direction from WP patches 449-451:
 *   white logo/utility row + full-width navy main menu bar.
 * - Does not edit the integration folder.
 * - Excludes the isolated WHMCS v9 support/testing route when dmv9support=1 is present.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    // Keep the isolated WHMCS v9 support/testing route untouched.
    if (stripos($requestUri, 'dmv9support=1') !== false) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-whmcs-top-header-match-wordpress">
/*
 * DomainMonger WHMCS header bridge.
 * Scope: WHMCS pages using the Stellar integration header only.
 */
body.whmcsbody.wordpressbody {
    --dm-orange: #f58220;
    --dm-orange-soft: #d8741f;
    --dm-navy: #163a5f;
    --dm-navy-hover: #214e7a;
    --dm-pale-orange: #fff3e8;
    --dm-border: #d9e0e7;
    --dm-body-text: #333f4f;
}

body.whmcsbody.wordpressbody .basecontainer {
    position: relative !important;
}

/* White logo/header row. */
body.whmcsbody.wordpressbody .headermain,
body.whmcsbody.wordpressbody .headermain.headermain-design1 {
    background: #ffffff !important;
    border-bottom: 0 !important;
    box-shadow: none !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}

body.whmcsbody.wordpressbody .headermain.headermain-design1 .headermain-inner {
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
    justify-content: flex-start !important;
}

body.whmcsbody.wordpressbody .headermain.headermain-design1 .headermain-logo {
    width: 100% !important;
    min-height: 72px !important;
    display: flex !important;
    align-items: center !important;
    padding: 8px 360px 8px 0 !important;
    margin: 0 !important;
    border-bottom: 1px solid var(--dm-border) !important;
}

body.whmcsbody.wordpressbody .headermain.headermain-design1 .headermain-logo a,
body.whmcsbody.wordpressbody .headermain.headermain-design1 .headermain-logo img {
    display: block !important;
}

/* Main navigation: full-width navy bar under the white header row. */
body.whmcsbody.wordpressbody .headermain.headermain-design1 .navmain-container {
    width: 100vw !important;
    min-width: 100vw !important;
    margin-left: calc((100vw - 100%) / -2) !important;
    margin-right: calc((100vw - 100%) / -2) !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    padding: 0 !important;
    background: var(--dm-navy) !important;
    border: 0 !important;
    border-bottom: 1px solid #102d49 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

body.whmcsbody.wordpressbody .headermain.headermain-design1 .navmain-container .navmain {
    width: var(--container-width, 90%) !important;
    max-width: var(--container-maxwidth, 100%) !important;
    margin: 0 auto !important;
}

body.whmcsbody.wordpressbody .headermain.headermain-design1 .navmain > ul {
    justify-content: flex-end !important;
}

body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li,
body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li.navmain-cta {
    background: var(--dm-navy) !important;
    border-color: #102d49 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li > a,
body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li.navmain-cta > a {
    color: #ffffff !important;
    text-shadow: none !important;
    text-decoration: none !important;
    border-radius: 0 !important;
}

body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li:hover,
body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li.navmain-active,
body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li.current_page_parent,
body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li.current_page_item,
body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li.current-menu-item {
    background: var(--dm-navy-hover) !important;
}

body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li.navmain-cta,
body.whmcsbody.wordpressbody .headermain-design1 .navmain ul li.navmain-cta:hover {
    background: var(--dm-orange) !important;
}

/* Dropdowns stay light, like WordPress and the WHMCS styling direction. */
body.whmcsbody.wordpressbody .headermain.headermain-design1 .navmain .navmain-dropdown-single .navmain-subcontainer,
body.whmcsbody.wordpressbody .headermain.headermain-design1 .navmain .navmain-subcontainer {
    background: #ffffff !important;
    border-color: var(--dm-border) !important;
    border-radius: 0 0 4px 4px !important;
    box-shadow: 0 10px 24px rgba(22, 58, 95, .12) !important;
}

body.whmcsbody.wordpressbody .headermain.headermain-design1 .navmain .navmain-dropdown-single .navmain-subcontainer .navmain-link a {
    background: #ffffff !important;
    border-bottom: 1px solid #eef2f6 !important;
    color: var(--dm-navy) !important;
    text-shadow: none !important;
}

body.whmcsbody.wordpressbody .headermain.headermain-design1 .navmain .navmain-dropdown-single .navmain-subcontainer .navmain-link a:hover,
body.whmcsbody.wordpressbody .headermain.headermain-design1 .navmain .navmain-dropdown-single .navmain-subcontainer .navmain-link.current-menu-item a {
    background: var(--dm-pale-orange) !important;
    color: var(--dm-orange-soft) !important;
}

/* Merge the utility links into the white logo row. */
body.whmcsbody.wordpressbody .toolbarmain-container {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 30 !important;
    height: 0 !important;
    overflow: visible !important;
    background: transparent !important;
    border: 0 !important;
    color: var(--dm-navy) !important;
}

body.whmcsbody.wordpressbody .toolbarmain {
    min-height: 72px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    color: var(--dm-navy) !important;
    pointer-events: none !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-text,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-googletranslate {
    display: none !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu {
    display: flex !important;
    justify-content: flex-end !important;
    align-items: center !important;
    width: auto !important;
    margin-left: auto !important;
    pointer-events: auto !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul {
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: flex-end !important;
    align-items: center !important;
    gap: 6px !important;
    margin: 0 !important;
    padding: 0 !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li {
    float: none !important;
    position: relative !important;
    list-style: none !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li a,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li.toolbarmain-menu-highlight a,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li.toolbarmain-menu-language a,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li.toolbarmain-menu-welcome a {
    display: flex !important;
    align-items: center !important;
    min-height: 34px !important;
    padding: 7px 10px !important;
    background: #ffffff !important;
    border: 1px solid transparent !important;
    border-radius: 4px !important;
    color: var(--dm-navy) !important;
    text-decoration: none !important;
    text-shadow: none !important;
    box-shadow: none !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li a i {
    margin-right: 5px !important;
    color: inherit !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li a:hover,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li a:focus,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li.toolbarmain-menu-highlight a:hover,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li.toolbarmain-menu-highlight a:focus,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li.toolbarmain-menu-language a:hover,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li.toolbarmain-menu-language a:focus,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li.toolbarmain-menu-welcome a:hover,
body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li.toolbarmain-menu-welcome a:focus {
    background: var(--dm-pale-orange) !important;
    border-color: #f6c9a5 !important;
    color: var(--dm-orange-soft) !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li ul {
    top: 100% !important;
    right: 0 !important;
    left: auto !important;
    background: #ffffff !important;
    border: 1px solid var(--dm-border) !important;
    border-radius: 4px !important;
    box-shadow: 0 10px 24px rgba(22, 58, 95, .12) !important;
    overflow: hidden !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li li a {
    min-height: 0 !important;
    padding: 9px 12px !important;
    background: #ffffff !important;
    border: 0 !important;
    border-bottom: 1px solid #eef2f6 !important;
    border-radius: 0 !important;
    color: var(--dm-navy) !important;
}

body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul li li a:hover {
    background: var(--dm-pale-orange) !important;
    color: var(--dm-orange-soft) !important;
}

/* Keep utility links with the sticky header on desktop, matching WordPress Patch 451. */
@media (min-width: 1101px) {
    body.whmcsbody.wordpressbody .toolbarmain-container {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 10001 !important;
        height: 0 !important;
        overflow: visible !important;
        pointer-events: none !important;
        background: transparent !important;
        border: 0 !important;
    }

    body.whmcsbody.wordpressbody.admin-bar .toolbarmain-container {
        top: 32px !important;
    }

    body.whmcsbody.wordpressbody .toolbarmain,
    body.whmcsbody.wordpressbody .toolbarmain .contentcontainer {
        pointer-events: none !important;
    }

    body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu {
        pointer-events: auto !important;
    }

    body.whmcsbody.wordpressbody .headermain.headermain-design1.isStuck {
        z-index: 9999 !important;
    }
}

/* Tablet/mobile: avoid crowding the logo. */
@media (max-width: 1100px) {
    body.whmcsbody.wordpressbody .toolbarmain-container {
        position: static !important;
        height: auto !important;
        background: #ffffff !important;
        border-bottom: 1px solid var(--dm-border) !important;
    }

    body.whmcsbody.wordpressbody .toolbarmain {
        min-height: 0 !important;
        padding: 8px 0 !important;
        justify-content: center !important;
        pointer-events: auto !important;
    }

    body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu,
    body.whmcsbody.wordpressbody .toolbarmain .toolbarmain-menu ul {
        justify-content: center !important;
        width: 100% !important;
    }

    body.whmcsbody.wordpressbody .headermain.headermain-design1 .headermain-logo {
        min-height: 0 !important;
        justify-content: center !important;
        padding: 8px 0 !important;
        border-bottom: 0 !important;
    }

    body.whmcsbody.wordpressbody .headermain.headermain-design1 .navmain-container {
        margin-bottom: 0 !important;
    }
}
</style>
HTML;
});
