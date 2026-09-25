<?php

declare(strict_types=1);

namespace DomainMonger\DnsMigrator;

use RuntimeException;
use Throwable;
use WHMCS\Database\Capsule;

final class MigrationException extends RuntimeException
{
}

final class RecordTools
{
    public const COMMON_TYPES = ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'SRV', 'NS'];

    public static function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;
        $domain = preg_replace('#[/:].*$#', '', $domain) ?? $domain;
        return rtrim($domain, '.');
    }

    public static function validDomain(string $domain): bool
    {
        return $domain !== ''
            && strlen($domain) <= 253
            && (bool) preg_match(
                '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i',
                $domain
            );
    }

    public static function relativeHost(string $host, string $domain): string
    {
        $host = strtolower(trim($host));
        $host = trim($host, '"\'');
        $host = rtrim($host, '.');
        $domain = self::normalizeDomain($domain);

        if ($host === '' || $host === '@' || $host === $domain) {
            return '@';
        }

        $suffix = '.' . $domain;
        if (str_ends_with($host, $suffix)) {
            $host = substr($host, 0, -strlen($suffix));
        }

        return $host === '' ? '@' : $host;
    }

    public static function fqdn(string $host, string $domain, bool $trailingDot = true): string
    {
        $host = trim($host);
        $domain = self::normalizeDomain($domain);

        if ($host === '' || $host === '@') {
            $name = $domain;
        } elseif (str_ends_with(strtolower(rtrim($host, '.')), '.' . $domain)
            || strtolower(rtrim($host, '.')) === $domain) {
            $name = rtrim($host, '.');
        } else {
            $name = rtrim($host, '.') . '.' . $domain;
        }

        return $trailingDot ? $name . '.' : $name;
    }

    public static function target(string $value): string
    {
        $value = trim($value);
        $value = trim($value, '"\'');
        return strtolower(rtrim($value, '.'));
    }

    public static function text(string $value): string
    {
        $value = trim($value);
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        return $value;
    }

    public static function normalizeRecord(array $record, string $domain): ?array
    {
        $type = strtoupper(trim((string) ($record['type'] ?? '')));
        if (!in_array($type, self::COMMON_TYPES, true)) {
            return null;
        }

        $name = self::relativeHost((string) ($record['name'] ?? $record['host'] ?? '@'), $domain);
        $ttl = (int) ($record['ttl'] ?? 3600);
        if ($ttl < 60) {
            $ttl = 3600;
        }

        $normalized = [
            'type' => $type,
            'name' => $name,
            'value' => '',
            'ttl' => $ttl,
            'priority' => null,
            'weight' => null,
            'port' => null,
        ];

        switch ($type) {
            case 'A':
            case 'AAAA':
                $normalized['value'] = trim((string) ($record['value'] ?? $record['address'] ?? ''));
                break;
            case 'CNAME':
            case 'NS':
                $normalized['value'] = self::target((string) ($record['value'] ?? $record['target'] ?? ''));
                break;
            case 'MX':
                $normalized['value'] = self::target((string) ($record['value'] ?? $record['target'] ?? ''));
                $normalized['priority'] = (int) ($record['priority'] ?? 0);
                break;
            case 'TXT':
                $normalized['value'] = self::text((string) ($record['value'] ?? $record['text'] ?? ''));
                break;
            case 'SRV':
                $normalized['value'] = self::target((string) ($record['value'] ?? $record['target'] ?? ''));
                $normalized['priority'] = (int) ($record['priority'] ?? 0);
                $normalized['weight'] = (int) ($record['weight'] ?? 0);
                $normalized['port'] = (int) ($record['port'] ?? 0);
                break;
        }

        if ($normalized['value'] === '') {
            return null;
        }

        return $normalized;
    }

    public static function signature(array $record): string
    {
        return implode('|', [
            strtoupper((string) ($record['type'] ?? '')),
            strtolower((string) ($record['name'] ?? '@')),
            strtolower((string) ($record['value'] ?? '')),
            (string) ($record['priority'] ?? ''),
            (string) ($record['weight'] ?? ''),
            (string) ($record['port'] ?? ''),
        ]);
    }

    public static function isRootNameserver(array $record): bool
    {
        return strtoupper((string) ($record['type'] ?? '')) === 'NS'
            && in_array((string) ($record['name'] ?? '@'), ['', '@'], true);
    }

    public static function display(array $record): string
    {
        $type = strtoupper((string) ($record['type'] ?? ''));
        $name = (string) ($record['name'] ?? '@');
        $value = (string) ($record['value'] ?? '');

        if ($type === 'MX') {
            return $name . ' MX ' . (int) ($record['priority'] ?? 0) . ' ' . $value;
        }
        if ($type === 'SRV') {
            return $name . ' SRV '
                . (int) ($record['priority'] ?? 0) . ' '
                . (int) ($record['weight'] ?? 0) . ' '
                . (int) ($record['port'] ?? 0) . ' ' . $value;
        }

        return $name . ' ' . $type . ' ' . $value;
    }
}

final class ServiceResolver
{
    private string $manageRoot;

    public function __construct(string $manageRoot)
    {
        $this->manageRoot = rtrim($manageRoot, '/\\');
    }

    public function domainUserId(string $domain): int
    {
        $domain = RecordTools::normalizeDomain($domain);

        try {
            $registered = Capsule::table('tbldomains')
                ->whereRaw('LOWER(domain) = ?', [$domain])
                ->select('userid')
                ->first();
            if ($registered) {
                return (int) $registered->userid;
            }

            $hosting = Capsule::table('tblhosting')
                ->whereRaw('LOWER(domain) = ?', [$domain])
                ->select('userid')
                ->first();
            if ($hosting) {
                return (int) $hosting->userid;
            }
        } catch (Throwable $e) {
            throw new MigrationException('WHMCS could not determine the account that owns this domain.');
        }

        return 0;
    }

    public function cpanelServiceId(string $domain): int
    {
        $domain = RecordTools::normalizeDomain($domain);

        try {
            // An exact active cPanel hosting-domain match is deterministic and
            // should not be hidden by a different WHMCS domain-registration owner.
            $exact = Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->whereRaw('LOWER(tblproducts.servertype) = ?', ['cpanel'])
                ->where('tblhosting.domainstatus', 'Active')
                ->whereRaw('LOWER(tblhosting.domain) = ?', [$domain])
                ->select('tblhosting.id')
                ->limit(2)
                ->get();

            if (count($exact) === 1) {
                return (int) $exact[0]->id;
            }
        } catch (Throwable $e) {
            throw new MigrationException('WHMCS could not resolve the matching cPanel hosting account.');
        }

        return $this->serviceId($domain, 'cpanel');
    }

    public function cpanelCandidateServiceIds(string $domain): array
    {
        $domain = RecordTools::normalizeDomain($domain);
        $userId = $this->domainUserId($domain);
        if ($userId < 1) {
            return [];
        }

        try {
            $services = Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->where('tblhosting.userid', $userId)
                ->whereRaw('LOWER(tblproducts.servertype) = ?', ['cpanel'])
                ->where('tblhosting.domainstatus', 'Active')
                ->select('tblhosting.id')
                ->orderBy('tblhosting.id')
                ->get();
        } catch (Throwable $e) {
            throw new MigrationException('WHMCS could not list the client cPanel services for this domain.');
        }

        $ids = [];
        foreach ($services as $service) {
            $id = (int) ($service->id ?? 0);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    public function dnsPlusServiceId(string $domain): int
    {
        $domain = RecordTools::normalizeDomain($domain);
        $userId = $this->domainUserId($domain);

        try {
            if (Capsule::schema()->hasTable('mod_cloudns_zones')) {
                $query = Capsule::table('mod_cloudns_zones')
                    ->join('tblhosting', 'tblhosting.id', '=', 'mod_cloudns_zones.serviceid')
                    ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                    ->whereRaw('LOWER(mod_cloudns_zones.name) = ?', [$domain])
                    ->whereRaw('LOWER(tblproducts.servertype) = ?', ['cloudns'])
                    ->where('tblhosting.domainstatus', 'Active');

                if ($userId > 0) {
                    $query->where('tblhosting.userid', $userId);
                }

                $zone = $query->select('tblhosting.id')->first();
                if ($zone) {
                    return (int) $zone->id;
                }
            }
        } catch (Throwable $e) {
            // Fall through to the standard safe service resolver.
        }

        return $this->serviceId($domain, 'cloudns');
    }

    private function serviceId(string $domain, string $serverType): int
    {
        $domain = RecordTools::normalizeDomain($domain);
        $userId = $this->domainUserId($domain);

        try {
            $base = Capsule::table('tblhosting')
                ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->whereRaw('LOWER(tblproducts.servertype) = ?', [strtolower($serverType)])
                ->where('tblhosting.domainstatus', 'Active');

            if ($userId > 0) {
                $base->where('tblhosting.userid', $userId);
            }

            $exact = (clone $base)
                ->whereRaw('LOWER(tblhosting.domain) = ?', [$domain])
                ->select('tblhosting.id')
                ->first();
            if ($exact) {
                return (int) $exact->id;
            }

            if ($userId < 1) {
                return 0;
            }

            $services = (clone $base)
                ->select('tblhosting.id')
                ->limit(2)
                ->get();

            if (count($services) === 1) {
                return (int) $services[0]->id;
            }
        } catch (Throwable $e) {
            throw new MigrationException('WHMCS could not resolve a safe ' . $serverType . ' service for this domain.');
        }

        return 0;
    }

    public function moduleParams(int $serviceId): array
    {
        if ($serviceId < 1) {
            throw new MigrationException('A valid WHMCS service could not be resolved.');
        }

        $moduleFunctions = $this->manageRoot . '/includes/modulefunctions.php';
        if (!is_file($moduleFunctions)) {
            throw new MigrationException('WHMCS module functions are unavailable.');
        }

        require_once $moduleFunctions;
        if (!function_exists('ModuleBuildParams')) {
            throw new MigrationException('WHMCS could not prepare the service connection.');
        }

        try {
            $params = ModuleBuildParams($serviceId);
        } catch (Throwable $e) {
            throw new MigrationException('WHMCS could not prepare service ' . $serviceId . '.');
        }

        if (!is_array($params)) {
            throw new MigrationException('WHMCS returned an invalid service connection.');
        }

        return $params;
    }

    public function associateDnsPlusZone(string $domain, int $serviceId): void
    {
        if ($serviceId < 1 || !Capsule::schema()->hasTable('mod_cloudns_zones')) {
            return;
        }

        try {
            $exists = Capsule::table('mod_cloudns_zones')
                ->whereRaw('LOWER(name) = ?', [RecordTools::normalizeDomain($domain)])
                ->exists();

            if (!$exists) {
                Capsule::table('mod_cloudns_zones')->insert([
                    'serviceid' => $serviceId,
                    'name' => RecordTools::normalizeDomain($domain),
                ]);
            }
        } catch (Throwable $e) {
            throw new MigrationException('The DNSPlus zone was created, but WHMCS could not associate it with the DNSPlus service.');
        }
    }
}

interface Adapter
{
    public function label(): string;

    public function preflight(string $domain): void;

    /** @return array<int,array<string,mixed>> */
    public function read(string $domain): array;

    /** @return array<int,array<string,mixed>> Existing records left after preparation. */
    public function prepareDestination(string $domain, bool $replace): array;

    /** @return array{ok:bool,message:string} */
    public function addRecord(string $domain, array $record): array;
}

abstract class CurlAdapter
{
    protected function request(
        string $method,
        string $url,
        array $params = [],
        array $headers = [],
        bool $json = true,
        bool $allowJsonErrorResponse = false
    ) {
        $method = strtoupper($method);
        if ($method === 'GET' && $params) {
            $url .= (str_contains($url, '?') ? '&' : '?')
                . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }

        $curl = curl_init($url);
        if ($curl === false) {
            throw new MigrationException('The HTTP connection could not be started.');
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 12,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'DomainMonger-DNS-Migrator/2.0',
            CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
        ];

        if ($method !== 'GET') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
        }

        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $curlError = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (!is_string($body) || $body === '') {
            throw new MigrationException('The remote DNS system returned no response'
                . ($curlError !== '' ? ' (cURL: ' . $curlError . ')' : '') . '.');
        }

        if (!$json) {
            if ($status < 200 || $status >= 300) {
                throw new MigrationException(
                    'The remote DNS system returned HTTP ' . $status . ': ' . $this->responseMessage($body)
                );
            }
            return $body;
        }

        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($status < 200 || $status >= 300) {
                throw new MigrationException(
                    'The remote DNS system returned HTTP ' . $status . ': ' . $this->responseMessage($body)
                );
            }
            throw new MigrationException('The remote DNS system returned an invalid response.');
        }

        if (!is_array($decoded)) {
            $decoded = ['_scalar' => $decoded];
        }

        if ($status < 200 || $status >= 300) {
            if ($allowJsonErrorResponse) {
                $decoded['_http_status'] = $status;
                return $decoded;
            }

            throw new MigrationException(
                'The remote DNS system returned HTTP ' . $status . ': ' . $this->jsonResponseMessage($decoded)
            );
        }

        return $decoded;
    }

    private function jsonResponseMessage(array $response): string
    {
        foreach (['statusDescription', 'description', 'message', 'error', 'msg'] as $key) {
            if (isset($response[$key]) && is_scalar($response[$key])) {
                $message = trim((string) $response[$key]);
                if ($message !== '') {
                    return $this->responseMessage($message);
                }
            }
        }

        return 'The remote system rejected the request.';
    }

    private function responseMessage(string $message): string
    {
        $message = trim(strip_tags($message));
        $message = trim(preg_replace('/\s+/', ' ', $message) ?? $message);
        if ($message === '') {
            return 'The remote system rejected the request.';
        }
        return strlen($message) > 240 ? substr($message, 0, 237) . '...' : $message;
    }
}

final class RegisterDnsAdapter extends CurlAdapter implements Adapter
{
    private string $baseUrl;
    private string $resellerId;
    private string $apiKey;
    private const RECORDS_PER_PAGE = 50;

    public function __construct(array $vars)
    {
        $url = trim((string) ($vars['neo_url'] ?? ''));
        if ($url === '') {
            $url = 'https://httpapi.com/api';
        }
        if (!preg_match('#^https://#i', $url)) {
            throw new MigrationException('RegistrarDNS API URL must use HTTPS.');
        }

        $url = rtrim($url, '/');
        if (!str_ends_with(strtolower($url), '/api')) {
            $url .= '/api';
        }

        $this->baseUrl = $url;
        $this->resellerId = trim((string) ($vars['neo_username'] ?? ''));
        $this->apiKey = trim((string) ($vars['neo_key'] ?? ''));
    }

    public function label(): string
    {
        return 'RegistrarDNS';
    }

    public function preflight(string $domain): void
    {
        $this->assertConfigured();
        $this->orderId($domain);
    }

    public function read(string $domain): array
    {
        $this->assertConfigured();
        $records = [];
        $types = RecordTools::COMMON_TYPES;

        foreach ($types as $type) {
            for ($page = 1; $page <= 100; $page++) {
                $response = $this->api('GET', '/dns/manage/search-records.json', [
                    'domain-name' => $domain,
                    'type' => $type,
                    'no-of-records' => self::RECORDS_PER_PAGE,
                    'page-no' => $page,
                ]);

                $this->assertApiSuccess($response, 'reading ' . $type . ' records', true);
                $rows = $this->numericRows($response);

                foreach ($rows as $row) {
                    $rowType = strtoupper((string) ($row['type'] ?? $type));
                    $normalized = RecordTools::normalizeRecord([
                        'type' => $rowType,
                        'name' => (string) ($row['host'] ?? '@'),
                        'value' => (string) ($row['value'] ?? ''),
                        'ttl' => (int) ($row['ttl'] ?? 14400),
                        'priority' => (int) ($row['priority'] ?? 0),
                        'weight' => (int) ($row['weight'] ?? 0),
                        'port' => (int) ($row['port'] ?? 0),
                    ], $domain);
                    if ($normalized !== null) {
                        $records[] = $normalized;
                    }
                }

                if (count($rows) < self::RECORDS_PER_PAGE) {
                    break;
                }
            }
        }

        return $this->dedupe($records);
    }

    public function prepareDestination(string $domain, bool $replace): array
    {
        $this->assertConfigured();
        $orderId = $this->orderId($domain);

        $activation = $this->apiRaw('POST', '/dns/activate.xml', ['order-id' => $orderId]);
        if (!$this->activationSucceeded($activation)) {
            $plain = strtolower(strip_tags($activation));
            if (!str_contains($plain, 'already') && !str_contains($plain, 'active')) {
                throw new MigrationException('RegistrarDNS could not activate the DNS service: '
                    . $this->shortMessage(strip_tags($activation)));
            }
        }

        $existing = $this->read($domain);
        if (!$replace) {
            return $existing;
        }

        $left = [];
        foreach ($existing as $record) {
            if (RecordTools::isRootNameserver($record)) {
                $left[] = $record;
                continue;
            }

            $result = $this->deleteRecord($domain, $record);
            if (!$result['ok']) {
                throw new MigrationException($result['message']);
            }
        }

        return $left;
    }

    public function addRecord(string $domain, array $record): array
    {
        $type = strtoupper((string) $record['type']);
        $endpoint = [
            'A' => 'add-ipv4-record.json',
            'AAAA' => 'add-ipv6-record.json',
            'CNAME' => 'add-cname-record.json',
            'MX' => 'add-mx-record.json',
            'TXT' => 'add-txt-record.json',
            'SRV' => 'add-srv-record.json',
            'NS' => 'add-ns-record.json',
        ][$type] ?? '';

        if ($endpoint === '') {
            return ['ok' => false, 'message' => 'RegistrarDNS does not support this record type.'];
        }

        $params = $this->recordParams($domain, $record);
        $response = $this->api('POST', '/dns/manage/' . $endpoint, $params);
        $error = $this->apiError($response);

        return $error === ''
            ? ['ok' => true, 'message' => 'Record added.']
            : ['ok' => false, 'message' => 'RegistrarDNS: ' . $error];
    }

    private function deleteRecord(string $domain, array $record): array
    {
        $type = strtoupper((string) $record['type']);
        $endpoint = [
            'A' => 'delete-ipv4-record.json',
            'AAAA' => 'delete-ipv6-record.json',
            'CNAME' => 'delete-cname-record.json',
            'MX' => 'delete-mx-record.json',
            'TXT' => 'delete-txt-record.json',
            'SRV' => 'delete-srv-record.json',
            'NS' => 'delete-ns-record.json',
        ][$type] ?? '';

        if ($endpoint === '') {
            return ['ok' => true, 'message' => 'Unsupported record ignored.'];
        }

        $response = $this->api('POST', '/dns/manage/' . $endpoint, $this->recordParams($domain, $record));
        $error = $this->apiError($response);

        return $error === ''
            ? ['ok' => true, 'message' => 'Record removed.']
            : ['ok' => false, 'message' => 'RegistrarDNS could not remove '
                . RecordTools::display($record) . ': ' . $error];
    }

    private function recordParams(string $domain, array $record): array
    {
        $type = strtoupper((string) $record['type']);
        $host = (string) ($record['name'] ?? '@');
        if ($host === '@') {
            $host = '';
        }

        $params = [
            'domain-name' => $domain,
            'host' => $host,
            'value' => (string) $record['value'],
            'ttl' => (int) ($record['ttl'] ?? 14400),
        ];

        if ($type === 'MX' || $type === 'SRV') {
            $params['priority'] = (int) ($record['priority'] ?? 0);
        }
        if ($type === 'SRV') {
            $params['weight'] = (int) ($record['weight'] ?? 0);
            $params['port'] = (int) ($record['port'] ?? 0);
        }

        return $params;
    }

    private function orderId(string $domain): int
    {
        $response = $this->api('GET', '/domains/orderid.json', ['domain-name' => $domain]);
        $error = $this->apiError($response);
        if ($error !== '') {
            throw new MigrationException('RegistrarDNS order lookup failed: ' . $error);
        }

        $value = $response['orderid'] ?? $response['order-id'] ?? $response['_scalar'] ?? null;
        if ($value === null && count($response) === 1) {
            $value = reset($response);
        }
        if (is_numeric($value) && (int) $value > 0) {
            return (int) $value;
        }

        throw new MigrationException('RegistrarDNS did not return a registrar order ID for this domain.');
    }

    private function api(string $method, string $path, array $params): array
    {
        $params = array_merge([
            'auth-userid' => $this->resellerId,
            'api-key' => $this->apiKey,
        ], $params);

        return $this->request(
            $method,
            $this->baseUrl . $path,
            $params,
            [],
            true,
            true
        );
    }

    private function apiRaw(string $method, string $path, array $params): string
    {
        $params = array_merge([
            'auth-userid' => $this->resellerId,
            'api-key' => $this->apiKey,
        ], $params);

        return (string) $this->request($method, $this->baseUrl . $path, $params, ['Accept: application/xml'], false);
    }

    private function assertConfigured(): void
    {
        if ($this->resellerId === '' || $this->apiKey === '') {
            throw new MigrationException('RegistrarDNS API credentials are incomplete in the DNS Migrator addon settings.');
        }
    }

    private function assertApiSuccess(array $response, string $action, bool $allowEmpty): void
    {
        $error = $this->apiError($response);
        if ($error === '') {
            return;
        }

        $lowerError = strtolower($error);
        if ($allowEmpty && str_contains($lowerError, 'no record')) {
            return;
        }

        if (
            str_contains($lowerError, "website doesn't exist")
            || str_contains($lowerError, 'website does not exist')
            || str_contains($lowerError, 'website not found')
        ) {
            throw new MigrationException('No RegistrarDNS zone exists for this domain.');
        }

        throw new MigrationException('RegistrarDNS failed while ' . $action . ': ' . $error);
    }

    private function apiError(array $response): string
    {
        if (array_key_exists('_scalar', $response)) {
            $raw = $response['_scalar'];
            if ($raw === true) {
                return '';
            }
            if ($raw === false || $raw === null) {
                return 'The registrar returned an unsuccessful response.';
            }

            $scalar = trim((string) $raw);
            $lower = strtolower($scalar);
            if ($this->isSuccessfulApiMessage($scalar)) {
                return '';
            }
            if ($scalar !== '' && is_numeric($scalar) && (float) $scalar > 0) {
                return '';
            }
            if ($scalar === '' || in_array($lower, ['0', 'false', 'failed', 'failure', 'error'], true)) {
                return 'The registrar returned an unsuccessful response.';
            }
            return $this->shortMessage($scalar);
        }

        foreach (['error', 'message', 'msg', 'statusDescription', 'description'] as $key) {
            if (isset($response[$key]) && is_scalar($response[$key])) {
                $value = trim((string) $response[$key]);
                if ($value !== '') {
                    if ($this->isSuccessfulApiMessage($value)) {
                        continue;
                    }
                    if ($key === 'message' && !isset($response['status']) && $this->numericRows($response)) {
                        continue;
                    }
                    return $this->shortMessage($value);
                }
            }
        }

        $status = strtolower(trim((string) ($response['status'] ?? '')));
        if (in_array($status, ['error', 'failed', 'failure'], true)) {
            return 'The registrar rejected the request.';
        }

        return '';
    }

    private function isSuccessfulApiMessage(string $message): bool
    {
        $message = strtolower(trim(strip_tags($message)));
        $message = trim($message, " \t\n\r\0\x0B.!");

        if (in_array($message, ['success', 'successful', 'ok', 'true'], true)) {
            return true;
        }

        if (str_contains($message, 'unsuccess') || str_contains($message, 'not successful')) {
            return false;
        }

        return (bool) preg_match(
            '/^(?:dns\s+)?records?\s+(?:was\s+|were\s+)?(?:added|created|deleted|removed|updated)\s+successfully$/i',
            $message
        );
    }

    private function numericRows(array $response): array
    {
        $rows = [];
        foreach ($response as $key => $value) {
            if ((is_int($key) || ctype_digit((string) $key)) && is_array($value)) {
                $rows[] = $value;
            }
        }
        return $rows;
    }

    private function activationSucceeded(string $response): bool
    {
        $plain = strtolower(trim(strip_tags($response)));
        return $plain !== '' && (str_contains($plain, 'success') || str_contains($plain, '<status>success'));
    }

    private function dedupe(array $records): array
    {
        $out = [];
        foreach ($records as $record) {
            $out[RecordTools::signature($record)] = $record;
        }
        return array_values($out);
    }

    private function shortMessage(string $message): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $message) ?? $message);
        return strlen($message) > 240 ? substr($message, 0, 237) . '...' : $message;
    }
}

final class DnsPlusAdapter extends CurlAdapter implements Adapter
{
    private ServiceResolver $resolver;
    private string $authId = '';
    private string $authPassword = '';

    public function __construct(ServiceResolver $resolver, string $manageRoot)
    {
        $this->resolver = $resolver;
        $configFile = rtrim($manageRoot, '/\\') . '/modules/servers/cloudns/cloudns_core/configuration.php';
        if (!is_file($configFile)) {
            throw new MigrationException('The installed DNSPlus module configuration could not be found.');
        }

        require_once $configFile;
        if (!class_exists('Cloudns_Configuration')) {
            throw new MigrationException('The installed DNSPlus module configuration could not be loaded.');
        }

        $config = new \Cloudns_Configuration();
        $this->authId = trim((string) $config->getApiUser());
        $this->authPassword = (string) $config->getApiPassword();
    }

    public function label(): string
    {
        return 'DNSPlus';
    }

    public function preflight(string $domain): void
    {
        $this->assertConfigured();
        $zone = $this->zoneInfo($domain, false);
        if (!$zone) {
            $serviceId = $this->resolver->dnsPlusServiceId($domain);
            if ($serviceId < 1) {
                throw new MigrationException('No matching DNSPlus zone or unambiguous active DNSPlus service was found.');
            }
        }
    }

    public function read(string $domain): array
    {
        $this->assertConfigured();
        $records = [];

        for ($page = 1; $page <= 100; $page++) {
            $response = $this->api('dns/records.json', [
                'domain-name' => $domain,
                'rows-per-page' => 100,
                'page' => $page,
                'include-notes' => 0,
            ]);
            $this->assertSuccess($response, 'reading the DNSPlus zone');

            $pageCount = 0;
            foreach ($response as $id => $row) {
                if (!is_array($row) || !isset($row['type'])) {
                    continue;
                }
                $pageCount++;

                $type = strtoupper((string) $row['type']);
                $value = (string) ($row['record'] ?? '');
                $normalized = RecordTools::normalizeRecord([
                    'type' => $type,
                    'name' => (string) ($row['host'] ?? '@'),
                    'value' => $value,
                    'ttl' => (int) ($row['ttl'] ?? 3600),
                    'priority' => (int) ($row['priority'] ?? 0),
                    'weight' => (int) ($row['weight'] ?? 0),
                    'port' => (int) ($row['port'] ?? 0),
                ], $domain);

                if ($normalized !== null) {
                    $normalized['_remote_id'] = (string) ($row['id'] ?? $id);
                    $records[] = $normalized;
                }
            }

            if ($pageCount < 100) {
                break;
            }
        }

        return $this->dedupe($records);
    }

    public function prepareDestination(string $domain, bool $replace): array
    {
        $this->assertConfigured();
        $zone = $this->zoneInfo($domain, false);
        $serviceId = $this->resolver->dnsPlusServiceId($domain);

        if (!$zone) {
            if ($serviceId < 1) {
                throw new MigrationException('No unambiguous active DNSPlus service is available for this domain.');
            }

            $response = $this->api('dns/register.json', [
                'domain-name' => $domain,
                'zone-type' => 'master',
                'ns' => [
                    'ns31.domainmonger.com',
                    'ns32.domainmonger.com',
                    'ns33.domainmonger.com',
                    'ns34.domainmonger.com',
                ],
            ]);
            $this->assertSuccess($response, 'creating the DNSPlus zone');
            $this->resolver->associateDnsPlusZone($domain, $serviceId);
        } elseif ($serviceId > 0) {
            $this->resolver->associateDnsPlusZone($domain, $serviceId);
        }

        $existing = $this->read($domain);
        if (!$replace) {
            return $existing;
        }

        $left = [];
        foreach ($existing as $record) {
            if (RecordTools::isRootNameserver($record)) {
                $left[] = $record;
                continue;
            }

            $recordId = (string) ($record['_remote_id'] ?? '');
            if ($recordId === '') {
                throw new MigrationException('DNSPlus did not return an ID for ' . RecordTools::display($record) . '.');
            }

            $response = $this->api('dns/delete-record.json', [
                'domain-name' => $domain,
                'record-id' => $recordId,
            ]);
            $this->assertSuccess($response, 'removing ' . RecordTools::display($record));
        }

        return $left;
    }

    public function addRecord(string $domain, array $record): array
    {
        $type = strtoupper((string) $record['type']);
        if (!in_array($type, RecordTools::COMMON_TYPES, true)) {
            return ['ok' => false, 'message' => 'DNSPlus does not support this normalized record type.'];
        }

        $host = (string) ($record['name'] ?? '@');
        if ($host === '@') {
            $host = '';
        }

        $sourceTtl = (int) ($record['ttl'] ?? 3600);
        $dnsPlusTtl = $this->supportedTtl($sourceTtl);
        $params = [
            'domain-name' => $domain,
            'record-type' => $type,
            'host' => $host,
            'record' => (string) $record['value'],
            'ttl' => $dnsPlusTtl,
        ];

        if ($type === 'MX' || $type === 'SRV') {
            $params['priority'] = (int) ($record['priority'] ?? 0);
        }
        if ($type === 'SRV') {
            $params['weight'] = (int) ($record['weight'] ?? 0);
            $params['port'] = (int) ($record['port'] ?? 0);
        }

        try {
            $response = $this->api('dns/add-record.json', $params);
            $this->assertSuccess($response, 'adding ' . RecordTools::display($record));
            $message = $sourceTtl === $dnsPlusTtl
                ? 'Record added.'
                : 'Record added; TTL adjusted from ' . $sourceTtl . ' to ' . $dnsPlusTtl . ' seconds for DNSPlus.';
            return ['ok' => true, 'message' => $message];
        } catch (MigrationException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function supportedTtl(int $ttl): int
    {
        $supported = [60, 300, 900, 1800, 3600, 21600, 43200, 86400, 172800, 259200, 604800, 1209600, 2592000];
        $ttl = max(60, $ttl);
        $closest = $supported[0];
        $distance = abs($ttl - $closest);

        foreach ($supported as $candidate) {
            $candidateDistance = abs($ttl - $candidate);
            if ($candidateDistance < $distance) {
                $closest = $candidate;
                $distance = $candidateDistance;
            }
        }

        return $closest;
    }

    private function zoneInfo(string $domain, bool $throw): array
    {
        $response = $this->api('dns/get-zone-info.json', ['domain-name' => $domain]);
        $status = strtolower(trim((string) ($response['status'] ?? '')));

        if (in_array($status, ['error', 'failed', '0'], true) || isset($response['statusDescription'])) {
            $message = strtolower(trim((string) (
                $response['statusDescription']
                ?? $response['description']
                ?? ''
            )));

            $zoneMissing = $message !== '' && (
                str_contains($message, 'missing domain-name')
                || str_contains($message, 'does not exist')
                || str_contains($message, 'not exist')
                || str_contains($message, 'not found')
                || str_contains($message, 'not added')
                || str_contains($message, 'no such zone')
            );

            if (!$throw && $zoneMissing) {
                return [];
            }

            $this->assertSuccess($response, 'checking the DNSPlus zone');
        }

        return $response;
    }

    private function api(string $endpoint, array $params): array
    {
        $params = array_merge([
            'auth-id' => $this->authId,
            'auth-password' => $this->authPassword,
        ], $params);

        return $this->request(
            'POST',
            'https://api.cloudns.net/' . ltrim($endpoint, '/'),
            $params,
            [],
            true,
            true
        );
    }

    private function assertConfigured(): void
    {
        if ($this->authId === '' || $this->authPassword === '') {
            throw new MigrationException('DNSPlus API credentials are incomplete in the installed DNSPlus module.');
        }
    }

    private function assertSuccess(array $response, string $action): void
    {
        $status = strtolower(trim((string) ($response['status'] ?? '')));
        if (in_array($status, ['success', '1'], true)) {
            return;
        }

        if ($status === '' && !isset($response['statusDescription']) && !isset($response['description'])) {
            return;
        }

        $message = (string) ($response['description'] ?? $response['statusDescription'] ?? 'The DNSPlus API rejected the request.');
        $message = trim(preg_replace('/\s+/', ' ', $message) ?? $message);
        throw new MigrationException('DNSPlus failed while ' . $action . ': ' . $message);
    }

    private function dedupe(array $records): array
    {
        $out = [];
        foreach ($records as $record) {
            $signature = RecordTools::signature($record);
            if (!isset($out[$signature])) {
                $out[$signature] = $record;
            }
        }
        return array_values($out);
    }
}

final class CpanelAdapter extends CurlAdapter implements Adapter
{
    private ServiceResolver $resolver;
    private array $connections = [];
    private array $serviceConnections = [];

    public function __construct(ServiceResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function label(): string
    {
        return 'cPanel';
    }

    public function preflight(string $domain): void
    {
        $this->connection($domain);
    }

    public function read(string $domain): array
    {
        $connection = $this->connection($domain);

        try {
            $parsed = $this->api($connection, 'parse_dns_zone', ['zone' => $domain]);
            $records = $this->parseCpanelResponse($parsed, $domain);
            if ($records) {
                return $this->dedupe($records);
            }
        } catch (MigrationException $e) {
            // Fall back to dumpzone for older cPanel/WHM versions.
        }

        $dump = $this->api($connection, 'dumpzone', ['domain' => $domain]);
        return $this->dedupe($this->parseCpanelResponse($dump, $domain));
    }

    public function prepareDestination(string $domain, bool $replace): array
    {
        $connection = $this->connection($domain);
        $dump = null;

        try {
            $dump = $this->api($connection, 'dumpzone', ['domain' => $domain]);
        } catch (MigrationException $e) {
            $ip = $this->accountIp($connection);
            $params = ['domain' => $domain, 'ip' => $ip];
            if ($connection['cpanel_user'] !== '') {
                $params['trueowner'] = $connection['cpanel_user'];
            }
            $this->api($connection, 'adddns', $params);
            $dump = $this->api($connection, 'dumpzone', ['domain' => $domain]);
        }

        $existing = $this->parseCpanelResponse($dump, $domain);
        if (!$replace) {
            return $this->dedupe($existing);
        }

        $lines = $this->cpanelDeletableLines($dump, $domain);
        rsort($lines, SORT_NUMERIC);
        foreach ($lines as $line) {
            $this->api($connection, 'removezonerecord', [
                'zone' => $domain,
                'line' => $line,
            ]);
        }

        $left = [];
        foreach ($existing as $record) {
            if (RecordTools::isRootNameserver($record)) {
                $left[] = $record;
            }
        }
        return $this->dedupe($left);
    }

    public function addRecord(string $domain, array $record): array
    {
        $connection = $this->connection($domain);
        $type = strtoupper((string) $record['type']);
        if (!in_array($type, RecordTools::COMMON_TYPES, true)) {
            return ['ok' => false, 'message' => 'cPanel does not support this normalized record type.'];
        }

        $params = [
            'zone' => $domain,
            'name' => RecordTools::fqdn((string) $record['name'], $domain, true),
            'type' => $type,
            'ttl' => (int) ($record['ttl'] ?? 3600),
        ];

        $value = (string) $record['value'];
        switch ($type) {
            case 'A':
            case 'AAAA':
                $params['address'] = $value;
                break;
            case 'CNAME':
                $params['cname'] = $this->cpanelTarget($value);
                break;
            case 'MX':
                $params['exchange'] = $this->cpanelTarget($value);
                $params['preference'] = (int) ($record['priority'] ?? 0);
                break;
            case 'TXT':
                $params['txtdata'] = $value;
                break;
            case 'NS':
                $params['nsdname'] = $this->cpanelTarget($value);
                break;
            case 'SRV':
                $params['priority'] = (int) ($record['priority'] ?? 0);
                $params['weight'] = (int) ($record['weight'] ?? 0);
                $params['port'] = (int) ($record['port'] ?? 0);
                $params['target'] = $this->cpanelTarget($value);
                break;
        }

        try {
            $this->api($connection, 'addzonerecord', $params);
            return ['ok' => true, 'message' => 'Record added.'];
        } catch (MigrationException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function connection(string $domain): array
    {
        $domain = RecordTools::normalizeDomain($domain);
        if (isset($this->connections[$domain])) {
            return $this->connections[$domain];
        }

        $serviceId = $this->resolver->cpanelServiceId($domain);
        if ($serviceId > 0) {
            $this->connections[$domain] = $this->buildConnection($serviceId);
            return $this->connections[$domain];
        }

        // The domain may be an addon or parked domain under one of several
        // active cPanel services. Ask WHM for the domain owner and only select
        // a service when the returned cPanel username matches exactly.
        $candidateIds = $this->resolver->cpanelCandidateServiceIds($domain);
        $matches = [];

        foreach ($candidateIds as $candidateId) {
            try {
                $candidate = $this->buildConnection((int) $candidateId);
                if ($this->connectionOwnsDomain($candidate, $domain)) {
                    $matches[(int) $candidate['service_id']] = $candidate;
                }
            } catch (MigrationException $e) {
                // A broken unrelated service must not prevent another exact
                // cPanel account from being safely identified.
            }
        }

        if (count($matches) === 1) {
            $this->connections[$domain] = array_values($matches)[0];
            return $this->connections[$domain];
        }

        if (count($matches) > 1) {
            throw new MigrationException('Multiple active cPanel services matched this domain; the destination account is ambiguous.');
        }

        if ($candidateIds) {
            throw new MigrationException('Active cPanel services were found for this client, but none could be confirmed as the account that owns ' . $domain . '.');
        }

        throw new MigrationException('No matching active cPanel hosting account was found for ' . $domain . '.');
    }

    private function buildConnection(int $serviceId): array
    {
        if (isset($this->serviceConnections[$serviceId])) {
            return $this->serviceConnections[$serviceId];
        }

        $params = $this->resolver->moduleParams($serviceId);
        $host = $this->extractHost((string) ($params['serverhostname'] ?? ''));
        if ($host === '') {
            $host = $this->extractHost((string) ($params['serverip'] ?? ''));
        }

        $whmUser = trim((string) ($params['serverusername'] ?? ''));
        $password = (string) ($params['serverpassword'] ?? '');
        $accessHash = preg_replace('/\s+/', '', (string) ($params['serveraccesshash'] ?? '')) ?? '';
        $cpanelUser = trim((string) ($params['username'] ?? ''));

        if ($host === '' || $whmUser === '' || ($password === '' && $accessHash === '')) {
            throw new MigrationException('The cPanel server connection is incomplete for service ' . $serviceId . '.');
        }

        $headers = ['Accept: application/json'];
        if ($accessHash !== '') {
            $headers[] = 'Authorization: WHM ' . $whmUser . ':' . $accessHash;
        } else {
            $headers[] = 'Authorization: Basic ' . base64_encode($whmUser . ':' . $password);
        }

        $this->serviceConnections[$serviceId] = [
            'service_id' => $serviceId,
            'host' => $host,
            'headers' => $headers,
            'cpanel_user' => $cpanelUser,
            'server_ip' => trim((string) ($params['serverip'] ?? '')),
        ];

        return $this->serviceConnections[$serviceId];
    }

    private function connectionOwnsDomain(array $connection, string $domain): bool
    {
        $expectedUser = strtolower(trim((string) ($connection['cpanel_user'] ?? '')));
        if ($expectedUser === '') {
            return false;
        }

        foreach (['getdomainowner', 'domainuserdata'] as $function) {
            try {
                $response = $this->api($connection, $function, ['domain' => $domain]);
                $owner = strtolower($this->findFirstScalarByKeys(
                    $response,
                    ['user', 'username', 'domainowner']
                ));
                if ($owner !== '') {
                    return hash_equals($expectedUser, $owner);
                }
            } catch (MigrationException $e) {
                // Try the next supported WHM lookup.
            }
        }

        // Older WHM versions may only expose the account summary lookup for a
        // primary domain. This fallback does not guess for addon domains.
        try {
            $response = $this->api($connection, 'accountsummary', ['domain' => $domain]);
            $owner = strtolower($this->findFirstScalarByKeys(
                $response,
                ['user', 'username']
            ));
            $primaryDomain = RecordTools::normalizeDomain(
                $this->findFirstScalarByKeys($response, ['domain'])
            );

            return $owner !== ''
                && $primaryDomain === $domain
                && hash_equals($expectedUser, $owner);
        } catch (MigrationException $e) {
            return false;
        }
    }

    private function findFirstScalarByKeys(array $node, array $wanted): string
    {
        foreach ($wanted as $key) {
            $value = $this->findScalarByKey($node, (string) $key);
            if ($value !== '') {
                return $value;
            }
        }
        return '';
    }

    private function api(array $connection, string $function, array $params): array
    {
        $params = array_merge(['api.version' => 1], $params);
        $url = 'https://' . $connection['host'] . ':2087/json-api/' . rawurlencode($function);
        $response = $this->request('GET', $url, $params, $connection['headers']);

        $metadata = $response['metadata'] ?? [];
        if (is_array($metadata) && isset($metadata['result']) && (int) $metadata['result'] !== 1) {
            $reason = trim((string) ($metadata['reason'] ?? 'The cPanel API rejected the request.'));
            throw new MigrationException('cPanel ' . $function . ' failed: ' . $reason);
        }

        if (isset($response['result'][0]['status']) && (int) $response['result'][0]['status'] !== 1) {
            $reason = trim((string) ($response['result'][0]['statusmsg'] ?? 'The cPanel API rejected the request.'));
            throw new MigrationException('cPanel ' . $function . ' failed: ' . $reason);
        }

        return $response;
    }

    private function parseCpanelResponse(array $response, string $domain): array
    {
        $candidates = [];
        $this->collectRecordArrays($response, $candidates);
        $records = [];

        foreach ($candidates as $row) {
            $type = strtoupper((string) ($row['record_type'] ?? $row['type'] ?? ''));
            if (!in_array($type, RecordTools::COMMON_TYPES, true)) {
                continue;
            }

            $name = (string) ($row['dname'] ?? $row['name'] ?? '@');
            $ttl = (int) ($row['ttl'] ?? 3600);
            $data = $row['data'] ?? [];
            if (!is_array($data)) {
                $data = [$data];
            }
            $data = array_values(array_map(static fn($v): string => (string) $v, $data));

            $raw = [
                'type' => $type,
                'name' => $name,
                'ttl' => $ttl,
                'value' => '',
                'priority' => 0,
                'weight' => 0,
                'port' => 0,
            ];

            switch ($type) {
                case 'A':
                case 'AAAA':
                    $raw['value'] = (string) ($row['address'] ?? $data[0] ?? '');
                    break;
                case 'CNAME':
                    $raw['value'] = (string) ($row['cname'] ?? $data[0] ?? '');
                    break;
                case 'NS':
                    $raw['value'] = (string) ($row['nsdname'] ?? $data[0] ?? '');
                    break;
                case 'MX':
                    $raw['priority'] = (int) ($row['preference'] ?? $row['priority'] ?? $data[0] ?? 0);
                    $raw['value'] = (string) ($row['exchange'] ?? $data[1] ?? $data[0] ?? '');
                    break;
                case 'TXT':
                    $txt = $row['txtdata'] ?? null;
                    if ($txt === null) {
                        $txt = implode('', $data);
                    }
                    $raw['value'] = (string) $txt;
                    break;
                case 'SRV':
                    $raw['priority'] = (int) ($row['priority'] ?? $data[0] ?? 0);
                    $raw['weight'] = (int) ($row['weight'] ?? $data[1] ?? 0);
                    $raw['port'] = (int) ($row['port'] ?? $data[2] ?? 0);
                    $raw['value'] = (string) ($row['target'] ?? $data[3] ?? '');
                    break;
            }

            $normalized = RecordTools::normalizeRecord($raw, $domain);
            if ($normalized !== null) {
                if (isset($row['line'])) {
                    $normalized['_line'] = (int) $row['line'];
                } elseif (isset($row['Line'])) {
                    $normalized['_line'] = (int) $row['Line'];
                }
                $records[] = $normalized;
            }
        }

        return $records;
    }

    private function collectRecordArrays(array $node, array &$out): void
    {
        $type = strtoupper((string) ($node['record_type'] ?? $node['type'] ?? ''));
        $hasName = isset($node['dname']) || isset($node['name']);
        if ($hasName && in_array($type, array_merge(RecordTools::COMMON_TYPES, ['SOA']), true)) {
            $out[] = $node;
        }

        foreach ($node as $value) {
            if (is_array($value)) {
                $this->collectRecordArrays($value, $out);
            }
        }
    }

    private function cpanelDeletableLines(array $dump, string $domain): array
    {
        $candidates = [];
        $this->collectRecordArrays($dump, $candidates);
        $lines = [];

        foreach ($candidates as $row) {
            $type = strtoupper((string) ($row['record_type'] ?? $row['type'] ?? ''));
            if (!in_array($type, RecordTools::COMMON_TYPES, true)) {
                continue;
            }

            $normalized = RecordTools::normalizeRecord([
                'type' => $type,
                'name' => (string) ($row['dname'] ?? $row['name'] ?? '@'),
                'value' => (string) (
                    $row['address']
                    ?? $row['cname']
                    ?? $row['exchange']
                    ?? $row['txtdata']
                    ?? $row['nsdname']
                    ?? $row['target']
                    ?? ($row['data'][0] ?? '')
                ),
                'ttl' => (int) ($row['ttl'] ?? 3600),
                'priority' => (int) ($row['preference'] ?? $row['priority'] ?? 0),
                'weight' => (int) ($row['weight'] ?? 0),
                'port' => (int) ($row['port'] ?? 0),
            ], $domain);

            if ($normalized === null || RecordTools::isRootNameserver($normalized)) {
                continue;
            }

            $line = (int) ($row['line'] ?? $row['Line'] ?? 0);
            if ($line > 0) {
                $lines[$line] = $line;
            }
        }

        return array_values($lines);
    }

    private function accountIp(array $connection): string
    {
        if ($connection['cpanel_user'] !== '') {
            try {
                $response = $this->api($connection, 'accountsummary', ['user' => $connection['cpanel_user']]);
                $ip = $this->findScalarByKey($response, 'ip');
                if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            } catch (MigrationException $e) {
                // Fall back to the configured server IP.
            }
        }

        $serverIp = trim((string) $connection['server_ip']);
        if (filter_var($serverIp, FILTER_VALIDATE_IP)) {
            return $serverIp;
        }

        $resolved = gethostbyname((string) $connection['host']);
        if (filter_var($resolved, FILTER_VALIDATE_IP)) {
            return $resolved;
        }

        throw new MigrationException('cPanel could not determine an IP address for the new DNS zone.');
    }

    private function findScalarByKey(array $node, string $wanted): string
    {
        foreach ($node as $key => $value) {
            if (strtolower((string) $key) === strtolower($wanted) && is_scalar($value)) {
                return trim((string) $value);
            }
            if (is_array($value)) {
                $found = $this->findScalarByKey($value, $wanted);
                if ($found !== '') {
                    return $found;
                }
            }
        }
        return '';
    }

    private function extractHost(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (!str_contains($value, '://')) {
            $value = 'https://' . $value;
        }
        $host = parse_url($value, PHP_URL_HOST);
        return is_string($host) ? strtolower(trim($host)) : '';
    }

    private function cpanelTarget(string $value): string
    {
        $value = rtrim(trim($value), '.');
        return $value === '' ? '' : $value . '.';
    }

    private function dedupe(array $records): array
    {
        $out = [];
        foreach ($records as $record) {
            $signature = RecordTools::signature($record);
            if (!isset($out[$signature])) {
                $out[$signature] = $record;
            }
        }
        return array_values($out);
    }
}

final class Engine
{
    private array $vars;
    private string $manageRoot;
    private ServiceResolver $resolver;
    private array $adapters = [];

    public function __construct(array $vars, string $manageRoot)
    {
        $this->vars = $vars;
        $this->manageRoot = rtrim($manageRoot, '/\\');
        $this->resolver = new ServiceResolver($this->manageRoot);
    }

    public static function systems(): array
    {
        return [
            'register' => 'RegistrarDNS',
            'dnsplus' => 'DNSPlus',
            'cpanel' => 'cPanel',
        ];
    }

    public function adapter(string $system): Adapter
    {
        if (isset($this->adapters[$system])) {
            return $this->adapters[$system];
        }

        $adapter = match ($system) {
            'register' => new RegisterDnsAdapter($this->vars),
            'dnsplus' => new DnsPlusAdapter($this->resolver, $this->manageRoot),
            'cpanel' => new CpanelAdapter($this->resolver),
            default => throw new MigrationException('The selected DNS system is not supported.'),
        };

        $this->adapters[$system] = $adapter;
        return $adapter;
    }

    public function preview(array $domains, string $from, string $to): array
    {
        if ($from === $to) {
            throw new MigrationException('Select two different DNS systems.');
        }

        $source = $this->adapter($from);
        $destination = $this->adapter($to);
        $results = [];

        foreach ($domains as $domain) {
            try {
                $source->preflight($domain);
                $destination->preflight($domain);
                $records = $source->read($domain);
                [$migratable, $skipped, $skipReasons] = $this->filterRecords($records);

                $types = [];
                foreach ($migratable as $record) {
                    $types[$record['type']] = $record['type'];
                }

                $message = $migratable ? 'Ready to migrate.' : 'No migratable DNS records were found.';
                if (($skipReasons['root_nameservers'] ?? 0) > 0) {
                    $count = (int) $skipReasons['root_nameservers'];
                    $message .= ' ' . $count . ' source root nameserver record'
                        . ($count === 1 ? ' will' : 's will')
                        . ' be preserved and not copied.';
                }
                if (($skipReasons['unsupported'] ?? 0) > 0) {
                    $count = (int) $skipReasons['unsupported'];
                    $message .= ' ' . $count . ' unsupported source record'
                        . ($count === 1 ? ' was' : 's were')
                        . ' skipped.';
                }

                $results[$domain] = [
                    'ok' => true,
                    'records' => count($migratable),
                    'skipped' => $skipped,
                    'types' => array_values($types),
                    'message' => $message,
                ];
            } catch (Throwable $e) {
                $results[$domain] = [
                    'ok' => false,
                    'records' => 0,
                    'skipped' => 0,
                    'types' => [],
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function migrate(array $domains, string $from, string $to, bool $replace): array
    {
        if ($from === $to) {
            throw new MigrationException('Select two different DNS systems.');
        }

        $source = $this->adapter($from);
        $destination = $this->adapter($to);
        $results = [];

        foreach ($domains as $domain) {
            $row = [
                'status' => 'failed',
                'added' => 0,
                'skipped' => 0,
                'failed' => 0,
                'conflicts' => 0,
                'details' => [],
            ];

            try {
                $source->preflight($domain);
                $destination->preflight($domain);
                $sourceRecords = $source->read($domain);
                [$records, $filtered, $skipReasons] = $this->filterRecords($sourceRecords);
                $row['skipped'] += $filtered;

                if (($skipReasons['root_nameservers'] ?? 0) > 0) {
                    $count = (int) $skipReasons['root_nameservers'];
                    $row['details'][] = 'Skipped ' . $count . ' source root nameserver record'
                        . ($count === 1 ? '' : 's')
                        . '; destination authoritative nameserver records are preserved.';
                }
                if (($skipReasons['unsupported'] ?? 0) > 0) {
                    $count = (int) $skipReasons['unsupported'];
                    $row['details'][] = 'Skipped ' . $count . ' unsupported source DNS record'
                        . ($count === 1 ? '.' : 's.');
                }

                if (!$records) {
                    $row['details'][] = 'No migratable DNS records were found in the source zone.';
                    $results[$domain] = $row;
                    continue;
                }

                $existing = $destination->prepareDestination($domain, $replace);
                $existingSignatures = [];
                $existingByName = [];
                foreach ($existing as $record) {
                    $existingSignatures[RecordTools::signature($record)] = true;
                    $nameKey = strtolower((string) ($record['name'] ?? '@'));
                    $existingByName[$nameKey][] = $record;
                }

                foreach ($records as $record) {
                    $signature = RecordTools::signature($record);

                    if (!$replace && $to === 'dnsplus') {
                        $spfConflict = $this->dnsPlusSpfConflict($record, $existingByName);
                        if ($spfConflict !== null) {
                            $row['skipped']++;
                            $row['conflicts']++;
                            $row['details'][] = 'Skipped conflicting record: '
                                . RecordTools::display($record)
                                . '; destination already has '
                                . RecordTools::display($spfConflict['record'])
                                . '. ' . $spfConflict['reason'];
                            continue;
                        }
                    }

                    if (isset($existingSignatures[$signature])) {
                        $row['skipped']++;
                        $row['details'][] = 'Skipped existing record: ' . RecordTools::display($record);
                        continue;
                    }

                    if (!$replace && $to === 'cpanel') {
                        $conflict = $this->cnameConflict($record, $existingByName);
                        if ($conflict !== null) {
                            $row['skipped']++;
                            $row['conflicts']++;
                            $row['details'][] = 'Skipped conflicting record: '
                                . RecordTools::display($record)
                                . '; destination already has '
                                . RecordTools::display($conflict)
                                . '. cPanel preserves the existing record because CNAME records cannot coexist with other records at the same name.';
                            continue;
                        }
                    }

                    if (!$replace && $to === 'register') {
                        $conflict = $this->registerDnsConflict($record, $existingByName);
                        if ($conflict !== null) {
                            $row['skipped']++;
                            $row['conflicts']++;
                            $row['details'][] = 'Skipped conflicting record: '
                                . RecordTools::display($record)
                                . '; destination already has '
                                . RecordTools::display($conflict['record'])
                                . '. ' . $conflict['reason'];
                            continue;
                        }
                    }

                    if (!$replace && $to === 'dnsplus') {
                        $conflict = $this->cnameConflict($record, $existingByName);
                        if ($conflict !== null) {
                            $row['skipped']++;
                            $row['conflicts']++;
                            $row['details'][] = 'Skipped conflicting record: '
                                . RecordTools::display($record)
                                . '; destination already has '
                                . RecordTools::display($conflict)
                                . '. DNSPlus preserves the existing record because CNAME records cannot coexist with other records at the same name.';
                            continue;
                        }
                    }

                    $result = $destination->addRecord($domain, $record);
                    if ($result['ok']) {
                        $row['added']++;
                        $existingSignatures[$signature] = true;
                        $nameKey = strtolower((string) ($record['name'] ?? '@'));
                        $existingByName[$nameKey][] = $record;
                        $row['details'][] = 'Added record: ' . RecordTools::display($record);
                        if (($result['message'] ?? 'Record added.') !== 'Record added.') {
                            $row['details'][] = RecordTools::display($record) . ' — ' . $result['message'];
                        }
                    } else {
                        $row['failed']++;
                        $row['details'][] = RecordTools::display($record) . ' — ' . $result['message'];
                    }
                }

                if ($row['failed'] > 0 && $row['added'] > 0) {
                    $row['status'] = 'partial';
                } elseif ($row['failed'] > 0) {
                    $row['status'] = 'failed';
                } elseif ($row['conflicts'] > 0) {
                    $row['status'] = 'partial';
                } else {
                    $row['status'] = 'success';
                }
            } catch (Throwable $e) {
                $row['failed']++;
                $row['details'][] = $e->getMessage();
            }

            $results[$domain] = $row;
        }

        return $results;
    }

    private function cnameConflict(array $sourceRecord, array $existingByName): ?array
    {
        $nameKey = strtolower((string) ($sourceRecord['name'] ?? '@'));
        $sourceType = strtoupper((string) ($sourceRecord['type'] ?? ''));

        foreach ($existingByName[$nameKey] ?? [] as $existingRecord) {
            $existingType = strtoupper((string) ($existingRecord['type'] ?? ''));

            // An exact match was already handled by the signature check. A
            // different CNAME at the same owner name also conflicts, and a
            // CNAME cannot coexist with any other DNS record type there.
            if ($sourceType === 'CNAME' || $existingType === 'CNAME') {
                return $existingRecord;
            }
        }

        return null;
    }

    private function dnsPlusSpfConflict(array $sourceRecord, array $existingByName): ?array
    {
        $sourceType = strtoupper((string) ($sourceRecord['type'] ?? ''));
        $sourceValue = strtolower(trim((string) ($sourceRecord['value'] ?? '')));
        if ($sourceType !== 'TXT' || !str_starts_with($sourceValue, 'v=spf1')) {
            return null;
        }

        $nameKey = strtolower((string) ($sourceRecord['name'] ?? '@'));
        $spfRecords = [];
        foreach ($existingByName[$nameKey] ?? [] as $existingRecord) {
            $existingType = strtoupper((string) ($existingRecord['type'] ?? ''));
            $existingValue = strtolower(trim((string) ($existingRecord['value'] ?? '')));
            if ($existingType === 'TXT' && str_starts_with($existingValue, 'v=spf1')) {
                $spfRecords[] = $existingRecord;
            }
        }

        if (!$spfRecords) {
            return null;
        }

        $sourceSignature = RecordTools::signature($sourceRecord);
        $exactExists = false;
        $conflictingRecord = $spfRecords[0];
        foreach ($spfRecords as $existingRecord) {
            if (RecordTools::signature($existingRecord) === $sourceSignature) {
                $exactExists = true;
                continue;
            }
            $conflictingRecord = $existingRecord;
            break;
        }

        if ($exactExists && count($spfRecords) === 1) {
            return null;
        }

        if ($exactExists) {
            return [
                'record' => $conflictingRecord,
                'reason' => 'DNSPlus already contains multiple SPF policies at this hostname, including the source record. Retain-existing mode leaves them unchanged; remove the unintended extra SPF record before treating the zones as matched.',
            ];
        }

        return [
            'record' => $conflictingRecord,
            'reason' => 'DNSPlus preserves the existing SPF policy because publishing multiple SPF policies at one hostname causes an SPF configuration conflict.',
        ];
    }

    private function registerDnsConflict(array $sourceRecord, array $existingByName): ?array
    {
        $cnameConflict = $this->cnameConflict($sourceRecord, $existingByName);
        if ($cnameConflict !== null) {
            return [
                'record' => $cnameConflict,
                'reason' => 'RegistrarDNS preserves the existing record because CNAME records cannot coexist with other records at the same name.',
            ];
        }

        $sourceType = strtoupper((string) ($sourceRecord['type'] ?? ''));
        $sourceValue = strtolower(trim((string) ($sourceRecord['value'] ?? '')));
        if ($sourceType !== 'TXT' || !str_starts_with($sourceValue, 'v=spf1')) {
            return null;
        }

        $nameKey = strtolower((string) ($sourceRecord['name'] ?? '@'));
        foreach ($existingByName[$nameKey] ?? [] as $existingRecord) {
            $existingType = strtoupper((string) ($existingRecord['type'] ?? ''));
            $existingValue = strtolower(trim((string) ($existingRecord['value'] ?? '')));
            if ($existingType === 'TXT' && str_starts_with($existingValue, 'v=spf1')) {
                return [
                    'record' => $existingRecord,
                    'reason' => 'RegistrarDNS permits only one SPF record per hostname, so the existing destination SPF record is preserved.',
                ];
            }
        }

        return null;
    }

    private function filterRecords(array $records): array
    {
        $out = [];
        $skipped = 0;
        $reasons = [
            'unsupported' => 0,
            'root_nameservers' => 0,
        ];

        foreach ($records as $record) {
            if (!in_array(strtoupper((string) ($record['type'] ?? '')), RecordTools::COMMON_TYPES, true)) {
                $skipped++;
                $reasons['unsupported']++;
                continue;
            }
            if (RecordTools::isRootNameserver($record)) {
                $skipped++;
                $reasons['root_nameservers']++;
                continue;
            }
            $signature = RecordTools::signature($record);
            $out[$signature] = $record;
        }

        return [array_values($out), $skipped, $reasons];
    }
}
