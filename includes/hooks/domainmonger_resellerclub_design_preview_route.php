<?php
/**
 * DomainMonger patch 850: ResellerClub Private Nameservers Stage 1 conversion layout preview pass.
 *
 * This keeps all existing WHMCS/ResellerClub pages untouched. When a working
 * WHMCS URL includes dmdesign=1, this hook swaps the visible page body for a
 * ClouDNS-style no-sidebar workspace with section-specific preview panels, production-style action bars, final-page blueprints, a rollout-ready conversion queue, and a Private Nameservers Stage 1 conversion layout plan.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaFooterOutput', 1, function ($vars) {
    // Patch 1005: this old design-preview shell must not hijack live converted pages.
    // The converted main links use dmconverted/dmsection and should render the real
    // WHMCS domain details tabs with the live conversion hooks, not this preview/blueprint page.
    if (!empty($_GET['dmconverted']) || !empty($_GET['dmsection'])) {
        return '';
    }

    if (empty($_GET['dmdesign']) || (string) $_GET['dmdesign'] !== '1') {
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

    $requestedDomainId = 0;
    if (isset($_GET['domainid'])) {
        $requestedDomainId = (int) $_GET['domainid'];
    } elseif (isset($_GET['id'])) {
        $requestedDomainId = (int) $_GET['id'];
    }

    $requestedDomainName = '';
    if (!empty($_GET['domain'])) {
        $requestedDomainName = trim((string) $_GET['domain']);
    }

    $domain = null;
    $domains = [];
    $error = '';

    try {
        $domains = Capsule::table('tbldomains')
            ->select('id', 'domain', 'status', 'registrar', 'expirydate', 'nextduedate', 'donotrenew')
            ->where('userid', $clientId)
            ->orderBy('domain', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'domain' => (string) $row->domain,
                    'status' => (string) $row->status,
                    'registrar' => (string) $row->registrar,
                    'expirydate' => (string) $row->expirydate,
                    'nextduedate' => (string) $row->nextduedate,
                    'donotrenew' => (int) $row->donotrenew,
                ];
            })
            ->all();

        if ($requestedDomainId > 0) {
            $domain = Capsule::table('tbldomains')
                ->select('id', 'domain', 'status', 'registrar', 'expirydate', 'nextduedate', 'donotrenew')
                ->where('userid', $clientId)
                ->where('id', $requestedDomainId)
                ->first();
        }

        if (!$domain && $requestedDomainName !== '') {
            $domain = Capsule::table('tbldomains')
                ->select('id', 'domain', 'status', 'registrar', 'expirydate', 'nextduedate', 'donotrenew')
                ->where('userid', $clientId)
                ->where('domain', $requestedDomainName)
                ->first();
        }

        if (!$domain) {
            $error = 'Choose a domain to preview the new ResellerClub management design.';
        }
    } catch (\Throwable $e) {
        $error = 'The design preview could not load the domain information.';
    }

    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    $domainId = $domain ? (int) $domain->id : 0;
    $domainName = $domain ? (string) $domain->domain : '';
    $status = $domain ? (string) $domain->status : '';
    $registrar = $domain ? (string) $domain->registrar : '';
    $expiryDate = $domain ? (string) $domain->expirydate : '';
    $nextDueDate = $domain ? (string) $domain->nextduedate : '';
    $autoRenew = $domain ? ((int) $domain->donotrenew === 1 ? 'Disabled' : 'Enabled') : '';

    $actions = [];
    $activeKey = 'overview';
    if (!empty($_GET['dmpanel'])) {
        $activeKey = preg_replace('/[^a-z0-9_-]/i', '', strtolower((string) $_GET['dmpanel']));
    } else {
        $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
        $requestAction = strtolower((string) ($_GET['action'] ?? ''));

        if ($scriptName === 'dnsmanagement.php') {
            $activeKey = 'dns';
        } elseif ($scriptName === 'domainforwarding.php') {
            $activeKey = 'forwarding';
        } elseif ($scriptName === 'emailmanagement.php') {
            $activeKey = 'emailforwarding';
        } elseif ($requestAction === 'domaincontacts') {
            $activeKey = 'whois';
        } elseif ($requestAction === 'domaingetepp') {
            $activeKey = 'epp';
        } elseif ($requestAction === 'childns') {
            $activeKey = 'privatens';
        } elseif ($requestAction === 'dnssec') {
            $activeKey = 'dnssec';
        } elseif ($requestAction === 'domaindetails') {
            $activeKey = 'overview';
        }
    }

    if ($domainId > 0) {
        $encodedDomain = rawurlencode($domainName);
        $makeClientAreaPreview = function ($panel, $extra, $hash = '') use ($domainId) {
            return 'clientarea.php?action=domaindetails&id=' . $domainId . '&dmdesign=1&dmpanel=' . rawurlencode($panel) . $extra . $hash;
        };
        $actions = [
            [
                'key' => 'overview',
                'label' => 'Overview',
                'group' => 'Domain',
                'description' => 'Review the domain status, renewal dates, and core management shortcuts.',
                'previewUrl' => 'clientarea.php?action=domaindetails&id=' . $domainId . '&dmdesign=1&dmpanel=overview',
                'legacyUrl' => 'clientarea.php?action=domaindetails&id=' . $domainId,
                'kind' => 'navy',
            ],
            [
                'key' => 'autorenew',
                'label' => 'Auto Renew',
                'group' => 'Domain',
                'description' => 'Preview the renewal-control section without using the old side menu.',
                'previewUrl' => $makeClientAreaPreview('autorenew', '', '#tabAutorenew'),
                'legacyUrl' => 'clientarea.php?action=domaindetails&id=' . $domainId . '#tabAutorenew',
                'kind' => 'navy',
            ],
            [
                'key' => 'nameservers',
                'label' => 'Nameservers',
                'group' => 'DNS',
                'description' => 'Preview a cleaner nameserver management section.',
                'previewUrl' => $makeClientAreaPreview('nameservers', '', '#tabNameservers'),
                'legacyUrl' => 'clientarea.php?action=domaindetails&id=' . $domainId . '#tabNameservers',
                'kind' => 'orange',
            ],
            [
                'key' => 'reglock',
                'label' => 'Registrar Lock',
                'group' => 'Security',
                'description' => 'Preview the registrar-lock section in the new horizontal-menu layout.',
                'previewUrl' => $makeClientAreaPreview('reglock', '', '#tabReglock'),
                'legacyUrl' => 'clientarea.php?action=domaindetails&id=' . $domainId . '#tabReglock',
                'kind' => 'navy',
            ],
            [
                'key' => 'addons',
                'label' => 'Addons',
                'group' => 'Domain',
                'description' => 'Preview domain addon management inside the new card layout.',
                'previewUrl' => $makeClientAreaPreview('addons', '', '#tabAddons'),
                'legacyUrl' => 'clientarea.php?action=domaindetails&id=' . $domainId . '#tabAddons',
                'kind' => 'navy',
            ],
            [
                'key' => 'whois',
                'label' => 'WHOIS Contact Info',
                'group' => 'Contacts',
                'description' => 'Preview the contact-management section using the new no-sidebar navigation.',
                'previewUrl' => 'clientarea.php?action=domaincontacts&domainid=' . $domainId . '&dmdesign=1&dmpanel=whois',
                'legacyUrl' => 'clientarea.php?action=domaincontacts&domainid=' . $domainId,
                'kind' => 'orange',
            ],
            [
                'key' => 'privatens',
                'label' => 'Private Nameservers',
                'group' => 'DNS',
                'description' => 'Preview custom/private nameserver management in the ClouDNS-style shell.',
                'previewUrl' => 'domainmanagement.php?action=childns&id=' . $domainId . '&dmdesign=1&dmpanel=privatens',
                'legacyUrl' => 'domainmanagement.php?action=childns&id=' . $domainId,
                'kind' => 'orange',
            ],
            [
                'key' => 'dns',
                'label' => 'DNS Management',
                'group' => 'DNS',
                'description' => 'Preview the DNS-management entry point in the same style as the ClouDNS tools.',
                'previewUrl' => 'dnsmanagement.php?action=managednszone&domain=' . $encodedDomain . '&domainid=' . $domainId . '&dmdesign=1&dmpanel=dns',
                'legacyUrl' => 'dnsmanagement.php?action=managednszone&domain=' . $encodedDomain . '&domainid=' . $domainId,
                'kind' => 'navy',
            ],
            [
                'key' => 'dnssec',
                'label' => 'DNSSEC Management',
                'group' => 'DNS',
                'description' => 'Preview DNSSEC management without the ResellerClub side menu.',
                'previewUrl' => 'domainmanagement.php?action=dnssec&id=' . $domainId . '&dmdesign=1&dmpanel=dnssec',
                'legacyUrl' => 'domainmanagement.php?action=dnssec&id=' . $domainId,
                'kind' => 'navy',
            ],
            [
                'key' => 'forwarding',
                'label' => 'Domain Forwarding',
                'group' => 'Forwarding',
                'description' => 'Preview domain forwarding in a cleaner card-based section.',
                'previewUrl' => 'domainforwarding.php?domainid=' . $domainId . '&dmdesign=1&dmpanel=forwarding',
                'legacyUrl' => 'domainforwarding.php?domainid=' . $domainId,
                'kind' => 'navy',
            ],
            [
                'key' => 'emailforwarding',
                'label' => 'Email Forwarding',
                'group' => 'Forwarding',
                'description' => 'Preview email forwarding using the same no-sidebar layout.',
                'previewUrl' => 'emailmanagement.php?domainid=' . $domainId . '&dmdesign=1&dmpanel=emailforwarding',
                'legacyUrl' => 'emailmanagement.php?domainid=' . $domainId,
                'kind' => 'navy',
            ],
            [
                'key' => 'epp',
                'label' => 'Get EPP Code',
                'group' => 'Security',
                'description' => 'Preview the authorization-code request section.',
                'previewUrl' => 'clientarea.php?action=domaingetepp&id=' . $domainId . '&dmdesign=1&dmpanel=epp',
                'legacyUrl' => 'clientarea.php?action=domaingetepp&id=' . $domainId,
                'kind' => 'navy',
            ],
        ];
    }
    $domainOptions = '<option value="">Choose a domain</option>';
    foreach ($domains as $row) {
        $selected = ($domainId > 0 && (int) $row['id'] === $domainId) ? ' selected' : '';
        $label = $row['domain'] . (!empty($row['status']) ? ' — ' . $row['status'] : '');
        $domainOptions .= '<option value="' . $e($row['id']) . '"' . $selected . '>' . $e($label) . '</option>';
    }

    $activeAction = null;
    foreach ($actions as $action) {
        if ($action['key'] === $activeKey) {
            $activeAction = $action;
            break;
        }
    }
    if (!$activeAction && !empty($actions)) {
        $activeAction = $actions[0];
        $activeKey = $activeAction['key'];
    }

    $menuHtml = '';
    $actionCardsHtml = '';
    foreach ($actions as $action) {
        $activeClass = ($action['key'] === $activeKey) ? ' class="dm-rc-menu-active"' : '';
        $menuHtml .= '<li><a href="' . $e($action['previewUrl']) . '"' . $activeClass . '><span class="dm-rc-menu-group">' . $e($action['group']) . '</span><span>' . $e($action['label']) . '</span></a></li>';
        $buttonClass = $action['kind'] === 'orange' ? 'dm-rc-btn-orange' : 'dm-rc-btn-navy';
        $cardClass = ($action['key'] === $activeKey) ? ' dm-rc-action-card-active' : '';
        $actionCardsHtml .= '<div class="dm-rc-action-card' . $cardClass . '"><div><span class="dm-rc-card-kicker">' . $e($action['group']) . '</span><h4>' . $e($action['label']) . '</h4><p>' . $e($action['description']) . '</p></div><div class="dm-rc-action-buttons"><a class="dm-rc-btn ' . $buttonClass . '" href="' . $e($action['previewUrl']) . '">Preview Design</a><a class="dm-rc-link-btn" href="' . $e($action['legacyUrl']) . '">Open Current</a></div></div>';
    }

    $activeLabel = $activeAction ? (string) $activeAction['label'] : 'Overview';
    $activeDescription = $activeAction ? (string) $activeAction['description'] : 'Preview the new ResellerClub domain-management design.';
    $activeLegacyUrl = $activeAction ? (string) $activeAction['legacyUrl'] : ($domainId > 0 ? 'clientarea.php?action=domaindetails&id=' . $domainId : 'clientarea.php');

    $sectionActionBarHtml = '';
    $sectionBlueprintHtml = '';
    $sectionWorkflowHtml = '';
    if ($domainId > 0) {
        $sectionActionBarHtml = '<div class="dm-rc-section-actionbar"><div><span class="dm-rc-card-kicker">Production Layout Preview</span><strong>' . $e($activeLabel) . '</strong><p>The new horizontal section navigation replaces the ResellerClub side menu for this domain-management workspace.</p></div><div class="dm-rc-section-actionbar-buttons"><span class="dm-rc-preview-badge">Design only</span><a class="dm-rc-btn dm-rc-btn-navy" href="' . $e($activeLegacyUrl) . '">Open Current Interface</a></div></div>';

        $blueprintMap = [
            'overview' => ['Use this as the domain landing page.', 'Surface status, renewal dates, and common actions first.', 'Keep detailed actions in the horizontal menu instead of sidebars.'],
            'autorenew' => ['Show renewal state as the main page status.', 'Keep the existing WHMCS renewal submit behavior.', 'Use one clear orange action for the active renewal choice.'],
            'nameservers' => ['Keep the existing nameserver fields and save flow.', 'Use a single navy-header card with cleaner field spacing.', 'Make optional nameservers feel secondary instead of cluttered.'],
            'reglock' => ['Show lock status and recommended state first.', 'Keep the current registrar lock action untouched.', 'Use warning/help text only where it supports the decision.'],
            'addons' => ['Present addons as status cards.', 'Keep existing addon activation routes unchanged.', 'Use orange only for primary addon actions.'],
            'whois' => ['Keep the current WHOIS contact tabs and save behavior.', 'Place the contact tabs inside the no-sidebar workspace.', 'Preserve the verification and 60-day lock messaging.'],
            'privatens' => ['Start final rollout here after the preview is approved.', 'Keep child nameserver create/update/delete behavior unchanged.', 'Use table-style rows like the ClouDNS interface.'],
            'dns' => ['Keep the existing ResellerClub DNS route.', 'Match the ClouDNS toolbar/table rhythm visually.', 'Avoid changing DNS record actions in this design phase.'],
            'dnssec' => ['Show DNSSEC status before actions.', 'Keep current DS/DNSSEC behavior unchanged.', 'Use the same navy-header card pattern as DNS Management.'],
            'forwarding' => ['Use a focused forwarding-rules card.', 'Keep existing destination URL behavior untouched.', 'Show active rules in a compact table layout.'],
            'emailforwarding' => ['Use the same table rhythm as Mail Forwards.', 'Keep current email forwarding behavior untouched.', 'Make add/search controls align with the ClouDNS style.'],
            'epp' => ['Keep the request behavior exactly as-is.', 'Use a focused authorization-code card.', 'Make warnings and next steps easy to understand.'],
        ];
        $blueprintItems = $blueprintMap[$activeKey] ?? $blueprintMap['overview'];
        $sectionBlueprintHtml = '<ul class="dm-rc-blueprint-list">';
        foreach ($blueprintItems as $item) {
            $sectionBlueprintHtml .= '<li>' . $e($item) . '</li>';
        }
        $sectionBlueprintHtml .= '</ul>';

        $overviewPreviewUrl = 'clientarea.php?action=domaindetails&id=' . $domainId . '&dmdesign=1&dmpanel=overview';
        $sectionWorkflowHtml = '<div class="dm-rc-production-strip"><div><span class="dm-rc-card-kicker">Final Page Direction</span><strong>Design the page around this section, not around the old side menu.</strong><p>When approved, this visual pattern can be applied to the real ' . $e($activeLabel) . ' page while preserving the current WHMCS/ResellerClub backend.</p></div><div class="dm-rc-production-actions"><a class="dm-rc-btn dm-rc-btn-orange" href="' . $e($activeLegacyUrl) . '">Open Current</a><a class="dm-rc-link-btn" href="' . $e($overviewPreviewUrl) . '">Preview Overview</a></div></div>';
    }

    $prototypeUrl = $domainId > 0 ? 'domainmanagement.php?action=childns&id=' . $domainId . '&dmdesign=1&dmpanel=privatens' : '#';
    $prototypeBannerHtml = '';
    if ($domainId > 0) {
        $prototypeBannerHtml = '<div class="dm-rc-prototype-banner"><div><span class="dm-rc-card-kicker">Converted Design Preview</span><strong>Private Nameservers uses the new no-side-menu design pattern for the domain-management section.</strong><p>This view now represents the converted design direction rather than a separate prototype launch step.</p></div><div class="dm-rc-prototype-steps"><span>1. Keep backend</span><span>2. Use converted layout</span><span>3. Continue section conversion</span></div><a class="dm-rc-btn dm-rc-btn-orange" href="' . $e($prototypeUrl) . '">View Current Design</a></div>';
    }

    $workspaceStatsHtml = '';
    if ($domainId > 0) {
        $workspaceStats = [
            ['label' => 'Status', 'value' => $status ?: '-'],
            ['label' => 'Registrar', 'value' => $registrar ?: '-'],
            ['label' => 'Auto Renew', 'value' => $autoRenew ?: '-'],
            ['label' => 'Expiry', 'value' => $expiryDate ?: '-'],
        ];
        foreach ($workspaceStats as $stat) {
            $workspaceStatsHtml .= '<div class="dm-rc-workspace-stat"><span>' . $e($stat['label']) . '</span><strong>' . $e($stat['value']) . '</strong></div>';
        }
    }

    $quickNavHtml = '';
    if ($domainId > 0) {
        foreach ($actions as $action) {
            $quickClass = ($action['key'] === $activeKey) ? ' dm-rc-quick-link-active' : '';
            $quickNavHtml .= '<a class="dm-rc-quick-link' . $quickClass . '" href="' . $e($action['previewUrl']) . '">' . $e($action['label']) . '</a>';
        }
    }

    $domainDisplay = $e($domainName);
    $sectionPreviewHtml = '';
    if ($domainId > 0) {
        $openCurrentButton = '<a class="dm-rc-btn dm-rc-btn-orange" href="' . $e($activeLegacyUrl) . '">Open Current ' . $e($activeLabel) . '</a>';
        switch ($activeKey) {
            case 'nameservers':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Nameserver Layout Preview</span><h4>Update Nameservers</h4><p>Cleaner spacing for the existing nameserver fields. Live editing stays on the current WHMCS page.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-form-grid dm-rc-form-grid-two"><label><span>Nameserver 1</span><input type="text" class="dm-rc-control-preview" value="ns1.' . $domainDisplay . '" disabled></label><label><span>Nameserver 2</span><input type="text" class="dm-rc-control-preview" value="ns2.' . $domainDisplay . '" disabled></label><label><span>Nameserver 3</span><input type="text" class="dm-rc-control-preview" placeholder="Optional" disabled></label><label><span>Nameserver 4</span><input type="text" class="dm-rc-control-preview" placeholder="Optional" disabled></label></div><div class="dm-rc-preview-footer"><span class="dm-rc-pill dm-rc-pill-warning">Design Preview Only</span><span>These fields are visual placeholders only.</span></div></div>';
                break;
            case 'privatens':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel dm-rc-section-panel-prototype"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Stage 1 Conversion Layout</span><h4>Private Nameservers</h4><p>This section is now organized as the first real no-sidebar conversion target: move the visible child-nameserver pieces into this layout while preserving every current backend action.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-ns-status-row"><div><span class="dm-rc-card-kicker">Conversion Status</span><strong>Stage 1 layout is ready</strong><p>The next real-page patch can place the current child-nameserver form inside this card layout while keeping the original route, tokens, hidden fields, and submit names.</p></div><div><span class="dm-rc-card-kicker">Backend Rule</span><strong>Move markup only</strong><p>Keep tokens, hidden fields, create/update/delete routes, and registrar logic exactly as-is.</p></div></div><div class="dm-rc-ns-prototype-grid"><div class="dm-rc-ns-create-card"><div class="dm-rc-subcard-head"><div><span class="dm-rc-card-kicker">Create Private Nameserver</span><strong>New host record</strong></div><span class="dm-rc-pill dm-rc-pill-warning">Preview</span></div><label><span>Hostname</span><div class="dm-rc-inline-host"><input type="text" class="dm-rc-control-preview" value="ns1" disabled><strong>.' . $domainDisplay . '</strong></div></label><label><span>IP Address</span><input type="text" class="dm-rc-control-preview" placeholder="192.0.2.10" disabled></label><div class="dm-rc-button-preview-row"><button type="button" class="dm-rc-btn dm-rc-btn-orange" disabled>Create Nameserver</button><a class="dm-rc-link-btn" href="' . $e($activeLegacyUrl) . '">Use Current Form</a></div><div class="dm-rc-preview-footer dm-rc-preview-footer-tight"><span class="dm-rc-pill dm-rc-pill-info">Placement Target</span><span>The existing create form should land here during real conversion.</span></div></div><div class="dm-rc-ns-table-card"><div class="dm-rc-subcard-head"><div><span class="dm-rc-card-kicker">Existing Private Nameservers</span><strong>Manage host records</strong></div><span class="dm-rc-pill dm-rc-pill-info">Table Layout</span></div><div class="dm-rc-mini-table dm-rc-mini-table-polished dm-rc-mini-table-actions"><div class="dm-rc-mini-row dm-rc-mini-head"><span>Hostname</span><span>IP Address</span><span>Status</span><span>Actions</span></div><div class="dm-rc-mini-row"><span>ns1.' . $domainDisplay . '</span><span class="dm-rc-muted-text">Existing IP from current page</span><span><span class="dm-rc-pill dm-rc-pill-info">Active</span></span><span class="dm-rc-mini-actions"><button type="button" class="dm-rc-mini-btn dm-rc-mini-btn-navy" disabled>Update</button><button type="button" class="dm-rc-mini-btn dm-rc-mini-btn-danger" disabled>Delete</button></span></div><div class="dm-rc-mini-row"><span>ns2.' . $domainDisplay . '</span><span class="dm-rc-muted-text">Existing IP from current page</span><span><span class="dm-rc-pill dm-rc-pill-info">Active</span></span><span class="dm-rc-mini-actions"><button type="button" class="dm-rc-mini-btn dm-rc-mini-btn-navy" disabled>Update</button><button type="button" class="dm-rc-mini-btn dm-rc-mini-btn-danger" disabled>Delete</button></span></div></div></div></div><div class="dm-rc-live-map-card"><div class="dm-rc-subcard-head"><div><span class="dm-rc-card-kicker">Live Conversion Plan</span><strong>Move the working page into this design without changing behavior</strong></div><span class="dm-rc-pill dm-rc-pill-info">Real Page Staging</span></div><div class="dm-rc-live-map-grid"><div><span>Current page output</span><strong>Capture existing childns forms</strong><p>Use the same submitted fields, tokens, hidden inputs, and action URLs.</p></div><div><span>New layout target</span><strong>Place forms inside cards</strong><p>Create form on the left, existing host rows on the right, notices above.</p></div><div><span>Safety check</span><strong>No registrar logic changes</strong><p>Only move visible markup. Do not change create/update/delete handling.</p></div></div></div><div class="dm-rc-first-scope-card"><div class="dm-rc-subcard-head"><div><span class="dm-rc-card-kicker">First Real Patch Scope</span><strong>Convert the Private Nameservers page visually, not functionally</strong></div><span class="dm-rc-pill dm-rc-pill-info">Staging Target</span></div><div class="dm-rc-first-scope-grid"><div><span>Keep</span><strong>Current childns actions</strong><p>Use the existing form posts, hidden fields, tokens, and registrar responses.</p></div><div><span>Move</span><strong>Visible page pieces</strong><p>Place the notice, create form, and existing host rows into the no-sidebar cards.</p></div><div><span>Do not change</span><strong>Backend behavior</strong><p>No registrar logic, submit names, route names, or validation changes.</p></div></div></div><div class="dm-rc-stage-one-card"><div class="dm-rc-subcard-head"><div><span class="dm-rc-card-kicker">Stage 1 Real Page Build</span><strong>Convert Private Nameservers visually first</strong></div><span class="dm-rc-pill dm-rc-pill-info">Ready To Build</span></div><div class="dm-rc-stage-one-grid"><div><span>1. Capture</span><strong>Use current childns output</strong><p>Keep current notices, forms, hidden fields, token fields, and action URLs.</p></div><div><span>2. Place</span><strong>Move visible pieces into cards</strong><p>Notice at top, create form on left, existing host records in the table card.</p></div><div><span>3. Verify</span><strong>Test all actions after install</strong><p>Create, update, delete, and error/success messages must behave exactly like the current page.</p></div></div></div><div class="dm-rc-next-conversion-card"><div><span class="dm-rc-card-kicker">Before Replacing The Real Page</span><strong>Test checklist</strong></div><ol><li>Open the current Private Nameservers page and confirm create/update/delete still works.</li><li>Install the first real conversion patch built from the confirmed restore point.</li><li>Test create, update, and delete with a safe host value.</li><li>Create a restore point only after all three actions are confirmed.</li></ol></div><div class="dm-rc-form-placement-map"><div><span class="dm-rc-card-kicker">Real Conversion Placement Map</span><strong>Where the current page pieces should go</strong></div><div class="dm-rc-placement-grid"><div><span>Notice Text</span><strong>Top info card</strong></div><div><span>Create Form</span><strong>Left create card</strong></div><div><span>Existing Hosts</span><strong>Right table card</strong></div><div><span>Save/Delete Actions</span><strong>Same current forms</strong></div></div></div><div class="dm-rc-preview-footer"><span class="dm-rc-pill dm-rc-pill-info">No Side Menu</span><span>The horizontal menu is the section navigation. This preview is now ready for the first real Private Nameservers visual conversion patch.</span></div></div>';
                break;
            case 'whois':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Contact Layout Preview</span><h4>WHOIS Contact Info</h4><p>Tabs and contact forms can be placed inside the same clean card structure while preserving the current save behavior.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-contact-preview"><div><span>Registrant</span><strong>Primary owner contact</strong></div><div><span>Admin</span><strong>Administrative contact</strong></div><div><span>Billing</span><strong>Billing contact</strong></div><div><span>Technical</span><strong>Technical contact</strong></div></div><div class="dm-rc-preview-footer"><span class="dm-rc-pill dm-rc-pill-info">Existing Form Preserved</span><span>The final page would reuse the current WHMCS fields and submit flow.</span></div></div>';
                break;
            case 'autorenew':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Renewal Layout Preview</span><h4>Auto Renew</h4><p>Show renewal status clearly with one primary action area instead of scattered controls.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-status-strip"><div><span>Status</span><strong>' . $e($autoRenew ?: '-') . '</strong></div><div><span>Expiry Date</span><strong>' . $e($expiryDate ?: '-') . '</strong></div><div><span>Next Due</span><strong>' . $e($nextDueDate ?: '-') . '</strong></div></div><div class="dm-rc-preview-footer"><span class="dm-rc-pill dm-rc-pill-warning">Preview Only</span><span>Renewal changes still happen on the current page.</span></div></div>';
                break;
            case 'reglock':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Security Layout Preview</span><h4>Registrar Lock</h4><p>Make lock status and the next action easier to understand.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-status-strip"><div><span>Domain</span><strong>' . $domainDisplay . '</strong></div><div><span>Protection</span><strong>Registrar Lock</strong></div><div><span>Recommended</span><strong>Keep enabled</strong></div></div><div class="dm-rc-preview-footer"><span class="dm-rc-pill dm-rc-pill-info">Security Section</span><span>The current toggle behavior remains unchanged.</span></div></div>';
                break;
            case 'dns':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">DNS Layout Preview</span><h4>DNS Management</h4><p>This can visually match the ClouDNS control style while still opening the existing ResellerClub DNS tool.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-toolbar-preview"><input type="text" class="dm-rc-control-preview" placeholder="Search DNS records" disabled><select class="dm-rc-control-preview" disabled><option>All Records</option></select><button type="button" class="dm-rc-btn dm-rc-btn-orange" disabled>+ Add Record</button></div><div class="dm-rc-preview-footer"><span class="dm-rc-pill dm-rc-pill-warning">Design Preview Only</span><span>DNS record editing stays on the current interface.</span></div></div>';
                break;
            case 'dnssec':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">DNSSEC Layout Preview</span><h4>DNSSEC Management</h4><p>Use the same card/header pattern for DNSSEC status and DS record actions.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-empty-preview"><strong>DNSSEC status and DS records would appear here.</strong><span>The final design would wrap the current WHMCS/ResellerClub output.</span></div></div>';
                break;
            case 'forwarding':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Forwarding Layout Preview</span><h4>Domain Forwarding</h4><p>Clean card layout for forwarding rules and destination URLs.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-toolbar-preview"><input type="text" class="dm-rc-control-preview" placeholder="Forward ' . $domainDisplay . ' to..." disabled><button type="button" class="dm-rc-btn dm-rc-btn-orange" disabled>Add Forward</button></div><div class="dm-rc-preview-footer"><span class="dm-rc-pill dm-rc-pill-warning">Preview Only</span><span>Forwarding actions remain on the current page.</span></div></div>';
                break;
            case 'emailforwarding':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Email Forwarding Layout Preview</span><h4>Email Forwarding</h4><p>Use the same toolbar/table rhythm as the ClouDNS tools.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-toolbar-preview"><input type="text" class="dm-rc-control-preview" placeholder="Alias" disabled><input type="text" class="dm-rc-control-preview" placeholder="Destination email" disabled><button type="button" class="dm-rc-btn dm-rc-btn-orange" disabled>Add Forward</button></div><div class="dm-rc-preview-footer"><span class="dm-rc-pill dm-rc-pill-warning">Preview Only</span><span>Email forwarding actions remain on the current page.</span></div></div>';
                break;
            case 'epp':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Transfer Code Layout Preview</span><h4>Get EPP Code</h4><p>Simple focused card for requesting the domain authorization code.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-empty-preview"><strong>Authorization code request area.</strong><span>The real request button and verification behavior stay on the current page.</span></div></div>';
                break;
            case 'addons':
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Addons Layout Preview</span><h4>Domain Addons</h4><p>Addon status cards can replace the older stacked WHMCS layout.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-addon-grid"><div><strong>DNS Management</strong><span>Manage DNS zone options</span></div><div><strong>Email Forwarding</strong><span>Manage forwarding options</span></div><div><strong>ID Protection</strong><span>Show availability/status</span></div></div></div>';
                break;
            case 'overview':
            default:
                $sectionPreviewHtml = '<div class="dm-rc-section-panel"><div class="dm-rc-panel-head"><div><span class="dm-rc-card-kicker">Overview Layout Preview</span><h4>Domain Overview</h4><p>A clean landing view for this domain with status, dates, and common actions.</p></div>' . $openCurrentButton . '</div><div class="dm-rc-status-strip"><div><span>Domain</span><strong>' . $domainDisplay . '</strong></div><div><span>Status</span><strong>' . $e($status ?: '-') . '</strong></div><div><span>Registrar</span><strong>' . $e($registrar ?: '-') . '</strong></div></div><div class="dm-rc-preview-footer"><span class="dm-rc-pill dm-rc-pill-info">Design Lab</span><span>Use the menu above to preview each section without side menus.</span></div></div>';
                break;
        }
    }

    $panelHtml = '<div id="dm-rc-route-preview" class="dm-rc-preview-wrap" style="display:none">'
        . '<div class="dm-rc-preview-title"><div><div class="dm-rc-preview-eyebrow">Manage Domain</div><h2>' . ($domainName !== '' ? $e($domainName) : 'ResellerClub Domain Design Preview') . '</h2><div class="dm-rc-preview-subtitle">No-side-menu design preview for the existing ResellerClub domain tools.</div></div>'
        . ($domainId > 0 ? '<div class="dm-rc-title-actions"><a class="dm-rc-btn dm-rc-btn-navy" href="' . $e($activeLegacyUrl) . '">Open Current Interface</a></div>' : '')
        . '</div>'
        . ($error !== '' ? '<div class="dm-rc-warning">' . $e($error) . '</div>' : '')
        . '<div class="dm-rc-preview-selector"><form method="get" action="clientarea.php"><input type="hidden" name="action" value="domaindetails"><input type="hidden" name="dmdesign" value="1"><input type="hidden" name="dmpanel" value="overview"><label for="dm-rc-domainid">Switch Domain</label><select id="dm-rc-domainid" name="id" class="form-control" onchange="this.form.submit()">' . $domainOptions . '</select><button type="submit" class="dm-rc-btn dm-rc-btn-orange">Open Preview</button></form></div>'
        . '<div class="dm-rc-mode-strip"><span class="dm-rc-mode-pill dm-rc-mode-pill-active">Design Preview</span><span class="dm-rc-mode-pill">No Side Menu</span><span class="dm-rc-mode-pill">Existing Actions Preserved</span><span class="dm-rc-mode-pill">Full Section Conversion</span><span class="dm-rc-mode-pill">Conversion Ready</span><span class="dm-rc-mode-pill">Private NS Converted</span><span class="dm-rc-mode-pill">Form Placement Ready</span><span class="dm-rc-mode-pill">Live Conversion Plan</span><span class="dm-rc-mode-pill">Real Page Staging</span><span class="dm-rc-mode-pill">Stage 1 Ready</span></div>';

    if ($domainId > 0) {
        $panelHtml .= '<div class="dm-rc-workspace-bar"><div><span class="dm-rc-card-kicker">Current Section</span><strong>Manage Domain: ' . $e($activeLabel) . '</strong><p>' . $e($activeDescription) . '</p></div><div class="dm-rc-workspace-stats">' . $workspaceStatsHtml . '</div></div>'
            . '<ul class="dm-rc-menu" aria-label="Domain management sections">' . $menuHtml . '</ul>'
            . '<div class="dm-rc-card dm-rc-section-card"><div class="dm-rc-card-header"><h3>Manage Domain: ' . $e($activeLabel) . '</h3><span>' . $e($domainName) . '</span></div><div class="dm-rc-card-body">'
            . '<div class="dm-rc-workspace-grid"><div class="dm-rc-workspace-main">'
            . $sectionActionBarHtml
            . '<div class="dm-rc-info dm-rc-info-compact"><strong>No side menus.</strong> This preview uses the horizontal menu above as the domain-management navigation while the current WHMCS/ResellerClub pages remain available.</div>'
            . $prototypeBannerHtml
            . $sectionWorkflowHtml
            . $sectionPreviewHtml
            . '</div><div class="dm-rc-workspace-rail"><div class="dm-rc-rail-card"><span class="dm-rc-card-kicker">Domain Snapshot</span><h4>' . $e($domainName) . '</h4><dl><dt>Status</dt><dd>' . $e($status ?: '-') . '</dd><dt>Auto Renew</dt><dd>' . $e($autoRenew ?: '-') . '</dd><dt>Expiry Date</dt><dd>' . $e($expiryDate ?: '-') . '</dd><dt>Next Due</dt><dd>' . $e($nextDueDate ?: '-') . '</dd></dl><a class="dm-rc-btn dm-rc-btn-orange dm-rc-rail-full" href="' . $e($activeLegacyUrl) . '">Open Current ' . $e($activeLabel) . '</a></div><div class="dm-rc-rail-card dm-rc-blueprint-card"><span class="dm-rc-card-kicker">Final Page Blueprint</span><h4>' . $e($activeLabel) . '</h4>' . $sectionBlueprintHtml . '</div></div></div>'
            . '</div></div>'
            . '<div class="dm-rc-card dm-rc-quick-card"><div class="dm-rc-card-header"><h3>Section Navigation</h3><span>Preview links</span></div><div class="dm-rc-card-body"><div class="dm-rc-quick-nav">' . $quickNavHtml . '</div></div></div>'
            . '<div class="dm-rc-card dm-rc-implementation-card"><div class="dm-rc-card-header"><h3>Final Conversion Map</h3><span>Design-only rollout</span></div><div class="dm-rc-card-body"><div class="dm-rc-implementation-grid"><div><span class="dm-rc-card-kicker">1. Keep backend</span><strong>Use current WHMCS routes</strong><p>Existing forms, tokens, and registrar actions stay in place.</p></div><div><span class="dm-rc-card-kicker">2. Replace navigation</span><strong>Horizontal menu only</strong><p>This section no longer needs the old ResellerClub side menus.</p></div><div><span class="dm-rc-card-kicker">3. Roll out safely</span><strong>One page at a time</strong><p>Start with Private Nameservers after the design preview is approved.</p></div></div></div></div>'
            . '<div class="dm-rc-card dm-rc-next-real-card"><div class="dm-rc-card-header"><h3>Next Real Conversion</h3><span>Private Nameservers</span></div><div class="dm-rc-card-body"><div class="dm-rc-next-real-grid"><div><span class="dm-rc-card-kicker">Source</span><strong>Current childns page</strong><p>Use the existing working ResellerClub output as the only source for forms and actions.</p></div><div><span class="dm-rc-card-kicker">Destination</span><strong>This no-sidebar layout</strong><p>Move visible form pieces into the create card and host-record table.</p></div><div><span class="dm-rc-card-kicker">Rule</span><strong>Design only</strong><p>Do not alter registrar calls, hidden fields, CSRF tokens, or submit names.</p></div></div></div></div>'
            . '<div class="dm-rc-card dm-rc-rollout-card"><div class="dm-rc-card-header"><h3>Rollout Readiness</h3><span>Suggested real-page order</span></div><div class="dm-rc-card-body"><div class="dm-rc-rollout-hero"><div><span class="dm-rc-card-kicker">Best first real conversion</span><strong>Private Nameservers</strong><p>Smallest page, recently tested, and the cleanest place to prove the no-side-menu design while preserving the current child-nameserver actions.</p></div><a class="dm-rc-btn dm-rc-btn-orange" href="domainmanagement.php?action=childns&id=' . $domainId . '&dmdesign=1&dmpanel=privatens">Preview Private Nameservers</a></div><div class="dm-rc-rollout-grid"><div><span>1</span><strong>Private Nameservers</strong><p>Convert visual layout first.</p></div><div><span>2</span><strong>Nameservers</strong><p>Apply same card/field rhythm.</p></div><div><span>3</span><strong>Auto Renew</strong><p>Use one clear status/action card.</p></div><div><span>4</span><strong>Registrar Lock</strong><p>Keep security status front and center.</p></div></div><div class="dm-rc-rollout-note"><strong>Conversion rule:</strong> pull the existing working form/actions into this layout page-by-page. Do not replace registrar logic, hidden fields, tokens, or submit behavior. Private Nameservers is now the prototype page for that conversion.</div></div></div>'
            . '<div class="dm-rc-card dm-rc-tools-card"><div class="dm-rc-card-header"><h3>All Domain Tools</h3><span>Design preview and current links</span></div><div class="dm-rc-card-body"><div class="dm-rc-action-grid">' . $actionCardsHtml . '</div></div></div>';
    } else {
        $panelHtml .= '<div class="dm-rc-empty-state">Choose a domain above to preview the new ResellerClub management design shell.</div>';
    }

    $panelHtml .= '</div>';

    return <<<'HTML'
<style>
body.whmcsbody .dm-rc-preview-wrap {
    --dm-rc-navy: #163a5f;
    --dm-rc-hover-navy: #214e7a;
    --dm-rc-text: #293f56;
    --dm-rc-muted: #60738a;
    --dm-rc-border: rgba(17, 43, 77, 0.14);
    --dm-rc-border-soft: rgba(17, 43, 77, 0.08);
    --dm-rc-shadow: 0 2px 8px rgba(17, 43, 77, 0.055);
    --dm-rc-orange: #f58220;
    --dm-rc-orange-hover: #d8741f;
    --dm-rc-warning: #fff8e5;
    color: var(--dm-rc-text);
}
body.whmcsbody .dm-rc-preview-title {
    align-items: flex-end;
    display: flex;
    gap: 16px;
    justify-content: space-between;
    margin: 0 0 16px;
}
body.whmcsbody .dm-rc-preview-title h2 {
    color: var(--dm-rc-navy) !important;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}
body.whmcsbody .dm-rc-preview-subtitle {
    color: var(--dm-rc-muted);
    font-size: 14px;
    margin-top: 4px;
}
body.whmcsbody .dm-rc-preview-eyebrow {
    color: var(--dm-rc-orange-hover);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.06em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody.dm-rc-design-preview-active .sidebar,
body.whmcsbody.dm-rc-design-preview-active .secondary-sidebar,
body.whmcsbody.dm-rc-design-preview-active .panel-sidebar,
body.whmcsbody.dm-rc-design-preview-active .dm-client-area-sidebar,
body.whmcsbody.dm-rc-design-preview-active aside,
body.whmcsbody.dm-rc-design-preview-active [class*="sidebar"] {
    display: none !important;
}
body.whmcsbody.dm-rc-design-preview-active #main-body .primary-content,
body.whmcsbody.dm-rc-design-preview-active #main-body .main-content,
body.whmcsbody.dm-rc-design-preview-active #main-body .col-md-9,
body.whmcsbody.dm-rc-design-preview-active #main-body .col-lg-9,
body.whmcsbody.dm-rc-design-preview-active #main-body .col-xl-9 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
}
body.whmcsbody .dm-rc-preview-selector,
body.whmcsbody .dm-rc-workspace-bar,
body.whmcsbody .dm-rc-card,
body.whmcsbody .dm-rc-empty-state {
    background: #ffffff;
    border: 1px solid var(--dm-rc-border);
    border-radius: 8px;
    box-shadow: var(--dm-rc-shadow);
    margin-bottom: 14px;
}
body.whmcsbody .dm-rc-preview-selector { padding: 12px 14px; }
body.whmcsbody .dm-rc-title-actions { display: flex; gap: 10px; flex-shrink: 0; }
body.whmcsbody .dm-rc-workspace-bar {
    align-items: center;
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(240px, 1fr) minmax(360px, 1.4fr);
    padding: 14px 16px;
}
body.whmcsbody .dm-rc-workspace-bar strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 18px;
    line-height: 1.2;
}
body.whmcsbody .dm-rc-workspace-bar p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.35;
    margin: 4px 0 0;
}
body.whmcsbody .dm-rc-workspace-stats {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
}
body.whmcsbody .dm-rc-workspace-stat {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 7px;
    padding: 10px;
}
body.whmcsbody .dm-rc-workspace-stat span {
    color: var(--dm-rc-muted);
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.03em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody .dm-rc-workspace-stat strong {
    color: var(--dm-rc-text);
    display: block;
    font-size: 13px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
body.whmcsbody .dm-rc-preview-selector form {
    align-items: center;
    display: flex;
    gap: 10px;
    margin: 0;
}
body.whmcsbody .dm-rc-preview-selector label {
    color: var(--dm-rc-navy);
    font-weight: 700;
    margin: 0;
    white-space: nowrap;
}
body.whmcsbody .dm-rc-preview-selector select.form-control {
    border-color: rgba(17, 43, 77, 0.18) !important;
    border-radius: 6px !important;
    box-shadow: none !important;
    color: var(--dm-rc-text) !important;
    max-width: 360px;
}
body.whmcsbody .dm-rc-btn,
body.whmcsbody .dm-rc-btn:visited {
    align-items: center;
    border: 0 !important;
    border-radius: 6px !important;
    display: inline-flex;
    font-size: 13px;
    font-weight: 700;
    gap: 6px;
    justify-content: center;
    line-height: 1.2;
    min-height: 38px;
    padding: 10px 14px !important;
    text-decoration: none !important;
    white-space: nowrap;
}
body.whmcsbody .dm-rc-btn-orange { background: var(--dm-rc-orange) !important; color: #ffffff !important; }
body.whmcsbody .dm-rc-btn-orange:hover,
body.whmcsbody .dm-rc-btn-orange:focus { background: var(--dm-rc-orange-hover) !important; color: #ffffff !important; text-decoration: none !important; }
body.whmcsbody .dm-rc-btn-navy { background: var(--dm-rc-navy) !important; color: #ffffff !important; }
body.whmcsbody .dm-rc-btn-navy:hover,
body.whmcsbody .dm-rc-btn-navy:focus { background: var(--dm-rc-hover-navy) !important; color: #ffffff !important; text-decoration: none !important; }
body.whmcsbody .dm-rc-menu {
    align-items: stretch;
    background: #ffffff;
    border: 1px solid var(--dm-rc-border);
    border-radius: 8px;
    box-shadow: var(--dm-rc-shadow);
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    margin: 0 0 14px 0;
    min-height: 42px;
    overflow: hidden;
    padding: 0;
}
body.whmcsbody .dm-rc-menu li { margin: 0; padding: 0; }
body.whmcsbody .dm-rc-menu a {
    align-items: center;
    border-bottom: 3px solid transparent;
    color: var(--dm-rc-text) !important;
    display: flex;
    flex-direction: column;
    font-size: 13px;
    font-weight: 700;
    justify-content: center;
    min-height: 50px;
    padding: 7px 15px;
    text-decoration: none !important;
}
body.whmcsbody .dm-rc-menu a.dm-rc-menu-active,
body.whmcsbody .dm-rc-menu a.dm-rc-menu-active:hover,
body.whmcsbody .dm-rc-menu a.dm-rc-menu-active:focus {
    background: var(--dm-rc-orange) !important;
    border-bottom-color: var(--dm-rc-orange-hover);
    color: #ffffff !important;
    text-decoration: none !important;
}
body.whmcsbody .dm-rc-menu a:hover,
body.whmcsbody .dm-rc-menu a:focus {
    background: #fff7ef !important;
    border-bottom-color: var(--dm-rc-orange);
    color: var(--dm-rc-orange-hover) !important;
    text-decoration: none !important;
}
body.whmcsbody .dm-rc-menu a .dm-rc-menu-group {
    color: var(--dm-rc-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.03em;
    line-height: 1.1;
    text-transform: uppercase;
}
body.whmcsbody .dm-rc-menu a.dm-rc-menu-active .dm-rc-menu-group,
body.whmcsbody .dm-rc-menu a.dm-rc-menu-active:hover .dm-rc-menu-group,
body.whmcsbody .dm-rc-menu a.dm-rc-menu-active:focus .dm-rc-menu-group {
    color: rgba(255, 255, 255, 0.82);
}
body.whmcsbody .dm-rc-menu a:hover .dm-rc-menu-group,
body.whmcsbody .dm-rc-menu a:focus .dm-rc-menu-group {
    color: var(--dm-rc-orange-hover);
}
body.whmcsbody .dm-rc-workspace-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 1fr) 280px;
}
body.whmcsbody .dm-rc-workspace-main { min-width: 0; }
body.whmcsbody .dm-rc-workspace-rail { min-width: 0; }
body.whmcsbody .dm-rc-rail-card {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    padding: 14px;
}
body.whmcsbody .dm-rc-rail-card h4 {
    color: var(--dm-rc-navy) !important;
    font-size: 16px;
    font-weight: 800;
    margin: 0 0 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
body.whmcsbody .dm-rc-rail-card dl { margin: 0 0 14px; }
body.whmcsbody .dm-rc-rail-card dt {
    color: var(--dm-rc-muted);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.03em;
    margin-top: 10px;
    text-transform: uppercase;
}
body.whmcsbody .dm-rc-rail-card dd {
    color: var(--dm-rc-text);
    font-size: 13px;
    font-weight: 700;
    margin: 2px 0 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
body.whmcsbody .dm-rc-rail-full { width: 100%; }
body.whmcsbody .dm-rc-info-compact { margin-bottom: 12px; }
body.whmcsbody .dm-rc-quick-card { margin-top: -2px; }
body.whmcsbody .dm-rc-quick-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
body.whmcsbody .dm-rc-quick-link,
body.whmcsbody .dm-rc-quick-link:visited {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 999px;
    color: var(--dm-rc-navy) !important;
    font-size: 12px;
    font-weight: 800;
    padding: 8px 11px;
    text-decoration: none !important;
}
body.whmcsbody .dm-rc-quick-link:hover,
body.whmcsbody .dm-rc-quick-link:focus,
body.whmcsbody .dm-rc-quick-link-active {
    background: #fff7ef;
    border-color: rgba(245, 130, 32, 0.42);
    color: var(--dm-rc-orange-hover) !important;
    text-decoration: none !important;
}
body.whmcsbody .dm-rc-section-panel {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    margin-bottom: 16px;
    padding: 16px;
}
body.whmcsbody .dm-rc-panel-head {
    align-items: flex-start;
    display: flex;
    gap: 16px;
    justify-content: space-between;
    margin-bottom: 14px;
}
body.whmcsbody .dm-rc-panel-head h4 {
    color: var(--dm-rc-navy) !important;
    font-size: 18px;
    font-weight: 800;
    margin: 0 0 5px;
}
body.whmcsbody .dm-rc-panel-head p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.45;
    margin: 0;
}
body.whmcsbody .dm-rc-form-grid,
body.whmcsbody .dm-rc-status-strip,
body.whmcsbody .dm-rc-contact-preview,
body.whmcsbody .dm-rc-addon-grid,
body.whmcsbody .dm-rc-toolbar-preview {
    display: grid;
    gap: 12px;
}
body.whmcsbody .dm-rc-form-grid-two,
body.whmcsbody .dm-rc-contact-preview,
body.whmcsbody .dm-rc-addon-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
body.whmcsbody .dm-rc-status-strip { grid-template-columns: repeat(3, minmax(0, 1fr)); }
body.whmcsbody .dm-rc-toolbar-preview { align-items: center; grid-template-columns: 1fr minmax(160px, 220px) auto; }
body.whmcsbody .dm-rc-form-grid label {
    color: var(--dm-rc-navy);
    font-size: 13px;
    font-weight: 800;
    margin: 0;
}
body.whmcsbody .dm-rc-form-grid label span { display: block; margin-bottom: 5px; }
body.whmcsbody .dm-rc-control-preview {
    background: #ffffff !important;
    border: 1px solid rgba(17, 43, 77, 0.18) !important;
    border-radius: 6px !important;
    box-shadow: none !important;
    color: var(--dm-rc-text) !important;
    min-height: 38px;
    padding: 8px 10px !important;
    width: 100%;
}
body.whmcsbody .dm-rc-control-preview:disabled { opacity: 1; }
body.whmcsbody .dm-rc-status-strip > div,
body.whmcsbody .dm-rc-contact-preview > div,
body.whmcsbody .dm-rc-addon-grid > div,
body.whmcsbody .dm-rc-empty-preview {
    background: #ffffff;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    padding: 12px;
}
body.whmcsbody .dm-rc-status-strip span,
body.whmcsbody .dm-rc-contact-preview span,
body.whmcsbody .dm-rc-addon-grid span,
body.whmcsbody .dm-rc-empty-preview span {
    color: var(--dm-rc-muted);
    display: block;
    font-size: 12px;
    font-weight: 700;
    margin-top: 3px;
}
body.whmcsbody .dm-rc-status-strip strong,
body.whmcsbody .dm-rc-contact-preview strong,
body.whmcsbody .dm-rc-addon-grid strong,
body.whmcsbody .dm-rc-empty-preview strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 14px;
}
body.whmcsbody .dm-rc-mini-table {
    background: #ffffff;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    overflow: hidden;
}
body.whmcsbody .dm-rc-mini-row {
    align-items: center;
    display: grid;
    gap: 12px;
    grid-template-columns: 1fr 1fr auto;
    padding: 10px 12px;
}
body.whmcsbody .dm-rc-mini-row + .dm-rc-mini-row { border-top: 1px solid var(--dm-rc-border-soft); }
body.whmcsbody .dm-rc-mini-head {
    background: var(--dm-rc-navy);
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
}
body.whmcsbody .dm-rc-mini-row a {
    color: var(--dm-rc-orange-hover) !important;
    font-weight: 800;
    text-decoration: none !important;
}
body.whmcsbody .dm-rc-muted-text { color: var(--dm-rc-muted); }
body.whmcsbody .dm-rc-preview-footer {
    align-items: center;
    color: var(--dm-rc-muted);
    display: flex;
    font-size: 13px;
    gap: 10px;
    margin-top: 14px;
}
body.whmcsbody .dm-rc-pill {
    border-radius: 999px;
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    line-height: 1;
    padding: 6px 9px;
    text-transform: uppercase;
    white-space: nowrap;
}
body.whmcsbody .dm-rc-pill-info { background: #eef5fb; color: var(--dm-rc-navy); }
body.whmcsbody .dm-rc-pill-warning { background: #fff2df; color: var(--dm-rc-orange-hover); }
body.whmcsbody .dm-rc-domain-summary-compact { margin-bottom: 0; }
body.whmcsbody .dm-rc-section-hero {
    align-items: center;
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    display: flex;
    gap: 16px;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 14px 16px;
}
body.whmcsbody .dm-rc-section-hero h4 {
    color: var(--dm-rc-navy) !important;
    font-size: 17px;
    font-weight: 800;
    margin: 0 0 5px;
}
body.whmcsbody .dm-rc-section-hero p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.4;
    margin: 0;
}
body.whmcsbody .dm-rc-card { overflow: hidden; }
body.whmcsbody .dm-rc-card-header {
    align-items: center;
    background: var(--dm-rc-navy);
    color: #ffffff;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    min-height: 46px;
    padding: 12px 16px;
}
body.whmcsbody .dm-rc-card-header h3 { color: #ffffff !important; font-size: 16px; font-weight: 700; margin: 0; }
body.whmcsbody .dm-rc-card-body { padding: 16px; }
body.whmcsbody .dm-rc-info {
    background: #eef5fb;
    border: 1px solid #b9cad9;
    border-radius: 6px;
    color: var(--dm-rc-navy);
    line-height: 1.45;
    margin-bottom: 16px;
    padding: 12px 16px;
}
body.whmcsbody .dm-rc-warning {
    background: var(--dm-rc-warning);
    border: 1px solid #f1d69a;
    border-radius: 6px;
    color: #72581f;
    margin-bottom: 16px;
    padding: 12px 16px;
}
body.whmcsbody .dm-rc-domain-summary,
body.whmcsbody .dm-rc-action-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 16px;
}
body.whmcsbody .dm-rc-action-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 0; }
body.whmcsbody .dm-rc-summary-item,
body.whmcsbody .dm-rc-action-card {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    padding: 12px;
}
body.whmcsbody .dm-rc-summary-label {
    color: var(--dm-rc-muted);
    display: block;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.01em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody .dm-rc-summary-value {
    color: var(--dm-rc-text);
    display: block;
    font-size: 14px;
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

body.whmcsbody .dm-rc-mode-strip {
    align-items: center;
    background: #ffffff;
    border: 1px solid var(--dm-rc-border);
    border-radius: 8px;
    box-shadow: var(--dm-rc-shadow);
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: -2px 0 14px;
    padding: 10px 12px;
}
body.whmcsbody .dm-rc-mode-pill {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 999px;
    color: var(--dm-rc-navy);
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    padding: 8px 11px;
    white-space: nowrap;
}
body.whmcsbody .dm-rc-mode-pill-active {
    background: var(--dm-rc-orange);
    border-color: var(--dm-rc-orange);
    color: #ffffff;
}
body.whmcsbody .dm-rc-section-actionbar {
    align-items: center;
    background: linear-gradient(180deg, #ffffff 0%, #fbfcfd 100%);
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    display: flex;
    gap: 14px;
    justify-content: space-between;
    margin-bottom: 12px;
    padding: 14px;
}
body.whmcsbody .dm-rc-section-actionbar strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 16px;
    margin-bottom: 3px;
}
body.whmcsbody .dm-rc-section-actionbar p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.35;
    margin: 0;
}
body.whmcsbody .dm-rc-section-actionbar-buttons {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: 10px;
}
body.whmcsbody .dm-rc-preview-badge {
    background: #fff2df;
    border-radius: 999px;
    color: var(--dm-rc-orange-hover);
    display: inline-flex;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.04em;
    line-height: 1;
    padding: 8px 10px;
    text-transform: uppercase;
    white-space: nowrap;
}
body.whmcsbody .dm-rc-production-strip {
    align-items: center;
    background: #ffffff;
    border: 1px solid var(--dm-rc-border-soft);
    border-left: 4px solid var(--dm-rc-orange);
    border-radius: 8px;
    display: flex;
    gap: 14px;
    justify-content: space-between;
    margin-bottom: 12px;
    padding: 13px 14px;
}
body.whmcsbody .dm-rc-production-strip strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 15px;
    margin-bottom: 4px;
}
body.whmcsbody .dm-rc-production-strip p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.35;
    margin: 0;
}
body.whmcsbody .dm-rc-production-actions {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: 10px;
}
body.whmcsbody .dm-rc-blueprint-card {
    margin-top: 12px;
}
body.whmcsbody .dm-rc-blueprint-list {
    list-style: none;
    margin: 10px 0 0;
    padding: 0;
}
body.whmcsbody .dm-rc-blueprint-list li {
    border-top: 1px solid var(--dm-rc-border-soft);
    color: var(--dm-rc-text);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.35;
    padding: 9px 0 9px 22px;
    position: relative;
}
body.whmcsbody .dm-rc-blueprint-list li:first-child { border-top: 0; }
body.whmcsbody .dm-rc-blueprint-list li::before {
    background: var(--dm-rc-orange);
    border-radius: 50%;
    color: #ffffff;
    content: "✓";
    font-size: 10px;
    font-weight: 900;
    height: 15px;
    left: 0;
    line-height: 15px;
    position: absolute;
    text-align: center;
    top: 10px;
    width: 15px;
}
body.whmcsbody .dm-rc-implementation-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
body.whmcsbody .dm-rc-implementation-grid > div {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    min-height: 112px;
    padding: 14px;
}
body.whmcsbody .dm-rc-implementation-grid strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 15px;
    margin-bottom: 5px;
}
body.whmcsbody .dm-rc-implementation-grid p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.35;
    margin: 0;
}
body.whmcsbody .dm-rc-rollout-hero {
    align-items: center;
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-left: 4px solid var(--dm-rc-orange);
    border-radius: 8px;
    display: flex;
    gap: 16px;
    justify-content: space-between;
    margin-bottom: 14px;
    padding: 14px;
}
body.whmcsbody .dm-rc-rollout-hero strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 18px;
    margin-bottom: 4px;
}
body.whmcsbody .dm-rc-rollout-hero p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.4;
    margin: 0;
}
body.whmcsbody .dm-rc-rollout-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-bottom: 12px;
}
body.whmcsbody .dm-rc-rollout-grid > div {
    background: #ffffff;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    padding: 12px;
}
body.whmcsbody .dm-rc-rollout-grid span {
    align-items: center;
    background: var(--dm-rc-navy);
    border-radius: 50%;
    color: #ffffff;
    display: inline-flex;
    font-size: 12px;
    font-weight: 900;
    height: 24px;
    justify-content: center;
    margin-bottom: 8px;
    width: 24px;
}
body.whmcsbody .dm-rc-rollout-grid strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 14px;
    margin-bottom: 4px;
}
body.whmcsbody .dm-rc-rollout-grid p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.35;
    margin: 0;
}
body.whmcsbody .dm-rc-rollout-note {
    background: #fff8e5;
    border: 1px solid #f1d69a;
    border-radius: 8px;
    color: #72581f;
    font-size: 13px;
    line-height: 1.4;
    padding: 12px 14px;
}
body.whmcsbody .dm-rc-prototype-banner {
    align-items: center;
    background: #ffffff;
    border: 1px solid rgba(245, 130, 32, 0.28);
    border-left: 5px solid var(--dm-rc-orange);
    border-radius: 8px;
    box-shadow: var(--dm-rc-shadow);
    display: grid;
    gap: 14px;
    grid-template-columns: minmax(260px, 1fr) minmax(250px, 0.9fr) auto;
    margin: 0 0 14px;
    padding: 14px 16px;
}
body.whmcsbody .dm-rc-prototype-banner strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 16px;
    line-height: 1.25;
}
body.whmcsbody .dm-rc-prototype-banner p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.4;
    margin: 5px 0 0;
}
body.whmcsbody .dm-rc-prototype-steps {
    display: grid;
    gap: 7px;
}
body.whmcsbody .dm-rc-prototype-steps span {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 7px;
    color: var(--dm-rc-text);
    font-size: 12px;
    font-weight: 700;
    padding: 8px 10px;
}
body.whmcsbody .dm-rc-section-panel-prototype {
    border-color: rgba(245, 130, 32, 0.25);
}
body.whmcsbody .dm-rc-ns-prototype-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: minmax(240px, 0.8fr) minmax(320px, 1.2fr);
}
body.whmcsbody .dm-rc-ns-create-card,
body.whmcsbody .dm-rc-ns-table-card {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 8px;
    padding: 13px;
}
body.whmcsbody .dm-rc-ns-create-card label {
    display: block;
    margin-top: 10px;
}
body.whmcsbody .dm-rc-ns-create-card label > span {
    color: var(--dm-rc-muted);
    display: block;
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 5px;
    text-transform: uppercase;
}
body.whmcsbody .dm-rc-inline-host {
    align-items: center;
    display: flex;
    gap: 8px;
}
body.whmcsbody .dm-rc-inline-host input {
    max-width: 120px;
}
body.whmcsbody .dm-rc-inline-host strong {
    color: var(--dm-rc-navy);
    font-size: 14px;
}
body.whmcsbody .dm-rc-preview-footer-tight {
    margin-top: 12px;
}
body.whmcsbody .dm-rc-mini-table-polished .dm-rc-mini-row {
    grid-template-columns: minmax(130px, 1fr) minmax(130px, 1fr) minmax(80px, 0.55fr) minmax(80px, 0.45fr);
}

body.whmcsbody .dm-rc-ns-status-row {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 14px;
}
body.whmcsbody .dm-rc-ns-status-row > div {
    background: #ffffff;
    border: 1px solid var(--dm-rc-border-soft);
    border-left: 4px solid var(--dm-rc-orange);
    border-radius: 8px;
    padding: 12px 13px;
}
body.whmcsbody .dm-rc-ns-status-row strong,
body.whmcsbody .dm-rc-subcard-head strong,
body.whmcsbody .dm-rc-form-placement-map strong,
body.whmcsbody .dm-rc-placement-grid strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody .dm-rc-ns-status-row p {
    color: var(--dm-rc-muted);
    font-size: 13px;
    line-height: 1.35;
    margin: 5px 0 0;
}
body.whmcsbody .dm-rc-subcard-head {
    align-items: flex-start;
    display: flex;
    gap: 10px;
    justify-content: space-between;
    margin-bottom: 10px;
}
body.whmcsbody .dm-rc-button-preview-row {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}
body.whmcsbody .dm-rc-mini-table-actions .dm-rc-mini-row {
    grid-template-columns: minmax(140px, 1fr) minmax(150px, 1fr) minmax(80px, 0.5fr) minmax(140px, 0.7fr);
}
body.whmcsbody .dm-rc-mini-actions {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 6px;
}
body.whmcsbody .dm-rc-mini-btn {
    border: 0;
    border-radius: 6px;
    color: #ffffff;
    cursor: not-allowed;
    font-size: 11px;
    font-weight: 800;
    line-height: 1;
    min-height: 28px;
    opacity: 0.9;
    padding: 8px 9px;
}
body.whmcsbody .dm-rc-mini-btn-navy { background: var(--dm-rc-navy); }
body.whmcsbody .dm-rc-mini-btn-danger { background: #b94a48; }
body.whmcsbody .dm-rc-live-map-card,
body.whmcsbody .dm-rc-next-conversion-card,
body.whmcsbody .dm-rc-stage-one-card,
body.whmcsbody .dm-rc-first-scope-card {
    background: #ffffff;
    border: 1px solid var(--dm-rc-border);
    border-radius: 8px;
    margin-top: 14px;
    padding: 14px;
}
body.whmcsbody .dm-rc-live-map-grid,
body.whmcsbody .dm-rc-next-real-grid,
body.whmcsbody .dm-rc-first-scope-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
body.whmcsbody .dm-rc-live-map-grid > div,
body.whmcsbody .dm-rc-next-real-grid > div,
body.whmcsbody .dm-rc-first-scope-grid > div {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 7px;
    padding: 12px;
}
body.whmcsbody .dm-rc-live-map-grid span,
body.whmcsbody .dm-rc-next-real-grid span,
body.whmcsbody .dm-rc-first-scope-grid span {
    color: var(--dm-rc-orange-hover);
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.03em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody .dm-rc-live-map-grid strong,
body.whmcsbody .dm-rc-next-real-grid strong,
body.whmcsbody .dm-rc-next-conversion-card strong,
body.whmcsbody .dm-rc-first-scope-grid strong {
    color: var(--dm-rc-navy);
    display: block;
    font-size: 14px;
    margin-bottom: 4px;
}
body.whmcsbody .dm-rc-live-map-grid p,
body.whmcsbody .dm-rc-next-real-grid p,
body.whmcsbody .dm-rc-first-scope-grid p {
    color: var(--dm-rc-muted);
    font-size: 12px;
    line-height: 1.4;
    margin: 0;
}
body.whmcsbody .dm-rc-next-conversion-card ol {
    margin: 10px 0 0 18px;
    padding: 0;
}
body.whmcsbody .dm-rc-next-conversion-card li {
    color: var(--dm-rc-text);
    font-size: 13px;
    line-height: 1.45;
    margin-bottom: 6px;
}
body.whmcsbody .dm-rc-form-placement-map {
    background: #ffffff;
    border: 1px solid rgba(245, 130, 32, 0.22);
    border-radius: 8px;
    margin-top: 14px;
    padding: 13px;
}
body.whmcsbody .dm-rc-placement-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-top: 11px;
}
body.whmcsbody .dm-rc-placement-grid > div {
    background: #fbfcfd;
    border: 1px solid var(--dm-rc-border-soft);
    border-radius: 7px;
    padding: 10px;
}
body.whmcsbody .dm-rc-placement-grid span {
    color: var(--dm-rc-muted);
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.03em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody .dm-rc-action-card {
    align-items: center;
    display: flex;
    gap: 14px;
    justify-content: space-between;
    min-height: 92px;
}
body.whmcsbody .dm-rc-action-card h4 { color: var(--dm-rc-navy) !important; font-size: 15px; font-weight: 700; margin: 0 0 5px; }
body.whmcsbody .dm-rc-action-card p { color: var(--dm-rc-muted); font-size: 13px; line-height: 1.35; margin: 0; }
body.whmcsbody .dm-rc-card-kicker {
    color: var(--dm-rc-orange-hover);
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.04em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody .dm-rc-action-card-active {
    border-color: rgba(245, 130, 32, 0.55);
    box-shadow: inset 4px 0 0 var(--dm-rc-orange);
}
body.whmcsbody .dm-rc-action-buttons {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: 10px;
}
body.whmcsbody .dm-rc-link-btn,
body.whmcsbody .dm-rc-link-btn:visited {
    color: var(--dm-rc-navy) !important;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none !important;
    white-space: nowrap;
}
body.whmcsbody .dm-rc-link-btn:hover,
body.whmcsbody .dm-rc-link-btn:focus {
    color: var(--dm-rc-orange-hover) !important;
    text-decoration: none !important;
}
body.whmcsbody .dm-rc-empty-state { padding: 18px; }
@media (max-width: 991px) {
    body.whmcsbody .dm-rc-workspace-bar,
    body.whmcsbody .dm-rc-workspace-grid { grid-template-columns: 1fr; }
    body.whmcsbody .dm-rc-workspace-stats,
    body.whmcsbody .dm-rc-domain-summary,
    body.whmcsbody .dm-rc-action-grid,
    body.whmcsbody .dm-rc-implementation-grid,
    body.whmcsbody .dm-rc-rollout-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 680px) {
    body.whmcsbody .dm-rc-preview-title,
    body.whmcsbody .dm-rc-preview-selector form,
    body.whmcsbody .dm-rc-section-hero,
    body.whmcsbody .dm-rc-section-actionbar,
    body.whmcsbody .dm-rc-section-actionbar-buttons,
    body.whmcsbody .dm-rc-production-strip,
    body.whmcsbody .dm-rc-production-actions,
    body.whmcsbody .dm-rc-action-card,
    body.whmcsbody .dm-rc-rollout-hero,
    body.whmcsbody .dm-rc-action-buttons { align-items: stretch; flex-direction: column; }
    body.whmcsbody .dm-rc-workspace-stats,
    body.whmcsbody .dm-rc-domain-summary,
    body.whmcsbody .dm-rc-action-grid,
    body.whmcsbody .dm-rc-implementation-grid,
    body.whmcsbody .dm-rc-rollout-grid { grid-template-columns: 1fr; }
    body.whmcsbody .dm-rc-preview-selector select.form-control { max-width: none; }
    body.whmcsbody .dm-rc-panel-head,
    body.whmcsbody .dm-rc-preview-footer { align-items: stretch; flex-direction: column; }
    body.whmcsbody .dm-rc-form-grid-two,
    body.whmcsbody .dm-rc-status-strip,
    body.whmcsbody .dm-rc-contact-preview,
    body.whmcsbody .dm-rc-addon-grid,
    body.whmcsbody .dm-rc-toolbar-preview,
    body.whmcsbody .dm-rc-mini-row { grid-template-columns: 1fr; }
}

@media (max-width: 991px) {
    body.whmcsbody .dm-rc-ns-status-row,
    body.whmcsbody .dm-rc-placement-grid {
        grid-template-columns: 1fr;
    }
    body.whmcsbody .dm-rc-mini-table-actions .dm-rc-mini-row {
        grid-template-columns: 1fr;
    }
    body.whmcsbody .dm-rc-mini-actions {
        justify-content: flex-start;
    }
}
@media (max-width: 767px) { body.whmcsbody .dm-rc-live-map-grid, body.whmcsbody .dm-rc-next-real-grid, body.whmcsbody .dm-rc-stage-one-grid, body.whmcsbody .dm-rc-first-scope-grid { grid-template-columns: 1fr; } }
</style>
HTML
    . $panelHtml .
<<<'HTML'
<script>
(function () {
    function showDomainMongerDesignPreview() {
        var preview = document.getElementById('dm-rc-route-preview');
        if (!preview) {
            return;
        }

        var primary = document.querySelector('#main-body .primary-content') || document.querySelector('#main-body .container') || document.querySelector('main');
        if (!primary) {
            preview.style.display = 'block';
            return;
        }

        var row = primary.closest('.row');
        if (row) {
            var sidebars = row.querySelectorAll('.sidebar, [class*="col-lg-4"], [class*="col-xl-3"]');
            for (var i = 0; i < sidebars.length; i++) {
                if (!sidebars[i].contains(primary)) {
                    sidebars[i].style.display = 'none';
                }
            }
        }

        primary.className = 'col-12 primary-content';
        primary.innerHTML = '';
        preview.style.display = 'block';
        primary.appendChild(preview);
        document.body.classList.add('dm-rc-design-preview-active');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showDomainMongerDesignPreview);
    } else {
        showDomainMongerDesignPreview();
    }
})();
</script>
HTML;
});
