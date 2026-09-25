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
require_once __DIR__ . '/InvoiceFixWorkQueue.php';
require_once __DIR__ . '/InvoiceFixFollowUp.php';

final class InvoiceFixDetails
{
    /**
     * Load one InvoiceFix audit record and current invoice state.
     *
     * @return array{
     *   log:object,
     *   source:object|null,
     *   replacement:object|null,
     *   admin:object|null,
     *   note_admin:object|null,
     *   follow_up_admin:object|null,
     *   follow_up_updated_by_admin:object|null,
     *   active_admins:array<int,object>,
     *   workflow_state:string
     * }
     */
    public static function load(int $logId): array
    {
        if ($logId <= 0) {
            throw new RuntimeException(InvoiceFixText::get('details_error_invalid_log'));
        }

        $log = Capsule::table(InvoiceFixService::LOG_TABLE)->where('id', $logId)->first();
        if (!$log) {
            throw new RuntimeException(InvoiceFixText::get('details_error_log_not_found'));
        }

        $sourceId = (int) ($log->source_invoice_id ?? 0);
        $replacementId = (int) ($log->draft_invoice_id ?? 0);

        $source = $sourceId > 0
            ? Capsule::table('tblinvoices')->where('id', $sourceId)->first()
            : null;
        $replacement = $replacementId > 0
            ? Capsule::table('tblinvoices')->where('id', $replacementId)->first()
            : null;

        $admin = self::loadAdmin((int) ($log->admin_id ?? 0));
        $noteAdmin = self::loadAdmin((int) ($log->internal_note_admin_id ?? 0));
        $followUpAdmin = self::loadAdmin((int) ($log->follow_up_admin_id ?? 0));
        $followUpUpdatedByAdmin = self::loadAdmin((int) ($log->follow_up_updated_by_admin_id ?? 0));

        $stateRow = (object) [
            'result' => (string) ($log->result ?? ''),
            'source_exists_id' => $source ? (int) ($source->id ?? 0) : 0,
            'replacement_exists_id' => $replacement ? (int) ($replacement->id ?? 0) : 0,
            'source_current_status' => $source ? (string) ($source->status ?? '') : '',
            'replacement_current_status' => $replacement ? (string) ($replacement->status ?? '') : '',
        ];

        return [
            'log' => $log,
            'source' => $source,
            'replacement' => $replacement,
            'admin' => $admin,
            'note_admin' => $noteAdmin,
            'follow_up_admin' => $followUpAdmin,
            'follow_up_updated_by_admin' => $followUpUpdatedByAdmin,
            'active_admins' => InvoiceFixFollowUp::activeAdministrators(),
            'workflow_state' => InvoiceFixWorkQueue::state($stateRow),
        ];
    }

    private static function loadAdmin(int $adminId): ?object
    {
        if ($adminId <= 0) {
            return null;
        }

        try {
            return Capsule::table('tbladmins')
                ->where('id', $adminId)
                ->first(['id', 'username', 'firstname', 'lastname']);
        } catch (Throwable $e) {
            return null;
        }
    }
}
