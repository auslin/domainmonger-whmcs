<?php
/**
 * DomainMonger WHMCS User Management page cleanup.
 *
 * Patch 426
 * - Replaces failed Patch 425 selectors with direct WHMCS v9 User Management targeting.
 * - Fixes remaining grey user table rows/backgrounds and owner-disabled action buttons.
 * - Page-specific to /manage/account/users and equivalent routed URLs.
 * - Does not touch templates, language files, integration files, or order-form logic.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmonger_user_management_cleanup_output')) {
    function domainmonger_user_management_cleanup_output(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $routePath = $_GET['rp'] ?? '';

        // Keep the isolated WHMCS v9 support/testing route untouched.
        if (stripos($requestUri, 'dmv9support=1') !== false || stripos($queryString, 'dmv9support=1') !== false) {
            return '';
        }

        // Scope to User Management only. Covers pretty route and index.php?rp=/account/users.
        $routeHaystack = $requestUri . ' ' . $scriptName . ' ' . $queryString . ' ' . (is_string($routePath) ? $routePath : '');
        if (stripos($routeHaystack, 'account/users') === false) {
            return '';
        }

        return <<<'HTML'
<style id="domainmonger-user-management-cleanup-v426">
:root {
    --dm-users-navy: #163a5f;
    --dm-users-navy-hover: #214e7a;
    --dm-users-red: #b94a48;
    --dm-users-red-hover: #a03f3d;
    --dm-users-border: #d8e0e8;
    --dm-users-border-soft: #e8edf2;
    --dm-users-hover: #fff3e8;
    --dm-users-muted-text: #687789;
    --dm-users-radius: 6px;
}

/* Patch 426: direct User Management table targeting. The template table is identifiable by its action buttons. */
body table.table.table-striped:has(.btn-manage-permissions),
body table.table.table-striped.dm-user-management-table {
    background: #ffffff !important;
    background-color: #ffffff !important;
    border: 1px solid var(--dm-users-border) !important;
    border-radius: var(--dm-users-radius) !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    overflow: hidden !important;
    margin-bottom: 10px !important;
}

body table.table.table-striped:has(.btn-manage-permissions) tr:first-child th,
body table.table.table-striped.dm-user-management-table tr:first-child th,
body table.table.table-striped.dm-user-management-table .dm-user-management-header-row th {
    background: var(--dm-users-navy) !important;
    background-color: var(--dm-users-navy) !important;
    border-color: var(--dm-users-navy) !important;
    color: #ffffff !important;
    font-weight: 650 !important;
    text-transform: none !important;
    vertical-align: middle !important;
}

body table.table.table-striped:has(.btn-manage-permissions) tr:first-child th *,
body table.table.table-striped.dm-user-management-table tr:first-child th * {
    color: #ffffff !important;
    font-weight: 650 !important;
    text-decoration: none !important;
}

body table.table.table-striped:has(.btn-manage-permissions) tr:not(:first-child),
body table.table.table-striped:has(.btn-manage-permissions) tr:not(:first-child) td,
body table.table.table-striped:has(.btn-manage-permissions) tbody tr,
body table.table.table-striped:has(.btn-manage-permissions) tbody tr td,
body table.table.table-striped:has(.btn-manage-permissions) tbody tr:nth-of-type(odd),
body table.table.table-striped:has(.btn-manage-permissions) tbody tr:nth-of-type(odd) td,
body table.table.table-striped:has(.btn-manage-permissions) tbody tr:nth-of-type(even),
body table.table.table-striped:has(.btn-manage-permissions) tbody tr:nth-of-type(even) td,
body table.table.table-striped.dm-user-management-table tr:not(:first-child),
body table.table.table-striped.dm-user-management-table tr:not(:first-child) td,
body table.table.table-striped.dm-user-management-table tbody tr,
body table.table.table-striped.dm-user-management-table tbody tr td,
body table.table.table-striped.dm-user-management-table tbody tr:nth-of-type(odd),
body table.table.table-striped.dm-user-management-table tbody tr:nth-of-type(odd) td,
body table.table.table-striped.dm-user-management-table tbody tr:nth-of-type(even),
body table.table.table-striped.dm-user-management-table tbody tr:nth-of-type(even) td {
    background: #ffffff !important;
    background-color: #ffffff !important;
}

body table.table.table-striped:has(.btn-manage-permissions) tr:not(:first-child) td,
body table.table.table-striped.dm-user-management-table tr:not(:first-child) td {
    border-color: var(--dm-users-border-soft) !important;
    color: #203040 !important;
    vertical-align: middle !important;
}

body table.table.table-striped:has(.btn-manage-permissions) tr:not(:first-child):hover td,
body table.table.table-striped.dm-user-management-table tr:not(:first-child):hover td {
    background: var(--dm-users-hover) !important;
    background-color: var(--dm-users-hover) !important;
}

body table.table.table-striped:has(.btn-manage-permissions) small,
body table.table.table-striped.dm-user-management-table small {
    color: var(--dm-users-muted-text) !important;
    font-weight: 400 !important;
}

/* Owner-disabled buttons should still read as purpose-styled buttons, not Bootstrap grey blocks. */
body .btn.btn-manage-permissions,
body .btn.btn-manage-permissions.disabled,
body .btn.btn-manage-permissions[disabled],
body table.table.table-striped:has(.btn-manage-permissions) .btn.btn-default.btn-manage-permissions,
body table.table.table-striped.dm-user-management-table .btn.btn-default.btn-manage-permissions {
    background: var(--dm-users-navy) !important;
    background-color: var(--dm-users-navy) !important;
    border-color: var(--dm-users-navy) !important;
    color: #ffffff !important;
    border-radius: var(--dm-users-radius) !important;
    box-shadow: none !important;
    text-decoration: none !important;
    font-weight: 650 !important;
}

body .btn.btn-manage-permissions:not(.disabled):not([disabled]):hover,
body .btn.btn-manage-permissions:not(.disabled):not([disabled]):focus {
    background: var(--dm-users-navy-hover) !important;
    background-color: var(--dm-users-navy-hover) !important;
    border-color: var(--dm-users-navy-hover) !important;
    color: #ffffff !important;
}

body .btn.btn-remove-user,
body .btn.btn-remove-user.disabled,
body .btn.btn-remove-user[disabled] {
    background: var(--dm-users-red) !important;
    background-color: var(--dm-users-red) !important;
    border-color: var(--dm-users-red) !important;
    color: #ffffff !important;
    border-radius: var(--dm-users-radius) !important;
    box-shadow: none !important;
    text-decoration: none !important;
    font-weight: 650 !important;
}

body .btn.btn-remove-user:not(.disabled):not([disabled]):hover,
body .btn.btn-remove-user:not(.disabled):not([disabled]):focus {
    background: var(--dm-users-red-hover) !important;
    background-color: var(--dm-users-red-hover) !important;
    border-color: var(--dm-users-red-hover) !important;
    color: #ffffff !important;
}

body .btn.btn-manage-permissions.disabled,
body .btn.btn-manage-permissions[disabled],
body .btn.btn-remove-user.disabled,
body .btn.btn-remove-user[disabled] {
    opacity: 0.68 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    filter: none !important;
}

/* The hidden permissions chooser is a helper panel, not a grey Bootstrap well. */
body #invitePermissions.well,
body #invitePermissions {
    background: #ffffff !important;
    background-color: #ffffff !important;
    border: 1px solid var(--dm-users-border) !important;
    border-radius: var(--dm-users-radius) !important;
    box-shadow: none !important;
}
</style>
<script id="domainmonger-user-management-cleanup-v426-js">
(function () {
    function applyUserManagementClasses() {
        var tables = document.querySelectorAll('table.table.table-striped');
        Array.prototype.forEach.call(tables, function (table) {
            if (!table.querySelector('.btn-manage-permissions')) {
                return;
            }
            table.classList.add('dm-user-management-table');
            var firstRow = table.querySelector('tr:first-child');
            if (firstRow) {
                firstRow.classList.add('dm-user-management-header-row');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyUserManagementClasses);
    } else {
        applyUserManagementClasses();
    }
})();
</script>
HTML;
    }
}

add_hook('ClientAreaHeadOutput', 1018, function ($vars) {
    return domainmonger_user_management_cleanup_output();
});

add_hook('ClientAreaFooterOutput', 1018, function ($vars) {
    return domainmonger_user_management_cleanup_output();
});
