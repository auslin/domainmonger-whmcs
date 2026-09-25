<?php
/**
 * DomainMonger ResellerClub DNS controls alignment.
 *
 * Patch 725: rebuilds the DNS Management/View All Records controls fix from
 * the last working Patch 723 approach and removes the Patch 724 regression.
 * Page-specific to dnsmanagement.php record-list views.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_rcdns_controls_alignment_is_dns_page')) {
    function dm_rcdns_controls_alignment_is_dns_page(): bool
    {
        $script = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
        $action = (string)($_REQUEST['action'] ?? '');

        if ($script !== 'dnsmanagement.php') {
            return false;
        }

        return in_array($action, ['managednszone', 'managednszoneview'], true);
    }
}

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    if (!dm_rcdns_controls_alignment_is_dns_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-rcdns-controls-alignment-css-v725">
body.dm-rcdns-controls-page .dm-rcdns-search-inline {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 8px !important;
    width: auto !important;
    max-width: 440px !important;
    min-width: 0 !important;
    margin: 8px 0 12px !important;
    padding: 0 !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
}

body.dm-rcdns-controls-page .dm-rcdns-search-inline input[type="text"],
body.dm-rcdns-controls-page .dm-rcdns-search-inline input[type="search"],
body.dm-rcdns-controls-page .dm-rcdns-search-inline .dm-rcdns-search-input {
    flex: 0 1 210px !important;
    width: 210px !important;
    min-width: 150px !important;
    max-width: 260px !important;
    margin: 0 !important;
    vertical-align: middle !important;
}

body.dm-rcdns-controls-page .dm-rcdns-search-inline button,
body.dm-rcdns-controls-page .dm-rcdns-search-inline input[type="submit"],
body.dm-rcdns-controls-page .dm-rcdns-search-inline .dm-rcdns-search-button {
    flex: 0 0 auto !important;
    margin: 0 !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
}

body.dm-rcdns-controls-page .dm-rcdns-pager-row {
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

body.dm-rcdns-controls-page .dm-rcdns-pager-row .pull-left,
body.dm-rcdns-controls-page .dm-rcdns-pager-row .pull-right,
body.dm-rcdns-controls-page .dm-rcdns-pager-row .text-left,
body.dm-rcdns-controls-page .dm-rcdns-pager-row .text-right,
body.dm-rcdns-controls-page .dm-rcdns-pager-row form,
body.dm-rcdns-controls-page .dm-rcdns-limit-host,
body.dm-rcdns-controls-page .dm-rcdns-pager-host {
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

body.dm-rcdns-controls-page .dm-rcdns-pager-host {
    order: 1 !important;
    gap: 5px !important;
}

body.dm-rcdns-controls-page .dm-rcdns-limit-host {
    order: 2 !important;
}

body.dm-rcdns-controls-page .dm-rcdns-pager-row select.dm-rcdns-itemlimit {
    display: inline-block !important;
    flex: 0 0 152px !important;
    width: 152px !important;
    min-width: 152px !important;
    max-width: 152px !important;
    height: 32px !important;
    margin: 0 !important;
    padding: 5px 24px 5px 8px !important;
    vertical-align: middle !important;
}

body.dm-rcdns-controls-page .dm-rcdns-pager-row .pagination,
body.dm-rcdns-controls-page .dm-rcdns-pager-host .pagination {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 5px !important;
    width: auto !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
}

body.dm-rcdns-controls-page .dm-rcdns-pager-row .pagination > li {
    display: inline-flex !important;
    margin: 0 !important;
    padding: 0 !important;
}

body.dm-rcdns-controls-page .dm-rcdns-pager-row .pagination > li > a,
body.dm-rcdns-controls-page .dm-rcdns-pager-row .pagination > li > span,
body.dm-rcdns-controls-page .dm-rcdns-pager-host a,
body.dm-rcdns-controls-page .dm-rcdns-pager-host button,
body.dm-rcdns-controls-page .dm-rcdns-pager-host input[type="button"],
body.dm-rcdns-controls-page .dm-rcdns-pager-host input[type="submit"] {
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

body.dm-rcdns-controls-page .dm-rcdns-pager-row + br,
body.dm-rcdns-controls-page br.dm-rcdns-remove-br {
    display: none !important;
}

@media (max-width: 700px) {
    body.dm-rcdns-controls-page .dm-rcdns-search-inline {
        width: 100% !important;
        max-width: 100% !important;
        flex-wrap: nowrap !important;
    }

    body.dm-rcdns-controls-page .dm-rcdns-search-inline input[type="text"],
    body.dm-rcdns-controls-page .dm-rcdns-search-inline input[type="search"] {
        flex: 1 1 auto !important;
        width: auto !important;
        max-width: none !important;
    }

    body.dm-rcdns-controls-page .dm-rcdns-pager-row {
        justify-content: flex-start !important;
        flex-wrap: wrap !important;
        text-align: left !important;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    if (!dm_rcdns_controls_alignment_is_dns_page()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-rcdns-controls-alignment-js-v725">
(function () {
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

    function closestByClass(el, className) {
        while (el && el !== document) {
            if (hasClass(el, className)) {
                return el;
            }
            el = el.parentNode;
        }
        return null;
    }

    function textOf(el) {
        if (!el) {
            return '';
        }
        return (el.value || el.getAttribute('aria-label') || el.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function isVisible(el) {
        if (!el) {
            return false;
        }
        if (el.type && String(el.type).toLowerCase() === 'hidden') {
            return false;
        }
        var style = window.getComputedStyle ? window.getComputedStyle(el) : null;
        if (style && (style.display === 'none' || style.visibility === 'hidden')) {
            return false;
        }
        return true;
    }

    function findSearchInputAndButton() {
        var forms = document.getElementsByTagName('form');
        var i;
        for (i = 0; i < forms.length; i += 1) {
            var form = forms[i];
            var inputs = form.querySelectorAll('input[type="text"], input[type="search"]');
            var buttons = form.querySelectorAll('button, input[type="submit"], input[type="button"]');
            var input = null;
            var button = null;
            var j;

            for (j = 0; j < inputs.length; j += 1) {
                if (isVisible(inputs[j])) {
                    input = inputs[j];
                    break;
                }
            }

            for (j = 0; j < buttons.length; j += 1) {
                var label = textOf(buttons[j]).toLowerCase();
                if (isVisible(buttons[j]) && (label === 'search' || label.indexOf('search') !== -1)) {
                    button = buttons[j];
                    break;
                }
            }

            if (input && button) {
                return { input: input, button: button, form: form };
            }
        }
        return null;
    }

    function normalizeSearchControls() {
        var found = findSearchInputAndButton();
        if (!found) {
            return;
        }

        var input = found.input;
        var button = found.button;
        var wrapper = closestByClass(input, 'dm-rcdns-search-inline');

        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'dm-rcdns-search-inline';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
        }

        if (button.parentNode !== wrapper) {
            wrapper.appendChild(button);
        }

        addClass(input, 'dm-rcdns-search-input');
        addClass(button, 'dm-rcdns-search-button');

        var brs = found.form.getElementsByTagName('br');
        var brList = [];
        var i;
        for (i = 0; i < brs.length; i += 1) {
            brList.push(brs[i]);
        }
        for (i = 0; i < brList.length; i += 1) {
            var br = brList[i];
            if (br.parentNode && (br.parentNode === wrapper || br.parentNode === found.form)) {
                addClass(br, 'dm-rcdns-remove-br');
            }
        }
    }

    function selectLooksLikeLimit(select) {
        if (!select || !select.options || !select.options.length) {
            return false;
        }

        var identity = ((select.id || '') + ' ' + (select.name || '')).toLowerCase();
        if (identity.indexOf('limit') !== -1 || identity.indexOf('page') !== -1) {
            return true;
        }

        var numeric = 0;
        var i;
        for (i = 0; i < select.options.length; i += 1) {
            var raw = (select.options[i].value || select.options[i].textContent || '').replace(/\s+/g, '');
            if (/^\d{1,3}$/.test(raw)) {
                numeric += 1;
            }
        }
        return numeric > 0;
    }

    function findItemLimitSelect() {
        var direct = document.querySelector('select#itemlimit, select[name="itemlimit"]');
        if (direct) {
            return direct;
        }

        var selects = document.getElementsByTagName('select');
        var i;
        for (i = 0; i < selects.length; i += 1) {
            if (selectLooksLikeLimit(selects[i])) {
                return selects[i];
            }
        }
        return null;
    }

    function labelLimitSelect(select) {
        if (!select || !select.options || !select.options.length) {
            return;
        }

        addClass(select, 'dm-rcdns-itemlimit');
        select.setAttribute('aria-label', 'Records Per Page');
        select.title = 'Records Per Page';

        var index = select.selectedIndex >= 0 ? select.selectedIndex : 0;
        var option = select.options[index];
        if (option && option.getAttribute('data-dm-rcdns-label') !== '1') {
            option.setAttribute('data-dm-rcdns-original-text', option.textContent || '');
            option.textContent = 'Records Per Page';
            option.setAttribute('data-dm-rcdns-label', '1');
        }
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

    function findPagination() {
        return document.querySelector('ul.pagination, .pagination');
    }

    function labelPagination(pagination) {
        if (!pagination) {
            return;
        }

        var controls = pagination.querySelectorAll('a, span, button, input[type="button"], input[type="submit"]');
        var usable = [];
        var i;
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

    function findLoosePagerButtons(select) {
        var host = select;
        var depth = 0;
        while (host && host !== document.body && depth < 5) {
            var links = host.querySelectorAll ? host.querySelectorAll('a, button, input[type="button"], input[type="submit"]') : [];
            var found = [];
            var i;
            for (i = 0; i < links.length; i += 1) {
                var el = links[i];
                var label = textOf(el).toLowerCase();
                if (!isVisible(el)) {
                    continue;
                }
                if (hasClass(el, 'dm-rcdns-search-button')) {
                    continue;
                }
                if (label.indexOf('delete') !== -1 || label.indexOf('modify') !== -1 || label.indexOf('search') !== -1 || label.indexOf('clear') !== -1 || label.indexOf('add') !== -1 || label.indexOf('save') !== -1) {
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
        if (!itemLimit) {
            return;
        }

        labelLimitSelect(itemLimit);

        var pagination = findPagination();
        var selectHost = itemLimit.form || itemLimit.parentNode;
        var pagerHost = null;

        if (pagination) {
            labelPagination(pagination);
            pagerHost = closestByClass(pagination, 'pull-left') || pagination.parentNode;
        } else {
            var loose = findLoosePagerButtons(itemLimit);
            if (loose && loose.controls.length) {
                pagerHost = document.createElement('span');
                pagerHost.className = 'dm-rcdns-pager-host';
                loose.host.insertBefore(pagerHost, loose.controls[0]);
                if (loose.controls.length === 1) {
                    labelPagerControl(loose.controls[0], 'Next Page');
                } else {
                    labelPagerControl(loose.controls[0], 'Prev Page');
                    labelPagerControl(loose.controls[loose.controls.length - 1], 'Next Page');
                }
                var i;
                for (i = 0; i < loose.controls.length; i += 1) {
                    pagerHost.appendChild(loose.controls[i]);
                }
            }
        }

        if (!selectHost) {
            return;
        }

        addClass(selectHost, 'dm-rcdns-limit-host');
        if (pagerHost) {
            addClass(pagerHost, 'dm-rcdns-pager-host');
        }

        var row = closestByClass(selectHost, 'dm-rcdns-pager-row') || (pagerHost ? closestByClass(pagerHost, 'dm-rcdns-pager-row') : null);
        if (!row) {
            row = document.createElement('div');
            row.className = 'dm-rcdns-pager-row';
            selectHost.parentNode.insertBefore(row, selectHost);
        }

        if (pagerHost && pagerHost.parentNode !== row) {
            row.appendChild(pagerHost);
        }
        if (selectHost.parentNode !== row) {
            row.appendChild(selectHost);
        }
    }

    function run() {
        addClass(document.body, 'dm-rcdns-controls-page');
        normalizeSearchControls();
        normalizePagerControls();
    }

    ready(function () {
        run();
        window.setTimeout(run, 250);
        window.setTimeout(run, 900);
        window.setTimeout(run, 1800);
    });
})();
</script>
HTML;
});
