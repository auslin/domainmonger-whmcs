<?php
/**
 * DomainMonger Patch 1465
 *
 * Replaces the failed Patch 1464 browser matcher.
 *
 * PayPal's detailed API reason (CANNOT_BE_ZERO_OR_NEGATIVE) is written to the
 * WHMCS Module Log, but the checkout browser receives only the generic
 * "PayPal Create Order Error: (UNPROCESSABLE_ENTITY)" message. Patch 1464
 * therefore could not match the visible error.
 *
 * This hook intercepts that actual visible PayPal Create Order error and shows
 * customer-friendly guidance explaining that an account discount or credit may
 * have reduced the amount sent to PayPal to $0.00. It does not change totals,
 * discounts, gateways, orders, transactions, stored payment methods, or data.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_paypal_checkout_error_target_1465')) {
    function dm_paypal_checkout_error_target_1465(): bool
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower(trim((string) ($_REQUEST['a'] ?? '')));

        return $script === 'cart.php' && $action === 'checkout';
    }
}

add_hook('ClientAreaFooterOutput', 10080, static function (): string {
    if (!dm_paypal_checkout_error_target_1465()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-paypal-zero-error-feedback-1465">
(function () {
    'use strict';

    var friendlyMessage = 'PayPal could not start this payment because the amount sent to PayPal is invalid. This can happen when an account discount or credit reduces the payable amount to $0.00 even though the checkout page shows a different total. No payment was taken. Please review the account discount or credit, choose another payment method, or contact support.';
    var processing = false;

    function normalize(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function paypalIsSelected() {
        var input = document.querySelector('input[name="paymentmethod"]:checked');
        var value;
        var label;
        var text;

        if (!input) {
            return false;
        }

        value = String(input.value || '').toLowerCase();
        label = input.closest ? input.closest('label') : null;
        text = label ? String(label.textContent || '').toLowerCase() : '';

        return value.indexOf('paypal') !== -1 || text.indexOf('paypal') !== -1;
    }

    function isVisiblePaypalCreateOrderError(value) {
        var text = normalize(value).toLowerCase();
        var namesPaypal = text.indexOf('paypal create order error') !== -1
            || text.indexOf('paypal') !== -1;
        var isUnprocessable = text.indexOf('unprocessable_entity') !== -1
            || text.indexOf('unprocessable entity') !== -1;

        return namesPaypal && isUnprocessable && (paypalIsSelected() || namesPaypal);
    }

    function restoreCheckoutButton() {
        var button = document.getElementById('btnCompleteOrder');
        var icon;

        if (!button) {
            return;
        }

        button.disabled = false;
        button.classList.remove('disabled');
        button.removeAttribute('aria-disabled');

        icon = button.querySelector('i.fas, i.far, i.fal, i.fab');
        if (icon && icon.classList.contains('fa-spinner')) {
            icon.removeAttribute('class');
            icon.className = 'fas fa-arrow-circle-right';
        }
    }

    function displayFriendlyMessage(container) {
        var box = container;

        if (processing) {
            return;
        }

        processing = true;

        if (!box || !box.nodeType) {
            box = document.querySelector('.gateway-errors');
        }

        if (!box) {
            box = document.createElement('div');
            box.className = 'alert alert-danger text-center gateway-errors';

            var anchor = document.getElementById('paymentGatewayInput')
                || document.getElementById('paymentGatewaysContainer')
                || document.getElementById('btnCompleteOrder');

            if (anchor && anchor.parentNode) {
                anchor.parentNode.insertBefore(box, anchor);
            }
        }

        if (box) {
            box.textContent = friendlyMessage;
            box.setAttribute('role', 'alert');
            box.setAttribute('aria-live', 'assertive');
            box.classList.remove('w-hidden', 'd-none', 'hidden');
            box.style.display = 'block';
        }

        restoreCheckoutButton();
        window.setTimeout(restoreCheckoutButton, 50);
        window.setTimeout(function () {
            processing = false;
        }, 0);
    }

    function inspectElement(element) {
        if (!element) {
            return;
        }

        if (element.nodeType === 3) {
            if (isVisiblePaypalCreateOrderError(element.nodeValue)) {
                displayFriendlyMessage(element.parentElement);
            }
            return;
        }

        if (element.nodeType !== 1 && element.nodeType !== 9 && element.nodeType !== 11) {
            return;
        }

        if (isVisiblePaypalCreateOrderError(element.textContent)) {
            if (element.matches && element.matches('.gateway-errors, .alert-danger, [role="alert"], .bootbox-body, .swal2-html-container')) {
                displayFriendlyMessage(element);
            } else {
                displayFriendlyMessage(document.querySelector('.gateway-errors'));
            }
        }
    }

    function wrapShowCheckoutError() {
        var original;

        if (window.__dmPaypalShowCheckoutError1465 || typeof window.showCheckoutError !== 'function') {
            return;
        }

        original = window.showCheckoutError;
        window.showCheckoutError = function (message, container) {
            if (isVisiblePaypalCreateOrderError(message)) {
                message = friendlyMessage;
            }

            var result = original.call(this, message, container);

            if (message === friendlyMessage) {
                window.setTimeout(function () {
                    var target = container && container.jquery ? container[0] : container;
                    displayFriendlyMessage(target || document.querySelector('.gateway-errors'));
                }, 0);
            }

            return result;
        };

        window.__dmPaypalShowCheckoutError1465 = true;
    }

    function wrapAlert() {
        var original;

        if (window.__dmPaypalAlert1465) {
            return;
        }

        original = window.alert;
        window.alert = function (message) {
            if (isVisiblePaypalCreateOrderError(message)) {
                displayFriendlyMessage(document.querySelector('.gateway-errors'));
                return original.call(window, friendlyMessage);
            }

            return original.apply(window, arguments);
        };

        window.__dmPaypalAlert1465 = true;
    }

    function initialize() {
        var observer;

        wrapShowCheckoutError();
        wrapAlert();
        inspectElement(document);

        if (!window.MutationObserver || !document.body) {
            return;
        }

        observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'characterData') {
                    inspectElement(mutation.target);
                }

                Array.prototype.forEach.call(mutation.addedNodes || [], inspectElement);
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
}());
</script>
HTML;
});
