<?php
/**
 * DomainMonger domain-management contact button fallback.
 *
 * Patch 774: Keeps the confirmed "Modify Selected Contact" fix and also
 * fixes the empty orange "Save Changes" button on:
 * manage/domainmanagement.php?action=moddomaincontacts#tabModify
 *
 * This is intentionally scoped to domainmanagement.php and does not touch
 * lang/overrides/english.php.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_domainmanagement_contact_button_action')) {
    function dm_domainmanagement_contact_button_action(): string
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        if ($script !== 'domainmanagement.php' && strpos($uri, '/manage/domainmanagement.php') === false) {
            return '';
        }

        if ($action === 'changedomaincontacts' || strpos($uri, 'action=changedomaincontacts') !== false) {
            return 'changedomaincontacts';
        }

        if ($action === 'moddomaincontacts' || strpos($uri, 'action=moddomaincontacts') !== false) {
            return 'moddomaincontacts';
        }

        return '';
    }
}

if (!function_exists('dm_domainmanagement_contact_button_is_page')) {
    function dm_domainmanagement_contact_button_is_page(): bool
    {
        return dm_domainmanagement_contact_button_action() !== '';
    }
}

if (!function_exists('dm_domainmanagement_contact_button_seed_lang')) {
    function dm_domainmanagement_contact_button_seed_lang(): void
    {
        if (!dm_domainmanagement_contact_button_is_page()) {
            return;
        }

        if (!isset($GLOBALS['_LANG']) || !is_array($GLOBALS['_LANG'])) {
            $GLOBALS['_LANG'] = [];
        }

        if (empty($GLOBALS['_LANG']['rcdom_contactinfomodbutton'])) {
            $GLOBALS['_LANG']['rcdom_contactinfomodbutton'] = 'Modify Selected Contact';
        }

        if (empty($GLOBALS['_LANG']['clientareasavechanges'])) {
            $GLOBALS['_LANG']['clientareasavechanges'] = 'Save Changes';
        }
    }
}

dm_domainmanagement_contact_button_seed_lang();

add_hook('ClientAreaPage', 1, function ($vars) {
    dm_domainmanagement_contact_button_seed_lang();

    if (!dm_domainmanagement_contact_button_is_page()) {
        return [];
    }

    $lang = [];
    if (isset($vars['LANG']) && is_array($vars['LANG'])) {
        $lang = $vars['LANG'];
    }

    if (empty($lang['rcdom_contactinfomodbutton'])) {
        $lang['rcdom_contactinfomodbutton'] = 'Modify Selected Contact';
    }

    if (empty($lang['clientareasavechanges'])) {
        $lang['clientareasavechanges'] = 'Save Changes';
    }

    return [
        'LANG' => $lang,
    ];
});

add_hook('ClientAreaFooterOutput', 1, function () {
    $action = dm_domainmanagement_contact_button_action();
    if ($action === '') {
        return '';
    }

    $actionJs = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<script id="dm-domainmanagement-contact-button-text-js-v774">
(function () {
    'use strict';

    var pageAction = '{$actionJs}';
    var label = pageAction === 'moddomaincontacts' ? 'Save Changes' : 'Modify Selected Contact';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function normalize(text) {
        return (text || '').replace(/\\s+/g, ' ').trim();
    }

    function setButtonLabel(button, text) {
        var tag = (button.tagName || '').toLowerCase();

        if (tag === 'input') {
            button.value = text;
        } else {
            button.textContent = text;
        }

        button.setAttribute('aria-label', text);
        button.setAttribute('title', text);
        button.setAttribute('data-dm-domain-contact-button-text-fixed', '1');
    }

    function fixButtonText() {
        var selectors;
        var buttons;
        var i;
        var button;
        var tag;
        var text;

        if (pageAction === 'moddomaincontacts') {
            selectors = [
                'form[action*="moddomaincontacts"] input[type="submit"]',
                'form[action*="moddomaincontacts"] button',
                'input.btn-success[type="submit"]',
                'button.btn-success',
                'input[type="submit"]',
                'button[type="submit"]'
            ];
        } else {
            selectors = [
                '#tabModify input[type="submit"]',
                '#tabModify button',
                'form[action*="moddomaincontacts"] input[type="submit"]',
                'form[action*="moddomaincontacts"] button',
                'input.btn-primary[type="submit"]',
                'button.btn-primary'
            ];
        }

        buttons = document.querySelectorAll(selectors.join(','));

        for (i = 0; i < buttons.length; i += 1) {
            button = buttons[i];
            tag = (button.tagName || '').toLowerCase();
            text = tag === 'input' ? normalize(button.value) : normalize(button.textContent);

            if (text !== '' && text !== 'rcdom_contactinfomodbutton' && text !== 'clientareasavechanges') {
                continue;
            }

            setButtonLabel(button, label);
            return true;
        }

        return false;
    }

    ready(fixButtonText);
    window.setTimeout(fixButtonText, 150);
    window.setTimeout(fixButtonText, 500);
    window.setTimeout(fixButtonText, 1000);
}());
</script>
HTML;
});
