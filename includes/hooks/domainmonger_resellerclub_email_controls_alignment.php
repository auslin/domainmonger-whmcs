<?php
/**
 * DomainMonger ResellerClub Email Forwarding controls alignment.
 *
 * Patch 743:
 * - keeps Patch 740 pagination behavior
 * - strengthens the Manage Email Forwards search-control detection
 * - adds/forces the records-found summary text next to the pager
 *
 * Scope: /manage/emailmanagement.php?action=manageemails
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_rcemail_controls_alignment_is_manageemails_page')) {
    function dm_rcemail_controls_alignment_is_manageemails_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        if ($script !== 'emailmanagement.php' && strpos($uri, '/manage/emailmanagement.php') === false) {
            return false;
        }

        return $action === 'manageemails' || strpos($uri, 'action=manageemails') !== false;
    }
}

add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_rcemail_controls_alignment_is_manageemails_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-rcemail-controls-alignment-css-v743">
body.dm-rcemail-controls-page .dm-rcemail-search-inline,
body.dm-rcemail-controls-page form.dm-rcemail-search-form {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    flex-wrap: nowrap !important;
    gap: 8px !important;
    width: auto !important;
    max-width: 560px !important;
    min-width: 0 !important;
    margin: 8px 0 12px !important;
    padding: 0 !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
}

body.dm-rcemail-controls-page .dm-rcemail-search-inline input[type="text"],
body.dm-rcemail-controls-page .dm-rcemail-search-inline input[type="search"],
body.dm-rcemail-controls-page form.dm-rcemail-search-form input[type="text"],
body.dm-rcemail-controls-page form.dm-rcemail-search-form input[type="search"],
body.dm-rcemail-controls-page .dm-rcemail-search-input {
    display: inline-block !important;
    flex: 0 1 240px !important;
    width: 240px !important;
    min-width: 180px !important;
    max-width: 300px !important;
    margin: 0 !important;
    vertical-align: middle !important;
}

body.dm-rcemail-controls-page .dm-rcemail-search-inline button,
body.dm-rcemail-controls-page .dm-rcemail-search-inline input[type="submit"],
body.dm-rcemail-controls-page .dm-rcemail-search-inline input[type="button"],
body.dm-rcemail-controls-page .dm-rcemail-search-inline a.dm-rcemail-search-button,
body.dm-rcemail-controls-page form.dm-rcemail-search-form button,
body.dm-rcemail-controls-page form.dm-rcemail-search-form input[type="submit"],
body.dm-rcemail-controls-page form.dm-rcemail-search-form input[type="button"],
body.dm-rcemail-controls-page form.dm-rcemail-search-form a.dm-rcemail-search-button,
body.dm-rcemail-controls-page .dm-rcemail-search-button {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex: 0 0 auto !important;
    margin: 0 !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
}

body.dm-rcemail-controls-page .dm-rcemail-summary-text {
    display: block !important;
    clear: both !important;
    width: 100% !important;
    min-height: 24px !important;
    margin: 10px 0 8px !important;
    padding: 0 !important;
    white-space: nowrap !important;
    color: inherit !important;
    text-align: left !important;
}

body.dm-rcemail-controls-page .dm-rcemail-pager-row {
    clear: both !important;
    display: flex !important;
    justify-content: flex-end !important;
    align-items: center !important;
    gap: 8px !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 14px 0 4px !important;
    padding: 0 !important;
    text-align: right !important;
}

body.dm-rcemail-controls-page .dm-rcemail-pager-row .pull-left,
body.dm-rcemail-controls-page .dm-rcemail-pager-row .pull-right,
body.dm-rcemail-controls-page .dm-rcemail-pager-row .text-left,
body.dm-rcemail-controls-page .dm-rcemail-pager-row .text-right,
body.dm-rcemail-controls-page .dm-rcemail-pager-row form,
body.dm-rcemail-controls-page .dm-rcemail-limit-host,
body.dm-rcemail-controls-page .dm-rcemail-pager-host {
    float: none !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    width: auto !important;
    max-width: none !important;
    min-width: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    text-align: right !important;
}

body.dm-rcemail-controls-page .dm-rcemail-pager-host {
    order: 1 !important;
    gap: 5px !important;
}

body.dm-rcemail-controls-page .dm-rcemail-limit-host {
    order: 2 !important;
}

body.dm-rcemail-controls-page .dm-rcemail-pager-row select.dm-rcemail-itemlimit {
    display: inline-block !important;
    flex: 0 0 78px !important;
    width: 78px !important;
    min-width: 78px !important;
    max-width: 78px !important;
    height: 32px !important;
    margin: 0 !important;
    padding: 5px 24px 5px 8px !important;
    vertical-align: middle !important;
}

body.dm-rcemail-controls-page .dm-rcemail-pager-row .pagination,
body.dm-rcemail-controls-page .dm-rcemail-pager-host .pagination {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 5px !important;
    width: auto !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
}

body.dm-rcemail-controls-page .dm-rcemail-pager-row .pagination > li {
    display: inline-flex !important;
    margin: 0 !important;
    padding: 0 !important;
}

body.dm-rcemail-controls-page .dm-rcemail-pager-row .pagination > li > a,
body.dm-rcemail-controls-page .dm-rcemail-pager-row .pagination > li > span,
body.dm-rcemail-controls-page .dm-rcemail-pager-host a,
body.dm-rcemail-controls-page .dm-rcemail-pager-host button,
body.dm-rcemail-controls-page .dm-rcemail-pager-host input[type="button"],
body.dm-rcemail-controls-page .dm-rcemail-pager-host input[type="submit"] {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 32px !important;
    height: 32px !important;
    padding: 6px 11px !important;
    line-height: 18px !important;
    margin: 0 !important;
    white-space: nowrap !important;
}

body.dm-rcemail-controls-page .dm-rcemail-hide-node,
body.dm-rcemail-controls-page .dm-rcemail-hide-node *,
body.dm-rcemail-controls-page br.dm-rcemail-remove-br,
body.dm-rcemail-controls-page .dm-rcemail-pager-row + br {
    display: none !important;
}

@media (max-width: 700px) {
    body.dm-rcemail-controls-page .dm-rcemail-search-inline,
    body.dm-rcemail-controls-page form.dm-rcemail-search-form {
        width: 100% !important;
        max-width: 100% !important;
        flex-wrap: nowrap !important;
    }

    body.dm-rcemail-controls-page .dm-rcemail-search-inline input[type="text"],
    body.dm-rcemail-controls-page .dm-rcemail-search-inline input[type="search"],
    body.dm-rcemail-controls-page form.dm-rcemail-search-form input[type="text"],
    body.dm-rcemail-controls-page form.dm-rcemail-search-form input[type="search"] {
        flex: 1 1 auto !important;
        width: auto !important;
        max-width: none !important;
    }

    body.dm-rcemail-controls-page .dm-rcemail-pager-row {
        justify-content: flex-start !important;
        flex-wrap: wrap !important;
        text-align: left !important;
    }

    body.dm-rcemail-controls-page .dm-rcemail-summary-text {
        width: 100% !important;
        margin-bottom: 6px !important;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_rcemail_controls_alignment_is_manageemails_page()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-rcemail-controls-alignment-js-v743">
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function hasClass(el, className) {
        return !!(el && el.classList && el.classList.contains(className));
    }

    function addClass(el, className) {
        if (el && el.classList) {
            el.classList.add(className);
        }
    }

    function textOf(el) {
        if (!el) {
            return '';
        }
        return (el.value || el.getAttribute('aria-label') || el.getAttribute('title') || el.getAttribute('placeholder') || el.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function isVisible(el) {
        var style;
        if (!el) {
            return false;
        }
        if (el.type && String(el.type).toLowerCase() === 'hidden') {
            return false;
        }
        style = window.getComputedStyle ? window.getComputedStyle(el) : null;
        if (style && (style.display === 'none' || style.visibility === 'hidden')) {
            return false;
        }
        return true;
    }

    function controlLooksDangerous(el) {
        var label = textOf(el).toLowerCase();
        return label.indexOf('delete') !== -1 ||
            label.indexOf('modify') !== -1 ||
            label.indexOf('clear') !== -1 ||
            label.indexOf('add') !== -1 ||
            label.indexOf('save') !== -1 ||
            label.indexOf('create') !== -1 ||
            label.indexOf('activate') !== -1 ||
            label.indexOf('disable') !== -1;
    }

    function inputLooksLikeSearch(input) {
        var identity;
        var formText;

        if (!input || !isVisible(input)) {
            return false;
        }

        identity = ((input.id || '') + ' ' + (input.name || '') + ' ' + (input.getAttribute('placeholder') || '') + ' ' + (input.getAttribute('aria-label') || '') + ' ' + (input.getAttribute('title') || '')).toLowerCase();

        if (identity.indexOf('search') !== -1 || identity.indexOf('query') !== -1 || identity.indexOf('keyword') !== -1 || identity.indexOf('filter') !== -1 || identity.indexOf('srch') !== -1) {
            return true;
        }

        if (input.form) {
            formText = textOf(input.form).toLowerCase();
            if (formText.indexOf('search') !== -1) {
                return true;
            }
        }

        return false;
    }

    function nearestButtonAfter(input) {
        var all = document.querySelectorAll('input[type="text"], input[type="search"], button, input[type="submit"], input[type="button"], a');
        var seenInput = false;
        var i;
        var el;
        var tag;
        var label;

        for (i = 0; i < all.length; i += 1) {
            el = all[i];

            if (el === input) {
                seenInput = true;
                continue;
            }

            if (!seenInput) {
                continue;
            }

            if ((el.matches && el.matches('input[type="text"], input[type="search"]'))) {
                return null;
            }

            tag = (el.tagName || '').toLowerCase();
            if ((tag === 'button' || tag === 'a' || (tag === 'input' && /^(submit|button)$/i.test(el.type || ''))) && isVisible(el) && !controlLooksDangerous(el)) {
                label = textOf(el).toLowerCase();
                if (label === '' || label.indexOf('search') !== -1 || tag !== 'a') {
                    return el;
                }
            }
        }

        return null;
    }

    function nearestButtonInForm(input) {
        var buttons;
        var i;
        var el;
        var label;

        if (!input || !input.form) {
            return null;
        }

        buttons = input.form.querySelectorAll('button, input[type="submit"], input[type="button"], a');
        for (i = 0; i < buttons.length; i += 1) {
            el = buttons[i];
            if (!isVisible(el) || controlLooksDangerous(el)) {
                continue;
            }
            label = textOf(el).toLowerCase();
            if (label === '' || label.indexOf('search') !== -1 || /^(submit|button)$/i.test(el.type || '')) {
                return el;
            }
        }

        return null;
    }

    function findSearchInputAndButton() {
        var inputs = document.querySelectorAll('input[type="search"], input[type="text"]');
        var i;
        var input = null;
        var button = null;

        for (i = 0; i < inputs.length; i += 1) {
            if (inputLooksLikeSearch(inputs[i])) {
                input = inputs[i];
                break;
            }
        }

        if (!input) {
            for (i = 0; i < inputs.length; i += 1) {
                if (isVisible(inputs[i]) && (inputs[i].offsetWidth || inputs[i].getBoundingClientRect().width) > 90) {
                    input = inputs[i];
                    break;
                }
            }
        }

        if (!input) {
            return null;
        }

        button = nearestButtonInForm(input) || nearestButtonAfter(input);
        if (!button) {
            return null;
        }

        return { input: input, button: button, form: input.form || button.form || input.parentNode };
    }

    function ensureSearchButtonLabel(button) {
        var label;
        if (!button) {
            return;
        }

        label = textOf(button);
        if (!label || /^\s*$/.test(label)) {
            if ('value' in button && /input/i.test(button.tagName || '')) {
                button.value = 'Search';
            } else {
                button.textContent = 'Search';
            }
        }

        button.setAttribute('aria-label', 'Search');
        button.title = 'Search';
    }

    function moveInlineSearchControls() {
        var found = findSearchInputAndButton();
        var input;
        var button;
        var form;
        var wrapper;
        var brs;
        var i;

        if (!found) {
            return;
        }

        input = found.input;
        button = found.button;
        form = found.form;
        ensureSearchButtonLabel(button);

        if (form && form.tagName && form.tagName.toLowerCase() === 'form' && input.form === form && button.form === form) {
            addClass(form, 'dm-rcemail-search-form');
            addClass(input, 'dm-rcemail-search-input');
            addClass(button, 'dm-rcemail-search-button');
            ensureSearchButtonLabel(button);

            brs = form.getElementsByTagName('br');
            for (i = 0; i < brs.length; i += 1) {
                addClass(brs[i], 'dm-rcemail-remove-br');
            }
            return;
        }

        wrapper = input.parentNode && hasClass(input.parentNode, 'dm-rcemail-search-inline') ? input.parentNode : null;
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'dm-rcemail-search-inline';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
        }

        if (button.parentNode !== wrapper) {
            wrapper.appendChild(button);
        }

        addClass(input, 'dm-rcemail-search-input');
        addClass(button, 'dm-rcemail-search-button');
        ensureSearchButtonLabel(button);

        if (form && form.getElementsByTagName) {
            brs = form.getElementsByTagName('br');
            for (i = 0; i < brs.length; i += 1) {
                addClass(brs[i], 'dm-rcemail-remove-br');
            }
        }
    }

    function selectLooksLikeLimit(select) {
        var identity;
        var numeric = 0;
        var i;
        var raw;

        if (!select || !select.options || !select.options.length) {
            return false;
        }

        identity = ((select.id || '') + ' ' + (select.name || '')).toLowerCase();
        if (identity.indexOf('limit') !== -1 || identity.indexOf('page') !== -1 || identity.indexOf('record') !== -1) {
            return true;
        }

        for (i = 0; i < select.options.length; i += 1) {
            raw = (select.options[i].value || select.options[i].textContent || '').replace(/\s+/g, '');
            if (/^\d{1,3}$/.test(raw)) {
                numeric += 1;
            }
        }
        return numeric > 0;
    }

    function findItemLimitSelect() {
        var direct = document.querySelector('select#itemlimit, select[name="itemlimit"], select[name="limit"], select[name="recordsperpage"], select[name="recordlimit"]');
        var selects;
        var i;

        if (direct) {
            return direct;
        }

        selects = document.getElementsByTagName('select');
        for (i = 0; i < selects.length; i += 1) {
            if (selectLooksLikeLimit(selects[i])) {
                return selects[i];
            }
        }
        return null;
    }

    function setLimitSelectToTen(select) {
        var i;
        var option;
        var raw;
        var form;
        var hidden;
        var name;

        if (!select) {
            return;
        }

        addClass(select, 'dm-rcemail-itemlimit');
        select.setAttribute('aria-label', 'Records Per Page');
        select.title = 'Records Per Page';

        for (i = 0; i < select.options.length; i += 1) {
            option = select.options[i];
            raw = (option.value || option.textContent || '').replace(/\s+/g, '');
            if (raw === '10') {
                select.selectedIndex = i;
                select.value = option.value || '10';
                option.selected = true;
                break;
            }
        }

        form = select.form;
        if (form) {
            name = select.name || 'itemlimit';
            hidden = form.querySelector('input[type="hidden"][name="' + name.replace(/"/g, '\\"') + '"]');
            if (!hidden && name) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = name;
                form.appendChild(hidden);
            }
            if (hidden) {
                hidden.value = '10';
            }
        }
    }

    function getCurrentPageFromQuery() {
        var params = new URLSearchParams(window.location.search || '');
        var page = parseInt(params.get('page') || '1', 10);
        return isNaN(page) || page < 1 ? 1 : page;
    }

    function findPagination() {
        return document.querySelector('ul.pagination, .pagination');
    }

    function getPaginationNumbers(pagination) {
        var controls;
        var i;
        var num;
        var current = 1;
        var total = 1;
        var cls;
        var txt;

        if (!pagination) {
            return { current: getCurrentPageFromQuery(), total: 1 };
        }

        controls = pagination.querySelectorAll('li, a, span');
        for (i = 0; i < controls.length; i += 1) {
            txt = textOf(controls[i]);
            num = parseInt(txt, 10);
            if (!isNaN(num)) {
                if (num > total) {
                    total = num;
                }
                cls = (controls[i].className || '').toLowerCase();
                if (cls.indexOf('active') !== -1 || controls[i].getAttribute('aria-current') === 'page') {
                    current = num;
                }
            }
        }

        if (current < 1) {
            current = getCurrentPageFromQuery();
        }
        if (total < current) {
            total = current;
        }

        return { current: current || 1, total: total || 1 };
    }

    function labelPagerControl(control, label) {
        if (!control) {
            return;
        }
        if ('value' in control && /input/i.test(control.tagName || '')) {
            control.value = label;
        } else {
            control.textContent = label;
        }
        control.setAttribute('aria-label', label);
        control.title = label;
    }

    function labelPagination(pagination) {
        var controls;
        var usable = [];
        var i;

        if (!pagination) {
            return;
        }

        controls = pagination.querySelectorAll('a, span, button, input[type="button"], input[type="submit"]');
        for (i = 0; i < controls.length; i += 1) {
            if (isVisible(controls[i])) {
                usable.push(controls[i]);
            }
        }

        if (usable.length === 1) {
            labelPagerControl(usable[0], 'Next Page');
            return;
        }

        if (usable.length >= 2) {
            labelPagerControl(usable[0], 'Prev Page');
            labelPagerControl(usable[usable.length - 1], 'Next Page');
        }
    }

    function forcePagerLinksToTen(root) {
        var links;
        var i;
        var href;
        var sep;

        if (!root || !root.querySelectorAll) {
            return;
        }

        links = root.querySelectorAll('a[href]');
        for (i = 0; i < links.length; i += 1) {
            href = links[i].getAttribute('href') || '';
            if (!href || href.indexOf('javascript:') === 0 || href.indexOf('#') === 0) {
                continue;
            }
            href = href.replace(/([?&](?:itemlimit|limit|recordsperpage|recordlimit|pagesize|numrecords)=)[^&#]*/ig, '$110');
            if (!/[?&](?:itemlimit|limit|recordsperpage|recordlimit|pagesize|numrecords)=/i.test(href)) {
                sep = href.indexOf('?') === -1 ? '?' : '&';
                href += sep + 'itemlimit=10';
            }
            links[i].setAttribute('href', href);
        }
    }

    function countVisibleDataRows() {
        var tables = document.getElementsByTagName('table');
        var i;
        var rows;
        var j;
        var best = 0;
        var row;
        var tdCount;
        var count;

        for (i = 0; i < tables.length; i += 1) {
            rows = tables[i].querySelectorAll('tr');
            count = 0;
            for (j = 0; j < rows.length; j += 1) {
                row = rows[j];
                tdCount = row.querySelectorAll('td').length;
                if (tdCount > 1 && isVisible(row)) {
                    var rowText = textOf(row).toLowerCase();
                    if (rowText.indexOf('no record') === -1 &&
                        rowText.indexOf('no email') === -1 &&
                        rowText.indexOf('not found') === -1 &&
                        rowText.indexOf('no data') === -1) {
                        count += 1;
                    }
                }
            }
            if (count > best) {
                best = count;
            }
        }

        return best;
    }

    function findLooseSummaryTriple() {
        var walker;
        var node;
        var text;
        var match;
        var parent;

        if (!document.body || !document.createTreeWalker) {
            return null;
        }

        walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
        while ((node = walker.nextNode())) {
            text = (node.nodeValue || '').replace(/\s+/g, ' ').trim();
            if (!text || text.length > 24) {
                continue;
            }

            // Registrar output can appear as "1 , 1 1" or as partial zero text like "0 ,".
            match = text.match(/^(\d+)\s*,\s*(?:(\d+)\s+(\d+))?$/);
            if (!match) {
                continue;
            }

            parent = node.parentNode;
            if (parent && parent.nodeType === 1) {
                return {
                    node: node,
                    totalRecords: parseInt(match[1], 10),
                    currentPage: match[2] ? parseInt(match[2], 10) : 1,
                    totalPages: match[3] ? parseInt(match[3], 10) : 1
                };
            }
        }
        return null;
    }

    function hideLooseCommaCountNodes() {
        var walker;
        var node;
        var text;
        var parent;

        if (!document.body || !document.createTreeWalker) {
            return;
        }

        walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
        while ((node = walker.nextNode())) {
            text = (node.nodeValue || '').replace(/\s+/g, ' ').trim();
            if (/^\d+\s*,\s*(?:\d+\s+\d+)?$/.test(text)) {
                parent = node.parentNode;
                if (parent && parent.nodeType === 1 && !parent.classList.contains('dm-rcemail-summary-text')) {
                    addClass(parent, 'dm-rcemail-hide-node');
                }
            }
        }
    }

    function findBestSummaryAnchor(row) {
        var table = document.querySelector('table');
        var raw = findLooseSummaryTriple();

        if (raw && raw.node && raw.node.parentNode) {
            return raw.node.parentNode;
        }

        if (table && table.parentNode) {
            return table;
        }

        return row;
    }

    function ensureSummaryText(row, pageInfo) {
        var summary = document.querySelector('.dm-rcemail-summary-text');
        var raw = findLooseSummaryTriple();
        var totalRecords = null;
        var currentPage = pageInfo.current;
        var totalPages = pageInfo.total;
        var visibleRows = countVisibleDataRows();
        var anchor;

        if (raw) {
            totalRecords = isNaN(raw.totalRecords) ? null : raw.totalRecords;
            currentPage = raw.currentPage || currentPage;
            totalPages = raw.totalPages || totalPages;
            if (raw.node && raw.node.parentNode) {
                addClass(raw.node.parentNode, 'dm-rcemail-hide-node');
            }
        }

        // Important: 0 is a real value. Only fall back when the raw registrar count is absent.
        if (totalRecords === null || typeof totalRecords === 'undefined') {
            totalRecords = visibleRows;
        }
        if (!currentPage || currentPage < 1) {
            currentPage = 1;
        }
        if (!totalPages || totalPages < currentPage) {
            totalPages = currentPage;
        }

        if (!summary) {
            summary = document.createElement('div');
            summary.className = 'dm-rcemail-summary-text';
        }

        summary.textContent = totalRecords + ' Records Found, Page ' + currentPage + ' of ' + totalPages;
        summary.setAttribute('aria-label', summary.textContent);

        anchor = findBestSummaryAnchor(row);
        if (anchor && anchor.parentNode && summary.parentNode !== anchor.parentNode) {
            anchor.parentNode.insertBefore(summary, anchor);
        } else if (anchor && anchor.parentNode && summary.nextSibling !== anchor) {
            anchor.parentNode.insertBefore(summary, anchor);
        }
    }

    function findLoosePagerButtons(select) {
        var host = select;
        var depth = 0;
        var links;
        var found;
        var i;
        var el;
        var label;

        while (host && host !== document.body && depth < 6) {
            links = host.querySelectorAll ? host.querySelectorAll('a, button, input[type="button"], input[type="submit"]') : [];
            found = [];
            for (i = 0; i < links.length; i += 1) {
                el = links[i];
                label = textOf(el).toLowerCase();
                if (!isVisible(el)) {
                    continue;
                }
                if (hasClass(el, 'dm-rcemail-search-button')) {
                    continue;
                }
                if (label.indexOf('delete') !== -1 || label.indexOf('modify') !== -1 || label.indexOf('search') !== -1 || label.indexOf('clear') !== -1 || label.indexOf('add') !== -1 || label.indexOf('save') !== -1 || label.indexOf('create') !== -1) {
                    continue;
                }
                found.push(el);
            }
            if (found.length >= 1 && found.length <= 4) {
                return { host: host, controls: found };
            }
            host = host.parentNode;
            depth += 1;
        }
        return null;
    }

    function normalizePagerControls() {
        var itemLimit = findItemLimitSelect();
        var pagination;
        var pageInfo;
        var selectHost;
        var pagerHost = null;
        var loose;
        var row;
        var i;

        if (!itemLimit) {
            return;
        }

        setLimitSelectToTen(itemLimit);
        pagination = findPagination();
        pageInfo = getPaginationNumbers(pagination);
        selectHost = itemLimit.form || itemLimit.parentNode;

        if (pagination) {
            labelPagination(pagination);
            pagerHost = pagination.parentNode;
            addClass(pagerHost, 'dm-rcemail-pager-host');
        } else {
            loose = findLoosePagerButtons(itemLimit);
            if (loose && loose.controls.length) {
                pagerHost = document.createElement('span');
                pagerHost.className = 'dm-rcemail-pager-host';
                loose.host.insertBefore(pagerHost, loose.controls[0]);
                if (loose.controls.length === 1) {
                    labelPagerControl(loose.controls[0], 'Next Page');
                } else {
                    labelPagerControl(loose.controls[0], 'Prev Page');
                    labelPagerControl(loose.controls[loose.controls.length - 1], 'Next Page');
                }
                for (i = 0; i < loose.controls.length; i += 1) {
                    pagerHost.appendChild(loose.controls[i]);
                }
            }
        }

        if (!selectHost) {
            return;
        }

        addClass(selectHost, 'dm-rcemail-limit-host');
        row = document.querySelector('.dm-rcemail-pager-row');
        if (!row) {
            row = document.createElement('div');
            row.className = 'dm-rcemail-pager-row';
            selectHost.parentNode.insertBefore(row, selectHost);
        }

        if (pagerHost && pagerHost.parentNode !== row) {
            row.appendChild(pagerHost);
        }
        if (selectHost.parentNode !== row) {
            row.appendChild(selectHost);
        }

        ensureSummaryText(row, pageInfo);
        hideLooseCommaCountNodes();
        forcePagerLinksToTen(row);
    }

    function run() {
        addClass(document.body, 'dm-rcemail-controls-page');
        moveInlineSearchControls();
        normalizePagerControls();
        forcePagerLinksToTen(document);
    }

    ready(function () {
        run();
        window.setTimeout(run, 250);
        window.setTimeout(run, 900);
        window.setTimeout(run, 1800);
        window.setTimeout(run, 3200);
    });
}());
</script>
HTML;
});
