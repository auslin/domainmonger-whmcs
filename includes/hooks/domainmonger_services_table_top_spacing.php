<?php
/**
 * DomainMonger My Services table top-control spacing.
 *
 * Patch 534
 * - Adds breathing room around the "Showing..." text and search box above the
 *   My Services table.
 * - Scoped to the My Services/clientareaproducts page only.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeaderOutput', 534, function ($vars) {
    $templateFile = isset($vars['templatefile']) ? strtolower((string) $vars['templatefile']) : '';
    $filename = isset($vars['filename']) ? strtolower((string) $vars['filename']) : '';
    $action = isset($_REQUEST['action']) && !is_array($_REQUEST['action']) ? strtolower((string) $_REQUEST['action']) : '';

    if ($templateFile !== 'clientareaproducts' && !($filename === 'clientarea' && $action === 'services')) {
        return '';
    }

    return <<<HTML
<style id="domainmonger-services-table-top-spacing-534">
body.whmcsbody #tableServicesList_wrapper .listtable > .dataTables_info {
    margin: 10px 0 10px 12px !important;
    padding: 0 !important;
    line-height: 34px !important;
}

body.whmcsbody #tableServicesList_wrapper .listtable > .dataTables_filter {
    margin: 10px 12px 10px 0 !important;
    padding: 0 !important;
    line-height: 34px !important;
}

body.whmcsbody #tableServicesList_wrapper .listtable > .dataTables_filter label {
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
}

body.whmcsbody #tableServicesList_wrapper .listtable > .dataTables_filter input[type="search"],
body.whmcsbody #tableServicesList_wrapper .listtable > .dataTables_filter input.form-control {
    height: 34px !important;
    min-height: 34px !important;
    margin-left: 8px !important;
    box-sizing: border-box !important;
}

body.whmcsbody #tableServicesList_wrapper table#tableServicesList {
    clear: both !important;
}
</style>
HTML;
});
