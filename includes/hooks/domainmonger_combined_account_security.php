<?php
/**
 * DomainMonger WHMCS v9
 * Patch 608: Combined Account Security page variables.
 *
 * Supplies the Single Sign-On toggle state to the user-security template so the
 * Account Security page can show Single Sign-On, Security Question, and 2FA
 * together. The existing WHMCS SSO toggle behavior still posts to
 * clientarea.php?action=security.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('domainmonger_combined_security_templatefile_608')) {
    function domainmonger_combined_security_templatefile_608(array $vars): string
    {
        return strtolower(trim((string)($vars['templatefile'] ?? '')));
    }
}

if (!function_exists('domainmonger_combined_security_client_id_608')) {
    function domainmonger_combined_security_client_id_608(array $vars): int
    {
        if (isset($vars['client']) && is_object($vars['client']) && isset($vars['client']->id)) {
            return (int)$vars['client']->id;
        }

        if (isset($vars['clientsdetails']) && is_array($vars['clientsdetails'])) {
            foreach (['id', 'userid', 'clientid'] as $key) {
                if (!empty($vars['clientsdetails'][$key])) {
                    return (int)$vars['clientsdetails'][$key];
                }
            }
        }

        foreach (['uid', 'clientid'] as $key) {
            if (!empty($_SESSION[$key]) && !is_array($_SESSION[$key])) {
                return (int)$_SESSION[$key];
            }
        }

        return 0;
    }
}

add_hook('ClientAreaPage', 20, function ($vars) {
    $vars = is_array($vars) ? $vars : [];
    $templateFile = domainmonger_combined_security_templatefile_608($vars);

    if ($templateFile !== 'user-security') {
        return [];
    }

    $clientId = domainmonger_combined_security_client_id_608($vars);
    $isSsoEnabled = true;

    if ($clientId > 0 && class_exists(Capsule::class)) {
        try {
            $allowSso = Capsule::table('tblclients')
                ->where('id', $clientId)
                ->value('allow_sso');

            if ($allowSso !== null) {
                $isSsoEnabled = (bool)$allowSso;
            }
        } catch (Throwable $e) {
            $isSsoEnabled = true;
        }
    }

    return [
        'dmCombinedSecurityPage' => true,
        'showSsoSetting' => true,
        'isSsoEnabled' => $isSsoEnabled,
    ];
});
