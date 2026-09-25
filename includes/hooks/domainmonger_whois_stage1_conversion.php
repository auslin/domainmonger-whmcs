<?php
/**
 * DomainMonger patch 906 + 1006 + 1151 + 1165 + 1166 + 1168: WHOIS Contact Info stage 3 role/action polish.
 *
 * Patch 1006: force active top domain-management menu button text to white.
 * Page-specific visual conversion and continued polish for the existing WHMCS
 * clientarea.php?action=domaincontacts page. This preserves the existing
 * WHOIS form, contact tabs, hidden fields, IRTP fields, same-contact behavior,
 * submit action, and save/cancel behavior. It only adds the no-side-menu
 * DomainMonger/ClouDNS-style layout around the already-rendered WHMCS form.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaFooterOutput', 999, function ($vars) {
    $scriptName = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = (string) ($_GET['action'] ?? '');

    if ($scriptName !== 'clientarea.php' || $action !== 'domaincontacts') {
        return '';
    }

    $clientId = 0;
    if (!empty($_SESSION['uid'])) {
        $clientId = (int) $_SESSION['uid'];
    } elseif (!empty($_SESSION['clientareauserid'])) {
        $clientId = (int) $_SESSION['clientareauserid'];
    }

    if ($clientId <= 0) {
        return '';
    }

    $domainId = 0;
    if (isset($_GET['domainid'])) {
        $domainId = (int) $_GET['domainid'];
    } elseif (isset($_GET['id'])) {
        $domainId = (int) $_GET['id'];
    }

    if ($domainId <= 0) {
        return '';
    }

    $domainName = '';
    try {
        $domainRow = Capsule::table('tbldomains')
            ->select('id', 'domain')
            ->where('userid', $clientId)
            ->where('id', $domainId)
            ->first();

        if (!$domainRow) {
            return '';
        }

        $domainId = (int) $domainRow->id;
        $domainName = (string) $domainRow->domain;
    } catch (\Throwable $e) {
        return '';
    }

    if ($domainName === '') {
        return '';
    }

    $baseClient = 'clientarea.php?action=domaindetails&id=' . $domainId;
    $menuItems = [
        ['key' => 'overview', 'label' => 'Overview', 'meta' => 'Domain', 'href' => $baseClient . '#tabOverview'],
        ['key' => 'autorenew', 'label' => 'Auto Renew', 'meta' => 'Billing', 'href' => $baseClient . '#tabAutorenew'],
        ['key' => 'nameservers', 'label' => 'Nameservers', 'meta' => 'DNS', 'href' => $baseClient . '#tabNameservers'],
        ['key' => 'reglock', 'label' => 'Registrar Lock', 'meta' => 'Security', 'href' => $baseClient . '#tabReglock'],
        ['key' => 'addons', 'label' => 'Addons', 'meta' => 'Options', 'href' => $baseClient . '#tabAddons'],
        ['key' => 'whois', 'label' => 'WHOIS Contact Info', 'meta' => 'Contacts', 'href' => 'clientarea.php?action=domaincontacts&domainid=' . $domainId],
        ['key' => 'privatens', 'label' => 'Private Nameservers', 'meta' => 'Hosts', 'href' => 'domainmanagement.php?action=childns&id=' . $domainId],
        ['key' => 'getepp', 'label' => 'Get EPP Code', 'meta' => 'Transfer', 'href' => 'clientarea.php?action=domaingetepp&domainid=' . $domainId],
    ];

    $menuHtml = '<ul class="dm-domain-section-menu" aria-label="Domain management sections">';
    foreach ($menuItems as $item) {
        $classes = 'dm-domain-section-link';
        if ($item['key'] === 'whois') {
            $classes .= ' dm-active';
        }
        $aria = $item['key'] === 'whois' ? ' aria-current="page"' : '';
        $menuHtml .= '<li><a class="' . $classes . '" href="' . htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') . '"' . $aria . '>'
            . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8')
            . '<span>' . htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8') . '</span></a></li>';
    }
    $menuHtml .= '</ul>';

    $domainJson = json_encode($domainName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $domainIdJson = json_encode((string) $domainId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $menuJson = json_encode($menuHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $overviewUrlJson = json_encode($baseClient . '#tabOverview', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $privateNsUrlJson = json_encode('domainmanagement.php?action=childns&id=' . $domainId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $eppUrlJson = json_encode('clientarea.php?action=domaingetepp&domainid=' . $domainId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<style>
body.whmcsbody.dm-whois-stage2 {
    --dm-navy: #163a5f;
    --dm-navy-hover: #214e7a;
    --dm-orange: #f58220;
    --dm-orange-soft: #d8741f;
    --dm-red: #b94a48;
    --dm-text: #293f56;
    --dm-muted: #60738a;
    --dm-border: rgba(17, 43, 77, 0.14);
    --dm-border-soft: rgba(17, 43, 77, 0.08);
    --dm-shadow: 0 2px 8px rgba(17, 43, 77, 0.055);
}
body.whmcsbody.dm-whois-stage2 .sidebar,
body.whmcsbody.dm-whois-stage2 .secondary-sidebar,
body.whmcsbody.dm-whois-stage2 .panel-sidebar,
body.whmcsbody.dm-whois-stage2 .dm-client-area-sidebar,
body.whmcsbody.dm-whois-stage2 aside,
body.whmcsbody.dm-whois-stage2 [class*="sidebar"] {
    display: none !important;
}
body.whmcsbody.dm-whois-stage2 #main-body .primary-content,
body.whmcsbody.dm-whois-stage2 #main-body .main-content,
body.whmcsbody.dm-whois-stage2 #main-body .col-md-9,
body.whmcsbody.dm-whois-stage2 #main-body .col-lg-9,
body.whmcsbody.dm-whois-stage2 #main-body .col-xl-9 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
}
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    display: flex;
    flex-wrap: nowrap;
    gap: 0;
    list-style: none;
    margin: 0 0 14px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 0;
    scrollbar-color: rgba(22, 58, 95, .28) transparent;
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
}
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu::-webkit-scrollbar { height: 8px; }
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu::-webkit-scrollbar-track { background: transparent; }
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu::-webkit-scrollbar-thumb {
    background: rgba(22, 58, 95, .24);
    border-radius: 999px;
}
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu li { margin: 0; }
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a {
    border-right: 1px solid var(--dm-border-soft);
    color: var(--dm-navy) !important;
    display: block;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.15;
    min-width: max-content;
    padding: 11px 13px;
    text-decoration: none !important;
    white-space: nowrap;
}
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a:hover,
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a:focus {
    background: #fff4eb;
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a:focus {
    box-shadow: inset 0 0 0 3px rgba(245, 130, 32, .22);
}
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a.dm-active,
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a.dm-active:hover,
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a.dm-active:focus,
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a[aria-current="page"],
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a[aria-current="page"]:hover,
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a[aria-current="page"]:focus {
    background: var(--dm-orange);
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a.dm-active *,
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a[aria-current="page"] *,
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a.dm-active span,
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a[aria-current="page"] span {
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
    opacity: .9;
}
body.whmcsbody.dm-whois-stage2 .dm-domain-section-menu a span {
    color: inherit;
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .04em;
    opacity: .78;
    text-transform: uppercase;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-workspace {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 18px;
    overflow: hidden;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-header {
    align-items: center;
    background: var(--dm-navy);
    color: #fff;
    display: flex;
    gap: 14px;
    justify-content: space-between;
    padding: 14px 16px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-header h3 {
    color: #fff !important;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-header span {
    color: rgba(255,255,255,.82);
    display: block;
    font-size: 12px;
    font-weight: 700;
    margin-top: 2px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-title-actions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-title-actions a,
body.whmcsbody.dm-whois-stage2 .dm-whois-btn {
    align-items: center;
    border-radius: 6px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    gap: 7px;
    justify-content: center;
    min-height: 34px;
    padding: 8px 11px;
    text-decoration: none !important;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-title-actions a {
    background: #fff;
    color: var(--dm-navy) !important;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-title-actions a:hover,
body.whmcsbody.dm-whois-stage2 .dm-whois-title-actions a:focus {
    background: #fff4eb;
    color: var(--dm-orange-soft) !important;
    outline: none;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-body {
    padding: 16px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-status-strip,
body.whmcsbody.dm-whois-stage2 .dm-whois-guide-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-status-strip > div,
body.whmcsbody.dm-whois-stage2 .dm-whois-guide-card,
body.whmcsbody.dm-whois-stage2 .dm-whois-note {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    padding: 11px 12px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-status-strip span,
body.whmcsbody.dm-whois-stage2 .dm-whois-guide-card span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-status-strip strong,
body.whmcsbody.dm-whois-stage2 .dm-whois-guide-card strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.3;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-guide-card p,
body.whmcsbody.dm-whois-stage2 .dm-whois-note p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 5px 0 0;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-note {
    background: #fff8f1;
    border-color: rgba(245, 130, 32, .22);
    margin-bottom: 14px;
}
/* Patch 1168: keep a clear visual break between the native same-contact
 * controls and the contact-specific information section below them. */
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .dm-whois-pane-intro {
    margin-top: 12px;
}
/* Patch 1165: the page-name bar and contact-role tabs are navigation
 * headers, not part of the white form card. Keep them visually outside the
 * content box, matching the other converted ResellerClub pages. */
body.whmcsbody.dm-whois-stage2 .dm-whois-form-card {
    background: transparent;
    border: 0;
    border-radius: 0;
    overflow: visible;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-form-head {
    align-items: flex-start;
    background: var(--dm-navy);
    border: 1px solid var(--dm-navy);
    border-radius: 8px;
    color: #fff;
    display: flex;
    flex-direction: column;
    gap: 2px;
    justify-content: flex-start;
    margin: 0 0 10px;
    padding: 11px 14px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-form-head strong {
    color: #fff;
    display: block;
    font-size: 15px;
    line-height: 1.25;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-form-head span {
    color: rgba(255,255,255,.78);
    display: block;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.3;
    margin-top: 2px;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification {
    background: transparent !important;
    padding: 0 !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification > .nav-tabs,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification > .responsive-tabs-sm {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    display: flex;
    flex-wrap: nowrap;
    gap: 0;
    margin: 0 0 10px;
    overflow-x: auto;
    padding: 0;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .nav-tabs .nav-item { margin: 0; }
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .nav-tabs .nav-link {
    border: 0 !important;
    border-radius: 0 !important;
    color: var(--dm-navy) !important;
    font-size: 13px;
    font-weight: 800;
    padding: 13px 14px;
    text-decoration: none !important;
    white-space: nowrap;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .nav-tabs .nav-link:hover,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .nav-tabs .nav-link:focus {
    background: #fff4eb;
    color: var(--dm-orange-soft) !important;
    outline: none;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .nav-tabs .nav-link.active {
    background: var(--dm-orange) !important;
    color: #fff !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .responsive-tabs-sm-connector {
    display: none !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .tab-content {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    padding: 16px !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .tab-pane {
    background: #fff;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .form-check,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .radio,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .checkbox-inline,
body.whmcsbody.dm-whois-stage2 .dm-domaincontacts-same-contact-wrap {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    margin: 0 0 10px;
    padding: 10px 12px;
}
/* Patch 1203: add breathing room below only the same-contact helper block. */
body.whmcsbody.dm-whois-stage2 .dm-domaincontacts-same-contact-wrap {
    margin-bottom: 18px !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification label {
    color: var(--dm-text);
    font-size: 13px;
    font-weight: 700;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .form-control,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification select,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification input[type="text"] {
    border: 1px solid rgba(17, 43, 77, .2) !important;
    border-radius: 6px !important;
    min-height: 38px;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .form-control:focus,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification select:focus,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification input[type="text"]:focus {
    border-color: var(--dm-orange) !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .18) !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification input[type="radio"] {
    accent-color: var(--dm-orange);
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .btn-primary,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification button[type="submit"] {
    background: var(--dm-orange) !important;
    border-color: var(--dm-orange) !important;
    color: #fff !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .btn-primary:hover,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification button[type="submit"]:hover {
    background: var(--dm-orange-soft) !important;
    border-color: var(--dm-orange-soft) !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .btn-default,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification button[type="reset"] {
    background: var(--dm-navy) !important;
    border-color: var(--dm-navy) !important;
    color: #fff !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .btn-default:hover,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification button[type="reset"]:hover {
    background: var(--dm-navy-hover) !important;
    border-color: var(--dm-navy-hover) !important;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-save-note {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 14px 0 0;
    padding: 11px 12px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-original-title,
body.whmcsbody.dm-whois-stage2 .dm-whois-original-warning {
    display: none !important;
}

body.whmcsbody.dm-whois-stage2 .dm-whois-review-panel {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(17, 43, 77, 0.04);
    margin: 0 0 14px;
    padding: 12px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-review-head {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: space-between;
    margin-bottom: 10px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-review-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-review-head span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    margin-top: 2px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-contact-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-chip {
    align-items: center;
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 999px;
    color: var(--dm-navy);
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    min-height: 28px;
    padding: 5px 9px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-chip.dm-active {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-chip.dm-warning {
    background: #fff8f1;
    border-color: rgba(245, 130, 32, .24);
    color: var(--dm-orange-soft);
}
body.whmcsbody.dm-whois-stage2 .dm-whois-chip.dm-clean {
    background: #f4f8fb;
    color: var(--dm-muted);
}
body.whmcsbody.dm-whois-stage2 .dm-whois-mini-checklist {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-top: 10px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-mini-checklist div {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.4;
    padding: 9px 10px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-mini-checklist strong {
    color: var(--dm-navy);
    display: block;
    font-size: 12px;
    margin-bottom: 2px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-tab-note {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 0 0 12px;
    padding: 10px 12px;
}


body.whmcsbody.dm-whois-stage2 .dm-whois-role-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-role-card {
    background: #fff;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    padding: 10px 11px;
    position: relative;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-role-card:before {
    background: rgba(22, 58, 95, .18);
    border-radius: 999px;
    content: '';
    display: block;
    height: 4px;
    margin: 0 0 8px;
    width: 36px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-role-card.dm-current {
    background: #fff8f1;
    border-color: rgba(245, 130, 32, .36);
    box-shadow: inset 0 0 0 1px rgba(245, 130, 32, .08);
}
body.whmcsbody.dm-whois-stage2 .dm-whois-role-card.dm-current:before {
    background: var(--dm-orange);
}
body.whmcsbody.dm-whois-stage2 .dm-whois-role-card span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-role-card strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-role-card p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.4;
    margin: 5px 0 0;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-role-card .dm-whois-role-badge {
    background: #f4f8fb;
    border: 1px solid var(--dm-border-soft);
    border-radius: 999px;
    color: var(--dm-muted);
    display: inline-flex;
    font-size: 10px;
    font-weight: 900;
    line-height: 1;
    margin-top: 8px;
    padding: 5px 7px;
    text-transform: uppercase;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-role-card.dm-current .dm-whois-role-badge {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-action-guidance {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    color: var(--dm-muted);
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: space-between;
    margin: 14px 16px 0;
    padding: 11px 12px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-action-guidance strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    margin-bottom: 2px;
}
body.whmcsbody.dm-whois-stage2 .dm-whois-action-guidance span {
    display: block;
    font-size: 12px;
    line-height: 1.4;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .dm-whois-action-controls,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification p.text-center:last-child {
    background: #fff;
    border-top: 1px solid var(--dm-border-soft);
    margin: 14px 0 0 !important;
    padding: 14px 16px !important;
}
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification .dm-whois-action-controls .btn,
body.whmcsbody.dm-whois-stage2 #frmDomainContactModification p.text-center:last-child .btn {
    border-radius: 6px !important;
    font-weight: 800 !important;
    min-height: 38px;
    padding-left: 18px !important;
    padding-right: 18px !important;
}

@media (max-width: 767px) {
    body.whmcsbody.dm-whois-stage2 .dm-whois-header,
    body.whmcsbody.dm-whois-stage2 .dm-whois-form-head {
        align-items: flex-start;
        flex-direction: column;
    }
    body.whmcsbody.dm-whois-stage2 .dm-whois-status-strip,
    body.whmcsbody.dm-whois-stage2 .dm-whois-guide-grid,
    body.whmcsbody.dm-whois-stage2 .dm-whois-mini-checklist,
    body.whmcsbody.dm-whois-stage2 .dm-whois-role-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<script>
(function () {
    var domainName = {$domainJson};
    var domainId = {$domainIdJson};
    var menuHtml = {$menuJson};
    var overviewUrl = {$overviewUrlJson};
    var privateNsUrl = {$privateNsUrlJson};
    var eppUrl = {$eppUrlJson};

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function findMainArea(form) {
        return form.closest('.main-content') || form.closest('.primary-content') || form.closest('#main-body') || form.parentElement;
    }

    function hideOriginalHeadings(mainArea) {
        var headings = mainArea.querySelectorAll('h1, h2, h3.card-title, .card-title');
        headings.forEach(function (heading) {
            var text = (heading.textContent || '').trim().toLowerCase();
            if (text.indexOf('contact') !== -1 || text.indexOf('whois') !== -1 || text.indexOf(domainName.toLowerCase()) !== -1) {
                heading.classList.add('dm-whois-original-title');
            }
        });
    }

    function hideOriginalWarning(form) {
        var warning = form.previousElementSibling;
        while (warning && warning.nodeType === 3) {
            warning = warning.previousElementSibling;
        }
        if (warning && warning.tagName && warning.tagName.toLowerCase() === 'p') {
            warning.classList.add('dm-whois-original-warning');
        }
        var parent = form.parentElement;
        if (parent) {
            Array.prototype.slice.call(parent.children).forEach(function (child) {
                if (child.tagName && child.tagName.toLowerCase() === 'p' && (child.textContent || '').toLowerCase().indexOf('whois') !== -1) {
                    child.classList.add('dm-whois-original-warning');
                }
            });
        }
    }

    function enhanceContactTabs(form) {
        var tabs = form.querySelectorAll('.nav-tabs .nav-link, [data-toggle="tab"]');
        tabs.forEach(function (tab) {
            var text = (tab.textContent || '').trim();
            if (/^administrative$/i.test(text)) {
                tab.textContent = 'Admin';
            }
        });

        var tabPanes = form.querySelectorAll('.tab-pane');
        tabPanes.forEach(function (pane) {
            if (pane.querySelector('.dm-whois-pane-intro')) {
                return;
            }
            var id = (pane.id || '').replace(/^tab/, '') || 'Contact';
            var label = id.replace(/administrative/i, 'Admin');
            var intro = document.createElement('div');
            intro.className = 'dm-whois-note dm-whois-pane-intro';
            intro.innerHTML = '<strong>' + label + ' contact</strong><p>Review this contact carefully before saving. Changes to Registrant first name, last name, or email may require verification.</p>';
            pane.insertBefore(intro, pane.firstChild);
        });
    }


    function normalizeRoleLabel(label) {
        label = (label || '').replace(/\s+/g, ' ').trim();
        if (/administrative/i.test(label) || /^admin$/i.test(label)) {
            return 'Admin';
        }
        if (/registrant/i.test(label)) {
            return 'Registrant';
        }
        if (/billing/i.test(label)) {
            return 'Billing';
        }
        if (/technical/i.test(label)) {
            return 'Technical';
        }
        return label || 'Contact';
    }

    function addRoleGrid(workspace, form) {
        var body = workspace.querySelector('.dm-whois-body');
        if (!body || body.querySelector('.dm-whois-role-grid')) {
            return;
        }
        var roleGrid = document.createElement('div');
        roleGrid.className = 'dm-whois-role-grid';
        roleGrid.innerHTML = ''
            + '<div class="dm-whois-role-card" data-dm-role-card="Registrant"><span>Registrant</span><strong>Ownership contact</strong><p>Changing first name, last name, or email may require verification.</p><em class="dm-whois-role-badge">Review</em></div>'
            + '<div class="dm-whois-role-card" data-dm-role-card="Admin"><span>Admin</span><strong>Administrative contact</strong><p>Used for administrative domain notices and registrar messages.</p><em class="dm-whois-role-badge">Review</em></div>'
            + '<div class="dm-whois-role-card" data-dm-role-card="Billing"><span>Billing</span><strong>Billing contact</strong><p>Keep billing details current for renewal and account-related notices.</p><em class="dm-whois-role-badge">Review</em></div>'
            + '<div class="dm-whois-role-card" data-dm-role-card="Technical"><span>Technical</span><strong>Technical contact</strong><p>Used for technical domain and DNS-related notices.</p><em class="dm-whois-role-badge">Review</em></div>';
        var formCard = body.querySelector('.dm-whois-form-card');
        if (formCard) {
            body.insertBefore(roleGrid, formCard);
        } else {
            body.appendChild(roleGrid);
        }
        updateRoleGrid(form);
    }

    function updateRoleGrid(form) {
        var label = normalizeRoleLabel(getActiveTabLabel(form));
        document.querySelectorAll('.dm-whois-role-card').forEach(function (card) {
            var isCurrent = (card.getAttribute('data-dm-role-card') === label);
            card.classList.toggle('dm-current', isCurrent);
            var badge = card.querySelector('.dm-whois-role-badge');
            if (badge) {
                badge.textContent = isCurrent ? 'Current tab' : 'Review';
            }
        });
    }

    function addActionGuidance(form) {
        if (form.querySelector('.dm-whois-action-guidance')) {
            return;
        }
        var controls = form.querySelector('p.text-center') || form.querySelector('.text-center:last-child') || form.lastElementChild;
        if (!controls) {
            return;
        }
        controls.classList.add('dm-whois-action-controls');
        var guide = document.createElement('div');
        guide.className = 'dm-whois-action-guidance';
        guide.innerHTML = ''
            + '<div><strong>Save all contact tabs together</strong><span>The existing WHMCS save button submits the complete WHOIS contact form.</span></div>'
            + '<div><strong>Verification can delay visible updates</strong><span>Registrant name or email changes may not appear until all required links are clicked.</span></div>';
        controls.insertAdjacentElement('beforebegin', guide);
    }

    function addFormWrapper(form) {
        if (form.closest('.dm-whois-form-card')) {
            return;
        }
        var wrapper = document.createElement('div');
        wrapper.className = 'dm-whois-form-card';
        var head = document.createElement('div');
        head.className = 'dm-whois-form-head';
        head.innerHTML = '<strong>WHOIS Contact Info</strong><span>Registrant, Admin, Billing & Technical</span>';
        form.parentNode.insertBefore(wrapper, form);
        wrapper.appendChild(head);
        wrapper.appendChild(form);

        // Patch 1166: the native WHOIS page is itself wrapped in a WHMCS
        // white card. Move the page-name header outside that outer card so
        // the final order matches the other converted pages:
        // unified menu -> page-name header -> white form card.
        var outerCard = wrapper.parentElement && wrapper.parentElement.closest
            ? wrapper.parentElement.closest('.card, .panel')
            : null;
        if (outerCard && outerCard !== wrapper && outerCard.parentNode) {
            outerCard.parentNode.insertBefore(head, outerCard);
            head.setAttribute('data-dm-whois-external-header', '1');
        } else if (wrapper.parentNode) {
            wrapper.parentNode.insertBefore(head, wrapper);
            head.setAttribute('data-dm-whois-external-header', '1');
        }
    }

    function addSaveNote(form) {
        if (form.querySelector('.dm-whois-save-note')) {
            return;
        }
        var controls = form.querySelector('p.text-center') || form.querySelector('.text-center:last-child') || form.lastElementChild;
        if (!controls) {
            return;
        }
        var note = document.createElement('div');
        note.className = 'dm-whois-save-note';
        note.innerHTML = '<strong>After saving:</strong> verification may be required for Registrant name or email changes. Check all required verification emails before making another update.';
        controls.insertAdjacentElement('beforebegin', note);
    }


    function addReviewPanel(workspace, form) {
        var body = workspace.querySelector('.dm-whois-body');
        if (!body || body.querySelector('.dm-whois-review-panel')) {
            return;
        }
        var panel = document.createElement('div');
        panel.className = 'dm-whois-review-panel';
        panel.innerHTML = ''
            + '<div class="dm-whois-review-head">'
            + '  <div><strong>Contact update review</strong><span>Use each contact tab below, then save once all updates are correct.</span></div>'
            + '  <div class="dm-whois-contact-chips" aria-label="WHOIS contact update status">'
            + '    <span class="dm-whois-chip dm-active" data-dm-chip="active">Viewing: Contact</span>'
            + '    <span class="dm-whois-chip dm-clean" data-dm-chip="changes">No unsaved changes detected</span>'
            + '  </div>'
            + '</div>'
            + '<div class="dm-whois-mini-checklist">'
            + '  <div><strong>Review all tabs</strong>Registrant, Admin, Billing, and Technical can each hold different contact details.</div>'
            + '  <div><strong>Verification may be required</strong>Registrant name or email updates may not show until verification links are clicked.</div>'
            + '  <div><strong>Save once ready</strong>The existing WHMCS save action and registrar verification flow are unchanged.</div>'
            + '</div>';
        var formCard = body.querySelector('.dm-whois-form-card');
        if (formCard) {
            body.insertBefore(panel, formCard);
        } else {
            body.appendChild(panel);
        }
        updateReviewPanel(form);
    }

    function getActiveTabLabel(form) {
        var active = form.querySelector('.nav-tabs .nav-link.active, [data-toggle="tab"].active');
        if (!active) {
            active = form.querySelector('.nav-tabs .nav-link, [data-toggle="tab"]');
        }
        var label = active ? (active.textContent || '').trim() : 'Contact';
        label = label.replace(/\s+/g, ' ');
        if (/^administrative$/i.test(label)) {
            label = 'Admin';
        }
        return label || 'Contact';
    }

    function updateReviewPanel(form) {
        var panel = document.querySelector('.dm-whois-review-panel');
        if (!panel) {
            return;
        }
        var activeChip = panel.querySelector('[data-dm-chip="active"]');
        if (activeChip) {
            activeChip.textContent = 'Viewing: ' + getActiveTabLabel(form);
        }
        updateRoleGrid(form);
    }

    function watchContactForm(form) {
        if (form.dataset.dmWhoisStage2Watching === '1') {
            return;
        }
        form.dataset.dmWhoisStage2Watching = '1';

        form.addEventListener('input', function () {
            var changesChip = document.querySelector('[data-dm-chip="changes"]');
            if (changesChip) {
                changesChip.classList.remove('dm-clean');
                changesChip.classList.add('dm-warning');
                changesChip.textContent = 'Unsaved changes';
            }
        }, true);
        form.addEventListener('change', function () {
            var changesChip = document.querySelector('[data-dm-chip="changes"]');
            if (changesChip) {
                changesChip.classList.remove('dm-clean');
                changesChip.classList.add('dm-warning');
                changesChip.textContent = 'Unsaved changes';
            }
            updateReviewPanel(form);
        }, true);
        form.addEventListener('click', function (event) {
            if (event.target && (event.target.matches('.nav-link') || event.target.closest('.nav-link'))) {
                window.setTimeout(function () { updateReviewPanel(form); }, 60);
            }
        }, true);
        document.addEventListener('shown.bs.tab', function () {
            updateReviewPanel(form);
        });
    }

    function buildWorkspace(form) {
        var mainArea = findMainArea(form);
        if (!mainArea || form.getAttribute('data-dm-whois-stage2-built') === '1') {
            return;
        }
        form.setAttribute('data-dm-whois-stage2-built', '1');

        hideOriginalHeadings(mainArea);
        hideOriginalWarning(form);
        addFormWrapper(form);
        enhanceContactTabs(form);
        addSaveNote(form);
        addActionGuidance(form);

        // Patch 1151: the large conversion scaffold above the working WHOIS
        // form was useful while this page was being built, but is now redundant.
        // Keep the shared horizontal domain menu and the real WHMCS form only.
        var formCard = form.closest('.dm-whois-form-card');
        if (formCard && formCard.parentNode && !mainArea.querySelector('.dm-domain-section-menu')) {
            var menuContainer = document.createElement('div');
            menuContainer.innerHTML = menuHtml;
            var menu = menuContainer.firstElementChild;
            if (menu) {
                formCard.parentNode.insertBefore(menu, formCard);
            }
        }
    }

    ready(function () {
        var form = document.getElementById('frmDomainContactModification');
        if (!form) {
            return;
        }
        document.body.classList.add('dm-whois-stage2');
        buildWorkspace(form);
        watchContactForm(form);
        updateReviewPanel(form);
    });
})();
</script>
HTML;
});
