<?php
/**
 * DomainMonger - Billing sidebar Payment Methods link.
 *
 * Adds Payment Methods between My Quotes and Add Funds in the native
 * WHMCS client-area Billing sidebar.
 */

use WHMCS\View\Menu\Item as MenuItem;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaPrimarySidebar', 50, function (MenuItem $primarySidebar): void {
    $billing = $primarySidebar->getChild('Billing');

    if (!$billing instanceof MenuItem) {
        foreach ($primarySidebar->getChildren() as $child) {
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

        if ($name === 'payment methods' || $label === 'payment methods' || strpos($uri, '/account/paymentmethods') !== false) {
            return;
        }
    }

    $paymentMethods = $billing->addChild('Payment Methods', [
        'label' => 'Payment Methods',
        'uri' => 'index.php?rp=/account/paymentmethods',
        'order' => 25,
    ]);

    $requestUri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
    if (strpos($requestUri, '/account/paymentmethods') !== false && method_exists($paymentMethods, 'setCurrent')) {
        $paymentMethods->setCurrent(true);
    }
});
