<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

use RuntimeException;
use Throwable;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

require_once __DIR__ . '/InvoiceFixDetails.php';
require_once __DIR__ . '/InvoiceFixComparison.php';
require_once __DIR__ . '/InvoiceFixText.php';
require_once __DIR__ . '/InvoiceFixWorkQueue.php';

final class InvoiceFixExport
{
    /**
     * Build a portable, read-only CSV audit report for one InvoiceFix relationship.
     *
     * @return array{filename:string,rows:array<int,array<int,string>>}
     */
    public static function build(int $logId): array
    {
        if ($logId <= 0) {
            throw new RuntimeException(InvoiceFixText::get('export_error_invalid_log'));
        }

        $details = InvoiceFixDetails::load($logId);
        $log = $details['log'];
        $source = $details['source'];
        $replacement = $details['replacement'];
        $sourceId = (int) ($log->source_invoice_id ?? 0);
        $replacementId = (int) ($log->draft_invoice_id ?? 0);

        $rows = [[
            InvoiceFixText::get('export_column_section'),
            InvoiceFixText::get('export_column_record'),
            InvoiceFixText::get('export_column_field'),
            InvoiceFixText::get('export_column_value'),
            InvoiceFixText::get('export_column_original'),
            InvoiceFixText::get('export_column_replacement'),
            InvoiceFixText::get('export_column_result'),
        ]];

        self::addAuditRows($rows, $details, $sourceId, $replacementId);
        self::addInvoiceSummaryRows($rows, $source, $replacement, $sourceId, $replacementId);

        if ($source && $replacement) {
            try {
                self::addComparisonRows($rows, InvoiceFixComparison::load($logId));
            } catch (Throwable $e) {
                self::addRow(
                    $rows,
                    InvoiceFixText::get('export_section_comparison'),
                    '',
                    InvoiceFixText::get('export_comparison_unavailable'),
                    $e->getMessage()
                );
            }
        } else {
            self::addRow(
                $rows,
                InvoiceFixText::get('export_section_comparison'),
                '',
                InvoiceFixText::get('export_comparison_unavailable'),
                InvoiceFixText::get('export_comparison_missing_invoice')
            );
        }

        return [
            'filename' => sprintf(
                'invoicefix-reissue-%d-%d-%s.csv',
                max(0, $sourceId),
                max(0, $replacementId),
                date('Ymd-His')
            ),
            'rows' => $rows,
        ];
    }

    /**
     * Neutralize spreadsheet-formula prefixes while preserving the displayed text.
     */
    public static function safeCell(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        if (preg_match('/^[\x00-\x20]*[=+\-@]/u', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    }

    /** @param array<int,array<int,string>> $rows */
    private static function addAuditRows(array &$rows, array $details, int $sourceId, int $replacementId): void
    {
        $log = $details['log'];
        $admin = $details['admin'];
        $workflowState = (string) $details['workflow_state'];

        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('export_field_invoice_pair'),
            InvoiceFixText::get('export_invoice_pair_value', [
                'original' => $sourceId,
                'replacement' => $replacementId,
            ])
        );
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('export_field_generated_at'),
            date('Y-m-d H:i:s T')
        );
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('details_workflow_status'),
            self::workflowLabel($workflowState)
        );
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('details_created_at'),
            (string) ($log->created_at ?? '')
        );
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('details_created_by'),
            self::adminName($admin, (int) ($log->admin_id ?? 0))
        );
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('details_result'),
            strtolower(trim((string) ($log->result ?? ''))) === 'success'
                ? InvoiceFixText::get('admin_result_success')
                : InvoiceFixText::get('admin_result_warning')
        );
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('details_original_status_at_creation'),
            (string) ($log->source_status ?? '')
        );
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('details_original_total_at_creation'),
            self::formatValue($log->source_total ?? '0.00', 'amount')
        );
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('details_module_message'),
            trim((string) ($log->message ?? '')) !== ''
                ? (string) $log->message
                : InvoiceFixText::get('details_no_message')
        );
        $internalNote = trim((string) ($log->internal_note ?? ''));
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('export_field_internal_note'),
            $internalNote !== '' ? $internalNote : InvoiceFixText::get('export_value_no_internal_note')
        );

        if ($internalNote !== '') {
            self::addRow(
                $rows,
                InvoiceFixText::get('export_section_audit'),
                InvoiceFixText::get('export_record_relationship'),
                InvoiceFixText::get('export_field_note_updated_by'),
                self::adminName($details['note_admin'] ?? null, (int) ($log->internal_note_admin_id ?? 0))
            );
            self::addRow(
                $rows,
                InvoiceFixText::get('export_section_audit'),
                InvoiceFixText::get('export_record_relationship'),
                InvoiceFixText::get('export_field_note_updated_at'),
                (string) ($log->internal_note_updated_at ?? '')
            );
        }


        $followUpAdminId = (int) ($log->follow_up_admin_id ?? 0);
        $followUpDueDate = trim((string) ($log->follow_up_due_date ?? ''));
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('export_field_follow_up_owner'),
            $followUpAdminId > 0
                ? self::adminName($details['follow_up_admin'] ?? null, $followUpAdminId)
                : InvoiceFixText::get('followup_unassigned')
        );
        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_audit'),
            InvoiceFixText::get('export_record_relationship'),
            InvoiceFixText::get('export_field_follow_up_due'),
            $followUpDueDate !== '' ? $followUpDueDate : InvoiceFixText::get('followup_no_due_date')
        );

        $followUpUpdatedAt = trim((string) ($log->follow_up_updated_at ?? ''));
        if ($followUpUpdatedAt !== '') {
            self::addRow(
                $rows,
                InvoiceFixText::get('export_section_audit'),
                InvoiceFixText::get('export_record_relationship'),
                InvoiceFixText::get('export_field_follow_up_updated_by'),
                self::adminName(
                    $details['follow_up_updated_by_admin'] ?? null,
                    (int) ($log->follow_up_updated_by_admin_id ?? 0)
                )
            );
            self::addRow(
                $rows,
                InvoiceFixText::get('export_section_audit'),
                InvoiceFixText::get('export_record_relationship'),
                InvoiceFixText::get('export_field_follow_up_updated_at'),
                $followUpUpdatedAt
            );
        }
    }

    /** @param array<int,array<int,string>> $rows */
    private static function addInvoiceSummaryRows(
        array &$rows,
        ?object $source,
        ?object $replacement,
        int $sourceId,
        int $replacementId
    ): void {
        $definitions = [
            ['details_card_status', 'status', 'text'],
            ['details_card_total', 'total', 'amount'],
            ['details_card_invoice_date', 'date', 'date'],
            ['details_card_due_date', 'duedate', 'date'],
            ['details_card_paid_date', 'datepaid', 'date'],
        ];

        foreach ($definitions as [$labelKey, $field, $type]) {
            self::addRow(
                $rows,
                InvoiceFixText::get('export_section_invoice_summary'),
                InvoiceFixText::get('export_record_current_invoices'),
                InvoiceFixText::get($labelKey),
                '',
                self::invoiceValue($source, $field, $type),
                self::invoiceValue($replacement, $field, $type),
                ''
            );
        }

        self::addRow(
            $rows,
            InvoiceFixText::get('export_section_invoice_summary'),
            InvoiceFixText::get('export_record_current_invoices'),
            InvoiceFixText::get('export_field_invoice_ids'),
            '',
            $source ? (string) $sourceId : InvoiceFixText::get('details_invoice_missing'),
            $replacement ? (string) $replacementId : InvoiceFixText::get('details_invoice_missing'),
            ''
        );
    }

    /** @param array<int,array<int,string>> $rows @param array<string,mixed> $comparison */
    private static function addComparisonRows(array &$rows, array $comparison): void
    {
        foreach ($comparison['header_rows'] as $row) {
            self::addRow(
                $rows,
                InvoiceFixText::get('export_section_invoice_details'),
                InvoiceFixText::get('export_record_header_comparison'),
                InvoiceFixText::get((string) ($row['label_key'] ?? '')),
                '',
                self::formatValue($row['original'] ?? '', (string) ($row['type'] ?? 'text')),
                self::formatValue($row['replacement'] ?? '', (string) ($row['type'] ?? 'text')),
                !empty($row['changed'])
                    ? InvoiceFixText::get('compare_result_changed')
                    : InvoiceFixText::get('compare_result_unchanged')
            );
        }

        foreach ($comparison['item_rows'] as $row) {
            $sourcePosition = isset($row['original_position']) && $row['original_position'] !== null
                ? (string) (int) $row['original_position']
                : InvoiceFixText::get('export_line_position_none');
            $replacementPosition = isset($row['replacement_position']) && $row['replacement_position'] !== null
                ? (string) (int) $row['replacement_position']
                : InvoiceFixText::get('export_line_position_none');
            $record = InvoiceFixText::get('export_line_record', [
                'original' => $sourcePosition,
                'replacement' => $replacementPosition,
            ]);
            $state = self::itemStateLabel((string) ($row['state'] ?? 'changed'));

            $fields = [
                ['export_item_description', 'description', 'multiline'],
                ['export_item_amount', 'amount', 'amount'],
                ['export_item_taxable', 'taxed', 'boolean'],
                ['export_item_type', 'type', 'text'],
                ['export_item_related_id', 'relid', 'integer'],
                ['export_item_due_date', 'duedate', 'date'],
                ['export_item_payment_method', 'paymentmethod', 'text'],
                ['export_item_notes', 'notes', 'multiline'],
            ];

            foreach ($fields as [$labelKey, $field, $type]) {
                self::addRow(
                    $rows,
                    InvoiceFixText::get('export_section_line_items'),
                    $record,
                    InvoiceFixText::get($labelKey),
                    '',
                    self::formatValue(self::itemValue($row['original'] ?? null, $field), $type),
                    self::formatValue(self::itemValue($row['replacement'] ?? null, $field), $type),
                    $state
                );
            }
        }
    }

    /** @param array<int,array<int,string>> $rows */
    private static function addRow(
        array &$rows,
        string $section,
        string $record,
        string $field,
        string $value = '',
        string $original = '',
        string $replacement = '',
        string $result = ''
    ): void {
        $rows[] = [$section, $record, $field, $value, $original, $replacement, $result];
    }

    private static function invoiceValue(?object $invoice, string $field, string $type): string
    {
        if (!$invoice) {
            return InvoiceFixText::get('details_invoice_missing');
        }

        return self::formatValue(property_exists($invoice, $field) ? $invoice->{$field} : '', $type);
    }

    /** @param object|array<string,mixed>|null $item */
    private static function itemValue($item, string $field)
    {
        if ($item === null) {
            return '';
        }
        if (is_array($item)) {
            return $item[$field] ?? '';
        }

        return property_exists($item, $field) ? $item->{$field} : '';
    }

    private static function formatValue($value, string $type): string
    {
        if ($type === 'amount') {
            return number_format((float) $value, 2, '.', '');
        }
        if ($type === 'rate') {
            return number_format((float) $value, 2, '.', '') . '%';
        }
        if ($type === 'boolean') {
            return !empty($value)
                ? InvoiceFixText::get('export_value_yes')
                : InvoiceFixText::get('export_value_no');
        }
        if ($type === 'integer') {
            return (string) (int) $value;
        }

        $stringValue = trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
        if (($type === 'date' || $type === 'text') && in_array($stringValue, ['0000-00-00', '0000-00-00 00:00:00'], true)) {
            return '';
        }

        return $stringValue;
    }

    private static function workflowLabel(string $state): string
    {
        $labels = [
            InvoiceFixWorkQueue::FILTER_DRAFT => 'queue_state_draft',
            InvoiceFixWorkQueue::FILTER_REVIEW => 'queue_state_review',
            InvoiceFixWorkQueue::FILTER_COMPLETED => 'queue_state_completed',
            InvoiceFixWorkQueue::FILTER_ATTENTION => 'queue_state_attention',
        ];

        return InvoiceFixText::get($labels[$state] ?? 'queue_state_attention');
    }

    private static function itemStateLabel(string $state): string
    {
        $labels = [
            'changed' => 'compare_result_changed',
            'unchanged' => 'compare_result_unchanged',
            'added' => 'compare_result_added',
            'removed' => 'compare_result_removed',
        ];

        return InvoiceFixText::get($labels[$state] ?? 'compare_result_changed');
    }

    private static function adminName(?object $admin, int $adminId): string
    {
        if ($admin) {
            $name = trim((string) ($admin->firstname ?? '') . ' ' . (string) ($admin->lastname ?? ''));
            $username = trim((string) ($admin->username ?? ''));
            if ($name !== '' && $username !== '') {
                return InvoiceFixText::get('details_admin_name_username', [
                    'name' => $name,
                    'username' => $username,
                    'admin_id' => $adminId,
                ]);
            }
            if ($name !== '') {
                return InvoiceFixText::get('details_admin_name_id', ['name' => $name, 'admin_id' => $adminId]);
            }
            if ($username !== '') {
                return InvoiceFixText::get('details_admin_username_id', ['username' => $username, 'admin_id' => $adminId]);
            }
        }

        return $adminId > 0
            ? InvoiceFixText::get('details_admin_id_only', ['admin_id' => $adminId])
            : InvoiceFixText::get('details_admin_unknown');
    }
}
