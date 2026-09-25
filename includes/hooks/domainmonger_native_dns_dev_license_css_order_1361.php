<?php
/**
 * DomainMonger Register DNS Development License visual ordering.
 *
 * Patch 1364 (extends confirmed Patch 1361)
 * - Uses CSS ordering only after the confirmed Patch 1322 hook has placed the
 *   WHMCS Development License notice as a direct child of #main-body > .container.
 * - Extends the confirmed native DNS ordering to Register DNS Email Forwarding.
 * - Does not move, clone, hide, remove, or observe the notice.
 * - Does not touch the native DNS live feed or Email Forwarding content.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1361, static function ($vars) {
    $action = $_GET['action'] ?? '';
    if (is_array($action)) {
        return '';
    }

    $action = strtolower(trim((string) $action));
    if (!in_array($action, ['domaindns', 'domainemailforwarding'], true)) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-register-dns-dev-license-css-order-1364">
/*
 * The site-wide WHMCS menu is outside #main-body. On these two pages, make the
 * existing #main-body container a vertical flex stack only after Patch 1322
 * has safely promoted the notice to a direct child. CSS order then places the
 * notice before the page-specific Manage Domain header without changing DOM.
 */
body.whmcs-templatefile-clientareadomaindns.dm-rc-page-dnsrecords.dm-dev-license-positioned-1322
#main-body > .container,
body.whmcs-templatefile-clientareadomainemailforwarding.dm-rc-page-emailforwarding.dm-dev-license-positioned-1322
#main-body > .container {
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
}

body.whmcs-templatefile-clientareadomaindns.dm-rc-page-dnsrecords.dm-dev-license-positioned-1322
#main-body > .container > *,
body.whmcs-templatefile-clientareadomainemailforwarding.dm-rc-page-emailforwarding.dm-dev-license-positioned-1322
#main-body > .container > * {
    order: 40;
    min-width: 0;
}

body.whmcs-templatefile-clientareadomaindns.dm-rc-page-dnsrecords.dm-dev-license-positioned-1322
#main-body > .container > .dm-dev-license-positioned-1322,
body.whmcs-templatefile-clientareadomainemailforwarding.dm-rc-page-emailforwarding.dm-dev-license-positioned-1322
#main-body > .container > .dm-dev-license-positioned-1322 {
    order: 10;
}

body.whmcs-templatefile-clientareadomaindns.dm-rc-page-dnsrecords.dm-dev-license-positioned-1322
#main-body > .container > #dm-rc-unified-nav-1152,
body.whmcs-templatefile-clientareadomainemailforwarding.dm-rc-page-emailforwarding.dm-dev-license-positioned-1322
#main-body > .container > #dm-rc-unified-nav-1152 {
    order: 20;
}

body.whmcs-templatefile-clientareadomaindns.dm-rc-page-dnsrecords.dm-dev-license-positioned-1322
#main-body > .container > #dm-dns-system-indicator-1337,
body.whmcs-templatefile-clientareadomainemailforwarding.dm-rc-page-emailforwarding.dm-dev-license-positioned-1322
#main-body > .container > #dm-dns-system-indicator-1337 {
    order: 30;
}

body.whmcs-templatefile-clientareadomaindns.dm-rc-page-dnsrecords.dm-dev-license-positioned-1322
#main-body > .container > .d-md-none.col-md-3.sidebar,
body.whmcs-templatefile-clientareadomainemailforwarding.dm-rc-page-emailforwarding.dm-dev-license-positioned-1322
#main-body > .container > .d-md-none.col-md-3.sidebar {
    order: 50;
}

body.whmcs-templatefile-clientareadomaindns.dm-rc-page-dnsrecords.dm-dev-license-positioned-1322
#main-body > .container > .clearfix,
body.whmcs-templatefile-clientareadomainemailforwarding.dm-rc-page-emailforwarding.dm-dev-license-positioned-1322
#main-body > .container > .clearfix {
    order: 60;
}
</style>
HTML;
});
