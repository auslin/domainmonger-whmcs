<?php
/**
 * DomainMonger Patches 1416, 1427, 1428, 1429, 1430, 1434, 1477, and 1478
 *
 * Checkout-only presentation repair for the credit-card entry panel:
 * - adds breathing room between the card controls and panel edges;
 * - keeps the card-storage explanation aligned beside its Yes/No toggle.
 *
 * Patch 1427 centers the stored-card/CVV choice area while preserving left-aligned form fields.
 * Patch 1428 turns the saved-card grid into a compact, consistently spaced card row.
 * Patch 1429 widens that row and redistributes its columns so it does not bunch in the center.
 * Patch 1430 removes the unused description column and clarifies the expired/disabled state.
 * Patch 1434 uses the live checkout DOM to align account radios and hide the empty saved-card container.
 * Patch 1477 restores WHMCS native per-gateway saved-method filtering and gives
 * the new-payment-method choice a consistent bordered row.
 * Patch 1478 enlarges payment-method text and removes nested/double borders so
 * each saved or new payment choice has one clean outer box.
 * No cart, payment, or database behavior is changed.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_cart_checkout_is_target_1416')) {
    function dm_cart_checkout_is_target_1416(): bool
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower(trim((string) ($_REQUEST['a'] ?? '')));

        return $script === 'cart.php' && $action === 'checkout';
    }
}

add_hook('ClientAreaHeadOutput', 1, static function (): string {
    if (!dm_cart_checkout_is_target_1416()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-cart-checkout-spacing-1416">
/* Patch 1416: keep checkout card controls away from the panel edges. */
#order-standard_cart #creditCardInputFields.cc-input-container {
    padding: 24px 24px 20px !important;
}

/* Keep the storage toggle and its explanation together on one line when space permits. */
#order-standard_cart #inputNoStoreContainer {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    min-height: 42px !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    line-height: 1.35 !important;
}

#order-standard_cart #inputNoStoreContainer .bootstrap-switch,
#order-standard_cart #inputNoStoreContainer #inputNoStore {
    flex: 0 0 auto !important;
    margin: 0 !important;
    vertical-align: middle !important;
}

#order-standard_cart #inputNoStoreContainer label[for="inputNoStore"] {
    display: block !important;
    flex: 1 1 auto !important;
    width: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    color: inherit !important;
    line-height: 1.35 !important;
    text-align: left !important;
    vertical-align: middle !important;
    white-space: normal !important;
    cursor: pointer !important;
}

/* Patches 1427-1430: center and style the stored-card selection area. */
#order-standard_cart #creditCardInputFields #existingCardsContainer.existing-cc-grid {
    display: grid !important;
    grid-template-columns: 40px 36px minmax(210px, 1fr) 104px !important;
    width: min(100%, 620px) !important;
    max-width: 100% !important;
    margin: 0 auto 14px !important;
    padding: 4px 10px !important;
    align-items: stretch !important;
    overflow-x: auto !important;
    color: #33475b !important;
    background: #fff !important;
    border: 1px solid #d8e0e8 !important;
    border-radius: 6px !important;
    box-shadow: 0 1px 2px rgba(22, 58, 95, 0.08) !important;
    text-align: left !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-info {
    /* Patch 1477: do not use !important on display. WHMCS applies inline
       display:none while filtering saved methods by the selected gateway.
       Patch 1478 removes the per-cell card styling; the grid itself is the
       single visible payment-method box. */
    display: flex;
    align-items: center !important;
    min-height: 52px !important;
    margin: 0 !important;
    padding: 10px 10px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    line-height: 1.35 !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-info:nth-child(n+5) {
    border-top: 1px solid #e7ebf0 !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-select,
#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-icon {
    justify-content: center !important;
    padding-left: 6px !important;
    padding-right: 6px !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-select input[type="radio"] {
    margin: 0 !important;
    accent-color: #d8741f !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-select input[type="radio"]:disabled {
    accent-color: #aab2bc !important;
    cursor: not-allowed !important;
    opacity: .72 !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-icon {
    color: #163a5f !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-icon i {
    width: auto !important;
    padding: 0 !important;
    font-size: 16px !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-details {
    flex-direction: column !important;
    align-items: flex-start !important;
    justify-content: center !important;
    min-width: 0 !important;
    padding-left: 10px !important;
    padding-right: 14px !important;
    color: #163a5f !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-name {
    display: block !important;
    max-width: 100% !important;
    overflow: hidden !important;
    color: #163a5f !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-description {
    display: block !important;
    max-width: 100% !important;
    margin-top: 3px !important;
    overflow: hidden !important;
    color: #667788 !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-expiry {
    flex-direction: column !important;
    justify-content: center !important;
    padding-left: 12px !important;
    padding-right: 8px !important;
    border-left: 1px solid #edf0f4 !important;
    color: #667788 !important;
    font-size: 14px !important;
    text-align: center !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-info.is-expired {
    background: #fafbfc !important;
}

#order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-expiry small {
    display: block !important;
    margin: 2px 0 0 !important;
    color: #b94a48 !important;
    font-weight: 600 !important;
    line-height: 1.1 !important;
}

#order-standard_cart #creditCardInputFields #existingCardInfo {
    justify-content: center !important;
    text-align: center !important;
}

#order-standard_cart #creditCardInputFields #existingCardInfo > [class*="col-"] {
    float: none !important;
    margin-left: auto !important;
    margin-right: auto !important;
    text-align: left !important;
}

#order-standard_cart #creditCardInputFields > ul {
    width: min(100%, 620px) !important;
    margin: 6px auto 14px !important;
    padding: 0 !important;
    background: #fff !important;
    border: 1px solid #d8e0e8 !important;
    border-radius: 6px !important;
    box-shadow: 0 1px 2px rgba(22, 58, 95, 0.08) !important;
    text-align: center !important;
    overflow: hidden !important;
}

#order-standard_cart #creditCardInputFields > ul > li {
    width: 100% !important;
    margin: 0 !important;
    padding: 12px 14px !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    text-align: center !important;
}

#order-standard_cart #creditCardInputFields > ul > li .radio-inline {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 auto !important;
    color: #163a5f !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
    text-align: left !important;
}


/* Patch 1478: make gateway choices and payment instructions easier to read. */
#order-standard_cart #paymentGatewaysContainer .radio-inline {
    color: #163a5f !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
}

#order-standard_cart #creditCardInputFields > p,
#order-standard_cart #creditCardInputFields > .text-muted,
#order-standard_cart #creditCardInputFields .dm-paypal-account-note-1472 {
    color: #33475b !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
}

/* Patch 1434: explicit account-choice layout. The input receives an iCheck wrapper after load,
   so the radio has its own stable column instead of relying on radio-inline positioning. */
#order-standard_cart #containerExistingAccountSelect .account {
    padding: 14px 16px !important;
}

#order-standard_cart #containerExistingAccountSelect .dm-account-choice-label {
    display: grid !important;
    grid-template-columns: 24px minmax(0, 1fr) !important;
    column-gap: 11px !important;
    align-items: start !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    color: #293f56 !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    line-height: 1.45 !important;
    text-align: left !important;
    white-space: normal !important;
    cursor: pointer !important;
}

#order-standard_cart #containerExistingAccountSelect .dm-account-choice-radio {
    display: flex !important;
    align-items: flex-start !important;
    justify-content: center !important;
    min-width: 24px !important;
    min-height: 24px !important;
    padding-top: 1px !important;
}

#order-standard_cart #containerExistingAccountSelect .dm-account-choice-radio > input[type="radio"],
#order-standard_cart #containerExistingAccountSelect .dm-account-choice-radio > .iradio_square-blue {
    position: static !important;
    float: none !important;
    margin: 0 !important;
}

#order-standard_cart #containerExistingAccountSelect .dm-account-choice-copy {
    display: block !important;
    min-width: 0 !important;
    color: #33475b !important;
    font-size: 14px !important;
    line-height: 1.45 !important;
}

#order-standard_cart #containerExistingAccountSelect .dm-account-choice-copy > strong {
    color: #163a5f !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
}

#order-standard_cart #containerExistingAccountSelect .dm-account-choice-copy .small,
#order-standard_cart #containerExistingAccountSelect .dm-account-choice-copy > small {
    display: block !important;
    margin-top: 4px !important;
    color: #667788 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.45 !important;
}

/* Hide the saved-card grid whenever it has no rendered payment-method row. */
#order-standard_cart #creditCardInputFields #existingCardsContainer.dm-empty-saved-cards-1434,
#order-standard_cart #creditCardInputFields #existingCardsContainer:not(:has(.paymethod-info)) {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    box-shadow: none !important;
    overflow: hidden !important;
}

@media (max-width: 767px) {
    #order-standard_cart #creditCardInputFields.cc-input-container {
        padding: 18px 16px 16px !important;
    }

    #order-standard_cart #inputNoStoreContainer {
        gap: 8px !important;
        min-height: 38px !important;
    }

    #order-standard_cart #creditCardInputFields #existingCardsContainer.existing-cc-grid {
        grid-template-columns: 34px 30px minmax(0, 1fr) 84px !important;
        width: 100% !important;
        padding-left: 6px !important;
        padding-right: 6px !important;
    }

    #order-standard_cart #creditCardInputFields #existingCardsContainer .paymethod-info {
        padding-left: 5px !important;
        padding-right: 5px !important;
    }
}
</style>
HTML;
});


add_hook('ClientAreaFooterOutput', 95, static function (): string {
    if (!dm_cart_checkout_is_target_1416()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-cart-checkout-live-empty-cards-1434">
(function ($) {
    'use strict';

    function syncSavedCardContainer() {
        var container = document.getElementById('existingCardsContainer');
        if (!container) {
            return;
        }

        var selectedNewAccount = document.querySelector(
            '#containerExistingAccountSelect input.account-select[value="new"]:checked'
        );
        var hasSavedMethod = container.querySelector('.paymethod-info, input.existing-card');
        var shouldHide = Boolean(selectedNewAccount) || !hasSavedMethod;

        container.classList.toggle('dm-empty-saved-cards-1434', shouldHide);
        if (shouldHide) {
            container.setAttribute('aria-hidden', 'true');
            container.style.setProperty('display', 'none', 'important');
        } else {
            container.removeAttribute('aria-hidden');
            container.style.removeProperty('display');
        }
    }

    function queueSync() {
        window.requestAnimationFrame(syncSavedCardContainer);
        window.setTimeout(syncSavedCardContainer, 100);
        window.setTimeout(syncSavedCardContainer, 600);
    }

    $(function () {
        queueSync();

        $(document).on(
            'ifChecked change',
            '#containerExistingAccountSelect input.account-select',
            queueSync
        );

        $(document).ajaxComplete(queueSync);

        var cardPanel = document.getElementById('creditCardInputFields');
        if (cardPanel && window.MutationObserver) {
            new MutationObserver(queueSync).observe(cardPanel, {
                childList: true,
                subtree: true
            });
        }
    });
}(jQuery));
</script>
HTML;
});
