<?php
/**
 * DomainMonger patch 946 + 1009 + 1151 + 1156: DNS workspace internal navigation fix.
 *
 * Keeps DNS Workspace links on the unified DNS Management page.
 * Internal DNS tools stay inside the converted workspace while record-type
 * buttons still load the matching preserved ResellerClub/WHMCS record UI.
 * Patch 1009: record buttons no longer intercept clicks as visual-only changes.
 * Patch 1100: converted DNS workspace/menu links point directly to the fast
 * native DNS page unless the legacy fallback is intentionally requested.
 * Patch 1103 update:
 * - Internal converted DNS Management links now use the clean native DNS route
 *   without dmconverted=1 because conversion runs automatically.
 * Patch 1107 update:
 * - Allow managednszone to render the converted ResellerClub shell when
 *   &dmfeednative=1 is present, so the fast native DNS form can be fed into
 *   the old URL as a preview without redirecting.
 * Patch 1109 update:
 * - Feed mode is now the normal live DNS Management view, so managednszone
 *   renders the converted shell by default again. Legacy fallback remains
 *   available with &dmlegacydns=1 or &dmnoredirect=1.
 * Patch 1132 update:
 * - Normal live DNS feed pages no longer show the old DNS Workspace quick
 *   action menu. Legacy fallback pages keep the detailed tool menu.
 * Patch 1137 update:
 * - Normal live DNS feed pages no longer show the old inner DNS module toolbar
 *   above the fast editor. Legacy fallback pages keep it.
 * Patch 1138 update:
 * - Normal live DNS feed pages no longer show the old header quick-action
 *   buttons (DNS Records / Nameservers / Private Nameservers). Legacy fallback
 *   pages keep those quick links.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaFooterOutput', 999, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? ''));

    $isDnsRecords = ($scriptName === 'dnsmanagement.php');
    $isDnssec = (($scriptName === 'domainmanagement.php' && in_array($action, ['dnssec', 'managednssec'], true))
        || ($scriptName === 'dnsmanagement.php' && $action === 'dnsseczone'));
    $isDomainForwarding = ($scriptName === 'domainforwarding.php');
    $isEmailForwarding = ($scriptName === 'emailmanagement.php');

    if (!$isDnsRecords && !$isDnssec && !$isDomainForwarding && !$isEmailForwarding) {
        return '';
    }

    // Patch 1109: DNS Management now stays on dnsmanagement.php and uses the
    // fast native DNS feed inside the converted ResellerClub shell. Do not block
    // the normal managednszone shell here. Legacy fallback/bypass flags are still
    // handled by the feed and legacy unified hooks.

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

    $domainName = trim((string) ($_GET['domain'] ?? ''));

    try {
        $query = Capsule::table('tbldomains')
            ->select('id', 'domain', 'status', 'registrar', 'expirydate', 'nextduedate', 'donotrenew')
            ->where('userid', $clientId);

        if ($domainId > 0) {
            $query->where('id', $domainId);
        } elseif ($domainName !== '') {
            $query->where('domain', $domainName);
        } else {
            return '';
        }

        $domainRow = $query->first();
        if (!$domainRow) {
            return '';
        }

        $domainId = (int) $domainRow->id;
        $domainName = (string) $domainRow->domain;
        $status = (string) $domainRow->status;
        $registrar = (string) $domainRow->registrar;
        $expiryDate = (string) $domainRow->expirydate;
        $nextDueDate = (string) $domainRow->nextduedate;
        $autoRenew = ((int) $domainRow->donotrenew === 1) ? 'Disabled' : 'Enabled';
    } catch (\Throwable $e) {
        return '';
    }

    if ($domainId <= 0 || $domainName === '') {
        return '';
    }

    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    $recordType = strtoupper((string) ($_GET['nsrecordtype'] ?? $_POST['nsrecordtype'] ?? 'A'));
    $recordTypes = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SRV', 'SOA'];
    if (!in_array($recordType, $recordTypes, true)) {
        $recordType = 'A';
    }

    $activeDnsTool = 'record-' . strtolower($recordType);
    $activeTitle = 'Manage ' . $recordType . ' Records';
    $activeMeta = 'DNS Records';
    $activeGroup = 'records';
    $activeFlowLabel = 'Records';

    if ($isDnssec) {
        $activeDnsTool = 'dnssec';
        $activeTitle = 'Manage DNSSEC Records';
        $activeMeta = 'DNS Security';
        $activeGroup = 'security';
        $activeFlowLabel = 'Security & Authority';
    } elseif ($recordType === 'SOA') {
        $activeGroup = 'security';
        $activeFlowLabel = 'Security & Authority';
    } elseif ($isDomainForwarding) {
        $activeDnsTool = 'domain-forwarding';
        $activeTitle = 'Domain Forwarding';
        $activeMeta = 'Forwarding';
        $activeGroup = 'forwarding';
        $activeFlowLabel = 'Forwarding';
    } elseif ($isEmailForwarding) {
        $activeDnsTool = 'email-forwarding';
        $activeTitle = 'Email Forwarding';
        $activeMeta = 'Email Routing';
        $activeGroup = 'forwarding';
        $activeFlowLabel = 'Forwarding';
    }

    $encodedDomain = rawurlencode($domainName);
    $baseClient = 'clientarea.php?action=domaindetails&id=' . $domainId;
    $legacyDnsBase = 'dnsmanagement.php?action=managednszone&domain=' . $encodedDomain . '&domainid=' . $domainId;
    // Patch 1100: DNS Management is now served by the fast native WHMCS DNS page.
    // Use direct native links from the converted DNS workspace/menu so users do
    // not bounce through dnsmanagement.php before landing on the live page.
    // When intentionally viewing the legacy fallback, keep legacy internal links
    // inside that fallback workspace.
    $legacyDnsRequestedForLinks = ($scriptName === 'dnsmanagement.php'
        && $action === 'managednszone'
        && (isset($_GET['dmlegacydns']) || isset($_GET['dmnoredirect'])));
    $nativeDnsBase = 'clientarea.php?action=domaindns&domainid=' . $domainId;
    // Patch 1109: when already in dnsmanagement.php, keep DNS Records links on
    // the visible ResellerClub URL so the fast native feed remains seamless.
    $dnsBase = ($scriptName === 'dnsmanagement.php' && $action === 'managednszone')
        ? $legacyDnsBase
        : $nativeDnsBase;
    $domainForwardingUrl = 'domainforwarding.php?action=managedomfwd&domainid=' . $domainId . '&domain=' . $encodedDomain;
    $dnssecUrl = 'dnsmanagement.php?action=dnsseczone&domain=' . $encodedDomain . '&domainid=' . $domainId . '&nsrecordtype=DNSSEC';
    // The reseller emailmanagement.php route can blank when Email Forwarding is inactive or unavailable.
    // Use WHMCS' safe built-in Email Forwarding/Addons path instead; it can show manage/buy/unavailable states.
    $emailForwardingUrl = 'clientarea.php?action=domainemailforwarding&domainid=' . $domainId;

    $domainMenuItems = [
        ['key' => 'overview', 'label' => 'Overview', 'meta' => 'Domain', 'href' => $baseClient . '#tabOverview'],
        ['key' => 'autorenew', 'label' => 'Auto Renew', 'meta' => 'Billing', 'href' => $baseClient . '#tabAutorenew'],
        ['key' => 'nameservers', 'label' => 'Nameservers', 'meta' => 'DNS', 'href' => $baseClient . '#tabNameservers'],
        ['key' => 'reglock', 'label' => 'Registrar Lock', 'meta' => 'Security', 'href' => $baseClient . '#tabReglock'],
        ['key' => 'addons', 'label' => 'Addons', 'meta' => 'Options', 'href' => $baseClient . '#tabAddons'],
        ['key' => 'whois', 'label' => 'WHOIS Contact Info', 'meta' => 'Contacts', 'href' => 'clientarea.php?action=domaincontacts&domainid=' . $domainId],
        ['key' => 'privatens', 'label' => 'Private Nameservers', 'meta' => 'Hosts', 'href' => 'domainmanagement.php?action=childns&id=' . $domainId],
        ['key' => 'dns', 'label' => 'DNS Management', 'meta' => 'Records', 'href' => $dnsBase . '&nsrecordtype=A'],
        ['key' => 'getepp', 'label' => 'Get EPP Code', 'meta' => 'Transfer', 'href' => 'clientarea.php?action=domaingetepp&domainid=' . $domainId],
    ];

    $domainMenuHtml = '<ul class="dm-domain-section-menu" aria-label="Domain management sections">';
    foreach ($domainMenuItems as $item) {
        $classes = 'dm-domain-section-link';
        if ($item['key'] === 'dns') {
            $classes .= ' dm-active';
        }
        $aria = $item['key'] === 'dns' ? ' aria-current="page"' : '';
        $domainMenuHtml .= '<li><a class="' . $classes . '" href="' . $e($item['href']) . '"' . $aria . '>'
            . $e($item['label'])
            . '<span>' . $e($item['meta']) . '</span></a></li>';
    }
    $domainMenuHtml .= '</ul>';

    $dnsToolGroups = [
        'records' => [
            'label' => 'Records',
            'meta' => 'A, AAAA, CNAME, MX, NS, TXT, SRV',
            'href' => $dnsBase . '&nsrecordtype=A',
            'tools' => [
                ['key' => 'record-a', 'label' => 'Manage A Records', 'meta' => 'IPv4', 'href' => $dnsBase . '&nsrecordtype=A'],
                ['key' => 'record-aaaa', 'label' => 'Manage AAAA Records', 'meta' => 'IPv6', 'href' => $dnsBase . '&nsrecordtype=AAAA'],
                ['key' => 'record-cname', 'label' => 'Manage CNAME Records', 'meta' => 'Alias', 'href' => $dnsBase . '&nsrecordtype=CNAME'],
                ['key' => 'record-mx', 'label' => 'Manage MX Records', 'meta' => 'Mail', 'href' => $dnsBase . '&nsrecordtype=MX'],
                ['key' => 'record-ns', 'label' => 'Manage NS Records', 'meta' => 'Delegation', 'href' => $dnsBase . '&nsrecordtype=NS'],
                ['key' => 'record-txt', 'label' => 'Manage TXT Records', 'meta' => 'Verification', 'href' => $dnsBase . '&nsrecordtype=TXT'],
                ['key' => 'record-srv', 'label' => 'Manage SRV Records', 'meta' => 'Services', 'href' => $dnsBase . '&nsrecordtype=SRV'],
            ],
        ],
        'security' => [
            'label' => 'Security & Authority',
            'meta' => 'DNSSEC and SOA',
            'href' => $dnssecUrl,
            'tools' => [
                ['key' => 'dnssec', 'label' => 'Manage DNSSEC Records', 'meta' => 'Security', 'href' => $dnssecUrl],
                ['key' => 'record-soa', 'label' => 'Manage SOA Record', 'meta' => 'Authority', 'href' => $dnsBase . '&nsrecordtype=SOA'],
            ],
        ],
        'forwarding' => [
            'label' => 'Forwarding',
            'meta' => 'Domain and email forwarding',
            'href' => $domainForwardingUrl,
            'tools' => [
                ['key' => 'domain-forwarding', 'label' => 'Domain Forwarding', 'meta' => 'Redirects', 'href' => $domainForwardingUrl],
                ['key' => 'email-forwarding', 'label' => 'Email Forwarding', 'meta' => 'Email Routing', 'href' => $emailForwardingUrl],
            ],
        ],
    ];

    $dnsFlowHtml = '<div class="dm-dns-flow-tabs" aria-label="DNS Management flow groups">';
    foreach ($dnsToolGroups as $groupKey => $group) {
        $classes = 'dm-dns-flow-tab';
        if ($groupKey === $activeGroup) {
            $classes .= ' dm-active';
        }
        $aria = $groupKey === $activeGroup ? ' aria-current="page"' : '';
        $dnsFlowHtml .= '<a class="' . $classes . '" href="' . $e($group['href']) . '"' . $aria . '>'
            . '<strong>' . $e($group['label']) . '</strong>'
            . '<span>' . $e($group['meta']) . '</span>'
            . '</a>';
    }
    $dnsFlowHtml .= '</div>';

    $dnsToolHtml = '<div class="dm-dns-tool-groups" aria-label="DNS Management tools">';
    foreach ($dnsToolGroups as $groupKey => $group) {
        $groupClasses = 'dm-dns-tool-group';
        if ($groupKey === $activeGroup) {
            $groupClasses .= ' dm-active-group';
        }
        $dnsToolHtml .= '<section class="' . $groupClasses . '">'
            . '<div class="dm-dns-tool-group-head"><strong>' . $e($group['label']) . '</strong><span>' . $e($group['meta']) . '</span></div>'
            . '<div class="dm-dns-tool-grid">';
        foreach ($group['tools'] as $item) {
            $classes = 'dm-dns-tool-link';
            if ($item['key'] === $activeDnsTool) {
                $classes .= ' dm-active';
            }
            $aria = $item['key'] === $activeDnsTool ? ' aria-current="page"' : '';
            $dnsToolHtml .= '<a class="' . $classes . '" href="' . $e($item['href']) . '"' . $aria . '>'
                . '<strong>' . $e($item['label']) . '</strong>'
                . '<span>' . $e($item['meta']) . '</span>'
                . '</a>';
        }
        $dnsToolHtml .= '</div></section>';
    }
    $dnsToolHtml .= '</div>';

    $recordShortcutHtml = '<div class="dm-dns-record-shortcuts" aria-label="DNS record type shortcuts">'
        . '<div class="dm-dns-record-shortcuts-head"><strong>Record Shortcuts</strong><span>Jump directly to the common DNS record tools</span></div>'
        . '<div class="dm-dns-record-shortcuts-grid">';
    foreach ($recordTypes as $shortcutType) {
        $shortcutKey = 'record-' . strtolower($shortcutType);
        $classes = 'dm-dns-record-shortcut';
        if ($shortcutKey === $activeDnsTool) {
            $classes .= ' dm-active';
        }
        $aria = $shortcutKey === $activeDnsTool ? ' aria-current="page"' : '';
        $recordShortcutHtml .= '<a class="' . $classes . '" href="' . $e($dnsBase . '&nsrecordtype=' . rawurlencode($shortcutType)) . '"' . $aria . '>'
            . '<strong>' . $e($shortcutType) . '</strong>'
            . '<span>' . ($shortcutType === 'SOA' ? 'Authority' : 'Records') . '</span>'
            . '</a>';
    }
    $recordShortcutHtml .= '</div></div>';

    $recordFamilyGroups = [
        [
            'label' => 'Website Routing',
            'meta' => 'A / AAAA / CNAME',
            'summary' => 'Use these records when a website, app, or subdomain needs to point somewhere.',
            'tools' => [
                ['type' => 'A', 'label' => 'A'],
                ['type' => 'AAAA', 'label' => 'AAAA'],
                ['type' => 'CNAME', 'label' => 'CNAME'],
            ],
        ],
        [
            'label' => 'Email Setup',
            'meta' => 'MX / TXT',
            'summary' => 'Use these records for mail delivery, SPF, DKIM, DMARC, and provider verification.',
            'tools' => [
                ['type' => 'MX', 'label' => 'MX'],
                ['type' => 'TXT', 'label' => 'TXT'],
            ],
        ],
        [
            'label' => 'Authority',
            'meta' => 'NS / SOA / DNSSEC',
            'summary' => 'Use these tools for delegated authority, zone authority, and DNSSEC security.',
            'tools' => [
                ['type' => 'NS', 'label' => 'NS'],
                ['type' => 'SOA', 'label' => 'SOA'],
                ['type' => 'DNSSEC', 'label' => 'DNSSEC', 'href' => $dnssecUrl],
            ],
        ],
        [
            'label' => 'Services & Forwarding',
            'meta' => 'SRV / Domain / Email',
            'summary' => 'Use these tools for service discovery, website redirects, and email forwarding.',
            'tools' => [
                ['type' => 'SRV', 'label' => 'SRV'],
                ['type' => 'FORWARD', 'label' => 'Domain Forwarding', 'href' => $domainForwardingUrl],
                ['type' => 'EMAILFORWARD', 'label' => 'Email Forwarding', 'href' => $emailForwardingUrl],
            ],
        ],
    ];

    $recordFamilyHtml = '<div class="dm-dns-record-families" aria-label="DNS record families">'
        . '<div class="dm-dns-record-families-head"><div><span>Record Families</span><strong>Choose the DNS task type first</strong></div><p>Grouped like a control panel so the old side-menu tools feel like one DNS workspace.</p></div>'
        . '<div class="dm-dns-record-family-grid">';
    foreach ($recordFamilyGroups as $family) {
        $familyActive = false;
        foreach ($family['tools'] as $tool) {
            $toolKey = 'record-' . strtolower((string) $tool['type']);
            if ($toolKey === $activeDnsTool || ($tool['type'] === 'DNSSEC' && $activeDnsTool === 'dnssec') || ($tool['type'] === 'FORWARD' && $activeDnsTool === 'domain-forwarding') || ($tool['type'] === 'EMAILFORWARD' && $activeDnsTool === 'email-forwarding')) {
                $familyActive = true;
                break;
            }
        }
        $recordFamilyHtml .= '<section class="dm-dns-record-family' . ($familyActive ? ' dm-active' : '') . '">'
            . '<div class="dm-dns-record-family-title"><span>' . $e($family['meta']) . '</span><strong>' . $e($family['label']) . '</strong></div>'
            . '<p>' . $e($family['summary']) . '</p>'
            . '<div class="dm-dns-record-family-links">';
        foreach ($family['tools'] as $tool) {
            $href = $tool['href'] ?? ($dnsBase . '&nsrecordtype=' . rawurlencode((string) $tool['type']));
            $toolKey = 'record-' . strtolower((string) $tool['type']);
            $isActiveTool = ($toolKey === $activeDnsTool) || ($tool['type'] === 'DNSSEC' && $activeDnsTool === 'dnssec') || ($tool['type'] === 'FORWARD' && $activeDnsTool === 'domain-forwarding') || ($tool['type'] === 'EMAILFORWARD' && $activeDnsTool === 'email-forwarding');
            $recordFamilyHtml .= '<a class="dm-dns-record-family-chip' . ($isActiveTool ? ' dm-active' : '') . '" href="' . $e($href) . '">' . $e($tool['label']) . '</a>';
        }
        $recordFamilyHtml .= '</div></section>';
    }
    $recordFamilyHtml .= '</div></div>';


    $taskLauncherItems = [
        ['key' => 'records', 'label' => 'DNS Records', 'meta' => 'A, AAAA, CNAME, MX, NS, TXT, SRV', 'href' => $dnsBase . '&nsrecordtype=A', 'active' => $activeGroup === 'records'],
        ['key' => 'dnssec', 'label' => 'DNSSEC', 'meta' => 'Security Records', 'href' => $dnssecUrl, 'active' => $activeDnsTool === 'dnssec'],
        ['key' => 'soa', 'label' => 'SOA Record', 'meta' => 'Authority', 'href' => $dnsBase . '&nsrecordtype=SOA', 'active' => $activeDnsTool === 'record-soa'],
        ['key' => 'domain-forwarding', 'label' => 'Domain Forwarding', 'meta' => 'Redirects', 'href' => $domainForwardingUrl, 'active' => $activeDnsTool === 'domain-forwarding'],
        ['key' => 'email-forwarding', 'label' => 'Email Forwarding', 'meta' => 'Email Routing', 'href' => $emailForwardingUrl, 'active' => $activeDnsTool === 'email-forwarding'],
    ];

    $taskLauncherHtml = '<div class="dm-dns-task-launcher" aria-label="DNS workspace quick actions">'
        . '<div class="dm-dns-task-launcher-head"><strong>DNS Workspace</strong><span>Records, authority, and forwarding in one flow</span></div>'
        . '<div class="dm-dns-task-launcher-grid">';
    foreach ($taskLauncherItems as $item) {
        $classes = 'dm-dns-task-link';
        if (!empty($item['active'])) {
            $classes .= ' dm-active';
        }
        $aria = !empty($item['active']) ? ' aria-current="page"' : '';
        $taskLauncherHtml .= '<a class="' . $classes . '" href="' . $e($item['href']) . '"' . $aria . '>'
            . '<strong>' . $e($item['label']) . '</strong>'
            . '<span>' . $e($item['meta']) . '</span>'
            . '</a>';
    }
    $taskLauncherHtml .= '</div></div>';

    $toolDetails = [
        'record-a' => [
            'summary' => 'Point the domain or a host name to an IPv4 address.',
            'best' => 'Website hosting, app servers, and standard IPv4 destinations.',
            'related' => 'AAAA records for IPv6 and CNAME records for aliases.',
        ],
        'record-aaaa' => [
            'summary' => 'Point the domain or a host name to an IPv6 address.',
            'best' => 'IPv6-ready hosting, app servers, and modern network destinations.',
            'related' => 'A records for IPv4 and CNAME records for aliases.',
        ],
        'record-cname' => [
            'summary' => 'Point a host name to another host name instead of an IP address.',
            'best' => 'Aliases such as www, app, mail vendor hosts, or hosted services.',
            'related' => 'A/AAAA records when the destination is an IP address.',
        ],
        'record-mx' => [
            'summary' => 'Control which mail servers receive email for the domain.',
            'best' => 'Google Workspace, Microsoft 365, cPanel mail, or other mail providers.',
            'related' => 'TXT records for SPF, DKIM, and verification records.',
        ],
        'record-ns' => [
            'summary' => 'Manage delegated DNS authority records inside the zone.',
            'best' => 'Subdomain delegation and advanced DNS authority setups.',
            'related' => 'Nameservers and Private Nameservers for registrar-level nameserver settings.',
        ],
        'record-txt' => [
            'summary' => 'Store verification, email authentication, and service configuration text.',
            'best' => 'SPF, DKIM, DMARC, ownership verification, and service setup records.',
            'related' => 'MX records for email delivery and CNAME records for vendor aliases.',
        ],
        'record-srv' => [
            'summary' => 'Define service location records with priority, weight, port, and target.',
            'best' => 'Voice, chat, directory, and application service discovery.',
            'related' => 'TXT and CNAME records for provider-specific service setup.',
        ],
        'record-soa' => [
            'summary' => 'Review or manage the Start of Authority details for the DNS zone.',
            'best' => 'Authority, refresh, retry, and serial-related zone settings.',
            'related' => 'DNSSEC records and NS records for DNS authority management.',
        ],
        'dnssec' => [
            'summary' => 'Manage DNSSEC records used to help validate DNS responses.',
            'best' => 'Domains using DNSSEC signing or registrar-level DNSSEC publishing.',
            'related' => 'SOA and NS records for DNS authority checks.',
        ],
        'domain-forwarding' => [
            'summary' => 'Forward domain traffic to another website or URL.',
            'best' => 'Redirecting parked domains, alternate domains, or marketing domains.',
            'related' => 'A/CNAME records when traffic should point instead of redirect.',
        ],
        'email-forwarding' => [
            'summary' => 'Forward incoming email addresses to another mailbox.',
            'best' => 'Simple alias-style email routing without full mailbox hosting.',
            'related' => 'MX and TXT records for full email hosting and authentication.',
        ],
    ];
    $activeToolDetails = $toolDetails[$activeDnsTool] ?? [
        'summary' => 'Use the selected DNS tool inside the unified DNS Management workspace.',
        'best' => 'Review the current module output below before saving any changes.',
        'related' => 'Use the DNS workspace launcher to switch tools.',
    ];

    $toolDetailHtml = '<div class="dm-dns-tool-detail" aria-label="Selected DNS tool details">'
        . '<div><span>Selected Tool</span><strong>' . $e($activeTitle) . '</strong><p>' . $e($activeToolDetails['summary']) . '</p></div>'
        . '<div><span>Best For</span><strong>Common Use</strong><p>' . $e($activeToolDetails['best']) . '</p></div>'
        . '<div><span>Related Tools</span><strong>Next Options</strong><p>' . $e($activeToolDetails['related']) . '</p></div>'
        . '</div>';

    $toolInputGuides = [
        'record-a' => [
            ['label' => 'Host / Name', 'value' => '@, www, app, or another host label'],
            ['label' => 'Points To', 'value' => 'IPv4 address, such as 192.0.2.10'],
            ['label' => 'Watch For', 'value' => 'Use AAAA instead when the destination is IPv6.'],
        ],
        'record-aaaa' => [
            ['label' => 'Host / Name', 'value' => '@, www, app, or another host label'],
            ['label' => 'Points To', 'value' => 'IPv6 address, such as 2001:db8::10'],
            ['label' => 'Watch For', 'value' => 'Use A records for IPv4 destinations.'],
        ],
        'record-cname' => [
            ['label' => 'Alias Host', 'value' => 'www, shop, app, or another alias'],
            ['label' => 'Target Hostname', 'value' => 'Another hostname, not an IP address'],
            ['label' => 'Watch For', 'value' => 'The root domain usually should not be a CNAME.'],
        ],
        'record-mx' => [
            ['label' => 'Mail Host', 'value' => '@ or the host receiving mail'],
            ['label' => 'Mail Server', 'value' => 'Provider mail server hostname plus priority'],
            ['label' => 'Watch For', 'value' => 'Most mail providers also require TXT records.'],
        ],
        'record-ns' => [
            ['label' => 'Delegated Host', 'value' => 'Subdomain or host being delegated'],
            ['label' => 'Nameserver', 'value' => 'Authoritative nameserver hostname'],
            ['label' => 'Watch For', 'value' => 'Registrar-level nameservers are changed on the Nameservers page.'],
        ],
        'record-txt' => [
            ['label' => 'Host / Name', 'value' => '@, _dmarc, selector._domainkey, or provider host'],
            ['label' => 'Text Value', 'value' => 'SPF, DKIM, DMARC, verification, or provider text'],
            ['label' => 'Watch For', 'value' => 'Copy TXT values exactly from the provider.'],
        ],
        'record-srv' => [
            ['label' => 'Service Name', 'value' => '_service._protocol, such as _sip._tcp'],
            ['label' => 'Destination', 'value' => 'Priority, weight, port, and target host'],
            ['label' => 'Watch For', 'value' => 'SRV records are provider-specific; match their format.'],
        ],
        'record-soa' => [
            ['label' => 'Authority Values', 'value' => 'Primary nameserver, responsible contact, serial, refresh, retry'],
            ['label' => 'Use Case', 'value' => 'Advanced authority or zone timing changes'],
            ['label' => 'Watch For', 'value' => 'SOA changes can affect the entire DNS zone.'],
        ],
        'dnssec' => [
            ['label' => 'Record Type', 'value' => 'DS/DNSSEC values from DNS provider or registrar flow'],
            ['label' => 'Use Case', 'value' => 'Publishing DNSSEC security records'],
            ['label' => 'Watch For', 'value' => 'Mismatch between DNS host and registrar DNSSEC can break resolution.'],
        ],
        'domain-forwarding' => [
            ['label' => 'Source', 'value' => 'Domain or host to redirect'],
            ['label' => 'Destination URL', 'value' => 'Full URL users should be sent to'],
            ['label' => 'Watch For', 'value' => 'Forwarding redirects visitors; DNS records point traffic instead.'],
        ],
        'email-forwarding' => [
            ['label' => 'Forward From', 'value' => 'Alias address at this domain'],
            ['label' => 'Forward To', 'value' => 'Destination mailbox that receives the message'],
            ['label' => 'Watch For', 'value' => 'Forwarding is not the same as full mailbox hosting.'],
        ],
    ];
    $activeToolInputGuide = $toolInputGuides[$activeDnsTool] ?? [
        ['label' => 'Current Tool', 'value' => 'Review the preserved module output below.'],
        ['label' => 'Before Saving', 'value' => 'Confirm values match the DNS provider instructions.'],
        ['label' => 'Watch For', 'value' => 'DNS changes can take time to propagate.'],
    ];
    $toolInputGuideHtml = '<div class="dm-dns-input-guide" aria-label="Selected DNS tool input guide">'
        . '<div class="dm-dns-input-guide-head"><div><span>Input Guide</span><strong>' . $e($activeTitle) . '</strong></div><p>Quick reference for the values usually needed by this DNS tool.</p></div>'
        . '<div class="dm-dns-input-guide-grid">';
    foreach ($activeToolInputGuide as $guide) {
        $toolInputGuideHtml .= '<div class="dm-dns-input-guide-item"><span>' . $e($guide['label']) . '</span><strong>' . $e($guide['value']) . '</strong></div>';
    }
    $toolInputGuideHtml .= '</div></div>';

    $toolExamples = [
        'record-a' => [
            ['label' => 'Root Website', 'value' => '@ → 192.0.2.10'],
            ['label' => 'Website Host', 'value' => 'www → 192.0.2.10'],
            ['label' => 'Use Instead', 'value' => 'Use CNAME when the provider gives a hostname instead of an IP.'],
        ],
        'record-aaaa' => [
            ['label' => 'Root IPv6', 'value' => '@ → 2001:db8::10'],
            ['label' => 'Website Host', 'value' => 'www → 2001:db8::10'],
            ['label' => 'Pairing', 'value' => 'Often paired with A records for IPv4/IPv6 support.'],
        ],
        'record-cname' => [
            ['label' => 'Website Alias', 'value' => 'www → hosting-provider.example.com'],
            ['label' => 'App Alias', 'value' => 'app → app-platform.example.net'],
            ['label' => 'Avoid', 'value' => 'Do not use CNAME when the provider gave an IP address.'],
        ],
        'record-mx' => [
            ['label' => 'Root Mail', 'value' => '@ → mail provider hostname + priority'],
            ['label' => 'Provider Setup', 'value' => 'Use the exact MX hostnames and priorities from the mail provider.'],
            ['label' => 'Related', 'value' => 'Add TXT records for SPF, DKIM, DMARC, or verification when required.'],
        ],
        'record-ns' => [
            ['label' => 'Delegation', 'value' => 'subdomain → ns1.provider.example'],
            ['label' => 'Scope', 'value' => 'Use NS records for delegated zones inside DNS.'],
            ['label' => 'Registrar Setting', 'value' => 'Use Nameservers for registrar-level nameserver changes.'],
        ],
        'record-txt' => [
            ['label' => 'SPF', 'value' => '@ → v=spf1 include:provider.example ~all'],
            ['label' => 'DMARC', 'value' => '_dmarc → v=DMARC1; p=none;'],
            ['label' => 'Verification', 'value' => 'Copy provider verification strings exactly.'],
        ],
        'record-srv' => [
            ['label' => 'Service Host', 'value' => '_service._protocol'],
            ['label' => 'Target', 'value' => 'priority weight port target-hostname'],
            ['label' => 'Provider Format', 'value' => 'Match the provider instructions exactly.'],
        ],
        'record-soa' => [
            ['label' => 'Authority', 'value' => 'Primary nameserver and responsible contact values'],
            ['label' => 'Timing', 'value' => 'Refresh, retry, expire, minimum, and serial settings'],
            ['label' => 'Scope', 'value' => 'SOA values affect the whole DNS zone.'],
        ],
        'dnssec' => [
            ['label' => 'DS Values', 'value' => 'Key tag, algorithm, digest type, and digest'],
            ['label' => 'Match Provider', 'value' => 'DNSSEC values must match the active DNS provider.'],
            ['label' => 'Check First', 'value' => 'Avoid enabling mismatched DNSSEC records.'],
        ],
        'domain-forwarding' => [
            ['label' => 'Forward From', 'value' => 'domain or host on this domain'],
            ['label' => 'Forward To', 'value' => 'Full destination URL'],
            ['label' => 'Difference', 'value' => 'Forwarding redirects visitors; DNS records point traffic.'],
        ],
        'email-forwarding' => [
            ['label' => 'Forward From', 'value' => 'alias@' . $domainName],
            ['label' => 'Forward To', 'value' => 'destination mailbox'],
            ['label' => 'Difference', 'value' => 'Forwarding is alias routing, not full mailbox hosting.'],
        ],
    ];
    $activeToolExamples = $toolExamples[$activeDnsTool] ?? [
        ['label' => 'Selected Tool', 'value' => 'Use the current module output below for the actual change.'],
        ['label' => 'Current Values', 'value' => 'Review current values before replacing them.'],
        ['label' => 'Save Location', 'value' => 'Save inside the preserved WHMCS/ResellerClub tool.'],
    ];
    $toolExamplesHtml = '<div class="dm-dns-example-guide" aria-label="DNS selected tool examples">'
        . '<div class="dm-dns-example-guide-head"><div><span>Examples</span><strong>' . $e($activeTitle) . ' patterns</strong></div><p>Use these as quick examples before editing values in the preserved module below.</p></div>'
        . '<div class="dm-dns-example-guide-grid">';
    foreach ($activeToolExamples as $example) {
        $toolExamplesHtml .= '<div class="dm-dns-example-guide-item"><span>' . $e($example['label']) . '</span><strong>' . $e($example['value']) . '</strong></div>';
    }
    $toolExamplesHtml .= '</div></div>';

    $changeImpactGuides = [
        'records' => [
            ['label' => 'Propagation', 'value' => 'Record changes may not appear everywhere immediately. TTL and resolver cache affect timing.'],
            ['label' => 'Related Records', 'value' => 'Check matching website, email, verification, or forwarding records before saving.'],
            ['label' => 'Rollback', 'value' => 'Copy the current value before changing it so the original record can be restored.'],
        ],
        'security' => [
            ['label' => 'Authority Impact', 'value' => 'DNSSEC and SOA changes can affect the entire DNS zone, not just one host.'],
            ['label' => 'Provider Match', 'value' => 'Confirm DNSSEC/authority values match the DNS provider and registrar instructions exactly.'],
            ['label' => 'Rollback', 'value' => 'Keep prior values available until the domain resolves correctly after the change.'],
        ],
        'forwarding' => [
            ['label' => 'Traffic Flow', 'value' => 'Forwarding changes where visitors or messages are sent; DNS records may still exist separately.'],
            ['label' => 'Destination Check', 'value' => 'Verify the destination URL or mailbox before saving to avoid routing to the wrong place.'],
            ['label' => 'Rollback', 'value' => 'Keep the previous destination handy so forwarding can be restored if needed.'],
        ],
    ];
    $activeChangeImpactGuide = $changeImpactGuides[$activeGroup] ?? $changeImpactGuides['records'];
    $changeImpactHtml = '<div class="dm-dns-change-impact" aria-label="DNS change impact guide">'
        . '<div class="dm-dns-change-impact-head"><div><span>Change Impact</span><strong>' . $e($activeFlowLabel) . '</strong></div><p>Use this as a quick check before saving in the preserved module below.</p></div>'
        . '<div class="dm-dns-change-impact-grid">';
    foreach ($activeChangeImpactGuide as $guide) {
        $changeImpactHtml .= '<div class="dm-dns-change-impact-item"><span>' . $e($guide['label']) . '</span><strong>' . $e($guide['value']) . '</strong></div>';
    }
    $changeImpactHtml .= '</div></div>';

    $validationGuides = [
        'records' => [
            ['label' => 'Host / Name', 'value' => 'Confirm the host label is correct before saving, especially @, www, mail, or provider-specific hosts.'],
            ['label' => 'Target Value', 'value' => 'Confirm the destination matches the provider instruction exactly: IP, hostname, mail server, or text value.'],
            ['label' => 'Existing Records', 'value' => 'Check for duplicate or conflicting records in the preserved DNS table below before adding another record.'],
        ],
        'security' => [
            ['label' => 'Provider Match', 'value' => 'Confirm DNSSEC, DS, or SOA values match the active DNS provider and registrar requirements.'],
            ['label' => 'Zone Scope', 'value' => 'Remember authority changes can affect the whole DNS zone, not only one hostname.'],
            ['label' => 'Recovery', 'value' => 'Keep prior authority values available until the domain resolves correctly after the change.'],
        ],
        'forwarding' => [
            ['label' => 'Source', 'value' => 'Confirm the domain, host, or email alias being forwarded is the one you intend to change.'],
            ['label' => 'Destination', 'value' => 'Open or verify the destination URL or mailbox before saving the forwarding rule.'],
            ['label' => 'Overlap', 'value' => 'Check whether existing DNS records or other forwarding rules already route this traffic.'],
        ],
    ];
    $activeValidationGuide = $validationGuides[$activeGroup] ?? $validationGuides['records'];
    $validationGuideHtml = '<div class="dm-dns-validation-guide" aria-label="DNS pre-save validation guide">'
        . '<div class="dm-dns-validation-guide-head"><div><span>Pre-Save Validation</span><strong>' . $e($activeFlowLabel) . ' checks</strong></div><p>Use this quick check before saving inside the preserved module output.</p></div>'
        . '<div class="dm-dns-validation-guide-grid">';
    foreach ($activeValidationGuide as $guide) {
        $validationGuideHtml .= '<div class="dm-dns-validation-guide-item"><span>' . $e($guide['label']) . '</span><strong>' . $e($guide['value']) . '</strong></div>';
    }
    $validationGuideHtml .= '</div></div>';

    $sideMenuMapSections = [
        [
            'label' => 'Record Tools',
            'meta' => 'Former DNS record side-menu links',
            'items' => [
                ['label' => 'Manage A Records', 'href' => $dnsBase . '&nsrecordtype=A', 'active' => $activeDnsTool === 'record-a'],
                ['label' => 'Manage AAAA Records', 'href' => $dnsBase . '&nsrecordtype=AAAA', 'active' => $activeDnsTool === 'record-aaaa'],
                ['label' => 'Manage CNAME Records', 'href' => $dnsBase . '&nsrecordtype=CNAME', 'active' => $activeDnsTool === 'record-cname'],
                ['label' => 'Manage MX Records', 'href' => $dnsBase . '&nsrecordtype=MX', 'active' => $activeDnsTool === 'record-mx'],
                ['label' => 'Manage NS Records', 'href' => $dnsBase . '&nsrecordtype=NS', 'active' => $activeDnsTool === 'record-ns'],
                ['label' => 'Manage TXT Records', 'href' => $dnsBase . '&nsrecordtype=TXT', 'active' => $activeDnsTool === 'record-txt'],
                ['label' => 'Manage SRV Records', 'href' => $dnsBase . '&nsrecordtype=SRV', 'active' => $activeDnsTool === 'record-srv'],
            ],
        ],
        [
            'label' => 'Authority Tools',
            'meta' => 'Security and zone authority',
            'items' => [
                ['label' => 'Manage DNSSEC Records', 'href' => $dnssecUrl, 'active' => $activeDnsTool === 'dnssec'],
                ['label' => 'Manage SOA Record', 'href' => $dnsBase . '&nsrecordtype=SOA', 'active' => $activeDnsTool === 'record-soa'],
            ],
        ],
        [
            'label' => 'Forwarding Tools',
            'meta' => 'Integrated into DNS Management',
            'items' => [
                ['label' => 'Domain Forwarding', 'href' => $domainForwardingUrl, 'active' => $activeDnsTool === 'domain-forwarding'],
                ['label' => 'Email Forwarding', 'href' => $emailForwardingUrl, 'active' => $activeDnsTool === 'email-forwarding'],
            ],
        ],
    ];

    $sideMenuMapHtml = '<div class="dm-dns-sidebar-map" aria-label="Former DNS side-menu replacement map">'
        . '<div class="dm-dns-sidebar-map-head"><div><span>Side Menu Replacement</span><strong>All DNS tools now live inside DNS Management</strong></div><p>The old DNS side-menu options are grouped here as internal DNS Management tools.</p></div>'
        . '<div class="dm-dns-sidebar-map-grid">';
    foreach ($sideMenuMapSections as $section) {
        $sideMenuMapHtml .= '<section class="dm-dns-sidebar-map-section">'
            . '<div class="dm-dns-sidebar-map-section-head"><strong>' . $e($section['label']) . '</strong><span>' . $e($section['meta']) . '</span></div>'
            . '<div class="dm-dns-sidebar-map-links">';
        foreach ($section['items'] as $item) {
            $sideMenuMapHtml .= '<a class="dm-dns-sidebar-map-link' . (!empty($item['active']) ? ' dm-active' : '') . '" href="' . $e($item['href']) . '">' . $e($item['label']) . '</a>';
        }
        $sideMenuMapHtml .= '</div></section>';
    }
    $sideMenuMapHtml .= '</div></div>';

    $forwardingIntegrationItems = [
        [
            'key' => 'domain-forwarding',
            'label' => 'Domain Forwarding',
            'meta' => 'Website Redirects',
            'summary' => 'Redirect domain traffic to another URL while staying inside the DNS Management workflow.',
            'href' => $domainForwardingUrl,
            'active' => $activeDnsTool === 'domain-forwarding',
        ],
        [
            'key' => 'email-forwarding',
            'label' => 'Email Forwarding',
            'meta' => 'Email Routing',
            'summary' => 'Forward domain email addresses without leaving the unified DNS Management workspace.',
            'href' => $emailForwardingUrl,
            'active' => $activeDnsTool === 'email-forwarding',
        ],
    ];

    $forwardingIntegrationHtml = '<div class="dm-dns-forwarding-integration" aria-label="Integrated forwarding tools">'
        . '<div class="dm-dns-forwarding-integration-head"><div><span>Integrated Forwarding</span><strong>Domain and Email Forwarding live inside DNS Management</strong></div><p>These used to feel like separate side-menu pages. They now sit with the DNS tools because forwarding changes are part of DNS/domain routing.</p></div>'
        . '<div class="dm-dns-forwarding-integration-grid">';
    foreach ($forwardingIntegrationItems as $item) {
        $forwardingIntegrationHtml .= '<a class="dm-dns-forwarding-card' . (!empty($item['active']) ? ' dm-active' : '') . '" href="' . $e($item['href']) . '">'
            . '<span>' . $e($item['meta']) . '</span>'
            . '<strong>' . $e($item['label']) . '</strong>'
            . '<p>' . $e($item['summary']) . '</p>'
            . '</a>';
    }
    $forwardingIntegrationHtml .= '</div></div>';

    $activeActionItems = [
        ['label' => 'Open Current Tool', 'meta' => $activeTitle, 'href' => ($_SERVER['REQUEST_URI'] ?? '#'), 'kind' => 'primary'],
        ['label' => 'DNS Records Home', 'meta' => 'Manage A Records', 'href' => $dnsBase . '&nsrecordtype=A', 'kind' => 'secondary'],
        ['label' => 'Security & Authority', 'meta' => 'DNSSEC / SOA', 'href' => $dnssecUrl, 'kind' => 'secondary'],
        ['label' => 'Forwarding Tools', 'meta' => 'Domain / Email', 'href' => $domainForwardingUrl, 'kind' => 'secondary'],
    ];

    if ($activeGroup === 'records') {
        $activeActionItems[1] = ['label' => 'Switch Record Type', 'meta' => 'Use shortcuts below', 'href' => $dnsBase . '&nsrecordtype=' . rawurlencode($recordType), 'kind' => 'secondary'];
    } elseif ($activeDnsTool === 'dnssec' || $activeDnsTool === 'record-soa') {
        $activeActionItems[1] = ['label' => 'SOA Record', 'meta' => 'Authority Settings', 'href' => $dnsBase . '&nsrecordtype=SOA', 'kind' => 'secondary'];
        $activeActionItems[2] = ['label' => 'DNSSEC Records', 'meta' => 'Security Records', 'href' => $dnssecUrl, 'kind' => 'secondary'];
    } elseif ($activeGroup === 'forwarding') {
        $activeActionItems[1] = ['label' => 'Domain Forwarding', 'meta' => 'Website Redirects', 'href' => $domainForwardingUrl, 'kind' => 'secondary'];
        $activeActionItems[2] = ['label' => 'Email Forwarding', 'meta' => 'Email Routing', 'href' => $emailForwardingUrl, 'kind' => 'secondary'];
    }

    $activeActionsHtml = '<div class="dm-dns-active-actions" aria-label="Active DNS tool actions">'
        . '<div class="dm-dns-active-actions-head"><div><span>Tool Actions</span><strong>' . $e($activeTitle) . '</strong></div><p>Use these shortcuts to stay inside the unified DNS Management flow.</p></div>'
        . '<div class="dm-dns-active-actions-grid">';
    foreach ($activeActionItems as $item) {
        $classes = 'dm-dns-active-action';
        if (($item['kind'] ?? '') === 'primary') {
            $classes .= ' dm-primary';
        }
        $activeActionsHtml .= '<a class="' . $classes . '" href="' . $e($item['href']) . '">'
            . '<strong>' . $e($item['label']) . '</strong>'
            . '<span>' . $e($item['meta']) . '</span>'
            . '</a>';
    }
    $activeActionsHtml .= '</div></div>';

    $commonTaskItems = [
        [
            'label' => 'Point Website',
            'meta' => 'A / AAAA / CNAME',
            'summary' => 'Use record tools when a website or app needs to point to hosting.',
            'href' => $dnsBase . '&nsrecordtype=A',
        ],
        [
            'label' => 'Set Up Email',
            'meta' => 'MX / TXT',
            'summary' => 'Use MX records for mail delivery and TXT records for SPF, DKIM, DMARC, or verification.',
            'href' => $dnsBase . '&nsrecordtype=MX',
        ],
        [
            'label' => 'Secure Authority',
            'meta' => 'DNSSEC / SOA',
            'summary' => 'Use DNSSEC and SOA tools for authority, signing, and zone-level checks.',
            'href' => $dnssecUrl,
        ],
        [
            'label' => 'Forward Traffic',
            'meta' => 'Domain / Email Forwarding',
            'summary' => 'Use forwarding tools when the goal is redirecting visitors or routing simple email aliases.',
            'href' => $domainForwardingUrl,
        ],
    ];

    $commonTasksHtml = '<div class="dm-dns-common-tasks" aria-label="Common DNS tasks">'
        . '<div class="dm-dns-common-tasks-head"><div><span>Common DNS Tasks</span><strong>Choose by what you are trying to do</strong></div><p>These shortcuts keep the old side-menu DNS tools inside one DNS Management workspace.</p></div>'
        . '<div class="dm-dns-common-tasks-grid">';
    foreach ($commonTaskItems as $item) {
        $commonTasksHtml .= '<a class="dm-dns-common-task" href="' . $e($item['href']) . '">'
            . '<strong>' . $e($item['label']) . '</strong>'
            . '<span>' . $e($item['meta']) . '</span>'
            . '<p>' . $e($item['summary']) . '</p>'
            . '</a>';
    }
    $commonTasksHtml .= '</div></div>';

    $workflowSteps = [
        'records' => [
            ['step' => '1', 'label' => 'Choose record type', 'summary' => 'Use the record shortcuts or tool grid to select A, AAAA, CNAME, MX, NS, TXT, SRV, or SOA.'],
            ['step' => '2', 'label' => 'Review current records', 'summary' => 'Use the preserved module output below to review existing DNS values before changing anything.'],
            ['step' => '3', 'label' => 'Save inside current tool', 'summary' => 'Use the existing ResellerClub/WHMCS buttons below so the current backend action remains unchanged.'],
        ],
        'security' => [
            ['step' => '1', 'label' => 'Review authority settings', 'summary' => 'Use DNSSEC or SOA depending on whether you are managing security records or zone authority details.'],
            ['step' => '2', 'label' => 'Confirm provider requirements', 'summary' => 'Match DS/DNSSEC/SOA values to the DNS provider or registrar instructions before saving.'],
            ['step' => '3', 'label' => 'Save inside current tool', 'summary' => 'Use the preserved WHMCS/ResellerClub output below for the actual change.'],
        ],
        'forwarding' => [
            ['step' => '1', 'label' => 'Pick forwarding type', 'summary' => 'Use Domain Forwarding for website redirects or Email Forwarding for address routing.'],
            ['step' => '2', 'label' => 'Review destination', 'summary' => 'Confirm the destination URL or email address before using the preserved module output.'],
            ['step' => '3', 'label' => 'Save inside current tool', 'summary' => 'Use the existing forms below so the current forwarding backend remains untouched.'],
        ],
    ];
    $activeWorkflowSteps = $workflowSteps[$activeGroup] ?? $workflowSteps['records'];
    $workflowHtml = '<div class="dm-dns-workflow-checklist" aria-label="DNS workspace workflow checklist">'
        . '<div class="dm-dns-workflow-head"><div><span>Workspace Flow</span><strong>' . $e($activeFlowLabel) . ' checklist</strong></div><a href="#dm-dns-current-module-output">Jump to Current Tool</a></div>'
        . '<div class="dm-dns-workflow-grid">';
    foreach ($activeWorkflowSteps as $step) {
        $workflowHtml .= '<div class="dm-dns-workflow-step">'
            . '<b>' . $e($step['step']) . '</b>'
            . '<div><strong>' . $e($step['label']) . '</strong><p>' . $e($step['summary']) . '</p></div>'
            . '</div>';
    }
    $workflowHtml .= '</div></div>';

    $moduleToolbarItems = [
        ['label' => 'Back to DNS Workspace', 'meta' => 'Top of this page', 'href' => '#dm-dns-unified-shell', 'active' => false, 'kind' => 'primary'],
        ['label' => 'Records', 'meta' => 'A / AAAA / CNAME / MX / NS / TXT / SRV', 'href' => $dnsBase . '&nsrecordtype=A', 'active' => $activeGroup === 'records', 'kind' => 'secondary'],
        ['label' => 'Security & Authority', 'meta' => 'DNSSEC / SOA', 'href' => $dnssecUrl, 'active' => $activeGroup === 'security', 'kind' => 'secondary'],
        ['label' => 'Forwarding', 'meta' => 'Domain / Email Forwarding', 'href' => $domainForwardingUrl, 'active' => $activeGroup === 'forwarding', 'kind' => 'secondary'],
    ];

    $moduleToolbarHtml = '<div class="dm-dns-module-toolbar" aria-label="Current module output navigation">'
        . '<div class="dm-dns-module-toolbar-head"><div><span>Inside Current Tool</span><strong>' . $e($activeTitle) . '</strong></div><p>Use the preserved module below, or jump back into the unified DNS workspace.</p></div>'
        . '<div class="dm-dns-module-toolbar-grid">';
    foreach ($moduleToolbarItems as $item) {
        $classes = 'dm-dns-module-toolbar-link';
        if (($item['kind'] ?? '') === 'primary') {
            $classes .= ' dm-primary';
        }
        if (!empty($item['active'])) {
            $classes .= ' dm-active';
        }
        $aria = !empty($item['active']) ? ' aria-current="page"' : '';
        $moduleToolbarHtml .= '<a class="' . $classes . '" href="' . $e($item['href']) . '"' . $aria . '>'
            . '<strong>' . $e($item['label']) . '</strong>'
            . '<span>' . $e($item['meta']) . '</span>'
            . '</a>';
    }
    $moduleToolbarHtml .= '</div></div>';

    // Patch 1131: normal managednszone now uses the fast native DNS feed.
    // Keep the older detailed helper/menu UI only for explicit legacy fallback.
    $isLiveNativeDnsFeed = ($scriptName === 'dnsmanagement.php'
        && $action === 'managednszone'
        && !isset($_GET['dmlegacydns'])
        && !isset($_GET['dmnoredirect']));

    // Patch 1156: DNSSEC and Domain Forwarding already have complete converted
    // module templates and the shared 1152 navigation. The older workspace
    // scaffold was only a build aid and should not appear above these tools.
    $isCleanConvertedDnsTool = ($isDnssec || $isDomainForwarding);

    $domainJson = json_encode($domainName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $domainMenuJson = json_encode($domainMenuHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $dnsFlowJson = json_encode($dnsFlowHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $dnsToolJson = json_encode($dnsToolHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $recordShortcutJson = json_encode($recordShortcutHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $recordFamilyJson = json_encode($recordFamilyHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $taskLauncherJson = json_encode($taskLauncherHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $toolDetailJson = json_encode($toolDetailHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $toolInputGuideJson = json_encode($toolInputGuideHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $toolExamplesJson = json_encode($toolExamplesHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $changeImpactJson = json_encode($changeImpactHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $validationGuideJson = json_encode($validationGuideHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $activeActionsJson = json_encode($activeActionsHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $sideMenuMapJson = json_encode($sideMenuMapHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $forwardingIntegrationJson = json_encode($forwardingIntegrationHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $commonTasksJson = json_encode($commonTasksHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $workflowJson = json_encode($workflowHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $moduleToolbarJson = json_encode($moduleToolbarHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $activeFlowLabelJson = json_encode($activeFlowLabel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $activeTitleJson = json_encode($activeTitle, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $activeMetaJson = json_encode($activeMeta, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $statusJson = json_encode($status ?: '-', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $registrarJson = json_encode($registrar ?: '-', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $autoRenewJson = json_encode($autoRenew, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $dnsBaseJson = json_encode($dnsBase . '&nsrecordtype=A', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $nameserversUrlJson = json_encode($baseClient . '#tabNameservers', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $privateNsUrlJson = json_encode('domainmanagement.php?action=childns&id=' . $domainId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $isLiveNativeDnsFeedJson = $isLiveNativeDnsFeed ? 'true' : 'false';
    $isCleanConvertedDnsToolJson = $isCleanConvertedDnsTool ? 'true' : 'false';

    return <<<HTML
<style>
body.whmcsbody.dm-dns-unified {
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
body.whmcsbody.dm-dns-unified .sidebar,
body.whmcsbody.dm-dns-unified .secondary-sidebar,
body.whmcsbody.dm-dns-unified .panel-sidebar,
body.whmcsbody.dm-dns-unified .dm-client-area-sidebar,
body.whmcsbody.dm-dns-unified aside,
body.whmcsbody.dm-dns-unified [class*="sidebar"] {
    display: none !important;
}
body.whmcsbody.dm-dns-unified #main-body .primary-content,
body.whmcsbody.dm-dns-unified #main-body .main-content,
body.whmcsbody.dm-dns-unified #main-body .col-md-9,
body.whmcsbody.dm-dns-unified #main-body .col-lg-9,
body.whmcsbody.dm-dns-unified #main-body .col-xl-9 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
}
body.whmcsbody.dm-dns-unified .dm-domain-section-menu {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    display: flex;
    flex-wrap: nowrap;
    list-style: none;
    margin: 0 0 14px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 0;
    scrollbar-color: rgba(22, 58, 95, .28) transparent;
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
}
body.whmcsbody.dm-dns-unified .dm-domain-section-menu::-webkit-scrollbar { height: 8px; }
body.whmcsbody.dm-dns-unified .dm-domain-section-menu::-webkit-scrollbar-track { background: transparent; }
body.whmcsbody.dm-dns-unified .dm-domain-section-menu::-webkit-scrollbar-thumb { background: rgba(22, 58, 95, .24); border-radius: 999px; }
body.whmcsbody.dm-dns-unified .dm-domain-section-menu li { margin: 0; }
body.whmcsbody.dm-dns-unified .dm-domain-section-menu a {
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
body.whmcsbody.dm-dns-unified .dm-domain-section-menu a:hover,
body.whmcsbody.dm-dns-unified .dm-domain-section-menu a:focus {
    background: #fff4eb;
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-domain-section-menu a:focus { box-shadow: inset 0 0 0 3px rgba(245, 130, 32, .22); }
body.whmcsbody.dm-dns-unified .dm-domain-section-menu a.dm-active {
    background: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-domain-section-menu a span {
    color: inherit;
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .04em;
    opacity: .78;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-workspace {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 18px;
    overflow: hidden;
}
body.whmcsbody.dm-dns-unified .dm-dns-header {
    align-items: center;
    background: var(--dm-navy);
    color: #fff;
    display: flex;
    gap: 14px;
    justify-content: space-between;
    padding: 14px 16px;
}
body.whmcsbody.dm-dns-unified .dm-dns-header h3 {
    color: #fff !important;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}
body.whmcsbody.dm-dns-unified .dm-dns-header span {
    color: rgba(255,255,255,.82);
    display: block;
    font-size: 12px;
    font-weight: 700;
    margin-top: 2px;
}
body.whmcsbody.dm-dns-unified .dm-dns-title-actions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}
body.whmcsbody.dm-dns-unified .dm-dns-title-actions a {
    align-items: center;
    background: #fff;
    border-radius: 6px;
    color: var(--dm-navy) !important;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    min-height: 34px;
    padding: 8px 11px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-title-actions a:hover,
body.whmcsbody.dm-dns-unified .dm-dns-title-actions a:focus {
    background: #fff4eb;
    color: var(--dm-orange-soft) !important;
    outline: none;
}
body.whmcsbody.dm-dns-unified .dm-dns-body { padding: 16px; }
body.whmcsbody.dm-dns-unified .dm-dns-status-strip,
body.whmcsbody.dm-dns-unified .dm-dns-guide-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody.dm-dns-unified .dm-dns-status-strip > div,
body.whmcsbody.dm-dns-unified .dm-dns-guide-card {
    background: #fff;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    min-width: 0;
    padding: 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-status-strip span,
body.whmcsbody.dm-dns-unified .dm-dns-guide-card span,
body.whmcsbody.dm-dns-unified .dm-dns-kicker {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-status-strip strong,
body.whmcsbody.dm-dns-unified .dm-dns-guide-card strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-guide-card p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 6px 0 0;
}
body.whmcsbody.dm-dns-unified .dm-dns-workspace-summary {
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(0, 1.4fr) minmax(240px, .6fr);
    margin: 0 0 14px;
}
body.whmcsbody.dm-dns-unified .dm-dns-current-card,
body.whmcsbody.dm-dns-unified .dm-dns-snapshot-card,
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcuts {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-current-card h4,
body.whmcsbody.dm-dns-unified .dm-dns-snapshot-card h4 {
    color: var(--dm-navy) !important;
    font-size: 16px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0 0 6px;
}
body.whmcsbody.dm-dns-unified .dm-dns-current-card p {
    color: var(--dm-muted);
    font-size: 13px;
    line-height: 1.45;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-current-meta,
body.whmcsbody.dm-dns-unified .dm-dns-snapshot-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-snapshot-grid { grid-template-columns: 1fr; }
body.whmcsbody.dm-dns-unified .dm-dns-current-meta div,
body.whmcsbody.dm-dns-unified .dm-dns-snapshot-grid div {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 7px;
    min-width: 0;
    padding: 9px 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-current-meta span,
body.whmcsbody.dm-dns-unified .dm-dns-snapshot-grid span,
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcuts-head span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-current-meta strong,
body.whmcsbody.dm-dns-unified .dm-dns-snapshot-grid strong,
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcuts-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-task-launcher {
    background: #fffdf9;
    border: 1px solid rgba(245, 130, 32, .22);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 14px;
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-task-launcher-head {
    align-items: center;
    display: flex;
    gap: 8px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-task-launcher-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    line-height: 1.2;
}
body.whmcsbody.dm-dns-unified .dm-dns-task-launcher-head span {
    color: var(--dm-muted);
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .05em;
    text-align: right;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-task-launcher-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-task-link {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    color: var(--dm-navy) !important;
    display: block;
    min-height: 60px;
    padding: 10px 12px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-task-link:hover,
body.whmcsbody.dm-dns-unified .dm-dns-task-link:focus {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .55);
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-task-link:focus { box-shadow: 0 0 0 3px rgba(245, 130, 32, .18); }
body.whmcsbody.dm-dns-unified .dm-dns-task-link.dm-active {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-task-link strong,
body.whmcsbody.dm-dns-unified .dm-dns-task-link span {
    color: inherit;
    display: block;
}
body.whmcsbody.dm-dns-unified .dm-dns-task-link strong { font-size: 13px; line-height: 1.25; }
body.whmcsbody.dm-dns-unified .dm-dns-task-link span {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-top: 4px;
    opacity: .76;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-detail {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-detail > div {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    min-width: 0;
    padding: 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-detail span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-detail strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
    margin-bottom: 5px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-detail p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 0;
}
body.whmcsbody.dm-dns-unified .dm-dns-input-guide,
body.whmcsbody.dm-dns-unified .dm-dns-example-guide {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(22, 58, 95, .06);
    margin: 0 0 14px;
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-input-guide-head {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-input-guide-head span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-input-guide-head strong,
body.whmcsbody.dm-dns-unified .dm-dns-example-guide-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-input-guide-head p,
body.whmcsbody.dm-dns-unified .dm-dns-example-guide-head p {
    color: var(--dm-muted);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    margin: 0;
    max-width: 420px;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-input-guide-grid,
body.whmcsbody.dm-dns-unified .dm-dns-example-guide-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-input-guide-item,
body.whmcsbody.dm-dns-unified .dm-dns-example-guide-item {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    padding: 10px 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-input-guide-item span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .08em;
    margin: 0 0 6px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-input-guide-item strong,
body.whmcsbody.dm-dns-unified .dm-dns-example-guide-item strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.35;
}
body.whmcsbody.dm-dns-unified .dm-dns-change-impact,
body.whmcsbody.dm-dns-unified .dm-dns-validation-guide {
    background: #f8fafc;
    border: 1px solid var(--dm-border);
    border-radius: 10px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 14px;
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-change-impact-head,
body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-head {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-change-impact-head span,
body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-head span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-change-impact-head strong,
body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-change-impact-head p,
body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-head p {
    color: var(--dm-muted);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    margin: 0;
    max-width: 430px;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-change-impact-grid,
body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-change-impact-item,
body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-item {
    background: #fff;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    padding: 10px 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-change-impact-item span,
body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-item span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .08em;
    margin: 0 0 6px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-change-impact-item strong,
body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-item strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.35;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-actions {
    background: #fffdf9;
    border: 1px solid rgba(245, 130, 32, .24);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 14px;
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-actions-head {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-actions-head span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-actions-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    line-height: 1.2;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-actions-head p {
    color: var(--dm-muted);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    margin: 0;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-actions-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-active-action {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    color: var(--dm-navy) !important;
    display: block;
    min-height: 58px;
    padding: 10px 12px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-action:hover,
body.whmcsbody.dm-dns-unified .dm-dns-active-action:focus {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .55);
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-action:focus { box-shadow: 0 0 0 3px rgba(245, 130, 32, .18); }
body.whmcsbody.dm-dns-unified .dm-dns-active-action.dm-primary {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-action.dm-primary:hover,
body.whmcsbody.dm-dns-unified .dm-dns-active-action.dm-primary:focus {
    background: var(--dm-orange-soft);
    border-color: var(--dm-orange-soft);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-action strong,
body.whmcsbody.dm-dns-unified .dm-dns-active-action span {
    color: inherit;
    display: block;
}
body.whmcsbody.dm-dns-unified .dm-dns-active-action strong { font-size: 13px; line-height: 1.25; }
body.whmcsbody.dm-dns-unified .dm-dns-active-action span {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-top: 4px;
    opacity: .78;
    text-transform: uppercase;
}

body.whmcsbody.dm-dns-unified .dm-dns-common-tasks {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 14px;
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-common-tasks-head {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-common-tasks-head span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-common-tasks-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    line-height: 1.2;
}
body.whmcsbody.dm-dns-unified .dm-dns-common-tasks-head p {
    color: var(--dm-muted);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    margin: 0;
    max-width: 420px;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-common-tasks-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-common-task {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    color: var(--dm-navy) !important;
    display: block;
    min-height: 108px;
    padding: 11px 12px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-common-task:hover,
body.whmcsbody.dm-dns-unified .dm-dns-common-task:focus {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .55);
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-common-task:focus { box-shadow: 0 0 0 3px rgba(245, 130, 32, .18); }
body.whmcsbody.dm-dns-unified .dm-dns-common-task strong,
body.whmcsbody.dm-dns-unified .dm-dns-common-task span {
    color: inherit;
    display: block;
}
body.whmcsbody.dm-dns-unified .dm-dns-common-task strong { font-size: 14px; line-height: 1.25; }
body.whmcsbody.dm-dns-unified .dm-dns-common-task span {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-top: 4px;
    opacity: .76;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-common-task p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.4;
    margin: 8px 0 0;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-checklist {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 14px;
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-head {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-head span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    line-height: 1.2;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-head a {
    background: var(--dm-navy);
    border-radius: 6px;
    color: #fff !important;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    min-height: 34px;
    padding: 8px 11px;
    text-decoration: none !important;
    white-space: nowrap;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-head a:hover,
body.whmcsbody.dm-dns-unified .dm-dns-workflow-head a:focus {
    background: var(--dm-navy-hover);
    color: #fff !important;
    outline: none;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-grid {
    display: grid;
    gap: 9px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-step {
    align-items: flex-start;
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    display: flex;
    gap: 10px;
    min-width: 0;
    padding: 11px 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-step b {
    align-items: center;
    background: var(--dm-orange);
    border-radius: 999px;
    color: #fff;
    display: inline-flex;
    flex: 0 0 26px;
    font-size: 12px;
    height: 26px;
    justify-content: center;
    line-height: 1;
    margin-top: 1px;
    width: 26px;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-step strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-workflow-step p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.4;
    margin: 5px 0 0;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcuts { margin: 0 0 12px; }
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcuts-head {
    align-items: center;
    display: flex;
    gap: 8px;
    justify-content: space-between;
    margin-bottom: 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcuts-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcut {
    background: #f8fafc;
    border: 1px solid var(--dm-border);
    border-radius: 7px;
    color: var(--dm-navy) !important;
    display: inline-flex;
    flex-direction: column;
    min-width: 82px;
    padding: 8px 10px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcut:hover,
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcut:focus {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .55);
    color: var(--dm-orange-soft) !important;
    outline: none;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcut.dm-active {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcut strong,
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcut span {
    color: inherit;
    display: block;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcut strong { font-size: 13px; line-height: 1.1; }
body.whmcsbody.dm-dns-unified .dm-dns-record-shortcut span {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-top: 3px;
    opacity: .78;
    text-transform: uppercase;
}


body.whmcsbody.dm-dns-unified .dm-dns-record-families {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 12px;
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-families-head {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-families-head span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-families-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    line-height: 1.2;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-families-head p {
    color: var(--dm-muted);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    margin: 0;
    max-width: 420px;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family-grid {
    display: grid;
    gap: 9px;
    grid-template-columns: repeat(auto-fit, minmax(205px, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    min-width: 0;
    padding: 11px 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family.dm-active {
    background: #fffdf9;
    border-color: rgba(245, 130, 32, .42);
    box-shadow: inset 4px 0 0 var(--dm-orange);
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family-title span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family-title strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.4;
    margin: 7px 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family-links {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family-chip {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 999px;
    color: var(--dm-navy) !important;
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    line-height: 1;
    min-height: 28px;
    padding: 8px 10px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family-chip:hover,
body.whmcsbody.dm-dns-unified .dm-dns-record-family-chip:focus {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .55);
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-record-family-chip.dm-active {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff !important;
}

body.whmcsbody.dm-dns-unified .dm-dns-flow-tabs {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-flow-tab {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    color: var(--dm-navy) !important;
    display: block;
    min-height: 66px;
    padding: 12px 13px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-flow-tab:hover,
body.whmcsbody.dm-dns-unified .dm-dns-flow-tab:focus {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .55);
    color: var(--dm-orange-soft) !important;
    outline: none;
}
body.whmcsbody.dm-dns-unified .dm-dns-flow-tab.dm-active {
    background: var(--dm-navy);
    border-color: var(--dm-navy);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-flow-tab strong,
body.whmcsbody.dm-dns-unified .dm-dns-flow-tab span {
    color: inherit;
    display: block;
}
body.whmcsbody.dm-dns-unified .dm-dns-flow-tab strong {
    font-size: 14px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-flow-tab span {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-top: 5px;
    opacity: .78;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-path-strip {
    align-items: center;
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    color: var(--dm-muted);
    display: flex;
    flex-wrap: wrap;
    font-size: 12px;
    font-weight: 700;
    gap: 6px;
    margin: 0 0 12px;
    padding: 10px 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-path-strip strong {
    color: var(--dm-navy);
}
body.whmcsbody.dm-dns-unified .dm-dns-path-strip span {
    color: var(--dm-orange-soft);
    font-weight: 900;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-panel {
    background: #f8fafc;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    margin: 0 0 14px;
    padding: 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-panel-head {
    align-items: center;
    display: flex;
    gap: 10px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-panel-head strong {
    color: var(--dm-navy);
    font-size: 15px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-panel-head span {
    color: var(--dm-muted);
    font-size: 12px;
    font-weight: 700;
}

body.whmcsbody.dm-dns-unified .dm-dns-tool-groups {
    display: grid;
    gap: 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-group {
    background: rgba(255,255,255,.72);
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    padding: 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-group.dm-active-group {
    background: #fffdf9;
    border-color: rgba(245, 130, 32, .32);
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-group-head {
    align-items: center;
    display: flex;
    gap: 8px;
    justify-content: space-between;
    margin: 0 0 8px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-group-head strong {
    color: var(--dm-navy);
    font-size: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-group-head span {
    color: var(--dm-muted);
    font-size: 11px;
    font-weight: 700;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-link {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    color: var(--dm-navy) !important;
    display: block;
    min-height: 58px;
    padding: 10px 12px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-link:hover,
body.whmcsbody.dm-dns-unified .dm-dns-tool-link:focus {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .55);
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-link:focus { box-shadow: 0 0 0 3px rgba(245, 130, 32, .18); }
body.whmcsbody.dm-dns-unified .dm-dns-tool-link.dm-active {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-link strong {
    color: inherit;
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-tool-link span {
    color: inherit;
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-top: 4px;
    opacity: .75;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-note {
    background: #fff8e5;
    border: 1px solid rgba(216, 116, 31, .22);
    border-radius: 8px;
    color: var(--dm-text);
    font-size: 13px;
    line-height: 1.45;
    margin: 0 0 14px;
    padding: 12px 14px;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-heading {
    align-items: center;
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    display: flex;
    gap: 10px;
    justify-content: space-between;
    margin: 0 0 14px;
    padding: 11px 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-heading span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-heading strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    line-height: 1.2;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-heading em {
    color: var(--dm-muted);
    font-size: 12px;
    font-style: normal;
    font-weight: 800;
    letter-spacing: .04em;
    text-align: right;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 18px;
    overflow: hidden;
    padding: 14px;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area > .panel:first-child,
body.whmcsbody.dm-dns-unified .dm-dns-module-area > .card:first-child,
body.whmcsbody.dm-dns-unified .dm-dns-module-area > .well:first-child,
body.whmcsbody.dm-dns-unified .dm-dns-module-area > form:first-child,
body.whmcsbody.dm-dns-unified .dm-dns-module-area > table:first-child {
    margin-top: 0 !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area .panel,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .card,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .well,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .box,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .tab-content {
    border-color: var(--dm-border) !important;
    border-radius: 8px !important;
    box-shadow: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area .panel-heading,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .card-header,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .box-header,
body.whmcsbody.dm-dns-unified .dm-dns-module-area th,
body.whmcsbody.dm-dns-unified .dm-dns-module-area thead td {
    background: var(--dm-navy) !important;
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area table {
    background: #fff;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area table th,
body.whmcsbody.dm-dns-unified .dm-dns-module-area table td {
    border-color: var(--dm-border-soft) !important;
    vertical-align: middle !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-primary,
body.whmcsbody.dm-dns-unified .dm-dns-module-area button[type="submit"].btn-primary,
body.whmcsbody.dm-dns-unified .dm-dns-module-area input[type="submit"].btn-primary,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-success,
body.whmcsbody.dm-dns-unified .dm-dns-module-area input[type="button"].btn-primary {
    background: var(--dm-orange) !important;
    border-color: var(--dm-orange) !important;
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-primary:hover,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-primary:focus,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-success:hover,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-success:focus {
    background: var(--dm-orange-soft) !important;
    border-color: var(--dm-orange-soft) !important;
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-default,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-secondary {
    background: var(--dm-navy) !important;
    border-color: var(--dm-navy) !important;
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-default:hover,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-default:focus,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-secondary:hover,
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-secondary:focus {
    background: var(--dm-navy-hover) !important;
    border-color: var(--dm-navy-hover) !important;
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area .btn-danger,
body.whmcsbody.dm-dns-unified .dm-dns-module-area input[type="submit"].btn-danger,
body.whmcsbody.dm-dns-unified .dm-dns-module-area button[type="submit"].btn-danger {
    background: var(--dm-red) !important;
    border-color: var(--dm-red) !important;
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area a:not(.btn) {
    color: var(--dm-navy) !important;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area a:not(.btn):hover,
body.whmcsbody.dm-dns-unified .dm-dns-module-area a:not(.btn):focus {
    color: var(--dm-orange-soft) !important;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area input[type="checkbox"] { accent-color: var(--dm-orange); }
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar {
    background: #fffdf9;
    border: 1px solid rgba(245, 130, 32, .24);
    border-radius: 8px;
    margin: 0 0 14px;
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-head {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-head span {
    color: var(--dm-muted);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    line-height: 1.2;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-head p {
    color: var(--dm-muted);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    margin: 0;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    color: var(--dm-navy) !important;
    display: block;
    min-height: 56px;
    padding: 10px 12px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link:hover,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link:focus {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .55);
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link:focus { box-shadow: 0 0 0 3px rgba(245, 130, 32, .18); }
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:hover,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-primary:focus {
    background: var(--dm-orange-soft);
    border-color: var(--dm-orange-soft);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link.dm-active:not(.dm-primary) {
    border-color: rgba(245, 130, 32, .65);
    box-shadow: inset 4px 0 0 var(--dm-orange);
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link strong,
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link span {
    color: inherit;
    display: block;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link strong { font-size: 13px; line-height: 1.25; }
body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-link span {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-top: 4px;
    opacity: .78;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area input,
body.whmcsbody.dm-dns-unified .dm-dns-module-area select,
body.whmcsbody.dm-dns-unified .dm-dns-module-area textarea {
    border-color: var(--dm-border) !important;
    border-radius: 6px !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-module-area input:focus,
body.whmcsbody.dm-dns-unified .dm-dns-module-area select:focus,
body.whmcsbody.dm-dns-unified .dm-dns-module-area textarea:focus {
    border-color: var(--dm-orange) !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .16) !important;
    outline: none !important;
}
@media (max-width: 767px) {
    body.whmcsbody.dm-dns-unified .dm-dns-header,
    body.whmcsbody.dm-dns-unified .dm-dns-tool-panel-head,
    body.whmcsbody.dm-dns-unified .dm-dns-task-launcher-head,
    body.whmcsbody.dm-dns-unified .dm-dns-active-actions-head,
    body.whmcsbody.dm-dns-unified .dm-dns-common-tasks-head,
    body.whmcsbody.dm-dns-unified .dm-dns-workflow-head,
    body.whmcsbody.dm-dns-unified .dm-dns-module-heading,
    body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-head,
    body.whmcsbody.dm-dns-unified .dm-dns-input-guide-head,
    body.whmcsbody.dm-dns-unified .dm-dns-example-guide-head,
    body.whmcsbody.dm-dns-unified .dm-dns-change-impact-head,
    body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-head {
        align-items: flex-start;
        flex-direction: column;
    }
    body.whmcsbody.dm-dns-unified .dm-dns-task-launcher-head span,
    body.whmcsbody.dm-dns-unified .dm-dns-active-actions-head p,
    body.whmcsbody.dm-dns-unified .dm-dns-common-tasks-head p,
    body.whmcsbody.dm-dns-unified .dm-dns-record-families-head p,
    body.whmcsbody.dm-dns-unified .dm-dns-module-heading em,
    body.whmcsbody.dm-dns-unified .dm-dns-module-toolbar-head p,
    body.whmcsbody.dm-dns-unified .dm-dns-input-guide-head p,
    body.whmcsbody.dm-dns-unified .dm-dns-example-guide-head p,
    body.whmcsbody.dm-dns-unified .dm-dns-change-impact-head p,
    body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-head p {
        text-align: left;
    }
    body.whmcsbody.dm-dns-unified .dm-dns-status-strip,
    body.whmcsbody.dm-dns-unified .dm-dns-guide-grid,
    body.whmcsbody.dm-dns-unified .dm-dns-flow-tabs,
    body.whmcsbody.dm-dns-unified .dm-dns-workspace-summary,
    body.whmcsbody.dm-dns-unified .dm-dns-current-meta,
    body.whmcsbody.dm-dns-unified .dm-dns-tool-detail,
    body.whmcsbody.dm-dns-unified .dm-dns-workflow-grid,
    body.whmcsbody.dm-dns-unified .dm-dns-record-family-grid,
    body.whmcsbody.dm-dns-unified .dm-dns-input-guide-grid,
    body.whmcsbody.dm-dns-unified .dm-dns-example-guide-grid,
    body.whmcsbody.dm-dns-unified .dm-dns-change-impact-grid,
    body.whmcsbody.dm-dns-unified .dm-dns-validation-guide-grid {
        grid-template-columns: 1fr;
    }
}

body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map {
    background: #fff;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    margin: 0 0 14px;
    overflow: hidden;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-head {
    align-items: flex-start;
    background: #f8fafc;
    border-bottom: 1px solid var(--dm-border-soft);
    display: flex;
    gap: 14px;
    justify-content: space-between;
    padding: 12px 14px;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-head span,
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-section-head span {
    color: var(--dm-muted);
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    margin-top: 2px;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-head p {
    color: var(--dm-muted);
    font-size: 12px;
    font-weight: 650;
    margin: 0;
    max-width: 430px;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) minmax(0, 1fr);
    padding: 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-section {
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    padding: 11px;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-section-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.2;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-links {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-link {
    align-items: center;
    background: #fff;
    border: 1px solid rgba(22,58,95,.16);
    border-radius: 6px;
    color: var(--dm-navy) !important;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    min-height: 31px;
    padding: 7px 9px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-link:hover,
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-link:focus {
    background: #fff4eb;
    border-color: rgba(245,130,32,.35);
    color: var(--dm-orange-soft) !important;
    outline: none;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-link.dm-active {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-link.dm-active * {
    color: #fff !important;
}

body.whmcsbody.dm-dns-unified .dm-dns-forwarding-integration {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 10px;
    box-shadow: var(--dm-shadow);
    margin: 0 0 14px;
    padding: 13px;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-integration-head {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 0 10px;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-integration-head span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-integration-head strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    line-height: 1.25;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-integration-head p {
    color: var(--dm-muted);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    margin: 0;
    max-width: 520px;
    text-align: right;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-integration-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card {
    background: #f8fafc;
    border: 1px solid var(--dm-border-soft);
    border-radius: 9px;
    color: var(--dm-navy) !important;
    display: block;
    min-height: 104px;
    padding: 12px;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card:hover,
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card:focus {
    background: #fff4eb;
    border-color: rgba(245, 130, 32, .42);
    color: var(--dm-orange-soft) !important;
    outline: none;
    text-decoration: none !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card:focus { box-shadow: 0 0 0 3px rgba(245, 130, 32, .18); }
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card span {
    color: inherit;
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .08em;
    margin: 0 0 5px;
    opacity: .78;
    text-transform: uppercase;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card strong {
    color: inherit;
    display: block;
    font-size: 14px;
    line-height: 1.25;
    margin-bottom: 6px;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 0;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card.dm-active {
    background: var(--dm-orange);
    border-color: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card.dm-active p,
body.whmcsbody.dm-dns-unified .dm-dns-forwarding-card.dm-active * {
    color: #fff !important;
}

body.whmcsbody.dm-dns-unified .dm-dns-selection-pulse {
    animation: dmDnsSelectionPulse 1.2s ease-out 1;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, .26), var(--dm-shadow) !important;
}
body.whmcsbody.dm-dns-unified .dm-dns-internal-selection-note {
    background: #fff7ef;
    border: 1px solid rgba(245, 130, 32, .28);
    border-radius: 8px;
    color: var(--dm-text);
    font-size: 12px;
    line-height: 1.45;
    margin: 0 0 14px;
    padding: 10px 12px;
}
body.whmcsbody.dm-dns-unified .dm-dns-internal-selection-note strong {
    color: var(--dm-navy);
}
@keyframes dmDnsSelectionPulse {
    0% { box-shadow: 0 0 0 0 rgba(245, 130, 32, .42), var(--dm-shadow); }
    100% { box-shadow: 0 0 0 12px rgba(245, 130, 32, 0), var(--dm-shadow); }
}

@media (max-width: 991px) {
    body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-grid {
        grid-template-columns: 1fr;
    }
    body.whmcsbody.dm-dns-unified .dm-dns-forwarding-integration-grid {
        grid-template-columns: 1fr;
    }
    body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-head {
        flex-direction: column;
    }
    body.whmcsbody.dm-dns-unified .dm-dns-sidebar-map-head p {
        max-width: none;
        text-align: left;
    }
}

</style>
<script>
(function () {
    var domainName = {$domainJson};
    var domainMenuHtml = {$domainMenuJson};
    var dnsFlowHtml = {$dnsFlowJson};
    var dnsToolHtml = {$dnsToolJson};
    var recordShortcutHtml = {$recordShortcutJson};
    var recordFamilyHtml = {$recordFamilyJson};
    var taskLauncherHtml = {$taskLauncherJson};
    var toolDetailHtml = {$toolDetailJson};
    var toolInputGuideHtml = {$toolInputGuideJson};
    var toolExamplesHtml = {$toolExamplesJson};
    var changeImpactHtml = {$changeImpactJson};
    var validationGuideHtml = {$validationGuideJson};
    var activeActionsHtml = {$activeActionsJson};
    var sideMenuMapHtml = {$sideMenuMapJson};
    var forwardingIntegrationHtml = {$forwardingIntegrationJson};
    var commonTasksHtml = {$commonTasksJson};
    var workflowHtml = {$workflowJson};
    var moduleToolbarHtml = {$moduleToolbarJson};
    var activeFlowLabel = {$activeFlowLabelJson};
    var activeTitle = {$activeTitleJson};
    var activeMeta = {$activeMetaJson};
    var isLiveNativeDnsFeed = {$isLiveNativeDnsFeedJson};
    var isCleanConvertedDnsTool = {$isCleanConvertedDnsToolJson};
    var headerActionsHtml = isLiveNativeDnsFeed ? '' : (
        '<div class="dm-dns-title-actions">'
        + '<a href="' + dnsHomeUrl + '">DNS Records</a>'
        + '<a href="' + nameserversUrl + '">Nameservers</a>'
        + '<a href="' + privateNsUrl + '">Private Nameservers</a>'
        + '</div>'
    );
    var statusText = {$statusJson};
    var registrarText = {$registrarJson};
    var autoRenewText = {$autoRenewJson};
    var dnsHomeUrl = {$dnsBaseJson};
    var nameserversUrl = {$nameserversUrlJson};
    var privateNsUrl = {$privateNsUrlJson};

    if (isLiveNativeDnsFeed || isCleanConvertedDnsTool) {
        sideMenuMapHtml = '';
        forwardingIntegrationHtml = '';
        taskLauncherHtml = '';
        toolDetailHtml = '';
        toolInputGuideHtml = '';
        toolExamplesHtml = '';
        changeImpactHtml = '';
        validationGuideHtml = '';
        activeActionsHtml = '';
        commonTasksHtml = '';
        workflowHtml = '';
        recordShortcutHtml = '';
        recordFamilyHtml = '';
        dnsFlowHtml = '';
        dnsToolHtml = '';
        // Patch 1137: the old inner DNS module toolbar is redundant on the live
        // fast editor page. Keep it only for explicit legacy fallback pages.
        moduleToolbarHtml = '';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function findMainContent() {
        var selectors = [
            '#main-body .primary-content',
            '#main-body .main-content',
            '#main-body .col-md-9',
            '#main-body .col-lg-9',
            '#main-body .col-xl-9',
            '.primary-content',
            '.main-content'
        ];
        for (var i = 0; i < selectors.length; i++) {
            var node = document.querySelector(selectors[i]);
            if (node) {
                return node;
            }
        }
        return document.querySelector('#main-body') || document.querySelector('main') || document.body;
    }

    function buildShell() {
        var shell = document.createElement('div');
        shell.id = 'dm-dns-unified-shell';

        // Patch 1151: the large DNS workspace scaffold above the fast native
        // editor was a conversion/build aid. On the normal live feed, retain
        // only the shared domain-management tabs and let the real DNS editor
        // follow immediately. Explicit legacy fallback pages keep the workspace.
        if (isLiveNativeDnsFeed || isCleanConvertedDnsTool) {
            shell.innerHTML = domainMenuHtml;
            return shell;
        }

        shell.innerHTML = ''
            + domainMenuHtml
            + '<div class="dm-dns-workspace">'
            + '  <div class="dm-dns-header">'
            + '    <div><h3>Manage Domain: DNS Management</h3><span>' + escapeHtml(domainName) + '</span></div>'
            +      headerActionsHtml
            + '  </div>'
            + '  <div class="dm-dns-body">'
            + '    <div class="dm-dns-status-strip">'
            + '      <div><span>Current Tool</span><strong>' + escapeHtml(activeTitle) + '</strong></div>'
            + '      <div><span>Flow Group</span><strong>' + escapeHtml(activeFlowLabel) + '</strong></div>'
            + '      <div><span>' + (isLiveNativeDnsFeed ? 'Editor' : 'Backend') + '</span><strong>' + (isLiveNativeDnsFeed ? 'Fast DNS editor' : 'Existing module actions') + '</strong></div>'
            + '    </div>'
            + '    <div class="dm-dns-path-strip"><strong>DNS Management</strong><span>›</span><strong>' + escapeHtml(activeFlowLabel) + '</strong><span>›</span><strong>' + escapeHtml(activeTitle) + '</strong></div>'
            +      sideMenuMapHtml
            +      forwardingIntegrationHtml
            +      taskLauncherHtml
            +      toolDetailHtml
            +      toolInputGuideHtml
            +      toolExamplesHtml
            +      changeImpactHtml
            +      validationGuideHtml
            +      activeActionsHtml
            +      commonTasksHtml
            +      workflowHtml
            + '    <div class="dm-dns-workspace-summary">'
            + '      <div class="dm-dns-current-card"><span class="dm-dns-kicker">Current DNS Workspace</span><h4>' + escapeHtml(activeTitle) + '</h4><p>' + (isLiveNativeDnsFeed ? 'The fast DNS editor below manages all DNS record types inside the ResellerClub page.' : 'This selected tool stays inside DNS Management while the DNS editor below stays connected to the existing WHMCS backend.') + '</p><div class="dm-dns-current-meta"><div><span>Tool Type</span><strong>' + escapeHtml(activeMeta) + '</strong></div><div><span>Flow</span><strong>' + escapeHtml(activeFlowLabel) + '</strong></div><div><span>' + (isLiveNativeDnsFeed ? 'Editor' : 'Backend') + '</span><strong>' + (isLiveNativeDnsFeed ? 'Fast' : 'Preserved') + '</strong></div></div></div>'
            + '      <div class="dm-dns-snapshot-card"><h4>Domain Snapshot</h4><div class="dm-dns-snapshot-grid"><div><span>Domain</span><strong>' + escapeHtml(domainName) + '</strong></div><div><span>Status</span><strong>' + escapeHtml(statusText) + '</strong></div><div><span>Registrar</span><strong>' + escapeHtml(registrarText) + '</strong></div><div><span>Auto Renew</span><strong>' + escapeHtml(autoRenewText) + '</strong></div></div></div>'
            + '    </div>'
            +      recordShortcutHtml
            +      recordFamilyHtml
            +      dnsFlowHtml
            + (isLiveNativeDnsFeed ? '' : '    <div class="dm-dns-tool-panel">'
            + '      <div class="dm-dns-tool-panel-head"><strong>DNS Management Tools</strong><span>Records, DNSSEC, SOA, Domain Forwarding, and Email Forwarding in one flow</span></div>'
            +        dnsToolHtml
            + '    </div>')
            + (isLiveNativeDnsFeed ? '' : '    <div class="dm-dns-guide-grid">'
            + '      <div class="dm-dns-guide-card"><span>Records</span><strong>Record tools flow together</strong><p>A, AAAA, CNAME, MX, NS, TXT, and SRV stay together as record tools inside DNS Management.</p></div>'
            + '      <div class="dm-dns-guide-card"><span>Security</span><strong>DNSSEC and SOA belong here</strong><p>DNSSEC and SOA are grouped as security/authority tools instead of separate side-menu destinations.</p></div>'
            + '      <div class="dm-dns-guide-card"><span>Forwarding</span><strong>Forwarding stays integrated</strong><p>Domain Forwarding and Email Forwarding are handled as DNS tools in the same workspace.</p></div>'
            + '    </div>'
            + '    <div class="dm-dns-note"><strong>Existing functionality is preserved.</strong> The section below contains the selected DNS tool, wrapped inside the new ClouDNS-style no-side-menu layout.</div>')
            + '  </div>'
            + '</div>';
        return shell;
    }

    function isUtilityNode(node) {
        if (!node || node.nodeType !== 1) {
            return true;
        }
        if (node.id === 'dm-dns-unified-shell' || node.classList.contains('dm-dns-module-area')) {
            return true;
        }
        var tag = node.tagName.toLowerCase();
        if (tag === 'script' || tag === 'style') {
            return true;
        }
        return false;
    }

    function wrapExistingOutput(content) {
        // Patch 1157: DNSSEC and Domain Forwarding already render their real
        // converted module content directly below the shared navigation. Do not
        // wrap them in the old build-time "Current Module Output" scaffold or
        // its now-empty secondary toolbar/header.
        if (isCleanConvertedDnsTool) {
            return;
        }
        if (document.querySelector('.dm-dns-module-area')) {
            return;
        }
        var shell = document.getElementById('dm-dns-unified-shell');
        var wrapper = document.createElement('div');
        wrapper.className = 'dm-dns-module-area';
        wrapper.id = 'dm-dns-current-module-output';
        wrapper.setAttribute('data-dm-preserves-existing-output', '1');
        var moduleHeading = document.createElement('div');
        moduleHeading.className = 'dm-dns-module-heading';
        moduleHeading.innerHTML = '<div><span>Current Module Output</span><strong>' + escapeHtml(activeTitle) + '</strong></div><em>Existing forms and actions preserved</em>';
        wrapper.appendChild(moduleHeading);
        if (moduleToolbarHtml) {
            var toolbar = document.createElement('div');
            toolbar.innerHTML = moduleToolbarHtml;
            while (toolbar.firstChild) {
                wrapper.appendChild(toolbar.firstChild);
            }
        }

        var cursor = shell ? shell.nextSibling : content.firstChild;
        var moved = false;
        while (cursor) {
            var next = cursor.nextSibling;
            if (cursor.nodeType === 1 && !isUtilityNode(cursor)) {
                wrapper.appendChild(cursor);
                moved = true;
            } else if (cursor.nodeType === 3 && cursor.textContent.trim() !== '') {
                wrapper.appendChild(cursor);
                moved = true;
            }
            cursor = next;
        }
        if (moved) {
            content.appendChild(wrapper);
        }
    }

    function normalizePageTitle(content) {
        var headings = content.querySelectorAll('h1, h2');
        for (var i = 0; i < headings.length; i++) {
            var text = (headings[i].textContent || '').trim();
            if (/^(dashboard|dns management|dnssec management|domain forwarding|email forwarding|manage domain:\s*(dns management|dnssec management|domain forwarding|email forwarding))$/i.test(text)) {
                headings[i].style.display = 'none';
            }
        }
    }


    var dmDnsToolConfigs = {
        'record-a': { title: 'Manage A Records', group: 'records', flow: 'Records', meta: 'IPv4 Records', target: '.dm-dns-tool-panel' },
        'record-aaaa': { title: 'Manage AAAA Records', group: 'records', flow: 'Records', meta: 'IPv6 Records', target: '.dm-dns-tool-panel' },
        'record-cname': { title: 'Manage CNAME Records', group: 'records', flow: 'Records', meta: 'Alias Records', target: '.dm-dns-tool-panel' },
        'record-mx': { title: 'Manage MX Records', group: 'records', flow: 'Records', meta: 'Mail Records', target: '.dm-dns-tool-panel' },
        'record-ns': { title: 'Manage NS Records', group: 'records', flow: 'Records', meta: 'Delegation Records', target: '.dm-dns-tool-panel' },
        'record-txt': { title: 'Manage TXT Records', group: 'records', flow: 'Records', meta: 'Verification Records', target: '.dm-dns-tool-panel' },
        'record-srv': { title: 'Manage SRV Records', group: 'records', flow: 'Records', meta: 'Service Records', target: '.dm-dns-tool-panel' },
        'record-soa': { title: 'Manage SOA Record', group: 'security', flow: 'Security & Authority', meta: 'Authority', target: '.dm-dns-tool-panel' },
        'dnssec': { title: 'Manage DNSSEC Records', group: 'security', flow: 'Security & Authority', meta: 'DNS Security', target: '.dm-dns-tool-panel' },
        'domain-forwarding': { title: 'Domain Forwarding', group: 'forwarding', flow: 'Forwarding', meta: 'Website Redirects', target: '.dm-dns-forwarding-integration' },
        'email-forwarding': { title: 'Email Forwarding', group: 'forwarding', flow: 'Forwarding', meta: 'Email Routing', target: '.dm-dns-forwarding-integration' }
    };

    var dmDnsInternalLinkSelector = [
        '.dm-dns-flow-tab',
        '.dm-dns-tool-link',
        '.dm-dns-record-shortcut',
        '.dm-dns-record-family-chip',
        '.dm-dns-task-link',
        '.dm-dns-sidebar-map-link',
        '.dm-dns-forwarding-card',
        '.dm-dns-active-action',
        '.dm-dns-common-task',
        '.dm-dns-module-toolbar-link'
    ].join(',');

    function dmDnsInferToolKey(link) {
        if (!link) {
            return '';
        }
        var href = (link.getAttribute('href') || '').toLowerCase();
        var text = (link.textContent || '').toLowerCase();
        var haystack = href + ' ' + text;
        if (haystack.indexOf('email forwarding') !== -1 || haystack.indexOf('emailmanagement.php') !== -1 || haystack.indexOf('domainemailforwarding') !== -1) {
            return 'email-forwarding';
        }
        if (haystack.indexOf('domain forwarding') !== -1 || haystack.indexOf('domainforwarding.php') !== -1 || haystack.indexOf('managedomfwd') !== -1) {
            return 'domain-forwarding';
        }
        if (haystack.indexOf('dnssec') !== -1 || haystack.indexOf('dnsseczone') !== -1) {
            return 'dnssec';
        }
        if (haystack.indexOf('soa') !== -1 || haystack.indexOf('nsrecordtype=soa') !== -1) {
            return 'record-soa';
        }
        var typeMatch = href.match(/[?&]nsrecordtype=([^&#]+)/);
        var type = typeMatch ? decodeURIComponent(typeMatch[1]).toLowerCase() : '';
        if (!type) {
            var types = ['aaaa', 'cname', 'mx', 'ns', 'txt', 'srv', 'a'];
            for (var i = 0; i < types.length; i++) {
                var candidate = types[i];
                var re = new RegExp('(^|\\s|/)manage\\s+' + candidate + '(\\s|$)', 'i');
                if (re.test(text) || text === candidate || text.indexOf(candidate + ' records') !== -1) {
                    type = candidate;
                    break;
                }
            }
        }
        if (type) {
            if (type === 'dnssec') {
                return 'dnssec';
            }
            return 'record-' + type;
        }
        if (haystack.indexOf('security & authority') !== -1 || haystack.indexOf('secure authority') !== -1) {
            return 'dnssec';
        }
        if (haystack.indexOf('forwarding tools') !== -1 || haystack.indexOf('forward traffic') !== -1) {
            return 'domain-forwarding';
        }
        if (haystack.indexOf('dns records') !== -1 || haystack.indexOf('records home') !== -1 || haystack.indexOf('back to dns workspace') !== -1) {
            return 'record-a';
        }
        return '';
    }

    function dmDnsSetLinkActive(link, selectedKey, selectedGroup) {
        var key = dmDnsInferToolKey(link);
        var isActive = key && key === selectedKey;
        if (link.classList.contains('dm-dns-flow-tab')) {
            var text = (link.textContent || '').toLowerCase();
            isActive = (selectedGroup === 'records' && text.indexOf('records') !== -1)
                || (selectedGroup === 'security' && text.indexOf('security') !== -1)
                || (selectedGroup === 'forwarding' && text.indexOf('forwarding') !== -1);
        }
        if (isActive) {
            link.classList.add('dm-active');
            link.setAttribute('aria-current', 'page');
        } else {
            link.classList.remove('dm-active');
            link.removeAttribute('aria-current');
        }
    }

    function dmDnsUpdateHeaderText(config) {
        var path = document.querySelector('.dm-dns-path-strip');
        if (path) {
            path.innerHTML = '<strong>DNS Management</strong><span>›</span><strong>' + escapeHtml(config.flow) + '</strong><span>›</span><strong>' + escapeHtml(config.title) + '</strong>';
        }
        var currentCardTitle = document.querySelector('.dm-dns-current-card h4');
        if (currentCardTitle) {
            currentCardTitle.textContent = config.title;
        }
        var currentMeta = document.querySelectorAll('.dm-dns-current-meta > div');
        if (currentMeta[0]) {
            var strong0 = currentMeta[0].querySelector('strong');
            if (strong0) {
                strong0.textContent = config.meta;
            }
        }
        if (currentMeta[1]) {
            var strong1 = currentMeta[1].querySelector('strong');
            if (strong1) {
                strong1.textContent = config.flow;
            }
        }
    }

    function dmDnsPulseTarget(config) {
        var target = document.querySelector(config.target || '.dm-dns-tool-panel') || document.querySelector('.dm-dns-workspace');
        if (!target) {
            return;
        }
        target.classList.remove('dm-dns-selection-pulse');
        void target.offsetWidth;
        target.classList.add('dm-dns-selection-pulse');
        try {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (err) {
            target.scrollIntoView(true);
        }
    }

    function dmDnsSelectTool(key, shouldScroll) {
        var config = dmDnsToolConfigs[key];
        if (!config) {
            return;
        }
        var links = document.querySelectorAll(dmDnsInternalLinkSelector);
        for (var i = 0; i < links.length; i++) {
            dmDnsSetLinkActive(links[i], key, config.group);
        }
        var families = document.querySelectorAll('.dm-dns-record-family');
        for (var f = 0; f < families.length; f++) {
            families[f].classList.toggle('dm-active', !!families[f].querySelector('.dm-dns-record-family-chip.dm-active'));
        }
        var toolGroups = document.querySelectorAll('.dm-dns-tool-group');
        for (var g = 0; g < toolGroups.length; g++) {
            toolGroups[g].classList.toggle('dm-active-group', !!toolGroups[g].querySelector('.dm-dns-tool-link.dm-active'));
        }
        dmDnsUpdateHeaderText(config);
        if (shouldScroll) {
            dmDnsPulseTarget(config);
        }
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '#dm-dns-' + key);
        }
    }

    function dmDnsInsertInternalNote() {
        var shell = document.getElementById('dm-dns-unified-shell');
        var workspaceBody = shell ? shell.querySelector('.dm-dns-body') : null;
        if (!workspaceBody || workspaceBody.querySelector('.dm-dns-internal-selection-note')) {
            return;
        }
        var note = document.createElement('div');
        note.className = 'dm-dns-internal-selection-note';
        note.innerHTML = '<strong>DNS Workspace navigation:</strong> record-type buttons load the matching preserved WHMCS/ResellerClub record tool below. Other workspace cards keep the DNS tools grouped inside this converted layout.';
        workspaceBody.insertBefore(note, workspaceBody.firstChild);
    }

    function bindInternalDnsNavigation() {
        if (document.body.getAttribute('data-dm-dns-internal-nav-bound') === '1') {
            return;
        }
        document.body.setAttribute('data-dm-dns-internal-nav-bound', '1');
        dmDnsInsertInternalNote();
        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }
            var link = event.target && event.target.closest ? event.target.closest(dmDnsInternalLinkSelector) : null;
            if (!link || !document.getElementById('dm-dns-unified-shell')) {
                return;
            }
            var key = dmDnsInferToolKey(link);
            if (!key) {
                return;
            }

            // Patch 1009: record-type buttons must use the real WHMCS/ResellerClub
            // route so the preserved module output below changes from A records to
            // the selected type (AAAA/CNAME/MX/NS/TXT/SRV/SOA).  The previous
            // visual-only interception made the upper buttons change while the
            // lower tool stayed on A records.
            var href = String(link.getAttribute('href') || '');
            var isRecordRoute = /^record-/.test(key) && /[?&]nsrecordtype=/i.test(href);
            if (isRecordRoute) {
                return;
            }

            event.preventDefault();
            dmDnsSelectTool(key, true);
        }, true);
    }

    function boot() {
        if (document.getElementById('dm-dns-unified-shell')) {
            return;
        }
        document.body.classList.add('dm-dns-unified');
        var content = findMainContent();
        if (!content) {
            return;
        }
        var shell = buildShell();
        content.insertBefore(shell, content.firstChild);
        normalizePageTitle(content);
        wrapExistingOutput(content);
        bindInternalDnsNavigation();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
HTML;
});
