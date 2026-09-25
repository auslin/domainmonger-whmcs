<?php
/**
 * DomainMonger ResellerClub Email Forwarding white table styling.
 *
 * Patch 1236:
 * - replaces the ineffective Patch 1235 background-only rules
 * - clears Bootstrap 5 striped-table variables and inset cell shadows
 * - keeps the navy table header, white controls, and pale-orange hover
 *
 * Scope: /manage/clientarea.php?action=domainemailforwarding only.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_rc_email_forwarding_white_table_is_page_1235')) {
    function dm_rc_email_forwarding_white_table_is_page_1235(): bool
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));

        if ($script !== 'clientarea.php' && strpos($uri, '/manage/clientarea.php') === false) {
            return false;
        }

        return $action === 'domainemailforwarding'
            || strpos($uri, 'action=domainemailforwarding') !== false;
    }
}

add_hook('ClientAreaHeadOutput', 2, function () {
    if (!dm_rc_email_forwarding_white_table_is_page_1235()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-rc-email-forwarding-white-table-css-1236">
/* This stylesheet is emitted only on the Email Forwarding page, so selectors
 * do not depend on a template/body class that can vary between WHMCS builds. */
.card,
.card-body,
form,
.table-responsive,
table.table,
table.table tbody,
table.table tbody tr,
table.table tbody td {
    --bs-table-bg: #ffffff !important;
    --bs-table-accent-bg: #ffffff !important;
    --bs-table-striped-bg: #ffffff !important;
    --bs-table-bg-type: #ffffff !important;
    --bs-table-bg-state: #ffffff !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
}

table.table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    border: 1px solid rgba(17, 43, 77, .12) !important;
    border-radius: 7px !important;
    overflow: hidden !important;
}

table.table > tbody > tr > *,
table.table.table-striped > tbody > tr:nth-of-type(odd) > *,
table.table.table-striped > tbody > tr:nth-of-type(even) > * {
    --bs-table-bg: #ffffff !important;
    --bs-table-accent-bg: #ffffff !important;
    --bs-table-striped-bg: #ffffff !important;
    --bs-table-bg-type: #ffffff !important;
    --bs-table-bg-state: #ffffff !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
    box-shadow: none !important;
}

table.table thead th,
table.table th {
    --bs-table-bg: #163a5f !important;
    --bs-table-accent-bg: #163a5f !important;
    --bs-table-bg-type: #163a5f !important;
    --bs-table-bg-state: #163a5f !important;
    background: #163a5f !important;
    background-color: #163a5f !important;
    border-color: rgba(255, 255, 255, .16) !important;
    box-shadow: none !important;
    color: #ffffff !important;
}

table.table tbody td {
    border-color: rgba(17, 43, 77, .08) !important;
    color: #293f56 !important;
    vertical-align: middle !important;
}

table.table tbody tr:hover,
table.table tbody tr:hover > * {
    --bs-table-bg: #fff8f1 !important;
    --bs-table-hover-bg: #fff8f1 !important;
    --bs-table-bg-state: #fff8f1 !important;
    background: #fff8f1 !important;
    background-color: #fff8f1 !important;
    box-shadow: none !important;
}

table.table input[type="text"],
table.table input[type="email"],
table.table .form-control {
    background: #ffffff !important;
    background-color: #ffffff !important;
    border-color: rgba(17, 43, 77, .22) !important;
    box-shadow: none !important;
    color: #293f56 !important;
}

table.table input:focus,
table.table .form-control:focus {
    border-color: #f58220 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .14) !important;
    outline: none !important;
}
</style>
HTML;
});
