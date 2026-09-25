<?php
/**
 * DomainMonger WHMCS v9 - Active DNS System Indicator 1535
 *
 * Read-only status indicator for the converted ResellerClub workspace and
 * ClouDNS module pages. The status is based on the domain's delegated
 * nameservers, not on whether a DNS zone merely exists.
 *
 * Patch 1535 adds an in-box Refresh Nameservers control for both Register DNS
 * and DNSPlus. The refresh bypasses the 60-second application caches, shows a
 * local spinner, and updates the existing indicator without reloading the page.
 * Patch 1563 places the indicator below the Register DNS submenu on the DNSSEC
 * and Domain Forwarding pages without changing the DNS Records page position.
 * Patch 1564 applies the same below-navigation placement to Auto Renew,
 * Nameservers, WHOIS Contact Info, Private Nameservers, Registrar Lock, and
 * ID Protection.
 * Patch 1565 applies the same below-main-menu placement to the Overview page.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use Illuminate\Database\Capsule\Manager as Capsule;

if (!function_exists('dm_dns_system_1337_normalize_domain')) {
    function dm_dns_system_1337_normalize_domain($value)
    {
        $value = strtolower(trim((string) $value));
        $value = rtrim($value, '.');

        if ($value !== '' && function_exists('idn_to_ascii')) {
            $flags = defined('IDNA_DEFAULT') ? IDNA_DEFAULT : 0;
            $variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
            $ascii = @idn_to_ascii($value, $flags, $variant);
            if (is_string($ascii) && $ascii !== '') {
                $value = strtolower(rtrim($ascii, '.'));
            }
        }

        return preg_replace('/[^a-z0-9._-]/', '', $value);
    }
}

if (!function_exists('dm_dns_system_1337_normalize_ns')) {
    function dm_dns_system_1337_normalize_ns($value)
    {
        return strtolower(rtrim(trim((string) $value), '.'));
    }
}

if (!function_exists('dm_dns_system_1337_is_resellerclub_registrar')) {
    function dm_dns_system_1337_is_resellerclub_registrar($registrar)
    {
        $registrar = strtolower(trim((string) $registrar));
        return $registrar !== '' && (
            strpos($registrar, 'resellerclub') !== false
            || strpos($registrar, 'netearth') !== false
            || $registrar === 'directi'
        );
    }
}

if (!function_exists('dm_dns_system_1337_domain_row')) {
    function dm_dns_system_1337_domain_row($userId, $domainId = 0, $domain = '')
    {
        try {
            $query = Capsule::table('tbldomains')
                ->where('userid', (int) $userId)
                ->select('id', 'domain', 'registrar', 'status');

            if ((int) $domainId > 0) {
                return $query->where('id', (int) $domainId)->first();
            }

            $domain = dm_dns_system_1337_normalize_domain($domain);
            if ($domain !== '') {
                return $query->whereRaw('LOWER(domain) = ?', [$domain])->first();
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}

if (!function_exists('dm_dns_system_1337_cloudns_service')) {
    function dm_dns_system_1337_cloudns_service($userId, $domain, $preferredServiceId = 0)
    {
        $userId = (int) $userId;
        $domain = dm_dns_system_1337_normalize_domain($domain);
        $preferredServiceId = (int) $preferredServiceId;

        try {
            if ($preferredServiceId > 0) {
                $preferred = Capsule::table('tblhosting')
                    ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                    ->where('tblhosting.id', $preferredServiceId)
                    ->where('tblhosting.userid', $userId)
                    ->whereRaw('LOWER(tblproducts.servertype) = ?', ['cloudns'])
                    ->select('tblhosting.id')
                    ->first();

                if ($preferred) {
                    return (int) $preferred->id;
                }
            }

            if ($domain !== '' && Capsule::schema()->hasTable('mod_cloudns_zones')) {
                $ownedZone = Capsule::table('mod_cloudns_zones')
                    ->join('tblhosting', 'tblhosting.id', '=', 'mod_cloudns_zones.serviceid')
                    ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                    ->where('tblhosting.userid', $userId)
                    ->whereRaw('LOWER(tblproducts.servertype) = ?', ['cloudns'])
                    ->whereRaw('LOWER(mod_cloudns_zones.name) = ?', [$domain])
                    ->select('tblhosting.id')
                    ->first();

                if ($ownedZone) {
                    return (int) $ownedZone->id;
                }
            }

            // Registered-domain ClouDNS products may not create one row per zone.
            // Use a fallback only when the client has exactly one active ClouDNS service.
            $services = Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->where('tblhosting.userid', $userId)
                ->whereRaw('LOWER(tblproducts.servertype) = ?', ['cloudns'])
                ->where('tblhosting.domainstatus', 'Active')
                ->select('tblhosting.id')
                ->limit(2)
                ->get();

            if (count($services) === 1) {
                return (int) $services[0]->id;
            }
        } catch (\Throwable $e) {
            return 0;
        }

        return 0;
    }
}

if (!function_exists('dm_dns_system_1337_cpanel_service')) {
    function dm_dns_system_1337_cpanel_service($userId, $domain)
    {
        $userId = (int) $userId;
        $domain = dm_dns_system_1337_normalize_domain($domain);

        try {
            $base = Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->where('tblhosting.userid', $userId)
                ->whereRaw('LOWER(tblproducts.servertype) = ?', ['cpanel'])
                ->where('tblhosting.domainstatus', 'Active');

            if ($domain !== '') {
                $exact = (clone $base)
                    ->whereRaw('LOWER(tblhosting.domain) = ?', [$domain])
                    ->select('tblhosting.id')
                    ->first();

                if ($exact) {
                    return (int) $exact->id;
                }
            }

            // An addon/parked zone may not match the WHMCS service's primary
            // domain. Use the account only when the client has one unambiguous
            // active cPanel service; otherwise omit the link rather than guess.
            $services = (clone $base)
                ->select('tblhosting.id')
                ->limit(2)
                ->get();

            if (count($services) === 1) {
                return (int) $services[0]->id;
            }
        } catch (\Throwable $e) {
            return 0;
        }

        return 0;
    }
}

if (!function_exists('dm_dns_system_1337_extract_nameservers')) {
    function dm_dns_system_1337_extract_nameservers($records)
    {
        $nameservers = [];
        if (!is_array($records)) {
            return $nameservers;
        }

        foreach ($records as $record) {
            if (!is_array($record)) {
                continue;
            }

            $type = strtoupper(trim((string) ($record['type'] ?? '')));
            if ($type !== '' && $type !== 'NS') {
                continue;
            }

            $target = dm_dns_system_1337_normalize_ns($record['target'] ?? '');
            if ($target !== '') {
                $nameservers[] = $target;
            }
        }

        $nameservers = array_values(array_unique($nameservers));
        sort($nameservers, SORT_NATURAL | SORT_FLAG_CASE);
        return $nameservers;
    }
}

if (!function_exists('dm_dns_system_1337_public_nameservers')) {
    function dm_dns_system_1337_public_nameservers($domain, $forceRefresh = false)
    {
        $domain = dm_dns_system_1337_normalize_domain($domain);
        if ($domain === '') {
            return [];
        }

        // Versioned cache key bypasses any empty result retained by Patch 1337.
        // Only positive public DNS answers are cached; an empty lookup is retried.
        $cacheKey = 'dm_dns_system_1412_' . sha1($domain);
        $forceRefresh = (bool) $forceRefresh;
        if (!$forceRefresh && isset($_SESSION[$cacheKey]) && is_array($_SESSION[$cacheKey])) {
            $cached = $_SESSION[$cacheKey];
            if (
                isset($cached['time'], $cached['nameservers'])
                && is_array($cached['nameservers'])
                && $cached['nameservers']
                && (time() - (int) $cached['time']) < 60
            ) {
                return $cached['nameservers'];
            }
        }

        $nameservers = [];
        if (function_exists('dns_get_record')) {
            $dnsType = defined('DNS_NS') ? DNS_NS : 2;
            $nameservers = dm_dns_system_1337_extract_nameservers(@dns_get_record($domain, $dnsType));

            // Some hosting resolvers return an empty NS-only answer while still
            // returning NS records in an ANY response.
            if (!$nameservers && defined('DNS_ANY')) {
                $nameservers = dm_dns_system_1337_extract_nameservers(@dns_get_record($domain, DNS_ANY));
            }
        }

        if ($nameservers) {
            $_SESSION[$cacheKey] = [
                'time' => time(),
                'nameservers' => $nameservers,
            ];
        } else {
            unset($_SESSION[$cacheKey]);
        }

        return $nameservers;
    }
}

if (!function_exists('dm_dns_system_1337_registrar_nameservers')) {
    function dm_dns_system_1337_registrar_nameservers($domainId, $forceRefresh = false)
    {
        $domainId = (int) $domainId;
        $forceRefresh = (bool) $forceRefresh;
        if ($domainId < 1 || !function_exists('localAPI')) {
            return [];
        }

        // The registrar module call can require a remote API request. Reuse a
        // recent result while the client moves between DNS tabs or reloads the
        // page, matching the existing 60-second public-DNS cache lifetime.
        $cacheKey = 'dm_dns_system_registrar_1532_' . $domainId;
        $cachedNameservers = [];
        $cachedAge = null;

        if (isset($_SESSION[$cacheKey]) && is_array($_SESSION[$cacheKey])) {
            $cached = $_SESSION[$cacheKey];
            if (isset($cached['time'], $cached['nameservers']) && is_array($cached['nameservers'])) {
                $cachedAge = time() - (int) $cached['time'];
                $cachedNameservers = $cached['nameservers'];

                $cacheLifetime = $cachedNameservers ? 60 : 15;
                if (!$forceRefresh && $cachedAge >= 0 && $cachedAge < $cacheLifetime) {
                    return $cachedNameservers;
                }
            }
        }

        try {
            $result = localAPI('DomainGetNameservers', ['domainid' => $domainId]);
        } catch (\Throwable $e) {
            // A recent positive result is safer than briefly changing the
            // indicator to Unknown when the registrar has a transient delay.
            if ($cachedNameservers && $cachedAge !== null && $cachedAge < 300) {
                return $cachedNameservers;
            }
            return [];
        }

        if (!is_array($result) || strtolower((string) ($result['result'] ?? '')) !== 'success') {
            if ($cachedNameservers && $cachedAge !== null && $cachedAge < 300) {
                return $cachedNameservers;
            }

            $_SESSION[$cacheKey] = [
                'time' => time(),
                'nameservers' => [],
            ];
            return [];
        }

        $nameservers = [];
        foreach (['ns1', 'ns2', 'ns3', 'ns4', 'ns5'] as $field) {
            $value = dm_dns_system_1337_normalize_ns($result[$field] ?? '');
            if ($value !== '') {
                $nameservers[] = $value;
            }
        }

        $nameservers = array_values(array_unique($nameservers));
        sort($nameservers, SORT_NATURAL | SORT_FLAG_CASE);

        $_SESSION[$cacheKey] = [
            'time' => time(),
            'nameservers' => $nameservers,
        ];

        return $nameservers;
    }
}

if (!function_exists('dm_dns_system_1337_classify')) {
    function dm_dns_system_1337_classify(array $nameservers)
    {
        $resellerClub = [
            'ns5.domainmonger.com',
            'ns6.domainmonger.com',
            'ns7.domainmonger.com',
            'ns8.domainmonger.com',
        ];
        $cloudns = [
            'ns31.domainmonger.com',
            'ns32.domainmonger.com',
            'ns33.domainmonger.com',
            'ns34.domainmonger.com',
        ];
        $cpanel = [
            'ns50a.domainmonger.com',
            'ns50b.domainmonger.com',
        ];

        if (!$nameservers) {
            return 'unknown';
        }

        $rcMatches = array_values(array_intersect($nameservers, $resellerClub));
        $cloudMatches = array_values(array_intersect($nameservers, $cloudns));
        $cpanelMatches = array_values(array_intersect($nameservers, $cpanel));
        $allRc = count($rcMatches) >= 2 && count(array_diff($nameservers, $resellerClub)) === 0;
        $allCloud = count($cloudMatches) >= 2 && count(array_diff($nameservers, $cloudns)) === 0;
        $allCpanel = count($cpanelMatches) >= 2 && count(array_diff($nameservers, $cpanel)) === 0;

        if ($allRc) {
            return 'resellerclub';
        }
        if ($allCloud) {
            return 'cloudns';
        }
        if ($allCpanel) {
            return 'cpanel';
        }
        if ($rcMatches || $cloudMatches || $cpanelMatches) {
            return 'mixed';
        }

        return 'external';
    }
}

if (!function_exists('dm_dns_system_1337_context')) {
    function dm_dns_system_1337_context($forceRefresh = null)
    {
        static $context = false;
        if ($context !== false) {
            return $context;
        }

        if ($forceRefresh === null) {
            $forceRefresh = (string) ($_GET['dmnsrefresh1535'] ?? '') === '1';
        }
        $forceRefresh = (bool) $forceRefresh;

        $context = null;
        $userId = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : 0;
        if ($userId < 1) {
            return null;
        }

        $script = strtolower(basename((string) parse_url($_SERVER['PHP_SELF'] ?? '', PHP_URL_PATH)));
        $action = strtolower(trim((string) ($_GET['action'] ?? '')));
        $pageSystem = '';
        $domain = '';
        $domainId = 0;
        $serviceId = 0;
        $domainRow = null;

        // ClouDNS product pages.
        if ($script === 'clientarea.php' && $action === 'productdetails' && !empty($_GET['id'])) {
            $serviceId = (int) $_GET['id'];
            try {
                $service = Capsule::table('tblhosting')
                    ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                    ->where('tblhosting.id', $serviceId)
                    ->where('tblhosting.userid', $userId)
                    ->select('tblhosting.domain', 'tblproducts.servertype')
                    ->first();

                if ($service && strtolower((string) $service->servertype) === 'cloudns') {
                    $pageSystem = 'cloudns';
                    $domain = dm_dns_system_1337_normalize_domain($_GET['zone'] ?? $service->domain ?? '');
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Converted ResellerClub/NetEarthOne domain-management pages.
        if ($pageSystem === '') {
            $resellerRoutes = (
                ($script === 'clientarea.php' && in_array($action, ['domaindetails', 'domaindns', 'domaincontacts', 'domaingetepp', 'domainemailforwarding'], true))
                || in_array($script, ['dnsmanagement.php', 'domainmanagement.php', 'domainforwarding.php', 'emailmanagement.php'], true)
            );

            if ($resellerRoutes) {
                if (!empty($_GET['domainid'])) {
                    $domainId = (int) $_GET['domainid'];
                } elseif ($script === 'clientarea.php' && $action === 'domaindetails' && !empty($_GET['id'])) {
                    $domainId = (int) $_GET['id'];
                }

                $domain = dm_dns_system_1337_normalize_domain($_GET['domain'] ?? '');
                $domainRow = dm_dns_system_1337_domain_row($userId, $domainId, $domain);

                if ($domainRow && dm_dns_system_1337_is_resellerclub_registrar($domainRow->registrar ?? '')) {
                    $pageSystem = 'resellerclub';
                    $domainId = (int) $domainRow->id;
                    $domain = dm_dns_system_1337_normalize_domain($domainRow->domain ?? $domain);
                }
            }
        }

        if ($pageSystem === '' || $domain === '') {
            return null;
        }

        $domainRow = $domainRow ?: dm_dns_system_1337_domain_row($userId, $domainId, $domain);
        if ($domainId < 1 && $domainRow) {
            $domainId = (int) ($domainRow->id ?? 0);
        }

        // Public recursive DNS can continue returning an older delegation while
        // its cache expires. For domains present in WHMCS, prefer the registrar's
        // current delegation so the indicator updates as soon as the nameserver
        // change is accepted. Keep the public answer for propagation details and
        // fall back to it when the registrar lookup is unavailable.
        $publicNameservers = dm_dns_system_1337_public_nameservers($domain, $forceRefresh);
        $registrarNameservers = $domainId > 0
            ? dm_dns_system_1337_registrar_nameservers($domainId, $forceRefresh)
            : [];

        if ($registrarNameservers) {
            $nameservers = $registrarNameservers;
            $nameserverSource = 'registrar';
        } elseif ($publicNameservers) {
            $nameservers = $publicNameservers;
            $nameserverSource = 'public';
        } else {
            $nameservers = [];
            $nameserverSource = 'none';
        }

        $activeSystem = dm_dns_system_1337_classify($nameservers);
        $link = '';
        $linkLabel = '';

        if ($activeSystem === 'cloudns' && $pageSystem !== 'cloudns') {
            $cloudServiceId = dm_dns_system_1337_cloudns_service($userId, $domain, 0);
            if ($cloudServiceId > 0) {
                $link = 'clientarea.php?action=productdetails&id=' . $cloudServiceId
                    . '&customAction=zone-settings&zone=' . rawurlencode($domain);
                $linkLabel = 'Open DNSPlus';
            }
        } elseif ($activeSystem === 'resellerclub' && $pageSystem !== 'resellerclub') {
            $domainRow = $domainRow ?: dm_dns_system_1337_domain_row($userId, 0, $domain);
            if ($domainRow && dm_dns_system_1337_is_resellerclub_registrar($domainRow->registrar ?? '')) {
                $link = 'clientarea.php?action=domaindns&domainid=' . (int) $domainRow->id . '&dmconverted=1';
                $linkLabel = 'Open RegistrarDNS';
            }
        } elseif ($activeSystem === 'cpanel') {
            $cpanelServiceId = dm_dns_system_1337_cpanel_service($userId, $domain);
            if ($cpanelServiceId > 0) {
                $link = 'dm-cpanel-zone-editor.php?serviceid=' . $cpanelServiceId
                    . '&domain=' . rawurlencode($domain);
                $linkLabel = 'Open cPanel Zone Editor';
            }
        }

        $indicatorPlacement = 'default';
        if ($pageSystem === 'resellerclub') {
            $recordType = strtoupper(trim((string) ($_GET['nsrecordtype'] ?? '')));
            $section = strtolower(trim((string) ($_GET['dmsection'] ?? '')));
            $isDnssecPage = $script === 'dnsmanagement.php' && (
                $action === 'dnsseczone'
                || $recordType === 'DNSSEC'
                || $section === 'dnssec'
            );
            $isDomainForwardingPage = $script === 'domainforwarding.php';

            $detailSection = strtolower(trim((string) ($_GET['dmsection'] ?? $_GET['dmnav'] ?? '')));
            $isDomainSettingsOrSecurityPage = false;

            if ($script === 'clientarea.php') {
                if ($action === 'domaincontacts') {
                    $isDomainSettingsOrSecurityPage = true;
                } elseif ($action === 'domaindetails' && in_array($detailSection, [
                    'overview',
                    'autorenew',
                    'nameservers',
                    'reglock',
                    'addons',
                    'idprotection',
                ], true)) {
                    $isDomainSettingsOrSecurityPage = true;
                }
            } elseif ($script === 'domainmanagement.php' && $action === 'childns') {
                $isDomainSettingsOrSecurityPage = true;
            }

            if ($isDnssecPage || $isDomainForwardingPage) {
                $indicatorPlacement = 'after-dns-submenu';
            } elseif ($isDomainSettingsOrSecurityPage) {
                $indicatorPlacement = 'after-main-menu';
            }
        }

        $context = [
            'pageSystem' => $pageSystem,
            'activeSystem' => $activeSystem,
            'domain' => $domain,
            'nameservers' => $nameservers,
            'publicNameservers' => $publicNameservers,
            'nameserverSource' => $nameserverSource,
            'link' => $link,
            'linkLabel' => $linkLabel,
            'indicatorPlacement' => $indicatorPlacement,
        ];

        return $context;
    }
}

add_hook('ClientAreaHeadOutput', 25, function () {
    $context = dm_dns_system_1337_context();
    if (!$context) {
        return '';
    }

    return <<<'HTML'
<style id="dm-dns-system-indicator-1337-css">
#dm-dns-system-indicator-1337 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    width: 100%;
    margin: 0 0 16px;
    padding: 12px 14px;
    border: 1px solid #d7e0e8;
    border-radius: 6px;
    background: #ffffff;
    color: #273b50;
    box-sizing: border-box;
    font-size: 14px;
    line-height: 1.45;
}
#dm-dns-system-indicator-1337[hidden] { display: none !important; }
#dm-rc-unified-nav-1152 > #dm-dns-system-indicator-1337 {
    margin: 12px 0 0 !important;
}
#dm-dns-system-indicator-1337.dm-dns-system-current {
    border-color: #b9ddc5;
    background: #eef8f1;
}
#dm-dns-system-indicator-1337.dm-dns-system-other {
    border-color: #c7d9e8;
    background: #eef5fb;
}
#dm-dns-system-indicator-1337.dm-dns-system-external,
#dm-dns-system-indicator-1337.dm-dns-system-mixed {
    border-color: #ecd58d;
    background: #fff8df;
}
#dm-dns-system-indicator-1337.dm-dns-system-unknown {
    border-color: #d9dee3;
    background: #f5f6f7;
}
#dm-dns-system-indicator-1337 .dm-dns-system-main {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    min-width: 0;
}
#dm-dns-system-indicator-1337 .dm-dns-system-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    min-height: 24px;
    padding: 3px 9px;
    border-radius: 999px;
    background: #737d86;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
}
#dm-dns-system-indicator-1337.dm-dns-system-current .dm-dns-system-pill {
    background: #2f8f4e;
}
#dm-dns-system-indicator-1337.dm-dns-system-mixed .dm-dns-system-pill {
    background: #d8741f;
}
#dm-dns-system-indicator-1337 .dm-dns-system-copy {
    min-width: 0;
}
#dm-dns-system-indicator-1337 .dm-dns-system-copy strong {
    display: block;
    margin: 0 0 2px;
    color: #163a5f;
    font-size: 14px;
    font-weight: 700;
}
#dm-dns-system-indicator-1337 .dm-dns-system-copy span {
    display: block;
    color: #536577;
    font-size: 12px;
    overflow-wrap: anywhere;
}
#dm-dns-system-indicator-1337 .dm-dns-system-copy .dm-dns-system-access-note {
    margin-top: 3px;
    color: #8a5a17;
    font-weight: 600;
}
#dm-dns-system-indicator-1337 .dm-dns-system-copy .dm-dns-system-detection-note {
    margin-top: 3px;
    color: #697a8a;
    font-style: italic;
}
#dm-dns-system-indicator-1337 .dm-dns-system-actions {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex: 0 0 auto;
    flex-wrap: wrap;
}
#dm-dns-system-indicator-1337 .dm-dns-system-link,
#dm-dns-system-indicator-1337 .dm-dns-system-link:visited,
#dm-dns-system-indicator-1337 .dm-dns-system-refresh {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    min-height: 34px;
    padding: 7px 13px;
    border: 1px solid #163a5f;
    border-radius: 5px;
    background: #163a5f;
    color: #ffffff !important;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.2;
    text-decoration: none !important;
    white-space: nowrap;
}
#dm-dns-system-indicator-1337 .dm-dns-system-link:hover,
#dm-dns-system-indicator-1337 .dm-dns-system-link:focus,
#dm-dns-system-indicator-1337 .dm-dns-system-refresh:hover,
#dm-dns-system-indicator-1337 .dm-dns-system-refresh:focus {
    border-color: #214e7a !important;
    background: #214e7a !important;
    color: #ffffff !important;
    text-decoration: none !important;
}
#dm-dns-system-indicator-1337 .dm-dns-system-refresh {
    border-radius: 5px !important;
    background: #163a5f !important;
    border-color: #163a5f !important;
    color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    box-shadow: none !important;
}
#dm-dns-system-indicator-1337 .dm-dns-system-refresh:disabled {
    background: #163a5f !important;
    border-color: #163a5f !important;
    color: #ffffff !important;
    cursor: wait;
    opacity: .82;
}
#dm-dns-system-indicator-1337 .dm-dns-system-refresh-icon,
#dm-dns-system-indicator-1337 .dm-dns-system-refresh-spinner {
    margin-right: 6px;
}
#dm-dns-system-indicator-1337 .dm-dns-system-refresh-spinner {
    display: none;
}
#dm-dns-system-indicator-1337 .dm-dns-system-refresh.dm-is-refreshing .dm-dns-system-refresh-icon {
    display: none;
}
#dm-dns-system-indicator-1337 .dm-dns-system-refresh.dm-is-refreshing .dm-dns-system-refresh-spinner {
    display: inline-block;
}
@media (max-width: 700px) {
    #dm-dns-system-indicator-1337 {
        align-items: stretch;
        flex-direction: column;
    }
    #dm-dns-system-indicator-1337 .dm-dns-system-actions {
        width: 100%;
        justify-content: stretch;
    }
    #dm-dns-system-indicator-1337 .dm-dns-system-link,
    #dm-dns-system-indicator-1337 .dm-dns-system-refresh {
        flex: 1 1 180px;
        width: auto;
    }
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 99, function () {
    $context = dm_dns_system_1337_context();
    if (!$context) {
        return '';
    }

    $pageSystem = (string) $context['pageSystem'];
    $activeSystem = (string) $context['activeSystem'];
    $pageLabel = $pageSystem === 'cloudns' ? 'DNSPlus' : 'RegistrarDNS';
    $activeLabelMap = [
        'cloudns' => 'DNSPlus',
        'resellerclub' => 'RegistrarDNS',
        'cpanel' => 'cPanel',
        'external' => 'External nameservers',
        'mixed' => 'Mixed nameservers',
        'unknown' => 'Unknown',
    ];
    $activeLabel = $activeLabelMap[$activeSystem] ?? 'Unknown';
    $isCurrent = $pageSystem === $activeSystem;

    if ($isCurrent) {
        $stateClass = 'dm-dns-system-current';
        $pill = 'Active';
        $headline = $pageLabel . ' is the active DNS system for this domain.';
    } elseif ($activeSystem === 'mixed') {
        $stateClass = 'dm-dns-system-mixed';
        $pill = 'Changing';
        $headline = 'The nameserver change may still be in progress.';
    } elseif ($activeSystem === 'unknown') {
        $stateClass = 'dm-dns-system-unknown';
        $pill = 'Unknown';
        $headline = 'The active DNS system could not be confirmed.';
    } elseif ($activeSystem === 'external') {
        $stateClass = 'dm-dns-system-external';
        $pill = 'Not Active';
        $headline = $pageLabel . ' is not active. This domain uses external DNS.';
    } else {
        $stateClass = 'dm-dns-system-other';
        $pill = 'Not Active';
        $headline = $pageLabel . ' is not active. Active DNS system: ' . $activeLabel . '.';
    }

    $nameservers = $context['nameservers'];
    $publicNameservers = isset($context['publicNameservers']) && is_array($context['publicNameservers'])
        ? $context['publicNameservers']
        : [];
    $nameserverSource = (string) ($context['nameserverSource'] ?? 'public');
    if ($nameservers && $nameserverSource === 'registrar') {
        $detail = 'Current RegistrarDNS nameservers: ' . implode(', ', $nameservers);
        if ($publicNameservers && $publicNameservers !== $nameservers) {
            $detail .= '. Public DNS is still returning: ' . implode(', ', $publicNameservers);
        }
    } elseif ($nameservers) {
        $detail = 'Authoritative nameservers: ' . implode(', ', $nameservers);
    } else {
        $detail = 'No public or registrar nameservers were available.';
    }

    $e = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    $linkHtml = '';
    if (!$isCurrent && !empty($context['link']) && !empty($context['linkLabel'])) {
        $newWindow = $activeSystem === 'cpanel' ? ' target="_blank" rel="noopener"' : '';
        $linkHtml = '<a class="dm-dns-system-link" href="' . $e($context['link']) . '"' . $newWindow . '>'
            . $e($context['linkLabel']) . '</a>';
    }

    $accessNote = '';
    if (!$isCurrent && $linkHtml === '') {
        if ($activeSystem === 'resellerclub') {
            $accessNote = 'RegistrarDNS nameservers were detected, but no matching registered domain was found in this account.';
        } elseif ($activeSystem === 'cloudns') {
            $accessNote = 'DNSPlus nameservers were detected, but no matching DNSPlus zone or unambiguous DNSPlus service was found.';
        } elseif ($activeSystem === 'cpanel') {
            $accessNote = 'cPanel nameservers were detected, but no matching cPanel account was found.';
        }
    }

    $accessNoteHtml = $accessNote !== ''
        ? '<span class="dm-dns-system-access-note">' . $e($accessNote) . '</span>'
        : '';

    $detectionNote = 'Recently updated nameservers may not appear immediately and may take 30 minutes, or occasionally longer, to be detected due to DNS caching.';
    $detectionNoteHtml = '<span class="dm-dns-system-detection-note">' . $e($detectionNote) . '</span>';

    $refreshHtml = '<button type="button" class="dm-dns-system-refresh" aria-label="Refresh nameservers">'
        . '<i class="fas fa-sync-alt dm-dns-system-refresh-icon" aria-hidden="true"></i>'
        . '<i class="fas fa-spinner fa-spin dm-dns-system-refresh-spinner" aria-hidden="true"></i>'
        . '<span class="dm-dns-system-refresh-label">Refresh Nameservers</span>'
        . '</button>';
    $actionsHtml = '<div class="dm-dns-system-actions">' . $linkHtml . $refreshHtml . '</div>';

    $html = '<div id="dm-dns-system-indicator-1337" class="' . $e($stateClass) . '" hidden>'
        . '<div class="dm-dns-system-main">'
        . '<span class="dm-dns-system-pill">' . $e($pill) . '</span>'
        . '<div class="dm-dns-system-copy"><strong>' . $e($headline) . '</strong><span>' . $e($detail) . '</span>' . $accessNoteHtml . $detectionNoteHtml . '</div>'
        . '</div>'
        . $actionsHtml
        . '</div>';

    $config = json_encode([
        'pageSystem' => $pageSystem,
        'refreshParam' => 'dmnsrefresh1535',
        'indicatorPlacement' => (string) ($context['indicatorPlacement'] ?? 'default'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return $html . <<<HTML
<script id="dm-dns-system-indicator-1337-js">
(function () {
    var config = {$config};
    var indicator = document.getElementById('dm-dns-system-indicator-1337');
    if (!indicator) { return; }
    var refreshResetTimer = 0;

    function setRefreshState(button, state, label) {
        var text = button ? button.querySelector('.dm-dns-system-refresh-label') : null;
        if (!button || !text) {
            return;
        }

        window.clearTimeout(refreshResetTimer);
        button.disabled = state === 'loading';
        button.classList.toggle('dm-is-refreshing', state === 'loading');
        text.textContent = label;
    }

    function resetRefreshButtonLater(button) {
        window.clearTimeout(refreshResetTimer);
        refreshResetTimer = window.setTimeout(function () {
            if (button && document.documentElement.contains(button)) {
                setRefreshState(button, 'idle', 'Refresh Nameservers');
            }
        }, 1600);
    }

    function bindRefreshButton() {
        var button = indicator.querySelector('.dm-dns-system-refresh');
        if (!button || button.getAttribute('data-dm-refresh-bound') === '1') {
            return;
        }
        button.setAttribute('data-dm-refresh-bound', '1');

        button.addEventListener('click', function () {
            var activeButton = indicator.querySelector('.dm-dns-system-refresh') || button;
            var requestUrl = new URL(window.location.href);
            requestUrl.searchParams.set(config.refreshParam || 'dmnsrefresh1535', '1');
            requestUrl.searchParams.set('dmnsrefreshnonce1535', String(Date.now()));
            requestUrl.hash = '';

            setRefreshState(activeButton, 'loading', 'Checking Nameservers…');

            window.fetch(requestUrl.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                redirect: 'follow',
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('Nameserver lookup failed with HTTP ' + response.status + '.');
                }
                return response.text();
            }).then(function (html) {
                var parsed = new DOMParser().parseFromString(html, 'text/html');
                var fresh = parsed.getElementById('dm-dns-system-indicator-1337');
                if (!fresh) {
                    throw new Error('The refreshed nameserver status was unavailable.');
                }

                indicator.className = fresh.className;
                indicator.innerHTML = fresh.innerHTML;
                indicator.hidden = false;
                bindRefreshButton();
                placeIndicator();

                var updatedButton = indicator.querySelector('.dm-dns-system-refresh');
                setRefreshState(updatedButton, 'success', 'Nameservers Updated');
                resetRefreshButtonLater(updatedButton);
            }).catch(function () {
                var failedButton = indicator.querySelector('.dm-dns-system-refresh') || activeButton;
                setRefreshState(failedButton, 'error', 'Refresh Failed');
                resetRefreshButtonLater(failedButton);
            });
        });
    }

    function placeAfter(target) {
        if (!target || !target.parentNode) {
            return false;
        }
        if (target.nextElementSibling !== indicator) {
            target.parentNode.insertBefore(indicator, target.nextSibling);
        }
        indicator.hidden = false;
        return true;
    }

    function placeAfterDnsSubmenu(nav) {
        if (!nav) {
            return false;
        }
        var submenu = nav.querySelector('.dm-rc-dns-submenu');
        if (!submenu || submenu.parentNode !== nav) {
            return false;
        }
        if (submenu.nextElementSibling !== indicator) {
            nav.insertBefore(indicator, submenu.nextSibling);
        }
        indicator.hidden = false;
        return true;
    }

    function placeAfterMainMenu(nav) {
        if (!nav) {
            return false;
        }
        var mainMenu = nav.querySelector('.dm-rc-main-menu');
        if (!mainMenu || mainMenu.parentNode !== nav) {
            return false;
        }
        if (mainMenu.nextElementSibling !== indicator) {
            nav.insertBefore(indicator, mainMenu.nextSibling);
        }
        indicator.hidden = false;
        return true;
    }

    function placeIndicator() {
        var target = null;

        if (config.pageSystem === 'cloudns') {
            target = document.getElementById('cloudnsSettingsMenu')
                || document.querySelector('.cloudns-manage-domain-header');
            return placeAfter(target);
        }

        // Final Register DNS position. The unified menu is emitted later in the
        // footer, so it may not exist when this script first runs.
        target = document.getElementById('dm-rc-unified-nav-1152');
        if (target && !target.hidden) {
            if (config.indicatorPlacement === 'after-dns-submenu' && placeAfterDnsSubmenu(target)) {
                return true;
            }
            if (config.indicatorPlacement === 'after-main-menu' && placeAfterMainMenu(target)) {
                return true;
            }
            if (target.closest && target.closest('#main-body') && placeAfter(target)) {
                return true;
            }
        }

        // Immediate provisional position: use markup already present in the page
        // body instead of waiting for the late footer navigation. The legacy menu
        // is subsequently hidden and the unified menu is inserted before it, so
        // the indicator naturally remains directly below the visible navigation.
        var legacy = document.querySelector(
            '.dm-domain-section-menu, .dm-private-ns-menu, .dm-epp-section-menu, .dm-pns-section-menu'
        );
        if (legacy && legacy.parentNode) {
            placeAfter(legacy);
            return false;
        }

        // Converted native DNS routes can omit the legacy menu. Use the same
        // stable shell family as the unified-menu hook. When that menu mounts it
        // is prepended ahead of this indicator, preserving the final order.
        var shell = document.querySelector(
            '#main-body .primary-content, #main-body .main-content, '
            + '#dm-dns-unified-shell, .dm-dns-workspace'
        );
        if (shell) {
            if (shell.firstElementChild !== indicator) {
                shell.insertBefore(indicator, shell.firstChild);
            }
            indicator.hidden = false;
        }

        return false;
    }

    bindRefreshButton();

    if (!placeIndicator()) {
        var observer = new MutationObserver(function () {
            if (placeIndicator()) {
                observer.disconnect();
            }
        });
        observer.observe(document.documentElement, { childList: true, subtree: true });
        window.setTimeout(function () {
            observer.disconnect();
            placeIndicator();
        }, 3000);
    }
})();
</script>
HTML;
});
