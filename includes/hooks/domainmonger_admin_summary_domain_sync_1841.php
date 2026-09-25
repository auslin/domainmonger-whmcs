<?php
/**
 * DomainMonger Patch 1844
 * Bulk Sync Selected Domains from the WHMCS Admin Client Summary page.
 *
 * Adds a third action beside Invoice Selected Items and Delete Selected Items.
 * The action processes checked domain rows sequentially and invokes the same
 * Sync Domain control already supplied on each individual Admin Domain page by
 * the assigned registrar module. This intentionally avoids duplicating the
 * registrar module's private sync logic or its database-update rules.
 *
 * Patch 1844 preserves Patch 1843's confirmed status-save and verification
 * behavior. It replaces the failed delegated Select All fallback with a direct
 * binding on the current master checkbox, rebinds after each server-side table
 * redraw, and applies the final state to every current domain checkbox after
 * WHMCS's original visibility-filtered click handler has finished.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('AdminAreaFooterOutput', 1844, static function (array $vars): string {
    $filename = strtolower((string) ($vars['filename'] ?? ''));
    $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    if (!in_array($filename, ['clientssummary', 'clientssummary.php'], true) && $script !== 'clientssummary.php') {
        return '';
    }

    $clientId = (int) ($_GET['userid'] ?? $_POST['userid'] ?? 0);
    if ($clientId <= 0) {
        return '';
    }

    $domainMeta = [];
    try {
        $rows = Capsule::table('tbldomains')
            ->where('userid', $clientId)
            ->get(['id', 'registrationperiod', 'donotrenew']);
        foreach ($rows as $row) {
            $domainMeta[(string) ((int) $row->id)] = [
                'rp' => (int) $row->registrationperiod,
                'ar' => ((int) $row->donotrenew) === 0 ? 1 : 0,
            ];
        }
    } catch (Throwable $e) {
        $domainMeta = [];
    }

    $domainMetaJson = json_encode($domainMeta, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if (!is_string($domainMetaJson)) {
        $domainMetaJson = '{}';
    }

    $output = <<<'HTML'
<style>
#dm1841-sync-selected-domains {
    margin-left: 4px;
}
#dm1841-sync-modal .modal-dialog {
    width: min(900px, calc(100% - 30px));
}
#dm1841-sync-modal .dm1841-current {
    margin: 0 0 10px;
    color: #555;
}
#dm1841-sync-modal .progress {
    height: 18px;
    margin-bottom: 14px;
}
#dm1841-sync-modal .progress-bar {
    min-width: 0;
    line-height: 18px;
}
#dm1841-sync-results-wrap {
    max-height: 320px;
    overflow-y: auto;
    border: 1px solid #ddd;
}
#dm1841-sync-results {
    margin-bottom: 0;
}
#dm1841-sync-results th,
#dm1841-sync-results td {
    vertical-align: middle;
}
#dm1841-sync-results th:nth-child(1),
#dm1841-sync-results td:nth-child(1) {
    width: 25%;
}
#dm1841-sync-results th:nth-child(2),
#dm1841-sync-results td:nth-child(2) {
    width: 13%;
    text-align: center;
    white-space: nowrap;
}
#dm1841-sync-results th:nth-child(3),
#dm1841-sync-results td:nth-child(3) {
    width: 28%;
    text-align: center;
}
#dm1841-sync-results .dm1841-status-arrow {
    display: inline-block;
    margin: 0 5px;
    color: #777;
}
#dm1841-sync-modal .dm1841-summary {
    margin-top: 12px;
    margin-bottom: 0;
}

#summaryDomains th.dm1876-rp,
#summaryDomains td.dm1876-rp,
#summaryDomains th.dm1876-ar,
#summaryDomains td.dm1876-ar {
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
    box-sizing: border-box;
    text-align: center !important;
    white-space: nowrap !important;
}
#summaryDomains th.dm1876-rp,
#summaryDomains th.dm1876-ar {
    padding-left: 8px !important;
    padding-right: 18px !important;
}
#summaryDomains .dm1876-ar-icon {
    display: inline-block;
    font-size: 12px;
    line-height: 1;
    width: 14px;
    text-align: center;
}
#summaryDomains .dm1876-ar-on { color: #163a5f; }
#summaryDomains .dm1876-ar-off { color: #8a8f95; }
</style>

<div class="modal fade" id="dm1841-sync-modal" tabindex="-1" role="dialog" aria-labelledby="dm1841-sync-modal-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="dm1841-sync-modal-title">
                    <i class="fas fa-sync-alt"></i>
                    Sync Selected Domains
                </h4>
            </div>
            <div class="modal-body">
                <p class="dm1841-current" id="dm1841-sync-current">Preparing domain sync...</p>
                <div class="progress" aria-label="Domain sync progress">
                    <div id="dm1841-sync-progress" class="progress-bar progress-bar-striped active" role="progressbar" style="width:0%" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
                </div>
                <div id="dm1841-sync-results-wrap">
                    <table id="dm1841-sync-results" class="table table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Result</th>
                                <th>Status Change</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="dm1841-sync-summary" class="alert alert-info dm1841-summary" style="display:none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" id="dm1841-sync-close" class="btn btn-default" data-dismiss="modal" disabled="disabled">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var clientId = __DM1841_CLIENT_ID__;
    var domainMeta = __DM1876_DOMAIN_META__;
    var running = false;
    var activeFrame = null;
    var initialNativeIdHtml = {};

    function normalise(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function domainIdFromRow(row) {
        if (!row) {
            return 0;
        }
        var box = row.querySelector('input[name="seldomains[]"], input.checkdomains');
        return box ? parseInt(box.value, 10) || 0 : 0;
    }

    function nativeIdColumnIndex(table) {
        if (!table || !table.tHead || !table.tHead.rows.length) {
            return -1;
        }
        var headers = table.tHead.rows[0].cells;
        for (var i = 0; i < headers.length; i++) {
            if (headers[i].getAttribute('data-name') === 'id'
                && !headers[i].classList.contains('dm1876-rp')
                && !headers[i].classList.contains('dm1876-ar')) {
                return i;
            }
        }
        return -1;
    }

    function snapshotInitialNativeIdCells(table) {
        if (!table || !table.tBodies || !table.tBodies.length) {
            return;
        }
        var idIndex = nativeIdColumnIndex(table);
        if (idIndex < 0) {
            return;
        }
        var rows = table.tBodies[0].rows;
        for (var i = 0; i < rows.length; i++) {
            var id = domainIdFromRow(rows[i]);
            if (!id || !rows[i].cells || rows[i].cells.length <= idIndex) {
                continue;
            }
            if (!Object.prototype.hasOwnProperty.call(initialNativeIdHtml, String(id))) {
                initialNativeIdHtml[String(id)] = rows[i].cells[idIndex].innerHTML;
            }
        }
    }

    function repairInitialNativeIdCells() {
        var table = document.getElementById('summaryDomains');
        if (!table || !table.tBodies || !table.tBodies.length) {
            return;
        }
        var idIndex = nativeIdColumnIndex(table);
        if (idIndex < 0) {
            return;
        }
        var rows = table.tBodies[0].rows;
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var id = domainIdFromRow(row);
            if (!id || !row.cells || row.cells.length <= idIndex) {
                continue;
            }
            var saved = initialNativeIdHtml[String(id)];
            if (typeof saved !== 'string') {
                continue;
            }
            var cell = row.cells[idIndex];
            if (cell.querySelector('.dm1876-ar-icon') || !String(cell.textContent || '').trim()) {
                cell.innerHTML = saved;
            }
        }
    }

    function metaColumnIndexes(table) {
        var result = {rp: -1, ar: -1};
        if (!table || !table.tHead || !table.tHead.rows.length) {
            return result;
        }
        var headers = table.tHead.rows[0].cells;
        for (var i = 0; i < headers.length; i++) {
            if (headers[i].classList.contains('dm1876-rp')) {
                result.rp = i;
            }
            if (headers[i].classList.contains('dm1876-ar')) {
                result.ar = i;
            }
        }
        return result;
    }

    function fillDomainMetaCells() {
        var table = document.getElementById('summaryDomains');
        if (!table || !table.tBodies || !table.tBodies.length) {
            return;
        }
        var indexes = metaColumnIndexes(table);
        if (indexes.rp < 0 || indexes.ar < 0) {
            return;
        }
        var rows = table.tBodies[0].rows;
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            if (!row.cells || row.cells.length <= Math.max(indexes.rp, indexes.ar)) {
                continue;
            }
            var id = domainIdFromRow(row);
            var meta = domainMeta[String(id)] || null;
            var rp = row.cells[indexes.rp];
            var ar = row.cells[indexes.ar];

            // Bind presentation by the header identity/index, never by a generic
            // DataTables column position. This prevents AR rendering from leaking
            // into the native ID column after a server-side redraw.
            rp.classList.add('dm1876-rp');
            ar.classList.add('dm1876-ar');

            rp.textContent = meta && meta.rp ? String(meta.rp) : '';
            rp.setAttribute('data-order', meta ? String(meta.rp || 0) : '0');
            if (meta) {
                ar.innerHTML = meta.ar
                    ? '<i class="fas fa-sync-alt dm1876-ar-icon dm1876-ar-on" title="Auto Renew Enabled" aria-label="Auto Renew Enabled"></i>'
                    : '<i class="fas fa-ban dm1876-ar-icon dm1876-ar-off" title="Auto Renew Disabled" aria-label="Auto Renew Disabled"></i>';
                ar.setAttribute('data-order', meta.ar ? '1' : '0');
            } else {
                ar.textContent = '';
                ar.setAttribute('data-order', '0');
            }
        }
    }

    function ensureDomainMetaColumns() {
        var table = document.getElementById('summaryDomains');
        if (!table || !table.tHead || !table.tHead.rows.length) {
            return;
        }

        var headerRow = table.tHead.rows[0];
        snapshotInitialNativeIdCells(table);
        if (!headerRow.querySelector('th.dm1876-rp')) {
            // These columns must exist before WHMCS initializes its server-side
            // DataTable so DataTables owns the correct column count/indexes.
            var actionHeader = headerRow.lastElementChild;
            var rp = document.createElement('th');
            rp.className = 'dm1876-rp';
            rp.setAttribute('data-name', 'id');
            rp.setAttribute('data-class-name', 'dm1876-rp text-center');
            rp.setAttribute('data-width', '44');
            rp.setAttribute('title', 'Registration Period');
            rp.textContent = 'RP';

            var ar = document.createElement('th');
            ar.className = 'dm1876-ar';
            ar.setAttribute('data-name', 'id');
            ar.setAttribute('data-class-name', 'dm1876-ar text-center');
            ar.setAttribute('data-width', '44');
            ar.setAttribute('title', 'Auto Renew');
            ar.textContent = 'AR';

            headerRow.insertBefore(rp, actionHeader);
            headerRow.insertBefore(ar, actionHeader);

            if (table.tBodies && table.tBodies.length) {
                var rows = table.tBodies[0].rows;
                for (var i = 0; i < rows.length; i++) {
                    var actionCell = rows[i].lastElementChild;
                    var rpCell = document.createElement('td');
                    rpCell.className = 'dm1876-rp text-center';
                    var arCell = document.createElement('td');
                    arCell.className = 'dm1876-ar text-center';
                    rows[i].insertBefore(rpCell, actionCell);
                    rows[i].insertBefore(arCell, actionCell);
                }
            }
        }

        fillDomainMetaCells();
    }

    function bindDomainMetaSorting() {
        var table = document.getElementById('summaryDomains');
        if (!table || !window.jQuery || !window.jQuery.fn.dataTable || !window.jQuery.fn.dataTable.isDataTable(table)) {
            return;
        }
        var $table = window.jQuery(table);
        if ($table.data('dm1876MetaBound')) {
            fillDomainMetaCells();
            return;
        }
        $table.data('dm1876MetaBound', true);
        $table.on('preXhr.dt.dm1876', function (e, settings, data) {
            var indexes = metaColumnIndexes(table);
            if (!data || !data.order || !data.columns) {
                return;
            }
            for (var j = 0; j < data.order.length; j++) {
                var idx = parseInt(data.order[j].column, 10);
                if (idx === indexes.rp && data.columns[idx]) {
                    data.columns[idx].data = 'registrationperiod';
                }
                if (idx === indexes.ar && data.columns[idx]) {
                    data.columns[idx].data = 'donotrenew';
                }
            }
        });
        $table.on('draw.dt.dm1876 xhr.dt.dm1876', function () {
            window.setTimeout(function () {
                fillDomainMetaCells();
                repairInitialNativeIdCells();
            }, 0);
        });
        fillDomainMetaCells();
        repairInitialNativeIdCells();
        window.setTimeout(repairInitialNativeIdCells, 50);
    }

    function installButton() {
        var actionBar = document.querySelector('.bulk-action-btns');
        if (!document.getElementById('summaryDomains') || !actionBar || document.getElementById('dm1841-sync-selected-domains')) {
            return;
        }

        var button = document.createElement('button');
        button.type = 'button';
        button.id = 'dm1841-sync-selected-domains';
        button.className = 'button btn btn-sm btn-default';
        button.innerHTML = '<i class="fas fa-sync-alt" aria-hidden="true"></i> Sync Selected Domains';
        button.addEventListener('click', startSync);
        actionBar.appendChild(button);
    }

    function selectedDomains() {
        var selected = [];
        var seen = {};
        var boxes = document.querySelectorAll('input[name="seldomains[]"]:checked, input.checkdomains:checked');

        for (var i = 0; i < boxes.length; i++) {
            var id = parseInt(boxes[i].value, 10);
            if (!id || seen[id]) {
                continue;
            }
            seen[id] = true;

            var row = boxes[i].closest ? boxes[i].closest('tr') : null;
            var domain = '';
            if (row && row.cells && row.cells.length > 2) {
                var domainLink = row.cells[2].querySelector('a');
                domain = String(domainLink ? domainLink.textContent : row.cells[2].textContent || '').trim();
            }

            selected.push({
                id: id,
                domain: domain || ('Domain #' + id)
            });
        }

        return selected;
    }

    function showModal() {
        var modal = document.getElementById('dm1841-sync-modal');
        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery(modal).modal({backdrop: 'static', keyboard: false, show: true});
            return;
        }

        modal.style.display = 'block';
        modal.className += ' in';
        modal.setAttribute('aria-hidden', 'false');
    }

    function resetModal() {
        var body = document.querySelector('#dm1841-sync-results tbody');
        while (body && body.firstChild) {
            body.removeChild(body.firstChild);
        }

        var progress = document.getElementById('dm1841-sync-progress');
        progress.style.width = '0%';
        progress.setAttribute('aria-valuenow', '0');
        progress.className = 'progress-bar progress-bar-striped active';
        progress.textContent = '';

        document.getElementById('dm1841-sync-current').textContent = 'Preparing domain sync...';
        document.getElementById('dm1841-sync-summary').style.display = 'none';
        document.getElementById('dm1841-sync-summary').textContent = '';
        document.getElementById('dm1841-sync-close').disabled = true;
    }

    function updateProgress(completed, total, currentDomain) {
        var percent = total > 0 ? Math.round((completed / total) * 100) : 0;
        var progress = document.getElementById('dm1841-sync-progress');
        progress.style.width = percent + '%';
        progress.setAttribute('aria-valuenow', String(percent));
        progress.textContent = percent > 10 ? percent + '%' : '';

        if (completed >= total) {
            document.getElementById('dm1841-sync-current').textContent = 'Domain sync complete.';
        } else {
            document.getElementById('dm1841-sync-current').textContent = 'Syncing ' + currentDomain + ' (' + (completed + 1) + ' of ' + total + ')...';
        }
    }

    function addResult(item, result) {
        var row = document.createElement('tr');
        var domainCell = document.createElement('td');
        var resultCell = document.createElement('td');
        var statusCell = document.createElement('td');
        var detailCell = document.createElement('td');
        var label = document.createElement('span');

        domainCell.textContent = item.domain;
        detailCell.textContent = result.message || '';

        label.className = 'label ' + (result.type === 'success'
            ? 'label-success'
            : (result.type === 'warning' ? 'label-warning' : 'label-danger'));
        label.textContent = result.type === 'success'
            ? 'Synced'
            : (result.type === 'warning' ? 'Skipped' : 'Failed');
        resultCell.appendChild(label);

        if (hasStatusChange(result)) {
            var oldStatus = document.createElement('span');
            var arrow = document.createElement('span');
            var newStatus = document.createElement('span');

            oldStatus.className = 'label label-default';
            oldStatus.textContent = result.statusBefore;
            arrow.className = 'dm1841-status-arrow';
            arrow.textContent = '\u2192';
            newStatus.className = 'label label-info';
            newStatus.textContent = result.statusAfter;

            statusCell.appendChild(oldStatus);
            statusCell.appendChild(arrow);
            statusCell.appendChild(newStatus);
            statusCell.title = result.type === 'success'
                ? 'WHMCS domain status updated and saved'
                : 'Sync Domain returned this status change; see the result details';
        } else {
            statusCell.textContent = '\u2014';
            statusCell.title = result.statusBefore && result.statusAfter
                ? 'WHMCS domain status did not change'
                : 'WHMCS domain status was unavailable';
        }

        row.appendChild(domainCell);
        row.appendChild(resultCell);
        row.appendChild(statusCell);
        row.appendChild(detailCell);
        document.querySelector('#dm1841-sync-results tbody').appendChild(row);
    }

    function hasStatusChange(result) {
        return !!(result.statusBefore
            && result.statusAfter
            && normalise(result.statusBefore) !== normalise(result.statusAfter));
    }

    function controlLabel(control) {
        var value = control.tagName === 'INPUT' ? control.value : control.textContent;
        return normalise(value || control.getAttribute('aria-label') || control.getAttribute('title'));
    }

    function findSyncControl(doc) {
        var controls = doc.querySelectorAll('button, input[type="submit"], input[type="button"], a');
        for (var i = 0; i < controls.length; i++) {
            if (controlLabel(controls[i]) === 'sync domain') {
                return controls[i];
            }
        }
        return null;
    }

    function findWhmcsStatusControl(doc) {
        if (!doc) {
            return null;
        }

        var selectors = [
            'select[name="status"]',
            'select[name="domainstatus"]',
            'select#status',
            'input[name="status"]',
            'input[name="domainstatus"]'
        ];

        for (var i = 0; i < selectors.length; i++) {
            var control = doc.querySelector(selectors[i]);
            if (control) {
                return control;
            }
        }

        return null;
    }

    function readWhmcsStatusState(doc) {
        var control = findWhmcsStatusControl(doc);
        if (!control) {
            return {label: '', value: ''};
        }

        var value = String(control.value || '').replace(/\s+/g, ' ').trim();
        var label = value;
        if (control.tagName === 'SELECT' && control.selectedIndex >= 0) {
            var option = control.options[control.selectedIndex];
            label = option ? (option.textContent || option.value || value) : value;
        }

        return {
            label: String(label || '').replace(/\s+/g, ' ').trim(),
            value: value
        };
    }

    function readWhmcsStatus(doc) {
        return readWhmcsStatusState(doc).label;
    }

    function sameStatusState(left, right) {
        var leftValue = normalise(left && left.value);
        var rightValue = normalise(right && right.value);
        if (leftValue && rightValue) {
            return leftValue === rightValue;
        }

        return normalise(left && left.label) === normalise(right && right.label);
    }

    function shouldPersistStatusChange(beforeState, afterState) {
        return !!(beforeState
            && afterState
            && beforeState.label
            && afterState.label
            && !sameStatusState(beforeState, afterState));
    }

    function findSaveChangesControl(doc, form) {
        if (!doc || !form) {
            return null;
        }

        var controls = doc.querySelectorAll('button, input[type="submit"], input[type="button"]');
        for (var i = 0; i < controls.length; i++) {
            if (controls[i].form !== form) {
                continue;
            }

            var label = controlLabel(controls[i]);
            if (label === 'save changes' || label.indexOf('save changes') !== -1) {
                return controls[i];
            }
        }

        return null;
    }

    function resultNotice(doc, ignoredMessages) {
        var selectors = [
            '.alert-danger',
            '.alert-error',
            '.errorbox',
            '.alert-warning',
            '.alert-success',
            '.successbox'
        ];

        for (var i = 0; i < selectors.length; i++) {
            var notices = doc.querySelectorAll(selectors[i]);
            for (var j = 0; j < notices.length; j++) {
                var message = String(notices[j].textContent || '').replace(/\s+/g, ' ').trim();
                if (!message || (ignoredMessages && ignoredMessages[message])) {
                    continue;
                }

                var lower = message.toLowerCase();
                var isFailure = selectors[i].indexOf('danger') !== -1
                    || selectors[i].indexOf('error') !== -1
                    || /\b(error|failed|failure|unable)\b/.test(lower);
                var isWarning = selectors[i].indexOf('warning') !== -1;

                return {
                    type: isFailure ? 'error' : (isWarning ? 'warning' : 'success'),
                    message: message.substring(0, 500)
                };
            }
        }

        return null;
    }

    function noticeSnapshot(doc) {
        var snapshot = {};
        var notices = doc.querySelectorAll('.alert-danger, .alert-error, .errorbox, .alert-warning, .alert-success, .successbox');
        for (var i = 0; i < notices.length; i++) {
            var message = String(notices[i].textContent || '').replace(/\s+/g, ' ').trim();
            if (message) {
                snapshot[message] = true;
            }
        }
        return snapshot;
    }

    function alertResult(message) {
        var clean = String(message || '').replace(/\s+/g, ' ').trim();
        if (!clean) {
            return null;
        }

        var lower = clean.toLowerCase();
        return {
            type: /\b(error|failed|failure|unable)\b/.test(lower) ? 'error' : 'success',
            message: clean.substring(0, 500)
        };
    }

    function syncOne(item) {
        return new Promise(function (resolve) {
            var frame = document.createElement('iframe');
            var commandStarted = false;
            var actionDocument = null;
            var finished = false;
            var observer = null;
            var timer = null;
            var baselineNotices = {};
            var capturedAlert = '';
            var statusBeforeState = {label: '', value: ''};
            var saveStarted = false;
            var saveDocument = null;
            var saveExpectedState = null;
            var pendingSaveResult = null;

            function domainPageUrl(extra) {
                return 'clientsdomains.php?userid=' + encodeURIComponent(clientId)
                    + '&domainid=' + encodeURIComponent(item.id)
                    + (extra || '');
            }

            function appendDetail(message, addition) {
                message = String(message || '').replace(/\s+/g, ' ').trim();
                return (message ? message + ' ' : '') + addition;
            }

            function complete(result) {
                if (finished) {
                    return;
                }

                result = result || {type: 'error', message: 'The Sync Domain result was unavailable.'};
                if (typeof result.statusBefore === 'undefined') {
                    result.statusBefore = statusBeforeState.label;
                }
                if (typeof result.statusAfter === 'undefined') {
                    try {
                        result.statusAfter = commandStarted ? readWhmcsStatus(frame.contentDocument) : '';
                    } catch (ignore) {
                        result.statusAfter = '';
                    }
                }

                finished = true;
                window.clearTimeout(timer);
                if (observer) {
                    observer.disconnect();
                }
                if (frame.parentNode) {
                    frame.parentNode.removeChild(frame);
                }
                if (activeFrame === frame) {
                    activeFrame = null;
                }
                resolve(result);
            }

            function verifySavedStatus() {
                if (finished || !saveStarted) {
                    return;
                }

                var result = pendingSaveResult || {type: 'error', message: 'The Sync Domain result was unavailable.'};
                try {
                    var savedState = readWhmcsStatusState(frame.contentDocument);
                    result.statusBefore = statusBeforeState.label;

                    if (sameStatusState(savedState, saveExpectedState)) {
                        result.statusAfter = savedState.label || saveExpectedState.label;
                        result.statusPersisted = true;
                        result.message = appendDetail(result.message, 'WHMCS status saved.');
                        complete(result);
                        return;
                    }

                    result.type = 'error';
                    result.statusPersisted = false;
                    result.statusAfter = saveExpectedState ? saveExpectedState.label : savedState.label;
                    result.message = appendDetail(
                        result.message,
                        'The status change was returned by Sync Domain but WHMCS did not save it.'
                    );
                    complete(result);
                } catch (error) {
                    result.type = 'error';
                    result.statusPersisted = false;
                    result.statusBefore = statusBeforeState.label;
                    result.statusAfter = saveExpectedState ? saveExpectedState.label : '';
                    result.message = appendDetail(result.message, 'The saved WHMCS status could not be verified.');
                    complete(result);
                }
            }

            function requestFreshSaveVerification(attempt) {
                if (finished || !saveStarted) {
                    return;
                }

                try {
                    var currentDocument = frame.contentDocument;
                    if (currentDocument !== saveDocument) {
                        return;
                    }

                    if (currentDocument && currentDocument.readyState === 'loading' && attempt < 20) {
                        window.setTimeout(function () {
                            requestFreshSaveVerification(attempt + 1);
                        }, 250);
                        return;
                    }

                    frame.src = domainPageUrl('&dm1843verify=' + encodeURIComponent(String(Date.now())));
                } catch (error) {
                    verifySavedStatus();
                }
            }

            function persistStatusChange(result, afterState) {
                var doc;
                try {
                    doc = frame.contentDocument;
                } catch (ignore) {
                    doc = null;
                }

                var statusControl = findWhmcsStatusControl(doc);
                var form = statusControl ? statusControl.form : null;
                var saveControl = findSaveChangesControl(doc, form);
                if (!statusControl || !form || !saveControl) {
                    result.type = 'error';
                    result.statusPersisted = false;
                    result.statusBefore = statusBeforeState.label;
                    result.statusAfter = afterState.label;
                    result.message = appendDetail(
                        result.message,
                        'The status changed on the Domain page, but its native Save Changes control was unavailable.'
                    );
                    complete(result);
                    return;
                }

                saveStarted = true;
                saveDocument = doc;
                saveExpectedState = {label: afterState.label, value: afterState.value};
                pendingSaveResult = result;
                result.statusBefore = statusBeforeState.label;
                result.statusAfter = afterState.label;

                if (observer) {
                    observer.disconnect();
                }

                try {
                    saveControl.click();
                } catch (error) {
                    result.type = 'error';
                    result.statusPersisted = false;
                    result.message = appendDetail(result.message, 'The native Save Changes action could not be started.');
                    complete(result);
                    return;
                }

                // Native WHMCS normally posts the form and loads a new page. If
                // a registrar/theme intercepts that post with AJAX, force one
                // clean reload afterward so the database value is still verified.
                window.setTimeout(function () {
                    requestFreshSaveVerification(0);
                }, 750);
            }

            function finish(result) {
                if (finished || saveStarted) {
                    return;
                }

                result = result || {type: 'error', message: 'The Sync Domain result was unavailable.'};
                var statusAfterState = {label: '', value: ''};
                try {
                    statusAfterState = commandStarted
                        ? readWhmcsStatusState(frame.contentDocument)
                        : statusAfterState;
                } catch (ignore) {
                }

                result.statusBefore = statusBeforeState.label;
                result.statusAfter = statusAfterState.label;

                if (result.type === 'success' && shouldPersistStatusChange(statusBeforeState, statusAfterState)) {
                    persistStatusChange(result, statusAfterState);
                    return;
                }

                complete(result);
            }

            function inspectAfterCommand() {
                if (finished) {
                    return;
                }

                try {
                    var doc = frame.contentDocument;
                    if (!doc || doc.readyState === 'loading') {
                        return;
                    }

                    var notice = resultNotice(doc, baselineNotices) || alertResult(capturedAlert);
                    if (notice) {
                        finish(notice);
                    }
                } catch (error) {
                    finish({type: 'error', message: 'The Sync Domain result could not be read.'});
                }
            }

            function runCommand(doc) {
                var attempts = 0;

                function locate() {
                    if (finished || commandStarted) {
                        return;
                    }

                    var control = findSyncControl(doc);
                    if (!control && attempts < 10) {
                        attempts++;
                        window.setTimeout(locate, 200);
                        return;
                    }

                    if (!control) {
                        var pageText = normalise(doc.body ? doc.body.textContent : '');
                        var message = pageText.indexOf('login') !== -1 && pageText.indexOf('password') !== -1
                            ? 'The admin session ended before this domain could be synced.'
                            : 'The individual Domain page does not offer a Sync Domain command.';
                        finish({type: pageText.indexOf('login') !== -1 ? 'error' : 'warning', message: message});
                        return;
                    }

                    statusBeforeState = readWhmcsStatusState(doc);

                    if (control.disabled || control.getAttribute('aria-disabled') === 'true') {
                        finish({type: 'warning', message: 'The Sync Domain command is disabled for this domain.'});
                        return;
                    }

                    commandStarted = true;
                    actionDocument = doc;
                    baselineNotices = noticeSnapshot(doc);

                    var form = control.form || (control.closest ? control.closest('form') : null);
                    if (form) {
                        form.removeAttribute('target');
                    }
                    control.removeAttribute('target');

                    try {
                        frame.contentWindow.confirm = function () { return true; };
                        frame.contentWindow.alert = function (message) {
                            capturedAlert = String(message || '');
                        };
                        frame.contentWindow.open = function (url) {
                            if (url) {
                                frame.contentWindow.location.href = url;
                            }
                            return frame.contentWindow;
                        };
                    } catch (ignore) {
                    }

                    if (window.MutationObserver && doc.body) {
                        observer = new MutationObserver(function () {
                            inspectAfterCommand();
                        });
                        observer.observe(doc.body, {childList: true, subtree: true, characterData: true});
                    }

                    try {
                        control.click();
                    } catch (error) {
                        finish({type: 'error', message: 'The existing Sync Domain command could not be started.'});
                        return;
                    }

                    window.setTimeout(function () {
                        if (finished) {
                            return;
                        }
                        try {
                            if (frame.contentDocument !== actionDocument) {
                                return;
                            }
                        } catch (ignore) {
                        }
                        inspectAfterCommand();
                    }, 500);
                }

                locate();
            }

            frame.setAttribute('aria-hidden', 'true');
            frame.tabIndex = -1;
            frame.style.display = 'none';
            frame.addEventListener('load', function () {
                if (finished) {
                    return;
                }

                try {
                    var doc = frame.contentDocument;
                    if (!doc) {
                        finish({type: 'error', message: 'The individual Domain page could not be loaded.'});
                        return;
                    }

                    if (!commandStarted) {
                        runCommand(doc);
                        return;
                    }

                    if (saveStarted) {
                        window.setTimeout(verifySavedStatus, 350);
                        return;
                    }

                    window.setTimeout(function () {
                        var notice = resultNotice(frame.contentDocument, baselineNotices) || alertResult(capturedAlert);
                        finish(notice || {type: 'success', message: 'The existing Sync Domain command completed.'});
                    }, 350);
                } catch (error) {
                    finish({type: 'error', message: 'The individual Domain page could not be accessed.'});
                }
            });

            timer = window.setTimeout(function () {
                if (saveStarted) {
                    var saveResult = pendingSaveResult || {type: 'error', message: ''};
                    saveResult.type = 'error';
                    saveResult.statusPersisted = false;
                    saveResult.statusBefore = statusBeforeState.label;
                    saveResult.statusAfter = saveExpectedState ? saveExpectedState.label : '';
                    saveResult.message = appendDetail(
                        saveResult.message,
                        'The WHMCS status save did not finish within 60 seconds.'
                    );
                    complete(saveResult);
                    return;
                }

                finish({type: 'error', message: commandStarted
                    ? 'The Sync Domain command did not finish within 60 seconds.'
                    : 'The individual Domain page did not load within 60 seconds.'});
            }, 60000);

            frame.src = domainPageUrl('');
            activeFrame = frame;
            document.body.appendChild(frame);
        });
    }

    function resetDomainSelection() {
        var boxes = document.querySelectorAll('input[name="seldomains[]"], input.checkdomains');
        for (var i = 0; i < boxes.length; i++) {
            boxes[i].checked = false;
        }

        var masters = document.querySelectorAll('#domainsall');
        for (var j = 0; j < masters.length; j++) {
            masters[j].checked = false;
            masters[j].indeterminate = false;
        }
    }

    function applyDomainSelectAll(master) {
        if (!master) {
            return;
        }

        var checked = !!master.checked;
        var boxes = document.querySelectorAll('input[name="seldomains[]"], input.checkdomains');
        for (var i = 0; i < boxes.length; i++) {
            boxes[i].checked = checked;
        }
    }

    function bindDomainSelectAll(master) {
        if (!master || master.dm1844SelectAllBound) {
            return;
        }

        // Use an element property rather than an HTML attribute. DataTables can
        // clone attributes onto a replacement header but cannot clone listeners
        // or this property, allowing the replacement control to be rebound.
        master.dm1844SelectAllBound = true;
        master.addEventListener('click', function () {
            // WHMCS's native handler filters with jQuery :visible. Apply the
            // final master state after that handler completes so newly redrawn
            // server-side rows are included regardless of that visibility test.
            window.setTimeout(function () {
                applyDomainSelectAll(master);
            }, 0);

            // A second pass covers DataTables completing a same-tick draw.
            window.setTimeout(function () {
                applyDomainSelectAll(master);
            }, 50);
        });
    }

    function bindCurrentDomainSelectAllControls() {
        var masters = document.querySelectorAll('#domainsall');
        for (var i = 0; i < masters.length; i++) {
            bindDomainSelectAll(masters[i]);
        }
    }

    function refreshDomainTable() {
        var table = document.getElementById('summaryDomains');

        resetDomainSelection();

        if (!table || !window.jQuery || !window.jQuery.fn.dataTable) {
            return;
        }

        try {
            if (window.jQuery.fn.dataTable.isDataTable(table)) {
                window.jQuery(table).DataTable().ajax.reload(function () {
                    resetDomainSelection();
                    bindCurrentDomainSelectAllControls();
                }, false);
            }
        } catch (ignore) {
        }
    }

    async function startSync() {
        if (running) {
            return;
        }

        var items = selectedDomains();
        if (!items.length) {
            window.alert('Select at least one domain first.');
            return;
        }

        var noun = items.length === 1 ? 'domain' : 'domains';
        if (!window.confirm('Run the existing Sync Domain command for ' + items.length + ' selected ' + noun + ', one at a time?')) {
            return;
        }

        running = true;
        var button = document.getElementById('dm1841-sync-selected-domains');
        button.disabled = true;
        resetModal();
        showModal();

        var counts = {success: 0, warning: 0, error: 0, statusChanged: 0};
        updateProgress(0, items.length, items[0].domain);

        for (var i = 0; i < items.length; i++) {
            updateProgress(i, items.length, items[i].domain);
            var result = await syncOne(items[i]);
            counts[result.type] = (counts[result.type] || 0) + 1;
            if (hasStatusChange(result) && result.type === 'success') {
                counts.statusChanged++;
            }
            addResult(items[i], result);
            updateProgress(i + 1, items.length, i + 1 < items.length ? items[i + 1].domain : '');
        }

        var summary = document.getElementById('dm1841-sync-summary');
        summary.className = 'alert dm1841-summary ' + (counts.error ? 'alert-warning' : 'alert-success');
        summary.textContent = counts.success + ' synced, ' + counts.warning + ' skipped, ' + counts.error + ' failed.'
            + (counts.statusChanged
                ? ' ' + counts.statusChanged + (counts.statusChanged === 1 ? ' status changed.' : ' statuses changed.')
                : '');
        summary.style.display = 'block';

        document.getElementById('dm1841-sync-progress').className = 'progress-bar';
        document.getElementById('dm1841-sync-close').disabled = false;
        button.disabled = false;
        running = false;
        refreshDomainTable();
    }

    ensureDomainMetaColumns();
    installButton();
    bindCurrentDomainSelectAllControls();
    window.setTimeout(bindDomainMetaSorting, 0);
    window.setTimeout(bindDomainMetaSorting, 150);
    window.setTimeout(bindDomainMetaSorting, 600);
    window.setTimeout(installButton, 100);
    window.setTimeout(installButton, 500);
    window.setTimeout(bindCurrentDomainSelectAllControls, 100);
    window.setTimeout(bindCurrentDomainSelectAllControls, 500);
})();
</script>
HTML;

    $output = str_replace('__DM1841_CLIENT_ID__', (string) $clientId, $output);
    return str_replace('__DM1876_DOMAIN_META__', $domainMetaJson, $output);
});
