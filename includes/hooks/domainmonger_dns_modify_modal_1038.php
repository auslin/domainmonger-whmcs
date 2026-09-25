<?php
/**
 * DomainMonger DNS Modify Modal 1038
 *
 * Adds a ClouDNS-style, DomainMonger-branded modal for DNS record Modify actions.
 * It leaves the existing WHMCS/ResellerClub modify endpoint and forms intact.
 *
 * Patch 1044: replace the failed close checks with an exact success-message
 * detector that closes the popup whenever the post-save modal content contains
 * "Record has been modified successfully".
 *
 * Patch 1097 update:
 * - The live DNS Management route now uses the fast native WHMCS DNS page.
 * - Keep this modal active for the legacy dnsmanagement.php fallback only.
 * - Still allow managednszonemodify so an already-open legacy popup can submit/save.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    $scriptName = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = (string) ($_GET['action'] ?? '');

    if ($scriptName !== 'dnsmanagement.php') {
        return '';
    }

    if (!in_array($action, ['managednszone', 'managednszonemodify'], true)) {
        return '';
    }

    // The live DNS Management route now redirects to the fast native DNS page.
    // Keep the old Modify popup only for intentional legacy/fallback usage,
    // plus the modify endpoint itself so submitted legacy modal saves still work.
    if ($action === 'managednszone' && !isset($_GET['dmlegacydns']) && !isset($_GET['dmnoredirect'])) {
        return '';
    }

    return <<<'HTML'
<style id="dm-dns-modify-modal-1038-style">
.dm-dns-modify-modal-open { overflow: hidden; }
#dm-dns-modify-modal-1038 {
    position: fixed;
    inset: 0;
    z-index: 2147483000;
    display: none;
    align-items: flex-start;
    justify-content: center;
    padding: 9vh 18px 40px;
    background: rgba(16, 22, 29, .68);
    backdrop-filter: blur(2px);
}
#dm-dns-modify-modal-1038.dm-open { display: flex; }
#dm-dns-modify-modal-1038 .dm-dns-modify-card {
    width: min(640px, calc(100vw - 36px));
    max-height: calc(100vh - 90px);
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, .34);
    overflow: hidden;
    color: #1f2a35;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 18px;
    background: #163a5f;
    color: #fff;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-head strong {
    display: block;
    margin: 0;
    color: #fff;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-head span {
    display: block;
    margin-top: 3px;
    color: rgba(255, 255, 255, .82);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .02em;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-close {
    flex: 0 0 auto;
    width: 34px;
    height: 34px;
    border: 2px solid rgba(255, 255, 255, .7);
    border-radius: 50%;
    background: #fff;
    color: #163a5f;
    font-size: 24px;
    line-height: 27px;
    text-align: center;
    font-weight: 700;
    cursor: pointer;
    transition: background .16s ease, color .16s ease, transform .16s ease;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-close:hover,
#dm-dns-modify-modal-1038 .dm-dns-modify-close:focus {
    background: #f58220;
    color: #fff;
    transform: scale(1.04);
    outline: none;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-body {
    padding: 20px 24px 22px;
    overflow: auto;
    max-height: calc(100vh - 175px);
}
#dm-dns-modify-modal-1038 .dm-dns-modify-loading,
#dm-dns-modify-modal-1038 .dm-dns-modify-message {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px;
    border: 1px solid rgba(22, 58, 95, .12);
    border-radius: 10px;
    background: #f7f9fb;
    color: #163a5f;
    font-weight: 700;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-spinner {
    width: 22px;
    height: 22px;
    border: 3px solid rgba(245, 130, 32, .25);
    border-top-color: #f58220;
    border-radius: 50%;
    animation: dmDnsModifySpin1038 .8s linear infinite;
}
@keyframes dmDnsModifySpin1038 { to { transform: rotate(360deg); } }
#dm-dns-modify-modal-1038 .dm-dns-modify-alerts {
    display: grid;
    gap: 10px;
    margin: 0 0 16px;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-alerts .alert {
    margin: 0;
    border-radius: 8px;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-type-row {
    display: grid;
    grid-template-columns: 150px minmax(0, 1fr);
    gap: 14px;
    align-items: center;
    margin-bottom: 14px;
    padding-bottom: 14px;
    border-bottom: 1px solid #edf0f4;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-type-row span,
#dm-dns-modify-modal-1038 .dm-dns-modify-field label {
    color: #263646;
    font-size: 14px;
    font-weight: 700;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-type-pill {
    justify-self: start;
    display: inline-flex;
    align-items: center;
    min-height: 32px;
    padding: 6px 12px;
    border-radius: 8px;
    background: rgba(22, 58, 95, .08);
    color: #163a5f;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .03em;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-form {
    margin: 0;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-fields {
    display: grid;
    gap: 14px;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-field {
    display: grid;
    grid-template-columns: 150px minmax(0, 1fr);
    gap: 14px;
    align-items: start;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-control,
#dm-dns-modify-modal-1038 .dm-dns-modify-control .input-group {
    width: 100%;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-control .input-group {
    display: flex;
    align-items: stretch;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-control .input-group .form-control {
    flex: 1 1 auto;
    min-width: 0;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-control .input-group-addon,
#dm-dns-modify-modal-1038 .dm-dns-modify-control .input-group-text {
    display: inline-flex;
    align-items: center;
    padding: 7px 10px;
    border: 1px solid #d7dee8;
    border-left: 0;
    border-radius: 0 7px 7px 0;
    background: #edf1f5;
    color: #163a5f;
    font-weight: 700;
    white-space: nowrap;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-control input[type="text"],
#dm-dns-modify-modal-1038 .dm-dns-modify-control input[type="number"],
#dm-dns-modify-modal-1038 .dm-dns-modify-control input[type="email"],
#dm-dns-modify-modal-1038 .dm-dns-modify-control select,
#dm-dns-modify-modal-1038 .dm-dns-modify-control textarea,
#dm-dns-modify-modal-1038 .dm-dns-modify-control .form-control {
    width: 100%;
    min-height: 38px;
    padding: 8px 10px;
    border: 1px solid #d7dee8;
    border-radius: 7px;
    background: #f3f5f7;
    color: #1f2a35;
    box-shadow: none;
    font-size: 14px;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-control textarea,
#dm-dns-modify-modal-1038 .dm-dns-modify-control textarea.form-control {
    min-height: 110px;
    resize: vertical;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-control input:focus,
#dm-dns-modify-modal-1038 .dm-dns-modify-control select:focus,
#dm-dns-modify-modal-1038 .dm-dns-modify-control textarea:focus,
#dm-dns-modify-modal-1038 .dm-dns-modify-control .form-control:focus {
    border-color: #f58220;
    outline: none;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .18);
    background: #fff;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-control br { display: none; }
#dm-dns-modify-modal-1038 .dm-dns-modify-control > span,
#dm-dns-modify-modal-1038 .dm-dns-modify-control > small,
#dm-dns-modify-modal-1038 .dm-dns-modify-control > em {
    display: block;
    margin-top: 6px;
    color: #657586;
    font-size: 12px;
    line-height: 1.35;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #edf0f4;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-submit,
#dm-dns-modify-modal-1038 .dm-dns-modify-cancel,
#dm-dns-modify-modal-1038 .dm-dns-modify-fullpage {
    min-width: 118px;
    min-height: 38px;
    border: 0;
    border-radius: 999px;
    padding: 8px 18px;
    font-weight: 800;
    line-height: 1.2;
    text-align: center;
    text-decoration: none !important;
    cursor: pointer;
    transition: background .16s ease, transform .16s ease, box-shadow .16s ease;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-submit {
    background: #f58220;
    color: #fff;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-submit:hover,
#dm-dns-modify-modal-1038 .dm-dns-modify-submit:focus {
    background: #d8741f;
    color: #fff;
    box-shadow: 0 4px 14px rgba(245, 130, 32, .28);
    transform: translateY(-1px);
    outline: none;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-cancel,
#dm-dns-modify-modal-1038 .dm-dns-modify-fullpage {
    background: #163a5f;
    color: #fff;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-cancel:hover,
#dm-dns-modify-modal-1038 .dm-dns-modify-cancel:focus,
#dm-dns-modify-modal-1038 .dm-dns-modify-fullpage:hover,
#dm-dns-modify-modal-1038 .dm-dns-modify-fullpage:focus {
    background: #214e7a;
    color: #fff;
    outline: none;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-submit[disabled] {
    opacity: .72;
    cursor: wait;
    transform: none;
}
#dm-dns-modify-modal-1038 .dm-dns-modify-actions .dm-dns-modify-cancel:first-child:last-child { margin-left: auto; }
@media (max-width: 640px) {
    #dm-dns-modify-modal-1038 { padding-top: 5vh; }
    #dm-dns-modify-modal-1038 .dm-dns-modify-head { padding: 14px 15px; }
    #dm-dns-modify-modal-1038 .dm-dns-modify-body { padding: 16px; }
    #dm-dns-modify-modal-1038 .dm-dns-modify-field,
    #dm-dns-modify-modal-1038 .dm-dns-modify-type-row {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    #dm-dns-modify-modal-1038 .dm-dns-modify-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }
    #dm-dns-modify-modal-1038 .dm-dns-modify-submit,
    #dm-dns-modify-modal-1038 .dm-dns-modify-cancel,
    #dm-dns-modify-modal-1038 .dm-dns-modify-fullpage {
        width: 100%;
    }
}
</style>
<script id="dm-dns-modify-modal-1038-script">
(function () {
    'use strict';

    if (window.dmDnsModifyModal1038Loaded) {
        return;
    }
    window.dmDnsModifyModal1038Loaded = true;

    var modal;
    var lastSourceForm = null;
    var lastSourceSubmitter = null;
    var reloadTimer = null;
    var saveCloseArmed = false;
    var saveSuccessObserver = null;

    function isModifyAction(form) {
        if (!form || !form.getAttribute) {
            return false;
        }
        var action = String(form.getAttribute('action') || '');
        return action.indexOf('managednszonemodify') !== -1;
    }

    function isSaveForm(form) {
        return !!(form && form.querySelector && form.querySelector('input[name="modify"][value="true"], input[name="modify"][value="1"]'));
    }

    function isInsideModal(node) {
        return !!(modal && node && node.closest && node.closest('#dm-dns-modify-modal-1038'));
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function ensureModal() {
        if (modal) {
            return modal;
        }
        modal = document.createElement('div');
        modal.id = 'dm-dns-modify-modal-1038';
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = ''
            + '<div class="dm-dns-modify-card" role="dialog" aria-modal="true" aria-labelledby="dm-dns-modify-title-1038">'
            + '  <div class="dm-dns-modify-head">'
            + '    <div><strong id="dm-dns-modify-title-1038">Modify DNS Record</strong><span id="dm-dns-modify-subtitle-1038">Loading record editor…</span></div>'
            + '    <button type="button" class="dm-dns-modify-close" aria-label="Close DNS record editor">×</button>'
            + '  </div>'
            + '  <div class="dm-dns-modify-body"></div>'
            + '</div>';
        document.body.appendChild(modal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal || (event.target.closest && event.target.closest('.dm-dns-modify-close, .dm-dns-modify-cancel'))) {
                event.preventDefault();
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal && modal.classList.contains('dm-open')) {
                closeModal();
            }
        });

        modal.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || !form.classList || !form.classList.contains('dm-dns-modify-form')) {
                return;
            }
            event.preventDefault();
            submitModalForm(form, event.submitter || document.activeElement || null);
        }, true);

        return modal;
    }

    function setModalTitle(title, subtitle) {
        ensureModal();
        var titleNode = modal.querySelector('#dm-dns-modify-title-1038');
        var subtitleNode = modal.querySelector('#dm-dns-modify-subtitle-1038');
        if (titleNode) {
            titleNode.textContent = title || 'Modify DNS Record';
        }
        if (subtitleNode) {
            subtitleNode.textContent = subtitle || 'Edit the selected DNS record without leaving this page';
        }
    }

    function setModalBody(html) {
        ensureModal();
        var body = modal.querySelector('.dm-dns-modify-body');
        if (body) {
            body.innerHTML = html;
        }
    }

    function openModal() {
        ensureModal();
        modal.classList.add('dm-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('dm-dns-modify-modal-open');
        window.setTimeout(function () {
            var first = modal.querySelector('input:not([type="hidden"]), textarea, select, button');
            if (first && first.focus) {
                first.focus();
            }
        }, 80);
    }

    function closeModal() {
        if (!modal) {
            return;
        }
        modal.classList.remove('dm-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('dm-dns-modify-modal-open');
        saveCloseArmed = false;
        if (saveSuccessObserver) {
            saveSuccessObserver.disconnect();
            saveSuccessObserver = null;
        }
        if (reloadTimer) {
            window.clearTimeout(reloadTimer);
            reloadTimer = null;
        }
    }

    function showLoading(message) {
        setModalBody('<div class="dm-dns-modify-loading"><span class="dm-dns-modify-spinner" aria-hidden="true"></span><span>' + escapeHtml(message || 'Loading record editor…') + '</span></div>');
    }

    function showMessage(message, isError, fullPageUrl) {
        var extra = '';
        if (fullPageUrl) {
            extra = '<div class="dm-dns-modify-actions"><button type="button" class="dm-dns-modify-cancel">Close</button><a class="dm-dns-modify-fullpage" href="' + escapeHtml(fullPageUrl) + '">Open Full Page</a></div>';
        } else {
            extra = '<div class="dm-dns-modify-actions"><button type="button" class="dm-dns-modify-cancel">Close</button></div>';
        }
        setModalBody('<div class="dm-dns-modify-message" style="' + (isError ? 'border-color: rgba(185,74,72,.35); color: #b94a48;' : '') + '">' + escapeHtml(message) + '</div>' + extra);
    }

    function getActionUrl(form) {
        var action = form && form.getAttribute ? form.getAttribute('action') : '';
        try {
            return new URL(action || window.location.href, window.location.href).toString();
        } catch (err) {
            return action || window.location.href;
        }
    }

    function getFormData(form, submitter) {
        var data = new FormData(form);
        if (submitter && submitter.name && !data.has(submitter.name)) {
            data.append(submitter.name, submitter.value || '');
        }
        return data;
    }

    function fetchForm(form, submitter) {
        var method = String(form.getAttribute('method') || 'POST').toUpperCase();
        var options = {
            method: method,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        };
        var url = getActionUrl(form);
        if (method === 'GET') {
            var params = new URLSearchParams(getFormData(form, submitter));
            url += (url.indexOf('?') === -1 ? '?' : '&') + params.toString();
        } else {
            options.body = getFormData(form, submitter);
        }
        return fetch(url, options).then(function (response) {
            if (!response.ok) {
                throw new Error('The record editor could not be loaded.');
            }
            return response.text();
        });
    }

    function parseHtml(html) {
        return new DOMParser().parseFromString(html, 'text/html');
    }

    function findModifyForm(doc) {
        var forms = doc.querySelectorAll('form');
        var fallback = null;
        for (var i = 0; i < forms.length; i++) {
            if (!isModifyAction(forms[i])) {
                continue;
            }
            if (isSaveForm(forms[i])) {
                return forms[i];
            }
            if (!fallback) {
                fallback = forms[i];
            }
        }
        return fallback;
    }

    function collectAlerts(doc) {
        var alerts = [];
        var candidates = doc.querySelectorAll('.alert-danger, .alert-success');
        for (var i = 0; i < candidates.length; i++) {
            var clone = candidates[i].cloneNode(true);
            alerts.push(clone.outerHTML);
        }
        return alerts.join('');
    }

    function getPageText(doc, html) {
        var text = '';
        if (doc && doc.body) {
            text += ' ' + normalizeText(doc.body.textContent || '');
        }
        text += ' ' + normalizeText(String(html || '').replace(/<script[\s\S]*?<\/script>/gi, ' ').replace(/<style[\s\S]*?<\/style>/gi, ' ').replace(/<[^>]+>/g, ' '));
        return normalizeText(text);
    }

    function hasModifySuccess(doc, html) {
        var text = getPageText(doc, html);
        return /record has been modified successfully/i.test(text)
            || /has been modified successfully/i.test(text)
            || !!(doc && doc.querySelector && doc.querySelector('.alert-success'));
    }

    function modalShowsModifySuccess() {
        if (!modal || !saveCloseArmed) {
            return false;
        }
        var body = modal.querySelector('.dm-dns-modify-body');
        var text = normalizeText(body ? body.textContent || '' : '');
        return /record has been modified successfully/i.test(text)
            || /has been modified successfully/i.test(text);
    }

    function armCloseOnVisibleSuccess() {
        ensureModal();
        saveCloseArmed = true;
        if (saveSuccessObserver) {
            saveSuccessObserver.disconnect();
        }
        var body = modal.querySelector('.dm-dns-modify-body');
        if (!body || !window.MutationObserver) {
            return;
        }
        saveSuccessObserver = new MutationObserver(function () {
            if (modalShowsModifySuccess()) {
                closeAfterSaveSuccess();
            }
        });
        saveSuccessObserver.observe(body, { childList: true, subtree: true, characterData: true });
    }

    function hasModifyError(doc, html) {
        var text = getPageText(doc, html);
        return !!(doc && doc.querySelector && doc.querySelector('.alert-danger'))
            || /error/i.test(text) && !/record has been modified successfully/i.test(text);
    }

    function closeAfterSaveSuccess() {
        closeModal();
        window.setTimeout(function () {
            window.location.reload();
        }, 250);
    }

    function getRecordSubtitle(form) {
        var typeInput = form.querySelector('input[name="nsrecordtype"]');
        var domainInput = form.querySelector('input[name="domain"]');
        var hostInput = form.querySelector('input[name="host"]');
        var type = typeInput ? typeInput.value : '';
        var domain = domainInput ? domainInput.value : '';
        var host = hostInput ? hostInput.value : '';
        var record = type ? (type.toUpperCase() + ' Record') : 'DNS Record';
        if (host && domain && host !== '@') {
            return record + ' · ' + host + '.' + domain;
        }
        if (domain) {
            return record + ' · ' + domain;
        }
        return 'Edit the selected DNS record without leaving this page';
    }

    function copyHiddenInputs(sourceForm, targetForm) {
        var hidden = sourceForm.querySelectorAll('input[type="hidden"]');
        for (var i = 0; i < hidden.length; i++) {
            targetForm.appendChild(hidden[i].cloneNode(true));
        }
    }

    function buildField(label, contentNode) {
        var field = document.createElement('div');
        field.className = 'dm-dns-modify-field';

        var labelNode = document.createElement('label');
        labelNode.textContent = label;

        var control = document.createElement('div');
        control.className = 'dm-dns-modify-control';
        while (contentNode.firstChild) {
            control.appendChild(contentNode.firstChild);
        }

        var firstControl = control.querySelector('input:not([type="hidden"]), textarea, select');
        if (firstControl) {
            var id = firstControl.getAttribute('id');
            if (!id) {
                id = 'dm-dns-modify-field-' + Math.random().toString(36).slice(2);
                firstControl.setAttribute('id', id);
            }
            labelNode.setAttribute('for', id);
        }

        field.appendChild(labelNode);
        field.appendChild(control);
        return field;
    }

    function buildModernForm(sourceForm, alertsHtml) {
        var form = document.createElement('form');
        form.method = sourceForm.getAttribute('method') || 'post';
        form.action = sourceForm.getAttribute('action') || 'dnsmanagement.php?action=managednszonemodify';
        form.className = 'dm-dns-modify-form';

        copyHiddenInputs(sourceForm, form);

        var typeInput = sourceForm.querySelector('input[name="nsrecordtype"]');
        var recordType = typeInput && typeInput.value ? typeInput.value.toUpperCase() : 'DNS';
        var titleText = 'Modify DNS Record';
        var h3 = sourceForm.querySelector('h3');
        if (h3 && normalizeText(h3.textContent)) {
            titleText = normalizeText(h3.textContent).replace(/^modify\s+/i, 'Modify ');
        }
        setModalTitle('Modify DNS Record', getRecordSubtitle(sourceForm));

        if (alertsHtml) {
            var alerts = document.createElement('div');
            alerts.className = 'dm-dns-modify-alerts';
            alerts.innerHTML = alertsHtml;
            form.appendChild(alerts);
        }

        var typeRow = document.createElement('div');
        typeRow.className = 'dm-dns-modify-type-row';
        typeRow.innerHTML = '<span>Type</span><strong class="dm-dns-modify-type-pill">' + escapeHtml(recordType) + '</strong>';
        form.appendChild(typeRow);

        var fields = document.createElement('div');
        fields.className = 'dm-dns-modify-fields';

        var table = sourceForm.querySelector('table');
        var rows = table ? table.querySelectorAll('tr') : [];
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].children;
            if (cells.length < 2) {
                continue;
            }
            if (cells[0].getAttribute('colspan') || cells[0].querySelector('h1,h2,h3') || cells[0].querySelector('input[type="submit"],button[type="submit"]')) {
                continue;
            }
            var label = normalizeText(cells[0].textContent).replace(/:$/, '');
            if (!label) {
                continue;
            }
            var content = cells[1].cloneNode(true);
            if (!content.querySelector('input:not([type="hidden"]), textarea, select') && normalizeText(content.textContent) === '') {
                continue;
            }
            fields.appendChild(buildField(label, content));
        }

        if (!fields.children.length) {
            var original = sourceForm.cloneNode(true);
            var submitRow = original.querySelector('input[type="submit"], button[type="submit"]');
            if (submitRow) {
                submitRow.closest('tr, p, div') && submitRow.closest('tr, p, div').remove();
            }
            fields.appendChild(buildField(titleText, original));
        }

        form.appendChild(fields);

        var actions = document.createElement('div');
        actions.className = 'dm-dns-modify-actions';
        actions.innerHTML = '<button type="button" class="dm-dns-modify-cancel">Cancel</button><button type="submit" class="dm-dns-modify-submit">Save Changes</button>';
        form.appendChild(actions);

        return form;
    }

    function renderResponse(html, isAfterSave) {
        var doc = parseHtml(html);
        var alertsHtml = collectAlerts(doc);
        var hasSuccess = hasModifySuccess(doc, html);
        var hasError = hasModifyError(doc, html);
        var form = findModifyForm(doc);
        var body = modal.querySelector('.dm-dns-modify-body');

        if (hasSuccess && isAfterSave) {
            closeAfterSaveSuccess();
            return;
        }

        if (form) {
            body.innerHTML = '';
            body.appendChild(buildModernForm(form, alertsHtml));
            return;
        }

        if (hasSuccess) {
            closeAfterSaveSuccess();
            return;
        }

        showMessage('The record editor did not return an editable form. The full modify page is still available as a fallback.', true, lastSourceForm ? getActionUrl(lastSourceForm) : 'dnsmanagement.php?action=managednszonemodify');
    }

    function openModifyFromSource(form, submitter) {
        if (!window.fetch || !window.FormData || !window.DOMParser) {
            return false;
        }
        lastSourceForm = form;
        lastSourceSubmitter = submitter || null;
        ensureModal();
        setModalTitle('Modify DNS Record', 'Loading record editor…');
        showLoading('Loading record editor…');
        openModal();
        fetchForm(form, submitter)
            .then(function (html) {
                renderResponse(html, false);
            })
            .catch(function () {
                showMessage('The popup could not load the modify form. You can still open the original modify page.', true, getActionUrl(form));
            });
        return true;
    }

    function submitModalForm(form, submitter) {
        armCloseOnVisibleSuccess();
        var button = form.querySelector('.dm-dns-modify-submit');
        if (button) {
            button.disabled = true;
            button.textContent = 'Saving…';
        }
        fetchForm(form, submitter)
            .then(function (html) {
                renderResponse(html, true);
                if (modalShowsModifySuccess()) {
                    closeAfterSaveSuccess();
                }
            })
            .catch(function () {
                saveCloseArmed = false;
                if (saveSuccessObserver) {
                    saveSuccessObserver.disconnect();
                    saveSuccessObserver = null;
                }
                if (button) {
                    button.disabled = false;
                    button.textContent = 'Save Changes';
                }
                showMessage('The record could not be saved in the popup. Please try again or open the full modify page.', true, getActionUrl(form));
            });
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        var target = event.target;
        var button = target && target.closest ? target.closest('input[type="submit"], button[type="submit"], .btn') : null;
        var form = button && button.form ? button.form : (target && target.closest ? target.closest('form') : null);
        if (!form || !isModifyAction(form) || isSaveForm(form) || isInsideModal(form)) {
            return;
        }
        var label = normalizeText(button && (button.value || button.textContent));
        if (label && !/^modify$/i.test(label)) {
            return;
        }
        if (openModifyFromSource(form, button)) {
            event.preventDefault();
            event.stopPropagation();
            if (event.stopImmediatePropagation) {
                event.stopImmediatePropagation();
            }
        }
    }, true);

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || !isModifyAction(form) || isSaveForm(form) || isInsideModal(form)) {
            return;
        }
        if (openModifyFromSource(form, event.submitter || document.activeElement || null)) {
            event.preventDefault();
            event.stopPropagation();
            if (event.stopImmediatePropagation) {
                event.stopImmediatePropagation();
            }
        }
    }, true);
})();
</script>
HTML;
});
