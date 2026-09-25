<?php
/**
 * DomainMonger Patch 1688: client-area account credit display.
 *
 * - Exposes the authenticated client's native WHMCS credit balance as formatted
 *   Smarty variables for page-specific templates.
 * - Adds an Account Credit panel to the client-area Dashboard only when the
 *   balance is greater than zero.
 * - Does not alter balances, invoices, transactions, or credit application.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Authentication\CurrentUser;
use WHMCS\Database\Capsule;
use WHMCS\View\Menu\Item as MenuItem;

if (!function_exists('dm1688_account_credit_data')) {
    function dm1688_account_credit_data()
    {
        static $resolved = false;
        static $data = null;

        if ($resolved) {
            return $data;
        }
        $resolved = true;

        try {
            $currentUser = new CurrentUser();
            $client = $currentUser->client();

            if (!$client) {
                return null;
            }

            $clientId = (int) $client->id;
            if ($clientId <= 0) {
                return null;
            }

            $clientRow = Capsule::table('tblclients')
                ->where('id', $clientId)
                ->first(['credit']);

            if (!$clientRow || !isset($clientRow->credit)) {
                return null;
            }

            $credit = (float) $clientRow->credit;

            if (!function_exists('getCurrency') || !function_exists('formatCurrency')) {
                return null;
            }

            $currencyData = getCurrency($clientId);
            if (!is_array($currencyData) || empty($currencyData['id'])) {
                return null;
            }

            $formatted = (string) formatCurrency($credit, (int) $currencyData['id']);

            $data = [
                'clientId' => $clientId,
                'raw' => $credit,
                'formatted' => $formatted,
                'positive' => ($credit > 0),
            ];
        } catch (\Throwable $e) {
            $data = null;
        }

        return $data;
    }
}

add_hook('ClientAreaPage', 50, function ($vars) {
    $credit = dm1688_account_credit_data();
    if (!$credit) {
        return [];
    }

    return [
        'dmAccountCreditRaw' => $credit['raw'],
        'dmAccountCreditFormatted' => $credit['formatted'],
        'dmAccountCreditPositive' => $credit['positive'],
    ];
});

add_hook('ClientAreaHomepagePanels', 50, function (MenuItem $homePagePanels) {
    $credit = dm1688_account_credit_data();
    if (!$credit || !$credit['positive']) {
        return;
    }

    $panel = $homePagePanels->addChild('DomainMonger Account Credit', [
        'name' => 'DomainMonger Account Credit',
        'label' => 'Account Credit',
        'icon' => 'fas fa-wallet',
        'order' => 5,
        'extras' => [
            'color' => 'blue',
            'btn-link' => 'clientarea.php?action=addfunds',
            'btn-text' => 'Add Funds',
            'btn-icon' => 'fas fa-plus',
        ],
    ]);

    $panel->addChild('DomainMonger Account Credit Balance', [
        'name' => 'DomainMonger Account Credit Balance',
        'label' => 'Available Balance: ' . $credit['formatted'],
        'uri' => 'clientarea.php?action=invoices',
        'order' => 10,
        'icon' => 'fas fa-credit-card',
    ]);
});
