<?php
/**
 * DomainMonger Patch 1281
 *
 * Announcements / Knowledgebase redirect-loop guard.
 *
 * WHMCS applies its Friendly URL system to these support pages. On an
 * integrated staging installation, a mismatch between the request host,
 * configured System URL, and Friendly URL path mode can cause the request to
 * bounce between equivalent URLs until the browser stops it.
 *
 * This compatibility guard is deliberately request-local and page-specific:
 * - Detects Announcements and Knowledgebase listing/article/category routes.
 * - Uses the host and scheme that successfully reached PHP for this request.
 * - Uses WHMCS Basic URL routing for these two support sections only.
 * - Does not update tblconfiguration or change any site-wide setting.
 * - Does not affect domain, cart, billing, ticket, or module pages.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

$dmSupportScript1281 = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
$dmSupportRequestUri1281 = (string) ($_SERVER['REQUEST_URI'] ?? '');
$dmSupportRequestPath1281 = strtolower((string) (parse_url($dmSupportRequestUri1281, PHP_URL_PATH) ?? ''));
$dmSupportRoute1281 = strtolower(trim((string) ($_GET['rp'] ?? '')));

$dmIsSupportRoute1281 = in_array($dmSupportScript1281, ['announcements.php', 'knowledgebase.php'], true)
    || preg_match('~/(?:announcements|knowledgebase)(?:\.php|/|$)~i', $dmSupportRequestPath1281)
    || preg_match('~^/?(?:announcements|knowledgebase)(?:/|$)~i', $dmSupportRoute1281);

if (!$dmIsSupportRoute1281 || !isset($GLOBALS['CONFIG']) || !is_array($GLOBALS['CONFIG'])) {
    return;
}

$dmForwardedProto1281 = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
$dmIsHttps1281 = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
    || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443')
    || ($dmForwardedProto1281 === 'https');
$dmScheme1281 = $dmIsHttps1281 ? 'https' : 'http';

$dmHost1281 = trim((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
$dmHost1281 = preg_replace('/[^a-z0-9.\-:\[\]]/i', '', $dmHost1281);

$dmScriptName1281 = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/manage/index.php'));
$dmBasePath1281 = rtrim(str_replace('\\', '/', dirname($dmScriptName1281)), '/');
if ($dmBasePath1281 === '.' || $dmBasePath1281 === '/') {
    $dmBasePath1281 = '';
}

if ($dmHost1281 !== '') {
    $dmCurrentSystemUrl1281 = $dmScheme1281 . '://' . $dmHost1281 . $dmBasePath1281 . '/';

    // Keep these two requests on the origin that actually reached WHMCS.
    $GLOBALS['CONFIG']['SystemURL'] = $dmCurrentSystemUrl1281;
    if ($dmIsHttps1281) {
        $GLOBALS['CONFIG']['SystemSSLURL'] = $dmCurrentSystemUrl1281;
    }
}

// Bypass conflicting full-friendly canonicalization for these support routes.
$GLOBALS['CONFIG']['RouteUriPathMode'] = 'basic';
$GLOBALS['CONFIG']['SEOFriendlyUrls'] = '';
