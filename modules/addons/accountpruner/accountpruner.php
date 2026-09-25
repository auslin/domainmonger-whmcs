<?php
/**
 * Account Pruner
 *
 * Admin-only WHMCS addon for reviewing and pruning empty client accounts.
 *
 * Safety rules:
 * - A client is "empty" only when there are zero tblhosting rows and zero tbldomains rows.
 * - Clients with any Products/Services or Domains may only be changed to Active.
 * - Inactive, Closed, and Delete are blocked for clients with Products/Services or Domains.
 * - Every action is revalidated server-side immediately before it runs.
 * - Status changes use the WHMCS UpdateClient Local API.
 * - Deletion uses the WHMCS DeleteClient Local API.
 *
 * v1.0.1 / Patch 1804: replaces failed initial build with compatibility-first
 * page loading, account-user last-login display, and full runtime fail-safe output.
 * Patch 1805: client names open the native WHMCS client summary page.
 * v1.1.0 / Patch 1806: adds sortable invoice/transaction history, Payment Method
 * Health-style sort arrows, 250/500-row bulk pages, top+bottom bulk controls, and
 * removes the redundant per-row Actions column while preserving all server guards.
 * v1.1.1 / Patch 1807: fixes server-side sort links being intercepted by WHMCS
 * DataTables behavior while preserving the confirmed Payment Method Health arrow look.
 * v1.1.2 / Patch 1808: makes the full sortable header (including arrow) clickable,
 * adds Products/Services and Domains sorting, and locks column geometry to prevent
 * visible left/right shifts while sorted result sets redraw.
 * v1.1.3 / Patch 1809: prevents POST resubmission after Delete/status changes
 * with a Post/Redirect/Get flow and adds an immediate processing counter/spinner.
 * v1.1.4 / Patch 1810: adds a sortable active Payment Methods count and removes
 * the tiny desktop horizontal scrollbar while preserving fixed-layout redraw stability.
 * v1.1.5 / Patch 1811: keeps the fixed-width table and allows only Transaction
 * history text to wrap cleanly inside its column instead of overflowing.
 */

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

function accountpruner_config(): array
{
    return [
        'name' => 'Account Pruner',
        'description' => 'Review empty client accounts, see last login and account contents, change eligible client status, and prune empty accounts safely.',
        'version' => '1.1.4',
        'author' => 'DomainMonger',
        'language' => 'english',
        'fields' => [],
    ];
}

function accountpruner_activate(): array
{
    return [
        'status' => 'success',
        'description' => 'Account Pruner is ready. Configure administrator role access under Addon Modules.',
    ];
}

function accountpruner_deactivate(): array
{
    return [
        'status' => 'success',
        'description' => 'Account Pruner has been deactivated. No client data was changed by deactivation.',
    ];
}

function accountpruner_output($vars)
{
    try {
        accountpruner_output_safe(is_array($vars) ? $vars : []);
    } catch (\Throwable $e) {
        accountpruner_render_runtime_error($e);
    }
}

function accountpruner_output_safe(array $vars): void
{
    $moduleLink = trim((string) ($vars['modulelink'] ?? ''));
    if ($moduleLink === '') {
        $moduleLink = 'addonmodules.php?module=accountpruner';
    }

    $filters = accountpruner_filters_from_request();
    $flash = accountpruner_take_flash();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ap_action'])) {
        check_token('WHMCS.admin.default');
        $flash = accountpruner_process_action($_POST);
        accountpruner_store_flash($flash);
        accountpruner_redirect_after_post(accountpruner_current_url($moduleLink, $filters));
        return;
    }

    try {
        $pageData = accountpruner_load_page($filters);
    } catch (Throwable $e) {
        logActivity('Account Pruner: could not load client list: ' . $e->getMessage());
        echo accountpruner_styles();
        echo '<div class="accountpruner-wrap">';
        echo '<div class="panel panel-default accountpruner-panel"><div class="panel-heading"><strong>Account Pruner</strong></div><div class="panel-body">';
        echo '<div class="alert alert-danger">Account Pruner could not load the client list. The technical error was written to the WHMCS Activity Log.</div>';
        echo '</div></div></div>';
        return;
    }

    echo accountpruner_styles();
    echo '<div class="accountpruner-wrap">';
    echo '<div class="accountpruner-heading"><div><h2>Account Pruner</h2>';
    echo '<p>Review empty client accounts before changing status or deleting them.</p></div></div>';

    if (is_array($flash)) {
        echo accountpruner_render_flash($flash);
    }

    echo '<div class="alert alert-warning accountpruner-safety">';
    echo '<strong>Protection rule:</strong> If a client has any Products/Services or Domains, this addon only permits changing that client to <strong>Active</strong>. ';
    echo 'Inactive, Closed, and Delete are blocked in the interface and rechecked on the server. Deletion is permanent.';
    echo '</div>';

    echo accountpruner_render_filters($moduleLink, $filters, $pageData);
    echo accountpruner_render_table($moduleLink, $filters, $pageData);
    echo accountpruner_script();
    echo '</div>';
}

function accountpruner_filters_from_request(): array
{
    $scope = strtolower(trim((string) ($_GET['ap_scope'] ?? 'empty')));
    if (!in_array($scope, ['empty', 'all', 'protected'], true)) {
        $scope = 'empty';
    }

    $status = ucfirst(strtolower(trim((string) ($_GET['ap_status'] ?? 'all'))));
    if (!in_array($status, ['All', 'Active', 'Inactive', 'Closed'], true)) {
        $status = 'All';
    }

    $lastLogin = strtolower(trim((string) ($_GET['ap_lastlogin'] ?? 'all')));
    if (!in_array($lastLogin, ['all', 'never', '30', '90', '180', '365'], true)) {
        $lastLogin = 'all';
    }

    $search = trim((string) ($_GET['ap_search'] ?? ''));
    if (strlen($search) > 150) {
        $search = substr($search, 0, 150);
    }

    $perPage = (int) ($_GET['ap_perpage'] ?? 25);
    if (!in_array($perPage, [25, 50, 100, 250, 500], true)) {
        $perPage = 25;
    }

    $sort = strtolower(trim((string) ($_GET['ap_sort'] ?? 'lastlogin')));
    if (!in_array($sort, ['lastlogin', 'created', 'client', 'status', 'services', 'domains', 'paymentmethods', 'invoices', 'transactions'], true)) {
        $sort = 'lastlogin';
    }

    $direction = strtolower(trim((string) ($_GET['ap_dir'] ?? 'asc')));
    if (!in_array($direction, ['asc', 'desc'], true)) {
        $direction = 'asc';
    }

    $page = max(1, (int) ($_GET['ap_page'] ?? 1));

    return [
        'scope' => $scope,
        'status' => $status,
        'lastlogin' => $lastLogin,
        'search' => $search,
        'perpage' => $perPage,
        'sort' => $sort,
        'dir' => $direction,
        'page' => $page,
    ];
}

function accountpruner_client_query(array $filters)
{
    $query = Capsule::table('tblclients as c');

    if ($filters['scope'] === 'empty') {
        $query->whereNotExists(function ($q) {
            $q->select(Capsule::raw('1'))
                ->from('tblhosting as h')
                ->whereRaw('h.userid = c.id');
        });
        $query->whereNotExists(function ($q) {
            $q->select(Capsule::raw('1'))
                ->from('tbldomains as d')
                ->whereRaw('d.userid = c.id');
        });
    } elseif ($filters['scope'] === 'protected') {
        $query->where(function ($q) {
            $q->whereExists(function ($sub) {
                $sub->select(Capsule::raw('1'))
                    ->from('tblhosting as h')
                    ->whereRaw('h.userid = c.id');
            })->orWhereExists(function ($sub) {
                $sub->select(Capsule::raw('1'))
                    ->from('tbldomains as d')
                    ->whereRaw('d.userid = c.id');
            });
        });
    }

    if ($filters['status'] !== 'All') {
        $query->where('c.status', $filters['status']);
    }

    if ($filters['lastlogin'] === 'never') {
        $query->where(function ($q) {
            $q->whereNull('c.lastlogin')
                ->orWhere('c.lastlogin', '=', '0000-00-00 00:00:00');
        });
    } elseif (in_array($filters['lastlogin'], ['30', '90', '180', '365'], true)) {
        $days = (int) $filters['lastlogin'];
        $cutoff = date('Y-m-d H:i:s', time() - ($days * 86400));
        $query->where(function ($q) use ($cutoff) {
            $q->whereNull('c.lastlogin')
                ->orWhere('c.lastlogin', '=', '0000-00-00 00:00:00')
                ->orWhere('c.lastlogin', '<=', $cutoff);
        });
    }

    if ($filters['search'] !== '') {
        $term = '%' . $filters['search'] . '%';
        $query->where(function ($q) use ($term) {
            $q->where('c.email', 'like', $term)
                ->orWhere('c.firstname', 'like', $term)
                ->orWhere('c.lastname', 'like', $term)
                ->orWhere('c.companyname', 'like', $term)
                ->orWhereRaw("CONCAT(c.firstname, ' ', c.lastname) LIKE ?", [$term]);

            $numeric = trim($term, '%');
            if ($numeric !== '' && ctype_digit($numeric)) {
                $q->orWhere('c.id', '=', (int) $numeric);
            }
        });
    }

    return $query;
}

function accountpruner_load_page(array $filters): array
{
    $countQuery = accountpruner_client_query($filters);
    $total = (int) $countQuery->count('c.id');

    $perPage = (int) $filters['perpage'];
    $totalPages = max(1, (int) ceil($total / max(1, $perPage)));
    $page = min((int) $filters['page'], $totalPages);
    $offset = ($page - 1) * $perPage;

    $selectColumns = [
        'c.id',
        'c.firstname',
        'c.lastname',
        'c.companyname',
        'c.email',
        'c.status',
        'c.datecreated',
        'c.lastlogin',
    ];

    $rows = [];
    $activitySortStats = [];

    if (in_array($filters['sort'], ['services', 'domains', 'paymentmethods', 'invoices', 'transactions'], true)) {
        // Keep invoice/transaction sorting compatibility-first: collect the matching
        // client IDs, calculate activity in grouped queries, sort IDs in PHP, then
        // load only the requested page. This avoids fragile correlated SQL subqueries.
        $allIds = accountpruner_client_query($filters)->pluck('c.id')->map(function ($id) {
            return (int) $id;
        })->all();

        if ($filters['sort'] === 'services') {
            $activitySortStats = accountpruner_count_stats('tblhosting', 'userid', $allIds);
            $sortedIds = accountpruner_sort_ids_by_count($allIds, $activitySortStats, $filters['dir']);
        } elseif ($filters['sort'] === 'domains') {
            $activitySortStats = accountpruner_count_stats('tbldomains', 'userid', $allIds);
            $sortedIds = accountpruner_sort_ids_by_count($allIds, $activitySortStats, $filters['dir']);
        } elseif ($filters['sort'] === 'paymentmethods') {
            $activitySortStats = accountpruner_active_payment_method_counts($allIds);
            $sortedIds = accountpruner_sort_ids_by_count($allIds, $activitySortStats, $filters['dir']);
        } elseif ($filters['sort'] === 'invoices') {
            $activitySortStats = accountpruner_activity_stats('tblinvoices', 'userid', 'date', $allIds);
            $sortedIds = accountpruner_sort_ids_by_activity($allIds, $activitySortStats, $filters['dir']);
        } else {
            $activitySortStats = accountpruner_activity_stats('tblaccounts', 'userid', 'date', $allIds);
            $sortedIds = accountpruner_sort_ids_by_activity($allIds, $activitySortStats, $filters['dir']);
        }
        $pageIds = array_slice($sortedIds, $offset, $perPage);

        if ($pageIds !== []) {
            $loaded = Capsule::table('tblclients as c')
                ->select($selectColumns)
                ->whereIn('c.id', $pageIds)
                ->get();

            $byId = [];
            foreach ($loaded as $row) {
                $byId[(int) $row->id] = $row;
            }
            foreach ($pageIds as $clientId) {
                if (isset($byId[$clientId])) {
                    $rows[] = $byId[$clientId];
                }
            }
        }
    } else {
        $query = accountpruner_client_query($filters)->select($selectColumns);

        switch ($filters['sort']) {
            case 'created':
                $query->orderBy('c.datecreated', $filters['dir'])->orderBy('c.id', 'asc');
                break;
            case 'client':
                $query->orderBy('c.lastname', $filters['dir'])->orderBy('c.firstname', $filters['dir'])->orderBy('c.id', 'asc');
                break;
            case 'status':
                $query->orderBy('c.status', $filters['dir'])->orderBy('c.id', 'asc');
                break;
            case 'lastlogin':
            default:
                // Preserve the confirmed 1804/1805 compatibility-first login sorter.
                $query->orderBy('c.lastlogin', $filters['dir'])->orderBy('c.id', 'asc');
                break;
        }

        $rows = $query->offset($offset)->limit($perPage)->get();
    }

    $ids = [];
    foreach ($rows as $row) {
        $ids[] = (int) $row->id;
    }

    $serviceCounts = accountpruner_client_counts('tblhosting', 'userid', $ids);
    $domainCounts = accountpruner_client_counts('tbldomains', 'userid', $ids);
    $accountLastLogins = accountpruner_account_last_logins($ids);
    $paymentMethodCounts = accountpruner_active_payment_method_counts($ids);
    $invoiceStats = $filters['sort'] === 'invoices'
        ? accountpruner_pick_activity_stats($activitySortStats, $ids)
        : accountpruner_activity_stats('tblinvoices', 'userid', 'date', $ids);
    $transactionStats = $filters['sort'] === 'transactions'
        ? accountpruner_pick_activity_stats($activitySortStats, $ids)
        : accountpruner_activity_stats('tblaccounts', 'userid', 'date', $ids);

    foreach ($rows as $row) {
        $clientId = (int) $row->id;
        $row->service_count = (int) ($serviceCounts[$clientId] ?? 0);
        $row->domain_count = (int) ($domainCounts[$clientId] ?? 0);
        $row->payment_method_count = (int) ($paymentMethodCounts[$clientId] ?? 0);
        $row->invoice_count = (int) ($invoiceStats[$clientId]['total'] ?? 0);
        $row->last_invoice_date = (string) ($invoiceStats[$clientId]['last_date'] ?? '');
        $row->transaction_count = (int) ($transactionStats[$clientId]['total'] ?? 0);
        $row->last_transaction_date = (string) ($transactionStats[$clientId]['last_date'] ?? '');
        if (!empty($accountLastLogins[$clientId])) {
            $row->account_last_login = (string) $accountLastLogins[$clientId];
        } else {
            $row->account_last_login = (string) ($row->lastlogin ?? '');
        }
    }

    $serviceBreakdown = accountpruner_status_breakdown('tblhosting', 'userid', 'domainstatus', $ids);
    $domainBreakdown = accountpruner_status_breakdown('tbldomains', 'userid', 'status', $ids);

    return [
        'rows' => $rows,
        'service_breakdown' => $serviceBreakdown,
        'domain_breakdown' => $domainBreakdown,
        'total' => $total,
        'page' => $page,
        'perpage' => $perPage,
        'total_pages' => $totalPages,
        'from' => $total > 0 ? $offset + 1 : 0,
        'to' => $total > 0 ? min($offset + $perPage, $total) : 0,
    ];
}

function accountpruner_count_stats(string $table, string $clientColumn, array $ids): array
{
    if ($ids === []) {
        return [];
    }

    $result = [];
    foreach (array_chunk(array_values(array_unique(array_map('intval', $ids))), 800) as $chunk) {
        $rows = Capsule::table($table)
            ->select([$clientColumn, Capsule::raw('COUNT(*) as total')])
            ->whereIn($clientColumn, $chunk)
            ->groupBy($clientColumn)
            ->get();

        foreach ($rows as $row) {
            $result[(int) $row->{$clientColumn}] = (int) $row->total;
        }
    }

    return $result;
}

function accountpruner_sort_ids_by_count(array $ids, array $stats, string $direction): array
{
    $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
    usort($ids, function ($left, $right) use ($stats, $direction) {
        $left = (int) $left;
        $right = (int) $right;
        $leftTotal = (int) ($stats[$left] ?? 0);
        $rightTotal = (int) ($stats[$right] ?? 0);

        if ($leftTotal !== $rightTotal) {
            $cmp = $leftTotal <=> $rightTotal;
            return $direction === 'desc' ? -$cmp : $cmp;
        }

        return $left <=> $right;
    });

    return $ids;
}

function accountpruner_activity_stats(string $table, string $clientColumn, string $dateColumn, array $ids): array
{
    if ($ids === []) {
        return [];
    }

    $result = [];
    foreach (array_chunk(array_values(array_unique(array_map('intval', $ids))), 800) as $chunk) {
        $rows = Capsule::table($table)
            ->select([$clientColumn, Capsule::raw('COUNT(*) as total'), Capsule::raw('MAX(' . $dateColumn . ') as last_date')])
            ->whereIn($clientColumn, $chunk)
            ->groupBy($clientColumn)
            ->get();

        foreach ($rows as $row) {
            $clientId = (int) $row->{$clientColumn};
            $result[$clientId] = [
                'total' => (int) $row->total,
                'last_date' => (string) ($row->last_date ?? ''),
            ];
        }
    }

    return $result;
}

function accountpruner_pick_activity_stats(array $stats, array $ids): array
{
    $result = [];
    foreach ($ids as $clientId) {
        $clientId = (int) $clientId;
        if (isset($stats[$clientId])) {
            $result[$clientId] = $stats[$clientId];
        }
    }
    return $result;
}

function accountpruner_sort_ids_by_activity(array $ids, array $stats, string $direction): array
{
    $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
    usort($ids, function ($left, $right) use ($stats, $direction) {
        $left = (int) $left;
        $right = (int) $right;
        $leftDate = trim((string) ($stats[$left]['last_date'] ?? ''));
        $rightDate = trim((string) ($stats[$right]['last_date'] ?? ''));
        $leftTs = ($leftDate !== '' && strpos($leftDate, '0000-00-00') !== 0 && strtotime($leftDate) !== false) ? (int) strtotime($leftDate) : 0;
        $rightTs = ($rightDate !== '' && strpos($rightDate, '0000-00-00') !== 0 && strtotime($rightDate) !== false) ? (int) strtotime($rightDate) : 0;

        if ($leftTs !== $rightTs) {
            $cmp = $leftTs <=> $rightTs;
            return $direction === 'desc' ? -$cmp : $cmp;
        }

        $leftTotal = (int) ($stats[$left]['total'] ?? 0);
        $rightTotal = (int) ($stats[$right]['total'] ?? 0);
        if ($leftTotal !== $rightTotal) {
            $cmp = $leftTotal <=> $rightTotal;
            return $direction === 'desc' ? -$cmp : $cmp;
        }

        return $left <=> $right;
    });

    return $ids;
}

function accountpruner_client_counts(string $table, string $clientColumn, array $ids): array
{
    if ($ids === []) {
        return [];
    }

    $rows = Capsule::table($table)
        ->select([$clientColumn, Capsule::raw('COUNT(*) as total')])
        ->whereIn($clientColumn, $ids)
        ->groupBy($clientColumn)
        ->get();

    $result = [];
    foreach ($rows as $row) {
        $result[(int) $row->{$clientColumn}] = (int) $row->total;
    }
    return $result;
}

function accountpruner_active_payment_method_counts(array $ids): array
{
    if ($ids === []) {
        return [];
    }

    try {
        $result = [];
        foreach (array_chunk(array_values(array_unique(array_map('intval', $ids))), 800) as $chunk) {
            $rows = Capsule::table('tblpaymethods')
                ->select(['userid', Capsule::raw('COUNT(*) as total')])
                ->whereIn('userid', $chunk)
                ->whereNull('deleted_at')
                ->groupBy('userid')
                ->get();

            foreach ($rows as $row) {
                $result[(int) $row->userid] = (int) $row->total;
            }
        }
        return $result;
    } catch (\Throwable $e) {
        // Payment Methods are informational only; never fail Account Pruner over this column.
        logActivity('Account Pruner: could not count active Payment Methods: ' . $e->getMessage());
        return [];
    }
}

function accountpruner_account_last_logins(array $ids): array
{
    if ($ids === []) {
        return [];
    }

    try {
        if (!Capsule::schema()->hasTable('tblusers_clients')) {
            return [];
        }
        if (!Capsule::schema()->hasColumn('tblusers_clients', 'last_login')) {
            return [];
        }

        $rows = Capsule::table('tblusers_clients')
            ->select(['client_id', Capsule::raw('MAX(last_login) as last_login')])
            ->whereIn('client_id', $ids)
            ->groupBy('client_id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            if (!empty($row->last_login)) {
                $result[(int) $row->client_id] = (string) $row->last_login;
            }
        }
        return $result;
    } catch (\Throwable $e) {
        // tblclients.lastlogin remains the fallback; never fail the page over this enhancement.
        return [];
    }
}

function accountpruner_status_breakdown(string $table, string $clientColumn, string $statusColumn, array $ids): array
{
    if ($ids === []) {
        return [];
    }

    $rows = Capsule::table($table)
        ->select([$clientColumn, $statusColumn, Capsule::raw('COUNT(*) as total')])
        ->whereIn($clientColumn, $ids)
        ->groupBy($clientColumn, $statusColumn)
        ->get();

    $result = [];
    foreach ($rows as $row) {
        $clientId = (int) $row->{$clientColumn};
        $status = trim((string) $row->{$statusColumn});
        if ($status === '') {
            $status = 'Unknown';
        }
        $result[$clientId][$status] = (int) $row->total;
    }

    return $result;
}

function accountpruner_process_action(array $post): array
{
    $action = strtolower(trim((string) ($post['ap_action'] ?? '')));
    $allowed = ['set_active', 'set_inactive', 'set_closed', 'delete'];
    if (!in_array($action, $allowed, true)) {
        return ['type' => 'danger', 'message' => 'The requested Account Pruner action is not supported.'];
    }

    $ids = [];
    if (isset($post['client_ids']) && is_array($post['client_ids'])) {
        foreach ($post['client_ids'] as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
    }

    if (isset($post['client_id'])) {
        $id = (int) $post['client_id'];
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }

    if (isset($post['client_ids_csv'])) {
        foreach (preg_split('/[\s,]+/', trim((string) $post['client_ids_csv'])) as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
    }

    $ids = array_values($ids);
    if ($ids === []) {
        return ['type' => 'warning', 'message' => 'Select at least one client account.'];
    }

    $succeeded = 0;
    $blocked = 0;
    $failed = 0;
    $messages = [];

    foreach ($ids as $clientId) {
        try {
            $client = Capsule::table('tblclients')->where('id', $clientId)->first(['id', 'firstname', 'lastname', 'email', 'status']);
            if (!$client) {
                $failed++;
                $messages[] = '#' . $clientId . ' no longer exists.';
                continue;
            }

            $eligibility = accountpruner_eligibility($clientId);
            $hasItems = ($eligibility['service_count'] + $eligibility['domain_count']) > 0;

            if ($action !== 'set_active' && $hasItems) {
                $blocked++;
                $messages[] = '#' . $clientId . ' was blocked because it has ' . $eligibility['service_count'] . ' Products/Services and ' . $eligibility['domain_count'] . ' Domains.';
                continue;
            }

            if ($action === 'delete') {
                $result = localAPI('DeleteClient', [
                    'clientid' => $clientId,
                    'deleteusers' => true,
                    'deletetransactions' => false,
                ]);

                if (strtolower((string) ($result['result'] ?? '')) !== 'success') {
                    $failed++;
                    $detail = trim((string) ($result['message'] ?? $result['error'] ?? 'Unknown WHMCS API error'));
                    $messages[] = '#' . $clientId . ' delete failed: ' . $detail;
                    logActivity('Account Pruner: DeleteClient failed for client #' . $clientId . ': ' . $detail);
                    continue;
                }

                $succeeded++;
                logActivity('Account Pruner: deleted empty client #' . $clientId . ' (' . trim((string) $client->firstname . ' ' . (string) $client->lastname) . ', ' . (string) $client->email . '). Products/Services: 0; Domains: 0.');
                continue;
            }

            $targetStatus = accountpruner_target_status($action);
            if ((string) $client->status === $targetStatus) {
                $succeeded++;
                continue;
            }

            $result = localAPI('UpdateClient', [
                'clientid' => $clientId,
                'status' => $targetStatus,
            ]);

            if (strtolower((string) ($result['result'] ?? '')) !== 'success') {
                $failed++;
                $detail = trim((string) ($result['message'] ?? $result['error'] ?? 'Unknown WHMCS API error'));
                $messages[] = '#' . $clientId . ' status update failed: ' . $detail;
                logActivity('Account Pruner: UpdateClient failed for client #' . $clientId . ' while setting ' . $targetStatus . ': ' . $detail);
                continue;
            }

            $succeeded++;
            logActivity('Account Pruner: changed client #' . $clientId . ' status from ' . (string) $client->status . ' to ' . $targetStatus . '. Products/Services: ' . $eligibility['service_count'] . '; Domains: ' . $eligibility['domain_count'] . '.');
        } catch (Throwable $e) {
            $failed++;
            $messages[] = '#' . $clientId . ' failed: ' . $e->getMessage();
            logActivity('Account Pruner: action failed for client #' . $clientId . ': ' . $e->getMessage());
        }
    }

    $label = accountpruner_action_label($action);
    $summary = $label . ': ' . $succeeded . ' succeeded';
    if ($blocked > 0) {
        $summary .= ', ' . $blocked . ' blocked';
    }
    if ($failed > 0) {
        $summary .= ', ' . $failed . ' failed';
    }
    $summary .= '.';

    if ($messages !== []) {
        $summary .= ' ' . implode(' ', array_slice($messages, 0, 6));
        if (count($messages) > 6) {
            $summary .= ' Additional details were written to the Activity Log.';
        }
    }

    $type = $failed > 0 ? 'danger' : ($blocked > 0 ? 'warning' : 'success');
    return ['type' => $type, 'message' => $summary];
}

function accountpruner_eligibility(int $clientId): array
{
    return [
        'service_count' => (int) Capsule::table('tblhosting')->where('userid', $clientId)->count(),
        'domain_count' => (int) Capsule::table('tbldomains')->where('userid', $clientId)->count(),
    ];
}

function accountpruner_target_status(string $action): string
{
    if ($action === 'set_inactive') {
        return 'Inactive';
    }
    if ($action === 'set_closed') {
        return 'Closed';
    }
    return 'Active';
}

function accountpruner_action_label(string $action): string
{
    return [
        'set_active' => 'Set Active',
        'set_inactive' => 'Set Inactive',
        'set_closed' => 'Set Closed',
        'delete' => 'Delete',
    ][$action] ?? 'Action';
}

function accountpruner_render_filters(string $moduleLink, array $filters, array $pageData): string
{
    $html = '<div class="panel panel-default accountpruner-panel">';
    $html .= '<div class="panel-heading"><strong>Accounts</strong></div>';
    $html .= '<div class="panel-body accountpruner-filter-body">';
    $html .= '<form method="get" action="addonmodules.php" class="accountpruner-filter-form">';
    $html .= '<input type="hidden" name="module" value="accountpruner">';

    $html .= '<div class="accountpruner-filter-field accountpruner-search-field"><label for="ap-search">Search</label>';
    $html .= '<input id="ap-search" type="search" class="form-control" name="ap_search" value="' . accountpruner_escape($filters['search']) . '" placeholder="Client ID, name, company, or email"></div>';

    $html .= '<div class="accountpruner-filter-field"><label for="ap-scope">Account Contents</label><select id="ap-scope" class="form-control" name="ap_scope">';
    $html .= accountpruner_option('empty', 'Empty Accounts', $filters['scope']);
    $html .= accountpruner_option('all', 'All Accounts', $filters['scope']);
    $html .= accountpruner_option('protected', 'Has Products/Services or Domains', $filters['scope']);
    $html .= '</select></div>';

    $html .= '<div class="accountpruner-filter-field"><label for="ap-status">Status</label><select id="ap-status" class="form-control" name="ap_status">';
    foreach (['All', 'Active', 'Inactive', 'Closed'] as $status) {
        $html .= accountpruner_option(strtolower($status), $status === 'All' ? 'All Statuses' : $status, strtolower($filters['status']));
    }
    $html .= '</select></div>';

    $html .= '<div class="accountpruner-filter-field"><label for="ap-lastlogin">Last Login</label><select id="ap-lastlogin" class="form-control" name="ap_lastlogin">';
    $lastLoginOptions = [
        'all' => 'Any',
        'never' => 'Never',
        '30' => '30+ Days Ago / Never',
        '90' => '90+ Days Ago / Never',
        '180' => '180+ Days Ago / Never',
        '365' => '1+ Year Ago / Never',
    ];
    foreach ($lastLoginOptions as $value => $label) {
        $html .= accountpruner_option($value, $label, $filters['lastlogin']);
    }
    $html .= '</select></div>';

    $html .= '<div class="accountpruner-filter-field accountpruner-perpage-field"><label for="ap-perpage">Rows</label><select id="ap-perpage" class="form-control" name="ap_perpage">';
    foreach ([25, 50, 100, 250, 500] as $count) {
        $html .= accountpruner_option((string) $count, (string) $count, (string) $filters['perpage']);
    }
    $html .= '</select></div>';

    $html .= '<div class="accountpruner-filter-actions"><button type="submit" class="btn btn-primary">Apply Filters</button>';
    $html .= '<a class="btn btn-default" href="' . accountpruner_escape($moduleLink) . '">Reset</a></div>';
    $html .= '</form>';

    $html .= '<div class="accountpruner-result-summary">Showing <strong>' . (int) $pageData['from'] . '–' . (int) $pageData['to'] . '</strong> of <strong>' . (int) $pageData['total'] . '</strong> matching accounts.</div>';
    $html .= '</div></div>';

    return $html;
}

function accountpruner_render_table(string $moduleLink, array $filters, array $pageData): string
{
    $token = function_exists('generate_token') ? (string) generate_token('plain') : '';
    $rows = $pageData['rows'];
    $actionUrl = accountpruner_current_url($moduleLink, $filters);

    // A single compact form receives the action and selected IDs from either the top
    // or bottom controls. Checkboxes intentionally have no form name so 500-row bulk
    // actions do not depend on PHP max_input_vars.
    $html = '<form method="post" action="' . accountpruner_escape($actionUrl) . '" id="accountpruner-bulk-form" class="accountpruner-hidden-bulk-form">';
    $html .= '<input type="hidden" name="token" value="' . accountpruner_escape($token) . '">';
    $html .= '<input type="hidden" name="ap_action" id="accountpruner-bulk-action-value" value="">';
    $html .= '<input type="hidden" name="client_ids_csv" id="accountpruner-client-ids-csv" value="">';
    $html .= '</form>';

    $html .= '<div class="panel panel-default accountpruner-panel accountpruner-table-panel">';
    $html .= accountpruner_bulk_bar('top', $moduleLink, $filters, $pageData);
    $html .= '<div class="table-responsive"><table class="table table-striped table-bordered accountpruner-table">';
    $html .= '<colgroup><col class="accountpruner-col-check"><col class="accountpruner-col-client"><col class="accountpruner-col-status"><col class="accountpruner-col-created"><col class="accountpruner-col-lastlogin"><col class="accountpruner-col-services"><col class="accountpruner-col-domains"><col class="accountpruner-col-paymentmethods"><col class="accountpruner-col-invoices"><col class="accountpruner-col-transactions"></colgroup>';
    $html .= '<thead><tr>';
    $html .= '<th class="accountpruner-check"><input type="checkbox" id="accountpruner-select-all" aria-label="Select all eligible rows"></th>';
    $html .= accountpruner_sort_header($moduleLink, $filters, 'client', 'Client');
    $html .= accountpruner_sort_header($moduleLink, $filters, 'status', 'Status');
    $html .= accountpruner_sort_header($moduleLink, $filters, 'created', 'Created');
    $html .= accountpruner_sort_header($moduleLink, $filters, 'lastlogin', 'Last Login');
    $html .= accountpruner_sort_header($moduleLink, $filters, 'services', 'Products/Services');
    $html .= accountpruner_sort_header($moduleLink, $filters, 'domains', 'Domains');
    $html .= accountpruner_sort_header($moduleLink, $filters, 'paymentmethods', 'PM');
    $html .= accountpruner_sort_header($moduleLink, $filters, 'invoices', 'Invoices');
    $html .= accountpruner_sort_header($moduleLink, $filters, 'transactions', 'Transactions');
    $html .= '</tr></thead><tbody>';

    if (count($rows) === 0) {
        $html .= '<tr><td colspan="10" class="text-center text-muted accountpruner-empty-row">No client accounts match the current filters.</td></tr>';
    }

    foreach ($rows as $row) {
        $clientId = (int) $row->id;
        $serviceCount = (int) $row->service_count;
        $domainCount = (int) $row->domain_count;
        $hasItems = ($serviceCount + $domainCount) > 0;
        $fullName = trim((string) $row->firstname . ' ' . (string) $row->lastname);
        if ($fullName === '') {
            $fullName = 'Client #' . $clientId;
        }

        $html .= '<tr data-accountpruner-row data-protected="' . ($hasItems ? '1' : '0') . '">';
        $html .= '<td class="accountpruner-check"><input type="checkbox" class="accountpruner-row-check" value="' . $clientId . '" aria-label="Select client #' . $clientId . '"></td>';

        $html .= '<td class="accountpruner-client-cell">';
        $html .= '<a class="accountpruner-client-name" href="clientssummary.php?userid=' . $clientId . '">' . accountpruner_escape($fullName) . '</a>';
        if (trim((string) $row->companyname) !== '') {
            $html .= '<div class="accountpruner-subtext">' . accountpruner_escape((string) $row->companyname) . '</div>';
        }
        $html .= '<div class="accountpruner-subtext"><a href="mailto:' . accountpruner_escape((string) $row->email) . '">' . accountpruner_escape((string) $row->email) . '</a> &middot; #' . $clientId . '</div>';
        $html .= '</td>';

        $html .= '<td>' . accountpruner_status_badge((string) $row->status) . '</td>';
        $html .= '<td class="accountpruner-date-cell">' . accountpruner_format_date((string) $row->datecreated, false) . '</td>';
        $html .= '<td class="accountpruner-date-cell">' . accountpruner_format_last_login((string) ($row->account_last_login ?? $row->lastlogin ?? '')) . '</td>';

        $serviceBreakdown = $pageData['service_breakdown'][$clientId] ?? [];
        $domainBreakdown = $pageData['domain_breakdown'][$clientId] ?? [];

        $html .= '<td>' . accountpruner_contents_cell($clientId, 'service', $serviceCount, $serviceBreakdown) . '</td>';
        $html .= '<td>' . accountpruner_contents_cell($clientId, 'domain', $domainCount, $domainBreakdown) . '</td>';
        $html .= '<td class="accountpruner-pm-cell" title="Active saved Payment Methods">' . accountpruner_payment_method_cell((int) ($row->payment_method_count ?? 0)) . '</td>';
        $html .= '<td class="accountpruner-history-cell">' . accountpruner_activity_cell((int) ($row->invoice_count ?? 0), (string) ($row->last_invoice_date ?? '')) . '</td>';
        $html .= '<td class="accountpruner-history-cell accountpruner-transaction-cell">' . accountpruner_activity_cell((int) ($row->transaction_count ?? 0), (string) ($row->last_transaction_date ?? ''), true) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';
    $html .= accountpruner_bulk_bar('bottom', $moduleLink, $filters, $pageData);
    $html .= '</div>';
    $html .= '<div class="accountpruner-processing" id="accountpruner-processing" aria-live="assertive" aria-hidden="true"><div class="accountpruner-processing-card"><span class="accountpruner-spinner" aria-hidden="true"></span><strong data-accountpruner-processing-text>Processing selected accounts...</strong></div></div>';
    return $html;
}

function accountpruner_bulk_bar(string $position, string $moduleLink, array $filters, array $pageData): string
{
    $position = $position === 'top' ? 'top' : 'bottom';
    $html = '<div class="accountpruner-footer accountpruner-bulk-bar accountpruner-bulk-bar-' . $position . '">';
    $html .= '<div class="accountpruner-bulk-controls">';
    $html .= '<select class="form-control accountpruner-bulk-action" data-accountpruner-bulk-action aria-label="Bulk action">';
    $html .= '<option value="">Bulk Action</option>';
    $html .= '<option value="set_active">Set Active</option>';
    $html .= '<option value="set_inactive">Set Inactive</option>';
    $html .= '<option value="set_closed">Set Closed</option>';
    $html .= '<option value="delete">Delete</option>';
    $html .= '</select>';
    $html .= '<button type="submit" class="btn btn-primary accountpruner-bulk-apply" data-accountpruner-bulk-apply form="accountpruner-bulk-form" disabled>Apply to Selected</button>';
    $html .= '<span class="accountpruner-selected-count" data-accountpruner-selected-count>0 selected</span>';
    $html .= '</div>';
    $html .= accountpruner_pagination($moduleLink, $filters, $pageData);
    $html .= '</div>';
    return $html;
}

function accountpruner_contents_cell(int $clientId, string $type, int $count, array $breakdown): string
{
    if ($count === 0) {
        return '<span class="accountpruner-zero">0</span>';
    }

    $url = $type === 'service'
        ? 'clientsservices.php?userid=' . $clientId
        : 'clientsdomains.php?userid=' . $clientId;

    $label = $count . ' total';
    $parts = [];
    foreach ($breakdown as $status => $statusCount) {
        $parts[] = accountpruner_escape($status) . ' ' . (int) $statusCount;
    }

    $html = '<a class="accountpruner-count-link" href="' . accountpruner_escape($url) . '">' . (int) $count . '</a>';
    $html .= '<div class="accountpruner-subtext">' . accountpruner_escape($label) . '</div>';
    if ($parts !== []) {
        $html .= '<div class="accountpruner-breakdown">' . implode(' &middot; ', $parts) . '</div>';
    }
    return $html;
}

function accountpruner_payment_method_cell(int $count): string
{
    if ($count <= 0) {
        return '<span class="accountpruner-pm-count accountpruner-pm-zero">0</span>';
    }

    return '<span class="accountpruner-pm-count">' . number_format($count) . '</span>';
}

function accountpruner_activity_cell(int $count, string $lastDate, bool $includeTime = false): string
{
    if ($count <= 0) {
        return '<span class="accountpruner-history-count accountpruner-history-zero">0</span><div class="accountpruner-subtext">Never</div>';
    }

    $formatted = accountpruner_format_date($lastDate, $includeTime);
    return '<span class="accountpruner-history-count">' . number_format($count) . '</span><div class="accountpruner-subtext">Last: ' . $formatted . '</div>';
}

function accountpruner_status_badge(string $status): string
{
    $class = 'accountpruner-status-' . strtolower($status);
    return '<span class="accountpruner-status ' . accountpruner_escape($class) . '">' . accountpruner_escape($status) . '</span>';
}

function accountpruner_format_date(string $value, bool $includeTime = true): string
{
    $value = trim($value);
    if ($value === '' || strpos($value, '0000-00-00') === 0) {
        return '<span class="text-muted">—</span>';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return accountpruner_escape($value);
    }

    return accountpruner_escape(date($includeTime ? 'M j, Y g:i A' : 'M j, Y', $timestamp));
}

function accountpruner_format_last_login(string $value): string
{
    $value = trim($value);
    if ($value === '' || strpos($value, '0000-00-00') === 0) {
        return '<span class="accountpruner-never">Never</span>';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return accountpruner_escape($value);
    }

    $days = max(0, (int) floor((time() - $timestamp) / 86400));
    $relative = $days === 0 ? 'Today' : ($days === 1 ? '1 day ago' : number_format($days) . ' days ago');

    return '<div>' . accountpruner_escape(date('M j, Y g:i A', $timestamp)) . '</div><div class="accountpruner-subtext">' . accountpruner_escape($relative) . '</div>';
}

function accountpruner_sort_header(string $moduleLink, array $filters, string $key, string $label): string
{
    $active = $filters['sort'] === $key;
    $direction = ($active && $filters['dir'] === 'asc') ? 'desc' : 'asc';
    $next = $filters;
    $next['sort'] = $key;
    $next['dir'] = $direction;
    $next['page'] = 1;

    $sortClass = 'accountpruner-sorting';
    $ariaSort = 'none';
    if ($active && $filters['dir'] === 'asc') {
        $sortClass = 'accountpruner-sorting-asc';
        $ariaSort = 'ascending';
    } elseif ($active && $filters['dir'] === 'desc') {
        $sortClass = 'accountpruner-sorting-desc';
        $ariaSort = 'descending';
    }

    $title = $key === 'paymentmethods' ? ' title="Payment Methods"' : '';
    return '<th class="accountpruner-sortable-heading ' . $sortClass . '" aria-sort="' . $ariaSort . '"' . $title . '><a class="accountpruner-sort" href="' . accountpruner_escape(accountpruner_current_url($moduleLink, $next)) . '">' . accountpruner_escape($label) . '</a></th>';
}

function accountpruner_pagination(string $moduleLink, array $filters, array $pageData): string
{
    $page = (int) $pageData['page'];
    $totalPages = (int) $pageData['total_pages'];

    $html = '<div class="accountpruner-pagination">';
    $html .= '<span class="accountpruner-page-label">Page ' . $page . ' of ' . $totalPages . '</span>';

    if ($page > 1) {
        $prev = $filters;
        $prev['page'] = $page - 1;
        $html .= '<a class="btn btn-default" href="' . accountpruner_escape(accountpruner_current_url($moduleLink, $prev)) . '">Previous</a>';
    } else {
        $html .= '<button type="button" class="btn btn-default" disabled>Previous</button>';
    }

    if ($page < $totalPages) {
        $next = $filters;
        $next['page'] = $page + 1;
        $html .= '<a class="btn btn-default" href="' . accountpruner_escape(accountpruner_current_url($moduleLink, $next)) . '">Next</a>';
    } else {
        $html .= '<button type="button" class="btn btn-default" disabled>Next</button>';
    }

    $html .= '</div>';
    return $html;
}

function accountpruner_current_url(string $moduleLink, array $filters): string
{
    $base = $moduleLink;
    $separator = strpos($base, '?') === false ? '?' : '&';
    $params = [
        'ap_scope' => $filters['scope'],
        'ap_status' => strtolower($filters['status']),
        'ap_lastlogin' => $filters['lastlogin'],
        'ap_search' => $filters['search'],
        'ap_perpage' => $filters['perpage'],
        'ap_sort' => $filters['sort'],
        'ap_dir' => $filters['dir'],
        'ap_page' => $filters['page'],
    ];

    return $base . $separator . http_build_query($params);
}

function accountpruner_store_flash(array $flash): void
{
    $_SESSION['accountpruner_flash'] = [
        'type' => (string) ($flash['type'] ?? 'info'),
        'message' => (string) ($flash['message'] ?? ''),
    ];
}

function accountpruner_take_flash(): ?array
{
    if (!isset($_SESSION['accountpruner_flash']) || !is_array($_SESSION['accountpruner_flash'])) {
        return null;
    }

    $flash = $_SESSION['accountpruner_flash'];
    unset($_SESSION['accountpruner_flash']);
    return $flash;
}

function accountpruner_redirect_after_post(string $url): void
{
    // Prefer an HTTP 303. Addon output can run after WHMCS starts the admin page,
    // so location.replace is the safe fallback if response headers are already sent.
    if (!headers_sent()) {
        header('Location: ' . $url, true, 303);
        exit;
    }

    $jsonUrl = json_encode($url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    echo '<div style="padding:20px;text-align:center">Processing complete. Returning to Account Pruner...</div>';
    echo '<script>window.location.replace(' . $jsonUrl . ');</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . accountpruner_escape($url) . '"><a href="' . accountpruner_escape($url) . '">Return to Account Pruner</a></noscript>';
    exit;
}

function accountpruner_render_flash(array $flash): string
{
    $type = (string) ($flash['type'] ?? 'info');
    if (!in_array($type, ['success', 'warning', 'danger', 'info'], true)) {
        $type = 'info';
    }
    return '<div class="alert alert-' . $type . '">' . accountpruner_escape((string) ($flash['message'] ?? '')) . '</div>';
}

function accountpruner_option(string $value, string $label, string $current): string
{
    return '<option value="' . accountpruner_escape($value) . '"' . ($value === $current ? ' selected' : '') . '>' . accountpruner_escape($label) . '</option>';
}

function accountpruner_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function accountpruner_render_runtime_error(\Throwable $e): void
{
    $message = $e->getMessage();
    $location = basename($e->getFile()) . ':' . $e->getLine();

    try {
        logActivity('Account Pruner runtime error: ' . $message . ' in ' . $location);
    } catch (\Throwable $ignored) {
        // Do not allow error logging itself to hide the original problem.
    }

    echo '<style>.accountpruner-runtime{margin:15px 0}.accountpruner-runtime>.panel-heading{background:#163a5f!important;color:#fff!important;border-color:#163a5f!important}.accountpruner-runtime code{white-space:normal;word-break:break-word}</style>';
    echo '<div class="panel panel-default accountpruner-runtime">';
    echo '<div class="panel-heading"><strong>Account Pruner</strong></div><div class="panel-body">';
    echo '<div class="alert alert-danger"><strong>Account Pruner could not load.</strong><br>';
    echo '<code>' . accountpruner_escape($message) . '</code><br><span class="text-muted">' . accountpruner_escape($location) . '</span></div>';
    echo '<p>No client account action was performed. This error was also sent to the WHMCS Activity Log when available.</p>';
    echo '</div></div>';
}

function accountpruner_styles(): string
{
    return <<<'HTML'
<style>
.accountpruner-wrap{max-width:100%;}
.accountpruner-heading{display:flex;justify-content:space-between;align-items:flex-start;margin:0 0 14px;}
.accountpruner-heading h2{margin:0 0 3px;font-size:24px;color:#163a5f;}
.accountpruner-heading p{margin:0;color:#666;}
.accountpruner-panel{border-color:#d5dbe1;box-shadow:none;}
.accountpruner-panel>.panel-heading{background:#163a5f;color:#fff;border-color:#163a5f;}
.accountpruner-filter-body{padding:12px 14px;}
.accountpruner-filter-form{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;}
.accountpruner-filter-field{min-width:150px;}
.accountpruner-filter-field label{display:block;margin:0 0 4px;font-weight:600;color:#333;}
.accountpruner-search-field{flex:1 1 270px;min-width:240px;}
.accountpruner-perpage-field{min-width:80px;max-width:90px;}
.accountpruner-filter-actions{display:flex;gap:6px;align-items:center;}
.accountpruner-filter-actions .btn-primary,.accountpruner-bulk-controls .btn-primary{background:#f58220;border-color:#f58220;color:#fff;}
.accountpruner-filter-actions .btn-primary:hover,.accountpruner-filter-actions .btn-primary:focus,.accountpruner-bulk-controls .btn-primary:hover,.accountpruner-bulk-controls .btn-primary:focus{background:#214e7a;border-color:#214e7a;color:#fff;}
.accountpruner-filter-actions .btn-default,.accountpruner-pagination .btn-default{background:#163a5f;border-color:#163a5f;color:#fff;}
.accountpruner-filter-actions .btn-default:hover,.accountpruner-pagination .btn-default:hover{background:#214e7a;border-color:#214e7a;color:#fff;}
.accountpruner-result-summary{margin-top:10px;color:#555;}
.accountpruner-safety{background:#fff8df;border-color:#ead89b;color:#5f531c;}
.accountpruner-hidden-bulk-form{display:none!important;}
.accountpruner-table-panel{overflow:visible;}
.accountpruner-table-panel .table-responsive{overflow-x:hidden;}
.accountpruner-table{margin-bottom:0;width:100%;max-width:100%;min-width:0;table-layout:fixed;}
.accountpruner-table col.accountpruner-col-check{width:3.5%;}
.accountpruner-table col.accountpruner-col-client{width:18.5%;}
.accountpruner-table col.accountpruner-col-status{width:7.5%;}
.accountpruner-table col.accountpruner-col-created{width:8.5%;}
.accountpruner-table col.accountpruner-col-lastlogin{width:14%;}
.accountpruner-table col.accountpruner-col-services{width:12.5%;}
.accountpruner-table col.accountpruner-col-domains{width:9%;}
.accountpruner-table col.accountpruner-col-paymentmethods{width:6%;}
.accountpruner-table col.accountpruner-col-invoices{width:9.5%;}
.accountpruner-table col.accountpruner-col-transactions{width:11%;}
.accountpruner-table thead th{background:#163a5f;color:#fff;vertical-align:middle;white-space:nowrap;}
.accountpruner-table thead th a{color:#fff;text-decoration:none;}
.accountpruner-table thead th a:hover,.accountpruner-table thead th a:focus{color:#ffd3ad;text-decoration:none;}
.accountpruner-sortable-heading{padding:0!important;position:relative;cursor:pointer;white-space:nowrap;}
/* Same Payment Method Health Glyphicons treatment, now inside the link so the arrow is clickable too. */
.accountpruner-sortable-heading .accountpruner-sort{display:block;position:relative;width:100%;min-height:36px;padding:8px 30px 8px 8px;margin:0;color:inherit!important;font:inherit;font-weight:600;text-decoration:none!important;box-sizing:border-box;}
.accountpruner-sortable-heading.accountpruner-sorting .accountpruner-sort:after,.accountpruner-sortable-heading.accountpruner-sorting-asc .accountpruner-sort:after,.accountpruner-sortable-heading.accountpruner-sorting-desc .accountpruner-sort:after{position:absolute;bottom:5px;right:8px;display:block;font-family:'Glyphicons Halflings';opacity:.5;font-weight:400;pointer-events:none;}
.accountpruner-sortable-heading.accountpruner-sorting .accountpruner-sort:after{opacity:.2;content:"\e150";}
.accountpruner-sortable-heading.accountpruner-sorting-asc .accountpruner-sort:after{content:"\e155";}
.accountpruner-sortable-heading.accountpruner-sorting-desc .accountpruner-sort:after{content:"\e156";}
.accountpruner-table td{vertical-align:middle;}
.accountpruner-check{width:42px;text-align:center;}
.accountpruner-client-name{font-weight:600;color:#222;}
.accountpruner-client-name:hover,.accountpruner-client-name:focus,.accountpruner-table td a:hover,.accountpruner-table td a:focus{color:#f58220;text-decoration:none;}
.accountpruner-subtext{font-size:12px;color:#777;margin-top:2px;line-height:1.35;}
.accountpruner-breakdown{font-size:11px;color:#777;margin-top:2px;line-height:1.35;}
.accountpruner-date-cell{white-space:nowrap;}
.accountpruner-never{font-weight:600;color:#777;}
.accountpruner-zero{display:inline-block;min-width:28px;text-align:center;color:#777;font-weight:600;}
.accountpruner-count-link{display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:24px;border-radius:12px;background:#d8741f;color:#fff!important;font-weight:700;text-decoration:none!important;}
.accountpruner-count-link:hover,.accountpruner-count-link:focus{background:#214e7a;color:#fff!important;}
.accountpruner-pm-cell{text-align:center;white-space:nowrap;}
.accountpruner-pm-count{display:inline-block;min-width:28px;text-align:center;font-weight:700;color:#163a5f;}
.accountpruner-pm-zero{color:#777;}
.accountpruner-history-cell{white-space:nowrap;}
.accountpruner-transaction-cell{white-space:normal;overflow-wrap:normal;word-break:normal;}
.accountpruner-transaction-cell .accountpruner-subtext{white-space:normal;}
.accountpruner-history-count{display:inline-block;min-width:30px;text-align:center;font-weight:700;color:#163a5f;}
.accountpruner-history-zero{color:#777;}
.accountpruner-status{display:inline-block;min-width:72px;padding:3px 8px;border-radius:12px;text-align:center;font-size:12px;font-weight:700;line-height:1.4;}
.accountpruner-status-active{background:#dff0d8;color:#3c763d;}
.accountpruner-status-inactive{background:#eee;color:#666;}
.accountpruner-status-closed{background:#ddd;color:#555;}
.accountpruner-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;background:#fff;flex-wrap:wrap;}
.accountpruner-bulk-bar-top{border-bottom:1px solid #ddd;}
.accountpruner-bulk-bar-bottom{border-top:1px solid #ddd;}
.accountpruner-bulk-controls{display:flex;align-items:center;gap:7px;flex-wrap:wrap;}
.accountpruner-bulk-controls select{width:150px;}
.accountpruner-selected-count{color:#666;min-width:70px;}
.accountpruner-pagination{display:flex;align-items:center;gap:6px;margin-left:auto;}
.accountpruner-page-label{color:#666;margin-right:3px;white-space:nowrap;}
.accountpruner-empty-row{padding:28px!important;}
.accountpruner-processing{display:none;position:fixed;inset:0;z-index:99999;background:rgba(255,255,255,.72);align-items:center;justify-content:center;}
.accountpruner-processing.is-active{display:flex;}
.accountpruner-processing-card{display:flex;align-items:center;gap:12px;padding:16px 20px;background:#fff;border:1px solid #d5dbe1;border-radius:5px;box-shadow:0 4px 18px rgba(0,0,0,.16);color:#163a5f;font-size:15px;}
.accountpruner-spinner{display:inline-block;width:22px;height:22px;border:3px solid #d5dbe1;border-top-color:#f58220;border-radius:50%;animation:accountpruner-spin .8s linear infinite;}
@keyframes accountpruner-spin{to{transform:rotate(360deg)}}
@media (max-width:900px){.accountpruner-table-panel .table-responsive{overflow-x:auto}.accountpruner-table{min-width:1050px}.accountpruner-pagination{margin-left:0}.accountpruner-footer{align-items:flex-start;}}
</style>
HTML;
}

function accountpruner_script(): string
{
    return <<<'HTML'
<script>
(function () {
    var form = document.getElementById('accountpruner-bulk-form');
    if (!form) { return; }

    var hiddenAction = document.getElementById('accountpruner-bulk-action-value');
    var hiddenIds = document.getElementById('accountpruner-client-ids-csv');
    var actionSelects = Array.prototype.slice.call(document.querySelectorAll('[data-accountpruner-bulk-action]'));
    var applyButtons = Array.prototype.slice.call(document.querySelectorAll('[data-accountpruner-bulk-apply]'));
    var countLabels = Array.prototype.slice.call(document.querySelectorAll('[data-accountpruner-selected-count]'));
    var selectAll = document.getElementById('accountpruner-select-all');
    var processing = document.getElementById('accountpruner-processing');
    var processingText = document.querySelector('[data-accountpruner-processing-text]');

    function checks() {
        return Array.prototype.slice.call(document.querySelectorAll('.accountpruner-row-check'));
    }

    function currentAction() {
        return hiddenAction ? hiddenAction.value : '';
    }

    function syncAction(value, source) {
        value = value || '';
        if (hiddenAction) { hiddenAction.value = value; }
        actionSelects.forEach(function (select) {
            if (select !== source) { select.value = value; }
        });
        refreshEligibility();
    }

    function actionAllowsProtected() {
        return currentAction() === 'set_active';
    }

    function selectedChecks() {
        return checks().filter(function (box) { return box.checked && !box.disabled; });
    }

    function refreshEligibility() {
        var action = currentAction();
        var allowProtected = actionAllowsProtected();
        checks().forEach(function (box) {
            var row = box.closest('[data-accountpruner-row]');
            var isProtected = row && row.getAttribute('data-protected') === '1';
            if (isProtected && !allowProtected && action !== '') {
                box.checked = false;
                box.disabled = true;
                box.title = 'Only Set Active is allowed because this account has Products/Services or Domains.';
            } else {
                box.disabled = false;
                box.title = '';
            }
        });
        refreshState();
    }

    function refreshState() {
        var enabled = checks().filter(function (box) { return !box.disabled; });
        var selected = selectedChecks();
        countLabels.forEach(function (label) {
            label.textContent = selected.length + ' selected';
        });
        applyButtons.forEach(function (button) {
            button.disabled = currentAction() === '' || selected.length === 0;
        });
        if (selectAll) {
            selectAll.checked = enabled.length > 0 && selected.length === enabled.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < enabled.length;
        }
    }

    function beginProcessing(action, count) {
        var text;
        if (action === 'delete') {
            text = 'Deleting ' + count + ' account' + (count === 1 ? '' : 's') + '...';
        } else {
            var statusName = action === 'set_active' ? 'Active' : (action === 'set_inactive' ? 'Inactive' : 'Closed');
            text = 'Changing ' + count + ' account' + (count === 1 ? '' : 's') + ' to ' + statusName + '...';
        }
        if (processingText) { processingText.textContent = text; }
        if (processing) {
            processing.classList.add('is-active');
            processing.setAttribute('aria-hidden', 'false');
        }
        applyButtons.forEach(function (button) { button.disabled = true; });
        actionSelects.forEach(function (select) { select.disabled = true; });
        checks().forEach(function (box) { box.disabled = true; });
        if (selectAll) { selectAll.disabled = true; }
    }

    actionSelects.forEach(function (select) {
        select.addEventListener('change', function () {
            syncAction(select.value, select);
        });
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checks().forEach(function (box) {
                if (!box.disabled) {
                    box.checked = selectAll.checked;
                }
            });
            refreshState();
        });
    }

    checks().forEach(function (box) {
        box.addEventListener('change', refreshState);
    });

    form.addEventListener('submit', function (event) {
        var action = currentAction();
        if (action === '') {
            event.preventDefault();
            return;
        }

        var selected = selectedChecks();
        if (selected.length < 1) {
            event.preventDefault();
            return;
        }

        if (hiddenIds) {
            hiddenIds.value = selected.map(function (box) { return box.value; }).join(',');
        }

        if (action === 'delete') {
            if (!window.confirm('Permanently delete ' + selected.length + ' selected empty account' + (selected.length === 1 ? '' : 's') + '?\n\nWHMCS client deletion cannot be undone. Eligibility will be checked again before each deletion.')) {
                event.preventDefault();
                return;
            }
            beginProcessing(action, selected.length);
            return;
        }

        var statusName = action === 'set_active' ? 'Active' : (action === 'set_inactive' ? 'Inactive' : 'Closed');
        if (!window.confirm('Change ' + selected.length + ' selected account' + (selected.length === 1 ? '' : 's') + ' to ' + statusName + '?')) {
            event.preventDefault();
            return;
        }
        beginProcessing(action, selected.length);
    });

    refreshEligibility();
})();
</script>
HTML;
}

