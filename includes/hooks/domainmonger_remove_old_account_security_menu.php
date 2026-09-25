<?php
/**
 * DomainMonger WHMCS v9
 * Patch 612: Remove old Account Security menu item.
 *
 * The combined security options now live on the Security Settings / user-security
 * page. This removes only the old "Account Security" navigation entry that went
 * to clientarea.php?action=security. It does not remove "Security Settings".
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmonger_normalize_menu_text_612')) {
    function domainmonger_normalize_menu_text_612($value): string
    {
        $text = trim(strip_tags((string)$value));
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);

        return strtolower(trim((string)$text));
    }
}

if (!function_exists('domainmonger_menu_item_value_612')) {
    function domainmonger_menu_item_value_612($item, string $method): string
    {
        if (!is_object($item) || !method_exists($item, $method)) {
            return '';
        }

        try {
            $value = $item->{$method}();

            if (is_array($value) || is_object($value)) {
                return '';
            }

            return (string)$value;
        } catch (Throwable $e) {
            return '';
        }
    }
}

if (!function_exists('domainmonger_is_old_account_security_item_612')) {
    function domainmonger_is_old_account_security_item_612($item): bool
    {
        $label = domainmonger_normalize_menu_text_612(domainmonger_menu_item_value_612($item, 'getLabel'));
        $name = domainmonger_normalize_menu_text_612(domainmonger_menu_item_value_612($item, 'getName'));
        $uri = strtolower(domainmonger_menu_item_value_612($item, 'getUri'));

        if ($label !== 'account security' && $name !== 'account-security' && $name !== 'accountsecurity') {
            return false;
        }

        // Keep this targeted at the old SSO/account-security route. Do not remove
        // the combined user-security / Security Settings page.
        if ($uri === '') {
            return $label === 'account security';
        }

        return strpos($uri, 'action=security') !== false
            || strpos($uri, 'clientarea.php') !== false && strpos($uri, 'security') !== false;
    }
}

if (!function_exists('domainmonger_remove_old_account_security_items_612')) {
    function domainmonger_remove_old_account_security_items_612($menu): void
    {
        if (!is_object($menu) || !method_exists($menu, 'getChildren')) {
            return;
        }

        try {
            $children = $menu->getChildren();
        } catch (Throwable $e) {
            return;
        }

        if (!is_iterable($children)) {
            return;
        }

        foreach ($children as $childName => $child) {
            if (domainmonger_is_old_account_security_item_612($child)) {
                try {
                    if (method_exists($menu, 'removeChild')) {
                        $menu->removeChild($childName);
                    }
                } catch (Throwable $e) {
                    // Ignore menu cleanup failures and continue.
                }

                continue;
            }

            domainmonger_remove_old_account_security_items_612($child);
        }
    }
}

$domainmongerAccountSecurityMenuCleanup612 = function ($menu) {
    domainmonger_remove_old_account_security_items_612($menu);
};

add_hook('ClientAreaPrimaryNavbar', 100, $domainmongerAccountSecurityMenuCleanup612);
add_hook('ClientAreaSecondaryNavbar', 100, $domainmongerAccountSecurityMenuCleanup612);
add_hook('ClientAreaPrimarySidebar', 100, $domainmongerAccountSecurityMenuCleanup612);
add_hook('ClientAreaSecondarySidebar', 100, $domainmongerAccountSecurityMenuCleanup612);
