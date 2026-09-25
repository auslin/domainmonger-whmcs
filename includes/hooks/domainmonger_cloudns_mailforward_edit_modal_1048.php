<?php
/**
 * DomainMonger ClouDNS Mail Forward Edit Modal 1048
 *
 * Adds a DomainMonger-styled modal for ClouDNS Mail Forwards pencil/Edit actions.
 * Scope is intentionally limited to the ClouDNS Mail Forwards edit flow:
 *   clientarea.php?action=productdetails&customAction=do-edit-forward
 *   clientarea.php?action=productdetails&customAction=edit-forward
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
<style id="dm-cloudns-forward-modal-1048-style">
.dm-cloudns-forward-modal-open { overflow: hidden; }
#dm-cloudns-forward-modal-1048 {
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
#dm-cloudns-forward-modal-1048.dm-open { display: flex; }
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-card {
    width: min(760px, calc(100vw - 36px));
    max-height: calc(100vh - 80px);
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, .34);
    overflow: hidden;
    color: #1f2a35;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 18px;
    background: #163a5f;
    color: #ffffff;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-head strong {
    display: block;
    margin: 0;
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-head span {
    display: block;
    margin-top: 3px;
    color: rgba(255, 255, 255, .82);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .02em;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-close {
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
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-close:hover,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-close:focus {
    background: #f58220;
    color: #ffffff;
    transform: scale(1.04);
    outline: none;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-body {
    padding: 20px 24px 22px;
    overflow: auto;
    max-height: calc(100vh - 165px);
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-loading,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-message {
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
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-spinner {
    width: 22px;
    height: 22px;
    border: 3px solid rgba(245, 130, 32, .25);
    border-top-color: #f58220;
    border-radius: 50%;
    animation: dmCloudnsForwardSpin1048 .8s linear infinite;
}
@keyframes dmCloudnsForwardSpin1048 { to { transform: rotate(360deg); } }
#dm-cloudns-forward-modal-1048 form#recordsForm,
#dm-cloudns-forward-modal-1048 form.recordsForm,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-form {
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
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-alerts {
    display: grid;
    gap: 10px;
    margin: 0 0 16px;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-alerts .notification,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-alerts .cloudns-response,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-alerts .alert {
    margin: 0 !important;
    border-radius: 8px !important;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-type-pill {
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
#dm-cloudns-forward-modal-1048 form#recordsForm br,
#dm-cloudns-forward-modal-1048 form.recordsForm br {
    line-height: 1.1;
}
#dm-cloudns-forward-modal-1048 form#recordsForm div,
#dm-cloudns-forward-modal-1048 form.recordsForm div,
#dm-cloudns-forward-modal-1048 form#recordsForm t,
#dm-cloudns-forward-modal-1048 form.recordsForm t {
    color: #263646;
    font-size: 14px;
    font-weight: 700;
}
#dm-cloudns-forward-modal-1048 form#recordsForm small,
#dm-cloudns-forward-modal-1048 form.recordsForm small,
#dm-cloudns-forward-modal-1048 form#recordsForm .small,
#dm-cloudns-forward-modal-1048 form.recordsForm .small {
    color: #657586 !important;
    font-size: 12px !important;
    line-height: 1.35 !important;
}
#dm-cloudns-forward-modal-1048 form#recordsForm input[type="text"],
#dm-cloudns-forward-modal-1048 form#recordsForm input[type="number"],
#dm-cloudns-forward-modal-1048 form#recordsForm input[type="email"],
#dm-cloudns-forward-modal-1048 form#recordsForm select,
#dm-cloudns-forward-modal-1048 form#recordsForm textarea,
#dm-cloudns-forward-modal-1048 form#recordsForm .form-control,
#dm-cloudns-forward-modal-1048 form.recordsForm input[type="text"],
#dm-cloudns-forward-modal-1048 form.recordsForm input[type="number"],
#dm-cloudns-forward-modal-1048 form.recordsForm input[type="email"],
#dm-cloudns-forward-modal-1048 form.recordsForm select,
#dm-cloudns-forward-modal-1048 form.recordsForm textarea,
#dm-cloudns-forward-modal-1048 form.recordsForm .form-control {
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
#dm-cloudns-forward-modal-1048 form#recordsForm textarea,
#dm-cloudns-forward-modal-1048 form.recordsForm textarea {
    min-height: 110px !important;
    resize: vertical !important;
}
#dm-cloudns-forward-modal-1048 form#recordsForm input:focus,
#dm-cloudns-forward-modal-1048 form#recordsForm select:focus,
#dm-cloudns-forward-modal-1048 form#recordsForm textarea:focus,
#dm-cloudns-forward-modal-1048 form.recordsForm input:focus,
#dm-cloudns-forward-modal-1048 form.recordsForm select:focus,
#dm-cloudns-forward-modal-1048 form.recordsForm textarea:focus {
    border-color: #f58220 !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .18) !important;
    background: #ffffff !important;
}
#dm-cloudns-forward-modal-1048 form#recordsForm .spanHost,
#dm-cloudns-forward-modal-1048 form.recordsForm .spanHost {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    width: 100% !important;
    margin: 4px 0 0 !important;
    color: #263646 !important;
    font-weight: 700 !important;
    word-break: break-word !important;
}
#dm-cloudns-forward-modal-1048 form#recordsForm #editRecordHost,
#dm-cloudns-forward-modal-1048 form.recordsForm #editRecordHost {
    flex: 1 1 220px !important;
    width: auto !important;
    min-width: 160px !important;
}
#dm-cloudns-forward-modal-1048 form#recordsForm .pull-left,
#dm-cloudns-forward-modal-1048 form#recordsForm .pull-right,
#dm-cloudns-forward-modal-1048 form.recordsForm .pull-left,
#dm-cloudns-forward-modal-1048 form.recordsForm .pull-right {
    float: none !important;
}
#dm-cloudns-forward-modal-1048 form#recordsForm .inputSRV,
#dm-cloudns-forward-modal-1048 form#recordsForm .inputTitle,
#dm-cloudns-forward-modal-1048 form#recordsForm .selectTLSA,
#dm-cloudns-forward-modal-1048 form#recordsForm .selectDS,
#dm-cloudns-forward-modal-1048 form#recordsForm .selectCERT,
#dm-cloudns-forward-modal-1048 form#recordsForm .selectLOC,
#dm-cloudns-forward-modal-1048 form#recordsForm .inputLOC,
#dm-cloudns-forward-modal-1048 form#recordsForm .inputFirstLOC,
#dm-cloudns-forward-modal-1048 form.recordsForm .inputSRV,
#dm-cloudns-forward-modal-1048 form.recordsForm .inputTitle,
#dm-cloudns-forward-modal-1048 form.recordsForm .selectTLSA,
#dm-cloudns-forward-modal-1048 form.recordsForm .selectDS,
#dm-cloudns-forward-modal-1048 form.recordsForm .selectCERT,
#dm-cloudns-forward-modal-1048 form.recordsForm .selectLOC,
#dm-cloudns-forward-modal-1048 form.recordsForm .inputLOC,
#dm-cloudns-forward-modal-1048 form.recordsForm .inputFirstLOC {
    width: 100% !important;
    max-width: 100% !important;
    margin: 6px 0 10px !important;
    box-sizing: border-box !important;
}
#dm-cloudns-forward-modal-1048 form#recordsForm h5,
#dm-cloudns-forward-modal-1048 form.recordsForm h5 {
    margin: 14px 0 8px !important;
    color: #163a5f !important;
    font-weight: 800 !important;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #edf0f4;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-submit,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-cancel,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-fullpage {
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
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-submit {
    background: #f58220 !important;
    color: #ffffff !important;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-submit:hover,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-submit:focus {
    background: #d8741f !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(245, 130, 32, .28) !important;
    transform: translateY(-1px);
    outline: none !important;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-cancel,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-fullpage {
    background: #163a5f !important;
    color: #ffffff !important;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-cancel:hover,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-cancel:focus,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-fullpage:hover,
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-fullpage:focus {
    background: #214e7a !important;
    color: #ffffff !important;
    outline: none !important;
}
#dm-cloudns-forward-modal-1048 .dm-cloudns-forward-submit[disabled] {
    opacity: .72 !important;
    cursor: wait !important;
    transform: none !important;
}
@media (max-width: 640px) {
    #dm-cloudns-forward-modal-1048 { padding-top: 5vh; }
    #dm-cloudns-forward-modal-1048 .dm-cloudns-forward-head { padding: 14px 15px; }
    #dm-cloudns-forward-modal-1048 .dm-cloudns-forward-body { padding: 16px; }
    #dm-cloudns-forward-modal-1048 .dm-cloudns-forward-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }
    #dm-cloudns-forward-modal-1048 .dm-cloudns-forward-submit,
    #dm-cloudns-forward-modal-1048 .dm-cloudns-forward-cancel,
    #dm-cloudns-forward-modal-1048 .dm-cloudns-forward-fullpage {
        width: 100% !important;
    }
}
</style>
<script id="dm-cloudns-forward-modal-1048-script">
(function () {
    'use strict';

    if (window.dmCloudnsForwardModal1048Loaded) {
        return;
    }
    window.dmCloudnsForwardModal1048Loaded = true;

    var modal = null;
    var lastForwardEditUrl = '';
    var lastMailForwardUrl = '';

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

    function isClouDnsForwardEditUrl(url) {
        return /clientarea\.php/i.test(String(url || ''))
            && /action=productdetails/i.test(String(url || ''))
            && /customAction=do-edit-forward/i.test(String(url || ''))
            && /forward_id=/i.test(String(url || ''));
    }

    function isClouDnsForwardSaveUrl(url) {
        return /clientarea\.php/i.test(String(url || ''))
            && /action=productdetails/i.test(String(url || ''))
            && /customAction=edit-forward/i.test(String(url || ''));
    }

    function isInsideModal(node) {
        return !!(modal && node && node.closest && node.closest('#dm-cloudns-forward-modal-1048'));
    }

    function ensureModal() {
        if (modal) {
            return modal;
        }
        modal = document.createElement('div');
        modal.id = 'dm-cloudns-forward-modal-1048';
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = ''
            + '<div class="dm-cloudns-forward-card" role="dialog" aria-modal="true" aria-labelledby="dm-cloudns-forward-title-1048">'
            + '  <div class="dm-cloudns-forward-head">'
            + '    <div><strong id="dm-cloudns-forward-title-1048">Modify Mail Forward</strong><span id="dm-cloudns-forward-subtitle-1048">Loading mail forward editor…</span></div>'
            + '    <button type="button" class="dm-cloudns-forward-close" aria-label="Close mail forward editor">×</button>'
            + '  </div>'
            + '  <div class="dm-cloudns-forward-body"></div>'
            + '</div>';
        document.body.appendChild(modal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal || (event.target.closest && event.target.closest('.dm-cloudns-forward-close, .dm-cloudns-forward-cancel'))) {
                event.preventDefault();
                closeModal();
            }
        });

        modal.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || !form.matches || !form.matches('form.dm-cloudns-forward-form')) {
                return;
            }
            event.preventDefault();
            submitForwardEditForm(form, event.submitter || document.activeElement || null);
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
        var titleNode = modal.querySelector('#dm-cloudns-forward-title-1048');
        var subtitleNode = modal.querySelector('#dm-cloudns-forward-subtitle-1048');
        if (titleNode) {
            titleNode.textContent = title || 'Modify Mail Forward';
        }
        if (subtitleNode) {
            subtitleNode.textContent = subtitle || 'Edit the selected mail forward without leaving this page';
        }
    }

    function setModalBody(html) {
        ensureModal();
        var body = modal.querySelector('.dm-cloudns-forward-body');
        if (body) {
            body.innerHTML = html;
        }
    }

    function openModal() {
        ensureModal();
        modal.classList.add('dm-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('dm-cloudns-forward-modal-open');
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
        document.body.classList.remove('dm-cloudns-forward-modal-open');
    }

    function showLoading(message) {
        setModalBody('<div class="dm-cloudns-forward-loading"><span class="dm-cloudns-forward-spinner" aria-hidden="true"></span><span>' + escapeHtml(message || 'Loading mail forward editor…') + '</span></div>');
    }

    function showMessage(message, isError, fullPageUrl) {
        var style = isError ? 'border-color: rgba(185,74,72,.35); color: #b94a48;' : '';
        var extra = '<div class="dm-cloudns-forward-actions"><button type="button" class="dm-cloudns-forward-cancel">Close</button>';
        if (fullPageUrl) {
            extra += '<a class="dm-cloudns-forward-fullpage" href="' + escapeHtml(fullPageUrl) + '">Open Full Page</a>';
        }
        extra += '</div>';
        setModalBody('<div class="dm-cloudns-forward-message" style="' + style + '">' + escapeHtml(message) + '</div>' + extra);
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
                throw new Error('Could not load the mail forward editor.');
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
                throw new Error('Could not save the mail forward.');
            }
            return response.text().then(function (html) {
                return { html: html, url: response.url || action };
            });
        });
    }

    function parseHtml(html) {
        return new DOMParser().parseFromString(html, 'text/html');
    }

    function findForwardEditForm(doc) {
        if (!doc || !doc.querySelector) {
            return null;
        }
        var exact = doc.querySelector('form#recordsForm[action*="customAction=edit-forward"]');
        if (exact) {
            return exact;
        }
        var forms = doc.querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) {
            if (isClouDnsForwardSaveUrl(forms[i].getAttribute('action') || '')) {
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

    function getMailForwardUrlFromEditUrl(url) {
        try {
            var parsed = new URL(url, window.location.href);
            parsed.searchParams.set('customAction', 'mail-forwarding');
            parsed.searchParams.delete('forward_id');
            return parsed.toString();
        } catch (err) {
            return '';
        }
    }

    function getSubtitleFromLink(link) {
        var row = link && link.closest ? link.closest('tr') : null;
        if (!row) {
            return 'Edit the selected mail forward without leaving this page';
        }
        var cells = row.querySelectorAll('td');
        var source = normalizeText(cells[1] ? cells[1].textContent : '');
        var destination = normalizeText(cells[2] ? cells[2].textContent : '');
        if (source && destination) {
            return source + ' → ' + destination;
        }
        if (source) {
            return source;
        }
        return 'Edit the selected mail forward without leaving this page';
    }

    function getForwardTypeFromForm(form) {
        return 'MAIL FORWARD';
    }

    function prepareForwardForm(form, alertsHtml) {
        var clone = form.cloneNode(true);
        clone.classList.add('dm-cloudns-forward-form');
        clone.setAttribute('data-dm-cloudns-modal-form', '1');

        var type = getForwardTypeFromForm(clone);
        var currentHtml = clone.innerHTML;
        currentHtml = currentHtml.replace(/^\s*Type:\s*(?:Web Redirect|[A-Z0-9]+)\s*/i, '');
        clone.innerHTML = currentHtml;

        var submit = clone.querySelector('input[type="submit"], button[type="submit"]');
        var actions = document.createElement('div');
        actions.className = 'dm-cloudns-forward-actions';
        actions.innerHTML = '<button type="button" class="dm-cloudns-forward-cancel">Cancel</button>';

        if (submit) {
            submit.value = 'Save Changes';
            submit.textContent = 'Save Changes';
            submit.className = 'btn dm-cloudns-forward-submit';
            actions.appendChild(submit);
        } else {
            var newSubmit = document.createElement('button');
            newSubmit.type = 'submit';
            newSubmit.className = 'btn dm-cloudns-forward-submit';
            newSubmit.textContent = 'Save Changes';
            actions.appendChild(newSubmit);
        }

        var wrapper = document.createElement('div');
        if (alertsHtml) {
            var alerts = document.createElement('div');
            alerts.className = 'dm-cloudns-forward-alerts';
            alerts.innerHTML = alertsHtml;
            wrapper.appendChild(alerts);
        }
        var typePill = document.createElement('div');
        typePill.className = 'dm-cloudns-forward-type-pill';
        typePill.textContent = type;
        wrapper.appendChild(typePill);
        wrapper.appendChild(clone);
        clone.appendChild(actions);
        return wrapper;
    }

    function renderForwardEditPage(result) {
        var doc = parseHtml(result.html);
        var form = findForwardEditForm(doc);
        if (!form) {
            showMessage('The ClouDNS mail forward editor did not return an editable form. The original page is still available.', true, lastForwardEditUrl);
            return;
        }
        setModalTitle('Modify Mail Forward', 'Edit the selected ClouDNS mail forward without leaving this page');
        var body = modal.querySelector('.dm-cloudns-forward-body');
        body.innerHTML = '';
        body.appendChild(prepareForwardForm(form, collectAlerts(doc)));
    }

    function responseLooksSuccessful(result) {
        var doc = parseHtml(result.html);
        if (/customAction=mail-forwarding/i.test(result.url || '') && !/customAction=edit-forward/i.test(result.url || '')) {
            return true;
        }
        if (doc.querySelector('#table-forwards')) {
            return true;
        }
        if (!findForwardEditForm(doc) && /mail-forwarding/i.test(result.html)) {
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

    function openForwardEditModal(link) {
        if (!window.fetch || !window.FormData || !window.DOMParser) {
            return false;
        }
        lastForwardEditUrl = absoluteUrl(link.href || link.getAttribute('href') || '');
        lastMailForwardUrl = getMailForwardUrlFromEditUrl(lastForwardEditUrl);
        ensureModal();
        setModalTitle('Modify Mail Forward', getSubtitleFromLink(link));
        showLoading('Loading mail forward editor…');
        openModal();
        fetchUrl(lastForwardEditUrl)
            .then(renderForwardEditPage)
            .catch(function () {
                showMessage('The popup could not load the ClouDNS mail forward editor. You can still open the original page.', true, lastForwardEditUrl);
            });
        return true;
    }

    function submitForwardEditForm(form, submitter) {
        var button = form.querySelector('.dm-cloudns-forward-submit');
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
                var editForm = findForwardEditForm(doc);
                if (editForm) {
                    var body = modal.querySelector('.dm-cloudns-forward-body');
                    body.innerHTML = '';
                    body.appendChild(prepareForwardForm(editForm, collectAlerts(doc)));
                    return;
                }
                if (!responseLooksError(result)) {
                    closeAfterSuccess();
                    return;
                }
                showMessage('The mail forward could not be saved in the popup. Please review the response or open the full edit page.', true, lastForwardEditUrl);
            })
            .catch(function () {
                if (button) {
                    button.disabled = false;
                    button.value = 'Save Changes';
                    button.textContent = 'Save Changes';
                }
                showMessage('The mail forward could not be saved in the popup. Please try again or open the full edit page.', true, lastForwardEditUrl);
            });
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        var link = event.target && event.target.closest ? event.target.closest('a[href*="customAction=do-edit-forward"][href*="forward_id="]') : null;
        if (!link || isInsideModal(link) || !isClouDnsForwardEditUrl(link.getAttribute('href') || link.href || '')) {
            return;
        }
        if (openForwardEditModal(link)) {
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
