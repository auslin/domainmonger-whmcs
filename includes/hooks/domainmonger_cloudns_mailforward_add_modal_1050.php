<?php
/**
 * DomainMonger ClouDNS Mail Forward Add Modal 1050 / Bulk Add 1526 / Width Allocation 1529
 *
 * Converts the existing DNSPlus Mail Forwards +Add popup into an expandable
 * bulk-add interface while preserving the native ClouDNS add-forward route.
 * Each row is submitted sequentially so partial failures can be corrected and
 * retried without resubmitting successful forwards.
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
<style id="dm-cloudns-forward-add-modal-1050-style">
.dm-cloudns-forward-add-modal-open { overflow: hidden; }
#dm-cloudns-forward-add-modal-1050 {
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
#dm-cloudns-forward-add-modal-1050.dm-open { display: flex; }
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-card {
    width: min(960px, calc(100vw - 36px));
    max-height: calc(100vh - 80px);
    overflow: hidden;
    border-radius: 12px;
    background: #ffffff;
    color: #1f2a35;
    box-shadow: 0 24px 70px rgba(0, 0, 0, .34);
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 18px;
    background: #163a5f;
    color: #ffffff;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-head strong {
    display: block;
    margin: 0;
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-head span {
    display: block;
    margin-top: 3px;
    color: rgba(255,255,255,.82);
    font-size: 12px;
    font-weight: 600;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-close {
    width: 32px;
    min-width: 32px;
    height: 32px;
    min-height: 32px;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #ffffff !important;
    font-size: 25px;
    line-height: 28px;
    box-shadow: none !important;
    cursor: pointer;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-close:hover,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-close:focus {
    background: transparent !important;
    color: #ffffff !important;
    outline: none !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-body {
    max-height: calc(100vh - 102px);
    overflow: auto;
    padding: 18px;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-loading,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-message,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-progress,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-error,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-help {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 12px;
    padding: 12px 14px;
    border: 1px solid rgba(22,58,95,.18);
    border-radius: 7px;
    background: #f7f9fb;
    color: #163a5f;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.45;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-error {
    border-color: rgba(185,74,72,.36);
    background: #fff5f5;
    color: #9f3735;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-help {
    display: block;
    margin: 12px 0 0;
    font-weight: 600;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-progress[hidden],
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-error[hidden],
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-help[hidden] {
    display: none !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-spinner {
    flex: 0 0 auto;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(245,130,32,.24);
    border-top-color: #f58220;
    border-radius: 50%;
    animation: dmCloudnsForwardSpin1050 .8s linear infinite;
}
@keyframes dmCloudnsForwardSpin1050 { to { transform: rotate(360deg); } }
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-table-wrap {
    overflow-x: auto;
    border: 1px solid rgba(22,58,95,.18);
    border-radius: 7px;
    background: #ffffff;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-grid {
    display: grid;
    grid-template-columns: 390px minmax(300px, 1fr) 164px;
    gap: 10px;
    min-width: 874px;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-grid-header {
    align-items: end;
    padding: 9px 12px;
    background: #163a5f;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .02em;
    text-transform: uppercase;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-row {
    position: relative;
    align-items: start;
    padding: 11px 12px;
    border-top: 1px solid rgba(22,58,95,.12);
    background: #ffffff;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-row:first-child { border-top: 0; }
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-row:nth-child(even) { background: #f8fafc; }
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-row.dm-row-error {
    background: #fff7f7;
    box-shadow: inset 3px 0 0 #b94a48;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-cell { min-width: 0; }
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-email-wrap {
    display: grid;
    grid-template-columns: 130px 254px;
    align-items: center;
    gap: 6px;
    min-width: 0;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-source,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-email-wrap input.form-control.dm-cloudns-forward-source {
    width: 130px !important;
    min-width: 130px !important;
    max-width: 130px !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-zone {
    display: block;
    width: 254px;
    min-width: 254px;
    max-width: 254px;
    overflow: hidden;
    color: #516579;
    font-size: 12px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-cell input.form-control {
    width: 100% !important;
    min-width: 0 !important;
    height: 36px !important;
    min-height: 36px !important;
    padding: 7px 9px !important;
    border: 1px solid #cfd8e3 !important;
    border-radius: 6px !important;
    background: #ffffff !important;
    color: #1f2a35 !important;
    font-size: 13px !important;
    box-shadow: none !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-cell input.form-control:focus {
    border-color: #f58220 !important;
    box-shadow: 0 0 0 2px rgba(245,130,32,.18) !important;
    outline: none !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-row-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-row-error {
    grid-column: 1 / -1;
    display: none;
    color: #9f3735;
    font-size: 12px;
    font-weight: 700;
}
#dm-cloudns-forward-add-modal-1050 .dm-row-error .dm-cloudns-forward-row-error { display: block; }
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin: 12px 0 0;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-toolbar,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin: 0;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-toolbar { flex: 1 1 auto; }
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-actions { flex: 0 0 auto; }
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-secondary,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-duplicate,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-remove,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-cancel,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-submit,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-fullpage {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 36px !important;
    padding: 7px 11px !important;
    border-radius: 6px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    text-decoration: none !important;
    box-shadow: none !important;
    cursor: pointer;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-secondary,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-duplicate,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-cancel,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-fullpage {
    border: 1px solid #163a5f !important;
    background: #163a5f !important;
    color: #ffffff !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-secondary:hover,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-secondary:focus,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-duplicate:hover,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-duplicate:focus,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-cancel:hover,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-cancel:focus,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-fullpage:hover,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-fullpage:focus {
    border-color: #214e7a !important;
    background: #214e7a !important;
    color: #ffffff !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-remove {
    border: 1px solid #b94a48 !important;
    background: #b94a48 !important;
    color: #ffffff !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-remove:hover,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-remove:focus {
    border-color: #9f3735 !important;
    background: #9f3735 !important;
    color: #ffffff !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-submit {
    min-width: 112px !important;
    min-height: 38px !important;
    padding: 8px 15px !important;
    border: 1px solid #f58220 !important;
    background: #f58220 !important;
    color: #ffffff !important;
    font-size: 13px !important;
}
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-submit:hover,
#dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-submit:focus {
    border-color: #d8741f !important;
    background: #d8741f !important;
    color: #ffffff !important;
}
#dm-cloudns-forward-add-modal-1050 button[disabled] {
    opacity: .68 !important;
    cursor: not-allowed !important;
}
@media (max-width: 820px) {
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-table-wrap {
        overflow: visible;
        border: 0;
    }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-email-wrap {
        grid-template-columns: minmax(12ch, 18ch) minmax(0, 1fr);
    }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-source,
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-email-wrap input.form-control.dm-cloudns-forward-source {
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
    }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-zone {
        width: auto;
        min-width: 0;
        max-width: 100%;
    }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-grid-header { display: none; }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-grid {
        grid-template-columns: 1fr 1fr;
        min-width: 0;
    }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-row {
        margin-bottom: 12px;
        border: 1px solid rgba(22,58,95,.18);
        border-radius: 7px;
    }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-row-actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
    }
}
@media (max-width: 640px) {
    #dm-cloudns-forward-add-modal-1050 {
        align-items: flex-start;
        padding: 5vh 16px 24px;
    }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-body { padding: 14px; }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-grid { grid-template-columns: 1fr; }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-row-actions { grid-column: auto; }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-footer {
        align-items: stretch;
        flex-direction: column;
    }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-toolbar,
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-actions { width: 100%; }
    #dm-cloudns-forward-add-modal-1050 .dm-cloudns-forward-actions button { flex: 1 1 0; }
}
</style>
<script id="dm-cloudns-forward-add-modal-1050-script">
(function () {
    'use strict';

    if (window.dmCloudnsForwardAddModal1050Loaded) {
        return;
    }
    window.dmCloudnsForwardAddModal1050Loaded = true;

    var modal = null;
    var lastForwardAddUrl = '';
    var currentBaseForm = null;
    var currentZone = '';
    var submitting = false;

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

    function absoluteUrl(url) {
        try {
            return new URL(url || window.location.href, window.location.href).toString();
        } catch (error) {
            return url || window.location.href;
        }
    }

    function isForwardAddUrl(url) {
        return /clientarea\.php/i.test(String(url || ''))
            && /action=productdetails/i.test(String(url || ''))
            && /customAction=add-new-forwarding/i.test(String(url || ''));
    }

    function isInsideModal(node) {
        return !!(modal && node && node.closest && node.closest('#dm-cloudns-forward-add-modal-1050'));
    }

    function parseHtml(html) {
        return new DOMParser().parseFromString(html, 'text/html');
    }

    function findForwardForm(doc) {
        if (!doc || !doc.querySelector) {
            return null;
        }
        return doc.querySelector('form#recordsForm[action*="customAction=add-forward"], form.recordsForm[action*="customAction=add-forward"]');
    }

    function zoneFromUrl(url) {
        try {
            return new URL(url, window.location.href).searchParams.get('zone') || '';
        } catch (error) {
            return '';
        }
    }

    function ensureModal() {
        if (modal) {
            return modal;
        }
        modal = document.createElement('div');
        modal.id = 'dm-cloudns-forward-add-modal-1050';
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = ''
            + '<div class="dm-cloudns-forward-card" role="dialog" aria-modal="true" aria-labelledby="dm-cloudns-forward-title-1050">'
            + '  <div class="dm-cloudns-forward-head">'
            + '    <div><strong id="dm-cloudns-forward-title-1050">Add Mail Forwards</strong><span id="dm-cloudns-forward-subtitle-1050">Loading mail forward form…</span></div>'
            + '    <button type="button" class="dm-cloudns-forward-close" aria-label="Close">×</button>'
            + '  </div>'
            + '  <div class="dm-cloudns-forward-body"></div>'
            + '</div>';
        document.body.appendChild(modal);

        modal.addEventListener('click', function (event) {
            var target = event.target;
            var closeControl = target && target.closest
                ? target.closest('.dm-cloudns-forward-close, .dm-cloudns-forward-cancel')
                : null;

            // Keep the modal open when the pointer is released on the backdrop.
            // It may close only from Cancel, the X button, or a successful save.
            if (closeControl) {
                event.preventDefault();
                if (!submitting) {
                    closeModal();
                }
            }
        });

        modal.addEventListener('submit', function (event) {
            if (!event.target || !event.target.matches('.dm-cloudns-forward-bulk-form')) {
                return;
            }
            event.preventDefault();
            submitBulkForwards(event.target);
        }, true);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal && modal.classList.contains('dm-open')) {
                // This modal intentionally stays open until Cancel, successful
                // Save, or the X button is used.
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);

        return modal;
    }

    function setTitle(title, subtitle) {
        ensureModal();
        modal.querySelector('#dm-cloudns-forward-title-1050').textContent = title || 'Add Mail Forwards';
        modal.querySelector('#dm-cloudns-forward-subtitle-1050').textContent = subtitle || 'Add one or more mail forwards';
    }

    function setBody(html) {
        ensureModal();
        modal.querySelector('.dm-cloudns-forward-body').innerHTML = html;
    }

    function openModal() {
        ensureModal();
        modal.classList.add('dm-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('dm-cloudns-forward-add-modal-open');
        window.setTimeout(function () {
            var first = modal.querySelector('input:not([type="hidden"]), button');
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
        document.body.classList.remove('dm-cloudns-forward-add-modal-open');
    }

    function showLoading(message) {
        setBody('<div class="dm-cloudns-forward-loading"><span class="dm-cloudns-forward-spinner" aria-hidden="true"></span><span>' + escapeHtml(message || 'Loading mail forward form…') + '</span></div>');
    }

    function showMessage(message, fullPageUrl) {
        var actions = '<div class="dm-cloudns-forward-footer"><div></div><div class="dm-cloudns-forward-actions"><button type="button" class="dm-cloudns-forward-cancel">Close</button>';
        if (fullPageUrl) {
            actions += '<a class="dm-cloudns-forward-fullpage" href="' + escapeHtml(fullPageUrl) + '">Open Full Page</a>';
        }
        actions += '</div></div>';
        setBody('<div class="dm-cloudns-forward-message">' + escapeHtml(message) + '</div>' + actions);
    }

    function fetchPage(url) {
        return fetch(absoluteUrl(url), {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Could not load the mail forward form.');
            }
            return response.text().then(function (html) {
                return { html: html, url: response.url || absoluteUrl(url) };
            });
        });
    }

    function createRow(data) {
        data = data || {};
        var row = document.createElement('div');
        row.className = 'dm-cloudns-forward-row dm-cloudns-forward-grid';
        row.innerHTML = ''
            + '<div class="dm-cloudns-forward-cell" data-label="Email"><div class="dm-cloudns-forward-email-wrap"><input type="text" class="form-control dm-cloudns-forward-source" placeholder="name or *" autocapitalize="off" spellcheck="false" value="' + escapeHtml(data.source || '') + '"><span class="dm-cloudns-forward-zone" title="@' + escapeHtml(currentZone) + '">@' + escapeHtml(currentZone) + '</span></div></div>'
            + '<div class="dm-cloudns-forward-cell" data-label="Points To"><input type="email" class="form-control dm-cloudns-forward-destination" placeholder="destination@example.com" autocapitalize="off" spellcheck="false" value="' + escapeHtml(data.destination || '') + '"></div>'
            + '<div class="dm-cloudns-forward-cell dm-cloudns-forward-row-actions" data-label="Actions"><button type="button" class="dm-cloudns-forward-duplicate">Duplicate</button><button type="button" class="dm-cloudns-forward-remove">Remove</button></div>'
            + '<div class="dm-cloudns-forward-row-error" role="alert"></div>';
        return row;
    }

    function rowData(row) {
        return {
            source: normalizeText((row.querySelector('.dm-cloudns-forward-source') || {}).value || ''),
            destination: normalizeText((row.querySelector('.dm-cloudns-forward-destination') || {}).value || '')
        };
    }

    function validateRow(row) {
        var data = rowData(row);
        var message = '';
        if (!data.source) {
            message = 'Enter the email name, or * for a catch-all forward.';
        } else if (!data.destination) {
            message = 'Enter the destination email address.';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.destination)) {
            message = 'Enter a valid destination email address.';
        }
        setRowError(row, message);
        return message === '';
    }

    function setRowError(row, message) {
        var box = row.querySelector('.dm-cloudns-forward-row-error');
        row.classList.toggle('dm-row-error', !!message);
        if (box) {
            box.textContent = message || '';
        }
    }

    function updateSubmitLabel(form) {
        var count = form.querySelectorAll('.dm-cloudns-forward-row').length;
        var button = form.querySelector('.dm-cloudns-forward-submit');
        if (!button || submitting) {
            return;
        }
        button.textContent = count === 1 ? 'Add Forward' : 'Add ' + count + ' Forwards';
    }

    function buildBulkForm() {
        var form = document.createElement('form');
        form.className = 'dm-cloudns-forward-bulk-form';
        form.innerHTML = ''
            + '<div class="dm-cloudns-forward-error" role="alert" hidden></div>'
            + '<div class="dm-cloudns-forward-progress" role="status" aria-live="polite" hidden><span class="dm-cloudns-forward-spinner" aria-hidden="true"></span><span class="dm-cloudns-forward-progress-text">Preparing forwards…</span></div>'
            + '<div class="dm-cloudns-forward-table-wrap">'
            + '  <div class="dm-cloudns-forward-grid dm-cloudns-forward-grid-header" aria-hidden="true"><div>Email</div><div>Points To</div><div>Actions</div></div>'
            + '  <div class="dm-cloudns-forward-rows"></div>'
            + '</div>'
            + '<div class="dm-cloudns-forward-footer">'
            + '  <div class="dm-cloudns-forward-toolbar"><button type="button" class="dm-cloudns-forward-secondary dm-cloudns-forward-add-another">+ Add Another Forward</button><button type="button" class="dm-cloudns-forward-secondary dm-cloudns-forward-help-toggle">Help</button></div>'
            + '  <div class="dm-cloudns-forward-actions"><button type="button" class="dm-cloudns-forward-cancel">Cancel</button><button type="submit" class="dm-cloudns-forward-submit">Add Forward</button></div>'
            + '</div>'
            + '<div class="dm-cloudns-forward-help" hidden>Enter only the part before @' + escapeHtml(currentZone) + ' in Email. Use * to create a catch-all forward. Add up to 50 forwards at once. Successful rows are not repeated if another row fails.</div>';

        var rows = form.querySelector('.dm-cloudns-forward-rows');
        rows.appendChild(createRow());

        form.addEventListener('click', function (event) {
            var target = event.target;
            if (target.closest('.dm-cloudns-forward-add-another')) {
                if (rows.querySelectorAll('.dm-cloudns-forward-row').length < 50) {
                    rows.appendChild(createRow());
                    updateSubmitLabel(form);
                }
            } else if (target.closest('.dm-cloudns-forward-duplicate')) {
                var sourceRow = target.closest('.dm-cloudns-forward-row');
                if (sourceRow && rows.querySelectorAll('.dm-cloudns-forward-row').length < 50) {
                    sourceRow.insertAdjacentElement('afterend', createRow(rowData(sourceRow)));
                    updateSubmitLabel(form);
                }
            } else if (target.closest('.dm-cloudns-forward-remove')) {
                var removeRow = target.closest('.dm-cloudns-forward-row');
                if (removeRow && rows.querySelectorAll('.dm-cloudns-forward-row').length > 1) {
                    removeRow.remove();
                    updateSubmitLabel(form);
                }
            } else if (target.closest('.dm-cloudns-forward-help-toggle')) {
                var help = form.querySelector('.dm-cloudns-forward-help');
                help.hidden = !help.hidden;
            }
        });

        return form;
    }

    function renderBulkPage(result) {
        var doc = parseHtml(result.html);
        var baseForm = findForwardForm(doc);
        if (!baseForm) {
            showMessage('The DNSPlus mail forward page did not return an add form. The original page is still available.', lastForwardAddUrl);
            return;
        }
        currentBaseForm = baseForm.cloneNode(true);
        currentZone = zoneFromUrl(result.url || lastForwardAddUrl);
        setTitle('Add Mail Forwards', currentZone ? 'Add mail forwards for ' + currentZone : 'Add one or more mail forwards');
        var body = modal.querySelector('.dm-cloudns-forward-body');
        body.innerHTML = '';
        body.appendChild(buildBulkForm());
    }

    function responseSuccessful(result) {
        var doc = parseHtml(result.html);
        if (/customAction=mail-forwarding/i.test(result.url || '') && !/customAction=add-forward/i.test(result.url || '')) {
            return true;
        }
        return !!doc.querySelector('#table-forwards');
    }

    function responseErrorText(result) {
        var doc = parseHtml(result.html);
        var nodes = doc.querySelectorAll('.notification, .cloudns-response, .cloudns-response-error, .alert-danger, .alert-warning');
        for (var i = 0; i < nodes.length; i++) {
            var text = normalizeText(nodes[i].textContent || '');
            if (text && !/enter \* to create a catch-all/i.test(text)) {
                return text;
            }
        }
        if (findForwardForm(doc)) {
            return 'The mail forward was not accepted. Check the values and try again.';
        }
        return 'The mail forward could not be added.';
    }

    function payloadForRow(row) {
        var data = new FormData(currentBaseForm);
        var values = rowData(row);
        data.set('addEmailForward', values.source);
        data.set('addEmailForwardTo', values.destination);
        if (!data.has('do_save')) {
            data.append('do_save', 'Save');
        }
        return data;
    }

    function submitRow(row) {
        var action = absoluteUrl(currentBaseForm.getAttribute('action') || lastForwardAddUrl);
        return fetch(action, {
            method: 'POST',
            credentials: 'same-origin',
            redirect: 'follow',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: payloadForRow(row)
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text().then(function (html) {
                return { html: html, url: response.url || action };
            });
        }).then(function (result) {
            if (responseSuccessful(result)) {
                return { ok: true, row: row };
            }
            return { ok: false, row: row, message: responseErrorText(result) };
        }).catch(function () {
            return { ok: false, row: row, message: 'The request could not be completed. Check the connection and try again.' };
        });
    }

    function submitBulkForwards(form) {
        if (submitting || !currentBaseForm) {
            return;
        }
        var rows = Array.prototype.slice.call(form.querySelectorAll('.dm-cloudns-forward-row'));
        var valid = true;
        rows.forEach(function (row) {
            if (!validateRow(row)) {
                valid = false;
            }
        });
        if (!valid) {
            return;
        }

        submitting = true;
        var errorBox = form.querySelector('.dm-cloudns-forward-error');
        var progress = form.querySelector('.dm-cloudns-forward-progress');
        var progressText = form.querySelector('.dm-cloudns-forward-progress-text');
        var submit = form.querySelector('.dm-cloudns-forward-submit');
        var controls = form.querySelectorAll('button, input');
        var total = rows.length;
        var completed = 0;
        var added = 0;
        var failed = [];

        errorBox.hidden = true;
        errorBox.textContent = '';
        progress.hidden = false;
        Array.prototype.forEach.call(controls, function (control) { control.disabled = true; });
        submit.textContent = total === 1 ? 'Adding Forward…' : 'Adding ' + total + ' Forwards…';

        function next(index) {
            if (index >= rows.length) {
                if (failed.length === 0) {
                    progressText.textContent = 'Added ' + added + ' of ' + total + ' forwards. Refreshing Mail Forwards…';
                    window.setTimeout(function () { window.location.reload(); }, 350);
                    return;
                }

                failed.forEach(function (failure) {
                    setRowError(failure.row, failure.message);
                });
                rows.forEach(function (row) {
                    if (failed.every(function (failure) { return failure.row !== row; })) {
                        row.remove();
                    }
                });
                if (!form.querySelector('.dm-cloudns-forward-row')) {
                    form.querySelector('.dm-cloudns-forward-rows').appendChild(createRow());
                }

                submitting = false;
                progress.hidden = true;
                errorBox.hidden = false;
                errorBox.textContent = 'Added ' + added + ' forward' + (added === 1 ? '' : 's') + '. ' + failed.length + ' failed and remain below for correction.';
                Array.prototype.forEach.call(form.querySelectorAll('button, input'), function (control) { control.disabled = false; });
                updateSubmitLabel(form);
                return;
            }

            progressText.textContent = 'Adding forward ' + (index + 1) + ' of ' + total + ' · ' + completed + ' completed';
            submitRow(rows[index]).then(function (result) {
                completed += 1;
                if (result.ok) {
                    added += 1;
                } else {
                    failed.push(result);
                }
                next(index + 1);
            });
        }

        next(0);
    }

    function openBulkModal(link) {
        if (!window.fetch || !window.FormData || !window.DOMParser) {
            return false;
        }
        lastForwardAddUrl = absoluteUrl(link.href || link.getAttribute('href') || '');
        currentZone = zoneFromUrl(lastForwardAddUrl);
        ensureModal();
        setTitle('Add Mail Forwards', currentZone ? 'Add mail forwards for ' + currentZone : 'Loading mail forward form…');
        showLoading('Loading mail forward form…');
        openModal();
        fetchPage(lastForwardAddUrl)
            .then(renderBulkPage)
            .catch(function () {
                showMessage('The popup could not load the DNSPlus mail forward form.', lastForwardAddUrl);
            });
        return true;
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        var link = event.target && event.target.closest ? event.target.closest('a[href*="customAction=add-new-forwarding"]') : null;
        if (!link || isInsideModal(link) || !isForwardAddUrl(link.getAttribute('href') || link.href || '')) {
            return;
        }
        if (openBulkModal(link)) {
            event.preventDefault();
            event.stopPropagation();
            if (event.stopImmediatePropagation) {
                event.stopImmediatePropagation();
            }
        }
    }, true);
}());
</script>
HTML;
});
