<?php
/**
 * DomainMonger WHMCS language cleanup: sslState.noSsl.
 *
 * Adds a safe language value for the missing WHMCS SSL state key and includes
 * a rendered-text fallback for pages where WHMCS has already output the raw key.
 *
 * Does not overwrite public_html/manage/lang/overrides/english.php.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_sslstate_nossl_apply_language_value_694')) {
    function dm_sslstate_nossl_apply_language_value_694($vars = [])
    {
        global $_LANG;

        if (!is_array($_LANG)) {
            $_LANG = [];
        }

        if (!isset($_LANG['sslState']) || !is_array($_LANG['sslState'])) {
            $_LANG['sslState'] = [];
        }

        $_LANG['sslState']['noSsl'] = 'No SSL Detected';
        $_LANG['sslState.noSsl'] = 'No SSL Detected';

        $templateLang = [];
        if (isset($vars['LANG']) && is_array($vars['LANG'])) {
            $templateLang = $vars['LANG'];
        }

        if (!isset($templateLang['sslState']) || !is_array($templateLang['sslState'])) {
            $templateLang['sslState'] = [];
        }

        $templateLang['sslState']['noSsl'] = 'No SSL Detected';
        $templateLang['sslState.noSsl'] = 'No SSL Detected';

        return [
            'LANG' => $templateLang,
        ];
    }
}

add_hook('ClientAreaPage', 1, function ($vars) {
    return dm_sslstate_nossl_apply_language_value_694($vars);
});

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    dm_sslstate_nossl_apply_language_value_694($vars);

    return <<<'HTML'
<script>
(function () {
    'use strict';

    var rawKey = 'sslState.noSsl';
    var replacement = 'No SSL Detected';

    function escapeRegExp(value) {
        return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function replaceTextNode(node) {
        if (!node || !node.nodeValue || node.nodeValue.indexOf(rawKey) === -1) {
            return;
        }
        node.nodeValue = node.nodeValue.replace(new RegExp(escapeRegExp(rawKey), 'g'), replacement);
    }

    function replaceElementAttributes(el) {
        if (!el || !el.getAttribute) {
            return;
        }
        ['title', 'aria-label', 'placeholder', 'value', 'data-original-title'].forEach(function (attr) {
            var value = el.getAttribute(attr);
            if (value && value.indexOf(rawKey) !== -1) {
                el.setAttribute(attr, value.replace(new RegExp(escapeRegExp(rawKey), 'g'), replacement));
            }
        });
    }

    function runLanguageCleanup(root) {
        root = root || document.body;
        if (!root) {
            return;
        }

        if (root.nodeType === Node.TEXT_NODE) {
            replaceTextNode(root);
            return;
        }

        if (root.nodeType !== Node.ELEMENT_NODE && root.nodeType !== Node.DOCUMENT_NODE) {
            return;
        }

        if (root.nodeType === Node.ELEMENT_NODE) {
            replaceElementAttributes(root);
        }

        var elements = root.querySelectorAll ? root.querySelectorAll('[title], [aria-label], [placeholder], [data-original-title], input[value], button[value]') : [];
        for (var i = 0; i < elements.length; i++) {
            replaceElementAttributes(elements[i]);
        }

        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null, false);
        var node;
        while ((node = walker.nextNode())) {
            replaceTextNode(node);
        }
    }

    function start() {
        runLanguageCleanup(document.body);

        if (window.MutationObserver && document.body) {
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'characterData') {
                        replaceTextNode(mutation.target);
                        return;
                    }
                    for (var i = 0; i < mutation.addedNodes.length; i++) {
                        runLanguageCleanup(mutation.addedNodes[i]);
                    }
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
                characterData: true
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
}());
</script>
HTML;
});
