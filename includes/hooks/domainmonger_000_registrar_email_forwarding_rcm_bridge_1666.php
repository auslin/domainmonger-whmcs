<?php
/**
 * DomainMonger Patch 1841 (EF1666 core)
 * Registrar Email Forwarding: preserve the confirmed destination-edit and
 * safe source-move behavior, allow multiple source moves to run sequentially,
 * and add page-specific field sizing, multi-destination/deletion guidance,
 * instant direct-API catch-all status and inline catch-all controls, including
 * verified catch-all deactivation.
 *
 * Destination changes continue using the exact source account and destination
 * address through emailmanagement.php?action=modifyaccount (Patch 1652's
 * confirmed behavior).
 *
 * A source-address change is intentionally not performed through fetch(), a
 * reconstructed registrar request, or an RCM account-delete route. Instead:
 * 1. restore the edited row to its original source and submit the replacement
 *    through the page's native new-forward fields using a normal browser POST;
 * 2. after the native page reloads and shows the replacement, clear the exact
 *    original source row and perform a second normal browser POST;
 * 3. after the next reload, verify the original is gone and the replacement
 *    remains.
 *
 * The staged state is keyed by exact source email addresses. DNS records, DNS
 * sorting, dm1487 identities, DNS record IDs, numeric table positions, and
 * reordered DOM rows are never used as identities.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm_registrar_email_rcm_bridge_1666_is_page')) {
    function dm_registrar_email_rcm_bridge_1666_is_page(): bool
    {
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));

        if ($script !== 'clientarea.php' && strpos($uri, '/manage/clientarea.php') === false) {
            return false;
        }

        return $action === 'domainemailforwarding'
            || strpos($uri, 'action=domainemailforwarding') !== false;
    }
}

if (!function_exists('dm_registrar_email_rcm_bridge_1666_context')) {
    function dm_registrar_email_rcm_bridge_1666_context(): array
    {
        $clientId = (int) ($_SESSION['uid'] ?? $_SESSION['clientid'] ?? 0);
        $domainId = (int) ($_REQUEST['domainid'] ?? $_REQUEST['id'] ?? 0);

        if ($clientId <= 0 || $domainId <= 0) {
            return [];
        }

        try {
            $row = Capsule::table('tbldomains')
                ->where('id', $domainId)
                ->where('userid', $clientId)
                ->first();
        } catch (Throwable $exception) {
            $row = null;
        }

        if (!$row) {
            return [];
        }

        $registrar = strtolower(trim((string) ($row->registrar ?? '')));
        if (!preg_match('/(resellerclub|netearth|stargate|logicboxes)/i', $registrar)) {
            return [];
        }

        return [
            'domainId' => (int) $row->id,
            'domain' => strtolower(rtrim(trim((string) $row->domain), '.')),
            'registrar' => $registrar,
            'domainRow' => $row,
            'handler' => 'EF1666',
        ];
    }
}

if (!function_exists('dm_registrar_email_catchall_1666_json')) {
    function dm_registrar_email_catchall_1666_json(array $payload, int $statusCode = 200): void
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('dm_registrar_email_catchall_1666_token')) {
    function dm_registrar_email_catchall_1666_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_start();
        }
        $key = 'dm_registrar_email_catchall_token_1666';
        if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
            try {
                $_SESSION[$key] = bin2hex(random_bytes(32));
            } catch (Throwable $exception) {
                $_SESSION[$key] = hash('sha256', uniqid('dm_email_catchall_1666_', true));
            }
        }
        return (string) $_SESSION[$key];
    }
}

if (!function_exists('dm_registrar_email_catchall_1666_load_helpers')) {
    function dm_registrar_email_catchall_1666_load_helpers(): bool
    {
        if (function_exists('dm_epp_find_credentials')
            && function_exists('dm_epp_api_base')
            && function_exists('dm_epp_http_request')
            && function_exists('dm_epp_get_order_id')) {
            return true;
        }
        $helper = __DIR__ . '/domainmonger_epp_authcode_manager.php';
        if (is_file($helper)) {
            require_once $helper;
        }
        return function_exists('dm_epp_find_credentials')
            && function_exists('dm_epp_api_base')
            && function_exists('dm_epp_http_request')
            && function_exists('dm_epp_get_order_id');
    }
}

if (!function_exists('dm_registrar_email_catchall_1666_extract')) {
    function dm_registrar_email_catchall_1666_extract($value, string $domain): string
    {
        $domain = strtolower(rtrim(trim($domain), '.'));
        if (is_array($value)) {
            $preferred = ['catchall-email', 'catchall_email', 'catchallemail', 'catchallEmail', 'emailAddress', 'email'];
            foreach ($preferred as $key) {
                if (array_key_exists($key, $value)) {
                    $found = dm_registrar_email_catchall_1666_extract($value[$key], $domain);
                    if ($found !== '') {
                        return $found;
                    }
                }
            }
            foreach ($value as $item) {
                $found = dm_registrar_email_catchall_1666_extract($item, $domain);
                if ($found !== '') {
                    return $found;
                }
            }
            return '';
        }
        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }
        $text = html_entity_decode(trim((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
            $email = strtolower($text);
            return substr($email, -strlen('@' . $domain)) === '@' . $domain ? $email : '';
        }
        if (preg_match('/[A-Z0-9._%+\-]+@' . preg_quote($domain, '/') . '/i', $text, $match)) {
            return strtolower($match[0]);
        }
        return '';
    }
}

if (!function_exists('dm_registrar_email_catchall_1666_no_active_error')) {
    function dm_registrar_email_catchall_1666_no_active_error(string $message): bool
    {
        $message = strtolower($message);
        foreach (['not active', 'not activated', 'not found', 'does not exist', 'no catch', 'not configured', 'no such'] as $needle) {
            if (strpos($message, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('dm_registrar_email_catchall_1666_api_context')) {
    function dm_registrar_email_catchall_1666_api_context(array $context): array
    {
        if (!dm_registrar_email_catchall_1666_load_helpers()) {
            return [false, [], '', '', 'The registrar API helper functions were unavailable.'];
        }
        $credentials = dm_epp_find_credentials((string) ($context['registrar'] ?? ''));
        if (trim((string) ($credentials['authUserId'] ?? '')) === ''
            || trim((string) ($credentials['apiKey'] ?? '')) === '') {
            return [false, [], '', '', 'The registrar API credentials could not be resolved.'];
        }
        [$orderId, $orderSource] = dm_epp_get_order_id($context['domainRow'] ?? null, $credentials);
        if ($orderId === '') {
            return [false, [], '', '', $orderSource ?: 'The registrar order ID could not be resolved.'];
        }
        $base = rtrim((string) dm_epp_api_base($credentials), '/');
        return [true, $credentials, (string) $orderId, $base, ''];
    }
}

if (!function_exists('dm_registrar_email_catchall_1666_cache_key')) {
    function dm_registrar_email_catchall_1666_cache_key(int $domainId): string
    {
        return 'dm_registrar_email_catchall_status_1666_' . $domainId;
    }
}

if (!function_exists('dm_registrar_email_catchall_1666_status')) {
    function dm_registrar_email_catchall_1666_status(array $context, bool $allowCache = true): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_start();
        }
        $cacheKey = dm_registrar_email_catchall_1666_cache_key((int) ($context['domainId'] ?? 0));
        if ($allowCache && !empty($_SESSION[$cacheKey]) && is_array($_SESSION[$cacheKey])) {
            $cached = $_SESSION[$cacheKey];
            if (!empty($cached['time']) && time() - (int) $cached['time'] <= 60) {
                return [true, (string) ($cached['source'] ?? ''), ''];
            }
        }

        [$ready, $credentials, $orderId, $base, $error] = dm_registrar_email_catchall_1666_api_context($context);
        if (!$ready) {
            return [false, '', $error];
        }
        [$ok, $response, $requestError] = dm_epp_http_request('GET', $base . '/mail/domain/catchall.json', [
            'auth-userid' => (string) ($credentials['authUserId'] ?? ''),
            'api-key' => (string) ($credentials['apiKey'] ?? ''),
            'order-id' => $orderId,
        ]);
        if (!$ok) {
            if (dm_registrar_email_catchall_1666_no_active_error((string) $requestError)) {
                $_SESSION[$cacheKey] = ['time' => time(), 'source' => ''];
                return [true, '', ''];
            }
            return [false, '', $requestError ?: 'The catch-all status could not be read.'];
        }
        $source = dm_registrar_email_catchall_1666_extract($response, (string) ($context['domain'] ?? ''));
        $_SESSION[$cacheKey] = ['time' => time(), 'source' => $source];
        return [true, $source, ''];
    }
}

if (dm_registrar_email_rcm_bridge_1666_is_page()
    && strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
    && in_array((string) ($_POST['dm_email_catchall_1666'] ?? ''), ['set', 'disable'], true)) {
    $catchallAction = (string) ($_POST['dm_email_catchall_1666'] ?? '');
    $context = dm_registrar_email_rcm_bridge_1666_context();
    if (!$context) {
        dm_registrar_email_catchall_1666_json(['success' => false, 'message' => 'The domain could not be found for this client account.'], 404);
    }
    $expectedToken = dm_registrar_email_catchall_1666_token();
    $submittedToken = (string) ($_POST['dm_email_catchall_token_1666'] ?? '');
    if ($submittedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
        dm_registrar_email_catchall_1666_json(['success' => false, 'message' => 'The catch-all security token was invalid or expired. Refresh the page and retry.'], 403);
    }

    $source = strtolower(trim(substr((string) ($_POST['catchall_source'] ?? ''), 0, 320)));
    $domain = (string) ($context['domain'] ?? '');
    if ($catchallAction === 'set'
        && (!filter_var($source, FILTER_VALIDATE_EMAIL)
        || substr($source, -strlen('@' . $domain)) !== '@' . $domain)) {
        dm_registrar_email_catchall_1666_json(['success' => false, 'message' => 'Select a valid forwarding address from this domain.'], 422);
    }

    if ($catchallAction === 'disable') {
        [$currentStatusOk, $currentSource] = dm_registrar_email_catchall_1666_status($context, false);
        if ($currentStatusOk && $currentSource === '') {
            $_SESSION[dm_registrar_email_catchall_1666_cache_key((int) $context['domainId'])] = [
                'time' => time(),
                'source' => '',
            ];
            dm_registrar_email_catchall_1666_json([
                'success' => true,
                'source' => '',
                'message' => 'Catch-all was already disabled.',
            ]);
        }
    }

    [$ready, $credentials, $orderId, $base, $error] = dm_registrar_email_catchall_1666_api_context($context);
    if (!$ready) {
        dm_registrar_email_catchall_1666_json(['success' => false, 'message' => $error], 500);
    }
    $requestParameters = [
        'auth-userid' => (string) ($credentials['authUserId'] ?? ''),
        'api-key' => (string) ($credentials['apiKey'] ?? ''),
        'order-id' => $orderId,
    ];
    $endpoint = '/mail/domain/deactivate-catchall.json';
    if ($catchallAction === 'set') {
        $endpoint = '/mail/domain/activate-catchall.json';
        $requestParameters['catchall-email'] = $source;
    }
    [$ok, $response, $requestError] = dm_epp_http_request('POST', $base . $endpoint, $requestParameters);
    if (!$ok) {
        if ($catchallAction !== 'disable'
            || !dm_registrar_email_catchall_1666_no_active_error((string) $requestError)) {
            dm_registrar_email_catchall_1666_json([
                'success' => false,
                'message' => $requestError ?: ($catchallAction === 'disable'
                    ? 'The registrar did not disable the catch-all address.'
                    : 'The registrar did not update the catch-all address.'),
            ], 502);
        }
    }

    $verified = false;
    $lastSource = '';
    foreach ([0, 250, 600, 1100, 1800] as $delayMs) {
        if ($delayMs > 0) {
            usleep($delayMs * 1000);
        }
        [$statusOk, $statusSource] = dm_registrar_email_catchall_1666_status($context, false);
        if ($statusOk) {
            $lastSource = $statusSource;
            if (($catchallAction === 'set' && $statusSource === $source)
                || ($catchallAction === 'disable' && $statusSource === '')) {
                $verified = true;
                break;
            }
        }
    }
    if (!$verified) {
        dm_registrar_email_catchall_1666_json([
            'success' => false,
            'message' => $catchallAction === 'disable'
                ? 'The registrar accepted the catch-all disable request, but deactivation could not be confirmed. Current reported address: ' . ($lastSource !== '' ? $lastSource : 'none') . '.'
                : 'The registrar accepted the catch-all request, but the selected address could not be confirmed. Current reported address: ' . ($lastSource !== '' ? $lastSource : 'none') . '.',
        ], 502);
    }
    $confirmedSource = $catchallAction === 'disable' ? '' : $source;
    $_SESSION[dm_registrar_email_catchall_1666_cache_key((int) $context['domainId'])] = [
        'time' => time(),
        'source' => $confirmedSource,
    ];
    dm_registrar_email_catchall_1666_json([
        'success' => true,
        'source' => $confirmedSource,
        'message' => $catchallAction === 'disable'
            ? 'Catch-all disabled and verified.'
            : 'Catch-all updated and verified.',
    ]);
}

/*
 * Disable every earlier experimental Email Forwarding submit interceptor before
 * footer scripts execute. This also protects against a stale OPcache copy of an
 * older hook while Patch 1666 loads from its new filename.
 */
add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_registrar_email_rcm_bridge_1666_is_page()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-registrar-email-rcm-bridge-suppress-old-1666">
window.dmRegistrarEmailDirectModify1641Installed=true;
window.dmRegistrarEmailDirectModify1642Installed=true;
window.dmRegistrarEmailDirectModify1643Installed=true;
window.dmRegistrarEmailDirectModify1644Installed=true;
window.dmRegistrarEmailDirectModify1645Installed=true;
window.dmRegistrarEmailDirectModify1646Installed=true;
window.dmRegistrarEmailDestinationModify1647Installed=true;
window.dmRegistrarEmailDestinationModify1648Installed=true;
window.dmRegistrarEmailMultiDestinationModify1649Installed=true;
window.dmRegistrarEmailMultiDestinationModify1650Installed=true;
window.dmRegistrarEmailMultiDestinationModify1651Installed=true;
window.dmRegistrarEmailRcmBridge1652Installed=true;
window.dmRegistrarEmailRcmBridge1653Installed=true;
window.dmRegistrarEmailRcmBridge1654Installed=true;
window.dmRegistrarEmailRcmBridge1655Installed=true;
window.dmRegistrarEmailRcmBridge1656Installed=true;
window.dmRegistrarEmailRcmBridge1657Installed=true;
window.dmRegistrarEmailRcmBridge1658Installed=true;
window.dmRegistrarEmailRcmBridge1659Installed=true;
window.dmRegistrarEmailRcmBridge1660Installed=true;
window.dmRegistrarEmailRcmBridge1661Installed=true;
</script>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_registrar_email_rcm_bridge_1666_is_page()) {
        return '';
    }

    $context = dm_registrar_email_rcm_bridge_1666_context();
    if (!$context || empty($context['domainId']) || empty($context['domain'])) {
        return '';
    }

    [$catchallStatusOk, $catchallSource] = dm_registrar_email_catchall_1666_status($context, true);
    $clientContext = [
        'domainId' => (int) $context['domainId'],
        'domain' => (string) $context['domain'],
        'handler' => 'EF1666',
        'catchallToken' => dm_registrar_email_catchall_1666_token(),
        'catchallSource' => $catchallStatusOk ? (string) $catchallSource : '',
        'catchallStatusAvailable' => (bool) $catchallStatusOk,
    ];
    $config = json_encode($clientContext, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return '<style id="dm-registrar-email-rcm-bridge-css-1666">'
        . '#dm-registrar-email-rcm-message-1666{display:none;width:100%;margin:0 0 14px}'
        . '#dm-registrar-email-rcm-message-1666.dm-show-1666{display:block}'
        . '#dm-registrar-email-rcm-message-1666 strong{display:block;margin-bottom:3px}'
        . '.dm-ef-source-cell-1666{width:40%!important;min-width:345px!important}'
        . '.dm-ef-bridge-cell-1666{display:none!important;width:0!important;padding:0!important;border:0!important}'
        . '.dm-ef-destination-cell-1666{width:60%!important}'
        . '.dm-ef-source-wrap-1666{display:flex!important;align-items:center!important;gap:7px!important;flex-wrap:nowrap!important;width:100%!important;white-space:nowrap!important}'
        . '.dm-ef-source-wrap-1666 input[name^="emailforwarderprefix["],.dm-ef-source-wrap-1666 input[name="emailforwarderprefixnew"]{flex:0 0 25ch!important;width:25ch!important;max-width:25ch!important;min-width:12ch!important}'
        . '.dm-ef-source-suffix-1666{display:inline-flex!important;align-items:center!important;flex:0 0 auto!important;white-space:nowrap!important;color:#163a5f!important;font-weight:500!important}'
        . '.dm-ef-destination-wrap-1666{display:flex!important;align-items:center!important;gap:8px!important;width:100%!important}'
        . '.dm-ef-destination-wrap-1666 input[name^="emailforwarderforwardto["],.dm-ef-destination-wrap-1666 input[name="emailforwarderforwardtonew"]{flex:1 1 auto!important;width:auto!important;max-width:none!important;min-width:24ch!important}'
        . '.dm-ef-remove-1666{display:inline-flex!important;align-items:center!important;justify-content:center!important;flex:0 0 auto!important;min-height:34px!important;padding:6px 10px!important;border:1px solid #b94a48!important;border-radius:4px!important;background:#b94a48!important;color:#fff!important;font-weight:600!important;line-height:1.2!important;white-space:nowrap!important;opacity:1!important;visibility:visible!important}'
        . '.dm-ef-remove-1666:hover,.dm-ef-remove-1666:focus{background:#a94442!important;border-color:#a94442!important;color:#fff!important}'
        . '.dm-ef-remove-pending-1666 td{background:#fff8e6!important}'
        . '.dm-ef-help-1666{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-top:9px;padding-top:9px;border-top:1px solid rgba(22,58,95,.18)}'
        . '.dm-ef-help-copy-1666{display:flex;flex-direction:column;gap:4px;min-width:300px}'
        . '.dm-ef-catchall-line-1666{display:flex;align-items:center;gap:7px;flex-wrap:wrap}'
        . '.dm-ef-catchall-badge-1666{display:inline-flex!important;align-items:center!important;flex:0 0 auto!important;min-height:22px!important;padding:3px 8px!important;border-radius:999px!important;background:#2f7d4a!important;color:#fff!important;font-size:12px!important;font-weight:700!important;line-height:1!important;white-space:nowrap!important}'
        . '.dm-ef-catchall-row-1666 td:first-child{box-shadow:inset 4px 0 0 #2f7d4a!important}'
        . '.dm-ef-catchall-buttons-1666{display:flex;align-items:center;gap:8px;flex-wrap:wrap}'
        . '.dm-ef-catchall-action-1666,.dm-ef-catchall-action-1666:visited{display:inline-flex!important;align-items:center!important;justify-content:center!important;min-height:34px!important;padding:6px 12px!important;border:1px solid #163a5f!important;border-radius:4px!important;background:#163a5f!important;color:#fff!important;text-decoration:none!important;font-weight:600!important;line-height:1.2!important;white-space:nowrap!important;opacity:1!important;visibility:visible!important;filter:none!important;text-shadow:none!important}'
        . '.dm-ef-catchall-action-1666:hover,.dm-ef-catchall-action-1666:focus{background:#214e7a!important;border-color:#214e7a!important;color:#fff!important;text-decoration:none!important;opacity:1!important}'
        . '.dm-ef-catchall-disable-1666{display:inline-flex!important;align-items:center!important;justify-content:center!important;min-height:34px!important;padding:6px 12px!important;border:1px solid #b94a48!important;border-radius:4px!important;background:#b94a48!important;color:#fff!important;font-weight:600!important;line-height:1.2!important;white-space:nowrap!important}'
        . '.dm-ef-catchall-disable-1666:hover,.dm-ef-catchall-disable-1666:focus{border-color:#a94442!important;background:#a94442!important;color:#fff!important}'
        . '.dm-ef-catchall-disable-1666[hidden]{display:none!important}'
        . '.dm-ef-catchall-panel-1666{display:none;align-items:center;gap:8px;flex-wrap:wrap;width:100%;margin-top:8px;padding:10px;border:1px solid #b8cbe0;border-radius:4px;background:#f7fbff}'
        . '.dm-ef-catchall-panel-1666.dm-open-1666{display:flex}'
        . '.dm-ef-catchall-panel-1666 label{margin:0;color:#163a5f;font-weight:700}'
        . '.dm-ef-catchall-select-1666{flex:1 1 300px;min-width:220px;max-width:560px;height:34px;border:1px solid #b8c3cf;border-radius:4px;background:#fff;color:#163a5f;padding:6px 9px}'
        . '.dm-ef-catchall-save-1666,.dm-ef-catchall-cancel-1666{display:inline-flex;align-items:center;justify-content:center;min-height:34px;padding:6px 12px;border-radius:4px;color:#fff!important;font-weight:600;white-space:nowrap}'
        . '.dm-ef-catchall-save-1666{border:1px solid #f58220;background:#f58220}.dm-ef-catchall-save-1666:hover,.dm-ef-catchall-save-1666:focus{border-color:#d8741f;background:#d8741f}'
        . '.dm-ef-catchall-cancel-1666{border:1px solid #163a5f;background:#163a5f}.dm-ef-catchall-cancel-1666:hover,.dm-ef-catchall-cancel-1666:focus{border-color:#214e7a;background:#214e7a}'
        . '@media(max-width:900px){.dm-ef-source-cell-1666{min-width:0!important}.dm-ef-source-wrap-1666{flex-wrap:wrap!important}.dm-ef-source-wrap-1666 input[name^="emailforwarderprefix["],.dm-ef-source-wrap-1666 input[name="emailforwarderprefixnew"]{flex:1 1 18ch!important;width:18ch!important;max-width:25ch!important}.dm-ef-source-suffix-1666{font-size:12px!important}}'
        . '@media(max-width:767px){.dm-ef-source-cell-1666,.dm-ef-destination-cell-1666{width:auto!important;min-width:0!important}.dm-ef-source-wrap-1666{display:flex!important;flex-wrap:wrap!important}.dm-ef-source-wrap-1666 input[name^="emailforwarderprefix["],.dm-ef-source-wrap-1666 input[name="emailforwarderprefixnew"],.dm-ef-destination-wrap-1666 input[name^="emailforwarderforwardto["],.dm-ef-destination-wrap-1666 input[name="emailforwarderforwardtonew"]{width:100%!important;max-width:100%!important;min-width:0!important}.dm-ef-destination-wrap-1666{align-items:stretch!important}.dm-ef-help-1666{align-items:stretch!important}.dm-ef-catchall-buttons-1666{width:100%!important}.dm-ef-catchall-action-1666,.dm-ef-catchall-disable-1666{width:100%!important}}'
        . '</style>'
        . '<script id="dm-registrar-email-rcm-bridge-config-1666">window.dmRegistrarEmailRcmBridge1666Config=' . $config . ';</script>'
        . <<<'HTML'
<script id="dm-registrar-email-rcm-bridge-js-1666">
(function () {
    'use strict';

    if (window.dmRegistrarEmailRcmBridge1666Installed) {
        return;
    }
    window.dmRegistrarEmailRcmBridge1666Installed = true;

    var config = window.dmRegistrarEmailRcmBridge1666Config || {};
    var busy = false;
    var moduleContextPromise = null;

    function text(value) {
        return String(value == null ? '' : value).replace(/\r\n/g, '\n').trim();
    }

    function normalizeEmail(value) {
        return text(value).replace(/^mailto:/i, '').replace(/^["']+|["']+$/g, '').toLowerCase();
    }

    function normalizePrefix(value) {
        value = text(value).replace(/[\u200B-\u200D\u2060\uFEFF]/g, '');
        if (value.indexOf('@') !== -1) {
            value = value.split('@')[0];
        }
        return value.toLowerCase();
    }

    function parseDestinations(value) {
        var raw = text(value);
        if (!raw) {
            return { valid: true, values: [], invalid: [] };
        }

        var values = [];
        var invalid = [];
        var seen = Object.create(null);
        raw.split(/[\s,;]+/).forEach(function (part) {
            part = text(part);
            if (!part) {
                return;
            }
            var email = normalizeEmail(part);
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                invalid.push(part);
                return;
            }
            if (!seen[email]) {
                seen[email] = true;
                values.push(email);
            }
        });

        return { valid: invalid.length === 0 && values.length > 0, values: values, invalid: invalid };
    }

    function unique(values) {
        var out = [];
        var seen = Object.create(null);
        values.forEach(function (value) {
            value = normalizeEmail(value);
            if (value && !seen[value]) {
                seen[value] = true;
                out.push(value);
            }
        });
        return out;
    }

    function difference(left, right) {
        var rightMap = Object.create(null);
        right.forEach(function (value) { rightMap[normalizeEmail(value)] = true; });
        return unique(left).filter(function (value) { return !rightMap[value]; });
    }

    function containsAll(haystack, needles) {
        var map = Object.create(null);
        haystack.forEach(function (value) { map[normalizeEmail(value)] = true; });
        return needles.every(function (value) { return !!map[normalizeEmail(value)]; });
    }

    function containsNone(haystack, needles) {
        var map = Object.create(null);
        haystack.forEach(function (value) { map[normalizeEmail(value)] = true; });
        return needles.every(function (value) { return !map[normalizeEmail(value)]; });
    }

    function sleep(ms) {
        return new Promise(function (resolve) { window.setTimeout(resolve, ms); });
    }

    function findNativeForm() {
        var forms = document.querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) {
            if (forms[i].querySelector('input[name="sub"][value="save"]')
                && forms[i].querySelector('input[name="domainid"]')
                && forms[i].querySelector('input[name^="emailforwarderprefix["]')) {
                return forms[i];
            }
        }
        return null;
    }

    function existingRows(form) {
        var rows = [];
        var tableRows = form.querySelectorAll('tbody tr');
        for (var i = 0; i < tableRows.length; i++) {
            var prefix = tableRows[i].querySelector('input[name^="emailforwarderprefix["]');
            var destinations = tableRows[i].querySelector('input[name^="emailforwarderforwardto["]');
            if (!prefix || !destinations) {
                continue;
            }
            rows.push({ row: tableRows[i], prefix: prefix, destinations: destinations });
        }
        return rows;
    }

    function sameSet(left, right) {
        var a = unique(left).sort();
        var b = unique(right).sort();
        if (a.length !== b.length) {
            return false;
        }
        for (var i = 0; i < a.length; i++) {
            if (a[i] !== b[i]) {
                return false;
            }
        }
        return true;
    }

    function fullSource(prefix) {
        var local = normalizePrefix(prefix);
        var domain = String(config.domain || '').toLowerCase();
        var email = local && domain ? local + '@' + domain : '';
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) ? email : '';
    }

    function originalState(form) {
        var state = Object.create(null);
        existingRows(form).forEach(function (item) {
            var source = fullSource(item.prefix.dataset.dmEf1666OriginalPrefix || item.prefix.defaultValue || item.prefix.value);
            var parsed = parseDestinations(item.destinations.dataset.dmEf1666OriginalDestinations || item.destinations.defaultValue || item.destinations.value);
            if (source && parsed.valid) {
                state[source] = { item: item, destinations: parsed.values };
            }
        });
        return state;
    }

    function renderedState(form) {
        var state = Object.create(null);
        existingRows(form).forEach(function (item) {
            var source = fullSource(item.prefix.value);
            var parsed = parseDestinations(item.destinations.value);
            if (source && parsed.valid) {
                state[source] = { item: item, destinations: parsed.values };
            }
        });
        return state;
    }

    function pendingKey() {
        return 'dmRegistrarEmailSourceMove1666:' + String(config.domainId || '') + ':' + String(config.domain || '').toLowerCase();
    }

    function readPending() {
        var raw = '';
        try {
            raw = window.sessionStorage.getItem(pendingKey()) || '';
        } catch (error) {
            return null;
        }
        if (!raw) {
            return null;
        }
        try {
            var value = JSON.parse(raw);
            if (!value || value.version !== 1666 || value.domainId !== String(config.domainId)
                || value.domain !== String(config.domain).toLowerCase()) {
                window.sessionStorage.removeItem(pendingKey());
                return null;
            }
            if (!value.started || Date.now() - value.started > 20 * 60 * 1000) {
                window.sessionStorage.removeItem(pendingKey());
                return null;
            }
            return value;
        } catch (error) {
            try { window.sessionStorage.removeItem(pendingKey()); } catch (ignored) {}
            return null;
        }
    }

    function writePending(value) {
        value.version = 1666;
        value.domainId = String(config.domainId);
        value.domain = String(config.domain).toLowerCase();
        value.started = value.started || Date.now();
        window.sessionStorage.setItem(pendingKey(), JSON.stringify(value));
    }

    function clearPending() {
        try { window.sessionStorage.removeItem(pendingKey()); } catch (error) {}
    }

    function restoreOriginalRows(form) {
        existingRows(form).forEach(function (item) {
            item.prefix.value = item.prefix.dataset.dmEf1666OriginalPrefix || item.prefix.defaultValue || '';
            item.destinations.value = item.destinations.dataset.dmEf1666OriginalDestinations || item.destinations.defaultValue || '';
        });
    }

    function clearNativeNewRow(form) {
        var prefix = form.querySelector('input[name="emailforwarderprefixnew"]');
        var destinations = form.querySelector('input[name="emailforwarderforwardtonew"]');
        if (prefix) { prefix.value = ''; }
        if (destinations) { destinations.value = ''; }
    }

    function submitNativePage(form) {
        form.dataset.dmEmailRcmBridge1666NativeSubmit = '1';

        /* A direct form.submit() skips every submit listener. The confirmed
         * manual whole-forward deletion depends on the ordinary browser submit
         * pipeline, so use requestSubmit() with a temporary enabled submitter.
         * Our own listener sees the native-submit marker and steps aside while
         * WHMCS and any page-level normalization handlers still run. */
        if (typeof form.requestSubmit === 'function') {
            var submitter = document.createElement('button');
            submitter.type = 'submit';
            submitter.hidden = true;
            submitter.tabIndex = -1;
            submitter.setAttribute('aria-hidden', 'true');
            form.appendChild(submitter);
            form.requestSubmit(submitter);
            return;
        }

        /* Older-browser fallback. Modern supported browsers use the path
         * above; this fallback preserves existing behavior only when the
         * standards-based submission API is unavailable. */
        window.HTMLFormElement.prototype.submit.call(form);
    }

    function pageDangerText() {
        var alerts = document.querySelectorAll('.alert-danger');
        var values = [];
        for (var i = 0; i < alerts.length; i++) {
            var value = cleanAlertText(alerts[i]);
            if (value && !/password length between 8 and 64 characters/i.test(value)) {
                values.push(value);
            }
        }
        return values.join(' ');
    }

    function messageBox(form) {
        var box = document.getElementById('dm-registrar-email-rcm-message-1666');
        if (!box) {
            box = document.createElement('div');
            box.id = 'dm-registrar-email-rcm-message-1666';
            box.setAttribute('role', 'status');
            form.parentNode.insertBefore(box, form);
        }
        return box;
    }

    function show(form, type, title, message) {
        var box = messageBox(form);
        box.className = 'alert alert-' + type + ' dm-show-1666';
        box.innerHTML = '';
        var strong = document.createElement('strong');
        strong.textContent = title;
        box.appendChild(strong);
        box.appendChild(document.createTextNode(message));
    }

    function setBusy(form, state) {
        busy = state;
        var controls = form.querySelectorAll('input[type="submit"],button[type="submit"],input[type="reset"],button[type="reset"]');
        for (var i = 0; i < controls.length; i++) {
            controls[i].disabled = state;
            controls[i].setAttribute('aria-disabled', state ? 'true' : 'false');
        }
    }

    function moduleUrl(action) {
        var url = new URL('emailmanagement.php', window.location.href);
        if (action) {
            url.searchParams.set('action', action);
        }
        return url.toString();
    }

    function parseHtml(html) {
        return new DOMParser().parseFromString(html, 'text/html');
    }

    function loginPage(doc) {
        return !!doc.querySelector('form[action*="login"] input[name="username"], input[name="password"][autocomplete="current-password"]');
    }

    function cleanAlertText(node) {
        return text(node ? node.textContent : '').replace(/\s+/g, ' ');
    }

    async function fetchDocument(url, options) {
        options = options || {};
        options.credentials = 'same-origin';
        options.cache = 'no-store';
        options.redirect = 'follow';
        options.headers = options.headers || {};
        if (!options.headers.Accept) {
            options.headers.Accept = 'text/html,application/xhtml+xml';
        }

        var response = await fetch(url, options);
        var html = await response.text();
        var doc = parseHtml(html);

        if (!response.ok) {
            throw new Error('EF1666-H1: The Email Management module returned HTTP ' + response.status + '.');
        }
        if (loginPage(doc)) {
            throw new Error('EF1666-H2: The client-area session expired while contacting the Email Management module.');
        }
        return doc;
    }

    function formBody(values) {
        var params = new URLSearchParams();
        Object.keys(values).forEach(function (key) {
            if (values[key] !== undefined && values[key] !== null) {
                params.append(key, String(values[key]));
            }
        });
        return params.toString();
    }

    function findContext(doc) {
        var free = doc.querySelector('input[name="freemailhosting"]');
        if (!free) {
            return null;
        }
        var domain = doc.querySelector('input[name="domain"]');
        var domainId = doc.querySelector('input[name="domainid"]');
        return {
            freemailhosting: text(free.value),
            domain: text(domain ? domain.value : config.domain),
            domainId: text(domainId ? domainId.value : config.domainId)
        };
    }

    async function loadModuleContext() {
        if (moduleContextPromise) {
            return moduleContextPromise;
        }

        moduleContextPromise = (async function () {
            var urls = [
                moduleUrl('') + (moduleUrl('').indexOf('?') === -1 ? '?' : '&')
                    + 'domainid=' + encodeURIComponent(config.domainId)
                    + '&domain=' + encodeURIComponent(config.domain),
                moduleUrl('manageemails')
                    + '&domainid=' + encodeURIComponent(config.domainId)
                    + '&domain=' + encodeURIComponent(config.domain)
            ];

            for (var i = 0; i < urls.length; i++) {
                try {
                    var doc = await fetchDocument(urls[i], { method: 'GET' });
                    var context = findContext(doc);
                    if (context) {
                        return context;
                    }
                } catch (error) {
                    if (i === urls.length - 1) {
                        throw error;
                    }
                }
            }

            throw new Error('EF1666-C1: The installed Email Management module context could not be read.');
        })();

        return moduleContextPromise;
    }

    function baseFields(context, sourceEmail) {
        return {
            page: 1,
            domainid: context.domainId,
            domain: context.domain,
            freemailhosting: context.freemailhosting,
            mailtype: 'onlyforwarder',
            emailaddress: sourceEmail,
            istab: 'tabadminfwd'
        };
    }

    function accountState(doc, expectedSource) {
        var tab = doc.querySelector('#tabAdminfwd');
        if (!tab) {
            var pageError = doc.querySelector('.alert-danger');
            throw new Error('EF1666-A1: The Email Management module did not open the forward-only account.'
                + (pageError ? ' ' + cleanAlertText(pageError) : ''));
        }

        var actionError = tab.querySelector('.alert-danger');
        var addInput = tab.querySelector('input[name="addforwarder"]');
        var addForm = addInput ? addInput.form : null;
        if (!addForm) {
            throw new Error('EF1666-A2: The module response did not contain the forward-management form.'
                + (actionError ? ' ' + cleanAlertText(actionError) : ''));
        }

        var mailType = addForm.querySelector('input[name="mailtype"]');
        var emailAddress = addForm.querySelector('input[name="emailaddress"]');
        if (!mailType || text(mailType.value).toLowerCase() !== 'onlyforwarder') {
            throw new Error('EF1666-A3: The selected address is not a forward-only account. No changes were submitted.');
        }
        if (!emailAddress || normalizeEmail(emailAddress.value) !== normalizeEmail(expectedSource)) {
            throw new Error('EF1666-A4: The module opened a different source account. No changes were submitted.');
        }

        var destinations = [];
        tab.querySelectorAll('input[name="forwarder"]').forEach(function (input) {
            var email = normalizeEmail(input.value);
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                destinations.push(email);
            }
        });
        tab.querySelectorAll('span.label-success,span.badge-success').forEach(function (node) {
            var email = normalizeEmail(node.textContent);
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                destinations.push(email);
            }
        });

        return {
            destinations: unique(destinations),
            actionError: actionError ? cleanAlertText(actionError) : ''
        };
    }

    async function openAccount(context, sourceEmail) {
        var fields = baseFields(context, sourceEmail);
        fields.accountaction = 'modify';
        var doc = await fetchDocument(moduleUrl('modifyaccount'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: formBody(fields)
        });
        return accountState(doc, sourceEmail);
    }

    async function postForwardAction(context, sourceEmail, actionFields) {
        var fields = baseFields(context, sourceEmail);
        Object.keys(actionFields).forEach(function (key) { fields[key] = actionFields[key]; });
        var doc = await fetchDocument(moduleUrl('modifyaccount'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: formBody(fields)
        });

        var tab = doc.querySelector('#tabAdminfwd');
        var error = tab ? tab.querySelector('.alert-danger') : doc.querySelector('.alert-danger');
        if (error) {
            throw new Error('EF1666-M1: ' + cleanAlertText(error));
        }
        return accountState(doc, sourceEmail);
    }

    async function waitForState(context, sourceEmail, required, absent) {
        var delays = [0, 500, 1400, 2800, 5000, 8000];
        var last = null;
        for (var i = 0; i < delays.length; i++) {
            if (delays[i] > 0) {
                await sleep(delays[i] - delays[i - 1]);
            }
            last = await openAccount(context, sourceEmail);
            if (containsAll(last.destinations, required) && containsNone(last.destinations, absent)) {
                return last;
            }
        }
        return last;
    }

    async function applyChange(context, change) {
        var state = await openAccount(context, change.sourceEmail);
        var additions = difference(change.newDestinations, change.oldDestinations);
        var removals = difference(change.oldDestinations, change.newDestinations);
        var actualAdditions = difference(additions, state.destinations);
        var actualRemovals = removals.filter(function (value) {
            return state.destinations.indexOf(value) !== -1;
        });

        for (var i = 0; i < actualAdditions.length; i++) {
            await postForwardAction(context, change.sourceEmail, {
                addforwarder: 'true',
                forwardto: actualAdditions[i]
            });
            var afterAdd = await waitForState(context, change.sourceEmail, [actualAdditions[i]], []);
            if (!afterAdd || afterAdd.destinations.indexOf(actualAdditions[i]) === -1) {
                throw new Error('EF1666-M2: The module did not confirm the added destination '
                    + actualAdditions[i] + '. No original destination was removed.');
            }
            state = afterAdd;
        }

        for (var j = 0; j < actualRemovals.length; j++) {
            if (state.destinations.length <= 1) {
                throw new Error('EF1666-M3: The module would have to remove the final destination. Clear the entire row to delete the forwarding account instead.');
            }
            await postForwardAction(context, change.sourceEmail, {
                delforwarder: 'true',
                forwarder: actualRemovals[j]
            });
            var afterRemove = await waitForState(context, change.sourceEmail, [], [actualRemovals[j]]);
            if (!afterRemove || afterRemove.destinations.indexOf(actualRemovals[j]) !== -1) {
                throw new Error('EF1666-M4: The installed Email Management module did not confirm removal of '
                    + actualRemovals[j] + '. Any newly added destination remains active, and no unrelated destination was removed.');
            }
            state = afterRemove;
        }

        var finalState = await waitForState(context, change.sourceEmail, additions, removals);
        if (!finalState || !containsAll(finalState.destinations, additions) || !containsNone(finalState.destinations, removals)) {
            throw new Error('EF1666-V1: The module did not confirm the requested destination changes. Reload Email Forwarding before another edit.');
        }

        return {
            additions: actualAdditions.length,
            removals: actualRemovals.length
        };
    }

    function initializeOriginals(form) {
        existingRows(form).forEach(function (item) {
            item.prefix.dataset.dmEf1666OriginalPrefix = text(item.prefix.value);
            item.destinations.dataset.dmEf1666OriginalDestinations = text(item.destinations.value);
        });
    }

    function collectChanges(form) {
        var changes = [];
        var sourceMoves = [];
        var nativeDeletes = 0;
        var errors = [];

        existingRows(form).forEach(function (item) {
            var originalPrefix = text(item.prefix.dataset.dmEf1666OriginalPrefix);
            var originalDestinationText = text(item.destinations.dataset.dmEf1666OriginalDestinations);
            var currentPrefix = text(item.prefix.value);
            var currentDestinationText = text(item.destinations.value);

            if (normalizePrefix(originalPrefix) === normalizePrefix(currentPrefix)
                && originalDestinationText.toLowerCase() === currentDestinationText.toLowerCase()) {
                return;
            }

            if (!currentPrefix || !currentDestinationText) {
                nativeDeletes++;
                return;
            }

            var oldList = parseDestinations(originalDestinationText);
            var newList = parseDestinations(currentDestinationText);
            if (!oldList.valid || !newList.valid) {
                errors.push('Each forwarding destination must be a valid email address. Separate multiple addresses with commas.');
                return;
            }

            var oldSource = fullSource(originalPrefix);
            var newSource = fullSource(currentPrefix);
            if (!oldSource || !newSource) {
                errors.push('The source forwarding address was invalid.');
                return;
            }

            if (oldSource !== newSource) {
                sourceMoves.push({
                    oldSource: oldSource,
                    newSource: newSource,
                    oldPrefix: normalizePrefix(originalPrefix),
                    newPrefix: normalizePrefix(currentPrefix),
                    oldDestinations: oldList.values,
                    newDestinations: newList.values
                });
                return;
            }

            var additions = difference(newList.values, oldList.values);
            var removals = difference(oldList.values, newList.values);
            if (!additions.length && !removals.length) {
                return;
            }

            changes.push({
                sourceEmail: oldSource,
                oldDestinations: oldList.values,
                newDestinations: newList.values
            });
        });

        var newPrefix = form.querySelector('input[name="emailforwarderprefixnew"]');
        var newDestination = form.querySelector('input[name="emailforwarderforwardtonew"]');
        var hasNativeAdd = !!(text(newPrefix ? newPrefix.value : '') || text(newDestination ? newDestination.value : ''));

        if (sourceMoves.length && (changes.length || nativeDeletes || hasNativeAdd)) {
            errors.push('Save source-address changes separately from destination-only edits, new forwards, or whole-forward deletions. Multiple source-address changes may be saved together.');
        }

        if (sourceMoves.length) {
            var originals = originalState(form);
            var oldSources = Object.create(null);
            var newSources = Object.create(null);

            sourceMoves.forEach(function (move) {
                oldSources[move.oldSource] = true;
                if (newSources[move.newSource]) {
                    errors.push('Two edited rows cannot use the same replacement source address. No changes were submitted.');
                }
                newSources[move.newSource] = true;
            });

            sourceMoves.forEach(function (move) {
                if (oldSources[move.newSource] && move.newSource !== move.oldSource) {
                    errors.push('A replacement source cannot be another source being changed in the same Save. Use a unique replacement address.');
                }
                if (originals[move.newSource] && move.newSource !== move.oldSource
                    && !sameSet(originals[move.newSource].destinations, move.newDestinations)) {
                    errors.push('The replacement source address ' + move.newSource + ' already exists with a different destination list. No changes were submitted.');
                }
            });
        }

        return {
            changes: changes,
            sourceMoves: sourceMoves,
            nativeDeletes: nativeDeletes,
            hasNativeAdd: hasNativeAdd,
            errors: errors
        };
    }

    function currentMove(pending) {
        if (!pending || !Array.isArray(pending.moves)) {
            return null;
        }
        return pending.moves[pending.index || 0] || null;
    }

    function moveProgress(pending) {
        var total = pending && Array.isArray(pending.moves) ? pending.moves.length : 1;
        var current = Math.min((pending && Number(pending.index) || 0) + 1, total);
        return total > 1 ? 'Source ' + current + ' of ' + total + '. ' : '';
    }

    function failPending(form, code, message) {
        clearPending();
        setBusy(form, false);
        show(form, 'danger', 'Email forward was not updated (EF1666).', code + ': ' + message);
        return false;
    }

    function completeCurrentMove(form, pending) {
        pending.index = (Number(pending.index) || 0) + 1;
        if (pending.index >= pending.moves.length) {
            var count = pending.moves.length;
            clearPending();
            setBusy(form, false);
            show(form, 'success', 'Email forwarding updated.', count + ' source address' + (count === 1 ? ' was' : 'es were') + ' changed and verified through normal Save Changes submissions.');
            return false;
        }

        pending.stage = 'start';
        writePending(pending);
        setBusy(form, false);
        window.setTimeout(function () { startCurrentMove(form, pending); }, 120);
        return true;
    }

    function scheduleDeleteStage(form, pending) {
        var move = currentMove(pending);
        if (!move) {
            return failPending(form, 'EF1666-Q1', 'The queued source-address change could not be read. No further changes were submitted.');
        }

        var live = renderedState(form);
        var replacement = live[move.newSource] || null;
        var original = live[move.oldSource] || null;

        if (!replacement || !sameSet(replacement.destinations, move.newDestinations)) {
            return failPending(form, 'EF1666-S3', 'The replacement source was not present with its complete destination list. The original source remains active.' + (pageDangerText() ? ' ' + pageDangerText() : ''));
        }
        if (!original) {
            return completeCurrentMove(form, pending);
        }
        if (!sameSet(original.destinations, move.oldDestinations)) {
            return failPending(form, 'EF1666-S4', 'The original source destination list changed before deletion. Both sources remain active for safety.');
        }

        restoreOriginalRows(form);
        clearNativeNewRow(form);
        var fresh = renderedState(form);
        var oldItem = fresh[move.oldSource] || null;
        if (!oldItem) {
            return failPending(form, 'EF1666-S5', 'The exact original source row could not be located on the fresh page. No deletion was submitted.');
        }

        oldItem.item.prefix.value = '';
        oldItem.item.destinations.value = '';
        pending.stage = 'verifyDelete';
        writePending(pending);
        setBusy(form, true);
        show(form, 'info', 'Finishing source-address change…', moveProgress(pending) + 'The replacement is active. Removing the exact original source through a normal Save Changes submission.');
        window.setTimeout(function () { submitNativePage(form); }, 120);
        return true;
    }

    function startCurrentMove(form, pending) {
        var move = currentMove(pending);
        if (!move) {
            return completeCurrentMove(form, pending);
        }

        var originals = originalState(form);
        var original = originals[move.oldSource] || null;
        var existingReplacement = originals[move.newSource] || null;

        if (existingReplacement && !sameSet(existingReplacement.destinations, move.newDestinations)) {
            return failPending(form, 'EF1666-S1', 'The replacement source ' + move.newSource + ' already exists with a different destination list. No changes were submitted for this source.');
        }

        if (!original) {
            if (existingReplacement && sameSet(existingReplacement.destinations, move.newDestinations)) {
                return completeCurrentMove(form, pending);
            }
            return failPending(form, 'EF1666-S9', 'The original source ' + move.oldSource + ' is no longer present. No replacement was created.');
        }

        if (existingReplacement) {
            pending.stage = 'awaitDelete';
            writePending(pending);
            restoreOriginalRows(form);
            return scheduleDeleteStage(form, pending);
        }

        restoreOriginalRows(form);
        clearNativeNewRow(form);
        var newPrefix = form.querySelector('input[name="emailforwarderprefixnew"]');
        var newDestination = form.querySelector('input[name="emailforwarderforwardtonew"]');
        if (!newPrefix || !newDestination) {
            return failPending(form, 'EF1666-S0', 'The native new-forward fields could not be located. No changes were submitted.');
        }

        newPrefix.value = move.newSource.split('@')[0];
        newDestination.value = move.newDestinations.join(',');
        pending.stage = 'awaitCreate';
        writePending(pending);
        setBusy(form, true);
        show(form, 'info', 'Changing source address…', moveProgress(pending) + 'Creating the replacement through a normal Save Changes submission. The original remains active until the replacement is verified.');
        window.setTimeout(function () { submitNativePage(form); }, 120);
        return true;
    }

    function resumePending(form) {
        var pending = readPending();
        if (!pending) {
            return false;
        }
        if (!Array.isArray(pending.moves) || !pending.moves.length) {
            return failPending(form, 'EF1666-Q2', 'The queued source-address changes were unreadable. No further changes were submitted.');
        }

        var move = currentMove(pending);
        var live = renderedState(form);
        var replacement = move ? live[move.newSource] || null : null;
        var original = move ? live[move.oldSource] || null : null;

        if (pending.stage === 'start') {
            return startCurrentMove(form, pending);
        }

        if (pending.stage === 'awaitCreate') {
            if (!replacement) {
                return failPending(form, 'EF1666-S2', 'The normal new-forward submission did not create ' + move.newSource + '. The original source remains active.' + (pageDangerText() ? ' ' + pageDangerText() : ''));
            }
            if (!sameSet(replacement.destinations, move.newDestinations)) {
                return failPending(form, 'EF1666-S3', 'The replacement source exists with a different destination list. Both sources remain active; nothing was deleted.');
            }
            pending.stage = 'awaitDelete';
            writePending(pending);
            return scheduleDeleteStage(form, pending);
        }

        if (pending.stage === 'awaitDelete') {
            return scheduleDeleteStage(form, pending);
        }

        if (pending.stage === 'verifyDelete') {
            if (!replacement || !sameSet(replacement.destinations, move.newDestinations)) {
                return failPending(form, 'EF1666-S7', 'The deletion submission completed, but the replacement source could not be reconfirmed.');
            }
            if (original) {
                return failPending(form, 'EF1666-S8', 'The native empty-row Save Changes submission did not remove the original source. Both sources remain active.' + (pageDangerText() ? ' ' + pageDangerText() : ''));
            }
            return completeCurrentMove(form, pending);
        }

        return failPending(form, 'EF1666-Q3', 'The queued source-address stage was not recognized. No further changes were submitted.');
    }

    function beginSourceMoves(form, moves) {
        var pending = {
            stage: 'start',
            moves: moves,
            index: 0,
            started: Date.now()
        };
        writePending(pending);
        return startCurrentMove(form, pending);
    }

    function replaceProviderLanguage(root) {
        if (!root || !document.createTreeWalker) {
            return;
        }
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                var parent = node.parentElement;
                if (!parent || /^(SCRIPT|STYLE|NOSCRIPT|TEXTAREA)$/i.test(parent.tagName)) {
                    return NodeFilter.FILTER_REJECT;
                }
                return /reseller\s*club/i.test(node.nodeValue || '') ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });
        var nodes = [];
        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }
        nodes.forEach(function (node) {
            node.nodeValue = String(node.nodeValue || '')
                .replace(/Reseller\s*Club\s+Email\s+Management/gi, 'Email Management')
                .replace(/Reseller\s*Club\s+Email\s+Forwarding/gi, 'Email Forwarding')
                .replace(/Reseller\s*Club/gi, 'Registrar');
        });
        root.querySelectorAll('[title],[aria-label]').forEach(function (node) {
            ['title', 'aria-label'].forEach(function (name) {
                var value = node.getAttribute(name);
                if (value && /reseller\s*club/i.test(value)) {
                    node.setAttribute(name, value.replace(/Reseller\s*Club/gi, 'Registrar'));
                }
            });
        });
    }

    function ensureSourceLayout(item) {
        var sourceCell = item.prefix.closest('td');
        if (!sourceCell) { return; }
        sourceCell.classList.add('dm-ef-source-cell-1666');

        var row = item.row || sourceCell.closest('tr');
        var bridgeCell = row && sourceCell.nextElementSibling;
        var bridgeText = bridgeCell ? text(bridgeCell.textContent).replace(/\s+/g, ' ') : '';
        if (!bridgeText) {
            bridgeText = '@' + String(config.domain || '') + ' =>';
        }
        if (bridgeCell) {
            bridgeCell.classList.add('dm-ef-bridge-cell-1666');
            bridgeCell.setAttribute('aria-hidden', 'true');
        }

        var wrap = sourceCell.querySelector('.dm-ef-source-wrap-1666');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'dm-ef-source-wrap-1666';
            sourceCell.insertBefore(wrap, sourceCell.firstChild);
            wrap.appendChild(item.prefix);
        }

        var suffix = wrap.querySelector('.dm-ef-source-suffix-1666');
        if (!suffix) {
            suffix = document.createElement('span');
            suffix.className = 'dm-ef-source-suffix-1666';
            wrap.appendChild(suffix);
        }
        suffix.textContent = bridgeText;
        item.prefix.setAttribute('size', '25');
    }

    function ensureDestinationLayout(item, removable) {
        var destinationCell = item.destinations.closest('td');
        if (!destinationCell) { return; }
        destinationCell.classList.add('dm-ef-destination-cell-1666');

        var wrap = destinationCell.querySelector('.dm-ef-destination-wrap-1666');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'dm-ef-destination-wrap-1666';
            destinationCell.insertBefore(wrap, destinationCell.firstChild);
            wrap.appendChild(item.destinations);
        }

        if (!removable || wrap.querySelector('.dm-ef-remove-1666')) { return; }
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'dm-ef-remove-1666';
        button.textContent = 'Remove';
        button.setAttribute('aria-label', 'Remove this email forward on Save Changes');
        button.addEventListener('click', function () {
            if (!item.row.classList.contains('dm-ef-remove-pending-1666')
                && item.row.classList.contains('dm-ef-catchall-row-1666')) {
                var owningForm = item.destinations.form || findNativeForm();
                if (owningForm) {
                    show(owningForm, 'warning', 'Disable or change the catch-all first.', 'Use the catch-all controls before removing the forwarding account currently designated as catch-all.');
                }
                return;
            }
            var pending = item.row.classList.toggle('dm-ef-remove-pending-1666');
            if (pending) {
                item.prefix.dataset.dmEf1666RemovePrefix = item.prefix.value;
                item.destinations.dataset.dmEf1666RemoveDestinations = item.destinations.value;
                item.prefix.value = '';
                item.destinations.value = '';
                button.textContent = 'Undo';
                button.setAttribute('aria-label', 'Undo removal of this email forward');
            } else {
                item.prefix.value = item.prefix.dataset.dmEf1666RemovePrefix || item.prefix.dataset.dmEf1666OriginalPrefix || item.prefix.defaultValue || '';
                item.destinations.value = item.destinations.dataset.dmEf1666RemoveDestinations || item.destinations.dataset.dmEf1666OriginalDestinations || item.destinations.defaultValue || '';
                button.textContent = 'Remove';
                button.setAttribute('aria-label', 'Remove this email forward on Save Changes');
            }
            item.prefix.dispatchEvent(new Event('input', { bubbles: true }));
            item.destinations.dispatchEvent(new Event('input', { bubbles: true }));
        });
        wrap.appendChild(button);
    }

    function decorateForwardingFields(form) {
        var headerRow = form.querySelector('table thead tr');
        if (headerRow && headerRow.children.length >= 3) {
            headerRow.children[0].classList.add('dm-ef-source-cell-1666');
            headerRow.children[1].classList.add('dm-ef-bridge-cell-1666');
            headerRow.children[2].classList.add('dm-ef-destination-cell-1666');
        }

        existingRows(form).forEach(function (item) {
            ensureSourceLayout(item);
            ensureDestinationLayout(item, true);
        });

        var newPrefix = form.querySelector('input[name="emailforwarderprefixnew"]');
        var newDestination = form.querySelector('input[name="emailforwarderforwardtonew"]');
        if (newPrefix && newDestination) {
            var row = newPrefix.closest('tr');
            ensureSourceLayout({ row: row, prefix: newPrefix, destinations: newDestination });
            ensureDestinationLayout({ row: row, prefix: newPrefix, destinations: newDestination }, false);
        }
    }

    function findInfoBox(form) {
        var root = form.closest('.primary-content') || form.parentElement || document;
        var boxes = root.querySelectorAll('.alert-info, .alert.alert-info');
        return boxes.length ? boxes[0] : null;
    }

    function extractCatchallSource(doc) {
        /* Patch 1666: the Email Management account template emits the exact
         * active catch-all address as a hidden marker. This is independent of
         * language text, account sorting, and visible table layout. */
        var marker = doc.querySelector('[data-dm-catchall-source]');
        if (marker) {
            var markedEmail = normalizeEmail(
                marker.getAttribute('data-dm-catchall-source')
                || marker.value
                || marker.textContent
                || ''
            );
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(markedEmail)) {
                return markedEmail;
            }
        }

        var deactivate = doc.querySelector('input[name="deactivatecatchall"]');
        if (deactivate && deactivate.form) {
            var labeled = deactivate.form.querySelector('.label-success strong,.badge-success strong,.label-success,.badge-success');
            var labeledEmail = normalizeEmail(labeled ? labeled.textContent : '');
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(labeledEmail)) {
                return labeledEmail;
            }
            var direct = String(deactivate.form.textContent || '').match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
            if (direct) { return normalizeEmail(direct[0]); }
        }

        var rows = doc.querySelectorAll('tr');
        for (var i = 0; i < rows.length; i++) {
            var rowText = text(rows[i].textContent).replace(/\s+/g, ' ');
            if (!/catch\s*-?\s*all/i.test(rowText)) { continue; }
            var hidden = rows[i].querySelector('input[name="emailaddress"]');
            if (hidden && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalizeEmail(hidden.value))) {
                return normalizeEmail(hidden.value);
            }
            var match = rowText.match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
            if (match) { return normalizeEmail(match[0]); }
        }
        return '';
    }

    async function loadCatchallSource(context) {
        /*
         * The active catch-all is exposed reliably in the module's account
         * listing, where the exact account row is labelled as the catch-all.
         * Do not use the rewritten Mail Hosting/status workflow page as the
         * source of truth.
         */
        var urls = [
            moduleUrl('manageemails')
                + '&q=forward'
                + '&page=1&itemlimit=100'
                + '&domainid=' + encodeURIComponent(context.domainId)
                + '&domain=' + encodeURIComponent(context.domain)
                + '&freemailhosting=' + encodeURIComponent(context.freemailhosting || ''),
            moduleUrl('manageemails')
                + '&page=1&itemlimit=100'
                + '&domainid=' + encodeURIComponent(context.domainId)
                + '&domain=' + encodeURIComponent(context.domain)
                + '&freemailhosting=' + encodeURIComponent(context.freemailhosting || '')
        ];
        var lastError = null;
        for (var i = 0; i < urls.length; i++) {
            try {
                var doc = await fetchDocument(urls[i], { method: 'GET' });
                var source = extractCatchallSource(doc);
                if (source) { return source; }
            } catch (error) {
                lastError = error;
            }
        }
        if (lastError) { throw lastError; }
        return '';
    }

    function clearCatchallMarks(form) {
        form.querySelectorAll('.dm-ef-catchall-badge-1666').forEach(function (node) { node.remove(); });
        form.querySelectorAll('.dm-ef-catchall-row-1666').forEach(function (node) { node.classList.remove('dm-ef-catchall-row-1666'); });
    }

    function markCatchall(form, source) {
        clearCatchallMarks(form);
        if (!source) { return; }
        existingRows(form).forEach(function (item) {
            if (fullSource(item.prefix.value) !== source) { return; }
            item.row.classList.add('dm-ef-catchall-row-1666');
            var badge = document.createElement('span');
            badge.className = 'dm-ef-catchall-badge-1666';
            badge.textContent = 'Catch-all';
            var wrap = item.prefix.closest('.dm-ef-source-wrap-1666') || item.prefix.parentElement;
            if (wrap) {
                var suffix = wrap.querySelector('.dm-ef-source-suffix-1666');
                if (suffix) {
                    wrap.insertBefore(badge, suffix);
                } else {
                    wrap.appendChild(badge);
                }
            }
        });
    }

    function installHelpAndCatchall(form) {
        var info = findInfoBox(form);
        if (!info || info.querySelector('.dm-ef-help-1666')) { return; }

        var help = document.createElement('div');
        help.className = 'dm-ef-help-1666';
        var copy = document.createElement('div');
        copy.className = 'dm-ef-help-copy-1666';
        var note = document.createElement('div');
        note.innerHTML = '<strong>Multiple destinations:</strong> Separate destination email addresses with a comma (,).';
        var deleteNote = document.createElement('div');
        deleteNote.innerHTML = '<strong>Remove a forward:</strong> Click Remove beside the destination, then Save Changes.';
        var catchallLine = document.createElement('div');
        catchallLine.className = 'dm-ef-catchall-line-1666';
        var catchallLabel = document.createElement('span');
        catchallLine.appendChild(catchallLabel);
        copy.appendChild(note);
        copy.appendChild(deleteNote);
        copy.appendChild(catchallLine);

        var action = document.createElement('button');
        action.type = 'button';
        action.className = 'btn btn-default dm-ef-catchall-action-1666';
        action.textContent = 'Set / Change Catch-all';
        action.setAttribute('data-dm-route-lock', '1');
        action.setAttribute('data-dm-catchall-control', '1');
        var disableButton = document.createElement('button');
        disableButton.type = 'button';
        disableButton.className = 'btn dm-ef-catchall-disable-1666';
        disableButton.textContent = 'Disable Catch-all';
        disableButton.setAttribute('aria-label', 'Disable catch-all email forwarding');
        disableButton.hidden = true;
        var actionButtons = document.createElement('div');
        actionButtons.className = 'dm-ef-catchall-buttons-1666';
        actionButtons.appendChild(action);
        actionButtons.appendChild(disableButton);
        help.appendChild(copy);
        help.appendChild(actionButtons);

        var panel = document.createElement('div');
        panel.className = 'dm-ef-catchall-panel-1666';
        var panelLabel = document.createElement('label');
        panelLabel.textContent = 'Catch-all address';
        var select = document.createElement('select');
        select.className = 'dm-ef-catchall-select-1666';
        select.setAttribute('aria-label', 'Catch-all address');
        var save = document.createElement('button');
        save.type = 'button';
        save.className = 'btn dm-ef-catchall-save-1666';
        save.textContent = 'Save Catch-all';
        var cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'btn dm-ef-catchall-cancel-1666';
        cancel.textContent = 'Cancel';
        panel.appendChild(panelLabel);
        panel.appendChild(select);
        panel.appendChild(save);
        panel.appendChild(cancel);
        help.appendChild(panel);
        info.appendChild(help);

        var currentSource = normalizeEmail(config.catchallSource || '');

        function refreshStatus(source) {
            currentSource = normalizeEmail(source || '');
            config.catchallSource = currentSource;
            catchallLabel.innerHTML = currentSource
                ? '<strong>Catch-all:</strong> ' + currentSource
                : (config.catchallStatusAvailable === false
                    ? '<strong>Catch-all:</strong> Status unavailable'
                    : '<strong>Catch-all:</strong> No active address');
            disableButton.hidden = !currentSource;
            markCatchall(form, currentSource);
        }

        function populateSelector() {
            var sources = [];
            var seen = Object.create(null);
            existingRows(form).forEach(function (item) {
                var source = fullSource(item.prefix.dataset.dmEf1666OriginalPrefix || item.prefix.defaultValue || item.prefix.value);
                if (source && !seen[source]) {
                    seen[source] = true;
                    sources.push(source);
                }
            });
            sources.sort();
            select.innerHTML = '';
            sources.forEach(function (source) {
                var option = document.createElement('option');
                option.value = source;
                option.textContent = source;
                if (source === currentSource) { option.selected = true; }
                select.appendChild(option);
            });
            save.disabled = sources.length === 0;
            if (!sources.length) {
                var empty = document.createElement('option');
                empty.value = '';
                empty.textContent = 'No email forwards are available';
                select.appendChild(empty);
            }
        }

        action.addEventListener('click', function () {
            populateSelector();
            panel.classList.toggle('dm-open-1666');
            if (panel.classList.contains('dm-open-1666')) {
                select.focus();
            }
        });

        cancel.addEventListener('click', function () {
            panel.classList.remove('dm-open-1666');
        });

        function submitCatchall(actionName, source) {
            save.disabled = true;
            action.disabled = true;
            disableButton.disabled = true;
            cancel.disabled = true;
            select.disabled = true;
            var body = new URLSearchParams();
            body.set('dm_email_catchall_1666', actionName);
            body.set('dm_email_catchall_token_1666', String(config.catchallToken || ''));
            body.set('domainid', String(config.domainId || ''));
            body.set('id', String(config.domainId || ''));
            if (actionName === 'set') {
                body.set('catchall_source', source);
            }

            fetch(window.location.href, {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString()
            }).then(function (response) {
                return response.text().then(function (raw) {
                    var payload = null;
                    try { payload = JSON.parse(raw); } catch (error) {}
                    if (!response.ok || !payload || !payload.success) {
                        throw new Error(payload && payload.message ? payload.message : 'The catch-all address was not updated.');
                    }
                    return payload;
                });
            }).then(function (payload) {
                config.catchallStatusAvailable = true;
                refreshStatus(actionName === 'disable' ? '' : (payload.source || source));
                panel.classList.remove('dm-open-1666');
                show(
                    form,
                    'success',
                    actionName === 'disable' ? 'Catch-all disabled.' : 'Catch-all updated.',
                    payload.message || (actionName === 'disable'
                        ? 'Catch-all email forwarding is no longer active.'
                        : 'The selected forwarding address is now the catch-all.')
                );
            }).catch(function (error) {
                show(
                    form,
                    'danger',
                    actionName === 'disable' ? 'Catch-all was not disabled.' : 'Catch-all was not updated.',
                    error && error.message ? error.message : 'The catch-all request failed.'
                );
            }).then(function () {
                save.disabled = !select.value;
                action.disabled = false;
                disableButton.disabled = false;
                cancel.disabled = false;
                select.disabled = false;
            });
        }

        save.addEventListener('click', function () {
            var source = normalizeEmail(select.value || '');
            if (!source) { return; }
            submitCatchall('set', source);
        });

        disableButton.addEventListener('click', function () {
            if (!currentSource) { return; }
            var confirmed = window.confirm(
                'Disable catch-all email forwarding for ' + String(config.domain || '')
                + '? Messages sent to addresses that do not exist will no longer be forwarded.'
            );
            if (!confirmed) { return; }
            submitCatchall('disable', '');
        });

        refreshStatus(currentSource);
    }

    function install() {
        var form = findNativeForm();
        if (!form || form.dataset.dmEmailRcmBridge1666 === '1') {
            return;
        }
        form.dataset.dmEmailRcmBridge1666 = '1';
        initializeOriginals(form);
        decorateForwardingFields(form);
        installHelpAndCatchall(form);
        replaceProviderLanguage(document.querySelector('#main-body') || document.body);

        if (resumePending(form)) {
            return;
        }

        form.addEventListener('reset', function () {
            window.setTimeout(function () {
                initializeOriginals(form);
                form.querySelectorAll('.dm-ef-remove-pending-1666').forEach(function (row) {
                    row.classList.remove('dm-ef-remove-pending-1666');
                });
                form.querySelectorAll('.dm-ef-remove-1666').forEach(function (button) {
                    button.textContent = 'Remove';
                    button.setAttribute('aria-label', 'Remove this email forward on Save Changes');
                });
                var box = document.getElementById('dm-registrar-email-rcm-message-1666');
                if (box) {
                    box.classList.remove('dm-show-1666');
                }
            }, 0);
        });

        form.addEventListener('submit', function (event) {
            if (form.dataset.dmEmailRcmBridge1666NativeSubmit === '1') {
                return;
            }
            if (busy) {
                event.preventDefault();
                event.stopImmediatePropagation();
                return;
            }

            var result = collectChanges(form);
            if (result.errors.length) {
                event.preventDefault();
                event.stopImmediatePropagation();
                show(form, 'danger', 'Email forward was not updated (EF1666).', result.errors[0]);
                return;
            }

            if (result.sourceMoves.length) {
                event.preventDefault();
                event.stopImmediatePropagation();
                beginSourceMoves(form, result.sourceMoves);
                return;
            }

            /*
             * Leave confirmed native whole-account deletion and new-forward
             * creation untouched when no existing nonblank row is being edited.
             */
            if (!result.changes.length) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            if (result.nativeDeletes || result.hasNativeAdd) {
                show(form, 'warning', 'Save these changes separately.', 'Modify existing destinations separately from adding a new forward or deleting an entire forwarding account.');
                return;
            }

            setBusy(form, true);
            show(form, 'info', 'Updating email forwarding…', 'Using Email Management to apply exact destination changes.');

            loadModuleContext().then(async function (context) {
                var totals = { additions: 0, removals: 0 };
                for (var i = 0; i < result.changes.length; i++) {
                    var applied = await applyChange(context, result.changes[i]);
                    totals.additions += applied.additions;
                    totals.removals += applied.removals;
                }
                return totals;
            }).then(function (totals) {
                var details = [];
                if (totals.additions) {
                    details.push(totals.additions + ' destination' + (totals.additions === 1 ? '' : 's') + ' added');
                }
                if (totals.removals) {
                    details.push(totals.removals + ' destination' + (totals.removals === 1 ? '' : 's') + ' removed');
                }
                show(form, 'success', 'Email forwarding updated.', (details.length ? details.join(' and ') : 'The requested state was already active') + ' and verified through Email Management.');
                window.setTimeout(function () { window.location.reload(); }, 450);
            }).catch(function (error) {
                setBusy(form, false);
                show(form, 'danger', 'Email forward was not updated (EF1666).', error && error.message ? error.message : 'An unexpected Email Management module error occurred.');
            });
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', install, { once: true });
    } else {
        install();
    }
})();
</script>
HTML;
});
