<?php
/**
 * DomainMonger Patch 1329
 * Change Password primary-button color consistency.
 *
 * Replaces the older implementation that forced the normal orange background
 * into the element's inline style with !important. That inline declaration
 * prevented every stylesheet hover rule from taking effect.
 *
 * Scope:
 * - Change Password / User Password page only.
 *
 * Result:
 * - Normal primary orange: #f58220
 * - Hover/focus/active orange: #d8741f
 * - Cancel and Generate Password remain navy.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function domainmonger_is_change_password_page(array $vars = []): bool
{
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');

    if (stripos($requestUri, 'dmv9support=1') !== false) {
        return false;
    }

    $uriMatches = [
        '/manage/user/password',
        '/user/password',
        'action=changepw',
        'action=changepassword',
        'action=change-password',
    ];

    foreach ($uriMatches as $match) {
        if (stripos($requestUri, $match) !== false) {
            return true;
        }
    }

    foreach (['templatefile', 'filename', 'pagetitle'] as $key) {
        if (!isset($vars[$key])) {
            continue;
        }

        $value = (string) $vars[$key];
        if (stripos($value, 'password') !== false || stripos($value, 'changepw') !== false) {
            return true;
        }
    }

    return false;
}

add_hook('ClientAreaHeadOutput', 210, function ($vars) {
    if (!domainmonger_is_change_password_page(is_array($vars) ? $vars : [])) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-change-password-save-button">
/*
 * Change Password only.
 * Do not write colors into element.style: inline !important declarations
 * suppress :hover and were the root cause of the missing hover state.
 */
body .dm-change-password-page .dm-password-actions input[type="submit"].btn-primary,
body .dm-change-password-page .dm-password-actions input[type="submit"].dm-btn-primary,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].btn-primary,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].dm-btn-primary {
    background: #f58220 !important;
    background-color: #f58220 !important;
    background-image: none !important;
    border-color: #f58220 !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    box-shadow: none !important;
    text-shadow: none !important;
    opacity: 1 !important;
    transition: background-color 0.15s ease, border-color 0.15s ease !important;
}

body .dm-change-password-page .dm-password-actions input[type="submit"].btn-primary:not([disabled]):hover,
body .dm-change-password-page .dm-password-actions input[type="submit"].btn-primary:not([disabled]):focus,
body .dm-change-password-page .dm-password-actions input[type="submit"].btn-primary:not([disabled]):focus-visible,
body .dm-change-password-page .dm-password-actions input[type="submit"].btn-primary:not([disabled]):active,
body .dm-change-password-page .dm-password-actions input[type="submit"].dm-btn-primary:not([disabled]):hover,
body .dm-change-password-page .dm-password-actions input[type="submit"].dm-btn-primary:not([disabled]):focus,
body .dm-change-password-page .dm-password-actions input[type="submit"].dm-btn-primary:not([disabled]):focus-visible,
body .dm-change-password-page .dm-password-actions input[type="submit"].dm-btn-primary:not([disabled]):active,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].btn-primary:not([disabled]):hover,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].btn-primary:not([disabled]):focus,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].btn-primary:not([disabled]):focus-visible,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].btn-primary:not([disabled]):active,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].dm-btn-primary:not([disabled]):hover,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].dm-btn-primary:not([disabled]):focus,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].dm-btn-primary:not([disabled]):focus-visible,
body.whmcs-templatefile-user-password .dm-password-actions input[type="submit"].dm-btn-primary:not([disabled]):active {
    background: #d8741f !important;
    background-color: #d8741f !important;
    background-image: none !important;
    border-color: #d8741f !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    box-shadow: none !important;
}
</style>
HTML;
});
