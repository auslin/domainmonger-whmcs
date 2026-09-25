<?php
/**
 * DomainMonger WHMCS language cleanup: clientareagrace.
 *
 * Patch 690 replaces the failed ClientAreaPage-only hook from Patch 689.
 * It keeps the language variable assignment, and also adds a tiny rendered-text
 * fallback for places where WHMCS has already resolved the language string before
 * ClientAreaPage variables are returned.
 *
 * Does not overwrite public_html/manage/lang/overrides/english.php.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function dm_clientareagrace_apply_language_value($vars = [])
{
    global $_LANG;

    if (!is_array($_LANG)) {
        $_LANG = [];
    }

    $_LANG['clientareagrace'] = 'Grace Period';

    $templateLang = [];
    if (isset($vars['LANG']) && is_array($vars['LANG'])) {
        $templateLang = $vars['LANG'];
    }

    $templateLang['clientareagrace'] = 'Grace Period';

    return [
        'LANG' => $templateLang,
    ];
}

add_hook('ClientAreaPage', 1, function ($vars) {
    return dm_clientareagrace_apply_language_value($vars);
});

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    dm_clientareagrace_apply_language_value($vars);

    return <<<'HTML'
<script>
(function () {
    'use strict';

    var rawKey = 'clientareagrace';
    var replacement = 'Grace Period';

    function replaceTextNode(node) {
        if (!node || !node.nodeValue || node.nodeValue.indexOf(rawKey) === -1) {
            return;
        }
        node.nodeValue = node.nodeValue.replace(new RegExp(rawKey, 'g'), replacement);
    }

    function replaceElementAttributes(el) {
        if (!el || !el.getAttribute) {
            return;
        }
        ['title', 'aria-label', 'placeholder', 'value'].forEach(function (attr) {
            var value = el.getAttribute(attr);
            if (value && value.indexOf(rawKey) !== -1) {
                el.setAttribute(attr, value.replace(new RegExp(rawKey, 'g'), replacement));
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

        var elements = root.querySelectorAll ? root.querySelectorAll('[title], [aria-label], [placeholder], input[value], button[value]') : [];
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
