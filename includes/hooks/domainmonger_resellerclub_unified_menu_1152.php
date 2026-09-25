<?php
/**
 * DomainMonger patch 1346: cached conditional grandfathered Email Forwarding menu.
 *
 * Retains the confirmed patch 1152/1188 menu and its DNS destinations.
 * Patch 1346 caches the verified Email Forwarding capability per domain so
 * the conditional button appears immediately on repeat visits without
 * repeating the 1.5-second full-page probe on every load.
 *
 * Adds one shared domain header, domain switcher, compact main menu, two dropdowns,
 * and a dedicated DNS submenu across the converted ResellerClub/LogicBoxes pages.
 * Existing registrar forms, module actions, templates, and backend behavior remain intact.
 */

/* Patch 1231: confirmed native DNS route is now the default DNS Records route. */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm_rc_menu_1152_client_id')) {
    function dm_rc_menu_1152_client_id(): int
    {
        if (!empty($_SESSION['uid'])) {
            return (int) $_SESSION['uid'];
        }
        if (!empty($_SESSION['clientareauserid'])) {
            return (int) $_SESSION['clientareauserid'];
        }
        return 0;
    }
}

if (!function_exists('dm_rc_menu_1152_supported_registrar')) {
    function dm_rc_menu_1152_supported_registrar(string $registrar): bool
    {
        return (bool) preg_match('/(?:resellerclub|netearth|logicboxes)/i', $registrar);
    }
}

if (!function_exists('dm_rc_menu_1152_page_context')) {
    function dm_rc_menu_1152_page_context(): array
    {
        $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));
        $rawNativeDns = $script === 'clientarea.php'
            && $action === 'domaindns'
            && (isset($_REQUEST['dmplainnative']) || isset($_REQUEST['dmnativeold']) || isset($_REQUEST['dmfeednative']));
        $allowed = false;

        if ($script === 'clientarea.php' && in_array($action, [
            'domaindetails',
            'domaincontacts',
            'domaingetepp',
            'domainemailforwarding',
        ], true)) {
            $allowed = true;
        }

        if ($script === 'clientarea.php' && $action === 'domaindns' && !$rawNativeDns) {
            $allowed = true;
        }

        if ($script === 'domainmanagement.php' && in_array($action, [
            'childns',
            'dnssec',
            'managednssec',
        ], true)) {
            $allowed = true;
        }

        if (in_array($script, [
            'dnsmanagement.php',
            'domainforwarding.php',
            'emailmanagement.php',
        ], true)) {
            $allowed = true;
        }

        if (!$allowed) {
            return [];
        }

        $active = 'overview';

        if ($script === 'clientarea.php') {
            if ($action === 'domaindns' && !$rawNativeDns) {
                $active = 'dnsrecords';
            } elseif ($action === 'domaincontacts') {
                $active = 'whois';
            } elseif ($action === 'domaingetepp') {
                $active = 'getepp';
            } elseif ($action === 'domainemailforwarding') {
                $active = 'emailforwarding';
            } elseif ($action === 'domaindetails') {
                $section = strtolower((string) ($_REQUEST['dmsection'] ?? $_REQUEST['dmnav'] ?? 'overview'));
                $section = preg_replace('/[^a-z0-9_-]/', '', $section);
                $map = [
                    'overview' => 'overview',
                    'autorenew' => 'autorenew',
                    'nameservers' => 'nameservers',
                    'reglock' => 'reglock',
                    'addons' => 'idprotection',
                    'idprotection' => 'idprotection',
                ];
                $active = $map[$section] ?? 'overview';
            }
        } elseif ($script === 'domainmanagement.php') {
            $active = $action === 'childns' ? 'privatens' : 'dnssec';
        } elseif ($script === 'dnsmanagement.php') {
            $recordType = strtoupper((string) ($_REQUEST['nsrecordtype'] ?? ''));
            $section = strtolower((string) ($_REQUEST['dmsection'] ?? ''));
            $active = ($recordType === 'DNSSEC' || $section === 'dnssec') ? 'dnssec' : 'dnsrecords';
        } elseif ($script === 'domainforwarding.php') {
            $active = 'domainforwarding';
        } elseif ($script === 'emailmanagement.php') {
            $active = 'emailforwarding';
        }

        return [
            'script' => $script,
            'action' => $action,
            'active' => $active,
        ];
    }
}

if (!function_exists('dm_rc_menu_1152_route')) {
    function dm_rc_menu_1152_route(string $key, int $domainId, string $domainName): string
    {
        $id = max(0, $domainId);
        $domain = rawurlencode($domainName);
        $detailBase = 'clientarea.php?action=domaindetails&id=' . $id . '&dmdesign=1&dmconverted=1&dmsection=';

        $routes = [
            'overview' => $detailBase . 'overview#tabOverview',
            'autorenew' => $detailBase . 'autorenew#tabAutorenew',
            'nameservers' => $detailBase . 'nameservers#tabNameservers',
            'reglock' => $detailBase . 'reglock#tabReglock',
            // ID Protection continues to use the registrar-backed WHMCS Addons tab.
            'idprotection' => $detailBase . 'addons#tabAddons',
            'whois' => 'clientarea.php?action=domaincontacts&domainid=' . $id,
            'privatens' => 'domainmanagement.php?action=childns&id=' . $id . '&domainid=' . $id . '&domain=' . $domain,
            'dnsrecords' => 'clientarea.php?action=domaindns&domainid=' . $id,
            'dnssec' => 'dnsmanagement.php?action=dnsseczone&domain=' . $domain . '&domainid=' . $id . '&id=' . $id . '&nsrecordtype=DNSSEC&dmdesign=1&dmconverted=1',
            'domainforwarding' => 'domainforwarding.php?action=managedomfwd&domainid=' . $id . '&id=' . $id . '&domain=' . $domain,
            'emailforwarding' => 'clientarea.php?action=domainemailforwarding&domainid=' . $id . '&id=' . $id . '&domain=' . $domain,
            'getepp' => 'clientarea.php?action=domaingetepp&domainid=' . $id . '&dmsection=epp&dmdesign=1&dmconverted=1',
        ];

        return $routes[$key] ?? $routes['overview'];
    }
}

add_hook('ClientAreaFooterOutput', 2005, function ($vars) {
    $context = dm_rc_menu_1152_page_context();
    if (!$context) {
        return '';
    }

    $clientId = dm_rc_menu_1152_client_id();
    if ($clientId <= 0) {
        return '';
    }

    $domainId = 0;
    foreach (['domainid', 'id'] as $key) {
        if (!empty($_REQUEST[$key])) {
            $domainId = (int) $_REQUEST[$key];
            if ($domainId > 0) {
                break;
            }
        }
    }

    $domainName = trim((string) ($_REQUEST['domain'] ?? ''));
    if ($domainName === '' && !empty($vars['domain'])) {
        $domainName = trim((string) $vars['domain']);
    }

    try {
        $currentQuery = Capsule::table('tbldomains')
            ->select('id', 'domain', 'registrar', 'status')
            ->where('userid', $clientId);

        if ($domainId > 0) {
            $currentQuery->where('id', $domainId);
        } elseif ($domainName !== '') {
            $currentQuery->where('domain', $domainName);
        } else {
            return '';
        }

        $current = $currentQuery->first();
        if (!$current || !dm_rc_menu_1152_supported_registrar((string) $current->registrar)) {
            return '';
        }

        $domainId = (int) $current->id;
        $domainName = (string) $current->domain;

        $rows = Capsule::table('tbldomains')
            ->select('id', 'domain', 'registrar', 'status')
            ->where('userid', $clientId)
            ->orderBy('domain', 'asc')
            ->get();
    } catch (\Throwable $e) {
        return '';
    }

    $domains = [];
    foreach ($rows as $row) {
        $registrar = (string) ($row->registrar ?? '');
        $status = strtolower((string) ($row->status ?? ''));
        if (!dm_rc_menu_1152_supported_registrar($registrar)) {
            continue;
        }
        if (in_array($status, ['cancelled', 'transferred', 'fraud'], true) && (int) $row->id !== $domainId) {
            continue;
        }
        $domains[] = [
            'id' => (int) $row->id,
            'domain' => (string) $row->domain,
        ];
    }

    if (!$domains) {
        $domains[] = ['id' => $domainId, 'domain' => $domainName];
    }

    $active = (string) $context['active'];
    $domainGroup = in_array($active, ['autorenew', 'nameservers', 'whois', 'privatens'], true);
    $securityGroup = in_array($active, ['reglock', 'idprotection', 'getepp'], true);
    $dnsGroup = in_array($active, ['dnsrecords', 'dnssec', 'domainforwarding', 'emailforwarding'], true);

    // Patch 1346: a short-lived positive cookie lets PHP render the confirmed
    // Email Forwarding button without waiting for JavaScript on repeat visits.
    // The cookie is only a presentation cache; the destination remains the
    // normal authenticated WHMCS route and JavaScript still refreshes the
    // authoritative result when the cache expires.
    $emailForwardingCookieName = 'dm_rc_emailfwd_' . $clientId . '_' . $domainId;
    $emailForwardingCachedAvailable = isset($_COOKIE[$emailForwardingCookieName])
        && (string) $_COOKIE[$emailForwardingCookieName] === '1';

    $e = static function ($value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    $link = static function (string $key, string $label, string $href, bool $isActive, string $extraClass = '', string $extraAttributes = '') use ($e): string {
        $classes = trim('dm-rc-nav-link ' . $extraClass . ($isActive ? ' dm-active' : ''));
        $current = $isActive ? ' aria-current="page"' : '';
        return '<a class="' . $e($classes) . '" data-dm-route-lock="1" data-dm-nav-key="' . $e($key) . '" href="' . $e($href) . '"' . $current . $extraAttributes . '>' . $e($label) . '</a>';
    };

    $routes = [];
    foreach ([
        'overview', 'autorenew', 'nameservers', 'whois', 'privatens',
        'reglock', 'idprotection', 'getepp', 'dnsrecords', 'dnssec',
        'domainforwarding', 'emailforwarding',
    ] as $key) {
        $routes[$key] = dm_rc_menu_1152_route($key, $domainId, $domainName);
    }
    $switchOptions = '';
    foreach ($domains as $domain) {
        $url = dm_rc_menu_1152_route($active, (int) $domain['id'], (string) $domain['domain']);
        $selected = ((int) $domain['id'] === $domainId) ? ' selected' : '';
        $switchOptions .= '<option value="' . $e($url) . '"' . $selected . '>' . $e($domain['domain']) . '</option>';
    }
    $switchDisabled = count($domains) < 2 ? ' disabled aria-disabled="true"' : '';

    $overviewLink = $link('overview', 'Overview', $routes['overview'], $active === 'overview', 'dm-rc-main-link');
    $dnsLink = $link('dnsrecords', 'DNS Management', $routes['dnsrecords'], $dnsGroup, 'dm-rc-main-link');

    $domainDropdownLinks =
        $link('autorenew', 'Auto Renew', $routes['autorenew'], $active === 'autorenew', 'dm-rc-dropdown-link') .
        $link('nameservers', 'Nameservers', $routes['nameservers'], $active === 'nameservers', 'dm-rc-dropdown-link') .
        $link('whois', 'WHOIS Contact Info', $routes['whois'], $active === 'whois', 'dm-rc-dropdown-link') .
        $link('privatens', 'Private Nameservers', $routes['privatens'], $active === 'privatens', 'dm-rc-dropdown-link');

    $securityDropdownLinks =
        $link('reglock', 'Registrar Lock', $routes['reglock'], $active === 'reglock', 'dm-rc-dropdown-link') .
        $link('idprotection', 'ID Protection', $routes['idprotection'], $active === 'idprotection', 'dm-rc-dropdown-link') .
        $link('getepp', 'Get EPP Code', $routes['getepp'], $active === 'getepp', 'dm-rc-dropdown-link');

    $dnsSubmenu = '';
    if ($dnsGroup) {
        $emailForwardingAttributes = ' data-dm-email-forwarding-conditional="1"';
        if ($emailForwardingCachedAvailable) {
            $emailForwardingAttributes .= ' data-dm-email-forwarding-cached="1" aria-hidden="false"';
        } else {
            $emailForwardingAttributes .= ' hidden aria-hidden="true"';
        }

        $dnsSubmenu = '<nav class="dm-rc-dns-submenu" aria-label="DNS Management tools">'
            . $link('dnsrecords', 'DNS Records', $routes['dnsrecords'], $active === 'dnsrecords', 'dm-rc-dns-link')
            . $link('dnssec', 'DNSSEC', $routes['dnssec'], $active === 'dnssec', 'dm-rc-dns-link')
            . $link('domainforwarding', 'Domain Forwarding', $routes['domainforwarding'], $active === 'domainforwarding', 'dm-rc-dns-link')
            . $link('emailforwarding', 'Email Forwarding', $routes['emailforwarding'], $active === 'emailforwarding', 'dm-rc-dns-link', $emailForwardingAttributes)
            . '</nav>';
    }

    // Patch 1162: these pages either had no title bar, an older white title,
    // or a duplicate internal card title. Render one shared navy page header
    // directly below the unified menu so its position is consistent.
    $sharedPageHeaders = [
        'overview' => ['Overview', 'Domain details and management'],
        'autorenew' => ['Auto Renew', 'Renewal settings'],
        'nameservers' => ['Nameservers', 'DNS delegation'],
        'reglock' => ['Registrar Lock', 'Domain security'],
        'idprotection' => ['ID Protection', 'WHOIS privacy'],
        'privatens' => ['Private Nameservers', 'Custom DNS hosts'],
        'dnssec' => ['DNSSEC', 'Manage DNSSEC records'],
        'emailforwarding' => ['Email Forwarding', 'Manage email forwards'],
        'getepp' => ['Get EPP Code', 'Domain transfer authorization'],
    ];
    $pageHeaderHtml = '';
    if (isset($sharedPageHeaders[$active])) {
        $pageHeader = $sharedPageHeaders[$active];
        $pageHeaderHtml = '<div class="dm-rc-page-header-1162" data-dm-page-key="' . $e($active) . '">'
            . '<div><strong>' . $e($pageHeader[0]) . '</strong><span>' . $e($pageHeader[1]) . '</span></div>'
            . '</div>';
    }

    $domainButtonClass = $domainGroup ? ' dm-active' : '';
    $securityButtonClass = $securityGroup ? ' dm-active' : '';
    $domainExpanded = $domainGroup ? 'true' : 'false';
    $securityExpanded = $securityGroup ? 'true' : 'false';

    $domainJson = json_encode([
        'active' => $active,
        'domainId' => $domainId,
        'domainName' => $domainName,
        'clientId' => $clientId,
        'emailForwardingProbeUrl' => $routes['emailforwarding'] . '&dmemailprobe=1346',
        'emailForwardingCacheKey' => 'dm:rc:emailfwd:v1346:' . $clientId . ':' . $domainId,
        'emailForwardingCookieName' => $emailForwardingCookieName,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<style id="dm-rc-unified-menu-1152-css">
body.dm-rc-unified-menu-1152 .dm-domain-section-menu,
body.dm-rc-unified-menu-1152 .dm-private-ns-menu,
body.dm-rc-unified-menu-1152 .dm-epp-section-menu,
body.dm-rc-unified-menu-1152 .dm-pns-section-menu {
    display: none !important;
}
#dm-rc-unified-nav-1152 {
    --dm-rc-navy: #163a5f;
    --dm-rc-navy-hover: #214e7a;
    --dm-rc-orange: #f58220;
    --dm-rc-orange-soft: #d8741f;
    --dm-rc-text: #273b50;
    --dm-rc-border: #dbe4ed;
    width: 100% !important;
    margin: 0 0 18px !important;
    font-family: inherit !important;
    position: relative !important;
    z-index: 40 !important;
}
#dm-rc-unified-nav-1152[hidden] { display: none !important; }
#dm-rc-unified-nav-1152 [data-dm-email-forwarding-conditional="1"][hidden] { display: none !important; }
#dm-rc-unified-nav-1152 .dm-rc-domain-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 18px !important;
    padding: 14px 18px !important;
    background: var(--dm-rc-navy) !important;
    color: #fff !important;
    border: 1px solid var(--dm-rc-navy) !important;
}
#dm-rc-unified-nav-1152 .dm-rc-domain-title {
    min-width: 0 !important;
}
#dm-rc-unified-nav-1152 .dm-rc-domain-title span {
    display: block !important;
    margin: 0 0 2px !important;
    color: rgba(255,255,255,.78) !important;
    font-size: 11px !important;
    line-height: 1.2 !important;
    font-weight: 800 !important;
    letter-spacing: .08em !important;
    text-transform: uppercase !important;
}
#dm-rc-unified-nav-1152 .dm-rc-domain-title strong {
    display: block !important;
    color: #fff !important;
    font-size: 21px !important;
    line-height: 1.25 !important;
    font-weight: 800 !important;
    overflow-wrap: anywhere !important;
}
#dm-rc-unified-nav-1152 .dm-rc-switch-wrap {
    display: flex !important;
    align-items: center !important;
    gap: 9px !important;
    flex: 0 1 390px !important;
    justify-content: flex-end !important;
}
#dm-rc-unified-nav-1152 .dm-rc-switch-wrap label {
    margin: 0 !important;
    color: #fff !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    white-space: nowrap !important;
}
#dm-rc-unified-nav-1152 .dm-rc-domain-switch {
    width: min(100%, 265px) !important;
    min-width: 190px !important;
    height: 38px !important;
    margin: 0 !important;
    padding: 7px 34px 7px 11px !important;
    border: 1px solid rgba(255,255,255,.5) !important;
    border-radius: 4px !important;
    background: #fff !important;
    color: var(--dm-rc-navy) !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    box-shadow: none !important;
}
#dm-rc-unified-nav-1152 .dm-rc-domain-switch:focus {
    outline: 0 !important;
    border-color: #fff !important;
    box-shadow: 0 0 0 3px rgba(245,130,32,.32) !important;
}
#dm-rc-unified-nav-1152 .dm-rc-domain-switch:disabled {
    opacity: .72 !important;
    cursor: default !important;
}
#dm-rc-unified-nav-1152 .dm-rc-main-menu {
    display: flex !important;
    align-items: stretch !important;
    gap: 0 !important;
    min-height: 48px !important;
    padding: 0 !important;
    background: #fff !important;
    border: 1px solid var(--dm-rc-border) !important;
    border-top: 0 !important;
    overflow: visible !important;
}
#dm-rc-unified-nav-1152 .dm-rc-main-link,
#dm-rc-unified-nav-1152 .dm-rc-dropdown-button {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 48px !important;
    margin: 0 !important;
    padding: 11px 18px !important;
    border: 0 !important;
    border-right: 1px solid var(--dm-rc-border) !important;
    border-radius: 0 !important;
    background: #fff !important;
    color: var(--dm-rc-navy) !important;
    font: inherit !important;
    font-size: 14px !important;
    line-height: 1.2 !important;
    font-weight: 800 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    cursor: pointer !important;
    box-shadow: none !important;
}
#dm-rc-unified-nav-1152 .dm-rc-main-link:hover,
#dm-rc-unified-nav-1152 .dm-rc-main-link:focus,
#dm-rc-unified-nav-1152 .dm-rc-dropdown-button:hover,
#dm-rc-unified-nav-1152 .dm-rc-dropdown-button:focus {
    background: #fff4e8 !important;
    color: var(--dm-rc-navy) !important;
    text-decoration: none !important;
    outline: 0 !important;
}
#dm-rc-unified-nav-1152 .dm-rc-main-link.dm-active,
#dm-rc-unified-nav-1152 .dm-rc-dropdown-button.dm-active,
#dm-rc-unified-nav-1152 .dm-rc-dropdown.dm-open > .dm-rc-dropdown-button {
    background: var(--dm-rc-orange) !important;
    color: #fff !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dropdown {
    position: relative !important;
    display: flex !important;
    align-items: stretch !important;
}
#dm-rc-unified-nav-1152 .dm-rc-caret {
    margin-left: 8px !important;
    font-size: 10px !important;
    line-height: 1 !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dropdown-menu {
    display: none !important;
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    z-index: 1000 !important;
    min-width: 235px !important;
    padding: 6px !important;
    background: #fff !important;
    border: 1px solid var(--dm-rc-border) !important;
    box-shadow: 0 9px 24px rgba(22,58,95,.18) !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dropdown.dm-open > .dm-rc-dropdown-menu,
#dm-rc-unified-nav-1152 .dm-rc-dropdown:hover > .dm-rc-dropdown-menu,
#dm-rc-unified-nav-1152 .dm-rc-dropdown:focus-within > .dm-rc-dropdown-menu {
    display: block !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dropdown-link {
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 10px 12px !important;
    border: 0 !important;
    border-radius: 3px !important;
    background: #fff !important;
    color: var(--dm-rc-navy) !important;
    font-size: 13px !important;
    line-height: 1.3 !important;
    font-weight: 700 !important;
    text-decoration: none !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dropdown-link:hover,
#dm-rc-unified-nav-1152 .dm-rc-dropdown-link:focus {
    background: #fff0df !important;
    color: var(--dm-rc-navy) !important;
    text-decoration: none !important;
    outline: 0 !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dropdown-link.dm-active {
    background: var(--dm-rc-orange-soft) !important;
    color: #fff !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dns-submenu {
    display: flex !important;
    align-items: stretch !important;
    gap: 0 !important;
    min-height: 42px !important;
    margin: 8px 0 0 !important;
    padding: 0 !important;
    background: #fff !important;
    border: 1px solid var(--dm-rc-border) !important;
    overflow-x: auto !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dns-link {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 42px !important;
    padding: 9px 15px !important;
    border-right: 1px solid var(--dm-rc-border) !important;
    background: #fff !important;
    color: var(--dm-rc-navy) !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dns-link:hover,
#dm-rc-unified-nav-1152 .dm-rc-dns-link:focus {
    background: #fff4e8 !important;
    color: var(--dm-rc-navy) !important;
    text-decoration: none !important;
    outline: 0 !important;
}
#dm-rc-unified-nav-1152 .dm-rc-dns-link.dm-active {
    background: var(--dm-rc-navy) !important;
    color: #fff !important;
}

/* Patch 1162: one page-name header in the same position on every converted
 * page that was missing the DNS Records-style title bar. */
#dm-rc-unified-nav-1152 .dm-rc-page-header-1162 {
    align-items: center !important;
    background: var(--dm-rc-navy) !important;
    border: 1px solid var(--dm-rc-navy) !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(17,43,77,.055) !important;
    color: #fff !important;
    display: flex !important;
    gap: 14px !important;
    justify-content: space-between !important;
    margin: 18px 0 0 !important;
    padding: 13px 16px !important;
    text-align: left !important;
}
#dm-rc-unified-nav-1152 .dm-rc-page-header-1162 strong {
    color: #fff !important;
    display: block !important;
    font-size: 16px !important;
    font-weight: 800 !important;
    line-height: 1.25 !important;
    margin: 0 !important;
}
#dm-rc-unified-nav-1152 .dm-rc-page-header-1162 span {
    color: rgba(255,255,255,.8) !important;
    display: block !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    margin-top: 2px !important;
}

/* Hide only the older page-title bars replaced by the shared header above. */
body.dm-rc-page-overview .dm-live-section-header,
body.dm-rc-page-autorenew .dm-ar-card-header,
body.dm-rc-page-autorenew #tabAutorenew .dm-live-section-header,
body.dm-rc-page-nameservers .dm-ns-card-header,
body.dm-rc-page-nameservers #tabNameservers .dm-live-section-header,
body.dm-rc-page-reglock .dm-rl-card-header,
body.dm-rc-page-reglock #tabReglock .dm-live-section-header,
body.dm-rc-page-idprotection .dm-addons-card-header,
body.dm-rc-page-idprotection #tabAddons .dm-live-section-header,
body.dm-rc-page-privatens .dm-private-ns-wrap > .dm-card > .dm-card-header,
body.dm-rc-page-dnssec .dm-rcdns-legacy-page-title,
body.dm-rc-page-emailforwarding #dm-rc-email-forwarding-page-heading-1157 {
    display: none !important;
}

/* Patch 1194: the converted domain-detail sections already contain their own
 * working white card. Remove the older tab-content card so these pages do not
 * render as a white box nested inside another white box. Keep the tab/content
 * nodes in place because WHMCS and the registrar actions still depend on them. */
body.dm-rc-page-overview.dm-domaindetails-live-1005 .tab-content,
body.dm-rc-page-autorenew.dm-domaindetails-live-1005 .tab-content,
body.dm-rc-page-nameservers.dm-domaindetails-live-1005 .tab-content,
body.dm-rc-page-reglock.dm-domaindetails-live-1005 .tab-content,
body.dm-rc-page-idprotection.dm-domaindetails-live-1005 .tab-content {
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    margin: 0 !important;
    overflow: visible !important;
    padding: 0 !important;
}
body.dm-rc-page-overview.dm-domaindetails-live-1005 .tab-content > .tab-pane,
body.dm-rc-page-autorenew.dm-domaindetails-live-1005 .tab-content > .tab-pane,
body.dm-rc-page-nameservers.dm-domaindetails-live-1005 .tab-content > .tab-pane,
body.dm-rc-page-reglock.dm-domaindetails-live-1005 .tab-content > .tab-pane,
body.dm-rc-page-idprotection.dm-domaindetails-live-1005 .tab-content > .tab-pane {
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    padding: 0 !important;
}
/* Overview does not use the later settings-card converter, so retain its
 * existing native card as the single white content card after flattening the
 * outer tab-content wrapper. */
body.dm-rc-page-overview.dm-domaindetails-live-1005 #tabOverview > .card {
    background: #fff !important;
    border: 1px solid var(--dm-rc-border) !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(17,43,77,.055) !important;
    margin: 0 !important;
    overflow: hidden !important;
}
body.dm-rc-page-overview.dm-domaindetails-live-1005 #tabOverview > .card > .card-body {
    padding: 16px !important;
}

/* Patch 1164: status is informational; the button is the action. Enabled or
 * locked may use green, disabled or unlocked is muted gray, enable uses the
 * orange primary action, and disable remains the red danger action. */
/* Patch 1177: the shared navy page bar supplies these page names.
 * The native WHMCS card title is a direct child of the card body. Hide it
 * regardless of the text an older label hook may assign, and hide only the
 * native Auto Renew explanation immediately following that title. */
body.dm-rc-page-autorenew #tabAutorenew .card-body > h3.card-title,
body.dm-rc-page-autorenew #tabAutorenew .card-body > h3.card-title + p,
body.dm-rc-page-nameservers #tabNameservers .card-body > h3.card-title {
    display: none !important;
}

body.dm-rc-page-autorenew #tabAutorenew h2.text-center,
body.dm-rc-page-autorenew #tabAutorenew h3.text-center,
body.dm-rc-page-autorenew #tabAutorenew h4.text-center,
body.dm-rc-page-reglock #tabReglock h2.text-center,
body.dm-rc-page-reglock #tabReglock h3.text-center,
body.dm-rc-page-reglock #tabReglock h4.text-center {
    color: #163a5f !important;
    font-size: 18px !important;
    font-weight: 800 !important;
    line-height: 1.4 !important;
    margin: 16px 0 18px !important;
}
body.dm-rc-page-autorenew #tabAutorenew h2.text-center .label,
body.dm-rc-page-autorenew #tabAutorenew h3.text-center .label,
body.dm-rc-page-autorenew #tabAutorenew h4.text-center .label,
body.dm-rc-page-reglock #tabReglock h2.text-center .label,
body.dm-rc-page-reglock #tabReglock h3.text-center .label,
body.dm-rc-page-reglock #tabReglock h4.text-center .label,
body.dm-rc-page-autorenew #tabAutorenew .dm-ar-status-badge,
body.dm-rc-page-reglock #tabReglock .dm-rl-status-badge {
    border: 0 !important;
    border-radius: 6px !important;
    box-shadow: none !important;
    color: #fff !important;
    display: inline-block !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    min-width: 76px !important;
    padding: 6px 9px !important;
    text-align: center !important;
    vertical-align: middle !important;
}
body.dm-rc-page-autorenew #tabAutorenew h2.text-center .label-success,
body.dm-rc-page-autorenew #tabAutorenew h3.text-center .label-success,
body.dm-rc-page-autorenew #tabAutorenew h4.text-center .label-success,
body.dm-rc-page-reglock #tabReglock h2.text-center .label-success,
body.dm-rc-page-reglock #tabReglock h3.text-center .label-success,
body.dm-rc-page-reglock #tabReglock h4.text-center .label-success,
body.dm-rc-page-autorenew #tabAutorenew .dm-ar-status-badge.dm-enabled,
body.dm-rc-page-reglock #tabReglock .dm-rl-status-badge.dm-locked {
    background: #4f8a5b !important;
}
body.dm-rc-page-autorenew #tabAutorenew h2.text-center .label-danger,
body.dm-rc-page-autorenew #tabAutorenew h3.text-center .label-danger,
body.dm-rc-page-autorenew #tabAutorenew h4.text-center .label-danger,
body.dm-rc-page-reglock #tabReglock h2.text-center .label-danger,
body.dm-rc-page-reglock #tabReglock h3.text-center .label-danger,
body.dm-rc-page-reglock #tabReglock h4.text-center .label-danger,
body.dm-rc-page-autorenew #tabAutorenew .dm-ar-status-badge.dm-disabled,
body.dm-rc-page-reglock #tabReglock .dm-rl-status-badge.dm-unlocked {
    background: #8c98a6 !important;
}
body.dm-rc-page-autorenew #tabAutorenew input.btn-success,
body.dm-rc-page-autorenew #tabAutorenew button.btn-success,
body.dm-rc-page-reglock #tabReglock input.btn-success,
body.dm-rc-page-reglock #tabReglock button.btn-success {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
}
body.dm-rc-page-autorenew #tabAutorenew input.btn-danger,
body.dm-rc-page-autorenew #tabAutorenew button.btn-danger,
body.dm-rc-page-reglock #tabReglock input.btn-danger,
body.dm-rc-page-reglock #tabReglock button.btn-danger {
    background: #b94a48 !important;
    border-color: #b94a48 !important;
    color: #fff !important;
}

/* Patch 1180: vertically center the Pay Invoice button in the native WHMCS
 * unpaid/overdue invoice alert while keeping the message on the left and the
 * action on the right. Limited to converted ResellerClub domain pages. */
body.dm-rc-unified-menu-1152 #alertUnpaidInvoice,
body.dm-rc-unified-menu-1152 #alertOverdueInvoice {
    align-items: center !important;
    display: flex !important;
    gap: 12px !important;
}
body.dm-rc-unified-menu-1152 #alertUnpaidInvoice > .float-right,
body.dm-rc-unified-menu-1152 #alertOverdueInvoice > .float-right {
    align-items: center !important;
    align-self: center !important;
    display: flex !important;
    float: none !important;
    margin-left: auto !important;
    order: 2 !important;
}
body.dm-rc-unified-menu-1152 #alertUnpaidInvoice > .float-right .btn,
body.dm-rc-unified-menu-1152 #alertOverdueInvoice > .float-right .btn {
    margin-bottom: 0 !important;
    margin-top: 0 !important;
}

/* Patch 1157: the working WHMCS Email Forwarding route still supplies the
 * legacy sidebar column. Keep the backend/page, but present it as part of the
 * converted ResellerClub workspace. */
body.dm-rc-email-forwarding-clean #main-body > .container > .row > .col-lg-4.col-xl-3,
body.dm-rc-email-forwarding-clean #main-body > .container-fluid > .row > .col-lg-4.col-xl-3,
body.dm-rc-email-forwarding-clean #main-body .sidebar,
body.dm-rc-email-forwarding-clean #main-body .secondary-sidebar,
body.dm-rc-email-forwarding-clean #main-body .panel-sidebar {
    display: none !important;
}
body.dm-rc-email-forwarding-clean #main-body > .container > .row > .primary-content,
body.dm-rc-email-forwarding-clean #main-body > .container-fluid > .row > .primary-content {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
}
@media (max-width: 767px) {
    #dm-rc-unified-nav-1152 .dm-rc-domain-header {
        align-items: stretch !important;
        flex-direction: column !important;
        gap: 12px !important;
    }
    #dm-rc-unified-nav-1152 .dm-rc-switch-wrap {
        flex: 1 1 auto !important;
        justify-content: stretch !important;
    }
    #dm-rc-unified-nav-1152 .dm-rc-domain-switch {
        width: 100% !important;
        max-width: none !important;
    }
    #dm-rc-unified-nav-1152 .dm-rc-main-menu {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
    #dm-rc-unified-nav-1152 .dm-rc-main-link,
    #dm-rc-unified-nav-1152 .dm-rc-dropdown-button {
        width: 100% !important;
        min-width: 0 !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
        border-bottom: 1px solid var(--dm-rc-border) !important;
    }
    #dm-rc-unified-nav-1152 .dm-rc-dropdown {
        min-width: 0 !important;
    }
    #dm-rc-unified-nav-1152 .dm-rc-dropdown-menu {
        position: fixed !important;
        top: auto !important;
        left: 12px !important;
        right: 12px !important;
        width: auto !important;
        min-width: 0 !important;
    }
}
@media (max-width: 480px) {
    #dm-rc-unified-nav-1152 .dm-rc-switch-wrap {
        align-items: stretch !important;
        flex-direction: column !important;
    }
    #dm-rc-unified-nav-1152 .dm-rc-main-menu {
        grid-template-columns: 1fr !important;
    }
}
</style>
<div id="dm-rc-unified-nav-1152" data-dm-route-lock="1" hidden>
    <div class="dm-rc-domain-header">
        <div class="dm-rc-domain-title">
            <span>Manage Domain</span>
            <strong>{$e($domainName)}</strong>
        </div>
        <div class="dm-rc-switch-wrap">
            <label for="dm-rc-domain-switch-1152">Switch Domain</label>
            <select id="dm-rc-domain-switch-1152" class="dm-rc-domain-switch"{$switchDisabled}>
                {$switchOptions}
            </select>
        </div>
    </div>
    <nav class="dm-rc-main-menu" aria-label="ResellerClub domain management">
        {$overviewLink}
        {$dnsLink}
        <div class="dm-rc-dropdown" data-dm-dropdown="domain-settings">
            <button class="dm-rc-dropdown-button{$domainButtonClass}" type="button" aria-haspopup="true" aria-expanded="{$domainExpanded}">
                Domain Settings <span class="dm-rc-caret" aria-hidden="true">▼</span>
            </button>
            <div class="dm-rc-dropdown-menu" role="menu">
                {$domainDropdownLinks}
            </div>
        </div>
        <div class="dm-rc-dropdown" data-dm-dropdown="security-transfer">
            <button class="dm-rc-dropdown-button{$securityButtonClass}" type="button" aria-haspopup="true" aria-expanded="{$securityExpanded}">
                Security &amp; Transfer <span class="dm-rc-caret" aria-hidden="true">▼</span>
            </button>
            <div class="dm-rc-dropdown-menu" role="menu">
                {$securityDropdownLinks}
            </div>
        </div>
    </nav>
    {$dnsSubmenu}
    {$pageHeaderHtml}
</div>
<script id="dm-rc-unified-menu-1152-js">
(function () {
    var config = {$domainJson};
    var nav = document.getElementById('dm-rc-unified-nav-1152');
    if (!nav) { return; }

    if (config && config.active) {
        document.body.classList.add('dm-rc-page-' + String(config.active).replace(/[^a-z0-9_-]/g, ''));
    }

    function oldMenus() {
        return document.querySelectorAll('.dm-domain-section-menu, .dm-private-ns-menu, .dm-epp-section-menu, .dm-pns-section-menu');
    }

    function hideLegacyNavigation() {
        var menus = oldMenus();
        for (var i = 0; i < menus.length; i++) {
            if (!menus[i].closest('#dm-rc-unified-nav-1152')) {
                menus[i].style.setProperty('display', 'none', 'important');
                menus[i].setAttribute('aria-hidden', 'true');
            }
        }

        var links = document.querySelectorAll('a');
        for (var j = 0; j < links.length; j++) {
            if (links[j].closest('#dm-rc-unified-nav-1152')) { continue; }
            var text = String(links[j].textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
            if (text === 'addons' || text === 'dns host record management') {
                var navigationArea = links[j].closest('nav, .sidebar, .secondary-sidebar, .panel-sidebar, .list-group, [class*="menu"]');
                var holder = navigationArea ? links[j].closest('li, .list-group-item, .menu-item') : null;
                if (holder) {
                    holder.style.setProperty('display', 'none', 'important');
                    holder.setAttribute('aria-hidden', 'true');
                }
            }
        }
    }

    function preserveDomainDetailFormRoutes() {
        if (!config || !config.domainId) { return; }
        var definitions = [
            { tab: 'tabAutorenew', section: 'autorenew', subs: ['autorenew'] },
            { tab: 'tabNameservers', section: 'nameservers', subs: ['savens'] },
            { tab: 'tabReglock', section: 'reglock', subs: ['savereglock'] }
        ];
        for (var i = 0; i < definitions.length; i++) {
            var definition = definitions[i];
            var tab = document.getElementById(definition.tab);
            if (!tab) { continue; }
            var forms = tab.querySelectorAll('form');
            for (var j = 0; j < forms.length; j++) {
                var form = forms[j];
                var subInput = form.querySelector('input[name="sub"]');
                var sub = subInput ? String(subInput.value || '').toLowerCase() : '';
                if (sub && definition.subs.indexOf(sub) === -1) { continue; }
                var action = String(form.getAttribute('action') || '').toLowerCase();
                if (action && action.indexOf('action=domaindetails') === -1) { continue; }
                form.setAttribute(
                    'action',
                    'clientarea.php?action=domaindetails&id=' + encodeURIComponent(config.domainId)
                        + '&dmsection=' + encodeURIComponent(definition.section)
                        + '&dmdesign=1&dmconverted=1#' + definition.tab
                );
                form.setAttribute('data-dm-rc-preserve-section', definition.section);
            }
        }
    }

    function removeSupersededSettingsHeaders() {
        var tabIds = ['tabAutorenew', 'tabNameservers', 'tabReglock', 'tabAddons'];
        for (var i = 0; i < tabIds.length; i++) {
            var tab = document.getElementById(tabIds[i]);
            if (!tab) { continue; }
            var headers = tab.querySelectorAll('.dm-live-section-header');
            for (var j = 0; j < headers.length; j++) {
                headers[j].remove();
            }
        }
    }

    function cleanNativeSettingsIntros() {
        if (!config || (config.active !== 'autorenew' && config.active !== 'nameservers')) {
            return;
        }

        var tabId = config.active === 'autorenew' ? 'tabAutorenew' : 'tabNameservers';
        var tab = document.getElementById(tabId);
        if (!tab) { return; }

        var headings = tab.querySelectorAll('.card-body > h3.card-title');
        for (var i = headings.length - 1; i >= 0; i--) {
            var heading = headings[i];
            if (config.active === 'autorenew') {
                var description = heading.nextElementSibling;
                if (description && String(description.tagName || '').toLowerCase() === 'p') {
                    description.remove();
                }
            }
            heading.remove();
        }
    }

    function cleanIdProtectionServices() {
        if (!config || config.active !== 'idprotection') { return; }
        var tab = document.getElementById('tabAddons');
        if (!tab) { return; }

        // Patch 1171: ID Protection is the only remaining service on this
        // converted page, so the native generic Addons title and introductory
        // sentence are redundant. Remove those exact elements and their nearby
        // separator without touching the ID Protection service row or action.
        var headings = tab.querySelectorAll('h1, h2, h3, h4, h5, h6, .card-title');
        for (var h = headings.length - 1; h >= 0; h--) {
            var headingText = String(headings[h].textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
            if (headingText === 'addons') {
                headings[h].remove();
            }
        }

        var paragraphs = tab.querySelectorAll('p');
        for (var pIndex = paragraphs.length - 1; pIndex >= 0; pIndex--) {
            var paragraphText = String(paragraphs[pIndex].textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
            if (paragraphText.indexOf('the following addons are available for your domain') === 0) {
                var paragraph = paragraphs[pIndex];
                var next = paragraph.nextElementSibling;
                var previous = paragraph.previousElementSibling;
                paragraph.remove();
                if (next && next.tagName === 'HR') {
                    next.remove();
                } else if (previous && previous.tagName === 'HR') {
                    previous.remove();
                }
            }
        }

        var fields = tab.querySelectorAll('input[name="buy"], input[name="disable"]');
        for (var i = fields.length - 1; i >= 0; i--) {
            var key = String(fields[i].value || '').toLowerCase();
            if (key !== 'dnsmanagement' && key !== 'emailfwd') { continue; }
            var form = fields[i].closest ? fields[i].closest('form') : null;
            var holder = form && form.closest ? form.closest('.row') : null;
            if (!holder && form) { holder = form.parentElement; }
            if (!holder || holder === tab) { continue; }
            var previous = holder.previousElementSibling;
            var next = holder.nextElementSibling;
            if (previous && previous.tagName === 'HR') {
                previous.remove();
            } else if (next && next.tagName === 'HR') {
                next.remove();
            }
            holder.remove();
        }
    }

    function cleanEmailForwardingPage() {
        if (!config || config.active !== 'emailforwarding') {
            return;
        }

        document.body.classList.add('dm-rc-email-forwarding-clean');

        // The route is functional, but WHMCS still renders its old sidebar
        // column. Hide only that top-level sidebar column and expand the real
        // Email Forwarding content to the full converted-page width.
        var sidebarColumns = document.querySelectorAll(
            '#main-body > .container > .row > .col-lg-4.col-xl-3, '
            + '#main-body > .container-fluid > .row > .col-lg-4.col-xl-3'
        );
        for (var i = 0; i < sidebarColumns.length; i++) {
            sidebarColumns[i].style.setProperty('display', 'none', 'important');
            sidebarColumns[i].setAttribute('aria-hidden', 'true');
        }

        var primary = document.querySelector('#main-body .primary-content');
        if (!primary) {
            return;
        }
        primary.style.setProperty('flex', '0 0 100%', 'important');
        primary.style.setProperty('max-width', '100%', 'important');
        primary.style.setProperty('width', '100%', 'important');

        // Patch 1194: the shared navy header is the only page title. Remove
        // the native Email Forwarding heading inside the working content area,
        // but never touch the unified navigation/header itself.
        var pageTitleCandidates = primary.querySelectorAll('h1, h2, h3, h4, h5, h6, .card-title, .panel-title');
        for (var titleIndex = pageTitleCandidates.length - 1; titleIndex >= 0; titleIndex--) {
            var pageTitle = pageTitleCandidates[titleIndex];
            if (pageTitle.closest && pageTitle.closest('#dm-rc-unified-nav-1152')) { continue; }
            var pageTitleText = String(pageTitle.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
            if (pageTitleText === 'email forwarding') {
                var pageTitleHolder = pageTitle.parentElement;
                pageTitle.remove();
                if (pageTitleHolder && pageTitleHolder !== primary
                    && String(pageTitleHolder.textContent || '').replace(/\s+/g, ' ').trim() === ''
                    && pageTitleHolder.children.length === 0) {
                    pageTitleHolder.remove();
                }
            }
        }

        // The domain is already shown in the shared Manage Domain header.
        // Remove only heading-like duplicates whose entire text is the domain.
        var domainText = String(config.domainName || '').replace(/\s+/g, ' ').trim().toLowerCase();
        if (domainText) {
            var duplicateCandidates = primary.querySelectorAll('h1, h2, h3, h4, h5, .card-title, .panel-title');
            for (var j = 0; j < duplicateCandidates.length; j++) {
                var candidateText = String(duplicateCandidates[j].textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
                if (candidateText === domainText) {
                    duplicateCandidates[j].style.setProperty('display', 'none', 'important');
                    duplicateCandidates[j].setAttribute('aria-hidden', 'true');
                }
            }
        }

    }

    var dmEmailProbeStarted1188 = false;
    var dmEmailCachePositiveTtl1346 = 12 * 60 * 60 * 1000;
    var dmEmailCacheNegativeTtl1346 = 30 * 60 * 1000;

    function dmSetEmailForwardingAvailable1188(available) {
        var links = nav.querySelectorAll('[data-dm-email-forwarding-conditional="1"]');
        for (var i = 0; i < links.length; i++) {
            links[i].hidden = !available;
            links[i].setAttribute('aria-hidden', available ? 'false' : 'true');
        }
    }

    function dmHasSavedEmailForwarders1188(root) {
        if (!root || !root.querySelector) { return false; }

        // The WHMCS addon flag and Addons controls are identical for active and
        // discontinued domains. Grandfathered domains are distinguished by at
        // least one saved indexed forwarding row. Unsupported domains expose
        // only the blank *new inputs.
        return !!root.querySelector(
            'input[name^="emailforwarderprefix["], '
            + 'input[name^="emailforwarderforwardto["]'
        );
    }

    function dmEmailCacheKey1346() {
        if (config && config.emailForwardingCacheKey) {
            return String(config.emailForwardingCacheKey);
        }
        return 'dm:rc:emailfwd:v1346:'
            + String(config && config.clientId ? config.clientId : '0') + ':'
            + String(config && config.domainId ? config.domainId : '0');
    }

    function dmReadEmailForwardingCache1346() {
        if (!window.localStorage) { return null; }
        try {
            var raw = window.localStorage.getItem(dmEmailCacheKey1346());
            if (!raw) { return null; }
            var item = JSON.parse(raw);
            if (!item || (item.available !== true && item.available !== false)) { return null; }
            var checkedAt = Number(item.checkedAt || 0);
            if (!checkedAt) { return null; }
            var ttl = item.available ? dmEmailCachePositiveTtl1346 : dmEmailCacheNegativeTtl1346;
            return {
                available: item.available === true,
                checkedAt: checkedAt,
                fresh: (Date.now() - checkedAt) < ttl
            };
        } catch (error) {
            return null;
        }
    }

    function dmWriteEmailForwardingCookie1346(available) {
        if (!config || !config.emailForwardingCookieName) { return; }
        var name = String(config.emailForwardingCookieName).replace(/[^a-zA-Z0-9_-]/g, '');
        if (!name) { return; }
        if (available) {
            document.cookie = name + '=1; Max-Age=43200; Path=/manage; SameSite=Lax';
        } else {
            document.cookie = name + '=; Max-Age=0; Path=/manage; SameSite=Lax';
        }
    }

    function dmWriteEmailForwardingCache1346(available) {
        if (window.localStorage) {
            try {
                window.localStorage.setItem(dmEmailCacheKey1346(), JSON.stringify({
                    available: available === true,
                    checkedAt: Date.now()
                }));
            } catch (error) {
                // Storage can be unavailable in strict privacy modes; the normal
                // probe still works and the button simply will not be cached.
            }
        }
        dmWriteEmailForwardingCookie1346(available === true);
    }

    function dmForceEmailForwardingRefresh1346() {
        try {
            return new URL(window.location.href).searchParams.get('dmemailrefresh') === '1';
        } catch (error) {
            return /(?:\?|&)dmemailrefresh=1(?:&|$)/.test(String(window.location.search || ''));
        }
    }

    function dmCheckEmailForwardingAvailability1188() {
        if (dmEmailProbeStarted1188) { return; }
        var conditionalLink = nav.querySelector('[data-dm-email-forwarding-conditional="1"]');
        if (!conditionalLink) { return; }
        dmEmailProbeStarted1188 = true;

        if (config && config.active === 'emailforwarding') {
            var pageAvailable = dmHasSavedEmailForwarders1188(document);
            dmSetEmailForwardingAvailable1188(pageAvailable);
            dmWriteEmailForwardingCache1346(pageAvailable);
            return;
        }

        var forceRefresh = dmForceEmailForwardingRefresh1346();
        var cached = dmReadEmailForwardingCache1346();
        if (cached) {
            // Apply the verified cached result synchronously. This is what makes
            // the button immediate on repeat DNS-page visits.
            dmSetEmailForwardingAvailable1188(cached.available);
            dmWriteEmailForwardingCookie1346(cached.available);
            if (cached.fresh && !forceRefresh) {
                return;
            }
        }

        var probeUrl = config && config.emailForwardingProbeUrl
            ? String(config.emailForwardingProbeUrl)
            : String(conditionalLink.getAttribute('href') || '');
        if (!probeUrl || !window.fetch || !window.DOMParser) {
            if (!cached) {
                dmSetEmailForwardingAvailable1188(false);
            }
            return;
        }

        probeUrl += (probeUrl.indexOf('?') === -1 ? '?' : '&') + '_dmts=' + String(Date.now());
        window.fetch(probeUrl, {
            credentials: 'same-origin',
            cache: 'no-store',
            redirect: 'follow'
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Email Forwarding probe returned ' + response.status);
            }
            return response.text();
        }).then(function (html) {
            var probeDocument = new window.DOMParser().parseFromString(html, 'text/html');
            var available = dmHasSavedEmailForwarders1188(probeDocument);
            dmSetEmailForwardingAvailable1188(available);
            dmWriteEmailForwardingCache1346(available);
        }).catch(function () {
            // Keep a previously verified cached state during a temporary probe
            // failure. With no cache, retain the safe hidden default.
            if (!cached) {
                dmSetEmailForwardingAvailable1188(false);
            }
        });
    }

    function findPlacementTarget() {
        // Patch 1166: WHOIS has an outer native WHMCS white card. Patch 1165
        // moved the page-name header visually, but the unified menu still
        // anchored to a hidden legacy menu inside that card. Anchor directly
        // above the now-external WHOIS page header instead.
        if (config && config.active === 'whois') {
            var whoisHeader = document.querySelector('.dm-whois-form-head[data-dm-whois-external-header="1"]');
            if (whoisHeader && whoisHeader.parentNode) {
                return { node: whoisHeader, mode: 'before' };
            }
        }

        var legacy = document.querySelector('.dm-domain-section-menu, .dm-private-ns-menu, .dm-epp-section-menu, .dm-pns-section-menu');
        if (legacy && legacy.parentNode) {
            return { node: legacy, mode: 'before' };
        }

        var shell = document.querySelector(
            '#dm-dns-unified-shell, .dm-dns-workspace, .dm-getepp-983-shell, '
            + '.dm-private-ns-shell, .dm-whois-workspace, .dm-domaincontacts-workspace, '
            + '.tab-content, .primary-content, .main-content, #main-body .container'
        );
        if (shell) {
            return { node: shell, mode: 'prepend' };
        }
        return null;
    }

    function placeNavigation() {
        document.body.classList.add('dm-rc-unified-menu-1152');
        hideLegacyNavigation();

        var target = findPlacementTarget();
        if (!target) { return false; }

        if (target.mode === 'before') {
            if (nav.nextElementSibling !== target.node) {
                target.node.parentNode.insertBefore(nav, target.node);
            }
        } else if (target.node.firstChild !== nav) {
            target.node.insertBefore(nav, target.node.firstChild);
        }

        nav.hidden = false;
        preserveDomainDetailFormRoutes();
        removeSupersededSettingsHeaders();
        cleanNativeSettingsIntros();
        cleanIdProtectionServices();
        cleanEmailForwardingPage();
        dmCheckEmailForwardingAvailability1188();
        return true;
    }

    function closeDropdowns(except) {
        var dropdowns = nav.querySelectorAll('.dm-rc-dropdown');
        for (var i = 0; i < dropdowns.length; i++) {
            if (except && dropdowns[i] === except) { continue; }
            dropdowns[i].classList.remove('dm-open');
            var button = dropdowns[i].querySelector('.dm-rc-dropdown-button');
            if (button) { button.setAttribute('aria-expanded', 'false'); }
        }
    }

    nav.addEventListener('click', function (event) {
        var button = event.target.closest ? event.target.closest('.dm-rc-dropdown-button') : null;
        if (!button) { return; }
        event.preventDefault();
        var dropdown = button.closest('.dm-rc-dropdown');
        if (!dropdown) { return; }
        var opening = !dropdown.classList.contains('dm-open');
        closeDropdowns(dropdown);
        dropdown.classList.toggle('dm-open', opening);
        button.setAttribute('aria-expanded', opening ? 'true' : 'false');
    });

    document.addEventListener('click', function (event) {
        if (!nav.contains(event.target)) {
            closeDropdowns(null);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDropdowns(null);
        }
    });

    var switcher = document.getElementById('dm-rc-domain-switch-1152');
    if (switcher) {
        switcher.addEventListener('change', function () {
            var destination = String(this.value || '');
            if (destination) {
                window.location.href = destination;
            }
        });
    }

    var tries = 0;
    function attemptPlacement() {
        tries += 1;
        placeNavigation();
        if (tries < 24) {
            window.setTimeout(attemptPlacement, 180);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attemptPlacement, { once: true });
    } else {
        attemptPlacement();
    }

    if (window.MutationObserver) {
        var observer = new MutationObserver(function () {
            placeNavigation();
        });
        observer.observe(document.documentElement, { childList: true, subtree: true });
        window.setTimeout(function () { observer.disconnect(); }, 7000);
    }
})();
</script>
HTML;
});
