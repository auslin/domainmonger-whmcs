<?php
/**
 * DomainMonger: clearer client user-invite association error.
 *
 * Upgrade-safe language override implemented as a WHMCS hook so core language
 * files, templates, and /lang/overrides/english.php are not modified.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_user_invite_associated_message_1870')) {
    function dm_user_invite_associated_message_1870(array $vars = [])
    {
        global $_LANG;

        $message = 'You are currently logged in with a user account that is already associated with this account. To accept this invitation using a different user, log out and reopen the invitation link.';

        if (!is_array($_LANG)) {
            $_LANG = [];
        }
        if (!isset($_LANG['accountInvite']) || !is_array($_LANG['accountInvite'])) {
            $_LANG['accountInvite'] = [];
        }
        $_LANG['accountInvite']['userAlreadyAssociated'] = $message;

        $templateLang = [];
        if (isset($vars['LANG']) && is_array($vars['LANG'])) {
            $templateLang = $vars['LANG'];
        }
        if (!isset($templateLang['accountInvite']) || !is_array($templateLang['accountInvite'])) {
            $templateLang['accountInvite'] = [];
        }
        $templateLang['accountInvite']['userAlreadyAssociated'] = $message;

        return ['LANG' => $templateLang];
    }
}

add_hook('ClientAreaPage', 1, function ($vars) {
    return dm_user_invite_associated_message_1870(is_array($vars) ? $vars : []);
});
