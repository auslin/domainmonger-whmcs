<?php
/**
 * DomainMonger Patch 1450
 *
 * Repairs the incomplete LANG template array on configureproduct.tpl. This
 * restores the visible product-configuration labels and controls without
 * overwriting lang/overrides/english.php or changing cart behavior.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_cart_configure_product_is_target_1450')) {
    function dm_cart_configure_product_is_target_1450(array $vars): bool
    {
        $templateFile = strtolower(trim((string) ($vars['templatefile'] ?? '')));

        return $templateFile === 'configureproduct';
    }
}

if (!function_exists('dm_cart_configure_product_language_1450')) {
    function dm_cart_configure_product_language_1450(array $vars): array
    {
        if (!dm_cart_configure_product_is_target_1450($vars)) {
            return [];
        }

        $lang = [];

        if (isset($GLOBALS['_LANG']) && is_array($GLOBALS['_LANG'])) {
            $lang = $GLOBALS['_LANG'];
        }

        if (isset($vars['LANG']) && is_array($vars['LANG'])) {
            $lang = array_replace_recursive($lang, $vars['LANG']);
        }

        $missing = static function ($value, string $key): bool {
            return $value === null
                || (is_string($value) && (trim($value) === '' || trim($value) === $key));
        };

        $set = static function (array &$target, string $key, string $value) use ($missing): void {
            if (!array_key_exists($key, $target) || $missing($target[$key], $key)) {
                $target[$key] = $value;
            }
        };

        $setNested = static function (
            array &$target,
            string $group,
            string $key,
            string $value
        ) use ($missing): void {
            if (!isset($target[$group]) || !is_array($target[$group])) {
                $target[$group] = [];
            }

            $fullKey = $group . '.' . $key;
            if (!array_key_exists($key, $target[$group]) || $missing($target[$group][$key], $fullKey)) {
                $target[$group][$key] = $value;
            }
        };

        // Flat keys used directly by configureproduct.tpl.
        $set($lang, 'orderconfigure', 'Configure');
        $set($lang, 'cartchoosecycle', 'Choose Billing Cycle');
        $set($lang, 'ordersummary', 'Order Summary');
        $set($lang, 'continue', 'Continue');
        $set($lang, 'addtocart', 'Add to Cart');
        $set($lang, 'enable', 'Enable');
        $set($lang, 'cartconfigserver', 'Configure Server');
        $set($lang, 'serverhostname', 'Hostname');
        $set($lang, 'serverrootpw', 'Root Password');
        $set($lang, 'serverns1prefix', 'Nameserver 1 Prefix');
        $set($lang, 'serverns2prefix', 'Nameserver 2 Prefix');
        $set($lang, 'orderconfigpackage', 'Configurable Options');
        $set($lang, 'orderadditionalrequiredinfo', 'Additional Required Information');
        $set($lang, 'cartavailableaddons', 'Available Addons');

        // Nested keys used by the visible introduction, validation, addon JS,
        // and help alert on this page.
        $setNested($lang, 'orderForm', 'configureDesiredOptions', 'Configure your desired options and continue to checkout.');
        $setNested($lang, 'orderForm', 'correctErrors', 'Please correct the following errors');
        $setNested($lang, 'orderForm', 'haveQuestionsContact', 'Have questions? Contact our sales team for assistance.');
        $setNested($lang, 'orderForm', 'haveQuestionsClickHere', 'Click here');
        $setNested($lang, 'orderForm', 'addToCart', 'Add to Cart');
        $setNested($lang, 'orderForm', 'addedToCartRemove', 'Added to Cart (Remove)');

        // Metered billing labels are conditional but belong to this template.
        $setNested($lang, 'metrics', 'title', 'Usage Billing');
        $setNested($lang, 'metrics', 'explanation', 'Usage charges are calculated based on your actual usage.');
        $setNested($lang, 'metrics', 'startingFrom', 'Starting from');
        $setNested($lang, 'metrics', 'unit', 'unit');
        $setNested($lang, 'metrics', 'viewPricing', 'View Pricing');
        $setNested($lang, 'metrics', 'includedNotCounted', 'included');

        $GLOBALS['_LANG'] = $lang;

        return ['LANG' => $lang];
    }
}

add_hook('ClientAreaPageCart', 9999, static function ($vars) {
    return dm_cart_configure_product_language_1450(is_array($vars) ? $vars : []);
});

add_hook('ClientAreaPage', 9999, static function ($vars) {
    return dm_cart_configure_product_language_1450(is_array($vars) ? $vars : []);
});
