<?php
/**
 * DomainMonger WHMCS v9 styling
 * Patch 679: Hide the confusing two-factor shield badge/icon on User Management.
 *
 * Page-specific to /manage/account/users. This only affects the user list page
 * and leaves permission buttons, role labels, and other 2FA indicators alone.
 */

use WHMCS\View\Menu\Item as MenuItem;

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    return <<<'HTML'
<style id="dm-user-management-hide-shield-badge-css">
    body.dm-user-management-page table.table td i.fa-shield,
    body.dm-user-management-page table.table td i.fas.fa-shield,
    body.dm-user-management-page table.table td i.text-success.fa-shield,
    body.dm-user-management-page table.table td i.text-grey.fa-shield {
        display: none !important;
    }
</style>
<script id="dm-user-management-hide-shield-badge-js">
(function () {
    function isUserManagementPage() {
        var path = (window.location.pathname || '').toLowerCase();
        var search = (window.location.search || '').toLowerCase();

        if (path.indexOf('/manage/account/users') !== -1 && path.indexOf('/permissions') === -1) {
            return true;
        }

        // Fallback for rewritten/alternate WHMCS routes: key off the visible page/card title.
        var titles = document.querySelectorAll('h1, h2, h3, .card-title, .header-lined h1');
        for (var i = 0; i < titles.length; i++) {
            if ((titles[i].textContent || '').trim().toLowerCase() === 'user management') {
                return true;
            }
        }

        return false;
    }

    function apply() {
        if (!isUserManagementPage()) {
            return;
        }

        document.body.classList.add('dm-user-management-page');

        var icons = document.querySelectorAll('table.table td i.fa-shield');
        for (var i = 0; i < icons.length; i++) {
            icons[i].style.display = 'none';
            icons[i].setAttribute('aria-hidden', 'true');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apply);
    } else {
        apply();
    }

    window.setTimeout(apply, 250);
    window.setTimeout(apply, 750);
})();
</script>
HTML;
});
