<?php
/**
 * DomainMonger Patch 1446
 *
 * Repairs the incomplete LANG template array on configureproductdomain.tpl.
 * This restores the Choose a Domain heading, domain-option labels, action
 * buttons, placeholders, and result text without modifying the protected
 * language override file or any domain-selection behavior.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_cart_configure_product_domain_is_target_1446')) {
    function dm_cart_configure_product_domain_is_target_1446(array $vars): bool
    {
        $templateFile = strtolower(trim((string) ($vars['templatefile'] ?? '')));

        return $templateFile === 'configureproductdomain';
    }
}

if (!function_exists('dm_cart_configure_product_domain_language_1446')) {
    function dm_cart_configure_product_domain_language_1446(array $vars): array
    {
        if (!dm_cart_configure_product_domain_is_target_1446($vars)) {
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

        // Legacy flat keys still used by WHMCS v9 configureproductdomain.tpl.
        $set($lang, 'domaincheckerchoosedomain', 'Choose a Domain');
        $set($lang, 'cartproductdomainuseincart', 'Use a domain already in my shopping cart');
        $set($lang, 'cartregisterdomainchoice', 'Register a new domain');
        $set($lang, 'carttransferdomainchoice', 'Transfer your domain from another registrar');
        $set($lang, 'cartexistingdomainchoice', 'I will use my existing domain and update my nameservers');
        $set($lang, 'cartsubdomainchoice', 'Use a subdomain from %s');
        $set($lang, 'yourdomainplaceholder', 'example');
        $set($lang, 'yourtldplaceholder', 'com');
        $set($lang, 'orderfreedomainregistration', 'Free Domain Registration');
        $set($lang, 'orderfreedomainappliesto', 'applies to the following extensions only');
        $set($lang, 'domainavailable1', 'Congratulations,');
        $set($lang, 'domainavailable2', 'is available!');
        $set($lang, 'domaincheckertaken', 'Taken');
        $set($lang, 'domaincheckeradded', 'Added');
        $set($lang, 'addtocart', 'Add to Cart');
        $set($lang, 'continue', 'Continue');

        // Nested keys used for the visible domain controls and buttons.
        $setNested($lang, 'orderForm', 'use', 'Use');
        $setNested($lang, 'orderForm', 'www', 'www.');
        $setNested($lang, 'orderForm', 'check', 'Check');
        $setNested($lang, 'orderForm', 'transfer', 'Transfer');

        $GLOBALS['_LANG'] = $lang;

        return ['LANG' => $lang];
    }
}

add_hook('ClientAreaPageCart', 9999, static function ($vars) {
    return dm_cart_configure_product_domain_language_1446(is_array($vars) ? $vars : []);
});

add_hook('ClientAreaPage', 9999, static function ($vars) {
    return dm_cart_configure_product_domain_language_1446(is_array($vars) ? $vars : []);
});
