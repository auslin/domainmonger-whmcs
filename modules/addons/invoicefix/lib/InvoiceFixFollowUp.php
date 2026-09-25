<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

use RuntimeException;
use Throwable;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

require_once __DIR__ . '/InvoiceFixService.php';
require_once __DIR__ . '/InvoiceFixText.php';

/**
 * Administrator-only follow-up assignment stored with an InvoiceFix audit row.
 *
 * Follow-up information never changes a WHMCS invoice, transaction, payment,
 * credit, service, domain, or ledger record.
 */
final class InvoiceFixFollowUp
{
    /**
     * @return array<int,object>
     */
    public static function activeAdministrators(): array
    {
        try {
            return Capsule::table('tbladmins')
                ->where('disabled', 0)
                ->orderBy('firstname')
                ->orderBy('lastname')
                ->orderBy('username')
                ->get(['id', 'username', 'firstname', 'lastname'])
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function save(
        int $logId,
        int $ownerAdminId,
        string $dueDate,
        int $updatedByAdminId
    ): void {
        if ($logId <= 0) {
            throw new RuntimeException(InvoiceFixText::get('followup_error_invalid_log'));
        }

        if ($updatedByAdminId <= 0) {
            throw new RuntimeException(InvoiceFixText::get('error_admin_required'));
        }

        $rawDueDate = trim($dueDate);
        $dueDate = self::normalizeDate($rawDueDate);
        if ($rawDueDate !== '' && $dueDate === '') {
            throw new RuntimeException(InvoiceFixText::get('followup_error_invalid_date'));
        }

        if ($ownerAdminId < 0) {
            $ownerAdminId = 0;
        }

        if ($ownerAdminId > 0 && !self::administratorExists($ownerAdminId)) {
            throw new RuntimeException(InvoiceFixText::get('followup_error_invalid_owner'));
        }

        $log = Capsule::table(InvoiceFixService::LOG_TABLE)
            ->where('id', $logId)
            ->first(['id', 'source_invoice_id', 'draft_invoice_id']);
        if (!$log) {
            throw new RuntimeException(InvoiceFixText::get('followup_error_log_not_found'));
        }

        Capsule::table(InvoiceFixService::LOG_TABLE)
            ->where('id', $logId)
            ->update([
                'follow_up_admin_id' => $ownerAdminId > 0 ? $ownerAdminId : null,
                'follow_up_due_date' => $dueDate !== '' ? $dueDate : null,
                'follow_up_updated_by_admin_id' => $updatedByAdminId,
                'follow_up_updated_at' => date('Y-m-d H:i:s'),
            ]);

        $isCleared = $ownerAdminId <= 0 && $dueDate === '';
        InvoiceFixService::activityLog(
            InvoiceFixText::get($isCleared ? 'followup_activity_cleared' : 'followup_activity_saved', [
                'original' => (int) ($log->source_invoice_id ?? 0),
                'replacement' => (int) ($log->draft_invoice_id ?? 0),
            ]),
            $updatedByAdminId
        );
    }

    public static function normalizeDate(string $date): string
    {
        $date = trim($date);
        if ($date === '') {
            return '';
        }

        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return '';
        }

        return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]) ? $date : '';
    }

    public static function isOverdue(string $dueDate, string $workflowState): bool
    {
        $dueDate = self::normalizeDate($dueDate);
        if ($dueDate === '' || strtolower(trim($workflowState)) === 'completed') {
            return false;
        }

        return $dueDate < date('Y-m-d');
    }

    private static function administratorExists(int $adminId): bool
    {
        try {
            return Capsule::table('tbladmins')
                ->where('id', $adminId)
                ->where('disabled', 0)
                ->exists();
        } catch (Throwable $e) {
            return false;
        }
    }
}
