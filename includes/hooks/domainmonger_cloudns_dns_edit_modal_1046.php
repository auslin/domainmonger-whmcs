<?php
/**
 * DomainMonger ClouDNS DNS Edit Modal 1046
 *
 * Adds a DomainMonger-styled modal for ClouDNS DNS Records pencil/Edit actions.
 * Scope is intentionally limited to the ClouDNS DNS Records edit flow:
 *   clientarea.php?action=productdetails&customAction=edit-record
 *   clientarea.php?action=productdetails&customAction=do-edit-record
 *
 * It preserves the existing ClouDNS backend form/action and falls back to the
 * original full page if the popup cannot load.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    $scriptName = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = (string) ($_GET['action'] ?? '');

    if ($scriptName !== 'clientarea.php' || $action !== 'productdetails') {
        return '';
    }

    return <<<'HTML'
<style id="dm-cloudns-edit-modal-1046-style">
.dm-cloudns-edit-modal-open { overflow: hidden; }
#dm-cloudns-edit-modal-1046 {
    position: fixed;
    inset: 0;
    z-index: 2147483000;
    display: none;
    align-items: flex-start;
    justify-content: center;
    padding: 7vh 18px 40px;
    background: rgba(16, 22, 29, .68);
    backdrop-filter: blur(2px);
}
#dm-cloudns-edit-modal-1046.dm-open { display: flex; }
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-card {
    width: min(760px, calc(100vw - 36px));
    max-height: calc(100vh - 80px);
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, .34);
    overflow: hidden;
    color: #1f2a35;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 18px;
    background: #163a5f;
    color: #ffffff;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-head strong {
    display: block;
    margin: 0;
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-head span {
    display: block;
    margin-top: 3px;
    color: rgba(255, 255, 255, .82);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .02em;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-close {
    flex: 0 0 auto;
    width: 34px;
    height: 34px;
    border: 2px solid rgba(255, 255, 255, .7);
    border-radius: 50%;
    background: #ffffff;
    color: #163a5f;
    font-size: 24px;
    line-height: 27px;
    text-align: center;
    font-weight: 700;
    cursor: pointer;
    transition: background .16s ease, color .16s ease, transform .16s ease;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-close:hover,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-close:focus {
    background: #f58220;
    color: #ffffff;
    transform: scale(1.04);
    outline: none;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-body {
    padding: 20px 24px 22px;
    overflow: auto;
    max-height: calc(100vh - 165px);
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-loading,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-message {
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
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-spinner {
    width: 22px;
    height: 22px;
    border: 3px solid rgba(245, 130, 32, .25);
    border-top-color: #f58220;
    border-radius: 50%;
    animation: dmCloudnsEditSpin1046 .8s linear infinite;
}
@keyframes dmCloudnsEditSpin1046 { to { transform: rotate(360deg); } }
#dm-cloudns-edit-modal-1046 form#recordsForm,
#dm-cloudns-edit-modal-1046 form.recordsForm,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-form {
    display: block !important;
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    background: #ffffff !important;
    box-shadow: none !important;
    color: #1f2a35 !important;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-alerts {
    display: grid;
    gap: 10px;
    margin: 0 0 16px;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-alerts .notification,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-alerts .cloudns-response,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-alerts .alert {
    margin: 0 !important;
    border-radius: 8px !important;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-record-type-pill {
    display: inline-flex;
    align-items: center;
    min-height: 32px;
    margin: 0 0 14px;
    padding: 6px 12px;
    border-radius: 8px;
    background: rgba(22, 58, 95, .08);
    color: #163a5f;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .03em;
}
#dm-cloudns-edit-modal-1046 form#recordsForm br,
#dm-cloudns-edit-modal-1046 form.recordsForm br {
    line-height: 1.1;
}
#dm-cloudns-edit-modal-1046 form#recordsForm div,
#dm-cloudns-edit-modal-1046 form.recordsForm div,
#dm-cloudns-edit-modal-1046 form#recordsForm t,
#dm-cloudns-edit-modal-1046 form.recordsForm t {
    color: #263646;
    font-size: 14px;
    font-weight: 700;
}
#dm-cloudns-edit-modal-1046 form#recordsForm small,
#dm-cloudns-edit-modal-1046 form.recordsForm small,
#dm-cloudns-edit-modal-1046 form#recordsForm .small,
#dm-cloudns-edit-modal-1046 form.recordsForm .small {
    color: #657586 !important;
    font-size: 12px !important;
    line-height: 1.35 !important;
}
#dm-cloudns-edit-modal-1046 form#recordsForm input[type="text"],
#dm-cloudns-edit-modal-1046 form#recordsForm input[type="number"],
#dm-cloudns-edit-modal-1046 form#recordsForm input[type="email"],
#dm-cloudns-edit-modal-1046 form#recordsForm select,
#dm-cloudns-edit-modal-1046 form#recordsForm textarea,
#dm-cloudns-edit-modal-1046 form#recordsForm .form-control,
#dm-cloudns-edit-modal-1046 form.recordsForm input[type="text"],
#dm-cloudns-edit-modal-1046 form.recordsForm input[type="number"],
#dm-cloudns-edit-modal-1046 form.recordsForm input[type="email"],
#dm-cloudns-edit-modal-1046 form.recordsForm select,
#dm-cloudns-edit-modal-1046 form.recordsForm textarea,
#dm-cloudns-edit-modal-1046 form.recordsForm .form-control {
    min-height: 38px !important;
    padding: 8px 10px !important;
    border: 1px solid #d7dee8 !important;
    border-radius: 7px !important;
    background: #f3f5f7 !important;
    color: #1f2a35 !important;
    box-shadow: none !important;
    font-size: 14px !important;
    line-height: 1.35 !important;
}
#dm-cloudns-edit-modal-1046 form#recordsForm textarea,
#dm-cloudns-edit-modal-1046 form.recordsForm textarea {
    min-height: 110px !important;
    resize: vertical !important;
}
#dm-cloudns-edit-modal-1046 form#recordsForm input:focus,
#dm-cloudns-edit-modal-1046 form#recordsForm select:focus,
#dm-cloudns-edit-modal-1046 form#recordsForm textarea:focus,
#dm-cloudns-edit-modal-1046 form.recordsForm input:focus,
#dm-cloudns-edit-modal-1046 form.recordsForm select:focus,
#dm-cloudns-edit-modal-1046 form.recordsForm textarea:focus {
    border-color: #f58220 !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .18) !important;
    background: #ffffff !important;
}
#dm-cloudns-edit-modal-1046 form#recordsForm .spanHost,
#dm-cloudns-edit-modal-1046 form.recordsForm .spanHost {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    width: 100% !important;
    margin: 4px 0 0 !important;
    color: #263646 !important;
    font-weight: 700 !important;
    word-break: break-word !important;
}
#dm-cloudns-edit-modal-1046 form#recordsForm #editRecordHost,
#dm-cloudns-edit-modal-1046 form.recordsForm #editRecordHost {
    flex: 1 1 220px !important;
    width: auto !important;
    min-width: 160px !important;
}
#dm-cloudns-edit-modal-1046 form#recordsForm .pull-left,
#dm-cloudns-edit-modal-1046 form#recordsForm .pull-right,
#dm-cloudns-edit-modal-1046 form.recordsForm .pull-left,
#dm-cloudns-edit-modal-1046 form.recordsForm .pull-right {
    float: none !important;
}
#dm-cloudns-edit-modal-1046 form#recordsForm .inputSRV,
#dm-cloudns-edit-modal-1046 form#recordsForm .inputTitle,
#dm-cloudns-edit-modal-1046 form#recordsForm .selectTLSA,
#dm-cloudns-edit-modal-1046 form#recordsForm .selectDS,
#dm-cloudns-edit-modal-1046 form#recordsForm .selectCERT,
#dm-cloudns-edit-modal-1046 form#recordsForm .selectLOC,
#dm-cloudns-edit-modal-1046 form#recordsForm .inputLOC,
#dm-cloudns-edit-modal-1046 form#recordsForm .inputFirstLOC,
#dm-cloudns-edit-modal-1046 form.recordsForm .inputSRV,
#dm-cloudns-edit-modal-1046 form.recordsForm .inputTitle,
#dm-cloudns-edit-modal-1046 form.recordsForm .selectTLSA,
#dm-cloudns-edit-modal-1046 form.recordsForm .selectDS,
#dm-cloudns-edit-modal-1046 form.recordsForm .selectCERT,
#dm-cloudns-edit-modal-1046 form.recordsForm .selectLOC,
#dm-cloudns-edit-modal-1046 form.recordsForm .inputLOC,
#dm-cloudns-edit-modal-1046 form.recordsForm .inputFirstLOC {
    width: 100% !important;
    max-width: 100% !important;
    margin: 6px 0 10px !important;
    box-sizing: border-box !important;
}
#dm-cloudns-edit-modal-1046 form#recordsForm h5,
#dm-cloudns-edit-modal-1046 form.recordsForm h5 {
    margin: 14px 0 8px !important;
    color: #163a5f !important;
    font-weight: 800 !important;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #edf0f4;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-submit,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-cancel,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-fullpage {
    min-width: 118px !important;
    min-height: 38px !important;
    border: 0 !important;
    border-radius: 999px !important;
    padding: 8px 18px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    text-align: center !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: background .16s ease, transform .16s ease, box-shadow .16s ease;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-submit {
    background: #f58220 !important;
    color: #ffffff !important;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-submit:hover,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-submit:focus {
    background: #d8741f !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(245, 130, 32, .28) !important;
    transform: translateY(-1px);
    outline: none !important;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-cancel,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-fullpage {
    background: #163a5f !important;
    color: #ffffff !important;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-cancel:hover,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-cancel:focus,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-fullpage:hover,
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-fullpage:focus {
    background: #214e7a !important;
    color: #ffffff !important;
    outline: none !important;
}
#dm-cloudns-edit-modal-1046 .dm-cloudns-edit-submit[disabled] {
    opacity: .72 !important;
    cursor: wait !important;
    transform: none !important;
}
@media (max-width: 640px) {
    #dm-cloudns-edit-modal-1046 { padding-top: 5vh; }
    #dm-cloudns-edit-modal-1046 .dm-cloudns-edit-head { padding: 14px 15px; }
    #dm-cloudns-edit-modal-1046 .dm-cloudns-edit-body { padding: 16px; }
    #dm-cloudns-edit-modal-1046 .dm-cloudns-edit-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }
    #dm-cloudns-edit-modal-1046 .dm-cloudns-edit-submit,
    #dm-cloudns-edit-modal-1046 .dm-cloudns-edit-cancel,
    #dm-cloudns-edit-modal-1046 .dm-cloudns-edit-fullpage {
        width: 100% !important;
    }
}
</style>
<script id="dm-cloudns-edit-modal-1046-script">
(function () {
    'use strict';

    if (window.dmCloudnsEditModal1046Loaded) {
        return;
    }
    window.dmCloudnsEditModal1046Loaded = true;

    var modal = null;
    var lastEditUrl = '';
    var lastZoneUrl = '';

    function normalizeText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function isClouDnsEditUrl(url) {
        return /clientarea\.php/i.test(String(url || ''))
            && /action=productdetails/i.test(String(url || ''))
            && /customAction=edit-record/i.test(String(url || ''))
            && /dns_record_id=/i.test(String(url || ''));
    }

    function isClouDnsDoEditUrl(url) {
        return /clientarea\.php/i.test(String(url || ''))
            && /action=productdetails/i.test(String(url || ''))
            && /customAction=do-edit-record/i.test(String(url || ''));
    }

    function isInsideModal(node) {
        return !!(modal && node && node.closest && node.closest('#dm-cloudns-edit-modal-1046'));
    }

    function ensureModal() {
        if (modal) {
            return modal;
        }
        modal = document.createElement('div');
        modal.id = 'dm-cloudns-edit-modal-1046';
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = ''
            + '<div class="dm-cloudns-edit-card" role="dialog" aria-modal="true" aria-labelledby="dm-cloudns-edit-title-1046">'
            + '  <div class="dm-cloudns-edit-head">'
            + '    <div><strong id="dm-cloudns-edit-title-1046">Modify DNS Record</strong><span id="dm-cloudns-edit-subtitle-1046">Loading record editor…</span></div>'
            + '    <button type="button" class="dm-cloudns-edit-close" aria-label="Close DNS record editor">×</button>'
            + '  </div>'
            + '  <div class="dm-cloudns-edit-body"></div>'
            + '</div>';
        document.body.appendChild(modal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal || (event.target.closest && event.target.closest('.dm-cloudns-edit-close, .dm-cloudns-edit-cancel'))) {
                event.preventDefault();
                closeModal();
            }
        });

        modal.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || !form.matches || !form.matches('form.dm-cloudns-edit-form')) {
                return;
            }
            event.preventDefault();
            submitEditForm(form, event.submitter || document.activeElement || null);
        }, true);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal && modal.classList.contains('dm-open')) {
                closeModal();
            }
        });

        return modal;
    }

    function setModalTitle(title, subtitle) {
        ensureModal();
        var titleNode = modal.querySelector('#dm-cloudns-edit-title-1046');
        var subtitleNode = modal.querySelector('#dm-cloudns-edit-subtitle-1046');
        if (titleNode) {
            titleNode.textContent = title || 'Modify DNS Record';
        }
        if (subtitleNode) {
            subtitleNode.textContent = subtitle || 'Edit the selected DNS record without leaving this page';
        }
    }

    function setModalBody(html) {
        ensureModal();
        var body = modal.querySelector('.dm-cloudns-edit-body');
        if (body) {
            body.innerHTML = html;
        }
    }

    function openModal() {
        ensureModal();
        modal.classList.add('dm-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('dm-cloudns-edit-modal-open');
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
        document.body.classList.remove('dm-cloudns-edit-modal-open');
    }

    function showLoading(message) {
        setModalBody('<div class="dm-cloudns-edit-loading"><span class="dm-cloudns-edit-spinner" aria-hidden="true"></span><span>' + escapeHtml(message || 'Loading record editor…') + '</span></div>');
    }

    function showMessage(message, isError, fullPageUrl) {
        var style = isError ? 'border-color: rgba(185,74,72,.35); color: #b94a48;' : '';
        var extra = '<div class="dm-cloudns-edit-actions"><button type="button" class="dm-cloudns-edit-cancel">Close</button>';
        if (fullPageUrl) {
            extra += '<a class="dm-cloudns-edit-fullpage" href="' + escapeHtml(fullPageUrl) + '">Open Full Page</a>';
        }
        extra += '</div>';
        setModalBody('<div class="dm-cloudns-edit-message" style="' + style + '">' + escapeHtml(message) + '</div>' + extra);
    }

    function absoluteUrl(url) {
        try {
            return new URL(url || window.location.href, window.location.href).toString();
        } catch (err) {
            return url || window.location.href;
        }
    }

    function fetchUrl(url) {
        return fetch(absoluteUrl(url), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Could not load the record editor.');
            }
            return response.text().then(function (html) {
                return { html: html, url: response.url || absoluteUrl(url) };
            });
        });
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
        var action = absoluteUrl(form.getAttribute('action') || window.location.href);
        var options = {
            method: method,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        };
        if (method === 'GET') {
            var params = new URLSearchParams(getFormData(form, submitter));
            action += (action.indexOf('?') === -1 ? '?' : '&') + params.toString();
        } else {
            options.body = getFormData(form, submitter);
        }
        return fetch(action, options).then(function (response) {
            if (!response.ok) {
                throw new Error('Could not save the record.');
            }
            return response.text().then(function (html) {
                return { html: html, url: response.url || action };
            });
        });
    }

    function parseHtml(html) {
        return new DOMParser().parseFromString(html, 'text/html');
    }

    function findEditForm(doc) {
        if (!doc || !doc.querySelector) {
            return null;
        }
        var exact = doc.querySelector('form#recordsForm[action*="customAction=do-edit-record"]');
        if (exact) {
            return exact;
        }
        var forms = doc.querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) {
            if (isClouDnsDoEditUrl(forms[i].getAttribute('action') || '')) {
                return forms[i];
            }
        }
        return null;
    }

    function collectAlerts(doc) {
        var alerts = [];
        if (!doc || !doc.querySelectorAll) {
            return '';
        }
        var nodes = doc.querySelectorAll('.notification, .cloudns-response, .alert-danger, .alert-success');
        for (var i = 0; i < nodes.length; i++) {
            var text = normalizeText(nodes[i].textContent || '');
            if (!text || /records\s*:\s*\d+/i.test(text)) {
                continue;
            }
            alerts.push(nodes[i].cloneNode(true).outerHTML);
        }
        return alerts.join('');
    }

    function getZoneUrlFromEditUrl(url) {
        try {
            var parsed = new URL(url, window.location.href);
            parsed.searchParams.set('customAction', 'zone-settings');
            parsed.searchParams.delete('dns_record_id');
            return parsed.toString();
        } catch (err) {
            return '';
        }
    }

    function getSubtitleFromLink(link) {
        var row = link && link.closest ? link.closest('tr') : null;
        if (!row) {
            return 'Edit the selected DNS record without leaving this page';
        }
        var cells = row.querySelectorAll('td');
        var host = normalizeText(cells[1] ? cells[1].textContent : '');
        var type = normalizeText(cells[2] ? cells[2].textContent : '');
        var points = normalizeText(cells[3] ? cells[3].textContent : '');
        var pieces = [];
        if (type) {
            pieces.push(type + ' Record');
        }
        if (host) {
            pieces.push(host);
        }
        if (points) {
            pieces.push(points);
        }
        return pieces.length ? pieces.join(' · ') : 'Edit the selected DNS record without leaving this page';
    }

    function getTypeFromForm(form) {
        var typeInput = form.querySelector('input[name="recordType"]');
        var type = typeInput && typeInput.value ? typeInput.value : '';
        return type ? type.toUpperCase() : 'DNS';
    }

    function prepareForm(form, alertsHtml) {
        var clone = form.cloneNode(true);
        clone.classList.add('dm-cloudns-edit-form');
        clone.setAttribute('data-dm-cloudns-modal-form', '1');

        var type = getTypeFromForm(clone);
        var currentHtml = clone.innerHTML;
        currentHtml = currentHtml.replace(/^\s*Type:\s*(?:Web Redirect|[A-Z0-9]+)\s*/i, '');
        clone.innerHTML = currentHtml;

        var submit = clone.querySelector('input[type="submit"], button[type="submit"]');
        var actions = document.createElement('div');
        actions.className = 'dm-cloudns-edit-actions';
        actions.innerHTML = '<button type="button" class="dm-cloudns-edit-cancel">Cancel</button>';

        if (submit) {
            submit.value = 'Save Changes';
            submit.textContent = 'Save Changes';
            submit.className = 'btn dm-cloudns-edit-submit';
            actions.appendChild(submit);
        } else {
            var newSubmit = document.createElement('button');
            newSubmit.type = 'submit';
            newSubmit.className = 'btn dm-cloudns-edit-submit';
            newSubmit.textContent = 'Save Changes';
            actions.appendChild(newSubmit);
        }

        var wrapper = document.createElement('div');
        if (alertsHtml) {
            var alerts = document.createElement('div');
            alerts.className = 'dm-cloudns-edit-alerts';
            alerts.innerHTML = alertsHtml;
            wrapper.appendChild(alerts);
        }
        var typePill = document.createElement('div');
        typePill.className = 'dm-cloudns-record-type-pill';
        typePill.textContent = type === 'WR' ? 'WEB REDIRECT' : type;
        wrapper.appendChild(typePill);
        wrapper.appendChild(clone);
        clone.appendChild(actions);
        return wrapper;
    }

    function renderEditPage(result) {
        var doc = parseHtml(result.html);
        var form = findEditForm(doc);
        if (!form) {
            showMessage('The ClouDNS record editor did not return an editable form. The original page is still available.', true, lastEditUrl);
            return;
        }
        setModalTitle('Modify DNS Record', 'Edit the selected ClouDNS record without leaving this page');
        var body = modal.querySelector('.dm-cloudns-edit-body');
        body.innerHTML = '';
        body.appendChild(prepareForm(form, collectAlerts(doc)));
    }

    function responseLooksSuccessful(result) {
        var doc = parseHtml(result.html);
        if (/customAction=zone-settings/i.test(result.url || '') && !/customAction=do-edit-record/i.test(result.url || '')) {
            return true;
        }
        if (doc.querySelector('#records-table')) {
            return true;
        }
        if (!findEditForm(doc) && /zone-settings/i.test(result.html)) {
            return true;
        }
        return false;
    }

    function responseLooksError(result) {
        var doc = parseHtml(result.html);
        var text = normalizeText(doc.body ? doc.body.textContent : result.html);
        return !!doc.querySelector('.notification, .cloudns-response-error, .alert-danger')
            || /error|failed|invalid|required|could not|missing/i.test(text);
    }

    function closeAfterSuccess() {
        closeModal();
        window.setTimeout(function () {
            window.location.reload();
        }, 250);
    }

    function openEditModal(link) {
        if (!window.fetch || !window.FormData || !window.DOMParser) {
            return false;
        }
        lastEditUrl = absoluteUrl(link.href || link.getAttribute('href') || '');
        lastZoneUrl = getZoneUrlFromEditUrl(lastEditUrl);
        ensureModal();
        setModalTitle('Modify DNS Record', getSubtitleFromLink(link));
        showLoading('Loading record editor…');
        openModal();
        fetchUrl(lastEditUrl)
            .then(renderEditPage)
            .catch(function () {
                showMessage('The popup could not load the ClouDNS record editor. You can still open the original page.', true, lastEditUrl);
            });
        return true;
    }

    function submitEditForm(form, submitter) {
        var button = form.querySelector('.dm-cloudns-edit-submit');
        if (button) {
            button.disabled = true;
            button.value = 'Saving…';
            button.textContent = 'Saving…';
        }
        fetchForm(form, submitter)
            .then(function (result) {
                if (responseLooksSuccessful(result)) {
                    closeAfterSuccess();
                    return;
                }
                var doc = parseHtml(result.html);
                var editForm = findEditForm(doc);
                if (editForm) {
                    var body = modal.querySelector('.dm-cloudns-edit-body');
                    body.innerHTML = '';
                    body.appendChild(prepareForm(editForm, collectAlerts(doc)));
                    return;
                }
                if (!responseLooksError(result)) {
                    closeAfterSuccess();
                    return;
                }
                showMessage('The record could not be saved in the popup. Please review the response or open the full edit page.', true, lastEditUrl);
            })
            .catch(function () {
                if (button) {
                    button.disabled = false;
                    button.value = 'Save Changes';
                    button.textContent = 'Save Changes';
                }
                showMessage('The record could not be saved in the popup. Please try again or open the full edit page.', true, lastEditUrl);
            });
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        var link = event.target && event.target.closest ? event.target.closest('a[href*="customAction=edit-record"][href*="dns_record_id="]') : null;
        if (!link || isInsideModal(link) || !isClouDnsEditUrl(link.getAttribute('href') || link.href || '')) {
            return;
        }
        if (openEditModal(link)) {
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
