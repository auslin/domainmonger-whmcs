<?php
/**
 * DomainMonger Payment Routing
 * Configuration surface for Patch 1723 per-item Pay Method assignments.
 * Patch 1767: preserves 1766 and simplifies Account Routing controls by removing the redundant account loader/top bulk toolbar and keeping the action controls in the bottom bar.
 * Patch 1770: makes Account Routing follow the configured module scope: Shadow/Test Live use only current Test Client IDs, while Live loads all clients. Saved assignments no longer force removed test clients back into the list.
 * Patch 1775: preserves the original compact badge boxes but optically reduces their rendered text at browser zoom levels where tiny font-size changes are flattened; restores centered Account Routing status alignment.
 * Patch 1776: preserves the smaller 1775 badge text and adds slight letter spacing so status/issue text remains readable at reduced browser zoom.
 * Patch 1777: renames the Payment Method Health dropdown "All" filter to "All Issues" so the scope is explicit; filter behavior and counts are unchanged.
 * Patch 1778: adds a diagnostic-only expiration-age filter to Payment Method Health. Admins can preview cards expired more than a selected number of years ago and see exactly how many unique Pay Methods would be deleted; no deletion action is enabled.
 * Patch 1779: adds client-side oldest/newest sort arrows to the Payment Method Health Expires column while preserving filters, search, pagination, and dry-run diagnostics.
 * Patch 1780: changes the Payment Method Health Expires sorter to the same WHMCS/DataTables-style header sorting treatment used by the Admin Client Profile Payment Methods manager.
 * Patch 1783: adds diagnostic-only Payment Method Health row selection and a Preview Selected Deletion action. Selection is limited to the active expired-age target set; no payment methods can be deleted.
 * Patch 1784: enables a controlled real-delete test for exactly one selected expired credit card, with server-side cutoff revalidation, one-time token protection, confirmation, DeletePayMethod fail-on-remote-failure handling, and post-delete default-PM verification. Bulk deletion remains disabled.
 * Patch 1786: rebuilds the controlled 1-10 card batch-delete test from the confirmed 1784 baseline using standard PHP form arrays for PM/client targets instead of the failed 1785 JSON submission.
 * Patch 1787: expands the confirmed batch-delete test ceiling from 10 to 25 cards while preserving full prevalidation and stop-on-first-failure behavior.
 * Patch 1788: makes Payment Method Health Select All page-scoped so a 25-row page selects only those visible cards and the 25-card delete button state stays in sync.
 * Patch 1789: adds synchronized Payment Method Health selection/delete controls above the table while retaining the bottom controls and the proven 25-card deletion path.
 * Patch 1790: adds a controlled multi-request automation test that deletes up to the first 50 currently matching expired cards in proven 25-card server-validated batches, with live progress and stop-on-first-failure behavior.
 * Patch 1795: expands the proven automated expired-card cleanup test to up to 250 matching cards while retaining 25-card server-validated batches and stop-on-first-failure behavior.
 * Patch 1798: clarifies Payment Method Health bulk actions by visually separating checkbox-based Selected Cards actions from the checkbox-independent Filtered Results automated cleanup.
 * Patch 1799: raises the automated Filtered Results cleanup ceiling from 250 to 1,000 cards while retaining the proven 25-card server-validated batch size and stop-on-first-failure behavior.
 * Patch 1800: removes the redundant "Filtered Results:" text label from the Payment Method Health action bars while preserving the 1,000-card filtered-results cleanup action.
 * Patch 1801: removes the obsolete Payment Method Health overall dry-run deletion summary now that production deletion is confirmed.
 * Patch 1802: simplifies the admin status banner to show only the module title, current mode, and Test Client IDs.
 * Patch 1803: shows Test Client IDs in the admin status banner only for Shadow and Test Live modes, where the allowlist is actually used.
 * Patch 1797: clean production-control graduation from confirmed 1795; removes test wording/restrictions and keeps AJAX success parsing synchronized with the production batch-deletion result text.
 * Patch 1791: preserves Payment Method Health rows-per-page and Expires sort direction across manual/automated deletion reloads.
 * Patch 1792: restores Payment Method Health post-delete state before initialization and also preserves Issue, expiration-age/custom-date, and text-search filters so the table no longer visibly flips through defaults.
 * Patch 1794: removes the full-page post-delete redraw by submitting both manual and automated expired-card deletions in-page, then swapping in the freshly generated server Health panel and restoring its current UI state before paint.
 * Patch 1781: simplifies the expired-card age filter to 1, 3, 5, or Custom date. Custom date uses the browser calendar picker and remains dry-run only.
 * Patch 1782: forces layout of the newly revealed Custom date input before opening the native calendar so Chrome anchors the popup beneath the input instead of the page upper-left.
 */

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

function domainmongerpayrouting_config(): array
{
    return [
        'name' => 'DomainMonger Payment Routing',
        'description' => 'Controls update-safe per-product/domain Pay Method assignment testing. Off is the default and leaves native WHMCS billing unchanged.',
        'author' => 'DomainMonger',
        'language' => 'english',
        'version' => '1.2.10',
        'fields' => [
            'routing_mode' => [
                'FriendlyName' => 'Routing Mode',
                'Type' => 'dropdown',
                'Options' => 'Off,Admin Preview (No Routing),Shadow (Test Clients),Test Live (Test Clients),Live (All Clients)',
                'Description' => 'Admin Preview shows/saves assignments in Admin only with no client UI or routing. Shadow exposes test-client UI and logs decisions without changing invoices. Test Live routes only listed client IDs. Live routes all eligible clients.',
                'Default' => 'Off',
            ],
            'test_client_ids' => [
                'FriendlyName' => 'Test Client IDs',
                'Type' => 'text',
                'Size' => '60',
                'Description' => 'Comma-separated WHMCS Client IDs used by Shadow and Test Live modes, for example: 123,456. Admin Preview does not require an allowlist.',
                'Default' => '',
            ],
            'log_decisions' => [
                'FriendlyName' => 'Log Routing Decisions',
                'Type' => 'yesno',
                'Description' => 'Write shadow, routing, fallback, and validation decisions to the WHMCS Activity Log.',
                'Default' => 'on',
            ],
        ],
    ];
}

function domainmongerpayrouting_activate(): array
{
    try {
        $schema = Capsule::schema();
        if (!$schema->hasTable('mod_domainmonger_item_paymethod_assignments')) {
            $schema->create('mod_domainmonger_item_paymethod_assignments', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('userid');
                $table->string('item_type', 16);
                $table->unsignedInteger('item_id');
                $table->unsignedInteger('pay_method_id');
                $table->tinyInteger('separate_invoices_original')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unique(['item_type', 'item_id'], 'dm_item_pm_assignment_unique');
                $table->index(['userid', 'pay_method_id'], 'dm_item_pm_client_paymethod');
                $table->index(['userid', 'separate_invoices_original'], 'dm_item_pm_client_separate');
            });
        }

        return [
            'status' => 'success',
            'description' => 'DomainMonger Payment Routing activated in Off mode. The custom assignment table is ready; configure the module before enabling Shadow or live routing.',
        ];
    } catch (Throwable $e) {
        return [
            'status' => 'error',
            'description' => 'DomainMonger Payment Routing could not create its custom assignment table: ' . $e->getMessage(),
        ];
    }
}

function domainmongerpayrouting_deactivate(): array
{
    return [
        'status' => 'success',
        'description' => 'DomainMonger Payment Routing deactivated. Routing is treated as Off; saved assignments and the custom table are retained.',
    ];
}

function domainmongerpayrouting_test_client_ids(string $raw): array
{
    $parts = preg_split('/[^0-9]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
    $ids = [];
    foreach ($parts ?: [] as $part) {
        $id = (int) $part;
        if ($id > 0) {
            $ids[$id] = true;
        }
    }
    return array_keys($ids);
}

function domainmongerpayrouting_diag_token(): string
{
    $key = 'dm1741_routed_geninvoices_token';
    if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
        try {
            $_SESSION[$key] = bin2hex(random_bytes(24));
        } catch (Throwable $e) {
            $_SESSION[$key] = hash('sha256', session_id() . '|' . microtime(true) . '|dm1741');
        }
    }
    return (string) $_SESSION[$key];
}

function domainmongerpayrouting_health_delete_token(): string
{
    $key = 'dm1784_health_delete_token';
    if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
        try {
            $_SESSION[$key] = bin2hex(random_bytes(24));
        } catch (Throwable $e) {
            $_SESSION[$key] = hash('sha256', session_id() . '|' . microtime(true) . '|dm1784');
        }
    }
    return (string) $_SESSION[$key];
}

function domainmongerpayrouting_health_delete_cutoff(string $mode, string $customDate = ''): array
{
    $mode = strtolower(trim($mode));
    try {
        if (in_array($mode, ['1', '3', '5'], true)) {
            $years = (int) $mode;
            $date = (new DateTimeImmutable('now'))->modify('-' . $years . ' years');
            return [
                'timestamp' => $date->getTimestamp(),
                'label' => 'expired more than ' . $years . ' ' . ($years === 1 ? 'year' : 'years') . ' ago',
            ];
        }
        if ($mode === 'custom') {
            $customDate = trim($customDate);
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $customDate);
            $errors = DateTimeImmutable::getLastErrors();
            if (!$date || (is_array($errors) && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0))) {
                return ['timestamp' => 0, 'label' => ''];
            }
            return [
                'timestamp' => $date->getTimestamp(),
                'label' => 'expired before ' . $date->format('F j, Y'),
            ];
        }
    } catch (Throwable $e) {
        return ['timestamp' => 0, 'label' => ''];
    }
    return ['timestamp' => 0, 'label' => ''];
}

function domainmongerpayrouting_health_current_default_pm(int $clientId): int
{
    if ($clientId <= 0) {
        return 0;
    }
    try {
        $methods = Capsule::table('tblpaymethods')
            ->where('userid', $clientId)
            ->whereNull('deleted_at')
            ->orderBy('order_preference', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'order_preference']);
        foreach ($methods ?: [] as $method) {
            if ((int) ($method->order_preference ?? 0) === 0) {
                return (int) ($method->id ?? 0);
            }
        }
        foreach ($methods ?: [] as $method) {
            $id = (int) ($method->id ?? 0);
            if ($id > 0) {
                return $id;
            }
        }
    } catch (Throwable $e) {
        return 0;
    }
    return 0;
}

function domainmongerpayrouting_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function domainmongerpayrouting_service_rows(int $clientId): array
{
    $GLOBALS['domainmongerpayrouting_service_rows_error'] = '';

    if ($clientId <= 0) {
        return [];
    }

    try {
        // Patch 1740: deliberately avoid the Patch 1739 multi-table JOIN.
        // The product/domain assignment UI already proves the assignment rows
        // exist; read services, products, and assignments independently so a
        // JOIN/alias incompatibility cannot make the entire selector vanish.
        $services = Capsule::table('tblhosting')
            ->where('userid', $clientId)
            ->orderBy('nextduedate', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'id',
                'packageid',
                'domain',
                'domainstatus',
                'nextduedate',
                'billingcycle',
                'paymentmethod',
            ]);

        $serviceRows = [];
        $serviceIds = [];
        $packageIds = [];
        foreach ($services ?: [] as $service) {
            $serviceId = (int) ($service->id ?? 0);
            if ($serviceId <= 0) {
                continue;
            }
            $serviceRows[$serviceId] = $service;
            $serviceIds[] = $serviceId;
            $packageId = (int) ($service->packageid ?? 0);
            if ($packageId > 0) {
                $packageIds[$packageId] = true;
            }
        }

        if (!$serviceRows) {
            return [];
        }

        $productNames = [];
        if ($packageIds) {
            $products = Capsule::table('tblproducts')
                ->whereIn('id', array_keys($packageIds))
                ->get(['id', 'name']);
            foreach ($products ?: [] as $product) {
                $productNames[(int) ($product->id ?? 0)] = trim((string) ($product->name ?? ''));
            }
        }

        $assignmentMap = [];
        $assignments = Capsule::table('mod_domainmonger_item_paymethod_assignments')
            ->where('userid', $clientId)
            ->where('item_type', 'service')
            ->whereIn('item_id', $serviceIds)
            ->where('pay_method_id', '>', 0)
            ->get(['item_id', 'pay_method_id']);
        foreach ($assignments ?: [] as $assignment) {
            $itemId = (int) ($assignment->item_id ?? 0);
            $payMethodId = (int) ($assignment->pay_method_id ?? 0);
            if ($itemId > 0 && $payMethodId > 0) {
                $assignmentMap[$itemId] = $payMethodId;
            }
        }

        $out = [];
        foreach ($serviceRows as $serviceId => $row) {
            $packageId = (int) ($row->packageid ?? 0);
            $row->product_name = $productNames[$packageId] ?? '';
            $row->pay_method_id = $assignmentMap[$serviceId] ?? 0;

            // If the main routing hook is loaded, use its already-proven lookup
            // as a second source of truth. This keeps the diagnostic aligned
            // with the actual product-page selector rather than inventing a
            // separate assignment interpretation.
            if ((int) $row->pay_method_id <= 0 && function_exists('dm1723_get_assignment')) {
                try {
                    $row->pay_method_id = (int) dm1723_get_assignment($clientId, 'service', $serviceId);
                } catch (Throwable $e) {
                    // The independent table lookup above remains authoritative.
                }
            }

            $out[] = $row;
        }

        return $out;
    } catch (Throwable $e) {
        $GLOBALS['domainmongerpayrouting_service_rows_error'] = $e->getMessage();
        if (function_exists('logActivity')) {
            logActivity('DomainMonger Payment Routing [TEST GENINVOICES] Could not build Test Live product selector for Client #' . $clientId . ': ' . $e->getMessage());
        }
        return [];
    }
}

function domainmongerpayrouting_service_rows_error(): string
{
    return trim((string) ($GLOBALS['domainmongerpayrouting_service_rows_error'] ?? ''));
}

function domainmongerpayrouting_domain_rows(int $clientId): array
{
    $GLOBALS['domainmongerpayrouting_domain_rows_error'] = '';

    if ($clientId <= 0) {
        return [];
    }

    try {
        $domains = Capsule::table('tbldomains')
            ->where('userid', $clientId)
            ->orderBy('nextduedate', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'id',
                'domain',
                'status',
                'nextduedate',
                'paymentmethod',
            ]);

        $domainRows = [];
        $domainIds = [];
        foreach ($domains ?: [] as $domain) {
            $domainId = (int) ($domain->id ?? 0);
            if ($domainId <= 0) {
                continue;
            }
            $domainRows[$domainId] = $domain;
            $domainIds[] = $domainId;
        }

        if (!$domainRows) {
            return [];
        }

        $assignmentMap = [];
        $assignments = Capsule::table('mod_domainmonger_item_paymethod_assignments')
            ->where('userid', $clientId)
            ->where('item_type', 'domain')
            ->whereIn('item_id', $domainIds)
            ->where('pay_method_id', '>', 0)
            ->get(['item_id', 'pay_method_id']);
        foreach ($assignments ?: [] as $assignment) {
            $itemId = (int) ($assignment->item_id ?? 0);
            $payMethodId = (int) ($assignment->pay_method_id ?? 0);
            if ($itemId > 0 && $payMethodId > 0) {
                $assignmentMap[$itemId] = $payMethodId;
            }
        }

        $out = [];
        foreach ($domainRows as $domainId => $row) {
            $row->pay_method_id = $assignmentMap[$domainId] ?? 0;

            // Keep the diagnostic aligned with the same proven assignment
            // lookup used by the domain Admin/client selector.
            if ((int) $row->pay_method_id <= 0 && function_exists('dm1723_get_assignment')) {
                try {
                    $row->pay_method_id = (int) dm1723_get_assignment($clientId, 'domain', $domainId);
                } catch (Throwable $e) {
                    // Independent table lookup above remains authoritative.
                }
            }

            $out[] = $row;
        }

        return $out;
    } catch (Throwable $e) {
        $GLOBALS['domainmongerpayrouting_domain_rows_error'] = $e->getMessage();
        if (function_exists('logActivity')) {
            logActivity('DomainMonger Payment Routing [TEST GENINVOICES] Could not build Test Live domain selector for Client #' . $clientId . ': ' . $e->getMessage());
        }
        return [];
    }
}

function domainmongerpayrouting_domain_rows_error(): string
{
    return trim((string) ($GLOBALS['domainmongerpayrouting_domain_rows_error'] ?? ''));
}

function domainmongerpayrouting_paymethod_labels(int $clientId): array
{
    $labels = [];

    // Reuse the proven Patch 1738 display logic when the routing hook is loaded.
    if (function_exists('dm1723_paymethods')) {
        try {
            foreach (dm1723_paymethods($clientId) as $id => $method) {
                $label = function_exists('dm1738_paymethod_dropdown_label')
                    ? dm1738_paymethod_dropdown_label($method)
                    : (string) ($method['label'] ?? ('Pay Method #' . (int) $id));
                $labels[(int) $id] = $label;
            }
        } catch (Throwable $e) {
            // Fall through to the database description fallback below.
        }
    }

    try {
        $rows = Capsule::table('tblpaymethods')
            ->where('userid', $clientId)
            ->whereNull('deleted_at')
            ->get(['id', 'description']);
        foreach ($rows ?: [] as $row) {
            $id = (int) ($row->id ?? 0);
            if ($id <= 0 || isset($labels[$id])) {
                continue;
            }
            $description = trim((string) ($row->description ?? ''));
            $labels[$id] = 'Pay Method #' . $id . ($description !== '' ? ' — ' . $description : '');
        }
    } catch (Throwable $e) {
        // Numeric fallback below remains sufficient for the diagnostic.
    }

    return $labels;
}

function domainmongerpayrouting_new_invoice_ids(int $clientId, int $afterInvoiceId): array
{
    try {
        return array_map('intval', Capsule::table('tblinvoices')
            ->where('userid', $clientId)
            ->where('id', '>', $afterInvoiceId)
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->all());
    } catch (Throwable $e) {
        return [];
    }
}


function domainmongerpayrouting_admin_active_tab(): string
{
    $allowed = ['account', 'assignments', 'health'];

    // Keep POST actions on the workflow that submitted them, even when the
    // form action itself does not include the tab query parameter.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ((string) ($_POST['dm1786_health_action'] ?? '') !== ''
            || (string) ($_POST['dm1784_health_action'] ?? '') !== '') {
            return 'health';
        }
        if ((string) ($_POST['dm1748_account_action'] ?? '') !== '') {
            return 'account';
        }
        if ((string) ($_POST['dm1746_bulk_action'] ?? '') !== ''
            || (string) ($_POST['dm1741_action'] ?? '') !== ''
            || (string) ($_POST['dm1742_action'] ?? '') !== ''
            || (string) ($_POST['dm1744_action'] ?? '') !== '') {
            return 'assignments';
        }
    }

    $requested = strtolower(trim((string) ($_REQUEST['dm1751_tab'] ?? 'account')));
    return in_array($requested, $allowed, true) ? $requested : 'account';
}

function domainmongerpayrouting_admin_tab_url(string $tab): string
{
    $params = [
        'module' => 'domainmongerpayrouting',
        'dm1751_tab' => $tab,
    ];

    $clientId = (int) ($_REQUEST['userid'] ?? $_POST['dm1746_bulk_clientid'] ?? 0);
    if ($clientId > 0) {
        $params['userid'] = $clientId;
    }

    return 'addonmodules.php?' . http_build_query($params);
}

function domainmongerpayrouting_initial_pager_status(int $count): string
{
    if ($count <= 0) {
        return 'Showing 0 matching rows';
    }
    $end = min(25, $count);
    return 'Showing 1–' . $end . ' of ' . $count . ' matching row' . ($count === 1 ? '' : 's');
}

function domainmongerpayrouting_pager_assets(): string
{
    return <<<'HTML'
<style>
.dm1757-table-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 10px}
.dm1757-table-toolbar-left{display:flex;align-items:center;gap:8px;flex:0 0 auto}
.dm1757-table-toolbar-left label{margin:0;color:#555;font-weight:600}
.dm1757-page-size{width:auto;min-width:82px;height:34px;display:inline-block}
.dm1757-table-search{position:relative;flex:0 1 300px;min-width:220px;margin-left:auto}
.dm1758-primary-toolbar{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 10px}
.dm1758-primary-actions{display:flex;align-items:flex-end;gap:8px;flex-wrap:wrap;min-width:0}
.dm1758-action-field{display:flex;flex-direction:column;gap:5px;min-width:260px;max-width:425px}
.dm1758-action-field label{margin:0;color:#163a5f;font-weight:600}
.dm1758-action-field .form-control{width:100%;height:34px}
.dm1758-primary-actions>.btn{height:34px;white-space:nowrap}
.dm1758-secondary-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 10px}
.dm1758-secondary-toolbar .dm1757-pager{margin:0;flex:1 1 auto}
.dm1758-secondary-toolbar .dm1757-pager-status{margin-right:0}
.dm1758-secondary-toolbar .dm1757-table-toolbar-left{min-width:82px}
.dm1758-primary-toolbar>.dm1757-table-search{margin-left:auto}
.dm1757-table-search .fas{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#7b8794;pointer-events:none;z-index:2}
.dm1757-table-search .form-control{width:100%;height:34px;padding-left:31px;border-color:#c7ced6;background:#fff;color:#222}
.dm1757-table-search .form-control:focus{border-color:#214e7a;box-shadow:0 0 0 1px rgba(33,78,122,.12)}
.dm1757-pager{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;margin:10px 0 0}
.dm1757-pager-status{color:#666;font-weight:600;margin-right:0;min-width:275px;min-height:20px;line-height:20px;white-space:nowrap;font-variant-numeric:tabular-nums}
[data-dm1757-pager-root]:not([data-dm1760-pager-ready="1"]) tbody>tr[data-dm1757-row]:nth-of-type(n+26){display:none!important}
.dm1757-pager .btn{min-width:82px;transition:none!important;animation:none!important}
.dm1757-pager .btn:hover,.dm1757-pager .btn:focus{transition:none!important;animation:none!important}
.dm1757-pager .btn[disabled]{opacity:.45;cursor:not-allowed}
.dm1761-bottom-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:10px}
.dm1761-bottom-bar .dm1757-pager{margin:0 0 0 auto}
.dm1761-bottom-actions{display:flex;align-items:center;justify-content:flex-start;gap:10px;flex-wrap:wrap}
.dm1757-no-match{margin:0 0 10px}
tr[data-dm1757-row],tr[data-dm1757-row]>td{transition:none!important;animation:none!important}
tr[data-dm1757-row]:hover>td{background:#f7f8fa!important;transition:none!important;animation:none!important}
@media(max-width:767px){.dm1757-table-search{flex:1 1 100%;max-width:none;margin-left:0}.dm1757-table-toolbar{align-items:stretch}.dm1757-pager-status{flex:1 1 100%}.dm1758-primary-toolbar{align-items:stretch}.dm1758-primary-actions{width:100%}.dm1758-action-field{flex:1 1 260px;max-width:none}.dm1758-secondary-toolbar{align-items:stretch}.dm1758-secondary-toolbar .dm1757-pager{flex:1 1 100%}.dm1758-secondary-toolbar .dm1757-pager-status{flex:1 1 100%}.dm1761-bottom-bar{align-items:stretch}.dm1761-bottom-actions{flex:1 1 100%}.dm1761-bottom-bar .dm1757-pager{flex:1 1 100%;margin-left:0}}
</style>
<script>
(function(){
    if (window.dm1757InitPager) { return; }
    window.dm1757InitPager = function(root, options){
        if (!root) { return null; }
        options = options || {};
        if (root._dm1757Pager) { return root._dm1757Pager; }
        var rows = Array.prototype.slice.call(root.querySelectorAll('[data-dm1757-row]'));
        rows.forEach(function(row){ row._dm1757Parent = row.parentNode; });
        var search = root.querySelector('[data-dm1757-search]');
        var size = root.querySelector('[data-dm1757-page-size]');
        var prevs = Array.prototype.slice.call(root.querySelectorAll('[data-dm1757-prev]'));
        var nexts = Array.prototype.slice.call(root.querySelectorAll('[data-dm1757-next]'));
        var statuses = Array.prototype.slice.call(root.querySelectorAll('[data-dm1757-status]'));
        var noMatch = root.querySelector('[data-dm1757-no-match]');
        var page = 1;

        function pageSize(){
            if (!size || size.value === 'all') { return Number.MAX_SAFE_INTEGER || 9007199254740991; }
            var n = parseInt(size.value, 10);
            return n > 0 ? n : 25;
        }
        function needle(){ return search ? String(search.value || '').toLowerCase().trim() : ''; }
        function rowMatches(row){
            if (row.getAttribute('data-dm1757-filter-hidden') === '1') { return false; }
            var q = needle();
            if (!q) { return true; }
            var hay = String(row.getAttribute('data-dm1757-search-text') || row.textContent || '').toLowerCase();
            return hay.indexOf(q) !== -1;
        }
        function matchingRows(){ return rows.filter(rowMatches); }
        function fire(detail){
            try { root.dispatchEvent(new CustomEvent('dm1757:render', {detail: detail})); }
            catch (e) {
                var ev = document.createEvent('CustomEvent');
                ev.initCustomEvent('dm1757:render', false, false, detail);
                root.dispatchEvent(ev);
            }
        }
        function render(resetPage){
            if (resetPage) { page = 1; }
            var matches = matchingRows();
            var perPage = pageSize();
            var totalPages = matches.length ? Math.max(1, Math.ceil(matches.length / perPage)) : 1;
            if (page > totalPages) { page = totalPages; }
            if (page < 1) { page = 1; }
            var start = matches.length ? (page - 1) * perPage : 0;
            var end = Math.min(start + perPage, matches.length);
            var visible = matches.slice(start, end);
            // Keep the full dataset in memory for instant search/filtering, but
            // detach non-visible rows from the live table DOM. This materially
            // reduces CSS/layout work and keeps row hover feedback immediate on
            // large WHMCS accounts while preserving selections across pages.
            rows.forEach(function(row){
                if (row.parentNode) { row.parentNode.removeChild(row); }
            });
            visible.forEach(function(row){
                var parent = row._dm1757Parent;
                if (parent) { parent.appendChild(row); }
            });
            var statusText = matches.length
                ? ('Showing ' + (start + 1) + '–' + end + ' of ' + matches.length + ' matching row' + (matches.length === 1 ? '' : 's'))
                : 'Showing 0 matching rows';
            statuses.forEach(function(status){ status.textContent = statusText; });
            prevs.forEach(function(prev){ prev.disabled = page <= 1 || matches.length === 0; });
            nexts.forEach(function(next){ next.disabled = page >= totalPages || matches.length === 0; });
            if (noMatch) { noMatch.style.display = matches.length === 0 ? '' : 'none'; }
            var detail = {page:page,totalPages:totalPages,matches:matches,visible:visible,totalRows:rows.length,start:start,end:end};
            fire(detail);
            if (typeof options.onRender === 'function') { options.onRender(detail); }
            return detail;
        }
        function attachAll(){
            rows.forEach(function(row){
                var parent = row._dm1757Parent;
                if (parent && row.parentNode !== parent) { parent.appendChild(row); }
            });
        }
        if (search) { search.addEventListener('input', function(){ render(true); }); }
        if (size) { size.addEventListener('change', function(){ render(true); }); }
        prevs.forEach(function(prev){ prev.addEventListener('click', function(){ if (page > 1) { page--; render(false); } }); });
        nexts.forEach(function(next){ next.addEventListener('click', function(){ var m=matchingRows(); var pages=m.length?Math.max(1,Math.ceil(m.length/pageSize())):1; if(page<pages){page++;render(false);} }); });
        // Detached checked rows still need to be successful form controls. Put
        // every row back immediately before a form submit; checkbox state is
        // retained while rows are detached between pages.
        if (root.tagName && root.tagName.toLowerCase() === 'form') {
            root.addEventListener('submit', attachAll, true);
        }
        var api = {
            render: render,
            reset: function(){ return render(true); },
            getVisibleRows: function(){ return matchingRows().slice((page-1)*pageSize(), Math.min(page*pageSize(), matchingRows().length)); },
            getMatchingRows: matchingRows,
            getRows: function(){ return rows.slice(); },
            sortRows: function(compareFn){
                if (typeof compareFn === 'function') { rows.sort(compareFn); }
                return render(true);
            },
            getPage: function(){ return page; },
            attachAll: attachAll
        };
        root._dm1757Pager = api;
        root.setAttribute('data-dm1760-pager-ready', '1');
        render(true);
        return api;
    };
})();
</script>
HTML;
}

function domainmongerpayrouting_admin_tabs_nav(string $active): string
{
    $tabs = [
        'account' => 'Account Routing',
        'assignments' => 'Payment Assignments',
        'health' => 'Payment Method Health',
    ];

    $html = '<div class="dm1751-tabs-wrap"><ul class="nav nav-tabs dm1751-tabs" role="tablist">';
    foreach ($tabs as $key => $label) {
        $class = $active === $key ? ' class="active"' : '';
        $html .= '<li role="presentation"' . $class . '><a href="'
            . domainmongerpayrouting_escape(domainmongerpayrouting_admin_tab_url($key)) . '">'
            . domainmongerpayrouting_escape($label) . '</a></li>';
    }
    $html .= '</ul>';
    $html .= <<<'HTML'
<style>
.dm1751-tabs-wrap{margin-top:15px}
.dm1751-tabs{margin-bottom:0;border-bottom:1px solid #d7dce2}
.dm1751-tabs>li>a{color:#163a5f;background:#f7f8fa;border:1px solid transparent;border-bottom:0;text-decoration:none;font-weight:600;transition:none!important;animation:none!important}
.dm1751-tabs>li>a:hover,.dm1751-tabs>li>a:focus{color:#f58220;background:#fff;text-decoration:none}
.dm1751-tabs>li.active>a,.dm1751-tabs>li.active>a:hover,.dm1751-tabs>li.active>a:focus{background:#163a5f!important;color:#fff!important;border-color:#163a5f!important}
.dm1751-tab-pane{display:none;padding-top:1px}
.dm1751-tab-pane.dm1751-active{display:block}
.dm1754-working-spinner{margin-right:6px}
.dm1754-loading{pointer-events:none;opacity:.82}
</style>
<script>
(function(){
    var nav = document.querySelector('.dm1751-tabs');
    if (!nav || nav.getAttribute('data-dm1754-spinner-ready') === '1') { return; }
    nav.setAttribute('data-dm1754-spinner-ready', '1');
    nav.addEventListener('click', function(event){
        var target = event.target;
        var link = target && target.closest ? target.closest('a') : null;
        if (!link || event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) { return; }
        if (typeof event.button === 'number' && event.button !== 0) { return; }
        var href = link.getAttribute('href');
        if (!href) { return; }
        event.preventDefault();
        if (!link.querySelector('.dm1754-tab-spinner')) {
            var spinner = document.createElement('i');
            spinner.className = 'fas fa-spinner fa-spin dm1754-working-spinner dm1754-tab-spinner';
            spinner.setAttribute('aria-hidden', 'true');
            link.insertBefore(spinner, link.firstChild);
        }
        link.classList.add('dm1754-loading');
        if (window.requestAnimationFrame) {
            window.requestAnimationFrame(function(){ window.location.href = href; });
        } else {
            window.setTimeout(function(){ window.location.href = href; }, 0);
        }
    });
})();
</script>
HTML;
    $html .= domainmongerpayrouting_pager_assets();
    return $html;
}

function domainmongerpayrouting_output(array $vars): void
{
    $modeRaw = (string) ($vars['routing_mode'] ?? 'Off');
    $testClientsRaw = (string) ($vars['test_client_ids'] ?? '');
    $mode = domainmongerpayrouting_escape($modeRaw);
    $testClients = domainmongerpayrouting_escape($testClientsRaw);
    $testClientIds = domainmongerpayrouting_test_client_ids($testClientsRaw);
    $isTestLive = stripos($modeRaw, 'Test Live') !== false;
    $notice = null;

    // Patch 1787: controlled batch-delete test for 1-25 selected expired cards.
    // Rebuilt from the confirmed 1784 single-delete baseline. Selected PM/client
    // pairs are submitted as ordinary PHP form arrays (not JSON) and every target
    // is re-read and validated before ANY deletion begins.
    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && (string) ($_POST['dm1786_health_action'] ?? '') === 'delete_expired_batch_test') {
        $submittedToken = (string) ($_POST['dm1786_health_token'] ?? '');
        $expectedToken = domainmongerpayrouting_health_delete_token();
        unset($_SESSION['dm1784_health_delete_token']);

        $ageMode = (string) ($_POST['dm1786_age_mode'] ?? '');
        $customDate = (string) ($_POST['dm1786_custom_date'] ?? '');
        $cutoff = domainmongerpayrouting_health_delete_cutoff($ageMode, $customDate);
        $cutoffTs = (int) ($cutoff['timestamp'] ?? 0);
        $cutoffLabel = (string) ($cutoff['label'] ?? '');

        $pmIdsRaw = $_POST['dm1786_pm_ids'] ?? [];
        $clientIdsRaw = $_POST['dm1786_client_ids'] ?? [];
        if (!is_array($pmIdsRaw)) {
            $pmIdsRaw = [$pmIdsRaw];
        }
        if (!is_array($clientIdsRaw)) {
            $clientIdsRaw = [$clientIdsRaw];
        }
        $targets = [];
        $pairCount = max(count($pmIdsRaw), count($clientIdsRaw));
        for ($i = 0; $i < $pairCount; $i++) {
            $payMethodId = (int) ($pmIdsRaw[$i] ?? 0);
            $clientId = (int) ($clientIdsRaw[$i] ?? 0);
            if ($payMethodId > 0 && $clientId > 0 && !isset($targets[$payMethodId])) {
                $targets[$payMethodId] = ['pm' => $payMethodId, 'client' => $clientId];
            }
        }
        $targets = array_values($targets);

        if ($submittedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
            $notice = ['danger', 'The expired-card batch deletion form expired or was already submitted. Nothing was deleted.'];
        } elseif (count($pmIdsRaw) !== count($clientIdsRaw)) {
            $notice = ['danger', 'The batch selection submission was incomplete (received ' . count($pmIdsRaw) . ' PM IDs and ' . count($clientIdsRaw) . ' Client IDs). Nothing was deleted.'];
        } elseif (count($targets) < 1 || count($targets) > 25) {
            $notice = ['danger', 'Select between 1 and 25 unique expired credit cards for this batch test. Server received ' . count($targets) . ' valid target(s). Nothing was deleted.'];
        } elseif ($cutoffTs <= 0) {
            $notice = ['danger', 'Choose a valid 1-year, 3-year, 5-year, or Custom expiration cutoff before deleting. Nothing was deleted.'];
        } elseif (!function_exists('localAPI')) {
            $notice = ['danger', 'WHMCS Local API is unavailable. Nothing was deleted.'];
        } else {
            $validated = [];
            $validationError = '';
            try {
                // Phase 1: validate every selected PM before deleting anything.
                foreach ($targets as $target) {
                    $payMethodId = (int) $target['pm'];
                    $clientId = (int) $target['client'];
                    $pm = Capsule::table('tblpaymethods')
                        ->where('id', $payMethodId)
                        ->where('userid', $clientId)
                        ->whereNull('deleted_at')
                        ->first(['id', 'userid', 'description', 'payment_type', 'gateway_name', 'order_preference']);
                    $card = Capsule::table('tblcreditcards')
                        ->where('pay_method_id', $payMethodId)
                        ->whereNull('deleted_at')
                        ->first(['pay_method_id', 'card_type', 'last_four', 'expiry_date']);

                    if (!$pm || !$card) {
                        $validationError = 'PM #' . $payMethodId . ' is no longer an active stored credit card for Client #' . $clientId . '.';
                        break;
                    }
                    if (strcasecmp(trim((string) ($card->card_type ?? '')), 'PayPal') === 0) {
                        $validationError = 'PM #' . $payMethodId . ' is a PayPal method, not an expired credit card.';
                        break;
                    }
                    $expiry = domainmongerpayrouting_health_expiry((string) ($card->expiry_date ?? ''));
                    $expiryEnd = $expiry ? (int) ($expiry['end_ts'] ?? 0) : 0;
                    if (!$expiry || empty($expiry['expired']) || $expiryEnd <= 0 || $expiryEnd >= $cutoffTs) {
                        $validationError = 'PM #' . $payMethodId . ' did not pass the server-side cutoff check (' . ($cutoffLabel !== '' ? $cutoffLabel : 'selected cutoff') . ').';
                        break;
                    }
                    $validated[] = [
                        'pm' => $payMethodId,
                        'client' => $clientId,
                        'expiry' => (string) ($expiry['display'] ?? '—'),
                        'was_default' => domainmongerpayrouting_health_current_default_pm($clientId) === $payMethodId,
                    ];
                }

                if ($validationError !== '') {
                    $notice = ['danger', 'Batch prevalidation failed: ' . $validationError . ' Nothing was deleted.'];
                } else {
                    // Phase 2: delete only after ALL selected targets passed validation.
                    $deleted = [];
                    $failure = '';
                    foreach ($validated as $item) {
                        $payMethodId = (int) $item['pm'];
                        $clientId = (int) $item['client'];
                        $result = localAPI('DeletePayMethod', [
                            'clientid' => $clientId,
                            'paymethodid' => $payMethodId,
                            'failonremotefailure' => true,
                        ]);
                        if (($result['result'] ?? '') !== 'success') {
                            $failure = 'PM #' . $payMethodId . ' for Client #' . $clientId . ' was not deleted: ' . (string) ($result['message'] ?? 'WHMCS returned an unknown deletion error.');
                            break;
                        }
                        $stillActive = Capsule::table('tblpaymethods')
                            ->where('id', $payMethodId)
                            ->where('userid', $clientId)
                            ->whereNull('deleted_at')
                            ->exists();
                        if ($stillActive) {
                            $failure = 'WHMCS returned success for PM #' . $payMethodId . ', but it still appears active. Processing stopped.';
                            break;
                        }
                        $newDefault = domainmongerpayrouting_health_current_default_pm($clientId);
                        $deleted[] = $item + ['new_default' => $newDefault];
                        if (function_exists('logActivity')) {
                            logActivity('DomainMonger Payment Routing [HEALTH 1797 BATCH DELETE] Deleted PM #' . $payMethodId . ' for Client #' . $clientId . '; expired ' . $item['expiry'] . '; cutoff ' . $cutoffLabel . '; was_default=' . (!empty($item['was_default']) ? 'yes' : 'no') . '; new_default_pm=' . $newDefault . '.');
                        }
                    }

                    $deletedCount = count($deleted);
                    $selectedCount = count($validated);
                    $defaultDeleted = count(array_filter($deleted, static function ($item) { return !empty($item['was_default']); }));
                    if ($failure !== '') {
                        $notice = ['warning', 'Batch deletion stopped after ' . $deletedCount . ' of ' . $selectedCount . ' selected cards were deleted. ' . $failure . ' Do not retry blindly; review the remaining cards first.'];
                    } else {
                        $notice = ['success', 'Expired-card batch deletion succeeded: ' . $deletedCount . ' of ' . $selectedCount . ' selected cards were deleted. ' . $defaultDeleted . ' of the deleted cards were Default PMs. Maximum server batch size remains 25.'];
                    }
                }
            } catch (Throwable $e) {
                $notice = ['danger', 'The expired-card batch deletion failed safely: ' . $e->getMessage() . ' Review the Activity Log before trying another batch.'];
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && (string) ($_POST['dm1741_action'] ?? '') === 'gen_selected_services_routed') {
        $submittedToken = (string) ($_POST['dm1741_token'] ?? '');
        $sessionToken = domainmongerpayrouting_diag_token();

        // One-time token: refreshing/reposting the result page cannot generate again.
        unset($_SESSION['dm1741_routed_geninvoices_token']);

        if (!$isTestLive) {
            $notice = ['danger', 'Test Routed Generate Invoices is available only while Routing Mode is Test Live.'];
        } elseif (count($testClientIds) !== 1) {
            $notice = ['danger', 'This diagnostic requires exactly one Test Client ID so it cannot target the wrong account.'];
        } elseif ($submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            $notice = ['danger', 'The routed-generation test form expired or was already submitted. Nothing was generated.'];
        } else {
            $clientId = (int) $testClientIds[0];
            $selected = $_POST['dm1741_service_ids'] ?? [];
            if (!is_array($selected)) {
                $selected = [$selected];
            }
            $serviceIds = [];
            foreach ($selected as $serviceId) {
                $serviceId = (int) $serviceId;
                if ($serviceId > 0) {
                    $serviceIds[$serviceId] = true;
                }
            }
            $serviceIds = array_keys($serviceIds);

            if (count($serviceIds) < 2) {
                $notice = ['warning', 'Select at least two products for this routed grouped-invoice test. Nothing was generated.'];
            } else {
                try {
                    $services = Capsule::table('tblhosting')
                        ->where('userid', $clientId)
                        ->whereIn('id', $serviceIds)
                        ->get(['id', 'domainstatus', 'nextduedate']);

                    $serviceMap = [];
                    foreach ($services ?: [] as $service) {
                        $serviceMap[(int) ($service->id ?? 0)] = $service;
                    }

                    $assignments = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                        ->where('userid', $clientId)
                        ->where('item_type', 'service')
                        ->whereIn('item_id', $serviceIds)
                        ->get(['item_id', 'pay_method_id']);
                    $assignmentMap = [];
                    foreach ($assignments ?: [] as $assignment) {
                        $assignmentMap[(int) ($assignment->item_id ?? 0)] = (int) ($assignment->pay_method_id ?? 0);
                    }

                    // Keep the same fallback used by the working product-page selector.
                    foreach ($serviceIds as $serviceId) {
                        if (($assignmentMap[$serviceId] ?? 0) <= 0 && function_exists('dm1723_get_assignment')) {
                            $fallbackId = (int) dm1723_get_assignment($clientId, 'service', $serviceId);
                            if ($fallbackId > 0) {
                                $assignmentMap[$serviceId] = $fallbackId;
                            }
                        }
                    }

                    $client = Capsule::table('tblclients')->where('id', $clientId)->first(['separateinvoices']);
                    $separateInvoices = $client ? (bool) ($client->separateinvoices ?? false) : false;

                    if (count($serviceMap) !== count($serviceIds)) {
                        $notice = ['danger', 'One or more selected products do not belong to the configured Test Live client. Nothing was generated.'];
                    } elseif (!$separateInvoices) {
                        $notice = ['warning', 'WHMCS Separate Invoices is OFF for this client. Enable it before this test. Nothing was generated.'];
                    } elseif (count($assignmentMap) !== count($serviceIds) || min($assignmentMap) <= 0) {
                        $notice = ['warning', 'Every selected product must have an explicit saved Payment Method assignment. Nothing was generated.'];
                    } elseif (count(array_unique(array_values($assignmentMap))) < 2) {
                        $notice = ['warning', 'For this test, the selected products must include at least two DIFFERENT Payment Methods. Nothing was generated.'];
                    } else {
                        $dueDates = [];
                        foreach ($serviceIds as $serviceId) {
                            $dueDates[] = (string) ($serviceMap[$serviceId]->nextduedate ?? '');
                        }
                        $uniqueDueDates = array_values(array_unique($dueDates));
                        $dueDate = $uniqueDueDates[0] ?? '';
                        if ($dueDate === '' || $dueDate === '0000-00-00' || count($uniqueDueDates) !== 1) {
                            $notice = ['warning', 'For this test, all selected products must have the SAME Next Due Date. Nothing was generated.'];
                        } elseif (!function_exists('localAPI')) {
                            $notice = ['danger', 'WHMCS Local API is unavailable. Nothing was generated.'];
                        } else {
                            $beforeInvoiceId = (int) (Capsule::table('tblinvoices')->where('userid', $clientId)->max('id') ?: 0);

                            // Core Patch 1741 test: split the selected services by explicit
                            // Pay Method BEFORE asking native WHMCS to generate invoices.
                            // Services that share a Pay Method would be sent in the same call;
                            // different Pay Methods are deliberately sent in separate calls.
                            $groups = [];
                            foreach ($serviceIds as $serviceId) {
                                $payMethodId = (int) ($assignmentMap[$serviceId] ?? 0);
                                $groups[$payMethodId][] = (int) $serviceId;
                            }

                            $groupResults = [];
                            $generationError = '';
                            foreach ($groups as $payMethodId => $groupServiceIds) {
                                try {
                                    $result = localAPI('GenInvoices', [
                                        'clientid' => $clientId,
                                        'serviceids' => array_values(array_map('intval', $groupServiceIds)),
                                        'noemails' => true,
                                    ]);
                                } catch (Throwable $e) {
                                    $result = ['result' => 'error', 'message' => $e->getMessage()];
                                }

                                $groupResults[(int) $payMethodId] = $result;
                                if (!is_array($result) || strtolower((string) ($result['result'] ?? '')) !== 'success') {
                                    $generationError = is_array($result)
                                        ? trim((string) ($result['message'] ?? $result['error'] ?? 'Unknown WHMCS GenInvoices error.'))
                                        : 'WHMCS returned an invalid GenInvoices response.';
                                    break;
                                }
                            }

                            $newInvoiceIds = domainmongerpayrouting_new_invoice_ids($clientId, $beforeInvoiceId);
                            $labels = domainmongerpayrouting_paymethod_labels($clientId);

                            if ($generationError !== '') {
                                $idsText = $newInvoiceIds ? ' Invoice IDs created before the error: ' . implode(', ', $newInvoiceIds) . '.' : '';
                                $notice = ['danger', 'Test Routed Generate Invoices stopped because WHMCS returned an error: ' . $generationError . $idsText . ' Invoice-created emails were suppressed. Review any listed invoice before retrying.'];
                            } else {
                                // Verify each selected service landed on a new invoice whose
                                // native invoice paymethodid matches that service assignment.
                                $invoiceRows = [];
                                if ($newInvoiceIds) {
                                    $rows = Capsule::table('tblinvoices')
                                        ->whereIn('id', $newInvoiceIds)
                                        ->get(['id', 'paymethodid', 'paymentmethod', 'status']);
                                    foreach ($rows ?: [] as $row) {
                                        $invoiceRows[(int) ($row->id ?? 0)] = $row;
                                    }
                                }

                                $serviceInvoice = [];
                                if ($newInvoiceIds) {
                                    $items = Capsule::table('tblinvoiceitems')
                                        ->whereIn('invoiceid', $newInvoiceIds)
                                        ->whereIn('relid', $serviceIds)
                                        ->get(['invoiceid', 'type', 'relid']);
                                    foreach ($items ?: [] as $item) {
                                        $type = strtolower(trim((string) ($item->type ?? '')));
                                        $serviceId = (int) ($item->relid ?? 0);
                                        if ($serviceId > 0 && ($type === 'hosting' || $type === 'service' || strpos($type, 'hosting') !== false)) {
                                            $serviceInvoice[$serviceId] = (int) ($item->invoiceid ?? 0);
                                        }
                                    }
                                }


                                $verification = [];
                                $expectedInvoiceCount = count($groups);
                                $allVerified = count($newInvoiceIds) === $expectedInvoiceCount;
                                $groupInvoiceMap = [];
                                foreach ($serviceIds as $serviceId) {
                                    $expectedPayMethodId = (int) ($assignmentMap[$serviceId] ?? 0);
                                    $invoiceId = (int) ($serviceInvoice[$serviceId] ?? 0);
                                    $actualPayMethodId = $invoiceId > 0 && isset($invoiceRows[$invoiceId])
                                        ? (int) ($invoiceRows[$invoiceId]->paymethodid ?? 0)
                                        : 0;
                                    $matched = $invoiceId > 0 && $actualPayMethodId === $expectedPayMethodId;
                                    if (!$matched) {
                                        $allVerified = false;
                                    }
                                    if ($invoiceId > 0) {
                                        $groupInvoiceMap[$expectedPayMethodId][$invoiceId] = true;
                                    }
                                    $verification[] = 'Service #' . $serviceId
                                        . ' → Invoice #' . ($invoiceId > 0 ? $invoiceId : 0)
                                        . ' → ' . ($labels[$actualPayMethodId] ?? ('Pay Method #' . $actualPayMethodId))
                                        . ($matched ? ' [matched]' : ' [EXPECTED Pay Method #' . $expectedPayMethodId . ']');
                                }

                                $groupVerification = [];
                                foreach ($groups as $payMethodId => $groupServiceIds) {
                                    $invoiceIdsForGroup = array_keys($groupInvoiceMap[(int) $payMethodId] ?? []);
                                    $oneGroupInvoice = count($invoiceIdsForGroup) === 1;
                                    if (!$oneGroupInvoice) {
                                        $allVerified = false;
                                    }
                                    $groupVerification[] = 'PM#' . (int) $payMethodId
                                        . ' services ' . implode(',', $groupServiceIds)
                                        . ' → invoice' . (count($invoiceIdsForGroup) === 1 ? ' #' . $invoiceIdsForGroup[0] : 's ' . ($invoiceIdsForGroup ? implode(',', $invoiceIdsForGroup) : 'none'))
                                        . ($oneGroupInvoice ? ' [grouped]' : ' [NOT grouped as one invoice]');
                                }

                                $idsText = $newInvoiceIds ? ' Invoice IDs: ' . implode(', ', $newInvoiceIds) . '.' : '';
                                if ($allVerified) {
                                    $notice = ['success', 'Routed generation created ' . count($newInvoiceIds) . ' invoices for ' . count($serviceIds) . ' same-date products across ' . $expectedInvoiceCount . ' Payment Method groups. Products sharing a Payment Method stayed together and every invoice received the correct assigned Payment Method.' . $idsText . ' ' . implode(' | ', $verification) . ' Grouping: ' . implode(' | ', $groupVerification) . ' Invoice-created emails were suppressed.'];
                                } elseif (!$newInvoiceIds) {
                                    $notice = ['info', 'Routed generation created 0 new invoices. These products may already have renewal invoices for this due date or may be outside the invoice-generation window. No invoice-created emails were sent.'];
                                } else {
                                    $notice = ['warning', 'Routed generation completed, but multi-item grouping did not fully match the intended one-invoice-per-Payment-Method result. Expected ' . $expectedInvoiceCount . ' invoice(s) from ' . count($serviceIds) . ' selected products; created ' . count($newInvoiceIds) . '.' . $idsText . ' ' . implode(' | ', $verification) . ' Grouping: ' . implode(' | ', $groupVerification) . ' Invoice-created emails were suppressed. Do not automate this path yet.'];
                                }

                                if (function_exists('logActivity')) {
                                    $groupLog = [];
                                    foreach ($groups as $payMethodId => $groupServiceIds) {
                                        $result = $groupResults[(int) $payMethodId] ?? [];
                                        $groupLog[] = 'PM#' . (int) $payMethodId . ' services=' . implode(',', $groupServiceIds) . ' numcreated=' . (int) ($result['numcreated'] ?? 0);
                                    }
                                    logActivity('DomainMonger Payment Routing [TEST ROUTED GENINVOICES] Client #' . $clientId
                                        . '; same due date ' . $dueDate
                                        . '; ' . implode('; ', $groupLog)
                                        . ($newInvoiceIds ? '; invoice IDs ' . implode(',', $newInvoiceIds) : '')
                                        . '; verification ' . implode(' | ', $verification)
                                        . '; noemails=true.');
                                }
                            }
                        }
                    }
                } catch (Throwable $e) {
                    $notice = ['danger', 'The routed-generation test could not be prepared safely: ' . $e->getMessage() . ' Nothing further was intentionally generated after this error.'];
                }
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && (string) ($_POST['dm1742_action'] ?? '') === 'gen_selected_domains_routed') {
        $submittedToken = (string) ($_POST['dm1742_token'] ?? '');
        $sessionToken = domainmongerpayrouting_diag_token();

        // Same one-time protection as the proven product diagnostic.
        unset($_SESSION['dm1741_routed_geninvoices_token']);

        if (!$isTestLive) {
            $notice = ['danger', 'Test Routed Generate Domains is available only while Routing Mode is Test Live.'];
        } elseif (count($testClientIds) !== 1) {
            $notice = ['danger', 'This diagnostic requires exactly one Test Client ID so it cannot target the wrong account.'];
        } elseif ($submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            $notice = ['danger', 'The routed domain-generation test form expired or was already submitted. Nothing was generated.'];
        } else {
            $clientId = (int) $testClientIds[0];
            $selected = $_POST['dm1742_domain_ids'] ?? [];
            if (!is_array($selected)) {
                $selected = [$selected];
            }
            $domainIds = [];
            foreach ($selected as $domainId) {
                $domainId = (int) $domainId;
                if ($domainId > 0) {
                    $domainIds[$domainId] = true;
                }
            }
            $domainIds = array_keys($domainIds);

            if (count($domainIds) < 2) {
                $notice = ['warning', 'Select at least two domains for this routed grouped-invoice test. Nothing was generated.'];
            } else {
                try {
                    $domains = Capsule::table('tbldomains')
                        ->where('userid', $clientId)
                        ->whereIn('id', $domainIds)
                        ->get(['id', 'domain', 'status', 'nextduedate']);

                    $domainMap = [];
                    foreach ($domains ?: [] as $domain) {
                        $domainMap[(int) ($domain->id ?? 0)] = $domain;
                    }

                    $assignments = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                        ->where('userid', $clientId)
                        ->where('item_type', 'domain')
                        ->whereIn('item_id', $domainIds)
                        ->get(['item_id', 'pay_method_id']);
                    $assignmentMap = [];
                    foreach ($assignments ?: [] as $assignment) {
                        $assignmentMap[(int) ($assignment->item_id ?? 0)] = (int) ($assignment->pay_method_id ?? 0);
                    }

                    foreach ($domainIds as $domainId) {
                        if (($assignmentMap[$domainId] ?? 0) <= 0 && function_exists('dm1723_get_assignment')) {
                            $fallbackId = (int) dm1723_get_assignment($clientId, 'domain', $domainId);
                            if ($fallbackId > 0) {
                                $assignmentMap[$domainId] = $fallbackId;
                            }
                        }
                    }

                    $client = Capsule::table('tblclients')->where('id', $clientId)->first(['separateinvoices']);
                    $separateInvoices = $client ? (bool) ($client->separateinvoices ?? false) : false;

                    if (count($domainMap) !== count($domainIds)) {
                        $notice = ['danger', 'One or more selected domains do not belong to the configured Test Live client. Nothing was generated.'];
                    } elseif (!$separateInvoices) {
                        $notice = ['warning', 'WHMCS Separate Invoices is OFF for this client. Enable it before this test. Nothing was generated.'];
                    } elseif (count($assignmentMap) !== count($domainIds) || min($assignmentMap) <= 0) {
                        $notice = ['warning', 'Every selected domain must have an explicit saved Payment Method assignment. Nothing was generated.'];
                    } elseif (count(array_unique(array_values($assignmentMap))) < 2) {
                        $notice = ['warning', 'For this test, the selected domains must include at least two DIFFERENT Payment Methods. Nothing was generated.'];
                    } else {
                        $dueDates = [];
                        foreach ($domainIds as $domainId) {
                            $dueDates[] = (string) ($domainMap[$domainId]->nextduedate ?? '');
                        }
                        $uniqueDueDates = array_values(array_unique($dueDates));
                        $dueDate = $uniqueDueDates[0] ?? '';
                        if ($dueDate === '' || $dueDate === '0000-00-00' || count($uniqueDueDates) !== 1) {
                            $notice = ['warning', 'For this test, all selected domains must have the SAME Next Due Date. Nothing was generated.'];
                        } elseif (!function_exists('localAPI')) {
                            $notice = ['danger', 'WHMCS Local API is unavailable. Nothing was generated.'];
                        } else {
                            $beforeInvoiceId = (int) (Capsule::table('tblinvoices')->where('userid', $clientId)->max('id') ?: 0);

                            // Domain equivalent of the proven Patch 1741 service test:
                            // split domain IDs by explicit Pay Method before calling
                            // native GenInvoices. WHMCS officially supports domainids[].
                            $groups = [];
                            foreach ($domainIds as $domainId) {
                                $payMethodId = (int) ($assignmentMap[$domainId] ?? 0);
                                $groups[$payMethodId][] = (int) $domainId;
                            }

                            $groupResults = [];
                            $generationError = '';
                            foreach ($groups as $payMethodId => $groupDomainIds) {
                                try {
                                    $result = localAPI('GenInvoices', [
                                        'clientid' => $clientId,
                                        'domainids' => array_values(array_map('intval', $groupDomainIds)),
                                        'noemails' => true,
                                    ]);
                                } catch (Throwable $e) {
                                    $result = ['result' => 'error', 'message' => $e->getMessage()];
                                }

                                $groupResults[(int) $payMethodId] = $result;
                                if (!is_array($result) || strtolower((string) ($result['result'] ?? '')) !== 'success') {
                                    $generationError = is_array($result)
                                        ? trim((string) ($result['message'] ?? $result['error'] ?? 'Unknown WHMCS GenInvoices error.'))
                                        : 'WHMCS returned an invalid GenInvoices response.';
                                    break;
                                }
                            }

                            $newInvoiceIds = domainmongerpayrouting_new_invoice_ids($clientId, $beforeInvoiceId);
                            $labels = domainmongerpayrouting_paymethod_labels($clientId);

                            if ($generationError !== '') {
                                $idsText = $newInvoiceIds ? ' Invoice IDs created before the error: ' . implode(', ', $newInvoiceIds) . '.' : '';
                                $notice = ['danger', 'Test Routed Generate Domains stopped because WHMCS returned an error: ' . $generationError . $idsText . ' Invoice-created emails were suppressed. Review any listed invoice before retrying.'];
                            } else {
                                $invoiceRows = [];
                                if ($newInvoiceIds) {
                                    $rows = Capsule::table('tblinvoices')
                                        ->whereIn('id', $newInvoiceIds)
                                        ->get(['id', 'paymethodid', 'paymentmethod', 'status']);
                                    foreach ($rows ?: [] as $row) {
                                        $invoiceRows[(int) ($row->id ?? 0)] = $row;
                                    }
                                }

                                $domainInvoice = [];
                                if ($newInvoiceIds) {
                                    $items = Capsule::table('tblinvoiceitems')
                                        ->whereIn('invoiceid', $newInvoiceIds)
                                        ->whereIn('relid', $domainIds)
                                        ->get(['invoiceid', 'type', 'relid']);
                                    foreach ($items ?: [] as $item) {
                                        $type = strtolower(trim((string) ($item->type ?? '')));
                                        $domainId = (int) ($item->relid ?? 0);
                                        if ($domainId > 0 && strpos($type, 'domain') !== false) {
                                            $domainInvoice[$domainId] = (int) ($item->invoiceid ?? 0);
                                        }
                                    }
                                }


                                $verification = [];
                                $expectedInvoiceCount = count($groups);
                                $allVerified = count($newInvoiceIds) === $expectedInvoiceCount;
                                $groupInvoiceMap = [];
                                foreach ($domainIds as $domainId) {
                                    $expectedPayMethodId = (int) ($assignmentMap[$domainId] ?? 0);
                                    $invoiceId = (int) ($domainInvoice[$domainId] ?? 0);
                                    $actualPayMethodId = $invoiceId > 0 && isset($invoiceRows[$invoiceId])
                                        ? (int) ($invoiceRows[$invoiceId]->paymethodid ?? 0)
                                        : 0;
                                    $matched = $invoiceId > 0 && $actualPayMethodId === $expectedPayMethodId;
                                    if (!$matched) {
                                        $allVerified = false;
                                    }
                                    if ($invoiceId > 0) {
                                        $groupInvoiceMap[$expectedPayMethodId][$invoiceId] = true;
                                    }
                                    $domainName = trim((string) ($domainMap[$domainId]->domain ?? ''));
                                    $verification[] = 'Domain #' . $domainId
                                        . ($domainName !== '' ? ' (' . $domainName . ')' : '')
                                        . ' → Invoice #' . ($invoiceId > 0 ? $invoiceId : 0)
                                        . ' → ' . ($labels[$actualPayMethodId] ?? ('Pay Method #' . $actualPayMethodId))
                                        . ($matched ? ' [matched]' : ' [EXPECTED Pay Method #' . $expectedPayMethodId . ']');
                                }

                                $groupVerification = [];
                                foreach ($groups as $payMethodId => $groupDomainIds) {
                                    $invoiceIdsForGroup = array_keys($groupInvoiceMap[(int) $payMethodId] ?? []);
                                    $oneGroupInvoice = count($invoiceIdsForGroup) === 1;
                                    if (!$oneGroupInvoice) {
                                        $allVerified = false;
                                    }
                                    $groupVerification[] = 'PM#' . (int) $payMethodId
                                        . ' domains ' . implode(',', $groupDomainIds)
                                        . ' → invoice' . (count($invoiceIdsForGroup) === 1 ? ' #' . $invoiceIdsForGroup[0] : 's ' . ($invoiceIdsForGroup ? implode(',', $invoiceIdsForGroup) : 'none'))
                                        . ($oneGroupInvoice ? ' [grouped]' : ' [NOT grouped as one invoice]');
                                }

                                $idsText = $newInvoiceIds ? ' Invoice IDs: ' . implode(', ', $newInvoiceIds) . '.' : '';
                                if ($allVerified) {
                                    $notice = ['success', 'Routed domain generation created ' . count($newInvoiceIds) . ' invoices for ' . count($domainIds) . ' same-date domains across ' . $expectedInvoiceCount . ' Payment Method groups. Domains sharing a Payment Method stayed together and every invoice received the correct assigned Payment Method.' . $idsText . ' ' . implode(' | ', $verification) . ' Grouping: ' . implode(' | ', $groupVerification) . ' Invoice-created emails were suppressed.'];
                                } elseif (!$newInvoiceIds) {
                                    $notice = ['info', 'Routed domain generation created 0 new invoices. These domains may already have renewal invoices for this due date or may be outside the invoice-generation window. No invoice-created emails were sent.'];
                                } else {
                                    $notice = ['warning', 'Routed domain generation completed, but multi-item grouping did not fully match the intended one-invoice-per-Payment-Method result. Expected ' . $expectedInvoiceCount . ' invoice(s) from ' . count($domainIds) . ' selected domains; created ' . count($newInvoiceIds) . '.' . $idsText . ' ' . implode(' | ', $verification) . ' Grouping: ' . implode(' | ', $groupVerification) . ' Invoice-created emails were suppressed. Do not automate this path yet.'];
                                }

                                if (function_exists('logActivity')) {
                                    $groupLog = [];
                                    foreach ($groups as $payMethodId => $groupDomainIds) {
                                        $result = $groupResults[(int) $payMethodId] ?? [];
                                        $groupLog[] = 'PM#' . (int) $payMethodId . ' domains=' . implode(',', $groupDomainIds) . ' numcreated=' . (int) ($result['numcreated'] ?? 0);
                                    }
                                    logActivity('DomainMonger Payment Routing [TEST ROUTED DOMAIN GENINVOICES] Client #' . $clientId
                                        . '; same due date ' . $dueDate
                                        . '; ' . implode('; ', $groupLog)
                                        . ($newInvoiceIds ? '; invoice IDs ' . implode(',', $newInvoiceIds) : '')
                                        . '; verification ' . implode(' | ', $verification)
                                        . '; noemails=true.');
                                }
                            }
                        }
                    }
                } catch (Throwable $e) {
                    $notice = ['danger', 'The routed domain-generation test could not be prepared safely: ' . $e->getMessage() . ' Nothing further was intentionally generated after this error.'];
                }
            }
        }
    }


    // Patch 1744 mixed diagnostic: products and domains are selected together,
    // grouped by explicit Pay Method, then each PM group is passed to native
    // GenInvoices with serviceids[] and/or domainids[] in the same call.
    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && (string) ($_POST['dm1744_action'] ?? '') === 'gen_selected_mixed_routed') {
        $submittedToken = (string) ($_POST['dm1744_token'] ?? '');
        $sessionToken = domainmongerpayrouting_diag_token();
        unset($_SESSION['dm1741_routed_geninvoices_token']);

        if (!$isTestLive) {
            $notice = ['danger', 'Test Mixed Routed Generate Invoices is available only while Routing Mode is Test Live.'];
        } elseif (count($testClientIds) !== 1) {
            $notice = ['danger', 'This diagnostic requires exactly one Test Client ID so it cannot target the wrong account.'];
        } elseif ($submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            $notice = ['danger', 'The mixed routed-generation test form expired or was already submitted. Nothing was generated.'];
        } else {
            $clientId = (int) $testClientIds[0];
            $selected = $_POST['dm1744_items'] ?? [];
            if (!is_array($selected)) {
                $selected = [$selected];
            }

            $serviceIds = [];
            $domainIds = [];
            foreach ($selected as $key) {
                $key = trim((string) $key);
                if (preg_match('/^service:(\\d+)$/', $key, $m)) {
                    $id = (int) $m[1];
                    if ($id > 0) {
                        $serviceIds[$id] = true;
                    }
                } elseif (preg_match('/^domain:(\\d+)$/', $key, $m)) {
                    $id = (int) $m[1];
                    if ($id > 0) {
                        $domainIds[$id] = true;
                    }
                }
            }
            $serviceIds = array_keys($serviceIds);
            $domainIds = array_keys($domainIds);
            $selectedCount = count($serviceIds) + count($domainIds);

            if ($selectedCount < 2) {
                $notice = ['warning', 'Select at least two items for this mixed routed grouped-invoice test. Nothing was generated.'];
            } elseif (!$serviceIds || !$domainIds) {
                $notice = ['warning', 'For the mixed test, select at least one product and at least one domain. Nothing was generated.'];
            } else {
                try {
                    $serviceMap = [];
                    if ($serviceIds) {
                        $services = Capsule::table('tblhosting')
                            ->where('userid', $clientId)
                            ->whereIn('id', $serviceIds)
                            ->get(['id', 'domainstatus', 'nextduedate']);
                        foreach ($services ?: [] as $service) {
                            $serviceMap[(int) ($service->id ?? 0)] = $service;
                        }
                    }

                    $domainMap = [];
                    if ($domainIds) {
                        $domains = Capsule::table('tbldomains')
                            ->where('userid', $clientId)
                            ->whereIn('id', $domainIds)
                            ->get(['id', 'domain', 'status', 'nextduedate']);
                        foreach ($domains ?: [] as $domain) {
                            $domainMap[(int) ($domain->id ?? 0)] = $domain;
                        }
                    }

                    $serviceAssignments = [];
                    if ($serviceIds) {
                        $assignments = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                            ->where('userid', $clientId)
                            ->where('item_type', 'service')
                            ->whereIn('item_id', $serviceIds)
                            ->get(['item_id', 'pay_method_id']);
                        foreach ($assignments ?: [] as $assignment) {
                            $serviceAssignments[(int) ($assignment->item_id ?? 0)] = (int) ($assignment->pay_method_id ?? 0);
                        }
                        foreach ($serviceIds as $serviceId) {
                            if (($serviceAssignments[$serviceId] ?? 0) <= 0 && function_exists('dm1723_get_assignment')) {
                                $fallbackId = (int) dm1723_get_assignment($clientId, 'service', $serviceId);
                                if ($fallbackId > 0) {
                                    $serviceAssignments[$serviceId] = $fallbackId;
                                }
                            }
                        }
                    }

                    $domainAssignments = [];
                    if ($domainIds) {
                        $assignments = Capsule::table('mod_domainmonger_item_paymethod_assignments')
                            ->where('userid', $clientId)
                            ->where('item_type', 'domain')
                            ->whereIn('item_id', $domainIds)
                            ->get(['item_id', 'pay_method_id']);
                        foreach ($assignments ?: [] as $assignment) {
                            $domainAssignments[(int) ($assignment->item_id ?? 0)] = (int) ($assignment->pay_method_id ?? 0);
                        }
                        foreach ($domainIds as $domainId) {
                            if (($domainAssignments[$domainId] ?? 0) <= 0 && function_exists('dm1723_get_assignment')) {
                                $fallbackId = (int) dm1723_get_assignment($clientId, 'domain', $domainId);
                                if ($fallbackId > 0) {
                                    $domainAssignments[$domainId] = $fallbackId;
                                }
                            }
                        }
                    }

                    $client = Capsule::table('tblclients')->where('id', $clientId)->first(['separateinvoices']);
                    $separateInvoices = $client ? (bool) ($client->separateinvoices ?? false) : false;

                    if (count($serviceMap) !== count($serviceIds) || count($domainMap) !== count($domainIds)) {
                        $notice = ['danger', 'One or more selected products/domains do not belong to the configured Test Live client. Nothing was generated.'];
                    } elseif (!$separateInvoices) {
                        $notice = ['warning', 'WHMCS Separate Invoices is OFF for this client. Enable it before this test. Nothing was generated.'];
                    } elseif (count($serviceAssignments) !== count($serviceIds) || ($serviceAssignments && min($serviceAssignments) <= 0)
                        || count($domainAssignments) !== count($domainIds) || ($domainAssignments && min($domainAssignments) <= 0)) {
                        $notice = ['warning', 'Every selected product and domain must have an explicit saved Payment Method assignment. Nothing was generated.'];
                    } else {
                        $dueDates = [];
                        foreach ($serviceIds as $serviceId) {
                            $dueDates[] = (string) ($serviceMap[$serviceId]->nextduedate ?? '');
                        }
                        foreach ($domainIds as $domainId) {
                            $dueDates[] = (string) ($domainMap[$domainId]->nextduedate ?? '');
                        }
                        $uniqueDueDates = array_values(array_unique($dueDates));
                        $dueDate = $uniqueDueDates[0] ?? '';

                        $groups = [];
                        foreach ($serviceIds as $serviceId) {
                            $pm = (int) ($serviceAssignments[$serviceId] ?? 0);
                            $groups[$pm]['service'][] = (int) $serviceId;
                        }
                        foreach ($domainIds as $domainId) {
                            $pm = (int) ($domainAssignments[$domainId] ?? 0);
                            $groups[$pm]['domain'][] = (int) $domainId;
                        }

                        if ($dueDate === '' || $dueDate === '0000-00-00' || count($uniqueDueDates) !== 1) {
                            $notice = ['warning', 'For this test, all selected products and domains must have the SAME Next Due Date. Nothing was generated.'];
                        } elseif (count($groups) < 2) {
                            $notice = ['warning', 'For this test, the selection must include at least two DIFFERENT Payment Methods. Nothing was generated.'];
                        } elseif (!function_exists('localAPI')) {
                            $notice = ['danger', 'WHMCS Local API is unavailable. Nothing was generated.'];
                        } else {
                            $beforeInvoiceId = (int) (Capsule::table('tblinvoices')->where('userid', $clientId)->max('id') ?: 0);
                            $groupResults = [];
                            $generationError = '';

                            foreach ($groups as $payMethodId => $group) {
                                $args = [
                                    'clientid' => $clientId,
                                    'noemails' => true,
                                ];
                                $groupServiceIds = array_values(array_map('intval', $group['service'] ?? []));
                                $groupDomainIds = array_values(array_map('intval', $group['domain'] ?? []));
                                if ($groupServiceIds) {
                                    $args['serviceids'] = $groupServiceIds;
                                }
                                if ($groupDomainIds) {
                                    $args['domainids'] = $groupDomainIds;
                                }

                                try {
                                    $result = localAPI('GenInvoices', $args);
                                } catch (Throwable $e) {
                                    $result = ['result' => 'error', 'message' => $e->getMessage()];
                                }
                                $groupResults[(int) $payMethodId] = $result;
                                if (!is_array($result) || strtolower((string) ($result['result'] ?? '')) !== 'success') {
                                    $generationError = is_array($result)
                                        ? trim((string) ($result['message'] ?? $result['error'] ?? 'Unknown WHMCS GenInvoices error.'))
                                        : 'WHMCS returned an invalid GenInvoices response.';
                                    break;
                                }
                            }

                            $newInvoiceIds = domainmongerpayrouting_new_invoice_ids($clientId, $beforeInvoiceId);
                            $labels = domainmongerpayrouting_paymethod_labels($clientId);

                            if ($generationError !== '') {
                                $idsText = $newInvoiceIds ? ' Invoice IDs created before the error: ' . implode(', ', $newInvoiceIds) . '.' : '';
                                $notice = ['danger', 'Test Mixed Routed Generate Invoices stopped because WHMCS returned an error: ' . $generationError . $idsText . ' Invoice-created emails were suppressed. Review any listed invoice before retrying.'];
                            } else {
                                $invoiceRows = [];
                                if ($newInvoiceIds) {
                                    $rows = Capsule::table('tblinvoices')
                                        ->whereIn('id', $newInvoiceIds)
                                        ->get(['id', 'paymethodid', 'paymentmethod', 'status']);
                                    foreach ($rows ?: [] as $row) {
                                        $invoiceRows[(int) ($row->id ?? 0)] = $row;
                                    }
                                }

                                $serviceInvoice = [];
                                $domainInvoice = [];
                                if ($newInvoiceIds) {
                                    $relIds = array_values(array_unique(array_merge($serviceIds, $domainIds)));
                                    $items = Capsule::table('tblinvoiceitems')
                                        ->whereIn('invoiceid', $newInvoiceIds)
                                        ->whereIn('relid', $relIds)
                                        ->get(['invoiceid', 'type', 'relid']);
                                    foreach ($items ?: [] as $item) {
                                        $type = strtolower(trim((string) ($item->type ?? '')));
                                        $relId = (int) ($item->relid ?? 0);
                                        $invoiceId = (int) ($item->invoiceid ?? 0);
                                        if ($relId <= 0 || $invoiceId <= 0) {
                                            continue;
                                        }
                                        if (isset($serviceMap[$relId]) && ($type === 'hosting' || $type === 'service' || strpos($type, 'hosting') !== false)) {
                                            $serviceInvoice[$relId] = $invoiceId;
                                        }
                                        if (isset($domainMap[$relId]) && strpos($type, 'domain') !== false) {
                                            $domainInvoice[$relId] = $invoiceId;
                                        }
                                    }
                                }

                                $verification = [];
                                $expectedInvoiceCount = count($groups);
                                $allVerified = count($newInvoiceIds) === $expectedInvoiceCount;
                                $groupInvoiceMap = [];

                                foreach ($serviceIds as $serviceId) {
                                    $expectedPayMethodId = (int) ($serviceAssignments[$serviceId] ?? 0);
                                    $invoiceId = (int) ($serviceInvoice[$serviceId] ?? 0);
                                    $actualPayMethodId = $invoiceId > 0 && isset($invoiceRows[$invoiceId])
                                        ? (int) ($invoiceRows[$invoiceId]->paymethodid ?? 0) : 0;
                                    $matched = $invoiceId > 0 && $actualPayMethodId === $expectedPayMethodId;
                                    if (!$matched) {
                                        $allVerified = false;
                                    }
                                    if ($invoiceId > 0) {
                                        $groupInvoiceMap[$expectedPayMethodId][$invoiceId] = true;
                                    }
                                    $verification[] = 'Service #' . $serviceId
                                        . ' → Invoice #' . ($invoiceId > 0 ? $invoiceId : 0)
                                        . ' → ' . ($labels[$actualPayMethodId] ?? ('Pay Method #' . $actualPayMethodId))
                                        . ($matched ? ' [matched]' : ' [EXPECTED Pay Method #' . $expectedPayMethodId . ']');
                                }

                                foreach ($domainIds as $domainId) {
                                    $expectedPayMethodId = (int) ($domainAssignments[$domainId] ?? 0);
                                    $invoiceId = (int) ($domainInvoice[$domainId] ?? 0);
                                    $actualPayMethodId = $invoiceId > 0 && isset($invoiceRows[$invoiceId])
                                        ? (int) ($invoiceRows[$invoiceId]->paymethodid ?? 0) : 0;
                                    $matched = $invoiceId > 0 && $actualPayMethodId === $expectedPayMethodId;
                                    if (!$matched) {
                                        $allVerified = false;
                                    }
                                    if ($invoiceId > 0) {
                                        $groupInvoiceMap[$expectedPayMethodId][$invoiceId] = true;
                                    }
                                    $domainName = trim((string) ($domainMap[$domainId]->domain ?? ''));
                                    $verification[] = 'Domain #' . $domainId
                                        . ($domainName !== '' ? ' (' . $domainName . ')' : '')
                                        . ' → Invoice #' . ($invoiceId > 0 ? $invoiceId : 0)
                                        . ' → ' . ($labels[$actualPayMethodId] ?? ('Pay Method #' . $actualPayMethodId))
                                        . ($matched ? ' [matched]' : ' [EXPECTED Pay Method #' . $expectedPayMethodId . ']');
                                }

                                $groupVerification = [];
                                foreach ($groups as $payMethodId => $group) {
                                    $invoiceIdsForGroup = array_keys($groupInvoiceMap[(int) $payMethodId] ?? []);
                                    $oneGroupInvoice = count($invoiceIdsForGroup) === 1;
                                    if (!$oneGroupInvoice) {
                                        $allVerified = false;
                                    }
                                    $parts = [];
                                    if (!empty($group['service'])) {
                                        $parts[] = 'services ' . implode(',', array_map('intval', $group['service']));
                                    }
                                    if (!empty($group['domain'])) {
                                        $parts[] = 'domains ' . implode(',', array_map('intval', $group['domain']));
                                    }
                                    $groupVerification[] = 'PM#' . (int) $payMethodId
                                        . ' ' . implode(' + ', $parts)
                                        . ' → invoice' . (count($invoiceIdsForGroup) === 1 ? ' #' . $invoiceIdsForGroup[0] : 's ' . ($invoiceIdsForGroup ? implode(',', $invoiceIdsForGroup) : 'none'))
                                        . ($oneGroupInvoice ? ' [grouped]' : ' [NOT grouped as one invoice]');
                                }

                                $idsText = $newInvoiceIds ? ' Invoice IDs: ' . implode(', ', $newInvoiceIds) . '.' : '';
                                if ($allVerified) {
                                    $notice = ['success', 'Mixed routed generation created ' . count($newInvoiceIds) . ' invoices for ' . $selectedCount . ' same-date products/domains across ' . $expectedInvoiceCount . ' Payment Method groups. Products and domains sharing a Payment Method stayed together and every invoice received the correct assigned Payment Method.' . $idsText . ' ' . implode(' | ', $verification) . ' Grouping: ' . implode(' | ', $groupVerification) . ' Invoice-created emails were suppressed.'];
                                } elseif (!$newInvoiceIds) {
                                    $notice = ['info', 'Mixed routed generation created 0 new invoices. These items may already have renewal invoices for this due date or may be outside the invoice-generation window. No invoice-created emails were sent.'];
                                } else {
                                    $notice = ['warning', 'Mixed routed generation completed, but grouping did not fully match the intended one-invoice-per-Payment-Method result. Expected ' . $expectedInvoiceCount . ' invoice(s) from ' . $selectedCount . ' selected items; created ' . count($newInvoiceIds) . '.' . $idsText . ' ' . implode(' | ', $verification) . ' Grouping: ' . implode(' | ', $groupVerification) . ' Invoice-created emails were suppressed. Do not automate this path yet.'];
                                }

                                if (function_exists('logActivity')) {
                                    $groupLog = [];
                                    foreach ($groups as $payMethodId => $group) {
                                        $result = $groupResults[(int) $payMethodId] ?? [];
                                        $parts = [];
                                        if (!empty($group['service'])) {
                                            $parts[] = 'services=' . implode(',', array_map('intval', $group['service']));
                                        }
                                        if (!empty($group['domain'])) {
                                            $parts[] = 'domains=' . implode(',', array_map('intval', $group['domain']));
                                        }
                                        $groupLog[] = 'PM#' . (int) $payMethodId . ' ' . implode(' ', $parts) . ' numcreated=' . (int) ($result['numcreated'] ?? 0);
                                    }
                                    logActivity('DomainMonger Payment Routing [TEST MIXED ROUTED GENINVOICES] Client #' . $clientId
                                        . '; same due date ' . $dueDate
                                        . '; ' . implode('; ', $groupLog)
                                        . ($newInvoiceIds ? '; invoice IDs ' . implode(',', $newInvoiceIds) : '')
                                        . '; verification ' . implode(' | ', $verification)
                                        . '; noemails=true.');
                                }
                            }
                        }
                    }
                } catch (Throwable $e) {
                    $notice = ['danger', 'The mixed routed-generation test could not be prepared safely: ' . $e->getMessage() . ' Nothing further was intentionally generated after this error.'];
                }
            }
        }
    }

    $showTestClientIds = stripos($modeRaw, 'Shadow') !== false || stripos($modeRaw, 'Test Live') !== false;
    echo '<div class="alert alert-info"><strong>DomainMonger Payment Routing</strong><br>';
    echo 'Current mode: <strong>' . $mode . '</strong>';
    if ($showTestClientIds) {
        echo '<br>Test Client IDs: <strong>' . ($testClients !== '' ? $testClients : 'None') . '</strong>';
    }
    echo '</div>';

    if ($notice) {
        echo '<div class="alert alert-' . domainmongerpayrouting_escape($notice[0]) . '">' . domainmongerpayrouting_escape($notice[1]) . '</div>';
    }

    // Patch 1757: render only the active workflow tab. Tab clicks already
    // navigate/reload, so carrying the other two large tables in the DOM only
    // increases render/layout cost and contributes to sluggish UI feedback.
    $dm1751ActiveTab = domainmongerpayrouting_admin_active_tab();
    echo domainmongerpayrouting_admin_tabs_nav($dm1751ActiveTab);

    if ($dm1751ActiveTab === 'account') {
        echo '<div id="dm1751-tab-account" class="dm1751-tab-pane dm1751-active">';
        echo domainmongerpayrouting_account_routing_section($vars);
        echo '</div></div>';
        return;
    }

    if ($dm1751ActiveTab === 'health') {
        echo '<div id="dm1751-tab-health" class="dm1751-tab-pane dm1751-active">';
        echo domainmongerpayrouting_health_report_html();
        echo '</div></div>';
        return;
    }

    // Payment Assignments: process and render only this tab's bulk manager and
    // (when applicable) the Test Live mixed-generation diagnostic.
    echo '<div id="dm1751-tab-assignments" class="dm1751-tab-pane dm1751-active">';
    echo domainmongerpayrouting_bulk_admin_section($vars);

    if (!$isTestLive) {
        echo '</div></div>';
        return;
    }

    if (count($testClientIds) !== 1) {
        echo '<div class="alert alert-warning"><strong>Test Routed Generate Invoices:</strong> Configure exactly one Test Client ID to use this diagnostic safely.</div>';
        echo '</div></div>';
        return;
    }

    $clientId = (int) $testClientIds[0];
    $clientName = 'Client #' . $clientId;
    try {
        $client = Capsule::table('tblclients')->where('id', $clientId)->first(['firstname', 'lastname', 'companyname']);
        if ($client) {
            $person = trim((string) ($client->firstname ?? '') . ' ' . (string) ($client->lastname ?? ''));
            $company = trim((string) ($client->companyname ?? ''));
            if ($company !== '') {
                $clientName .= ' — ' . $company . ($person !== '' ? ' (' . $person . ')' : '');
            } elseif ($person !== '') {
                $clientName .= ' — ' . $person;
            }
        }
    } catch (Throwable $e) {
        // The numeric client ID remains sufficient.
    }

    $rows = domainmongerpayrouting_service_rows($clientId);
    $domainRows = domainmongerpayrouting_domain_rows($clientId);
    $labels = domainmongerpayrouting_paymethod_labels($clientId);
    $token = domainmongerpayrouting_diag_token();
    $initialPagerStatus = domainmongerpayrouting_initial_pager_status(count($rows) + count($domainRows));

    echo '<div class="panel panel-default dm1741-geninvoices-panel">';
    echo '<div class="panel-heading"><strong>Test Mixed Product + Domain Payment Method Grouping</strong></div>';
    echo '<div class="panel-body">';
    echo '<p><strong>Test Live diagnostic for ' . domainmongerpayrouting_escape($clientName) . '.</strong> Select <strong>two or more items</strong> from the combined Products and Domains list. The selection must include at least <strong>one product</strong>, <strong>one domain</strong>, the <strong>same Next Due Date</strong>, and at least <strong>two different assigned Payment Methods</strong>. Items sharing the same Payment Method are intentionally grouped together, even when one is a product and the other is a domain.</p>';
    echo '<p class="text-muted">This creates real renewal invoice records when the selected items are due for invoice generation. It does not capture payment. For each Payment Method group, the test calls native WHMCS <code>GenInvoices</code> once and passes that group\'s <code>serviceids[]</code> and/or <code>domainids[]</code> together with <code>noemails=true</code>.</p>';

    $serviceLookupError = domainmongerpayrouting_service_rows_error();
    $domainLookupError = domainmongerpayrouting_domain_rows_error();
    if ($serviceLookupError !== '' || $domainLookupError !== '') {
        echo '<div class="alert alert-danger">One of the Test Live item lists could not be read. The technical error was written to the WHMCS Activity Log. Nothing was generated.</div>';
    }

    if (!$rows && !$domainRows) {
        echo '<div class="alert alert-warning" style="margin-bottom:0;">No products/services or domains were found for this Test Live client.</div>';
        echo '</div></div>';
    } else {
        echo '<form method="post" id="dm1744-mixed-geninvoices-form" data-dm1757-pager-root="diagnostic">';
        echo '<input type="hidden" name="dm1744_action" value="gen_selected_mixed_routed">';
        echo '<input type="hidden" name="dm1744_token" value="' . domainmongerpayrouting_escape($token) . '">';
        echo '<div class="dm1758-primary-toolbar"><div class="dm1757-table-search"><span class="fas fa-search" aria-hidden="true"></span><input type="search" class="form-control" data-dm1757-search placeholder="Search diagnostic items" aria-label="Search Test Live diagnostic items" autocomplete="off"></div></div>';
        echo '<div class="dm1758-secondary-toolbar"><div class="dm1757-table-toolbar-left"><select class="form-control dm1757-page-size" data-dm1757-page-size aria-label="Rows per page"><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option><option value="all">All</option></select></div><div class="dm1757-pager"><span class="dm1757-pager-status" data-dm1757-status>' . domainmongerpayrouting_escape($initialPagerStatus) . '</span><button type="button" class="btn btn-default" data-dm1757-prev>Previous</button><button type="button" class="btn btn-default" data-dm1757-next>Next</button></div></div>';
        echo '<div class="table-responsive"><table class="table table-striped table-bordered" style="margin-bottom:12px;">';
        echo '<thead><tr><th style="width:56px;text-align:center;">Select</th><th style="width:90px;">Type</th><th>Item</th><th>Status</th><th>Next Due Date</th><th>Assigned Payment Method</th></tr></thead><tbody>';

        foreach ($rows as $row) {
            $serviceId = (int) ($row->id ?? 0);
            $productName = trim((string) ($row->product_name ?? ''));
            $domain = trim((string) ($row->domain ?? ''));
            $itemName = $productName !== '' ? $productName : 'Product';
            if ($domain !== '') {
                $itemName .= ' — ' . $domain;
            }
            $payMethodId = (int) ($row->pay_method_id ?? 0);
            $hasExplicitAssignment = $payMethodId > 0;
            $payMethodLabel = $hasExplicitAssignment ? ($labels[$payMethodId] ?? ('Pay Method #' . $payMethodId)) : 'Account Default / No explicit assignment';
            $status = trim((string) ($row->domainstatus ?? ''));
            $disabled = $hasExplicitAssignment ? '' : ' disabled title="Assign a specific Payment Method to this product before selecting it for this test."';
            $rowClass = $hasExplicitAssignment ? '' : ' class="text-muted"';

            echo '<tr data-dm1757-row' . ($rowClass !== '' ? ' class="text-muted"' : '') . '>';
            echo '<td style="text-align:center;vertical-align:middle;"><input type="checkbox" name="dm1744_items[]" value="service:' . $serviceId . '" class="dm1744-item-check" data-type="service"' . $disabled . '></td>';
            echo '<td style="vertical-align:middle;"><strong>Product</strong></td>';
            echo '<td style="vertical-align:middle;">#' . $serviceId . ' — ' . domainmongerpayrouting_escape($itemName) . '</td>';
            echo '<td style="vertical-align:middle;">' . domainmongerpayrouting_escape($status) . '</td>';
            echo '<td style="vertical-align:middle;">' . domainmongerpayrouting_escape((string) ($row->nextduedate ?? '')) . '</td>';
            echo '<td style="vertical-align:middle;">' . domainmongerpayrouting_escape($payMethodLabel) . '</td>';
            echo '</tr>';
        }

        foreach ($domainRows as $row) {
            $domainId = (int) ($row->id ?? 0);
            $domainName = trim((string) ($row->domain ?? ''));
            $payMethodId = (int) ($row->pay_method_id ?? 0);
            $hasExplicitAssignment = $payMethodId > 0;
            $payMethodLabel = $hasExplicitAssignment ? ($labels[$payMethodId] ?? ('Pay Method #' . $payMethodId)) : 'Account Default / No explicit assignment';
            $status = trim((string) ($row->status ?? ''));
            $disabled = $hasExplicitAssignment ? '' : ' disabled title="Assign a specific Payment Method to this domain before selecting it for this test."';
            $rowClass = $hasExplicitAssignment ? '' : ' class="text-muted"';

            echo '<tr data-dm1757-row' . ($rowClass !== '' ? ' class="text-muted"' : '') . '>';
            echo '<td style="text-align:center;vertical-align:middle;"><input type="checkbox" name="dm1744_items[]" value="domain:' . $domainId . '" class="dm1744-item-check" data-type="domain"' . $disabled . '></td>';
            echo '<td style="vertical-align:middle;"><strong>Domain</strong></td>';
            echo '<td style="vertical-align:middle;">#' . $domainId . ' — ' . domainmongerpayrouting_escape($domainName !== '' ? $domainName : 'Domain') . '</td>';
            echo '<td style="vertical-align:middle;">' . domainmongerpayrouting_escape($status) . '</td>';
            echo '<td style="vertical-align:middle;">' . domainmongerpayrouting_escape((string) ($row->nextduedate ?? '')) . '</td>';
            echo '<td style="vertical-align:middle;">' . domainmongerpayrouting_escape($payMethodLabel) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
        echo '<div class="alert alert-warning dm1757-no-match" data-dm1757-no-match style="display:none;">No diagnostic items match this search.</div><div class="dm1757-pager"><span class="dm1757-pager-status" data-dm1757-status>' . domainmongerpayrouting_escape($initialPagerStatus) . '</span><button type="button" class="btn btn-default" data-dm1757-prev>Previous</button><button type="button" class="btn btn-default" data-dm1757-next>Next</button></div>';
        echo '<button type="submit" class="btn btn-primary" id="dm1744-mixed-geninvoices-button" disabled style="margin-top:10px;">Test Mixed Routed Generate Invoices</button>';
        echo '</form></div></div>';
    }

    echo <<<'HTML'
<style>
.dm1741-geninvoices-panel{margin-top:15px;border-color:#d7dce2}
.dm1741-geninvoices-panel>.panel-heading{background:#163a5f!important;color:#fff!important;border-color:#163a5f!important}
.dm1741-geninvoices-panel .btn-primary{background:#f58220;border-color:#f58220;color:#fff}
.dm1741-geninvoices-panel .btn-primary:hover,.dm1741-geninvoices-panel .btn-primary:focus{background:#214e7a;border-color:#214e7a;color:#fff}
.dm1741-geninvoices-panel .btn[disabled]{opacity:.55;cursor:not-allowed}
</style>
<script>
(function(){
    var form=document.getElementById('dm1744-mixed-geninvoices-form');
    var button=document.getElementById('dm1744-mixed-geninvoices-button');
    if(!form||!button){return;}
    var boxes=Array.prototype.slice.call(form.querySelectorAll('.dm1744-item-check'));
    var pager=window.dm1757InitPager?window.dm1757InitPager(form):null;
    function sync(){
        var selected=boxes.filter(function(box){return box.checked;});
        var hasService=selected.some(function(box){return box.getAttribute('data-type')==='service';});
        var hasDomain=selected.some(function(box){return box.getAttribute('data-type')==='domain';});
        button.disabled=selected.length<2||!hasService||!hasDomain;
    }
    boxes.forEach(function(box){box.addEventListener('change',sync);});
    form.addEventListener('submit',function(ev){
        if(button.disabled){ev.preventDefault();return;}
        var ok=window.confirm('Generate routed renewal invoices now for the selected Test Live products and domains? Items will be grouped by assigned Payment Method, and each PM group may include both serviceids and domainids. Invoice-created emails will be suppressed.');
        if(!ok){ev.preventDefault();return;}
        button.disabled=true;
        button.textContent='Generating...';
    });
    sync();
}());
</script>
HTML;

    echo '</div></div>';
}
/**
 * Patch 1748: account-level bulk Payment Routing enable/disable.
 *
 * The existing one-table design is preserved. A single marker row with
 * item_type=account, item_id=Client ID, pay_method_id=0 pauses routing for that
 * account. Product/domain assignments are never deleted by this control.
 */
function domainmongerpayrouting_account_token(): string
{
    $key = 'dm1748_account_routing_token';
    if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
        try {
            $_SESSION[$key] = bin2hex(random_bytes(24));
        } catch (Throwable $e) {
            $_SESSION[$key] = hash('sha256', session_id() . '|' . microtime(true) . '|dm1748-account-routing');
        }
    }
    return (string) $_SESSION[$key];
}

function domainmongerpayrouting_account_disabled(int $clientId): bool
{
    if ($clientId <= 0) {
        return false;
    }
    if (function_exists('dm1723_account_routing_disabled')) {
        return (bool) dm1723_account_routing_disabled($clientId);
    }
    try {
        return Capsule::table('mod_domainmonger_item_paymethod_assignments')
            ->where('userid', $clientId)
            ->where('item_type', 'account')
            ->where('item_id', $clientId)
            ->where('pay_method_id', 0)
            ->exists();
    } catch (Throwable $e) {
        return false;
    }
}

function domainmongerpayrouting_account_has_assigned_invoice_item(int $invoiceId, int $clientId): bool
{
    if ($invoiceId <= 0 || $clientId <= 0 || !function_exists('dm1723_invoice_item_assignment')) {
        return false;
    }
    try {
        $items = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $invoiceId)
            ->get(['type', 'relid', 'amount']);
        foreach ($items ?: [] as $item) {
            [$routable, $payMethodId] = dm1723_invoice_item_assignment($clientId, $item);
            if ($routable && (int) $payMethodId > 0) {
                return true;
            }
        }
    } catch (Throwable $e) {
        return false;
    }
    return false;
}

function domainmongerpayrouting_account_reconcile_disabled(int $clientId): array
{
    $changed = 0;
    $skippedManual = 0;
    $errors = [];
    if ($clientId <= 0 || !function_exists('dm1732_set_invoice_default')) {
        return [$changed, $skippedManual, ['Invoice reconciliation helper is unavailable.']];
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
            if ($invoiceId <= 0 || !domainmongerpayrouting_account_has_assigned_invoice_item($invoiceId, $clientId)) {
                continue;
            }

            // Manual invoice-only selections are intentionally not routing state.
            // Pausing account routing must not overwrite an Admin's manual choice.
            if (function_exists('dm1732_invoice_manual_marker')
                && dm1732_invoice_manual_marker($invoiceId, $clientId) !== null) {
                $skippedManual++;
                continue;
            }

            [$ok, $message] = dm1732_set_invoice_default($invoiceId, $clientId);
            if ($ok) {
                $changed++;
                if (function_exists('dm1732_clear_invoice_manual_marker')) {
                    dm1732_clear_invoice_manual_marker($invoiceId);
                }
            } else {
                $errors[] = 'Invoice #' . $invoiceId . ': ' . $message;
            }
        }
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }

    return [$changed, $skippedManual, $errors];
}

function domainmongerpayrouting_account_reconcile_enabled(int $clientId): array
{
    $changed = 0;
    $errors = [];
    if ($clientId <= 0 || !function_exists('dm1732_reconcile_unpaid_invoice')) {
        return [$changed, ['Invoice reconciliation helper is unavailable.']];
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
            if ($invoiceId <= 0 || !domainmongerpayrouting_account_has_assigned_invoice_item($invoiceId, $clientId)) {
                continue;
            }
            [$ok, $message] = dm1732_reconcile_unpaid_invoice($invoiceId, $clientId);
            if ($ok) {
                if (!in_array($message, ['not-unpaid', 'no-change'], true)) {
                    $changed++;
                }
            } else {
                $errors[] = 'Invoice #' . $invoiceId . ': ' . $message;
            }
        }
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
    return [$changed, $errors];
}

function domainmongerpayrouting_set_account_routing(int $clientId, bool $enabled): array
{
    if ($clientId <= 0 || !domainmongerpayrouting_load_routing_helpers()) {
        return [false, 'Client or routing helper could not be identified.'];
    }

    try {
        $client = Capsule::table('tblclients')->where('id', $clientId)->first(['id']);
        if (!$client) {
            return [false, 'Client #' . $clientId . ' was not found.'];
        }

        if ($enabled) {
            Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->where('userid', $clientId)
                ->where('item_type', 'account')
                ->where('item_id', $clientId)
                ->delete();

            $mode = function_exists('dm1723_client_mode') ? (string) dm1723_client_mode($clientId) : 'off';
            if ($mode === 'live' && function_exists('dm1723_enable_separate_invoices')) {
                if (!dm1723_enable_separate_invoices($clientId)) {
                    // Put the pause marker back so a failed enable cannot leave
                    // the account half-enabled with unmanaged invoice grouping.
                    $rollback = [
                        'userid' => $clientId,
                        'item_type' => 'account',
                        'item_id' => $clientId,
                        'pay_method_id' => 0,
                        'separate_invoices_original' => null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    Capsule::table('mod_domainmonger_item_paymethod_assignments')->insert($rollback);
                    return [false, 'Payment Routing remains disabled because WHMCS Separate Invoices could not be confirmed for Client #' . $clientId . '.'];
                }
            }

            $reconciled = 0;
            $errors = [];
            if ($mode === 'live') {
                [$reconciled, $errors] = domainmongerpayrouting_account_reconcile_enabled($clientId);
            }
            if (function_exists('dm1723_log')) {
                dm1723_log('[ADMIN BULK ACCOUNT] Client #' . $clientId . ' Payment Routing enabled. Saved product/domain assignments were retained; effective mode=' . $mode . '.');
            }
            $message = 'Enabled Payment Routing for Client #' . $clientId . '. Saved assignments were retained.';
            if ($mode === 'live') {
                $message .= ' Reconciled ' . $reconciled . ' Unpaid invoice' . ($reconciled === 1 ? '' : 's') . '.';
            } elseif ($mode === 'off') {
                $message .= ' The current module mode/allowlist still leaves this client inactive.';
            }
            if ($errors) {
                $message .= ' Invoice reconciliation warnings: ' . implode(' | ', array_slice($errors, 0, 2));
            }
            return [empty($errors), $message];
        }

        // Capture the effective mode before adding the pause marker. Only an
        // account that was actually live-routed should have existing Unpaid
        // invoices rewritten as part of the disable operation.
        $priorMode = function_exists('dm1723_client_mode') ? (string) dm1723_client_mode($clientId) : 'off';

        $existing = Capsule::table('mod_domainmonger_item_paymethod_assignments')
            ->where('userid', $clientId)
            ->where('item_type', 'account')
            ->where('item_id', $clientId)
            ->first(['id']);
        $data = [
            'userid' => $clientId,
            'item_type' => 'account',
            'item_id' => $clientId,
            'pay_method_id' => 0,
            'separate_invoices_original' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($existing) {
            Capsule::table('mod_domainmonger_item_paymethod_assignments')->where('id', (int) $existing->id)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            Capsule::table('mod_domainmonger_item_paymethod_assignments')->insert($data);
        }

        $restored = true;
        $reconciled = 0;
        $skippedManual = 0;
        $errors = [];
        if ($priorMode === 'live') {
            if (function_exists('dm1723_restore_separate_invoices')) {
                $restored = dm1723_restore_separate_invoices($clientId);
            }
            [$reconciled, $skippedManual, $errors] = domainmongerpayrouting_account_reconcile_disabled($clientId);
        }
        if (function_exists('dm1723_log')) {
            dm1723_log('[ADMIN BULK ACCOUNT] Client #' . $clientId . ' Payment Routing disabled. Saved product/domain assignments retained; assignment-driven Unpaid invoices returned to Account Default where safe.');
        }
        $message = 'Disabled Payment Routing for Client #' . $clientId . '. Saved assignments were retained.';
        if ($priorMode === 'live') {
            $message .= ' Returned ' . $reconciled . ' assignment-driven Unpaid invoice' . ($reconciled === 1 ? '' : 's') . ' to Account Default.';
        } else {
            $message .= ' This account was not live-routed, so existing invoices were not changed.';
        }
        if ($skippedManual > 0) {
            $message .= ' Preserved ' . $skippedManual . ' manual invoice Payment Method selection' . ($skippedManual === 1 ? '' : 's') . '.';
        }
        if (!$restored) {
            $errors[] = 'The prior WHMCS Separate Invoices setting could not be restored.';
        }
        if ($errors) {
            $message .= ' Warnings: ' . implode(' | ', array_slice($errors, 0, 2));
        }
        return [empty($errors), $message];
    } catch (Throwable $e) {
        return [false, 'Client #' . $clientId . ' routing status could not be changed: ' . $e->getMessage()];
    }
}

function domainmongerpayrouting_account_candidate_ids(array $vars, string $search): array
{
    $ids = [];
    $modeRaw = trim((string) ($vars['routing_mode'] ?? 'Off'));
    $modeLower = strtolower($modeRaw);
    $isLiveAll = $modeLower === 'live (all clients)';
    $isTestScoped = strpos($modeLower, 'test live') !== false || strpos($modeLower, 'shadow') !== false;

    try {
        if ($isTestScoped) {
            foreach (domainmongerpayrouting_test_client_ids((string) ($vars['test_client_ids'] ?? '')) as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[$id] = true;
                }
            }

            if ($search !== '' && $ids) {
                $query = Capsule::table('tblclients')->select('id')->whereIn('id', array_keys($ids));
                if (ctype_digit($search)) {
                    $query->where('id', (int) $search);
                } else {
                    $like = '%' . $search . '%';
                    $query->where(function ($q) use ($like) {
                        $q->where('firstname', 'like', $like)
                            ->orWhere('lastname', 'like', $like)
                            ->orWhere('companyname', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
                }
                $matched = [];
                foreach ($query->pluck('id')->all() ?: [] as $id) {
                    $id = (int) $id;
                    if ($id > 0) {
                        $matched[$id] = true;
                    }
                }
                $ids = $matched;
            }
        } elseif ($isLiveAll) {
            $query = Capsule::table('tblclients')->select('id');
            if ($search !== '') {
                if (ctype_digit($search)) {
                    $query->where('id', (int) $search);
                } else {
                    $like = '%' . $search . '%';
                    $query->where(function ($q) use ($like) {
                        $q->where('firstname', 'like', $like)
                            ->orWhere('lastname', 'like', $like)
                            ->orWhere('companyname', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
                }
            }
            foreach ($query->orderBy('id', 'asc')->pluck('id')->all() ?: [] as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[$id] = true;
                }
            }
        } elseif ($search !== '') {
            $query = Capsule::table('tblclients')->select('id');
            if (ctype_digit($search)) {
                $query->where('id', (int) $search);
            } else {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('firstname', 'like', $like)
                        ->orWhere('lastname', 'like', $like)
                        ->orWhere('companyname', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            }
            foreach ($query->orderBy('id', 'desc')->limit(100)->pluck('id')->all() ?: [] as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[$id] = true;
                }
            }
        } else {
            foreach (Capsule::table('mod_domainmonger_item_paymethod_assignments')
                ->whereIn('item_type', ['service', 'domain', 'account'])
                ->select('userid')->distinct()->pluck('userid')->all() ?: [] as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[$id] = true;
                }
            }
            foreach (domainmongerpayrouting_test_client_ids((string) ($vars['test_client_ids'] ?? '')) as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[$id] = true;
                }
            }
        }
    } catch (Throwable $e) {
        return [];
    }
    $ids = array_keys($ids);
    sort($ids, SORT_NUMERIC);
    return $ids;
}

function domainmongerpayrouting_account_rows(array $vars, string $search): array
{
    if (!domainmongerpayrouting_load_routing_helpers()) {
        return [];
    }
    $ids = domainmongerpayrouting_account_candidate_ids($vars, $search);
    if (!$ids) {
        return [];
    }

    $counts = [];
    try {
        $assignmentOwners = Capsule::table('mod_domainmonger_item_paymethod_assignments')
            ->whereIn('userid', $ids)
            ->whereIn('item_type', ['service', 'domain'])
            ->get(['userid']);
        foreach ($assignmentOwners ?: [] as $row) {
            $ownerId = (int) ($row->userid ?? 0);
            if ($ownerId > 0) {
                $counts[$ownerId] = (int) ($counts[$ownerId] ?? 0) + 1;
            }
        }
    } catch (Throwable $e) {
    }

    $clients = [];
    try {
        foreach (Capsule::table('tblclients')->whereIn('id', $ids)->orderBy('id', 'asc')->get(['id', 'firstname', 'lastname', 'companyname', 'email', 'status']) ?: [] as $client) {
            $id = (int) ($client->id ?? 0);
            if ($id <= 0) {
                continue;
            }
            $clients[] = [
                'id' => $id,
                'name' => trim((string) ($client->firstname ?? '') . ' ' . (string) ($client->lastname ?? '')),
                'company' => trim((string) ($client->companyname ?? '')),
                'email' => trim((string) ($client->email ?? '')),
                'status' => trim((string) ($client->status ?? '')),
                'assignments' => (int) ($counts[$id] ?? 0),
                'disabled' => domainmongerpayrouting_account_disabled($id),
                'effective_mode' => function_exists('dm1723_client_mode') ? (string) dm1723_client_mode($id) : 'off',
            ];
        }
    } catch (Throwable $e) {
        return [];
    }
    return $clients;
}

function domainmongerpayrouting_account_routing_section(array $vars): string
{
    if (!domainmongerpayrouting_load_routing_helpers()) {
        return '';
    }

    $notice = null;
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
        && (string) ($_POST['dm1748_account_action'] ?? '') === 'apply') {
        $submittedToken = (string) ($_POST['dm1748_account_token'] ?? '');
        $expectedToken = domainmongerpayrouting_account_token();
        $operation = strtolower(trim((string) ($_POST['dm1748_account_operation'] ?? '')));
        $selected = $_POST['dm1748_account_ids'] ?? [];
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        $clientIds = [];
        foreach ($selected as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $clientIds[$id] = true;
            }
        }
        $clientIds = array_keys($clientIds);

        if ($submittedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
            $notice = ['danger', 'The account routing form expired. Nothing was changed.'];
        } elseif (!in_array($operation, ['enable', 'disable'], true)) {
            $notice = ['warning', 'Choose Enable Payment Routing or Disable Payment Routing. Nothing was changed.'];
        } elseif (!$clientIds) {
            $notice = ['warning', 'Select at least one client account. Nothing was changed.'];
        } else {
            $success = 0;
            $failures = [];
            foreach ($clientIds as $clientId) {
                [$ok, $message] = domainmongerpayrouting_set_account_routing((int) $clientId, $operation === 'enable');
                if ($ok) {
                    $success++;
                } else {
                    $failures[] = $message;
                }
            }
            if (!$failures) {
                $notice = ['success', ($operation === 'enable' ? 'Enabled' : 'Disabled') . ' Payment Routing for ' . $success . ' selected account' . ($success === 1 ? '' : 's') . '. Saved product/domain assignments were preserved.'];
            } else {
                $notice = ['warning', 'Updated ' . $success . ' of ' . count($clientIds) . ' selected accounts. ' . implode(' | ', array_slice($failures, 0, 3))];
            }
        }
    }

    $search = trim((string) ($_GET['dm1748_q'] ?? ''));
    $rows = domainmongerpayrouting_account_rows($vars, $search);
    $initialPagerStatus = domainmongerpayrouting_initial_pager_status(count($rows));
    $token = domainmongerpayrouting_account_token();
    $esc = static fn ($v): string => domainmongerpayrouting_escape($v);

    $html = '<div class="panel panel-default dm1748-account-panel">';
    $html .= '<div class="panel-heading"><strong>Account Payment Routing</strong></div><div class="panel-body">';
    if ($notice) {
        $html .= '<div class="alert alert-' . $esc($notice[0]) . '">' . $esc($notice[1]) . '</div>';
    }
    if (!$rows) {
        $html .= '<div class="alert alert-info">' . ($search !== '' ? 'No client accounts matched that search.' : 'No managed/test accounts were found.') . '</div>';
        $html .= '</div></div>';
        return $html;
    }

    $html .= '<form method="post" action="addonmodules.php?module=domainmongerpayrouting' . ($search !== '' ? '&amp;dm1748_q=' . rawurlencode($search) : '') . '" class="dm1748-account-form" data-dm1757-pager-root="account">';
    $html .= '<input type="hidden" name="dm1748_account_action" value="apply"><input type="hidden" name="dm1748_account_token" value="' . $esc($token) . '">';
    $html .= '<div class="dm1758-primary-toolbar"><div class="dm1757-table-search"><span class="fas fa-search" aria-hidden="true"></span><input type="search" class="form-control" data-dm1757-search placeholder="Search loaded accounts" aria-label="Search Account Routing rows" autocomplete="off"></div></div>';
    $html .= '<div class="dm1758-secondary-toolbar"><div class="dm1757-table-toolbar-left"><select class="form-control dm1757-page-size" data-dm1757-page-size aria-label="Rows per page"><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option><option value="all">All</option></select></div><div class="dm1757-pager"><span class="dm1757-pager-status" data-dm1757-status>' . $esc($initialPagerStatus) . '</span><button type="button" class="btn btn-default" data-dm1757-prev>Previous</button><button type="button" class="btn btn-default" data-dm1757-next>Next</button></div></div>';
    $html .= '<div class="table-responsive"><table class="table table-striped table-bordered dm1748-account-table"><thead><tr><th style="width:48px;text-align:center;"><input type="checkbox" class="dm1748-account-select-all" aria-label="Select all"></th><th>Client</th><th style="width:130px;">Client Status</th><th style="width:130px;">Assignments</th><th style="width:150px;">Account Routing</th><th style="width:140px;">Effective Mode</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $id = (int) $row['id'];
        $nameParts = [];
        if ($row['company'] !== '') {
            $nameParts[] = $row['company'];
        }
        if ($row['name'] !== '') {
            $nameParts[] = $row['name'];
        }
        $display = $nameParts ? implode(' — ', $nameParts) : ('Client #' . $id);
        $accountStatus = !empty($row['disabled'])
            ? '<span class="label label-default"><span class="dm1775-badge-text">Disabled</span></span>'
            : '<span class="label" style="background:#163a5f;color:#fff;"><span class="dm1775-badge-text">Enabled</span></span>';
        $effective = (string) ($row['effective_mode'] ?? 'off');
        $effectiveLabel = $effective === 'live' ? 'Live' : ($effective === 'shadow' ? 'Shadow' : 'Off');
        $html .= '<tr data-dm1757-row><td style="text-align:center;vertical-align:middle;"><input type="checkbox" class="dm1748-account-check" name="dm1748_account_ids[]" value="' . $id . '"></td>';
        $html .= '<td><a href="clientssummary.php?userid=' . $id . '"><strong>' . $esc($display) . '</strong> (#' . $id . ')</a>' . ($row['email'] !== '' ? '<br><span class="text-muted">' . $esc($row['email']) . '</span>' : '') . '</td>';
        $html .= '<td>' . $esc($row['status'] !== '' ? $row['status'] : '—') . '</td><td>' . (int) $row['assignments'] . '</td><td class="dm1775-routing-status-cell">' . $accountStatus . '</td><td>' . $esc($effectiveLabel) . '</td></tr>';
    }
    $html .= '</tbody></table></div><div class="alert alert-warning dm1757-no-match" data-dm1757-no-match style="display:none;">No account rows match this search.</div><div class="dm1761-bottom-bar"><div class="dm1748-account-footer dm1761-bottom-actions"><span class="dm1748-account-count">0 selected</span><select class="form-control dm1748-account-operation" name="dm1748_account_operation" aria-label="Account routing action"><option value="">Choose Action</option><option value="enable">Enable Payment Routing</option><option value="disable">Disable Payment Routing</option></select><button type="submit" class="btn btn-primary dm1748-account-save" disabled>Apply to Selected</button></div><div class="dm1757-pager"><span class="dm1757-pager-status" data-dm1757-status>' . $esc($initialPagerStatus) . '</span><button type="button" class="btn btn-default" data-dm1757-prev>Previous</button><button type="button" class="btn btn-default" data-dm1757-next>Next</button></div></div></form>';
    $html .= <<<'HTML'
<style>
.dm1748-account-panel{margin-top:15px;border-color:#d7dce2;background:#fff}
.dm1748-account-panel>.panel-heading{background:#163a5f!important;color:#fff!important;border-color:#163a5f!important}
.dm1748-account-panel .panel-body{background:#fff}
.dm1748-account-panel .btn-primary{background:#f58220!important;border-color:#f58220!important;color:#fff!important}
.dm1748-account-panel .btn-primary:hover,.dm1748-account-panel .btn-primary:focus{background:#214e7a!important;border-color:#214e7a!important;color:#fff!important}
.dm1748-account-panel .btn[disabled]{opacity:.5;cursor:not-allowed}
.dm1748-account-table thead th{background:#163a5f;color:#fff;vertical-align:middle}
.dm1775-routing-status-cell{text-align:center;vertical-align:middle}
.dm1775-badge-text{display:inline-block;transform:scale(.86);transform-origin:center center;white-space:nowrap;letter-spacing:.5px}
.dm1748-account-footer{display:flex;justify-content:flex-start;align-items:center;gap:10px;margin:0}
.dm1748-account-operation{width:260px;height:34px}
.dm1748-account-count{color:#666;font-weight:600}
@media(max-width:767px){.dm1748-account-operation{width:100%;max-width:320px}}
</style>
<script>
(function(){
    document.querySelectorAll('.dm1748-account-form').forEach(function(form){
        if(form.getAttribute('data-dm1757-account-ready')==='1'){return;}
        form.setAttribute('data-dm1757-account-ready','1');
        var boxes=Array.prototype.slice.call(form.querySelectorAll('.dm1748-account-check'));
        var all=form.querySelector('.dm1748-account-select-all');
        var saves=Array.prototype.slice.call(form.querySelectorAll('.dm1748-account-save'));
        var count=form.querySelector('.dm1748-account-count');
        var action=form.querySelector('select[name="dm1748_account_operation"]');
        var pager=window.dm1757InitPager?window.dm1757InitPager(form):null;
        function visibleBoxes(){
            if(!pager){return boxes;}
            return pager.getVisibleRows().map(function(row){return row.querySelector('.dm1748-account-check');}).filter(Boolean);
        }
        function sync(){
            var selected=boxes.filter(function(box){return box.checked;}).length;
            if(count){count.textContent=selected+' selected';}
            var pageBoxes=visibleBoxes();
            var pageSelected=pageBoxes.filter(function(box){return box.checked;}).length;
            if(all){all.checked=pageBoxes.length>0&&pageSelected===pageBoxes.length;all.indeterminate=pageSelected>0&&pageSelected<pageBoxes.length;}
            var disabled=selected===0||!action||!action.value;
            saves.forEach(function(button){button.disabled=disabled;});
        }
        boxes.forEach(function(box){box.addEventListener('change',sync);});
        if(all){all.addEventListener('change',function(){visibleBoxes().forEach(function(box){box.checked=all.checked;});sync();});}
        if(action){action.addEventListener('change',sync);}
        form.addEventListener('dm1757:render',sync);
        sync();
    });
}());
</script>
HTML;
    $html .= '</div></div>';
    return $html;
}

/**
 * Patch 1750: read-only Payment Method Health report.
 *
 * This deliberately reads native WHMCS Pay Method/card rows directly rather
 * than reusing the routing eligibility list. Expired cards are intentionally
 * ineligible for routing, but they must remain visible to this audit report.
 */
function domainmongerpayrouting_health_expiry(string $raw): ?array
{
    $value = trim($raw);
    if ($value === '' || strpos($value, '0000-00-00') === 0) {
        return null;
    }

    if (!preg_match('/^(\d{4})-(\d{2})-/', $value, $m)) {
        return null;
    }
    $year = (int) $m[1];
    $month = (int) $m[2];
    if ($year < 2000 || $year > 2099 || $month < 1 || $month > 12) {
        return null;
    }

    try {
        $start = new DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $year, $month));
        $end = $start->modify('last day of this month')->setTime(23, 59, 59);
        $now = new DateTimeImmutable('now');
        $expired = $end < $now;
        $days = (int) floor(($end->getTimestamp() - $now->getTimestamp()) / 86400);
        return [
            'display' => sprintf('%02d/%02d', $month, $year % 100),
            'expired' => $expired,
            'soon' => !$expired && $days <= 30,
            'days' => $days,
            'end_ts' => $end->getTimestamp(),
        ];
    } catch (Throwable $e) {
        return null;
    }
}

function domainmongerpayrouting_health_method_label(?object $payMethod, ?object $card): string
{
    if (!$payMethod) {
        return 'Missing Pay Method';
    }

    $parts = [];
    if ($card) {
        $cardType = trim((string) ($card->card_type ?? ''));
        $lastFour = trim((string) ($card->last_four ?? ''));
        if ($cardType !== '') {
            $parts[] = $cardType . ($lastFour !== '' ? ' •••• ' . $lastFour : '');
        } elseif ($lastFour !== '') {
            $parts[] = 'Card •••• ' . $lastFour;
        }
    }

    $gateway = trim((string) ($payMethod->gateway_name ?? ''));
    if ($gateway !== '') {
        if (function_exists('dm1723_gateway_display')) {
            $gateway = (string) dm1723_gateway_display($gateway);
        } elseif (in_array(strtolower($gateway), ['authorize', 'authorizecim'], true)) {
            $gateway = strtolower($gateway) === 'authorizecim' ? 'Authorize.net CIM' : 'Authorize.net';
        }
        if ($gateway !== '') {
            $parts[] = $gateway;
        }
    }

    if (!$parts) {
        $parts[] = 'Pay Method #' . (int) ($payMethod->id ?? 0);
    }
    return implode(' — ', $parts);
}

function domainmongerpayrouting_health_rows(): array
{
    $GLOBALS['domainmongerpayrouting_health_error'] = '';
    try {
        $payMethods = Capsule::table('tblpaymethods')
            ->orderBy('userid', 'asc')
            ->orderBy('order_preference', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'userid', 'description', 'payment_type', 'gateway_name', 'order_preference', 'deleted_at']);

        $payById = [];
        $activeByClient = [];
        foreach ($payMethods ?: [] as $pm) {
            $id = (int) ($pm->id ?? 0);
            $clientId = (int) ($pm->userid ?? 0);
            if ($id <= 0 || $clientId <= 0) {
                continue;
            }
            $payById[$id] = $pm;
            if (empty($pm->deleted_at)) {
                $activeByClient[$clientId][] = $pm;
            }
        }

        // Match the confirmed Admin Payment Methods manager: prefer
        // order_preference=0; only if older/inconsistent data has no zero row,
        // fall back to the first active row in the same native ordering.
        $defaultByClient = [];
        foreach ($activeByClient as $clientId => $methods) {
            foreach ($methods as $pm) {
                if ((int) ($pm->order_preference ?? 0) === 0) {
                    $defaultByClient[(int) $clientId] = (int) ($pm->id ?? 0);
                    break;
                }
            }
            if (!isset($defaultByClient[(int) $clientId]) && $methods) {
                $defaultByClient[(int) $clientId] = (int) ($methods[0]->id ?? 0);
            }
        }

        $cardsByPayMethod = [];
        $cards = Capsule::table('tblcreditcards')
            ->whereNull('deleted_at')
            ->get(['pay_method_id', 'card_type', 'last_four', 'expiry_date']);
        foreach ($cards ?: [] as $card) {
            $pmId = (int) ($card->pay_method_id ?? 0);
            if ($pmId > 0 && !isset($cardsByPayMethod[$pmId])) {
                $cardsByPayMethod[$pmId] = $card;
            }
        }

        // Patch 1764: a Pay Method with an explicit gateway_name is unusable
        // when that gateway is no longer activated in WHMCS. Local card Pay
        // Methods can legitimately have an empty gateway_name, so do not infer
        // an inactive gateway for those rows.
        $activeGateways = [];
        try {
            foreach (Capsule::table('tblpaymentgateways')->distinct()->pluck('gateway')->all() ?: [] as $gatewayName) {
                $gatewayKey = strtolower(trim((string) $gatewayName));
                if ($gatewayKey !== '') {
                    $activeGateways[$gatewayKey] = true;
                }
            }
        } catch (Throwable $e) {
            // If gateway configuration cannot be read, omit Gateway Inactive
            // findings rather than generating false positives.
            $activeGateways = null;
        }

        // Read every explicit product/domain assignment once. These rows are
        // usage context for active PMs and are also the source for stale /
        // deleted-PM health issues.
        $assignments = Capsule::table('mod_domainmonger_item_paymethod_assignments')
            ->whereIn('item_type', ['service', 'domain'])
            ->where('pay_method_id', '>', 0)
            ->orderBy('userid', 'asc')
            ->orderBy('pay_method_id', 'asc')
            ->get(['userid', 'item_type', 'item_id', 'pay_method_id']);

        $assignmentGroups = [];
        $assignmentByItem = [];
        $serviceIds = [];
        $domainIds = [];
        foreach ($assignments ?: [] as $assignment) {
            $clientId = (int) ($assignment->userid ?? 0);
            $pmId = (int) ($assignment->pay_method_id ?? 0);
            $type = strtolower(trim((string) ($assignment->item_type ?? '')));
            $itemId = (int) ($assignment->item_id ?? 0);
            if ($clientId <= 0 || $pmId <= 0 || $itemId <= 0 || !in_array($type, ['service', 'domain'], true)) {
                continue;
            }
            $key = $clientId . ':' . $pmId;
            $assignmentGroups[$key]['client_id'] = $clientId;
            $assignmentGroups[$key]['pay_method_id'] = $pmId;
            $assignmentGroups[$key]['items'][] = ['type' => $type, 'id' => $itemId];
            $assignmentByItem[$type . ':' . $itemId] = [
                'client_id' => $clientId,
                'pay_method_id' => $pmId,
            ];
            if ($type === 'service') {
                $serviceIds[$itemId] = true;
            } else {
                $domainIds[$itemId] = true;
            }
        }

        $serviceLabels = [];
        if ($serviceIds) {
            $services = Capsule::table('tblhosting')->whereIn('id', array_keys($serviceIds))->get(['id', 'packageid', 'domain']);
            $packageIds = [];
            foreach ($services ?: [] as $service) {
                $pid = (int) ($service->packageid ?? 0);
                if ($pid > 0) {
                    $packageIds[$pid] = true;
                }
            }
            $productNames = [];
            if ($packageIds) {
                foreach (Capsule::table('tblproducts')->whereIn('id', array_keys($packageIds))->get(['id', 'name']) ?: [] as $product) {
                    $productNames[(int) ($product->id ?? 0)] = trim((string) ($product->name ?? ''));
                }
            }
            foreach ($services ?: [] as $service) {
                $id = (int) ($service->id ?? 0);
                $name = $productNames[(int) ($service->packageid ?? 0)] ?? 'Product/Service';
                $domain = trim((string) ($service->domain ?? ''));
                $serviceLabels[$id] = $name . ($domain !== '' ? ' — ' . $domain : '');
            }
        }

        $domainLabels = [];
        if ($domainIds) {
            foreach (Capsule::table('tbldomains')->whereIn('id', array_keys($domainIds))->get(['id', 'domain']) ?: [] as $domain) {
                $domainLabels[(int) ($domain->id ?? 0)] = trim((string) ($domain->domain ?? '')) ?: 'Domain';
            }
        }

        $affectedLinksForGroup = static function (array $group, int $clientId) use ($serviceLabels, $domainLabels): array {
            $affected = [];
            foreach (($group['items'] ?? []) as $item) {
                $type = (string) ($item['type'] ?? '');
                $id = (int) ($item['id'] ?? 0);
                if ($type === 'service') {
                    $affected[] = [
                        'label' => 'Product #' . $id . ' — ' . ($serviceLabels[$id] ?? 'Product/Service'),
                        'url' => 'clientsservices.php?userid=' . $clientId . '&id=' . $id,
                    ];
                } elseif ($type === 'domain') {
                    $affected[] = [
                        'label' => 'Domain #' . $id . ' — ' . ($domainLabels[$id] ?? 'Domain'),
                        'url' => 'clientsdomains.php?userid=' . $clientId . '&id=' . $id,
                    ];
                }
            }
            return $affected;
        };

        // Patch 1757: for a Default PM that is expired/expiring, "Affected
        // Items" means renewal-enabled items that actually resolve to that
        // Default PM. Explicit items assigned to another PM are not affected
        // by the Default PM's health issue.
        $defaultRenewalAffected = [];
        $defaultAuditClients = [];
        foreach ($defaultByClient as $clientId => $defaultPmId) {
            $card = $cardsByPayMethod[(int) $defaultPmId] ?? null;
            if (!$card || strcasecmp(trim((string) ($card->card_type ?? '')), 'PayPal') === 0) {
                continue;
            }
            $expiry = domainmongerpayrouting_health_expiry((string) ($card->expiry_date ?? ''));
            if (!$expiry || (!$expiry['expired'] && !$expiry['soon'])) {
                continue;
            }
            $defaultAuditClients[(int) $clientId] = (int) $defaultPmId;
        }

        if ($defaultAuditClients) {
            $auditClientIds = array_map('intval', array_keys($defaultAuditClients));

            // Products/services: recurring, currently renewable service states,
            // and no pending cancellation request. WHMCS does not expose the
            // same donotrenew flag used by domains, so this mirrors whether a
            // service is still expected to generate renewal billing.
            $renewalServices = Capsule::table('tblhosting')
                ->whereIn('userid', $auditClientIds)
                ->get(['id', 'userid', 'packageid', 'domain', 'domainstatus', 'billingcycle', 'nextduedate']);

            $serviceCancelIds = [];
            $renewalServiceIds = [];
            $renewalPackageIds = [];
            foreach ($renewalServices ?: [] as $service) {
                $sid = (int) ($service->id ?? 0);
                if ($sid > 0) {
                    $renewalServiceIds[$sid] = true;
                }
                $pid = (int) ($service->packageid ?? 0);
                if ($pid > 0) {
                    $renewalPackageIds[$pid] = true;
                }
            }
            try {
                if ($renewalServiceIds && Capsule::schema()->hasTable('tblcancelrequests')) {
                    foreach (Capsule::table('tblcancelrequests')->whereIn('relid', array_keys($renewalServiceIds))->pluck('relid')->all() ?: [] as $relid) {
                        $relid = (int) $relid;
                        if ($relid > 0) {
                            $serviceCancelIds[$relid] = true;
                        }
                    }
                }
            } catch (Throwable $e) {
                // If cancellation-request metadata cannot be read, do not let
                // the Health report fail. The service status/billing checks
                // below remain the conservative baseline.
            }

            $renewalProductNames = [];
            if ($renewalPackageIds) {
                foreach (Capsule::table('tblproducts')->whereIn('id', array_keys($renewalPackageIds))->get(['id', 'name']) ?: [] as $product) {
                    $renewalProductNames[(int) ($product->id ?? 0)] = trim((string) ($product->name ?? ''));
                }
            }

            foreach ($renewalServices ?: [] as $service) {
                $sid = (int) ($service->id ?? 0);
                $ownerId = (int) ($service->userid ?? 0);
                if ($sid <= 0 || !isset($defaultAuditClients[$ownerId])) {
                    continue;
                }
                $status = strtolower(trim((string) ($service->domainstatus ?? '')));
                $billing = strtolower(trim((string) ($service->billingcycle ?? '')));
                $nextDue = trim((string) ($service->nextduedate ?? ''));
                if (!in_array($status, ['active', 'suspended'], true)) {
                    continue;
                }
                if ($billing === '' || in_array($billing, ['free account', 'free', 'one time', 'onetime'], true)) {
                    continue;
                }
                if ($nextDue === '' || strpos($nextDue, '0000-00-00') === 0 || isset($serviceCancelIds[$sid])) {
                    continue;
                }
                $assignedPm = (int) (($assignmentByItem['service:' . $sid]['pay_method_id'] ?? 0));
                $defaultPm = (int) $defaultAuditClients[$ownerId];
                if ($assignedPm > 0 && $assignedPm !== $defaultPm) {
                    continue;
                }
                $productName = $renewalProductNames[(int) ($service->packageid ?? 0)] ?? 'Product/Service';
                $domain = trim((string) ($service->domain ?? ''));
                $label = 'Product #' . $sid . ' — ' . $productName . ($domain !== '' ? ' — ' . $domain : '');
                $defaultRenewalAffected[$ownerId][] = [
                    'label' => $label,
                    'url' => 'clientsservices.php?userid=' . $ownerId . '&id=' . $sid,
                ];
            }

            // Domains: WHMCS's native donotrenew flag is authoritative for
            // automatic renewal invoicing; only Active + Auto Renew enabled
            // domains are exposed by the Default PM row.
            $renewalDomains = Capsule::table('tbldomains')
                ->whereIn('userid', $auditClientIds)
                ->get(['id', 'userid', 'domain', 'status', 'donotrenew', 'nextduedate']);
            foreach ($renewalDomains ?: [] as $domain) {
                $domainId = (int) ($domain->id ?? 0);
                $ownerId = (int) ($domain->userid ?? 0);
                if ($domainId <= 0 || !isset($defaultAuditClients[$ownerId])) {
                    continue;
                }
                $status = strtolower(trim((string) ($domain->status ?? '')));
                $nextDue = trim((string) ($domain->nextduedate ?? ''));
                if ($status !== 'active' || (int) ($domain->donotrenew ?? 0) === 1) {
                    continue;
                }
                if ($nextDue === '' || strpos($nextDue, '0000-00-00') === 0) {
                    continue;
                }
                $assignedPm = (int) (($assignmentByItem['domain:' . $domainId]['pay_method_id'] ?? 0));
                $defaultPm = (int) $defaultAuditClients[$ownerId];
                if ($assignedPm > 0 && $assignedPm !== $defaultPm) {
                    continue;
                }
                $domainName = trim((string) ($domain->domain ?? '')) ?: 'Domain';
                $defaultRenewalAffected[$ownerId][] = [
                    'label' => 'Domain #' . $domainId . ' — ' . $domainName,
                    'url' => 'clientsdomains.php?userid=' . $ownerId . '&id=' . $domainId,
                ];
            }
        }

        $rows = [];
        $affectedClientIds = [];

        // Patch 1764: audit every active stored Payment Method. Card
        // expiration health applies to real card rows; gateway health applies
        // to any Pay Method that names a gateway. A PM can therefore produce
        // more than one issue row (for example Expired + Gateway Inactive).
        foreach ($activeByClient as $clientId => $methods) {
            foreach ($methods as $pm) {
                $pmId = (int) ($pm->id ?? 0);
                $card = $cardsByPayMethod[$pmId] ?? null;
                $key = (int) $clientId . ':' . $pmId;
                $group = $assignmentGroups[$key] ?? [];
                $assigned = !empty($group['items']);
                $isDefault = (($defaultByClient[(int) $clientId] ?? 0) === $pmId);

                if ($isDefault && $assigned) {
                    $usage = 'Default + Assigned';
                } elseif ($isDefault) {
                    $usage = 'Default';
                } elseif ($assigned) {
                    $usage = 'Assigned';
                } else {
                    $usage = 'Unassigned';
                }

                $affected = $isDefault
                    ? ($defaultRenewalAffected[(int) $clientId] ?? [])
                    : ($assigned ? $affectedLinksForGroup($group, (int) $clientId) : []);

                $cardType = $card ? trim((string) ($card->card_type ?? '')) : '';
                $isPayPalCard = $card && strcasecmp($cardType, 'PayPal') === 0;

                // Real card expiration health. Missing/malformed expiration is
                // actionable even when the PM is not Default or assigned.
                if ($card && !$isPayPalCard) {
                    $expiryRaw = trim((string) ($card->expiry_date ?? ''));
                    $expiry = domainmongerpayrouting_health_expiry($expiryRaw);
                    if ($expiry && ($expiry['expired'] || $expiry['soon'])) {
                        $tags = [];
                        if ($expiry['expired']) {
                            $tags[] = 'expired';
                            if ($assigned) {
                                $tags[] = 'assigned-expired';
                            }
                            if (!$assigned) {
                                $tags[] = 'unassigned-expired';
                            }
                        } else {
                            $tags[] = 'expires-soon';
                        }

                        $rows[] = [
                            'severity' => $expiry['expired'] ? 10 : 40,
                            'issue' => $expiry['expired'] ? 'Expired' : 'Expires ≤30 Days',
                            'client_id' => (int) $clientId,
                            'pay_method_id' => $pmId,
                            'method' => domainmongerpayrouting_health_method_label($pm, $card),
                            'description' => trim((string) ($pm->description ?? '')),
                            'expiration' => (string) $expiry['display'],
                            'expiry_end_ts' => (int) ($expiry['end_ts'] ?? 0),
                            'usage' => $usage,
                            'is_default' => $isDefault,
                            'assigned' => $assigned,
                            'tags' => $tags,
                            'affected' => $affected,
                        ];
                        $affectedClientIds[(int) $clientId] = true;
                    } elseif (!$expiry) {
                        $rows[] = [
                            'severity' => 30,
                            'issue' => 'Invalid Expiration',
                            'client_id' => (int) $clientId,
                            'pay_method_id' => $pmId,
                            'method' => domainmongerpayrouting_health_method_label($pm, $card),
                            'description' => trim((string) ($pm->description ?? '')),
                            'expiration' => $expiryRaw !== '' ? $expiryRaw : '—',
                            'usage' => $usage,
                            'is_default' => $isDefault,
                            'assigned' => $assigned,
                            'tags' => ['invalid-expiration'],
                            'affected' => $affected,
                        ];
                        $affectedClientIds[(int) $clientId] = true;
                    }
                }

                // Explicitly named gateways that are no longer configured are
                // unhealthy regardless of whether the PM is a card, PayPal,
                // bank account, Default, Assigned, or currently unused.
                $gatewayKey = strtolower(trim((string) ($pm->gateway_name ?? '')));
                if (is_array($activeGateways) && $gatewayKey !== '' && !isset($activeGateways[$gatewayKey])) {
                    $rows[] = [
                        'severity' => 25,
                        'issue' => 'Gateway Inactive',
                        'client_id' => (int) $clientId,
                        'pay_method_id' => $pmId,
                        'method' => domainmongerpayrouting_health_method_label($pm, $card),
                        'description' => trim((string) ($pm->description ?? '')),
                        'expiration' => ($card && !$isPayPalCard && ($parsed = domainmongerpayrouting_health_expiry((string) ($card->expiry_date ?? '')))) ? (string) $parsed['display'] : '—',
                        'usage' => $usage,
                        'is_default' => $isDefault,
                        'assigned' => $assigned,
                        'tags' => ['gateway-inactive'],
                        'affected' => $affected,
                    ];
                    $affectedClientIds[(int) $clientId] = true;
                }
            }
        }

        // Stale/deleted explicit assignments remain a separate health issue.
        foreach ($assignmentGroups as $group) {
            $clientId = (int) ($group['client_id'] ?? 0);
            $pmId = (int) ($group['pay_method_id'] ?? 0);
            $pm = $payById[$pmId] ?? null;
            $deleted = $pm && !empty($pm->deleted_at);
            $missing = !$pm || $deleted;
            if (!$missing) {
                continue;
            }

            $rows[] = [
                'severity' => 20,
                'issue' => 'Missing/Deleted',
                'client_id' => $clientId,
                'pay_method_id' => $pmId,
                'method' => 'Pay Method #' . $pmId,
                'description' => $pm ? trim((string) ($pm->description ?? '')) : '',
                'expiration' => '—',
                'usage' => 'Assigned',
                'is_default' => false,
                'assigned' => true,
                'tags' => ['missing-deleted'],
                'affected' => $affectedLinksForGroup($group, $clientId),
            ];
            $affectedClientIds[$clientId] = true;
        }

        $clients = [];
        $accountInventory = [];
        if ($affectedClientIds) {
            $affectedIds = array_map('intval', array_keys($affectedClientIds));
            foreach (Capsule::table('tblclients')->whereIn('id', $affectedIds)->get(['id', 'firstname', 'lastname', 'companyname', 'email', 'status']) ?: [] as $client) {
                $clients[(int) ($client->id ?? 0)] = $client;
            }

            foreach ($affectedIds as $affectedId) {
                $accountInventory[$affectedId] = [
                    'products_total' => 0,
                    'products_status' => [],
                    'domains_total' => 0,
                    'domains_status' => [],
                ];
            }

            foreach (Capsule::table('tblhosting')->whereIn('userid', $affectedIds)->get(['userid', 'domainstatus']) ?: [] as $service) {
                $ownerId = (int) ($service->userid ?? 0);
                if (!isset($accountInventory[$ownerId])) {
                    continue;
                }
                $status = trim((string) ($service->domainstatus ?? '')) ?: 'Unknown';
                $accountInventory[$ownerId]['products_total']++;
                $accountInventory[$ownerId]['products_status'][$status] = ($accountInventory[$ownerId]['products_status'][$status] ?? 0) + 1;
            }

            foreach (Capsule::table('tbldomains')->whereIn('userid', $affectedIds)->get(['userid', 'status']) ?: [] as $domain) {
                $ownerId = (int) ($domain->userid ?? 0);
                if (!isset($accountInventory[$ownerId])) {
                    continue;
                }
                $status = trim((string) ($domain->status ?? '')) ?: 'Unknown';
                $accountInventory[$ownerId]['domains_total']++;
                $accountInventory[$ownerId]['domains_status'][$status] = ($accountInventory[$ownerId]['domains_status'][$status] ?? 0) + 1;
            }
        }

        foreach ($rows as &$row) {
            $client = $clients[(int) $row['client_id']] ?? null;
            $person = $client ? trim((string) ($client->firstname ?? '') . ' ' . (string) ($client->lastname ?? '')) : '';
            $company = $client ? trim((string) ($client->companyname ?? '')) : '';
            $row['client_name'] = $company !== '' ? $company . ($person !== '' ? ' — ' . $person : '') : ($person !== '' ? $person : 'Client #' . (int) $row['client_id']);
            $row['email'] = $client ? trim((string) ($client->email ?? '')) : '';
            $row['client_status'] = $client ? trim((string) ($client->status ?? '')) : '';
            $row['account_inventory'] = $accountInventory[(int) $row['client_id']] ?? [
                'products_total' => 0,
                'products_status' => [],
                'domains_total' => 0,
                'domains_status' => [],
            ];
        }
        unset($row);

        usort($rows, static function (array $a, array $b): int {
            $severity = ((int) ($a['severity'] ?? 99)) <=> ((int) ($b['severity'] ?? 99));
            if ($severity !== 0) {
                return $severity;
            }
            $client = ((int) ($a['client_id'] ?? 0)) <=> ((int) ($b['client_id'] ?? 0));
            if ($client !== 0) {
                return $client;
            }
            return ((int) ($a['pay_method_id'] ?? 0)) <=> ((int) ($b['pay_method_id'] ?? 0));
        });

        return $rows;
    } catch (Throwable $e) {
        $GLOBALS['domainmongerpayrouting_health_error'] = $e->getMessage();
        if (function_exists('logActivity')) {
            logActivity('DomainMonger Payment Routing [HEALTH 1764] Could not build Payment Method Health report: ' . $e->getMessage());
        }
        return [];
    }
}

function domainmongerpayrouting_health_status_summary(array $statuses): string
{
    if (!$statuses) {
        return '<span class="text-muted">None</span>';
    }

    $preferred = [
        'Active', 'Pending', 'Pending Registration', 'Pending Transfer',
        'Suspended', 'Grace', 'Redemption', 'Expired', 'Cancelled',
        'Terminated', 'Fraud', 'Transferred Away', 'Unknown',
    ];
    $rank = array_flip($preferred);
    uksort($statuses, static function ($a, $b) use ($rank): int {
        $ra = $rank[(string) $a] ?? 999;
        $rb = $rank[(string) $b] ?? 999;
        if ($ra !== $rb) {
            return $ra <=> $rb;
        }
        return strcasecmp((string) $a, (string) $b);
    });

    $parts = [];
    foreach ($statuses as $status => $count) {
        $statusText = trim((string) $status) ?: 'Unknown';
        $statusLower = strtolower($statusText);
        $class = 'dm1751-health-status-neutral';
        if (in_array($statusLower, ['cancelled', 'terminated', 'fraud', 'expired', 'redemption'], true)) {
            $class = 'dm1751-health-status-danger';
        } elseif (in_array($statusLower, ['pending', 'pending registration', 'pending transfer', 'suspended', 'grace'], true)) {
            $class = 'dm1751-health-status-warning';
        } elseif ($statusLower === 'active') {
            $class = 'dm1751-health-status-active';
        }
        $parts[] = '<span class="dm1751-health-status ' . $class . '">' . domainmongerpayrouting_escape($statusText) . ' ' . (int) $count . '</span>';
    }
    return implode(' ', $parts);
}

function domainmongerpayrouting_health_report_html(): string
{
    $rows = domainmongerpayrouting_health_rows();
    $healthError = trim((string) ($GLOBALS['domainmongerpayrouting_health_error'] ?? ''));
    $esc = static fn ($v): string => domainmongerpayrouting_escape($v);
    $deleteToken = domainmongerpayrouting_health_delete_token();

    // Patch 1753: these are filter counts, not mutually-exclusive buckets.
    // Assigned Expired and Unassigned Expired are subsets of Expired.
    $counts = [
        'all' => count($rows),
        'expired' => 0,
        'expires-soon' => 0,
        'assigned-expired' => 0,
        'missing-deleted' => 0,
        'unassigned-expired' => 0,
        'gateway-inactive' => 0,
        'invalid-expiration' => 0,
    ];
    foreach ($rows as $row) {
        $tags = is_array($row['tags'] ?? null) ? $row['tags'] : [];
        foreach (['expired', 'expires-soon', 'assigned-expired', 'missing-deleted', 'unassigned-expired', 'gateway-inactive', 'invalid-expiration'] as $tag) {
            if (in_array($tag, $tags, true)) {
                $counts[$tag]++;
            }
        }
    }

    // Patch 1778: server-generated cutoffs keep the dry-run age calculation
    // consistent with WHMCS/PHP time rather than the Admin browser clock.
    $expiredAgeCutoffs = [];
    try {
        $healthNow = new DateTimeImmutable('now');
        foreach ([1, 3, 5] as $years) {
            $expiredAgeCutoffs[$years] = $healthNow->modify('-' . $years . ' years')->getTimestamp();
        }
    } catch (Throwable $e) {
        $expiredAgeCutoffs = [];
    }

    // Patch 1756: emit Health CSS before the long table so the toolbar is correct on first paint.
    $html = <<<'HTML'
<style>
.dm1750-health-panel{margin-top:15px;border-color:#d7dce2;background:#fff}
.dm1750-health-panel>.panel-heading{background:#163a5f!important;color:#fff!important;border-color:#163a5f!important}
.dm1750-health-panel .panel-body{background:#fff}
.dm1755-health-toolbar{display:flex;align-items:flex-start;gap:12px;margin:0 0 7px}
.dm1769-health-filter-wrap{flex:0 0 235px;margin:0}
.dm1769-health-filter-select{width:100%;height:34px!important;border-color:#c7ced6!important;background:#fff!important;color:#163a5f!important;font-weight:600;box-shadow:none!important}
.dm1769-health-filter-select:focus{border-color:#214e7a!important;box-shadow:0 0 0 1px rgba(33,78,122,.12)!important}
.dm1778-expired-age-wrap{display:flex;flex:0 0 470px;gap:8px;margin:0}
.dm1778-expired-age-select{width:235px;height:34px!important;border-color:#c7ced6!important;background:#fff!important;color:#163a5f!important;font-weight:600;box-shadow:none!important}
.dm1778-expired-age-select:focus{border-color:#214e7a!important;box-shadow:0 0 0 1px rgba(33,78,122,.12)!important}
.dm1781-custom-expiry-date{display:none;width:225px;height:34px!important;border-color:#c7ced6!important;background:#fff!important;color:#163a5f!important;font-weight:600;box-shadow:none!important}
.dm1781-custom-expiry-date:focus{border-color:#214e7a!important;box-shadow:0 0 0 1px rgba(33,78,122,.12)!important}
.dm1778-expired-age-wrap.dm1781-custom-active .dm1781-custom-expiry-date{display:block}
.dm1778-delete-preview{margin:0 0 10px;padding:9px 12px}
.dm1778-delete-preview strong{color:#163a5f}
.dm1778-delete-preview .dm1778-preview-meta{margin-top:3px;color:#665c42}
.dm1755-health-search{position:relative;flex:0 0 250px;margin-left:auto}
.dm1755-health-search .fas{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#7b8794;pointer-events:none;z-index:2}
.dm1755-health-search .form-control{width:100%;height:34px;padding-left:31px;border-color:#c7ced6;background:#fff;color:#222}
.dm1755-health-search .form-control:focus{border-color:#214e7a;box-shadow:0 0 0 1px rgba(33,78,122,.12)}
@media(max-width:1100px){.dm1755-health-toolbar{flex-wrap:wrap}.dm1769-health-filter-wrap{flex:0 1 235px}.dm1778-expired-age-wrap{flex:1 1 470px;max-width:470px}.dm1755-health-search{flex:1 1 250px;max-width:360px;margin-left:0}}
@media(max-width:560px){.dm1778-expired-age-wrap{flex:1 1 100%;max-width:none;flex-wrap:wrap}.dm1778-expired-age-select,.dm1781-custom-expiry-date{width:100%}}
.dm1753-health-no-match{margin:0 0 12px}
.dm1750-health-table thead th{background:#163a5f;color:#fff;vertical-align:middle}
.dm1780-sortable-heading{padding-right:30px!important;position:relative;cursor:pointer;white-space:nowrap}
/* Match the WHMCS/DataTables sorting treatment used by the Admin Client Profile Payment Methods manager. */
.dm1780-sortable-heading.sorting:after,.dm1780-sortable-heading.sorting_asc:after,.dm1780-sortable-heading.sorting_desc:after{position:absolute;bottom:5px;right:8px;display:block;font-family:'Glyphicons Halflings';opacity:.5;font-weight:400}
.dm1780-sortable-heading.sorting:after{opacity:.2;content:"\e150"}
.dm1780-sortable-heading.sorting_asc:after{content:"\e155"}
.dm1780-sortable-heading.sorting_desc:after{content:"\e156"}
.dm1780-sort-button{border:0!important;padding:0!important;margin:0!important;background:transparent!important;color:inherit!important;font:inherit;font-weight:600;cursor:pointer;box-shadow:none!important}
.dm1780-sort-button:hover,.dm1780-sort-button:focus,.dm1780-sort-button:active{color:inherit!important;text-decoration:none!important;outline:none!important}
.dm1750-health-table td{vertical-align:middle}
.dm1750-health-table a{color:#222;text-decoration:none}
.dm1750-health-table a:hover,.dm1750-health-table a:focus{color:#f58220;text-decoration:none}
.dm1750-health-table .label-warning{background:#d8741f;color:#fff}
.dm1764-health-issue{font-family:Arial,Helvetica,sans-serif!important;font-weight:600;white-space:nowrap;transition:none!important;animation:none!important}
.dm1766-health-issue-text{display:inline-block;position:relative;top:1px;transform:scale(.86);transform-origin:center center;white-space:nowrap;letter-spacing:.5px;}
.dm1783-select-col{width:42px;min-width:42px;text-align:center!important;vertical-align:middle!important}
.dm1783-select-col input[type=checkbox]{margin:0;vertical-align:middle}
.dm1783-selection-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:10px}
.dm1789-top-selection-bar{margin:0 0 10px}
.dm1783-selection-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.dm1798-action-group{display:inline-flex;align-items:center;gap:8px;flex-wrap:wrap}
.dm1798-action-group+.dm1798-action-group{margin-left:10px;padding-left:14px;border-left:1px solid #d7dce2}
.dm1798-action-label{color:#163a5f;font-size:12px;font-weight:700;white-space:nowrap}
@media(max-width:900px){.dm1798-action-group+.dm1798-action-group{margin-left:0;padding-left:0;border-left:0;width:100%}}
.dm1783-selected-count{color:#666;font-weight:600;min-width:105px}
.dm1783-preview-selected{min-width:165px}
.dm1786-delete-test-form{display:inline-flex;margin:0}
.dm1786-delete-test{min-width:205px;background:#b94a48!important;border-color:#b94a48!important;color:#fff!important}
.dm1786-delete-test:hover,.dm1786-delete-test:focus{background:#9f3f3d!important;border-color:#9f3f3d!important;color:#fff!important}
.dm1786-delete-test[disabled]{opacity:.5!important;cursor:not-allowed!important}
.dm1790-auto-delete{min-width:265px;background:#b94a48!important;border-color:#b94a48!important;color:#fff!important}
.dm1790-auto-delete:hover,.dm1790-auto-delete:focus{background:#9f3f3d!important;border-color:#9f3f3d!important;color:#fff!important}
.dm1790-auto-delete[disabled]{opacity:.5!important;cursor:not-allowed!important}
.dm1790-auto-progress{display:none;margin:0 0 10px;padding:9px 12px}
.dm1790-auto-progress.is-active{display:block}
.dm1783-selected-preview{margin:10px 0 0;padding:9px 12px}
.dm1783-selected-preview strong{color:#163a5f}
.dm1783-selected-preview-meta{margin-top:3px;color:#665c42}
.dm1783-selected-preview-list{margin:7px 0 0;padding-left:19px;max-height:240px;overflow:auto}
.dm1783-selected-preview-list li{margin:2px 0}
.dm1752-health-scroll{display:block;width:100%;max-width:100%;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;border:0}
.dm1752-health-scroll .dm1750-health-table{width:max-content;min-width:1260px;margin-bottom:0}
.dm1750-health-table .dm1752-health-client-col{width:170px;min-width:170px;max-width:190px;white-space:normal;overflow-wrap:anywhere;word-break:break-word}
.dm1753-health-usage{display:inline-block;border-radius:10px;padding:2px 8px;font-size:11px;line-height:16px;white-space:nowrap;border:1px solid #cbd2d9;background:#f1f3f5;color:#444}
.dm1753-usage-default{border-color:#163a5f;background:#eef3f8;color:#163a5f}
.dm1753-usage-assigned{border-color:#d8741f;background:#fff4e8;color:#9a5317}
.dm1753-usage-default-assigned{border-color:#d8741f;background:#fff0df;color:#8f4a11}
.dm1753-usage-unassigned{border-color:#cbd2d9;background:#f1f3f5;color:#555}
.dm1751-health-inventory>strong{display:block;margin:0 0 3px;color:#163a5f}
.dm1751-health-inventory>div{display:flex;flex-wrap:wrap;gap:4px;margin:0 0 7px}
.dm1751-health-inventory>div:last-child{margin-bottom:0}
.dm1751-health-status{display:inline-block;border-radius:10px;padding:2px 7px;font-size:11px;line-height:16px;white-space:nowrap;border:1px solid #cbd2d9;background:#f1f3f5;color:#444}
.dm1751-health-status-active{border-color:#163a5f;background:#eef3f8;color:#163a5f}
.dm1751-health-status-warning{border-color:#d8741f;background:#fff4e8;color:#9a5317}
.dm1751-health-status-danger{border-color:#b94a48;background:#f9ecec;color:#9b3533}
.dm1751-health-status-neutral{border-color:#cbd2d9;background:#f1f3f5;color:#555}
</style>
HTML;
    $initialPagerStatus = domainmongerpayrouting_initial_pager_status(count($rows));
    $html .= '<div class="panel panel-default dm1750-health-panel" data-dm1753-health-panel data-dm1757-pager-root="health">';
    $html .= '<div class="panel-heading"><strong>Payment Method Health</strong></div><div class="panel-body">';
    $html .= '<script>(function(){try{if(window.sessionStorage&&window.sessionStorage.getItem("dm1791_health_return_state")){var p=document.currentScript&&document.currentScript.closest?document.currentScript.closest("[data-dm1753-health-panel]"):null;if(p){p.style.visibility="hidden";p.setAttribute("data-dm1792-restoring","1");}}}catch(e){}})();</script>';
    $html .= '<div class="dm1758-primary-toolbar dm1755-health-toolbar">'
        . '<div class="dm1769-health-filter-wrap">'
        . '<select class="form-control dm1769-health-filter-select" data-dm1753-filter-select aria-label="Payment Method Health filter">'
        . '<option value="all">All Issues — ' . number_format((int) $counts['all']) . '</option>'
        . '<option value="expired">Expired — ' . number_format((int) $counts['expired']) . '</option>'
        . '<option value="expires-soon">Exp ≤30 Days — ' . number_format((int) $counts['expires-soon']) . '</option>'
        . '<option value="assigned-expired">Assigned Exp — ' . number_format((int) $counts['assigned-expired']) . '</option>'
        . '<option value="missing-deleted">Missing/Deleted — ' . number_format((int) $counts['missing-deleted']) . '</option>'
        . '<option value="unassigned-expired">Unassigned Exp — ' . number_format((int) $counts['unassigned-expired']) . '</option>'
        . '<option value="gateway-inactive">Gateway Off — ' . number_format((int) $counts['gateway-inactive']) . '</option>'
        . '<option value="invalid-expiration">Invalid Exp — ' . number_format((int) $counts['invalid-expiration']) . '</option>'
        . '</select>'
        . '</div>'
        . '<div class="dm1778-expired-age-wrap"><select class="form-control dm1778-expired-age-select" data-dm1778-expired-age aria-label="Expired card age filter">'
        . '<option value="0" data-cutoff="0">Expired Age — Any</option>';
    foreach ($expiredAgeCutoffs as $years => $cutoffTs) {
        $html .= '<option value="' . (int) $years . '" data-cutoff="' . (int) $cutoffTs . '">Expired more than ' . (int) $years . ' ' . ((int) $years === 1 ? 'year' : 'years') . ' ago</option>';
    }
    $html .= '<option value="custom" data-cutoff="0">Custom date…</option></select><input type="date" class="form-control dm1781-custom-expiry-date" data-dm1781-custom-expiry-date aria-label="Custom expiration cutoff date"></div>'
        . '<div class="dm1755-health-search dm1757-table-search"><span class="fas fa-search" aria-hidden="true"></span><input type="search" class="form-control" data-dm1755-health-search data-dm1757-search placeholder="Search health results" aria-label="Search Payment Method Health results" autocomplete="off"></div>'
        . '</div>';
    $html .= '<div class="dm1758-secondary-toolbar"><div class="dm1757-table-toolbar-left"><select class="form-control dm1757-page-size" data-dm1757-page-size aria-label="Rows per page"><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option><option value="all">All</option></select></div><div class="dm1757-pager"><span class="dm1757-pager-status" data-dm1757-status>' . $esc($initialPagerStatus) . '</span><button type="button" class="btn btn-default" data-dm1757-prev>Previous</button><button type="button" class="btn btn-default" data-dm1757-next>Next</button></div></div>';

    if ($healthError !== '') {
        $html .= '<div class="alert alert-danger" style="margin-bottom:0;">Payment Method Health could not be read. The technical error was written to the WHMCS Activity Log.</div></div></div>';
        return $html;
    }

    if (!$rows) {
        $html .= '<div class="alert alert-success" style="margin-bottom:0;">No Payment Method Health issues were found.</div></div></div>';
        return $html;
    }

    $html .= '<div class="dm1753-health-no-match alert alert-warning" data-dm1753-no-match data-dm1757-no-match style="display:none;">No Payment Method Health rows match this filter.</div>';
    $html .= '<div class="dm1783-selection-bar dm1789-top-selection-bar"><div class="dm1783-selection-actions"><div class="dm1798-action-group"><span class="dm1798-action-label">Selected Cards:</span><span class="dm1783-selected-count" data-dm1783-selected-count>0 selected</span><button type="button" class="btn btn-primary dm1783-preview-selected" data-dm1783-preview-selected disabled>Preview Selected Deletion</button><button type="button" class="btn btn-danger dm1786-delete-test" data-dm1789-delete-top disabled>Delete Selected — 25 Max</button></div><div class="dm1798-action-group"><button type="button" class="btn btn-danger dm1790-auto-delete" data-dm1790-auto-delete title="Uses the current filters, search, and sort order; checkboxes are not required." disabled>Delete Up to 1,000 Filtered Results</button></div></div></div><div class="alert alert-info dm1790-auto-progress" data-dm1790-auto-progress><strong>Deletion progress:</strong> <span data-dm1790-auto-progress-text>Preparing…</span></div>';
    $html .= '<div class="dm1752-health-scroll"><table class="table table-striped table-bordered dm1750-health-table"><thead><tr>';
    $html .= '<th class="dm1783-select-col"><input type="checkbox" data-dm1783-select-all aria-label="Select all expired cards on this page" title="Select all expired cards on this page" disabled></th><th style="width:145px;">Issue</th><th class="dm1752-health-client-col">Client</th><th style="min-width:240px;">Payment Method</th><th style="min-width:150px;">Description</th><th style="width:115px;" class="dm1780-sortable-heading sorting" id="dm1780-exp-sort-heading"><button type="button" class="dm1780-sort-button" id="dm1780-sort-expiry" data-direction="none" aria-label="Sort by expiration date" aria-sort="none">Expires</button></th><th style="min-width:135px;">Usage</th><th style="min-width:250px;">Account Inventory</th><th style="min-width:220px;">Affected Items</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $issue = (string) ($row['issue'] ?? '');
        $issueClass = in_array($issue, ['Expired', 'Missing/Deleted', 'Gateway Inactive'], true) ? 'label-danger' : 'label-warning';
        $clientId = (int) ($row['client_id'] ?? 0);
        $clientText = (string) ($row['client_name'] ?? ('Client #' . $clientId));
        $clientMeta = [];
        if ((string) ($row['client_status'] ?? '') !== '') {
            $clientMeta[] = (string) $row['client_status'];
        }
        if ((string) ($row['email'] ?? '') !== '') {
            $clientMeta[] = (string) $row['email'];
        }
        $inventory = is_array($row['account_inventory'] ?? null) ? $row['account_inventory'] : [];
        $productTotal = (int) ($inventory['products_total'] ?? 0);
        $domainTotal = (int) ($inventory['domains_total'] ?? 0);
        $productStatuses = is_array($inventory['products_status'] ?? null) ? $inventory['products_status'] : [];
        $domainStatuses = is_array($inventory['domains_status'] ?? null) ? $inventory['domains_status'] : [];
        $inventoryHtml = '<div class="dm1751-health-inventory"><strong>Products/Services: ' . $productTotal . '</strong><div>'
            . domainmongerpayrouting_health_status_summary($productStatuses)
            . '</div><strong>Domains: ' . $domainTotal . '</strong><div>'
            . domainmongerpayrouting_health_status_summary($domainStatuses)
            . '</div></div>';

        $affected = $row['affected'] ?? [];
        $affectedHtml = '<span class="text-muted">—</span>';
        if (is_array($affected) && $affected) {
            $parts = [];
            foreach (array_slice($affected, 0, 8) as $item) {
                $parts[] = '<a href="' . $esc((string) ($item['url'] ?? '')) . '">' . $esc((string) ($item['label'] ?? 'Item')) . '</a>';
            }
            if (count($affected) > 8) {
                $parts[] = '<span class="text-muted">+' . (count($affected) - 8) . ' more</span>';
            }
            $affectedHtml = implode('<br>', $parts);
        }

        $usage = trim((string) ($row['usage'] ?? '')) ?: 'Unassigned';
        $usageClass = 'dm1753-usage-unassigned';
        if ($usage === 'Default + Assigned') {
            $usageClass = 'dm1753-usage-default-assigned';
        } elseif ($usage === 'Default') {
            $usageClass = 'dm1753-usage-default';
        } elseif ($usage === 'Assigned') {
            $usageClass = 'dm1753-usage-assigned';
        }
        $tags = is_array($row['tags'] ?? null) ? implode(' ', array_map('strval', $row['tags'])) : '';

        $html .= '<tr data-dm1753-health-row data-dm1757-row data-dm1753-tags="' . $esc($tags) . '" data-dm1778-pay-method-id="' . (int) ($row['pay_method_id'] ?? 0) . '" data-dm1778-expiry-end="' . (int) ($row['expiry_end_ts'] ?? 0) . '" data-dm1778-usage="' . $esc($usage) . '" data-dm1783-client-id="' . $clientId . '" data-dm1783-client-label="' . $esc($clientText) . '" data-dm1783-method-label="' . $esc((string) ($row['method'] ?? '')) . '" data-dm1783-expiry-label="' . $esc((string) ($row['expiration'] ?? '—')) . '">';
        $html .= '<td class="dm1783-select-col"><input type="checkbox" data-dm1783-select aria-label="Select payment method ' . (int) ($row['pay_method_id'] ?? 0) . ' for deletion" disabled></td>';
        $html .= '<td><span class="label dm1764-health-issue ' . $issueClass . '"><span class="dm1766-health-issue-text">' . $esc($issue) . '</span></span></td>';
        $html .= '<td class="dm1752-health-client-col"><a href="clientssummary.php?userid=' . $clientId . '"><strong>' . $esc($clientText) . '</strong> (#' . $clientId . ')</a>'
            . ($clientMeta ? '<br><span class="text-muted">' . $esc(implode(' — ', $clientMeta)) . '</span>' : '') . '</td>';
        $html .= '<td>' . $esc((string) ($row['method'] ?? '')) . '<br><span class="text-muted">PM #' . (int) ($row['pay_method_id'] ?? 0) . '</span></td>';
        $html .= '<td>' . $esc((string) ($row['description'] ?? '')) . '</td>';
        $html .= '<td>' . $esc((string) ($row['expiration'] ?? '—')) . '</td>';
        $html .= '<td><span class="dm1753-health-usage ' . $usageClass . '">' . $esc($usage) . '</span></td>';
        $html .= '<td>' . $inventoryHtml . '</td>';
        $html .= '<td>' . $affectedHtml . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div><div class="dm1783-selection-bar"><div class="dm1783-selection-actions"><div class="dm1798-action-group"><span class="dm1798-action-label">Selected Cards:</span><span class="dm1783-selected-count" data-dm1783-selected-count>0 selected</span><button type="button" class="btn btn-primary dm1783-preview-selected" data-dm1783-preview-selected disabled>Preview Selected Deletion</button><form method="post" action="addonmodules.php?module=domainmongerpayrouting&amp;dm1751_tab=health" class="dm1786-delete-test-form" data-dm1786-delete-form><input type="hidden" name="dm1786_health_action" value="delete_expired_batch_test"><input type="hidden" name="dm1786_health_token" value="' . $esc($deleteToken) . '"><input type="hidden" name="dm1786_age_mode" value="" data-dm1786-delete-age><input type="hidden" name="dm1786_custom_date" value="" data-dm1786-delete-custom><span data-dm1786-delete-target-inputs></span><button type="submit" class="btn btn-danger dm1786-delete-test" data-dm1786-delete-test disabled>Delete Selected — 25 Max</button></form></div><div class="dm1798-action-group"><button type="button" class="btn btn-danger dm1790-auto-delete" data-dm1790-auto-delete title="Uses the current filters, search, and sort order; checkboxes are not required." disabled>Delete Up to 1,000 Filtered Results</button></div></div><div class="dm1757-pager"><span class="dm1757-pager-status" data-dm1757-status>' . $esc($initialPagerStatus) . '</span><button type="button" class="btn btn-default" data-dm1757-prev>Previous</button><button type="button" class="btn btn-default" data-dm1757-next>Next</button></div></div><div class="alert alert-warning dm1783-selected-preview" data-dm1783-selected-preview style="display:none;"><strong>Selected Deletion Preview — Dry Run Only</strong><div data-dm1783-selected-preview-text></div><div class="dm1783-selected-preview-meta" data-dm1783-selected-preview-meta></div><ul class="dm1783-selected-preview-list" data-dm1783-selected-preview-list></ul></div></div></div>';
    $html .= <<<'HTML'
<script>
(function(){
    var panel = document.querySelector('[data-dm1753-health-panel]');
    if (!panel || panel.getAttribute('data-dm1753-ready') === '1') { return; }
    panel.setAttribute('data-dm1753-ready', '1');
    var filterSelect = panel.querySelector('[data-dm1753-filter-select]');
    var ageSelect = panel.querySelector('[data-dm1778-expired-age]');
    var customDate = panel.querySelector('[data-dm1781-custom-expiry-date]');
    var ageWrap = ageSelect ? ageSelect.closest('.dm1778-expired-age-wrap') : null;
    var preview = panel.querySelector('[data-dm1778-delete-preview]');
    var previewText = panel.querySelector('[data-dm1778-preview-text]');
    var previewMeta = panel.querySelector('[data-dm1778-preview-meta]');
    var pageSizeSelect = panel.querySelector('[data-dm1757-page-size]');
    var healthSearch = panel.querySelector('[data-dm1757-search]');
    var returnStateKey = 'dm1791_health_return_state';
    var returnState = null;
    try {
        var returnStateRaw = window.sessionStorage.getItem(returnStateKey);
        if (returnStateRaw) { returnState = JSON.parse(returnStateRaw); }
    } catch (e) { returnState = null; }
    if (!returnState || typeof returnState !== 'object') { returnState = null; }
    if (returnState) {
        var preSize = String(returnState.size || '25');
        var preFilter = String(returnState.filter || 'all');
        var preAge = String(returnState.age || '0');
        var validFilters = ['all','expired','expires-soon','assigned-expired','missing-deleted','unassigned-expired','gateway-inactive','invalid-expiration'];
        if (pageSizeSelect && ['25','50','100','all'].indexOf(preSize) !== -1) { pageSizeSelect.value = preSize; }
        if (filterSelect && validFilters.indexOf(preFilter) !== -1) { filterSelect.value = preFilter; }
        if (ageSelect && ['0','1','3','5','custom'].indexOf(preAge) !== -1) { ageSelect.value = preAge; }
        if (customDate && preAge === 'custom' && typeof returnState.custom === 'string') { customDate.value = returnState.custom; }
        if (healthSearch && typeof returnState.search === 'string') { healthSearch.value = returnState.search; }
    }
    var rows = panel.querySelectorAll('[data-dm1753-health-row]');
    var activeFilter = 'all';
    var activeAgeYears = 0;
    var activeAgeMode = 'any';
    var activeCutoff = 0;
    var activeCustomLabel = '';
    var pager = window.dm1757InitPager ? window.dm1757InitPager(panel) : null;
    var expirySortHeading = panel.querySelector('#dm1780-exp-sort-heading');
    var expirySortButton = panel.querySelector('#dm1780-sort-expiry');
    var selectAll = panel.querySelector('[data-dm1783-select-all]');
    var selectBoxes = Array.prototype.slice.call(rows).map(function(row){ return row.querySelector('[data-dm1783-select]'); }).filter(Boolean);
    var selectedCounts = Array.prototype.slice.call(panel.querySelectorAll('[data-dm1783-selected-count]'));
    var previewSelectedButtons = Array.prototype.slice.call(panel.querySelectorAll('[data-dm1783-preview-selected]'));
    var deleteTopButtons = Array.prototype.slice.call(panel.querySelectorAll('[data-dm1789-delete-top]'));
    var selectedPreview = panel.querySelector('[data-dm1783-selected-preview]');
    var selectedPreviewText = panel.querySelector('[data-dm1783-selected-preview-text]');
    var selectedPreviewMeta = panel.querySelector('[data-dm1783-selected-preview-meta]');
    var selectedPreviewList = panel.querySelector('[data-dm1783-selected-preview-list]');
    var deleteTestForm = panel.querySelector('[data-dm1786-delete-form]');
    var deleteTestButton = panel.querySelector('[data-dm1786-delete-test]');
    var deleteTargetInputs = panel.querySelector('[data-dm1786-delete-target-inputs]');
    var deleteAgeInput = panel.querySelector('[data-dm1786-delete-age]');
    var deleteCustomInput = panel.querySelector('[data-dm1786-delete-custom]');
    var deleteTokenInput = deleteTestForm ? deleteTestForm.querySelector('input[name="dm1786_health_token"]') : null;
    var autoDeleteButtons = Array.prototype.slice.call(panel.querySelectorAll('[data-dm1790-auto-delete]'));
    var autoProgress = panel.querySelector('[data-dm1790-auto-progress]');
    var autoProgressText = panel.querySelector('[data-dm1790-auto-progress-text]');
    var autoRunning = false;

    function saveReturnTableState() {
        try {
            var sizeValue = pageSizeSelect ? String(pageSizeSelect.value || '25') : '25';
            var sortDirection = expirySortButton ? String(expirySortButton.getAttribute('data-direction') || 'none') : 'none';
            var filterValue = filterSelect ? String(filterSelect.value || activeFilter || 'all') : (activeFilter || 'all');
            var ageValue = ageSelect ? String(ageSelect.value || '0') : '0';
            var customValue = customDate ? String(customDate.value || '') : '';
            var searchValue = healthSearch ? String(healthSearch.value || '') : '';
            if (['25','50','100','all'].indexOf(sizeValue) === -1) { sizeValue = '25'; }
            if (['asc','desc','none'].indexOf(sortDirection) === -1) { sortDirection = 'none'; }
            if (['all','expired','expires-soon','assigned-expired','missing-deleted','unassigned-expired','gateway-inactive','invalid-expiration'].indexOf(filterValue) === -1) { filterValue = 'all'; }
            if (['0','1','3','5','custom'].indexOf(ageValue) === -1) { ageValue = '0'; }
            if (ageValue !== 'custom') { customValue = ''; }
            window.sessionStorage.setItem(returnStateKey, JSON.stringify({size:sizeValue, sort:sortDirection, filter:filterValue, age:ageValue, custom:customValue, search:searchValue}));
        } catch (e) {}
    }

    function finishReturnStateRestore() {
        if (!returnState) {
            if (panel.getAttribute('data-dm1792-restoring') === '1') {
                panel.style.visibility = '';
                panel.removeAttribute('data-dm1792-restoring');
            }
            return;
        }
        var sortDirection = String(returnState.sort || 'none');
        var filterValue = filterSelect ? String(filterSelect.value || 'all') : 'all';
        markFilter(filterValue);
        if (sortDirection === 'asc' || sortDirection === 'desc') {
            sortExpiry(sortDirection);
        } else if (pager) {
            pager.reset();
        }
        try { window.sessionStorage.removeItem(returnStateKey); } catch (e) {}
        returnState = null;
        panel.style.visibility = '';
        panel.removeAttribute('data-dm1792-restoring');
    }

    function sortExpiry(direction) {
        if (!pager || typeof pager.sortRows !== 'function') { return; }
        var multiplier = direction === 'desc' ? -1 : 1;
        pager.sortRows(function(a, b){
            var aTs = parseInt(a.getAttribute('data-dm1778-expiry-end') || '0', 10) || 0;
            var bTs = parseInt(b.getAttribute('data-dm1778-expiry-end') || '0', 10) || 0;
            if (aTs <= 0 && bTs <= 0) { return 0; }
            if (aTs <= 0) { return 1; }
            if (bTs <= 0) { return -1; }
            if (aTs === bTs) { return 0; }
            return (aTs < bTs ? -1 : 1) * multiplier;
        });
        if (expirySortHeading) {
            expirySortHeading.classList.remove('sorting', 'sorting_asc', 'sorting_desc');
            expirySortHeading.classList.add(direction === 'asc' ? 'sorting_asc' : 'sorting_desc');
        }
        if (expirySortButton) {
            expirySortButton.setAttribute('data-direction', direction);
            expirySortButton.setAttribute('aria-sort', direction === 'asc' ? 'ascending' : 'descending');
        }
    }

    function rowForBox(box) {
        return box && box.closest ? box.closest('[data-dm1753-health-row]') : null;
    }

    function pmIdForRow(row) {
        return row ? (parseInt(row.getAttribute('data-dm1778-pay-method-id') || '0', 10) || 0) : 0;
    }

    function matchingEligibleRows() {
        if (!activeCutoff || activeAgeMode === 'any' || !pager) { return []; }
        return pager.getMatchingRows().filter(function(row){ return pmIdForRow(row) > 0; });
    }

    function visibleEligibleRows() {
        if (!activeCutoff || activeAgeMode === 'any' || !pager) { return []; }
        return pager.getVisibleRows().filter(function(row){ return pmIdForRow(row) > 0; });
    }

    function matchingUniqueRows(limit) {
        var byId = {}, out = [];
        matchingEligibleRows().forEach(function(row){
            var pmId = pmIdForRow(row);
            if (pmId <= 0 || byId[pmId]) { return; }
            byId[pmId] = true;
            out.push(row);
        });
        if (limit && out.length > limit) { out = out.slice(0, limit); }
        return out;
    }

    function selectedUniqueRows() {
        var byId = {};
        selectBoxes.forEach(function(box){
            if (!box.checked) { return; }
            var row = rowForBox(box), pmId = pmIdForRow(row);
            if (pmId > 0 && !byId[pmId]) { byId[pmId] = row; }
        });
        return Object.keys(byId).map(function(id){ return byId[id]; });
    }

    function setPmSelected(pmId, checked) {
        if (pmId <= 0) { return; }
        selectBoxes.forEach(function(box){
            var row = rowForBox(box);
            if (pmIdForRow(row) === pmId && !box.disabled) { box.checked = !!checked; }
        });
    }

    function rebuildDeleteTargets(selectedRows) {
        if (!deleteTargetInputs) { return; }
        deleteTargetInputs.innerHTML = '';
        if (!selectedRows || selectedRows.length < 1 || selectedRows.length > 25) { return; }
        selectedRows.forEach(function(row){
            var pmId = pmIdForRow(row);
            var clientId = parseInt(row.getAttribute('data-dm1783-client-id') || '0', 10) || 0;
            if (pmId <= 0 || clientId <= 0) { return; }
            var pmInput = document.createElement('input');
            pmInput.type = 'hidden';
            pmInput.name = 'dm1786_pm_ids[]';
            pmInput.value = String(pmId);
            deleteTargetInputs.appendChild(pmInput);
            var clientInput = document.createElement('input');
            clientInput.type = 'hidden';
            clientInput.name = 'dm1786_client_ids[]';
            clientInput.value = String(clientId);
            deleteTargetInputs.appendChild(clientInput);
        });
    }

    function syncSelectionState() {
        var eligibleRows = matchingEligibleRows();
        var eligibleIds = {};
        eligibleRows.forEach(function(row){ var id = pmIdForRow(row); if (id > 0) { eligibleIds[id] = true; } });

        selectBoxes.forEach(function(box){
            var row = rowForBox(box), pmId = pmIdForRow(row);
            var eligible = !!eligibleIds[pmId];
            box.disabled = !eligible;
            if (!eligible) { box.checked = false; }
        });

        var selectedRows = selectedUniqueRows();
        selectedCounts.forEach(function(el){ el.textContent = selectedRows.length.toLocaleString() + ' selected'; });
        previewSelectedButtons.forEach(function(btn){ btn.disabled = selectedRows.length === 0; });
        var deleteDisabled = selectedRows.length < 1 || selectedRows.length > 25;
        if (deleteTestButton) { deleteTestButton.disabled = deleteDisabled || autoRunning; }
        deleteTopButtons.forEach(function(btn){ btn.disabled = deleteDisabled || autoRunning; });
        var autoMatchCount = matchingUniqueRows(1).length;
        autoDeleteButtons.forEach(function(btn){ btn.disabled = autoRunning || autoMatchCount < 1; });
        rebuildDeleteTargets(selectedRows);
        if (deleteAgeInput) { deleteAgeInput.value = selectedRows.length >= 1 && selectedRows.length <= 25 ? (activeAgeMode || '') : ''; }
        if (deleteCustomInput) { deleteCustomInput.value = selectedRows.length >= 1 && selectedRows.length <= 25 && activeAgeMode === 'custom' && customDate ? (customDate.value || '') : ''; }

        if (selectAll) {
            var pageIds = {};
            visibleEligibleRows().forEach(function(row){
                var id = pmIdForRow(row);
                if (id > 0) { pageIds[id] = true; }
            });
            var selectedIds = {};
            selectedRows.forEach(function(row){
                var id = pmIdForRow(row);
                if (id > 0) { selectedIds[id] = true; }
            });
            var pageUnique = Object.keys(pageIds).length;
            var pageSelected = Object.keys(pageIds).filter(function(id){ return !!selectedIds[id]; }).length;
            selectAll.disabled = pageUnique === 0;
            selectAll.checked = pageUnique > 0 && pageSelected === pageUnique;
            selectAll.indeterminate = pageSelected > 0 && pageSelected < pageUnique;
        }
    }

    function setAutoProgress(message) {
        if (autoProgressText) { autoProgressText.textContent = message || ''; }
        if (autoProgress) { autoProgress.classList.toggle('is-active', !!message); }
    }

    function responseBatchResult(html) {
        var parsed = new DOMParser().parseFromString(html, 'text/html');
        var nextTokenNode = parsed.querySelector('input[name="dm1786_health_token"]');
        var nextToken = nextTokenNode ? (nextTokenNode.value || '') : '';
        var alerts = Array.prototype.slice.call(parsed.querySelectorAll('.alert'));
        var batchAlert = null;
        alerts.some(function(node){
            var txt = (node.textContent || '').trim();
            if (txt.indexOf('Expired-card batch deletion succeeded:') === 0
                || txt.indexOf('Batch deletion stopped after ') === 0
                || txt.indexOf('Batch prevalidation failed:') === 0
                || txt.indexOf('The expired-card batch deletion') === 0
                || txt.indexOf('The batch selection submission') === 0
                || txt.indexOf('Select between 1 and 25 unique expired credit cards') === 0
                || txt.indexOf('Choose a valid 1-year, 3-year, 5-year, or Custom expiration cutoff') === 0
                || txt.indexOf('WHMCS Local API is unavailable') === 0) {
                batchAlert = node;
                return true;
            }
            return false;
        });
        var textValue = batchAlert ? (batchAlert.textContent || '').trim() : 'The server response did not contain an expired-card batch result.';
        var success = !!(batchAlert && batchAlert.classList.contains('alert-success') && textValue.indexOf('Expired-card batch deletion succeeded:') === 0);
        return {success:success, message:textValue, nextToken:nextToken, html:html};
    }

    function postAutoBatch(batchRows, token) {
        var data = new FormData();
        data.append('dm1786_health_action', 'delete_expired_batch_test');
        data.append('dm1786_health_token', token || '');
        data.append('dm1786_age_mode', activeAgeMode || '');
        data.append('dm1786_custom_date', activeAgeMode === 'custom' && customDate ? (customDate.value || '') : '');
        batchRows.forEach(function(row){
            data.append('dm1786_pm_ids[]', String(pmIdForRow(row)));
            data.append('dm1786_client_ids[]', row.getAttribute('data-dm1783-client-id') || '0');
        });
        return fetch(deleteTestForm.action, {method:'POST', body:data, credentials:'same-origin'})
            .then(function(response){
                if (!response.ok) { throw new Error('HTTP ' + response.status + ' from the batch deletion request.'); }
                return response.text();
            })
            .then(responseBatchResult);
    }

    function refreshHealthPanelFromHtml(html) {
        if (!html) { throw new Error('The server did not return refreshed Payment Method Health markup.'); }
        // Save the CURRENT browser state immediately before the authoritative
        // server panel is swapped in. The fresh Health initializer consumes
        // this one-time state synchronously and clears it again.
        saveReturnTableState();
        var parsed = new DOMParser().parseFromString(html, 'text/html');
        var freshPanelSource = parsed.querySelector('[data-dm1753-health-panel]');
        var healthScriptSource = null;
        Array.prototype.slice.call(parsed.querySelectorAll('script')).some(function(node){
            var code = node.textContent || '';
            if (code.indexOf("var panel = document.querySelector('[data-dm1753-health-panel]')") !== -1
                && code.indexOf("panel.setAttribute('data-dm1753-ready', '1')") !== -1) {
                healthScriptSource = node;
                return true;
            }
            return false;
        });
        if (!freshPanelSource || !healthScriptSource) {
            throw new Error('The refreshed server response did not contain a complete Payment Method Health panel.');
        }
        var freshPanel = document.importNode ? document.importNode(freshPanelSource, true) : freshPanelSource.cloneNode(true);
        freshPanel.style.visibility = 'hidden';
        freshPanel.setAttribute('data-dm1792-restoring', '1');
        if (!panel.parentNode) { throw new Error('The current Payment Method Health panel is no longer attached to the page.'); }
        panel.parentNode.replaceChild(freshPanel, panel);

        // The parsed response's inline script does not execute when imported.
        // Execute only the Health initializer after the new panel is in place.
        // It restores filter/search/page-size/sort from sessionStorage before
        // clearing visibility, so the browser paints the final state once.
        var runner = document.createElement('script');
        runner.type = 'text/javascript';
        runner.text = healthScriptSource.textContent || '';
        (document.body || document.documentElement).appendChild(runner);
        if (runner.parentNode) { runner.parentNode.removeChild(runner); }
        if (freshPanel.getAttribute('data-dm1753-ready') !== '1') {
            freshPanel.style.visibility = '';
            throw new Error('The refreshed Payment Method Health panel could not be initialized.');
        }
        return true;
    }

    function runAutoDeleteTest() {
        if (autoRunning || !deleteTestForm || !deleteTokenInput || !activeCutoff || activeAgeMode === 'any') { return; }
        var targets = matchingUniqueRows(1000);
        if (targets.length < 1) {
            window.alert('There are no matching expired credit cards for the current filters and cutoff.');
            return;
        }
        var cutoffText = activeAgeMode === 'custom'
            ? 'expired before ' + (activeCustomLabel || (customDate ? customDate.value : 'the selected date'))
            : 'expired more than ' + activeAgeYears + ' ' + (activeAgeYears === 1 ? 'year' : 'years') + ' ago';
        var confirmText = 'DELETE UP TO 1,000 FILTERED EXPIRED CARDS\n\n'
            + 'This will permanently delete the first ' + targets.length + ' unique expired cards in the CURRENT FILTERED/SORTED RESULTS (' + cutoffText + ').\n\n'
            + 'They will be processed as server-validated batches of no more than 25 cards. Processing stops immediately if any batch fails.\n\n'
            + 'This cannot be undone. Continue?';
        if (!window.confirm(confirmText)) { return; }
        saveReturnTableState();

        autoRunning = true;
        syncSelectionState();
        var batches = [];
        for (var i = 0; i < targets.length; i += 25) { batches.push(targets.slice(i, i + 25)); }
        var token = deleteTokenInput.value || '';
        var deletedTotal = 0;
        var lastResponseHtml = '';

        function runBatch(index) {
            if (index >= batches.length) {
                setAutoProgress('Completed: ' + deletedTotal + ' of ' + targets.length + ' cards deleted successfully in ' + batches.length + ' server-validated batch' + (batches.length === 1 ? '' : 'es') + '. Updating results…');
                try {
                    refreshHealthPanelFromHtml(lastResponseHtml);
                    window.alert('Automated expired-card deletion succeeded: ' + deletedTotal + ' of ' + targets.length + ' filtered expired cards were deleted in ' + batches.length + ' server-validated batches. Payment Method Health was updated in place.');
                } catch (refreshError) {
                    window.alert('The deletion succeeded, but the in-page Health refresh failed. The page will reload safely.\n\n' + refreshError.message);
                    window.location.reload();
                }
                return;
            }
            var batch = batches[index];
            setAutoProgress('Batch ' + (index + 1) + ' of ' + batches.length + ': deleting ' + batch.length + ' cards… ' + deletedTotal + ' deleted so far.');
            postAutoBatch(batch, token).then(function(result){
                if (result.nextToken) {
                    token = result.nextToken;
                    deleteTokenInput.value = result.nextToken;
                }
                if (!result.success) {
                    throw new Error(result.message || 'The batch did not return a success result.');
                }
                lastResponseHtml = result.html || lastResponseHtml;
                deletedTotal += batch.length;
                runBatch(index + 1);
            }).catch(function(error){
                autoRunning = false;
                syncSelectionState();
                setAutoProgress('Stopped after ' + deletedTotal + ' of ' + targets.length + ' cards. ' + error.message);
                window.alert('Automated expired-card deletion stopped after ' + deletedTotal + ' of ' + targets.length + ' cards.\n\n' + error.message + '\n\nDo not retry blindly; review the Payment Method Health results and Activity Log first.');
            });
        }
        runBatch(0);
    }

    function clearSelectedPreview() {
        if (selectedPreview) { selectedPreview.style.display = 'none'; }
        if (selectedPreviewList) { selectedPreviewList.innerHTML = ''; }
    }

    function renderSelectedPreview() {
        if (!selectedPreview) { return; }
        var selectedRows = selectedUniqueRows();
        if (!selectedRows.length) {
            clearSelectedPreview();
            return;
        }
        var usageCounts = {'Default':0,'Assigned':0,'Default + Assigned':0,'Unassigned':0};
        selectedRows.forEach(function(row){
            var usage = row.getAttribute('data-dm1778-usage') || 'Unassigned';
            if (typeof usageCounts[usage] === 'undefined') { usageCounts[usage] = 0; }
            usageCounts[usage]++;
        });
        if (selectedPreviewText) {
            selectedPreviewText.textContent = selectedRows.length.toLocaleString() + ' unique expired credit card' + (selectedRows.length === 1 ? '' : 's') + ' would be submitted for deletion if deletion were active. No payment methods were deleted.';
        }
        if (selectedPreviewMeta) {
            selectedPreviewMeta.textContent = 'Default: ' + (usageCounts['Default'] || 0).toLocaleString() + ' · Assigned: ' + (usageCounts['Assigned'] || 0).toLocaleString() + ' · Default + Assigned: ' + (usageCounts['Default + Assigned'] || 0).toLocaleString() + ' · Unassigned: ' + (usageCounts['Unassigned'] || 0).toLocaleString();
        }
        if (selectedPreviewList) {
            selectedPreviewList.innerHTML = '';
            var limit = Math.min(selectedRows.length, 50);
            for (var i = 0; i < limit; i++) {
                var row = selectedRows[i];
                var li = document.createElement('li');
                var pmId = pmIdForRow(row);
                var clientId = row.getAttribute('data-dm1783-client-id') || '0';
                var clientLabel = row.getAttribute('data-dm1783-client-label') || ('Client #' + clientId);
                var methodLabel = row.getAttribute('data-dm1783-method-label') || ('PM #' + pmId);
                var expiryLabel = row.getAttribute('data-dm1783-expiry-label') || '—';
                var usageLabel = row.getAttribute('data-dm1778-usage') || 'Unassigned';
                li.textContent = 'PM #' + pmId + ' · Client #' + clientId + ' ' + clientLabel + ' · ' + methodLabel + ' · Expires ' + expiryLabel + ' · ' + usageLabel;
                selectedPreviewList.appendChild(li);
            }
            if (selectedRows.length > limit) {
                var more = document.createElement('li');
                more.textContent = '+' + (selectedRows.length - limit).toLocaleString() + ' more selected payment methods';
                selectedPreviewList.appendChild(more);
            }
        }
        selectedPreview.style.display = '';
    }

    function customDateCutoff() {
        if (!customDate || !customDate.value) { return 0; }
        var parts = customDate.value.split('-');
        if (parts.length !== 3) { return 0; }
        var year = parseInt(parts[0], 10), month = parseInt(parts[1], 10), day = parseInt(parts[2], 10);
        if (!year || !month || !day) { return 0; }
        return Math.floor(new Date(year, month - 1, day, 0, 0, 0, 0).getTime() / 1000);
    }

    function selectedCutoff() {
        if (!ageSelect || !ageSelect.options || ageSelect.selectedIndex < 0) { return 0; }
        if (ageSelect.value === 'custom') { return customDateCutoff(); }
        return parseInt(ageSelect.options[ageSelect.selectedIndex].getAttribute('data-cutoff') || '0', 10) || 0;
    }

    function formatCustomDate() {
        if (!customDate || !customDate.value) { return ''; }
        var parts = customDate.value.split('-');
        if (parts.length !== 3) { return customDate.value; }
        var d = new Date(parseInt(parts[0],10), parseInt(parts[1],10)-1, parseInt(parts[2],10));
        if (isNaN(d.getTime())) { return customDate.value; }
        try { return d.toLocaleDateString(undefined, {year:'numeric', month:'long', day:'numeric'}); } catch (e) { return customDate.value; }
    }

    function updatePreview(matches) {
        if (!preview) { return; }
        if (!activeCutoff || activeAgeMode === 'any') {
            preview.style.display = 'none';
            return;
        }
        matches = matches || (pager ? pager.getMatchingRows() : []);
        var seen = {};
        var usageCounts = {'Default':0,'Assigned':0,'Default + Assigned':0,'Unassigned':0};
        var unique = 0;
        for (var i = 0; i < matches.length; i++) {
            var pmId = parseInt(matches[i].getAttribute('data-dm1778-pay-method-id') || '0', 10) || 0;
            if (pmId <= 0 || seen[pmId]) { continue; }
            seen[pmId] = true;
            unique++;
            var usage = matches[i].getAttribute('data-dm1778-usage') || 'Unassigned';
            if (typeof usageCounts[usage] === 'undefined') { usageCounts[usage] = 0; }
            usageCounts[usage]++;
        }
        if (previewText) {
            var matchText = activeAgeMode === 'custom'
                ? 'expired before ' + (activeCustomLabel || 'the selected date')
                : 'expired more than ' + activeAgeYears + ' ' + (activeAgeYears === 1 ? 'year' : 'years') + ' ago';
            previewText.textContent = unique.toLocaleString() + ' unique expired credit card' + (unique === 1 ? '' : 's') + ' match “' + matchText + '” and would be submitted for deletion if deletion were enabled. No payment methods were deleted.';
        }
        if (previewMeta) {
            previewMeta.textContent = 'Default: ' + (usageCounts['Default'] || 0).toLocaleString() + ' · Assigned: ' + (usageCounts['Assigned'] || 0).toLocaleString() + ' · Default + Assigned: ' + (usageCounts['Default + Assigned'] || 0).toLocaleString() + ' · Unassigned: ' + (usageCounts['Unassigned'] || 0).toLocaleString();
        }
        preview.style.display = '';
    }

    function markFilter(filter) {
        activeFilter = filter || 'all';
        activeAgeMode = ageSelect ? (ageSelect.value || '0') : '0';
        activeAgeYears = activeAgeMode === 'custom' ? 0 : (parseInt(activeAgeMode || '0', 10) || 0);
        activeCustomLabel = activeAgeMode === 'custom' ? formatCustomDate() : '';
        activeCutoff = selectedCutoff();
        if (ageWrap) { ageWrap.classList.toggle('dm1781-custom-active', activeAgeMode === 'custom'); }
        for (var i = 0; i < rows.length; i++) {
            var tags = (rows[i].getAttribute('data-dm1753-tags') || '').split(/\s+/);
            var issueMatch = activeFilter === 'all' || tags.indexOf(activeFilter) !== -1;
            var ageMatch = true;
            if (activeCutoff) {
                var expiryEnd = parseInt(rows[i].getAttribute('data-dm1778-expiry-end') || '0', 10) || 0;
                ageMatch = tags.indexOf('expired') !== -1 && expiryEnd > 0 && expiryEnd < activeCutoff;
            } else if (activeAgeMode === 'custom') {
                ageMatch = false;
            }
            rows[i].setAttribute('data-dm1757-filter-hidden', issueMatch && ageMatch ? '0' : '1');
        }
        if (filterSelect && filterSelect.value !== activeFilter) {
            filterSelect.value = activeFilter;
        }
        if (pager) { pager.reset(); }
        syncSelectionState();
        clearSelectedPreview();
    }

    if (expirySortHeading) {
        expirySortHeading.addEventListener('click', function(event){
            if (event && event.target && event.target.closest && !event.target.closest('#dm1780-sort-expiry') && event.target !== expirySortHeading) { return; }
            var current = expirySortButton ? (expirySortButton.getAttribute('data-direction') || 'none') : 'none';
            sortExpiry(current === 'asc' ? 'desc' : 'asc');
        });
    }

    if (filterSelect) {
        filterSelect.addEventListener('change', function(){
            markFilter(this.value || 'all');
        });
    }
    if (ageSelect) {
        ageSelect.addEventListener('change', function(){
            markFilter(activeFilter);
            if (this.value === 'custom' && customDate) {
                try {
                    // Patch 1782: markFilter() has just changed this input from display:none.
                    // Force style/layout now so the native picker uses its real on-page anchor.
                    customDate.getBoundingClientRect();
                    customDate.focus({preventScroll:true});
                    if (typeof customDate.showPicker === 'function') { customDate.showPicker(); }
                    else { customDate.click(); }
                } catch (e) { customDate.focus(); }
            }
        });
    }
    if (customDate) {
        customDate.addEventListener('change', function(){
            if (ageSelect && ageSelect.value !== 'custom') { ageSelect.value = 'custom'; }
            markFilter(activeFilter);
        });
    }
    selectBoxes.forEach(function(box){
        box.addEventListener('change', function(){
            var row = rowForBox(this), pmId = pmIdForRow(row);
            setPmSelected(pmId, this.checked);
            syncSelectionState();
            clearSelectedPreview();
        });
    });
    if (selectAll) {
        selectAll.addEventListener('change', function(){
            var checked = this.checked;
            var pageIds = {};
            visibleEligibleRows().forEach(function(row){
                var pmId = pmIdForRow(row);
                if (pmId > 0) { pageIds[pmId] = true; }
            });
            Object.keys(pageIds).forEach(function(pmId){ setPmSelected(parseInt(pmId, 10) || 0, checked); });
            syncSelectionState();
            clearSelectedPreview();
        });
    }
    previewSelectedButtons.forEach(function(button){
        button.addEventListener('click', function(){ renderSelectedPreview(); });
    });
    autoDeleteButtons.forEach(function(button){
        button.addEventListener('click', function(){ if (!button.disabled) { runAutoDeleteTest(); } });
    });
    deleteTopButtons.forEach(function(button){
        button.addEventListener('click', function(){
            if (!deleteTestForm || !deleteTestButton || button.disabled) { return; }
            if (typeof deleteTestForm.requestSubmit === 'function') { deleteTestForm.requestSubmit(deleteTestButton); }
            else { deleteTestButton.click(); }
        });
    });
    if (deleteTestForm) {
        deleteTestForm.addEventListener('submit', function(event){
            var selectedRows = selectedUniqueRows();
            if (selectedRows.length < 1 || selectedRows.length > 25) {
                event.preventDefault();
                syncSelectionState();
                window.alert('Select between 1 and 25 unique expired cards.');
                return;
            }
            // Rebuild conventional hidden array fields immediately before submit.
            rebuildDeleteTargets(selectedRows);
            if (deleteAgeInput) { deleteAgeInput.value = activeAgeMode || ''; }
            if (deleteCustomInput) { deleteCustomInput.value = activeAgeMode === 'custom' && customDate ? (customDate.value || '') : ''; }
            var lines = selectedRows.map(function(row){
                var pmId = pmIdForRow(row);
                var clientId = row.getAttribute('data-dm1783-client-id') || '0';
                var expiryLabel = row.getAttribute('data-dm1783-expiry-label') || '—';
                var usageLabel = row.getAttribute('data-dm1778-usage') || 'Unassigned';
                return 'PM #' + pmId + ' · Client #' + clientId + ' · Expires ' + expiryLabel + ' · ' + usageLabel;
            });
            var message = 'DELETE SELECTED — ' + selectedRows.length + ' card' + (selectedRows.length === 1 ? '' : 's') + ' will be permanently deleted.\n\n'
                + lines.join('\n')
                + '\n\nEvery card will be revalidated on the server before deletion begins. Processing stops on the first deletion/verification failure.\n\nThis cannot be undone. Delete the selected Payment Methods?';
            if (!window.confirm(message)) {
                event.preventDefault();
                return;
            }

            // Patch 1794: keep the WHMCS admin shell/tabs in place. Submit the
            // already-proven 1-25 card server action via fetch, then replace only
            // the Health panel with the authoritative server-rendered result.
            event.preventDefault();
            saveReturnTableState();
            autoRunning = true;
            syncSelectionState();
            setAutoProgress('Deleting ' + selectedRows.length + ' selected card' + (selectedRows.length === 1 ? '' : 's') + ' in one server-validated batch…');
            var token = deleteTokenInput ? (deleteTokenInput.value || '') : '';
            postAutoBatch(selectedRows, token).then(function(result){
                if (result.nextToken && deleteTokenInput) { deleteTokenInput.value = result.nextToken; }
                if (!result.success) { throw new Error(result.message || 'The batch did not return a success result.'); }
                setAutoProgress('Deletion succeeded. Updating Payment Method Health…');
                refreshHealthPanelFromHtml(result.html || '');
                window.alert(result.message + '\n\nPayment Method Health was updated in place.');
            }).catch(function(error){
                autoRunning = false;
                syncSelectionState();
                setAutoProgress('Deletion request stopped. ' + error.message);
                window.alert('Expired-card deletion did not complete cleanly.\n\n' + error.message + '\n\nReview Payment Method Health and the Activity Log before retrying.');
            });
        });
    }
    panel.addEventListener('dm1757:render', function(event){
        updatePreview(event && event.detail ? event.detail.matches : null);
        syncSelectionState();
    });
    if (returnState) {
        finishReturnStateRestore();
    } else {
        markFilter('all');
        panel.style.visibility = '';
        panel.removeAttribute('data-dm1792-restoring');
    }
}());
</script>
HTML;
    return $html;
}

/**
 * Patch 1746: shared bulk Payment Method assignment UI for Admin and Client Area.
 * This is assignment-only. All validation, Separate Invoices handling, and
 * Unpaid-invoice reconciliation remain authoritative in dm1723_save_assignment().
 */
function domainmongerpayrouting_load_routing_helpers(): bool
{
    if (function_exists('dm1723_save_assignment') && function_exists('dm1723_paymethods')) {
        return true;
    }

    $hook = dirname(__DIR__, 3) . '/includes/hooks/domainmonger_client_item_payment_method_assignment_1723.php';
    if (is_file($hook)) {
        require_once $hook;
    }

    return function_exists('dm1723_save_assignment') && function_exists('dm1723_paymethods');
}

function domainmongerpayrouting_bulk_token(string $scope, int $clientId): string
{
    $scope = preg_replace('/[^a-z0-9_\-]+/i', '', $scope);
    $key = 'dm1746_bulk_token_' . $scope . '_' . max(0, $clientId);
    if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
        try {
            $_SESSION[$key] = bin2hex(random_bytes(24));
        } catch (Throwable $e) {
            $_SESSION[$key] = hash('sha256', session_id() . '|' . microtime(true) . '|dm1746|' . $scope . '|' . $clientId);
        }
    }
    return (string) $_SESSION[$key];
}

function domainmongerpayrouting_bulk_client_name(int $clientId): string
{
    $label = 'Client #' . $clientId;
    try {
        $client = Capsule::table('tblclients')->where('id', $clientId)->first(['firstname', 'lastname', 'companyname']);
        if (!$client) {
            return $label;
        }
        $person = trim((string) ($client->firstname ?? '') . ' ' . (string) ($client->lastname ?? ''));
        $company = trim((string) ($client->companyname ?? ''));
        if ($company !== '') {
            return $label . ' — ' . $company . ($person !== '' ? ' (' . $person . ')' : '');
        }
        if ($person !== '') {
            return $label . ' — ' . $person;
        }
    } catch (Throwable $e) {
    }
    return $label;
}

function domainmongerpayrouting_bulk_methods(int $clientId): array
{
    if (!domainmongerpayrouting_load_routing_helpers()) {
        return [];
    }
    $methods = [];
    try {
        foreach (dm1723_paymethods($clientId) as $id => $method) {
            $id = (int) $id;
            if ($id <= 0) {
                continue;
            }
            $label = function_exists('dm1738_paymethod_dropdown_label')
                ? dm1738_paymethod_dropdown_label($method)
                : (string) ($method['label'] ?? ('Pay Method #' . $id));
            if (!empty($method['is_default'])) {
                $label .= ' (Account Default)';
            }
            $methods[$id] = [
                'id' => $id,
                'label' => $label,
                'is_default' => !empty($method['is_default']),
            ];
        }
    } catch (Throwable $e) {
        return [];
    }
    return $methods;
}

function domainmongerpayrouting_bulk_items(int $clientId): array
{
    if ($clientId <= 0 || !domainmongerpayrouting_load_routing_helpers()) {
        return [];
    }

    $methods = domainmongerpayrouting_bulk_methods($clientId);
    $out = [];

    try {
        $services = Capsule::table('tblhosting')
            ->where('userid', $clientId)
            ->orderBy('domainstatus', 'asc')
            ->orderBy('nextduedate', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'packageid', 'domain', 'domainstatus', 'nextduedate']);

        $packageIds = [];
        foreach ($services ?: [] as $service) {
            $pid = (int) ($service->packageid ?? 0);
            if ($pid > 0) {
                $packageIds[$pid] = true;
            }
        }
        $productNames = [];
        if ($packageIds) {
            foreach (Capsule::table('tblproducts')->whereIn('id', array_keys($packageIds))->get(['id', 'name']) ?: [] as $product) {
                $productNames[(int) ($product->id ?? 0)] = trim((string) ($product->name ?? ''));
            }
        }

        foreach ($services ?: [] as $service) {
            $id = (int) ($service->id ?? 0);
            if ($id <= 0) {
                continue;
            }
            $pid = (int) ($service->packageid ?? 0);
            $product = $productNames[$pid] ?? 'Product/Service';
            $domain = trim((string) ($service->domain ?? ''));
            $itemLabel = $product . ($domain !== '' ? ' — ' . $domain : '');
            $assignedId = function_exists('dm1723_cleanup_stale_assignment')
                ? (int) dm1723_cleanup_stale_assignment($clientId, 'service', $id)
                : (int) dm1723_get_assignment($clientId, 'service', $id);
            $currentLabel = $assignedId > 0
                ? ($methods[$assignedId]['label'] ?? ('Pay Method #' . $assignedId))
                : 'Account Default / Existing WHMCS Setting';
            $out[] = [
                'key' => 'service:' . $id,
                'type' => 'service',
                'type_label' => 'Product',
                'id' => $id,
                'label' => $itemLabel,
                'status' => trim((string) ($service->domainstatus ?? '')),
                'next_due_date' => (string) ($service->nextduedate ?? ''),
                'pay_method_id' => $assignedId,
                'current_label' => $currentLabel,
            ];
        }

        $domains = Capsule::table('tbldomains')
            ->where('userid', $clientId)
            ->orderBy('status', 'asc')
            ->orderBy('nextduedate', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'domain', 'status', 'nextduedate']);
        foreach ($domains ?: [] as $domain) {
            $id = (int) ($domain->id ?? 0);
            if ($id <= 0) {
                continue;
            }
            $assignedId = function_exists('dm1723_cleanup_stale_assignment')
                ? (int) dm1723_cleanup_stale_assignment($clientId, 'domain', $id)
                : (int) dm1723_get_assignment($clientId, 'domain', $id);
            $currentLabel = $assignedId > 0
                ? ($methods[$assignedId]['label'] ?? ('Pay Method #' . $assignedId))
                : 'Account Default / Existing WHMCS Setting';
            $out[] = [
                'key' => 'domain:' . $id,
                'type' => 'domain',
                'type_label' => 'Domain',
                'id' => $id,
                'label' => trim((string) ($domain->domain ?? '')) ?: 'Domain',
                'status' => trim((string) ($domain->status ?? '')),
                'next_due_date' => (string) ($domain->nextduedate ?? ''),
                'pay_method_id' => $assignedId,
                'current_label' => $currentLabel,
            ];
        }
    } catch (Throwable $e) {
        if (function_exists('logActivity')) {
            logActivity('DomainMonger Payment Routing [BULK 1746] Could not build item list for Client #' . $clientId . ': ' . $e->getMessage());
        }
        return [];
    }

    return $out;
}

function domainmongerpayrouting_bulk_parse_items($raw): array
{
    if (!is_array($raw)) {
        $raw = [$raw];
    }
    $items = [];
    foreach ($raw as $value) {
        $value = trim((string) $value);
        if (!preg_match('/^(service|domain):(\d+)$/', $value, $m)) {
            continue;
        }
        $id = (int) $m[2];
        if ($id <= 0) {
            continue;
        }
        $items[$m[1] . ':' . $id] = ['type' => $m[1], 'id' => $id];
        if (count($items) >= 500) {
            break;
        }
    }
    return array_values($items);
}

function domainmongerpayrouting_bulk_apply(int $clientId, array $items, int $payMethodId, string $mode): array
{
    if ($clientId <= 0 || !$items || !in_array($mode, ['preview', 'shadow', 'live'], true)) {
        return [false, 0, count($items), ['The bulk assignment request was not valid.']];
    }
    if (!domainmongerpayrouting_load_routing_helpers()) {
        return [false, 0, count($items), ['The Payment Method routing helpers are unavailable.']];
    }

    if ($payMethodId > 0) {
        [$valid, $method, $error] = dm1723_validate_paymethod($clientId, $payMethodId);
        if (!$valid || !$method) {
            return [false, 0, count($items), [$error !== '' ? $error : 'The selected Payment Method is not available for this client.']];
        }
    }

    $success = 0;
    $failures = [];
    foreach ($items as $item) {
        $type = (string) ($item['type'] ?? '');
        $id = (int) ($item['id'] ?? 0);
        [$ok, $message] = dm1723_save_assignment($clientId, $type, $id, $payMethodId, $mode);
        if ($ok) {
            $success++;
        } else {
            $failures[] = ucfirst($type) . ' #' . $id . ': ' . $message;
        }
    }

    if (function_exists('logActivity')) {
        logActivity('DomainMonger Payment Routing [BULK 1746] Client #' . $clientId
            . '; mode=' . $mode
            . '; selected=' . count($items)
            . '; processed=' . $success
            . '; paymethod=' . ($payMethodId > 0 ? '#' . $payMethodId : 'Account Default')
            . ($failures ? '; failures=' . implode(' | ', array_slice($failures, 0, 10)) : ''));
    }

    return [empty($failures), $success, count($items), $failures];
}

function domainmongerpayrouting_bulk_manager_html(int $clientId, string $mode, string $actionUrl, string $scope, ?array $notice = null, bool $showClientHeading = false): string
{
    $items = domainmongerpayrouting_bulk_items($clientId);
    $methods = domainmongerpayrouting_bulk_methods($clientId);
    $token = domainmongerpayrouting_bulk_token($scope, $clientId);
    $esc = 'domainmongerpayrouting_escape';

    $options = '<option value="0">Use Account Default / Existing WHMCS Setting</option>';
    foreach ($methods as $method) {
        $options .= '<option value="' . (int) $method['id'] . '">' . $esc($method['label']) . '</option>';
    }

    $html = '<div class="panel panel-default dm1746-bulk-panel">';
    $html .= '<div class="panel-heading"><strong>Bulk Payment Method Assignment</strong></div><div class="panel-body">';
    if ($showClientHeading) {
        $html .= '<p><strong>' . $esc(domainmongerpayrouting_bulk_client_name($clientId)) . '</strong></p>';
    }
    if ($notice) {
        $html .= '<div class="alert alert-' . $esc($notice[0]) . '">' . $esc($notice[1]) . '</div>';
    }

    if (function_exists('dm1723_account_routing_disabled') && dm1723_account_routing_disabled($clientId)) {
        $html .= '<div class="alert alert-warning"><strong>Payment Routing Disabled for this account:</strong> You can maintain bulk assignments here, but they remain inactive until the account is enabled.</div>';
    } elseif ($mode === 'preview') {
        $html .= '<div class="alert alert-info"><strong>Admin Preview:</strong> Bulk assignments are saved, but client UI, invoices, and Separate Invoices are not changed.</div>';
    } elseif ($mode === 'shadow') {
        $html .= '<div class="alert alert-info"><strong>Shadow:</strong> Bulk assignments are saved for this test client, but invoices are not changed.</div>';
    } else {
        $html .= '<div class="alert alert-warning"><strong>Live routing:</strong> Saving here uses the same assignment and Unpaid-invoice reconciliation logic as the individual product/domain selector.</div>';
    }

    if (!$items) {
        $html .= '<div class="alert alert-warning" style="margin-bottom:0;">No products/services or domains were found for this account.</div></div></div>';
        return $html;
    }

    $initialPagerStatus = domainmongerpayrouting_initial_pager_status(count($items));
    $html .= '<form method="post" action="' . $esc($actionUrl) . '" class="dm1746-bulk-form" data-dm1757-pager-root="assignments">';
    $html .= '<input type="hidden" name="dm1746_bulk_action" value="save">';
    $html .= '<input type="hidden" name="dm1746_bulk_scope" value="' . $esc($scope) . '">';
    $html .= '<input type="hidden" name="dm1746_bulk_token" value="' . $esc($token) . '">';
    $html .= '<input type="hidden" name="dm1746_bulk_clientid" value="' . $clientId . '">';
    $html .= '<div class="dm1758-primary-toolbar"><div class="dm1758-primary-actions"><div class="dm1758-action-field"><label>Payment Method</label><select class="form-control dm1746-bulk-paymethod" name="dm1746_bulk_paymethod_id">' . $options . '</select></div><button type="submit" class="btn btn-primary dm1746-bulk-save" disabled>Assign to Selected</button></div><div class="dm1757-table-search"><span class="fas fa-search" aria-hidden="true"></span><input type="search" class="form-control" data-dm1757-search placeholder="Search products, domains or Payment Methods" aria-label="Search Payment Assignments" autocomplete="off"></div></div>';
    $html .= '<div class="dm1758-secondary-toolbar"><div class="dm1757-table-toolbar-left"><select class="form-control dm1757-page-size" data-dm1757-page-size aria-label="Rows per page"><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option><option value="all">All</option></select></div><div class="dm1757-pager"><span class="dm1757-pager-status" data-dm1757-status>' . $esc($initialPagerStatus) . '</span><button type="button" class="btn btn-default" data-dm1757-prev>Previous</button><button type="button" class="btn btn-default" data-dm1757-next>Next</button></div></div>';
    $html .= '<div class="table-responsive"><table class="table table-striped table-bordered dm1746-bulk-table"><thead><tr>';
    $html .= '<th style="width:48px;text-align:center;"><input type="checkbox" class="dm1746-bulk-select-all" aria-label="Select all"></th><th style="width:90px;">Type</th><th>Item</th><th style="width:130px;">Status</th><th style="width:130px;">Next Due Date</th><th>Current Payment Method</th></tr></thead><tbody>';
    foreach ($items as $item) {
        $html .= '<tr data-dm1757-row><td style="text-align:center;vertical-align:middle;"><input type="checkbox" class="dm1746-bulk-check" name="dm1746_bulk_items[]" value="' . $esc($item['key']) . '"></td>';
        $html .= '<td style="vertical-align:middle;"><strong>' . $esc($item['type_label']) . '</strong></td>';
        $html .= '<td style="vertical-align:middle;">#' . (int) $item['id'] . ' — ' . $esc($item['label']) . '</td>';
        $html .= '<td style="vertical-align:middle;">' . $esc($item['status']) . '</td>';
        $html .= '<td style="vertical-align:middle;">' . $esc($item['next_due_date']) . '</td>';
        $html .= '<td style="vertical-align:middle;">' . $esc($item['current_label']) . '</td></tr>';
    }
    $html .= '</tbody></table></div>';
    $html .= '<div class="alert alert-warning dm1757-no-match" data-dm1757-no-match style="display:none;">No products or domains match this search.</div><div class="dm1761-bottom-bar"><div class="dm1746-bulk-footer dm1761-bottom-actions"><span class="dm1746-bulk-count">0 selected</span><button type="submit" class="btn btn-primary dm1746-bulk-save" disabled>Assign to Selected</button></div><div class="dm1757-pager"><span class="dm1757-pager-status" data-dm1757-status>' . $esc($initialPagerStatus) . '</span><button type="button" class="btn btn-default" data-dm1757-prev>Previous</button><button type="button" class="btn btn-default" data-dm1757-next>Next</button></div></div>';
    $html .= '</form></div></div>';

    $html .= domainmongerpayrouting_pager_assets();

    $html .= <<<'HTML'
<style>
.dm1746-bulk-panel{margin-top:15px;border-color:#d7dce2;background:#fff}
.dm1746-bulk-panel>.panel-heading{background:#163a5f!important;color:#fff!important;border-color:#163a5f!important}
.dm1746-bulk-panel .panel-body{background:#fff}
.dm1746-bulk-panel .btn-primary{background:#f58220!important;border-color:#f58220!important;color:#fff!important}
.dm1746-bulk-panel .btn-primary:hover,.dm1746-bulk-panel .btn-primary:focus{background:#214e7a!important;border-color:#214e7a!important;color:#fff!important}
.dm1746-bulk-panel .btn[disabled]{opacity:.5;cursor:not-allowed}
.dm1746-bulk-table thead th{background:#163a5f;color:#fff;vertical-align:middle}
.dm1746-bulk-footer{display:flex;justify-content:flex-start;align-items:center;gap:10px;margin:0}
.dm1746-bulk-count{color:#666;font-weight:600}
</style>
<script>
(function(){
    document.querySelectorAll('.dm1746-bulk-form').forEach(function(form){
        if(form.getAttribute('data-dm1746-ready')==='1'){return;}
        form.setAttribute('data-dm1746-ready','1');
        var boxes=Array.prototype.slice.call(form.querySelectorAll('.dm1746-bulk-check'));
        var all=form.querySelector('.dm1746-bulk-select-all');
        var saves=Array.prototype.slice.call(form.querySelectorAll('.dm1746-bulk-save'));
        var count=form.querySelector('.dm1746-bulk-count');
        var pager=window.dm1757InitPager?window.dm1757InitPager(form):null;
        function visibleBoxes(){
            if(!pager){return boxes;}
            return pager.getVisibleRows().map(function(row){return row.querySelector('.dm1746-bulk-check');}).filter(Boolean);
        }
        function sync(){
            var selected=boxes.filter(function(b){return b.checked;}).length;
            if(count){count.textContent=selected+' selected';}
            saves.forEach(function(b){b.disabled=selected===0;});
            var pageBoxes=visibleBoxes();
            var pageSelected=pageBoxes.filter(function(b){return b.checked;}).length;
            if(all){all.checked=pageBoxes.length>0&&pageSelected===pageBoxes.length;all.indeterminate=pageSelected>0&&pageSelected<pageBoxes.length;}
        }
        if(all){all.addEventListener('change',function(){visibleBoxes().forEach(function(b){b.checked=all.checked;});sync();});}
        boxes.forEach(function(b){b.addEventListener('change',sync);});
        form.addEventListener('dm1757:render',sync);
        form.addEventListener('submit',function(e){
            var selected=boxes.filter(function(b){return b.checked;}).length;
            if(selected===0){e.preventDefault();return;}
            var select=form.querySelector('.dm1746-bulk-paymethod');
            var label=select&&select.options[select.selectedIndex]?select.options[select.selectedIndex].text:'the selected Payment Method';
            if(!window.confirm('Apply '+label+' to '+selected+' selected item'+(selected===1?'':'s')+'?')){e.preventDefault();return;}
            saves.forEach(function(b){b.disabled=true;b.textContent='Saving...';});
        });
        sync();
    });
}());
</script>
HTML;

    return $html;
}

function domainmongerpayrouting_bulk_admin_section(array $vars): string
{
    if (!domainmongerpayrouting_load_routing_helpers()) {
        return '';
    }

    $testIds = domainmongerpayrouting_test_client_ids((string) ($vars['test_client_ids'] ?? ''));
    $clientId = (int) ($_REQUEST['userid'] ?? $_POST['dm1746_bulk_clientid'] ?? 0);
    $isTestLive = stripos((string) ($vars['routing_mode'] ?? ''), 'Test Live') !== false;
    if ($clientId <= 0 && $isTestLive && count($testIds) === 1) {
        $clientId = (int) $testIds[0];
    }

    if ($clientId <= 0) {
        return '<div class="panel panel-default dm1746-bulk-panel"><div class="panel-heading"><strong>Bulk Payment Method Assignment</strong></div><div class="panel-body">'
            . '<form method="get" action="addonmodules.php" class="form-inline"><input type="hidden" name="module" value="domainmongerpayrouting">'
            . '<label for="dm1746-clientid" style="margin-right:8px;">Client ID</label><input type="number" min="1" class="form-control" id="dm1746-clientid" name="userid" style="width:140px;margin-right:8px;">'
            . '<button type="submit" class="btn btn-primary">Load Client</button></form></div></div>';
    }

    $mode = dm1723_admin_assignment_mode($clientId);
    if (!in_array($mode, ['preview', 'shadow', 'live'], true)) {
        return '<div class="alert alert-warning"><strong>Bulk Payment Method Assignment:</strong> This client is not enabled for assignment in the current Routing Mode.</div>';
    }

    $notice = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['dm1746_bulk_action'] ?? '') === 'save' && (string) ($_POST['dm1746_bulk_scope'] ?? '') === 'admin') {
        $submittedClientId = (int) ($_POST['dm1746_bulk_clientid'] ?? 0);
        $submittedToken = (string) ($_POST['dm1746_bulk_token'] ?? '');
        $expectedToken = domainmongerpayrouting_bulk_token('admin', $clientId);
        if ($submittedClientId !== $clientId || $submittedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
            $notice = ['danger', 'The bulk assignment form expired or did not match this client. Nothing was changed.'];
        } else {
            $items = domainmongerpayrouting_bulk_parse_items($_POST['dm1746_bulk_items'] ?? []);
            $payMethodId = (int) ($_POST['dm1746_bulk_paymethod_id'] ?? 0);
            if (!$items) {
                $notice = ['warning', 'Select at least one product or domain. Nothing was changed.'];
            } else {
                [$allOk, $success, $total, $failures] = domainmongerpayrouting_bulk_apply($clientId, $items, $payMethodId, $mode);
                if ($allOk) {
                    $notice = ['success', 'Processed ' . $success . ' selected item' . ($success === 1 ? '' : 's') . ' successfully.'];
                } else {
                    $notice = ['warning', 'Processed ' . $success . ' of ' . $total . ' selected items. ' . implode(' | ', array_slice($failures, 0, 3))];
                }
            }
        }
    }

    $actionUrl = 'addonmodules.php?module=domainmongerpayrouting&userid=' . $clientId;
    return domainmongerpayrouting_bulk_manager_html($clientId, $mode, $actionUrl, 'admin', $notice, true);
}

function domainmongerpayrouting_clientarea(array $vars): array
{
    domainmongerpayrouting_load_routing_helpers();
    $clientId = function_exists('dm1723_client_id') ? (int) dm1723_client_id() : 0;
    $mode = function_exists('dm1723_client_mode') ? (string) dm1723_client_mode($clientId) : 'off';
    $moduleLink = (string) ($vars['modulelink'] ?? 'index.php?m=domainmongerpayrouting');
    $notice = null;

    if ($clientId <= 0 || !in_array($mode, ['shadow', 'live'], true)) {
        $html = '<div class="alert alert-warning">Bulk Payment Method assignment is not available for this account in the current routing mode.</div>';
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['dm1746_bulk_action'] ?? '') === 'save' && (string) ($_POST['dm1746_bulk_scope'] ?? '') === 'client') {
            $submittedClientId = (int) ($_POST['dm1746_bulk_clientid'] ?? 0);
            $submittedToken = (string) ($_POST['dm1746_bulk_token'] ?? '');
            $expectedToken = domainmongerpayrouting_bulk_token('client', $clientId);
            if ($submittedClientId !== $clientId || $submittedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
                $notice = ['danger', 'The bulk assignment form expired or did not match your account. Nothing was changed.'];
            } else {
                $items = domainmongerpayrouting_bulk_parse_items($_POST['dm1746_bulk_items'] ?? []);
                $payMethodId = (int) ($_POST['dm1746_bulk_paymethod_id'] ?? 0);
                if (!$items) {
                    $notice = ['warning', 'Select at least one product or domain. Nothing was changed.'];
                } else {
                    [$allOk, $success, $total, $failures] = domainmongerpayrouting_bulk_apply($clientId, $items, $payMethodId, $mode);
                    if ($allOk) {
                        $notice = ['success', 'Processed ' . $success . ' selected item' . ($success === 1 ? '' : 's') . ' successfully.'];
                    } else {
                        $notice = ['warning', 'Processed ' . $success . ' of ' . $total . ' selected items. ' . implode(' | ', array_slice($failures, 0, 3))];
                    }
                }
            }
        }
        $html = domainmongerpayrouting_bulk_manager_html($clientId, $mode, $moduleLink, 'client', $notice, false);
    }

    return [
        'pagetitle' => 'Bulk Payment Methods',
        'breadcrumb' => [$moduleLink => 'Bulk Payment Methods'],
        'templatefile' => 'bulkassign',
        'requirelogin' => true,
        'forcessl' => false,
        'vars' => ['dmBulkHtml' => $html],
    ];
}

