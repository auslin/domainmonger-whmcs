<?php
/**
 * DomainMonger WHMCS v9
 * Patch 620: Submit Ticket markdown editor rendered-DOM cleanup.
 *
 * Replaces the failed route-specific Patch 618 hook and reinforces Patch 619.
 * This does not depend on templatefile. It finds the generated editor for the
 * client_ticket_open markdown textarea and marks/styles only that editor.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 100, function (array $vars) {
    return <<<'HTML'
<style id="domainmonger-submit-ticket-editor-fix-620">
body.whmcsbody .md-editor.dm-submit-ticket-editor-620,
body.whmcsbody .md-editor.dm-submit-ticket-editor-620.active {
    border: 1px solid #cfd9e3 !important;
    border-radius: 6px !important;
    background: #ffffff !important;
    overflow: hidden !important;
    box-shadow: none !important;
}

body.whmcsbody .md-editor.dm-submit-ticket-editor-620 .md-header,
body.whmcsbody .md-editor.dm-submit-ticket-editor-620 .btn-toolbar {
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    border-color: #d6e0ea !important;
    box-shadow: none !important;
}

body.whmcsbody .md-editor.dm-submit-ticket-editor-620 .md-footer,
body.whmcsbody .md-editor.dm-submit-ticket-editor-620 .markdown-editor-status,
body.whmcsbody .md-editor.dm-submit-ticket-editor-620 [id$="-footer"] {
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    color: #53687d !important;
    min-height: 30px !important;
    height: auto !important;
    line-height: 1.5 !important;
    padding: 6px 12px !important;
    overflow: visible !important;
    text-align: right !important;
    border-color: #d6e0ea !important;
    box-shadow: none !important;
}

body.whmcsbody .md-editor.dm-submit-ticket-editor-620 .small-font,
body.whmcsbody .md-editor.dm-submit-ticket-editor-620 .markdown-save {
    color: #53687d !important;
    font-size: 12px !important;
    line-height: 1.5 !important;
    white-space: nowrap !important;
    overflow: visible !important;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 100, function (array $vars) {
    return <<<'HTML'
<script id="domainmonger-submit-ticket-editor-fix-js-620">
(function () {
    'use strict';

    function paint(el, props) {
        if (!el || !el.style) {
            return;
        }

        Object.keys(props).forEach(function (key) {
            el.style.setProperty(key, props[key], 'important');
        });
    }

    function findTicketMessageTextarea() {
        return document.querySelector(
            'textarea.markdown-editor[data-auto-save-name="client_ticket_open"], textarea#inputMessage.markdown-editor'
        );
    }

    function cleanSubmitTicketEditor() {
        var textarea = findTicketMessageTextarea();

        if (!textarea) {
            return;
        }

        var editor = textarea.closest ? textarea.closest('.md-editor') : null;

        if (!editor) {
            return;
        }

        editor.classList.add('dm-submit-ticket-editor-620');

        paint(editor, {
            'border': '1px solid #cfd9e3',
            'border-radius': '6px',
            'background': '#ffffff',
            'background-color': '#ffffff',
            'background-image': 'none',
            'overflow': 'hidden',
            'box-shadow': 'none'
        });

        var toolbarNodes = editor.querySelectorAll('.md-header, .btn-toolbar');
        Array.prototype.forEach.call(toolbarNodes, function (node) {
            paint(node, {
                'display': 'flex',
                'align-items': 'center',
                'flex-wrap': 'wrap',
                'gap': '4px',
                'min-height': '42px',
                'padding': '6px 8px',
                'margin': '0',
                'background': '#ffffff',
                'background-color': '#ffffff',
                'background-image': 'none',
                'border-bottom': '1px solid #d6e0ea',
                'box-shadow': 'none'
            });
        });

        var footerNodes = editor.querySelectorAll('.md-footer, .markdown-editor-status, [id$="-footer"]');
        Array.prototype.forEach.call(footerNodes, function (node) {
            paint(node, {
                'display': 'block',
                'clear': 'both',
                'width': '100%',
                'min-height': '30px',
                'height': 'auto',
                'line-height': '1.5',
                'margin': '0',
                'padding': '6px 12px',
                'background': '#ffffff',
                'background-color': '#ffffff',
                'background-image': 'none',
                'border': '0',
                'color': '#53687d',
                'text-align': 'right',
                'overflow': 'visible',
                'white-space': 'normal',
                'box-shadow': 'none'
            });

            var inner = node.querySelectorAll('*');
            Array.prototype.forEach.call(inner, function (child) {
                paint(child, {
                    'color': '#53687d',
                    'font-size': '12px',
                    'line-height': '1.5',
                    'white-space': 'nowrap',
                    'overflow': 'visible'
                });
            });
        });
    }

    function scheduleCleanup() {
        cleanSubmitTicketEditor();
        window.setTimeout(cleanSubmitTicketEditor, 75);
        window.setTimeout(cleanSubmitTicketEditor, 250);
        window.setTimeout(cleanSubmitTicketEditor, 700);
        window.setTimeout(cleanSubmitTicketEditor, 1500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleCleanup);
    } else {
        scheduleCleanup();
    }

    window.addEventListener('load', scheduleCleanup);
})();
</script>
HTML;
});
