<?php
/**
 * DomainMonger client-area EPP/Auth Code manager.
 *
 * Patch 1366: Correctly resolves the LogicBoxes registrar order ID by domain
 * name before modifying an EPP/Auth code. WHMCS tbldomains.orderid is an
 * internal WHMCS order reference and must never be sent as a registrar order ID.
 *
 * Scope: clientarea.php?action=domaingetepp
 * API: /api/domains/orderid.json and /api/domains/modify-auth-code.json
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm_epp_is_page')) {
    function dm_epp_is_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        if ($script !== 'clientarea.php' && strpos($uri, '/manage/clientarea.php') === false) {
            return false;
        }

        return $action === 'domaingetepp' || strpos($uri, 'action=domaingetepp') !== false;
    }
}

if (!function_exists('dm_epp_flash')) {
    function dm_epp_flash(?string $type = null, ?string $message = null): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_start();
        }

        if ($type !== null && $message !== null) {
            $_SESSION['dm_epp_flash'] = [
                'type' => $type,
                'message' => $message,
            ];
            return $_SESSION['dm_epp_flash'];
        }

        $flash = $_SESSION['dm_epp_flash'] ?? [];
        unset($_SESSION['dm_epp_flash']);

        return is_array($flash) ? $flash : [];
    }
}

if (!function_exists('dm_epp_get_token')) {
    function dm_epp_get_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_start();
        }

        if (empty($_SESSION['dm_epp_token'])) {
            try {
                $_SESSION['dm_epp_token'] = bin2hex(random_bytes(24));
            } catch (Throwable $e) {
                $_SESSION['dm_epp_token'] = sha1(uniqid('dm_epp_', true));
            }
        }

        return (string) $_SESSION['dm_epp_token'];
    }
}

if (!function_exists('dm_epp_valid_token')) {
    function dm_epp_valid_token(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_start();
        }

        return $token !== '' && isset($_SESSION['dm_epp_token']) && hash_equals((string) $_SESSION['dm_epp_token'], $token);
    }
}

if (!function_exists('dm_epp_current_client_id')) {
    function dm_epp_current_client_id(): int
    {
        return (int) ($_SESSION['uid'] ?? $_SESSION['clientid'] ?? 0);
    }
}

if (!function_exists('dm_epp_get_domain_row')) {
    function dm_epp_get_domain_row(int $domainId)
    {
        $clientId = dm_epp_current_client_id();
        if ($domainId <= 0 || $clientId <= 0) {
            return null;
        }

        try {
            return Capsule::table('tbldomains')
                ->where('id', $domainId)
                ->where('userid', $clientId)
                ->first();
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('dm_epp_decrypt_candidates')) {
    function dm_epp_decrypt_candidates($value): array
    {
        $value = (string) $value;
        $candidates = [];

        if ($value !== '') {
            $candidates[] = $value;
        }

        /*
         * WHMCS registrar module fields can be stored using password2-style
         * encryption. Use WHMCS's own local API decrypt path when available,
         * instead of guessing encryption internals.
         */
        if ($value !== '' && function_exists('localAPI')) {
            try {
                $result = localAPI('DecryptPassword', ['password2' => $value]);
                if (is_array($result) && ($result['result'] ?? '') === 'success') {
                    $decrypted = (string) ($result['password'] ?? '');
                    if ($decrypted !== '' && $decrypted !== $value) {
                        $candidates[] = $decrypted;
                    }
                }
            } catch (Throwable $e) {
                // Ignore and try other supported decrypt methods.
            }
        }

        if ($value !== '' && function_exists('decrypt')) {
            try {
                $decrypted = decrypt($value);
                if (is_string($decrypted) && $decrypted !== '' && $decrypted !== $value) {
                    $candidates[] = $decrypted;
                }
            } catch (Throwable $e) {
                // Ignore decrypt failures; some registrar settings are stored plain-text.
            }
        }

        foreach ([
            'WHMCS\\Security\\Encryption',
            'WHMCS\\Security\\Encryption\\Aes',
        ] as $className) {
            if ($value === '' || !class_exists($className)) {
                continue;
            }

            try {
                $encryption = new $className();
                foreach (['decrypt', 'decryptValue'] as $method) {
                    if (method_exists($encryption, $method)) {
                        $decrypted = $encryption->{$method}($value);
                        if (is_string($decrypted) && $decrypted !== '' && $decrypted !== $value) {
                            $candidates[] = $decrypted;
                        }
                    }
                }
            } catch (Throwable $e) {
                // Ignore.
            }
        }

        return array_values(array_unique(array_filter($candidates, static function ($item) {
            return trim((string) $item) !== '';
        })));
    }
}

if (!function_exists('dm_epp_get_registrar_config')) {
    function dm_epp_get_registrar_config(string $registrar): array
    {
        $config = [];

        if ($registrar === '') {
            return $config;
        }

        try {
            $rows = Capsule::table('tblregistrars')
                ->where('registrar', $registrar)
                ->get();

            foreach ($rows as $row) {
                $setting = (string) ($row->setting ?? '');
                if ($setting === '') {
                    continue;
                }
                $config[$setting] = dm_epp_decrypt_candidates($row->value ?? '');
            }
        } catch (Throwable $e) {
            $config = [];
        }

        return dm_epp_merge_config_arrays($config, dm_epp_collect_resellerclubmods_addon_config($registrar));
    }
}


if (!function_exists('dm_epp_add_config_candidate')) {
    function dm_epp_add_config_candidate(array &$config, string $key, $value): void
    {
        $key = trim($key);
        if ($key === '') {
            return;
        }

        $values = [];
        foreach (dm_epp_decrypt_candidates($value) as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '' && !in_array($candidate, $values, true)) {
                $values[] = $candidate;
            }
        }

        if (!$values) {
            return;
        }

        if (!isset($config[$key]) || !is_array($config[$key])) {
            $config[$key] = [];
        }

        foreach ($values as $candidate) {
            if (!in_array($candidate, $config[$key], true)) {
                $config[$key][] = $candidate;
            }
        }
    }
}

if (!function_exists('dm_epp_add_structured_config_candidates')) {
    function dm_epp_add_structured_config_candidates(array &$config, string $sourcePrefix, $value): void
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return;
        }

        $decodedValues = [$raw];
        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $decodedValues[] = $json;
        }

        $unserialized = @unserialize($raw);
        if (is_array($unserialized)) {
            $decodedValues[] = $unserialized;
        }

        $walk = function ($item, string $prefix = '') use (&$walk, &$config, $sourcePrefix) {
            if (is_array($item) || is_object($item)) {
                foreach ((array) $item as $key => $nested) {
                    $walk($nested, trim($prefix . ' ' . (string) $key));
                }
                return;
            }

            $label = strtolower($prefix);
            $scalar = trim((string) $item);
            if ($scalar === '') {
                return;
            }

            if (preg_match('/(auth.*user|user.*id|reseller.*id|resellerid|rid|authuserid)/i', $label) && preg_match('/^[0-9]{2,}$/', $scalar)) {
                dm_epp_add_config_candidate($config, $sourcePrefix . '.auth-userid', $scalar);
                dm_epp_add_config_candidate($config, 'auth-userid', $scalar);
            }

            if (preg_match('/(api.*key|apikey|api_key|access.*key|token|secret|password)/i', $label)
                && !preg_match('/license/i', $label)
                && strlen($scalar) >= 8) {
                dm_epp_add_config_candidate($config, $sourcePrefix . '.api-key', $scalar);
                dm_epp_add_config_candidate($config, 'api-key', $scalar);
            }
        };

        foreach ($decodedValues as $decodedValue) {
            $walk($decodedValue, $sourcePrefix);
        }
    }
}

if (!function_exists('dm_epp_collect_resellerclubmods_addon_config')) {
    function dm_epp_collect_resellerclubmods_addon_config(string $registrar): array
    {
        $config = [];
        $registrarNeedle = strtolower($registrar);
        $isNeo = preg_match('/netearth|neo/i', $registrar) === 1;

        try {
            if (Capsule::schema()->hasTable('tbladdonmodules')) {
                $rows = Capsule::table('tbladdonmodules')
                    ->where(function ($query) {
                        $query->where('module', 'like', '%resellerclubmods%')
                            ->orWhere('module', 'like', '%logicbox%')
                            ->orWhere('module', 'like', '%netearth%')
                            ->orWhere('module', 'like', '%lcdrm%')
                            ->orWhere('setting', 'like', '%reseller%')
                            ->orWhere('setting', 'like', '%logicbox%')
                            ->orWhere('setting', 'like', '%netearth%')
                            ->orWhere('setting', 'like', '%neo%')
                            ->orWhere('setting', 'like', '%api%')
                            ->orWhere('setting', 'like', '%rid%');
                    })
                    ->get();

                foreach ($rows as $row) {
                    $module = (string) ($row->module ?? '');
                    $setting = (string) ($row->setting ?? '');
                    $value = $row->value ?? '';
                    $label = strtolower($module . ' ' . $setting);
                    $source = 'addon:' . $module . '.' . $setting;

                    dm_epp_add_structured_config_candidates($config, $source, $value);

                    if ($isNeo && !preg_match('/netearth|neo|logicbox|resellerclubmods|lcdrm/i', $label)) {
                        continue;
                    }

                    if (preg_match('/(auth.*user|user.*id|reseller.*id|resellerid|rid|authuserid)/i', $label)) {
                        foreach (dm_epp_decrypt_candidates($value) as $candidate) {
                            if (preg_match('/([0-9]{2,})/', (string) $candidate, $match)) {
                                dm_epp_add_config_candidate($config, $source . '.auth-userid', $match[1]);
                                dm_epp_add_config_candidate($config, 'auth-userid', $match[1]);
                            }
                        }
                    }

                    if (preg_match('/(api.*key|apikey|api_key|access.*key|token|secret|password|key)/i', $label)
                        && !preg_match('/license|modulekey|licensekey/i', $label)) {
                        dm_epp_add_config_candidate($config, $source . '.api-key', $value);
                        dm_epp_add_config_candidate($config, 'api-key', $value);
                    }
                }
            }
        } catch (Throwable $e) {
            // Continue with tblconfiguration discovery.
        }

        try {
            if (Capsule::schema()->hasTable('tblconfiguration')) {
                $rows = Capsule::table('tblconfiguration')
                    ->where(function ($query) {
                        $query->where('setting', 'like', '%resellerclubmods%')
                            ->orWhere('setting', 'like', '%logicbox%')
                            ->orWhere('setting', 'like', '%netearth%')
                            ->orWhere('setting', 'like', '%neo%')
                            ->orWhere('setting', 'like', '%lcdrm%')
                            ->orWhere('setting', 'like', '%reseller%');
                    })
                    ->limit(250)
                    ->get();

                foreach ($rows as $row) {
                    $setting = (string) ($row->setting ?? '');
                    $value = $row->value ?? '';
                    $label = strtolower($setting);
                    $source = 'configuration:' . $setting;

                    dm_epp_add_structured_config_candidates($config, $source, $value);

                    if ($isNeo && !preg_match('/netearth|neo|logicbox|resellerclubmods|lcdrm/i', $label)) {
                        continue;
                    }

                    if (preg_match('/(auth.*user|user.*id|reseller.*id|resellerid|rid|authuserid)/i', $label)) {
                        foreach (dm_epp_decrypt_candidates($value) as $candidate) {
                            if (preg_match('/([0-9]{2,})/', (string) $candidate, $match)) {
                                dm_epp_add_config_candidate($config, $source . '.auth-userid', $match[1]);
                                dm_epp_add_config_candidate($config, 'auth-userid', $match[1]);
                            }
                        }
                    }

                    if (preg_match('/(api.*key|apikey|api_key|access.*key|token|secret|password|key)/i', $label)
                        && !preg_match('/license|modulekey|licensekey/i', $label)) {
                        dm_epp_add_config_candidate($config, $source . '.api-key', $value);
                        dm_epp_add_config_candidate($config, 'api-key', $value);
                    }
                }
            }
        } catch (Throwable $e) {
            // No addon credential table available.
        }

        return $config;
    }
}

if (!function_exists('dm_epp_merge_config_arrays')) {
    function dm_epp_merge_config_arrays(array $primary, array $secondary): array
    {
        foreach ($secondary as $key => $values) {
            foreach (is_array($values) ? $values : [(string) $values] as $value) {
                dm_epp_add_config_candidate($primary, (string) $key, $value);
            }
        }
        return $primary;
    }
}

if (!function_exists('dm_epp_pick_config_value')) {
    function dm_epp_pick_config_value(array $config, array $names, bool $numericOnly = false): string
    {
        $lookup = [];
        foreach ($config as $key => $values) {
            $lookup[strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $key))] = is_array($values) ? $values : [(string) $values];
        }

        foreach ($names as $name) {
            $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $name));
            if (!isset($lookup[$normalized])) {
                continue;
            }

            foreach ($lookup[$normalized] as $candidate) {
                $candidate = trim((string) $candidate);
                if ($candidate === '') {
                    continue;
                }
                if ($numericOnly && !preg_match('/^\d+$/', $candidate)) {
                    // Some encrypted/serialized values include labels around the id.
                    if (preg_match('/(?:auth[-_ ]?userid|userid|reseller[-_ ]?id)[^0-9]{0,12}(\d{2,})/i', $candidate, $match)) {
                        return $match[1];
                    }
                    continue;
                }
                return $candidate;
            }
        }

        return '';
    }
}

if (!function_exists('dm_epp_pick_auth_userid')) {
    function dm_epp_pick_auth_userid(array $config, array $names): array
    {
        $numeric = dm_epp_pick_config_value($config, $names, true);
        if ($numeric !== '') {
            return [$numeric, true];
        }

        /*
         * The NetEarthOne RCM module in this install exposes only sparse keys
         * such as Username and notapplicable. If Username is non-numeric, send
         * it to the LogicBoxes API before failing. The API will give the real
         * registrar-side error if it cannot accept it.
         */
        $fallback = dm_epp_pick_config_value($config, $names, false);
        if ($fallback !== '') {
            return [$fallback, false];
        }

        return ['', false];
    }
}

if (!function_exists('dm_epp_config_value_exists')) {
    function dm_epp_config_value_exists(array $config, array $names): bool
    {
        $lookup = [];
        foreach ($config as $key => $values) {
            $lookup[strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $key))] = is_array($values) ? $values : [(string) $values];
        }

        foreach ($names as $name) {
            $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $name));
            if (!isset($lookup[$normalized])) {
                continue;
            }

            foreach ($lookup[$normalized] as $candidate) {
                if (trim((string) $candidate) !== '') {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('dm_epp_redacted_config_keys')) {
    function dm_epp_redacted_config_keys(array $config): string
    {
        $keys = array_keys($config);
        return $keys ? implode(', ', $keys) : 'none';
    }
}

if (!function_exists('dm_epp_find_credentials')) {
    function dm_epp_find_credentials(string $registrar): array
    {
        $config = dm_epp_get_registrar_config($registrar);

        /*
         * NetEarthOne RCM uses sparse setting names in tblregistrars. In the
         * live error we saw only: moduledescription, notapplicable, Username.
         * Username should resolve to the LogicBoxes auth-userid. notapplicable
         * is commonly where the protected API/password value is stored by this
         * commercial module.
         */
        $userIdNames = [
            'auth-userid', 'authuserid', 'userid', 'user-id', 'user id',
            'resellerid', 'reseller-id', 'reseller id',
            'username', 'Username', 'UserName',
        ];
        $apiKeyNames = [
            'api-key', 'apikey', 'api key', 'APIKey', 'ApiKey',
            'accesskey', 'access key', 'key',
            'password', 'Password',
            'notapplicable', 'NotApplicable',
        ];

        [$userId, $userIdIsNumeric] = dm_epp_pick_auth_userid($config, $userIdNames);
        $apiKey = dm_epp_pick_config_value($config, $apiKeyNames, false);

        return [
            'authUserId' => $userId,
            'authUserIdIsNumeric' => $userIdIsNumeric,
            'apiKey' => $apiKey,
            'configKeys' => array_keys($config),
            'hasUserIdCandidate' => dm_epp_config_value_exists($config, $userIdNames),
            'hasApiKeyCandidate' => dm_epp_config_value_exists($config, $apiKeyNames),
            'testMode' => dm_epp_pick_config_value($config, ['testmode', 'test mode', 'sandbox', 'demo'], false),
            'apiUrl' => dm_epp_pick_config_value($config, ['apiurl', 'api url', 'url', 'endpoint'], false),
        ];
    }
}

if (!function_exists('dm_epp_api_base')) {
    function dm_epp_api_base(array $credentials): string
    {
        $configured = trim((string) ($credentials['apiUrl'] ?? ''));
        if ($configured !== '' && preg_match('#^https?://#i', $configured)) {
            return rtrim($configured, '/');
        }

        $testMode = strtolower(trim((string) ($credentials['testMode'] ?? '')));
        if (in_array($testMode, ['on', 'yes', 'true', '1', 'test', 'sandbox'], true)) {
            return 'https://test.httpapi.com/api';
        }

        return 'https://httpapi.com/api';
    }
}

if (!function_exists('dm_epp_http_request')) {
    function dm_epp_http_request(string $method, string $url, array $params): array
    {
        $method = strtoupper($method);
        $body = http_build_query($params, '', '&');

        if ($method === 'GET') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $body;
        }

        if (!function_exists('curl_init')) {
            return [false, null, 'cURL is not available on this PHP installation.'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 35);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DomainMonger-WHMCS-EPP-Manager/1.0');

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return [false, null, $error ?: 'No response from registrar API.'];
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

if (!function_exists('dm_epp_get_order_id')) {
    function dm_epp_get_order_id($domainRow, array $credentials): array
    {
        /*
         * Always ask LogicBoxes for the domain registration order ID first.
         * tbldomains.orderid is the WHMCS order that created the local domain
         * record; it is not the registrar's OrderBox/LogicBoxes order ID.
         */
        $base = dm_epp_api_base($credentials);
        [$ok, $response, $error] = dm_epp_http_request('GET', $base . '/domains/orderid.json', [
            'auth-userid' => $credentials['authUserId'],
            'api-key' => $credentials['apiKey'],
            'domain-name' => strtolower(trim((string) ($domainRow->domain ?? ''))),
        ]);

        if ($ok) {
            if (is_numeric($response) && dm_epp_is_valid_order_id($response)) {
                return [(string) $response, 'logicboxes-api.domains/orderid'];
            }

            if (is_string($response)) {
                $candidate = trim($response);
                if (dm_epp_is_valid_order_id($candidate)) {
                    return [$candidate, 'logicboxes-api.domains/orderid'];
                }
            }

            if (is_array($response)) {
                foreach (['orderid', 'order-id', 'entityid', 'id'] as $key) {
                    if (isset($response[$key]) && dm_epp_is_valid_order_id($response[$key])) {
                        return [(string) $response[$key], 'logicboxes-api.domains/orderid'];
                    }
                }
            }
        }

        /*
         * Only use explicitly registrar-specific local fields as a fallback.
         * Generic WHMCS order/subscription fields are deliberately excluded.
         */
        [$localOrderId, $localOrderSource] = dm_epp_pick_best_order_id($domainRow);
        if ($localOrderId !== '') {
            return [$localOrderId, $localOrderSource ?: 'registrar-specific-local-field'];
        }

        if (!$ok) {
            return ['', $error ?: 'Could not look up the registrar order ID by domain name.'];
        }

        return ['', 'Registrar order ID lookup did not return a valid order ID for this domain.'];
    }
}

if (!function_exists('dm_epp_random_index')) {
    function dm_epp_random_index(int $max): int
    {
        if ($max <= 0) {
            return 0;
        }

        try {
            return random_int(0, $max);
        } catch (Throwable $e) {
            return mt_rand(0, $max);
        }
    }
}

if (!function_exists('dm_epp_generate_code')) {
    function dm_epp_generate_code(): string
    {
        /*
         * Patch 1483: Register DNS auth codes must be 8-16 ASCII characters
         * and contain at least one uppercase letter, lowercase letter, number,
         * and approved symbol. Generate at the maximum supported length so the
         * result is strong while remaining registrar-compatible.
         */
        $groups = [
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'abcdefghijklmnopqrstuvwxyz',
            '0123456789',
            '.~!@#%^+=:-',
        ];
        $targetLength = 16;
        $characters = [];

        foreach ($groups as $group) {
            $characters[] = $group[dm_epp_random_index(strlen($group) - 1)];
        }

        $alphabet = implode('', $groups);
        while (count($characters) < $targetLength) {
            $characters[] = $alphabet[dm_epp_random_index(strlen($alphabet) - 1)];
        }

        for ($i = count($characters) - 1; $i > 0; $i--) {
            $j = dm_epp_random_index($i);
            $tmp = $characters[$i];
            $characters[$i] = $characters[$j];
            $characters[$j] = $tmp;
        }

        return implode('', $characters);
    }
}

if (!function_exists('dm_epp_validate_custom_code')) {
    function dm_epp_validate_custom_code(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return 'Please enter a custom EPP/Auth code.';
        }
        if (strlen($code) < 8 || strlen($code) > 16) {
            return 'Custom EPP/Auth codes must be between 8 and 16 characters.';
        }
        if (!preg_match('/^[A-Za-z0-9.~!@#%^+=:\-]+$/', $code)) {
            return 'Use only uppercase letters, lowercase letters, numbers, and these symbols: . ~ ! @ # % ^ + = : -';
        }
        if (!preg_match('/[A-Z]/', $code)) {
            return 'The EPP/Auth code must contain at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $code)) {
            return 'The EPP/Auth code must contain at least one lowercase letter.';
        }
        if (!preg_match('/[0-9]/', $code)) {
            return 'The EPP/Auth code must contain at least one number.';
        }
        if (!preg_match('/[.~!@#%^+=:\-]/', $code)) {
            return 'The EPP/Auth code must contain at least one allowed symbol: . ~ ! @ # % ^ + = : -';
        }
        return '';
    }
}


if (!function_exists('dm_epp_first_config_candidate')) {
    function dm_epp_first_config_candidate(array $config, string $key): string
    {
        if (!array_key_exists($key, $config)) {
            return '';
        }
        $values = is_array($config[$key]) ? $config[$key] : [(string) $config[$key]];

        /* Prefer the last candidate because dm_epp_decrypt_candidates keeps the
         * original first and appends successful decrypt results after it.
         */
        for ($i = count($values) - 1; $i >= 0; $i--) {
            $candidate = trim((string) $values[$i]);
            if ($candidate !== '') {
                return $candidate;
            }
        }
        return '';
    }
}

if (!function_exists('dm_epp_split_domain')) {
    function dm_epp_split_domain(string $domain): array
    {
        $domain = strtolower(trim($domain));
        $parts = explode('.', $domain, 2);
        if (count($parts) === 2) {
            return [$parts[0], '.' . $parts[1]];
        }
        return [$domain, ''];
    }
}



if (!function_exists('dm_epp_is_valid_order_id')) {
    function dm_epp_is_valid_order_id($value): bool
    {
        $value = trim((string) $value);
        if ($value === '' || strtoupper($value) === 'ERROR' || strtoupper($value) === 'NULL') {
            return false;
        }
        return preg_match('/^[1-9][0-9]*$/', $value) === 1;
    }
}

if (!function_exists('dm_epp_extract_valid_order_id')) {
    function dm_epp_extract_valid_order_id($value): string
    {
        $value = trim((string) $value);
        if (dm_epp_is_valid_order_id($value)) {
            return $value;
        }
        if (preg_match('/(?:order|entity|remote|registrar)[^0-9]{0,20}([1-9][0-9]{3,})/i', $value, $match)) {
            return $match[1];
        }
        return '';
    }
}

if (!function_exists('dm_epp_local_order_id_candidates')) {
    function dm_epp_local_order_id_candidates($domainRow): array
    {
        $fields = [
            'registrarorderid', 'remoteid', 'registrarOrderId',
            'registrar_order_id', 'epporderid', 'entityid', 'entity_id'
        ];
        $candidates = [];

        foreach ($fields as $field) {
            if (!is_object($domainRow) || !isset($domainRow->{$field})) {
                continue;
            }
            $value = trim((string) $domainRow->{$field});
            if (!dm_epp_is_valid_order_id($value)) {
                continue;
            }
            $candidates[$field] = $value;
        }

        return $candidates;
    }
}

if (!function_exists('dm_epp_pick_local_order_id')) {
    function dm_epp_pick_local_order_id($domainRow): string
    {
        foreach (dm_epp_local_order_id_candidates($domainRow) as $candidate) {
            if (dm_epp_is_valid_order_id($candidate)) {
                return (string) $candidate;
            }
        }
        return '';
    }
}

if (!function_exists('dm_epp_safe_identifier')) {
    function dm_epp_safe_identifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}

if (!function_exists('dm_epp_database_table_names')) {
    function dm_epp_database_table_names(): array
    {
        try {
            $rows = Capsule::select('SHOW TABLES');
            $tables = [];
            foreach ($rows as $row) {
                $values = array_values((array) $row);
                if (!empty($values[0])) {
                    $tables[] = (string) $values[0];
                }
            }
            return $tables;
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('dm_epp_database_columns')) {
    function dm_epp_database_columns(string $table): array
    {
        try {
            $rows = Capsule::select('SHOW COLUMNS FROM ' . dm_epp_safe_identifier($table));
            $columns = [];
            foreach ($rows as $row) {
                $arr = (array) $row;
                if (!empty($arr['Field'])) {
                    $columns[] = (string) $arr['Field'];
                }
            }
            return $columns;
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('dm_epp_dynamic_order_id_candidates')) {
    function dm_epp_dynamic_order_id_candidates($domainRow): array
    {
        $domainId = (int) ($domainRow->id ?? 0);
        $domainName = strtolower(trim((string) ($domainRow->domain ?? '')));
        if ($domainId <= 0 || $domainName === '') {
            return [];
        }

        $candidates = [];

        // Domain custom fields are a common safe place for registrar order IDs in custom modules.
        try {
            if (Capsule::schema()->hasTable('tblcustomfields') && Capsule::schema()->hasTable('tblcustomfieldsvalues')) {
                $rows = Capsule::table('tblcustomfieldsvalues as v')
                    ->join('tblcustomfields as f', 'f.id', '=', 'v.fieldid')
                    ->where('v.relid', $domainId)
                    ->where(function ($query) {
                        $query->where('f.fieldname', 'like', '%order%')
                            ->orWhere('f.fieldname', 'like', '%entity%')
                            ->orWhere('f.fieldname', 'like', '%logic%')
                            ->orWhere('f.fieldname', 'like', '%reseller%')
                            ->orWhere('f.fieldname', 'like', '%remote%');
                    })
                    ->select('f.fieldname', 'v.value')
                    ->limit(10)
                    ->get();
                foreach ($rows as $row) {
                    $orderId = dm_epp_extract_valid_order_id($row->value ?? '');
                    if ($orderId !== '') {
                        $candidates['tblcustomfieldsvalues.' . (string) ($row->fieldname ?? 'value')] = $orderId;
                    }
                }
            }
        } catch (Throwable $e) {
            // Continue with table discovery.
        }

        $tables = dm_epp_database_table_names();
        $interestingTablePattern = '/(reseller|logicbox|logicboxes|netearth|registrar|domain|rcm|mods|module)/i';
        foreach ($tables as $table) {
            if (!preg_match($interestingTablePattern, $table)) {
                continue;
            }
            if ($table === 'tbldomains' || $table === 'tbldomainpricing') {
                continue;
            }

            $columns = dm_epp_database_columns($table);
            if (!$columns) {
                continue;
            }

            $domainIdColumns = [];
            $domainNameColumns = [];
            $orderColumns = [];
            foreach ($columns as $column) {
                $lower = strtolower($column);
                if (preg_match('/^(domainid|domain_id|whmcsdomainid|whmcs_domain_id|tbldomains_id|relid)$/', $lower)) {
                    $domainIdColumns[] = $column;
                }
                if (preg_match('/^(domain|domainname|domain_name|name)$/', $lower)) {
                    $domainNameColumns[] = $column;
                }
                if (preg_match('/(order.*id|orderid|entity.*id|entityid|remote.*id|registrar.*id|logicbox.*id|reseller.*id)/i', $column)) {
                    $orderColumns[] = $column;
                }
            }

            if (!$orderColumns || (!$domainIdColumns && !$domainNameColumns)) {
                continue;
            }

            try {
                $query = Capsule::table($table)->select($orderColumns);
                $query->where(function ($subQuery) use ($domainIdColumns, $domainNameColumns, $domainId, $domainName) {
                    foreach ($domainIdColumns as $column) {
                        $subQuery->orWhere($column, $domainId);
                    }
                    foreach ($domainNameColumns as $column) {
                        $subQuery->orWhere($column, $domainName)
                            ->orWhere($column, strtoupper($domainName));
                    }
                });
                $rows = $query->limit(5)->get();
                foreach ($rows as $row) {
                    foreach ($orderColumns as $orderColumn) {
                        $orderId = dm_epp_extract_valid_order_id($row->{$orderColumn} ?? '');
                        if ($orderId !== '') {
                            $candidates[$table . '.' . $orderColumn] = $orderId;
                        }
                    }
                }
            } catch (Throwable $e) {
                continue;
            }
        }

        return $candidates;
    }
}


if (!function_exists('dm_epp_basic_registrar_params_for_lookup')) {
    function dm_epp_basic_registrar_params_for_lookup($domainRow): array
    {
        $registrar = (string) ($domainRow->registrar ?? '');
        [$sld, $tld] = dm_epp_split_domain((string) ($domainRow->domain ?? ''));
        $config = dm_epp_get_registrar_config($registrar);

        $params = [
            'userid' => (int) ($domainRow->userid ?? 0),
            'domainid' => (int) ($domainRow->id ?? 0),
            'id' => (int) ($domainRow->id ?? 0),
            'domain' => (string) ($domainRow->domain ?? ''),
            'sld' => $sld,
            'tld' => $tld,
            'regperiod' => (int) ($domainRow->registrationperiod ?? $domainRow->regperiod ?? 1),
            'registrar' => $registrar,
            'ns1' => (string) ($domainRow->ns1 ?? ''),
            'ns2' => (string) ($domainRow->ns2 ?? ''),
            'ns3' => (string) ($domainRow->ns3 ?? ''),
            'ns4' => (string) ($domainRow->ns4 ?? ''),
            'ns5' => (string) ($domainRow->ns5 ?? ''),
            'dnsmanagement' => !empty($domainRow->dnsmanagement),
            'emailforwarding' => !empty($domainRow->emailforwarding),
            'idprotection' => !empty($domainRow->idprotection),
        ];

        foreach ($config as $key => $values) {
            $params[$key] = dm_epp_first_config_candidate($config, (string) $key);
        }

        return $params;
    }
}

if (!function_exists('dm_epp_extract_order_id_from_text')) {
    function dm_epp_extract_order_id_from_text(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);

        $patterns = [
            '/\bOrder\s*ID\s*[:#-]?\s*([1-9][0-9]{3,})\b/i',
            '/\border[\s_-]*id\s*[:#=-]?\s*([1-9][0-9]{3,})\b/i',
            '/\bentity[\s_-]*id\s*[:#=-]?\s*([1-9][0-9]{3,})\b/i',
            '/\bLogicBoxes\s*ID\s*[:#=-]?\s*([1-9][0-9]{3,})\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $match) && dm_epp_is_valid_order_id($match[1])) {
                return (string) $match[1];
            }
        }

        return '';
    }
}

if (!function_exists('dm_epp_extract_order_id_from_result')) {
    function dm_epp_extract_order_id_from_result($result): string
    {
        if (is_scalar($result)) {
            return dm_epp_extract_order_id_from_text((string) $result);
        }

        if (is_object($result)) {
            $result = (array) $result;
        }

        if (!is_array($result)) {
            return '';
        }

        foreach ($result as $key => $value) {
            $keyText = strtolower((string) $key);

            if (is_scalar($value) && preg_match('/order|entity|logicbox|remote|registrar/i', $keyText)) {
                $candidate = dm_epp_extract_valid_order_id($value);
                if ($candidate !== '') {
                    return $candidate;
                }
            }

            $nested = dm_epp_extract_order_id_from_result($value);
            if ($nested !== '') {
                return $nested;
            }
        }

        return '';
    }
}

if (!function_exists('dm_epp_find_module_order_lookup_functions')) {
    function dm_epp_find_module_order_lookup_functions(string $registrar, array $params): array
    {
        [$loaded, $loadError] = dm_epp_load_registrar_module($registrar);
        if (!$loaded) {
            return [];
        }

        $functions = [];
        $arrayFunctions = [
            $registrar . '_AdminCustomButtonArray',
            $registrar . '_ClientAreaCustomButtonArray',
            $registrar . '_ClientAreaAllowedFunctions',
        ];

        foreach ($arrayFunctions as $arrayFunction) {
            if (!function_exists($arrayFunction)) {
                continue;
            }

            try {
                $buttons = call_user_func($arrayFunction, $params);
            } catch (Throwable $e) {
                try {
                    $buttons = call_user_func($arrayFunction);
                } catch (Throwable $e2) {
                    continue;
                }
            }

            if (!is_array($buttons)) {
                continue;
            }

            foreach ($buttons as $label => $method) {
                $haystack = strtolower((string) $label . ' ' . (string) $method);
                if (!preg_match('/info|detail|service|customer|order|account|lookup|sync/i', $haystack)) {
                    continue;
                }
                if (preg_match('/delete|renew|transfer|lock|dns|mail|forward|protect|privacy|epp|auth/i', $haystack)) {
                    continue;
                }

                $method = (string) $method;
                $function = strpos($method, $registrar . '_') === 0 ? $method : $registrar . '_' . $method;
                if (function_exists($function)) {
                    $functions[$function] = true;
                }
            }
        }

        foreach (get_defined_functions()['user'] ?? [] as $function) {
            if (stripos($function, strtolower($registrar . '_')) !== 0) {
                continue;
            }
            if (preg_match('/info|detail|service|customer|order|lookup|sync/i', $function)
                && !preg_match('/delete|renew|transfer|lock|dns|mail|forward|protect|privacy|epp|auth/i', $function)) {
                $functions[$function] = true;
            }
        }

        return array_keys($functions);
    }
}

if (!function_exists('dm_epp_lookup_order_id_via_registrar_module')) {
    function dm_epp_lookup_order_id_via_registrar_module($domainRow): array
    {
        $registrar = (string) ($domainRow->registrar ?? '');
        if ($registrar === '') {
            return ['', ''];
        }

        $params = dm_epp_basic_registrar_params_for_lookup($domainRow);
        $functions = dm_epp_find_module_order_lookup_functions($registrar, $params);

        foreach ($functions as $function) {
            $oldPost = $_POST;
            $oldRequest = $_REQUEST;
            $_POST['domainid'] = (string) ($domainRow->id ?? '');
            $_REQUEST['domainid'] = (string) ($domainRow->id ?? '');
            $_POST['domain'] = (string) ($domainRow->domain ?? '');
            $_REQUEST['domain'] = (string) ($domainRow->domain ?? '');

            try {
                $result = call_user_func($function, $params);
            } catch (Throwable $e) {
                $_POST = $oldPost;
                $_REQUEST = $oldRequest;
                continue;
            }

            $_POST = $oldPost;
            $_REQUEST = $oldRequest;

            $orderId = dm_epp_extract_order_id_from_result($result);
            if ($orderId !== '') {
                return [$orderId, 'registrar-module-info.' . $function];
            }
        }

        return ['', ''];
    }
}


if (!function_exists('dm_epp_pick_best_order_id')) {
    function dm_epp_pick_best_order_id($domainRow): array
    {
        foreach (dm_epp_local_order_id_candidates($domainRow) as $field => $candidate) {
            if (dm_epp_is_valid_order_id($candidate)) {
                return [(string) $candidate, 'tbldomains.' . $field];
            }
        }
        foreach (dm_epp_dynamic_order_id_candidates($domainRow) as $source => $candidate) {
            if (dm_epp_is_valid_order_id($candidate)) {
                return [(string) $candidate, $source];
            }
        }

        [$moduleOrderId, $moduleOrderSource] = dm_epp_lookup_order_id_via_registrar_module($domainRow);
        if (dm_epp_is_valid_order_id($moduleOrderId)) {
            return [(string) $moduleOrderId, $moduleOrderSource ?: 'registrar-module-info'];
        }

        return ['', ''];
    }
}


if (!function_exists('dm_epp_build_registrar_params')) {
    function dm_epp_build_registrar_params($domainRow): array
    {
        $registrar = (string) ($domainRow->registrar ?? '');
        [$sld, $tld] = dm_epp_split_domain((string) ($domainRow->domain ?? ''));
        $config = dm_epp_get_registrar_config($registrar);

        $params = [
            'userid' => (int) ($domainRow->userid ?? 0),
            'domainid' => (int) ($domainRow->id ?? 0),
            'domain' => (string) ($domainRow->domain ?? ''),
            'sld' => $sld,
            'tld' => $tld,
            'regperiod' => (int) ($domainRow->registrationperiod ?? $domainRow->regperiod ?? 1),
            'registrar' => $registrar,
            'ns1' => (string) ($domainRow->ns1 ?? ''),
            'ns2' => (string) ($domainRow->ns2 ?? ''),
            'ns3' => (string) ($domainRow->ns3 ?? ''),
            'ns4' => (string) ($domainRow->ns4 ?? ''),
            'ns5' => (string) ($domainRow->ns5 ?? ''),
            'dnsmanagement' => !empty($domainRow->dnsmanagement),
            'emailforwarding' => !empty($domainRow->emailforwarding),
            'idprotection' => !empty($domainRow->idprotection),
        ];

        [$localOrderId, $localOrderSource] = dm_epp_pick_best_order_id($domainRow);
        if ($localOrderId !== '') {
            foreach ([
                'orderid', 'order-id', 'order_id', 'OrderID', 'OrderId',
                'registrarorderid', 'registrarOrderId', 'subscriptionid',
                'entityid', 'entity-id'
            ] as $orderKey) {
                $params[$orderKey] = $localOrderId;
            }
        }

        foreach (dm_epp_local_order_id_candidates($domainRow) as $field => $value) {
            $params['local_' . $field] = $value;
        }
        foreach (dm_epp_dynamic_order_id_candidates($domainRow) as $source => $value) {
            $safeKey = preg_replace('/[^A-Za-z0-9_]/', '_', 'dynamic_' . $source);
            $params[$safeKey] = $value;
        }

        foreach ($config as $key => $values) {
            $params[$key] = dm_epp_first_config_candidate($config, (string) $key);
        }

        try {
            $client = Capsule::table('tblclients')->where('id', (int) ($domainRow->userid ?? 0))->first();
            if ($client) {
                foreach ([
                    'firstname', 'lastname', 'companyname', 'email', 'address1', 'address2',
                    'city', 'state', 'postcode', 'country', 'phonenumber'
                ] as $field) {
                    $params[$field] = (string) ($client->{$field} ?? '');
                }
                $params['fullname'] = trim(($params['firstname'] ?? '') . ' ' . ($params['lastname'] ?? ''));
                $params['countrycode'] = $params['country'] ?? '';
            }
        } catch (Throwable $e) {
            // Contact fields are not required for auth-code modification.
        }

        return $params;
    }
}

if (!function_exists('dm_epp_load_registrar_module')) {
    function dm_epp_load_registrar_module(string $registrar): array
    {
        $registrar = preg_replace('/[^a-z0-9_]/i', '', $registrar);
        if ($registrar === '') {
            return [false, 'Registrar module name is empty.'];
        }

        $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
        $moduleFile = $root . '/modules/registrars/' . $registrar . '/' . $registrar . '.php';
        if (!is_file($moduleFile)) {
            return [false, 'Registrar module file was not found: modules/registrars/' . $registrar . '/' . $registrar . '.php'];
        }

        try {
            require_once $moduleFile;
        } catch (Throwable $e) {
            return [false, 'Registrar module could not be loaded: ' . $e->getMessage()];
        }

        return [true, ''];
    }
}

if (!function_exists('dm_epp_find_module_authcode_function')) {
    function dm_epp_find_module_authcode_function(string $registrar, array $params): array
    {
        [$loaded, $loadError] = dm_epp_load_registrar_module($registrar);
        if (!$loaded) {
            return ['', $loadError];
        }

        $arrayFunctions = [
            $registrar . '_AdminCustomButtonArray',
            $registrar . '_ClientAreaCustomButtonArray',
            $registrar . '_ClientAreaAllowedFunctions',
        ];

        $seen = [];
        foreach ($arrayFunctions as $arrayFunction) {
            if (!function_exists($arrayFunction)) {
                continue;
            }

            try {
                $buttons = call_user_func($arrayFunction, $params);
            } catch (Throwable $e) {
                try {
                    $buttons = call_user_func($arrayFunction);
                } catch (Throwable $e2) {
                    continue;
                }
            }

            if (!is_array($buttons)) {
                continue;
            }

            foreach ($buttons as $label => $method) {
                $labelText = strtolower((string) $label);
                $methodText = strtolower((string) $method);
                $seen[] = (string) $label . '=>' . (string) $method;

                if ((preg_match('/epp|auth/i', $labelText) || preg_match('/epp|auth/i', $methodText))
                    && (preg_match('/modif|generate|change|set|code/i', $labelText . ' ' . $methodText))) {
                    $method = (string) $method;
                    $function = strpos($method, $registrar . '_') === 0 ? $method : $registrar . '_' . $method;
                    if (function_exists($function)) {
                        return [$function, ''];
                    }
                }
            }
        }

        return ['', $seen ? 'No EPP/Auth custom function matched. Buttons found: ' . implode(', ', $seen) : 'No registrar EPP/Auth custom button array was found.'];
    }
}

if (!function_exists('dm_epp_normalize_module_result')) {
    function dm_epp_normalize_module_result($result, string $newCode): array
    {
        if (is_array($result)) {
            foreach (['error', 'Error', 'message'] as $key) {
                if (isset($result[$key]) && stripos((string) $key, 'error') !== false && trim((string) $result[$key]) !== '') {
                    return [false, (string) $result[$key]];
                }
            }

            $status = strtoupper((string) ($result['status'] ?? $result['result'] ?? $result['actionstatus'] ?? ''));
            if ($status === 'ERROR') {
                return [false, (string) ($result['message'] ?? $result['error'] ?? $result['actionstatusdesc'] ?? 'Registrar module returned an error.')];
            }

            if (isset($result['success']) && trim((string) $result['success']) !== '') {
                return [true, (string) $result['success']];
            }
            if ($status === 'SUCCESS' || $status === 'OK') {
                return [true, 'The registrar module reported success. New code requested: ' . $newCode];
            }

            return [true, 'The registrar module accepted the EPP/Auth code action. New code requested: ' . $newCode];
        }

        if (is_string($result) && trim($result) !== '') {
            if (stripos($result, 'error') !== false || stripos($result, 'failed') !== false) {
                return [false, trim($result)];
            }
            return [true, trim($result)];
        }

        return [true, 'The registrar module completed the EPP/Auth code action. New code requested: ' . $newCode];
    }
}

if (!function_exists('dm_epp_try_module_authcode_function')) {
    function dm_epp_try_module_authcode_function($domainRow, string $newCode): array
    {
        $registrar = (string) ($domainRow->registrar ?? '');
        $params = dm_epp_build_registrar_params($domainRow);
        $params['authcode'] = $newCode;
        $params['auth-code'] = $newCode;
        $params['eppcode'] = $newCode;
        $params['eppCode'] = $newCode;
        $params['newauthcode'] = $newCode;
        $params['newAuthCode'] = $newCode;

        [$function, $error] = dm_epp_find_module_authcode_function($registrar, $params);
        if ($function === '') {
            return [false, $error ?: 'No registrar module EPP/Auth function was found.'];
        }

        $oldPost = $_POST;
        $oldRequest = $_REQUEST;
        foreach ([
            'authcode', 'auth-code', 'eppcode', 'eppCode', 'newauthcode', 'newAuthCode',
            'customauthcode', 'customAuthCode', 'new_epp_code', 'new_auth_code'
        ] as $key) {
            $_POST[$key] = $newCode;
            $_REQUEST[$key] = $newCode;
        }
        $_POST['domainid'] = (string) ($domainRow->id ?? '');
        $_REQUEST['domainid'] = (string) ($domainRow->id ?? '');

        [$localOrderId, $localOrderSource] = dm_epp_pick_best_order_id($domainRow);
        if ($localOrderId !== '') {
            foreach ([
                'orderid', 'order-id', 'order_id', 'OrderID', 'OrderId',
                'registrarorderid', 'registrarOrderId', 'subscriptionid',
                'entityid', 'entity-id'
            ] as $orderKey) {
                $_POST[$orderKey] = $localOrderId;
                $_REQUEST[$orderKey] = $localOrderId;
            }
        }

        try {
            $result = call_user_func($function, $params);
        } catch (Throwable $e) {
            $_POST = $oldPost;
            $_REQUEST = $oldRequest;
            return [false, 'Registrar module function ' . $function . ' failed: ' . $e->getMessage()];
        }

        $_POST = $oldPost;
        $_REQUEST = $oldRequest;

        return dm_epp_normalize_module_result($result, $newCode);
    }
}

if (!function_exists('dm_epp_modify_auth_code')) {
    function dm_epp_modify_auth_code($domainRow, string $newCode): array
    {
        $registrar = (string) ($domainRow->registrar ?? '');
        if ($registrar === '' || !preg_match('/(netearth|resellerclub|logicboxes)/i', $registrar)) {
            return [false, 'EPP/Auth code management is only enabled for NetEarthOne/LogicBoxes registrar domains.'];
        }

        $credentials = dm_epp_find_credentials($registrar);

        /* NetEarthOne requires auth-userid to be an integer. If the only value
         * available is the commercial module's Username string, do not send it
         * to the public API. Use the installed registrar module fallback instead.
         */
        $canTryDirectApi = !empty($credentials['authUserId'])
            && !empty($credentials['apiKey'])
            && preg_match('/^\d+$/', (string) $credentials['authUserId']);

        $directApiError = '';
        if ($canTryDirectApi) {
            [$orderId, $orderSourceOrError] = dm_epp_get_order_id($domainRow, $credentials);
            if ($orderId === '') {
                $directApiError = $orderSourceOrError ?: 'Registrar order ID is missing for this domain.';
            } else {
                $base = dm_epp_api_base($credentials);
                [$ok, $response, $error] = dm_epp_http_request('POST', $base . '/domains/modify-auth-code.json', [
                    'auth-userid' => $credentials['authUserId'],
                    'api-key' => $credentials['apiKey'],
                    'order-id' => $orderId,
                    'auth-code' => $newCode,
                ]);

                if ($ok) {
                    return [true, 'The EPP/Auth code was updated successfully. New code: ' . $newCode];
                }

                $directApiError = $error ?: 'Registrar API did not accept the new EPP/Auth code.';
            }
        } else {
            $keys = !empty($credentials['configKeys']) && is_array($credentials['configKeys']) ? implode(', ', $credentials['configKeys']) : 'none';
            $directApiError = 'Direct API skipped because usable numeric auth-userid/api-key credentials were not available. Found config keys: ' . $keys . '.';
        }

        [$moduleOk, $moduleMessage] = dm_epp_try_module_authcode_function($domainRow, $newCode);
        if ($moduleOk) {
            return [true, $moduleMessage . ' Direct API note: ' . $directApiError];
        }

        [$localOrderId, $localOrderSource] = dm_epp_pick_best_order_id($domainRow);
        $localOrderNote = $localOrderId !== ''
            ? ' Registrar order-id candidate used: ' . $localOrderId . ' from ' . $localOrderSource . '.'
            : ' No explicit registrar order ID was found in registrar-specific local storage or via the registrar module info lookup.';

        return [false, $directApiError . $localOrderNote . ' Registrar module fallback also failed: ' . $moduleMessage];
    }
}

if (!function_exists('dm_epp_redirect_to_page')) {
    function dm_epp_redirect_to_page(?int $domainId = null): void
    {
        if ($domainId === null) {
            $domainId = (int) ($_REQUEST['domainid'] ?? 0);
        }

        $url = dm_epp_form_url($domainId);
        header('Location: ' . $url, true, 303);
        exit;
    }
}


if (!function_exists('dm_epp_form_url')) {
    function dm_epp_form_url(int $domainId): string
    {
        $url = 'clientarea.php?action=domaingetepp';
        if ($domainId > 0) {
            $url .= '&domainid=' . $domainId;
        }
        return $url;
    }
}

if (!function_exists('dm_epp_endpoint_url')) {
    function dm_epp_endpoint_url(): string
    {
        return 'dm-epp-authcode-action.php';
    }
}

if (!function_exists('dm_epp_process_authcode_request')) {
    function dm_epp_process_authcode_request(int $domainId, string $action, string $token, string $customCode = ''): array
    {
        if (!dm_epp_valid_token($token)) {
            return [false, 'Security token expired. Please reload the page and try again.'];
        }

        $domainRow = dm_epp_get_domain_row($domainId);
        if (!$domainRow) {
            return [false, 'This domain could not be found for your account.'];
        }

        $newCode = '';
        if ($action === 'generate') {
            $newCode = dm_epp_generate_code();
        } elseif ($action === 'custom') {
            $newCode = trim($customCode);
            $validationError = dm_epp_validate_custom_code($newCode);
            if ($validationError !== '') {
                return [false, $validationError];
            }
        } else {
            return [false, 'Unknown EPP/Auth code action.'];
        }

        return dm_epp_modify_auth_code($domainRow, $newCode);
    }
}

add_hook('ClientAreaPageDomainEPPCode', 1, function ($vars) {
    if (!dm_epp_is_page()) {
        return [];
    }

    $domainId = (int) ($_REQUEST['domainid'] ?? 0);
    $domainRow = dm_epp_get_domain_row($domainId);
    $flash = dm_epp_flash();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dm_epp_action'])) {
        [$ok, $message] = dm_epp_process_authcode_request(
            $domainId,
            (string) ($_POST['dm_epp_action'] ?? ''),
            (string) ($_POST['dm_epp_token'] ?? ''),
            (string) ($_POST['dm_epp_custom_code'] ?? '')
        );
        dm_epp_flash($ok ? 'success' : 'error', $message);
        dm_epp_redirect_to_page($domainId);
    }

    $enabled = false;
    if ($domainRow && !empty($domainRow->registrar)) {
        $enabled = (bool) preg_match('/(netearth|resellerclub|logicboxes)/i', (string) $domainRow->registrar);
    }

    return [
        'dmEppManagerEnabled' => $enabled,
        'dmEppToken' => dm_epp_get_token(),
        'dmEppFormUrl' => dm_epp_form_url($domainId),
        'dmEppEndpointUrl' => dm_epp_endpoint_url(),
        'dmEppDomainId' => $domainId,
        'dmEppFlashType' => $flash['type'] ?? '',
        'dmEppFlashMessage' => $flash['message'] ?? '',
    ];
});

add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_epp_is_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-epp-authcode-manager-css-v758">
.dm-epp-authcode-tools {
    margin-top: 18px;
    padding-top: 18px;
    border-top: 1px solid #e5eaf0;
}
.dm-epp-authcode-tools h4 {
    margin: 0 0 8px;
    color: #163a5f;
    font-weight: 700;
}
.dm-epp-authcode-tools p {
    margin-bottom: 12px;
}
.dm-epp-authcode-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: nowrap;
    margin: 10px 0;
}
.dm-epp-authcode-row button {
    flex: 0 0 300px;
    width: 300px;
    min-width: 300px;
    text-align: left;
    justify-content: flex-start;
}
.dm-epp-authcode-row input[type="text"] {
    order: 2;
    flex: 1 1 320px;
    max-width: 420px;
    min-width: 240px;
}
.dm-epp-authcode-row button + input[type="text"],
.dm-epp-authcode-row input[type="text"] + button {
    order: 1;
}
.dm-epp-authcode-row input[type="text"] + button {
    order: 1;
}
.dm-epp-authcode-row input[type="text"] {
    order: 2;
}
@media (max-width: 640px) {
    .dm-epp-authcode-row {
        flex-wrap: wrap;
    }
    .dm-epp-authcode-row button {
        flex-basis: 100%;
        min-width: 0;
        width: 100%;
        text-align: center;
    }
    .dm-epp-authcode-row input[type="text"] {
        max-width: none;
        width: 100%;
        flex-basis: 100%;
    }
}
.dm-epp-authcode-tools .btn-primary,
.dm-epp-authcode-tools .btn-success {
    background: #f58220 !important;
    border-color: #f58220 !important;
    color: #fff !important;
}
.dm-epp-authcode-tools .btn-primary:hover,
.dm-epp-authcode-tools .btn-primary:focus,
.dm-epp-authcode-tools .btn-success:hover,
.dm-epp-authcode-tools .btn-success:focus {
    background: #d8741f !important;
    border-color: #d8741f !important;
    color: #fff !important;
}
.dm-epp-authcode-note {
    font-size: 12px;
    opacity: .85;
}

.dm-epp-authcode-tools h4,
.dm-epp-authcode-tools p {
    display: none !important;
}
.dm-epp-authcode-note {
    display: block !important;
    margin-top: 8px;
    font-size: 12px;
    opacity: .85;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_epp_is_page()) {
        return '';
    }

    $endpointUrl = htmlspecialchars(dm_epp_endpoint_url(), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<script id="dm-epp-authcode-endpoint-js-v758">
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function closestForm(el) {
        while (el && el !== document) {
            if ((el.tagName || '').toLowerCase() === 'form') {
                return el;
            }
            el = el.parentNode;
        }
        return null;
    }

    function submitToEndpoint(sourceForm) {
        var clean = document.createElement('form');
        var fields;
        var i;
        var input;

        clean.method = 'post';
        clean.action = '{$endpointUrl}';
        clean.style.display = 'none';

        fields = sourceForm.querySelectorAll('input, textarea, select');
        for (i = 0; i < fields.length; i += 1) {
            if (!fields[i].name || fields[i].disabled) {
                continue;
            }
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = fields[i].name;
            input.value = fields[i].value;
            clean.appendChild(input);
        }

        document.body.appendChild(clean);
        clean.submit();
    }

    ready(function () {
        var customForms = document.querySelectorAll('.dm-epp-authcode-row');
        var j;
        var customInput;
        var customButton;
        for (j = 0; j < customForms.length; j += 1) {
            customInput = customForms[j].querySelector('input[name="dm_epp_custom_code"]');
            customButton = customForms[j].querySelector('button');
            if (customInput && customButton && customForms[j].firstElementChild !== customButton) {
                customForms[j].insertBefore(customButton, customInput);
                customForms[j].setAttribute('data-dm-epp-authcode-layout-fix', '1');
            }
        }

    
        var buttons = document.querySelectorAll('[data-dm-epp-submit="1"]');

        var safeNote = document.querySelector('.dm-epp-authcode-note');
        if (safeNote) {
            safeNote.textContent = '8-16 characters with uppercase, lowercase, a number, and one symbol: . ~ ! @ # % ^ + = : -';
        }

        var forms = document.querySelectorAll('form.dm-epp-authcode-row');
        var i;

        for (i = 0; i < forms.length; i += 1) {
            forms[i].setAttribute('action', '{$endpointUrl}');
            forms[i].setAttribute('method', 'post');
            forms[i].addEventListener('submit', function (event) {
                event.preventDefault();
                event.stopPropagation();
                submitToEndpoint(this);
                return false;
            }, true);
        }

        for (i = 0; i < buttons.length; i += 1) {
            buttons[i].setAttribute('type', 'button');
            buttons[i].addEventListener('click', function (event) {
                var form = closestForm(this);
                event.preventDefault();
                event.stopPropagation();
                if (form) {
                    submitToEndpoint(form);
                }
                return false;
            }, true);
        }
    });
}());
</script>
HTML;
});
