<?php
/**
 * DomainMonger WHMCS v9 styling
 * Page-specific: Account > User Management action buttons.
 *
 * Patch 631 replaces the Patch 630 layout attempt. It keeps the fix scoped to
 * the User Management page and aligns the Manage Permissions / Remove Access
 * controls as a clean two-button action group with a consistent gap.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function dm_user_management_actions_is_target_page($vars)
{
    $templateFile = isset($vars['templatefile']) ? (string) $vars['templatefile'] : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    return $templateFile === 'account-user-management'
        || strpos($requestUri, '/manage/account/users') !== false
        || strpos($requestUri, '/account/users') !== false;
}

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    if (!dm_user_management_actions_is_target_page($vars)) {
        return '';
    }

    return <<<'HTML'
<style id="dm-user-management-action-buttons-v2">
/* Page-specific: Account > User Management action column alignment. */
body.whmcs-templatefile-account-user-management .dm-user-management-access-table {
    table-layout: auto !important;
}

body.whmcs-templatefile-account-user-management .dm-user-management-access-table th.dm-user-actions-heading,
body.whmcs-templatefile-account-user-management .dm-user-management-access-table td.dm-user-actions-cell {
    width: 300px !important;
    min-width: 300px !important;
    max-width: 300px !important;
    text-align: center !important;
    vertical-align: middle !important;
}

body.whmcs-templatefile-account-user-management .dm-user-management-access-table th.dm-user-actions-heading {
    white-space: nowrap !important;
}

body.whmcs-templatefile-account-user-management .dm-user-management-access-table td.dm-user-actions-cell {
    padding-left: 14px !important;
    padding-right: 14px !important;
}

body.whmcs-templatefile-account-user-management .dm-user-actions-group {
    display: grid !important;
    grid-template-columns: minmax(132px, max-content) minmax(118px, max-content) !important;
    column-gap: 12px !important;
    row-gap: 0 !important;
    align-items: center !important;
    justify-content: center !important;
    justify-items: stretch !important;
    width: 100% !important;
    margin: 0 auto !important;
}

body.whmcs-templatefile-account-user-management .dm-user-actions-group .btn-manage-permissions,
body.whmcs-templatefile-account-user-management .dm-user-actions-group .btn-remove-user {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 28px !important;
    margin: 0 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
}

body.whmcs-templatefile-account-user-management .dm-user-actions-group .btn-manage-permissions {
    min-width: 132px !important;
}

body.whmcs-templatefile-account-user-management .dm-user-actions-group .btn-remove-user {
    min-width: 118px !important;
}

body.whmcs-templatefile-account-user-management .dm-user-actions-group .btn.disabled,
body.whmcs-templatefile-account-user-management .dm-user-actions-group .btn[disabled] {
    pointer-events: none;
}

@media (max-width: 991px) {
    body.whmcs-templatefile-account-user-management .dm-user-management-access-table th.dm-user-actions-heading,
    body.whmcs-templatefile-account-user-management .dm-user-management-access-table td.dm-user-actions-cell {
        width: 210px !important;
        min-width: 210px !important;
        max-width: 210px !important;
    }

    body.whmcs-templatefile-account-user-management .dm-user-actions-group {
        grid-template-columns: 1fr !important;
        row-gap: 8px !important;
        max-width: 170px !important;
    }

    body.whmcs-templatefile-account-user-management .dm-user-actions-group .btn-manage-permissions,
    body.whmcs-templatefile-account-user-management .dm-user-actions-group .btn-remove-user {
        width: 100% !important;
        min-width: 0 !important;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    if (!dm_user_management_actions_is_target_page($vars)) {
        return '';
    }

    return <<<'HTML'
<script id="dm-user-management-action-buttons-v2-script">
(function ($) {
    function dmAlignUserManagementActions() {
        if (!$('body').hasClass('whmcs-templatefile-account-user-management')
            && window.location.pathname.indexOf('/account/users') === -1
            && window.location.pathname.indexOf('/manage/account/users') === -1) {
            return;
        }

        $('table.table').each(function () {
            var $table = $(this);

            if (!$table.find('.btn-manage-permissions, .btn-remove-user').length) {
                return;
            }

            $table.addClass('dm-user-management-access-table');
            $table.find('tr:first th:last-child').addClass('dm-user-actions-heading');

            $table.find('tr').each(function () {
                var $row = $(this);
                var $buttons = $row.find('.btn-manage-permissions, .btn-remove-user');

                if (!$buttons.length) {
                    return;
                }

                var $cell = $buttons.first().closest('td');
                $cell.addClass('dm-user-actions-cell');

                if (!$buttons.first().parent().hasClass('dm-user-actions-group')) {
                    var $group = $('<div class="dm-user-actions-group" />');
                    $buttons.first().before($group);
                    $buttons.appendTo($group);
                }
            });
        });
    }

    $(dmAlignUserManagementActions);
    $(window).on('load', dmAlignUserManagementActions);
})(jQuery);
</script>
HTML;
});
