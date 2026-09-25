<?php
/**
 * DomainMonger Patch 1747
 * Corrected navigation entry points for the shared bulk Payment Method assignment UI.
 * The actual save path remains dm1723_save_assignment() in the confirmed
 * per-item routing hook.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('AdminAreaClientSummaryActionLinks', 1747, static function (array $vars): array {
    $clientId = (int) ($vars['userid'] ?? 0);
    if ($clientId <= 0 || !function_exists('dm1723_admin_assignment_mode')) {
        return [];
    }
    $mode = dm1723_admin_assignment_mode($clientId);
    if (!in_array($mode, ['preview', 'shadow', 'live'], true)) {
        return [];
    }

    return [
        '<a href="addonmodules.php?module=domainmongerpayrouting&amp;userid=' . $clientId . '">Bulk Payment Methods</a>',
    ];
});

if (!function_exists('dm1746_client_bulk_link')) {
    function dm1746_client_bulk_link(int $clientId): string
    {
        if ($clientId <= 0 || !function_exists('dm1723_client_mode')) {
            return '';
        }
        $mode = dm1723_client_mode($clientId);
        if (!in_array($mode, ['shadow', 'live'], true)) {
            return '';
        }
        return '<div class="dm1746-client-bulk-link" style="margin-top:10px;text-align:right;">'
            . '<a class="btn btn-default" href="index.php?m=domainmongerpayrouting" style="background:#163a5f;border-color:#163a5f;color:#fff;text-decoration:none;">Bulk Assign Payment Methods</a>'
            . '</div>';
    }
}

add_hook('ClientAreaProductDetailsOutput', 1747, static function (array $vars): string {
    $clientId = function_exists('dm1723_client_id') ? (int) dm1723_client_id() : 0;
    return dm1746_client_bulk_link($clientId);
});

add_hook('ClientAreaDomainDetailsOutput', 1747, static function (array $vars): string {
    $clientId = function_exists('dm1723_client_id') ? (int) dm1723_client_id() : 0;
    return dm1746_client_bulk_link($clientId);
});
