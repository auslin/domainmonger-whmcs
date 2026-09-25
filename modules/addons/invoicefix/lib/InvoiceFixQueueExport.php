<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

use RuntimeException;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

require_once __DIR__ . '/InvoiceFixWorkQueue.php';
require_once __DIR__ . '/InvoiceFixAging.php';
require_once __DIR__ . '/InvoiceFixText.php';
require_once __DIR__ . '/InvoiceFixFollowUp.php';

/**
 * Build a read-only CSV representation of the current Work Queue result set.
 */
final class InvoiceFixQueueExport
{
    /**
     * @return array{filename:string,rows:array<int,array<int,string>>}
     */
    public static function build(
        string $filter,
        string $search,
        string $dateFrom,
        string $dateTo,
        string $sort,
        string $direction,
        int $currentAdminId = 0
    ): array {
        $queue = InvoiceFixWorkQueue::all(
            $filter,
            $search,
            $dateFrom,
            $dateTo,
            $sort,
            $direction,
            InvoiceFixAging::configuredThreshold(),
            $currentAdminId
        );
        if ((int) $queue['total'] <= 0) {
            throw new RuntimeException(InvoiceFixText::get('queue_export_error_no_results'));
        }

        $rows = [];
        $rows[] = [InvoiceFixText::get('queue_export_field_report'), InvoiceFixText::get('queue_export_report_title')];
        $rows[] = [InvoiceFixText::get('queue_export_field_generated_at'), gmdate('Y-m-d H:i:s') . ' UTC'];
        $rows[] = [InvoiceFixText::get('queue_export_field_filter'), self::filterLabel((string) $queue['filter'])];
        $rows[] = [InvoiceFixText::get('queue_export_field_search'), self::criteriaValue((string) $queue['search'])];
        $rows[] = [InvoiceFixText::get('queue_export_field_date_from'), self::criteriaValue((string) $queue['date_from'])];
        $rows[] = [InvoiceFixText::get('queue_export_field_date_to'), self::criteriaValue((string) $queue['date_to'])];
        $rows[] = [InvoiceFixText::get('queue_export_field_sort'), InvoiceFixText::get('queue_export_sort_value', [
            'column' => self::sortLabel((string) $queue['sort']),
            'direction' => self::directionLabel((string) $queue['direction']),
        ])];
        $rows[] = [InvoiceFixText::get('queue_export_field_record_count'), (string) $queue['total']];
        $rows[] = [];
        $rows[] = [
            InvoiceFixText::get('queue_export_column_log_id'),
            InvoiceFixText::get('queue_export_column_original_id'),
            InvoiceFixText::get('queue_export_column_original_number'),
            InvoiceFixText::get('queue_export_column_original_status'),
            InvoiceFixText::get('queue_export_column_replacement_id'),
            InvoiceFixText::get('queue_export_column_replacement_number'),
            InvoiceFixText::get('queue_export_column_replacement_status'),
            InvoiceFixText::get('queue_export_column_workflow'),
            InvoiceFixText::get('queue_export_column_follow_up_owner'),
            InvoiceFixText::get('queue_export_column_follow_up_due'),
            InvoiceFixText::get('queue_export_column_follow_up_status'),
            InvoiceFixText::get('queue_export_column_client_id'),
            InvoiceFixText::get('queue_export_column_client_name'),
            InvoiceFixText::get('queue_export_column_company'),
            InvoiceFixText::get('queue_export_column_email'),
            InvoiceFixText::get('queue_export_column_admin_id'),
            InvoiceFixText::get('queue_export_column_result'),
            InvoiceFixText::get('queue_export_column_message'),
            InvoiceFixText::get('queue_export_column_created'),
        ];

        foreach ($queue['rows'] as $row) {
            $client = self::client($row);
            $rows[] = [
                (string) ((int) ($row->log_id ?? 0)),
                (string) ((int) ($row->source_invoice_id ?? 0)),
                trim((string) ($row->source_invoice_number ?? '')),
                self::invoiceStatus($row, true),
                (string) ((int) ($row->draft_invoice_id ?? 0)),
                trim((string) ($row->replacement_invoice_number ?? '')),
                self::invoiceStatus($row, false),
                self::workflowLabel((string) ($row->workflow_state ?? InvoiceFixWorkQueue::FILTER_ATTENTION)),
                self::followUpOwner($row),
                self::followUpDueDate($row),
                self::followUpStatus($row),
                (string) $client['id'],
                $client['name'],
                $client['company'],
                $client['email'],
                (string) ((int) ($row->admin_id ?? 0)),
                trim((string) ($row->result ?? '')),
                trim((string) ($row->message ?? '')),
                trim((string) ($row->created_at ?? '')),
            ];
        }

        return [
            'filename' => 'invoicefix-work-queue-' . gmdate('Ymd-His') . '.csv',
            'rows' => $rows,
        ];
    }

    private static function filterLabel(string $filter): string
    {
        return [
            InvoiceFixWorkQueue::FILTER_ALL => InvoiceFixText::get('queue_filter_all'),
            InvoiceFixWorkQueue::FILTER_DRAFT => InvoiceFixText::get('queue_filter_draft'),
            InvoiceFixWorkQueue::FILTER_REVIEW => InvoiceFixText::get('queue_filter_review'),
            InvoiceFixWorkQueue::FILTER_COMPLETED => InvoiceFixText::get('queue_filter_completed'),
            InvoiceFixWorkQueue::FILTER_ATTENTION => InvoiceFixText::get('queue_filter_attention'),
            InvoiceFixWorkQueue::FILTER_STALE => InvoiceFixText::get('queue_filter_stale'),
            InvoiceFixWorkQueue::FILTER_MY_FOLLOW_UPS => InvoiceFixText::get('queue_filter_my_followups'),
            InvoiceFixWorkQueue::FILTER_OVERDUE => InvoiceFixText::get('queue_filter_overdue'),
        ][InvoiceFixWorkQueue::normalizeFilter($filter)];
    }

    private static function workflowLabel(string $state): string
    {
        return [
            InvoiceFixWorkQueue::FILTER_DRAFT => InvoiceFixText::get('queue_state_draft'),
            InvoiceFixWorkQueue::FILTER_REVIEW => InvoiceFixText::get('queue_state_review'),
            InvoiceFixWorkQueue::FILTER_COMPLETED => InvoiceFixText::get('queue_state_completed'),
            InvoiceFixWorkQueue::FILTER_ATTENTION => InvoiceFixText::get('queue_state_attention'),
        ][InvoiceFixWorkQueue::normalizeFilter($state)] ?? InvoiceFixText::get('queue_state_attention');
    }

    private static function sortLabel(string $sort): string
    {
        return [
            InvoiceFixWorkQueue::SORT_ORIGINAL => InvoiceFixText::get('queue_table_original'),
            InvoiceFixWorkQueue::SORT_REPLACEMENT => InvoiceFixText::get('queue_table_replacement'),
            InvoiceFixWorkQueue::SORT_WORKFLOW => InvoiceFixText::get('queue_table_workflow'),
            InvoiceFixWorkQueue::SORT_CREATED => InvoiceFixText::get('queue_table_created'),
        ][InvoiceFixWorkQueue::normalizeSort($sort)];
    }

    private static function directionLabel(string $direction): string
    {
        return InvoiceFixWorkQueue::normalizeDirection($direction) === InvoiceFixWorkQueue::DIRECTION_ASC
            ? InvoiceFixText::get('queue_sort_ascending')
            : InvoiceFixText::get('queue_sort_descending');
    }

    private static function criteriaValue(string $value): string
    {
        return trim($value) !== '' ? trim($value) : InvoiceFixText::get('queue_export_value_none');
    }

    /** @return array{id:int,name:string,company:string,email:string} */
    private static function client(object $row): array
    {
        $useSource = (int) ($row->source_user_id ?? 0) > 0;
        $prefix = $useSource ? 'source' : 'replacement';
        $first = trim((string) ($row->{$prefix . '_client_firstname'} ?? ''));
        $last = trim((string) ($row->{$prefix . '_client_lastname'} ?? ''));

        return [
            'id' => (int) ($row->{$prefix . '_user_id'} ?? 0),
            'name' => trim($first . ' ' . $last),
            'company' => trim((string) ($row->{$prefix . '_client_company'} ?? '')),
            'email' => trim((string) ($row->{$prefix . '_client_email'} ?? '')),
        ];
    }


    private static function followUpOwner(object $row): string
    {
        $adminId = (int) ($row->follow_up_admin_id ?? 0);
        if ($adminId <= 0) {
            return InvoiceFixText::get('followup_unassigned');
        }

        $first = trim((string) ($row->follow_up_admin_firstname ?? ''));
        $last = trim((string) ($row->follow_up_admin_lastname ?? ''));
        $username = trim((string) ($row->follow_up_admin_username ?? ''));
        $name = trim($first . ' ' . $last);

        if ($name !== '' && $username !== '') {
            return InvoiceFixText::get('details_admin_name_username', [
                'name' => $name,
                'username' => $username,
                'admin_id' => $adminId,
            ]);
        }

        if ($name !== '') {
            return InvoiceFixText::get('details_admin_name_id', [
                'name' => $name,
                'admin_id' => $adminId,
            ]);
        }

        if ($username !== '') {
            return InvoiceFixText::get('details_admin_username_id', [
                'username' => $username,
                'admin_id' => $adminId,
            ]);
        }

        return InvoiceFixText::get('details_admin_id_only', ['admin_id' => $adminId]);
    }

    private static function followUpDueDate(object $row): string
    {
        $dueDate = trim((string) ($row->follow_up_due_date ?? ''));
        return $dueDate !== '' ? $dueDate : InvoiceFixText::get('followup_no_due_date');
    }

    private static function followUpStatus(object $row): string
    {
        $state = (string) ($row->workflow_state ?? InvoiceFixWorkQueue::FILTER_ATTENTION);
        $dueDate = trim((string) ($row->follow_up_due_date ?? ''));
        $adminId = (int) ($row->follow_up_admin_id ?? 0);

        if ($adminId <= 0 && $dueDate === '') {
            return InvoiceFixText::get('followup_status_none');
        }

        if (InvoiceFixFollowUp::isOverdue($dueDate, $state)) {
            return InvoiceFixText::get('followup_status_overdue');
        }

        return InvoiceFixText::get('followup_status_active');
    }

    private static function invoiceStatus(object $row, bool $source): string
    {
        $exists = (int) ($row->{$source ? 'source_exists_id' : 'replacement_exists_id'} ?? 0) > 0;
        if (!$exists) {
            return InvoiceFixText::get('queue_invoice_missing');
        }

        $status = trim((string) ($row->{$source ? 'source_current_status' : 'replacement_current_status'} ?? ''));
        return $status !== '' ? $status : InvoiceFixText::get('admin_status_unknown');
    }
}
