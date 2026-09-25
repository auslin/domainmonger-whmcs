<?php
/**
 * DomainMonger table-row hover consistency.
 *
 * Patch 1323 / 1324 / initial-overlay correction Patch 1325 / Email History extension Patch 1458
 * - Uses one subtle pale-orange hover (#fff8f1) for standard WHMCS,
 *   converted ResellerClub, and ClouDNS data tables.
 * - Loads in the document head.
 * - Leaves semantic success, warning, danger, info, selected, and active rows intact.
 * - Protects the custom v8x Register Domains namespinner and WHMCS v9 support route.
 * - On the affected standard WHMCS list pages, hides the built-in black
 *   #fullpage-overlay only during initial rendering. The guard releases after
 *   page load, preserving future intentional WHMCS overlay use.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 13230, function ($vars) {
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');

    // Keep the isolated WHMCS v9 support/testing route untouched.
    if (stripos($requestUri, 'dmv9support=1') !== false) {
        return '';
    }

    // Protect the working custom v8x Register Domains namespinner route.
    $isCart = stripos($scriptName, '/cart.php') !== false
        || stripos($requestUri, '/cart.php') !== false;
    $isRegisterRoute = preg_match('/(?:\?|&)a=add(?:&|$)/i', $requestUri) === 1
        && preg_match('/(?:\?|&)domain=(?:register|r)(?:&|$)/i', $requestUri) === 1;

    if ($isCart && $isRegisterRoute) {
        return '';
    }

    $action = strtolower((string) ($_GET['action'] ?? ''));
    $scriptBase = strtolower(basename($scriptName));
    $initialOverlayGuardActions = [
        'domains',
        'services',
        'invoices',
        'quotes',
        'emails',
    ];
    $useInitialOverlayGuard = $scriptBase === 'clientarea.php'
        && in_array($action, $initialOverlayGuardActions, true);

    $overlayGuard = '';
    if ($useInitialOverlayGuard) {
        $overlayGuard = <<<'HTML'
<style id="domainmonger-standard-list-initial-overlay-guard-1325">
/*
 * WHMCS defines #fullpage-overlay as a black full-screen layer with a white
 * spinner. Keep it from painting during only the initial render of these list
 * pages. Once the page has loaded, the html class is removed and intentional
 * later WHMCS overlay use remains available.
 */
html.dm-standard-list-initial-overlay-guard-1325 #fullpage-overlay,
body.dm-standard-list-initial-overlay-guard-1325 #fullpage-overlay,
body #fullpage-overlay.w-hidden {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}
html.dm-standard-list-initial-overlay-guard-1325 #fullpage-overlay img,
body.dm-standard-list-initial-overlay-guard-1325 #fullpage-overlay img,
html.dm-standard-list-initial-overlay-guard-1325 #fullpage-overlay .overlay-spinner,
body.dm-standard-list-initial-overlay-guard-1325 #fullpage-overlay .overlay-spinner {
    display: none !important;
}
</style>
<script id="domainmonger-standard-list-initial-overlay-guard-1325-script">
(function () {
    'use strict';

    var root = document.documentElement;
    var guardClass = 'dm-standard-list-initial-overlay-guard-1325';
    root.classList.add(guardClass);

    function keepInitialOverlayHidden() {
        var overlay = document.getElementById('fullpage-overlay');
        if (!overlay) {
            return;
        }
        overlay.classList.add('w-hidden');
        overlay.setAttribute('aria-hidden', 'true');
    }

    function releaseInitialGuard() {
        keepInitialOverlayHidden();
        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                root.classList.remove(guardClass);
                if (document.body) {
                    document.body.classList.remove(guardClass);
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.body) {
            document.body.classList.add(guardClass);
        }
        keepInitialOverlayHidden();
    }, true);

    window.addEventListener('load', releaseInitialGuard, true);

    // Safety release for cached pages or unusual load ordering.
    window.setTimeout(releaseInitialGuard, 2500);
})();
</script>
HTML;
    }

    $hoverCss = <<<'HTML'
<style id="domainmonger-table-row-hover-consistency-1323">
/*
 * One shared, subtle hover color for interactive data rows.
 * Status-colored and explicitly selected rows keep their semantic styling.
 */
body #main-body table.table-hover > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover,
body #main-body table.table-hover > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover > *,
body #main-body table.table-list > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover,
body #main-body table.table-list > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover > *,
body #main-body table.dataTable > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):not(.child):hover,
body #main-body table.dataTable > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):not(.child):hover > *,
body #main-body .moduleoutput table.table > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover,
body #main-body .moduleoutput table.table > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover > *,
body[class*="dm-resellerclub"] #main-body table.table > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover,
body[class*="dm-resellerclub"] #main-body table.table > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover > *,
body #main-body .dm-dns-live-feed-form-1113 table > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover,
body #main-body .dm-dns-live-feed-form-1113 table > tbody > tr:not(.success):not(.danger):not(.warning):not(.info):not(.table-success):not(.table-danger):not(.table-warning):not(.table-info):not(.selected):not(.active):hover > *,
body #main-body #tableDomainsList > tbody > tr:not(.selected):not(.active):hover,
body #main-body #tableDomainsList > tbody > tr:not(.selected):not(.active):hover > *,
body #main-body #tableServicesList > tbody > tr:not(.selected):not(.active):hover,
body #main-body #tableServicesList > tbody > tr:not(.selected):not(.active):hover > *,
body #main-body #tableInvoicesList > tbody > tr:not(.selected):not(.active):hover,
body #main-body #tableInvoicesList > tbody > tr:not(.selected):not(.active):hover > *,
body #main-body #tableQuotesList > tbody > tr:not(.selected):not(.active):hover,
body #main-body #tableQuotesList > tbody > tr:not(.selected):not(.active):hover > *,
body #main-body #tableTicketsList > tbody > tr:not(.selected):not(.active):hover,
body #main-body #tableTicketsList > tbody > tr:not(.selected):not(.active):hover > *,
body #main-body #payMethodList > tbody > tr:not(.selected):not(.active):hover,
body #main-body #payMethodList > tbody > tr:not(.selected):not(.active):hover > *,
body #main-body #tableLinkedAccounts > tbody > tr:not(.selected):not(.active):hover,
body #main-body #tableLinkedAccounts > tbody > tr:not(.selected):not(.active):hover > *,
body #main-body .dm-server-status-table > tbody > tr:not(.selected):not(.active):hover,
body #main-body .dm-server-status-table > tbody > tr:not(.selected):not(.active):hover > * {
    --bs-table-bg: #fff8f1 !important;
    --bs-table-accent-bg: #fff8f1 !important;
    --bs-table-striped-bg: #fff8f1 !important;
    --bs-table-hover-bg: #fff8f1 !important;
    --bs-table-bg-type: #fff8f1 !important;
    --bs-table-bg-state: #fff8f1 !important;
    background: #fff8f1 !important;
    background-color: #fff8f1 !important;
    background-image: none !important;
    box-shadow: none !important;
}
</style>
HTML;

    return $overlayGuard . $hoverCss;
});
