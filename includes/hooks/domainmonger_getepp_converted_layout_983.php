<?php
/**
 * DomainMonger Get EPP converted layout/control bridge — Patch 995.
 *
 * Page-specific recovery:
 * - Keeps the confirmed Restore 759 EPP/Auth Code manager/action handling.
 * - Keeps Generate/Create controls in the main content flow.
 * - Uses the approved full-width card layout for checklist, actions, and related tools.
 * - Restores the current-code value only from the trusted #dm-epp-code-field output and never from tokens/custom fields.
 * - De-duplicates repeated EPP output alerts/messages.
 * - Does not touch templates, integration folder, language file, registrar/module files,
 *   or the confirmed Patch 953/Patch 962 behavior.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_epp_983_is_page')) {
    function dm_epp_983_is_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        if ($script !== 'clientarea.php' && strpos($uri, '/manage/clientarea.php') === false) {
            return false;
        }

        return $action === 'domaingetepp' || strpos($uri, 'action=domaingetepp') !== false;
    }
}

if (!function_exists('dm_epp_983_domain_id')) {
    function dm_epp_983_domain_id(): int
    {
        foreach (['domainid', 'id'] as $key) {
            $value = (int) ($_REQUEST[$key] ?? 0);
            if ($value > 0) {
                return $value;
            }
        }
        return 0;
    }
}

if (!function_exists('dm_epp_983_require_manager')) {
    function dm_epp_983_require_manager(): void
    {
        if (!function_exists('dm_epp_get_token')) {
            $manager = __DIR__ . '/domainmonger_epp_authcode_manager.php';
            if (is_readable($manager)) {
                require_once $manager;
            }
        }
    }
}

add_hook('ClientAreaFooterOutput', 40, function () {
    if (!dm_epp_983_is_page()) {
        return '';
    }

    dm_epp_983_require_manager();

    $domainId = dm_epp_983_domain_id();
    $token = function_exists('dm_epp_get_token') ? (string) dm_epp_get_token() : '';
    $endpointUrl = function_exists('dm_epp_endpoint_url') ? (string) dm_epp_endpoint_url() : 'dm-epp-authcode-action.php';
    $formUrl = function_exists('dm_epp_form_url') ? (string) dm_epp_form_url($domainId) : ('clientarea.php?action=domaingetepp&domainid=' . max(0, $domainId));

    $managerEnabled = true;
    if (function_exists('dm_epp_get_domain_row')) {
        try {
            $row = dm_epp_get_domain_row($domainId);
            if ($row && !empty($row->registrar)) {
                $managerEnabled = (bool) preg_match('/(netearth|resellerclub|logicboxes)/i', (string) $row->registrar);
            }
        } catch (Throwable $e) {
            $managerEnabled = true;
        }
    }

    $domainIdAttr = htmlspecialchars((string) $domainId, ENT_QUOTES, 'UTF-8');
    $tokenAttr = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    $endpointAttr = htmlspecialchars($endpointUrl, ENT_QUOTES, 'UTF-8');
    $formUrlAttr = htmlspecialchars($formUrl, ENT_QUOTES, 'UTF-8');
    $disabled = ($token === '' || !$managerEnabled) ? ' disabled' : '';

    $statusHtml = '';
    if (!$managerEnabled) {
        $statusHtml = '<div class="dm-getepp-983-warning">EPP/Auth code management is not enabled for this registrar/domain.</div>';
    } elseif ($token === '') {
        $statusHtml = '<div class="dm-getepp-983-warning">Security token was not available. Reload this page before using these controls.</div>';
    }

    return <<<HTML
<style id="dm-getepp-converted-layout-css-995">
body.whmcs-templatefile-clientareadomaingetepp.dm-getepp-983-ready .dm-getepp-983-hidden,
body.whmcs-templatefile-clientareadomaingetepp.dm-getepp-983-ready .dm-getepp-983-side-hidden {
    display: none !important;
}
body.whmcs-templatefile-clientareadomaingetepp.dm-getepp-983-ready .dm-getepp-983-main {
    width: 100% !important;
    max-width: 100% !important;
    flex: 0 0 100% !important;
}
.dm-getepp-983-shell {
    max-width: 1180px !important;
    margin: 22px auto 34px !important;
    padding: 0 10px !important;
    color: #263746 !important;
}
.dm-getepp-983-hero {
    border: 0 !important;
    border-radius: 0 !important;
    overflow: visible !important;
    background: transparent !important;
    box-shadow: none !important;
    margin: 0 0 18px !important;
}
.dm-getepp-983-hero-head {
    background: #163a5f !important;
    color: #fff !important;
    padding: 14px 18px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 12px !important;
    flex-wrap: wrap !important;
}
.dm-getepp-983-hero-head h2 {
    margin: 0 !important;
    color: #fff !important;
    font-size: 20px !important;
    line-height: 1.25 !important;
}
.dm-getepp-983-badge {
    display: inline-flex !important;
    align-items: center !important;
    border-radius: 999px !important;
    padding: 5px 10px !important;
    background: rgba(255,255,255,.14) !important;
    color: #fff !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}
.dm-getepp-983-hero-body {
    padding: 18px !important;
    background: #fff !important;
}
.dm-getepp-983-grid {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 16px !important;
    align-items: start !important;
}
.dm-getepp-983-card {
    border: 1px solid #dfe7ef !important;
    border-radius: 9px !important;
    background: #fff !important;
    overflow: hidden !important;
    margin: 0 !important;
    box-shadow: 0 2px 10px rgba(22,58,95,.06) !important;
}
.dm-getepp-983-card-head {
    background: #163a5f !important;
    color: #fff !important;
    padding: 12px 16px !important;
    font-weight: 700 !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}
.dm-getepp-983-card-body {
    padding: 16px !important;
}
.dm-getepp-983-intro {
    margin: 0 0 12px !important;
    color: #4d5965 !important;
    line-height: 1.5 !important;
}
.dm-getepp-983-current-code .alert,
.dm-getepp-983-current-code [class*="alert"] {
    margin: 0 !important;
}
.dm-getepp-983-controls form {
    margin: 0 !important;
}
.dm-getepp-983-controls .dm-getepp-983-card-body {
    display: block !important;
}
.dm-getepp-983-controls .dm-getepp-983-intro,
.dm-getepp-983-controls .dm-getepp-983-note,
.dm-getepp-983-controls .dm-getepp-983-warning {
    margin-bottom: 12px !important;
}
.dm-getepp-983-controls .form-control {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    margin: 0 0 9px !important;
}
.dm-getepp-983-action-grid {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr) !important;
    gap: 16px !important;
    align-items: stretch !important;
    width: 100% !important;
}
.dm-getepp-983-action-card {
    border: 1px solid #dfe7ef !important;
    border-radius: 8px !important;
    background: #f8fbff !important;
    padding: 16px !important;
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
    min-height: 210px !important;
}
.dm-getepp-983-action-card h3 {
    margin: 0 !important;
    color: #163a5f !important;
    font-size: 16px !important;
    line-height: 1.3 !important;
}
.dm-getepp-983-action-card p {
    margin: 0 !important;
    color: #4d5965 !important;
    line-height: 1.45 !important;
    font-size: 13px !important;
}
.dm-getepp-983-custom-card {
    background: #fff9ef !important;
    border-color: #f6d29c !important;
}
.dm-getepp-983-action-icon {
    width: 46px !important;
    height: 46px !important;
    border-radius: 50% !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: #fff !important;
    color: #163a5f !important;
    box-shadow: 0 2px 8px rgba(22,58,95,.10) !important;
    font-size: 18px !important;
}
.dm-getepp-983-custom-card .dm-getepp-983-action-icon {
    color: #f58220 !important;
    background: #fff3df !important;
}
.dm-getepp-983-or {
    align-self: center !important;
    justify-self: center !important;
    width: 38px !important;
    height: 38px !important;
    border-radius: 50% !important;
    border: 1px solid #dfe7ef !important;
    background: #fff !important;
    color: #607080 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    box-shadow: 0 2px 8px rgba(22,58,95,.08) !important;
}
.dm-getepp-983-check-list {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
    display: grid !important;
    gap: 12px !important;
}
.dm-getepp-983-check-list li {
    position: relative !important;
    margin: 0 !important;
    padding-left: 34px !important;
    color: #4d5965 !important;
    line-height: 1.45 !important;
}
.dm-getepp-983-check-list li:before {
    content: "\f00c" !important;
    font-family: FontAwesome, "Font Awesome 5 Free", "Font Awesome 6 Free" !important;
    font-weight: 900 !important;
    position: absolute !important;
    left: 0 !important;
    top: -1px !important;
    width: 22px !important;
    height: 22px !important;
    border: 2px solid #55bfa3 !important;
    color: #55bfa3 !important;
    border-radius: 50% !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 11px !important;
}
.dm-getepp-983-related-row {
    display: grid !important;
    grid-template-columns: auto minmax(0, 1fr) auto !important;
    gap: 12px !important;
    align-items: center !important;
}
.dm-getepp-983-related-icon {
    width: 42px !important;
    height: 42px !important;
    border-radius: 50% !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: #eaf3ff !important;
    color: #163a5f !important;
    font-size: 18px !important;
}
.dm-getepp-983-related-title {
    margin: 0 0 2px !important;
    color: #163a5f !important;
    font-weight: 700 !important;
}
.dm-getepp-983-related-desc {
    margin: 0 !important;
    color: #4d5965 !important;
    font-size: 13px !important;
}

.dm-getepp-983-action-card form {
    margin-top: auto !important;
    width: 100% !important;
    min-width: 0 !important;
}
.dm-getepp-983-action-card label {
    display: block !important;
    margin: 0 0 5px !important;
    color: #263746 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}
.dm-getepp-983-custom-controls {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 10px !important;
    min-width: 0 !important;
    width: 100% !important;
}
.dm-getepp-983-custom-controls input[name="dm_epp_custom_code"] {
    min-height: 44px !important;
    font-size: 14px !important;
    padding: 10px 12px !important;
}
.dm-getepp-983-safe-note {
    border: 1px solid #f2dd99 !important;
    border-radius: 6px !important;
    background: #fff7df !important;
    color: #5d4a12 !important;
    padding: 8px 9px !important;
    line-height: 1.45 !important;
    margin: 0 !important;
}
.dm-getepp-983-safe-note code {
    color: #5d4a12 !important;
    background: transparent !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
}
.dm-getepp-983-controls .btn,
.dm-getepp-983-actions .btn {
    white-space: normal !important;
    text-align: center !important;
    border-radius: 5px !important;
    font-weight: 700 !important;
}
.dm-getepp-983-controls .btn-primary,
.dm-getepp-983-controls .btn-success,
.dm-getepp-983-actions .btn-primary {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
}
.dm-getepp-983-controls .btn-primary:hover,
.dm-getepp-983-controls .btn-primary:focus,
.dm-getepp-983-controls .btn-success:hover,
.dm-getepp-983-controls .btn-success:focus,
.dm-getepp-983-actions .btn-primary:hover,
.dm-getepp-983-actions .btn-primary:focus {
    background: #d8741f !important;
    border-color: #d8741f !important;
    color: #fff !important;
}
.dm-getepp-983-controls .btn {
    display: block !important;
    width: 100% !important;
    padding: 10px 14px !important;
}
.dm-getepp-983-note,
.dm-getepp-983-small {
    display: block !important;
    font-size: 12px !important;
    color: #607080 !important;
    line-height: 1.45 !important;
}
.dm-getepp-983-warning {
    margin: 0 0 10px !important;
    padding: 9px 10px !important;
    border-radius: 6px !important;
    background: #fff7df !important;
    border: 1px solid #f2dd99 !important;
    color: #5d4a12 !important;
    font-size: 12px !important;
}
.dm-getepp-983-list {
    margin: 0 !important;
    padding-left: 18px !important;
    color: #4d5965 !important;
}
.dm-getepp-983-list li {
    margin: 0 0 7px !important;
}
.dm-getepp-983-cloned-alert {
    margin-bottom: 12px !important;
}

.dm-getepp-983-codebox {
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
}
.dm-getepp-983-codebox label {
    display: block !important;
    margin: 0 0 6px !important;
    color: #163a5f !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}
.dm-getepp-983-code-row {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto !important;
    gap: 14px !important;
    align-items: center !important;
    min-width: 0 !important;
    margin: 10px 0 12px !important;
}
.dm-getepp-983-code-row input {
    min-width: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    font-family: Menlo, Monaco, Consolas, "Courier New", monospace !important;
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #163a5f !important;
    height: 48px !important;
}
.dm-getepp-983-code-row .btn {
    width: auto !important;
    min-width: 116px !important;
    white-space: nowrap !important;
}
.dm-getepp-983-current-empty {
    border: 1px solid #dfe7ef !important;
    border-radius: 8px !important;
    background: #f8fafc !important;
    color: #4d5965 !important;
    padding: 10px 12px !important;
    margin: 10px 0 12px !important;
}
.dm-getepp-983-privacy-note {
    border: 1px solid #cfe0f2 !important;
    border-radius: 6px !important;
    background: #f1f7ff !important;
    color: #27527d !important;
    padding: 8px 10px !important;
    font-size: 12px !important;
    line-height: 1.45 !important;
}
.dm-getepp-983-custom-controls input[name="dm_epp_custom_code"] {
    order: 1 !important;
}
.dm-getepp-983-custom-controls .dm-getepp-983-safe-note {
    order: 2 !important;
}
.dm-getepp-983-custom-controls button {
    order: 3 !important;
}


/* Patch 995: stronger page-specific action layout and button cleanup. */
.dm-getepp-983-shell .dm-getepp-983-custom-controls label,
.dm-getepp-983-shell .dm-getepp-983-custom-card label[for="dm-epp-custom-code-983"],
.dm-getepp-983-shell label[for="dm-epp-custom-code-983"] {
    display: none !important;
}
.dm-getepp-983-shell .dm-getepp-983-form button[data-dm-epp-submit="1"],
.dm-getepp-983-shell .dm-getepp-983-form input[type="submit"],
.dm-getepp-983-shell .dm-getepp-983-controls button.btn,
.dm-getepp-983-shell .dm-getepp-983-controls .btn-primary,
.dm-getepp-983-shell .dm-getepp-983-controls .btn-success,
.dm-getepp-983-shell .dm-getepp-983-custom-card button,
.dm-getepp-983-shell .dm-getepp-983-generate-card button {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
    box-shadow: none !important;
}
.dm-getepp-983-shell .dm-getepp-983-form button[data-dm-epp-submit="1"]:hover,
.dm-getepp-983-shell .dm-getepp-983-form button[data-dm-epp-submit="1"]:focus,
.dm-getepp-983-shell .dm-getepp-983-form input[type="submit"]:hover,
.dm-getepp-983-shell .dm-getepp-983-form input[type="submit"]:focus,
.dm-getepp-983-shell .dm-getepp-983-controls button.btn:hover,
.dm-getepp-983-shell .dm-getepp-983-controls button.btn:focus,
.dm-getepp-983-shell .dm-getepp-983-custom-card button:hover,
.dm-getepp-983-shell .dm-getepp-983-custom-card button:focus,
.dm-getepp-983-shell .dm-getepp-983-generate-card button:hover,
.dm-getepp-983-shell .dm-getepp-983-generate-card button:focus {
    background: #d8741f !important;
    border-color: #d8741f !important;
    color: #fff !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .18) !important;
}
.dm-getepp-983-shell .dm-getepp-983-custom-controls input[name="dm_epp_custom_code"],
.dm-getepp-983-shell #dm-epp-custom-code-983 {
    width: 100% !important;
    max-width: none !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
}
.dm-getepp-983-shell .dm-getepp-983-custom-controls button[data-dm-epp-submit="1"] {
    width: 100% !important;
    max-width: none !important;
}



/* Patch 995: force the visible action controls to match and fill the card width. */
.dm-getepp-983-shell .dm-getepp-983-generate-card form,
.dm-getepp-983-shell .dm-getepp-983-custom-card form,
.dm-getepp-983-shell .dm-getepp-983-custom-controls {
    width: 100% !important;
    max-width: 100% !important;
}
.dm-getepp-983-shell .dm-getepp-983-generate-card button[data-dm-epp-submit="1"],
.dm-getepp-983-shell .dm-getepp-983-custom-card button[data-dm-epp-submit="1"],
.dm-getepp-983-shell .dm-getepp-983-controls button[data-dm-epp-submit="1"],
.dm-getepp-983-shell .dm-getepp-983-controls input[type="submit"],
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .btn-primary {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
    text-align: center !important;
    box-shadow: none !important;
}
.dm-getepp-983-shell .dm-getepp-983-generate-card button[data-dm-epp-submit="1"]:hover,
.dm-getepp-983-shell .dm-getepp-983-generate-card button[data-dm-epp-submit="1"]:focus,
.dm-getepp-983-shell .dm-getepp-983-custom-card button[data-dm-epp-submit="1"]:hover,
.dm-getepp-983-shell .dm-getepp-983-custom-card button[data-dm-epp-submit="1"]:focus,
.dm-getepp-983-shell .dm-getepp-983-controls button[data-dm-epp-submit="1"]:hover,
.dm-getepp-983-shell .dm-getepp-983-controls button[data-dm-epp-submit="1"]:focus,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .btn-primary:hover,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .btn-primary:focus {
    background: #d8741f !important;
    border-color: #d8741f !important;
    color: #fff !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .18) !important;
}
.dm-getepp-983-shell .dm-getepp-983-generate-card form:focus,
.dm-getepp-983-shell .dm-getepp-983-generate-card form:focus-within,
.dm-getepp-983-shell .dm-getepp-983-custom-card form:focus,
.dm-getepp-983-shell .dm-getepp-983-custom-card form:focus-within {
    outline: none !important;
    box-shadow: none !important;
}
.dm-getepp-983-shell .dm-getepp-983-custom-card label,
.dm-getepp-983-shell .dm-getepp-983-custom-card .control-label,
.dm-getepp-983-shell label[for="dm-epp-custom-code-983"] {
    display: none !important;
}
.dm-getepp-983-shell .dm-getepp-983-custom-card input[name="dm_epp_custom_code"],
.dm-getepp-983-shell #dm-epp-custom-code-983 {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
}

@media (max-width: 700px) {
    .dm-getepp-983-code-row { grid-template-columns: 1fr !important; }
    .dm-getepp-983-code-row .btn { width: 100% !important; }
}
@media (max-width: 900px) {
    .dm-getepp-983-grid { grid-template-columns: 1fr !important; }
    .dm-getepp-983-action-grid { grid-template-columns: 1fr !important; }
    .dm-getepp-983-or { width: 100% !important; border-radius: 8px !important; height: 32px !important; }
    .dm-getepp-983-related-row { grid-template-columns: auto minmax(0, 1fr) !important; }
    .dm-getepp-983-related-row .btn { grid-column: 1 / -1 !important; width: 100% !important; }
}


/* Patch 996: layout-only cleanup after real EPP code was confirmed working.
   Do not change EPP-code extraction or action handling; only normalize visible controls. */
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell {
    max-width: 1220px !important;
}
body.dm-rc-page-getepp .dm-getepp-983-shell {
    margin-top: 0 !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-grid {
    grid-template-columns: minmax(0, 1fr) 46px minmax(0, 1fr) !important;
    gap: 18px !important;
    align-items: stretch !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card {
    padding: 18px !important;
    min-height: 0 !important;
    gap: 11px !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card p {
    min-height: 38px !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card form,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card .dm-epp-authcode-row,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card .form-group,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card .input-group,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card .input-group-btn,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card [class*="col-"] {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    float: none !important;
    border: 0 !important;
    outline: 0 !important;
    box-shadow: none !important;
    background: transparent !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-generate-card form,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-custom-card form {
    margin-top: auto !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card button,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card input[type="submit"],
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card .btn {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    min-height: 44px !important;
    padding: 10px 14px !important;
    border-radius: 5px !important;
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
    font-weight: 700 !important;
    text-align: center !important;
    white-space: normal !important;
    box-shadow: none !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card button:hover,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card button:focus,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card input[type="submit"]:hover,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card input[type="submit"]:focus,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card .btn:hover,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-card .btn:focus {
    background: #d8741f !important;
    border-color: #d8741f !important;
    color: #fff !important;
    outline: 0 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .18) !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-generate-card form:focus-within {
    outline: 0 !important;
    border: 0 !important;
    box-shadow: none !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-custom-controls {
    width: 100% !important;
    max-width: 100% !important;
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 10px !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-custom-controls input[name="dm_epp_custom_code"],
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell #dm-epp-custom-code-983 {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
}
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-custom-controls label,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-custom-card label,
body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-custom-card .control-label {
    display: none !important;
}
@media (max-width: 900px) {
    body.whmcs-templatefile-clientareadomaingetepp .dm-getepp-983-shell .dm-getepp-983-action-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
<template id="dm-getepp-983-template">
    <section class="dm-getepp-983-shell" data-dm-getepp-layout="995">
        <div class="dm-getepp-983-hero">
            <div class="dm-getepp-983-hero-body">
                <div class="dm-getepp-983-grid">
                    <div class="dm-getepp-983-card dm-getepp-983-current-card">
                        <div class="dm-getepp-983-card-body">
                            <p class="dm-getepp-983-intro"><strong>Current EPP/Auth Code</strong><br>Use the code below when transferring this domain to another registrar.</p>
                            <div class="dm-getepp-983-current-code" data-dm-code-target></div>
                        </div>
                    </div>

                    <div class="dm-getepp-983-card">
                        <div class="dm-getepp-983-card-head">
                            <span class="fa fa-shield" aria-hidden="true"></span>
                            <span>Before Using the Code</span>
                        </div>
                        <div class="dm-getepp-983-card-body">
                            <ul class="dm-getepp-983-check-list">
                                <li>Confirm the domain is unlocked if you plan to transfer it.</li>
                                <li>Use the most recent EPP/Auth code shown on this page.</li>
                                <li>Generate a new code if the current code was shared by mistake.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="dm-getepp-983-card dm-getepp-983-controls">
                        <div class="dm-getepp-983-card-head">
                            <span class="fa fa-key" aria-hidden="true"></span>
                            <span>Manage EPP/Auth Code</span>
                        </div>
                        <div class="dm-getepp-983-card-body">
                            {$statusHtml}
                            <div class="dm-getepp-983-action-grid">
                                <div class="dm-getepp-983-action-card dm-getepp-983-generate-card">
                                    <span class="dm-getepp-983-action-icon fa fa-refresh" aria-hidden="true"></span>
                                    <h3>Generate New EPP/Auth Code</h3>
                                    <p>Generate a new system-created code. This will immediately replace your current code.</p>
                                    <form class="dm-epp-authcode-row dm-getepp-983-form" method="post" action="{$endpointAttr}" data-dm-epp-983-form="generate">
                                        <input type="hidden" name="dm_epp_action" value="generate">
                                        <input type="hidden" name="dm_epp_token" value="{$tokenAttr}">
                                        <input type="hidden" name="domainid" value="{$domainIdAttr}">
                                        <button type="submit" class="btn btn-primary" data-dm-epp-submit="1"{$disabled}>Generate New Code</button>
                                    </form>
                                </div>

                                <div class="dm-getepp-983-or" aria-hidden="true">OR</div>

                                <div class="dm-getepp-983-action-card dm-getepp-983-custom-card">
                                    <span class="dm-getepp-983-action-icon fa fa-pencil" aria-hidden="true"></span>
                                    <h3>Create Custom EPP/Auth Code</h3>
                                    <p>Enter a custom code you want to use as the domain authorization code.</p>
                                    <form class="dm-epp-authcode-row dm-getepp-983-form" method="post" action="{$endpointAttr}" data-dm-epp-983-form="custom">
                                        <input type="hidden" name="dm_epp_action" value="custom">
                                        <input type="hidden" name="dm_epp_token" value="{$tokenAttr}">
                                        <input type="hidden" name="domainid" value="{$domainIdAttr}">
                                        <div class="dm-getepp-983-custom-controls">
                                            <input id="dm-epp-custom-code-983" type="text" class="form-control" name="dm_epp_custom_code" placeholder="Enter custom EPP/Auth code" minlength="8" maxlength="16" autocomplete="off" autocapitalize="off" spellcheck="false">
                                            <span class="dm-getepp-983-note dm-getepp-983-safe-note"><strong>Required:</strong> 8-16 characters with uppercase, lowercase, a number, and one symbol: <code>. ~ ! @ # % ^ + = : -</code></span>
                                            <button type="submit" class="btn btn-primary" data-dm-epp-submit="1"{$disabled}>Save Custom Code</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dm-getepp-983-card dm-getepp-983-actions">
                        <div class="dm-getepp-983-card-head">
                            <span class="fa fa-wrench" aria-hidden="true"></span>
                            <span>Related Tools</span>
                        </div>
                        <div class="dm-getepp-983-card-body">
                            <div class="dm-getepp-983-related-row">
                                <span class="dm-getepp-983-related-icon fa fa-lock" aria-hidden="true"></span>
                                <div>
                                    <p class="dm-getepp-983-related-title">Registrar Lock</p>
                                    <p class="dm-getepp-983-related-desc">Check if your domain is locked or unlocked before transferring.</p>
                                </div>
                                <a class="btn btn-primary" data-dm-reglock-link href="clientarea.php?action=domaindetails&id={$domainIdAttr}&dmsection=reglock&dmdesign=1&dmconverted=1#tabReglock">Check Lock Status</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <span data-dm-form-url="{$formUrlAttr}" hidden></span>
    </section>
</template>
<script id="dm-getepp-converted-layout-js-995">
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function cleanText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function lowerText(el) {
        return cleanText((el && (el.innerText || el.textContent)) || '').toLowerCase();
    }

    function isSideColumn(el) {
        var text = lowerText(el);
        var cls = String((el && el.className) || '').toLowerCase();
        if (!el || el.id === 'dm-getepp-983-live') {
            return false;
        }
        return (
            cls.indexOf('sidebar') !== -1 ||
            text.indexOf('auto renew') !== -1 && text.indexOf('nameservers') !== -1 && text.indexOf('registrar lock') !== -1 ||
            text.indexOf('manage epp/auth code') !== -1 && text.indexOf('renew domain') !== -1
        );
    }

    function hideSideColumns() {
        var nodes = document.querySelectorAll('.panel-sidebar, .sidebar, aside, .col-md-3, .col-sm-3, .col-lg-3, [class*="sidebar"]');
        var i;
        for (i = 0; i < nodes.length; i += 1) {
            if (isSideColumn(nodes[i])) {
                nodes[i].classList.add('dm-getepp-983-side-hidden');
            }
        }
    }

    function findMainTarget() {
        var selectors = [
            '.whmcs-templatefile-clientareadomaingetepp .col-md-9',
            '.whmcs-templatefile-clientareadomaingetepp .col-sm-9',
            '.whmcs-templatefile-clientareadomaingetepp .col-lg-9',
            '#main-body .container .row > .col-md-9',
            '#main-body .container .row > .col-sm-9',
            '#main-body .container .row > .col-lg-9',
            '.whmcs-templatefile-clientareadomaingetepp .contentarea',
            '.whmcs-templatefile-clientareadomaingetepp .main-content',
            '#main-body .container'
        ];
        var i, el, text;
        for (i = 0; i < selectors.length; i += 1) {
            el = document.querySelector(selectors[i]);
            if (!el || isSideColumn(el)) {
                continue;
            }
            text = lowerText(el);
            if (text.indexOf('epp') !== -1 || text.indexOf('authorization') !== -1 || selectors[i] === '#main-body .container') {
                return el;
            }
        }
        return document.querySelector('#main-body') || document.body;
    }

    function extractDomainName(root) {
        var nodes = (root || document).querySelectorAll('h1, h2, h3, h4, strong, .domain-name, .dm-domain, [data-domain]');
        var i, text, match;
        for (i = 0; i < nodes.length; i += 1) {
            text = cleanText(nodes[i].getAttribute && nodes[i].getAttribute('data-domain') || nodes[i].textContent || '');
            match = text.match(/[a-z0-9][a-z0-9-]*(?:\.[a-z0-9][a-z0-9-]*)+\.?/i);
            if (match) {
                return match[0].replace(/\.$/, '');
            }
        }
        return 'Domain';
    }


    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function looksLikeEppCode(value) {
        var text = cleanText(value);
        if (!text || text.length < 6 || text.length > 128) {
            return false;
        }
        if (/^(generate|create|copy|domain|register|transfer|auth|epp|code)$/i.test(text)) {
            return false;
        }
        if (/^[0-9]+$/.test(text) && text.length < 8) {
            return false;
        }
        if (/\s/.test(text)) {
            return false;
        }
        return /^[A-Za-z0-9.~!@#%^+=:_-]+$/.test(text);
    }

    function isBadCodeSource(node) {
        if (!node) {
            return true;
        }
        var tag = String(node.tagName || '').toLowerCase();
        var type = String(node.getAttribute && node.getAttribute('type') || '').toLowerCase();
        var name = String(node.getAttribute && node.getAttribute('name') || '').toLowerCase();
        var id = String(node.getAttribute && node.getAttribute('id') || '').toLowerCase();
        var value = cleanText(node.getAttribute && (node.getAttribute('value') || node.getAttribute('data-epp-code') || node.getAttribute('data-auth-code')) || node.value || node.textContent || '');

        if (id === 'dm-epp-code-field') {
            return false;
        }
        if (tag === 'input' && type === 'hidden') {
            return true;
        }
        if (/(token|csrf|action|domainid|domain_id|^id$|custom|length|password|generate)/.test(name + ' ' + id)) {
            return true;
        }
        if (/^(?:[a-f0-9]{32}|[a-f0-9]{40}|[a-f0-9]{48}|[a-f0-9]{64})$/i.test(value)) {
            return true;
        }
        return false;
    }

    function trustedEppFieldValue() {
        var field = document.getElementById('dm-epp-code-field');
        var value = '';
        if (!field) {
            return '';
        }
        value = cleanText(field.value || field.getAttribute('value') || field.textContent || '');
        if (looksLikeEppCode(value) && !isBadCodeSource(field)) {
            return value;
        }
        return '';
    }

    function extractExistingEppCode(root) {
        /*
         * Patch 995: keep the token-safe rule from 994, but search the whole rendered
         * page for the real WHMCS EPP display field because the current code can sit
         * outside the main column before the converted layout moves content.
         */
        return trustedEppFieldValue();
    }

    function renderCurrentCode(root, target) {
        var code = extractExistingEppCode(document);
        if (code) {
            target.innerHTML = '' +
                '<div class="dm-getepp-983-codebox">' +
                    '<div class="dm-getepp-983-code-row">' +
                        '<input id="dm-getepp-current-code-983" class="form-control" type="text" readonly value="' + escapeHtml(code) + '">' +
                        '<button type="button" class="btn btn-primary" data-dm-epp-copy-current="1">Copy Code</button>' +
                    '</div>' +
                    '<div class="dm-getepp-983-privacy-note"><span class="fa fa-info-circle" aria-hidden="true"></span> Keep this code private. It can be used to transfer your domain to another registrar.</div>' +
                '</div>';
            return;
        }

        cloneImportantAlerts(document, target);
        if (!target.children.length || /use the controls on this page/i.test(target.textContent || '')) {
            target.innerHTML = '<div class="dm-getepp-983-current-empty">Current EPP/Auth code will appear here after WHMCS returns it.</div>';
        }
    }

    function bindCopyButton(shell) {
        var btn = shell.querySelector('[data-dm-epp-copy-current]');
        var input = shell.querySelector('#dm-getepp-current-code-983');
        if (!btn || !input || btn.getAttribute('data-dm-copy-bound') === '1') {
            return;
        }
        btn.setAttribute('data-dm-copy-bound', '1');
        btn.addEventListener('click', function () {
            var value = input.value || '';
            input.focus();
            input.select();
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value).then(function () {
                    btn.textContent = 'Copied';
                    window.setTimeout(function () { btn.textContent = 'Copy Code'; }, 1500);
                }).catch(function () {
                    document.execCommand('copy');
                });
            } else {
                document.execCommand('copy');
            }
        });
    }

    function cloneImportantAlerts(root, target) {
        var nodes = (root || document).querySelectorAll('.alert, [class*="alert"], .successbox, .errorbox, .infobox');
        var i, text, key, clone;
        var seen = {};
        var appended = 0;
        target.innerHTML = '';
        for (i = 0; i < nodes.length; i += 1) {
            text = lowerText(nodes[i]);
            if (nodes[i].closest && nodes[i].closest('.dm-getepp-983-shell')) {
                continue;
            }
            if (text.indexOf('epp') === -1 && text.indexOf('auth') === -1 && text.indexOf('code') === -1) {
                continue;
            }
            key = text.replace(/\s+/g, ' ').trim();
            if (!key || seen[key]) {
                nodes[i].classList.add('dm-getepp-983-hidden');
                continue;
            }
            seen[key] = true;
            clone = nodes[i].cloneNode(true);
            clone.classList.add('dm-getepp-983-cloned-alert');
            target.appendChild(clone);
            appended += 1;
            nodes[i].classList.add('dm-getepp-983-hidden');
            if (appended >= 1) {
                break;
            }
        }
        if (!target.children.length) {
            target.innerHTML = '<div class="alert alert-info dm-getepp-983-cloned-alert">Use the controls on this page to review, generate, or create the EPP/Auth code for this domain.</div>';
        }
    }

    function hideOldMainContent(main, shell) {
        var children = Array.prototype.slice.call(main.children || []);
        children.forEach(function (child) {
            if (child === shell || child.id === 'dm-getepp-983-live') {
                return;
            }
            if (child.tagName && child.tagName.toLowerCase() === 'script') {
                return;
            }
            if (child.tagName && child.tagName.toLowerCase() === 'style') {
                return;
            }
            child.classList.add('dm-getepp-983-hidden');
        });
    }

    function preventManagerDoubleSubmit(shell) {
        var endpoint = '{$endpointAttr}';
        var forms = shell.querySelectorAll('form.dm-getepp-983-form');
        var i;
        for (i = 0; i < forms.length; i += 1) {
            forms[i].setAttribute('action', endpoint);
            forms[i].setAttribute('method', 'post');
            forms[i].addEventListener('submit', function (event) {
                var custom = this.querySelector('input[name="dm_epp_custom_code"]');
                if (custom && !custom.value.trim()) {
                    event.preventDefault();
                    custom.focus();
                    return false;
                }
                return true;
            }, true);
        }
    }

    function removeDuplicateEppPanels(shell) {
        var nodes = document.querySelectorAll('#dm-epp-side-controls-981, #dm-epp-controls-976, #dm-epp-controls-977, #dm-epp-controls-971, #dm-epp-controls-970, [data-dm-epp-side-controls], [data-dm-epp-controls]');
        var i;
        for (i = 0; i < nodes.length; i += 1) {
            if (shell.contains(nodes[i])) {
                continue;
            }
            if (nodes[i].parentNode) {
                nodes[i].parentNode.removeChild(nodes[i]);
            }
        }
    }

    function render() {
        var template = document.getElementById('dm-getepp-983-template');
        var main = findMainTarget();
        var existing = document.getElementById('dm-getepp-983-live');
        var shell;
        var codeTarget;

        if (!template || !main) {
            return;
        }

        hideSideColumns();
        main.classList.add('dm-getepp-983-main');

        if (existing) {
            shell = existing;
        } else {
            shell = template.content ? template.content.firstElementChild.cloneNode(true) : null;
            if (!shell) {
                var wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML;
                shell = wrapper.firstElementChild;
            }
            shell.id = 'dm-getepp-983-live';
            main.insertBefore(shell, main.firstChild);
        }

        codeTarget = shell.querySelector('[data-dm-code-target]');
        if (codeTarget) {
            renderCurrentCode(main, codeTarget);
        }

        preventManagerDoubleSubmit(shell);
        bindCopyButton(shell);
        removeDuplicateEppPanels(shell);
        hideOldMainContent(main, shell);
        document.body.classList.add('dm-getepp-983-ready');
    }

    ready(function () {
        render();
        window.setTimeout(render, 120);
        window.setTimeout(render, 450);
        window.setTimeout(render, 1100);
        window.setTimeout(render, 2500);
    });
}());
</script>
HTML;
});
