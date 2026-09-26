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

    if (!$billing instanceof MenuItem || $billing->getChild('Payment Methods') instanceof MenuItem) {
        return;
    }

    $billing->addChild('Payment Methods', [
        'label' => 'Payment Methods',
        'uri' => 'index.php?rp=/account/paymentmethods',
        'order' => 25,
    ]);
});
