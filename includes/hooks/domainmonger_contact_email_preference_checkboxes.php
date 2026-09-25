<?php
/**
 * DomainMonger Contacts/Sub-Accounts checkbox styling.
 *
 * Scope:
 * - /manage/account/contacts
 * - Contact Details / Email Preferences checkboxes only.
 *
 * Purpose:
 * - Match these contact email-preference checkboxes to the confirmed soft-orange
 *   checkbox selected state used on the Register page.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function domainmonger_is_contact_preferences_page(array $vars = []): bool
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    if (strpos($requestUri, 'dmv9support=1') !== false) {
        return false;
    }

    if (strpos($requestUri, '/manage/account/contacts') !== false || strpos($requestUri, 'account/contacts') !== false) {
        return true;
    }

    foreach (['templatefile', 'filename', 'pagetitle'] as $key) {
        if (isset($vars[$key]) && stripos((string) $vars[$key], 'contact') !== false) {
            return true;
        }
    }

    return false;
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    if (!domainmonger_is_contact_preferences_page(is_array($vars) ? $vars : [])) {
        return '';
    }

    return <<<'HTML'
<style id="domainmonger-contact-email-preference-checkboxes">
/* Contacts/Sub-Accounts only: native email-preference checkbox color. */
#main-body input[type="checkbox"][name^="email_preferences"],
#main-body input[type="checkbox"][id$="emails"],
.main-content input[type="checkbox"][name^="email_preferences"],
.primary-content input[type="checkbox"][name^="email_preferences"],
.clientarea input[type="checkbox"][name^="email_preferences"] {
    accent-color: #d8741f !important;
    -webkit-accent-color: #d8741f !important;
}

/* Contacts/Sub-Accounts only: WHMCS/iCheck fallback if these are skinned. */
#main-body label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue,
.main-content label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue,
.primary-content label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue,
.clientarea label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue {
    display: inline-block !important;
    width: 18px !important;
    height: 18px !important;
    min-width: 18px !important;
    min-height: 18px !important;
    margin: 0 6px 0 0 !important;
    padding: 0 !important;
    border: 1px solid #b7c0cc !important;
    border-radius: 2px !important;
    background: #ffffff !important;
    background-image: none !important;
    box-shadow: none !important;
    cursor: pointer !important;
    position: relative !important;
    vertical-align: -4px !important;
}

#main-body label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.hover,
.main-content label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.hover,
.primary-content label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.hover,
.clientarea label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.hover {
    border-color: #d8741f !important;
    background: #fff7ef !important;
    background-image: none !important;
}

#main-body label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.checked,
.main-content label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.checked,
.primary-content label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.checked,
.clientarea label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.checked {
    border-color: #d8741f !important;
    background: #d8741f !important;
    background-image: none !important;
}

#main-body label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.checked::after,
.main-content label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.checked::after,
.primary-content label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.checked::after,
.clientarea label:has(input[type="checkbox"][name^="email_preferences"]) .icheckbox_square-blue.checked::after {
    content: "";
    position: absolute;
    left: 5px;
    top: 2px;
    width: 5px;
    height: 9px;
    border: solid #ffffff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
</style>
<script>
(function () {
    function applyDomainMongerContactCheckboxColor() {
        document.querySelectorAll('input[type="checkbox"][name^="email_preferences"], input[type="checkbox"][id$="emails"]').forEach(function (checkbox) {
            checkbox.style.setProperty('accent-color', '#d8741f', 'important');
            checkbox.style.setProperty('-webkit-accent-color', '#d8741f', 'important');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyDomainMongerContactCheckboxColor);
    } else {
        applyDomainMongerContactCheckboxColor();
    }

    window.setTimeout(applyDomainMongerContactCheckboxColor, 250);
})();
</script>
HTML;
});
