<?php
/**
 * DomainMonger WHMCS v9 - cPanel Zone Editor direct-domain SSO (Patch 1351)
 *
 * Creates a fresh temporary cPanel session for a client-owned active cPanel
 * service and sends the browser to Jupiter's Zone Editor. No session token is
 * stored or hard-coded.
 */

define('CLIENTAREA', true);
require_once __DIR__ . '/init.php';

use WHMCS\Database\Capsule;

function dm_cpanel_zone_1350_fail(string $message, int $serviceId = 0): void
{
    $returnUrl = 'clientarea.php?action=services';
    if ($serviceId > 0) {
        $returnUrl = 'clientarea.php?action=productdetails&id=' . $serviceId;
    }

    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $safeReturn = htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8');

    http_response_code(400);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>Zone Editor</title>'
        . '<style>body{margin:0;background:#f5f7f9;color:#273b50;font-family:Arial,sans-serif}'
        . '.dm-wrap{max-width:620px;margin:60px auto;padding:0 18px}.dm-card{background:#fff;border:1px solid #d7e0e8;border-radius:7px;overflow:hidden}'
        . '.dm-head{padding:14px 18px;background:#163a5f;color:#fff;font-size:18px;font-weight:700}'
        . '.dm-body{padding:22px 18px}.dm-button{display:inline-block;margin-top:14px;padding:9px 15px;border-radius:5px;background:#163a5f;color:#fff;text-decoration:none;font-weight:700}'
        . '.dm-button:hover{background:#214e7a}</style></head><body><div class="dm-wrap"><div class="dm-card">'
        . '<div class="dm-head">Zone Editor</div><div class="dm-body"><p>' . $safeMessage . '</p>'
        . '<a class="dm-button" href="' . $safeReturn . '">Return to Service</a></div></div></div></body></html>';
    exit;
}

function dm_cpanel_zone_1350_client_id(): int
{
    foreach (['uid', 'clientareauserid', 'clientid'] as $key) {
        if (!empty($_SESSION[$key])) {
            return (int) $_SESSION[$key];
        }
    }

    return 0;
}

function dm_cpanel_zone_1350_host(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    if (strpos($value, '://') === false) {
        $value = 'https://' . $value;
    }

    $host = parse_url($value, PHP_URL_HOST);
    return is_string($host) ? strtolower(trim($host)) : '';
}

$serviceId = isset($_GET['serviceid']) ? (int) $_GET['serviceid'] : 0;
$clientId = dm_cpanel_zone_1350_client_id();

if ($clientId < 1) {
    header('Location: clientarea.php', true, 302);
    exit;
}

if ($serviceId < 1) {
    dm_cpanel_zone_1350_fail('A valid cPanel service was not provided.');
}

try {
    $service = Capsule::table('tblhosting')
        ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
        ->where('tblhosting.id', $serviceId)
        ->where('tblhosting.userid', $clientId)
        ->whereRaw('LOWER(tblproducts.servertype) = ?', ['cpanel'])
        ->select(
            'tblhosting.id',
            'tblhosting.username',
            'tblhosting.domain',
            'tblhosting.domainstatus'
        )
        ->first();
} catch (Throwable $e) {
    $service = null;
}

if (!$service) {
    dm_cpanel_zone_1350_fail('This cPanel service was not found or is not available to the current account.', $serviceId);
}

if (strtolower((string) $service->domainstatus) !== 'active') {
    dm_cpanel_zone_1350_fail('Zone Editor is available only for an active cPanel service.', $serviceId);
}

require_once __DIR__ . '/includes/modulefunctions.php';
if (!function_exists('ModuleBuildParams')) {
    dm_cpanel_zone_1350_fail('WHMCS could not prepare the cPanel connection.', $serviceId);
}

try {
    $params = ModuleBuildParams($serviceId);
} catch (Throwable $e) {
    $params = [];
}

if (!is_array($params)) {
    $params = [];
}

$cpanelUser = trim((string) ($params['username'] ?? $service->username ?? ''));
$whmUser = trim((string) ($params['serverusername'] ?? ''));
$whmPassword = (string) ($params['serverpassword'] ?? '');
$whmAccessHash = preg_replace('/\s+/', '', (string) ($params['serveraccesshash'] ?? ''));
$serverHost = dm_cpanel_zone_1350_host((string) ($params['serverhostname'] ?? ''));
if ($serverHost === '') {
    $serverHost = dm_cpanel_zone_1350_host((string) ($params['serverip'] ?? ''));
}

if ($cpanelUser === '' || $whmUser === '' || $serverHost === '') {
    dm_cpanel_zone_1350_fail('The cPanel server connection is incomplete.', $serviceId);
}

$query = 'https://' . $serverHost . ':2087/json-api/create_user_session?'
    . http_build_query([
        'api.version' => 1,
        'user' => $cpanelUser,
        'service' => 'cpaneld',
    ], '', '&', PHP_QUERY_RFC3986);

$curl = curl_init($query);
if ($curl === false) {
    dm_cpanel_zone_1350_fail('WHMCS could not start the cPanel connection.', $serviceId);
}

$headers = ['Accept: application/json'];
if ($whmAccessHash !== '') {
    $headers[] = 'Authorization: WHM ' . $whmUser . ':' . $whmAccessHash;
} elseif ($whmPassword !== '') {
    $headers[] = 'Authorization: Basic ' . base64_encode($whmUser . ':' . $whmPassword);
} else {
    curl_close($curl);
    dm_cpanel_zone_1350_fail('No cPanel server authentication method is configured.', $serviceId);
}

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_HTTPHEADER => $headers,
]);

$responseBody = curl_exec($curl);
$curlError = curl_error($curl);
$httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if (!is_string($responseBody) || $responseBody === '' || $httpCode < 200 || $httpCode >= 300) {
    if (function_exists('logActivity')) {
        logActivity('DomainMonger Zone Editor SSO 1350 failed for service ' . $serviceId
            . ' (HTTP ' . $httpCode . ($curlError !== '' ? '; cURL error present' : '') . ').');
    }
    dm_cpanel_zone_1350_fail('cPanel did not return a usable temporary session.', $serviceId);
}

$decoded = json_decode($responseBody, true);
$sessionUrl = is_array($decoded) ? trim((string) ($decoded['data']['url'] ?? '')) : '';
$result = is_array($decoded) ? (int) ($decoded['metadata']['result'] ?? 0) : 0;

if ($result !== 1 || $sessionUrl === '' || stripos($sessionUrl, 'https://') !== 0) {
    $reason = is_array($decoded) ? trim((string) ($decoded['metadata']['reason'] ?? '')) : '';
    if (function_exists('logActivity')) {
        logActivity('DomainMonger Zone Editor SSO 1350 was rejected for service ' . $serviceId
            . ($reason !== '' ? ': ' . substr($reason, 0, 180) : '.'));
    }
    dm_cpanel_zone_1350_fail('cPanel could not create the temporary Zone Editor session.', $serviceId);
}

$sessionParts = parse_url($sessionUrl);
$sessionHost = strtolower((string) ($sessionParts['host'] ?? ''));
if ($sessionHost === '' || $sessionHost !== $serverHost) {
    dm_cpanel_zone_1350_fail('cPanel returned an unexpected session destination.', $serviceId);
}

// cPanel's login handler establishes the temporary browser session and
// honors goto_uri for the server-side Jupiter path. The #/manage route is a
// client-side fragment, so it is appended to the login URL and carried by the
// browser through cPanel's redirect.
$requestedDomain = strtolower(trim((string) ($_GET['domain'] ?? '')));
$requestedDomain = rtrim($requestedDomain, '.');
$serviceDomain = strtolower(trim((string) ($service->domain ?? '')));
$serviceDomain = rtrim($serviceDomain, '.');

$validHostname = static function (string $value): bool {
    return $value !== ''
        && strlen($value) <= 253
        && preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i', $value);
};

// Indicator links may provide a specific zone. cPanel still enforces that the
// authenticated account can access the requested zone. The service domain
// remains the default for the Quick Shortcuts tile.
$targetDomain = $validHostname($requestedDomain) ? $requestedDomain : $serviceDomain;
$openDirectly = $validHostname($targetDomain);

$separator = strpos($sessionUrl, '?') === false ? '?' : '&';
$zoneEditorLoginUrl = $sessionUrl . $separator
    . 'goto_uri=' . rawurlencode('frontend/jupiter/zone_editor/index.html');

if ($openDirectly) {
    $zoneEditorLoginUrl .= '#/manage?domain=' . rawurlencode($targetDomain);
} else {
    $zoneEditorLoginUrl .= '#/list';
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Location: ' . $zoneEditorLoginUrl, true, 302);
exit;
