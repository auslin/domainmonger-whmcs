<?php
/**
 * DomainMonger patch 1020: Unified DNS Records recovery hook.
 *
 * Builds a ClouDNS-style combined DNS records interface on the ResellerClub
 * DNS Management page without changing backend routes, forms, or actions.
 *
 * Patch 1096 update:
 * - The fast native DNS page is now the live route.
 * - Keep this legacy unified renderer available only when a bypass flag is used:
 *     &dmlegacydns=1 or &dmnoredirect=1
 *
 * Patch 1098 update:
 * - Keep this older recovery renderer out of the normal legacy fallback too.
 * - The primary legacy unified hook 1010 remains the default bypass renderer.
 * - This recovery copy now runs only with an explicit recovery flag:
 *     &dmlegacydns=1&dmrecoverydns=1
 *   or
 *     &dmnoredirect=1&dmrecoverydns=1
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1009, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

    if ($scriptName !== 'dnsmanagement.php' || $action !== 'managednszone') {
        return '';
    }

    // The live DNS Management route now redirects to the fast native DNS page.
    // Keep the primary legacy renderer (1010) as the normal fallback. This
    // recovery hook now runs only when explicitly requested, so the legacy
    // fallback does not render duplicate unified DNS shells/scripts.
    $legacyRequested = isset($_GET['dmlegacydns']) || isset($_GET['dmnoredirect']);
    $recoveryRequested = isset($_GET['dmrecoverydns']);

    if (!$legacyRequested || !$recoveryRequested) {
        return '';
    }

    $domainId = (int) ($_GET['domainid'] ?? $_POST['domainid'] ?? $_GET['id'] ?? $_POST['id'] ?? 0);
    $domain = trim((string) ($_GET['domain'] ?? $_POST['domain'] ?? ''));
    $freednsHosting = trim((string) ($_GET['freednshosting'] ?? $_POST['freednshosting'] ?? ''));

    if ($domainId <= 0 || $domain === '') {
        return '';
    }

    $domainJson = json_encode($domain, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $domainIdJson = json_encode($domainId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $freednsJson = json_encode($freednsHosting, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<style id="dm-dns-records-unified-1020-style">
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-shell {
    background: #fff;
    border: 1px solid rgba(17, 43, 77, .14);
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(17, 43, 77, .055);
    margin: 0 0 18px;
    overflow: hidden;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-head {
    align-items: center;
    background: #163a5f;
    color: #fff;
    display: flex;
    justify-content: space-between;
    gap: 14px;
    padding: 13px 16px;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-head strong {
    color: #fff;
    display: block;
    font-size: 16px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-head span {
    color: rgba(255,255,255,.8);
    display: block;
    font-size: 12px;
    margin-top: 2px;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-zone {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 6px;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    padding: 7px 10px;
    white-space: nowrap;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-toolbar {
    align-items: center;
    background: #f8fafc;
    border-bottom: 1px solid rgba(17, 43, 77, .10);
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-count,
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-toolbar select,
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-toolbar input {
    background: #fff;
    border: 1px solid rgba(17, 43, 77, .16) !important;
    border-radius: 6px !important;
    color: #293f56;
    font-size: 12px;
    height: 34px;
    line-height: 32px;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-count {
    font-weight: 800;
    padding: 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-toolbar select {
    min-width: 150px;
    padding: 0 28px 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-toolbar input {
    min-width: 190px;
    padding: 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-toolbar-spacer {
    flex: 1 1 auto;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-button,
body.whmcsbody.dm-dns-unified .dm-dns-records-button:visited,
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-shell .btn.dm-dns-records-button {
    align-items: center;
    border: 0 !important;
    border-radius: 6px !important;
    display: inline-flex;
    font-size: 12px !important;
    font-weight: 800 !important;
    height: 34px;
    justify-content: center;
    line-height: 1 !important;
    padding: 0 13px !important;
    text-decoration: none !important;
    white-space: nowrap;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-button.dm-primary {
    background: #f58220 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-button.dm-primary:hover,
body.whmcsbody.dm-dns-unified .dm-dns-records-button.dm-primary:focus {
    background: #d8741f !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-button.dm-danger {
    background: #b94a48 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-button.dm-secondary {
    background: #163a5f !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-table-wrap {
    overflow-x: auto;
    padding: 12px;
}
body.whmcsbody.dm-dns-unified table.dm-dns-records-unified-table {
    border-collapse: separate;
    border-spacing: 0;
    margin: 0;
    min-width: 860px;
    width: 100%;
}
body.whmcsbody.dm-dns-unified table.dm-dns-records-unified-table thead th {
    background: #163a5f !important;
    border: 0 !important;
    border-right: 1px solid rgba(255,255,255,.24) !important;
    color: #fff !important;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .03em;
    padding: 11px 10px !important;
    text-transform: uppercase;
    vertical-align: middle !important;
}
body.whmcsbody.dm-dns-unified table.dm-dns-records-unified-table thead th:first-child {
    border-top-left-radius: 7px;
    text-align: center;
    width: 34px;
}
body.whmcsbody.dm-dns-unified table.dm-dns-records-unified-table thead th:last-child {
    border-right: 0 !important;
    border-top-right-radius: 7px;
    text-align: right;
    width: 132px;
}
body.whmcsbody.dm-dns-unified table.dm-dns-records-unified-table tbody td {
    background: #fff;
    border: 0 !important;
    border-bottom: 1px solid rgba(17,43,77,.09) !important;
    border-right: 1px solid rgba(17,43,77,.07) !important;
    color: #293f56;
    font-size: 12px;
    padding: 9px 10px !important;
    vertical-align: middle !important;
}
body.whmcsbody.dm-dns-unified table.dm-dns-records-unified-table tbody td:last-child {
    border-right: 0 !important;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-type-pill {
    background: #f58220;
    border-radius: 5px;
    color: #fff;
    display: inline-block;
    font-size: 10px;
    font-weight: 900;
    min-width: 48px;
    padding: 4px 7px;
    text-align: center;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-value {
    max-width: 310px;
    overflow-wrap: anywhere;
    word-break: break-word;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-extra {
    color: #60738a;
    display: block;
    font-size: 11px;
    margin-top: 3px;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-actions {
    align-items: center;
    display: flex;
    gap: 5px;
    justify-content: flex-end;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-actions form {
    display: inline-flex;
    margin: 0 !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-actions input[type="submit"] {
    border: 0 !important;
    border-radius: 5px !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    height: 26px !important;
    line-height: 26px !important;
    min-width: 58px !important;
    padding: 0 10px !important;
    width: auto !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-actions input[value="Modify"] {
    background: #163a5f !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-actions input[value="Delete"] {
    background: #b94a48 !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-actions .dm-action-label {
    display: none;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-empty,
body.whmcsbody.dm-dns-unified .dm-dns-records-loading,
body.whmcsbody.dm-dns-unified .dm-dns-records-error {
    background: #fff7ef;
    border: 1px solid rgba(245, 130, 32, .22);
    border-radius: 7px;
    color: #293f56;
    font-size: 13px;
    margin: 12px;
    padding: 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-error {
    background: #f9eeee;
    border-color: rgba(185, 74, 72, .28);
}
body.whmcsbody.dm-dns-unified .dm-dns-records-legacy-hidden > :not(.dm-dns-records-unified-shell) {
    display: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-shell input[type="checkbox"] {
    accent-color: #f58220;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-shell .dm-hidden-form-source {
    display: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-records-unified-shell .dm-dns-record-actions-loading {
    color: #6b7682;
    font-size: 12px;
    font-weight: 700;
}
@media (max-width: 767px) {
    body.whmcsbody.dm-dns-unified .dm-dns-records-unified-head {
        align-items: flex-start;
        flex-direction: column;
    }
    body.whmcsbody.dm-dns-unified .dm-dns-records-toolbar-spacer {
        display: none;
    }
    body.whmcsbody.dm-dns-unified .dm-dns-records-unified-toolbar input,
    body.whmcsbody.dm-dns-unified .dm-dns-records-unified-toolbar select {
        min-width: 100%;
        width: 100%;
    }
}
</style>
<script id="dm-dns-records-unified-1020-script">
(function () {
    var dmDns1010Domain = {$domainJson};
    var dmDns1010DomainId = {$domainIdJson};
    var dmDns1010FreeDnsHosting = {$freednsJson};
    var dmDns1010RecordTypes = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SRV'];
    var dmDns1010Built = false;

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getText(node) {
        return (node ? node.textContent : '').replace(/\s+/g, ' ').trim();
    }

    function currentBaseUrl(type) {
        var url = new URL(window.location.href);
        url.hash = '';
        url.searchParams.set('action', 'managednszone');
        url.searchParams.set('domainid', dmDns1010DomainId);
        url.searchParams.set('domain', dmDns1010Domain);
        url.searchParams.set('nsrecordtype', type || 'A');
        url.searchParams.set('itemlimit', 'all');
        url.searchParams.delete('q');
        url.searchParams.delete('page');
        return url;
    }

    function addUrl(type) {
        var url = new URL(window.location.href);
        url.pathname = url.pathname.replace(/\/[^\/]*$/, '/dnsmanagement.php');
        url.hash = '';
        url.search = '';
        url.searchParams.set('action', 'managednszoneadd');
        url.searchParams.set('domainid', dmDns1010DomainId);
        url.searchParams.set('domain', dmDns1010Domain);
        url.searchParams.set('nsrecordtype', type || 'A');
        if (dmDns1010FreeDnsHosting !== '') {
            url.searchParams.set('freednshosting', dmDns1010FreeDnsHosting);
        }
        return url.toString();
    }

    function findRecordTable(doc) {
        var tables = doc.querySelectorAll('table.dm-rcdns-zone-table, table.table');
        for (var i = 0; i < tables.length; i++) {
            var text = getText(tables[i]).toLowerCase();
            if (text.indexOf('name') !== -1 && text.indexOf('value') !== -1 && text.indexOf('actions') !== -1) {
                return tables[i];
            }
        }
        return null;
    }

    function normalizeHost(host) {
        host = String(host || '').trim();
        if (!host) {
            return '';
        }
        return host.replace(/\s+/g, ' ');
    }

    function normalizeValue(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function readInput(form, name) {
        var field = form ? form.querySelector('[name="' + name + '"]') : null;
        return field ? field.value : '';
    }

    function extractRowsFromDocument(doc, type) {
        var table = findRecordTable(doc);
        var records = [];
        if (!table) {
            return records;
        }
        var rows = table.querySelectorAll('tr');
        for (var i = 1; i < rows.length; i++) {
            var row = rows[i];
            var rowText = getText(row).toLowerCase();
            if (!rowText || rowText.indexOf('no records found') !== -1 || rowText.indexOf('delete selected') !== -1 || rowText.indexOf('clear search') !== -1) {
                continue;
            }
            var modifyForm = row.querySelector('form[action*="managednszonemodify"]');
            var deleteForm = row.querySelector('form[action*="managednszone"] input[name="delete"][value="true"]');
            deleteForm = deleteForm ? deleteForm.closest('form') : null;
            if (!modifyForm && !deleteForm) {
                continue;
            }
            var cells = row.children;
            var host = normalizeHost(readInput(modifyForm, 'host') || (cells[0] ? getText(cells[0]) : ''));
            var value = normalizeValue(readInput(modifyForm, 'value') || (cells[1] ? getText(cells[1]) : ''));
            var ttl = normalizeValue(readInput(modifyForm, 'ttl') || (cells[2] ? getText(cells[2]) : ''));
            var priority = normalizeValue(readInput(modifyForm, 'priority'));
            var weight = normalizeValue(readInput(modifyForm, 'weight'));
            var port = normalizeValue(readInput(modifyForm, 'port'));
            var statusCellIndex = (type === 'SRV') ? 6 : ((type === 'MX') ? 4 : 3);
            var status = cells[statusCellIndex] ? getText(cells[statusCellIndex]) : 'Active';
            var bulk = row.querySelector('input[name="multidelete[]"]');
            var actionsHtml = '';
            if (modifyForm) {
                actionsHtml += '<span class="dm-hidden-form-source">Modify</span>' + modifyForm.outerHTML;
            }
            if (deleteForm) {
                actionsHtml += '<span class="dm-hidden-form-source">Delete</span>' + deleteForm.outerHTML;
            }
            if (!host && !value) {
                continue;
            }
            records.push({
                type: type,
                host: host,
                value: value,
                ttl: ttl,
                priority: priority,
                weight: weight,
                port: port,
                status: status || 'Active',
                bulkValue: bulk ? bulk.value : '',
                actionsHtml: actionsHtml
            });
        }
        return records;
    }

    function createShell(moduleArea) {
        var shell = document.createElement('div');
        shell.className = 'dm-dns-records-unified-shell';
        shell.innerHTML = ''
            + '<div class="dm-dns-records-unified-head">'
            + '  <div><strong>DNS Records</strong><span>Combined records view for ' + escapeHtml(dmDns1010Domain) + '</span></div>'
            + '  <div class="dm-dns-records-unified-zone">Backend actions preserved</div>'
            + '</div>'
            + '<div class="dm-dns-records-unified-toolbar">'
            + '  <div class="dm-dns-records-count">Records: <span data-dm-record-count>0</span></div>'
            + '  <select data-dm-record-filter aria-label="Filter DNS records"><option value="ALL">All Records</option><option value="A">A</option><option value="AAAA">AAAA</option><option value="CNAME">CNAME</option><option value="MX">MX</option><option value="NS">NS</option><option value="TXT">TXT</option><option value="SRV">SRV</option></select>'
            + '  <input type="search" data-dm-record-search placeholder="Search Hosts" aria-label="Search DNS record hosts and values">'
            + '  <button type="button" class="dm-dns-records-button dm-secondary" data-dm-record-search-button>Search Hosts</button>'
            + '  <div class="dm-dns-records-toolbar-spacer"></div>'
            + '  <button type="button" class="dm-dns-records-button dm-danger" data-dm-record-delete>Delete Selected</button>'
            + '  <a class="dm-dns-records-button dm-primary" data-dm-record-add href="' + escapeHtml(addUrl('A')) + '">+Add</a>'
            + '</div>'
            + '<div class="dm-dns-records-loading">Loading combined DNS records...</div>';
        moduleArea.insertBefore(shell, moduleArea.firstChild);
        moduleArea.classList.add('dm-dns-records-legacy-hidden');
        return shell;
    }

    function renderTable(shell, records) {
        var loading = shell.querySelector('.dm-dns-records-loading');
        if (loading) {
            loading.remove();
        }
        var existing = shell.querySelector('.dm-dns-records-table-wrap, .dm-dns-records-empty, .dm-dns-records-error');
        if (existing) {
            existing.remove();
        }
        if (!records.length) {
            var empty = document.createElement('div');
            empty.className = 'dm-dns-records-empty';
            empty.textContent = 'No DNS records found for the selected filter.';
            shell.appendChild(empty);
            updateCount(shell, records);
            return;
        }
        syncFilterOptions(shell, records);
        var wrap = document.createElement('div');
        wrap.className = 'dm-dns-records-table-wrap';
        wrap.innerHTML = '<table class="table dm-dns-records-unified-table"><thead><tr>'
            + '<th><input type="checkbox" data-dm-record-master aria-label="Select all visible records"></th>'
            + '<th>Host</th><th>Type</th><th>Points To</th><th>TTL</th><th>Status</th><th>Actions</th>'
            + '</tr></thead><tbody></tbody></table>';
        var tbody = wrap.querySelector('tbody');
        for (var i = 0; i < records.length; i++) {
            var record = records[i];
            var extra = [];
            if (record.priority) { extra.push('Priority: ' + record.priority); }
            if (record.weight) { extra.push('Weight: ' + record.weight); }
            if (record.port) { extra.push('Port: ' + record.port); }
            var tr = document.createElement('tr');
            tr.setAttribute('data-dm-record-type', record.type);
            tr.setAttribute('data-dm-record-search-text', (record.host + ' ' + record.type + ' ' + record.value + ' ' + extra.join(' ')).toLowerCase());
            tr.innerHTML = ''
                + '<td><input type="checkbox" data-dm-record-check data-record-type="' + escapeHtml(record.type) + '" data-bulk-value="' + escapeHtml(record.bulkValue) + '"></td>'
                + '<td>' + escapeHtml(record.host) + '</td>'
                + '<td><span class="dm-dns-record-type-pill">' + escapeHtml(record.type) + '</span></td>'
                + '<td><div class="dm-dns-record-value">' + escapeHtml(record.value) + (extra.length ? '<span class="dm-dns-record-extra">' + escapeHtml(extra.join(' · ')) + '</span>' : '') + '</div></td>'
                + '<td>' + escapeHtml(record.ttl || '-') + '</td>'
                + '<td>' + escapeHtml(record.status || 'Active') + '</td>'
                + '<td><div class="dm-dns-record-actions">' + record.actionsHtml + '</div></td>';
            tbody.appendChild(tr);
        }
        shell.appendChild(wrap);
        updateCount(shell, records);
        bindTableEvents(shell);
    }

    function updateCount(shell) {
        var count = shell.querySelectorAll('tbody tr:not([hidden])').length;
        var countNode = shell.querySelector('[data-dm-record-count]');
        if (countNode) {
            countNode.textContent = count;
        }
    }

    function applyFilters(shell) {
        var filter = shell.querySelector('[data-dm-record-filter]');
        var search = shell.querySelector('[data-dm-record-search]');
        var selectedType = filter ? filter.value : 'ALL';
        var q = search ? search.value.toLowerCase().trim() : '';
        var add = shell.querySelector('[data-dm-record-add]');
        if (add) {
            add.href = addUrl(selectedType === 'ALL' ? 'A' : selectedType);
            add.textContent = selectedType === 'ALL' ? '+Add' : '+Add ' + selectedType;
        }
        var rows = shell.querySelectorAll('tbody tr');
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var typeOk = selectedType === 'ALL' || row.getAttribute('data-dm-record-type') === selectedType;
            var searchOk = !q || (row.getAttribute('data-dm-record-search-text') || '').indexOf(q) !== -1;
            row.hidden = !(typeOk && searchOk);
        }
        updateCount(shell);
    }

    function bindTableEvents(shell) {
        if (shell.getAttribute('data-dm-bound') === '1') {
            return;
        }
        shell.setAttribute('data-dm-bound', '1');
        shell.addEventListener('change', function (event) {
            if (event.target.matches('[data-dm-record-filter]')) {
                applyFilters(shell);
            }
            if (event.target.matches('[data-dm-record-master]')) {
                var checked = event.target.checked;
                var boxes = shell.querySelectorAll('tbody tr:not([hidden]) [data-dm-record-check]');
                for (var i = 0; i < boxes.length; i++) {
                    boxes[i].checked = checked;
                }
            }
        });
        shell.addEventListener('input', function (event) {
            if (event.target.matches('[data-dm-record-search]')) {
                applyFilters(shell);
            }
        });
        shell.addEventListener('click', function (event) {
            if (event.target.matches('[data-dm-record-search-button]')) {
                applyFilters(shell);
            }
            if (event.target.matches('[data-dm-record-delete]')) {
                runBulkDelete(shell);
            }
        });
    }

    function runBulkDelete(shell) {
        var checked = Array.prototype.slice.call(shell.querySelectorAll('tbody tr:not([hidden]) [data-dm-record-check]:checked'));
        if (!checked.length) {
            alert('Select one or more records first.');
            return;
        }
        var type = checked[0].getAttribute('data-record-type');
        for (var i = 0; i < checked.length; i++) {
            if (checked[i].getAttribute('data-record-type') !== type) {
                alert('Bulk delete can only run for one record type at a time. Filter to one type first, then select records.');
                return;
            }
        }
        if (typeof confirmDelete === 'function' && !confirmDelete()) {
            return;
        }
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'dnsmanagement.php?action=managednszone';
        var fields = {
            delete: 'true',
            domain: dmDns1010Domain,
            domainid: dmDns1010DomainId,
            freednshosting: dmDns1010FreeDnsHosting,
            nsrecordtype: type,
            page: '1',
            itemlimit: 'all',
            q: ''
        };
        Object.keys(fields).forEach(function (name) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = fields[name];
            form.appendChild(input);
        });
        checked.forEach(function (box) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'multidelete[]';
            input.value = box.getAttribute('data-bulk-value') || '';
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }

    function showError(shell, message) {
        var loading = shell.querySelector('.dm-dns-records-loading');
        if (loading) {
            loading.remove();
        }
        var error = document.createElement('div');
        error.className = 'dm-dns-records-error';
        error.textContent = message;
        shell.appendChild(error);
    }

    function currentRequestedType() {
        try {
            var params = new URL(window.location.href).searchParams;
            var requested = (params.get('nsrecordtype') || 'A').toUpperCase();
            for (var i = 0; i < dmDns1010RecordTypes.length; i++) {
                if (dmDns1010RecordTypes[i] === requested) {
                    return requested;
                }
            }
        } catch (e) {}
        return 'A';
    }

    function prioritizedRecordTypes(firstType) {
        var out = [];
        if (firstType) {
            out.push(firstType);
        }
        for (var i = 0; i < dmDns1010RecordTypes.length; i++) {
            if (dmDns1010RecordTypes[i] !== firstType) {
                out.push(dmDns1010RecordTypes[i]);
            }
        }
        return out;
    }

    function singleSourceUrl() {
        var url = new URL('clientarea.php', window.location.href);
        url.search = '';
        url.hash = '';
        url.searchParams.set('action', 'domaindns');
        url.searchParams.set('domainid', dmDns1010DomainId);
        return url;
    }

    function selectValue(select) {
        if (!select) {
            return '';
        }
        if (select.value) {
            return String(select.value).trim();
        }
        var option = select.options && select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
        return option ? getText(option) : '';
    }

    function extractRowsFromSingleSource(doc) {
        var records = [];
        var form = doc.querySelector('form input[name="dnsrecid[]"]');
        form = form ? form.closest('form') : null;
        if (!form) {
            form = doc.querySelector('form[action*="domaindns"]');
        }
        if (!form) {
            return records;
        }

        var recids = form.querySelectorAll('input[name="dnsrecid[]"]');
        var hosts = form.querySelectorAll('input[name="dnsrecordhost[]"]');
        var types = form.querySelectorAll('select[name="dnsrecordtype[]"]');
        var values = form.querySelectorAll('input[name="dnsrecordaddress[]"]');
        var priorities = form.querySelectorAll('input[name="dnsrecordpriority[]"]');
        var max = Math.max(hosts.length, types.length, values.length, priorities.length);

        for (var i = 0; i < max; i++) {
            var recid = recids[i] ? String(recids[i].value || '').trim() : '';
            var host = normalizeHost(hosts[i] ? hosts[i].value : '');
            var type = selectValue(types[i]).toUpperCase();
            var value = normalizeValue(values[i] ? values[i].value : '');
            var priority = normalizeValue(priorities[i] ? priorities[i].value : '');

            if (!recid && !host && !type && !value) {
                continue;
            }
            if (!recid && !host && !value) {
                continue;
            }
            if (!type) {
                type = 'A';
            }
            if (priority.toUpperCase() === 'N/A') {
                priority = '';
            }

            records.push({
                type: type,
                host: host,
                value: value,
                ttl: '',
                priority: priority,
                weight: '',
                port: '',
                status: 'Active',
                bulkValue: recid,
                singleSourceRecid: recid,
                actionsHtml: '<span class="dm-dns-record-actions-loading">Actions loading…</span>'
            });
        }
        return records;
    }

    function fetchRecordsFromSingleSource() {
        return fetch(singleSourceUrl().toString(), { credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('single-source DNS request failed');
                }
                return response.text();
            })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var records = extractRowsFromSingleSource(doc);
                if (!records.length) {
                    throw new Error('single-source DNS page did not contain records');
                }
                return records;
            });
    }

    function compactKeyPart(value) {
        return normalizeValue(value).toLowerCase();
    }

    function looseRecordKey(record) {
        return [
            compactKeyPart(record.type),
            compactKeyPart(record.host),
            compactKeyPart(record.value)
        ].join('\u001f');
    }

    function findMergeTarget(records, legacyRecord) {
        var looseKey = looseRecordKey(legacyRecord);
        var fallback = null;
        for (var i = 0; i < records.length; i++) {
            var candidate = records[i];
            if (candidate._dmLegacyAttached) {
                continue;
            }
            if (looseRecordKey(candidate) !== looseKey) {
                continue;
            }
            if (legacyRecord.priority && candidate.priority && compactKeyPart(legacyRecord.priority) !== compactKeyPart(candidate.priority)) {
                fallback = fallback || candidate;
                continue;
            }
            return candidate;
        }
        return fallback;
    }

    function mergeLegacyRecords(displayRecords, legacyRecords) {
        var changed = false;
        for (var i = 0; i < legacyRecords.length; i++) {
            var legacy = legacyRecords[i];
            var target = findMergeTarget(displayRecords, legacy);
            if (target) {
                target._dmLegacyAttached = true;
                target.ttl = legacy.ttl || target.ttl;
                target.status = legacy.status || target.status;
                target.bulkValue = legacy.bulkValue || target.bulkValue;
                target.actionsHtml = legacy.actionsHtml || target.actionsHtml;
                target.priority = legacy.priority || target.priority;
                target.weight = legacy.weight || target.weight;
                target.port = legacy.port || target.port;
                changed = true;
            } else {
                legacy._dmLegacyAttached = true;
                displayRecords.push(legacy);
                changed = true;
            }
        }
        return changed;
    }

    function syncFilterOptions(shell, records) {
        var filter = shell.querySelector('[data-dm-record-filter]');
        if (!filter) {
            return;
        }
        var seen = {};
        for (var i = 0; i < filter.options.length; i++) {
            seen[filter.options[i].value] = true;
        }
        var types = [];
        for (var j = 0; j < records.length; j++) {
            var type = String(records[j].type || '').toUpperCase();
            if (type && !seen[type]) {
                seen[type] = true;
                types.push(type);
            }
        }
        types.sort();
        for (var k = 0; k < types.length; k++) {
            var option = document.createElement('option');
            option.value = types[k];
            option.textContent = types[k];
            filter.appendChild(option);
        }
    }

    function fetchRecordsForType(type) {
        return fetch(currentBaseUrl(type).toString(), { credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error(type + ' request failed');
                }
                return response.text();
            })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                return extractRowsFromDocument(doc, type);
            });
    }

    function setLoadingMessage(shell, message) {
        var existing = shell.querySelector('.dm-dns-records-loading');
        if (!existing) {
            existing = document.createElement('div');
            existing.className = 'dm-dns-records-loading';
            shell.appendChild(existing);
        }
        existing.textContent = message;
    }

    function clearLoadingMessage(shell) {
        var existing = shell.querySelector('.dm-dns-records-loading');
        if (existing) {
            existing.remove();
        }
    }

    function loadRecordsFromLegacyPages(shell) {
        var firstType = currentRequestedType();
        var typeOrder = prioritizedRecordTypes(firstType);
        var loadedByType = {};
        var completedByType = {};
        var completedCount = 0;
        var renderedAny = false;

        function allLoadedRecords() {
            var allRecords = [];
            for (var i = 0; i < typeOrder.length; i++) {
                var group = loadedByType[typeOrder[i]] || [];
                allRecords = allRecords.concat(group);
            }
            return allRecords;
        }

        function redrawProgress(message) {
            var allRecords = allLoadedRecords();

            if (allRecords.length) {
                renderedAny = true;
                renderTable(shell, allRecords);
                applyFilters(shell);
            }

            if (completedCount < typeOrder.length) {
                setLoadingMessage(shell, message || ('Loading DNS record types... ' + completedCount + '/' + typeOrder.length));
            } else {
                clearLoadingMessage(shell);
                if (!renderedAny) {
                    showError(shell, 'The combined records view could not load any record types. The original preserved DNS tool is still available if this appears after a reload.');
                }
            }
        }

        function finishType(type, records, failed) {
            if (completedByType[type]) {
                return;
            }

            completedByType[type] = true;
            completedCount++;
            loadedByType[type] = records || [];

            var remaining = typeOrder.length - completedCount;
            if (remaining > 0) {
                redrawProgress('Loaded ' + completedCount + '/' + typeOrder.length + ' DNS record types. Loading ' + remaining + ' more...');
            } else {
                redrawProgress('');
            }
        }

        setLoadingMessage(shell, 'Loading ' + firstType + ' records first...');

        fetchRecordsForType(firstType).then(function (records) {
            finishType(firstType, records, false);
        }).catch(function () {
            finishType(firstType, [], true);
        }).then(function () {
            setLoadingMessage(shell, 'Loading remaining DNS record types...');

            for (var i = 0; i < typeOrder.length; i++) {
                (function (type) {
                    if (type === firstType) {
                        return;
                    }

                    fetchRecordsForType(type).then(function (records) {
                        finishType(type, records, false);
                    }).catch(function () {
                        finishType(type, [], true);
                    });
                })(typeOrder[i]);
            }
        });
    }

    function enrichActionsFromLegacyPages(shell, displayRecords) {
        var typeOrder = prioritizedRecordTypes(currentRequestedType());
        var completedCount = 0;

        function finish(type, records) {
            completedCount++;
            if (records && records.length && mergeLegacyRecords(displayRecords, records)) {
                renderTable(shell, displayRecords);
                applyFilters(shell);
            }
            if (completedCount < typeOrder.length) {
                setLoadingMessage(shell, 'Records loaded. Preparing Modify/Delete actions... ' + completedCount + '/' + typeOrder.length);
            } else {
                clearLoadingMessage(shell);
                for (var i = 0; i < displayRecords.length; i++) {
                    delete displayRecords[i]._dmLegacyAttached;
                    if (!displayRecords[i].actionsHtml || displayRecords[i].actionsHtml.indexOf('Actions loading') !== -1) {
                        displayRecords[i].actionsHtml = '<a class="btn btn-primary btn-sm" href="' + escapeHtml(singleSourceUrl().toString()) + '">Manage</a>';
                    }
                }
                renderTable(shell, displayRecords);
                applyFilters(shell);
            }
        }

        setLoadingMessage(shell, 'Records loaded. Preparing Modify/Delete actions...');
        for (var i = 0; i < typeOrder.length; i++) {
            (function (type) {
                fetchRecordsForType(type).then(function (records) {
                    finish(type, records);
                }).catch(function () {
                    finish(type, []);
                });
            })(typeOrder[i]);
        }
    }

    function loadAllRecords(shell) {
        setLoadingMessage(shell, 'Loading DNS records from single source...');
        fetchRecordsFromSingleSource().then(function (records) {
            renderTable(shell, records);
            applyFilters(shell);
            enrichActionsFromLegacyPages(shell, records);
        }).catch(function () {
            loadRecordsFromLegacyPages(shell);
        });
    }

    function boot() {
        if (dmDns1010Built) {
            return;
        }
        var moduleArea = document.querySelector('#dm-dns-current-module-output.dm-dns-module-area, .dm-dns-module-area');
        if (!moduleArea) {
            return;
        }
        if (moduleArea.querySelector('.dm-dns-records-unified-shell')) {
            dmDns1010Built = true;
            return;
        }
        dmDns1010Built = true;
        var shell = createShell(moduleArea);
        loadAllRecords(shell);
    }

    var attempts = 0;
    var timer = window.setInterval(function () {
        attempts++;
        boot();
        if (dmDns1010Built || attempts > 80) {
            window.clearInterval(timer);
        }
    }, 100);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
HTML;
});
