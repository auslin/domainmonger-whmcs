<?php
/**
 * DomainMonger WHMCS v9 styling cleanup: Payment Methods direct table cleanup.
 *
 * Patch 437 replaces the failed/no-change Patch 436 selector set. It is still
 * scoped to the Payment Methods page only, but targets the real WHMCS table id
 * (#payMethodList) and outputs late in the footer so theme CSS cannot easily
 * override it.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function domainmonger_is_payment_methods_page(array $vars = []): bool
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    if (strpos($requestUri, 'dmv9support=1') !== false) {
        return false;
    }

    if (strpos($requestUri, '/manage/account/paymentmethods') !== false || strpos($requestUri, 'account/paymentmethods') !== false) {
        return true;
    }

    foreach (['templatefile', 'filename', 'pagetitle'] as $key) {
        if (isset($vars[$key]) && stripos((string) $vars[$key], 'payment') !== false) {
            return true;
        }
    }

    return false;
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    if (!domainmonger_is_payment_methods_page(is_array($vars) ? $vars : [])) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-patch437-payment-methods-direct">
/* DomainMonger Patch 437: Payment Methods direct cleanup. Replaces failed Patch 436 selectors. */
#payMethodList,
#payMethodList tbody,
#payMethodList tbody tr,
#payMethodList tbody tr:nth-child(odd),
#payMethodList tbody tr:nth-child(even),
#payMethodList tbody tr > td,
#payMethodList tbody tr > th,
#payMethodList tr:not(:first-child),
#payMethodList tr:not(:first-child) > td,
#payMethodList tr:not(:first-child) > th,
.table-striped#payMethodList > tbody > tr:nth-of-type(odd),
.table-striped#payMethodList > tbody > tr:nth-of-type(even),
.table#payMethodList > tbody > tr > td,
.table#payMethodList > tbody > tr > th {
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
}

#payMethodList tr:first-child,
#payMethodList tr:first-child > th,
#payMethodList tbody tr:first-child,
#payMethodList tbody tr:first-child > th {
    background: #163a5f !important;
    background-color: #163a5f !important;
    color: #ffffff !important;
    border-color: #163a5f !important;
}

#payMethodList tbody tr:not(:first-child):hover,
#payMethodList tbody tr:not(:first-child):hover > td,
#payMethodList tr:not(:first-child):hover,
#payMethodList tr:not(:first-child):hover > td {
    background: #fff3e8 !important;
    background-color: #fff3e8 !important;
}

#payMethodList td,
#payMethodList th {
    border-color: #d7dee8 !important;
}

/* The remaining grey control on this page is usually Set as Default. Keep disabled behavior, but use muted navy instead of Bootstrap grey. */
#payMethodList .btn-set-default,
#payMethodList .btn-set-default.disabled,
#payMethodList .btn-set-default:disabled,
#payMethodList a.btn.btn-default:not(.btn-delete):not([data-role="edit-payment-method"]),
#payMethodList a.btn.btn-secondary:not(.btn-delete):not([data-role="edit-payment-method"]),
#payMethodList a.btn.btn-light:not(.btn-delete):not([data-role="edit-payment-method"]) {
    background: #5f7184 !important;
    background-color: #5f7184 !important;
    border-color: #5f7184 !important;
    color: #ffffff !important;
    opacity: 1 !important;
    text-shadow: none !important;
    box-shadow: none !important;
}

#payMethodList .btn-set-default:hover,
#payMethodList .btn-set-default:focus,
#payMethodList a.btn.btn-default:not(.btn-delete):not([data-role="edit-payment-method"]):hover,
#payMethodList a.btn.btn-default:not(.btn-delete):not([data-role="edit-payment-method"]):focus,
#payMethodList a.btn.btn-secondary:not(.btn-delete):not([data-role="edit-payment-method"]):hover,
#payMethodList a.btn.btn-secondary:not(.btn-delete):not([data-role="edit-payment-method"]):focus,
#payMethodList a.btn.btn-light:not(.btn-delete):not([data-role="edit-payment-method"]):hover,
#payMethodList a.btn.btn-light:not(.btn-delete):not([data-role="edit-payment-method"]):focus {
    background: #214e7a !important;
    background-color: #214e7a !important;
    border-color: #214e7a !important;
    color: #ffffff !important;
}

/* Keep the existing action colors from the successful global button palette. */
#payMethodList [data-role="edit-payment-method"],
#payMethodList a[href*="paymentmethods-view"].btn,
#payMethodList a[href*="paymentmethod"]:not(.btn-delete).btn:not(.btn-set-default) {
    background: #163a5f !important;
    background-color: #163a5f !important;
    border-color: #163a5f !important;
    color: #ffffff !important;
}

#payMethodList [data-role="edit-payment-method"]:hover,
#payMethodList [data-role="edit-payment-method"]:focus,
#payMethodList a[href*="paymentmethods-view"].btn:hover,
#payMethodList a[href*="paymentmethods-view"].btn:focus,
#payMethodList a[href*="paymentmethod"]:not(.btn-delete).btn:not(.btn-set-default):hover,
#payMethodList a[href*="paymentmethod"]:not(.btn-delete).btn:not(.btn-set-default):focus {
    background: #214e7a !important;
    background-color: #214e7a !important;
    border-color: #214e7a !important;
    color: #ffffff !important;
}
</style>
<script>
(function () {
    function applyDomainMongerPaymentMethodCleanup() {
        var table = document.getElementById('payMethodList');
        if (!table) {
            return;
        }

        var rows = table.querySelectorAll('tr');
        rows.forEach(function (row, index) {
            if (index === 0) {
                row.style.setProperty('background', '#163a5f', 'important');
                row.style.setProperty('background-color', '#163a5f', 'important');
                row.querySelectorAll('th,td').forEach(function (cell) {
                    cell.style.setProperty('background', '#163a5f', 'important');
                    cell.style.setProperty('background-color', '#163a5f', 'important');
                    cell.style.setProperty('color', '#ffffff', 'important');
                    cell.style.setProperty('border-color', '#163a5f', 'important');
                });
            } else {
                row.style.setProperty('background', '#ffffff', 'important');
                row.style.setProperty('background-color', '#ffffff', 'important');
                row.querySelectorAll('th,td').forEach(function (cell) {
                    cell.style.setProperty('background', '#ffffff', 'important');
                    cell.style.setProperty('background-color', '#ffffff', 'important');
                });
            }
        });

        table.querySelectorAll('.btn-set-default, a.btn.btn-default:not(.btn-delete):not([data-role="edit-payment-method"])').forEach(function (button) {
            button.style.setProperty('background', '#5f7184', 'important');
            button.style.setProperty('background-color', '#5f7184', 'important');
            button.style.setProperty('border-color', '#5f7184', 'important');
            button.style.setProperty('color', '#ffffff', 'important');
            button.style.setProperty('opacity', '1', 'important');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyDomainMongerPaymentMethodCleanup);
    } else {
        applyDomainMongerPaymentMethodCleanup();
    }

    window.setTimeout(applyDomainMongerPaymentMethodCleanup, 250);
})();
</script>
HTML;
});
