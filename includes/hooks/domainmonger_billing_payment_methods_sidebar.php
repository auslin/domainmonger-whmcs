<?php
/**
 * DomainMonger - Billing Payment Methods navigation.
 *
 * Adds Payment Methods to the native WHMCS Billing navigation:
 * - client Billing sidebar
 * - top Billing dropdown
 *
 * The item is placed between My Quotes and Add Funds.
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('domainmonger_add_payment_methods_to_billing_menu_18')) {
    function domainmonger_add_payment_methods_to_billing_menu_18(MenuItem $menu): void
    {
        $billing = $menu->getChild('Billing');

        if (!$billing instanceof MenuItem) {
            foreach ($menu->getChildren() as $child) {
                if (!$child instanceof MenuItem) {
                    continue;
                }

                $label = strtolower(trim(strip_tags((string) $child->getLabel())));
                if ($label === 'billing') {
                    $billing = $child;
                    break;
                }
            }
        }

        if (!$billing instanceof MenuItem) {
            return;
        }

        foreach ($billing->getChildren() as $child) {
            if (!$child instanceof MenuItem) {
                continue;
            }

            $name = strtolower(trim((string) $child->getName()));
            $label = strtolower(trim(strip_tags((string) $child->getLabel())));
            $uri = strtolower(trim((string) $child->getUri()));

            if (
                $name === 'payment methods'
                || $label === 'payment methods'
                || strpos($uri, '/account/paymentmethods') !== false
            ) {
                return;
            }
        }

        $paymentMethods = $billing->addChild('Payment Methods', [
            'label' => 'Payment Methods',
            'uri' => 'index.php?rp=/account/paymentmethods',
            'order' => 25,
        ]);

        $requestUri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        if (
            strpos($requestUri, '/account/paymentmethods') !== false
            && method_exists($paymentMethods, 'setCurrent')
        ) {
            $paymentMethods->setCurrent(true);
        }
    }
}

$domainmongerPaymentMethodsNav18 = static function (MenuItem $menu): void {
    domainmonger_add_payment_methods_to_billing_menu_18($menu);
};

add_hook('ClientAreaPrimarySidebar', 50, $domainmongerPaymentMethodsNav18);
add_hook('ClientAreaSecondarySidebar', 50, $domainmongerPaymentMethodsNav18);
add_hook('ClientAreaPrimaryNavbar', 50, $domainmongerPaymentMethodsNav18);
add_hook('ClientAreaSecondaryNavbar', 50, $domainmongerPaymentMethodsNav18);
