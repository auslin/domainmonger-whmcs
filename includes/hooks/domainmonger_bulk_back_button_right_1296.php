<?php
/**
 * DomainMonger Patch 1296
 * Move the Back button to the bottom-right on Bulk Auto Renewal and
 * Bulk Registrar Lock pages only.
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 9999, function (array $vars) {
    $templateFile = strtolower((string) ($vars['templatefile'] ?? ''));
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');

    $isBulkDomain = ($templateFile === 'bulkdomainmanagement')
        || (stripos($requestUri, 'clientarea.php') !== false
            && preg_match('/(?:\?|&)action=bulkdomain(?:&|$)/i', $requestUri));

    if (!$isBulkDomain) {
        return '';
    }

    return <<<'HTML'
<style id="dm-bulk-back-right-style-1296">
body.dm-bulk-back-right-1296 #main-body .primary-content > a.dm-bulk-back-button-1296,
body.dm-bulk-back-right-1296 #main-body .main-content > a.dm-bulk-back-button-1296 {
    display: block !important;
    float: none !important;
    clear: both !important;
    width: max-content !important;
    margin: 12px 0 0 auto !important;
    text-align: center !important;
}
</style>
<script id="dm-bulk-back-right-script-1296">
(function () {
    'use strict';

    function applyBulkBackAlignment1296() {
        var form = document.querySelector('form[action*="action=bulkdomain"]');
        if (!form) {
            return;
        }

        var updateInput = form.querySelector('input[name="update"]');
        var update = updateInput ? String(updateInput.value || '').toLowerCase() : '';
        if (update !== 'autorenew' && update !== 'reglock') {
            return;
        }

        var card = form.closest('.card');
        var backButton = null;

        if (card && card.nextElementSibling && card.nextElementSibling.matches('a.btn')) {
            backButton = card.nextElementSibling;
        }

        if (!backButton) {
            var candidates = document.querySelectorAll('a.btn[href*="clientarea.php?action=domains"]');
            for (var i = candidates.length - 1; i >= 0; i -= 1) {
                if (candidates[i].closest('#main-body')) {
                    backButton = candidates[i];
                    break;
                }
            }
        }

        if (!backButton) {
            return;
        }

        document.body.classList.add('dm-bulk-back-right-1296');
        backButton.classList.add('dm-bulk-back-button-1296');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyBulkBackAlignment1296, { once: true });
    } else {
        applyBulkBackAlignment1296();
    }

    window.setTimeout(applyBulkBackAlignment1296, 250);
})();
</script>
HTML;
});
