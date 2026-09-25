<?php
/**
 * DomainMonger header consistency normalization — Patch 1294.
 *
 * Audited header systems:
 * - Shared portal page headers (Patch 1283/1292)
 * - Four bulk-domain page headers (Patches 1258/1263/1278/1293)
 * - ResellerClub unified Manage Domain header/menu
 * - ClouDNS regular and slave-zone Manage Domain headers/menus
 *
 * Standard:
 * - Ordinary page headers follow the confirmed Bulk Auto Renewal appearance:
 *   18px title, 13px subtitle, 13px/18px padding, square corners, 12px gap.
 * - Module Manage Domain headers retain their specialized title/switcher layout,
 *   while ClouDNS and ResellerClub use matching geometry and typography.
 *
 * Presentation only. No routes, forms, module processing, namespinner behavior,
 * language files, or integration files are changed.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 20000, static function ($vars) {
    return <<<'HTML'
<style id="domainmonger-header-consistency-1294-css">
/* ================================================================
   1. STANDARD PORTAL + BULK PAGE HEADERS
   Preferred reference: confirmed Bulk Auto Renewal header.
   ================================================================ */
body #main-body .dm-portal-page-header-1283,
body #main-body .dm-bulk-contact-section-header-1258,
body #main-body .dm-bulk-standard-section-header-1263 {
    box-sizing: border-box !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    min-height: 62px !important;
    margin: 0 0 12px !important;
    padding: 13px 18px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: #163a5f !important;
    background-color: #163a5f !important;
    background-image: none !important;
    box-shadow: none !important;
    color: #ffffff !important;
    text-align: left !important;
}

/* Full-width sidebar wrappers own the 12px gap; avoid doubling it. */
body #main-body .dm-portal-page-header-wrap-1292,
body #main-body .dm-bulk-wide-header-wrap-1293 {
    box-sizing: border-box !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 0 12px !important;
}

body #main-body .dm-portal-page-header-wrap-1292 > .dm-portal-page-header-1283,
body #main-body .dm-bulk-wide-header-wrap-1293 > .dm-bulk-contact-section-header-1258,
body #main-body .dm-bulk-wide-header-wrap-1293 > .dm-bulk-standard-section-header-1263 {
    margin: 0 !important;
}

/* Use one title scale and one subtitle scale everywhere. */
body #main-body .dm-portal-page-header-1283__text,
body #main-body .dm-bulk-contact-section-header-1258 > div,
body #main-body .dm-bulk-standard-section-header-1263 > div {
    display: flex !important;
    flex: 1 1 auto !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    justify-content: center !important;
    gap: 2px !important;
    min-width: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    text-align: left !important;
}

body #main-body .dm-portal-page-header-1283__title,
body #main-body .dm-bulk-contact-section-header-1258 strong,
body #main-body .dm-bulk-standard-section-header-1263 strong {
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.25 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
    text-shadow: none !important;
    text-align: left !important;
}

body #main-body .dm-portal-page-header-1283__subtitle,
body #main-body .dm-bulk-contact-section-header-1258 span,
body #main-body .dm-bulk-standard-section-header-1263 span {
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    color: rgba(255, 255, 255, .88) !important;
    -webkit-text-fill-color: rgba(255, 255, 255, .88) !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.35 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
    text-shadow: none !important;
    text-align: left !important;
}

/* ================================================================
   2. RESELLERCLUB + CLOUDNS MANAGE DOMAIN HEADERS
   Retain the specialized module hierarchy while matching dimensions.
   ================================================================ */
body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-header,
body #main-body .cloudns-module-header.cloudns-manage-domain-header {
    box-sizing: border-box !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 18px !important;
    width: 100% !important;
    min-height: 66px !important;
    margin: 0 !important;
    padding: 14px 18px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: #163a5f !important;
    background-color: #163a5f !important;
    background-image: none !important;
    box-shadow: none !important;
    color: #ffffff !important;
}

body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-title,
body #main-body .cloudns-manage-domain-header .cloudns-module-title-block {
    display: flex !important;
    flex: 1 1 auto !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    justify-content: center !important;
    gap: 2px !important;
    min-width: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
}

body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-title span,
body #main-body .cloudns-manage-domain-header .cloudns-module-eyebrow {
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    color: rgba(255, 255, 255, .78) !important;
    -webkit-text-fill-color: rgba(255, 255, 255, .78) !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    letter-spacing: .08em !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}

body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-title strong,
body #main-body .cloudns-manage-domain-header .cloudns-module-domain {
    display: block !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 21px !important;
    font-weight: 800 !important;
    line-height: 1.25 !important;
    text-overflow: ellipsis !important;
    overflow-wrap: anywhere !important;
    white-space: nowrap !important;
}

/* Match the domain selectors and confirmation buttons as part of the header. */
body #main-body #dm-rc-unified-nav-1152 .dm-rc-switch-wrap,
body #main-body .cloudns-manage-domain-header .cloudns-header-tools,
body #main-body .cloudns-manage-domain-header .cloudns-global-domain-switcher {
    align-items: center !important;
    gap: 9px !important;
    margin: 0 !important;
    padding: 0 !important;
}

body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-switch,
body #main-body .cloudns-manage-domain-header .cloudns-global-domain-switcher select.form-control {
    box-sizing: border-box !important;
    width: min(100%, 265px) !important;
    min-width: 190px !important;
    max-width: 265px !important;
    height: 38px !important;
    min-height: 38px !important;
    margin: 0 !important;
    padding: 7px 34px 7px 11px !important;
    border: 1px solid rgba(255, 255, 255, .50) !important;
    border-radius: 4px !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
    color: #163a5f !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
    box-shadow: none !important;
}

body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-switch-button-1272,
body #main-body .cloudns-manage-domain-header .cloudns-domain-switch-button {
    box-sizing: border-box !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 72px !important;
    height: 38px !important;
    min-height: 38px !important;
    margin: 0 !important;
    padding: 7px 15px !important;
    border: 1px solid #f58220 !important;
    border-radius: 4px !important;
    background: #f58220 !important;
    background-color: #f58220 !important;
    color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    box-shadow: none !important;
}

body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-switch-button-1272:hover,
body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-switch-button-1272:focus,
body #main-body .cloudns-manage-domain-header .cloudns-domain-switch-button:hover,
body #main-body .cloudns-manage-domain-header .cloudns-domain-switch-button:focus {
    border-color: #d8741f !important;
    background: #d8741f !important;
    background-color: #d8741f !important;
    color: #ffffff !important;
    outline: 0 !important;
    box-shadow: none !important;
}

/* Preserve the connected header/menu join and exact outside alignment. */
body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-header,
body #main-body #dm-rc-unified-nav-1152 .dm-rc-main-menu,
body #main-body .cloudns-module-header.cloudns-manage-domain-header,
body #main-body .cloudns-module-header.cloudns-manage-domain-header + ul#cloudnsSettingsMenu,
body #main-body .cloudns-module-header.cloudns-manage-domain-header + ul#cloudnsSlaveMenu {
    box-sizing: border-box !important;
    width: 100% !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

@media (max-width: 870px) {
    body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-header,
    body #main-body .cloudns-module-header.cloudns-manage-domain-header {
        align-items: stretch !important;
        flex-direction: column !important;
        min-height: 0 !important;
        gap: 10px !important;
        padding: 12px 14px !important;
    }

    body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-title strong,
    body #main-body .cloudns-manage-domain-header .cloudns-module-domain {
        font-size: 19px !important;
    }

    body #main-body #dm-rc-unified-nav-1152 .dm-rc-switch-wrap,
    body #main-body .cloudns-manage-domain-header .cloudns-header-tools,
    body #main-body .cloudns-manage-domain-header .cloudns-global-domain-switcher {
        width: 100% !important;
        justify-content: flex-start !important;
    }

    body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-switch,
    body #main-body .cloudns-manage-domain-header .cloudns-global-domain-switcher select.form-control {
        flex: 1 1 190px !important;
        width: auto !important;
        min-width: 0 !important;
        max-width: none !important;
    }
}

@media (max-width: 767px) {
    body #main-body .dm-portal-page-header-1283,
    body #main-body .dm-bulk-contact-section-header-1258,
    body #main-body .dm-bulk-standard-section-header-1263 {
        min-height: 0 !important;
        padding: 12px 14px !important;
    }

    body #main-body .dm-portal-page-header-1283__title,
    body #main-body .dm-bulk-contact-section-header-1258 strong,
    body #main-body .dm-bulk-standard-section-header-1263 strong {
        font-size: 17px !important;
    }

    body #main-body .dm-portal-page-header-1283__subtitle,
    body #main-body .dm-bulk-contact-section-header-1258 span,
    body #main-body .dm-bulk-standard-section-header-1263 span {
        font-size: 12px !important;
    }
}

@media (max-width: 480px) {
    body #main-body #dm-rc-unified-nav-1152 .dm-rc-switch-wrap,
    body #main-body .cloudns-manage-domain-header .cloudns-global-domain-switcher {
        align-items: stretch !important;
        flex-direction: column !important;
    }

    body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-switch,
    body #main-body #dm-rc-unified-nav-1152 .dm-rc-domain-switch-button-1272,
    body #main-body .cloudns-manage-domain-header .cloudns-global-domain-switcher select.form-control,
    body #main-body .cloudns-manage-domain-header .cloudns-domain-switch-button {
        width: 100% !important;
        max-width: none !important;
    }
}
</style>
HTML;
});
