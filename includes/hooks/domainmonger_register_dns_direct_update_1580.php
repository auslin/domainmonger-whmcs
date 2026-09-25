<?php
/**
 * DomainMonger WHMCS v9 — Register DNS Direct Update 1580
 *
 * Purpose:
 * - Prevent an edit to one Register DNS row from changing unrelated records.
 * - Bypass the native full-zone SaveDNS submission for existing-record edits.
 * - Fresh-read and uniquely match each original live record before updating it
 *   through the LogicBoxes record-type update endpoint.
 *
 * Scope:
 * - clientarea.php?action=domaindns and the converted legacy DNS route.
 * - Existing A, AAAA, CNAME, MX, NS, TXT, and SRV records only.
 * - Add-only saves continue through the confirmed native add path.
 *
 * Safety:
 * - Authenticated client, domain ownership, registrar, and session-token checks.
 * - Synthetic dm1487 IDs are never trusted as registrar record IDs.
 * - All changed rows are validated against a fresh complete live read before
 *   the first update is attempted.
 * - Ambiguous, missing, stale, host-changed, or type-changed rows are rejected.
 * - The complete DNS form is never submitted for an existing-record edit.
 * - Timeout recovery verifies the desired live value before any retry.
 * - A retry is attempted only when the original live record still exists uniquely.
 * - Success is returned only after a fresh live verification.
 * - No database changes; no language or integration-folder changes.
 * - Patch 1623: TXT uses real registrar record ID first, then a unique canonical value fallback.
 * - Patch 1624: send TXT new-value as registrar content, not DNS display quotes.
 * - Patch 1632: move a record to a new Host by verified add-then-delete, never by a full-zone save.
 * - Patch 1685: provides a page-background, verified MX add endpoint without changing the visible DNS interface.
 * - Patch 1686: allows MX and standard new record types in one verified add batch.
 * - Patch 1849: accepts standalone verified A, AAAA, CNAME, MX, NS, and TXT
 *   add batches from the RegistrarDNS editor, removing their dependency on
 *   WHMCS full-zone SaveDNS reconciliation.
 * - Patch 1851: provides an authenticated live-TTL feed so the RegistrarDNS
 *   table uses each record's current NEO value instead of a template default.
 * - Patch 1852: reads a record's exact TTL from DomainMonger's authoritative
 *   DNS servers when the LogicBoxes search response omits it, and uses the
 *   same authoritative read to verify TTL updates before reporting success.
 * - Patch 1853: sends an empty LogicBoxes host for apex records, validates
 *   submitted TTLs, and gives authoritative DNS additional time to confirm a
 *   TTL change before reporting that the update failed.
 * - Patch 1856: inspects nested NEO management-record fields for TTL and adds
 *   an authenticated, read-only diagnostic for the exact search-records row.
 *   The diagnostic never returns registrar credentials.
 * - Patch 1857: enforces LogicBoxes/NEO's 7200-second minimum TTL on
 *   verified direct add and update requests before any registrar write or
 *   live-record matching can occur. Existing record-ID safety is unchanged.
 * - Patch 1858: treats LogicBoxes/NEO HTTP 500 responses containing an upstream
 *   502 Bad Gateway/SOAP bad-response error as an ambiguous transport failure.
 *   The existing verify-before-one-retry safety path is used; no blind retry is added.
 * - Patch 1868: normalizes client-supplied TXT presentation quote layers once
 *   at request ingress so the same canonical desired value is used for the
 *   registrar write and verification. Blocks apex/root CNAME writes before any
 *   registrar call. Existing record-ID matching and retry safety are unchanged.
 * - Patch 1881: recognizes legacy TXT rows created during the former duplicate-
 *   quote bug, including HTML-entity and escaped presentation variants. The
 *   existing live-TTL background pass safely repairs a uniquely matched legacy
 *   row in place and preserves its TTL, then the client reloads the cleaned row.
 *   The real RecordID-first branch in match_live() is unchanged.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm_register_dns_update_1580_json')) {
    function dm_register_dns_update_1580_json(array $payload, int $statusCode = 200): void
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

if (!function_exists('dm_register_dns_update_1580_session_token')) {
    function dm_register_dns_update_1580_session_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_start();
        }

        $key = 'dm_register_dns_update_token_1580';
        if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
            try {
                $_SESSION[$key] = bin2hex(random_bytes(32));
            } catch (Throwable $exception) {
                $_SESSION[$key] = hash('sha256', uniqid('dm_dns_update_1580_', true));
            }
        }

        return (string) $_SESSION[$key];
    }
}

if (!function_exists('dm_register_dns_update_1580_load_helpers')) {
    function dm_register_dns_update_1580_load_helpers(): bool
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

if (!function_exists('dm_register_dns_update_1580_pick')) {
    function dm_register_dns_update_1580_pick(array $row, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('dm_register_dns_update_1580_numeric_rows')) {
    function dm_register_dns_update_1580_numeric_rows($response): array
    {
        if (!is_array($response)) {
            return [];
        }

        $rows = [];
        foreach ($response as $key => $value) {
            if ((is_int($key) || ctype_digit((string) $key)) && is_array($value)) {
                // Diagnostic 1884: retain the API's outer numeric response key
                // for reporting only.  It is deliberately NOT treated as a
                // registrar RecordID until a live NEO case proves what it is.
                $value['_dm_response_key_1884'] = (string) $key;
                $rows[] = $value;
            }
        }

        return $rows;
    }
}

if (!function_exists('dm_register_dns_update_1580_relative_host')) {
    function dm_register_dns_update_1580_relative_host(string $host, string $domain): string
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

if (!function_exists('dm_register_dns_update_1580_fqdn_host')) {
    function dm_register_dns_update_1580_fqdn_host(string $host, string $domain): string
    {
        $host = strtolower(rtrim(trim($host), '.'));
        $domain = strtolower(rtrim(trim($domain), '.'));

        if ($host === '' || $host === '@' || $host === $domain) {
            return $domain;
        }

        $suffix = '.' . $domain;
        if ($domain !== '' && strlen($host) > strlen($suffix) && substr($host, -strlen($suffix)) === $suffix) {
            return $host;
        }

        return $host . $suffix;
    }
}

if (!function_exists('dm_register_dns_update_1580_value_key')) {
    function dm_register_dns_update_1580_value_key(string $value, string $type): string
    {
        $value = trim(str_replace(["\r\n", "\r"], "\n", $value));
        if ($type === 'TXT') {
            return $value;
        }

        if (in_array($type, ['CNAME', 'MX', 'NS', 'SRV'], true)) {
            return strtolower(rtrim($value, '.'));
        }

        return strtolower($value);
    }
}

if (!function_exists('dm_register_dns_update_1580_txt_key_1623')) {
    function dm_register_dns_update_1580_txt_key_1623(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = trim(str_replace(["\r\n", "\r"], "\n", $value));

        // Some WHMCS/registrar paths preserve escaped outer quotes.
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

if (!function_exists('dm_register_dns_update_1580_txt_decode_stable_1881')) {
    /**
     * Decode only presentation/entity wrappers, with a hard pass limit so a
     * legacy TXT row that crossed more than one HTML-escaping layer can still
     * be compared safely. This helper never selects or updates a row by itself.
     */
    function dm_register_dns_update_1580_txt_decode_stable_1881(string $value): string
    {
        $value = trim($value);
        for ($pass = 0; $pass < 4; $pass++) {
            $decoded = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($decoded === $value) {
                break;
            }
            $value = $decoded;
        }
        return $value;
    }
}

if (!function_exists('dm_register_dns_update_1580_txt_edge_quote_token_1881')) {
    /** Return one presentation quote token from the requested edge. */
    function dm_register_dns_update_1580_txt_edge_quote_token_1881(string $value, bool $fromStart): string
    {
        if ($fromStart) {
            if (substr($value, 0, 2) === '\\"') {
                return '\\"';
            }
            return substr($value, 0, 1) === '"' ? '"' : '';
        }

        if (substr($value, -2) === '\\"') {
            return '\\"';
        }
        return substr($value, -1) === '"' ? '"' : '';
    }
}

if (!function_exists('dm_register_dns_update_1580_txt_legacy_duplicate_1881')) {
    /**
     * Recognize the historical duplicate complete outer presentation layer,
     * for example ""value"" or \\"\\"value\\"\\". Embedded quotes are not
     * treated as a legacy wrapper. Returns [recognized, canonicalContent].
     */
    function dm_register_dns_update_1580_txt_legacy_duplicate_1881(string $value): array
    {
        $candidate = dm_register_dns_update_1580_txt_decode_stable_1881($value);
        if ($candidate === '') {
            return [false, ''];
        }

        $left1 = dm_register_dns_update_1580_txt_edge_quote_token_1881($candidate, true);
        $right1 = dm_register_dns_update_1580_txt_edge_quote_token_1881($candidate, false);
        if ($left1 === '' || $right1 === '' || $left1 !== $right1) {
            return [false, ''];
        }

        $innerLength = strlen($candidate) - strlen($left1) - strlen($right1);
        if ($innerLength <= 0) {
            return [false, ''];
        }
        $inner = substr($candidate, strlen($left1), $innerLength);

        $left2 = dm_register_dns_update_1580_txt_edge_quote_token_1881($inner, true);
        $right2 = dm_register_dns_update_1580_txt_edge_quote_token_1881($inner, false);
        if ($left2 === '' || $right2 === '' || $left2 !== $right2 || $left2 !== $left1) {
            return [false, ''];
        }

        // Remove exactly one duplicated presentation layer, then require the
        // established strict parser to validate the remaining normal layer.
        $strictKey = dm_register_dns_update_1580_txt_key_1623($inner);
        if ($strictKey === $inner && $inner !== '' && $inner[0] === '"') {
            return [false, ''];
        }

        return [true, $strictKey];
    }
}

if (!function_exists('dm_register_dns_update_1580_txt_match_key_1880')) {
    /**
     * Build a comparison-only TXT key for live-record identity checks.
     *
     * LogicBoxes/NEO can occasionally expose an existing TXT value through
     * search-records with one extra complete presentation-quote layer even
     * though the control panel renders the normal single-quoted value.  Peel
     * only complete outer quote layers and then require the existing strict
     * TXT parser to validate the remainder.  This helper is never submitted
     * to the registrar and is never used as a replacement for a real record
     * ID.  Ambiguous matches are still rejected by match_live().
     */
    function dm_register_dns_update_1580_txt_match_key_1880(string $value): string
    {
        [$legacyDuplicate, $legacyContent] = dm_register_dns_update_1580_txt_legacy_duplicate_1881($value);
        if ($legacyDuplicate) {
            return $legacyContent;
        }

        $decoded = dm_register_dns_update_1580_txt_decode_stable_1881($value);
        $key = dm_register_dns_update_1580_txt_key_1623($decoded);

        // Existing canonical forms (plain, normally quoted, quoted chunks,
        // escaped outer quotes) are already handled by the 1623 parser.
        if ($decoded === '' || $key !== $decoded || $decoded[0] !== '"') {
            return $key;
        }

        // If the strict parser could not consume the value, allow only the
        // narrow representation quirk where the whole TXT presentation has
        // been wrapped in another complete quote pair.  Repeat a few times to
        // cover records that were saved more than once by a quoting layer.
        $candidate = $decoded;
        for ($layer = 0; $layer < 4; $layer++) {
            if (strlen($candidate) < 4
                || $candidate[0] !== '"'
                || substr($candidate, -1) !== '"') {
                break;
            }

            $candidate = trim(substr($candidate, 1, -1));
            $candidateKey = dm_register_dns_update_1580_txt_key_1623($candidate);
            if ($candidate === '') {
                return $candidateKey;
            }

            if ($candidateKey !== $candidate || $candidate[0] !== '"') {
                return $candidateKey;
            }
        }

        // Preserve the original strict key when no safe representation-only
        // normalization could be proven.
        return $key;
    }
}

if (!function_exists('dm_register_dns_update_1580_txt_submission_value_1868')) {
    function dm_register_dns_update_1580_txt_submission_value_1868(string $value): array
    {
        $decoded = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $content = dm_register_dns_update_1580_txt_key_1623($decoded);

        // Standard quoted DNS presentation (and ordinary unquoted TXT) is
        // handled by the existing canonicalizer. This helper is submission-
        // only and is never used to identify the original live record.
        if ($decoded === '' || $decoded[0] !== '"' || $content !== $decoded) {
            return [true, $content, ''];
        }

        // Clients sometimes paste an already-quoted TXT value into a field
        // whose presentation layer adds another quote pair. Peel only complete
        // duplicate outer layers, then require the existing TXT parser to
        // validate the remaining DNS presentation. Embedded quotes are kept.
        $candidate = $decoded;
        for ($layer = 0; $layer < 4; $layer++) {
            if (strlen($candidate) < 4
                || substr($candidate, 0, 2) !== '""'
                || substr($candidate, -2) !== '""') {
                break;
            }

            $candidate = trim(substr($candidate, 1, -1));
            $content = dm_register_dns_update_1580_txt_key_1623($candidate);
            if ($candidate === '' || $candidate[0] !== '"' || $content !== $candidate) {
                return [true, $content, ''];
            }
        }

        return [false, '', 'The TXT value could not be normalized safely.'];
    }
}

if (!function_exists('dm_register_dns_update_1580_priority_key')) {
    function dm_register_dns_update_1580_priority_key(string $priority, string $type): string
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

if (!function_exists('dm_register_dns_update_1580_ttl_key')) {
    function dm_register_dns_update_1580_ttl_key($ttl): string
    {
        return is_numeric($ttl) && (int) $ttl > 0 ? (string) ((int) $ttl) : '';
    }
}

if (!function_exists('dm_register_dns_ttl_1852_from_row')) {
    function dm_register_dns_ttl_1852_from_row(array $row): string
    {
        $ttlKeyNames = [
            'ttl',
            'timetolive',
            'recordttl',
            'dnsttl',
        ];
        $ttlValues = [];

        $walk = static function ($value, int $depth = 0) use (&$walk, &$ttlValues, $ttlKeyNames): void {
            if (!is_array($value) || $depth > 8) {
                return;
            }

            foreach ($value as $key => $nestedValue) {
                $normalizedKey = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', (string) $key));
                if (in_array($normalizedKey, $ttlKeyNames, true)) {
                    $ttl = dm_register_dns_update_1580_ttl_key($nestedValue);
                    if ($ttl !== '') {
                        $ttlValues[$ttl] = true;
                    }
                }

                if (is_array($nestedValue)) {
                    $walk($nestedValue, $depth + 1);
                }
            }
        };

        $walk($row);

        // A management row must expose one unambiguous TTL. Never choose one
        // when multiple TTL-like values are present in the same NEO response.
        return count($ttlValues) === 1 ? (string) array_key_first($ttlValues) : '';
    }
}

if (!function_exists('dm_register_dns_neo_diag_1856_sanitize')) {
    function dm_register_dns_neo_diag_1856_sanitize($value, int $depth = 0)
    {
        if ($depth > 8) {
            return '[depth limit]';
        }

        if (is_array($value)) {
            $clean = [];
            $itemCount = 0;
            foreach ($value as $key => $nestedValue) {
                if (++$itemCount > 100) {
                    $clean['__truncated__'] = true;
                    break;
                }

                $keyText = (string) $key;
                if (preg_match('/(?:api.?key|password|secret|token|auth)/i', $keyText)) {
                    $clean[$key] = '[redacted]';
                    continue;
                }

                $clean[$key] = dm_register_dns_neo_diag_1856_sanitize($nestedValue, $depth + 1);
            }
            return $clean;
        }

        if (is_object($value)) {
            return dm_register_dns_neo_diag_1856_sanitize((array) $value, $depth + 1);
        }

        if (is_string($value) && strlen($value) > 1000) {
            return substr($value, 0, 1000) . '[truncated]';
        }

        return $value;
    }
}

if (!function_exists('dm_register_dns_ttl_1852_encode_name')) {
    function dm_register_dns_ttl_1852_encode_name(string $name): string
    {
        $name = strtolower(rtrim(trim($name), '.'));
        if ($name === '' || strlen($name) > 253) {
            return '';
        }

        $wire = '';
        foreach (explode('.', $name) as $label) {
            $length = strlen($label);
            if ($length < 1
                || $length > 63
                || !preg_match('/^[a-z0-9_*\-]+$/iD', $label)) {
                return '';
            }
            $wire .= chr($length) . $label;
        }

        return $wire . "\0";
    }
}

if (!function_exists('dm_register_dns_ttl_1852_decode_name')) {
    function dm_register_dns_ttl_1852_decode_name(string $packet, int &$offset): array
    {
        $packetLength = strlen($packet);
        $cursor = $offset;
        $labels = [];
        $jumped = false;
        $seenPointers = [];

        for ($steps = 0; $steps < 128; $steps++) {
            if ($cursor < 0 || $cursor >= $packetLength) {
                return [false, ''];
            }

            $length = ord($packet[$cursor]);
            if (($length & 0xC0) === 0xC0) {
                if ($cursor + 1 >= $packetLength) {
                    return [false, ''];
                }
                $pointer = (($length & 0x3F) << 8) | ord($packet[$cursor + 1]);
                if ($pointer >= $packetLength || isset($seenPointers[$pointer])) {
                    return [false, ''];
                }
                $seenPointers[$pointer] = true;
                if (!$jumped) {
                    $offset = $cursor + 2;
                    $jumped = true;
                }
                $cursor = $pointer;
                continue;
            }

            if (($length & 0xC0) !== 0) {
                return [false, ''];
            }

            $cursor++;
            if ($length === 0) {
                if (!$jumped) {
                    $offset = $cursor;
                }
                return [true, strtolower(implode('.', $labels))];
            }

            if ($length > 63 || $cursor + $length > $packetLength) {
                return [false, ''];
            }
            $labels[] = substr($packet, $cursor, $length);
            $cursor += $length;
        }

        return [false, ''];
    }
}

if (!function_exists('dm_register_dns_ttl_1852_read_exact')) {
    function dm_register_dns_ttl_1852_read_exact($stream, int $length): string
    {
        $result = '';
        while (strlen($result) < $length && !feof($stream)) {
            $part = @fread($stream, $length - strlen($result));
            if (!is_string($part) || $part === '') {
                break;
            }
            $result .= $part;
        }
        return $result;
    }
}

if (!function_exists('dm_register_dns_ttl_1852_write_all')) {
    function dm_register_dns_ttl_1852_write_all($stream, string $payload): bool
    {
        $offset = 0;
        $length = strlen($payload);
        while ($offset < $length) {
            $written = @fwrite($stream, substr($payload, $offset));
            if (!is_int($written) || $written < 1) {
                return false;
            }
            $offset += $written;
        }
        return true;
    }
}

if (!function_exists('dm_register_dns_ttl_1852_value_key')) {
    function dm_register_dns_ttl_1852_value_key(string $value, string $type): string
    {
        $value = trim($value);
        if ($type === 'A' || $type === 'AAAA') {
            $packed = @inet_pton($value);
            return $packed === false ? strtolower($value) : bin2hex($packed);
        }
        if ($type === 'TXT') {
            return dm_register_dns_update_1580_txt_key_1623($value);
        }
        return strtolower(rtrim($value, '.'));
    }
}

if (!function_exists('dm_register_dns_ttl_1852_parse_response')) {
    function dm_register_dns_ttl_1852_parse_response(
        string $packet,
        int $queryId,
        string $fqdn,
        string $type,
        string $expectedValue,
        string $expectedPriority
    ): array {
        if (strlen($packet) < 12) {
            return [false, '', 'The authoritative DNS response was incomplete.'];
        }

        $header = unpack('nid/nflags/nqdcount/nancount/nnscount/narcount', substr($packet, 0, 12));
        if (!is_array($header)
            || (int) ($header['id'] ?? -1) !== $queryId
            || (((int) ($header['flags'] ?? 0)) & 0x8000) === 0) {
            return [false, '', 'The authoritative DNS response could not be validated.'];
        }

        $flags = (int) $header['flags'];
        if (($flags & 0x000F) !== 0) {
            return [false, '', 'The authoritative DNS server did not return this record.'];
        }

        $qtypeMap = [
            'A' => 1,
            'NS' => 2,
            'CNAME' => 5,
            'MX' => 15,
            'TXT' => 16,
            'AAAA' => 28,
            'SRV' => 33,
        ];
        $qtype = $qtypeMap[$type] ?? 0;
        if ($qtype === 0) {
            return [false, '', 'This DNS type does not support authoritative TTL verification.'];
        }

        $offset = 12;
        for ($question = 0; $question < (int) ($header['qdcount'] ?? 0); $question++) {
            [$nameOk] = dm_register_dns_ttl_1852_decode_name($packet, $offset);
            if (!$nameOk || $offset + 4 > strlen($packet)) {
                return [false, '', 'The authoritative DNS question could not be parsed.'];
            }
            $offset += 4;
        }

        $fqdnKey = strtolower(rtrim($fqdn, '.'));
        $records = [];
        $sections = [
            ['answer', (int) ($header['ancount'] ?? 0)],
            ['authority', (int) ($header['nscount'] ?? 0)],
            ['additional', (int) ($header['arcount'] ?? 0)],
        ];

        foreach ($sections as [$section, $count]) {
            for ($recordIndex = 0; $recordIndex < $count; $recordIndex++) {
                [$ownerOk, $owner] = dm_register_dns_ttl_1852_decode_name($packet, $offset);
                if (!$ownerOk || $offset + 10 > strlen($packet)) {
                    return [false, '', 'An authoritative DNS record could not be parsed.'];
                }

                $fixed = unpack('ntype/nclass/Nttl/nlength', substr($packet, $offset, 10));
                $offset += 10;
                $rdLength = (int) ($fixed['length'] ?? -1);
                $rdataOffset = $offset;
                $rdataEnd = $rdataOffset + $rdLength;
                if ($rdLength < 0 || $rdataEnd > strlen($packet)) {
                    return [false, '', 'An authoritative DNS record value was incomplete.'];
                }
                $offset = $rdataEnd;

                $recordType = (int) ($fixed['type'] ?? 0);
                $recordClass = (int) ($fixed['class'] ?? 0);
                if ($recordType !== $qtype
                    || $recordClass !== 1
                    || strtolower(rtrim($owner, '.')) !== $fqdnKey
                    || ($section !== 'answer' && !($type === 'NS' && $section === 'authority'))) {
                    continue;
                }

                $value = '';
                $priority = '';
                if ($type === 'A' && $rdLength === 4) {
                    $value = (string) @inet_ntop(substr($packet, $rdataOffset, 4));
                } elseif ($type === 'AAAA' && $rdLength === 16) {
                    $value = (string) @inet_ntop(substr($packet, $rdataOffset, 16));
                } elseif ($type === 'CNAME' || $type === 'NS') {
                    $valueOffset = $rdataOffset;
                    [$valueOk, $value] = dm_register_dns_ttl_1852_decode_name($packet, $valueOffset);
                    if (!$valueOk || $valueOffset > $rdataEnd) {
                        $value = '';
                    }
                } elseif ($type === 'MX' && $rdLength >= 3) {
                    $mx = unpack('npriority', substr($packet, $rdataOffset, 2));
                    $priority = (string) ((int) ($mx['priority'] ?? 0));
                    $valueOffset = $rdataOffset + 2;
                    [$valueOk, $value] = dm_register_dns_ttl_1852_decode_name($packet, $valueOffset);
                    if (!$valueOk || $valueOffset > $rdataEnd) {
                        $value = '';
                    }
                } elseif ($type === 'TXT') {
                    $valueOffset = $rdataOffset;
                    while ($valueOffset < $rdataEnd) {
                        $partLength = ord($packet[$valueOffset]);
                        $valueOffset++;
                        if ($valueOffset + $partLength > $rdataEnd) {
                            $value = '';
                            break;
                        }
                        $value .= substr($packet, $valueOffset, $partLength);
                        $valueOffset += $partLength;
                    }
                } elseif ($type === 'SRV' && $rdLength >= 7) {
                    $srv = unpack('npriority/nweight/nport', substr($packet, $rdataOffset, 6));
                    $priority = (string) ((int) ($srv['priority'] ?? 0));
                    $valueOffset = $rdataOffset + 6;
                    [$valueOk, $value] = dm_register_dns_ttl_1852_decode_name($packet, $valueOffset);
                    if (!$valueOk || $valueOffset > $rdataEnd) {
                        $value = '';
                    }
                }

                $ttl = dm_register_dns_update_1580_ttl_key($fixed['ttl'] ?? '');
                if ($ttl !== '') {
                    $records[] = [
                        'section' => $section,
                        'ttl' => $ttl,
                        'valueKey' => dm_register_dns_ttl_1852_value_key($value, $type),
                        'priority' => $priority,
                    ];
                }
            }
        }

        if (!$records) {
            return [false, '', 'The authoritative DNS server did not return a matching record TTL.'];
        }

        $isAuthoritativeAnswer = ($flags & 0x0400) !== 0;
        $eligible = array_values(array_filter($records, static function (array $record) use ($isAuthoritativeAnswer, $type): bool {
            return $isAuthoritativeAnswer
                ? true
                : ($type === 'NS' && $record['section'] === 'authority');
        }));
        if (!$eligible) {
            return [false, '', 'The DNS response was not authoritative for this record.'];
        }

        $expectedValueKey = dm_register_dns_ttl_1852_value_key($expectedValue, $type);
        $priorityKey = dm_register_dns_update_1580_priority_key($expectedPriority, $type);
        $matching = array_values(array_filter($eligible, static function (array $record) use (
            $expectedValueKey,
            $priorityKey,
            $type
        ): bool {
            if ($expectedValueKey !== '' && $record['valueKey'] !== $expectedValueKey) {
                return false;
            }
            return !in_array($type, ['MX', 'SRV'], true)
                || $priorityKey === ''
                || $record['priority'] === $priorityKey;
        }));

        // RFC-compliant RRsets have one TTL. If presentation differences keep
        // the expected value from matching but every answer agrees, the TTL is
        // still unambiguous for this owner/type pair.
        $ttlSource = $matching ?: $eligible;
        $ttls = array_values(array_unique(array_column($ttlSource, 'ttl')));
        if (count($ttls) !== 1) {
            return [false, '', 'More than one authoritative TTL was returned for this DNS record.'];
        }

        return [true, (string) $ttls[0], ''];
    }
}

if (!function_exists('dm_register_dns_ttl_1852_tcp_query')) {
    function dm_register_dns_ttl_1852_tcp_query(string $server, string $query): array
    {
        $errno = 0;
        $error = '';
        $stream = @stream_socket_client(
            'tcp://' . $server . ':53',
            $errno,
            $error,
            1.5,
            STREAM_CLIENT_CONNECT
        );
        if (!is_resource($stream)) {
            return [false, '', 'The authoritative DNS server could not be reached.'];
        }

        @stream_set_timeout($stream, 1, 500000);
        $written = dm_register_dns_ttl_1852_write_all($stream, pack('n', strlen($query)) . $query);
        if (!$written) {
            @fclose($stream);
            return [false, '', 'The authoritative DNS query could not be sent.'];
        }

        $lengthBytes = dm_register_dns_ttl_1852_read_exact($stream, 2);
        if (strlen($lengthBytes) !== 2) {
            @fclose($stream);
            return [false, '', 'The authoritative DNS server did not answer.'];
        }
        $lengthData = unpack('nlength', $lengthBytes);
        $responseLength = (int) ($lengthData['length'] ?? 0);
        $packet = $responseLength > 0
            ? dm_register_dns_ttl_1852_read_exact($stream, $responseLength)
            : '';
        @fclose($stream);

        if ($responseLength < 12 || strlen($packet) !== $responseLength) {
            return [false, '', 'The authoritative DNS response was incomplete.'];
        }
        return [true, $packet, ''];
    }
}

if (!function_exists('dm_register_dns_ttl_1852_query_server')) {
    function dm_register_dns_ttl_1852_query_server(
        string $server,
        string $fqdn,
        string $type,
        string $expectedValue,
        string $expectedPriority,
        bool $allowCache
    ): array {
        static $packetCache = [];

        $qtypeMap = [
            'A' => 1,
            'NS' => 2,
            'CNAME' => 5,
            'MX' => 15,
            'TXT' => 16,
            'AAAA' => 28,
            'SRV' => 33,
        ];
        $qtype = $qtypeMap[$type] ?? 0;
        $wireName = dm_register_dns_ttl_1852_encode_name($fqdn);
        if ($qtype === 0 || $wireName === '') {
            return [false, '', 'The DNS record name or type could not be queried safely.'];
        }

        $cacheKey = strtolower($server . '|' . $fqdn . '|' . $type);
        if ($allowCache && isset($packetCache[$cacheKey])) {
            $queryId = (int) $packetCache[$cacheKey]['id'];
            $packet = (string) $packetCache[$cacheKey]['packet'];
        } else {
            try {
                $queryId = random_int(0, 65535);
            } catch (Throwable $exception) {
                $queryId = mt_rand(0, 65535);
            }
            $query = pack('nnnnnn', $queryId, 0, 1, 0, 0, 0)
                . $wireName
                . pack('nn', $qtype, 1);

            $errno = 0;
            $error = '';
            $stream = @stream_socket_client(
                'udp://' . $server . ':53',
                $errno,
                $error,
                1.5,
                STREAM_CLIENT_CONNECT
            );
            $packet = '';
            if (is_resource($stream)) {
                @stream_set_timeout($stream, 1, 500000);
                $written = @fwrite($stream, $query);
                if (is_int($written) && $written === strlen($query)) {
                    $read = @fread($stream, 65535);
                    $packet = is_string($read) ? $read : '';
                }
                @fclose($stream);
            }

            $truncated = false;
            if (strlen($packet) >= 4) {
                $shortHeader = unpack('nid/nflags', substr($packet, 0, 4));
                $truncated = (((int) ($shortHeader['flags'] ?? 0)) & 0x0200) !== 0;
            }

            if (strlen($packet) < 12 || $truncated) {
                [$tcpOk, $tcpPacket, $tcpError] = dm_register_dns_ttl_1852_tcp_query($server, $query);
                if (!$tcpOk) {
                    return [false, '', $tcpError ?: 'The authoritative DNS server did not answer.'];
                }
                $packet = $tcpPacket;
            }

            if ($allowCache) {
                $packetCache[$cacheKey] = ['id' => $queryId, 'packet' => $packet];
            }
        }

        return dm_register_dns_ttl_1852_parse_response(
            $packet,
            $queryId,
            $fqdn,
            $type,
            $expectedValue,
            $expectedPriority
        );
    }
}

if (!function_exists('dm_register_dns_ttl_1852_authoritative')) {
    function dm_register_dns_ttl_1852_authoritative(
        string $domain,
        string $host,
        string $type,
        string $expectedValue,
        string $expectedPriority = '',
        bool $allowCache = true
    ): array {
        static $preferredServer = '';
        static $resultCache = [];

        $type = strtoupper(trim($type));
        $fqdn = dm_register_dns_update_1580_fqdn_host($host, $domain);
        $cacheKey = strtolower(implode('|', [$fqdn, $type, $expectedValue, $expectedPriority]));
        if ($allowCache && isset($resultCache[$cacheKey])) {
            return $resultCache[$cacheKey];
        }

        $servers = [
            'ns5.domainmonger.com',
            'ns6.domainmonger.com',
            'ns7.domainmonger.com',
            'ns8.domainmonger.com',
        ];
        if ($preferredServer !== '' && in_array($preferredServer, $servers, true)) {
            $servers = array_values(array_unique(array_merge([$preferredServer], $servers)));
        }

        $lastError = 'DomainMonger authoritative DNS did not return this record TTL.';
        foreach ($servers as $server) {
            [$ok, $ttl, $error] = dm_register_dns_ttl_1852_query_server(
                $server,
                $fqdn,
                $type,
                $expectedValue,
                $expectedPriority,
                $allowCache
            );
            if ($ok) {
                $preferredServer = $server;
                $result = [true, $ttl, ''];
                if ($allowCache) {
                    $resultCache[$cacheKey] = $result;
                }
                return $result;
            }
            if (trim((string) $error) !== '') {
                $lastError = (string) $error;
            }
        }

        $result = [false, '', $lastError];
        if ($allowCache) {
            $resultCache[$cacheKey] = $result;
        }
        return $result;
    }
}

if (!function_exists('dm_register_dns_update_1580_remote_id')) {
    function dm_register_dns_update_1580_remote_id(array $row): string
    {
        return trim((string) dm_register_dns_update_1580_pick(
            $row,
            ['record-id', 'recordid', 'record_id', 'recid', 'rrid', 'entityid', 'id'],
            ''
        ));
    }
}

if (!function_exists('dm_register_dns_update_1580_fetch_type')) {
    function dm_register_dns_update_1580_fetch_type(
        string $domain,
        string $type,
        array $credentials
    ): array {
        $authUserId = trim((string) ($credentials['authUserId'] ?? ''));
        $apiKey = trim((string) ($credentials['apiKey'] ?? ''));
        if ($authUserId === '' || !ctype_digit($authUserId) || $apiKey === '') {
            return [false, [], 'Usable LogicBoxes API credentials were unavailable.'];
        }

        $base = rtrim((string) dm_epp_api_base($credentials), '/');
        $records = [];

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
                return [false, [], $error ?: 'The registrar DNS search failed.'];
            }

            $rows = dm_register_dns_update_1580_numeric_rows($response);
            foreach ($rows as $row) {
                $rowType = strtoupper(trim((string) dm_register_dns_update_1580_pick(
                    $row,
                    ['type', 'record-type', 'recordtype'],
                    $type
                )));
                if ($rowType === $type) {
                    $records[] = $row;
                }
            }

            if (count($rows) < 50) {
                break;
            }
        }

        return [true, $records, ''];
    }
}


if (!function_exists('dm_register_dns_update_1580_diag_key_1884')) {
    /**
     * Comparison diagnostics only. Never use this fingerprint to select a
     * registrar row or to submit a DNS write.
     */
    function dm_register_dns_update_1580_diag_key_1884(string $value, string $type): string
    {
        $type = strtoupper(trim($type));
        $key = $type === 'TXT'
            ? dm_register_dns_update_1580_txt_match_key_1880($value)
            : dm_register_dns_update_1580_value_key($value, $type);

        return 'len=' . strlen($key) . ',sha=' . substr(hash('sha256', $key), 0, 12);
    }
}

if (!function_exists('dm_register_dns_update_1580_diag_row_1884')) {
    /** Build a non-secret identity/comparison summary for one live API row. */
    function dm_register_dns_update_1580_diag_row_1884(array $row, string $domain, string $type): string
    {
        $hostRaw = (string) dm_register_dns_update_1580_pick($row, ['host', 'hostname', 'name'], '@');
        $host = dm_register_dns_update_1580_relative_host($hostRaw, $domain);
        $valueRaw = trim((string) dm_register_dns_update_1580_pick($row, ['value', 'address', 'target', 'data'], ''));
        $remoteId = dm_register_dns_update_1580_remote_id($row);
        $responseKey = trim((string) ($row['_dm_response_key_1884'] ?? ''));
        $ttl = dm_register_dns_ttl_1852_from_row($row);

        $idFields = [];
        foreach ($row as $key => $value) {
            $keyText = (string) $key;
            if ($keyText === '_dm_response_key_1884') {
                continue;
            }
            if (!preg_match('/(?:^|[-_])(id|rrid|recid)(?:$|[-_])/i', $keyText)
                && !preg_match('/(?:recordid|record-id|record_id|entityid)$/i', $keyText)) {
                continue;
            }
            if (is_scalar($value) && trim((string) $value) !== '') {
                $idFields[] = $keyText . '=' . substr(trim((string) $value), 0, 64);
            } else {
                $idFields[] = $keyText . '=present';
            }
        }

        $keys = [];
        foreach (array_keys($row) as $key) {
            $key = (string) $key;
            if ($key === '_dm_response_key_1884') {
                continue;
            }
            $keys[] = $key;
        }
        sort($keys, SORT_STRING);

        return 'host=' . $host
            . ', responseKey=' . ($responseKey !== '' ? $responseKey : 'none')
            . ', remoteId=' . ($remoteId !== '' ? $remoteId : 'none')
            . ', key(' . dm_register_dns_update_1580_diag_key_1884($valueRaw, $type) . ')'
            . ', ttl=' . ($ttl !== '' ? $ttl : 'none')
            . ', idFields=' . ($idFields ? implode('|', $idFields) : 'none')
            . ', fields=' . ($keys ? implode('|', array_slice($keys, 0, 20)) : 'none');
    }
}

if (!function_exists('dm_register_dns_update_1580_match_live')) {
    function dm_register_dns_update_1580_match_live(
        string $domain,
        array $change,
        array $liveRows
    ): array {
        $type = strtoupper(trim((string) ($change['type'] ?? '')));
        $hostKey = dm_register_dns_update_1580_relative_host((string) ($change['originalHost'] ?? ''), $domain);
        $valueKey = dm_register_dns_update_1580_value_key((string) ($change['originalValue'] ?? ''), $type);
        $priorityKey = dm_register_dns_update_1580_priority_key((string) ($change['originalPriority'] ?? ''), $type);
        $ttlKey = dm_register_dns_update_1580_ttl_key($change['originalTtl'] ?? '');
        $recordId = trim((string) ($change['recordId'] ?? ''));
        $hasRealRecordId = $recordId !== '' && strpos($recordId, 'dm1487:') !== 0;

        // TXT values can be re-quoted by LogicBoxes between page display and
        // the fresh safety read. A real registrar record ID is the strongest
        // identity and is checked before any value representation comparison.
        if ($type === 'TXT' && $hasRealRecordId) {
            $idMatches = [];
            foreach ($liveRows as $row) {
                $remoteId = dm_register_dns_update_1580_remote_id($row);
                if ($remoteId === '' || !hash_equals($remoteId, $recordId)) {
                    continue;
                }

                $rowHostRaw = (string) dm_register_dns_update_1580_pick($row, ['host', 'hostname', 'name'], '@');
                $rowHostKey = dm_register_dns_update_1580_relative_host($rowHostRaw, $domain);
                if ($rowHostKey !== $hostKey) {
                    return [false, null, 'The live TXT record ID now points to a different Host. Reload the page and try again.'];
                }

                $rowTtlKey = dm_register_dns_ttl_1852_from_row($row);
                if ($ttlKey !== '' && $rowTtlKey !== '' && $ttlKey !== $rowTtlKey) {
                    return [false, null, 'The live TXT record changed after this page loaded. Reload the page and try again.'];
                }

                $idMatches[] = [
                    'raw' => $row,
                    'hostRaw' => $rowHostRaw,
                    'hostRelative' => $rowHostKey,
                    'valueRaw' => trim((string) dm_register_dns_update_1580_pick($row, ['value', 'address', 'target', 'data'], '')),
                    'priority' => '',
                    'ttl' => $rowTtlKey,
                    'remoteId' => $remoteId,
                ];
            }

            if (count($idMatches) === 1) {
                return [true, $idMatches[0], ''];
            }
            if (count($idMatches) > 1) {
                return [false, null, 'More than one live TXT record returned the same registrar record ID. No update was attempted.'];
            }
        }

        $exactMatches = [];
        $txtCanonicalMatches = [];
        $submittedTxtKey = $type === 'TXT'
            ? dm_register_dns_update_1580_txt_match_key_1880((string) ($change['originalValue'] ?? ''))
            : '';
        $sameHostCount = 0;

        foreach ($liveRows as $row) {
            $rowHostRaw = (string) dm_register_dns_update_1580_pick($row, ['host', 'hostname', 'name'], '@');
            $rowHostKey = dm_register_dns_update_1580_relative_host($rowHostRaw, $domain);
            if ($rowHostKey !== $hostKey) {
                continue;
            }
            $sameHostCount++;

            $rowValueRaw = trim((string) dm_register_dns_update_1580_pick(
                $row,
                ['value', 'address', 'target', 'data'],
                ''
            ));
            $rowValueKey = dm_register_dns_update_1580_value_key($rowValueRaw, $type);
            $isExact = $rowValueKey === $valueKey;
            $isCanonicalTxt = !$isExact
                && $type === 'TXT'
                && dm_register_dns_update_1580_txt_match_key_1880($rowValueRaw) === $submittedTxtKey;
            if (!$isExact && !$isCanonicalTxt) {
                continue;
            }

            $rowPriorityKey = dm_register_dns_update_1580_priority_key(
                (string) dm_register_dns_update_1580_pick($row, ['priority', 'pref'], ''),
                $type
            );
            if ($priorityKey !== '' && $rowPriorityKey !== '' && $priorityKey !== $rowPriorityKey) {
                continue;
            }

            $rowTtlKey = dm_register_dns_ttl_1852_from_row($row);
            $candidate = [
                'raw' => $row,
                'hostRaw' => $rowHostRaw,
                'hostRelative' => $rowHostKey,
                'valueRaw' => $rowValueRaw,
                'priority' => $rowPriorityKey,
                'ttl' => $rowTtlKey,
                'remoteId' => dm_register_dns_update_1580_remote_id($row),
            ];

            if ($isExact) {
                $exactMatches[] = $candidate;
            } else {
                $txtCanonicalMatches[] = $candidate;
            }
        }

        $matches = $exactMatches ?: $txtCanonicalMatches;
        if (!$matches) {
            if ($type === 'TXT') {
                $sameHostDiag = [];
                foreach ($liveRows as $diagRow) {
                    $diagHostRaw = (string) dm_register_dns_update_1580_pick($diagRow, ['host', 'hostname', 'name'], '@');
                    if (dm_register_dns_update_1580_relative_host($diagHostRaw, $domain) !== $hostKey) {
                        continue;
                    }
                    $sameHostDiag[] = dm_register_dns_update_1580_diag_row_1884($diagRow, $domain, $type);
                }
                $submittedDiag = dm_register_dns_update_1580_diag_key_1884((string) ($change['originalValue'] ?? ''), $type);
                return [false, null, 'The original live TXT record was not found. No changes were submitted. Diagnostic 1884: Host=' . $hostKey . ', live TXT rows=' . count($liveRows) . ', same Host rows=' . $sameHostCount . ', record ID=' . ($hasRealRecordId ? 'real but unmatched' : 'synthetic/unavailable') . ', pageKey(' . $submittedDiag . '), live=[' . ($sameHostDiag ? implode(' ; ', $sameHostDiag) : 'none') . '].'];
            }
            return [false, null, 'The original live DNS record was not found. No changes were submitted.'];
        }

        if (count($matches) > 1 && $hasRealRecordId) {
            $idMatches = array_values(array_filter($matches, static function (array $match) use ($recordId): bool {
                return $match['remoteId'] !== '' && hash_equals((string) $match['remoteId'], $recordId);
            }));
            if (count($idMatches) === 1) {
                $matches = $idMatches;
            }
        }

        if (count($matches) > 1 && $ttlKey !== '') {
            $ttlMatches = array_values(array_filter($matches, static function (array $match) use ($ttlKey): bool {
                return $match['ttl'] !== '' && $match['ttl'] === $ttlKey;
            }));
            if (count($ttlMatches) === 1) {
                $matches = $ttlMatches;
            }
        }

        if (count($matches) !== 1) {
            $matchDiag = [];
            foreach ($matches as $diagMatch) {
                $diagRaw = is_array($diagMatch['raw'] ?? null) ? $diagMatch['raw'] : [];
                $matchDiag[] = dm_register_dns_update_1580_diag_row_1884($diagRaw, $domain, $type);
            }
            $submittedDiag = dm_register_dns_update_1580_diag_key_1884((string) ($change['originalValue'] ?? ''), $type);
            return [false, null, 'More than one identical live DNS record matched this row. No update was attempted. Diagnostic 1884: Host=' . $hostKey . ', matches=' . count($matches) . ', record ID=' . ($hasRealRecordId ? 'real but unmatched' : 'synthetic/unavailable') . ', pageKey(' . $submittedDiag . '), live=[' . implode(' ; ', $matchDiag) . '].'];
        }

        $match = $matches[0];
        if ($ttlKey !== '' && $match['ttl'] !== '' && $ttlKey !== $match['ttl']) {
            return [false, null, 'The live DNS record changed after this page loaded. Reload the page and try again.'];
        }

        return [true, $match, ''];
    }
}

if (!function_exists('dm_register_dns_update_1580_readonly_match_1885')) {
    /**
     * Read-only identity recovery for the background live-TTL probe.
     *
     * Normal update/delete safety continues to use match_live() unchanged.
     * This helper may recover a TXT row only for a read-only probe when the
     * registrar does not expose a real record ID:
     * - one and only one live TXT exists at the requested Host; or
     * - multiple live TXT rows at that Host are fully equivalent for value,
     *   priority and TTL, so reading their common TTL is unambiguous.
     *
     * The exact live value returned here can be retained by the browser as the
     * original identity value. Any later write is still re-read and validated
     * by the existing strict match_live() path immediately before submission.
     */
    function dm_register_dns_update_1580_readonly_match_1885(
        string $domain,
        array $probe,
        array $liveRows
    ): array {
        [$matched, $liveRecord, $matchError] = dm_register_dns_update_1580_match_live(
            $domain,
            $probe,
            $liveRows
        );
        if ($matched && is_array($liveRecord)) {
            return [true, $liveRecord, $matchError, 'strict'];
        }

        $type = strtoupper(trim((string) ($probe['type'] ?? '')));
        $recordId = trim((string) ($probe['recordId'] ?? ''));
        $hasRealRecordId = $recordId !== '' && strpos($recordId, 'dm1487:') !== 0;
        if ($hasRealRecordId) {
            return [false, null, $matchError, ''];
        }

        $hostKey = dm_register_dns_update_1580_relative_host(
            (string) ($probe['originalHost'] ?? ''),
            $domain
        );
        $candidates = [];
        foreach ($liveRows as $row) {
            $rowHostRaw = (string) dm_register_dns_update_1580_pick(
                $row,
                ['host', 'hostname', 'name'],
                '@'
            );
            $rowHostKey = dm_register_dns_update_1580_relative_host($rowHostRaw, $domain);
            if ($rowHostKey !== $hostKey) {
                continue;
            }

            $rowValueRaw = trim((string) dm_register_dns_update_1580_pick(
                $row,
                ['value', 'address', 'target', 'data'],
                ''
            ));
            $rowPriorityKey = dm_register_dns_update_1580_priority_key(
                (string) dm_register_dns_update_1580_pick($row, ['priority', 'pref'], ''),
                $type
            );
            $rowTtlKey = dm_register_dns_ttl_1852_from_row($row);
            $candidates[] = [
                'raw' => $row,
                'hostRaw' => $rowHostRaw,
                'hostRelative' => $rowHostKey,
                'valueRaw' => $rowValueRaw,
                'priority' => $rowPriorityKey,
                'ttl' => $rowTtlKey,
                'remoteId' => dm_register_dns_update_1580_remote_id($row),
            ];
        }

        if (count($candidates) === 1 && $type === 'TXT') {
            // TXT presentation can differ harmlessly between the WHMCS form
            // and NEO's management API. A single TXT row at this Host is safe
            // to use for this read-only identity/TTL probe only. Actual writes
            // still go through the unchanged strict matcher.
            return [true, $candidates[0], '', 'unique-host'];
        }

        if (count($candidates) > 1) {
            // Patch 1887: for read-only TTL loading only, duplicate rows of any
            // supported DNS type are interchangeable when the rows that match
            // the page value also agree on priority and TTL. This does NOT make
            // those rows editable/deletable when the registrar exposes no real
            // RecordID; all writes still use the unchanged strict matcher.
            $pageValueKey = $type === 'TXT'
                ? dm_register_dns_update_1580_txt_match_key_1880((string) ($probe['originalValue'] ?? ''))
                : dm_register_dns_update_1580_value_key((string) ($probe['originalValue'] ?? ''), $type);
            $pagePriorityKey = dm_register_dns_update_1580_priority_key(
                (string) ($probe['originalPriority'] ?? ''),
                $type
            );
            $equivalentCandidates = array_values(array_filter(
                $candidates,
                static function (array $candidate) use ($pageValueKey, $pagePriorityKey, $type): bool {
                    $candidateValueKey = $type === 'TXT'
                        ? dm_register_dns_update_1580_txt_match_key_1880((string) ($candidate['valueRaw'] ?? ''))
                        : dm_register_dns_update_1580_value_key((string) ($candidate['valueRaw'] ?? ''), $type);
                    if (!hash_equals($pageValueKey, $candidateValueKey)) {
                        return false;
                    }
                    if ($pagePriorityKey !== ''
                        && (string) ($candidate['priority'] ?? '') !== ''
                        && (string) ($candidate['priority'] ?? '') !== $pagePriorityKey) {
                        return false;
                    }
                    return true;
                }
            ));

            if (count($equivalentCandidates) > 1) {
                $first = $equivalentCandidates[0];
                $firstTtl = (string) ($first['ttl'] ?? '');
                $equivalent = true;
                foreach ($equivalentCandidates as $candidate) {
                    if ((string) ($candidate['ttl'] ?? '') !== $firstTtl) {
                        $equivalent = false;
                        break;
                    }
                }

                if ($equivalent) {
                    return [true, $first, '', 'equivalent-duplicates'];
                }
            }
        }

        return [false, null, $matchError, ''];
    }
}

if (!function_exists('dm_register_dns_update_1580_api_success')) {
    function dm_register_dns_update_1580_api_success($response): bool
    {
        if (is_string($response)) {
            return strtoupper(trim($response, " \t\n\r\0\x0B\"'")) === 'SUCCESS';
        }

        if (is_array($response)) {
            $status = strtoupper(trim((string) ($response['status'] ?? $response['actionstatus'] ?? '')));
            if ($status === 'SUCCESS' || $status === 'OK') {
                return true;
            }
            if (isset($response['success'])) {
                return filter_var($response['success'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $response === null || $response === '' || $response === [];
    }
}

if (!function_exists('dm_register_dns_update_1580_is_timeout_error')) {
    function dm_register_dns_update_1580_is_timeout_error(string $message): bool
    {
        if ((bool) preg_match('/(?:timed?\s*out|timeout|operation\s+timed\s+out)/i', $message)) {
            return true;
        }

        // Patch 1858: LogicBoxes can wrap an upstream 502 Bad Gateway in an
        // HTTP 500 SOAPException. That response is ambiguous in the same way
        // as a timeout: the registrar may have received/applied the request
        // before the gateway failed. Reuse the existing verify-before-retry
        // path rather than reporting failure immediately or retrying blindly.
        return (bool) preg_match(
            '/(?:HTTP\s*500.*(?:502\s*Bad\s*Gateway|502Bad\s*Gateway)|SOAPException.*Bad\s*response.*502)/is',
            $message
        );
    }
}

if (!function_exists('dm_register_dns_update_1580_verify_live_with_retries')) {
    function dm_register_dns_update_1580_verify_live_with_retries(
        string $domain,
        array $change,
        array $credentials,
        array $delaysMs = [0, 1000, 2500, 5000]
    ): array {
        $lastError = 'The updated live DNS record could not be verified.';

        foreach ($delaysMs as $delayMs) {
            $delayMs = max(0, (int) $delayMs);
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }

            [$verified, $verifyError] = dm_register_dns_update_1580_verify_live(
                $domain,
                $change,
                $credentials
            );
            if ($verified) {
                return [true, ''];
            }
            if (trim((string) $verifyError) !== '') {
                $lastError = (string) $verifyError;
            }
        }

        return [false, $lastError];
    }
}

if (!function_exists('dm_register_dns_update_1580_update_live')) {
    function dm_register_dns_update_1580_update_live(
        string $domain,
        array $change,
        array $liveRecord,
        array $credentials
    ): array {
        $type = strtoupper(trim((string) ($change['type'] ?? '')));
        $endpointMap = [
            'A' => 'update-ipv4-record.json',
            'AAAA' => 'update-ipv6-record.json',
            'CNAME' => 'update-cname-record.json',
            'MX' => 'update-mx-record.json',
            'NS' => 'update-ns-record.json',
            'TXT' => 'update-txt-record.json',
            'SRV' => 'update-srv-record.json',
        ];

        if (!isset($endpointMap[$type])) {
            return [false, 'This DNS record type is not supported for direct editing.', ''];
        }

        $authUserId = trim((string) ($credentials['authUserId'] ?? ''));
        $apiKey = trim((string) ($credentials['apiKey'] ?? ''));
        $base = rtrim((string) dm_epp_api_base($credentials), '/');
        $row = is_array($liveRecord['raw'] ?? null) ? $liveRecord['raw'] : [];
        $host = (string) ($liveRecord['hostRelative'] ?? '@');
        if ($host === '@') {
            // LogicBoxes represents the zone apex by an omitted/empty host.
            // Sending the RegistrarDNS display marker literally can cause an
            // otherwise valid TTL-only update to be ignored or rejected.
            $host = '';
        }
        $submittedNewValue = trim((string) ($change['newValue'] ?? ''));
        $newValue = $submittedNewValue;

        // TXT values are normalized at request ingress in Patch 1868 so the
        // write and later verification use the exact same desired content.
        // Keep this defensive normalization for direct/internal callers only;
        // it does not participate in record identity or record-ID matching.
        if ($type === 'TXT') {
            [$txtValueOk, $newValue] = dm_register_dns_update_1580_txt_submission_value_1868(
                $submittedNewValue
            );
            if (!$txtValueOk) {
                $recordName = dm_register_dns_update_1580_fqdn_host(
                    (string) ($change['newHost'] ?? $liveRecord['hostRelative'] ?? ''),
                    $domain
                );
                return [
                    false,
                    'Unable to save TXT record for ' . $recordName . '. Please verify the value and try again.',
                    $endpointMap[$type],
                ];
            }
        }

        $newTtl = (int) ($change['newTtl'] ?? 0);
        $newPriority = dm_register_dns_update_1580_priority_key((string) ($change['newPriority'] ?? ''), $type);

        if ($type === 'SRV') {
            $host = dm_register_dns_update_1580_fqdn_host(
                (string) ($liveRecord['hostRaw'] ?? $host),
                $domain
            );
        }

        $params = [
            'auth-userid' => $authUserId,
            'api-key' => $apiKey,
            'domain-name' => strtolower($domain),
            'host' => $host,
            'current-value' => (string) ($liveRecord['valueRaw'] ?? ''),
            'new-value' => $newValue,
            'ttl' => $newTtl > 0 ? $newTtl : 14400,
        ];

        if ($type === 'MX') {
            $params['priority'] = $newPriority !== '' ? (int) $newPriority : 0;
        }

        if ($type === 'SRV') {
            $port = dm_register_dns_update_1580_pick(
                $row,
                ['port', 'srv-port', 'srv_port', 'service-port', 'service_port'],
                null
            );
            $weight = dm_register_dns_update_1580_pick(
                $row,
                ['weight', 'srv-weight', 'srv_weight'],
                null
            );

            if ($port === null || $weight === null || !is_numeric($port) || !is_numeric($weight)) {
                return [false, 'The live SRV record did not include the required port and weight values. No update was attempted.', $endpointMap[$type]];
            }

            $params['priority'] = $newPriority !== '' ? (int) $newPriority : (int) ($liveRecord['priority'] ?? 0);
            $params['port'] = (int) $port;
            $params['weight'] = (int) $weight;
        }

        [$ok, $response, $error] = dm_epp_http_request(
            'POST',
            $base . '/dns/manage/' . $endpointMap[$type],
            $params
        );

        if (!$ok) {
            return [false, $error ?: 'The registrar rejected the update request.', $endpointMap[$type]];
        }

        if (!dm_register_dns_update_1580_api_success($response)) {
            // dm_epp_http_request() reaches this point only after a successful
            // 2xx HTTP response and after rejecting the registrar's standard
            // ERROR status. LogicBoxes has returned several other successful
            // shapes over time (boolean, numeric, or arrays without a status
            // key). Do not report those as success from the response alone;
            // allow the existing fresh live-record verification below to make
            // the final decision. Explicit failure shapes still stop here.
            $message = '';
            $explicitFailure = false;

            if (is_array($response)) {
                $status = strtoupper(trim((string) (
                    $response['status']
                    ?? $response['actionstatus']
                    ?? $response['result']
                    ?? ''
                )));
                $message = trim((string) (
                    $response['error']
                    ?? $response['message']
                    ?? $response['actionstatusdesc']
                    ?? ''
                ));

                if (in_array($status, ['ERROR', 'FAILED', 'FAILURE', 'FAIL', 'FALSE'], true)) {
                    $explicitFailure = true;
                }
                if (array_key_exists('success', $response)
                    && filter_var($response['success'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false) {
                    $explicitFailure = true;
                }
                if (isset($response['error']) && trim((string) $response['error']) !== '') {
                    $explicitFailure = true;
                }
            } elseif (is_string($response)) {
                $status = strtoupper(trim($response, " \t\n\r\0\x0B\"'"));
                if (in_array($status, ['ERROR', 'FAILED', 'FAILURE', 'FAIL', 'FALSE'], true)) {
                    $explicitFailure = true;
                    $message = trim($response);
                }
            } elseif ($response === false) {
                $explicitFailure = true;
            }

            if ($explicitFailure) {
                return [
                    false,
                    $message !== '' ? $message : 'The registrar rejected the update request.',
                    $endpointMap[$type],
                ];
            }

            return [
                true,
                'The registrar returned a nonstandard 2xx response; live DNS verification is required.',
                $endpointMap[$type],
            ];
        }

        return [true, 'Updated successfully.', $endpointMap[$type]];
    }
}


if (!function_exists('dm_register_dns_update_1580_auto_repair_legacy_txt_1881')) {
    /**
     * Repair one already-matched legacy TXT row. Safety rules are deliberately
     * stricter than the normal edit path: re-read TXT immediately before the
     * write, re-run the existing matcher, require the exact malformed raw value
     * to be unchanged and unique at that Host, preserve the confirmed TTL, and
     * verify the cleaned live value. Normal record matching is not modified.
     *
     * Returns [attempted, repaired, message].
     */
    function dm_register_dns_update_1580_auto_repair_legacy_txt_1881(
        string $domain,
        array $probe,
        array $matchedLiveRecord,
        array $credentials,
        string $confirmedTtl
    ): array {
        if (strtoupper(trim((string) ($probe['type'] ?? ''))) !== 'TXT') {
            return [false, false, ''];
        }

        $initialRaw = trim((string) ($matchedLiveRecord['valueRaw'] ?? ''));
        [$isLegacy, $cleanContent] = dm_register_dns_update_1580_txt_legacy_duplicate_1881($initialRaw);
        if (!$isLegacy || $cleanContent === '') {
            return [false, false, ''];
        }

        $ttlKey = dm_register_dns_update_1580_ttl_key($confirmedTtl);
        if ($ttlKey === '' || (int) $ttlKey < 7200) {
            return [true, false, 'A legacy TXT record was detected, but its current TTL could not be preserved safely. No cleanup was submitted.'];
        }

        $hostRelative = dm_register_dns_update_1580_relative_host(
            (string) ($matchedLiveRecord['hostRelative'] ?? $probe['originalHost'] ?? ''),
            $domain
        );

        $repairChange = [
            'recordId' => trim((string) ($matchedLiveRecord['remoteId'] ?? $probe['recordId'] ?? '')),
            'type' => 'TXT',
            'originalType' => 'TXT',
            'originalHost' => $hostRelative,
            'newHost' => $hostRelative,
            'originalValue' => $initialRaw,
            'newValue' => $cleanContent,
            'originalPriority' => '',
            'newPriority' => '',
            'originalTtl' => $ttlKey,
            'newTtl' => $ttlKey,
        ];

        // Concurrency/identity check immediately before the repair write.
        [$freshOk, $freshRows, $freshError] = dm_register_dns_update_1580_fetch_type($domain, 'TXT', $credentials);
        if (!$freshOk) {
            return [true, false, $freshError ?: 'The legacy TXT record could not be re-read safely. No cleanup was submitted.'];
        }

        [$freshMatched, $freshRecord, $freshMatchError] = dm_register_dns_update_1580_match_live(
            $domain,
            $repairChange,
            $freshRows
        );
        if (!$freshMatched || !is_array($freshRecord)) {
            return [true, false, $freshMatchError ?: 'The legacy TXT record could not be uniquely re-identified. No cleanup was submitted.'];
        }

        $freshRaw = trim((string) ($freshRecord['valueRaw'] ?? ''));
        if (!hash_equals($initialRaw, $freshRaw)) {
            return [true, false, 'The legacy TXT record changed during the safety check. No cleanup was submitted.'];
        }

        [$stillLegacy, $freshCleanContent] = dm_register_dns_update_1580_txt_legacy_duplicate_1881($freshRaw);
        if (!$stillLegacy || $freshCleanContent === '') {
            // Another process already cleaned it between reads.
            return [true, false, ''];
        }

        if (!hash_equals($cleanContent, $freshCleanContent)) {
            return [true, false, 'The legacy TXT record changed during normalization. No cleanup was submitted.'];
        }

        // The LogicBoxes TXT update endpoint identifies the target by Host and
        // exact current-value. Require that exact signature to occur once only.
        $exactSignatureCount = 0;
        foreach ($freshRows as $freshRow) {
            $freshHost = dm_register_dns_update_1580_relative_host(
                (string) dm_register_dns_update_1580_pick($freshRow, ['host', 'hostname', 'name'], '@'),
                $domain
            );
            if ($freshHost !== $hostRelative) {
                continue;
            }
            $freshValue = trim((string) dm_register_dns_update_1580_pick(
                $freshRow,
                ['value', 'address', 'target', 'data'],
                ''
            ));
            if (hash_equals($initialRaw, $freshValue)) {
                $exactSignatureCount++;
            }
        }
        if ($exactSignatureCount !== 1) {
            return [true, false, 'The legacy TXT record did not have one unique live Host/value signature. No cleanup was submitted.'];
        }

        $freshTtl = dm_register_dns_ttl_1852_from_row(
            is_array($freshRecord['raw'] ?? null) ? $freshRecord['raw'] : []
        );
        if ($freshTtl !== '') {
            if ((int) $freshTtl < 7200) {
                return [true, false, 'The legacy TXT record has a TTL below the current RegistrarDNS minimum, so it was not changed automatically.'];
            }
            $repairChange['originalTtl'] = $freshTtl;
            $repairChange['newTtl'] = $freshTtl;
        }

        [$writeOk, $writeMessage] = dm_register_dns_update_1580_update_live(
            $domain,
            $repairChange,
            $freshRecord,
            $credentials
        );
        if (!$writeOk) {
            return [true, false, $writeMessage ?: 'The legacy TXT cleanup was rejected by the registrar.'];
        }

        // Confirm against NEO management state, not authoritative DNS. The
        // management record should change immediately, while DNS propagation
        // may legitimately lag and must not cause the safe cleanup to repeat.
        $verified = false;
        $verifyError = 'The cleaned NEO TXT record could not be confirmed.';
        foreach ([0, 500, 1500, 3000] as $delayMs) {
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }

            [$verifyReadOk, $verifyRows, $verifyReadError] = dm_register_dns_update_1580_fetch_type(
                $domain,
                'TXT',
                $credentials
            );
            if (!$verifyReadOk) {
                if (trim((string) $verifyReadError) !== '') {
                    $verifyError = (string) $verifyReadError;
                }
                continue;
            }

            $cleanMatches = [];
            foreach ($verifyRows as $verifyRow) {
                $verifyHost = dm_register_dns_update_1580_relative_host(
                    (string) dm_register_dns_update_1580_pick($verifyRow, ['host', 'hostname', 'name'], '@'),
                    $domain
                );
                if ($verifyHost !== $hostRelative) {
                    continue;
                }

                $verifyRaw = trim((string) dm_register_dns_update_1580_pick(
                    $verifyRow,
                    ['value', 'address', 'target', 'data'],
                    ''
                ));
                [$verifyStillLegacy] = dm_register_dns_update_1580_txt_legacy_duplicate_1881($verifyRaw);
                if ($verifyStillLegacy) {
                    continue;
                }
                if (!hash_equals($cleanContent, dm_register_dns_update_1580_txt_match_key_1880($verifyRaw))) {
                    continue;
                }

                $verifyTtl = dm_register_dns_ttl_1852_from_row($verifyRow);
                if ($verifyTtl !== '' && !hash_equals((string) $repairChange['newTtl'], $verifyTtl)) {
                    continue;
                }
                $cleanMatches[] = $verifyRow;
            }

            if (count($cleanMatches) === 1) {
                $verified = true;
                break;
            }
            if (count($cleanMatches) > 1) {
                $verifyError = 'More than one cleaned TXT record matched after the legacy cleanup.';
                break;
            }
        }

        if (!$verified) {
            return [true, false, $verifyError ?: 'The registrar accepted the legacy TXT cleanup, but the cleaned live record could not be confirmed.'];
        }

        return [true, true, 'Legacy duplicate TXT quote layer cleaned automatically.'];
    }
}

if (!function_exists('dm_register_dns_update_1580_add_submission_value_1632')) {
    function dm_register_dns_update_1580_add_submission_value_1632(string $value, string $type): array
    {
        $value = trim($value);
        if ($type !== 'TXT') {
            return [true, $value, ''];
        }

        [$ok, $content, $error] = dm_register_dns_update_1580_txt_submission_value_1868($value);
        if (!$ok) {
            return [false, '', $error];
        }

        return [true, $content, ''];
    }
}

if (!function_exists('dm_register_dns_update_1580_live_candidate_1632')) {
    function dm_register_dns_update_1580_live_candidate_1632(
        string $domain,
        string $type,
        array $row
    ): array {
        $hostRaw = (string) dm_register_dns_update_1580_pick($row, ['host', 'hostname', 'name'], '@');
        return [
            'raw' => $row,
            'hostRaw' => $hostRaw,
            'hostRelative' => dm_register_dns_update_1580_relative_host($hostRaw, $domain),
            'valueRaw' => trim((string) dm_register_dns_update_1580_pick($row, ['value', 'address', 'target', 'data'], '')),
            'priority' => dm_register_dns_update_1580_priority_key(
                (string) dm_register_dns_update_1580_pick($row, ['priority', 'pref'], ''),
                $type
            ),
            'ttl' => dm_register_dns_ttl_1852_from_row($row),
            'remoteId' => dm_register_dns_update_1580_remote_id($row),
        ];
    }
}

if (!function_exists('dm_register_dns_update_1580_delete_signature_matches_1632')) {
    function dm_register_dns_update_1580_delete_signature_matches_1632(
        string $domain,
        string $type,
        array $row,
        array $liveRecord
    ): bool {
        $candidate = dm_register_dns_update_1580_live_candidate_1632($domain, $type, $row);
        if ($candidate['hostRelative'] !== (string) ($liveRecord['hostRelative'] ?? '')) {
            return false;
        }

        $candidateValue = dm_register_dns_update_1580_value_key((string) $candidate['valueRaw'], $type);
        $recordValue = dm_register_dns_update_1580_value_key((string) ($liveRecord['valueRaw'] ?? ''), $type);
        if ($candidateValue !== $recordValue) {
            if ($type !== 'TXT'
                || dm_register_dns_update_1580_txt_key_1623((string) $candidate['valueRaw'])
                    !== dm_register_dns_update_1580_txt_key_1623((string) ($liveRecord['valueRaw'] ?? ''))) {
                return false;
            }
        }

        $liveRaw = is_array($liveRecord['raw'] ?? null) ? $liveRecord['raw'] : [];
        if ($type === 'MX') {
            $candidatePriority = dm_register_dns_update_1580_priority_key(
                (string) dm_register_dns_update_1580_pick($row, ['priority', 'pref'], ''),
                $type
            );
            $livePriority = dm_register_dns_update_1580_priority_key(
                (string) dm_register_dns_update_1580_pick($liveRaw, ['priority', 'pref'], ''),
                $type
            );
            return $candidatePriority === $livePriority;
        }

        if ($type === 'SRV') {
            foreach ([
                ['port', 'srv-port', 'srv_port', 'service-port', 'service_port'],
                ['weight', 'srv-weight', 'srv_weight'],
                ['priority', 'pref'],
            ] as $keys) {
                $candidatePart = (string) dm_register_dns_update_1580_pick($row, $keys, '');
                $livePart = (string) dm_register_dns_update_1580_pick($liveRaw, $keys, '');
                if ((string) ((int) $candidatePart) !== (string) ((int) $livePart)) {
                    return false;
                }
            }
        }

        return true;
    }
}

if (!function_exists('dm_register_dns_update_1580_delete_signature_count_1632')) {
    function dm_register_dns_update_1580_delete_signature_count_1632(
        string $domain,
        string $type,
        array $rows,
        array $liveRecord
    ): int {
        $count = 0;
        foreach ($rows as $row) {
            if (dm_register_dns_update_1580_delete_signature_matches_1632($domain, $type, $row, $liveRecord)) {
                $count++;
            }
        }
        return $count;
    }
}

if (!function_exists('dm_register_dns_update_1580_desired_candidates_1632')) {
    function dm_register_dns_update_1580_desired_candidates_1632(
        string $domain,
        array $change,
        array $rows
    ): array {
        $type = strtoupper(trim((string) ($change['type'] ?? '')));
        $hostKey = dm_register_dns_update_1580_relative_host((string) ($change['newHost'] ?? ''), $domain);
        $valueKey = dm_register_dns_update_1580_value_key((string) ($change['newValue'] ?? ''), $type);
        $priorityKey = dm_register_dns_update_1580_priority_key((string) ($change['newPriority'] ?? ''), $type);
        $ttlKey = dm_register_dns_update_1580_ttl_key($change['newTtl'] ?? '');
        $matches = [];

        foreach ($rows as $row) {
            $candidate = dm_register_dns_update_1580_live_candidate_1632($domain, $type, $row);
            if ($candidate['hostRelative'] !== $hostKey) {
                continue;
            }

            $candidateValue = dm_register_dns_update_1580_value_key((string) $candidate['valueRaw'], $type);
            if ($candidateValue !== $valueKey) {
                if ($type !== 'TXT'
                    || dm_register_dns_update_1580_txt_key_1623((string) $candidate['valueRaw'])
                        !== dm_register_dns_update_1580_txt_key_1623((string) ($change['newValue'] ?? ''))) {
                    continue;
                }
            }

            if ($priorityKey !== '' && $candidate['priority'] !== '' && $candidate['priority'] !== $priorityKey) {
                continue;
            }
            if ($ttlKey !== '' && $candidate['ttl'] !== '' && $candidate['ttl'] !== $ttlKey) {
                continue;
            }

            $matches[] = $candidate;
        }

        return $matches;
    }
}

if (!function_exists('dm_register_dns_update_1580_add_live_1632')) {
    function dm_register_dns_update_1580_add_live_1632(
        string $domain,
        array $change,
        array $originalLiveRecord,
        array $credentials
    ): array {
        $type = strtoupper(trim((string) ($change['type'] ?? '')));
        $endpointMap = [
            'A' => 'add-ipv4-record.json',
            'AAAA' => 'add-ipv6-record.json',
            'CNAME' => 'add-cname-record.json',
            'MX' => 'add-mx-record.json',
            'NS' => 'add-ns-record.json',
            'TXT' => 'add-txt-record.json',
            'SRV' => 'add-srv-record.json',
        ];
        if (!isset($endpointMap[$type])) {
            return [false, 'This DNS record type is not supported for a Host change.', ''];
        }

        if ($type === 'CNAME'
            && dm_register_dns_update_1580_relative_host((string) ($change['newHost'] ?? ''), $domain) === '@') {
            $recordName = dm_register_dns_update_1580_fqdn_host(
                (string) ($change['newHost'] ?? ''),
                $domain
            );
            return [
                false,
                'A CNAME cannot be saved for ' . $recordName . ' at the root domain because it would conflict with other DNS records. Use a subdomain instead.',
                $endpointMap[$type],
            ];
        }

        [$valueOk, $value, $valueError] = dm_register_dns_update_1580_add_submission_value_1632(
            (string) ($change['newValue'] ?? ''),
            $type
        );
        if (!$valueOk) {
            if ($type === 'TXT') {
                $recordName = dm_register_dns_update_1580_fqdn_host(
                    (string) ($change['newHost'] ?? ''),
                    $domain
                );
                return [
                    false,
                    'Unable to save TXT record for ' . $recordName . '. Please verify the value and try again.',
                    $endpointMap[$type],
                ];
            }
            return [false, $valueError, $endpointMap[$type]];
        }

        $authUserId = trim((string) ($credentials['authUserId'] ?? ''));
        $apiKey = trim((string) ($credentials['apiKey'] ?? ''));
        $base = rtrim((string) dm_epp_api_base($credentials), '/');
        $host = dm_register_dns_update_1580_relative_host((string) ($change['newHost'] ?? ''), $domain);
        if ($host === '@') {
            $host = '';
        }
        if ($type === 'SRV') {
            $host = dm_register_dns_update_1580_fqdn_host((string) ($change['newHost'] ?? ''), $domain);
        }

        $newTtl = (int) ($change['newTtl'] ?? 0);
        if ($newTtl <= 0) {
            $newTtl = (int) ($originalLiveRecord['ttl'] ?? 0);
        }
        if ($newTtl <= 0) {
            $newTtl = 14400;
        }

        $params = [
            'auth-userid' => $authUserId,
            'api-key' => $apiKey,
            'domain-name' => strtolower($domain),
            'host' => $host,
            'value' => $value,
            'ttl' => $newTtl,
        ];

        if ($type === 'MX' || $type === 'SRV') {
            $params['priority'] = (int) ($change['newPriority'] ?? 0);
        }
        if ($type === 'SRV') {
            $raw = is_array($originalLiveRecord['raw'] ?? null) ? $originalLiveRecord['raw'] : [];
            $port = dm_register_dns_update_1580_pick(
                $raw,
                ['port', 'srv-port', 'srv_port', 'service-port', 'service_port'],
                null
            );
            $weight = dm_register_dns_update_1580_pick(
                $raw,
                ['weight', 'srv-weight', 'srv_weight'],
                null
            );
            if ($port === null || $weight === null || !is_numeric($port) || !is_numeric($weight)) {
                return [false, 'The live SRV record did not include the required port and weight values. No Host change was attempted.', $endpointMap[$type]];
            }
            $params['port'] = (int) $port;
            $params['weight'] = (int) $weight;
        }

        [$ok, $response, $error] = dm_epp_http_request(
            'POST',
            $base . '/dns/manage/' . $endpointMap[$type],
            $params
        );
        if (!$ok) {
            return [false, $error ?: 'The registrar rejected the replacement-record request.', $endpointMap[$type]];
        }
        if (!dm_register_dns_update_1580_api_success($response)) {
            $message = is_array($response)
                ? trim((string) ($response['error'] ?? $response['message'] ?? ''))
                : '';
            return [false, $message !== '' ? $message : 'The registrar returned an unexpected add-record response.', $endpointMap[$type]];
        }

        return [true, 'Replacement record added.', $endpointMap[$type]];
    }
}

if (!function_exists('dm_register_dns_update_1580_delete_live_1632')) {
    function dm_register_dns_update_1580_delete_live_1632(
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
            return [false, 'This DNS record type is not supported for a Host change.', ''];
        }

        $authUserId = trim((string) ($credentials['authUserId'] ?? ''));
        $apiKey = trim((string) ($credentials['apiKey'] ?? ''));
        $base = rtrim((string) dm_epp_api_base($credentials), '/');
        $raw = is_array($liveRecord['raw'] ?? null) ? $liveRecord['raw'] : [];
        $host = (string) ($liveRecord['hostRelative'] ?? '@');
        if ($host === '@') {
            $host = '';
        }
        $value = (string) ($liveRecord['valueRaw'] ?? '');
        if ($type === 'SRV') {
            $host = dm_register_dns_update_1580_fqdn_host(
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
        if ($type === 'MX') {
            $params['priority'] = (int) dm_register_dns_update_1580_pick($raw, ['priority', 'pref'], 0);
        }
        if ($type === 'SRV') {
            $port = dm_register_dns_update_1580_pick(
                $raw,
                ['port', 'srv-port', 'srv_port', 'service-port', 'service_port'],
                null
            );
            $weight = dm_register_dns_update_1580_pick(
                $raw,
                ['weight', 'srv-weight', 'srv_weight'],
                null
            );
            if ($port === null || $weight === null || !is_numeric($port) || !is_numeric($weight)) {
                return [false, 'The live SRV record did not include the required port and weight values. The record was not deleted.', $endpointMap[$type]];
            }
            $params['priority'] = (int) dm_register_dns_update_1580_pick($raw, ['priority', 'pref'], 0);
            $params['port'] = (int) $port;
            $params['weight'] = (int) $weight;
        }

        [$ok, $response, $error] = dm_epp_http_request(
            'POST',
            $base . '/dns/manage/' . $endpointMap[$type],
            $params
        );
        if (!$ok) {
            return [false, $error ?: 'The registrar rejected the delete request.', $endpointMap[$type]];
        }
        if (!dm_register_dns_update_1580_api_success($response)) {
            $message = is_array($response)
                ? trim((string) ($response['error'] ?? $response['message'] ?? ''))
                : '';
            return [false, $message !== '' ? $message : 'The registrar returned an unexpected delete-record response.', $endpointMap[$type]];
        }

        return [true, 'Record deleted.', $endpointMap[$type]];
    }
}

if (!function_exists('dm_register_dns_update_1580_fetch_move_state_1632')) {
    function dm_register_dns_update_1580_fetch_move_state_1632(
        string $domain,
        array $change,
        array $originalLiveRecord,
        array $credentials
    ): array {
        [$ok, $rows, $error] = dm_register_dns_update_1580_fetch_type(
            $domain,
            (string) ($change['type'] ?? ''),
            $credentials
        );
        if (!$ok) {
            return [false, [], 0, [], $error ?: 'The live DNS records could not be read.'];
        }

        $originalCount = dm_register_dns_update_1580_delete_signature_count_1632(
            $domain,
            (string) ($change['type'] ?? ''),
            $rows,
            $originalLiveRecord
        );
        $desired = dm_register_dns_update_1580_desired_candidates_1632($domain, $change, $rows);
        return [true, $rows, $originalCount, $desired, ''];
    }
}

if (!function_exists('dm_register_dns_update_1580_move_live_1632')) {
    function dm_register_dns_update_1580_move_live_1632(
        string $domain,
        array $change,
        array $originalLiveRecord,
        array $credentials
    ): array {
        $type = strtoupper(trim((string) ($change['type'] ?? '')));
        $endpointLabel = 'add/delete-' . strtolower($type) . '-record';

        [$stateOk, $rows, $originalCount, $desiredBefore, $stateError] =
            dm_register_dns_update_1580_fetch_move_state_1632($domain, $change, $originalLiveRecord, $credentials);
        if (!$stateOk) {
            return [false, $stateError, $endpointLabel];
        }
        if ($originalCount !== 1) {
            return [
                false,
                $originalCount < 1
                    ? 'The original live DNS record was no longer present. No Host change was attempted.'
                    : 'More than one live record shared the registrar delete identity. The Host change was refused to avoid deleting the wrong record.',
                $endpointLabel,
            ];
        }
        if (count($desiredBefore) > 1) {
            return [false, 'More than one identical replacement record already exists at the new Host. No Host change was attempted.', $endpointLabel];
        }

        $createdReplacement = false;
        if (count($desiredBefore) === 0) {
            [$addOk, $addMessage, $addEndpoint] = dm_register_dns_update_1580_add_live_1632(
                $domain,
                $change,
                $originalLiveRecord,
                $credentials
            );
            $endpointLabel = $addEndpoint !== '' ? $addEndpoint : $endpointLabel;

            $replacementConfirmed = false;
            $lastReadError = '';
            foreach ([0, 400, 900, 1500] as $delayMs) {
                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }
                [$checkOk, $checkRows, $checkError] = dm_register_dns_update_1580_fetch_type($domain, $type, $credentials);
                if (!$checkOk) {
                    $lastReadError = $checkError;
                    continue;
                }
                $desiredNow = dm_register_dns_update_1580_desired_candidates_1632($domain, $change, $checkRows);
                if (count($desiredNow) === 1) {
                    $replacementConfirmed = true;
                    break;
                }
                if (count($desiredNow) > 1) {
                    return [false, 'The replacement Host returned more than one identical live record. The original record was left unchanged.', $endpointLabel];
                }
            }

            if (!$replacementConfirmed) {
                return [
                    false,
                    $addOk
                        ? 'The registrar accepted the replacement record, but it could not be confirmed. The original record was left unchanged.'
                        : ($addMessage ?: ($lastReadError ?: 'The replacement record could not be created.')),
                    $endpointLabel,
                ];
            }
            $createdReplacement = true;
        }

        [$deleteOk, $deleteMessage, $deleteEndpoint] = dm_register_dns_update_1580_delete_live_1632(
            $domain,
            $type,
            $originalLiveRecord,
            $credentials
        );
        if ($deleteEndpoint !== '') {
            $endpointLabel .= ' -> ' . $deleteEndpoint;
        }

        $oldGone = false;
        $desiredAfter = [];
        $verificationError = '';
        foreach ([0, 450, 900, 1500] as $delayMs) {
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
            [$checkOk, $checkRows, $checkError] = dm_register_dns_update_1580_fetch_type($domain, $type, $credentials);
            if (!$checkOk) {
                $verificationError = $checkError;
                continue;
            }
            $oldCount = dm_register_dns_update_1580_delete_signature_count_1632(
                $domain,
                $type,
                $checkRows,
                $originalLiveRecord
            );
            $desiredAfter = dm_register_dns_update_1580_desired_candidates_1632($domain, $change, $checkRows);
            if ($oldCount === 0 && count($desiredAfter) === 1) {
                $oldGone = true;
                break;
            }
            if ($oldCount === 0 && count($desiredAfter) > 1) {
                return [false, 'The Host changed, but more than one identical replacement record was found. Review the live DNS records before making another change.', $endpointLabel];
            }
        }

        if ($oldGone) {
            return [true, 'DNS record Host changed and verified.', $endpointLabel];
        }

        // Never remove a replacement that existed before this request. If this
        // request created it and the original still exists, roll back only the
        // uniquely verified replacement record.
        if ($createdReplacement) {
            [$rollbackReadOk, $rollbackRows, $rollbackReadError] = dm_register_dns_update_1580_fetch_type($domain, $type, $credentials);
            $rollbackCandidates = $rollbackReadOk
                ? dm_register_dns_update_1580_desired_candidates_1632($domain, $change, $rollbackRows)
                : [];

            if ($rollbackReadOk && count($rollbackCandidates) === 1) {
                [$rollbackOk, $rollbackMessage] = dm_register_dns_update_1580_delete_live_1632(
                    $domain,
                    $type,
                    $rollbackCandidates[0],
                    $credentials
                );

                $rollbackGone = false;
                foreach ([300, 800] as $delayMs) {
                    usleep($delayMs * 1000);
                    [$verifyRollbackOk, $verifyRollbackRows] = dm_register_dns_update_1580_fetch_type($domain, $type, $credentials);
                    if ($verifyRollbackOk
                        && count(dm_register_dns_update_1580_desired_candidates_1632($domain, $change, $verifyRollbackRows)) === 0) {
                        $rollbackGone = true;
                        break;
                    }
                }

                if ($rollbackGone) {
                    return [
                        false,
                        'The original record could not be removed, so the replacement record was rolled back. No Host change was kept.',
                        $endpointLabel,
                    ];
                }

                return [
                    false,
                    'The original record could not be removed, and the replacement rollback could not be confirmed. Both records may be present. Reload DNS Records before making another change. ' . ($rollbackOk ? '' : $rollbackMessage),
                    $endpointLabel,
                ];
            }

            return [
                false,
                'The original record could not be confirmed as deleted, and the replacement could not be uniquely identified for rollback. Both records may be present. Reload DNS Records before making another change. ' . ($rollbackReadError ?: $verificationError),
                $endpointLabel,
            ];
        }

        return [
            false,
            $deleteOk
                ? 'The registrar accepted the original-record deletion, but the Host change could not be confirmed. Reload DNS Records before making another change.'
                : ($deleteMessage ?: ($verificationError ?: 'The original record could not be removed.')),
            $endpointLabel,
        ];
    }
}

if (!function_exists('dm_register_dns_update_1580_verify_live')) {
    function dm_register_dns_update_1580_verify_live(
        string $domain,
        array $change,
        array $credentials
    ): array {
        $type = strtoupper(trim((string) ($change['type'] ?? '')));
        [$ok, $rows, $error] = dm_register_dns_update_1580_fetch_type($domain, $type, $credentials);
        if (!$ok) {
            return [false, $error ?: 'The updated live DNS record could not be verified.'];
        }

        $hostKey = dm_register_dns_update_1580_relative_host((string) ($change['newHost'] ?? ''), $domain);
        $valueKey = dm_register_dns_update_1580_value_key((string) ($change['newValue'] ?? ''), $type);
        $priorityKey = dm_register_dns_update_1580_priority_key((string) ($change['newPriority'] ?? ''), $type);
        $ttlKey = dm_register_dns_update_1580_ttl_key($change['newTtl'] ?? '');
        $matches = 0;

        foreach ($rows as $row) {
            $rowHost = dm_register_dns_update_1580_relative_host(
                (string) dm_register_dns_update_1580_pick($row, ['host', 'hostname', 'name'], '@'),
                $domain
            );
            if ($rowHost !== $hostKey) {
                continue;
            }

            $rowValue = dm_register_dns_update_1580_value_key(
                (string) dm_register_dns_update_1580_pick($row, ['value', 'address', 'target', 'data'], ''),
                $type
            );
            if ($rowValue !== $valueKey) {
                if ($type !== 'TXT'
                    || dm_register_dns_update_1580_txt_key_1623((string) dm_register_dns_update_1580_pick($row, ['value', 'address', 'target', 'data'], ''))
                        !== dm_register_dns_update_1580_txt_key_1623((string) ($change['newValue'] ?? ''))) {
                    continue;
                }
            }

            $rowPriority = dm_register_dns_update_1580_priority_key(
                (string) dm_register_dns_update_1580_pick($row, ['priority', 'pref'], ''),
                $type
            );
            if ($priorityKey !== '' && $rowPriority !== '' && $rowPriority !== $priorityKey) {
                continue;
            }

            $rowTtl = dm_register_dns_ttl_1852_from_row($row);
            if ($ttlKey !== '' && $rowTtl !== '' && $rowTtl !== $ttlKey) {
                continue;
            }

            $matches++;
        }

        if ($matches < 1) {
            return [false, 'The registrar accepted the request, but the updated live DNS record could not be confirmed.'];
        }

        if ($ttlKey !== '') {
            [$ttlOk, $authoritativeTtl, $ttlError] = dm_register_dns_ttl_1852_authoritative(
                $domain,
                (string) ($change['newHost'] ?? ''),
                $type,
                (string) ($change['newValue'] ?? ''),
                (string) ($change['newPriority'] ?? ''),
                false
            );
            if (!$ttlOk) {
                return [
                    false,
                    $ttlError ?: 'The registrar accepted the request, but its authoritative TTL could not be verified.',
                ];
            }
            if (!hash_equals($ttlKey, (string) $authoritativeTtl)) {
                return [
                    false,
                    'The registrar accepted the request, but authoritative DNS still reports TTL '
                        . (string) $authoritativeTtl . ' instead of ' . $ttlKey . '.',
                ];
            }
        }

        return [true, ''];
    }
}


/**
 * Patch 1856 read-only diagnostic.
 *
 * Open the current RegistrarDNS URL with:
 * &dm_register_dns_neo_ttl_diag_1856=1&dm_neo_host_1856=host.example.com
 *
 * The response contains only the matching NEO search-record row. Registrar
 * credentials and credential-like response fields are always omitted.
 */
if (
    strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'GET'
    && (string) ($_GET['dm_register_dns_neo_ttl_diag_1856'] ?? '') === '1'
) {
    $domainId = (int) ($_GET['id'] ?? $_GET['domainid'] ?? 0);
    $clientId = (int) ($_SESSION['uid'] ?? $_SESSION['clientid'] ?? 0);
    $requestedHost = trim(substr((string) ($_GET['dm_neo_host_1856'] ?? ''), 0, 255));
    $requestedType = strtoupper(trim(substr((string) ($_GET['dm_neo_type_1856'] ?? ''), 0, 10)));

    if ($clientId <= 0) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'Your client-area session has expired. Sign in again and retry.',
        ], 401);
    }

    if ($domainId <= 0 || $requestedHost === '') {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'Keep the current RegistrarDNS domain ID in the URL and supply dm_neo_host_1856.',
        ], 422);
    }

    $allowedTypes = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SRV'];
    if ($requestedType !== '' && !in_array($requestedType, $allowedTypes, true)) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The optional diagnostic record type was not supported.',
        ], 422);
    }

    $domain = null;
    try {
        $domain = Capsule::table('tbldomains')
            ->select(['id', 'userid', 'domain', 'registrar'])
            ->where('id', $domainId)
            ->where('userid', $clientId)
            ->first();
    } catch (Throwable $exception) {
        $domain = null;
    }

    if (!$domain) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The domain could not be found for this client account.',
        ], 404);
    }

    $registrar = strtolower(trim((string) ($domain->registrar ?? '')));
    if (!preg_match('/(resellerclub|netearth|stargate|logicboxes)/i', $registrar)) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The domain is not assigned to a supported LogicBoxes registrar module.',
        ], 422);
    }

    if (!dm_register_dns_update_1580_load_helpers()) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The registrar API helper functions were unavailable.',
        ], 500);
    }

    $domainName = strtolower(rtrim(trim((string) $domain->domain), '.'));
    $hostKey = dm_register_dns_update_1580_relative_host($requestedHost, $domainName);
    $credentials = dm_epp_find_credentials($registrar);
    $typesToRead = $requestedType !== '' ? [$requestedType] : $allowedTypes;
    $matches = [];
    $recordCounts = [];
    $typeErrors = [];

    foreach ($typesToRead as $type) {
        [$ok, $rows, $error] = dm_register_dns_update_1580_fetch_type($domainName, $type, $credentials);
        if (!$ok) {
            $typeErrors[$type] = $error ?: 'The NEO management records could not be read.';
            continue;
        }

        $recordCounts[$type] = count($rows);
        foreach ($rows as $row) {
            $rowHostRaw = (string) dm_register_dns_update_1580_pick($row, ['host', 'hostname', 'name'], '@');
            if (dm_register_dns_update_1580_relative_host($rowHostRaw, $domainName) !== $hostKey) {
                continue;
            }

            $matches[] = [
                'type' => $type,
                'recordId' => dm_register_dns_update_1580_remote_id($row),
                'directTtlDetected' => dm_register_dns_ttl_1852_from_row($row),
                'neoSearchRow' => dm_register_dns_neo_diag_1856_sanitize($row),
            ];

            if (count($matches) >= 20) {
                break 2;
            }
        }
    }

    dm_register_dns_update_1580_json([
        'success' => count($matches) > 0,
        'diagnostic' => 'Patch 1856 read-only NEO management TTL inspection',
        'endpoint' => 'dns/manage/search-records.json',
        'domain' => $domainName,
        'requestedHost' => $requestedHost,
        'normalizedHost' => $hostKey,
        'requestedType' => $requestedType !== '' ? $requestedType : 'all supported types',
        'credentialsIncluded' => false,
        'recordCountsByType' => $recordCounts,
        'typeErrors' => $typeErrors,
        'matchCount' => count($matches),
        'matches' => $matches,
        'message' => count($matches) > 0
            ? 'Paste this complete diagnostic response into the conversation.'
            : 'NEO returned no management record matching this Host.',
    ], count($matches) > 0 ? 200 : 404);
}


/**
 * Patches 1851/1852: return authoritative per-record TTL values for the visible
 * RegistrarDNS rows. WHMCS's GetDNS result omits TTL on this installation and
 * LogicBoxes search does not guarantee a TTL field, so the client template
 * cannot safely invent one. Each row is first matched against a fresh NEO API
 * read, then its TTL is obtained from NEO's response when present or directly
 * from DomainMonger's authoritative DNS servers.
 */
if (
    strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
    && (string) ($_POST['dm_register_dns_live_ttl_1851'] ?? '') === '1'
) {
    $domainId = (int) ($_POST['domain_id'] ?? $_POST['domainid'] ?? 0);
    $clientId = (int) ($_SESSION['uid'] ?? $_SESSION['clientid'] ?? 0);
    $submittedToken = trim((string) ($_POST['dm_update_token_1580'] ?? ''));
    $expectedToken = dm_register_dns_update_1580_session_token();

    if ($clientId <= 0) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'Your client-area session has expired. Sign in again and retry.',
        ], 401);
    }

    if ($submittedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The DNS update security token was invalid or expired. Refresh the page and retry.',
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
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The domain could not be found for this client account.',
        ], 404);
    }

    $registrar = strtolower(trim((string) ($domain->registrar ?? '')));
    if (!preg_match('/(resellerclub|netearth|stargate|logicboxes)/i', $registrar)) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'Live RegistrarDNS TTL values are not available for this registrar module.',
        ], 422);
    }

    if (!dm_register_dns_update_1580_load_helpers()) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The registrar API helper functions were unavailable.',
        ], 500);
    }

    $probeCount = (int) ($_POST['dm_ttl_probe_count_1851'] ?? 0);
    if ($probeCount < 1 || $probeCount > 500) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The DNS record count did not reach the live TTL handler correctly. Refresh the page and try again.',
        ], 422);
    }

    $allowedTypes = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SRV'];
    $probes = [];
    for ($probeIndex = 0; $probeIndex < $probeCount; $probeIndex++) {
        $prefix = 'dm_ttl_probe_' . $probeIndex . '_';
        $type = strtoupper(trim(substr((string) ($_POST[$prefix . 'type_1851'] ?? ''), 0, 10)));
        $host = trim(substr((string) ($_POST[$prefix . 'host_1851'] ?? ''), 0, 255));
        $value = trim(substr((string) ($_POST[$prefix . 'value_1851'] ?? ''), 0, 4096));

        if (!in_array($type, $allowedTypes, true) || $value === '') {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => 'One DNS row could not be identified for live TTL loading. Refresh the page and try again.',
                'failedRow' => $probeIndex + 1,
            ], 422);
        }

        $probes[] = [
            'index' => $probeIndex,
            'recordId' => trim(substr((string) ($_POST[$prefix . 'recordId_1851'] ?? ''), 0, 255)),
            'type' => $type,
            'originalType' => $type,
            'originalHost' => $host,
            'newHost' => $host,
            'originalValue' => $value,
            'newValue' => $value,
            'originalPriority' => trim(substr((string) ($_POST[$prefix . 'priority_1851'] ?? ''), 0, 20)),
            'newPriority' => trim(substr((string) ($_POST[$prefix . 'priority_1851'] ?? ''), 0, 20)),
            // Deliberately blank: the purpose of this read is to discover the
            // authoritative TTL that WHMCS omitted from the page.
            'originalTtl' => '',
            'newTtl' => '',
        ];
    }

    $domainName = strtolower(rtrim(trim((string) $domain->domain), '.'));
    $credentials = dm_epp_find_credentials($registrar);
    $liveByType = [];
    foreach (array_values(array_unique(array_column($probes, 'type'))) as $type) {
        [$ok, $rows, $error] = dm_register_dns_update_1580_fetch_type($domainName, $type, $credentials);
        if (!$ok) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => $error ?: 'The live DNS records could not be read for TTL loading.',
            ], 502);
        }
        $liveByType[$type] = $rows;
    }

    $results = [];
    $resolvedCount = 0;
    $legacyTxtRepairedCount = 0;
    $legacyTxtRepairFailureCount = 0;
    $legacyTxtRepairMessages = [];
    foreach ($probes as $probe) {
        [$matched, $liveRecord, $matchError, $identityMode1885] = dm_register_dns_update_1580_readonly_match_1885(
            $domainName,
            $probe,
            $liveByType[$probe['type']] ?? []
        );

        $ttl = '';
        $ttlSource = '';
        $ttlError = '';
        $liveIdentityValue1885 = '';
        if ($matched && is_array($liveRecord)) {
            $liveRaw = is_array($liveRecord['raw'] ?? null) ? $liveRecord['raw'] : [];
            $liveIdentityValue1885 = trim((string) ($liveRecord['valueRaw'] ?? ''));
            $ttl = dm_register_dns_ttl_1852_from_row($liveRaw);
            if ($ttl !== '') {
                $ttlSource = 'neo-api';
            } else {
                [$ttlOk, $authoritativeTtl, $ttlError] = dm_register_dns_ttl_1852_authoritative(
                    $domainName,
                    (string) ($probe['originalHost'] ?? ''),
                    (string) ($probe['type'] ?? ''),
                    $liveIdentityValue1885 !== ''
                        ? $liveIdentityValue1885
                        : (string) ($probe['originalValue'] ?? ''),
                    (string) ($probe['originalPriority'] ?? ''),
                    true
                );
                if ($ttlOk) {
                    $ttl = dm_register_dns_update_1580_ttl_key($authoritativeTtl);
                    $ttlSource = 'authoritative-dns';
                }
            }
        }
        $resolved = $ttl !== '';
        if ($resolved) {
            $resolvedCount++;
        }

        $legacyRepairAttempted = false;
        $legacyTxtRepaired = false;
        $legacyRepairMessage = '';
        if ($resolved && $matched && is_array($liveRecord)) {
            [$legacyRepairAttempted, $legacyTxtRepaired, $legacyRepairMessage] =
                dm_register_dns_update_1580_auto_repair_legacy_txt_1881(
                    $domainName,
                    $probe,
                    $liveRecord,
                    $credentials,
                    $ttl
                );

            if ($legacyTxtRepaired) {
                $legacyTxtRepairedCount++;
            } elseif ($legacyRepairAttempted && trim((string) $legacyRepairMessage) !== '') {
                $legacyTxtRepairFailureCount++;
                $legacyTxtRepairMessages[] = (string) $legacyRepairMessage;
            }
        }

        $results[] = [
            'index' => $probe['index'],
            'resolved' => $resolved,
            'ttl' => $ttl,
            'source' => $ttlSource,
            'identityMode' => (string) ($identityMode1885 ?? ''),
            'liveOriginalValue' => $matched && $liveIdentityValue1885 !== ''
                ? $liveIdentityValue1885
                : '',
            'legacyTxtRepairAttempted' => $legacyRepairAttempted,
            'legacyTxtRepaired' => $legacyTxtRepaired,
            'legacyTxtRepairMessage' => $legacyRepairMessage,
            'message' => $resolved
                ? ($legacyTxtRepaired ? 'Live TTL loaded; legacy TXT quoting cleaned automatically.' : 'Live TTL loaded.')
                : ($matchError ?: ($ttlError ?: 'The live record was confirmed, but its authoritative TTL was unavailable.')),
        ];
    }

    $allResolved = $resolvedCount === count($probes);
    $allSafe = $allResolved && $legacyTxtRepairFailureCount === 0;
    $responseMessage = '';
    if ($legacyTxtRepairFailureCount > 0) {
        $responseMessage = 'A legacy TXT record was detected, but it could not be cleaned safely. '
            . (string) ($legacyTxtRepairMessages[0] ?? 'Reload the page before making DNS changes.');
    } elseif ($legacyTxtRepairedCount > 0) {
        $responseMessage = $legacyTxtRepairedCount === 1
            ? 'A legacy TXT record was cleaned automatically.'
            : $legacyTxtRepairedCount . ' legacy TXT records were cleaned automatically.';
    } elseif ($allResolved) {
        $responseMessage = $resolvedCount === 1 ? 'Live TTL loaded.' : $resolvedCount . ' live TTL values loaded.';
    } elseif ($resolvedCount > 0) {
        $responseMessage = $resolvedCount . ' of ' . count($probes) . ' live TTL values were loaded.';
    } else {
        $responseMessage = 'No live TTL values could be loaded.';
    }

    dm_register_dns_update_1580_json([
        'success' => $allSafe,
        'resolvedCount' => $resolvedCount,
        'attemptedCount' => count($probes),
        'legacyTxtRepairedCount' => $legacyTxtRepairedCount,
        'legacyTxtRepairFailureCount' => $legacyTxtRepairFailureCount,
        'message' => $responseMessage,
        'results' => $results,
    ], $allSafe ? 200 : ($resolvedCount > 0 && $legacyTxtRepairFailureCount === 0 ? 207 : 422));
}


if (
    strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
    && (string) ($_POST['dm_register_dns_direct_mx_add_1685'] ?? '') === '1'
) {
    $domainId = (int) ($_POST['domain_id'] ?? $_POST['domainid'] ?? 0);
    $clientId = (int) ($_SESSION['uid'] ?? $_SESSION['clientid'] ?? 0);
    $submittedToken = trim((string) ($_POST['dm_update_token_1580'] ?? ''));
    $expectedToken = dm_register_dns_update_1580_session_token();

    if ($clientId <= 0) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'Your client-area session has expired. Sign in again and retry.',
        ], 401);
    }

    if ($submittedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The DNS update security token was invalid or expired. Refresh the page and retry.',
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
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The domain could not be found for this client account.',
        ], 404);
    }

    $registrar = strtolower(trim((string) ($domain->registrar ?? '')));
    if (!preg_match('/(resellerclub|netearth|stargate|logicboxes)/i', $registrar)) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'Verified RegistrarDNS additions are not available for this registrar module.',
        ], 422);
    }

    if (!dm_register_dns_update_1580_load_helpers()) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The registrar API helper functions were unavailable.',
        ], 500);
    }

    $addCount = (int) ($_POST['dm_add_count_1685'] ?? 0);
    if ($addCount < 1 || $addCount > 50) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The DNS record count did not reach the verified add handler correctly. Refresh the page and try again.',
        ], 422);
    }

    // Patches 1686/1849: the verified add request accepts any batch composed
    // entirely of standard record types whose complete add parameters are
    // present in the current table. The legacy POST field name is retained for
    // compatibility with already-rendered RegistrarDNS pages.
    $allowedAddTypes = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT'];
    $records = [];
    $seenRequested = [];
    for ($index = 0; $index < $addCount; $index++) {
        $type = strtoupper(trim(substr((string) ($_POST['dm_add_' . $index . '_type_1685'] ?? 'MX'), 0, 10)));
        $host = trim(substr((string) ($_POST['dm_add_' . $index . '_host_1685'] ?? ''), 0, 255));
        $value = trim(substr((string) ($_POST['dm_add_' . $index . '_value_1685'] ?? ''), 0, 4096));
        $priorityRaw = trim(substr((string) ($_POST['dm_add_' . $index . '_priority_1685'] ?? ''), 0, 20));
        $ttlRaw = trim(substr((string) ($_POST['dm_add_' . $index . '_ttl_1685'] ?? ''), 0, 20));

        if (!in_array($type, $allowedAddTypes, true)) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => 'Record ' . ($index + 1) . ' uses a type that is not supported by verified direct add. No records were added.',
            ], 422);
        }
        if ($value === '') {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => $type . ' record ' . ($index + 1) . ' requires a value. No records were added.',
            ], 422);
        }
        if ($type === 'CNAME'
            && dm_register_dns_update_1580_relative_host($host, (string) $domain->domain) === '@') {
            $recordName = dm_register_dns_update_1580_fqdn_host($host, (string) $domain->domain);
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => 'A CNAME cannot be added for ' . $recordName . ' at the root domain because it would conflict with other DNS records. Use a subdomain instead. No records were added.',
                'failedRow' => $index + 1,
            ], 422);
        }
        if ($type === 'TXT') {
            [$txtValueOk, $normalizedTxtValue] = dm_register_dns_update_1580_txt_submission_value_1868($value);
            if (!$txtValueOk) {
                $recordName = dm_register_dns_update_1580_fqdn_host($host, (string) $domain->domain);
                dm_register_dns_update_1580_json([
                    'success' => false,
                    'message' => 'Unable to save TXT record for ' . $recordName . '. Please verify the value and try again.',
                    'failedRow' => $index + 1,
                ], 422);
            }
            $value = $normalizedTxtValue;
        }
        if ($type === 'MX' && ($priorityRaw === '' || !ctype_digit($priorityRaw))) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => 'MX record ' . ($index + 1) . ' requires a whole-number priority. No records were added.',
            ], 422);
        }
        if ($ttlRaw === '' || !ctype_digit($ttlRaw) || (int) $ttlRaw < 7200) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => $type . ' record ' . ($index + 1) . ' requires a TTL of at least 7200 seconds. No records were added.',
            ], 422);
        }

        $record = [
            'number' => $index + 1,
            'type' => $type,
            'newHost' => $host,
            'newValue' => $value,
            'newPriority' => $type === 'MX' ? (string) ((int) $priorityRaw) : '',
            'newTtl' => (string) ((int) $ttlRaw),
        ];
        $requestKey = implode('|', [
            $type,
            dm_register_dns_update_1580_relative_host($host, (string) $domain->domain),
            dm_register_dns_update_1580_value_key($value, $type),
            dm_register_dns_update_1580_priority_key($record['newPriority'], $type),
        ]);
        if (isset($seenRequested[$requestKey])) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => 'The same ' . $type . ' record was entered more than once. No records were added.',
            ], 422);
        }
        $seenRequested[$requestKey] = true;
        $records[] = $record;
    }

    $domainName = strtolower(rtrim(trim((string) $domain->domain), '.'));
    $credentials = dm_epp_find_credentials($registrar);
    $initialRowsByType = [];
    foreach (array_values(array_unique(array_column($records, 'type'))) as $type) {
        [$readOk, $initialRows, $readError] = dm_register_dns_update_1580_fetch_type(
            $domainName,
            $type,
            $credentials
        );
        if (!$readOk) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => $readError ?: 'The live ' . $type . ' records could not be read. No records were added.',
            ], 502);
        }
        $initialRowsByType[$type] = $initialRows;
    }

    foreach ($records as $record) {
        $duplicateProbe = $record;
        // TTL can be normalized by LogicBoxes. Identity is type, host, value,
        // and MX priority; do not permit an already-live duplicate through.
        $duplicateProbe['newTtl'] = '';
        if (count(dm_register_dns_update_1580_desired_candidates_1632(
            $domainName,
            $duplicateProbe,
            $initialRowsByType[$record['type']] ?? []
        )) > 0) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => $record['type'] . ' record ' . $record['number'] . ' already exists in the live DNS zone. No records were added.',
            ], 409);
        }
    }

    // Put MX records first. If an MX request is rejected, unrelated records in
    // the same submitted batch are not written before the primary requested fix.
    usort($records, static function (array $left, array $right): int {
        $leftMx = ($left['type'] ?? '') === 'MX' ? 0 : 1;
        $rightMx = ($right['type'] ?? '') === 'MX' ? 0 : 1;
        if ($leftMx !== $rightMx) {
            return $leftMx <=> $rightMx;
        }
        return ((int) ($left['number'] ?? 0)) <=> ((int) ($right['number'] ?? 0));
    });

    $results = [];
    $addedCount = 0;
    foreach ($records as $record) {
        [$added, $addMessage, $endpoint] = dm_register_dns_update_1580_add_live_1632(
            $domainName,
            $record,
            ['ttl' => (int) $record['newTtl']],
            $credentials
        );

        $verificationRecord = $record;
        // Verify the durable DNS identity. LogicBoxes may normalize TTL.
        $verificationRecord['newTtl'] = '';
        [$verified, $verifyError] = dm_register_dns_update_1580_verify_live_with_retries(
            $domainName,
            $verificationRecord,
            $credentials,
            $added ? [0, 350, 700, 1200] : [350, 900, 1600]
        );

        $success = $verified;
        $message = $verified
            ? $record['type'] . ' record added and verified.'
            : ($addMessage ?: ($verifyError ?: 'The DNS record was not added.'));
        if (!$added && $verified) {
            $message = 'The registrar response was unclear, but the ' . $record['type'] . ' record was found and verified live.';
        } elseif ($added && !$verified) {
            $message = $verifyError ?: 'The registrar accepted the ' . $record['type'] . ' record, but the live record could not be confirmed.';
        }

        $results[] = [
            'number' => $record['number'],
            'type' => $record['type'],
            'host' => $record['newHost'],
            'value' => $record['newValue'],
            'priority' => $record['newPriority'],
            'ttl' => $record['newTtl'],
            'added' => $success,
            'message' => $message,
            'apiEndpoint' => $endpoint,
        ];

        if ($success) {
            $addedCount++;
            continue;
        }
        break;
    }

    $attemptedCount = count($records);
    $allAdded = $addedCount === $attemptedCount;
    dm_register_dns_update_1580_json([
        'success' => $allAdded,
        'partialSuccess' => !$allAdded && $addedCount > 0,
        'addedCount' => $addedCount,
        'attemptedCount' => $attemptedCount,
        'message' => $allAdded
            ? ($addedCount === 1 ? 'DNS record added.' : $addedCount . ' DNS records added.')
            : ($addedCount > 0
                ? $addedCount . ' of ' . $attemptedCount . ' DNS records were added.'
                : 'No DNS records were added.'),
        'results' => $results,
    ], $allAdded ? 200 : ($addedCount > 0 ? 207 : 422));
}

if (
    strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
    && (string) ($_POST['dm_register_dns_direct_update_1580'] ?? '') === '1'
) {
    $domainId = (int) ($_POST['domain_id'] ?? $_POST['domainid'] ?? 0);
    $clientId = (int) ($_SESSION['uid'] ?? $_SESSION['clientid'] ?? 0);
    $submittedToken = trim((string) ($_POST['dm_update_token_1580'] ?? ''));
    $expectedToken = dm_register_dns_update_1580_session_token();

    if ($clientId <= 0) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'Your client-area session has expired. Sign in again and retry.',
        ], 401);
    }

    if ($submittedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The DNS update security token was invalid or expired. Refresh the page and retry.',
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
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The domain could not be found for this client account.',
        ], 404);
    }

    $registrar = strtolower(trim((string) ($domain->registrar ?? '')));
    if (!preg_match('/(resellerclub|netearth|stargate|logicboxes)/i', $registrar)) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'Direct RegistrarDNS editing is not available for this registrar module.',
        ], 422);
    }

    if (!dm_register_dns_update_1580_load_helpers()) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The registrar API helper functions were unavailable.',
        ], 500);
    }

    // WHMCS/request middleware on this installation did not reliably preserve
    // the previous JSON-in-FormData `changes` field. Read explicit indexed
    // fields instead so each changed row survives normal PHP form parsing.
    $changeCount = (int) ($_POST['dm_change_count_1580'] ?? 0);
    if ($changeCount < 1 || $changeCount > 50) {
        dm_register_dns_update_1580_json([
            'success' => false,
            'message' => 'The changed DNS row count did not reach the update handler correctly. Refresh the page and try again.',
        ], 422);
    }

    $changes = [];
    $fieldNames = [
        'recordId', 'type', 'originalType', 'originalHost', 'newHost',
        'originalValue', 'newValue', 'originalPriority', 'newPriority',
        'originalTtl', 'newTtl',
    ];

    for ($changeIndex = 0; $changeIndex < $changeCount; $changeIndex++) {
        $change = [];
        foreach ($fieldNames as $fieldName) {
            $postKey = 'dm_change_' . $changeIndex . '_' . $fieldName . '_1580';
            $change[$fieldName] = isset($_POST[$postKey]) ? (string) $_POST[$postKey] : '';
        }
        $changes[] = $change;
    }

    $allowedTypes = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SRV'];
    $cleanChanges = [];
    foreach ($changes as $index => $change) {
        if (!is_array($change)) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => 'One changed DNS row was invalid. No updates were attempted.',
            ], 422);
        }

        $type = strtoupper(trim(substr((string) ($change['type'] ?? ''), 0, 10)));
        $originalType = strtoupper(trim(substr((string) ($change['originalType'] ?? ''), 0, 10)));
        $originalHost = trim(substr((string) ($change['originalHost'] ?? ''), 0, 255));
        $newHost = trim(substr((string) ($change['newHost'] ?? ''), 0, 255));
        $originalValue = trim(substr((string) ($change['originalValue'] ?? ''), 0, 4096));
        $newValue = trim(substr((string) ($change['newValue'] ?? ''), 0, 4096));
        $originalTtl = trim(substr((string) ($change['originalTtl'] ?? ''), 0, 20));
        $newTtl = trim(substr((string) ($change['newTtl'] ?? ''), 0, 20));

        if (!in_array($type, $allowedTypes, true) || $originalType !== $type || $newValue === '') {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => 'A changed DNS row used an unsupported type or empty value. No updates were attempted.',
            ], 422);
        }

        if ($type === 'CNAME'
            && dm_register_dns_update_1580_relative_host($newHost, (string) $domain->domain) === '@') {
            $recordName = dm_register_dns_update_1580_fqdn_host($newHost, (string) $domain->domain);
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => 'A CNAME cannot be saved for ' . $recordName . ' at the root domain because it would conflict with other DNS records. Use a subdomain instead. No updates were attempted.',
                'failedRow' => $index + 1,
            ], 422);
        }

        if ($type === 'TXT') {
            [$txtValueOk, $normalizedTxtValue] = dm_register_dns_update_1580_txt_submission_value_1868($newValue);
            if (!$txtValueOk) {
                $recordName = dm_register_dns_update_1580_fqdn_host($newHost, (string) $domain->domain);
                dm_register_dns_update_1580_json([
                    'success' => false,
                    'message' => 'Unable to save TXT record for ' . $recordName . '. Please verify the value and try again.',
                    'failedRow' => $index + 1,
                ], 422);
            }
            $newValue = $normalizedTxtValue;
        }

        if (!preg_match('/^[0-9]+$/D', $newTtl)
            || (int) $newTtl < 7200
            || (int) $newTtl > 2147483647) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => 'TTL must be at least 7200 seconds. No updates were attempted.',
            ], 422);
        }

        $hostChanged = dm_register_dns_update_1580_relative_host($originalHost, (string) $domain->domain)
            !== dm_register_dns_update_1580_relative_host($newHost, (string) $domain->domain);

        $cleanChanges[] = [
            'number' => $index + 1,
            'recordId' => trim(substr((string) ($change['recordId'] ?? ''), 0, 255)),
            'type' => $type,
            'originalType' => $originalType,
            'originalHost' => $originalHost,
            'newHost' => $newHost,
            'originalValue' => $originalValue,
            'newValue' => $newValue,
            'originalPriority' => trim(substr((string) ($change['originalPriority'] ?? ''), 0, 20)),
            'newPriority' => trim(substr((string) ($change['newPriority'] ?? ''), 0, 20)),
            'originalTtl' => $originalTtl,
            'newTtl' => $newTtl,
            'ttlChanged' => dm_register_dns_update_1580_ttl_key($originalTtl)
                !== dm_register_dns_update_1580_ttl_key($newTtl),
            'hostChanged' => $hostChanged,
        ];
    }

    $domainName = strtolower(rtrim(trim((string) $domain->domain), '.'));
    $credentials = dm_epp_find_credentials($registrar);
    $liveByType = [];
    $resolved = [];

    // Validate every original row against a fresh live list before the first write.
    foreach (array_values(array_unique(array_column($cleanChanges, 'type'))) as $type) {
        [$ok, $rows, $error] = dm_register_dns_update_1580_fetch_type($domainName, $type, $credentials);
        if (!$ok) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => $error ?: 'The live DNS records could not be read. No updates were attempted.',
            ], 502);
        }
        $liveByType[$type] = $rows;
    }

    foreach ($cleanChanges as $change) {
        [$matched, $liveRecord, $matchError] = dm_register_dns_update_1580_match_live(
            $domainName,
            $change,
            $liveByType[$change['type']] ?? []
        );
        if (!$matched || !is_array($liveRecord)) {
            dm_register_dns_update_1580_json([
                'success' => false,
                'message' => $matchError ?: 'A changed live DNS record could not be verified. No updates were attempted.',
                'failedRow' => $change['number'],
            ], 409);
        }
        $resolved[] = [$change, $liveRecord];
    }

    $results = [];
    $updatedCount = 0;
    foreach ($resolved as [$change, $liveRecord]) {
        if (!empty($change['hostChanged'])) {
            [$moved, $moveMessage, $moveEndpoint] = dm_register_dns_update_1580_move_live_1632(
                $domainName,
                $change,
                $liveRecord,
                $credentials
            );

            $result = [
                'number' => $change['number'],
                'type' => $change['type'],
                'host' => $change['newHost'],
                'value' => $change['newValue'],
                'updated' => $moved,
                'retried' => false,
                'message' => $moveMessage,
                'apiEndpoint' => $moveEndpoint,
            ];
            $results[] = $result;
            if ($moved) {
                $updatedCount++;
                continue;
            }
            break;
        }

        [$updated, $message, $endpoint] = dm_register_dns_update_1580_update_live(
            $domainName,
            $change,
            $liveRecord,
            $credentials
        );

        $result = [
            'number' => $change['number'],
            'type' => $change['type'],
            'host' => $change['newHost'],
            'value' => $change['newValue'],
            'updated' => false,
            'retried' => false,
            'message' => $message,
            'apiEndpoint' => $endpoint,
        ];

        $verified = false;
        $verifyError = '';
        $ttlChanged = !empty($change['ttlChanged']);
        $successVerificationDelays = $ttlChanged
            ? [0, 1000, 2500, 5000]
            : [0, 450, 900];
        $timeoutVerificationDelays = $ttlChanged
            ? [600, 1500, 3000, 5000]
            : [600, 1200];

        if ($updated) {
            // Do not report success from the update response alone. Require a
            // fresh registrar read. TTL changes are verified against
            // authoritative DNS and receive a longer propagation window.
            [$verified, $verifyError] = dm_register_dns_update_1580_verify_live_with_retries(
                $domainName,
                $change,
                $credentials,
                $successVerificationDelays
            );
        } elseif (dm_register_dns_update_1580_is_timeout_error((string) $message)) {
            // A timed-out request may still have reached LogicBoxes. Verify the
            // requested value before considering any retry, preventing a late
            // successful first request from being submitted twice.
            [$verified, $verifyError] = dm_register_dns_update_1580_verify_live_with_retries(
                $domainName,
                $change,
                $credentials,
                $timeoutVerificationDelays
            );

            if (!$verified) {
                [$freshOk, $freshRows, $freshError] = dm_register_dns_update_1580_fetch_type(
                    $domainName,
                    $change['type'],
                    $credentials
                );

                if ($freshOk) {
                    [$stillOriginal, $freshLiveRecord, $matchError] = dm_register_dns_update_1580_match_live(
                        $domainName,
                        $change,
                        $freshRows
                    );

                    // Retry exactly once, and only if the original value still
                    // exists as one unique live record. If the first request was
                    // applied late, this condition will no longer be true.
                    if ($stillOriginal && is_array($freshLiveRecord)) {
                        $result['retried'] = true;
                        [$retryUpdated, $retryMessage, $retryEndpoint] = dm_register_dns_update_1580_update_live(
                            $domainName,
                            $change,
                            $freshLiveRecord,
                            $credentials
                        );
                        if ($retryEndpoint !== '') {
                            $result['apiEndpoint'] = $retryEndpoint;
                        }

                        [$verified, $verifyError] = dm_register_dns_update_1580_verify_live_with_retries(
                            $domainName,
                            $change,
                            $credentials,
                            $ttlChanged ? [0, 1000, 2500, 5000] : [0, 500, 1000]
                        );

                        if (!$verified) {
                            $message = $retryUpdated
                                ? ($verifyError ?: 'The retried update could not be verified.')
                                : ((string) $message . ' Retry: ' . ($retryMessage ?: 'The registrar did not complete the retry.'));
                        }
                    } else {
                        // The original row disappeared or changed while the first
                        // request was unresolved. Verify the desired value one
                        // final time, but never issue a blind retry.
                        [$verified, $verifyError] = dm_register_dns_update_1580_verify_live_with_retries(
                            $domainName,
                            $change,
                            $credentials,
                            $ttlChanged ? [1000, 2500, 5000] : [500]
                        );
                        if (!$verified && $matchError) {
                            $message .= ' ' . $matchError;
                        }
                    }
                } else {
                    $message .= ' ' . ($freshError ?: 'The live zone could not be re-read after the timeout.');
                }
            }
        }

        if ($verified) {
            $result['updated'] = true;
            $result['message'] = $result['retried']
                ? 'DNS record updated after one verified retry.'
                : 'DNS record updated and verified.';
            $updatedCount++;
        } elseif ($updated) {
            $genericVerifyFailure = $verifyError === ''
                || $verifyError === 'The registrar accepted the request, but the updated live DNS record could not be confirmed.'
                || $verifyError === 'The registrar accepted the request, but the live DNS record could not be verified.';
            if ($change['type'] === 'TXT' && $genericVerifyFailure) {
                $recordName = dm_register_dns_update_1580_fqdn_host(
                    (string) ($change['newHost'] ?? ''),
                    $domainName
                );
                $result['message'] = 'Unable to save TXT record for ' . $recordName . '. Please verify the value and try again.';
            } else {
                $result['message'] = $verifyError ?: 'The registrar accepted the request, but the live DNS record could not be verified.';
            }
        } else {
            $result['message'] = $message ?: ($verifyError ?: 'The DNS record was not updated.');
        }

        $results[] = $result;
        if (!$result['updated']) {
            break;
        }
    }

    $attemptedCount = count($cleanChanges);
    $allUpdated = $updatedCount === $attemptedCount;
    $statusCode = $allUpdated ? 200 : ($updatedCount > 0 ? 207 : 422);

    dm_register_dns_update_1580_json([
        'success' => $allUpdated,
        'partialSuccess' => !$allUpdated && $updatedCount > 0,
        'updatedCount' => $updatedCount,
        'attemptedCount' => $attemptedCount,
        'message' => $allUpdated
            ? ($updatedCount === 1 ? 'DNS record updated.' : $updatedCount . ' DNS records updated.')
            : ($updatedCount > 0
                ? $updatedCount . ' of ' . $attemptedCount . ' DNS records were updated. Reloading the live zone is required.'
                : 'No DNS records were updated.'),
        'results' => $results,
    ], $statusCode);
}

add_hook('ClientAreaFooterOutput', 1580, function ($vars) {
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

    $config = [
        'domainId' => $domainId,
        'token' => dm_register_dns_update_1580_session_token(),
    ];
    $configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return '<script id="dm-register-dns-direct-update-1580-config">window.dmRegisterDnsDirectUpdate1580Config=' . $configJson . ';</script>';
});
