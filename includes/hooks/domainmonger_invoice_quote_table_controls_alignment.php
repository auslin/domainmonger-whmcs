<?php
/**
 * DomainMonger WHMCS v9 styling
 * Page-specific: My Invoices and My Quotes DataTables top controls.
 *
 * Patch 633 aligns the top "Showing..." text and search input with the
 * already-approved My Services layout without changing global DataTables styles.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function dm_invoice_quote_controls_is_target_page($vars)
{
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    return $templateFile === 'clientareainvoices'
        || $templateFile === 'clientareaquotes'
        || strpos($requestUri, 'clientarea.php?action=invoices') !== false
        || strpos($requestUri, 'clientarea.php?action=quotes') !== false;
}

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    if (!dm_invoice_quote_controls_is_target_page($vars)) {
        return '';
    }

    return <<<'HTML'
<style id="dm-invoice-quote-controls-alignment-v1">
/* Page-specific: My Invoices / My Quotes top table controls. */
body.whmcs-templatefile-clientareainvoices #tableInvoicesList_wrapper .listtable,
body.whmcs-templatefile-clientareaquotes #tableQuotesList_wrapper .listtable {
    overflow: visible !important;
}

/* Match the approved My Services left inset for the "Showing..." text. */
body.whmcs-templatefile-clientareainvoices #tableInvoicesList_wrapper .dataTables_info,
body.whmcs-templatefile-clientareaquotes #tableQuotesList_wrapper .dataTables_info {
    padding-left: 14px !important;
    padding-top: 0 !important;
    margin-bottom: 12px !important;
    line-height: 34px !important;
}

/* Move the search control down and in from the right to match My Services. */
body.whmcs-templatefile-clientareainvoices #tableInvoicesList_wrapper .dataTables_filter,
body.whmcs-templatefile-clientareaquotes #tableQuotesList_wrapper .dataTables_filter {
    padding-top: 7px !important;
    padding-right: 14px !important;
    margin-top: 0 !important;
    margin-bottom: 13px !important;
    text-align: right !important;
}

body.whmcs-templatefile-clientareainvoices #tableInvoicesList_wrapper .dataTables_filter label,
body.whmcs-templatefile-clientareaquotes #tableQuotesList_wrapper .dataTables_filter label {
    margin-bottom: 0 !important;
}

body.whmcs-templatefile-clientareainvoices #tableInvoicesList_wrapper .dataTables_filter input,
body.whmcs-templatefile-clientareaquotes #tableQuotesList_wrapper .dataTables_filter input {
    margin-top: 0 !important;
    margin-right: 0 !important;
}

body.whmcs-templatefile-clientareainvoices #tableInvoicesList,
body.whmcs-templatefile-clientareaquotes #tableQuotesList {
    clear: both !important;
}

@media (max-width: 767px) {
    body.whmcs-templatefile-clientareainvoices #tableInvoicesList_wrapper .dataTables_info,
    body.whmcs-templatefile-clientareaquotes #tableQuotesList_wrapper .dataTables_info,
    body.whmcs-templatefile-clientareainvoices #tableInvoicesList_wrapper .dataTables_filter,
    body.whmcs-templatefile-clientareaquotes #tableQuotesList_wrapper .dataTables_filter {
        float: none !important;
        width: 100% !important;
        padding-left: 12px !important;
        padding-right: 12px !important;
        text-align: left !important;
    }

    body.whmcs-templatefile-clientareainvoices #tableInvoicesList_wrapper .dataTables_filter,
    body.whmcs-templatefile-clientareaquotes #tableQuotesList_wrapper .dataTables_filter {
        padding-top: 4px !important;
    }
}
</style>
HTML;
});
