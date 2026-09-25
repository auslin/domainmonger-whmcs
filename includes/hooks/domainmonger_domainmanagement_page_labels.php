<?php
/**
 * DomainMonger domain-management page labels.
 *
 * Patch 816:
 * Builds from confirmed Restore 814 and replaces failed Patch 815. Adds hash-tab detection for the standard domaindetails tabs.
 *
 * Adds explicit support for module-style domain pages that use standalone
 * scripts instead of standard clientarea.php actions:
 * - dnsmanagement.php       => Manage Domain: DNS Management
 * - domainforwarding.php    => Manage Domain: Domain Forwarding
 * - emailmanagement.php     => Manage Domain: Email Forwarding
 *
 * Pattern:
 * - Top banner/header: Manage Domain: Page Name
 * - Main content heading: domain-name.com
 *
 * Scope:
 * WHMCS client-facing domain management pages only.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm_domainmanagement_labels_script')) {
    function dm_domainmanagement_labels_script(): string
    {
        return strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
    }
}

if (!function_exists('dm_domainmanagement_labels_action')) {
    function dm_domainmanagement_labels_action(): string
    {
        $keys = ['action', 'a', 'modop', 'op', 'view'];

        foreach ($keys as $key) {
            if (!isset($_REQUEST[$key])) {
                continue;
            }

            $value = $_REQUEST[$key];

            if (is_array($value)) {
                $value = reset($value);
            }

            $value = strtolower(trim((string) $value));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('dm_domainmanagement_labels_uri')) {
    function dm_domainmanagement_labels_uri(): string
    {
        return strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
    }
}

if (!function_exists('dm_domainmanagement_labels_script_page_map')) {
    function dm_domainmanagement_labels_script_page_map(): array
    {
        return [
            'dnsmanagement.php' => 'DNS Management',
            'domainforwarding.php' => 'Domain Forwarding',
            'emailmanagement.php' => 'Email Forwarding',
        ];
    }
}

if (!function_exists('dm_domainmanagement_labels_is_page')) {
    function dm_domainmanagement_labels_is_page(): bool
    {
        $script = dm_domainmanagement_labels_script();
        $action = dm_domainmanagement_labels_action();
        $uri = dm_domainmanagement_labels_uri();

        $scriptMap = dm_domainmanagement_labels_script_page_map();

        if (isset($scriptMap[$script])) {
            return true;
        }

        if ($script === 'domainmanagement.php' || strpos($uri, '/manage/domainmanagement.php') !== false) {
            return true;
        }

        if ($script !== 'clientarea.php' && strpos($uri, '/manage/clientarea.php') === false) {
            return false;
        }

        $knownActions = [
            'domaindetails',
            'domainautorenew',
            'domainreglock',
            'domainaddons',
            'domaincontacts',
            'domainregisterns',
            'domainnameservers',
            'domaindns',
            'domaindnsmanagement',
            'dnsmanagement',
            'managedns',
            'domainmanagedns',
            'domaindnssec',
            'dnssecmanagement',
            'domainforwarding',
            'domainurlforwarding',
            'urlforwarding',
            'domainemailforwarding',
            'domainemailfwd',
            'emailforwarding',
            'mailforwarding',
            'domaingetepp',
            'domainepp',
            'getepp',
        ];

        if (in_array($action, $knownActions, true)) {
            return true;
        }

        return strpos($action, 'domain') === 0
            && strpos($uri, 'cart.php') === false
            && strpos($uri, 'a=add') === false;
    }
}

if (!function_exists('dm_domainmanagement_labels_domain_id')) {
    function dm_domainmanagement_labels_domain_id(): int
    {
        $keys = ['domainid', 'id', 'domain_id', 'did'];

        foreach ($keys as $key) {
            if (!isset($_REQUEST[$key])) {
                continue;
            }

            $value = $_REQUEST[$key];

            if (is_array($value)) {
                $value = reset($value);
            }

            $id = (int) $value;

            if ($id > 0) {
                return $id;
            }
        }

        return 0;
    }
}

if (!function_exists('dm_domainmanagement_labels_domain_name')) {
    function dm_domainmanagement_labels_domain_name(): string
    {
        foreach (['domain', 'domainname', 'sld'] as $key) {
            $requestDomain = trim((string) ($_REQUEST[$key] ?? ''));

            if ($requestDomain !== '' && preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $requestDomain)) {
                return $requestDomain;
            }
        }

        $domainId = dm_domainmanagement_labels_domain_id();

        if ($domainId <= 0) {
            return '';
        }

        try {
            $query = Capsule::table('tbldomains')->where('id', $domainId);

            if (!empty($_SESSION['uid'])) {
                $query->where('userid', (int) $_SESSION['uid']);
            }

            return trim((string) $query->value('domain'));
        } catch (\Throwable $e) {
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
}

if (!function_exists('dm_domainmanagement_labels_page_map')) {
    function dm_domainmanagement_labels_page_map(): array
    {
        return [
            'domaindetails' => 'Overview',
            'domainautorenew' => 'Auto Renew',
            'domainreglock' => 'Registrar Lock',
            'domainaddons' => 'Addons',
            'domaincontacts' => 'WHOIS Contact Info',
            'domainregisterns' => 'Private Nameservers',
            'domainnameservers' => 'Nameservers',
            'domaindns' => 'DNS Management',
            'domaindnsmanagement' => 'DNS Management',
            'dnsmanagement' => 'DNS Management',
            'managedns' => 'DNS Management',
            'domainmanagedns' => 'DNS Management',
            'domaindnssec' => 'DNSSEC Management',
            'dnssecmanagement' => 'DNSSEC Management',
            'domainforwarding' => 'Domain Forwarding',
            'domainurlforwarding' => 'Domain Forwarding',
            'urlforwarding' => 'Domain Forwarding',
            'domainemailforwarding' => 'Email Forwarding',
            'domainemailfwd' => 'Email Forwarding',
            'emailforwarding' => 'Email Forwarding',
            'mailforwarding' => 'Email Forwarding',
            'domaingetepp' => 'Get EPP Code',
            'domainepp' => 'Get EPP Code',
            'getepp' => 'Get EPP Code',
        ];
    }
}

if (!function_exists('dm_domainmanagement_labels_page_name')) {
    function dm_domainmanagement_labels_page_name(): string
    {
        $script = dm_domainmanagement_labels_script();
        $scriptMap = dm_domainmanagement_labels_script_page_map();

        if (isset($scriptMap[$script])) {
            return $scriptMap[$script];
        }

        $action = dm_domainmanagement_labels_action();
        $map = dm_domainmanagement_labels_page_map();

        if (isset($map[$action])) {
            return $map[$action];
        }

        return '';
    }
}

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_domainmanagement_labels_is_page()) {
        return '';
    }

    $domainName = htmlspecialchars(dm_domainmanagement_labels_domain_name(), ENT_QUOTES, 'UTF-8');
    $pageName = htmlspecialchars(dm_domainmanagement_labels_page_name(), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<script id="dm-domainmanagement-page-labels-js-v816">
(function () {
    'use strict';

    var domainName = '{$domainName}';
    var mappedPageName = '{$pageName}';

    var knownPageLabels = [
        'overview',
        'auto renew',
        'nameservers',
        'private nameservers',
        'registrar lock',
        'addons',
        'contact information',
        'whois contact info',
        'dns management',
        'dnssec management',
        'dnsec management',
        'domain forwarding',
        'email forwarding',
        'get epp code'
    ];

    var sideMenuNameMap = {
        'contact information': 'WHOIS Contact Info',
        'dnsec management': 'DNSSEC Management',
        'dnssec management': 'DNSSEC Management'
    };

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function normalize(text) {
        return String(text || '').replace(/\\s+/g, ' ').trim();
    }

    function normalizedLower(text) {
        return normalize(text).toLowerCase();
    }

    function isDomainLike(text) {
        return /^[a-z0-9.-]+\\.[a-z]{2,}$/i.test(normalize(text));
    }

    function isKnownPageLabel(text) {
        return knownPageLabels.indexOf(normalizedLower(text)) !== -1;
    }

    function fixPageLabel(text) {
        var lower = normalizedLower(text);
        return sideMenuNameMap[lower] || normalize(text);
    }

    function setText(el, text) {
        if (!el || !text) {
            return false;
        }

        if (normalize(el.textContent) !== text) {
            el.textContent = text;
        }

        el.setAttribute('data-dm-domainmanagement-label-fixed', '1');
        return true;
    }

    function isSideMenu(el) {
        return !!(el && el.closest && el.closest('.panel-sidebar, .sidebar, aside, .list-group, .sidebar-menu'));
    }

    function discoverPageNameFromSideMenu() {
        var active = document.querySelector('.panel-sidebar a.active, .panel-sidebar .active > a, .sidebar a.active, .sidebar .active > a, .list-group-item.active, .list-group .active > a');
        var text = normalize(active ? active.textContent : '');

        if (!text) {
            return '';
        }

        return fixPageLabel(text);
    }

    function discoverPageNameFromHeadings() {
        var headings = document.querySelectorAll('h1, h2, h3, h4, .card-title, .panel-title, .section-title, .header-lined h1, .header-lined h2, .panel-heading, .card-header');
        var i;
        var text;

        for (i = 0; i < headings.length; i += 1) {
            if (isSideMenu(headings[i])) {
                continue;
            }

            text = normalize(headings[i].textContent);

            if (!text || isDomainLike(text)) {
                continue;
            }

            if (isKnownPageLabel(text)) {
                return fixPageLabel(text);
            }
        }

        return '';
    }

    function pageNameFromHash() {
        var hash = String(window.location.hash || '').toLowerCase();

        if (!hash) {
            return '';
        }

        if (hash === '#tabautorenew' || hash.indexOf('autorenew') !== -1 || hash.indexOf('auto-renew') !== -1) {
            return 'Auto Renew';
        }

        if (hash === '#tabnameservers' || hash.indexOf('nameserver') !== -1 || hash.indexOf('nameservers') !== -1) {
            return 'Nameservers';
        }

        if (hash === '#tabreglock' || hash.indexOf('reglock') !== -1 || hash.indexOf('registrarlock') !== -1 || hash.indexOf('registrar-lock') !== -1) {
            return 'Registrar Lock';
        }

        if (hash === '#tabaddons' || hash.indexOf('addon') !== -1 || hash.indexOf('addons') !== -1) {
            return 'Addons';
        }

        if (hash === '#taboverview' || hash.indexOf('overview') !== -1) {
            return 'Overview';
        }

        return '';
    }

    function pageNameFromActiveTab() {
        var activeTab = document.querySelector('.nav-tabs li.active a[href^="#"], .nav-tabs a.active[href^="#"], li.active .tabControlLink[href^="#"], .tabControlLink.active[href^="#"]');
        var href = String(activeTab ? activeTab.getAttribute('href') || '' : '').toLowerCase();

        if (!href) {
            return '';
        }

        if (href.indexOf('tabautorenew') !== -1) {
            return 'Auto Renew';
        }

        if (href.indexOf('tabnameservers') !== -1) {
            return 'Nameservers';
        }

        if (href.indexOf('tabreglock') !== -1) {
            return 'Registrar Lock';
        }

        if (href.indexOf('tabaddons') !== -1) {
            return 'Addons';
        }

        if (href.indexOf('taboverview') !== -1) {
            return 'Overview';
        }

        return '';
    }

    function pageName() {
        var hashName = pageNameFromHash();
        var activeTabName = pageNameFromActiveTab();

        /*
         * The standard WHMCS Auto Renew / Nameservers / Registrar Lock /
         * Addons views are hash tabs on action=domaindetails. PHP correctly
         * maps action=domaindetails to Overview, so client-side hash/active-tab
         * detection must override that default.
         */
        if (hashName && mappedPageName === 'Overview') {
            return hashName;
        }

        if (activeTabName && mappedPageName === 'Overview') {
            return activeTabName;
        }

        return mappedPageName || hashName || activeTabName || discoverPageNameFromSideMenu() || discoverPageNameFromHeadings() || 'Overview';
    }

    function topTitle() {
        return 'Manage Domain: ' + pageName();
    }

    function isTopHeaderCandidate(el) {
        var text = normalizedLower(el ? el.textContent : '');

        if (!text || text.length > 90 || isSideMenu(el)) {
            return false;
        }

        return text === 'dashboard'
            || text === 'client area'
            || text.indexOf('domain management:') === 0
            || text.indexOf('manage domain:') === 0;
    }

    function replaceTopBannerTitle() {
        var candidates = document.querySelectorAll([
            'h1',
            'h2',
            '.page-title',
            '.page-header h1',
            '.main-title',
            '.banner-title',
            '.whmcs-page-title',
            '.header-lined h1',
            '.header-lined h2',
            '.breadcrumb-title',
            '.hero-title',
            '.entry-title',
            '.title'
        ].join(','));

        var i;

        for (i = 0; i < candidates.length; i += 1) {
            if (isTopHeaderCandidate(candidates[i])) {
                setText(candidates[i], topTitle());
                return true;
            }
        }

        /*
         * Module pages may render the top Dashboard label inside a generic
         * wrapper instead of a heading. Exact text only, page-content areas only.
         */
        candidates = document.querySelectorAll([
            '#main-body div',
            '#main-body span',
            '.main-content div',
            '.main-content span',
            '.contentarea div',
            '.contentarea span',
            '.page-wrapper div',
            '.page-wrapper span',
            '.page-content div',
            '.page-content span',
            'main div',
            'main span'
        ].join(','));

        for (i = 0; i < candidates.length; i += 1) {
            if (isTopHeaderCandidate(candidates[i])) {
                setText(candidates[i], topTitle());
                return true;
            }
        }

        return false;
    }

    function replaceSideMenuLabels() {
        var candidates = document.querySelectorAll('.panel-sidebar a, .sidebar a, aside a, .list-group a, .sidebar-menu a');
        var i;
        var lower;

        for (i = 0; i < candidates.length; i += 1) {
            lower = normalizedLower(candidates[i].textContent);

            if (sideMenuNameMap[lower]) {
                setText(candidates[i], sideMenuNameMap[lower]);
            }
        }
    }

    function isUnifiedConvertedPage() {
        var params;
        try {
            params = new URLSearchParams(window.location.search || '');
        } catch (error) {
            params = null;
        }

        return !!(
            (params && (params.get('dmconverted') === '1' || params.get('dmdesign') === '1'))
            || document.getElementById('dm-rc-unified-nav-1152')
            || (document.body && document.body.classList.contains('dm-rc-unified-menu-1152'))
        );
    }

    function replaceMainContentHeading() {
        var candidates;
        var i;
        var el;
        var text;
        var lower;

        if (!domainName || isUnifiedConvertedPage()) {
            return false;
        }

        candidates = document.querySelectorAll('h1, h2, h3, h4, .card-title, .panel-title, .section-title, .header-lined h1, .header-lined h2, .panel-heading, .card-header');

        for (i = 0; i < candidates.length; i += 1) {
            el = candidates[i];

            if (isSideMenu(el)) {
                continue;
            }

            text = normalize(el.textContent);
            lower = text.toLowerCase();

            if (!text || text === domainName || lower === 'dashboard' || lower.indexOf('domain management:') === 0 || lower.indexOf('manage domain:') === 0) {
                continue;
            }

            if (isKnownPageLabel(text)) {
                setText(el, domainName);
                return true;
            }
        }

        return false;
    }

    function applyLabels() {
        replaceSideMenuLabels();
        replaceTopBannerTitle();
        replaceMainContentHeading();
    }

    ready(function () {
        applyLabels();

        document.addEventListener('click', function (event) {
            var link = event.target && event.target.closest ? event.target.closest('a[href^="#tab"]') : null;

            if (link) {
                window.setTimeout(applyLabels, 60);
                window.setTimeout(applyLabels, 180);
            }
        }, true);
    });

    window.addEventListener('hashchange', function () {
        applyLabels();
        window.setTimeout(applyLabels, 120);
    });

    window.setTimeout(applyLabels, 150);
    window.setTimeout(applyLabels, 500);
    window.setTimeout(applyLabels, 1000);
    window.setTimeout(applyLabels, 1600);
}());
</script>
HTML;
});
