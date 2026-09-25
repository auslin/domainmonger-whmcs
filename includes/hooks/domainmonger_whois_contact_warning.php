<?php
/**
 * DomainMonger WHOIS contact warning fallback.
 *
 * Patch 780:
 * - Updates the WHOIS warning copy to the shorter approved text.
 * - Seeds the WHMCS language value for the standard domain contacts page.
 * - Replaces already-rendered old warning text on the page.
 * - Keeps duplicate blue-box cleanup.
 *
 * This does not touch public_html/manage/lang/overrides/english.php.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_whois_contact_warning_old_text')) {
    function dm_whois_contact_warning_old_text(): string
    {
        return 'It is important to keep your domain WHOIS contact information up-to-date at all times to avoid losing control of your domain.';
    }
}

if (!function_exists('dm_whois_contact_warning_new_text')) {
    function dm_whois_contact_warning_new_text(): string
    {
        return 'Keep domain WHOIS contact information up to date to avoid losing control of domain.';
    }
}

if (!function_exists('dm_whois_contact_warning_is_page')) {
    function dm_whois_contact_warning_is_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        if ($script === 'clientarea.php' && ($action === 'domaincontacts' || strpos($uri, 'action=domaincontacts') !== false)) {
            return true;
        }

        if ($script === 'domainmanagement.php' && ($action === 'domaincontacts' || strpos($uri, 'action=domaincontacts') !== false)) {
            return true;
        }

        return strpos($uri, '/manage/clientarea.php') !== false && strpos($uri, 'action=domaincontacts') !== false;
    }
}

if (!function_exists('dm_whois_contact_warning_seed_lang')) {
    function dm_whois_contact_warning_seed_lang(): void
    {
        if (!dm_whois_contact_warning_is_page()) {
            return;
        }

        if (!isset($GLOBALS['_LANG']) || !is_array($GLOBALS['_LANG'])) {
            $GLOBALS['_LANG'] = [];
        }

        $GLOBALS['_LANG']['whoisContactWarning'] = dm_whois_contact_warning_new_text();
    }
}

dm_whois_contact_warning_seed_lang();

add_hook('ClientAreaPage', 1, function ($vars) {
    dm_whois_contact_warning_seed_lang();

    if (!dm_whois_contact_warning_is_page()) {
        return [];
    }

    $lang = [];
    if (isset($vars['LANG']) && is_array($vars['LANG'])) {
        $lang = $vars['LANG'];
    }

    $lang['whoisContactWarning'] = dm_whois_contact_warning_new_text();

    return [
        'LANG' => $lang,
    ];
});

add_hook('ClientAreaPageDomainContacts', 1, function ($vars) {
    dm_whois_contact_warning_seed_lang();

    $lang = [];
    if (isset($vars['LANG']) && is_array($vars['LANG'])) {
        $lang = $vars['LANG'];
    }

    $lang['whoisContactWarning'] = dm_whois_contact_warning_new_text();

    return [
        'LANG' => $lang,
    ];
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_whois_contact_warning_is_page()) {
        return '';
    }

    $oldText = htmlspecialchars(dm_whois_contact_warning_old_text(), ENT_QUOTES, 'UTF-8');
    $newText = htmlspecialchars(dm_whois_contact_warning_new_text(), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<script id="dm-whois-contact-warning-fix-js-v780">
(function () {
    'use strict';

    var oldWarningText = '{$oldText}';
    var warningText = '{$newText}';

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

    function isVisible(el) {
        var rect;
        if (!el || !window.getComputedStyle) {
            return false;
        }
        rect = el.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0 && window.getComputedStyle(el).display !== 'none';
    }

    function skipTextNode(node) {
        var parent = node && node.parentNode;
        var tag = parent && parent.tagName ? parent.tagName.toLowerCase() : '';

        return tag === 'script' || tag === 'style' || tag === 'textarea' || tag === 'noscript';
    }

    function replaceRenderedOldText() {
        var walker;
        var node;
        var next;
        var changed = false;

        if (!document.body || !document.createTreeWalker) {
            return false;
        }

        walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null);

        while ((node = walker.nextNode())) {
            if (skipTextNode(node)) {
                continue;
            }

            if (node.nodeValue && node.nodeValue.indexOf(oldWarningText) !== -1) {
                next = node.nodeValue.replace(oldWarningText, warningText);
                if (next !== node.nodeValue) {
                    node.nodeValue = next;
                    changed = true;
                }
            }
        }

        return changed;
    }

    function looksLikeInfoBox(el) {
        var cls;
        var style;
        var bg;
        var borderColor;
        var rect;

        if (!isVisible(el)) {
            return false;
        }

        rect = el.getBoundingClientRect();
        if (rect.width < 250 || rect.height < 8 || rect.height > 140) {
            return false;
        }

        cls = (el.className || '').toString();
        if (/alert|info|notice|message|success|warning/i.test(cls)) {
            return true;
        }

        if (!window.getComputedStyle) {
            return false;
        }

        style = window.getComputedStyle(el);
        bg = style.backgroundColor || '';
        borderColor = style.borderTopColor || '';

        return bg
            && bg !== 'rgba(0, 0, 0, 0)'
            && bg !== 'transparent'
            && !/255, 255, 255/.test(bg)
            && borderColor
            && borderColor !== 'rgba(0, 0, 0, 0)'
            && borderColor !== 'transparent';
    }

    function warningBoxes() {
        var candidates = document.querySelectorAll('.alert-info, .alert.alert-info, .alert-primary, .alert, .info, .notice, p, main div, .main-content div, .container div, .card div');
        var boxes = [];
        var i;
        var el;
        var text;

        for (i = 0; i < candidates.length; i += 1) {
            el = candidates[i];
            text = normalize(el.textContent);

            if (looksLikeInfoBox(el) && (text === warningText || text === oldWarningText)) {
                boxes.push(el);
            }
        }

        return boxes;
    }

    function dedupeWarningBoxes() {
        var boxes = warningBoxes();
        var i;

        if (!boxes.length) {
            return false;
        }

        boxes[0].style.display = '';
        boxes[0].setAttribute('data-dm-whois-contact-warning-fixed', '1');

        if (normalize(boxes[0].textContent) === oldWarningText) {
            boxes[0].textContent = warningText;
        }

        for (i = 1; i < boxes.length; i += 1) {
            boxes[i].style.display = 'none';
            boxes[i].setAttribute('data-dm-whois-contact-warning-duplicate-hidden', '1');
        }

        return true;
    }

    function fillEmptyWarningBox() {
        var candidates = document.querySelectorAll('.alert-info, .alert.alert-info, .alert-primary, .alert, .info, .notice, main div, .main-content div, .container div, .card div');
        var i;
        var el;
        var text;

        for (i = 0; i < candidates.length; i += 1) {
            el = candidates[i];
            text = normalize(el.textContent);

            if ((text === '' || text === 'whoisContactWarning') && looksLikeInfoBox(el)) {
                el.textContent = warningText;
                el.setAttribute('data-dm-whois-contact-warning-fixed', '1');
                el.style.display = '';
                return true;
            }
        }

        return false;
    }

    function fixWarning() {
        replaceRenderedOldText();
        if (!dedupeWarningBoxes()) {
            fillEmptyWarningBox();
            dedupeWarningBoxes();
        }
    }

    ready(fixWarning);
    window.setTimeout(fixWarning, 150);
    window.setTimeout(fixWarning, 500);
    window.setTimeout(fixWarning, 1000);
}());
</script>
HTML;
});
