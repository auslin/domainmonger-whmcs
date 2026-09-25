<?php
/**
 * DomainMonger Patch 1435
 *
 * Repairs the incomplete LANG template array on cart.php?a=confdomains.
 * This is page-specific and does not alter domain configuration behavior.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_cart_confdomains_is_target_1435')) {
    function dm_cart_confdomains_is_target_1435(): bool
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower(trim((string) ($_REQUEST['a'] ?? '')));

        return $script === 'cart.php' && $action === 'confdomains';
    }
}

if (!function_exists('dm_cart_confdomains_language_1435')) {
    function dm_cart_confdomains_language_1435(array $vars): array
    {
        if (!dm_cart_confdomains_is_target_1435()) {
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

        $set($lang, 'cartdomainsconfig', 'Domains Configuration');
        $setNested($lang, 'orderForm', 'reviewDomainAndAddons', 'Please review your domain name selections and any addons that are available for them.');
        $setNested($lang, 'orderForm', 'correctErrors', 'Please correct the following errors before continuing');
        $setNested($lang, 'orderForm', 'addToCart', 'Add to Cart');
        $setNested($lang, 'orderForm', 'addedToCartRemove', 'Added to Cart (Remove)');

        $set($lang, 'orderregperiod', 'Registration Period');
        $set($lang, 'orderyears', 'Year/s');
        $set($lang, 'hosting', 'Hosting');
        $set($lang, 'cartdomainshashosting', 'Has Hosting');
        $set($lang, 'cartdomainsnohosting', 'No Hosting! Click to Add');
        $set($lang, 'domaineppcode', 'EPP Code');
        $set($lang, 'domaineppcodedesc', 'The EPP code is required to transfer this domain.');

        $set($lang, 'domaindnsmanagement', 'DNS Management');
        $set($lang, 'domainaddonsdnsmanagementinfo', 'External DNS Hosting can help speed up your website and improve availability with increased redundancy.');
        $set($lang, 'domainidprotection', 'ID Protection');
        $set($lang, 'domainaddonsidprotectioninfo', 'Protect your personal information and reduce the amount of spam to your inbox by enabling ID Protection.');
        $set($lang, 'domainemailforwarding', 'Email Forwarding');
        $set($lang, 'domainaddonsemailforwardinginfo', 'Get emails forwarded to alternate email addresses of your choice so that you can monitor all from a single account.');

        $set($lang, 'domainnameservers', 'Nameservers');
        $set($lang, 'cartnameserversdesc', 'If you want to use custom nameservers then enter them below. By default, new domains will use our nameservers for hosting on our network.');
        $set($lang, 'domainnameserver1', 'Nameserver 1');
        $set($lang, 'domainnameserver2', 'Nameserver 2');
        $set($lang, 'domainnameserver3', 'Nameserver 3');
        $set($lang, 'domainnameserver4', 'Nameserver 4');
        $set($lang, 'domainnameserver5', 'Nameserver 5');
        $set($lang, 'continue', 'Continue');

        $GLOBALS['_LANG'] = $lang;

        return ['LANG' => $lang];
    }
}

add_hook('ClientAreaPageCart', 9999, static function ($vars) {
    return dm_cart_confdomains_language_1435(is_array($vars) ? $vars : []);
});

add_hook('ClientAreaPage', 9999, static function ($vars) {
    return dm_cart_confdomains_language_1435(is_array($vars) ? $vars : []);
});
