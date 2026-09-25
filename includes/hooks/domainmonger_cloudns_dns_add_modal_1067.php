<?php
/**
 * DomainMonger ClouDNS DNS Add Modal 1067 / Bulk Add 1522 / Register DNS Style 1523
 *
 * Adds a DomainMonger-styled expandable bulk-add modal for the ClouDNS DNS Records +Add action while preserving the proven native single-record route.
 * Scope is intentionally limited to the ClouDNS DNS Records add flow:
 *   clientarea.php?action=productdetails&customAction=add-new-record
 *   clientarea.php?action=productdetails&customAction=add-record
 *
 * It preserves the existing ClouDNS backend form/action, keeps the old 1051 hook neutralized, and does not open/create an overlay during normal page load.
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
<style id="dm-cloudns-add-modal-1067-style">
.dm-cloudns-add-modal-open { overflow: hidden; }
#dm-cloudns-add-modal-1067 {
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
#dm-cloudns-add-modal-1067.dm-open { display: flex; }
#dm-cloudns-add-modal-1067 .dm-cloudns-add-card {
    width: min(760px, calc(100vw - 36px));
    max-height: calc(100vh - 80px);
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, .34);
    overflow: hidden;
    color: #1f2a35;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 18px;
    background: #163a5f;
    color: #ffffff;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-head strong {
    display: block;
    margin: 0;
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-head span {
    display: block;
    margin-top: 3px;
    color: rgba(255, 255, 255, .82);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .02em;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-close {
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
#dm-cloudns-add-modal-1067 .dm-cloudns-add-close:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-close:focus {
    background: #f58220;
    color: #ffffff;
    transform: scale(1.04);
    outline: none;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-body {
    padding: 20px 24px 22px;
    overflow: auto;
    max-height: calc(100vh - 165px);
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-loading,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-message {
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
#dm-cloudns-add-modal-1067 .dm-cloudns-add-spinner {
    width: 22px;
    height: 22px;
    border: 3px solid rgba(245, 130, 32, .25);
    border-top-color: #f58220;
    border-radius: 50%;
    animation: dmCloudnsAddSpin1067 .8s linear infinite;
}
@keyframes dmCloudnsAddSpin1067 { to { transform: rotate(360deg); } }
#dm-cloudns-add-modal-1067 form#recordsForm,
#dm-cloudns-add-modal-1067 form.recordsForm,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-form {
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
#dm-cloudns-add-modal-1067 .dm-cloudns-add-alerts {
    display: grid;
    gap: 10px;
    margin: 0 0 16px;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-alerts .notification,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-alerts .cloudns-response,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-alerts .alert {
    margin: 0 !important;
    border-radius: 8px !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-record-type-pill {
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
#dm-cloudns-add-modal-1067 form#recordsForm br,
#dm-cloudns-add-modal-1067 form.recordsForm br {
    line-height: 1.1;
}
#dm-cloudns-add-modal-1067 form#recordsForm div,
#dm-cloudns-add-modal-1067 form.recordsForm div,
#dm-cloudns-add-modal-1067 form#recordsForm t,
#dm-cloudns-add-modal-1067 form.recordsForm t {
    color: #263646;
    font-size: 14px;
    font-weight: 700;
}
#dm-cloudns-add-modal-1067 form#recordsForm small,
#dm-cloudns-add-modal-1067 form.recordsForm small,
#dm-cloudns-add-modal-1067 form#recordsForm .small,
#dm-cloudns-add-modal-1067 form.recordsForm .small {
    color: #657586 !important;
    font-size: 12px !important;
    line-height: 1.35 !important;
}
#dm-cloudns-add-modal-1067 form#recordsForm input[type="text"],
#dm-cloudns-add-modal-1067 form#recordsForm input[type="number"],
#dm-cloudns-add-modal-1067 form#recordsForm input[type="email"],
#dm-cloudns-add-modal-1067 form#recordsForm select,
#dm-cloudns-add-modal-1067 form#recordsForm textarea,
#dm-cloudns-add-modal-1067 form#recordsForm .form-control,
#dm-cloudns-add-modal-1067 form.recordsForm input[type="text"],
#dm-cloudns-add-modal-1067 form.recordsForm input[type="number"],
#dm-cloudns-add-modal-1067 form.recordsForm input[type="email"],
#dm-cloudns-add-modal-1067 form.recordsForm select,
#dm-cloudns-add-modal-1067 form.recordsForm textarea,
#dm-cloudns-add-modal-1067 form.recordsForm .form-control {
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
#dm-cloudns-add-modal-1067 form#recordsForm textarea,
#dm-cloudns-add-modal-1067 form.recordsForm textarea {
    min-height: 110px !important;
    resize: vertical !important;
}
#dm-cloudns-add-modal-1067 form#recordsForm input:focus,
#dm-cloudns-add-modal-1067 form#recordsForm select:focus,
#dm-cloudns-add-modal-1067 form#recordsForm textarea:focus,
#dm-cloudns-add-modal-1067 form.recordsForm input:focus,
#dm-cloudns-add-modal-1067 form.recordsForm select:focus,
#dm-cloudns-add-modal-1067 form.recordsForm textarea:focus {
    border-color: #f58220 !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .18) !important;
    background: #ffffff !important;
}
#dm-cloudns-add-modal-1067 form#recordsForm .spanHost,
#dm-cloudns-add-modal-1067 form.recordsForm .spanHost {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    width: 100% !important;
    margin: 4px 0 0 !important;
    color: #263646 !important;
    font-weight: 700 !important;
    word-break: break-word !important;
}
#dm-cloudns-add-modal-1067 form#recordsForm #addRecordHost,
#dm-cloudns-add-modal-1067 form.recordsForm #addRecordHost,
#dm-cloudns-add-modal-1067 form#recordsForm #editRecordHost,
#dm-cloudns-add-modal-1067 form.recordsForm #editRecordHost {
    flex: 1 1 220px !important;
    width: auto !important;
    min-width: 160px !important;
}
#dm-cloudns-add-modal-1067 form#recordsForm .pull-left,
#dm-cloudns-add-modal-1067 form#recordsForm .pull-right,
#dm-cloudns-add-modal-1067 form.recordsForm .pull-left,
#dm-cloudns-add-modal-1067 form.recordsForm .pull-right {
    float: none !important;
}
#dm-cloudns-add-modal-1067 form#recordsForm .inputSRV,
#dm-cloudns-add-modal-1067 form#recordsForm .inputTitle,
#dm-cloudns-add-modal-1067 form#recordsForm .selectTLSA,
#dm-cloudns-add-modal-1067 form#recordsForm .selectDS,
#dm-cloudns-add-modal-1067 form#recordsForm .selectCERT,
#dm-cloudns-add-modal-1067 form#recordsForm .selectLOC,
#dm-cloudns-add-modal-1067 form#recordsForm .inputLOC,
#dm-cloudns-add-modal-1067 form#recordsForm .inputFirstLOC,
#dm-cloudns-add-modal-1067 form.recordsForm .inputSRV,
#dm-cloudns-add-modal-1067 form.recordsForm .inputTitle,
#dm-cloudns-add-modal-1067 form.recordsForm .selectTLSA,
#dm-cloudns-add-modal-1067 form.recordsForm .selectDS,
#dm-cloudns-add-modal-1067 form.recordsForm .selectCERT,
#dm-cloudns-add-modal-1067 form.recordsForm .selectLOC,
#dm-cloudns-add-modal-1067 form.recordsForm .inputLOC,
#dm-cloudns-add-modal-1067 form.recordsForm .inputFirstLOC {
    width: 100% !important;
    max-width: 100% !important;
    margin: 6px 0 10px !important;
    box-sizing: border-box !important;
}
#dm-cloudns-add-modal-1067 form#recordsForm h5,
#dm-cloudns-add-modal-1067 form.recordsForm h5 {
    margin: 14px 0 8px !important;
    color: #163a5f !important;
    font-weight: 800 !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #edf0f4;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-submit,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-cancel,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-fullpage {
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
#dm-cloudns-add-modal-1067 .dm-cloudns-add-submit {
    background: #f58220 !important;
    color: #ffffff !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-submit:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-submit:focus {
    background: #d8741f !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(245, 130, 32, .28) !important;
    transform: translateY(-1px);
    outline: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-cancel,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-fullpage {
    background: #163a5f !important;
    color: #ffffff !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-cancel:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-cancel:focus,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-fullpage:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-fullpage:focus {
    background: #214e7a !important;
    color: #ffffff !important;
    outline: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-submit[disabled] {
    opacity: .72 !important;
    cursor: wait !important;
    transform: none !important;
}
@media (max-width: 640px) {
    #dm-cloudns-add-modal-1067 { padding-top: 5vh; }
    #dm-cloudns-add-modal-1067 .dm-cloudns-add-head { padding: 14px 15px; }
    #dm-cloudns-add-modal-1067 .dm-cloudns-add-body { padding: 16px; }
    #dm-cloudns-add-modal-1067 .dm-cloudns-add-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }
    #dm-cloudns-add-modal-1067 .dm-cloudns-add-submit,
    #dm-cloudns-add-modal-1067 .dm-cloudns-add-cancel,
    #dm-cloudns-add-modal-1067 .dm-cloudns-add-fullpage {
        width: 100% !important;
    }
}


/* DomainMonger Patch 1522: expandable DNSPlus bulk add inside the live 1067 popup. */
/* Patch 1524: align the DNSPlus bulk footer with Register DNS and remove stale empty notices. */
#dm-cloudns-add-modal-1067 .dm-cloudns-add-card {
    width: min(1180px, calc(100vw - 30px));
    max-width: 1180px;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-body {
    overflow-x: hidden;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-form {
    margin: 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-toolbar,
#dm-cloudns-add-modal-1067 .dm-cloudns-advanced-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin: 0 0 12px;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-help {
    margin: 0 0 12px;
    padding: 10px 12px;
    border: 1px solid #d7e0e8;
    border-radius: 6px;
    background: #f7f9fb;
    color: #536577;
    font-size: 13px;
    line-height: 1.45;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-error {
    margin: 0 0 12px;
    padding: 10px 12px;
    border: 1px solid #ebccd1;
    border-radius: 6px;
    background: #f2dede;
    color: #a94442;
    font-size: 13px;
    line-height: 1.45;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-progress {
    display: flex;
    align-items: center;
    gap: 9px;
    min-height: 42px;
    margin: 0 0 12px;
    padding: 9px 12px;
    border: 1px solid #c7d9e8;
    border-radius: 6px;
    background: #eef5fb;
    color: #163a5f;
    font-size: 13px;
    font-weight: 700;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-progress[hidden],
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-help[hidden],
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-error[hidden] {
    display: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-grid {
    display: grid;
    grid-template-columns: minmax(100px, .75fr) minmax(150px, 1.1fr) minmax(220px, 2fr) minmax(160px, 1.35fr) minmax(105px, .8fr) minmax(160px, auto);
    gap: 8px;
    align-items: start;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-grid-header {
    padding: 0 4px 7px;
    color: #43576a;
    font-size: 12px;
    font-weight: 700;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row {
    margin-bottom: 8px;
    padding: 9px;
    border: 1px solid #dbe2e9;
    border-radius: 6px;
    background: #ffffff;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row.dm-row-success {
    border-color: #b9ddc5;
    background: #eef8f1;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row.dm-row-error {
    border-color: #e2b8b7;
    background: #fff6f6;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-cell {
    min-width: 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-cell .form-control,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-cell input,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-cell select {
    width: 100% !important;
    height: 36px !important;
    min-height: 36px !important;
    margin: 0 !important;
    padding: 6px 8px !important;
    border: 1px solid #cfd8e3 !important;
    border-radius: 5px !important;
    background: #fff !important;
    color: #263b50 !important;
    box-sizing: border-box !important;
    font-size: 13px !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-details-inner {
    display: flex;
    align-items: center;
    gap: 5px;
    min-height: 36px;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-details-inner .form-control {
    min-width: 0;
    flex: 1 1 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-detail-none {
    display: flex;
    align-items: center;
    min-height: 36px;
    color: #7a8794;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-secondary,
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-view-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 6px 11px;
    border: 1px solid #163a5f;
    border-radius: 5px;
    background: #163a5f;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-secondary:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-secondary:focus,
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row:focus,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-view-button:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-view-button:focus {
    border-color: #214e7a;
    background: #214e7a;
    color: #fff;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 6px 11px;
    border: 1px solid #b94a48;
    border-radius: 5px;
    background: #b94a48;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row:focus {
    border-color: #a33d3b;
    background: #a33d3b;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-advanced-help {
    margin: 0 0 12px;
    border: 1px solid #d7e0e8;
    border-radius: 6px;
    background: #f7f9fb;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-advanced-help summary {
    padding: 9px 12px;
    color: #163a5f;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-advanced-help .notification {
    margin: 0 !important;
    border: 0 !important;
    border-top: 1px solid #d7e0e8 !important;
    border-radius: 0 !important;
    background: #fff !important;
    color: #536577 !important;
    font-size: 12px !important;
    line-height: 1.45 !important;
}
#dm-cloudns-add-modal-1067.dm-bulk-busy .dm-cloudns-add-close {
    opacity: .45;
    cursor: wait;
}
@media (max-width: 900px) {
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-grid-header { display: none; }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-grid { grid-template-columns: 1fr 1fr; }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-cell::before {
        content: attr(data-label);
        display: block;
        margin: 0 0 4px;
        color: #536577;
        font-size: 11px;
        font-weight: 700;
    }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row-actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
    }
}
@media (max-width: 560px) {
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-grid { grid-template-columns: 1fr; }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row-actions { grid-column: auto; }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row-actions button { flex: 1 1 0; }
}

/* DomainMonger Patch 1523: make DNSPlus bulk add visually match Register DNS bulk add. */
#dm-cloudns-add-modal-1067 {
    align-items: center;
    padding: 22px;
    background: rgba(14, 32, 52, .62);
    backdrop-filter: none;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-card {
    width: 100%;
    max-width: 1080px;
    max-height: calc(100vh - 44px);
    border-radius: 8px;
    box-shadow: 0 18px 55px rgba(10, 31, 52, .28);
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-head {
    padding: 14px 16px;
    border-radius: 8px 8px 0 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-head strong {
    font-size: 16px;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-head span {
    display: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-close {
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
    transform: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-close:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-close:focus {
    background: transparent !important;
    color: #ffffff !important;
    transform: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-body {
    padding: 18px;
    max-height: calc(100vh - 102px);
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-table-wrap {
    border: 1px solid rgba(22, 58, 95, .18);
    border-radius: 7px;
    overflow-x: auto;
    background: #ffffff;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-grid {
    grid-template-columns: minmax(112px, .8fr) minmax(145px, 1fr) minmax(250px, 2fr) minmax(160px, 1.2fr) 92px 96px;
    gap: 10px;
    min-width: 1000px;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-grid-header {
    align-items: end;
    padding: 9px 12px;
    background: #163a5f;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .02em;
    text-transform: uppercase;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row {
    margin: 0;
    padding: 11px 12px;
    border: 0;
    border-top: 1px solid rgba(22, 58, 95, .12);
    border-radius: 0;
    background: #ffffff;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row:first-child {
    border-top: 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row:nth-child(even) {
    background: #f8fafc;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-footer-1524 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin: 12px 0 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-footer-1524 .dm-cloudns-bulk-toolbar {
    flex: 1 1 auto;
    justify-content: flex-start;
    margin: 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-footer-1524 .dm-cloudns-add-actions {
    flex: 0 0 auto;
    margin: 0;
    padding: 0;
    border: 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-another.dm-cloudns-add-submit,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-secondary,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-view-button,
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row,
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row {
    min-width: 0 !important;
    min-height: 36px !important;
    padding: 7px 10px !important;
    border-radius: 6px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    box-shadow: none !important;
    transform: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-another.dm-cloudns-add-submit,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-secondary,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-view-button,
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row {
    border: 1px solid #163a5f !important;
    background: #163a5f !important;
    color: #ffffff !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-another.dm-cloudns-add-submit:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-another.dm-cloudns-add-submit:focus,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-secondary:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-secondary:focus,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-view-button:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-view-button:focus,
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row:focus {
    border-color: #214e7a !important;
    background: #214e7a !important;
    color: #ffffff !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row {
    border: 1px solid #b94a48 !important;
    background: #b94a48 !important;
    color: #ffffff !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-help {
    margin: 12px 0 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-error:empty,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-progress:empty,
#dm-cloudns-add-modal-1067 .dm-cloudns-bulk-help:empty {
    display: none !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    min-height: 0 !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-actions {
    margin-top: 12px;
    padding-top: 0;
    border-top: 0;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-add-actions .dm-cloudns-add-submit,
#dm-cloudns-add-modal-1067 .dm-cloudns-add-actions .dm-cloudns-add-cancel {
    min-width: 108px !important;
    min-height: 38px !important;
    padding: 8px 15px !important;
    border-radius: 6px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    box-shadow: none !important;
    transform: none !important;
}
@media (max-width: 900px) {
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-table-wrap {
        border: 0;
        overflow: visible;
    }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-grid {
        min-width: 0;
    }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-row {
        margin-bottom: 12px;
        border: 1px solid rgba(22, 58, 95, .18);
        border-radius: 7px;
        padding: 12px;
    }
}
@media (max-width: 640px) {
    #dm-cloudns-add-modal-1067 {
        align-items: flex-start;
        padding: 5vh 16px 24px;
    }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-footer-1524 {
        align-items: stretch;
        flex-direction: column;
    }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-footer-1524 .dm-cloudns-bulk-toolbar,
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-footer-1524 .dm-cloudns-add-actions {
        width: 100%;
    }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-footer-1524 .dm-cloudns-add-actions {
        flex-direction: row;
    }
    #dm-cloudns-add-modal-1067 .dm-cloudns-bulk-footer-1524 .dm-cloudns-add-actions button {
        flex: 1 1 0;
        width: auto !important;
    }
}


#dm-cloudns-add-modal-1067 .dm-cloudns-wr-save-path {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    min-height: 38px !important;
    margin: 0 !important;
    padding: 0 6px !important;
    color: #263646 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    white-space: nowrap !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-wr-save-path input[type="checkbox"] {
    width: auto !important;
    min-height: 0 !important;
    margin: 0 !important;
}


/* DomainMonger Patch 1816: match Add Records row actions to the crisp table treatment. */
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row,
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row {
    width: 36px !important;
    min-width: 36px !important;
    max-width: 36px !important;
    height: 36px !important;
    min-height: 36px !important;
    padding: 0 !important;
    border-radius: 5px !important;
    background: #ffffff !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row {
    border: 1px solid #cfd8e3 !important;
    color: #163a5f !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row:focus {
    border-color: #163a5f !important;
    background: #f3f7fa !important;
    color: #214e7a !important;
    box-shadow: 0 0 0 2px rgba(22, 58, 95, .12) !important;
    outline: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row {
    border: 1px solid #cfd8e3 !important;
    color: #b94a48 !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row:hover,
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row:focus {
    border-color: #b94a48 !important;
    background: #fff1f1 !important;
    color: #a94442 !important;
    box-shadow: 0 0 0 2px rgba(185, 74, 72, .12) !important;
    outline: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row:disabled {
    opacity: 1 !important;
    cursor: not-allowed !important;
    background: #f7f8fa !important;
    border-color: #d9dee4 !important;
    color: #b94a48 !important;
    box-shadow: none !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row:disabled .dm-cloudns-row-action-svg {
    opacity: 1 !important;
    stroke: #b94a48 !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-row-action-svg {
    width: 15px;
    height: 15px;
    display: block;
    fill: none;
    stroke-width: 2.2;
    stroke-linecap: round;
    stroke-linejoin: round;
    pointer-events: none;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row .dm-cloudns-row-action-svg {
    stroke: #163a5f !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-remove-row .dm-cloudns-row-action-svg {
    stroke: #b94a48 !important;
}
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row:hover .dm-cloudns-row-action-svg,
#dm-cloudns-add-modal-1067 .dm-cloudns-duplicate-row:focus .dm-cloudns-row-action-svg {
    stroke: #214e7a !important;
}

</style>
<script id="dm-cloudns-add-modal-1067-script">
(function () {
    'use strict';

    if (window.dmCloudnsDnsAddModal1067Loaded) {
        return;
    }
    window.dmCloudnsDnsAddModal1067Loaded = true;

    var modal = null;
    var lastAddUrl = '';
    var lastZoneUrl = '';
    var currentBaseForm = null;
    var currentAlertsHtml = '';
    var currentSubtitle = '';
    var bulkBusy = false;
    var addPageCache = Object.create(null);
    var addPageInflight = Object.create(null);
    var supportedBulkTypes = ['A', 'AAAA', 'ALIAS', 'CNAME', 'DNAME', 'MX', 'NS', 'PTR', 'SPF', 'SRV', 'TXT', 'WR', 'CAA', 'SSHFP', 'TLSA', 'DS', 'OPENPGPKEY', 'SVCB', 'HTTPS'];

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

    function isClouDnsAddUrl(url) {
        return /clientarea\.php/i.test(String(url || ''))
            && /action=productdetails/i.test(String(url || ''))
            && /customAction=add-new-record/i.test(String(url || ''));
    }

    function isClouDnsDoAddUrl(url) {
        return /clientarea\.php/i.test(String(url || ''))
            && /action=productdetails/i.test(String(url || ''))
            && /customAction=add-record/i.test(String(url || ''));
    }

    function isInsideModal(node) {
        return !!(modal && node && node.closest && node.closest('#dm-cloudns-add-modal-1067'));
    }

    function ensureModal() {
        if (modal) {
            return modal;
        }
        modal = document.createElement('div');
        modal.id = 'dm-cloudns-add-modal-1067';
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = ''
            + '<div class="dm-cloudns-add-card" role="dialog" aria-modal="true" aria-labelledby="dm-cloudns-add-title-1067">'
            + '  <div class="dm-cloudns-add-head">'
            + '    <div><strong id="dm-cloudns-add-title-1067">Add DNS Records</strong><span id="dm-cloudns-add-subtitle-1067">Loading DNS record form…</span></div>'
            + '    <button type="button" class="dm-cloudns-add-close" aria-label="Close DNS record form">×</button>'
            + '  </div>'
            + '  <div class="dm-cloudns-add-body"></div>'
            + '</div>';
        document.body.appendChild(modal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal || (event.target.closest && event.target.closest('.dm-cloudns-add-close, .dm-cloudns-add-cancel'))) {
                event.preventDefault();
                closeModal();
            }
        });

        modal.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || !form.matches) {
                return;
            }
            if (form.matches('form.dm-cloudns-bulk-form')) {
                event.preventDefault();
                submitBulkForm(form);
                return;
            }
            if (form.matches('form.dm-cloudns-add-form')) {
                event.preventDefault();
                submitAddForm(form, event.submitter || document.activeElement || null);
            }
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
        var titleNode = modal.querySelector('#dm-cloudns-add-title-1067');
        var subtitleNode = modal.querySelector('#dm-cloudns-add-subtitle-1067');
        if (titleNode) {
            titleNode.textContent = title || 'Add DNS Record';
        }
        if (subtitleNode) {
            subtitleNode.textContent = subtitle || 'Add a DNS record without leaving this page';
        }
    }

    function setModalBody(html) {
        ensureModal();
        var body = modal.querySelector('.dm-cloudns-add-body');
        if (body) {
            body.innerHTML = html;
        }
    }

    function openModal() {
        ensureModal();
        modal.classList.add('dm-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('dm-cloudns-add-modal-open');
        window.setTimeout(function () {
            var first = modal.querySelector('input:not([type="hidden"]), textarea, select, button');
            if (first && first.focus) {
                first.focus();
            }
        }, 80);
    }

    function closeModal() {
        if (bulkBusy) {
            return;
        }
        if (!modal) {
            return;
        }
        modal.classList.remove('dm-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('dm-cloudns-add-modal-open');
    }

    function showLoading(message) {
        setModalBody('<div class="dm-cloudns-add-loading"><span class="dm-cloudns-add-spinner" aria-hidden="true"></span><span>' + escapeHtml(message || 'Loading DNS record form…') + '</span></div>');
    }

    function showMessage(message, isError, fullPageUrl) {
        var style = isError ? 'border-color: rgba(185,74,72,.35); color: #b94a48;' : '';
        var extra = '<div class="dm-cloudns-add-actions"><button type="button" class="dm-cloudns-add-cancel">Close</button>';
        if (fullPageUrl) {
            extra += '<a class="dm-cloudns-add-fullpage" href="' + escapeHtml(fullPageUrl) + '">Open Full Page</a>';
        }
        extra += '</div>';
        setModalBody('<div class="dm-cloudns-add-message" style="' + style + '">' + escapeHtml(message) + '</div>' + extra);
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
                throw new Error('Could not load the DNS record add form.');
            }
            return response.text().then(function (html) {
                return { html: html, url: response.url || absoluteUrl(url) };
            });
        });
    }

    function loadAddPage(url) {
        var key = absoluteUrl(url);
        if (addPageCache[key]) {
            return Promise.resolve(addPageCache[key]);
        }
        if (addPageInflight[key]) {
            return addPageInflight[key];
        }
        addPageInflight[key] = fetchUrl(key)
            .then(function (result) {
                addPageCache[key] = result;
                delete addPageInflight[key];
                return result;
            })
            .catch(function (error) {
                delete addPageInflight[key];
                throw error;
            });
        return addPageInflight[key];
    }

    function warmMainAddLink(link) {
        if (!link || !link.matches || !link.matches('a.cloudns-add-record-link[href*=\"customAction=add-new-record\"]')) {
            return;
        }
        var url = link.getAttribute('href') || link.href || '';
        if (!isClouDnsAddUrl(url)) {
            return;
        }
        loadAddPage(url).catch(function () {
            // Warming is opportunistic. A click will retry and preserve the native fallback.
        });
    }

    // DomainMonger Patch 1820: warm the native ClouDNS add-record form as soon
    // as the DNS Records page is ready. The fetch is asynchronous and does not
    // block the records table, but it lets the normal 5-7 second server render
    // happen in the background before the client clicks +Add Record.
    function warmInitialAddForm() {
        var link = document.querySelector('a.cloudns-add-record-link[href*=\"customAction=add-new-record\"]');
        if (!link) {
            return false;
        }
        warmMainAddLink(link);
        return true;
    }

    function scheduleInitialAddFormWarmup() {
        var attempts = 0;
        function tryWarm() {
            attempts++;
            if (warmInitialAddForm() || attempts >= 20) {
                return;
            }
            window.setTimeout(tryWarm, 150);
        }

        // Let the current DOM work finish first; fetch itself remains fully async.
        window.setTimeout(tryWarm, 0);
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
                throw new Error('Could not add the DNS record.');
            }
            return response.text().then(function (html) {
                return { html: html, url: response.url || action };
            });
        });
    }

    function parseHtml(html) {
        return new DOMParser().parseFromString(html, 'text/html');
    }

    function findAddForm(doc) {
        if (!doc || !doc.querySelector) {
            return null;
        }
        var exact = doc.querySelector('form#recordsForm[action*="customAction=add-record"]');
        if (exact) {
            return exact;
        }
        var forms = doc.querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) {
            if (isClouDnsDoAddUrl(forms[i].getAttribute('action') || '')) {
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
            if (nodes[i].classList && nodes[i].classList.contains('WR_fields')) {
                continue;
            }
            var text = normalizeText(nodes[i].textContent || '');
            if (!text || /records\s*:\s*\d+/i.test(text)) {
                continue;
            }
            alerts.push(nodes[i].cloneNode(true).outerHTML);
        }
        return alerts.join('');
    }

    function getZoneUrlFromAddUrl(url) {
        try {
            var parsed = new URL(url, window.location.href);
            parsed.searchParams.set('customAction', 'zone-settings');
            parsed.searchParams.delete('source_record');
            parsed.searchParams.delete('type');
            return parsed.toString();
        } catch (err) {
            return '';
        }
    }

    function getSubtitleFromLink(link) {
        var row = link && link.closest ? link.closest('tr') : null;
        if (!row) {
            return 'Add a DNS record without leaving this page';
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
        return pieces.length ? pieces.join(' · ') : 'Add a DNS record without leaving this page';
    }

    function getTypeFromForm(form) {
        var typeInput = form.querySelector('#addRecordType, [name="addRecordType"]');
        var type = typeInput && typeInput.value ? typeInput.value : '';
        return type ? type.toUpperCase() : 'DNS';
    }

    function ensureWebRedirectOption(form) {
        if (!form || !form.querySelector) {
            return;
        }
        var select = form.querySelector('#addRecordType, [name="addRecordType"]');
        if (!select) {
            return;
        }
        for (var i = 0; i < select.options.length; i++) {
            if (String(select.options[i].value || '').toUpperCase() === 'WR') {
                return;
            }
        }
        var option = document.createElement('option');
        option.value = 'WR';
        option.textContent = 'Web Redirect';
        select.appendChild(option);
    }

    function formatRecordType(type) {
        type = String(type || 'DNS').toUpperCase();
        return type === 'WR' ? 'WEB REDIRECT' : type;
    }

    function recordTypeClass(type) {
        return String(type || '').replace(/[^A-Z0-9_-]/gi, '');
    }

    function setNodesVisible(nodes, visible) {
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].style.display = visible ? '' : 'none';
        }
    }

    function syncRecordTypeFields(root) {
        if (!root || !root.querySelectorAll) {
            return;
        }
        var form = root.matches && root.matches('form') ? root : root.querySelector('form.dm-cloudns-add-form');
        if (!form) {
            return;
        }
        var type = getTypeFromForm(form);
        setNodesVisible(form.querySelectorAll('.type_fields'), false);
        setNodesVisible(form.querySelectorAll('.' + recordTypeClass(type) + '_fields'), true);

        var hideRecordContainer = /^(RP|NAPTR|CAA|HINFO|LOC)$/i.test(type);
        setNodesVisible(form.querySelectorAll('.recordContainter'), !hideRecordContainer);

        var hideRecordLabel = /^(OPENPGPKEY|SMIMEA)$/i.test(type);
        setNodesVisible(form.querySelectorAll('.recordContainter .records-label'), !hideRecordLabel);

        var pill = root.querySelector('.dm-cloudns-record-type-pill');
        if (pill) {
            pill.textContent = formatRecordType(type);
        }
        var advancedHelp = root.querySelector('.dm-cloudns-advanced-help');
        if (advancedHelp) {
            advancedHelp.style.display = type === 'WR' ? '' : 'none';
            if (type !== 'WR') {
                advancedHelp.removeAttribute('open');
            }
        }
    }

    function getFieldValue(form, selector, fallback) {
        var field = form && form.querySelector ? form.querySelector(selector) : null;
        return field && field.value != null ? String(field.value) : String(fallback == null ? '' : fallback);
    }

    function getInitialRowData(form) {
        var type = getTypeFromForm(form);
        var data = {
            type: type,
            host: getFieldValue(form, '#addRecordHost, [name="addRecordHost"]', ''),
            ttl: getFieldValue(form, '#addRecordTtl, [name="addRecordTtl"]', '3600'),
            value: getFieldValue(form, '#addRecordRecord, [name="addRecordRecord"]', ''),
            priority: '0',
            weight: '0',
            port: '0',
            flag: '0',
            caaType: 'issue',
            algorithm: '1',
            fingerprintType: '1',
            usage: '3',
            selector: '1',
            matchingType: '1',
            keyTag: '0',
            digestAlgorithm: '8',
            digestType: '2',
            parameters: '',
            wrType: '301',
            wrSavePath: '0'
        };
        if (type === 'MX') {
            data.priority = getFieldValue(form, '#addRecordMXPriority, [name="addRecordMXPriority"]', '10');
        } else if (type === 'SRV') {
            data.priority = getFieldValue(form, '#addRecordSRVPriority, [name="addRecordSRVPriority"]', '0');
            data.weight = getFieldValue(form, '#addRecordWeight, [name="addRecordWeight"]', '0');
            data.port = getFieldValue(form, '#addRecordPort, [name="addRecordPort"]', '0');
        } else if (type === 'CAA') {
            data.value = getFieldValue(form, '#addRecordCAAvalue, [name="addRecordCAAvalue"]', '');
            data.flag = getFieldValue(form, '#addRecordCAAflag, [name="addRecordCAAflag"]', '0');
            data.caaType = getFieldValue(form, '#addRecordCAAtype, [name="addRecordCAAtype"]', 'issue');
        } else if (type === 'SSHFP') {
            data.algorithm = getFieldValue(form, '#addRecordAlgorithm, [name="algorithm"]', '1');
            data.fingerprintType = getFieldValue(form, '#addRecordFingerprintType, [name="fp_type"]', '1');
        } else if (type === 'TLSA') {
            data.usage = getFieldValue(form, '#addRecordUsage, [name="addRecordUsage"]', '3');
            data.selector = getFieldValue(form, '#addRecordSelector, [name="addRecordSelector"]', '1');
            data.matchingType = getFieldValue(form, '#addRecordMatchingType, [name="addRecordMatchingType"]', '1');
        } else if (type === 'DS') {
            data.keyTag = getFieldValue(form, '#addRecordKeyTag, [name="addRecordKeyTag"]', '0');
            data.digestAlgorithm = getFieldValue(form, '#addRecordDsAlgorithm, [name="addRecordDsAlgorithm"]', '8');
            data.digestType = getFieldValue(form, '#addRecordDigestType, [name="addRecordDigestType"]', '2');
        } else if (type === 'SVCB' || type === 'HTTPS') {
            data.priority = getFieldValue(form, '#addRecordPriority, [name="addRecordPriority"]', '0');
            data.parameters = getFieldValue(form, '#addRecordParameters, [name="addRecordParameters"]', '');
        }
        if (supportedBulkTypes.indexOf(data.type) === -1) {
            data.type = supportedBulkTypes.indexOf('A') !== -1 ? 'A' : supportedBulkTypes[0];
            data.value = '';
        }
        return data;
    }

    function selectOptionsHtml(select, allowedValues, selectedValue) {
        var html = '';
        if (!select || !select.options) {
            return html;
        }
        for (var i = 0; i < select.options.length; i++) {
            var option = select.options[i];
            var value = String(option.value || '').toUpperCase();
            if (allowedValues && allowedValues.indexOf(value) === -1) {
                continue;
            }
            html += '<option value="' + escapeHtml(option.value) + '"' + (value === String(selectedValue || '').toUpperCase() ? ' selected' : '') + '>' + escapeHtml(normalizeText(option.textContent || option.value)) + '</option>';
        }
        return html;
    }

    function ttlOptionsHtml(form, selectedValue) {
        var select = form.querySelector('#addRecordTtl, [name="addRecordTtl"]');
        return selectOptionsHtml(select, null, selectedValue);
    }

    function typeOptionsHtml(form, selectedValue) {
        var select = form.querySelector('#addRecordType, [name="addRecordType"]');
        return selectOptionsHtml(select, supportedBulkTypes, selectedValue);
    }

    function valuePlaceholder(type) {
        type = String(type || '').toUpperCase();
        if (type === 'A') { return 'IPv4 address'; }
        if (type === 'AAAA') { return 'IPv6 address'; }
        if (/^(ALIAS|CNAME|DNAME|NS|PTR)$/.test(type)) { return 'Target hostname'; }
        if (type === 'MX') { return 'Mail server hostname'; }
        if (type === 'SRV') { return 'Target hostname'; }
        if (type === 'CAA') { return 'CA hostname, mailto, or URL'; }
        if (type === 'OPENPGPKEY') { return 'PGP public key'; }
        if (type === 'DS') { return 'Digest'; }
        if (type === 'SSHFP') { return 'Fingerprint'; }
        if (type === 'TLSA') { return 'Certificate association data'; }
        if (type === 'WR') { return 'Destination URL'; }
        return 'Record value';
    }

    function createBulkRow(form, data) {
        data = data || getInitialRowData(form);
        var row = document.createElement('div');
        row.className = 'dm-cloudns-bulk-row dm-cloudns-bulk-grid';
        row.innerHTML = ''
            + '<div class="dm-cloudns-bulk-cell" data-label="Type"><select class="form-control dm-cloudns-bulk-type" aria-label="Record type">' + typeOptionsHtml(form, data.type) + '</select></div>'
            + '<div class="dm-cloudns-bulk-cell" data-label="Host"><input type="text" class="form-control dm-cloudns-bulk-host" placeholder="@ or subdomain" autocapitalize="off" spellcheck="false" value="' + escapeHtml(data.host) + '"></div>'
            + '<div class="dm-cloudns-bulk-cell" data-label="Points To / Value"><input type="text" class="form-control dm-cloudns-bulk-value" autocapitalize="off" spellcheck="false" value="' + escapeHtml(data.value) + '"></div>'
            + '<div class="dm-cloudns-bulk-cell dm-cloudns-bulk-details" data-label="Details"><div class="dm-cloudns-bulk-details-inner"></div></div>'
            + '<div class="dm-cloudns-bulk-cell" data-label="TTL"><select class="form-control dm-cloudns-bulk-ttl" aria-label="TTL">' + ttlOptionsHtml(form, data.ttl) + '</select></div>'
            + '<div class="dm-cloudns-bulk-cell dm-cloudns-bulk-row-actions" data-label="Actions"><button type="button" class="dm-cloudns-duplicate-row" title="Duplicate Record" aria-label="Duplicate Record"><svg class="dm-cloudns-row-action-svg" viewBox="0 0 18 18" aria-hidden="true" focusable="false"><rect x="6.5" y="6.5" width="9" height="9" rx="1.25"></rect><path d="M4.5 12.5H3.75A1.25 1.25 0 0 1 2.5 11.25v-7.5A1.25 1.25 0 0 1 3.75 2.5h7.5A1.25 1.25 0 0 1 12.5 3.75v.75"></path></svg></button><button type="button" class="dm-cloudns-remove-row" title="Remove Record" aria-label="Remove Record"><svg class="dm-cloudns-row-action-svg" viewBox="0 0 18 18" aria-hidden="true" focusable="false"><path d="M3 5h12"></path><path d="M7 5V3h4v2"></path><path d="M5 5l1 10h6l1-10"></path><path d="M8 8v4"></path><path d="M10 8v4"></path></svg></button></div>';
        row._dmInitialDetails = data;
        updateBulkRowType(row, data);
        return row;
    }

    function updateBulkRowType(row, data) {
        if (!row) { return; }
        data = data || collectBulkRowData(row);
        var type = String((row.querySelector('.dm-cloudns-bulk-type') || {}).value || data.type || '').toUpperCase();
        var previousType = String(row.getAttribute('data-dm-record-type') || '').toUpperCase();
        if (previousType && previousType !== type) {
            data.priority = type === 'MX' ? '10' : '0';
            data.weight = '0';
            data.port = '0';
            data.flag = '0';
            data.caaType = 'issue';
            data.algorithm = '1';
            data.fingerprintType = '1';
            data.usage = '3';
            data.selector = '1';
            data.matchingType = '1';
            data.keyTag = '0';
            data.digestAlgorithm = '8';
            data.digestType = '2';
            data.parameters = '';
            data.wrType = '301';
            data.wrSavePath = '0';
        }
        row.setAttribute('data-dm-record-type', type);
        var valueInput = row.querySelector('.dm-cloudns-bulk-value');
        var details = row.querySelector('.dm-cloudns-bulk-details-inner');
        if (valueInput) {
            valueInput.placeholder = valuePlaceholder(type);
        }
        if (!details) { return; }
        if (type === 'MX') {
            details.innerHTML = '<input type="number" class="form-control dm-detail-priority" min="0" max="65535" title="Priority" aria-label="Priority" value="' + escapeHtml(data.priority || '10') + '">';
        } else if (type === 'SRV') {
            details.innerHTML = '<input type="number" class="form-control dm-detail-priority" min="0" max="65535" title="Priority" aria-label="Priority" value="' + escapeHtml(data.priority || '0') + '"><input type="number" class="form-control dm-detail-weight" min="0" max="65535" title="Weight" aria-label="Weight" value="' + escapeHtml(data.weight || '0') + '"><input type="number" class="form-control dm-detail-port" min="0" max="65535" title="Port" aria-label="Port" value="' + escapeHtml(data.port || '0') + '">';
        } else if (type === 'CAA') {
            details.innerHTML = '<select class="form-control dm-detail-flag" title="Flag" aria-label="CAA flag"><option value="0"' + (String(data.flag) === '0' ? ' selected' : '') + '>0</option><option value="128"' + (String(data.flag) === '128' ? ' selected' : '') + '>128</option></select><select class="form-control dm-detail-caa-type" title="Tag" aria-label="CAA tag"><option value="issue"' + (data.caaType === 'issue' ? ' selected' : '') + '>issue</option><option value="issuewild"' + (data.caaType === 'issuewild' ? ' selected' : '') + '>issuewild</option><option value="iodef"' + (data.caaType === 'iodef' ? ' selected' : '') + '>iodef</option><option value="issuemail"' + (data.caaType === 'issuemail' ? ' selected' : '') + '>issuemail</option><option value="issuevmc"' + (data.caaType === 'issuevmc' ? ' selected' : '') + '>issuevmc</option><option value="contactemail"' + (data.caaType === 'contactemail' ? ' selected' : '') + '>contactemail</option><option value="contactphone"' + (data.caaType === 'contactphone' ? ' selected' : '') + '>contactphone</option></select>';
        } else if (type === 'SSHFP') {
            details.innerHTML = '<select class="form-control dm-detail-algorithm" title="Algorithm" aria-label="SSHFP algorithm"><option value="1"' + (String(data.algorithm) === '1' ? ' selected' : '') + '>RSA</option><option value="2"' + (String(data.algorithm) === '2' ? ' selected' : '') + '>DSA</option><option value="3"' + (String(data.algorithm) === '3' ? ' selected' : '') + '>ECDSA</option><option value="4"' + (String(data.algorithm) === '4' ? ' selected' : '') + '>Ed25519</option></select><select class="form-control dm-detail-fingerprint" title="Fingerprint type" aria-label="Fingerprint type"><option value="1"' + (String(data.fingerprintType) === '1' ? ' selected' : '') + '>SHA-1</option><option value="2"' + (String(data.fingerprintType) === '2' ? ' selected' : '') + '>SHA-256</option></select>';
        } else if (type === 'TLSA') {
            details.innerHTML = '<select class="form-control dm-detail-usage" title="Usage" aria-label="TLSA usage"><option value="0"' + (String(data.usage) === '0' ? ' selected' : '') + '>U0</option><option value="1"' + (String(data.usage) === '1' ? ' selected' : '') + '>U1</option><option value="2"' + (String(data.usage) === '2' ? ' selected' : '') + '>U2</option><option value="3"' + (String(data.usage) === '3' ? ' selected' : '') + '>U3</option></select><select class="form-control dm-detail-selector" title="Selector" aria-label="TLSA selector"><option value="0"' + (String(data.selector) === '0' ? ' selected' : '') + '>S0</option><option value="1"' + (String(data.selector) === '1' ? ' selected' : '') + '>S1</option></select><select class="form-control dm-detail-matching" title="Matching" aria-label="TLSA matching type"><option value="0"' + (String(data.matchingType) === '0' ? ' selected' : '') + '>M0</option><option value="1"' + (String(data.matchingType) === '1' ? ' selected' : '') + '>M1</option><option value="2"' + (String(data.matchingType) === '2' ? ' selected' : '') + '>M2</option></select>';
        } else if (type === 'DS') {
            details.innerHTML = '<input type="number" class="form-control dm-detail-keytag" min="0" max="65535" title="Key tag" aria-label="DS key tag" value="' + escapeHtml(data.keyTag || '0') + '"><input type="number" class="form-control dm-detail-digest-algorithm" min="0" max="255" title="Algorithm" aria-label="DS algorithm" value="' + escapeHtml(data.digestAlgorithm || '8') + '"><select class="form-control dm-detail-digest-type" title="Digest type" aria-label="DS digest type"><option value="1"' + (String(data.digestType) === '1' ? ' selected' : '') + '>D1</option><option value="2"' + (String(data.digestType) === '2' ? ' selected' : '') + '>D2</option><option value="3"' + (String(data.digestType) === '3' ? ' selected' : '') + '>D3</option><option value="4"' + (String(data.digestType) === '4' ? ' selected' : '') + '>D4</option></select>';
        } else if (type === 'SVCB' || type === 'HTTPS') {
            details.innerHTML = '<input type="number" class="form-control dm-detail-priority" min="0" max="65535" title="Priority" aria-label="Priority" value="' + escapeHtml(data.priority || '0') + '"><input type="text" class="form-control dm-detail-parameters" title="Parameters" aria-label="Parameters" placeholder="Parameters" value="' + escapeHtml(data.parameters || '') + '">';
        } else if (type === 'WR') {
            details.innerHTML = '<select class="form-control dm-detail-wr-type" title="Redirect type" aria-label="Redirect type"><option value="301"' + (String(data.wrType || '301') === '301' ? ' selected' : '') + '>301 Permanent</option><option value="302"' + (String(data.wrType || '301') === '302' ? ' selected' : '') + '>302 Temporary</option></select><label class="dm-cloudns-wr-save-path"><input type="checkbox" class="dm-detail-wr-save-path" value="1"' + (String(data.wrSavePath || '0') === '1' ? ' checked' : '') + '> Save Path</label>';
        } else {
            details.innerHTML = '<span class="dm-cloudns-detail-none">—</span>';
        }
    }

    function fieldValue(row, selector, fallback) {
        var field = row.querySelector(selector);
        return field && field.value != null ? String(field.value) : String(fallback == null ? '' : fallback);
    }

    function collectBulkRowData(row) {
        return {
            type: fieldValue(row, '.dm-cloudns-bulk-type', '').toUpperCase(),
            host: fieldValue(row, '.dm-cloudns-bulk-host', ''),
            value: fieldValue(row, '.dm-cloudns-bulk-value', ''),
            ttl: fieldValue(row, '.dm-cloudns-bulk-ttl', '3600'),
            priority: fieldValue(row, '.dm-detail-priority', '0'),
            weight: fieldValue(row, '.dm-detail-weight', '0'),
            port: fieldValue(row, '.dm-detail-port', '0'),
            flag: fieldValue(row, '.dm-detail-flag', '0'),
            caaType: fieldValue(row, '.dm-detail-caa-type', 'issue'),
            algorithm: fieldValue(row, '.dm-detail-algorithm', '1'),
            fingerprintType: fieldValue(row, '.dm-detail-fingerprint', '1'),
            usage: fieldValue(row, '.dm-detail-usage', '3'),
            selector: fieldValue(row, '.dm-detail-selector', '1'),
            matchingType: fieldValue(row, '.dm-detail-matching', '1'),
            keyTag: fieldValue(row, '.dm-detail-keytag', '0'),
            digestAlgorithm: fieldValue(row, '.dm-detail-digest-algorithm', '8'),
            digestType: fieldValue(row, '.dm-detail-digest-type', '2'),
            parameters: fieldValue(row, '.dm-detail-parameters', ''),
            wrType: fieldValue(row, '.dm-detail-wr-type', '301'),
            wrSavePath: (row.querySelector('.dm-detail-wr-save-path') && row.querySelector('.dm-detail-wr-save-path').checked) ? '1' : '0'
        };
    }

    function updateBulkButton(form) {
        var rows = form.querySelectorAll('.dm-cloudns-bulk-row');
        var submit = form.querySelector('.dm-cloudns-bulk-submit');
        var removeButtons = form.querySelectorAll('.dm-cloudns-remove-row');
        var count = rows.length;
        if (submit && !bulkBusy) {
            submit.textContent = count === 1 ? 'Add Record' : 'Add ' + count + ' Records';
            submit.value = submit.textContent;
        }
        for (var i = 0; i < removeButtons.length; i++) {
            removeButtons[i].disabled = count <= 1;
        }
    }

    function prepareBulkForm(form, alertsHtml) {
        var wrapper = document.createElement('form');
        wrapper.className = 'dm-cloudns-bulk-form';
        wrapper.setAttribute('novalidate', 'novalidate');
        wrapper.innerHTML = ''
            + '<div class="dm-cloudns-bulk-error" role="alert" hidden></div>'
            + '<div class="dm-cloudns-bulk-progress" role="status" aria-live="polite" hidden><span class="dm-cloudns-add-spinner" aria-hidden="true"></span><span class="dm-cloudns-bulk-progress-text">Preparing records…</span></div>'
            + '<div class="dm-cloudns-bulk-table-wrap">'
            + '  <div class="dm-cloudns-bulk-grid dm-cloudns-bulk-grid-header" aria-hidden="true"><div>Type</div><div>Host</div><div>Points To / Value</div><div>Details</div><div>TTL</div><div>Actions</div></div>'
            + '  <div class="dm-cloudns-bulk-rows"></div>'
            + '</div>'
            + '<div class="dm-cloudns-bulk-footer-1524">'
            + '  <div class="dm-cloudns-bulk-toolbar"><button type="button" class="dm-cloudns-add-another dm-cloudns-add-submit">+ Add Another Record</button><button type="button" class="dm-cloudns-bulk-view-button dm-cloudns-show-advanced">Advanced Single Record</button><button type="button" class="dm-cloudns-bulk-secondary dm-cloudns-bulk-help-toggle">Help</button></div>'
            + '  <div class="dm-cloudns-add-actions"><button type="button" class="dm-cloudns-add-cancel">Cancel</button><button type="submit" class="btn dm-cloudns-add-submit dm-cloudns-bulk-submit">Add Record</button></div>'
            + '</div>'
            + '<div class="dm-cloudns-bulk-help" hidden>Add up to 50 records in one operation. Leave Host empty for the zone root. Use the Duplicate Record icon for similar records. NAPTR, LOC, RP, HINFO, CERT, and SMIMEA remain available under Advanced Single Record. Web Redirect frame/title metadata is also available there.</div>';
        var rows = wrapper.querySelector('.dm-cloudns-bulk-rows');
        rows.appendChild(createBulkRow(form, getInitialRowData(form)));
        /* Patch 1524: do not carry legacy full-page notices into the clean bulk editor.
         * Bulk validation and provider errors are displayed by submitBulkForm(). */
        wrapper.addEventListener('click', function (event) {
            var target = event.target;
            if (!target || !target.closest || bulkBusy) { return; }
            if (target.closest('.dm-cloudns-add-another')) {
                if (rows.querySelectorAll('.dm-cloudns-bulk-row').length >= 50) { return; }
                rows.appendChild(createBulkRow(form, {
                    type: getInitialRowData(form).type,
                    host: '', value: '', ttl: getInitialRowData(form).ttl,
                    priority: '0', weight: '0', port: '0', flag: '0', caaType: 'issue',
                    algorithm: '1', fingerprintType: '1', usage: '3', selector: '1', matchingType: '1',
                    keyTag: '0', digestAlgorithm: '8', digestType: '2', parameters: '', wrType: '301', wrSavePath: '0'
                }));
                updateBulkButton(wrapper);
            } else if (target.closest('.dm-cloudns-duplicate-row')) {
                var source = target.closest('.dm-cloudns-bulk-row');
                if (source && rows.querySelectorAll('.dm-cloudns-bulk-row').length < 50) {
                    source.parentNode.insertBefore(createBulkRow(form, collectBulkRowData(source)), source.nextSibling);
                    updateBulkButton(wrapper);
                }
            } else if (target.closest('.dm-cloudns-remove-row')) {
                var row = target.closest('.dm-cloudns-bulk-row');
                if (row && rows.querySelectorAll('.dm-cloudns-bulk-row').length > 1) {
                    row.remove();
                    updateBulkButton(wrapper);
                }
            } else if (target.closest('.dm-cloudns-bulk-help-toggle')) {
                var help = wrapper.querySelector('.dm-cloudns-bulk-help');
                help.hidden = !help.hidden;
            } else if (target.closest('.dm-cloudns-show-advanced')) {
                renderAdvancedView();
            }
        });
        wrapper.addEventListener('change', function (event) {
            if (event.target && event.target.classList.contains('dm-cloudns-bulk-type')) {
                updateBulkRowType(event.target.closest('.dm-cloudns-bulk-row'), collectBulkRowData(event.target.closest('.dm-cloudns-bulk-row')));
            }
        });
        updateBulkButton(wrapper);
        return wrapper;
    }

    function prepareAdvancedForm(form, alertsHtml) {
        var clone = form.cloneNode(true);
        clone.classList.add('dm-cloudns-add-form');
        clone.setAttribute('data-dm-cloudns-modal-form', '1');

        var type = getTypeFromForm(clone);
        var currentHtml = clone.innerHTML;
        currentHtml = currentHtml.replace(/^\s*Type:\s*(?:Web Redirect|[A-Z0-9]+)\s*/i, '');
        clone.innerHTML = currentHtml;

        var submit = clone.querySelector('input[type="submit"], button[type="submit"]');
        var actions = document.createElement('div');
        actions.className = 'dm-cloudns-add-actions';
        actions.innerHTML = '<button type="button" class="dm-cloudns-add-cancel">Cancel</button>';

        if (submit) {
            submit.value = 'Add Record';
            submit.textContent = 'Add Record';
            submit.className = 'btn dm-cloudns-add-submit';
            actions.appendChild(submit);
        } else {
            var newSubmit = document.createElement('button');
            newSubmit.type = 'submit';
            newSubmit.className = 'btn dm-cloudns-add-submit';
            newSubmit.textContent = 'Add Record';
            actions.appendChild(newSubmit);
        }

        var wrapper = document.createElement('div');
        var toolbar = document.createElement('div');
        toolbar.className = 'dm-cloudns-advanced-toolbar';
        toolbar.innerHTML = '<button type="button" class="dm-cloudns-bulk-view-button dm-cloudns-show-bulk">Bulk Add Records</button>';
        wrapper.appendChild(toolbar);
        if (alertsHtml) {
            var alerts = document.createElement('div');
            alerts.className = 'dm-cloudns-add-alerts';
            alerts.innerHTML = alertsHtml;
            wrapper.appendChild(alerts);
        }
        var typePill = document.createElement('div');
        typePill.className = 'dm-cloudns-record-type-pill';
        typePill.textContent = formatRecordType(type);
        wrapper.appendChild(typePill);

        var webRedirectHelp = clone.querySelector('.WR_fields.type_fields.notification');
        if (webRedirectHelp) {
            var details = document.createElement('details');
            details.className = 'dm-cloudns-advanced-help';
            details.innerHTML = '<summary>Web Redirect Help</summary>';
            webRedirectHelp.parentNode.removeChild(webRedirectHelp);
            details.appendChild(webRedirectHelp);
            wrapper.appendChild(details);
        }

        wrapper.appendChild(clone);
        clone.appendChild(actions);
        toolbar.querySelector('.dm-cloudns-show-bulk').addEventListener('click', function () {
            renderBulkView();
        });

        var typeSelector = clone.querySelector('#addRecordType, [name="addRecordType"]');
        if (typeSelector) {
            typeSelector.removeAttribute('onchange');
            typeSelector.removeAttribute('onChange');
            typeSelector.addEventListener('change', function () {
                syncRecordTypeFields(wrapper);
            });
        }
        syncRecordTypeFields(wrapper);
        return wrapper;
    }

    function renderBulkView() {
        ensureModal();
        setModalTitle('Add DNS Records', currentSubtitle || 'Add one or multiple ClouDNS DNS records');
        var body = modal.querySelector('.dm-cloudns-add-body');
        body.innerHTML = '';
        body.appendChild(prepareBulkForm(currentBaseForm.cloneNode(true), currentAlertsHtml));
    }

    function renderAdvancedView() {
        ensureModal();
        setModalTitle('Add DNS Record', currentSubtitle || 'Advanced single-record form');
        var body = modal.querySelector('.dm-cloudns-add-body');
        body.innerHTML = '';
        body.appendChild(prepareAdvancedForm(currentBaseForm.cloneNode(true), currentAlertsHtml));
    }

    function renderAddPage(result, subtitle) {
        var doc = parseHtml(result.html);
        var form = findAddForm(doc);
        ensureModal();
        if (!form) {
            setModalTitle('Add DNS Records', subtitle || 'Add DNS records');
            showMessage('The ClouDNS DNS record add page did not return an add form. The original page is still available.', true, lastAddUrl);
            openModal();
            return;
        }
        ensureWebRedirectOption(form);
        currentBaseForm = form.cloneNode(true);
        currentAlertsHtml = collectAlerts(doc);
        currentSubtitle = subtitle || 'Add one or multiple ClouDNS DNS records';
        renderBulkView();
        openModal();
    }

    function responseLooksSuccessful(result) {
        var doc = parseHtml(result.html);
        if (/customAction=zone-settings/i.test(result.url || '') && !/customAction=add-record/i.test(result.url || '')) {
            return true;
        }
        if (doc.querySelector('#records-table')) {
            return true;
        }
        if (!findAddForm(doc) && /zone-settings/i.test(result.html)) {
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

    function formDataForBulkRow(baseForm, rowData) {
        var data = new FormData(baseForm);
        data.set('addRecordType', rowData.type);
        data.set('addRecordHost', rowData.host);
        data.set('addRecordTtl', rowData.ttl);
        data.set('addRecordRecord', rowData.type === 'CAA' ? '' : rowData.value);
        data.set('addRecordMXPriority', rowData.type === 'MX' ? rowData.priority : '10');
        data.set('addRecordSRVPriority', rowData.type === 'SRV' ? rowData.priority : '0');
        data.set('addRecordWeight', rowData.type === 'SRV' ? rowData.weight : '0');
        data.set('addRecordPort', rowData.type === 'SRV' ? rowData.port : '0');
        data.set('addRecordCAAflag', rowData.type === 'CAA' ? rowData.flag : '0');
        data.set('addRecordCAAtype', rowData.type === 'CAA' ? rowData.caaType : 'issue');
        data.set('addRecordCAAvalue', rowData.type === 'CAA' ? rowData.value : '');
        data.set('algorithm', rowData.type === 'SSHFP' ? rowData.algorithm : '1');
        data.set('fp_type', rowData.type === 'SSHFP' ? rowData.fingerprintType : '1');
        data.set('addRecordUsage', rowData.type === 'TLSA' ? rowData.usage : '3');
        data.set('addRecordSelector', rowData.type === 'TLSA' ? rowData.selector : '1');
        data.set('addRecordMatchingType', rowData.type === 'TLSA' ? rowData.matchingType : '1');
        data.set('addRecordKeyTag', rowData.type === 'DS' ? rowData.keyTag : '0');
        data.set('addRecordDsAlgorithm', rowData.type === 'DS' ? rowData.digestAlgorithm : '8');
        data.set('addRecordDigestType', rowData.type === 'DS' ? rowData.digestType : '2');
        data.set('addRecordPriority', (rowData.type === 'SVCB' || rowData.type === 'HTTPS') ? rowData.priority : '0');
        data.set('addRecordParameters', (rowData.type === 'SVCB' || rowData.type === 'HTTPS') ? rowData.parameters : '');
        data.set('wr_type', rowData.type === 'WR' ? (rowData.wrType || '301') : '301');
        if (rowData.type === 'WR' && String(rowData.wrSavePath || '0') === '1') {
            data.set('addRecordWRSavePath', '1');
        } else {
            data.delete('addRecordWRSavePath');
        }
        data.delete('addRecordWRFrame');
        data.delete('addRecordWRFrameTitle');
        data.delete('addRecordWRFrameDescription');
        data.delete('addRecordWRFrameKeywords');
        data.delete('addRecordWRMobileMeta');
        if (!data.has('do_save')) {
            data.append('do_save', 'Save');
        }
        return data;
    }

    function fetchFormData(form, data) {
        var action = absoluteUrl(form.getAttribute('action') || window.location.href);
        return fetch(action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: data
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text().then(function (html) {
                return { html: html, url: response.url || action };
            });
        });
    }

    function responseErrorText(result) {
        var doc = parseHtml(result.html);
        var nodes = doc.querySelectorAll('.notification, .cloudns-response-error, .alert-danger');
        for (var i = 0; i < nodes.length; i++) {
            var text = normalizeText(nodes[i].textContent || '');
            if (text && !/records\s*:\s*\d+/i.test(text)) {
                return text.slice(0, 280);
            }
        }
        return 'The DNS provider rejected this record.';
    }

    function validateBulkRows(form) {
        var rows = form.querySelectorAll('.dm-cloudns-bulk-row');
        var values = [];
        var errors = [];
        for (var i = 0; i < rows.length; i++) {
            var data = collectBulkRowData(rows[i]);
            rows[i].classList.remove('dm-row-error', 'dm-row-success');
            if (supportedBulkTypes.indexOf(data.type) === -1) {
                errors.push('Row ' + (i + 1) + ': choose a supported record type.');
                rows[i].classList.add('dm-row-error');
            }
            if (!normalizeText(data.value)) {
                errors.push('Row ' + (i + 1) + ': Points To / Value is required.');
                rows[i].classList.add('dm-row-error');
            }
            values.push({ row: rows[i], data: data, number: i + 1 });
        }
        return { values: values, errors: errors };
    }

    function setBulkDisabled(form, disabled) {
        var fields = form.querySelectorAll('input, select, button');
        for (var i = 0; i < fields.length; i++) {
            fields[i].disabled = disabled;
        }
        var close = modal ? modal.querySelector('.dm-cloudns-add-close') : null;
        if (close) { close.disabled = disabled; }
    }

    function submitBulkForm(form) {
        if (bulkBusy || !currentBaseForm) { return; }
        var validation = validateBulkRows(form);
        var errorBox = form.querySelector('.dm-cloudns-bulk-error');
        var progress = form.querySelector('.dm-cloudns-bulk-progress');
        var progressText = form.querySelector('.dm-cloudns-bulk-progress-text');
        var submit = form.querySelector('.dm-cloudns-bulk-submit');
        if (validation.errors.length) {
            errorBox.textContent = validation.errors.slice(0, 5).join(' ');
            errorBox.hidden = false;
            return;
        }
        errorBox.hidden = true;
        errorBox.textContent = '';
        bulkBusy = true;
        modal.classList.add('dm-bulk-busy');
        setBulkDisabled(form, true);
        progress.hidden = false;
        var total = validation.values.length;
        var completed = 0;
        var added = 0;
        var failures = [];
        if (submit) {
            submit.textContent = 'Adding ' + total + (total === 1 ? ' Record…' : ' Records…');
            submit.value = submit.textContent;
        }

        function updateProgress(currentNumber) {
            progressText.textContent = 'Adding record ' + currentNumber + ' of ' + total + ' · ' + completed + ' completed';
        }

        function finish() {
            bulkBusy = false;
            modal.classList.remove('dm-bulk-busy');
            if (failures.length === 0) {
                progressText.textContent = 'Added ' + added + ' of ' + total + ' records. Refreshing DNS records…';
                window.setTimeout(function () { window.location.reload(); }, 550);
                return;
            }
            setBulkDisabled(form, false);
            var close = modal.querySelector('.dm-cloudns-add-close');
            if (close) { close.disabled = false; }
            for (var i = validation.values.length - 1; i >= 0; i--) {
                if (validation.values[i].row.classList.contains('dm-row-success')) {
                    validation.values[i].row.remove();
                }
            }
            if (!form.querySelector('.dm-cloudns-bulk-row')) {
                form.querySelector('.dm-cloudns-bulk-rows').appendChild(createBulkRow(currentBaseForm, getInitialRowData(currentBaseForm)));
            }
            progress.hidden = true;
            errorBox.textContent = 'Added ' + added + ' of ' + total + '. ' + failures.slice(0, 4).join(' ');
            errorBox.hidden = false;
            updateBulkButton(form);
        }

        function run(index) {
            if (index >= validation.values.length) {
                finish();
                return;
            }
            var item = validation.values[index];
            updateProgress(item.number);
            fetchFormData(currentBaseForm, formDataForBulkRow(currentBaseForm, item.data))
                .then(function (result) {
                    completed++;
                    if (responseLooksSuccessful(result)) {
                        added++;
                        item.row.classList.add('dm-row-success');
                    } else {
                        item.row.classList.add('dm-row-error');
                        failures.push('Row ' + item.number + ': ' + responseErrorText(result));
                    }
                    run(index + 1);
                })
                .catch(function (error) {
                    completed++;
                    item.row.classList.add('dm-row-error');
                    failures.push('Row ' + item.number + ': request failed (' + normalizeText(error && error.message ? error.message : 'network error') + ').');
                    finish();
                });
        }
        run(0);
    }

    function closeAfterSuccess() {
        closeModal();
        window.setTimeout(function () {
            window.location.reload();
        }, 250);
    }

    function openAddModal(link) {
        if (!window.fetch || !window.FormData || !window.DOMParser) {
            return false;
        }
        lastAddUrl = absoluteUrl(link.href || link.getAttribute('href') || '');
        lastZoneUrl = getZoneUrlFromAddUrl(lastAddUrl);
        var subtitle = getSubtitleFromLink(link);

        // Give immediate feedback while the native ClouDNS add form is loading.
        // The form request can be slow because it is rendered server-side; the
        // custom modal itself is created locally and does not need to wait.
        setModalTitle('Add DNS Records', subtitle || 'Loading DNS record form…');
        showLoading('Loading DNS record form…');
        openModal();

        // Reuse an in-flight hover/focus warm-up request or an already-fetched
        // form for this exact URL. This keeps repeat opens instant without
        // preloading the add page on every DNS Records page view.
        loadAddPage(lastAddUrl)
            .then(function (result) {
                renderAddPage(result, subtitle);
            })
            .catch(function () {
                window.location.href = lastAddUrl;
            });
        return true;
    }

    function submitAddForm(form, submitter) {
        var button = form.querySelector('.dm-cloudns-add-submit');
        if (button) {
            button.disabled = true;
            button.value = 'Adding…';
            button.textContent = 'Adding…';
        }
        fetchForm(form, submitter)
            .then(function (result) {
                if (responseLooksSuccessful(result)) {
                    closeAfterSuccess();
                    return;
                }
                var doc = parseHtml(result.html);
                var addForm = findAddForm(doc);
                if (addForm) {
                    var body = modal.querySelector('.dm-cloudns-add-body');
                    body.innerHTML = '';
                    body.appendChild(prepareAdvancedForm(addForm, collectAlerts(doc)));
                    return;
                }
                if (!responseLooksError(result)) {
                    closeAfterSuccess();
                    return;
                }
                showMessage('The DNS record could not be added in the popup. Please review the response or open the full page.', true, lastAddUrl);
            })
            .catch(function () {
                if (button) {
                    button.disabled = false;
                    button.value = 'Add Record';
                    button.textContent = 'Add Record';
                }
                showMessage('The DNS record could not be added in the popup. Please try again or open the full page.', true, lastAddUrl);
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleInitialAddFormWarmup, { once: true });
    } else {
        scheduleInitialAddFormWarmup();
    }

    document.addEventListener('mouseover', function (event) {
        var link = event.target && event.target.closest ? event.target.closest('a.cloudns-add-record-link[href*=\"customAction=add-new-record\"]') : null;
        if (link) {
            warmMainAddLink(link);
        }
    }, true);

    document.addEventListener('focusin', function (event) {
        var link = event.target && event.target.closest ? event.target.closest('a.cloudns-add-record-link[href*=\"customAction=add-new-record\"]') : null;
        if (link) {
            warmMainAddLink(link);
        }
    }, true);

    document.addEventListener('touchstart', function (event) {
        var link = event.target && event.target.closest ? event.target.closest('a.cloudns-add-record-link[href*=\"customAction=add-new-record\"]') : null;
        if (link) {
            warmMainAddLink(link);
        }
    }, { capture: true, passive: true });

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.isTrusted === false) {
            return;
        }
        var link = event.target && event.target.closest ? event.target.closest('a[href*="customAction=add-new-record"]') : null;
        if (!link || isInsideModal(link) || !isClouDnsAddUrl(link.getAttribute('href') || link.href || '')) {
            return;
        }
        if (openAddModal(link)) {
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
