<?php
/**
 * DomainMonger Patch 576
 * Payment Methods action button colors, no grey-to-navy flash for Set as Default.
 *
 * Replaces/cleans up Patch 575 in the same hook file.
 *
 * Scope:
 * - Only elements inside #payMethodList.
 * - All small row action buttons default to DomainMonger navy immediately via CSS.
 * - Delete buttons are red via specific CSS where identifiable, plus a small JS
 *   fallback for WHMCS markup where Delete is only identifiable by visible text.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, function (array $vars) {
    return <<<'HTML'
<style id="domainmonger-payment-methods-button-style-576">
/* DM Patch 576: Payment Methods row action buttons only. */
#payMethodList a.btn.btn-sm,
#payMethodList button.btn.btn-sm,
#payMethodList input.btn.btn-sm {
    background: #163a5f !important;
    background-color: #163a5f !important;
    background-image: none !important;
    border-color: #163a5f !important;
    color: #ffffff !important;
    text-shadow: none !important;
    box-shadow: none !important;
    opacity: 1 !important;
}

#payMethodList a.btn.btn-sm:hover,
#payMethodList a.btn.btn-sm:focus,
#payMethodList button.btn.btn-sm:hover,
#payMethodList button.btn.btn-sm:focus,
#payMethodList input.btn.btn-sm:hover,
#payMethodList input.btn.btn-sm:focus {
    background: #214e7a !important;
    background-color: #214e7a !important;
    background-image: none !important;
    border-color: #214e7a !important;
    color: #ffffff !important;
    opacity: 1 !important;
}

#payMethodList a.btn.btn-sm.btn-delete,
#payMethodList a.btn.btn-sm.btn-danger,
#payMethodList a.btn.btn-sm.dm-payment-method-delete,
#payMethodList a.btn.btn-sm[href*="delete"],
#payMethodList button.btn.btn-sm.btn-delete,
#payMethodList button.btn.btn-sm.btn-danger,
#payMethodList button.btn.btn-sm.dm-payment-method-delete,
#payMethodList input.btn.btn-sm.btn-delete,
#payMethodList input.btn.btn-sm.btn-danger,
#payMethodList input.btn.btn-sm.dm-payment-method-delete {
    background: #b94a48 !important;
    background-color: #b94a48 !important;
    background-image: none !important;
    border-color: #b94a48 !important;
    color: #ffffff !important;
    text-shadow: none !important;
    box-shadow: none !important;
    opacity: 1 !important;
}

#payMethodList a.btn.btn-sm.btn-delete:hover,
#payMethodList a.btn.btn-sm.btn-delete:focus,
#payMethodList a.btn.btn-sm.btn-danger:hover,
#payMethodList a.btn.btn-sm.btn-danger:focus,
#payMethodList a.btn.btn-sm.dm-payment-method-delete:hover,
#payMethodList a.btn.btn-sm.dm-payment-method-delete:focus,
#payMethodList a.btn.btn-sm[href*="delete"]:hover,
#payMethodList a.btn.btn-sm[href*="delete"]:focus,
#payMethodList button.btn.btn-sm.btn-delete:hover,
#payMethodList button.btn.btn-sm.btn-delete:focus,
#payMethodList button.btn.btn-sm.btn-danger:hover,
#payMethodList button.btn.btn-sm.btn-danger:focus,
#payMethodList button.btn.btn-sm.dm-payment-method-delete:hover,
#payMethodList button.btn.btn-sm.dm-payment-method-delete:focus,
#payMethodList input.btn.btn-sm.btn-delete:hover,
#payMethodList input.btn.btn-sm.btn-delete:focus,
#payMethodList input.btn.btn-sm.btn-danger:hover,
#payMethodList input.btn.btn-sm.btn-danger:focus,
#payMethodList input.btn.btn-sm.dm-payment-method-delete:hover,
#payMethodList input.btn.btn-sm.dm-payment-method-delete:focus {
    background: #a33d3b !important;
    background-color: #a33d3b !important;
    background-image: none !important;
    border-color: #a33d3b !important;
    color: #ffffff !important;
    opacity: 1 !important;
}

#payMethodList a.btn.btn-sm i,
#payMethodList button.btn.btn-sm i {
    color: #ffffff !important;
}
</style>
<script id="domainmonger-payment-methods-button-style-576-js">
(function () {
    'use strict';

    function paintButton(button, bg, hoverBg) {
        button.style.setProperty('background', bg, 'important');
        button.style.setProperty('background-color', bg, 'important');
        button.style.setProperty('background-image', 'none', 'important');
        button.style.setProperty('border-color', bg, 'important');
        button.style.setProperty('color', '#ffffff', 'important');
        button.style.setProperty('text-shadow', 'none', 'important');
        button.style.setProperty('box-shadow', 'none', 'important');
        button.style.setProperty('opacity', '1', 'important');
        if (hoverBg) {
            button.setAttribute('data-dm-hover-bg', hoverBg);
        }

        var icons = button.querySelectorAll ? button.querySelectorAll('i') : [];
        Array.prototype.forEach.call(icons, function (icon) {
            icon.style.setProperty('color', '#ffffff', 'important');
        });
    }

    function stylePaymentMethodButtons() {
        var table = document.getElementById('payMethodList');
        if (!table || !table.querySelectorAll) {
            return;
        }

        var buttons = table.querySelectorAll('a.btn, button.btn, input.btn');
        Array.prototype.forEach.call(buttons, function (button) {
            var text = ((button.textContent || button.value || '') + '').replace(/\s+/g, ' ').trim().toLowerCase();
            var href = ((button.getAttribute && button.getAttribute('href')) || '').toLowerCase();

            if (
                text === 'delete'
                || button.classList.contains('btn-delete')
                || button.classList.contains('btn-danger')
                || href.indexOf('delete') !== -1
            ) {
                button.classList.add('dm-payment-method-delete');
                paintButton(button, '#b94a48', '#a33d3b');
                return;
            }

            if (
                text === 'set as default'
                || text === 'edit'
                || button.classList.contains('btn-set-default')
                || button.getAttribute('data-role') === 'edit-payment-method'
            ) {
                paintButton(button, '#163a5f', '#214e7a');
            }
        });
    }

    function start() {
        stylePaymentMethodButtons();

        if (window.MutationObserver) {
            var observer = new MutationObserver(stylePaymentMethodButtons);
            observer.observe(document.documentElement, { childList: true, subtree: true });
        }

        window.setTimeout(stylePaymentMethodButtons, 100);
        window.setTimeout(stylePaymentMethodButtons, 500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>
HTML;
});
