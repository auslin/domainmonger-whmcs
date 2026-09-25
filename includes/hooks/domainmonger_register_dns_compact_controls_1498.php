<?php
/**
 * DomainMonger Register DNS compact controls — Patch 1558.
 *
 * Corrects the Patch 1498 layout while preserving the confirmed native
 * Register DNS handlers. The original bulk controls remain inside their
 * native toolbar so Apply continues to use the existing Patch 1490 logic;
 * compact proxy controls are displayed below the records table.
 *
 * Also removes the duplicate Records badge, removes the obsolete Priority/TTL
 * helper note, and removes the boxed toolbar appearance.
 *
 * Patch 1547 updates the visible Records counter from the active record-type
 * filter and quick-search text. Patch 1546 targeted a hidden header badge; this
 * file owns the visible compact counter shown beside the page-size control.
 *
 * Patch 1554 updates the visible bottom proxy controls so the TTL field is
 * hidden until Change TTL is selected. This corrects Patch 1553, which changed
 * the hidden native controls rather than the visible compact proxy controls.
 *
 * Patch 1555 reserves the TTL control slot while it is hidden so revealing the
 * field does not change the footer height or move the surrounding controls.
 *
 * Patch 1556 disables the visible Choose Action control until at least one DNS
 * record is selected.
 *
 * Patch 1557 matches DNSPlus more closely: once a row is selected, both Apply
 * and Choose Action become active. Apply remains harmless until an action is
 * selected and directs focus to Choose Action instead of submitting.
 *
 * Patch 1558 standardizes the visible Register DNS toolbar and footer controls
 * at the normal 34px system height and gives Search records the same 170px
 * desktop width used by DNSPlus. Row-action icons and modal controls are not
 * changed.
 *
 * No database changes.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1499, function (array $vars) {
    $filename = strtolower((string) ($vars['filename'] ?? ''));
    $action = strtolower((string) ($_REQUEST['action'] ?? ''));

    $isDnsPage = ($filename === 'clientarea' && $action === 'domaindns')
        || $filename === 'dnsmanagement';

    if (!$isDnsPage) {
        return '';
    }

    return <<<'HTML'
<style id="dm-register-dns-compact-controls-1508">
/* Patch 1507: keep the native bulk controls hidden at all times. The stable
 * bottom proxy controls are the only visible Apply / Choose Action / TTL set,
 * preventing the controls from flashing between the top and bottom when the
 * native form is replaced after Save or Delete. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 > [data-dm-dns-bulk-apply],
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 > .dm-dns-bulk-action-label-1139,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 > .dm-dns-bulk-ttl-label-1139,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-native-bulk-holder-1499 {
    display: none !important;
}

/* The top and bottom control rows should not look like boxed cards. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139.dm-dns-top-controls-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-controls-1499 {
    align-items: center !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    display: flex !important;
    flex-flow: row nowrap !important;
    gap: 10px !important;
    justify-content: space-between !important;
    margin: 8px 0 !important;
    padding: 0 6px !important;
    width: 100% !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-left-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-right-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-right-1499 {
    align-items: center !important;
    display: inline-flex !important;
    flex-flow: row nowrap !important;
    gap: 8px !important;
    white-space: nowrap !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-right-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-right-1499 {
    justify-content: flex-end !important;
    margin-left: auto !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-record-count-1499 {
    align-items: center !important;
    background: #163a5f !important;
    border: 1px solid #163a5f !important;
    border-radius: 6px !important;
    color: #fff !important;
    display: inline-flex !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    height: 32px !important;
    padding: 0 10px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-duplicate-record-count-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-row-1493,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-row-1494,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-row-1493,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-row-1494,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-native-bulk-holder-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-form-1113 > p.text-right.text-muted {
    display: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 {
    gap: 6px !important;
    margin: 0 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 .dm-dns-pagination-1490 button {
    height: 32px !important;
    line-height: 30px !important;
    min-height: 32px !important;
    min-width: 68px !important;
    padding: 0 9px !important;
    width: 68px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-summary-1490 {
    display: inline-block !important;
    flex: 0 0 82px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    max-width: 82px !important;
    min-width: 82px !important;
    overflow: hidden !important;
    padding: 0 2px !important;
    text-align: center !important;
    text-overflow: clip !important;
    white-space: nowrap !important;
    width: 82px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-1490 button:disabled {
    background: #e5e9ed !important;
    border-color: #d7dde3 !important;
    color: #8b97a3 !important;
    opacity: 1 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-add-record-button-1490,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-add-record-bottom-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-apply-proxy-1499 {
    background: #f58220 !important;
    border: 1px solid #f58220 !important;
    border-radius: 6px !important;
    color: #fff !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    height: 32px !important;
    line-height: 30px !important;
    min-height: 32px !important;
    padding: 0 11px !important;
    white-space: nowrap !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-add-record-button-1490:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-add-record-bottom-1499:hover,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-apply-proxy-1499:hover {
    background: #d8741f !important;
    border-color: #d8741f !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-apply-proxy-1499:disabled {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
    cursor: not-allowed !important;
    opacity: .55 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 select,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 input {
    background: #fff !important;
    border: 1px solid #cbd5df !important;
    border-radius: 6px !important;
    color: #163a5f !important;
    height: 32px !important;
    line-height: 30px !important;
    margin: 0 !important;
    min-height: 32px !important;
    vertical-align: middle !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 select:disabled {
    background: #e5e9ed !important;
    border-color: #d7dde3 !important;
    color: #8b97a3 !important;
    cursor: not-allowed !important;
    opacity: 1 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 label {
    align-items: center !important;
    display: inline-flex !important;
    gap: 6px !important;
    margin: 0 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 label.dm-dns-bulk-ttl-proxy-1554 {
    display: inline-flex !important;
    min-height: 32px !important;
    opacity: 0 !important;
    pointer-events: none !important;
    visibility: hidden !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 label.dm-dns-bulk-ttl-proxy-1554.dm-dns-bulk-ttl-proxy-visible-1554 {
    opacity: 1 !important;
    pointer-events: auto !important;
    visibility: visible !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-status-1139 {
    display: none !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 {
    margin: 8px 6px 8px 0 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 input,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 a {
    min-width: 86px !important;
}
/* Patch 1558: normalize the main Register DNS record-page controls.
 * Keep row-action icons and modal controls out of this sizing rule. */
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-left-1499 .dm-dns-record-count-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-left-1499 select,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-right-1499 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-right-1499 .dm-dns-add-record-button-1490,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 select,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 input,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-right-1499 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 input,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 a {
    box-sizing: border-box !important;
    height: 34px !important;
    min-height: 34px !important;
    max-height: 34px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-left-1499 .dm-dns-record-count-1499,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-right-1499 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-right-1499 .dm-dns-add-record-button-1490,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-right-1499 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 button,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 input,
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-live-feed-actions-1113 a {
    line-height: 32px !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499 label.dm-dns-bulk-ttl-proxy-1554 {
    min-height: 34px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-pagination-summary-1490 {
    align-items: center !important;
    display: inline-flex !important;
    height: 34px !important;
    justify-content: center !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-quick-search-label-1545 {
    flex: 0 0 170px !important;
    min-width: 170px !important;
    max-width: 170px !important;
    width: 170px !important;
}
body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139 .dm-dns-quick-search-label-1545 input[type="search"] {
    box-sizing: border-box !important;
    height: 34px !important;
    min-height: 34px !important;
    max-height: 34px !important;
    line-height: 32px !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}

@media (max-width: 900px) {
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bulk-toolbar-1139.dm-dns-top-controls-1499,
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-controls-1499 {
        align-items: flex-start !important;
        flex-flow: column nowrap !important;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-right-1499,
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-right-1499 {
        margin-left: 0 !important;
        width: 100% !important;
    }
}
@media (max-width: 620px) {
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-quick-search-label-1545 {
        flex: 1 1 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-left-1499,
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-top-right-1499,
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-left-1499,
    body.dm-resellerclub-dns-live-feed-1113 .dm-dns-bottom-right-1499 {
        flex-wrap: wrap !important;
        white-space: normal !important;
    }
}
</style>
<script id="dm-register-dns-compact-controls-script-1508">
(function () {
    'use strict';

    function textOf(node) {
        if (!node) { return ''; }
        return String(node.tagName === 'INPUT' ? node.value : node.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function setText(node, value) {
        if (!node) { return; }
        if (node.tagName === 'INPUT') { node.value = value; }
        else { node.textContent = value; }
    }

    function fieldValue1547(row, selector) {
        var field = row ? row.querySelector(selector) : null;
        return field ? String(field.value || '').trim() : '';
    }

    function rowMatchesQuickSearch1547(row, query) {
        if (!query) { return true; }

        var type = row ? row.querySelector('select[name="dnsrecordtype[]"]') : null;
        var address = row ? row.querySelector('input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]') : null;
        var haystack = [
            fieldValue1547(row, 'input[name="dnsrecordhost[]"], input[name="hostname[]"], input[name="host[]"]'),
            type ? String(type.value || '') : '',
            address ? String(address.value || '') : '',
            fieldValue1547(row, 'input[name="dnsrecordttl[]"]'),
            fieldValue1547(row, 'input[name="dnsrecordpriority[]"]')
        ].join(' ').toLowerCase().replace(/\s+/g, ' ').trim();

        return query.split(' ').every(function (token) {
            return !token || haystack.indexOf(token) !== -1;
        });
    }

    function filteredCount(form, table) {
        var filter = form.querySelector('[data-dm-dns-type-filter]');
        var search = form.querySelector('[data-dm-dns-quick-search]');
        var filterValue = filter ? String(filter.value || '').toUpperCase() : '';
        var searchValue = search ? String(search.value || '').toLowerCase().replace(/\s+/g, ' ').trim() : '';

        return Array.prototype.filter.call(table.querySelectorAll('tbody tr'), function (row) {
            if (row.classList.contains('dm-dns-native-add-row-1490')) { return false; }

            var host = fieldValue1547(row, 'input[name="dnsrecordhost[]"], input[name="hostname[]"], input[name="host[]"]');
            var address = fieldValue1547(row, 'input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]');
            var recid = fieldValue1547(row, 'input[name="dnsrecid[]"]');
            if (!host && !address && !recid) { return false; }

            var type = row.querySelector('select[name="dnsrecordtype[]"]');
            if (filterValue && (!type || String(type.value || '').toUpperCase() !== filterValue)) {
                return false;
            }

            return rowMatchesQuickSearch1547(row, searchValue);
        }).length;
    }

    function compactSummary(summary) {
        if (!summary) { return; }
        var value = textOf(summary);
        var match = value.match(/\((\d+)\s*[-–]\s*(\d+)\s+of\s+(\d+)\)/i)
            || value.match(/(\d+)\s*[-–]\s*(\d+)\s+of\s+(\d+)/i);
        if (match) {
            var compact = match[1] + '–' + match[2] + ' of ' + match[3];
            if (value !== compact) {
                summary.textContent = compact;
            }
        }
    }

    function observePagerSummaries1516(form) {
        if (!form || form.dataset.dmPagerSummaryObserver1516 === '1') { return; }

        Array.prototype.forEach.call(form.querySelectorAll('[data-dm-dns-page-summary]'), function (summary) {
            compactSummary(summary);

            var observer = new MutationObserver(function () {
                compactSummary(summary);
            });
            observer.observe(summary, {
                childList: true,
                characterData: true,
                subtree: true
            });
        });

        form.dataset.dmPagerSummaryObserver1516 = '1';
    }

    function normalizePagerText(form) {
        Array.prototype.forEach.call(form.querySelectorAll('[data-dm-dns-page-summary]'), compactSummary);
        var badge = form.querySelector('.dm-dns-record-count-1499');
        var table = form.querySelector('table');
        if (badge && table) {
            var value = filteredCount(form, table);
            badge.textContent = 'Records: ' + value;
            badge.setAttribute('aria-label', 'Records: ' + value);
        }
    }

    function shortenActionLabels(form) {
        Array.prototype.forEach.call(form.querySelectorAll('.dm-dns-live-feed-actions-1113 button, .dm-dns-live-feed-actions-1113 input, .dm-dns-live-feed-actions-1113 a'), function (control) {
            var label = textOf(control);
            if (/^Save Changes$/i.test(label)) { setText(control, 'Save'); }
            if (/^Cancel Changes$/i.test(label)) { setText(control, 'Cancel'); }
        });
    }

    function hideDuplicateRecordBadges(form, primary) {
        var scope = form.closest('.dm-dns-live-feed-panel-1113') || form;
        Array.prototype.forEach.call(scope.querySelectorAll('button, a, span, strong, div'), function (node) {
            if (node === primary || node.contains(primary)) { return; }
            if (/^Records\s*:?\s*\d+$/i.test(textOf(node))) {
                node.classList.add('dm-dns-duplicate-record-count-1499');
                node.setAttribute('aria-hidden', 'true');
            }
        });
        var headerBadge = document.querySelector('#dm-dns-live-feed-page-header-1173 .dm-dns-live-feed-badge-1113');
        if (headerBadge && /^Records\s*:?\s*\d+$/i.test(textOf(headerBadge))) {
            headerBadge.classList.add('dm-dns-duplicate-record-count-1499');
            headerBadge.setAttribute('aria-hidden', 'true');
        }
    }

    function removeHelperText(form) {
        Array.prototype.forEach.call(form.querySelectorAll('p, small'), function (node) {
            if (/Priority is normally used for MX and SRV records/i.test(textOf(node))) {
                var paragraph = node.closest('p');
                (paragraph || node).remove();
            }
        });
    }

    function copyOptions(source, target) {
        target.innerHTML = '';
        Array.prototype.forEach.call(source.options, function (option) {
            var clone = option.cloneNode(true);
            target.appendChild(clone);
        });
        target.value = source.value;
    }

    function applyLayout() {
        var form = document.querySelector('.dm-dns-live-feed-form-1113');
        if (!form || form.dataset.dmCompactControls1499 === '1') { return !!form; }

        var table = form.querySelector('table');
        var toolbar = form.querySelector('.dm-dns-bulk-toolbar-1139');
        var topPager = toolbar && toolbar.querySelector('[data-dm-dns-pager="top"]');
        var bottomPager = form.querySelector('[data-dm-dns-pager="bottom"]');
        var addTop = toolbar && toolbar.querySelector('[data-dm-dns-add-record]');
        var pageSize = toolbar && toolbar.querySelector('.dm-dns-page-size-label-1489');
        var filter = toolbar && toolbar.querySelector('.dm-dns-type-filter-label-1139');
        var nativeApply = toolbar && toolbar.querySelector('[data-dm-dns-bulk-apply]');
        var nativeActionLabel = toolbar && toolbar.querySelector('.dm-dns-bulk-action-label-1139');
        var nativeTtlLabel = toolbar && toolbar.querySelector('.dm-dns-bulk-ttl-label-1139');
        var nativeAction = nativeActionLabel && nativeActionLabel.querySelector('[data-dm-dns-bulk-action]');
        var nativeTtl = nativeTtlLabel && nativeTtlLabel.querySelector('[data-dm-dns-bulk-ttl]');

        if (!table || !toolbar || !topPager || !bottomPager || !addTop || !pageSize || !filter
            || !nativeApply || !nativeActionLabel || !nativeTtlLabel || !nativeAction || !nativeTtl) {
            return false;
        }

        var nativeHolder = document.createElement('div');
        nativeHolder.className = 'dm-dns-native-bulk-holder-1499';
        nativeHolder.appendChild(nativeApply);
        nativeHolder.appendChild(nativeActionLabel);
        nativeHolder.appendChild(nativeTtlLabel);
        toolbar.appendChild(nativeHolder);

        var topLeft = document.createElement('div');
        topLeft.className = 'dm-dns-top-left-1499';
        var countBadge = document.createElement('span');
        countBadge.className = 'dm-dns-record-count-1499';
        topLeft.appendChild(countBadge);
        topLeft.appendChild(pageSize);
        topLeft.appendChild(filter);

        var topRight = document.createElement('div');
        topRight.className = 'dm-dns-top-right-1499';
        topRight.appendChild(topPager);
        topRight.appendChild(addTop);

        toolbar.classList.add('dm-dns-top-controls-1499');
        toolbar.insertBefore(topLeft, toolbar.firstChild);
        toolbar.appendChild(topRight);

        var bottom = document.createElement('div');
        bottom.className = 'dm-dns-bottom-controls-1499';
        var bottomLeft = document.createElement('div');
        bottomLeft.className = 'dm-dns-bottom-left-1499';

        var proxyApply = document.createElement('button');
        proxyApply.type = 'button';
        proxyApply.className = 'dm-dns-bulk-apply-proxy-1499';
        proxyApply.textContent = 'Apply';

        var proxyActionLabel = document.createElement('label');
        var proxyAction = document.createElement('select');
        proxyAction.setAttribute('aria-label', 'Bulk action');
        copyOptions(nativeAction, proxyAction);
        proxyActionLabel.appendChild(proxyAction);

        var proxyTtlLabel = document.createElement('label');
        proxyTtlLabel.className = 'dm-dns-bulk-ttl-proxy-1554';
        proxyTtlLabel.appendChild(document.createTextNode('TTL '));
        var proxyTtl = document.createElement('input');
        proxyTtl.type = 'text';
        proxyTtl.value = nativeTtl.value || '14400';
        proxyTtl.disabled = true;
        proxyTtl.setAttribute('aria-label', 'Bulk TTL');
        proxyTtlLabel.appendChild(proxyTtl);

        bottomLeft.appendChild(proxyApply);
        bottomLeft.appendChild(proxyActionLabel);
        bottomLeft.appendChild(proxyTtlLabel);

        var bottomRight = document.createElement('div');
        bottomRight.className = 'dm-dns-bottom-right-1499';
        bottomRight.appendChild(bottomPager);
        var addBottom = document.createElement('button');
        addBottom.type = 'button';
        addBottom.className = 'dm-dns-add-record-bottom-1499';
        addBottom.textContent = '+ Add Record';
        addBottom.addEventListener('click', function (event) {
            event.preventDefault();
            addTop.click();
        });
        bottomRight.appendChild(addBottom);

        bottom.appendChild(bottomLeft);
        bottom.appendChild(bottomRight);

        var tableHost = table.closest('.dm-dns-live-feed-scroll-1113, .table-responsive') || table;
        tableHost.parentNode.insertBefore(bottom, tableHost.nextSibling);

        function syncProxyTtlVisibility1554() {
            var ttlSelected = proxyAction.value === 'ttl';
            proxyTtlLabel.classList.toggle('dm-dns-bulk-ttl-proxy-visible-1554', ttlSelected);
            proxyTtlLabel.setAttribute('aria-hidden', ttlSelected ? 'false' : 'true');
            proxyTtl.disabled = !ttlSelected;
        }

        function syncProxyState() {
            var hasSelectedRecord = !!form.querySelector('.dm-dns-row-select:checked');

            proxyAction.disabled = !hasSelectedRecord;
            proxyApply.disabled = !hasSelectedRecord;

            if (proxyAction.value !== nativeAction.value) { proxyAction.value = nativeAction.value; }
            if (document.activeElement !== proxyTtl && proxyTtl.value !== nativeTtl.value) {
                proxyTtl.value = nativeTtl.value;
            }
            syncProxyTtlVisibility1554();
        }

        proxyAction.addEventListener('change', function () {
            nativeAction.value = proxyAction.value;
            syncProxyTtlVisibility1554();
            nativeAction.dispatchEvent(new Event('change', { bubbles: true }));
            window.setTimeout(syncProxyState, 0);
        });
        proxyTtl.addEventListener('input', function () {
            nativeTtl.value = proxyTtl.value;
            nativeTtl.dispatchEvent(new Event('input', { bubbles: true }));
        });
        proxyApply.addEventListener('click', function (event) {
            event.preventDefault();
            if (!proxyAction.value) {
                proxyAction.focus();
                return;
            }
            nativeAction.value = proxyAction.value;
            nativeTtl.value = proxyTtl.value;
            nativeApply.click();
            window.setTimeout(syncProxyState, 0);
            window.setTimeout(syncProxyState, 40);
        });

        form.addEventListener('change', function (event) {
            if (event.target.matches('.dm-dns-row-select, .dm-dns-select-all, [data-dm-dns-page-size], [data-dm-dns-type-filter], select[name="dnsrecordtype[]"]')) {
                window.setTimeout(function () {
                    syncProxyState();
                    normalizePagerText(form);
                }, 0);
            }
        });
        form.addEventListener('input', function (event) {
            if (event.target.matches('[data-dm-dns-quick-search]')) {
                window.setTimeout(function () {
                    normalizePagerText(form);
                }, 0);
            }
        });
        form.addEventListener('click', function (event) {
            if (event.target.closest('[data-dm-dns-page-prev], [data-dm-dns-page-next], .dm-dns-row-select, .dm-dns-select-all')) {
                window.setTimeout(function () {
                    syncProxyState();
                    normalizePagerText(form);
                }, 0);
                window.setTimeout(function () {
                    syncProxyState();
                    normalizePagerText(form);
                }, 40);
            }
        });

        removeHelperText(form);
        shortenActionLabels(form);
        hideDuplicateRecordBadges(form, countBadge);
        normalizePagerText(form);
        observePagerSummaries1516(form);
        syncProxyState();

        form.dataset.dmCompactControls1499 = '1';
        return true;
    }

    function start() {
        /* Patch 1507: keep a lightweight idempotent check running because the
         * DNS feed replaces the native form after Save/Delete. applyLayout()
         * exits immediately for an already-enhanced form, but applies the
         * compact layout to each newly rendered form without moving controls
         * back and forth. */
        applyLayout();
        window.setInterval(function () {
            applyLayout();
        }, 100);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
}());
</script>
HTML;
});
