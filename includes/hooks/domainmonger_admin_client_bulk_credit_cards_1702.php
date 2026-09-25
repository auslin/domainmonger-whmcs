<?php
/**
 * Patch 1722 - Admin Client Summary Payment Methods alignment and date sorting.
 *
 * Built from confirmed Patch 1721 behavior. Left-aligns Payment Methods table columns except Default and adds client-side sorting for Added and Last Updated using the native WHMCS timestamps as sort keys. All confirmed 1721/1720 payment behavior is preserved. The confirmed 1711 search hook is untouched.
 *
 * Scope:
 * - Admin client summary page only (clientssummary.php?userid=...)
 * - Lists saved CreditCard, RemoteCreditCard, BankAccount and RemoteBankAccount Pay Methods
 *   with native WHMCS descriptions, card expiry where applicable, storage/gateway,
 *   current-default state and inactive-gateway status
 * - Bulk deletes selected Pay Methods through WHMCS DeletePayMethod
 * - Sets one selected Pay Method as default through WHMCS UpdatePayMethod
 * - Edits expiration dates for actual card Pay Methods through WHMCS UpdatePayMethod; PayPal and bank-account rows are excluded
 * - Client-side sort of Expiration by real year/month value
 * - Client-side sort of Added and Last Updated by native WHMCS timestamps
 * - Displays native tblpaymethods created_at (Added) and updated_at (Last Updated) dates
 * - Left-aligns table columns except Default, which retains its existing alignment
 * - Reuses WHMCS native Add Credit Card and Add Bank Account modal triggers
 * - Keeps the native Pay Methods block in the DOM for WHMCS modal plumbing, but suppresses it visually
 *
 * Safety:
 * - No card numbers, CVVs or remote tokens are exposed
 * - No direct database deletes/updates are performed
 * - Pay Method ownership is revalidated through GetPayMethods before actions
 * - Remote deletion/update failures are not forced; WHMCS/gateway errors are shown
 * - Native WHMCS Pay Methods markup remains in the DOM so its modal links and refresh route stay authoritative
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm1702_is_client_summary_page')) {
    function dm1702_is_client_summary_page(array $vars = []): bool
    {
        $filename = strtolower((string) ($vars['filename'] ?? ''));
        $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));

        return in_array($filename, ['clientssummary', 'clientssummary.php'], true)
            || $script === 'clientssummary.php';
    }
}

if (!function_exists('dm1702_can_manage_cards')) {
    function dm1702_can_manage_cards(array $vars = []): bool
    {
        if (function_exists('checkPermission')) {
            try {
                return (bool) checkPermission('Update/Delete Stored Credit Card', true);
            } catch (Throwable $e) {
                // Fall through to the permission array supplied by WHMCS.
            }
        }

        $permissions = $vars['admin_perms'] ?? [];
        return is_array($permissions)
            && in_array('Update/Delete Stored Credit Card', $permissions, true);
    }
}

if (!function_exists('dm1702_get_cards')) {
    /**
     * @return array{result:string,message:string,cards:array<int,array<string,mixed>>}
     */
    function dm1702_get_cards(int $clientId): array
    {
        $result = localAPI('GetPayMethods', [
            'clientid' => $clientId,
        ]);

        if (($result['result'] ?? '') !== 'success') {
            return [
                'result' => 'error',
                'message' => (string) ($result['message'] ?? 'WHMCS could not load this client\'s Pay Methods.'),
                'cards' => [],
            ];
        }

        $cards = [];
        foreach (($result['paymethods'] ?? []) as $payMethod) {
            if (!is_array($payMethod)) {
                continue;
            }

            $type = (string) ($payMethod['type'] ?? '');
            if (!in_array($type, ['CreditCard', 'RemoteCreditCard', 'BankAccount', 'RemoteBankAccount'], true)) {
                continue;
            }

            $id = (int) ($payMethod['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            // Intentionally copy only display-safe fields. In particular,
            // never copy remote_token into the data used to render the page.
            $cards[$id] = [
                'id' => $id,
                'type' => $type,
                'description' => (string) ($payMethod['description'] ?? ''),
                'gateway_name' => (string) ($payMethod['gateway_name'] ?? ''),
                'card_last_four' => (string) ($payMethod['card_last_four'] ?? ''),
                'expiry_date' => (string) ($payMethod['expiry_date'] ?? ''),
                'card_type' => (string) ($payMethod['card_type'] ?? ''),
                'created_at' => '',
                'updated_at' => '',
            ];
        }

        // Get the native WHMCS timestamps in one read-only query. GetPayMethods
        // does not expose these fields, so tblpaymethods remains authoritative.
        if ($cards) {
            try {
                $timestampRows = Capsule::table('tblpaymethods')
                    ->where('userid', $clientId)
                    ->whereNull('deleted_at')
                    ->whereIn('id', array_keys($cards))
                    ->get(['id', 'created_at', 'updated_at']);

                foreach ($timestampRows as $timestampRow) {
                    $timestampId = isset($timestampRow->id) ? (int) $timestampRow->id : 0;
                    if ($timestampId <= 0 || !isset($cards[$timestampId])) {
                        continue;
                    }
                    $cards[$timestampId]['created_at'] = isset($timestampRow->created_at)
                        ? (string) $timestampRow->created_at
                        : '';
                    $cards[$timestampId]['updated_at'] = isset($timestampRow->updated_at)
                        ? (string) $timestampRow->updated_at
                        : '';
                }
            } catch (Throwable $e) {
                // Dates are informational only. Never prevent the Payment Methods
                // manager from loading if timestamp metadata cannot be read.
            }
        }

        return [
            'result' => 'success',
            'message' => '',
            'cards' => $cards,
        ];
    }
}

if (!function_exists('dm1702_get_default_paymethod_id')) {
    function dm1702_get_default_paymethod_id(int $clientId): int
    {
        try {
            $row = Capsule::table('tblpaymethods')
                ->where('userid', $clientId)
                ->whereNull('deleted_at')
                ->where('order_preference', 0)
                ->orderBy('id', 'asc')
                ->first(['id']);

            if ($row && isset($row->id)) {
                return (int) $row->id;
            }

            // Defensive fallback for old/inconsistent data that has no row at 0.
            $row = Capsule::table('tblpaymethods')
                ->where('userid', $clientId)
                ->whereNull('deleted_at')
                ->orderBy('order_preference', 'asc')
                ->orderBy('id', 'asc')
                ->first(['id']);

            return ($row && isset($row->id)) ? (int) $row->id : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }
}

if (!function_exists('dm1702_escape')) {
    function dm1702_escape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('dm1702_display_paymethod_date')) {
    function dm1702_display_paymethod_date(string $mysqlDateTime): string
    {
        $value = trim($mysqlDateTime);
        if ($value === '' || strpos($value, '0000-00-00') === 0) {
            return '';
        }

        // WHMCS's date helper applies General Settings > Localisation > Date Format
        // when applyClientDateFormat is false, which is appropriate for Admin Area output.
        if (function_exists('fromMySQLDate')) {
            try {
                $formatted = (string) fromMySQLDate($value, false, false);
                if (trim($formatted) !== '') {
                    return $formatted;
                }
            } catch (Throwable $e) {
                // Fall through to a deterministic safe fallback.
            }
        }

        $timestamp = strtotime($value);
        return $timestamp !== false ? date('Y-m-d', $timestamp) : $value;
    }
}

if (!function_exists('dm1702_paymethod_date_sort_key')) {
    function dm1702_paymethod_date_sort_key(string $mysqlDateTime): int
    {
        $value = trim($mysqlDateTime);
        if ($value === '' || strpos($value, '0000-00-00') === 0) {
            return 0;
        }

        $timestamp = strtotime($value);
        return $timestamp !== false ? (int) $timestamp : 0;
    }
}

if (!function_exists('dm1702_normalize_expiry')) {
    /**
     * Convert admin-friendly MM/YY, MM/YYYY or WHMCS MMYY into MMYY.
     * Returns an empty string when the value is invalid.
     */
    function dm1702_normalize_expiry(string $expiry): string
    {
        $value = trim($expiry);
        if ($value === '') {
            return '';
        }

        $month = 0;
        $year = 0;

        if (preg_match('~^(\d{1,2})\s*/\s*(\d{2}|\d{4})$~', $value, $matches)) {
            $month = (int) $matches[1];
            $year = (int) $matches[2];
        } elseif (preg_match('~^(\d{2})(\d{2})$~', $value, $matches)) {
            $month = (int) $matches[1];
            $year = (int) $matches[2];
        } else {
            return '';
        }

        if ($month < 1 || $month > 12) {
            return '';
        }

        if ($year >= 2000 && $year <= 2099) {
            $year -= 2000;
        }

        if ($year < 0 || $year > 99) {
            return '';
        }

        return sprintf('%02d%02d', $month, $year);
    }
}

if (!function_exists('dm1702_display_expiry')) {
    function dm1702_display_expiry(string $expiry): string
    {
        $normalized = dm1702_normalize_expiry($expiry);
        if ($normalized === '') {
            return trim($expiry);
        }

        return substr($normalized, 0, 2) . '/' . substr($normalized, 2, 2);
    }
}

if (!function_exists('dm1702_expiry_sort_key')) {
    function dm1702_expiry_sort_key(string $expiry): int
    {
        $normalized = dm1702_normalize_expiry($expiry);
        if ($normalized === '') {
            return 999999;
        }

        $month = (int) substr($normalized, 0, 2);
        $year = 2000 + (int) substr($normalized, 2, 2);
        return ($year * 100) + $month;
    }
}

if (!function_exists('dm1702_is_expired')) {
    function dm1702_is_expired(string $expiry): bool
    {
        $normalized = dm1702_normalize_expiry($expiry);
        if ($normalized === '') {
            return false;
        }

        $month = (int) substr($normalized, 0, 2);
        $year = 2000 + (int) substr($normalized, 2, 2);
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        return $year < $currentYear || ($year === $currentYear && $month < $currentMonth);
    }
}

if (!function_exists('dm1702_is_paypal_account')) {
    function dm1702_is_paypal_account(array $card): bool
    {
        $type = (string) ($card['type'] ?? '');
        if (in_array($type, ['BankAccount', 'RemoteBankAccount'], true)) {
            return false;
        }

        return strcasecmp(trim((string) ($card['card_type'] ?? '')), 'PayPal') === 0;
    }
}

if (!function_exists('dm1702_gateway_display_name')) {
    function dm1702_gateway_display_name(string $gateway): string
    {
        $gateway = trim($gateway);
        if ($gateway === '') {
            return '';
        }

        static $cache = [];
        if (array_key_exists($gateway, $cache)) {
            return $cache[$gateway];
        }

        // WHMCS stores the administrator-facing gateway name in the activated
        // gateway settings. Prefer that label, but keep the module identifier
        // as a safe fallback when no configured display name is available.
        try {
            $configuredName = Capsule::table('tblpaymentgateways')
                ->where('gateway', $gateway)
                ->where('setting', 'name')
                ->value('value');

            if (is_string($configuredName) && trim($configuredName) !== '') {
                return $cache[$gateway] = trim($configuredName);
            }
        } catch (Throwable $e) {
            // Fall back to the module identifier without affecting the manager.
        }

        return $cache[$gateway] = $gateway;
    }
}

if (!function_exists('dm1702_card_label')) {
    function dm1702_card_label(array $card, int $payMethodId): string
    {
        $type = (string) ($card['type'] ?? '');
        if (in_array($type, ['BankAccount', 'RemoteBankAccount'], true)) {
            return 'Bank Account Pay Method #' . $payMethodId;
        }

        $cardType = trim((string) ($card['card_type'] ?? ''));
        if (strcasecmp($cardType, 'PayPal') === 0) {
            return 'PayPal Pay Method #' . $payMethodId;
        }

        $lastFour = preg_replace('/\D+/', '', (string) ($card['card_last_four'] ?? ''));
        $label = trim($cardType . ($lastFour !== '' ? ' •••• ' . $lastFour : ''));

        return $label !== '' ? $label : 'Pay Method #' . $payMethodId;
    }
}

if (!function_exists('dm1702_set_notice')) {
    function dm1702_set_notice(string $type, string $message): void
    {
        $_SESSION['dm1702_bulk_cards_notice'] = [
            'type' => $type === 'success' ? 'success' : ($type === 'warning' ? 'warning' : 'danger'),
            'message' => $message,
        ];
    }
}

if (!function_exists('dm1702_redirect')) {
    function dm1702_redirect(int $clientId): void
    {
        header('Location: clientssummary.php?userid=' . $clientId);
        exit;
    }
}

/**
 * Process our page-specific POST before the template is rendered.
 */
add_hook('AdminAreaPage', 1704, function (array $vars) {
    if (!dm1702_is_client_summary_page($vars)) {
        return [];
    }

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        return [];
    }

    $action = (string) ($_POST['dm1702_action'] ?? '');
    if (!in_array($action, ['delete_selected', 'set_default', 'update_expirations'], true)) {
        return [];
    }

    $clientId = (int) ($_POST['userid'] ?? $_GET['userid'] ?? 0);
    if ($clientId <= 0) {
        return [];
    }

    if (!dm1702_can_manage_cards($vars)) {
        dm1702_set_notice('danger', 'You do not have permission to update or delete stored payment methods.');
        dm1702_redirect($clientId);
    }

    // Use WHMCS's native admin CSRF validation.
    check_token('WHMCS.admin.default');

    $loaded = dm1702_get_cards($clientId);
    if ($loaded['result'] !== 'success') {
        dm1702_set_notice('danger', $loaded['message']);
        dm1702_redirect($clientId);
    }

    $cards = $loaded['cards'];

    if ($action === 'set_default') {
        $payMethodId = (int) ($_POST['default_paymethod_id'] ?? 0);
        if ($payMethodId <= 0 || !isset($cards[$payMethodId])) {
            dm1702_set_notice('danger', 'Select a payment method to make the default.');
            dm1702_redirect($clientId);
        }

        $result = localAPI('UpdatePayMethod', [
            'clientid' => $clientId,
            'paymethodid' => $payMethodId,
            'set_as_default' => true,
        ]);

        if (($result['result'] ?? '') === 'success') {
            dm1702_set_notice('success', 'Default payment method updated.');
            if (function_exists('logActivity')) {
                logActivity('Admin payment-method manager: Pay Method #' . $payMethodId . ' set as default for Client #' . $clientId . '.');
            }
        } else {
            dm1702_set_notice('danger', (string) ($result['message'] ?? 'WHMCS could not update the default payment method.'));
        }

        dm1702_redirect($clientId);
    }

    if ($action === 'update_expirations') {
        $submitted = $_POST['expiry_by_paymethod'] ?? [];
        if (!is_array($submitted)) {
            $submitted = [];
        }

        $changes = [];
        $validationErrors = [];

        foreach ($cards as $payMethodId => $card) {
            $payMethodType = (string) ($card['type'] ?? '');
            // Only real credit-card Pay Methods belong in this action. PayPal
            // vaulted accounts can expose a WHMCS remote expiry-like value,
            // but it is not a normal card expiration. Bank accounts likewise
            // have no editable card expiration. Enforce this server-side as
            // well as in the rendered UI.
            if (!in_array($payMethodType, ['CreditCard', 'RemoteCreditCard'], true)
                || dm1702_is_paypal_account($card)) {
                continue;
            }

            if (!array_key_exists((string) $payMethodId, $submitted) && !array_key_exists($payMethodId, $submitted)) {
                continue;
            }

            $rawValue = (string) ($submitted[$payMethodId] ?? $submitted[(string) $payMethodId] ?? '');
            $currentRaw = (string) ($card['expiry_date'] ?? '');
            $currentDisplay = dm1702_display_expiry($currentRaw);

            // Unchanged blank or legacy/malformed values should not block an
            // administrator from updating a different card on the page.
            if (trim($rawValue) === trim($currentDisplay)) {
                continue;
            }

            $normalized = dm1702_normalize_expiry($rawValue);
            if ($normalized === '') {
                $validationErrors[] = dm1702_card_label($card, $payMethodId) . ': enter a valid expiration date as MM/YY.';
                continue;
            }

            $current = dm1702_normalize_expiry($currentRaw);
            if ($normalized !== $current) {
                $changes[$payMethodId] = $normalized;
            }
        }

        if ($validationErrors) {
            dm1702_set_notice('danger', 'No expiration dates were updated. ' . implode(' | ', $validationErrors));
            dm1702_redirect($clientId);
        }

        if (!$changes) {
            dm1702_set_notice('warning', 'No expiration dates were changed.');
            dm1702_redirect($clientId);
        }

        $updated = 0;
        $errors = [];
        foreach ($changes as $payMethodId => $normalized) {
            $result = localAPI('UpdatePayMethod', [
                'clientid' => $clientId,
                'paymethodid' => $payMethodId,
                'card_expiry' => $normalized,
            ]);

            if (($result['result'] ?? '') === 'success') {
                $updated++;
                continue;
            }

            $errors[] = dm1702_card_label($cards[$payMethodId], $payMethodId)
                . ': ' . (string) ($result['message'] ?? 'Expiration update failed.');
        }

        if ($updated > 0 && function_exists('logActivity')) {
            logActivity('Admin payment-method manager: updated expiration date for ' . $updated . ' Pay Method(s) for Client #' . $clientId . '.');
        }

        if (!$errors) {
            dm1702_set_notice('success', $updated . ' expiration date' . ($updated === 1 ? '' : 's') . ' updated.');
        } elseif ($updated > 0) {
            dm1702_set_notice(
                'warning',
                $updated . ' expiration date' . ($updated === 1 ? '' : 's') . ' updated. '
                . count($errors) . ' could not be updated: ' . implode(' | ', $errors)
            );
        } else {
            dm1702_set_notice('danger', 'No expiration dates were updated. ' . implode(' | ', $errors));
        }

        dm1702_redirect($clientId);
    }

    $requestedIds = $_POST['delete_paymethod_ids'] ?? [];
    if (!is_array($requestedIds)) {
        $requestedIds = [];
    }

    $selected = [];
    foreach ($requestedIds as $id) {
        $id = (int) $id;
        if ($id > 0 && isset($cards[$id])) {
            $selected[$id] = $id;
        }
    }

    if (!$selected) {
        dm1702_set_notice('danger', 'Select at least one payment method to delete.');
        dm1702_redirect($clientId);
    }

    // If the current default is selected, process it last. This lets WHMCS
    // keep the account in the most stable state while other selected Pay Methods
    // are being removed.
    $defaultPayMethodId = dm1702_get_default_paymethod_id($clientId);
    $selectedIds = array_values($selected);
    usort($selectedIds, static function (int $a, int $b) use ($defaultPayMethodId): int {
        if ($a === $defaultPayMethodId && $b !== $defaultPayMethodId) {
            return 1;
        }
        if ($b === $defaultPayMethodId && $a !== $defaultPayMethodId) {
            return -1;
        }
        return $a <=> $b;
    });

    $deleted = 0;
    $errors = [];

    foreach ($selectedIds as $payMethodId) {
        $result = localAPI('DeletePayMethod', [
            'clientid' => $clientId,
            'paymethodid' => $payMethodId,
            // Never silently force-remove a local record if the tokenized
            // gateway reports that the remote payment method was not deleted.
            'failonremotefailure' => true,
        ]);

        if (($result['result'] ?? '') === 'success') {
            $deleted++;
            continue;
        }

        $errors[] = dm1702_card_label($cards[$payMethodId], $payMethodId)
            . ': ' . (string) ($result['message'] ?? 'Deletion failed.');
    }

    if ($deleted > 0 && function_exists('logActivity')) {
        logActivity('Admin payment-method manager: deleted ' . $deleted . ' Pay Method(s) for Client #' . $clientId . '.');
    }

    if (!$errors) {
        dm1702_set_notice('success', $deleted . ' payment method' . ($deleted === 1 ? '' : 's') . ' deleted.');
    } elseif ($deleted > 0) {
        dm1702_set_notice(
            'warning',
            $deleted . ' payment method' . ($deleted === 1 ? '' : 's') . ' deleted. '
            . count($errors) . ' could not be deleted: ' . implode(' | ', $errors)
        );
    } else {
        dm1702_set_notice('danger', 'No payment methods were deleted. ' . implode(' | ', $errors));
    }

    dm1702_redirect($clientId);
});

/**
 * Render the bulk manager only on the Client Summary page. The markup is
 * returned in the admin footer and then moved immediately below the normal
 * summary-panels row so the manager can use the full page width without
 * editing the admin template.
 */
add_hook('AdminAreaFooterOutput', 1704, function (array $vars): string {
    if (!dm1702_is_client_summary_page($vars) || !dm1702_can_manage_cards($vars)) {
        return '';
    }

    $clientId = (int) ($_GET['userid'] ?? $_POST['userid'] ?? 0);
    if ($clientId <= 0) {
        return '';
    }

    $loaded = dm1702_get_cards($clientId);
    $cards = $loaded['cards'];
    $defaultPayMethodId = dm1702_get_default_paymethod_id($clientId);
    $csrfToken = function_exists('generate_token') ? (string) generate_token('plain') : '';

    $noticeHtml = '';
    if (isset($_SESSION['dm1702_bulk_cards_notice']) && is_array($_SESSION['dm1702_bulk_cards_notice'])) {
        $notice = $_SESSION['dm1702_bulk_cards_notice'];
        unset($_SESSION['dm1702_bulk_cards_notice']);
        $noticeType = in_array(($notice['type'] ?? ''), ['success', 'warning', 'danger'], true)
            ? (string) $notice['type']
            : 'danger';
        $noticeHtml = '<div class="alert alert-' . dm1702_escape($noticeType) . ' dm1702-notice" role="alert">'
            . dm1702_escape((string) ($notice['message'] ?? ''))
            . '</div>';
    }

    $rowsHtml = '';
    if ($loaded['result'] !== 'success') {
        $rowsHtml = '<tr><td colspan="8" class="text-center text-danger">'
            . dm1702_escape($loaded['message'])
            . '</td></tr>';
    } elseif (!$cards) {
        $rowsHtml = '<tr><td colspan="8" class="text-center">No saved payment methods.</td></tr>';
    } else {
        foreach ($cards as $card) {
            $id = (int) $card['id'];
            $type = (string) ($card['type'] ?? '');
            $isBankAccount = in_array($type, ['BankAccount', 'RemoteBankAccount'], true);
            $isRemote = in_array($type, ['RemoteCreditCard', 'RemoteBankAccount'], true);
            $isDefault = $id === $defaultPayMethodId;
            $cardType = trim((string) ($card['card_type'] ?? ''));
            $lastFour = preg_replace('/\D+/', '', (string) ($card['card_last_four'] ?? ''));
            $isPayPalAccount = dm1702_is_paypal_account($card);

            if ($isBankAccount) {
                $methodTitle = 'Bank Account';
                // WHMCS's native Pay Methods block has the authoritative masked bank
                // description. JavaScript copies that safe display text by Pay Method ID.
                $methodSecondaryHtml = '<span class="dm1702-muted dm1702-native-paymethod-description" data-paymethod-id="' . $id . '">Stored bank account</span>';
            } else {
                if ($cardType === '') {
                    $cardType = 'Credit Card';
                }
                $methodTitle = $cardType;
                if ($isPayPalAccount) {
                    // PayPal account Pay Methods can expose digits derived from the account
                    // identifier through card_last_four. Never present those as card digits.
                    $methodSecondaryHtml = '<span class="dm1702-muted dm1702-paypal-account dm1702-native-paymethod-description" data-paymethod-id="' . $id . '">PayPal account</span>';
                } else {
                    $lastFourDisplay = $lastFour !== '' ? '•••• ' . dm1702_escape($lastFour) : 'Stored Card';
                    $methodSecondaryHtml = '<span class="dm1702-muted">' . $lastFourDisplay . '</span>';
                }
            }

            $description = trim((string) $card['description']);
            $expiry = ($isBankAccount || $isPayPalAccount) ? '' : dm1702_display_expiry((string) $card['expiry_date']);
            $gateway = dm1702_gateway_display_name((string) $card['gateway_name']);
            if ($isPayPalAccount) {
                // PayPal Vault is a reusable tokenized PayPal account. Put the
                // payment type first so the label reads naturally in Admin.
                $storage = 'PayPal Tokenized';
            } elseif (!$isRemote && !$isBankAccount) {
                // Local card records in this installation are the Authorize.net
                // credit-card path. "Authorize" is more useful than "Local".
                $storage = 'Authorize';
            } else {
                $storage = $isRemote ? 'Tokenized' : 'Local';
                if ($gateway !== '') {
                    $storage .= ' · ' . $gateway;
                }
            }

            $addedRaw = (string) ($card['created_at'] ?? '');
            $updatedRaw = (string) ($card['updated_at'] ?? '');
            $addedDate = dm1702_display_paymethod_date($addedRaw);
            $updatedDate = dm1702_display_paymethod_date($updatedRaw);
            $addedSort = dm1702_paymethod_date_sort_key($addedRaw);
            $updatedSort = dm1702_paymethod_date_sort_key($updatedRaw);
            $addedHtml = $addedDate !== '' ? dm1702_escape($addedDate) : '<span class="dm1702-muted">—</span>';
            $updatedHtml = $updatedDate !== '' ? dm1702_escape($updatedDate) : '<span class="dm1702-muted">—</span>';

            $expiryHtml = '<span class="dm1702-muted">—</span>';
            if (!$isBankAccount && !$isPayPalAccount) {
                $expiryBadge = '';
                if ($expiry !== '' && dm1702_is_expired($expiry)) {
                    $expiryBadge = '<span class="label label-danger dm1702-expired-badge">Expired</span>';
                }
                $expiryAria = $cardType . ($lastFour !== '' ? ' ending in ' . $lastFour : '');
                $expiryHtml = '<div class="dm1702-expiry-wrap">'
                    . '<input type="text" class="form-control input-sm dm1702-expiry-input" name="expiry_by_paymethod[' . $id . ']" value="' . dm1702_escape($expiry) . '" data-original-expiry="' . dm1702_escape($expiry) . '" placeholder="MM/YY" maxlength="7" inputmode="numeric" aria-label="Expiration date for ' . dm1702_escape($expiryAria) . '">'
                    . $expiryBadge . '</div>';
            }

            $defaultLabel = $isDefault
                ? '<span class="label dm1702-default-badge"><i class="fas fa-check-circle"></i> Current</span>'
                : '';

            $rowsHtml .= '<tr data-paymethod-id="' . $id . '" data-paymethod-type="' . dm1702_escape($type) . '" data-expiry-sort="' . dm1702_expiry_sort_key($expiry) . '" data-added-sort="' . $addedSort . '" data-updated-sort="' . $updatedSort . '">'
                . '<td class="dm1702-check"><input type="checkbox" class="dm1702-delete-check" name="delete_paymethod_ids[]" value="' . $id . '" aria-label="Select payment method for deletion"></td>'
                . '<td><button type="button" class="dm1702-paymethod-open" data-paymethod-id="' . $id . '" title="Edit Pay Method"><span class="dm1702-method-title"><strong>' . dm1702_escape($methodTitle) . '</strong><span class="dm1702-edit-indicator" aria-hidden="true"><i class="fas fa-pencil-alt"></i></span></span><br>' . $methodSecondaryHtml . '</button></td>'
                . '<td>' . ($description !== '' ? dm1702_escape($description) : '<span class="dm1702-muted">—</span>') . '</td>'
                . '<td class="dm1702-expiry-cell">' . $expiryHtml . '</td>'
                . '<td class="dm1702-storage-cell"><span class="dm1702-storage-text">' . dm1702_escape($storage) . '</span><span class="label label-warning dm1702-inactive-gateway-badge" style="display:none">Inactive</span></td>'
                . '<td class="dm1702-date-cell">' . $addedHtml . '</td>'
                . '<td class="dm1702-date-cell">' . $updatedHtml . '</td>'
                . '<td class="dm1702-default-cell"><div class="dm1702-default-control"><span class="dm1702-current-slot">' . $defaultLabel . '</span><label title="Make this the default payment method"><input type="radio" name="default_paymethod_id" value="' . $id . '"' . ($isDefault ? ' checked' : '') . '> <span>Default</span></label></div></td>'
                . '</tr>';
        }
    }

    $cardCount = count($cards);

    $manager = $noticeHtml
        . '<div id="dm-bulk-credit-cards-1702" class="dm1702-card-manager" style="display:none">'
        . '<table width="100%" class="form dm1702-panel-shell">'
        . '<tr><td colspan="2" class="fieldarea dm1702-panel-title"><strong>Payment Methods</strong><span class="dm1702-count">' . $cardCount . ' saved</span></td></tr>'
        . '<tr><td align="center" class="dm1702-panel-content">'
        . '<form method="post" action="clientssummary.php?userid=' . $clientId . '" id="dm1702-card-form">'
        . '<input type="hidden" name="token" value="' . dm1702_escape($csrfToken) . '">'
        . '<input type="hidden" name="userid" value="' . $clientId . '">'
        . '<div class="dm1702-add-toolbar" aria-label="Add Pay Method">'
        . '<button type="button" id="dm1702-add-credit-card" class="btn dm1702-btn-add-paymethod" style="display:none"><i class="fas fa-plus"></i> Add Credit Card</button>'
        . '<button type="button" id="dm1702-add-bank-account" class="btn dm1702-btn-add-paymethod" style="display:none"><i class="fas fa-plus"></i> Add Bank Account</button>'
        . '</div>'
        . '<div class="table-responsive dm1702-table-wrap">'
        . '<table class="table table-themed table-striped table-hover dm1702-table">'
        . '<colgroup><col class="dm1702-col-check"><col class="dm1702-col-card"><col class="dm1702-col-description"><col class="dm1702-col-expiration"><col class="dm1702-col-storage"><col class="dm1702-col-added"><col class="dm1702-col-updated"><col class="dm1702-col-default"></colgroup>'
        . '<thead><tr>'
        . '<th class="dm1702-check"><input type="checkbox" id="dm1702-select-all" aria-label="Select all payment methods"></th>'
        . '<th>Payment Method</th><th>Description</th>'
        . '<th class="dm1702-sortable-heading dm1702-expiry-heading sorting" id="dm1702-expiry-sort-heading"><button type="button" id="dm1702-sort-expiry" class="dm1702-sort-button" aria-label="Sort by expiration date" aria-sort="none">Expiration</button></th>'
        . '<th>Storage / Gateway</th>'
        . '<th class="dm1702-sortable-heading dm1702-date-heading sorting" id="dm1702-added-sort-heading"><button type="button" id="dm1702-sort-added" class="dm1702-sort-button" aria-label="Sort by date added" aria-sort="none">Added</button></th>'
        . '<th class="dm1702-sortable-heading dm1702-date-heading sorting" id="dm1702-updated-sort-heading"><button type="button" id="dm1702-sort-updated" class="dm1702-sort-button" aria-label="Sort by last updated date" aria-sort="none">Last Updated</button></th>'
        . '<th class="dm1702-default-heading">Default</th>'
        . '</tr></thead><tbody>' . $rowsHtml . '</tbody>'
        . '<tfoot><tr class="dm1702-action-row">'
        . '<td colspan="3" class="dm1702-delete-action-cell"><button type="submit" name="dm1702_action" value="delete_selected" id="dm1702-delete-selected" class="btn dm1702-btn-delete" disabled><i class="fas fa-trash-alt"></i> Delete Selected</button> <span id="dm1702-selected-count" class="dm1702-selected-count">0 selected</span></td>'
        . '<td class="dm1702-expiry-action-cell"><button type="submit" name="dm1702_action" value="update_expirations" id="dm1702-save-expirations" class="btn dm1702-btn-save-expiry" disabled><i class="fas fa-save"></i> Save Expirations</button></td>'
        . '<td class="dm1702-cancel-action-cell"><button type="button" id="dm1702-cancel-changes" class="btn dm1702-btn-cancel" disabled><i class="fas fa-undo"></i> Cancel Changes</button></td>'
        . '<td colspan="2" class="dm1702-date-action-spacer"></td>'
        . '<td class="dm1702-default-action-cell"><button type="submit" name="dm1702_action" value="set_default" id="dm1702-set-default" class="btn dm1702-btn-default" disabled><i class="fas fa-check"></i> Set Default</button></td>'
        . '</tr></tfoot></table></div>'
        . '</form>'
        . '</td></tr></table></div>';

    return $manager . <<<'HTML'
<style id="dm1702-credit-card-styles">
#dm-bulk-credit-cards-1702.dm1702-card-manager {
    margin: 0 0 24px;
    width: 100%;
}
#dm-bulk-credit-cards-1702 .dm1702-panel-shell { width: 100%; }
#dm-bulk-credit-cards-1702 .dm1702-panel-title {
    position: relative;
    text-align: center !important;
}
#dm-bulk-credit-cards-1702 .dm1702-count {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 400;
    color: #666;
}
#dm-bulk-credit-cards-1702 .dm1702-panel-content { text-align: center; }
#dm-bulk-credit-cards-1702 .dm1702-add-toolbar {
    width: 1090px;
    max-width: 100%;
    margin: 0 auto 6px;
    text-align: right;
    white-space: nowrap;
}
#dm-bulk-credit-cards-1702 .dm1702-btn-add-paymethod {
    margin-left: 5px;
    background: #f58220;
    border-color: #f58220;
    color: #fff;
}
#dm-bulk-credit-cards-1702 .dm1702-btn-add-paymethod:hover,
#dm-bulk-credit-cards-1702 .dm1702-btn-add-paymethod:focus {
    background: #d8741f;
    border-color: #d8741f;
    color: #fff;
}
#dm-bulk-credit-cards-1702 .dm1702-storage-cell { white-space: nowrap; }
#dm-bulk-credit-cards-1702 .dm1702-inactive-gateway-badge {
    margin-left: 6px;
    vertical-align: middle;
}
.dm1702-native-paymethods-hidden { display: none !important; }
#dm-bulk-credit-cards-1702 .dm1702-table-wrap {
    overflow-x: auto;
    text-align: center;
}
#dm-bulk-credit-cards-1702 .dm1702-table {
    width: 1090px;
    min-width: 1090px;
    max-width: 1090px;
    margin: 0 auto 8px;
    table-layout: fixed;
}
#dm-bulk-credit-cards-1702 .dm1702-table col.dm1702-col-check { width: 30px; }
#dm-bulk-credit-cards-1702 .dm1702-table col.dm1702-col-card { width: 130px; }
#dm-bulk-credit-cards-1702 .dm1702-table col.dm1702-col-description { width: 160px; }
#dm-bulk-credit-cards-1702 .dm1702-table col.dm1702-col-expiration { width: 160px; }
#dm-bulk-credit-cards-1702 .dm1702-table col.dm1702-col-storage { width: 210px; }
#dm-bulk-credit-cards-1702 .dm1702-table col.dm1702-col-added { width: 120px; }
#dm-bulk-credit-cards-1702 .dm1702-table col.dm1702-col-updated { width: 130px; }
#dm-bulk-credit-cards-1702 .dm1702-table col.dm1702-col-default { width: 150px; }
#dm-bulk-credit-cards-1702 .dm1702-table th {
    white-space: nowrap;
    vertical-align: middle;
    text-align: left;
}
#dm-bulk-credit-cards-1702 .dm1702-table td {
    vertical-align: middle;
    text-align: left;
}
#dm-bulk-credit-cards-1702 .dm1702-check {
    white-space: nowrap;
    text-align: left;
}
#dm-bulk-credit-cards-1702 .dm1702-paymethod-open {
    display: inline-block;
    padding: 0;
    margin: 0;
    border: 0;
    background: transparent;
    color: #333;
    text-align: left;
    font: inherit;
    line-height: inherit;
    cursor: pointer;
}
#dm-bulk-credit-cards-1702 .dm1702-method-title {
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    gap: 5px;
}
#dm-bulk-credit-cards-1702 .dm1702-edit-indicator {
    color: #8a8a8a;
    font-size: 10px;
    line-height: 1;
}
#dm-bulk-credit-cards-1702 .dm1702-paymethod-open:hover .dm1702-edit-indicator,
#dm-bulk-credit-cards-1702 .dm1702-paymethod-open:focus .dm1702-edit-indicator {
    color: #f58220;
}
#dm-bulk-credit-cards-1702 .dm1702-paymethod-open:hover,
#dm-bulk-credit-cards-1702 .dm1702-paymethod-open:focus {
    color: #f58220;
    text-decoration: none;
    outline: none;
}
#dm-bulk-credit-cards-1702 .dm1702-paymethod-open:hover .dm1702-muted,
#dm-bulk-credit-cards-1702 .dm1702-paymethod-open:focus .dm1702-muted {
    color: #f58220;
}
#dm-bulk-credit-cards-1702 .dm1702-muted { color: #777; }
#dm-bulk-credit-cards-1702 .dm1702-date-cell {
    text-align: left;
    white-space: nowrap;
}
#dm-bulk-credit-cards-1702 .dm1702-date-action-spacer {
    background: #fafafa;
}
#dm-bulk-credit-cards-1702 .dm1702-default-cell,
#dm-bulk-credit-cards-1702 .dm1702-default-heading,
#dm-bulk-credit-cards-1702 .dm1702-default-action-cell {
    text-align: center;
    white-space: nowrap;
}
#dm-bulk-credit-cards-1702 .dm1702-default-control {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
#dm-bulk-credit-cards-1702 .dm1702-current-slot {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    width: 88px;
    margin-right: 8px;
}
#dm-bulk-credit-cards-1702 .dm1702-default-cell label {
    margin: 0;
    font-weight: 400;
    cursor: pointer;
}
#dm-bulk-credit-cards-1702 .dm1702-default-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 78px;
    height: 22px;
    padding: 3px 7px;
    box-sizing: border-box;
    background: #163a5f;
    color: #fff;
    font-size: 11px;
    line-height: 14px;
    white-space: nowrap;
    border-radius: 3px;
}
#dm-bulk-credit-cards-1702 .dm1702-expiry-cell,
#dm-bulk-credit-cards-1702 .dm1702-expiry-heading,
#dm-bulk-credit-cards-1702 .dm1702-expiry-action-cell { width: 160px; }
#dm-bulk-credit-cards-1702 .dm1702-expiry-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
}
#dm-bulk-credit-cards-1702 .dm1702-expiry-input {
    width: 82px;
    min-width: 82px;
    text-align: center;
}
#dm-bulk-credit-cards-1702 .dm1702-expired-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 20px;
    padding: 0 6px;
    line-height: 1;
    white-space: nowrap;
    position: relative;
    top: 1px;
}
#dm-bulk-credit-cards-1702 .dm1702-sortable-heading {
    padding-right: 30px !important;
    position: relative;
    cursor: pointer;
}
/* Reproduce WHMCS/DataTables native sort indicators without initializing this custom table as a DataTable. */
#dm-bulk-credit-cards-1702 .dm1702-sortable-heading.sorting:after,
#dm-bulk-credit-cards-1702 .dm1702-sortable-heading.sorting_asc:after,
#dm-bulk-credit-cards-1702 .dm1702-sortable-heading.sorting_desc:after {
    position: absolute;
    bottom: 5px;
    right: 8px;
    display: block;
    font-family: 'Glyphicons Halflings';
    opacity: .5;
    font-weight: 400;
}
#dm-bulk-credit-cards-1702 .dm1702-sortable-heading.sorting:after {
    opacity: .2;
    content: "\e150";
}
#dm-bulk-credit-cards-1702 .dm1702-sortable-heading.sorting_asc:after {
    content: "\e155";
}
#dm-bulk-credit-cards-1702 .dm1702-sortable-heading.sorting_desc:after {
    content: "\e156";
}
#dm-bulk-credit-cards-1702 .dm1702-sort-button {
    border: 0;
    padding: 0;
    margin: 0;
    background: transparent;
    color: inherit;
    font: inherit;
    font-weight: 600;
    cursor: pointer;
}
#dm-bulk-credit-cards-1702 .dm1702-sort-button:hover,
#dm-bulk-credit-cards-1702 .dm1702-sort-button:focus,
#dm-bulk-credit-cards-1702 .dm1702-sort-button:active {
    color: inherit;
    text-decoration: none;
    outline: none;
}
#dm-bulk-credit-cards-1702 .dm1702-action-row td {
    border-top: 2px solid #d6dbe1 !important;
    background: #fafafa;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
}
#dm-bulk-credit-cards-1702 .dm1702-delete-action-cell {
    white-space: nowrap;
    text-align: left;
}
#dm-bulk-credit-cards-1702 .dm1702-expiry-action-cell {
    text-align: left;
}
#dm-bulk-credit-cards-1702 .dm1702-default-action-cell {
    text-align: right;
}
#dm-bulk-credit-cards-1702 .dm1702-cancel-action-cell {
    text-align: left;
    white-space: nowrap;
}
#dm-bulk-credit-cards-1702 .dm1702-btn-default,
#dm-bulk-credit-cards-1702 .dm1702-btn-save-expiry {
    background: #f58220;
    border-color: #f58220;
    color: #fff;
    white-space: nowrap;
}
#dm-bulk-credit-cards-1702 .dm1702-btn-default:hover,
#dm-bulk-credit-cards-1702 .dm1702-btn-default:focus,
#dm-bulk-credit-cards-1702 .dm1702-btn-save-expiry:hover,
#dm-bulk-credit-cards-1702 .dm1702-btn-save-expiry:focus {
    background: #d8741f;
    border-color: #d8741f;
    color: #fff;
}
#dm-bulk-credit-cards-1702 .dm1702-btn-delete {
    background: #b94a48;
    border-color: #b94a48;
    color: #fff;
    white-space: nowrap;
}
#dm-bulk-credit-cards-1702 .dm1702-btn-delete:hover,
#dm-bulk-credit-cards-1702 .dm1702-btn-delete:focus {
    background: #a43f3d;
    border-color: #a43f3d;
    color: #fff;
}
#dm-bulk-credit-cards-1702 .dm1702-btn-cancel {
    background: #163a5f;
    border-color: #163a5f;
    color: #fff;
    white-space: nowrap;
}
#dm-bulk-credit-cards-1702 .dm1702-btn-cancel:hover,
#dm-bulk-credit-cards-1702 .dm1702-btn-cancel:focus {
    background: #214e7a;
    border-color: #214e7a;
    color: #fff;
}
#dm-bulk-credit-cards-1702 .dm1702-btn-delete[disabled],
#dm-bulk-credit-cards-1702 .dm1702-btn-default[disabled],
#dm-bulk-credit-cards-1702 .dm1702-btn-save-expiry[disabled],
#dm-bulk-credit-cards-1702 .dm1702-btn-cancel[disabled] { opacity: .5; }
#dm-bulk-credit-cards-1702 .dm1702-selected-count { color: #666; }
.dm1702-notice { margin: 0 15px 12px; }
</style>
<script id="dm1702-credit-card-script">
(function () {
    'use strict';

    var manager = document.getElementById('dm-bulk-credit-cards-1702');
    if (!manager) {
        return;
    }

    function placeManager() {
        var panels = document.querySelector('#clientsummarycontainer .row.client-summary-panels');
        if (panels && panels.parentNode) {
            panels.parentNode.insertBefore(manager, panels.nextSibling);
        }
        manager.style.display = '';
    }

    function findNativePayMethodsBox() {
        var anchor = document.getElementById('btnAddCcPayMethod')
            || document.getElementById('btnAddBankAccountPayMethod')
            || document.getElementById('btnNoGateways')
            || document.querySelector('[id^="btnPayMethodDetails"]');
        if (anchor && anchor.closest) {
            var anchorBox = anchor.closest('.clientssummarybox');
            if (anchorBox) {
                var anchorTitle = anchorBox.querySelector('.title');
                if (anchorTitle && String(anchorTitle.textContent || '').trim() === 'Pay Methods') {
                    return anchorBox;
                }
            }
        }

        var boxes = document.querySelectorAll('.clientssummarybox');
        for (var i = 0; i < boxes.length; i++) {
            var title = boxes[i].querySelector('.title');
            if (title && String(title.textContent || '').trim() === 'Pay Methods') {
                return boxes[i];
            }
        }
        return null;
    }

    function populateNativePayMethodDetails() {
        var labels = manager.querySelectorAll('.dm1702-native-paymethod-description[data-paymethod-id]');
        for (var i = 0; i < labels.length; i++) {
            var label = labels[i];
            var payMethodId = label.getAttribute('data-paymethod-id');
            if (!payMethodId) {
                continue;
            }

            // The hidden native WHMCS Pay Methods block remains the authoritative
            // safe display source for PayPal and bank-account descriptions.
            var nativeLink = document.getElementById('btnPayMethodDetails' + payMethodId);
            if (!nativeLink) {
                continue;
            }

            var nativeText = String(nativeLink.textContent || '').replace(/\s+/g, ' ').trim();
            if (nativeText) {
                if (label.classList.contains('dm1702-paypal-account')) {
                    var emailMatch = nativeText.match(/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i);
                    label.textContent = emailMatch ? emailMatch[0] : nativeText;
                } else {
                    label.textContent = nativeText;
                }
            }
        }

        var rows = manager.querySelectorAll('tr[data-paymethod-id]');
        for (var r = 0; r < rows.length; r++) {
            var id = rows[r].getAttribute('data-paymethod-id');
            var link = id ? document.getElementById('btnPayMethodDetails' + id) : null;
            if (!link || !link.closest) {
                continue;
            }
            var nativeCell = link.closest('.client-paymethod');
            if (nativeCell && nativeCell.classList.contains('gateway-inactive')) {
                rows[r].classList.add('dm1702-gateway-inactive');
                var badge = rows[r].querySelector('.dm1702-inactive-gateway-badge');
                if (badge) {
                    badge.style.display = '';
                    if (nativeCell.getAttribute('title')) {
                        badge.setAttribute('title', nativeCell.getAttribute('title'));
                    } else if (link.getAttribute('title')) {
                        badge.setAttribute('title', link.getAttribute('title'));
                    }
                }
            }
        }
    }

    function suppressNativePayMethodsBox() {
        var box = findNativePayMethodsBox();
        if (box) {
            box.classList.add('dm1702-native-paymethods-hidden');
            box.setAttribute('aria-hidden', 'true');
        }
    }

    function syncNativeAddPayMethodButtons() {
        var addCreditButton = document.getElementById('dm1702-add-credit-card');
        var addBankButton = document.getElementById('dm1702-add-bank-account');
        var nativeAddCredit = document.getElementById('btnAddCcPayMethod');
        var nativeAddBank = document.getElementById('btnAddBankAccountPayMethod');
        var nativeNoGateways = document.getElementById('btnNoGateways');

        if (addCreditButton) {
            if (nativeAddCredit) {
                addCreditButton.style.display = '';
                addCreditButton.disabled = false;
            } else if (nativeNoGateways) {
                addCreditButton.style.display = '';
                addCreditButton.disabled = true;
                var noGatewayTitle = nativeNoGateways.getAttribute('title');
                if (noGatewayTitle) {
                    addCreditButton.setAttribute('title', noGatewayTitle);
                }
            } else {
                addCreditButton.style.display = 'none';
            }
        }
        if (addBankButton) {
            addBankButton.style.display = nativeAddBank ? '' : 'none';
        }
    }

    var nativePayMethodsChanged = false;
    function watchNativePayMethodsRefresh() {
        var nativeTable = document.getElementById('tablePayMethods');
        if (!nativeTable || !window.MutationObserver) {
            return;
        }
        var observer = new MutationObserver(function () {
            if (!nativePayMethodsChanged) {
                return;
            }
            // WHMCS refreshes #tablePayMethods after a successful native modal save.
            // Reload once so the replacement manager immediately reflects the same data.
            window.setTimeout(function () { window.location.reload(); }, 80);
        });
        observer.observe(nativeTable, { childList: true, subtree: true });
    }

    function selectedChecks() {
        return manager.querySelectorAll('.dm1702-delete-check:checked');
    }

    function updateDeleteState() {
        var count = selectedChecks().length;
        var button = document.getElementById('dm1702-delete-selected');
        var counter = document.getElementById('dm1702-selected-count');
        var selectAll = document.getElementById('dm1702-select-all');
        var all = manager.querySelectorAll('.dm1702-delete-check');

        if (button) {
            button.disabled = count === 0;
        }
        if (counter) {
            counter.textContent = count + ' selected';
        }
        if (selectAll) {
            selectAll.checked = all.length > 0 && count === all.length;
            selectAll.indeterminate = count > 0 && count < all.length;
        }
    }

    function normalizedExpiryForComparison(value) {
        var text = String(value || '').trim();
        if (text === '') {
            return '';
        }

        var match = text.match(/^(\d{1,2})\s*\/\s*(\d{2}|\d{4})$/);
        if (!match) {
            match = text.match(/^(\d{2})(\d{2})$/);
        }
        if (!match) {
            return text;
        }

        var month = parseInt(match[1], 10);
        var year = parseInt(match[2], 10);
        if (month < 1 || month > 12) {
            return text;
        }
        if (year >= 2000 && year <= 2099) {
            year -= 2000;
        }
        if (year < 0 || year > 99) {
            return text;
        }

        return (month < 10 ? '0' : '') + month + (year < 10 ? '0' : '') + year;
    }

    function updateExpirationSaveState() {
        var button = document.getElementById('dm1702-save-expirations');
        if (!button) {
            return;
        }

        var inputs = manager.querySelectorAll('.dm1702-expiry-input');
        var changed = false;
        for (var i = 0; i < inputs.length; i++) {
            var original = inputs[i].getAttribute('data-original-expiry') || '';
            if (normalizedExpiryForComparison(inputs[i].value) !== normalizedExpiryForComparison(original)) {
                changed = true;
                break;
            }
        }
        button.disabled = !changed;
    }

    var initialDefaultPayMethodId = '';

    function captureInitialDefault() {
        var selected = manager.querySelector('input[name="default_paymethod_id"]:checked');
        initialDefaultPayMethodId = selected ? String(selected.value) : '';
    }

    function updateDefaultSaveState() {
        var button = document.getElementById('dm1702-set-default');
        if (!button) {
            return;
        }

        var selected = manager.querySelector('input[name="default_paymethod_id"]:checked');
        var selectedId = selected ? String(selected.value) : '';
        button.disabled = selectedId === '' || selectedId === initialDefaultPayMethodId;
    }

    function hasExpirationChanges() {
        var inputs = manager.querySelectorAll('.dm1702-expiry-input');
        for (var i = 0; i < inputs.length; i++) {
            var original = inputs[i].getAttribute('data-original-expiry') || '';
            if (normalizedExpiryForComparison(inputs[i].value) !== normalizedExpiryForComparison(original)) {
                return true;
            }
        }
        return false;
    }

    function hasDefaultChange() {
        var selected = manager.querySelector('input[name="default_paymethod_id"]:checked');
        var selectedId = selected ? String(selected.value) : '';
        return selectedId !== '' && selectedId !== initialDefaultPayMethodId;
    }

    function updateCancelState() {
        var button = document.getElementById('dm1702-cancel-changes');
        if (!button) {
            return;
        }
        button.disabled = selectedChecks().length === 0 && !hasExpirationChanges() && !hasDefaultChange();
    }

    function cancelPendingChanges() {
        var checks = manager.querySelectorAll('.dm1702-delete-check');
        for (var i = 0; i < checks.length; i++) {
            checks[i].checked = false;
        }

        var selectAll = document.getElementById('dm1702-select-all');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }

        var inputs = manager.querySelectorAll('.dm1702-expiry-input');
        for (var j = 0; j < inputs.length; j++) {
            inputs[j].value = inputs[j].getAttribute('data-original-expiry') || '';
        }

        var originalDefault = initialDefaultPayMethodId !== ''
            ? manager.querySelector('input[name="default_paymethod_id"][value="' + initialDefaultPayMethodId + '"]')
            : null;
        if (originalDefault) {
            originalDefault.checked = true;
        }

        updateDeleteState();
        updateExpirationSaveState();
        updateDefaultSaveState();
        updateCancelState();
    }

    function expirySortKey(value) {
        var text = String(value || '').trim();
        var match = text.match(/^(\d{1,2})\s*\/\s*(\d{2}|\d{4})$/);
        if (!match) {
            return 999999;
        }
        var month = parseInt(match[1], 10);
        var year = parseInt(match[2], 10);
        if (month < 1 || month > 12) {
            return 999999;
        }
        if (year < 100) {
            year += 2000;
        }
        return (year * 100) + month;
    }

    function setSortHeadingState(activeHeadingId, direction) {
        var headings = manager.querySelectorAll('.dm1702-sortable-heading');
        for (var i = 0; i < headings.length; i++) {
            headings[i].classList.remove('sorting', 'sorting_asc', 'sorting_desc');
            var button = headings[i].querySelector('.dm1702-sort-button');
            if (headings[i].id === activeHeadingId) {
                headings[i].classList.add(direction === 'asc' ? 'sorting_asc' : 'sorting_desc');
                if (button) {
                    button.setAttribute('aria-sort', direction === 'asc' ? 'ascending' : 'descending');
                }
            } else {
                headings[i].classList.add('sorting');
                if (button) {
                    button.setAttribute('data-direction', 'none');
                    button.setAttribute('aria-sort', 'none');
                }
            }
        }
    }

    function appendSortedRows(tbody, rows) {
        for (var i = 0; i < rows.length; i++) {
            tbody.appendChild(rows[i]);
        }
    }

    function sortByExpiration() {
        var button = document.getElementById('dm1702-sort-expiry');
        var tbody = manager.querySelector('.dm1702-table tbody');
        if (!button || !tbody) {
            return;
        }

        var current = button.getAttribute('data-direction') || 'none';
        var direction = current === 'asc' ? 'desc' : 'asc';
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-paymethod-id]'));

        rows.sort(function (a, b) {
            var inputA = a.querySelector('.dm1702-expiry-input');
            var inputB = b.querySelector('.dm1702-expiry-input');
            var keyA = expirySortKey(inputA ? inputA.value : '');
            var keyB = expirySortKey(inputB ? inputB.value : '');

            // Keep missing/invalid dates at the bottom in either direction.
            var invalidA = keyA === 999999;
            var invalidB = keyB === 999999;
            if (invalidA && !invalidB) {
                return 1;
            }
            if (!invalidA && invalidB) {
                return -1;
            }
            if (keyA === keyB) {
                return parseInt(a.getAttribute('data-paymethod-id'), 10) - parseInt(b.getAttribute('data-paymethod-id'), 10);
            }
            return direction === 'asc' ? keyA - keyB : keyB - keyA;
        });

        appendSortedRows(tbody, rows);
        button.setAttribute('data-direction', direction);
        setSortHeadingState('dm1702-expiry-sort-heading', direction);
    }

    function sortByPayMethodDate(buttonId, headingId, dataAttribute) {
        var button = document.getElementById(buttonId);
        var tbody = manager.querySelector('.dm1702-table tbody');
        if (!button || !tbody) {
            return;
        }

        var current = button.getAttribute('data-direction') || 'none';
        var direction = current === 'asc' ? 'desc' : 'asc';
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-paymethod-id]'));

        rows.sort(function (a, b) {
            var keyA = parseInt(a.getAttribute(dataAttribute) || '0', 10);
            var keyB = parseInt(b.getAttribute(dataAttribute) || '0', 10);
            var invalidA = !keyA;
            var invalidB = !keyB;

            // Keep missing/invalid timestamps at the bottom in either direction.
            if (invalidA && !invalidB) {
                return 1;
            }
            if (!invalidA && invalidB) {
                return -1;
            }
            if (keyA === keyB) {
                return parseInt(a.getAttribute('data-paymethod-id'), 10) - parseInt(b.getAttribute('data-paymethod-id'), 10);
            }
            return direction === 'asc' ? keyA - keyB : keyB - keyA;
        });

        appendSortedRows(tbody, rows);
        button.setAttribute('data-direction', direction);
        setSortHeadingState(headingId, direction);
    }

    manager.addEventListener('click', function (event) {
        var addCredit = event.target && event.target.closest
            ? event.target.closest('#dm1702-add-credit-card')
            : null;
        if (addCredit && manager.contains(addCredit)) {
            event.preventDefault();
            var nativeAddCredit = document.getElementById('btnAddCcPayMethod');
            if (nativeAddCredit) {
                nativePayMethodsChanged = true;
                nativeAddCredit.click();
            }
            return;
        }

        var addBank = event.target && event.target.closest
            ? event.target.closest('#dm1702-add-bank-account')
            : null;
        if (addBank && manager.contains(addBank)) {
            event.preventDefault();
            var nativeAddBank = document.getElementById('btnAddBankAccountPayMethod');
            if (nativeAddBank) {
                nativePayMethodsChanged = true;
                nativeAddBank.click();
            }
            return;
        }

        var opener = event.target && event.target.closest
            ? event.target.closest('.dm1702-paymethod-open[data-paymethod-id]')
            : null;
        if (!opener || !manager.contains(opener)) {
            return;
        }

        event.preventDefault();
        var payMethodId = opener.getAttribute('data-paymethod-id');
        if (!payMethodId) {
            return;
        }

        // Trigger WHMCS's existing Pay Methods control for this exact Pay Method.
        // WHMCS remains responsible for the native modal, validation and updates.
        var nativeLink = document.getElementById('btnPayMethodDetails' + payMethodId);
        if (nativeLink) {
            nativePayMethodsChanged = true;
            nativeLink.click();
        }
    });

    manager.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'dm1702-select-all') {
            var checks = manager.querySelectorAll('.dm1702-delete-check');
            for (var i = 0; i < checks.length; i++) {
                checks[i].checked = event.target.checked;
            }
        }
        updateDeleteState();

        if (event.target && event.target.matches('input[name="default_paymethod_id"]')) {
            updateDefaultSaveState();
        }
        if (event.target && event.target.classList.contains('dm1702-expiry-input')) {
            updateExpirationSaveState();
        }
        updateCancelState();
    });

    manager.addEventListener('input', function (event) {
        if (event.target && event.target.classList.contains('dm1702-expiry-input')) {
            updateExpirationSaveState();
        }
        updateCancelState();
    });

    function syncToProductsServicesWidth() {
        var nativeTable = document.getElementById('summaryServices');
        var ourTable = manager.querySelector('.dm1702-table');
        var addToolbar = manager.querySelector('.dm1702-add-toolbar');
        if (!nativeTable || !ourTable) {
            return;
        }

        var rect = nativeTable.getBoundingClientRect();
        if (!rect || rect.width < 100) {
            return;
        }

        var width = Math.round(rect.width);
        ourTable.style.width = width + 'px';
        ourTable.style.minWidth = width + 'px';
        ourTable.style.maxWidth = width + 'px';
        if (addToolbar) {
            addToolbar.style.width = width + 'px';
        }
    }

    var nativeServicesTable = document.getElementById('summaryServices');
    if (nativeServicesTable && window.ResizeObserver) {
        var dm1702WidthObserver = new ResizeObserver(syncToProductsServicesWidth);
        dm1702WidthObserver.observe(nativeServicesTable);
    }

    var sortHeading = document.getElementById('dm1702-expiry-sort-heading');
    if (sortHeading) {
        sortHeading.addEventListener('click', sortByExpiration);
    }

    var addedSortHeading = document.getElementById('dm1702-added-sort-heading');
    if (addedSortHeading) {
        addedSortHeading.addEventListener('click', function () {
            sortByPayMethodDate('dm1702-sort-added', 'dm1702-added-sort-heading', 'data-added-sort');
        });
    }

    var updatedSortHeading = document.getElementById('dm1702-updated-sort-heading');
    if (updatedSortHeading) {
        updatedSortHeading.addEventListener('click', function () {
            sortByPayMethodDate('dm1702-sort-updated', 'dm1702-updated-sort-heading', 'data-updated-sort');
        });
    }

    var cancelButton = document.getElementById('dm1702-cancel-changes');
    if (cancelButton) {
        cancelButton.addEventListener('click', cancelPendingChanges);
    }

    var form = document.getElementById('dm1702-card-form');
    if (form) {
        form.addEventListener('submit', function (event) {
            var submitter = event.submitter || document.activeElement;
            if (!submitter || submitter.value !== 'delete_selected') {
                return;
            }

            var count = selectedChecks().length;
            if (count === 0) {
                event.preventDefault();
                return;
            }

            var message = 'Delete ' + count + ' selected payment method' + (count === 1 ? '' : 's') + '? This cannot be undone.';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    }

    function initializeManager() {
        placeManager();
        populateNativePayMethodDetails();
        syncNativeAddPayMethodButtons();
        watchNativePayMethodsRefresh();
        suppressNativePayMethodsBox();
        captureInitialDefault();
        updateDeleteState();
        updateExpirationSaveState();
        updateDefaultSaveState();
        updateCancelState();
        syncToProductsServicesWidth();
        window.setTimeout(syncToProductsServicesWidth, 100);
        window.setTimeout(syncToProductsServicesWidth, 400);
        window.setTimeout(syncToProductsServicesWidth, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeManager);
    } else {
        initializeManager();
    }
    window.addEventListener('resize', syncToProductsServicesWidth);
}());
</script>
HTML;
});
