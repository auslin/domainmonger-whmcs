<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

use RuntimeException;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

require_once __DIR__ . '/InvoiceFixService.php';
require_once __DIR__ . '/InvoiceFixText.php';

/**
 * Administrator-only notes attached to an InvoiceFix relationship.
 *
 * Notes are stored only in the InvoiceFix audit table. They never change an
 * invoice, transaction, payment, credit, or ledger record.
 */
final class InvoiceFixNote
{
    public const MAX_LENGTH = 10000;

    public static function save(int $logId, string $note, int $adminId): void
    {
        if ($logId <= 0) {
            throw new RuntimeException(InvoiceFixText::get('note_error_invalid_log'));
        }

        if ($adminId <= 0) {
            throw new RuntimeException(InvoiceFixText::get('error_admin_required'));
        }

        $note = self::normalize($note);
        if (self::length($note) > self::MAX_LENGTH) {
            throw new RuntimeException(InvoiceFixText::get('note_error_too_long', [
                'limit' => self::MAX_LENGTH,
            ]));
        }

        $log = Capsule::table(InvoiceFixService::LOG_TABLE)
            ->where('id', $logId)
            ->first(['id', 'source_invoice_id', 'draft_invoice_id']);
        if (!$log) {
            throw new RuntimeException(InvoiceFixText::get('note_error_log_not_found'));
        }

        Capsule::table(InvoiceFixService::LOG_TABLE)
            ->where('id', $logId)
            ->update([
                'internal_note' => $note !== '' ? $note : null,
                'internal_note_admin_id' => $note !== '' ? $adminId : null,
                'internal_note_updated_at' => $note !== '' ? date('Y-m-d H:i:s') : null,
            ]);

        InvoiceFixService::activityLog(
            InvoiceFixText::get($note !== '' ? 'note_activity_saved' : 'note_activity_cleared', [
                'original' => (int) ($log->source_invoice_id ?? 0),
                'replacement' => (int) ($log->draft_invoice_id ?? 0),
            ]),
            $adminId
        );
    }

    private static function normalize(string $note): string
    {
        $note = str_replace(["\r\n", "\r"], "\n", $note);
        return trim($note);
    }

    private static function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
