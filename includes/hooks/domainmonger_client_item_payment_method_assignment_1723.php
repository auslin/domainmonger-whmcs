<?php
/**
 * DomainMonger Patch 1748 (1747 baseline + per-account routing enable/disable controls)
 *
 * Per-product/service and per-domain WHMCS Pay Method assignments with a
 * parallel-test safety layer.
 *
 * Supported explicit Pay Methods:
 * - Authorize.net / Authorize.net CIM saved credit cards
 * - PayPal Payments vaulted PayPal accounts (paypal_ppcpv)
 *
 * Explicitly excluded:
 * - PayPal Card Payments / ACDC (paypal_acdc)
 * - Bank accounts and unrelated gateways
 *
 * Routing modes are configured through the DomainMonger Payment Routing addon:
 * - Off: no assignment UI and no routing changes.
 * - Admin Preview (No Routing): admin-only assignment UI + saved mappings;
 *   no client UI, invoice changes, or Separate Invoices changes.
 * - Shadow (Test Clients): test-client UI + saved assignments + Activity Log
 *   decisions; invoices and Separate Invoices remain untouched.
 * - Test Live (Test Clients): live routing only for allowlisted clients.
 * - Live (All Clients): live routing for all clients.
 *
 * WHMCS remains authoritative for Pay Method storage, gateway processing and
 * invoice capture. This hook stores only native WHMCS Pay Method IDs.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm1723_escape')) {
    function dm1723_escape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('dm1723_client_id')) {
    function dm1723_client_id(): int
    {
        // WHMCS 8/9 separates authenticated Users from Client Accounts. Use
        // CurrentUser::client() as the authoritative active client resolver so
        // GetPayMethods always receives tblclients.id, then retain the proven
        // legacy session aliases only as compatibility fallbacks.
        try {
            $currentUser = new \WHMCS\Authentication\CurrentUser();
            $client = $currentUser->client();
            if ($client && (int) ($client->id ?? 0) > 0) {
                return (int) $client->id;
            }
        } catch (Throwable $e) {
            // Fall through to the existing session aliases.
        }

        return (int) (
            $_SESSION['uid']
            ?? $_SESSION['clientid']
            ?? $_SESSION['clientareauserid']
            ?? 0
        );
    }
}

if (!function_exists('dm1723_now')) {
    function dm1723_now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('dm1723_settings')) {
    function dm1723_settings(): array
    {
        static $settings = null;
        if ($settings !== null) {
            return $settings;
        }

        $settings = [
            'active' => false,
            'routing_mode' => 'off',
            'test_client_ids' => [],
            'log_decisions' => true,
        ];

        try {
            $rows = Capsule::table('tbladdonmodules')
                ->where('module', 'domainmongerpayrouting')
                ->get(['setting', 'value']);

            if (!$rows || count($rows) === 0) {
                return $settings;
            }

            $raw = [];
            foreach ($rows as $row) {
                $raw[(string) ($row->setting ?? '')] = (string) ($row->value ?? '');
            }

            // WHMCS removes/omits addon settings when the module is inactive.
            // Any row for this module is enough to treat it as activated; mode
            // still defaults safely to Off when no routing_mode value exists.
            $settings['active'] = true;
            $mode = strtolower(trim((string) ($raw['routing_mode'] ?? 'Off')));
            if (strpos($mode, 'admin preview') !== false) {
                $settings['routing_mode'] = 'admin_preview';
            } elseif (strpos($mode, 'test live') !== false) {
                $settings['routing_mode'] = 'test_live';
            } elseif (strpos($mode, 'shadow') !== false) {
                $settings['routing_mode'] = 'shadow';
            } elseif (strpos($mode, 'live') !== false) {
                $settings['routing_mode'] = 'live';
            } else {
                $settings['routing_mode'] = 'off';
            }

            $ids = preg_split('/[^0-9]+/', (string) ($raw['test_client_ids'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
            $testIds = [];
            foreach ($ids ?: [] as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $testIds[$id] = true;
                }
            }
            $settings['test_client_ids'] = array_keys($testIds);

            $logValue = strtolower(trim((string) ($raw['log_decisions'] ?? 'on')));
            $settings['log_decisions'] = in_array($logValue, ['on', '1', 'yes', 'true'], true);
        } catch (Throwable $e) {
            // Configuration failure must always fall back to Off.
            $settings = [
                'active' => false,
                'routing_mode' => 'off',
                'test_client_ids' => [],
                'log_decisions' => true,
            ];
        }

        return $settings;
    }
}

if (!function_exists('dm1723_account_routing_disabled')) {
    /**
     * Patch 1748 account-level pause marker. The one-table design is preserved:
     * item_type=account, item_id=Client ID, pay_method_id=0 means routing is
     * explicitly disabled for that account. No row means the account follows
     * the configured module mode normally. Product/domain assignments remain
     * untouched while the account is paused.
     */
    function dm1723_account_routing_disabled(int $clientId): bool
    {
        if ($clientId <= 0) {
            return false;
        }
        try {
            if (!Capsule::schema()->hasTable('mod_domainmonger_item_paymethod_assignments')) {
                return false;
            }
            return Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->where('item_type', 'account')
                ->where('item_id', $clientId)
                ->where('pay_method_id', 0)
                ->exists();
        } catch (Throwable $e) {
            // Fail closed: if an existing account override cannot be read safely,
            // do not broaden payment routing for that client on this request.
            return true;
        }
    }
}

if (!function_exists('dm1723_client_mode')) {
    function dm1723_client_mode(int $clientId): string
    {
        if ($clientId <= 0) {
            return 'off';
        }

        $settings = dm1723_settings();
        if (empty($settings['active'])) {
            return 'off';
        }
        if (dm1723_account_routing_disabled($clientId)) {
            return 'off';
        }

        $mode = (string) ($settings['routing_mode'] ?? 'off');
        if ($mode === 'live') {
            return 'live';
        }

        $testIds = array_map('intval', (array) ($settings['test_client_ids'] ?? []));
        if (!in_array($clientId, $testIds, true)) {
            return 'off';
        }

        if ($mode === 'shadow') {
            return 'shadow';
        }
        if ($mode === 'test_live') {
            return 'live';
        }

        return 'off';
    }
}

if (!function_exists('dm1723_admin_assignment_mode')) {
    /**
     * Determine whether the assignment selector should be available for this
     * client in the Admin area. Admin Preview is intentionally admin-only and
     * never becomes a client/invoice routing mode. Patch 1748 keeps Admin
     * assignment maintenance available while an account is routing-disabled by
     * treating that account as preview-only: mappings can be maintained, but
     * invoices/Separate Invoices remain untouched until the account is enabled.
     */
    function dm1723_admin_assignment_mode(int $clientId): string
    {
        if ($clientId <= 0) {
            return 'off';
        }

        $settings = dm1723_settings();
        if (empty($settings['active'])) {
            return 'off';
        }

        $mode = (string) ($settings['routing_mode'] ?? 'off');
        if ($mode === 'admin_preview') {
            return 'preview';
        }

        if ($mode === 'live') {
            return dm1723_account_routing_disabled($clientId) ? 'preview' : 'live';
        }

        $testIds = array_map('intval', (array) ($settings['test_client_ids'] ?? []));
        if (!in_array($clientId, $testIds, true)) {
            return 'off';
        }

        if (dm1723_account_routing_disabled($clientId)) {
            return 'preview';
        }
        if ($mode === 'shadow') {
            return 'shadow';
        }
        if ($mode === 'test_live') {
            return 'live';
        }

        return 'off';
    }
}

if (!function_exists('dm1723_log')) {
    function dm1723_log(string $message): void
    {
        $settings = dm1723_settings();
        if (empty($settings['log_decisions']) || !function_exists('logActivity')) {
            return;
        }
        logActivity('DomainMonger Pay Routing 1723: ' . $message);
    }
}

if (!function_exists('dm1723_is_authorize_gateway')) {
    function dm1723_is_authorize_gateway(string $gateway): bool
    {
        return in_array(strtolower(trim($gateway)), ['authorize', 'authorizecim'], true);
    }
}

if (!function_exists('dm1723_is_paypal_vault_gateway')) {
    function dm1723_is_paypal_vault_gateway(string $gateway): bool
    {
        return strtolower(trim($gateway)) === 'paypal_ppcpv';
    }
}

if (!function_exists('dm1723_gateway_display')) {
    function dm1723_gateway_display(string $gateway): string
    {
        switch (strtolower(trim($gateway))) {
            case 'authorize':
                return 'Authorize.net';
            case 'authorizecim':
                return 'Authorize.net CIM';
            case 'paypal_ppcpv':
                return 'PayPal Payments';
            default:
                return trim($gateway) !== '' ? trim($gateway) : 'WHMCS Default';
        }
    }
}

if (!function_exists('dm1723_ensure_schema')) {
    function dm1723_ensure_schema(): bool
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }

        try {
            $schema = Capsule::schema();

            if (!$schema->hasTable('mod_domainmonger_item_paymethod_assignments')) {
                $schema->create('mod_domainmonger_item_paymethod_assignments', function ($table) {
                    $table->increments('id');
                    $table->unsignedInteger('userid');
                    $table->string('item_type', 16);
                    $table->unsignedInteger('item_id');
                    $table->unsignedInteger('pay_method_id');
                    // One-table design: while this client is in live routing,
                    // every assignment row carries the client's pre-feature
                    // Separate Invoices value. NULL means this row is currently
                    // shadow/off and the module is not managing that setting.
                    $table->tinyInteger('separate_invoices_original')->nullable();
                    $table->timestamp('created_at')->nullable();
                    $table->timestamp('updated_at')->nullable();
                    $table->unique(['item_type', 'item_id'], 'dm_item_pm_assignment_unique');
                    $table->index(['userid', 'pay_method_id'], 'dm_item_pm_client_paymethod');
                    $table->index(['userid', 'separate_invoices_original'], 'dm_item_pm_client_separate');
                });
            } elseif (!$schema->hasColumn('mod_domainmonger_item_paymethod_assignments', 'separate_invoices_original')) {
                // Defensive forward compatibility if an early test copy of the
                // one-table schema ever existed without this state column.
                $schema->table('mod_domainmonger_item_paymethod_assignments', function ($table) {
                    $table->tinyInteger('separate_invoices_original')->nullable()->after('pay_method_id');
                    $table->index(['userid', 'separate_invoices_original'], 'dm_item_pm_client_separate');
                });
            }

            $ready = true;
        } catch (Throwable $e) {
            $ready = false;
            dm1723_log('Could not prepare assignment table: ' . $e->getMessage());
        }

        return $ready;
    }
}

if (!function_exists('dm1723_token')) {
    function dm1723_token(): string
    {
        if (empty($_SESSION['dm_pm_assignment_token_1723']) || !is_string($_SESSION['dm_pm_assignment_token_1723'])) {
            try {
                $_SESSION['dm_pm_assignment_token_1723'] = bin2hex(random_bytes(24));
            } catch (Throwable $e) {
                $_SESSION['dm_pm_assignment_token_1723'] = hash('sha256', session_id() . '|' . microtime(true) . '|dm1723');
            }
        }
        return (string) $_SESSION['dm_pm_assignment_token_1723'];
    }
}

if (!function_exists('dm1723_flash')) {
    function dm1723_flash(?string $type = null, ?string $message = null): array
    {
        $key = 'dm_pm_assignment_flash_1723';
        if ($type !== null) {
            $_SESSION[$key] = ['type' => $type, 'message' => (string) $message];
            return [];
        }

        $flash = isset($_SESSION[$key]) && is_array($_SESSION[$key]) ? $_SESSION[$key] : [];
        unset($_SESSION[$key]);
        return $flash;
    }
}

if (!function_exists('dm1723_safe_clientarea_return_uri')) {
    function dm1723_safe_clientarea_return_uri(string $uri = ''): string
    {
        $uri = trim($uri);
        if ($uri === '' || preg_match('/[\r\n]/', $uri) || strpos($uri, '://') !== false || strpos($uri, '//') === 0) {
            return '/manage/clientarea.php';
        }

        $parts = parse_url($uri);
        if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
            return '/manage/clientarea.php';
        }

        $path = (string) ($parts['path'] ?? '');
        if ($path === '' || !preg_match('~(?:^|/)clientarea\.php$~i', $path)) {
            return '/manage/clientarea.php';
        }

        $safe = $path;
        if (isset($parts['query']) && (string) $parts['query'] !== '') {
            $safe .= '?' . (string) $parts['query'];
        }
        if (isset($parts['fragment']) && (string) $parts['fragment'] !== '') {
            $safe .= '#' . (string) $parts['fragment'];
        }

        return $safe;
    }
}

if (!function_exists('dm1723_current_clientarea_return_uri')) {
    function dm1723_current_clientarea_return_uri(): string
    {
        return dm1723_safe_clientarea_return_uri((string) ($_SERVER['REQUEST_URI'] ?? '/manage/clientarea.php'));
    }
}

if (!function_exists('dm1723_redirect_current')) {
    function dm1723_redirect_current(?string $preferredUri = null): void
    {
        $uri = $preferredUri !== null
            ? dm1723_safe_clientarea_return_uri($preferredUri)
            : dm1723_current_clientarea_return_uri();
        header('Location: ' . $uri, true, 303);
        exit;
    }
}

if (!function_exists('dm1723_json_response')) {
    function dm1723_json_response(bool $ok, string $message): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        }
        echo json_encode([
            'ok' => $ok,
            'message' => $message,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('dm1723_item_row')) {
    function dm1723_item_row(string $itemType, int $itemId, int $clientId)
    {
        if ($itemId <= 0 || $clientId <= 0) {
            return null;
        }

        try {
            if ($itemType === 'service') {
                return Capsule::table('tblhosting')
                    ->select(['id', 'userid', 'domain', 'paymentmethod', 'domainstatus'])
                    ->where('id', $itemId)
                    ->where('userid', $clientId)
                    ->first();
            }
            if ($itemType === 'domain') {
                return Capsule::table('tbldomains')
                    ->select(['id', 'userid', 'domain', 'paymentmethod', 'status'])
                    ->where('id', $itemId)
                    ->where('userid', $clientId)
                    ->first();
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }
}

if (!function_exists('dm1723_parse_expiry')) {
    function dm1723_parse_expiry(string $expiry): array
    {
        $value = trim($expiry);
        if ($value === '') {
            return ['valid' => false, 'expired' => false, 'display' => '—', 'sort' => 0];
        }

        $month = 0;
        $year = 0;
        if (preg_match('~^(\d{4})-(\d{2})-(\d{2})(?:\s+.*)?$~', $value, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
        } elseif (preg_match('~^(\d{1,2})\s*/\s*(\d{2}|\d{4})$~', $value, $m)) {
            $month = (int) $m[1];
            $year = (int) $m[2];
        } elseif (preg_match('~^(\d{2})(\d{2})$~', $value, $m)) {
            $month = (int) $m[1];
            $year = 2000 + (int) $m[2];
        } else {
            return ['valid' => false, 'expired' => false, 'display' => '—', 'sort' => 0];
        }

        if ($year < 100) {
            $year += 2000;
        }
        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2099) {
            return ['valid' => false, 'expired' => false, 'display' => '—', 'sort' => 0];
        }

        $sort = ($year * 100) + $month;
        $now = ((int) date('Y') * 100) + (int) date('n');
        return [
            'valid' => true,
            'expired' => $sort < $now,
            'display' => sprintf('%02d/%02d', $month, $year % 100),
            'sort' => $sort,
        ];
    }
}

if (!function_exists('dm1723_default_paymethod_id')) {
    function dm1723_default_paymethod_id(int $clientId): int
    {
        try {
            $row = Capsule::table('tblpaymethods')
                ->where('userid', $clientId)
                ->whereNull('deleted_at')
                ->where('order_preference', 0)
                ->orderBy('id', 'asc')
                ->first(['id']);
            return $row ? (int) ($row->id ?? 0) : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }
}

if (!function_exists('dm1723_paypal_account_label')) {
    function dm1723_paypal_account_label(array $row): string
    {
        $description = trim((string) ($row['description'] ?? ''));
        if ($description !== '' && preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $description, $m)) {
            return 'PayPal — ' . $m[0];
        }
        if ($description !== '') {
            return 'PayPal — ' . $description;
        }
        return 'PayPal Vault';
    }
}

if (!function_exists('dm1723_paymethods')) {
    /**
     * Return Pay Methods eligible for explicit per-item assignment.
     * Authorize cards must have a valid unexpired card expiration. PayPal Vault
     * deliberately ignores the synthetic remote-card expiry exposed by WHMCS.
     */
    function dm1723_paymethods(int $clientId): array
    {
        if ($clientId <= 0 || !function_exists('localAPI')) {
            return [];
        }

        try {
            $result = localAPI('GetPayMethods', ['clientid' => $clientId]);
        } catch (Throwable $e) {
            return [];
        }

        if (!is_array($result) || strtolower((string) ($result['result'] ?? '')) !== 'success') {
            return [];
        }

        $defaultId = dm1723_default_paymethod_id($clientId);
        $methods = [];
        foreach (($result['paymethods'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = (int) ($row['id'] ?? 0);
            $type = (string) ($row['type'] ?? '');
            $gateway = strtolower(trim((string) ($row['gateway_name'] ?? '')));
            if ($id <= 0) {
                continue;
            }

            // WHMCS GetPayMethods legitimately returns an empty gateway_name
            // for locally stored CreditCard Pay Methods. DomainMonger uses
            // Authorize.net for those local client credit cards, so treat only
            // a local CreditCard (never a RemoteCreditCard) with no gateway as
            // the native Authorize.net card path. Remote methods still require
            // an explicit supported gateway, keeping paypal_acdc excluded.
            $effectiveGateway = ($gateway === '' && $type === 'CreditCard')
                ? 'authorize'
                : $gateway;

            if (dm1723_is_authorize_gateway($effectiveGateway)) {
                if (!in_array($type, ['CreditCard', 'RemoteCreditCard'], true)) {
                    continue;
                }

                $expiry = dm1723_parse_expiry((string) ($row['expiry_date'] ?? ''));
                if (!$expiry['valid'] || $expiry['expired']) {
                    continue;
                }

                $brand = trim((string) ($row['card_type'] ?? ''));
                if ($brand === '' || strcasecmp($brand, 'PayPal') === 0) {
                    $brand = 'Credit Card';
                }
                $lastFour = preg_replace('/\D+/', '', (string) ($row['card_last_four'] ?? ''));
                $lastFour = substr($lastFour, -4);
                $label = $brand . ($lastFour !== '' ? ' •••• ' . $lastFour : '');
                $label .= ' — ' . dm1723_gateway_display($effectiveGateway) . ' — Expires ' . $expiry['display'];

                $methods[$id] = [
                    'id' => $id,
                    'gateway' => $effectiveGateway,
                    'kind' => 'card',
                    'label' => $label,
                    'description' => trim((string) ($row['description'] ?? '')),
                    'is_default' => ($id === $defaultId),
                    'expiry' => $expiry['display'],
                ];
                continue;
            }

            // The only PayPal method accepted is the actual PayPal Payments
            // wallet vault. paypal_acdc (hidden card processing) is excluded.
            if (dm1723_is_paypal_vault_gateway($gateway)) {
                $cardType = trim((string) ($row['card_type'] ?? ''));
                if (!in_array($type, ['CreditCard', 'RemoteCreditCard'], true)
                    && strcasecmp($cardType, 'PayPal') !== 0) {
                    continue;
                }

                $methods[$id] = [
                    'id' => $id,
                    'gateway' => $gateway,
                    'kind' => 'paypal',
                    'label' => dm1723_paypal_account_label($row) . ' — PayPal Payments',
                    'description' => trim((string) ($row['description'] ?? '')),
                    'is_default' => ($id === $defaultId),
                    'expiry' => '—',
                ];
            }
        }

        uasort($methods, static function (array $a, array $b): int {
            if (!empty($a['is_default']) && empty($b['is_default'])) {
                return -1;
            }
            if (empty($a['is_default']) && !empty($b['is_default'])) {
                return 1;
            }
            return strcasecmp((string) $a['label'], (string) $b['label']);
        });

        return $methods;
    }
}

if (!function_exists('dm1738_paymethod_dropdown_label')) {
    /**
     * Add the native WHMCS Pay Method Description to assignment selectors.
     * Keep the routing/logging label unchanged; this helper is display-only.
     * PayPal labels already use their native description/account identifier,
     * so avoid repeating the same text when it is already visible.
     */
    function dm1738_paymethod_dropdown_label(array $method): string
    {
        $label = trim((string) ($method['label'] ?? ''));
        if ($label === '') {
            $label = 'Pay Method #' . (int) ($method['id'] ?? 0);
        }

        $description = trim((string) ($method['description'] ?? ''));
        if ($description !== '' && stripos($label, $description) === false) {
            $label .= ' — ' . $description;
        }

        return $label;
    }
}

if (!function_exists('dm1723_client_card_expiry_map')) {
    function dm1723_client_card_expiry_map(int $clientId): array
    {
        if ($clientId <= 0 || !function_exists('localAPI')) {
            return [];
        }

        try {
            $result = localAPI('GetPayMethods', ['clientid' => $clientId]);
        } catch (Throwable $e) {
            return [];
        }

        if (!is_array($result) || strtolower((string) ($result['result'] ?? '')) !== 'success') {
            return [];
        }

        $map = [];
        foreach (($result['paymethods'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            $gateway = strtolower(trim((string) ($row['gateway_name'] ?? '')));
            $type = (string) ($row['type'] ?? '');
            $cardType = trim((string) ($row['card_type'] ?? ''));
            if ($id <= 0
                || !dm1723_is_authorize_gateway($gateway)
                || !in_array($type, ['CreditCard', 'RemoteCreditCard'], true)
                || strcasecmp($cardType, 'PayPal') === 0) {
                continue;
            }

            $expiry = dm1723_parse_expiry((string) ($row['expiry_date'] ?? ''));
            if ($expiry['valid']) {
                $map[$id] = $expiry['display'];
            }
        }
        return $map;
    }
}

if (!function_exists('dm1723_validate_paymethod')) {
    function dm1723_validate_paymethod(int $clientId, int $payMethodId): array
    {
        $methods = dm1723_paymethods($clientId);
        if (!isset($methods[$payMethodId])) {
            return [false, null, 'Select a valid saved Authorize.net credit card or PayPal Vault payment method from this account.'];
        }
        return [true, $methods[$payMethodId], ''];
    }
}

if (!function_exists('dm1723_get_assignment')) {
    function dm1723_get_assignment(int $clientId, string $itemType, int $itemId): int
    {
        if (!dm1723_ensure_schema()) {
            return 0;
        }
        try {
            $row = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->first(['pay_method_id']);
            return $row ? (int) ($row->pay_method_id ?? 0) : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }
}

if (!function_exists('dm1723_prune_orphan_assignments')) {
    function dm1723_prune_orphan_assignments(int $clientId): void
    {
        if ($clientId <= 0 || !dm1723_ensure_schema()) {
            return;
        }
        try {
            $rows = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->whereIn('item_type', ['service', 'domain'])
                ->orderBy('id', 'asc')
                ->get(['id', 'item_type', 'item_id', 'separate_invoices_original']);

            $remaining = count($rows);
            foreach ($rows as $row) {
                $itemType = (string) ($row->item_type ?? '');
                $itemId = (int) ($row->item_id ?? 0);
                $orphan = !in_array($itemType, ['service', 'domain'], true)
                    || $itemId <= 0
                    || !dm1723_item_row($itemType, $itemId, $clientId);
                if (!$orphan) {
                    continue;
                }

                // If this is the last remembered row and live routing previously
                // changed Separate Invoices, restore that native setting before
                // removing our only copy of its original value.
                if ($remaining <= 1 && $row->separate_invoices_original !== null) {
                    $original = ((int) $row->separate_invoices_original) === 1;
                    if (!dm1723_restore_separate_invoices($clientId, $original)) {
                        continue;
                    }
                }

                Capsule::table('mod_domainmonger_item_paymethod_assignments')
                    ->where('id', (int) ($row->id ?? 0))
                    ->delete();
                $remaining--;
            }
        } catch (Throwable $e) {
            // Defensive cleanup only; never interrupt billing for this.
        }
    }
}

if (!function_exists('dm1723_client_assignment_count')) {
    function dm1723_client_assignment_count(int $clientId): int
    {
        if ($clientId <= 0 || !dm1723_ensure_schema()) {
            return 0;
        }
        dm1723_prune_orphan_assignments($clientId);
        try {
            return (int) Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->whereIn('item_type', ['service', 'domain'])
                ->count();
        } catch (Throwable $e) {
            return 0;
        }
    }
}

if (!function_exists('dm1723_current_separate_invoices')) {
    function dm1723_current_separate_invoices(int $clientId): ?bool
    {
        if ($clientId <= 0) {
            return null;
        }
        try {
            $client = Capsule::table('tblclients')->where('id', $clientId)->first(['separateinvoices']);
            return $client ? (bool) ($client->separateinvoices ?? false) : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('dm1723_set_separate_invoices')) {
    function dm1723_set_separate_invoices(int $clientId, bool $enabled): bool
    {
        if ($clientId <= 0 || !function_exists('localAPI')) {
            return false;
        }
        try {
            $result = localAPI('UpdateClient', [
                'clientid' => $clientId,
                'separateinvoices' => $enabled ? true : false,
            ]);
            if (!is_array($result) || strtolower((string) ($result['result'] ?? '')) !== 'success') {
                return false;
            }
            $confirmed = dm1723_current_separate_invoices($clientId);
            return $confirmed !== null && $confirmed === $enabled;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('dm1723_client_original_separate_state')) {
    function dm1723_client_original_separate_state(int $clientId): ?bool
    {
        if ($clientId <= 0 || !dm1723_ensure_schema()) {
            return null;
        }
        try {
            $row = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->whereIn('item_type', ['service', 'domain'])
                ->whereNotNull('separate_invoices_original')
                ->orderBy('id', 'asc')
                ->first(['separate_invoices_original']);
            if (!$row) {
                return null;
            }
            return (bool) ((int) ($row->separate_invoices_original ?? 0));
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('dm1723_stamp_original_separate_state')) {
    function dm1723_stamp_original_separate_state(int $clientId, bool $original): bool
    {
        if ($clientId <= 0 || !dm1723_ensure_schema()) {
            return false;
        }
        try {
            Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->whereIn('item_type', ['service', 'domain'])
                ->update([
                    'separate_invoices_original' => $original ? 1 : 0,
                    'updated_at' => dm1723_now(),
                ]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('dm1723_clear_original_separate_state')) {
    function dm1723_clear_original_separate_state(int $clientId): void
    {
        if ($clientId <= 0 || !dm1723_ensure_schema()) {
            return;
        }
        try {
            Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->whereIn('item_type', ['service', 'domain'])
                ->update([
                    'separate_invoices_original' => null,
                    'updated_at' => dm1723_now(),
                ]);
        } catch (Throwable $e) {
            // Leaving the remembered state intact is safer than guessing.
        }
    }
}

if (!function_exists('dm1723_enable_separate_invoices')) {
    /**
     * Ensure native Separate Invoices is enabled for a client that already has
     * assignment rows. The original value is remembered in those same rows.
     */
    function dm1723_enable_separate_invoices(int $clientId): bool
    {
        if ($clientId <= 0 || dm1723_client_mode($clientId) !== 'live' || !dm1723_ensure_schema()) {
            return false;
        }
        if (dm1723_client_assignment_count($clientId) <= 0) {
            return true;
        }

        $remembered = dm1723_client_original_separate_state($clientId);
        $current = dm1723_current_separate_invoices($clientId);
        if ($current === null) {
            return false;
        }

        $newlyRemembered = false;
        if ($remembered === null) {
            $remembered = $current;
            if (!dm1723_stamp_original_separate_state($clientId, $remembered)) {
                return false;
            }
            $newlyRemembered = true;
        }

        if (!$current && !dm1723_set_separate_invoices($clientId, true)) {
            if ($newlyRemembered) {
                dm1723_clear_original_separate_state($clientId);
            }
            return false;
        }

        return true;
    }
}

if (!function_exists('dm1723_restore_separate_invoices')) {
    /**
     * Restore the client setting captured before live routing began. This does
     * not delete assignment rows; it only returns WHMCS configuration to the
     * state it had before this feature started managing it.
     */
    function dm1723_restore_separate_invoices(int $clientId, ?bool $originalOverride = null): bool
    {
        if ($clientId <= 0 || !dm1723_ensure_schema()) {
            return false;
        }

        try {
            $target = $originalOverride;
            if ($target === null) {
                $target = dm1723_client_original_separate_state($clientId);
            }
            if ($target === null) {
                return true;
            }

            $current = dm1723_current_separate_invoices($clientId);
            if ($current === null) {
                return false;
            }
            if ($current !== $target && !dm1723_set_separate_invoices($clientId, $target)) {
                return false;
            }

            dm1723_clear_original_separate_state($clientId);
            return true;
        } catch (Throwable $e) {
            dm1723_log('Could not restore Separate Invoices for Client #' . $clientId . ': ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('dm1723_reconcile_managed_clients')) {
    function dm1723_reconcile_managed_clients(): void
    {
        $settings = dm1723_settings();

        // If the addon is inactive/Off, do not create the custom table just
        // because the hook file exists. Only reconcile if a prior activation
        // already created assignment storage.
        if (empty($settings['active'])) {
            try {
                if (!Capsule::schema()->hasTable('mod_domainmonger_item_paymethod_assignments')) {
                    return;
                }
            } catch (Throwable $e) {
                return;
            }
        }

        if (!dm1723_ensure_schema()) {
            return;
        }

        try {
            $clients = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->whereIn('item_type', ['service', 'domain'])
                ->select('userid')
                ->distinct()
                ->pluck('userid')
                ->all();

            foreach ($clients ?: [] as $clientId) {
                $clientId = (int) $clientId;
                if ($clientId <= 0) {
                    continue;
                }

                dm1723_prune_orphan_assignments($clientId);
                $count = dm1723_client_assignment_count($clientId);
                if ($count <= 0) {
                    continue;
                }

                if (dm1723_client_mode($clientId) === 'live') {
                    if (!dm1723_enable_separate_invoices($clientId)) {
                        dm1723_log('Live Client #' . $clientId . ' has assignments but Separate Invoices could not be confirmed. Conflict fallback remains active.');
                    }
                } else {
                    // Shadow, Off, or addon deactivated: return the native
                    // setting to its pre-live value while preserving mappings.
                    dm1723_restore_separate_invoices($clientId);
                }
            }
        } catch (Throwable $e) {
            dm1723_log('Could not reconcile Separate Invoices state: ' . $e->getMessage());
        }
    }
}

if (!function_exists('dm1723_save_assignment')) {
    function dm1723_save_assignment(int $clientId, string $itemType, int $itemId, int $payMethodId, ?string $modeOverride = null): array
    {
        if (!in_array($itemType, ['service', 'domain'], true) || $clientId <= 0 || $itemId <= 0) {
            return [false, 'The requested product/domain could not be identified.'];
        }

        $mode = $modeOverride !== null ? $modeOverride : dm1723_client_mode($clientId);
        if (!in_array($mode, ['preview', 'shadow', 'live'], true)) {
            return [false, 'Per-item Payment Method assignment is not enabled for this account.'];
        }
        if (!dm1723_ensure_schema()) {
            return [false, 'Payment Method assignment storage is not available.'];
        }
        if (!dm1723_item_row($itemType, $itemId, $clientId)) {
            return [false, 'The requested product/domain could not be found for this account.'];
        }

        try {
            $existing = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->first(['id', 'userid', 'separate_invoices_original']);

            $clientCountBefore = (int) Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->whereIn('item_type', ['service', 'domain'])
                ->count();

            // "Use Account Default" means no override row at all.
            if ($payMethodId <= 0) {
                if (!$existing || (int) ($existing->userid ?? 0) !== $clientId) {
                    return [true, 'This item is already using normal WHMCS default/existing payment behavior.'];
                }

                $original = $existing->separate_invoices_original !== null
                    ? (((int) $existing->separate_invoices_original) === 1)
                    : dm1723_client_original_separate_state($clientId);

                if ($clientCountBefore <= 1 && $original !== null) {
                    if (!dm1723_restore_separate_invoices($clientId, $original)) {
                        return [false, 'The assignment was not removed because the prior WHMCS Separate Invoices setting could not be safely restored.'];
                    }
                }

                Capsule::table('mod_domainmonger_item_paymethod_assignments')
                    ->where('id', (int) $existing->id)
                    ->delete();

                dm1723_log(($mode === 'preview' ? '[ADMIN PREVIEW] ' : ($mode === 'shadow' ? '[SHADOW] ' : '')) . ucfirst($itemType) . ' #' . $itemId . ' for Client #' . $clientId . ' returned to normal WHMCS default/existing payment behavior.');
                if ($mode === 'live' && function_exists('dm1732_reconcile_unpaid_invoices_for_item')) {
                    dm1732_reconcile_unpaid_invoices_for_item($clientId, $itemType, $itemId);
                }
                return [true, 'This item will use the normal WHMCS default/existing payment behavior.'];
            }

            [$valid, $method, $error] = dm1723_validate_paymethod($clientId, $payMethodId);
            if (!$valid || !$method) {
                return [false, $error];
            }

            $originalForLive = null;
            $changedSeparate = false;

            if (in_array($mode, ['preview', 'shadow'], true)) {
                // Preview/Shadow must never leave a prior live-mode Separate Invoices
                // change active.
                if (dm1723_client_original_separate_state($clientId) !== null
                    && !dm1723_restore_separate_invoices($clientId)) {
                    return [false, ($mode === 'preview' ? 'Admin Preview' : 'Shadow') . ' mode could not safely restore the prior WHMCS Separate Invoices setting.'];
                }
            } else {
                $remembered = dm1723_client_original_separate_state($clientId);
                $current = dm1723_current_separate_invoices($clientId);
                if ($current === null) {
                    return [false, 'WHMCS Separate Invoices could not be read, so the specific Payment Method assignment was not saved.'];
                }

                $originalForLive = $remembered ?? $current;

                // Existing rows need the same remembered original value before
                // we change the native setting.
                if ($remembered === null && $clientCountBefore > 0) {
                    if (!dm1723_stamp_original_separate_state($clientId, $originalForLive)) {
                        return [false, 'The prior WHMCS Separate Invoices setting could not be recorded safely.'];
                    }
                }

                if (!$current) {
                    if (!dm1723_set_separate_invoices($clientId, true)) {
                        if ($remembered === null && $clientCountBefore > 0) {
                            dm1723_clear_original_separate_state($clientId);
                        }
                        return [false, 'WHMCS Separate Invoices could not be enabled, so the specific Payment Method assignment was not saved.'];
                    }
                    $changedSeparate = true;
                }
            }

            // If an item was transferred between clients and still has an old
            // custom row, preserve the old client's remembered state before
            // moving the row to its current owner.
            $oldClientId = $existing ? (int) ($existing->userid ?? 0) : 0;
            if ($existing && $oldClientId > 0 && $oldClientId !== $clientId) {
                $oldCount = (int) Capsule::table('mod_domainmonger_item_paymethod_assignments')
                    ->where('userid', $oldClientId)
                    ->whereIn('item_type', ['service', 'domain'])
                    ->count();
                $oldOriginal = $existing->separate_invoices_original !== null
                    ? (((int) $existing->separate_invoices_original) === 1)
                    : dm1723_client_original_separate_state($oldClientId);
                if ($oldCount <= 1 && $oldOriginal !== null
                    && !dm1723_restore_separate_invoices($oldClientId, $oldOriginal)) {
                    if ($mode === 'live' && $changedSeparate && $clientCountBefore === 0 && $originalForLive !== null) {
                        dm1723_set_separate_invoices($clientId, $originalForLive);
                    }
                    return [false, 'The previous owner’s WHMCS Separate Invoices setting could not be safely restored.'];
                }
            }

            $data = [
                'userid' => $clientId,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'pay_method_id' => $payMethodId,
                'separate_invoices_original' => $mode === 'live' ? ($originalForLive ? 1 : 0) : null,
                'updated_at' => dm1723_now(),
            ];

            try {
                if ($existing) {
                    Capsule::table('mod_domainmonger_item_paymethod_assignments')
                        ->where('id', (int) $existing->id)
                        ->update($data);
                } else {
                    $data['created_at'] = dm1723_now();
                    Capsule::table('mod_domainmonger_item_paymethod_assignments')->insert($data);
                }

                if ($mode === 'live' && $originalForLive !== null) {
                    dm1723_stamp_original_separate_state($clientId, $originalForLive);
                }
            } catch (Throwable $saveError) {
                // If this was the first live assignment and we changed the
                // native client setting before the row could be saved, put it
                // back immediately.
                if ($mode === 'live' && $clientCountBefore === 0 && $originalForLive !== null) {
                    dm1723_set_separate_invoices($clientId, $originalForLive);
                }
                throw $saveError;
            }

            $prefix = $mode === 'preview' ? '[ADMIN PREVIEW] ' : ($mode === 'shadow' ? '[SHADOW] ' : '');
            dm1723_log($prefix . ucfirst($itemType) . ' #' . $itemId . ' for Client #' . $clientId . ' assigned Pay Method #' . $payMethodId . ' (' . $method['label'] . ').');
            if ($mode === 'preview') {
                $message = 'Admin Preview assignment saved. Client UI, invoices, and WHMCS Separate Invoices remain unchanged.';
            } elseif ($mode === 'shadow') {
                $message = 'Shadow assignment saved. WHMCS invoices will not be changed while Shadow mode is active.';
            } else {
                if (function_exists('dm1732_reconcile_unpaid_invoices_for_item')) {
                    dm1732_reconcile_unpaid_invoices_for_item($clientId, $itemType, $itemId);
                }
                $message = 'Future and matching Unpaid invoices for this item will use ' . (string) $method['label'] . '.';
            }
            return [true, $message];
        } catch (Throwable $e) {
            dm1723_log('Could not save ' . $itemType . ' #' . $itemId . ' assignment for Client #' . $clientId . ': ' . $e->getMessage());
            return [false, 'The Payment Method assignment could not be saved.'];
        }
    }
}

if (!function_exists('dm1723_cleanup_stale_assignment')) {
    function dm1723_cleanup_stale_assignment(int $clientId, string $itemType, int $itemId): int
    {
        $assignedId = dm1723_get_assignment($clientId, $itemType, $itemId);
        if ($assignedId <= 0) {
            return 0;
        }
        [$valid] = dm1723_validate_paymethod($clientId, $assignedId);
        if ($valid) {
            return $assignedId;
        }

        try {
            $row = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->first(['id', 'separate_invoices_original']);
            if (!$row) {
                return 0;
            }

            $count = (int) Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->whereIn('item_type', ['service', 'domain'])
                ->count();

            if ($count <= 1 && $row->separate_invoices_original !== null) {
                $original = ((int) $row->separate_invoices_original) === 1;
                if (!dm1723_restore_separate_invoices($clientId, $original)) {
                    dm1723_log('Stale assignment for ' . $itemType . ' #' . $itemId . ' was retained because Separate Invoices could not be restored safely.');
                    return 0;
                }
            }

            Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('id', (int) $row->id)
                ->delete();
        } catch (Throwable $e) {
            return 0;
        }

        dm1723_log('Removed stale Pay Method assignment from ' . $itemType . ' #' . $itemId . ' for Client #' . $clientId . '; normal WHMCS behavior will be used.');
        return 0;
    }
}

if (!function_exists('dm1723_render_assignment_panel')) {
    function dm1723_render_assignment_panel(int $clientId, string $itemType, int $itemId): string
    {
        $mode = dm1723_client_mode($clientId);
        if (!in_array($mode, ['shadow', 'live'], true)) {
            return '';
        }

        $item = dm1723_item_row($itemType, $itemId, $clientId);
        if (!$item) {
            return '';
        }

        $methods = dm1723_paymethods($clientId);
        $assignedId = dm1723_cleanup_stale_assignment($clientId, $itemType, $itemId);
        $flash = dm1723_flash();

        $currentGateway = trim((string) ($item->paymentmethod ?? ''));
        $options = '<option value="0"' . ($assignedId === 0 ? ' selected' : '') . '>Use Account Default / Existing WHMCS Setting</option>';
        foreach ($methods as $method) {
            $id = (int) $method['id'];
            $label = dm1738_paymethod_dropdown_label($method);
            if (!empty($method['is_default'])) {
                $label .= ' (Account Default)';
            }
            $options .= '<option value="' . $id . '"' . ($assignedId === $id ? ' selected' : '') . '>' . dm1723_escape($label) . '</option>';
        }

        $flashHtml = '';
        if (!empty($flash['message'])) {
            $success = (($flash['type'] ?? '') === 'success');
            $flashHtml = '<div class="alert alert-' . ($success ? 'success' : 'warning') . ' dm-pmassign-alert">'
                . dm1723_escape((string) $flash['message']) . '</div>';
        }

        $modeHtml = '';
        if ($mode === 'shadow') {
            $modeHtml = '<div class="alert alert-info dm-pmassign-mode"><strong>Shadow Test:</strong> Selections are saved and routing decisions are logged, but invoices and WHMCS Separate Invoices are not changed.</div>';
        } elseif ((string) (dm1723_settings()['routing_mode'] ?? '') === 'test_live') {
            $modeHtml = '<div class="alert alert-warning dm-pmassign-mode"><strong>Test Live:</strong> Payment routing is active only for designated test clients.</div>';
        }

        $empty = empty($methods)
            ? '<div class="alert alert-warning dm-pmassign-empty">No eligible saved Authorize.net cards or PayPal Vault methods are available for this account.</div>'
            : '';

        $itemLabel = $itemType === 'domain' ? 'domain' : 'product/service';
        $liveFallbackNote = $mode === 'live'
            ? '<p class="dm-pmassign-live-note"><strong>Fallback:</strong> If the selected Payment Method is unavailable or cannot be applied to an invoice, normal WHMCS/account-default payment behavior will be used.</p>'
            : '';
        $token = dm1723_token();
        $returnUri = dm1723_current_clientarea_return_uri();
        return '<style id="dm-pmassign-style-1723">'
            . '.dm-pmassign-card{margin-top:18px;border:1px solid #d8dee6;border-radius:4px;overflow:hidden;background:#fff;}'
            . '.dm-pmassign-card .dm-pmassign-head{background:#163a5f;color:#fff;font-weight:700;padding:10px 14px;font-size:16px;}'
            . '.dm-pmassign-card .dm-pmassign-body{padding:14px;background:#fff;}'
            . '.dm-pmassign-card .dm-pmassign-row{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;}'
            . '.dm-pmassign-card .dm-pmassign-field{flex:1 1 420px;min-width:260px;}'
            . '.dm-pmassign-card label{display:block;margin:0 0 6px;color:#163a5f;font-weight:700;text-align:left;}'
            . '.dm-pmassign-card select.form-control{width:100%;}'
            . '.dm-pmassign-card .btn-primary{background:#f58220!important;border-color:#f58220!important;color:#fff!important;}'
            . '.dm-pmassign-card .btn-primary:hover,.dm-pmassign-card .btn-primary:focus{background:#163a5f!important;border-color:#163a5f!important;}'
            . '.dm-pmassign-note{margin:9px 0 0;color:#5e6875;font-size:12px;text-align:left;}'
            . '.dm-pmassign-live-note{margin:10px 0 0;padding:9px 11px;background:#fcf8e3;border:1px solid #faebcc;border-radius:3px;color:#66512c;font-size:12px;text-align:left;}'
            . '.dm-pmassign-alert,.dm-pmassign-empty,.dm-pmassign-mode{margin:0 0 12px;}'
            . '</style>'
            . '<div class="card dm-pmassign-card"><div class="dm-pmassign-head">Payment Method</div><div class="dm-pmassign-body">'
            . $flashHtml . $modeHtml . $empty
            . '<div class="dm-pmassign-control" data-item-type="' . dm1723_escape($itemType) . '" data-item-id="' . $itemId . '" data-token="' . dm1723_escape($token) . '" data-saved-paymethod-id="' . $assignedId . '">'
            . '<div class="dm-pmassign-row"><div class="dm-pmassign-field">'
            . '<label for="dm-pmassign-select-' . dm1723_escape($itemType) . '-' . $itemId . '">Payment Method</label>'
            . '<select class="form-control dm-pmassign-select" id="dm-pmassign-select-' . dm1723_escape($itemType) . '-' . $itemId . '">'
            . $options . '</select></div><div><button type="button" class="btn btn-primary dm-pmassign-save" disabled>Save Payment Method</button></div></div>'
            . '<p class="dm-pmassign-note">Current native WHMCS gateway: ' . dm1723_escape(dm1723_gateway_display($currentGateway)) . '. '
            . 'Use Account Default / Existing WHMCS Setting removes the explicit override. A specific Authorize.net card or PayPal Vault selection applies to future invoices. '
            . 'If a grouped invoice contains conflicting/default assignments, the override is abandoned and normal WHMCS payment behavior wins.</p>'
            . $liveFallbackNote
            . '</div>'
            . '<script>(function(){if(window.dmPmAssignAjax1731){return;}window.dmPmAssignAjax1731=true;function sync(c){if(!c){return;}var s=c.querySelector(".dm-pmassign-select"),b=c.querySelector(".dm-pmassign-save");if(!s||!b){return;}var saved=String(c.getAttribute("data-saved-paymethod-id")||"0"),current=String(s.value||"0");b.disabled=(current===saved);}document.querySelectorAll(".dm-pmassign-control").forEach(sync);document.addEventListener("change",function(e){if(!e.target.classList||!e.target.classList.contains("dm-pmassign-select")){return;}sync(e.target.closest(".dm-pmassign-control"));},true);document.addEventListener("click",function(e){var b=e.target.closest&&e.target.closest(".dm-pmassign-save");if(!b||b.disabled){return;}e.preventDefault();e.stopPropagation();var c=b.closest(".dm-pmassign-control");if(!c){return;}var s=c.querySelector(".dm-pmassign-select");if(!s){return;}var fd=new FormData();fd.append("dm_pmassign_action_1723","save");fd.append("dm_pmassign_ajax_1730","1");fd.append("dm_pmassign_item_type_1723",c.getAttribute("data-item-type")||"");fd.append("dm_pmassign_item_id_1723",c.getAttribute("data-item-id")||"0");fd.append("dm_pmassign_paymethod_id_1723",s.value||"0");fd.append("dm_pmassign_token_1723",c.getAttribute("data-token")||"");var original=b.innerHTML;b.disabled=true;b.innerHTML="Saving...";fetch(window.location.href,{method:"POST",body:fd,credentials:"same-origin",headers:{"X-Requested-With":"XMLHttpRequest"}}).then(function(r){return r.json();}).then(function(){window.location.reload();}).catch(function(){b.innerHTML=original;sync(c);window.location.reload();});},true);})();</script>'
            . '</div></div>';
    }
}

if (!function_exists('dm1723_admin_assignment_field')) {
    function dm1723_admin_assignment_field(int $clientId, string $itemType, int $itemId): string
    {
        $mode = dm1723_admin_assignment_mode($clientId);
        if (!in_array($mode, ['preview', 'shadow', 'live'], true)) {
            return '';
        }

        $item = dm1723_item_row($itemType, $itemId, $clientId);
        if (!$item || !dm1723_ensure_schema()) {
            return '';
        }

        $methods = dm1723_paymethods($clientId);
        $assignedId = dm1723_cleanup_stale_assignment($clientId, $itemType, $itemId);
        $fieldName = $itemType === 'domain' ? 'dm_pmassign_admin_domain_1726' : 'dm_pmassign_admin_service_1726';
        $markerName = $itemType === 'domain' ? 'dm_pmassign_admin_domain_present_1726' : 'dm_pmassign_admin_service_present_1726';

        $options = '<option value="0"' . ($assignedId === 0 ? ' selected' : '') . '>Use Account Default / Existing WHMCS Setting</option>';
        foreach ($methods as $method) {
            $id = (int) ($method['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $label = dm1738_paymethod_dropdown_label($method);
            if (!empty($method['is_default'])) {
                $label .= ' (Account Default)';
            }
            $options .= '<option value="' . $id . '"' . ($assignedId === $id ? ' selected' : '') . '>' . dm1723_escape($label) . '</option>';
        }

        if (dm1723_account_routing_disabled($clientId)) {
            $status = '<div style="margin-top:6px;color:#8a6d3b;"><strong>Payment Routing Disabled for this account:</strong> Assignments can be maintained in Admin, but client routing, invoices, and Separate Invoices are not changed until the account is enabled.</div>';
        } elseif ($mode === 'preview') {
            $status = '<div style="margin-top:6px;color:#31708f;"><strong>Admin Preview:</strong> Saved here only. Client UI, invoices, and Separate Invoices are not changed.</div>';
        } elseif ((string) (dm1723_settings()['routing_mode'] ?? '') === 'shadow') {
            $status = '<div style="margin-top:6px;color:#31708f;"><strong>Shadow Test:</strong> Saved and logged for this test client; invoices are not changed.</div>';
        } elseif ((string) (dm1723_settings()['routing_mode'] ?? '') === 'test_live') {
            $status = '<div style="margin-top:6px;color:#8a6d3b;"><strong>Test Live:</strong> Routing is active for this designated test client.</div>';
        } else {
            $status = '<div style="margin-top:6px;color:#3c763d;"><strong>Live:</strong> Future eligible invoices use this explicit Payment Method when the assignment is unambiguous.</div>';
        }

        $currentGateway = dm1723_gateway_display((string) ($item->paymentmethod ?? ''));
        $empty = empty($methods)
            ? '<div style="margin-top:6px;color:#a94442;">No eligible saved Authorize.net cards or PayPal Vault methods are available for this client.</div>'
            : '';

        return '<input type="hidden" name="' . dm1723_escape($markerName) . '" value="1">'
            . '<select name="' . dm1723_escape($fieldName) . '" class="form-control input-500" style="max-width:620px;">' . $options . '</select>'
            . '<div style="margin-top:6px;color:#666;">Native WHMCS gateway: ' . dm1723_escape($currentGateway) . '. Selecting Account Default removes the custom override.</div>'
            . $status . $empty;
    }
}

if (!function_exists('dm1723_admin_save_assignment')) {
    function dm1723_admin_save_assignment(string $itemType, int $itemId, string $fieldName, string $markerName): void
    {
        if ($itemId <= 0 || empty($_REQUEST[$markerName])) {
            return;
        }

        $row = null;
        try {
            if ($itemType === 'service') {
                $row = Capsule::table('tblhosting')->where('id', $itemId)->first(['userid']);
            } elseif ($itemType === 'domain') {
                $row = Capsule::table('tbldomains')->where('id', $itemId)->first(['userid']);
            }
        } catch (Throwable $e) {
            dm1723_log('[ADMIN] Could not identify owner for ' . $itemType . ' #' . $itemId . ': ' . $e->getMessage());
            return;
        }

        $clientId = $row ? (int) ($row->userid ?? 0) : 0;
        $mode = dm1723_admin_assignment_mode($clientId);
        if (!in_array($mode, ['preview', 'shadow', 'live'], true)) {
            return;
        }

        $payMethodId = (int) ($_REQUEST[$fieldName] ?? 0);
        [$ok, $message] = dm1723_save_assignment($clientId, $itemType, $itemId, $payMethodId, $mode);
        dm1723_log('[ADMIN] ' . ($ok ? 'Saved' : 'Rejected') . ' ' . $itemType . ' #' . $itemId . ' for Client #' . $clientId . ': ' . $message);
    }
}

if (!function_exists('dm1723_invoice_item_assignment')) {
    function dm1723_invoice_item_assignment(int $clientId, object $item): array
    {
        $type = strtolower(trim((string) ($item->type ?? '')));
        $relId = (int) ($item->relid ?? 0);
        if ($relId <= 0) {
            return [false, 0, '', ''];
        }

        if ($type === 'hosting' || $type === 'service' || strpos($type, 'hosting') !== false) {
            $service = dm1723_item_row('service', $relId, $clientId);
            if (!$service) {
                return [false, 0, '', ''];
            }
            $assignedId = dm1723_cleanup_stale_assignment($clientId, 'service', $relId);
            return [true, $assignedId, 'service', (string) ($service->paymentmethod ?? '')];
        }

        if ($type === 'addon' || strpos($type, 'addon') !== false) {
            try {
                $addon = Capsule::table('tblhostingaddons')->where('id', $relId)->first(['hostingid']);
            } catch (Throwable $e) {
                $addon = null;
            }
            $hostingId = $addon ? (int) ($addon->hostingid ?? 0) : 0;
            if ($hostingId > 0) {
                $service = dm1723_item_row('service', $hostingId, $clientId);
                if ($service) {
                    $assignedId = dm1723_cleanup_stale_assignment($clientId, 'service', $hostingId);
                    return [true, $assignedId, 'service-addon', (string) ($service->paymentmethod ?? '')];
                }
            }
            return [false, 0, '', ''];
        }

        if (strpos($type, 'domain') !== false) {
            $domain = dm1723_item_row('domain', $relId, $clientId);
            if (!$domain) {
                return [false, 0, '', ''];
            }
            $assignedId = dm1723_cleanup_stale_assignment($clientId, 'domain', $relId);
            return [true, $assignedId, 'domain', (string) ($domain->paymentmethod ?? '')];
        }

        return [false, 0, '', ''];
    }
}

if (!function_exists('dm1723_evaluate_invoice')) {
    function dm1723_evaluate_invoice(int $invoiceId): array
    {
        if ($invoiceId <= 0 || !dm1723_ensure_schema()) {
            return ['action' => 'none', 'reason' => 'invalid'];
        }

        $invoice = Capsule::table('tblinvoices')
            ->where('id', $invoiceId)
            ->first(['id', 'userid', 'paymentmethod', 'paymethodid']);
        if (!$invoice) {
            return ['action' => 'none', 'reason' => 'invoice-not-found'];
        }

        $clientId = (int) ($invoice->userid ?? 0);
        if ($clientId <= 0 || dm1723_client_assignment_count($clientId) <= 0) {
            return ['action' => 'none', 'reason' => 'no-assignments', 'clientid' => $clientId];
        }

        $items = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $invoiceId)
            ->get(['id', 'type', 'relid', 'amount']);

        $assigned = [];
        $routable = 0;
        $defaults = 0;
        $otherBillable = 0;
        foreach ($items as $item) {
            [$isRoutable, $payMethodId] = dm1723_invoice_item_assignment($clientId, $item);
            if (!$isRoutable) {
                if (abs((float) ($item->amount ?? 0)) > 0.00001) {
                    $otherBillable++;
                }
                continue;
            }

            $routable++;
            if ($payMethodId > 0) {
                $assigned[$payMethodId] = true;
            } else {
                $defaults++;
            }
        }

        if (!$assigned) {
            return ['action' => 'default', 'reason' => 'no-explicit-assignment', 'clientid' => $clientId];
        }

        // A WHMCS invoice has one gateway and one Pay Method ID. Different
        // explicit methods, or a mixture of explicit + default/other billable
        // items, must fall back to native WHMCS behavior rather than guessing.
        if (count($assigned) !== 1 || $defaults > 0 || $otherBillable > 0 || $routable <= 0) {
            return [
                'action' => 'fallback',
                'reason' => 'mixed-or-conflicting-assignments',
                'clientid' => $clientId,
                'assigned_ids' => array_map('intval', array_keys($assigned)),
                'default_items' => $defaults,
                'other_items' => $otherBillable,
            ];
        }

        $payMethodId = (int) array_key_first($assigned);
        [$valid, $method, $error] = dm1723_validate_paymethod($clientId, $payMethodId);
        if (!$valid || !$method) {
            return ['action' => 'fallback', 'reason' => 'paymethod-invalid', 'clientid' => $clientId, 'error' => $error];
        }

        return [
            'action' => 'route',
            'reason' => 'single-explicit-assignment',
            'clientid' => $clientId,
            'paymethodid' => $payMethodId,
            'gateway' => (string) $method['gateway'],
            'label' => (string) $method['label'],
            'original_gateway' => (string) ($invoice->paymentmethod ?? ''),
            'original_paymethodid' => (int) ($invoice->paymethodid ?? 0),
        ];
    }
}

if (!function_exists('dm1723_apply_invoice_decision')) {
    function dm1723_apply_invoice_decision(int $invoiceId, array $decision, string $mode): void
    {
        $action = (string) ($decision['action'] ?? 'none');
        $clientId = (int) ($decision['clientid'] ?? 0);

        if ($mode === 'shadow') {
            if ($action === 'route') {
                dm1723_log('[SHADOW] Invoice #' . $invoiceId . ' for Client #' . $clientId . ' would route to Pay Method #' . (int) $decision['paymethodid'] . ' (' . (string) $decision['label'] . '). No invoice fields were changed.');
            } elseif ($action === 'fallback') {
                dm1723_log('[SHADOW] Invoice #' . $invoiceId . ' for Client #' . $clientId . ' would fall back to normal WHMCS behavior because of ' . (string) ($decision['reason'] ?? 'a routing conflict') . '. No invoice fields were changed.');
            }
            return;
        }

        if ($mode !== 'live') {
            return;
        }

        if ($action === 'fallback') {
            dm1723_log('Invoice #' . $invoiceId . ' for Client #' . $clientId . ' kept normal WHMCS/default payment behavior because the grouped invoice has conflicting/default assignments.');
            return;
        }
        if ($action !== 'route') {
            return;
        }

        $model = null;
        try {
            $model = \WHMCS\Billing\Invoice::find($invoiceId);
            if (!$model) {
                return;
            }

            $targetGateway = (string) $decision['gateway'];
            $targetPayMethodId = (int) $decision['paymethodid'];

            // Set gateway first, then the native Pay Method ID. WHMCS performs
            // its own ownership/gateway validation when setPayMethodId() runs.
            $model->setPaymentMethod($targetGateway);
            $model->setPayMethodId($targetPayMethodId);
            $model->save();

            dm1723_log('Invoice #' . $invoiceId . ' for Client #' . $clientId . ' routed to Pay Method #' . $targetPayMethodId . ' (' . (string) $decision['label'] . ').');
        } catch (Throwable $e) {
            // If gateway assignment succeeded but Pay Method assignment failed,
            // make a best-effort rollback to the invoice state seen before this
            // hook. Never leave a half-applied gateway switch intentionally.
            try {
                if ($model) {
                    $originalGateway = (string) ($decision['original_gateway'] ?? '');
                    $originalPayMethodId = (int) ($decision['original_paymethodid'] ?? 0);
                    if ($originalGateway !== '') {
                        $model->setPaymentMethod($originalGateway);
                    }
                    if ($originalPayMethodId > 0) {
                        $model->setPayMethodId($originalPayMethodId);
                    } else {
                        $model->clearPayMethodId();
                    }
                    $model->save();
                }
            } catch (Throwable $rollbackError) {
                dm1723_log('Invoice #' . $invoiceId . ' routing failed and rollback also failed: ' . $rollbackError->getMessage());
            }
            dm1723_log('Invoice #' . $invoiceId . ' routing failed; normal WHMCS state was preserved/restored when possible. Error: ' . $e->getMessage());
        }
    }
}


if (!function_exists('dm1732_invoice_manual_marker')) {
    function dm1732_invoice_manual_marker(int $invoiceId, int $clientId): ?int
    {
        if ($invoiceId <= 0 || $clientId <= 0 || !dm1723_ensure_schema()) {
            return null;
        }
        try {
            $row = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->where('item_type', 'invoice')
                ->where('item_id', $invoiceId)
                ->first(['pay_method_id']);
            return $row ? (int) ($row->pay_method_id ?? 0) : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('dm1732_set_invoice_manual_marker')) {
    function dm1732_set_invoice_manual_marker(int $invoiceId, int $clientId, int $payMethodId): void
    {
        if ($invoiceId <= 0 || $clientId <= 0 || !dm1723_ensure_schema()) {
            return;
        }
        try {
            $existing = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('item_type', 'invoice')
                ->where('item_id', $invoiceId)
                ->first(['id']);
            $data = [
                'userid' => $clientId,
                'item_type' => 'invoice',
                'item_id' => $invoiceId,
                'pay_method_id' => max(0, $payMethodId),
                'separate_invoices_original' => null,
                'updated_at' => dm1723_now(),
            ];
            if ($existing) {
                Capsule::table('mod_domainmonger_item_paymethod_assignments')
                    ->where('id', (int) $existing->id)
                    ->update($data);
            } else {
                $data['created_at'] = dm1723_now();
                Capsule::table('mod_domainmonger_item_paymethod_assignments')->insert($data);
            }
        } catch (Throwable $e) {
            dm1723_log('[ADMIN] Could not record manual invoice Payment Method source for Invoice #' . $invoiceId . ': ' . $e->getMessage());
        }
    }
}

if (!function_exists('dm1732_clear_invoice_manual_marker')) {
    function dm1732_clear_invoice_manual_marker(int $invoiceId): void
    {
        if ($invoiceId <= 0 || !dm1723_ensure_schema()) {
            return;
        }
        try {
            Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('item_type', 'invoice')
                ->where('item_id', $invoiceId)
                ->delete();
        } catch (Throwable $e) {
            // Source metadata must never interrupt invoice handling.
        }
    }
}

if (!function_exists('dm1732_invoice_native_gateway')) {
    /**
     * Resolve the normal WHMCS gateway from the invoice's underlying items.
     * A single native item gateway is authoritative. If grouped items disagree,
     * retain the invoice's current gateway and only clear the specific Pay Method.
     */
    function dm1732_invoice_native_gateway(int $invoiceId, int $clientId): string
    {
        $gateways = [];
        try {
            $items = Capsule::table('tblinvoiceitems')
                ->where('invoiceid', $invoiceId)
                ->get(['type', 'relid', 'amount']);
            foreach ($items as $item) {
                if (abs((float) ($item->amount ?? 0)) <= 0.00001) {
                    continue;
                }
                [$isRoutable, $payMethodId, $kind, $gateway] = dm1723_invoice_item_assignment($clientId, $item);
                if ($isRoutable && trim((string) $gateway) !== '') {
                    $gateways[strtolower(trim((string) $gateway))] = trim((string) $gateway);
                }
            }
        } catch (Throwable $e) {
            $gateways = [];
        }

        if (count($gateways) === 1) {
            return (string) array_values($gateways)[0];
        }

        try {
            $invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first(['paymentmethod']);
            return $invoice ? trim((string) ($invoice->paymentmethod ?? '')) : '';
        } catch (Throwable $e) {
            return '';
        }
    }
}

if (!function_exists('dm1732_set_invoice_default')) {
    function dm1732_set_invoice_default(int $invoiceId, int $clientId): array
    {
        try {
            $model = \WHMCS\Billing\Invoice::find($invoiceId);
            if (!$model) {
                return [false, 'Invoice not found.'];
            }
            if (strcasecmp((string) ($model->status ?? ''), 'Unpaid') !== 0) {
                return [false, 'Only Unpaid invoices can have their Payment Method changed.'];
            }
            $nativeGateway = dm1732_invoice_native_gateway($invoiceId, $clientId);
            if ($nativeGateway !== '') {
                $model->setPaymentMethod($nativeGateway);
            }
            $model->clearPayMethodId();
            $model->save();
            $confirmed = Capsule::table('tblinvoices')->where('id', $invoiceId)->first(['paymethodid']);
            if ($confirmed && (int) ($confirmed->paymethodid ?? 0) > 0) {
                return [false, 'WHMCS did not clear the invoice-specific Pay Method.'];
            }
            return [true, 'Invoice returned to Account Default / normal WHMCS payment behavior.'];
        } catch (Throwable $e) {
            return [false, 'The invoice could not be returned to Account Default: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('dm1732_set_invoice_specific')) {
    function dm1732_set_invoice_specific(int $invoiceId, int $clientId, int $payMethodId): array
    {
        [$valid, $method, $error] = dm1723_validate_paymethod($clientId, $payMethodId);
        if (!$valid || !$method) {
            return [false, $error ?: 'Select a valid Payment Method.'];
        }

        try {
            $model = \WHMCS\Billing\Invoice::find($invoiceId);
            if (!$model) {
                return [false, 'Invoice not found.'];
            }
            if (strcasecmp((string) ($model->status ?? ''), 'Unpaid') !== 0) {
                return [false, 'Only Unpaid invoices can have their Payment Method changed.'];
            }

            $originalGateway = (string) ($model->paymentmethod ?? '');
            $originalPayMethodId = (int) ($model->paymethodid ?? 0);
            try {
                $model->setPaymentMethod((string) $method['gateway']);
                $model->setPayMethodId($payMethodId);
                $model->save();
            } catch (Throwable $applyError) {
                try {
                    if ($originalGateway !== '') {
                        $model->setPaymentMethod($originalGateway);
                    }
                    if ($originalPayMethodId > 0) {
                        $model->setPayMethodId($originalPayMethodId);
                    } else {
                        $model->clearPayMethodId();
                    }
                    $model->save();
                } catch (Throwable $ignore) {
                }
                throw $applyError;
            }

            $confirmed = Capsule::table('tblinvoices')->where('id', $invoiceId)->first(['paymentmethod', 'paymethodid']);
            if (!$confirmed
                || (int) ($confirmed->paymethodid ?? 0) !== $payMethodId
                || strtolower(trim((string) ($confirmed->paymentmethod ?? ''))) !== strtolower(trim((string) $method['gateway']))) {
                return [false, 'WHMCS did not confirm the requested invoice Payment Method.'];
            }
            return [true, 'Invoice Payment Method changed to ' . (string) $method['label'] . '.'];
        } catch (Throwable $e) {
            return [false, 'The invoice Payment Method could not be changed: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('dm1732_invoice_contains_item')) {
    function dm1732_invoice_contains_item(int $invoiceId, int $clientId, string $itemType, int $itemId): bool
    {
        if ($invoiceId <= 0 || $clientId <= 0 || $itemId <= 0) {
            return false;
        }
        try {
            $items = Capsule::table('tblinvoiceitems')
                ->where('invoiceid', $invoiceId)
                ->get(['type', 'relid']);
            foreach ($items as $item) {
                $type = strtolower(trim((string) ($item->type ?? '')));
                $relId = (int) ($item->relid ?? 0);
                if ($itemType === 'domain' && strpos($type, 'domain') !== false && $relId === $itemId) {
                    return true;
                }
                if ($itemType === 'service') {
                    if (($type === 'hosting' || $type === 'service' || strpos($type, 'hosting') !== false) && $relId === $itemId) {
                        return true;
                    }
                    if ($type === 'addon' || strpos($type, 'addon') !== false) {
                        $addon = Capsule::table('tblhostingaddons')->where('id', $relId)->first(['hostingid']);
                        if ($addon && (int) ($addon->hostingid ?? 0) === $itemId) {
                            return true;
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            return false;
        }
        return false;
    }
}

if (!function_exists('dm1736_queue_invoice_notice')) {
    /**
     * Queue a page-specific Admin invoice notice. Notices are keyed by invoice
     * so a reconciliation on one invoice cannot leave a stale message on
     * another invoice. The expected Pay Method ID is stored as a display guard.
     */
    function dm1736_queue_invoice_notice(int $invoiceId, string $type, string $message, ?int $payMethodId = null): void
    {
        if ($invoiceId <= 0) {
            return;
        }
        if (!isset($_SESSION['dm1732_invoice_notices']) || !is_array($_SESSION['dm1732_invoice_notices'])) {
            $_SESSION['dm1732_invoice_notices'] = [];
        }
        $notice = [
            'invoiceid' => $invoiceId,
            'type' => $type,
            'message' => $message,
        ];
        if ($payMethodId !== null) {
            $notice['paymethodid'] = max(0, $payMethodId);
        }
        $_SESSION['dm1732_invoice_notices'][$invoiceId] = $notice;

        // Remove the old singleton notice for this invoice. This also cleans up
        // any stale pre-1736 manual-selection message on the next assignment save.
        if (isset($_SESSION['dm1732_invoice_notice']) && is_array($_SESSION['dm1732_invoice_notice'])
            && (int) ($_SESSION['dm1732_invoice_notice']['invoiceid'] ?? 0) === $invoiceId) {
            unset($_SESSION['dm1732_invoice_notice']);
        }
    }
}

if (!function_exists('dm1732_reconcile_unpaid_invoice')) {
    /**
     * Re-evaluate an existing Unpaid invoice after an underlying item assignment
     * changes. Assignment changes are authoritative and replace any manual
     * invoice-only selection.
     */
    function dm1732_reconcile_unpaid_invoice(int $invoiceId, int $clientId): array
    {
        try {
            $invoice = Capsule::table('tblinvoices')
                ->where('id', $invoiceId)
                ->where('userid', $clientId)
                ->first(['status']);
            if (!$invoice || strcasecmp((string) ($invoice->status ?? ''), 'Unpaid') !== 0) {
                return [true, 'not-unpaid'];
            }

            $decision = dm1723_evaluate_invoice($invoiceId);
            $action = (string) ($decision['action'] ?? 'none');
            if ($action === 'route') {
                dm1723_apply_invoice_decision($invoiceId, $decision, 'live');
                $confirmed = Capsule::table('tblinvoices')->where('id', $invoiceId)->first(['paymentmethod', 'paymethodid']);
                if (!$confirmed
                    || (int) ($confirmed->paymethodid ?? 0) !== (int) ($decision['paymethodid'] ?? 0)
                    || strtolower(trim((string) ($confirmed->paymentmethod ?? ''))) !== strtolower(trim((string) ($decision['gateway'] ?? '')))) {
                    return [false, 'WHMCS did not confirm the reconciled Payment Method.'];
                }
                dm1732_clear_invoice_manual_marker($invoiceId);
                return [true, 'Invoice Payment Method changed to ' . (string) ($decision['label'] ?? ('Pay Method #' . (int) ($decision['paymethodid'] ?? 0))) . '.'];
            }

            if (in_array($action, ['default', 'fallback', 'none'], true)) {
                [$ok, $message] = dm1732_set_invoice_default($invoiceId, $clientId);
                if ($ok) {
                    dm1732_clear_invoice_manual_marker($invoiceId);
                    dm1723_log('Unpaid Invoice #' . $invoiceId . ' was reconciled to Account Default/normal WHMCS behavior after an item assignment change (' . $action . ').');
                }
                return [$ok, $message];
            }
        } catch (Throwable $e) {
            return [false, $e->getMessage()];
        }
        return [true, 'no-change'];
    }
}

if (!function_exists('dm1732_reconcile_unpaid_invoices_for_item')) {
    function dm1732_reconcile_unpaid_invoices_for_item(int $clientId, string $itemType, int $itemId): void
    {
        if ($clientId <= 0 || !in_array($itemType, ['service', 'domain'], true) || $itemId <= 0) {
            return;
        }
        try {
            $invoiceIds = Capsule::table('tblinvoices')
                ->where('userid', $clientId)
                ->where('status', 'Unpaid')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->all();
            foreach ($invoiceIds ?: [] as $invoiceId) {
                $invoiceId = (int) $invoiceId;
                if ($invoiceId <= 0 || !dm1732_invoice_contains_item($invoiceId, $clientId, $itemType, $itemId)) {
                    continue;
                }
                [$ok, $message] = dm1732_reconcile_unpaid_invoice($invoiceId, $clientId);
                if (!$ok) {
                    dm1723_log('Could not reconcile Unpaid Invoice #' . $invoiceId . ' after ' . $itemType . ' #' . $itemId . ' assignment changed: ' . $message);
                } elseif (!in_array($message, ['not-unpaid', 'no-change'], true)) {
                    try {
                        $current = Capsule::table('tblinvoices')->where('id', $invoiceId)->first(['paymethodid']);
                        $currentPayMethodId = $current ? (int) ($current->paymethodid ?? 0) : 0;
                    } catch (Throwable $e) {
                        $currentPayMethodId = 0;
                    }
                    dm1736_queue_invoice_notice($invoiceId, 'success', $message, $currentPayMethodId);
                }
            }
        } catch (Throwable $e) {
            dm1723_log('Could not locate Unpaid invoices for ' . $itemType . ' #' . $itemId . ': ' . $e->getMessage());
        }
    }
}

if (!function_exists('dm1732_invoice_assignment_source')) {
    function dm1732_invoice_assignment_source(int $invoiceId, int $clientId, int $currentPayMethodId): string
    {
        $manual = dm1732_invoice_manual_marker($invoiceId, $clientId);
        if ($manual !== null && $manual === $currentPayMethodId) {
            return 'Manually selected for this invoice';
        }

        $decision = dm1723_evaluate_invoice($invoiceId);
        if ((string) ($decision['action'] ?? '') === 'route'
            && (int) ($decision['paymethodid'] ?? 0) === $currentPayMethodId
            && $currentPayMethodId > 0) {
            $sources = [];
            try {
                $items = Capsule::table('tblinvoiceitems')->where('invoiceid', $invoiceId)->get(['type', 'relid']);
                foreach ($items as $item) {
                    [$routable, $assignedId, $kind] = dm1723_invoice_item_assignment($clientId, $item);
                    if (!$routable || $assignedId !== $currentPayMethodId) {
                        continue;
                    }
                    $type = strtolower((string) ($item->type ?? ''));
                    $relId = (int) ($item->relid ?? 0);
                    if (strpos($type, 'domain') !== false) {
                        $d = Capsule::table('tbldomains')->where('id', $relId)->first(['domain']);
                        $sources[] = $d && trim((string) ($d->domain ?? '')) !== ''
                            ? 'domain ' . trim((string) $d->domain)
                            : 'domain #' . $relId;
                    } elseif ($kind === 'service' || $kind === 'service-addon') {
                        $serviceId = $relId;
                        if ($kind === 'service-addon') {
                            $addon = Capsule::table('tblhostingaddons')->where('id', $relId)->first(['hostingid']);
                            $serviceId = $addon ? (int) ($addon->hostingid ?? 0) : 0;
                        }
                        if ($serviceId > 0) {
                            $sources[] = 'product/service #' . $serviceId;
                        }
                    }
                }
            } catch (Throwable $e) {
                $sources = [];
            }
            $sources = array_values(array_unique(array_filter($sources)));
            if (count($sources) === 1) {
                return 'Assigned from ' . $sources[0];
            }
            if (count($sources) > 1) {
                return 'Assigned from ' . count($sources) . ' invoice items';
            }
            return 'Assigned from product/domain Payment Method override';
        }

        if ($currentPayMethodId <= 0) {
            return 'Account Default / normal WHMCS behavior';
        }
        return 'Invoice-specific Payment Method';
    }
}

if (!function_exists('dm1732_admin_invoice_id')) {
    function dm1732_admin_invoice_id(): int
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if (preg_match('~/admin/billing/invoice/(\\d+)~i', $uri, $m)) {
            return (int) $m[1];
        }
        $script = strtolower(basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        if ($script === 'invoices.php' && strtolower((string) ($_REQUEST['action'] ?? '')) === 'edit') {
            return (int) ($_REQUEST['id'] ?? 0);
        }
        return 0;
    }
}

if (!function_exists('dm1732_admin_invoice_url')) {
    function dm1732_admin_invoice_url(int $invoiceId): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if ($uri !== '' && preg_match('~/admin/billing/invoice/' . preg_quote((string) $invoiceId, '~') . '(?:[/?#]|$)~i', $uri)) {
            return $uri;
        }
        return '/manage/admin/billing/invoice/' . $invoiceId;
    }
}

// Process only our own client-area assignment form.
if (
    strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
    && (string) ($_POST['dm_pmassign_action_1723'] ?? '') === 'save'
) {
    $clientId = dm1723_client_id();
    $itemType = strtolower(trim((string) ($_POST['dm_pmassign_item_type_1723'] ?? '')));
    $itemId = (int) ($_POST['dm_pmassign_item_id_1723'] ?? 0);
    $payMethodId = (int) ($_POST['dm_pmassign_paymethod_id_1723'] ?? 0);
    $submittedToken = (string) ($_POST['dm_pmassign_token_1723'] ?? '');
    $expectedToken = dm1723_token();
    $isAjax = (string) ($_POST['dm_pmassign_ajax_1730'] ?? '') === '1';
    $returnUri = dm1723_safe_clientarea_return_uri((string) ($_POST['dm_pmassign_return_1723'] ?? ''));

    if ($clientId <= 0) {
        $message = 'Your client-area session has expired. Sign in again and retry.';
        dm1723_flash('error', $message);
        if ($isAjax) {
            dm1723_json_response(false, $message);
        }
        dm1723_redirect_current($returnUri);
    }
    if ($submittedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
        $message = 'The Payment Method security token was invalid or expired. Refresh the page and retry.';
        dm1723_flash('error', $message);
        if ($isAjax) {
            dm1723_json_response(false, $message);
        }
        dm1723_redirect_current($returnUri);
    }

    [$ok, $message] = dm1723_save_assignment($clientId, $itemType, $itemId, $payMethodId);
    dm1723_flash($ok ? 'success' : 'error', $message);
    if ($isAjax) {
        dm1723_json_response($ok, $message);
    }
    dm1723_redirect_current($returnUri);
}

// Client Payment Methods: show real Authorize.net card expirations. PayPal Vault
// remains "—" because its remote expiry-like value is not a card expiration.
add_hook('ClientAreaPaymentMethods', 1723, static function ($vars): array {
    $clientId = dm1723_client_id();
    return ['dm1723CardExpiry' => $clientId > 0 ? dm1723_client_card_expiry_map($clientId) : []];
});

add_hook('ClientAreaProductDetailsOutput', 1723, static function ($vars): string {
    $clientId = dm1723_client_id();
    if ($clientId <= 0) {
        return '';
    }

    $service = is_array($vars) && isset($vars['service']) ? $vars['service'] : $vars;
    $serviceId = 0;
    if (is_object($service) && isset($service->id)) {
        $serviceId = (int) $service->id;
    } elseif (is_array($service) && isset($service['id'])) {
        $serviceId = (int) $service['id'];
    }
    if ($serviceId <= 0) {
        $serviceId = (int) ($_REQUEST['id'] ?? 0);
    }
    return dm1723_render_assignment_panel($clientId, 'service', $serviceId);
});

add_hook('ClientAreaDomainDetailsOutput', 1723, static function ($vars): string {
    $clientId = dm1723_client_id();
    if ($clientId <= 0) {
        return '';
    }

    $domain = is_array($vars) && isset($vars['domain']) ? $vars['domain'] : $vars;
    $domainId = 0;
    if (is_object($domain) && isset($domain->id)) {
        $domainId = (int) $domain->id;
    } elseif (is_array($domain) && isset($domain['id'])) {
        $domainId = (int) $domain['id'];
    }
    if ($domainId <= 0) {
        $domainId = (int) ($_REQUEST['id'] ?? $_REQUEST['domainid'] ?? 0);
    }
    return dm1723_render_assignment_panel($clientId, 'domain', $domainId);
});

// Run before WHMCS aggregates due items so Test Live/Live clients with explicit
// assignments have native Separate Invoices enabled in time. Shadow does not
// modify this setting.
add_hook('PreInvoicingGenerateInvoiceItems', 1723, static function (): void {
    dm1723_reconcile_managed_clients();
});

// Admin Product/Service and Domain selectors use native WHMCS admin tab-field
// hooks. In Admin Preview they save mappings only; client UI and invoice routing
// remain completely inert.
add_hook('AdminClientServicesTabFields', 1726, static function (array $vars): array {
    $serviceId = (int) ($vars['id'] ?? 0);
    if ($serviceId <= 0) {
        return [];
    }
    try {
        $service = Capsule::table('tblhosting')->where('id', $serviceId)->first(['userid']);
    } catch (Throwable $e) {
        return [];
    }
    $clientId = $service ? (int) ($service->userid ?? 0) : 0;
    $html = dm1723_admin_assignment_field($clientId, 'service', $serviceId);
    return $html !== '' ? ['Payment Method Override' => $html] : [];
});

add_hook('AdminClientServicesTabFieldsSave', 1726, static function (array $vars): void {
    dm1723_admin_save_assignment(
        'service',
        (int) ($vars['id'] ?? 0),
        'dm_pmassign_admin_service_1726',
        'dm_pmassign_admin_service_present_1726'
    );
});

add_hook('AdminClientDomainsTabFields', 1726, static function (array $vars): array {
    $domainId = (int) ($vars['id'] ?? 0);
    if ($domainId <= 0) {
        return [];
    }
    try {
        $domain = Capsule::table('tbldomains')->where('id', $domainId)->first(['userid']);
    } catch (Throwable $e) {
        return [];
    }
    $clientId = $domain ? (int) ($domain->userid ?? 0) : 0;
    $html = dm1723_admin_assignment_field($clientId, 'domain', $domainId);
    return $html !== '' ? ['Payment Method Override' => $html] : [];
});

add_hook('AdminClientDomainsTabFieldsSave', 1726, static function (array $vars): void {
    dm1723_admin_save_assignment(
        'domain',
        (int) ($vars['id'] ?? 0),
        'dm_pmassign_admin_domain_1726',
        'dm_pmassign_admin_domain_present_1726'
    );
});


if (!function_exists('dm1737_test_live_capture_client')) {
    /**
     * Restrict the diagnostic capture control to the configured Test Live
     * allowlist. Full Live mode deliberately does not expose this test button.
     */
    function dm1737_test_live_capture_client(int $clientId): bool
    {
        if ($clientId <= 0) {
            return false;
        }
        $settings = dm1723_settings();
        if (empty($settings['active']) || (string) ($settings['routing_mode'] ?? '') !== 'test_live') {
            return false;
        }
        $testIds = array_map('intval', (array) ($settings['test_client_ids'] ?? []));
        return in_array($clientId, $testIds, true) && dm1723_client_mode($clientId) === 'live';
    }
}

if (!function_exists('dm1737_capture_result_message')) {
    function dm1737_capture_result_message(array $result): string
    {
        foreach (['message', 'error', 'errormessage'] as $key) {
            $value = trim((string) ($result[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }
        return 'WHMCS returned no additional error message.';
    }
}

// Test Live diagnostic: call native WHMCS CapturePayment with ONLY invoiceid.
// This intentionally does not send a gateway or Pay Method ID, so the result
// lets the resulting gateway transaction show what WHMCS chose from the invoice state at capture time.
add_hook('AdminAreaPage', 1737, static function (array $vars): array {
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'
        || (string) ($_POST['dm1737_action'] ?? '') !== 'test_native_capture') {
        return [];
    }

    $invoiceId = (int) ($_POST['dm1737_invoice_id'] ?? dm1732_admin_invoice_id());
    if ($invoiceId <= 0) {
        return [];
    }

    if (function_exists('check_token')) {
        check_token('WHMCS.admin.default');
    }

    try {
        $invoice = Capsule::table('tblinvoices')
            ->where('id', $invoiceId)
            ->first(['id', 'userid', 'status', 'paymentmethod', 'paymethodid']);
    } catch (Throwable $e) {
        $invoice = null;
    }

    $clientId = $invoice ? (int) ($invoice->userid ?? 0) : 0;
    $ok = false;
    $message = '';

    if (!$invoice || $clientId <= 0) {
        $message = 'The invoice could not be loaded for the Test Live capture.';
    } elseif (!dm1737_test_live_capture_client($clientId)) {
        $message = 'Test Native Capture is available only for allowlisted clients while Routing Mode is Test Live.';
    } elseif (strcasecmp((string) ($invoice->status ?? ''), 'Unpaid') !== 0) {
        $message = 'Only Unpaid invoices can be submitted for the Test Live capture.';
    } else {
        $payMethodId = (int) ($invoice->paymethodid ?? 0);
        $gateway = strtolower(trim((string) ($invoice->paymentmethod ?? '')));
        [$valid, $method, $validationError] = dm1723_validate_paymethod($clientId, $payMethodId);

        if ($payMethodId <= 0) {
            $message = 'This invoice does not currently have a specific Pay Method ID. Assign a saved Payment Method before running the diagnostic capture.';
        } elseif (!$valid || !$method) {
            $message = $validationError ?: 'The invoice-specific Payment Method is not eligible for this diagnostic capture.';
        } elseif ($gateway === '' || $gateway !== strtolower(trim((string) ($method['gateway'] ?? '')))) {
            $message = 'The invoice gateway does not match its specific saved Payment Method. Capture was not attempted.';
        } elseif (!function_exists('localAPI')) {
            $message = 'WHMCS Local API is unavailable, so CapturePayment was not attempted.';
        } else {
            $label = (string) ($method['label'] ?? ('Pay Method #' . $payMethodId));
            dm1723_log('[TEST CAPTURE] Invoice #' . $invoiceId . ' submitted to native CapturePayment with invoiceid only. Pre-capture Pay Method #' . $payMethodId . ' (' . $label . '), gateway ' . $gateway . '. No Pay Method ID or gateway was supplied to CapturePayment.');

            try {
                // Critical diagnostic rule: invoiceid is the ONLY capture input.
                $result = localAPI('CapturePayment', ['invoiceid' => $invoiceId]);
            } catch (Throwable $e) {
                $result = ['result' => 'error', 'message' => $e->getMessage()];
            }

            try {
                $after = Capsule::table('tblinvoices')
                    ->where('id', $invoiceId)
                    ->first(['status', 'paymentmethod', 'paymethodid']);
            } catch (Throwable $e) {
                $after = null;
            }

            $apiSuccess = is_array($result) && strtolower((string) ($result['result'] ?? '')) === 'success';
            if ($apiSuccess) {
                $ok = true;
                $afterStatus = $after ? trim((string) ($after->status ?? '')) : '';
                $message = 'Test Native Capture succeeded. WHMCS CapturePayment received Invoice #' . $invoiceId
                    . ' only; no Payment Method ID or gateway was supplied by the test. Before capture the invoice was set to '
                    . $label . '. Current invoice status: ' . ($afterStatus !== '' ? $afterStatus : 'unknown')
                    . '. Verify the gateway transaction used this exact saved Payment Method.';
                dm1723_log('[TEST CAPTURE] Invoice #' . $invoiceId . ' CapturePayment returned success. Current invoice status: ' . ($afterStatus !== '' ? $afterStatus : 'unknown') . '. Gateway transaction should be checked to confirm the exact stored Payment Method charged.');
            } else {
                $apiMessage = is_array($result) ? dm1737_capture_result_message($result) : 'WHMCS returned an invalid CapturePayment response.';
                $message = 'Test Native Capture did not succeed: ' . $apiMessage
                    . ' No Payment Method ID or gateway was supplied to CapturePayment.';
                dm1723_log('[TEST CAPTURE] Invoice #' . $invoiceId . ' CapturePayment returned an error: ' . $apiMessage);
            }
        }
    }

    // Do not bind the capture-result notice to a Pay Method ID: a successful
    // native capture may change invoice state before the redirected page loads.
    dm1736_queue_invoice_notice($invoiceId, $ok ? 'success' : 'danger', $message, null);
    header('Location: ' . dm1732_admin_invoice_url($invoiceId));
    exit;
});

// Admin invoice Payment Method control. WHMCS has no dedicated invoice-field
// output hook, so this remains page-specific through AdminAreaPage/FooterOutput
// and never edits the protected WHMCS invoice templates or core files.
add_hook('AdminAreaPage', 1732, static function (array $vars): array {
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'
        || (string) ($_POST['dm1732_action'] ?? '') !== 'save_invoice_paymethod') {
        return [];
    }

    $invoiceId = (int) ($_POST['dm1732_invoice_id'] ?? dm1732_admin_invoice_id());
    if ($invoiceId <= 0) {
        return [];
    }

    try {
        $invoice = Capsule::table('tblinvoices')
            ->where('id', $invoiceId)
            ->first(['userid', 'status']);
    } catch (Throwable $e) {
        $invoice = null;
    }
    $clientId = $invoice ? (int) ($invoice->userid ?? 0) : 0;
    if ($clientId <= 0) {
        return [];
    }

    if (function_exists('check_token')) {
        check_token('WHMCS.admin.default');
    }

    $mode = dm1723_admin_assignment_mode($clientId);
    $ok = false;
    $message = '';
    if ($mode !== 'live') {
        $message = 'Invoice Payment Method editing is available only in Test Live or Live routing mode.';
    } elseif (strcasecmp((string) ($invoice->status ?? ''), 'Unpaid') !== 0) {
        $message = 'Only Unpaid invoices can have their Payment Method changed.';
    } else {
        $payMethodId = (int) ($_POST['dm1732_invoice_paymethod_id'] ?? 0);
        if ($payMethodId > 0) {
            [$ok, $message] = dm1732_set_invoice_specific($invoiceId, $clientId, $payMethodId);
        } else {
            [$ok, $message] = dm1732_set_invoice_default($invoiceId, $clientId);
        }
        if ($ok) {
            dm1732_set_invoice_manual_marker($invoiceId, $clientId, max(0, $payMethodId));
            dm1723_log('[ADMIN] Invoice #' . $invoiceId . ' manually set to ' . ($payMethodId > 0 ? 'Pay Method #' . $payMethodId : 'Account Default') . '. Underlying product/domain assignments were not changed.');
        }
    }

    $noticePayMethodId = 0;
    if ($ok) {
        try {
            $noticeInvoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first(['paymethodid']);
            $noticePayMethodId = $noticeInvoice ? (int) ($noticeInvoice->paymethodid ?? 0) : 0;
        } catch (Throwable $e) {
            $noticePayMethodId = 0;
        }
    }
    dm1736_queue_invoice_notice($invoiceId, $ok ? 'success' : 'danger', $message, $ok ? $noticePayMethodId : null);

    header('Location: ' . dm1732_admin_invoice_url($invoiceId));
    exit;
});

add_hook('AdminAreaFooterOutput', 1732, static function (array $vars): string {
    $invoiceId = dm1732_admin_invoice_id();
    if ($invoiceId <= 0) {
        return '';
    }

    try {
        $invoice = Capsule::table('tblinvoices')
            ->where('id', $invoiceId)
            ->first(['id', 'userid', 'status', 'paymentmethod', 'paymethodid']);
    } catch (Throwable $e) {
        $invoice = null;
    }
    if (!$invoice) {
        return '';
    }

    $clientId = (int) ($invoice->userid ?? 0);
    $mode = dm1723_admin_assignment_mode($clientId);
    if ($clientId <= 0 || $mode === 'off') {
        return '';
    }

    $currentPayMethodId = (int) ($invoice->paymethodid ?? 0);
    $currentGateway = trim((string) ($invoice->paymentmethod ?? ''));
    $status = trim((string) ($invoice->status ?? ''));
    $methods = dm1723_paymethods($clientId);
    $editable = $mode === 'live' && strcasecmp($status, 'Unpaid') === 0;
    $source = dm1732_invoice_assignment_source($invoiceId, $clientId, $currentPayMethodId);
    $testCaptureAllowed = dm1737_test_live_capture_client($clientId)
        && strcasecmp($status, 'Unpaid') === 0
        && $currentPayMethodId > 0
        && isset($methods[$currentPayMethodId])
        && strtolower(trim($currentGateway)) === strtolower(trim((string) ($methods[$currentPayMethodId]['gateway'] ?? '')));

    $defaultLabel = 'Account Default / Existing WHMCS Setting';
    $defaultId = dm1723_default_paymethod_id($clientId);
    if ($defaultId > 0 && isset($methods[$defaultId])) {
        $defaultLabel .= ' — currently ' . dm1738_paymethod_dropdown_label($methods[$defaultId]);
    }

    $options = '<option value="0"' . ($currentPayMethodId <= 0 ? ' selected' : '') . '>' . dm1723_escape($defaultLabel) . '</option>';
    foreach ($methods as $method) {
        $id = (int) ($method['id'] ?? 0);
        if ($id <= 0) {
            continue;
        }
        $options .= '<option value="' . $id . '"' . ($id === $currentPayMethodId ? ' selected' : '') . '>'
            . dm1723_escape(dm1738_paymethod_dropdown_label($method)) . '</option>';
    }
    if ($currentPayMethodId > 0 && !isset($methods[$currentPayMethodId])) {
        $options .= '<option value="' . $currentPayMethodId . '" selected disabled>Current Pay Method #' . $currentPayMethodId . ' — unavailable for new assignment</option>';
    }

    $noticeHtml = '';
    $notice = null;
    if (isset($_SESSION['dm1732_invoice_notices'][$invoiceId]) && is_array($_SESSION['dm1732_invoice_notices'][$invoiceId])) {
        $notice = $_SESSION['dm1732_invoice_notices'][$invoiceId];
        unset($_SESSION['dm1732_invoice_notices'][$invoiceId]);
        if (empty($_SESSION['dm1732_invoice_notices'])) {
            unset($_SESSION['dm1732_invoice_notices']);
        }
    } elseif (isset($_SESSION['dm1732_invoice_notice']) && is_array($_SESSION['dm1732_invoice_notice'])
        && (int) ($_SESSION['dm1732_invoice_notice']['invoiceid'] ?? 0) === $invoiceId) {
        // Backward-compatible cleanup for a notice queued before Patch 1736.
        $notice = $_SESSION['dm1732_invoice_notice'];
        unset($_SESSION['dm1732_invoice_notice']);
    }
    if (is_array($notice)) {
        $expectedPayMethodId = array_key_exists('paymethodid', $notice) ? (int) $notice['paymethodid'] : null;
        if ($expectedPayMethodId === null || $expectedPayMethodId === $currentPayMethodId) {
            $type = in_array((string) ($notice['type'] ?? ''), ['success', 'danger', 'warning', 'info'], true)
                ? (string) $notice['type'] : 'info';
            $noticeHtml = '<div class="alert alert-' . dm1723_escape($type) . '" style="margin-bottom:10px;">'
                . dm1723_escape((string) ($notice['message'] ?? '')) . '</div>';
        }
    }

    $csrf = function_exists('generate_token') ? (string) generate_token('plain') : '';
    $modeNote = '';
    if (dm1723_account_routing_disabled($clientId)) {
        $modeNote = '<div class="alert alert-warning" style="margin-top:10px;margin-bottom:0;"><strong>Payment Routing Disabled for this account:</strong> Invoice Payment Method routing/editing from this feature is paused. Saved product/domain assignments are retained.</div>';
    } elseif ($mode === 'preview') {
        $modeNote = '<div class="alert alert-info" style="margin-top:10px;margin-bottom:0;"><strong>Admin Preview:</strong> Invoice Payment Method editing is disabled.</div>';
    } elseif ($mode === 'shadow') {
        $modeNote = '<div class="alert alert-info" style="margin-top:10px;margin-bottom:0;"><strong>Shadow:</strong> Invoice Payment Method editing is disabled; live invoices remain untouched.</div>';
    } elseif (strcasecmp($status, 'Unpaid') !== 0) {
        $modeNote = '<div class="alert alert-info" style="margin-top:10px;margin-bottom:0;">Only Unpaid invoices can have their Payment Method changed.</div>';
    }

    $disabled = $editable ? '' : ' disabled';
    $buttonDisabled = ' disabled';
    $html = '<div id="dm1732-invoice-paymethod-panel" class="dm1733-invoice-paymethod">'
        . '<div class="dm1733-invoice-paymethod-heading">Payment Method</div>'
        . '<div class="dm1733-invoice-paymethod-body">' . $noticeHtml
        . '<form method="post" action="' . dm1723_escape(dm1732_admin_invoice_url($invoiceId)) . '" id="dm1732-invoice-paymethod-form">'
        . '<input type="hidden" name="token" value="' . dm1723_escape($csrf) . '">'
        . '<input type="hidden" name="dm1732_action" value="save_invoice_paymethod">'
        . '<input type="hidden" name="dm1732_invoice_id" value="' . $invoiceId . '">'
        . '<div style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;">'
        . '<div style="flex:1 1 460px;max-width:720px;"><label for="dm1732-invoice-paymethod" style="display:block;margin-bottom:5px;">Payment Method for this invoice</label>'
        . '<select class="form-control" id="dm1732-invoice-paymethod" name="dm1732_invoice_paymethod_id" data-saved="' . $currentPayMethodId . '"' . $disabled . '>' . $options . '</select></div>'
        . '<div><button type="submit" class="btn btn-primary" id="dm1732-invoice-paymethod-save"' . $buttonDisabled . '>Save Payment Method</button></div>'
        . '</div></form>'
        . '<div style="margin-top:9px;color:#555;"><strong>Source:</strong> ' . dm1723_escape($source) . '</div>'
        . '<div style="margin-top:3px;color:#777;"><strong>Gateway:</strong> ' . dm1723_escape($currentGateway !== '' ? dm1723_gateway_display($currentGateway) : 'WHMCS Default') . '</div>'
        . '<div style="margin-top:6px;color:#777;font-size:12px;">Changing this invoice here does not change the product/domain assignment. A later assignment change on an underlying product/domain will become authoritative again while this invoice remains Unpaid.</div>'
        . ($testCaptureAllowed
            ? '<div class="dm1737-test-capture"><form method="post" action="' . dm1723_escape(dm1732_admin_invoice_url($invoiceId)) . '" id="dm1737-test-capture-form">'
                . '<input type="hidden" name="token" value="' . dm1723_escape($csrf) . '">'
                . '<input type="hidden" name="dm1737_action" value="test_native_capture">'
                . '<input type="hidden" name="dm1737_invoice_id" value="' . $invoiceId . '">'
                . '<button type="submit" class="btn btn-default" id="dm1737-test-capture-button">Test Native Capture</button>'
                . '</form><div class="dm1737-test-capture-note"><strong>Test Live diagnostic:</strong> Performs a real WHMCS CapturePayment call using only Invoice #' . $invoiceId . '. The test does not submit a Payment Method ID or gateway. Verify the resulting gateway transaction against <strong>' . dm1723_escape((string) ($methods[$currentPayMethodId]['label'] ?? ('Pay Method #' . $currentPayMethodId))) . '</strong>.</div></div>'
            : '')
        . $modeNote
        . '</div></div>';

    return $html . <<<'HTML'
<style id="dm1732-invoice-paymethod-style">
#dm1732-invoice-paymethod-panel.dm1733-invoice-paymethod{display:block!important;float:none!important;clear:none!important;position:static!important;width:100%!important;height:auto!important;min-height:0!important;margin:0 0 14px 0!important;padding:0!important;overflow:visible!important;border:1px solid #d7dce2;border-radius:3px;background:#fff;box-sizing:border-box}
#dm1732-invoice-paymethod-panel .dm1733-invoice-paymethod-heading{display:block;background:#163a5f;color:#fff;border:0;padding:8px 10px;font-weight:600;line-height:20px;box-sizing:border-box}
#dm1732-invoice-paymethod-panel .dm1733-invoice-paymethod-body{display:block!important;float:none!important;clear:none!important;height:auto!important;min-height:0!important;margin:0!important;padding:10px 12px 11px!important;overflow:visible!important;background:#fff;box-sizing:border-box}
#dm1732-invoice-paymethod-panel form{display:block!important;float:none!important;height:auto!important;min-height:0!important;margin:0!important;padding:0!important}
#dm1732-invoice-paymethod-panel .alert{margin-bottom:10px}
#dm1732-invoice-paymethod-panel .btn-primary{background:#f58220;border-color:#f58220;color:#fff}
#dm1732-invoice-paymethod-panel .btn-primary:hover,#dm1732-invoice-paymethod-panel .btn-primary:focus{background:#214e7a;border-color:#214e7a}
#dm1732-invoice-paymethod-panel .btn[disabled]{opacity:.55;cursor:not-allowed}
#dm1732-invoice-paymethod-panel .dm1737-test-capture{margin-top:12px;padding-top:10px;border-top:1px solid #e5e5e5}
#dm1732-invoice-paymethod-panel .dm1737-test-capture form{display:inline-block!important;margin:0 10px 0 0!important}
#dm1732-invoice-paymethod-panel .dm1737-test-capture .btn-default{background:#163a5f;border-color:#163a5f;color:#fff}
#dm1732-invoice-paymethod-panel .dm1737-test-capture .btn-default:hover,#dm1732-invoice-paymethod-panel .dm1737-test-capture .btn-default:focus{background:#214e7a;border-color:#214e7a;color:#fff}
#dm1732-invoice-paymethod-panel .dm1737-test-capture-note{display:inline;color:#777;font-size:12px;line-height:1.45}
</style>
<script id="dm1732-invoice-paymethod-script">
(function(){
    var panel=document.getElementById('dm1732-invoice-paymethod-panel');
    if(!panel){return;}
    var select=document.getElementById('dm1732-invoice-paymethod');
    var save=document.getElementById('dm1732-invoice-paymethod-save');
    function sync(){
        if(!select||!save||select.disabled){return;}
        save.disabled=String(select.value||'0')===String(select.getAttribute('data-saved')||'0');
    }
    if(select){select.addEventListener('change',sync);sync();}
    var captureForm=document.getElementById('dm1737-test-capture-form');
    var captureButton=document.getElementById('dm1737-test-capture-button');
    if(captureForm){
        captureForm.addEventListener('submit',function(ev){
            var message='This will attempt a REAL payment capture for this invoice. WHMCS CapturePayment will be given only the invoice ID; this diagnostic will not send a Payment Method ID or gateway. Continue?';
            if(!window.confirm(message)){ev.preventDefault();return;}
            if(captureButton){captureButton.disabled=true;captureButton.textContent='Capturing...';}
        });
    }
    var target=document.querySelector('.contentarea')||document.querySelector('#contentarea')||document.querySelector('main');
    if(target && panel.parentNode!==target){
        var first=target.firstElementChild;
        if(first){target.insertBefore(panel,first);}else{target.appendChild(panel);}
    }
}());
</script>
HTML;
});

// Also reconcile after admin configuration changes are visible on the next
// admin request, giving the Off switch a prompt path to restore settings that
// this module previously managed.
add_hook('AdminAreaFooterOutput', 1723, static function ($vars): string {
    static $done = false;
    if (!$done) {
        $done = true;
        dm1723_reconcile_managed_clients();
    }
    return '';
});

// Route newly-created invoices. Shadow mode evaluates/logs the exact same
// decision but makes zero invoice changes.
add_hook('InvoiceCreation', 1723, static function (array $vars): void {
    $invoiceId = (int) ($vars['invoiceid'] ?? 0);
    if ($invoiceId <= 0) {
        return;
    }

    try {
        $invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first(['userid']);
        $clientId = $invoice ? (int) ($invoice->userid ?? 0) : 0;
        $mode = dm1723_client_mode($clientId);
        if (!in_array($mode, ['shadow', 'live'], true) || !dm1723_ensure_schema()) {
            return;
        }

        $decision = dm1723_evaluate_invoice($invoiceId);
        dm1723_apply_invoice_decision($invoiceId, $decision, $mode);
    } catch (Throwable $e) {
        dm1723_log('Invoice #' . $invoiceId . ' evaluation failed; native WHMCS billing was left unchanged. Error: ' . $e->getMessage());
    }
});
