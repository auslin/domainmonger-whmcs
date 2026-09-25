<?php
/**
 * DomainMonger Account Details page cleanup.
 *
 * Scope:
 * - /manage/clientarea.php?action=details
 *
 * Purpose:
 * - Preserve confirmed soft-orange Email Preferences checkbox styling.
 * - Remove the failed Patch 429 corner selectors and replace them with a more
 *   direct page-scoped sidebar corner cleanup.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    $filename = isset($vars['filename']) ? strtolower((string) $vars['filename']) : '';
    $action = isset($_GET['action']) ? strtolower((string) $_GET['action']) : '';

    if ($filename !== 'clientarea' || $action !== 'details') {
        return '';
    }

    if (isset($_GET['dmv9support'])) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-account-details-cleanup">
/*
 * Account Details only: remove rounded corners from side-menu containers/items.
 * Patch 430 replaces Patch 429's too-specific #Primary_Sidebar-Account rules.
 */
#main-body .sidebar .panel,
#main-body .sidebar .panel-heading,
#main-body .sidebar .panel-body,
#main-body .sidebar .panel-footer,
#main-body .sidebar .card,
#main-body .sidebar .card-header,
#main-body .sidebar .card-body,
#main-body .sidebar .card-footer,
#main-body .sidebar .list-group,
#main-body .sidebar .list-group-item,
#main-body .sidebar a.list-group-item,
#main-body .sidebar .nav,
#main-body .sidebar .nav-item,
#main-body .sidebar .nav-link,
#main-body .sidebar-primary .panel,
#main-body .sidebar-primary .panel-heading,
#main-body .sidebar-primary .list-group,
#main-body .sidebar-primary .list-group-item,
#main-body .sidebar-secondary .panel,
#main-body .sidebar-secondary .panel-heading,
#main-body .sidebar-secondary .list-group,
#main-body .sidebar-secondary .list-group-item,
#main-body .col-md-3 .panel,
#main-body .col-md-3 .panel-heading,
#main-body .col-md-3 .list-group,
#main-body .col-md-3 .list-group-item,
#main-body .col-sm-3 .panel,
#main-body .col-sm-3 .panel-heading,
#main-body .col-sm-3 .list-group,
#main-body .col-sm-3 .list-group-item,
#main-body .col-lg-3 .panel,
#main-body .col-lg-3 .panel-heading,
#main-body .col-lg-3 .list-group,
#main-body .col-lg-3 .list-group-item {
    border-radius: 0 !important;
}

#main-body .sidebar .list-group-item:first-child,
#main-body .sidebar .list-group-item:last-child,
#main-body .sidebar a.list-group-item:first-child,
#main-body .sidebar a.list-group-item:last-child,
#main-body .sidebar .panel > .list-group:first-child .list-group-item:first-child,
#main-body .sidebar .panel > .list-group:last-child .list-group-item:last-child,
#main-body .sidebar .card > .list-group:first-child .list-group-item:first-child,
#main-body .sidebar .card > .list-group:last-child .list-group-item:last-child,
#main-body .col-md-3 .list-group-item:first-child,
#main-body .col-md-3 .list-group-item:last-child,
#main-body .col-sm-3 .list-group-item:first-child,
#main-body .col-sm-3 .list-group-item:last-child,
#main-body .col-lg-3 .list-group-item:first-child,
#main-body .col-lg-3 .list-group-item:last-child {
    border-radius: 0 !important;
}

/* Account Details only: Email Preferences native checkbox color. */
#main-body input[type="checkbox"],
.main-content input[type="checkbox"],
.primary-content input[type="checkbox"],
.clientarea input[type="checkbox"] {
    accent-color: #d8741f !important;
    -webkit-accent-color: #d8741f !important;
}

/* Account Details only: WHMCS/iCheck fallback if this page uses a skinned checkbox. */
#main-body .icheckbox_square-blue,
.main-content .icheckbox_square-blue,
.primary-content .icheckbox_square-blue,
.clientarea .icheckbox_square-blue {
    display: inline-block !important;
    width: 18px !important;
    height: 18px !important;
    min-width: 18px !important;
    min-height: 18px !important;
    margin: 0 6px 0 0 !important;
    padding: 0 !important;
    border: 1px solid #b7c0cc !important;
    border-radius: 2px !important;
    background: #fff !important;
    background-image: none !important;
    box-shadow: none !important;
    cursor: pointer !important;
    position: relative !important;
    vertical-align: -4px !important;
}

#main-body .icheckbox_square-blue.hover,
.main-content .icheckbox_square-blue.hover,
.primary-content .icheckbox_square-blue.hover,
.clientarea .icheckbox_square-blue.hover {
    border-color: #d8741f !important;
    background: #fff7ef !important;
    background-image: none !important;
}

#main-body .icheckbox_square-blue.checked,
.main-content .icheckbox_square-blue.checked,
.primary-content .icheckbox_square-blue.checked,
.clientarea .icheckbox_square-blue.checked {
    border-color: #d8741f !important;
    background: #d8741f !important;
    background-image: none !important;
}

#main-body .icheckbox_square-blue.checked::after,
.main-content .icheckbox_square-blue.checked::after,
.primary-content .icheckbox_square-blue.checked::after,
.clientarea .icheckbox_square-blue.checked::after {
    content: "";
    position: absolute;
    left: 5px;
    top: 2px;
    width: 5px;
    height: 9px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

#main-body .icheckbox_square-blue.disabled,
.main-content .icheckbox_square-blue.disabled,
.primary-content .icheckbox_square-blue.disabled,
.clientarea .icheckbox_square-blue.disabled {
    opacity: .55 !important;
    cursor: default !important;
}
</style>
HTML;
});
