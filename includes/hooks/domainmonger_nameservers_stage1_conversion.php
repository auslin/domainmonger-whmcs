<?php
/**
 * DomainMonger patch 896: Addons stage 2 polish for the converted domain-management section.
 *
 * This is a page-specific visual conversion for the existing WHMCS
 * clientarea.php?action=domaindetails Nameservers, Auto Renew, Registrar Lock, and Addons tabs. It preserves the
 * existing nameserver form, action, hidden fields, radio inputs, and submit
 * behavior. It only adds the no-side-menu layout/navigation and ClouDNS-style
 * visual treatment around the existing rendered markup.
 * Patch 1103 update:
 * - DNS Management action links now use dnsmanagement.php so the ResellerClub URL
 *   remains visible while the fast native DNS feed powers the table.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaFooterOutput', 999, function ($vars) {
    $scriptName = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = (string) ($_GET['action'] ?? '');

    if ($scriptName !== 'clientarea.php' || $action !== 'domaindetails') {
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
    if (isset($_GET['id'])) {
        $domainId = (int) $_GET['id'];
    } elseif (isset($_GET['domainid'])) {
        $domainId = (int) $_GET['domainid'];
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
        ['key' => 'overview', 'label' => 'Overview', 'meta' => 'Domain', 'href' => $baseClient . '#tabOverview', 'target' => 'tabOverview'],
        ['key' => 'autorenew', 'label' => 'Auto Renew', 'meta' => 'Billing', 'href' => $baseClient . '#tabAutorenew', 'target' => 'tabAutorenew'],
        ['key' => 'nameservers', 'label' => 'Nameservers', 'meta' => 'Active', 'href' => $baseClient . '#tabNameservers', 'target' => 'tabNameservers'],
        ['key' => 'reglock', 'label' => 'Registrar Lock', 'meta' => 'Security', 'href' => $baseClient . '#tabReglock', 'target' => 'tabReglock'],
        ['key' => 'addons', 'label' => 'Addons', 'meta' => 'Options', 'href' => $baseClient . '#tabAddons', 'target' => 'tabAddons'],
        ['key' => 'whois', 'label' => 'WHOIS Contact Info', 'meta' => 'Contacts', 'href' => 'clientarea.php?action=domaincontacts&domainid=' . $domainId, 'target' => ''],
        ['key' => 'privatens', 'label' => 'Private Nameservers', 'meta' => 'Hosts', 'href' => 'domainmanagement.php?action=childns&id=' . $domainId, 'target' => ''],
        ['key' => 'getepp', 'label' => 'Get EPP Code', 'meta' => 'Transfer', 'href' => 'clientarea.php?action=domaingetepp&domainid=' . $domainId, 'target' => ''],
    ];

    $menuHtml = '<ul class="dm-domain-section-menu" aria-label="Domain management sections">';
    foreach ($menuItems as $item) {
        $classes = 'dm-domain-section-link';
        if ($item['key'] === 'nameservers') {
            $classes .= ' dm-active';
        }
        $targetAttr = $item['target'] !== '' ? ' data-dm-tab-target="' . htmlspecialchars($item['target'], ENT_QUOTES, 'UTF-8') . '"' : '';
        $aria = $item['key'] === 'nameservers' ? ' aria-current="page"' : '';
        $menuHtml .= '<li><a class="' . $classes . '" href="' . htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') . '"' . $targetAttr . $aria . '>'
            . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8')
            . '<span>' . htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8') . '</span></a></li>';
    }
    $menuHtml .= '</ul>';

    $domainJson = json_encode($domainName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $domainIdJson = json_encode((string) $domainId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $menuJson = json_encode($menuHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<style>
body.whmcsbody.dm-nameservers-stage1 {
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
body.whmcsbody.dm-nameservers-stage1 .sidebar,
body.whmcsbody.dm-nameservers-stage1 .secondary-sidebar,
body.whmcsbody.dm-nameservers-stage1 .panel-sidebar,
body.whmcsbody.dm-nameservers-stage1 .dm-client-area-sidebar,
body.whmcsbody.dm-nameservers-stage1 aside,
body.whmcsbody.dm-nameservers-stage1 [class*="sidebar"] {
    display: none !important;
}
body.whmcsbody.dm-nameservers-stage1 #main-body .primary-content,
body.whmcsbody.dm-nameservers-stage1 #main-body .main-content,
body.whmcsbody.dm-nameservers-stage1 #main-body .col-md-9,
body.whmcsbody.dm-nameservers-stage1 #main-body .col-lg-9,
body.whmcsbody.dm-nameservers-stage1 #main-body .col-xl-9 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu {
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
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu::-webkit-scrollbar { height: 8px; }
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu::-webkit-scrollbar-track { background: transparent; }
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu::-webkit-scrollbar-thumb {
    background: rgba(22, 58, 95, .24);
    border-radius: 999px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu li { margin: 0; }
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu a {
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
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu a:hover,
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu a:focus {
    background: #fff4eb;
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu a:focus {
    box-shadow: inset 0 0 0 3px rgba(245, 130, 32, .22);
}
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu a.dm-active {
    background: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu a span {
    color: inherit;
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .04em;
    opacity: .78;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers.dm-nameservers-converted > .card {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    overflow: hidden;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers.dm-nameservers-converted > .card > .card-body {
    padding: 0 !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-card-header {
    align-items: center;
    background: var(--dm-navy);
    color: #fff;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 12px 16px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-card-header h3 {
    color: #fff !important;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-card-header span {
    color: rgba(255,255,255,.82);
    font-size: 12px;
    font-weight: 700;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-content {
    padding: 16px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-info-card {
    align-items: flex-start;
    background: #eef5fc;
    border: 1px solid rgba(22, 58, 95, .15);
    border-left: 4px solid var(--dm-navy);
    border-radius: 8px;
    color: var(--dm-text);
    display: flex;
    gap: 12px;
    margin-bottom: 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-info-icon {
    align-items: center;
    background: var(--dm-navy);
    border-radius: 50%;
    color: #fff;
    display: inline-flex;
    flex: 0 0 22px;
    font-size: 13px;
    font-weight: 800;
    height: 22px;
    justify-content: center;
    margin-top: 1px;
    width: 22px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-info-card strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 2px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-info-card p {
    margin: 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-quickbar {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-quickitem {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    padding: 10px 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-quickitem span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 2px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-quickitem strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers .alert-info {
    background: #eef5fc !important;
    border-color: rgba(22, 58, 95, .18) !important;
    color: var(--dm-text) !important;
    margin-bottom: 14px;
    text-align: left !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-choice-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin: 0 0 16px;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers .form-check {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 !important;
    padding: 12px 14px !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers .form-check label {
    color: var(--dm-text);
    cursor: pointer;
    font-weight: 700;
    margin: 0;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers input[type="radio"] {
    accent-color: var(--dm-orange);
    margin-right: 8px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-form-grid {
    background: #fbfdff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    padding: 14px;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers .form-group.row {
    align-items: center;
    margin-bottom: 10px;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers .col-form-label {
    color: var(--dm-navy);
    font-weight: 700;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers .form-control {
    border-color: rgba(17, 43, 77, .22) !important;
    border-radius: 6px !important;
    min-height: 40px;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers .form-control:focus {
    border-color: var(--dm-orange) !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .18) !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers .btn-primary,
body.whmcsbody.dm-nameservers-stage1 #tabNameservers button[type="submit"] {
    background: var(--dm-orange) !important;
    border-color: var(--dm-orange) !important;
    border-radius: 6px !important;
    color: #fff !important;
    font-weight: 700 !important;
    padding: 9px 16px;
}
body.whmcsbody.dm-nameservers-stage1 #tabNameservers .btn-primary:hover,
body.whmcsbody.dm-nameservers-stage1 #tabNameservers button[type="submit"]:hover {
    background: var(--dm-orange-soft) !important;
    border-color: var(--dm-orange-soft) !important;
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-post-note {
    background: #fff8f1;
    border: 1px solid rgba(245, 130, 32, .22);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    color: var(--dm-text);
    margin-top: 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-post-note strong {
    color: var(--dm-navy);
}

body.whmcsbody.dm-nameservers-stage1 .dm-ns-title-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-action-btn {
    align-items: center;
    border-radius: 6px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    line-height: 1.2;
    padding: 8px 11px;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-action-btn.dm-primary {
    background: var(--dm-orange);
    border: 1px solid var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-action-btn.dm-secondary {
    background: var(--dm-navy);
    border: 1px solid var(--dm-navy);
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-action-btn:hover,
body.whmcsbody.dm-nameservers-stage1 .dm-ns-action-btn:focus {
    background: var(--dm-navy-hover);
    border-color: var(--dm-navy-hover);
    color: #fff !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-status-strip {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-status-chip {
    background: #fbfdff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    padding: 9px 11px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-status-chip span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 2px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-status-chip strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-choice-note {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.35;
    margin-top: 6px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-field-help {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
    margin-top: 5px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-label-badge {
    background: #fff4eb;
    border: 1px solid rgba(245, 130, 32, .24);
    border-radius: 999px;
    color: var(--dm-orange-soft);
    display: inline-block;
    font-size: 10px;
    font-weight: 800;
    line-height: 1;
    margin-left: 6px;
    padding: 4px 6px;
    text-transform: uppercase;
    vertical-align: middle;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-form-caption {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    color: var(--dm-text);
    margin-bottom: 12px;
    padding: 10px 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-form-caption strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 2px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-form-caption p {
    margin: 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-bottom-guide {
    background: #f7fafd;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    color: var(--dm-text);
    margin-top: 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-bottom-guide strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 4px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-bottom-guide p {
    margin: 0;
}


body.whmcsbody.dm-nameservers-stage1 .dm-ns-choice-grid .form-check.dm-selected-choice {
    background: #fff8f1;
    border-color: rgba(245, 130, 32, .55);
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .12), var(--dm-shadow);
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-selected-badge {
    background: var(--dm-orange);
    border-radius: 999px;
    color: #fff;
    display: inline-flex;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .04em;
    line-height: 1;
    margin-left: 8px;
    padding: 4px 7px;
    text-transform: uppercase;
    vertical-align: middle;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-review-strip {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-navy);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
    padding: 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-review-item span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-review-item strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-current-mode {
    background: #f7fafd;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    color: var(--dm-text);
    margin: 0 0 12px;
    padding: 10px 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-current-mode strong {
    color: var(--dm-navy);
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-custom-count-note {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.35;
    margin-top: 8px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-custom-count-note.dm-warning {
    color: #8a5a1f;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-review {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    margin: 0 0 14px;
    padding: 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-review-header {
    align-items: flex-start;
    display: flex;
    gap: 10px;
    justify-content: space-between;
    margin-bottom: 10px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-review-header strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-review-header span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
    margin-top: 2px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-review-mode {
    background: #fff4eb;
    border: 1px solid rgba(245, 130, 32, .24);
    border-radius: 999px;
    color: var(--dm-orange-soft);
    flex: 0 0 auto;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
    padding: 5px 8px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-list {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-chip {
    align-items: center;
    background: #f7fafd;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    display: flex;
    gap: 8px;
    min-width: 0;
    padding: 8px 10px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-chip span {
    background: var(--dm-navy);
    border-radius: 999px;
    color: #fff;
    flex: 0 0 auto;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .04em;
    padding: 4px 6px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-chip strong {
    color: var(--dm-text);
    display: block;
    font-size: 13px;
    line-height: 1.25;
    min-width: 0;
    overflow-wrap: anywhere;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-empty {
    background: #f7fafd;
    border: 1px dashed rgba(22, 58, 95, .25);
    border-radius: 8px;
    color: var(--dm-muted);
    font-size: 13px;
    line-height: 1.4;
    padding: 10px 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-save-checklist {
    background: #fbfdff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    color: var(--dm-text);
    margin-top: 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-save-checklist strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 6px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-save-checklist ul {
    margin: 0;
    padding-left: 18px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-save-checklist li {
    margin: 3px 0;
}


body.whmcsbody.dm-nameservers-stage1 .dm-ns-action-panel {
    align-items: center;
    background: #fff8f1;
    border: 1px solid rgba(245, 130, 32, .24);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 14px;
    padding: 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-action-panel strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-action-panel span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
    margin-top: 2px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-save-state {
    background: #fff;
    border: 1px solid rgba(22, 58, 95, .18);
    border-radius: 999px;
    color: var(--dm-navy);
    flex: 0 0 auto;
    font-size: 10px;
    font-style: normal;
    font-weight: 900;
    letter-spacing: .05em;
    padding: 6px 9px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-save-state.dm-dirty {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-field-status {
    align-items: center;
    border-radius: 999px;
    display: inline-flex;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .04em;
    margin-top: 6px;
    padding: 4px 7px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-field-status.dm-empty {
    background: #eef3f8;
    border: 1px solid rgba(22, 58, 95, .14);
    color: var(--dm-muted);
}
body.whmcsbody.dm-nameservers-stage1 .dm-ns-field-status.dm-filled {
    background: #fff4eb;
    border: 1px solid rgba(245, 130, 32, .26);
    color: var(--dm-orange-soft);
}


body.whmcsbody.dm-nameservers-stage1 #tabAutorenew.dm-autorenew-converted > .card {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    overflow: hidden;
}
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew.dm-autorenew-converted > .card > .card-body {
    padding: 0 !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-card-header {
    align-items: center;
    background: var(--dm-navy);
    color: #fff;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 12px 16px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-card-header h3 {
    color: #fff !important;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-card-header span {
    color: rgba(255,255,255,.82);
    font-size: 12px;
    font-weight: 700;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-content {
    padding: 16px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-title-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-action-btn {
    align-items: center;
    border-radius: 6px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    line-height: 1.2;
    padding: 8px 11px;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-action-btn.dm-primary {
    background: var(--dm-orange);
    border: 1px solid var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-action-btn.dm-secondary {
    background: var(--dm-navy);
    border: 1px solid var(--dm-navy);
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-action-btn:hover,
body.whmcsbody.dm-nameservers-stage1 .dm-ar-action-btn:focus {
    background: var(--dm-navy-hover);
    border-color: var(--dm-navy-hover);
    color: #fff !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-panel {
    align-items: center;
    background: #fbfdff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    display: flex;
    gap: 14px;
    justify-content: space-between;
    margin: 0 0 14px;
    padding: 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-panel strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-panel span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
    margin-top: 3px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-badge {
    border-radius: 999px;
    color: #fff;
    flex: 0 0 auto;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .05em;
    padding: 7px 10px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-badge.dm-enabled { background: var(--dm-orange); }
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-badge.dm-disabled { background: #8c98a6; }
body.whmcsbody.dm-nameservers-stage1 .dm-ar-guide-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-guide-card {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    padding: 10px 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-guide-card span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-guide-card strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew .alert,
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew .alert-success {
    margin-bottom: 14px;
    text-align: left !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew form {
    background: #fff8f1;
    border: 1px solid rgba(245, 130, 32, .24);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0;
    padding: 14px;
}
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew form p.text-center {
    margin: 0;
    text-align: left !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew form .btn,
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew form button[type="submit"] {
    border-radius: 6px !important;
    color: #fff !important;
    font-weight: 800 !important;
    padding: 9px 16px !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew form .btn-success,
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew form .btn-primary {
    background: var(--dm-orange) !important;
    border-color: var(--dm-orange) !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew form .btn-success:hover,
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew form .btn-primary:hover {
    background: var(--dm-orange-soft) !important;
    border-color: var(--dm-orange-soft) !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew form .btn-danger {
    background: var(--dm-red) !important;
    border-color: var(--dm-red) !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabAutorenew .dm-ar-action-note {
    color: var(--dm-text);
    margin: 0 0 10px;
}

body.whmcsbody.dm-nameservers-stage1 .dm-ar-renewal-grid,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-guide-grid,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-strip {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-renewal-card {
    background: #fbfdff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    padding: 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-renewal-card span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-renewal-card strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-checklist-panel {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    margin: 0 0 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-checklist-panel strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 6px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-checklist-panel ul {
    margin: 0;
    padding-left: 18px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-checklist-panel li {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 3px 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-current-action {
    background: #eef5fc;
    border: 1px solid rgba(22, 58, 95, .15);
    border-left: 4px solid var(--dm-navy);
    border-radius: 8px;
    color: var(--dm-text);
    margin: 0 0 12px;
    padding: 11px 13px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-current-action strong {
    color: var(--dm-navy);
}

body.whmcsbody.dm-nameservers-stage1 .dm-ar-final-note {
    background: #eef5fc;
    border: 1px solid rgba(22, 58, 95, .15);
    border-left: 4px solid var(--dm-navy);
    border-radius: 8px;
    color: var(--dm-text);
    margin-top: 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-final-note strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 3px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-strip {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-chip {
    background: #fbfdff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    padding: 10px 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-chip span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-chip strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-save-panel,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-card-header,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-panel,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-panel {
    align-items: center;
    background: #fff8f1;
    border: 1px solid rgba(245, 130, 32, .24);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    color: var(--dm-text);
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 12px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-save-panel strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-save-panel span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
    margin-top: 3px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-ar-save-panel em {
    background: var(--dm-navy);
    border-radius: 999px;
    color: #fff;
    flex: 0 0 auto;
    font-size: 10px;
    font-style: normal;
    font-weight: 900;
    letter-spacing: .05em;
    padding: 6px 9px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-domain-section-menu a[aria-current="page"] {
    background: var(--dm-orange);
    color: #fff !important;
}


body.whmcsbody.dm-nameservers-stage1 #tabReglock.dm-reglock-converted > .card {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    overflow: hidden;
}
body.whmcsbody.dm-nameservers-stage1 #tabReglock.dm-reglock-converted > .card > .card-body {
    padding: 0 !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-content {
    padding: 16px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-card-header {
    align-items: center;
    background: var(--dm-navy);
    color: #fff;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 12px 16px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-card-header h3 {
    color: #fff !important;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-card-header span {
    color: rgba(255,255,255,.82);
    font-size: 12px;
    font-weight: 700;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-title-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-btn {
    align-items: center;
    border-radius: 6px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    line-height: 1.2;
    padding: 8px 11px;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-btn.dm-primary {
    background: var(--dm-orange);
    border: 1px solid var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-btn.dm-secondary {
    background: var(--dm-navy);
    border: 1px solid var(--dm-navy);
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-btn:hover,
body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-btn:focus {
    background: var(--dm-navy-hover);
    border-color: var(--dm-navy-hover);
    color: #fff !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-panel {
    align-items: center;
    background: #fbfdff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    display: flex;
    gap: 14px;
    justify-content: space-between;
    margin: 0 0 14px;
    padding: 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-panel strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-panel span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
    margin-top: 3px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-badge {
    background: var(--dm-navy);
    border-radius: 999px;
    color: #fff;
    flex: 0 0 auto;
    font-size: 11px;
    font-style: normal;
    font-weight: 900;
    letter-spacing: .05em;
    padding: 7px 10px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-guide-grid,
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-strip {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-guide-card,
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-chip {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    padding: 10px 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-guide-card {
    border-left: 4px solid var(--dm-orange);
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-guide-card span,
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-chip span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-guide-card strong,
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-chip strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-checklist-panel,
body.whmcsbody.dm-nameservers-stage1 .dm-rl-final-note {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    margin: 0 0 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-final-note {
    background: #eef5fc;
    border-left: 4px solid var(--dm-navy);
    margin: 14px 0 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-checklist-panel strong,
body.whmcsbody.dm-nameservers-stage1 .dm-rl-final-note strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 5px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-checklist-panel ul {
    margin: 0;
    padding-left: 18px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-checklist-panel li,
body.whmcsbody.dm-nameservers-stage1 .dm-rl-final-note p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 3px 0;
}
body.whmcsbody.dm-nameservers-stage1 #tabReglock .alert,
body.whmcsbody.dm-nameservers-stage1 #tabReglock .alert-success,
body.whmcsbody.dm-nameservers-stage1 #tabReglock .alert-info,
body.whmcsbody.dm-nameservers-stage1 #tabReglock .alert-warning {
    margin-bottom: 14px;
    text-align: left !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabReglock form {
    background: #fff8f1;
    border: 1px solid rgba(245, 130, 32, .24);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0;
    padding: 14px;
}
body.whmcsbody.dm-nameservers-stage1 #tabReglock form p.text-center,
body.whmcsbody.dm-nameservers-stage1 #tabReglock form .text-center {
    text-align: left !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabReglock form .btn,
body.whmcsbody.dm-nameservers-stage1 #tabReglock form button[type="submit"],
body.whmcsbody.dm-nameservers-stage1 #tabReglock form input[type="submit"] {
    border-radius: 6px !important;
    color: #fff !important;
    font-weight: 800 !important;
    padding: 9px 16px !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabReglock form .btn-success,
body.whmcsbody.dm-nameservers-stage1 #tabReglock form .btn-primary,
body.whmcsbody.dm-nameservers-stage1 #tabReglock form input.btn-primary,
body.whmcsbody.dm-nameservers-stage1 #tabReglock form button.btn-primary {
    background: var(--dm-orange) !important;
    border-color: var(--dm-orange) !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabReglock form .btn-danger,
body.whmcsbody.dm-nameservers-stage1 #tabReglock form button.btn-danger,
body.whmcsbody.dm-nameservers-stage1 #tabReglock form input.btn-danger {
    background: var(--dm-red) !important;
    border-color: var(--dm-red) !important;
}
body.whmcsbody.dm-nameservers-stage1 #tabReglock form .btn:hover,
body.whmcsbody.dm-nameservers-stage1 #tabReglock form button[type="submit"]:hover,
body.whmcsbody.dm-nameservers-stage1 #tabReglock form input[type="submit"]:hover {
    filter: brightness(.96);
}



body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-badge.dm-locked {
    background: var(--dm-orange);
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-badge.dm-unlocked {
    background: #8c98a6;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-badge.dm-review {
    background: var(--dm-navy);
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-panel {
    align-items: center;
    background: #fff8f1;
    border: 1px solid rgba(245, 130, 32, .24);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 12px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-panel strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-panel span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
    margin-top: 3px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-panel em {
    background: var(--dm-navy);
    border-radius: 999px;
    color: #fff;
    flex: 0 0 auto;
    font-size: 10px;
    font-style: normal;
    font-weight: 900;
    letter-spacing: .05em;
    padding: 6px 9px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-transfer-note {
    background: #fbfdff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-navy);
    border-radius: 8px;
    color: var(--dm-text);
    margin: 0 0 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-transfer-note strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 4px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-rl-transfer-note p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 0;
}


body.whmcsbody.dm-nameservers-stage1 #tabAddons.dm-addons-converted > .card {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    overflow: hidden;
}
body.whmcsbody.dm-nameservers-stage1 #tabAddons.dm-addons-converted > .card > .card-body {
    padding: 0 !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-content {
    padding: 16px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-header {
    align-items: center;
    background: var(--dm-navy);
    color: #fff;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: -16px -16px 16px;
    padding: 12px 16px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-header h3 {
    color: #fff !important;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-header span {
    color: rgba(255,255,255,.82);
    font-size: 12px;
    font-weight: 700;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-title-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-action-btn {
    align-items: center;
    border-radius: 6px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    line-height: 1.2;
    padding: 8px 11px;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-action-btn.dm-primary {
    background: var(--dm-orange);
    border: 1px solid var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-action-btn.dm-secondary {
    background: var(--dm-navy);
    border: 1px solid var(--dm-navy);
    color: #fff !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-action-btn:hover,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-action-btn:focus {
    background: var(--dm-navy-hover);
    border-color: var(--dm-navy-hover);
    color: #fff !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-info-panel {
    align-items: center;
    background: #fff8f1;
    border: 1px solid rgba(245, 130, 32, .24);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    color: var(--dm-text);
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-info-panel strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-info-panel span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
    margin-top: 3px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-info-panel em {
    background: var(--dm-navy);
    border-radius: 999px;
    color: #fff;
    flex: 0 0 auto;
    font-size: 10px;
    font-style: normal;
    font-weight: 900;
    letter-spacing: .05em;
    padding: 6px 9px;
    text-transform: uppercase;
}

body.whmcsbody.dm-nameservers-stage1 .dm-addons-summary-panel {
    background: #eef5fc;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-navy);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    display: grid;
    gap: 10px;
    grid-template-columns: minmax(0, 1.6fr) repeat(2, minmax(110px, .7fr));
    margin: 0 0 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-summary-panel div {
    min-width: 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-summary-panel span {
    color: var(--dm-muted);
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .04em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-summary-panel strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-summary-panel p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.42;
    margin: 3px 0 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-status-strip,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-guide-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-status-chip,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-guide-card {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    padding: 10px 12px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-guide-card {
    border-left: 4px solid var(--dm-orange);
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-status-chip span,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-guide-card span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-status-chip strong,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-guide-card strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: 1fr;
    margin: 0 0 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card {
    background: #fbfdff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 !important;
    padding: 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card > [class*="col-"] {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    text-align: left !important;
    width: 100% !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card > [class*="col-"]:first-child {
    align-items: center;
    background: #fff4eb;
    border-radius: 8px;
    color: var(--dm-orange);
    display: inline-flex;
    height: 46px;
    justify-content: center;
    margin: 0 0 10px;
    max-width: 54px !important;
    width: 54px !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card i {
    color: var(--dm-orange) !important;
    font-size: 22px !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    margin-bottom: 5px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card br {
    display: none;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card form {
    border-top: 1px solid var(--dm-border-soft);
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 12px 0 0;
    padding-top: 12px;
}

body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-note {
    background: #fff;
    border: 1px solid var(--dm-border-soft);
    border-radius: 6px;
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.4;
    margin: 10px 0 0;
    padding: 8px 10px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-action-summary {
    align-items: center;
    background: #f7fbff;
    border: 1px solid var(--dm-border-soft);
    border-radius: 6px;
    color: var(--dm-text);
    display: flex;
    gap: 8px;
    justify-content: space-between;
    margin: 10px 0 0;
    padding: 8px 10px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-action-summary span {
    color: var(--dm-muted);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-action-summary strong {
    color: var(--dm-navy);
    display: inline;
    font-size: 12px;
    margin: 0;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-checklist-panel {
    background: #fff8f1;
    border: 1px solid rgba(245, 130, 32, .22);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    margin: 0 0 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-checklist-panel strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    margin-bottom: 6px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-checklist-panel ul {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 0;
    padding-left: 18px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-checklist-panel li + li {
    margin-top: 3px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card .btn,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card button[type="submit"],
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card input[type="submit"] {
    border-radius: 6px !important;
    color: #fff !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    padding: 8px 11px !important;
    text-decoration: none !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card .btn-success,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card .btn-primary,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card button.btn-success,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card button.btn-primary {
    background: var(--dm-orange) !important;
    border-color: var(--dm-orange) !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card a.btn-success {
    background: var(--dm-navy) !important;
    border-color: var(--dm-navy) !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card .btn-danger,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card button.btn-danger {
    background: var(--dm-red) !important;
    border-color: var(--dm-red) !important;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card .btn:hover,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card button[type="submit"]:hover,
body.whmcsbody.dm-nameservers-stage1 .dm-addons-addon-card input[type="submit"]:hover {
    filter: brightness(.96);
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-badge {
    background: #eef5fc;
    border: 1px solid rgba(22, 58, 95, .14);
    border-radius: 999px;
    color: var(--dm-navy);
    display: inline-block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
    margin: 8px 0 0;
    padding: 5px 8px;
    text-transform: uppercase;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-badge.dm-enabled {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .24);
    color: var(--dm-orange-soft);
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-final-note {
    background: #eef5fc;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-navy);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    color: var(--dm-text);
    margin: 14px 0 0;
    padding: 12px 14px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-final-note strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 4px;
}
body.whmcsbody.dm-nameservers-stage1 .dm-addons-final-note p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 0;
}

@media (max-width: 767px) {
    body.whmcsbody.dm-nameservers-stage1 .dm-ns-quickbar,
    body.whmcsbody.dm-nameservers-stage1 .dm-ns-status-strip,
    body.whmcsbody.dm-nameservers-stage1 .dm-ns-choice-grid,
    body.whmcsbody.dm-nameservers-stage1 .dm-ns-review-strip,
    body.whmcsbody.dm-nameservers-stage1 .dm-ns-live-list,
    body.whmcsbody.dm-nameservers-stage1 .dm-ar-guide-grid,
    body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-strip,
    body.whmcsbody.dm-nameservers-stage1 .dm-ar-renewal-grid,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-guide-grid,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-strip,
    body.whmcsbody.dm-nameservers-stage1 .dm-addons-summary-panel,
    body.whmcsbody.dm-nameservers-stage1 .dm-addons-status-strip,
    body.whmcsbody.dm-nameservers-stage1 .dm-addons-guide-grid,
    body.whmcsbody.dm-nameservers-stage1 .dm-addons-grid {
        grid-template-columns: 1fr;
    }
    body.whmcsbody.dm-nameservers-stage1 .dm-ns-card-header,
    body.whmcsbody.dm-nameservers-stage1 .dm-ns-action-panel,
    body.whmcsbody.dm-nameservers-stage1 .dm-ar-status-panel,
    body.whmcsbody.dm-nameservers-stage1 .dm-ar-save-panel,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-card-header,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-status-panel,
    body.whmcsbody.dm-nameservers-stage1 .dm-rl-action-panel,
    body.whmcsbody.dm-nameservers-stage1 .dm-addons-card-header,
    body.whmcsbody.dm-nameservers-stage1 .dm-addons-info-panel {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
<script>
(function () {
    var domainName = {$domainJson};
    var domainId = {$domainIdJson};
    var menuHtml = {$menuJson};
    var overviewHref = 'clientarea.php?action=domaindetails&id=' + encodeURIComponent(domainId) + '#tabOverview';
    var privateNsHref = 'domainmanagement.php?action=childns&id=' + encodeURIComponent(domainId);
    // Patch 1109: DNS Management stays on the ResellerClub URL while the fast native DNS feed powers the table.
    var dnsHref = 'dnsmanagement.php?action=managednszone&domain=' + encodeURIComponent(domainName) + '&domainid=' + encodeURIComponent(domainId) + '&nsrecordtype=A';

    function sectionKeyFromTarget(targetId) {
        var map = {
            tabOverview: 'overview',
            tabAutorenew: 'autorenew',
            tabNameservers: 'nameservers',
            tabReglock: 'reglock',
            tabAddons: 'addons'
        };
        return map[targetId] || '';
    }

    function updateSectionMenuActive(targetId) {
        var key = sectionKeyFromTarget(targetId || '');
        var links = document.querySelectorAll('.dm-domain-section-menu .dm-domain-section-link');
        for (var i = 0; i < links.length; i++) {
            var target = links[i].getAttribute('data-dm-tab-target') || '';
            var linkKey = sectionKeyFromTarget(target);
            var isActive = key !== '' && linkKey === key;
            links[i].classList.toggle('dm-active', isActive);
            if (isActive) {
                links[i].setAttribute('aria-current', 'page');
            } else {
                links[i].removeAttribute('aria-current');
            }
        }
    }

    function activateTab(targetId) {
        if (!targetId) {
            return;
        }
        updateSectionMenuActive(targetId);
        var target = document.getElementById(targetId);
        if (!target) {
            return;
        }
        var tabContent = target.closest('.tab-content');
        if (tabContent) {
            var panes = tabContent.querySelectorAll('.tab-pane');
            for (var i = 0; i < panes.length; i++) {
                panes[i].classList.remove('active', 'show', 'in');
            }
            target.classList.add('active', 'show', 'in');
        }
        var links = document.querySelectorAll('.dm-domain-section-link');
        for (var j = 0; j < links.length; j++) {
            links[j].classList.remove('dm-active');
            links[j].removeAttribute('aria-current');
            if (links[j].getAttribute('data-dm-tab-target') === targetId) {
                links[j].classList.add('dm-active');
                links[j].setAttribute('aria-current', 'page');
            }
        }
    }


    function getCheckedMode(form) {
        if (!form) {
            return 'Unknown';
        }
        var checked = form.querySelector('input[name="nschoice"]:checked');
        if (!checked) {
            return 'Not selected';
        }
        var value = (checked.value || '').toLowerCase();
        var label = checked.closest ? checked.closest('.form-check') : null;
        var labelText = label ? (label.textContent || '').replace(/\s+/g, ' ').trim() : '';
        if (value.indexOf('custom') !== -1 || labelText.toLowerCase().indexOf('custom') !== -1) {
            return 'Custom nameservers';
        }
        if (value.indexOf('default') !== -1 || labelText.toLowerCase().indexOf('default') !== -1) {
            return 'Default nameservers';
        }
        return labelText || 'Selected option';
    }

    function countCustomNameservers(form) {
        if (!form) {
            return 0;
        }
        var inputs = form.querySelectorAll('input[name^="ns"]');
        var count = 0;
        for (var i = 0; i < inputs.length; i++) {
            if (/^ns[1-5]$/.test(inputs[i].getAttribute('name') || '') && (inputs[i].value || '').trim() !== '') {
                count++;
            }
        }
        return count;
    }


    function getNameserverFormState(form) {
        var state = [];
        if (!form) {
            return state;
        }
        var checked = form.querySelector('input[name="nschoice"]:checked');
        state.push('mode=' + (checked ? (checked.value || '') : ''));
        var inputs = form.querySelectorAll('input[name^="ns"]');
        for (var i = 0; i < inputs.length; i++) {
            var name = inputs[i].getAttribute('name') || '';
            if (/^ns[1-5]$/.test(name)) {
                state.push(name + '=' + ((inputs[i].value || '').trim()));
            }
        }
        return state;
    }

    function updateNameserverFieldStatus(form) {
        if (!form) {
            return;
        }
        var inputs = form.querySelectorAll('input[name^="ns"]');
        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            var name = input.getAttribute('name') || '';
            if (!/^ns[1-5]$/.test(name)) {
                continue;
            }
            var holder = input.parentNode;
            if (!holder) {
                continue;
            }
            var status = holder.querySelector('.dm-ns-field-status');
            if (!status) {
                status = document.createElement('small');
                status.className = 'dm-ns-field-status dm-empty';
                holder.appendChild(status);
            }
            var filled = (input.value || '').trim() !== '';
            status.classList.toggle('dm-filled', filled);
            status.classList.toggle('dm-empty', !filled);
            status.textContent = filled ? 'Filled' : 'Empty';
        }
    }

    function updateNameserverSaveState(form) {
        if (!form) {
            return;
        }
        if (!form.getAttribute('data-dm-ns-initial-state')) {
            form.setAttribute('data-dm-ns-initial-state', JSON.stringify(getNameserverFormState(form)));
        }
        var target = form.querySelector('.dm-ns-save-state');
        if (!target) {
            return;
        }
        var initial = form.getAttribute('data-dm-ns-initial-state') || '';
        var current = JSON.stringify(getNameserverFormState(form));
        var dirty = initial !== current;
        target.classList.toggle('dm-dirty', dirty);
        target.textContent = dirty ? 'Unsaved changes' : 'Current values loaded';
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function updateNameserverLiveReview(form) {
        if (!form) {
            return;
        }
        var panel = form.querySelector('.dm-ns-live-review');
        if (!panel) {
            return;
        }
        var mode = getCheckedMode(form);
        var modeTarget = panel.querySelector('.dm-ns-live-review-mode');
        var list = panel.querySelector('.dm-ns-live-list');
        if (modeTarget) {
            modeTarget.textContent = mode;
        }
        if (!list) {
            return;
        }
        var inputs = form.querySelectorAll('input[name^="ns"]');
        var html = '';
        for (var i = 0; i < inputs.length; i++) {
            var name = inputs[i].getAttribute('name') || '';
            var match = name.match(/^ns([1-5])$/);
            var value = (inputs[i].value || '').trim();
            if (!match || value === '') {
                continue;
            }
            html += '<div class="dm-ns-live-chip"><span>NS' + escapeHtml(match[1]) + '</span><strong>' + escapeHtml(value) + '</strong></div>';
        }
        if (html === '') {
            html = '<div class="dm-ns-live-empty">No custom nameserver entries are currently filled. Use the fields below if this domain should use custom nameservers.</div>';
        }
        list.innerHTML = html;
    }

    function updateNameserverReview(form) {
        if (!form) {
            return;
        }
        var mode = getCheckedMode(form);
        var count = countCustomNameservers(form);
        var modeTarget = form.querySelector('.dm-ns-mode-value');
        var countTarget = form.querySelector('.dm-ns-count-value');
        var noteTarget = form.querySelector('.dm-ns-custom-count-note');
        if (modeTarget) {
            modeTarget.textContent = mode;
        }
        if (countTarget) {
            countTarget.textContent = count + (count === 1 ? ' custom entry filled' : ' custom entries filled');
        }
        if (noteTarget) {
            noteTarget.textContent = count < 2 ? 'Tip: most domains should have at least two working nameservers before saving custom nameservers.' : 'This has enough custom nameserver entries for the usual two-nameserver setup.';
            if (count < 2) {
                noteTarget.classList.add('dm-warning');
            } else {
                noteTarget.classList.remove('dm-warning');
            }
        }

        updateNameserverFieldStatus(form);
        updateNameserverSaveState(form);
        updateNameserverLiveReview(form);

        var choices = form.querySelectorAll('.form-check');
        for (var j = 0; j < choices.length; j++) {
            var input = choices[j].querySelector('input[type="radio"]');
            choices[j].classList.toggle('dm-selected-choice', !!(input && input.checked));
            var existingBadge = choices[j].querySelector('.dm-ns-selected-badge');
            if (input && input.checked && !existingBadge) {
                var label = choices[j].querySelector('label') || choices[j];
                label.insertAdjacentHTML('beforeend', '<span class="dm-ns-selected-badge">Selected</span>');
            } else if ((!input || !input.checked) && existingBadge) {
                existingBadge.parentNode.removeChild(existingBadge);
            }
        }
    }


    function convertAutoRenew() {
        var tab = document.getElementById('tabAutorenew');
        if (!tab || tab.getAttribute('data-dm-autorenew-stage') === '888') {
            return;
        }
        tab.setAttribute('data-dm-autorenew-stage', '888');
        tab.classList.add('dm-autorenew-converted');
        document.body.classList.add('dm-nameservers-stage1');
        updateSectionMenuActive('tabAutorenew');

        var tabContent = tab.closest('.tab-content');
        if (tabContent && !document.querySelector('.dm-domain-section-menu')) {
            tabContent.insertAdjacentHTML('beforebegin', menuHtml);
        }

        var cardBody = tab.querySelector('.card > .card-body') || tab;
        cardBody.classList.add('dm-ar-content');

        var title = cardBody.querySelector('h3.card-title, h3');
        if (title && !cardBody.querySelector('.dm-ar-card-header')) {
            var titleText = title.textContent || 'Auto Renew';
            var header = document.createElement('div');
            header.className = 'dm-ar-card-header';
            header.innerHTML = '<h3>Auto Renew</h3><span>Renewal Settings</span>';
            title.parentNode.insertBefore(header, title);
            title.style.display = 'none';
        }

        if (!cardBody.querySelector('.dm-ar-title-actions')) {
            var titleActions = document.createElement('div');
            titleActions.className = 'dm-ar-title-actions';
            titleActions.innerHTML = '<a class="dm-ar-action-btn dm-secondary" href="' + overviewHref + '">Domain Overview</a>'
                + '<a class="dm-ar-action-btn dm-secondary" href="' + privateNsHref + '">Private Nameservers</a>'
                + '<a class="dm-ar-action-btn dm-primary" href="' + dnsHref + '">DNS Management</a>';
            var insertAfterHeader = cardBody.querySelector('.dm-ar-card-header');
            if (insertAfterHeader && insertAfterHeader.nextSibling) {
                insertAfterHeader.parentNode.insertBefore(titleActions, insertAfterHeader.nextSibling);
            } else {
                cardBody.insertBefore(titleActions, cardBody.firstChild);
            }
        }

        var form = tab.querySelector('form');
        if (!form) {
            return;
        }

        var hiddenAction = form.querySelector('input[name="autorenew"]');
        var nextAction = hiddenAction ? (hiddenAction.value || '').toLowerCase() : '';
        var currentlyEnabled = nextAction === 'disable';
        var statusText = currentlyEnabled ? 'Enabled' : 'Disabled';
        var nextText = currentlyEnabled ? 'Disable auto renew' : 'Enable auto renew';
        var statusClass = currentlyEnabled ? 'dm-enabled' : 'dm-disabled';

        if (!tab.querySelector('.dm-ar-status-panel')) {
            var existingStatus = cardBody.querySelector('h4.text-center, h2.text-center');
            if (existingStatus) {
                existingStatus.style.display = 'none';
            }
            var statusPanel = document.createElement('div');
            statusPanel.className = 'dm-ar-status-panel';
            statusPanel.innerHTML = '<div><strong>Auto renew is currently ' + statusText.toLowerCase() + '</strong><span>This page uses the existing WHMCS auto-renew save action. Only the visible layout has changed.</span></div><em class="dm-ar-status-badge ' + statusClass + '">' + statusText + '</em>';
            form.parentNode.insertBefore(statusPanel, form);

            var guideGrid = document.createElement('div');
            guideGrid.className = 'dm-ar-guide-grid';
            guideGrid.innerHTML = '<div class="dm-ar-guide-card"><span>Renewal</span><strong>Controls whether this domain renews automatically.</strong></div><div class="dm-ar-guide-card"><span>Billing</span><strong>Keep payment details current before renewal.</strong></div><div class="dm-ar-guide-card"><span>Action</span><strong>The button below will ' + escapeHtml(nextText.toLowerCase()) + '.</strong></div>';
            statusPanel.parentNode.insertBefore(guideGrid, statusPanel.nextSibling);

            var statusStrip = document.createElement('div');
            statusStrip.className = 'dm-ar-status-strip';
            statusStrip.innerHTML = '<div class="dm-ar-status-chip"><span>Section</span><strong>Auto Renew</strong></div><div class="dm-ar-status-chip"><span>Current Status</span><strong>' + escapeHtml(statusText) + '</strong></div><div class="dm-ar-status-chip"><span>Next Action</span><strong>' + escapeHtml(nextText) + '</strong></div>';
            guideGrid.parentNode.insertBefore(statusStrip, guideGrid.nextSibling);

            var renewalGrid = document.createElement('div');
            renewalGrid.className = 'dm-ar-renewal-grid';
            renewalGrid.innerHTML = '<div class="dm-ar-renewal-card"><span>Renewal Control</span><strong>Use this page to choose whether WHMCS should attempt automatic renewal.</strong></div><div class="dm-ar-renewal-card"><span>Billing Check</span><strong>Keep the account balance or payment method ready before the renewal date.</strong></div><div class="dm-ar-renewal-card"><span>Manual Option</span><strong>If auto renew is disabled, renew the domain manually before expiration.</strong></div>';
            statusStrip.parentNode.insertBefore(renewalGrid, statusStrip.nextSibling);
        }

        if (!form.querySelector('.dm-ar-save-panel')) {
            var savePanel = document.createElement('div');
            savePanel.className = 'dm-ar-save-panel';
            savePanel.innerHTML = '<div><strong>Confirm auto-renew change</strong><span>The existing WHMCS button below will submit the saved registrar action for this domain.</span></div><em>' + escapeHtml(nextText) + '</em>';
            form.insertBefore(savePanel, form.firstChild);
        }

        if (!form.querySelector('.dm-ar-current-action')) {
            var currentAction = document.createElement('div');
            currentAction.className = 'dm-ar-current-action';
            currentAction.innerHTML = '<strong>Current action:</strong> The button below will ' + escapeHtml(nextText.toLowerCase()) + ' for ' + escapeHtml(domainName) + '.';
            form.insertBefore(currentAction, form.firstChild);
        }

        if (!form.querySelector('.dm-ar-checklist-panel')) {
            var checklist = document.createElement('div');
            checklist.className = 'dm-ar-checklist-panel';
            checklist.innerHTML = '<strong>Before changing auto renew</strong><ul><li>Confirm this is the domain you want to update.</li><li>Keep billing details current if auto renew will stay enabled.</li><li>If auto renew is disabled, plan to renew manually before expiration.</li></ul>';
            form.insertBefore(checklist, form.firstChild);
        }

        if (!form.querySelector('.dm-ar-action-note')) {
            var actionNote = document.createElement('p');
            actionNote.className = 'dm-ar-action-note';
            actionNote.innerHTML = '<strong>Next action:</strong> ' + escapeHtml(nextText) + ' for ' + escapeHtml(domainName) + '.';
            form.insertBefore(actionNote, form.firstChild);
        }

        if (!cardBody.querySelector('.dm-ar-final-note')) {
            var finalNote = document.createElement('div');
            finalNote.className = 'dm-ar-final-note';
            finalNote.innerHTML = '<strong>DomainMonger design conversion</strong><p>Auto Renew is now part of the no-side-menu domain management section while keeping the existing WHMCS form and save behavior.</p>';
            form.parentNode.insertBefore(finalNote, form.nextSibling);
        }
    }


    function detectRegistrarLockStatus(tab) {
        if (!tab) {
            return {status: 'Review status', badge: 'Registrar Lock', badgeClass: 'dm-review', note: 'Review the current registrar lock status before changing it.'};
        }
        var text = (tab.textContent || '').replace(/\s+/g, ' ').toLowerCase();
        if (text.indexOf('currently disabled') !== -1 || text.indexOf('unlocked') !== -1) {
            return {status: 'Unlocked or disabled', badge: 'Review', badgeClass: 'dm-unlocked', note: 'The domain may be easier to transfer while registrar lock is disabled.'};
        }
        if (text.indexOf('currently enabled') !== -1 || text.indexOf('locked') !== -1 || text.indexOf('enabled') !== -1) {
            return {status: 'Locked or enabled', badge: 'Protected', badgeClass: 'dm-locked', note: 'Registrar lock is normally the safer everyday setting.'};
        }
        return {status: 'Review current page status', badge: 'Registrar Lock', badgeClass: 'dm-review', note: 'Review the current registrar lock status before changing it.'};
    }

    function detectRegistrarLockAction(form) {
        if (!form) {
            return 'Review action';
        }
        var button = form.querySelector('button[type="submit"], input[type="submit"], .btn');
        var text = '';
        if (button) {
            text = (button.value || button.textContent || '').replace(/\s+/g, ' ').trim();
        }
        return text || 'Save registrar lock change';
    }

    function convertRegistrarLock() {
        var tab = document.getElementById('tabReglock');
        if (!tab || tab.getAttribute('data-dm-reglock-stage') === '892') {
            return;
        }
        tab.setAttribute('data-dm-reglock-stage', '892');
        tab.classList.add('dm-reglock-converted');
        document.body.classList.add('dm-nameservers-stage1');

        var tabContent = tab.closest('.tab-content');
        if (tabContent && !document.querySelector('.dm-domain-section-menu')) {
            tabContent.insertAdjacentHTML('beforebegin', menuHtml);
        }

        var cardBody = tab.querySelector('.card > .card-body') || tab;
        cardBody.classList.add('dm-rl-content');

        var title = cardBody.querySelector('h3.card-title, h3');
        if (title && !cardBody.querySelector('.dm-rl-card-header')) {
            var titleText = title.textContent || 'Registrar Lock';
            var header = document.createElement('div');
            header.className = 'dm-rl-card-header';
            header.innerHTML = '<h3>Registrar Lock</h3><span>Domain Security</span>';
            title.parentNode.insertBefore(header, title);
            title.style.display = 'none';
        }

        if (!cardBody.querySelector('.dm-rl-title-actions')) {
            var titleActions = document.createElement('div');
            titleActions.className = 'dm-rl-title-actions';
            titleActions.innerHTML = '<a class="dm-rl-action-btn dm-secondary" href="' + overviewHref + '">Domain Overview</a>'
                + '<a class="dm-rl-action-btn dm-secondary" href="' + privateNsHref + '">Private Nameservers</a>'
                + '<a class="dm-rl-action-btn dm-primary" href="' + dnsHref + '">DNS Management</a>';
            var insertAfterHeader = cardBody.querySelector('.dm-rl-card-header');
            if (insertAfterHeader && insertAfterHeader.nextSibling) {
                insertAfterHeader.parentNode.insertBefore(titleActions, insertAfterHeader.nextSibling);
            } else {
                cardBody.insertBefore(titleActions, cardBody.firstChild);
            }
        }

        var form = tab.querySelector('form');
        var status = detectRegistrarLockStatus(tab);
        var insertBefore = form || cardBody.firstChild;

        if (!tab.querySelector('.dm-rl-status-panel')) {
            var existingStatus = cardBody.querySelector('h4.text-center, h2.text-center');
            if (existingStatus) {
                existingStatus.style.display = 'none';
            }
            var statusPanel = document.createElement('div');
            statusPanel.className = 'dm-rl-status-panel';
            statusPanel.innerHTML = '<div><strong>Registrar Lock</strong><span>' + escapeHtml(status.note) + ' Existing WHMCS registrar actions are preserved.</span></div><em class="dm-rl-status-badge ' + escapeHtml(status.badgeClass) + '">' + escapeHtml(status.badge) + '</em>';
            cardBody.insertBefore(statusPanel, insertBefore);

            var guideGrid = document.createElement('div');
            guideGrid.className = 'dm-rl-guide-grid';
            guideGrid.innerHTML = '<div class="dm-rl-guide-card"><span>Protection</span><strong>Keep registrar lock enabled unless you are preparing a transfer.</strong></div><div class="dm-rl-guide-card"><span>Transfer</span><strong>Unlock only when the domain needs to move to another registrar.</strong></div><div class="dm-rl-guide-card"><span>Security</span><strong>Lock status changes use the existing registrar workflow.</strong></div>';
            statusPanel.parentNode.insertBefore(guideGrid, statusPanel.nextSibling);

            var statusStrip = document.createElement('div');
            statusStrip.className = 'dm-rl-status-strip';
            statusStrip.innerHTML = '<div class="dm-rl-status-chip"><span>Section</span><strong>Registrar Lock</strong></div><div class="dm-rl-status-chip"><span>Current Status</span><strong>' + escapeHtml(status.status) + '</strong></div><div class="dm-rl-status-chip"><span>Backend</span><strong>Existing registrar action</strong></div>';
            guideGrid.parentNode.insertBefore(statusStrip, guideGrid.nextSibling);
        }

        if (form && !form.querySelector('.dm-rl-action-panel')) {
            var actionText = detectRegistrarLockAction(form);
            var actionPanel = document.createElement('div');
            actionPanel.className = 'dm-rl-action-panel';
            actionPanel.innerHTML = '<div><strong>Current registrar action</strong><span>The existing WHMCS button below will submit the current registrar lock change for ' + escapeHtml(domainName) + '.</span></div><em>' + escapeHtml(actionText) + '</em>';
            form.insertBefore(actionPanel, form.firstChild);
        }

        if (form && !form.querySelector('.dm-rl-checklist-panel')) {
            var checklist = document.createElement('div');
            checklist.className = 'dm-rl-checklist-panel';
            checklist.innerHTML = '<strong>Before changing registrar lock</strong><ul><li>Keep registrar lock enabled for normal domain protection.</li><li>Only unlock when you are intentionally preparing a domain transfer.</li><li>After changing lock status, allow the registrar response to complete before leaving the page.</li></ul>';
            form.insertBefore(checklist, form.firstChild);
        }

        if (form && !form.querySelector('.dm-rl-transfer-note')) {
            var transferNote = document.createElement('div');
            transferNote.className = 'dm-rl-transfer-note';
            transferNote.innerHTML = '<strong>Transfer reminder</strong><p>Registrar lock is separate from the EPP authorization code. For a transfer, unlock the domain first, then use the Get EPP Code section when needed.</p>';
            form.parentNode.insertBefore(transferNote, form.nextSibling);
        }

        if (!cardBody.querySelector('.dm-rl-final-note')) {
            var finalNote = document.createElement('div');
            finalNote.className = 'dm-rl-final-note';
            finalNote.innerHTML = '<strong>DomainMonger design conversion</strong><p>Registrar Lock is now part of the no-side-menu domain management section while keeping the existing WHMCS registrar lock behavior.</p>';
            if (form) {
                form.parentNode.insertBefore(finalNote, form.nextSibling);
            } else {
                cardBody.appendChild(finalNote);
            }
        }
    }


    function convertAddons() {
        var tab = document.getElementById('tabAddons');
        if (!tab || tab.getAttribute('data-dm-addons-stage') === '896') {
            return;
        }
        tab.setAttribute('data-dm-addons-stage', '896');
        tab.classList.add('dm-addons-converted');
        document.body.classList.add('dm-nameservers-stage1');

        var tabContent = tab.closest('.tab-content');
        if (tabContent && !document.querySelector('.dm-domain-section-menu')) {
            tabContent.insertAdjacentHTML('beforebegin', menuHtml);
        }

        var cardBody = tab.querySelector('.card > .card-body') || tab;
        cardBody.classList.add('dm-addons-content');

        // Patch 1164: the failed 1162 direct-child addon filter was removed.
        // The working unified ResellerClub hook now filters the nested addon
        // forms by their actual dnsmanagement/emailfwd service values.

        var title = cardBody.querySelector('h3.card-title, h3');
        if (title && !cardBody.querySelector('.dm-addons-card-header')) {
            var titleText = title.textContent || 'Addons';
            var header = document.createElement('div');
            header.className = 'dm-addons-card-header';
            header.innerHTML = '<h3>ID Protection</h3><span>WHOIS Privacy</span>';
            title.parentNode.insertBefore(header, title);
            title.style.display = 'none';
        }

        if (!cardBody.querySelector('.dm-addons-title-actions')) {
            var titleActions = document.createElement('div');
            titleActions.className = 'dm-addons-title-actions';
            titleActions.innerHTML = '<a class="dm-addons-action-btn dm-secondary" href="' + overviewHref + '">Domain Overview</a>'
                + '<a class="dm-addons-action-btn dm-secondary" href="' + privateNsHref + '">Private Nameservers</a>'
                + '<a class="dm-addons-action-btn dm-primary" href="' + dnsHref + '">DNS Management</a>';
            var insertAfterHeader = cardBody.querySelector('.dm-addons-card-header');
            if (insertAfterHeader && insertAfterHeader.nextSibling) {
                insertAfterHeader.parentNode.insertBefore(titleActions, insertAfterHeader.nextSibling);
            } else {
                cardBody.insertBefore(titleActions, cardBody.firstChild);
            }
        }

        var forms = tab.querySelectorAll('form[action*="domainaddons"], form');
        var addonCount = forms.length;
        var enabledCount = 0;
        for (var f = 0; f < forms.length; f++) {
            if (forms[f].querySelector('input[name="disable"], a.btn')) {
                enabledCount++;
            }
        }

        function dmAddonServiceName(card) {
            var strong = card.querySelector('strong');
            var txt = strong ? strong.textContent : 'Domain Addon';
            return (txt || 'Domain Addon').replace(/\s+/g, ' ').trim();
        }

        function dmAddonStatus(card) {
            var cardForm = card.querySelector('form');
            if (cardForm && cardForm.querySelector('input[name="disable"]')) {
                return { label: 'Enabled', note: 'This service is currently active or can be disabled from the existing WHMCS control.' };
            }
            if (cardForm && cardForm.querySelector('input[name="buy"]')) {
                return { label: 'Available', note: 'This service can be added with the existing WHMCS purchase action.' };
            }
            if (card.querySelector('a.btn')) {
                return { label: 'Managed', note: 'This service is managed through the existing WHMCS destination.' };
            }
            return { label: 'Review', note: 'Review this addon using the existing WHMCS controls.' };
        }

        if (!cardBody.querySelector('.dm-addons-info-panel')) {
            var introParagraph = null;
            var paragraphs = cardBody.querySelectorAll('p');
            for (var ip = 0; ip < paragraphs.length; ip++) {
                if (!paragraphs[ip].closest('form') && !paragraphs[ip].closest('.dm-addons-final-note')) {
                    introParagraph = paragraphs[ip];
                    break;
                }
            }
            if (introParagraph) {
                introParagraph.style.display = 'none';
            }
            var infoPanel = document.createElement('div');
            infoPanel.className = 'dm-addons-info-panel';
            infoPanel.innerHTML = '<div><strong>ID Protection</strong><span>Review WHOIS privacy protection and use the existing WHMCS control below.</span></div><em>' + addonCount + (addonCount === 1 ? ' Option' : ' Options') + '</em>';
            var anchor = cardBody.querySelector('.dm-addons-title-actions') || cardBody.querySelector('.dm-addons-card-header') || cardBody.firstChild;
            if (anchor && anchor.nextSibling) {
                anchor.parentNode.insertBefore(infoPanel, anchor.nextSibling);
            } else {
                cardBody.insertBefore(infoPanel, cardBody.firstChild);
            }

            var statusStrip = document.createElement('div');
            statusStrip.className = 'dm-addons-status-strip';
            statusStrip.innerHTML = '<div class="dm-addons-status-chip"><span>Section</span><strong>ID Protection</strong></div><div class="dm-addons-status-chip"><span>Available Options</span><strong>' + addonCount + '</strong></div><div class="dm-addons-status-chip"><span>Enabled / Managed</span><strong>' + enabledCount + '</strong></div>';
            infoPanel.parentNode.insertBefore(statusStrip, infoPanel.nextSibling);

            var guideGrid = document.createElement('div');
            guideGrid.className = 'dm-addons-guide-grid';
            guideGrid.innerHTML = '<div class="dm-addons-guide-card"><span>Privacy</span><strong>Hide eligible WHOIS contact details when ID Protection is available.</strong></div><div class="dm-addons-guide-card"><span>Eligibility</span><strong>Availability depends on the domain extension and registrar support.</strong></div><div class="dm-addons-guide-card"><span>Control</span><strong>Use the existing button below to enable or disable ID Protection.</strong></div>';
            statusStrip.parentNode.insertBefore(guideGrid, statusStrip.nextSibling);
        }

        if (!cardBody.querySelector('.dm-addons-summary-panel')) {
            var summaryPanel = document.createElement('div');
            summaryPanel.className = 'dm-addons-summary-panel';
            summaryPanel.innerHTML = '<div><span>ID Protection Control</span><strong>Manage WHOIS privacy for this domain.</strong><p>The button below still uses the existing WHMCS registrar action.</p></div>'
                + '<div><span>Service</span><strong>ID Protection</strong></div>'
                + '<div><span>Purpose</span><strong>WHOIS Privacy</strong></div>';
            var summaryAnchor = cardBody.querySelector('.dm-addons-guide-grid') || cardBody.querySelector('.dm-addons-status-strip') || cardBody.querySelector('.dm-addons-info-panel');
            if (summaryAnchor && summaryAnchor.nextSibling) {
                summaryAnchor.parentNode.insertBefore(summaryPanel, summaryAnchor.nextSibling);
            } else if (summaryAnchor) {
                summaryAnchor.parentNode.appendChild(summaryPanel);
            } else {
                cardBody.insertBefore(summaryPanel, cardBody.firstChild);
            }
        }

        if (!cardBody.querySelector('.dm-addons-checklist-panel')) {
            var checklistPanel = document.createElement('div');
            checklistPanel.className = 'dm-addons-checklist-panel';
            checklistPanel.innerHTML = '<strong>Before changing ID Protection</strong><ul><li>Review whether privacy protection is currently enabled.</li><li>Confirm the domain extension supports ID Protection.</li><li>Disable only when public WHOIS contact visibility is intentionally required.</li></ul>';
            var checklistAnchor = cardBody.querySelector('.dm-addons-summary-panel') || cardBody.querySelector('.dm-addons-guide-grid') || cardBody.querySelector('.dm-addons-status-strip');
            if (checklistAnchor && checklistAnchor.nextSibling) {
                checklistAnchor.parentNode.insertBefore(checklistPanel, checklistAnchor.nextSibling);
            } else if (checklistAnchor) {
                checklistAnchor.parentNode.appendChild(checklistPanel);
            } else {
                cardBody.insertBefore(checklistPanel, cardBody.firstChild);
            }
        }

        var directChildren = Array.prototype.slice.call(cardBody.children);
        var addonRows = [];
        for (var i = 0; i < directChildren.length; i++) {
            var child = directChildren[i];
            if (child.classList && child.classList.contains('row') && child.querySelector('form')) {
                addonRows.push(child);
            }
        }

        if (addonRows.length && !cardBody.querySelector('.dm-addons-grid')) {
            var grid = document.createElement('div');
            grid.className = 'dm-addons-grid';
            addonRows[0].parentNode.insertBefore(grid, addonRows[0]);
            for (var r = 0; r < addonRows.length; r++) {
                grid.appendChild(addonRows[r]);
            }
            var hrs = cardBody.querySelectorAll('hr');
            for (var h = 0; h < hrs.length; h++) {
                hrs[h].style.display = 'none';
            }
        }

        var cards = tab.querySelectorAll('.dm-addons-grid > .row');
        for (var c = 0; c < cards.length; c++) {
            var card = cards[c];
            card.classList.add('dm-addons-addon-card');
            if (!card.querySelector('.dm-addons-card-badge')) {
                var cardForm = card.querySelector('form');
                var statusText = 'Available';
                var badgeClass = '';
                if (cardForm && cardForm.querySelector('input[name="disable"]')) {
                    statusText = 'Enabled';
                    badgeClass = ' dm-enabled';
                } else if (cardForm && cardForm.querySelector('input[name="buy"]')) {
                    statusText = 'Available';
                } else if (card.querySelector('a.btn')) {
                    statusText = 'Managed';
                    badgeClass = ' dm-enabled';
                }
                var contentCol = card.querySelector('.col-9, .col-md-10, [class*="col-"]:last-child') || card;
                var badge = document.createElement('span');
                badge.className = 'dm-addons-card-badge' + badgeClass;
                badge.textContent = statusText;
                contentCol.insertBefore(badge, contentCol.querySelector('form') || null);
            }

            if (!card.querySelector('.dm-addons-card-note')) {
                var statusData = dmAddonStatus(card);
                var cardNote = document.createElement('div');
                cardNote.className = 'dm-addons-card-note';
                cardNote.textContent = statusData.note;
                var formForNote = card.querySelector('form');
                if (formForNote) {
                    formForNote.parentNode.insertBefore(cardNote, formForNote);
                } else {
                    card.appendChild(cardNote);
                }
            }

            if (!card.querySelector('.dm-addons-card-action-summary')) {
                var statusData2 = dmAddonStatus(card);
                var actionSummary = document.createElement('div');
                actionSummary.className = 'dm-addons-card-action-summary';
                actionSummary.innerHTML = '<span>Current state</span><strong>' + escapeHtml(statusData2.label) + '</strong>';
                var firstForm = card.querySelector('form');
                if (firstForm) {
                    firstForm.parentNode.insertBefore(actionSummary, firstForm);
                } else {
                    card.appendChild(actionSummary);
                }
            }
        }

        if (!cardBody.querySelector('.dm-addons-final-note')) {
            var finalNote = document.createElement('div');
            finalNote.className = 'dm-addons-final-note';
            finalNote.innerHTML = '<strong>ID Protection</strong><p>This page keeps the existing WHMCS enable and disable actions while showing only the WHOIS privacy service.</p>';
            cardBody.appendChild(finalNote);
        }
    }

    function convertNameservers() {
        var tab = document.getElementById('tabNameservers');
        if (!tab || tab.getAttribute('data-dm-nameservers-stage1') === '884') {
            return;
        }
        tab.setAttribute('data-dm-nameservers-stage1', '884');
        tab.classList.add('dm-nameservers-converted');
        document.body.classList.add('dm-nameservers-stage1');
        updateSectionMenuActive('tabNameservers');

        var tabContent = tab.closest('.tab-content');
        if (tabContent && !document.querySelector('.dm-domain-section-menu')) {
            tabContent.insertAdjacentHTML('beforebegin', menuHtml);
        }

        var cardBody = tab.querySelector('.card > .card-body') || tab;
        cardBody.classList.add('dm-ns-content');

        var title = cardBody.querySelector('h3.card-title, h3');
        if (title && !cardBody.querySelector('.dm-ns-card-header')) {
            var titleText = title.textContent || 'Nameservers';
            var header = document.createElement('div');
            header.className = 'dm-ns-card-header';
            header.innerHTML = '<h3>Nameservers</h3><span>DNS Delegation</span>';
            title.parentNode.insertBefore(header, title);
            title.style.display = 'none';
        }

        if (!cardBody.querySelector('.dm-ns-title-actions')) {
            var titleActions = document.createElement('div');
            titleActions.className = 'dm-ns-title-actions';
            titleActions.innerHTML = '<a class="dm-ns-action-btn dm-secondary" href="' + overviewHref + '">Domain Overview</a>'
                + '<a class="dm-ns-action-btn dm-secondary" href="' + privateNsHref + '">Private Nameservers</a>'
                + '<a class="dm-ns-action-btn dm-primary" href="' + dnsHref + '">DNS Management</a>';
            var insertAfterHeader = cardBody.querySelector('.dm-ns-card-header');
            if (insertAfterHeader && insertAfterHeader.nextSibling) {
                insertAfterHeader.parentNode.insertBefore(titleActions, insertAfterHeader.nextSibling);
            } else {
                cardBody.insertBefore(titleActions, cardBody.firstChild);
            }
        }

        var firstForm = tab.querySelector('form');
        if (firstForm && !tab.querySelector('.dm-ns-info-card')) {
            var info = document.createElement('div');
            info.className = 'dm-ns-info-card';
            info.innerHTML = '<span class="dm-ns-info-icon">i</span><div><strong>Nameservers</strong><p>Choose whether this domain uses the default nameservers or custom nameservers. Existing WHMCS save behavior is preserved.</p></div>';
            firstForm.parentNode.insertBefore(info, firstForm);

            var quickbar = document.createElement('div');
            quickbar.className = 'dm-ns-quickbar';
            quickbar.innerHTML = '<div class="dm-ns-quickitem"><span>Default</span><strong>Use the account default nameservers</strong></div><div class="dm-ns-quickitem"><span>Custom</span><strong>Enter up to five nameservers</strong></div><div class="dm-ns-quickitem"><span>Propagation</span><strong>Changes may take time to appear globally</strong></div>';
            info.parentNode.insertBefore(quickbar, firstForm);

            var statusStrip = document.createElement('div');
            statusStrip.className = 'dm-ns-status-strip';
            statusStrip.innerHTML = '<div class="dm-ns-status-chip"><span>Section</span><strong>Nameservers</strong></div><div class="dm-ns-status-chip"><span>Layout</span><strong>No side menu</strong></div><div class="dm-ns-status-chip"><span>Backend</span><strong>Existing save action</strong></div>';
            quickbar.parentNode.insertBefore(statusStrip, quickbar.nextSibling);

            var reviewStrip = document.createElement('div');
            reviewStrip.className = 'dm-ns-review-strip';
            reviewStrip.innerHTML = '<div class="dm-ns-review-item"><span>Selected Mode</span><strong class="dm-ns-mode-value">Checking...</strong></div><div class="dm-ns-review-item"><span>Custom Entries</span><strong class="dm-ns-count-value">Checking...</strong></div><div class="dm-ns-review-item"><span>Before Saving</span><strong>Confirm each hostname is active and typed correctly</strong></div>';
            statusStrip.parentNode.insertBefore(reviewStrip, statusStrip.nextSibling);

            var liveReview = document.createElement('div');
            liveReview.className = 'dm-ns-live-review';
            liveReview.innerHTML = '<div class="dm-ns-live-review-header"><div><strong>Review nameserver values</strong><span>This updates as you type and does not change the existing save behavior.</span></div><em class="dm-ns-live-review-mode">Checking...</em></div><div class="dm-ns-live-list"><div class="dm-ns-live-empty">Checking current nameserver entries...</div></div>';
            reviewStrip.parentNode.insertBefore(liveReview, reviewStrip.nextSibling);

            var actionPanel = document.createElement('div');
            actionPanel.className = 'dm-ns-action-panel';
            actionPanel.innerHTML = '<div><strong>Ready to save?</strong><span>Review the selected mode and nameserver values, then use the existing Save Changes button below.</span></div><em class="dm-ns-save-state">Current values loaded</em>';
            liveReview.parentNode.insertBefore(actionPanel, liveReview.nextSibling);
        }

        var checks = firstForm ? firstForm.querySelectorAll('.form-check') : [];
        if (checks.length && !tab.querySelector('.dm-ns-choice-grid')) {
            var choiceGrid = document.createElement('div');
            choiceGrid.className = 'dm-ns-choice-grid';
            checks[0].parentNode.insertBefore(choiceGrid, checks[0]);
            for (var k = 0; k < checks.length; k++) {
                choiceGrid.appendChild(checks[k]);
                if (!checks[k].querySelector('.dm-ns-choice-note')) {
                    var noteText = k === 0
                        ? 'Best when DomainMonger should manage the standard nameserver set for this domain.'
                        : 'Use this when the domain should point to custom DNS hosting or private nameservers.';
                    var choiceNote = document.createElement('span');
                    choiceNote.className = 'dm-ns-choice-note';
                    choiceNote.textContent = noteText;
                    checks[k].appendChild(choiceNote);
                }
            }
        }

        if (firstForm && !firstForm.querySelector('.dm-ns-form-grid')) {
            var formGroups = firstForm.querySelectorAll('.form-group.row');
            if (formGroups.length) {
                var formGrid = document.createElement('div');
                formGrid.className = 'dm-ns-form-grid';
                formGroups[0].parentNode.insertBefore(formGrid, formGroups[0]);
                var caption = document.createElement('div');
                caption.className = 'dm-ns-form-caption';
                caption.innerHTML = '<strong>Custom nameserver entries</strong><p>Enter the full nameserver hostname, such as ns1.example.com. Leave unused fields blank.</p>';
                formGrid.appendChild(caption);
                var currentMode = document.createElement('div');
                currentMode.className = 'dm-ns-current-mode';
                currentMode.innerHTML = '<strong>Current selection:</strong> <span class="dm-ns-mode-value">Checking...</span><small class="dm-ns-custom-count-note">Checking custom nameserver entries...</small>';
                formGrid.appendChild(currentMode);
                for (var n = 0; n < formGroups.length; n++) {
                    formGrid.appendChild(formGroups[n]);
                }
            }

            var nsInputs = firstForm.querySelectorAll('input[name^="ns"]');
            for (var p = 0; p < nsInputs.length; p++) {
                var input = nsInputs[p];
                var name = input.getAttribute('name') || '';
                var match = name.match(/^ns([1-5])$/);
                if (!match) {
                    continue;
                }
                if (!input.getAttribute('placeholder')) {
                    input.setAttribute('placeholder', 'ns' + match[1] + '.' + domainName);
                }
                var group = input.closest ? input.closest('.form-group, .row') : null;
                if (group && !group.querySelector('.dm-ns-field-help')) {
                    var label = group.querySelector('label, .col-form-label');
                    if (label && (match[1] === '1' || match[1] === '2') && !label.querySelector('.dm-ns-label-badge')) {
                        label.insertAdjacentHTML('beforeend', '<span class="dm-ns-label-badge">Recommended</span>');
                    }
                    var help = document.createElement('small');
                    help.className = 'dm-ns-field-help';
                    help.textContent = match[1] === '1' || match[1] === '2'
                        ? 'Most domains should have at least two working nameservers.'
                        : 'Optional additional nameserver.';
                    input.parentNode.appendChild(help);
                }
            }

            var note = document.createElement('div');
            note.className = 'dm-ns-post-note';
            note.innerHTML = '<strong>After saving:</strong> The registrar will update the domain nameserver records. DNS propagation can take time depending on registry and resolver caching.';
            firstForm.appendChild(note);

            var bottomGuide = document.createElement('div');
            bottomGuide.className = 'dm-ns-bottom-guide';
            bottomGuide.innerHTML = '<strong>Using private nameservers?</strong><p>Create or update the private nameserver hosts first, then return here and save those hostnames as this domain's nameservers.</p>';
            firstForm.appendChild(bottomGuide);

            var saveChecklist = document.createElement('div');
            saveChecklist.className = 'dm-ns-save-checklist';
            saveChecklist.innerHTML = '<strong>Before saving nameservers</strong><ul><li>Use full hostnames, such as ns1.' + escapeHtml(domainName) + '.</li><li>Confirm custom private nameserver hosts exist before selecting custom nameservers.</li><li>Save only after the selected mode, filled-field badges, and review values look correct.</li></ul>';
            firstForm.appendChild(saveChecklist);
        }

        if (firstForm && firstForm.getAttribute('data-dm-ns-review-bound') !== '1') {
            firstForm.setAttribute('data-dm-ns-review-bound', '1');
            firstForm.addEventListener('change', function () { updateNameserverReview(firstForm); });
            firstForm.addEventListener('input', function () { updateNameserverReview(firstForm); });
        }
        updateNameserverReview(firstForm);

        document.addEventListener('click', function (event) {
            var link = event.target && event.target.closest ? event.target.closest('.dm-domain-section-link[data-dm-tab-target]') : null;
            if (!link) {
                return;
            }
            var targetId = link.getAttribute('data-dm-tab-target');
            if (!targetId) {
                return;
            }
            event.preventDefault();
            if (history && history.replaceState) {
                history.replaceState(null, '', link.getAttribute('href'));
            } else {
                window.location.hash = '#' + targetId;
            }
            activateTab(targetId);
        }, true);

        if (window.location.hash) {
            activateTab(window.location.hash.replace('#', ''));
        }
    }


    function syncCurrentDomainSection() {
        var hash = window.location.hash ? window.location.hash.replace('#', '') : '';
        if (hash && document.getElementById(hash)) {
            activateTab(hash);
            return;
        }
        var activePane = document.querySelector('#tabNameservers.active, #tabAutorenew.active, #tabReglock.active, #tabAddons.active, #tabOverview.active');
        if (activePane && activePane.id) {
            updateSectionMenuActive(activePane.id);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { convertNameservers(); convertAutoRenew(); convertRegistrarLock(); convertAddons(); syncCurrentDomainSection(); });
    } else {
        convertNameservers();
        convertAutoRenew();
        convertRegistrarLock();
        syncCurrentDomainSection();
    }
    window.addEventListener('hashchange', function () {
        var hash = window.location.hash ? window.location.hash.replace('#', '') : '';
        if (hash) {
            convertRegistrarLock();
            convertAddons();
            activateTab(hash);
        }
    });
    window.setTimeout(function () { convertNameservers(); convertAutoRenew(); convertRegistrarLock(); convertAddons(); syncCurrentDomainSection(); }, 250);
})();
</script>
HTML;
});
