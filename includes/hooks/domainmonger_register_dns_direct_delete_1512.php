<?php
/**
 * DomainMonger WHMCS v9 — Register DNS Direct Delete 1512
 *
 * Purpose:
 * - Delete only the DNS rows explicitly selected in the Register DNS editor.
 * - Bypass the unsafe native blank-row/full-form delete behavior.
 * - Resolve every selected row against the registrar's live DNS list before
 *   calling the matching LogicBoxes record-type delete endpoint.
 *
 * Safety:
 * - Page-specific to clientarea.php?action=domaindns and the legacy DNS route.
 * - Requires an authenticated client, domain ownership, and a private
 *   session-bound deletion token.
 * - Uses real registrar IDs or recomputed dm1487 identities, never row positions.
 * - Never blanks rows, reorders rows, or submits the complete DNS form.
 * - Makes no WHMCS database changes.
 * - Does not change Add, Edit, Save, Cancel, filtering, or pagination.
 * - Sorting remains view-only and is not used for record identity.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm_register_dns_delete_1512_json')) {
    function dm_register_dns_delete_1512_json(array $payload, int $statusCode = 200): void
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

if (!function_exists('dm_register_dns_delete_1512_session_token')) {
    function dm_register_dns_delete_1512_session_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_start();
        }

        $key = 'dm_register_dns_delete_token_1512';
        if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
            try {
                $_SESSION[$key] = bin2hex(random_bytes(32));
            } catch (Throwable $exception) {
                $_SESSION[$key] = hash('sha256', uniqid('dm_dns_delete_1512_', true));
            }
        }

        return (string) $_SESSION[$key];
    }
}

if (!function_exists('dm_register_dns_delete_1512_load_helpers')) {
    function dm_register_dns_delete_1512_load_helpers(): bool
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

if (!function_exists('dm_register_dns_delete_1512_pick')) {
    function dm_register_dns_delete_1512_pick(array $row, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('dm_register_dns_delete_1512_numeric_rows')) {
    function dm_register_dns_delete_1512_numeric_rows($response): array
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

if (!function_exists('dm_register_dns_delete_1512_relative_host')) {
    function dm_register_dns_delete_1512_relative_host(string $host, string $domain): string
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

if (!function_exists('dm_register_dns_delete_1512_fqdn_host')) {
    function dm_register_dns_delete_1512_fqdn_host(string $host, string $domain): string
    {
        $host = strtolower(rtrim(trim($host), '.'));
        $domain = strtolower(rtrim(trim($domain), '.'));

        if ($host === '' || $host === '@' || $host === $domain) {
            return $domain;
        }

        if ($domain !== '' && substr($host, -strlen('.' . $domain)) === '.' . $domain) {
            return $host;
        }

        return $host . '.' . $domain;
    }
}

if (!function_exists('dm_register_dns_delete_1512_txt_key_1625')) {
    /**
     * Normalize standard DNS TXT presentation forms without changing the
     * stored content. This mirrors the confirmed Patch 1624 edit behavior.
     */
    function dm_register_dns_delete_1512_txt_key_1625(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = trim(str_replace(["\r\n", "\r"], "\n", $value));

        if (strlen($value) >= 4 && substr($value, 0, 2) === '\\"' && substr($value, -2) === '\\"') {
            $value = substr($value, 2, -2);
        }

        if ($value === '' || $value[0] !== '"') {
            return $value;
        }

        $length = strlen($value);
        $offset = 0;
        $parts = [];

        while ($offset < $length) {
            while ($offset < $length && ctype_space($value[$offset])) {
                $offset++;
            }
            if ($offset >= $length) {
                break;
            }
            if ($value[$offset] !== '"') {
                return $value;
            }

            $offset++;
            $part = '';
            $closed = false;
            while ($offset < $length) {
                $character = $value[$offset];
                if ($character === '"') {
                    $offset++;
                    $closed = true;
                    break;
                }
                if ($character === '\\' && $offset + 1 < $length) {
                    if ($offset + 3 < $length
                        && ctype_digit($value[$offset + 1])
                        && ctype_digit($value[$offset + 2])
                        && ctype_digit($value[$offset + 3])) {
                        $decimal = (int) substr($value, $offset + 1, 3);
                        if ($decimal > 255) {
                            return $value;
                        }
                        $part .= chr($decimal);
                        $offset += 4;
                        continue;
                    }
                    $part .= $value[$offset + 1];
                    $offset += 2;
                    continue;
                }
                $part .= $character;
                $offset++;
            }

            if (!$closed) {
                return $value;
            }

            $parts[] = $part;
            while ($offset < $length && ctype_space($value[$offset])) {
                $offset++;
            }
            if ($offset < $length && $value[$offset] !== '"') {
                return $value;
            }
        }

        return $parts ? implode('', $parts) : $value;
    }
}

if (!function_exists('dm_register_dns_delete_1512_value_key')) {
    function dm_register_dns_delete_1512_value_key(string $value, string $type): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = trim(str_replace(["\r\n", "\r"], "\n", $value));

        if ($type === 'TXT') {
            return dm_register_dns_delete_1512_txt_key_1625($value);
        }

        if (in_array($type, ['CNAME', 'MX', 'NS', 'SRV'], true)) {
            return strtolower(rtrim($value, '.'));
        }

        return strtolower($value);
    }
}

if (!function_exists('dm_register_dns_delete_1512_priority_key')) {
    function dm_register_dns_delete_1512_priority_key(string $priority, string $type): string
    {
        if (!in_array($type, ['MX', 'SRV'], true)) {
            return '';
        }

        $priority = trim($priority);
        if ($priority === '' || preg_match('/^(?:n\/?a|not applicable)$/i', $priority)) {
            return '';
        }

        return is_numeric($priority) ? (string) ((int) $priority) : $priority;
    }
}

if (!function_exists('dm_register_dns_delete_1512_ttl_key')) {
    function dm_register_dns_delete_1512_ttl_key($ttl): string
    {
        return is_numeric($ttl) && (int) $ttl > 0 ? (string) ((int) $ttl) : '';
    }
}

if (!function_exists('dm_register_dns_delete_1512_remote_id')) {
    function dm_register_dns_delete_1512_remote_id(array $row): string
    {
        return trim((string) dm_register_dns_delete_1512_pick(
            $row,
            ['record-id', 'recordid', 'record_id', 'recid', 'rrid', 'entityid', 'id'],
            ''
        ));
    }
}

if (!function_exists('dm_register_dns_delete_1512_synthetic_id')) {
    /**
     * Recreate the stable dm1487 identity from a fresh registrar row.
     *
     * This is an identity check only. The synthetic value is never submitted
     * to the registrar as a record ID.
     */
    function dm_register_dns_delete_1512_synthetic_id(
        string $domain,
        string $type,
        string $host,
        string $value,
        string $priority
    ): string {
        $host = dm_register_dns_delete_1512_relative_host($host, $domain);
        $priority = in_array($type, ['MX', 'SRV'], true) ? trim($priority) : 'N/A';

        $signature = implode('|', [
            strtoupper(trim($type)),
            strtolower(trim($host)),
            strtolower(trim($value)),
            $priority,
        ]);

        return 'dm1487:' . hash('sha256', $signature);
    }
}

if (!function_exists('dm_register_dns_delete_1512_candidate')) {
    function dm_register_dns_delete_1512_candidate(
        array $row,
        string $domain,
        string $type
    ): array {
        $rowHostRaw = (string) dm_register_dns_delete_1512_pick($row, ['host', 'hostname', 'name'], '@');
        $rowHostKey = dm_register_dns_delete_1512_relative_host($rowHostRaw, $domain);
        $rowValueRaw = trim((string) dm_register_dns_delete_1512_pick(
            $row,
            ['value', 'address', 'target', 'data'],
            ''
        ));
        $rowPriorityRaw = (string) dm_register_dns_delete_1512_pick($row, ['priority', 'pref'], 'N/A');

        return [
            'raw' => $row,
            'hostRaw' => $rowHostRaw,
            'hostRelative' => $rowHostKey,
            'valueRaw' => $rowValueRaw,
            'valueKey' => dm_register_dns_delete_1512_value_key($rowValueRaw, $type),
            'priority' => dm_register_dns_delete_1512_priority_key($rowPriorityRaw, $type),
            'ttl' => dm_register_dns_delete_1512_ttl_key(
                dm_register_dns_delete_1512_pick($row, ['ttl'], '')
            ),
            'remoteId' => dm_register_dns_delete_1512_remote_id($row),
            'syntheticId' => dm_register_dns_delete_1512_synthetic_id(
                $domain,
                $type,
                $rowHostRaw,
                $rowValueRaw,
                $rowPriorityRaw
            ),
        ];
    }
}

if (!function_exists('dm_register_dns_delete_1512_candidate_matches_selection')) {
    /**
     * Verify that an ID-selected live row still represents the visible row.
     * This is what prevents an old/scrambled row ID from deleting another
     * record after sorting or a stale page.
     */
    function dm_register_dns_delete_1512_candidate_matches_selection(
        array $candidate,
        string $selectedHostKey,
        string $selectedValueKey,
        string $selectedPriorityKey,
        string $selectedTtlKey
    ): array {
        if ((string) ($candidate['hostRelative'] ?? '') !== $selectedHostKey) {
            return [false, 'The selected record identity now points to a different Host. Reload the page and try again.'];
        }

        if ((string) ($candidate['valueKey'] ?? '') !== $selectedValueKey) {
            return [false, 'The selected record identity now points to a different Value. Reload the page and try again.'];
        }

        $candidatePriority = (string) ($candidate['priority'] ?? '');
        if ($selectedPriorityKey !== '' && $candidatePriority !== '' && $candidatePriority !== $selectedPriorityKey) {
            return [false, 'The selected record identity now points to a different Priority. Reload the page and try again.'];
        }

        $candidateTtl = (string) ($candidate['ttl'] ?? '');
        if ($selectedTtlKey !== '' && $candidateTtl !== '' && $candidateTtl !== $selectedTtlKey) {
            return [false, 'The selected record changed after this page loaded. Reload the page and try again.'];
        }

        return [true, ''];
    }
}

if (!function_exists('dm_register_dns_delete_1512_select_live_record')) {
    /**
     * Select one live row without using its array position or visual sort
     * position. Real registrar ID is strongest; dm1487 identity is next; a
     * unique Host/Type/Value match is the final fallback.
     */
    function dm_register_dns_delete_1512_select_live_record(
        string $domain,
        string $type,
        string $selectedHost,
        string $selectedValue,
        string $selectedPriority,
        string $selectedTtl,
        string $selectedRecordId,
        array $liveRows
    ): array {
        $selectedHostKey = dm_register_dns_delete_1512_relative_host($selectedHost, $domain);
        $selectedValueKey = dm_register_dns_delete_1512_value_key($selectedValue, $type);
        $selectedPriorityKey = dm_register_dns_delete_1512_priority_key($selectedPriority, $type);
        $selectedTtlKey = dm_register_dns_delete_1512_ttl_key($selectedTtl);
        $selectedRecordId = trim($selectedRecordId);

        $candidates = [];
        foreach ($liveRows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $rowType = strtoupper(trim((string) dm_register_dns_delete_1512_pick(
                $row,
                ['type', 'record-type', 'recordtype'],
                $type
            )));
            if ($rowType !== $type) {
                continue;
            }

            $candidates[] = dm_register_dns_delete_1512_candidate($row, $domain, $type);
        }

        $identityMatches = [];
        $identityLabel = '';

        if ($selectedRecordId !== '' && strpos($selectedRecordId, 'dm1487:') !== 0) {
            $identityLabel = 'registrar record ID';
            $identityMatches = array_values(array_filter(
                $candidates,
                static function (array $candidate) use ($selectedRecordId): bool {
                    $remoteId = (string) ($candidate['remoteId'] ?? '');
                    return $remoteId !== '' && hash_equals($remoteId, $selectedRecordId);
                }
            ));
        } elseif (strpos($selectedRecordId, 'dm1487:') === 0) {
            $identityLabel = 'stable record identity';
            $identityMatches = array_values(array_filter(
                $candidates,
                static function (array $candidate) use ($selectedRecordId): bool {
                    $syntheticId = (string) ($candidate['syntheticId'] ?? '');
                    return $syntheticId !== '' && hash_equals($syntheticId, $selectedRecordId);
                }
            ));
        }

        if (count($identityMatches) === 1) {
            [$valid, $identityError] = dm_register_dns_delete_1512_candidate_matches_selection(
                $identityMatches[0],
                $selectedHostKey,
                $selectedValueKey,
                $selectedPriorityKey,
                $selectedTtlKey
            );

            if (!$valid) {
                return [false, null, $identityError];
            }

            return [true, $identityMatches[0], ''];
        }

        if (count($identityMatches) > 1) {
            return [false, null, 'More than one live DNS record returned the same ' . $identityLabel . '. No deletion was attempted.'];
        }

        $exactMatches = [];
        foreach ($candidates as $candidate) {
            if ((string) ($candidate['hostRelative'] ?? '') !== $selectedHostKey) {
                continue;
            }
            if ((string) ($candidate['valueKey'] ?? '') !== $selectedValueKey) {
                continue;
            }

            $candidatePriority = (string) ($candidate['priority'] ?? '');
            if ($selectedPriorityKey !== '' && $candidatePriority !== '' && $candidatePriority !== $selectedPriorityKey) {
                continue;
            }

            $exactMatches[] = $candidate;
        }

        if (count($exactMatches) > 1 && $selectedTtlKey !== '') {
            $ttlMatches = array_values(array_filter(
                $exactMatches,
                static function (array $candidate) use ($selectedTtlKey): bool {
                    $candidateTtl = (string) ($candidate['ttl'] ?? '');
                    return $candidateTtl !== '' && $candidateTtl === $selectedTtlKey;
                }
            ));
            if (count($ttlMatches) === 1) {
                $exactMatches = $ttlMatches;
            }
        }

        if (!$exactMatches) {
            return [false, null, 'The selected record was not found in the registrar live DNS list. No record was deleted.'];
        }

        if (count($exactMatches) !== 1) {
            return [false, null, 'More than one identical live DNS record matched the selected row. No deletion was attempted.'];
        }

        return [true, $exactMatches[0], ''];
    }
}

if (!function_exists('dm_register_dns_delete_1512_live_record')) {
    function dm_register_dns_delete_1512_live_record(
        string $domain,
        string $type,
        string $selectedHost,
        string $selectedValue,
        string $selectedPriority,
        string $selectedTtl,
        string $selectedRecordId,
        array $credentials
    ): array {
        $authUserId = trim((string) ($credentials['authUserId'] ?? ''));
        $apiKey = trim((string) ($credentials['apiKey'] ?? ''));
        if ($authUserId === '' || !ctype_digit($authUserId) || $apiKey === '') {
            return [false, null, 'Usable LogicBoxes API credentials were unavailable.'];
        }

        $base = rtrim((string) dm_epp_api_base($credentials), '/');
        $liveRows = [];

        for ($page = 1; $page <= 100; $page++) {
            [$ok, $response, $error] = dm_epp_http_request(
                'GET',
                $base . '/dns/manage/search-records.json',
                [
                    'auth-userid' => $authUserId,
                    'api-key' => $apiKey,
                    'domain-name' => strtolower($domain),
                    'type' => $type,
                    'no-of-records' => 50,
                    'page-no' => $page,
                ]
            );

            if (!$ok) {
                return [false, null, $error ?: 'The registrar DNS search failed.'];
            }

            $rows = dm_register_dns_delete_1512_numeric_rows($response);
            foreach ($rows as $row) {
                $liveRows[] = $row;
            }

            if (count($rows) < 50) {
                break;
            }
        }

        return dm_register_dns_delete_1512_select_live_record(
            $domain,
            $type,
            $selectedHost,
            $selectedValue,
            $selectedPriority,
            $selectedTtl,
            $selectedRecordId,
            $liveRows
        );
    }
}

if (!function_exists('dm_register_dns_delete_1512_delete_live_record')) {
    function dm_register_dns_delete_1512_delete_live_record(
        string $domain,
        string $type,
        array $liveRecord,
        array $credentials
    ): array {
        $endpointMap = [
            'A' => 'delete-ipv4-record.json',
            'AAAA' => 'delete-ipv6-record.json',
            'CNAME' => 'delete-cname-record.json',
            'MX' => 'delete-mx-record.json',
            'NS' => 'delete-ns-record.json',
            'TXT' => 'delete-txt-record.json',
            'SRV' => 'delete-srv-record.json',
        ];

        if (!isset($endpointMap[$type])) {
            return [false, 'This DNS record type is not supported for direct deletion.', ''];
        }

        $authUserId = trim((string) ($credentials['authUserId'] ?? ''));
        $apiKey = trim((string) ($credentials['apiKey'] ?? ''));
        $base = rtrim((string) dm_epp_api_base($credentials), '/');
        $endpointName = $endpointMap[$type];
        $row = is_array($liveRecord['raw'] ?? null) ? $liveRecord['raw'] : [];
        $host = (string) ($liveRecord['hostRelative'] ?? '@');
        $value = (string) ($liveRecord['valueRaw'] ?? '');

        if ($type === 'SRV') {
            $host = dm_register_dns_delete_1512_fqdn_host(
                (string) ($liveRecord['hostRaw'] ?? $host),
                $domain
            );
        }

        $params = [
            'auth-userid' => $authUserId,
            'api-key' => $apiKey,
            'domain-name' => strtolower($domain),
            'host' => $host,
            'value' => $value,
        ];

        if ($type === 'SRV') {
            $port = dm_register_dns_delete_1512_pick(
                $row,
                ['port', 'srv-port', 'srv_port', 'service-port', 'service_port'],
                null
            );
            $weight = dm_register_dns_delete_1512_pick(
                $row,
                ['weight', 'srv-weight', 'srv_weight'],
                null
            );

            if ($port === null || $weight === null || !is_numeric($port) || !is_numeric($weight)) {
                return [
                    false,
                    'The registrar live SRV record did not include the required port and weight values. No record was deleted.',
                    $endpointName,
                ];
            }

            $params['port'] = (int) $port;
            $params['weight'] = (int) $weight;
        }

        [$ok, $response, $error] = dm_epp_http_request(
            'POST',
            $base . '/dns/manage/' . $endpointName,
            $params
        );

        if (!$ok) {
            return [false, $error ?: 'The registrar rejected the delete request.', $endpointName];
        }

        $success = false;
        if (is_string($response)) {
            $success = strtoupper(trim($response, " \t\n\r\0\x0B\"'")) === 'SUCCESS';
        } elseif (is_array($response)) {
            $status = strtoupper(trim((string) ($response['status'] ?? $response['actionstatus'] ?? '')));
            $success = $status === 'SUCCESS' || $status === 'OK';
            if (!$success && isset($response['success'])) {
                $success = filter_var($response['success'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        // LogicBoxes commonly returns the plain string "Success". If the
        // helper reported an HTTP success and no API error but the response is
        // an empty success body, treat it as accepted.
        if (!$success && ($response === null || $response === '' || $response === [])) {
            $success = true;
        }

        if (!$success) {
            return [false, 'The registrar returned an unexpected response and deletion could not be confirmed.', $endpointName];
        }

        return [true, 'Deleted successfully.', $endpointName];
    }
}

if (
    strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
    && (string) ($_POST['dm_register_dns_direct_delete_1512'] ?? '') === '1'
) {
    $domainId = (int) ($_POST['domain_id'] ?? $_POST['domainid'] ?? 0);
    $clientId = (int) ($_SESSION['uid'] ?? $_SESSION['clientid'] ?? 0);
    $submittedToken = trim((string) ($_POST['dm_delete_token_1512'] ?? ''));
    $expectedToken = dm_register_dns_delete_1512_session_token();

    if ($clientId <= 0) {
        dm_register_dns_delete_1512_json([
            'success' => false,
            'message' => 'Your client-area session has expired. Sign in again and retry.',
        ], 401);
    }

    if ($submittedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
        dm_register_dns_delete_1512_json([
            'success' => false,
            'message' => 'The deletion security token was invalid or expired. Refresh the page and retry.',
        ], 403);
    }

    $domain = null;
    if ($domainId > 0) {
        try {
            $domain = Capsule::table('tbldomains')
                ->select(['id', 'userid', 'domain', 'registrar'])
                ->where('id', $domainId)
                ->where('userid', $clientId)
                ->first();
        } catch (Throwable $exception) {
            $domain = null;
        }
    }

    if (!$domain) {
        dm_register_dns_delete_1512_json([
            'success' => false,
            'message' => 'The domain could not be found for this client account.',
        ], 404);
    }

    $registrar = strtolower(trim((string) ($domain->registrar ?? '')));
    if (!preg_match('/(resellerclub|netearth|stargate|logicboxes)/i', $registrar)) {
        dm_register_dns_delete_1512_json([
            'success' => false,
            'message' => 'Direct RegistrarDNS deletion is not available for this registrar module.',
        ], 422);
    }

    if (!dm_register_dns_delete_1512_load_helpers()) {
        dm_register_dns_delete_1512_json([
            'success' => false,
            'message' => 'The registrar API helper functions were unavailable.',
        ], 500);
    }

    $postedFields = [
        'recordId' => $_POST['selected_record_id'] ?? [],
        'type' => $_POST['selected_record_type'] ?? [],
        'hostname' => $_POST['selected_record_hostname'] ?? [],
        'value' => $_POST['selected_record_value'] ?? [],
        'priority' => $_POST['selected_record_priority'] ?? [],
        'ttl' => $_POST['selected_record_ttl'] ?? [],
        'port' => $_POST['selected_record_port'] ?? [],
        'weight' => $_POST['selected_record_weight'] ?? [],
    ];

    foreach ($postedFields as $key => $values) {
        if (!is_array($values)) {
            $postedFields[$key] = [$values];
        }
    }

    $recordCount = 0;
    foreach ($postedFields as $values) {
        $recordCount = max($recordCount, count($values));
    }

    $submittedCount = max(0, (int) ($_POST['selected_record_count'] ?? 0));
    if ($recordCount < 1 || $recordCount > 50 || ($submittedCount > 0 && $submittedCount !== $recordCount)) {
        dm_register_dns_delete_1512_json([
            'success' => false,
            'message' => 'The selected DNS rows did not reach the delete handler correctly. Refresh the page and try again.',
            'selectedCount' => $submittedCount,
            'receivedRowCount' => $recordCount,
        ], 422);
    }

    $records = [];
    for ($index = 0; $index < $recordCount; $index++) {
        $records[] = [
            'recordId' => (string) ($postedFields['recordId'][$index] ?? ''),
            'type' => (string) ($postedFields['type'][$index] ?? ''),
            'hostname' => (string) ($postedFields['hostname'][$index] ?? ''),
            'value' => (string) ($postedFields['value'][$index] ?? ''),
            'priority' => (string) ($postedFields['priority'][$index] ?? ''),
            'ttl' => (string) ($postedFields['ttl'][$index] ?? ''),
            'port' => (string) ($postedFields['port'][$index] ?? ''),
            'weight' => (string) ($postedFields['weight'][$index] ?? ''),
        ];
    }

    $credentials = dm_epp_find_credentials($registrar);
    $domainName = strtolower(trim((string) ($domain->domain ?? '')));
    $results = [];
    $deletedCount = 0;

    foreach ($records as $index => $record) {
        if (!is_array($record)) {
            $results[] = [
                'number' => $index + 1,
                'deleted' => false,
                'message' => 'The selected record payload was invalid.',
            ];
            continue;
        }

        $type = strtoupper(trim(substr((string) ($record['type'] ?? ''), 0, 10)));
        $host = trim(substr((string) ($record['hostname'] ?? ''), 0, 255));
        $value = trim(substr((string) ($record['value'] ?? ''), 0, 2048));
        $priority = trim(substr((string) ($record['priority'] ?? ''), 0, 20));
        $recordId = trim(substr((string) ($record['recordId'] ?? ''), 0, 255));

        $result = [
            'number' => $index + 1,
            'type' => $type,
            'hostname' => $host,
            'value' => $value,
            'deleted' => false,
            'message' => '',
            'apiEndpoint' => '',
        ];

        if (!in_array($type, ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SRV'], true) || $value === '') {
            $result['message'] = 'The selected DNS record type or value was invalid.';
            $results[] = $result;
            continue;
        }

        [$found, $liveRecord, $findError] = dm_register_dns_delete_1512_live_record(
            $domainName,
            $type,
            $host,
            $value,
            $priority,
            (string) ($record['ttl'] ?? ''),
            $recordId,
            $credentials
        );

        if (!$found || !is_array($liveRecord)) {
            $result['message'] = $findError ?: 'The live DNS record could not be verified.';
            $results[] = $result;
            continue;
        }

        [$deleted, $deleteMessage, $endpointName] = dm_register_dns_delete_1512_delete_live_record(
            $domainName,
            $type,
            $liveRecord,
            $credentials
        );

        $result['deleted'] = $deleted;
        $result['message'] = $deleteMessage;
        $result['apiEndpoint'] = $endpointName;
        if ($deleted) {
            $deletedCount++;
        }
        $results[] = $result;
    }

    $attemptedCount = count($records);
    $allDeleted = $deletedCount === $attemptedCount;
    $statusCode = $allDeleted ? 200 : ($deletedCount > 0 ? 207 : 422);

    dm_register_dns_delete_1512_json([
        'success' => $allDeleted,
        'partialSuccess' => !$allDeleted && $deletedCount > 0,
        'deletedCount' => $deletedCount,
        'attemptedCount' => $attemptedCount,
        'message' => $allDeleted
            ? ($deletedCount === 1 ? 'DNS record deleted.' : $deletedCount . ' DNS records deleted.')
            : ($deletedCount > 0
                ? $deletedCount . ' of ' . $attemptedCount . ' DNS records were deleted.'
                : 'No DNS records were deleted.'),
        'results' => $results,
    ], $statusCode);
}

add_hook('ClientAreaFooterOutput', 1512, function ($vars) {
    $scriptName = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

    $nativeRoute = $scriptName === 'clientarea.php'
        && $action === 'domaindns'
        && !isset($_REQUEST['dmplainnative'])
        && !isset($_REQUEST['dmnativeold'])
        && !isset($_REQUEST['dmfeednative']);
    $legacyRoute = $scriptName === 'dnsmanagement.php'
        && $action === 'managednszone'
        && !isset($_REQUEST['dmlegacydns'])
        && !isset($_REQUEST['dmnoredirect']);

    if (!$nativeRoute && !$legacyRoute) {
        return '';
    }

    $domainId = (int) ($_GET['domainid'] ?? $_POST['domainid'] ?? $_GET['id'] ?? $_POST['id'] ?? 0);
    if ($domainId <= 0) {
        return '';
    }

    $sessionToken = dm_register_dns_delete_1512_session_token();
    $domainIdJson = json_encode($domainId);
    $sessionTokenJson = json_encode($sessionToken);

    return <<<HTML
<style id="dm-register-dns-direct-delete-1512-css">
.dm-dns-delete-result-1512 {
    background: #fff8df;
    border: 1px solid #e3c66d;
    border-radius: 5px;
    box-sizing: border-box;
    clear: both;
    color: #263746;
    display: block;
    margin: 8px 0 10px;
    max-width: 100%;
    padding: 9px 11px;
    width: 100%;
}
.dm-dns-delete-result-1512.dm-error {
    background: #f8e7e7;
    border-color: #d7a3a2;
    color: #742e2c;
}
.dm-dns-delete-result-1512 strong {
    display: block;
    margin-bottom: 4px;
}
.dm-dns-delete-result-1512 ul {
    margin: 5px 0 0 18px;
    padding: 0;
}
</style>
<script id="dm-register-dns-direct-delete-1512-js">
(function () {
    'use strict';

    if (window.dmRegisterDnsDirectDelete1512Bound) {
        return;
    }
    window.dmRegisterDnsDirectDelete1512Bound = true;

    var fallbackDomainId = {$domainIdJson};
    var deleteToken = {$sessionTokenJson};

    function textValue(field) {
        return field ? String(field.value || '').trim() : '';
    }

    function safeRecord(row, index) {
        return {
            number: index + 1,
            recordId: textValue(row.querySelector('input[name="dnsrecid[]"]')),
            type: textValue(row.querySelector('select[name="dnsrecordtype[]"], input[name="dnsrecordtype[]"]')),
            hostname: textValue(row.querySelector('input[name="dnsrecordhost[]"]')),
            value: textValue(row.querySelector('input[name="dnsrecordaddress[]"], textarea[name="dnsrecordaddress[]"]')),
            priority: textValue(row.querySelector('input[name="dnsrecordpriority[]"]')),
            ttl: textValue(row.querySelector('input[name="dnsrecordttl[]"]')),
            port: textValue(row.querySelector('input[name="dnsrecordport[]"], input[name="dnsrecordserviceport[]"]')),
            weight: textValue(row.querySelector('input[name="dnsrecordweight[]"]'))
        };
    }

    function findApplyButton(target) {
        if (!target || !target.closest) { return null; }
        var direct = target.closest('[data-dm-dns-bulk-apply]');
        if (direct) { return direct; }

        var button = target.closest('button, input[type="button"], input[type="submit"]');
        if (!button || !button.closest('.dm-dns-bulk-toolbar-1139')) { return null; }
        var label = button.tagName.toLowerCase() === 'input' ? button.value : button.textContent;
        return String(label || '').replace(/\s+/g, ' ').trim().toLowerCase() === 'apply' ? button : null;
    }

    function deleteContext(applyButton) {
        var toolbar = applyButton.closest('.dm-dns-bulk-toolbar-1139');
        if (!toolbar) { return null; }

        var action = toolbar.querySelector('[data-dm-dns-bulk-action]');
        if (!action || String(action.value || '').toLowerCase() !== 'delete') {
            return null;
        }

        var form = toolbar.closest('form') || document.querySelector('form[data-dm-dns-enhanced1139="1"], form');
        var rows = form ? Array.prototype.slice.call(form.querySelectorAll('tbody tr')) : [];
        var selectedRows = rows.filter(function (row) {
            var checkbox = row.querySelector('.dm-dns-row-select');
            return checkbox && checkbox.checked && !checkbox.disabled;
        });

        return {
            toolbar: toolbar,
            form: form,
            records: selectedRows.map(safeRecord)
        };
    }

    function findDomainId(form) {
        var params = new URLSearchParams(window.location.search || '');
        var value = params.get('domainid') || params.get('id') || '';
        if (!value && form) {
            var field = form.querySelector('input[name="domainid"], input[name="domain_id"], input[name="id"]');
            value = field ? field.value : '';
        }
        var parsed = parseInt(value || fallbackDomainId || '0', 10);
        return isFinite(parsed) && parsed > 0 ? parsed : 0;
    }

    function resultScope(toolbar) {
        return toolbar.closest('form') || toolbar.parentNode || document;
    }

    function resultAnchor(toolbar) {
        var form = toolbar.closest('form');
        if (form) {
            return form.querySelector('.dm-dns-top-controls-1499') || toolbar;
        }
        return toolbar;
    }

    function clearResult(toolbar) {
        var scope = resultScope(toolbar);
        Array.prototype.slice.call(scope.querySelectorAll('.dm-dns-delete-result-1512')).forEach(function (existing) {
            if (existing.parentNode) {
                existing.parentNode.removeChild(existing);
            }
        });
    }

    function showResult(toolbar, payload, isError) {
        clearResult(toolbar);
        var box = document.createElement('div');
        box.className = 'dm-dns-delete-result-1512' + (isError ? ' dm-error' : '');
        box.setAttribute('role', isError ? 'alert' : 'status');

        var title = document.createElement('strong');
        title.textContent = isError ? 'DNS record was not deleted' : 'DNS record deleted';
        box.appendChild(title);

        var message = document.createElement('div');
        message.textContent = String(payload && payload.message || (isError ? 'The delete request failed.' : 'The delete request completed.'));
        box.appendChild(message);

        var failed = payload && Array.isArray(payload.results)
            ? payload.results.filter(function (item) { return !item.deleted; })
            : [];
        if (failed.length) {
            var list = document.createElement('ul');
            failed.forEach(function (item) {
                var line = document.createElement('li');
                var label = [item.type, item.hostname, item.value].filter(Boolean).join(' | ');
                line.textContent = (label ? label + ': ' : '') + String(item.message || 'Delete failed.');
                list.appendChild(line);
            });
            box.appendChild(list);
        }

        var anchor = resultAnchor(toolbar);
        if (anchor && anchor.parentNode) {
            anchor.parentNode.insertBefore(box, anchor.nextSibling);
        } else {
            toolbar.appendChild(box);
        }
    }

    function runDelete(context, applyButton) {
        var toolbar = context.toolbar;
        var status = toolbar.querySelector('.dm-dns-bulk-status-1139');
        var domainId = findDomainId(context.form);
        var endpoint = new URL(window.location.href);
        endpoint.searchParams.set('dmdnsdelete1512', '1');
        endpoint.hash = '';

        var body = new FormData();
        body.append('dm_register_dns_direct_delete_1512', '1');
        body.append('domain_id', String(domainId));
        body.append('dm_delete_token_1512', deleteToken);
        body.append('selected_record_count', String(context.records.length));
        context.records.forEach(function (record) {
            body.append('selected_record_id[]', String(record.recordId || ''));
            body.append('selected_record_type[]', String(record.type || ''));
            body.append('selected_record_hostname[]', String(record.hostname || ''));
            body.append('selected_record_value[]', String(record.value || ''));
            body.append('selected_record_priority[]', String(record.priority || ''));
            body.append('selected_record_ttl[]', String(record.ttl || ''));
            body.append('selected_record_port[]', String(record.port || ''));
            body.append('selected_record_weight[]', String(record.weight || ''));
        });

        clearResult(toolbar);
        applyButton.disabled = true;
        if (status) { status.textContent = 'Deleting selected DNS record' + (context.records.length === 1 ? '…' : 's…'); }

        fetch(endpoint.toString(), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body,
            cache: 'no-store',
            redirect: 'follow'
        }).then(function (response) {
            return response.text().then(function (text) {
                var payload;
                try {
                    payload = JSON.parse(text);
                } catch (error) {
                    throw new Error('The server returned an invalid delete response (HTTP ' + response.status + ').');
                }
                return { response: response, payload: payload };
            });
        }).then(function (result) {
            var payload = result.payload || {};
            if (payload.success) {
                if (status) { status.textContent = String(payload.message || 'DNS record deleted.') + ' Refreshing…'; }
                window.setTimeout(function () {
                    window.location.reload();
                }, 700);
                return;
            }

            applyButton.disabled = false;
            if (status) { status.textContent = String(payload.message || 'The DNS record was not deleted.'); }
            showResult(toolbar, payload, true);
        }).catch(function (error) {
            applyButton.disabled = false;
            var message = String(error && error.message || error || 'The delete request failed.');
            if (status) { status.textContent = message; }
            showResult(toolbar, { message: message, results: [] }, true);
        });
    }

    window.addEventListener('click', function (event) {
        var applyButton = findApplyButton(event.target);
        if (!applyButton) { return; }

        var context = deleteContext(applyButton);
        if (!context) { return; }

        // Capture before the older toolbar handler can blank rows or submit the
        // full DNS form.
        event.preventDefault();
        event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') {
            event.stopImmediatePropagation();
        }

        var status = context.toolbar.querySelector('.dm-dns-bulk-status-1139');
        if (!context.records.length) {
            if (status) { status.textContent = 'Select at least one DNS record.'; }
            return;
        }

        var label = context.records.length === 1 ? 'DNS record' : 'DNS records';
        if (!window.confirm('Are you sure you want to delete ' + context.records.length + ' selected ' + label + '?')) {
            if (status) { status.textContent = 'Deletion canceled.'; }
            return;
        }

        runDelete(context, applyButton);
    }, true);
}());
</script>
HTML;
});
