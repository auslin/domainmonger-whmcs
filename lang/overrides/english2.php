<?php
/**
 * WHMCS language overrides for v9.0.5 cart/checkout text.
 *
 * These nested keys are referenced by WHMCS v9.0.5 templates such as:
 * /templates/orderforms/standard_cart/checkout.tpl
 * /templates/orderforms/standard_cart_2/checkout.tpl
 */

if (!isset($_LANG['cart']) || !is_array($_LANG['cart'])) {
    $_LANG['cart'] = [];
}

$_LANG['cart']['availableCreditBalance'] = 'You have :amount in available account credit.';
$_LANG['cart']['applyCreditAmountNoFurtherPayment'] = 'Apply :amount from your credit balance. No further payment will be required.';
$_LANG['cart']['applyCreditAmount'] = 'Apply :amount from your credit balance and pay the remaining amount.';
$_LANG['cart']['applyCreditSkip'] = 'Do not apply credit to this order.';

if (!isset($_LANG['switchAccount']) || !is_array($_LANG['switchAccount'])) {
    $_LANG['switchAccount'] = [];
}

$_LANG['switchAccount']['title'] = 'Choose Account';
$_LANG['switchAccount']['choose'] = 'Choose the account you want to use.';
$_LANG['switchAccount']['noneFound'] = 'No accounts were found.';
$_LANG['switchAccount']['createInstructions'] = 'Create a new order to continue.';
$_LANG['switchAccount']['forcedSwitchRequest'] = 'You need to switch accounts to continue.';
$_LANG['switchAccount']['cancelAndReturn'] = 'Cancel and return';
