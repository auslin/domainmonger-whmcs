<?php
/**
 * DomainMonger Patch 1745
 * Test Live automatic routed invoice generation (daily cron pre-generation).
 *
 * Purpose:
 * - Run only while DomainMonger Payment Routing is in Test Live mode.
 * - Before WHMCS daily automation invoice generation, pre-generate invoices
 *   for explicitly assigned products/domains grouped by:
 *      client + next due date + native WHMCS Pay Method ID.
 * - Products and domains sharing a Pay Method and due date stay together.
 * - Different Pay Methods are generated in separate native GenInvoices calls.
 * - Account Default/unassigned items are not touched here and remain for the
 *   normal WHMCS invoice-generation task later in the same daily cron run.
 *
 * Safety for this first automated Test Live stage:
 * - Full Live mode is intentionally excluded.
 * - Invoice-created emails from these pre-generation calls are suppressed.
 * - WHMCS remains authoritative for invoice creation and InvoiceCreation hooks.
 * - Stale assignments are validated through the proven 1723 helper before use.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

if (!function_exists('dm1745_log')) {
    function dm1745_log(string $message): void
    {
        if (function_exists('dm1723_log')) {
            dm1723_log('[1745 TEST LIVE AUTO GEN] ' . $message);
            return;
        }
        if (function_exists('logActivity')) {
            logActivity('DomainMonger Pay Routing 1745 [TEST LIVE AUTO GEN]: ' . $message);
        }
    }
}

if (!function_exists('dm1745_valid_due_date')) {
    function dm1745_valid_due_date($value): string
    {
        $date = trim((string) $value);
        if ($date === '' || $date === '0000-00-00' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '';
        }

        $parts = array_map('intval', explode('-', $date));
        if (count($parts) !== 3 || !checkdate($parts[1], $parts[2], $parts[0])) {
            return '';
        }

        return $date;
    }
}

if (!function_exists('dm1745_new_invoice_ids')) {
    function dm1745_new_invoice_ids(int $clientId, int $afterInvoiceId): array
    {
        if ($clientId <= 0) {
            return [];
        }

        try {
            return array_values(array_map('intval', Capsule::table('tblinvoices')
                ->where('userid', $clientId)
                ->where('id', '>', max(0, $afterInvoiceId))
                ->orderBy('id')
                ->pluck('id')
                ->all()));
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('dm1745_generate_for_client')) {
    function dm1745_generate_for_client(int $clientId): void
    {
        if ($clientId <= 0
            || !function_exists('dm1723_cleanup_stale_assignment')
            || !function_exists('localAPI')) {
            return;
        }

        try {
            if (!Capsule::schema()->hasTable('mod_domainmonger_item_paymethod_assignments')) {
                return;
            }

            $assignmentRows = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->whereIn('item_type', ['service', 'domain'])
                ->get(['item_type', 'item_id', 'pay_method_id']);

            if (!$assignmentRows || count($assignmentRows) === 0) {
                return;
            }

            $serviceIds = [];
            $domainIds = [];
            foreach ($assignmentRows as $row) {
                $itemType = strtolower(trim((string) ($row->item_type ?? '')));
                $itemId = (int) ($row->item_id ?? 0);
                if ($itemId <= 0) {
                    continue;
                }
                if ($itemType === 'service') {
                    $serviceIds[$itemId] = true;
                } elseif ($itemType === 'domain') {
                    $domainIds[$itemId] = true;
                }
            }

            $serviceMap = [];
            if ($serviceIds) {
                $rows = Capsule::table('tblhosting')
                    ->where('userid', $clientId)
                    ->whereIn('id', array_keys($serviceIds))
                    ->get(['id', 'nextduedate']);
                foreach ($rows ?: [] as $row) {
                    $serviceMap[(int) ($row->id ?? 0)] = (string) ($row->nextduedate ?? '');
                }
            }

            $domainMap = [];
            if ($domainIds) {
                $rows = Capsule::table('tbldomains')
                    ->where('userid', $clientId)
                    ->whereIn('id', array_keys($domainIds))
                    ->get(['id', 'nextduedate']);
                foreach ($rows ?: [] as $row) {
                    $domainMap[(int) ($row->id ?? 0)] = (string) ($row->nextduedate ?? '');
                }
            }

            // Build exactly the grouping proven by 1744: one native GenInvoices
            // call per next-due-date + explicit Pay Method group, with products
            // and domains allowed in the same group.
            $groups = [];
            $skipped = 0;
            foreach ($assignmentRows as $row) {
                $itemType = strtolower(trim((string) ($row->item_type ?? '')));
                $itemId = (int) ($row->item_id ?? 0);
                if (!in_array($itemType, ['service', 'domain'], true) || $itemId <= 0) {
                    continue;
                }

                // Use the existing proven validation/cleanup path. The returned
                // ID is authoritative; stale or no-longer-eligible assignments
                // are excluded and left for normal WHMCS behavior.
                $payMethodId = (int) dm1723_cleanup_stale_assignment($clientId, $itemType, $itemId);
                if ($payMethodId <= 0) {
                    $skipped++;
                    continue;
                }

                $dueDate = $itemType === 'service'
                    ? dm1745_valid_due_date($serviceMap[$itemId] ?? '')
                    : dm1745_valid_due_date($domainMap[$itemId] ?? '');

                if ($dueDate === '') {
                    $skipped++;
                    continue;
                }

                $groupKey = $dueDate . '|pm:' . $payMethodId;
                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [
                        'due_date' => $dueDate,
                        'pay_method_id' => $payMethodId,
                        'service' => [],
                        'domain' => [],
                    ];
                }
                $groups[$groupKey][$itemType][$itemId] = $itemId;
            }

            if (!$groups) {
                return;
            }

            ksort($groups, SORT_STRING);

            $beforeInvoiceId = (int) (Capsule::table('tblinvoices')
                ->where('userid', $clientId)
                ->max('id') ?? 0);

            $attemptedGroups = 0;
            $successfulGroups = 0;
            $reportedCreated = 0;
            $errors = [];

            foreach ($groups as $group) {
                $params = [
                    'clientid' => $clientId,
                    // First automatic Test Live stage: avoid customer-facing
                    // invoice-created mail while we prove cron integration.
                    'noemails' => true,
                ];

                $groupServiceIds = array_values(array_map('intval', array_values($group['service'] ?? [])));
                $groupDomainIds = array_values(array_map('intval', array_values($group['domain'] ?? [])));
                if ($groupServiceIds) {
                    $params['serviceids'] = $groupServiceIds;
                }
                if ($groupDomainIds) {
                    $params['domainids'] = $groupDomainIds;
                }
                if (!$groupServiceIds && !$groupDomainIds) {
                    continue;
                }

                $attemptedGroups++;
                $result = localAPI('GenInvoices', $params);
                if (!is_array($result) || strtolower(trim((string) ($result['result'] ?? ''))) !== 'success') {
                    $errors[] = 'PM#' . (int) ($group['pay_method_id'] ?? 0)
                        . ' due ' . (string) ($group['due_date'] ?? '')
                        . ': ' . (is_array($result)
                            ? trim((string) ($result['message'] ?? $result['error'] ?? 'Unknown GenInvoices error'))
                            : 'Invalid GenInvoices response');
                    continue;
                }

                $successfulGroups++;
                $reportedCreated += max(0, (int) ($result['numcreated'] ?? 0));
            }

            $newInvoiceIds = dm1745_new_invoice_ids($clientId, $beforeInvoiceId);
            $invoiceText = $newInvoiceIds ? implode(',', $newInvoiceIds) : 'none';

            if ($errors) {
                dm1745_log('Client #' . $clientId
                    . ': routed pre-generation completed with errors. Groups attempted=' . $attemptedGroups
                    . ', successful=' . $successfulGroups
                    . ', API numcreated=' . $reportedCreated
                    . ', new invoice IDs=' . $invoiceText
                    . ', skipped assignments=' . $skipped
                    . '. Errors: ' . implode(' | ', $errors)
                    . '. Invoice-created emails were suppressed. Native daily invoicing will continue afterward; unresolved items retain the existing invoice conflict/default safety fallback.');
            } elseif ($reportedCreated > 0 || $newInvoiceIds) {
                dm1745_log('Client #' . $clientId
                    . ': routed pre-generation succeeded. Groups attempted=' . $attemptedGroups
                    . ', API numcreated=' . $reportedCreated
                    . ', new invoice IDs=' . $invoiceText
                    . ', skipped assignments=' . $skipped
                    . '. Invoice-created emails were suppressed for this Test Live stage.');
            }
        } catch (Throwable $e) {
            dm1745_log('Client #' . $clientId . ': routed pre-generation aborted safely: ' . $e->getMessage());
        }
    }
}

// WHMCS documents PreCronJob as running before the daily automation cron.
// This means our explicitly assigned Test Live items can be invoiced first in
// proven Pay-Method groups; the native invoicing task then handles remaining
// Account Default/unassigned items normally.
add_hook('PreCronJob', 1745, static function (): void {
    static $running = false;
    if ($running) {
        return;
    }

    $running = true;
    try {
        if (!function_exists('dm1723_settings') || !function_exists('dm1723_client_mode')) {
            return;
        }

        $settings = dm1723_settings();
        if (empty($settings['active']) || (string) ($settings['routing_mode'] ?? '') !== 'test_live') {
            // Explicitly do nothing in Off, Admin Preview, Shadow, and full Live.
            return;
        }

        $testClientIds = array_values(array_unique(array_filter(
            array_map('intval', (array) ($settings['test_client_ids'] ?? [])),
            static fn (int $id): bool => $id > 0
        )));

        foreach ($testClientIds as $clientId) {
            if (dm1723_client_mode($clientId) !== 'live') {
                continue;
            }
            dm1745_generate_for_client($clientId);
        }
    } catch (Throwable $e) {
        dm1745_log('PreCronJob handler aborted safely: ' . $e->getMessage());
    } finally {
        $running = false;
    }
});
