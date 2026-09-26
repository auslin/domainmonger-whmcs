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


if (!function_exists('domainmonger_issue18_log_sidebar_tree')) {
    function domainmonger_issue18_log_sidebar_tree(string $hookName, MenuItem $menu): void
    {
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if (stripos($requestUri, 'addfunds') === false && stripos($requestUri, '/account/paymentmethods') === false) {
            return;
        }

        $lines = ['HOOK=' . $hookName . ' URI=' . $requestUri];
        foreach ($menu->getChildren() as $child) {
            if (!$child instanceof MenuItem) {
                continue;
            }

            $lines[] = 'TOP name=' . $child->getName()
                . ' label=' . trim(strip_tags((string) $child->getLabel()))
                . ' uri=' . (string) $child->getUri()
                . ' order=' . (string) $child->getOrder();

            foreach ($child->getChildren() as $grandchild) {
                if (!$grandchild instanceof MenuItem) {
                    continue;
                }

                $lines[] = '  CHILD name=' . $grandchild->getName()
                    . ' label=' . trim(strip_tags((string) $grandchild->getLabel()))
                    . ' uri=' . (string) $grandchild->getUri()
                    . ' order=' . (string) $grandchild->getOrder();
            }
        }

        @file_put_contents('/tmp/domainmonger_issue18_sidebar.log', implode(PHP_EOL, $lines) . PHP_EOL . "---" . PHP_EOL, FILE_APPEND);
    }
}

add_hook('ClientAreaPrimarySidebar', 999, function (MenuItem $primarySidebar): void {
    domainmonger_issue18_log_sidebar_tree('ClientAreaPrimarySidebar', $primarySidebar);
});

add_hook('ClientAreaSecondarySidebar', 999, function (MenuItem $secondarySidebar): void {
    domainmonger_issue18_log_sidebar_tree('ClientAreaSecondarySidebar', $secondarySidebar);
});
