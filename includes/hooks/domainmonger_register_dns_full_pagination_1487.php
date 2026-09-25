<?php
/**
 * DomainMonger WHMCS v9 - Register DNS Full Pagination 1487
 *
 * Purpose:
 * - Replace the truncated LogicBoxes/Register DNS record list with all pages
 *   returned by the registrar API before the native WHMCS DNS template renders.
 * - Preserve the existing native Save DNS form and all current page styling.
 *
 * Scope:
 * - clientarea.php?action=domaindns
 * - LogicBoxes-compatible registrar modules only
 *
 * Safety:
 * - Read-only registrar API calls.
 * - No database changes.
 * - Falls back to the native WHMCS/registrar result if any direct API page fails.
 * - Does not touch language overrides or the protected integration folder.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm_register_dns_1487_is_target')) {
    function dm_register_dns_1487_is_target(): bool
    {
        $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        return $script === 'clientarea.php' && $action === 'domaindns';
    }
}

if (!function_exists('dm_register_dns_1487_relative_host')) {
    function dm_register_dns_1487_relative_host(string $host, string $domain): string
    {
        $host = strtolower(trim($host));
        $host = trim($host, "\"'");
        $host = rtrim($host, '.');
        $domain = strtolower(rtrim(trim($domain), '.'));

        if ($host === '' || $host === '@' || $host === $domain) {
            return '@';
        }

        $suffix = '.' . $domain;
        if ($domain !== '' && strlen($host) > strlen($suffix) && substr($host, -strlen($suffix)) === $suffix) {
            $host = substr($host, 0, -strlen($suffix));
        }

        return $host === '' ? '@' : $host;
    }
}

if (!function_exists('dm_register_dns_1487_numeric_rows')) {
    function dm_register_dns_1487_numeric_rows($response): array
    {
        if (!is_array($response)) {
            return [];
        }

        $rows = [];
        foreach ($response as $key => $value) {
            if ((is_int($key) || ctype_digit((string) $key)) && is_array($value)) {
                $rows[] = $value;
            }
        }

        return $rows;
    }
}

if (!function_exists('dm_register_dns_1487_pick')) {
    function dm_register_dns_1487_pick(array $row, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('dm_register_dns_1487_signature')) {
    function dm_register_dns_1487_signature(array $record): string
    {
        return implode('|', [
            strtoupper(trim((string) ($record['type'] ?? ''))),
            strtolower(trim((string) ($record['hostname'] ?? '@'))),
            strtolower(trim((string) ($record['address'] ?? ''))),
            trim((string) ($record['priority'] ?? '')),
        ]);
    }
}

if (!function_exists('dm_register_dns_1487_map_row')) {
    function dm_register_dns_1487_map_row(array $row, string $requestedType, string $domain, array $nativeRecids): ?array
    {
        $type = strtoupper(trim((string) dm_register_dns_1487_pick($row, ['type', 'record-type', 'recordtype'], $requestedType)));
        if (!in_array($type, ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'SRV', 'NS'], true)) {
            return null;
        }

        $host = dm_register_dns_1487_relative_host(
            (string) dm_register_dns_1487_pick($row, ['host', 'hostname', 'name'], '@'),
            $domain
        );
        $address = trim((string) dm_register_dns_1487_pick($row, ['value', 'address', 'target', 'data'], ''));
        if ($address === '') {
            return null;
        }

        $priority = dm_register_dns_1487_pick($row, ['priority', 'pref'], 'N/A');
        if ($type !== 'MX' && $type !== 'SRV') {
            $priority = 'N/A';
        }

        $ttl = (int) dm_register_dns_1487_pick($row, ['ttl'], 14400);
        if ($ttl < 1) {
            $ttl = 14400;
        }

        $record = [
            'recid' => '',
            'hostname' => $host,
            'type' => $type,
            'address' => $address,
            'priority' => $priority,
            'ttl' => $ttl,
        ];

        $signature = dm_register_dns_1487_signature($record);
        if (isset($nativeRecids[$signature]) && trim((string) $nativeRecids[$signature]) !== '') {
            $record['recid'] = (string) $nativeRecids[$signature];
        } else {
            $remoteId = dm_register_dns_1487_pick(
                $row,
                ['record-id', 'recordid', 'record_id', 'recid', 'rrid', 'entityid', 'id'],
                ''
            );

            /*
             * LogicBoxes DNS modification/deletion APIs identify records by
             * their type/host/value details rather than a mandatory numeric ID.
             * Keep a stable non-empty identifier so WHMCS treats paginated rows
             * as existing records instead of new blank additions.
             */
            $record['recid'] = trim((string) $remoteId) !== ''
                ? (string) $remoteId
                : 'dm1487:' . hash('sha256', $signature);
        }

        return $record;
    }
}

if (!function_exists('dm_register_dns_1487_native_recids')) {
    function dm_register_dns_1487_native_recids(array $nativeRecords): array
    {
        $map = [];
        foreach ($nativeRecords as $record) {
            if (!is_array($record)) {
                continue;
            }

            $normalized = [
                'hostname' => (string) ($record['hostname'] ?? $record['host'] ?? '@'),
                'type' => (string) ($record['type'] ?? ''),
                'address' => (string) ($record['address'] ?? $record['value'] ?? ''),
                'priority' => (string) ($record['priority'] ?? 'N/A'),
            ];
            if (strtoupper($normalized['type']) !== 'MX' && strtoupper($normalized['type']) !== 'SRV') {
                $normalized['priority'] = 'N/A';
            }

            $signature = dm_register_dns_1487_signature($normalized);
            $recid = (string) ($record['recid'] ?? '');
            if ($signature !== '|||N/A' && $recid !== '') {
                $map[$signature] = $recid;
            }
        }

        return $map;
    }
}

if (!function_exists('dm_register_dns_1487_load_helpers')) {
    function dm_register_dns_1487_load_helpers(): bool
    {
        if (function_exists('dm_epp_find_credentials')
            && function_exists('dm_epp_api_base')
            && function_exists('dm_epp_http_request')) {
            return true;
        }

        $helper = __DIR__ . '/domainmonger_epp_authcode_manager.php';
        if (is_file($helper)) {
            require_once $helper;
        }

        return function_exists('dm_epp_find_credentials')
            && function_exists('dm_epp_api_base')
            && function_exists('dm_epp_http_request');
    }
}

if (!function_exists('dm_register_dns_1487_decode_http_response')) {
    /**
     * Decode one LogicBoxes API response using the same success/error rules as
     * dm_epp_http_request(), without changing that shared helper.
     */
    function dm_register_dns_1487_decode_http_response($response, string $curlError, int $httpCode): array
    {
        if ($response === false) {
            return [false, null, $curlError ?: 'No response from registrar API.'];
        }

        $decoded = json_decode((string) $response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded = trim((string) $response);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $detail = '';
            if (is_array($decoded)) {
                foreach (['message', 'error', 'actionstatusdesc', 'status'] as $key) {
                    if (isset($decoded[$key]) && trim((string) $decoded[$key]) !== '') {
                        $detail = trim((string) $decoded[$key]);
                        break;
                    }
                }
            } elseif (is_string($decoded)) {
                $plain = trim(preg_replace('/\s+/', ' ', strip_tags($decoded)) ?? '');
                if ($plain !== '') {
                    $detail = substr($plain, 0, 240);
                }
            }

            $message = 'Registrar API returned HTTP ' . $httpCode . '.';
            if ($detail !== '') {
                $message .= ' ' . $detail;
            }

            return [false, $decoded, $message];
        }

        if (is_array($decoded)) {
            $status = strtoupper((string) ($decoded['status'] ?? $decoded['actionstatus'] ?? ''));
            if ($status === 'ERROR') {
                $message = (string) ($decoded['message'] ?? $decoded['error'] ?? $decoded['actionstatusdesc'] ?? 'Registrar API returned an error.');
                return [false, $decoded, $message];
            }
        }

        return [true, $decoded, ''];
    }
}

if (!function_exists('dm_register_dns_1487_parallel_get')) {
    /**
     * Execute small batches of read-only LogicBoxes GET requests concurrently.
     * A maximum of four requests is used to avoid an aggressive API burst.
     *
     * @param array<string,array{url:string,params:array}> $requests
     * @return array<string,array{0:bool,1:mixed,2:string}>
     */
    function dm_register_dns_1487_parallel_get(array $requests, int $maxConcurrent = 4): array
    {
        if (!$requests) {
            return [];
        }

        if (!function_exists('curl_init')
            || !function_exists('curl_multi_init')
            || !function_exists('curl_multi_exec')) {
            $fallback = [];
            foreach ($requests as $key => $request) {
                $fallback[$key] = dm_epp_http_request(
                    'GET',
                    (string) ($request['url'] ?? ''),
                    isset($request['params']) && is_array($request['params']) ? $request['params'] : []
                );
            }
            return $fallback;
        }

        $results = [];
        $maxConcurrent = max(1, min(4, $maxConcurrent));

        foreach (array_chunk($requests, $maxConcurrent, true) as $batch) {
            $multi = curl_multi_init();
            if ($multi === false) {
                foreach ($batch as $key => $request) {
                    $results[$key] = dm_epp_http_request(
                        'GET',
                        (string) ($request['url'] ?? ''),
                        isset($request['params']) && is_array($request['params']) ? $request['params'] : []
                    );
                }
                continue;
            }

            $handles = [];
            foreach ($batch as $key => $request) {
                $params = isset($request['params']) && is_array($request['params'])
                    ? $request['params']
                    : [];
                $url = (string) ($request['url'] ?? '');
                $query = http_build_query($params, '', '&');
                if ($query !== '') {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
                }

                $handle = curl_init($url);
                if ($handle === false) {
                    $results[$key] = [false, null, 'Could not initialize a registrar API request.'];
                    continue;
                }

                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_TIMEOUT, 35);
                curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($handle, CURLOPT_USERAGENT, 'DomainMonger-WHMCS-EPP-Manager/1.0');

                $addStatus = curl_multi_add_handle($multi, $handle);
                if ($addStatus !== CURLM_OK) {
                    curl_close($handle);
                    $results[$key] = [false, null, 'Could not queue a registrar API request.'];
                    continue;
                }

                $handles[$key] = $handle;
            }

            if ($handles) {
                $running = 0;
                do {
                    do {
                        $multiStatus = curl_multi_exec($multi, $running);
                    } while (defined('CURLM_CALL_MULTI_PERFORM') && $multiStatus === CURLM_CALL_MULTI_PERFORM);

                    if ($multiStatus !== CURLM_OK) {
                        break;
                    }

                    if ($running > 0) {
                        $selected = curl_multi_select($multi, 1.0);
                        if ($selected === -1) {
                            usleep(10000);
                        }
                    }
                } while ($running > 0);

                foreach ($handles as $key => $handle) {
                    $response = curl_multi_getcontent($handle);
                    $curlError = curl_error($handle);
                    $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
                    $results[$key] = dm_register_dns_1487_decode_http_response(
                        $response,
                        $curlError,
                        $httpCode
                    );

                    curl_multi_remove_handle($multi, $handle);
                    curl_close($handle);
                }
            }

            curl_multi_close($multi);
        }

        return $results;
    }
}

if (!function_exists('dm_register_dns_1487_fetch_all')) {
    function dm_register_dns_1487_fetch_all(string $domain, string $registrar, array $nativeRecords): array
    {
        if (!dm_register_dns_1487_load_helpers()) {
            return [false, [], 'Credential/API helpers were unavailable.'];
        }

        $credentials = dm_epp_find_credentials($registrar);
        $authUserId = trim((string) ($credentials['authUserId'] ?? ''));
        $apiKey = trim((string) ($credentials['apiKey'] ?? ''));

        if ($authUserId === '' || !ctype_digit($authUserId) || $apiKey === '') {
            return [false, [], 'Usable LogicBoxes API credentials were unavailable.'];
        }

        $base = rtrim((string) dm_epp_api_base($credentials), '/');
        $endpoint = $base . '/dns/manage/search-records.json';
        $types = ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'SRV', 'NS'];
        $perPage = 50;
        $records = [];
        $nativeRecids = dm_register_dns_1487_native_recids($nativeRecords);

        /*
         * The previous implementation waited for every record type in series.
         * Fetch the same pages in conservative parallel batches instead. Types
         * that contain 50 rows remain active for the next page, preserving the
         * original all-record pagination behavior through page 100.
         */
        $pendingPages = array_fill_keys($types, 1);

        while ($pendingPages) {
            $requests = [];
            foreach ($pendingPages as $type => $page) {
                $requests[$type] = [
                    'url' => $endpoint,
                    'params' => [
                        'auth-userid' => $authUserId,
                        'api-key' => $apiKey,
                        'domain-name' => strtolower($domain),
                        'type' => $type,
                        'no-of-records' => $perPage,
                        'page-no' => $page,
                    ],
                ];
            }

            $responses = dm_register_dns_1487_parallel_get($requests, 4);
            $nextPages = [];

            foreach ($pendingPages as $type => $page) {
                if (!isset($responses[$type])) {
                    return [false, [], 'The registrar API record search did not return a response.'];
                }

                [$ok, $response, $error] = $responses[$type];
                if (!$ok) {
                    return [false, [], $error ?: 'The registrar API record search failed.'];
                }

                $rows = dm_register_dns_1487_numeric_rows($response);
                foreach ($rows as $row) {
                    $mapped = dm_register_dns_1487_map_row($row, $type, $domain, $nativeRecids);
                    if ($mapped !== null) {
                        $records[dm_register_dns_1487_signature($mapped)] = $mapped;
                    }
                }

                if (count($rows) >= $perPage && $page < 100) {
                    $nextPages[$type] = $page + 1;
                }
            }

            $pendingPages = $nextPages;
        }

        return [true, array_values($records), ''];
    }
}

add_hook('ClientAreaPageDomainDNSManagement', 9999, function ($vars) {
    if (!dm_register_dns_1487_is_target()) {
        return [];
    }

    $domainId = (int) ($vars['domainid'] ?? $_REQUEST['domainid'] ?? 0);
    $clientId = (int) ($_SESSION['uid'] ?? $_SESSION['clientid'] ?? 0);
    if ($domainId <= 0 || $clientId <= 0) {
        return [];
    }

    try {
        $domainRow = Capsule::table('tbldomains')
            ->where('id', $domainId)
            ->where('userid', $clientId)
            ->first();
    } catch (Throwable $e) {
        return [];
    }

    if (!$domainRow) {
        return [];
    }

    $registrar = strtolower(trim((string) ($domainRow->registrar ?? '')));
    if (!preg_match('/(resellerclub|netearth|stargate|logicboxes)/i', $registrar)) {
        return [];
    }

    $domain = strtolower(trim((string) ($domainRow->domain ?? '')));
    if ($domain === '') {
        return [];
    }

    $nativeRecords = isset($vars['dnsrecords']) && is_array($vars['dnsrecords'])
        ? $vars['dnsrecords']
        : [];

    [$ok, $records] = dm_register_dns_1487_fetch_all($domain, $registrar, $nativeRecords);
    if (!$ok || !$records) {
        return [];
    }

    return [
        'dnsrecords' => $records,
        'dmRegisterDnsPagination1487' => true,
        'dmRegisterDnsRecordCount1487' => count($records),
    ];
});
