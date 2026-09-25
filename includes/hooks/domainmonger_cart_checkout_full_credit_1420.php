<?php
/**
 * DomainMonger Patches 1420, 1424, 1455, 1462, and 1463
 *
 * Full-account-credit checkout state handling.
 *
 * Patch 1455 replaced the earlier restore path. Patch 1462 additionally fixed
 * the zero-balance PayPal path: WHMCS can mount the PayPal Smart Button inside
 * #paymentGatewayInput even while the gateway radios and card panel are hidden.
 * That allowed a $0.00 Create Order request when account credit covered the cart.
 *
 * Patch 1463 fixes the reverse transition. PayPal Commerce can retain a Smart
 * Button instance initialized while full credit was active. Simply revealing
 * that instance after "Do not apply credit" can reuse the stale $0.00 order
 * amount. The checkout now performs a one-time same-page refresh and applies
 * the no-credit radio before WHMCS initializes payment gateways.
 *
 * The current implementation:
 * - remembers the customer's gateway before full credit hides payment fields;
 * - keeps a non-card gateway posted for WHMCS's zero-balance submission flow;
 * - disables only fields that this hook disabled;
 * - restores the remembered gateway once and lets the native order-form
 *   gateway handler rebuild its fields;
 * - clears any stale animation height/overflow after the native transition;
 * - hides and disables the dynamic gateway-input area while full credit is active;
 * - restores the dynamic gateway area only after external payment is required;
 * - refreshes once when leaving a full-credit state so remote gateways are
 *   initialized from the active no-credit form state.
 *
 * Limited to cart.php?a=checkout. It does not alter credit balances, totals,
 * orders, payment methods, gateway settings, or database data.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_cart_checkout_is_target_1424')) {
    function dm_cart_checkout_is_target_1424(): bool
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower(trim((string) ($_REQUEST['a'] ?? '')));

        return $script === 'cart.php' && $action === 'checkout';
    }
}

add_hook('ClientAreaHeaderOutput', 1, static function (): string {
    if (!dm_cart_checkout_is_target_1424()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-cart-checkout-credit-bootstrap-1463">
(function () {
    'use strict';

    var forceSkipKey = 'dmCheckoutForceSkipCredit1463';
    var gatewayKey = 'dmCheckoutGateway1463';
    var termsKey = 'dmCheckoutTerms1463';

    function storageGet(key) {
        try {
            return window.sessionStorage.getItem(key);
        } catch (e) {
            return null;
        }
    }

    function storageRemove(key) {
        try {
            window.sessionStorage.removeItem(key);
        } catch (e) {
            // Storage can be unavailable in restricted browser modes.
        }
    }

    function applyNoCreditBootstrap() {
        var container;
        var useCredit;
        var skipCredit;
        var gatewayValue;
        var termsValue;
        var gateways;
        var terms;
        var index;

        if (storageGet(forceSkipKey) !== '1') {
            return false;
        }

        container = document.getElementById('applyCreditContainer');
        useCredit = document.getElementById('useCreditOnCheckout');
        skipCredit = document.getElementById('skipCreditOnCheckout');

        if (!container || !useCredit || !skipCredit) {
            return false;
        }

        /*
         * WHMCS reads this data value during its order-form ready handler. Set
         * it before that handler runs so it does not re-apply full credit.
         */
        container.setAttribute('data-apply-credit', '0');
        useCredit.checked = false;
        useCredit.removeAttribute('checked');
        skipCredit.checked = true;
        skipCredit.setAttribute('checked', 'checked');

        gatewayValue = storageGet(gatewayKey);
        if (gatewayValue) {
            gateways = document.querySelectorAll('input[name="paymentmethod"]');
            for (index = 0; index < gateways.length; index += 1) {
                gateways[index].checked = gateways[index].value === gatewayValue;
                if (gateways[index].checked) {
                    gateways[index].setAttribute('checked', 'checked');
                } else {
                    gateways[index].removeAttribute('checked');
                }
            }
        }

        termsValue = storageGet(termsKey);
        terms = document.getElementById('accepttos');
        if (terms && termsValue !== null) {
            terms.checked = termsValue === '1';
        }

        window.dmCheckoutNoCreditBootstrapped1463 = true;
        storageRemove(forceSkipKey);
        storageRemove(gatewayKey);
        storageRemove(termsKey);
        return true;
    }

    if (!applyNoCreditBootstrap()) {
        document.addEventListener('DOMContentLoaded', applyNoCreditBootstrap, { once: true });
    }
})(window);
</script>
HTML;
});

add_hook('ClientAreaFooterOutput', 9999, static function (): string {
    if (!dm_cart_checkout_is_target_1424()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-cart-checkout-full-credit-1462-style">
#frmCheckout.dm-full-credit-payment-hidden-1462 #paymentGatewaysContainer,
#frmCheckout.dm-full-credit-payment-hidden-1462 #paymentGatewayInput,
#frmCheckout.dm-full-credit-payment-hidden-1462 #creditCardInputFields {
    display: none !important;
}
#frmCheckout.dm-full-credit-payment-hidden-1462 #paymentGatewayInput,
#frmCheckout.dm-full-credit-payment-hidden-1462 #paymentGatewayInput * {
    pointer-events: none !important;
}
</style>
<script id="dm-cart-checkout-full-credit-1462">
(function ($) {
    'use strict';

    var formSelector = '#frmCheckout';
    var priorGatewayKey = 'dmPriorGateway1455';
    var disabledMarker = 'dmFullCreditDisabled1455';
    var transitionTimer = null;
    var finalTimer = null;
    var applyingState = false;
    var fullCreditClass = 'dm-full-credit-payment-hidden-1462';
    var forceSkipKey = 'dmCheckoutForceSkipCredit1463';
    var gatewayStorageKey = 'dmCheckoutGateway1463';
    var termsStorageKey = 'dmCheckoutTerms1463';
    var fullCreditWasActive = false;
    var reloadStarted = false;

    function $form() {
        return $(formSelector);
    }


    function storageSet(key, value) {
        try {
            window.sessionStorage.setItem(key, value);
        } catch (e) {
            // Continue without persistence if storage is unavailable.
        }
    }

    function reloadForNoCreditGatewayInitialization() {
        var $gateway;
        var terms;

        if (reloadStarted) {
            return;
        }

        reloadStarted = true;
        $gateway = selectedGateway();
        terms = document.getElementById('accepttos');

        storageSet(forceSkipKey, '1');
        if ($gateway.length && String($gateway.val() || '')) {
            storageSet(gatewayStorageKey, String($gateway.val()));
        }
        if (terms) {
            storageSet(termsStorageKey, terms.checked ? '1' : '0');
        }

        $form().addClass(fullCreditClass);
        $('#paymentGatewaysContainer, #paymentGatewayInput, #creditCardInputFields')
            .stop(true, true)
            .hide()
            .attr('aria-hidden', 'true');

        window.location.replace(window.location.href);
    }

    function fullCreditIsSelected() {
        var $useCredit = $('#useCreditOnCheckout');
        var $fullCreditMessage = $('#spanFullCredit');

        return $useCredit.length > 0
            && $useCredit.prop('checked')
            && $fullCreditMessage.length > 0
            && !$fullCreditMessage.hasClass('w-hidden')
            && $fullCreditMessage.is(':visible');
    }

    function selectedGateway() {
        return $('input[name="paymentmethod"]:checked').first();
    }

    function firstUsableNonCardGateway() {
        return $('input[name="paymentmethod"]')
            .not('.is-credit-card')
            .not(':disabled')
            .first();
    }

    function updateIcheck($inputs) {
        if (!$.fn.iCheck) {
            return;
        }

        $inputs.each(function () {
            var $input = $(this);
            if ($input.data('iCheck')) {
                $input.iCheck('update');
            }
        });
    }

    function setGatewayCheckedDirectly($gateway) {
        var $all = $('input[name="paymentmethod"]');

        if (!$gateway.length) {
            return;
        }

        $all.not($gateway).prop('checked', false);
        $gateway.prop('checked', true);
        updateIcheck($all);
    }

    function rememberCustomerGateway() {
        var $checkoutForm = $form();
        var $gateway = selectedGateway();
        var value;

        if (!$checkoutForm.length || !$gateway.length) {
            return;
        }

        value = String($gateway.val() || '');
        if (value) {
            $checkoutForm.data(priorGatewayKey, value);
        }
    }

    function rememberedGateway() {
        var value = String($form().data(priorGatewayKey) || '');
        var $gateway = $();

        if (value) {
            $gateway = $('input[name="paymentmethod"]').filter(function () {
                return String(this.value || '') === value && !this.disabled;
            }).first();
        }

        if (!$gateway.length) {
            $gateway = selectedGateway().not(':disabled');
        }

        if (!$gateway.length) {
            $gateway = $('input[name="paymentmethod"]:not(:disabled)').first();
        }

        return $gateway;
    }

    function setWhmcsSelectedGateway($gateway) {
        var moduleName = String($gateway.val() || '');

        if (
            moduleName
            && window.WHMCS
            && WHMCS.payment
            && WHMCS.payment.event
        ) {
            WHMCS.payment.event.previouslySelected = {
                module: moduleName,
                formElement: $gateway
            };
        }
    }

    function disableCardFieldsForFullCredit() {
        $('#creditCardInputFields').find(':input').each(function () {
            if (!this.disabled) {
                this.dataset[disabledMarker] = '1';
                this.disabled = true;
            }
        });
    }

    function restoreFieldsDisabledByThisHook() {
        $('#creditCardInputFields').find(':input').each(function () {
            if (this.dataset[disabledMarker] === '1') {
                this.disabled = false;
                delete this.dataset[disabledMarker];
            }
        });
    }

    function clearCardValidationErrors() {
        var $cardFields = $('#creditCardInputFields');

        $cardFields.find('.form-group').removeClass('has-error');
        $cardFields.find('.field-error-msg').hide();
        $('.gateway-errors').addClass('w-hidden').hide().empty();
    }

    function hidePaymentControlsForFullCredit() {
        var $checkoutForm = $form();
        var $gatewayContainer = $('#paymentGatewaysContainer');
        var $gatewayInput = $('#paymentGatewayInput');
        var $cardFields = $('#creditCardInputFields');

        $checkoutForm.addClass(fullCreditClass);

        $gatewayContainer.stop(true, true).css({
            display: 'none',
            height: '',
            overflow: ''
        }).attr('aria-hidden', 'true');

        /*
         * PayPal Commerce renders its Smart Button here, outside the radio row.
         * Hiding only #paymentGatewaysContainer is not sufficient: the iframe can
         * still launch a Create Order request even when no external payment is due.
         */
        $gatewayInput.stop(true, true).css({
            display: 'none',
            height: '',
            overflow: '',
            pointerEvents: 'none'
        }).attr('aria-hidden', 'true');

        $cardFields.stop(true, true).css({
            display: 'none',
            height: '',
            overflow: ''
        }).attr('aria-hidden', 'true');
    }

    function applyFullCreditState() {
        var $nonCardGateway;

        if (!fullCreditIsSelected() || applyingState) {
            return false;
        }

        applyingState = true;
        rememberCustomerGateway();
        $nonCardGateway = firstUsableNonCardGateway();

        if ($nonCardGateway.length) {
            setGatewayCheckedDirectly($nonCardGateway);
            setWhmcsSelectedGateway($nonCardGateway);
        }

        hidePaymentControlsForFullCredit();
        disableCardFieldsForFullCredit();
        clearCardValidationErrors();
        applyingState = false;
        return true;
    }

    function finalizeNormalPaymentState($gateway) {
        var $checkoutForm = $form();
        var $gatewayContainer = $('#paymentGatewaysContainer');
        var $gatewayInput = $('#paymentGatewayInput');
        var $cardFields = $('#creditCardInputFields');
        var gatewayUsesCardPanel = $gateway.hasClass('is-credit-card');

        $checkoutForm.removeClass(fullCreditClass);

        $gatewayContainer.stop(true, true).css({
            display: 'block',
            height: '',
            overflow: ''
        }).removeAttr('aria-hidden');

        $gatewayInput.stop(true, true).css({
            display: '',
            height: '',
            overflow: '',
            pointerEvents: ''
        }).removeAttr('aria-hidden');

        /*
         * Remote-credit-card gateways such as PayPal Commerce can also use the
         * native card panel for vaulted methods, so the native is-credit-card
         * flag remains authoritative here.
         */
        $cardFields.stop(true, true).css({
            display: gatewayUsesCardPanel ? 'block' : 'none',
            height: '',
            overflow: ''
        }).attr('aria-hidden', gatewayUsesCardPanel ? 'false' : 'true');
    }

    function restoreNormalPaymentState() {
        var $gateway;

        if (fullCreditIsSelected() || applyingState) {
            return;
        }

        applyingState = true;
        window.clearTimeout(finalTimer);
        $form().removeClass(fullCreditClass);
        $('#paymentGatewayInput').stop(true, true).css({
            display: '',
            height: '',
            overflow: '',
            pointerEvents: ''
        }).removeAttr('aria-hidden');
        restoreFieldsDisabledByThisHook();

        $gateway = rememberedGateway();
        if ($gateway.length) {
            setGatewayCheckedDirectly($gateway);

            /* Remove an unfinished earlier slide before asking the native
             * gateway handler to rebuild the correct panel exactly once. */
            $('#creditCardInputFields').stop(true, true).css({
                height: '',
                overflow: ''
            });
            $('#paymentGatewaysContainer').stop(true, true).css({
                display: 'block',
                height: '',
                overflow: ''
            });

            $gateway.trigger('ifChecked');
            setWhmcsSelectedGateway($gateway);

            finalTimer = window.setTimeout(function () {
                finalizeNormalPaymentState($gateway);
            }, 450);
        } else {
            $('#paymentGatewaysContainer').stop(true, true).css({
                display: 'block',
                height: '',
                overflow: ''
            });
        }

        applyingState = false;
    }

    function synchronizeCreditState() {
        window.clearTimeout(transitionTimer);
        transitionTimer = window.setTimeout(function () {
            if (!applyFullCreditState()) {
                restoreNormalPaymentState();
            }
        }, 0);
    }

    function prepareFullCreditSubmission() {
        if (fullCreditIsSelected()) {
            applyFullCreditState();
        }
    }

    $(function () {
        fullCreditWasActive = fullCreditIsSelected();
        synchronizeCreditState();

        $(document).off('.dmFullCredit1455').on(
            'ifChecked.dmFullCredit1455 change.dmFullCredit1455',
            '#useCreditOnCheckout, #skipCreditOnCheckout',
            function () {
                if (this.id === 'useCreditOnCheckout' && this.checked) {
                    fullCreditWasActive = fullCreditIsSelected();
                    synchronizeCreditState();
                    return;
                }

                if (
                    this.id === 'skipCreditOnCheckout'
                    && this.checked
                    && fullCreditWasActive
                    && !window.dmCheckoutNoCreditBootstrapped1463
                ) {
                    reloadForNoCreditGatewayInitialization();
                    return;
                }

                fullCreditWasActive = false;
                synchronizeCreditState();
            }
        );
    });

    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest
            ? event.target.closest('#btnCompleteOrder')
            : null;

        if (button) {
            prepareFullCreditSubmission();
        }
    }, true);

    document.addEventListener('submit', function (event) {
        if (event.target && event.target.id === 'frmCheckout') {
            prepareFullCreditSubmission();
        }
    }, true);
})(jQuery);
</script>
HTML;
});
