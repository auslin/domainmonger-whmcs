<?php
/**
 * DomainMonger Patches 1452-1455
 *
 * Hides stored card-type payment methods while PayPal is selected, while
 * preserving genuine linked PayPal accounts and the normal Credit Card list.
 *
 * Patch 1455 removes the earlier timers, mutation observer, input disabling,
 * and card-panel manipulation. Those behaviors overlapped WHMCS's native
 * full-credit and gateway animations. This hook now changes only individual
 * saved-method rows after the native gateway selection event has settled.
 *
 * This does not delete or alter stored payment methods, gateway settings,
 * payment processing, account credit, cart totals, or database data.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_checkout_paypal_saved_card_target_1455')) {
    function dm_checkout_paypal_saved_card_target_1455(): bool
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower(trim((string) ($_REQUEST['a'] ?? '')));

        return $script === 'cart.php' && $action === 'checkout';
    }
}

add_hook('ClientAreaFooterOutput', 10090, static function (): string {
    if (!dm_checkout_paypal_saved_card_target_1455()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-checkout-paypal-hide-saved-cards-style-1455">
#order-standard_cart #creditCardInputFields .dm-paypal-card-method-hidden-1455 {
    display: none !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer.dm-paypal-no-linked-account-1455 {
    display: none !important;
}
</style>
<script id="dm-checkout-paypal-hide-saved-cards-1455">
(function ($) {
    'use strict';

    var hiddenClass = 'dm-paypal-card-method-hidden-1455';
    var emptyClass = 'dm-paypal-no-linked-account-1455';
    var cardBrandPattern = /\b(?:american\s*express|amex|master\s*card|mastercard|visa|discover|diners|jcb|unionpay|maestro)\b/i;
    var queued = false;

    function selectedGateway() {
        return $('input[name="paymentmethod"]:checked').first();
    }

    function paypalIsSelected() {
        var $gateway = selectedGateway();
        var value = String($gateway.val() || '').toLowerCase();
        var labelText = String($gateway.closest('label').text() || '').toLowerCase();

        return $gateway.length > 0
            && (value.indexOf('paypal') !== -1 || labelText.indexOf('paypal') !== -1);
    }

    function groupElements($container, $input) {
        var id = String($input.val() || '');
        var $elements = $();

        if (id) {
            $elements = $container.find('[data-paymethod-id="' + id.replace(/"/g, '\\"') + '"]');
        }

        if (!$elements.length) {
            $elements = $input.closest('.paymethod-info, label, li, .row');
        }

        return $elements;
    }

    function methodLooksLikeCard($elements, $input) {
        var text = String($elements.text() || '');
        var classes = '';
        var type = String($input.data('payment-type') || '').toLowerCase();

        $elements.each(function () {
            classes += ' ' + String(this.className || '');
            $(this).find('[class]').each(function () {
                classes += ' ' + String(this.className || '');
            });
        });
        classes = classes.toLowerCase();

        return type === 'creditcard'
            || cardBrandPattern.test(text)
            || classes.indexOf('fa-credit-card') !== -1
            || classes.indexOf('fa-cc-') !== -1;
    }

    function updateIcheck($input) {
        if ($.fn.iCheck && $input.data('iCheck')) {
            $input.iCheck('update');
        }
    }

    function setCheckedDirectly($input) {
        var $all = $('input[name="ccinfo"]');

        if (!$input.length) {
            return;
        }

        $all.not($input).prop('checked', false);
        $input.prop('checked', true);
        updateIcheck($all);
    }

    function firstVisibleSavedMethod($container) {
        var $result = $();

        $container.find('input.existing-card').each(function () {
            var $input = $(this);
            var $elements = groupElements($container, $input);
            var hiddenByPatch = $elements.filter('.' + hiddenClass).length > 0;
            var hiddenByNative = $elements.filter(function () {
                return this.style && this.style.display === 'none';
            }).length === $elements.length;

            if (!hiddenByPatch && !hiddenByNative && !this.disabled) {
                $result = $input;
                return false;
            }
        });

        return $result;
    }

    function restoreRows($container) {
        $container.find('.' + hiddenClass).removeClass(hiddenClass).removeAttr('aria-hidden');
        $container.removeClass(emptyClass).removeAttr('aria-hidden');
    }

    function synchronizeRows() {
        var $container = $('#existingCardsContainer');
        var selectedCardWasHidden = false;
        var $visibleMethod;
        var $newMethod;

        queued = false;

        if (!$container.length) {
            return;
        }

        restoreRows($container);

        if (!paypalIsSelected()) {
            return;
        }

        $container.find('input.existing-card').each(function () {
            var $input = $(this);
            var $elements = groupElements($container, $input);
            var hide = methodLooksLikeCard($elements, $input);

            if (!hide) {
                return;
            }

            if (this.checked) {
                selectedCardWasHidden = true;
                this.checked = false;
                updateIcheck($input);
            }

            $elements.addClass(hiddenClass).attr('aria-hidden', 'true');
        });

        $visibleMethod = firstVisibleSavedMethod($container);
        $container.toggleClass(emptyClass, !$visibleMethod.length);

        if (!$visibleMethod.length) {
            $container.attr('aria-hidden', 'true');
        }

        if (selectedCardWasHidden) {
            if ($visibleMethod.length) {
                setCheckedDirectly($visibleMethod);
            } else {
                $newMethod = $('#new:not(:disabled)').first();
                setCheckedDirectly($newMethod);
            }
        }
    }

    function queueSynchronization() {
        if (queued) {
            return;
        }

        queued = true;
        window.setTimeout(synchronizeRows, 0);
    }

    $(function () {
        queueSynchronization();

        $(document).off('.dmPaypalCards1455').on(
            'ifChecked.dmPaypalCards1455 change.dmPaypalCards1455',
            'input[name="paymentmethod"]',
            queueSynchronization
        ).on(
            'ajaxComplete.dmPaypalCards1455',
            queueSynchronization
        );
    });
})(jQuery);
</script>
HTML;
});
