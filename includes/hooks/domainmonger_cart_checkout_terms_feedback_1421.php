<?php
/**
 * DomainMonger Patch 1422
 *
 * Corrects the failed Patch 1421 Terms of Service feedback script.
 * The earlier package contained corrupted JavaScript characters and therefore
 * never attached its checkout validation handlers.
 *
 * This replacement remains limited to cart.php?a=checkout. WHMCS server-side
 * validation remains authoritative; this only makes the blocked reason clear.
 * It also preserves comfortable spacing above the Terms row when full account
 * credit collapses the payment and credit-card controls.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_cart_checkout_terms_target_1422')) {
    function dm_cart_checkout_terms_target_1422(): bool
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower(trim((string) ($_REQUEST['a'] ?? '')));

        return $script === 'cart.php' && $action === 'checkout';
    }
}

add_hook('ClientAreaFooterOutput', 10020, static function (): string {
    if (!dm_cart_checkout_terms_target_1422()) {
        return '';
    }

    $message = 'You must accept our Terms of Service';

    if (
        isset($GLOBALS['_LANG']['ordererroraccepttos'])
        && is_string($GLOBALS['_LANG']['ordererroraccepttos'])
        && trim($GLOBALS['_LANG']['ordererroraccepttos']) !== ''
    ) {
        $message = trim($GLOBALS['_LANG']['ordererroraccepttos']);
    }

    $messageJson = json_encode(
        $message,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
    );

    if ($messageJson === false) {
        $messageJson = '"You must accept our Terms of Service"';
    }

    $output = <<<'HTML'
<style id="dm-cart-checkout-terms-feedback-style-1422">
#frmCheckout .dm-checkout-terms-row-1422 {
    margin-top: 18px;
    margin-bottom: 12px;
}
#frmCheckout.dm-full-credit-terms-spacing-1422 .dm-checkout-terms-row-1422 {
    margin-top: 28px;
}
#frmCheckout .dm-terms-missing-1422 {
    display: inline-flex;
    align-items: flex-start;
    justify-content: center;
    flex-wrap: wrap;
    gap: 4px;
    margin: 0 auto 8px;
    padding: 10px 13px;
    border: 1px solid #b94a48;
    border-radius: 6px;
    background: #fdf2f2;
    color: #842029;
    text-align: left;
}
#frmCheckout .dm-terms-error-1422 {
    max-width: 560px;
    margin: 6px auto 14px;
    padding: 10px 13px;
    border: 1px solid #b94a48;
    border-radius: 6px;
    background: #fdf2f2;
    color: #842029;
    text-align: center;
    font-weight: 600;
}
#frmCheckout .dm-terms-error-1422 i {
    margin-right: 6px;
}
</style>
<script id="dm-cart-checkout-terms-feedback-1422">
(function () {
    'use strict';

    var termsMessage = __DM_TERMS_MESSAGE__;
    var errorId = 'dmTermsError1422';
    var summaryItemClass = 'dm-terms-error-item-1422';

    function getTermsInput() {
        return document.getElementById('accepttos');
    }

    function getCheckoutForm() {
        return document.getElementById('frmCheckout');
    }

    function restoreCompleteOrderButton() {
        var button = document.getElementById('btnCompleteOrder');
        var icon;

        if (!button) {
            return;
        }

        button.disabled = false;
        button.classList.remove('disabled');
        icon = button.querySelector('i.fas, i.far, i.fal, i.fab');

        if (icon) {
            icon.classList.remove('fa-spinner', 'fa-spin');
            icon.classList.add('fas', 'fa-arrow-circle-right');
        }
    }

    function getTermsParagraph(input) {
        return input ? input.closest('p') : null;
    }

    function elementIsHidden(element) {
        if (!element) {
            return true;
        }

        return element.classList.contains('w-hidden')
            || window.getComputedStyle(element).display === 'none'
            || element.offsetParent === null;
    }

    function fullCreditLayoutIsActive() {
        var useCredit = document.getElementById('useCreditOnCheckout');
        var fullCreditMessage = document.getElementById('spanFullCredit');
        var cardFields = document.getElementById('creditCardInputFields');

        return Boolean(
            useCredit
            && useCredit.checked
            && fullCreditMessage
            && !fullCreditMessage.classList.contains('w-hidden')
            && !elementIsHidden(fullCreditMessage)
            && elementIsHidden(cardFields)
        );
    }

    function updateTermsSpacing() {
        var form = getCheckoutForm();
        var input = getTermsInput();
        var paragraph = getTermsParagraph(input);

        if (paragraph) {
            paragraph.classList.add('dm-checkout-terms-row-1422');
        }

        if (form) {
            form.classList.toggle(
                'dm-full-credit-terms-spacing-1422',
                fullCreditLayoutIsActive()
            );
        }
    }

    function showTopCheckoutError() {
        var alertBox = document.querySelector('.checkout-error-feedback');
        var list;
        var item;

        if (!alertBox) {
            return;
        }

        list = alertBox.querySelector('ul');
        if (list && !list.querySelector('.' + summaryItemClass)) {
            item = document.createElement('li');
            item.className = summaryItemClass;
            item.textContent = termsMessage;
            list.appendChild(item);
        }

        alertBox.classList.remove('d-none', 'w-hidden');
        alertBox.style.display = '';
    }

    function showTermsError() {
        var input = getTermsInput();
        var paragraph;
        var label;
        var error;
        var icon;
        var offsetTop;

        if (!input) {
            return;
        }

        paragraph = getTermsParagraph(input);
        label = input.closest('label');
        error = document.getElementById(errorId);

        if (label) {
            label.classList.add('dm-terms-missing-1422');
        }

        input.setAttribute('aria-invalid', 'true');
        input.setAttribute('aria-describedby', errorId);

        if (!error) {
            error = document.createElement('div');
            error.id = errorId;
            error.className = 'dm-terms-error-1422';
            error.setAttribute('role', 'alert');
            error.setAttribute('aria-live', 'assertive');

            icon = document.createElement('i');
            icon.className = 'fas fa-exclamation-circle';
            icon.setAttribute('aria-hidden', 'true');
            error.appendChild(icon);
            error.appendChild(document.createTextNode(termsMessage));

            if (paragraph && paragraph.parentNode) {
                paragraph.insertAdjacentElement('afterend', error);
            } else if (input.parentNode) {
                input.parentNode.appendChild(error);
            }
        }

        showTopCheckoutError();
        restoreCompleteOrderButton();
        window.setTimeout(restoreCompleteOrderButton, 0);

        if (paragraph) {
            offsetTop = Math.max(0, paragraph.getBoundingClientRect().top + window.pageYOffset - 150);
            window.scrollTo({ top: offsetTop, behavior: 'smooth' });
        }

        input.focus({ preventScroll: true });
    }

    function clearTermsError() {
        var input = getTermsInput();
        var label;
        var error = document.getElementById(errorId);
        var alertBox = document.querySelector('.checkout-error-feedback');
        var summaryItem;
        var remainingVisibleItems;

        if (input) {
            input.removeAttribute('aria-invalid');
            input.removeAttribute('aria-describedby');
            label = input.closest('label');
            if (label) {
                label.classList.remove('dm-terms-missing-1422');
            }
        }

        if (error) {
            error.remove();
        }

        if (!alertBox) {
            return;
        }

        summaryItem = alertBox.querySelector('.' + summaryItemClass);
        if (summaryItem) {
            summaryItem.remove();
        }

        remainingVisibleItems = Array.prototype.some.call(
            alertBox.querySelectorAll('li'),
            function (item) {
                return !item.classList.contains('d-none') && item.textContent.trim() !== '';
            }
        );

        if (!remainingVisibleItems) {
            alertBox.classList.add('d-none');
            alertBox.style.display = 'none';
        }
    }

    function termsAreValid() {
        var input = getTermsInput();
        return !input || input.checked;
    }

    function blockInvalidCheckout(event) {
        if (termsAreValid()) {
            clearTermsError();
            return false;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        showTermsError();
        return true;
    }

    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest
            ? event.target.closest('#btnCompleteOrder')
            : null;

        if (!button) {
            return;
        }

        blockInvalidCheckout(event);
    }, true);

    document.addEventListener('submit', function (event) {
        if (!event.target || event.target.id !== 'frmCheckout') {
            return;
        }

        blockInvalidCheckout(event);
    }, true);

    function initializeTermsFeedback() {
        var input = getTermsInput();
        var cardFields = document.getElementById('creditCardInputFields');
        var gatewayContainer = document.getElementById('paymentGatewaysContainer');
        var observer;

        if (!input) {
            return;
        }

        updateTermsSpacing();
        window.setTimeout(updateTermsSpacing, 0);
        window.setTimeout(updateTermsSpacing, 300);

        input.addEventListener('change', function () {
            if (input.checked) {
                clearTermsError();
            }
        });

        document.addEventListener('change', function (event) {
            if (event.target && (
                event.target.id === 'useCreditOnCheckout'
                || event.target.id === 'skipCreditOnCheckout'
                || event.target.name === 'paymentmethod'
            )) {
                window.setTimeout(updateTermsSpacing, 0);
                window.setTimeout(updateTermsSpacing, 250);
            }
        });

        if (window.MutationObserver) {
            observer = new MutationObserver(function () {
                updateTermsSpacing();
            });

            [cardFields, gatewayContainer].forEach(function (element) {
                if (element) {
                    observer.observe(element, {
                        attributes: true,
                        attributeFilter: ['class', 'style']
                    });
                }
            });
        }

        if (window.jQuery) {
            window.jQuery(document).off('.dmTerms1422').on(
                'ifChecked.dmTerms1422 change.dmTerms1422',
                '#accepttos',
                function () {
                    if (this.checked) {
                        clearTermsError();
                    }
                    window.setTimeout(updateTermsSpacing, 0);
                    window.setTimeout(updateTermsSpacing, 250);
                }
            );
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeTermsFeedback);
    } else {
        initializeTermsFeedback();
    }
})();
</script>
HTML;

    return str_replace('__DM_TERMS_MESSAGE__', $messageJson, $output);
});
