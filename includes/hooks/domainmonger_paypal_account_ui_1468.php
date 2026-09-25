<?php
/**
 * DomainMonger Patch 1472
 *
 * Keeps the confirmed Patch 1469 removal of the empty single-choice PayPal
 * wrapper. Removes the two extra PayPal account explanation lines and leaves
 * only the heading "Continue with PayPal" without a trailing period.
 *
 * Checkout-page UI only. No PayPal tokens, saved payment methods, gateway
 * settings, orders, transactions, prices, credits, discounts, or database data
 * are changed.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_paypal_account_ui_target_1469')) {
    function dm_paypal_account_ui_target_1469(): bool
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower(trim((string) ($_REQUEST['a'] ?? '')));

        return $script === 'cart.php' && $action === 'checkout';
    }
}

add_hook('ClientAreaFooterOutput', 10110, static function (): string {
    if (!dm_paypal_account_ui_target_1469()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-paypal-account-ui-style-1472">
#order-standard_cart #creditCardInputFields ul.dm-paypal-single-choice-list-1472 {
    display: none !important;
}
#order-standard_cart #creditCardInputFields .dm-paypal-account-note-1472 {
    margin: 8px 0 10px;
    text-align: center;
    color: #163a5f;
    font-weight: 600;
}
#order-standard_cart #creditCardInputFields .dm-paypal-hidden-instruction-1472 {
    display: none !important;
}
</style>
<script id="dm-paypal-account-ui-1472">
(function ($) {
    'use strict';

    var observer = null;
    var scheduled = false;

    var removableInstructions = [
        'To complete payment with PayPal, choose an existing linked PayPal account or link a new account.',
        'Continue with PayPal below using the account shown in the PayPal window.',
        'PayPal will use the account shown in the PayPal window.'
    ];

    function selectedGatewayIsPayPal() {
        var input = document.querySelector('input[name="paymentmethod"]:checked');
        var value;
        var label;

        if (!input) {
            return false;
        }

        value = String(input.value || '').toLowerCase();
        label = input.closest('label');
        label = label ? String(label.textContent || '').toLowerCase() : '';

        return value.indexOf('paypal') !== -1 || label.indexOf('paypal') !== -1;
    }

    function methodRow(input) {
        if (!input) {
            return null;
        }

        var id = String(input.value || '');
        var container = document.getElementById('existingCardsContainer');
        var row = null;

        if (container && id) {
            try {
                row = container.querySelector('[data-paymethod-id="' + CSS.escape(id) + '"]');
            } catch (ignore) {
                row = null;
            }
        }

        return row || input.closest('.paymethod-info, label, li, .row');
    }

    function isActuallyVisible(element) {
        if (!element) {
            return false;
        }

        var style = window.getComputedStyle(element);
        return style.display !== 'none' && style.visibility !== 'hidden' && !element.hidden;
    }

    function hasVisibleLinkedPayPalMethod() {
        var inputs = document.querySelectorAll('#existingCardsContainer input.existing-card');
        var found = false;

        Array.prototype.forEach.call(inputs, function (input) {
            var gateway;
            var type;
            var row;

            if (found || input.disabled) {
                return;
            }

            gateway = String(input.getAttribute('data-payment-gateway') || '').toLowerCase();
            type = String(input.getAttribute('data-payment-type') || '').toLowerCase();
            row = methodRow(input);

            if ((gateway.indexOf('paypal') !== -1 || type.indexOf('paypal') !== -1)
                && isActuallyVisible(row)) {
                found = true;
            }
        });

        return found;
    }

    function removeExactInstruction(root, text) {
        if (!root || !document.createTreeWalker) {
            return;
        }

        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
        var node;
        var parent;
        var value;

        while ((node = walker.nextNode())) {
            value = String(node.nodeValue || '');
            if (value.trim() !== text) {
                continue;
            }

            parent = node.parentElement;
            if (parent && String(parent.textContent || '').trim() === text) {
                parent.classList.add('dm-paypal-hidden-instruction-1472');
                parent.setAttribute('aria-hidden', 'true');
            } else {
                node.nodeValue = value.replace(text, '');
            }
        }
    }

    function removeExtraInstructions(panel) {
        removableInstructions.forEach(function (text) {
            removeExactInstruction(panel, text);
        });
    }

    function updateAccountNote(panel, list, linkedMethodExists) {
        var note = panel.querySelector('.dm-paypal-account-note-1472');

        if (linkedMethodExists) {
            if (note) {
                note.remove();
            }
            return;
        }

        if (!note) {
            note = document.createElement('div');
            note.className = 'dm-paypal-account-note-1472';

            if (list && list.parentNode) {
                list.parentNode.insertBefore(note, list);
            } else {
                panel.appendChild(note);
            }
        }

        if (String(note.textContent || '').trim() !== 'Continue with PayPal') {
            note.textContent = 'Continue with PayPal';
        }
    }

    function updateNewChoiceList(panel, linkedMethodExists) {
        var input = panel.querySelector('input[name="ccinfo"][value="new"]');
        var row;
        var list;

        if (!input) {
            return;
        }

        row = input.closest('li') || input.closest('.paymethod-info, label, .row');
        list = row && row.closest('ul');

        /* Remove markers from the superseded Patch 1468/1470 implementations. */
        if (row) {
            row.classList.remove('dm-paypal-single-choice-row-1468');
            row.removeAttribute('aria-hidden');
        }
        if (list) {
            list.classList.remove('dm-paypal-single-choice-list-1470');
        }

        panel.querySelectorAll('.dm-paypal-different-account-note-1470').forEach(function (oldNote) {
            oldNote.remove();
        });

        if (!linkedMethodExists) {
            input.checked = true;
            if ($.fn.iCheck && $(input).data('iCheck')) {
                $(input).iCheck('update');
            }

            if (list) {
                list.classList.add('dm-paypal-single-choice-list-1472');
                list.setAttribute('aria-hidden', 'true');
            }

            updateAccountNote(panel, list, false);
            return;
        }

        if (list) {
            list.classList.remove('dm-paypal-single-choice-list-1472');
            list.removeAttribute('aria-hidden');
        }

        updateAccountNote(panel, list, true);
    }

    function synchronizePanel() {
        scheduled = false;

        var panel = document.getElementById('creditCardInputFields');
        var linkedMethodExists;

        if (!panel) {
            return;
        }

        if (!selectedGatewayIsPayPal()) {
            panel.querySelectorAll('.dm-paypal-account-note-1472, .dm-paypal-different-account-note-1470').forEach(function (note) {
                note.remove();
            });
            return;
        }

        linkedMethodExists = hasVisibleLinkedPayPalMethod();
        updateNewChoiceList(panel, linkedMethodExists);

        if (!linkedMethodExists) {
            removeExtraInstructions(panel);
        }
    }

    function scheduleSynchronization() {
        if (scheduled) {
            return;
        }

        scheduled = true;
        window.setTimeout(synchronizePanel, 0);
    }

    function startObserver() {
        var panel = document.getElementById('creditCardInputFields');

        if (!panel || observer) {
            return;
        }

        observer = new MutationObserver(scheduleSynchronization);
        observer.observe(panel, {
            childList: true,
            subtree: true,
            characterData: true,
            attributes: true,
            attributeFilter: ['class', 'style', 'checked', 'disabled']
        });
    }

    $(function () {
        startObserver();
        scheduleSynchronization();

        $(document)
            .off('.dmPaypalAccountUi1472')
            .on(
                'ifChecked.dmPaypalAccountUi1472 change.dmPaypalAccountUi1472',
                'input[name="paymentmethod"], input[name="ccinfo"]',
                scheduleSynchronization
            )
            .on('ajaxComplete.dmPaypalAccountUi1472', function () {
                startObserver();
                scheduleSynchronization();
            });
    });
})(jQuery);
</script>
HTML;
});
