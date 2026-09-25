<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

use RuntimeException;
use Throwable;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

require_once __DIR__ . '/InvoiceFixText.php';

final class InvoiceFixService
{
    public const MODULE = 'invoicefix';
    public const LOG_TABLE = 'mod_invoicefix_log';

    /**
     * Return the current administrator context and permissions.
     *
     * @return array{admin_id:int,username:string,role_id:int,permissions:string[]}
     */
    public static function adminContext(): array
    {
        $adminId = (int) ($_SESSION['adminid'] ?? 0);
        if ($adminId <= 0) {
            throw new RuntimeException(InvoiceFixText::get('error_admin_required'));
        }

        $admin = Capsule::table('tbladmins')
            ->select(['id', 'username', 'roleid'])
            ->where('id', $adminId)
            ->first();

        if (!$admin || trim((string) $admin->username) === '') {
            throw new RuntimeException(InvoiceFixText::get('error_admin_load'));
        }

        if (!function_exists('localAPI')) {
            throw new RuntimeException(InvoiceFixText::get('error_local_api_unavailable'));
        }

        $details = localAPI('GetAdminDetails', [], (string) $admin->username);
        if (($details['result'] ?? '') !== 'success') {
            throw new RuntimeException(InvoiceFixText::get('error_permissions_unverified'));
        }

        $permissions = array_values(array_filter(array_map(
            static fn ($permission): string => trim((string) $permission),
            explode(',', (string) ($details['allowedpermissions'] ?? ''))
        )));

        return [
            'admin_id' => $adminId,
            'username' => (string) $admin->username,
            'role_id' => (int) $admin->roleid,
            'permissions' => $permissions,
        ];
    }

    /**
     * Require both invoice creation and invoice management permissions.
     * WHMCS has used singular and plural labels across versions.
     *
     * @param string[] $permissions
     */
    public static function hasRequiredInvoicePermissions(array $permissions): bool
    {
        $canCreate = self::hasAnyPermission($permissions, [
            'Create Invoice',
            'Create Invoices',
        ]);

        $canManage = self::hasAnyPermission($permissions, [
            'Manage Invoice',
            'Manage Invoices',
        ]);

        return $canCreate && $canManage;
    }

    /**
     * Confirm that the administrator's role has access to InvoiceFix.
     */
    public static function roleHasModuleAccess(int $roleId): bool
    {
        if ($roleId <= 0) {
            return false;
        }

        try {
            $access = Capsule::table('tbladdonmodules')
                ->where('module', self::MODULE)
                ->where('setting', 'access')
                ->value('value');

            if ($access === null || trim((string) $access) === '') {
                return false;
            }

            $roleIds = array_map(
                'intval',
                preg_split('/[^0-9]+/', (string) $access, -1, PREG_SPLIT_NO_EMPTY) ?: []
            );

            return in_array($roleId, $roleIds, true);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Read an InvoiceFix addon setting from WHMCS.
     */
    public static function moduleSetting(string $name, string $default = ''): string
    {
        try {
            $value = Capsule::table('tbladdonmodules')
                ->where('module', self::MODULE)
                ->where('setting', $name)
                ->value('value');

            if ($value === null) {
                return $default;
            }

            return (string) $value;
        } catch (Throwable $e) {
            return $default;
        }
    }

    public static function moduleSettingEnabled(string $name, bool $default = false): bool
    {
        $fallback = $default ? 'on' : '';
        $value = strtolower(trim(self::moduleSetting($name, $fallback)));

        return in_array($value, ['1', 'on', 'yes', 'true', 'enabled'], true);
    }

    /**
     * Create a true Draft invoice through the supported WHMCS Local API.
     * The original invoice, transactions, credit, and ledger are never changed.
     *
     * @return array{source_invoice_id:int,draft_invoice_id:int,source_status:string,warning:string}
     */
    public static function createEditableDraft(int $sourceInvoiceId, array $admin): array
    {
        if ($sourceInvoiceId <= 0) {
            throw new RuntimeException(InvoiceFixText::get('error_invalid_source_id'));
        }

        $source = Capsule::table('tblinvoices')->where('id', $sourceInvoiceId)->first();
        if (!$source) {
            throw new RuntimeException(InvoiceFixText::get('error_source_not_found'));
        }

        $sourceStatus = trim((string) ($source->status ?? ''));
        if (strcasecmp($sourceStatus, 'Draft') === 0) {
            throw new RuntimeException(InvoiceFixText::get('error_already_draft'));
        }

        $sourceItems = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $sourceInvoiceId)
            ->orderBy('id')
            ->get();

        if ($sourceItems->count() === 0) {
            throw new RuntimeException(InvoiceFixText::get('error_no_line_items'));
        }

        foreach ($sourceItems as $item) {
            if (strcasecmp((string) ($item->type ?? ''), 'Invoice') === 0) {
                throw new RuntimeException(InvoiceFixText::get('error_mass_pay'));
            }
        }

        $existingReplacement = self::replacementForSource($sourceInvoiceId);
        if ($existingReplacement !== null) {
            throw new RuntimeException(InvoiceFixText::get('error_existing_replacement', [
                'invoice_id' => (int) $existingReplacement['invoice_id'],
            ]));
        }

        $postData = [
            'userid' => (int) ($source->userid ?? 0),
            'draft' => true,
            'autoapplycredit' => false,
        ];

        if ($postData['userid'] <= 0) {
            throw new RuntimeException(InvoiceFixText::get('error_invalid_client'));
        }

        self::copyApiField($postData, 'paymentmethod', $source->paymentmethod ?? null);
        self::copyApiField($postData, 'taxrate', $source->taxrate ?? null);
        self::copyApiField($postData, 'taxrate2', $source->taxrate2 ?? null);
        self::copyDateField($postData, 'date', $source->date ?? null);
        self::copyDateField($postData, 'duedate', $source->duedate ?? null);
        self::copyApiField($postData, 'notes', $source->notes ?? null, true);

        $itemNumber = 1;
        foreach ($sourceItems as $item) {
            $postData['itemdescription' . $itemNumber] = (string) ($item->description ?? '');
            $postData['itemamount' . $itemNumber] = (string) ($item->amount ?? '0.00');
            $postData['itemtaxed' . $itemNumber] = !empty($item->taxed) ? 1 : 0;
            $itemNumber++;
        }

        $result = localAPI('CreateInvoice', $postData, (string) $admin['username']);
        if (($result['result'] ?? '') !== 'success' || (int) ($result['invoiceid'] ?? 0) <= 0) {
            $message = trim((string) ($result['message'] ?? $result['error'] ?? InvoiceFixText::get('api_unknown_error')));
            throw new RuntimeException(InvoiceFixText::get('error_api_create', ['message' => $message]));
        }

        $draftInvoiceId = (int) $result['invoiceid'];
        $warning = '';

        try {
            self::finalizeDraftItemRelationships($sourceItems->all(), $draftInvoiceId);
            self::copyAdminOnlyInvoiceNotes($source, $draftInvoiceId);
        } catch (Throwable $e) {
            $warning = InvoiceFixText::get('error_finalize', [
                'draft_id' => $draftInvoiceId,
                'details' => $e->getMessage(),
            ]);

            self::activityLog(
                InvoiceFixText::get('activity_finalize_warning', [
                    'draft_id' => $draftInvoiceId,
                    'source_id' => $sourceInvoiceId,
                    'details' => $e->getMessage(),
                ]),
                (int) $admin['admin_id']
            );

            self::writeLog(
                $sourceInvoiceId,
                $draftInvoiceId,
                (int) $admin['admin_id'],
                $sourceStatus,
                (string) ($source->total ?? '0.00'),
                'warning',
                $warning
            );

            throw new RuntimeException($warning);
        }

        self::activityLog(
            InvoiceFixText::get('activity_created', [
                'draft_id' => $draftInvoiceId,
                'source_id' => $sourceInvoiceId,
                'source_status' => $sourceStatus,
            ]),
            (int) $admin['admin_id']
        );

        try {
            self::writeLog(
                $sourceInvoiceId,
                $draftInvoiceId,
                (int) $admin['admin_id'],
                $sourceStatus,
                (string) ($source->total ?? '0.00'),
                'success',
                InvoiceFixText::get('audit_created_message')
            );
        } catch (Throwable $e) {
            $warning = InvoiceFixText::get('error_audit_write', ['details' => $e->getMessage()]);
            self::activityLog(InvoiceFixText::get('activity_audit_warning', [
                'draft_id' => $draftInvoiceId,
                'details' => $e->getMessage(),
            ]), (int) $admin['admin_id']);
        }

        return [
            'source_invoice_id' => $sourceInvoiceId,
            'draft_invoice_id' => $draftInvoiceId,
            'source_status' => $sourceStatus,
            'warning' => $warning,
        ];
    }

    /**
     * Return the newest InvoiceFix-created Draft that is still in Draft status.
     */
    public static function activeDraftForSource(int $sourceInvoiceId): ?int
    {
        if (!self::logTableExists()) {
            return null;
        }

        $rows = Capsule::table(self::LOG_TABLE)
            ->where('source_invoice_id', $sourceInvoiceId)
            ->whereIn('result', ['success', 'warning'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        foreach ($rows as $row) {
            $draftId = (int) ($row->draft_invoice_id ?? 0);
            if ($draftId <= 0) {
                continue;
            }

            $status = Capsule::table('tblinvoices')->where('id', $draftId)->value('status');
            if (is_string($status) && strcasecmp($status, 'Draft') === 0) {
                return $draftId;
            }
        }

        return null;
    }

    /**
     * Return the newest existing replacement invoice for a source invoice.
     *
     * @return array{invoice_id:int,status:string,result:string}|null
     */
    public static function replacementForSource(int $sourceInvoiceId): ?array
    {
        if (!self::logTableExists()) {
            return null;
        }

        $rows = Capsule::table(self::LOG_TABLE)
            ->where('source_invoice_id', $sourceInvoiceId)
            ->whereIn('result', ['success', 'warning'])
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        foreach ($rows as $row) {
            $replacementId = (int) ($row->draft_invoice_id ?? 0);
            if ($replacementId <= 0) {
                continue;
            }

            $status = Capsule::table('tblinvoices')
                ->where('id', $replacementId)
                ->value('status');

            if ($status !== null) {
                return [
                    'invoice_id' => $replacementId,
                    'status' => trim((string) $status),
                    'result' => (string) ($row->result ?? ''),
                ];
            }
        }

        return null;
    }

    /**
     * Find the source invoice for an InvoiceFix-created draft.
     *
     * @return object|null
     */
    public static function sourceForDraft(int $draftInvoiceId)
    {
        if (!self::logTableExists()) {
            return null;
        }

        return Capsule::table(self::LOG_TABLE)
            ->where('draft_invoice_id', $draftInvoiceId)
            ->orderByDesc('id')
            ->first();
    }

    public static function logTableExists(): bool
    {
        try {
            return Capsule::schema()->hasTable(self::LOG_TABLE);
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function activityLog(string $message, int $adminId): void
    {
        if (function_exists('logAdminActivity')) {
            logAdminActivity($message, $adminId);
            return;
        }

        if (function_exists('logActivity')) {
            logActivity($message, 0);
        }
    }

    private static function finalizeDraftItemRelationships(array $sourceItems, int $draftInvoiceId): void
    {
        $draft = Capsule::table('tblinvoices')->where('id', $draftInvoiceId)->first();
        if (!$draft || strcasecmp((string) ($draft->status ?? ''), 'Draft') !== 0) {
            throw new RuntimeException(InvoiceFixText::get('error_created_not_draft'));
        }

        $targetItems = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $draftInvoiceId)
            ->orderBy('id')
            ->get()
            ->all();

        if (count($sourceItems) !== count($targetItems)) {
            throw new RuntimeException(InvoiceFixText::get('error_line_count_mismatch', [
                'source_count' => count($sourceItems),
                'target_count' => count($targetItems),
            ]));
        }

        $optionalColumns = ['userid', 'type', 'relid', 'duedate', 'paymentmethod', 'notes'];
        $availableColumns = [];
        foreach ($optionalColumns as $column) {
            if (Capsule::schema()->hasColumn('tblinvoiceitems', $column)) {
                $availableColumns[] = $column;
            }
        }

        Capsule::connection()->transaction(function () use ($sourceItems, $targetItems, $availableColumns): void {
            foreach ($sourceItems as $index => $sourceItem) {
                $targetItem = $targetItems[$index];
                $update = [];

                foreach ($availableColumns as $column) {
                    if (property_exists($sourceItem, $column)) {
                        $update[$column] = $sourceItem->{$column};
                    }
                }

                if ($update !== []) {
                    Capsule::table('tblinvoiceitems')
                        ->where('id', (int) $targetItem->id)
                        ->update($update);
                }
            }
        });
    }

    private static function copyAdminOnlyInvoiceNotes(object $source, int $draftInvoiceId): void
    {
        if (!Capsule::schema()->hasColumn('tblinvoices', 'adminnotes') || !property_exists($source, 'adminnotes')) {
            return;
        }

        Capsule::table('tblinvoices')
            ->where('id', $draftInvoiceId)
            ->update(['adminnotes' => $source->adminnotes]);
    }

    private static function copyApiField(array &$target, string $key, $value, bool $allowEmpty = false): void
    {
        if ($value === null) {
            return;
        }

        $stringValue = (string) $value;
        if (!$allowEmpty && trim($stringValue) === '') {
            return;
        }

        $target[$key] = $stringValue;
    }

    private static function copyDateField(array &$target, string $key, $value): void
    {
        $date = trim((string) $value);
        if ($date === '' || $date === '0000-00-00' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return;
        }

        $target[$key] = $date;
    }

    /**
     * @param string[] $permissions
     * @param string[] $candidates
     */
    private static function hasAnyPermission(array $permissions, array $candidates): bool
    {
        $normalized = array_map('strtolower', $permissions);
        foreach ($candidates as $candidate) {
            if (in_array(strtolower($candidate), $normalized, true)) {
                return true;
            }
        }

        return false;
    }

    private static function writeLog(
        int $sourceInvoiceId,
        int $draftInvoiceId,
        int $adminId,
        string $sourceStatus,
        string $sourceTotal,
        string $result,
        string $message
    ): void {
        if (!self::logTableExists()) {
            throw new RuntimeException(InvoiceFixText::get('error_log_table_missing'));
        }

        Capsule::table(self::LOG_TABLE)->updateOrInsert(
            ['draft_invoice_id' => $draftInvoiceId],
            [
                'source_invoice_id' => $sourceInvoiceId,
                'admin_id' => $adminId,
                'source_status' => $sourceStatus,
                'source_total' => $sourceTotal,
                'result' => $result,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        );
    }
}
