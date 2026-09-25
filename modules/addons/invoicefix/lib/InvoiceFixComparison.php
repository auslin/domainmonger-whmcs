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

final class InvoiceFixComparison
{
    /**
     * Load one InvoiceFix relationship and build a read-only comparison.
     *
     * @return array{
     *   log_id:int,
     *   source_invoice_id:int,
     *   replacement_invoice_id:int,
     *   source:object,
     *   replacement:object,
     *   header_rows:array<int,array<string,mixed>>,
     *   item_rows:array<int,array<string,mixed>>,
     *   header_change_count:int,
     *   item_change_count:int,
     *   original_item_count:int,
     *   replacement_item_count:int
     * }
     */
    public static function load(int $logId): array
    {
        if ($logId <= 0) {
            throw new RuntimeException(InvoiceFixText::get('compare_error_invalid_log'));
        }

        $log = Capsule::table(InvoiceFixService::LOG_TABLE)->where('id', $logId)->first();
        if (!$log) {
            throw new RuntimeException(InvoiceFixText::get('compare_error_log_not_found'));
        }

        $sourceInvoiceId = (int) ($log->source_invoice_id ?? 0);
        $replacementInvoiceId = (int) ($log->draft_invoice_id ?? 0);

        $source = Capsule::table('tblinvoices')->where('id', $sourceInvoiceId)->first();
        if (!$source) {
            throw new RuntimeException(InvoiceFixText::get('compare_error_original_missing', [
                'invoice_id' => $sourceInvoiceId,
            ]));
        }

        $replacement = Capsule::table('tblinvoices')->where('id', $replacementInvoiceId)->first();
        if (!$replacement) {
            throw new RuntimeException(InvoiceFixText::get('compare_error_replacement_missing', [
                'invoice_id' => $replacementInvoiceId,
            ]));
        }

        if ((int) ($source->userid ?? 0) !== (int) ($replacement->userid ?? 0)) {
            throw new RuntimeException(InvoiceFixText::get('compare_error_client_mismatch'));
        }

        $sourceItems = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $sourceInvoiceId)
            ->orderBy('id')
            ->get()
            ->all();

        $replacementItems = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $replacementInvoiceId)
            ->orderBy('id')
            ->get()
            ->all();

        $headerRows = self::compareHeaders($source, $replacement);
        $itemRows = self::compareItems($sourceItems, $replacementItems);

        return [
            'log_id' => $logId,
            'source_invoice_id' => $sourceInvoiceId,
            'replacement_invoice_id' => $replacementInvoiceId,
            'source' => $source,
            'replacement' => $replacement,
            'header_rows' => $headerRows,
            'item_rows' => $itemRows,
            'header_change_count' => count(array_filter($headerRows, static fn (array $row): bool => !empty($row['changed']))),
            'item_change_count' => count(array_filter($itemRows, static fn (array $row): bool => ($row['state'] ?? '') !== 'unchanged')),
            'original_item_count' => count($sourceItems),
            'replacement_item_count' => count($replacementItems),
        ];
    }

    /**
     * Compare line items while preserving useful alignment when an administrator adds or removes a line.
     *
     * @param array<int,object|array<string,mixed>> $sourceItems
     * @param array<int,object|array<string,mixed>> $replacementItems
     * @return array<int,array<string,mixed>>
     */
    public static function compareItems(array $sourceItems, array $replacementItems): array
    {
        $sourceCount = count($sourceItems);
        $replacementCount = count($replacementItems);
        $gapPenalty = -4;
        $scores = array_fill(0, $sourceCount + 1, array_fill(0, $replacementCount + 1, 0));
        $moves = array_fill(0, $sourceCount + 1, array_fill(0, $replacementCount + 1, ''));

        for ($i = 1; $i <= $sourceCount; $i++) {
            $scores[$i][0] = $i * $gapPenalty;
            $moves[$i][0] = 'remove';
        }
        for ($j = 1; $j <= $replacementCount; $j++) {
            $scores[0][$j] = $j * $gapPenalty;
            $moves[0][$j] = 'add';
        }

        for ($i = 1; $i <= $sourceCount; $i++) {
            for ($j = 1; $j <= $replacementCount; $j++) {
                $similarity = self::itemSimilarity($sourceItems[$i - 1], $replacementItems[$j - 1]);
                $matchScore = $scores[$i - 1][$j - 1] + $similarity;
                $removeScore = $scores[$i - 1][$j] + $gapPenalty;
                $addScore = $scores[$i][$j - 1] + $gapPenalty;

                if ($matchScore >= $removeScore && $matchScore >= $addScore) {
                    $scores[$i][$j] = $matchScore;
                    $moves[$i][$j] = 'match';
                } elseif ($removeScore >= $addScore) {
                    $scores[$i][$j] = $removeScore;
                    $moves[$i][$j] = 'remove';
                } else {
                    $scores[$i][$j] = $addScore;
                    $moves[$i][$j] = 'add';
                }
            }
        }

        $aligned = [];
        $i = $sourceCount;
        $j = $replacementCount;
        while ($i > 0 || $j > 0) {
            $move = $moves[$i][$j] ?? '';
            if ($i > 0 && $j > 0 && $move === 'match') {
                $aligned[] = self::buildItemRow($sourceItems[$i - 1], $replacementItems[$j - 1], $i, $j);
                $i--;
                $j--;
                continue;
            }

            if ($i > 0 && ($move === 'remove' || $j === 0)) {
                $aligned[] = self::buildItemRow($sourceItems[$i - 1], null, $i, null);
                $i--;
                continue;
            }

            if ($j > 0) {
                $aligned[] = self::buildItemRow(null, $replacementItems[$j - 1], null, $j);
                $j--;
            }
        }

        return array_reverse($aligned);
    }

    /** @return array<int,array<string,mixed>> */
    private static function compareHeaders(object $source, object $replacement): array
    {
        $definitions = [
            ['status', 'compare_field_status', 'text'],
            ['invoicenum', 'compare_field_invoice_number', 'text'],
            ['date', 'compare_field_invoice_date', 'date'],
            ['duedate', 'compare_field_due_date', 'date'],
            ['paymentmethod', 'compare_field_payment_method', 'text'],
            ['taxrate', 'compare_field_tax_rate_1', 'rate'],
            ['taxrate2', 'compare_field_tax_rate_2', 'rate'],
            ['subtotal', 'compare_field_subtotal', 'amount'],
            ['tax', 'compare_field_tax_1', 'amount'],
            ['tax2', 'compare_field_tax_2', 'amount'],
            ['credit', 'compare_field_credit', 'amount'],
            ['total', 'compare_field_total', 'amount'],
            ['notes', 'compare_field_customer_notes', 'multiline'],
            ['adminnotes', 'compare_field_admin_notes', 'multiline'],
        ];

        $rows = [];
        foreach ($definitions as [$field, $labelKey, $type]) {
            if (!property_exists($source, $field) && !property_exists($replacement, $field)) {
                continue;
            }

            $original = property_exists($source, $field) ? $source->{$field} : null;
            $replacementValue = property_exists($replacement, $field) ? $replacement->{$field} : null;
            $rows[] = [
                'field' => $field,
                'label_key' => $labelKey,
                'type' => $type,
                'original' => $original,
                'replacement' => $replacementValue,
                'changed' => !self::valuesEqual($original, $replacementValue, $type),
            ];
        }

        return $rows;
    }

    /**
     * @param object|array<string,mixed>|null $source
     * @param object|array<string,mixed>|null $replacement
     * @return array<string,mixed>
     */
    private static function buildItemRow($source, $replacement, ?int $sourcePosition, ?int $replacementPosition): array
    {
        if ($source === null) {
            return [
                'state' => 'added',
                'original' => null,
                'replacement' => $replacement,
                'original_position' => null,
                'replacement_position' => $replacementPosition,
                'changed_fields' => ['all'],
            ];
        }

        if ($replacement === null) {
            return [
                'state' => 'removed',
                'original' => $source,
                'replacement' => null,
                'original_position' => $sourcePosition,
                'replacement_position' => null,
                'changed_fields' => ['all'],
            ];
        }

        $fields = [
            'description' => 'text',
            'amount' => 'amount',
            'taxed' => 'boolean',
            'type' => 'text',
            'relid' => 'integer',
            'duedate' => 'date',
            'paymentmethod' => 'text',
            'notes' => 'multiline',
        ];
        $changedFields = [];
        foreach ($fields as $field => $type) {
            $originalValue = self::itemValue($source, $field);
            $replacementValue = self::itemValue($replacement, $field);
            if (!self::valuesEqual($originalValue, $replacementValue, $type)) {
                $changedFields[] = $field;
            }
        }

        return [
            'state' => $changedFields === [] ? 'unchanged' : 'changed',
            'original' => $source,
            'replacement' => $replacement,
            'original_position' => $sourcePosition,
            'replacement_position' => $replacementPosition,
            'changed_fields' => $changedFields,
        ];
    }

    /** @param object|array<string,mixed> $source @param object|array<string,mixed> $replacement */
    private static function itemSimilarity($source, $replacement): int
    {
        $score = -1;
        $sourceKey = self::stableItemKey($source);
        $replacementKey = self::stableItemKey($replacement);

        if ($sourceKey !== '' && $replacementKey !== '') {
            $score += $sourceKey === $replacementKey ? 14 : -20;
        }

        $sourceDescription = self::normalizeText((string) self::itemValue($source, 'description'));
        $replacementDescription = self::normalizeText((string) self::itemValue($replacement, 'description'));
        if ($sourceDescription !== '' && $sourceDescription === $replacementDescription) {
            $score += 8;
        } elseif ($sourceDescription !== '' && $replacementDescription !== '') {
            similar_text($sourceDescription, $replacementDescription, $percent);
            if ($percent >= 75.0) {
                $score += 3;
            }
        }

        if (self::valuesEqual(self::itemValue($source, 'amount'), self::itemValue($replacement, 'amount'), 'amount')) {
            $score += 3;
        }
        if (self::valuesEqual(self::itemValue($source, 'taxed'), self::itemValue($replacement, 'taxed'), 'boolean')) {
            $score += 1;
        }

        return $score;
    }

    /** @param object|array<string,mixed> $item */
    private static function stableItemKey($item): string
    {
        $type = strtolower(trim((string) self::itemValue($item, 'type')));
        $relid = (int) self::itemValue($item, 'relid');
        if ($type === '' && $relid <= 0) {
            return '';
        }

        return $type . '|' . $relid;
    }

    /** @param object|array<string,mixed> $item */
    private static function itemValue($item, string $field)
    {
        if (is_array($item)) {
            return $item[$field] ?? null;
        }

        return property_exists($item, $field) ? $item->{$field} : null;
    }

    private static function valuesEqual($original, $replacement, string $type): bool
    {
        if (in_array($type, ['amount', 'rate'], true)) {
            return abs((float) $original - (float) $replacement) < 0.0001;
        }

        if ($type === 'boolean') {
            return (bool) $original === (bool) $replacement;
        }

        if ($type === 'integer') {
            return (int) $original === (int) $replacement;
        }

        return self::normalizeText((string) $original) === self::normalizeText((string) $replacement);
    }

    private static function normalizeText(string $value): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", $value));
    }
}
