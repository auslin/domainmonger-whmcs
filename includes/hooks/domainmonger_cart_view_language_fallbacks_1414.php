<?php
/**
 * DomainMonger Patches 1414-1415, 1421
 *
 * Restores missing direct LANG template values on the standard-cart review and
 * checkout pages when another client-area hook has returned an incomplete LANG
 * array. The repair is intentionally limited to cart.php?a=view and
 * cart.php?a=checkout and does not alter cart calculations or order behavior.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_cart_language_target_action_1415')) {
    function dm_cart_language_target_action_1415(): string
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower(trim((string) ($_REQUEST['a'] ?? '')));

        if ($script !== 'cart.php' || !in_array($action, ['view', 'checkout'], true)) {
            return '';
        }

        return $action;
    }
}

if (!function_exists('dm_cart_apply_language_fallbacks_1415')) {
    function dm_cart_apply_language_fallbacks_1415(array $vars): array
    {
        $action = dm_cart_language_target_action_1415();

        if ($action === '') {
            return [];
        }

        /*
         * Start with WHMCS's complete global language array, then overlay the
         * current template array. This repairs a partial LANG array without
         * replacing valid translations that are already present.
         */
        $lang = [];

        if (isset($GLOBALS['_LANG']) && is_array($GLOBALS['_LANG'])) {
            $lang = $GLOBALS['_LANG'];
        }

        if (isset($vars['LANG']) && is_array($vars['LANG'])) {
            $lang = array_replace_recursive($lang, $vars['LANG']);
        }

        $isMissing = static function ($value, string $key): bool {
            if ($value === null) {
                return true;
            }

            if (!is_string($value)) {
                return false;
            }

            $value = trim($value);

            return $value === '' || $value === $key;
        };

        $setTopLevel = static function (array &$target, string $key, string $value) use ($isMissing): void {
            if (!array_key_exists($key, $target) || $isMissing($target[$key], $key)) {
                $target[$key] = $value;
            }
        };

        $setNested = static function (
            array &$target,
            string $group,
            string $key,
            string $value
        ) use ($isMissing): void {
            if (!isset($target[$group]) || !is_array($target[$group])) {
                $target[$group] = [];
            }

            $fullKey = $group . '.' . $key;

            if (!array_key_exists($key, $target[$group]) || $isMissing($target[$group][$key], $fullKey)) {
                $target[$group][$key] = $value;
            }
        };

        if ($action === 'view') {
            /* Review-cart heading and item-table labels. */
            $setTopLevel($lang, 'cartreviewcheckout', 'Review & Checkout');
            $setNested($lang, 'orderForm', 'productOptions', 'Product/Options');
            $setNested($lang, 'orderForm', 'qty', 'Qty');
            $setNested($lang, 'orderForm', 'priceCycle', 'Price/Cycle');
            $setNested($lang, 'orderForm', 'edit', 'Edit');
            $setNested($lang, 'orderForm', 'remove', 'Remove');
            $setNested($lang, 'orderForm', 'update', 'Update');
            $setNested($lang, 'orderForm', 'year', 'Year');
            $setNested($lang, 'orderForm', 'years', 'Years');

            /* Product, addon, domain, and renewal labels used by viewcart.tpl. */
            $setTopLevel($lang, 'orderaddon', 'Addon');
            $setTopLevel($lang, 'orderdomainregistration', 'Domain Registration');
            $setTopLevel($lang, 'orderdomaintransfer', 'Domain Transfer');
            $setTopLevel($lang, 'domainrenewal', 'Domain Renewal');
            $setTopLevel($lang, 'domaindnsmanagement', 'DNS Management');
            $setTopLevel($lang, 'domainemailforwarding', 'Email Forwarding');
            $setTopLevel($lang, 'domainidprotection', 'ID Protection');
            $setTopLevel($lang, 'ordersetupfee', 'Setup Fee');
            $setTopLevel($lang, 'orderprorata', 'Pro Rata');
            $setTopLevel($lang, 'orderyears', 'Year/s');
            $setTopLevel($lang, 'upgrade', 'Upgrade');
            $setTopLevel($lang, 'upgradeCredit', 'Upgrade Credit');

            /* Order-summary labels and actions. */
            $setTopLevel($lang, 'ordersummary', 'Order Summary');
            $setTopLevel($lang, 'ordersubtotal', 'Subtotal');
            $setNested($lang, 'orderForm', 'totals', 'Totals');
            $setTopLevel($lang, 'ordertotalduetoday', 'Total Due Today');
            $setNested($lang, 'orderForm', 'checkout', 'Checkout');
            $setNested($lang, 'orderForm', 'continueShopping', 'Continue Shopping');
            $setTopLevel($lang, 'emptycart', 'Empty Cart');
            $setTopLevel($lang, 'cartempty', 'Your Shopping Cart is Empty');

            /* Recurring-cycle text used in the order summary. */
            $setTopLevel($lang, 'orderpaymenttermmonthly', 'Monthly');
            $setTopLevel($lang, 'orderpaymenttermquarterly', 'Quarterly');
            $setTopLevel($lang, 'orderpaymenttermsemiannually', 'Semi-Annually');
            $setTopLevel($lang, 'orderpaymenttermannually', 'Annually');
            $setTopLevel($lang, 'orderpaymenttermbiennially', 'Biennially');
            $setTopLevel($lang, 'orderpaymenttermtriennially', 'Triennially');

            /* Alerts, tax tab, and confirmation modals on this page. */
            $setTopLevel($lang, 'bundlereqsnotmet', 'Bundle Requirements Not Met');
            $setTopLevel(
                $lang,
                'promoappliedbutnodiscount',
                'The promotion code you entered has been applied to your cart but no items qualify for the discount yet - please check the promotion terms'
            );
            $setNested($lang, 'orderForm', 'correctErrors', 'Please correct the following errors before continuing');
            $setNested($lang, 'orderForm', 'promotionAccepted', 'Promotion Code Accepted! Your order total has been updated.');
            $setNested($lang, 'orderForm', 'estimateTaxes', 'Estimate Taxes');
            $setNested($lang, 'orderForm', 'close', 'Close');
            $setNested($lang, 'orderForm', 'removeItem', 'Remove Item');
            $setTopLevel($lang, 'cartremoveitemconfirm', 'Are you sure you want to remove this item from your cart?');
            $setTopLevel($lang, 'cartemptyconfirm', 'Are you sure you want to empty your shopping cart?');
            $setTopLevel($lang, 'or', 'or');
            $setTopLevel($lang, 'yes', 'Yes');
            $setTopLevel($lang, 'no', 'No');
        }

        if ($action === 'checkout') {
            /* Checkout headings, customer/account controls, and form labels. */
            $setNested($lang, 'orderForm', 'checkout', 'Checkout');
            $setNested($lang, 'orderForm', 'alreadyRegistered', 'Already Registered?');
            $setNested($lang, 'orderForm', 'createAccount', 'Create a New Account');
            $setNested($lang, 'orderForm', 'correctErrors', 'Please correct the following errors before continuing');
            $setNested($lang, 'orderForm', 'existingCustomerLogin', 'Existing Customer Login');
            $setNested($lang, 'orderForm', 'personalInformation', 'Personal Information');
            $setNested($lang, 'orderForm', 'billingAddress', 'Billing Address');
            $setNested($lang, 'orderForm', 'accountSecurity', 'Account Security');
            $setNested($lang, 'orderForm', 'paymentDetails', 'Payment Details');
            $setNested($lang, 'orderForm', 'preferredPaymentMethod', 'Please choose your preferred method of payment.');
            $setNested($lang, 'orderForm', 'additionalNotes', 'Additional Notes');

            /* Personal, billing, and domain-contact field placeholders. */
            $setNested($lang, 'orderForm', 'firstName', 'First Name');
            $setNested($lang, 'orderForm', 'lastName', 'Last Name');
            $setNested($lang, 'orderForm', 'emailAddress', 'Email Address');
            $setNested($lang, 'orderForm', 'phoneNumber', 'Phone Number');
            $setNested($lang, 'orderForm', 'companyName', 'Company Name');
            $setNested($lang, 'orderForm', 'optional', 'Optional');
            $setNested($lang, 'orderForm', 'streetAddress', 'Street Address');
            $setNested($lang, 'orderForm', 'streetAddress2', 'Street Address 2');
            $setNested($lang, 'orderForm', 'city', 'City');
            $setNested($lang, 'orderForm', 'state', 'State');
            $setNested($lang, 'orderForm', 'postcode', 'Postcode');
            $setNested(
                $lang,
                'orderForm',
                'domainAlternativeContact',
                'You may specify alternative registered contact details for the domain registration(s) in your order when placing an order on behalf of another person or entity. If you do not require this, you can skip this section.'
            );

            /* Password, captcha, and additional-information labels. */
            $setTopLevel($lang, 'clientareapassword', 'Password');
            $setTopLevel($lang, 'clientareaconfirmpassword', 'Confirm Password');
            $setTopLevel($lang, 'clientareasecurityquestion', 'Please choose a security question');
            $setTopLevel($lang, 'clientareasecurityanswer', 'Please enter an answer');
            $setTopLevel($lang, 'clientareanavaddcontact', 'Add New Contact');
            $setTopLevel($lang, 'usedefaultcontact', 'Use Default Contact (Details Above)');
            $setTopLevel($lang, 'domainregistrantinfo', 'Domain Registrant Information');
            $setTopLevel($lang, 'orderadditionalrequiredinfo', 'Additional Information');
            $setTopLevel($lang, 'captchatitle', 'Spam Bot Verification');
            $setNested($lang, 'generatePassword', 'btnLabel', 'Generate Password');
            $setTopLevel($lang, 'pwstrength', 'Password Strength');
            $setTopLevel($lang, 'pwstrengthenter', 'Enter a Password');
            $setTopLevel($lang, 'pwstrengthweak', 'Weak');
            $setTopLevel($lang, 'pwstrengthmoderate', 'Moderate');
            $setTopLevel($lang, 'pwstrengthstrong', 'Strong');
            $setNested($lang, 'tax', 'errorVatInvalidFormat', 'Invalid VAT number format for :countryName.');

            /* Payment total, card fields, storage choice, and checkout action. */
            $setTopLevel($lang, 'ordertotalduetoday', 'Total Due Today');
            $setNested($lang, 'orderForm', 'cardNumber', 'Card Number');
            $setTopLevel($lang, 'creditcardcvvnumbershort', 'CVV/CVC2');
            $setTopLevel($lang, 'creditcardcardexpires', 'Expiry Date');
            $setTopLevel($lang, 'creditcardcardstart', 'Start Date');
            $setTopLevel($lang, 'creditcardcardissuenum', 'Issue Number');
            $setNested($lang, 'paymentMethods', 'descriptionInput', 'Enter a name for this card');
            $setNested($lang, 'paymentMethodsManage', 'optional', '(Optional)');
            $setTopLevel($lang, 'creditCardStore', 'Save card for faster checkout in future');
            $setTopLevel($lang, 'ordernotesdescription', 'You can enter any additional notes or information you want included with your order here...');
            $setTopLevel($lang, 'ordertosagreement', 'I have read and agree to the');
            $setTopLevel($lang, 'ordertos', 'Terms of Service');
            $setTopLevel($lang, 'ordererroraccepttos', 'You must accept our Terms of Service');
            $setTopLevel($lang, 'completeorder', 'Complete Order');
            $setTopLevel($lang, 'confirmAndPay', 'Confirm & Pay');
            $setTopLevel($lang, 'ordersecure', 'This order form is provided in a secure environment and to help protect against fraud your current IP address');
            $setTopLevel($lang, 'ordersecure2', 'is being logged.');
        }

        /* Keep the global array complete for any later cart hooks. */
        $GLOBALS['_LANG'] = $lang;

        $return = [
            'LANG' => $lang,
        ];

        /*
         * WHMCS prebuilds this value in each domain row on the cart review page.
         * Repair only blank values so the cart shows "1 Year" instead of "1".
         */
        if ($action === 'view' && isset($vars['domains']) && is_array($vars['domains'])) {
            $domains = $vars['domains'];

            foreach ($domains as $index => $domain) {
                if (!is_array($domain)) {
                    continue;
                }

                $yearsLanguage = trim((string) ($domain['yearsLanguage'] ?? ''));

                if ($yearsLanguage === '') {
                    $period = (int) ($domain['regperiod'] ?? 0);
                    $domains[$index]['yearsLanguage'] = $period === 1 ? 'Year' : 'Years';
                }
            }

            $return['domains'] = $domains;
        }

        return $return;
    }
}

/*
 * Register on both cart-specific and general client-area page hooks. The
 * request check keeps the change limited to the two standard-cart actions.
 */
add_hook('ClientAreaPageCart', 9999, static function ($vars) {
    return dm_cart_apply_language_fallbacks_1415(is_array($vars) ? $vars : []);
});

add_hook('ClientAreaPage', 9999, static function ($vars) {
    return dm_cart_apply_language_fallbacks_1415(is_array($vars) ? $vars : []);
});
