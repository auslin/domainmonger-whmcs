<?php
/**
 * DomainMonger WHOIS Contact Information radio styling.
 *
 * Patch 807:
 * Replaces the native accent-color radio styling from Patch 806 with a
 * cleaner custom radio control:
 * - orange ring
 * - orange selected dot
 * - no dark browser ring
 * - soft orange focus glow
 *
 * Scope:
 * manage/clientarea.php?action=domaincontacts&domainid=...
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_domaincontacts_orange_radios_is_page')) {
    function dm_domaincontacts_orange_radios_is_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        return ($script === 'clientarea.php' || strpos($uri, '/manage/clientarea.php') !== false)
            && ($action === 'domaincontacts' || strpos($uri, 'action=domaincontacts') !== false);
    }
}

add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_domaincontacts_orange_radios_is_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-domaincontacts-orange-radios-style-v807">
#frmDomainContactModification input[type="radio"],
form[action*="domaincontacts"] input[type="radio"] {
    -webkit-appearance: none !important;
    appearance: none !important;
    background: #ffffff !important;
    border: 1.5px solid #f58220 !important;
    border-radius: 50% !important;
    box-shadow: none !important;
    cursor: pointer !important;
    display: inline-block !important;
    height: 14px !important;
    margin: 0 7px 0 0 !important;
    outline: none !important;
    position: relative !important;
    vertical-align: middle !important;
    width: 14px !important;
}

#frmDomainContactModification input[type="radio"]:checked,
form[action*="domaincontacts"] input[type="radio"]:checked {
    background:
        radial-gradient(circle at center, #f58220 0 38%, transparent 42%) !important;
    border-color: #f58220 !important;
    box-shadow: none !important;
}

#frmDomainContactModification input[type="radio"]:hover,
form[action*="domaincontacts"] input[type="radio"]:hover {
    border-color: #d8741f !important;
}

#frmDomainContactModification input[type="radio"]:focus,
#frmDomainContactModification input[type="radio"]:focus-visible,
form[action*="domaincontacts"] input[type="radio"]:focus,
form[action*="domaincontacts"] input[type="radio"]:focus-visible {
    border-color: #f58220 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .22) !important;
    outline: none !important;
}

/* Keep disabled radios muted if WHMCS disables one. */
#frmDomainContactModification input[type="radio"]:disabled,
form[action*="domaincontacts"] input[type="radio"]:disabled {
    background: #f1f3f5 !important;
    border-color: #b8c2cc !important;
    cursor: not-allowed !important;
    opacity: .75 !important;
}
</style>
HTML;
});
